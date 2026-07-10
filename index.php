<?php $page_title = 'مقاييس - أذكى نظام سحابي لإدارة الموارد (ERP)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" href="manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#030712">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>مقاييس - أذكى نظام سحابي لإدارة الموارد (ERP)</title>
    <style>
        :root {
            --bg-color: #030712;
            --surface-color: rgba(17, 24, 39, 0.7);
            --primary-accent: #3b82f6;
            --secondary-accent: #8b5cf6;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --success-color: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Tajawal', sans-serif; }
        
        body {
            background-color: var(--bg-color); color: var(--text-primary);
            overflow-x: hidden;
            background-image: radial-gradient(circle at top right, rgba(59,130,246,0.1), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(139,92,246,0.1), transparent 40%);
            background-attachment: fixed;
        }

        .glass-panel { background: var(--surface-color); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid var(--glass-border); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); }

        nav { position: fixed; top: 0; width: 100%; z-index: 1000; display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 5%; border-bottom: 1px solid var(--glass-border); transition: all 0.3s ease; }
        .logo { font-size: 1.6rem; font-weight: 900; display: flex; align-items: center; gap: 12px; color: #fff; text-decoration: none; }
        .logo-icon { background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 4px 20px rgba(59,130,246, 0.4); }

        .btn-nav { background: rgba(255,255,255,0.05); color: #fff; padding: 0.6rem 1.8rem; border-radius: 10px; text-decoration: none; font-weight: 700; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s; cursor: pointer;}
        .btn-nav.active { background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); border:none; box-shadow: 0 0 20px rgba(59,130,246,0.5); }
        .btn-nav:hover { transform: translateY(-2px); }

        /* Hero */
        .hero { position: relative; min-height: 100vh; display: flex; align-items: center; padding: 120px 5% 50px; overflow: hidden; }
        .hero-bg-img { position: absolute; top: 0; right: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.15; z-index: -1; filter: saturate(1.5); }
        
        .hero-content { flex: 1; max-width: 600px; z-index: 2; }
        .hero-badge { display: inline-block; padding: 8px 16px; background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.3); border-radius: 20px; color: #60a5fa; font-weight: 700; margin-bottom: 20px; font-size: 0.9rem; letter-spacing: 1px; }
        .hero h1 { font-size: 4.5rem; font-weight: 900; line-height: 1.1; margin-bottom: 20px; background: linear-gradient(to left, #fff, #93c5fd); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 1.4rem; color: var(--text-secondary); margin-bottom: 40px; line-height: 1.6; }
        
        .hero-visual { flex: 1; position: relative; z-index: 2; perspective: 1000px; display: flex; justify-content: center; }
        .hero-visual img { width: 100%; max-width: 700px; border-radius: 20px; transform: rotateY(-15deg) rotateX(5deg); box-shadow: -20px 20px 50px rgba(0,0,0,0.5), 0 0 50px rgba(59,130,246,0.3); transition: transform 0.5s; border: 1px solid rgba(255,255,255,0.1); }
        .hero-visual img:hover { transform: rotateY(0) rotateX(0); }

        .btn-primary { background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); color: #fff; padding: 1rem 2.5rem; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 1.2rem; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(59,130,246, 0.4); transition: all 0.3s; display: inline-flex; align-items: center; gap: 10px; }
        .btn-primary:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(59,130,246, 0.6); }

        /* Marquee Partners */
        .partners { padding: 60px 0; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); overflow: hidden; position: relative; }
        .partners p { text-align: center; color: var(--text-secondary); font-size: 1.1rem; font-weight: 600; margin-bottom: 30px; letter-spacing: 1px; }
        .marquee-wrapper { display: flex; width: 200%; animation: scroll 30s linear infinite; gap: 40px; align-items: center; }
        .marquee-wrapper:hover { animation-play-state: paused; }
        .partner-logo { filter: grayscale(100%) opacity(0.7); transition: all 0.3s; display: flex; align-items: center; gap: 15px; color: #fff; text-decoration: none; font-size: 1.2rem; font-weight: 700; white-space: nowrap; }
        .partner-logo img { height: 50px; border-radius: 10px; }
        .partner-logo:hover { filter: grayscale(0%) opacity(1); transform: scale(1.05); }
        .partner-avatar { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 900; color: #fff; flex-shrink: 0; }
        
        @keyframes scroll { 0% { transform: translateX(0); } 100% { transform: translateX(50%); } }

        /* Features */
        .features { padding: 100px 5%; position: relative; }
        .section-title { text-align: center; font-size: 3rem; font-weight: 900; margin-bottom: 20px; color: #fff; }
        .section-subtitle { text-align: center; color: var(--text-secondary); font-size: 1.2rem; max-width: 600px; margin: 0 auto 60px; line-height: 1.6; }
        
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; max-width: 1300px; margin: 0 auto; }
        .feature-card { padding: 40px 30px; border-radius: 24px; transition: all 0.4s; position: relative; overflow: hidden; background: linear-gradient(180deg, rgba(30,41,59,0.8), rgba(15,23,42,0.9)); border: 1px solid rgba(255,255,255,0.05); }
        .feature-card:hover { transform: translateY(-10px); border-color: var(--primary-accent); box-shadow: 0 20px 40px rgba(0,0,0,0.4), inset 0 0 30px rgba(59,130,246,0.05); }
        .feature-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 25px; background: rgba(59,130,246,0.1); color: var(--primary-accent); border: 1px solid rgba(59,130,246,0.2); }
        .feature-card h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 15px; color: #fff; }
        .feature-card p { color: var(--text-secondary); line-height: 1.7; font-size: 1rem; }

        /* Pricing Section */
        .pricing { padding: 100px 5%; position: relative; background: radial-gradient(circle at center, rgba(139,92,246,0.05), transparent 60%); }
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; max-width: 1400px; margin: 0 auto; }
        .plan-card { padding: 50px 40px; border-radius: 30px; text-align: center; transition: all 0.4s; position: relative; background: var(--surface-color); border: 2px solid var(--glass-border); display: flex; flex-direction: column; }
        .plan-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,0,0,0.5); border-color: var(--primary-accent); }
        .plan-name { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 10px; }
        .plan-price { font-size: 3.5rem; font-weight: 900; color: var(--primary-accent); margin-bottom: 5px; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; }
        .plan-price span { font-size: 1.2rem; color: var(--text-secondary); font-weight: 500; margin-top: 15px; margin-right: 5px; }
        .plan-features { list-style: none; margin: 30px 0; padding: 0; text-align: right; flex-grow: 1; }
        .plan-features li { margin-bottom: 15px; color: #cbd5e1; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
        .plan-features li::before { content: '✓'; color: var(--success-color); font-weight: 900; font-size: 1.2rem; }
        .plan-btn { width: 100%; padding: 15px; border-radius: 15px; font-weight: 800; font-size: 1.1rem; border: none; cursor: pointer; transition: all 0.3s; margin-top: auto; text-decoration: none; display: block; }
        
        /* Recommended Plan styling */
        .plan-card.recommended { background: linear-gradient(180deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border: 2px solid var(--primary-accent); transform: scale(1.05); }
        .plan-card.recommended:hover { transform: scale(1.05) translateY(-10px); }
        .plan-badge { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); color: #fff; padding: 5px 20px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; letter-spacing: 1px; }

        footer { padding: 40px 5%; text-align: center; border-top: 1px solid var(--glass-border); background: #020617; }
        footer p { color: var(--text-secondary); font-size: 0.95rem; }

        @media (max-width: 992px) {
            .hero { flex-direction: column; text-align: center; padding-top: 150px; }
            .hero-visual { margin-top: 50px; perspective: none; }
            .hero-visual img { transform: none; }
            .hero h1 { font-size: 3rem; }
            .plan-card.recommended { transform: scale(1); }
            .plan-card.recommended:hover { transform: translateY(-10px); }
        }
    
        /* Ads Banner */
        .ads-banner {
            width: 100%;
            height: 120px;
            margin-top: 80px; 
            position: relative;
            overflow: hidden;
            display: none;
            background: rgba(15,23,42,0.8);
            border-bottom: 1px solid var(--glass-border);
            z-index: 900;
        }
        .ad-slide {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ad-slide.active { opacity: 1; z-index: 2; }
        .ad-badge {
            position: absolute;
            bottom: 15px;
            right: 20px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 5;
            font-family: 'Tajawal', sans-serif;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .ad-slide img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            cursor: default;
        }
    
        /* Dynamic Dashboard Mockup */
        .hero-visual {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .dash-mockup {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 40px rgba(56, 189, 248, 0.2);
            display: flex;
            height: 450px;
            direction: rtl;
        }
        .dash-sidebar {
            width: 80px;
            background: rgba(15, 23, 42, 0.8);
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .dash-icon {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        .dash-icon:hover {
            background: var(--primary-accent);
            transform: scale(1.1);
        }
        .dash-icon::after {
            content: '';
            position: absolute;
            top: 0; right: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: dashShimmer 3s infinite;
        }
        .dash-main {
            flex: 1;
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dash-title {
            width: 150px; height: 24px;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
        }
        .dash-user {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(56,189,248,0.5);
            animation: pulseGlow 2s infinite alternate;
        }
        .dash-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .dash-stat-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        .dash-stat-card:hover {
            background: rgba(255,255,255,0.08);
            transform: translateY(-5px);
            border-color: var(--primary-accent);
        }
        .stat-label {
            width: 80px; height: 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: 900;
            color: #fff;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .stat-value span {
            font-size: 1rem;
            color: var(--success-color);
            background: rgba(16,185,129,0.2);
            padding: 2px 8px;
            border-radius: 20px;
        }
        .dash-chart {
            flex: 1;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            padding: 20px;
            gap: 15px;
        }
        .chart-bar {
            flex: 1;
            background: linear-gradient(0deg, var(--primary-accent), rgba(56,189,248,0.1));
            border-radius: 6px 6px 0 0;
            opacity: 0.9;
            transform-origin: bottom;
            animation: dashPulseBar 3s alternate infinite ease-in-out;
            box-shadow: 0 0 10px rgba(56,189,248,0.2);
        }
        .chart-bar:nth-child(1) { height: 30%; animation-delay: 0.0s; }
        .chart-bar:nth-child(2) { height: 50%; animation-delay: 0.2s; }
        .chart-bar:nth-child(3) { height: 40%; animation-delay: 0.4s; }
        .chart-bar:nth-child(4) { height: 70%; animation-delay: 0.6s; }
        .chart-bar:nth-child(5) { height: 60%; animation-delay: 0.8s; }
        .chart-bar:nth-child(6) { height: 90%; animation-delay: 1.0s; }
        .chart-bar:nth-child(7) { height: 80%; animation-delay: 1.2s; background: linear-gradient(0deg, var(--success-color), transparent); }
        
        @keyframes dashPulseBar {
            0% { transform: scaleY(0.85); opacity: 0.7; }
            100% { transform: scaleY(1.05); opacity: 1; }
        }
        @keyframes dashShimmer {
            0% { right: -100%; }
            100% { right: 200%; }
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 10px rgba(56,189,248,0.4); }
            100% { box-shadow: 0 0 25px rgba(56,189,248,0.8); }
        }
    </style>
</head>
<body>

    <img src="assets/ai_bg.png" alt="AI Background" class="hero-bg-img" loading="lazy" onerror="this.style.display='none'">

    <!-- Navbar -->
    <nav class="glass-panel" id="navbar">
        <a href="#" class="logo">
            <div class="logo-icon">⚡</div>
            مقاييس
        </a>
        <div style="display: flex; gap: 15px;">
            <a href="login.php" class="btn-nav">تسجيل الدخول</a>
            <a href="#pricing" class="btn-nav active">إنشاء حساب جديد</a>
        </div>
    
    </nav>

    <!-- Ads Banner Section -->
    <div class="ads-banner" id="ads-banner">
        <!-- Ads will be injected here -->
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">مُدعم بالذكاء الاصطناعي ✨</div>
            <h1>مستقبل إدارة الأعمال بين يديك.</h1>
            <p>مقاييس (MAGAIEES) هو الحل السحابي الأذكى والأسرع للتحكم بمبيعاتك، مخازنك، وحساباتك بدقة متناهية. صُمم للمؤسسات التي تبحث عن التفوق والتحكم المطلق.</p>
            <div style="display: flex; gap: 20px; justify-content: flex-start;">
                <a href="#pricing" class="btn-primary">
                    إنشاء حساب جديد
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
        <div class="hero-visual">
            <img src="assets/dashboard_mockup.png" alt="SaaS Dashboard Interface" loading="lazy" onerror="this.style.display='none'">
        </div>
    </section>

    <!-- Partners Marquee -->
    <section class="partners">
        <p>يثق بنا قادة الصناعة والأعمال</p>
        <div class="marquee-wrapper" id="partners-marquee">
            <a href="#" class="partner-logo"><img src="assets/logo_maqayis.png" alt="مقاييس" loading="lazy"> مقاييس</a>
            <a href="#" class="partner-logo"><img src="assets/logo_alasaad.png" alt="مصنع الأسعد" loading="lazy"> مصنع الأسعد</a>
            <a href="#" class="partner-logo"><img src="assets/logo_omega.png" alt="أوميغا" loading="lazy"> أوميغا</a>
            <a href="#" class="partner-logo"><img src="assets/logo_bok.png" alt="بنك الخرطوم" loading="lazy"> بنك الخرطوم</a>
            <a href="#" class="partner-logo"><img src="assets/logo_fcb.png" alt="بنك المزارع" loading="lazy"> بنك المزارع</a>
            <a href="#" class="partner-logo"><img src="assets/logo_maqayis.png" alt="مقاييس" loading="lazy"> مقاييس</a>
            <a href="#" class="partner-logo"><img src="assets/logo_alasaad.png" alt="مصنع الأسعد" loading="lazy"> مصنع الأسعد</a>
            <a href="#" class="partner-logo"><img src="assets/logo_omega.png" alt="أوميغا" loading="lazy"> أوميغا</a>
            <a href="#" class="partner-logo"><img src="assets/logo_bok.png" alt="بنك الخرطوم" loading="lazy"> بنك الخرطوم</a>
            <a href="#" class="partner-logo"><img src="assets/logo_fcb.png" alt="بنك المزارع" loading="lazy"> بنك المزارع</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">قوة لا محدودة، واجهة بسيطة</h2>
        <p class="section-subtitle">قمنا بدمج أعقد العمليات المحاسبية والإدارية في واجهات عصرية تتيح لك إنجاز المهام بضغطة زر وبدون أخطاء.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
                </div>
                <h3>إدارة مالية فائقة الدقة</h3>
                <p>تتبع كافة العمليات المالية من قيود يومية وتقارير أرباح وخسائر في الوقت الفعلي، مع شجرة حسابات مرنة تتكيف مع حجم عملك.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
                <h3>تحكم ذكي بالمخازن</h3>
                <p>مراقبة لحظية لحركة الأصناف، تحويلات ذكية بين المستودعات، وتنبيهات استباقية لنواقص المخزون لضمان استمرارية مبيعاتك.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
                <h3>مبيعات سريعة ومتكاملة</h3>
                <p>نظام نقاط بيع (POS) سريع جداً، إصدار فواتير إلكترونية متوافقة، وإدارة عروض الأسعار والعملاء بسهولة تامة.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>أمان سيبراني متقدم</h3>
                <p>بنية تحتية مشفرة بالكامل، نظام صلاحيات دقيق (Role-Based)، وسجل مراجعة (Audit Log) يراقب ويسجل كل حركة على النظام.</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <h2 class="section-title">اختر الباقة المناسبة لطموحك</h2>
        <p class="section-subtitle">خطط تسعير مرنة تناسب الشركات الناشئة والمؤسسات الكبرى على حد سواء. ابدأ الآن واكتشف الفارق.</p>
        
        <div class="pricing-grid" id="pricing-grid">
            <div class="plan-card">
                <h3 class="plan-name">باقة الأساسية</h3>
                <div class="plan-price">50<span>$</span><span>/شهرياً</span></div>
                <ul class="plan-features">
                    <li>حتى 5 مستخدمين</li>
                    <li>مساحة تخزينية 10 GB</li>
                    <li>دعم فني وتحديثات مستمرة</li>
                    <li>نسخ احتياطي يومي تلقائي</li>
                </ul>
                <a href="register.php?plan=1" class="btn-nav" style="width:100%; text-align:center; background:rgba(255,255,255,0.05);">اختيار الباقة</a>
            </div>
            <div class="plan-card recommended">
                <div class="plan-badge">الأكثر شيوعاً</div>
                <h3 class="plan-name">باقة المتقدمة</h3>
                <div class="plan-price">120<span>$</span><span>/شهرياً</span></div>
                <ul class="plan-features">
                    <li>حتى 20 مستخدمين</li>
                    <li>مساحة تخزينية 50 GB</li>
                    <li>دعم فني وتحديثات مستمرة</li>
                    <li>نسخ احتياطي يومي تلقائي</li>
                </ul>
                <a href="register.php?plan=2" class="btn-primary" style="width:100%; border-radius:12px;">اختيار الباقة</a>
            </div>
            <div class="plan-card">
                <h3 class="plan-name">باقة الشركات (VIP)</h3>
                <div class="plan-price">300<span>$</span><span>/شهرياً</span></div>
                <ul class="plan-features">
                    <li>حتى 100 مستخدمين</li>
                    <li>مساحة تخزينية 200 GB</li>
                    <li>دعم فني وتحديثات مستمرة</li>
                    <li>نسخ احتياطي يومي تلقائي</li>
                </ul>
                <a href="register.php?plan=3" class="btn-nav" style="width:100%; text-align:center; background:rgba(255,255,255,0.05);">اختيار الباقة</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 مؤسسة مقاييس التجارية. جميع الحقوق محفوظة. مُصمم بعناية ليقود التكنولوجيا.</p>
    </footer>

    <script>
        // Navbar blur effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.style.background = 'rgba(3, 7, 18, 0.9)';
            } else {
                nav.style.background = 'rgba(17, 24, 39, 0.7)';
            }
        });

        // Load Public Data
        async function loadPublicData() {
            try {
                const res = await fetch('/api_index.php?r=public&_=' + Date.now());
                if (!res.ok) throw new Error('HTTP error ' + res.status);
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
                
                // Ads Logic
                if (data && data.ads && data.ads.length > 0) {
                    const activeAds = data.ads.filter(ad => ad.active);
                    if (activeAds.length > 0) {
                        const banner = document.getElementById('ads-banner');
                        if (banner) {
                            banner.style.display = 'block';
                            
                            activeAds.forEach((ad, index) => {
                                const slide = document.createElement('div');
                                slide.className = 'ad-slide' + (index === 0 ? ' active' : '');
                                const detailsHtml = ad.companyName ? `<div class="ad-badge">إعلان: ${ad.companyName}</div>` : '';
                                slide.innerHTML = `<img src="${ad.image}" alt="إعلان">${detailsHtml}`;
                                banner.appendChild(slide);
                            });

                            if (activeAds.length > 1) {
                                let currentAdIndex = 0;
                                const slides = banner.querySelectorAll('.ad-slide');
                                setInterval(() => {
                                    slides[currentAdIndex].classList.remove('active');
                                    currentAdIndex = (currentAdIndex + 1) % slides.length;
                                    slides[currentAdIndex].classList.add('active');
                                }, 5000);
                            }
                        }
                    }
                }

                // Render Partners
                const pWrapper = document.getElementById('partners-marquee');
                if(data && data.partners && data.partners.length > 0) {
                    let html = '';
                    const renderLogos = () => {
                        data.partners.forEach(p => {
                            if (p.active === false) return;
                            html += `<div class="partner-logo" title="${p.name}" style="display:inline-flex; align-items:center; gap:8px;">
                                <img src="${p.logo}" alt="${p.name}" style="height:35px; border-radius:4px;"> ${p.name}
                            </div>`;
                        });
                    };
                    renderLogos();
                    renderLogos(); // Duplicate for infinite scroll effect
                    pWrapper.innerHTML = html;
                } else {
                    if(pWrapper) pWrapper.parentElement.style.display = 'none';
                }

                // Render Plans
                const grid = document.getElementById('pricing-grid');
                if(data && data.plans && data.plans.length > 0) {
                    grid.innerHTML = '';
                    // Filter out suspended plans
                    const activePlans = data.plans.filter(p => p.status !== 'suspended');
                    
                    // وضع الباقة التجريبية في الأول
                    const normalPlans = activePlans.filter(p => !(p.isTrial || p.name.includes('تجريب')));
                    const trialPlans = activePlans.filter(p => p.isTrial || p.name.includes('تجريب'));
                    const sortedPlans = [...trialPlans, ...normalPlans];

                    sortedPlans.forEach((plan, index) => {
                        const isRecommended = plan.name.includes('متقدمة');
                        const isTrialPlan = plan.isTrial || plan.name.includes('تجريب');
                        const badgeHtml = isRecommended ? '<div class="plan-badge">الأكثر شيوعاً</div>' : (isTrialPlan ? '<div class="plan-badge" style="background:#10b981;">باقة تجريبية</div>' : '');
                        const btnClass = isRecommended ? 'btn-primary' : 'btn-nav';
                        const btnStyle = isRecommended ? 'width: 100%; border-radius: 12px;' : 'width: 100%; text-align:center; background:rgba(255,255,255,0.05);';
                        
                        grid.innerHTML += `
                            <div class="plan-card ${isRecommended ? 'recommended' : ''}">
                                ${badgeHtml}
                                <h3 class="plan-name">${plan.name}</h3>
                                <div class="plan-price">${isTrialPlan ? 'مجاناً' : plan.price + '<span>$</span>'}<span style="font-size:0.9rem;opacity:0.8;display:block;">${plan.duration ? 'لمدة ' + plan.duration + ' يوماً' : 'شهرياً'}</span></div>
                                <ul class="plan-features">
                                    <li>حتى ${plan.maxUsers} مستخدمين</li>
                                    <li>مساحة تخزينية ${plan.storage} GB</li>
                                    <li>دعم فني وتحديثات مستمرة</li>
                                    <li>نسخ احتياطي يومي تلقائي</li>
                                </ul>
                                <a href="register.php?plan=${plan.id}" class="${btnClass}" style="${btnStyle}">اختيار الباقة</a>
                            </div>
                        `;
                    });
                } else {
                    if(grid) grid.innerHTML = '<div style="text-align:center; width:100%;">لا توجد باقات متاحة حالياً.</div>';
                }

            } catch (e) {
                console.error("Failed to load public data", e);
                const grid = document.getElementById('pricing-grid');
                if(grid) grid.innerHTML = '<div style="text-align:center; width:100%; color:red;">حدث خطأ أثناء تحميل البيانات: ' + e.message + '</div>';
                const pWrapper = document.getElementById('partners-marquee');
                if(pWrapper) pWrapper.innerHTML = '<div style="text-align:center; width:100%; color:red;">خطأ: ' + e.message + '</div>';
            }
        }

        loadPublicData();
        
        // Register PWA service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
                .then(reg => console.log('Service Worker registered successfully:', reg.scope))
                .catch(err => console.error('Service Worker registration failed:', err));
        }
    </script>

    <!-- Animation JS -->
    <script>
        function animateValue(id, start, end, duration) {
            if (start === end) return;
            const obj = document.getElementById(id);
            if(!obj) return;
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const current = Math.floor(progress * (end - start) + start);
                obj.innerHTML = current.toLocaleString('en-US');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    // Reverse animation
                    setTimeout(() => animateValue(id, end, start, duration), 2000);
                }
            };
            window.requestAnimationFrame(step);
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            animateValue("anim-val-1", 15000, 24500, 3000);
            animateValue("anim-val-2", 342, 512, 2500);
            animateValue("anim-val-3", 89, 98, 4000);
        });
    </script>
</body>
</html>

