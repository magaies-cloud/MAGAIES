<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() === 0) { @ob_start('ob_gzhandler'); } ?>
<?php $page_title = 'البيانات الأساسية'; ?>
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
    <title>مقاييس (MAGAIEES) - البيانات الأساسية</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
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

        :root.light-theme,
        html.light-theme,
        html[data-theme="warm_cream"],
        html[data-theme="ultra_white"],
        html[data-theme="exec_gray"] {
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --surface-hover: rgba(241, 245, 249, 0.98);
            --border: rgba(0, 0, 0, 0.12);
            --border-highlight: rgba(0, 0, 0, 0.22);
            --text-primary: #000000;
            --text-secondary: #000000;
        }
        /* ═══════════════════════════════════════════════════════════════════════════
           🎨 قانون التباين اللوني الصارم للخطوط والشاشات الوليدة (100% Contrast Law)
           - السمات الغامقة: خط أبيض فاتح صريح (#FFFFFF) على جميع العناصر والشاشات الوليدة
           - السمات الفاتحة: خط أسود صريح (#000000) على جميع العناصر والشاشات الوليدة
           ═══════════════════════════════════════════════════════════════════════════ */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .md-content-area,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .md-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .neon-glass-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .pro-field-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .input-group,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .pro-field-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) h1,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) h2,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) h3,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) h4,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) h5,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) table.pro-table th,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) table.pro-table td,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) textarea,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .md-child-modal,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .md-child-modal * {
            color: #ffffff !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) input::placeholder,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) textarea::placeholder {
            color: rgba(255, 255, 255, 0.65) !important;
        }

        :root.light-theme body,
        html.light-theme body,
        html[data-theme="warm_cream"] body,
        html[data-theme="ultra_white"] body,
        html[data-theme="exec_gray"] body {
            background-color: #d6c7aa !important;
            color: #000000 !important;
        }
        :root.light-theme .ambient-bg,
        html[data-theme="warm_cream"] .ambient-bg,
        html[data-theme="ultra_white"] .ambient-bg,
        html[data-theme="exec_gray"] .ambient-bg {
            background: radial-gradient(circle at 50% 50%, #dfd1b8 0%, #cfc0a3 100%) !important;
        }
        :root.light-theme .panel,
        :root.light-theme .neo-card,
        :root.light-theme .glass-panel,
        :root.light-theme .md-card,
        :root.light-theme .neon-glass-panel,
        :root.light-theme #customer-form,
        :root.light-theme #supplier-form,
        :root.light-theme #warehouse-form,
        :root.light-theme #bank-form,
        :root.light-theme #safe-form,
        :root.light-theme #item-form,
        :root.light-theme #employee-form,
        :root.light-theme #costcenter-form,
        :root.light-theme #admin-wa-policy-panel-cust,
        :root.light-theme #admin-wa-policy-panel-sup,
        :root.light-theme .md-child-modal-card,
        html[data-theme="warm_cream"] .md-card,
        html[data-theme="warm_cream"] .neon-glass-panel,
        html[data-theme="warm_cream"] #customer-form,
        html[data-theme="warm_cream"] #supplier-form,
        html[data-theme="warm_cream"] #item-form,
        html[data-theme="warm_cream"] #admin-wa-policy-panel-cust,
        html[data-theme="warm_cream"] #admin-wa-policy-panel-sup,
        html[data-theme="warm_cream"] .md-child-modal-card,
        html[data-theme="ultra_white"] .md-card,
        html[data-theme="ultra_white"] .neon-glass-panel,
        html[data-theme="ultra_white"] #customer-form,
        html[data-theme="ultra_white"] #supplier-form,
        html[data-theme="ultra_white"] #item-form,
        html[data-theme="ultra_white"] #admin-wa-policy-panel-cust,
        html[data-theme="ultra_white"] #admin-wa-policy-panel-sup,
        html[data-theme="ultra_white"] .md-child-modal-card,
        html[data-theme="exec_gray"] .md-card,
        html[data-theme="exec_gray"] .neon-glass-panel,
        html[data-theme="exec_gray"] #customer-form,
        html[data-theme="exec_gray"] #supplier-form,
        html[data-theme="exec_gray"] #item-form,
        html[data-theme="exec_gray"] #admin-wa-policy-panel-cust,
        html[data-theme="exec_gray"] #admin-wa-policy-panel-sup,
        html[data-theme="exec_gray"] .md-child-modal-card {
            background: #e4d7be !important;
            border: 2px solid #8c7b65 !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.09) !important;
        }
        :root.light-theme .md-content-area *,
        :root.light-theme .md-child-modal *,
        :root.light-theme #master-data-delete-modal *,
        html[data-theme="warm_cream"] .md-content-area *,
        html[data-theme="warm_cream"] .md-child-modal *,
        html[data-theme="warm_cream"] #master-data-delete-modal *,
        html[data-theme="ultra_white"] .md-content-area *,
        html[data-theme="ultra_white"] .md-child-modal *,
        html[data-theme="ultra_white"] #master-data-delete-modal *,
        html[data-theme="exec_gray"] .md-content-area *,
        html[data-theme="exec_gray"] .md-child-modal *,
        html[data-theme="exec_gray"] #master-data-delete-modal * {
            color: #000000 !important;
            text-shadow: none !important;
        }
        :root.light-theme .md-content-area div[style*="background: rgba(15, 23, 42"],
        :root.light-theme .md-content-area div[style*="background:rgba(15,23,42"],
        :root.light-theme .md-content-area div[style*="background: rgba(0,0,0"],
        :root.light-theme .md-content-area div[style*="background:rgba(0,0,0"],
        :root.light-theme #master-data-delete-modal div[style*="background: rgba(0,0,0"],
        :root.light-theme #master-data-delete-modal div[style*="background:rgba(0,0,0"],
        html[data-theme="warm_cream"] .md-content-area div[style*="background: rgba(15, 23, 42"],
        html[data-theme="warm_cream"] .md-content-area div[style*="background:rgba(15,23,42"],
        html[data-theme="warm_cream"] .md-content-area div[style*="background: rgba(0,0,0"],
        html[data-theme="warm_cream"] .md-content-area div[style*="background:rgba(0,0,0"] {
            background: rgba(0, 0, 0, 0.04) !important;
            border-color: rgba(0, 0, 0, 0.14) !important;
        }
        :root.light-theme input::placeholder,
        :root.light-theme textarea::placeholder,
        html[data-theme="warm_cream"] input::placeholder,
        html[data-theme="ultra_white"] input::placeholder,
        html[data-theme="exec_gray"] input::placeholder {
            color: rgba(0, 0, 0, 0.55) !important;
        }
        :root.light-theme td, :root.light-theme th, :root.light-theme tr {
            color: #000000 !important;
            border-color: rgba(0, 0, 0, 0.1) !important;
        }
        :root.light-theme .pos-grid thead th {
            background: rgba(0, 0, 0, 0.05) !important;
            color: #000000 !important;
        }
        :root.light-theme .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.98) !important;
        }
        :root.light-theme .pos-grid tbody tr:hover {
            background: rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme input, :root.light-theme select, :root.light-theme textarea {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.22) !important;
        }
        :root.light-theme .md-tabs-sidebar {
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        :root.light-theme .md-tab-btn {
            background: #ffffff !important;
            border-color: var(--tab-border-color, rgba(0,0,0,0.2)) !important;
            color: #000000 !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        :root.light-theme .md-tab-btn .md-tab-label {
            color: #000000 !important;
        }
        :root.light-theme .md-tab-btn:hover,
        :root.light-theme .md-tab-btn.active {
            background: var(--tab-active-bg, linear-gradient(135deg, #3b82f6, #60a5fa)) !important;
            border-color: var(--tab-border-color, transparent) !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px var(--tab-glow-color, rgba(59, 130, 246, 0.35)) !important;
            transform: scale(1.03) translateY(-1px);
        }
        :root.light-theme .md-sidebar-panel,
        :root.light-theme .md-content-panel {
            background: #ffffff !important;
            border-color: rgba(0,0,0,0.1) !important;
        }
        :root.light-theme table.pro-table th {
            background: #e2e8f0 !important;
            color: #000000 !important;
            border-bottom: 2px solid #94a3b8 !important;
        }
        :root.light-theme table.pro-table td {
            color: #000000 !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td {
            background: rgba(0, 0, 0, 0.03) !important;
        }
        :root.light-theme label, :root.light-theme .h-label, :root.light-theme .tot-lbl {
            color: #000000 !important;
        }
        .kbd-hint {
            display: inline-block;
            padding: 1px 5px;
            font-size: 0.7rem;
            font-family: var(--md-font-num);
            font-weight: 900;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            line-height: 1.1;
            pointer-events: none;
        }
        :root.light-theme .kbd-hint {
            background: rgba(0, 0, 0, 0.08) !important;
            border-color: rgba(0, 0, 0, 0.22) !important;
            color: #000000 !important;
        }
        .md-num-live-badge {
            font-family: var(--md-font-num);
            font-size: 0.72rem;
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 5px;
            background: rgba(56, 189, 248, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.35);
            direction: ltr;
            display: inline-block;
        }
        @keyframes mdSavePulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 14px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .md-save-flash {
            animation: mdSavePulse 0.65s ease-out;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0; min-height: calc(100vh + 160px);
            display: flex; flex-direction: column; overflow-x: hidden;
            font-family: 'Tajawal', sans-serif; color: #fff;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.8); }

        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background:
                radial-gradient(circle at 88% 12%, rgba(99, 102, 241, 0.14) 0%, transparent 45%),
                radial-gradient(circle at 12% 88%, rgba(56, 189, 248, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, #0a0f1d 0%, #020617 100%);
        }
        .orb { display: none !important; }

        /* 👑 World-Class Cursor Focus, Smoothness & Visual Comfort (60fps Zero-Lag) */
        .md-content-area input:not([type="checkbox"]):not([type="radio"]):focus,
        .md-content-area select:focus,
        .md-content-area textarea:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.28), 0 4px 12px rgba(0, 0, 0, 0.25) !important;
            outline: none !important;
            transition: border-color 0.12s ease, box-shadow 0.12s ease;
        }
        button[data-md-busy="1"] {
            opacity: 0.65 !important;
            pointer-events: none !important;
            cursor: wait !important;
        }

        .dash-main { width: 96%; max-width: 1600px; margin: 0 auto; padding: 0px 0 20px 0; box-sizing: border-box; display: flex; flex-direction: column; gap: 15px; flex: 1; }



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

        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 36px; height: 36px; border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 10px rgba(0,0,0,0.3); }

        .user-info { display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }

        /* Master Data Specific Styles */
        /* Master Data Specific Styles - Ultra Premium Animated Glassmorphic Tabs */
        .md-tabs-sidebar {
            background: rgba(12, 10, 22, 0.65);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: clamp(6px, 0.75vw, 12px);
            display: flex;
            flex-direction: row;
            align-items: stretch;
            justify-content: space-between;
            overflow: visible;
            gap: clamp(4px, 0.65vw, 10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.1);
            margin-bottom: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        .md-tab-btn {
            position: relative;
            flex: 1 1 0;
            min-width: 0;
            min-height: clamp(44px, 4vw, 60px);
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 3.8px solid var(--tab-border-color, rgba(255, 255, 255, 0.2));
            border-radius: 14px;
            padding: clamp(4px, 0.45vw, 8px) clamp(4px, 0.55vw, 10px);
            font-size: clamp(0.9rem, 1.65vw, 1.7rem);
            font-weight: 900;
            line-height: 1.1;
            color: #e2e8f0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0;
            white-space: nowrap;
            overflow: hidden;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, background 0.35s ease, border-color 0.35s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 1;
            box-sizing: border-box;
        }

        .md-tab-btn.tab-costcenters {
            flex: 1.42 1 0;
        }

        .md-tab-icon {
            display: none !important;
        }

        .md-tab-label {
            display: inline-block;
            position: relative;
            z-index: 4;
            font-weight: 900;
            line-height: 1.1;
            white-space: nowrap;
            text-align: center;
            pointer-events: none;
            transform-origin: center center;
        }

        .md-tab-btn:hover {
            transform: translateY(-2px);
            color: #ffffff !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 6px 16px var(--tab-glow-color) !important;
            background: var(--tab-hover-bg) !important;
            z-index: 10;
        }

        .md-tab-btn.active {
            color: #ffffff !important;
            background: var(--tab-active-bg) !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 6px 20px var(--tab-glow-color), inset 0 1px 2px rgba(255,255,255,0.35) !important;
            transform: translateY(-1px);
        }

        /* 🎨 تخصيص الألوان والحدود والإضاءة العاكسة لكل تبويب */
        
        /* 🏢 المخازن - سماوي زاهي (Sky Blue) */
        .md-tab-btn.tab-warehouses {
            --tab-border-color: #38bdf8;
            --tab-glow-color: rgba(56, 189, 248, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(2, 132, 199, 0.4), rgba(56, 189, 248, 0.6));
            --tab-active-bg: linear-gradient(135deg, #0284c7, #38bdf8);
        }

        /* 👥 العملاء - زمردي نيون (Emerald Green) */
        .md-tab-btn.tab-customers {
            --tab-border-color: #34d399;
            --tab-glow-color: rgba(52, 211, 153, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(5, 150, 105, 0.4), rgba(52, 211, 153, 0.6));
            --tab-active-bg: linear-gradient(135deg, #059669, #34d399);
        }

        /* 🏭 الموردين - بنفسجي ساحر (Purple Violet) */
        .md-tab-btn.tab-suppliers {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(192, 132, 252, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(124, 58, 237, 0.4), rgba(192, 132, 252, 0.6));
            --tab-active-bg: linear-gradient(135deg, #7c3aed, #c084fc);
        }

        /* 🏦 البنوك - ذهبي متألق (Amber Gold) */
        .md-tab-btn.tab-banks {
            --tab-border-color: #fbbf24;
            --tab-glow-color: rgba(251, 191, 36, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(217, 119, 6, 0.4), rgba(251, 191, 36, 0.6));
            --tab-active-bg: linear-gradient(135deg, #d97706, #fbbf24);
        }

        /* 💰 الخزن - ليموني فسفوري (Neon Lime) */
        .md-tab-btn.tab-safes {
            --tab-border-color: #4ade80;
            --tab-glow-color: rgba(74, 222, 128, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(22, 163, 74, 0.4), rgba(74, 222, 128, 0.6));
            --tab-active-bg: linear-gradient(135deg, #16a34a, #4ade80);
        }

        /* 📦 الأصناف والوحدات - وردي ملكي (Fuchsia Rose) */
        .md-tab-btn.tab-items {
            --tab-border-color: #f472b6;
            --tab-glow-color: rgba(244, 114, 182, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(219, 39, 119, 0.4), rgba(244, 114, 182, 0.6));
            --tab-active-bg: linear-gradient(135deg, #db2777, #f472b6);
        }

        /* 👤 الموظفين - نيلي براق (Indigo Blue) */
        .md-tab-btn.tab-employees {
            --tab-border-color: #818cf8;
            --tab-glow-color: rgba(129, 140, 248, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(79, 70, 229, 0.4), rgba(129, 140, 248, 0.6));
            --tab-active-bg: linear-gradient(135deg, #4f46e5, #818cf8);
        }

        /* 📊 مراكز التكلفة - برتقالي متوهج (Vibrant Orange) */
        .md-tab-btn.tab-costcenters {
            --tab-border-color: #fb923c;
            --tab-glow-color: rgba(251, 146, 60, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(234, 88, 12, 0.4), rgba(251, 146, 60, 0.6));
            --tab-active-bg: linear-gradient(135deg, #ea580c, #fb923c);
        }

        /* 🚪 خروج / رجوع - روبي أحمر (Ruby Red) */
        .md-tab-btn.tab-exit {
            --tab-border-color: #f87171;
            --tab-glow-color: rgba(248, 113, 113, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(220, 38, 38, 0.4), rgba(248, 113, 113, 0.6));
            --tab-active-bg: linear-gradient(135deg, #dc2626, #f87171);
        }

        /* التوافق التام مع الوضع الفاتح Light Theme */
        :root.light-theme .md-tabs-sidebar {
            background: rgba(255, 255, 255, 0.8) !important;
            border-color: rgba(0, 0, 0, 0.1) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .md-tab-btn {
            color: #1e293b !important;
            background: rgba(255, 255, 255, 0.9) !important;
        }
        :root.light-theme .md-tab-btn:hover,
        :root.light-theme .md-tab-btn.active {
            color: #ffffff !important;
        }

        .md-content-area { flex: 1; display: flex; flex-direction: column; width: 100%; box-sizing: border-box; }
        .md-tab-content { display: none; height: 100%; animation: fadeInSlideUp 0.4s ease-out forwards; }
        .md-tab-content.active { display: flex; flex-direction: column; }
        
        .md-card { background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: 18px; padding: 18px; box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03); margin-bottom: 15px; }
        .md-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; flex-wrap: wrap; gap: 10px; }
        .md-card-title { margin: 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        
        .btn-add { background: linear-gradient(135deg, #38bdf8, #818cf8); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; box-shadow: 0 4px 10px rgba(56, 189, 248, 0.3); transition: all 0.2s; font-family: 'Tajawal', sans-serif;}
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4); }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-size: 0.85rem; color: #94a3b8; font-weight: 700; }
        .input-group input, .input-group select { width: 100%; padding: 10px 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; font-size: 0.9rem; outline: none; box-sizing: border-box; font-family: 'Tajawal', sans-serif; transition: all 0.3s; }
        .input-group input:focus, .input-group select:focus { border-color: #38bdf8; background: rgba(0,0,0,0.4); box-shadow: 0 0 0 2px rgba(56,189,248,0.2); }
        
        .inner-tabs { display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px; overflow-x: auto; }
        .inner-tab-btn { background: none; border: none; padding: 8px 15px; font-weight: 700; font-size: 0.9rem; color: #94a3b8; cursor: pointer; border-radius: 8px; white-space: nowrap; font-family: 'Tajawal', sans-serif; transition: all 0.3s;}
        .inner-tab-btn:hover { background: rgba(255,255,255,0.05); color: #fff;}
        .inner-tab-btn.active { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .inner-tab-content { display: none; }
        .inner-tab-content.active { display: block; animation: fadeInSlideUp 0.3s; }
        
        .toggle-sw { position:relative; display:inline-block; width:44px; height:24px; }
        .toggle-sw input { opacity:0; width:0; height:0; }
        .toggle-sl { position:absolute; cursor:pointer; inset:0; background:rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); transition:.3s; border-radius:24px; }
        .toggle-sl:before { content:""; position:absolute; height:16px; width:16px; left:4px; bottom:3px; background:#fff; transition:.3s; border-radius:50%; }
        .toggle-sw input:checked + .toggle-sl { background:#10b981; border-color:#10b981;}
        .toggle-sw input:checked + .toggle-sl:before { transform:translateX(20px); }
        
        .sec-hdr { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:10px; margin-bottom:15px; border-right:3px solid; background: rgba(255,255,255,0.02); }
        .sec-hdr h5 { margin:0; font-size:0.95rem; font-weight:800; }
        .item-section { margin-bottom:20px; }

        /* Tables override for glassmorphism */
        table.pro-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
        table.pro-table th { padding: 10px; text-align: right; color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 700; }
        table.pro-table td { padding: 10px; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.03); }
        table.pro-table tbody tr:hover td { background: rgba(255,255,255,0.02); }
        
        @media (max-width: 768px) {
            .header-controls { justify-content: center; width: 100%; flex-wrap: wrap; }
            .md-tabs-sidebar { padding: 8px; }
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           🏛️ نظام التصميم الهندسي القياسي الموحد (Unified ERP Design System)
           1. توحيد نوع الخط ومقاساته (4 مستويات قياسية صارمة بدون أي شذوذ)
           2. توحيد مقاس الأيقونات (1rem قياسي متناسق)
           3. توحيد ارتفاع وعرض الخانات والأزرار والجداول والـ Padding والـ Border-Radius
           ═══════════════════════════════════════════════════════════════════════════ */
        :root {
            --md-font-sans: 'Cairo', 'Tajawal', sans-serif;
            --md-font-num: 'Outfit', 'Cairo', monospace;
            --md-fs-title: 1.05rem;      /* المستوى 1: العناوين الرئيسية للأقسام والبطاقات */
            --md-fs-label: 0.84rem;      /* المستوى 2: عناوين الحقول ورؤوس أعمدة الجداول */
            --md-fs-input: 0.94rem;      /* المستوى 3: النصوص داخل الخانات والقوائم والجداول والأزرار */
            --md-fs-num: 1.04rem;        /* المستوى 4: الأرقام والأرصدة والإجماليات والأكواد */
            --md-ctrl-h: 40px;           /* ارتفاع موحد لجميع الخانات والقوائم المنسدلة */
            --md-btn-h: 38px;            /* ارتفاع موحد لجميع الأزرار التنفيذية */
            --md-radius-ctrl: 10px;      /* حواف موحدة للخانات والأزرار */
            --md-radius-card: 14px;      /* حواف موحدة للبطاقات والحاويات */
            --md-icon-size: 1rem;        /* مقاس موحد لجميع الأيقونات */
        }

        body, .dash-main, .glass-panel, .tab-content, button, input, select, textarea {
            font-family: var(--md-font-sans) !important;
        }

        .md-card-title,
        .md-card h3,
        .md-card h4,
        .md-card h5,
        .neon-glass-panel h4,
        .neon-glass-panel h5,
        .glass-panel h3,
        .glass-panel h4,
        .glass-panel h5,
        #form-mode-indicator,
        #bank-form-title,
        #safe-form-title {
            font-family: var(--md-font-sans) !important;
            font-size: var(--md-fs-title) !important;
            font-weight: 900 !important;
            line-height: 1.35 !important;
            margin: 0 !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .input-group label,
        .pro-field-card .pro-field-label,
        label,
        table.pro-table th {
            font-family: var(--md-font-sans) !important;
            font-size: var(--md-fs-label) !important;
            font-weight: 800 !important;
            line-height: 1.3 !important;
            letter-spacing: 0 !important;
        }

        .input-group label {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin: 0 0 6px 0 !important;
            color: var(--text-secondary) !important;
            min-height: 20px !important;
        }

        input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]),
        select,
        .input-group input:not([type="checkbox"]):not([type="radio"]),
        .input-group select,
        .pro-field-card input:not([type="checkbox"]):not([type="hidden"]),
        .pro-field-card select,
        .form-control,
        .search-input,
        .system-currency-select {
            font-family: var(--md-font-sans) !important;
            font-size: var(--md-fs-input) !important;
            font-weight: 700 !important;
            height: var(--md-ctrl-h) !important;
            min-height: var(--md-ctrl-h) !important;
            max-height: var(--md-ctrl-h) !important;
            padding: 0 12px !important;
            border-radius: var(--md-radius-ctrl) !important;
            line-height: calc(var(--md-ctrl-h) - 2px) !important;
            box-sizing: border-box !important;
            width: 100% !important;
            margin: 0 !important;
        }

        select option {
            font-size: var(--md-fs-input) !important;
            font-weight: 700 !important;
            padding: 8px 12px !important;
            background-color: #0f172a;
            color: #ffffff;
        }
        :root.light-theme select option {
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        textarea,
        .input-group textarea,
        #cust-notes,
        #sup-notes,
        #item-description {
            font-family: var(--md-font-sans) !important;
            font-size: var(--md-fs-input) !important;
            font-weight: 700 !important;
            min-height: 64px !important;
            padding: 8px 12px !important;
            border-radius: var(--md-radius-ctrl) !important;
            line-height: 1.45 !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }

        .pro-field-card textarea,
        .pro-field-card #cust-notes,
        .pro-field-card #sup-notes {
            height: var(--md-ctrl-h) !important;
            min-height: var(--md-ctrl-h) !important;
            max-height: var(--md-ctrl-h) !important;
            padding: 8px 12px !important;
            line-height: 1.35 !important;
            resize: none !important;
        }

        #customer-search-input,
        #item-search-input,
        #item-search,
        #supplier-search-input {
            height: var(--md-ctrl-h) !important;
            min-height: var(--md-ctrl-h) !important;
            max-height: var(--md-ctrl-h) !important;
            font-size: var(--md-fs-input) !important;
            font-weight: 700 !important;
            padding-right: 38px !important;
            border-radius: var(--md-radius-ctrl) !important;
        }

        input[type="number"],
        input[type="tel"],
        input[type="date"],
        input.pro-num-input,
        input.num-input,
        .num-val,
        [id*="balance"],
        [id*="price"]:not(select),
        [id*="limit"],
        [id*="phone"],
        [id*="qty"],
        [id*="rate"]:not(div):not(label):not(small),
        [id*="code"]:not(div),
        [id*="whatsapp"]:not(div):not(button),
        #kpi-total-items,
        #kpi-low-stock,
        #kpi-total-categories {
            font-family: var(--md-font-num) !important;
            font-size: var(--md-fs-num) !important;
            font-weight: 800 !important;
            font-variant-numeric: tabular-nums !important;
            letter-spacing: 0.3px !important;
        }

        table input:not([type="checkbox"]):not([type="radio"]),
        table select,
        table textarea,
        table.pro-table input:not([type="checkbox"]):not([type="radio"]),
        table.pro-table select {
            height: 34px !important;
            min-height: 34px !important;
            max-height: 34px !important;
            padding: 0 10px !important;
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            border-radius: 8px !important;
        }

        table input[type="number"],
        table.pro-table input[type="number"] {
            font-family: var(--md-font-num) !important;
            font-size: 0.96rem !important;
            font-weight: 800 !important;
            min-width: 95px !important;
        }

        table.pro-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: var(--md-fs-input) !important;
        }

        table.pro-table th {
            height: 42px !important;
            padding: 8px 12px !important;
            white-space: nowrap !important;
            vertical-align: middle !important;
        }

        table.pro-table td,
        table.pro-table td strong,
        .tot-val,
        .val {
            font-size: var(--md-fs-input) !important;
            font-weight: 700 !important;
            padding: 8px 12px !important;
            height: 44px !important;
            vertical-align: middle !important;
            font-variant-numeric: tabular-nums !important;
        }

        .btn-action,
        .btn-add,
        .inner-tab-btn {
            height: var(--md-btn-h) !important;
            min-height: var(--md-btn-h) !important;
            padding: 0 14px !important;
            border-radius: var(--md-radius-ctrl) !important;
            font-family: var(--md-font-sans) !important;
            font-size: 0.88rem !important;
            font-weight: 800 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            cursor: pointer;
            box-sizing: border-box !important;
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }

        .btn-text {
            height: 30px !important;
            min-height: 30px !important;
            padding: 0 10px !important;
            border-radius: 8px !important;
            font-family: var(--md-font-sans) !important;
            font-size: 0.82rem !important;
            font-weight: 800 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 4px !important;
            background: rgba(56, 189, 248, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.2);
            line-height: 1 !important;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-text:hover {
            background: rgba(56, 189, 248, 0.16);
            transform: translateY(-1px);
        }

        .tab-btn .icon,
        .btn-action svg,
        .btn-add svg,
        .btn-text svg {
            font-size: var(--md-icon-size) !important;
            width: var(--md-icon-size) !important;
            height: var(--md-icon-size) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            flex-shrink: 0 !important;
        }



        
        .split-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        .split-panel {
            position: relative;
            z-index: 1;
            border-radius: 12px;
        }
        .split-container:hover .split-panel {
            filter: none;
            opacity: 1;
            transform: none;
        }
        .split-container .split-panel:hover,
        .split-container .split-panel:focus-within {
            filter: none;
            opacity: 1;
            transform: none;
            z-index: 1;
        }
        
        /* تأثيرات هادئة وسلسة للأزرار (Smooth & Calm Action Buttons) */
        .btn-action, .btn-add, .hover-glow {
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
        .btn-action:hover, .btn-add:hover, .hover-glow:hover {
            transform: translateY(-1px);
            filter: brightness(1.08);
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }

        /* ═══════════════════════════════════════════════════════════════════════════
           الشبكة الهندسية الموحدة للبطاقات والحقول (Symmetrical ERP Grid)
           ═══════════════════════════════════════════════════════════════════════════ */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: stretch;
            margin-bottom: 14px;
        }
        .pro-entity-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: stretch;
            margin-bottom: 12px;
        }
        @media (max-width: 1200px) {
            .form-grid, .pro-entity-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 680px) {
            .form-grid, .pro-entity-grid {
                grid-template-columns: 1fr;
            }
        }
        /* 🌙 DARK THEME 4-TIER STEPPED TONAL HIERARCHY:
           - الخلفية العامة: #050912
           - حولها ثاني (لوحة البطاقة الرئيسية .md-card / .neon-glass-panel): #0d1526
           - حول منطقة الكتابة (بطاقة الخانة .pro-field-card / .input-group): #1c2a44 مع تدرج طفيف لكل منطقة
           - منطقة الكتابة (input / select / textarea): أسود فاحم صريح (#02040a) والكتابة بالأبيض الناصع (#ffffff) دائماً */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .md-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .neon-glass-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-form,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #supplier-form,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #item-form {
            background: #0d1526 !important;
            border: 2px solid rgba(148, 163, 184, 0.32) !important;
        }
        .input-group,
        .pro-field-card {
            background: #1c2a44 !important;
            border: 2px solid rgba(255, 255, 255, 0.38) !important;
            border-radius: 12px;
            padding: 8px 12px !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 82px !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .pro-field-card:nth-child(even),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .input-group:nth-child(even) {
            background: #172339 !important;
        }
        .pro-field-card {
            height: 82px !important;
            max-height: 82px !important;
        }
        .input-group:focus-within,
        .pro-field-card:focus-within {
            border-color: #38bdf8 !important;
            border-width: 2.4px !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.28) !important;
            background: #223454 !important;
        }
        .pro-field-card.accent-sky,
        .pro-field-card:has(#cust-name),
        .pro-field-card:has(#sup-name),
        .input-group:has(#item-name-input),
        .input-group:has(#warehouse-name),
        .input-group:has(#bank-name),
        .input-group:has(#safe-name),
        .input-group:has(#emp-name),
        .input-group:has(#costcenter-name) {
            background: linear-gradient(135deg, #173257, #132744) !important;
            border: 2.4px solid rgba(56, 189, 248, 0.82) !important;
        }
        .pro-field-card:has(#cust-opening-balance),
        .pro-field-card:has(#sup-opening-balance),
        .input-group:has(#bank-opening-balance),
        .input-group:has(#safe-opening-balance),
        .input-group:has(#item-opening-qty),
        .input-group:has(#item-opening-value) {
            background: linear-gradient(135deg, #332613, #211a10) !important;
            border: 2.2px solid rgba(245, 158, 11, 0.78) !important;
        }
        .pro-field-card:has(#cust-limit),
        .pro-field-card:has(#sup-limit),
        .input-group:has(#item-min-limit),
        .input-group:has(#item-max-limit) {
            background: linear-gradient(135deg, #381924, #241219) !important;
            border: 2.2px solid rgba(248, 113, 113, 0.75) !important;
        }
        .pro-field-card.accent-emerald {
            background: linear-gradient(135deg, #13392c, #102721) !important;
            border: 2.2px solid rgba(16, 185, 129, 0.75) !important;
        }
        .pro-field-card.accent-purple {
            background: linear-gradient(135deg, #2d1b4e, #1c1332) !important;
            border: 2.2px solid rgba(168, 85, 247, 0.78) !important;
        }
        .pro-field-card.accent-whatsapp {
            background: linear-gradient(135deg, #143a27, #10261c) !important;
            border: 2.2px solid rgba(37, 211, 102, 0.75) !important;
        }
        .pro-field-card .pro-field-label {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 6px !important;
            height: 20px !important;
            min-height: 20px !important;
            max-height: 20px !important;
            margin: 0 0 4px 0 !important;
            padding: 0 !important;
            font-size: var(--md-fs-label) !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            white-space: nowrap !important;
            overflow: hidden !important;
        }
        .md-tab-btn {
            font-family: var(--md-font-sans) !important;
            font-size: 1.02rem !important;
            font-weight: 900 !important;
            height: 46px !important;
            min-height: 46px !important;
            max-height: 46px !important;
            border-radius: 12px !important;
            border-width: 2px !important;
        }
        .pro-field-card span.pro-field-badge {
            display: none !important;
        }
        .pro-field-card button.pro-field-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            height: 20px !important;
            padding: 0 7px !important;
            border-radius: 6px !important;
            font-size: 0.72rem !important;
            font-weight: 900 !important;
            border-width: 1.8px !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }
        .input-group input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        .input-group select,
        .input-group textarea,
        .pro-field-card input:not([type="checkbox"]):not([type="hidden"]),
        .pro-field-card select,
        .pro-field-card textarea,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .input-group input:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .pro-field-card input:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .input-group select:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .pro-field-card select:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .input-group textarea:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .pro-field-card textarea:focus {
            height: var(--md-ctrl-h) !important;
            min-height: var(--md-ctrl-h) !important;
            max-height: var(--md-ctrl-h) !important;
            width: 100% !important;
            padding: 0 12px !important;
            border-radius: var(--md-radius-ctrl) !important;
            border: 2px solid rgba(255, 255, 255, 0.52) !important;
            background: #02040a !important;
            background-color: #02040a !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-size: var(--md-fs-input) !important;
            font-weight: 800 !important;
            line-height: calc(var(--md-ctrl-h) - 4px) !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            resize: none !important;
        }
        #cust-name,
        #sup-name,
        #item-name-input,
        #warehouse-name,
        #bank-name,
        #safe-name,
        #emp-name,
        #costcenter-name {
            font-weight: 900 !important;
            border-width: 2.2px !important;
            letter-spacing: 0.15px !important;
        }
        table.pro-table th,
        table.pro-table td {
            font-weight: 800 !important;
            border-bottom-width: 1.8px !important;
        }
        table.pro-table td:nth-child(2) {
            font-weight: 900 !important;
        }
        svg path, svg circle, svg rect, svg line, svg polyline, svg polygon {
            stroke-width: 2.5px !important;
        }
        .input-group textarea {
            height: auto !important;
            min-height: 64px !important;
            max-height: 120px !important;
            padding: 8px 12px !important;
            line-height: 1.4 !important;
        }
        .pro-field-card textarea {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            line-height: 1.35 !important;
        }
        .pro-field-card input.pro-num-input {
            font-size: var(--md-fs-num) !important;
            font-weight: 900 !important;
            font-family: var(--md-font-num) !important;
            text-align: center !important;
            direction: ltr !important;
            letter-spacing: 0.3px !important;
        }
        /* ☀️ LIGHT THEME 4-TIER STEPPED BEIGE & WHITE HIERARCHY:
           - الخلفية العامة: بيج رملي دافئ أغمق قليلاً (#d6c7aa)
           - حولها ثاني (لوحة البطاقة الرئيسية .md-card / .neon-glass-panel): بيج متوسط دافئ (#e4d7be)
           - حول منطقة الكتابة (بطاقة الخانة .pro-field-card / .input-group): بيج ناعم فاتح (#f2e8d5) مع تمييز لوني طفيف لمنطقة الاسم ومنطقة الرصيد الافتتاحي وحد الائتمان
           - منطقة الكتابة (input / select / textarea): أبيض ناصع صريح (#ffffff) والكتابة بالأسود الداكن الصريح (#000000) */
        :root.light-theme .input-group,
        :root.light-theme .pro-field-card,
        html[data-theme="warm_cream"] .input-group,
        html[data-theme="warm_cream"] .pro-field-card,
        html[data-theme="ultra_white"] .input-group,
        html[data-theme="ultra_white"] .pro-field-card,
        html[data-theme="exec_gray"] .input-group,
        html[data-theme="exec_gray"] .pro-field-card {
            background: #f2e8d5 !important;
            border: 2px solid #473d31 !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .pro-field-card:nth-child(even),
        :root.light-theme .input-group:nth-child(even),
        html[data-theme="warm_cream"] .pro-field-card:nth-child(even),
        html[data-theme="warm_cream"] .input-group:nth-child(even),
        html[data-theme="ultra_white"] .pro-field-card:nth-child(even),
        html[data-theme="ultra_white"] .input-group:nth-child(even),
        html[data-theme="exec_gray"] .pro-field-card:nth-child(even),
        html[data-theme="exec_gray"] .input-group:nth-child(even) {
            background: #ecdfc8 !important;
        }
        :root.light-theme .pro-field-card.accent-sky,
        :root.light-theme .pro-field-card:has(#cust-name),
        :root.light-theme .pro-field-card:has(#sup-name),
        :root.light-theme .input-group:has(#item-name-input),
        html[data-theme="warm_cream"] .pro-field-card.accent-sky,
        html[data-theme="warm_cream"] .pro-field-card:has(#cust-name),
        html[data-theme="warm_cream"] .pro-field-card:has(#sup-name),
        html[data-theme="ultra_white"] .pro-field-card.accent-sky,
        html[data-theme="exec_gray"] .pro-field-card.accent-sky {
            background: #e5eff9 !important;
            border: 2.4px solid #0284c7 !important;
        }
        :root.light-theme .pro-field-card:has(#cust-opening-balance),
        :root.light-theme .pro-field-card:has(#sup-opening-balance),
        :root.light-theme .input-group:has(#bank-opening-balance),
        :root.light-theme .input-group:has(#safe-opening-balance),
        :root.light-theme .input-group:has(#item-opening-qty),
        :root.light-theme .input-group:has(#item-opening-value),
        html[data-theme="warm_cream"] .pro-field-card:has(#cust-opening-balance),
        html[data-theme="warm_cream"] .pro-field-card:has(#sup-opening-balance),
        html[data-theme="ultra_white"] .pro-field-card:has(#cust-opening-balance),
        html[data-theme="ultra_white"] .pro-field-card:has(#sup-opening-balance),
        html[data-theme="exec_gray"] .pro-field-card:has(#cust-opening-balance),
        html[data-theme="exec_gray"] .pro-field-card:has(#sup-opening-balance) {
            background: #f6e3ba !important;
            border: 2.2px solid #b45309 !important;
        }
        :root.light-theme .pro-field-card:has(#cust-limit),
        :root.light-theme .pro-field-card:has(#sup-limit),
        html[data-theme="warm_cream"] .pro-field-card:has(#cust-limit),
        html[data-theme="warm_cream"] .pro-field-card:has(#sup-limit),
        html[data-theme="ultra_white"] .pro-field-card:has(#cust-limit),
        html[data-theme="ultra_white"] .pro-field-card:has(#sup-limit),
        html[data-theme="exec_gray"] .pro-field-card:has(#cust-limit),
        html[data-theme="exec_gray"] .pro-field-card:has(#sup-limit) {
            background: #f7deda !important;
            border: 2.2px solid #be123c !important;
        }
        :root.light-theme .pro-field-card.accent-purple,
        html[data-theme="warm_cream"] .pro-field-card.accent-purple,
        html[data-theme="ultra_white"] .pro-field-card.accent-purple,
        html[data-theme="exec_gray"] .pro-field-card.accent-purple {
            background: #ede2f8 !important;
            border: 2.2px solid #7e22ce !important;
        }
        :root.light-theme .pro-field-card.accent-whatsapp,
        html[data-theme="warm_cream"] .pro-field-card.accent-whatsapp,
        html[data-theme="ultra_white"] .pro-field-card.accent-whatsapp,
        html[data-theme="exec_gray"] .pro-field-card.accent-whatsapp {
            background: #dff2e6 !important;
            border: 2.2px solid #15803d !important;
        }
        :root.light-theme .input-group label,
        :root.light-theme .pro-field-card .pro-field-label,
        html[data-theme="warm_cream"] .input-group label,
        html[data-theme="warm_cream"] .pro-field-card .pro-field-label,
        html[data-theme="ultra_white"] .input-group label,
        html[data-theme="ultra_white"] .pro-field-card .pro-field-label,
        html[data-theme="exec_gray"] .input-group label,
        html[data-theme="exec_gray"] .pro-field-card .pro-field-label {
            color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme .input-group input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        :root.light-theme .input-group select,
        :root.light-theme .input-group textarea,
        :root.light-theme .pro-field-card input:not([type="checkbox"]):not([type="hidden"]),
        :root.light-theme .pro-field-card select,
        :root.light-theme .pro-field-card textarea,
        html[data-theme="warm_cream"] .input-group input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        html[data-theme="warm_cream"] .input-group select,
        html[data-theme="warm_cream"] .input-group textarea,
        html[data-theme="warm_cream"] .pro-field-card input:not([type="checkbox"]):not([type="hidden"]),
        html[data-theme="warm_cream"] .pro-field-card select,
        html[data-theme="warm_cream"] .pro-field-card textarea,
        html[data-theme="ultra_white"] .input-group input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        html[data-theme="ultra_white"] .input-group select,
        html[data-theme="ultra_white"] .input-group textarea,
        html[data-theme="ultra_white"] .pro-field-card input:not([type="checkbox"]):not([type="hidden"]),
        html[data-theme="ultra_white"] .pro-field-card select,
        html[data-theme="ultra_white"] .pro-field-card textarea,
        html[data-theme="exec_gray"] .input-group input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        html[data-theme="exec_gray"] .input-group select,
        html[data-theme="exec_gray"] .input-group textarea,
        html[data-theme="exec_gray"] .pro-field-card input:not([type="checkbox"]):not([type="hidden"]),
        html[data-theme="exec_gray"] .pro-field-card select,
        html[data-theme="exec_gray"] .pro-field-card textarea {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            border: 2px solid #000000 !important;
        }
    </style>

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
        <?php $header_theme_class = 'header-theme-masterdata'; include 'includes/header.php'; ?>

        <aside class="md-tabs-sidebar">
            <button class="md-tab-btn tab-warehouses active" onclick="openTab(event, 'tab-warehouses')"><span class="md-tab-label">المخازن</span></button>
            <button class="md-tab-btn tab-customers" onclick="openTab(event, 'tab-customers')"><span class="md-tab-label">العملاء</span></button>
            <button class="md-tab-btn tab-suppliers" onclick="openTab(event, 'tab-suppliers')"><span class="md-tab-label">الموردين</span></button>
            <button class="md-tab-btn tab-banks" onclick="openTab(event, 'tab-banks')"><span class="md-tab-label">البنوك</span></button>
            <button class="md-tab-btn tab-safes" onclick="openTab(event, 'tab-safes')"><span class="md-tab-label">الخزن</span></button>
            <button class="md-tab-btn tab-items" onclick="openTab(event, 'tab-items')"><span class="md-tab-label">الأصناف</span></button>
            <button class="md-tab-btn tab-employees" onclick="openTab(event, 'tab-employees')"><span class="md-tab-label">الموظفين</span></button>
            <button class="md-tab-btn tab-costcenters" onclick="openTab(event, 'tab-costcenters')"><span class="md-tab-label">مركز التكلفة</span></button>
            <button class="md-tab-btn tab-exit" onclick="goBackOrExit()"><span class="md-tab-label">خروج</span></button>
        </aside>
        <script>
        (function initMasterDataProportionalTabLabels() {
            function fitMasterDataTabButtons() {
                var sidebar = document.querySelector('.md-tabs-sidebar');
                if (!sidebar) return;
                var buttons = sidebar.querySelectorAll('.md-tab-btn');
                if (!buttons.length) return;

                for (var i = 0; i < buttons.length; i++) {
                    var btn = buttons[i];
                    var label = btn.querySelector('.md-tab-label');
                    if (!label) continue;

                    var btnW = btn.clientWidth;
                    var btnH = btn.clientHeight;
                    if (btnW <= 0 || btnH <= 0) continue;

                    label.style.transform = 'none';
                    label.style.fontSize = '20px';
                    var textWAt20 = label.scrollWidth || label.offsetWidth || 1;

                    var availW = Math.max(14, btnW * 0.85);
                    var availH = Math.max(12, btnH * 0.52);

                    var fontByW = (20 * availW) / textWAt20;
                    var fontByH = availH;
                    var targetFont = Math.max(7, Math.min(fontByW, fontByH, 32));
                    label.style.fontSize = targetFont.toFixed(2) + 'px';

                    var finalTextW = label.scrollWidth || label.offsetWidth || 0;
                    var maxAllowedW = btnW * 0.90;
                    if (finalTextW > maxAllowedW && finalTextW > 0) {
                        var ratio = maxAllowedW / finalTextW;
                        label.style.transform = 'scale(' + ratio.toFixed(3) + ')';
                    }
                }
            }

            fitMasterDataTabButtons();
            window.addEventListener('resize', fitMasterDataTabButtons);
            window.addEventListener('load', fitMasterDataTabButtons);
            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', fitMasterDataTabButtons);
            }
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(fitMasterDataTabButtons).catch(function(){});
            }
            if (typeof ResizeObserver !== 'undefined') {
                var ro = new ResizeObserver(function() {
                    requestAnimationFrame(fitMasterDataTabButtons);
                });
                var sb = document.querySelector('.md-tabs-sidebar');
                if (sb) {
                    ro.observe(sb);
                    sb.querySelectorAll('.md-tab-btn').forEach(function(b) { ro.observe(b); });
                }
            }
        })();
        </script>

        <main class="md-content-area">
            
            <!-- شريط الإجراءات المباشرة (Action-Only Bar) -->
            <div style="display:flex; justify-content:flex-end; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                <input type="file" id="md-excel-import-input" accept=".csv,.txt" style="display:none;" onchange="handleActiveTabExcelImport(event)">
                <button type="button" onclick="openOpeningEquilibriumModal()" style="height:34px; background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>⚖️</span> <span>القيد الافتتاحي</span>
                </button>
                <button type="button" onclick="openBulkPriceUpdaterModal()" style="height:34px; background:rgba(245,158,11,0.16); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>📈</span> <span>تعديل الأسعار</span>
                </button>
                <button type="button" onclick="openSmartClipboardPasteModal()" style="height:34px; background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>📋</span> <span>لصق Excel</span>
                </button>
                <button type="button" onclick="exportActiveTabToExcel()" style="height:34px; background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>📤</span> <span>تصدير Excel</span>
                </button>
                <button type="button" onclick="triggerActiveTabExcelImport()" style="height:34px; background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>📥</span> <span>استيراد Excel</span>
                </button>
                <button type="button" id="md-btn-rollback-import" onclick="window.rollbackLastMasterImport()" style="display:none; height:34px; background:rgba(239,68,68,0.18); color:#fca5a5; border:1px solid rgba(239,68,68,0.45); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; align-items:center; gap:6px;">
                    <span>↩️</span> <span>تراجع استيراد</span>
                </button>
                <button type="button" onclick="openActiveTabPrintAndShareModal()" style="height:34px; background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.45); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>🖨️</span> <span>طباعة / PDF / واتساب</span>
                </button>
                <button type="button" onclick="openMasterAuditLogModal()" style="height:34px; background:rgba(168,85,247,0.18); color:#e9d5ff; border:1px solid rgba(168,85,247,0.45); padding:0 13px; border-radius:9px; font-size:0.82rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>🛡️</span> <span>سجل الرقابة</span>
                    <span id="md-audit-count-badge" style="background:rgba(168,85,247,0.4); color:#fff; padding:1px 7px; border-radius:10px; font-size:0.72rem; font-family:monospace;">0</span>
                </button>
            </div>

            <div id="tab-warehouses" class="md-tab-content active">
                <div class="md-card">
                    <div class="md-card-header">
                        <h3 class="md-card-title">🏢 المخازن</h3>
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <div style="position:relative; min-width:240px;">
                                <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none;">🔍</span>
                                <input type="text" id="warehouse-search-input" oninput="renderWarehouses(this.value)" placeholder="بحث في المخازن..." style="padding-right:36px !important;">
                            </div>
                            <button class="btn-add" onclick="openWarehouseForm()">+ إضافة مخزن <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    
                    <div id="warehouse-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0; color:#38bdf8;">🏢 بيانات المخزن</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-warehouse" onclick="cancelEditWarehouse()">✖ إلغاء</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="warehouse-id">
                            <div class="input-group"><label>اسم المخزن *</label><input type="text" id="warehouse-name" placeholder="مثال: مخزن مقاييس الرئيسي"></div>
                            <div class="input-group"><label>النوع</label><select id="warehouse-type"><option value="مستودع رئيسي">مستودع رئيسي</option><option value="معرض (نقطة بيع)">معرض (نقطة بيع)</option><option value="مخزن فرعي">مخزن فرعي</option><option value="مخزن بضاعة تالفة">مخزن بضاعة تالفة</option></select></div>
                            <div class="input-group"><label style="justify-content:space-between;"><span>أمين المخزن</span><button type="button" class="btn-text" style="height:20px !important; min-height:20px !important; padding:0 6px !important; font-size:0.72rem !important;" onclick="quickAddKeeperModal()">➕</button></label><input type="text" id="warehouse-keeper" list="wh-keepers-datalist" placeholder="اسم أمين المخزن"><datalist id="wh-keepers-datalist"></datalist></div>
                            <div class="input-group"><label>الهاتف</label><input type="text" id="warehouse-phone" placeholder="0912345678" style="direction:ltr; text-align:center;"></div>
                            <div class="input-group"><label>العنوان</label><input type="text" id="warehouse-location" placeholder="مثال: المنطقة الصناعية"></div>
                            <div class="input-group"><label>الحالة</label><select id="warehouse-status"><option value="active">🟢 نشط</option><option value="inventory_lock">🟠 جرد</option><option value="inactive">🔴 موقوف</option></select></div>
                            <div class="input-group"><label>السرية</label><select id="warehouse-security"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:15px;">
                            <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveWarehouse()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-action" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.4); padding:8px 20px; border-radius:8px; cursor:pointer; font-weight:bold;" onclick="cancelEditWarehouse()">✖ إغلاق <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>اسم المخزن</th><th>النوع</th><th>أمين المخزن</th><th>الهاتف</th><th>العنوان</th><th>الحالة</th><th>السرية</th><th>الإجراء</th></tr></thead>
                            <tbody id="warehouses-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-customers" class="md-tab-content">
                <div class="md-card">
                    <!-- أزرار التحكم المباشر لواتساب العملاء -->
                    <div id="admin-wa-policy-panel-cust" style="margin-bottom: 14px; border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; background: rgba(15, 23, 42, 0.65); padding: 10px 14px;">
                        <div style="display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <button type="button" onclick="bulkSetEntityStatementMode('customer', 'detailed')" style="height: 32px; background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">📊 كشف للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('customer', 'balance_only')" style="height: 32px; background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">💰 رصيد للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('customer', 'disabled')" style="height: 32px; background: rgba(239, 68, 68, 0.14); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔒 إيقاف الكل</button>
                            <button type="button" onclick="bulkGenerateEntityPins('customer')" style="height: 32px; background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔐 توليد باسورد</button>
                            <button type="button" onclick="toggleAdminWaPolicyDrawer()" style="height: 32px; background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.45); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">⚙️ السياسة</button>
                        </div>

                        <div class="admin-wa-policy-drawer" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px dashed rgba(168, 85, 247, 0.3);">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; align-items: end;">
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #e9d5ff; font-size: 0.8rem; font-weight: 800;">صلاحية الكشف</label>
                                    <select class="wa-policy-master-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(168,85,247,0.45);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="force_detailed_all">كشف مفصل للكل</option>
                                        <option value="force_balance_only_all">الرصيد فقط للكل</option>
                                        <option value="force_disabled_all">إيقاف الكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #10b981; font-size: 0.8rem; font-weight: 800;">اعتماد الإشعارات</label>
                                    <select class="wa-policy-receipt-approval-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.5);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="auto_post_all">اعتماد فوري للكل</option>
                                        <option value="review_first_all">مراجعة بالترحيل للكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #fbbf24; font-size: 0.8rem; font-weight: 800;">البنك الافتراضي</label>
                                    <select class="wa-policy-default-bank-id" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(245,158,11,0.5);">
                                        <option value="">تلقائي</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #38bdf8; font-size: 0.8rem; font-weight: 800;">التحقق الأمني</label>
                                    <select class="wa-policy-phone-verification" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(56,189,248,0.4);">
                                        <option value="phone_or_pin">الرقم المعتمد أو الباسورد</option>
                                        <option value="any_phone_with_code">الباسورد لأي رقم</option>
                                        <option value="registered_if_set">مطابقة الرقم أو الباسورد</option>
                                        <option value="strict_registered_only">الرقم المسجل فقط</option>
                                        <option value="pin_always">اشتراط الباسورد دائماً</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #34d399; font-size: 0.8rem; font-weight: 800;">نطاق الكشف</label>
                                    <select class="wa-policy-statement-scope" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.4);">
                                        <option value="all">كامل الحركات</option>
                                        <option value="last_30_days">آخر 30 يوماً</option>
                                        <option value="last_20">آخر 20 حركة</option>
                                        <option value="last_50">آخر 50 حركة</option>
                                        <option value="current_month">الشهر الحالي</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap; margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06);">
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #e2e8f0;">
                                    <input type="checkbox" class="wa-policy-include-items" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #10b981;">
                                    <span>إظهار الأصناف</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #fbbf24;">
                                    <input type="checkbox" class="wa-policy-notify-admin" onchange="syncAdminWaPolicyFromUI(this)" style="width: 16px; height: 16px; accent-color: #f59e0b;">
                                    <span>إشعار المدير</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #38bdf8;">
                                    <input type="checkbox" class="wa-policy-antispam" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #38bdf8;">
                                    <span>منع التكرار</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="customer-form" class="md-card" style="display: block; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.1); background: rgba(8, 12, 24, 0.45); padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                            <h4 style="margin: 0; font-size: 1.08rem; color: #38bdf8; display: flex; align-items: center; gap: 8px; font-weight: 900;">
                                <span>👥</span> <span>بيانات العميل</span>
                            </h4>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <input type="hidden" id="cust-id" value="">
                                <input type="hidden" id="cust-allow-statement" value="1">
                                <input type="hidden" id="cust-statement-mode" value="detailed">
                                <input type="hidden" id="cust-receipt-approval-mode" value="auto_post">
                                <input type="hidden" id="cust-receipt-code-num" value="">
                                <input type="hidden" id="cust-receipt-code-word" value="">
                                <input type="hidden" id="cust-phone" value="">
                                <input type="hidden" id="cust-account-no" value="">
                                <input type="hidden" id="cust-opening-debit" value="0">
                                <input type="hidden" id="cust-opening-credit" value="0">
                                <div style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="cust-mode-btn-disabled" onclick="setFormStatementMode('cust', 'disabled', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">🔒 موقوف</button>
                                    <button type="button" id="cust-mode-btn-balance_only" onclick="setFormStatementMode('cust', 'balance_only', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">💰 الرصيد</button>
                                    <button type="button" id="cust-mode-btn-detailed" onclick="setFormStatementMode('cust', 'detailed', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">📊 كشف</button>
                                </div>
                                <div style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="cust-rcpt-btn-auto_post" onclick="setFormReceiptApprovalMode('cust', 'auto_post', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">⚡ اعتماد فوري</button>
                                    <button type="button" id="cust-rcpt-btn-review_first" onclick="setFormReceiptApprovalMode('cust', 'review_first', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">👁️ مراجعة</button>
                                </div>
                                <div style="display:inline-flex; align-items:center; gap:4px;">
                                    <select id="cust-default-bank-id" class="entity-default-bank-select" style="height: 36px; background: rgba(15, 23, 42, 0.9); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 10px; padding: 0 10px; font-size: 0.78rem; font-weight: 800; max-width: 175px;">
                                        <option value="">🏦 البنك الافتراضي</option>
                                    </select>
                                    <button type="button" class="btn-text" onclick="quickAddBankModal('cust-default-bank-id')" style="height:36px !important; min-height:36px !important; padding:0 8px !important;">➕</button>
                                </div>
                                <button type="button" onclick="openEntityQrCardModal('cust')" style="height: 36px; background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.45); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🪪 بطاقة QR</button>
                                <button type="button" onclick="copyCurrentFormWhatsAppCard('cust')" style="height: 36px; background: rgba(168, 85, 247, 0.18); color: #e9d5ff; border: 1px solid rgba(168, 85, 247, 0.45); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">📋 نسخ البطاقة</button>
                                <button type="button" onclick="clearCustomerForm()" class="btn-action" style="height: 36px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🔄 جديد <span class="kbd-hint">F2</span></button>
                            </div>
                        </div>

                        <div class="pro-entity-grid">
                            <div class="pro-field-card accent-sky" style="grid-column: span 2;">
                                <label class="pro-field-label" style="color: #38bdf8 !important;">
                                    <span>👤 اسم العميل *</span>
                                </label>
                                <input type="text" id="cust-name" placeholder="اسم العميل أو الشركة">
                            </div>

                            <div class="pro-field-card accent-purple">
                                <label class="pro-field-label" style="color: #d8b4fe !important;">
                                    <span>🔐 الرقم السري</span>
                                    <button type="button" onclick="generateRandomFormPin('cust')" class="pro-field-badge" style="background:rgba(168,85,247,0.32); color:#fff; border:1px solid rgba(168,85,247,0.6); cursor:pointer;">🎲 توليد</button>
                                </label>
                                <input type="text" id="cust-statement-pin" class="pro-num-input" maxlength="8" oninput="updateCombinedPinPreview('cust')" placeholder="532" style="color: #e9d5ff !important;">
                            </div>

                            <div class="pro-field-card accent-whatsapp">
                                <label class="pro-field-label" style="color: #4ade80 !important;">
                                    <span>💬 رقم الواتساب</span>
                                </label>
                                <input type="text" id="cust-whatsapp" class="pro-num-input" oninput="document.getElementById('cust-phone').value = this.value" placeholder="00249123000000">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>💰 الرصيد الافتتاحي</span>
                                </label>
                                <input type="number" id="cust-opening-balance" class="pro-num-input" step="any" placeholder="0.00">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span style="color: #fca5a5;">🛑 حد الائتمان</span>
                                </label>
                                <input type="number" id="cust-limit" class="pro-num-input" placeholder="0.00">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>💱 العملة</span>
                                </label>
                                <select id="cust-currency" class="system-currency-select" onchange="updateCustExchangeRate()"></select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🟢 الحالة</span>
                                </label>
                                <select id="cust-status">
                                    <option value="active">🟢 نشط</option>
                                    <option value="vip">🌟 VIP</option>
                                    <option value="blacklisted">🔴 محظور</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>👔 المسؤول</span>
                                </label>
                                <input type="text" id="cust-contact" placeholder="اسم المسؤول">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>📍 العنوان</span>
                                </label>
                                <input type="text" id="cust-address" placeholder="المدينة / الموقع">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🧾 الرقم الضريبي</span>
                                </label>
                                <input type="text" id="cust-tax" placeholder="الرقم الضريبي">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🛡️ السرية</span>
                                </label>
                                <select id="cust-security">
                                    <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🏷️ شريحة التسعير</span>
                                </label>
                                <select id="cust-price-tier">
                                    <option value="cash">💵 نقدي</option>
                                    <option value="credit">📦 آجل</option>
                                    <option value="min">💎 أقل سعر</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>⏳ فترة السداد (يوم)</span>
                                </label>
                                <input type="number" id="cust-payment-terms" class="pro-num-input" min="0" placeholder="15">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>✉️ البريد الإلكتروني</span>
                                </label>
                                <input type="email" id="cust-email" placeholder="example@email.com" style="direction:ltr; text-align:center;">
                            </div>

                            <div class="pro-field-card accent-sky" id="cust-ex-rate-group" style="display: none;">
                                <label class="pro-field-label" id="cust-ex-rate-label" style="color:#38bdf8 !important;">
                                    <span>💵 سعر الصرف</span>
                                </label>
                                <input type="number" id="cust-ex-rate" class="pro-num-input" step="any" value="1" placeholder="1">
                            </div>

                            <div class="pro-field-card" id="cust-notes-card" style="grid-column: 1 / -1;">
                                <label class="pro-field-label">
                                    <span>📝 ملاحظات</span>
                                </label>
                                <textarea id="cust-notes" placeholder="ملاحظات..."></textarea>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; margin-top: 6px;">
                            <div style="padding: 10px 14px; border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; background: rgba(15, 23, 42, 0.4);">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                    <h4 style="margin: 0; color: #38bdf8; display:flex; align-items:center; gap:6px; font-size: 0.9rem; font-weight: 800;">
                                        <span>📱</span> <span>أرقام المناديب والتابعين</span>
                                    </h4>
                                    <button type="button" onclick="addCustomerSubPhone()" style="height: 30px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); padding: 0 12px; border-radius: 8px; cursor: pointer; font-size: 0.78rem; font-weight: 800;">+ إضافة رقم</button>
                                </div>
                                <div id="cust-sub-phones-container" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
                            </div>

                            <div style="padding: 10px 14px; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 10px; background: rgba(15, 23, 42, 0.4);">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                    <h4 style="margin: 0; color: #34d399; display:flex; align-items:center; gap:6px; font-size: 0.9rem; font-weight: 800;">
                                        <span>🏦</span> <span>الحسابات البنكية</span>
                                    </h4>
                                    <button type="button" onclick="addCustomerBankAccount()" style="height: 30px; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); padding: 0 12px; border-radius: 8px; cursor: pointer; font-size: 0.78rem; font-weight: 800;">+ إضافة حساب</button>
                                </div>
                                <div id="cust-bank-accounts-container" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
                            </div>
                        </div>

                        <div style="margin-top: 14px; display: flex; justify-content: flex-end;">
                            <button type="button" class="btn-add" style="height: 42px; background: linear-gradient(135deg, #10b981, #059669); padding: 0 28px; font-weight: 900; font-size: 0.95rem; border-radius: 10px;" onclick="saveCustomer()">💾 حفظ <span class="kbd-hint">F10</span></button>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div class="input-group" style="width: 100%; max-width: 480px; position: relative; margin-bottom: 0;">
                            <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; pointer-events: none;">🔍</span>
                            <input type="text" id="customer-search-input" oninput="filterCustomers()" placeholder="بحث بالاسم، الرقم السري، أو الهاتف..." style="padding-right: 42px; border-radius: 10px; border: 1px solid rgba(56,189,248,0.3); background: rgba(0,0,0,0.3); font-size: 0.95rem; color: #fff; width: 100%;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="button" id="btn-export-customers-csv" onclick="exportMasterEntityCsv('customer')" style="height: 36px; background: rgba(16, 185, 129, 0.14); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📤 تصدير Excel</button>
                            <button type="button" id="btn-import-customers-csv" onclick="triggerMasterEntityCsvImport('customer')" style="height: 36px; background: rgba(56, 189, 248, 0.14); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📥 استيراد Excel</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th style="width: 115px; text-align: center;">🔐 الرقم السري</th><th style="width: 25%; min-width: 180px; text-align: right;">العميل</th><th style="width: 130px; text-align: center;">الواتساب</th><th style="width: 175px; text-align: center;">كشف الحساب</th><th style="width: 85px; text-align: center;">الحالة</th><th style="width: 125px; text-align: center;">الرصيد</th><th style="width: 110px; text-align: center;">الائتمان</th><th style="width: 95px; text-align: center;">المسؤول</th><th style="width: 55px; text-align: center;">السرية</th><th style="width: 110px; text-align: center;">الإجراء</th></tr></thead>
                            <tbody id="customers-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Suppliers Tab -->
            <div id="tab-suppliers" class="md-tab-content">
                <div class="md-card">
                    <!-- أزرار التحكم المباشر لواتساب الموردين -->
                    <div id="admin-wa-policy-panel-sup" style="margin-bottom: 14px; border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; background: rgba(15, 23, 42, 0.65); padding: 10px 14px;">
                        <div style="display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <button type="button" onclick="bulkSetEntityStatementMode('supplier', 'detailed')" style="height: 32px; background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">📊 كشف للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('supplier', 'balance_only')" style="height: 32px; background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">💰 رصيد للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('supplier', 'disabled')" style="height: 32px; background: rgba(239, 68, 68, 0.14); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔒 إيقاف الكل</button>
                            <button type="button" onclick="bulkGenerateEntityPins('supplier')" style="height: 32px; background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔐 توليد باسورد</button>
                            <button type="button" onclick="toggleAdminWaPolicyDrawer()" style="height: 32px; background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.45); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">⚙️ السياسة</button>
                        </div>

                        <div class="admin-wa-policy-drawer" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px dashed rgba(168, 85, 247, 0.3);">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; align-items: end;">
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #e9d5ff; font-size: 0.8rem; font-weight: 800;">صلاحية الكشف</label>
                                    <select class="wa-policy-master-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(168,85,247,0.45);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="force_detailed_all">كشف مفصل للكل</option>
                                        <option value="force_balance_only_all">الرصيد فقط للكل</option>
                                        <option value="force_disabled_all">إيقاف الكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #10b981; font-size: 0.8rem; font-weight: 800;">اعتماد الإشعارات</label>
                                    <select class="wa-policy-receipt-approval-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.5);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="auto_post_all">اعتماد فوري للكل</option>
                                        <option value="review_first_all">مراجعة بالترحيل للكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #fbbf24; font-size: 0.8rem; font-weight: 800;">البنك الافتراضي</label>
                                    <select class="wa-policy-default-bank-id" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(245,158,11,0.5);">
                                        <option value="">تلقائي</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #38bdf8; font-size: 0.8rem; font-weight: 800;">التحقق الأمني</label>
                                    <select class="wa-policy-phone-verification" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(56,189,248,0.4);">
                                        <option value="phone_or_pin">الرقم المعتمد أو الباسورد</option>
                                        <option value="any_phone_with_code">الباسورد لأي رقم</option>
                                        <option value="registered_if_set">مطابقة الرقم أو الباسورد</option>
                                        <option value="strict_registered_only">الرقم المسجل فقط</option>
                                        <option value="pin_always">اشتراط الباسورد دائماً</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #34d399; font-size: 0.8rem; font-weight: 800;">نطاق الكشف</label>
                                    <select class="wa-policy-statement-scope" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.4);">
                                        <option value="all">كامل الحركات</option>
                                        <option value="last_30_days">آخر 30 يوماً</option>
                                        <option value="last_20">آخر 20 حركة</option>
                                        <option value="last_50">آخر 50 حركة</option>
                                        <option value="current_month">الشهر الحالي</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap; margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06);">
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #e2e8f0;">
                                    <input type="checkbox" class="wa-policy-include-items" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #10b981;">
                                    <span>إظهار الأصناف</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #fbbf24;">
                                    <input type="checkbox" class="wa-policy-notify-admin" onchange="syncAdminWaPolicyFromUI(this)" style="width: 16px; height: 16px; accent-color: #f59e0b;">
                                    <span>إشعار المدير</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #38bdf8;">
                                    <input type="checkbox" class="wa-policy-antispam" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #38bdf8;">
                                    <span>منع التكرار</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="supplier-form" class="md-card" style="display: block; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.1); background: rgba(8, 12, 24, 0.45); padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                            <h4 style="margin: 0; font-size: 1.08rem; color: #c084fc; display: flex; align-items: center; gap: 8px; font-weight: 900;">
                                <span>🏭</span> <span>بيانات المورد</span>
                            </h4>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <input type="hidden" id="sup-id" value="">
                                <input type="hidden" id="sup-allow-statement" value="1">
                                <input type="hidden" id="sup-statement-mode" value="detailed">
                                <input type="hidden" id="sup-receipt-approval-mode" value="auto_post">
                                <input type="hidden" id="sup-receipt-code-num" value="">
                                <input type="hidden" id="sup-receipt-code-word" value="">
                                <input type="hidden" id="sup-phone" value="">
                                <input type="hidden" id="sup-account-no" value="">
                                <input type="hidden" id="sup-opening-debit" value="0">
                                <input type="hidden" id="sup-opening-credit" value="0">
                                <div style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="sup-mode-btn-disabled" onclick="setFormStatementMode('sup', 'disabled', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">🔒 موقوف</button>
                                    <button type="button" id="sup-mode-btn-balance_only" onclick="setFormStatementMode('sup', 'balance_only', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">💰 الرصيد</button>
                                    <button type="button" id="sup-mode-btn-detailed" onclick="setFormStatementMode('sup', 'detailed', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">📊 كشف</button>
                                </div>
                                <div style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="sup-rcpt-btn-auto_post" onclick="setFormReceiptApprovalMode('sup', 'auto_post', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">⚡ اعتماد فوري</button>
                                    <button type="button" id="sup-rcpt-btn-review_first" onclick="setFormReceiptApprovalMode('sup', 'review_first', true)" style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1px solid transparent; font-size: 0.74rem; font-weight: 800; cursor: pointer;">👁️ مراجعة</button>
                                </div>
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <select id="sup-default-bank-id" class="entity-default-bank-select" style="height: 36px; background: rgba(15, 23, 42, 0.9); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 10px; padding: 0 10px; font-size: 0.78rem; font-weight: 800; max-width: 175px;">
                                        <option value="">🏦 البنك الافتراضي</option>
                                    </select>
                                    <button type="button" onclick="quickAddBankModal('sup-default-bank-id')" style="height: 36px; padding: 0 10px; border-radius: 9px; background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-weight: 900; cursor: pointer;">➕</button>
                                </div>
                                <button type="button" onclick="openEntityQrCardModal('sup')" style="height: 36px; background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.45); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🪪 بطاقة QR</button>
                                <button type="button" onclick="copyCurrentFormWhatsAppCard('sup')" style="height: 36px; background: rgba(168, 85, 247, 0.18); color: #e9d5ff; border: 1px solid rgba(168, 85, 247, 0.45); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">📋 نسخ البطاقة</button>
                                <button type="button" onclick="clearSupplierForm()" class="btn-action" style="height: 36px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🔄 جديد <span class="kbd-hint">F2</span></button>
                            </div>
                        </div>

                        <div class="pro-entity-grid">
                            <div class="pro-field-card accent-sky" style="grid-column: span 2;">
                                <label class="pro-field-label" style="color: #38bdf8 !important;">
                                    <span>🏭 اسم المورد *</span>
                                </label>
                                <input type="text" id="sup-name" placeholder="اسم المورد أو الشركة">
                            </div>

                            <div class="pro-field-card accent-purple">
                                <label class="pro-field-label" style="color: #d8b4fe !important;">
                                    <span>🔐 الرقم السري</span>
                                    <button type="button" onclick="generateRandomFormPin('sup')" class="pro-field-badge" style="background:rgba(168,85,247,0.32); color:#fff; border:1px solid rgba(168,85,247,0.6); cursor:pointer;">🎲 توليد</button>
                                </label>
                                <input type="text" id="sup-statement-pin" class="pro-num-input" maxlength="8" oninput="updateCombinedPinPreview('sup')" placeholder="532" style="color: #e9d5ff !important;">
                            </div>

                            <div class="pro-field-card accent-whatsapp">
                                <label class="pro-field-label" style="color: #4ade80 !important;">
                                    <span>💬 رقم الواتساب</span>
                                </label>
                                <input type="text" id="sup-whatsapp" class="pro-num-input" oninput="document.getElementById('sup-phone').value = this.value" placeholder="00249123000000">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>💰 الرصيد الافتتاحي</span>
                                </label>
                                <input type="number" id="sup-opening-balance" class="pro-num-input" step="any" placeholder="0.00">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span style="color: #fca5a5;">🛑 حد الائتمان</span>
                                </label>
                                <input type="number" id="sup-limit" class="pro-num-input" placeholder="0.00">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>💱 العملة</span>
                                </label>
                                <select id="sup-currency" class="system-currency-select" onchange="updateSupExchangeRate()"></select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🟢 الحالة</span>
                                </label>
                                <select id="sup-status">
                                    <option value="active">🟢 نشط</option>
                                    <option value="blacklisted">🔴 محظور</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>📍 العنوان</span>
                                </label>
                                <input type="text" id="sup-address" placeholder="المدينة / الموقع">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>✉️ البريد الإلكتروني</span>
                                </label>
                                <input type="email" id="sup-email" placeholder="example@email.com" style="direction:ltr; text-align:center;">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🧾 الرقم الضريبي</span>
                                </label>
                                <input type="text" id="sup-tax" placeholder="الرقم الضريبي">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🛡️ السرية</span>
                                </label>
                                <select id="sup-security">
                                    <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>⏳ فترة السداد (يوم)</span>
                                </label>
                                <input type="number" id="sup-payment-terms" class="pro-num-input" min="0" placeholder="30">
                            </div>

                            <div class="pro-field-card accent-sky" id="sup-ex-rate-group" style="display: none;">
                                <label class="pro-field-label" id="sup-ex-rate-label" style="color:#38bdf8 !important;">
                                    <span>💵 سعر الصرف</span>
                                </label>
                                <input type="number" id="sup-ex-rate" class="pro-num-input" step="any" value="1" placeholder="1">
                            </div>

                            <div class="pro-field-card" id="sup-notes-card" style="grid-column: span 3;">
                                <label class="pro-field-label">
                                    <span>📝 ملاحظات</span>
                                </label>
                                <textarea id="sup-notes" placeholder="ملاحظات..."></textarea>
                            </div>
                        </div>

                        <div style="margin-top: 6px; padding: 10px 14px; border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; background: rgba(15, 23, 42, 0.4);">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <h4 style="margin: 0; color: #38bdf8; display:flex; align-items:center; gap:6px; font-size: 0.9rem; font-weight: 800;">
                                    <span>📱</span> <span>أرقام المناديب والتابعين</span>
                                </h4>
                                <button type="button" onclick="addSupplierSubPhone()" style="height: 30px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); padding: 0 12px; border-radius: 8px; cursor: pointer; font-size: 0.78rem; font-weight: 800;">+ إضافة رقم</button>
                            </div>
                            <div id="sup-sub-phones-container" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
                        </div>

                        <div style="margin-top: 14px; display: flex; justify-content: flex-end;">
                            <button type="button" class="btn-add" style="height: 42px; background: linear-gradient(135deg, #10b981, #059669); padding: 0 28px; font-weight: 900; font-size: 0.95rem; border-radius: 10px;" onclick="saveSupplier()">💾 حفظ <span class="kbd-hint">F10</span></button>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div class="input-group" style="width: 100%; max-width: 480px; position: relative; margin-bottom: 0;">
                            <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; pointer-events: none;">🔍</span>
                            <input type="text" id="supplier-search-input" oninput="filterSuppliers()" placeholder="بحث بالاسم، الرقم السري، أو الهاتف..." style="padding-right: 42px; border-radius: 10px; border: 1px solid rgba(56,189,248,0.3); background: rgba(0,0,0,0.3); font-size: 0.95rem; color: #fff; width: 100%;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="button" id="btn-export-suppliers-csv" onclick="exportMasterEntityCsv('supplier')" style="height: 36px; background: rgba(16, 185, 129, 0.14); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📥 تصدير Excel</button>
                            <button type="button" id="btn-import-suppliers-csv" onclick="triggerMasterEntityCsvImport('supplier')" style="height: 36px; background: rgba(168, 85, 247, 0.14); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📤 استيراد Excel</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="tab-suppliers-table">
                            <thead><tr><th style="width: 115px; text-align: center;">🔐 الرقم السري</th><th style="width: 28%; min-width: 180px; text-align: right;">المورد</th><th style="width: 130px; text-align: center;">الواتساب</th><th style="width: 175px; text-align: center;">كشف الحساب</th><th style="width: 85px; text-align: center;">الحالة</th><th style="width: 125px; text-align: center;">الرصيد</th><th style="width: 115px; text-align: center;">الائتمان</th><th style="width: 55px; text-align: center;">السرية</th><th style="width: 110px; text-align: center;">الإجراء</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Banks Tab -->
            <div id="tab-banks" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">🏦 البنوك</h3>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 260px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="bank-search-input" oninput="renderBanks()" placeholder="بحث باسم البنك أو الحساب..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button class="btn-add" onclick="openBankForm()">+ إضافة بنك <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    <div id="bank-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="bank-form-title">بيانات البنك</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="bank-code" placeholder="رقم الحساب"></div>
                            <div class="input-group"><label>اسم البنك *</label><input type="text" id="bank-name" placeholder="مثال: بنك الخرطوم" oninput="autoUpdateMasterBankIBAN()"></div>
                            <div class="input-group" id="bank-branch-group"><label>الفرع</label><input type="text" id="bank-branch" placeholder="مثال: الفرع الرئيسي"></div>
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="bank-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد</label><input type="date" id="bank-opening-date"></div>
                            <div class="input-group"><label>رقم الحساب البنكي</label><input type="text" id="bank-gl-account" placeholder="رقم الحساب" oninput="autoUpdateMasterBankIBAN()"></div>
                            <div class="input-group bank-only-field"><label>IBAN</label><input type="text" id="bank-iban" placeholder="IBAN"></div>
                            <div class="input-group bank-only-field"><label>SWIFT</label><input type="text" id="bank-swift" placeholder="SWIFT"></div>
                            
                            <div class="input-group"><label>العملة</label><select id="bank-currency" class="system-currency-select" onchange="updateBankExchangeRate()"></select></div>
                            <div class="input-group" id="bank-ex-rate-group" style="display: none;"><label id="bank-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف</label><input type="number" id="bank-ex-rate" step="any" value="1" placeholder="1" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group"><label>السرية</label><select id="bank-confidentiality"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="bank-inactive"> إيقاف الحساب</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveBank()">💾 حفظ <span class="kbd-hint">F10</span></button></div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="banks-table">
                            <thead><tr><th>الرقم</th><th>اسم الحساب</th><th>النوع</th><th>العملة</th><th>الرصيد الافتتاحي</th><th>الإجراء</th></tr></thead>
                            <tbody id="banks-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-safes" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">🏦 الخزن</h3>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 260px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="safe-search-input" oninput="renderSafes()" placeholder="بحث باسم الخزينة أو الرقم..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button class="btn-add" onclick="openSafeForm()">+ إضافة خزينة <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    <div id="safe-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="safe-form-title">بيانات الخزينة</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="safe-code" placeholder="رقم الخزينة"></div>
                            <div class="input-group"><label>الاسم *</label><input type="text" id="safe-name" placeholder="مثال: الخزينة الرئيسية"></div>
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="safe-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد</label><input type="date" id="safe-opening-date"></div>
                            <div class="input-group"><label>رقم حساب الأستاذ</label><input type="text" id="safe-gl-account" placeholder="110101"></div>
                            <div class="input-group" id="safe-responsible-group">
                                <label style="display:flex; justify-content:space-between; align-items:center;">
                                    <span>المسؤول</span>
                                    <button type="button" onclick="quickAddEmployeeModal('safe-responsible')" style="background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); border-radius:6px; padding:1px 7px; font-size:0.75rem; font-weight:800; cursor:pointer;">➕</button>
                                </label>
                                <select id="safe-responsible"><option value="">-- اختر --</option></select>
                            </div>
                            <div class="input-group"><label>العملة</label><select id="safe-currency" class="system-currency-select" onchange="updateSafeExchangeRate()"></select></div>
                            <div class="input-group" id="safe-ex-rate-group" style="display: none;"><label id="safe-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف</label><input type="number" id="safe-ex-rate" step="any" value="1" placeholder="1" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group" id="safe-max-limit-group"><label>الحد الأعلى</label><input type="number" id="safe-max-limit" placeholder="0.00"></div>
                            <div class="input-group" id="safe-min-limit-group"><label>الحد الأدنى</label><input type="number" id="safe-min-limit" placeholder="0.00"></div>
                            <div class="input-group"><label>السرية</label><select id="safe-confidentiality"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-inactive"> إيقاف الحساب</label>
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-modifier"> المعدل</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveSafe()">💾 حفظ <span class="kbd-hint">F10</span></button></div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="safes-table">
                            <thead><tr><th>الرقم</th><th>اسم الخزينة</th><th>النوع</th><th>العملة</th><th>الرصيد الافتتاحي</th><th>الإجراء</th></tr></thead>
                            <tbody id="safes-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Items Tab -->
            <div id="tab-items" class="md-tab-content">
                <div class="md-card">
                    <div id="inner-products" class="inner-tab-content active">
                        <div id="item-form" class="md-card" style="display: none; padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                            <div style="background:linear-gradient(135deg,rgba(56,189,248,0.2),rgba(15,23,42,0.95)); padding:12px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; border-bottom:1px solid rgba(56,189,248,0.3);">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:44px; height:44px; background:linear-gradient(135deg, #38bdf8, #818cf8); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">📦</div>
                                    <h3 id="form-mode-indicator" style="margin:0; color:#fff; font-size:1.25rem; font-weight:800;">بيانات الصنف</h3>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; align-items: center;">
                                    <div class="neon-glass-panel neon-border-danger" style="padding: 5px 12px; display: flex; align-items: center; gap: 8px; border-radius: 8px; border: 1px solid rgba(244,63,94,0.3);">
                                        <span>⚠️</span>
                                        <div>
                                            <span style="color:var(--text-secondary); font-size:0.7rem; display: block; line-height: 1; margin-bottom: 2px;">النواقص</span>
                                            <strong id="kpi-low-stock" class="neon-text-danger" style="font-size:0.9rem; font-weight:800; line-height: 1;">0</strong>
                                        </div>
                                    </div>
                                    
                                    <button onclick="openInnerTab(event, 'inner-categories')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:8px 16px; font-size:0.9rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(88,28,135,0.6)); border: 1px solid rgba(168,85,247,0.5); color: #fff; cursor: pointer;">
                                        <span>🗂️</span> الفئات
                                    </button>
                                    <button onclick="openInnerTab(event, 'inner-units')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:8px 16px; font-size:0.9rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); color: #fff; cursor: pointer;">
                                        <span>📐</span> الوحدات
                                    </button>
                                    <button onclick="printItemBarcodeFromForm()" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:8px 16px; font-size:0.9rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(52,211,153,0.2), rgba(6,78,59,0.6)); border: 1px solid rgba(52,211,153,0.5); color: #fff; cursor: pointer;">
                                        <span>🖨️</span> باركود
                                    </button>
                                    <button class="btn-add hover-glow" style="background:linear-gradient(135deg, var(--success-color), #059669); padding:8px 20px; font-size: 0.95rem; border-radius: 8px; border: 1px solid rgba(16,185,129,0.5); color: #fff; cursor: pointer;" onclick="saveItem()">💾 حفظ <span class="kbd-hint">F10</span></button>
                                    <button onclick="closeItemForm()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.5); padding:8px 18px; border-radius:8px; cursor:pointer; font-weight:bold;">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                                </div>
                            </div>
                            
                            <div style="padding:20px; background: rgba(0,0,0,0.2);">
                                <div class="split-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
                                    <div class="neon-glass-panel split-panel" style="padding: 18px; border-top: 3px solid #38bdf8; background: linear-gradient(to bottom, rgba(56,189,248,0.05), transparent);">
                                        <div style="display:flex; align-items:center; gap:8px; margin-bottom: 14px;">
                                            <span style="font-size:1.3rem;">📋</span><h4 style="margin:0; color:#38bdf8; font-weight: 800;">بيانات التعريف</h4>
                                        </div>
                                        
                                        <div style="display:flex; gap:16px; align-items:flex-start;">
                                            <div style="display:flex; flex-direction:column; align-items:center; gap:6px; flex-shrink:0;">
                                                <input type="hidden" id="item-image-data" value="">
                                                <input type="file" id="item-image-file-input" accept="image/*" style="display:none;" onchange="handleItemImageUpload(event)">
                                                <div id="item-qr-box" onclick="printItemBarcodeFromForm()" style="width:98px; height:98px; border:2px dashed rgba(56,189,248,0.5); border-radius:14px; display:flex; flex-direction:column; justify-content:center; align-items:center; background:rgba(0,0,0,0.45); cursor:pointer; overflow:hidden; position:relative;">
                                                    <span style="font-size:1.8rem;">📱</span>
                                                    <span style="font-size:0.7rem; color:#38bdf8; margin-top:4px; font-weight:bold;">QR</span>
                                                </div>
                                                <div style="display:flex; gap:4px;">
                                                    <button type="button" onclick="document.getElementById('item-image-file-input').click()" style="background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.35); border-radius:6px; padding:3px 7px; font-size:0.7rem; font-weight:bold; cursor:pointer;">📷 صورة</button>
                                                    <button type="button" onclick="updateLiveItemQRPreview(true)" style="background:rgba(168,85,247,0.16); color:#d8b4fe; border:1px solid rgba(168,85,247,0.35); border-radius:6px; padding:3px 7px; font-size:0.7rem; font-weight:bold; cursor:pointer;">🔲 QR</button>
                                                </div>
                                            </div>
                                            <div class="form-grid" style="flex:1; grid-template-columns: 1fr;">
                                                <div class="input-group"><label style="color:#fff;">اسم الصنف *</label><input type="text" id="item-name-input" oninput="updateLiveItemQRPreview()" placeholder="مثال: سيخ 3 لنيه" style="border-color: rgba(56,189,248,0.5); background: rgba(0,0,0,0.3);"></div>
                                                <div style="display:flex; gap:10px;">
                                                    <div class="input-group" style="flex:1;"><label>الكود</label><input type="text" id="item-code" oninput="updateLiveItemQRPreview()" placeholder="تلقائي" style="color:#38bdf8; font-weight:bold;"></div>
                                                    <div class="input-group" style="flex:1;"><label>الاسم اللاتيني</label><input type="text" id="item-name-latin" placeholder="Rebar"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-grid" style="margin-top: 14px;">
                                            <div class="input-group"><label>الباركود</label><div style="display:flex; gap:5px;"><input type="text" id="item-barcode" oninput="updateLiveItemQRPreview()" placeholder="الباركود" style="flex:1;"><button type="button" class="btn-text" style="color:#38bdf8; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.35); border-radius:8px; padding:0 10px; font-weight:800;" onclick="openWebRtcBarcodeScanner('item-barcode')">📷 مسح</button></div></div>
                                            <div class="input-group"><label>الماركة</label><input type="text" id="item-brand" placeholder="الماركة / المنشأ"></div>
                                            <div class="input-group"><label>الفئة</label><div style="display:flex; gap:5px;"><select id="item-category" style="flex:1;"></select><button class="btn-text" style="color:#a855f7; background:rgba(168,85,247,0.1); border-radius:8px; padding:0 10px;" onclick="quickAddCategory()">➕</button></div></div>
                                            <input type="hidden" id="item-sub-category" value="">
                                            <div class="input-group"><label>الطبيعة</label><select id="item-nature"><option value="مخزني">📦 مخزني</option><option value="خدمي">🔧 خدمي</option><option value="تجميعي">🏗️ تجميعي</option></select></div>
                                            <div class="input-group"><label>الحالة</label><div style="display:flex; align-items:center; gap:10px; padding:10px; background: rgba(0,0,0,0.2); border-radius: 8px;"><label class="toggle-sw"><input type="checkbox" id="item-active-toggle" checked onchange="updateToggleLabel()"><span class="toggle-sl"></span></label><span id="item-active-label" style="color:var(--success-color); font-weight:bold; font-size:0.9rem;">🟢 نشط</span></div></div>
                                        </div>
                                    </div>

                                    <div class="split-panel" style="display: flex; flex-direction: column; gap: 18px;">
                                        <div class="neon-glass-panel" style="padding: 18px; border-top: 3px solid #34d399; background: linear-gradient(to bottom, rgba(52,211,153,0.05), transparent);">
                                            <div style="display:flex; align-items:center; gap:8px; margin-bottom: 12px;">
                                                <span style="font-size:1.3rem;">💰</span><h4 style="margin:0; color:#34d399; font-weight: 800;">التسعير والضرائب</h4>
                                            </div>
                                            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                                                <div class="input-group"><label>سعر البيع</label><input type="number" id="item-selling-price" placeholder="0.00" style="font-weight:bold; color:var(--success-color); border-color: rgba(52,211,153,0.4); font-size: 1.1rem; background: rgba(52,211,153,0.05);"></div>
                                                <div class="input-group"><label>أقصى خصم %</label><input type="number" id="item-max-discount" placeholder="0" max="100"></div>
                                                <div class="input-group"><label>الضريبة</label><div style="padding:10px; background:rgba(0,0,0,0.2); border-radius:8px;"><label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0;"><input type="checkbox" id="item-taxable" onchange="document.getElementById('item-tax-rate').disabled=!this.checked" style="width:18px; height:18px; accent-color:#34d399;"> خاضع</label></div></div>
                                                <div class="input-group"><label>نسبة الضريبة %</label><input type="number" id="item-tax-rate" placeholder="0" disabled></div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                                            <div class="neon-glass-panel" style="padding: 18px; border-top: 3px solid #f59e0b; background: linear-gradient(to bottom, rgba(245,158,11,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:8px; margin-bottom: 12px;">
                                                    <span style="font-size:1.2rem;">📊</span><h5 style="margin:0; color:#f59e0b; font-weight: 800;">التكلفة والرصيد</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px; grid-column: 1 / -1;"><label style="color:#f59e0b;">طريقة التكلفة</label>
                                                    <div style="display:flex; gap:10px; flex-wrap:wrap; background: rgba(0,0,0,0.2); padding: 8px; border-radius: 8px;">
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="lastPurchase" checked style="accent-color:#f59e0b;"> آخر شراء</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="highest" style="accent-color:#f59e0b;"> الأعلى</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="opening" style="accent-color:#f59e0b;"> الافتتاحي</label>
                                                    </div>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>الكمية الافتتاحية</label><input type="number" id="item-opening-qty" placeholder="0"></div>
                                                <div class="input-group"><label>تكلفة الوحدة</label><input type="number" id="item-opening-value" placeholder="0.00"></div>
                                            </div>

                                            <div class="neon-glass-panel" style="padding: 18px; border-top: 3px solid #ef4444; background: linear-gradient(to bottom, rgba(239,68,68,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:8px; margin-bottom: 12px;">
                                                    <span style="font-size:1.2rem;">🏭</span><h5 style="margin:0; color:#ef4444; font-weight: 800;">حدود المخزون</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>موقع الرف</label><input type="text" id="item-bin-location" placeholder="ممر 3"></div>
                                                <div style="display:flex; gap:10px; margin-bottom: 10px;">
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأعلى</label><input type="number" id="item-max-limit" placeholder="500"></div>
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأدنى</label><input type="number" id="item-min-limit" placeholder="20"></div>
                                                </div>
                                                <div class="input-group"><label style="color:#f59e0b;">حد الطلب</label><input type="number" id="item-reorder-level" placeholder="50"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="neon-glass-panel" style="margin-top: 18px; padding: 20px; border-top: 3px solid #a855f7; background: linear-gradient(to bottom, rgba(168,85,247,0.05), transparent);">
                                    <div style="display:flex; align-items:center; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:1.4rem;">📐</span><h4 style="margin:0; color:#a855f7; font-weight: 800; font-size: 1.15rem;">الوحدات</h4>
                                        </div>
                                        <div style="display:flex; align-items:center; justify-content: flex-start; gap:12px; background: rgba(0,0,0,0.3); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(168,85,247,0.3); flex: 1; min-width: 280px;">
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 0.95rem;">الوحدة الأساسية *</label>
                                            <div style="display:flex; align-items:center; gap:5px;">
                                                <select id="item-base-unit" onchange="updateBaseUnitRow()" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.05rem; padding: 4px; outline:none; width: 140px; text-align: center;">
                                                    <option value="">-- اختر --</option>
                                                </select>
                                                <button class="btn-text" style="color:#38bdf8; background:rgba(56,189,248,0.1); border-radius:8px; padding:0 8px; font-size:0.9rem;" onclick="quickAddUnit()">➕</button>
                                            </div>
                                            <div style="width: 1px; height: 22px; background: rgba(168,85,247,0.3);"></div>
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 0.95rem;">الشيلة</label>
                                            <input type="number" id="item-bundle-factor" placeholder="15" value="1" min="1" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.05rem; padding: 4px; outline:none; width: 70px; text-align: center;">
                                        </div>
                                    </div>
                                    
                                    <div style="background:rgba(15,23,42,0.8); border-radius:12px; border:1px solid rgba(168,85,247,0.2); overflow: hidden;">
                                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px; padding:10px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(168,85,247,0.05); flex-wrap:wrap;">
                                            <button type="button" class="btn-text" style="color:#fef08a; font-weight:900; background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.45); padding:7px 14px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:5px;" onclick="window.generateMultiUnitEan13Barcodes()">⚡ توليد باركودات الوحدات (EAN-13)</button>
                                            <button type="button" class="btn-text" style="color:#fff; font-weight:bold; background:linear-gradient(135deg, #a855f7, #9333ea); padding:7px 14px; border-radius:8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 5px;" onclick="addUnitRow()">+ إضافة وحدة</button>
                                        </div>
                                        <div class="table-responsive" style="overflow-x: auto;">
                                            <table class="pro-table" style="width: 100%; min-width: 1200px;">
                                                <thead><tr style="background: rgba(0,0,0,0.2);">
                                                    <th style="padding: 12px; width: 4%; text-align: center;">م</th>
                                                    <th style="padding: 12px; width: 14%;">الوحدة</th>
                                                    <th style="padding: 12px; width: 8%; text-align: center;">المعادل</th>
                                                    <th style="padding: 12px; width: 12%;">الباركود</th>
                                                    <th style="padding: 12px; width: 16%; color: #34d399;">نقدي</th>
                                                    <th style="padding: 12px; width: 16%; color: #60a5fa;">آجل</th>
                                                    <th style="padding: 12px; width: 16%; color: #f87171;">أقل سعر</th>
                                                    <th style="padding: 12px; width: 7%;">الوزن</th>
                                                    <th style="padding: 12px; width: 7%; text-align: center;">إجراء</th>
                                                </tr></thead>
                                                <tbody id="units-matrix-body">
                                                    <tr style="background:rgba(52,211,153,0.05); border-right:4px solid var(--success-color);">
                                                        <td style="text-align: center; font-weight: bold; color: var(--text-secondary);">1</td>
                                                        <td><input type="text" id="base-unit-name-display" value="" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; font-size: 1.05rem; outline: none;"></td>
                                                        <td><input type="number" value="1" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; font-size:1.1rem; outline: none;"></td>
                                                        <td><input type="text" placeholder="الباركود" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:8px; border-radius:8px; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#34d399; padding:8px; border-radius:8px; font-weight:bold; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#60a5fa; padding:8px; border-radius:8px; font-weight:bold; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#f87171; padding:8px; border-radius:8px; font-weight:bold; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:8px; border-radius:8px; outline: none;"></td>
                                                        <td style="text-align: center;"><span style="font-size:0.78rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); border: 1px solid rgba(52,211,153,0.4); padding:5px 10px; border-radius:6px; display: inline-block;">أساسية</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="neon-glass-panel" style="margin-top: 16px; padding: 16px; border-top: 3px solid #94a3b8; background: linear-gradient(to bottom, rgba(148,163,184,0.05), transparent);">
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom: 10px;">
                                        <span style="font-size:1.1rem;">📝</span><h5 style="margin:0; color:#94a3b8; font-weight: 800;">ملاحظات</h5>
                                    </div>
                                    <textarea id="item-description" placeholder="ملاحظات..." style="width:100%; min-height:70px; padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:#fff; font-family:inherit; resize:vertical; box-sizing:border-box; font-size: 0.95rem; outline: none;"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px; background:rgba(0,0,0,0.3); padding:8px 15px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); flex:1; max-width:400px;">
                                    <span>🔍</span>
                                    <input type="text" id="item-search" oninput="filterItemsTable()" placeholder="بحث بالاسم أو الكود..." style="background:transparent; border:none; color:#fff; width:100%; outline:none;">
                                </div>
                                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                    <span id="kpi-total-items-badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; padding:6px 14px; border-radius:20px; font-weight:bold; border:1px solid rgba(56,189,248,0.3);">الأصناف: <strong id="kpi-total-items">0</strong></span>
                                    <button type="button" onclick="openBulkPriceUpdaterModal()" style="height: 36px; background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45); padding: 0 14px; border-radius: 8px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📈 تعديل الأسعار</button>
                                    <button type="button" onclick="openSmartClipboardPasteModal('item')" style="height: 36px; background: rgba(168, 85, 247, 0.16); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.45); padding: 0 14px; border-radius: 8px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📋 لصق Excel</button>
                                    <button type="button" id="btn-export-items-csv" onclick="exportMasterEntityCsv('item')" style="height: 36px; background: rgba(16, 185, 129, 0.14); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 14px; border-radius: 8px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📥 تصدير Excel</button>
                                    <button type="button" id="btn-import-items-csv" onclick="triggerMasterEntityCsvImport('item')" style="height: 36px; background: rgba(245, 158, 11, 0.14); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 0 14px; border-radius: 8px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📤 استيراد Excel</button>
                                    <button class="btn-add hover-glow" onclick="openItemForm()" style="background:linear-gradient(135deg, #38bdf8, #2563eb); padding:8px 18px; border-radius:8px; font-weight:bold; border:none; color:#fff; cursor:pointer;">+ إضافة صنف <span class="kbd-hint">F2</span></button>
                                </div>
                            </div>
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="pro-table" id="main-items-table">
                                    <thead><tr><th>الكود</th><th>الصنف</th><th>الفئة</th><th>الوحدة</th><th>سعر البيع</th><th>الحالة</th><th>الإجراء</th></tr></thead>
                                    <tbody id="items-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="inner-categories" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-purple" style="margin-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <h4 style="margin:0; color:#a855f7;">الفئات</h4>
                                <button type="button" class="btn-text" style="color:#38bdf8; font-weight:bold;" onclick="openInnerTab(event, 'inner-products')">↩ الأصناف</button>
                            </div>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الفئة</label><input type="text" id="cat-name-input" placeholder="مثال: أسمنت"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:var(--success-color); padding:10px 20px; height:fit-content;" onclick="saveCategory()">💾 حفظ <span class="kbd-hint">F10</span></button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th>اسم الفئة</th><th>الأصناف</th><th>الإجراء</th></tr></thead>
                                <tbody id="categories-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="inner-units" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-info" style="margin-bottom:20px; border-color: rgba(56,189,248,0.3);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                                <h4 style="margin:0; color:#38bdf8; font-weight:bold; font-size:1.1rem;">📐 الوحدات</h4>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" class="btn-add" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; padding:6px 14px; font-size:0.82rem;" onclick="cleanAndResetUnits()">🧹 استعادة الوحدات</button>
                                    <button type="button" class="btn-text" style="color:#38bdf8; font-weight:bold;" onclick="openInnerTab(event, 'inner-products')">↩ الأصناف</button>
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الوحدة</label><input type="text" id="unit-name-input" placeholder="مثال: طن، كرتونة، حبة"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); padding:10px 20px; height:fit-content; font-weight:bold;" onclick="saveGlobalUnit()">💾 حفظ <span class="kbd-hint">F10</span></button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th style="padding:12px 15px;">اسم الوحدة</th><th style="padding:12px 15px; text-align:center; width:180px;">الإجراء</th></tr></thead>
                                <tbody id="units-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees Tab -->
            <div id="tab-employees" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">👥 الموظفين</h3>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 260px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="employee-search-input" oninput="renderEmployees()" placeholder="بحث باسم الموظف أو الهاتف..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button class="btn-add" onclick="openNewEmployeeForm()">+ إضافة موظف <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    <div id="employee-form" class="md-card" style="display: none;">
                        <h4 style="margin-top:0; color:var(--primary-accent);">بيانات الموظف</h4>
                        <div class="form-grid">
                            <div class="input-group"><label>الكود</label><input type="text" id="emp-id" disabled></div>
                            <div class="input-group"><label>الاسم *</label><input type="text" id="emp-name"></div>
                            <div class="input-group"><label>الهاتف</label><input type="text" id="emp-phone"></div>
                            <div class="input-group"><label>كلمة المرور</label><input type="text" id="emp-password" placeholder="كلمة المرور"></div>
                            <div class="input-group"><label>الدور</label><select id="emp-role"><option value="admin">مدير</option><option value="accountant">محاسب</option><option value="cashier">كاشير</option><option value="sales">مبيعات</option><option value="storekeeper">أمين مخزن</option></select></div>
                            <div class="input-group"><label>السرية</label><select id="emp-security"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                            <div class="input-group"><label>الحالة</label><select id="emp-status"><option value="active">نشط</option><option value="inactive">موقوف</option></select></div>
                        </div>
                        <div style="display:flex; gap:10px; margin-top:15px;">
                            <button type="button" class="btn-add" style="background:var(--success-color);" onclick="saveEmployee()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-action" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.5); padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:bold;" onclick="document.getElementById('employee-form').style.display='none'">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>الاسم</th><th>الدور</th><th>الهاتف</th><th>السرية</th><th>الحالة</th><th>الإجراء</th></tr></thead>
                            <tbody id="tab-employees-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-costcenters" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">📊 مراكز التكلفة</h3>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 260px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="costcenter-search-input" oninput="renderCostCenters()" placeholder="بحث باسم المركز أو الكود..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button class="btn-add" onclick="openCostCenterForm()">+ إضافة مركز تكلفة <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    
                    <div id="costcenter-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">بيانات مركز التكلفة</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-costcenter" onclick="closeCostCenterForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="costcenter-id">
                            <div class="input-group"><label>الكود</label><input type="text" id="costcenter-code" placeholder="تلقائي" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;"></div>
                            <div class="input-group"><label>الاسم *</label><input type="text" id="costcenter-name" placeholder="مثال: مشروع الخرطوم"></div>
                            <div class="input-group"><label>ملاحظات</label><input type="text" id="costcenter-desc" placeholder="ملاحظات..."></div>
                        </div>
                        <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveCostCenter()">💾 حفظ <span class="kbd-hint">F10</span></button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>الكود</th><th>الاسم</th><th>الوصف</th><th>الإجراء</th></tr></thead>
                            <tbody id="costcenters-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Universal Master Data Entity Deletion & Voucher Check Modal -->
    <div id="master-data-delete-modal" class="md-child-modal" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(0, 0, 0, 0.82); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); justify-content: center; align-items: center; padding: 20px; animation: fadeIn 0.25s ease-out;">
        <div class="neo-card md-child-modal-card" style="background: rgba(18, 15, 25, 0.96); border: 2.5px solid rgba(255, 255, 255, 0.15); border-radius: 22px; width: 100%; max-width: 630px; box-shadow: 0 25px 65px rgba(0,0,0,0.85), 0 0 35px rgba(0,0,0,0.6); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; font-family: 'Tajawal', sans-serif;">
            <div id="md-del-modal-header" style="padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.02);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div id="md-del-modal-icon-wrap" style="width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); box-shadow: 0 0 20px rgba(239,68,68,0.2);">
                        <span id="md-del-modal-icon">🚫</span>
                    </div>
                    <div>
                        <h3 id="md-del-modal-title" style="margin: 0; font-size: 1.3rem; font-weight: 900; color: #fff; letter-spacing: 0.3px;">فحص السندات المرتبطة</h3>
                    </div>
                </div>
                <button type="button" onclick="closeEntityDeleteModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">✕</button>
            </div>

            <div style="padding: 24px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 18px;">
                <div style="background: linear-gradient(135deg, rgba(56,189,248,0.08), rgba(99,102,241,0.05)); border: 1px solid rgba(56,189,248,0.25); border-radius: 16px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span id="md-del-target-type-label" style="font-size: 0.8rem; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 2px;">📦 الاسم:</span>
                        <strong id="md-del-target-name" style="font-size: 1.3rem; color: #38bdf8; font-weight: 900;">-</strong>
                    </div>
                    <div style="text-align: left;">
                        <span id="md-del-target-code-label" style="font-size: 0.8rem; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 2px;">🏷️ الكود:</span>
                        <span id="md-del-target-code" style="font-family: 'Outfit', monospace; font-size: 1.15rem; font-weight: 900; color: #f1f5f9; background: rgba(0,0,0,0.3); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">-</span>
                    </div>
                </div>

                <div id="md-del-status-box" style="border-radius: 16px; padding: 18px; border: 1.5px solid transparent;"></div>

                <div id="md-del-vouchers-container" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span id="md-del-vouchers-header-label" style="font-size: 0.95rem; font-weight: 800; color: #f87171; display: flex; align-items: center; gap: 6px;">📋 السندات المرتبطة:</span>
                        <span id="md-del-vouchers-count" style="font-size: 0.85rem; background: rgba(239,68,68,0.25); color: #fca5a5; padding: 3px 10px; border-radius: 8px; font-weight: 900; border: 1px solid rgba(239,68,68,0.4);">0 سند</span>
                    </div>
                    <div id="md-del-vouchers-list" style="max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 10px; display: flex; flex-direction: column; gap: 8px;"></div>
                </div>
            </div>

            <div id="md-del-modal-footer" style="padding: 18px 24px; border-top: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap;"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/master-data-core.js?v=<?php echo file_exists('js/master-data-core.js') ? filemtime('js/master-data-core.js') : '20261030_v10'; ?>"></script>
</body>
</html>
