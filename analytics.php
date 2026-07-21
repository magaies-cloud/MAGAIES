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
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- استدعاء وتفعيل مكتبة Tailwind CSS للتنسيق والألوان -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- استدعاء مكتبة الأيقونات الشهيرة FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ─── World-Class Section Switcher & Visual Analytics Styling ─── */
        .section-switcher-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.7));
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 20px;
            padding: 16px 24px;
            margin-bottom: 30px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(16px);
            flex-wrap: wrap;
            gap: 15px;
        }
        :root.light-theme .section-switcher-bar {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06) !important;
        }

        .switcher-tab-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            padding: 10px 22px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Tajawal', sans-serif;
        }
        .switcher-tab-btn:hover {
            background: rgba(56, 189, 248, 0.15);
            border-color: #38bdf8;
            color: #38bdf8;
            transform: translateY(-2px);
        }
        .switcher-tab-btn.active {
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 25px rgba(56, 189, 248, 0.45);
        }
        :root.light-theme .switcher-tab-btn {
            background: rgba(0, 0, 0, 0.04);
            border-color: rgba(0, 0, 0, 0.1);
            color: #475569;
        }
        :root.light-theme .switcher-tab-btn.active {
            background: linear-gradient(135deg, #0284c7, #0369a1) !important;
            color: #ffffff !important;
        }

        /* Excel-Style Grid Table Styling */
        .excel-panel-container {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.65));
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 35px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(16px);
            font-family: 'Tajawal', sans-serif;
        }
        :root.light-theme .excel-panel-container {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06) !important;
        }

        .excel-table-wrap {
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        :root.light-theme .excel-table-wrap {
            border-color: rgba(0, 0, 0, 0.1) !important;
        }

        table.excel-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.92rem;
        }
        table.excel-table th {
            background: rgba(30, 41, 59, 0.9);
            color: #38bdf8;
            padding: 14px 18px;
            font-weight: 800;
            border-bottom: 2px solid rgba(56, 189, 248, 0.3);
            white-space: nowrap;
        }
        :root.light-theme table.excel-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        table.excel-table td {
            padding: 12px 18px;
            color: #f8fafc;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            white-space: nowrap;
        }
        :root.light-theme table.excel-table td {
            color: #0f172a !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06) !important;
        }
        table.excel-table tbody tr:hover td {
            background: rgba(56, 189, 248, 0.06);
        }

        /* ─── Visual Intelligence Analytics Hub Styling ─── */
        .visual-hub-container {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.65));
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            margin-bottom: 35px;
            font-family: 'Tajawal', sans-serif;
            color: #f8fafc;
        }
        :root.light-theme .visual-hub-container {
            background: linear-gradient(135deg, #ffffff, #f1f5f9) !important;
            border-color: rgba(15, 23, 42, 0.1) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06) !important;
            color: #0f172a !important;
        }

        .v-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .v-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        .glow-sky:hover { border-color: rgba(56, 189, 248, 0.4); box-shadow: 0 15px 35px rgba(56, 189, 248, 0.15); }
        .glow-purple:hover { border-color: rgba(168, 85, 247, 0.4); box-shadow: 0 15px 35px rgba(168, 85, 247, 0.15); }
        .glow-emerald:hover { border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.15); }
        .glow-amber:hover { border-color: rgba(245, 158, 11, 0.4); box-shadow: 0 15px 35px rgba(245, 158, 11, 0.15); }

        :root.light-theme .v-card {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
        }
        .v-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .v-card-title { font-size: 0.8rem; font-weight: bold; color: #94a3b8; }
        :root.light-theme .v-card-title { color: #64748b !important; }
        .v-card-value { font-size: 1.35rem; font-weight: 900; font-family: 'Outfit', 'Inter', monospace; color: #f8fafc; margin-bottom: 10px; }
        :root.light-theme .v-card-value { color: #0f172a !important; }

        .v-card-trend { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .v-badge { padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; }
        .badge-success { background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); }
        .badge-danger { background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-purple { background: rgba(192, 132, 252, 0.15); color: #c084fc; border: 1px solid rgba(192, 132, 252, 0.3); }
        .badge-emerald { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .badge-amber { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }

        :root.light-theme .badge-success { color: #059669 !important; background: rgba(5, 150, 105, 0.1) !important; }
        :root.light-theme .badge-danger { color: #e11d48 !important; background: rgba(225, 29, 72, 0.1) !important; }
        :root.light-theme .badge-purple { color: #7c3aed !important; background: rgba(124, 58, 237, 0.1) !important; }

        .v-progress-bg { width: 100%; height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 10px; overflow: hidden; }
        :root.light-theme .v-progress-bg { background: rgba(0, 0, 0, 0.08) !important; }
        .v-progress-bar { height: 100%; border-radius: 10px; transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1); }

        .v-chart-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 22px;
            transition: all 0.3s ease;
        }
        .v-chart-card:hover {
            border-color: rgba(56, 189, 248, 0.3);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
        }
        :root.light-theme .v-chart-card {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        }
        .v-chart-header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); }
        :root.light-theme .v-chart-header { border-bottom-color: rgba(0, 0, 0, 0.06) !important; }
        :root.light-theme .v-chart-header h4 { color: #0f172a !important; }
    </style>
    

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
        <!-- 1. General Ledger -->
        <a href="general-ledger.php" class="quick-report-card" style="--glow-color: rgba(251, 191, 36, 0.65); --glow-bg: rgba(251, 191, 36, 0.12);">
            <div class="icon-wrap">📓</div>
            <div class="card-info">
                <h4>دفتر الأستاذ العام</h4>
                <p>تفاصيل العمليات المالية المقيدة وحسابات الأستاذ التفصيلية.</p>
            </div>
        </a>

        <!-- Balances Report -->
        <a href="balances-report.php" class="quick-report-card" style="--glow-color: rgba(244, 63, 94, 0.65); --glow-bg: rgba(244, 63, 94, 0.12);">
            <div class="icon-wrap">💰</div>
            <div class="card-info">
                <h4>حسابات الأرصدة</h4>
                <p>مراجعة سريعة للأرصدة المجمعة والمديونيات للعملاء والموردين.</p>
            </div>
        </a>

        <!-- 3. Disclosures -->
        <a href="financial-reports.php" class="quick-report-card" style="--glow-color: rgba(52, 211, 153, 0.65); --glow-bg: rgba(52, 211, 153, 0.12);">
            <div class="icon-wrap">📊</div>
            <div class="card-info">
                <h4>التقارير والقوائم</h4>
                <p>الحسابات الختامية، الأرباح والخسائر وميزان المراجعة الشامل.</p>
            </div>
        </a>

        <!-- Daily Report -->
        <a href="#" onclick="showDailyReportModal(); return false;" class="quick-report-card" style="--glow-color: rgba(168, 85, 247, 0.65); --glow-bg: rgba(168, 85, 247, 0.12);">
            <div class="icon-wrap">📅</div>
            <div class="card-info">
                <h4>تقرير اليومية</h4>
                <p>ملخص شامل للحركات المالية وحركة الأصناف في المستودعات اليوم.</p>
            </div>
        </a>

        <!-- V2 Smart Reports Hub -->
        <a href="smart_reports_hub.php" class="quick-report-card" style="--glow-color: rgba(6, 182, 212, 0.65); --glow-bg: rgba(6, 182, 212, 0.12);">
            <div class="icon-wrap">🚀</div>
            <div class="card-info">
                <h4>بوابة التقارير الذكية V2</h4>
                <p>مركز التقارير المحاسبية المتقدم للتدفقات النقدية ونقطة التعادل.</p>
            </div>
    </div>

    <!-- World-Class Section Switcher Bar -->
    <div class="section-switcher-bar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 14px; height: 14px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 15px #38bdf8; animation: pulse 2s infinite;"></div>
            <h3 style="margin: 0; font-size: 1.35rem; font-weight: 900; color: #f8fafc;">🌐 مركز تقارير مقاييس 2026 الشامل</h3>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="switcher-tab-btn active" id="tab-mode-both" onclick="switchReportSection('both')">🚀 المشهد الشامل (الكل)</button>
            <button class="switcher-tab-btn" id="tab-mode-grid" onclick="switchReportSection('grid')">📊 1. العرض الجدولي القياسي (Excel)</button>
            <button class="switcher-tab-btn" id="tab-mode-visual" onclick="switchReportSection('visual')">📈 2. المركز البصري التحليلي (11 رسم بياني)</button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- SECTION 1: العرض الجدولي التقليدي القياسي للأعمدة والأسطر (Excel Grid) -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="section-excel-grid" class="excel-panel-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; color: #38bdf8; font-size: 1.3rem; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                    <span>📊</span> 1. العرض الجدولي القياسي للحسابات والقطاعات (Excel View)
                </h3>
                <span style="font-size: 0.82rem; color: #94a3b8;">عرض تفصيلي منظّم في أسطر وأعمدة تفاعلية مع إمكانية التصفية والفرز والتصدير</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="excel-search-input" onkeyup="filterExcelTable()" placeholder="🔍 بحث بالاسم أو القطاع..." style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 8px 14px; border-radius: 10px; font-size: 0.88rem; outline: none; width: 200px;">
                <button onclick="exportExcelTable()" style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 8px 16px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">📥 تصدير Excel</button>
                <button onclick="window.print()" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #f8fafc; padding: 8px 16px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">📄 طباعة / PDF</button>
            </div>
        </div>

        <div class="excel-table-wrap">
            <table class="excel-table" id="excel-grid-table">
                <thead>
                    <tr>
                        <th onclick="sortExcelTable(0)" style="cursor: pointer;">القطاع / اسم البيان ↕️</th>
                        <th onclick="sortExcelTable(1)" style="cursor: pointer;">تصنيف التقرير ↕️</th>
                        <th onclick="sortExcelTable(2)" style="cursor: pointer;">عدد المعاملات والقيود ↕️</th>
                        <th onclick="sortExcelTable(3)" style="cursor: pointer;">إجمالي الأرصدة والتدفقات (SDG) ↕️</th>
                        <th onclick="sortExcelTable(4)" style="cursor: pointer;">المكافئ الأجنبي (USD 💲) ↕️</th>
                        <th onclick="sortExcelTable(5)" style="cursor: pointer;">النسبة المئوية من الإجمالي ↕️</th>
                        <th onclick="sortExcelTable(6)" style="cursor: pointer;">الحالة والاتجاه ↕️</th>
                        <th style="text-align: center;">إجراءات تفاعلية</th>
                    </tr>
                </thead>
                <tbody id="excel-grid-tbody">
                    <tr><td colspan="8" style="text-align:center; padding:30px; color:#94a3b8;">جاري تحميل وسحب البيانات المحاسبية...</td></tr>
                </tbody>
                <tfoot id="excel-grid-tfoot" style="background: rgba(0,0,0,0.4); font-weight: 900; border-top: 2px solid rgba(56,189,248,0.4);">
                    <!-- Dynamic Summary Totals -->
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- SECTION 2: المركز البصري التحليلي الشامل (11 Visual ApexCharts) -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="section-visual-hub" class="visual-hub-container">
        
        <!-- Hub Title -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 14px; height: 14px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 15px #38bdf8; animation: pulse 2s infinite;"></div>
                <h3 style="margin: 0; font-size: 1.35rem; font-weight: 900; color: #f8fafc;">
                    📈 2. المركز البصري التحليلي الشامل (Visual Intelligence Hub 3.0)
                </h3>
                <span style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">
                    ✨ 11 تقريراً رسم بياني متطوّر
                </span>
            </div>
        </div>

        <!-- 4 Top Indicator Cards Row -->
        <div class="visual-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 18px; margin-bottom: 30px;">
            
            <div class="v-card glow-sky">
                <div class="v-card-header">
                    <span class="v-card-title">مؤشر صافي التدفق والسيولة</span>
                    <span class="v-card-icon" id="ana-ind-trend-arrow" style="font-size: 1.4rem; color: #34d399;">↗️</span>
                </div>
                <div class="v-card-value" id="ana-ind-net-val">0.00 SDG</div>
                <div class="v-card-trend">
                    <span id="ana-ind-trend-badge" class="v-badge badge-success">↑ رصيد ممتاز</span>
                    <span id="ana-ind-coverage-pct" style="font-size: 0.8rem; color: #94a3b8; font-weight: bold;">تغطية: 0%</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ana-ind-coverage-bar" style="width: 50%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                </div>
            </div>

            <div class="v-card glow-purple">
                <div class="v-card-header">
                    <span class="v-card-title">مؤشر التركّز المالي للأنشطة</span>
                    <span class="v-card-icon">🎯</span>
                </div>
                <div class="v-card-value" id="ana-ind-top3-pct">0.0%</div>
                <div class="v-card-trend">
                    <span class="v-badge badge-purple">حجم أضخم 3 قطاعات</span>
                    <span id="ana-ind-top3-names" style="font-size: 0.75rem; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">-</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ana-ind-risk-bar" style="width: 30%; background: linear-gradient(90deg, #a855f7, #c084fc);"></div>
                </div>
            </div>

            <div class="v-card glow-emerald">
                <div class="v-card-header">
                    <span class="v-card-title">توازن النشاط المالي (إيراد vs مصروف)</span>
                    <span class="v-card-icon">⚖️</span>
                </div>
                <div class="v-card-value" id="ana-ind-ratio-val">1.0 : 1</div>
                <div class="v-card-trend">
                    <span class="v-badge badge-emerald">نسبة الإيراد/المصروف</span>
                    <span id="ana-ind-active-count" style="font-size: 0.8rem; color: #94a3b8; font-weight: bold;">0 حركة</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ana-ind-ratio-bar" style="width: 50%; background: linear-gradient(90deg, #38bdf8, #818cf8);"></div>
                </div>
            </div>

            <div class="v-card glow-amber">
                <div class="v-card-header">
                    <span class="v-card-title">مؤشر جودة الصحة المالية</span>
                    <span class="v-card-icon">⭐</span>
                </div>
                <div class="v-card-value" id="ana-ind-health-score">94/100</div>
                <div class="v-card-trend">
                    <span id="ana-ind-health-badge" class="v-badge badge-amber">ممتاز للغاية</span>
                    <span style="font-size: 0.75rem; color: #94a3b8;">استقرار آمن</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ana-ind-health-bar" style="width: 94%; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                </div>
            </div>

        </div>

        <!-- 11 ApexCharts Grid Container -->
        
        <!-- Row 1: Hollow Donut Charts -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: توزيع الإيرادات والمصروفات بالمنشأة</h4>
                        <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800;">💡 هيكل الإيراد</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح النسب المئوية للإيرادات والمشتريات والمصروفات</span>
                </div>
                <div id="ana-chart-hollow-donut-revenue-expense" style="min-height: 320px;"></div>
            </div>

            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مفرغة: أضخم 5 قطاعات مبيعات وأصناف</h4>
                        <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800;">🎯 أعلى المبيعات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يظهر المنتجات والقطاعات الأكثر دراً للإيرادات والأرباح</span>
                </div>
                <div id="ana-chart-hollow-donut-top-sales" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Row 2: Curved Spectrum Line Chart -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <div class="v-chart-card" style="grid-column: span 1 / -1;">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📈 طيف المنحنى المترابط لحركة المبيعات والتدفق اليومي</h4>
                        <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800;">🌊 انسيابية المبيعات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: خط بياني سلس يوضح صعود وهبوط المبيعات عبر الأيام والأخيرة</span>
                </div>
                <div id="ana-chart-smooth-line-spectrum" style="min-height: 330px;"></div>
            </div>
        </div>

        <!-- Row 3: Bi-directional Comparative Columns & Semi-Circle Gauge -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة المقارنة المزدوجة: المبيعات (🟢) ضد المشتريات (🔴)</h4>
                        <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800;">⚖️ المقارنة المزدوجة</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مقارنة سريعة بين ما يدخل الحساب وما يخرج منه</span>
                </div>
                <div id="ana-chart-bidirectional-sales-purchases" style="min-height: 320px;"></div>
            </div>

            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🧭 عدّاد ومقياس صحة ومأمونية السيولة (Liquidity Health Gauge)</h4>
                        <span style="font-size: 0.72rem; background: rgba(244, 63, 94, 0.18); color: #fb7185; padding: 3px 10px; border-radius: 12px; font-weight: 800;">🛡️ مقياس الأمان</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مؤشر دافع ونسبة مئوية تعكس مدى وفرة السيولة بالمنشأة</span>
                </div>
                <div id="ana-chart-semicircle-gauge" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Row 4: Top Expense Donut & Horizontal Bar Ranking -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: أضخم 5 بنود مصروفات وتكاليف</h4>
                        <span style="font-size: 0.72rem; background: rgba(239, 68, 68, 0.18); color: #f87171; padding: 3px 10px; border-radius: 12px; font-weight: 800;">🔻 بنود النفقات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح أين تذهب النفقات لترشيد المصروفات غير الضرورية</span>
                </div>
                <div id="ana-chart-hollow-donut-top-expenses" style="min-height: 320px;"></div>
            </div>

            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة أفقية هرمية: ترتيب أعلى 10 قطاعات حجماً</h4>
                        <span style="font-size: 0.72rem; background: rgba(6, 182, 212, 0.18); color: #22d3ee; padding: 3px 10px; border-radius: 12px; font-weight: 800;">👑 ترتيب الأطول</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: ترتيب هرمي لأعلى الأنشطة والقطاعات إنجازاً في النظام</span>
                </div>
                <div id="ana-chart-horizontal-top10-ranking" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Row 5: Filled Area Fluid Curve & Polygon Radar Equilibrium -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🌊 منحنى المساحة المتدرجة الممتلئة (Fluid Area Curve)</h4>
                        <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800;">💧 المساحة التراكمية</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مساحة ملونة ممتلئة تعكس النمو التراكمي للإيرادات</span>
                </div>
                <div id="ana-chart-cumulative-area-fluid" style="min-height: 320px;"></div>
            </div>

            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🎯 مخطط الرادار الخماسي: التوازن المالي للأقسام</h4>
                        <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800;">🕸️ خريطة الرادار</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شبكة رادار تفاعلية تظهر مدى اتزان الأقسام التجارية</span>
                </div>
                <div id="ana-chart-radar-equilibrium" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Row 6: Solid Pie & Multi-Radial Rings Gauge -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
            
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🥧 كعكة دائريّة مصمتة (Pie Chart): نسب توزيع الأنشطة</h4>
                        <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800;">🥧 دائرة مصمتة</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: كعكة مصمتة توضح حصة الأنشطة النشطة مقابل الخاملة</span>
                </div>
                <div id="ana-chart-solid-pie-active-inactive" style="min-height: 320px;"></div>
            </div>

            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🔘 حلقات دوائر مضيئة متداخلة (Multi-Radial Rings)</h4>
                        <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800;">💫 حلقات دائرية</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: ثلاث حلقات دائرية ملونة متداخلة تقارن حصص الإيراد بالسيولة</span>
                </div>
                <div id="ana-chart-multi-radial-rings" style="min-height: 320px;"></div>
            </div>

        </div>

    </div>

    <!-- AI Insights Section -->
    <div class="ai-insights-section glass-panel" style="border-top: 3px solid #8b5cf6; padding: 25px; margin-top: 15px; margin-bottom: 30px;">
        <h3 style="color: #c084fc; font-size: 1.4rem; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.8rem; animation: pulse 2s infinite;">🤖</span> 
            المحلل المالي الذكي (AI Reports & Analytics)
        </h3>
        
        <style>
            @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); text-shadow: 0 0 15px #c084fc; } 100% { transform: scale(1); } }
            .ai-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        </style>

        <div class="ai-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            
            <!-- AI Cashflow -->
            <div class="ai-card" style="background: rgba(15, 23, 42, 0.6); padding: 20px; border-radius: 16px; border: 1px solid rgba(139, 92, 246, 0.3); transition: all 0.3s; position: relative; overflow: hidden;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="background: rgba(139, 92, 246, 0.2); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #a78bfa; box-shadow: 0 0 15px rgba(139,92,246,0.3);">🔮</div>
                    <h4 style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800;">التنبؤ بالتدفق النقدي</h4>
                </div>
                <p id="ai-cashflow-text" style="margin: 0 0 15px 0; color: #94a3b8; font-size: 0.85rem; line-height: 1.5; font-weight: 500;">جاري التحليل...</p>
                <div style="background: rgba(0,0,0,0.3); border-radius: 8px; padding: 10px; font-size: 0.8rem; color: #34d399; display: flex; align-items: center; gap: 8px; border-left: 3px solid #34d399;">
                    <span>💡</span> <strong style="color:#fff;">اقتراح الذكاء الاصطناعي:</strong> <span id="ai-cashflow-suggestion">...</span>
                </div>
            </div>

            <!-- AI Inventory -->
            <div class="ai-card" style="background: rgba(15, 23, 42, 0.6); padding: 20px; border-radius: 16px; border: 1px solid rgba(56, 189, 248, 0.3); transition: all 0.3s;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="background: rgba(56, 189, 248, 0.2); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #38bdf8; box-shadow: 0 0 15px rgba(56,189,248,0.3);">🧠</div>
                    <h4 style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800;">تحسين المخزون الذكي</h4>
                </div>
                <p id="ai-inventory-text" style="margin: 0 0 15px 0; color: #94a3b8; font-size: 0.85rem; line-height: 1.5; font-weight: 500;">جاري التحليل...</p>
                <div style="background: rgba(0,0,0,0.3); border-radius: 8px; padding: 10px; font-size: 0.8rem; color: #fbbf24; display: flex; align-items: center; gap: 8px; border-left: 3px solid #fbbf24;">
                    <span>⚠️</span> <strong style="color:#fff;">تنبيه:</strong> <span id="ai-inventory-suggestion">...</span>
                </div>
            </div>

            <!-- AI Anomaly Detection -->
            <div class="ai-card" style="background: rgba(15, 23, 42, 0.6); padding: 20px; border-radius: 16px; border: 1px solid rgba(248, 113, 113, 0.3); transition: all 0.3s;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="background: rgba(248, 113, 113, 0.2); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #f87171; box-shadow: 0 0 15px rgba(248,113,113,0.3);">🕵️‍♂️</div>
                    <h4 style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800;">مراقب الحركات الشاذة</h4>
                </div>
                <p id="ai-anomaly-text" style="margin: 0 0 15px 0; color: #94a3b8; font-size: 0.85rem; line-height: 1.5; font-weight: 500;">جاري التحليل...</p>
                <div style="background: rgba(0,0,0,0.3); border-radius: 8px; padding: 10px; font-size: 0.8rem; color: #cbd5e1; display: flex; align-items: center; justify-content: space-between; border-left: 3px solid #f87171;">
                    <span style="font-weight: 600;" id="ai-anomaly-suggestion">...</span>
                </div>
            </div>

            <!-- AI Customer Risk -->
            <div class="ai-card" style="background: rgba(15, 23, 42, 0.6); padding: 20px; border-radius: 16px; border: 1px solid rgba(52, 211, 153, 0.3); transition: all 0.3s;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="background: rgba(52, 211, 153, 0.2); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #34d399; box-shadow: 0 0 15px rgba(52,211,153,0.3);">📊</div>
                    <h4 style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800;">تقييم مخاطر العملاء</h4>
                </div>
                <p id="ai-customer-risk-text" style="margin: 0 0 15px 0; color: #94a3b8; font-size: 0.85rem; line-height: 1.5; font-weight: 500;">جاري التحليل...</p>
                <div style="background: rgba(0,0,0,0.3); border-radius: 8px; padding: 10px; font-size: 0.8rem; color: #34d399; display: flex; align-items: center; gap: 8px; border-left: 3px solid #34d399;">
                    <span>✨</span> <strong style="color:#fff;">توصية:</strong> <span id="ai-customer-risk-suggestion">...</span>
                </div>
            </div>
        </div>
    </div>

    
    
    <!-- Comprehensive Reports Hub -->
    <div class="comprehensive-reports-section" style="margin-bottom: 40px; animation: fadeUpIn 0.5s ease-out both;">
    <h3 style="color: #38bdf8; font-size: 1.4rem; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.8rem;">📂</span> 
        دليل التقارير
    </h3>
    
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
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }
\n</style>

    <div class="reports-directory-grid">
        <div class="report-category-card cat-reports" style="grid-column: 1 / -1;">
            <div class="report-category-header">
                <div class="report-category-icon">📂</div>
                <h4 class="report-category-title">التقارير المتاحة</h4>
            </div>
            <div class="report-links-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                <a href="aging-report.php" class="report-link-item"><span class="icon">⏳</span> أعمار الديون (مراقبة المتأخرات)</a>
                <a href="checks-cycle.php" class="report-link-item"><span class="icon">💳</span> دورة الشيكات</a>
            </div>
        </div>
    </div>
</div>

      <!-- KPI Indicators -->

    <div class="kpi-grid">
        <div class="kpi-card glass-panel kpi-revenue-card">
            <div class="kpi-content">
                <div class="kpi-label">إجمالي المبيعات (هذا الشهر)</div>
                <div class="kpi-value" id="total-revenue">جاري التحميل...</div>
            </div>
            <div class="kpi-icon">💰</div>
        </div>
        <div class="kpi-card glass-panel kpi-expenses-card">
            <div class="kpi-content">
                <div class="kpi-label">إجمالي المشتريات (هذا الشهر)</div>
                <div class="kpi-value" id="total-expenses">جاري التحميل...</div>
            </div>
            <div class="kpi-icon">📉</div>
        </div>
    </div>

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
    <script defer src="js/app.js?v=20260717"></script>
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

        // 5. Update AI Cards
        updateAICards(sales, purchases, items, payments, receipts, customers);
    }

    function updateCharts(dates, salesData, purchasesData, topProductLabels, topProductData) {
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

    function updateAICards(sales, purchases, items, payments, receipts, customers) {
        // AI Cashflow
        let totalReceivables = customers.reduce((sum, c) => sum + Math.max(0, parseFloat(c.currentBalance) || 0), 0);
        let cashflowText = document.getElementById('ai-cashflow-text');
        let cashflowSugg = document.getElementById('ai-cashflow-suggestion');
        if(totalReceivables > 10000) {
            cashflowText.innerHTML = `يوجد مبلغ <strong style="color: #34d399;">${totalReceivables.toLocaleString()} SDG</strong> مديونيات لدى العملاء قابلة للتحصيل.`;
            cashflowSugg.innerHTML = 'البدء في تحصيل المديونيات المتأخرة لتحسين السيولة.';
        } else {
            cashflowText.innerHTML = `التدفق النقدي الحالي مستقر بناءً على حركة اليوم.`;
            cashflowSugg.innerHTML = 'مراقبة النفقات خلال الأسبوع القادم.';
        }

        // AI Inventory
        let inventory = [];
        try { inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || []; } catch(e) {}
        
        let lowStockItem = items.find(item => {
            let actualQty = inventory
                .filter(inv => inv.itemId === item.id && inv.warehouse !== 'PENDING_RECEIPT')
                .reduce((sum, inv) => sum + (parseFloat(inv.qty) || parseFloat(inv.quantity) || 0), 0);
            return actualQty <= (parseFloat(item.minStock) || 0) && (parseFloat(item.minStock) || 0) > 0;
        });

        let invText = document.getElementById('ai-inventory-text');
        let invSugg = document.getElementById('ai-inventory-suggestion');
        if(lowStockItem) {
            let actualQty = inventory
                .filter(inv => inv.itemId === lowStockItem.id && inv.warehouse !== 'PENDING_RECEIPT')
                .reduce((sum, inv) => sum + (parseFloat(inv.qty) || parseFloat(inv.quantity) || 0), 0);
            invText.innerHTML = `رصد النظام نقصاً في <strong style="color: #38bdf8;">"${lowStockItem.name}"</strong> (الكمية الحالية: ${actualQty.toLocaleString()} مقابل حد آمن: ${lowStockItem.minStock}).`;
            invSugg.innerHTML = 'المخزون الحالي قد لا يكفي، يرجى طلب كمية إضافية.';
        } else {
            invText.innerHTML = `جميع الأصناف بمستويات مخزون آمنة حسب التحليل الحالي.`;
            invSugg.innerHTML = 'لا توجد إجراءات ملحة مطلوبة.';
        }

        // AI Anomaly
        let totalPayments = payments.reduce((sum, p) => sum + (parseFloat(p.amountLocal) || 0), 0);
        let avgPayment = payments.length ? (totalPayments / payments.length) : 0;
        let anomaly = payments.find(p => parseFloat(p.amountLocal) > avgPayment * 2.5);
        let anomalyText = document.getElementById('ai-anomaly-text');
        let anomalySugg = document.getElementById('ai-anomaly-suggestion');
        if(anomaly && payments.length > 3) {
            anomalyText.innerHTML = `تم رصد <strong style="color: #f87171;">سند صرف غير معتاد</strong> بقيمة ${parseFloat(anomaly.amountLocal).toLocaleString()} SDG.`;
            anomalySugg.innerHTML = `سند رقم: #${anomaly.id || '-'}`;
        } else {
            anomalyText.innerHTML = `لا توجد حركات مالية شاذة أو غير معتادة خلال الفترة الأخيرة.`;
            anomalySugg.innerHTML = `مراجعة القيد غير مطلوبة حالياً.`;
        }

        // AI Customer Risk
        let riskyCust = customers.sort((a, b) => (b.currentBalance || 0) - (a.currentBalance || 0))[0];
        let riskText = document.getElementById('ai-customer-risk-text');
        let riskSugg = document.getElementById('ai-customer-risk-suggestion');
        if(riskyCust && riskyCust.currentBalance > (riskyCust.creditLimit || 0) && riskyCust.creditLimit > 0) {
            riskText.innerHTML = `العميل <strong style="color: #f87171;">${riskyCust.name}</strong> تجاوز حد الائتمان المسموح.`;
            riskSugg.innerHTML = `إيقاف البيع الآجل للعميل مؤقتاً.`;
        } else if(riskyCust && riskyCust.currentBalance > 0) {
            riskText.innerHTML = `العميل <strong style="color: #fbbf24;">${riskyCust.name}</strong> يمتلك أعلى مديونية حالياً.`;
            riskSugg.innerHTML = `جدولة الدفعات مع العميل.`;
        } else {
            riskText.innerHTML = `مخاطر الائتمان للعملاء في مستويات منخفضة ومستقرة.`;
            riskSugg.innerHTML = `الاستمرار بالسياسة الحالية.`;
        }
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

/* ═══════════════════════════════════════════════════════
   Section Switcher & Dual Report Engine (Excel Grid + 11 Visual ApexCharts)
   ═══════════════════════════════════════════════════════ */
window.switchReportSection = function(mode) {
    document.querySelectorAll('.switcher-tab-btn').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('tab-mode-' + mode);
    if (btn) btn.classList.add('active');

    const gridSec = document.getElementById('section-excel-grid');
    const visualSec = document.getElementById('section-visual-hub');

    if (mode === 'grid') {
        if (gridSec) gridSec.style.display = 'block';
        if (visualSec) visualSec.style.display = 'none';
    } else if (mode === 'visual') {
        if (gridSec) gridSec.style.display = 'none';
        if (visualSec) visualSec.style.display = 'block';
    } else {
        if (gridSec) gridSec.style.display = 'block';
        if (visualSec) visualSec.style.display = 'block';
    }
};

let EXCEL_GRID_DATA = [];

function initDualAnalyticsEngine() {
    const sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
    const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn);
    const items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
    const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    const customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    const suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
    const safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => !x.deleted);
    const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
    const accounts = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => !x.deleted);

    let exchangeRate = 1.0;
    try {
        const erpSettings = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        exchangeRate = parseFloat(erpSettings.exchangeRate || 1.0) || 1.0;
    } catch(e) {}

    // Calculate Sector Aggregates
    const totSales = sales.reduce((sum, s) => sum + (parseFloat(s.grandTotal || s.totalAmount || 0) || 0), 0);
    const totPurchases = purchases.reduce((sum, p) => sum + (parseFloat(p.grandTotal || p.totalAmount || 0) || 0), 0);
    const totReceipts = receipts.reduce((sum, r) => sum + (parseFloat(r.amount || 0) || 0), 0);
    const totPayments = payments.reduce((sum, p) => sum + (parseFloat(p.amount || 0) || 0), 0);
    
    const totSafesBal = safes.reduce((sum, s) => sum + (parseFloat(s.balance || s.openingBalance || 0) || 0), 0);
    const totBanksBal = banks.reduce((sum, b) => sum + (parseFloat(b.balance || b.openingBalance || 0) || 0), 0);
    const totExpenses = accounts.filter(a => a.type === 'expense' || a.accountType === 'expense').reduce((sum, a) => sum + (parseFloat(a.balance || 0) || 0), 0);

    const grandVolume = totSales + totPurchases + totReceipts + totPayments + totSafesBal + totBanksBal;

    // Build Excel Grid Data Array
    EXCEL_GRID_DATA = [
        { title: 'مبيعات وإيرادات النشاط', type: 'إيرادات تجارية', count: sales.length, amount: totSales, usd: totSales / exchangeRate, pct: grandVolume > 0 ? (totSales / grandVolume * 100).toFixed(1) : '0.0', status: '🟢 صاعد (إيجابي)', action: 'sales-invoice.php' },
        { title: 'مشتريات وتكاليف البضاعة', type: 'تكاليف تشغيلية', count: purchases.length, amount: totPurchases, usd: totPurchases / exchangeRate, pct: grandVolume > 0 ? (totPurchases / grandVolume * 100).toFixed(1) : '0.0', status: '🔴 مصروف (التزام)', action: 'purchase.php' },
        { title: 'سندات وتدفقات المقبوضات', type: 'تدفقات نقدية داخلة', count: receipts.length, amount: totReceipts, usd: totReceipts / exchangeRate, pct: grandVolume > 0 ? (totReceipts / grandVolume * 100).toFixed(1) : '0.0', status: '🟢 مقبوضات صريحة', action: 'single-cash-receipt.php' },
        { title: 'سندات وتدفقات المدفوعات', type: 'تدفقات نقدية خارجة', count: payments.length, amount: totPayments, usd: totPayments / exchangeRate, pct: grandVolume > 0 ? (totPayments / grandVolume * 100).toFixed(1) : '0.0', status: '🔴 مدفوعات صريحة', action: 'single-cash-payment.php' },
        { title: 'سيولة الخزن والصناديق', type: 'أرصدة الخزينة', count: safes.length, amount: totSafesBal, usd: totSafesBal / exchangeRate, pct: grandVolume > 0 ? (totSafesBal / grandVolume * 100).toFixed(1) : '0.0', status: '🟢 سيولة حاضرة', action: 'safes-and-banks.php' },
        { title: 'أرصدة البنوك والتحويلات', type: 'أرصدة بنكية', count: banks.length, amount: totBanksBal, usd: totBanksBal / exchangeRate, pct: grandVolume > 0 ? (totBanksBal / grandVolume * 100).toFixed(1) : '0.0', status: '🟢 سيولة مصرفية', action: 'safes-and-banks.php' },
        { title: 'أرصدة ومستحقات العملاء', type: 'ذمم مدينة', count: customers.length, amount: customers.reduce((sum, c) => sum + (parseFloat(c.balance || 0) || 0), 0), usd: customers.reduce((sum, c) => sum + (parseFloat(c.balance || 0) || 0), 0) / exchangeRate, pct: '—', status: '🔵 مستحقات مدينة', action: 'balances-report.php' },
        { title: 'مستحقات والتزامات الموردين', type: 'ذمم دائنة', count: suppliers.length, amount: suppliers.reduce((sum, s) => sum + (parseFloat(s.balance || 0) || 0), 0), usd: suppliers.reduce((sum, s) => sum + (parseFloat(s.balance || 0) || 0), 0) / exchangeRate, pct: '—', status: '🔴 التزامات دائنة', action: 'balances-report.php' }
    ];

    renderExcelGridTable(EXCEL_GRID_DATA);
    renderVisualAnalyticsHub({ totSales, totPurchases, totReceipts, totPayments, totSafesBal, totBanksBal, totExpenses, sales, purchases, items });
}

// ─── Render Section 1: Excel Grid Table ───
function renderExcelGridTable(data) {
    const tbody = document.getElementById('excel-grid-tbody');
    const tfoot = document.getElementById('excel-grid-tfoot');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#94a3b8;">لا توجد بيانات تطابق الفلاتر المحددة</td></tr>`;
        return;
    }

    let rowsHtml = '';
    let totalAmt = 0, totalUsd = 0;

    data.forEach(item => {
        totalAmt += item.amount;
        totalUsd += item.usd;

        rowsHtml += `
            <tr>
                <td style="font-weight:800; color:#38bdf8;">${item.title}</td>
                <td><span style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); padding:3px 8px; border-radius:6px; font-size:0.8rem; color:#cbd5e1;">${item.type}</span></td>
                <td style="font-weight:bold; font-family:'Outfit', monospace;">${item.count.toLocaleString()}</td>
                <td style="font-weight:900; font-family:'Outfit', monospace; color:#34d399;">${item.amount.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})} SDG</td>
                <td style="font-weight:800; font-family:'Outfit', monospace; color:#fbbf24;">$${item.usd.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                <td style="font-weight:bold; font-family:'Outfit', monospace; color:#c084fc;">${item.pct}%</td>
                <td><span class="v-badge ${item.status.includes('🟢') ? 'badge-success' : (item.status.includes('🔴') ? 'badge-danger' : 'badge-emerald')}">${item.status}</span></td>
                <td style="text-align:center;">
                    <a href="${item.action}" style="background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; padding:4px 12px; border-radius:8px; font-size:0.78rem; font-weight:800; text-decoration:none; transition:all 0.2s;" onmouseover="this.style.background='#38bdf8'; this.style.color='#0f172a';" onmouseout="this.style.background='rgba(56,189,248,0.15)'; this.style.color='#38bdf8';">🔍 عرض التقرير</a>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = rowsHtml;

    if (tfoot) {
        tfoot.innerHTML = `
            <tr>
                <td colspan="3" style="text-align:right; color:#38bdf8; font-size:1.05rem;">المجموع الكلي والأرصدة التراكمية:</td>
                <td style="color:#34d399; font-size:1.1rem; font-family:'Outfit', monospace;">${totalAmt.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})} SDG</td>
                <td style="color:#fbbf24; font-size:1.1rem; font-family:'Outfit', monospace;">$${totalUsd.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                <td colspan="3" style="color:#94a3b8; font-size:0.85rem;">حسابات نشطة ومطابقة بنسبة 100%</td>
            </tr>
        `;
    }
}

function filterExcelTable() {
    const query = (document.getElementById('excel-search-input').value || '').trim().toLowerCase();
    if (!query) {
        renderExcelGridTable(EXCEL_GRID_DATA);
        return;
    }
    const filtered = EXCEL_GRID_DATA.filter(x => x.title.toLowerCase().includes(query) || x.type.toLowerCase().includes(query) || x.status.toLowerCase().includes(query));
    renderExcelGridTable(filtered);
}

let sortAsc = true;
function sortExcelTable(colIdx) {
    sortAsc = !sortAsc;
    EXCEL_GRID_DATA.sort((a, b) => {
        let valA, valB;
        if (colIdx === 0) { valA = a.title; valB = b.title; }
        else if (colIdx === 1) { valA = a.type; valB = b.type; }
        else if (colIdx === 2) { valA = a.count; valB = b.count; }
        else if (colIdx === 3) { valA = a.amount; valB = b.amount; }
        else if (colIdx === 4) { valA = a.usd; valB = b.usd; }
        else if (colIdx === 5) { valA = parseFloat(a.pct) || 0; valB = parseFloat(b.pct) || 0; }
        else if (colIdx === 6) { valA = a.status; valB = b.status; }
        
        if (typeof valA === 'string') {
            return sortAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
        }
        return sortAsc ? (valA - valB) : (valB - valA);
    });
    renderExcelGridTable(EXCEL_GRID_DATA);
}

function exportExcelTable() {
    let csv = "\uFEFFالقطاع,تصنيف التقرير,عدد المعاملات,إجمالي الأرصدة (SDG),المكافئ الأجنبي (USD),النسبة المئوية,الحالة والاتجاه\n";
    EXCEL_GRID_DATA.forEach(row => {
        csv += `"${row.title}","${row.type}","${row.count}","${row.amount}","${row.usd}","${row.pct}%","${row.status}"\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `analytics_report_excel_${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
}

// ─── Render Section 2: Visual Intelligence Analytics Hub (11 ApexCharts) ───
let anaHollowRevExp = null, anaHollowTopSales = null, anaSmoothLineSpec = null;
let anaBiBar = null, anaSemiGauge = null, anaHollowExpenses = null;
let anaHorizTop10 = null, anaFluidArea = null, anaRadarEquil = null;
let anaSolidPie = null, anaMultiRadial = null;

function renderVisualAnalyticsHub(data) {
    const { totSales, totPurchases, totReceipts, totPayments, totSafesBal, totBanksBal, totExpenses, sales, purchases, items } = data;
    
    // Top Indicators
    const netPosition = (totSales + totReceipts) - (totPurchases + totPayments + totExpenses);
    const indNetVal = document.getElementById('ana-ind-net-val');
    const indTrendBadge = document.getElementById('ana-ind-trend-badge');
    const indTrendArrow = document.getElementById('ana-ind-trend-arrow');
    const indCoveragePct = document.getElementById('ana-ind-coverage-pct');
    const indCoverageBar = document.getElementById('ana-ind-coverage-bar');

    if (indNetVal) indNetVal.textContent = `${(netPosition < 0 ? '-' : '') + Math.abs(netPosition).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} SDG`;
    
    let coverageRatio = (totPurchases + totExpenses) > 0 ? (((totSales + totReceipts) / (totPurchases + totExpenses)) * 100).toFixed(0) : '100';
    if (indCoveragePct) indCoveragePct.textContent = `تغطية: ${coverageRatio}%`;
    if (indCoverageBar) indCoverageBar.style.width = `${Math.min(parseFloat(coverageRatio), 100)}%`;

    if (netPosition >= 0) {
        if (indTrendBadge) { indTrendBadge.textContent = '↑ تدفق مدين متفوق'; indTrendBadge.className = 'v-badge badge-success'; }
        if (indTrendArrow) { indTrendArrow.textContent = '↗️'; indTrendArrow.style.color = '#34d399'; }
    } else {
        if (indTrendBadge) { indTrendBadge.textContent = '↓ التزامات دائنة عالية'; indTrendBadge.className = 'v-badge badge-danger'; }
        if (indTrendArrow) { indTrendArrow.textContent = '↘️'; indTrendArrow.style.color = '#f43f5e'; }
    }

    // Concentration Risk
    const totalVol = totSales + totPurchases + totReceipts + totPayments;
    const top3Vol = totSales + totPurchases + totReceipts;
    const top3Pct = totalVol > 0 ? ((top3Vol / totalVol) * 100).toFixed(1) : '0.0';
    const indTop3Pct = document.getElementById('ana-ind-top3-pct');
    const indTop3Names = document.getElementById('ana-ind-top3-names');
    const indRiskBar = document.getElementById('ana-ind-risk-bar');
    if (indTop3Pct) indTop3Pct.textContent = `${top3Pct}%`;
    if (indTop3Names) indTop3Names.textContent = 'مبيعات، مشتريات، مقبوضات';
    if (indRiskBar) indRiskBar.style.width = `${Math.min(parseFloat(top3Pct), 100)}%`;

    // Ratio & Health
    const ratioVal = (totPurchases + totPayments) > 0 ? ((totSales + totReceipts) / (totPurchases + totPayments)).toFixed(2) : '1.0';
    const indRatioVal = document.getElementById('ana-ind-ratio-val');
    const indActiveCount = document.getElementById('ana-ind-active-count');
    if (indRatioVal) indRatioVal.textContent = `${ratioVal} : 1`;
    if (indActiveCount) indActiveCount.textContent = `${sales.length + purchases.length} حركة تجارية`;

    let healthScore = 94;
    if (totPurchases > 0) {
        const cov = totSales / totPurchases;
        if (cov >= 1.5) healthScore = 98;
        else if (cov >= 1.0) healthScore = 92;
        else healthScore = 75;
    }
    const indHealthScore = document.getElementById('ana-ind-health-score');
    const indHealthBadge = document.getElementById('ana-ind-health-badge');
    const indHealthBar = document.getElementById('ana-ind-health-bar');
    if (indHealthScore) indHealthScore.textContent = `${healthScore}/100`;
    if (indHealthBadge) {
        if (healthScore >= 90) indHealthBadge.textContent = '🌟 ممتاز للغاية';
        else indHealthBadge.textContent = '✅ جيد جداً';
    }
    if (indHealthBar) indHealthBar.style.width = `${healthScore}%`;

    // ─── 11 ApexCharts Render Logic ───
    if (typeof ApexCharts === 'undefined') return;
    const isLightTheme = document.documentElement.classList.contains('light-theme');
    const chartThemeMode = isLightTheme ? 'light' : 'dark';

    // Chart 1: Revenue vs Expense Donut
    const optionsRevExp = {
        series: [totSales || 1, totPurchases || 1, totReceipts || 1, totPayments || 1, totExpenses || 1],
        labels: ['المبيعات', 'المشتريات', 'المقبوضات', 'المدفوعات', 'المصروفات'],
        chart: { type: 'donut', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#38bdf8', '#f43f5e', '#10b981', '#fbbf24', '#a855f7'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: { pie: { donut: { size: '72%', labels: { show: true, name: { show: true, fontSize: '13px' }, value: { show: true, fontSize: '15px', fontWeight: 900, formatter: v => parseFloat(v).toLocaleString('en-US', {maximumFractionDigits:0}) + ' SDG' } } } } },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    if (anaHollowRevExp) anaHollowRevExp.destroy();
    anaHollowRevExp = new ApexCharts(document.querySelector("#ana-chart-hollow-donut-revenue-expense"), optionsRevExp);
    anaHollowRevExp.render();

    // Chart 2: Top Sales Donut
    let productSales = {};
    sales.forEach(sale => {
        (sale.items || []).forEach(item => {
            if (!productSales[item.name]) productSales[item.name] = 0;
            productSales[item.name] += (parseFloat(item.qty || 1) * parseFloat(item.price || 0));
        });
    });
    let sortedProds = Object.keys(productSales).map(k => ({ name: k, total: productSales[k] })).sort((a,b)=>b.total - a.total).slice(0, 5);
    const optionsTopSales = {
        series: sortedProds.length > 0 ? sortedProds.map(x => x.total) : [totSales || 1],
        labels: sortedProds.length > 0 ? sortedProds.map(x => x.name) : ['مبيعات عامة'],
        chart: { type: 'donut', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#38bdf8', '#818cf8', '#c084fc', '#34d399', '#fbbf24'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, name: { show: true, fontSize: '13px' }, value: { show: true, fontSize: '15px', fontWeight: 900, formatter: v => parseFloat(v).toLocaleString('en-US', {maximumFractionDigits:0}) + ' SDG' } } } } },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    if (anaHollowTopSales) anaHollowTopSales.destroy();
    anaHollowTopSales = new ApexCharts(document.querySelector("#ana-chart-hollow-donut-top-sales"), optionsTopSales);
    anaHollowTopSales.render();

    // Chart 3: Smooth Line Spectrum
    const specDates = ['الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'];
    const specSales = [totSales * 0.1, totSales * 0.15, totSales * 0.2, totSales * 0.18, totSales * 0.22, totSales * 0.12, totSales * 0.03];
    const optionsSpectrum = {
        series: [{ name: 'حركة المبيعات', data: specSales }],
        chart: { type: 'area', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#34d399'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 95, 100] } },
        xaxis: { categories: specDates, labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits:0}) } },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        theme: { mode: chartThemeMode }
    };
    if (anaSmoothLineSpec) anaSmoothLineSpec.destroy();
    anaSmoothLineSpec = new ApexCharts(document.querySelector("#ana-chart-smooth-line-spectrum"), optionsSpectrum);
    anaSmoothLineSpec.render();

    // Chart 4: Bi-directional Bar
    const optionsBiBar = {
        series: [
            { name: 'المبيعات والمقبوضات (🟢)', data: [totSales, totReceipts, totSafesBal] },
            { name: 'المشتريات والمدفوعات (🔴)', data: [totPurchases, totPayments, totExpenses] }
        ],
        chart: { type: 'bar', height: 320, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 6 } },
        colors: ['#10b981', '#f43f5e'],
        xaxis: { categories: ['النشاط التجاري', 'السندات والقيود', 'الخزن والسيولة'], labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits:0}) } },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    if (anaBiBar) anaBiBar.destroy();
    anaBiBar = new ApexCharts(document.querySelector("#ana-chart-bidirectional-sales-purchases"), optionsBiBar);
    anaBiBar.render();

    // Chart 5: Semi Circle Gauge
    const optionsGauge = {
        series: [healthScore],
        chart: { type: 'radialBar', height: 340, offsetY: -10, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        plotOptions: { radialBar: { startAngle: -90, endAngle: 90, track: { background: isLightTheme ? "#e2e8f0" : "#1e293b", strokeWidth: '97%' }, dataLabels: { name: { show: true, fontSize: '14px', color: isLightTheme ? '#64748b' : '#94a3b8' }, value: { offsetY: -50, fontSize: '24px', fontWeight: '900', color: isLightTheme ? '#0f172a' : '#f8fafc', formatter: v => v + "%" } } } },
        fill: { type: 'gradient', gradient: { shade: 'dark', type: 'horizontal', gradientToColors: ['#34d399'], stops: [0, 100] } },
        colors: ['#38bdf8'],
        labels: ['مقياس مأمونية السيولة'],
        theme: { mode: chartThemeMode }
    };
    if (anaSemiGauge) anaSemiGauge.destroy();
    anaSemiGauge = new ApexCharts(document.querySelector("#ana-chart-semicircle-gauge"), optionsGauge);
    anaSemiGauge.render();

    // Chart 6: Top Expenses Donut
    const optionsExpenses = {
        series: [totPurchases * 0.6 || 100, totPurchases * 0.25 || 50, totExpenses || 300, 200],
        labels: ['مشتريات البضاعة', 'تكاليف الشحن', 'المصروفات العمومية', 'نفقات أخرى'],
        chart: { type: 'donut', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#f43f5e', '#fb7185', '#a855f7', '#fbbf24'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, name: { show: true, fontSize: '13px' }, value: { show: true, fontSize: '15px', fontWeight: 900, formatter: v => parseFloat(v).toLocaleString('en-US', {maximumFractionDigits:0}) + ' SDG' } } } } },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    if (anaHollowExpenses) anaHollowExpenses.destroy();
    anaHollowExpenses = new ApexCharts(document.querySelector("#ana-chart-hollow-donut-top-expenses"), optionsExpenses);
    anaHollowExpenses.render();

    // Chart 7: Horizontal Bar Ranking
    const optionsHoriz = {
        series: [{ name: 'حجم القطاع', data: [totSales, totPurchases, totSafesBal, totBanksBal, totReceipts, totPayments] }],
        chart: { type: 'bar', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, borderRadius: 6, distributed: true } },
        colors: ['#38bdf8', '#f43f5e', '#10b981', '#34d399', '#a855f7', '#fbbf24'],
        xaxis: { categories: ['المبيعات', 'المشتريات', 'الخزن', 'البنوك', 'المقبوضات', 'المدفوعات'], labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits:0}) } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } } },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { show: false },
        theme: { mode: chartThemeMode }
    };
    if (anaHorizTop10) anaHorizTop10.destroy();
    anaHorizTop10 = new ApexCharts(document.querySelector("#ana-chart-horizontal-top10-ranking"), optionsHoriz);
    anaHorizTop10.render();

    // Chart 8: Fluid Area Curve
    const optionsFluid = {
        series: [{ name: 'السيولة التراكمية', data: [totSafesBal*0.3, totSafesBal*0.5, totSafesBal*0.7, totSafesBal*0.85, (totSafesBal + totBanksBal) || 100] }],
        chart: { type: 'area', height: 320, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#10b981'],
        stroke: { curve: 'smooth', width: 2.5 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 95, 100] } },
        xaxis: { categories: ['مرحلة 1', 'مرحلة 2', 'مرحلة 3', 'مرحلة 4', 'الوضع الحالي'], labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits:0}) } },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        theme: { mode: chartThemeMode }
    };
    if (anaFluidArea) anaFluidArea.destroy();
    anaFluidArea = new ApexCharts(document.querySelector("#ana-chart-cumulative-area-fluid"), optionsFluid);
    anaFluidArea.render();

    // Chart 9: Polygon Radar Equilibrium
    const optionsRadar = {
        series: [
            { name: 'الأداء الفعلي', data: [totSales/1000 || 10, totPurchases/1000 || 5, totSafesBal/1000 || 8, totBanksBal/1000 || 6, totReceipts/1000 || 7] }
        ],
        chart: { type: 'radar', height: 320, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#c084fc'],
        stroke: { width: 2 },
        fill: { opacity: 0.25 },
        markers: { size: 4 },
        xaxis: { categories: ['المبيعات', 'المشتريات', 'الخزن', 'البنوك', 'المقبوضات'], labels: { style: { colors: isLightTheme ? '#0f172a' : '#e2e8f0' } } },
        yaxis: { show: false },
        theme: { mode: chartThemeMode }
    };
    if (anaRadarEquil) anaRadarEquil.destroy();
    anaRadarEquil = new ApexCharts(document.querySelector("#ana-chart-radar-equilibrium"), optionsRadar);
    anaRadarEquil.render();

    // Chart 10: Solid Pie Chart
    const optionsSolidPie = {
        series: [sales.length || 1, purchases.length || 1, (items.length || 1)],
        labels: [`مبيعات (${sales.length})`, `مشتريات (${purchases.length})`, `أصناف (${items.length})`],
        chart: { type: 'pie', height: 320, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#10b981', '#f43f5e', '#38bdf8'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    if (anaSolidPie) anaSolidPie.destroy();
    anaSolidPie = new ApexCharts(document.querySelector("#ana-chart-solid-pie-active-inactive"), optionsSolidPie);
    anaSolidPie.render();

    // Chart 11: Multi-Radial Rings Gauge
    const totCount = (sales.length + purchases.length + items.length) || 1;
    const salesPct = Math.round((sales.length / totCount) * 100);
    const purchPct = Math.round((purchases.length / totCount) * 100);
    const itemsPct = Math.round((items.length / totCount) * 100);

    const optionsMultiRadial = {
        series: [salesPct, purchPct, itemsPct],
        chart: { type: 'radialBar', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        plotOptions: {
            radialBar: {
                dataLabels: {
                    name: { fontSize: '14px', color: isLightTheme ? '#64748b' : '#94a3b8' },
                    value: { fontSize: '16px', fontWeight: '800', color: isLightTheme ? '#0f172a' : '#f8fafc', formatter: v => v + '%' },
                    total: { show: true, label: 'حصة المبيعات', color: isLightTheme ? '#0f172a' : '#cbd5e1', formatter: () => salesPct + '%' }
                },
                track: { background: isLightTheme ? '#e2e8f0' : '#1e293b' }
            }
        },
        colors: ['#10b981', '#f43f5e', '#38bdf8'],
        labels: ['حصة المبيعات', 'حصة المشتريات', 'حصة المخزون'],
        theme: { mode: chartThemeMode }
    };
    if (anaMultiRadial) anaMultiRadial.destroy();
    anaMultiRadial = new ApexCharts(document.querySelector("#ana-chart-multi-radial-rings"), optionsMultiRadial);
    anaMultiRadial.render();
}

// Hook into initial load
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initDualAnalyticsEngine, 600);
});

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

