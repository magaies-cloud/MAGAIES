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
    <script src="js/bban_auto_helper.js"></script>
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
            border: 3.8px solid var(--tab-border-color, rgba(255, 255, 255, 0.2)); /* زيادة سمك الحواف بـ 1.5 مرة (3.8px) */
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

        /* تأثير لمعة الجليتر البرّاقة الجذابة في الداخل */
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

        /* الإيماءة الضوئية الساطعة العابرة على سطح الزر */
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

        /* تحريك الأيقونة داخل الزر عند التمرير */
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

        /* حالة التحديد الزر النشط (Active Tab) مع الحفاظ الكلي على الإضاءة العاكسة الخاطفة للأبصار */
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

        /* --- تحسين وتكبير الخطوط داخل جميع الحقول والمستندات في البيانات الأساسية --- */
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
            font-size: 1.25rem !important;  /* تكبير الخط داخل الحقول بدرجات مضاعفة (حوالي 20px) ليكون واضحاً وجلياً */
            font-weight: 700 !important;    /* خط بارز وعريض لسهولة القراءة والوضوح */
            min-height: 48px !important;    /* ضبط مقاس وتناسق ارتفاع الحقل مع حجم الخط المكبر */
            padding: 10px 16px !important;  /* حشو داخلي متناسب مع مقاس الحقول */
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

        /* --- تكبير خطوط الأرقام (في الحقول والجداول والأرصدة) بدرجتين إضافيتين زيادة (حجم فائق البروز والوضوح) --- */
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
            font-size: 1.55rem !important;   /* تكبير الأرقام بدرجتين إضافيتين زيادة (~25px) لبروز ووضوح فائق ومريح للعين */
            font-weight: 900 !important;     /* خط عريض وفائق البروز للأرقام */
            font-family: 'Outfit', 'Tajawal', sans-serif !important;
            font-variant-numeric: tabular-nums !important;
            letter-spacing: 0.5px !important;
            min-height: 52px !important;
        }

        table.pro-table td,
        table.pro-table td strong,
        .tot-val, 
        .val {
            font-size: 1.28rem !important;   /* تكبير أرقام الخلايا والأرصدة والحدود في الجداول (~20.5px) */
            font-weight: 800 !important;
            font-variant-numeric: tabular-nums !important;
        }

        table input[type="number"],
        table.pro-table input[type="number"] {
            font-size: 1.38rem !important;   /* تكبير أرقام الحقول داخل الجداول والمصفوفات (~22px) */
            font-weight: 900 !important;
            font-family: 'Outfit', 'Tajawal', sans-serif !important;
            min-width: 140px !important;
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
        
        /* تأثيرات حركية وضوئية للأزرار */
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
            <button class="md-tab-btn tab-warehouses active" onclick="openTab(event, 'tab-warehouses')"><span class="md-tab-icon">🏢</span> المخازن</button>
            <button class="md-tab-btn tab-customers" onclick="openTab(event, 'tab-customers')"><span class="md-tab-icon">👥</span> العملاء</button>
            <button class="md-tab-btn tab-suppliers" onclick="openTab(event, 'tab-suppliers')"><span class="md-tab-icon">🏭</span> الموردين</button>
            <button class="md-tab-btn tab-banks" onclick="openTab(event, 'tab-banks')"><span class="md-tab-icon">🏦</span> البنوك</button>
            <button class="md-tab-btn tab-safes" onclick="openTab(event, 'tab-safes')"><span class="md-tab-icon">💰</span> الخزن</button>
            <button class="md-tab-btn tab-items" onclick="openTab(event, 'tab-items')"><span class="md-tab-icon">📦</span> الأصناف والوحدات</button>
            <button class="md-tab-btn tab-employees" onclick="openTab(event, 'tab-employees')"><span class="md-tab-icon">👤</span> الموظفين</button>
            <button class="md-tab-btn tab-costcenters" onclick="openTab(event, 'tab-costcenters')"><span class="md-tab-icon">📊</span> مراكز التكلفة</button>

            <div style="flex: 1;"></div>
            <button class="md-tab-btn tab-exit" onclick="goBackOrExit()"><span class="md-tab-icon">🚪</span> خروج / رجوع</button>
        </aside>

        <main class="md-content-area">
            
            <div id="tab-warehouses" class="md-tab-content active">
                <div class="md-card">
                    <div class="md-card-header">
                        <h3 class="md-card-title">🏢 قائمة المخازن</h3>
                        <button class="btn-add" onclick="openWarehouseForm()">+ إضافة مخزن جديد</button>
                    </div>
                    
                    <div id="warehouse-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">إضافة/تعديل مخزن</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-warehouse" onclick="cancelEditWarehouse()">إلغاء التعديل</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="warehouse-id">
                            <div class="input-group"><label>اسم المخزن</label><input type="text" id="warehouse-name" placeholder="مثال: مخزن مقاييس الرئيسي"></div>
                            <div class="input-group"><label>النوع</label><select id="warehouse-type"><option value="مستودع رئيسي">مستودع رئيسي</option><option value="معرض (نقطة بيع)">معرض (نقطة بيع)</option><option value="مخزن بضاعة تالفة">مخزن بضاعة تالفة</option></select></div>
                            <div class="input-group"><label>درجة السرية</label><select id="warehouse-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                        </div>
                        <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveWarehouse()">💾 حفظ البيانات</button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>اسم المخزن</th><th>النوع</th><th>الإجراء</th></tr></thead>
                            <tbody id="warehouses-tbody"><!-- سيتم إضافة المخازن هنا --></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-customers" class="md-tab-content">
                <div class="md-card">
                    <div id="customer-form" class="md-card" style="display: block; margin-bottom: 25px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; margin-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 1.1rem; color: var(--primary-accent); display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 1.2rem;">👥</span> إضافة / تعديل بيانات عميل
                            </h4>
                            <button onclick="clearCustomerForm()" class="btn-action" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">🔄 تفريغ الحقول (عميل جديد)</button>
                        </div>
                        <div class="form-grid">
                            <div class="input-group" style="grid-column: span 2; min-width: 280px;"><label style="font-weight: 800; color: #38bdf8;">👤 اسم العميل / الشركة</label><input type="text" id="cust-name" placeholder="مثال: شركة النيل للتجارة والاستثمار" style="font-size: 1.05rem; font-weight: 800; border-color: rgba(56,189,248,0.4); background: rgba(56,189,248,0.05);"></div>
                            <div class="input-group"><label>📞 رقم الهاتف</label><input type="text" id="cust-phone" placeholder="00249123000000" style="width: 100%; font-size: 1rem; font-weight: 700; text-align: center; direction: ltr;"></div>
                            <div class="input-group"><label>💬 رقم الواتساب</label><input type="text" id="cust-whatsapp" placeholder="00249123000000" style="width: 100%; font-size: 1rem; font-weight: 700; text-align: center; direction: ltr;"></div>
                            <div class="input-group"><label>✉️ الايميل (البريد الإلكتروني)</label><input type="email" id="cust-email" placeholder="example@email.com" style="width: 100%;"></div>
                            <div class="input-group" style="grid-column: span 1;"><label>📍 العنوان</label><input type="text" id="cust-address" placeholder="مثال: الخرطوم، مقاييس" style="width: 100%;"></div>
                            <div class="input-group"><label>العملة</label><select id="cust-currency" class="system-currency-select" onchange="updateCustExchangeRate()"></select></div>
                            <div class="input-group" id="cust-ex-rate-group" style="display: none;"><label id="cust-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف (مقابل المحلية)</label><input type="number" id="cust-ex-rate" step="any" value="1" placeholder="سعر الصرف مقابل SDG" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group"><label>الرقم الضريبي</label><input type="text" id="cust-tax" placeholder="الرقم الضريبي للعميل"></div>
                            <input type="hidden" id="cust-account-no" value="">
                            <div class="input-group">
                                <label>الرصيد الافتتاحي <small style="color:#f97316; font-weight:normal;">(موجب = عليه | سالب = له (يريد منا))</small></label>
                                <input type="number" id="cust-opening-balance" step="any" placeholder="0.00" style="direction:ltr; text-align:center;">
                                <input type="hidden" id="cust-opening-debit" value="0">
                                <input type="hidden" id="cust-opening-credit" value="0">
                            </div>
                            <div class="input-group"><label style="color: var(--danger-color);">حد الائتمان (أقصى سقف)</label><input type="number" id="cust-limit" placeholder="المبلغ المسموح للدين"></div>
                            <div class="input-group"><label>درجة السرية</label><select id="cust-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                            <div class="input-group"><label>حالة العميل (القائمة السوداء)</label><select id="cust-status" style="font-weight: bold;"><option value="active" style="color: var(--success-color);">🟢 نشط (عادي)</option><option value="vip" style="color: var(--primary-accent);">🌟 VIP (مميز)</option><option value="blacklisted" style="color: var(--danger-color);">🔴 قائمة سوداء (محظور)</option></select></div>
                            <div class="input-group"><label>كود العميل</label><input type="text" id="cust-id" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;" placeholder="تلقائي عند الحفظ"></div>
                            <div class="input-group"><label>الشخص المسؤول</label><input type="text" id="cust-contact" placeholder="اسم المندوب / الشخص المسؤول"></div>
                            <div class="input-group" style="grid-column: 1 / -1;"><label>ملاحظات إضافية</label><textarea id="cust-notes" placeholder="أضف أي ملاحظات عن العميل هنا..." style="width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-primary); outline: none; font-family: inherit; resize: none; height: 50px; box-sizing: border-box;"></textarea></div>

                            <!-- بيانات الحساب البنكي للعميل -->
                            <div style="grid-column: 1 / -1; margin-top: 10px; padding: 15px; border: 1px solid var(--glass-border); border-radius: 12px; background: rgba(0,0,0,0.1);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h4 style="margin: 0; color: var(--primary-accent); display:flex; align-items:center; gap:8px;"><span>🏦</span> بيانات الحسابات البنكية للعميل</h4>
                                    <button type="button" onclick="addCustomerBankAccount()" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: bold;">+ إضافة حساب آخر</button>
                                </div>
                                <div id="cust-bank-accounts-container" style="display: flex; flex-direction: column; gap: 15px;">
                                    <!-- Bank accounts will be injected here -->
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn-add" style="background: var(--success-color); padding: 10px 25px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="saveCustomer()">💾 حفظ العميل</button>
                        </div>
                    </div>

                    <!-- Customer Search Bar -->
                    <div style="margin-bottom: 15px; display: flex; align-items: center;">
                        <div class="input-group" style="width: 100%; max-width: 500px; position: relative; margin-bottom: 0;">
                            <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; pointer-events: none;">🔍</span>
                            <input type="text" id="customer-search-input" onkeyup="filterCustomers()" placeholder="ابحث ذكياً باسم العميل، رقم الهاتف، أو الإيميل..." style="padding-right: 45px; border-radius: 12px; border: 1px solid rgba(56,189,248,0.3); background: rgba(0,0,0,0.3); font-size: 1rem; color: #fff; width: 100%; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);">
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th style="width: 70px; text-align: center;">كود</th><th style="width: 35%; min-width: 250px; text-align: right;">اسم العميل / الشركة</th><th style="width: 115px; text-align: center;">الهاتف</th><th style="width: 90px; text-align: center;">الحالة</th><th style="width: 130px; text-align: center;">الرصيد الحالي</th><th style="width: 120px; text-align: center;">حد الائتمان</th><th style="width: 120px; text-align: center;">المسؤول</th><th style="width: 60px; text-align: center;">السرية</th><th style="width: 100px; text-align: center;">الإجراء</th></tr></thead>
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
                                <span style="font-size: 1.2rem;">🏭</span> إضافة / تعديل بيانات مورد
                            </h4>
                            <button onclick="clearSupplierForm()" class="btn-action" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">🔄 تفريغ الحقول (مورد جديد)</button>
                        </div>
                        <div class="form-grid">
                            <div class="input-group" style="grid-column: span 2; min-width: 280px;"><label style="font-weight: 800; color: #38bdf8;">🏭 اسم المورد / المصنع / الشركة</label><input type="text" id="sup-name" placeholder="مثال: شركة جياد للصناعات الهندسية" style="font-size: 1.05rem; font-weight: 800; border-color: rgba(56,189,248,0.4); background: rgba(56,189,248,0.05);"></div>
                            <div class="input-group"><label>📞 رقم الهاتف</label><input type="text" id="sup-phone" placeholder="00249123000000" style="width: 100%; font-size: 1rem; font-weight: 700; text-align: center; direction: ltr;"></div>
                            <div class="input-group"><label>💬 رقم الواتساب</label><input type="text" id="sup-whatsapp" placeholder="00249123000000" style="width: 100%; font-size: 1rem; font-weight: 700; text-align: center; direction: ltr;"></div>
                            <div class="input-group"><label>✉️ الايميل (البريد الإلكتروني)</label><input type="email" id="sup-email" placeholder="example@email.com" style="width: 100%;"></div>
                            <div class="input-group" style="grid-column: span 1;"><label>📍 العنوان</label><input type="text" id="sup-address" placeholder="موقع المصنع / المعرض" style="width: 100%;"></div>
                            <div class="input-group"><label>العملة</label><select id="sup-currency" class="system-currency-select" onchange="updateSupExchangeRate()"></select></div>
                            <div class="input-group" id="sup-ex-rate-group" style="display: none;"><label id="sup-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف (مقابل المحلية)</label><input type="number" id="sup-ex-rate" step="any" value="1" placeholder="سعر الصرف مقابل SDG" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group"><label>الرقم الضريبي</label><input type="text" id="sup-tax" placeholder="الرقم الضريبي للمورد"></div>
                            <input type="hidden" id="sup-account-no" value="">
                            <div class="input-group">
                                <label>الرصيد الافتتاحي <small style="color:#f97316; font-weight:normal;">(موجب = له | سالب = عليه (نريد منه))</small></label>
                                <input type="number" id="sup-opening-balance" step="any" placeholder="0.00" style="direction:ltr; text-align:center;">
                                <input type="hidden" id="sup-opening-debit" value="0">
                                <input type="hidden" id="sup-opening-credit" value="0">
                            </div>
                            <div class="input-group"><label style="color: var(--danger-color);">حد الائتمان (أقصى سقف)</label><input type="number" id="sup-limit" placeholder="أقصى سقف"></div>
                            <div class="input-group"><label>درجة السرية</label><select id="sup-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                            <div class="input-group"><label>حالة المورد</label><select id="sup-status" style="font-weight: bold;"><option value="active" style="color: var(--success-color);">🟢 مورد نشط</option><option value="blacklisted" style="color: var(--danger-color);">🔴 قائمة سوداء</option></select></div>
                            <div class="input-group"><label>كود المورد</label><input type="text" id="sup-id" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;" placeholder="تلقائي عند الحفظ"></div>
                            <div class="input-group" style="grid-column: 1 / -1;"><label>ملاحظات إضافية</label><textarea id="sup-notes" placeholder="أي ملاحظات حول المورد..." style="width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-primary); outline: none; font-family: inherit; resize: none; height: 50px; box-sizing: border-box;"></textarea></div>
                        </div>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn-add" style="background: var(--success-color); padding: 10px 25px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="saveSupplier()">💾 حفظ بيانات المورد</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="tab-suppliers-table">
                            <thead><tr><th style="width: 70px; text-align: center;">كود</th><th style="width: 38%; min-width: 260px; text-align: right;">اسم المورد / الشركة</th><th style="width: 115px; text-align: center;">الهاتف</th><th style="width: 90px; text-align: center;">الحالة</th><th style="width: 130px; text-align: center;">الرصيد الحالي</th><th style="width: 120px; text-align: center;">حد الائتمان</th><th style="width: 60px; text-align: center;">السرية</th><th style="width: 100px; text-align: center;">الإجراء</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Banks Tab -->
            <div id="tab-banks" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header"><h3 class="md-card-title">🏦 البنوك</h3><button class="btn-add" onclick="openBankForm()">+ إضافة حساب بنكي</button></div>
                    <div id="bank-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="bank-form-title">إضافة حساب بنكي</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">✖ إلغاء</button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="bank-code" placeholder="رقم الحساب أو الخزينة"></div>
                            <div class="input-group"><label>اسم البنك / الحساب</label><input type="text" id="bank-name" placeholder="مثال: بنك الخرطوم (بنكك)" oninput="autoUpdateMasterBankIBAN()"></div>
                            <div class="input-group" id="bank-branch-group"><label>الفرع</label><input type="text" id="bank-branch" placeholder="مثال: فرع مقاييس"></div>
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="bank-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد الافتتاحي</label><input type="date" id="bank-opening-date"></div>
                            <div class="input-group"><label>رقم الحساب البنكي / الأستاذ</label><input type="text" id="bank-gl-account" placeholder="أدخل رقم الحساب (يولد الآيبان تلقائياً)" oninput="autoUpdateMasterBankIBAN()"></div>
                            <div class="input-group bank-only-field"><label>IBAN</label><input type="text" id="bank-iban" placeholder="SA..."></div>
                            <div class="input-group bank-only-field"><label>SWIFT</label><input type="text" id="bank-swift" placeholder="BOKS..."></div>
                            
                            <div class="input-group"><label>العملة</label><select id="bank-currency" class="system-currency-select" onchange="updateBankExchangeRate()"></select></div>
                            <div class="input-group" id="bank-ex-rate-group" style="display: none;"><label id="bank-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف (مقابل المحلية)</label><input type="number" id="bank-ex-rate" step="any" value="1" placeholder="سعر الصرف مقابل SDG" style="font-weight:800; color:#38bdf8;"></div>
                            
                            
                            <div class="input-group"><label>درجة السرية</label><select id="bank-confidentiality"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع المسموحة</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="bank-inactive"> عدم تفعيل الحساب</label>
                            
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">✖ إلغاء</button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveBank()">💾 حفظ</button></div>
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
                    <div class="md-card-header"><h3 class="md-card-title">🏦 الخزن</h3><button class="btn-add" onclick="openSafeForm()">+ إضافة خزينة</button></div>
                    <div id="safe-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="safe-form-title">إضافة خزينة</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">✖ إلغاء</button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="safe-code" placeholder="رقم الحساب أو الخزينة"></div>
                            <div class="input-group"><label>الاسم</label><input type="text" id="safe-name" placeholder="مثال: الخزينة الرئيسية"></div>
                            
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="safe-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد الافتتاحي</label><input type="date" id="safe-opening-date"></div>
                            <div class="input-group"><label>رقم حساب الأستاذ (GL)</label><input type="text" id="safe-gl-account" placeholder="مثال: 110101"></div>
                            
                            
                            <div class="input-group" id="safe-responsible-group"><label>المسؤول</label><select id="safe-responsible"><option value="">-- اختر --</option></select></div>
                            <div class="input-group"><label>العملة</label><select id="safe-currency" class="system-currency-select" onchange="updateSafeExchangeRate()"></select></div>
                            <div class="input-group" id="safe-ex-rate-group" style="display: none;"><label id="safe-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف (مقابل المحلية)</label><input type="number" id="safe-ex-rate" step="any" value="1" placeholder="سعر الصرف مقابل SDG" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group" id="safe-max-limit-group"><label>الحد الأعلى</label><input type="number" id="safe-max-limit" placeholder="0.00"></div>
                            <div class="input-group" id="safe-min-limit-group"><label>الحد الأدنى</label><input type="number" id="safe-min-limit" placeholder="0.00"></div>
                            <div class="input-group"><label>درجة السرية</label><select id="safe-confidentiality"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع المسموحة</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-inactive"> عدم تفعيل الحساب</label>
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-modifier"> المعدل</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">✖ إلغاء</button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveSafe()">💾 حفظ</button></div>
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
                    <!-- Header and redundant KPIs removed to utilize vertical space -->
                    <!-- Quick Actions moved to the item form -->

                    <!-- Main Inner Tabs removed as requested -->

                    <div id="inner-products" class="inner-tab-content active">
                        
                        <!-- Premium Item Form Modal -->
                        <div id="item-form" class="md-card" style="display: none; padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                            <!-- HEADER BAR -->
                            <div style="background:linear-gradient(135deg,rgba(56,189,248,0.2),rgba(15,23,42,0.95)); padding:12px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; border-bottom:1px solid rgba(56,189,248,0.3);">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <div style="width:50px; height:50px; background:linear-gradient(135deg, #38bdf8, #818cf8); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; box-shadow: 0 4px 15px rgba(56,189,248,0.4);">📦</div>
                                    <div><h3 id="form-mode-indicator" style="margin:0; color:#fff; font-size:1.4rem; font-weight:800; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">إضافة صنف جديد</h3><small style="color:#cbd5e1; font-size: 0.85rem;">إدارة بطاقة الصنف وتفاصيل التسعير والوحدات</small></div>
                                </div>
                                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items: center;">
                                    <!-- Low Stock Mini -->
                                    <div class="neon-glass-panel neon-border-danger" style="padding: 5px 12px; display: flex; align-items: center; gap: 8px; border-radius: 8px; border: 1px solid rgba(244,63,94,0.3); margin-left: 10px;">
                                        <span style="font-size:1rem; background: rgba(244,63,94,0.1); width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">⚠️</span>
                                        <div>
                                            <span style="color:var(--text-secondary); font-size:0.7rem; display: block; line-height: 1; margin-bottom: 3px;">النواقص</span>
                                            <strong id="kpi-low-stock" class="neon-text-danger" style="font-size:0.95rem; font-weight:800; line-height: 1;">0 صنف</strong>
                                        </div>
                                    </div>
                                    
                                    <button onclick="openInnerTab(event, 'inner-categories')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(88,28,135,0.6)); border: 1px solid rgba(168,85,247,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">🗂️</span> أنواع الأصناف
                                    </button>
                                    <button onclick="openInnerTab(event, 'inner-units')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">📐</span> الوحدات
                                    </button>
                                    <button onclick="alert('جاري التجهيز للطباعة...')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(52,211,153,0.2), rgba(6,78,59,0.6)); border: 1px solid rgba(52,211,153,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">🖨️</span> طباعة باركود
                                    </button>
                                    <button class="btn-add hover-glow" style="background:linear-gradient(135deg, var(--success-color), #059669); padding:10px 25px; font-size: 1rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.3); border: 1px solid rgba(16,185,129,0.5); color: #fff; cursor: pointer;" onclick="saveItem()">💾 حفظ الصنف</button>
                                    <button onclick="closeItemForm()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.5); padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:bold; transition: all 0.3s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">✖ إلغاء</button>
                                </div>
                            </div>
                            
                            <div style="padding:25px; background: rgba(0,0,0,0.2);">

                                <div class="split-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
                                    
                                    <!-- SEC 1: General -->
                                    <div class="neon-glass-panel split-panel" style="padding: 20px; border-top: 3px solid #38bdf8; background: linear-gradient(to bottom, rgba(56,189,248,0.05), transparent);">
                                        <div style="display:flex; align-items:center; gap:10px; margin-bottom: 20px;">
                                            <span style="font-size:1.5rem;">📋</span><h4 style="margin:0; color:#38bdf8; font-weight: 800;">البيانات العامة والتعريف</h4>
                                        </div>
                                        
                                        <div style="display:flex; gap:20px;">
                                            <div id="item-qr-box" style="width:100px; height:100px; border:2px dashed rgba(56,189,248,0.4); border-radius:14px; display:flex; flex-direction:column; justify-content:center; align-items:center; background:rgba(0,0,0,0.4); flex-shrink:0; transition: all 0.3s;"><span style="font-size:2rem;">📱</span><span style="font-size:0.75rem; color:#38bdf8; margin-top:5px; font-weight:bold;">QR تلقائي</span></div>
                                            <div class="form-grid" style="flex:1; grid-template-columns: 1fr;">
                                                <div class="input-group"><label style="color:#fff;">اسم الصنف <span style="color:#ef4444;">*</span></label><input type="text" id="item-name-input" placeholder="مثال: سيخ 3 لنيه ماستر" style="border-color: rgba(56,189,248,0.5); background: rgba(0,0,0,0.3);"></div>
                                                <div style="display:flex; gap:10px;">
                                                    <div class="input-group" style="flex:1;"><label>كود الصنف (SKU)</label><input type="text" id="item-code" placeholder="تلقائي إذا تُرك فارغاً" style="color:#38bdf8; font-weight:bold;"></div>
                                                    <div class="input-group" style="flex:1;"><label>الاسم اللاتيني</label><input type="text" id="item-name-latin" placeholder="Rebar 3 Linia"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-grid" style="margin-top: 15px;">
                                            <div class="input-group"><label>الباركود</label><input type="text" id="item-barcode" placeholder="امسح بالليزر..."></div>
                                            <div class="input-group"><label>الماركة / المنشأ</label><input type="text" id="item-brand" placeholder="مثال: تركي، جياد"></div>
                                            <div class="input-group"><label>الفئة الرئيسية</label><div style="display:flex; gap:5px;"><select id="item-category" style="flex:1;"></select><button class="btn-text" style="color:#a855f7; background:rgba(168,85,247,0.1); border-radius:8px; padding:0 10px;" onclick="quickAddCategory()" title="إضافة فئة">➕</button></div></div>
                                            <input type="hidden" id="item-sub-category" value="">
                                            <div class="input-group"><label>طبيعة الصنف</label><select id="item-nature"><option value="مخزني">📦 مخزني (يتم جرده)</option><option value="خدمي">🔧 خدمي (لا يجرد)</option><option value="تجميعي">🏗️ تجميعي</option></select></div>
                                            <div class="input-group"><label>حالة التفعيل</label><div style="display:flex; align-items:center; gap:10px; padding:10px; background: rgba(0,0,0,0.2); border-radius: 8px;"><label class="toggle-sw"><input type="checkbox" id="item-active-toggle" checked onchange="updateToggleLabel()"><span class="toggle-sl"></span></label><span id="item-active-label" style="color:var(--success-color); font-weight:bold; font-size:0.9rem;">🟢 نشط</span></div></div>
                                        </div>
                                    </div>

                                    <!-- Right Column Stack -->
                                    <div class="split-panel" style="display: flex; flex-direction: column; gap: 20px;">
                                        
                                        <!-- SEC 2: Pricing & Tax -->
                                        <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #34d399; background: linear-gradient(to bottom, rgba(52,211,153,0.05), transparent);">
                                            <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                <span style="font-size:1.5rem;">💰</span><h4 style="margin:0; color:#34d399; font-weight: 800;">التسعير والضرائب</h4>
                                            </div>
                                            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                                                <div class="input-group"><label>سعر البيع الافتراضي (SDG)</label><input type="number" id="item-selling-price" placeholder="0.00" style="font-weight:bold; color:var(--success-color); border-color: rgba(52,211,153,0.4); font-size: 1.1rem; background: rgba(52,211,153,0.05);"></div>
                                                <div class="input-group"><label>الحد الأقصى للخصم %</label><input type="number" id="item-max-discount" placeholder="0" max="100"></div>
                                                <div class="input-group"><label>خاضع للضريبة</label><div style="padding:10px; background:rgba(0,0,0,0.2); border-radius:8px;"><label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0;"><input type="checkbox" id="item-taxable" onchange="document.getElementById('item-tax-rate').disabled=!this.checked" style="width:18px; height:18px; accent-color:#34d399;"> نعم، خاضع</label></div></div>
                                                <div class="input-group"><label>نسبة الضريبة %</label><input type="number" id="item-tax-rate" placeholder="0" disabled></div>
                                            </div>
                                        </div>

                                        <!-- SEC 3 & 4: Costing & Warehousing in one row inside column -->
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                            <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #f59e0b; background: linear-gradient(to bottom, rgba(245,158,11,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                    <span style="font-size:1.2rem;">📊</span><h5 style="margin:0; color:#f59e0b; font-weight: 800;">التكلفة والأرصدة</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px; grid-column: 1 / -1;"><label style="color:#f59e0b;">طريقة التكلفة</label>
                                                    <div style="display:flex; gap:10px; flex-wrap:wrap; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px;">
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="lastPurchase" checked style="accent-color:#f59e0b;"> آخر شراء</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="highest" style="accent-color:#f59e0b;"> الأعلى</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="opening" style="accent-color:#f59e0b;"> الافتتاحي</label>
                                                    </div>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>الكمية الافتتاحية</label><input type="number" id="item-opening-qty" placeholder="0"></div>
                                                <div class="input-group"><label>تكلفة الافتتاحي</label><input type="number" id="item-opening-value" placeholder="0.00"></div>
                                            </div>

                                            <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #ef4444; background: linear-gradient(to bottom, rgba(239,68,68,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                    <span style="font-size:1.2rem;">🏭</span><h5 style="margin:0; color:#ef4444; font-weight: 800;">حدود المخزون</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>مكان الصنف / الرف</label><input type="text" id="item-bin-location" placeholder="ممر 3، رف ب"></div>
                                                <div style="display:flex; gap:10px; margin-bottom: 10px;">
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأعلى</label><input type="number" id="item-max-limit" placeholder="500"></div>
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأدنى</label><input type="number" id="item-min-limit" placeholder="20"></div>
                                                </div>
                                                <div class="input-group"><label style="color:#f59e0b;">حد إعادة الطلب</label><input type="number" id="item-reorder-level" placeholder="50"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- SEC 5: Unit Matrix (Full Width) -->
                                <div class="neon-glass-panel" style="margin-top: 20px; padding: 25px; border-top: 3px solid #a855f7; background: linear-gradient(to bottom, rgba(168,85,247,0.05), transparent); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                                    <div style="display:flex; align-items:center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span style="font-size:1.8rem; filter: drop-shadow(0 0 8px rgba(168,85,247,0.5));">📐</span><h4 style="margin:0; color:#a855f7; font-weight: 800; font-size: 1.3rem; white-space: nowrap;">مصفوفة الوحدات والتحويل</h4>
                                        </div>
                                        <div style="display:flex; align-items:center; justify-content: flex-start; gap:15px; background: rgba(0,0,0,0.3); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(168,85,247,0.3); box-shadow: inset 0 2px 10px rgba(0,0,0,0.2); flex: 1; min-width: 300px;">
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 1rem; white-space: nowrap;">الوحدة الأساسية <span style="color:#ef4444;">*</span></label>
                                            <div style="display:flex; align-items:center; gap:5px;">
                                                <select id="item-base-unit" onchange="updateBaseUnitRow()" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.1rem; padding: 5px; outline:none; width: 150px; text-align: center; transition: all 0.3s;" onfocus="this.style.borderBottomColor='#d8b4fe'">
                                                    <option value="">-- اختر الوحدة --</option>
                                                </select>
                                                <button class="btn-text" style="color:#38bdf8; background:rgba(56,189,248,0.1); border-radius:8px; padding:0 8px; font-size:0.9rem;" onclick="quickAddUnit()" title="إضافة وحدة سريعة">➕</button>
                                            </div>
                                            <div style="width: 1px; height: 25px; background: rgba(168,85,247,0.3); margin: 0 5px;"></div>
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 1rem;">الشيلة</label>
                                            <input type="number" id="item-bundle-factor" placeholder="مثال: 15" value="1" min="1" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.1rem; padding: 5px; outline:none; width: 80px; text-align: center; transition: all 0.3s;" onfocus="this.style.borderBottomColor='#d8b4fe'">
                                        </div>
                                    </div>
                                    
                                    <div style="background:rgba(15,23,42,0.8); border-radius:14px; border:1px solid rgba(168,85,247,0.2); overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(168,85,247,0.05);">
                                            <span style="color:#cbd5e1; font-weight: 600;">جدول تحويل الوحدات وتسعيرها (المعادل بالوحدة الأساسية)</span>
                                            <button class="btn-text" style="color:#fff; font-weight:bold; background:linear-gradient(135deg, #a855f7, #9333ea); padding:8px 15px; border-radius:8px; box-shadow: 0 4px 10px rgba(168,85,247,0.3); border: none; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" onclick="addUnitRow()"><span style="font-size: 1.2rem;">+</span> إضافة وحدة جديدة</button>
                                        </div>
                                        <div class="table-responsive" style="overflow-x: auto;">
                                            <table class="pro-table" style="width: 100%; min-width: 1400px;">
                                                <thead><tr style="background: rgba(0,0,0,0.2);">
                                                    <th style="padding: 15px; width: 4%; text-align: center;">م</th>
                                                    <th style="padding: 15px; width: 14%;">اسم الوحدة</th>
                                                    <th style="padding: 15px; width: 8%; text-align: center;">المعادل (Factor)</th>
                                                    <th style="padding: 15px; width: 12%;">باركود</th>
                                                    <th style="padding: 15px; width: 16%; color: #34d399;">سعر نقدي</th>
                                                    <th style="padding: 15px; width: 16%; color: #60a5fa;">سعر آجل</th>
                                                    <th style="padding: 15px; width: 16%; color: #f87171;">أقل سعر</th>
                                                    <th style="padding: 15px; width: 7%;">وزن (Kg)</th>
                                                    <th style="padding: 15px; width: 7%; text-align: center;">إجراء</th>
                                                </tr></thead>
                                                <tbody id="units-matrix-body">
                                                    <tr style="background:rgba(52,211,153,0.05); border-right:4px solid var(--success-color); transition: background 0.3s;" onmouseover="this.style.background='rgba(52,211,153,0.1)'" onmouseout="this.style.background='rgba(52,211,153,0.05)'">
                                                        <td style="text-align: center; font-weight: bold; color: var(--text-secondary);">1</td>
                                                        <td><input type="text" id="base-unit-name-display" value="" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; font-size: 1.1rem; outline: none;"></td>
                                                        <td><input type="number" value="1" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; font-size:1.2rem; outline: none;"></td>
                                                        <td><input type="text" placeholder="باركود الوحدة..." style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:10px; border-radius:8px; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#34d399; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#34d399'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#60a5fa; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#60a5fa'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#f87171; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#f87171'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:10px; border-radius:8px; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td style="text-align: center;"><span style="font-size:0.8rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); border: 1px solid rgba(52,211,153,0.4); padding:6px 12px; border-radius:6px; display: inline-block; box-shadow: 0 2px 5px rgba(52,211,153,0.2);">وحدة أساسية</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEC 6: Description -->
                                <div class="neon-glass-panel" style="margin-top: 20px; padding: 20px; border-top: 3px solid #94a3b8; background: linear-gradient(to bottom, rgba(148,163,184,0.05), transparent);">
                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                        <span style="font-size:1.2rem;">📝</span><h5 style="margin:0; color:#94a3b8; font-weight: 800;">الوصف والملاحظات</h5>
                                    </div>
                                    <textarea id="item-description" placeholder="أضف وصفاً أو ملاحظات داخلية عن الصنف..." style="width:100%; min-height:100px; padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:#fff; font-family:inherit; resize:vertical; box-sizing:border-box; transition: border-color 0.3s; font-size: 0.95rem; outline: none;" onfocus="this.style.borderColor='#94a3b8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- List Data Table -->
                        <div class="md-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px; background:rgba(0,0,0,0.3); padding:8px 15px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); flex:1; max-width:400px;">
                                    <span>🔍</span>
                                    <input type="text" id="item-search" onkeyup="filterItemsTable()" placeholder="ابحث بالاسم أو الكود..." style="background:transparent; border:none; color:#fff; width:100%; outline:none;">
                                </div>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <span id="kpi-total-items-badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; padding:6px 14px; border-radius:20px; font-weight:bold; border:1px solid rgba(56,189,248,0.3);">إجمالي الأصناف: <strong id="kpi-total-items">0 صنف</strong></span>
                                    <button class="btn-add hover-glow" onclick="openItemForm()" style="background:linear-gradient(135deg, #38bdf8, #2563eb); padding:8px 20px; border-radius:8px; font-weight:bold; box-shadow:0 4px 15px rgba(56,189,248,0.3); border:none; color:#fff; cursor:pointer;">➕ إضافة صنف جديد</button>
                                </div>
                            </div>
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="pro-table" id="main-items-table">
                                    <thead><tr><th>الكود</th><th>الصنف</th><th>الفئة</th><th>الوحدة الأساسية</th><th>سعر البيع</th><th>الحالة</th><th>الإجراء</th></tr></thead>
                                    <tbody id="items-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div id="inner-categories" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-purple" style="margin-bottom:20px;">
                            <h4 style="margin-top:0; color:#a855f7;">إضافة/تعديل فئة</h4>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الفئة</label><input type="text" id="cat-name-input" placeholder="مثال: أسمنت"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:var(--success-color); padding:10px 20px; height:fit-content;" onclick="saveCategory()">💾 حفظ الفئة</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th>اسم الفئة</th><th>عدد الأصناف</th><th>الإجراء</th></tr></thead>
                                <tbody id="categories-table-body">
                                    <!-- Dynamic categories -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Units Tab -->
                    <div id="inner-units" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-info" style="margin-bottom:20px; border-color: rgba(56,189,248,0.3);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                                <h4 style="margin:0; color:#38bdf8; font-weight:bold; font-size:1.2rem;">📐 إدارة مصفوفة وحدات القياس العامة</h4>
                                <button type="button" class="btn-add" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; padding:6px 14px; font-size:0.85rem;" onclick="cleanAndResetUnits()" title="إزالة الرموز التالفة واستعادة الوحدات القياسية">🧹 تنظيف واستعادة الوحدات القياسية</button>
                            </div>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الوحدة الجديدة</label><input type="text" id="unit-name-input" placeholder="مثال: دزينة، صندوق، كرتونة، حبة"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); padding:10px 20px; height:fit-content; font-weight:bold;" onclick="saveGlobalUnit()">💾 حفظ الوحدة</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th style="padding:12px 15px;">اسم الوحدة</th><th style="padding:12px 15px; text-align:center; width:180px;">الإجراء</th></tr></thead>
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
                    <div class="md-card-header"><h3 class="md-card-title">👥 الموظفين والصلاحيات</h3><button class="btn-add" onclick="openNewEmployeeForm()">+ إضافة موظف جديد</button></div>
                    <div id="employee-form" class="md-card" style="display: none;">
                        <h4 style="margin-top:0; color:var(--primary-accent);">إضافة/تعديل موظف</h4>
                        <div class="form-grid">
                            <div class="input-group"><label>الكود</label><input type="text" id="emp-id" disabled></div>
                            <div class="input-group"><label>الاسم</label><input type="text" id="emp-name"></div>
                            <div class="input-group"><label>رقم الهاتف</label><input type="text" id="emp-phone"></div>
                            <div class="input-group"><label>كلمة المرور</label><input type="text" id="emp-password"></div>
                            <div class="input-group"><label>الدور</label><select id="emp-role"><option value="admin">مدير</option><option value="cashier">كاشير</option></select></div>
                            <div class="input-group"><label>درجة السرية</label><select id="emp-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                            <div class="input-group"><label>الحالة</label><select id="emp-status"><option value="active">نشط</option><option value="inactive">موقوف</option></select></div>
                        </div>
                        <button type="button" class="btn-add" style="background:var(--success-color);" onclick="saveEmployee()">💾 حفظ</button>
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
                    <div class="md-card-header">
                        <h3 class="md-card-title">📊 قائمة مراكز التكلفة</h3>
                        <button class="btn-add" onclick="openCostCenterForm()">+ إضافة مركز تكلفة جديد</button>
                    </div>
                    
                    <div id="costcenter-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">إضافة/تعديل مركز تكلفة</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-costcenter" onclick="closeCostCenterForm()">إلغاء التعديل</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="costcenter-id">
                            <div class="input-group"><label>رمز/كود المركز</label><input type="text" id="costcenter-code" placeholder="تلقائي عند الحفظ" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;"></div>
                            <div class="input-group"><label>اسم مركز التكلفة</label><input type="text" id="costcenter-name" placeholder="مثال: مشروع الخرطوم الجديد"></div>
                            <div class="input-group"><label>الوصف / ملاحظات</label><input type="text" id="costcenter-desc" placeholder="..."></div>
                        </div>
                        <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveCostCenter()">💾 حفظ البيانات</button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>كود المركز</th><th>الاسم</th><th>الوصف</th><th>الإجراء</th></tr></thead>
                            <tbody id="costcenters-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            

        </main>
    </div>

    <!-- Universal Master Data Entity Deletion & Voucher Check Modal -->
    <div id="master-data-delete-modal" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(0, 0, 0, 0.82); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); justify-content: center; align-items: center; padding: 20px; animation: fadeIn 0.25s ease-out;">
        <div class="neo-card" style="background: rgba(18, 15, 25, 0.96); border: 2.5px solid rgba(255, 255, 255, 0.15); border-radius: 22px; width: 100%; max-width: 630px; box-shadow: 0 25px 65px rgba(0,0,0,0.85), 0 0 35px rgba(0,0,0,0.6); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; font-family: 'Tajawal', sans-serif;">
            
            <!-- Modal Header -->
            <div id="md-del-modal-header" style="padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.02);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div id="md-del-modal-icon-wrap" style="width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); box-shadow: 0 0 20px rgba(239,68,68,0.2);">
                        <span id="md-del-modal-icon">🚫</span>
                    </div>
                    <div>
                        <h3 id="md-del-modal-title" style="margin: 0; font-size: 1.3rem; font-weight: 900; color: #fff; letter-spacing: 0.3px;">فحص حالة العنصر والسندات</h3>
                        <p id="md-del-modal-subtitle" style="margin: 4px 0 0 0; font-size: 0.88rem; color: #94a3b8; font-weight: 600;">التحقق من ارتباط المكون بالعمليات المالية والمخزنية</p>
                    </div>
                </div>
                <button type="button" onclick="closeEntityDeleteModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">✕</button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 24px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 18px;">
                
                <!-- Target Summary Card -->
                <div style="background: linear-gradient(135deg, rgba(56,189,248,0.08), rgba(99,102,241,0.05)); border: 1px solid rgba(56,189,248,0.25); border-radius: 16px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span id="md-del-target-type-label" style="font-size: 0.8rem; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 2px;">📦 الاسم:</span>
                        <strong id="md-del-target-name" style="font-size: 1.3rem; color: #38bdf8; font-weight: 900;">-</strong>
                    </div>
                    <div style="text-align: left;">
                        <span id="md-del-target-code-label" style="font-size: 0.8rem; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 2px;">🏷️ الكود / المعرف:</span>
                        <span id="md-del-target-code" style="font-family: 'Outfit', monospace; font-size: 1.15rem; font-weight: 900; color: #f1f5f9; background: rgba(0,0,0,0.3); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">-</span>
                    </div>
                </div>

                <!-- Dynamic State Box -->
                <div id="md-del-status-box" style="border-radius: 16px; padding: 18px; border: 1.5px solid transparent; transition: all 0.3s;">
                    <!-- Filled dynamically -->
                </div>

                <!-- Vouchers List Section (Shown only if used) -->
                <div id="md-del-vouchers-container" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span id="md-del-vouchers-header-label" style="font-size: 0.95rem; font-weight: 800; color: #f87171; display: flex; align-items: center; gap: 6px;">📋 السندات والفواتير المرتبطة بهذا العنصر:</span>
                        <span id="md-del-vouchers-count" style="font-size: 0.85rem; background: rgba(239,68,68,0.25); color: #fca5a5; padding: 3px 10px; border-radius: 8px; font-weight: 900; border: 1px solid rgba(239,68,68,0.4);">0 سند</span>
                    </div>
                    <div id="md-del-vouchers-list" style="max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 10px; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Vouchers items -->
                    </div>
                </div>

            </div>

            <!-- Modal Footer Actions -->
            <div id="md-del-modal-footer" style="padding: 18px 24px; border-top: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap;">
                <!-- Buttons dynamically populated -->
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        if (typeof window.showToast !== 'function') {
            window.showToast = function(msg, type = 'success', duration = 3500) {
                let container = document.getElementById('md-toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'md-toast-container';
                    container.style.cssText = 'position:fixed; bottom:30px; left:50%; transform:translateX(-50%); z-index:9999999; display:flex; flex-direction:column; gap:10px; pointer-events:none;';
                    document.body.appendChild(container);
                }
                const toast = document.createElement('div');
                const isSuccess = type === 'success';
                const isError = type === 'error';
                const bg = isSuccess ? 'linear-gradient(135deg, #10b981, #059669)' : (isError ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #f59e0b, #d97706)');
                toast.style.cssText = `background:${bg}; color:#ffffff; padding:14px 28px; border-radius:14px; font-weight:800; font-size:1.05rem; box-shadow:0 12px 35px rgba(0,0,0,0.6), 0 0 20px rgba(0,0,0,0.4); pointer-events:auto; font-family:'Tajawal', sans-serif; text-align:center; display:flex; align-items:center; gap:10px; border:1px solid rgba(255,255,255,0.25); transition:all 0.35s cubic-bezier(0.4, 0, 0.2, 1); transform:translateY(20px); opacity:0;`;
                toast.innerHTML = (isSuccess ? '<span>✅</span> ' : (isError ? '<span>🚫</span> ' : '<span>⚠️</span> ')) + `<span>${msg}</span>`;
                container.appendChild(toast);
                requestAnimationFrame(() => {
                    toast.style.transform = 'translateY(0)';
                    toast.style.opacity = '1';
                });
                setTimeout(() => {
                    toast.style.transform = 'translateY(20px)';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 350);
                }, duration);
            };
        }

        // ================= BACKEND MYSQL PERSISTENCE =================
        async function syncToBackend(type, data) {
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const headers = {
                    'Content-Type': 'application/json'
                };
                if (token) headers['Authorization'] = 'Bearer ' + token;
                if (tenantId) headers['X-Tenant-Id'] = String(tenantId);

                const res = await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ type, data })
                });
                const result = await res.json();
                if (result && result.success) {
                    console.log(`[Master Data Sync] Successfully saved ${type} to MySQL.`);
                } else if (result && result.error) {
                    console.warn(`[Master Data Sync] Server responded with error for ${type}:`, result.error);
                }
                return result;
            } catch(err) {
                console.warn(`[Master Data Sync] Failed to save ${type} to MySQL:`, err);
            }
        }

        async function loadMasterDataFromBackend() {
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const headers = {};
                if (token) headers['Authorization'] = 'Bearer ' + token;
                if (tenantId) headers['X-Tenant-Id'] = String(tenantId);

                const res = await fetch('api/get_master_data.php', { headers });
                const json = await res.json();
                if (json && json.success && json.data) {
                    const d = json.data;
                    if (Array.isArray(d.units) && d.units.length > 0) {
                        localStorage.setItem('erp_global_units_v1', JSON.stringify(d.units));
                    }
                    if (Array.isArray(d.categories) && d.categories.length > 0) {
                        localStorage.setItem('erp_categories_v1', JSON.stringify(d.categories));
                    }
                    if (Array.isArray(d.items) && d.items.length > 0) {
                        localStorage.setItem('erp_items_v1', JSON.stringify(d.items));
                    }
                    if (Array.isArray(d.warehouses) && d.warehouses.length > 0) {
                        localStorage.setItem('erp_warehouses_v1', JSON.stringify(d.warehouses));
                    }
                    if (Array.isArray(d.customers) && d.customers.length > 0) {
                        localStorage.setItem('erp_customers_v1', JSON.stringify(d.customers));
                    }
                    if (Array.isArray(d.suppliers) && d.suppliers.length > 0) {
                        localStorage.setItem('erp_suppliers_v1', JSON.stringify(d.suppliers));
                    }
                    if (Array.isArray(d.banks) && d.banks.length > 0) {
                        localStorage.setItem('erp_banks_v1', JSON.stringify(d.banks));
                    }
                    if (Array.isArray(d.safes) && d.safes.length > 0) {
                        localStorage.setItem('erp_safes_v1', JSON.stringify(d.safes));
                    }
                    if (Array.isArray(d.cost_centers) && d.cost_centers.length > 0) {
                        localStorage.setItem('erp_cost_centers_v1', JSON.stringify(d.cost_centers));
                        localStorage.setItem('erp_jobs_v1', JSON.stringify(d.cost_centers));
                    }
                    if (Array.isArray(d.employees) && d.employees.length > 0) {
                        localStorage.setItem('erp_users_v1', JSON.stringify(d.employees));
                        localStorage.setItem('erp_employees_v1', JSON.stringify(d.employees));
                    }
                    if (d.currency_rates) {
                        localStorage.setItem('erp_currency_rates_v1', JSON.stringify(d.currency_rates));
                    }
                    renderAllMasterData();
                    if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
                    if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
                }
            } catch(err) {
                console.warn('[Master Data] Failed to load from backend:', err);
            }
        }
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
            loadMasterDataFromBackend();
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
            const id = document.getElementById('warehouse-id').value || 'WH-' + Date.now();
            const name = document.getElementById('warehouse-name').value;
            if(!name.trim()) { alert('الرجاء إدخال اسم المخزن'); return; }
            if(isDuplicateName('erp_warehouses_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }
            
            const type = document.getElementById('warehouse-type').value;
            const security = parseInt(document.getElementById('warehouse-security').value) || 1;

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
            syncToBackend('save_warehouse', whData);
            cancelEditWarehouse();
            renderWarehouses();
            
            if(typeof showToast === 'function') showToast('✅ تم حفظ المخزن نهائياً في قاعدة البيانات بنجاح', 'success');
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
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 15px; color: var(--text-secondary);">لا توجد مخازن مضافة بعد</td></tr>';
                return;
            }

            warehouses.forEach(w => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td><strong>${w.name}</strong></td>
                    <td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${w.type}</span></td>
                    <td>
                        <button class="btn-text" onclick="editWarehouse('${w.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteWarehouse('${w.id}')">حذف</button>
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
            document.getElementById('warehouse-type').value = wh.type || 'مستودع رئيسي';
            document.getElementById('warehouse-security').value = wh.securityLevel || '1';
            document.getElementById('btn-cancel-warehouse').style.display = 'inline-block';
        }

        function cancelEditWarehouse() {
            document.getElementById('warehouse-form').style.display = 'none';
            document.getElementById('warehouse-id').value = '';
            document.getElementById('warehouse-name').value = '';
            document.getElementById('warehouse-type').value = 'مستودع رئيسي';
            document.getElementById('warehouse-security').value = '1';
        }

        function openWarehouseForm() {
            cancelEditWarehouse();
            document.getElementById('warehouse-form').style.display = 'block';
        }

        function deleteWarehouse(id) {
            genericDeleteEntity('warehouse', id);
        }

        function isDuplicateName(storageKey, name, currentId) {
            if (!name) return false;
            try {
                const items = JSON.parse(localStorage.getItem(storageKey) || '[]');
                const nameLower = name.trim().toLowerCase();
                for (let i = 0; i < items.length; i++) {
                    if (items[i].deleted) continue;
                    if (items[i].id !== currentId && (items[i].name || '').trim().toLowerCase() === nameLower) {
                        return true;
                    }
                }
            } catch(e) {}
            return false;
        }

        // ================= CUSTOMERS LOGIC =================
        function saveCustomer() {
            const id = document.getElementById('cust-id').value || 'CUST-' + Date.now();
            const name = document.getElementById('cust-name').value;
            if(!name.trim()) { alert('الرجاء إدخال اسم العميل'); return; }
            if(isDuplicateName('erp_customers_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }
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

            if (!name) { alert('الرجاء إدخال اسم العميل'); return; }

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
                alert('هذا الاسم موجود مسبقاً (كعميل أو مورد). لا يمكن تكرار الأسماء!');
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
            
            const todayStr = new Date().toISOString().split('T')[0];
            const nowIso = new Date().toISOString();
            
            const custData = {
                id, name, phone, whatsapp, email, contactPerson, address, 
                openingDebit, openingCredit, 
                currency, exRate, taxNumber, accountNo,
                currentBalance: (openingDebit - openingCredit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes,
                bankAccounts: bankAccounts,
                date: existingIdx > -1 ? (customers[existingIdx].date || todayStr) : todayStr,
                createdAt: existingIdx > -1 ? (customers[existingIdx].createdAt || nowIso) : nowIso
            };

            if (existingIdx > -1) {
                custData.currentBalance = customers[existingIdx].currentBalance;
                customers[existingIdx] = custData;
            } else {
                customers.push(custData);
            }

            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            syncToBackend('save_customer', custData);
            clearCustomerForm();
            renderCustomers();
            if (typeof showToast === 'function') showToast('✅ تم حفظ العميل نهائياً في قاعدة البيانات بنجاح', 'success');
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
                const statusMap = {'vip': '<span style="background:rgba(168,85,247,0.15); color:#a855f7; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🌟 VIP</span>', 'blacklisted': '<span style="background:rgba(239,68,68,0.15); color:#ef4444; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🔴 محظور</span>'};
                const statusBadge = statusMap[c.status] || '<span style="background:rgba(52,211,153,0.15); color:#34d399; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🟢 نشط</span>';
                const bal = parseFloat(c.currentBalance) || 0;
                const balColor = bal > 0 ? '#f87171' : (bal < 0 ? '#34d399' : '#94a3b8');
                const balLabel = bal > 0 ? 'عليه' : (bal < 0 ? 'له' : 'مسدد');
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
                    <td><button class="btn-text" onclick="editCustomer('${c.id}')">تعديل</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCustomer('${c.id}')">حذف</button></td>
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
                <div class="input-group"><label>اسم البنك</label><input type="text" autocomplete="off" list="master-banks-datalist" class="bank-name" placeholder="مثال: بنك الخرطوم" value="${data ? (data.bankName || '') : ''}"></div>
                <div class="input-group"><label>الفرع</label><input type="text" class="bank-branch" placeholder="مثال: فرع الرياض" value="${data ? (data.branch || '') : ''}"></div>
                <div class="input-group"><label>رقم الحساب</label><input type="text" class="bank-acc" placeholder="رقم الحساب البنكي" value="${data ? (data.accountNo || '') : ''}"></div>
                <div class="input-group"><label>درجة سرية البنك</label>
                    <select class="bank-security">
                        <option value="1" ${data && data.securityLevel == 1 ? 'selected' : ''}>1 (الأقل سرية)</option>
                        <option value="2" ${data && data.securityLevel == 2 ? 'selected' : ''}>2</option>
                        <option value="3" ${data && data.securityLevel == 3 ? 'selected' : ''}>3</option>
                        <option value="4" ${data && data.securityLevel == 4 ? 'selected' : ''}>4</option>
                        <option value="5" ${data && data.securityLevel == 5 ? 'selected' : ''}>5</option>
                        <option value="6" ${data && data.securityLevel == 6 ? 'selected' : ''}>6</option>
                        <option value="7" ${data && data.securityLevel == 7 ? 'selected' : ''}>7</option>
                        <option value="8" ${data && data.securityLevel == 8 ? 'selected' : ''}>8</option>
                        <option value="9" ${data && data.securityLevel == 9 ? 'selected' : ''}>9</option>
                        <option value="10" ${data && data.securityLevel == 10 ? 'selected' : ''}>10 (الأعلى سرية)</option>
                    </select>
                </div>
                <div class="input-group" style="display:flex; align-items:flex-end; gap: 10px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:bold; color:var(--danger-color); margin-bottom:10px;">
                        <input type="checkbox" class="bank-deactive" style="width:18px; height:18px; accent-color:var(--danger-color);" ${data && data.isDeactivated ? 'checked' : ''}> إيقاف
                    </label>
                    <button type="button" onclick="this.closest('.cust-bank-row').remove()" style="background: rgba(244,63,94,0.15); color: #f43f5e; border: 1px solid rgba(244,63,94,0.3); border-radius: 6px; cursor: pointer; padding: 6px 12px; margin-bottom: 5px; margin-right: auto;" title="حذف الحساب">🗑️ حذف</button>
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
            genericDeleteEntity('customer', id);
        }

        // ================= SUPPLIERS LOGIC =================
        function saveSupplier() {
            const id = document.getElementById('sup-id').value || 'SUP-' + Date.now();
            const name = document.getElementById('sup-name').value;
            if(!name.trim()) { alert('الرجاء إدخال اسم المورد'); return; }
            if(isDuplicateName('erp_suppliers_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }
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

            if (!name) { alert('الرجاء إدخال اسم المورد'); return; }

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
                alert('هذا الاسم موجود مسبقاً (كعميل أو مورد). لا يمكن تكرار الأسماء!');
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
            
            const todayStr = new Date().toISOString().split('T')[0];
            const nowIso = new Date().toISOString();

            const supData = {
                id, name, phone, whatsapp, email, address, 
                openingDebit, openingCredit, 
                currency, exRate, taxNumber, accountNo,
                currentBalance: (openingCredit - openingDebit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes,
                date: existingIdx > -1 ? (suppliers[existingIdx].date || todayStr) : todayStr,
                createdAt: existingIdx > -1 ? (suppliers[existingIdx].createdAt || nowIso) : nowIso
            };

            if (existingIdx > -1) {
                supData.currentBalance = suppliers[existingIdx].currentBalance;
                suppliers[existingIdx] = supData;
            } else {
                suppliers.push(supData);
            }

            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
            syncToBackend('save_supplier', supData);
            clearSupplierForm();
            renderSuppliers();
            if (typeof showToast === 'function') showToast('✅ تم حفظ المورد نهائياً في قاعدة البيانات بنجاح', 'success');
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
            if (suppliers.length === 0) {
                suppliers = [
    { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, creditLimit: 50000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, creditLimit: 30000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, creditLimit: 20000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, creditLimit: 100000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, creditLimit: 15000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, creditLimit: 25000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, creditLimit: 40000000, status: 'active', securityLevel: 1 }
];
                try { localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers)); } catch(e){}
            }
            tbody.innerHTML = '';
            suppliers.forEach(s => {
                const statusMap = {'vip': '<span style="background:rgba(168,85,247,0.15); color:#a855f7; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🌟 VIP</span>', 'blacklisted': '<span style="background:rgba(239,68,68,0.15); color:#ef4444; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🔴 محظور</span>'};
                const statusBadge = statusMap[s.status] || '<span style="background:rgba(52,211,153,0.15); color:#34d399; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🟢 نشط</span>';
                const bal = parseFloat(s.currentBalance) || 0;
                const balColor = bal > 0 ? '#34d399' : (bal < 0 ? '#f87171' : '#94a3b8');
                const balLabel = bal > 0 ? 'له' : (bal < 0 ? 'عليه' : 'مسدد');
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
                    <td><button class="btn-text" onclick="editSupplier('${s.id}')">تعديل</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSupplier('${s.id}')">حذف</button></td>
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
            genericDeleteEntity('supplier', id);
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
                safeSelect.innerHTML = '<option value="">-- اختر --</option>';
                employees.forEach(emp => {
                    if (emp.status === 'active' || emp.status === 'نشط') {
                        const opt = document.createElement('option');
                        opt.value = emp.id || emp.username || emp.name;
                        opt.textContent = emp.name || emp.fullName || emp.username || 'بدون اسم';
                        safeSelect.appendChild(opt);
                    }
                });
                safeSelect.value = currentVal;
            }

            employees.forEach(emp => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td>${emp.name}</td>
                    <td>${emp.role}</td>
                    <td>${emp.phone||'-'}</td>
                    <td>${emp.securityLevel}</td>
                    <td>${emp.status}</td>
                    <td>
                        <button class="btn-text" style="color:#a855f7; font-weight:bold; margin-left:8px;" onclick="editEmployee('${emp.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); font-weight:bold;" onclick="deleteEmployee('${emp.id}')">🗑️ حذف</button>
                    </td>
                </tr>`);
            });
        }
        
        function deleteEmployee(id) {
            genericDeleteEntity('employee', id);
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
            if(!name.trim()) { alert('الرجاء إدخال اسم الموظف'); return; }
            if(isDuplicateName('erp_employees_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }
            
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
            localStorage.setItem('erp_employees_v1', JSON.stringify(employees));
            syncToBackend('save_employee', empData);
            document.getElementById('employee-form').style.display='none';
            renderEmployees();
            if (typeof showToast === 'function') showToast('✅ تم حفظ الموظف نهائياً في قاعدة البيانات بنجاح', 'success');
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
        
    function autoUpdateMasterBankIBAN() {
        const bankName = document.getElementById('bank-name')?.value || '';
        const accNo = document.getElementById('bank-gl-account')?.value || document.getElementById('bank-code')?.value || '';
        const branch = document.getElementById('bank-branch')?.value || '00001';
        const ibanField = document.getElementById('bank-iban');
        
        if (ibanField && accNo) {
            const bban = typeof calculateSudanBBAN === 'function' ? calculateSudanBBAN(bankName, accNo, branch) : '';
            if (bban) {
                ibanField.value = bban;
            }
        }
    }

        function openBankForm() {
            const form = document.getElementById('bank-form');
            if (!form) return;
            form.style.display = 'block';
            document.getElementById('bank-code').value = 'B-' + Date.now();
            document.getElementById('bank-name').value = '';
            document.getElementById('bank-branch').value = '';
            document.getElementById('bank-opening-balance').value = '';
            document.getElementById('bank-opening-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('bank-gl-account').value = '';
            document.getElementById('bank-iban').value = '';
            document.getElementById('bank-swift').value = '';
            document.getElementById('bank-currency').value = 'SDG';
            if (document.getElementById('bank-ex-rate')) {
                document.getElementById('bank-ex-rate').value = 1;
            }
            document.getElementById('bank-confidentiality').value = '1';
            document.getElementById('bank-inactive').checked = false;
            document.querySelectorAll('.bank-payment-method').forEach(cb => cb.checked = true);
            const title = document.getElementById('bank-form-title');
            if (title) title.innerText = 'إضافة حساب بنكي';
            if (typeof updateBankExchangeRate === 'function') updateBankExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function closeBankForm() {
            const form = document.getElementById('bank-form');
            if (form) form.style.display = 'none';
        }

        function saveBank() {
            const id = document.getElementById('bank-code').value || 'B-' + Date.now();
            const name = document.getElementById('bank-name').value.trim();
            if(!name) { alert('الرجاء إدخال اسم الحساب البنكي'); return; }
            if(isDuplicateName('erp_banks_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }

            const type = 'bank';
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            
            const existingIdx = banks.findIndex(b => b.id === id);

            const paymentMethods = [];
            document.querySelectorAll('.bank-payment-method:checked').forEach(cb => {
                paymentMethods.push(cb.value);
            });

            const exRate = parseFloat(document.getElementById('bank-ex-rate')?.value) || 1;

            const bankData = {
                id, name, type,
                branch: document.getElementById('bank-branch').value.trim(),
                openingBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                openingDate: document.getElementById('bank-opening-date').value,
                glAccount: document.getElementById('bank-gl-account').value.trim(),
                iban: document.getElementById('bank-iban').value.trim(),
                swift: document.getElementById('bank-swift').value.trim(),
                responsible: '',
                currency: document.getElementById('bank-currency').value,
                exchangeRate: exRate,
                paymentMethods: paymentMethods,
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
            syncToBackend('save_bank', bankData);
            if (window.MasterBanksHelper) window.MasterBanksHelper.refresh();
            closeBankForm();
            renderBanks();
            if (typeof showToast === 'function') showToast('✅ تم حفظ البنك نهائياً في قاعدة البيانات بنجاح', 'success');
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
                const typeLabel = '🏦 حساب بنكي';
                const balFormatted = (parseFloat(b.openingBalance) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="color:#38bdf8; font-weight:bold;">${b.id}</td>
                    <td style="font-weight:bold;">${b.name} <br><small style="color:#94a3b8;">${b.branch || ''}</small></td>
                    <td>${typeLabel}</td>
                    <td><span class="badge" style="background:rgba(56,189,248,0.2); color:#38bdf8; padding:3px 8px; border-radius:4px;">${b.currency || 'SDG'}</span></td>
                    <td style="font-weight:bold;">${balFormatted}</td>
                    <td>
                        <button class="btn-text" style="color:#38bdf8;" onclick="editBank('${b.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteBank('${b.id}')">🗑️ حذف</button>
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
            
            const form = document.getElementById('bank-form');
            if (!form) return;
            form.style.display = 'block';

            document.getElementById('bank-code').value = bank.id;
            document.getElementById('bank-name').value = bank.name || '';
            document.getElementById('bank-branch').value = bank.branch || '';
            document.getElementById('bank-opening-balance').value = bank.openingBalance !== undefined ? bank.openingBalance : '';
            document.getElementById('bank-opening-date').value = bank.openingDate || '';
            document.getElementById('bank-gl-account').value = bank.glAccount || '';
            document.getElementById('bank-iban').value = bank.iban || '';
            document.getElementById('bank-swift').value = bank.swift || '';
            document.getElementById('bank-currency').value = bank.currency || 'SDG';
            if (document.getElementById('bank-ex-rate')) {
                document.getElementById('bank-ex-rate').value = bank.exchangeRate || 1;
            }
            document.getElementById('bank-confidentiality').value = bank.confidentiality || '1';
            document.getElementById('bank-inactive').checked = bank.isInactive || false;
            
            const methods = bank.paymentMethods || ['cash', 'pos', 'transfer', 'check'];
            document.querySelectorAll('.bank-payment-method').forEach(cb => {
                cb.checked = methods.includes(cb.value);
            });

            const title = document.getElementById('bank-form-title');
            if (title) title.innerText = 'تعديل حساب بنكي: ' + (bank.name || '');
            if (typeof updateBankExchangeRate === 'function') updateBankExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function deleteBank(id) {
            genericDeleteEntity('bank', id);
            if (window.MasterBanksHelper) window.MasterBanksHelper.refresh();
        }

        // ================= SAFES (خزن) LOGIC =================
        function openSafeForm() {
            const form = document.getElementById('safe-form');
            if (!form) return;
            form.style.display = 'block';
            document.getElementById('safe-code').value = 'SAFE-' + Date.now();
            document.getElementById('safe-name').value = '';
            document.getElementById('safe-opening-balance').value = '';
            document.getElementById('safe-opening-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('safe-gl-account').value = '';
            document.getElementById('safe-responsible').value = '';
            document.getElementById('safe-currency').value = 'SDG';
            if (document.getElementById('safe-ex-rate')) {
                document.getElementById('safe-ex-rate').value = 1;
            }
            document.getElementById('safe-max-limit').value = '';
            document.getElementById('safe-min-limit').value = '';
            document.getElementById('safe-confidentiality').value = '1';
            document.getElementById('safe-inactive').checked = false;
            document.getElementById('safe-modifier').checked = false;
            document.querySelectorAll('.safe-payment-method').forEach(cb => cb.checked = true);
            const title = document.getElementById('safe-form-title');
            if (title) title.innerText = 'إضافة خزينة جديدة';
            if (typeof updateSafeExchangeRate === 'function') updateSafeExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function closeSafeForm() {
            const form = document.getElementById('safe-form');
            if (form) form.style.display = 'none';
        }

        function saveSafe() {
            const id = document.getElementById('safe-code').value || 'SAFE-' + Date.now();
            const name = document.getElementById('safe-name').value.trim();
            if(!name) { alert('الرجاء إدخال اسم الخزينة'); return; }
            if(isDuplicateName('erp_safes_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }

            const type = 'safe';
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            
            const isDuplicateName = safes.some(b => b.name && b.name.trim().toLowerCase() === name.toLowerCase() && b.id !== id);
            if (isDuplicateName) {
                alert('هذا الاسم مستخدم لخزنة أخرى، الرجاء إدخال اسم مختلف.');
                return;
            }

            const existingIdx = safes.findIndex(b => b.id === id);

            const paymentMethods = [];
            document.querySelectorAll('.safe-payment-method:checked').forEach(cb => {
                paymentMethods.push(cb.value);
            });

            const exRate = parseFloat(document.getElementById('safe-ex-rate')?.value) || 1;

            const safeData = {
                id, name, type,
                branch: '',
                openingBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                openingDate: document.getElementById('safe-opening-date').value,
                glAccount: document.getElementById('safe-gl-account').value.trim(),
                iban: '',
                swift: '',
                responsible: document.getElementById('safe-responsible').value,
                currency: document.getElementById('safe-currency').value,
                exchangeRate: exRate,
                paymentMethods: paymentMethods,
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
            syncToBackend('save_safe', safeData);
            closeSafeForm();
            renderSafes();
            if (typeof showToast === 'function') showToast('✅ تم حفظ الخزينة نهائياً في قاعدة البيانات بنجاح', 'success');
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
                const typeLabel = '💰 خزينة';
                const balFormatted = (parseFloat(b.openingBalance) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="color:#4ade80; font-weight:bold;">${b.id}</td>
                    <td style="font-weight:bold;">${b.name}</td>
                    <td>${typeLabel}</td>
                    <td><span class="badge" style="background:rgba(74,222,128,0.2); color:#4ade80; padding:3px 8px; border-radius:4px;">${b.currency || 'SDG'}</span></td>
                    <td style="font-weight:bold;">${balFormatted}</td>
                    <td>
                        <button class="btn-text" style="color:#38bdf8;" onclick="editSafe('${b.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSafe('${b.id}')">🗑️ حذف</button>
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
            
            const form = document.getElementById('safe-form');
            if (!form) return;
            form.style.display = 'block';

            document.getElementById('safe-code').value = safe.id;
            document.getElementById('safe-name').value = safe.name || '';
            document.getElementById('safe-opening-balance').value = safe.openingBalance !== undefined ? safe.openingBalance : '';
            document.getElementById('safe-opening-date').value = safe.openingDate || '';
            document.getElementById('safe-gl-account').value = safe.glAccount || '';
            document.getElementById('safe-responsible').value = safe.responsible || '';
            document.getElementById('safe-currency').value = safe.currency || 'SDG';
            if (document.getElementById('safe-ex-rate')) {
                document.getElementById('safe-ex-rate').value = safe.exchangeRate || 1;
            }
            document.getElementById('safe-max-limit').value = safe.maxLimit !== undefined ? safe.maxLimit : '';
            document.getElementById('safe-min-limit').value = safe.minLimit !== undefined ? safe.minLimit : '';
            document.getElementById('safe-confidentiality').value = safe.confidentiality || '1';
            document.getElementById('safe-inactive').checked = safe.isInactive || false;
            document.getElementById('safe-modifier').checked = safe.isModifier || false;

            const methods = safe.paymentMethods || ['cash', 'pos', 'transfer', 'check'];
            document.querySelectorAll('.safe-payment-method').forEach(cb => {
                cb.checked = methods.includes(cb.value);
            });

            const title = document.getElementById('safe-form-title');
            if (title) title.innerText = 'تعديل الخزينة: ' + (safe.name || '');
            if (typeof updateSafeExchangeRate === 'function') updateSafeExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function deleteSafe(id) {
            genericDeleteEntity('safe', id);
        }

                        // ================= ITEMS LOGIC =================
        function closeItemForm() { document.getElementById('item-form').style.display = 'none'; }

        function updateToggleLabel() {
            const t = document.getElementById('item-active-toggle');
            const l = document.getElementById('item-active-label');
            if(t.checked) { l.innerText = '🟢 نشط'; l.style.color = 'var(--success-color)'; }
            else { l.innerText = '🔴 موقوف'; l.style.color = 'var(--danger-color)'; }
        }

        function updateBaseUnitRow() {
            const v = document.getElementById('item-base-unit').value;
            const d = document.getElementById('base-unit-name-display');
            if(d) d.value = v;
        }

        function quickAddCategory() {
            const name = prompt('أدخل اسم الفئة الجديدة:');
            if(!name) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); syncToBackend('categories', cats); }
            updateCategoryDropdowns();
            document.getElementById('item-category').value = name;
            renderCategoriesList();
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value.trim();
            if(!name) { alert('الرجاء إدخال اسم الصنف'); return; }
            const baseUnit = document.getElementById('item-base-unit').value.trim();
            if(!baseUnit) { alert('الرجاء إدخال الوحدة الأساسية'); return; }
            const id = document.getElementById('item-code').value.trim() || 'ITEM-' + Date.now();
            if(isDuplicateName('erp_items_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }
            
            const sellingPriceInput = parseFloat(document.getElementById('item-selling-price').value) || 0;
            const itemBarcode = document.getElementById('item-barcode').value.trim();

            const units = [];
            const unitsArray = [];

            const rows = document.querySelectorAll('#units-matrix-body tr');
            rows.forEach((row, idx) => {
                const tds = row.querySelectorAll('td');
                if (tds.length >= 8) {
                    let uName = '';
                    if (idx === 0) {
                        uName = baseUnit;
                    } else {
                        const sel = tds[1].querySelector('select, input');
                        uName = sel ? sel.value.trim() : '';
                    }
                    if (!uName) uName = (idx === 0 ? baseUnit : 'وحدة ' + (idx + 1));

                    const uFactor = (idx === 0) ? 1 : (parseFloat(tds[2].querySelector('input')?.value) || 1);
                    const uBarcode = tds[3].querySelector('input')?.value.trim() || (idx === 0 ? itemBarcode : '');
                    
                    let uCash = parseFloat(tds[4].querySelector('input')?.value) || 0;
                    if (idx === 0 && uCash === 0 && sellingPriceInput > 0) {
                        uCash = sellingPriceInput;
                    }
                    const uCredit = parseFloat(tds[5].querySelector('input')?.value) || 0;
                    const uMin = parseFloat(tds[6].querySelector('input')?.value) || 0;
                    const uWeight = parseFloat(tds[7].querySelector('input')?.value) || 0;
                    
                    units.push({
                        unitName: uName,
                        factor: uFactor,
                        barcode: uBarcode,
                        cashPrice: uCash,
                        creditPrice: uCredit,
                        minPrice: uMin,
                        weight: uWeight,
                        stock: 0
                    });

                    unitsArray.push({
                        name: uName,
                        unitName: uName,
                        factor: uFactor,
                        price: uCash,
                        cashPrice: uCash,
                        creditPrice: uCredit,
                        minPrice: uMin,
                        barcode: uBarcode
                    });
                }
            });

            if (units.length === 0) {
                units.push({
                    unitName: baseUnit,
                    factor: 1,
                    barcode: itemBarcode,
                    cashPrice: sellingPriceInput,
                    creditPrice: 0,
                    minPrice: 0,
                    weight: 0,
                    stock: 0
                });
                unitsArray.push({
                    name: baseUnit,
                    unitName: baseUnit,
                    factor: 1,
                    price: sellingPriceInput,
                    cashPrice: sellingPriceInput,
                    barcode: itemBarcode
                });
            }

            const finalSellingPrice = (units[0] && units[0].cashPrice > 0) ? units[0].cashPrice : sellingPriceInput;
            const finalBarcode = (units[0] && units[0].barcode) ? units[0].barcode : itemBarcode;

            const newBarcodes = [finalBarcode, ...units.map(u => u.barcode)].filter(b => b && b.trim() !== '');

            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const itemData = {
                id,
                name,
                nameLatin: document.getElementById('item-name-latin').value.trim(),
                barcode: finalBarcode,
                category: document.getElementById('item-category').value,
                subCategory: document.getElementById('item-sub-category').value,
                nature: document.getElementById('item-nature').value,
                brand: document.getElementById('item-brand').value.trim(),
                status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive',
                sellingPrice: finalSellingPrice,
                maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0,
                taxable: document.getElementById('item-taxable').checked,
                taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0,
                costMethod: costRadio ? costRadio.value : 'lastPurchase',
                openingQty: parseFloat(document.getElementById('item-opening-qty').value) || 0,
                openingValue: parseFloat(document.getElementById('item-opening-value').value) || 0,
                binLocation: document.getElementById('item-bin-location').value.trim(),
                maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0,
                minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0,
                reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0,
                baseUnit: baseUnit,
                unit: baseUnit,
                bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1,
                units: units,
                unitsArray: unitsArray,
                description: document.getElementById('item-description').value
            };

            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const duplicateName = items.find(i => i.name.trim() === name && i.id !== id);
            if (duplicateName) { alert('هذا الصنف مسجل مسبقاً بنفس الاسم.'); return; }
            
            for (const b of newBarcodes) {
                const duplicateBarcode = items.find(i => i.id !== id && (i.barcode === b || (i.units && i.units.some(u => u.barcode === b))));
                if (duplicateBarcode) {
                    alert('الباركود (' + b + ') مستخدم مسبقاً في صنف آخر: ' + duplicateBarcode.name);
                    return;
                }
            }
            
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) {
                if(items[ei].units) {
                    itemData.units.forEach((u,i) => { if(items[ei].units[i]) u.stock = items[ei].units[i].stock || 0; });
                }
                items[ei] = itemData;
            } else {
                itemData.units[0].stock = itemData.openingQty;
                items.push(itemData);
            }
            
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            syncToBackend('save_item', itemData);
            closeItemForm();
            renderItems();
            if (typeof showToast === 'function') {
                showToast('✅ تم حفظ الصنف ووحداته نهائياً في قاعدة البيانات بنجاح', 'success');
            } else {
                alert('✅ تم حفظ الصنف ووحداته نهائياً في قاعدة البيانات بنجاح');
            }
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
            if(totalEl) totalEl.innerText = items.length + ' صنف';
            
            let lowStock = 0;
            items.forEach(i => {
                let stock = i.units && i.units[0] ? (i.units[0].stock || 0) : 0;
                if(stock <= (i.minLimit || 5)) lowStock++;
            });
            const lowEl = document.getElementById('kpi-low-stock');
            if(lowEl) lowEl.innerText = lowStock + ' صنف';
            
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
                const badge = i.status === 'inactive' ? '<span style="color:var(--danger-color); font-size:0.8rem; font-weight:bold; background:rgba(239,68,68,0.1); padding:3px 8px; border-radius:6px;">🔴 موقوف</span>' : '<span style="color:var(--success-color); font-size:0.8rem; font-weight:bold; background:rgba(52,211,153,0.1); padding:3px 8px; border-radius:6px;">🟢 نشط</span>';
                const subCat = i.subCategory ? ' <small style="color:var(--text-secondary);">(' + i.subCategory + ')</small>' : '';
                tbody.insertAdjacentHTML('beforeend', `<tr class="item-row"><td class="item-id-cell" style="font-weight:bold; color:#38bdf8;">${i.id}</td><td class="item-name-cell" style="font-weight:bold;">${i.name}${subCat}</td><td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${i.category || '-'}</span></td><td>${bu}</td><td style="font-weight:bold; color:var(--success-color);">${price.toLocaleString()} SDG</td><td>${badge}</td><td><button class="btn-text" style="color:#a855f7; font-size:1.1rem; margin-left:8px;" onclick="editItem('${i.id}')" title="تعديل">✏️</button><button class="btn-text" style="color:var(--danger-color); font-size:1.1rem;" onclick="deleteItem('${i.id}')" title="حذف">🗑️</button></td></tr>`);
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
                if (totalPages <= 1) {
                    pagContainer.style.display = 'none';
                } else {
                    pagContainer.style.display = 'flex';
                    pagContainer.innerHTML = `
                        <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems>1){currentPageItems--; renderItems();}">السابق</button>
                        <span style="color:var(--text-secondary); font-weight:bold;">صفحة ${currentPageItems} من ${totalPages}</span>
                        <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems<${totalPages}){currentPageItems++; renderItems();}">التالي</button>
                    `;
                }
            }
        }

        let currentPageItems = 1;
        const itemsPerPage = 100;
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
            const name = prompt('أدخل اسم الوحدة الجديدة:');
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
            const act = isBase ? '<span style="font-size:0.75rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); padding:3px 8px; border-radius:4px;">أساسية</span>' : "<button class=\"btn-text\" style=\"color:var(--danger-color);\" onclick=\"this.closest('tr').remove()\">✖</button>";
            const idAttr = isBase ? ' id="base-unit-name-display"' : ' class="unit-name-input"';
            
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            const val = u.unitName || '';
            if (val && !units.includes(val)) units.push(val); // Ensure backward compat display
            
            let opts = `<option value="">-- اختر --</option>` + units.map(un => `<option value="${un}" ${un === val ? 'selected' : ''}>${un}</option>`).join('');
            
            let unitField = isBase ? 
                `<input type="text" id="base-unit-name-display" value="${val}" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; padding:10px; border-radius:8px; outline:none; font-size:1.1rem;">` : 
                `<select ${idAttr} style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:bold; text-align:center; padding:10px; border-radius:8px; outline:none;">${opts}</select>`;

            return '<tr ' + bg + '><td style="text-align:center; font-weight:bold;">' + num + '</td><td>' + unitField + '</td><td><input type="number" value="' + (u.factor || 1) + '" ' + dis + ' style="width:100%; background:transparent; border:' + (isBase ? 'none' : '1px solid rgba(255,255,255,0.15)') + '; color:' + (isBase ? 'var(--success-color)' : '#fff') + '; font-weight:bold; text-align:center; padding:10px; border-radius:8px; outline:none; font-size:1.2rem;"></td><td><input type="text" value="' + (u.barcode || '') + '" placeholder="باركود..." style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:10px; border-radius:8px; outline:none;"></td><td><input type="number" value="' + (u.cashPrice || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#34d399; padding:10px; border-radius:8px; font-weight:bold; outline:none;"></td><td><input type="number" value="' + (u.creditPrice || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#60a5fa; padding:10px; border-radius:8px; font-weight:bold; outline:none;"></td><td><input type="number" value="' + (u.minPrice || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#f87171; padding:10px; border-radius:8px; font-weight:bold; outline:none;"></td><td><input type="number" value="' + (u.weight || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:10px; border-radius:8px; outline:none;"></td><td style="text-align:center;">' + act + '</td></tr>';
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
            document.getElementById('form-mode-indicator').innerText = 'تعديل صنف: ' + item.name;
            document.getElementById('item-code').value = item.id;
            document.getElementById('item-name-input').value = item.name;
            document.getElementById('item-name-latin').value = item.nameLatin || '';
            document.getElementById('item-barcode').value = item.barcode || '';
            updateCategoryDropdowns();
            document.getElementById('item-category').value = item.category || '';
            document.getElementById('item-sub-category').value = item.subCategory || '';
            document.getElementById('item-nature').value = item.nature || 'مخزني';
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
            
            const baseUnit = item.baseUnit || item.unit || 'قطعة';
            updateUnitDropdowns(baseUnit);
            document.getElementById('item-base-unit').value = baseUnit;
            document.getElementById('item-bundle-factor').value = item.bundleFactor || 1;
            document.getElementById('item-description').value = item.description || '';
            updateBaseUnitRow();
            
            const tbody = document.getElementById('units-matrix-body');
            tbody.innerHTML = '';
            
            let matrixUnits = [];
            if (Array.isArray(item.units) && item.units.length > 0) {
                matrixUnits = item.units;
            } else if (Array.isArray(item.unitsArray) && item.unitsArray.length > 0) {
                matrixUnits = item.unitsArray.map(u => ({
                    unitName: u.name || u.unitName || baseUnit,
                    factor: u.factor || 1,
                    barcode: u.barcode || '',
                    cashPrice: u.price || u.cashPrice || item.sellingPrice || 0,
                    creditPrice: u.creditPrice || 0,
                    minPrice: u.minPrice || 0,
                    weight: u.weight || 0
                }));
            }
            if (matrixUnits.length === 0) {
                matrixUnits = [{
                    unitName: baseUnit,
                    factor: 1,
                    barcode: item.barcode || '',
                    cashPrice: item.sellingPrice || 0,
                    creditPrice: 0,
                    minPrice: 0,
                    weight: 0
                }];
            }
            
            matrixUnits.forEach((u, idx) => {
                tbody.insertAdjacentHTML('beforeend', buildUnitRow(idx + 1, u, idx === 0));
            });
            
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        // ================= ITEM DELETION & USAGE CHECK LOGIC =================
        let currentDeleteItemData = null;

        // ================= UNIVERSAL MASTER DATA DELETION & VOUCHER USAGE CHECK ENGINE =================
        const ENTITY_CONFIG = {
            warehouse: {
                type: 'warehouse',
                label: 'المخزن',
                labelPlural: 'المخازن',
                icon: '🏢',
                localKey: 'erp_warehouses_v1',
                backendType: 'delete_warehouse',
                render: () => { if (typeof renderWarehouses === 'function') renderWarehouses(); }
            },
            customer: {
                type: 'customer',
                label: 'العميل',
                labelPlural: 'العملاء',
                icon: '👥',
                localKey: 'erp_customers_v1',
                backendType: 'delete_customer',
                render: () => { if (typeof renderCustomers === 'function') renderCustomers(); }
            },
            supplier: {
                type: 'supplier',
                label: 'المورد',
                labelPlural: 'الموردين',
                icon: '🏭',
                localKey: 'erp_suppliers_v1',
                backendType: 'delete_supplier',
                render: () => { if (typeof renderSuppliers === 'function') renderSuppliers(); }
            },
            bank: {
                type: 'bank',
                label: 'البنك',
                labelPlural: 'البنوك',
                icon: '🏦',
                localKey: 'erp_banks_v1',
                backendType: 'delete_bank',
                render: () => { if (typeof renderBanks === 'function') renderBanks(); }
            },
            safe: {
                type: 'safe',
                label: 'الخزينة',
                labelPlural: 'الخزن',
                icon: '💰',
                localKey: 'erp_safes_v1',
                backendType: 'delete_safe',
                render: () => { if (typeof renderSafes === 'function') renderSafes(); }
            },
            item: {
                type: 'item',
                label: 'الصنف',
                labelPlural: 'الأصناف',
                icon: '📦',
                localKey: 'erp_items_v1',
                backendType: 'delete_item',
                render: () => { if (typeof renderItems === 'function') renderItems(); }
            },
            employee: {
                type: 'employee',
                label: 'الموظف',
                labelPlural: 'الموظفين',
                icon: '👤',
                localKey: 'erp_users_v1',
                backendType: 'delete_employee',
                render: () => { if (typeof renderEmployees === 'function') renderEmployees(); }
            },
            unit: {
                type: 'unit',
                label: 'وحدة القياس',
                labelPlural: 'الوحدات',
                icon: '📐',
                localKey: 'erp_global_units_v1',
                backendType: 'delete_unit',
                render: () => {
                    if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
                    if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
                    if (typeof renderItems === 'function') renderItems();
                }
            },
            cost_center: {
                type: 'cost_center',
                label: 'مركز التكلفة',
                labelPlural: 'مراكز التكلفة',
                icon: '📊',
                localKey: 'erp_jobs_v1',
                backendType: 'delete_cost_center',
                render: () => { if (typeof renderCostCenters === 'function') renderCostCenters(); }
            },
            category: {
                type: 'category',
                label: 'الفئة',
                labelPlural: 'الفئات',
                icon: '🏷️',
                localKey: 'erp_categories_v1',
                backendType: 'delete_category',
                render: () => {
                    if (typeof renderCategoriesList === 'function') renderCategoriesList();
                    if (typeof updateCategoryDropdowns === 'function') updateCategoryDropdowns();
                    if (typeof renderItems === 'function') renderItems();
                }
            }
        };

        let currentDeleteTarget = null;

        function closeEntityDeleteModal() {
            const modal = document.getElementById('master-data-delete-modal') || document.getElementById('item-delete-modal');
            if (modal) modal.style.display = 'none';
            currentDeleteTarget = null;
        }
        function closeItemDeleteModal() { closeEntityDeleteModal(); }

        function openEntityDeleteModalLoading(cfg, entity) {
            const modal = document.getElementById('master-data-delete-modal') || document.getElementById('item-delete-modal');
            if (!modal) return;

            const nameEl = document.getElementById('md-del-target-name') || document.getElementById('item-del-target-name');
            const codeEl = document.getElementById('md-del-target-code') || document.getElementById('item-del-target-code');
            const typeLabelEl = document.getElementById('md-del-target-type-label');
            const codeLabelEl = document.getElementById('md-del-target-code-label');

            if (nameEl) nameEl.innerText = entity.name || entity.id || 'عنصر غير محدد';
            if (codeEl) codeEl.innerText = entity.id || entity.name || '-';
            if (typeLabelEl) typeLabelEl.innerText = `${cfg.icon} اسم ${cfg.label}:`;
            if (codeLabelEl) codeLabelEl.innerText = `🏷️ كود ${cfg.label}:`;

            const iconWrap = document.getElementById('md-del-modal-icon-wrap') || document.getElementById('item-del-modal-icon-wrap');
            const iconEl = document.getElementById('md-del-modal-icon') || document.getElementById('item-del-modal-icon');
            if (iconWrap) {
                iconWrap.style.background = 'rgba(56,189,248,0.15)';
                iconWrap.style.borderColor = 'rgba(56,189,248,0.3)';
                iconWrap.style.boxShadow = '0 0 20px rgba(56,189,248,0.2)';
            }
            if (iconEl) iconEl.innerText = '🔍';

            const titleEl = document.getElementById('md-del-modal-title') || document.getElementById('item-del-modal-title');
            const subtitleEl = document.getElementById('md-del-modal-subtitle') || document.getElementById('item-del-modal-subtitle');
            if (titleEl) {
                titleEl.innerText = `جاري فحص ارتباطات ${cfg.label} بالسندات...`;
                titleEl.style.color = '#fff';
            }
            if (subtitleEl) subtitleEl.innerText = `يتم التحقق من الفواتير، السندات، والقيود المحاسبية وحركات المخزون`;

            const statusBox = document.getElementById('md-del-status-box') || document.getElementById('item-del-status-box');
            if (statusBox) {
                statusBox.style.background = 'rgba(56,189,248,0.08)';
                statusBox.style.borderColor = 'rgba(56,189,248,0.2)';
                statusBox.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:center; gap:12px; padding:15px; color:#38bdf8; font-weight:800; font-size:1.1rem;">
                        <span style="display:inline-block; animation:spin 1s infinite linear;">⏳</span>
                        <span>جاري فحص السجلات المحاسبية والمخزنية وقاعدة البيانات...</span>
                    </div>
                `;
            }

            const vouchersContainer = document.getElementById('md-del-vouchers-container') || document.getElementById('item-del-vouchers-container');
            if (vouchersContainer) vouchersContainer.style.display = 'none';

            const footerEl = document.getElementById('md-del-modal-footer') || document.getElementById('item-del-modal-footer');
            if (footerEl) {
                footerEl.innerHTML = `
                    <button type="button" onclick="closeEntityDeleteModal()" class="btn-action" style="padding:8px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; cursor:pointer;">إلغاء</button>
                `;
            }

            modal.style.display = 'flex';
        }

        function scanEntityLocalUsages(entityType, entity) {
            const usages = [];
            const entId = String(entity.id || '');
            const entName = String(entity.name || '').trim().toLowerCase();
            const barcodes = [entity.barcode || ''];
            if (Array.isArray(entity.units)) {
                entity.units.forEach(u => { if (u && u.barcode) barcodes.push(u.barcode); });
            }

            const isMatch = (val) => {
                if (val === null || val === undefined || val === '') return false;
                const s = String(val).trim();
                const sl = s.toLowerCase();
                if (entId && s === entId) return true;
                if (entName && sl === entName) return true;
                if (entId && (s === 'cust-' + entId || s === 'supp-' + entId || s === 'wh-' + entId || s === 'safe-' + entId || s === 'bank-' + entId)) return true;
                if (barcodes.includes(s)) return true;
                return false;
            };

            // 1. Sales Invoices
            try {
                const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
                if (Array.isArray(sales)) {
                    sales.forEach(s => {
                        if (!s) return;
                        let matched = false;
                        if (entityType === 'warehouse') {
                            if (isMatch(s.warehouse) || isMatch(s.warehouseId) || isMatch(s.deliveryWarehouse)) matched = true;
                            if (Array.isArray(s.items)) {
                                s.items.forEach(it => { if (isMatch(it.warehouseId) || isMatch(it.warehouse)) matched = true; });
                            }
                        } else if (entityType === 'customer') {
                            if (isMatch(s.customer) || isMatch(s.customerId) || isMatch(s.customerName) || isMatch(s.client)) matched = true;
                        } else if (entityType === 'bank') {
                            if (isMatch(s.paymentAccountId) || isMatch(s.bankId) || isMatch(s.paymentAccount) || isMatch(s.bank)) matched = true;
                        } else if (entityType === 'safe') {
                            if (isMatch(s.safe) || isMatch(s.safeId) || isMatch(s.paymentAccountId) || isMatch(s.cashBox)) matched = true;
                        } else if (entityType === 'item') {
                            if (Array.isArray(s.items)) {
                                s.items.forEach(it => {
                                    if (isMatch(it.itemId) || isMatch(it.id) || isMatch(it.itemName) || isMatch(it.name) || isMatch(it.barcode) || isMatch(it.qrCode)) matched = true;
                                });
                            }
                        } else if (entityType === 'unit') {
                            if (Array.isArray(s.items)) {
                                s.items.forEach(it => { if (isMatch(it.selectedUnit) || isMatch(it.unit)) matched = true; });
                            }
                        } else if (entityType === 'cost_center') {
                            if (isMatch(s.costCenter) || isMatch(s.costCenterId)) matched = true;
                        } else if (entityType === 'employee') {
                            if (isMatch(s.createdBy) || isMatch(s.salesRep) || isMatch(s.salesman) || isMatch(s.driver) || isMatch(s.user)) matched = true;
                        }

                        if (matched) {
                            const invNo = s.invoiceNo || s.id || '';
                            usages.push({
                                type: 'فاتورة مبيعات',
                                doc_no: String(invNo),
                                date: s.date || '',
                                label: `فاتورة مبيعات رقم ${invNo}` + (s.date ? ` بتاريخ ${s.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 2. Purchase Invoices
            try {
                const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
                if (Array.isArray(purchases)) {
                    purchases.forEach(p => {
                        if (!p) return;
                        let matched = false;
                        if (entityType === 'warehouse') {
                            if (isMatch(p.warehouse) || isMatch(p.warehouseId)) matched = true;
                            if (Array.isArray(p.items)) {
                                p.items.forEach(it => { if (isMatch(it.warehouseId) || isMatch(it.warehouse)) matched = true; });
                            }
                        } else if (entityType === 'supplier') {
                            if (isMatch(p.supplier) || isMatch(p.supplierId) || isMatch(p.supplierName) || isMatch(p.customer)) matched = true;
                        } else if (entityType === 'bank') {
                            if (isMatch(p.paymentAccountId) || isMatch(p.bankId) || isMatch(p.paymentAccount) || isMatch(p.bank)) matched = true;
                        } else if (entityType === 'safe') {
                            if (isMatch(p.safe) || isMatch(p.safeId) || isMatch(p.paymentAccountId) || isMatch(p.cashBox)) matched = true;
                        } else if (entityType === 'item') {
                            if (Array.isArray(p.items)) {
                                p.items.forEach(it => {
                                    if (isMatch(it.itemId) || isMatch(it.id) || isMatch(it.itemName) || isMatch(it.name) || isMatch(it.barcode) || isMatch(it.qrCode)) matched = true;
                                });
                            }
                        } else if (entityType === 'unit') {
                            if (Array.isArray(p.items)) {
                                p.items.forEach(it => { if (isMatch(it.selectedUnit) || isMatch(it.unit)) matched = true; });
                            }
                        } else if (entityType === 'cost_center') {
                            if (isMatch(p.costCenter) || isMatch(p.costCenterId)) matched = true;
                        } else if (entityType === 'employee') {
                            if (isMatch(p.createdBy) || isMatch(p.user)) matched = true;
                        }

                        if (matched) {
                            const invNo = p.invoiceNo || p.id || '';
                            usages.push({
                                type: 'فاتورة مشتريات',
                                doc_no: String(invNo),
                                date: p.date || '',
                                label: `فاتورة مشتريات رقم ${invNo}` + (p.date ? ` بتاريخ ${p.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 3. Single Payments & Receipts
            try {
                const singlePay = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
                if (Array.isArray(singlePay)) {
                    singlePay.forEach(sp => {
                        if (!sp) return;
                        let matched = false;
                        if (['customer', 'supplier', 'bank', 'safe'].includes(entityType)) {
                            if (isMatch(sp.fromAccount) || isMatch(sp.toAccount) || isMatch(sp.accountId) || isMatch(sp.partyId)) matched = true;
                        } else if (entityType === 'cost_center' && (isMatch(sp.costCenter) || isMatch(sp.costCenterId))) {
                            matched = true;
                        } else if (entityType === 'employee' && (isMatch(sp.createdBy) || isMatch(sp.paidBy))) {
                            matched = true;
                        }
                        if (matched) {
                            const no = sp.id || sp.voucherNo || '';
                            usages.push({
                                type: 'سند صرف',
                                doc_no: String(no),
                                date: sp.date || '',
                                label: `سند صرف رقم ${no}` + (sp.date ? ` بتاريخ ${sp.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            try {
                const singleRec = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                if (Array.isArray(singleRec)) {
                    singleRec.forEach(sr => {
                        if (!sr) return;
                        let matched = false;
                        if (['customer', 'supplier', 'bank', 'safe'].includes(entityType)) {
                            if (isMatch(sr.fromAccount) || isMatch(sr.toAccount) || isMatch(sr.accountId) || isMatch(sr.partyId)) matched = true;
                        } else if (entityType === 'cost_center' && (isMatch(sr.costCenter) || isMatch(sr.costCenterId))) {
                            matched = true;
                        } else if (entityType === 'employee' && (isMatch(sr.createdBy) || isMatch(sr.receivedBy) || isMatch(sr.collectorName))) {
                            matched = true;
                        }
                        if (matched) {
                            const no = sr.id || sr.voucherNo || '';
                            usages.push({
                                type: 'سند قبض',
                                doc_no: String(no),
                                date: sr.date || '',
                                label: `سند قبض رقم ${no}` + (sr.date ? ` بتاريخ ${sr.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 4. Delivery Notes
            try {
                const dNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1')) || [];
                if (Array.isArray(dNotes)) {
                    dNotes.forEach(dn => {
                        if (!dn) return;
                        let matched = false;
                        if (entityType === 'warehouse' && (isMatch(dn.warehouse) || isMatch(dn.warehouseId) || isMatch(dn.targetWarehouse))) matched = true;
                        if (entityType === 'customer' && (isMatch(dn.customer) || isMatch(dn.customerId) || isMatch(dn.customerName))) matched = true;
                        if (entityType === 'employee' && (isMatch(dn.driver) || isMatch(dn.deliveredBy) || isMatch(dn.receivedBy) || isMatch(dn.createdBy))) matched = true;
                        if (entityType === 'item' && Array.isArray(dn.items)) {
                            dn.items.forEach(it => { if (isMatch(it.itemId) || isMatch(it.id) || isMatch(it.itemName) || isMatch(it.name)) matched = true; });
                        }
                        if (entityType === 'unit' && Array.isArray(dn.items)) {
                            dn.items.forEach(it => { if (isMatch(it.selectedUnit) || isMatch(it.unit)) matched = true; });
                        }
                        if (matched) {
                            const no = dn.noteNo || dn.id || '';
                            usages.push({
                                type: 'سند تسليم / إذن صرف',
                                doc_no: String(no),
                                date: dn.date || '',
                                label: `سند تسليم رقم ${no}` + (dn.date ? ` بتاريخ ${dn.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 5. Items table for Unit & Category usage
            if (entityType === 'unit' || entityType === 'category') {
                try {
                    const items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                    if (Array.isArray(items)) {
                        items.forEach(it => {
                            if (!it || it.deleted) return;
                            if (entityType === 'unit') {
                                let matched = isMatch(it.baseUnit) || isMatch(it.unit);
                                if (Array.isArray(it.units)) {
                                    it.units.forEach(u => { if (u && isMatch(u.unitName)) matched = true; });
                                }
                                if (matched) {
                                    usages.push({
                                        type: 'بطاقة صنف',
                                        doc_no: String(it.id || it.name),
                                        date: '',
                                        label: `مستخدم كوحدة في بطاقة صنف: ${it.name || it.id}`
                                    });
                                }
                            } else if (entityType === 'category') {
                                if (isMatch(it.category) || isMatch(it.subCategory)) {
                                    usages.push({
                                        type: 'بطاقة صنف',
                                        doc_no: String(it.id || it.name),
                                        date: '',
                                        label: `مستخدم كفئة للصنف: ${it.name || it.id}`
                                    });
                                }
                            }
                        });
                    }
                } catch(e) {}
            }

            return usages;
        }

        function renderEntityDeleteModalResult(cfg, entity, isUsed, allUsages) {
            const iconWrap = document.getElementById('md-del-modal-icon-wrap') || document.getElementById('item-del-modal-icon-wrap');
            const iconEl = document.getElementById('md-del-modal-icon') || document.getElementById('item-del-modal-icon');
            const titleEl = document.getElementById('md-del-modal-title') || document.getElementById('item-del-modal-title');
            const subtitleEl = document.getElementById('md-del-modal-subtitle') || document.getElementById('item-del-modal-subtitle');
            const statusBox = document.getElementById('md-del-status-box') || document.getElementById('item-del-status-box');
            const vouchersContainer = document.getElementById('md-del-vouchers-container') || document.getElementById('item-del-vouchers-container');
            const vouchersList = document.getElementById('md-del-vouchers-list') || document.getElementById('item-del-vouchers-list');
            const vouchersCount = document.getElementById('md-del-vouchers-count') || document.getElementById('item-del-vouchers-count');
            const vouchersHeader = document.getElementById('md-del-vouchers-header-label');
            const footerEl = document.getElementById('md-del-modal-footer') || document.getElementById('item-del-modal-footer');

            const displayName = entity.name || entity.id || cfg.label;

            if (isUsed) {
                // STATE 1: USED IN VOUCHER -> PREVENT DELETION
                if (iconWrap) {
                    iconWrap.style.background = 'rgba(239, 68, 68, 0.18)';
                    iconWrap.style.borderColor = 'rgba(239, 68, 68, 0.45)';
                    iconWrap.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.35)';
                }
                if (iconEl) iconEl.innerText = '🚫';

                if (titleEl) {
                    titleEl.innerText = `لا يمكن حذف هذا ${cfg.label} نهائياً`;
                    titleEl.style.color = '#f87171';
                }
                if (subtitleEl) subtitleEl.innerText = `${cfg.label} مرتبط بسجلات وسندات وفواتير رسمية بالنظام`;

                if (statusBox) {
                    statusBox.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(185, 28, 28, 0.08))';
                    statusBox.style.borderColor = 'rgba(239, 68, 68, 0.35)';
                    statusBox.innerHTML = `
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <div style="display:flex; align-items:center; gap:8px; color:#fca5a5; font-weight:800; font-size:1.05rem;">
                                <span>⚠️</span>
                                <span>تنبيه نظام مقاييس المحاسبي:</span>
                            </div>
                            <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                                تم استخدام ${cfg.label} (<span style="color:#38bdf8; font-weight:800;">${displayName}</span>) في عدد <span style="color:#f87171; font-weight:900; font-size:1.15rem; background:rgba(239,68,68,0.2); padding:1px 8px; border-radius:6px;">${allUsages.length}</span> سند / حركة / سجل مسجل.
                            </p>
                            <div style="background:rgba(0,0,0,0.3); border-radius:10px; padding:10px 14px; margin-top:4px; border-right:3px solid #ef4444;">
                                <span style="color:#cbd5e1; font-size:0.88rem; font-weight:700;">
                                    🔒 طبقاً لمعايير المحاسبة والرقابة والضبط المالي الصارمة، <strong style="color:#f87171;">لا يمكن حذف أي ${cfg.label} له حركات أو سندات مرتبطة</strong> نهائياً، وذلك لحماية سلامة القوائم المالية، الحسابات، والتقارير الرسمية.
                                </span>
                            </div>
                        </div>
                    `;
                }

                // Render Vouchers List
                if (vouchersHeader) vouchersHeader.innerText = `📋 السندات والفواتير المرتبطة بهذا ${cfg.label}:`;
                if (vouchersCount) vouchersCount.innerText = `${allUsages.length} سند / سجل`;
                if (vouchersList) {
                    vouchersList.innerHTML = allUsages.map((u, idx) => `
                        <div style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:8px 12px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="color:#f87171; font-weight:bold; font-size:0.85rem;">${idx + 1}.</span>
                                <span style="color:#e2e8f0; font-weight:700; font-size:0.9rem;">${u.label || (u.type + ' رقم ' + u.doc_no)}</span>
                            </div>
                            <span style="background:rgba(239,68,68,0.15); color:#fca5a5; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:4px;">${u.type}</span>
                        </div>
                    `).join('');
                }
                if (vouchersContainer) vouchersContainer.style.display = 'block';

                // Footer with Close button only
                if (footerEl) {
                    footerEl.innerHTML = `
                        <button type="button" onclick="closeEntityDeleteModal()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #3b82f6, #1d4ed8); border:none; color:#fff; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(59,130,246,0.4);">حسناً، فهمت</button>
                    `;
                }

            } else {
                // STATE 2: NOT USED IN ANY VOUCHER -> ALLOW HARD DELETE
                if (iconWrap) {
                    iconWrap.style.background = 'rgba(245, 158, 11, 0.18)';
                    iconWrap.style.borderColor = 'rgba(245, 158, 11, 0.45)';
                    iconWrap.style.boxShadow = '0 0 25px rgba(245, 158, 11, 0.35)';
                }
                if (iconEl) iconEl.innerText = '⚠️';

                if (titleEl) {
                    titleEl.innerText = `تأكيد حذف ${cfg.label} نهائياً`;
                    titleEl.style.color = '#fbbf24';
                }
                if (subtitleEl) subtitleEl.innerText = `${cfg.label} غير مستخدم في أي سند أو حركة`;

                if (statusBox) {
                    statusBox.style.background = 'linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(239, 68, 68, 0.06))';
                    statusBox.style.borderColor = 'rgba(245, 158, 11, 0.3)';
                    statusBox.innerHTML = `
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div style="display:flex; align-items:center; gap:8px; color:#34d399; font-weight:800; font-size:1rem;">
                                <span>✅</span>
                                <span>تم التحقق: هذا ${cfg.label} غير مستخدم في أي سند أو فاتورة أو حركة.</span>
                            </div>
                            <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                                هل أنت متأكد من رغبتك في حذف ${cfg.label} (<span style="color:#38bdf8; font-weight:800;">${displayName}</span>)؟
                            </p>
                            <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.35); border-radius:10px; padding:12px 14px; margin-top:4px;">
                                <div style="display:flex; align-items:center; gap:8px; color:#f87171; font-weight:900; font-size:0.95rem; margin-bottom:4px;">
                                    <span>🚨</span>
                                    <span>تحذير نهائي لا يمكن الرجوع فيه:</span>
                                </div>
                                <span style="color:#fecaca; font-size:0.88rem; font-weight:700; line-height:1.5; display:block;">
                                    سيتم حذف ${cfg.label} <strong>نهائياً وبشكل دائم من قاعدة البيانات (MySQL) ومن المتصفح</strong>، ولن يمكن استرجاعه أو الرجوع فيه ثانية.
                                </span>
                            </div>
                        </div>
                    `;
                }

                if (vouchersContainer) vouchersContainer.style.display = 'none';

                // Footer with Confirm Delete and Cancel buttons
                if (footerEl) {
                    footerEl.innerHTML = `
                        <button type="button" onclick="closeEntityDeleteModal()" class="btn-action" style="padding:10px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:800; cursor:pointer;">إلغاء الأمر</button>
                        <button type="button" id="md-del-confirm-btn" onclick="confirmExecuteHardDeleteEntity()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #ef4444, #dc2626); border:1px solid rgba(239,68,68,0.5); color:#fff; font-weight:900; font-size:1rem; cursor:pointer; box-shadow:0 6px 20px rgba(239,68,68,0.4);">🗑️ نعم، احذف ${cfg.label} نهائياً الآن</button>
                    `;
                }
            }
        }

        async function genericDeleteEntity(entityType, id, customName = null) {
            const cfg = ENTITY_CONFIG[entityType];
            if (!cfg) return;

            let entity = { id: String(id), name: customName || String(id) };

            // Find entity details from localStorage
            try {
                const list = JSON.parse(localStorage.getItem(cfg.localKey)) || [];
                if (Array.isArray(list)) {
                    if (entityType === 'unit' || entityType === 'category') {
                        const found = list.find(u => String(u) === String(id));
                        if (found) entity = { id: found, name: found };
                    } else {
                        const found = list.find(item => item && (
                            String(item.id) === String(id) || 
                            String(item.frontend_id) === String(id) || 
                            String(item.name || item.title || item.username) === String(id)
                        ));
                        if (found) {
                            entity = {
                                ...found,
                                id: found.id || found.frontend_id || id,
                                name: found.name || found.fullName || found.title || found.username || customName || id
                            };
                        }
                    }
                }
            } catch(e) {}

            currentDeleteTarget = { entityType, entity, cfg };

            // 1. Open modal loading state
            openEntityDeleteModalLoading(cfg, entity);

            // 2. Scan local usages
            const localUsages = scanEntityLocalUsages(entityType, entity);

            // 3. Scan backend MySQL usages
            let backendUsages = [];
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const chkHeaders = { 'Content-Type': 'application/json' };
                if (token) chkHeaders['Authorization'] = 'Bearer ' + token;
                if (tenantId) chkHeaders['X-Tenant-Id'] = String(tenantId);

                const res = await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: chkHeaders,
                    body: JSON.stringify({
                        type: 'check_entity_usage',
                        data: {
                            entity_type: entityType,
                            id: entity.id,
                            name: entity.name,
                            barcode: entity.barcode || ''
                        }
                    })
                });
                const resJson = await res.json();
                if (resJson && resJson.success) {
                    backendUsages = resJson.usages || [];
                }
            } catch(e) {
                console.warn(`Backend ${entityType} usage check error:`, e);
            }

            // 4. Merge unique usages
            const allUsagesMap = new Map();
            [...localUsages, ...backendUsages].forEach(u => {
                const key = (u.type || '') + '_' + (u.doc_no || '') + '_' + (u.date || '');
                if (!allUsagesMap.has(key)) {
                    allUsagesMap.set(key, u);
                }
            });

            const allUsages = Array.from(allUsagesMap.values());
            const isUsed = allUsages.length > 0;

            renderEntityDeleteModalResult(cfg, entity, isUsed, allUsages);
        }

        function deleteItem(id) {
            genericDeleteEntity('item', id);
        }

        async function confirmExecuteHardDeleteEntity() {
            if (!currentDeleteTarget) return;
            const { entityType, entity, cfg } = currentDeleteTarget;

            const btn = document.getElementById('md-del-confirm-btn') || document.getElementById('item-del-confirm-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '⏳ جاري الحذف النهائي من قاعدة البيانات...';
            }

            try {
                // 1. Send hard delete request to backend MySQL
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const delHeaders = { 'Content-Type': 'application/json' };
                if (token) delHeaders['Authorization'] = 'Bearer ' + token;
                if (tenantId) delHeaders['X-Tenant-Id'] = String(tenantId);

                const res = await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: delHeaders,
                    body: JSON.stringify({
                        type: cfg.backendType,
                        data: {
                            id: entity.id,
                            name: entity.name,
                            barcode: entity.barcode || ''
                        }
                    })
                });

                const json = await res.json();
                if (!json || (!json.success && json.error)) {
                    const errMsg = (json && json.error) ? json.error : 'فشل الحذف من قاعدة البيانات';
                    alert('🚫 خطأ: ' + errMsg);
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = `🗑️ نعم، احذف ${cfg.label} نهائياً الآن`;
                    }
                    return;
                }

                // 2. Remove permanently from localStorage (Hard delete from array)
                try {
                    let list = JSON.parse(localStorage.getItem(cfg.localKey)) || [];
                    if (Array.isArray(list)) {
                        if (entityType === 'unit' || entityType === 'category') {
                            list = list.filter(u => String(u).trim() !== String(entity.name || entity.id).trim());
                        } else {
                            list = list.filter(item => {
                                if (!item) return false;
                                if (String(item.id) === String(entity.id)) return false;
                                if (item.frontend_id && String(item.frontend_id) === String(entity.id)) return false;
                                if (entity.name && item.name && item.name.trim() === entity.name.trim()) return false;
                                return true;
                            });
                        }
                        localStorage.setItem(cfg.localKey, JSON.stringify(list));
                    }
                } catch(e) {}

                // Handle secondary localStorage keys if any
                if (entityType === 'cost_center') {
                    try {
                        let ccs2 = JSON.parse(localStorage.getItem('erp_cost_centers_v1')) || [];
                        if (Array.isArray(ccs2)) {
                            ccs2 = ccs2.filter(c => c && String(c.id) !== String(entity.id) && String(c.name) !== String(entity.name));
                            localStorage.setItem('erp_cost_centers_v1', JSON.stringify(ccs2));
                        }
                    } catch(e) {}
                }

                // 3. Close modal & re-render
                closeEntityDeleteModal();
                cfg.render();

                const successMsg = `✅ تم حذف ${cfg.label} (${entity.name || entity.id}) نهائياً من قاعدة البيانات بنجاح ولا يمكن استرجاعه`;
                if (typeof showToast === 'function') {
                    showToast(successMsg, 'success');
                } else {
                    alert(successMsg);
                }
            } catch(err) {
                console.error(`Error hard deleting ${entityType}:`, err);
                alert(`حدث خطأ أثناء الاتصال بقاعدة البيانات لحذف ${cfg.label}: ` + err.message);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `🗑️ نعم، احذف ${cfg.label} نهائياً الآن`;
                }
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
            
            document.getElementById('form-mode-indicator').innerText = 'إضافة صنف جديد';
            document.getElementById('item-code').value = '';
            document.getElementById('item-name-input').value = '';
            document.getElementById('item-name-latin').value = '';
            document.getElementById('item-barcode').value = '';
            document.getElementById('item-sub-category').value = '';
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
        const DEFAULT_STANDARD_UNITS = [
            'طن', 'طن وزني', 'طن عددي', 'طن العددي',
            'سيخه', 'سيخة / عود', 'كيس', 'حبة', 'ألف حبة',
            'متر مكعب', 'متر مربع', 'متر طولي',
            'كرتونة', 'كرتونة (4 جالون)', 'درزن', 'جالون',
            'لتر', 'كيلو', 'قطعة', 'عددي', 'قلاب 12متر'
        ];

        function sanitizeGlobalUnits() {
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
                if (!Array.isArray(units)) units = [];
            } catch(e) { units = []; }

            // 1. Purge corrupted question mark strings
            let cleaned = units.filter(u => {
                if (!u || typeof u !== 'string') return false;
                let trimmed = u.trim();
                if (trimmed === '' || trimmed.includes('?')) return false;
                return true;
            });

            // 2. Ensure standard defaults are present
            DEFAULT_STANDARD_UNITS.forEach(def => {
                if (!cleaned.includes(def)) {
                    cleaned.push(def);
                }
            });

            // 3. Clean any corrupted units in erp_items_v1
            try {
                let items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                let itemsModified = false;
                items.forEach(item => {
                    if (item.baseUnit && typeof item.baseUnit === 'string' && item.baseUnit.includes('?')) {
                        item.baseUnit = 'حبة';
                        itemsModified = true;
                    }
                    if (item.unit && typeof item.unit === 'string' && item.unit.includes('?')) {
                        item.unit = 'حبة';
                        itemsModified = true;
                    }
                    if (Array.isArray(item.units)) {
                        item.units = item.units.filter(u => u && (!u.unitName || !u.unitName.includes('?')));
                        itemsModified = true;
                    }
                    if (Array.isArray(item.unitsArray)) {
                        item.unitsArray = item.unitsArray.filter(u => u && (!u.name || !u.name.includes('?')));
                        itemsModified = true;
                    }
                });
                if (itemsModified) {
                    localStorage.setItem('erp_items_v1', JSON.stringify(items));
                }
            } catch(e) {}

            localStorage.setItem('erp_global_units_v1', JSON.stringify(cleaned));
            return cleaned;
        }

        function saveGlobalUnit() {
            const input = document.getElementById('unit-name-input');
            const name = input ? input.value.trim() : '';
            if(!name) { alert('الرجاء إدخال اسم الوحدة'); return; }
            if(name.includes('?')) { alert('اسم الوحدة يحتوي على رموز غير صالحة (?)'); return; }
            
            let units = sanitizeGlobalUnits();
            if(!units.includes(name)) { 
                units.push(name); 
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units)); 
                syncToBackend('units', units);
            }
            if (input) input.value = '';
            renderGlobalUnitsList();
            updateUnitDropdowns();
            if (typeof showToast === 'function') showToast('✅ تم حفظ الوحدة بنجاح', 'success');
        }

        function editGlobalUnit(oldName) {
            const newName = prompt('تعديل اسم الوحدة:', oldName);
            if(!newName || newName.trim() === '' || newName.trim() === oldName) return;
            const cleanNew = newName.trim();
            if(cleanNew.includes('?')) { alert('الاسم يحتوي على رموز غير صالحة (?)'); return; }

            let units = sanitizeGlobalUnits();
            const idx = units.indexOf(oldName);
            if(idx > -1) {
                units[idx] = cleanNew;
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
                syncToBackend('units', units);
            }

            // Update items referencing this unit
            try {
                let items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                items.forEach(item => {
                    if(item.baseUnit === oldName) item.baseUnit = cleanNew;
                    if(item.unit === oldName) item.unit = cleanNew;
                    if(Array.isArray(item.units)) {
                        item.units.forEach(u => { if(u.unitName === oldName) u.unitName = cleanNew; });
                    }
                    if(Array.isArray(item.unitsArray)) {
                        item.unitsArray.forEach(u => { if(u.name === oldName) u.name = cleanNew; });
                    }
                });
                localStorage.setItem('erp_items_v1', JSON.stringify(items));
            } catch(e) {}

            renderGlobalUnitsList();
            updateUnitDropdowns();
            if (typeof renderItems === 'function') renderItems();
        }

        function deleteGlobalUnit(name) {
            genericDeleteEntity('unit', name, name);
        }

        function cleanAndResetUnits() {
            if(confirm('هل تريد تنظيف وإزالة كافة الوحدات التالفة والرموز غير الصالحة واستعادة الوحدات القياسية؟')) {
                localStorage.removeItem('erp_global_units_v1');
                const cleanU = sanitizeGlobalUnits();
                syncToBackend('units', cleanU);
                renderGlobalUnitsList();
                updateUnitDropdowns();
                alert('✅ تم تنظيف وإصلاح مصفوفة الوحدات بنجاح!');
            }
        }

        function renderGlobalUnitsList() {
            const tbody = document.getElementById('units-table-body');
            if(!tbody) return;
            let units = sanitizeGlobalUnits();
            tbody.innerHTML = '';
            units.forEach(u => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="font-weight:bold; font-size:1.1rem; color:#38bdf8; padding:12px;">${u}</td>
                    <td style="text-align:center; padding:12px; white-space:nowrap;">
                        <button class="btn-text" style="color:#38bdf8; margin-left:12px; font-weight:bold;" onclick="editGlobalUnit('${u}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); font-weight:bold;" onclick="deleteGlobalUnit('${u}')">🗑️ حذف</button>
                    </td>
                </tr>`);
            });
        }

        function importLegacyUnits() {
            // Import units that were manually typed in items without importing corrupted '?' strings
            let items = [];
            try { items = JSON.parse(localStorage.getItem('erp_items_v1')) || []; } catch(e) {}
            if(items.length === 0) return;
            let units = sanitizeGlobalUnits();
            let added = false;
            items.forEach(i => {
                if(i.baseUnit && typeof i.baseUnit === 'string' && !i.baseUnit.includes('?') && !units.includes(i.baseUnit.trim())) { 
                    units.push(i.baseUnit.trim()); 
                    added = true; 
                }
                if(i.units && Array.isArray(i.units)) {
                    i.units.forEach(u => {
                        if(u.unitName && typeof u.unitName === 'string' && !u.unitName.includes('?') && !units.includes(u.unitName.trim())) { 
                            units.push(u.unitName.trim()); 
                            added = true; 
                        }
                    });
                }
            });
            if(added) {
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            }
        }

        function updateUnitDropdowns(selectedValue = '') {
            let units = sanitizeGlobalUnits();
            
            const options = units.map(u => `<option value="${u}">${u}</option>`).join('');
            const baseSelect = document.getElementById('item-base-unit');
            if(baseSelect) {
                const current = selectedValue || baseSelect.value;
                baseSelect.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                if(current && units.includes(current)) baseSelect.value = current;
            }
            
            // Also update any dynamic selects in the matrix
            document.querySelectorAll('.unit-name-input').forEach(sel => {
                if(sel.tagName === 'SELECT') {
                    const current = sel.value;
                    sel.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                    if(current && units.includes(current)) sel.value = current;
                }
            });
        }

        // ================= CATEGORY LOGIC =================
        function saveCategory() {
            const name = document.getElementById('cat-name-input').value;
            if(!name) { alert('أدخل اسم الفئة'); return; }
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); syncToBackend('categories', cats); }
            document.getElementById('cat-name-input').value = '';
            renderCategoriesList();
            updateCategoryDropdowns();
        }

        function renderCategoriesList() {
            const tbody = document.getElementById('categories-table-body');
            if(!tbody) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
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
            if(kpi) kpi.innerText = cats.length + ' فئة';
            tbody.innerHTML = '';
            cats.forEach(c => {
                const count = items.filter(i => i.category === c).length;
                const subs = [...new Set(items.filter(i => i.category === c && i.subCategory).map(i => i.subCategory))];
                const subHtml = subs.length > 0 ? '<br><small style="color:#a855f7;">' + subs.join(' | ') + '</small>' : '';
                tbody.insertAdjacentHTML('beforeend', '<tr><td style="font-weight:bold; color:#a855f7;">' + c + subHtml + '</td><td><span style="background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px;">' + count + '</span></td><td><button class="btn-text" style="color:#38bdf8; margin-left:10px;" onclick="editCategory(\'' + c + '\')">✏️ تعديل</button><button class="btn-text" style="color:var(--danger-color);" onclick="deleteCategory(\'' + c + '\')">🗑️ حذف</button></td></tr>');
            });
        }

        function editCategory(oldName) {
            const newName = prompt('تعديل اسم الفئة:', oldName);
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
            syncToBackend('categories', cats);
            syncToBackend('items_bulk', items);
            renderCategoriesList();
            updateCategoryDropdowns();
            renderItems();
            if (typeof showToast === 'function') showToast('✅ تم تعديل الفئة وتحديث الأصناف في قاعدة البيانات بنجاح', 'success');
        }

        function deleteCategory(name) {
            genericDeleteEntity('category', name, name);
        }

        function updateCategoryDropdowns() {
            const select = document.getElementById('item-category');
            if(!select) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
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
            
            if(!name.trim()) { alert('الرجاء إدخال اسم مركز التكلفة'); return; }
            if(isDuplicateName('erp_cost_centers_v1', name, id)) { alert('الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.'); return; }

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
            localStorage.setItem('erp_cost_centers_v1', JSON.stringify(ccs));
            syncToBackend('cost_centers', ccs);
            closeCostCenterForm();
            renderCostCenters();
            if (typeof showToast === 'function') showToast('✅ تم حفظ مركز التكلفة نهائياً في قاعدة البيانات بنجاح', 'success');
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
                        <button class="btn-text" onclick="editCostCenter('${c.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCostCenter('${c.id}')">حذف</button>
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
            genericDeleteEntity('cost_center', id);
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
                'SDG': 'جنيه سوداني',
                'USD': 'دولار',
                'SAR': 'ريال سعودي',
                'AED': 'درهم إماراتي',
                'EGP': 'جنيه مصري'
            };
            
            for (let cur in rates) {
                const tr = document.createElement('tr');
                const isBase = cur === 'SDG';
                const readonlyAttr = isBase ? 'readonly disabled style="background:rgba(0,0,0,0.1); color:#94a3b8; cursor:not-allowed;"' : '';
                const helperText = isBase ? '<span style="color:#94a3b8; font-size:0.75rem;">(العملة الأساسية للنظام)</span>' : '';
                
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
            syncToBackend('currency_rates', rates);
            if (typeof showToast === 'function') {
                showToast('✅ تم حفظ أسعار صرف العملات نهائياً في قاعدة البيانات بنجاح', 'success');
            } else {
                alert('✅ تم حفظ معاملات العملات بنجاح!');
            }
        }

        function populateSystemCurrencySelects() {
            const selects = document.querySelectorAll('.system-currency-select');
            
            const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
            const baseCur = cfg.currency || 'SDG';
            const foreignCur = cfg.foreignCurrency || 'USD';
            
            const currencyLabels = {
                'SDG': 'SDG - جنيه سوداني (محلي)',
                'USD': 'USD - دولار أمريكي',
                'SAR': 'SAR - ريال سعودي',
                'AED': 'AED - درهم إماراتي',
                'EGP': 'EGP - جنيه مصري',
                'QAR': 'QAR - ريال قطري'
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
                if (exLabel) exLabel.innerHTML = `💵 سعر الصرف (قيمة 1 ${cur} مقابل العملة المحلية SDG - مسجل بالإعدادات: ${defaultRate.toLocaleString()})`;
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

