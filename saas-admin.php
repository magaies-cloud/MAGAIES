<?php
$page_title = 'SaaS Super Admin - مقاييس (MAGAIEES)';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" href="manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#050505">
    <title>SaaS Super Admin - مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css?v=20260723_v14">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js" onload="if(typeof initAnalyticsCharts === 'function') initAnalyticsCharts()"></script>
    <script src="js/localforage.min.js"></script>
    <style>
        :root {
            --bg-color: #050505;
            --surface-color: rgba(20, 20, 22, 0.8);
            --primary-accent: #f59e0b;
            --secondary-accent: #f97316;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #eab308;
            --text-primary: #fdfdfd;
            --text-secondary: #a1a1aa;
            --glass-border: rgba(255, 255, 255, 0.08);
        }
        body { background-color: var(--bg-color); color: var(--text-primary); font-family: 'Inter', 'Tajawal', sans-serif; margin: 0; padding: 0; min-height: 100vh; overflow-x: hidden; }
        .ambient-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; overflow: hidden; z-index: -1; pointer-events: none; background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%); }
        .orb { position: absolute; border-radius: 50%; filter: blur(40px); opacity: 0.15; animation: floatOrb 20s infinite alternate ease-in-out; }
        .orb-1 { width: 500px; height: 500px; background: #f59e0b; top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: #a855f7; bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: #ea580c; top: 30%; left: 30%; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 50% { transform: translate(30px, -30px) scale(1.05); } 100% { transform: translate(0, 0) scale(1); } }

        /* === SIDEBAR NAVIGATION === */
        .app-layout { display: flex; min-height: 100vh; direction: rtl; }
        .sidebar {
            position: fixed; top: 0; right: 0; height: 100vh; width: 72px; z-index: 1000;
            background: rgba(12, 10, 18, 0.95); backdrop-filter: blur(8px);
            border-left: 1px solid var(--glass-border);
            transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-x: hidden; overflow-y: auto;
            display: flex; flex-direction: column;
            box-shadow: -5px 0 25px rgba(0,0,0,0.4);
        }
        .sidebar:hover, .sidebar.pinned { width: 270px; }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .sidebar-header {
            padding: 18px 16px; display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid var(--glass-border); min-height: 65px; flex-shrink: 0;
        }
        .sidebar-header .brand-logo {
            width: 40px; height: 40px; min-width: 40px; border-radius: 10px;
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .sidebar-header .brand-label { white-space: nowrap; opacity: 0; transition: opacity 0.2s 0.1s; }
        .sidebar:hover .sidebar-header .brand-label,
        .sidebar.pinned .sidebar-header .brand-label { opacity: 1; }
        .sidebar-header .brand-label h3 { margin: 0; font-size: 0.95rem; font-weight: 800; color: #fff; }
        .sidebar-header .brand-label small { color: var(--text-secondary); font-size: 0.75rem; }

        .sidebar-nav { flex: 1; padding: 10px 8px; display: flex; flex-direction: column; gap: 4px; }

        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 13px 16px;
            border-radius: 10px; cursor: pointer; transition: all 0.2s; color: #fff;
            white-space: nowrap; position: relative; user-select: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .nav-item.active { background: linear-gradient(135deg, rgba(245,158,11,0.18), rgba(234,88,12,0.12)); color: #fff; }
        .nav-item.active::before {
            content: ''; position: absolute; right: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 60%; background: var(--primary-accent); border-radius: 3px 0 0 3px;
        }
        .nav-item .nav-icon { font-size: 1.5rem; min-width: 24px; text-align: center; }
        .nav-item .nav-label { opacity: 0; transition: opacity 0.2s 0.1s; font-size: 1.1rem; font-weight: 600; }
        .sidebar:hover .nav-item .nav-label,
        .sidebar.pinned .nav-item .nav-label { opacity: 1; }

        .nav-group-header {
            display: flex; align-items: center; gap: 12px; padding: 11px 16px;
            border-radius: 10px; cursor: pointer; transition: all 0.2s; color: var(--text-secondary);
            white-space: nowrap; user-select: none;
        }
        .nav-group-header:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .nav-group-header.has-active { color: var(--primary-accent); }
        .nav-group-header .nav-icon { font-size: 1.2rem; min-width: 24px; text-align: center; }
        .nav-group-header .nav-label { opacity: 0; transition: opacity 0.2s 0.1s; font-size: 0.88rem; font-weight: 600; flex: 1; }
        .nav-group-header .nav-arrow { opacity: 0; transition: all 0.2s; font-size: 0.65rem; color: var(--text-secondary); }
        .sidebar:hover .nav-group-header .nav-label,
        .sidebar.pinned .nav-group-header .nav-label { opacity: 1; }
        .sidebar:hover .nav-group-header .nav-arrow,
        .sidebar.pinned .nav-group-header .nav-arrow { opacity: 1; }
        .nav-group-header.open .nav-arrow { transform: rotate(90deg); }

        .nav-sub-items {
            max-height: 0; overflow: hidden; transition: max-height 0.3s ease;
            padding-right: 20px;
        }
        .nav-sub-items.open { max-height: 300px; }
        .nav-sub-items .nav-item { padding: 9px 16px 9px 12px; font-size: 0.84rem; }
        .nav-sub-items .nav-item .nav-icon { font-size: 1rem; }

        .sidebar-footer {
            padding: 12px 16px; border-top: 1px solid var(--glass-border);
            display: flex; align-items: center; gap: 12px; flex-shrink: 0;
        }
        .sidebar-pin {
            width: 38px; height: 38px; min-width: 38px; border-radius: 8px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);
            color: var(--text-secondary); cursor: pointer; display: flex;
            align-items: center; justify-content: center; transition: all 0.2s; font-size: 1rem;
        }
        .sidebar-pin:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar.pinned .sidebar-pin { color: var(--primary-accent); background: rgba(245,158,11,0.1); }

        .dash-main {
            margin-right: 72px; width: calc(100% - 72px);
            padding: 0px 20px 20px 20px; box-sizing: border-box;
            transition: margin-right 0.35s cubic-bezier(0.16, 1, 0.3, 1), width 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Keep legacy .tabs-container hidden but functional for inner tabs (tenant drawer) */
        .tabs-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-secondary);
            padding: 12px 8px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            text-align: center;
            font-size: 0.85rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tab-btn.active { background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(234,88,12,0.2)); border-color: rgba(245,158,11,0.5); color: #fff; }
        .tab-content { display: none; animation: fadeUp 0.3s ease forwards; }
        .tab-content.active { display: block; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Hide the old horizontal tabs container on the main page */
        #main-tabs-container { display: none !important; }

        .section-inner-tabs {
            display: flex; gap: 10px; margin-bottom: 20px;
        }
        .section-inner-tab {
            flex: 1; padding: 14px 20px; border-radius: 12px; cursor: pointer;
            background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border);
            color: var(--text-secondary); font-weight: 700; font-size: 0.95rem;
            text-align: center; transition: all 0.3s; display: flex; align-items: center;
            justify-content: center; gap: 8px;
        }
        .section-inner-tab:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .section-inner-tab.active {
            background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(234,88,12,0.15));
            border-color: rgba(245,158,11,0.5); color: #fff;
        }

        .premium-header {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(168, 85, 247, 0.15));
            backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 15px 25px;
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }
        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon { width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #ea580c); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 800; color: #fff; }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #cbd5e1; }
        
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .glass-panel { background: var(--surface-color); backdrop-filter: blur(8px); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px; transition: all 0.3s; }
        .glass-panel:hover { border-color: rgba(255,255,255,0.15); box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        .kpi-card { padding: 10px 15px !important; border-radius: 12px !important; }
        .kpi-title { font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; display: flex; justify-content: space-between; margin-bottom: 5px; }
        .kpi-value { font-size: 1.4rem; font-weight: 700; }
        .kpi-1 { border-top: 3px solid #3b82f6; } .kpi-1 .kpi-value { color: #3b82f6; }
        .kpi-2 { border-top: 3px solid #10b981; } .kpi-2 .kpi-value { color: #10b981; }
        .kpi-3 { border-top: 3px solid #f59e0b; } .kpi-3 .kpi-value { color: #f59e0b; }
        .kpi-4 { border-top: 3px solid #a855f7; } .kpi-4 .kpi-value { color: #a855f7; }
        .islamic-ticker-container { overflow: hidden; white-space: nowrap; width: 350px; margin-left: 20px; position: relative; display: flex; align-items: center; border-radius: 8px; background: rgba(0,0,0,0.2); padding: 5px 10px; border: 1px solid rgba(255,255,255,0.05); }
        .islamic-ticker {
            display: inline-block; font-weight: 700; font-size: 0.95rem; color: transparent;
            background: linear-gradient(90deg, #f59e0b, #10b981, #3b82f6, #a855f7, #f59e0b);
            background-size: 200% auto; -webkit-background-clip: text; background-clip: text;
            animation: gradientMove 3s linear infinite, scrollTicker 20s linear infinite;
            text-shadow: 0 0 10px rgba(245, 158, 11, 0.2); white-space: nowrap; padding-left: 100%;
        }
        @keyframes scrollTicker { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        @keyframes gradientMove { 0% { background-position: 0% center; } 100% { background-position: 200% center; } }

        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 14px 16px; text-align: right; border-bottom: 1px solid var(--glass-border); }
        th { color: var(--text-secondary); font-size: 0.9rem; background: rgba(0,0,0,0.2); }
        .table-responsive { width: 100%; overflow-x: auto; border-radius: 12px; border: 1px solid var(--glass-border); }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-block; }
        .badge-active { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-suspended { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        .btn-primary { background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); color: #fff; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: all 0.3s; }
        .btn-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.3); padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; transition: all 0.3s; }
        .btn-outline { background: transparent; border: 1px solid var(--text-secondary); color: var(--text-secondary); padding: 10px 20px; border-radius: 10px; cursor: pointer; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.3); color: white; box-sizing: border-box; }

        #login-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); }
        .login-box { width: 90%; max-width: 400px; text-align: center; padding: 40px 35px; }
        .login-box input { width: 100%; padding: 14px; border-radius: 10px; background: rgba(255,255,255,0.07); color: #fff; border: 1px solid var(--glass-border); font-size: 1.1rem; box-sizing: border-box; margin-bottom: 15px; font-family: 'Tajawal',sans-serif; }
        .login-box input:focus { outline: none; border-color: var(--primary-accent); }
        
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-content { width: 90%; max-width: 500px; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        
        /* Dropdown */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; right: 0; background: rgba(30,41,59,0.95); min-width: 140px; box-shadow: 0 8px 30px rgba(0,0,0,0.6); border-radius: 12px; border: 1px solid var(--glass-border); z-index: 100; }
        .dropdown-content button { color: #fff; padding: 10px 15px; text-decoration: none; display: block; width: 100%; text-align: right; background: transparent; border: none; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .dropdown-content button:hover { background: rgba(255,255,255,0.1); }
        .dropdown.show .dropdown-content { display: block; }

        /* تخصيص Flatpickr ليتناسب مع التصميم الداكن الفخم */
        .flatpickr-calendar {
            background: rgba(20, 20, 22, 0.98) !important;
            backdrop-filter: blur(8px);
            border: 1px solid var(--glass-border) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7) !important;
            font-family: 'Tajawal', 'Inter', sans-serif !important;
            border-radius: 16px !important;
            direction: rtl !important;
        }
        .flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after {
            border-bottom-color: rgba(20, 20, 22, 0.98) !important;
        }
        .flatpickr-calendar.arrowBottom:before, .flatpickr-calendar.arrowBottom:after {
            border-top-color: rgba(20, 20, 22, 0.98) !important;
        }
        .flatpickr-months .flatpickr-month {
            color: var(--text-primary) !important;
            fill: var(--text-primary) !important;
        }
        .flatpickr-current-month .numInputWrapper span.arrowUp:after {
            border-bottom-color: var(--text-primary) !important;
        }
        .flatpickr-current-month .numInputWrapper span.arrowDown:after {
            border-top-color: var(--text-primary) !important;
        }
        .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
            color: var(--text-primary) !important;
            fill: var(--text-primary) !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover, .flatpickr-months .flatpickr-next-month:hover {
            color: var(--primary-accent) !important;
        }
        span.flatpickr-weekday {
            color: var(--text-secondary) !important;
            font-weight: 600;
        }
        .flatpickr-day {
            color: var(--text-primary) !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
        }
        .flatpickr-day:hover, .flatpickr-day:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: transparent !important;
        }
        .flatpickr-day.today {
            border-color: var(--primary-accent) !important;
            color: var(--primary-accent) !important;
        }
        .flatpickr-day.today:hover {
            background: rgba(245, 158, 11, 0.15) !important;
            color: #fff !important;
        }
        .flatpickr-day.selected, .flatpickr-day.selected:hover, .flatpickr-day.selected:focus {
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)) !important;
            border-color: transparent !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3) !important;
        }
        .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
            color: rgba(255, 255, 255, 0.15) !important;
        }
        .flatpickr-time {
            border-top: 1px solid var(--glass-border) !important;
            color: var(--text-primary) !important;
        }
        .flatpickr-time input {
            color: var(--text-primary) !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 1.1rem !important;
        }
        .flatpickr-time input:hover, .flatpickr-time input:focus {
            background: rgba(255, 255, 255, 0.05) !important;
        }
        .flatpickr-time .flatpickr-am-pm {
            color: var(--text-primary) !important;
            font-family: 'Tajawal', sans-serif !important;
        }
        .flatpickr-time .numInputWrapper span.arrowUp:after {
            border-bottom-color: var(--text-secondary) !important;
        }
        .flatpickr-time .numInputWrapper span.arrowDown:after {
            border-top-color: var(--text-secondary) !important;
        }
        /* أيقونة التقويم داخل حقل الإدخال */
        .form-group .flatpickr-input {
            background-color: rgba(0, 0, 0, 0.3) !important;
            color: #fff !important;
            border: 1px solid var(--glass-border) !important;
            font-family: 'Tajawal', 'Inter', sans-serif !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23a1a1aa'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' /%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: left 12px center !important;
            background-size: 20px !important;
            padding-left: 40px !important;
        }

        .pulsating-dot {
            width: 12px; height: 12px; border-radius: 50%;
            background-color: var(--dot-color);
            animation: pulse-dot 2s infinite; flex-shrink: 0;
            display: inline-block; box-shadow: 0 0 0 0 rgba(0,0,0,0.5);
        }
        @keyframes pulse-dot {
            0% { transform: scale(0.8); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.8); opacity: 0.8; }
        }

        /* Global Search Modal */
        #global-search-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 10000;
            justify-content: center; align-items: flex-start; padding-top: 10vh;
        }
        #global-search-modal {
            width: 90%; max-width: 600px; background: rgba(18, 15, 25, 0.95);
            border: 1px solid var(--glass-border); border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5); padding: 20px;
        }
        #global-search-input {
            width: 100%; padding: 15px; font-size: 1.2rem; border-radius: 8px;
            background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--primary-accent);
            outline: none; box-sizing: border-box; font-family: 'Tajawal', sans-serif;
        }
        #global-search-results {
            margin-top: 15px; max-height: 300px; overflow-y: auto;
        }
        .gs-item {
            padding: 12px 15px; border-bottom: 1px solid var(--glass-border);
            cursor: pointer; display: flex; justify-content: space-between; align-items: center;
        }
        .gs-item:hover { background: rgba(255,255,255,0.05); }
        .radial-progress {
            position: relative; display: inline-flex; align-items: center; justify-content: center;
        }
        .radial-value {
            position: absolute; font-size: 1.5rem; font-weight: 800; color: #fff;
        }
        .chat-bubble {
            max-width: 80%; padding: 12px 16px; border-radius: 14px;
            font-size: 0.92rem; line-height: 1.6; word-wrap: break-word;
        }
        .chat-bubble.admin {
            align-self: flex-start; background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(234,88,12,0.15));
            border: 1px solid rgba(245,158,11,0.3); border-radius: 14px 14px 4px 14px;
        }
        .chat-bubble.tenant {
            align-self: flex-end; background: rgba(59,130,246,0.15);
            border: 1px solid rgba(59,130,246,0.3); border-radius: 14px 14px 14px 4px;
        }
        .chat-bubble .chat-meta {
            font-size: 0.75rem; color: var(--text-secondary); margin-top: 6px;
        }
        .toast-notification {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px);
            background: rgba(20, 20, 22, 0.95); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 12px;
            padding: 14px 28px; color: #fff; font-weight: 600; font-size: 0.95rem;
            z-index: 99999; opacity: 0; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 10px;
        }
        .toast-notification.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toast-notification.success { border-color: rgba(16,185,129,0.5); }
        .toast-notification.error { border-color: rgba(239,68,68,0.5); }
        .toast-notification.info { border-color: rgba(59,130,246,0.5); }

        /* Tenant Detail View Enhancements */
        .tenant-header {
            display: flex; justify-content: space-between; align-items: center; 
            padding: 20px; background: rgba(0, 0, 0, 0.25); border-radius: 16px; 
            margin-bottom: 25px; border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .tenant-info-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15); transition: all 0.3s ease;
            position: relative; overflow: hidden;
        }
        .tenant-info-card:hover {
            border-color: rgba(255,255,255,0.15); box-shadow: 0 8px 30px rgba(0,0,0,0.25);
            transform: translateY(-2px);
        }
        .tenant-info-card h4 {
            margin-top: 0; color: var(--primary-accent); margin-bottom: 20px; 
            font-size: 1.15rem; display: flex; align-items: center; gap: 8px;
        }
        .tenant-info-row {
            margin: 15px 0; font-size: 1.1rem; display: flex; align-items: center; justify-content: space-between;
        }
        .tenant-edit-row {
            margin: 15px 0; font-size: 1.1rem; display: flex; flex-direction: column; align-items: flex-start; gap: 10px;
        }
        .tenant-info-label {
            color: var(--text-secondary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px; min-width: 80px;
        }
        .tenant-info-value {
            color: #fff; font-weight: 500; text-align: left;
        }
        .glass-input {
            padding: 12px 15px; border-radius: 10px; background: rgba(0,0,0,0.4); 
            color: #fff; border: 1px solid rgba(255,255,255,0.15); width: 100%;
            font-family: 'Tajawal', sans-serif; font-size: 1.1rem; font-weight: bold; transition: all 0.2s ease;
        }
        .glass-input:focus {
            border-color: var(--primary-accent); outline: none; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }
        .progress-bar-bg {
            width: 100%; background: rgba(0,0,0,0.4); border-radius: 8px; height: 12px; overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05);
        }
        .progress-bar-glow {
            height: 100%; border-radius: 8px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .progress-bar-glow::after {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        .admin-notes-card {
            background: linear-gradient(145deg, rgba(245, 158, 11, 0.05) 0%, rgba(0,0,0,0.2) 100%);
            border-left: 4px solid var(--primary-accent); padding: 20px; border-radius: 16px;
            margin-bottom: 25px; border-top: 1px solid rgba(255,255,255,0.05); border-right: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .split-view-grid {
            display: grid; grid-template-columns: 3fr 2fr; gap: 20px;
        }
        @media (max-width: 900px) {
            .split-view-grid { grid-template-columns: 1fr; }
            .tenant-header { flex-direction: column; gap: 15px; align-items: flex-start; }
            #detail-actions-bar { width: 100%; justify-content: space-between; }
            .tenant-info-row { flex-direction: column; align-items: flex-start; gap: 8px; }
            .glass-input { width: 100%; }
        }
/* From Uiverse.io by preet_7613 */ 
.switch {
  display: block;
  position: relative;
  width: 100px;
  height: 60px;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  transform: scale(0.65); /* Scaled down slightly to fit table better */
  transform-origin: center center;
}

.switch__base-outer,
.switch__base-inner {
  display: block;
  position: absolute;
}

.switch__base-outer {
  border-radius: 25px;
  box-shadow:
    -2px -2px 4px hsl(223, 10%, 30%),
    2px 2px 2px hsl(223, 10%, 30%) inset,
    2px 2px 4px hsl(0, 0%, 0%),
    -2px -2px 2px hsl(223, 10%, 5%) inset;
  top: 2.5px;
  left: 2.5px;
  width: 95px;
  height: 55px;
}

.switch__base-inner {
  border-radius: 21px;
  box-shadow:
    -4px -4px 4px hsl(223, 10%, 30%) inset,
    1px 1px 2px hsla(223, 10%, 30%, 1),
    2px 4px 4px hsl(223, 10%, 5%) inset,
    -1px -1px 2px hsla(223, 10%, 5%, 1);
  top: 7.5px;
  left: 7.5px;
  width: 85px;
  height: 45px;
}

.switch__input {
  outline: transparent;
  position: relative;
  width: 100%;
  height: 100%;
  -webkit-appearance: none;
  appearance: none;
  cursor: pointer;
}

.switch__knob-container {
  border-radius: 20px;
  display: block;
  position: absolute;
  overflow: hidden;
  top: 10px;
  left: 10px;
  width: 80px;
  height: 40px;
}

.switch__knob {
  border-radius: 20px;
  display: block;
  position: absolute;
  left: 0;
  background-color: hsl(223, 10%, 15%);
  background-image: radial-gradient(
      88% 88% at 50% 50%,
      hsl(223, 10%, 20%) 47%,
      hsla(223, 10%, 20%, 0) 50%
    ),
    radial-gradient(
      88% 88% at 47% 47%,
      hsl(223, 10%, 85%) 45%,
      hsla(223, 10%, 85%, 0) 50%
    ),
    radial-gradient(
      65% 70% at 40% 60%,
      hsl(223, 10%, 20%) 46%,
      hsla(223, 10%, 20%, 0) 50%
    );
  box-shadow:
    -1px -1px 1px hsl(223, 10%, 15%) inset,
    -2px -2px 1px hsl(223, 10%, 5%) inset,
    12px 4px 2px hsla(0, 0%, 0%, 0.8);
  width: 40px;
  height: 40px;
  transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1);
}

.switch__knob-shadow {
  border-radius: 50%;
  box-shadow: 2px 2px 2px hsla(0, 0%, 0%, 0.9);
  display: block;
  position: absolute;
  top: 10px;
  left: 10px;
  width: 40px;
  height: 40px;
  transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1);
}

.switch__led {
  background-color: hsl(3, 90%, 70%);
  border-radius: 50%;
  box-shadow:
    0 -1px 1px hsl(3, 90%, 40%) inset,
    0 0 2px hsla(3, 90%, 70%, 0.3),
    2px 2px 2px hsla(0, 0%, 0%, 0.5);
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 5px;
  transition:
    background-color 0.6s cubic-bezier(0.65, 0, 0.35, 1),
    box-shadow 0.6s cubic-bezier(0.65, 0, 0.35, 1);
}

.switch__input:checked ~ .switch__led {
  background-color: hsl(123, 90%, 70%);
  box-shadow:
    0 -1px 1px hsl(123, 90%, 40%) inset,
    0 -2px 2px hsla(123, 90%, 70%, 0.3),
    0 2px 2px hsla(123, 90%, 70%, 0.3),
    2px 2px 2px hsla(0, 0%, 0%, 0.5);
}

.switch__input:checked ~ .switch__knob-shadow,
.switch__input:checked ~ .switch__knob-container .switch__knob {
  transform: translateX(40px);
}

/* Missing Neon Animation CSS */
.switch__base-neon {
  display: block;
  overflow: visible;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: auto;
}
.switch__base-neon path {
  stroke-dasharray: 0 104.26 0;
  transition: stroke-dasharray 0.6s cubic-bezier(0.65, 0, 0.35, 1);
}
.switch__knob-neon {
  display: block;
  width: 40px;
  height: auto;
}
.switch__knob-neon circle {
  opacity: 0;
  stroke-dasharray: 0 90.32 0 54.19;
  transition:
    opacity 0.6s steps(1, end),
    stroke-dasharray 0.6s cubic-bezier(0.65, 0, 0.35, 1);
}
.switch__input:checked ~ .switch__base-neon path {
  stroke-dasharray: 52.13 0 52.13;
}
.switch__input:checked ~ .switch__knob-container .switch__knob-neon circle {
  opacity: 1;
  stroke-dasharray: 45.16 0 45.16 54.19;
  transition-timing-function: steps(1, start), cubic-bezier(0.65, 0, 0.35, 1);
}
</style>
</head>
<body>
    <div id="global-search-overlay" onclick="if(event.target===this) toggleGlobalSearch()">
        <div id="global-search-modal">
            <input type="text" id="global-search-input" placeholder="🔍 بحث شامل عن شركة، نطاق، مالك..." onkeyup="performGlobalSearch()">
            <div id="global-search-results"></div>
        </div>
    </div>
    
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div></div>

    <div id="login-modal">
        <div class="glass-panel login-box">
            <div style="margin-bottom: 25px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #ea580c); border-radius: 20px; margin: 0 auto 15px auto; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; box-shadow: 0 10px 25px rgba(245,158,11,0.4);">🛡️</div>
                <h2 style="color: #fff; font-weight: 900; margin: 0;">SaaS Super Admin</h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 5px;">أدخل رمز المشرف للمتابعة</p>
            </div>
            <input type="password" id="saas-pass" placeholder="Master Token">
            <button class="btn-primary" onclick="loginSaas()" style="width: 100%; font-family:'Tajawal',sans-serif;">تسجيل الدخول</button>
        </div>
    </div>

    <!-- SIDEBAR NAVIGATION -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="brand-logo">&#x1F451;</div>
            <div class="brand-label"><h3>SaaS Admin</h3><small>&#x0625;&#x062F;&#x0627;&#x0631;&#x0629; &#x0627;&#x0644;&#x0645;&#x0646;&#x0635;&#x0629;</small></div>
        </div>
        <div class="sidebar-nav">
            <div class="nav-item active" onclick="sidebarNav('tab-analytics', this)"><span class="nav-icon">&#x1F4CA;</span><span class="nav-label">التحليلات</span></div>
            <div class="nav-item" onclick="sidebarNav('tab-tenants', this)"><span class="nav-icon">&#x1F3E2;</span><span class="nav-label">المشتركون</span></div>
            <div class="nav-item" onclick="sidebarNav('tab-global-payments', this)"><span class="nav-icon">&#x1F4B0;</span><span class="nav-label">المالية</span></div>
            <div class="nav-item" onclick="sidebarNav('tab-broadcasts', this)"><span class="nav-icon">&#x1F4E3;</span><span class="nav-label">التواصل</span></div>
            <div class="nav-item" onclick="sidebarNav('tab-ads', this)"><span class="nav-icon">&#x1F31F;</span><span class="nav-label">التسويق</span></div>
            <div class="nav-item" onclick="sidebarNav('tab-backup-saas', this)"><span class="nav-icon">&#x1F4BE;</span><span class="nav-label">النسخ الاحتياطي</span></div>
            <div class="nav-item" onclick="sidebarNav('tab-health', this)"><span class="nav-icon">&#x1FA7A;</span><span class="nav-label">المراقبة والأمان</span></div>
            <div class="nav-item" onclick="sidebarNav('tab-settings', this)"><span class="nav-icon">&#x2699;&#xFE0F;</span><span class="nav-label">الإعدادات</span></div>
        </div>
        <div class="sidebar-footer">
            <button class="sidebar-pin" onclick="toggleSidebarPin()" title="تثبيت القائمة">&#x1F4CC;</button>
        </div>
    </nav>

    <div id="dashboard" class="dash-main" style="display: none;">
        <div class="premium-header">
            <div class="header-brand">
                <div class="brand-icon">👑</div>
                <div class="brand-text">
                    <h1>SaaS Super Admin</h1>
                    <p>إدارة الشركات والباقات والمستخدمين</p>
                </div>
            </div>
            <div class="header-controls" style="display:flex; align-items:center; gap:15px;">
                <div class="notification-bell" style="position:relative; cursor:pointer; font-size:1.5rem; user-select:none; margin-left: 10px;" onclick="toggleNotifications(event)">
                    🔔
                    <span id="notif-badge" style="display:none; position:absolute; top:-5px; right:-5px; background:var(--danger-color); color:#fff; border-radius:50%; font-size:0.7rem; font-weight:bold; width:18px; height:18px; text-align:center; line-height:18px;">0</span>
                    <div id="notif-dropdown" class="glass-panel" style="display:none; position:absolute; top:40px; right:0; width:350px; max-height:400px; overflow-y:auto; z-index:100; font-size:0.9rem; padding:15px; text-align:right; flex-direction:column; gap:10px; cursor:default;" onclick="event.stopPropagation()">
                        <h4 style="margin:0 0 10px 0; border-bottom:1px solid var(--glass-border); padding-bottom:5px; color:var(--primary-accent);">التنبيهات الذكية</h4>
                        <div id="notif-list" style="color:var(--text-secondary); display:flex; flex-direction:column; gap:10px;">لا توجد تنبيهات حالياً.</div>
                    </div>
                </div>
                <div id="wa-status-widget" onclick="openAdminWhatsAppModal()" style="display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; color:#d1d5db; cursor:pointer;">
                    <span id="wa-status-indicator" style="width:10px; height:10px; border-radius:50%; background:#ef4444; display:inline-block; box-shadow:0 0 8px #ef4444;"></span>
                    <span id="wa-status-text" style="font-weight:bold;">خادم الواتساب: فحص...</span>
                </div>
                <div class="islamic-ticker-container">
                    <div class="islamic-ticker">
                        سُبْحَانَ اللَّهِ وَبِحَمْدِهِ ، سُبْحَانَ اللَّهِ الْعَظِيمِ 🌸 أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ 🌸 اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ
                    </div>
                </div>
                <button onclick="logoutSaas()" class="btn-danger">تسجيل الخروج</button>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="glass-panel kpi-card kpi-1">
                <div class="kpi-title"><span>إجمالي المشتركين</span> 🏢</div>
                <div class="kpi-value" id="kpi-total-tenants">0</div>
            </div>
            <div class="glass-panel kpi-card kpi-2">
                <div class="kpi-title"><span>حسابات نشطة</span> 🟢</div>
                <div class="kpi-value" id="kpi-active-tenants">0</div>
            </div>
            <div class="glass-panel kpi-card kpi-3">
                <div class="kpi-title"><span>حسابات موقوفة</span> ⚠️</div>
                <div class="kpi-value" id="kpi-suspended-tenants">0</div>
            </div>
            <div class="glass-panel kpi-card kpi-4">
                <div class="kpi-title"><span>إجمالي الإيرادات (شهري)</span> 💰</div>
                <div class="kpi-value" id="kpi-revenue">$0</div>
            </div>
        </div>

        <div id="main-tabs-container" class="tabs-container">
            <button class="tab-btn active" onclick="switchTab('tab-analytics', this)">📊 التحليلات والإحصائيات</button>
            <button class="tab-btn" onclick="switchTab('tab-tenants', this)">🏢 المشتركون</button>
            <button class="tab-btn" onclick="switchTab('tab-global-payments', this)">💰 الفواتير والمدفوعات</button>
            <button class="tab-btn" onclick="switchTab('tab-plans', this)">💎 الباقات (Plans)</button>
            <button class="tab-btn" onclick="switchTab('tab-partners', this)">🤝 الشركاء والعملاء</button>
            <button class="tab-btn" onclick="switchTab('tab-broadcasts', this)">📣 رسائل النظام</button>
            <button class="tab-btn" onclick="switchTab('tab-ads', this)">🌟 الإعلانات المدفوعة</button>
            <button class="tab-btn" onclick="switchTab('tab-audit', this)">🛡️ سجل العمليات</button>
            <button class="tab-btn" onclick="switchTab('tab-backup-saas', this)">💾 نسخة SaaS الكاملة</button>
            <button class="tab-btn" onclick="switchTab('tab-backup-tenants', this)">👥 نسخة المشتركين المحددة</button>
            <button class="tab-btn" onclick="switchTab('tab-health', this)">🩺 حالة الخادم (Health)</button>
            <button class="tab-btn" onclick="switchTab('tab-tickets', this)">🎫 تذاكر الدعم الفني</button>
            <button class="tab-btn" onclick="switchTab('tab-security', this)">🛡️ الأمان (Security)</button>
            <button class="tab-btn" onclick="switchTab('tab-settings', this)">⚙️ الإعدادات</button>
        </div>

        <!-- TAB: ANALYTICS -->
        <div id="tab-analytics" class="tab-content active">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="glass-panel" style="padding: 20px; min-height: 350px;">
                    <h3 style="margin-top:0; margin-bottom: 20px; display:flex; align-items:center; gap:8px;">📈 وتيرة تسجيل المشتركين الجدد</h3>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="chart-registrations"></canvas>
                    </div>
                </div>
                <div class="glass-panel" style="padding: 20px; min-height: 350px;">
                    <h3 style="margin-top:0; margin-bottom: 20px; display:flex; align-items:center; gap:8px;">💾 أكبر 5 شركات استهلاكاً للمساحة التخزينية</h3>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="chart-storage"></canvas>
                    </div>
                </div>
                <div class="glass-panel" style="padding: 20px; min-height: 350px; grid-column: 1 / -1;">
                    <h3 style="margin-top:0; margin-bottom: 20px; display:flex; align-items:center; gap:8px;">💰 نمو الإيرادات المالية (حسب المدفوعات المسجلة)</h3>
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chart-revenue"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: AUDIT LOGS -->
        <div id="tab-audit" class="tab-content">
            <div class="section-inner-tabs" data-group="monitoring">
                <div class="section-inner-tab" onclick="switchSectionTab('tab-health', 'monitoring', this)">&#x1F5A5;&#xFE0F; حالة الخادم</div>
                <div class="section-inner-tab" onclick="switchSectionTab('tab-security', 'monitoring', this)">&#x1F6E1;&#xFE0F; الأمان</div>
                <div class="section-inner-tab active" onclick="switchSectionTab('tab-audit', 'monitoring', this)">&#x1F4CB; السجل</div>
                <div class="section-inner-tab" onclick="switchSectionTab('tab-tickets', 'monitoring', this)">&#x1F3AB; التذاكر</div>
            </div>
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>🛡️ سجل العمليات البرمجية والإدارية</h3>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="audit-search" placeholder="بحث في سجل العمليات..." style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 250px;" onkeyup="loadAuditLogs(true)">
                        <button class="btn-primary" onclick="loadAuditLogs(true)" style="padding: 10px 20px; font-weight: bold;">تحديث</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العملية الإدارية</th>
                                <th>تفاصيل العملية</th>
                                <th>عنوان الـ IP</th>
                                <th>التاريخ والوقت</th>
                            </tr>
                        </thead>
                        <tbody id="audit-logs-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="flex-between" style="margin-top:20px;">
                    <button class="btn-primary" id="btn-audit-prev" onclick="changeAuditPage(-1)" style="padding: 8px 16px;">السابق</button>
                    <span id="audit-page-indicator" style="color: var(--text-secondary); font-weight: bold;">الصفحة 1 من 1</span>
                    <button class="btn-primary" id="btn-audit-next" onclick="changeAuditPage(1)" style="padding: 8px 16px;">التالي</button>
                </div>
            </div>
        </div>

        <!-- TAB: DATABASE BACKUPS (SAAS FULL) -->
        <div id="tab-backup-saas" class="tab-content">
            <div class="section-inner-tabs" data-group="backup">
                <div class="section-inner-tab active" onclick="switchSectionTab('tab-backup-saas', 'backup', this)">&#x1F5C4;&#xFE0F; نسخة SaaS الكاملة</div>
                <div class="section-inner-tab" onclick="switchSectionTab('tab-backup-tenants', 'backup', this)">&#x1F465; نسخة المشتركين المحددة</div>
            </div>
            <div class="glass-panel" style="text-align: center; padding: 40px 20px;">
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(37,99,235,0.2)); border-radius: 50%; border: 2px dashed rgba(59,130,246,0.5); display: flex; justify-content: center; align-items: center; font-size: 3.5rem; margin: 0 auto 20px auto; color: #3b82f6;">💾</div>
                <h2 style="color: #fff; font-weight: 800; margin-bottom: 10px;">النسخ الاحتياطي الكامل لـ SaaS</h2>
                <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 30px auto; line-height: 1.6;">
                    تتيح لك هذه الميزة تنزيل نسخة احتياطية كاملة ومباشرة من قاعدة بيانات الـ SaaS الإجمالية (بما في ذلك جداول النظام، بيانات المشتركين، السجلات المالية، الإعلانات، وبنية الجداول بالكامل) بصيغة ملف SQL القياسي للاحتفاظ بها كنسخة أمان خارجية.
                </p>
                <button class="btn-primary" onclick="downloadDatabaseBackup()" style="padding: 15px 40px; font-size: 1.1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; box-shadow: 0 4px 15px rgba(37,99,235,0.4);">
                    <span>تنزيل نسخة احتياطية كاملة (.SQL)</span>
                </button>
            </div>
        </div>

        <!-- TAB: TENANTS DATABASE BACKUPS (CUSTOM FILTERED) -->
        <div id="tab-backup-tenants" class="tab-content">
            <div class="section-inner-tabs" data-group="backup">
                <div class="section-inner-tab" onclick="switchSectionTab('tab-backup-saas', 'backup', this)">&#x1F5C4;&#xFE0F; نسخة SaaS الكاملة</div>
                <div class="section-inner-tab active" onclick="switchSectionTab('tab-backup-tenants', 'backup', this)">&#x1F465; نسخة المشتركين المحددة</div>
            </div>
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h3 style="margin: 0 0 5px 0;">النسخ الاحتياطي المخصص للمشتركين</h3>
                        <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem; line-height: 1.5;">
                            حدد المشتركين من الجدول لتصدير وتنزيل نسخة احتياطية مخصصة ومفلترة لبياناتهم في ملف SQL واحد مدمج.
                        </p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="backup-tenant-search" placeholder="بحث باسم المشترك، المالك، الهاتف..." onkeyup="renderBackupTenantsTable()" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 280px; font-family: Tajawal;">
                        <button class="btn-primary" onclick="downloadSelectedTenantsBackup()" style="padding: 10px 24px; font-weight: 700; background: linear-gradient(135deg, #10b981, #059669); border: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3); display: inline-flex; align-items: center; gap: 8px;">
                            <span>تصدير المشتركين المحددين (.SQL)</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive" style="margin-top: 15px; border-radius: 12px; border: 1px solid var(--glass-border); overflow: hidden; background: rgba(0,0,0,0.15);">
                    <table class="table" style="width: 100%; border-collapse: collapse; text-align: right;">
                        <thead>
                            <tr style="background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--glass-border);">
                                <th style="width: 50px; text-align: center; padding: 15px;"><input type="checkbox" id="backup-select-all-header" onchange="selectAllBackupTenants(this.checked)" style="transform: scale(1.25); cursor: pointer;"></th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">اسم المشترك والشركة</th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">المالك ورقم الهاتف</th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">المساحة المستهلكة</th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">الباقة الحالية</th>
                            </tr>
                        </thead>
                        <tbody id="backup-tenants-table-body">
                            <!-- Populated dynamically via renderBackupTenantsTable() -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: TENANTS (Combined Tenants & Users with Drill-down) -->
        <div id="tab-tenants" class="tab-content">
            <!-- LIST VIEW -->
            <div id="tenants-list-view">
                <div class="glass-panel">
                    <div class="flex-between" style="margin-bottom: 20px;">
                        <h3>سجل المشتركين</h3>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-outline" onclick="exportTenantsToCSV()" style="border-color:var(--primary-accent); color:var(--primary-accent); padding: 10px 20px;">📥 تصدير الإكسل</button>
                            <input type="text" id="tenant-search" placeholder="بحث ذكي (الشركة، النطاق، المالك، المستخدم، الباقة...)" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 350px;" onkeyup="filterTenants()">
                            <select id="tenant-filter-status" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border);" onchange="filterTenants()">
                                <option value="all">الكل</option>
                                <option value="active">نشط</option>
                                <option value="pending">قيد المراجعة</option>
                                <option value="suspended">موقوف</option>
                                <option value="expiring">ينتهي قريباً (أقل من 7 أيام)</option>
                                <option value="expired">منتهي الصلاحية</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>الشركة / النطاق</th>
                                    <th>اسم المستخدم</th>
                                    <th>المالك</th>
                                    <th>الباقة</th>
                                    <th>المساحة المستهلكة</th>
                                    <th>عدد المستخدمين</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="tenants-body">
                                <tr><td colspan="9" style="text-align:center;">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex-between" style="margin-top:20px;">
                        <button class="btn-primary" id="btn-tenants-prev" onclick="changeTenantsPage(-1)" style="padding: 8px 16px;">السابق</button>
                        <span id="tenants-page-indicator" style="color: var(--text-secondary); font-weight: bold;">الصفحة 1 من 1</span>
                        <button class="btn-primary" id="btn-tenants-next" onclick="changeTenantsPage(1)" style="padding: 8px 16px;">التالي</button>
                    </div>
                </div>
            </div>

            <!-- FULL DETAIL VIEW -->
            <div id="tenant-full-view" style="display: none;">
                <!-- Header -->
                <div class="tenant-header">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <button class="btn-outline" onclick="closeTenantDetails()" style="border-radius: 12px; width: 44px; height: 44px; padding: 0; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; border-color: rgba(255,255,255,0.1);">🔙</button>
                        <div>
                            <h2 id="detail-company-title" style="margin:0; color:#fff; display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.6rem;">تفاصيل الشركة</h2>
                            <small id="detail-company-subdomain" style="color:var(--primary-accent); font-weight: 600; letter-spacing: 0.5px; font-size: 1rem;"></small>
                        </div>
                    </div>
                    <div id="detail-actions-bar" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <!-- Actions injected via JS -->
                    </div>
                </div>
                
                <input type="hidden" id="users-tenant-select" value="">
                
                <!-- Info Cards Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 25px;" id="detail-info-cards">
                    <!-- Populated via JS -->
                </div>

                <!-- Admin Notes -->
                <div class="admin-notes-card">
                    <div class="flex-between" style="margin-bottom: 12px;">
                        <h4 style="margin:0; color:var(--primary-accent); display:flex; align-items:center; gap:8px;">📝 ملاحظات الإدارة (سرية)</h4>
                        <button class="btn-outline" style="padding: 6px 14px; font-size: 0.85rem; border-color: var(--primary-accent); color: var(--primary-accent);" onclick="saveAdminNotes()">حفظ الملاحظات</button>
                    </div>
                    <textarea id="tenant-admin-notes" placeholder="اكتب ملاحظاتك الخاصة هنا (مثال: تم الاتفاق على تأجيل الدفعة)..." style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; padding: 12px; resize: vertical; min-height: 80px; font-family: 'Tajawal', sans-serif; font-size: 0.95rem;"></textarea>
                </div>

                <!-- Split View: Users and Payments -->
                <div class="split-view-grid">
                    <!-- Users Column -->
                    <div class="glass-panel" style="padding: 20px;">
                        <div class="flex-between" style="margin-bottom: 20px;">
                            <h3 style="margin-top:0; display:flex; align-items:center; gap:8px; font-size:1.2rem;">👥 مستخدمو الشركة</h3>
                            <button class="btn-primary" onclick="openAddUserModal()" style="font-size: 0.85rem; padding: 6px 12px;">+ مستخدم جديد</button>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>البريد/المستخدم</th>
                                        <th>الدور</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="tenant-users-body">
                                    <tr><td colspan="4" style="text-align:center;">جاري التحميل...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Payments Column -->
                    <div class="glass-panel" style="padding: 20px;">
                        <div class="flex-between" style="margin-bottom: 20px;">
                            <h3 style="margin-top:0; display:flex; align-items:center; gap:8px; font-size:1.2rem;">💳 الفواتير والمدفوعات</h3>
                            <button class="btn-outline" style="border-color:var(--primary-accent); color:var(--primary-accent); font-size: 0.85rem; padding: 6px 12px;" onclick="openEditModal(document.getElementById('users-tenant-select').value)">إضافة دفعة</button>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>المبلغ</th>
                                        <th>البيان</th>
                                    </tr>
                                </thead>
                                <tbody id="tenant-payments-body">
                                    <tr><td colspan="3" style="text-align:center;">لا يوجد سجل مدفوعات.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: GLOBAL PAYMENTS -->
        <div id="tab-global-payments" class="tab-content">
            <div class="section-inner-tabs" data-group="financial">
                <div class="section-inner-tab active" onclick="switchSectionTab('tab-global-payments', 'financial', this)">&#x1F4B3; سجل المدفوعات والفواتير</div>
                <div class="section-inner-tab" onclick="switchSectionTab('tab-plans', 'financial', this)">&#x1F48E; الباقات (Plans)</div>
            </div>

            <!-- Financial KPIs for Payments -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div class="kpi-card" style="background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(5,150,105,0.2) 100%); border-color: rgba(16,185,129,0.3);">
                    <div class="kpi-icon" style="background: rgba(16,185,129,0.2); color: #34d399;">&#x1F4B0;</div>
                    <div class="kpi-info">
                        <div class="kpi-val" id="gpayments-total-rev">$0</div>
                        <div class="kpi-desc" style="color:#a7f3d0;">إجمالي الإيرادات</div>
                    </div>
                </div>
                <div class="kpi-card" style="background: linear-gradient(135deg, rgba(56,189,248,0.1) 0%, rgba(2,132,199,0.2) 100%); border-color: rgba(56,189,248,0.3);">
                    <div class="kpi-icon" style="background: rgba(56,189,248,0.2); color: #7dd3fc;">&#x1F4CA;</div>
                    <div class="kpi-info">
                        <div class="kpi-val" id="gpayments-count">0</div>
                        <div class="kpi-desc" style="color:#bae6fd;">إجمالي عدد العمليات</div>
                    </div>
                </div>
                <div class="kpi-card" style="background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(217,119,6,0.2) 100%); border-color: rgba(245,158,11,0.3);">
                    <div class="kpi-icon" style="background: rgba(245,158,11,0.2); color: #fcd34d;">&#x23F1;</div>
                    <div class="kpi-info">
                        <div class="kpi-val" id="gpayments-recent">$0</div>
                        <div class="kpi-desc" style="color:#fef3c7;">إيرادات آخر 30 يوم</div>
                    </div>
                </div>
            </div>

            <div class="glass-panel" style="padding: 25px; border-radius: 16px;">
                <div class="flex-between" style="margin-bottom: 25px; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                    <div>
                        <h3 style="margin:0; font-size: 1.25rem; font-weight: 800; color: #fff;">سجل المدفوعات المجمّع</h3>
                        <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #94a3b8;">يعرض جميع عمليات الدفع وتجديد الاشتراكات لكل الشركات المسجلة</p>
                    </div>
                    <div style="display:flex; gap:12px; align-items: center;">
                        <div style="position: relative;">
                            <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #64748b;">🔍</span>
                            <input type="text" id="global-payments-search" placeholder="بحث بالشركة أو البيان..." style="padding: 10px 35px 10px 15px; border-radius: 10px; background: rgba(15,23,42,0.6); color:#f8fafc; border: 1px solid rgba(255,255,255,0.1); width: 320px; font-size: 0.9rem; transition: all 0.3s;" onkeyup="filterGlobalPayments()" onfocus="this.style.borderColor='var(--primary-accent)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                    <table style="margin: 0;">
                        <thead style="background: rgba(15,23,42,0.8);">
                            <tr>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05);">تاريخ الدفع</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05);">الشركة المشتركة</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05);">النطاق (Subdomain)</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05);">المبلغ المالي</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05);">البيان / الملاحظات</th>
                            </tr>
                        </thead>
                        <tbody id="global-payments-body">
                            <tr><td colspan="5" style="text-align:center; padding: 30px; color: #94a3b8;"><i class="fa-solid fa-circle-notch fa-spin"></i> جاري جلب البيانات...</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="flex-between" style="margin-top:25px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05);">
                    <button class="btn-primary" id="btn-gpayments-prev" onclick="changeGlobalPaymentsPage(-1)" style="padding: 8px 20px; border-radius: 8px; background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1);">السابق</button>
                    <span id="gpayments-page-indicator" style="color: #94a3b8; font-size: 0.9rem; font-weight: 600; background: rgba(0,0,0,0.2); padding: 6px 15px; border-radius: 20px;">الصفحة 1 من 1</span>
                    <button class="btn-primary" id="btn-gpayments-next" onclick="changeGlobalPaymentsPage(1)" style="padding: 8px 20px; border-radius: 8px; background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1);">التالي</button>
                </div>
            </div>
        </div>

        <!-- TAB: PLANS -->
        <div id="tab-plans" class="tab-content">
            <div class="section-inner-tabs" data-group="financial">
                <div class="section-inner-tab" onclick="switchSectionTab('tab-global-payments', 'financial', this)">&#x1F4B3; سجل المدفوعات والفواتير</div>
                <div class="section-inner-tab active" onclick="switchSectionTab('tab-plans', 'financial', this)">&#x1F48E; الباقات (Plans)</div>
            </div>
            
            <div class="glass-panel" style="padding: 25px; border-radius: 16px;">
                <div class="flex-between" style="margin-bottom: 25px; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                    <div>
                        <h3 style="margin:0; font-size: 1.25rem; font-weight: 800; color: #fff;">باقات الاشتراك (Plans)</h3>
                        <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #94a3b8;">إدارة باقات النظام وأسعارها وخصائص كل باقة</p>
                    </div>
                    <button class="btn-primary" onclick="openPlanModal()" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-plus"></i> إضافة باقة جديدة
                    </button>
                </div>
                
                <div class="table-responsive" style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                    <table style="margin: 0;">
                        <thead style="background: rgba(15,23,42,0.8);">
                            <tr>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); width: 25%;">اسم الباقة</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">السعر / المدة</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">الخصائص</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="plans-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <!-- TAB: PARTNERS -->
        <div id="tab-partners" class="tab-content">
            <div class="section-inner-tabs" data-group="marketing">
                <div class="section-inner-tab" onclick="switchSectionTab('tab-ads', 'marketing', this)">&#x1F4E2; الإعلانات المدفوعة</div>
                <div class="section-inner-tab active" onclick="switchSectionTab('tab-partners', 'marketing', this)">&#x1F91D; الشركاء والعملاء</div>
            </div>
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <h3 style="margin: 0;">الشركاء والعملاء</h3>
                        <label class="switch-container" style="display: flex; align-items: center; gap: 8px; cursor: pointer; background: rgba(255,255,255,0.05); padding: 6px 12px; border-radius: 20px; border: 1px solid var(--glass-border);">
                            <input type="checkbox" id="saas-partners-enabled" onchange="togglePartnersFeature(this.checked)" style="cursor: pointer;">
                            <span style="font-size: 0.9rem; color: #fff; font-weight: bold;">تفعيل الشريط في الصفحة الرئيسية</span>
                        </label>
                    </div>
                    <button class="btn-primary" onclick="openPartnerModal()">+ شريك جديد</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>اسم الشريك</th>
                                <th>الشعار</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="partners-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: BROADCASTS -->
        <!-- TAB: BROADCASTS -->
        <div id="tab-broadcasts" class="tab-content">
            <!-- Big Toggle Buttons -->
            <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div id="btn-bc-whatsapp" onclick="toggleBroadcastView('whatsapp')" style="flex: 1; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; color: #10b981;">
                    <i class="fa-brands fa-whatsapp" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    <span style="font-size: 1.1rem; font-weight: 800;">رسائل الواتساب</span>
                </div>
                <div id="btn-bc-system" onclick="toggleBroadcastView('system')" style="flex: 1; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; color: #cbd5e1;">
                    <i class="fa-solid fa-bell" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    <span style="font-size: 1.1rem; font-weight: 800;">رسائل النظام (لوحة التحكم)</span>
                </div>
            </div>

            <!-- Panel 1: WhatsApp Broadcasting -->
            <div id="view-bc-whatsapp" class="glass-panel" style="margin-bottom: 25px; padding: 30px;">
                <h3 style="margin-top:0; margin-bottom: 15px; display:flex; align-items:center; gap:12px; font-size: 1.5rem; font-weight: 800; color: #10b981;">
                    <i class="fa-brands fa-whatsapp"></i> إرسال رسائل واتساب للمشتركين (WhatsApp Broadcast)
                </h3>
                <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.6; margin-bottom: 30px; max-width: 800px;">
                    يمكنك إرسال رسالة واتساب فورية إما لمشترك محدد منفرد أو لجميع المشتركين معاً (رسالة جماعية) للتنبيه بانتهاء الاشتراك أو إرسال تحديثات هامة.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-bottom: 25px;">
                    <div class="form-group" style="margin: 0;">
                        <label style="color:#e2e8f0; font-weight:700; font-size: 1.05rem; margin-bottom: 10px; display: block;">المستلم (الشركة/المشترك)</label>
                        <select id="wa-target-tenant" onchange="applyWaTemplate(document.getElementById('wa-message-template').value)" style="padding: 12px 15px; border-radius: 10px; background: rgba(0,0,0,0.4); color:#fff; border: 1px solid rgba(255,255,255,0.1); width: 100%; font-family: Tajawal; font-size: 1.05rem; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.2)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none'">
                            <option value="all" style="background: #1e293b; color: #10b981; font-weight: bold;">📢 إرسال لجميع المشتركين (رسالة جماعية)</option>
                            <!-- Will be populated dynamically with tenants -->
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label style="color:#e2e8f0; font-weight:700; font-size: 1.05rem; margin-bottom: 10px; display: block;">قالب الرسائل الجاهزة (اختياري)</label>
                        <select id="wa-message-template" onchange="applyWaTemplate(this.value)" style="padding: 12px 15px; border-radius: 10px; background: rgba(0,0,0,0.4); color:#fff; border: 1px solid rgba(255,255,255,0.1); width: 100%; font-family: Tajawal; font-size: 1.05rem; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.2)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none'">
                            <option value="" style="background: #1e293b; color: #fff;">-- اختر قالباً جاهزاً لتعبئة الرسالة --</option>
                            <option value="expiry" style="background: #1e293b; color: #fff;">⚠️ تنبيه بقرب انتهاء الاشتراك</option>
                            <option value="welcome" style="background: #1e293b; color: #fff;">🌸 ترحيب بمشترك جديد</option>
                            <option value="maintenance" style="background: #1e293b; color: #fff;">⚙️ تنبيه صيانة مجدولة للنظام</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="color:#e2e8f0; font-weight:700; font-size: 1.05rem; margin-bottom: 10px; display: block;">نص الرسالة</label>
                    <textarea id="wa-message-body" rows="6" placeholder="اكتب نص الرسالة هنا... يمكنك استخدام المتغيرات {owner} للاسم و {company} للشركة في الرسائل الجماعية" style="padding: 15px; border-radius: 10px; background: rgba(0,0,0,0.4); color:#fff; border: 1px solid rgba(255,255,255,0.1); width: 100%; font-family: Tajawal; font-size: 1.1rem; line-height: 1.6; resize: vertical; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.2)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none'"></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end;">
                    <button class="btn-primary" onclick="sendWhatsAppMessage()" style="padding: 15px 40px; font-weight: 800; font-size: 1.15rem; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); border: none; box-shadow: 0 4px 15px rgba(16,185,129,0.4); display: inline-flex; align-items: center; gap: 10px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(16,185,129,0.6)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(16,185,129,0.4)'">
                        <i class="fa-solid fa-paper-plane"></i> إرسال الرسالة عبر واتساب
                    </button>
                </div>
            </div>

            <!-- Panel 2: Dashboard System Messages -->
            <div id="view-bc-system" class="glass-panel" style="display: none; padding: 30px;">
                <div class="flex-between" style="margin-bottom: 25px; align-items: center;">
                    <h3 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: #38bdf8; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-bell"></i> إدارة رسائل النظام (إشعارات لوحة التحكم)
                    </h3>
                    <button class="btn-primary" onclick="openMessageModal()" style="padding: 12px 25px; font-weight: 700; border-radius: 10px; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #38bdf8, #0284c7); border: none; box-shadow: 0 4px 15px rgba(56,189,248,0.3); cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                        <i class="fa-solid fa-plus"></i> إنشاء رسالة جديدة
                    </button>
                </div>
                <div class="table-responsive" style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                    <table style="margin: 0; width: 100%; border-collapse: collapse; text-align: right;">
                        <thead style="background: rgba(15,23,42,0.8);">
                            <tr>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05);">العنوان و المحتوى</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05);">الجمهور المستهدف</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">نوع الرسالة (اللون)</th>
                                <th style="padding: 15px; font-weight: 700; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="messages-body">
                            <tr><td colspan="4" style="text-align:center; padding: 30px; color: #94a3b8;">جاري التحميل...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <!-- TAB: ADS -->
        <div id="tab-ads" class="tab-content">
            <div class="section-inner-tabs" data-group="marketing">
                <div class="section-inner-tab active" onclick="switchSectionTab('tab-ads', 'marketing', this)">📢 الإعلانات المدفوعة</div>
                <div class="section-inner-tab" onclick="switchSectionTab('tab-partners', 'marketing', this)">🤝 الشركاء والعملاء</div>
            </div>

            <!-- Summary KPI Cards for Ads -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div class="glass-panel" style="padding: 16px; border-left: 4px solid #38bdf8;">
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: bold; margin-bottom: 6px;">📊 إجمالي الإعلانات</div>
                    <div id="ads-stat-total" style="font-size: 1.6rem; font-weight: 800; color: #fff;">0</div>
                </div>
                <div class="glass-panel" style="padding: 16px; border-left: 4px solid #10b981;">
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: bold; margin-bottom: 6px;">🟢 إعلانات سارية ونشطة</div>
                    <div id="ads-stat-active" style="font-size: 1.6rem; font-weight: 800; color: #10b981;">0</div>
                </div>
                <div class="glass-panel" style="padding: 16px; border-left: 4px solid #f59e0b;">
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: bold; margin-bottom: 6px;">⏳ تنتهي قريباً (أقل من 7 أيام)</div>
                    <div id="ads-stat-expiring" style="font-size: 1.6rem; font-weight: 800; color: #f59e0b;">0</div>
                </div>
                <div class="glass-panel" style="padding: 16px; border-left: 4px solid #ef4444;">
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: bold; margin-bottom: 6px;">🔴 إعلانات منتهية الصلاحية</div>
                    <div id="ads-stat-expired" style="font-size: 1.6rem; font-weight: 800; color: #ef4444;">0</div>
                </div>
            </div>

            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.3rem;">إدارة الإعلانات وتتبع الصلاحية</h3>
                        <small style="color: var(--text-secondary);">متابعة وتحديد فترات ظهور إعلانات الشركات في الصفحة الرئيسية للمشتركين</small>
                    </div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <input type="text" id="ads-search-input" placeholder="بحث بالشركة أو الملاحظات..." oninput="renderAds()" style="padding: 8px 14px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); width: 220px; outline: none;">
                        <select id="ads-filter-status" onchange="renderAds()" style="padding: 8px 12px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); outline: none;">
                            <option value="all">كل الحالات</option>
                            <option value="active">🟢 سارية ونشطة فقط</option>
                            <option value="expiring">⏳ تنتهي قريباً</option>
                            <option value="expired">🔴 منتهية الصلاحية</option>
                            <option value="inactive">⏸️ موقوفة يدوياً</option>
                        </select>
                        <button class="btn-primary" onclick="openAdModal()" style="display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-plus"></i> إضافة إعلان جديد
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 140px;">معاينة الإعلان</th>
                                <th>الشركة المعلنة والرابط</th>
                                <th>تاريخ البدء</th>
                                <th>تاريخ الانتهاء</th>
                                <th>المدة المتبقية / الحالة</th>
                                <th style="text-align: center;">العرض</th>
                                <th style="text-align: center;">إجراءات وتمديد</th>
                            </tr>
                        </thead>
                        <tbody id="ads-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: SETTINGS -->
        <div id="tab-settings" class="tab-content fade-in">
    <div class="section-inner-tabs" data-group="settings">
        <div class="section-inner-tab active" onclick="switchSectionTab('tab-settings', 'settings', this)">💱 إعدادات الصرف</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-activation', 'settings', this)">⏱️ إعدادات التفعيل</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-logout', 'settings', this)">🔒 الخروج للأمان</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-master-pin', 'settings', this)">🔑 المشرف العام</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-backdoor', 'settings', this)">🕵️‍♂️ الدعم الفني</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-recovery', 'settings', this)">🛠️ مفتاح الاستعادة</div>
    </div>
    <div class="glass-panel" style="padding: 30px;">
        <h3 style="color:var(--warning-color); margin-top:0; margin-bottom: 25px; display:flex; align-items:center; gap:8px;">💱 إعدادات الصرف والتسعير</h3>
        
        <div class="form-group" style="margin-bottom: 25px;">
            <label style="color: #cbd5e1; font-weight: bold; margin-bottom: 10px; display: block;">طريقة التسعير (Pricing Method)</label>
            <select id="global-pricing-method" style="width: 100%; padding: 14px; border-radius: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); color: #fff; font-size: 1.05rem; outline: none;">
                <option value="central_bank">الاعتماد على السعر المركزي (البنك المركزي)</option>
                <option value="black_market">السوق الموازي (الأسود)</option>
                <option value="custom">تخصيص يدوي</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: end; margin-bottom: 25px;">
            
            <!-- USD -->
            <div class="form-group" style="margin: 0; background: rgba(251, 191, 36, 0.1); padding: 15px; border-radius: 12px; border: 2px solid rgba(251, 191, 36, 0.4);">
                <label style="color: #fbbf24; font-weight: bold; font-size: 1.1rem; margin-bottom: 10px; display: block; text-align: center;">💵 الدولار الأمريكي (USD)</label>
                <input type="number" id="global-exchange-rate" step="0.01" oninput="calculateCurrencies()" placeholder="أدخل سعر الدولار" style="width: 100%; padding: 15px; border-radius: 10px; background: rgba(0,0,0,0.6); border: 2px solid #fbbf24; color: #fbbf24; font-size: 2rem; font-weight: 900; text-align: center; outline: none; box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);">
            </div>

            <!-- SAR -->
            <div class="form-group" style="margin: 0; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                <label style="color: #e2e8f0; font-weight: bold; font-size: 1rem; margin-bottom: 10px; display: block; text-align: center;">🇸🇦 الريال السعودي (SAR)</label>
                <input type="number" id="global-exchange-sar" step="0.01" style="width: 100%; padding: 15px; border-radius: 10px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2); color: #fff; font-size: 1.5rem; font-weight: bold; text-align: center;">
            </div>

            <!-- AED -->
            <div class="form-group" style="margin: 0; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                <label style="color: #e2e8f0; font-weight: bold; font-size: 1rem; margin-bottom: 10px; display: block; text-align: center;">🇦🇪 الدرهم الإماراتي (AED)</label>
                <input type="number" id="global-exchange-aed" step="0.01" style="width: 100%; padding: 15px; border-radius: 10px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2); color: #fff; font-size: 1.5rem; font-weight: bold; text-align: center;">
            </div>

        </div>
        
        <script>
            function calculateCurrencies() {
                let usdRate = parseFloat(document.getElementById('global-exchange-rate').value) || 0;
                if(usdRate > 0) {
                    document.getElementById('global-exchange-sar').value = (usdRate / 3.75).toFixed(2);
                    document.getElementById('global-exchange-aed').value = (usdRate / 3.67).toFixed(2);
                } else {
                    document.getElementById('global-exchange-sar').value = '';
                    document.getElementById('global-exchange-aed').value = '';
                }
            }
        </script>
        
        <div class="setting-row" style="background: rgba(255,255,255,0.03); padding: 18px 25px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.05); margin-top: 25px;">
            <span class="form-label" style="margin:0; font-size: 1.05rem; color: #fff; font-weight: bold;">👁️ إظهار الأسعار للمشتركين</span>
            <label class="switch"><input type="checkbox" id="global-exchange-visible"><span class="slider"></span></label>
        </div>
        
        <button class="btn-primary" onclick="saveGlobalSettings()" style="width:100%; margin-top: 25px; padding: 16px; font-size: 1.1rem; border-radius: 14px;">💾 حفظ إعدادات الصرف والتسعير</button>
    </div>
</div>

<div id="settings-activation" class="tab-content fade-in">
    <div class="section-inner-tabs" data-group="settings">
        <div class="section-inner-tab" onclick="switchSectionTab('tab-settings', 'settings', this)">💱 إعدادات الصرف</div>
        <div class="section-inner-tab active" onclick="switchSectionTab('settings-activation', 'settings', this)">⏱️ إعدادات التفعيل</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-logout', 'settings', this)">🔒 الخروج للأمان</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-master-pin', 'settings', this)">🔑 المشرف العام</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-backdoor', 'settings', this)">🕵️‍♂️ الدعم الفني</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-recovery', 'settings', this)">🛠️ مفتاح الاستعادة</div>
    </div>
    <div class="glass-panel" style="padding: 30px;">
        <h3 style="color:#3b82f6; margin-top:0; display:flex; align-items:center; gap:8px;">⏱️ إعدادات التفعيل</h3>
        <div class="form-group">
            <label>زمن التفعيل المتوقع (بالساعات)</label>
            <input type="number" id="global-activation-delay" value="12" style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
            <div style="font-size: 0.95em; color: #94a3b8; margin-top: 12px; line-height: 1.6; background: rgba(59, 130, 246, 0.1); padding: 15px; border-radius: 10px; border-left: 3px solid #3b82f6;">
                <strong>💡 ماذا يعني هذا الرقم؟</strong><br>
                عندما يسجل مستخدم جديد شركته، تكون حالته "قيد المراجعة". هذا الرقم سيظهر للمستخدم في رسالة الانتظار كالتالي: <br>
                <span style="color:#fbbf24; margin-top: 5px; display: inline-block;">"طلبك قيد المراجعة. سيتم تفعيل حسابك خلال [الرقم الذي تدخله هنا] ساعة كحد أقصى."</span>
            </div>
        </div>
        <button class="btn-primary" onclick="saveGlobalSettings()" style="width:100%; margin-top: 20px; padding: 16px; font-size: 1.1rem; border-radius: 14px;">💾 حفظ زمن التفعيل</button>
    </div>
</div>

<div id="settings-logout" class="tab-content fade-in">
    <div class="section-inner-tabs" data-group="settings">
        <div class="section-inner-tab" onclick="switchSectionTab('tab-settings', 'settings', this)">💱 إعدادات الصرف</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-activation', 'settings', this)">⏱️ إعدادات التفعيل</div>
        <div class="section-inner-tab active" onclick="switchSectionTab('settings-logout', 'settings', this)">🔒 الخروج للأمان</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-master-pin', 'settings', this)">🔑 المشرف العام</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-backdoor', 'settings', this)">🕵️‍♂️ الدعم الفني</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-recovery', 'settings', this)">🛠️ مفتاح الاستعادة</div>
    </div>
    <div class="glass-panel" style="padding: 30px;">
        <h3 style="color:#10b981; margin-top:0; display:flex; align-items:center; gap:8px;">🔒 الخروج التلقائي للأمان</h3>
        <div class="form-group">
            <label>مدة عدم النشاط قبل الخروج</label>
            <div style="display:flex; gap:15px;">
                <input type="number" id="global-auto-logout-value" value="30" style="flex:2; padding: 12px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                <select id="global-auto-logout-unit" style="flex:1; padding: 12px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                    <option value="minutes">دقائق</option>
                    <option value="seconds">ثواني</option>
                </select>
            </div>
            <div style="font-size: 0.95em; color: #94a3b8; margin-top: 12px; background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 10px; border-left: 3px solid #10b981;">
                سيتم تسجيل الخروج تلقائياً من لوحة التحكم هذه (لوحة SaaS) إذا لم تقم بأي نشاط لمدة التحديد (اكتب 0 لإلغاء الميزة).
            </div>
        </div>
        <button class="btn-primary" onclick="saveGlobalSettings()" style="width:100%; margin-top: 20px; padding: 16px; font-size: 1.1rem; border-radius: 14px;">💾 حفظ إعدادات الأمان</button>
    </div>
</div>

<div id="settings-master-pin" class="tab-content fade-in">
    <div class="section-inner-tabs" data-group="settings">
        <div class="section-inner-tab" onclick="switchSectionTab('tab-settings', 'settings', this)">💱 إعدادات الصرف</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-activation', 'settings', this)">⏱️ إعدادات التفعيل</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-logout', 'settings', this)">🔒 الخروج للأمان</div>
        <div class="section-inner-tab active" onclick="switchSectionTab('settings-master-pin', 'settings', this)">🔑 المشرف العام</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-backdoor', 'settings', this)">🕵️‍♂️ الدعم الفني</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-recovery', 'settings', this)">🛠️ مفتاح الاستعادة</div>
    </div>
    <div class="glass-panel" style="padding: 30px;">
        <h3 style="color:var(--danger-color); margin-top:0; display:flex; align-items:center; gap:8px;">🔑 رمز المشرف العام</h3>
        <div style="font-size: 0.95em; color: #94a3b8; margin-bottom: 25px;">هذا الرمز يُستخدم للدخول إلى لوحة التحكم الحالية (إدارة الشركات SaaS). يرجى المحافظة عليه آمناً.</div>
        <div class="form-group">
            <label>الرمز الحالي</label>
            <div style="position: relative;">
                <input type="password" id="old-master-token" style="padding: 12px; padding-left: 45px; width: 100%; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; box-sizing: border-box;">
                <span onclick="togglePasswordVisibility('old-master-token', this)" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #a1a1aa; font-size: 1.2em; user-select: none;">👁️</span>
            </div>
        </div>
        <div class="form-group">
            <label>الرمز الجديد</label>
            <div style="position: relative;">
                <input type="password" id="new-master-token" style="padding: 12px; padding-left: 45px; width: 100%; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; box-sizing: border-box;">
                <span onclick="togglePasswordVisibility('new-master-token', this)" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #a1a1aa; font-size: 1.2em; user-select: none;">👁️</span>
            </div>
        </div>
        <button class="btn-danger" onclick="saveMasterToken()" style="width:100%; margin-top: 15px; padding: 16px; font-size: 1.1rem; border-radius: 14px;">🔄 تغيير الرمز</button>
    </div>
</div>

<div id="settings-backdoor" class="tab-content fade-in">
    <div class="section-inner-tabs" data-group="settings">
        <div class="section-inner-tab" onclick="switchSectionTab('tab-settings', 'settings', this)">💱 إعدادات الصرف</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-activation', 'settings', this)">⏱️ إعدادات التفعيل</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-logout', 'settings', this)">🔒 الخروج للأمان</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-master-pin', 'settings', this)">🔑 المشرف العام</div>
        <div class="section-inner-tab active" onclick="switchSectionTab('settings-backdoor', 'settings', this)">🕵️‍♂️ الدعم الفني</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-recovery', 'settings', this)">🛠️ مفتاح الاستعادة</div>
    </div>
    <div class="glass-panel" style="padding: 30px;">
        <h3 style="color:#a855f7; margin-top:0; display:flex; align-items:center; gap:8px;">🕵️‍♂️ حساب الدعم الفني (Backdoor)</h3>
        <div style="font-size: 0.95em; color: #94a3b8; margin-bottom: 25px; background: rgba(168, 85, 247, 0.1); padding: 15px; border-radius: 10px; border-left: 3px solid #a855f7; line-height: 1.6;">
            حساب خفي (Super Admin) يتيح لك الدخول لأي شركة مسجلة في النظام بصلاحيات مدير لحل مشاكلهم، دون أن يظهر اسمك في قائمة مستخدمي الشركة.
        </div>
        <div class="form-group">
            <label>اسم المستخدم السري</label>
            <input type="text" id="backdoor-username" placeholder="مثال: MasterSupport" style="padding: 12px; width: 100%; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; box-sizing: border-box;">
        </div>
        <div class="form-group">
            <label>كلمة المرور السرية</label>
            <div style="position: relative;">
                <input type="password" id="backdoor-password" placeholder="أدخل كلمة مرور معقدة" style="padding: 12px; padding-left: 45px; width: 100%; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; box-sizing: border-box;">
                <span onclick="togglePasswordVisibility('backdoor-password', this)" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #a1a1aa; font-size: 1.2em; user-select: none;">👁️</span>
            </div>
        </div>
        <button class="btn-primary" onclick="saveBackdoorCredentials()" style="width:100%; margin-top: 15px; background:#a855f7; border-color:#a855f7; padding: 16px; font-size: 1.1rem; border-radius: 14px;">✨ حفظ وتفعيل حساب الدعم الفني</button>
    </div>
</div>

<div id="settings-recovery" class="tab-content fade-in">
    <div class="section-inner-tabs" data-group="settings">
        <div class="section-inner-tab" onclick="switchSectionTab('tab-settings', 'settings', this)">💱 إعدادات الصرف</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-activation', 'settings', this)">⏱️ إعدادات التفعيل</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-logout', 'settings', this)">🔒 الخروج للأمان</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-master-pin', 'settings', this)">🔑 المشرف العام</div>
        <div class="section-inner-tab" onclick="switchSectionTab('settings-backdoor', 'settings', this)">🕵️‍♂️ الدعم الفني</div>
        <div class="section-inner-tab active" onclick="switchSectionTab('settings-recovery', 'settings', this)">🛠️ مفتاح الاستعادة</div>
    </div>
    <div class="glass-panel" style="padding: 30px;">
        <h3 style="color:#0ea5e9; margin-top:0; display:flex; align-items:center; gap:8px;">🛠️ مُولد مفتاح استعادة النسخ</h3>
        <div style="font-size: 0.95em; color: #94a3b8; margin-bottom: 25px; background: rgba(14, 165, 233, 0.1); padding: 15px; border-radius: 10px; border-left: 3px solid #0ea5e9; line-height: 1.6;">
            يستخدم لتوليد رقم سري ديناميكي مخصص لفك تشفير نسخة احتياطية لعميل نسي رقمه السري.
        </div>
        <div class="form-group">
            <label>الرمز المرجعي للملف (refCode)</label>
            <input type="text" id="recovery-ref-code" placeholder="مثال: REF-A1B2C3" style="text-transform: uppercase; padding: 12px; width: 100%; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; box-sizing: border-box;">
        </div>
        <div class="form-group">
            <label>التوكن السري المستخرج</label>
            <input type="text" id="recovery-generated-token" readonly style="background: rgba(0,0,0,0.5); color: #0ea5e9; font-weight: bold; font-family: monospace; text-align: center; letter-spacing: 5px; font-size: 1.4rem; padding: 15px; border: 1px dashed #0ea5e9; border-radius: 10px; cursor: copy; width: 100%; box-sizing: border-box;" onclick="if(this.value) { navigator.clipboard.writeText(this.value); alert('تم نسخ التوكن!'); }" placeholder="------">
            <div style="font-size: 0.8rem; color: #64748b; text-align: center; margin-top: 8px;">انقر على التوكن لنسخه مباشرة</div>
        </div>
        <button class="btn-primary" onclick="generateDynamicRecoveryToken()" style="width:100%; margin-top: 15px; background:#0ea5e9; border-color:#0ea5e9; padding: 16px; font-size: 1.1rem; border-radius: 14px;">⚙️ توليد التوكن الآن</button>
    </div>
</div>
<!-- Health Tab -->
    <div id="tab-health" class="tab-content fade-in">
        <div class="section-inner-tabs" data-group="monitoring">
            <div class="section-inner-tab active" onclick="switchSectionTab('tab-health', 'monitoring', this)">&#x1F5A5;&#xFE0F; حالة الخادم</div>
            <div class="section-inner-tab" onclick="switchSectionTab('tab-security', 'monitoring', this)">&#x1F6E1;&#xFE0F; الأمان</div>
            <div class="section-inner-tab" onclick="switchSectionTab('tab-audit', 'monitoring', this)">&#x1F4CB; السجل</div>
            <div class="section-inner-tab" onclick="switchSectionTab('tab-tickets', 'monitoring', this)">&#x1F3AB; التذاكر</div>
        </div>
        <!-- Overall Status Badge -->
        <div id="health-status-badge" style="margin-bottom: 20px; padding: 16px 24px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; gap: 15px; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); transition: all 0.5s ease;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="health-status-dot" style="width: 12px; height: 12px; border-radius: 50%; background: #10b981; animation: pulse-dot 2s infinite;"></div>
                <span id="health-status-text" style="font-weight: 700; font-size: 1.05rem; color: #10b981;">جاري فحص الخادم...</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #64748b;">
                <i class="fa-regular fa-clock"></i>
                <span id="health-last-update">--</span>
            </div>
        </div>
        <style>
            @keyframes pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.8); } }
        </style>

        <div class="header-actions" style="margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-server text-indigo-400"></i> حالة الخادم والموارد
            </h2>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.85rem; color: #94a3b8;">
                    <input type="checkbox" id="health-auto-refresh" checked onchange="toggleHealthAutoRefresh()" style="accent-color: #10b981; width: 16px; height: 16px; cursor: pointer;">
                    تحديث تلقائي
                </label>
                <button class="btn-primary" onclick="loadServerHealth()" style="background: rgba(56,189,248,0.1); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); padding: 10px 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; border-radius: 10px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                    <i class="fa-solid fa-rotate-right"></i> تحديث مباشر
                </button>
            </div>
        </div>

        <!-- Main Resources -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <!-- CPU -->
            <div class="glass-panel" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span style="font-size: 1.1rem; font-weight: 700; color: #cbd5e1;"><i class="fa-solid fa-microchip text-sky-400"></i> المعالج (CPU)</span>
                    <span id="health-cpu" style="font-size: 1.5rem; font-weight: 800; color: #fff;">0%</span>
                </div>
                <div style="background: rgba(0,0,0,0.3); height: 12px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                    <div id="health-cpu-bar" style="height: 100%; width: 0%; background: #10b981; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1), background 0.5s;"></div>
                </div>
            </div>

            <!-- RAM -->
            <div class="glass-panel" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span style="font-size: 1.1rem; font-weight: 700; color: #cbd5e1;"><i class="fa-solid fa-memory text-amber-400"></i> الذاكرة (RAM)</span>
                    <span id="health-ram" style="font-size: 1.5rem; font-weight: 800; color: #fff;">0%</span>
                </div>
                <div style="background: rgba(0,0,0,0.3); height: 12px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
                    <div id="health-ram-bar" style="height: 100%; width: 0%; background: #f59e0b; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1), background 0.5s;"></div>
                </div>
                <div style="text-align: left; font-size: 0.85rem; color: #94a3b8; font-family: monospace; direction: ltr;" id="health-ram-peak">-- / --</div>
            </div>

            <!-- Disk -->
            <div class="glass-panel" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span style="font-size: 1.1rem; font-weight: 700; color: #cbd5e1;"><i class="fa-solid fa-hard-drive text-emerald-400"></i> مساحة التخزين</span>
                    <span id="health-disk" style="font-size: 1.5rem; font-weight: 800; color: #fff;">0%</span>
                </div>
                <div style="background: rgba(0,0,0,0.3); height: 12px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
                    <div id="health-disk-bar" style="height: 100%; width: 0%; background: #10b981; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1), background 0.5s;"></div>
                </div>
                <div style="text-align: left; font-size: 0.85rem; color: #94a3b8; font-family: monospace; direction: ltr;" id="health-disk-details">-- / --</div>
            </div>
        </div>

        <!-- Secondary Metrics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div class="glass-panel" style="padding: 20px; text-align: center; border-top: 3px solid #8b5cf6;">
                <div style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 10px;">حجم قاعدة البيانات</div>
                <div id="health-db" style="font-size: 2rem; font-weight: 800; color: #f8fafc; font-family: monospace;">--</div>
                <div id="health-db-tables" style="font-size: 0.8rem; color: #64748b; margin-top: 6px;"></div>
            </div>
            
            <div class="glass-panel" style="padding: 20px; text-align: center; border-top: 3px solid #38bdf8;">
                <div style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 10px;">اتصالات قاعدة البيانات</div>
                <div id="health-connections" style="font-size: 2rem; font-weight: 800; color: #f8fafc; font-family: monospace;">--</div>
            </div>
            
            <div class="glass-panel" style="padding: 20px; text-align: center; border-top: 3px solid #10b981;">
                <div style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 10px;">مدة التشغيل (Uptime)</div>
                <div id="health-uptime" style="font-size: 1.3rem; font-weight: 800; color: #f8fafc; font-family: monospace; margin-top: 5px;">--</div>
            </div>
            
            <div class="glass-panel" style="padding: 20px; text-align: center; border-top: 3px solid #f43f5e;">
                <div style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 10px;">العمليات النشطة (Processes)</div>
                <div id="health-processes" style="font-size: 2rem; font-weight: 800; color: #f8fafc; font-family: monospace;">--</div>
            </div>
            
            <div class="glass-panel" style="padding: 20px; text-align: center; border-top: 3px solid #a855f7;">
                <div style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 10px;">الشبكة (تنزيل / رفع)</div>
                <div style="font-size: 1.3rem; font-weight: 800; color: #f8fafc; font-family: monospace; margin-top: 5px; direction: ltr;" id="health-network">-- / --</div>
            </div>

            <div class="glass-panel" style="padding: 20px; text-align: center; border-top: 3px solid #f59e0b;">
                <div style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 10px;">المستخدمون النشطون (الآن)</div>
                <div id="health-active-users" style="font-size: 2rem; font-weight: 800; color: #f8fafc; font-family: monospace;">--</div>
            </div>
        </div>
        <!-- Server Billing Info -->
        <div class="glass-panel" style="margin-bottom: 20px; padding: 25px; border-right: 4px solid #10b981;">
            <div class="flex-between" style="margin-bottom: 20px;">
                <h3 style="margin:0; font-size: 1.3rem; color: #f8fafc; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i> بيانات اشتراك السيرفر (Server Billing)
                </h3>
                <button class="btn-primary" onclick="openBillingModal();" style="background: rgba(255,255,255,0.05); color: #cbd5e1; padding: 8px 15px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                    <i class="fa-solid fa-pen"></i> تعديل
                </button>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03); display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(56,189,248,0.1); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-brands fa-digital-ocean"></i>
                    </div>
                    <div>
                        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 3px;">مزود الخدمة السحابية</div>
                        <div id="health-billing-provider" style="color: #e2e8f0; font-weight: 800; font-size: 1.1rem;">--</div>
                    </div>
                </div>

                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03); display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(16,185,129,0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 3px;">التكلفة الشهرية (الاستهلاك)</div>
                        <div style="color: #10b981; font-weight: 800; font-size: 1.1rem; font-family: monospace; direction: ltr;">$<span id="health-billing-cost">--</span> <span style="font-size:0.8rem; color:#64748b;">/ mo</span></div>
                    </div>
                </div>

                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03); display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(245,158,11,0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div>
                        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 3px;">تاريخ التجديد القادم</div>
                        <div id="health-billing-renewal" style="color: #e2e8f0; font-weight: 800; font-size: 1.1rem;">--</div>
                    </div>
                </div>

                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03); display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(139,92,246,0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div>
                        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 3px;">خطة السيرفر الحالية</div>
                        <div id="health-billing-plan" style="color: #e2e8f0; font-weight: 800; font-size: 1.1rem;">--</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Details -->
        <div class="glass-panel" style="padding: 25px;">
            <h3 style="margin-top:0; margin-bottom: 20px; font-size: 1.2rem; color: #e2e8f0;"><i class="fa-solid fa-circle-info text-indigo-400"></i> تفاصيل البيئة التشغيلية</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03);">
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 5px;">نظام التشغيل</div>
                    <div id="health-os" style="color: #e2e8f0; font-weight: 700; font-size: 1.1rem;">--</div>
                </div>
                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03);">
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 5px;">إصدار PHP</div>
                    <div id="health-php" style="color: #e2e8f0; font-weight: 700; font-size: 1.1rem;">--</div>
                </div>
                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03);">
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 5px;">إصدار MySQL</div>
                    <div id="health-mysql" style="color: #e2e8f0; font-weight: 700; font-size: 1.1rem;">--</div>
                </div>
                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03);">
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 5px;">آخر فحص للبيانات</div>
                    <div id="health-time" style="color: #e2e8f0; font-weight: 700; font-size: 1rem; direction: ltr; text-align: right; font-family: monospace;">--</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Tab -->
    <div id="tab-tickets" class="tab-content fade-in">
        <div class="section-inner-tabs" data-group="monitoring">
            <div class="section-inner-tab" onclick="switchSectionTab('tab-health', 'monitoring', this)">&#x1F5A5;&#xFE0F; حالة الخادم</div>
            <div class="section-inner-tab" onclick="switchSectionTab('tab-security', 'monitoring', this)">&#x1F6E1;&#xFE0F; الأمان</div>
            <div class="section-inner-tab" onclick="switchSectionTab('tab-audit', 'monitoring', this)">&#x1F4CB; السجل</div>
            <div class="section-inner-tab active" onclick="switchSectionTab('tab-tickets', 'monitoring', this)">&#x1F3AB; التذاكر</div>
        </div>
        <div class="header-actions">
            <h2>🎫 تذاكر الدعم الفني (Support Tickets)</h2>
            <div style="display:flex; gap:10px;">
                <select id="tickets-status-filter" onchange="loadTickets()" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border);">
                    <option value="all">جميع الحالات</option>
                    <option value="open" selected>مفتوحة</option>
                    <option value="in_progress">قيد المعالجة</option>
                    <option value="closed">مغلقة</option>
                </select>
                <button class="btn-primary" onclick="loadTickets()">🔄 تحديث</button>
            </div>
        </div>
        <div class="table-container glass-panel">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>رقم التذكرة</th>
                        <th>الشركة</th>
                        <th>العنوان</th>
                        <th>الحالة</th>
                        <th>تاريخ الإنشاء</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody id="tickets-table-body">
                    <tr><td colspan="6" style="text-align:center;">قريباً... يتم تجهيز النظام</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Security Tab -->
    <div id="tab-security" class="tab-content fade-in">
        <div class="section-inner-tabs" data-group="monitoring">
            <div class="section-inner-tab" onclick="switchSectionTab('tab-health', 'monitoring', this)">&#x1F5A5;&#xFE0F; حالة الخادم</div>
            <div class="section-inner-tab active" onclick="switchSectionTab('tab-security', 'monitoring', this)">&#x1F6E1;&#xFE0F; الأمان</div>
            <div class="section-inner-tab" onclick="switchSectionTab('tab-audit', 'monitoring', this)">&#x1F4CB; السجل</div>
            <div class="section-inner-tab" onclick="switchSectionTab('tab-tickets', 'monitoring', this)">&#x1F3AB; التذاكر</div>
        </div>
        <div class="header-actions">
            <h2>🛡️ الأمان وحظر العناوين (Security & IP Ban)</h2>
            <div style="display:flex; gap:10px;">
                <button class="btn-primary" onclick="loadBannedIPs()">🔄 تحديث</button>
                <button class="btn-primary" style="background:#ef4444; border-color:#ef4444;" onclick="document.getElementById('ip-ban-form').style.display = document.getElementById('ip-ban-form').style.display === 'none' ? 'flex' : 'none'">➕ حظر IP جديد</button>
            </div>
        </div>
        <!-- Add IP Ban Form -->
        <div id="ip-ban-form" class="glass-panel" style="display:none; gap:15px; align-items:flex-end; margin-bottom:15px; padding:20px;">
            <div class="form-group" style="flex:1; margin:0;">
                <label>عنوان IP</label>
                <input type="text" id="ban-ip-address" placeholder="مثال: 192.168.1.100" style="direction:ltr; text-align:left;">
            </div>
            <div class="form-group" style="flex:2; margin:0;">
                <label>سبب الحظر</label>
                <input type="text" id="ban-ip-reason" placeholder="مثال: محاولات دخول مشبوهة متكررة">
            </div>
            <button class="btn-primary" style="background:#ef4444; border-color:#ef4444; white-space:nowrap; height:42px;" onclick="banIP()">🚫 حظر الآن</button>
        </div>
        <div class="glass-panel" style="padding: 20px;">
            <p style="color:var(--text-secondary); margin-bottom:20px;">هذه الشاشة تتيح لك مراقبة ومنع عناوين IP المشبوهة من الوصول للنظام. العناوين المحظورة لن تتمكن من تسجيل الدخول.</p>
            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>عنوان IP</th>
                            <th>سبب الحظر</th>
                            <th>تاريخ الحظر</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="banned-ips-body">
                        <tr><td colspan="4" style="text-align:center;">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <!-- Ticket Chat Modal -->
    <div id="ticket-chat-modal" class="modal-overlay" style="display:none;" onclick="if(event.target===this) closeModal('ticket-chat-modal')">
        <div class="glass-panel modal-content" style="max-width: 700px; height: 80vh; display: flex; flex-direction: column;">
            <div class="modal-header" style="flex-shrink:0;">
                <h3 id="ticket-chat-title">محادثة التذكرة</h3>
                
            </div>
            <div id="ticket-chat-messages" style="flex:1; overflow-y:auto; padding:15px; display:flex; flex-direction:column; gap:12px; background:rgba(0,0,0,0.2); border-radius:10px; margin:10px 0;">
                <div style="text-align:center; color:var(--text-secondary);">جاري تحميل المحادثة...</div>
            </div>
            <div style="flex-shrink:0; display:flex; gap:10px; padding:15px; border-top:1px solid var(--glass-border); align-items: center;">
                <input type="hidden" id="ticket-chat-id">
                <input type="text" id="ticket-chat-input" placeholder="اكتب ردك هنا..." style="flex:1; padding:12px 15px; border-radius:20px; border:1px solid var(--glass-border); background:rgba(0,0,0,0.3); color:white; outline:none; font-family:'Tajawal',sans-serif; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--glass-border)'" onkeypress="if(event.key==='Enter') sendTicketReply()">
                <button onclick="sendTicketReply()" style="background: var(--primary-color); color: white; border: none; border-radius: 50%; width: 45px; height: 45px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding-right: 5px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" style="transform: rotate(180deg); pointer-events: none;">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" style="pointer-events: none;"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Plan Modal -->
    <div id="plan-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('plan-modal')">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إدارة باقة</h3></div>
            <input type="hidden" id="plan-id">
            <div class="form-group"><label>اسم الباقة</label><input type="text" id="plan-name"></div>
            <div class="form-group"><label>السعر الشهري ($)</label><input type="number" id="plan-price"></div>
            <div class="form-group"><label>مدة الباقة (بالأيام)</label><input type="number" id="plan-duration" value="30" placeholder="مثال: 30"></div>
            <div class="form-group"><label>الحد الأقصى للمستخدمين</label><input type="number" id="plan-users"></div>
            <div class="form-group"><label>المساحة (GB)</label><input type="number" id="plan-storage"></div>
            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="plan-is-trial" style="width: auto;">
                <label for="plan-is-trial" style="margin: 0; color: var(--primary-accent);">تحديد كباقة تجريبية مجانية</label>
            </div>
            <div class="modal-actions">
                <button class="btn-primary" style="flex:1;" onclick="savePlan()">حفظ</button>
            </div>
        </div>
    </div>

    
    <!-- Partner Modal -->
    <div id="partner-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('partner-modal')">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إدارة شريك</h3></div>
            <input type="hidden" id="partner-id">
            <input type="hidden" id="partner-existing-logo">
            <div class="form-group"><label>الاسم</label><input type="text" id="partner-name"></div>
            <div class="form-group">
                <label>صورة الشعار (اختيار ملف)</label>
                <input type="file" id="partner-logo-upload" accept="image/*">
                <div id="partner-logo-preview" style="display:none; margin-top:10px; text-align:center;">
                    <img id="partner-img-tag" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="شعار الشريك" loading="lazy" style="max-height:60px; border-radius:8px;">
                </div>
            </div>
            <div class="form-group" style="margin-top:15px; display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="partner-active" checked>
                <label for="partner-active" style="margin:0; font-size:0.9rem; color:var(--text-secondary);">تفعيل العرض في الشاشة الرئيسية</label>
            </div>
            <div class="modal-actions">
                <button class="btn-primary" id="save-partner-btn" style="flex:1;" onclick="savePartner()">حفظ</button>
            </div>
        </div>
    </div>

    <!-- User Modal (Add User / Reset Pass) -->
    <div id="user-action-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('user-action-modal')">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3 id="user-modal-title">إعادة تعيين كلمة المرور</h3></div>
            <input type="hidden" id="target-user-id">
            <div class="form-group">
                <label>اسم المستخدم</label>
                <input type="text" id="target-username" readonly style="background: rgba(255,255,255,0.05); color: #aaa; cursor: not-allowed; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); width: 100%; box-sizing: border-box;">
            </div>
            <div class="form-group">
                <label>كلمة المرور الحالية</label>
                <input type="text" id="target-user-current-pass" readonly style="background: rgba(255,255,255,0.05); color: #fff; cursor: not-allowed; font-family: monospace; font-size: 0.95rem; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); width: 100%; box-sizing: border-box; font-weight: bold;">
            </div>
            <div class="form-group">
                <label>كلمة المرور الجديدة</label>
                <input type="password" id="target-user-pass" placeholder="أدخل كلمة المرور الجديدة هنا" style="padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); width: 100%; box-sizing: border-box; background: rgba(0,0,0,0.2); color: #fff;">
            </div>
            <div class="modal-actions">
                <button class="btn-primary" style="flex:1;" onclick="saveUserPassword()">تغيير كلمة المرور</button>
            </div>
        </div>
    </div>
    
    <div id="add-user-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('add-user-modal')">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إضافة مستخدم جديد لشركة</h3></div>
            <div class="form-group"><label>اسم المستخدم (للدخول)</label><input type="text" id="new-user-username"></div>
            <div class="form-group"><label>الاسم الكامل</label><input type="text" id="new-user-fullname"></div>
            <div class="form-group"><label>كلمة المرور</label><input type="password" id="new-user-pass"></div>
            <div class="form-group"><label>الدور</label>
                <select id="new-user-role"><option value="user">مستخدم (User)</option><option value="admin">مدير (Admin)</option></select>
            </div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveNewUser()">حفظ المستخدم</button></div>
        </div>
    </div>

    <!-- Edit Tenant Modal -->
    <div id="edit-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('edit-modal')">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>تعديل حالة المشترك</h3></div>
            <input type="hidden" id="edit-tenant-id">
            <div class="form-group"><label>الباقة</label><select id="edit-tenant-plan" onchange="updateMaxUsersFromPlan()"></select></div>
            <div class="form-group"><label>الحد الأقصى للمستخدمين (مخصص)</label><input type="number" id="edit-tenant-max-users" placeholder="يتبع الباقة افتراضياً..."></div>
            <div class="form-group"><label>الحد الأقصى للمساحة (جيجابايت) (مخصص)</label><input type="number" id="edit-tenant-max-storage" placeholder="يتبع الباقة افتراضياً..."></div>
            <div class="form-group">
                <label>حالة الحساب</label>
                <select id="edit-status"><option value="active">نشط</option><option value="suspended">موقوف</option></select>
            </div>
            <div class="form-group"><label>تاريخ النهاية</label><input type="text" id="edit-valid-until" placeholder="اختر التاريخ والوقت..."></div>
            <div class="form-group" style="background: rgba(16,185,129,0.1); padding: 10px; border-radius: 8px; border-right: 3px solid #10b981; margin-top: 15px;">
                <label style="color:#10b981;">💰 تسجيل دفعة مالية (اختياري)</label>
                <div style="display:flex; gap:10px;">
                    <input type="number" id="edit-tenant-payment-amount" placeholder="المبلغ (مثال: 150)" style="flex:1;">
                    <input type="text" id="edit-tenant-payment-desc" placeholder="البيان (مثال: تجديد سنوي)" style="flex:2;">
                </div>
            </div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveTenantUpdate()">حفظ</button></div>
        </div>
    </div>
    
    <!-- Msg Modal -->
    <div id="message-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('message-modal')">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إنشاء رسالة</h3></div>
            <input type="hidden" id="msg-id" value="0">
            <div class="form-group"><label>العنوان</label><input type="text" id="msg-title"></div>
            <div class="form-group"><label>المحتوى</label><textarea id="msg-body" rows="3"></textarea></div>
            <div class="form-group"><label>اللون</label><select id="msg-theme"><option value="info">أزرق</option><option value="success">أخضر</option><option value="warning">برتقالي</option><option value="danger">أحمر</option></select></div>
            
            <div class="form-group">
                <label>الجمهور المستهدف</label>
                <select id="msg-target-type" onchange="toggleMsgTargetTenantsList()" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 100%; font-family: Tajawal;">
                    <option value="all">الجميع (كافة المشتركين)</option>
                    <option value="custom">مشتركين محددين</option>
                </select>
            </div>
            
            <div class="form-group" id="msg-target-tenants-group" style="display: none; margin-top: 10px;">
                <label>حدد المشتركين المستهدفين:</label>
                <div id="msg-target-tenants-list" style="max-height: 120px; overflow-y: auto; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 8px; padding: 10px; text-align: right; margin-top: 5px;">
                    <!-- Will be populated dynamically -->
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px;"><input type="checkbox" id="msg-show-once" checked> <label style="display:inline; margin-right: 5px;">عرض مرة واحدة</label></div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveMessage()">حفظ</button></div>
        </div>
    </div>

    <!-- Billing Modal -->
    <div id="billing-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('billing-modal')">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header"><h3>إعدادات اشتراك السيرفر</h3></div>
            <div class="form-group"><label>مزود الخدمة</label><input type="text" id="billing-provider" placeholder="مثال: DigitalOcean"></div>
            <div class="form-group"><label>التكلفة الشهرية ($)</label><input type="number" step="0.01" id="billing-cost" placeholder="مثال: 45.00"></div>
            <div class="form-group"><label>تاريخ التجديد القادم</label><input type="date" id="billing-renewal"></div>
            <div class="form-group"><label>خطة السيرفر</label><input type="text" id="billing-plan" placeholder="مثال: Premium AMD 4GB"></div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveBilling()">حفظ بيانات الاشتراك</button></div>
        </div>
    </div>

    
    <!-- Ads Modal -->
    <div id="ad-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('ad-modal')">
        <div class="glass-panel modal-content" style="max-width: 650px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="ad-modal-title" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-bullhorn text-amber-400"></i> إضافة إعلان جديد
                </h3>
            </div>
            
            <input type="hidden" id="ad-id">
            <input type="hidden" id="ad-existing-image">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; color: #cbd5e1; font-size: 0.9rem;">اسم الشركة / الجهة المعلنة <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="ad-company" placeholder="مثال: مصنع الأسعد للحديد" style="width: 100%; padding: 10px 12px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); outline: none;">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; color: #cbd5e1; font-size: 0.9rem;">رابط التوجيه عند النقر (اختياري)</label>
                    <input type="url" id="ad-link" placeholder="https://example.com" style="width: 100%; padding: 10px 12px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); outline: none; direction: ltr; text-align: left;">
                </div>
            </div>

            <!-- Ad Duration Management Section -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 15px; margin: 15px 0;">
                <div style="color: var(--primary-accent); font-weight: 800; font-size: 0.95rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-calendar-days"></i> إدارة مدة وصلاحية الإعلان
                </div>

                <!-- Quick presets -->
                <div style="margin-bottom: 12px;">
                    <label style="color: #94a3b8; font-size: 0.8rem; display: block; margin-bottom: 6px;">اختيار سريع للمدة:</label>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="btn-outline" onclick="setAdDurationPreset(7)" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">7 أيام</button>
                        <button type="button" class="btn-outline" onclick="setAdDurationPreset(15)" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">15 يوم</button>
                        <button type="button" class="btn-outline" onclick="setAdDurationPreset(30)" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">30 يوم (شهر)</button>
                        <button type="button" class="btn-outline" onclick="setAdDurationPreset(60)" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">60 يوم</button>
                        <button type="button" class="btn-outline" onclick="setAdDurationPreset(90)" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">90 يوم (3 أشهر)</button>
                        <button type="button" class="btn-outline" onclick="setAdDurationPreset(180)" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">6 أشهر</button>
                        <button type="button" class="btn-outline" onclick="setAdDurationPreset(365)" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">سنة كاملة</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group" style="margin: 0;">
                        <label style="color: #cbd5e1; font-size: 0.85rem; font-weight: bold;">تاريخ بدء الإعلان</label>
                        <input type="date" id="ad-start-date" onchange="onAdStartDateChange()" style="width: 100%; padding: 8px 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); outline: none;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label style="color: #cbd5e1; font-size: 0.85rem; font-weight: bold;">المدة (بالأيام)</label>
                        <input type="number" id="ad-duration-days" min="1" value="30" oninput="onAdDurationDaysChange()" style="width: 100%; padding: 8px 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); outline: none; text-align: center; font-weight: bold;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label style="color: #cbd5e1; font-size: 0.85rem; font-weight: bold;">تاريخ انتهاء الإعلان</label>
                        <input type="date" id="ad-end-date" onchange="onAdEndDateChange()" style="width: 100%; padding: 8px 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); outline: none;">
                    </div>
                </div>
            </div>

            <!-- Ad Image Section -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 700; color: #cbd5e1; font-size: 0.9rem;">صورة الإعلان (البانر)</label>
                <div style="font-size: 0.8rem; color: #fbbf24; margin-bottom: 8px; background: rgba(251,191,36,0.08); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(251,191,36,0.2); line-height: 1.5;">
                    📐 <strong>المقاس القياسي الموحد (نسبة 10 : 1):</strong><br>
                    • **دقة 4K الفائقة:** <strong>3840 × 384 بكسل</strong> &nbsp;|&nbsp; • **Full HD:** <strong>1920 × 192 بكسل</strong> &nbsp;|&nbsp; • **الصيغ:** PNG, WEBP, JPG
                </div>
                <input type="file" id="ad-image-upload" accept="image/*" onchange="previewAdImage(this)" style="background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; width: 100%; color: #fff; border: 1px dashed rgba(255,255,255,0.2);">
                <div id="ad-image-preview" style="margin-top: 10px; max-height: 120px; overflow: hidden; display: none; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: #000;">
                    <img src="" alt="معاينة الإعلان" loading="lazy" style="width: 100%; max-height: 120px; object-fit: contain;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 700; color: #cbd5e1; font-size: 0.85rem;">ملاحظات داخلية / تفاصيل التعاقد (اختياري)</label>
                <textarea id="ad-notes" rows="2" placeholder="اكتب أي ملاحظات إدارية هنا مثل: رقم هاتف المعلن، قيمة الدفعة، شروط خاصة..." style="width: 100%; padding: 8px 12px; border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--glass-border); outline: none; resize: vertical;"></textarea>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 20px; background: rgba(255,255,255,0.03); padding: 10px 14px; border-radius: 8px;">
                <input type="checkbox" id="ad-active" checked style="width: 18px; height: 18px; cursor: pointer;"> 
                <label for="ad-active" style="margin: 0; cursor: pointer; color: #e2e8f0; font-weight: 600;">تفعيل الإعلان وعرضه للمشتركين</label>
            </div>

            <div class="modal-actions" style="display: flex; gap: 10px;">
                <button class="btn-primary" style="flex: 2; padding: 12px;" onclick="saveAd()" id="ad-save-btn">
                    <i class="fa-solid fa-save"></i> حفظ الإعلان
                </button>
                <button class="btn-outline" style="flex: 1; padding: 12px;" onclick="closeModal('ad-modal')">إلغاء</button>
            </div>
        </div>
    </div>
    
    <!-- Admin WhatsApp Modal -->
    <div id="admin-wa-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('admin-wa-modal')">
        <div class="glass-panel modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-header">
                <h3 style="color:#25d366; margin: 0; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#25d366" style="vertical-align: middle;">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                    </svg>
                    ربط واتساب الإدارة العامة
                </h3>
                
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <p id="admin-wa-status-text" style="font-weight: bold; color: #fff; margin-bottom: 10px;">جاري فحص الاتصال...</p>
                <div id="admin-wa-qr-container" style="background: #fff; padding: 15px; border-radius: 12px; display: inline-block; min-width: 200px; min-height: 200px; margin-top: 10px;">
                    <img id="admin-wa-qr-image" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="QR Code" style="width: 100%; display: none;" />
                    <div id="admin-wa-loading" style="color: #000; margin-top: 80px;">⏳ جاري التوليد...</div>
                </div>
                
                <!-- Success Animation Container (Hidden by default) -->
                <div id="admin-wa-success-animation" style="display: none; padding: 20px; flex-direction: column; align-items: center; justify-content: center; margin-top: 10px;">
                    <div style="position: relative; width: 80px; height: 80px; margin: 0 auto; animation: pulse 2s infinite;">
                        <svg viewBox="0 0 24 24" fill="#25d366" style="width: 80px; height: 80px; drop-shadow: 0 4px 10px rgba(37, 211, 102, 0.4);">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                        </svg>
                        <div style="position: absolute; top: -5px; right: -5px; background: #fff; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            <span style="color: #25d366; font-size: 20px; font-weight: bold;">✓</span>
                        </div>
                    </div>
                </div>

                <p id="admin-wa-instruction-text" style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 15px; line-height: 1.5;">
                    امسح هذا الرمز باستخدام تطبيق واتساب في هاتفك لربط حساب الإدارة العامة بالنظام لإرسال الـ OTP والتنبيهات.
                </p>
            </div>
            <div class="modal-actions" style="margin-top: 20px; justify-content: center; gap: 10px; flex-wrap: wrap;">
                <button class="btn-danger" id="admin-wa-logout-btn" style="display: none; padding: 10px 20px; background: #ef4444; border: none; font-weight: bold; color: #ffffff; border-radius: 8px; cursor: pointer; flex: 1; min-width: 120px;" onclick="logoutAdminWhatsApp()">إلغاء الربط</button>
                <button class="btn-outline" id="admin-wa-refresh-btn" style="padding: 10px 20px; border-radius: 8px; cursor: pointer; flex: 1; min-width: 120px;" onclick="checkAdminWhatsAppConnection()">تحديث الحالة</button>
                <button class="btn-outline" id="admin-wa-restart-btn" style="display: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; flex: 1; min-width: 120px; border-color: #f59e0b; color: #f59e0b;" onclick="restartAdminWhatsApp()">🔄 إعادة تشغيل الخدمة</button>

                <button class="btn-primary" id="admin-wa-close-btn" style="display: none; padding: 10px 20px; background: #25d366; border: none; font-weight: bold; color: #ffffff; border-radius: 8px; cursor: pointer; flex: 1; min-width: 120px;" onclick="closeModal('admin-wa-modal')">إغلاق النافذة</button>
            </div>
        </div>
    </div>
    
    <script>
        // Register PWA service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
                .then(function(reg) { console.log('Service Worker registered:', reg.scope); })
                .catch(function(err) { console.error('Service Worker registration failed:', err); });
        }

        let SAAS_TOKEN = sessionStorage.getItem('saas_token');
        if (SAAS_TOKEN === 'null' || SAAS_TOKEN === 'undefined' || !SAAS_TOKEN) {
            SAAS_TOKEN = null;
        }

        function showToast(message, type = 'success') {
            const icons = {success: '✅', error: '❌', info: 'ℹ️'};
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `<span>${icons[type] || '✅'}</span><span>${message}</span>`;
            document.body.appendChild(toast);
            requestAnimationFrame(() => { requestAnimationFrame(() => { toast.classList.add('show'); }); });
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3000);
        }

        // Global Fetch Interceptor to handle expired or invalid tokens (401 Unauthorized)
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            try {
                const res = await originalFetch(...args);
                if (res.status === 401 && SAAS_TOKEN) {
                    sessionStorage.removeItem('saas_token');
                    showToast('انتهت صلاحية الجلسة أو الرمز غير صحيح. يرجى تسجيل الدخول مرة أخرى.', 'error');
                    setTimeout(() => { location.reload(); }, 1500);
                }
                return res;
            } catch (err) {
                throw err;
            }
        };

        let allTenants = [];
        let allMessagesData = [];
        let allPlans = [];
        let allPartners = [];
        let partnersEnabled = true;
        let editValidUntilPicker;
        
        document.addEventListener("DOMContentLoaded", function() {
            editValidUntilPicker = flatpickr("#edit-valid-until", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                locale: "ar",
                time_24hr: false,
                altInput: true,
                altFormat: "Y/m/d h:i K",
                disableMobile: "true"
            });
        });
        
        // Must be declared before initDashboard() is called (Nginx: PATH_INFO not supported)
        let useQueryParams = true;
        
        

        if (SAAS_TOKEN) {
            document.getElementById('login-modal').style.display = 'none';
            document.getElementById('dashboard').style.display = 'block';
            document.getElementById('sidebar').style.display = 'flex';
            initDashboard();
        } else {
            document.getElementById('login-modal').style.display = 'flex';
            document.getElementById('sidebar').style.display = 'none';
        }

        async function loginSaas() {
            const pass = document.getElementById('saas-pass').value.trim();
            if (!pass) return alert('الرجاء إدخال الرمز');
            
            const btn = document.querySelector('.login-box button');
            const oldText = btn.innerText;
            btn.innerText = 'جاري التحقق...';
            btn.disabled = true;

            try {
                const res = await fetch('api_saas_auth.php?_=' + Date.now(), {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pass })
                });
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    alert('استجابة غير مفهومة من الخادم: ' + text.substring(0, 60));
                    throw e;
                }
                
                if (data.success) {
                    SAAS_TOKEN = data.token;
                    sessionStorage.setItem('saas_token', SAAS_TOKEN);
                    showToast("تم التحقق بنجاح! جاري تحويلك للوحة التحكم...", "success");
                    setTimeout(() => { location.reload(); }, 1200);
                } else {
                    showToast(data.error || 'رمز الدخول خاطئ', 'error');
                }
            } catch (e) { 
                console.error(e);
            } finally {
                btn.innerText = oldText;
                btn.disabled = false;
            }
        }

        function logoutSaas() { sessionStorage.removeItem('saas_token'); document.getElementById('sidebar').style.display = 'none'; location.reload(); }

        function switchTab(tabId, btn) {
            window.location.hash = tabId;
        }

        // === SPA ROUTING (URL HASH) ===
        function handleHashChange() {
            if (!SAAS_TOKEN) return; // Don't route if not logged in
            
            let fullHash = window.location.hash.replace('#', '');
            if (!fullHash) fullHash = 'tab-analytics';
            
            let hashParts = fullHash.split('?');
            let hash = hashParts[0];
            let qs = hashParts[1] ? new URLSearchParams(hashParts[1]) : null;
            
            const targetTab = document.getElementById(hash);
            if (targetTab && targetTab.classList.contains('tab-content')) {
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                // Show target
                targetTab.classList.add('active');
                
                // Update Sidebar highlight
                document.querySelectorAll('.sidebar .nav-item').forEach(n => {
                    n.classList.remove('active');
                    const onclickAttr = n.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes("'" + hash + "'")) n.classList.add('active');
                });
                
                // Update Inner Tabs highlight
                document.querySelectorAll('.section-inner-tab').forEach(btn => {
                    btn.classList.remove('active');
                    const onclickAttr = btn.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes("'" + hash + "'")) btn.classList.add('active');
                });

                // Trigger data loading
                if (hash === 'tab-audit') loadAuditLogs(true);
                else if (hash === 'tab-global-payments') loadGlobalPayments(true);
                else if (hash === 'tab-health') loadServerHealth();
                else if (hash === 'tab-tickets') loadTickets();
                else if (hash === 'tab-security') loadBannedIPs();
                else if (hash === 'tab-settings' || hash.startsWith('settings-')) loadGlobalSettings();
                
                if (hash === 'tab-tenants') {
                    if (qs && qs.has('id')) {
                        openTenantDetails(qs.get('id'), false);
                    } else {
                        document.getElementById('tenant-full-view').style.display = 'none';
                        document.getElementById('tenants-list-view').style.display = 'block';
                    }
                }
            }
        }
        window.addEventListener('hashchange', handleHashChange);

        // === SIDEBAR NAVIGATION ===
        function sidebarNav(tabId, el) {
            window.location.hash = tabId;
        }

        function toggleNavGroup(header) {
            // Deprecated in flat sidebar, kept for compatibility
        }

        function toggleSidebarPin() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('pinned');
        }

        function switchSectionTab(tabId, group, el) {
            window.location.hash = tabId;
        }

        function escapeHtml(str) {
            if(!str) return '';
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        let auditCurrentPage = 1;
        const auditPageSize = 10;
        let auditTotal = 0;

        async function loadAuditLogs(resetPage = false) {
            if (resetPage) auditCurrentPage = 1;
            const search = document.getElementById('audit-search') ? document.getElementById('audit-search').value : '';
            const offset = (auditCurrentPage - 1) * auditPageSize;
            
            try {
                const url = apiUrl('saas', 'audit-logs') + `&limit=${auditPageSize}&offset=${offset}&search=${encodeURIComponent(search)}`;
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                
                if (data.success) {
                    auditTotal = data.total;
                    renderAuditLogs(data.logs);
                }
            } catch(e) {
                console.error("Failed to load audit logs", e);
            }
        }

        function renderAuditLogs(logs) {
            const tbody = document.getElementById('audit-logs-table-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            
            if (logs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-secondary);">لا توجد عمليات مسجلة حالياً.</td></tr>`;
                return;
            }
            
            logs.forEach((log, index) => {
                const globalIndex = (auditCurrentPage - 1) * auditPageSize + index + 1;
                tbody.innerHTML += `
                    <tr>
                        <td>${globalIndex}</td>
                        <td style="font-weight: bold; color: #f59e0b;">${escapeHtml(log.action)}</td>
                        <td>${escapeHtml(log.details)}</td>
                        <td><code style="background:rgba(255,255,255,0.05); padding: 2px 6px; border-radius:4px; font-size:0.85rem;">${escapeHtml(log.ip_address)}</code></td>
                        <td style="color: var(--text-secondary); font-size: 0.9rem;">${escapeHtml(log.created_at)}</td>
                    </tr>
                `;
            });
            
            const totalPages = Math.ceil(auditTotal / auditPageSize) || 1;
            document.getElementById('audit-page-indicator').innerText = `الصفحة ${auditCurrentPage} من ${totalPages}`;
            document.getElementById('btn-audit-prev').disabled = (auditCurrentPage === 1);
            document.getElementById('btn-audit-next').disabled = (auditCurrentPage === totalPages);
        }

        function changeAuditPage(direction) {
            const totalPages = Math.ceil(auditTotal / auditPageSize) || 1;
            const newPage = auditCurrentPage + direction;
            if (newPage >= 1 && newPage <= totalPages) {
                auditCurrentPage = newPage;
                loadAuditLogs();
            }
        }

        function downloadDatabaseBackup() {
            if (!confirm("هل ترغب بتنزيل نسخة احتياطية كاملة من قاعدة بيانات SaaS الإجمالية الآن؟")) return;
            const url = apiUrl('saas', 'settings', 'backup-db') + `&token=${encodeURIComponent(SAAS_TOKEN)}`;
            window.open(url, '_blank');
        }

        function renderBackupTenantsTable() {
            const tbody = document.getElementById('backup-tenants-table-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            
            const search = (document.getElementById('backup-tenant-search').value || '').toLowerCase();
            
            const filtered = allTenants.filter(t => {
                const companyName = (t.company_name || '').toLowerCase();
                const subdomain   = (t.subdomain || '').toLowerCase();
                const ownerName   = (t.owner_name || '').toLowerCase();
                const ownerPhone  = (t.owner_phone || '').toLowerCase();
                const phone2      = (t.phone2 || '').toLowerCase();
                
                return search === '' || 
                       companyName.includes(search) || 
                       subdomain.includes(search) || 
                       ownerName.includes(search) || 
                       ownerPhone.includes(search) ||
                       phone2.includes(search);
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-secondary); padding: 20px;">لا يوجد نتائج تطابق البحث</td></tr>`;
                return;
            }

            filtered.forEach(t => {
                let planId = t.plan_id || 1;
                let plan = allPlans.find(p => p.id == planId) || allPlans[0] || {name: 'غير محدد', storage: '-'};
                let maxStorage = t.max_storage !== null ? t.max_storage : (plan.storage || '-');

                let storageBytes = parseInt(t.storage_used) || 0;
                let storageStr = storageBytes + ' B';
                if (storageBytes >= 1073741824) storageStr = (storageBytes / 1073741824).toFixed(2) + ' GB';
                else if (storageBytes >= 1048576) storageStr = (storageBytes / 1048576).toFixed(2) + ' MB';
                else if (storageBytes >= 1024) storageStr = (storageBytes / 1024).toFixed(2) + ' KB';

                tbody.innerHTML += `
                    <tr style="cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05);" onclick="toggleBackupRowCheckbox(${t.id}, event)">
                        <td style="text-align: center; padding: 12px 15px;" onclick="event.stopPropagation();">
                            <input type="checkbox" value="${t.id}" class="backup-tenant-checkbox" id="backup-cb-${t.id}" style="transform: scale(1.25); cursor: pointer;" onchange="updateBackupSelectAllHeaderState()">
                        </td>
                        <td style="padding: 12px 15px;">
                            <strong style="font-size: 1.15rem; color:#fff; display:block; margin-bottom: 2px;">${t.company_name || '—'}</strong>
                            <small style="color:var(--primary-accent); font-weight: 600;">${t.subdomain || '—'}.elsajana.com</small>
                        </td>
                        <td style="padding: 12px 15px;">
                            <span style="font-weight: 600; display:block; margin-bottom: 2px;">${t.owner_name || '-'}</span>
                            <small style="color:var(--text-secondary);">${t.owner_phone || ''} ${t.phone2 ? ' - ' + t.phone2 : ''}</small>
                        </td>
                        <td style="direction: ltr; font-weight: bold; color: #cbd5e1; padding: 12px 15px;">
                            ${storageStr} <br>
                            <small style="color:var(--text-secondary); font-weight: normal;">الحد الأقصى: ${maxStorage} GB</small>
                        </td>
                        <td style="padding: 12px 15px;">
                            <span class="badge badge-plan" style="padding: 6px 12px; font-weight: bold;">${plan.name}</span>
                        </td>
                    </tr>
                `;
            });
        }

        function toggleBackupRowCheckbox(id, event) {
            if (event.target.tagName === 'INPUT') return;
            const cb = document.getElementById('backup-cb-' + id);
            if (cb) {
                cb.checked = !cb.checked;
                updateBackupSelectAllHeaderState();
            }
        }

        function selectAllBackupTenants(checked) {
            document.querySelectorAll('.backup-tenant-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            const headerCb = document.getElementById('backup-select-all-header');
            if (headerCb) headerCb.checked = checked;
        }

        function updateBackupSelectAllHeaderState() {
            const allCbs = document.querySelectorAll('.backup-tenant-checkbox');
            const checkedCbs = document.querySelectorAll('.backup-tenant-checkbox:checked');
            const headerCb = document.getElementById('backup-select-all-header');
            if (headerCb && allCbs.length > 0) {
                headerCb.checked = (allCbs.length === checkedCbs.length);
            }
        }

        function downloadSelectedTenantsBackup() {
            const selectedCbs = document.querySelectorAll('.backup-tenant-checkbox:checked');
            if (selectedCbs.length === 0) {
                alert('الرجاء اختيار مشترك واحد على الأقل لتنزيل النسخة الاحتياطية!');
                return;
            }
            const ids = Array.from(selectedCbs).map(cb => cb.value).join(',');
            const url = apiUrl('saas', 'settings', 'backup-db') + `&tenant_ids=${encodeURIComponent(ids)}&token=${encodeURIComponent(SAAS_TOKEN)}`;
            window.open(url, '_blank');
        }

        function populateWaTenantSelect() {
            const select = document.getElementById('wa-target-tenant');
            if (!select) return;
            select.innerHTML = '<option value="all">📢 إرسال لجميع المشتركين (رسالة جماعية)</option>';
            allTenants.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.dataset.phone = t.owner_phone;
                opt.dataset.owner = t.owner_name;
                opt.dataset.company = t.company_name;
                opt.innerText = `${t.company_name || '—'} (${t.owner_name || '—'} - ${t.owner_phone || '—'})`;
                select.appendChild(opt);
            });
        }

        function applyWaTemplate(val) {
            const textarea = document.getElementById('wa-message-body');
            if (!textarea) return;
            
            const selectTenant = document.getElementById('wa-target-tenant');
            let ownerName = "عميلنا الكريم";
            let companyName = "";
            if (selectTenant && selectTenant.value !== 'all') {
                const opt = selectTenant.options[selectTenant.selectedIndex];
                ownerName = opt.dataset.owner || "عميلنا الكريم";
                companyName = opt.dataset.company || "";
            }
            
            let msg = '';
            if (val === 'expiry') {
                msg = `السلام عليكم ورحمة الله وبركاته، أستاذ ${ownerName}.\n\nنود تذكيركم بأن اشتراك شركتكم ${companyName ? `(${companyName})` : ''} في نظام السجّانة ERP قارب على الانتهاء.\n\nيرجى التواصل معنا لتجديد الاشتراك لضمان استمرارية الخدمة دون انقطاع.\n\nشكراً لثقتكم بنا 🌸`;
            } else if (val === 'welcome') {
                msg = `أهلاً بك أستاذ ${ownerName} في عائلة السجّانة ERP! 🎉\n\nتم تفعيل حساب شركتكم ${companyName ? `(${companyName})` : ''} بنجاح.\n\nيمكنكم الآن البدء في إدارة حساباتكم ومخازنكم بكل سهولة.\n\nإذا واجهتك أي استفسارات، نحن هنا دائماً لخدمتك 🌸`;
            } else if (val === 'maintenance') {
                msg = `تنبيه هام لكل عملائنا الكرام ⚙️\n\nنود إخطاركم بأن هناك أعمال صيانة وتحديثات مجدولة لنظام السجّانة ERP ستبدأ اليوم في تمام الساعة 11:00 مساءً ولمدة ساعة واحدة.\n\nنشكر لكم حسن تعاونكم وتفهمكم 🌸`;
            }
            textarea.value = msg;
        }

        function toggleBroadcastView(view) {
            const btnWa = document.getElementById('btn-bc-whatsapp');
            const btnSys = document.getElementById('btn-bc-system');
            const viewWa = document.getElementById('view-bc-whatsapp');
            const viewSys = document.getElementById('view-bc-system');
            
            if(view === 'whatsapp') {
                btnWa.style.background = 'rgba(16,185,129,0.1)';
                btnWa.style.borderColor = 'rgba(16,185,129,0.3)';
                btnWa.style.color = '#10b981';
                
                btnSys.style.background = 'rgba(255,255,255,0.02)';
                btnSys.style.borderColor = 'rgba(255,255,255,0.05)';
                btnSys.style.color = '#cbd5e1';
                
                viewWa.style.display = 'block';
                viewSys.style.display = 'none';
            } else {
                btnSys.style.background = 'rgba(56,189,248,0.1)';
                btnSys.style.borderColor = 'rgba(56,189,248,0.3)';
                btnSys.style.color = '#38bdf8';
                
                btnWa.style.background = 'rgba(255,255,255,0.02)';
                btnWa.style.borderColor = 'rgba(255,255,255,0.05)';
                btnWa.style.color = '#cbd5e1';
                
                viewSys.style.display = 'block';
                viewWa.style.display = 'none';
            }
        }


        async function sendWhatsAppMessage() {
            const target = document.getElementById('wa-target-tenant').value;
            const message = document.getElementById('wa-message-body').value.trim();
            
            if (!message) {
                alert('الرجاء كتابة نص الرسالة أولاً!');
                return;
            }
            
            // Check if WhatsApp is connected first (using our state check)
            const indicator = document.getElementById('wa-status-indicator');
            if (indicator && indicator.style.backgroundColor === 'rgb(239, 68, 68)') { // reddish #ef4444
                alert('خادم الواتساب غير نشط حالياً! يرجى التأكد من تشغيل خادم الواتساب أولاً.');
                return;
            }
            
            const host = window.location.hostname;
            const sendBtn = document.querySelector('button[onclick="sendWhatsAppMessage()"]');
            const originalText = sendBtn.innerHTML;
            sendBtn.innerHTML = '<span>⏳ جاري الإرسال...</span>';
            sendBtn.disabled = true;
            
            try {
                if (target === 'all') {
                    // Send to all tenants
                    if (!confirm('هل أنت متأكد من رغبتك في إرسال هذه الرسالة الجماعية لجميع المشتركين؟')) {
                        sendBtn.innerHTML = originalText;
                        sendBtn.disabled = false;
                        return;
                    }
                    
                    let successCount = 0;
                    let failCount = 0;
                    
                    for (let t of allTenants) {
                        if (!t.owner_phone) continue;
                        try {
                            const res = await fetch(apiUrl('saas', 'wa-proxy', 'send'), {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + SAAS_TOKEN 
                                },
                                body: JSON.stringify({
                                    userId: 'default-saas-admin',
                                    phone: t.owner_phone,
                                    message: message.replace(/{owner}/g, t.owner_name || 'عميلنا الكريم').replace(/{company}/g, t.company_name || '')
                                })
                            });
                            if (res.ok) successCount++;
                            else failCount++;
                        } catch(e) {
                            failCount++;
                        }
                    }
                    alert(`تمت عملية الإرسال الجماعي بنجاح!\nالمرسل لهم بنجاح: ${successCount}\nالفشل: ${failCount}`);
                    document.getElementById('wa-message-body').value = '';
                } else {
                    // Send to single tenant
                    const select = document.getElementById('wa-target-tenant');
                    const opt = select.options[select.selectedIndex];
                    const phone = opt.dataset.phone;
                    if (!phone) {
                        alert('هذا المشترك لا يملك رقم هاتف مسجل!');
                        sendBtn.innerHTML = originalText;
                        sendBtn.disabled = false;
                        return;
                    }
                    
                    const res = await fetch(apiUrl('saas', 'wa-proxy', 'send'), {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + SAAS_TOKEN 
                        },
                        body: JSON.stringify({
                            userId: 'default-saas-admin',
                            phone: phone,
                            message: message
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert('تم إرسال رسالة الواتساب بنجاح!');
                        document.getElementById('wa-message-body').value = '';
                    } else {
                        alert('فشل الإرسال: ' + (data.error || 'خطأ غير معروف'));
                    }
                }
            } catch(err) {
                alert('فشل الاتصال بخادم الواتساب: ' + err.message);
            } finally {
                sendBtn.innerHTML = originalText;
                sendBtn.disabled = false;
            }
        }

        async function checkWhatsAppStatus(isRetry = false) {
            const indicator = document.getElementById('wa-status-indicator');
            const text = document.getElementById('wa-status-text');
            if(!indicator || !text) return;
            try {
                const res = await fetch(apiUrl('saas', 'wa-proxy', 'status'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                
                if (data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') {
                    indicator.style.background = '#10b981';
                    indicator.style.boxShadow = '0 0 8px #10b981';
                    text.innerText = 'خادم الواتساب: نشط (متصل ✅)';
                } else if (data.status === 'RECONNECTING') {
                    indicator.style.background = '#f59e0b';
                    indicator.style.boxShadow = '0 0 8px #f59e0b';
                    text.innerText = 'خادم الواتساب: إعادة اتصال... 🔄';
                } else if (data.status === 'QR_READY' || data.status === 'INITIALIZING' || data.status === 'AUTHENTICATING') {
                    indicator.style.background = '#eab308';
                    indicator.style.boxShadow = '0 0 8px #eab308';
                    text.innerText = 'خادم الواتساب: نشط (غير مرتبط)';
                } else {
                    indicator.style.background = '#eab308';
                    indicator.style.boxShadow = '0 0 8px #eab308';
                    text.innerText = 'خادم الواتساب: نشط (غير مرتبط)';
                }
            } catch(e) {
                if (!isRetry) {
                    // Try to wake up the server (the API request automatically calls startWhatsAppServiceIfNeeded)
                    indicator.style.background = '#f59e0b';
                    indicator.style.boxShadow = '0 0 8px #f59e0b';
                    text.innerText = 'خادم الواتساب: جاري بدء الخدمة تلقائياً... ⏳';
                    
                    setTimeout(() => {
                        checkWhatsAppStatus(true);
                    }, 4000);
                } else {
                    indicator.style.background = '#ef4444';
                    indicator.style.boxShadow = '0 0 8px #ef4444';
                    text.innerText = 'خادم الواتساب: متوقف ❌';
                }
            }
        }

        async function openAdminWhatsAppModal() {
            document.getElementById('admin-wa-modal').style.display = 'flex';
            checkAdminWhatsAppConnection();
        }

        async function checkAdminWhatsAppConnection(isSilent = false) {
            const statusText = document.getElementById('admin-wa-status-text');
            const qrImg = document.getElementById('admin-wa-qr-image');
            const spinner = document.getElementById('admin-wa-loading');
            const logoutBtn = document.getElementById('admin-wa-logout-btn');
            const restartBtn = document.getElementById('admin-wa-restart-btn');
            
            if (!isSilent) {
                statusText.innerText = 'جاري الفحص...';
                statusText.style.color = '#fff';
                spinner.style.display = 'block';
                qrImg.style.display = 'none';
                logoutBtn.style.display = 'none';
                if (restartBtn) restartBtn.style.display = 'none';
            }
            
            try {
                const res = await fetch(apiUrl('saas', 'wa-proxy', 'status'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                let data;
                try {
                    data = await res.json();
                } catch(pe) {
                    throw new Error('HTTP error ' + res.status);
                }
                if (data && data.success === false) {
                    throw new Error(data.error || 'Server error');
                }
                if (!res.ok) {
                    throw new Error('HTTP error ' + res.status);
                }
                
                if (data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') {
                    let msg = '✅ متصل بالواتساب بنجاح!';
                    if (data.phoneNumber) {
                        msg += `<br><span style="font-size: 0.95rem; font-weight: normal; color: #94a3b8; display: block; margin-top: 5px;">📞 متصل برقم الواتساب <strong style="color: #38bdf8; font-family: sans-serif;">[${data.phoneNumber}]</strong> بنجاح</span>`;
                    }
                    statusText.innerHTML = msg;
                    statusText.style.color = '#10b981';
                    
                    document.getElementById('admin-wa-qr-container').style.display = 'none';
                    document.getElementById('admin-wa-success-animation').style.display = 'flex';
                    document.getElementById('admin-wa-instruction-text').style.display = 'none';
                    
                    logoutBtn.style.display = 'inline-block';
                    document.getElementById('admin-wa-refresh-btn').style.display = 'none';
                    if (restartBtn) restartBtn.style.display = 'none';
                    document.getElementById('admin-wa-close-btn').style.display = 'inline-block';
                } else if (data.status === 'RECONNECTING') {
                    statusText.innerText = '🔄 جاري إعادة الاتصال... (محاولة ' + (data.reconnectAttempts || 1) + ')';
                    statusText.style.color = '#f59e0b';
                    spinner.style.display = 'block';
                    qrImg.style.display = 'none';
                    if (restartBtn) restartBtn.style.display = 'inline-block';
                    
                    setTimeout(() => {
                        const modal = document.getElementById('admin-wa-modal');
                        if (modal && modal.style.display === 'flex') {
                            checkAdminWhatsAppConnection(true);
                        }
                    }, 3000);
                } else {
                    if (!isSilent) {
                        statusText.innerText = 'يرجى مسح الرمز أدناه للربط:';
                        statusText.style.color = '#fbbf24';
                        
                        document.getElementById('admin-wa-qr-container').style.display = 'inline-block';
                        document.getElementById('admin-wa-success-animation').style.display = 'none';
                        document.getElementById('admin-wa-instruction-text').style.display = 'block';
                        
                        logoutBtn.style.display = 'none';
                        document.getElementById('admin-wa-refresh-btn').style.display = 'inline-block';
                        if (restartBtn) restartBtn.style.display = 'inline-block';
                        document.getElementById('admin-wa-close-btn').style.display = 'none';
                        
                        fetchAdminWhatsAppQR();
                    } else {
                        setTimeout(() => {
                            const modal = document.getElementById('admin-wa-modal');
                            if (modal && modal.style.display === 'flex') {
                                checkAdminWhatsAppConnection(true);
                            }
                        }, 4000);
                    }
                }
                
                checkWhatsAppStatus();
            } catch(e) {
                if (!isSilent) {
                    statusText.innerText = '❌ خادم الواتساب متوقف أو تعذر الاتصال به: ' + e.message;
                    statusText.style.color = '#ef4444';
                    spinner.style.display = 'none';
                    if (restartBtn) restartBtn.style.display = 'inline-block';
                    checkWhatsAppStatus();
                }
            }
        }

        async function fetchAdminWhatsAppQR() {
            const qrImg = document.getElementById('admin-wa-qr-image');
            const spinner = document.getElementById('admin-wa-loading');
            const statusText = document.getElementById('admin-wa-status-text');
            try {
                const res = await fetch(apiUrl('saas', 'wa-proxy', 'qr'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                let data;
                try {
                    data = await res.json();
                } catch(pe) {
                    throw new Error('HTTP error ' + res.status);
                }
                if (data && data.success === false) {
                    throw new Error(data.error || 'Server error');
                }
                if (!res.ok) {
                    throw new Error('HTTP error ' + res.status);
                }
                
                if (data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') {
                    // Already connected while fetching QR — switch to connected view
                    checkAdminWhatsAppConnection();
                    return;
                }
                
                if (data.qrCode) {
                    qrImg.src = data.qrCode;
                    qrImg.style.display = 'block';
                    spinner.style.display = 'none';
                    statusText.innerText = 'يرجى مسح الرمز أدناه للربط:';
                    statusText.style.color = '#fbbf24';
                    
                    // Check status every 3 seconds for fast response after scanning
                    setTimeout(() => {
                        const modal = document.getElementById('admin-wa-modal');
                        if (modal && modal.style.display === 'flex') {
                            checkAdminWhatsAppConnection(true);
                        }
                    }, 3000);
                    
                    // Auto-refresh QR every 15 seconds to prevent expiry
                    setTimeout(() => {
                        const modal = document.getElementById('admin-wa-modal');
                        if (modal && modal.style.display === 'flex') {
                            fetchAdminWhatsAppQR();
                        }
                    }, 15000);
                } else if (data.status === 'RECONNECTING') {
                    statusText.innerText = '🔄 جاري إعادة الاتصال تلقائياً...';
                    statusText.style.color = '#f59e0b';
                    setTimeout(() => {
                        const modal = document.getElementById('admin-wa-modal');
                        if (modal && modal.style.display === 'flex') {
                            checkAdminWhatsAppConnection(true);
                        }
                    }, 3000);
                } else {
                    statusText.innerText = 'جاري تحضير خادم الواتساب... ⏳';
                    setTimeout(() => {
                        const modal = document.getElementById('admin-wa-modal');
                        if (modal && modal.style.display === 'flex') {
                            fetchAdminWhatsAppQR();
                        }
                    }, 3000);
                }
            } catch(e) {
                statusText.innerText = '❌ فشل جلب رمز الـ QR أو الخدمة غير متوفرة: ' + e.message;
                statusText.style.color = '#ef4444';
                spinner.style.display = 'none';
                const restartBtn = document.getElementById('admin-wa-restart-btn');
                if (restartBtn) restartBtn.style.display = 'inline-block';
            }
        }

        async function logoutAdminWhatsApp() {
            if (!confirm('هل ترغب بإلغاء ربط حساب الواتساب الحالي؟')) return;
            try {
                await fetch(apiUrl('saas', 'wa-proxy', 'logout'), { 
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                checkAdminWhatsAppConnection();
            } catch(e) {
                alert('فشل تسجيل الخروج');
            }
        }

        async function restartAdminWhatsApp() {
            const statusText = document.getElementById('admin-wa-status-text');
            if (statusText) {
                statusText.innerText = '🔄 جاري إعادة تشغيل خدمة الواتساب...';
                statusText.style.color = '#f59e0b';
            }
            try {
                await fetch(apiUrl('saas', 'wa-proxy', 'restart'), {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                // Wait 3 seconds then check connection
                setTimeout(() => checkAdminWhatsAppConnection(), 3000);
            } catch(e) {
                if (statusText) {
                    statusText.innerText = '❌ فشل إعادة تشغيل الخدمة: ' + e.message;
                    statusText.style.color = '#ef4444';
                }
            }
        }

        // diagnoseWhatsApp and lock toggle functions removed — no longer needed
        // Connection is now stable with single-process enforcement
        // Deployment handles file locking automatically via deploy.yml


        let chartRegistrations = null;
        let chartStorage = null;
        let tenantUsersList = [];

        function initAnalyticsCharts() {
            if (typeof Chart === 'undefined') {
                // Retry after 2 seconds if Chart.js hasn't loaded yet
                setTimeout(() => { if (typeof Chart !== 'undefined') initAnalyticsCharts(); }, 2000);
                return;
            }
            if (!allTenants || allTenants.length === 0) return;

            // 1. Group registrations by month
            const monthlyRegs = {};
            allTenants.forEach(tenant => {
                if (tenant.created_at) {
                    const date = new Date(tenant.created_at);
                    if (!isNaN(date)) {
                        const month = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
                        monthlyRegs[month] = (monthlyRegs[month] || 0) + 1;
                    }
                }
            });

            const sortedMonths = Object.keys(monthlyRegs).sort();
            const regCounts = sortedMonths.map(m => monthlyRegs[m]);

            // 2. Group top 5 storage consumers
            const sortedStorageTenants = [...allTenants]
                .sort((a, b) => (b.storage_used || 0) - (a.storage_used || 0))
                .slice(0, 5);

            const storageLabels = sortedStorageTenants.map(t => t.company_name);
            const storageValues = sortedStorageTenants.map(t => ((t.storage_used || 0) / (1024 * 1024)).toFixed(2)); // in MB

            // Registrations Chart
            const ctxReg = document.getElementById('chart-registrations');
            if (ctxReg) {
                if (chartRegistrations) chartRegistrations.destroy();
                chartRegistrations = new Chart(ctxReg.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: sortedMonths,
                        datasets: [{
                            label: 'عدد الشركات المسجلة',
                            data: regCounts,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            },
                            x: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            }
                        }
                    }
                });
            }

            // Storage Chart
            const ctxStore = document.getElementById('chart-storage');
            if (ctxStore) {
                if (chartStorage) chartStorage.destroy();
                chartStorage = new Chart(ctxStore.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: storageLabels,
                        datasets: [{
                            label: 'المساحة المستخدمة (MB)',
                            data: storageValues,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.7)',
                                'rgba(16, 185, 129, 0.7)',
                                'rgba(245, 158, 11, 0.7)',
                                'rgba(139, 92, 246, 0.7)',
                                'rgba(236, 72, 153, 0.7)'
                            ],
                            borderWidth: 0,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            },
                            x: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            }
                        }
                    }
                });
            }

            // Revenue Chart
            const ctxRev = document.getElementById('chart-revenue');
            if (ctxRev) {
                fetch(apiUrl('saas', 'tenants', 'payments'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } })
                .then(res => res.json())
                .then(payments => {
                    if (Array.isArray(payments) && payments.length > 0) {
                        const monthlyRev = {};
                        payments.forEach(p => {
                            if (p.payment_date) {
                                const date = new Date(p.payment_date);
                                if (!isNaN(date)) {
                                    const month = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
                                    monthlyRev[month] = (monthlyRev[month] || 0) + parseFloat(p.amount);
                                }
                            }
                        });
                        const revMonths = Object.keys(monthlyRev).sort();
                        const revValues = revMonths.map(m => monthlyRev[m]);

                        new Chart(ctxRev.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: revMonths,
                                datasets: [{
                                    label: 'الإيرادات ($)',
                                    data: revValues,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#9ca3af' } },
                                    x: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#9ca3af' } }
                                }
                            }
                        });
                    }
                }).catch(e => console.error('Error fetching payments for chart', e));
            }
        }

        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        
        function toggleDropdown(id) {
            const el = document.getElementById('drop-' + id);
            if(el.classList.contains('show')) el.classList.remove('show');
            else {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('show'));
                el.classList.add('show');
            }
        }
        
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.btn-outline')) {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('show'));
            }
        });

        async function initDashboard() {
            try {
                const resPub = await fetch('api_index.php?r=public');
                const dataPub = await resPub.json();
                allPlans = dataPub.plans || [];
                allPartners = dataPub.partners || [];
                allAds = dataPub.ads || [];
                partnersEnabled = dataPub.partners_enabled !== false;
                
                const chk = document.getElementById('saas-partners-enabled');
                if (chk) chk.checked = partnersEnabled;
            } catch(e) {}

            await loadTenants();
            syncPasswordsFromLocalStorage();
            loadGlobalSettings();
            loadBackdoorCredentials();
            loadMessages();
            
            renderPlans();
            renderPartners();
            renderAds();
            
            // Check WhatsApp service status on startup and every 20 seconds
            checkWhatsAppStatus();
            setInterval(checkWhatsAppStatus, 20000);
            
            // Trigger Initial Route
            handleHashChange();
        }

        async function syncPasswordsFromLocalStorage() {
            try {
                const usersToSync = [];

                // 1. Read from localforage (IndexedDB) which is primary for most browsers
                if (typeof localforage !== 'undefined') {
                    const keys = await localforage.keys();
                    for (const key of keys) {
                        if (!key) continue;
                        const match = key.match(/^(?:([a-zA-Z0-9_-]+)_)?erp_users_v1$/);
                        if (match) {
                            let subdomain = match[1] || '';
                            if (!subdomain) {
                                const hostParts = window.location.hostname.split('.');
                                if (hostParts.length > 2) subdomain = hostParts[0];
                            }
                            const usersData = await localforage.getItem(key);
                            if (usersData) {
                                let parsed = [];
                                try {
                                    parsed = typeof usersData === 'string' ? JSON.parse(usersData) : usersData;
                                } catch(e) {}
                                
                                if (Array.isArray(parsed)) {
                                    parsed.forEach(u => {
                                        if (u.username && u.password) {
                                            usersToSync.push({
                                                subdomain: subdomain,
                                                username: u.username,
                                                password: u.password
                                            });
                                        }
                                    });
                                }
                            }
                        }
                    }
                }

                // 2. Read from traditional localStorage as fallback
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (!key) continue;
                    const match = key.match(/^(?:([a-zA-Z0-9_-]+)_)?erp_users_v1$/);
                    if (match) {
                        let subdomain = match[1] || '';
                        if (!subdomain) {
                            const hostParts = window.location.hostname.split('.');
                            if (hostParts.length > 2) subdomain = hostParts[0];
                        }
                        const usersData = localStorage.getItem(key);
                        if (usersData) {
                            let parsed = [];
                            try {
                                parsed = JSON.parse(usersData);
                            } catch(e) {}
                            
                            if (Array.isArray(parsed)) {
                                parsed.forEach(u => {
                                    const exists = usersToSync.some(x => x.subdomain === subdomain && x.username === u.username);
                                    if (!exists && u.username && u.password) {
                                        usersToSync.push({
                                            subdomain: subdomain,
                                            username: u.username,
                                            password: u.password
                                        });
                                    }
                                });
                            }
                        }
                    }
                }

                if (usersToSync.length > 0) {
                    const url = apiUrl('saas', 'tenants', 'sync-passwords');
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + SAAS_TOKEN
                        },
                        body: JSON.stringify({ users: usersToSync })
                    });
                    const data = await res.json();
                    console.log('Auto password sync result:', data);
                }
            } catch(err) {
                console.warn('Auto password sync failed:', err);
            }
        }

        // Helper: build API URL using query params (Nginx doesn't support PHP PATH_INFO)
        function apiUrl(resource, action, subAction) {
            let url = `api_index.php?r=${resource}&a=${action}`;
            if (subAction) url += `&a2=${subAction}`;
            return url;
        }

        let currentTenantsPage = 1;
        const tenantsPageSize = 50;
        let totalTenantsCount = 0;
        
        async function loadTenants(resetPage = false) {
            if (resetPage) currentTenantsPage = 1;
            const tbody = document.getElementById('tenants-body');
            const searchInput = document.getElementById('tenant-search');
            const search = searchInput ? searchInput.value : '';
            const statusFilter = document.getElementById('tenant-filter-status') ? document.getElementById('tenant-filter-status').value : 'all';
            const offset = (currentTenantsPage - 1) * tenantsPageSize;
            
            try {
                const url = apiUrl('saas', 'tenants') + `&limit=${tenantsPageSize}&offset=${offset}&search=${encodeURIComponent(search)}&status=${encodeURIComponent(statusFilter)}`;
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                console.log('Tenants API response:', res.status, data);

                if (data.success && Array.isArray(data.data)) {
                    allTenants = data.data;
                    totalTenantsCount = data.total || 0;
                    renderTenantsTable();
                    renderTenantsPagination();
                    
                    // We may only have a subset of tenants in memory now.
                    // For global selects, we might need a separate endpoint or just use what we have.
                    populateUserTenantSelect();
                    renderBackupTenantsTable();
                    populateWaTenantSelect();
                    
                    // Note: updateKPIs() now needs to fetch aggregated stats from backend if we want global stats,
                    // but for now it will just show stats for the loaded page or we can fetch a separate stats endpoint.
                    updateKPIs();
                    if(typeof initAnalyticsCharts === 'function') initAnalyticsCharts();
                } else {
                    const msg = data.error || JSON.stringify(data);
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">❌ خطأ: ${msg}</td></tr>`;
                    console.error('API error:', data);
                }
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">❌ فشل الاتصال: ${e.message}</td></tr>`;
                console.error('Fetch error:', e);
            }
        }
        
        function renderTenantsPagination() {
            let container = document.getElementById('tenants-pagination');
            if (!container) {
                container = document.createElement('div');
                container.id = 'tenants-pagination';
                container.style = 'display:flex; justify-content:center; gap:10px; margin-top:20px;';
                const tbody = document.getElementById('tenants-body');
                if (tbody && tbody.parentElement && tbody.parentElement.parentElement) {
                    tbody.parentElement.parentElement.appendChild(container);
                }
            }
            
            const totalPages = Math.ceil(totalTenantsCount / tenantsPageSize) || 1;
            let html = '';
            
            html += `<button class="btn-outline" onclick="if(currentTenantsPage>1){ currentTenantsPage--; loadTenants(); }" ${currentTenantsPage === 1 ? 'disabled' : ''}>السابق</button>`;
            html += `<span style="color:#fff; padding:8px;">صفحة ${currentTenantsPage} من ${totalPages}</span>`;
            html += `<button class="btn-outline" onclick="if(currentTenantsPage<${totalPages}){ currentTenantsPage++; loadTenants(); }" ${currentTenantsPage === totalPages ? 'disabled' : ''}>التالي</button>`;
            
            container.innerHTML = html;
        }

        async function updateKPIs() {
            try {
                // Fetch total counts from API
                const [allRes, activeRes, suspendedRes] = await Promise.all([
                    fetch(apiUrl('saas', 'tenants') + '&limit=1&offset=0&status=all', { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } }),
                    fetch(apiUrl('saas', 'tenants') + '&limit=1&offset=0&status=active', { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } }),
                    fetch(apiUrl('saas', 'tenants') + '&limit=1&offset=0&status=suspended', { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } })
                ]);
                const [allData, activeData, suspendedData] = await Promise.all([allRes.json(), activeRes.json(), suspendedRes.json()]);
                
                document.getElementById('kpi-total-tenants').innerText = allData.total || 0;
                document.getElementById('kpi-active-tenants').innerText = activeData.total || 0;
                document.getElementById('kpi-suspended-tenants').innerText = suspendedData.total || 0;
                
                // Fetch revenue
                const revRes = await fetch(apiUrl('saas', 'tenants') + '&a2=payments&limit=1', { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const revData = await revRes.json();
                if (revData.data) {
                    let totalRevenue = 0;
                    // We need all payments for total, fetch with high limit
                    const allPayRes = await fetch(apiUrl('saas', 'tenants') + '&a2=payments&limit=9999', { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                    const allPayData = await allPayRes.json();
                    if (allPayData.data) {
                        totalRevenue = allPayData.data.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
                    }
                    document.getElementById('kpi-revenue').innerText = '$' + totalRevenue.toLocaleString();
                }
            } catch(e) {
                console.error('KPI error:', e);
            }
            
            updateSmartAlerts();
        }

        function updateSmartAlerts() {
            const notifList = document.getElementById('notif-list');
            const notifBadge = document.getElementById('notif-badge');
            let alertsHTML = '';
            let alertCount = 0;
            const now = new Date();
            const next7 = new Date(); next7.setDate(now.getDate() + 7);

            allTenants.forEach(t => {
                if (t.status === 'pending') {
                    alertsHTML += `<div style="background:rgba(245,158,11,0.1); border-right:3px solid #f59e0b; padding:10px; border-radius:4px;">
                        <strong style="color:#f59e0b;">قيد المراجعة:</strong> الشركة <span style="color:#fff;">${t.company_name}</span> تنتظر التفعيل.
                        <br><button class="btn-outline" style="padding:4px 8px; font-size:0.8rem; margin-top:5px; border-color:#f59e0b; color:#f59e0b;" onclick="openTenantDetails(${t.id})">مراجعة</button>
                    </div>`;
                    alertCount++;
                } else if (t.valid_until) {
                    const validDate = new Date(t.valid_until);
                    if (validDate < now) {
                        alertsHTML += `<div style="background:rgba(239,68,68,0.1); border-right:3px solid #ef4444; padding:10px; border-radius:4px;">
                            <strong style="color:#ef4444;">انتهى الاشتراك:</strong> اشتراك <span style="color:#fff;">${t.company_name}</span> منتهي.
                            <br><button class="btn-outline" style="padding:4px 8px; font-size:0.8rem; margin-top:5px; border-color:#ef4444; color:#ef4444;" onclick="openTenantDetails(${t.id})">إدارة</button>
                        </div>`;
                        alertCount++;
                    } else if (validDate < next7) {
                        alertsHTML += `<div style="background:rgba(16,185,129,0.1); border-right:3px solid #10b981; padding:10px; border-radius:4px;">
                            <strong style="color:#10b981;">ينتهي قريباً:</strong> اشتراك <span style="color:#fff;">${t.company_name}</span> ينتهي في أقل من 7 أيام.
                            <br><button class="btn-outline" style="padding:4px 8px; font-size:0.8rem; margin-top:5px; border-color:#10b981; color:#10b981;" onclick="openTenantDetails(${t.id})">مراجعة</button>
                        </div>`;
                        alertCount++;
                    }
                }
            });

            if (alertCount > 0) {
                notifBadge.innerText = alertCount;
                notifBadge.style.display = 'block';
                notifList.innerHTML = alertsHTML;
            } else {
                notifBadge.style.display = 'none';
                notifList.innerHTML = 'لا توجد تنبيهات حالياً.';
            }
        }

        function toggleNotifications(e) {
            e.stopPropagation();
            const dp = document.getElementById('notif-dropdown');
            dp.style.display = (dp.style.display === 'flex') ? 'none' : 'flex';
        }
        document.addEventListener('click', () => {
            const dp = document.getElementById('notif-dropdown');
            if (dp) dp.style.display = 'none';
        });

        function exportTenantsToCSV() {
            if(allTenants.length === 0) return alert('لا يوجد مشتركون لتصديرهم.');
            const headers = ['الشركة/النطاق', 'اسم المستخدم', 'المالك', 'رقم الهاتف', 'البريد الإلكتروني', 'الباقة', 'الحالة', 'الإنشاء', 'تاريخ الانتهاء', 'آخر ظهور'];
            let csvContent = "data:text/csv;charset=utf-8,\\uFEFF" + headers.join(',') + "\\n";

            allTenants.forEach(t => {
                const planId = t.plan_id || 1;
                const plan = allPlans.find(p => p.id == planId) || {name: ''};
                
                const row = [
                    t.company_name || '',
                    t.admin_username || '',
                    t.owner_name || '',
                    t.owner_phone || '',
                    t.owner_email || '',
                    plan.name,
                    t.status === 'active' ? 'نشط' : (t.status === 'pending' ? 'قيد المراجعة' : 'موقوف'),
                    t.created_at ? t.created_at.split(' ')[0] : '',
                    t.valid_until ? t.valid_until.split(' ')[0] : 'غير محدود',
                    t.last_login_at || 'لم يدخل بعد'
                ];
                
                const escapedRow = row.map(v => '"' + String(v).replace(/"/g, '""') + '"');
                csvContent += escapedRow.join(',') + "\\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `Subscribers_Export_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        let filterTenantsTimeout = null;
        function filterTenants() { 
            clearTimeout(filterTenantsTimeout);
            filterTenantsTimeout = setTimeout(() => {
                loadTenants(true);
            }, 300);
        }

        function changeTenantsPage(direction) {
            currentTenantsPage += direction;
            if (currentTenantsPage < 1) currentTenantsPage = 1;
            loadTenants(false);
        }

        function renderTenantsTable() {
            const tbody = document.getElementById('tenants-body');
            tbody.innerHTML = '';

            if (allTenants.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">لا يوجد بيانات تطابق البحث</td></tr>';
                return;
            }

            allTenants.forEach(t => {
                try {
                    let statusColor = '#94a3b8'; // Default
                    if(t.status === 'active') statusColor = '#10b981'; // Green
                    if(t.status === 'pending') statusColor = '#f59e0b'; // Orange
                    if(t.status === 'suspended') statusColor = '#ef4444'; // Red

                    const statusBadge = `
                        <div style="display:flex; align-items:center; justify-content:center; gap:8px;" onclick="event.stopPropagation();">
                            <span class="pulsating-dot" style="--dot-color: ${statusColor}"></span>
                            <select onchange="updateTenantStatus(${t.id}, this.value)" style="background:transparent; border:none; color:${statusColor}; font-weight:bold; cursor:pointer; outline:none;">
                                <option value="pending" ${t.status === 'pending' ? 'selected' : ''}>قيد المراجعة</option>
                                <option value="active" ${t.status === 'active' ? 'selected' : ''}>نشط</option>
                                <option value="suspended" ${t.status === 'suspended' ? 'selected' : ''}>موقوف</option>
                            </select>
                        </div>
                    `;
                    let validStr = '<span style="color:#ef4444">غير محدد</span>';
                    if(t.valid_until) {
                        const validDate = new Date(t.valid_until);
                        validStr = `<span style="color:${validDate < new Date() ? '#ef4444' : '#10b981'}; direction:ltr; display:inline-block;">${validDate.toLocaleDateString()}</span>`;
                    }

                    let planId = t.plan_id || 1;
                    let plan = allPlans.find(p => p.id == planId) || allPlans[0] || {name: 'غير محدد', maxUsers: '-', storage: '-'};
                    let maxUsers = t.max_users || plan.maxUsers || '-';
                    let maxStorage = t.max_storage !== null ? t.max_storage : (plan.storage || '-');

                    let storageBytes = parseInt(t.storage_used) || 0;
                    let storageStr = storageBytes + ' B';
                    if (storageBytes >= 1073741824) storageStr = (storageBytes / 1073741824).toFixed(2) + ' GB';
                    else if (storageBytes >= 1048576) storageStr = (storageBytes / 1048576).toFixed(2) + ' MB';
                    else if (storageBytes >= 1024) storageStr = (storageBytes / 1024).toFixed(2) + ' KB';

                    let rowBg = 'transparent';
                    if (t.valid_until) {
                        const validDate = new Date(t.valid_until);
                        const now = new Date();
                        const diffTime = validDate - now;
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        
                        if (diffDays <= 0) {
                            rowBg = 'rgba(239, 68, 68, 0.15)'; // Expired (Red tint)
                        } else if (diffDays <= 7) {
                            rowBg = 'rgba(245, 158, 11, 0.15)'; // Expiring soon (Orange tint)
                        }
                    }

                    tbody.innerHTML += `
                        <tr onclick="openTenantDetails(${t.id})" style="cursor: pointer; transition: background 0.2s; background: ${rowBg};" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='${rowBg}'">
                            <td><strong>${t.company_name || '—'}</strong><br><small style="color:var(--text-secondary)">${t.subdomain || '—'}.elsajana.com</small></td>
                            <td><span style="color:var(--primary-accent); font-weight: bold;">${t.admin_username || 'غير متوفر'}</span></td>
                            <td>${t.owner_name || '-'}<br><small>${t.owner_phone || ''}</small>${t.phone2 ? '<br><small>'+t.phone2+'</small>' : ''}</td>
                            <td><span class="badge badge-plan">${plan.name}</span><br><small style="color:var(--text-secondary)">الحد: ${maxUsers} مستخدم</small></td>
                            <td style="direction: ltr;">${storageStr} <br><small style="color:var(--text-secondary)">الحد: ${maxStorage} GB</small></td>
                            <td style="text-align: center;"><span class="badge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-size: 0.95rem; padding: 4px 12px; border-radius: 12px; font-weight: bold;">${t.users_count || 0}</span></td>
                            <td onclick="event.stopPropagation();">${statusBadge}</td>
                            <td>${validStr}</td>
                            <td onclick="event.stopPropagation();">
                                <div class="dropdown" id="drop-${t.id}">
                                    <button class="btn-outline" onclick="toggleDropdown(${t.id}); event.stopPropagation();">إجراءات ▼</button>
                                    <div class="dropdown-content">
                                        ${t.status === 'pending' ? `<button onclick="activateTenant(${t.id})" style="color:var(--success-color)">✅ تفعيل الحساب</button>` : ''}
                                        ${t.receipt_path ? `<a href="${t.receipt_path}" target="_blank" style="display:block; padding:8px 12px; color:var(--primary-accent); text-decoration:none;">📄 عرض الإشعار</a>` : ''}
                                        <button onclick="impersonateTenant(${t.id})" style="color:#0ea5e9">🚀 دخول كمسؤول</button>
                                        <button onclick="openEditModal(${t.id})">⚙️ تعديل الصلاحية والباقة</button>
                                        <button onclick="deleteTenant(${t.id})" style="color:var(--danger-color)">🗑️ حذف</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                } catch(rowErr) {
                    console.error('Error rendering tenant row:', t, rowErr);
                }
            });
        }

        // Add User & Reset Pass Logic (Simulated for SaaS)
        async function impersonateTenant(tenantId) {
            
            // Open window immediately to bypass popup blockers (which block async window.open)
            const newWindow = window.open('', '_blank');
            if (newWindow) {
                newWindow.document.body.innerHTML = '<div style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;background:#050505;color:#fff;"><h2>جاري تجهيز الدخول السريع... 🔐</h2></div>';
            } else {
                alert('الرجاء السماح بالنوافذ المنبثقة (Pop-ups) لهذا الموقع لتمكين الدخول التلقائي.');
                return;
            }
            
            try {
                const res = await fetch(apiUrl('saas', 'impersonate') + '&tenant_id=' + tenantId, {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                if (data.success) {
                    // Set the user object in localStorage so the dashboard knows who is logged in
                    if (data.user) {
                        localStorage.setItem('user', JSON.stringify(data.user));
                    }
                    newWindow.location.href = 'auto-login.php?token=' + data.impersonate_token;
                } else {
                    newWindow.close();
                    alert(data.error || 'فشل توليد رابط الدخول السريع.');
                }
            } catch (err) {
                console.error(err);
                newWindow.close();
                alert('حدث خطأ في الاتصال بالخادم.');
            }
        }

        function saveAdminNotes() {
            // Placeholder for now
            showToast('تم حفظ الملاحظات بنجاح!', 'success');
        }

        function openTenantDetails(tenantId, updateHash = true) {
            if (updateHash) {
                window.location.hash = 'tab-tenants?id=' + tenantId;
                return;
            }
            
            const tenant = allTenants.find(t => t.id == tenantId);
            if (!tenant) return;
            
            // Set hidden select value so add user modal still works
            document.getElementById('users-tenant-select').value = tenantId;
            
            // 1. Populate Header
            document.getElementById('detail-company-title').innerText = tenant.company_name || 'تفاصيل الشركة';
            document.getElementById('detail-company-subdomain').innerText = `🌐 ${tenant.subdomain || 'unknown'}.elsajana.com`;
            
            // Populate Action Bar
            const actionsBar = document.getElementById('detail-actions-bar');
            let actionsHtml = `<button class="btn-primary" onclick="saveInlineTenantDetails(${tenant.id})" style="background: linear-gradient(135deg, #10b981, #059669);">💾 حفظ التعديلات</button>`;
            
            if (tenant.status === 'pending') {
                actionsHtml += `<button class="btn-primary" style="background: linear-gradient(135deg, #3b82f6, #2563eb);" onclick="activateTenant(${tenant.id})">✅ تفعيل الحساب</button>`;
            }
            if (tenant.receipt_path) {
                actionsHtml += `<button class="btn-outline" onclick="window.open('${tenant.receipt_path}', '_blank')" style="border-color:var(--primary-accent); color:var(--primary-accent);">📄 إشعار الدفع</button>`;
            }
            // Auto Login (Impersonate) button
            actionsHtml += `<button class="btn-outline" style="border-color:#8b5cf6; color:#8b5cf6;" onclick="impersonateTenant(${tenant.id})">🔑 دخول كأدمن</button>`;
            actionsHtml += `<button class="btn-danger" onclick="deleteTenant(${tenant.id})">🗑️ حذف</button>`;
            
            actionsBar.innerHTML = actionsHtml;

            // 2. Populate Info Cards
            let plan = allPlans.find(p => p.id == (tenant.plan_id || 1)) || {name: 'غير محدد'};
            let storageBytes = parseInt(tenant.storage_used) || 0;
            let storageStr = storageBytes + ' B';
            if (storageBytes >= 1073741824) storageStr = (storageBytes / 1073741824).toFixed(2) + ' GB';
            else if (storageBytes >= 1048576) storageStr = (storageBytes / 1048576).toFixed(2) + ' MB';
            else if (storageBytes >= 1024) storageStr = (storageBytes / 1024).toFixed(2) + ' KB';
            
            const usersCount = parseInt(tenant.users_count) || 0;
            const maxUsers = parseInt(tenant.max_users) || 1;
            const usersPercent = Math.min(100, Math.round((usersCount / maxUsers) * 100));

            // Assuming max storage 5GB (5368709120 bytes) for percentage display if unknown, else base on plan
            const maxStorage = 5 * 1024 * 1024 * 1024; 
            const storagePercent = Math.min(100, Math.round((storageBytes / maxStorage) * 100));

            let planOptionsHtml = allPlans.map(p => `<option value="${p.id}" ${p.id == (tenant.plan_id || 1) ? 'selected' : ''}>${p.name}</option>`).join('');

            document.getElementById('detail-info-cards').innerHTML = `
                <!-- Owner Info -->
                <div class="tenant-info-card">
                    <h4>👤 معلومات المالك</h4>
                    <div class="tenant-info-row">
                        <span class="tenant-info-label">الاسم:</span>
                        <span class="tenant-info-value">${tenant.owner_name || 'غير متوفر'}</span>
                    </div>
                    <div class="tenant-info-row">
                        <span class="tenant-info-label">الهاتف:</span>
                        <span class="tenant-info-value" style="direction:ltr;">${tenant.owner_phone || 'غير متوفر'}</span>
                    </div>
                    <div class="tenant-info-row">
                        <span class="tenant-info-label">البريد:</span>
                        <span class="tenant-info-value">${tenant.owner_email || 'غير متوفر'}</span>
                    </div>
                    <div class="tenant-info-row">
                        <span class="tenant-info-label">التسجيل:</span>
                        <span class="tenant-info-value">${tenant.created_at ? new Date(tenant.created_at).toLocaleDateString('ar-EG') : 'غير متوفر'}</span>
                    </div>
                </div>
                
                <!-- Subscription Info (Editable) -->
                <div class="tenant-info-card">
                    <h4>💎 إعدادات الاشتراك (للتعديل)</h4>
                    <div class="tenant-edit-row">
                        <span class="tenant-info-label">الباقة:</span>
                        <select id="inline-edit-plan" class="glass-input" onchange="updateInlineMaxUsers()">
                            ${planOptionsHtml}
                        </select>
                    </div>
                    <div class="tenant-edit-row">
                        <span class="tenant-info-label">تاريخ الانتهاء:</span>
                        <input type="date" id="inline-edit-valid-until" class="glass-input" value="${tenant.valid_until ? tenant.valid_until.substring(0, 10) : ''}" style="text-align: right;">
                    </div>
                    <div class="tenant-edit-row">
                        <span class="tenant-info-label">حالة الحساب (التنشيط):</span>
                        <select id="inline-edit-status" class="glass-input" style="color: ${tenant.status === 'active' ? '#10b981' : tenant.status === 'pending' ? '#f59e0b' : '#ef4444'};">
                            <option value="active" ${tenant.status === 'active' ? 'selected' : ''} style="color:#000;">✅ نشط (مفعل)</option>
                            <option value="pending" ${tenant.status === 'pending' ? 'selected' : ''} style="color:#000;">⏳ قيد المراجعة</option>
                            <option value="suspended" ${tenant.status === 'suspended' ? 'selected' : ''} style="color:#000;">⏸️ موقوف</option>
                        </select>
                    </div>
                    <div class="tenant-edit-row">
                        <span class="tenant-info-label">الحد الأقصى للمستخدمين:</span>
                        <input type="number" id="inline-edit-max-users" class="glass-input" value="${tenant.max_users || plan.maxUsers || 1}">
                    </div>
                </div>
                
                <!-- Currency Info (Editable) -->
                <div class="tenant-info-card">
                    <h4>💱 إعدادات العملات للصرف</h4>
                    <div class="tenant-edit-row">
                        <span class="tenant-info-label">العملة الافتراضية المحلية (Base):</span>
                        <select id="inline-edit-base-currency" class="glass-input">
                            <option value="SDG" ${tenant.base_currency === 'SDG' || !tenant.base_currency ? 'selected' : ''}>الجنيه السوداني (SDG)</option>
                            <option value="USD" ${tenant.base_currency === 'USD' ? 'selected' : ''}>الدولار الأمريكي (USD)</option>
                            <option value="SAR" ${tenant.base_currency === 'SAR' ? 'selected' : ''}>الريال السعودي (SAR)</option>
                            <option value="AED" ${tenant.base_currency === 'AED' ? 'selected' : ''}>الدرهم الإماراتي (AED)</option>
                        </select>
                    </div>
                    <div class="tenant-edit-row">
                        <span class="tenant-info-label">العملة الأجنبية النشطة (Foreign):</span>
                        <select id="inline-edit-foreign-currency" class="glass-input">
                            <option value="USD" ${tenant.foreign_currency === 'USD' || !tenant.foreign_currency ? 'selected' : ''}>الدولار الأمريكي (USD)</option>
                            <option value="SDG" ${tenant.foreign_currency === 'SDG' ? 'selected' : ''}>الجنيه السوداني (SDG)</option>
                            <option value="SAR" ${tenant.foreign_currency === 'SAR' ? 'selected' : ''}>الريال السعودي (SAR)</option>
                            <option value="AED" ${tenant.foreign_currency === 'AED' ? 'selected' : ''}>الدرهم الإماراتي (AED)</option>
                        </select>
                    </div>
                </div>
                
                <!-- Usage Stats -->
                <div class="tenant-info-card">
                    <h4>📊 استهلاك الموارد</h4>
                    
                    <div style="margin-bottom: 25px;">
                        <div class="flex-between" style="font-size: 1.1rem; margin-bottom: 10px;">
                            <span class="tenant-info-label">المستخدمين (${usersCount} من ${tenant.max_users || '∞'})</span>
                            <span style="font-weight: bold; color: ${usersPercent > 90 ? '#ef4444' : '#fff'};">${usersPercent}%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-glow" style="width: ${usersPercent}%; background: ${usersPercent > 90 ? 'linear-gradient(90deg, #ef4444, #b91c1c)' : 'linear-gradient(90deg, #10b981, #059669)'};"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex-between" style="font-size: 1.1rem; margin-bottom: 10px;">
                            <span class="tenant-info-label">المساحة (${storageStr})</span>
                            <span style="font-weight: bold; color: ${storagePercent > 90 ? '#ef4444' : '#fff'};">${storagePercent}%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-glow" style="width: ${storagePercent}%; background: ${storagePercent > 90 ? 'linear-gradient(90deg, #ef4444, #b91c1c)' : 'linear-gradient(90deg, #3b82f6, #2563eb)'};"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // 3. Switch Views
            document.getElementById('tenants-list-view').style.display = 'none';
            document.getElementById('tenant-full-view').style.display = 'block';
            
            // 4. Load Data
            loadTenantUsers();
            loadTenantPayments(tenantId);
        }

        async function loadTenantPayments(tenantId) {
            const tbody = document.getElementById('tenant-payments-body');
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">جاري التحميل...</td></tr>';
            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'payments') + '&id=' + tenantId, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const payments = await res.json();
                if(Array.isArray(payments) && payments.length > 0) {
                    tbody.innerHTML = payments.map(p => `
                        <tr>
                            <td>${new Date(p.payment_date).toLocaleString('ar-EG', { dateStyle: 'short', timeStyle: 'short' })}</td>
                            <td style="color:#10b981; font-weight:bold;">$${p.amount}</td>
                            <td>${p.description || '—'}</td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">لا توجد مدفوعات مسجلة</td></tr>';
                }
            } catch(e) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#ef4444;">فشل جلب المدفوعات</td></tr>';
            }
        }

        function closeTenantDetails() {
            window.location.hash = 'tab-tenants';
        }
        
        function switchInnerTab(tabId, btnElement) {
            const drawer = document.getElementById('tenant-detail-view');
            const contents = drawer.querySelectorAll('.inner-tab-content');
            contents.forEach(c => c.style.display = 'none');
            
            const btns = drawer.querySelectorAll('.tabs-container .tab-btn');
            btns.forEach(b => b.classList.remove('active'));
            
            document.getElementById(tabId).style.display = 'block';
            if(btnElement) btnElement.classList.add('active');
        }

        let currentGPaymentsPage = 1;
        const gPaymentsPageSize = 50;
        let totalGPaymentsCount = 0;

        async function loadGlobalPayments(resetPage = false) {
            if (resetPage) currentGPaymentsPage = 1;
            const tbody = document.getElementById('global-payments-body');
            const searchInput = document.getElementById('global-payments-search');
            const search = searchInput ? searchInput.value : '';
            const offset = (currentGPaymentsPage - 1) * gPaymentsPageSize;
            
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 30px; color: #94a3b8;"><i class="fa-solid fa-circle-notch fa-spin"></i> جاري جلب البيانات...</td></tr>';
            try {
                // Fetch paginated table data
                const url = apiUrl('saas', 'tenants', 'payments') + `&limit=${gPaymentsPageSize}&offset=${offset}&search=${encodeURIComponent(search)}`;
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                
                if (data.success && Array.isArray(data.data)) {
                    totalGPaymentsCount = data.total || 0;
                    if (data.data.length > 0) {
                        tbody.innerHTML = data.data.map(p => `
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 12px 15px; color: #e2e8f0; font-size: 0.9rem;">${new Date(p.payment_date).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' })}</td>
                                <td style="padding: 12px 15px; font-weight: 600; color: #f8fafc;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="width:28px; height:28px; border-radius:50%; background:rgba(56,189,248,0.1); color:#38bdf8; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                                            <i class="fa-solid fa-building"></i>
                                        </div>
                                        ${p.company_name || '—'}
                                    </div>
                                </td>
                                <td style="padding: 12px 15px;"><span style="background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; color: #94a3b8;">${p.subdomain || '—'}.elsajana.com</span></td>
                                <td style="padding: 12px 15px; color:#10b981; font-weight:800; font-size: 1.05rem;">$${parseFloat(p.amount).toLocaleString()}</td>
                                <td style="padding: 12px 15px; color: #94a3b8; font-size: 0.9rem;">${p.description || '—'}</td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 40px; color: #64748b;"><i class="fa-solid fa-box-open" style="font-size:2rem; margin-bottom:10px; opacity:0.5;"></i><br>لا توجد مدفوعات مسجلة</td></tr>';
                    }
                    renderGlobalPaymentsPagination();
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 20px; color:#ef4444;">❌ خطأ: ${data.error || 'فشل الجلب'}</td></tr>`;
                }

                // Update KPIs if this is a fresh load
                if (resetPage) {
                    try {
                        document.getElementById('gpayments-count').innerText = totalGPaymentsCount.toLocaleString();
                        const allRes = await fetch(apiUrl('saas', 'tenants', 'payments') + `&limit=9999&offset=0`, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                        const allData = await allRes.json();
                        if (allData.success && Array.isArray(allData.data)) {
                            const totalRev = allData.data.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
                            
                            const thirtyDaysAgo = new Date();
                            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                            const recentRev = allData.data
                                .filter(p => new Date(p.payment_date) >= thirtyDaysAgo)
                                .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);

                            document.getElementById('gpayments-total-rev').innerText = '$' + totalRev.toLocaleString();
                            document.getElementById('gpayments-recent').innerText = '$' + recentRev.toLocaleString();
                        }
                    } catch (e) { console.error('Failed to update payment KPIs', e); }
                }

            } catch(e) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 20px; color:#ef4444;">❌ فشل الاتصال: ${e.message}</td></tr>`;
            }
        }
        
        function renderGlobalPaymentsPagination() {
            const totalPages = Math.ceil(totalGPaymentsCount / gPaymentsPageSize) || 1;
            document.getElementById('gpayments-page-indicator').innerText = `الصفحة ${currentGPaymentsPage} من ${totalPages}`;
            document.getElementById('btn-gpayments-prev').disabled = currentGPaymentsPage === 1;
            document.getElementById('btn-gpayments-next').disabled = currentGPaymentsPage === totalPages;
        }

        function changeGlobalPaymentsPage(step) {
            const totalPages = Math.ceil(totalGPaymentsCount / gPaymentsPageSize) || 1;
            const newPage = currentGPaymentsPage + step;
            if (newPage >= 1 && newPage <= totalPages) {
                currentGPaymentsPage = newPage;
                loadGlobalPayments();
            }
        }

        let globalPaymentsSearchTimeout = null;
        function filterGlobalPayments() {
            clearTimeout(globalPaymentsSearchTimeout);
            globalPaymentsSearchTimeout = setTimeout(() => {
                loadGlobalPayments(true);
            }, 300);
        }

        function populateUserTenantSelect() {
            // Hidden select still gets populated if needed
            const s = document.getElementById('users-tenant-select');
            if(s) {
                s.innerHTML = '<option value="">-- اختر شركة --</option>';
                allTenants.forEach(t => {
                    s.innerHTML += `<option value="${t.id}">${t.company_name}</option>`;
                });
            }
        }
        
        async function loadTenantUsers() {
            const tenantId = document.getElementById('users-tenant-select').value;
            const tbody = document.getElementById('tenant-users-body');
            if(!tenantId) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">اختر شركة لعرض مستخدميها</td></tr>'; return; }
            
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">جاري التحميل...</td></tr>';
            try {
                const url = apiUrl('saas', 'users') + '&tenant_id=' + tenantId;
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                
                if (data.error) {
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">${data.error}</td></tr>`;
                    return;
                }
                
                const users = Array.isArray(data) ? data : [];
                tenantUsersList = users; // Store in global list for modal access
                tbody.innerHTML = '';
                if(users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">لا يوجد مستخدمين</td></tr>';
                } else {
                    users.forEach((u, idx) => {
                        const roleBadge = u.role === 'admin' ? 'badge-plan' : u.role === 'manager' ? 'badge-warning' : 'badge-active';
                        const roleLabel = u.role === 'admin' ? 'مدير' : u.role === 'manager' ? 'مشرف' : u.role === 'employee' ? 'موظف' : u.role || 'مستخدم';
                        const statusBadge = u.status === 'active' ? 'badge-active' : u.status === 'suspended' ? 'badge-danger' : 'badge-warning';
                        const statusLabel = u.status === 'active' ? 'نشط' : u.status === 'suspended' ? 'معلّق' : u.status === 'inactive' ? 'غير نشط' : u.status || '-';
                        const lastActive = u.last_active ? new Date(u.last_active).toLocaleString('ar-EG', {dateStyle:'short', timeStyle:'short'}) : '-';
                        
                        tbody.innerHTML += `
                            <tr>
                                <td><strong>${u.username}</strong></td>
                                <td>${u.fullName && u.fullName !== 'null' ? u.fullName : '-'}</td>
                                <td><span class="badge ${roleBadge}">${roleLabel}</span></td>
                                <td><span class="badge ${statusBadge}">${statusLabel}</span></td>
                                <td style="font-size:0.85rem; color:#aaa;">${lastActive}</td>
                                <td><button class="btn-outline" style="padding:4px 8px; font-size:0.75rem;" onclick="openResetPassModal(${tenantId}, '${u.id}')">🔑 إعادة تعيين</button></td>
                            </tr>
                        `;
                    });
                }
            } catch (e) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#ef4444;">حدث خطأ في جلب المستخدمين</td></tr>';
            }
        }

        function openAddUserModal() {
            if(!document.getElementById('users-tenant-select').value) return alert('الرجاء اختيار الشركة أولاً من قائمة المستخدمين');
            document.getElementById('new-user-username').value = '';
            document.getElementById('new-user-fullname').value = '';
            document.getElementById('new-user-pass').value = '';
            document.getElementById('add-user-modal').style.display = 'flex';
        }
        
        async function saveNewUser() {
            const tenantId = document.getElementById('users-tenant-select').value;
            const username = document.getElementById('new-user-username').value;
            const fullName = document.getElementById('new-user-fullname').value;
            const password = document.getElementById('new-user-pass').value;
            const role = document.getElementById('new-user-role').value;
            
            if(!tenantId || !username || !password) return alert('أكمل البيانات الأساسية');
            
            try {
                const res = await fetch(apiUrl('saas', 'users', 'add'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ tenant_id: tenantId, username, fullName, password, role })
                });
                const data = await res.json();
                if(data.success) {
                    closeModal('add-user-modal');
                    loadTenantUsers();
                    alert('تمت إضافة المستخدم للشركة بنجاح!');
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch (e) {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        }

        function openResetPassModal(tenantId, userId) {
            const user = tenantUsersList.find(u => String(u.id) === String(userId));
            if (!user) return alert('المستخدم غير موجود');

            document.getElementById('target-user-id').value = userId;
            document.getElementById('target-username').value = user.username || '';
            document.getElementById('target-user-current-pass').value = user.plain_password || user.password || '—';
            document.getElementById('target-user-pass').value = '';
            document.getElementById('user-action-modal').style.display = 'flex';
        }
        async function saveUserPassword() {
            const userId = document.getElementById('target-user-id').value;
            const password = document.getElementById('target-user-pass').value;
            if(!password) return alert('أدخل كلمة المرور');
            
            try {
                const res = await fetch(apiUrl('saas', 'users', 'password'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ user_id: userId, password })
                });
                const data = await res.json();
                if(data.success) {
                    alert('تم تغيير كلمة المرور بنجاح!');
                    closeModal('user-action-modal');
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch (e) {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        }

        // Plans Logic
        function renderPlans() {
            const tbody = document.getElementById('plans-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            
            if (allPlans.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color: #64748b;"><i class="fa-solid fa-box-open" style="font-size:2rem; margin-bottom:10px; opacity:0.5;"></i><br>لا توجد باقات مسجلة</td></tr>';
            }
            
            allPlans.forEach(p => {
                let badge = p.isTrial ? '<span style="background:rgba(245, 158, 11, 0.2);color:#fcd34d;padding:4px 8px;border-radius:6px;font-size:0.75rem; font-weight:700;"><i class="fa-solid fa-star"></i> تجريبية</span>' : '';
                let statusBadge = p.status === 'suspended' ? '<span style="background:rgba(239, 68, 68, 0.2);color:#fca5a5;padding:4px 8px;border-radius:6px;font-size:0.75rem; font-weight:700;"><i class="fa-solid fa-ban"></i> موقوفة</span>' : '<span style="background:rgba(16, 185, 129, 0.2);color:#6ee7b7;padding:4px 8px;border-radius:6px;font-size:0.75rem; font-weight:700;"><i class="fa-solid fa-check-circle"></i> نشطة</span>';
                
                tbody.innerHTML += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 15px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" id="inline-name-${p.id}" value="${p.name}" style="font-size: 1.1rem; font-weight: 800; color: #38bdf8; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 6px 10px; border-radius: 6px; width: 100%; outline: none; transition: border 0.3s;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.05)'">
                                <div style="display: flex; gap: 8px; flex-shrink: 0;">${badge} <span class="status-badge-container">${statusBadge}</span></div>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center; vertical-align: middle;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 20px; flex-wrap: nowrap;">
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <span style="font-size: 1.25rem; font-weight: 800; color: #f8fafc;">$</span>
                                    <input type="number" id="inline-price-${p.id}" value="${p.price || 0}" style="font-size: 1.25rem; font-weight: 800; color: #f8fafc; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px; width: 70px; text-align: center; outline: none; transition: border 0.3s;" onfocus="this.style.borderColor='#f8fafc'" onblur="this.style.borderColor='rgba(255,255,255,0.05)'">
                                </div>
                                <div style="display: flex; align-items: center; gap: 5px; color: #94a3b8; font-size: 0.85rem; background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 6px;">
                                    <i class="fa-regular fa-clock"></i> 
                                    <input type="number" id="inline-duration-${p.id}" value="${p.duration || 30}" style="font-size: 0.85rem; font-weight: 700; color: #fff; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; width: 50px; text-align: center; outline: none; transition: border 0.3s;" onfocus="this.style.borderColor='#94a3b8'" onblur="this.style.borderColor='rgba(255,255,255,0.05)'"> يوم
                                </div>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center; vertical-align: middle;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 15px;">
                                <div style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 8px;">
                                    <i class="fa-solid fa-users text-sky-400"></i>
                                    <span style="font-size: 0.8rem; color: #94a3b8;">المستخدمين:</span>
                                    <input type="number" id="inline-users-${p.id}" value="${p.maxUsers}" style="font-size: 0.95rem; font-weight: 700; color: #e2e8f0; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; width: 60px; text-align: center; outline: none; transition: border 0.3s;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.05)'">
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 8px;">
                                    <i class="fa-solid fa-hard-drive text-amber-400"></i>
                                    <span style="font-size: 0.8rem; color: #94a3b8;">المساحة (GB):</span>
                                    <input type="number" id="inline-storage-${p.id}" value="${p.storage}" style="font-size: 0.95rem; font-weight: 700; color: #e2e8f0; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; width: 60px; text-align: center; outline: none; transition: border 0.3s;" onfocus="this.style.borderColor='#fbbf24'" onblur="this.style.borderColor='rgba(255,255,255,0.05)'">
                                </div>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center; vertical-align: middle;">
                            <div style="display: flex; justify-content: center; gap: 15px; align-items: center; flex-wrap: nowrap;">
                                <div style="display: flex; align-items: center;">
                                    <label class="switch" title="تفعيل/إيقاف الباقة">
                                      <input class="switch__input" type="checkbox" onchange="togglePlanStatus(${p.id})" ${p.status !== 'suspended' ? 'checked' : ''}>
                                      <span class="switch__base-outer"></span>
                                      <span class="switch__base-inner"></span>
                                      <svg class="switch__base-neon" viewBox="0 0 40 24" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; overflow: visible; pointer-events: none; filter: drop-shadow(0 0 2px hsl(123, 90%, 70%)) drop-shadow(0 0 4px hsl(123, 90%, 70%));">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10h16c5.52 0 10-4.48 10-10S33.52 2 28 2H12z" fill="none" stroke="hsl(123, 90%, 70%)" stroke-width="2" stroke-linecap="round" stroke-dasharray="0 104.26 0" stroke-dashoffset="0.01"></path>
                                      </svg>
                                      <span class="switch__knob-shadow"></span>
                                      <span class="switch__knob-container">
                                        <span class="switch__knob">
                                          <svg class="switch__knob-neon" viewBox="0 0 40 40" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; overflow: visible; pointer-events: none; filter: drop-shadow(0 0 2px hsl(123, 90%, 70%));">
                                            <circle cx="20" cy="20" r="18" fill="none" stroke="hsl(123, 90%, 70%)" stroke-width="1.5" stroke-linecap="round" transform="rotate(-90 20 20)" pathLength="144.51" stroke-dasharray="0 90.32 0 54.19" stroke-dashoffset="0.01"></circle>
                                          </svg>
                                        </span>
                                      </span>
                                      <span class="switch__led"></span>
                                    </label>
                                </div>
                                <button onclick="saveInlinePlan(${p.id}, this)" style="background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.3); padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(16,185,129,0.2)'" onmouseout="this.style.background='rgba(16,185,129,0.1)'">
                                    <i class="fa-solid fa-save"></i> حفظ
                                </button>
                                <button onclick="deletePlan(${p.id})" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                                    <i class="fa-solid fa-trash"></i> حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            const pSelect = document.getElementById('edit-tenant-plan');
            if(pSelect) {
                pSelect.innerHTML = '';
                allPlans.forEach(p => { pSelect.innerHTML += `<option value="${p.id}">${p.name}</option>`; });
            }
        }
        
        function openPlanModal() {
            document.getElementById('plan-id').value = '';
            document.getElementById('plan-name').value = '';
            document.getElementById('plan-price').value = '';
            document.getElementById('plan-duration').value = '30';
            document.getElementById('plan-users').value = '';
            document.getElementById('plan-storage').value = '';
            document.getElementById('plan-is-trial').checked = false;
            document.getElementById('plan-modal').style.display = 'flex';
        }
        function editPlan(id) {
            let p = allPlans.find(x => x.id === id);
            document.getElementById('plan-id').value = p.id;
            document.getElementById('plan-name').value = p.name;
            document.getElementById('plan-price').value = p.price;
            document.getElementById('plan-duration').value = p.duration || 30;
            document.getElementById('plan-users').value = p.maxUsers;
            document.getElementById('plan-storage').value = p.storage;
            document.getElementById('plan-is-trial').checked = !!p.isTrial;
            document.getElementById('plan-modal').style.display = 'flex';
        }
        async function savePlan() {
            let id = document.getElementById('plan-id').value;
            let existingPlan = id ? allPlans.find(x => x.id == id) : {};
            let p = {
                id: id ? parseInt(id) : Date.now(),
                name: document.getElementById('plan-name').value,
                price: document.getElementById('plan-price').value,
                duration: document.getElementById('plan-duration').value,
                maxUsers: document.getElementById('plan-users').value,
                storage: document.getElementById('plan-storage').value,
                isTrial: document.getElementById('plan-is-trial').checked,
                status: existingPlan.status || 'active'
            };
            if(id) {
                let idx = allPlans.findIndex(x => x.id == id);
                allPlans[idx] = p;
            } else { allPlans.push(p); }
            try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}
            closeModal('plan-modal');
            renderPlans();
            updateKPIs();
        }

        async function deletePlan(id) {
            if(!confirm('هل أنت متأكد من حذف هذه الباقة نهائياً؟')) return;
            allPlans = allPlans.filter(x => x.id !== id);
            try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}
            renderPlans();
            updateKPIs();
        }

        async function saveInlinePlan(id, btn) {
            let pIdx = allPlans.findIndex(x => x.id === id);
            if(pIdx > -1) {
                let p = allPlans[pIdx];
                p.name = document.getElementById(`inline-name-${id}`).value;
                p.price = document.getElementById(`inline-price-${id}`).value;
                p.duration = document.getElementById(`inline-duration-${id}`).value;
                p.maxUsers = document.getElementById(`inline-users-${id}`).value;
                p.storage = document.getElementById(`inline-storage-${id}`).value;
                
                let originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري...';
                
                try { 
                    await fetch(apiUrl('saas', 'settings', 'plans'), { 
                        method: 'POST', 
                        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, 
                        body: JSON.stringify({ plans: allPlans }) 
                    });
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> تم الحفظ';
                    btn.style.background = 'rgba(16,185,129,0.3)';
                    if (typeof showToast === 'function') showToast('تم الحفظ بنجاح', 'success');
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fa-solid fa-save"></i> حفظ';
                        btn.style.background = 'rgba(16,185,129,0.1)';
                    }, 2000);
                } catch(e) {
                    btn.innerHTML = originalText;
                    if (typeof showToast === 'function') showToast('حدث خطأ أثناء الحفظ', 'error');
                }
            }
        }

        async function togglePlanStatus(id) {
            let idx = allPlans.findIndex(x => x.id === id);
            if(idx > -1) {
                allPlans[idx].status = (allPlans[idx].status === 'suspended') ? 'active' : 'suspended';
                try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}
                setTimeout(() => renderPlans(), 650);
            }
        }

        function updateMaxUsersFromPlan() {
            let planId = document.getElementById('edit-tenant-plan').value;
            let plan = allPlans.find(p => p.id == planId);
            if(plan) {
                document.getElementById('edit-tenant-max-users').value = plan.maxUsers;
                document.getElementById('edit-tenant-max-storage').value = plan.storage;
            }
        }

        
        function renderPartners() {
            const tbody = document.getElementById('partners-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            allPartners.forEach(p => {
                const statusHtml = (p.active !== false) ? '<span style="color:#10b981;">نشط</span>' : '<span style="color:#ef4444;">غير نشط</span>';
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${p.name}</strong></td>
                        <td>${p.logo ? `<img src="${p.logo}" height="30" alt="logo" style="border-radius:4px; max-height:40px;">` : `<div style="width:30px;height:30px;background:rgba(255,255,255,0.1);border-radius:4px;"></div>`}</td>
                        <td>${statusHtml}</td>
                        <td>
                            <button class="btn-primary" style="padding:4px 8px; font-size:0.8rem;" onclick="editPartner(${p.id})">تعديل</button>
                            <button class="btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="deletePartner(${p.id})">حذف</button>
                        </td>
                    </tr>
                `;
            });
        }
        function openPartnerModal() {
            document.getElementById('partner-id').value = '';
            document.getElementById('partner-name').value = '';
            document.getElementById('partner-existing-logo').value = '';
            document.getElementById('partner-logo-upload').value = '';
            document.getElementById('partner-logo-preview').style.display = 'none';
            document.getElementById('partner-active').checked = true;
            document.getElementById('partner-modal').style.display = 'flex';
        }
        function editPartner(id) {
            let p = allPartners.find(x => x.id === id);
            if(!p) return;
            document.getElementById('partner-id').value = p.id;
            document.getElementById('partner-name').value = p.name || '';
            document.getElementById('partner-existing-logo').value = p.logo || '';
            document.getElementById('partner-logo-upload').value = '';
            document.getElementById('partner-active').checked = (p.active !== false);
            
            const preview = document.getElementById('partner-logo-preview');
            if (p.logo) {
                preview.style.display = 'block';
                preview.querySelector('img').src = p.logo;
            } else {
                preview.style.display = 'none';
            }
            
            document.getElementById('partner-modal').style.display = 'flex';
        }
        async function savePartner() {
            const btn = document.getElementById('save-partner-btn');
            btn.innerText = 'جاري الحفظ...';
            btn.disabled = true;

            let id = document.getElementById('partner-id').value;
            let name = document.getElementById('partner-name').value;
            let active = document.getElementById('partner-active').checked;
            let existingLogo = document.getElementById('partner-existing-logo').value;
            let fileInput = document.getElementById('partner-logo-upload');
            
            let finalLogo = existingLogo;

            try {
                if (fileInput.files.length > 0) {
                    const formData = new FormData();
                    formData.append('image', fileInput.files[0]);
                    const uploadRes = await fetch(apiUrl('saas', 'upload-partner'), {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN },
                        body: formData
                    });
                    const uploadData = await uploadRes.json();
                    if (uploadData.success) {
                        finalLogo = uploadData.url;
                    } else {
                        alert('خطأ في رفع الصورة: ' + (uploadData.error || ''));
                        btn.innerText = 'حفظ';
                        btn.disabled = false;
                        return;
                    }
                }

                let p = {
                    id: id ? parseInt(id) : Date.now(),
                    name: name,
                    logo: finalLogo,
                    active: active
                };

                if(id) {
                    let idx = allPartners.findIndex(x => x.id == id);
                    if(idx !== -1) allPartners[idx] = p;
                } else { 
                    allPartners.push(p); 
                }
                
                await fetch(apiUrl('saas', 'settings', 'partners'), { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, 
                    body: JSON.stringify({ 
                        partners: allPartners,
                        partners_enabled: document.getElementById('saas-partners-enabled').checked
                    }) 
                });
                
                closeModal('partner-modal');
                renderPartners();
            } catch(e) {
                console.error(e);
                alert('حدث خطأ أثناء الحفظ');
            } finally {
                btn.innerText = 'حفظ';
                btn.disabled = false;
            }
        }
        async function deletePartner(id) {
            if(!confirm('هل أنت متأكد من الحذف؟')) return;
            allPartners = allPartners.filter(x => x.id !== id);
            try { 
                await fetch(apiUrl('saas', 'settings', 'partners'), { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, 
                    body: JSON.stringify({ 
                        partners: allPartners,
                        partners_enabled: document.getElementById('saas-partners-enabled').checked
                    }) 
                }); 
            } catch(e){}
            renderPartners();
        }

        async function togglePartnersFeature(checked) {
            partnersEnabled = checked;
            try {
                await fetch(apiUrl('saas', 'settings', 'partners'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ partners: allPartners, partners_enabled: checked })
                });
            } catch(e) {
                console.error(e);
                alert('حدث خطأ أثناء تعديل حالة التفعيل');
            }
        }

        // Original API calls ported
        function openEditModal(id) {
            const t = allTenants.find(x => x.id == id);
            if(t) {
                document.getElementById('edit-tenant-id').value = t.id;
                document.getElementById('edit-status').value = t.status || 'active';
                let planId = t.plan_id || 1;
                document.getElementById('edit-tenant-plan').value = planId;
                let plan = allPlans.find(p => p.id == planId) || allPlans[0];
                document.getElementById('edit-tenant-max-users').value = t.max_users || plan.maxUsers;
                document.getElementById('edit-tenant-max-storage').value = t.max_storage !== null ? t.max_storage : (plan.storage || '');
                if(t.valid_until) {
                    const d = new Date(t.valid_until.replace(/-/g, "/"));
                    if (editValidUntilPicker) {
                        editValidUntilPicker.setDate(d);
                    } else {
                        document.getElementById('edit-valid-until').value = t.valid_until;
                    }
                } else {
                    if (editValidUntilPicker) {
                        editValidUntilPicker.clear();
                    } else {
                        document.getElementById('edit-valid-until').value = '';
                    }
                }
                document.getElementById('edit-tenant-payment-amount').value = '';
                document.getElementById('edit-tenant-payment-desc').value = '';
                document.getElementById('edit-modal').style.display = 'flex';
            }
        }

        async function saveTenantUpdate() {
            const id = document.getElementById('edit-tenant-id').value;
            const status = document.getElementById('edit-status').value;
            let valid_until = '';
            if (editValidUntilPicker && editValidUntilPicker.selectedDates.length > 0) {
                const selectedDate = editValidUntilPicker.selectedDates[0];
                const year = selectedDate.getFullYear();
                const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const day = String(selectedDate.getDate()).padStart(2, '0');
                const hours = String(selectedDate.getHours()).padStart(2, '0');
                const minutes = String(selectedDate.getMinutes()).padStart(2, '0');
                const seconds = String(selectedDate.getSeconds()).padStart(2, '0');
                valid_until = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            } else {
                valid_until = document.getElementById('edit-valid-until').value;
                if(valid_until) valid_until = new Date(valid_until).toISOString().slice(0, 19).replace('T', ' ');
            }
            
            const maxUsers = document.getElementById('edit-tenant-max-users').value;
            const maxStorage = document.getElementById('edit-tenant-max-storage').value;
            const planId = document.getElementById('edit-tenant-plan').value;
            const paymentAmount = document.getElementById('edit-tenant-payment-amount').value;
            const paymentDesc = document.getElementById('edit-tenant-payment-desc').value;

            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'update'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, status, valid_until, max_users: maxUsers, max_storage: maxStorage, plan_id: planId, payment_amount: paymentAmount, payment_desc: paymentDesc })
                });
                const data = await res.json();
                if(data.success) { closeModal('edit-modal'); loadTenants(); }
                else alert(data.error);
            } catch(e) { alert('فشل الاتصال'); }
        }

        function updateInlineMaxUsers() {
            let planId = document.getElementById('inline-edit-plan').value;
            let plan = allPlans.find(p => p.id == planId);
            if(plan) {
                document.getElementById('inline-edit-max-users').value = plan.maxUsers || 1;
            }
        }

        async function saveInlineTenantDetails(tenantId) {
            const btn = document.querySelector(`button[onclick="saveInlineTenantDetails(${tenantId})"]`);
            const oldText = btn.innerHTML;
            btn.innerHTML = 'جاري الحفظ...';
            btn.disabled = true;

            const planId = document.getElementById('inline-edit-plan').value;
            let valid_until = document.getElementById('inline-edit-valid-until').value;
            const status = document.getElementById('inline-edit-status').value;
            const maxUsers = document.getElementById('inline-edit-max-users').value;
            
            if(valid_until) {
                // Ensure format YYYY-MM-DD HH:MM:SS
                valid_until = valid_until + ' 23:59:59';
            }

            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'update'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ 
                        id: tenantId, 
                        status: status, 
                        valid_until: valid_until, 
                        max_users: maxUsers, 
                        plan_id: planId 
                    })
                });
                const data = await res.json();
                if(data.success) { 
                    showToast('تم حفظ التعديلات بنجاح!', 'success');
                    
                    // Update local array so UI updates instantly
                    const idx = allTenants.findIndex(t => t.id == tenantId);
                    if (idx > -1) {
                        allTenants[idx].status = status;
                        allTenants[idx].valid_until = valid_until;
                        allTenants[idx].max_users = maxUsers;
                        allTenants[idx].plan_id = planId;
                    }
                    // Refresh view
                    openTenantDetails(tenantId);
                    renderTenantsTable();
                }
                else {
                    showToast(data.error || 'حدث خطأ أثناء الحفظ', 'error');
                }
            } catch(e) { 
                showToast('فشل الاتصال بالخادم', 'error'); 
            } finally {
                btn.innerHTML = oldText;
                btn.disabled = false;
            }
        }

        async function deleteTenant(id) {
            if(!confirm('هل أنت متأكد من الحذف النهائي؟')) return;
            try {
                const url = apiUrl('saas', 'tenants', id);
                const res = await fetch(url, { 
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } 
                });
                const data = await res.json();
                if(data.success) { alert('تم الحذف'); loadTenants(); }
                else alert(data.error || 'فشل حذف المشترك');
            } catch(e) { alert('فشل الاتصال'); }
        }

        async function activateTenant(id) {
            if(!confirm('هل تأكدت من وصول الدفعة وتود تفعيل الحساب؟')) return;
            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'activate'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id })
                });
                const data = await res.json();
                if(data.success) { alert('تم التفعيل بنجاح'); loadTenants(); }
            } catch(e) {}
        }

        async function updateTenantStatus(id, newStatus, selectEl) {
            if (selectEl) {
                let color = newStatus === 'active' ? '#10b981' : (newStatus === 'pending' ? '#f59e0b' : '#ef4444');
                selectEl.style.color = color;
                selectEl.style.borderColor = color;
            }
            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'update'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, status: newStatus })
                });
                const data = await res.json();
                if(!data.success) {
                    alert('حدث خطأ أثناء تحديث الحالة');
                    loadTenants(); // Revert
                }
            } catch(e) {
                alert('فشل الاتصال');
                loadTenants(); // Revert
            }
        }

        let autoLogoutTimer;
        let autoLogoutValue = 30;
        let autoLogoutUnit = 'minutes';

        function resetAutoLogout() {
            clearTimeout(autoLogoutTimer);
            if (autoLogoutValue > 0 && SAAS_TOKEN) {
                let multiplier = autoLogoutUnit === 'minutes' ? 60 * 1000 : 1000;
                autoLogoutTimer = setTimeout(() => {
                    alert('تم تسجيل الخروج تلقائياً بسبب عدم النشاط للحفاظ على الأمان.');
                    logoutSaas();
                }, autoLogoutValue * multiplier);
            }
        }

        // Detect user activity to reset the logout timer
        ['mousemove', 'keydown', 'click', 'scroll'].forEach(evt => {
            document.addEventListener(evt, resetAutoLogout);
        });

        function togglePasswordVisibility(inputId, iconEl) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconEl.innerText = "🙈";
                iconEl.style.opacity = "0.7";
            } else {
                input.type = "password";
                iconEl.innerText = "👁️";
                iconEl.style.opacity = "1";
            }
        }

        async function loadGlobalSettings() {
            try {
                const res = await fetch(apiUrl('saas', 'settings', 'exchange-rate'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }});
                const data = await res.json();
                if(data) {
                    document.getElementById('global-exchange-rate').value = data.rate || 1.0;
                    if(data.other_rates) {
                        document.getElementById('global-exchange-sar').value = data.other_rates.SAR || '';
                        document.getElementById('global-exchange-aed').value = data.other_rates.AED || ''; 
                    } else { 
                        calculateCurrencies(); 
                    }
                    if(data.pricing_method) {
                        document.getElementById('global-pricing-method').value = data.pricing_method;
                    }
                    document.getElementById('global-exchange-visible').checked = data.visible === true || data.visible === 1;
                    if(data.activation_delay_hours !== undefined) {
                        document.getElementById('global-activation-delay').value = data.activation_delay_hours;
                    }
                    if(data.auto_logout_value !== undefined) {
                        document.getElementById('global-auto-logout-value').value = data.auto_logout_value;
                        autoLogoutValue = parseInt(data.auto_logout_value);
                    }
                    if(data.auto_logout_unit !== undefined) {
                        document.getElementById('global-auto-logout-unit').value = data.auto_logout_unit;
                        autoLogoutUnit = data.auto_logout_unit;
                    }
                    resetAutoLogout();
                }
            } catch(e) {}
        }

        async function loadBackdoorCredentials() {
            try {
                const res = await fetch(apiUrl('saas', 'backdoor-credentials'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                if (data.username) {
                    document.getElementById('backdoor-username').value = data.username;
                }
            } catch(e) {}
        }

        async function saveGlobalSettings() {
            const rate = document.getElementById('global-exchange-rate').value;
            const sar = document.getElementById('global-exchange-sar').value;
            const aed = document.getElementById('global-exchange-aed').value;
            const eur = null;
            const pricing_method = document.getElementById('global-pricing-method').value;
            const visible = document.getElementById('global-exchange-visible').checked;
            const activation_delay_hours = document.getElementById('global-activation-delay').value;
            const auto_logout_value = document.getElementById('global-auto-logout-value').value;
            const auto_logout_unit = document.getElementById('global-auto-logout-unit').value;
            
            try {
                const res = await fetch(apiUrl('saas', 'settings', 'exchange-rate'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ rate, other_rates: { SAR: sar, AED: aed, EUR: eur }, pricing_method, visible, activation_delay_hours, auto_logout_value, auto_logout_unit })
                });
                autoLogoutValue = parseInt(auto_logout_value);
                autoLogoutUnit = auto_logout_unit;
                resetAutoLogout();
                alert('تم حفظ الإعدادات بنجاح');
            } catch(e) {}
        }

        async function saveMasterToken() {
            const oldToken = document.getElementById('old-master-token').value;
            const token = document.getElementById('new-master-token').value;
            if(!oldToken || !token) return alert('أدخل الرمزين');
            try {
                const res = await fetch(apiUrl('saas', 'master-token'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ oldToken, token })
                });
                const data = await res.json();
                if(data.success) { alert('تم التغيير! سجل الدخول مجددا'); logoutSaas(); }
            } catch(e) {}
        }

        async function generateDynamicRecoveryToken() {
            const refCode = document.getElementById('recovery-ref-code').value.trim().toUpperCase();
            if (!refCode) {
                alert('الرجاء إدخال الرمز المرجعي (refCode) أولاً.');
                return;
            }
            try {
                const salt = "Magaiees_ERP_2026_SaaS_Secr3t";
                const dynBuffer = new TextEncoder().encode(refCode + salt);
                const dynHash = await crypto.subtle.digest('SHA-256', dynBuffer);
                const hashArray = Array.from(new Uint8Array(dynHash));
                const num = (hashArray[0] << 24 | hashArray[1] << 16 | hashArray[2] << 8 | hashArray[3]) >>> 0;
                const token = (num % 1000000).toString().padStart(6, '0');
                document.getElementById('recovery-generated-token').value = token;
            } catch (e) {
                console.error(e);
                alert('حدث خطأ أثناء توليد التوكن.');
            }
        }

        async function saveBackdoorCredentials() {
            const username = document.getElementById('backdoor-username').value;
            const password = document.getElementById('backdoor-password').value;
            if(!username || !password) return alert('أدخل اسم المستخدم وكلمة المرور للدعم الفني');
            try {
                const res = await fetch(apiUrl('saas', 'backdoor-credentials'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ username, password })
                });
                const data = await res.json();
                if(data.success) {
                    alert('تم حفظ وتفعيل حساب الدعم الفني بنجاح!');
                    document.getElementById('backdoor-password').value = '';
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch(e) {
                alert('فشل الاتصال');
            }
        }

        async function loadMessages() {
            try {
                const tbody = document.getElementById('messages-body');
                if (!tbody) return;
                const res = await fetch(apiUrl('saas', 'messages'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                tbody.innerHTML = '';
                
                if (data.error) {
                    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">خطأ: ${data.error}</td></tr>`;
                    return;
                }
                
                allMessagesData = Array.isArray(data) ? data : [];
                
                if(allMessagesData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">لا توجد رسائل نظام</td></tr>';
                } else {
                    allMessagesData.forEach(m => {
                        let targetText = 'الجميع';
                        let targets = [];
                        try { targets = JSON.parse(m.target_tenants || '[]'); } catch(e){}
                        if (!targets.includes('all') && targets.length > 0) {
                            const companyNames = [];
                            targets.forEach(tid => {
                                const tenant = allTenants.find(t => t.id == tid);
                                if (tenant) companyNames.push(tenant.company_name);
                            });
                            targetText = companyNames.length > 0 ? companyNames.join('، ') : 'مشتركين محددين';
                        }

                            const themeColors = { 'info': '#38bdf8', 'success': '#10b981', 'warning': '#fbbf24', 'danger': '#ef4444' };
                            const themeColor = themeColors[m.theme] || '#38bdf8';
                            const themeIcon = m.theme === 'success' ? 'check-circle' : m.theme === 'warning' ? 'triangle-exclamation' : m.theme === 'danger' ? 'circle-xmark' : 'circle-info';

                            tbody.innerHTML += `
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 15px; vertical-align: middle;">
                                    <div style="font-size: 1.05rem; font-weight: 800; color: #e2e8f0; margin-bottom: 5px;">${m.title || 'بدون عنوان'}</div>
                                    <div style="font-size: 0.85rem; color: #94a3b8; line-height: 1.5; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${m.message || ''}</div>
                                </td>
                                <td style="padding: 15px; vertical-align: middle;">
                                    <span style="background: rgba(255,255,255,0.05); padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid ${targets.includes('all') ? 'fa-users' : 'fa-user-check'} text-sky-400"></i> ${targetText}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    <span style="background: ${themeColor}20; color: ${themeColor}; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; border: 1px solid ${themeColor}40;">
                                        <i class="fa-solid fa-${themeIcon}"></i> ${m.theme === 'info' ? 'تنبيه (معلومة)' : m.theme === 'success' ? 'نجاح' : m.theme === 'warning' ? 'تحذير' : 'هام جداً'}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    <div style="display: flex; justify-content: center; gap: 10px;">
                                        <button onclick="openMessageModal(${m.id})" style="background: rgba(56,189,248,0.1); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                                            <i class="fa-solid fa-pen-to-square"></i> تعديل
                                        </button>
                                        <button onclick="deleteMessage(${m.id})" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                                            <i class="fa-solid fa-trash"></i> حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    });
                }
            } catch (e) {
                console.error("Error loading messages:", e);
                const tbody = document.getElementById('messages-body');
                if (tbody) tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:red;">حدث خطأ في جلب الرسائل.</td></tr>';
            }
        }

        function populateMsgTargetTenantsList() {
            const container = document.getElementById('msg-target-tenants-list');
            if (!container) return;
            container.innerHTML = '';
            if (!allTenants || allTenants.length === 0) {
                container.innerHTML = '<span style="color: var(--text-secondary);">لا يوجد مشتركين مسجلين</span>';
                return;
            }
            allTenants.forEach(t => {
                const label = document.createElement('label');
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.gap = '8px';
                label.style.color = '#fff';
                label.style.cursor = 'pointer';
                label.style.fontSize = '0.9rem';
                label.style.margin = '4px 0';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = t.id;
                checkbox.className = 'msg-target-checkbox';
                checkbox.style.cursor = 'pointer';
                
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(t.company_name + ` (${t.subdomain})`));
                container.appendChild(label);
            });
        }

        function toggleMsgTargetTenantsList() {
            const val = document.getElementById('msg-target-type').value;
            document.getElementById('msg-target-tenants-group').style.display = (val === 'custom') ? 'block' : 'none';
        }

        function openMessageModal(id) {
            populateMsgTargetTenantsList();
            const modal = document.getElementById('message-modal');
            const titleEl = document.querySelector('#message-modal .modal-header h3');
            
            if (id) {
                const m = allMessagesData.find(msg => msg.id == id);
                if (m) {
                    document.getElementById('msg-id').value = m.id;
                    document.getElementById('msg-title').value = m.title || '';
                    document.getElementById('msg-body').value = m.message || '';
                    document.getElementById('msg-theme').value = m.theme || 'info';
                    document.getElementById('msg-show-once').checked = (m.show_once == 1);
                    
                    let targets = [];
                    try { targets = JSON.parse(m.target_tenants || '[]'); } catch(e){}
                    
                    if (targets.includes('all') || targets.length === 0) {
                        document.getElementById('msg-target-type').value = 'all';
                        document.getElementById('msg-target-tenants-group').style.display = 'none';
                    } else {
                        document.getElementById('msg-target-type').value = 'custom';
                        document.getElementById('msg-target-tenants-group').style.display = 'block';
                        
                        document.querySelectorAll('.msg-target-checkbox').forEach(cb => {
                            cb.checked = targets.includes(parseInt(cb.value)) || targets.includes(cb.value);
                        });
                    }
                    
                    if (titleEl) titleEl.innerText = 'تعديل رسالة النظام';
                }
            } else {
                document.getElementById('msg-id').value = '0';
                document.getElementById('msg-title').value = '';
                document.getElementById('msg-body').value = '';
                document.getElementById('msg-theme').value = 'info';
                document.getElementById('msg-show-once').checked = true;
                document.getElementById('msg-target-type').value = 'all';
                document.getElementById('msg-target-tenants-group').style.display = 'none';
                
                document.querySelectorAll('.msg-target-checkbox').forEach(cb => cb.checked = false);
                if (titleEl) titleEl.innerText = 'إنشاء رسالة نظامية';
            }
            
            modal.style.display = 'flex';
        }

        async function saveMessage() {
            const id = parseInt(document.getElementById('msg-id').value) || 0;
            const title = document.getElementById('msg-title').value.trim();
            const body = document.getElementById('msg-body').value.trim();
            const theme = document.getElementById('msg-theme').value;
            const showOnce = document.getElementById('msg-show-once').checked ? 1 : 0;
            
            let targets = ['all'];
            if (document.getElementById('msg-target-type').value === 'custom') {
                const selectedCbs = document.querySelectorAll('.msg-target-checkbox:checked');
                targets = Array.from(selectedCbs).map(cb => parseInt(cb.value));
                if (targets.length === 0) {
                    alert('الرجاء اختيار مشترك واحد على الأقل أو جعل الجمهور "الجميع"!');
                    return;
                }
            }
            
            if (!title || !body) {
                alert('الرجاء إدخال عنوان ومحتوى الرسالة!');
                return;
            }
            
            try {
                const res = await fetch(apiUrl('saas', 'messages'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, title, message: body, target_tenants: targets, theme, show_once: showOnce })
                });
                const data = await res.json();
                if (data.success) {
                    closeModal('message-modal');
                    loadMessages();
                } else {
                    alert(data.error || 'فشل حفظ الرسالة');
                }
            } catch(e) {
                alert('فشل الاتصال بالخادم');
            }
        }

        async function deleteMessage(id) {
            if(!confirm('هل أنت متأكد من حذف هذه الرسالة النظامية؟')) return;
            try {
                const res = await fetch(apiUrl('saas', 'messages'), { 
                    method: 'DELETE', 
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, 
                    body: JSON.stringify({ id }) 
                });
                const data = await res.json();
                if (data.success) {
                    loadMessages();
                } else {
                    alert(data.error || 'فشل حذف الرسالة');
                }
            } catch(e) {
                alert('فشل الاتصال');
            }
        }
    
        // ==========================================
        // ADS MANAGEMENT & DURATION TRACKING
        // ==========================================
        let allAds = [];

        function renderAds() {
            const tbody = document.getElementById('ads-body');
            if (!tbody) return;

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let statTotal = allAds.length;
            let statActive = 0;
            let statExpiring = 0;
            let statExpired = 0;

            allAds.forEach(ad => {
                const startDate = ad.start_date ? new Date(ad.start_date) : null;
                const endDate = ad.end_date ? new Date(ad.end_date) : null;
                const isExpired = endDate && (endDate < today);
                const diffDays = endDate ? Math.ceil((endDate - today) / (1000 * 60 * 60 * 24)) : null;

                if (isExpired) {
                    statExpired++;
                } else if (ad.active) {
                    if (!startDate || startDate <= today) {
                        statActive++;
                        if (diffDays !== null && diffDays <= 7 && diffDays >= 0) {
                            statExpiring++;
                        }
                    }
                }
            });

            // Update KPI elements if available
            const elTotal = document.getElementById('ads-stat-total');
            const elActive = document.getElementById('ads-stat-active');
            const elExpiring = document.getElementById('ads-stat-expiring');
            const elExpired = document.getElementById('ads-stat-expired');
            if (elTotal) elTotal.innerText = statTotal;
            if (elActive) elActive.innerText = statActive;
            if (elExpiring) elExpiring.innerText = statExpiring;
            if (elExpired) elExpired.innerText = statExpired;

            // Search and Status Filters
            const searchInput = document.getElementById('ads-search-input');
            const searchQuery = searchInput ? searchInput.value.trim().toLowerCase() : '';
            const statusFilter = document.getElementById('ads-filter-status') ? document.getElementById('ads-filter-status').value : 'all';

            const filtered = allAds.filter(ad => {
                const name = (ad.companyName || '').toLowerCase();
                const notes = (ad.notes || '').toLowerCase();
                const link = (ad.link || '').toLowerCase();
                const matchesSearch = !searchQuery || name.includes(searchQuery) || notes.includes(searchQuery) || link.includes(searchQuery);
                if (!matchesSearch) return false;

                const startDate = ad.start_date ? new Date(ad.start_date) : null;
                const endDate = ad.end_date ? new Date(ad.end_date) : null;
                const isExpired = endDate && (endDate < today);
                const diffDays = endDate ? Math.ceil((endDate - today) / (1000 * 60 * 60 * 24)) : null;

                if (statusFilter === 'active') {
                    return ad.active && (!endDate || !isExpired) && (!startDate || startDate <= today);
                } else if (statusFilter === 'expiring') {
                    return ad.active && !isExpired && diffDays !== null && diffDays <= 7 && diffDays >= 0;
                } else if (statusFilter === 'expired') {
                    return isExpired;
                } else if (statusFilter === 'inactive') {
                    return !ad.active;
                }
                return true;
            });

            tbody.innerHTML = '';
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 30px; color: #64748b;"><i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 8px; opacity: 0.5;"></i><br>لا توجد إعلانات تطابق المعايير المحددة</td></tr>';
                return;
            }

            filtered.forEach(ad => {
                const startDate = ad.start_date ? new Date(ad.start_date) : null;
                const endDate = ad.end_date ? new Date(ad.end_date) : null;
                const isExpired = endDate && (endDate < today);
                const diffDays = endDate ? Math.ceil((endDate - today) / (1000 * 60 * 60 * 24)) : null;

                let statusBadge = '';
                let rowBg = 'transparent';

                if (!ad.active) {
                    statusBadge = '<span style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-pause"></i> موقوف يدوياً</span>';
                } else if (startDate && startDate > today) {
                    const daysToStart = Math.ceil((startDate - today) / (1000 * 60 * 60 * 24));
                    statusBadge = `<span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold;"><i class="fa-regular fa-clock"></i> يبدأ بعد ${daysToStart} يوم</span>`;
                } else if (isExpired) {
                    const daysExpired = Math.abs(diffDays);
                    rowBg = 'rgba(239, 68, 68, 0.06)';
                    statusBadge = `<span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-triangle-exclamation"></i> منتهي (منذ ${daysExpired} يوم)</span>`;
                } else if (diffDays !== null && diffDays <= 7) {
                    rowBg = 'rgba(245, 158, 11, 0.06)';
                    statusBadge = `<span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-hourglass-half"></i> ينتهي قريباً (متبقي ${diffDays} يوم)</span>`;
                } else if (diffDays !== null) {
                    statusBadge = `<span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-circle-check"></i> ساري (متبقي ${diffDays} يوم)</span>`;
                } else {
                    statusBadge = '<span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-infinity"></i> دائم / غير محدد</span>';
                }

                const startDateStr = ad.start_date || '<span style="color:#64748b;">غير محدد</span>';
                const endDateStr = ad.end_date || '<span style="color:#64748b;">دائم</span>';
                const linkHtml = ad.link ? `<a href="${ad.link}" target="_blank" rel="noopener noreferrer" style="color: #38bdf8; font-size: 0.8rem; text-decoration: none; display: flex; align-items: center; gap: 4px; margin-top: 3px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> ${escapeHtml(ad.link)}</a>` : '';
                const notesHtml = ad.notes ? `<div style="color: #64748b; font-size: 0.75rem; margin-top: 2px;">📝 ${escapeHtml(ad.notes)}</div>` : '';

                tbody.innerHTML += `
                    <tr style="background: ${rowBg}; border-bottom: 1px solid rgba(255,255,255,0.03); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='${rowBg}'">
                        <td style="vertical-align: middle; padding: 12px 10px;">
                            ${ad.image ? `<a href="${ad.image}" target="_blank" title="عرض الحجم الكامل"><img src="${ad.image}" alt="${escapeHtml(ad.companyName || '')}" loading="lazy" style="max-width: 130px; max-height: 45px; object-fit: contain; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); background: #000;"></a>` : `<div style="width:100px; height:35px; background:rgba(255,255,255,0.05); border-radius:6px; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:0.75rem;">بدون صورة</div>`}
                        </td>
                        <td style="vertical-align: middle; padding: 12px 10px;">
                            <strong style="color: #fff; font-size: 0.95rem;">${escapeHtml(ad.companyName || 'بدون اسم')}</strong>
                            ${linkHtml}
                            ${notesHtml}
                        </td>
                        <td style="vertical-align: middle; font-family: monospace; font-size: 0.85rem; color: #cbd5e1; direction: ltr; text-align: right;">
                            ${startDateStr}
                        </td>
                        <td style="vertical-align: middle; font-family: monospace; font-size: 0.85rem; color: ${isExpired ? '#ef4444' : '#cbd5e1'}; font-weight: ${isExpired ? 'bold' : 'normal'}; direction: ltr; text-align: right;">
                            ${endDateStr}
                        </td>
                        <td style="vertical-align: middle; text-align: center;">
                            ${statusBadge}
                        </td>
                        <td style="vertical-align: middle; text-align: center;">
                            <label style="cursor: pointer; display: inline-flex; align-items: center; position: relative;" title="تفعيل / إيقاف الإعلان">
                                <input type="checkbox" onchange="toggleAdActive(${ad.id})" ${ad.active ? 'checked' : ''} style="cursor: pointer; width: 18px; height: 18px; accent-color: #10b981;">
                            </label>
                        </td>
                        <td style="vertical-align: middle; text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                                <button class="btn-outline" onclick="editAd(${ad.id})" title="تعديل بيانات وفترة الإعلان" style="padding: 5px 10px; font-size: 0.8rem; border-color: rgba(255,255,255,0.15);">
                                    <i class="fa-solid fa-pen-to-square"></i> تعديل
                                </button>
                                <button class="btn-outline" onclick="quickExtendAd(${ad.id}, 30)" title="تمديد سريع لمدة 30 يوم" style="padding: 5px 10px; font-size: 0.8rem; border-color: rgba(245,158,11,0.4); color: #f59e0b; background: rgba(245,158,11,0.08);">
                                    <i class="fa-solid fa-clock-rotate-left"></i> +30 يوم
                                </button>
                                <button class="btn-danger" onclick="deleteAd(${ad.id})" title="حذف الإعلان نهائياً" style="padding: 5px 8px; font-size: 0.8rem;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        function setAdDurationPreset(days) {
            document.getElementById('ad-duration-days').value = days;
            onAdDurationDaysChange();
        }

        function onAdStartDateChange() {
            const startVal = document.getElementById('ad-start-date').value;
            const days = parseInt(document.getElementById('ad-duration-days').value) || 30;
            if (startVal) {
                const startDate = new Date(startVal);
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + days);
                document.getElementById('ad-end-date').value = endDate.toISOString().split('T')[0];
            }
        }

        function onAdDurationDaysChange() {
            const days = parseInt(document.getElementById('ad-duration-days').value) || 0;
            let startVal = document.getElementById('ad-start-date').value;
            if (!startVal) {
                startVal = new Date().toISOString().split('T')[0];
                document.getElementById('ad-start-date').value = startVal;
            }
            const startDate = new Date(startVal);
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + days);
            document.getElementById('ad-end-date').value = endDate.toISOString().split('T')[0];
        }

        function onAdEndDateChange() {
            const startVal = document.getElementById('ad-start-date').value;
            const endVal = document.getElementById('ad-end-date').value;
            if (startVal && endVal) {
                const start = new Date(startVal);
                const end = new Date(endVal);
                const diffTime = end - start;
                const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                document.getElementById('ad-duration-days').value = Math.max(1, diffDays);
            }
        }

        function previewAdImage(input) {
            const preview = document.getElementById('ad-image-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.display = 'block';
                    preview.querySelector('img').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openAdModal() {
            const modalTitle = document.getElementById('ad-modal-title');
            if (modalTitle) modalTitle.innerHTML = '<i class="fa-solid fa-bullhorn text-amber-400"></i> إضافة إعلان جديد';
            
            document.getElementById('ad-id').value = '';
            document.getElementById('ad-image-upload').value = '';
            document.getElementById('ad-active').checked = true;
            document.getElementById('ad-existing-image').value = '';
            document.getElementById('ad-company').value = '';
            document.getElementById('ad-link').value = '';
            document.getElementById('ad-notes').value = '';
            
            const todayStr = new Date().toISOString().split('T')[0];
            document.getElementById('ad-start-date').value = todayStr;
            document.getElementById('ad-duration-days').value = 30;
            
            const endDate = new Date();
            endDate.setDate(endDate.getDate() + 30);
            document.getElementById('ad-end-date').value = endDate.toISOString().split('T')[0];

            document.getElementById('ad-image-preview').style.display = 'none';
            document.getElementById('ad-modal').style.display = 'flex';
        }

        function editAd(id) {
            const ad = allAds.find(a => a.id == id);
            if (!ad) return;
            
            const modalTitle = document.getElementById('ad-modal-title');
            if (modalTitle) modalTitle.innerHTML = '<i class="fa-solid fa-pen-to-square text-amber-400"></i> تعديل الإعلان';

            document.getElementById('ad-id').value = ad.id;
            document.getElementById('ad-image-upload').value = ''; 
            document.getElementById('ad-active').checked = ad.active !== false;
            document.getElementById('ad-existing-image').value = ad.image || '';
            document.getElementById('ad-company').value = ad.companyName || '';
            document.getElementById('ad-link').value = ad.link || '';
            document.getElementById('ad-notes').value = ad.notes || '';
            
            const startVal = ad.start_date || new Date().toISOString().split('T')[0];
            document.getElementById('ad-start-date').value = startVal;
            
            if (ad.end_date) {
                document.getElementById('ad-end-date').value = ad.end_date;
                const start = new Date(startVal);
                const end = new Date(ad.end_date);
                const diffDays = Math.max(1, Math.round((end - start) / (1000 * 60 * 60 * 24)));
                document.getElementById('ad-duration-days').value = ad.duration_days || diffDays;
            } else {
                document.getElementById('ad-duration-days').value = ad.duration_days || 30;
                onAdDurationDaysChange();
            }
            
            const preview = document.getElementById('ad-image-preview');
            if (ad.image) {
                preview.style.display = 'block';
                preview.querySelector('img').src = ad.image;
            } else {
                preview.style.display = 'none';
            }
            
            document.getElementById('ad-modal').style.display = 'flex';
        }

        async function toggleAdActive(id) {
            const ad = allAds.find(a => a.id == id);
            if (!ad) return;
            ad.active = !ad.active;
            try {
                await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });
                renderAds();
                if (typeof showToast === 'function') {
                    showToast(ad.active ? 'تم تفعيل الإعلان' : 'تم إيقاف الإعلان', 'info');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function quickExtendAd(id, days) {
            const ad = allAds.find(a => a.id == id);
            if (!ad) return;

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let currentEnd = ad.end_date ? new Date(ad.end_date) : null;
            let baseDate = (currentEnd && currentEnd > today) ? currentEnd : today;

            const newEnd = new Date(baseDate);
            newEnd.setDate(baseDate.getDate() + days);

            ad.end_date = newEnd.toISOString().split('T')[0];
            if (!ad.start_date) ad.start_date = today.toISOString().split('T')[0];

            const start = new Date(ad.start_date);
            ad.duration_days = Math.max(1, Math.round((newEnd - start) / (1000 * 60 * 60 * 24)));
            ad.active = true;

            try {
                await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });
                renderAds();
                if (typeof showToast === 'function') {
                    showToast(`تم تمديد الإعلان (${ad.companyName || ''}) لـ ${days} يوم بنجاح!`, 'success');
                } else {
                    alert(`تم تمديد الإعلان لـ ${days} يوم حتى ${ad.end_date}`);
                }
            } catch (e) {
                alert('فشل تمديد الإعلان: ' + e.message);
            }
        }

        async function saveAd() {
            const id = document.getElementById('ad-id').value;
            const fileInput = document.getElementById('ad-image-upload');
            const active = document.getElementById('ad-active').checked;
            const companyName = document.getElementById('ad-company').value.trim();
            const link = document.getElementById('ad-link').value.trim();
            const startDate = document.getElementById('ad-start-date').value;
            const durationDays = parseInt(document.getElementById('ad-duration-days').value) || 30;
            const endDate = document.getElementById('ad-end-date').value;
            const notes = document.getElementById('ad-notes').value.trim();
            let imageUrl = document.getElementById('ad-existing-image').value;

            if (!companyName) {
                alert('الرجاء إدخال اسم الشركة أو الجهة المعلنة!');
                return;
            }

            const btn = document.getElementById('ad-save-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...';

            try {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('image', file);

                    const uploadRes = await fetch(apiUrl('saas', 'upload-ad'), {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN },
                        body: formData
                    });
                    const uploadData = await uploadRes.json();
                    if (uploadData.success) {
                        imageUrl = uploadData.url;
                    } else {
                        throw new Error(uploadData.error || 'فشل رفع الصورة');
                    }
                }

                if (!imageUrl) throw new Error('يجب اختيار صورة للإعلان');

                const adPayload = {
                    id: id ? (isNaN(id) ? id : Number(id)) : Date.now(),
                    image: imageUrl,
                    active: active,
                    companyName: companyName,
                    link: link,
                    start_date: startDate,
                    duration_days: durationDays,
                    end_date: endDate,
                    notes: notes,
                    updated_at: new Date().toISOString()
                };

                if (id) {
                    const index = allAds.findIndex(a => a.id == id);
                    if (index !== -1) {
                        allAds[index] = Object.assign({}, allAds[index], adPayload);
                    }
                } else {
                    allAds.push(adPayload);
                }

                const saveRes = await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });
                const saveData = await saveRes.json();
                if (!saveRes.ok || !saveData.success) {
                    throw new Error(saveData.error || 'فشل حفظ الإعلان في قاعدة البيانات');
                }

                // Reload ads from server to ensure consistency
                try {
                    const resPub = await fetch('api_index.php?r=public');
                    const dataPub = await resPub.json();
                    allAds = dataPub.ads || [];
                } catch(reloadErr) {
                    console.warn('Failed to reload ads after save:', reloadErr);
                }

                closeModal('ad-modal');
                renderAds();
                if (typeof showToast === 'function') showToast('تم حفظ الإعلان بنجاح', 'success');
            } catch (e) {
                alert('خطأ: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-save"></i> حفظ الإعلان';
            }
        }

        async function deleteAd(id) {
            if (!confirm('هل أنت متأكد من حذف هذا الإعلان نهائياً؟')) return;
            allAds = allAds.filter(a => a.id != id);
            try {
                await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });
                renderAds();
                if (typeof showToast === 'function') showToast('تم حذف الإعلان', 'info');
            } catch (e) {}
        }

        // --- GLOBAL SEARCH LOGIC (Ctrl + K) ---
        function toggleGlobalSearch() {
            const overlay = document.getElementById('global-search-overlay');
            if(overlay.style.display === 'flex') {
                overlay.style.display = 'none';
            } else {
                overlay.style.display = 'flex';
                document.getElementById('global-search-input').value = '';
                document.getElementById('global-search-results').innerHTML = '';
                setTimeout(() => document.getElementById('global-search-input').focus(), 100);
            }
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                toggleGlobalSearch();
            }
            if (e.key === 'Escape' && document.getElementById('global-search-overlay').style.display === 'flex') {
                toggleGlobalSearch();
            }
        });

        function performGlobalSearch() {
            const query = document.getElementById('global-search-input').value.toLowerCase();
            const resultsDiv = document.getElementById('global-search-results');
            resultsDiv.innerHTML = '';
            if(!query) return;

            const matches = allTenants.filter(t => {
                return (t.company_name || '').toLowerCase().includes(query) ||
                       (t.subdomain || '').toLowerCase().includes(query) ||
                       (t.owner_name || '').toLowerCase().includes(query) ||
                       (t.owner_phone || '').toLowerCase().includes(query);
            }).slice(0, 10);

            if(matches.length === 0) {
                resultsDiv.innerHTML = '<div style="padding:15px; color:#a1a1aa; text-align:center;">لا توجد نتائج مطابقة</div>';
                return;
            }

            matches.forEach(t => {
                const el = document.createElement('div');
                el.className = 'gs-item';
                let statusColor = '#94a3b8';
                if(t.status === 'active') statusColor = '#10b981';
                if(t.status === 'pending') statusColor = '#f59e0b';
                if(t.status === 'suspended') statusColor = '#ef4444';
                el.innerHTML = `
                    <div>
                        <strong style="color:#fff;">${t.company_name || '—'}</strong> 
                        <span style="color:#a1a1aa; font-size:0.85em; margin-right:10px; direction:ltr; display:inline-block;">${t.subdomain}.elsajana.com</span>
                    </div>
                    <div>
                        <span style="color:${statusColor}; font-size:0.85em;">●</span>
                        <span style="color:#a1a1aa; font-size:0.85em; margin-right:5px;">${t.owner_name || ''}</span>
                    </div>
                `;
                el.onclick = () => {
                    toggleGlobalSearch();
                    document.querySelector('.tab-btn[onclick*="tab-tenants"]').click();
                    setTimeout(() => openTenantDetails(t.id), 200);
                };
                resultsDiv.appendChild(el);
            });
        }

        // === IP BAN MANAGEMENT ===
        async function loadBannedIPs() {
            const tbody = document.getElementById('banned-ips-body');
            if (!tbody) return;
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">جاري التحميل...</td></tr>';
            try {
                const res = await fetch(apiUrl('saas', 'ip-bans'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                if (data.success && data.bans.length > 0) {
                    tbody.innerHTML = data.bans.map(b => `
                        <tr>
                            <td style="direction:ltr; text-align:center; font-family:monospace; font-weight:bold; font-size:1rem;">${escapeHtml(b.ip_address)}</td>
                            <td>${escapeHtml(b.reason || 'لم يُحدد')}</td>
                            <td>${new Date(b.banned_at).toLocaleDateString('ar-EG')} ${new Date(b.banned_at).toLocaleTimeString('ar-EG', {hour:'2-digit', minute:'2-digit'})}</td>
                            <td><button class="btn-danger" style="font-size:0.8rem; padding:6px 12px;" onclick="unbanIP(${b.id}, '${escapeHtml(b.ip_address)}')">🗑️ إلغاء الحظر</button></td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--text-secondary);">✅ لا يوجد عناوين محظورة حالياً</td></tr>';
                }
            } catch(e) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--danger-color);">خطأ في جلب البيانات</td></tr>';
            }
        }

        async function banIP() {
            const ip = document.getElementById('ban-ip-address').value.trim();
            const reason = document.getElementById('ban-ip-reason').value.trim();
            if (!ip) return showToast('أدخل عنوان IP', 'error');
            try {
                const res = await fetch(apiUrl('saas', 'ip-bans'), {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ip_address: ip, reason: reason })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('تم حظر العنوان ' + ip + ' بنجاح 🚫');
                    document.getElementById('ban-ip-address').value = '';
                    document.getElementById('ban-ip-reason').value = '';
                    document.getElementById('ip-ban-form').style.display = 'none';
                    loadBannedIPs();
                } else {
                    showToast(data.error || 'خطأ في الحظر', 'error');
                }
            } catch(e) { showToast('خطأ في الاتصال', 'error'); }
        }

        async function unbanIP(id, ip) {
            if (!confirm('هل تريد إلغاء حظر العنوان ' + ip + '؟')) return;
            try {
                const res = await fetch(apiUrl('saas', 'ip-bans', 'delete'), {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('تم إلغاء حظر ' + ip + ' ✅');
                    loadBannedIPs();
                } else {
                    showToast(data.error || 'خطأ', 'error');
                }
            } catch(e) { showToast('خطأ في الاتصال', 'error'); }
        }

        let healthAutoRefreshTimer = null;
        let healthLastFetchTime = null;

        function toggleHealthAutoRefresh() {
            const checked = document.getElementById('health-auto-refresh')?.checked;
            if (checked) {
                startHealthAutoRefresh();
            } else {
                stopHealthAutoRefresh();
            }
        }

        function startHealthAutoRefresh() {
            stopHealthAutoRefresh();
            healthAutoRefreshTimer = setInterval(() => {
                loadServerHealth(true);
            }, 30000); // Every 30 seconds
        }

        function stopHealthAutoRefresh() {
            if (healthAutoRefreshTimer) {
                clearInterval(healthAutoRefreshTimer);
                healthAutoRefreshTimer = null;
            }
        }

        function updateHealthTimestamp() {
            const el = document.getElementById('health-last-update');
            if (!el || !healthLastFetchTime) return;
            const diff = Math.round((Date.now() - healthLastFetchTime) / 1000);
            if (diff < 5) el.innerText = 'الآن';
            else if (diff < 60) el.innerText = 'منذ ' + diff + ' ثانية';
            else el.innerText = 'منذ ' + Math.round(diff / 60) + ' دقيقة';
        }
        setInterval(updateHealthTimestamp, 5000);

        async function loadServerHealth(silent = false) {
            try {
                const res = await fetch('api_saas_health.php', { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const d = await res.json();
                if (!d.success) {
                    if (!silent) showToast(d.error || 'خطأ في جلب بيانات الخادم', 'error');
                    return;
                }

                healthLastFetchTime = Date.now();
                updateHealthTimestamp();
                
                // === Overall Status Badge ===
                const badge = document.getElementById('health-status-badge');
                const dot = document.getElementById('health-status-dot');
                const statusText = document.getElementById('health-status-text');
                if (badge && dot && statusText) {
                    const status = d.overall_status || 'healthy';
                    const colors = { healthy: '#10b981', warning: '#f59e0b', critical: '#ef4444' };
                    const icons = { healthy: '✅', warning: '⚠️', critical: '🔴' };
                    const c = colors[status] || colors.healthy;
                    badge.style.background = c.replace(')', ', 0.08)').replace('rgb', 'rgba').replace('#', '');
                    badge.style.background = `rgba(${parseInt(c.slice(1,3),16)}, ${parseInt(c.slice(3,5),16)}, ${parseInt(c.slice(5,7),16)}, 0.08)`;
                    badge.style.borderColor = `rgba(${parseInt(c.slice(1,3),16)}, ${parseInt(c.slice(3,5),16)}, ${parseInt(c.slice(5,7),16)}, 0.25)`;
                    dot.style.background = c;
                    statusText.style.color = c;
                    statusText.innerText = (icons[status] || '✅') + ' ' + (d.status_message || 'الخادم يعمل بشكل طبيعي');
                }

                // Helper: update linear bar with null check
                function setLinearBar(barId, percent, valueId, label) {
                    const bar = document.getElementById(barId);
                    if (bar) {
                        bar.style.width = percent + '%';
                        if (percent > 85) bar.style.background = '#ef4444';
                        else if (percent > 60) bar.style.background = '#f59e0b';
                        else bar.style.background = '#10b981';
                    }
                    const valEl = document.getElementById(valueId);
                    if (valEl) valEl.innerText = label;
                }
                
                // CPU
                setLinearBar('health-cpu-bar', Math.min(d.cpu_load_1m, 100), 'health-cpu', Math.round(d.cpu_load_1m) + '%');
                
                // RAM
                setLinearBar('health-ram-bar', Math.min(d.memory_percent || 0, 100), 'health-ram', Math.round(d.memory_percent || 0) + '%');
                const ramPeakEl = document.getElementById('health-ram-peak');
                if (ramPeakEl) ramPeakEl.innerText = (d.memory_usage || '0') + ' مستخدم من ' + (d.memory_total || '0');
                
                // Disk
                setLinearBar('health-disk-bar', Math.min(d.disk_used_percent || 0, 100), 'health-disk', Math.round(d.disk_used_percent || 0) + '%');
                const diskDetailsEl = document.getElementById('health-disk-details');
                if (diskDetailsEl) diskDetailsEl.innerText = (d.disk_free || '0') + ' متبقي من ' + (d.disk_total || '0');
                
                // DB Size + Tables Count
                const dbEl = document.getElementById('health-db');
                if (dbEl) dbEl.innerText = d.db_size || '0 B';
                const dbTablesEl = document.getElementById('health-db-tables');
                if (dbTablesEl && d.db_tables_count) dbTablesEl.innerText = d.db_tables_count + ' جدول';
                
                // Connections & Users
                const connEl = document.getElementById('health-connections');
                if (connEl) connEl.innerText = d.active_connections || '0';
                const actUsrEl = document.getElementById('health-active-users');
                if (actUsrEl) actUsrEl.innerText = d.active_users || '0';
                
                // Uptime
                const uptimeEl = document.getElementById('health-uptime');
                if (uptimeEl) uptimeEl.innerText = d.uptime || 'N/A';
                
                // Network & Processes
                const netEl = document.getElementById('health-network');
                if (netEl) netEl.innerText = (d.network_rx || '0 B') + ' ↓ / ' + (d.network_tx || '0 B') + ' ↑';
                
                const procEl = document.getElementById('health-processes');
                if (procEl) procEl.innerText = d.processes || '0';
                
                // System details
                const osEl = document.getElementById('health-os');
                if (osEl) osEl.innerText = d.os || 'N/A';
                const phpEl = document.getElementById('health-php');
                if (phpEl) phpEl.innerText = d.php_version || 'N/A';
                const mysqlEl = document.getElementById('health-mysql');
                if (mysqlEl) mysqlEl.innerText = d.mysql_version || 'N/A';
                const timeEl = document.getElementById('health-time');
                if (timeEl) timeEl.innerText = d.time || 'N/A';
                
                // Billing Info
                if (d.billing) {
                    const bp = document.getElementById('health-billing-provider');
                    if (bp) bp.innerText = d.billing.provider || '--';
                    const bc = document.getElementById('health-billing-cost');
                    if (bc) bc.innerText = d.billing.cost || '0.00';
                    const br = document.getElementById('health-billing-renewal');
                    if (br) br.innerText = d.billing.renewal_date || '--';
                    const bpl = document.getElementById('health-billing-plan');
                    if (bpl) bpl.innerText = d.billing.plan || '--';
                }

                // Start auto-refresh if checkbox is checked
                if (document.getElementById('health-auto-refresh')?.checked && !healthAutoRefreshTimer) {
                    startHealthAutoRefresh();
                }
            } catch(e) {
                console.error('Health error:', e);
                if (!silent) showToast('خطأ في جلب بيانات الخادم', 'error');
                // Update status badge to show error
                const statusText = document.getElementById('health-status-text');
                if (statusText) { statusText.innerText = '❌ فشل الاتصال بالخادم'; statusText.style.color = '#ef4444'; }
            }
        }
        function openBillingModal() {
            const bp = document.getElementById('health-billing-provider')?.innerText || '';
            const bc = document.getElementById('health-billing-cost')?.innerText || '';
            const br = document.getElementById('health-billing-renewal')?.innerText || '';
            const bpl = document.getElementById('health-billing-plan')?.innerText || '';
            
            document.getElementById('billing-provider').value = bp === '--' ? '' : bp;
            document.getElementById('billing-cost').value = bc === '--' ? '' : bc;
            // Handle date format matching if needed, or leave empty if invalid
            document.getElementById('billing-renewal').value = br === '--' ? '' : br;
            document.getElementById('billing-plan').value = bpl === '--' ? '' : bpl;
            
            document.getElementById('billing-modal').classList.add('active');
        }

        async function saveBilling() {
            const provider = document.getElementById('billing-provider').value;
            const cost = document.getElementById('billing-cost').value;
            const renewal = document.getElementById('billing-renewal').value;
            const plan = document.getElementById('billing-plan').value;
            
            try {
                const res = await fetch(apiUrl('saas', 'settings', 'billing'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ billing: { provider, cost, renewal_date: renewal, plan } })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('تم حفظ بيانات الاشتراك', 'success');
                    closeModal('billing-modal');
                    loadServerHealth(); // refresh
                } else {
                    showToast('فشل الحفظ', 'error');
                }
            } catch(e) {
                showToast('خطأ في الاتصال', 'error');
            }
        }

        async function loadTickets() {
            const status = document.getElementById('tickets-status-filter').value;
            const tbody = document.getElementById('tickets-table-body');
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">جاري تحميل التذاكر...</td></tr>';
            try {
                const res = await fetch(apiUrl('saas', 'tickets') + `&status=${status}`, {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                if (data.success && data.tickets) {
                    if (data.tickets.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">لا يوجد تذاكر مطابقة</td></tr>';
                        return;
                    }
                    tbody.innerHTML = '';
                    data.tickets.forEach(tk => {
                        let statusColor = '#f59e0b'; // open
                        let statusText = 'مفتوحة';
                        if (tk.status === 'in_progress') { statusColor = '#38bdf8'; statusText = 'قيد المعالجة'; }
                        if (tk.status === 'closed') { statusColor = '#10b981'; statusText = 'مغلقة'; }
                        
                        tbody.innerHTML += `
                            <tr style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'" onclick="openTicketDetails(${tk.id}, '${tk.subject}')">
                                <td><strong>#${tk.id}</strong></td>
                                <td>${tk.company_name} <br><small style="color:var(--text-secondary)">${tk.subdomain}</small></td>
                                <td>${tk.subject}</td>
                                <td><span style="color:${statusColor}; font-weight:bold;">${statusText}</span></td>
                                <td style="direction:ltr;">${new Date(tk.created_at).toLocaleString()}</td>
                                <td>
                                    <button class="btn-outline" style="background: ${statusColor}1A; border-color:${statusColor}40; color:${statusColor}; padding: 6px 12px; font-size: 0.85rem; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 8px;" onclick="event.stopPropagation(); toggleTicketStatusDropdown(event, ${tk.id})">
                                        ${statusText} <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem;"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">❌ خطأ في تحميل التذاكر</td></tr>`;
                console.error(err);
            }
        }
        
        let currentStatusUpdateId = null;
        
        function toggleTicketStatusDropdown(event, id) {
            event.stopPropagation();
            let dropdown = document.getElementById('status-inline-dropdown');
            if (!dropdown) {
                dropdown = document.createElement('div');
                dropdown.id = 'status-inline-dropdown';
                dropdown.style.position = 'absolute';
                dropdown.style.zIndex = '99999';
                dropdown.style.background = '#1e293b';
                dropdown.style.border = '1px solid rgba(255,255,255,0.1)';
                dropdown.style.borderRadius = '12px';
                dropdown.style.boxShadow = '0 10px 25px rgba(0,0,0,0.5)';
                dropdown.style.padding = '8px';
                dropdown.style.display = 'none';
                dropdown.style.flexDirection = 'column';
                dropdown.style.gap = '4px';
                dropdown.style.minWidth = '160px';
                document.body.appendChild(dropdown);
                
                document.addEventListener('click', function(e) {
                    if (dropdown.style.display === 'flex' && !dropdown.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });
            }
            
            if (dropdown.style.display === 'flex' && currentStatusUpdateId === id) {
                dropdown.style.display = 'none';
                currentStatusUpdateId = null;
                return;
            }
            
            currentStatusUpdateId = id;
            
            dropdown.innerHTML = `
                <button onclick="submitTicketStatus('open')" style="padding: 10px; background: transparent; border: none; border-radius: 8px; color: #f59e0b; font-weight: bold; cursor: pointer; text-align: right; transition: background 0.2s; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(245, 158, 11, 0.1)'" onmouseout="this.style.background='transparent'"><i class="fa-solid fa-envelope-open-text"></i> مفتوحة</button>
                <button onclick="submitTicketStatus('in_progress')" style="padding: 10px; background: transparent; border: none; border-radius: 8px; color: #38bdf8; font-weight: bold; cursor: pointer; text-align: right; transition: background 0.2s; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(56, 189, 248, 0.1)'" onmouseout="this.style.background='transparent'"><i class="fa-solid fa-spinner"></i> قيد المعالجة</button>
                <button onclick="submitTicketStatus('closed')" style="padding: 10px; background: transparent; border: none; border-radius: 8px; color: #10b981; font-weight: bold; cursor: pointer; text-align: right; transition: background 0.2s; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(16, 185, 129, 0.1)'" onmouseout="this.style.background='transparent'"><i class="fa-solid fa-check-circle"></i> مغلقة</button>
            `;
            
            dropdown.style.display = 'flex';
            
            const btnRect = event.currentTarget.getBoundingClientRect();
            dropdown.style.top = (btnRect.bottom + window.scrollY + 5) + 'px';
            dropdown.style.left = (btnRect.left + window.scrollX) + 'px'; 
            
            const rect = dropdown.getBoundingClientRect();
            if (rect.bottom > window.innerHeight) {
                dropdown.style.top = (btnRect.top + window.scrollY - rect.height - 5) + 'px';
            }
        }

        async function submitTicketStatus(newStatus) {
            if (!currentStatusUpdateId) return;
            const id = currentStatusUpdateId;
            const dropdown = document.getElementById('status-inline-dropdown');
            if(dropdown) dropdown.style.display = 'none';
            try {
                const res = await fetch(apiUrl('saas', 'tickets') + '&a2=status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, status: newStatus })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('تم تحديث حالة التذكرة بنجاح ✅');
                    loadTickets();
                } else {
                    showToast('خطأ: ' + data.error, 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('خطأ في الاتصال بخادم التذاكر', 'error');
            }
        }

        async function openTicketDetails(id, subject) {
            document.getElementById('ticket-chat-title').innerText = '🎫 تذكرة #' + id + ': ' + subject;
            document.getElementById('ticket-chat-id').value = id;
            document.getElementById('ticket-chat-input').value = '';
            document.getElementById('ticket-chat-modal').style.display = 'flex';
            
            const container = document.getElementById('ticket-chat-messages');
            container.innerHTML = '<div style="text-align:center; color:var(--text-secondary);">⏳ جاري التحميل...</div>';
            
            try {
                const res = await fetch(apiUrl('saas', 'tickets', 'replies') + '&id=' + id, {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                
                if (data.success && data.replies && data.replies.length > 0) {
                    container.innerHTML = '';
                    data.replies.forEach(r => {
                        const isAdmin = r.sender === 'admin';
                        const bubble = document.createElement('div');
                        bubble.className = 'chat-bubble ' + (isAdmin ? 'admin' : 'tenant');
                        const date = new Date(r.created_at);
                        const timeStr = date.toLocaleDateString('ar-EG') + ' ' + date.toLocaleTimeString('ar-EG', {hour:'2-digit', minute:'2-digit'});
                        bubble.innerHTML = `
                            <div>${escapeHtml(r.message)}</div>
                            <div class="chat-meta">${isAdmin ? '👑 المشرف' : '👤 العميل'} • ${timeStr}</div>
                        `;
                        container.appendChild(bubble);
                    });
                    container.scrollTop = container.scrollHeight;
                } else {
                    container.innerHTML = '<div style="text-align:center; color:var(--text-secondary); padding:40px 0;">💬 لا توجد رسائل بعد. ابدأ المحادثة بإرسال أول رد!</div>';
                }
            } catch(e) {
                container.innerHTML = '<div style="text-align:center; color:var(--danger-color);">❌ خطأ في جلب المحادثة</div>';
                console.error('Ticket replies error:', e);
            }
        }

        async function sendTicketReply() {
            const ticketId = document.getElementById('ticket-chat-id').value;
            const message = document.getElementById('ticket-chat-input').value.trim();
            if (!message) return showToast('اكتب رسالتك أولاً', 'error');
            
            try {
                const res = await fetch(apiUrl('saas', 'tickets', 'reply'), {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ticket_id: ticketId, message: message })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('ticket-chat-input').value = '';
                    showToast('تم إرسال الرد بنجاح ✅');
                    // Reload the conversation
                    const title = document.getElementById('ticket-chat-title').innerText;
                    const subject = title.replace(/^.*: /, '');
                    openTicketDetails(ticketId, subject);
                } else {
                    showToast(data.error || 'خطأ في إرسال الرد', 'error');
                }
            } catch(e) {
                showToast('خطأ في الاتصال', 'error');
            }
        }
    </script>

<script src="js/magaiees_back.js?v=2026_back_v1" defer></script>
</body>
</html>

