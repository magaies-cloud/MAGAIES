<?php $page_title = 'المبيعات المباشرة'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المبيعات المباشرة | Al-Sajana Protocol</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
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

        .dash-main { width: 100%; max-width: 1600px; margin: 0 auto; margin: 0 auto; padding: 0px 30px 20px 30px; box-sizing: border-box; display: flex; flex-direction: column; gap: 15px; flex: 1; animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        

        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 32px; height: 32px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #34d399); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2); }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }
        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .clock-container { display: flex; flex-direction: column; align-items: center; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); min-width: 100px; }
        .clock-time { color: #34d399; font-size: 1rem; font-weight: 900; font-variant-numeric: tabular-nums; letter-spacing: 1px; text-shadow: 0 0 8px rgba(16,185,129,0.5); font-family: 'Outfit', sans-serif;}
        .clock-date { color: var(--text-secondary); font-size: 0.65rem; margin-top: 1px; }

        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 36px; height: 36px; border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 10px rgba(0,0,0,0.3); }

        .user-info { display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }

        /* Disable old background on pos-wrapper */
        .pos-wrapper { background: transparent !important; }
        
        @media (max-width: 768px) {
            .header-controls { justify-content: center; width: 100%; flex-wrap: wrap; }
        }

        
        /* ── تصميم ترويسة نقطة البيع الليمونية ── */
        .pos-lemon-title {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, rgba(163, 230, 53, 0.15), rgba(132, 204, 22, 0.05));
            border: 1px solid rgba(163, 230, 53, 0.4);
            padding: 8px 28px;
            border-radius: 50px;
            box-shadow: 0 0 20px rgba(163, 230, 53, 0.2), inset 0 0 10px rgba(163, 230, 53, 0.1);
            animation: pulseLemonGlow 2.5s infinite alternate ease-in-out;
            position: relative;
            overflow: hidden;
            margin: 0;
        }

        .pos-lemon-title::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(163, 230, 53, 0.2) 0%, transparent 60%);
            animation: rotateLemonOrb 8s linear infinite;
        }

        @keyframes pulseLemonGlow {
            from { box-shadow: 0 0 15px rgba(163, 230, 53, 0.1), inset 0 0 5px rgba(163, 230, 53, 0.05); }
            to { box-shadow: 0 0 30px rgba(163, 230, 53, 0.5), inset 0 0 15px rgba(163, 230, 53, 0.25); }
        }

        @keyframes rotateLemonOrb {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .pos-icon-svg {
            width: 32px;
            height: 32px;
            fill: #bef264;
            filter: drop-shadow(0 0 8px rgba(163, 230, 53, 0.8));
            position: relative;
            z-index: 1;
            animation: floatPosIcon 3s infinite alternate ease-in-out;
        }
        
        @keyframes floatPosIcon {
            from { transform: translateY(-2px); }
            to { transform: translateY(2px); }
        }

        .pos-text {
            font-size: 1.7rem;
            font-weight: 900;
            color: #d9f99d;
            text-shadow: 0 0 12px rgba(163, 230, 53, 0.6);
            font-family: 'Cairo', 'Outfit', sans-serif;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pos-en {
            font-size: 1.1rem;
            font-weight: 800;
            color: #a3e635;
            background: rgba(132, 204, 22, 0.15);
            padding: 2px 10px;
            border-radius: 8px;
            border: 1px solid rgba(132, 204, 22, 0.3);
            letter-spacing: 1.5px;
        }

        /* ═══ POS PAGE: إلغاء تضاعف مسافة الشريط الجانبي ═══
           theme.css   → padding-right: 64px
           components.css → margin-right: 62px
           المجموع: 126px فراغ — نحذف الـ padding الزائد */
        body.has-sidebar {
            padding-right: 0 !important;
        }
        /* Toast Notifications */

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
        .toast.warning { background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.4); color:#fbbf24; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }
        @keyframes fadeSlideIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

        /* Cheque fields highlight */
        #cheque-number-group .ctrl-input,
        #cheque-bank-group .ctrl-input {
            border-color: rgba(251,191,36,0.4) !important;
            background: rgba(251,191,36,0.04);
        }
        #cheque-number-group .ctrl-input:focus,
        #cheque-bank-group .ctrl-input:focus {
            border-color: rgba(251,191,36,0.7) !important;
            box-shadow: 0 0 0 2px rgba(251,191,36,0.12);
        }

        /* Custom Dropdown for Customers */
        .custom-dropdown { position: relative; width: 100%; display: flex; gap: 6px; }
        .custom-dropdown-input-wrap { position: relative; flex: 1; }
        .custom-dropdown-list {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px;
            max-height: 300px; overflow-y: auto;
            background: rgba(30, 41, 59, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(251, 191, 36, 0.25);
            border-radius: 10px; box-shadow: 0 15px 50px rgba(0,0,0,0.6);
            z-index: 99999; display: none; flex-direction: column;
            min-width: 320px;
            scrollbar-width: thin;
            scrollbar-color: rgba(251, 191, 36, 0.3) transparent;
        }
        .custom-dropdown-list::-webkit-scrollbar {
            width: 6px;
        }
        .custom-dropdown-list::-webkit-scrollbar-thumb {
            background: rgba(251, 191, 36, 0.3);
            border-radius: 4px;
        }
        .custom-dropdown-list.show { display: flex; }
        td:has(.custom-dropdown-list.show),
        .m-card-full:has(.custom-dropdown-list.show),
        .custom-dropdown-input-wrap:has(.custom-dropdown-list.show) { z-index: 99999 !important; }
        .grid-scroll-wrap { min-height: 350px !important; overflow: visible !important; }

        .custom-dropdown-item {
            padding: 11px 15px; cursor: pointer; font-size: 0.9rem; font-weight: 600;
            color: rgba(241, 245, 249, 0.9); border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .custom-dropdown-item:last-child {
            border-bottom: none;
        }
        .custom-dropdown-item:hover, .custom-dropdown-item.selected {
            background: rgba(251,191,36,0.12); color: var(--gold-primary);
            padding-right: 18px; /* subtle sliding micro-animation */
        }
        .custom-dropdown-empty { padding: 10px 12px; font-size: 0.8rem; color: var(--text-muted); text-align: center; }

        input[type="date"]::-webkit-datetime-edit-text,
        input[type="date"]::-webkit-datetime-edit-month-field,
        input[type="date"]::-webkit-datetime-edit-day-field,
        input[type="date"]::-webkit-datetime-edit-year-field {
            color: #ef4444 !important;
        }

        /* ═══════════════════════════════════════════════
           COMPACT SCREEN LAYOUT — Max space utilization
           ═══════════════════════════════════════════════ */

        /* Remove default pos-wrapper bottom padding — footer handles it */
        .pos-wrapper {
            padding-bottom: 72px !important;
        }

        /* Top bar — tighter */
        .page-breadcrumb { padding: 6px 0 8px; }
        .top-action-bar  { margin-bottom: 8px; }

        /* منطقة الدفع الشرطية */
        .pzone-inner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            animation: fadeSlideIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
        }

        /* ── New Layout: Top row (3 cols) + Full-width items + Notes ── */
        .ds-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Top row: بيانات الفاتورة (مدمجة) + الإجماليات */
        .ds-top-row {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 10px;
            align-items: start;
        }

        /* ── شريط الحالة المالية الأفقي ── */
        .fin-strip-inline.visible {
            flex: 0 0 58% !important; min-width: 560px !important;
            opacity: 1 !important;
            margin-left: 5px;
        }

        .fin-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            margin-top: 0;
            margin-bottom: 0;
            padding: 0 15px;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid transparent;
            border-radius: 8px;
            transition: all 0.4s ease;
            width: auto;
            min-width: 400px;
        }
        .fin-strip.visible {
            max-height: 80px;
            opacity: 1;
            padding: 6px 15px;
            border-color: rgba(56, 189, 248, 0.3);
            margin-bottom: 4px;
        }
        .fin-strip-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 6px;
            border-radius: 10px;
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.05);
            gap: 4px;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: cardPopIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .fin-strip-card:nth-child(1) { animation-delay: 0.00s; }
        .fin-strip-card:nth-child(2) { animation-delay: 0.06s; }
        .fin-strip-card:nth-child(3) { animation-delay: 0.12s; }
        .fin-strip-card:nth-child(4) { animation-delay: 0.18s; }
        .fin-strip-card:nth-child(5) { animation-delay: 0.24s; }
        @keyframes cardPopIn {
            from { opacity:0; transform:translateY(-8px) scale(0.92); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }
        .fin-strip-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .fin-strip-card.warn  { border-color: rgba(251,191,36,0.25); background: rgba(251,191,36,0.05); }
        .fin-strip-card.avail {
            border-color: rgba(56,189,248,0.35);
            background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(16,185,129,0.07));
            box-shadow: 0 0 14px rgba(56,189,248,0.12);
            animation: cardPopIn 0.4s cubic-bezier(0.34,1.56,0.64,1) 0.24s both,
                       breatheGlow 3s ease-in-out 0.7s infinite;
        }
        .fin-strip-lbl {
            font-size: 0.58rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-align: center;
            white-space: nowrap;
        }
        .fin-strip-val {
            font-size: 0.82rem;
            font-weight: 900;
            color: var(--text-primary);
            direction: ltr;
        }
        .fin-strip-card.warn  .fin-strip-val  { color: #fbbf24; }
        .fin-strip-card.avail .fin-strip-val  { color: #38bdf8; text-shadow: 0 0 8px rgba(56,189,248,0.4); font-size: 0.9rem; }
        /* badge الحالة بجانب العميل */
        #fin-status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.65rem; font-weight: 700;
            padding: 2px 8px; border-radius: 12px;
            background: rgba(255,255,255,0.05);
            margin-right: 6px;
            vertical-align: middle;
            transition: all 0.3s;
        }

        /* Professional Equal-Height Panels */
        .pro-panel {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.7), rgba(10, 17, 40, 0.9));
            border: 1px solid rgba(56, 189, 248, 0.15);
            border-radius: 14px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            overflow: hidden;
            padding: 8px 12px;
        }
        .pro-panel::before {
            content: '';
            position: absolute; top:0; left:0; right:0; height:2px;
            background: linear-gradient(90deg, transparent, rgba(56,189,248,0.5), rgba(16,185,129,0.3), transparent);
            opacity: 0.5;
            transition: opacity 0.4s ease;
        }
        .pro-panel:hover {
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 12px 40px 0 rgba(56, 189, 248, 0.15);
            transform: translateY(-2px);
        }
        .pro-panel:hover::before {
            opacity: 1;
        }
        /* Override defaults for pro-panel */
        .pro-panel.panel { margin-bottom: 0; padding: 14px; }

        /* Left column (legacy - now unused wrapper) */
        .ds-left { display: contents; }

        /* ── header-grid no longer needed as wrapper ── */
        .header-grid {
            display: contents;
        }

        /* ── Panel compact overrides ── */
        .panel { padding: 8px 12px; margin-bottom: 0; }
        .panel-title { font-size: 0.67rem; margin-bottom: 6px; }

        /* ── Compact form grid ── */
        .header-form {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }
        .header-form .ctrl-full {
            grid-column: span 2;
        }

        /* ── Compact inputs ── */
        .ctrl-input, .g-input {
            padding: 3px 7px;
            font-size: 0.78rem;
            border-radius: 6px;
        }
        .label { font-size: 0.63rem; margin-bottom: 1px; }
        .ctrl-group { gap: 1px; }

        /* ── Financial panel — vertical list layout ── */
        .fin-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .fin-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(255,255,255,0.03);
            border-left: 2px solid rgba(255,255,255,0.06);
            transition: background 0.2s;
        }
        .fin-row:hover { background: rgba(255,255,255,0.06); }
        .fin-row.warn { border-left-color: rgba(251,191,36,0.5); }
        .fin-row-label {
            font-size: 0.62rem;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .fin-row-value {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--text-primary);
            direction: ltr;
            letter-spacing: 0.02em;
        }
        .fin-row-value.warn  { color: #fbbf24; }
        .fin-row-value.success { color: #34d399; }

        /* الائتمان المتاح — بطاقة مميزة */
        .fin-available-card {
            margin-top: 6px;
            padding: 7px 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(16,185,129,0.08));
            border: 1px solid rgba(56,189,248,0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 12px rgba(56,189,248,0.1);
            animation: breatheGlow 3s ease-in-out infinite;
        }
        @keyframes breatheGlow {
            0%,100% { box-shadow: 0 0 8px rgba(56,189,248,0.08); }
            50% { box-shadow: 0 0 18px rgba(56,189,248,0.22); }
        }
        .fin-available-label {
            font-size: 0.62rem;
            font-weight: 700;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .fin-available-value {
            font-size: 1rem;
            font-weight: 900;
            color: #38bdf8;
            text-shadow: 0 0 10px rgba(56,189,248,0.5);
            direction: ltr;
        }

        /* ── Items panel ── */
        #items-panel { flex: 1; }
        #items-panel .grid-scroll-wrap {
            min-height: unset !important;
            overflow: visible !important;
        }
        .panel-header-row { margin-bottom: 10px; }
        .quick-add-box { max-width: 280px !important; }

        /* ── Table Compact & High Readability ── */
        .pos-grid thead th {
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            padding: 10px 8px !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .pos-grid tbody td {
            padding: 6px 4px !important;
            vertical-align: middle;
        }
        .g-input {
            padding: 6px 8px !important;
            font-size: 0.95rem !important;
            font-weight: bold !important;
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
            transition: all 0.2s ease;
        }
        .g-input:focus {
            border-color: #38bdf8 !important;
            background: rgba(15, 23, 42, 0.8) !important;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.3);
        }
        .del-btn { width: 26px; height: 26px; font-size: 0.85rem; }
        .row-num {
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        .empty-state { padding: 20px 16px; }
        .empty-icon { font-size: 2rem; }

        /* Column Specific Colors */
        #pos-grid input[id^="item-search-"] {
            color: #e2e8f0 !important;
        }
        #pos-grid td:nth-child(4) .g-input,
        #pos-grid input[oninput*="'quantity'"] {
            color: #38bdf8 !important;
            text-align: center;
        }
        #pos-grid td:nth-child(5) .g-input,
        #pos-grid input[oninput*="'price'"] {
            color: #fbbf24 !important;
            text-align: center;
        }
        #pos-grid input[data-base-input] {
            color: #c084fc !important;
            text-align: center;
        }
        #pos-grid span[data-avail] {
            font-size: 0.95rem !important;
            color: #0ea5e9 !important;
            font-weight: 800 !important;
        }
        #pos-grid span[data-rem] {
            font-size: 0.95rem !important;
            color: #f59e0b !important;
            font-weight: 800 !important;
        }
        #pos-grid input[data-fcprice-input] {
            color: #34d399 !important;
            text-align: center;
        }
        #pos-grid span[data-total-fc] {
            font-size: 0.95rem !important;
            color: #34d399 !important;
            font-weight: 800 !important;
        }
        #pos-grid span[data-total] {
            font-size: 1.05rem !important;
            color: #10b981 !important;
            font-weight: 900 !important;
        }

        /* ── RIGHT SIDEBAR PANEL ── */
        .ds-right {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* ── NOTES BAR — full width below table ── */
        .ds-notes-bar {
            background: rgba(10, 17, 40, 0.60);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 12px 16px;
            backdrop-filter: blur(12px);
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: start;
            gap: 12px;
        }
        .ds-notes-bar .label {
            display: block;
            margin-bottom: 0;
            padding-top: 8px;
            white-space: nowrap;
        }
        .ds-notes-bar textarea {
            min-height: 38px;
            max-height: 80px;
            resize: vertical;
            font-size: 0.82rem;
        }

        /* Totals panel — right sidebar */
        .totals-sidebar-panel {
            flex: 1;
        }
        .totals-sidebar-panel::before {
            content: '';
            position: absolute; top:0; left:0; right:0; height:1px;
            background: linear-gradient(90deg, transparent, rgba(56,189,248,0.35), rgba(16,185,129,0.25), transparent);
        }

        .totals-title {
            font-size: 0.62rem;
            font-weight: 800;
            color: rgba(148,163,184,0.9);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Vertical Totals List ── */
        .totals-vlist {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .tv-sep {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 4px 0;
        }
        .tv-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 7px;
            border-radius: 5px;
            background: rgba(0,0,0,0.12);
            transition: background 0.2s;
        }
        .tv-row:hover { background: rgba(255,255,255,0.05); }
        .tv-row.editable { background: rgba(56,189,248,0.04); border: 1px solid rgba(56,189,248,0.1); }
        .tv-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .tv-value {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-primary);
            direction: ltr;
        }
        /* Grand total card */
        .tv-grand {
            margin-top: 6px;
            padding: 8px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(52,211,153,0.05));
            border: 1px solid rgba(16,185,129,0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tv-grand-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #34d399;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .tv-grand-value {
            font-size: 1.1rem;
            font-weight: 900;
            color: #34d399;
            text-shadow: 0 0 12px rgba(52,211,153,0.5);
            direction: ltr;
        }
        /* tl-input used inside tv-row */
        .tv-row .tl-input {
            width: 75px;
            padding: 2px 5px;
            font-size: 0.7rem;
            border-radius: 4px;
        }

        /* Divider line */
        .totals-divider {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 4px 0;
        }
        /* tl-input base */
        .tl-input {
            padding: 2px 6px;
            font-size: 0.7rem;
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.3);
            color: var(--text-primary);
            font-family: var(--font-base);
            font-weight: 700;
            text-align: left;
            direction: ltr;
            outline: none;
            width: 75px;
        }
        .tl-input:focus {
            border-color: rgba(56,189,248,0.5);
            box-shadow: 0 0 0 2px rgba(56,189,248,0.1);
        }

        /* Notes inside right panel */
        .notes-panel {
            background: rgba(10, 17, 40, 0.70);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 12px 14px;
            backdrop-filter: blur(12px);
        }
        .notes-panel .label { display: block; margin-bottom: 6px; }
        .ctrl-textarea { min-height: 52px; resize: vertical; font-size: 0.8rem; }

        /* ── Sticky footer adjustments ── */
        .sticky-footer { padding: 8px 20px; }
        .ft-value { font-size: 1.05rem; }
        .footer-actions .btn { padding: 7px 14px; font-size: 0.78rem; }

        /* ── Back arrow ── */
        .back-arrow-btn { width: 34px; height: 34px; border-radius: 10px; }

        /* ── Responsive collapse ── */
        @media (max-width: 1100px) {
            .ds-top-row { grid-template-columns: 2fr 1fr 240px; }
        }
        @media (max-width: 860px) {
            .ds-top-row { grid-template-columns: 1fr; }
            .ds-right { position: static; }
            .ds-notes-bar { grid-template-columns: 1fr; }
        }

        /* 5. Dynamic Totals Card Animation */
        #form-fields-card {
            transition: flex 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), min-width 0.8s, max-width 0.8s;
            flex: 75;
            min-width: 450px;
        }
        #totals-calc-card {
            transition: flex 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), min-width 0.8s, opacity 0.5s ease-in-out, transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform-origin: left center;
            flex: 25;
            min-width: 250px;
        }
        .hidden-totals {
            flex: 0 0 0% !important;
            min-width: 0 !important;
            max-width: 0 !important;
            padding: 0 !important;
            border-width: 0 !important;
            opacity: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }
        .expanded-form {
            flex: 1 1 100% !important;
            max-width: 100% !important;
        }
    
    /* Kinetic Animation for Sales Layout */
    #sales-top-container {
        display: flex; gap: 20px; align-items: stretch; width: 100%; margin-bottom: 15px;
    }
    #sales-top-container #form-fields-card {
        flex: 1; 
        min-width: 0; 
        transition: flex 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), max-width 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    }
    #sales-top-container #totals-calc-card {
        display: flex !important;
        flex: 0 0 360px;
        min-width: 360px;
        max-width: 360px;
        opacity: 1;
        overflow: hidden;
        white-space: nowrap;
        background: linear-gradient(180deg, rgba(15,23,42,0.8), rgba(30,58,138,0.4));
        border: 1px solid rgba(56, 189, 248, 0.3);
        border-top: 3px solid #38bdf8 !important;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        transform-origin: right center;
    }
    #sales-top-container #totals-calc-card.hidden-totals {
        flex: 0 0 0px !important;
        min-width: 0px !important;
        max-width: 0px !important;
        opacity: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-width: 0 !important;
    }
    .tot-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 3px 0; border-bottom: 1px dashed rgba(255,255,255,0.1);
    }
    .tot-lbl { font-size: 0.75rem; font-weight: 700; color: #94a3b8; }
    .tot-val { font-size: 0.85rem; font-weight: 900; color: #fff; font-family: 'Outfit', sans-serif; }
    .tot-grand {
        margin-top: 15px; background: rgba(56, 189, 248, 0.1); padding: 15px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3); box-shadow: 0 0 15px rgba(56,189,248,0.2);
        display: flex; justify-content: space-between; align-items: center;
    }
    .tot-grand .lbl { font-size: 0.75rem; font-weight: 900; color: #38bdf8; }
    .tot-grand .val { font-size: 1.15rem; font-weight: 900; color: #fff; font-family: 'Outfit', sans-serif; text-shadow: 0 0 10px rgba(255,255,255,0.3); }
</style>


    <style>
        .hub-action-card {
            background: rgba(15, 23, 42, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 15px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            height: 80px; /* Reduced height to ~50% of the previous tall card */
        }
        .hub-action-card::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            transition: all 0.6s ease;
        }
        .hub-action-card:hover::after {
            left: 200%;
        }
        .hub-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.15);
        }
        .hub-action-card .icon {
            font-size: 1.8rem;
            margin-bottom: 0;
            padding: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
        }
        .hub-action-card:hover .icon {
            transform: scale(1.1) rotate(-5deg);
        }
        .hub-action-card .text-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: right;
        }
        .hub-action-card .title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .hub-action-card .subtitle {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
            text-align: right;
            margin: 0;
        }
        .hub-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        .hub-badge.completed { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .hub-badge.draft { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .hub-badge.preliminary-badge { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }
        #invoice-editor-view.preliminary-active .th-wh,
        #invoice-editor-view.preliminary-active .td-wh,
        #invoice-editor-view.preliminary-active .m-wh-group {
            display: none !important;
        }
        #invoice-editor-view.preliminary-active .pro-serial-number {
            color: #c084fc !important;
            text-shadow: 0 0 10px rgba(192, 132, 252, 0.5) !important;
        }
        #invoice-editor-view.preliminary-active #form-fields-card {
            border-color: rgba(168, 85, 247, 0.5) !important;
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.2), inset 0 0 15px rgba(255, 255, 255, 0.05) !important;
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
    
        /* User request: Sky Blue outer border for invoice header inputs */
        .ds-top-row .ctrl-input, 
        .ds-top-row select.ctrl-input, 
        .ds-top-row input.ctrl-input,
        .ds-top-row textarea.ctrl-input,
        .pro-panel .ctrl-input {
            border: 1px solid #38bdf8 !important;
            box-shadow: 0 0 3px rgba(56, 189, 248, 0.3) !important;
            background: rgba(30, 41, 59, 0.85) !important;
        }
        .ds-top-row .ctrl-input:focus, 
        .ds-top-row select.ctrl-input:focus, 
        .ds-top-row input.ctrl-input:focus,
        .ds-top-row textarea.ctrl-input:focus,
        .pro-panel .ctrl-input:focus {
            border: 1px solid #7dd3fc !important;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.6) !important;
            outline: none !important;
        }
        
        /* Make sure date input gets the same treatment */
        #hdr-date {
            border: 1px solid #38bdf8 !important;
            color: #38bdf8 !important;
            text-shadow: 0 0 5px rgba(56, 189, 248, 0.3) !important;
        }
        #hdr-date-label {
            color: #38bdf8 !important;
            text-shadow: 0 0 5px rgba(56, 189, 248, 0.3) !important;
        }
        .date-icon-wrap {
            background: rgba(56, 189, 248, 0.1) !important;
            border: 1px solid rgba(56, 189, 248, 0.3) !important;
        }

        .hub-table { width: 100%; border-collapse: separate !important; border-spacing: 0 8px !important; }
        .hub-table thead th {
            background: rgba(15, 23, 42, 0.8) !important; 
            color: #38bdf8 !important; 
            font-size: 0.85rem !important; 
            font-weight: 800 !important;
            padding: 12px 15px !important; 
            text-align: center !important; 
            position: sticky; top: 0; z-index: 5;
            border-top: 1px solid rgba(56, 189, 248, 0.2) !important;
            border-bottom: 2px solid rgba(56, 189, 248, 0.3) !important; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .hub-table thead th:first-child { 
            border-radius: 10px 0 0 10px !important; 
            border-right: 1px solid rgba(56, 189, 248, 0.2) !important; 
            text-align: right !important;
        }
        .hub-table thead th:nth-child(2) { text-align: right !important; }
        .hub-table thead th:nth-child(3) { text-align: right !important; }
        .hub-table thead th:nth-child(4) { text-align: right !important; }
        .hub-table thead th:last-child { 
            border-radius: 0 10px 10px 0 !important; 
            border-left: 1px solid rgba(56, 189, 248, 0.2) !important; 
        }
        .hub-table tbody tr {
            background: rgba(15, 23, 42, 0.45) !important;
            transition: all 0.3s ease !important;
        }
        .hub-table tbody tr:hover {
            background: rgba(56, 189, 248, 0.08) !important; 
            transform: translateY(-2px) !important; 
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4) !important;
        }
        .hub-table tbody tr:hover td {
            border-top-color: rgba(56, 189, 248, 0.25) !important;
            border-bottom-color: rgba(56, 189, 248, 0.25) !important;
        }
        .hub-table tbody tr:hover td:first-child {
            border-right-color: rgba(56, 189, 248, 0.25) !important;
        }
        .hub-table tbody tr:hover td:last-child {
            border-left-color: rgba(56, 189, 248, 0.25) !important;
        }
        .hub-table tbody td {
            padding: 12px 10px !important; 
            border-bottom: 1px solid rgba(255,255,255,0.03) !important;
            border-top: 1px solid rgba(255,255,255,0.03) !important;
            font-size: 0.95rem !important; 
            font-weight: 700 !important;
            vertical-align: middle !important;
            text-align: center;
        }
        .hub-table tbody td:first-child { 
            border-radius: 12px 0 0 12px !important; 
            border-right: 1px solid rgba(255,255,255,0.03) !important; 
            text-align: right; 
        }
        .hub-table tbody td:nth-child(2) { text-align: right; }
        .hub-table tbody td:nth-child(3) { text-align: right; }
        .hub-table tbody td:nth-child(4) { text-align: right; }
        .hub-table tbody td:last-child { 
            border-radius: 0 12px 12px 0 !important; 
            border-left: 1px solid rgba(255,255,255,0.03) !important; 
        }

        /* Split Board Table Custom Style */
        #split-board-step table.pos-grid {
            border-collapse: separate !important;
            border-spacing: 0 6px !important;
        }
        #split-board-step table.pos-grid thead th {
            background: rgba(15, 23, 42, 0.85) !important;
            padding: 10px 8px !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            border-top: 1px solid rgba(255,255,255,0.06) !important;
            border-bottom: 2px solid rgba(255,255,255,0.12) !important;
        }
        #split-board-step table.pos-grid thead th:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(255,255,255,0.06) !important;
        }
        #split-board-step table.pos-grid thead th:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(255,255,255,0.06) !important;
        }
        #split-board-step table.pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.02) !important;
            transition: background 0.2s ease !important;
        }
        #split-board-step table.pos-grid tbody tr:hover {
            background: rgba(255, 255, 255, 0.06) !important;
        }
        #split-board-step table.pos-grid tbody td {
            padding: 10px 8px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.02) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02) !important;
            font-size: 0.95rem !important;
            vertical-align: middle !important;
        }
        #split-board-step table.pos-grid tbody td:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(255, 255, 255, 0.02) !important;
        }
        #split-board-step table.pos-grid tbody td:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(255, 255, 255, 0.02) !important;
        }

        /* Split Select Step Table Custom Style */
        #split-select-step table.pos-grid {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
        }
        #split-select-step table.pos-grid thead th {
            background: rgba(15, 23, 42, 0.85) !important;
            padding: 12px 10px !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            color: #38bdf8 !important;
            border-top: 1px solid rgba(56, 189, 248, 0.2) !important;
            border-bottom: 2px solid rgba(56, 189, 248, 0.3) !important;
        }
        #split-select-step table.pos-grid thead th:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(56, 189, 248, 0.2) !important;
            text-align: right !important;
        }
        #split-select-step table.pos-grid thead th:nth-child(2) { text-align: right !important; }
        #split-select-step table.pos-grid thead th:nth-child(3) { text-align: right !important; }
        #split-select-step table.pos-grid thead th:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(56, 189, 248, 0.2) !important;
        }
        #split-select-step table.pos-grid tbody tr {
            background: rgba(15, 23, 42, 0.45) !important;
            transition: all 0.3s ease !important;
        }
        #split-select-step table.pos-grid tbody tr:hover {
            background: rgba(56, 189, 248, 0.08) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        }
        #split-select-step table.pos-grid tbody tr:hover td {
            border-top-color: rgba(56, 189, 248, 0.25) !important;
            border-bottom-color: rgba(56, 189, 248, 0.25) !important;
        }
        #split-select-step table.pos-grid tbody tr:hover td:first-child {
            border-right-color: rgba(56, 189, 248, 0.25) !important;
        }
        #split-select-step table.pos-grid tbody tr:hover td:last-child {
            border-left-color: rgba(56, 189, 248, 0.25) !important;
        }
        #split-select-step table.pos-grid tbody td {
            padding: 14px 12px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.03) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            vertical-align: middle !important;
        }
        #split-select-step table.pos-grid tbody td:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(255, 255, 255, 0.03) !important;
            text-align: right;
        }
        #split-select-step table.pos-grid tbody td:nth-child(2) { text-align: right; }
        #split-select-step table.pos-grid tbody td:nth-child(3) { text-align: right; }
        #split-select-step table.pos-grid tbody td:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(255, 255, 255, 0.03) !important;
        }

    </style>
    <script>
        if(localStorage.getItem('erp_theme') === 'light') document.documentElement.classList.add('light-theme');
        function toggleTheme() {
            document.documentElement.classList.toggle('light-theme');
            localStorage.setItem('erp_theme', document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
        }
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script defer src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
 <style>
 /* Choices custom overrides */
 .choices__inner { background-color: rgba(30,41,59,0.8) !important; border: 1px solid rgba(255,255,255,0.15) !important; color: #fff !important; border-radius: 8px !important; min-height: 42px !important; }
 .choices__list--dropdown { background-color: #1e293b !important; color: #fff !important; border: 1px solid #38bdf8 !important; z-index: 1000 !important; }
 .choices__item--selectable.is-highlighted { background-color: #38bdf8 !important; color: #fff !important; }
 .choices__input { color: #fff !important; }

        /* Resizable columns */
        .pos-grid th {
            position: relative;
        }
        .resizer {
            width: 5px;
            cursor: col-resize;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: transparent;
            z-index: 10;
        }
        .resizer:hover, .resizing {
            background-color: var(--gold-primary);
        }

        /* Context Menu */
        #col-context-menu {
            position: absolute;
            z-index: 100000;
            background: var(--velvet-panel-hover);
            border: 1px solid var(--gold-border);
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            display: none;
            min-width: 150px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .col-toggle-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        .col-toggle-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
 </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- ═══ Add Customer Modal ═══ -->
    <div class="modal-overlay" id="add-customer-modal">
        <div class="modal-card" style="max-width: 600px;">
            <div class="modal-header">
                <h3>➕ إضافة عميل جديد</h3>
                <button class="modal-close" onclick="closeModal('add-customer-modal')">✕</button>
            </div>
            <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="label">اسم العميل</label>
                    <input type="text" class="ctrl-input" id="new-cust-name" placeholder="اسم العميل...">
                </div>
                <div class="form-group">
                    <label class="label">رقم الهاتف</label>
                    <input type="tel" class="ctrl-input" id="new-cust-phone" placeholder="09XXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="label">الواتساب</label>
                    <input type="tel" class="ctrl-input" id="new-cust-whatsapp" placeholder="رقم الواتساب">
                </div>
                <div class="form-group">
                    <label class="label">الإيميل</label>
                    <input type="email" class="ctrl-input" id="new-cust-email" placeholder="example@email.com">
                </div>
                <div class="form-group">
                    <label class="label">درجة السرية</label>
                    <select class="ctrl-input" id="new-cust-security">
                        <option value="1">1 (الأقل سرية)</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5 (الأعلى سرية)</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="label">العنوان</label>
                    <input type="text" class="ctrl-input" id="new-cust-address" placeholder="عنوان العميل...">
                </div>
                <div class="form-group">
                    <label class="label">الرصيد الافتتاحي</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" id="new-cust-opening" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
                <div class="form-group">
                    <label class="label">حد الائتمان (المسموح)</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" id="new-cust-credit" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('add-customer-modal')">إلغاء</button>
                <button class="btn btn-primary" onclick="addNewCustomer()">💾 حفظ العميل</button>
            </div>
        </div>
    </div>

    <div class="pos-wrapper dash-main">

        <!-- ═══ BREADCRUMB + TOP BAR ═══ -->
                <!-- Unified Header -->
        
    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Unified Premium Header -->
    <?php $header_theme_class = 'header-theme-sales'; ?>
    <?php include 'includes/header.php'; ?>


        
    <!-- ═══ SALES HUB VIEW ═══ -->
    <div id="sales-hub-view" style="display: flex; flex-direction: column; height: 100%; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 0 5px;">
        <!-- Action Cards (فاتورة جديدة, تقسيم الفاتورة, تسليم, فاتورة مبدئية) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <button onclick="openInvoiceEditor('pending_delivery')" class="hub-action-card" style="background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(56,189,248,0.02)); border-color: rgba(56,189,248,0.2);">
                <div class="icon" style="color: #38bdf8; text-shadow: 0 0 10px rgba(56,189,248,0.4); box-shadow: inset 0 0 10px rgba(56,189,248,0.1);">➕</div>
                <div class="text-content">
                    <div class="title" style="color: #38bdf8;">فاتورة جديدة</div>
                    <div class="subtitle">إنشاء فاتورة مبيعات (بدون خصم مخزون)</div>
                </div>
            </button>
            <button onclick="openSplitInvoiceView()" class="hub-action-card" style="background: linear-gradient(135deg, rgba(236,72,153,0.1), rgba(236,72,153,0.02)); border-color: rgba(236,72,153,0.2);">
                <div class="icon" style="color: #ec4899; text-shadow: 0 0 10px rgba(236,72,153,0.4); box-shadow: inset 0 0 10px rgba(236,72,153,0.1);">✂️</div>
                <div class="text-content">
                    <div class="title" style="color: #ec4899;">تقسيم الفاتورة</div>
                    <div class="subtitle">تقسيم فاتورة مبيعات إلى فاتورتين مستقلتين</div>
                </div>
            </button>
            <button onclick="openDeliveryHub()" class="hub-action-card" style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(16,185,129,0.02)); border-color: rgba(16,185,129,0.2);">
                <div class="icon" style="color: #10b981; text-shadow: 0 0 10px rgba(16,185,129,0.4); box-shadow: inset 0 0 10px rgba(16,185,129,0.1);">🚚</div>
                <div class="text-content">
                    <div class="title" style="color: #10b981;">تسليم</div>
                    <div class="subtitle">تسليم فواتير المبيعات (خصم من المخزون)</div>
                </div>
            </button>
            <button onclick="openInvoiceEditor('preliminary')" class="hub-action-card" style="background: linear-gradient(135deg, rgba(168,85,247,0.1), rgba(168,85,247,0.02)); border-color: rgba(168,85,247,0.2);">
                <div class="icon" style="color: #a855f7; text-shadow: 0 0 10px rgba(168,85,247,0.4); box-shadow: inset 0 0 10px rgba(168,85,247,0.1);">📋</div>
                <div class="text-content">
                    <div class="title" style="color: #a855f7;">فاتورة مبدئية</div>
                    <div class="subtitle">عرض أسعار / غير ملزمة</div>
                </div>
            </button>
        </div>

        <!-- Recent Transactions Table -->
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center;">
                <span>📋 العمليات الأخيرة (Recent Transactions)</span>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="hub-search" class="v-input" placeholder="بحث برقم الفاتورة أو العميل..." style="width: 250px;" oninput="filterHubTransactions()">
                    <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.05);" onclick="loadHubTransactions()">🔄 تحديث</button>
                </div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid hub-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الفاتورة</th>
                            <th style="text-align:right;">التاريخ</th>
                            <th style="text-align:right;">العميل</th>
                            <th style="text-align:right;">الصنف</th>
                            <th style="text-align:center;">الكمية</th>
                            <th style="text-align:center;">السعر</th>
                            <th style="text-align:center;">الإجمالي</th>
                            <th style="text-align:center;">المخزن</th>
                            <th style="text-align:center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="hub-transactions-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <div class="empty-state" id="hub-empty-state" style="display: none;">
                    <div class="icon">📭</div>
                    <h3>لا توجد عمليات مسجلة</h3>
                    <p>اضغط على "فاتورة جديدة" للبدء.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ SPLIT INVOICE VIEW ═══ -->
    <div id="split-invoice-view" style="display: none; flex-direction: column; height: 100%; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 0 5px;">
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">

            
            <!-- Step 1: Select Invoice -->
            <div id="split-select-step" style="display: flex; flex-direction: column; flex: 1; gap: 15px;">
                <div style="display: flex; gap: 15px; align-items: center; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 5px; font-weight: bold;">البحث عن فاتورة نشطة للتقسيم:</label>
                        <input type="text" id="split-invoice-search" class="v-input" placeholder="ابحث برقم الفاتورة، اسم العميل، أو الإجمالي..." oninput="filterSplitInvoices()">
                    </div>
                    <button class="btn-action" style="width: auto; height: 42px; margin-top: auto; background: rgba(255,255,255,0.05);" onclick="loadSplitInvoices()">🔄 تحديث القائمة</button>
                    <button class="btn-action" style="width: auto; height: 42px; margin-top: auto; background: rgba(239,68,68,0.15); color: #f87171;" onclick="showSalesHub()">رجوع</button>
                </div>
                
                <div class="grid-scroll-wrap" style="flex: 1; max-height: 450px; overflow-y: auto; margin-top: 10px;">
                    <table class="pos-grid" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align:right;">رقم الفاتورة</th>
                                <th style="text-align:right;">التاريخ</th>
                                <th style="text-align:right;">العميل</th>
                                <th style="text-align:center;">طريقة الدفع</th>
                                <th style="text-align:left;">المبلغ الإجمالي</th>
                                <th style="text-align:center;">الحالة</th>
                                <th style="text-align:center;">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody id="split-invoices-body">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                    <div class="empty-state" id="split-empty-state" style="display: none;">
                        <div class="icon">📭</div>
                        <h3>لا توجد فواتير صالحة للتقسيم</h3>
                        <p>تأكد من وجود فواتير مبيعات نشطة وغير مسلمة بعد.</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Split Board -->
            <div id="split-board-step" style="display: none; flex-direction: column; gap: 6px;">
                <div id="split-header-info" style="display: flex; background: linear-gradient(135deg, rgba(236,72,153,0.15), rgba(99,102,241,0.05)); padding: 8px; border-radius: 12px; border: 1px solid rgba(236,72,153,0.2); width: 100%;">
                    <!-- Filled dynamically -->
                </div>

                <div class="grid-scroll-wrap" style="max-height: 260px; overflow-y: auto; background: rgba(0,0,0,0.15); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    <table class="pos-grid" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align:right;">اسم الصنف</th>
                                <th style="text-align:center; width: 110px;">الكمية الأصلية</th>
                                <th style="text-align:left; width: 130px;">السعر</th>
                                <th style="text-align:center; width: 230px; color: #38bdf8;">كمية الفاتورة (أ)</th>
                                <th style="text-align:center; width: 230px; color: #ec4899;">كمية الفاتورة (ب)</th>
                                <th style="text-align:left; width: 160px;">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody id="split-items-body">
                            <!-- Filled dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Summaries & Actions -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 8px;">
                    <!-- Invoice A Card -->
                    <div class="neo-card" style="border: 1px solid rgba(56, 189, 248, 0.3); background: rgba(56, 189, 248, 0.02); padding: 8px 12px; border-radius: 12px; position: relative; overflow: hidden; margin: 0;">
                        <div style="position: absolute; top:0; right:0; width:4px; height:100%; background:#38bdf8;"></div>
                        <h4 style="color: #38bdf8; margin: 0 0 8px 0; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                            <span>📄</span> الفاتورة الفرعية الأولى (أ)
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الإجمالي قبل الخصم:</span><span id="split-subtotal-a" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الخصم (النسبي):</span><span id="split-discount-a" style="font-family: monospace; font-weight: 800; font-size: 1.05rem; color: #f87171;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الضريبة (النسبية):</span><span id="split-tax-a" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <hr style="border-color: rgba(255,255,255,0.05); margin: 4px 0;">
                            <div style="display: flex; justify-content: space-between; font-weight: 900; color: #38bdf8; font-size: 1.25rem;"><span>الصافي النهائي:</span><span id="split-grand-a" style="font-family: monospace;">0.00</span></div>
                        </div>
                    </div>

                    <!-- Invoice B Card -->
                    <div class="neo-card" style="border: 1px solid rgba(236, 72, 153, 0.3); background: rgba(236, 72, 153, 0.02); padding: 8px 12px; border-radius: 12px; position: relative; overflow: hidden; margin: 0;">
                        <div style="position: absolute; top:0; right:0; width:4px; height:100%; background:#ec4899;"></div>
                        <h4 style="color: #ec4899; margin: 0 0 8px 0; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                            <span>📄</span> الفاتورة الفرعية الثانية (ب)
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الإجمالي قبل الخصم:</span><span id="split-subtotal-b" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الخصم (النسبي):</span><span id="split-discount-b" style="font-family: monospace; font-weight: 800; font-size: 1.05rem; color: #f87171;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الضريبة (النسبية):</span><span id="split-tax-b" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <hr style="border-color: rgba(255,255,255,0.05); margin: 4px 0;">
                            <div style="display: flex; justify-content: space-between; font-weight: 900; color: #ec4899; font-size: 1.25rem;"><span>الصافي النهائي:</span><span id="split-grand-b" style="font-family: monospace;">0.00</span></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 5px;">
                    <button class="btn-action" style="width: auto; padding: 8px 20px; background: rgba(255,255,255,0.05);" onclick="cancelCurrentSplit()">إلغاء وتراجع</button>
                    <button class="btn-action" style="width: auto; padding: 8px 30px; background: linear-gradient(135deg, #ec4899, #8b5cf6); color: white; font-weight: bold; border: none; box-shadow: 0 4px 15px rgba(236,72,153,0.3);" onclick="submitInvoiceSplit()">تأكيد واعتماد التقسيم ✂️</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DELIVERY HUB VIEW ═══ -->
    <div id="delivery-hub-view" style="display: none; flex-direction: column; height: 100%; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 0 5px;">
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #10b981;">🚚 فواتير قيد التسليم (Pending Delivery)</span>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="delivery-search" class="v-input" placeholder="بحث..." style="width: 250px;" oninput="filterDeliveryTransactions()">
                    <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.05);" onclick="loadDeliveryTransactions()">🔄 تحديث</button>
                    <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(239,68,68,0.1); color: #ef4444;" onclick="showSalesHub()">رجوع</button>
                </div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الفاتورة</th>
                            <th style="text-align:right;">التاريخ</th>
                            <th style="text-align:right;">العميل</th>
                            <th style="text-align:center;">المستودع</th>
                            <th style="text-align:center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="delivery-transactions-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <div class="empty-state" id="delivery-empty-state" style="display: none;">
                    <div class="icon">📭</div>
                    <h3>لا توجد فواتير قيد التسليم</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ INVOICE EDITOR VIEW ═══ -->
    <div id="invoice-editor-view" style="display: none; flex-direction: column; height: 100%; flex: 1;">
    
        <!-- ═══ MAIN BODY ═══ -->
        <div class="ds-body" style="padding-top: 5px;">

            <!-- ══ TOP ROW: بيانات الفاتورة (مدمجة) ══ -->
            <div id="sales-top-container">

                
                <!-- Form Fields -->
                <div class="neo-card expanded-form" id="form-fields-card" style="
                    margin-bottom: 0; 
                    padding: 25px; 
                    border-radius: 16px; 
                    background: rgba(15, 23, 42, 0.15); 
                    border: 1px solid rgba(56, 189, 248, 0.3); 
                    backdrop-filter: blur(25px); 
                    -webkit-backdrop-filter: blur(25px); 
                    box-shadow: 0 0 20px rgba(56, 189, 248, 0.15), inset 0 0 15px rgba(255,255,255,0.05);
                    position: relative;
                    overflow: visible;
                    width: 100%;
                ">
                    <!-- Neon glow effect -->
                    <div style="position:absolute; top:0; left:0; width:100%; height:100%; overflow:hidden; border-radius:16px; z-index:0; pointer-events:none;">
                        <div style="position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle, rgba(56,189,248,0.1) 0%, transparent 60%);"></div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 18px; position:relative; z-index:1;">
                        
                        
                        
    <!-- ROW 1 WRAPPER (Dynamic Split) -->
    <div style="display: flex; width: 100%; gap: 15px; margin-bottom: 12px; transition: all 0.4s ease;">
        
        <!-- Row 1: Invoice Number & Date -->
        <div id="invoice-info-box" style="flex: 1; display: flex; gap: 12px; width: 100%; background: linear-gradient(90deg, rgba(30,58,138,0.2), rgba(15,23,42,0.4)); padding: 4px 10px; border-radius: 12px; border: 1px solid rgba(56,189,248,0.25); box-shadow: 0 4px 15px rgba(0,0,0,0.2), inset 0 2px 5px rgba(255,255,255,0.05); align-items: center; justify-content: space-between;">
            <div class="h-form-group" style="flex: 1.1; display:flex; align-items:center; gap:8px; margin:0; min-width: 0;">
                <label class="h-label" style="margin:0; color:#38bdf8; font-size: 0.95rem; font-weight:bold; white-space:nowrap; text-shadow: 0 0 5px rgba(56,189,248,0.3);">رقم الفاتورة:</label>
                <div style="display:flex; align-items:center; justify-content:space-between; flex:1; background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); min-width: 0;">
                    <button class="pro-serial-btn" onclick="loadPreviousInvoice()" title="السابقة" style="background: transparent; border: none; color: #fff; font-size: 1.1rem; cursor: pointer; padding: 0 5px; opacity:0.7; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">❮</button>
                    <span id="inv-number" class="pro-serial-number" style="font-size: 0.95rem; font-family: 'Outfit', sans-serif; font-weight: 900; color: #38bdf8; letter-spacing: 1px; text-shadow: 0 0 10px rgba(56,189,248,0.5); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">INV-000001</span>
                    <input type="hidden" id="hdr-inv-no">
                    <button class="pro-serial-btn" onclick="loadNextInvoice()" title="التالية" style="background: transparent; border: none; color: #fff; font-size: 1.1rem; cursor: pointer; padding: 0 5px; opacity:0.7; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">❯</button>
                </div>
            </div>

            <div style="width: 1px; height: 28px; background: rgba(255,255,255,0.1); margin: 0 2px;"></div>

            <div class="h-form-group" style="flex: 0.9; display:flex; align-items:center; gap:8px; margin:0; min-width: 0;">
                <label class="h-label" id="hdr-date-label" style="margin:0; color:#ef4444; font-size: 0.95rem; font-weight:bold; white-space:nowrap; text-shadow: 0 0 5px rgba(239,68,68,0.3);">التاريخ:</label>
                <input type="date" class="v-input ctrl-input" id="hdr-date" style="flex:1; text-align: center; background: rgba(0,0,0,0.3); border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; color:#ef4444; padding:3px 6px; font-family:'Outfit',sans-serif; font-weight:bold; font-size:1.05rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); min-width: 0;">
            </div>
        </div>

        <!-- RIGHT: Financial Strip -->
        <div id="fin-strip" class="fin-strip-inline" style="flex: 0; width: 0; opacity: 0; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: flex;">
            <div style="width: 100%; background: linear-gradient(90deg, rgba(15,23,42,0.4), rgba(30,58,138,0.2)); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 4px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2), inset 0 2px 5px rgba(255,255,255,0.05); min-width: auto;">
                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                    <span style="font-size: 1.1rem;">📊</span>
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight: 800; color:#fff; font-size:0.75rem; white-space: nowrap;">الحالة المالية للعميل</span>
                        <span id="fin-status-badge" style="display:none;"><span class="status-dot neutral"></span></span>
                    </div>
                </div>
                
                <div style="display:flex; gap: 12px; align-items: center; flex: 1; justify-content: flex-end; min-width: 0;">
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-size:0.6rem; color:#94a3b8; font-weight:700; white-space: nowrap;">الرصيد الدفتري</span>
                        <span id="fin-ledger" style="font-weight:800; color:#fff; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-size:0.6rem; color:#f59e0b; font-weight:700; white-space: nowrap;">شيكات معلقة</span>
                        <span id="fin-checks" style="font-weight:800; color:#f59e0b; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-size:0.6rem; color:#ef4444; font-weight:700; white-space: nowrap;">المديونية</span>
                        <span id="fin-total-debt" style="font-weight:800; color:#ef4444; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-size:0.6rem; color:#38bdf8; font-weight:700; white-space: nowrap;">حد الائتمان</span>
                        <span id="fin-credit-limit" style="font-weight:800; color:#38bdf8; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; background: rgba(16,185,129,0.15); padding: 3px 8px; border-radius: 8px; border: 1px solid rgba(16,185,129,0.3); flex-shrink: 0; text-align: center;">
                        <span style="font-size:0.65rem; color:#34d399; font-weight:800; white-space: nowrap;">✨ المتاح</span>
                        <span id="fin-remaining" style="font-weight:900; color:#34d399; font-size:0.85rem; white-space: nowrap;">0</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Row 2: Customer, Currency, Payment -->
                        <div style="display: flex; gap: 20px; width: 100%;">
                            
                            <!-- Customer Field (35% width) -->
                            <div class="h-form-group" style="flex: 0 0 35%; display:flex; align-items:center; gap:10px;">
                                <label class="h-label" style="margin:0; white-space:nowrap;">اسم العميل <span id="fin-status-badge" style="display:none;"><span class="status-dot neutral"></span></span></label>
                                <div style="display: flex; flex-direction: row; align-items: center; gap: 8px; flex: 1; width: 100%;">
                                    
                                    <div class="custom-dropdown" style="flex:1;">
                                        <div class="custom-dropdown-input-wrap" style="position: relative;">
                                            <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم العميل..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown()" style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15); padding-left: 95px !important;" autocomplete="one-time-code">
                                            <button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('hdr-customer').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.8rem; padding: 4px 8px; height: 32px; display: none;" title="دفتر الأستاذ العام">📖 دفتر الأستاذ</button>
                                            <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange()">
                                            <div class="custom-dropdown-list" id="customer-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none;">
                                                <!-- Populated via JS -->
                                            </div>
                                        </div>
                                    </div>

                                    <button onclick="openModal('add-customer-modal')" style="flex-shrink: 0; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: 1px solid rgba(16,212,143,0.3); width: 38px; height: 38px; border-radius: 8px; font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2); transition: 0.2s;" title="إضافة عميل">
                                        ➕
                                    </button>
                                </div>
                            </div>

                            <!-- Exchange Rate Field (40% width) -->
                            <div class="h-form-group" style="flex: 0 0 40%; display:flex; align-items:center; gap:10px;">
                                <label class="h-label" style="margin:0; white-space:nowrap;">سعر الصرف</label>
                                <div style="display: flex; flex-direction: row; align-items: center; gap: 8px; flex: 1; width: 100%;">
                                    <select class="v-input ctrl-input system-currency-select" id="hdr-currency" onchange="document.getElementById('hdr-exchange-rate').disabled = (this.value === 'SDG'); document.getElementById('hdr-exchange-rate').value = (this.value === 'SDG') ? '1' : ''; recalcAll();" style="flex: 1; min-width:0; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                        <option value="SDG">🇸🇩 جنيه</option>
                                        <option value="USD">🇺🇸 دولار</option>
                                        <option value="SAR">🇸🇦 ريال</option>
                                        <option value="AED">🇦🇪 درهم</option>
                                        <option value="EGP">🇪🇬 مصري</option>
                                    </select>
                                    <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="1" disabled oninput="recalcAll()" style="flex: 1; min-width:0; text-align: center; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="الصرف">
                                </div>
                            </div>

                            <!-- Payment Method Field (25% width) -->
                            <div class="h-form-group" style="flex: 1; display:flex; align-items:center; gap:10px;">
                                <label class="h-label" style="margin:0; white-space:nowrap;">طريقة الدفع</label>
                                <div style="flex: 1; width: 100%;">
                                    <select class="v-input ctrl-input" id="hdr-payment-method" onchange="togglePaymentAccount()" style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                        <option value="cash">نقدي فوري</option>
                                        <option value="credit">آجل (على الحساب)</option>
                                        <option value="bank">تحويل بنكي</option>
                                        <option value="cheque">شيك</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div id="payment-details-row" style="display: none; gap: 15px; width: 100%; padding: 6px 10px; background: rgba(56, 189, 248, 0.1); border: 1px dashed rgba(56, 189, 248, 0.4); border-radius: 8px;">
                            <div class="h-form-group" style="flex: 1; min-width: 120px; display:flex; align-items:center; gap:8px;">
                                  <label class="h-label" id="pay-detail-1-label" style="font-size:0.9rem; color:#38bdf8; margin:0; white-space:nowrap;">رقم المرجع</label>
                                <div class="h-input-wrapper" style="flex:1;">
                                    <input type="text" class="v-input ctrl-input" id="pay-detail-1" placeholder="..." style="height: 34px !important; min-height: 34px !important; font-size: 0.95rem !important; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                            </div>
                            <div class="h-form-group" style="flex: 1; min-width: 120px; display:flex; align-items:center; gap:8px;">
                                  <label class="h-label" id="pay-detail-2-label" style="font-size:0.9rem; color:#38bdf8; margin:0; white-space:nowrap;">اسم البنك</label>
                                <div class="h-input-wrapper" style="flex:1;">
                                    <input type="text" class="v-input ctrl-input" id="pay-detail-2" placeholder="..." style="height: 34px !important; min-height: 34px !important; font-size: 0.95rem !important; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                            </div>
                            <div class="h-form-group" style="flex: 1; min-width: 120px; display:flex; align-items:center; gap:8px;">
                                  <label class="h-label" id="pay-detail-3-label" style="font-size:0.9rem; color:#38bdf8; margin:0; white-space:nowrap;">الفرع (أو تاريخ)</label>
                                <div class="h-input-wrapper" style="flex:1;">
                                    <input type="text" class="v-input ctrl-input" id="pay-detail-3" placeholder="..." style="height: 34px !important; min-height: 34px !important; font-size: 0.95rem !important; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Treasury, Warehouse, Cost Center -->
                        <div id="warehouse-treasury-row" style="display: flex; gap: 20px; width: 100%;">
                            <div class="h-form-group" id="payment-account-group" style="flex: 1; display:flex; align-items:center; gap:10px;">
                                <label class="h-label" style="margin:0; white-space:nowrap; min-width:80px;">الصندوق/الخزنة</label>
                                <div style="flex:1;">
                                    <select class="v-input ctrl-input" id="hdr-payment-account" style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);"></select>
                                </div>
                            </div>

                            <div class="h-form-group" style="flex: 1; display:flex; align-items:center; gap:10px;">
                                <label class="h-label" style="margin:0; white-space:nowrap; min-width:60px;">المخزن</label>
                                <div style="flex:1;">
                                    <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange()" style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);"></select>
                                </div>
                            </div>

                            <div class="h-form-group" style="flex: 1; display:flex; align-items:center; gap:10px;">
                                <label class="h-label" style="margin:0; white-space:nowrap; min-width:75px;">مركز التكلفة</label>
                                <div style="flex:1;">
                                    <select class="v-input ctrl-input" id="hdr-cost-center" style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                        <option value="cc-001">الفرع الرئيسي</option>
                                        <option value="cc-002">فرع بحري</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Description -->
                        <div style="display: flex; gap: 20px; width: 100%;">
                            <div class="h-form-group" style="flex: 1; display:flex; align-items:center; gap:10px;">
                                <label class="h-label" style="margin:0; white-space:nowrap; min-width:80px;">البيان</label>
                                <div style="flex:1;">
                                    <input type="text" class="v-input ctrl-input" id="hdr-description" placeholder="ملاحظات، أرقام مرجعية..." style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Due Date (hidden normally) -->
                        <div class="h-form-group" id="due-date-group" style="display:none; border-color: rgba(245,158,11,0.3); background: rgba(245,158,11,0.05); padding:10px; border-radius:8px;">
                            <label class="h-label" style="color: #f59e0b;">الاستحقاق</label>
                            <div class="h-input-wrapper">
                                <input type="date" class="v-input ctrl-input" id="hdr-due-date" style="color:#f59e0b; background: rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.2);">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Totals Card (Now second in DOM, rendering on the Left in RTL) -->
                <div class="neo-card totals-card hidden-totals" id="totals-calc-card" style="margin-bottom: 0; flex-direction: column; justify-content: space-between; border-top: 2px solid var(--gold-primary);">
                    <div class="neo-card-title" style="margin-bottom: 10px; text-align: center; color: var(--gold-primary); font-size: 0.95rem;">🧮 الإجماليات</div>
                    
                    <!-- Hidden: keeps JS id reference working -->
                    <span id="t-invoice-total" style="display:none;">0</span>
                    
                    <div class="tot-row" style="padding: 3px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="tot-lbl">الترحيل</span>
                        <input type="text" inputmode="decimal" class="v-input tot-input tl-input" id="t-transport" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))" style="width:120px; text-align:left; background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.4); border-radius: 6px; padding: 2px 8px; height: 26px; color: #fff; font-size: 0.85rem; line-height: 1;">
                    </div>
                    <div class="tot-row" style="padding: 3px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="tot-lbl">تكلفة العمالة</span>
                        <input type="text" inputmode="decimal" class="v-input tot-input tl-input" id="t-labor" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))" style="width:120px; text-align:left; background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.4); border-radius: 6px; padding: 2px 8px; height: 26px; color: #fff; font-size: 0.85rem; line-height: 1;">
                    </div>
                    <div class="tot-row" style="padding: 3px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="tot-lbl">إضافات أخرى</span>
                        <span class="tot-val" id="t-additions" style="color:var(--gold-primary);">+ 0</span>
                    </div>
                    <div class="tot-row" style="padding: 3px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="tot-lbl">الضريبة</span>
                        <span class="tot-val" id="t-tax" style="color:var(--danger-color);">+ 0</span>
                    </div>
                    <div class="tot-row" style="padding: 3px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="tot-lbl">خصم مسموح</span>
                        <span class="tot-val" id="t-discount" style="color:var(--danger-color);">- 0</span>
                    </div>

                    <div class="tot-grand">
                        <div class="lbl">الصافي النهائي</div>
                        <div class="val" id="t-grand">0 <span style="color: #ef4444; font-size: 0.8em; text-shadow: none;">SDG</span></div>
                    </div>
                </div>
            </div><!-- end ds-top-row -->


            <!-- ══ FULL-WIDTH: جدول الأصناف ══ -->
            <div class="panel" id="items-panel" style="z-index: 50;">
                <div class="panel-header-row" style="display: flex; align-items: center;">
                    <div class="panel-title">📦 أصناف الفاتورة</div>
                    <div class="quick-add-box" style="flex: 1; max-width: 340px; margin-right: 12px; position: relative; z-index: 99999; display: flex; gap: 6px; align-items: center;">
                        <input type="text" class="g-input" id="quick-item-search" placeholder="بحث صنف أو باركود..."
                            style="background: rgba(56,189,248,0.08); border: 1px solid rgba(56,189,248,0.35); border-radius: 8px; color: var(--text-primary); padding: 5px 10px; flex: 1;"
                            onfocus="this.select(); showQuickItemDropdown()"
                            onblur="hideQuickItemDropdownDelayed()"
                            oninput="filterQuickItemDropdown()" autocomplete="new-password">
                        <div class="custom-dropdown-list" id="quick-item-dropdown-list" style="text-align:right; top: 100%; left: 0; right: 0; position: absolute;"></div>
                    </div>
                    

                </div>

                <!-- Desktop Table -->
                <div class="grid-scroll-wrap">
                    <table class="pos-grid" id="pos-grid">
                        <thead>
                            <tr style="background: rgba(15, 23, 42, 0.8); border-bottom: 2px solid rgba(56, 189, 248, 0.3);">
                                <th data-col-id="0" class="th-num" style="width: 3%;">#</th>
                                <th data-col-id="1" class="th-item" style="width: 18%;">الصنف</th>
                                <th data-col-id="2" class="th-unit" style="width: 7%;">الوحدة</th>
                                <th data-col-id="3" class="th-qty" style="width: 7%;">الكمية</th>
                                <th data-col-id="4" class="th-price" style="width: 8%;">السعر</th>
                                <th data-col-id="5" class="th-baseqty" style="width: 7%;">العدد</th>
                                <th data-col-id="6" class="th-avail" style="width: 7%; color:#0ea5e9;">المتاح</th>
                                <th data-col-id="7" class="th-rem" style="width: 7%; color:#f59e0b;">المتبقي</th>
                                <th data-col-id="8" class="th-fcprice" style="width: 8%; color:#10b981;">سعر العملة</th>
                                <th data-col-id="9" class="th-fctotal" style="width: 9%; color:#10b981;">إجمالي العملة</th>
                                <th data-col-id="10" class="th-wh" style="width: 8%;">المخزن</th>
                                <th data-col-id="11" class="th-total" style="width: 8%;">الإجمالي</th>
                                <th data-col-id="12" class="th-del" style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="grid-body"></tbody>
                    </table>
                </div>

                <!-- Centered Add Row Button -->
                <div id="add-row-container" style="display: flex; justify-content: center; margin-top: 15px; margin-bottom: 10px;">
                    <button type="button" onclick="addRow()" id="add-row-btn" style="
                        width: 40px;
                        height: 40px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: linear-gradient(135deg, #10b981, #059669);
                        color: white;
                        border: none;
                        border-radius: 50%;
                        cursor: pointer;
                        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
                        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        outline: none;
                    " onmouseover="this.style.transform='scale(1.15) rotate(90deg)'; this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='scale(1) rotate(0deg)'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.25)';" title="إضافة سطر">
                        <span style="font-size: 1.2rem; font-weight: bold; line-height: 1;">➕</span>
                    </button>
                </div>

                <!-- Mobile Cards -->
                <div class="mobile-cards" id="mobile-cards"></div>

                <!-- Empty State -->
                <div class="empty-state" id="empty-state">
                    <p>لم يتم إضافة أصناف بعد</p>
                    <span class="empty-hint">اضغط <strong>"+ صنف"</strong> أو <kbd>Ctrl+N</kbd></span>
                </div>
            </div><!-- end items-panel -->

            <!-- ══ NOTES BAR: ملاحظات الفاتورة (full width below table) ══ -->
            <div class="ds-notes-bar" style="display: none;">
                <label class="label">📝 ملاحظات الفاتورة</label>
                <textarea class="ctrl-input" id="inv-notes" rows="2" placeholder="أي ملاحظات إضافية على الفاتورة..."></textarea>
            </div>

        </div><!-- end ds-body -->



        
        <div class="items-toolbar" style="display: flex; align-items: center; justify-content: center; padding: 15px; border-radius: 12px; background: rgba(18, 15, 25, 0.8); border: 1px solid rgba(255,255,255,0.05); margin-top: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            <div class="toolbar-actions" style="display: flex; gap: 12px; width: 100%; align-items: center;">
                <button class="btn-action" id="attach-btn" style="flex: 1; height: 45px; background: #8b5cf6; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(139, 92, 246, 0.3);" onmouseover="this.style.background='#7c3aed'; this.style.boxShadow='0 4px 15px rgba(139, 92, 246, 0.5)';" onmouseout="this.style.background='#8b5cf6'; this.style.boxShadow='0 2px 10px rgba(139, 92, 246, 0.3)';" onclick="document.getElementById('hdr-attachment').click()">📎 إرفاق مستند</button>
                <input type="file" id="hdr-attachment" accept="image/*,application/pdf" style="display:none;" onchange="document.getElementById('attach-btn').innerText = this.files.length > 0 ? '📎 تم الإرفاق' : '📎 إرفاق مستند'; document.getElementById('attach-btn').style.background = this.files.length > 0 ? '#10b981' : '#8b5cf6';">
                
                <button class="btn-action btn-draft" style="flex: 1; height: 45px; background: #f97316; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(249, 115, 22, 0.3);" onmouseover="this.style.background='#ea580c'; this.style.boxShadow='0 4px 15px rgba(249, 115, 22, 0.5)';" onmouseout="this.style.background='#f97316'; this.style.boxShadow='0 2px 10px rgba(249, 115, 22, 0.3)';" onclick="saveDraft()">💾 مسودة</button>
                
                <button class="btn-action btn-delete" style="flex: 1; height: 45px; background: #ef4444; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 4px 15px rgba(239, 68, 68, 0.5)';" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 2px 10px rgba(239, 68, 68, 0.3)';" onclick="confirmDeleteTransaction()">❌ إلغاء</button>
                
                <button type="button" class="btn-action btn-preview" id="btn-preview-invoice" style="flex: 1; height: 45px; background: #3b82f6; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3); display: none; align-items: center; justify-content: center;" onmouseover="this.style.background='#2563eb'; this.style.boxShadow='0 4px 15px rgba(59, 130, 246, 0.5)';" onmouseout="this.style.background='#3b82f6'; this.style.boxShadow='0 2px 10px rgba(59, 130, 246, 0.3)';" onclick="printCurrentInvoice()">👁️ معاينة</button>
                
                <button type="button" class="btn-action btn-whatsapp" id="btn-whatsapp-invoice" style="flex: 1.2; height: 45px; background: #25d366; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(37, 211, 102, 0.3); display: none; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.background='#1da851'; this.style.boxShadow='0 4px 15px rgba(37, 211, 102, 0.5)';" onmouseout="this.style.background='#25d366'; this.style.boxShadow='0 2px 10px rgba(37, 211, 102, 0.3)';" onclick="sendCurrentInvoiceWA()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>إرسال للواتساب</span>
                </button>
                
                <span style="display:none;" id="footer-grand"></span>
                <button class="btn-action btn-submit" style="flex: 1.5; height: 45px; box-shadow: 0 2px 10px rgba(56, 189, 248, 0.4); background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #fff; border: none; font-size: 1.1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.boxShadow='0 6px 20px rgba(56, 189, 248, 0.6)';" onmouseout="this.style.boxShadow='0 2px 10px rgba(56, 189, 248, 0.4)';" onclick="submitInvoice()">✅ تأكيد وحفظ</button>
            </div>
        </div>

        <div id="adjustments-panel" style="display: none;">
            <input type="text" id="discount-amt" value="0">
            <input type="number" id="discount-pct" value="0">
            <input type="text" id="tax-amt" value="0">
            <input type="number" id="tax-pct" value="0">
            <input type="text" id="addition-amt" value="0">
            <input type="number" id="addition-pct" value="0">
        </div>

        <!-- ═══ STICKY FOOTER ═══ -->
        </div>
</div>
</div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js"></script>
    <script defer src="js/app.js?v=1783054967.56221"></script>
    <script defer src="js/print-engine.js"></script>
    

    <script defer src="js/direct-sales-v3.js?v=<?php echo time(); ?>"></script>

    <script>
        // Sales Hub Logic
        let currentSalesView = 'hub'; // 'hub' or 'editor'

        function showSalesHub() {
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('sales-hub-view').style.display = 'flex';
            currentSalesView = 'hub';
            
            // Restore header title to "المبيعات المباشرة"
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">🛒</span> المبيعات المباشرة';
            }
            
            loadHubTransactions();
            
            // Adjust header Back button behavior
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { 
                    if (document.referrer && document.referrer.indexOf('general-ledger.php') !== -1) { 
                        history.back(); 
                    } else { 
                        window.location.href = 'dashboard-pro.php'; 
                    } 
                };
                backBtn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>';
            }
        }

        function openInvoiceEditor(type = 'pending_delivery', isEdit = false) {
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'flex';
            currentSalesView = 'editor';
            currentSaleType = type;
            
            // Adjust header Back button behavior to return to hub
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { showSalesHub(); };
                backBtn.innerHTML = '<span style="font-weight:bold; font-size:0.8rem; padding: 0 4px;">العودة للقائمة</span>';
                backBtn.style.width = 'auto';
                backBtn.style.padding = '0 10px';
            }

            // Set active tab
            document.querySelectorAll('#sale-type-tabs .action-tab').forEach(t => {
                t.classList.remove('active');
            });
            const activeTab = document.getElementById('tab-' + type);
            if(activeTab) activeTab.classList.add('active');

            // Handle Preliminary UI changes
            const editorView = document.getElementById('invoice-editor-view');
            const whTreasuryRow = document.getElementById('warehouse-treasury-row');
            
            const headerTitle = document.querySelector('.header-page-title');
            if (type === 'preliminary') {
                if (headerTitle) headerTitle.innerHTML = '<span style="color:#c084fc;">📋 فاتورة مبدئية</span> <span style="font-size:0.7em;color:#f87171;">(غير ملزمة)</span>';
                if (editorView) editorView.classList.add('preliminary-active');
                if (whTreasuryRow) whTreasuryRow.style.display = 'none';
            } else {
                if (headerTitle) headerTitle.innerHTML = '<span class="header-page-title-icon">🛒</span> فاتورة مبيعات';
                if (editorView) editorView.classList.remove('preliminary-active');
                if (whTreasuryRow) whTreasuryRow.style.display = 'flex';
            }
            
            // If creating a new invoice, reset the editor form and load the next invoice number
            if (!isEdit && typeof onSuccessNewInvoice === 'function') {
                onSuccessNewInvoice(false);
            }
            
            window.dispatchEvent(new Event('resize'));
        }

        function openDeliveryHub() {
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'flex';
            currentSalesView = 'delivery';
            
            // Change header title to "تسليم مبيعات"
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">🚚</span> تسليم مبيعات';
            }
            
            loadDeliveryTransactions();
        }

        function openSplitInvoiceView() {
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'flex';
            currentSalesView = 'split';
            
            // Change header title to "تقسيم الفواتير"
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">✂️</span> تقسيم الفواتير';
            }
            
            if (typeof loadSplitInvoices === 'function') {
                loadSplitInvoices();
            }
        }

        function loadHubTransactions() {
            const tbody = document.getElementById('hub-transactions-body');
            const emptyState = document.getElementById('hub-empty-state');
            
            // Fetch from localStorage
            const savedData = localStorage.getItem('erp_sales_invoices_v1');
            let invoices = [];
            if (savedData) {
                try { invoices = JSON.parse(savedData); } catch(e) {}
            }
            
            if (!invoices || invoices.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'flex';
                return;
            }

            // Load customers to map IDs to names
            let customers = [];
            try {
                let rawCusts = localStorage.getItem('erp_customers_v1');
                if (typeof rawCusts === 'string') customers = JSON.parse(rawCusts) || [];
                else if (rawCusts && typeof rawCusts === 'object') customers = rawCusts;
            } catch(e) {}

            function getCustomerName(id) {
                if (id === 'walk-in' || !id) return 'عميل نقدي';
                const c = customers.find(x => x && x.id === id);
                return c ? c.name : id;
            }

            // Load warehouses to map IDs to names
            let warehouses = [];
            try {
                let rawWh = localStorage.getItem('erp_warehouses_v1');
                if (typeof rawWh === 'string') warehouses = JSON.parse(rawWh) || [];
                else if (rawWh && typeof rawWh === 'object') warehouses = rawWh;
            } catch(e) {}

            function getWarehouseName(id) {
                if (!id) return '—';
                const w = warehouses.find(x => x && x.id === id);
                return w ? w.name : id;
            }

            // Load items to map IDs to names
            let items = [];
            try {
                let rawItems = localStorage.getItem('erp_items_v1');
                if (typeof rawItems === 'string') items = JSON.parse(rawItems) || [];
                else if (rawItems && typeof rawItems === 'object') items = rawItems;
            } catch(e) {}

            function getItemName(id) {
                const item = items.find(x => x && x.id === id);
                return item ? item.name : id || 'صنف غير معروف';
            }

            emptyState.style.display = 'none';
            tbody.innerHTML = '';
            
            // Sort by date/time descending
            invoices.sort((a,b) => {
                const dateA = new Date(a.timestamp || a.date).getTime();
                const dateB = new Date(b.timestamp || b.date).getTime();
                return dateB - dateA;
            });
            
            invoices.forEach(inv => {
                const tr = document.createElement('tr');
                
                let itemText = 'بدون أصناف';
                let qtyText = '0';
                let priceText = '0';
                
                const lines = inv.items || [];
                if (lines.length === 1) {
                    const line = lines[0];
                    itemText = line.itemName || line.name || getItemName(line.itemId);
                    qtyText = fmtNum(line.quantity || 0);
                    priceText = fmtNum(line.price || 0) + ' SDG';
                } else if (lines.length > 1) {
                    itemText = lines.length + ' أصناف';
                    let totalQty = 0;
                    lines.forEach(l => {
                        totalQty += parseFloat(l.quantity) || 0;
                    });
                    qtyText = fmtNum(totalQty);
                    priceText = '—';
                }

                // Check if the invoice is from 27/6/2026 to highlight numbers in red
                let isTargetDate = false;
                if (inv.date === '2026-06-27' || (inv.timestamp && inv.timestamp.startsWith('2026-06-27'))) {
                    isTargetDate = true;
                }
                const numColor = isTargetDate ? '#ef4444' : '#fff';

                const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                const serialColor = isPrelim ? '#c084fc' : 'var(--gold-primary)';

                let actionBtns = `
                    <button onclick="editHubTransaction('${inv.invoiceNo}')" style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض">👁️</button>
                    <button onclick="deleteHubTransaction('${inv.invoiceNo}')" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:6px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
                `;

                if (inv.status === 'preliminary') {
                    actionBtns = `
                        <button onclick="convertToOfficial('${inv.invoiceNo}')" style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="تحويل لرسمية">✅</button>
                    ` + actionBtns;
                }

                tr.innerHTML = `
                    <td style="text-align:right; font-weight:800; color:${serialColor};">${inv.invoiceNo}</td>
                    <td style="text-align:right; color:var(--text-dim);">${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG')}</td>
                    <td style="text-align:right; font-weight:600;">${inv.customerName || getCustomerName(inv.customer || inv.customerId) || 'عميل غير محدد'}</td>
                    <td style="text-align:right; color:var(--text-dim);">${itemText}</td>
                    <td style="text-align:center; color:var(--text-dim);">${qtyText}</td>
                    <td style="text-align:center; color:var(--text-dim);">${priceText}</td>
                    <td style="text-align:center; font-weight:900; direction:ltr; color:${numColor};">${fmtNum(inv.totals?.grandTotal || inv.grandTotal || 0)} <span style="color:#a855f7; font-weight:700;">SDG</span></td>
                    <td style="text-align:center; font-weight:600; color:var(--text-dim);">${getWarehouseName(inv.warehouse)}</td>
                    <td style="text-align:center;">
                        ${actionBtns}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
        
        function filterHubTransactions() {
            const query = document.getElementById('hub-search').value.toLowerCase();
            const rows = document.querySelectorAll('#hub-transactions-body tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }

        function editHubTransaction(invNo) {
            try {
                openInvoiceEditor(undefined, true);
                if(typeof loadInvoiceFromStorage === 'function') {
                    setTimeout(() => {
                        try {
                            const success = loadInvoiceFromStorage(invNo);
                            if (!success) {
                                showToast('❌ فشل تحميل بيانات الفاتورة من التخزين', 'error');
                            }
                        } catch (innerErr) {
                            console.error(innerErr);
                            alert('خطأ أثناء تحميل تفاصيل الفاتورة: ' + innerErr.message + '\n' + innerErr.stack);
                        }
                    }, 100);
                } else {
                    alert('خطأ: لم يتم العثور على دالة تحميل الفاتورة!');
                }
            } catch (err) {
                console.error(err);
                alert('خطأ في محرر الفواتير: ' + err.message);
            }
        }
        
        function deleteHubTransaction(invNo) {
            if(confirm('هل أنت متأكد من حذف الفاتورة ' + invNo + ' نهائياً؟')) {
                let savedData = localStorage.getItem('erp_sales_invoices_v1');
                if (savedData) {
                    let invoices = JSON.parse(savedData);
                    invoices = invoices.filter(i => i.invoiceNo !== invNo);
                    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(invoices));
                    showToast('تم حذف الفاتورة بنجاح', 'success');
                    loadHubTransactions();
                }
            }
        }

        // Initialize view on load
        document.addEventListener('DBReady', () => {
            // Wait a brief moment for other scripts to initialize
            setTimeout(() => {
                showSalesHub();
            }, 50);
        });
    </script>
    
    <!-- Context Menu for Columns -->
    <div id="col-context-menu">
        <div style="font-size: 0.75rem; color: var(--gold-primary); margin-bottom: 8px; font-weight: bold; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;">إظهار/إخفاء الأعمدة</div>
        <div id="col-toggle-list" style="max-height: 300px; overflow-y: auto;"></div>
    </div>

    <script>
    function initTableEnhancements() {
        const table = document.getElementById('pos-grid');
        if(!table) return;

        // --- Column Resizing ---
        const ths = table.querySelectorAll('thead th');
        ths.forEach((th, index) => {
            if(th.querySelector('.resizer')) return; // already added

            const resizer = document.createElement('div');
            resizer.classList.add('resizer');
            th.appendChild(resizer);
            
            let x = 0;
            let w = 0;
            
            const mouseDownHandler = function(e) {
                x = e.clientX;
                const styles = window.getComputedStyle(th);
                w = parseInt(styles.width, 10);
                
                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);
                resizer.classList.add('resizing');
            };
            
            const mouseMoveHandler = function(e) {
                const dx = e.clientX - x;
                const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction === 'rtl';
                th.style.width = isRTL ? `${w - dx}px` : `${w + dx}px`;
                th.style.minWidth = th.style.width;
            };
            
            const mouseUpHandler = function() {
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
                resizer.classList.remove('resizing');
            };
            
            resizer.addEventListener('mousedown', mouseDownHandler);
        });

        // --- Context Menu ---
        const menu = document.getElementById('col-context-menu');
        const toggleList = document.getElementById('col-toggle-list');
        
        const updateMenu = () => {
            toggleList.innerHTML = '';
            const currentThs = table.querySelectorAll('thead th');
            currentThs.forEach((th, index) => {
                let title = th.textContent.trim() || 'إجراء';
                
                const item = document.createElement('label');
                item.className = 'col-toggle-item';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = th.style.display !== 'none';
                checkbox.dataset.colIndex = index + 1;
                
                checkbox.addEventListener('change', (e) => {
                    const isChecked = e.target.checked;
                    const colIndex = e.target.dataset.colIndex;
                    
                    table.querySelector(`thead th:nth-child(${colIndex})`).style.display = isChecked ? '' : 'none';
                    
                    const rows = table.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const td = row.querySelector(`td:nth-child(${colIndex})`);
                        if(td) td.style.display = isChecked ? '' : 'none';
                    });
                    
                    if (window.saveColumnVisibility) window.saveColumnVisibility();
                });
                
                item.appendChild(checkbox);
                item.appendChild(document.createTextNode(title));
                toggleList.appendChild(item);
            });
        };

        table.addEventListener('contextmenu', (e) => {
            if(e.target.closest('table') === table) {
                e.preventDefault();
                updateMenu();
                menu.style.display = 'block';
                menu.style.left = `${e.pageX}px`;
                menu.style.top = `${e.pageY}px`;
            }
        });

        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && e.button !== 2) {
                menu.style.display = 'none';
            }
        });

        // Observer for dynamically added rows
        if(!window.tableObserver) {
            const tbody = document.getElementById('grid-body');
            if(tbody) {
                window.tableObserver = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.addedNodes.length > 0) {
                            const currentThs = table.querySelectorAll('thead th');
                            currentThs.forEach((th, index) => {
                                if(th.style.display === 'none') {
                                    const colIndex = index + 1;
                                    mutation.addedNodes.forEach(row => {
                                        if(row.nodeType === 1 && row.tagName === 'TR') {
                                            const td = row.querySelector(`td:nth-child(${colIndex})`);
                                            if(td) td.style.display = 'none';
                                        }
                                    });
                                }
                            });
                        }
                    });
                });
                window.tableObserver.observe(tbody, { childList: true });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', initTableEnhancements);
    
    // --- Column Reordering Logic ---
    let dragSrcCol = null;

    function initDraggableColumns() {
        const theadTr = document.querySelector('#pos-grid thead tr');
        if(!theadTr) return;
        const ths = theadTr.querySelectorAll('th');
        ths.forEach((th, index) => {
            if(!th.hasAttribute('data-col-id')) {
                th.setAttribute('data-col-id', index);
            }
            th.setAttribute('draggable', 'true');
            th.style.cursor = 'grab';
            
            th.addEventListener('dragstart', handleColDragStart);
            th.addEventListener('dragenter', handleColDragEnter);
            th.addEventListener('dragover', handleColDragOver);
            th.addEventListener('dragleave', handleColDragLeave);
            th.addEventListener('drop', handleColDrop);
            th.addEventListener('dragend', handleColDragEnd);
        });
        
        applySavedColumnOrder();
    }

    function handleColDragStart(e) {
        if(e.target.classList.contains('resizer')) {
            e.preventDefault();
            return;
        }
        dragSrcCol = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        this.style.opacity = '0.4';
    }

    function handleColDragOver(e) {
        if (e.preventDefault) e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleColDragEnter(e) {
        let th = e.target.closest('th');
        if (th && th !== dragSrcCol) {
            th.style.border = '2px dashed #38bdf8';
        }
    }

    function handleColDragLeave(e) {
        let th = e.target.closest('th');
        if (th && th !== dragSrcCol) {
            th.style.border = '';
            th.style.borderBottom = '2px solid rgba(255,255,255,0.05)';
        }
    }

    function handleColDrop(e) {
        if (e.stopPropagation) e.stopPropagation();
        let targetTh = e.target.closest('th');
        if (dragSrcCol && targetTh && dragSrcCol !== targetTh) {
            const tr = targetTh.parentElement;
            const children = Array.from(tr.children);
            const srcIdx = children.indexOf(dragSrcCol);
            const targetIdx = children.indexOf(targetTh);

            if (srcIdx < targetIdx) {
                targetTh.after(dragSrcCol);
            } else {
                targetTh.before(dragSrcCol);
            }
            
            saveColumnOrder();
            applySavedColumnOrder(true);
        }
        return false;
    }

    function handleColDragEnd(e) {
        this.style.opacity = '1';
        let ths = document.querySelectorAll('#pos-grid thead th');
        ths.forEach(th => {
            th.style.border = '';
            th.style.borderBottom = '2px solid rgba(255,255,255,0.05)';
        });
    }

    function saveColumnOrder() {
        let ths = document.querySelectorAll('#pos-grid thead tr th');
        let order = Array.from(ths).map(th => parseInt(th.getAttribute('data-col-id')));
        localStorage.setItem('erp_sales_col_order_v4', JSON.stringify(order));
    }

    window.reorderTds = function(tr) {
        let orderStr = localStorage.getItem('erp_sales_col_order_v4');
        let hiddenStr = localStorage.getItem('erp_sales_col_vis_v4');
        let hidden = hiddenStr ? JSON.parse(hiddenStr) : [];
        
        if (!orderStr && hidden.length === 0) return;
        
        let order = orderStr ? JSON.parse(orderStr) : null;
        let tds = Array.from(tr.children);
        
        if (tds.length > 0 && !tds[0].hasAttribute('data-col-id')) {
            tds.forEach((td, idx) => td.setAttribute('data-col-id', idx));
        }
        
        if (order) {
            order.forEach(id => {
                let td = tds.find(t => parseInt(t.getAttribute('data-col-id')) === id);
                if (td) tr.appendChild(td);
            });
        }
        
        if (hidden.length > 0) {
            Array.from(tr.children).forEach(td => {
                let id = parseInt(td.getAttribute('data-col-id'));
                if (hidden.includes(id)) {
                    td.style.display = 'none';
                } else {
                    td.style.display = '';
                }
            });
        }
    };

    function applySavedColumnOrder(forceTbody = false) {
        let orderStr = localStorage.getItem('erp_sales_col_order_v4');
        if (!orderStr) return;
        let order = JSON.parse(orderStr);
        
        let theadTr = document.querySelector('#pos-grid thead tr');
        if (theadTr) {
            let ths = Array.from(theadTr.children);
            order.forEach(id => {
                let th = ths.find(t => parseInt(t.getAttribute('data-col-id')) === id);
                if (th) theadTr.appendChild(th);
            });
        }
        
        if (forceTbody) {
            let trs = document.querySelectorAll('#pos-grid tbody tr');
            trs.forEach(tr => window.reorderTds(tr));
        }
    }

    window.saveColumnVisibility = function() {
        let ths = document.querySelectorAll('#pos-grid thead th');
        let hidden = [];
        ths.forEach(th => {
            if(th.style.display === 'none') hidden.push(parseInt(th.getAttribute('data-col-id')));
        });
        localStorage.setItem('erp_sales_col_vis_v4', JSON.stringify(hidden));
    };

    window.applySavedColumnVisibility = function() {
        let hiddenStr = localStorage.getItem('erp_sales_col_vis_v4');
        if(!hiddenStr) return;
        let hidden = JSON.parse(hiddenStr);
        
        let ths = document.querySelectorAll('#pos-grid thead th');
        ths.forEach(th => {
            let id = parseInt(th.getAttribute('data-col-id'));
            if(hidden.includes(id)) {
                th.style.display = 'none';
            }
        });
        
        let trs = document.querySelectorAll('#pos-grid tbody tr');
        trs.forEach(tr => {
            let tds = Array.from(tr.children);
            tds.forEach(td => {
                let id = parseInt(td.getAttribute('data-col-id'));
                if(id !== null && !isNaN(id) && hidden.includes(id)) {
                    td.style.display = 'none';
                }
            });
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            initDraggableColumns();
            window.applySavedColumnVisibility();
        }, 600);
    });
    </script>
</body>
</html>
