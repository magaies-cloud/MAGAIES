<?php $page_title = 'الحسابات'; ?>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الحسابات | Al-Sajana ERP</title>
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
            --gold-primary: #38bdf8;
            --text-main: #f3f4f6;
            --text-dim: #9ca3af;
            
            --theme-receipt-single: #10b981;
            --theme-receipt-batch: #38bdf8;
            --theme-payment-single: #f43f5e;
            --theme-payment-batch: #f59e0b;
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
        }

        /* Override double sidebar spacing like in purchase/sales */
        body.has-sidebar { padding-right: 0 !important; }

        /* Ambient Animated Background Orbs */
        .ambient-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #08080c 0%, #000000 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            animation: floatOrb 20s infinite alternate ease-in-out;
        }

        .orb-1 { width: 600px; height: 600px; background: #10b981; top: -10%; right: -5%; }
        .orb-2 { width: 500px; height: 500px; background: #f43f5e; bottom: -10%; left: -10%; animation-delay: -5s; }
        .orb-3 { width: 400px; height: 400px; background: #0ea5e9; top: 40%; left: 40%; animation-duration: 25s; opacity: 0.1; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Layout Container taking full width with small equal padding */
        .hub-container {
            width: 100%; max-width: 1600px; margin: 0 auto; margin: 0 auto;
            padding: 0px 30px 20px 30px; box-sizing: border-box;
            display: flex; flex-direction: column; gap: 15px; flex: 1;
        }

        /* Ultra Premium Header */
        

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        
        .premium-header {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.15), rgba(139, 92, 246, 0.15), rgba(168, 85, 247, 0.15), rgba(217, 70, 239, 0.15));
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
            background: linear-gradient(135deg, #a855f7, #6366f1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
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
            background: linear-gradient(to right, #fff, #94a3b8);
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
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border);
            padding: 8px 20px;
            border-radius: 14px;
            text-align: center;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
        }

        .clock-widget #header-clock {
            color: #e2e8f0;
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

        /* Content Grid */
        .vouchers-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
            width: 100%;
        }

        @media (max-width: 1400px) {
            .vouchers-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .vouchers-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Premium Card Design */
        .premium-card {
            background: var(--surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
            height: 100%;
            min-height: 220px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2), inset 0 1px 1px rgba(255,255,255,0.03);
            transform-style: preserve-3d;
            animation: fadeUpIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeUpIn {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .premium-card:nth-child(1) { animation-delay: 0.1s; }
        .premium-card:nth-child(2) { animation-delay: 0.2s; }
        .premium-card:nth-child(3) { animation-delay: 0.3s; }
        .premium-card:nth-child(4) { animation-delay: 0.4s; }

        /* Colored Glows */
        .card-glow {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 0%, var(--card-color), transparent 70%);
            opacity: 0.08;
            transition: opacity 0.5s ease, transform 0.5s ease;
            z-index: 0;
            pointer-events: none;
        }

        .premium-card:hover .card-glow {
            opacity: 0.2;
            transform: scale(1.1) translateY(-20px);
        }

        .premium-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, var(--card-color), transparent);
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }

        .premium-card:hover {
            transform: translateY(-12px) scale(1.02);
            border-color: var(--border-highlight);
            box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 40px rgba(var(--card-rgb), 0.15);
        }

        .card-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .icon-wrapper {
            width: 55px;
            height: 55px;
            border-radius: 16px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.5), 0 10px 20px rgba(0,0,0,0.2);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }
        
        .icon-wrapper::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 26px;
            background: linear-gradient(135deg, var(--card-color), transparent);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .premium-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(-5deg);
            background: rgba(0,0,0,0.5);
            border-color: rgba(255,255,255,0.15);
        }
        
        .premium-card:hover .icon-wrapper::after {
            opacity: 0.5;
        }

        .v-title {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
        }

        .premium-card:hover .v-title {
            color: var(--card-color);
        }

        .v-subtitle {
            color: #94a3b8;
            font-size: 0.85rem;
            line-height: 1.5;
            margin: 0 0 15px 0;
            font-weight: 400;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .v-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.05);
            transition: border-color 0.3s;
        }

        .premium-card:hover .v-footer {
            border-top-color: rgba(255,255,255,0.1);
        }

        .v-badge {
            background: rgba(0,0,0,0.4);
            color: #cbd5e1;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            border: 1px solid rgba(255,255,255,0.05);
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .premium-card:hover .v-badge {
            background: rgba(var(--card-rgb), 0.15);
            color: var(--card-color);
            border-color: rgba(var(--card-rgb), 0.3);
        }

        .v-arrow {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #fff;
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transform: translateX(10px) scale(0.8);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            font-family: 'Inter', sans-serif;
        }

        .premium-card:hover .v-arrow {
            transform: translateX(0) scale(1);
            opacity: 1;
            background: var(--card-color);
            color: #fff;
            box-shadow: 0 5px 15px rgba(var(--card-rgb), 0.4);
        }

        /* Unified Card Theme */
        .premium-card { --card-color: #fbbf24; --card-rgb: 251, 191, 36; }

        .flow-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(var(--card-rgb), 0.1);
            color: var(--card-color);
            border: 1px solid rgba(var(--card-rgb), 0.2);
        }

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
    
</head>
<body>

<!-- Ambient Animated Background -->
<div class="ambient-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="hub-container">

    <!-- Unified Premium Header -->
    <?php $header_theme_class = 'header-theme-sales'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- Vouchers Grid - Full Width -->
    <div class="vouchers-grid">

        <!-- 3. Single Payment -->
        <a data-href="single-cash-payment.php" onclick="window.location.href='single-cash-payment.php'" class="premium-card" style="--card-color: #f43f5e; --card-rgb: 244, 63, 94;">
            <div class="card-glow"></div>
            <div class="flow-badge" style="background: rgba(244, 63, 94, 0.1); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.2);">
                <span>🔴</span> مدفوعات
            </div>
            <div class="card-content">
                <div class="icon-wrapper">
                    <span class="v-icon">💸</span>
                </div>
                <h3 class="v-title">حركية مالية</h3>
                <p class="v-subtitle">صرف مبلغ لجهة واحدة.</p>
                <div class="v-footer">
                    <span class="v-badge" style="background: rgba(244, 63, 94, 0.15); color: #f43f5e; border-color: rgba(244, 63, 94, 0.3);">Payment</span>
                    <span class="v-arrow" style="background: #f43f5e; color: #fff;">←</span>
                </div>
            </div>
        </a>

        <!-- Grouped Payment -->
        <a data-href="multi-cash-payment.php" onclick="window.location.href='multi-cash-payment.php'" class="premium-card" style="--card-color: #f97316; --card-rgb: 249, 115, 22;">
            <div class="card-glow"></div>
            <div class="flow-badge" style="background: rgba(249,115,22,0.1); color: #f97316; border: 1px solid rgba(249,115,22,0.2);">
                <span>👥</span> مجمعة
            </div>
            <div class="card-content">
                <div class="icon-wrapper">
                    <span class="v-icon">🧾</span>
                </div>
                <h3 class="v-title">حركة مالية مجمعة</h3>
                <p class="v-subtitle">صرف أو قبض من عدة حسابات.</p>
                <div class="v-footer">
                    <span class="v-badge" style="background: rgba(249,115,22,0.15); color: #f97316; border-color: rgba(249,115,22,0.3);">Multi</span>
                    <span class="v-arrow" style="background: #f97316; color: #fff;">←</span>
                </div>
            </div>
        </a>

        <!-- Journal Voucher -->
        <a data-href="journal-voucher.php" onclick="window.location.href='journal-voucher.php'" class="premium-card" style="--card-color: #a855f7; --card-rgb: 168, 85, 247;">
            <div class="card-glow"></div>
            <div class="flow-badge" style="background: rgba(168,85,247,0.1); color: #a855f7; border: 1px solid rgba(168,85,247,0.2);">
                <span>📈</span> قيود
            </div>
            <div class="card-content">
                <div class="icon-wrapper">
                    <span class="v-icon">📝</span>
                </div>
                <h3 class="v-title">قيد يومية</h3>
                <p class="v-subtitle">إدخال قيود محاسبية وتسويات مالية.</p>
                <div class="v-footer">
                    <span class="v-badge" style="background: rgba(168,85,247,0.15); color: #a855f7; border-color: rgba(168,85,247,0.3);">Journal</span>
                    <span class="v-arrow" style="background: #a855f7; color: #fff;">←</span>
                </div>
            </div>
        </a>

        <!-- Checks Cycle -->
        <a data-href="checks-cycle.php" onclick="window.location.href='checks-cycle.php'" class="premium-card" style="--card-color: #f59e0b; --card-rgb: 245, 158, 11;">
            <div class="card-glow"></div>
            <div class="flow-badge" style="background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2);">
                <span>🧾</span> شيكات
            </div>
            <div class="card-content">
                <div class="icon-wrapper">
                    <span class="v-icon">💳</span>
                </div>
                <h3 class="v-title">دورة الشيكات</h3>
                <p class="v-subtitle">إدارة ومتابعة الشيكات الصادرة والواردة.</p>
                <div class="v-footer">
                    <span class="v-badge" style="background: rgba(245,158,11,0.15); color: #f59e0b; border-color: rgba(245,158,11,0.3);">Checks</span>
                    <span class="v-arrow" style="background: #f59e0b; color: #fff;">←</span>
                </div>
            </div>
        </a>

        <!-- Posting Accounts -->
        <a data-href="posting-accounts.php" onclick="window.location.href='posting-accounts.php'" class="premium-card" style="--card-color: #38bdf8; --card-rgb: 56, 189, 248;">
            <div class="card-glow"></div>
            <div class="flow-badge" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);">
                <span>🔄</span> ترحيل
            </div>
            <div class="card-content">
                <div class="icon-wrapper">
                    <span class="v-icon">🔄</span>
                </div>
                <h3 class="v-title">ترحيل الحسابات</h3>
                <p class="v-subtitle">مراجعة وترحيل العمليات المالية وتدقيق الحسابات المفتوحة.</p>
                <div class="v-footer">
                    <span class="v-badge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border-color: rgba(56, 189, 248, 0.3);">Posting</span>
                    <span class="v-arrow" style="background: #38bdf8; color: #fff;">←</span>
                </div>
            </div>
        </a>

        <!-- AI Money Advisor -->
        <a data-href="ai_money_advisor.php" onclick="window.location.href='ai_money_advisor.php'" class="premium-card" style="--card-color: #10b981; --card-rgb: 16, 185, 129;">
            <div class="card-glow"></div>
            <div class="flow-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">
                <span>🤖</span> مستشار
            </div>
            <div class="card-content">
                <div class="icon-wrapper">
                    <span class="v-icon">🤖</span>
                </div>
                <h3 class="v-title">مستشار الـ AI للأموال</h3>
                <p class="v-subtitle">تحليل مالي ذكي وإدارة التدفقات النقدية بالذكاء الاصطناعي.</p>
                <div class="v-footer">
                    <span class="v-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border-color: rgba(16, 185, 129, 0.3);">AI Advisor</span>
                    <span class="v-arrow" style="background: #10b981; color: #fff;">←</span>
                </div>
            </div>
        </a>

    </div>

    <!-- Recent Transactions -->
    <div class="premium-card" style="flex: 1; min-height: 250px; display: flex; flex-direction: column; margin-top: 15px; background: rgba(18, 15, 25, 0.85); cursor: default; transform: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5); padding: 20px;">
        <h3 style="color: #fff; margin-top: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">🕒 أحدث العمليات (آخر 10 سندات)</h3>
        <div style="overflow-y: auto; flex: 1; padding-right: 5px;" class="custom-scrollbar">
            <table style="width: 100%; border-collapse: collapse; text-align: right; color: #fff;">
                <thead>
                    <tr style="background: rgba(255,255,255,0.05);">
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">رقم السند</th>
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">النوع</th>
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">التاريخ</th>
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">البيان</th>
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">من حساب (صُرف منه)</th>
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">إلى حساب (تم الاستلام فيه)</th>
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">المبلغ</th>
                        <th style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="recent-transactions-body">
                    <tr><td colspan='8' style='text-align:center; padding:20px; color:#94a3b8;'>جاري التحميل...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
    document.addEventListener('DBReady', async () => {
        try {
            const tkSR = window.getTenantKey ? window.getTenantKey('erp_single_receipts_v1') : 'erp_single_receipts_v1';
            const tkBR = window.getTenantKey ? window.getTenantKey('erp_receipts_v1') : 'erp_receipts_v1';
            const tkSP = window.getTenantKey ? window.getTenantKey('erp_single_payments_v1') : 'erp_single_payments_v1';
            const tkBP = window.getTenantKey ? window.getTenantKey('erp_payments_v1') : 'erp_payments_v1';
            const tkCR = window.getTenantKey ? window.getTenantKey('erp_comprehensive_receipts_v1') : 'erp_comprehensive_receipts_v1';
            const tkCP = window.getTenantKey ? window.getTenantKey('erp_comprehensive_payments_v1') : 'erp_comprehensive_payments_v1';

            let sr = [], br = [], sp = [], bp = [], cr = [], cp = [];
            
            if (typeof localforage !== 'undefined') {
                sr = (await localforage.getItem(tkSR)) || [];
                br = (await localforage.getItem(tkBR)) || [];
                sp = (await localforage.getItem(tkSP)) || [];
                bp = (await localforage.getItem(tkBP)) || [];
                cr = (await localforage.getItem(tkCR)) || [];
                cp = (await localforage.getItem(tkCP)) || [];
            }

            if (!Array.isArray(sr) || sr.length === 0) sr = JSON.parse(localStorage.getItem(tkSR) || localStorage.getItem('erp_single_receipts_v1') || '[]');
            if (!Array.isArray(br) || br.length === 0) br = JSON.parse(localStorage.getItem(tkBR) || localStorage.getItem('erp_receipts_v1') || '[]');
            if (!Array.isArray(sp) || sp.length === 0) sp = JSON.parse(localStorage.getItem(tkSP) || localStorage.getItem('erp_single_payments_v1') || '[]');
            if (!Array.isArray(bp) || bp.length === 0) bp = JSON.parse(localStorage.getItem(tkBP) || localStorage.getItem('erp_payments_v1') || '[]');
            if (!Array.isArray(cr) || cr.length === 0) cr = JSON.parse(localStorage.getItem(tkCR) || localStorage.getItem('erp_comprehensive_receipts_v1') || '[]');
            if (!Array.isArray(cp) || cp.length === 0) cp = JSON.parse(localStorage.getItem(tkCP) || localStorage.getItem('erp_comprehensive_payments_v1') || '[]');

            sr = Array.isArray(sr) ? sr : Object.values(sr);
            br = Array.isArray(br) ? br : Object.values(br);
            sp = Array.isArray(sp) ? sp : Object.values(sp);
            bp = Array.isArray(bp) ? bp : Object.values(bp);
            cr = Array.isArray(cr) ? cr : Object.values(cr);
            cp = Array.isArray(cp) ? cp : Object.values(cp);
            
            let all = [];
            sr.forEach(x => { if(x && !x.deleted) all.push({...x, _type: 'سند قبض مفرد', _color: '#10b981', _date: new Date(x.date || x.createdAt || 0), _storeKey: tkSR, _source: 'sr'})});
            br.forEach(x => { if(x && !x.deleted) all.push({...x, _type: 'سند قبض متعدد', _color: '#38bdf8', _date: new Date(x.date || x.createdAt || 0), _storeKey: tkBR, _source: 'br'})});
            sp.forEach(x => { if(x && !x.deleted) all.push({...x, _type: 'سند صرف مفرد', _color: '#f43f5e', _date: new Date(x.date || x.createdAt || 0), _storeKey: tkSP, _source: 'sp'})});
            bp.forEach(x => { if(x && !x.deleted) all.push({...x, _type: 'سند صرف متعدد', _color: '#f59e0b', _date: new Date(x.date || x.createdAt || 0), _storeKey: tkBP, _source: 'bp'})});
            cr.forEach(x => { if(x && !x.deleted) all.push({...x, _type: 'سند قبض مجمع', _color: '#0ea5e9', _date: new Date(x.date || x.createdAt || 0), _storeKey: tkCR, _source: 'cr'})});
            cp.forEach(x => { if(x && !x.deleted) all.push({...x, _type: 'سند صرف مجمع', _color: '#eab308', _date: new Date(x.date || x.createdAt || 0), _storeKey: tkCP, _source: 'cp'})});
            
            function sanitizeList(list, storeKey) {
                if (!Array.isArray(list)) return;
                let changed = false;
                list.forEach(x => {
                    if (!x) return;
                    const isTimestampId = (id) => !id || String(id).startsWith('MV-') || String(id).startsWith('MV17') || String(id).startsWith('TEMP_');
                    if (isTimestampId(x.id) && !isTimestampId(x.voucherNo)) {
                        x.id = x.voucherNo;
                        changed = true;
                    } else if (isTimestampId(x.voucherNo) && !isTimestampId(x.id)) {
                        x.voucherNo = x.id;
                        changed = true;
                    } else if (isTimestampId(x.id) && isTimestampId(x.voucherNo)) {
                        const yymm = (x.date || x.createdAt || '').replace(/-/g, '').substring(2, 6) || '2608';
                        const cleanNo = 'M' + yymm + String(Math.floor(Math.random() * 8999) + 1000);
                        x.id = cleanNo;
                        x.voucherNo = cleanNo;
                        changed = true;
                    }
                });
                if (changed) {
                    try {
                        localStorage.setItem(storeKey, JSON.stringify(list));
                        if (typeof localforage !== 'undefined') localforage.setItem(storeKey, list);
                    } catch(e) {}
                }
            }

            sanitizeList(sr, tkSR);
            sanitizeList(br, tkBR);
            sanitizeList(sp, tkSP);
            sanitizeList(bp, tkBP);
            sanitizeList(cr, tkCR);
            sanitizeList(cp, tkCP);

            if (window.rebuildGLFromAllTables) {
                try { window.rebuildGLFromAllTables(false); } catch(e) {}
            }

            all.sort((a,b) => {
                const dateA = isNaN(a._date.getTime()) ? 0 : a._date.getTime();
                const dateB = isNaN(b._date.getTime()) ? 0 : b._date.getTime();
                return dateB - dateA;
            });
            let top = all.slice(0, 10);
            
            let tbody = document.getElementById('recent-transactions-body');
            if(top.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:20px; color:#94a3b8;">لا توجد عمليات مسجلة</td></tr>';
                return;
            }

            let localAccountMap = new Map();
            try {
                let keysToFetch = ['erp_customers_v1', 'erp_suppliers_v1', 'erp_banks_v1', 'erp_safes_v1', 'erp_employees_v1', 'erp_accounts_v1', 'erp_cost_centers_v1'];
                for (let k of keysToFetch) {
                    let tk = window.getTenantKey ? window.getTenantKey(k) : k;
                    let items = JSON.parse(localStorage.getItem(tk) || localStorage.getItem(k) || '[]');
                    items.forEach(item => {
                        if (item && item.id) localAccountMap.set(String(item.id).toLowerCase(), item.name || item.nameAr || item.accountName);
                    });
                }
            } catch(e) {}

            function resolveAccName(accId) {
                if (!accId || accId === 'متعدد') return accId;
                let idStr = String(accId).toLowerCase().trim();
                if (localAccountMap.has(idStr)) return localAccountMap.get(idStr);
                let cleanId = idStr.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
                if (localAccountMap.has(cleanId)) return localAccountMap.get(cleanId);
                return accId;
            }
            
            tbody.innerHTML = top.map((x, rowIdx) => {
                let fromAcc = x.fromAccount || x.withdrawalAccount || x.cashSafe || x.bankSourceName || x.mobileWallet || 'متعدد';
                let toAcc = x.toAccount || x.depositAccount || x.accountId || 'متعدد';
                let fromName = resolveAccName(fromAcc);
                let toName = resolveAccName(toAcc);
                let vid = (x.voucherNo && !String(x.voucherNo).startsWith('MV-') && !String(x.voucherNo).startsWith('MV17')) 
                          ? x.voucherNo 
                          : ((x.id && !String(x.id).startsWith('MV-') && !String(x.id).startsWith('MV17')) 
                              ? x.id 
                              : (x.voucherNo || x.id || ''));
                
                // Store full voucher data for reliable drill-down
                try { sessionStorage.setItem('_hub_voucher_' + rowIdx, JSON.stringify(x)); } catch(e) {}
                
                return `
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; cursor: pointer;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px 10px; font-weight: bold; color: ${x._color}; text-decoration: underline;" onclick="editHubVoucher('${vid}', '${x._source}', ${rowIdx})" title="انقر لتعديل السند">${vid || '-'}</td>
                    <td style="padding: 12px 10px;"><span style="background: ${x._color}20; color: ${x._color}; padding: 3px 8px; border-radius: 6px; font-size: 0.85rem;">${x._type}</span></td>
                    <td style="padding: 12px 10px; color: #cbd5e1;">${!isNaN(x._date.getTime()) ? x._date.toLocaleDateString('en-GB') : '-'}</td>
                    <td style="padding: 12px 10px; color: #cbd5e1;">${x.description || x.notes || x.statement || '-'}</td>
                    <td style="padding: 12px 10px; color: #cbd5e1;">${fromName}</td>
                    <td style="padding: 12px 10px; color: #cbd5e1;">${toName}</td>
                    <td style="padding: 12px 10px; font-weight: bold; color: #fff;">${(x.amount || x.totalAmount || x.amountLocal || 0).toLocaleString()}</td>
                    <td style="padding: 12px 10px; white-space: nowrap;">
                        <button onclick="editHubVoucher('${vid}', '${x._source}', ${rowIdx})" style="background: rgba(56,189,248,0.1); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); border-radius: 4px; padding: 4px 8px; cursor: pointer; margin-left: 5px; font-family: 'Tajawal', sans-serif;">تعديل</button>
                        <button onclick="deleteHubVoucher('${vid}', '${x._storeKey}')" style="background: rgba(244,63,94,0.1); color: #f43f5e; border: 1px solid rgba(244,63,94,0.3); border-radius: 4px; padding: 4px 8px; cursor: pointer; font-family: 'Tajawal', sans-serif;">حذف</button>
                    </td>
                </tr>
            `}).join('');
        } catch(e) {
            console.error(e);
            document.getElementById('recent-transactions-body').innerHTML = '<tr><td colspan="8" style="text-align:center; padding:20px; color:#ef4444;">خطأ في جلب البيانات: ' + (e.message || e) + '</td></tr>';
        }
    });

    window.editHubVoucher = function(id, source, rowIdx) {
        // Store the full voucher data so the target page can load it reliably
        try {
            let storedData = sessionStorage.getItem('_hub_voucher_' + rowIdx);
            if (storedData) {
                sessionStorage.setItem('_edit_voucher_data', storedData);
            }
        } catch(e) { console.error('Failed to store voucher for edit:', e); }
        
        let route = '';
        if (source === 'sr') route = 'single-cash-payment.php?type=receipt&id=' + id;
        else if (source === 'br') route = 'multi-cash-payment.php?type=receipt&id=' + id;
        else if (source === 'sp') route = 'single-cash-payment.php?id=' + id;
        else if (source === 'bp') route = 'multi-cash-payment.php?id=' + id;
        else if (source === 'cr') route = 'comprehensive-receipt.php?id=' + id;
        else if (source === 'cp') route = 'comprehensive-payment.php?id=' + id;
        
        if (route) window.location.href = route;
    };

    window.deleteHubVoucher = async function(id, storeKey) {
        if(!confirm('هل أنت متأكد من حذف هذا السند؟ سيتم تحديث الحسابات ودفتر الأستاذ تلقائياً.')) return;
        try {
            let data = (await localforage.getItem(storeKey)) || [];
            if (!Array.isArray(data) && data) data = Object.values(data);
            
            let idx = data.findIndex(v => String(v.id) === String(id) || String(v.voucherNo) === String(id));
            if(idx > -1) {
                data[idx].deleted = true;
                await localforage.setItem(storeKey, data);
                localStorage.setItem(storeKey, JSON.stringify(data)); // Fallback
                if (window.rebuildGLFromAllTables) window.rebuildGLFromAllTables(false);
                if (typeof showToast === 'function') showToast('تم حذف السند وتحديث الحسابات بنجاح.', 'success');
                else alert('تم الحذف بنجاح.');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('لم يتم العثور على السند.');
            }
        } catch(e) {
            console.error(e);
            alert('حدث خطأ أثناء الحذف.');
        }
    };
    </script>
    <style>
        @keyframes shakeEdit {
            0% { transform: rotate(-1deg); }
            50% { transform: rotate(1.5deg); }
            100% { transform: rotate(-1deg); }
        }
        .vouchers-grid.edit-mode .premium-card {
            animation: shakeEdit 0.4s infinite;
            cursor: grab !important;
            box-shadow: 0 0 15px rgba(234,179,8,0.5);
            /* Disable normal hover effects during edit mode */
            transform: none !important;
        }
        .vouchers-grid.edit-mode .premium-card:active {
            cursor: grabbing !important;
        }
        .sortable-ghost {
            opacity: 0.4;
            background: rgba(255,255,255,0.1);
        }
    
        
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
    <!-- SortableJS for Drag and Drop -->
    <script defer src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        let isEditMode = false;
        let sortableInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            const grid = document.querySelector('.vouchers-grid');
            const savedOrder = JSON.parse(localStorage.getItem('vouchers_hub_order') || '[]');
            
            if (savedOrder && savedOrder.length > 0) {
                const cards = Array.from(grid.children);
                cards.sort((a, b) => {
                    const idA = a.getAttribute('data-href');
                    const idB = b.getAttribute('data-href');
                    if(!idA || !idB) return 0;
                    const indexA = savedOrder.indexOf(idA);
                    const indexB = savedOrder.indexOf(idB);
                    const posA = indexA === -1 ? 999 : indexA;
                    const posB = indexB === -1 ? 999 : indexB;
                    return posA - posB;
                });
                cards.forEach(card => grid.appendChild(card));
            }

            sortableInstance = new Sortable(grid, {
                animation: 150,
                disabled: true,
                ghostClass: 'sortable-ghost',
                onEnd: function () {
                    const newOrder = Array.from(grid.children)
                        .map(c => c.getAttribute('data-href'))
                        .filter(href => href);
                    localStorage.setItem('vouchers_hub_order', JSON.stringify(newOrder));
                }
            });

            // Prevent clicks when in edit mode
            grid.addEventListener('click', (e) => {
                if (isEditMode) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
            
            // Override onclick attributes during edit mode to prevent navigation
            const cards = grid.querySelectorAll('.premium-card');
            cards.forEach(card => {
                const originalOnClick = card.getAttribute('onclick');
                if (originalOnClick) {
                    card.setAttribute('data-onclick', originalOnClick);
                    card.onclick = function(e) {
                        if (isEditMode) {
                            e.preventDefault();
                            return false;
                        } else {
                            // execute original onclick
                            window.location.href = card.getAttribute('data-href');
                        }
                    };
                }
            });
        });

        function toggleEditMode() {
            isEditMode = !isEditMode;
            const btn = document.getElementById('edit-order-btn');
            const grid = document.querySelector('.vouchers-grid');
            
            if (isEditMode) {
                btn.innerHTML = '✔️ حفظ الترتيب';
                btn.style.background = '#10b981';
                btn.style.color = '#fff';
                btn.style.borderColor = '#059669';
                grid.classList.add('edit-mode');
                sortableInstance.option('disabled', false);
            } else {
                btn.innerHTML = '⚙️ ترتيب';
                btn.style.background = 'rgba(234,179,8,0.2)';
                btn.style.color = '#fbbf24';
                btn.style.borderColor = 'rgba(234,179,8,0.4)';
                grid.classList.remove('edit-mode');
                sortableInstance.option('disabled', true);
            }
        }
    </script>
</body>
</html>
