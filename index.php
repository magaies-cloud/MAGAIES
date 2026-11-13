<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';

if (isset($_GET['logout']) || isset($_GET['login']) || isset($_GET['force_login'])) {
    if (isset($_GET['logout'])) {
        @setcookie('session_active', '', time() - 3600, '/');
        @setcookie('saas_token', '', time() - 3600, '/');
        @setcookie('authToken', '', time() - 3600, '/');
        @setcookie('mg_device_auth', '', time() - 3600, '/');
        @setcookie('currentTenantId', '', time() - 3600, '/');
        @setcookie('currentSubdomain', '', time() - 3600, '/');
    }
    header("Location: login.php{$qs}");
    exit;
}

if (isset($_GET['voucher']) || (isset($_GET['screen']) && $_GET['screen'] === 'voucher') || (isset($_GET['page']) && $_GET['page'] === 'voucher')) {
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

$reqSubdomain = strtolower(trim((string)($_GET['tenant'] ?? $_GET['subdomain'] ?? '')));
if ($reqSubdomain === '') {
    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    if ($host !== '' && !preg_match('/^[0-9.]+$/', $host) && $host !== 'localhost') {
        $parts = explode('.', $host);
        if (substr($host, -10) === '.localhost' && count($parts) >= 2 && $parts[0] !== 'www') {
            $reqSubdomain = $parts[0];
        } elseif (count($parts) > 2 && $parts[0] !== 'www') {
            $reqSubdomain = $parts[0];
        }
    }
}

if ($reqSubdomain !== '' && !isset($_GET['landing'])) {
    header("Location: login.php{$qs}");
    exit;
}

$page_title = 'السيطرة (MAGAIEES) - المنظومة السحابية العالمية لإدارة موارد المؤسسات والذكاء المحاسبي';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" href="manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#030712">
    <title><?php echo $page_title; ?></title>
    <style>
        :root {
            --bg-color: #020617;
            --bg-secondary: #070d1f;
            --surface-color: rgba(15, 23, 42, 0.72);
            --primary-accent: #38bdf8;
            --primary-deep: #2563eb;
            --secondary-accent: #8b5cf6;
            --gold-accent: #f59e0b;
            --emerald-accent: #10b981;
            --rose-accent: #f43f5e;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --success-color: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Tajawal', sans-serif; }
        html { scroll-behavior: smooth; }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            overflow-x: hidden;
            background-image:
                radial-gradient(circle at 12% 12%, rgba(56, 189, 248, 0.14), transparent 38%),
                radial-gradient(circle at 88% 18%, rgba(139, 92, 246, 0.14), transparent 40%),
                radial-gradient(circle at 50% 65%, rgba(16, 185, 129, 0.07), transparent 45%),
                radial-gradient(circle at 85% 90%, rgba(245, 158, 11, 0.10), transparent 38%);
            background-attachment: fixed;
        }

        /* Ambient Kinetic Orbs */
        .aurora-canvas {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }
        .aurora-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(95px);
            opacity: 0.28;
            animation: auroraFloat 18s ease-in-out infinite alternate;
        }
        .aurora-orb.orb-cyan { width: 520px; height: 520px; background: #0ea5e9; top: -120px; right: -100px; }
        .aurora-orb.orb-violet { width: 480px; height: 480px; background: #7c3aed; bottom: -140px; left: -100px; animation-delay: -6s; }
        .aurora-orb.orb-gold { width: 380px; height: 380px; background: #f59e0b; top: 42%; left: 38%; opacity: 0.14; animation-delay: -11s; }

        @keyframes auroraFloat {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(-35px, 25px, 0) scale(1.08); }
            100% { transform: translate3d(25px, -30px, 0) scale(0.96); }
        }

        /* Full-Bleed Top Executive Ads & Featured Partners Banner */
        .ads-banner-shell {
            width: 100%;
            margin: 0;
            padding: 0;
            display: none;
            position: relative;
            z-index: 950;
            background: #020617;
            border-bottom: 1px solid rgba(56, 189, 248, 0.32);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.55);
        }
        .ads-banner-overlay-bar {
            position: absolute;
            top: 14px;
            right: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
            pointer-events: none;
        }
        .ads-banner-title {
            pointer-events: auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 800;
            color: #fde047;
            background: rgba(2, 6, 23, 0.78);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(250, 204, 21, 0.4);
            padding: 5px 14px;
            border-radius: 999px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.5);
        }
        .ads-dots {
            pointer-events: auto;
            display: flex;
            gap: 6px;
            align-items: center;
            background: rgba(2, 6, 23, 0.65);
            backdrop-filter: blur(10px);
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .ads-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.35);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .ads-dot.active {
            width: 26px;
            background: linear-gradient(90deg, #38bdf8, #f59e0b);
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.75);
        }
        .ads-banner {
            width: 100%;
            min-height: 175px;
            max-height: 360px;
            aspect-ratio: 10 / 1;
            position: relative;
            overflow: hidden;
            display: none;
            border-radius: 0;
            background: linear-gradient(135deg, #0b132b, #020617);
            z-index: 900;
        }
        .ad-slide {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: scale(1.03);
            transition: opacity 0.9s ease-in-out, transform 1.2s ease;
            display: block;
        }
        .ad-slide.active {
            opacity: 1;
            transform: scale(1);
            z-index: 2;
        }
        .ad-badge {
            position: absolute;
            bottom: 14px;
            right: 20px;
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(12px);
            color: #f8fafc;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 800;
            border: 1px solid rgba(56, 189, 248, 0.45);
            z-index: 5;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.55);
        }
        .ad-badge::before {
            content: '★';
            color: #fbbf24;
        }
        .ad-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            image-rendering: -webkit-optimize-contrast;
        }

        /* Executive Promotional Highlights Showcase (Replaces Top Scrolling Ticker) */
        .exec-promo-showcase {
            max-width: 1380px;
            margin: 0 auto 30px;
            padding: 0 5%;
            position: relative;
            z-index: 12;
        }
        .exec-promo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(275px, 1fr));
            gap: 18px;
            padding: 18px;
            border-radius: 26px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(7, 13, 30, 0.92));
            border: 1px solid rgba(56, 189, 248, 0.22);
            box-shadow: 0 22px 50px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
        }
        .exec-promo-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.025);
            border: 1px solid rgba(255, 255, 255, 0.07);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: right;
            position: relative;
            overflow: hidden;
        }
        .exec-promo-card:hover {
            transform: translateY(-4px);
            background: rgba(56, 189, 248, 0.06);
            border-color: rgba(56, 189, 248, 0.35);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
        }
        .exec-promo-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--ep-bg, linear-gradient(135deg, #38bdf8, #2563eb));
            box-shadow: 0 10px 22px var(--ep-glow, rgba(56, 189, 248, 0.35)), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }
        .exec-promo-tag {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 9px;
            border-radius: 999px;
            margin-bottom: 4px;
            background: rgba(56, 189, 248, 0.14);
            color: #7dd3fc;
            border: 1px solid rgba(56, 189, 248, 0.28);
        }
        .exec-promo-text {
            font-size: 0.9rem;
            font-weight: 700;
            color: #f1f5f9;
            line-height: 1.5;
        }

        /* Glass Panel & Navbar */
        .glass-panel {
            background: var(--surface-color);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }

        nav {
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.35s ease;
        }
        .logo {
            font-size: 1.7rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .logo-3d-cube {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #38bdf8 0%, #2563eb 50%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow:
                0 8px 22px rgba(56, 189, 248, 0.45),
                inset 0 2px 3px rgba(255, 255, 255, 0.55),
                inset 0 -3px 6px rgba(0, 0, 0, 0.35);
            transform: perspective(300px) rotateY(-8deg) rotateX(6deg);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        .logo:hover .logo-3d-cube {
            transform: perspective(300px) rotateY(0deg) rotateX(0deg) scale(1.06);
            box-shadow: 0 12px 28px rgba(56, 189, 248, 0.65), inset 0 2px 4px rgba(255,255,255,0.7);
        }
        .logo-sub {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            color: #38bdf8;
            letter-spacing: 1.5px;
            margin-top: -4px;
            font-family: 'Inter', sans-serif;
        }

        .nav-center-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }
        .nav-center-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.98rem;
            transition: color 0.25s, transform 0.25s;
            position: relative;
        }
        .nav-center-links a:hover {
            color: #38bdf8;
            transform: translateY(-1px);
        }

        .btn-nav {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            padding: 0.68rem 1.6rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            border: 1px solid rgba(255, 255, 255, 0.14);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-nav.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 50%, #7c3aed 100%);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }
        .btn-nav:hover {
            transform: translateY(-2px);
            border-color: #38bdf8;
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.25);
        }

        /* Hero Section */
        .hero {
            position: relative;
            min-height: 88vh;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            padding: 65px 5% 60px;
            overflow: hidden;
        }
        .hero-bg-img {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 70% 30%, rgba(56, 189, 248, 0.16) 0%, transparent 55%),
                radial-gradient(circle at 25% 70%, rgba(139, 92, 246, 0.15) 0%, transparent 55%);
            z-index: -1;
        }

        .hero-content {
            flex: 1.15;
            max-width: 680px;
            z-index: 2;
            text-align: right;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.15), rgba(139, 92, 246, 0.15));
            border: 1px solid rgba(56, 189, 248, 0.38);
            border-radius: 999px;
            color: #7dd3fc;
            font-weight: 800;
            margin-bottom: 24px;
            font-size: 0.95rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 0 25px rgba(56, 189, 248, 0.18);
            animation: badgePulse 3s ease-in-out infinite alternate;
        }
        @keyframes badgePulse {
            0% { box-shadow: 0 0 15px rgba(56, 189, 248, 0.15); }
            100% { box-shadow: 0 0 30px rgba(139, 92, 246, 0.35); }
        }

        .hero h1 {
            font-size: 3.75rem;
            font-weight: 900;
            line-height: 1.18;
            margin-bottom: 22px;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .hero h1 .gradient-highlight {
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 50%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        .hero p {
            font-size: 1.22rem;
            color: #cbd5e1;
            margin-bottom: 32px;
            line-height: 1.8;
            font-weight: 500;
        }

        .hero-cta-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 38px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 50%, #7c3aed 100%);
            color: #fff;
            padding: 1.05rem 2.4rem;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 900;
            font-size: 1.15rem;
            border: 1px solid rgba(255, 255, 255, 0.25);
            cursor: pointer;
            box-shadow: 0 14px 32px rgba(37, 99, 235, 0.45), inset 0 1px 2px rgba(255, 255, 255, 0.45);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }
        .btn-primary:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 42px rgba(56, 189, 248, 0.55);
        }

        .btn-secondary-hero {
            background: rgba(15, 23, 42, 0.75);
            color: #f8fafc;
            padding: 1.05rem 2rem;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.08rem;
            border: 1px solid rgba(56, 189, 248, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .btn-secondary-hero:hover {
            background: rgba(56, 189, 248, 0.14);
            border-color: #38bdf8;
            transform: translateY(-3px);
        }

        /* Hero Live Enterprise Metrics Bar */
        .hero-metrics-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            padding: 16px;
            border-radius: 20px;
            background: rgba(15, 23, 42, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(14px);
        }
        .hero-metric-item {
            text-align: center;
            padding: 6px 8px;
            border-left: 1px solid rgba(255, 255, 255, 0.07);
        }
        .hero-metric-item:last-child { border-left: none; }
        .hero-metric-num {
            font-family: 'Inter', sans-serif;
            font-size: 1.35rem;
            font-weight: 900;
            background: linear-gradient(135deg, #38bdf8, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: block;
        }
        .hero-metric-label {
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 700;
            margin-top: 2px;
            display: block;
        }

        /* 3D Interactive Dashboard Showcase */
        .hero-visual {
            flex: 1;
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 630px;
            margin: 0 auto;
            perspective: 1200px;
        }
        .dash-mockup {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            overflow: hidden;
            display: flex;
            height: 410px;
            direction: rtl;
            position: relative;
            transform: rotateY(-8deg) rotateX(4deg);
            transition: transform 0.55s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.55s ease;
            box-shadow:
                -25px 30px 65px rgba(0, 0, 0, 0.65),
                0 0 45px rgba(56, 189, 248, 0.22),
                inset 0 0 20px rgba(255, 255, 255, 0.7);
        }
        .hero-visual:hover .dash-mockup {
            transform: rotateY(0deg) rotateX(0deg) translateY(-6px);
            box-shadow:
                0 35px 75px rgba(0, 0, 0, 0.7),
                0 0 60px rgba(56, 189, 248, 0.35);
        }
        .dash-mockup::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #38bdf8, #8b5cf6, #f59e0b, #10b981);
        }

        .dash-sidebar {
            width: 64px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        .dash-icon {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        }
        .dash-icon:hover, .dash-icon.active-3d {
            background: linear-gradient(135deg, #38bdf8, #6366f1);
            border-color: rgba(255,255,255,0.4);
            transform: translateY(-2px) scale(1.08);
            box-shadow: 0 8px 16px rgba(56, 189, 248, 0.4);
            color: #fff;
        }

        .dash-main {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            padding-bottom: 12px;
        }
        .dash-user {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #38bdf8, #8b5cf6);
            border-radius: 50%;
            box-shadow: 0 0 12px rgba(59,130,246,0.4);
            animation: pulseGlow 2s infinite alternate;
        }

        .dash-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .dash-stat-card {
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.07);
            border-radius: 16px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
            transition: all 0.3s ease;
        }
        .dash-stat-card:hover {
            border-color: #38bdf8;
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(56, 189, 248, 0.12);
        }
        .stat-label-text { font-size: 0.78rem; color: #475569; font-weight: 700; }
        .stat-value {
            font-size: 1.28rem;
            font-weight: 900;
            color: #0f172a;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .stat-change { font-size: 0.74rem; color: #10b981; font-weight: 800; }

        .dash-content-area {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 14px;
            flex: 1;
            min-height: 0;
        }
        .dash-chart-container {
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.07);
            border-radius: 16px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }
        .dash-chart {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            flex: 1;
            height: 120px;
        }
        .chart-bar-wrapper { flex: 1; height: 100%; display: flex; align-items: flex-end; }
        .chart-bar {
            width: 100%;
            background: linear-gradient(0deg, #2563eb, #38bdf8, #8b5cf6);
            border-radius: 6px;
            transition: height 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 12px;
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.25);
        }

        .dash-ai-logs {
            background: #0f172a;
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 16px;
            padding: 14px;
            font-size: 0.76rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: inset 0 0 20px rgba(56, 189, 248, 0.08);
        }
        .logs-wrapper {
            display: flex;
            flex-direction: column;
            gap: 9px;
            overflow: hidden;
            flex: 1;
        }
        .log-line {
            color: #38bdf8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.04);
        }

        .dash-status-dot {
            width: 9px;
            height: 9px;
            background-color: var(--success-color);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px var(--success-color);
            animation: statusPulse 1.5s infinite alternate;
        }

        /* 3D Floating Cards around Dashboard */
        .floating-card {
            position: absolute;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 18px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45), 0 0 20px rgba(56, 189, 248, 0.15);
            z-index: 10;
            pointer-events: none;
        }
        .floating-3d-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: inset 0 2px 3px rgba(255,255,255,0.45), 0 6px 14px rgba(0,0,0,0.35);
            flex-shrink: 0;
        }
        .card-left { bottom: 35px; right: -45px; animation: floatLeft 6s ease-in-out infinite alternate; }
        .card-right { top: 55px; left: -45px; animation: floatRight 6s ease-in-out infinite alternate; }
        .card-whatsapp { top: -24px; right: 35px; animation: floatTop 7s ease-in-out infinite alternate; }
        .card-users { bottom: -22px; left: 25px; animation: floatBottom 5s ease-in-out infinite alternate; }

        @keyframes floatLeft { 0% { transform: translateY(0px) scale(1); } 100% { transform: translateY(-12px) scale(1.03); } }
        @keyframes floatRight { 0% { transform: translateY(0px) scale(1); } 100% { transform: translateY(12px) scale(1.03); } }
        @keyframes floatTop { 0% { transform: translateX(0px) translateY(0px); } 100% { transform: translateX(8px) translateY(-10px); } }
        @keyframes floatBottom { 0% { transform: translateX(0px) translateY(0px); } 100% { transform: translateX(-8px) translateY(10px); } }
        @keyframes statusPulse { 0% { opacity: 0.5; box-shadow: 0 0 4px var(--success-color); } 100% { opacity: 1; box-shadow: 0 0 12px var(--success-color); } }
        @keyframes pulseGlow { 0% { box-shadow: 0 0 8px rgba(59,130,246,0.3); } 100% { box-shadow: 0 0 18px rgba(59,130,246,0.6); } }

        /* Marquee Partners */
        .partners {
            padding: 50px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            background: linear-gradient(90deg, rgba(2,6,23,0.9), rgba(15,23,42,0.7), rgba(2,6,23,0.9));
            overflow: hidden;
            position: relative;
        }
        .partners p {
            text-align: center;
            color: #94a3b8;
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 28px;
            letter-spacing: 0.5px;
        }
        .marquee-wrapper { display: flex; width: 200%; animation: scroll 30s linear infinite; gap: 45px; align-items: center; }
        .marquee-wrapper:hover { animation-play-state: paused; }
        .partner-logo {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 10px 22px;
            border-radius: 16px;
            transition: all 0.35s ease;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #f8fafc;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .partner-logo img { height: 42px; border-radius: 10px; }
        .partner-logo:hover {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.1);
            transform: translateY(-4px) scale(1.04);
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.2);
        }
        @keyframes scroll { 0% { transform: translateX(0); } 100% { transform: translateX(50%); } }

        /* Features Section with 3D Isometric Icons */
        .features {
            padding: 110px 5%;
            position: relative;
            background-color: #04091a;
            overflow: hidden;
            background-image:
                radial-gradient(circle at 15% 25%, rgba(56, 189, 248, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(139, 92, 246, 0.12) 0%, transparent 45%);
        }
        .features-grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(circle at center, black 40%, transparent 95%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 95%);
            z-index: 1;
            pointer-events: none;
        }

        .section-eyebrow {
            text-align: center;
            display: table;
            margin: 0 auto 14px;
            padding: 6px 18px;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: #38bdf8;
            font-weight: 800;
            font-size: 0.9rem;
            position: relative;
            z-index: 2;
        }
        .section-title {
            text-align: center;
            font-size: 3.1rem;
            font-weight: 900;
            margin-bottom: 18px;
            color: #fff;
            z-index: 2;
            position: relative;
        }
        .section-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 1.2rem;
            max-width: 720px;
            margin: 0 auto 65px;
            line-height: 1.75;
            z-index: 2;
            position: relative;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 28px;
            max-width: 1360px;
            margin: 0 auto;
            z-index: 3;
            position: relative;
        }
        .feature-card {
            padding: 38px 30px;
            border-radius: 26px;
            transition: all 0.45s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            background: linear-gradient(165deg, rgba(30, 41, 59, 0.72), rgba(10, 17, 36, 0.92));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35);
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--card-accent, #38bdf8), transparent);
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-12px);
            border-color: var(--card-accent, #38bdf8);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6), 0 0 35px rgba(56, 189, 248, 0.14);
        }
        .feature-card:hover::before { opacity: 1; }

        /* 3D Icon Pedestal */
        .icon-3d-stage {
            width: 78px;
            height: 78px;
            margin-bottom: 26px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 400px;
        }
        .icon-3d-pedestal {
            width: 74px;
            height: 74px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transform: rotateX(12deg) rotateY(-12deg);
            transform-style: preserve-3d;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.5s ease;
            box-shadow:
                -8px 12px 24px rgba(0, 0, 0, 0.55),
                0 0 22px var(--icon-glow, rgba(56, 189, 248, 0.35)),
                inset 0 2px 4px rgba(255, 255, 255, 0.55),
                inset 0 -4px 8px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.28);
        }
        .icon-3d-pedestal::after {
            content: '';
            position: absolute;
            inset: 5px;
            border-radius: 17px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: linear-gradient(135deg, rgba(255,255,255,0.22) 0%, transparent 60%);
            pointer-events: none;
        }
        .feature-card:hover .icon-3d-pedestal {
            transform: rotateX(0deg) rotateY(0deg) translateY(-6px) scale(1.08);
            box-shadow:
                0 18px 32px rgba(0, 0, 0, 0.65),
                0 0 32px var(--icon-glow, rgba(56, 189, 248, 0.6)),
                inset 0 2px 6px rgba(255, 255, 255, 0.75);
        }
        .icon-3d-svg {
            width: 42px;
            height: 42px;
            filter: drop-shadow(0 6px 8px rgba(0, 0, 0, 0.45));
            transform: translateZ(12px);
        }

        .feature-card h3 {
            font-size: 1.45rem;
            font-weight: 900;
            margin-bottom: 14px;
            color: #fff;
        }
        .feature-card p {
            color: #94a3b8;
            line-height: 1.78;
            font-size: 1.02rem;
        }
        .feature-tag {
            display: inline-block;
            margin-top: 16px;
            font-size: 0.78rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Executive Capabilities Banner with Antigravity 3D Levitating Icons */
        .capabilities-showcase {
            padding: 95px 5%;
            background: linear-gradient(180deg, #04091a 0%, #071026 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            position: relative;
            overflow: hidden;
        }
        .cap-grid {
            max-width: 1340px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(295px, 1fr));
            gap: 28px;
        }
        .cap-box {
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.88), rgba(7, 13, 30, 0.95));
            border: 1px solid rgba(56, 189, 248, 0.22);
            border-radius: 26px;
            padding: 32px 26px;
            display: flex;
            align-items: flex-start;
            gap: 22px;
            transition: all 0.45s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.38);
        }
        .cap-box::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, var(--ag-glow, rgba(56, 189, 248, 0.18)), transparent 70%);
            pointer-events: none;
        }
        .cap-box:hover {
            transform: translateY(-10px);
            border-color: var(--ag-accent, #38bdf8);
            box-shadow: 0 28px 55px rgba(0, 0, 0, 0.6), 0 0 32px var(--ag-glow, rgba(56, 189, 248, 0.28));
        }
        .antigravity-3d-stage {
            width: 78px;
            height: 86px;
            position: relative;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 700px;
        }
        .antigravity-orbit-ring {
            position: absolute;
            width: 74px;
            height: 74px;
            border-radius: 50%;
            border: 1.5px dashed var(--ag-accent, #38bdf8);
            opacity: 0.45;
            transform: rotateX(68deg) rotateZ(0deg);
            animation: agRingSpin 10s linear infinite;
            pointer-events: none;
        }
        .antigravity-orb {
            width: 66px;
            height: 66px;
            border-radius: 20px;
            background: var(--ag-bg, linear-gradient(135deg, #38bdf8 0%, #2563eb 55%, #1e1b4b 100%));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            transform-style: preserve-3d;
            animation: antigravityLevitating 4.2s ease-in-out infinite;
            box-shadow:
                0 16px 30px -6px rgba(0, 0, 0, 0.7),
                0 0 24px var(--ag-glow, rgba(56, 189, 248, 0.55)),
                inset 0 2px 3px rgba(255, 255, 255, 0.5),
                inset 0 -4px 8px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.28);
            transition: transform 0.45s ease;
        }
        .antigravity-orb::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.28) 0%, transparent 55%);
            pointer-events: none;
        }
        .antigravity-orb svg {
            width: 38px;
            height: 38px;
            filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.45));
            transform: translateZ(18px);
        }
        .antigravity-floor-shadow {
            position: absolute;
            bottom: 0;
            width: 48px;
            height: 10px;
            border-radius: 50%;
            background: radial-gradient(ellipse at center, var(--ag-accent, #38bdf8) 0%, rgba(0, 0, 0, 0.8) 55%, transparent 75%);
            filter: blur(4px);
            opacity: 0.75;
            animation: antigravityShadowPulse 4.2s ease-in-out infinite;
        }
        .cap-box:hover .antigravity-orb {
            transform: translateY(-10px) rotateY(-14deg) rotateX(12deg) scale(1.08);
        }
        @keyframes antigravityLevitating {
            0%, 100% { transform: translateY(0px) rotateX(6deg) rotateY(-6deg); }
            50% { transform: translateY(-11px) rotateX(-6deg) rotateY(8deg); }
        }
        @keyframes antigravityShadowPulse {
            0%, 100% { transform: scale(1); opacity: 0.78; }
            50% { transform: scale(0.68); opacity: 0.38; }
        }
        @keyframes agRingSpin {
            0% { transform: rotateX(68deg) rotateZ(0deg); }
            100% { transform: rotateX(68deg) rotateZ(360deg); }
        }
        .cap-box h4 { color: #fff; font-size: 1.24rem; font-weight: 800; margin-bottom: 8px; }
        .cap-box p { color: #94a3b8; font-size: 0.96rem; line-height: 1.7; }

        /* Pricing Section */
        .pricing {
            padding: 110px 5%;
            position: relative;
            background-color: #050b1c;
            overflow: hidden;
            background-image:
                radial-gradient(circle at 80% 20%, rgba(56, 189, 248, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 20% 80%, rgba(139, 92, 246, 0.12) 0%, transparent 45%);
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(285px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            z-index: 3;
            position: relative;
        }
        .plan-card {
            padding: 50px 38px;
            border-radius: 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.72), rgba(15, 23, 42, 0.9));
            border: 2px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            z-index: 2;
        }
        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.55);
            border-color: var(--primary-accent);
        }
        .plan-name { font-size: 1.65rem; font-weight: 900; color: #fff; margin-bottom: 10px; }
        .plan-price {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--primary-accent);
            margin-bottom: 5px;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .plan-price span { font-size: 1.2rem; color: var(--text-secondary); font-weight: 600; margin-top: 15px; margin-right: 5px; }
        .plan-features { list-style: none; margin: 30px 0; padding: 0; text-align: right; flex-grow: 1; }
        .plan-features li { margin-bottom: 15px; color: #cbd5e1; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .plan-features li::before { content: '✓'; color: var(--success-color); font-weight: 900; font-size: 1.2rem; }
        .plan-card.recommended {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.96), rgba(15, 23, 42, 0.99));
            border: 2px solid var(--primary-accent);
            transform: scale(1.04);
            box-shadow: 0 25px 60px rgba(37, 99, 235, 0.25);
        }
        .plan-card.recommended:hover { transform: scale(1.04) translateY(-10px); }
        .plan-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #0ea5e9, #7c3aed);
            color: #fff;
            padding: 6px 22px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 800;
            box-shadow: 0 6px 18px rgba(56, 189, 248, 0.4);
        }

        /* Footer */
        footer {
            padding: 80px 5% 40px;
            background: linear-gradient(180deg, #020617 0%, #050914 100%);
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
        .footer-col p { color: var(--text-secondary); font-size: 1rem; line-height: 1.8; margin-bottom: 15px; }
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 12px; }
        .footer-links a { color: var(--text-secondary); text-decoration: none; font-size: 1rem; transition: all 0.3s; display: inline-block; }
        .footer-links a:hover { color: var(--primary-accent); transform: translateX(-5px); }
        .footer-contact { list-style: none; padding: 0; margin: 0; }
        .footer-contact li { display: flex; align-items: center; gap: 10px; color: var(--text-secondary); font-size: 1rem; margin-bottom: 15px; }
        .footer-contact a { color: var(--text-secondary); text-decoration: none; transition: color 0.3s; font-family: 'Inter', 'Tajawal', sans-serif; direction: ltr; display: inline-block; }
        .footer-contact a:hover { color: var(--primary-accent); }
        .footer-contact svg { color: var(--primary-accent); flex-shrink: 0; }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 30px;
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .footer-bottom p { color: var(--text-secondary); font-size: 0.95rem; }
        .footer-bottom-links { display: flex; gap: 25px; }
        .footer-bottom-links a { color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; transition: color 0.3s; }
        .footer-bottom-links a:hover { color: var(--primary-accent); }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0; top: 0; width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(3, 7, 18, 0.85);
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding: 20px;
        }
        .modal.show { display: flex; opacity: 1; }
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
        .modal.show .modal-content { transform: translateY(0); }
        .close-modal {
            position: absolute;
            top: 20px; left: 20px;
            font-size: 30px; font-weight: bold;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.3s;
            line-height: 1;
        }
        .close-modal:hover { color: #fff; }
        .modal-content h3 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 25px;
            background: linear-gradient(to left, #fff, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .modal-body { max-height: 400px; overflow-y: auto; padding-left: 10px; }
        .modal-body p { color: var(--text-secondary); line-height: 1.8; margin-bottom: 15px; font-size: 1rem; }
        .modal-body h4 { color: #fff; margin: 20px 0 10px; font-weight: 700; }

        @media (max-width: 992px) {
            .nav-center-links { display: none; }
            .hero { flex-direction: column; text-align: center; padding-top: 40px; }
            .hero-content { text-align: center; }
            .hero-cta-group { justify-content: center; }
            .hero-metrics-bar { grid-template-columns: repeat(2, 1fr); }
            .hero-visual { margin-top: 20px; perspective: none; }
            .dash-mockup { transform: none; }
            .hero h1 { font-size: 2.6rem; }
            .section-title { font-size: 2.3rem; }
            .plan-card.recommended { transform: scale(1); }
            .card-left, .card-right { display: none; }
        }
        @media (max-width: 768px) {
            .footer-bottom { flex-direction: column; text-align: center; }
            .footer-bottom-links { justify-content: center; }
        }
    </style>
</head>
<body>

    <!-- Ambient Kinetic Aurora Background -->
    <div class="aurora-canvas">
        <div class="aurora-orb orb-cyan"></div>
        <div class="aurora-orb orb-violet"></div>
        <div class="aurora-orb orb-gold"></div>
    </div>

    <!-- Full-Bleed Top Executive Ads & Featured Partners Banner -->
    <div class="ads-banner-shell" id="ads-banner-shell">
        <div class="ads-banner-overlay-bar">
            <div class="ads-banner-title">✨ مساحات إعلانية وشركاء متميزون</div>
            <div class="ads-dots" id="ads-dots"></div>
        </div>
        <div class="ads-banner" id="ads-banner">
            <!-- Ads will be injected here -->
        </div>
    </div>

    <!-- Navbar -->
    <nav class="glass-panel" id="navbar">
        <a href="#" class="logo">
            <div class="logo-3d-cube" style="background:transparent !important; box-shadow:none !important; padding:0;">
                <img src="assets/logo.png?v=saytara_shield_v3" alt="شعار السيطرة" style="width:42px; height:42px; object-fit:contain; filter:drop-shadow(0 4px 10px rgba(0,0,0,0.6));">
            </div>
            <div>
                السيطرة
                <span class="logo-sub">AL-SAYTARA CLOUD ERP</span>
            </div>
        </a>

        <div class="nav-center-links">
            <a href="#features">المميزات الثلاثية الأبعاد</a>
            <a href="#capabilities">قدرات المنظومة</a>
            <a href="#pricing">باقات الاشتراك</a>
            <a href="#" id="open-about-nav" onclick="document.getElementById('open-about').click(); return false;">الذكاء المحاسبي</a>
        </div>

        <div style="display: flex; gap: 12px;">
            <a href="login.php" class="btn-nav">🔐 تسجيل الدخول</a>
            <a href="#pricing" class="btn-nav active">🚀 اشترك الآن</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-img"></div>

        <div class="hero-content">
            <div class="hero-badge">
                <span>✨ الجيل القادم من أنظمة الـ ERP السحابية المدعومة بالذكاء الاصطناعي</span>
            </div>
            <h1>
                قُد إمبراطورية أعمالك
                <span class="gradient-highlight">بدقة الذكاء الاصطناعي</span>
                وسرعة تفوق التوقعات.
            </h1>
            <p>
                منظومة <strong>السيطرة (MAGAIEES ERP)</strong> تجمع لك المحاسبة المالية المعتمدة، المخازن المتعددة، المبيعات ونقاط البيع (POS)، الموارد البشرية، ومطابقة الإشعارات البنكية عبر الواتساب في سحابة واحدة فائقة الأمان — صُممت خصيصاً للشركات والمؤسسات التي تطلب القمة.
            </p>

            <div class="hero-cta-group">
                <a href="#pricing" class="btn-primary">
                    <span>ابدأ اشتراك مؤسستك الآن</span>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </a>
                <a href="login.php" class="btn-secondary-hero">
                    <span>بوابة دخول المشتركين</span>
                </a>
            </div>

            <!-- Live Enterprise Trust Metrics -->
            <div class="hero-metrics-bar">
                <div class="hero-metric-item">
                    <span class="hero-metric-num">99.99%</span>
                    <span class="hero-metric-label">استقرار وأمان سحابي</span>
                </div>
                <div class="hero-metric-item">
                    <span class="hero-metric-num">&lt; 0.02s</span>
                    <span class="hero-metric-label">سرعة ترحيل القيود</span>
                </div>
                <div class="hero-metric-item">
                    <span class="hero-metric-num">100%</span>
                    <span class="hero-metric-label">عزل بيانات كل شركة</span>
                </div>
                <div class="hero-metric-item">
                    <span class="hero-metric-num">24/7</span>
                    <span class="hero-metric-label">يعمل Online &amp; Offline</span>
                </div>
            </div>
        </div>

        <!-- 3D Interactive Dashboard Mockup -->
        <div class="hero-visual" id="hero-3d-tilt">
            <div class="dash-mockup" id="dash-mockup-card">
                <!-- Sidebar -->
                <div class="dash-sidebar">
                    <div class="dash-icon active-3d" title="اللوحة المالية">📊</div>
                    <div class="dash-icon" title="المخازن الذكية">📦</div>
                    <div class="dash-icon" title="المبيعات والفواتير">🧾</div>
                    <div class="dash-icon" title="الموارد البشرية">👥</div>
                    <div class="dash-icon" title="الذكاء الاصطناعي">🧠</div>
                </div>

                <!-- Main Panel -->
                <div class="dash-main">
                    <!-- Top Bar -->
                    <div class="dash-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="dash-status-dot"></span>
                            <span style="font-size: 0.86rem; color: #0f172a; font-weight: 800;">المحاسب الآلي والمدقق الذكي (نشط الآن)</span>
                        </div>
                        <div class="dash-user"></div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="dash-stats">
                        <div class="dash-stat-card">
                            <span class="stat-label-text">صافي التدفقات اللحظية</span>
                            <div class="stat-value" style="color: #0f172a;"><span id="anim-val-1">15,820</span> $</div>
                            <span class="stat-change">▲ +18.6% نمو مباشر</span>
                        </div>
                        <div class="dash-stat-card">
                            <span class="stat-label-text">العمليات المرحلة آلياً</span>
                            <div class="stat-value" style="color: #0f172a;"><span id="anim-val-2">342</span></div>
                            <span class="stat-change" style="color: #2563eb;">مطابقة فورية 100%</span>
                        </div>
                        <div class="dash-stat-card">
                            <span class="stat-label-text">دقة التدقيق الذكي</span>
                            <div class="stat-value" style="color: #0f172a;"><span id="anim-val-3">99.9</span>%</div>
                            <span class="stat-change" style="color: #7c3aed;">محرك AI الرقابي</span>
                        </div>
                    </div>

                    <!-- Live Activity & Chart -->
                    <div class="dash-content-area">
                        <!-- Chart Container -->
                        <div class="dash-chart-container">
                            <span class="stat-label-text" style="margin-bottom: 10px; display: block; color: #334155;">مؤشر الأداء المالي والمبيعات (مباشر)</span>
                            <div class="dash-chart">
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-1" style="height:55%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-2" style="height:75%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-3" style="height:48%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-4" style="height:84%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-5" style="height:64%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-6" style="height:92%;"></div></div>
                                <div class="chart-bar-wrapper"><div class="chart-bar" id="bar-7" style="height:88%;"></div></div>
                            </div>
                        </div>

                        <!-- AI Terminal Logs -->
                        <div class="dash-ai-logs">
                            <span class="stat-label-text" style="margin-bottom: 8px; display: block; color: #a78bfa;">⚡ سجل محرك الذكاء الاصطناعي</span>
                            <div class="logs-wrapper" id="logs-container">
                                <div class="log-line">🤖 تحليل المبيعات وتوقع فائض الأرباح...</div>
                                <div class="log-line">🏦 مطابقة إشعار بنكي وارد عبر الواتساب.</div>
                                <div class="log-line">🧾 ترحيل فاتورة المبيعات #10928 لدفتر الأستاذ.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3D Floating Glass Cards -->
            <div class="floating-card card-left">
                <div class="floating-3d-badge" style="background: linear-gradient(135deg, #8b5cf6, #6366f1);">🧠</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.74rem; color: #94a3b8; font-weight:700;">توصية الذكاء الاصطناعي</div>
                    <div style="font-size: 0.88rem; font-weight: 800; color: #f8fafc;">فائض سيولة +24% هذا الشهر</div>
                </div>
            </div>
            <div class="floating-card card-right">
                <div class="floating-3d-badge" style="background: linear-gradient(135deg, #10b981, #059669); color:#fff;">✓</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.74rem; color: #94a3b8; font-weight:700;">فحص الإشعارات البنكية</div>
                    <div style="font-size: 0.88rem; font-weight: 800; color: #f8fafc;">مطابقة وقيد محاسبي فوري</div>
                </div>
            </div>
            <div class="floating-card card-whatsapp">
                <div class="floating-3d-badge" style="background: linear-gradient(135deg, #22c55e, #15803d);">💬</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.74rem; color: #94a3b8; font-weight:700;">ربط واتساب المؤسسة</div>
                    <div style="font-size: 0.88rem; font-weight: 800; color: #f8fafc;">إرسال الفواتير والكشوف تلقائياً</div>
                </div>
            </div>
            <div class="floating-card card-users">
                <div class="floating-3d-badge" style="background: linear-gradient(135deg, #38bdf8, #2563eb);">👥</div>
                <div style="text-align: right;">
                    <div style="font-size: 0.74rem; color: #94a3b8; font-weight:700;">فروع ومستخدمون متصلون</div>
                    <div style="font-size: 0.88rem; font-weight: 800; color: #f8fafc;"><span id="active-users-count">18</span> مستخدم نشط لحظياً</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Executive Promotional Highlights Showcase (Structured & High-Impact) -->
    <section class="exec-promo-showcase" aria-label="أبرز ركائز المنظومة">
        <div class="exec-promo-grid">
            <div class="exec-promo-card" style="--ep-bg: linear-gradient(135deg, #38bdf8, #2563eb); --ep-glow: rgba(56, 189, 248, 0.4);">
                <div class="exec-promo-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <div>
                    <span class="exec-promo-tag">AI ERP 2026</span>
                    <div class="exec-promo-text">المحاسب الآلي الأول المدعوم بالذكاء الاصطناعي للشركات والمؤسسات</div>
                </div>
            </div>

            <div class="exec-promo-card" style="--ep-bg: linear-gradient(135deg, #a855f7, #6366f1); --ep-glow: rgba(168, 85, 247, 0.4);">
                <div class="exec-promo-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <span class="exec-promo-tag" style="color:#d8b4fe; border-color:rgba(168,85,247,0.35); background:rgba(168,85,247,0.14);">حماية مطلقة</span>
                    <div class="exec-promo-text">عزل سحابي مشفر 100% ونطاق فرعي رسمي مستقل لكل شركة</div>
                </div>
            </div>

            <div class="exec-promo-card" style="--ep-bg: linear-gradient(135deg, #10b981, #059669); --ep-glow: rgba(16, 185, 129, 0.4);">
                <div class="exec-promo-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                </div>
                <div>
                    <span class="exec-promo-tag" style="color:#6ee7b7; border-color:rgba(16,185,129,0.35); background:rgba(16,185,129,0.14);">ربط مصرفي وواتساب</span>
                    <div class="exec-promo-text">فحص ومطابقة الإشعارات البنكية وإرسال الفواتير والكشوف تلقائياً</div>
                </div>
            </div>

            <div class="exec-promo-card" style="--ep-bg: linear-gradient(135deg, #f59e0b, #ea580c); --ep-glow: rgba(245, 158, 11, 0.4);">
                <div class="exec-promo-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </div>
                <div>
                    <span class="exec-promo-tag" style="color:#fde68a; border-color:rgba(245,158,11,0.35); background:rgba(245,158,11,0.14);">استمرارية بلا انقطاع</span>
                    <div class="exec-promo-text">تقنية العمل المزدوج (Online &amp; Offline) بسرعة استجابة صفرية التأخير</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners Marquee -->
    <section class="partners">
        <p>🏆 يثق بمنظومة السيطرة نخبة الشركات والمصانع والمؤسسات المالية</p>
        <div class="marquee-wrapper" id="partners-marquee">
            <a href="#" class="partner-logo"><img src="assets/logo_maqayis.png?v=saytara_shield_v3" alt="السيطرة" loading="lazy"> السيطرة</a>
            <a href="#" class="partner-logo"><img src="assets/logo_alasaad.png" alt="مصنع الأسعد" loading="lazy"> مصنع الأسعد</a>
            <a href="#" class="partner-logo"><img src="assets/logo_omega.png" alt="أوميغا" loading="lazy"> أوميغا</a>
            <a href="#" class="partner-logo"><img src="assets/logo_bok.png" alt="بنك الخرطوم" loading="lazy"> بنك الخرطوم</a>
            <a href="#" class="partner-logo"><img src="assets/logo_fcb.png" alt="بنك المزارع" loading="lazy"> بنك المزارع</a>
            <a href="#" class="partner-logo"><img src="assets/logo_maqayis.png?v=saytara_shield_v3" alt="السيطرة" loading="lazy"> السيطرة</a>
            <a href="#" class="partner-logo"><img src="assets/logo_alasaad.png" alt="مصنع الأسعد" loading="lazy"> مصنع الأسعد</a>
            <a href="#" class="partner-logo"><img src="assets/logo_omega.png" alt="أوميغا" loading="lazy"> أوميغا</a>
            <a href="#" class="partner-logo"><img src="assets/logo_bok.png" alt="بنك الخرطوم" loading="lazy"> بنك الخرطوم</a>
            <a href="#" class="partner-logo"><img src="assets/logo_fcb.png" alt="بنك المزارع" loading="lazy"> بنك المزارع</a>
        </div>
    </section>

    <!-- Features Section with 3D Layered Isometric Icons -->
    <section class="features" id="features">
        <div class="features-grid-overlay"></div>

        <span class="section-eyebrow">🌟 هندسة برمجية بمواصفات عالمية</span>
        <h2 class="section-title">إمكانيات جبارة تغنيك عن عشرات البرامج</h2>
        <p class="section-subtitle">صممنا كل وحدة في نظام السيطرة بأيقونات وواجهات ثلاثية الأبعاد فائقة الوضوح، لتنجز أعقد العمليات المالية والمخزنية والرقابية في أجزاء من الثانية.</p>

        <div class="features-grid">
            <!-- Feature 1: 3D Financial Ledger Cube -->
            <div class="feature-card" style="--card-accent: #38bdf8;">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 55%, #0f172a 100%); --icon-glow: rgba(56, 189, 248, 0.5);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <polygon points="32,6 56,18 32,30 8,18" fill="#7dd3fc"/>
                            <polygon points="8,18 32,30 32,56 8,44" fill="#0284c7"/>
                            <polygon points="56,18 32,30 32,56 56,44" fill="#1e40af"/>
                            <rect x="20" y="24" width="6" height="18" rx="3" fill="#fde047"/>
                            <rect x="30" y="18" width="6" height="24" rx="3" fill="#34d399"/>
                            <rect x="40" y="12" width="6" height="30" rx="3" fill="#ffffff"/>
                        </svg>
                    </div>
                </div>
                <h3>إدارة مالية وشجرة حسابات ذكية</h3>
                <p>قيود يومية آلية، مراكز تكلفة، تعدد عملات وأسعار صرف حية، مع قوائم دخل وميزانيات عمومية وكشوف حساب تفصيلية تُبنى لحظياً بدقة 100%.</p>
                <span class="feature-tag">📊 دقة محاسبية دولية IFRS</span>
            </div>

            <!-- Feature 2: 3D AI Auditor & Bank Receipt Scanner -->
            <div class="feature-card" style="--card-accent: #a855f7; border-color: rgba(168, 85, 247, 0.35);">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #9333ea 0%, #4f46e5 55%, #1e1b4b 100%); --icon-glow: rgba(168, 85, 247, 0.55);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <path d="M32 6L54 16V32C54 46 44 55 32 60C20 55 10 46 10 32V16L32 6Z" fill="url(#aiShield3d)" stroke="#f3e8ff" stroke-width="2"/>
                            <circle cx="32" cy="31" r="11" fill="#1e1b4b" stroke="#38bdf8" stroke-width="2.5"/>
                            <path d="M27 31L30.5 34.5L37.5 27.5" stroke="#34d399" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="14" y1="24" x2="50" y2="24" stroke="#fde047" stroke-width="2" stroke-dasharray="4 3"/>
                            <defs>
                                <linearGradient id="aiShield3d" x1="10" y1="6" x2="54" y2="60" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#c084fc"/>
                                    <stop offset="1" stop-color="#4338ca"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
                <h3 style="background: linear-gradient(135deg, #fff, #d8b4fe); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">المدقق الذكي وفحص الإشعارات بالـ AI</h3>
                <p>يقرأ الإشعارات البنكية المرسلة بالواتساب، يتحقق من رقم العملية والمبلغ، يكتشف التزوير أو التكرار، ويقيد السند المالي في حساب العميل تلقائياً.</p>
                <span class="feature-tag" style="color:#d8b4fe; border-color:rgba(168,85,247,0.35);">🤖 حصرياً في السيطرة AI</span>
            </div>

            <!-- Feature 3: 3D Isometric Smart Warehouse -->
            <div class="feature-card" style="--card-accent: #f59e0b;">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 55%, #451a03 100%); --icon-glow: rgba(245, 158, 11, 0.5);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <polygon points="32,8 56,20 32,32 8,20" fill="#fde68a"/>
                            <polygon points="8,20 32,32 32,56 8,44" fill="#f59e0b"/>
                            <polygon points="56,20 32,32 32,56 56,44" fill="#b45309"/>
                            <polygon points="24,14 40,22 32,26 16,18" fill="#ffffff" opacity="0.85"/>
                            <line x1="32" y1="32" x2="32" y2="56" stroke="#fef3c7" stroke-width="2"/>
                        </svg>
                    </div>
                </div>
                <h3>هندسة مخازن ومستودعات متعددة</h3>
                <p>رقابة لحظية على الأرصدة الفعلية والمحجوزة والمتاحة، جرد ذكي بالباركود، أوامر استلام وتسليم مخزني (GRN/GDN)، وتنبيهات استباقية للنواقص.</p>
                <span class="feature-tag">📦 جرد ومطابقة صفرية الأخطاء</span>
            </div>

            <!-- Feature 4: 3D Cloud POS & Instant Invoicing -->
            <div class="feature-card" style="--card-accent: #10b981;">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #10b981 0%, #059669 55%, #064e3b 100%); --icon-glow: rgba(16, 185, 129, 0.5);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <rect x="10" y="14" width="44" height="34" rx="7" fill="#064e3b" stroke="#6ee7b7" stroke-width="2.5"/>
                            <rect x="16" y="20" width="20" height="14" rx="3" fill="#34d399"/>
                            <rect x="40" y="20" width="8" height="6" rx="2" fill="#fde047"/>
                            <rect x="40" y="29" width="8" height="5" rx="2" fill="#a7f3d0"/>
                            <path d="M20 48H44L48 56H16L20 48Z" fill="#6ee7b7"/>
                        </svg>
                    </div>
                </div>
                <h3>مبيعات ونقاط بيع (POS) فائقة السرعة</h3>
                <p>شاشة مبيعات وكاشير تدعم الباركود واللمس والبيع النقدي والآجل وتجزئة الفواتير وحجوزات البضاعة، مع طباعة حرارية وA4 ومشاركة فورية.</p>
                <span class="feature-tag">⚡ إصدار الفاتورة في أقل من ثانية</span>
            </div>

            <!-- Feature 5: 3D Holographic BI Analytics -->
            <div class="feature-card" style="--card-accent: #06b6d4;">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #06b6d4 0%, #0284c7 55%, #083344 100%); --icon-glow: rgba(6, 182, 212, 0.5);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <circle cx="30" cy="34" r="20" fill="#0e7490" stroke="#67e8f9" stroke-width="2.5"/>
                            <path d="M30 14A20 20 0 0 1 50 34H30V14Z" fill="#fde047"/>
                            <path d="M34 10A20 20 0 0 1 54 30H34V10Z" fill="#38bdf8" stroke="#fff" stroke-width="1.5"/>
                            <circle cx="24" cy="38" r="4" fill="#fff"/>
                        </svg>
                    </div>
                </div>
                <h3>ذكاء أعمال وتقارير تنفيذية تفاعلية</h3>
                <p>لوحات قيادة حية للمدير العام تعرض صافي الأرباح، أعمار الديون، الأصناف الأكثر ربحية، والتدفقات النقدية المتوقعة لاتخاذ قرارات استراتيجية واثقة.</p>
                <span class="feature-tag">📈 رؤية تنفيذية شاملة 360°</span>
            </div>

            <!-- Feature 6: 3D WhatsApp & FinTech Automation -->
            <div class="feature-card" style="--card-accent: #22c55e;">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 55%, #14532d 100%); --icon-glow: rgba(34, 197, 94, 0.5);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <path d="M32 10C19.8 10 10 19.3 10 30.8C10 35.4 11.6 39.7 14.3 43.2L11.5 53L21.8 50.3C24.9 51.8 28.3 52.6 32 52.6C44.2 52.6 54 43.3 54 31.8C54 20.3 44.2 10 32 10Z" fill="#4ade80" stroke="#ffffff" stroke-width="2.5"/>
                            <path d="M25 23C24 23 22 24.5 22 27.5C22 32 27 38.5 34.5 41.5C37.5 42.5 40 41.5 41 39.5L37.5 36.5L34.5 38C31.5 36.5 28.5 33.5 27 30.5L28.5 27.5L25 23Z" fill="#052e16"/>
                        </svg>
                    </div>
                </div>
                <h3>منظومة واتساب مستقلة لكل شركة</h3>
                <p>اربط رقم واتساب شركتك بمسح QR واحد لإرسال الفواتير وسندات القبض والصرف وكشوف الحساب للعملاء تلقائياً، مع استقبال وفحص الإشعارات البنكية.</p>
                <span class="feature-tag">💬 تواصل آلي فوري مع العملاء</span>
            </div>

            <!-- Feature 7: 3D HR & Payroll Engine -->
            <div class="feature-card" style="--card-accent: #f43f5e;">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 55%, #4c0519 100%); --icon-glow: rgba(244, 63, 94, 0.5);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <circle cx="26" cy="22" r="9" fill="#fda4af" stroke="#fff" stroke-width="2"/>
                            <path d="M12 48C12 39.5 18.2 34 26 34C33.8 34 40 39.5 40 48" fill="#fb7185" stroke="#fff" stroke-width="2"/>
                            <rect x="34" y="24" width="20" height="26" rx="5" fill="#fde047" stroke="#fff" stroke-width="2"/>
                            <line x1="39" y1="32" x2="49" y2="32" stroke="#881337" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="39" y1="39" x2="46" y2="39" stroke="#881337" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <h3>شؤون الموظفين ومسير الرواتب الآلي</h3>
                <p>إدارة متكاملة لملفات الموظفين، العقود، الحضور والانصراف، السلف، البدلات، والاستقطاعات، مع صرف وترحيل الرواتب للخزينة أو البنك بضغطة زر.</p>
                <span class="feature-tag">👥 أتمتة كاملة للموارد البشرية</span>
            </div>

            <!-- Feature 8: 3D Cyber Vault & Multi-Tenant Isolation -->
            <div class="feature-card" style="--card-accent: #6366f1;">
                <div class="icon-3d-stage">
                    <div class="icon-3d-pedestal" style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 55%, #1e1b4b 100%); --icon-glow: rgba(99, 102, 241, 0.55);">
                        <svg class="icon-3d-svg" viewBox="0 0 64 64" fill="none">
                            <rect x="14" y="26" width="36" height="28" rx="7" fill="#818cf8" stroke="#ffffff" stroke-width="2.5"/>
                            <path d="M22 26V19C22 13.5 26.5 9 32 9C37.5 9 42 13.5 42 19V26" stroke="#fde047" stroke-width="4" stroke-linecap="round"/>
                            <circle cx="32" cy="38" r="4" fill="#1e1b4b"/>
                            <path d="M32 42V47" stroke="#1e1b4b" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <h3>أمان سيبراني وعزل سحابي للشركات</h3>
                <p>تشفير متقدم، حماية ببصمة الجهاز المعتمدة (Device Trust)، صلاحيات دقيقة لكل موظف وشاشة، ونسخ احتياطي سحابي يومي يضمن أمان بياناتك 100%.</p>
                <span class="feature-tag">🔒 درع حماية مؤسسي متكامل</span>
            </div>
        </div>
    </section>

    <!-- Why Enterprises Choose Magaiees Section (Antigravity 3D Icons) -->
    <section class="capabilities-showcase" id="capabilities">
        <span class="section-eyebrow">💎 لماذا تختار الشركات الكبرى منظومة السيطرة؟</span>
        <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 50px;">مصمم ليمنح مؤسستك التفوق التنافسي المطلق</h2>
        <div class="cap-grid">
            <!-- Antigravity 3D Icon 1: Dedicated Cloud Subdomain & SSL Shield -->
            <div class="cap-box" style="--ag-accent: #38bdf8; --ag-glow: rgba(56, 189, 248, 0.45); --ag-bg: linear-gradient(135deg, #0ea5e9 0%, #2563eb 55%, #0f172a 100%);">
                <div class="antigravity-3d-stage">
                    <div class="antigravity-orbit-ring"></div>
                    <div class="antigravity-orb">
                        <svg viewBox="0 0 64 64" fill="none">
                            <ellipse cx="32" cy="30" rx="20" ry="20" fill="url(#agGlobe1)" stroke="#e0f2fe" stroke-width="2"/>
                            <ellipse cx="32" cy="30" rx="10" ry="20" stroke="#7dd3fc" stroke-width="1.6" stroke-dasharray="3 2"/>
                            <line x1="12" y1="30" x2="52" y2="30" stroke="#7dd3fc" stroke-width="1.6"/>
                            <path d="M44 34L54 38V46C54 52 49.5 56 44 58C38.5 56 34 52 34 46V38L44 34Z" fill="#fde047" stroke="#ffffff" stroke-width="2"/>
                            <path d="M40.5 46L43 48.5L48 43.5" stroke="#0f172a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <defs>
                                <linearGradient id="agGlobe1" x1="12" y1="10" x2="52" y2="50" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#38bdf8"/>
                                    <stop offset="1" stop-color="#1e3a8a"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="antigravity-floor-shadow"></div>
                </div>
                <div>
                    <h4>رابط ونطاق سحابي خاص بشركتك</h4>
                    <p>تحصل كل شركة فور اشتراكها على نطاق فرعي رسمي مستقل بشهادة أمان SSL خاصة، مع عزل تام لقاعدة بياناتها ومستخدميها.</p>
                </div>
            </div>

            <!-- Antigravity 3D Icon 2: Multi-Device 3D Constellation -->
            <div class="cap-box" style="--ag-accent: #a855f7; --ag-glow: rgba(168, 85, 247, 0.45); --ag-bg: linear-gradient(135deg, #a855f7 0%, #6366f1 55%, #1e1b4b 100%);">
                <div class="antigravity-3d-stage">
                    <div class="antigravity-orbit-ring"></div>
                    <div class="antigravity-orb" style="animation-delay: -1.1s;">
                        <svg viewBox="0 0 64 64" fill="none">
                            <rect x="8" y="12" width="38" height="26" rx="5" fill="#312e81" stroke="#e9d5ff" stroke-width="2"/>
                            <rect x="13" y="17" width="16" height="10" rx="2" fill="#38bdf8"/>
                            <rect x="36" y="24" width="18" height="28" rx="4" fill="url(#agPhone2)" stroke="#ffffff" stroke-width="2"/>
                            <circle cx="45" cy="47" r="2" fill="#fde047"/>
                            <path d="M20 38H34L37 44H17L20 38Z" fill="#c084fc"/>
                            <defs>
                                <linearGradient id="agPhone2" x1="36" y1="24" x2="54" y2="52" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#f472b6"/>
                                    <stop offset="1" stop-color="#7e22ce"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="antigravity-floor-shadow" style="animation-delay: -1.1s;"></div>
                </div>
                <div>
                    <h4>يعمل على الحاسوب والآيباد والهاتف</h4>
                    <p>واجهات مخصصة للمحاسبين على الشاشات الكبيرة، وتطبيق ميداني فائق السرعة للمديرين والمندوبين على الهواتف الذكية.</p>
                </div>
            </div>

            <!-- Antigravity 3D Icon 3: Offline-First Quantum Sync Core -->
            <div class="cap-box" style="--ag-accent: #10b981; --ag-glow: rgba(16, 185, 129, 0.45); --ag-bg: linear-gradient(135deg, #10b981 0%, #059669 55%, #064e3b 100%);">
                <div class="antigravity-3d-stage">
                    <div class="antigravity-orbit-ring"></div>
                    <div class="antigravity-orb" style="animation-delay: -2.2s;">
                        <svg viewBox="0 0 64 64" fill="none">
                            <path d="M20 42H46C51.5 42 56 37.5 56 32C56 26.8 52 22.5 46.8 22C44.8 14.5 38 9 30 9C20.6 9 13 16.6 13 26C13 26.8 13.1 27.6 13.2 28.3C9.6 30 7 33.7 7 38C7 43.5 11.5 48 17 48" fill="url(#agCloud3)" stroke="#d1fae5" stroke-width="2"/>
                            <circle cx="32" cy="36" r="12" fill="#064e3b" stroke="#fde047" stroke-width="2.2"/>
                            <path d="M33 28L26 37H32L31 44L38 35H32L33 28Z" fill="#fde047"/>
                            <defs>
                                <linearGradient id="agCloud3" x1="7" y1="9" x2="56" y2="48" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#34d399"/>
                                    <stop offset="1" stop-color="#047857"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="antigravity-floor-shadow" style="animation-delay: -2.2s;"></div>
                </div>
                <div>
                    <h4>تقنية Offline-First لا تتوقف أبداً</h4>
                    <p>حتى عند انقطاع الإنترنت، يواصل النظام إصدار الفواتير والسندات وحفظها محلياً ثم يزامنها تلقائياً مع السحابة فور عودة الاتصال.</p>
                </div>
            </div>

            <!-- Antigravity 3D Icon 4: 60-Second Antigravity Warp Rocket -->
            <div class="cap-box" style="--ag-accent: #f59e0b; --ag-glow: rgba(245, 158, 11, 0.45); --ag-bg: linear-gradient(135deg, #f59e0b 0%, #ea580c 55%, #451a03 100%);">
                <div class="antigravity-3d-stage">
                    <div class="antigravity-orbit-ring"></div>
                    <div class="antigravity-orb" style="animation-delay: -3.1s;">
                        <svg viewBox="0 0 64 64" fill="none">
                            <path d="M32 8C39 14 43 24 43 35L32 42L21 35C21 24 25 14 32 8Z" fill="url(#agRocket4)" stroke="#ffffff" stroke-width="2"/>
                            <circle cx="32" cy="25" r="5" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                            <path d="M21 31L13 39L21 41V31Z" fill="#fde047"/>
                            <path d="M43 31L51 39L43 41V31Z" fill="#fde047"/>
                            <path d="M27 42L32 56L37 42H27Z" fill="#38bdf8"/>
                            <defs>
                                <linearGradient id="agRocket4" x1="21" y1="8" x2="43" y2="42" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#fef08a"/>
                                    <stop offset="1" stop-color="#f97316"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="antigravity-floor-shadow" style="animation-delay: -3.1s;"></div>
                </div>
                <div>
                    <h4>جاهزية فورية خلال 60 ثانية</h4>
                    <p>بدون خوادم معقدة أو تكاليف تأسيس باهظة؛ اختر باقتك، أنشئ حساب شركتك، وابدأ العمل الفوري بكافة الوحدات المحاسبية والمخزنية.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="features-grid-overlay"></div>

        <span class="section-eyebrow">🚀 استثمر في مستقبل شركتك</span>
        <h2 class="section-title">اختر الباقة المناسبة لطموح مؤسستك</h2>
        <p class="section-subtitle">خطط اشتراك مرنة ومدروسة تناسب الشركات الناشئة، المصانع، والمؤسسات الكبرى. جميع الباقات تشمل التحديثات المجانية والدعم الفني.</p>

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
                <a href="register.php?plan=1" class="btn-nav" style="width:100%; justify-content:center; background:rgba(255,255,255,0.05);">اختيار الباقة</a>
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
                <a href="register.php?plan=3" class="btn-nav" style="width:100%; justify-content:center; background:rgba(255,255,255,0.05);">اختيار الباقة</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <!-- Column 1: About -->
            <div class="footer-col">
                <a href="#" class="logo" style="margin-bottom: 20px;">
                    <div class="logo-3d-cube" style="width:40px; height:40px;">⚡</div>
                    السيطرة
                </a>
                <p>نظام السيطرة (MAGAIEES) هو منصة سحابية عالمية متكاملة لإدارة موارد المؤسسات (ERP)، صُمم خصيصاً لتمكين الشركات من أتمتة حساباتها، مخازنها، ومبيعاتها بالذكاء الاصطناعي لتحقيق نمو ذكي ومستدام.</p>
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
            <p>&copy; 2026 مؤسسة السيطرة التجارية. جميع الحقوق محفوظة.</p>
            <div class="footer-bottom-links">
                <a href="#" id="open-privacy">سياسة الخصوصية</a>
                <a href="magaiees-privacy.html" target="_blank">وثيقة الخصوصية الكاملة</a>
                <a href="#">شروط الخدمة</a>
            </div>
        </div>
    </footer>

    <!-- Privacy Policy Modal -->
    <div class="modal" id="privacy-modal">
        <div class="modal-content">
            <span class="close-modal" id="close-privacy">&times;</span>
            <h3>قانون حماية الخصوصية</h3>
            <div class="modal-body">
                <p>مرحباً بكم في منصة <strong>السيطرة (MAGAIEES)</strong>. نحن نولي أقصى درجات الأهمية لحماية خصوصية بياناتكم وأمن معلوماتكم الشخصية والمالية.</p>
                <h4>1. جمع المعلومات واستخدامها</h4>
                <p>نقوم بجمع البيانات اللازمة لتقديم وإدارة الخدمات السحابية بفعالية، ويشمل ذلك معلومات الاتصال، بيانات الحساب الشخصي، وتفضيلات النظام. نضمن استخدام هذه البيانات فقط للأغراض التشغيلية وتحسين تجربة المستخدم.</p>
                <h4>2. حماية البيانات وأمنها</h4>
                <p>نطبق بروتوكولات أمان متطورة وتشفير كامل للبيانات (End-to-End Encryption) لمنع أي وصول غير مصرح به أو تسريب للمعلومات. بياناتكم تُخزن في مراكز بيانات سحابية آمنة ومحمية بصفة مستمرة.</p>
                <h4>3. مشاركة البيانات مع أطراف ثالثة</h4>
                <p>نحن لا نقوم ببيع، تأجير، أو مشاركة بياناتكم الخاصة أو التجارية مع أي جهة خارجية أو أطراف ثالثة لأغراض تسويقية مطلقاً، إلا في الحالات التي يتطلبها القانون أو بموافقتكم الصريحة.</p>
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
            <h3>من نحن وابتكارات السيطرة</h3>
            <div class="modal-body">
                <p>تأسست <strong>مؤسسة السيطرة التجارية</strong> برؤية طموحة تهدف لتمكين قطاع الأعمال في سلطنة عُمان والوطن العربي من خلال ابتكار حلول برمجية سحابية ذكية وموثوقة بالكامل. نحن لا نقدم مجرد برمجيات، بل نبني بيئات عمل متكاملة تقود المستقبل.</p>
                <h4>⚡ المحاسب الآلي (Automated Accountant)</h4>
                <p>يمثل المحاسب الآلي في السيطرة ثورة في طريقة إدارة الدفاتر المحاسبية. يقوم النظام تلقائياً وبشكل مستقل بـ:</p>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style-type:square; line-height:1.7;">
                    <li>تحليل الفواتير والمستندات واستخراج البيانات آلياً دون تدخل بشري.</li>
                    <li>توليد قيود اليومية المتوازنة فورياً وترحيلها لدفاتر الأستاذ والميزانية.</li>
                    <li>اكتشاف وتصحيح الأخطاء الحسابية وتطابق ميزان المراجعة بنسبة دقة تصل إلى 99.9%.</li>
                </ul>
                <h4>🧠 تقنيات الذكاء الاصطناعي (AI Core)</h4>
                <p>تم دمج الذكاء الاصطناعي في صميم نظام السيطرة ليقدم للشركات تحليلات تنبؤية فائقة الذكاء:</p>
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
            <h3>رؤيتنا وقيمنا ومميزات السيطرة</h3>
            <div class="modal-body">
                <h4>🎯 رؤيتنا</h4>
                <p>أن نصبح الشريك الرقمي السحابي الأول والأكثر وثوقاً لكافة الشركات والمؤسسات في الشرق الأوسط، والمحرك الأساسي الذي يقود الأعمال التجارية نحو الكفاءة والنمو الذكي باستخدام الذكاء الاصطناعي دون تعقيد.</p>
                <h4>💎 قيمنا الراسخة</h4>
                <ul style="margin-right:20px; color:var(--text-secondary); margin-bottom:15px; list-style-type:square; line-height:1.7;">
                    <li><strong>الدقة المتناهية:</strong> صفر أخطاء محاسبية بفضل الأتمتة المتقدمة.</li>
                    <li><strong>الأمن السيبراني:</strong> تشفير كامل لبياناتك وحساباتك وفق أعلى المعايير العالمية.</li>
                    <li><strong>الابتكار المستمر:</strong> مواكبة متواصلة للتحديثات والتكنولوجيا.</li>
                    <li><strong>الشفافية وسهولة الاستخدام:</strong> نظام محاسبي قوي بواجهات تفاعلية يفهمها الجميع.</li>
                </ul>
                <div style="text-align:center; margin-top:25px; padding:15px; background:rgba(59,130,246,0.1); border-radius:12px; border:1px solid rgba(59,130,246,0.2);">
                    <p style="color:#fff; font-weight:700; margin-bottom:10px;">انضم إلى مئات الشركات التي تدير أعمالها بذكاء مع السيطرة!</p>
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
                <p>تعبّر الهوية البصرية لنظام <strong>السيطرة (MAGAIEES)</strong> عن الاحترافية، السرعة، والابتكار التقني.</p>
                <h4>⚡ الشعار الرسمي ودلالاته</h4>
                <p>يمثل شعار الصاعقة ⚡ الكفاءة والذكاء في معالجة البيانات، ويعبر عن السرعة الفائقة والربط التقني المتكامل.</p>
                <h4>🎨 الألوان المعتمدة (Brand Colors)</h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:15px; margin:20px 0;">
                    <div style="background:#38bdf8; border-radius:12px; padding:15px; text-align:center; color:#020617; font-weight:bold;">
                        الأزرق السيبراني
                        <div style="font-size:0.85rem; font-weight:normal; margin-top:5px; font-family:monospace;">#38BDF8</div>
                    </div>
                    <div style="background:#8b5cf6; border-radius:12px; padding:15px; text-align:center; color:#fff; font-weight:bold;">
                        البنفسجي الذكي
                        <div style="font-size:0.85rem; font-weight:normal; margin-top:5px; font-family:monospace;">#8B5CF6</div>
                    </div>
                    <div style="background:#020617; border-radius:12px; padding:15px; text-align:center; color:#fff; font-weight:bold; border:1px solid rgba(255,255,255,0.1);">
                        الأسود الملكي
                        <div style="font-size:0.85rem; font-weight:normal; margin-top:5px; font-family:monospace;">#020617</div>
                    </div>
                </div>
                <div style="text-align:center; margin-top:20px;">
                    <a href="assets/logo_maqayis.png" download class="btn-primary" style="padding:10px 25px; font-size:1.1rem; border-radius:10px; display:inline-flex; align-items:center; gap:8px; text-decoration:none;">
                        تحميل حزمة الهوية والشعار
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Navbar blur & elevation on scroll
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (!nav) return;
            if (window.scrollY > 40) {
                nav.style.background = 'rgba(2, 6, 23, 0.92)';
                nav.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.5)';
            } else {
                nav.style.background = 'rgba(15, 23, 42, 0.72)';
                nav.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.25)';
            }
        });

        // Subtle 3D Mouse Parallax on Hero Dashboard Mockup
        (function initHero3DTilt() {
            const wrap = document.getElementById('hero-3d-tilt');
            const card = document.getElementById('dash-mockup-card');
            if (!wrap || !card) return;
            wrap.addEventListener('mousemove', (e) => {
                if (window.innerWidth <= 992) return;
                const rect = wrap.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `rotateY(${(-x * 10).toFixed(2)}deg) rotateX(${(y * 8).toFixed(2)}deg) translateY(-4px)`;
            });
            wrap.addEventListener('mouseleave', () => {
                if (window.innerWidth <= 992) return;
                card.style.transform = 'rotateY(-8deg) rotateX(4deg)';
            });
        })();

        // Load Public Data (Ads, Partners, Pricing Plans)
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
                            {id: 1, name: "السيطرة", logo: "https://ui-avatars.com/api/?name=%D8%A7%D9%84%D8%B3%D9%8A%D8%B7%D8%B1%D8%A9&background=38bdf8&color=fff&size=100", link: "#"},
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
                        const shell = document.getElementById('ads-banner-shell');
                        const banner = document.getElementById('ads-banner');
                        const dotsWrap = document.getElementById('ads-dots');
                        if (banner) {
                            if (shell) shell.style.display = 'block';
                            banner.style.display = 'block';
                            banner.innerHTML = '';
                            if (dotsWrap) {
                                dotsWrap.innerHTML = '';
                                dotsWrap.style.display = activeAds.length > 1 ? 'flex' : 'none';
                            }

                            activeAds.forEach((ad, index) => {
                                const slide = document.createElement('div');
                                slide.className = 'ad-slide' + (index === 0 ? ' active' : '');
                                const safeCompanyName = (ad.companyName || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                const detailsHtml = safeCompanyName ? `<div class="ad-badge">شريك معلن: ${safeCompanyName}</div>` : '';
                                const imgHtml = `<img src="${ad.image}" alt="${safeCompanyName || 'إعلان'}" loading="lazy">${detailsHtml}`;
                                slide.innerHTML = ad.link ? `<a href="${ad.link}" target="_blank" rel="noopener noreferrer" style="display:block; width:100%; height:100%; text-decoration:none;">${imgHtml}</a>` : imgHtml;
                                banner.appendChild(slide);

                                if (dotsWrap && activeAds.length > 1) {
                                    const dot = document.createElement('span');
                                    dot.className = 'ads-dot' + (index === 0 ? ' active' : '');
                                    dot.onclick = () => switchAdSlide(index);
                                    dotsWrap.appendChild(dot);
                                }
                            });

                            let currentAdIndex = 0;
                            const slides = banner.querySelectorAll('.ad-slide');
                            const dots = dotsWrap ? dotsWrap.querySelectorAll('.ads-dot') : [];

                            function switchAdSlide(nextIdx) {
                                if (!slides.length) return;
                                slides[currentAdIndex].classList.remove('active');
                                if (dots[currentAdIndex]) dots[currentAdIndex].classList.remove('active');
                                currentAdIndex = nextIdx % slides.length;
                                slides[currentAdIndex].classList.add('active');
                                if (dots[currentAdIndex]) dots[currentAdIndex].classList.add('active');
                            }

                            if (activeAds.length > 1) {
                                setInterval(() => {
                                    switchAdSlide(currentAdIndex + 1);
                                }, 5000);
                            }
                        }
                    }
                }

                // Render Partners
                const pWrapper = document.getElementById('partners-marquee');
                if (data && data.partners_enabled !== false && data.partners && data.partners.length > 0) {
                    let html = '';
                    const renderLogos = () => {
                        data.partners.forEach(p => {
                            if (p.active === false) return;
                            html += `<div class="partner-logo" title="${p.name}">
                                <img src="${p.logo}" alt="${p.name}" style="height:36px; border-radius:6px;"> ${p.name}
                            </div>`;
                        });
                    };
                    renderLogos();
                    renderLogos();
                    pWrapper.innerHTML = html;
                    pWrapper.parentElement.style.display = 'block';
                } else {
                    if (pWrapper) pWrapper.parentElement.style.display = 'none';
                }

                // Render Plans
                const grid = document.getElementById('pricing-grid');
                if (data && data.plans && data.plans.length > 0) {
                    grid.innerHTML = '';
                    const activePlans = data.plans.filter(p => p.status !== 'suspended');
                    const normalPlans = activePlans.filter(p => !(p.isTrial || p.name.includes('تجريب')));
                    const trialPlans = activePlans.filter(p => p.isTrial || p.name.includes('تجريب'));
                    const sortedPlans = [...trialPlans, ...normalPlans];

                    if (sortedPlans.length === 1) {
                        grid.style.display = 'flex';
                        grid.style.justifyContent = 'center';
                    } else {
                        grid.style.display = 'grid';
                    }

                    sortedPlans.forEach((plan) => {
                        const isRecommended = plan.name.includes('متقدمة') || sortedPlans.length === 1;
                        const isTrialPlan = plan.isTrial || plan.name.includes('تجريب');

                        let badgeText = 'باقة مؤسسية';
                        if (isRecommended) badgeText = '⭐ الأكثر اختياراً للشركات';
                        if (isTrialPlan) badgeText = '🎁 تجربة مجانية فورية';

                        const badgeHtml = `<div class="plan-badge" style="${isTrialPlan ? 'background:linear-gradient(135deg,#10b981,#059669);' : ''}">${badgeText}</div>`;
                        const btnClass = isRecommended ? 'btn-primary' : 'btn-nav';
                        const btnStyle = isRecommended ? 'width: 100%; border-radius: 14px;' : 'width: 100%; justify-content:center; background:rgba(255,255,255,0.06);';

                        const featuresList = [
                            `نطاق فرعي مستقل خاص بشركتك (.elsajana.com)`,
                            `حتى ${plan.maxUsers} مستخدمين نشطين بصلاحيات مستقلة`,
                            `مساحة تخزينية سحابية مشفرة ${plan.storage} GB`,
                            `المحاسب الآلي والمدقق الذكي بالـ AI مفعل بالكامل`,
                            `ربط مباشر بالواتساب وفحص الإشعارات البنكية`,
                            `حفظ نسخ احتياطي يومي آمن ومشفر تلقائياً`,
                            `دعم فني وتحديثات مستمرة 24/7 مجاناً`
                        ];

                        const featuresHtml = featuresList.map(f => `<li>${f}</li>`).join('');

                        grid.innerHTML += `
                            <div class="plan-card ${isRecommended ? 'recommended' : ''}" style="${sortedPlans.length === 1 ? 'max-width: 440px; width: 100%;' : ''}">
                                ${badgeHtml}
                                <h3 class="plan-name" style="font-size: 1.8rem; margin-bottom:15px;">${plan.name}</h3>
                                <div class="plan-price" style="font-size: 3.8rem;">${isTrialPlan ? 'مجاناً' : plan.price + '<span>$</span>'}<span style="font-size:0.95rem;opacity:0.85;display:block;margin-right:10px;">${plan.duration ? 'لمدة ' + plan.duration + ' يوماً' : 'شهرياً'}</span></div>
                                <ul class="plan-features" style="margin: 32px 0;">
                                    ${featuresHtml}
                                </ul>
                                <a href="register.php?plan=${plan.id}" class="${btnClass}" style="${btnStyle}">اشترك الآن وانطلق بقوة 🚀</a>
                            </div>
                        `;
                    });
                } else {
                    if (grid) grid.innerHTML = '<div style="text-align:center; width:100%;">لا توجد باقات متاحة حالياً.</div>';
                }
            } catch (e) {
                console.error("Failed to load public data", e);
            }
        }

        loadPublicData();

        // Register PWA service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
                .then(reg => console.log('Service Worker registered successfully:', reg.scope))
                .catch(err => console.error('Service Worker registration failed:', err));
        }

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

            if (salesEl) salesEl.innerText = sales.toLocaleString('en-US');
            if (invoicesEl) invoicesEl.innerText = invoices;
            if (efficiencyEl) efficiencyEl.innerText = "99.9";
            if (usersEl) usersEl.innerText = activeUsers;

            setInterval(() => {
                const change = Math.floor(Math.random() * 140) - 25;
                sales += change;
                if (salesEl) {
                    salesEl.innerText = sales.toLocaleString('en-US');
                    salesEl.style.color = change >= 0 ? '#10b981' : '#f43f5e';
                    setTimeout(() => salesEl.style.color = '#0f172a', 500);
                }

                if (Math.random() > 0.35) {
                    invoices += 1;
                    if (invoicesEl) {
                        invoicesEl.innerText = invoices;
                        invoicesEl.style.color = '#2563eb';
                        setTimeout(() => invoicesEl.style.color = '#0f172a', 500);
                    }
                }

                if (Math.random() > 0.6 && usersEl) {
                    const userChange = Math.floor(Math.random() * 3) - 1;
                    activeUsers = Math.max(12, Math.min(32, activeUsers + userChange));
                    usersEl.innerText = activeUsers;
                }

                if (efficiencyEl) {
                    const eff = (99.8 + Math.random() * 0.19).toFixed(1);
                    efficiencyEl.innerText = Math.min(eff, 100);
                }

                for (let i = 1; i <= 7; i++) {
                    const bar = document.getElementById(`bar-${i}`);
                    if (bar) {
                        const newHeight = Math.floor(Math.random() * 58) + 35;
                        bar.style.height = `${newHeight}%`;
                    }
                }

                if (Math.random() > 0.5 && logsContainer) {
                    const aiLogs = [
                        "🤖 تحليل التدفقات النقدية: وضع السيولة ممتاز.",
                        "🏦 مطابقة إشعار بنك الخرطوم #984122 تلقائياً.",
                        "🧾 قيد آلي: ترحيل فاتورة مبيعات لميزان المراجعة.",
                        "📦 جرد ذكي: رصيد المستودع الرئيسي مطابق 100%.",
                        "💬 واتساب: تم إرسال كشف الحساب للعميل بصيغة PDF.",
                        "🛡️ درع الأمان: النسخ الاحتياطي المشفر مكتمل."
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
            }, 2400);
        }

        document.addEventListener('DOMContentLoaded', runLiveDashboard);

        // Modal Toggle Helper
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
                if (e.target === modal) closeModalFunc();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('show')) closeModalFunc();
            });
        }

        setupModal('privacy-modal', ['open-privacy'], 'close-privacy');
        setupModal('about-modal', ['open-about'], 'close-about');
        setupModal('vision-modal', ['open-vision'], 'close-vision');
        setupModal('brand-modal', ['open-brand-assets', 'open-logo-guide', 'open-colors'], 'close-brand');
    </script>
</body>
</html>
