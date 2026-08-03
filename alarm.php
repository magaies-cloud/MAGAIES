<?php $page_title = 'مركز التنبيهات'; ?>
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
    <title>مركز التنبيهات - مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.65);
            --border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.2);
            
            --theme-danger: #f43f5e;
            --theme-warning: #f59e0b;
            --theme-info: #0ea5e9;
            --theme-purple: #8b5cf6;
        }

        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0;
            min-height: 100vh;
            font-family: 'Tajawal', sans-serif;
            color: #fff;
            position: relative;
            overflow-x: hidden;
        }

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

        .orb-1 { width: 500px; height: 500px; background: var(--theme-danger); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-warning); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-info); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Layout Container */
        .alarm-main {
            width: 100%; max-width: 100%;
            margin: 0 auto; padding: 0px 25px 25px 25px;
            box-sizing: border-box;
            display: flex; flex-direction: column; gap: 30px;
        }


        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 16px;
            background: linear-gradient(135deg, #f43f5e, #f59e0b);
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(244, 63, 94, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #94a3b8; font-weight: 500; }

        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.6rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }
        .header-center #last-refresh-time { font-size: 0.8rem; color: #94a3b8; margin-top: 5px; font-weight: 600; }

        .header-controls { display: flex; align-items: center; gap: 15px; }
        .refresh-btn {
            background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3);
            color: #fb7185; padding: 10px 20px; border-radius: 14px;
            font-size: 0.9rem; font-weight: 700; cursor: pointer;
            transition: all 0.3s; display: flex; align-items: center; gap: 8px; font-family: 'Tajawal', sans-serif;
            box-shadow: 0 5px 15px rgba(244, 63, 94, 0.1);
        }
        .refresh-btn:hover { background: rgba(244, 63, 94, 0.25); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(244, 63, 94, 0.2); }
        .refresh-btn .spin-icon { display: inline-block; }
        .refresh-btn.loading .spin-icon { animation: spin 0.6s linear infinite; }
        
        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        /* KPI Summary Grid */
        .summary-cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;
            animation: fadeSlideUp 0.5s ease-out 0.1s both;
        }

        .kpi-card {
            background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 20px; padding: 25px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2), inset 0 1px 1px rgba(255,255,255,0.03);
            transition: all 0.3s ease;
        }
        .kpi-card:hover { transform: translateY(-5px); border-color: var(--border-highlight); }

        .kpi-content { display: flex; flex-direction: column; gap: 5px; }
        .kpi-label { font-size: 1rem; color: #94a3b8; font-weight: 600; }
        .kpi-value { font-size: 2.2rem; font-weight: 900; font-family: 'Outfit', sans-serif; letter-spacing: 1px; }
        
        .kpi-icon { width: 70px; height: 70px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 2rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }

        /* Themes */
        .card-danger .kpi-value { background: linear-gradient(to right, #fb7185, #e11d48); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-danger .kpi-icon { background: rgba(244, 63, 94, 0.15); border-color: rgba(244, 63, 94, 0.3); color: #fb7185; box-shadow: 0 0 20px rgba(244, 63, 94, 0.2); }
        .card-danger:hover { box-shadow: 0 15px 30px rgba(244, 63, 94, 0.1); }

        .card-warning .kpi-value { background: linear-gradient(to right, #fcd34d, #d97706); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-warning .kpi-icon { background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3); color: #fbbf24; box-shadow: 0 0 20px rgba(245, 158, 11, 0.2); }
        .card-warning:hover { box-shadow: 0 15px 30px rgba(245, 158, 11, 0.1); }

        .card-info .kpi-value { background: linear-gradient(to right, #7dd3fc, #0284c7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-info .kpi-icon { background: rgba(14, 165, 233, 0.15); border-color: rgba(14, 165, 233, 0.3); color: #38bdf8; box-shadow: 0 0 20px rgba(14, 165, 233, 0.2); }
        .card-info:hover { box-shadow: 0 15px 30px rgba(14, 165, 233, 0.1); }

        .card-purple .kpi-value { background: linear-gradient(to right, #c084fc, #7e22ce); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-purple .kpi-icon { background: rgba(139, 92, 246, 0.15); border-color: rgba(139, 92, 246, 0.3); color: #c084fc; box-shadow: 0 0 20px rgba(139, 92, 246, 0.2); }
        .card-purple:hover { box-shadow: 0 15px 30px rgba(139, 92, 246, 0.1); }

        /* Main Grid */
        .alarms-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;
            animation: fadeSlideUp 0.5s ease-out 0.2s both;
        }

        .glass-panel {
            background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 24px; padding: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
            transition: all 0.4s ease; display: flex; flex-direction: column;
        }
        .glass-panel:hover { border-color: var(--border-highlight); box-shadow: 0 20px 45px rgba(0,0,0,0.4); }

        .section-title {
            color: #fff; font-size: 1.2rem; font-weight: 800; margin: 0 0 20px;
            display: flex; align-items: center; gap: 12px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .section-title span { font-size: 1.5rem; }

        /* Alarm Individual Card */
        .alarm-card {
            display: flex; align-items: center; gap: 15px; padding: 18px; border-radius: 16px; margin-bottom: 15px;
            transition: all 0.3s; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.03);
        }
        .alarm-card:hover { transform: translateX(-5px); background: rgba(255,255,255,0.03); }
        
        .alarm-card.critical { border-left: 4px solid var(--theme-danger); }
        .alarm-card.warning { border-left: 4px solid var(--theme-warning); }
        .alarm-card.info { border-left: 4px solid var(--theme-info); }
        .alarm-card.purple { border-left: 4px solid var(--theme-purple); }

        .alarm-icon-wrap { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: inset 0 2px 5px rgba(0,0,0,0.3); }
        .alarm-icon-wrap.critical { background: rgba(244,63,94,0.15); border: 1px solid rgba(244,63,94,0.3); animation: pulse-red 2s infinite; }
        .alarm-icon-wrap.warning  { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); animation: pulse-yellow 2s infinite; }
        .alarm-icon-wrap.info     { background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.3); }
        .alarm-icon-wrap.purple   { background: rgba(139,92,246,0.15); border: 1px solid rgba(139,92,246,0.3); }

        .alarm-content { flex: 1; }
        .alarm-title { color: #fff; font-weight: 800; font-size: 1.05rem; margin-bottom: 5px; }
        .alarm-meta { color: #94a3b8; font-size: 0.85rem; line-height: 1.6; }

        .alarm-badge { font-size: 0.75rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; white-space: nowrap; margin-top: 5px; font-family: 'Outfit', sans-serif; display: inline-block; }
        .badge-red    { background: rgba(244,63,94,0.15); color: #fb7185; border: 1px solid rgba(244,63,94,0.3); }
        .badge-yellow { background: rgba(245,158,11,0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
        .badge-blue   { background: rgba(14,165,233,0.15); color: #7dd3fc; border: 1px solid rgba(14,165,233,0.3); }
        .badge-purple { background: rgba(139,92,246,0.15); color: #c084fc; border: 1px solid rgba(139,92,246,0.3); }

        .alarm-action { padding: 8px 16px; border-radius: 10px; text-decoration: none; font-size: 0.8rem; font-weight: 800; color: #fff; flex-shrink: 0; align-self: center; transition: all 0.3s; font-family: 'Tajawal', sans-serif; }
        .alarm-action.red    { background: rgba(244,63,94,0.2); border: 1px solid rgba(244,63,94,0.5); }
        .alarm-action.yellow { background: rgba(245,158,11,0.2); border: 1px solid rgba(245,158,11,0.5); }
        .alarm-action.blue   { background: rgba(14,165,233,0.2); border: 1px solid rgba(14,165,233,0.5); }
        .alarm-action.purple { background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.5); }
        .alarm-action:hover  { filter: brightness(1.2); transform: scale(1.05); }

        /* Animations & Utilities */
        @keyframes fadeSlideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse-red { 0%, 100% { box-shadow: 0 0 0 0 rgba(244,63,94,0.4); } 50% { box-shadow: 0 0 0 12px rgba(244,63,94,0); } }
        @keyframes pulse-yellow { 0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.4); } 50% { box-shadow: 0 0 0 12px rgba(245,158,11,0); } }
        @keyframes spin { to { transform: rotate(360deg); } }

        .empty-state { text-align: center; padding: 50px 20px; color: #64748b; }
        .empty-state .empty-icon { font-size: 4rem; margin-bottom: 15px; opacity: 0.8; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3)); }
        .empty-state p { font-size: 1.1rem; font-weight: 600; }

        .loading-spinner { display: flex; justify-content: center; align-items: center; padding: 50px; }
        .spinner { width: 40px; height: 40px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.1); border-top-color: var(--theme-danger); animation: spin 0.8s linear infinite; }
        
        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }
    
        
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

<!-- Ambient Background -->
<div class="ambient-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="alarm-main">

    <!-- Header -->
    <?php $header_theme_class = 'header-theme-alarm'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- Header Controls Row -->
    <div style="display:flex; justify-content:space-between; align-items:center; background:var(--surface); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--border); border-radius:20px; padding:15px 25px; box-shadow:0 10px 25px rgba(0,0,0,0.2), inset 0 1px 1px rgba(255,255,255,0.03); margin-top:-10px; gap: 15px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span id="total-alarms-badge" class="alarm-badge" style="font-size:0.9rem; padding:6px 16px; border-radius:12px; font-weight:800; font-family:'Tajawal', sans-serif;">🔔 جاري تحميل التنبيهات...</span>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span id="last-refresh-time" style="font-size:0.85rem; color:#94a3b8; font-weight:600;">آخر تحديث: -</span>
            <button onclick="window.location.href='alerts_manager.php'" class="refresh-btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); gap: 8px;">
                <i class="fa-solid fa-triangle-exclamation"></i> تنبيهات التقارير المالية
            </button>
            <button id="refresh-btn" class="refresh-btn" onclick="loadAlarms()">
                <span class="spin-icon">🔄</span> تحديث التنبيهات
            </button>
        </div>
    </div>

    <!-- KPI Summary -->
    <div class="summary-cards" id="summary-cards">
        <div class="kpi-card card-danger">
            <div class="kpi-content">
                <div class="kpi-label">مدفوعات متأخرة</div>
                <div class="kpi-value" id="kpi-overdue">-</div>
            </div>
            <div class="kpi-icon">💸</div>
        </div>
        <div class="kpi-card card-warning">
            <div class="kpi-content">
                <div class="kpi-label">نواقص المخزون</div>
                <div class="kpi-value" id="kpi-lowstock">-</div>
            </div>
            <div class="kpi-icon">📦</div>
        </div>
        <div class="kpi-card card-info">
            <div class="kpi-content">
                <div class="kpi-label">شيكات مستحقة</div>
                <div class="kpi-value" id="kpi-checks">-</div>
            </div>
            <div class="kpi-icon">🧾</div>
        </div>
        <div class="kpi-card card-purple">
            <div class="kpi-content">
                <div class="kpi-label">تسليمات معلقة</div>
                <div class="kpi-value" id="kpi-deliveries">-</div>
            </div>
            <div class="kpi-icon">🚚</div>
        </div>
    </div>

    <!-- Alarms Grid -->
    <div class="alarms-grid" id="alarms-grid">

        <!-- Overdue Payments -->
        <div class="glass-panel" style="border-top: 4px solid var(--theme-danger);">
            <h2 class="section-title"><span style="color:var(--theme-danger);">🔴</span> مدفوعات متأخرة</h2>
            <div id="overdue-list" class="loading-spinner"><div class="spinner" style="border-top-color:var(--theme-danger);"></div></div>
        </div>

        <!-- Low Stock -->
        <div class="glass-panel" style="border-top: 4px solid var(--theme-warning);">
            <h2 class="section-title"><span style="color:var(--theme-warning);">📦</span> نواقص المخزون</h2>
            <div id="lowstock-list" class="loading-spinner"><div class="spinner" style="border-top-color:var(--theme-warning);"></div></div>
        </div>

        <!-- Due Checks -->
        <div class="glass-panel" style="border-top: 4px solid var(--theme-info);">
            <h2 class="section-title"><span style="color:var(--theme-info);">🧾</span> شيكات مستحقة</h2>
            <div id="checks-list" class="loading-spinner"><div class="spinner" style="border-top-color:var(--theme-info);"></div></div>
        </div>

        <!-- Pending Deliveries -->
        <div class="glass-panel" style="border-top: 4px solid var(--theme-purple);">
            <h2 class="section-title"><span style="color:var(--theme-purple);">🚚</span> تسليمات معلقة</h2>
            <div id="deliveries-list" class="loading-spinner"><div class="spinner" style="border-top-color:var(--theme-purple);"></div></div>
        </div>

    </div>

</div>

<script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260803.88213"></script>
<script>
document.addEventListener('DBReady', () => {
    if (!localStorage.getItem('authToken')) {
        window.location.href = 'login.php';
        return;
    }
    loadAlarms();
});

function loadAlarms() {
    const btn = document.getElementById('refresh-btn');
    btn.classList.add('loading');

    // Small delay for UX
    setTimeout(() => {
        try {
            renderOverdue();
            renderLowStock();
            renderChecks();
            renderDeliveries();
            updateSummary();
        } catch(e) {
            console.error(e);
        }

        const now = new Date();
        document.getElementById('last-refresh-time').innerText =
            'آخر تحديث: ' + now.toLocaleTimeString('ar-EG');
        btn.classList.remove('loading');
    }, 600);
}

/* ─── 1. OVERDUE PAYMENTS ─── */
function renderOverdue() {
    const invoices  = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const customers = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
    const today = new Date(); today.setHours(0,0,0,0);

    const results = [];

    invoices.forEach(inv => {
        if (inv.paymentMethod === 'credit' && inv.dueDate) {
            const net = Number(inv.grandTotal) || Number(inv.netTotal) || 0;
            const paid = Number(inv.paidAmount) || 0;
            const remaining = net - paid;
            if (remaining <= 0) return; // Skip if fully paid

            const due = new Date(inv.dueDate); due.setHours(0,0,0,0);
            const diff = Math.ceil((due - today) / 86400000);
            const cust = customers.find(c => c.id === inv.customer || c.id === inv.customerId);
            const custName = cust ? cust.name : (inv.customerName || inv.customer || 'عميل');
            const custPhone = cust && cust.phone ? cust.phone : null;

            if (diff < 0) {
                results.push({ severity: 'critical', inv, custName, custPhone, diff, due, remaining });
            } else if (diff <= 3) {
                results.push({ severity: 'warning', inv, custName, custPhone, diff, due, remaining });
            }
        }
    });

    results.sort((a,b) => a.diff - b.diff);

    const el = document.getElementById('overdue-list');
    if (!results.length) {
        el.innerHTML = emptyState('✅', 'لا توجد مدفوعات متأخرة أو مستحقة. أحسنت!');
        return;
    }

    el.innerHTML = results.map(r => {
        const isLate = r.diff < 0;
        const dayLabel = isLate
            ? `متأخر ${Math.abs(r.diff)} يوم`
            : (r.diff === 0 ? 'اليوم!' : (r.diff === 1 ? 'غداً' : `بعد ${r.diff} أيام`));
        const amount = r.remaining.toLocaleString('en-US');

        return `
        <div class="alarm-card ${isLate ? 'critical' : 'warning'}">
            <div class="alarm-icon-wrap ${isLate ? 'critical' : 'warning'}">${isLate ? '🔴' : '🟡'}</div>
            <div class="alarm-content">
                <div class="alarm-title">${r.custName}</div>
                <div class="alarm-meta">
                    فاتورة: <strong style="color:#fff">${r.inv.invoiceNo || '-'}</strong> &nbsp;|&nbsp;
                    المبلغ: <strong style="color:${isLate?'#fb7185':'#fcd34d'}">${amount} SDG</strong><br>
                    تاريخ الاستحقاق: ${r.due.toLocaleDateString('ar-EG')}
                    ${r.custPhone ? `<br>📞 ${r.custPhone}` : ''}
                </div>
                <span class="alarm-badge ${isLate?'badge-red':'badge-yellow'}">${dayLabel}</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">

            </div>
        </div>`;
    }).join('');
}

/* ─── 2. LOW STOCK ─── */
function renderLowStock() {
    const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

    const results = items.filter(item => {
        const stock = item.units && item.units[0] ? (Number(item.units[0].stock) || 0) : 0;
        const safe  = Number(item.safeLimit) || 10;
        return stock <= safe;
    }).map(item => {
        const stock = item.units && item.units[0] ? (Number(item.units[0].stock) || 0) : 0;
        const safe  = Number(item.safeLimit) || 10;
        return { ...item, stock, safe };
    }).sort((a,b) => a.stock - b.stock);

    const el = document.getElementById('lowstock-list');
    if (!results.length) {
        el.innerHTML = emptyState('✅', 'المخزون بخير! لا توجد أصناف تحت الحد الآمن.');
        return;
    }

    el.innerHTML = results.slice(0, 15).map(item => {
        const isCritical = item.stock <= 0;
        const percentage = Math.max(0, Math.min(100, Math.round((item.stock / item.safe) * 100))) || 0;
        const colorHex = isCritical ? '#fb7185' : '#fcd34d';
        const colorBg = isCritical ? 'rgba(244,63,94,0.15)' : 'rgba(245,158,11,0.15)';
        const unitName = item.units && item.units[0] ? item.units[0].name : '';

        return `
        <div style="background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 20px; margin-bottom: 15px; display: flex; align-items: center; gap: 20px; transition: transform 0.3s, box-shadow 0.3s; position: relative; overflow: hidden; border-right: 4px solid ${colorHex};" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            
            <!-- Corner Status Badge -->
            <div style="position: absolute; top:0; right:0; padding: 4px 15px; background: ${colorHex}; color: #000; font-size: 0.7rem; font-weight: 900; border-bottom-left-radius: 12px; box-shadow: -2px 2px 10px rgba(0,0,0,0.2);">
                ${isCritical ? 'مخزون صفري!' : 'ناقص عن الحد'}
            </div>
            
            <!-- Stock Value Block -->
            <div style="width: 70px; height: 70px; border-radius: 14px; background: ${colorBg}; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);">
                <span style="font-size: 1.6rem; color: ${colorHex}; font-weight: 900; line-height: 1; text-shadow: 0 2px 10px ${colorBg};">${item.stock}</span>
                <span style="font-size: 0.65rem; color: rgba(255,255,255,0.6); margin-top: 4px; font-weight:bold;">متوفر</span>
            </div>
            
            <!-- Item Details & Progress Bar -->
            <div style="flex: 1;">
                <div style="font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 12px; letter-spacing: 0.5px;">${item.name || 'صنف غير معروف'}</div>
                
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; white-space: nowrap; min-width: 90px;">الحد الآمن: ${item.safe} ${unitName}</span>
                    <div style="flex: 1; height: 8px; background: rgba(0,0,0,0.3); border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);">
                        <div style="width: ${percentage}%; height: 100%; background: ${colorHex}; border-radius: 6px; box-shadow: 0 0 10px ${colorBg}; transition: width 1s ease-out;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Action Button -->
            <div>
                <button onclick="window.location.href='purchase.php'" style="background: rgba(255,255,255,0.03); color: ${colorHex}; border: 1px solid rgba(255,255,255,0.1); padding: 12px 20px; border-radius: 12px; font-weight: 900; font-family: 'Outfit', 'Cairo', sans-serif; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s; font-size: 0.9rem;" onmouseover="this.style.background='${colorHex}'; this.style.color='#000'; this.style.borderColor='transparent';" onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.color='${colorHex}'; this.style.borderColor='rgba(255,255,255,0.1)';">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="2"></circle>
                        <circle cx="20" cy="21" r="2"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    طلب شراء
                </button>
            </div>
        </div>`;
    }).join('');
}

/* ─── 3. DUE CHECKS ─── */
function renderChecks() {
    const checks    = JSON.parse(localStorage.getItem('erp_checks_v1') || '[]');
    const customers = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
    const today = new Date(); today.setHours(0,0,0,0);

    const results = checks.filter(ch => {
        if (ch.status === 'cleared' || ch.status === 'deposited') return false;
        if (!ch.dueDate) return false;
        const due = new Date(ch.dueDate); due.setHours(0,0,0,0);
        return (due - today) / 86400000 <= 7;
    }).map(ch => {
        const due  = new Date(ch.dueDate); due.setHours(0,0,0,0);
        const diff = Math.ceil((due - today) / 86400000);
        const cust = customers.find(c => c.id === ch.customerId);
        return { ...ch, diff, due, custName: cust ? cust.name : (ch.drawerName || 'غير محدد') };
    }).sort((a,b) => a.diff - b.diff);

    const el = document.getElementById('checks-list');
    if (!results.length) {
        el.innerHTML = emptyState('✅', 'لا توجد شيكات مستحقة خلال الأسبوع القادم.');
        return;
    }

    el.innerHTML = results.map(ch => {
        const isLate = ch.diff < 0;
        const dayLabel = isLate ? `متأخر ${Math.abs(ch.diff)} يوم`
            : (ch.diff === 0 ? 'اليوم!' : `بعد ${ch.diff} أيام`);
        const amount = (Number(ch.amount) || 0).toLocaleString('en-US');

        return `
        <div class="alarm-card ${isLate ? 'critical' : 'info'}">
            <div class="alarm-icon-wrap ${isLate ? 'critical' : 'info'}">${isLate ? '⛔' : '🧾'}</div>
            <div class="alarm-content">
                <div class="alarm-title">${ch.custName}</div>
                <div class="alarm-meta">
                    شيك رقم: <strong style="color:#fff">${ch.checkNo || '-'}</strong> &nbsp;|&nbsp;
                    المبلغ: <strong style="color:#7dd3fc">${amount} SDG</strong><br>
                    تاريخ الاستحقاق: ${ch.due.toLocaleDateString('ar-EG')}
                </div>
                <span class="alarm-badge ${isLate?'badge-red':'badge-blue'}">${dayLabel}</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                <a data-href="checks-cycle.php" onclick="window.location.href='checks-cycle.php'" class="alarm-action ${isLate?'red':'blue'}">إدارة الشيك</a>
            </div>
        </div>`;
    }).join('');
}

/* ─── 4. PENDING DELIVERIES ─── */
function renderDeliveries() {
    const invoices  = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const customers = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');

    const pending = invoices.filter(inv => {
        const s = inv.deliveryStatus;
        return s !== 'delivered' && s !== 'Admin_Closed' && s !== 'Delivered_Pending_Admin';
    }).map(inv => {
        const cust = customers.find(c => c.id === inv.customerId || c.id === inv.customer);
        return { ...inv, custName: cust ? cust.name : (inv.customerName || inv.customer || 'عميل') };
    });

    const el = document.getElementById('deliveries-list');
    if (!pending.length) {
        el.innerHTML = emptyState('✅', 'جميع الطلبات تم تسليمها. لا توجد تسليمات معلقة!');
        return;
    }

    el.innerHTML = pending.slice(0, 15).map(inv => {
        const amount = (Number(inv.grandTotal) || Number(inv.netTotal) || 0).toLocaleString('en-US');
        const statusLabel = {
            'pending':    'قيد الانتظار',
            'processing': 'جاري التجهيز',
            'ready':      'جاهز للتسليم',
        }[inv.deliveryStatus] || 'معلق';

        return `
        <div class="alarm-card purple">
            <div class="alarm-icon-wrap purple">🚚</div>
            <div class="alarm-content">
                <div class="alarm-title">${inv.custName}</div>
                <div class="alarm-meta">
                    فاتورة: <strong style="color:#fff">${inv.invoiceNo || '-'}</strong> &nbsp;|&nbsp;
                    المبلغ: <strong style="color:#c084fc">${amount} SDG</strong><br>
                    التاريخ: ${inv.date || '-'}
                </div>
                <span class="alarm-badge badge-purple">${statusLabel}</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                <a data-href="sales-delivery.php" onclick="window.location.href='sales-delivery.php'" class="alarm-action purple">تسليم</a>
            </div>
        </div>`;
    }).join('');
}

/* ─── Summary KPI ─── */
function updateSummary() {
    const invoices  = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const items     = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
    const checks    = JSON.parse(localStorage.getItem('erp_checks_v1') || '[]');
    const today = new Date(); today.setHours(0,0,0,0);

    // Overdue count (only active credit balances that are overdue or due within 3 days)
    const overdueCount = invoices.filter(inv => {
        if (inv.paymentMethod !== 'credit' || !inv.dueDate) return false;
        const net = Number(inv.grandTotal) || Number(inv.netTotal) || 0;
        const paid = Number(inv.paidAmount) || 0;
        if (net - paid <= 0) return false; // Already paid
        const due = new Date(inv.dueDate); due.setHours(0,0,0,0);
        return Math.ceil((due - today) / 86400000) <= 3;
    }).length;

    // Low stock count
    const lowStockCount = items.filter(item => {
        const stock = item.units && item.units[0] ? (Number(item.units[0].stock) || 0) : 0;
        return stock <= (Number(item.safeLimit) || 10);
    }).length;

    // Checks due soon
    const checksCount = checks.filter(ch => {
        if (!ch.dueDate || ch.status === 'cleared' || ch.status === 'deposited') return false;
        const due = new Date(ch.dueDate); due.setHours(0,0,0,0);
        return (due - today) / 86400000 <= 7;
    }).length;

    // Pending deliveries
    const deliveriesCount = invoices.filter(inv => {
        const s = inv.deliveryStatus;
        return s !== 'delivered' && s !== 'Admin_Closed' && s !== 'Delivered_Pending_Admin';
    }).length;

    document.getElementById('kpi-overdue').innerText    = overdueCount;
    document.getElementById('kpi-lowstock').innerText   = lowStockCount;
    document.getElementById('kpi-checks').innerText     = checksCount;
    document.getElementById('kpi-deliveries').innerText = deliveriesCount;

    const total = overdueCount + lowStockCount + checksCount + deliveriesCount;
    const badge = document.getElementById('total-alarms-badge');
    if (badge) {
        if (total === 0) {
            badge.style.background   = 'rgba(16, 185, 129, 0.15)';
            badge.style.color        = '#34d399';
            badge.innerText = '✅ لا توجد تنبيهات نشطة';
        } else {
            badge.style.background   = 'rgba(244, 63, 94, 0.15)';
            badge.style.color        = '#fb7185';
            badge.innerText = `🔔 ${total} تنبيهات حرجة`;
        }
    }
}

function emptyState(icon, msg) {
    return `<div class="empty-state">
        <div class="empty-icon">${icon}</div>
        <p>${msg}</p>
    </div>`;
}
</script>
</body>
</html>

