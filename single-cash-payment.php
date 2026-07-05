<?php $page_title = 'حركة مالية'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حركة مالية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    
    <style>
        html { font-size: 14.5px; } /* Slightly reduce overall scale to make everything beautifully compact */
        /* -------------------------------------------
           ✨ ADVANCED UI / UX VISUAL EFFECTS ✨
           ------------------------------------------- */
        /* Glassmorphism & Blur */
        .main-panel, .side-panel, .premium-header, .sticky-footer {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        
        /* Fluid Transitions & State Changes */
        .form-input, .btn, .pm-tab-content, .choices__inner {
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1) !important;
        }
        
        /* Hover Effects & Neumorphism / Glow */
        .form-input:hover, .choices__inner:hover, textarea.form-input:hover {
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3) !important;
        }
        .form-input:focus, .choices.is-focused .choices__inner {
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4) !important;
            border-color: #38bdf8 !important;
            transform: translateY(-2px);
        }
        
        /* Amount Field Premium Glow */
        #amount-local:focus {
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.6) !important;
            border-color: #ef4444 !important;
            transform: scale(1.02);
        }

        /* Micro-interactions (Buttons & Payment Tabs) */
        .btn:hover, .pm-tab input:checked + .pm-tab-content {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important;
        }
        .btn:active {
            transform: scale(0.95);
        }

        /* Focus Zones Subtle Glow & Blur */
        .focus-zone {
            transition: filter 0.5s ease, opacity 0.5s ease, transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.5s ease !important;
            border-radius: 16px;
            backdrop-filter: blur(8px);
        }
        .interactive-columns:focus-within .focus-zone:not(:focus-within) {
            filter: grayscale(30%) blur(0.5px) brightness(0.85);
            transform: scale(0.99);
            opacity: 0.9;
        }
        .interactive-columns:focus-within .focus-zone:focus-within {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(-4px);
            z-index: 10;
        }

        /* Continuous Shine Animation for Amount */
        @keyframes subtleShine {
            0% { text-shadow: 0 0 10px rgba(239,68,68,0.3); box-shadow: 0 0 15px rgba(239,68,68,0.1); }
            50% { text-shadow: 0 0 20px rgba(239,68,68,0.8), 0 0 30px rgba(239,68,68,0.4); box-shadow: 0 0 25px rgba(239,68,68,0.3); }
            100% { text-shadow: 0 0 10px rgba(239,68,68,0.3); box-shadow: 0 0 15px rgba(239,68,68,0.1); }
        }
        #amount-local { animation: subtleShine 3s infinite alternate; }

        /* Typography Scale for Clarity */
        .label { font-size: 1.3rem !important; font-weight: 900 !important; color: #f8fafc !important; text-shadow: 0 2px 4px rgba(0,0,0,0.9) !important; }
        .form-input, textarea.form-input { font-size: 1.25rem !important; font-weight: 800 !important; color: #ffffff !important; background: #0f172a !important; border: 1px solid #38bdf8 !important; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5); text-shadow: none !important; }
        .form-input:not(#amount-local) { height: 48px !important; }
        textarea.form-input { height: auto !important; min-height: 48px; }
        #to-statement, #from-statement { font-size: 1.15rem !important; }
        #amount-local { 
            font-size: 3.2rem !important; 
            font-weight: 900 !important; 
            color: #b91c1c !important; /* Medium elegant red */
            background: #d6d3d1 !important; /* Warm, calm beige/gray, not bright white */
            border: 2px solid #991b1b !important;
            text-shadow: none !important;
            box-shadow: inset 0 3px 8px rgba(0, 0, 0, 0.15) !important;
            line-height: 1 !important;
            padding-top: 5px !important;
        }
        #amount-local:focus {
            border-color: #7f1d1d !important;
            background: #e7e5e4 !important; /* Slightly lighter warm gray on focus, still not white */
            box-shadow: 0 0 15px rgba(127, 29, 29, 0.3), inset 0 3px 8px rgba(0, 0, 0, 0.1) !important;
        }
        
        .choices { margin-bottom: 0 !important; }
        .choices__inner { font-size: 1.1rem !important; font-weight: 800 !important; min-height: 48px !important; padding: 6px 12px !important; border-radius: 8px !important; background: #0f172a !important; border: 1px solid #38bdf8 !important; color: #ffffff !important; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5); text-shadow: none !important; }
        .choices__input { color: #ffffff !important; font-size: 1.1rem !important; font-weight: 700 !important; }
        .choices__list--dropdown { background: #1e293b !important; color: #fff !important; border: 1px solid rgba(255,255,255,0.1) !important; z-index: 1000 !important; }
        .choices__list--dropdown .choices__item { font-size: 1.1rem !important; font-weight: 500 !important; }
        .choices__list--dropdown .choices__item--selectable.is-highlighted { background: #38bdf8 !important; color: #fff !important; }
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast {
            padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem;
            display:flex; align-items:center; gap:8px; pointer-events:auto;
            backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5);
            animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both;
            font-family:'Cairo',sans-serif; max-width:90vw;
        }
        .toast.success { background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        .top-action-bar { background: rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.2); }
        .action-tab.active { background: #ef4444; color: #fff; border-color: #ef4444; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .form-input {
            width: 100%; border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 12px; border-radius: 8px;
            font-family: inherit; transition: all 0.2s;
        }
        .form-input:focus { border-color: #ef4444; background: rgba(0,0,0,0.6) !important; outline: none; }
        .form-input.amount { color: #f87171 !important; font-weight: 900 !important; font-family: monospace; }
        
        .form-section-title { 
            font-size: 1.3rem; font-weight: 800; color: #f87171; 
            border-bottom: 2px solid rgba(239, 68, 68, 0.2);
            padding-bottom: 5px; margin-bottom: 15px; 
            display: flex; align-items: center; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .balance-summary {
            background: rgba(249, 115, 22, 0.1);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 8px;
            display: flex;
            padding: 8px 15px;
            gap: 15px;
            font-size: 0.85rem;
            color: #fdba74;
            float: left; /* Left align for RTL */
            margin-top: -5px;
        }
        .bal-item strong { color: #f97316; margin-right: 5px; font-family: monospace; font-size: 1.05rem; }
        .hover-animate { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-animate:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(249,115,22,0.2) !important; }

        /* Custom Radio Buttons */
        .radio-group {
            display: flex; gap: 20px; align-items: center; padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 15px;
        }
        .radio-label {
            display: flex; align-items: center; gap: 8px; cursor: pointer;
            font-weight: 600; color: var(--text-primary);
        }
        .radio-label input[type="radio"] {
            width: 18px; height: 18px; cursor: pointer;
            accent-color: #ef4444;
        }

        .dynamic-section { display: none; background: rgba(0,0,0,0.1); padding: 15px; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.1); margin-bottom: 15px; }
        .dynamic-section.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .checkbox-label {
            display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; margin-top: 10px;
        }
        .checkbox-label input { width: 16px; height: 16px; accent-color: #ef4444; }

        /* Premium Header Styling */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .premium-header {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(249, 115, 22, 0.15), rgba(220, 38, 38, 0.15), rgba(234, 88, 12, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 12px 20px;
            display: flex; justify-content: space-between; align-items: center;
            height: 110px; box-sizing: border-box;
            margin-bottom: 0px; width: 100%;
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

        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #fca5a5; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 {
            margin: 0; font-size: 1.6rem; font-weight: 900;
            background: linear-gradient(to right, #fff, #fca5a5);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }
        .header-controls { display: flex; align-items: center; gap: 15px; }
        
        .icon-btn {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
            width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;
        }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .sub-header-bar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 15px; 
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px; margin-bottom: 15px; margin-top: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .header-group { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
        
        .crystal-pill {
            display: flex; align-items: center; gap: 10px;
            height: 42px; /* Slimmer height */
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 0 12px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.5);
        }
        .title-crystal { border-color: rgba(239, 68, 68, 0.6); box-shadow: 0 0 20px rgba(239, 68, 68, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); }
        .date-crystal { border-color: rgba(56, 189, 248, 0.6); box-shadow: 0 0 20px rgba(56, 189, 248, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); }
        .bal-crystal { border-color: rgba(249, 115, 22, 0.6); box-shadow: 0 0 20px rgba(249, 115, 22, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); gap: 15px; }
        .nav-crystal { border-color: rgba(16, 185, 129, 0.6); box-shadow: 0 0 20px rgba(16, 185, 129, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); padding: 0 8px; }
        
        .icon-box {
            width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .bg-gradient-red { background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; }
        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; }
        .pill-text { font-size: 1.15rem; color: #ffffff; font-weight: 800; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        .date-input { 
            background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); 
            color: #ffffff; border-radius: 6px; padding: 2px 10px;
            font-family: 'Inter', sans-serif; font-size: 1.05rem; font-weight: 700; 
            outline: none; cursor: pointer; height: 32px; line-height: 1; text-align: center;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); transition: all 0.2s;
        }
        .date-input:focus { border-color: #38bdf8; background: rgba(0,0,0,0.8); }
        .date-input::-webkit-calendar-picker-indicator { 
            filter: invert(1); opacity: 0.8; cursor: pointer; 
            margin-left: 8px; width: 16px; height: 16px; padding: 0;
        }

        /* Unused balance-container replaced by crystal-pill */
        .bal-card { display: flex; align-items: center; gap: 8px; }
        .bal-icon { font-size: 1.3rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
        .bal-info { display: flex; flex-direction: column; line-height: 1.1; }
        .bal-label { font-size: 0.8rem; color: #e2e8f0; font-weight: 700; }
        .bal-val { font-size: 1.25rem; color: #f97316; font-family: 'Inter', monospace; font-weight: 900; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        .cash-card .bal-val { color: #10b981; }
        .check-card .bal-val { color: #3b82f6; }
        .loan-card .bal-val { color: #f59e0b; }
        .bal-divider { width: 1px; height: 30px; background: rgba(255,255,255,0.1); }

        /* Unused nav-pill replaced by crystal-pill */
        .nav-btn {
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            border-radius: 8px; background: transparent; border: none; color: #94a3b8; cursor: pointer; transition: all 0.2s;
        }
        .nav-btn:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .nav-badge { display: flex; flex-direction: column; align-items: center; padding: 0 15px; min-width: 90px; }
        .badge-label { font-size: 0.7rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .badge-val { font-size: 1.2rem; color: #ef4444; font-family: 'Inter', monospace; font-weight: 800; text-shadow: 0 0 10px rgba(239, 68, 68, 0.4); }

        .hover-scale { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease; }
        .hover-scale:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 12px 25px rgba(0,0,0,0.3); z-index: 10; }
        
        /* Entrance animation */
        .premium-header { animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* Advanced Layout & Animations */
        .split-layout-wrapper {
            display: flex; flex-direction: row-reverse; gap: 0; margin-top: 15px; width: 100%; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
        }
        
        /* Side Panel */
        .side-panel {
            width: 0; opacity: 0; overflow: hidden; transform: translateX(-80px) scale(0.95);
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(25px); border-radius: 16px;
            border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1); flex-shrink: 0;
            padding: 0; /* Padding is 0 when hidden */
        }
        
        /* When side panel is active */
        .split-layout-wrapper.has-sidebar { gap: 20px; }
        .split-layout-wrapper.has-sidebar .side-panel {
            width: 380px; opacity: 1; transform: translateX(0) scale(1); padding: 25px;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        /* Main Panel */
        .main-panel {
            flex-grow: 1; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
            background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);
            border-radius: 16px; padding: 25px; border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transform-origin: left center;
        }
        
        .interactive-columns {
            display: grid; grid-template-columns: 1fr 1fr; gap: 30px;
        }

        @media (max-width: 900px) {
            .interactive-columns { grid-template-columns: 1fr; }
            .split-layout-wrapper.has-sidebar { flex-direction: column; }
            .split-layout-wrapper.has-sidebar .side-panel { width: 100%; transform: translateX(0) translateY(-20px); }
        }

        /* Crystalline Glassmorphic Transfer Arrow */
        @keyframes rotateRing {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes crystalShineSweep {
            0% { transform: translate(-150%, -150%) rotate(45deg); }
            100% { transform: translate(150%, 150%) rotate(45deg); }
        }

        .epic-arrow-container {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 20; 
            display: flex; align-items: center; justify-content: center; 
            width: 72px; height: 72px; 
            /* Premium Crystalline Background: high glassmorphic translucency with colorful light */
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 50%, rgba(255, 255, 255, 0.02) 100%);
            background-size: 200% 200%;
            border-radius: 50%; 
            /* Sharp, highly reflective, thin double border effect */
            border: 1.5px solid rgba(255, 255, 255, 0.45);
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.6), 
                inset 0 4px 18px rgba(255, 255, 255, 0.35), 
                inset 0 -4px 18px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(25px) saturate(220%);
            -webkit-backdrop-filter: blur(25px) saturate(220%);
            cursor: pointer;
            overflow: hidden; /* To clip the shine reflection */
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        /* Outer Crystalline Orbit Ring with soft green-red gradient path */
        .epic-arrow-container::before {
            content: '';
            position: absolute;
            top: -8px; left: -8px; right: -8px; bottom: -8px;
            border-radius: 50%;
            border: 1.5px dashed rgba(255, 255, 255, 0.25);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(239, 68, 68, 0.15));
            animation: rotateRing 15s linear infinite;
            pointer-events: none;
            z-index: -1;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Moving Diagonal Reflection Gleam */
        .epic-arrow-container::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0) 40%,
                rgba(255, 255, 255, 0.45) 50%,
                rgba(255, 255, 255, 0) 60%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: translate(-150%, -150%) rotate(45deg);
            transition: all 0.6s ease;
            pointer-events: none;
            /* Sweep every 6 seconds automatically */
            animation: crystalShineSweep 6s infinite ease-in-out;
        }
        
        .epic-arrow-container:hover {
            transform: translate(-50%, -50%) scale(1.18);
            border-color: rgba(255, 255, 255, 0.7);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.1) 50%, rgba(255, 255, 255, 0.05) 100%);
            box-shadow: 
                0 20px 45px rgba(0, 0, 0, 0.7), 
                inset 0 6px 24px rgba(255, 255, 255, 0.4), 
                inset 0 -6px 24px rgba(0, 0, 0, 0.2),
                0 0 30px rgba(255, 255, 255, 0.3);
        }
        
        .epic-arrow-container:hover::before {
            top: -12px; left: -12px; right: -12px; bottom: -12px;
            border-color: rgba(255, 255, 255, 0.65);
            animation-duration: 6s; /* Spin faster */
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(239, 68, 68, 0.3));
        }

        .epic-arrow-container:hover::after {
            /* Trigger a faster shine sweep on hover */
            animation: crystalShineSweep 1.5s infinite ease-in-out;
        }

        .epic-arrow-container:active {
            transform: translate(-50%, -50%) scale(0.9);
            transition: all 0.1s ease;
        }

        .transfer-arrow-anim {
            display: block;
            overflow: visible;
        }

        /* Continuous flowing chevrons (Right to Left / Green to Red) */
        .chev {
            opacity: 0.15;
            stroke-width: 3.2px;
            transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .chev-3 { animation: chevFlow3 1.5s infinite; }
        .chev-2 { animation: chevFlow2 1.5s infinite; }
        .chev-1 { animation: chevFlow1 1.5s infinite; }

        @keyframes chevFlow3 {
            0%, 100% { opacity: 0.15; stroke-width: 3.2px; color: rgba(16, 185, 129, 0.25); transform: scale(0.9); }
            15% { 
                opacity: 1; 
                stroke-width: 4.8px;
                color: #00ff88; 
                filter: drop-shadow(0 0 6px #00ff88) drop-shadow(0 0 15px #00ff88) drop-shadow(0 0 30px #00ff88); 
                transform: scale(1.22) translateX(-3px); 
            }
            40% { opacity: 0.15; stroke-width: 3.2px; color: rgba(16, 185, 129, 0.25); transform: scale(0.9); }
        }
        @keyframes chevFlow2 {
            0%, 20%, 100% { opacity: 0.15; stroke-width: 3.2px; color: rgba(251, 191, 36, 0.25); transform: scale(0.9); }
            35% { 
                opacity: 1; 
                stroke-width: 4.8px;
                color: #ffff00; 
                filter: drop-shadow(0 0 8px #ffff00) drop-shadow(0 0 20px #ffff00) drop-shadow(0 0 35px #ffff00); 
                transform: scale(1.25) translateX(-3px); 
            }
            60% { opacity: 0.15; stroke-width: 3.2px; color: rgba(251, 191, 36, 0.25); transform: scale(0.9); }
        }
        @keyframes chevFlow1 {
            0%, 40%, 100% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
            55% { 
                opacity: 1; 
                stroke-width: 4.8px;
                color: #ff0055; 
                filter: drop-shadow(0 0 6px #ff0055) drop-shadow(0 0 15px #ff0055) drop-shadow(0 0 30px #ff0055); 
                transform: scale(1.22) translateX(-3px); 
            }
            80% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
        }

        /* Money Flow Conduit Styles */
        .money-flow-svg {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            pointer-events: none;
            z-index: 10;
            overflow: visible;
        }

        .flow-path-bg {
            stroke-linecap: round;
            opacity: 0.7;
        }

        .flow-path-wave-1 {
            stroke-linecap: round;
            animation: waveLaser1 2.2s linear infinite;
            filter: drop-shadow(0 0 4px #00ff88) drop-shadow(0 0 10px #ffff00);
        }

        .flow-path-wave-2 {
            stroke-linecap: round;
            animation: waveLaser2 1.4s linear infinite;
            animation-delay: 0.5s;
            filter: drop-shadow(0 0 3px #ffff00) drop-shadow(0 0 8px #ff0055);
            opacity: 0.85;
        }

        @keyframes waveLaser1 {
            0% { stroke-dashoffset: 1000; }
            100% { stroke-dashoffset: 0; }
        }

        @keyframes waveLaser2 {
            0% { stroke-dashoffset: 1000; }
            100% { stroke-dashoffset: 0; }
        }

        .focus-zone {
            background: rgba(0, 0, 0, 0.2); padding: 25px; border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.5s cubic-bezier(0.25, 1, 0.2, 1);
            transform-origin: center;
        }
        
        /* When Main Form has focus somewhere, handle siblings */
        .interactive-columns:focus-within .focus-zone {
            opacity: 0.4; transform: scale(0.96) translateY(5px); filter: grayscale(50%);
        }
        /* Elevate and light up the zone that actually contains focus */
        .interactive-columns:focus-within .focus-zone:focus-within {
            opacity: 1; transform: scale(1.02) translateY(-5px); filter: grayscale(0%);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Specialized glowing borders for active zones */
        #zone-recipient:focus-within { border-bottom: 4px solid #ef4444; }
        #zone-sender:focus-within { border-bottom: 4px solid #38bdf8; }

        /* Payment Methods Tabs (Modern Apple-like Segmented Control) */
        .payment-methods-tabs {
            display: flex; gap: 10px; background: rgba(0,0,0,0.4); padding: 8px;
            border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
        }
        .pm-tab { flex: 1; cursor: pointer; }
        .pm-tab input { display: none; }
        .pm-tab-content {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 15px 5px; gap: 10px; border-radius: 10px; transition: all 0.4s cubic-bezier(0.25, 1, 0.2, 1);
            color: #94a3b8; font-weight: 700; font-size: 0.9rem;
        }
        .pm-icon { font-size: 1.8rem; transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s; filter: grayscale(100%); opacity: 0.5; }
        
        .pm-tab input:checked + .pm-tab-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); 
            color: #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            transform: translateY(-4px);
        }
        .pm-tab input:checked + .pm-tab-content .pm-icon {
            transform: scale(1.3); filter: grayscale(0%); opacity: 1;
            text-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }

        /* -------------------------------------------
           💎 CRYSTAL FOOTER BUTTONS 💎
           ------------------------------------------- */
        .crystal-btn {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.95rem !important;
            padding: 7px 18px !important;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
            z-index: 1;
        }
        .crystal-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: crystalShine 6s infinite;
            z-index: -1;
            pointer-events: none;
        }
        @keyframes crystalShine { 0% { left: -100%; } 20% { left: 200%; } 100% { left: 200%; } }
        
        @keyframes tactileBuzz {
            0% { transform: translateY(-5px) scale(1.05) rotate(0deg); }
            25% { transform: translateY(-5px) scale(1.05) rotate(-0.5deg); }
            50% { transform: translateY(-5px) scale(1.05) rotate(0.5deg); }
            75% { transform: translateY(-5px) scale(1.05) rotate(-0.5deg); }
            100% { transform: translateY(-5px) scale(1.05) rotate(0deg); }
        }

        .crystal-btn:hover {
            animation: tactileBuzz 0.1s linear infinite;
            z-index: 10;
        }
        .crystal-btn:active {
            transform: translateY(2px) scale(0.98) !important;
        }
        
        .crystal-btn i, .crystal-btn span.icon, .crystal-btn .btn-icon, .crystal-btn span:first-child {
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-block;
        }
        .crystal-btn:hover i, .crystal-btn:hover span.icon, .crystal-btn:hover .btn-icon, .crystal-btn:hover span:first-child {
            transform: scale(1.2);
        }

        .btn-exit {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(185, 28, 28, 0.4));
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-exit:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.4), rgba(185, 28, 28, 0.6));
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.6), 0 0 30px rgba(239, 68, 68, 0.3), inset 0 0 15px rgba(239, 68, 68, 0.2) !important;
            border-color: #ef4444 !important;
            color: #fff;
        }

        .btn-new {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(3, 105, 161, 0.4));
            border: 1px solid rgba(56, 189, 248, 0.5);
            color: #bae6fd;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-new:hover {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.4), rgba(3, 105, 161, 0.6));
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.6), 0 0 30px rgba(56, 189, 248, 0.3), inset 0 0 15px rgba(56, 189, 248, 0.2) !important;
            border-color: #38bdf8 !important;
            color: #fff;
        }

        .btn-delete {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(194, 65, 12, 0.4));
            border: 1px solid rgba(249, 115, 22, 0.5);
            color: #fdba74;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.4), rgba(194, 65, 12, 0.6));
            box-shadow: 0 0 15px rgba(249, 115, 22, 0.6), 0 0 30px rgba(249, 115, 22, 0.3), inset 0 0 15px rgba(249, 115, 22, 0.2) !important;
            border-color: #f97316 !important;
            color: #fff;
        }

        .btn-print {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(107, 33, 168, 0.4));
            border: 1px solid rgba(168, 85, 247, 0.5);
            color: #d8b4fe;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-print:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(107, 33, 168, 0.6));
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.6), 0 0 30px rgba(168, 85, 247, 0.3), inset 0 0 15px rgba(168, 85, 247, 0.2) !important;
            border-color: #a855f7 !important;
            color: #fff;
        }

        .btn-save-master {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.6), rgba(4, 120, 87, 0.8));
            border: 1px solid rgba(52, 211, 153, 0.8);
            color: #fff;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4), inset 0 2px 10px rgba(255,255,255,0.2);
            font-size: 1.1rem !important;
            padding: 8px 24px !important;
            animation: pulseGlow 2s infinite alternate;
        }
        .btn-save-master:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.8), rgba(4, 120, 87, 1));
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.8), 0 0 40px rgba(16, 185, 129, 0.4), inset 0 0 20px rgba(16, 185, 129, 0.3) !important;
            border-color: #10b981 !important;
            animation: tactileBuzz 0.1s linear infinite !important;
        }
        @keyframes pulseGlow { 0% { box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); } 100% { box-shadow: 0 4px 35px rgba(16, 185, 129, 0.8); } }

        /* Small Crystal Buttons */
        .crystal-btn-sm {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
            z-index: 1;
            padding: 6px 12px;
            font-size: 0.9rem;
            color: #fff;
        }
        .crystal-btn-sm::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: crystalShine 6s infinite;
            z-index: -1;
            pointer-events: none;
        }
        .crystal-btn-sm:hover {
            transform: translateY(-3px) scale(1.05) !important;
        }
        .crystal-btn-sm:active {
            transform: translateY(1px) scale(0.95) !important;
        }
        .crystal-btn-icon-only {
            padding: 6px !important;
            border-radius: 50%;
        }
        .btn-blue-glass {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(3, 105, 161, 0.4));
            border: 1px solid rgba(56, 189, 248, 0.5);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-blue-glass:hover {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.4), rgba(3, 105, 161, 0.6));
            box-shadow: 0 8px 25px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
        }
        .btn-green-glass {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(4, 120, 87, 0.4));
            border: 1px solid rgba(52, 211, 153, 0.5);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-green-glass:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.4), rgba(4, 120, 87, 0.6));
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
        }
        .btn-orange-glass {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(194, 65, 12, 0.4));
            border: 1px solid rgba(249, 115, 22, 0.5);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-orange-glass:hover {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.4), rgba(194, 65, 12, 0.6));
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
        }
        .btn-slate-glass {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.2), rgba(71, 85, 105, 0.4));
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow: 0 4px 15px rgba(148, 163, 184, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-slate-glass:hover {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.4), rgba(71, 85, 105, 0.6));
            box-shadow: 0 8px 25px rgba(148, 163, 184, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
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

    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="width: 100%; max-width: 100%; margin: 0 auto; padding: 5px 5px 100px 5px;">


        <!-- NEW ADVANCED HEADER -->
        <?php include 'includes/header.php'; ?>

        <!-- (Moved SUB HEADER BAR inside main form) -->
        <div class="split-layout-wrapper" id="main-layout-wrapper">
            <!-- Left Side Panel (Hidden by default, slides in when Check/Transfer is active) -->
            <div class="side-panel" id="payment-details-panel">
                <h3 class="form-section-title" style="color: #fbbf24; border-color: rgba(251,191,36,0.2);">
                    <span style="display:inline-block; margin-left:8px; font-size:1.3rem;">📋</span> 
                    تفاصيل <span id="panel-title-type">الشيك</span>
                </h3>
                
                <!-- MOBILE TRANSFER FIELDS -->
                <div id="section-mobile" class="dynamic-section" style="padding:0; background:none; border:none; margin:0;">
                    <div class="form-grid" style="grid-template-columns: 1fr; gap: 15px; margin-bottom: 0;">
                        <div>
                            <label class="label">رقم حساب التحويل</label>
                            <input type="text" class="form-input" id="mobile-transfer-no" placeholder="رقم الحساب..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label">ملاحظات التحويل...</label>
                            <input type="text" class="form-input" id="mobile-note" placeholder="ملاحظات التحويل..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                    </div>
                </div>

                <!-- CHECK FIELDS -->
                <div id="section-check" class="dynamic-section" style="padding:0; background:none; border:none; margin:0;">
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 0;">
                        <div>
                            <label class="label">رقم الشيك</label>
                            <input type="text" class="form-input input-glow-focus hover-glow" id="check-no" placeholder="000000" style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label">اسم البنك</label>
                            <input type="text" class="form-input input-glow-focus hover-glow" id="check-branch" placeholder="الفرع..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label">تاريخ الشيك</label>
                            <input type="date" class="form-input" id="check-date" style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label">تاريخ الاستحقاق</label>
                            <input type="date" class="form-input" id="check-due-date" style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label">رقم الدفتر...</label>
                            <input type="text" class="form-input" id="check-ref" placeholder="رقم الدفتر..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label">رقم الدفتر</label>
                            <input type="text" class="form-input" id="check-ref-no" placeholder="رقم الدفتر..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                    </div>
                    <label class="checkbox-label" style="margin-top: 20px;">
                        <input type="checkbox" id="check-settle-local">
                        إرفاق صورة المستند
                    </label>
                </div>
            </div>

            <!-- Right Side (Main Content area) -->
            <div class="main-panel" id="main-form-panel" style="padding: 10px; margin-bottom: 15px;">

                <div class="interactive-columns" style="display: block;">
                    <!-- ONE SINGLE RECTANGLE -->
                    <div class="focus-zone single-unified-form" style="padding: 25px; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        
                        <!-- Top Row: Date, Balances, Voucher Number -->
                        <div class="sub-header-bar" style="margin-top: 0; margin-bottom: 25px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);">
                            <!-- Title & Date -->
                            <div class="header-group group-primary">
                                <div class="crystal-pill date-crystal hover-scale">
                                    <div class="icon-box bg-gradient-blue">📅</div>
                                    <input type="date" id="hdr-date" class="date-input">
                                </div>
                            </div>
                            
                            <!-- Balances -->
                            <div class="header-group group-balances">
                                <div class="crystal-pill bal-crystal hover-scale">
                                    <div class="bal-card cash-card">
                                        <span class="bal-icon">💵</span>
                                        <div class="bal-info">
                                            <span class="bal-label">نقدية</span>
                                            <strong id="bal-checks" class="bal-val">50</strong>
                                        </div>
                                    </div>
                                    <div class="bal-divider"></div>
                                    <div class="bal-card check-card">
                                        <span class="bal-icon">🧾</span>
                                        <div class="bal-info">
                                            <span class="bal-label">شيكات</span>
                                            <strong id="bal-debt" class="bal-val">70</strong>
                                        </div>
                                    </div>
                                    <div class="bal-divider"></div>
                                    <div class="bal-card loan-card">
                                        <span class="bal-icon">💼</span>
                                        <div class="bal-info">
                                            <span class="bal-label">عهد</span>
                                            <strong id="bal-loan" class="bal-val">120</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Next / Prev -->
                            <div class="header-group group-nav">
                                <div class="crystal-pill nav-crystal hover-scale">
                                    <button class="crystal-btn-sm btn-blue-glass crystal-btn-icon-only prev-btn" onclick="loadPreviousVoucher()" title="السند السابق">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </button>
                                    <div class="nav-badge">
                                        <span class="badge-label">رقم السند</span>
                                        <span id="voucher-number" class="badge-val">000001</span>
                                    </div>
                                    <button class="crystal-btn-sm btn-blue-glass crystal-btn-icon-only next-btn" onclick="loadNextVoucher()" title="السند التالي">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Row 1: Amount | Currency | Rate | Foreign -->
                        <div style="display: grid; grid-template-columns: 1.7fr 1.3fr 1fr 1.5fr; gap: 15px; margin-bottom: 25px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#cbd5e1 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;"><span class="vivid-icon vivid-green" style="width:24px; height:24px; font-size:1.1rem;">💰</span> المبلغ</label>
                                <input type="text" class="form-input amount hover-scale input-glow-focus hover-glow" id="amount-local" data-next="from-account" autofocus value="0" inputmode="numeric" onfocus="this.select()" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, ''); let parts = val.split('.'); parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); this.value = parts.join('.'); calcEquiv('local');" style="height: 55px; font-size: 2.5rem !important; font-weight: 900; text-align: center; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 3px; direction: ltr; border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%;">
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#fbbf24 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">💱 العملة</label>
                                <select class="form-input system-currency-select input-glow-focus hover-glow" id="currency" data-next="exchange-rate" onchange="updateCurrency(); document.getElementById('exchange-rate').focus(); document.getElementById('exchange-rate').select();" style="height: 55px; border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%; font-size: 1.3rem !important; font-weight: 800; padding-right: 5px; padding-left: 5px;">
                                    <option value="SDG">جنيه (SDG)</option>
                                    <option value="USD">دولار (USD)</option>
                                    <option value="AED">درهم (AED)</option>
                                </select>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#fbbf24 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">⚖️ التعادل</label>
                                <input type="text" class="form-input input-glow-focus hover-glow" id="exchange-rate" data-next="amount-foreign" value="1.0" inputmode="decimal" onfocus="this.select()" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); calcEquiv('rate')" style="height: 55px; font-size: 1.8rem !important; font-weight: 900; text-align: center; font-family: 'Inter', sans-serif; letter-spacing: 1px; direction: ltr; border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%;">
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#cbd5e1 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">💵 القيمة</label>
                                <input type="text" class="form-input amount input-glow-focus hover-glow" id="amount-foreign" data-next="from-account" value="0" inputmode="decimal" onfocus="this.select()" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, ''); let parts = val.split('.'); parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); this.value = parts.join('.'); calcEquiv('foreign');" style="height: 55px; font-size: 2.5rem !important; text-align: center; background: #ffffff !important; color: #ef4444 !important; font-weight: 900; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 3px; direction: ltr; border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%;">
                            </div>
                        </div>

                        <!-- Row 2: من حساب | إلى حساب -->
                        <style>
                            .from-to-row .choices__inner .choices__item--selectable {
                                color: #ffffff !important;
                                font-size: 1.25rem !important;
                                font-weight: 900 !important;
                                text-shadow: 0 0 8px rgba(255,255,255,0.4);
                            }
                        </style>
                        <div class="from-to-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 70px; margin-bottom: 25px; position: relative;">
                            
                            <!-- Crystalline Wave Flow Connection -->
                            <svg class="money-flow-svg" width="100%" height="40" viewBox="0 0 1000 40" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="pathColorGradient" x1="100%" y1="0%" x2="0%" y2="0%">
                                        <stop offset="0%" stop-color="#00ff88" />
                                        <stop offset="50%" stop-color="#ffff00" />
                                        <stop offset="100%" stop-color="#ff0055" />
                                    </linearGradient>
                                </defs>
                                <!-- Static subtle path -->
                                <path class="flow-path-bg" d="M 720 20 C 630 5, 570 35, 500 20 C 430 5, 370 35, 280 20" fill="none" stroke="rgba(255, 255, 255, 0.05)" stroke-width="2.5" />
                                <!-- Flowing pulses -->
                                <path class="flow-path-wave-1" d="M 720 20 C 630 5, 570 35, 500 20 C 430 5, 370 35, 280 20" fill="none" stroke="url(#pathColorGradient)" stroke-width="3.5" stroke-dasharray="100 900" stroke-dashoffset="1000" />
                                <path class="flow-path-wave-2" d="M 720 20 C 630 5, 570 35, 500 20 C 430 5, 370 35, 280 20" fill="none" stroke="url(#pathColorGradient)" stroke-width="2.2" stroke-dasharray="50 950" stroke-dashoffset="1000" />
                            </svg>

                            <!-- Epic Animated Transfer Arrow (Centered Absolute) -->
                            <div class="epic-arrow-container" onclick="swapAccounts()" role="button" aria-label="عكس الحسابات" title="عكس حساب الصرف والاستلام">
                                <svg class="transfer-arrow-anim" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline class="chev chev-3" points="20 18 14 12 20 6"></polyline>
                                    <polyline class="chev chev-2" points="13 18 7 12 13 6"></polyline>
                                    <polyline class="chev chev-1" points="6 18 0 12 6 6"></polyline>
                                </svg>
                            </div>

                            <!-- من حساب (Green) -->
                            <div style="background: rgba(16, 185, 129, 0.05); padding: 8px 15px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.3); display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#10b981 !important; margin-bottom:0; font-weight:800; white-space:nowrap; flex-shrink:0;">
                                    <span style="display:flex; align-items:center; justify-content:center; width: 24px; height: 24px; background: rgba(16, 185, 129, 0.15); border-radius: 50%;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                                    </span>
                                    من حساب <span style="color:#ffffff !important;">(صرف)</span>
                                </label>
                                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="from-account">
                                    <input type="text" class="form-input input-glow-focus hover-glow" id="from-account-search" placeholder="ابحث عن حساب الصرف..." onfocus="showAccountDropdown('from')" onblur="hideAccountDropdownDelayed('from')" oninput="filterAccountDropdown('from')" style="width: 100%; padding: 12px 15px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; border: 1px solid rgba(16, 185, 129, 0.5); background: rgba(0,0,0,0.3); color: #fff;">
                                    <div class="custom-dropdown-list" id="from-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none; margin-top: 5px;"></div>
                                </div>
                            </div>
                            <!-- إلى حساب (Red) -->
                            <div style="background: rgba(239, 68, 68, 0.05); padding: 8px 15px; border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.3); display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#ef4444 !important; margin-bottom:0; font-weight:800; white-space:nowrap; flex-shrink:0;">
                                    <span style="display:flex; align-items:center; justify-content:center; width: 24px; height: 24px; background: rgba(239, 68, 68, 0.15); border-radius: 50%;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </span>
                                    إلى حساب <span style="color:#ffffff !important;">(استلام)</span>
                                </label>
                                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="to-account">
                                    <input type="text" class="form-input input-glow-focus hover-glow" id="to-account-search" placeholder="ابحث عن حساب الاستلام..." onfocus="showAccountDropdown('to')" onblur="hideAccountDropdownDelayed('to')" oninput="filterAccountDropdown('to')" style="width: 100%; padding: 12px 15px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; border: 1px solid rgba(239, 68, 68, 0.5); background: rgba(0,0,0,0.3); color: #fff;">
                                    <div class="custom-dropdown-list" id="to-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none; margin-top: 5px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: طريقة الدفع | المستفيد | مركز التكلفة -->
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; background: rgba(255,255,255,0.02); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            
                            <!-- Payment Method (Rightmost) -->
                            <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; margin-bottom:0; white-space:nowrap;">
                                    <span style="font-size:1.1rem; opacity:0.8;">💳</span> طريقة الدفع
                                </label>
                                <div class="payment-methods-tabs" style="margin:0; box-shadow:none; background: transparent; padding: 0; display: flex; gap: 8px;">
                                    <label class="pm-tab" style="flex: 0 0 auto; margin: 0;">
                                        <input type="radio" name="payment_method" value="cash" checked onchange="togglePaymentMethod()">
                                        <div class="pm-tab-content hover-scale" style="padding: 6px 15px; gap: 6px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); flex-direction: row; height: 44px;">
                                            <span class="pm-icon" style="font-size: 1.2rem;">💵</span>
                                            <span class="pm-label" style="font-size: 0.9rem; margin-top: 0;">نقدي</span>
                                        </div>
                                    </label>
                                    <label class="pm-tab" style="flex: 0 0 auto; margin: 0;">
                                        <input type="radio" name="payment_method" value="check" onchange="togglePaymentMethod()">
                                        <div class="pm-tab-content hover-scale" style="padding: 6px 15px; gap: 6px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); flex-direction: row; height: 44px;">
                                            <span class="pm-icon" style="font-size: 1.2rem;">🧾</span>
                                            <span class="pm-label" style="font-size: 0.9rem; margin-top: 0;">شيك</span>
                                        </div>
                                    </label>
                                    <label class="pm-tab" style="flex: 0 0 auto; margin: 0;">
                                        <input type="radio" name="payment_method" value="mobile" onchange="togglePaymentMethod()">
                                        <div class="pm-tab-content hover-scale" style="padding: 6px 15px; gap: 6px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); flex-direction: row; height: 44px;">
                                            <span class="pm-icon" style="font-size: 1.2rem;">📱</span>
                                            <span class="pm-label" style="font-size: 0.9rem; margin-top: 0;">تحويل</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Beneficiary (Middle) -->
                            <div style="display: flex; align-items: center; gap: 10px; flex-grow: 1; min-width: 250px;">
                                <label class="label" style="color:#fbbf24 !important; font-weight:800; display:flex; align-items:center; gap:5px; margin-bottom:0; white-space:nowrap;">
                                    <span class="vivid-icon vivid-yellow" style="width:24px; height:24px; margin-left:0px;"><span class="icon-pulse" style="font-size:0.9rem;">👨‍💼</span></span> المستفيد
                                </label>
                                <input type="text" class="form-input input-glow-focus hover-glow" id="beneficiary-name" data-next="cost-center" placeholder="اسم المستفيد (اختياري)..." style="height: 48px; border-radius: 10px; border-color: rgba(251, 191, 36, 0.4); flex-grow: 1;">
                            </div>

                            <!-- Cost Center (Leftmost) -->
                            <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0; min-width: 200px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; margin-bottom:0; white-space:nowrap;">
                                    <span style="font-size:1.1rem; opacity:0.8;">📊</span> مركز التكلفة
                                </label>
                                <select class="form-input" id="cost-center" data-next="to-statement" style="height: 48px; border-radius: 10px; flex-grow: 1; min-width: 150px;">
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                        </div>

                        <!-- Row 4: البيان -->
                        <div style="margin-bottom: 5px; display: flex; align-items: center; gap: 15px; background: rgba(255,255,255,0.02); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <label class="label" style="display:flex; align-items:center; gap:5px; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">
                                <span style="font-size:1.1rem; opacity:0.8;">📝</span> البيان
                            </label>
                            <div style="display: flex; flex-grow: 1; gap: 10px;">
                                <input type="text" class="form-input input-glow-focus hover-glow" id="to-statement" data-next="btn-save" placeholder="اكتب مبرر أو تفاصيل الدفع (البيان)..." style="height: 48px; border-radius: 10px; flex-grow: 1;">
                                <button type="button" onclick="autoCategorize()" id="btn-auto-categorize" style="background: linear-gradient(135deg, #c084fc, #9333ea); color: white; border: none; border-radius: 10px; padding: 0 15px; height: 48px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: bold; white-space: nowrap; box-shadow: 0 4px 15px rgba(147, 51, 234, 0.4); transition: transform 0.2s;" title="توجيه محاسبي ذكي">
                                    <span style="font-size: 1.2rem;">🪄</span> <span class="btn-text">توقع الحساب</span>
                                </button>
                            </div>
                            <input type="hidden" id="from-statement"> <!-- Hidden to satisfy JS -->
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY FOOTER -->
        <div class="sticky-footer" style="background: rgba(15,23,42,0.85); backdrop-filter: blur(30px); -webkit-backdrop-filter: blur(30px); border-top: 1px solid rgba(56, 189, 248, 0.4); border-left: 1px solid rgba(56, 189, 248, 0.4); border-right: 1px solid rgba(56, 189, 248, 0.4); border-radius: 20px 20px 0 0; padding: 8px 15px; position: sticky; bottom: 0; z-index: 100; box-shadow: 0 -10px 40px rgba(0,0,0,0.6), inset 0 2px 15px rgba(255,255,255,0.05);">
            <div class="footer-actions" style="width: 100%; justify-content: center; gap: 10px; display: flex; flex-wrap: wrap;">
                <button type="button" class="btn crystal-btn btn-exit" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة">🚪 خروج</button>
                <button type="button" class="btn crystal-btn btn-new" onclick="resetVoucher()">✨ مستند جديد</button>
                <button type="button" class="btn crystal-btn btn-print" onclick="printVoucher()">🖨️ طباعة</button>
                <button type="button" class="btn crystal-btn btn-delete" onclick="confirmDelete()">🗑️ حذف السند</button>
                <button type="button" class="btn crystal-btn btn-save-master" id="btn-save" onclick="submitVoucher()">✅ حفظ واعتماد السند</button>
            </div>
        </div>
    </div>

    <script src="js/localforage.min.js"></script>
    
    <script src="js/app.js?v=1783054970.09484"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', () => {
          
          
      });
    </script>
    <script src="js/single-cash-payment.js?v=20260625_3"></script>
    <script>
        let lastEnterTime = 0;
        // Perfected Exact Path Enter Navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                let now = Date.now();
                if (now - lastEnterTime < 200) { e.preventDefault(); return; } // prevent rapid double jump
                lastEnterTime = now;
                
                let current = document.activeElement;
                if (current.tagName === 'TEXTAREA' && current.id !== 'from-statement') return; 

                // Determine sequence based on current focus ID
                let id = current.id;
                
                // If focus is inside a choices container, figure out which one (ignore choices inner IDs)
                if (current.closest('.choices')) {
                    let choicesDiv = current.closest('.choices');
                    if (choicesDiv.classList.contains('is-open')) return; // let choices select item
                    
                    let originalSelect = choicesDiv.querySelector('select');
                    if (originalSelect) id = originalSelect.id;
                    else {
                        let prev = choicesDiv.previousElementSibling;
                        if (prev && prev.tagName === 'SELECT') id = prev.id;
                    }
                }

                if (!id) return;
                
                let nextAction = null;

                if (id === 'amount-local') {
                    nextAction = () => { 
                        document.getElementById('to-account-search').focus();
                    };
                } else if (id === 'to-account-search') {
                    nextAction = () => { let n = document.getElementById('to-statement'); n.focus(); n.select(); };
                } else if (id === 'to-statement') {
                    nextAction = () => { let n = document.getElementById('currency'); n.focus(); };
                } else if (id === 'currency') {
                    nextAction = () => { let n = document.getElementById('exchange-rate'); n.focus(); n.select(); };
                } else if (id === 'exchange-rate') {
                    nextAction = () => { let n = document.getElementById('beneficiary-name'); n.focus(); n.select(); };
                } else if (id === 'amount-foreign') {
                    nextAction = () => { let n = document.getElementById('beneficiary-name'); n.focus(); n.select(); };
                } else if (id === 'beneficiary-name') {
                    nextAction = () => { 
                        document.getElementById('from-account-search').focus();
                    };
                } else if (id === 'from-account-search') {
                    nextAction = () => { 
                        if(window.ccChoice) window.ccChoice.showDropdown(); 
                        let cc = document.getElementById('cost-center').closest('.choices');
                        if(cc) { let inp = cc.querySelector('.choices__input.choices__input--cloned') || cc.querySelector('.choices__input'); if(inp) inp.focus(); }
                        else { let n = document.getElementById('from-statement'); n.focus(); n.select(); } 
                    };
                } else if (id === 'cost-center') {
                    nextAction = () => { let n = document.getElementById('from-statement'); n.focus(); n.select(); };
                } else if (id === 'from-statement') {
                    nextAction = () => { document.getElementById('btn-save').focus(); };
                }

                if (nextAction) {
                    e.preventDefault();
                    // Small timeout ensures Choices.js DOM is ready if it just opened
                    setTimeout(nextAction, 10);
                }
            }
        });

        // Advanced Sliding Animation Logic
        document.addEventListener('DOMContentLoaded', () => {
            const originalToggle = window.togglePaymentMethod;
            
            // Override togglePaymentMethod to handle the sliding wrapper
            window.togglePaymentMethod = function() {
                // Call original logic if it exists (which toggles dynamic-sections inside)
                if(typeof originalToggle === 'function') {
                    originalToggle();
                } else {
                    // Fallback logic if original isn't defined yet
                    const method = document.querySelector('input[name="payment_method"]:checked').value;
                    document.querySelectorAll('.dynamic-section').forEach(el => el.classList.remove('active'));
                    if(method === 'check' || method === 'mobile') {
                        document.getElementById('section-' + method).classList.add('active');
                    }
                }
                
                // Sidebar Logic
                const method = document.querySelector('input[name="payment_method"]:checked').value;
                const wrapper = document.getElementById('main-layout-wrapper');
                const titleType = document.getElementById('panel-title-type');
                
                if(method === 'check' || method === 'mobile') {
                    wrapper.classList.add('has-sidebar');
                    titleType.innerText = method === 'check' ? 'الشيك' : 'التحويل';
                } else {
                    wrapper.classList.remove('has-sidebar');
                }
            };
            
            // Initialize once to set correct state
            setTimeout(() => {
                if(window.togglePaymentMethod) window.togglePaymentMethod();
            }, 100);
        });
    </script>

    <script>
    function autoCategorize() {
        const desc = document.getElementById('to-statement').value.trim();
        if (!desc) {
            alert('الرجاء كتابة البيان أولاً لكي يتوقع الذكاء الاصطناعي الحساب.');
            return;
        }
        
        const btn = document.getElementById('btn-auto-categorize');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span style="font-size: 1.2rem;">⏳</span> <span class="btn-text">جاري التفكير...</span>';
        btn.disabled = true;

        // Gather all accounts to give Gemini context of what choices exist
        let allAccounts = [];
        try {
            allAccounts = allAccounts.concat(JSON.parse(localStorage.getItem('erp_accounts_v1') || '[]'));
            allAccounts = allAccounts.concat(JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]'));
        } catch(e){}

        // Select only relevant fields to save tokens
        let accountListStr = allAccounts.filter(a => !a.deleted).map(a => a.name).join(' | ');

        let promptMsg = `أنت موجه محاسبي آلي. لقد كتب المستخدم البيان التالي لسند صرف نقدية: "${desc}". 
المطلوب: اختار الحساب الأنسب من ضمن القائمة التالية: [${accountListStr}]. 
يجب أن يكون ردك عبارة عن اسم الحساب فقط بدون أي إضافات أو شرح. إذا لم تجد حساباً مطابقاً بدقة اختر أقرب حساب (مثلاً مصروفات عمومية أو مصروفات ضيافة).`;

        fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: promptMsg, context: '' })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            if (data.reply) {
                let suggestedName = data.reply.trim();
                // Find account ID
                let match = allAccounts.find(a => a.name === suggestedName);
                if (match) {
                    // Update UI
                    document.getElementById('to-account').value = match.id;
                    document.getElementById('to-account-search').value = match.name;
                    // Flash success
                    document.getElementById('to-account-search').style.boxShadow = '0 0 15px #10b981';
                    setTimeout(() => document.getElementById('to-account-search').style.boxShadow = '', 2000);
                } else {
                    alert('اقترح الذكاء الاصطناعي: ' + suggestedName + ' ولكن لم يتم العثور عليه في القائمة بدقة.');
                }
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('حدث خطأ في الاتصال بالمساعد الذكي.');
        });
    }
    </script>
    
</body>
</html>
