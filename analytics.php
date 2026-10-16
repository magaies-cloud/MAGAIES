<?php $page_title = 'التقارير'; ?>
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
    <title>مقاييس 2026 - التقارير</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- استدعاء وتفعيل مكتبة Tailwind CSS للتنسيق والألوان -->
    <link rel="stylesheet" href="css/tailwind.min.css">
    <!-- استدعاء مكتبة الأيقونات الشهيرة FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    

        :root.light-theme {
            font-size: 106% !important; /* Increase typography by 1 step */
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --glass-border: #000000;
        }
        :root.light-theme body {
            background-color: var(--bg-color);
            color: #000000 !important;
        }
        :root.light-theme .ambient-bg {
            background: #f1f5f9 !important; /* Pure light off-white background */
        }
        :root.light-theme .glass-panel,
        :root.light-theme .chart-card,
        :root.light-theme .ai-insights-section,
        :root.light-theme .ai-card,
        :root.light-theme .report-category-card,
        :root.light-theme .quick-report-card,
        :root.light-theme .kpi-card {
            background: #ffffff !important; /* Pure white card for contrast */
            border: 3.5px solid #000000 !important; /* Thicker bold black border (3.5px) */
            border-radius: 12px !important; /* Sharper rounded corners (تقليل الحواف) */
            color: #000000 !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }
        
        :root.light-theme .glass-panel:hover,
        :root.light-theme .chart-card:hover,
        :root.light-theme .ai-insights-section:hover,
        :root.light-theme .report-category-card:hover,
        :root.light-theme .kpi-card:hover {
            border-color: #7c3aed !important; /* Change border color slightly to a beautiful bright purple */
            box-shadow: 0 15px 35px rgba(124, 58, 237, 0.22) !important; /* Glow light effect */
            transform: translateY(-6px) scale(1.03) !important; /* Scale up slightly and lift */
            background: #ffffff !important;
        }

        /* Color-coded hover settings for quick reports bento grid */
        :root.light-theme .quick-report-card:hover {
            border-color: var(--glow-color) !important;
            box-shadow: 0 15px 35px var(--glow-color) !important;
            transform: translateY(-6px) scale(1.04) !important;
            background: #ffffff !important;
        }
        
        /* Color-coded hover settings for AI cards */
        :root.light-theme .ai-card:nth-child(1):hover { border-color: #8b5cf6 !important; box-shadow: 0 15px 35px rgba(139,92,246,0.25) !important; transform: translateY(-6px) scale(1.04) !important; background: #ffffff !important; }
        :root.light-theme .ai-card:nth-child(2):hover { border-color: #38bdf8 !important; box-shadow: 0 15px 35px rgba(56,189,248,0.25) !important; transform: translateY(-6px) scale(1.04) !important; background: #ffffff !important; }
        :root.light-theme .ai-card:nth-child(3):hover { border-color: #f87171 !important; box-shadow: 0 15px 35px rgba(248,113,113,0.25) !important; transform: translateY(-6px) scale(1.04) !important; background: #ffffff !important; }
        :root.light-theme .ai-card:nth-child(4):hover { border-color: #34d399 !important; box-shadow: 0 15px 35px rgba(52,211,153,0.25) !important; transform: translateY(-6px) scale(1.04) !important; background: #ffffff !important; }

        /* Gray letters override to dark black in Light Mode */
        :root.light-theme [style*="color:#94a3b8"],
        :root.light-theme [style*="color:#cbd5e1"],
        :root.light-theme [style*="color:#64748b"],
        :root.light-theme [style*="color: #94a3b8"],
        :root.light-theme [style*="color: #cbd5e1"],
        :root.light-theme [style*="color: #64748b"],
        :root.light-theme .kpi-label,
        :root.light-theme p,
        :root.light-theme span {
            color: #000000 !important;
        }

        :root.light-theme .chart-title {
            color: #000000 !important;
            font-weight: bold !important;
        }
        :root.light-theme h3,
        :root.light-theme h4,
        :root.light-theme .kpi-label {
            color: #000000 !important;
            font-weight: bold !important;
        }
        :root.light-theme .kpi-value {
            color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme .ai-card h4 {
            color: #000000 !important;
            font-weight: bold !important;
        }
        :root.light-theme .ai-card p {
            color: #000000 !important;
        }
        :root.light-theme .ai-card div[style*="background: rgba(0,0,0,0.3)"] {
            background: rgba(0, 0, 0, 0.03) !important;
            border: 1.5px solid #000000 !important;
            color: #000000 !important;
        }
        :root.light-theme .ai-card div[style*="background: rgba(0,0,0,0.3)"] strong {
            color: #000000 !important;
        }
        :root.light-theme .report-category-title {
            color: #0ea5e9 !important;
            font-weight: bold !important;
        }
        :root.light-theme .report-link-item {
            background: rgba(0, 0, 0, 0.03) !important;
            color: #000000 !important;
            border: 1.5px solid #000000 !important;
        }
        :root.light-theme .report-link-item:hover {
            background: rgba(0, 0, 0, 0.06) !important;
            color: #000000 !important;
            border-color: #000000 !important;
        }
        :root {
            --bg-color: #000000; /* Pure black background for high contrast */
            --surface: #13101a; /* Dark gray surface for card contrast */
            --surface-hover: #1e1926;
            --border: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.2);
            
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
            --theme-green: #10b981;
            --theme-purple: #8b5cf6;
        }

        body {
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            font-family: 'Tajawal', sans-serif;
            position: relative;
            color: #fff;
        }

        /* Ambient Animated Background Orbs */
        .ambient-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            animation: floatOrb 20s infinite alternate ease-in-out;
        }

        .orb-1 { width: 500px; height: 500px; background: var(--theme-pink); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-purple); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.08; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* CSS rules for the 10 strategic reports buttons */
        .strategic-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: linear-gradient(135deg, rgba(20, 16, 40, 0.6) 0%, rgba(10, 8, 24, 0.8) 100%) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1.5px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4);
            border-radius: 16px !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .strategic-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.03) 30%,
                rgba(255, 255, 255, 0.18) 50%,
                rgba(255, 255, 255, 0.03) 70%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            transition: all 0.6s ease;
            opacity: 0;
            pointer-events: none;
        }

        .strategic-btn:hover::after {
            left: 100%;
            opacity: 1;
            transition: all 0.8s ease-in-out;
        }

        .strategic-btn:hover {
            transform: translateY(-8px) scale(1.06) !important;
            border-color: var(--accent-color) !important;
            box-shadow: 0 12px 30px rgba(var(--shadow-rgb), 0.35), 0 0 15px rgba(var(--shadow-rgb), 0.25) !important;
            animation: gentle-wobble 0.6s ease-in-out;
            background: linear-gradient(135deg, rgba(30, 24, 60, 0.7) 0%, rgba(15, 12, 36, 0.9) 100%) !important;
        }

        @keyframes gentle-wobble {
            0% { transform: translateY(-8px) scale(1.06) rotate(0deg); }
            20% { transform: translateY(-8px) scale(1.06) rotate(1deg); }
            40% { transform: translateY(-8px) scale(1.06) rotate(-1deg); }
            60% { transform: translateY(-8px) scale(1.06) rotate(0.5deg); }
            80% { transform: translateY(-8px) scale(1.06) rotate(-0.5deg); }
            100% { transform: translateY(-8px) scale(1.06) rotate(0deg); }
        }

        html {
            scroll-behavior: smooth;
        }
        body {
            min-height: calc(100vh + 160px);
        }
        /* Layout Container taking full width with small equal padding */
        .analytics-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 30px 20px 30px; /* Match dashboard padding */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 30px;
            flex: 1;
            min-height: calc(100vh + 160px);
        }

        /* Ultra Premium Header */
        

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        
        .premium-header {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(79, 70, 229, 0.15), rgba(139, 92, 246, 0.15), rgba(99, 102, 241, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 12px 20px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.2), inset 0 -1px 1px rgba(0,0,0,0.3);
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1), animateGradient 12s ease infinite;
            position: relative;
            overflow: hidden;
            margin: 0;
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
        

        .header-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ec4899, #8b5cf6);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(236, 72, 153, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .brand-text h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 10px rgba(255,255,255,0.1);
        }
        
        .brand-text p {
            margin: 0;
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .header-center {
            text-align: center;
        }

        .header-center h2 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 900;
            background: linear-gradient(to right, #fff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .clock-widget {
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--border);
            padding: 8px 20px;
            border-radius: 14px;
            text-align: center;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
        }

        .clock-widget #header-clock {
            color: #f472b6;
            font-size: 1.25rem;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 1.5px;
        }

        .clock-widget #header-date-mini {
            color: #64748b;
            font-size: 0.7rem;
            display: block;
            margin-top: 2px;
            font-weight: 600;
        }

        .icon-btn {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            width: 46px; height: 46px;
            border-radius: 14px;
            color: #fff;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .icon-btn:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        /* Glass Panel Styling for all cards */
        .glass-panel {
            background: var(--surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .glass-panel:hover {
            border-color: var(--border-highlight);
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(0,0,0,0.4), 0 0 30px rgba(236,72,153,0.05);
        }

        /* KPI Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            width: 100%;
        }

        .kpi-card {
            padding: 35px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fadeUpIn 0.5s ease-out both;
        }
        
        .kpi-card:nth-child(1) { animation-delay: 0.1s; }
        .kpi-card:nth-child(2) { animation-delay: 0.2s; }

        .kpi-content {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .kpi-label {
            font-size: 1.1rem;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: 900;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 1px;
            background: linear-gradient(to right, #fff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .kpi-revenue-card .kpi-value {
            background: linear-gradient(to right, #34d399, #10b981);
            -webkit-background-clip: text;
        }

        .kpi-expenses-card .kpi-value {
            background: linear-gradient(to right, #fb7185, #e11d48);
            -webkit-background-clip: text;
        }

        .kpi-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
        }

        .kpi-revenue-card .kpi-icon {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
            color: #34d399;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .kpi-expenses-card .kpi-icon {
            background: rgba(225, 29, 72, 0.15);
            border-color: rgba(225, 29, 72, 0.3);
            color: #fb7185;
            box-shadow: 0 0 20px rgba(225, 29, 72, 0.2);
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            width: 100%;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            padding: 30px;
            display: flex;
            flex-direction: column;
            animation: fadeUpIn 0.6s ease-out both;
            min-height: 450px;
        }

        .chart-card:nth-child(1) { animation-delay: 0.3s; }
        .chart-card:nth-child(2) { animation-delay: 0.4s; }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-title::before {
            content: '';
            display: block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--theme-pink);
            box-shadow: 0 0 10px var(--theme-pink);
        }
        
        .chart-title.blue-dot::before {
            background: var(--theme-blue);
            box-shadow: 0 0 10px var(--theme-blue);
        }

        .chart-actions {
            display: flex;
            gap: 10px;
        }

        .chart-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #cbd5e1;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Tajawal', sans-serif;
            font-weight: 600;
        }

        .chart-btn:hover, .chart-btn.active {
            background: rgba(236, 72, 153, 0.2);
            color: #fff;
            border-color: rgba(236, 72, 153, 0.5);
        }

        .chart-container {
            flex: 1;
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeUpIn {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .kpi-card { flex-direction: column; text-align: center; gap: 20px; }
            .kpi-value { font-size: 2rem; }
        }
        
        /* Compact Bento Grid Layout */
        .quick-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }

        /* Compact Bento Card */
        .quick-report-card {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #fff;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transform-style: preserve-3d;
        }

        .quick-report-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 100%;
            background: radial-gradient(circle at top, var(--glow-color), transparent 70%);
            opacity: 0.15; transition: opacity 0.4s ease;
        }
        
        .quick-report-card::after {
            content: ''; position: absolute; inset: 0;
            border-radius: 16px; padding: 2px;
            background: linear-gradient(to bottom, var(--glow-color), transparent 60%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            opacity: 0.6; transition: opacity 0.4s ease;
        }

        .quick-report-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5), 0 0 20px var(--glow-color);
            background: var(--glow-bg);
            border-color: var(--glow-color);
        }

        .quick-report-card:hover::before { opacity: 0.25; }
        .quick-report-card:hover::after { opacity: 1; }

        .quick-report-card .icon-wrap {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative; z-index: 2;
            flex-shrink: 0;
        }

        .quick-report-card:hover .icon-wrap {
            transform: scale(1.15) rotate(5deg);
            background: var(--glow-color);
            border-color: transparent;
            box-shadow: 0 10px 25px var(--glow-color);
            color: #000;
        }

        .quick-report-card .card-info { position: relative; z-index: 2; display: flex; flex-direction: column; gap: 4px; text-align: right; }
        .quick-report-card .card-info h4 { margin: 0; font-size: 1.05rem; font-weight: 800; color: #f8fafc; }
        .quick-report-card .card-info p { margin: 0; font-size: 0.8rem; color: #94a3b8; line-height: 1.4; font-weight: 500; }

        /* ── Light Mode overrides for Quick Report Cards ── */
        :root.light-theme .quick-report-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.6)) !important;
            border: 1px solid rgba(15, 23, 42, 0.08) !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03), inset 0 1px 0 0 rgba(255, 255, 255, 0.8) !important;
        }
        :root.light-theme .quick-report-card .card-info h4 {
            color: #0f172a !important;
        }
        :root.light-theme .quick-report-card .card-info p {
            color: #475569 !important;
            font-weight: 600 !important;
        }
        :root.light-theme .quick-report-card .icon-wrap {
            background: rgba(15, 23, 42, 0.03) !important;
            border-color: rgba(15, 23, 42, 0.06) !important;
            color: #0f172a !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme .quick-report-card:hover {
            background: var(--glow-bg) !important;
            border-color: var(--glow-color) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06), 0 0 20px rgba(255,255,255,0.5) !important;
        }
        :root.light-theme .quick-report-card:hover .icon-wrap {
            color: #000 !important;
        }

        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }

        /* Daily Report Premium Table & Modal Styles */
        .premium-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
            background: rgba(255,255,255,0.01);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .premium-table th {
            background: rgba(255, 255, 255, 0.03);
            color: #94a3b8;
            padding: 12px 15px;
            font-weight: 700;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-align: right;
        }
        .premium-table td {
            padding: 12px 15px;
            color: #e2e8f0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-size: 0.85rem;
            text-align: right;
        }
        .premium-table tbody tr:last-child td {
            border-bottom: none;
        }
        .premium-table tbody tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
        
        :root.light-theme .premium-table {
            background: #ffffff;
            border-color: rgba(0,0,0,0.08);
        }
        :root.light-theme .premium-table th {
            background: #f1f5f9;
            color: #334155;
            border-bottom-color: rgba(0,0,0,0.12);
        }
        :root.light-theme .premium-table td {
            color: #1e293b;
            border-bottom-color: rgba(0,0,0,0.06);
        }
        :root.light-theme .premium-table tbody tr:hover td {
            background: rgba(0,0,0,0.02);
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

<div class="analytics-container">

    <?php $header_theme_class = 'header-theme-reports'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- Desktop/Mobile Premium Reports Bento Grid (Rearranged under header) -->
    <div class="quick-reports-grid" style="animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1); display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <!-- 1. General Accounts Reports Hub Button -->
        <a href="general_accounts_hub.php" class="quick-report-card" style="--glow-color: rgba(251, 191, 36, 0.65); --glow-bg: rgba(251, 191, 36, 0.12);">
            <div class="icon-wrap">📓</div>
            <div class="card-info">
                <h4>تقرير الحسابات العامة</h4>
                <p>مركز الحسابات العامة: دفتر الأستاذ العام (GL) وحسابات الأرصدة المجمعة.</p>
            </div>
        </a>

        <!-- 2. Modern Balances & Measures Hub Button -->
        <a href="balances_modern_hub.php" class="quick-report-card" style="--glow-color: rgba(56, 189, 248, 0.65); --glow-bg: rgba(56, 189, 248, 0.12);">
            <div class="icon-wrap">⚖️</div>
            <div class="card-info">
                <h4>مركز الأرصدة والمقاييس الموحد</h4>
                <p>متابعة أرصدة المقاييس، حسابات العملاء والموردين، كشوفات الحساب التفاعلية وميزان المراجعة.</p>
            </div>
        </a>

        <!-- 3. Final Statements & Accounting Reports Button -->
        <a href="financial-reports.php" class="quick-report-card" style="--glow-color: rgba(251, 191, 36, 0.65); --glow-bg: rgba(251, 191, 36, 0.12);">
            <div class="icon-wrap">🏛️</div>
            <div class="card-info">
                <h4>القوائم الختامية والمحاسبية</h4>
                <p>ميزان المراجعة، قائمة الأرباح والخسائر، قيود اليومية ودفتر الأستاذ العام.</p>
            </div>
        </a>



        <!-- 5. Smart Reports Hub V2 -->
        <a href="smart_reports_hub.php" class="quick-report-card" style="--glow-color: rgba(6, 182, 212, 0.65); --glow-bg: rgba(6, 182, 212, 0.12);">
            <div class="icon-wrap">🚀</div>
            <div class="card-info">
                <h4>بوابة التقارير الذكية V2</h4>
                <p>مركز التقارير المحاسبية المتقدم للتدفقات النقدية ونقطة التعادل.</p>
            </div>
        </a>

        <!-- 6. Inventory & Warehouses Report Button -->
        <a href="inventory-report.php" class="quick-report-card" style="--glow-color: rgba(16, 185, 129, 0.65); --glow-bg: rgba(16, 185, 129, 0.12);">
            <div class="icon-wrap">📦</div>
            <div class="card-info">
                <h4>تقارير المخازن والمستودعات</h4>
                <p>جرد أرصدة المستودعات وحركة الأصناف.</p>
            </div>
        </a>

        <!-- 7. Customers & Suppliers Report Hub Button -->
        <a href="partners_reports_hub.php" class="quick-report-card" style="--glow-color: rgba(139, 92, 246, 0.65); --glow-bg: rgba(139, 92, 246, 0.12);">
            <div class="icon-wrap">🤝</div>
            <div class="card-info">
                <h4>تقارير العملاء والموردين</h4>
                <p>مركز التقارير الشامل: الموقف المالي، كشف الحساب، الأرصدة والتحليل الصحي.</p>
            </div>
        </a>
    </div>



    
    

    
    <style>
        .reports-directory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            align-items: stretch;
        }
        .report-category-card {
            background: rgba(18, 15, 25, 0.65);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2), inset 0 1px 1px rgba(255,255,255,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .report-category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.1);
        }
        .report-category-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }
        .report-category-icon {
            font-size: 1.8rem;
            width: 45px; height: 45px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            flex-shrink: 0;
        }
        .report-category-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .report-links-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }
        .report-link-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }
        .report-link-item:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
            border-color: rgba(255,255,255,0.1);
            transform: translateX(-4px);
        }
        .report-link-item span.icon {
            font-size: 1.1rem;
            filter: grayscale(0.5);
            transition: filter 0.2s;
        }
        .report-link-item:hover span.icon {
            filter: grayscale(0);
        }
        
        .cat-reports .report-category-icon { background: rgba(56, 189, 248, 0.15); box-shadow: 0 0 15px rgba(56, 189, 248, 0.2); }
        .cat-reports .report-category-title { color: #38bdf8; }
        .cat-reports .report-link-item:hover { border-right: 3px solid #38bdf8; }
    
        
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
            border: 3.5px solid #000000 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
            color: #000000 !important;
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
            color: #000000 !important;
            border: 2px solid #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }
        :root.light-theme table.pro-table td,
        :root.light-theme .pos-grid tbody td,
        :root.light-theme .ledger-table td,
        :root.light-theme .report-table td {
            color: #000000 !important;
            border-bottom: 2px solid #000000 !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td,
        :root.light-theme .pos-grid tbody tr:hover td,
        :root.light-theme .ledger-table tbody tr:hover td,
        :root.light-theme .report-table tbody tr:hover td {
            background: rgba(0,0,0,0.03) !important;
        }
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .tot-lbl,
        :root.light-theme .lbl,
        :root.light-theme label,
        :root.light-theme .table-header {
            color: #000000 !important;
            font-weight: bold !important;
        }
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #000000 !important;
            font-weight: bold !important;
        }
        :root.light-theme .h-form-group,
        :root.light-theme .control-panel {
            background: transparent !important;
            border: 2px solid #000000 !important;
        }
        :root.light-theme .modern-btn {
            color: #000000 !important;
            border: 1.5px solid #000000 !important;
        }
        :root.light-theme .modern-btn:hover {
            color: #000000 !important;
            background: rgba(0, 0, 0, 0.05) !important;
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
</style>



    <!-- Charts Section -->
    <div class="charts-grid">
        
        <!-- Sales vs Purchases Chart -->
        <div class="chart-card glass-panel">
            <div class="chart-header">
                <div class="chart-title">حركة المبيعات والمشتريات</div>
                <div class="chart-actions">
                    <button class="chart-btn active">7 أيام</button>
                    <button class="chart-btn">شهر</button>
                    <button class="chart-btn">سنة</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Top Selling Products Chart -->
        <div class="chart-card glass-panel">
            <div class="chart-header">
                <div class="chart-title blue-dot">الأصناف الأكثر مبيعاً</div>
            </div>
            <div class="chart-container">
                <canvas id="productsChart"></canvas>
            </div>
        </div>
        
    </div>

</div>

<script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // We might want to wait for DBReady, but let's initialize directly if data is in localStorage
        setTimeout(initAIAnalytics, 500);
    });

    // Reload page once background cloud sync completes (essential for iOS Safari onboarding)
    window.addEventListener('cloudDataRestored', () => {
        console.log("[Analytics] Cloud sync completed. Reloading...");
        window.location.reload();
    });

    function initAIAnalytics() {
        // 1. Fetch Data
        const sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
        const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn);
        const items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
        const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
        const customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];

        // Utility: Date formatter
        const formatDate = (d) => {
            let m = '' + (d.getMonth() + 1), day = '' + d.getDate(), y = d.getFullYear();
            if (m.length < 2) m = '0' + m;
            if (day.length < 2) day = '0' + day;
            return [y, m, day].join('-');
        };

        // 2. Aggregate Sales vs Purchases (Last 7 Days)
        let dates = [];
        let salesData = [];
        let purchasesData = [];
        for (let i = 6; i >= 0; i--) {
            let d = new Date();
            d.setDate(d.getDate() - i);
            let dStr = formatDate(d);
            dates.push(d.toLocaleDateString('ar-EG', { weekday: 'short' }));
            
            // Sum sales for this day
            let daySales = sales.filter(s => s.date === dStr && s.status !== 'cancelled').reduce((sum, s) => sum + (parseFloat(s.grandTotal) || 0), 0);
            salesData.push(daySales);

            // Sum purchases for this day
            let dayPurchases = purchases.filter(p => p.date === dStr && p.status !== 'cancelled').reduce((sum, p) => sum + (parseFloat(p.grandTotal) || 0), 0);
            purchasesData.push(dayPurchases);
        }

        // 3. Top Products Aggregation
        let productSales = {};
        sales.forEach(sale => {
            if (sale.status === 'cancelled') return;
            (sale.items || []).forEach(item => {
                if (!productSales[item.name]) productSales[item.name] = 0;
                productSales[item.name] += (parseFloat(item.qty) || 0);
            });
        });
        
        let sortedProducts = Object.keys(productSales).map(k => ({ name: k, qty: productSales[k] })).sort((a, b) => b.qty - a.qty).slice(0, 4);
        let topProductLabels = sortedProducts.map(p => p.name);
        let topProductData = sortedProducts.map(p => p.qty);

        if(topProductLabels.length === 0) {
            topProductLabels = ['لا توجد مبيعات'];
            topProductData = [1];
        }

        // 4. Update Charts
        updateCharts(dates, salesData, purchasesData, topProductLabels, topProductData);
    }

    function updateCharts(dates, salesData, purchasesData, topProductLabels, topProductData) {
        if (typeof Chart === 'undefined') return;
        const isLightTheme = document.documentElement.classList.contains('light-theme');
        const gridColor = isLightTheme ? 'rgba(0, 0, 0, 0.15)' : 'rgba(255, 255, 255, 0.05)';
        const tickColor = isLightTheme ? '#000000' : '#ffffff';
        const fontSettings = { family: 'Tajawal', weight: 'bold' };

        const ctxFlow = document.getElementById('salesChart').getContext('2d');
        let gradientSales = ctxFlow.createLinearGradient(0, 0, 0, 400);
        gradientSales.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
        gradientSales.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
        
        let gradientPurchases = ctxFlow.createLinearGradient(0, 0, 0, 400);
        gradientPurchases.addColorStop(0, 'rgba(236, 72, 153, 0.4)');
        gradientPurchases.addColorStop(1, 'rgba(236, 72, 153, 0.0)');

        new Chart(ctxFlow, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'المبيعات (SDG)',
                        data: salesData,
                        borderColor: '#10b981',
                        backgroundColor: gradientSales,
                        borderWidth: 3,
                        pointBackgroundColor: '#050308',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 3,
                        pointRadius: 5,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'المشتريات (SDG)',
                        data: purchasesData,
                        borderColor: '#ec4899',
                        backgroundColor: gradientPurchases,
                        borderWidth: 3,
                        borderDash: [6, 4],
                        pointBackgroundColor: '#050308',
                        pointBorderColor: '#ec4899',
                        pointBorderWidth: 3,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { 
                        grid: { color: gridColor, drawBorder: false }, 
                        ticks: { color: tickColor, font: fontSettings, padding: 10 } 
                    },
                    y: { 
                        grid: { color: gridColor, drawBorder: false }, 
                        beginAtZero: true,
                        ticks: { color: tickColor, font: fontSettings, padding: 15, callback: value => (value >= 1000 ? (value / 1000) + 'k' : value) }
                    }
                },
                plugins: {
                    legend: { 
                        position: 'top', 
                        align: 'end', 
                        labels: { color: tickColor, font: fontSettings, usePointStyle: true, padding: 20 } 
                    },
                    tooltip: { backgroundColor: 'rgba(10, 8, 15, 0.9)', padding: 15, cornerRadius: 12 }
                }
            }
        });

        const ctxProducts = document.getElementById('productsChart').getContext('2d');
        new Chart(ctxProducts, {
            type: 'doughnut',
            data: {
                labels: topProductLabels,
                datasets: [{
                    data: topProductData,
                    backgroundColor: ['#8b5cf6', '#ec4899', '#3b82f6', '#10b981'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '75%', layout: { padding: 20 },
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { color: tickColor, font: fontSettings, usePointStyle: true, padding: 20 } 
                    },
                    tooltip: { backgroundColor: 'rgba(10, 8, 15, 0.9)', padding: 15, cornerRadius: 12 }
                }
            }
        });
    }
</script>

<!-- مكتبة السحب والإفلات -->
<script defer src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // High Performance 3D Parallax effect on Quick Report Cards
        document.querySelectorAll('.quick-report-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left; 
                const y = e.clientY - rect.top;  
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = ((y - centerY) / centerY) * -4;
                const rotateY = ((x - centerX) / centerX) * 4;
                
                card.style.transform = `translateY(-5px) scale(1.02) perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            });
            
            card.style.transition = 'transform 0.1s ease-out';
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = `translateY(0) scale(1) perspective(1000px) rotateX(0deg) rotateY(0deg)`;
            });
        });

        if (typeof Sortable !== 'undefined') {
            // تفعيل السحب والإفلات للأقسام الرئيسية (البطاقات الكبيرة)
            const grid = document.querySelector('.reports-directory-grid');
            if (grid) {
                new Sortable(grid, {
                    animation: 300,
                    ghostClass: 'sortable-ghost',
                    handle: '.report-category-header',
                    easing: "cubic-bezier(0.2, 0.8, 0.2, 1)"
                });
            }
            
            // تفعيل السحب والإفلات للروابط داخل كل قسم
            const lists = document.querySelectorAll('.report-links-list');
            lists.forEach(function(list) {
                new Sortable(list, {
                    animation: 250,
                    ghostClass: 'sortable-ghost',
                    easing: "cubic-bezier(0.2, 0.8, 0.2, 1)"
                });
            });
        }
    });

    // --- Daily Report Logic ---
    const resolveWhName = (id) => {
        try {
            const whs = JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]');
            const wh = whs.find(w => w.id === id);
            return wh ? wh.name : (id === 'w-main' ? 'المخزن الرئيسي' : id);
        } catch(e) {
            return id;
        }
    };

    const resolveClientName = (id, customersList) => {
        const cust = customersList.find(c => c.id === id);
        return cust ? cust.name : id;
    };

    const resolveItemName = (id, itemsList) => {
        const item = itemsList.find(i => i.id === id);
        return item ? item.name : id;
    };

    window.showDailyReportModal = function() {
        const modal = document.getElementById('dailyReport-modal');
        modal.style.display = 'flex';
        
        // Set default date to today
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayStr = `${year}-${month}-${day}`;
        
        const dateInput = document.getElementById('daily-report-date');
        if (dateInput) {
            dateInput.value = todayStr;
        }
        
        loadDailyReportData(todayStr);
    };

    window.loadDailyReportData = function(dateStr) {
        const container = document.getElementById('dailyReport-content');
        container.innerHTML = 'جاري تحميل تقرير اليومية... ⏳';
        setTimeout(() => {
            try {
                container.innerHTML = generateDailyReport(dateStr);
            } catch(e) {
                console.error(e);
                container.innerHTML = `<span style="color:#f43f5e">حدث خطأ أثناء إنشاء التقرير: ${e.message}</span>`;
            }
        }, 200);
    };

    function generateDailyReport(dateStr) {
        const sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
        const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn);
        const items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
        const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
        const multiPayments = JSON.parse(localStorage.getItem('erp_payments_v1')) || [];
        const multiReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
        const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
        const customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];

        // Filter by Date
        const todaySales = sales.filter(x => x.date === dateStr);
        const todayPurchases = purchases.filter(x => x.date === dateStr);
        const todayPayments = payments.filter(x => x.date === dateStr && !x.deleted);
        const todayReceipts = receipts.filter(x => x.date === dateStr && !x.deleted);
        const todayMultiPayments = multiPayments.filter(x => x.date === dateStr && !x.deleted);
        const todayMultiReceipts = multiReceipts.filter(x => x.date === dateStr && !x.deleted);
        const todayJvs = jvs.filter(x => x.date === dateStr && !x.deleted);

        // Sum KPIs
        const totalSalesAmt = todaySales.reduce((sum, x) => sum + (parseFloat(x.grandTotal) || 0), 0);
        const totalPurchasesAmt = todayPurchases.reduce((sum, x) => sum + (parseFloat(x.grandTotal) || 0), 0);
        const totalReceiptsAmt = todayReceipts.reduce((sum, x) => sum + (parseFloat(x.amount) || 0), 0) + todayMultiReceipts.reduce((sum, x) => sum + (parseFloat(x.totalAmount) || 0), 0);
        const totalPaymentsAmt = todayPayments.reduce((sum, x) => sum + (parseFloat(x.amount) || 0), 0) + todayMultiPayments.reduce((sum, x) => sum + (parseFloat(x.totalAmount) || 0), 0);
        const netFlow = totalReceiptsAmt - totalPaymentsAmt;

        // Build Financial Table HTML
        let finRows = '';
        todayReceipts.forEach(x => {
            finRows += `
                <tr>
                    <td><span style="background:rgba(16,185,129,0.15); color:#10b981; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">سند قبض مفرد</span></td>
                    <td>#${x.id || '-'}</td>
                    <td>${x.description || x.notes || 'قبض نقدية'}</td>
                    <td>${resolveClientName(x.customer || x.clientId, customers)}</td>
                    <td><strong style="color:#10b981;">+${(parseFloat(x.amount) || 0).toLocaleString()} SDG</strong></td>
                </tr>`;
        });
        todayMultiReceipts.forEach(x => {
            finRows += `
                <tr>
                    <td><span style="background:rgba(16,185,129,0.15); color:#10b981; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">سند قبض مجمع</span></td>
                    <td>#${x.id || '-'}</td>
                    <td>${x.notes || 'قبض مجمع'}</td>
                    <td>-</td>
                    <td><strong style="color:#10b981;">+${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</strong></td>
                </tr>`;
        });
        todayPayments.forEach(x => {
            finRows += `
                <tr>
                    <td><span style="background:rgba(244,63,94,0.15); color:#f43f5e; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">سند صرف مفرد</span></td>
                    <td>#${x.id || '-'}</td>
                    <td>${x.description || x.notes || 'صرف نقدية'}</td>
                    <td>${resolveClientName(x.customer || x.clientId, customers)}</td>
                    <td><strong style="color:#f43f5e;">-${(parseFloat(x.amount) || 0).toLocaleString()} SDG</strong></td>
                </tr>`;
        });
        todayMultiPayments.forEach(x => {
            finRows += `
                <tr>
                    <td><span style="background:rgba(244,63,94,0.15); color:#f43f5e; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">سند صرف مجمع</span></td>
                    <td>#${x.id || '-'}</td>
                    <td>${x.notes || 'صرف مجمع'}</td>
                    <td>-</td>
                    <td><strong style="color:#f43f5e;">-${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</strong></td>
                </tr>`;
        });
        todayJvs.forEach(x => {
            finRows += `
                <tr>
                    <td><span style="background:rgba(168,85,247,0.15); color:#a855f7; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">قيد محاسبي</span></td>
                    <td>#${x.id || '-'}</td>
                    <td>${x.statement || 'قيد يومية'}</td>
                    <td>-</td>
                    <td><strong>${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</strong></td>
                </tr>`;
        });

        if(!finRows) {
            finRows = `<tr><td colspan="5" style="text-align:center; padding:15px; color:#94a3b8;">لا توجد حركات مالية مسجلة لهذا اليوم.</td></tr>`;
        }

        // Build Invoices Table HTML
        let invRows = '';
        todaySales.forEach(x => {
            let itemsList = (x.items || []).map(item => `${item.name} (${item.quantity || item.qty} وحدات)`).join('، ');
            invRows += `
                <tr>
                    <td><span style="background:rgba(52,211,153,0.15); color:#34d399; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">فاتورة مبيعات</span></td>
                    <td>#${x.invoiceNo || '-'}</td>
                    <td>${resolveClientName(x.customer || x.customerId, customers)}</td>
                    <td>${x.paymentMethod === 'credit' ? 'آجل 💳' : 'نقداً 💵'}</td>
                    <td><span style="font-size:0.8rem; color:#cbd5e1;">${itemsList || '-'}</span></td>
                    <td><strong style="color:#34d399;">${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</strong></td>
                </tr>`;
        });
        todayPurchases.forEach(x => {
            let itemsList = (x.items || x.lines || []).map(item => `${resolveItemName(item.itemId || item.productId, items)} (${item.quantity || item.qty} وحدات)`).join('، ');
            invRows += `
                <tr>
                    <td><span style="background:rgba(245,158,11,0.15); color:#f59e0b; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">فاتورة مشتريات</span></td>
                    <td>#${x.invoiceNo || '-'}</td>
                    <td>${resolveClientName(x.customer || x.customerId, customers)}</td>
                    <td>${x.paymentMethod === 'credit' ? 'آجل 💳' : 'نقداً 💵'}</td>
                    <td><span style="font-size:0.8rem; color:#cbd5e1;">${itemsList || '-'}</span></td>
                    <td><strong style="color:#f59e0b;">${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</strong></td>
                </tr>`;
        });

        if(!invRows) {
            invRows = `<tr><td colspan="6" style="text-align:center; padding:15px; color:#94a3b8;">لا توجد فواتير مبيعات أو مشتريات مسجلة لهذا اليوم.</td></tr>`;
        }

        // Build Inventory Movements Table HTML
        let stockRows = '';
        todaySales.forEach(inv => {
            (inv.items || []).forEach(line => {
                stockRows += `
                    <tr>
                        <td>${line.name}</td>
                        <td><span style="background:rgba(244,63,94,0.15); color:#f43f5e; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">صرف (بيع)</span></td>
                        <td><strong style="color:#f87171;">-${line.quantity || line.qty}</strong></td>
                        <td>${resolveWhName(inv.deliveryWarehouse || line.warehouseId || inv.warehouse)}</td>
                        <td>فاتورة مبيعات #${inv.invoiceNo}</td>
                    </tr>`;
            });
        });
        todayPurchases.forEach(inv => {
            (inv.items || inv.lines || []).forEach(line => {
                stockRows += `
                    <tr>
                        <td>${resolveItemName(line.itemId || line.productId, items)}</td>
                        <td><span style="background:rgba(16,185,129,0.15); color:#10b981; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700;">إدخل (شراء)</span></td>
                        <td><strong style="color:#34d399;">+${line.quantity || line.qty}</strong></td>
                        <td>${resolveWhName(line.warehouseId || inv.warehouseId || inv.warehouse)}</td>
                        <td>فاتورة مشتريات #${inv.invoiceNo}</td>
                    </tr>`;
            });
        });

        if(!stockRows) {
            stockRows = `<tr><td colspan="5" style="text-align:center; padding:15px; color:#94a3b8;">لا توجد حركة للأصناف في المستودعات لهذا اليوم.</td></tr>`;
        }

        // Modal Content template
        return `
            <!-- Summary Dashboard Inside Modal -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:15px; margin-bottom:25px;">
                <div style="background:rgba(56, 189, 248, 0.08); border:1px solid rgba(56, 189, 248, 0.2); padding:15px; border-radius:12px; text-align:center;">
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:600;">المبيعات اليومية</div>
                    <div style="font-size:1.15rem; font-weight:800; color:#38bdf8; margin-top:5px;">${totalSalesAmt.toLocaleString()} SDG</div>
                </div>
                <div style="background:rgba(245, 158, 11, 0.08); border:1px solid rgba(245, 158, 11, 0.2); padding:15px; border-radius:12px; text-align:center;">
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:600;">المشتريات اليومية</div>
                    <div style="font-size:1.15rem; font-weight:800; color:#f59e0b; margin-top:5px;">${totalPurchasesAmt.toLocaleString()} SDG</div>
                </div>
                <div style="background:rgba(16, 185, 129, 0.08); border:1px solid rgba(16, 185, 129, 0.2); padding:15px; border-radius:12px; text-align:center;">
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:600;">إجمالي المقبوضات</div>
                    <div style="font-size:1.15rem; font-weight:800; color:#10b981; margin-top:5px;">${totalReceiptsAmt.toLocaleString()} SDG</div>
                </div>
                <div style="background:rgba(244, 63, 94, 0.08); border:1px solid rgba(244, 63, 94, 0.2); padding:15px; border-radius:12px; text-align:center;">
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:600;">إجمالي المدفوعات</div>
                    <div style="font-size:1.15rem; font-weight:800; color:#f43f5e; margin-top:5px;">${totalPaymentsAmt.toLocaleString()} SDG</div>
                </div>
                <div style="background:rgba(168, 85, 247, 0.08); border:1px solid rgba(168, 85, 247, 0.2); padding:15px; border-radius:12px; text-align:center; grid-column:span 1;">
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:600;">صافي التدفق المالي</div>
                    <div style="font-size:1.15rem; font-weight:800; color:${netFlow >= 0 ? '#10b981' : '#f43f5e'}; margin-top:5px;">${netFlow.toLocaleString()} SDG</div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:25px;">
                <!-- Section 1: Financial Movements -->
                <div>
                    <h4 style="margin:0 0 10px; color:#fff; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:5px; font-weight:800; font-size:1rem; display:flex; align-items:center; gap:8px;">
                        <span>💵</span> السندات والقيود المالية
                    </h4>
                    <div style="overflow-x:auto;">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>نوع المستند</th>
                                    <th>الرقم المرجعي</th>
                                    <th>البيان / الوصف</th>
                                    <th>العميل/المورد</th>
                                    <th>المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${finRows}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 2: Invoices -->
                <div>
                    <h4 style="margin:0 0 10px; color:#fff; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:5px; font-weight:800; font-size:1rem; display:flex; align-items:center; gap:8px;">
                        <span>🧾</span> الفواتير التجارية (بيع وشراء)
                    </h4>
                    <div style="overflow-x:auto;">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>نوع الفاتورة</th>
                                    <th>رقم الفاتورة</th>
                                    <th>الجهة</th>
                                    <th>طريقة الدفع</th>
                                    <th>الأصناف المباعة/المشتراة</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${invRows}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 3: Warehouse / Item Movements -->
                <div>
                    <h4 style="margin:0 0 10px; color:#fff; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:5px; font-weight:800; font-size:1rem; display:flex; align-items:center; gap:8px;">
                        <span>📦</span> حركة الأصناف في المستودعات
                    </h4>
                    <div style="overflow-x:auto;">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>اسم الصنف</th>
                                    <th>نوع الحركة</th>
                                    <th>الكمية</th>
                                    <th>المستودع</th>
                                    <th>المستند المرجعي</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${stockRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }
</script>

<!-- Daily Report Modal -->
<div id="dailyReport-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(5,3,8,0.85); backdrop-filter: blur(10px); z-index: 10000; align-items: center; justify-content: center; direction: rtl;">
    <div class="glass-panel" style="width: 95%; max-width: 900px; max-height: 90vh; overflow-y: auto; padding: 30px; position: relative; border: 1px solid rgba(255,255,255,0.12); border-radius: 24px; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
        <button onclick="document.getElementById('dailyReport-modal').style.display='none'" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #f87171; font-size: 1.8rem; cursor: pointer; transition: 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">&times;</button>
        <h3 style="color: #a855f7; margin-bottom: 20px; font-weight: 800; border-bottom: 1px dashed rgba(255,255,255,0.15); padding-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
            <span style="display:flex; align-items:center; gap:10px;">
                <span style="font-size: 1.5rem;">📅</span> تقرير اليومية الشامل
            </span>
            <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; font-weight:normal; color:#cbd5e1;">
                <span>تاريخ التقرير:</span>
                <input type="date" id="daily-report-date" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:8px; padding:6px 12px; font-family:inherit; outline:none;" onchange="loadDailyReportData(this.value)">
            </div>
        </h3>
        <div id="dailyReport-content" style="color: #e2e8f0; line-height: 1.8; font-size: 0.95rem;">
            <!-- Content populated dynamically -->
        </div>
    </div>
</div>

</body>
</html>

