<?php $page_title = 'السجانة 2026 - التقارير والإحصائيات'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>السجانة 2026 - التقارير والإحصائيات</title>
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

        /* Layout Container taking full width with small equal padding */
        .analytics-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 25px 25px; /* Small equal padding on all sides */
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
        
        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }
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

    <!-- Unified Premium Header -->
    <?php include 'includes/header.php'; ?>

    <!-- AI Insights Section -->
    <div class="ai-insights-section glass-panel" style="border-top: 3px solid #8b5cf6; padding: 25px; margin-top: 30px; margin-bottom: 30px;">
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
        دليل التقارير التفصيلية الشاملة
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
        
        /* Accents */
        .cat-sales .report-category-icon { background: rgba(59, 130, 246, 0.15); box-shadow: 0 0 15px rgba(59, 130, 246, 0.2); }
        .cat-sales .report-category-title { color: #60a5fa; }
        .cat-sales .report-link-item:hover { border-right: 3px solid #3b82f6; }
        
        .cat-purchases .report-category-icon { background: rgba(236, 72, 153, 0.15); box-shadow: 0 0 15px rgba(236, 72, 153, 0.2); }
        .cat-purchases .report-category-title { color: #f472b6; }
        .cat-purchases .report-link-item:hover { border-right: 3px solid #ec4899; }
        
        .cat-inventory .report-category-icon { background: rgba(16, 185, 129, 0.15); box-shadow: 0 0 15px rgba(16, 185, 129, 0.2); }
        .cat-inventory .report-category-title { color: #34d399; }
        .cat-inventory .report-link-item:hover { border-right: 3px solid #10b981; }
        
        .cat-accounting .report-category-icon { background: rgba(245, 158, 11, 0.15); box-shadow: 0 0 15px rgba(245, 158, 11, 0.2); }
        .cat-accounting .report-category-title { color: #fbbf24; }
        .cat-accounting .report-link-item:hover { border-right: 3px solid #f59e0b; }
        
        .cat-final .report-category-icon { background: rgba(139, 92, 246, 0.15); box-shadow: 0 0 15px rgba(139, 92, 246, 0.2); }
        .cat-final .report-category-title { color: #a78bfa; }
        .cat-final .report-link-item:hover { border-right: 3px solid #8b5cf6; }
        
        .special-highlight {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.15), rgba(139, 92, 246, 0.05)) !important;
            border-color: rgba(139, 92, 246, 0.3) !important;
            color: #c084fc !important;
        }

        /* أنماط السحب والإفلات */
        .sortable-ghost {
            opacity: 0.4;
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px dashed rgba(255, 255, 255, 0.4) !important;
            border-radius: 16px;
            box-shadow: none !important;
        }
        .report-category-header {
            cursor: grab;
            transition: background 0.3s;
        }
        .report-category-header:hover {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
        }
        .report-link-item {
            cursor: grab;
        }
        .report-category-header:active, .report-link-item:active {
            cursor: grabbing;
        }
    </style>

    <div class="reports-directory-grid">
        <!-- 1. Sales -->
        <div class="report-category-card cat-sales">
            <div class="report-category-header">
                <div class="report-category-icon">🛒</div>
                <h4 class="report-category-title">تقارير المبيعات والعملاء</h4>
            </div>
            <div class="report-links-list">
                <a href="direct-sales.php" class="report-link-item"><span class="icon">📝</span> شاشة المبيعات (نقاط البيع)</a>


                <a href="aging-report.php" class="report-link-item"><span class="icon">⏳</span> أعمار الديون (مراقبة المتأخرات)</a>
            </div>
        </div>

        <!-- 2. Purchases -->
        <div class="report-category-card cat-purchases">
            <div class="report-category-header">
                <div class="report-category-icon">📦</div>
                <h4 class="report-category-title">تقارير المشتريات والموردين</h4>
            </div>
            <div class="report-links-list">
                <a href="purchase.php" class="report-link-item"><span class="icon">📥</span> شاشة فواتير المشتريات</a>


                <a href="#" class="report-link-item"><span class="icon">📊</span> تحليل مشتريات الأصناف</a>
            </div>
        </div>

        <!-- 3. Inventory -->
        <div class="report-category-card cat-inventory">
            <div class="report-category-header">
                <div class="report-category-icon">🏭</div>
                <h4 class="report-category-title">تقارير الجرد والمخازن</h4>
            </div>
            <div class="report-links-list">
                <a href="inventory-report.php" class="report-link-item"><span class="icon">📋</span> جرد الأرصدة (المتوفر والمحجوز)</a>
                <a href="warehouse-ledger.php" class="report-link-item"><span class="icon">🔄</span> تتبع حركة الأذونات</a>
                <a href="item-ledger.php" class="report-link-item"><span class="icon">🏷️</span> بطاقة الصنف (حركة مادة)</a>
                <a href="#" class="report-link-item"><span class="icon">💰</span> تقييم المخزون المالي</a>
            </div>
        </div>

        <!-- 4. Accounting -->
        <div class="report-category-card cat-accounting">
            <div class="report-category-header">
                <div class="report-category-icon">🏦</div>
                <h4 class="report-category-title">الحسابات والسندات الأساسية</h4>
            </div>
            <div class="report-links-list">

                <a href="vouchers-hub.php" class="report-link-item"><span class="icon">💵</span> إدارة المقبوضات والمدفوعات</a>
                <a href="posting-accounts.php" class="report-link-item"><span class="icon">✔️</span> ترحيل الحسابات</a>
                <a href="account-statement.php" class="report-link-item"><span class="icon">📄</span> كشف الحساب</a>
                <a href="checks-cycle.php" class="report-link-item"><span class="icon">💳</span> دورة الشيكات</a>
                <a href="journal-ledger.html" class="report-link-item"><span class="icon">📅</span> دفتر اليومية</a>
                <a href="journal-voucher.php" class="report-link-item"><span class="icon">📝</span> قيد اليومية</a>
                <a href="analytics.php" class="report-link-item"><span class="icon">📈</span> الإحصاء والتحليل</a>
                <a href="trial-balance.php" class="report-link-item"><span class="icon">⚖️</span> ميزانية المراجعة</a>
                <a href="pl-statement.php" class="report-link-item"><span class="icon">📉</span> قائمة الدخل</a>
                <a href="balance-sheet.html" class="report-link-item"><span class="icon">🏢</span> الميزانية العمومية</a>
                <a href="aging-report.php" class="report-link-item"><span class="icon">⏳</span> أعمار الأرصدة</a>
                <a href="#" class="report-link-item"><span class="icon">📜</span> كشف حساب التاريخي</a>
                <a href="#" class="report-link-item"><span class="icon">📑</span> كشف حساب مختصر</a>
                <a href="#" class="report-link-item"><span class="icon">🗂️</span> كشف حساب عام</a>
                <a href="#" class="report-link-item"><span class="icon">🏢</span> تقارير مركز التكلفة</a>
                <a href="#" class="report-link-item"><span class="icon">📊</span> المصاريف والإيرادات الإحصائية</a>
                <a href="#" class="report-link-item"><span class="icon">📉</span> قائمة الدخل التشغيلي</a>
                <a href="#" class="report-link-item"><span class="icon">📓</span> اليومية التوزيعية</a>
                <a href="#" class="report-link-item"><span class="icon">📔</span> دفتر الأستاذ التوزيعي</a>
                <a href="#" class="report-link-item"><span class="icon">⚖️</span> ميزانية المراجعة التوزيعية</a>
                <a href="#" class="report-link-item"><span class="icon">📉</span> قائمة الدخل التوزيعي</a>
                <a href="#" class="report-link-item"><span class="icon">🏢</span> تقرير الميزانية التوزيعي</a>
                <a href="#" class="report-link-item"><span class="icon">🎯</span> تقرير مركز التكلفة التوزيعي</a>
                <a href="#" class="report-link-item"><span class="icon">⚙️</span> مركز مولد التقارير</a>
            </div>
        </div>

        <!-- 5. Final Reports -->
        <div class="report-category-card cat-final">
            <div class="report-category-header">
                <div class="report-category-icon">📈</div>
                <h4 class="report-category-title">التقارير الختامية</h4>
            </div>
            <div class="report-links-list">
                <a href="financial-reports.php" class="report-link-item special-highlight"><span class="icon">📊</span> التقارير المالية والقوائم الختامية</a>
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

<script src="js/localforage.min.js"></script>
    <script src="js/app.js?v=1783054966.00607"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // We might want to wait for DBReady, but let's initialize directly if data is in localStorage
        setTimeout(initAIAnalytics, 500);
    });

    function initAIAnalytics() {
        // 1. Fetch Data
        const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
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
        const ctxFlow = document.getElementById('cashFlowChart').getContext('2d');
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
                    x: { grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false }, ticks: { padding: 10 } },
                    y: { 
                        grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false }, 
                        beginAtZero: true,
                        ticks: { padding: 15, callback: value => (value >= 1000 ? (value / 1000) + 'k' : value) }
                    }
                },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, padding: 20 } },
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
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                    tooltip: { backgroundColor: 'rgba(10, 8, 15, 0.9)', padding: 15, cornerRadius: 12 }
                }
            }
        });
    }

    function updateAICards(sales, purchases, items, payments, receipts, customers) {
        // AI Cashflow
        let totalReceivables = customers.reduce((sum, c) => sum + (parseFloat(c.currentBalance) || 0), 0);
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
        let lowStockItem = items.find(i => parseFloat(i.qty) <= parseFloat(i.minStock || 0));
        let invText = document.getElementById('ai-inventory-text');
        let invSugg = document.getElementById('ai-inventory-suggestion');
        if(lowStockItem) {
            invText.innerHTML = `رصد النظام نقصاً في <strong style="color: #38bdf8;">"${lowStockItem.name}"</strong> (الكمية: ${lowStockItem.qty}).`;
            invSugg.innerHTML = 'المخزون الحالي قد لا يكفي، يرجى طلب كمية إضافية.';
        } else {
            invText.innerHTML = `جميع الأصناف بمستويات مخزون آمنة حسب التحليل الحالي.`;
            invSugg.innerHTML = 'لا توجد إجراءات ملحة مطلوبة.';
        }

        // AI Anomaly
        let totalPayments = payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
        let avgPayment = payments.length ? (totalPayments / payments.length) : 0;
        let anomaly = payments.find(p => parseFloat(p.amount) > avgPayment * 2.5);
        let anomalyText = document.getElementById('ai-anomaly-text');
        let anomalySugg = document.getElementById('ai-anomaly-suggestion');
        if(anomaly && payments.length > 3) {
            anomalyText.innerHTML = `تم رصد <strong style="color: #f87171;">سند صرف غير معتاد</strong> بقيمة ${parseFloat(anomaly.amount).toLocaleString()} SDG.`;
            anomalySugg.innerHTML = `سند رقم: #${anomaly.voucherNo || '-'}`;
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
</script>
</body>
</html>
