<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php $page_title = 'استلام البضاعة (GRN)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="has-sidebar">
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
<script>
(function initScreenMaster7ThemeEarly() {
  var raw = localStorage.getItem('magaies_active_theme') || 
            localStorage.getItem('magaiees_global_theme') || 
            localStorage.getItem('magaiees_mobile_theme') || 
            localStorage.getItem('ui_theme') || 
            'warm_cream';
  var aliasMap = {
    'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream',
    'pure-platinum': 'ultra_white', 'offwhite': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white',
    'pearl-platinum': 'exec_gray', 'nordic-paper': 'warm_cream', 'solar_contrast': 'warm_cream',
    'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian', 'ocean-navy': 'midnight_obsidian', 'navy': 'midnight_obsidian',
    'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
    'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled', 'dark': 'pure_oled',
    'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight'
  };
  var getCk = function(n) { var m = document.cookie.match(new RegExp("(?:^|; )" + n + "=([^;]*)")); return m ? decodeURIComponent(m[1]) : ""; };
  var savedTone = getCk('magaies_light_tone') || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || '';
  var toneToTheme = { 'dark_midnight':'midnight_obsidian','pure_oled':'pure_oled','mocha_brown':'mocha_brown','slate_twilight':'slate_twilight','ivory_default':'warm_cream','royal_bronze':'warm_cream','platinum_gray':'exec_gray','crystal_silver':'ultra_white','sage_pearl':'warm_cream','sky_alabaster':'ultra_white','rose_blossom':'warm_cream','warm_mocha_dark':'warm_cream','deep_slate_dark':'exec_gray' };
  var activeKey = (savedTone && toneToTheme[savedTone]) ? toneToTheme[savedTone] : (aliasMap[raw] || raw);
  var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
  if (!valid7.includes(activeKey)) activeKey = 'warm_cream';
  var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);
  var activeTone = savedTone || (isLight ? 'ivory_default' : 'dark_midnight');

  document.documentElement.setAttribute('data-theme', activeKey);
  document.documentElement.setAttribute('data-light-tone', activeTone);
  if (isLight) document.documentElement.classList.add('light-theme');
  else document.documentElement.classList.remove('light-theme');

  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) {
      document.body.setAttribute('data-theme', activeKey);
      document.body.setAttribute('data-light-tone', activeTone);
      if (isLight) document.body.classList.add('light-theme');
      else document.body.classList.remove('light-theme');
    }
  });
})();
</script>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
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

        /* Ambient Animated Background for GRN (Solid Matte Coordinated Navy - No Reflections) */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            z-index: -1; overflow: hidden; pointer-events: none;
            background: #070f1d !important;
            background-image: none !important;
        }
        .orb {
            display: none !important;
        }

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
            /* gpu-opt */ /* gpu-opt */
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
        .icon-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.3s ease, opacity 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease; }
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
            padding: 20px; display: flex; flex-direction: column; /* gpu-opt */ /* gpu-opt */
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
            background: rgba(0,0,0,0.2); transition: transform 0.3s ease, opacity 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
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
        .editor-left { flex: 1; display: flex; flex-direction: column; background: rgba(15, 23, 42, 0.65); border: 1px solid var(--glass-border); border-radius: 20px; padding: 20px; /* gpu-opt */ /* gpu-opt */ box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; min-height: 0; }
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
            animation: none; /* cpu-opt */
        }
        .write-indicator-box:focus, .write-indicator-box.focused {
            animation: none !important;
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.55) !important;
        }
    </style>
</head>
<body class="has-sidebar">

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
<link rel="stylesheet" href="css/screens/purchase-receipt-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-receipt-s1.css') ?: '2027' ?>">

    <div class="dash-main" id="grn-proportional-root">
        <?php $header_theme_class = 'header-theme-purchase'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- View 1: Hub (Select Invoice with Pending Goods) -->
        <div class="view-container active" id="view-hub">

            <div class="neo-card">
                <!-- السطر الأول (الأعلى): حالة الشاشة + البوابة اللوجستية + زر التحويل + عداد الأذونات + الفلاتر المحاسبية (الكل، التاريخ من وإلى مع أيقونة التقويم يمين التاريخ، المورد، المستودع، البحث) -->
                <div class="neo-card-title">
                    <div id="hub-top-bar-items">
                                                <span id="hub-title-text">قيد الاستلام</span><a href="smart-logistics.php?mode=purchases&from=purchase-receipt.php" id="btn-grn-logistics-portal" class="btn-action" style="text-decoration:none;">📥 استلام المشتريات</a>
                        <div id="grn-3level-bar" style="display:none !important;"></div>
                        
                        <div class="uiverse-switch demo3" title="اضغط للتبديل بين البضاعة قيد الاستلام والأذونات المستلمة">
                            <input type="checkbox" id="uiverse-toggle" onchange="toggleHubViewBySwitch(this.checked)">
                            <label><i></i></label>
                        </div>
                        <span id="grn-live-sync-badge" style="display:none !important;"></span>
                        <div id="hub-counter-badge"></div>

                        <span class="grn-row1-divider" aria-hidden="true"></span>

                        <!-- الفلاتر المحاسبية الفورية في السطر الأول -->
                        <button type="button" id="btn-grn-hub-reset-all" class="btn-action btn-cancel grn-light-clear-btn" style="height: 36px; padding: 0 14px; font-size: 0.84rem; font-weight: 900; border-radius: 9px; border: 2px solid rgba(56, 189, 248, 0.45);" onclick="window.resetGrnHubFilters && window.resetGrnHubFilters()" title="عرض الكل وإلغاء الفلاتر">🔄 الكل / إلغاء</button>

                        <div class="grn-date-input-wrap" title="من تاريخ — اضغط على علامة التقويم يمين التاريخ للاختيار">
                            <button type="button" class="grn-date-picker-btn" onclick="var el=document.getElementById('grn-filter-date-from'); if(el){if(el.showPicker)el.showPicker();else{el.focus();el.click();}}" title="اضغط لإظهار التقويم (من تاريخ)">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </button>
                            <input type="date" id="grn-filter-date-from" class="grn-log-input" title="من تاريخ" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                        </div>

                        <div class="grn-date-input-wrap" title="إلى تاريخ — اضغط على علامة التقويم يمين التاريخ للاختيار">
                            <button type="button" class="grn-date-picker-btn" onclick="var el=document.getElementById('grn-filter-date-to'); if(el){if(el.showPicker)el.showPicker();else{el.focus();el.click();}}" title="اضغط لإظهار التقويم (إلى تاريخ)">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </button>
                            <input type="date" id="grn-filter-date-to" class="grn-log-input" title="إلى تاريخ" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                        </div>

                        <div style="display:inline-flex;align-items:center;gap:4px;">
                            <select id="grn-filter-supplier" class="grn-log-input" style="height: 36px; min-width: 140px; font-size: 0.80rem;" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                                <option value="">كل الموردين</option>
                            </select>
                            <button type="button" id="btn-grn-quick-add-supplier" onclick="window.openGrnQuickSupplierModal && window.openGrnQuickSupplierModal()" title="إضافة مورد جديد فوراً" style="width:34px;height:36px;border-radius:8px;background:rgba(14,165,233,0.18);border:1px solid rgba(56,189,248,0.35);color:#38bdf8;font-size:0.9rem;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">+</button>
                        </div>

                        <div style="display:inline-flex;align-items:center;gap:4px;">
                            <select id="grn-filter-warehouse" class="grn-log-input" style="height: 36px; min-width: 135px; font-size: 0.80rem;" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                                <option value="">كل المستودعات</option>
                            </select>
                            <button type="button" id="btn-grn-hub-quick-wh" onclick="window.openGrnQuickWarehouseModal && window.openGrnQuickWarehouseModal()" title="إضافة مستودع جديد فوراً" style="width:34px;height:36px;border-radius:8px;background:rgba(16,185,129,0.18);border:1px solid rgba(52,211,153,0.35);color:#34d399;font-size:0.9rem;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">+</button>
                        </div>

                        <input type="text" id="search-inv" class="v-input" placeholder="بحث ذكي (فاتورة / مورد / صنف)..." oninput="if(currentHubView === 'pending') renderPendingInvoices(true); else renderRecentGRNs(true);">
                    </div>
                </div>

                <!-- شريط الرصيد والملخص المالي لبوابة الاستلام (دستور المشتريات 2027: جنيه ⇄ دولار + الذكاء الاصطناعي + التخصيص) -->
                <div id="grn-hub-fin-strip" class="grn-fin-strip-2027" style="margin-bottom: 10px; padding: 8px 14px; border-radius: 12px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; background: rgba(15, 23, 42, 0.78); border: 2px solid rgba(56, 189, 248, 0.35);">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span class="grn-fin-cap" style="background: linear-gradient(135deg, #d97706, #b45309); border: 2px solid #fcd34d; color: #ffffff !important; padding: 4px 12px; border-radius: 9px; font-weight: 900; font-size: 0.86rem; display: inline-flex; align-items: center; gap: 6px;">📦 قيد الاستلام: <strong id="grn-hub-stat-pending" style="font-size: 1.22rem; font-family: 'Outfit', sans-serif; color: #ffffff !important;">0</strong></span>
                        <span class="grn-fin-cap" style="background: linear-gradient(135deg, #059669, #047857); border: 2px solid #6ee7b7; color: #ffffff !important; padding: 4px 12px; border-radius: 9px; font-weight: 900; font-size: 0.86rem; display: inline-flex; align-items: center; gap: 6px;">✅ أذونات مستلمة: <strong id="grn-hub-stat-received" style="font-size: 1.22rem; font-family: 'Outfit', sans-serif; color: #ffffff !important;">0</strong></span>
                        <span class="grn-fin-cap" style="background: linear-gradient(135deg, #e11d48, #be123c); border: 2px solid #fda4af; color: #ffffff !important; padding: 4px 13px; border-radius: 9px; font-weight: 900; font-size: 0.86rem; display: inline-flex; align-items: center; gap: 6px;">💰 قيمة المعلق (ج.س): <strong id="grn-hub-stat-val-sdg" style="font-size: 1.25rem; font-family: 'Outfit', sans-serif; color: #ffffff !important;">0 ج.س</strong></span>
                        <span class="grn-fin-cap" style="background: linear-gradient(135deg, #0284c7, #0369a1); border: 2px solid #7dd3fc; color: #ffffff !important; padding: 4px 13px; border-radius: 9px; font-weight: 900; font-size: 0.86rem; display: inline-flex; align-items: center; gap: 6px;">💵 بالدولار ($): <strong id="grn-hub-stat-val-usd" style="font-size: 1.25rem; font-family: 'Outfit', sans-serif; color: #ffffff !important;">$0.00</strong></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 7px; flex-wrap: wrap;">
                        <button type="button" id="grn-hub-curr-btn" onclick="window.toggleGrnHubCurrency && window.toggleGrnHubCurrency()" style="height: 36px; padding: 0 13px; border-radius: 9px; background: linear-gradient(135deg, #7c3aed, #5b21b6); border: 2px solid #c4b5fd; color: #ffffff !important; font-weight: 900; font-size: 0.84rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">💱 <span id="grn-hub-curr-lbl">جنيه ⇄</span></button>
                        <button type="button" id="btn-grn-hub-excel" class="btn-action grn-light-clear-btn grn-btn-excel" style="height: 36px; padding: 0 12px; font-size: 0.82rem; font-weight: 900; border-radius: 9px; background: rgba(16, 185, 129, 0.25); border: 2px solid #10b981;" onclick="window.exportGrnHubToExcel && window.exportGrnHubToExcel()">📊 Excel</button>
                        <button type="button" id="btn-grn-hub-a4" class="btn-action grn-light-clear-btn grn-btn-a4" style="height: 36px; padding: 0 12px; font-size: 0.82rem; font-weight: 900; border-radius: 9px; background: rgba(14, 165, 233, 0.25); border: 2px solid #0284c7;" onclick="window.printGrnHubReport && window.printGrnHubReport('a4')">📄 PDF</button>
                        <button type="button" id="btn-grn-hub-thermal" class="btn-action grn-light-clear-btn grn-btn-thermal" style="height: 36px; padding: 0 12px; font-size: 0.82rem; font-weight: 900; border-radius: 9px; background: rgba(124, 58, 237, 0.25); border: 2px solid #7c3aed;" onclick="window.printGrnHubReport && window.printGrnHubReport('thermal')">🖨️ حراري</button>
                        <button type="button" id="btn-grn-hub-wa" class="btn-action grn-light-clear-btn grn-btn-wa" style="height: 36px; padding: 0 12px; font-size: 0.82rem; font-weight: 900; border-radius: 9px; background: rgba(5, 150, 105, 0.25); border: 2px solid #059669;" onclick="window.shareGrnHubSummaryWhatsApp && window.shareGrnHubSummaryWhatsApp()">💬 واتساب</button>
                        <button type="button" class="unified-col-props-btn" onclick="openGridSettings()" style="display:inline-flex !important; align-items:center !important; gap:6px !important; height:36px !important; padding:0 14px !important; border-radius:9px !important; background:linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important; border:1.8px solid #38bdf8 !important; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; font-size:0.84rem !important; font-weight:900 !important; cursor:pointer !important;"><span>⚙️</span><span>الأعمدة</span></button>
                    </div>
                </div>
                <div id="grn-hub-filters-bar" style="display:none !important;"><div id="grn-cross-screen-nav"></div></div>
                
                <div id="hub-pending-container">
                    <div class="grid-scroll-wrap">
                        <table class="pos-grid" id="grn-hub-pending-table">
                            <thead>
                                <tr>
                                    <th style="text-align:center;">التاريخ</th>
                                    <th style="text-align:center;">رقم الفاتورة</th>
                                    <th style="text-align:center;">المورد</th>
                                    <th style="text-align:center;">الأصناف (المعلق والقيمة)</th>
                                    <th style="text-align:center;">إجمالي المعلق</th>
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
                        </div>
                    </div>
                </div>

                <div id="hub-received-container" style="display: none;">
                    <div class="grid-scroll-wrap" style="padding-top: 10px;">
                        <div id="recent-grns-container" style="overflow-x: auto;">
                            <!-- Populated dynamically by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View 2: Editor (Receive Items — Zero-Scroll Balanced Layout) -->
        <div class="view-container" id="view-editor" style="gap: 10px; animation: fadeIn 0.2s ease-out;">
            <!-- Header Metadata Bar + Purchase Constitution 2027 Financial Strip -->
            <div class="grn-meta-bar" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 10px; background: rgba(30, 41, 59, 0.4); border: 2px solid rgba(56,189,248,0.28); padding: 10px 18px; border-radius: 14px;  align-items: center;">
                <div style="display: flex; flex-direction: column; gap: 3px;">
                    <span class="grn-meta-lbl" style="color: #94a3b8; font-size: 0.85rem; font-weight: 900;">📄 الرقم:</span>
                    <span id="lbl-inv-no" style="color: #38bdf8; font-size: 1.58rem; font-weight: 900; font-family: 'Outfit', 'Cairo', sans-serif; line-height: 1.1;"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 3px; border-right: 1px solid rgba(255,255,255,0.08); padding-right: 15px; position: relative; padding-left: 78px;">
                    <span class="grn-meta-lbl" style="color: #94a3b8; font-size: 0.85rem; font-weight: 900;">👤 المورد</span>
                    <span id="lbl-supplier" style="color: #fff; font-size: 1.18rem; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</span>
                    <button type="button" id="grn-supplier-ledger-btn" onclick="window.openGrnSupplierLedger && window.openGrnSupplierLedger()" title="كشف حساب المورد (أستاذ)" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%) !important; height: 32px; padding: 0 11px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0369a1); border: 2px solid #38bdf8; color: #ffffff !important; font-size: 0.82rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 3px 8px rgba(2,132,199,0.35);">📖 أستاذ</button>
                </div>
                <div style="display: flex; flex-direction: column; gap: 3px; border-right: 1px solid rgba(255,255,255,0.08); padding-right: 15px;">
                    <span class="grn-meta-lbl" style="color: #94a3b8; font-size: 0.85rem; font-weight: 900;">📅 التاريخ</span>
                    <span id="lbl-date" style="color: #fff; font-size: 1.38rem; font-weight: 900; font-family: 'Outfit', 'Cairo', sans-serif; line-height: 1.15;">-</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 3px; border-right: 1px solid rgba(255,255,255,0.08); padding-right: 15px;">
                    <span class="grn-meta-lbl" style="color: #94a3b8; font-size: 0.85rem; font-weight: 900;">🛡️ المنشئ</span>
                    <span id="lbl-creator" style="color: #a855f7; font-size: 1.12rem; font-weight: 900;">-</span>
                </div>
            </div>
            <!-- Supplier Financial Strip (Purchase Constitution 2027: الرصيد | المديونية | الشيكات | الحد | المتاح + زر جنيه ⇄ دولار) -->
            <div id="grn-party-fin-strip" class="grn-fin-strip-2027" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 7px 14px; border-radius: 12px; background: rgba(15, 23, 42, 0.78); border: 2px solid rgba(56, 189, 248, 0.32);">
                <div class="grn-fin-cap cap-balance" style="background: linear-gradient(135deg, #334155, #1e293b); border: 2px solid #94a3b8; border-radius: 9px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; color: #ffffff !important;">
                    <span style="font-size: 0.82rem; font-weight: 900; opacity: 0.92;">الرصيد:</span>
                    <strong id="grn-fin-balance" style="font-size: 1.25rem; font-weight: 900; font-family: 'Outfit', sans-serif;">0</strong>
                </div>
                <div class="grn-fin-cap cap-debt" style="background: linear-gradient(135deg, #e11d48, #be123c); border: 2px solid #fda4af; border-radius: 9px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; color: #ffffff !important;">
                    <span style="font-size: 0.82rem; font-weight: 900; opacity: 0.95;">المديونية:</span>
                    <strong id="grn-fin-debt" style="font-size: 1.25rem; font-weight: 900; font-family: 'Outfit', sans-serif;">0</strong>
                </div>
                <div class="grn-fin-cap cap-cheques" style="background: linear-gradient(135deg, #d97706, #b45309); border: 2px solid #fcd34d; border-radius: 9px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; color: #ffffff !important;">
                    <span style="font-size: 0.82rem; font-weight: 900; opacity: 0.95;">الشيكات:</span>
                    <strong id="grn-fin-cheques" style="font-size: 1.25rem; font-weight: 900; font-family: 'Outfit', sans-serif;">0</strong>
                </div>
                <div class="grn-fin-cap cap-limit" style="background: linear-gradient(135deg, #0284c7, #0369a1); border: 2px solid #7dd3fc; border-radius: 9px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; color: #ffffff !important;">
                    <span style="font-size: 0.82rem; font-weight: 900; opacity: 0.95;">الحد:</span>
                    <strong id="grn-fin-limit" style="font-size: 1.25rem; font-weight: 900; font-family: 'Outfit', sans-serif;">مفتوح</strong>
                </div>
                <div class="grn-fin-cap cap-avail" style="background: linear-gradient(135deg, #059669, #047857); border: 2px solid #6ee7b7; border-radius: 9px; padding: 4px 14px; display: inline-flex; align-items: center; gap: 6px; color: #ffffff !important; box-shadow: 0 3px 10px rgba(5,150,105,0.35);">
                    <span style="font-size: 0.85rem; font-weight: 900;">المتاح:</span>
                    <strong id="grn-fin-avail" style="font-size: 1.48rem; font-weight: 900; font-family: 'Outfit', sans-serif;">مفتوح</strong>
                </div>
                <button type="button" id="grn-fin-strip-currency-btn" onclick="window.toggleGrnFinStripCurrency && window.toggleGrnFinStripCurrency()" title="تحويل شريط الرصيد والمديونية والإجمالي بين الجنيه والدولار" style="margin-right: auto; height: 34px; padding: 0 14px; border-radius: 9px; background: linear-gradient(135deg, #7c3aed, #5b21b6); border: 2px solid #c4b5fd; color: #ffffff !important; font-size: 0.9rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 3px 10px rgba(124,58,237,0.35);">
                    <span>💱</span><span id="grn-fin-curr-lbl">جنيه ⇄</span>
                </button>
            </div>

            <div class="editor-layout" style="display: flex; flex-direction: column; gap: 15px; flex: 1; width: 100%; min-height: 0;">
                <!-- Full Width: Items Table -->
                <div class="editor-table-box" style="display: flex; flex-direction: column; width: 100%; flex: 1;">
                    <div class="neo-card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; background: rgba(30, 41, 59, 0.4); padding: 12px 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 1.15rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 8px;">
                            <span>📦</span> أصناف الاستلام
                        </span>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="button" id="btn-grn-fill-all" onclick="window.fillAllRemainingGRNQty && window.fillAllRemainingGRNQty()" title="تعبئة كامل الكميات المتبقية لجميع الأصناف (F4)" style="padding: 9px 14px; font-size: 0.86rem; border-radius: 10px; background: rgba(16, 185, 129, 0.16); border: 2px solid rgba(16, 185, 129, 0.45); color: #34d399; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <span>⚡</span><span>استلام الكل</span><kbd class="grn-kbd-badge">F4</kbd>
                            </button>
                            <button type="button" id="btn-grn-zero-all" onclick="window.zeroAllGRNQty && window.zeroAllGRNQty()" title="تصفير الكميات المستلمة" style="padding: 9px 12px; font-size: 0.84rem; border-radius: 10px; background: rgba(245, 158, 11, 0.14); border: 2px solid rgba(245, 158, 11, 0.4); color: #fbbf24; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <span>🧹</span><span>تصفير</span>
                            </button>
                            <button class="btn-action grn-btn-save-primary" style="padding: 10px 22px; font-size: 0.96rem; font-weight: 900; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:10px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16,185,129,0.4); border: 2.2px solid #34d399; color: #fff; cursor: pointer;" onclick="saveReceiptNote()">
                                <span>💾</span><span>اعتماد الاستلام</span><kbd class="grn-kbd-badge">F10</kbd>
                            </button>
                            <button type="button" class="btn-action" id="btn-grn-ai-prices" style="padding: 9px 14px; font-size: 0.88rem; border-radius: 10px; background: linear-gradient(135deg, #7c3aed, #4f46e5); border: 2px solid #c4b5fd; color: #ffffff !important; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(124,58,237,0.35);" onclick="window.openGrnAIAuditModal && window.openGrnAIAuditModal()">
                                <span>🤖</span><span>الذكاء الاصطناعي للأسعار</span>
                            </button>
                            <button class="btn-action" style="padding: 9px 14px; font-size: 0.88rem; border-radius: 10px; background: rgba(56, 189, 248, 0.15); border: 2px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;" onclick="openGridSettings()">
                                <span>⚙️</span><span>تنسيق الجدول</span>
                            </button>
                            <button class="btn-action btn-cancel" style="padding: 9px 14px; font-size: 0.88rem; border-radius: 10px; margin-inline-start: 6px;" onclick="showHub(true)">↩️ رجوع <kbd class="grn-kbd-badge">Esc</kbd></button>
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
                <div class="editor-bottom-bar" style="position: sticky; bottom: 0; z-index: 20; background: rgba(15, 23, 42, 0.85); border: 1.5px solid rgba(56, 189, 248, 0.25); padding: 12px 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);  flex-wrap: wrap;">
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

                        <div class="grn-tot-capsule-sdg" style="background: rgba(30, 41, 59, 0.85); border: 2px solid rgba(250, 204, 21, 0.55); padding: 8px 18px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: #94a3b8; font-size: 0.88rem; font-weight: 900;">القيمة المستلمة (ج.س):</span>
                            <span id="lbl-total-received-val" style="color: #facc15; font-weight: 900; font-family: 'Outfit', sans-serif; font-size: 1.48rem;">0 ج.س</span>
                        </div>

                        <div class="grn-tot-capsule-usd" style="background: rgba(6, 78, 59, 0.85); border: 2px solid #34d399; padding: 8px 18px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: #a7f3d0; font-size: 0.88rem; font-weight: 900;">الإجمالي بالدولار ($):</span>
                            <span id="lbl-total-received-val-usd" style="color: #ffffff; font-weight: 900; font-family: 'Outfit', sans-serif; font-size: 1.42rem;">$0.00</span>
                        </div>
                    </div>

                    <div id="grn-bottom-actions-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <button class="btn-action grn-btn-save-primary" style="padding: 11px 28px; font-size: 1.05rem; font-weight: 900; display:inline-flex; align-items:center; justify-content:center; gap:10px; border-radius:12px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 20px rgba(16,185,129,0.4); border: 2.2px solid #34d399; color: #fff; cursor: pointer;" onclick="saveReceiptNote()">
                            <span>💾</span><span>اعتماد الاستلام</span><kbd class="grn-kbd-badge">F10</kbd>
                        </button>
                        <button type="button" class="btn-action btn-cancel grn-light-clear-btn" style="padding: 10px 18px; font-size: 0.92rem; font-weight: 900; border-radius: 11px;" onclick="showHub(true)">↩️ رجوع للشاشة الرئيسية</button>
                    </div>
                </div>
            </div>

            <!-- Recent Operations moved to Hub View -->
        </div>
        </div>
    </div>

    <!-- Notification Toast Container -->
    <div class="toast-container" id="toast-container" style="position:fixed; bottom:20px; left:20px; z-index:99999; display:flex; flex-direction:column; gap:10px;"></div>

    <script defer src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo filemtime('js/app.js'); ?>"></script>
    <script>(function(){var l=0,v="<?php echo file_exists('js/magaies-smart-guide.js') ? filemtime('js/magaies-smart-guide.js') : '2027'; ?>";window.loadMagaiesSmartGuide=function(c){if(l){if(c)c();return;}l=1;var s=document.createElement('script');s.src='js/magaies-smart-guide.js?v='+v;s.onload=function(){if(c)c();};document.body.appendChild(s);};window.addEventListener('keydown',function(e){if(e.key==='F1'){e.preventDefault();window.loadMagaiesSmartGuide();}});window.addEventListener('contextmenu',function(){window.loadMagaiesSmartGuide();},{once:true,passive:true});})();</script>
    <script defer src="js/elsajana-universal-ui-2027.js?v=<?php echo file_exists('js/elsajana-universal-ui-2027.js') ? filemtime('js/elsajana-universal-ui-2027.js') : 1; ?>"></script>
<script defer src="js/screens/purchase-receipt-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-receipt-j1.js') ?: '2027' ?>"></script>

    <!-- Context Menu HTML -->
    <div id="grid-context-menu" style="display: none; position: absolute; z-index: 100000; background: rgba(15, 23, 42, 0.95);  border: 1px solid #38bdf8; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); padding: 5px; min-width: 150px;">
        <div onclick="openGridSettings()" style="padding: 10px 15px; color: #fff; cursor: pointer; border-radius: 5px; transition: background 0.2s; font-size: 0.9rem;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='none'">
            🎨 خيارات وتنسيق الجدول
        </div>
    </div>

    <!-- Grid Settings Modal -->
    <div class="modal-overlay" id="grid-settings-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7);  z-index: 100001; justify-content: center; align-items: center;">
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
            /* gpu-opt */
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
            /* gpu-opt */
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
        html:not(.light-theme) .grn-col-baseQty:hover,
        html:not(.light-theme) .factor-input:hover,
        html:not(.light-theme) .qty-input:hover,
        html:not(.light-theme) .wh-select:hover,
        html:not(.light-theme) .notes-input:hover,
        html:not(.light-theme) .v-input:hover,
        html:not(.light-theme) .factor-input:focus,
        html:not(.light-theme) .qty-input:focus,
        html:not(.light-theme) .wh-select:focus,
        html:not(.light-theme) .notes-input:focus,
        html:not(.light-theme) .v-input:focus {
            border-color: #38bdf8 !important;
            outline: none !important;
            transform: scale(1.03) translateY(-1px) !important;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98)) !important;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.5), 0 0 18px rgba(56, 189, 248, 0.38) !important;
            z-index: 15 !important;
        }
        html.light-theme .factor-input:focus,
        html.light-theme .qty-input:focus,
        html.light-theme .wh-select:focus,
        html.light-theme .notes-input:focus,
        html.light-theme .v-input:focus,
        html.light-theme .write-indicator-box:focus,
        html.light-theme .write-indicator-box.focused {
            background: #ffffff !important;
            color: #020617 !important;
            -webkit-text-fill-color: #020617 !important;
            border: 2.5px solid #0369a1 !important;
            box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.22) !important;
            z-index: 15 !important;
        }

        html:not(.light-theme) .write-indicator-box:focus,
        html:not(.light-theme) .write-indicator-box.focused {
            animation: none !important;
            border-color: #34d399 !important;
            transform: scale(1.04) translateY(-1px) !important;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.55), 0 0 22px rgba(16, 185, 129, 0.65) !important;
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
            animation: none; /* cpu-opt */
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
            /* gpu-opt */
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1),
                        box-shadow 0.28s ease,
                        border-color 0.22s ease !important;
        }
        .btn-action:hover {
            transform: translateY(-3px) scale(1.055) !important;
            border-color: transparent !important;
            animation: none; /* cpu-opt */
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
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
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
                const capEl = tr.querySelector('[data-inv-id]');
                const cleanInvId = (capEl && capEl.getAttribute('data-inv-id')) || (invIdTd ? invIdTd.textContent.replace(/^[^\w#\-]+/, '').trim() : '');
                if (cleanInvId && dateTd && pendingMap[cleanInvId]) {
                    const invId = cleanInvId;
                    const origInv = (allPurchases || []).find(p => String(p.invoiceNo || p.id) === String(invId)) || {};
                    const rawDt = origInv.savedAt || origInv.createdAt || pendingMap[invId].date;
                    dateTd.innerHTML = window.formatSpacedDateTime(rawDt);
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
        window.deleteGRN = function(grnId, skipConfirm) {
            _origDeleteGRN.apply(this, arguments);
            if (skipConfirm && typeof window.pushToCloud === 'function') {
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
            /* gpu-opt */
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
            transition: transform 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.26s cubic-bezier(0.34, 1.56, 0.64, 1);
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
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
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
            transition: transform 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.26s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.26s cubic-bezier(0.34, 1.56, 0.64, 1);
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

<script defer src="js/full-receipt-confirm-2027.js?v=<?= @filemtime(__DIR__ . '/js/full-receipt-confirm-2027.js') ?: '2027' ?>"></script>
<script defer src="js/screens/purchase-receipt-j2.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-receipt-j2.js') ?: '2027' ?>"></script>
    <!-- ══════════════════════════════════════════════════════════════════════════
         PURCHASE DESIGN CONSTITUTION 2027 — APPLIED TO PURCHASE-RECEIPT.PHP
         9 Tones + Sculpted Borders + Financial Strip + Price Radar + AI Audit
         ══════════════════════════════════════════════════════════════════════════ -->
<link rel="stylesheet" id="grn-purchase-constitution-2027-style" href="css/screens/purchase-receipt-s2.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-receipt-s2.css') ?: '2027' ?>">

    <!-- AI Price Audit Modal (Purchase Constitution 2027) -->
    <div id="grn-ai-audit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.72);  z-index:100020; align-items:center; justify-content:center; padding:16px;">
        <div class="grn-ai-modal-card" style="width:820px; max-width:96vw; max-height:88vh; display:flex; flex-direction:column; background:#111e36; border:3px solid #7c3aed; border-radius:18px; padding:20px; box-shadow:0 20px 50px rgba(0,0,0,0.65); color:#ffffff;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(124,58,237,0.35); padding-bottom:12px; margin-bottom:14px;">
                <h3 style="margin:0; font-size:1.3rem; font-weight:900; display:flex; align-items:center; gap:8px;">
                    <span>🤖</span><span>رادار الذكاء الاصطناعي لمراجعة أسعار وتكلفة أصناف الاستلام</span>
                </h3>
                <button type="button" onclick="document.getElementById('grn-ai-audit-modal').style.display='none'" style="background:#ef4444; color:#fff; border:none; border-radius:8px; width:34px; height:34px; font-weight:900; cursor:pointer;">✕</button>
            </div>
            <div id="grn-ai-audit-body" style="overflow-y:auto; flex:1; padding-right:4px;"></div>
        </div>
    </div>

<script defer id="grn-purchase-constitution-2027-script" src="js/screens/purchase-receipt-j3.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-receipt-j3.js') ?: '2027' ?>"></script>
<style id="grn-clean-minimal-2027">
#grn-cross-screen-nav, #grn-hub-filters-bar, #grn-3level-bar, #btn-grn-c4-impact-pill, #btn-grn-c5-role-pill, #btn-grn-c6-sync-pill, #btn-grn-c7-eye-pill,
#grn-c8-tafqeet-bar, #grn-c8-parked-strip, #grn-c9-staged-banner, #grn-c10-commercial-bar, #grn-c12-security-bar, #grn-c13-cyber-bar, #grn-c14-command-bar,
#grn-c15-master-bar, #grn-c16-zero-error-bar, #grn-fc1-ai-command-bar, #grn-fc1-landscape-hdr-pill, #btn-grn-audio-summary, #grn-c5-dropdown-mirror,
#btn-grn-fc2-proactive-alerts, #header-smart-guide-btn, #magaies-smart-guide-fab, .smart-guide-banner, .guide-hint-banner { display: none !important; }
#hub-top-bar-items { display: flex !important; align-items: center !important; gap: 8px !important; flex-wrap: wrap !important; width: 100% !important; }
#search-inv { flex: 1 1 200px !important; min-width: 180px !important; height: 36px !important; }
#grn-hub-pending-table { border-collapse: collapse !important; width: 100% !important; }
#grn-hub-pending-table th, #grn-hub-pending-table td { border-left: 1px solid rgba(148, 163, 184, 0.28) !important; border-bottom: 1.5px solid rgba(56, 189, 248, 0.35) !important; text-align: center !important; vertical-align: middle !important; padding: 7px 10px !important; }
#grn-hub-pending-table th:last-child, #grn-hub-pending-table td:last-child { border-left: none !important; }
#grn-hub-pending-table tbody tr:nth-child(odd) { background: #070e1d !important; }
#grn-hub-pending-table tbody tr:nth-child(even) { background: #1d3557 !important; }
:root.light-theme #grn-hub-pending-table th, :root.light-theme #grn-hub-pending-table td { border-left: 1px solid rgba(92, 40, 8, 0.22) !important; border-bottom: 1.5px solid rgba(92, 40, 8, 0.32) !important; }
:root.light-theme #grn-hub-pending-table tbody tr:nth-child(odd) { background: #ffffff !important; }
:root.light-theme #grn-hub-pending-table tbody tr:nth-child(even) { background: #e6d8c3 !important; }
</style>
</body>
</html>