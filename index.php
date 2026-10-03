<?php
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isMobile = (bool)preg_match('/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini)/i', $ua);
$forceDesktop = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';

if ($isMobile && !$forceDesktop) {
    header('Location: /mobile_erp_global_suite.html');
    exit;
}

$page_title = 'مقاييس - أذكى نظام سحابي لإدارة الموارد (ERP)';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" href="manifest.json?v=2026_m_v5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="مقاييس ERP">
    <link rel="apple-touch-icon" href="assets/apple-touch-icon.png?v=2026_m_v5">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="256x256" href="assets/icon-256.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="384x384" href="assets/icon-384.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png?v=2026_m_v5">
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=2026_m_v5">
    <link rel="shortcut icon" href="assets/favicon.ico?v=2026_m_v5">
    <meta name="theme-color" content="#030712">
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

        .glass-panel { background: var(--surface-color); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--glass-border); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); }

        nav { position: sticky; top: 0; width: 100%; z-index: 1000; display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 5%; border-bottom: 1px solid var(--glass-border); transition: all 0.3s ease; }
        .logo { font-size: 1.6rem; font-weight: 900; display: flex; align-items: center; gap: 12px; color: #fff; text-decoration: none; }
        .logo-icon { background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 4px 20px rgba(59,130,246, 0.4); }

        .btn-nav { background: rgba(255,255,255,0.05); color: #fff; padding: 0.6rem 1.8rem; border-radius: 10px; text-decoration: none; font-weight: 700; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s; cursor: pointer;}
        .btn-nav.active { background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); border:none; box-shadow: 0 0 20px rgba(59,130,246,0.5); }
        .btn-nav:hover { transform: translateY(-2px); }

        /* Hero */
        .hero { position: relative; min-height: calc(100vh - 350px); display: flex; align-items: center; padding: 50px 5% 50px; overflow: hidden; }
        .hero-bg-img { position: absolute; top: 0; right: 0; width: 100%; height: 100%; background: radial-gradient(circle at center, rgba(59,130,246,0.15) 0%, transparent 70%); z-index: -1; }
        
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
        /* Features Section with Premium Background Effects */
        .features {
            padding: 100px 5%;
            position: relative;
            background-color: #060b1e;
            overflow: hidden;
            background-image: 
                radial-gradient(circle at 15% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(139, 92, 246, 0.1) 0%, transparent 45%);
        }
        
        .features-grid-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            background-position: center top;
            mask-image: radial-gradient(circle at center, black 40%, transparent 95%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 95%);
            z-index: 1;
            pointer-events: none;
        }
        
        .features-glow-line {
            display: none; /* Disabled for performance */
        }
        
        @keyframes lineFlow {
            0% { transform: translate(-30%, -150px) rotate(-12deg); }
            100% { transform: translate(30%, 150px) rotate(-12deg); }
        }

        .section-title { text-align: center; font-size: 3rem; font-weight: 900; margin-bottom: 20px; color: #fff; z-index: 2; position: relative; }
        .section-subtitle { text-align: center; color: var(--text-secondary); font-size: 1.2rem; max-width: 600px; margin: 0 auto 60px; line-height: 1.6; z-index: 2; position: relative; }
        
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; max-width: 1300px; margin: 0 auto; z-index: 3; position: relative; }
        .feature-card { padding: 40px 30px; border-radius: 24px; transition: all 0.4s; position: relative; overflow: hidden; background: linear-gradient(180deg, rgba(30,41,59,0.7), rgba(15,23,42,0.85)); border: 1px solid rgba(255,255,255,0.05); }
        .feature-card:hover { transform: translateY(-10px); border-color: var(--primary-accent); box-shadow: 0 20px 40px rgba(0,0,0,0.5), inset 0 0 30px rgba(59,130,246,0.05); }
        .feature-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 25px; background: rgba(59,130,246,0.1); color: var(--primary-accent); border: 1px solid rgba(59,130,246,0.2); }
        .feature-card h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 15px; color: #fff; }
        .feature-card p { color: var(--text-secondary); line-height: 1.7; font-size: 1rem; }

        /* Pricing Section */
        .pricing { 
            padding: 100px 5%; 
            position: relative; 
            background-color: #060b1e; 
            overflow: hidden;
            background-image: 
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 45%),
                radial-gradient(circle at 20% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 45%);
            border-top: 1px solid rgba(255, 255, 255, 0.03);
        }
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; max-width: 1400px; margin: 0 auto; z-index: 3; position: relative; }
        .plan-card { 
            padding: 50px 40px; 
            border-radius: 30px; 
            text-align: center; 
            transition: all 0.4s; 
            position: relative; 
            background: linear-gradient(180deg, rgba(30,41,59,0.7), rgba(15,23,42,0.85)); 
            border: 2px solid var(--glass-border); 
            display: flex; 
            flex-direction: column; 
            z-index: 2;
        }
        .plan-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,0,0,0.5); border-color: var(--primary-accent); }
        .plan-name { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 10px; }
        .plan-price { font-size: 3.5rem; font-weight: 900; color: var(--primary-accent); margin-bottom: 5px; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; }
        .plan-price span { font-size: 1.2rem; color: var(--text-secondary); font-weight: 500; margin-top: 15px; margin-right: 5px; }
        .plan-features { list-style: none; margin: 30px 0; padding: 0; text-align: right; flex-grow: 1; }
        .plan-features li { margin-bottom: 15px; color: #cbd5e1; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
        .plan-features li::before { content: '✓'; color: var(--success-color); font-weight: 900; font-size: 1.2rem; }
        .plan-btn { width: 100%; padding: 15px; border-radius: 15px; font-weight: 800; font-size: 1.1rem; border: none; cursor: pointer; transition: all 0.3s; margin-top: auto; text-decoration: none; display: block; }
        
        /* Recommended Plan styling */
        .plan-card.recommended { background: linear-gradient(180deg, rgba(30,41,59,0.95), rgba(15,23,42,0.98)); border: 2px solid var(--primary-accent); transform: scale(1.05); }
        .plan-card.recommended:hover { transform: scale(1.05) translateY(-10px); }
        .plan-badge { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); color: #fff; padding: 5px 20px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; letter-spacing: 1px; }

        /* Shooting Star animation */
        .shooting-star {
            position: absolute;
            width: 120px;
            height: 2px;
            background: linear-gradient(90deg, rgba(255,255,255,0.9), rgba(59, 130, 246, 0.4), transparent);
            filter: drop-shadow(0 0 6px var(--primary-accent));
            opacity: 0;
            z-index: 1;
            pointer-events: none;
        }
        .shooting-star { display: none; }
        .shooting-star-purple { display: none; }
        .shooting-star-green { display: none; }
        
        .shooting-star:nth-child(1) { display: none; }
        .shooting-star:nth-child(2) { display: none; }
        .shooting-star:nth-child(3) { display: none; }
        .shooting-star:nth-child(4) { display: none; }

        @keyframes meteor {
            0% { opacity: 0; }
            100% { opacity: 0; }
        }

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
    
        /* Ads Banner (10:1 Ratio - 3840x384 4K UHD / 1920x192 FHD) */
        .ads-banner {
            width: 100%;
            height: auto;
            min-height: 180px;
            max-height: 380px;
            aspect-ratio: 10 / 1;
            margin-top: 0;
            position: relative;
            overflow: hidden;
            display: none;
            background: linear-gradient(135deg, rgba(9, 27, 54, 0.95), rgba(3, 7, 18, 0.98));
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
            background: radial-gradient(circle at center, rgba(30, 41, 59, 0.5), rgba(3, 7, 18, 0.9));
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
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.6));
            cursor: default;
        }
    
        /* Hero styling updates */
        .hero {
            position: relative;
            min-height: 85vh;
            display: flex;
            align-items: center;
            padding: 90px 5% 40px;
            overflow: hidden;
        }
        .hero-bg-img {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.15;
            z-index: -1;
            filter: saturate(1.5);
        }
        .hero-content {
            flex: 1.2;
            max-width: 650px;
            z-index: 2;
            text-align: right;
        }
        .hero-badge {
            display: inline-block;
            padding: 8px 18px;
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.25);
            border-radius: 30px;
            color: #60a5fa;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 20px rgba(59,130,246,0.1);
        }
        .hero h1 {
            font-size: 3.6rem;
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 20px;
            background: linear-gradient(to left, #fff, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 35px;
            line-height: 1.7;
        }

        /* Dynamic Dashboard Mockup Styling - Flat & Light Mode */
        .hero-visual {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 620px;
            margin: 0 auto;
            transform: none; /* Removed 3D rotation tilt */
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5));
        }
        .hero-visual:hover {
            transform: translateY(-8px); /* Hover elevates straight up */
            filter: drop-shadow(0 30px 60px rgba(0,0,0,0.6));
        }
        
        /* Light Mode mockup container */
        .dash-mockup {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            height: 390px;
            direction: rtl;
            position: relative;
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.6);
        }
        .dash-mockup::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--primary-accent), var(--secondary-accent));
        }
        
        /* Light Mode sidebar */
        .dash-sidebar {
            width: 60px;
            background: #f1f5f9;
            border-left: 1px solid rgba(0, 0, 0, 0.06);
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }
        .dash-icon {
            width: 34px;
            height: 34px;
            background: rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.04);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dash-icon:hover {
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            border-color: transparent;
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
            color: #fff;
        }
        
        /* Light Mode main panel */
        .dash-main {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-width: 0;
        }
        
        /* Light Mode header */
        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding-bottom: 12px;
        }
        .dash-header span {
            color: #0f172a;
        }
        .dash-user {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(59,130,246,0.3);
            animation: pulseGlow 2s infinite alternate;
        }
        
        /* Light Mode stats */
        .dash-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .dash-stat-card {
            background: #f8fafc;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 14px;
            padding: 12px 15px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .dash-stat-card:hover {
            background: #f1f5f9;
            border-color: var(--primary-accent);
            transform: translateY(-2px);
        }
        .stat-label-text {
            font-size: 0.8rem;
            color: #475569;
            font-weight: 600;
        }
        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }
        .stat-change {
            font-size: 0.75rem;
            color: #10b981;
            font-weight: 700;
        }
        
        /* Light Mode charts & content */
        .dash-content-area {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 15px;
            flex: 1;
            min-height: 0;
        }
        .dash-chart-container {
            background: #f8fafc;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 14px;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        .dash-chart {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            flex: 1;
            height: 120px;
        }
        .chart-bar-wrapper {
            flex: 1;
            height: 100%;
            display: flex;
            align-items: flex-end;
        }
        .chart-bar {
            width: 100%;
            background: linear-gradient(0deg, var(--primary-accent), var(--secondary-accent));
            border-radius: 5px;
            transition: height 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 10px;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.1);
        }
        
        /* Clean Light terminal style for AI Logs */
        .dash-ai-logs {
            background: #f8fafc;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 14px;
            padding: 15px;
            font-family: 'Inter', 'Tajawal', monospace;
            font-size: 0.75rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .logs-wrapper {
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow: hidden;
            flex: 1;
        }
        .log-line {
            color: #2563eb;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            animation: typing 0.5s ease;
            font-weight: 500;
        }
        
        .dash-status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success-color);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px var(--success-color);
            animation: statusPulse 1.5s infinite alternate;
        }
        
        /* Floating/Popping glassmorphic elements in light mode */
        .floating-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15), 0 0 1px rgba(0,0,0,0.1);
            z-index: 10;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        
        /* Four floating items popping out of the screen */
        .card-left {
            bottom: 40px;
            right: -60px;
            animation: floatLeft 6s ease-in-out infinite alternate;
        }
        .card-right {
            top: 60px;
            left: -60px;
            animation: floatRight 6s ease-in-out infinite alternate;
        }
        .card-whatsapp {
            top: -25px;
            right: 40px;
            animation: floatTop 7s ease-in-out infinite alternate;
        }
        .card-users {
            bottom: -20px;
            left: 20px;
            animation: floatBottom 5s ease-in-out infinite alternate;
        }

        /* Float Animations */
        @keyframes floatLeft {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(-12px) scale(1.03); }
        }
        @keyframes floatRight {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(12px) scale(1.03); }
        }
        @keyframes floatTop {
            0% { transform: translateX(0px) translateY(0px); }
            100% { transform: translateX(8px) translateY(-10px); }
        }
        @keyframes floatBottom {
            0% { transform: translateX(0px) translateY(0px); }
            100% { transform: translateX(-8px) translateY(10px); }
        }
        @keyframes statusPulse {
            0% { opacity: 0.5; box-shadow: 0 0 4px var(--success-color); }
            100% { opacity: 1; box-shadow: 0 0 12px var(--success-color); }
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 8px rgba(59,130,246,0.3); }
            100% { box-shadow: 0 0 18px rgba(59,130,246,0.6); }
        }

        /* Footer Style Updates */
        footer {
            padding: 80px 5% 40px;
            background: linear-gradient(180deg, #020617 0%, #090d16 100%);
            border-top: 1px solid var(--glass-border);
            color: var(--text-primary);
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 40px;
            max-width: 1300px;
            margin: 0 auto 60px;
            text-align: right;
        }
        .footer-col h4 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 25px;
            color: #fff;
            position: relative;
            padding-bottom: 10px;
        }
        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-accent), var(--secondary-accent));
        }
        .footer-col p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-links li {
            margin-bottom: 12px;
        }
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s;
            display: inline-block;
        }
        .footer-links a:hover {
            color: var(--primary-accent);
            transform: translateX(-5px);
        }
        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-contact li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 15px;
        }
        .footer-contact a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
            font-family: 'Inter', 'Tajawal', sans-serif;
            direction: ltr;
            display: inline-block;
        }
        .footer-contact a:hover {
            color: var(--primary-accent);
        }
        .footer-contact svg {
            color: var(--primary-accent);
            flex-shrink: 0;
        }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 30px;
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .footer-bottom p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .footer-bottom-links {
            display: flex;
            gap: 25px;
        }
        .footer-bottom-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .footer-bottom-links a:hover {
            color: var(--primary-accent);
        }
        
        /* Modal Style for Privacy Policy */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(3, 7, 18, 0.85);
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding: 20px;
        }
        .modal.show {
            display: flex;
            opacity: 1;
        }
        .modal-content {
            background: #0f172a;
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 24px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
            color: var(--text-primary);
            text-align: right;
        }
        .modal.show .modal-content {
            transform: translateY(0);
        }
        .close-modal {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 30px;
            font-weight: bold;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.3s;
            line-height: 1;
        }
        .close-modal:hover {
            color: #fff;
        }
        .modal-content h3 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 25px;
            color: #fff;
            background: linear-gradient(to left, #fff, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .modal-body {
            max-height: 400px;
            overflow-y: auto;
            padding-left: 10px;
        }
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }
        .modal-body::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.02);
        }
        .modal-body::-webkit-scrollbar-thumb {
            background: var(--glass-border);
            border-radius: 3px;
        }
        .modal-body p {
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 1rem;
        }
        .modal-body h4 {
            color: #fff;
            margin: 20px 0 10px;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
            .footer-bottom-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <div class="hero-bg-img"></div>

    <!-- Ads Banner Section -->
    <div class="ads-banner" id="ads-banner">
        <!-- Ads will be injected here -->
    </div>

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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">مدعوم بالذكاء الاصطناعي لمساعدة إدارة الأعمال بين يديك ✨</div>
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
            <div class="dash-mockup">
                <!-- Sidebar -->
                <div class="dash-sidebar">
                    <div class="dash-icon" style="background: var(--primary-accent); color: #fff;">📊</div>
                    <div class="dash-icon">📦</div>
                    <div class="dash-icon">🧾</div>
                    <div class="dash-icon">👥</div>
                    <div class="dash-icon">⚙️</div>
                </div>
                
                <!-- Main Panel -->
                <div class="dash-main">
                    <!-- Top Bar -->
                    <div class="dash-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="dash-status-dot"></span>
                            <span style="font-size: 0.85rem; color: #0f172a; font-weight: 700;">المحاسب الآلي نشط</span>
                        </div>
                        <div class="dash-user"></div>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="dash-stats">
                        <div class="dash-stat-card">
                            <span class="stat-label-text">المبيعات الحالية</span>
                            <div class="stat-value" style="color: #0f172a;"><span id="anim-val-1">0</span> ر.ع</div>
                            <span class="stat-change font-inter">▲ +12.4%</span>
                        </div>
                        <div class="dash-stat-card">
                            <span class="stat-label-text">الفواتير النشطة</span>
                            <div class="stat-value" style="color: #0f172a;"><span id="anim-val-2">0</span></div>
                            <span class="stat-change font-inter" style="color: var(--primary-accent);">مكتملة آلياً</span>
                        </div>
                        <div class="dash-stat-card">
                            <span class="stat-label-text">كفاءة النظام</span>
                            <div class="stat-value" style="color: #0f172a;"><span id="anim-val-3">0</span>%</div>
                            <span class="stat-change font-inter" style="color: var(--secondary-accent);">مدعوم بالـ AI</span>
                        </div>
                    </div>
                    
                    <!-- Live Activity & Chart -->
                    <div class="dash-content-area">
                        <!-- Chart Container -->
                        <div class="dash-chart-container">
                            <span class="stat-label-text" style="margin-bottom: 10px; display: block; color: #475569;">مراقبة الأداء المالي (مباشر)</span>
                            <div class="dash-chart">
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-1" style="height:50%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-2" style="height:70%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-3" style="height:40%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-4" style="height:80%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-5" style="height:60%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-6" style="height:90%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-7" style="height:85%;"></div></div>
                            </div>
                        </div>
                        
                        <!-- AI Terminal Logs -->
                        <div class="dash-ai-logs">
                            <span class="stat-label-text" style="margin-bottom: 8px; display: block; color: var(--secondary-accent);">سجل ذكاء الأعمال</span>
                            <div class="logs-wrapper" id="logs-container">
                                <div class="log-line">🤖 تحليل المبيعات وتوقع فائض الأرباح...</div>
                                <div class="log-line">📦 جرد المستعد B: تم تنبيه الموزّع.</div>
                                <div class="log-line">🧾 ترحيل فاتورة المبيعات #10928 لدفتر الأستاذ.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Floating Decorative Glass Cards (Emerging/Popping out of screen) -->
            <div class="floating-card card-left">
                <div style="font-size: 1.3rem;">🧠</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; color: #475569;">توصية الذكاء الاصطناعي</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">زيادة مخزون الصنف A</div>
                </div>
            </div>
            <div class="floating-card card-right">
                <div style="font-size: 1.3rem; color: var(--success-color);">✓</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; color: #475569;">موازنة تلقائية</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">مطابقة الحسابات المالية</div>
                </div>
            </div>
            <div class="floating-card card-whatsapp">
                <div style="font-size: 1.3rem; color: #10b981;">💬</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; color: #475569;">ربط الواتساب</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">نشط ويرسل تلقائياً</div>
                </div>
            </div>
            <div class="floating-card card-users">
                <div style="font-size: 1.3rem; color: #3b82f6;">👥</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; color: #475569;">المستخدمين الآن</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a;"><span id="active-users-count">18</span> موظف نشط</div>
                </div>
            </div>
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
        <!-- Decorative Premium Background Effects -->
        <div class="features-grid-overlay"></div>
        <div class="features-glow-line"></div>
        
        <!-- Shooting Stars -->
        <div class="shooting-star"></div>
        <div class="shooting-star shooting-star-purple"></div>
        <div class="shooting-star shooting-star-green"></div>
        <div class="shooting-star"></div>

        <h2 class="section-title">قوة لا محدودة، واجهة بسيطة</h2>
        <p class="section-subtitle">قمنا بدمج أعقد العمليات المحاسبية والإدارية في واجهات عصرية تتيح لك إنجاز المهام بضغطة زر وبدون أخطاء.</p>
        
        <div class="features-grid">
            <!-- Feature 1: Financials -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
                </div>
                <h3>إدارة مالية فائقة الدقة</h3>
                <p>تتبع كافة العمليات المالية من قيود يومية وتقارير أرباح وخسائر في الوقت الفعلي، مع شجرة حسابات مرنة تتكيف مع حجم عملك.</p>
            </div>

            <!-- Feature 2: AI Audit -->
            <div class="feature-card" style="border-color: rgba(59, 130, 246, 0.35);">
                <div class="feature-icon" style="background: rgba(139, 92, 246, 0.15); color: var(--secondary-accent); border-color: rgba(139, 92, 246, 0.3);">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/><path d="M9 12h6"/></svg>
                </div>
                <h3 style="background: linear-gradient(135deg, #fff, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">مراجع حسابات بالذكاء الاصطناعي</h3>
                <p>نظام مراجعة وتدقيق مستقل يعمل بالتعلم الآلي، يفحص القيود ويكتشف الفجوات ويطابق الأرصدة البنكية تلقائياً لضمان سلامة قوائمك.</p>
            </div>

            <!-- Feature 3: Warehouses -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
                <h3>تحكم ذكي بالمخازن</h3>
                <p>مراقبة لحظية لحركة الأصناف، تحويلات ذكية بين المستودعات، وتنبيهات استباقية لنواقص المخزون لضمان استمرارية مبيعاتك.</p>
            </div>

            <!-- Feature 4: POS & Sales -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
                <h3>مبيعات ونقاط بيع ذكية</h3>
                <p>نظام نقاط بيع POS سريع وسهل، إصدار فواتير إلكترونية متوافقة ضريبياً، وإدارة عروض الأسعار والعملاء بكل سلاسة.</p>
            </div>

            <!-- Feature 5: AI Analytics -->
            <div class="feature-card">
                <div class="feature-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                </div>
                <h3>تقارير ذكاء الأعمال الاستباقي</h3>
                <p>تحليلات رسومية تفاعلية تتوقع الأرباح والخسائر المستقبلية وتدرس سلوك المبيعات لمنحك رؤية واضحة تدعم قراراتك الاستراتيجية.</p>
            </div>

            <!-- Feature 6: WhatsApp & Payment APIs -->
            <div class="feature-card">
                <div class="feature-icon" style="color: #06b6d4; background: rgba(6, 182, 212, 0.1);">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <h3>ربط الواتساب وبوابات الدفع</h3>
                <p>أرسل الفواتير وتنبيهات الدفع تلقائياً للعملاء عبر الواتساب، مع ربط مباشر مع بوابات الدفع الإلكتروني المحلية والخدمات البنكية.</p>
            </div>

            <!-- Feature 7: HR & Automated Payroll -->
            <div class="feature-card">
                <div class="feature-icon" style="color: #f59e0b; background: rgba(245, 158, 17, 0.1);">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3>إدارة شؤون الموظفين والرواتب</h3>
                <p>حساب الرواتب، البدلات، الحوافز، والغياب أوتوماتيكياً تماشياً مع القوانين المحلية وترحيلها مباشرة لدفاتر الحسابات بضغطة زر.</p>
            </div>

            <!-- Feature 8: Cybersecurity -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>أمان سيبراني وبنية مشفرة</h3>
                <p>تشفير كامل للبيانات، إدارة دقيقة لصلاحيات المستخدمين (Role-Based)، وسجل تدقيق تفصيلي يراقب كافة التغييرات بدقة.</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <!-- Decorative Premium Background Effects -->
        <div class="features-grid-overlay"></div>
        
        <!-- Shooting Stars -->
        <div class="shooting-star"></div>
        <div class="shooting-star shooting-star-purple"></div>
        <div class="shooting-star shooting-star-green"></div>
        <div class="shooting-star"></div>

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
        <div class="footer-grid">
            <!-- Column 1: About -->
            <div class="footer-col">
                <a href="#" class="logo" style="margin-bottom: 20px;">
                    <div class="logo-icon">⚡</div>
                    مقاييس
                </a>
                <p>نظام مقاييس (MAGAIEES) هو منصة سحابية متكاملة لإدارة موارد المؤسسات (ERP)، صُمم خصيصاً لمساعدة الشركات على تنظيم حساباتها، مخازنها، مبيعاتها، وربطها بالذكاء الاصطناعي لتحقيق نمو ذكي ومستدام.</p>
            </div>
            
            <!-- Column 2: Our Company -->
            <div class="footer-col">
                <h4>شركتنا</h4>
                <ul class="footer-links">
                    <li><a href="#features">مميزات النظام</a></li>
                    <li><a href="#pricing">باقات الاشتراك</a></li>
                    <li><a href="#" id="open-about">من نحن</a></li>
                    <li><a href="#" id="open-vision">رؤيتنا وقيمنا</a></li>
                </ul>
            </div>
            
            <!-- Column 3: Brand Assets -->
            <div class="footer-col">
                <h4>الهوية البصرية</h4>
                <ul class="footer-links">
                    <li><a href="#" id="open-brand-assets">أصول العلامة التجارية</a></li>
                    <li><a href="#" id="open-logo-guide">دليل استخدام الشعار</a></li>
                    <li><a href="#" id="open-colors">مجموعة الألوان المعتمدة</a></li>
                    <li><a href="assets/logo_maqayis.png" download id="open-brand-pack">تحميل حزمة الهوية الرسمية</a></li>
                </ul>
            </div>
            
            <!-- Column 4: Contact Us -->
            <div class="footer-col">
                <h4>اتصل بنا</h4>
                <ul class="footer-contact">
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <a href="tel:0096894700105">+968 9470 0105</a>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <a href="mailto:magaiees00105@gmail.com">magaiees00105@gmail.com</a>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>سلطنة عمان</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2026 مؤسسة مقاييس التجارية. جميع الحقوق محفوظة.</p>
            <div class="footer-bottom-links">
                <a href="#" id="open-privacy">سياسة الخصوصية</a>
                <a href="#">شروط الخدمة</a>
                <a href="#">قانوني</a>
            </div>
        </div>
    </footer>

    <!-- Privacy Policy Modal -->
    <div class="modal" id="privacy-modal">
        <div class="modal-content">
            <span class="close-modal" id="close-privacy">&times;</span>
            <h3>قانون حماية الخصوصية</h3>
            <div class="modal-body">
                <p>مرحباً بكم في منصة <strong>مقاييس (MAGAIEES)</strong>. نحن نولي أقصى درجات الأهمية لحماية خصوصية بياناتكم وأمن معلوماتكم الشخصية والمالية.</p>
                
                <h4>1. جمع المعلومات واستخدامها</h4>
                <p>نقوم بجمع البيانات اللازمة لتقديم وإدارة الخدمات السحابية بفعالية، ويشمل ذلك معلومات الاتصال، بيانات الحساب الشخصي، وتفضيلات النظام. نضمن استخدام هذه البيانات فقط للأغراض التشغيلية وتحسين تجربة المستخدم.</p>
                
                <h4>2. حماية البيانات وأمنها</h4>
                <p>نطبق بروتوكولات أمان متطورة وتشفير كامل للبيانات (End-to-End Encryption) لمنع أي وصول غير مصرح به أو تسريب للمعلومات. بياناتكم تُخزن في مراكز بيانات سحابية آمنة ومحمية بصفة مستمرة.</p>
                
                <h4>3. مشاركة البيانات مع أطراف ثالثة</h4>
                <p>نحن لا نقوم ببيع, تأجير، أو مشاركة بياناتكم الخاصة أو التجارية مع أي جهة خارجية أو أطراف ثالثة لأغراض تسويقية مطلقاً، إلا في الحالات التي يتطلبها القانون أو بموافقتكم الصريحة.</p>
                
                <h4>4. التحديثات والتعديلات</h4>
                <p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر لتعكس التغييرات في ممارساتنا أو المتطلبات القانونية. سيتم إشعاركم بأي تغييرات جوهرية عبر المنصة.</p>
                
                <h4>5. التواصل معنا</h4>
                <p>إذا كانت لديكم أي استفسارات أو مخاوف بشأن سياسة الخصوصية أو معالجة بياناتكم، يسعدنا تواصلكم معنا مباشرة عبر البريد الإلكتروني <a href="mailto:magaiees00105@gmail.com" style="color:var(--primary-accent); text-decoration:none;">magaiees00105@gmail.com</a>.</p>
            </div>
        </div>
    </div>

    <!-- About Us & Innovations Modal -->
    <div class="modal" id="about-modal">
        <div class="modal-content">
            <span class="close-modal" id="close-about">&times;</span>
            <h3>من نحن وابتكارات مقاييس</h3>
            <div class="modal-body">
                <p>تأسست <strong>مؤسسة مقاييس التجارية</strong> برؤية طموحة تهدف لتمكين قطاع الأعمال في سلطنة عُمان والوطن العربي من خلال ابتكار حلول برمجية سحابية ذكية وموثوقة بالكامل. نحن لا نقدم مجرد برمجيات، بل نبني بيئات عمل متكاملة تقود المستقبل.</p>
                
                <h4>⚡ المحاسب الآلي (Automated Accountant)</h4>
                <p>يمثل المحاسب الآلي في مقاييس ثورة في طريقة إدارة الدفاتر المحاسبية. يقوم النظام تلقائياً وبشكل مستقل بـ:</p>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style-type:square; line-height:1.7;">
                    <li>تحليل الفواتير والمستندات واستخراج البيانات آلياً دون تدخل بشري.</li>
                    <li>توليد قيود اليومية المتوازنة فورياً وترحيلها لدفاتر الأستاذ والميزانية.</li>
                    <li>اكتشاف وتصحيح الأخطاء الحسابية وتطابق ميزان المراجعة بنسبة دقة تصل إلى 99.9%.</li>
                </ul>

                <h4>🧠 تقنيات الذكاء الاصطناعي (AI Core)</h4>
                <p>تم دمج الذكاء الاصطناعي في صميم نظام مقاييس ليقدم للشركات تحليلات تنبؤية فائقة الذكاء:</p>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style-type:square; line-height:1.7;">
                    <li><strong>التنبؤ بالتدفقات النقدية:</strong> تحليل سلوك الإيرادات والمصروفات للتنبؤ بالفترات المالية القادمة.</li>
                    <li><strong>توصيات خفض التكاليف:</strong> خوارزميات ذكية تقارن المصروفات وتقترح حلولاً ذكية لزيادة الهوامش الربحية.</li>
                    <li><strong>تحليل المبيعات:</strong> التعرف على سلوك العملاء وتوقع ذروة الطلب على المنتجات.</li>
                </ul>

                <h4>📦 نظام المستودعات والمخازن الذكي</h4>
                <p>نقدم لك حلاً متطوراً للمخازن يمنع تكدس البضائع أو نفادها:</p>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style-type:square; line-height:1.7;">
                    <li>مراقبة لحظية وتنبيهات أوتوماتيكية عند وصول الأصناف لـ "حد الطلب".</li>
                    <li>إدارة مرنة للتحويلات بين الفروع والمخازن المتعددة مع تتبع الباركود.</li>
                    <li>تحليل معدلات ركود ودوران البضائع لرفع كفاءة رأس المال المستثمر.</li>
                </ul>

                <h4>📊 التقارير المالية والإدارية الفورية</h4>
                <p>لا مزيد من الانتظار لنهاية الشهر أو العام المالي. بضغطة زر واحدة، يصدر النظام تقارير متوافقة مع المعايير الدولية (IFRS) وقوانين الضرائب المحلية، بما يشمل: قوائم الدخل، الميزانية العمومية، تقارير أداء المبيعات، والإقرارات الضريبية الجاهزة للتقديم.</p>
            </div>
        </div>
    </div>

    <!-- Our Vision & Values Modal -->
    <div class="modal" id="vision-modal">
        <div class="modal-content">
            <span class="close-modal" id="close-vision">&times;</span>
            <h3>رؤيتنا وقيمنا ومميزات مقاييس</h3>
            <div class="modal-body">
                <h4>🎯 رؤيتنا</h4>
                <p>أن نصبح الشريك الرقمي السحابي الأول والأكثر وثوقاً لكافة الشركات والمؤسسات في الشرق الأوسط، والمحرك الأساسي الذي يقود الأعمال التجارية نحو الكفاءة والنمو الذكي باستخدام الذكاء الاصطناعي دون تعقيد.</p>
                
                <h4>💎 قيمنا الراسخة</h4>
                <p>نعمل يومياً وفق مبادئ صارمة تضمن نجاح عملائنا:</p>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style-type:square; line-height:1.7;">
                    <li><strong>الدقة المتناهية:</strong> صفر أخطاء محاسبية بفضل الأتمتة المتقدمة.</li>
                    <li><strong>الأمن السيبراني:</strong> تشفير كامل لبياناتك وحساباتك وفق أعلى المعايير العالمية.</li>
                    <li><strong>الابتكار المستمر:</strong> مواكبة متواصلة للتحديثات والتكنولوجيا.</li>
                    <li><strong>الشفافية وسهولة الاستخدام:</strong> نظام محاسبي قوي بواجهات تفاعلية يفهمها الجميع.</li>
                </ul>

                <h4>💡 لماذا تختار مقاييس؟ (مميزات تجعل قرارك حاسماً)</h4>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style:none; line-height:1.8;">
                    <li>🔹 <strong>لا حاجة لخلفية محاسبية:</strong> صممنا النظام ليعمل باللغة العربية البسيطة وبخطوات مباشرة.</li>
                    <li>🔹 <strong>توفير الوقت والجهد بنسبة 80%:</strong> الأتمتة الكاملة تعني توفير مئات الساعات المهدرة في الإدخال اليدوي.</li>
                    <li>🔹 <strong>دعم فني استثنائي:</strong> فريق الدعم الفني متواجد لمساعدتك وحل استفساراتك على مدار الساعة.</li>
                    <li>🔹 <strong>تحديثات مجانية مستمرة:</strong> تماشياً مع القوانين الضريبية والتحديثات التقنية محلياً ودولياً.</li>
                    <li>🔹 <strong>مرونة التوسع:</strong> يتكيف النظام وينمو معك، سواء كنت تمتلك متجراً صغيراً أو سلسلة شركات كبرى.</li>
                </ul>
                
                <div style="text-align:center; margin-top:25px; padding:15px; background:rgba(59,130,246,0.1); border-radius:12px; border:1px solid rgba(59,130,246,0.2);">
                    <p style="color:#fff; font-weight:700; margin-bottom:10px;">انضم إلى أكثر من 500 شركة تدير أعمالها بذكاء مع مقاييس!</p>
                    <a href="#pricing" class="btn-primary" style="padding:8px 20px; font-size:1rem; border-radius:8px;" onclick="document.getElementById('vision-modal').classList.remove('show'); document.body.style.overflow='auto';">ابدأ تجربتك المجانية الآن</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Brand Assets & Logo Guidelines Modal -->
    <div class="modal" id="brand-modal">
        <div class="modal-content">
            <span class="close-modal" id="close-brand">&times;</span>
            <h3>الهوية البصرية وأصول العلامة التجارية</h3>
            <div class="modal-body">
                <p>تعبّر الهوية البصرية لنظام <strong>مقاييس (MAGAIEES)</strong> عن الاحترافية، السرعة، والابتكار التقني. يرجى اتباع الإرشادات التالية لضمان الحفاظ على اتساق العلامة التجارية في جميع المنصات والمطبوعات.</p>
                
                <h4>⚡ الشعار الرسمي ودلالاته</h4>
                <p>يمثل شعار الصاعقة ⚡ الكفاءة والذكاء في معالجة البيانات، ويعبر عن السرعة الفائقة والربط التقني المتكامل. يتكون الشعار من رمز الصاعقة المتدرج متبوعاً بكلمة "مقاييس" بخط عربي متوازن ومعاصر.</p>

                <h4>🎨 الألوان المعتمدة (Brand Colors)</h4>
                <p>تتكون هويتنا من لوحة ألوان دقيقة توازن بين الثقة والابتكار:</p>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:15px; margin:20px 0;">
                    <div style="background:#3b82f6; border-radius:12px; padding:15px; text-align:center; color:#fff; font-weight:bold;">
                        الأزرق التقني
                        <div style="font-size:0.85rem; font-weight:normal; margin-top:5px; font-family:monospace;">#3B82F6</div>
                    </div>
                    <div style="background:#8b5cf6; border-radius:12px; padding:15px; text-align:center; color:#fff; font-weight:bold;">
                        البنفسجي الذكي
                        <div style="font-size:0.85rem; font-weight:normal; margin-top:5px; font-family:monospace;">#8B5CF6</div>
                    </div>
                    <div style="background:#030712; border-radius:12px; padding:15px; text-align:center; color:#fff; font-weight:bold; border:1px solid rgba(255,255,255,0.1);">
                        الأسود الفاخر
                        <div style="font-size:0.85rem; font-weight:normal; margin-top:5px; font-family:monospace;">#030712</div>
                    </div>
                </div>

                <h4>⚠️ دليل استخدام الشعار وقواعد المنع</h4>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style-type:square; line-height:1.7;">
                    <li>يجب الحفاظ على "المساحة الآمنة" حول الشعار لمنع تداخله مع عناصر التصميم الأخرى.</li>
                    <li><strong>يُمنع منعاً باتاً:</strong> تمديد الشعار أو تغيير أبعاده بشكل طولي أو عرضي.</li>
                    <li><strong>يُمنع:</strong> تغيير الألوان الرسمية للشعار أو استخدام تدرجات ألوان غير معتمدة.</li>
                    <li>يُفضل استخدام الشعار الأبيض بالكامل على الخلفيات الداكنة للغاية، والشعار الملون على الخلفيات الفاتحة.</li>
                </ul>

                <h4>📥 تحميل حزمة الهوية الرسمية</h4>
                <p>الحزمة تحتوي على ملفات الشعار بدقة عالية بصيغ (PNG, SVG, PDF) بالإضافة إلى الخطوط الرسمية وملفات دليل الاستخدام الكامل.</p>
                <div style="text-align:center; margin-top:20px;">
                    <a href="assets/logo_maqayis.png" download class="btn-primary" style="padding:10px 25px; font-size:1.1rem; border-radius:10px; display:inline-flex; align-items:center; gap:8px; text-decoration:none;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        تحميل حزمة الهوية والشعار
                    </a>
                </div>
            </div>
        </div>
    </div>

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
                let data;
                try {
                    const res = await fetch('api_index.php?r=public&_=' + Date.now());
                    if (!res.ok) throw new Error('HTTP error ' + res.status);
                    const text = await res.text();
                    data = JSON.parse(text);
                } catch (fetchError) {
                    console.warn("Failed to fetch live public data, using default fallback data:", fetchError);
                    data = {
                        plans: [
                            {id: 4, name: "باقة تجريبية (مجانية)", price: "0", status: "active", isTrial: true, storage: "5", duration: "128", maxUsers: "3"},
                            {id: 1, name: "باقة الأساسية", price: "20", status: "active", isTrial: false, storage: "10", duration: "360", maxUsers: "5"},
                            {id: 2, name: "باقة المتقدمة", price: "80", status: "active", isTrial: false, storage: "50", duration: "360", maxUsers: "20"},
                            {id: 3, name: "باقة الشركات (VIP)", price: "100", status: "active", isTrial: false, storage: "200", duration: "360", maxUsers: "100"}
                        ],
                        partners: [
                            {id: 1, name: "مقاييس", logo: "https://ui-avatars.com/api/?name=%D9%85%D9%82%D8%A7%D9%8A%D9%8A%D8%B3&background=38bdf8&color=fff&size=100", link: "#"},
                            {id: 2, name: "مصنع الأسعد", logo: "https://ui-avatars.com/api/?name=%D9%85%D8%B5%D9%86%D8%B9+%D8%A7%D9%84%D8%A3%D8%B3%D8%B9%D8%AF&background=818cf8&color=fff&size=100", link: "#"},
                            {id: 3, name: "أوميغا", logo: "https://ui-avatars.com/api/?name=%D8%A3%D9%88%D9%85%D9%8A%D8%BA%D8%A7&background=10b981&color=fff&size=100", link: "#"},
                            {id: 4, name: "بنك الخرطوم", logo: "https://ui-avatars.com/api/?name=%D8%A8%D9%86%D9%83+%D8%A7%D9%84%D8%AE%D8%B1%D8%B7%D9%88%D9%85&background=f59e0b&color=fff&size=100", link: "#"},
                            {id: 5, name: "بنك المزارع", logo: "https://ui-avatars.com/api/?name=%D8%A8%D9%86%D9%83+%D8%A7%D9%84%D9%85%D8%B2%D8%A7%D8%B1%D8%B9&background=ef4444&color=fff&size=100", link: "#"}
                        ],
                        ads: [],
                        partners_enabled: true
                    };
                }
                
                // Ads Logic (Filtered by Active status and Validity timeframe)
                if (data && data.ads && data.ads.length > 0) {
                    const todayStr = new Date().toISOString().split('T')[0];
                    const activeAds = data.ads.filter(ad => {
                        if (!ad.active) return false;
                        if (ad.start_date && ad.start_date > todayStr) return false;
                        if (ad.end_date && ad.end_date < todayStr) return false;
                        return true;
                    });

                    if (activeAds.length > 0) {
                        const banner = document.getElementById('ads-banner');
                        if (banner) {
                            banner.style.display = 'block';
                            banner.innerHTML = '';
                            
                            activeAds.forEach((ad, index) => {
                                const slide = document.createElement('div');
                                slide.className = 'ad-slide' + (index === 0 ? ' active' : '');
                                const safeCompanyName = (typeof escapeHtml === 'function') ? escapeHtml(ad.companyName) : (ad.companyName || '');
                                const detailsHtml = ad.companyName ? `<div class="ad-badge">إعلان: ${safeCompanyName}</div>` : '';
                                const imgHtml = `<img src="${ad.image}" alt="${ad.companyName || 'إعلان'}" loading="lazy">${detailsHtml}`;
                                slide.innerHTML = ad.link ? `<a href="${ad.link}" target="_blank" rel="noopener noreferrer" style="display:block; width:100%; height:100%; text-decoration:none;">${imgHtml}</a>` : imgHtml;
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
                if(data && data.partners_enabled !== false && data.partners && data.partners.length > 0) {
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
                    pWrapper.parentElement.style.display = 'block';
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

                    // Center the plan card if there is only one plan
                    if (sortedPlans.length === 1) {
                        grid.style.display = 'flex';
                        grid.style.justifyContent = 'center';
                    } else {
                        grid.style.display = 'grid';
                    }

                    sortedPlans.forEach((plan, index) => {
                        const isRecommended = plan.name.includes('متقدمة') || sortedPlans.length === 1; // Highlight if it is the only plan
                        const isTrialPlan = plan.isTrial || plan.name.includes('تجريب');
                        
                        let badgeText = 'باقة متميزة';
                        if (isRecommended) badgeText = 'الأكثر شعبية';
                        if (isTrialPlan) badgeText = 'باقة تجريبية';
                        
                        const badgeHtml = `<div class="plan-badge" style="${isTrialPlan ? 'background:#10b981;' : ''}">${badgeText}</div>`;
                        const btnClass = isRecommended ? 'btn-primary' : 'btn-nav';
                        const btnStyle = isRecommended ? 'width: 100%; border-radius: 12px;' : 'width: 100%; text-align:center; background:rgba(255,255,255,0.05);';
                        
                        // Custom list of detailed features to show capabilities
                        const featuresList = [
                            `حتى ${plan.maxUsers} مستخدمين نشطين`,
                            `مساحة تخزينية سحابية ${plan.storage} GB`,
                            `المحاسب الآلي والذكاء الاصطناعي مفعل بالكامل`,
                            `مراجع الحسابات الذكي وتدقيق القيود تلقائياً`,
                            `ربط مباشر بالواتساب وتنبيهات الفواتير الفورية`,
                            `حفظ نسخ احتياطي يومي آمن ومشفر`,
                            `دعم فني وتحديثات مستمرة 24/7 مجاناً`
                        ];
                        
                        let featuresHtml = '';
                        featuresList.forEach(f => {
                            featuresHtml += `<li>${f}</li>`;
                        });
                        
                        grid.innerHTML += `
                            <div class="plan-card ${isRecommended ? 'recommended' : ''}" style="${sortedPlans.length === 1 ? 'max-width: 420px; width: 100%;' : ''}">
                                ${badgeHtml}
                                <h3 class="plan-name" style="font-size: 1.8rem; margin-bottom:15px;">${plan.name}</h3>
                                <div class="plan-price" style="font-size: 3.8rem;">${isTrialPlan ? 'مجاناً' : plan.price + '<span>$</span>'}<span style="font-size:0.95rem;opacity:0.8;display:block;margin-right:10px;">${plan.duration ? 'لمدة ' + plan.duration + ' يوماً' : 'شهرياً'}</span></div>
                                <ul class="plan-features" style="margin: 35px 0;">
                                    ${featuresHtml}
                                </ul>
                                <a href="register.php?plan=${plan.id}" class="${btnClass}" style="${btnStyle}">اشترك الآن واكتشف الفارق</a>
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
        // Live Dashboard Simulation
        function runLiveDashboard() {
            let sales = 15820;
            let invoices = 342;
            let activeUsers = 18;
            
            const salesEl = document.getElementById('anim-val-1');
            const invoicesEl = document.getElementById('anim-val-2');
            const efficiencyEl = document.getElementById('anim-val-3');
            const usersEl = document.getElementById('active-users-count');
            const logsContainer = document.getElementById('logs-container');
            
            // Set initial state
            if (salesEl) salesEl.innerText = sales.toLocaleString('en-US');
            if (invoicesEl) invoicesEl.innerText = invoices;
            if (efficiencyEl) efficiencyEl.innerText = "99.9";
            if (usersEl) usersEl.innerText = activeUsers;
            
            // Fluctuating values
            setInterval(() => {
                // Fluctuating sales
                const change = Math.floor(Math.random() * 120) - 30; // Mostly positive
                sales += change;
                if (salesEl) {
                    salesEl.innerText = sales.toLocaleString('en-US');
                    salesEl.style.color = change >= 0 ? '#10b981' : '#f43f5e';
                    setTimeout(() => salesEl.style.color = '#0f172a', 500); // Light Mode text color reset
                }
                
                // Active invoices increase occasionally
                if (Math.random() > 0.4) {
                    invoices += 1;
                    if (invoicesEl) {
                        invoicesEl.innerText = invoices;
                        invoicesEl.style.color = '#3b82f6';
                        setTimeout(() => invoicesEl.style.color = '#0f172a', 500); // Light Mode text color reset
                    }
                }
                
                // Active users count fluctuates
                if (Math.random() > 0.6 && usersEl) {
                    const userChange = Math.floor(Math.random() * 3) - 1; // -1, 0, +1
                    activeUsers = Math.max(10, Math.min(25, activeUsers + userChange));
                    usersEl.innerText = activeUsers;
                    usersEl.style.color = '#8b5cf6';
                    setTimeout(() => usersEl.style.color = '#0f172a', 500);
                }
                
                // Fluctuate efficiency slightly
                if (efficiencyEl) {
                    const eff = (99.8 + Math.random() * 0.15).toFixed(1);
                    efficiencyEl.innerText = Math.min(eff, 100);
                }
                
                // Randomize chart bars
                for (let i = 1; i <= 7; i++) {
                    const bar = document.getElementById(`bar-${i}`);
                    if (bar) {
                        const newHeight = Math.floor(Math.random() * 60) + 30; // 30% to 90%
                        bar.style.height = `${newHeight}%`;
                    }
                }
                
                // Push new AI log line
                if (Math.random() > 0.6 && logsContainer) {
                    const aiLogs = [
                        "🤖 تحليل التدفقات النقدية: وضع السيولة ممتاز.",
                        "🧾 قيد تلقائي: تم ترحيل مصروفات التشغيل.",
                        "📦 تحويل مخزني: 50 قطعة من مخزن 1 إلى مخزن 2.",
                        "🧠 تنبؤ: زيادة مبيعات التجزئة بنسبة 15% غداً.",
                        "👥 تحديث: المزامنة التلقائية مع البنك مكتملة.",
                        "💬 إشعار: تم إرسال فاتورة العميل عبر الواتساب."
                    ];
                    const randomLog = aiLogs[Math.floor(Math.random() * aiLogs.length)];
                    const logLine = document.createElement('div');
                    logLine.className = 'log-line';
                    logLine.innerText = randomLog;
                    logsContainer.appendChild(logLine);
                    
                    if (logsContainer.children.length > 3) {
                        logsContainer.removeChild(logsContainer.firstElementChild);
                    }
                }
            }, 2500);
        }

        document.addEventListener('DOMContentLoaded', runLiveDashboard);

        // Privacy Policy Modal Toggle
        function setupModal(modalId, triggerIds, closeId) {
            const modal = document.getElementById(modalId);
            const closeBtn = document.getElementById(closeId);
            if (!modal || !closeBtn) return;

            triggerIds.forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        modal.classList.add('show');
                        document.body.style.overflow = 'hidden';
                    });
                }
            });

            const closeModalFunc = () => {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            };

            closeBtn.addEventListener('click', closeModalFunc);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModalFunc();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    closeModalFunc();
                }
            });
        }

        // Setup all modals
        setupModal('privacy-modal', ['open-privacy'], 'close-privacy');
        setupModal('about-modal', ['open-about'], 'close-about');
        setupModal('vision-modal', ['open-vision'], 'close-vision');
        setupModal('brand-modal', ['open-brand-assets', 'open-logo-guide', 'open-colors'], 'close-brand');
    </script>
<script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
</body>
</html>

