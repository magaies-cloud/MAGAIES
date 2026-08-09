<?php $page_title = 'ط§ظ„ط¨ظٹط§ظ†ط§طھ ط§ظ„ط£ط³ط§ط³ظٹط©'; ?>
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
    <title>ظ…ظ‚ط§ظٹظٹط³ (MAGAIEES) - ط§ظ„ط¨ظٹط§ظ†ط§طھ ط§ظ„ط£ط³ط§ط³ظٹط©</title>
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
        :root.light-theme .md-tabs-sidebar {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        :root.light-theme .md-tab-btn {
            background: #ffffff !important;
            border-color: rgba(0,0,0,0.06) !important;
            color: #64748b !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        :root.light-theme .md-tab-btn:hover {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
            color: #0f172a !important;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: rgba(0,0,0,0.12) !important;
        }
        :root.light-theme .md-tab-btn.active {
            background: linear-gradient(135deg, #3b82f6, #60a5fa) !important;
            border-color: transparent !important;
            color: #ffffff !important;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4) !important;
            transform: scale(1.05) translateY(-2px);
        }
        :root.light-theme .md-sidebar-panel {
            background: #ffffff !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme .md-content-panel {
            background: #ffffff !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme table.pro-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        :root.light-theme table.pro-table td {
            color: #000000 !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td {
            background: rgba(0, 0, 0, 0.02) !important;
        }
        :root.light-theme label, :root.light-theme .h-label, :root.light-theme .tot-lbl {
            color: #334155 !important;
        }
        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0; min-height: 100vh;
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
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15; animation: floatOrb 20s infinite alternate ease-in-out; }
        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-pink); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 33% { transform: translate(30px, -50px) scale(1.1); } 66% { transform: translate(-30px, 30px) scale(0.9); } 100% { transform: translate(0, 0) scale(1); } }

        .dash-main { width: 96%; max-width: 1600px; margin: 0 auto; padding: 0px 0 20px 0; box-sizing: border-box; display: flex; flex-direction: column; gap: 15px; flex: 1; animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }



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
            padding: 12px;
            display: flex;
            flex-direction: row;
            align-items: center;
            overflow-x: auto;
            gap: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.1);
            margin-bottom: 5px;
        }

        .md-tab-btn {
            position: relative;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 3.8px solid var(--tab-border-color, rgba(255, 255, 255, 0.2)); /* ط²ظٹط§ط¯ط© ط³ظ…ظƒ ط§ظ„ط­ظˆط§ظپ ط¨ظ€ 1.5 ظ…ط±ط© (3.8px) */
            border-radius: 14px;
            padding: 11px 18px;
            font-size: 1.05rem;
            font-weight: 800;
            color: #e2e8f0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        /* طھط£ط«ظٹط± ظ„ظ…ط¹ط© ط§ظ„ط¬ظ„ظٹطھط± ط§ظ„ط¨ط±ظ‘ط§ظ‚ط© ط§ظ„ط¬ط°ط§ط¨ط© ظپظٹ ط§ظ„ط¯ط§ط®ظ„ */
        .md-tab-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 35%, rgba(255, 255, 255, 0.95) 1.5px, transparent 1.5px),
                radial-gradient(circle at 75% 25%, rgba(255, 255, 255, 0.85) 1.2px, transparent 1.2px),
                radial-gradient(circle at 45% 70%, rgba(255, 255, 255, 0.9) 1.5px, transparent 1.5px),
                radial-gradient(circle at 85% 75%, rgba(255, 255, 255, 0.8) 1.2px, transparent 1.2px),
                linear-gradient(135deg, transparent 35%, rgba(255, 255, 255, 0.22) 50%, transparent 65%);
            background-size: 55px 55px, 40px 40px, 48px 48px, 42px 42px, 200% 200%;
            background-repeat: repeat, repeat, repeat, repeat, no-repeat;
            opacity: 0.55;
            mix-blend-mode: overlay;
            pointer-events: none;
            z-index: 2;
            animation: glitterShimmer 3.5s infinite linear;
        }

        .md-tab-btn:hover::after,
        .md-tab-btn.active::after {
            opacity: 0.95;
            animation: glitterShimmer 2.2s infinite linear;
        }

        @keyframes glitterShimmer {
            0% {
                background-position: 0 0, 0 0, 0 0, 0 0, -100% -100%;
                filter: brightness(1) drop-shadow(0 0 1px #fff);
            }
            50% {
                filter: brightness(1.75) drop-shadow(0 0 3px #fff);
            }
            100% {
                background-position: 55px 55px, -40px 40px, 48px -48px, -42px -42px, 200% 200%;
                filter: brightness(1) drop-shadow(0 0 1px #fff);
            }
        }

        /* ط§ظ„ط¥ظٹظ…ط§ط،ط© ط§ظ„ط¶ظˆط¦ظٹط© ط§ظ„ط³ط§ط·ط¹ط© ط§ظ„ط¹ط§ط¨ط±ط© ط¹ظ„ظ‰ ط³ط·ط­ ط§ظ„ط²ط± */
        .md-tab-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 70%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
            transform: skewX(-25deg);
            transition: 0.5s;
            pointer-events: none;
            z-index: 3;
        }
        .md-tab-btn:hover::before {
            left: 140%;
            transition: 0.75s ease-in-out;
        }

        /* طھط­ط±ظٹظƒ ط§ظ„ط£ظٹظ‚ظˆظ†ط© ط¯ط§ط®ظ„ ط§ظ„ط²ط± ط¹ظ†ط¯ ط§ظ„طھظ…ط±ظٹط± */
        .md-tab-icon {
            font-size: 1.3rem;
            display: inline-block;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.4));
            position: relative;
            z-index: 4;
        }

        .md-tab-btn:hover {
            transform: translateY(-5px) scale(1.08);
            color: #ffffff !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 12px 30px var(--tab-glow-color), 0 0 20px var(--tab-glow-color), inset 0 1px 2px rgba(255,255,255,0.4) !important;
            background: var(--tab-hover-bg) !important;
            z-index: 10;
        }

        .md-tab-btn:hover .md-tab-icon {
            transform: scale(1.35) rotate(8deg);
        }

        /* ط­ط§ظ„ط© ط§ظ„طھط­ط¯ظٹط¯ ط§ظ„ط²ط± ط§ظ„ظ†ط´ط· (Active Tab) ظ…ط¹ ط§ظ„ط­ظپط§ط¸ ط§ظ„ظƒظ„ظٹ ط¹ظ„ظ‰ ط§ظ„ط¥ط¶ط§ط،ط© ط§ظ„ط¹ط§ظƒط³ط© ط§ظ„ط®ط§ط·ظپط© ظ„ظ„ط£ط¨طµط§ط± */
        .md-tab-btn.active {
            color: #ffffff !important;
            background: var(--tab-active-bg) !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 10px 30px var(--tab-glow-color), 0 0 25px var(--tab-glow-color), inset 0 2px 4px rgba(255,255,255,0.5) !important;
            transform: translateY(-3px) scale(1.05);
            animation: tabPulse 2.5s infinite alternate ease-in-out;
        }

        .md-tab-btn.active .md-tab-icon {
            transform: scale(1.25);
        }

        @keyframes tabPulse {
            0% { box-shadow: 0 10px 25px var(--tab-glow-color), 0 0 15px var(--tab-glow-color), inset 0 2px 4px rgba(255,255,255,0.4); }
            100% { box-shadow: 0 16px 40px var(--tab-glow-color), 0 0 35px var(--tab-glow-color), inset 0 2px 6px rgba(255,255,255,0.7); }
        }

        /* ًںژ¨ طھط®طµظٹطµ ط§ظ„ط£ظ„ظˆط§ظ† ظˆط§ظ„ط­ط¯ظˆط¯ ظˆط§ظ„ط¥ط¶ط§ط،ط© ط§ظ„ط¹ط§ظƒط³ط© ظ„ظƒظ„ طھط¨ظˆظٹط¨ */
        
        /* ًںڈ¢ ط§ظ„ظ…ط®ط§ط²ظ† - ط³ظ…ط§ظˆظٹ ط²ط§ظ‡ظٹ (Sky Blue) */
        .md-tab-btn.tab-warehouses {
            --tab-border-color: #38bdf8;
            --tab-glow-color: rgba(56, 189, 248, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(2, 132, 199, 0.4), rgba(56, 189, 248, 0.6));
            --tab-active-bg: linear-gradient(135deg, #0284c7, #38bdf8);
        }

        /* ًں‘¥ ط§ظ„ط¹ظ…ظ„ط§ط، - ط²ظ…ط±ط¯ظٹ ظ†ظٹظˆظ† (Emerald Green) */
        .md-tab-btn.tab-customers {
            --tab-border-color: #34d399;
            --tab-glow-color: rgba(52, 211, 153, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(5, 150, 105, 0.4), rgba(52, 211, 153, 0.6));
            --tab-active-bg: linear-gradient(135deg, #059669, #34d399);
        }

        /* ًںڈ­ ط§ظ„ظ…ظˆط±ط¯ظٹظ† - ط¨ظ†ظپط³ط¬ظٹ ط³ط§ط­ط± (Purple Violet) */
        .md-tab-btn.tab-suppliers {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(192, 132, 252, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(124, 58, 237, 0.4), rgba(192, 132, 252, 0.6));
            --tab-active-bg: linear-gradient(135deg, #7c3aed, #c084fc);
        }

        /* ًںڈ¦ ط§ظ„ط¨ظ†ظˆظƒ - ط°ظ‡ط¨ظٹ ظ…طھط£ظ„ظ‚ (Amber Gold) */
        .md-tab-btn.tab-banks {
            --tab-border-color: #fbbf24;
            --tab-glow-color: rgba(251, 191, 36, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(217, 119, 6, 0.4), rgba(251, 191, 36, 0.6));
            --tab-active-bg: linear-gradient(135deg, #d97706, #fbbf24);
        }

        /* ًں’° ط§ظ„ط®ط²ظ† - ظ„ظٹظ…ظˆظ†ظٹ ظپط³ظپظˆط±ظٹ (Neon Lime) */
        .md-tab-btn.tab-safes {
            --tab-border-color: #4ade80;
            --tab-glow-color: rgba(74, 222, 128, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(22, 163, 74, 0.4), rgba(74, 222, 128, 0.6));
            --tab-active-bg: linear-gradient(135deg, #16a34a, #4ade80);
        }

        /* ًں“¦ ط§ظ„ط£طµظ†ط§ظپ ظˆط§ظ„ظˆط­ط¯ط§طھ - ظˆط±ط¯ظٹ ظ…ظ„ظƒظٹ (Fuchsia Rose) */
        .md-tab-btn.tab-items {
            --tab-border-color: #f472b6;
            --tab-glow-color: rgba(244, 114, 182, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(219, 39, 119, 0.4), rgba(244, 114, 182, 0.6));
            --tab-active-bg: linear-gradient(135deg, #db2777, #f472b6);
        }

        /* ًں‘¤ ط§ظ„ظ…ظˆط¸ظپظٹظ† - ظ†ظٹظ„ظٹ ط¨ط±ط§ظ‚ (Indigo Blue) */
        .md-tab-btn.tab-employees {
            --tab-border-color: #818cf8;
            --tab-glow-color: rgba(129, 140, 248, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(79, 70, 229, 0.4), rgba(129, 140, 248, 0.6));
            --tab-active-bg: linear-gradient(135deg, #4f46e5, #818cf8);
        }

        /* ًں“ٹ ظ…ط±ط§ظƒط² ط§ظ„طھظƒظ„ظپط© - ط¨ط±طھظ‚ط§ظ„ظٹ ظ…طھظˆظ‡ط¬ (Vibrant Orange) */
        .md-tab-btn.tab-costcenters {
            --tab-border-color: #fb923c;
            --tab-glow-color: rgba(251, 146, 60, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(234, 88, 12, 0.4), rgba(251, 146, 60, 0.6));
            --tab-active-bg: linear-gradient(135deg, #ea580c, #fb923c);
        }

        /* ًںڑھ ط®ط±ظˆط¬ / ط±ط¬ظˆط¹ - ط±ظˆط¨ظٹ ط£ط­ظ…ط± (Ruby Red) */
        .md-tab-btn.tab-exit {
            --tab-border-color: #f87171;
            --tab-glow-color: rgba(248, 113, 113, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(220, 38, 38, 0.4), rgba(248, 113, 113, 0.6));
            --tab-active-bg: linear-gradient(135deg, #dc2626, #f87171);
        }

        /* ط§ظ„طھظˆط§ظپظ‚ ط§ظ„طھط§ظ… ظ…ط¹ ط§ظ„ظˆط¶ط¹ ط§ظ„ظپط§طھط­ Light Theme */
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

        /* --- طھط­ط³ظٹظ† ظˆطھظƒط¨ظٹط± ط§ظ„ط®ط·ظˆط· ط¯ط§ط®ظ„ ط¬ظ…ظٹط¹ ط§ظ„ط­ظ‚ظˆظ„ ظˆط§ظ„ظ…ط³طھظ†ط¯ط§طھ ظپظٹ ط§ظ„ط¨ظٹط§ظ†ط§طھ ط§ظ„ط£ط³ط§ط³ظٹط© --- */
        body { font-size: 1.05rem; }
        
        input, 
        select, 
        textarea,
        .input-group input, 
        .input-group select, 
        .input-group textarea,
        .form-control,
        .search-input,
        .system-currency-select {
            font-size: 1.25rem !important;  /* طھظƒط¨ظٹط± ط§ظ„ط®ط· ط¯ط§ط®ظ„ ط§ظ„ط­ظ‚ظˆظ„ ط¨ط¯ط±ط¬ط§طھ ظ…ط¶ط§ط¹ظپط© (ط­ظˆط§ظ„ظٹ 20px) ظ„ظٹظƒظˆظ† ظˆط§ط¶ط­ط§ظ‹ ظˆط¬ظ„ظٹط§ظ‹ */
            font-weight: 700 !important;    /* ط®ط· ط¨ط§ط±ط² ظˆط¹ط±ظٹط¶ ظ„ط³ظ‡ظˆظ„ط© ط§ظ„ظ‚ط±ط§ط،ط© ظˆط§ظ„ظˆط¶ظˆط­ */
            min-height: 48px !important;    /* ط¶ط¨ط· ظ…ظ‚ط§ط³ ظˆطھظ†ط§ط³ظ‚ ط§ط±طھظپط§ط¹ ط§ظ„ط­ظ‚ظ„ ظ…ط¹ ط­ط¬ظ… ط§ظ„ط®ط· ط§ظ„ظ…ظƒط¨ط± */
            padding: 10px 16px !important;  /* ط­ط´ظˆ ط¯ط§ط®ظ„ظٹ ظ…طھظ†ط§ط³ط¨ ظ…ط¹ ظ…ظ‚ط§ط³ ط§ظ„ط­ظ‚ظˆظ„ */
            line-height: 1.4 !important;
            box-sizing: border-box !important;
            font-family: 'Tajawal', sans-serif !important;
        }

        select option {
            font-size: 1.15rem !important;
            font-weight: 600 !important;
            padding: 10px 14px !important;
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
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            min-height: 95px !important;
            padding: 12px 16px !important;
            line-height: 1.5 !important;
        }

        table input,
        table select,
        table textarea,
        table.pro-table input,
        table.pro-table select {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            min-height: 42px !important;
            padding: 6px 12px !important;
        }

        .input-group label,
        label {
            font-size: 1.05rem !important;
            font-weight: 800 !important;
            margin-bottom: 6px !important;
        }
        
        #customer-search-input,
        #item-search-input,
        #supplier-search-input {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            min-height: 52px !important;
            padding-right: 48px !important;
        }

        table.pro-table { font-size: 1rem !important; }

        /* --- طھظƒط¨ظٹط± ط®ط·ظˆط· ط§ظ„ط£ط±ظ‚ط§ظ… (ظپظٹ ط§ظ„ط­ظ‚ظˆظ„ ظˆط§ظ„ط¬ط¯ط§ظˆظ„ ظˆط§ظ„ط£ط±طµط¯ط©) ط¨ط¯ط±ط¬طھظٹظ† ط¥ط¶ط§ظپظٹطھظٹظ† ط²ظٹط§ط¯ط© (ط­ط¬ظ… ظپط§ط¦ظ‚ ط§ظ„ط¨ط±ظˆط² ظˆط§ظ„ظˆط¶ظˆط­) --- */
        input[type="number"],
        input[type="tel"],
        input[type="date"],
        input[step],
        input.num-input,
        .num-val,
        [id*="balance"],
        [id*="price"],
        [id*="limit"],
        [id*="phone"],
        [id*="qty"],
        [id*="rate"],
        [id*="code"],
        [id*="whatsapp"] {
            font-size: 1.55rem !important;   /* طھظƒط¨ظٹط± ط§ظ„ط£ط±ظ‚ط§ظ… ط¨ط¯ط±ط¬طھظٹظ† ط¥ط¶ط§ظپظٹطھظٹظ† ط²ظٹط§ط¯ط© (~25px) ظ„ط¨ط±ظˆط² ظˆظˆط¶ظˆط­ ظپط§ط¦ظ‚ ظˆظ…ط±ظٹط­ ظ„ظ„ط¹ظٹظ† */
            font-weight: 900 !important;     /* ط®ط· ط¹ط±ظٹط¶ ظˆظپط§ط¦ظ‚ ط§ظ„ط¨ط±ظˆط² ظ„ظ„ط£ط±ظ‚ط§ظ… */
            font-family: 'Outfit', 'Tajawal', sans-serif !important;
            font-variant-numeric: tabular-nums !important;
            letter-spacing: 0.5px !important;
            min-height: 52px !important;
        }

        table.pro-table td,
        table.pro-table td strong,
        .tot-val, 
        .val {
            font-size: 1.28rem !important;   /* طھظƒط¨ظٹط± ط£ط±ظ‚ط§ظ… ط§ظ„ط®ظ„ط§ظٹط§ ظˆط§ظ„ط£ط±طµط¯ط© ظˆط§ظ„ط­ط¯ظˆط¯ ظپظٹ ط§ظ„ط¬ط¯ط§ظˆظ„ (~20.5px) */
            font-weight: 800 !important;
            font-variant-numeric: tabular-nums !important;
        }

        table input[type="number"],
        table.pro-table input[type="number"] {
            font-size: 1.38rem !important;   /* طھظƒط¨ظٹط± ط£ط±ظ‚ط§ظ… ط§ظ„ط­ظ‚ظˆظ„ ط¯ط§ط®ظ„ ط§ظ„ط¬ط¯ط§ظˆظ„ ظˆط§ظ„ظ…طµظپظˆظپط§طھ (~22px) */
            font-weight: 900 !important;
            font-family: 'Outfit', 'Tajawal', sans-serif !important;
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
        
        /* طھط£ط«ظٹط±ط§طھ ط­ط±ظƒظٹط© ظˆط¶ظˆط¦ظٹط© ظ„ظ„ط£ط²ط±ط§ط± */
        .btn-action:hover, .btn-add:hover, .hover-glow:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 10px 25px rgba(168, 85, 247, 0.5) !important;
            filter: brightness(1.2);
            animation: pulse-glow 1.5s infinite;
        }
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 15px rgba(168,85,247,0.4); }
            50% { box-shadow: 0 0 30px rgba(168,85,247,0.8); }
            100% { box-shadow: 0 0 15px rgba(168,85,247,0.4); }
        }
        /* ------------------------------------------- */
    
        
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
        
        /* ط¥ط®ظپط§ط، ط­ظ‚ظ„ ط§ظ„ظپط¦ط© ط§ظ„ظپط±ط¹ظٹط© ظ„ظ„ط¬ط±ط¯ ظ†ظ‡ط§ط¦ظٹط§ظ‹ ط¨ظ†ط§ط، ط¹ظ„ظ‰ ط·ظ„ط¨ ط§ظ„ظ…ط³طھط®ط¯ظ… */
        #item-sub-category { display: none !important; }
        .input-group:has(#item-sub-category) { display: none !important; }
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
        <?php $header_theme_class = 'header-theme-masterdata'; include 'includes/header.php'; ?>

        <aside class="md-tabs-sidebar">
            <button class="md-tab-btn tab-warehouses active" onclick="openTab(event, 'tab-warehouses')"><span class="md-tab-icon">ًںڈ¢</span> ط§ظ„ظ…ط®ط§ط²ظ†</button>
            <button class="md-tab-btn tab-customers" onclick="openTab(event, 'tab-customers')"><span class="md-tab-icon">ًں‘¥</span> ط§ظ„ط¹ظ…ظ„ط§ط،</button>
            <button class="md-tab-btn tab-suppliers" onclick="openTab(event, 'tab-suppliers')"><span class="md-tab-icon">ًںڈ­</span> ط§ظ„ظ…ظˆط±ط¯ظٹظ†</button>
            <button class="md-tab-btn tab-banks" onclick="openTab(event, 'tab-banks')"><span class="md-tab-icon">ًںڈ¦</span> ط§ظ„ط¨ظ†ظˆظƒ</button>
            <button class="md-tab-btn tab-safes" onclick="openTab(event, 'tab-safes')"><span class="md-tab-icon">ًں’°</span> ط§ظ„ط®ط²ظ†</button>
            <button class="md-tab-btn tab-items" onclick="openTab(event, 'tab-items'); setTimeout(openItemForm, 50);"><span class="md-tab-icon">ًں“¦</span> ط§ظ„ط£طµظ†ط§ظپ ظˆط§ظ„ظˆط­ط¯ط§طھ</button>
            <button class="md-tab-btn tab-employees" onclick="openTab(event, 'tab-employees')"><span class="md-tab-icon">ًں‘¤</span> ط§ظ„ظ…ظˆط¸ظپظٹظ†</button>
            <button class="md-tab-btn tab-costcenters" onclick="openTab(event, 'tab-costcenters')"><span class="md-tab-icon">ًں“ٹ</span> ظ…ط±ط§ظƒط² ط§ظ„طھظƒظ„ظپط©</button>

            <div style="flex: 1;"></div>
            <button class="md-tab-btn tab-exit" onclick="goBackOrExit()"><span class="md-tab-icon">ًںڑھ</span> ط®ط±ظˆط¬ / ط±ط¬ظˆط¹</button>
        </aside>

        <main class="md-content-area">
            
            <div id="tab-warehouses" class="md-tab-content active">
                <div class="md-card">
                    <div class="md-card-header">
                        <h3 class="md-card-title">ًںڈ¢ ظ‚ط§ط¦ظ…ط© ط§ظ„ظ…ط®ط§ط²ظ†</h3>
                        <button class="btn-add" onclick="openWarehouseForm()">+ ط¥ط¶ط§ظپط© ظ…ط®ط²ظ† ط¬ط¯ظٹط¯</button>
                    </div>
                    
                    <div id="warehouse-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">ط¥ط¶ط§ظپط©/طھط¹ط¯ظٹظ„ ظ…ط®ط²ظ†</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-warehouse" onclick="cancelEditWarehouse()">ط¥ظ„ط؛ط§ط، ط§ظ„طھط¹ط¯ظٹظ„</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="warehouse-id">
                            <div class="input-group"><label>ط§ط³ظ… ط§ظ„ظ…ط®ط²ظ†</label><input type="text" id="warehouse-name" placeholder="ظ…ط«ط§ظ„: ظ…ط®ط²ظ† ظ…ظ‚ط§ظٹظٹط³ ط§ظ„ط±ط¦ظٹط³ظٹ"></div>
                            <div class="input-group"><label>ط§ظ„ظ†ظˆط¹</label><select id="warehouse-type"><option value="ظ…ط³طھظˆط¯ط¹ ط±ط¦ظٹط³ظٹ">ظ…ط³طھظˆط¯ط¹ ط±ط¦ظٹط³ظٹ</option><option value="ظ…ط¹ط±ط¶ (ظ†ظ‚ط·ط© ط¨ظٹط¹)">ظ…ط¹ط±ط¶ (ظ†ظ‚ط·ط© ط¨ظٹط¹)</option><option value="ظ…ط®ط²ظ† ط¨ط¶ط§ط¹ط© طھط§ظ„ظپط©">ظ…ط®ط²ظ† ط¨ط¶ط§ط¹ط© طھط§ظ„ظپط©</option></select></div>
                            <div class="input-group"><label>ط¯ط±ط¬ط© ط§ظ„ط³ط±ظٹط©</label><select id="warehouse-security"><option value="1">1 (ط§ظ„ط£ظ‚ظ„ ط³ط±ظٹط©)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (ط§ظ„ط£ط¹ظ„ظ‰ ط³ط±ظٹط©)</option></select></div>
                        </div>
                        <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveWarehouse()">ًں’¾ ط­ظپط¸ ط§ظ„ط¨ظٹط§ظ†ط§طھ</button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>ط§ط³ظ… ط§ظ„ظ…ط®ط²ظ†</th><th>ط§ظ„ظ†ظˆط¹</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                            <tbody id="warehouses-tbody"><!-- ط³ظٹطھظ… ط¥ط¶ط§ظپط© ط§ظ„ظ…ط®ط§ط²ظ† ظ‡ظ†ط§ --></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-customers" class="md-tab-content">
                <div class="md-card">
                    <div id="customer-form" class="md-card" style="display: block; margin-bottom: 25px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; margin-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 1.1rem; color: var(--primary-accent); display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 1.2rem;">ًں‘¥</span> ط¥ط¶ط§ظپط© / طھط¹ط¯ظٹظ„ ط¨ظٹط§ظ†ط§طھ ط¹ظ…ظٹظ„
                            </h4>
                            <button onclick="clearCustomerForm()" class="btn-action" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">ًں”„ طھظپط±ظٹط؛ ط§ظ„ط­ظ‚ظˆظ„ (ط¹ظ…ظٹظ„ ط¬ط¯ظٹط¯)</button>
                        </div>
                        <div class="form-grid">
                            <div class="input-group"><label>ط§ط³ظ… ط§ظ„ط¹ظ…ظٹظ„ / ط§ظ„ط´ط±ظƒط©</label><input type="text" id="cust-name" placeholder="ظ…ط«ط§ظ„: ط£ط­ظ…ط¯ ظ„ظ„طھط¬ط§ط±ط©"></div>
                            <div class="input-group"><label>ط±ظ‚ظ… ط§ظ„ظ‡ط§طھظپ</label><input type="text" id="cust-phone" placeholder="00249123000000"></div>
                            <div class="input-group"><label>ط±ظ‚ظ… ط§ظ„ظˆط§طھط³ط§ط¨</label><input type="text" id="cust-whatsapp" placeholder="00249123000000"></div>
                            <div class="input-group"><label>ط§ظ„ط§ظٹظ…ظٹظ„ (ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ)</label><input type="email" id="cust-email" placeholder="example@email.com"></div>
                            <div class="input-group" style="grid-column: span 2;"><label>ط§ظ„ط¹ظ†ظˆط§ظ†</label><input type="text" id="cust-address" placeholder="ظ…ط«ط§ظ„: ط§ظ„ط®ط±ط·ظˆظ…طŒ ظ…ظ‚ط§ظٹظٹط³"></div>
                            <div class="input-group"><label>ط§ظ„ط¹ظ…ظ„ط©</label><select id="cust-currency" class="system-currency-select" onchange="updateCustExchangeRate()"></select></div>
                            <div class="input-group" id="cust-ex-rate-group" style="display: none;"><label id="cust-ex-rate-label" style="color:#38bdf8; font-weight:800;">ًں’µ ط³ط¹ط± ط§ظ„طµط±ظپ (ظ…ظ‚ط§ط¨ظ„ ط§ظ„ظ…ط­ظ„ظٹط©)</label><input type="number" id="cust-ex-rate" step="any" value="1" placeholder="ط³ط¹ط± ط§ظ„طµط±ظپ ظ…ظ‚ط§ط¨ظ„ SDG" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group"><label>ط§ظ„ط±ظ‚ظ… ط§ظ„ط¶ط±ظٹط¨ظٹ</label><input type="text" id="cust-tax" placeholder="ط§ظ„ط±ظ‚ظ… ط§ظ„ط¶ط±ظٹط¨ظٹ ظ„ظ„ط¹ظ…ظٹظ„"></div>
                            <input type="hidden" id="cust-account-no" value="">
                            <div class="input-group">
                                <label>ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ <small style="color:#f97316; font-weight:normal;">(ظ…ظˆط¬ط¨ = ط¹ظ„ظٹظ‡ | ط³ط§ظ„ط¨ = ظ„ظ‡ (ظٹط±ظٹط¯ ظ…ظ†ط§))</small></label>
                                <input type="number" id="cust-opening-balance" step="any" placeholder="0.00" style="direction:ltr; text-align:center;">
                                <input type="hidden" id="cust-opening-debit" value="0">
                                <input type="hidden" id="cust-opening-credit" value="0">
                            </div>
                            <div class="input-group"><label style="color: var(--danger-color);">ط­ط¯ ط§ظ„ط§ط¦طھظ…ط§ظ† (ط£ظ‚طµظ‰ ط³ظ‚ظپ)</label><input type="number" id="cust-limit" placeholder="ط§ظ„ظ…ط¨ظ„ط؛ ط§ظ„ظ…ط³ظ…ظˆط­ ظ„ظ„ط¯ظٹظ†"></div>
                            <div class="input-group"><label>ط¯ط±ط¬ط© ط§ظ„ط³ط±ظٹط©</label><select id="cust-security"><option value="1">1 (ط§ظ„ط£ظ‚ظ„ ط³ط±ظٹط©)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (ط§ظ„ط£ط¹ظ„ظ‰ ط³ط±ظٹط©)</option></select></div>
                            <div class="input-group"><label>ط­ط§ظ„ط© ط§ظ„ط¹ظ…ظٹظ„ (ط§ظ„ظ‚ط§ط¦ظ…ط© ط§ظ„ط³ظˆط¯ط§ط،)</label><select id="cust-status" style="font-weight: bold;"><option value="active" style="color: var(--success-color);">ًںں¢ ظ†ط´ط· (ط¹ط§ط¯ظٹ)</option><option value="vip" style="color: var(--primary-accent);">ًںŒں VIP (ظ…ظ…ظٹط²)</option><option value="blacklisted" style="color: var(--danger-color);">ًں”´ ظ‚ط§ط¦ظ…ط© ط³ظˆط¯ط§ط، (ظ…ط­ط¸ظˆط±)</option></select></div>
                            <div class="input-group"><label>ظƒظˆط¯ ط§ظ„ط¹ظ…ظٹظ„</label><input type="text" id="cust-id" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;" placeholder="طھظ„ظ‚ط§ط¦ظٹ ط¹ظ†ط¯ ط§ظ„ط­ظپط¸"></div>
                            <div class="input-group"><label>ط§ظ„ط´ط®طµ ط§ظ„ظ…ط³ط¤ظˆظ„</label><input type="text" id="cust-contact" placeholder="ط§ط³ظ… ط§ظ„ظ…ظ†ط¯ظˆط¨ / ط§ظ„ط´ط®طµ ط§ظ„ظ…ط³ط¤ظˆظ„"></div>
                            <div class="input-group" style="grid-column: 1 / -1;"><label>ظ…ظ„ط§ط­ط¸ط§طھ ط¥ط¶ط§ظپظٹط©</label><textarea id="cust-notes" placeholder="ط£ط¶ظپ ط£ظٹ ظ…ظ„ط§ط­ط¸ط§طھ ط¹ظ† ط§ظ„ط¹ظ…ظٹظ„ ظ‡ظ†ط§..." style="width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-primary); outline: none; font-family: inherit; resize: none; height: 50px; box-sizing: border-box;"></textarea></div>

                            <!-- ط¨ظٹط§ظ†ط§طھ ط§ظ„ط­ط³ط§ط¨ ط§ظ„ط¨ظ†ظƒظٹ ظ„ظ„ط¹ظ…ظٹظ„ -->
                            <div style="grid-column: 1 / -1; margin-top: 10px; padding: 15px; border: 1px solid var(--glass-border); border-radius: 12px; background: rgba(0,0,0,0.1);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h4 style="margin: 0; color: var(--primary-accent); display:flex; align-items:center; gap:8px;"><span>ًںڈ¦</span> ط¨ظٹط§ظ†ط§طھ ط§ظ„ط­ط³ط§ط¨ط§طھ ط§ظ„ط¨ظ†ظƒظٹط© ظ„ظ„ط¹ظ…ظٹظ„</h4>
                                    <button type="button" onclick="addCustomerBankAccount()" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: bold;">+ ط¥ط¶ط§ظپط© ط­ط³ط§ط¨ ط¢ط®ط±</button>
                                </div>
                                <div id="cust-bank-accounts-container" style="display: flex; flex-direction: column; gap: 15px;">
                                    <!-- Bank accounts will be injected here -->
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn-add" style="background: var(--success-color); padding: 10px 25px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="saveCustomer()">ًں’¾ ط­ظپط¸ ط§ظ„ط¹ظ…ظٹظ„</button>
                        </div>
                    </div>

                    <!-- Customer Search Bar -->
                    <div style="margin-bottom: 15px; display: flex; align-items: center;">
                        <div class="input-group" style="width: 100%; max-width: 500px; position: relative; margin-bottom: 0;">
                            <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; pointer-events: none;">ًں”چ</span>
                            <input type="text" id="customer-search-input" onkeyup="filterCustomers()" placeholder="ط§ط¨ط­ط« ط°ظƒظٹط§ظ‹ ط¨ط§ط³ظ… ط§ظ„ط¹ظ…ظٹظ„طŒ ط±ظ‚ظ… ط§ظ„ظ‡ط§طھظپطŒ ط£ظˆ ط§ظ„ط¥ظٹظ…ظٹظ„..." style="padding-right: 45px; border-radius: 12px; border: 1px solid rgba(56,189,248,0.3); background: rgba(0,0,0,0.3); font-size: 1rem; color: #fff; width: 100%; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);">
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>ظƒظˆط¯</th><th>ط§ط³ظ… ط§ظ„ط¹ظ…ظٹظ„</th><th>ط§ظ„ظ‡ط§طھظپ</th><th>ط§ظ„ط­ط§ظ„ط©</th><th>ط§ظ„ط±طµظٹط¯ ط§ظ„ط­ط§ظ„ظٹ</th><th>ط­ط¯ ط§ظ„ط§ط¦طھظ…ط§ظ†</th><th>ط§ظ„ظ…ط³ط¤ظˆظ„</th><th>ط§ظ„ط³ط±ظٹط©</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                            <tbody id="customers-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Suppliers Tab -->
            <div id="tab-suppliers" class="md-tab-content">
                <div class="md-card">
                    <div id="supplier-form" class="md-card" style="display: block; margin-bottom: 25px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; margin-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 1.1rem; color: var(--primary-accent); display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 1.2rem;">ًںڈ­</span> ط¥ط¶ط§ظپط© / طھط¹ط¯ظٹظ„ ط¨ظٹط§ظ†ط§طھ ظ…ظˆط±ط¯
                            </h4>
                            <button onclick="clearSupplierForm()" class="btn-action" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">ًں”„ طھظپط±ظٹط؛ ط§ظ„ط­ظ‚ظˆظ„ (ظ…ظˆط±ط¯ ط¬ط¯ظٹط¯)</button>
                        </div>
                        <div class="form-grid">
                            <div class="input-group"><label>ط§ط³ظ… ط§ظ„ظ…ظˆط±ط¯ / ط§ظ„ظ…طµظ†ط¹</label><input type="text" id="sup-name" placeholder="ظ…ط«ط§ظ„: ظ…طµظ†ط¹ ط¬ظٹط§ط¯"></div>
                            <div class="input-group"><label>ط±ظ‚ظ… ط§ظ„ظ‡ط§طھظپ</label><input type="text" id="sup-phone" placeholder="00249123000000"></div>
                            <div class="input-group"><label>ط±ظ‚ظ… ط§ظ„ظˆط§طھط³ط§ط¨</label><input type="text" id="sup-whatsapp" placeholder="00249123000000"></div>
                            <div class="input-group"><label>ط§ظ„ط§ظٹظ…ظٹظ„ (ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ)</label><input type="email" id="sup-email" placeholder="example@email.com"></div>
                            <div class="input-group" style="grid-column: span 2;"><label>ط§ظ„ط¹ظ†ظˆط§ظ†</label><input type="text" id="sup-address" placeholder="ظ…ظˆظ‚ط¹ ط§ظ„ظ…طµظ†ط¹ / ط§ظ„ظ…ط¹ط±ط¶"></div>
                            <div class="input-group"><label>ط§ظ„ط¹ظ…ظ„ط©</label><select id="sup-currency" class="system-currency-select" onchange="updateSupExchangeRate()"></select></div>
                            <div class="input-group" id="sup-ex-rate-group" style="display: none;"><label id="sup-ex-rate-label" style="color:#38bdf8; font-weight:800;">ًں’µ ط³ط¹ط± ط§ظ„طµط±ظپ (ظ…ظ‚ط§ط¨ظ„ ط§ظ„ظ…ط­ظ„ظٹط©)</label><input type="number" id="sup-ex-rate" step="any" value="1" placeholder="ط³ط¹ط± ط§ظ„طµط±ظپ ظ…ظ‚ط§ط¨ظ„ SDG" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group"><label>ط§ظ„ط±ظ‚ظ… ط§ظ„ط¶ط±ظٹط¨ظٹ</label><input type="text" id="sup-tax" placeholder="ط§ظ„ط±ظ‚ظ… ط§ظ„ط¶ط±ظٹط¨ظٹ ظ„ظ„ظ…ظˆط±ط¯"></div>
                            <input type="hidden" id="sup-account-no" value="">
                            <div class="input-group">
                                <label>ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ <small style="color:#f97316; font-weight:normal;">(ظ…ظˆط¬ط¨ = ظ„ظ‡ | ط³ط§ظ„ط¨ = ط¹ظ„ظٹظ‡ (ظ†ط±ظٹط¯ ظ…ظ†ظ‡))</small></label>
                                <input type="number" id="sup-opening-balance" step="any" placeholder="0.00" style="direction:ltr; text-align:center;">
                                <input type="hidden" id="sup-opening-debit" value="0">
                                <input type="hidden" id="sup-opening-credit" value="0">
                            </div>
                            <div class="input-group"><label style="color: var(--danger-color);">ط­ط¯ ط§ظ„ط§ط¦طھظ…ط§ظ† (ط£ظ‚طµظ‰ ط³ظ‚ظپ)</label><input type="number" id="sup-limit" placeholder="ط£ظ‚طµظ‰ ط³ظ‚ظپ"></div>
                            <div class="input-group"><label>ط¯ط±ط¬ط© ط§ظ„ط³ط±ظٹط©</label><select id="sup-security"><option value="1">1 (ط§ظ„ط£ظ‚ظ„ ط³ط±ظٹط©)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (ط§ظ„ط£ط¹ظ„ظ‰ ط³ط±ظٹط©)</option></select></div>
                            <div class="input-group"><label>ط­ط§ظ„ط© ط§ظ„ظ…ظˆط±ط¯</label><select id="sup-status" style="font-weight: bold;"><option value="active" style="color: var(--success-color);">ًںں¢ ظ…ظˆط±ط¯ ظ†ط´ط·</option><option value="blacklisted" style="color: var(--danger-color);">ًں”´ ظ‚ط§ط¦ظ…ط© ط³ظˆط¯ط§ط،</option></select></div>
                            <div class="input-group"><label>ظƒظˆط¯ ط§ظ„ظ…ظˆط±ط¯</label><input type="text" id="sup-id" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;" placeholder="طھظ„ظ‚ط§ط¦ظٹ ط¹ظ†ط¯ ط§ظ„ط­ظپط¸"></div>
                            <div class="input-group" style="grid-column: 1 / -1;"><label>ظ…ظ„ط§ط­ط¸ط§طھ ط¥ط¶ط§ظپظٹط©</label><textarea id="sup-notes" placeholder="ط£ظٹ ظ…ظ„ط§ط­ط¸ط§طھ ط­ظˆظ„ ط§ظ„ظ…ظˆط±ط¯..." style="width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-primary); outline: none; font-family: inherit; resize: none; height: 50px; box-sizing: border-box;"></textarea></div>
                        </div>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn-add" style="background: var(--success-color); padding: 10px 25px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="saveSupplier()">ًں’¾ ط­ظپط¸ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظˆط±ط¯</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="tab-suppliers-table">
                            <thead><tr><th>ظƒظˆط¯</th><th>ط§ط³ظ… ط§ظ„ظ…ظˆط±ط¯</th><th>ط§ظ„ظ‡ط§طھظپ</th><th>ط§ظ„ط­ط§ظ„ط©</th><th>ط§ظ„ط±طµظٹط¯ ط§ظ„ط­ط§ظ„ظٹ</th><th>ط­ط¯ ط§ظ„ط§ط¦طھظ…ط§ظ†</th><th>ط§ظ„ط³ط±ظٹط©</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Banks Tab -->
            <div id="tab-banks" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header"><h3 class="md-card-title">ًںڈ¦ ط§ظ„ط¨ظ†ظˆظƒ</h3><button class="btn-add" onclick="openBankForm()">+ ط¥ط¶ط§ظپط© ط­ط³ط§ط¨ ط¨ظ†ظƒظٹ</button></div>
                    <div id="bank-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="bank-form-title">ط¥ط¶ط§ظپط© ط­ط³ط§ط¨ ط¨ظ†ظƒظٹ</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">âœ– ط¥ظ„ط؛ط§ط،</button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>ط§ظ„ط±ظ‚ظ…</label><input type="text" id="bank-code" placeholder="ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨ ط£ظˆ ط§ظ„ط®ط²ظٹظ†ط©"></div>
                            <div class="input-group"><label>ط§ظ„ط§ط³ظ…</label><input type="text" id="bank-name" placeholder="ظ…ط«ط§ظ„: ط§ظ„ط®ط²ظٹظ†ط© ط§ظ„ط±ط¦ظٹط³ظٹط©"></div>
                            <div class="input-group" id="bank-branch-group"><label>ط§ظ„ظپط±ط¹</label><input type="text" id="bank-branch" placeholder="ظ…ط«ط§ظ„: ظپط±ط¹ ظ…ظ‚ط§ظٹظٹط³"></div>
                            <div class="input-group"><label>ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ</label><input type="number" id="bank-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>طھط§ط±ظٹط® ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ</label><input type="date" id="bank-opening-date"></div>
                            <div class="input-group"><label>ط±ظ‚ظ… ط­ط³ط§ط¨ ط§ظ„ط£ط³طھط§ط° (GL)</label><input type="text" id="bank-gl-account" placeholder="ظ…ط«ط§ظ„: 110101"></div>
                            <div class="input-group bank-only-field"><label>IBAN</label><input type="text" id="bank-iban" placeholder="SA..."></div>
                            <div class="input-group bank-only-field"><label>SWIFT</label><input type="text" id="bank-swift" placeholder="BOKS..."></div>
                            
                            <div class="input-group"><label>ط§ظ„ط¹ظ…ظ„ط©</label><select id="bank-currency" class="system-currency-select" onchange="updateBankExchangeRate()"></select></div>
                            <div class="input-group" id="bank-ex-rate-group" style="display: none;"><label id="bank-ex-rate-label" style="color:#38bdf8; font-weight:800;">ًں’µ ط³ط¹ط± ط§ظ„طµط±ظپ (ظ…ظ‚ط§ط¨ظ„ ط§ظ„ظ…ط­ظ„ظٹط©)</label><input type="number" id="bank-ex-rate" step="any" value="1" placeholder="ط³ط¹ط± ط§ظ„طµط±ظپ ظ…ظ‚ط§ط¨ظ„ SDG" style="font-weight:800; color:#38bdf8;"></div>
                            
                            
                            <div class="input-group"><label>ط¯ط±ط¬ط© ط§ظ„ط³ط±ظٹط©</label><select id="bank-confidentiality"><option value="1">1 (ط§ظ„ط£ظ‚ظ„ ط³ط±ظٹط©)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (ط§ظ„ط£ط¹ظ„ظ‰ ط³ط±ظٹط©)</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">ط·ط±ظ‚ ط§ظ„ط¯ظپط¹ ط§ظ„ظ…ط³ظ…ظˆط­ط©</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="cash" checked> ظƒط§ط´</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="pos" checked> ط´ط¨ظƒط©</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="transfer" checked> طھط­ظˆظٹظ„</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="check"> ط´ظٹظƒط§طھ</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="bank-inactive"> ط¹ط¯ظ… طھظپط¹ظٹظ„ ط§ظ„ط­ط³ط§ط¨</label>
                            
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">âœ– ط¥ظ„ط؛ط§ط،</button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveBank()">ًں’¾ ط­ظپط¸</button></div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="banks-table">
                            <thead><tr><th>ط§ظ„ط±ظ‚ظ…</th><th>ط§ط³ظ… ط§ظ„ط­ط³ط§ط¨</th><th>ط§ظ„ظ†ظˆط¹</th><th>ط§ظ„ط¹ظ…ظ„ط©</th><th>ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                            <tbody id="banks-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            

<div id="tab-safes" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header"><h3 class="md-card-title">ًںڈ¦ ط§ظ„ط®ط²ظ†</h3><button class="btn-add" onclick="openSafeForm()">+ ط¥ط¶ط§ظپط© ط®ط²ظٹظ†ط©</button></div>
                    <div id="safe-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="safe-form-title">ط¥ط¶ط§ظپط© ط®ط²ظٹظ†ط©</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">âœ– ط¥ظ„ط؛ط§ط،</button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>ط§ظ„ط±ظ‚ظ…</label><input type="text" id="safe-code" placeholder="ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨ ط£ظˆ ط§ظ„ط®ط²ظٹظ†ط©"></div>
                            <div class="input-group"><label>ط§ظ„ط§ط³ظ…</label><input type="text" id="safe-name" placeholder="ظ…ط«ط§ظ„: ط§ظ„ط®ط²ظٹظ†ط© ط§ظ„ط±ط¦ظٹط³ظٹط©"></div>
                            
                            <div class="input-group"><label>ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ</label><input type="number" id="safe-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>طھط§ط±ظٹط® ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ</label><input type="date" id="safe-opening-date"></div>
                            <div class="input-group"><label>ط±ظ‚ظ… ط­ط³ط§ط¨ ط§ظ„ط£ط³طھط§ط° (GL)</label><input type="text" id="safe-gl-account" placeholder="ظ…ط«ط§ظ„: 110101"></div>
                            
                            
                            <div class="input-group" id="safe-responsible-group"><label>ط§ظ„ظ…ط³ط¤ظˆظ„</label><select id="safe-responsible"><option value="">-- ط§ط®طھط± --</option></select></div>
                            <div class="input-group"><label>ط§ظ„ط¹ظ…ظ„ط©</label><select id="safe-currency" class="system-currency-select" onchange="updateSafeExchangeRate()"></select></div>
                            <div class="input-group" id="safe-ex-rate-group" style="display: none;"><label id="safe-ex-rate-label" style="color:#38bdf8; font-weight:800;">ًں’µ ط³ط¹ط± ط§ظ„طµط±ظپ (ظ…ظ‚ط§ط¨ظ„ ط§ظ„ظ…ط­ظ„ظٹط©)</label><input type="number" id="safe-ex-rate" step="any" value="1" placeholder="ط³ط¹ط± ط§ظ„طµط±ظپ ظ…ظ‚ط§ط¨ظ„ SDG" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group" id="safe-max-limit-group"><label>ط§ظ„ط­ط¯ ط§ظ„ط£ط¹ظ„ظ‰</label><input type="number" id="safe-max-limit" placeholder="0.00"></div>
                            <div class="input-group" id="safe-min-limit-group"><label>ط§ظ„ط­ط¯ ط§ظ„ط£ط¯ظ†ظ‰</label><input type="number" id="safe-min-limit" placeholder="0.00"></div>
                            <div class="input-group"><label>ط¯ط±ط¬ط© ط§ظ„ط³ط±ظٹط©</label><select id="safe-confidentiality"><option value="1">1 (ط§ظ„ط£ظ‚ظ„ ط³ط±ظٹط©)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (ط§ظ„ط£ط¹ظ„ظ‰ ط³ط±ظٹط©)</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">ط·ط±ظ‚ ط§ظ„ط¯ظپط¹ ط§ظ„ظ…ط³ظ…ظˆط­ط©</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="cash" checked> ظƒط§ط´</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="pos" checked> ط´ط¨ظƒط©</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="transfer" checked> طھط­ظˆظٹظ„</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="check"> ط´ظٹظƒط§طھ</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-inactive"> ط¹ط¯ظ… طھظپط¹ظٹظ„ ط§ظ„ط­ط³ط§ط¨</label>
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-modifier"> ط§ظ„ظ…ط¹ط¯ظ„</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">âœ– ط¥ظ„ط؛ط§ط،</button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveSafe()">ًں’¾ ط­ظپط¸</button></div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="safes-table">
                            <thead><tr><th>ط§ظ„ط±ظ‚ظ…</th><th>ط§ط³ظ… ط§ظ„ط®ط²ظٹظ†ط©</th><th>ط§ظ„ظ†ظˆط¹</th><th>ط§ظ„ط¹ظ…ظ„ط©</th><th>ط§ظ„ط±طµظٹط¯ ط§ظ„ط§ظپطھطھط§ط­ظٹ</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                            <tbody id="safes-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Items Tab -->
            <div id="tab-items" class="md-tab-content">
                <div class="md-card">
                    <!-- Header and redundant KPIs removed to utilize vertical space -->
                    <!-- Quick Actions moved to the item form -->

                    <!-- Main Inner Tabs removed as requested -->

                    <div id="inner-products" class="inner-tab-content active">
                        
                        <!-- Premium Item Form Modal -->
                        <div id="item-form" class="md-card" style="display: none; padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                            <!-- HEADER BAR -->
                            <div style="background:linear-gradient(135deg,rgba(56,189,248,0.2),rgba(15,23,42,0.95)); padding:12px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; border-bottom:1px solid rgba(56,189,248,0.3);">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <div style="width:50px; height:50px; background:linear-gradient(135deg, #38bdf8, #818cf8); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; box-shadow: 0 4px 15px rgba(56,189,248,0.4);">ًں“¦</div>
                                    <div><h3 id="form-mode-indicator" style="margin:0; color:#fff; font-size:1.4rem; font-weight:800; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">ط¥ط¶ط§ظپط© طµظ†ظپ ط¬ط¯ظٹط¯</h3><small style="color:#cbd5e1; font-size: 0.85rem;">ط¥ط¯ط§ط±ط© ط¨ط·ط§ظ‚ط© ط§ظ„طµظ†ظپ ظˆطھظپط§طµظٹظ„ ط§ظ„طھط³ط¹ظٹط± ظˆط§ظ„ظˆط­ط¯ط§طھ</small></div>
                                </div>
                                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items: center;">
                                    <!-- Low Stock Mini -->
                                    <div class="neon-glass-panel neon-border-danger" style="padding: 5px 12px; display: flex; align-items: center; gap: 8px; border-radius: 8px; border: 1px solid rgba(244,63,94,0.3); margin-left: 10px;">
                                        <span style="font-size:1rem; background: rgba(244,63,94,0.1); width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">âڑ ï¸ڈ</span>
                                        <div>
                                            <span style="color:var(--text-secondary); font-size:0.7rem; display: block; line-height: 1; margin-bottom: 3px;">ط§ظ„ظ†ظˆط§ظ‚طµ</span>
                                            <strong id="kpi-low-stock" class="neon-text-danger" style="font-size:0.95rem; font-weight:800; line-height: 1;">0 طµظ†ظپ</strong>
                                        </div>
                                    </div>
                                    
                                    <button onclick="openInnerTab(event, 'inner-categories')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(88,28,135,0.6)); border: 1px solid rgba(168,85,247,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">ًں—‚ï¸ڈ</span> ط£ظ†ظˆط§ط¹ ط§ظ„ط£طµظ†ط§ظپ
                                    </button>
                                    <button onclick="openInnerTab(event, 'inner-units')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">ًں“گ</span> ط§ظ„ظˆط­ط¯ط§طھ
                                    </button>
                                    <button onclick="alert('ط¬ط§ط±ظٹ ط§ظ„طھط¬ظ‡ظٹط² ظ„ظ„ط·ط¨ط§ط¹ط©...')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(52,211,153,0.2), rgba(6,78,59,0.6)); border: 1px solid rgba(52,211,153,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">ًں–¨ï¸ڈ</span> ط·ط¨ط§ط¹ط© ط¨ط§ط±ظƒظˆط¯
                                    </button>
                                    <button class="btn-add hover-glow" style="background:linear-gradient(135deg, var(--success-color), #059669); padding:10px 25px; font-size: 1rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.3); border: 1px solid rgba(16,185,129,0.5); color: #fff; cursor: pointer;" onclick="saveItem()">ًں’¾ ط­ظپط¸ ط§ظ„طµظ†ظپ</button>
                                    <button onclick="closeItemForm()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.5); padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:bold; transition: all 0.3s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">âœ– ط¥ظ„ط؛ط§ط،</button>
                                </div>
                            </div>
                            
                            <div style="padding:25px; background: rgba(0,0,0,0.2);">

                                <div class="split-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
                                    
                                    <!-- SEC 1: General -->
                                    <div class="neon-glass-panel split-panel" style="padding: 20px; border-top: 3px solid #38bdf8; background: linear-gradient(to bottom, rgba(56,189,248,0.05), transparent);">
                                        <div style="display:flex; align-items:center; gap:10px; margin-bottom: 20px;">
                                            <span style="font-size:1.5rem;">ًں“‹</span><h4 style="margin:0; color:#38bdf8; font-weight: 800;">ط§ظ„ط¨ظٹط§ظ†ط§طھ ط§ظ„ط¹ط§ظ…ط© ظˆط§ظ„طھط¹ط±ظٹظپ</h4>
                                        </div>
                                        
                                        <div style="display:flex; gap:20px;">
                                            <div id="item-qr-box" style="width:100px; height:100px; border:2px dashed rgba(56,189,248,0.4); border-radius:14px; display:flex; flex-direction:column; justify-content:center; align-items:center; background:rgba(0,0,0,0.4); flex-shrink:0; transition: all 0.3s;"><span style="font-size:2rem;">ًں“±</span><span style="font-size:0.75rem; color:#38bdf8; margin-top:5px; font-weight:bold;">QR طھظ„ظ‚ط§ط¦ظٹ</span></div>
                                            <div class="form-grid" style="flex:1; grid-template-columns: 1fr;">
                                                <div class="input-group"><label style="color:#fff;">ط§ط³ظ… ط§ظ„طµظ†ظپ <span style="color:#ef4444;">*</span></label><input type="text" id="item-name-input" placeholder="ظ…ط«ط§ظ„: ط³ظٹط® 3 ظ„ظ†ظٹظ‡ ظ…ط§ط³طھط±" style="border-color: rgba(56,189,248,0.5); background: rgba(0,0,0,0.3);"></div>
                                                <div style="display:flex; gap:10px;">
                                                    <div class="input-group" style="flex:1;"><label>ظƒظˆط¯ ط§ظ„طµظ†ظپ (SKU)</label><input type="text" id="item-code" placeholder="طھظ„ظ‚ط§ط¦ظٹ ط¥ط°ط§ طھظڈط±ظƒ ظپط§ط±ط؛ط§ظ‹" style="color:#38bdf8; font-weight:bold;"></div>
                                                    <div class="input-group" style="flex:1;"><label>ط§ظ„ط§ط³ظ… ط§ظ„ظ„ط§طھظٹظ†ظٹ</label><input type="text" id="item-name-latin" placeholder="Rebar 3 Linia"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-grid" style="margin-top: 15px;">
                                            <div class="input-group"><label>ط§ظ„ط¨ط§ط±ظƒظˆط¯</label><input type="text" id="item-barcode" placeholder="ط§ظ…ط³ط­ ط¨ط§ظ„ظ„ظٹط²ط±..."></div>
                                            <div class="input-group"><label>ط§ظ„ظ…ط§ط±ظƒط© / ط§ظ„ظ…ظ†ط´ط£</label><input type="text" id="item-brand" placeholder="ظ…ط«ط§ظ„: طھط±ظƒظٹطŒ ط¬ظٹط§ط¯"></div>
                                            <div class="input-group"><label>ط§ظ„ظپط¦ط© ط§ظ„ط±ط¦ظٹط³ظٹط©</label><div style="display:flex; gap:5px;"><select id="item-category" style="flex:1;"></select><button class="btn-text" style="color:#a855f7; background:rgba(168,85,247,0.1); border-radius:8px; padding:0 10px;" onclick="quickAddCategory()" title="ط¥ط¶ط§ظپط© ظپط¦ط©">â‍•</button></div></div>
                                            <div class="input-group" style="grid-column: 1 / -1;">
                                                <label>ط§ظ„ظˆط­ط¯ط§طھ ط§ظ„ط£ط³ط§ط³ظٹط© <span style="color:#ef4444;">*</span></label>
                                                <input type="text" id="item-primary-base-unit" oninput="updateBaseUnitRow()" placeholder="ظ…ط«ط§ظ„: ط­ط¨ط©طŒ ظƒط±طھظˆظ†">
                                            </div>
                                            <div class="input-group" style="grid-column: 1 / -1;">
                                                <label>ط§ظ„ظˆط­ط¯ط§طھ</label>
                                                <div style="display:flex; gap:15px; align-items:center; background: rgba(0,0,0,0.15); padding: 10px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                                                    <input type="text" id="item-base-unit" placeholder="ظ…ط«ط§ظ„: 1 طŒ ط­ط¨ط©" style="flex:4; font-size: 1.35rem !important;">
                                                    <span style="color:#a855f7; font-weight:bold; font-size: 1.1rem; white-space: nowrap;">+ ط´ظٹظ„ط©</span>
                                                    <input type="number" id="item-bundle-factor" placeholder="ط§ظ„ظ…ط¹ط§ظ…ظ„" value="1" min="1" style="flex:1;">
                                                </div>
                                            </div>
                                            <div class="input-group"><label>ط·ط¨ظٹط¹ط© ط§ظ„طµظ†ظپ</label><select id="item-nature"><option value="ظ…ط®ط²ظ†ظٹ">ًں“¦ ظ…ط®ط²ظ†ظٹ (ظٹطھظ… ط¬ط±ط¯ظ‡)</option><option value="ط®ط¯ظ…ظٹ">ًں”§ ط®ط¯ظ…ظٹ (ظ„ط§ ظٹط¬ط±ط¯)</option><option value="طھط¬ظ…ظٹط¹ظٹ">ًںڈ—ï¸ڈ طھط¬ظ…ظٹط¹ظٹ</option></select></div>
                                            <div class="input-group"><label>ط­ط§ظ„ط© ط§ظ„طھظپط¹ظٹظ„</label><div style="display:flex; align-items:center; gap:10px; padding:10px; background: rgba(0,0,0,0.2); border-radius: 8px;"><label class="toggle-sw"><input type="checkbox" id="item-active-toggle" checked onchange="updateToggleLabel()"><span class="toggle-sl"></span></label><span id="item-active-label" style="color:var(--success-color); font-weight:bold; font-size:0.9rem;">ًںں¢ ظ†ط´ط·</span></div></div>
                                        </div>
                                    </div>

                                    <!-- Right Column Stack -->
                                    <div class="split-panel" style="display: flex; flex-direction: column; gap: 20px;">
                                        
                                        <!-- SEC 2: Pricing & Tax -->
                                        <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #34d399; background: linear-gradient(to bottom, rgba(52,211,153,0.05), transparent);">
                                            <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                <span style="font-size:1.5rem;">ًں’°</span><h4 style="margin:0; color:#34d399; font-weight: 800;">ط§ظ„طھط³ط¹ظٹط± ظˆط§ظ„ط¶ط±ط§ط¦ط¨</h4>
                                            </div>
                                            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                                                <div class="input-group"><label>ط³ط¹ط± ط§ظ„ط¨ظٹط¹ ط§ظ„ط§ظپطھط±ط§ط¶ظٹ (SDG)</label><input type="number" id="item-selling-price" placeholder="0.00" style="font-weight:bold; color:var(--success-color); border-color: rgba(52,211,153,0.4); font-size: 1.1rem; background: rgba(52,211,153,0.05);"></div>
                                                <div class="input-group"><label>ط§ظ„ط­ط¯ ط§ظ„ط£ظ‚طµظ‰ ظ„ظ„ط®طµظ… %</label><input type="number" id="item-max-discount" placeholder="0" max="100"></div>
                                                <div class="input-group"><label>ط®ط§ط¶ط¹ ظ„ظ„ط¶ط±ظٹط¨ط©</label><div style="padding:10px; background:rgba(0,0,0,0.2); border-radius:8px;"><label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0;"><input type="checkbox" id="item-taxable" onchange="document.getElementById('item-tax-rate').disabled=!this.checked" style="width:18px; height:18px; accent-color:#34d399;"> ظ†ط¹ظ…طŒ ط®ط§ط¶ط¹</label></div></div>
                                                <div class="input-group"><label>ظ†ط³ط¨ط© ط§ظ„ط¶ط±ظٹط¨ط© %</label><input type="number" id="item-tax-rate" placeholder="0" disabled></div>
                                            </div>
                                        </div>

                                        <!-- SEC 3 & 4: Costing & Warehousing in one row inside column -->
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                            <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #f59e0b; background: linear-gradient(to bottom, rgba(245,158,11,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                    <span style="font-size:1.2rem;">ًں“ٹ</span><h5 style="margin:0; color:#f59e0b; font-weight: 800;">ط§ظ„طھظƒظ„ظپط© ظˆط§ظ„ط£ط±طµط¯ط©</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px; grid-column: 1 / -1;"><label style="color:#f59e0b;">ط·ط±ظٹظ‚ط© ط§ظ„طھظƒظ„ظپط©</label>
                                                    <div style="display:flex; gap:10px; flex-wrap:wrap; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px;">
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="lastPurchase" checked style="accent-color:#f59e0b;"> ط¢ط®ط± ط´ط±ط§ط،</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="highest" style="accent-color:#f59e0b;"> ط§ظ„ط£ط¹ظ„ظ‰</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="opening" style="accent-color:#f59e0b;"> ط§ظ„ط§ظپطھطھط§ط­ظٹ</label>
                                                    </div>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>ط§ظ„ظƒظ…ظٹط© ط§ظ„ط§ظپطھطھط§ط­ظٹط©</label><input type="number" id="item-opening-qty" placeholder="0"></div>
                                                <div class="input-group"><label>طھظƒظ„ظپط© ط§ظ„ط§ظپطھطھط§ط­ظٹ</label><input type="number" id="item-opening-value" placeholder="0.00"></div>
                                            </div>

                                            <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #ef4444; background: linear-gradient(to bottom, rgba(239,68,68,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                    <span style="font-size:1.2rem;">ًںڈ­</span><h5 style="margin:0; color:#ef4444; font-weight: 800;">ط­ط¯ظˆط¯ ط§ظ„ظ…ط®ط²ظˆظ†</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>ظ…ظƒط§ظ† ط§ظ„طµظ†ظپ / ط§ظ„ط±ظپ</label><input type="text" id="item-bin-location" placeholder="ظ…ظ…ط± 3طŒ ط±ظپ ط¨"></div>
                                                <div style="display:flex; gap:10px; margin-bottom: 10px;">
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">ط§ظ„ط­ط¯ ط§ظ„ط£ط¹ظ„ظ‰</label><input type="number" id="item-max-limit" placeholder="500"></div>
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">ط§ظ„ط­ط¯ ط§ظ„ط£ط¯ظ†ظ‰</label><input type="number" id="item-min-limit" placeholder="20"></div>
                                                </div>
                                                <div class="input-group"><label style="color:#f59e0b;">ط­ط¯ ط¥ط¹ط§ط¯ط© ط§ظ„ط·ظ„ط¨</label><input type="number" id="item-reorder-level" placeholder="50"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- SEC 5: Unit Matrix (Full Width) -->
                                <div class="neon-glass-panel" style="margin-top: 20px; padding: 25px; border-top: 3px solid #a855f7; background: linear-gradient(to bottom, rgba(168,85,247,0.05), transparent); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span style="font-size:1.8rem; filter: drop-shadow(0 0 8px rgba(168,85,247,0.5));">ًں“گ</span><h4 style="margin:0; color:#a855f7; font-weight: 800; font-size: 1.3rem;">ظ…طµظپظˆظپط© ط§ظ„ظˆط­ط¯ط§طھ ظˆط§ظ„طھط­ظˆظٹظ„</h4>
                                        </div>
                                    </div>
                                    
                                    <div style="background:rgba(15,23,42,0.8); border-radius:14px; border:1px solid rgba(168,85,247,0.2); overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(168,85,247,0.05);">
                                            <span style="color:#cbd5e1; font-weight: 600;">ط¬ط¯ظˆظ„ طھط­ظˆظٹظ„ ط§ظ„ظˆط­ط¯ط§طھ ظˆطھط³ط¹ظٹط±ظ‡ط§ (ط§ظ„ظ…ط¹ط§ط¯ظ„ ط¨ط§ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط©)</span>
                                            <button class="btn-text" style="color:#fff; font-weight:bold; background:linear-gradient(135deg, #a855f7, #9333ea); padding:8px 15px; border-radius:8px; box-shadow: 0 4px 10px rgba(168,85,247,0.3); border: none; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" onclick="addUnitRow()"><span style="font-size: 1.2rem;">+</span> ط¥ط¶ط§ظپط© ظˆط­ط¯ط© ط¬ط¯ظٹط¯ط©</button>
                                        </div>
                                        <div class="table-responsive" style="overflow-x: auto;">
                                            <table class="pro-table" style="width: 100%; min-width: 1000px;">
                                                <thead><tr style="background: rgba(0,0,0,0.2);">
                                                    <th style="padding: 15px; width: 40px; text-align: center;">ظ…</th>
                                                    <th style="padding: 15px;">ط§ط³ظ… ط§ظ„ظˆط­ط¯ط©</th>
                                                    <th style="padding: 15px; text-align: center;">ط§ظ„ظ…ط¹ط§ط¯ظ„ (Factor)</th>
                                                    <th style="padding: 15px;">ط§ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط©</th>
                                                    <th style="padding: 15px;">ط¨ط§ط±ظƒظˆط¯</th>
                                                    <th style="padding: 15px; color: #34d399;">ط³ط¹ط± ظ†ظ‚ط¯ظٹ</th>
                                                    <th style="padding: 15px; color: #60a5fa;">ط³ط¹ط± ط¢ط¬ظ„</th>
                                                    <th style="padding: 15px; color: #f87171;">ط£ظ‚ظ„ ط³ط¹ط±</th>
                                                    <th style="padding: 15px;">ظˆط²ظ† (Kg)</th>
                                                    <th style="padding: 15px; text-align: center;">ط¥ط¬ط±ط§ط،</th>
                                                </tr></thead>
                                                <tbody id="units-matrix-body">
                                                    <tr style="background:rgba(52,211,153,0.05); border-right:4px solid var(--success-color); transition: background 0.3s;" onmouseover="this.style.background='rgba(52,211,153,0.1)'" onmouseout="this.style.background='rgba(52,211,153,0.05)'">
                                                        <td style="text-align: center; font-weight: bold; color: var(--text-secondary);">1</td>
                                                        <td><input type="text" id="base-unit-name-display" value="" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; font-size: 1.1rem; outline: none;"></td>
                                                        <td><input type="number" value="1" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; font-size:1.2rem; outline: none;"></td>
                                                        <td><input type="text" placeholder="ط¨ط§ط±ظƒظˆط¯ ط§ظ„ظˆط­ط¯ط©..." style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:10px; border-radius:8px; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#34d399; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#34d399'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#60a5fa; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#60a5fa'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#f87171; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#f87171'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:10px; border-radius:8px; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td style="text-align: center;"><span style="font-size:0.8rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); border: 1px solid rgba(52,211,153,0.4); padding:6px 12px; border-radius:6px; display: inline-block; box-shadow: 0 2px 5px rgba(52,211,153,0.2);">ظˆط­ط¯ط© ط£ط³ط§ط³ظٹط©</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEC 6: Description -->
                                <div class="neon-glass-panel" style="margin-top: 20px; padding: 20px; border-top: 3px solid #94a3b8; background: linear-gradient(to bottom, rgba(148,163,184,0.05), transparent);">
                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                        <span style="font-size:1.2rem;">ًں“‌</span><h5 style="margin:0; color:#94a3b8; font-weight: 800;">ط§ظ„ظˆطµظپ ظˆط§ظ„ظ…ظ„ط§ط­ط¸ط§طھ</h5>
                                    </div>
                                    <textarea id="item-description" placeholder="ط£ط¶ظپ ظˆطµظپط§ظ‹ ط£ظˆ ظ…ظ„ط§ط­ط¸ط§طھ ط¯ط§ط®ظ„ظٹط© ط¹ظ† ط§ظ„طµظ†ظپ..." style="width:100%; min-height:100px; padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:#fff; font-family:inherit; resize:vertical; box-sizing:border-box; transition: border-color 0.3s; font-size: 0.95rem; outline: none;" onfocus="this.style.borderColor='#94a3b8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- List Data Table -->
                        <div class="md-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px; background:rgba(0,0,0,0.3); padding:8px 15px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); flex:1; max-width:400px;">
                                    <span>ًں”چ</span>
                                    <input type="text" id="item-search" onkeyup="filterItemsTable()" placeholder="ط§ط¨ط­ط« ط¨ط§ظ„ط§ط³ظ… ط£ظˆ ط§ظ„ظƒظˆط¯..." style="background:transparent; border:none; color:#fff; width:100%; outline:none;">
                                </div>
                            </div>
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="pro-table" id="main-items-table">
                                    <thead><tr><th>ط§ظ„ظƒظˆط¯</th><th>ط§ظ„طµظ†ظپ</th><th>ط§ظ„ظپط¦ط©</th><th>ط§ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط©</th><th>ط³ط¹ط± ط§ظ„ط¨ظٹط¹</th><th>ط§ظ„ط­ط§ظ„ط©</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                                    <tbody id="items-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div id="inner-categories" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-purple" style="margin-bottom:20px;">
                            <h4 style="margin-top:0; color:#a855f7;">ط¥ط¶ط§ظپط©/طھط¹ط¯ظٹظ„ ظپط¦ط©</h4>
                            <div class="form-grid">
                                <div class="input-group"><label>ط§ط³ظ… ط§ظ„ظپط¦ط©</label><input type="text" id="cat-name-input" placeholder="ظ…ط«ط§ظ„: ط£ط³ظ…ظ†طھ"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:var(--success-color); padding:10px 20px; height:fit-content;" onclick="saveCategory()">ًں’¾ ط­ظپط¸ ط§ظ„ظپط¦ط©</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th>ط§ط³ظ… ط§ظ„ظپط¦ط©</th><th>ط¹ط¯ط¯ ط§ظ„ط£طµظ†ط§ظپ</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                                <tbody id="categories-table-body">
                                    <!-- Dynamic categories -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Units Tab -->
                    <div id="inner-units" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-info" style="margin-bottom:20px; border-color: rgba(56,189,248,0.3);">
                            <h4 style="margin-top:0; color:#38bdf8;">ط¥ط¶ط§ظپط©/طھط¹ط¯ظٹظ„ ظˆط­ط¯ط©</h4>
                            <div class="form-grid">
                                <div class="input-group"><label>ط§ط³ظ… ط§ظ„ظˆط­ط¯ط©</label><input type="text" id="unit-name-input" placeholder="ظ…ط«ط§ظ„: ط¯ط²ظٹظ†ط©طŒ طµظ†ط¯ظˆظ‚طŒ ط­ط¨ط©"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); padding:10px 20px; height:fit-content;" onclick="saveGlobalUnit()">ًں’¾ ط­ظپط¸ ط§ظ„ظˆط­ط¯ط©</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th>ط§ط³ظ… ط§ظ„ظˆط­ط¯ط©</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                                <tbody id="units-table-body">
                                    <!-- Dynamic units -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees Tab -->
            <div id="tab-employees" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header"><h3 class="md-card-title">ًں‘¥ ط§ظ„ظ…ظˆط¸ظپظٹظ† ظˆط§ظ„طµظ„ط§ط­ظٹط§طھ</h3><button class="btn-add" onclick="openNewEmployeeForm()">+ ط¥ط¶ط§ظپط© ظ…ظˆط¸ظپ ط¬ط¯ظٹط¯</button></div>
                    <div id="employee-form" class="md-card" style="display: none;">
                        <h4 style="margin-top:0; color:var(--primary-accent);">ط¥ط¶ط§ظپط©/طھط¹ط¯ظٹظ„ ظ…ظˆط¸ظپ</h4>
                        <div class="form-grid">
                            <div class="input-group"><label>ط§ظ„ظƒظˆط¯</label><input type="text" id="emp-id" disabled></div>
                            <div class="input-group"><label>ط§ظ„ط§ط³ظ…</label><input type="text" id="emp-name"></div>
                            <div class="input-group"><label>ط±ظ‚ظ… ط§ظ„ظ‡ط§طھظپ</label><input type="text" id="emp-phone"></div>
                            <div class="input-group"><label>ظƒظ„ظ…ط© ط§ظ„ظ…ط±ظˆط±</label><input type="text" id="emp-password"></div>
                            <div class="input-group"><label>ط§ظ„ط¯ظˆط±</label><select id="emp-role"><option value="admin">ظ…ط¯ظٹط±</option><option value="cashier">ظƒط§ط´ظٹط±</option></select></div>
                            <div class="input-group"><label>ط¯ط±ط¬ط© ط§ظ„ط³ط±ظٹط©</label><select id="emp-security"><option value="1">1 (ط§ظ„ط£ظ‚ظ„ ط³ط±ظٹط©)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (ط§ظ„ط£ط¹ظ„ظ‰ ط³ط±ظٹط©)</option></select></div>
                            <div class="input-group"><label>ط§ظ„ط­ط§ظ„ط©</label><select id="emp-status"><option value="active">ظ†ط´ط·</option><option value="inactive">ظ…ظˆظ‚ظˆظپ</option></select></div>
                        </div>
                        <button type="button" class="btn-add" style="background:var(--success-color);" onclick="saveEmployee()">ًں’¾ ط­ظپط¸</button>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>ط§ظ„ط§ط³ظ…</th><th>ط§ظ„ط¯ظˆط±</th><th>ط§ظ„ظ‡ط§طھظپ</th><th>ط§ظ„ط³ط±ظٹط©</th><th>ط§ظ„ط­ط§ظ„ط©</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                            <tbody id="tab-employees-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-costcenters" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header">
                        <h3 class="md-card-title">ًں“ٹ ظ‚ط§ط¦ظ…ط© ظ…ط±ط§ظƒط² ط§ظ„طھظƒظ„ظپط©</h3>
                        <button class="btn-add" onclick="openCostCenterForm()">+ ط¥ط¶ط§ظپط© ظ…ط±ظƒط² طھظƒظ„ظپط© ط¬ط¯ظٹط¯</button>
                    </div>
                    
                    <div id="costcenter-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">ط¥ط¶ط§ظپط©/طھط¹ط¯ظٹظ„ ظ…ط±ظƒط² طھظƒظ„ظپط©</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-costcenter" onclick="closeCostCenterForm()">ط¥ظ„ط؛ط§ط، ط§ظ„طھط¹ط¯ظٹظ„</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="costcenter-id">
                            <div class="input-group"><label>ط±ظ…ط²/ظƒظˆط¯ ط§ظ„ظ…ط±ظƒط²</label><input type="text" id="costcenter-code" placeholder="طھظ„ظ‚ط§ط¦ظٹ ط¹ظ†ط¯ ط§ظ„ط­ظپط¸" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;"></div>
                            <div class="input-group"><label>ط§ط³ظ… ظ…ط±ظƒط² ط§ظ„طھظƒظ„ظپط©</label><input type="text" id="costcenter-name" placeholder="ظ…ط«ط§ظ„: ظ…ط´ط±ظˆط¹ ط§ظ„ط®ط±ط·ظˆظ… ط§ظ„ط¬ط¯ظٹط¯"></div>
                            <div class="input-group"><label>ط§ظ„ظˆطµظپ / ظ…ظ„ط§ط­ط¸ط§طھ</label><input type="text" id="costcenter-desc" placeholder="..."></div>
                        </div>
                        <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveCostCenter()">ًں’¾ ط­ظپط¸ ط§ظ„ط¨ظٹط§ظ†ط§طھ</button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>ظƒظˆط¯ ط§ظ„ظ…ط±ظƒط²</th><th>ط§ظ„ط§ط³ظ…</th><th>ط§ظ„ظˆطµظپ</th><th>ط§ظ„ط¥ط¬ط±ط§ط،</th></tr></thead>
                            <tbody id="costcenters-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            

        </main>
    </div>

    <!-- Scripts -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260803"></script>
    <script>
        function renderAllMasterData() {
            renderCustomers();
            renderSuppliers();
            renderEmployees();
            if(typeof renderBanks === 'function') renderBanks();
            if(typeof renderSafes === 'function') renderSafes();
            if(typeof renderItems === 'function') renderItems();
            renderWarehouses();
            if(typeof renderCostCenters === 'function') renderCostCenters();
        }

        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) window.location.href = 'login.php';
            if (window.location.hash) {
                const tabId = window.location.hash.substring(1);
                const tabBtn = document.querySelector(`button[onclick*="${tabId}"]`);
                if (tabBtn) tabBtn.click();
            }
            clearCustomerForm();
            clearSupplierForm();
            renderAllMasterData();
        });

        // Re-render when cloud sync completes (essential for iOS Safari first load/fresh sessions)
        window.addEventListener('cloudDataRestored', () => {
            console.log("[Master Data] Cloud sync completed. Re-rendering lists...");
            renderAllMasterData();
            if (typeof deduplicateEntities === 'function') deduplicateEntities();
            if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
            if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
        });
        window.addEventListener('strictDataReady', () => {
            console.log("[Master Data] Strict data ready. Re-rendering lists...");
            renderAllMasterData();
            if (typeof deduplicateEntities === 'function') deduplicateEntities();
            if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
            if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
        });

        function openTab(evt, tabId) {
            document.querySelectorAll('.md-tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.md-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
            
        }

        function openInnerTab(evt, tabId) {
            const container = evt.currentTarget.closest('.md-card');
            container.querySelectorAll('.inner-tab-content').forEach(c => c.classList.remove('active'));
            container.querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        function switchSupplierTab(event, tabId) {
            const form = document.getElementById('supplier-form');
            form.querySelectorAll('.inner-tab-btn').forEach(btn => btn.classList.remove('active'));
            form.querySelectorAll('.inner-tab-content').forEach(tab => tab.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        // ================= WAREHOUSES LOGIC =================
        function saveWarehouse() {
            const name = document.getElementById('warehouse-name').value;
            if(!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„ظ…ط®ط²ظ†'); return; }
            
            const type = document.getElementById('warehouse-type').value;
            const security = parseInt(document.getElementById('warehouse-security').value) || 1;
            const id = document.getElementById('warehouse-id').value || 'WH-' + Date.now();

            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            const existingIdx = warehouses.findIndex(w => w.id === id);

            const whData = { id, name, type, securityLevel: security };

            if (existingIdx > -1) {
                warehouses[existingIdx] = whData;
            } else {
                warehouses.push(whData);
            }

            localStorage.setItem('erp_warehouses_v1', JSON.stringify(warehouses));
            cancelEditWarehouse();
            renderWarehouses();
            
            if(typeof showToast === 'function') showToast('طھظ… ط­ظپط¸ ط§ظ„ظ…ط®ط²ظ† ط¨ظ†ط¬ط§ط­', 'success');
        }

        function renderWarehouses() {
            const tbody = document.getElementById('warehouses-tbody');
            if(!tbody) return;
            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            tbody.innerHTML = '';
            
            if(warehouses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 15px; color: var(--text-secondary);">ظ„ط§ طھظˆط¬ط¯ ظ…ط®ط§ط²ظ† ظ…ط¶ط§ظپط© ط¨ط¹ط¯</td></tr>';
                return;
            }

            warehouses.forEach(w => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td><strong>${w.name}</strong></td>
                    <td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${w.type}</span></td>
                    <td>
                        <button class="btn-text" onclick="editWarehouse('${w.id}')">طھط¹ط¯ظٹظ„</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteWarehouse('${w.id}')">ط­ط°ظپ</button>
                    </td>
                </tr>`);
            });
        }

        function editWarehouse(id) {
            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            const wh = warehouses.find(w => w.id === id);
            if(!wh) return;
            document.getElementById('warehouse-id').value = wh.id;
            document.getElementById('warehouse-name').value = wh.name;
            document.getElementById('warehouse-type').value = wh.type || 'ظ…ط³طھظˆط¯ط¹ ط±ط¦ظٹط³ظٹ';
            document.getElementById('warehouse-security').value = wh.securityLevel || '1';
            document.getElementById('btn-cancel-warehouse').style.display = 'inline-block';
        }

        function cancelEditWarehouse() {
            document.getElementById('warehouse-form').style.display = 'none';
            document.getElementById('warehouse-id').value = '';
            document.getElementById('warehouse-name').value = '';
            document.getElementById('warehouse-type').value = 'ظ…ط³طھظˆط¯ط¹ ط±ط¦ظٹط³ظٹ';
            document.getElementById('warehouse-security').value = '1';
        }

        function openWarehouseForm() {
            cancelEditWarehouse();
            document.getElementById('warehouse-form').style.display = 'block';
        }

        function deleteWarehouse(id) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ‡ط°ط§ ط§ظ„ظ…ط®ط²ظ†طں ط³ظٹطھظ… ط­ط°ظپ ط§ظ„ظ…ط®ط²ظ† ظپظ‚ط·طŒ ظ„ظ† ظٹطھظ… ط­ط°ظپ ط§ظ„ط¨ط¶ط§ط¦ط¹ ط§ظ„ظ…ظˆط¬ظˆط¯ط©.')) {
                let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
                const idx = warehouses.findIndex(w => w.id === id); if(idx > -1) { warehouses[idx].deleted = true; warehouses[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_warehouses_v1', JSON.stringify(warehouses));
                renderWarehouses();
            }
        }

        // ================= CUSTOMERS LOGIC =================
        function saveCustomer() {
            const id = document.getElementById('cust-id').value || 'CUST-' + Date.now();
            const name = document.getElementById('cust-name').value;
            const phone = document.getElementById('cust-phone').value;
            const whatsapp = document.getElementById('cust-whatsapp').value;
            const email = document.getElementById('cust-email').value;
            const contactPerson = document.getElementById('cust-contact').value;
            const address = document.getElementById('cust-address').value;
            const opBalance = parseFloat(document.getElementById('cust-opening-balance').value) || 0;
            const openingDebit = opBalance >= 0 ? opBalance : 0;
            const openingCredit = opBalance < 0 ? Math.abs(opBalance) : 0;
            // Auto-read exchange rate from settings based on currency
            const currency = document.getElementById('cust-currency').value || 'SDG';
            let exRate = 1;
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (currency !== 'SDG' && cfg.rateUSD) exRate = parseFloat(cfg.rateUSD) || 1;
            } catch(e) {}
            const taxNumber = document.getElementById('cust-tax').value || '';
            const accountNo = document.getElementById('cust-account-no').value || '';
            const limit = parseFloat(document.getElementById('cust-limit').value) || 0;
            const security = parseInt(document.getElementById('cust-security').value) || 1;
            const status = document.getElementById('cust-status').value || 'active';
            const notes = document.getElementById('cust-notes').value;

            const bankAccounts = [];
            document.querySelectorAll('.cust-bank-row').forEach(row => {
                bankAccounts.push({
                    bankName: row.querySelector('.bank-name').value,
                    branch: row.querySelector('.bank-branch').value,
                    accountNo: row.querySelector('.bank-acc').value,
                    securityLevel: parseInt(row.querySelector('.bank-security').value) || 1,
                    isDeactivated: row.querySelector('.bank-deactive').checked
                });
            });

            if (!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„ط¹ظ…ظٹظ„'); return; }

            // Check for duplicate names in Customers and Suppliers
            const currentName = name.trim().toLowerCase();
            let allCusts = []; let allSups = [];
            try {
                allCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                allSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if(!Array.isArray(allCusts)) allCusts = [];
                if(!Array.isArray(allSups)) allSups = [];
            } catch(e) {}
            
            const isCustDup = allCusts.some(c => c.name && c.name.trim().toLowerCase() === currentName && c.id !== id);
            const isSupDup = allSups.some(s => s.name && s.name.trim().toLowerCase() === currentName && s.id !== id);
            
            if (isCustDup || isSupDup) {
                alert('ظ‡ط°ط§ ط§ظ„ط§ط³ظ… ظ…ظˆط¬ظˆط¯ ظ…ط³ط¨ظ‚ط§ظ‹ (ظƒط¹ظ…ظٹظ„ ط£ظˆ ظ…ظˆط±ط¯). ظ„ط§ ظٹظ…ظƒظ† طھظƒط±ط§ط± ط§ظ„ط£ط³ظ…ط§ط،!');
                return;
            }

            let customers = allCusts;
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            
            const existingIdx = customers.findIndex(c => c.id === id);
            
            const custData = {
                id, name, phone, whatsapp, email, contactPerson, address, 
                openingDebit, openingCredit, 
                currency, exRate, taxNumber, accountNo,
                currentBalance: (openingDebit - openingCredit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes,
                bankAccounts: bankAccounts
            };

            if (existingIdx > -1) {
                custData.currentBalance = customers[existingIdx].currentBalance;
                customers[existingIdx] = custData;
            } else {
                customers.push(custData);
            }

            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            clearCustomerForm();
            renderCustomers();
        }

        function renderCustomers(query = '') {
            const tbody = document.getElementById('customers-tbody');
            if(!tbody) return;
            let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);

            if (query) {
                const q = query.toLowerCase();
                customers = customers.filter(c => 
                    (c.name && c.name.toLowerCase().includes(q)) || 
                    (c.phone && c.phone.toLowerCase().includes(q)) || 
                    (c.email && c.email.toLowerCase().includes(q))
                );
            }

            tbody.innerHTML = '';
            customers.forEach(c => {
                const statusMap = {'vip': '<span style="background:rgba(168,85,247,0.15); color:#a855f7; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">ًںŒں VIP</span>', 'blacklisted': '<span style="background:rgba(239,68,68,0.15); color:#ef4444; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">ًں”´ ظ…ط­ط¸ظˆط±</span>'};
                const statusBadge = statusMap[c.status] || '<span style="background:rgba(52,211,153,0.15); color:#34d399; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">ًںں¢ ظ†ط´ط·</span>';
                const bal = parseFloat(c.currentBalance) || 0;
                const balColor = bal > 0 ? '#f87171' : (bal < 0 ? '#34d399' : '#94a3b8');
                const balLabel = bal > 0 ? 'ط¹ظ„ظٹظ‡' : (bal < 0 ? 'ظ„ظ‡' : 'ظ…ط³ط¯ط¯');
                const balDisplay = `<span style="color:${balColor}; font-weight:900; font-family:monospace;">${Math.abs(bal).toLocaleString()}</span> <small style="color:${balColor}; opacity:0.8;">${balLabel}</small>`;
                const shortId = (c.id || '').replace('CUST-', '').slice(-6);
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="font-family:monospace; font-size:0.8rem; color:#94a3b8;">${shortId}</td>
                    <td style="font-weight:bold;">${c.name}</td>
                    <td>${c.phone || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>${balDisplay}</td>
                    <td>${(c.creditLimit || 0).toLocaleString()} SDG</td>
                    <td style="font-size:0.85rem;">${c.contactPerson || '-'}</td>
                    <td style="text-align:center;">${c.securityLevel || 1}</td>
                    <td><button class="btn-text" onclick="editCustomer('${c.id}')">طھط¹ط¯ظٹظ„</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCustomer('${c.id}')">ط­ط°ظپ</button></td>
                </tr>`);
            });
        }

        function filterCustomers() {
            const query = document.getElementById('customer-search-input').value;
            renderCustomers(query);
        }

        function addCustomerBankAccount(data = null) {
            const container = document.getElementById('cust-bank-accounts-container');
            if(!container) return;
            const row = document.createElement('div');
            row.className = 'cust-bank-row';
            row.style.cssText = 'display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; padding: 15px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; position: relative; animation: fadeIn 0.3s ease-out;';
            
            row.innerHTML = `
                <div class="input-group"><label>ط§ط³ظ… ط§ظ„ط¨ظ†ظƒ</label><input type="text" class="bank-name" placeholder="ظ…ط«ط§ظ„: ط¨ظ†ظƒ ط§ظ„ط®ط±ط·ظˆظ…" value="${data ? (data.bankName || '') : ''}"></div>
                <div class="input-group"><label>ط§ظ„ظپط±ط¹</label><input type="text" class="bank-branch" placeholder="ظ…ط«ط§ظ„: ظپط±ط¹ ط§ظ„ط±ظٹط§ط¶" value="${data ? (data.branch || '') : ''}"></div>
                <div class="input-group"><label>ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨</label><input type="text" class="bank-acc" placeholder="ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨ ط§ظ„ط¨ظ†ظƒظٹ" value="${data ? (data.accountNo || '') : ''}"></div>
                <div class="input-group"><label>ط¯ط±ط¬ط© ط³ط±ظٹط© ط§ظ„ط¨ظ†ظƒ</label>
                    <select class="bank-security">
                        <option value="1" ${data && data.securityLevel == 1 ? 'selected' : ''}>1 (ط§ظ„ط£ظ‚ظ„ ط³ط±ظٹط©)</option>
                        <option value="2" ${data && data.securityLevel == 2 ? 'selected' : ''}>2</option>
                        <option value="3" ${data && data.securityLevel == 3 ? 'selected' : ''}>3</option>
                        <option value="4" ${data && data.securityLevel == 4 ? 'selected' : ''}>4</option>
                        <option value="5" ${data && data.securityLevel == 5 ? 'selected' : ''}>5</option>
                        <option value="6" ${data && data.securityLevel == 6 ? 'selected' : ''}>6</option>
                        <option value="7" ${data && data.securityLevel == 7 ? 'selected' : ''}>7</option>
                        <option value="8" ${data && data.securityLevel == 8 ? 'selected' : ''}>8</option>
                        <option value="9" ${data && data.securityLevel == 9 ? 'selected' : ''}>9</option>
                        <option value="10" ${data && data.securityLevel == 10 ? 'selected' : ''}>10 (ط§ظ„ط£ط¹ظ„ظ‰ ط³ط±ظٹط©)</option>
                    </select>
                </div>
                <div class="input-group" style="display:flex; align-items:flex-end; gap: 10px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:bold; color:var(--danger-color); margin-bottom:10px;">
                        <input type="checkbox" class="bank-deactive" style="width:18px; height:18px; accent-color:var(--danger-color);" ${data && data.isDeactivated ? 'checked' : ''}> ط¥ظٹظ‚ط§ظپ
                    </label>
                    <button type="button" onclick="this.closest('.cust-bank-row').remove()" style="background: rgba(244,63,94,0.15); color: #f43f5e; border: 1px solid rgba(244,63,94,0.3); border-radius: 6px; cursor: pointer; padding: 6px 12px; margin-bottom: 5px; margin-right: auto;" title="ط­ط°ظپ ط§ظ„ط­ط³ط§ط¨">ًں—‘ï¸ڈ ط­ط°ظپ</button>
                </div>
            `;
            container.appendChild(row);
        }

        function clearCustomerForm() {
            document.getElementById('cust-id').value = '';
            document.getElementById('cust-name').value = '';
            document.getElementById('cust-phone').value = '';
            document.getElementById('cust-whatsapp').value = '';
            document.getElementById('cust-email').value = '';
            document.getElementById('cust-contact').value = '';
            document.getElementById('cust-address').value = '';
            document.getElementById('cust-opening-balance').value = '';
            document.getElementById('cust-currency').value = 'SDG';

            document.getElementById('cust-tax').value = '';
            document.getElementById('cust-account-no').value = '';
            document.getElementById('cust-limit').value = '';
            document.getElementById('cust-security').value = '1';
            document.getElementById('cust-status').value = 'active';
            document.getElementById('cust-notes').value = '';
            
            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) {
                bankContainer.innerHTML = '';
                addCustomerBankAccount();
            }
        }

        function editCustomer(id) {
            let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);
            const cust = customers.find(c => c.id === id);
            if(!cust) return;
            document.getElementById('cust-id').value = cust.id;
            document.getElementById('cust-name').value = cust.name;
            document.getElementById('cust-phone').value = cust.phone || '';
            document.getElementById('cust-whatsapp').value = cust.whatsapp || '';
            document.getElementById('cust-email').value = cust.email || '';
            document.getElementById('cust-contact').value = cust.contactPerson || '';
            document.getElementById('cust-address').value = cust.address || '';
            const bal = (cust.openingDebit || 0) - (cust.openingCredit || 0);
            document.getElementById('cust-opening-balance').value = bal || '';
            document.getElementById('cust-currency').value = cust.currency || 'SDG';

            document.getElementById('cust-tax').value = cust.taxNumber || '';
            document.getElementById('cust-account-no').value = cust.accountNo || '';
            document.getElementById('cust-limit').value = cust.creditLimit || '';
            document.getElementById('cust-security').value = cust.securityLevel || '1';
            document.getElementById('cust-status').value = cust.status || 'active';
            document.getElementById('cust-notes').value = cust.notes || '';
            
            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) bankContainer.innerHTML = '';
            
            if (cust.bankAccounts && cust.bankAccounts.length > 0) {
                cust.bankAccounts.forEach(acc => addCustomerBankAccount(acc));
            } else if (cust.bankDetails) {
                addCustomerBankAccount(cust.bankDetails);
            } else {
                addCustomerBankAccount();
            }
            
            document.getElementById('customer-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteCustomer(id) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ‡ط°ط§ ط§ظ„ط¹ظ…ظٹظ„طں')) {
                let customers = [];
                try {
                    customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                    if (!Array.isArray(customers)) customers = [];
                } catch(e) {}
                
                const idx = customers.findIndex(c => String(c.id) === String(id));
                if (idx > -1) {
                    customers.splice(idx, 1);
                } else {
                    // Fallback for corrupted data (try by name if id somehow matches name)
                    const nameIdx = customers.findIndex(c => c.name === id);
                    if (nameIdx > -1) customers.splice(nameIdx, 1);
                }
                
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
                renderCustomers();
            }
        }

        // ================= SUPPLIERS LOGIC =================
        function saveSupplier() {
            const id = document.getElementById('sup-id').value || 'SUP-' + Date.now();
            const name = document.getElementById('sup-name').value;
            const phone = document.getElementById('sup-phone').value;
            const whatsapp = document.getElementById('sup-whatsapp').value;
            const email = document.getElementById('sup-email').value;
            const address = document.getElementById('sup-address').value;
            const opBalance = parseFloat(document.getElementById('sup-opening-balance').value) || 0;
            const openingCredit = opBalance >= 0 ? opBalance : 0;
            const openingDebit = opBalance < 0 ? Math.abs(opBalance) : 0;
            // Auto-read exchange rate from settings based on currency
            const currency = document.getElementById('sup-currency').value || 'SDG';
            let exRate = 1;
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (currency !== 'SDG' && cfg.rateUSD) exRate = parseFloat(cfg.rateUSD) || 1;
            } catch(e) {}
            const taxNumber = document.getElementById('sup-tax').value || '';
            const accountNo = document.getElementById('sup-account-no').value || '';
            const limit = parseFloat(document.getElementById('sup-limit').value) || 0;
            const security = parseInt(document.getElementById('sup-security').value) || 1;
            const status = document.getElementById('sup-status').value || 'active';
            const notes = document.getElementById('sup-notes').value;

            if (!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„ظ…ظˆط±ط¯'); return; }

            // Check for duplicate names in Customers and Suppliers
            const currentName = name.trim().toLowerCase();
            let allCusts = []; let allSups = [];
            try {
                allCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                allSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if(!Array.isArray(allCusts)) allCusts = [];
                if(!Array.isArray(allSups)) allSups = [];
            } catch(e) {}
            
            const isCustDup = allCusts.some(c => c.name && c.name.trim().toLowerCase() === currentName && c.id !== id);
            const isSupDup = allSups.some(s => s.name && s.name.trim().toLowerCase() === currentName && s.id !== id);
            
            if (isCustDup || isSupDup) {
                alert('ظ‡ط°ط§ ط§ظ„ط§ط³ظ… ظ…ظˆط¬ظˆط¯ ظ…ط³ط¨ظ‚ط§ظ‹ (ظƒط¹ظ…ظٹظ„ ط£ظˆ ظ…ظˆط±ط¯). ظ„ط§ ظٹظ…ظƒظ† طھظƒط±ط§ط± ط§ظ„ط£ط³ظ…ط§ط،!');
                return;
            }

            let suppliers = allSups;
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            
            const existingIdx = suppliers.findIndex(s => s.id === id);
            
            const supData = {
                id, name, phone, whatsapp, email, address, 
                openingDebit, openingCredit, 
                currency, exRate, taxNumber, accountNo,
                currentBalance: (openingCredit - openingDebit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes
            };

            if (existingIdx > -1) {
                supData.currentBalance = suppliers[existingIdx].currentBalance;
                suppliers[existingIdx] = supData;
            } else {
                suppliers.push(supData);
            }

            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
            clearSupplierForm();
            renderSuppliers();
        }

        function renderSuppliers() {
            const tbody = document.querySelector('#tab-suppliers-table tbody');
            if(!tbody) return;
            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
            tbody.innerHTML = '';
            suppliers.forEach(s => {
                const statusMap = {'vip': '<span style="background:rgba(168,85,247,0.15); color:#a855f7; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">ًںŒں VIP</span>', 'blacklisted': '<span style="background:rgba(239,68,68,0.15); color:#ef4444; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">ًں”´ ظ…ط­ط¸ظˆط±</span>'};
                const statusBadge = statusMap[s.status] || '<span style="background:rgba(52,211,153,0.15); color:#34d399; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">ًںں¢ ظ†ط´ط·</span>';
                const bal = parseFloat(s.currentBalance) || 0;
                const balColor = bal > 0 ? '#34d399' : (bal < 0 ? '#f87171' : '#94a3b8');
                const balLabel = bal > 0 ? 'ظ„ظ‡' : (bal < 0 ? 'ط¹ظ„ظٹظ‡' : 'ظ…ط³ط¯ط¯');
                const balDisplay = `<span style="color:${balColor}; font-weight:900; font-family:monospace;">${Math.abs(bal).toLocaleString()}</span> <small style="color:${balColor}; opacity:0.8;">${balLabel}</small>`;
                const shortId = (s.id || '').replace('SUP-', '').slice(-6);
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="font-family:monospace; font-size:0.8rem; color:#94a3b8;">${shortId}</td>
                    <td style="font-weight:bold;">${s.name}</td>
                    <td>${s.phone || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>${balDisplay}</td>
                    <td>${(s.creditLimit || 0).toLocaleString()} SDG</td>
                    <td style="text-align:center;">${s.securityLevel || 1}</td>
                    <td><button class="btn-text" onclick="editSupplier('${s.id}')">طھط¹ط¯ظٹظ„</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSupplier('${s.id}')">ط­ط°ظپ</button></td>
                </tr>`);
            });
        }
        
        function clearSupplierForm() {
            document.getElementById('sup-id').value = '';
            document.getElementById('sup-name').value = '';
            document.getElementById('sup-phone').value = '';
            document.getElementById('sup-whatsapp').value = '';
            document.getElementById('sup-email').value = '';
            document.getElementById('sup-address').value = '';
            document.getElementById('sup-opening-balance').value = '';
            document.getElementById('sup-currency').value = 'SDG';

            document.getElementById('sup-tax').value = '';
            document.getElementById('sup-account-no').value = '';
            document.getElementById('sup-limit').value = '';
            document.getElementById('sup-security').value = '1';
            document.getElementById('sup-status').value = 'active';
            document.getElementById('sup-notes').value = '';
        }
        
        function editSupplier(id) {
            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
            const sup = suppliers.find(s => s.id === id);
            if(!sup) return;
            document.getElementById('sup-id').value = sup.id;
            document.getElementById('sup-name').value = sup.name;
            document.getElementById('sup-phone').value = sup.phone || '';
            document.getElementById('sup-whatsapp').value = sup.whatsapp || '';
            document.getElementById('sup-email').value = sup.email || '';
            document.getElementById('sup-address').value = sup.address || '';
            const bal = (sup.openingCredit || 0) - (sup.openingDebit || 0);
            document.getElementById('sup-opening-balance').value = bal || '';
            document.getElementById('sup-currency').value = sup.currency || 'SDG';

            document.getElementById('sup-tax').value = sup.taxNumber || '';
            document.getElementById('sup-account-no').value = sup.accountNo || '';
            document.getElementById('sup-limit').value = sup.creditLimit || '';
            document.getElementById('sup-security').value = sup.securityLevel || '1';
            document.getElementById('sup-status').value = sup.status || 'active';
            document.getElementById('sup-notes').value = sup.notes || '';
            
            document.getElementById('supplier-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteSupplier(id) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ‡ط°ط§ ط§ظ„ظ…ظˆط±ط¯طں')) {
                let suppliers = [];
                try {
                    suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                    if (!Array.isArray(suppliers)) suppliers = [];
                } catch(e) {}
                
                const idx = suppliers.findIndex(s => String(s.id) === String(id));
                if (idx > -1) {
                    suppliers.splice(idx, 1);
                } else {
                    const nameIdx = suppliers.findIndex(s => s.name === id);
                    if (nameIdx > -1) suppliers.splice(nameIdx, 1);
                }
                
                localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
                renderSuppliers();
            }
        }

        function renderEmployees() {
            const tbody = document.getElementById('tab-employees-table');
            if(!tbody) return;
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            employees = employees.filter(e => !e.deleted);
            tbody.innerHTML = '';
            
            const safeSelect = document.getElementById('safe-responsible');
            if (safeSelect) {
                const currentVal = safeSelect.value;
                safeSelect.innerHTML = '<option value="">-- ط§ط®طھط± --</option>';
                employees.forEach(emp => {
                    if (emp.status === 'active' || emp.status === 'ظ†ط´ط·') {
                        const opt = document.createElement('option');
                        opt.value = emp.id || emp.username || emp.name;
                        opt.textContent = emp.name || emp.fullName || emp.username || 'ط¨ط¯ظˆظ† ط§ط³ظ…';
                        safeSelect.appendChild(opt);
                    }
                });
                safeSelect.value = currentVal;
            }

            employees.forEach(emp => {
                tbody.insertAdjacentHTML('beforeend', `<tr><td>${emp.name}</td><td>${emp.role}</td><td>${emp.phone||'-'}</td><td>${emp.securityLevel}</td><td>${emp.status}</td><td><button class="btn-text" onclick="editEmployee('${emp.id}')">طھط¹ط¯ظٹظ„</button></td></tr>`);
            });
        }
        
        function openNewEmployeeForm() {
            document.getElementById('emp-id').value = '';
            document.getElementById('emp-name').value = '';
            document.getElementById('emp-phone').value = '';
            document.getElementById('emp-password').value = '';
            document.getElementById('emp-role').value = 'cashier';
            document.getElementById('emp-security').value = '1';
            document.getElementById('emp-status').value = 'active';
            document.getElementById('employee-form').style.display = 'block';
        }
        
        function saveEmployee() {
            const id = document.getElementById('emp-id').value || 'EMP-' + Date.now();
            const name = document.getElementById('emp-name').value;
            if(!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„ظ…ظˆط¸ظپ'); return; }
            
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            
            const existingIdx = employees.findIndex(e => e.id === id);
            
            const empData = {
                id,
                name,
                phone: document.getElementById('emp-phone').value,
                password: document.getElementById('emp-password').value,
                role: document.getElementById('emp-role').value,
                securityLevel: parseInt(document.getElementById('emp-security').value) || 1,
                status: document.getElementById('emp-status').value
            };
            
            if (existingIdx > -1) {
                employees[existingIdx] = empData;
            } else {
                employees.push(empData);
            }
            
            localStorage.setItem('erp_users_v1', JSON.stringify(employees));
            document.getElementById('employee-form').style.display='none';
            renderEmployees();
        }
        
        function editEmployee(id) {
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            employees = employees.filter(e => !e.deleted);
            const emp = employees.find(e => e.id === id);
            if(!emp) return;
            document.getElementById('emp-id').value = emp.id;
            document.getElementById('emp-name').value = emp.name;
            document.getElementById('emp-phone').value = emp.phone || '';
            document.getElementById('emp-password').value = emp.password || '';
            document.getElementById('emp-role').value = emp.role || 'cashier';
            document.getElementById('emp-security').value = emp.securityLevel || '1';
            document.getElementById('emp-status').value = emp.status || 'active';
            document.getElementById('employee-form').style.display='block';
        }

    
        // ================= BANKS & SAFES LOGIC =================
        function openBankForm() {
            document.getElementById('bank-form').style.display = 'block';
            document.getElementById('bank-code').value = 'B-' + Date.now();
        }

        function closeBankForm() {
            document.getElementById('bank-form').style.display = 'none';
        }

        

        function saveBank() {
            const id = document.getElementById('bank-code').value;
            const name = document.getElementById('bank-name').value;
            if(!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„ط­ط³ط§ط¨/ط§ظ„ط®ط²ظٹظ†ط©'); return; }

            const type = 'bank';
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            
            const existingIdx = banks.findIndex(b => b.id === id);

            const bankData = {
                id, name, type,
                branch: document.getElementById('bank-branch').value,
                openingBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                openingDate: document.getElementById('bank-opening-date').value,
                glAccount: document.getElementById('bank-gl-account').value,
                iban: document.getElementById('bank-iban').value,
                swift: document.getElementById('bank-swift').value,
                responsible: '',
                currency: document.getElementById('bank-currency').value,
                maxLimit: 0,
                minLimit: 0,
                confidentiality: document.getElementById('bank-confidentiality').value,
                isInactive: document.getElementById('bank-inactive').checked,
                isModifier: false
            };

            if (existingIdx > -1) {
                bankData.currentBalance = banks[existingIdx].currentBalance;
                banks[existingIdx] = bankData;
            } else {
                banks.push(bankData);
            }

            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            closeBankForm();
            renderBanks();
        }

        function renderBanks() {
            const tbody = document.getElementById('banks-tbody');
            if(!tbody) return;
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
            tbody.innerHTML = '';
            banks.forEach(b => {
                const typeLabel = b.type === 'bank' ? 'ًںڈ¦ ط­ط³ط§ط¨ ط¨ظ†ظƒظٹ' : 'ًں’° ط®ط²ظٹظ†ط©';
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td>${b.id}</td>
                    <td>${b.name} <br><small>${b.branch || ''}</small></td>
                    <td>${typeLabel}</td>
                    <td>${b.currency}</td>
                    <td>${b.currentBalance}</td>
                    <td>
                        <button class="btn-text" onclick="editBank('${b.id}')">طھط¹ط¯ظٹظ„</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteBank('${b.id}')">ط­ط°ظپ</button>
                    </td>
                </tr>`);
            });
        }

        function editBank(id) {
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
            const bank = banks.find(b => b.id === id);
            if(!bank) return;
            document.querySelector(`input[name="bank_type"][value="${bank.type}"]`).checked = true;
            toggleBankFields();
            document.getElementById('bank-code').value = bank.id;
            document.getElementById('bank-name').value = bank.name;
            document.getElementById('bank-branch').value = bank.branch || '';
            document.getElementById('bank-opening-balance').value = bank.openingBalance || '';
            document.getElementById('bank-opening-date').value = bank.openingDate || '';
            document.getElementById('bank-gl-account').value = bank.glAccount || '';
            document.getElementById('bank-iban').value = bank.iban || '';
            document.getElementById('bank-swift').value = bank.swift || '';
            document.getElementById('bank-responsible').value = bank.responsible || '';
            document.getElementById('bank-currency').value = bank.currency || 'SDG';
            document.getElementById('safe-max-limit').value = bank.maxLimit || '';
            document.getElementById('safe-min-limit').value = bank.minLimit || '';
            document.getElementById('bank-confidentiality').value = bank.confidentiality || '1';
            document.getElementById('bank-inactive').checked = bank.isInactive || false;
            
            openBankForm();
        }

        function deleteBank(id) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ‡ط°ط§ ط§ظ„ط­ط³ط§ط¨طں')) {
                let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
                const idx = banks.findIndex(b => b.id === id); if(idx > -1) { banks[idx].deleted = true; banks[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
                renderBanks();
            }
        }

                // ================= ITEMS LOGIC =================
        function closeItemForm() { document.getElementById('item-form').style.display = 'none'; }

        function updateToggleLabel() {
            const t = document.getElementById('item-active-toggle');
            const l = document.getElementById('item-active-label');
            if(t.checked) { l.innerText = 'ًںں¢ ظ†ط´ط·'; l.style.color = 'var(--success-color)'; }
            else { l.innerText = 'ًں”´ ظ…ظˆظ‚ظˆظپ'; l.style.color = 'var(--danger-color)'; }
        }

        function updateBaseUnitRow() {
            const v = document.getElementById('item-primary-base-unit').value;
            const d = document.getElementById('base-unit-name-display');
            if(d) d.value = v;
            document.querySelectorAll('.row-base-unit').forEach(el => el.value = v);
        }

        function quickAddCategory() {
            const name = prompt('ط£ط¯ط®ظ„ ط§ط³ظ… ط§ظ„ظپط¦ط© ط§ظ„ط¬ط¯ظٹط¯ط©:');
            if(!name) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
                if (!Array.isArray(cats)) cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            } catch(e) {
                cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            updateCategoryDropdowns();
            document.getElementById('item-category').value = name;
            renderCategoriesList();
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value;
            if(!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„طµظ†ظپ'); return; }
            const baseUnit = document.getElementById('item-primary-base-unit').value;
            if(!baseUnit) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ظ„ظˆط­ط¯ط§طھ ط§ظ„ط£ط³ط§ط³ظٹط©'); return; }
            const secondaryUnit = document.getElementById('item-primary-base-unit').value;
            const id = document.getElementById('item-code').value || 'ITEM-' + Date.now();
            const units = [];
            document.querySelectorAll('#units-matrix-body tr').forEach((row) => {
                const inputs = row.querySelectorAll('input');
                if(inputs.length >= 7) {
                    units.push({ unitName: inputs[0].value || baseUnit, factor: parseFloat(inputs[1].value) || 1, barcode: inputs[2].value, cashPrice: parseFloat(inputs[3].value) || 0, creditPrice: parseFloat(inputs[4].value) || 0, minPrice: parseFloat(inputs[5].value) || 0, weight: parseFloat(inputs[6].value) || 0, stock: 0 });
                }
            });
            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const itemData = { id, name, nameLatin: document.getElementById('item-name-latin').value, barcode: document.getElementById('item-barcode').value, category: document.getElementById('item-category').value, nature: document.getElementById('item-nature').value, brand: document.getElementById('item-brand').value, status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive', sellingPrice: parseFloat(document.getElementById('item-selling-price').value) || 0, maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0, taxable: document.getElementById('item-taxable').checked, taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0, costMethod: costRadio ? costRadio.value : 'lastPurchase', openingQty: parseFloat(document.getElementById('item-opening-qty').value) || 0, openingValue: parseFloat(document.getElementById('item-opening-value').value) || 0, binLocation: document.getElementById('item-bin-location').value, maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0, minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0, reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0, primaryBaseUnit: baseUnit, baseUnit: secondaryUnit, bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1, units, description: document.getElementById('item-description').value };
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) { if(items[ei].units) { itemData.units.forEach((u,i) => { if(items[ei].units[i]) u.stock = items[ei].units[i].stock || 0; }); } items[ei] = itemData; }
            else { itemData.units[0].stock = itemData.openingQty; items.push(itemData); }
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            closeItemForm(); renderItems();
        }

        

function openSafeForm() {
            document.getElementById('safe-form').style.display = 'block';
            document.getElementById('safe-code').value = 'B-' + Date.now();
        }

        function closeSafeForm() {
            document.getElementById('safe-form').style.display = 'none';
        }

        

        function saveSafe() {
            const id = document.getElementById('safe-code').value;
            const name = document.getElementById('safe-name').value;
            if(!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„ط®ط²ظٹظ†ط©'); return; }

            const type = 'safe';
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            
            const isDuplicateName = safes.some(b => b.name.trim() === name.trim() && b.id !== id);
            if (isDuplicateName) {
                alert('ظ‡ط°ط§ ط§ظ„ط§ط³ظ… ظ…ط³طھط®ط¯ظ… ظ„ط®ط²ظ†ط© ط£ط®ط±ظ‰طŒ ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ظ…ط®طھظ„ظپ.');
                return;
            }

            const existingIdx = safes.findIndex(b => b.id === id);

            const safeData = {
                id, name, type,
                branch: '',
                openingBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                openingDate: document.getElementById('safe-opening-date').value,
                glAccount: document.getElementById('safe-gl-account').value,
                iban: '',
                swift: '',
                responsible: document.getElementById('safe-responsible').value,
                currency: document.getElementById('safe-currency').value,
                maxLimit: parseFloat(document.getElementById('safe-max-limit').value) || 0,
                minLimit: parseFloat(document.getElementById('safe-min-limit').value) || 0,
                confidentiality: document.getElementById('safe-confidentiality').value,
                isInactive: document.getElementById('safe-inactive').checked,
                isModifier: document.getElementById('safe-modifier').checked
            };

            if (existingIdx > -1) {
                safeData.currentBalance = safes[existingIdx].currentBalance;
                safes[existingIdx] = safeData;
            } else {
                safes.push(safeData);
            }

            localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
            closeSafeForm();
            renderSafes();
        }

        function renderSafes() {
            const tbody = document.getElementById('safes-tbody');
            if(!tbody) return;
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            tbody.innerHTML = '';
            safes.forEach(b => {
                const typeLabel = b.type === 'safe' ? 'ًں’° ط®ط²ظٹظ†ط©' : 'ًں’° ط®ط²ظٹظ†ط©';
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td>${b.id}</td>
                    <td>${b.name} <br><small>${b.branch || ''}</small></td>
                    <td>${typeLabel}</td>
                    <td>${b.currency}</td>
                    <td>${b.currentBalance}</td>
                    <td>
                        <button class="btn-text" onclick="editSafe('${b.id}')">طھط¹ط¯ظٹظ„</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSafe('${b.id}')">ط­ط°ظپ</button>
                    </td>
                </tr>`);
            });
        }

        function editSafe(id) {
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            const safe = safes.find(b => b.id === id);
            if(!safe) return;
            document.querySelector(`input[name="safe_type"][value="${safe.type}"]`).checked = true;
            toggleSafeFields();
            document.getElementById('safe-code').value = safe.id;
            document.getElementById('safe-name').value = safe.name;
            
            document.getElementById('safe-opening-balance').value = safe.openingBalance || '';
            document.getElementById('safe-opening-date').value = safe.openingDate || '';
            document.getElementById('safe-gl-account').value = safe.glAccount || '';
            
            
            document.getElementById('safe-responsible').value = safe.responsible || '';
            document.getElementById('safe-currency').value = safe.currency || 'SDG';
            document.getElementById('safe-max-limit').value = safe.maxLimit || '';
            document.getElementById('safe-min-limit').value = safe.minLimit || '';
            document.getElementById('safe-confidentiality').value = safe.confidentiality || '1';
            document.getElementById('safe-inactive').checked = safe.isInactive || false;
            document.getElementById('safe-modifier').checked = safe.isModifier || false;
            openSafeForm();
        }

        function deleteSafe(id) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ‡ط°ط§ ط§ظ„ط­ط³ط§ط¨طں')) {
                let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
                const idx = safes.findIndex(b => b.id === id); if(idx > -1) { safes[idx].deleted = true; safes[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
                renderSafes();
            }
        }

                // ================= ITEMS LOGIC =================
        function closeItemForm() { document.getElementById('item-form').style.display = 'none'; }

        function updateToggleLabel() {
            const t = document.getElementById('item-active-toggle');
            const l = document.getElementById('item-active-label');
            if(t.checked) { l.innerText = 'ًںں¢ ظ†ط´ط·'; l.style.color = 'var(--success-color)'; }
            else { l.innerText = 'ًں”´ ظ…ظˆظ‚ظˆظپ'; l.style.color = 'var(--danger-color)'; }
        }

        function updateBaseUnitRow() {
            const v = document.getElementById('item-primary-base-unit').value;
            const d = document.getElementById('base-unit-name-display');
            if(d) d.value = v;
            document.querySelectorAll('.row-base-unit').forEach(el => el.value = v);
        }

        function quickAddCategory() {
            const name = prompt('ط£ط¯ط®ظ„ ط§ط³ظ… ط§ظ„ظپط¦ط© ط§ظ„ط¬ط¯ظٹط¯ط©:');
            if(!name) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
                if (!Array.isArray(cats)) cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            } catch(e) {
                cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            updateCategoryDropdowns();
            document.getElementById('item-category').value = name;
            renderCategoriesList();
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value;
            if(!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ط§ظ„طµظ†ظپ'); return; }
            const baseUnit = document.getElementById('item-primary-base-unit').value;
            if(!baseUnit) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ظ„ظˆط­ط¯ط§طھ ط§ظ„ط£ط³ط§ط³ظٹط©'); return; }
            const secondaryUnit = document.getElementById('item-primary-base-unit').value;
            const id = document.getElementById('item-code').value || 'ITEM-' + Date.now();
            const units = [];
            document.querySelectorAll('#units-matrix-body tr').forEach((row) => {
                const unitNameElem = row.querySelector('input[type="text"]:first-of-type, select.unit-name-input');
                const inputs = row.querySelectorAll('input[type="number"], input[type="text"]:not(#base-unit-name-display)');
                
                // inputs now has:
                // [number factor, text barcode, number cash, number credit, number min, number weight]
                // but wait, let's just get them by specific selectors or index correctly to avoid bugs.
                
                const factorInput = row.querySelector('input[type="number"]:nth-of-type(1)'); // wait, nth-of-type(1) might be wrong if there are multiple td's.
                // It's safer to get all inputs AND selects and map them, or use specific classes/indices.
                // Let's use the td index.
                const tds = row.querySelectorAll('td');
                if (tds.length >= 8) {
                    const uName = tds[1].querySelector('input, select')?.value || baseUnit;
                    const uFactor = parseFloat(tds[2].querySelector('input')?.value) || 1;
                    const uBarcode = tds[4].querySelector('input')?.value || '';
                    const uCash = parseFloat(tds[5].querySelector('input')?.value) || 0;
                    const uCredit = parseFloat(tds[6].querySelector('input')?.value) || 0;
                    const uMin = parseFloat(tds[7].querySelector('input')?.value) || 0;
                    const uWeight = parseFloat(tds[8].querySelector('input')?.value) || 0;
                    
                    units.push({ unitName: uName, factor: uFactor, barcode: uBarcode, cashPrice: uCash, creditPrice: uCredit, minPrice: uMin, weight: uWeight, stock: 0 });
                }
            });
            const itemBarcode = document.getElementById('item-barcode').value;
            const newBarcodes = [itemBarcode, ...units.map(u => u.barcode)].filter(b => b && b.trim() !== '');

            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const itemData = { id, name, nameLatin: document.getElementById('item-name-latin').value, barcode: itemBarcode, category: document.getElementById('item-category').value, nature: document.getElementById('item-nature').value, brand: document.getElementById('item-brand').value, status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive', sellingPrice: parseFloat(document.getElementById('item-selling-price').value) || 0, maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0, taxable: document.getElementById('item-taxable').checked, taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0, costMethod: costRadio ? costRadio.value : 'lastPurchase', openingQty: parseFloat(document.getElementById('item-opening-qty').value) || 0, openingValue: parseFloat(document.getElementById('item-opening-value').value) || 0, binLocation: document.getElementById('item-bin-location').value, maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0, minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0, reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0, primaryBaseUnit: baseUnit, baseUnit: secondaryUnit, bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1, units, description: document.getElementById('item-description').value };
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const duplicateName = items.find(i => i.name.trim() === name.trim() && i.id !== id);
            if (duplicateName) { alert('ظ‡ط°ط§ ط§ظ„طµظ†ظپ ظ…ط³ط¬ظ„ ظ…ط³ط¨ظ‚ط§ظ‹ ط¨ظ†ظپط³ ط§ظ„ط§ط³ظ….'); return; }
            
            for (const b of newBarcodes) {
                const duplicateBarcode = items.find(i => i.id !== id && (i.barcode === b || (i.units && i.units.some(u => u.barcode === b))));
                if (duplicateBarcode) {
                    alert('ط§ظ„ط¨ط§ط±ظƒظˆط¯ (' + b + ') ظ…ط³طھط®ط¯ظ… ظ…ط³ط¨ظ‚ط§ظ‹ ظپظٹ طµظ†ظپ ط¢ط®ط±: ' + duplicateBarcode.name);
                    return;
                }
            }
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) { if(items[ei].units) { itemData.units.forEach((u,i) => { if(items[ei].units[i]) u.stock = items[ei].units[i].stock || 0; }); } items[ei] = itemData; }
            else { itemData.units[0].stock = itemData.openingQty; items.push(itemData); }
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            closeItemForm(); renderItems();
        }

        function renderItems() {
            const tbody = document.getElementById('items-table-body');
            if(!tbody) return;
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const totalEl = document.getElementById('kpi-total-items');
            if(totalEl) totalEl.innerText = items.length + ' طµظ†ظپ';
            
            let lowStock = 0;
            items.forEach(i => {
                let stock = i.units && i.units[0] ? (i.units[0].stock || 0) : 0;
                if(stock <= (i.minLimit || 5)) lowStock++;
            });
            const lowEl = document.getElementById('kpi-low-stock');
            if(lowEl) lowEl.innerText = lowStock + ' طµظ†ظپ';
            
            if (currentItemSearchQuery) {
                items = items.filter(i => {
                    const unitBarcodes = i.units ? i.units.map(u => u.barcode || '').join(' ') : '';
                    const str = (i.id + ' ' + i.name + ' ' + (i.category||'') + ' ' + (i.barcode||'') + ' ' + unitBarcodes).toLowerCase();
                    return str.includes(currentItemSearchQuery);
                });
            }
            
            const totalPages = Math.ceil(items.length / itemsPerPage) || 1;
            if (currentPageItems > totalPages) currentPageItems = totalPages;
            const start = (currentPageItems - 1) * itemsPerPage;
            const paginatedItems = items.slice(start, start + itemsPerPage);
            
            tbody.innerHTML = '';
            paginatedItems.forEach(i => {
                let bu = i.baseUnit || (i.units && i.units[0] ? i.units[0].unitName : '-');
                let price = i.sellingPrice || (i.units && i.units[0] ? i.units[0].cashPrice : 0);
                const badge = i.status === 'inactive' ? '<span style="color:var(--danger-color); font-size:0.8rem; font-weight:bold; background:rgba(239,68,68,0.1); padding:3px 8px; border-radius:6px;">ًں”´ ظ…ظˆظ‚ظˆظپ</span>' : '<span style="color:var(--success-color); font-size:0.8rem; font-weight:bold; background:rgba(52,211,153,0.1); padding:3px 8px; border-radius:6px;">ًںں¢ ظ†ط´ط·</span>';
                const subCat = i.subCategory ? ' <small style="color:var(--text-secondary);">(' + i.subCategory + ')</small>' : '';
                tbody.insertAdjacentHTML('beforeend', `<tr class="item-row"><td class="item-id-cell" style="font-weight:bold; color:#38bdf8;">${i.id}</td><td class="item-name-cell" style="font-weight:bold;">${i.name}${subCat}</td><td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${i.category || '-'}</span></td><td>${bu}</td><td style="font-weight:bold; color:var(--success-color);">${price.toLocaleString()} SDG</td><td>${badge}</td><td><button class="btn-text" style="color:#a855f7; font-size:1.1rem; margin-left:8px;" onclick="editItem('${i.id}')" title="طھط¹ط¯ظٹظ„">âœڈï¸ڈ</button><button class="btn-text" style="color:var(--danger-color); font-size:1.1rem;" onclick="deleteItem('${i.id}')" title="ط­ط°ظپ">ًں—‘ï¸ڈ</button></td></tr>`);
            });
            
            renderItemsPagination(totalPages);
            renderCategoriesList();
        }
        
        function renderItemsPagination(totalPages) {
            let pagContainer = document.getElementById('items-pagination-container');
            if (!pagContainer) {
                const tableWrap = document.querySelector('#inner-products .table-responsive');
                if (tableWrap) {
                    tableWrap.insertAdjacentHTML('afterend', '<div id="items-pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding:10px 20px; background:rgba(0,0,0,0.2); border-radius:10px;"></div>');
                    pagContainer = document.getElementById('items-pagination-container');
                }
            }
            if (pagContainer) {
                pagContainer.innerHTML = `
                    <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems>1){currentPageItems--; renderItems();}">ط§ظ„ط³ط§ط¨ظ‚</button>
                    <span style="color:var(--text-secondary); font-weight:bold;">طµظپط­ط© ${currentPageItems} ظ…ظ† ${totalPages}</span>
                    <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems<${totalPages}){currentPageItems++; renderItems();}">ط§ظ„طھط§ظ„ظٹ</button>
                `;
            }
        }

        let currentPageItems = 1;
        const itemsPerPage = 20;
        let currentItemSearchQuery = '';

        let itemSearchTimeout = null;
        function filterItemsTable() {
            clearTimeout(itemSearchTimeout);
            itemSearchTimeout = setTimeout(() => {
                currentItemSearchQuery = (document.getElementById('item-search').value || '').toLowerCase();
                currentPageItems = 1;
                renderItems();
            }, 300);
        }

        function quickAddUnit() {
            const name = prompt('ط£ط¯ط®ظ„ ط§ط³ظ… ط§ظ„ظˆط­ط¯ط© ط§ظ„ط¬ط¯ظٹط¯ط©:');
            if(name) {
                let units = [];
                try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
                if(!units.includes(name)) {
                    units.push(name);
                    localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
                    renderGlobalUnitsList();
                    updateUnitDropdowns(name);
                }
            }
        }

        function buildUnitRow(num, u, isBase) {
            const dis = isBase ? 'disabled' : '';
            const bg = isBase ? 'style="background:rgba(52,211,153,0.08); border-right:3px solid var(--success-color);"' : '';
            const act = isBase ? '<span style="font-size:0.75rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); padding:3px 8px; border-radius:4px;">ط£ط³ط§ط³ظٹط©</span>' : "<button class=\"btn-text\" style=\"color:var(--danger-color);\" onclick=\"this.closest('tr').remove()\">âœ–</button>";
            const idAttr = isBase ? ' id="base-unit-name-display"' : ' class="unit-name-input"';
            
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            const val = u.unitName || '';
            if (val && !units.includes(val)) units.push(val); // Ensure backward compat display
            
            let opts = `<option value="">-- ط§ط®طھط± --</option>` + units.map(un => `<option value="${un}" ${un === val ? 'selected' : ''}>${un}</option>`).join('');
            
            let unitField = isBase ? 
                `<input type="text" id="base-unit-name-display" value="${val}" disabled style="width:100px; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; padding:5px; border-radius:6px;">` : 
                `<select ${idAttr} style="width:120px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:bold; text-align:center; padding:5px; border-radius:6px; outline:none;">${opts}</select>`;

            return '<tr ' + bg + '><td>' + num + '</td><td>' + unitField + '</td><td><input type="number" value="' + (u.factor || 1) + '" ' + dis + ' style="width:60px; background:transparent; border:' + (isBase ? 'none' : '1px solid rgba(255,255,255,0.15)') + '; color:' + (isBase ? 'var(--success-color)' : '#fff') + '; font-weight:bold; text-align:center; padding:5px; border-radius:6px;"></td><td><input type="text" value="' + (document.getElementById('item-primary-base-unit') ? document.getElementById('item-primary-base-unit').value : '') + '" class="row-base-unit" style="width:80px; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; padding:5px; border-radius:6px;" disabled></td><td><input type="text" value="' + (u.barcode || '') + '" placeholder="ط¨ط§ط±ظƒظˆط¯..." style="width:100px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.cashPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px; font-weight:bold;"></td><td><input type="number" value="' + (u.creditPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.minPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.weight || '') + '" placeholder="0.00" style="width:60px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td>' + act + '</td></tr>';
        }

        function editItem(id) {
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const item = items.find(i => i.id === id);
            if(!item) return;
            document.getElementById('form-mode-indicator').innerText = 'طھط¹ط¯ظٹظ„ طµظ†ظپ: ' + item.name;
            document.getElementById('item-code').value = item.id;
            document.getElementById('item-name-input').value = item.name;
            document.getElementById('item-name-latin').value = item.nameLatin || '';
            document.getElementById('item-barcode').value = item.barcode || '';
            updateCategoryDropdowns();
            document.getElementById('item-category').value = item.category || '';
            document.getElementById('item-nature').value = item.nature || 'ظ…ط®ط²ظ†ظٹ';
            document.getElementById('item-brand').value = item.brand || '';
            document.getElementById('item-active-toggle').checked = item.status !== 'inactive';
            updateToggleLabel();
            document.getElementById('item-selling-price').value = item.sellingPrice || '';
            document.getElementById('item-max-discount').value = item.maxDiscount || '';
            document.getElementById('item-taxable').checked = item.taxable || false;
            document.getElementById('item-tax-rate').value = item.taxRate || '';
            document.getElementById('item-tax-rate').disabled = !item.taxable;
            const cm = document.querySelector('input[name="cost_method"][value="' + (item.costMethod || 'lastPurchase') + '"]');
            if(cm) cm.checked = true;
            document.getElementById('item-opening-qty').value = item.openingQty || '';
            document.getElementById('item-opening-value').value = item.openingValue || '';
            document.getElementById('item-bin-location').value = item.binLocation || '';
            document.getElementById('item-max-limit').value = item.maxLimit || '';
            document.getElementById('item-min-limit').value = item.minLimit || '';
            document.getElementById('item-reorder-level').value = item.reorderLevel || '';
            document.getElementById('item-primary-base-unit').value = item.primaryBaseUnit || item.baseUnit || '';
            document.getElementById('item-base-unit').value = item.baseUnit || '';
            document.getElementById('item-bundle-factor').value = item.bundleFactor || 1;
            document.getElementById('item-description').value = item.description || '';
            updateBaseUnitRow();
            const tbody = document.getElementById('units-matrix-body');
            tbody.innerHTML = '';
            if(item.units && item.units.length > 0) { item.units.forEach((u, idx) => { tbody.insertAdjacentHTML('beforeend', buildUnitRow(idx + 1, u, idx === 0)); }); }
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        function deleteItem(id) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ‡ط°ط§ ط§ظ„طµظ†ظپطں')) {
                let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
                const idx = items.findIndex(i => i.id === id); if(idx > -1) { items[idx].deleted = true; items[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_items_v1', JSON.stringify(items));
                renderItems();
            }
        }

        function openItemForm() {
            const tabItems = document.getElementById('tab-items');
            if(tabItems) {
                tabItems.querySelectorAll('.inner-tab-content').forEach(c => c.classList.remove('active'));
                tabItems.querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
                const productsTab = document.getElementById('inner-products');
                if(productsTab) productsTab.classList.add('active');
                const productsBtn = tabItems.querySelector('button[onclick*="inner-products"]');
                if(productsBtn) productsBtn.classList.add('active');
            }
            
            document.getElementById('form-mode-indicator').innerText = 'ط¥ط¶ط§ظپط© طµظ†ظپ ط¬ط¯ظٹط¯';
            document.getElementById('item-code').value = '';
            document.getElementById('item-name-input').value = '';
            document.getElementById('item-name-latin').value = '';
            document.getElementById('item-barcode').value = '';
            document.getElementById('item-brand').value = '';
            document.getElementById('item-active-toggle').checked = true;
            updateToggleLabel();
            document.getElementById('item-selling-price').value = '';
            document.getElementById('item-max-discount').value = '';
            document.getElementById('item-taxable').checked = false;
            document.getElementById('item-tax-rate').value = '';
            document.getElementById('item-tax-rate').disabled = true;
            document.querySelector('input[name="cost_method"][value="lastPurchase"]').checked = true;
            document.getElementById('item-opening-qty').value = '';
            document.getElementById('item-opening-value').value = '';
            document.getElementById('item-bin-location').value = '';
            document.getElementById('item-max-limit').value = '';
            document.getElementById('item-min-limit').value = '';
            document.getElementById('item-reorder-level').value = '';
            document.getElementById('item-base-unit').value = '';
            document.getElementById('item-bundle-factor').value = '1';
            document.getElementById('item-description').value = '';
            updateCategoryDropdowns();
            document.getElementById('units-matrix-body').innerHTML = buildUnitRow(1, { unitName: '', factor: 1 }, true);
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        function addUnitRow() {
            const tbody = document.getElementById('units-matrix-body');
            if(!tbody) return;
            const count = tbody.querySelectorAll('tr').length + 1;
            tbody.insertAdjacentHTML('beforeend', buildUnitRow(count, { unitName: '', factor: 1 }, false));
        }

        // ================= UNITS LOGIC =================
        function saveGlobalUnit() {
            const name = document.getElementById('unit-name-input').value;
            if(!name) { alert('ط£ط¯ط®ظ„ ط§ط³ظ… ط§ظ„ظˆط­ط¯ط©'); return; }
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            if(!units.includes(name)) { 
                units.push(name); 
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units)); 
            }
            document.getElementById('unit-name-input').value = '';
            renderGlobalUnitsList();
            updateUnitDropdowns();
        }

        function renderGlobalUnitsList() {
            const tbody = document.getElementById('units-table-body');
            if(!tbody) return;
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            tbody.innerHTML = '';
            units.forEach(u => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td><strong style="color:#38bdf8;">${u}</strong></td>
                    <td><button class="btn-text" style="color:var(--danger-color);" onclick="deleteGlobalUnit('${u}')">ط­ط°ظپ</button></td>
                </tr>`);
            });
        }

        function deleteGlobalUnit(name) {
            if(!confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ‡ط°ظ‡ ط§ظ„ظˆط­ط¯ط©طں')) return;
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            units = units.filter(u => u !== name);
            localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            renderGlobalUnitsList();
            updateUnitDropdowns();
        }

        function importLegacyUnits() {
            // Import units that were manually typed in older items
            let items = [];
            try { items = JSON.parse(localStorage.getItem('erp_items_v1')) || []; } catch(e) {}
            if(items.length === 0) return;
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            let added = false;
            items.forEach(i => {
                if(i.baseUnit && !units.includes(i.baseUnit)) { units.push(i.baseUnit); added = true; }
                if(i.units) {
                    i.units.forEach(u => {
                        if(u.unitName && !units.includes(u.unitName)) { units.push(u.unitName); added = true; }
                    });
                }
            });
            if(added) {
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            }
        }

        function updateUnitDropdowns(selectedValue = '') {
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            
            const options = units.map(u => `<option value="${u}">${u}</option>`).join('');
            const baseSelect = document.getElementById('item-base-unit');
            if(baseSelect) {
                const current = selectedValue || baseSelect.value;
                baseSelect.innerHTML = `<option value="">-- ط§ط®طھط± ط§ظ„ظˆط­ط¯ط© --</option>` + options;
                if(current) baseSelect.value = current;
            }
            
            // Also update any dynamic selects in the matrix
            document.querySelectorAll('.unit-name-input').forEach(sel => {
                if(sel.tagName === 'SELECT') {
                    const current = sel.value;
                    sel.innerHTML = `<option value="">-- ط§ط®طھط± ط§ظ„ظˆط­ط¯ط© --</option>` + options;
                    if(current) sel.value = current;
                }
            });
        }

        // ================= CATEGORY LOGIC =================
        function saveCategory() {
            const name = document.getElementById('cat-name-input').value;
            if(!name) { alert('ط£ط¯ط®ظ„ ط§ط³ظ… ط§ظ„ظپط¦ط©'); return; }
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
                if (!Array.isArray(cats)) cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            } catch(e) {
                cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            document.getElementById('cat-name-input').value = '';
            renderCategoriesList();
            updateCategoryDropdowns();
        }

        function renderCategoriesList() {
            const tbody = document.getElementById('categories-table-body');
            if(!tbody) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
                if (!Array.isArray(cats)) cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            } catch(e) {
                cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            }
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const kpi = document.getElementById('kpi-total-categories');
            if(kpi) kpi.innerText = cats.length + ' ظپط¦ط©';
            tbody.innerHTML = '';
            cats.forEach(c => {
                const count = items.filter(i => i.category === c).length;
                const subs = [...new Set(items.filter(i => i.category === c && i.subCategory).map(i => i.subCategory))];
                const subHtml = subs.length > 0 ? '<br><small style="color:#a855f7;">' + subs.join(' | ') + '</small>' : '';
                tbody.insertAdjacentHTML('beforeend', '<tr><td style="font-weight:bold; color:#a855f7;">' + c + subHtml + '</td><td><span style="background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px;">' + count + '</span></td><td><button class="btn-text" style="color:#38bdf8; margin-left:10px;" onclick="editCategory(\'' + c + '\')">âœڈï¸ڈ طھط¹ط¯ظٹظ„</button><button class="btn-text" style="color:var(--danger-color);" onclick="deleteCategory(\'' + c + '\')">ًں—‘ï¸ڈ ط­ط°ظپ</button></td></tr>');
            });
        }

        function editCategory(oldName) {
            const newName = prompt('طھط¹ط¯ظٹظ„ ط§ط³ظ… ط§ظ„ظپط¦ط©:', oldName);
            if(!newName || newName === oldName) return;
            let cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || [];
            const idx = cats.indexOf(oldName);
            if(idx > -1) { cats[idx] = newName; localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            // Update all items that use this category
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            items.forEach(i => { if(i.category === oldName) i.category = newName; });
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            renderCategoriesList();
            updateCategoryDropdowns();
            renderItems();
        }

        function deleteCategory(name) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ط§ظ„ظپط¦ط©طں')) {
                let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
                if (!Array.isArray(cats)) cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            } catch(e) {
                cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            }
                cats = cats.filter(c => c !== name);
                localStorage.setItem('erp_categories_v1', JSON.stringify(cats));
                renderCategoriesList();
                updateCategoryDropdowns();
            }
        }

        function updateCategoryDropdowns() {
            const select = document.getElementById('item-category');
            if(!select) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
                if (!Array.isArray(cats)) cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            } catch(e) {
                cats = ['ظ…ظˆط§ط¯ ط¨ظ†ط§ط،','ط£ط¯ظˆط§طھ طµط­ظٹط©','ظƒظ‡ط±ط¨ط§ط،'];
            }
            const val = select.value;
            select.innerHTML = '';
            cats.forEach(c => { select.insertAdjacentHTML('beforeend', '<option value="' + c + '">' + c + '</option>'); });
            if(cats.includes(val)) select.value = val;
        }


        // ================= COST CENTERS LOGIC =================
        function openCostCenterForm() {
            document.getElementById('costcenter-form').style.display = 'block';
            document.getElementById('btn-cancel-costcenter').style.display = 'block';
            document.getElementById('costcenter-id').value = '';
            document.getElementById('costcenter-code').value = 'CC-' + Date.now();
            document.getElementById('costcenter-name').value = '';
            document.getElementById('costcenter-desc').value = '';
        }

        function closeCostCenterForm() {
            document.getElementById('costcenter-form').style.display = 'none';
        }

        function saveCostCenter() {
            const id = document.getElementById('costcenter-id').value || document.getElementById('costcenter-code').value || 'CC-' + Date.now();
            const name = document.getElementById('costcenter-name').value;
            const desc = document.getElementById('costcenter-desc').value;
            
            if(!name) { alert('ط§ظ„ط±ط¬ط§ط، ط¥ط¯ط®ط§ظ„ ط§ط³ظ… ظ…ط±ظƒط² ط§ظ„طھظƒظ„ظپط©'); return; }

            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || [];
            } catch(e) { ccs = []; }
            
            const existingIdx = ccs.findIndex(c => c.id === id);
            
            const ccData = {
                id, name, desc,
                createdAt: Date.now()
            };

            if (existingIdx > -1) {
                ccs[existingIdx] = { ...ccs[existingIdx], ...ccData };
            } else {
                ccs.push(ccData);
            }

            localStorage.setItem('erp_jobs_v1', JSON.stringify(ccs));
            closeCostCenterForm();
            renderCostCenters();
        }

        function renderCostCenters() {
            const tbody = document.getElementById('costcenters-tbody');
            if(!tbody) return;
            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || [];
            } catch(e) { ccs = []; }
            
            tbody.innerHTML = '';
            ccs.forEach(c => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="color:#38bdf8; font-weight:bold;">${c.id}</td>
                    <td style="font-weight:bold;">${c.name || c.title || ''}</td>
                    <td>${c.desc || ''}</td>
                    <td>
                        <button class="btn-text" onclick="editCostCenter('${c.id}')">طھط¹ط¯ظٹظ„</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCostCenter('${c.id}')">ط­ط°ظپ</button>
                    </td>
                </tr>`);
            });
        }

        function editCostCenter(id) {
            let ccs = [];
            try { ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) { ccs = []; }
            const cc = ccs.find(c => c.id === id);
            if(!cc) return;
            
            document.getElementById('costcenter-id').value = cc.id;
            document.getElementById('costcenter-code').value = cc.id;
            document.getElementById('costcenter-name').value = cc.name || cc.title || '';
            document.getElementById('costcenter-desc').value = cc.desc || '';
            
            document.getElementById('costcenter-form').style.display = 'block';
            document.getElementById('btn-cancel-costcenter').style.display = 'block';
            document.getElementById('costcenter-form').scrollIntoView({ behavior: 'smooth' });
        }

        function deleteCostCenter(id) {
            if(confirm('ظ‡ظ„ ط£ظ†طھ ظ…طھط£ظƒط¯ ظ…ظ† ط­ط°ظپ ظ…ط±ظƒط² ط§ظ„طھظƒظ„ظپط©طں')) {
                let ccs = [];
                try { ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) { ccs = []; }
                ccs = ccs.filter(c => c.id !== id);
                localStorage.setItem('erp_jobs_v1', JSON.stringify(ccs));
                renderCostCenters();
            }
        }

        // ================= CURRENCIES LOGIC =================
        function getCurrencyRates() {
            let rates = {};
            try {
                rates = JSON.parse(localStorage.getItem('erp_currency_rates_v1')) || {};
            } catch(e) {}
            
            let manualUsd = 0;
            try {
                const sysSettings = JSON.parse(localStorage.getItem('erp_settings_v1')) || {};
                manualUsd = parseFloat(sysSettings.rateUSD || sysSettings.usd_rate || sysSettings.usdRate || sysSettings.exchangeRate || 0);
            } catch(e){}

            if (!manualUsd && typeof window.getExchangeRateForCurrency === 'function') {
                const sysUsd = window.getExchangeRateForCurrency('USD');
                if (sysUsd && sysUsd > 1) manualUsd = sysUsd;
            }

            if (!manualUsd) {
                manualUsd = parseFloat(localStorage.getItem('erp_rate_usd') || 0);
            }

            const defaults = {
                'SDG': 1,
                'USD': manualUsd > 0 ? manualUsd : 600,
                'SAR': 160,
                'AED': 163,
                'EGP': 12
            };
            
            for (let cur in defaults) {
                if (rates[cur] === undefined || (cur === 'USD' && manualUsd > 0)) {
                    rates[cur] = defaults[cur];
                }
            }
            return rates;
        }

        function renderCurrencyRates() {
            const tbody = document.getElementById('currencies-tbody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            const rates = getCurrencyRates();
            const currencyLabels = {
                'SDG': 'ط¬ظ†ظٹظ‡ ط³ظˆط¯ط§ظ†ظٹ',
                'USD': 'ط¯ظˆظ„ط§ط±',
                'SAR': 'ط±ظٹط§ظ„ ط³ط¹ظˆط¯ظٹ',
                'AED': 'ط¯ط±ظ‡ظ… ط¥ظ…ط§ط±ط§طھظٹ',
                'EGP': 'ط¬ظ†ظٹظ‡ ظ…طµط±ظٹ'
            };
            
            for (let cur in rates) {
                const tr = document.createElement('tr');
                const isBase = cur === 'SDG';
                const readonlyAttr = isBase ? 'readonly disabled style="background:rgba(0,0,0,0.1); color:#94a3b8; cursor:not-allowed;"' : '';
                const helperText = isBase ? '<span style="color:#94a3b8; font-size:0.75rem;">(ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© ظ„ظ„ظ†ط¸ط§ظ…)</span>' : '';
                
                tr.innerHTML = `
                    <td style="font-weight:bold; color:#a855f7;">${cur}</td>
                    <td>${currencyLabels[cur] || cur} ${helperText}</td>
                    <td>
                        <input type="number" step="any" class="cfg-input cur-rate-input" data-cur="${cur}" value="${rates[cur]}" ${readonlyAttr} style="width:120px; font-weight:bold; text-align:center; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: #fff; padding: 6px; border-radius: 6px;">
                    </td>
                `;
                tbody.appendChild(tr);
            }
        }

        function saveCurrencyRates() {
            const rates = getCurrencyRates();
            const inputs = document.querySelectorAll('.cur-rate-input');
            inputs.forEach(input => {
                const cur = input.getAttribute('data-cur');
                const val = parseFloat(input.value) || 1;
                rates[cur] = val;
            });
            
            localStorage.setItem('erp_currency_rates_v1', JSON.stringify(rates));
            alert('âœ… طھظ… ط­ظپط¸ ظ…ط¹ط§ظ…ظ„ط§طھ ط§ظ„ط¹ظ…ظ„ط§طھ ط¨ظ†ط¬ط§ط­!');
        }

        function populateSystemCurrencySelects() {
            const selects = document.querySelectorAll('.system-currency-select');
            
            const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
            const baseCur = cfg.currency || 'SDG';
            const foreignCur = cfg.foreignCurrency || 'USD';
            
            const currencyLabels = {
                'SDG': 'SDG - ط¬ظ†ظٹظ‡ ط³ظˆط¯ط§ظ†ظٹ (ظ…ط­ظ„ظٹ)',
                'USD': 'USD - ط¯ظˆظ„ط§ط± ط£ظ…ط±ظٹظƒظٹ',
                'SAR': 'SAR - ط±ظٹط§ظ„ ط³ط¹ظˆط¯ظٹ',
                'AED': 'AED - ط¯ط±ظ‡ظ… ط¥ظ…ط§ط±ط§طھظٹ',
                'EGP': 'EGP - ط¬ظ†ظٹظ‡ ظ…طµط±ظٹ',
                'QAR': 'QAR - ط±ظٹط§ظ„ ظ‚ط·ط±ظٹ'
            };
            
            selects.forEach(select => {
                const currentVal = select.value;
                select.innerHTML = '';
                
                const optBase = document.createElement('option');
                optBase.value = baseCur;
                optBase.textContent = currencyLabels[baseCur] || baseCur;
                select.appendChild(optBase);
                
                if (foreignCur && foreignCur !== baseCur) {
                    const optForeign = document.createElement('option');
                    optForeign.value = foreignCur;
                    optForeign.textContent = currencyLabels[foreignCur] || foreignCur;
                    select.appendChild(optForeign);
                }
                
                if (currentVal && (currentVal === baseCur || currentVal === foreignCur)) {
                    select.value = currentVal;
                } else {
                    select.value = baseCur;
                }
            });
        }

        function updateExchangeRateField(selectId, inputId, groupId, labelId) {
            const curSelect = document.getElementById(selectId);
            const exInput = document.getElementById(inputId);
            const exGroup = document.getElementById(groupId);
            const exLabel = document.getElementById(labelId);
            if (!curSelect || !exInput) return;
            
            const cur = curSelect.value || 'SDG';
            const rates = getCurrencyRates();
            const defaultRate = rates[cur] || 1;
            
            if (cur !== 'SDG' && cur !== '') {
                if (exGroup) exGroup.style.display = 'block';
                if (exLabel) exLabel.innerHTML = `ًں’µ ط³ط¹ط± ط§ظ„طµط±ظپ (ظ‚ظٹظ…ط© 1 ${cur} ظ…ظ‚ط§ط¨ظ„ ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ظ…ط­ظ„ظٹط© SDG - ظ…ط³ط¬ظ„ ط¨ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ: ${defaultRate.toLocaleString()})`;
                if (!exInput.value || exInput.value == 1 || exInput.value == 600) {
                    exInput.value = defaultRate;
                }
            } else {
                if (exGroup) exGroup.style.display = 'none';
                exInput.value = 1;
            }
        }

        function updateCustExchangeRate() {
            updateExchangeRateField('cust-currency', 'cust-ex-rate', 'cust-ex-rate-group', 'cust-ex-rate-label');
        }

        function updateSupExchangeRate() {
            updateExchangeRateField('sup-currency', 'sup-ex-rate', 'sup-ex-rate-group', 'sup-ex-rate-label');
        }

        function updateBankExchangeRate() {
            updateExchangeRateField('bank-currency', 'bank-ex-rate', 'bank-ex-rate-group', 'bank-ex-rate-label');
        }

        function updateSafeExchangeRate() {
            updateExchangeRateField('safe-currency', 'safe-ex-rate', 'safe-ex-rate-group', 'safe-ex-rate-label');
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                populateSystemCurrencySelects();
                updateCustExchangeRate();
                updateSupExchangeRate();
                updateBankExchangeRate();
                updateSafeExchangeRate();
            }, 100);
        });

        function goBackOrExit() {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                window.history.back();
            } else {
                window.location.href = 'index.php';
            }
        }
</script>
<script>
function deduplicateEntities() {
    try {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        let suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
        if(!Array.isArray(customers)) customers = [];
        if(!Array.isArray(suppliers)) suppliers = [];
        
        let uniqueNames = new Set();
        
        let newCusts = customers.filter(c => {
            if(!c || !c.name) return false;
            let n = c.name.trim().toLowerCase();
            if(uniqueNames.has(n)) return false;
            uniqueNames.add(n);
            return true;
        });
        
        let newSups = suppliers.filter(s => {
            if(!s || !s.name) return false;
            let n = s.name.trim().toLowerCase();
            if(uniqueNames.has(n)) return false;
            uniqueNames.add(n);
            return true;
        });
        
        if (newCusts.length !== customers.length) localStorage.setItem('erp_customers_v1', JSON.stringify(newCusts));
        if (newSups.length !== suppliers.length) localStorage.setItem('erp_suppliers_v1', JSON.stringify(newSups));
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', () => {
    deduplicateEntities();
    // Units Init
    importLegacyUnits();
    renderGlobalUnitsList();
    updateUnitDropdowns();
    
    setTimeout(() => {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get('tab');
        if (tabParam) {
            const tabId = 'tab-' + tabParam;
            const targetBtn = document.querySelector(`button[onclick*="'${tabId}'"]`);
            if (targetBtn) {
                targetBtn.click();
            }
        }
    }, 100);
});
</script>
</body>
</html>

