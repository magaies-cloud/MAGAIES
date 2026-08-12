<?php $page_title = 'بطاقة الموقف المالي للعملاء'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بطاقة الموقف المالي للعملاء | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        /* ═══════════════════════════════════════════════════════════
           CUSTOMER PORTFOLIO — بطاقة الموقف المالي للعملاء
           ═══════════════════════════════════════════════════════════ */
        :root {
            --primary: #38bdf8; --primary-hover: #0ea5e9;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --bg-dark: #0f172a; --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc; --text-muted: #94a3b8;
            --accent-gold: #f59e0b; --accent-gold-light: #fbbf24;
            --accent-gold-dim: rgba(245, 158, 11, 0.15);
        }

        body {
            font-family: 'Tajawal', 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            margin: 0; padding: 0;
            transition: background-color 0.5s ease;
            overflow-x: hidden;
        }
        body.has-sidebar { padding-right: 0 !important; }

        .num { font-family: 'JetBrains Mono', 'Inter', monospace; letter-spacing: 0.5px; }

        .portfolio-container { max-width: 1700px; margin: 0 auto; padding: 20px 30px 80px; }

        /* ── Page Hero Header ── */
        .page-hero {
            text-align: center;
            padding: 35px 20px 25px;
            margin-bottom: 30px;
            position: relative;
        }
        .page-hero h1 {
            font-size: 2.6rem;
            font-weight: 900;
            margin: 0 0 10px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24, #f59e0b, #d97706);
            background-size: 300% auto;
            animation: heroShimmer 4s linear infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.4;
        }
        .page-hero p {
            color: var(--text-muted);
            font-size: 1rem;
            margin: 0;
            font-weight: 500;
        }
        @keyframes heroShimmer {
            0% { background-position: 0% center; }
            100% { background-position: 300% center; }
        }

        /* ── Glassmorphic Filter Panel ── */
        .filter-panel {
            background: rgba(30, 41, 59, 0.65);
            backdrop-filter: blur(20px) saturate(1.4);
            -webkit-backdrop-filter: blur(20px) saturate(1.4);
            border: 1px solid rgba(245, 158, 11, 0.15);
            border-radius: 20px;
            padding: 28px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.06);
            position: relative;
        }
        .filter-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 15%; right: 15%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(245,158,11,0.25), transparent);
            border-radius: 20px 20px 0 0;
            pointer-events: none;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 18px;
            align-items: end;
        }
        .filter-group { display: flex; flex-direction: column; gap: 6px; }
        .filter-group label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .filter-input {
            width: 100%;
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-main);
            padding: 11px 14px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            outline: none;
        }
        .filter-input:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.15);
            background: rgba(0,0,0,0.5);
        }
        .filter-input::placeholder { color: #64748b; }

        /* ── Customer Search with Multi-select Chips ── */
        .customer-search-wrapper { position: relative; }
        .customer-chips-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            min-height: 44px;
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 6px 10px;
            align-items: center;
            cursor: text;
            transition: all 0.3s ease;
        }
        .customer-chips-container.focused {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.15);
            background: rgba(0,0,0,0.5);
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(245,158,11,0.2);
            border: 1px solid rgba(245,158,11,0.4);
            color: var(--accent-gold-light);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            animation: chipIn 0.25s var(--ease-spring, cubic-bezier(0.34,1.56,0.64,1));
        }
        @keyframes chipIn { from { transform: scale(0.7); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .chip .chip-remove {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.7;
            transition: opacity 0.2s;
            line-height: 1;
        }
        .chip .chip-remove:hover { opacity: 1; }
        .chip-search-input {
            border: none;
            background: transparent;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            flex: 1;
            min-width: 120px;
            padding: 4px 0;
        }
        .chip-search-input::placeholder { color: #64748b; }

        .autocomplete-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            right: 0; left: 0;
            background: rgba(15,23,42,0.97);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(245,158,11,0.25);
            border-radius: 12px;
            max-height: 260px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
        }
        .autocomplete-dropdown.open { display: block; }
        .ac-item {
            padding: 10px 14px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.15s;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        .ac-item:last-child { border-bottom: none; }
        .ac-item:hover { background: rgba(245,158,11,0.12); color: var(--accent-gold-light); }
        .ac-item .ac-phone { font-size: 0.75rem; color: #64748b; font-family: 'JetBrains Mono', monospace; }

        .btn-run {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #0f172a;
            border: none;
            padding: 11px 28px;
            border-radius: 10px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-family: inherit;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(245,158,11,0.35);
            white-space: nowrap;
        }
        .btn-run:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245,158,11,0.45);
        }
        .btn-run:active { transform: translateY(0); }

        /* ── KPI Dashboard Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: rgba(30,41,59,0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 18px;
            padding: 24px 20px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 8px 30px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
            transition: all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
            position: relative;
            overflow: hidden;
        }
        .kpi-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 18px 18px 0 0;
        }
        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 50px rgba(0,0,0,0.5);
        }
        .kpi-card.green { border-color: rgba(16,185,129,0.2); }
        .kpi-card.green::after { background: linear-gradient(90deg, #10b981, #34d399); }
        .kpi-card.green:hover { box-shadow: 0 0 30px rgba(16,185,129,0.2), 0 16px 50px rgba(0,0,0,0.4); }
        .kpi-card.blue { border-color: rgba(56,189,248,0.2); }
        .kpi-card.blue::after { background: linear-gradient(90deg, #38bdf8, #7dd3fc); }
        .kpi-card.blue:hover { box-shadow: 0 0 30px rgba(56,189,248,0.2), 0 16px 50px rgba(0,0,0,0.4); }
        .kpi-card.amber { border-color: rgba(245,158,11,0.2); }
        .kpi-card.amber::after { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .kpi-card.amber:hover { box-shadow: 0 0 30px rgba(245,158,11,0.2), 0 16px 50px rgba(0,0,0,0.4); }
        .kpi-card.purple { border-color: rgba(168,85,247,0.2); }
        .kpi-card.purple::after { background: linear-gradient(90deg, #a855f7, #c084fc); }
        .kpi-card.purple:hover { box-shadow: 0 0 30px rgba(168,85,247,0.2), 0 16px 50px rgba(0,0,0,0.4); }

        .kpi-icon {
            font-size: 2rem;
            margin-bottom: 8px;
            display: block;
        }
        .kpi-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.85rem;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .kpi-card.green .kpi-value { color: #34d399; }
        .kpi-card.blue .kpi-value { color: #38bdf8; }
        .kpi-card.amber .kpi-value { color: #fbbf24; }
        .kpi-card.purple .kpi-value { color: #c084fc; }
        .kpi-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 700;
        }

        /* ── Circular Progress ── */
        .circular-progress {
            display: inline-block;
            width: 56px; height: 56px;
            position: relative;
            margin-bottom: 4px;
        }
        .circular-progress svg { transform: rotate(-90deg); }
        .circular-progress .progress-text {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 800;
            color: #c084fc;
        }

        /* ── Data Table — Bloomberg Terminal Style ── */
        .table-panel {
            background: rgba(12, 10, 20, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 20px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.04);
            margin-bottom: 30px;
        }
        .table-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .table-header-bar h3 {
            margin: 0;
            color: var(--text-main);
            font-weight: 800;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-search-inline {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--text-main);
            padding: 8px 14px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.85rem;
            width: 220px;
            outline: none;
            transition: all 0.3s;
        }
        .table-search-inline:focus { border-color: var(--accent-gold); }

        .portfolio-table {
            width: 100%;
            border-collapse: collapse;
        }
        .portfolio-table thead th {
            background: rgba(0,0,0,0.45);
            color: #cbd5e1;
            padding: 14px 16px;
            text-align: right;
            font-weight: 800;
            font-size: 0.82rem;
            border-bottom: 2px solid rgba(245,158,11,0.2);
            letter-spacing: 0.3px;
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s;
            position: relative;
        }
        .portfolio-table thead th:hover { color: var(--accent-gold-light); background: rgba(0,0,0,0.55); }
        .portfolio-table thead th .sort-icon {
            display: inline-block;
            margin-right: 4px;
            font-size: 0.7rem;
            opacity: 0.4;
            transition: opacity 0.2s;
        }
        .portfolio-table thead th.sorted .sort-icon { opacity: 1; color: var(--accent-gold); }

        .portfolio-table tbody td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-main);
            transition: all 0.2s;
            vertical-align: middle;
        }
        .portfolio-table tbody tr { transition: all 0.2s; }
        .portfolio-table tbody tr:nth-child(even) td { background: rgba(255,255,255,0.015); }
        .portfolio-table tbody tr:hover td { background: rgba(245,158,11,0.05); }
        .portfolio-table tbody tr.data-row { cursor: pointer; }
        .portfolio-table tbody tr.data-row:hover { box-shadow: inset 0 0 0 1px rgba(245,158,11,0.15); }

        .portfolio-table td.customer-name {
            color: #38bdf8;
            font-weight: 800;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .portfolio-table td.customer-name:hover { color: #7dd3fc; }

        /* Footer row */
        .portfolio-table tfoot td {
            padding: 16px;
            background: rgba(0,0,0,0.5);
            border-top: 2px solid rgba(245,158,11,0.25);
            font-weight: 900;
            font-size: 0.92rem;
            color: var(--accent-gold-light);
        }

        /* ── Status Badges ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.77rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .badge-paid { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .badge-partial { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .badge-overdue { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .badge-none { background: rgba(148,163,184,0.1); color: #94a3b8; border: 1px solid rgba(148,163,184,0.2); }

        /* ── Mini Progress Bar ── */
        .mini-progress-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
        }
        .mini-progress-bar {
            flex: 1;
            height: 6px;
            background: rgba(255,255,255,0.08);
            border-radius: 10px;
            overflow: hidden;
        }
        .mini-progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.8s cubic-bezier(0.16,1,0.3,1);
            min-width: 2px;
        }
        .progress-green { background: linear-gradient(90deg, #10b981, #34d399); }
        .progress-amber { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .progress-red { background: linear-gradient(90deg, #ef4444, #f87171); }
        .mini-progress-pct {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.73rem;
            font-weight: 700;
            min-width: 38px;
            text-align: left;
        }

        /* ── Accordion Detail Row ── */
        .detail-row { display: none; }
        .detail-row.open { display: table-row; }
        .detail-row td {
            padding: 0 !important;
            background: rgba(15,23,42,0.8) !important;
            border-bottom: 2px solid rgba(245,158,11,0.1) !important;
        }
        .detail-content {
            padding: 20px 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            animation: detailSlide 0.35s cubic-bezier(0.16,1,0.3,1);
        }
        .detail-full-width { grid-column: span 2; }
        @keyframes detailSlide { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Checks Mini Table */
        .checks-mini-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; margin-top: 8px; }
        .checks-mini-table thead th {
            background: rgba(245, 158, 11, 0.08);
            color: var(--accent-gold-light);
            padding: 8px 10px;
            font-weight: 700;
            text-align: right;
            border-bottom: 1px solid rgba(245,158,11,0.2);
            font-size: 0.78rem;
        }
        .checks-mini-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            color: #cbd5e1;
        }
        .checks-mini-table tbody tr:hover { background: rgba(245,158,11,0.05); }
        .check-status-badge {
            display: inline-block; padding: 2px 8px; border-radius: 6px;
            font-size: 0.72rem; font-weight: 700;
        }
        .check-status-safe { background: rgba(56,189,248,0.12); color: #38bdf8; }
        .check-status-collecting { background: rgba(245,158,11,0.12); color: #fbbf24; }
        .check-status-cleared { background: rgba(52,211,153,0.12); color: #34d399; }
        .check-status-bounced { background: rgba(239,68,68,0.12); color: #f87171; }

        /* Credit Account Summary */
        .credit-summary-cards {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 10px;
        }
        .credit-mini-card {
            background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);
            border-radius: 10px; padding: 12px; text-align: center;
        }
        .credit-mini-card .cmc-label { font-size: 0.75rem; color: #64748b; margin-bottom: 4px; }
        .credit-mini-card .cmc-val { font-size: 1.1rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; }

        .detail-section h4 {
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--accent-gold-light);
            margin: 0 0 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        /* Timeline */
        .timeline { list-style: none; padding: 0; margin: 0; }
        .timeline li {
            position: relative;
            padding: 8px 0 8px 24px;
            border-right: 2px solid rgba(245,158,11,0.2);
            margin-right: 8px;
            font-size: 0.82rem;
            color: #cbd5e1;
            transition: all 0.2s;
        }
        .timeline li::before {
            content: '';
            position: absolute;
            right: -6px;
            top: 12px;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--accent-gold);
            border: 2px solid #0f172a;
        }
        .timeline li .tl-date { color: #64748b; font-size: 0.75rem; font-family: 'JetBrains Mono', monospace; }
        .timeline li .tl-amount { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
        .timeline li .tl-sale { color: #f87171; }
        .timeline li .tl-receipt { color: #34d399; }

        /* Donut chart */
        .donut-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 10px 0;
        }
        .donut-canvas-wrap {
            width: 120px;
            height: 120px;
            position: relative;
        }
        .donut-canvas-wrap canvas { width: 100% !important; height: 100% !important; }
        .donut-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .donut-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .donut-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Action Bar ── */
        .action-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .btn-action {
            background: rgba(255,255,255,0.05);
            color: var(--text-main);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            font-size: 0.88rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .btn-action:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }
        .btn-print { border-color: rgba(56,189,248,0.3); }
        .btn-print:hover { border-color: rgba(56,189,248,0.6); color: #38bdf8; }
        .btn-excel { border-color: rgba(16,185,129,0.3); }
        .btn-excel:hover { border-color: rgba(16,185,129,0.6); color: #34d399; }
        .btn-pdf { border-color: rgba(239,68,68,0.3); }
        .btn-pdf:hover { border-color: rgba(239,68,68,0.6); color: #f87171; }

        /* ── Toast ── */
        .toast-container { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 99999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .toast { padding: 12px 20px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; pointer-events: auto; backdrop-filter: blur(20px); box-shadow: 0 12px 40px rgba(0,0,0,0.5); animation: toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; font-family: 'Tajawal', sans-serif; }
        .toast.success { background: rgba(52,211,153,0.15); border: 1px solid rgba(52,211,153,0.4); color: #34d399; }
        .toast.warning { background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.4); color: #fbbf24; }
        .toast.error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #f87171; }
        @keyframes toastIn { from { opacity: 0; transform: translateY(-20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #64748b;
        }
        .empty-state .empty-icon { font-size: 3.5rem; margin-bottom: 15px; opacity: 0.5; }
        .empty-state h3 { font-size: 1.1rem; margin: 0 0 8px; color: #94a3b8; }
        .empty-state p { font-size: 0.85rem; margin: 0; }

        /* ── Responsive ── */
        @media (max-width: 1100px) {
            .filter-grid { grid-template-columns: 1fr 1fr; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .portfolio-container { padding: 15px; }
            .filter-grid { grid-template-columns: 1fr; }
            .kpi-grid { grid-template-columns: 1fr; }
            .page-hero h1 { font-size: 1.8rem; }
            .detail-content { grid-template-columns: 1fr; }
            .action-bar { justify-content: center; }
        }

        /* ═══════════════════════════════════════════════════════════
           PRINT STYLES
           ═══════════════════════════════════════════════════════════ */
        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print, .filter-panel, .action-bar, .ambient-bg,
            .premium-header, .toast-container, .autocomplete-dropdown { display: none !important; }
            .portfolio-container { max-width: 100%; padding: 0; }
            .page-hero { padding: 10px 0; }
            .page-hero h1 {
                background: none !important;
                -webkit-text-fill-color: #000 !important;
                color: #000 !important;
                font-size: 1.6rem;
            }
            .page-hero p { color: #555 !important; }
            .kpi-grid { gap: 10px; }
            .kpi-card {
                background: #fff !important;
                border: 2px solid #ddd !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
                color: #000 !important;
            }
            .kpi-card::after { background: #ddd !important; }
            .kpi-value { color: #000 !important; font-size: 1.3rem; }
            .kpi-label { color: #555 !important; }
            .table-panel {
                background: #fff !important;
                border: 2px solid #000 !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
                border-radius: 0 !important;
            }
            .table-header-bar { border-bottom: 2px solid #000 !important; }
            .table-header-bar h3 { color: #000 !important; }
            .table-search-inline { display: none !important; }
            .portfolio-table thead th {
                background: #f0f0f0 !important;
                color: #000 !important;
                border-bottom: 2px solid #000 !important;
                font-size: 0.8rem;
            }
            .portfolio-table tbody td {
                color: #000 !important;
                border-bottom: 1px solid #ccc !important;
                font-size: 0.8rem;
                padding: 8px 10px;
            }
            .portfolio-table tfoot td {
                background: #f0f0f0 !important;
                color: #000 !important;
                border-top: 2px solid #000 !important;
            }
            .portfolio-table tbody tr:nth-child(even) td { background: #f9f9f9 !important; }
            .portfolio-table td.customer-name { color: #000 !important; }
            .status-badge { border: 1px solid #999 !important; background: transparent !important; color: #000 !important; }
            .mini-progress-bar { background: #ddd !important; }
            .mini-progress-fill { background: #666 !important; }
            .mini-progress-pct { color: #000 !important; }
            .detail-row.open { display: table-row; }
            .detail-content { border: 1px solid #ddd; }
        }

        /* ═══════════════════════════════════════════════════════════
           LIGHT THEME OVERRIDES
           ═══════════════════════════════════════════════════════════ */
        :root.light-theme body,
        :root.light-theme .ambient-bg { background: #f8fafc !important; }

        :root.light-theme .filter-panel,
        :root.light-theme .kpi-card,
        :root.light-theme .table-panel {
            background: #ffffff !important;
            border-color: rgba(0,0,0,0.08) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important;
            backdrop-filter: none !important;
        }
        :root.light-theme .filter-panel * ,
        :root.light-theme .kpi-card * { text-shadow: none !important; }

        :root.light-theme .filter-input,
        :root.light-theme .customer-chips-container,
        :root.light-theme .table-search-inline {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        :root.light-theme .filter-input:focus,
        :root.light-theme .customer-chips-container.focused,
        :root.light-theme .table-search-inline:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12) !important;
        }
        :root.light-theme .chip-search-input { color: #0f172a !important; }
        :root.light-theme .chip { background: rgba(245,158,11,0.1) !important; color: #b45309 !important; border-color: rgba(245,158,11,0.3) !important; }

        :root.light-theme .autocomplete-dropdown {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
        }
        :root.light-theme .ac-item { color: #1e293b !important; }
        :root.light-theme .ac-item:hover { background: rgba(245,158,11,0.08) !important; color: #b45309 !important; }

        :root.light-theme .page-hero p { color: #64748b !important; }

        :root.light-theme .kpi-card.green .kpi-value { color: #059669 !important; }
        :root.light-theme .kpi-card.blue .kpi-value { color: #0284c7 !important; }
        :root.light-theme .kpi-card.amber .kpi-value { color: #b45309 !important; }
        :root.light-theme .kpi-card.purple .kpi-value { color: #7c3aed !important; }
        :root.light-theme .kpi-label { color: #64748b !important; }

        :root.light-theme .portfolio-table thead th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom-color: #cbd5e1 !important;
        }
        :root.light-theme .portfolio-table tbody td {
            color: #1e293b !important;
            border-bottom-color: rgba(0,0,0,0.06) !important;
        }
        :root.light-theme .portfolio-table tbody tr:hover td { background: rgba(245,158,11,0.04) !important; }
        :root.light-theme .portfolio-table tbody tr:nth-child(even) td { background: rgba(0,0,0,0.015) !important; }
        :root.light-theme .portfolio-table td.customer-name { color: #0284c7 !important; }
        :root.light-theme .portfolio-table tfoot td { background: #f1f5f9 !important; color: #b45309 !important; border-top-color: #cbd5e1 !important; }

        :root.light-theme .table-header-bar { border-bottom-color: rgba(0,0,0,0.08) !important; }
        :root.light-theme .table-header-bar h3 { color: #0f172a !important; }

        :root.light-theme .detail-row td { background: #f8fafc !important; }
        :root.light-theme .detail-section h4 { color: #b45309 !important; }
        :root.light-theme .timeline li { color: #334155 !important; border-right-color: rgba(245,158,11,0.3) !important; }
        :root.light-theme .timeline li .tl-date { color: #94a3b8 !important; }

        :root.light-theme .btn-action {
            background: rgba(0,0,0,0.03) !important;
            color: #1e293b !important;
            border-color: rgba(0,0,0,0.1) !important;
        }
        :root.light-theme .btn-action:hover { background: rgba(0,0,0,0.06) !important; color: #0f172a !important; }

        :root.light-theme .mini-progress-bar { background: #e2e8f0 !important; }
        :root.light-theme .mini-progress-pct { color: #334155 !important; }

        :root.light-theme .badge-paid { background: rgba(16,185,129,0.1) !important; color: #059669 !important; border-color: rgba(16,185,129,0.3) !important; }
        :root.light-theme .badge-partial { background: rgba(245,158,11,0.1) !important; color: #b45309 !important; border-color: rgba(245,158,11,0.3) !important; }
        :root.light-theme .badge-overdue { background: rgba(239,68,68,0.1) !important; color: #dc2626 !important; border-color: rgba(239,68,68,0.3) !important; }
        :root.light-theme .badge-none { background: rgba(0,0,0,0.04) !important; color: #64748b !important; border-color: rgba(0,0,0,0.1) !important; }

        :root.light-theme .circular-progress .progress-text { color: #7c3aed !important; }

        :root.light-theme .empty-state h3 { color: #64748b !important; }
        :root.light-theme .empty-state p { color: #94a3b8 !important; }
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

    <div class="portfolio-container">

        <!-- Premium Header -->
        <?php $header_theme_class = 'header-theme-reports'; include 'includes/header.php'; ?>

        <!-- Page Hero -->
        <div class="page-hero">
            <h1>📊 بطاقة الموقف المالي للعملاء</h1>
            <p>لوحة تحليل شاملة لرصد مواقف العملاء وأداء التحصيل والمديونيات</p>
        </div>

        <!-- Filter Panel -->
        <div class="filter-panel no-print">
            <div class="filter-grid">
                <!-- Customer Search -->
                <div class="filter-group">
                    <label>🔍 بحث العملاء</label>
                    <div class="customer-search-wrapper">
                        <div class="customer-chips-container" id="chipsContainer" onclick="document.getElementById('customerSearchInput').focus()">
                            <input type="text" class="chip-search-input" id="customerSearchInput" placeholder="ابحث باسم العميل أو رقم الهاتف..." autocomplete="off">
                        </div>
                        <div class="autocomplete-dropdown" id="customerDropdown"></div>
                    </div>
                </div>
                <!-- Date From -->
                <div class="filter-group">
                    <label>📅 من تاريخ</label>
                    <input type="date" class="filter-input" id="dateFrom">
                </div>
                <!-- Date To -->
                <div class="filter-group">
                    <label>📅 إلى تاريخ</label>
                    <input type="date" class="filter-input" id="dateTo">
                </div>
                <!-- Status Filter -->
                <div class="filter-group">
                    <label>📋 حالة السداد</label>
                    <select class="filter-input" id="statusFilter">
                        <option value="ALL">الكل</option>
                        <option value="HAS_BALANCE">لديه رصيد مستحق</option>
                        <option value="FULLY_PAID">مسدد بالكامل</option>
                        <option value="OVERDUE">متأخر</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; gap:12px; margin-top:18px; align-items:center; flex-wrap:wrap;">
                <button class="btn-run" onclick="generateReport()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg>
                    تشغيل التقرير
                </button>
                <button class="btn-action" onclick="clearFilters()" style="font-size:0.82rem;">🔄 إعادة تعيين</button>
            </div>
        </div>

        <!-- KPI Dashboard -->
        <div class="kpi-grid" id="kpiDashboard" style="display:none;">
            <div class="kpi-card green">
                <span class="kpi-icon">💰</span>
                <div class="kpi-value num" id="kpi-sales">0</div>
                <div class="kpi-label">إجمالي المبيعات</div>
            </div>
            <div class="kpi-card blue">
                <span class="kpi-icon">📥</span>
                <div class="kpi-value num" id="kpi-receipts">0</div>
                <div class="kpi-label">إجمالي التحصيل</div>
            </div>
            <div class="kpi-card amber">
                <span class="kpi-icon">⚠️</span>
                <div class="kpi-value num" id="kpi-balance">0</div>
                <div class="kpi-label">الرصيد المستحق</div>
            </div>
            <div class="kpi-card purple">
                <span class="kpi-icon">📈</span>
                <div class="circular-progress" id="collectionCircle">
                    <svg width="56" height="56" viewBox="0 0 56 56">
                        <circle cx="28" cy="28" r="24" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="5"/>
                        <circle cx="28" cy="28" r="24" fill="none" stroke="#a855f7" stroke-width="5" stroke-linecap="round"
                            stroke-dasharray="150.796" stroke-dashoffset="150.796" id="progressCircle"/>
                    </svg>
                    <span class="progress-text num" id="kpi-pct">0%</span>
                </div>
                <div class="kpi-label">نسبة التحصيل %</div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar no-print" id="actionBar" style="display:none;">
            <button class="btn-action btn-print" onclick="directPrint()">🖨️ طباعة التقرير</button>
            <button class="btn-action btn-excel" onclick="exportExcel()">📊 تصدير Excel</button>
            <button class="btn-action btn-pdf" onclick="exportPDF()">📄 تصدير PDF</button>
        </div>

        <!-- Data Table -->
        <div class="table-panel" id="tablePanel" style="display:none;">
            <div class="table-header-bar">
                <h3>📋 تفصيل الموقف المالي للعملاء</h3>
                <input type="text" class="table-search-inline no-print" id="tableSearch" placeholder="🔍 تصفية سريعة..." oninput="filterTable()">
            </div>
            <div style="overflow-x:auto;">
                <table class="portfolio-table" id="portfolioTable">
                    <thead>
                        <tr>
                            <th data-col="idx" onclick="sortTable('idx')" style="width:40px;"><span class="sort-icon">⇅</span> #</th>
                            <th data-col="name" onclick="sortTable('name')"><span class="sort-icon">⇅</span> اسم العميل</th>
                            <th data-col="sales" onclick="sortTable('sales')"><span class="sort-icon">⇅</span> إجمالي المبيعات</th>
                            <th data-col="paid" onclick="sortTable('paid')"><span class="sort-icon">⇅</span> المبالغ المسددة</th>
                            <th data-col="balance" onclick="sortTable('balance')"><span class="sort-icon">⇅</span> الرصيد المتبقي</th>
                            <th data-col="lastTxn" onclick="sortTable('lastTxn')"><span class="sort-icon">⇅</span> آخر عملية</th>
                            <th data-col="status" onclick="sortTable('status')"><span class="sort-icon">⇅</span> حالة السداد</th>
                            <th data-col="perf" onclick="sortTable('perf')"><span class="sort-icon">⇅</span> أداء التحصيل</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                    <tfoot id="tableFoot" style="display:none;">
                        <tr>
                            <td colspan="2" style="text-align:center;">📊 الإجمالي</td>
                            <td class="num" id="foot-sales">0</td>
                            <td class="num" id="foot-paid">0</td>
                            <td class="num" id="foot-balance">0</td>
                            <td>—</td>
                            <td>—</td>
                            <td>—</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

<script defer src="js/localforage.min.js"></script>
<script defer src="js/app.js?v=<?= time() ?>"></script>
<script>
/* ═══════════════════════════════════════════════════════════════
   CUSTOMER PORTFOLIO — JavaScript Engine
   ═══════════════════════════════════════════════════════════════ */

// ── Global State ──
let allCustomers = [];
let allInvoices = [];
let allReceipts = [];
let allSingleReceipts = [];
let selectedCustomerIds = [];
let reportData = [];
let currentSort = { col: 'balance', dir: 'desc' };

// ── Utility Functions ──
function fmtNum(n) {
    const val = parseFloat(n) || 0;
    return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function parseNum(str) {
    return parseFloat(String(str || '0').replace(/,/g, '')) || 0;
}
function showToast(msg, type = 'success') {
    const c = document.getElementById('toast-container');
    if (!c) return;
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = msg;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

// ── Initialization ──
document.addEventListener('DBReady', () => {
    if (!localStorage.getItem('authToken')) {
        window.location.href = 'login.php';
        return;
    }
    loadData();
    setupCustomerSearch();
});

function loadData() {
    // Load customers from both stores
    const customersRaw = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
    const partnersRaw = JSON.parse(localStorage.getItem('erp_partners_v1') || '[]');

    // Merge: erp_customers_v1 + partners with type customer/عميل/CUSTOMER
    const customersFromPartners = (Array.isArray(partnersRaw) ? partnersRaw : [])
        .filter(p => !p.deleted && (
            (p.type || '').toLowerCase() === 'customer' ||
            (p.type || '') === 'عميل' ||
            (p.type || '').toUpperCase() === 'CUSTOMER'
        ));

    const customersFromStore = (Array.isArray(customersRaw) ? customersRaw : [])
        .filter(c => !c.deleted);

    // Combine and deduplicate by id
    const merged = new Map();
    customersFromStore.forEach(c => merged.set(c.id, c));
    customersFromPartners.forEach(c => { if (!merged.has(c.id)) merged.set(c.id, c); });
    allCustomers = Array.from(merged.values());

    // Load invoices (sales)
    allInvoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]'))
        .filter(x => !x.deleted);

    // Load receipts (payments received from customers)
    allReceipts = (JSON.parse(localStorage.getItem('erp_receipts_v1') || '[]'))
        .filter(x => !x.deleted);
    allSingleReceipts = (JSON.parse(localStorage.getItem('erp_single_receipts_v1') || '[]'))
        .filter(x => !x.deleted);

    // Load checks database for customer check details
    const checksRaw = JSON.parse(localStorage.getItem('erp_checks_v1') || '[]');
    // Also support the mock checksDB format from checks-cycle.js
    if (checksRaw.length > 0) {
        window._checksDB = checksRaw;
    } else {
        // Fallback: load from the global checksDB if checks-cycle.js was loaded on another page
        window._checksDB = [
            { id: 'CHQ-9902', type: 'incoming', partner: 'شريهان أحمد', bank: 'بنك الخرطوم', amount: 45000.00, dueDate: '2026-05-15', status: 'في الخزنة' },
            { id: 'CHQ-9903', type: 'incoming', partner: 'محمد الفاتح', bank: 'بنك أمدرمان الوطني', amount: 12000.50, dueDate: '2026-05-10', status: 'برسم التحصيل' },
            { id: 'CHQ-9904', type: 'incoming', partner: 'شركة الرواد', bank: 'بنك فيصل الإسلامي', amount: 85000.00, dueDate: '2026-05-20', status: 'محصل' },
            { id: 'CHQ-9905', type: 'incoming', partner: 'أحمد كمال', bank: 'بنك الخرطوم', amount: 3000.00, dueDate: '2026-05-01', status: 'مرتجع' },
            { id: 'OUT-1001', type: 'outgoing', partner: 'مورد التقنية', bank: 'بنك النيلين', amount: 50000.00, dueDate: '2026-06-01', status: 'غير مسحوب' },
            { id: 'OUT-1002', type: 'outgoing', partner: 'الشركة العالمية', bank: 'بنك فيصل الإسلامي', amount: 120000.00, dueDate: '2026-05-25', status: 'مسحوب' }
        ];
    }
}

// ═══════════════════════════════════════════════════════════════
// CUSTOMER AUTOCOMPLETE SEARCH WITH MULTI-SELECT CHIPS
// ═══════════════════════════════════════════════════════════════
function setupCustomerSearch() {
    const input = document.getElementById('customerSearchInput');
    const dropdown = document.getElementById('customerDropdown');
    const container = document.getElementById('chipsContainer');

    input.addEventListener('focus', () => {
        container.classList.add('focused');
        showDropdown(input.value);
    });
    input.addEventListener('blur', () => {
        container.classList.remove('focused');
        setTimeout(() => dropdown.classList.remove('open'), 200);
    });
    input.addEventListener('input', () => showDropdown(input.value));
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value === '' && selectedCustomerIds.length > 0) {
            removeChip(selectedCustomerIds[selectedCustomerIds.length - 1]);
        }
        if (e.key === 'Escape') dropdown.classList.remove('open');
    });
}

function showDropdown(query) {
    const dropdown = document.getElementById('customerDropdown');
    const q = (query || '').trim().toLowerCase();

    let filtered = allCustomers.filter(c => !selectedCustomerIds.includes(c.id));
    if (q) {
        filtered = filtered.filter(c =>
            (c.name || '').toLowerCase().includes(q) ||
            (c.phone || '').includes(q) ||
            String(c.id).includes(q)
        );
    }

    if (filtered.length === 0 && q) {
        dropdown.innerHTML = '<div class="ac-item" style="color:#f87171; cursor:default; justify-content:center;">لا يوجد عميل مطابق</div>';
    } else if (filtered.length === 0) {
        dropdown.innerHTML = '<div class="ac-item" style="color:#64748b; cursor:default; justify-content:center;">لا يوجد عملاء متاحون</div>';
    } else {
        dropdown.innerHTML = filtered.slice(0, 30).map(c => `
            <div class="ac-item" onmousedown="selectCustomer('${c.id}')">
                <span>👤 ${c.name}</span>
                <span class="ac-phone">${c.phone || ''}</span>
            </div>
        `).join('');
    }
    dropdown.classList.add('open');
}

function selectCustomer(id) {
    id = String(id);
    if (selectedCustomerIds.includes(id)) return;
    const customer = allCustomers.find(c => String(c.id) === id);
    if (!customer) return;

    selectedCustomerIds.push(id);

    const chip = document.createElement('span');
    chip.className = 'chip';
    chip.dataset.id = id;
    chip.innerHTML = `${customer.name} <span class="chip-remove" onmousedown="event.stopPropagation(); removeChip('${id}')">×</span>`;

    const input = document.getElementById('customerSearchInput');
    input.parentNode.insertBefore(chip, input);
    input.value = '';
    document.getElementById('customerDropdown').classList.remove('open');
}

function removeChip(id) {
    id = String(id);
    selectedCustomerIds = selectedCustomerIds.filter(cid => cid !== id);
    const chip = document.querySelector(`.chip[data-id="${id}"]`);
    if (chip) chip.remove();
}

function clearFilters() {
    selectedCustomerIds = [];
    document.querySelectorAll('.chip').forEach(c => c.remove());
    document.getElementById('customerSearchInput').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('statusFilter').value = 'ALL';
}

// ═══════════════════════════════════════════════════════════════
// REPORT GENERATION ENGINE
// ═══════════════════════════════════════════════════════════════
function generateReport() {
    loadData(); // Refresh data

    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const statusFilter = document.getElementById('statusFilter').value;

    // Determine target customers
    let targetCustomers = allCustomers;
    if (selectedCustomerIds.length > 0) {
        targetCustomers = allCustomers.filter(c => selectedCustomerIds.includes(String(c.id)));
    }

    if (targetCustomers.length === 0) {
        showToast('⚠️ لا يوجد عملاء لعرض التقرير', 'warning');
        return;
    }

    // Build portfolio data per customer
    reportData = [];

    targetCustomers.forEach(customer => {
        const cid = String(customer.id);
        const cName = (customer.name || '').toLowerCase();

        // Find sales for this customer
        let customerSales = allInvoices.filter(inv => {
            const invCid = String(inv.customer || inv.customerId || inv.partner_id || '');
            const invCname = (inv.customerName || '').toLowerCase();
            return invCid === cid || invCname === cName;
        });

        // Apply date filter on sales
        if (dateFrom) customerSales = customerSales.filter(inv => (inv.date || inv.createdAt || '') >= dateFrom);
        if (dateTo) customerSales = customerSales.filter(inv => (inv.date || inv.createdAt || '') <= dateTo);

        // Find receipts for this customer
        let customerReceipts = [];

        // Multi-receipts (erp_receipts_v1)
        allReceipts.forEach(r => {
            const entries = r.entries || r.items || [];
            entries.forEach(entry => {
                const eCid = String(entry.customerId || entry.customer || entry.partnerId || entry.partner_id || '');
                const eName = (entry.customerName || entry.partnerName || '').toLowerCase();
                if (eCid === cid || eName === cName) {
                    let amt = parseFloat(entry.amount) || 0;
                    let dt = entry.date || r.date || r.createdAt || '';
                    if ((!dateFrom || dt >= dateFrom) && (!dateTo || dt <= dateTo)) {
                        customerReceipts.push({ amount: amt, date: dt, description: entry.description || r.description || 'سند قبض', type: 'receipt' });
                    }
                }
            });
            // Single-entry receipt structure
            if (entries.length === 0) {
                const rCid = String(r.customerId || r.customer || r.partnerId || r.partner_id || '');
                const rName = (r.customerName || r.partnerName || '').toLowerCase();
                if (rCid === cid || rName === cName) {
                    let amt = parseFloat(r.amount || r.totalAmount) || 0;
                    let dt = r.date || r.createdAt || '';
                    if ((!dateFrom || dt >= dateFrom) && (!dateTo || dt <= dateTo)) {
                        customerReceipts.push({ amount: amt, date: dt, description: r.description || 'سند قبض', type: 'receipt' });
                    }
                }
            }
        });

        // Single receipts (erp_single_receipts_v1)
        allSingleReceipts.forEach(r => {
            const rCid = String(r.customerId || r.customer || r.partnerId || r.partner_id || '');
            const rName = (r.customerName || r.partnerName || '').toLowerCase();
            if (rCid === cid || rName === cName) {
                let amt = parseFloat(r.amount || r.totalAmount) || 0;
                let dt = r.date || r.createdAt || '';
                if ((!dateFrom || dt >= dateFrom) && (!dateTo || dt <= dateTo)) {
                    customerReceipts.push({ amount: amt, date: dt, description: r.description || 'سند قبض', type: 'receipt' });
                }
            }
        });

        // Calculate totals
        const totalSales = customerSales.reduce((sum, inv) => sum + (parseFloat(inv.netTotal || inv.total || inv.grandTotal || inv.amount) || 0), 0);
        const totalPaid = customerReceipts.reduce((sum, r) => sum + r.amount, 0);
        const balance = totalSales - totalPaid;
        const collectionPct = totalSales > 0 ? Math.min((totalPaid / totalSales) * 100, 100) : 0;

        // Collect all transactions for the timeline
        let allTxns = [];
        customerSales.forEach(inv => {
            allTxns.push({
                date: inv.date || inv.createdAt || '',
                amount: parseFloat(inv.netTotal || inv.total || inv.grandTotal || inv.amount) || 0,
                type: 'sale',
                description: inv.description || inv.invoiceNumber || 'فاتورة بيع'
            });
        });
        allTxns = allTxns.concat(customerReceipts);
        allTxns.sort((a, b) => (b.date || '').localeCompare(a.date || ''));

        // Find last transaction date
        const lastTxnDate = allTxns.length > 0 ? allTxns[0].date : '';

        // Determine status
        let status = 'none';
        if (totalSales === 0 && totalPaid === 0) {
            status = 'none';
        } else if (balance <= 0.01) {
            status = 'paid';
        } else if (totalPaid > 0) {
            status = 'partial';
        } else {
            status = 'overdue';
        }

        // Skip based on status filter
        if (statusFilter === 'HAS_BALANCE' && balance <= 0.01) return;
        if (statusFilter === 'FULLY_PAID' && balance > 0.01) return;
        if (statusFilter === 'OVERDUE' && status !== 'overdue') return;

        reportData.push({
            id: customer.id,
            name: customer.name || 'عميل غير معروف',
            phone: customer.phone || '',
            totalSales,
            totalPaid,
            balance,
            collectionPct,
            lastTxnDate,
            status,
            recentTxns: allTxns.slice(0, 5)
        });
    });

    // Sort by default (balance desc)
    applySorting();

    // Render everything
    renderKPIs();
    renderTable();

    // Show sections
    document.getElementById('kpiDashboard').style.display = 'grid';
    document.getElementById('actionBar').style.display = 'flex';
    document.getElementById('tablePanel').style.display = 'block';

    if (reportData.length > 0) {
        showToast(`✅ تم تحليل ${reportData.length} عميل بنجاح`, 'success');
    } else {
        showToast('⚠️ لا توجد بيانات تطابق المعايير المحددة', 'warning');
    }
}

// ═══════════════════════════════════════════════════════════════
// KPI DASHBOARD RENDERING
// ═══════════════════════════════════════════════════════════════
function renderKPIs() {
    const totalSales = reportData.reduce((s, d) => s + d.totalSales, 0);
    const totalPaid = reportData.reduce((s, d) => s + d.totalPaid, 0);
    const totalBalance = reportData.reduce((s, d) => s + d.balance, 0);
    const collectionRate = totalSales > 0 ? Math.min((totalPaid / totalSales) * 100, 100) : 0;

    animateValue('kpi-sales', totalSales);
    animateValue('kpi-receipts', totalPaid);
    animateValue('kpi-balance', totalBalance);
    animateCircularProgress(collectionRate);
}

function animateValue(elementId, targetValue) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const duration = 1200;
    const startTime = performance.now();
    const startValue = 0;

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeProgress = 1 - Math.pow(1 - progress, 3); // ease out cubic
        const currentValue = startValue + (targetValue - startValue) * easeProgress;
        el.textContent = fmtNum(currentValue);
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

function animateCircularProgress(pct) {
    const circle = document.getElementById('progressCircle');
    const text = document.getElementById('kpi-pct');
    if (!circle || !text) return;

    const circumference = 2 * Math.PI * 24; // r=24
    const duration = 1400;
    const startTime = performance.now();

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeProgress = 1 - Math.pow(1 - progress, 3);
        const currentPct = pct * easeProgress;
        const offset = circumference - (currentPct / 100) * circumference;
        circle.setAttribute('stroke-dashoffset', offset);
        text.textContent = Math.round(currentPct) + '%';
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

// ═══════════════════════════════════════════════════════════════
// TABLE RENDERING
// ═══════════════════════════════════════════════════════════════
function renderTable() {
    const tbody = document.getElementById('tableBody');
    const tfoot = document.getElementById('tableFoot');
    tbody.innerHTML = '';

    if (reportData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📭</div><h3>لا توجد بيانات للعرض</h3><p>حدّد العملاء والفترة ثم اضغط "تشغيل التقرير"</p></div></td></tr>`;
        tfoot.style.display = 'none';
        return;
    }

    let totalSales = 0, totalPaid = 0, totalBalance = 0;

    reportData.forEach((d, i) => {
        totalSales += d.totalSales;
        totalPaid += d.totalPaid;
        totalBalance += d.balance;

        const statusBadge = getStatusBadge(d.status);
        const progressClass = d.collectionPct >= 80 ? 'progress-green' : d.collectionPct >= 40 ? 'progress-amber' : 'progress-red';

        // Data Row
        const tr = document.createElement('tr');
        tr.className = 'data-row';
        tr.dataset.idx = i;
        tr.onclick = () => toggleDetail(i);
        tr.innerHTML = `
            <td class="num" style="text-align:center; color:#64748b;">${i + 1}</td>
            <td class="customer-name" title="${d.name}">${d.name}</td>
            <td class="num">${fmtNum(d.totalSales)}</td>
            <td class="num" style="color:#34d399;">${fmtNum(d.totalPaid)}</td>
            <td class="num" style="color:${d.balance > 0 ? '#fbbf24' : '#34d399'}; font-weight:800;">${fmtNum(d.balance)}</td>
            <td class="num" style="color:#94a3b8; font-size:0.8rem;">${d.lastTxnDate || '—'}</td>
            <td>${statusBadge}</td>
            <td>
                <div class="mini-progress-wrap">
                    <div class="mini-progress-bar">
                        <div class="mini-progress-fill ${progressClass}" style="width:${d.collectionPct}%"></div>
                    </div>
                    <span class="mini-progress-pct num">${Math.round(d.collectionPct)}%</span>
                </div>
            </td>
        `;
        tbody.appendChild(tr);

        // Detail Row (Accordion)
        const detailTr = document.createElement('tr');
        detailTr.className = 'detail-row';
        detailTr.id = `detail-${i}`;
        detailTr.innerHTML = `<td colspan="8"><div class="detail-content" id="detail-content-${i}"></div></td>`;
        tbody.appendChild(detailTr);
    });

    // Update footer
    document.getElementById('foot-sales').textContent = fmtNum(totalSales);
    document.getElementById('foot-paid').textContent = fmtNum(totalPaid);
    document.getElementById('foot-balance').textContent = fmtNum(totalBalance);
    tfoot.style.display = '';
}

function getStatusBadge(status) {
    switch (status) {
        case 'paid': return '<span class="status-badge badge-paid">✅ مسدد بالكامل</span>';
        case 'partial': return '<span class="status-badge badge-partial">⏳ سداد جزئي</span>';
        case 'overdue': return '<span class="status-badge badge-overdue">🔴 متأخر</span>';
        default: return '<span class="status-badge badge-none">⚪ بدون حركة</span>';
    }
}

// ═══════════════════════════════════════════════════════════════
// ACCORDION DETAIL (TIMELINE + DONUT CHART)
// ═══════════════════════════════════════════════════════════════
function toggleDetail(idx) {
    const detailRow = document.getElementById(`detail-${idx}`);
    if (!detailRow) return;

    const isOpen = detailRow.classList.contains('open');

    // Close all detail rows
    document.querySelectorAll('.detail-row.open').forEach(r => r.classList.remove('open'));

    if (!isOpen) {
        detailRow.classList.add('open');
        renderDetail(idx);
    }
}

function renderDetail(idx) {
    const d = reportData[idx];
    if (!d) return;
    const container = document.getElementById(`detail-content-${idx}`);

    // Timeline of last 5 transactions
    let timelineHtml = '';
    if (d.recentTxns.length > 0) {
        timelineHtml = '<ul class="timeline">';
        d.recentTxns.forEach(txn => {
            const isSale = txn.type === 'sale';
            const icon = isSale ? '📄' : '💵';
            const cls = isSale ? 'tl-sale' : 'tl-receipt';
            const label = isSale ? 'مبيعات' : 'تحصيل';
            timelineHtml += `
                <li>
                    <div>${icon} <strong>${label}</strong> — ${txn.description || ''}</div>
                    <div><span class="tl-amount ${cls}">${fmtNum(txn.amount)}</span></div>
                    <div class="tl-date">${txn.date || 'بدون تاريخ'}</div>
                </li>
            `;
        });
        timelineHtml += '</ul>';
    } else {
        timelineHtml = '<div style="color:#64748b; text-align:center; padding:20px;">لا توجد حركات مسجلة</div>';
    }

    // Checks for this customer
    const customerChecks = getCustomerChecks(d.name);
    let checksHtml = '';
    if (customerChecks.length > 0) {
        const totalChecks = customerChecks.reduce((s, c) => s + (c.amount || 0), 0);
        checksHtml = `
            <div class="detail-section detail-full-width">
                <h4>🧾 الشيكات الواردة من العميل (${customerChecks.length} شيك — إجمالي: <span class="num">${fmtNum(totalChecks)}</span>)</h4>
                <table class="checks-mini-table">
                    <thead>
                        <tr>
                            <th>رقم الشيك</th>
                            <th>البنك</th>
                            <th>المبلغ</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${customerChecks.map(c => `
                            <tr>
                                <td style="font-weight:700; font-family:'JetBrains Mono',monospace;">${c.id}</td>
                                <td>${c.bank || '-'}</td>
                                <td class="num" style="font-weight:700; color:#fbbf24;">${fmtNum(c.amount)}</td>
                                <td class="num" style="color:#94a3b8;">${c.dueDate || '-'}</td>
                                <td><span class="check-status-badge ${getCheckStatusClass(c.status)}">${c.status}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } else {
        checksHtml = `
            <div class="detail-section detail-full-width">
                <h4>🧾 الشيكات الواردة من العميل</h4>
                <div style="color:#64748b; text-align:center; padding:15px; font-size:0.85rem;">لا توجد شيكات مسجلة لهذا العميل</div>
            </div>
        `;
    }

    // Credit account summary
    const creditSales = d.recentTxns.filter(t => t.type === 'sale');
    const creditReceipts = d.recentTxns.filter(t => t.type === 'receipt');
    const checksCleared = customerChecks.filter(c => c.status === 'محصل').reduce((s, c) => s + (c.amount || 0), 0);
    const checksPending = customerChecks.filter(c => c.status !== 'محصل' && c.status !== 'مرتجع').reduce((s, c) => s + (c.amount || 0), 0);
    const checksBounced = customerChecks.filter(c => c.status === 'مرتجع').reduce((s, c) => s + (c.amount || 0), 0);

    let creditHtml = `
        <div class="detail-section detail-full-width">
            <h4>💳 ملخص الحساب الآجل والتحصيل</h4>
            <div class="credit-summary-cards">
                <div class="credit-mini-card">
                    <div class="cmc-label">إجمالي المبيعات الآجلة</div>
                    <div class="cmc-val" style="color:#f87171;">${fmtNum(d.totalSales)}</div>
                </div>
                <div class="credit-mini-card">
                    <div class="cmc-label">المسدد نقداً + تحويلات</div>
                    <div class="cmc-val" style="color:#34d399;">${fmtNum(d.totalPaid)}</div>
                </div>
                <div class="credit-mini-card">
                    <div class="cmc-label">المسدد بشيكات (محصلة)</div>
                    <div class="cmc-val" style="color:#38bdf8;">${fmtNum(checksCleared)}</div>
                </div>
                <div class="credit-mini-card">
                    <div class="cmc-label">شيكات قيد التحصيل</div>
                    <div class="cmc-val" style="color:#fbbf24;">${fmtNum(checksPending)}</div>
                </div>
                <div class="credit-mini-card">
                    <div class="cmc-label">شيكات مرتجعة</div>
                    <div class="cmc-val" style="color:#ef4444;">${fmtNum(checksBounced)}</div>
                </div>
                <div class="credit-mini-card">
                    <div class="cmc-label">صافي الرصيد المستحق</div>
                    <div class="cmc-val" style="color:#f59e0b;">${fmtNum(d.balance)}</div>
                </div>
            </div>
        </div>
    `;

    // Donut chart canvas
    const canvasId = `donut-${idx}`;
    container.innerHTML = `
        <div class="detail-section">
            <h4>📜 آخر الحركات المالية</h4>
            ${timelineHtml}
        </div>
        <div class="detail-section">
            <h4>📊 توزيع المبيعات والتحصيل</h4>
            <div class="donut-container">
                <div class="donut-canvas-wrap">
                    <canvas id="${canvasId}" width="120" height="120"></canvas>
                </div>
                <div class="donut-legend">
                    <div class="donut-legend-item"><span class="donut-dot" style="background:#f87171;"></span> المبيعات: <span class="num">${fmtNum(d.totalSales)}</span></div>
                    <div class="donut-legend-item"><span class="donut-dot" style="background:#34d399;"></span> التحصيل: <span class="num">${fmtNum(d.totalPaid)}</span></div>
                    <div class="donut-legend-item"><span class="donut-dot" style="background:#fbbf24;"></span> المتبقي: <span class="num">${fmtNum(d.balance)}</span></div>
                </div>
            </div>
        </div>
        ${checksHtml}
        ${creditHtml}
    `;

    // Draw donut chart
    setTimeout(() => drawDonut(canvasId, d.totalPaid, d.balance), 50);
}

function getCustomerChecks(customerName) {
    // checksDB is loaded from checks-cycle mock data or localStorage
    const allChecks = window._checksDB || [];
    return allChecks.filter(c => c.type === 'incoming' && c.partner && c.partner.trim() === customerName.trim());
}

function getCheckStatusClass(status) {
    switch (status) {
        case 'في الخزنة': return 'check-status-safe';
        case 'برسم التحصيل': return 'check-status-collecting';
        case 'محصل': case 'مسحوب': return 'check-status-cleared';
        case 'مرتجع': return 'check-status-bounced';
        default: return 'check-status-safe';
    }
}

function drawDonut(canvasId, paid, remaining) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const size = 120;
    canvas.width = size * 2;
    canvas.height = size * 2;
    ctx.scale(2, 2); // HiDPI

    const cx = size / 2, cy = size / 2, r = 40, lineWidth = 16;
    const total = paid + remaining;

    if (total === 0) {
        // Draw empty circle
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(255,255,255,0.1)';
        ctx.lineWidth = lineWidth;
        ctx.stroke();
        ctx.fillStyle = '#64748b';
        ctx.font = '700 11px JetBrains Mono';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('0%', cx, cy);
        return;
    }

    const paidAngle = (paid / total) * Math.PI * 2;
    const remainAngle = (remaining / total) * Math.PI * 2;
    let startAngle = -Math.PI / 2;

    // Paid arc (green)
    if (paid > 0) {
        ctx.beginPath();
        ctx.arc(cx, cy, r, startAngle, startAngle + paidAngle);
        ctx.strokeStyle = '#34d399';
        ctx.lineWidth = lineWidth;
        ctx.lineCap = 'round';
        ctx.stroke();
        startAngle += paidAngle;
    }

    // Remaining arc (amber)
    if (remaining > 0) {
        ctx.beginPath();
        ctx.arc(cx, cy, r, startAngle + 0.04, startAngle + remainAngle);
        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = lineWidth;
        ctx.lineCap = 'round';
        ctx.stroke();
    }

    // Center text
    const pct = Math.round((paid / total) * 100);
    ctx.fillStyle = '#f8fafc';
    ctx.font = '800 13px JetBrains Mono';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(pct + '%', cx, cy);
}

// ═══════════════════════════════════════════════════════════════
// SORTING
// ═══════════════════════════════════════════════════════════════
function sortTable(col) {
    if (currentSort.col === col) {
        currentSort.dir = currentSort.dir === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.col = col;
        currentSort.dir = 'desc';
    }

    // Update header UI
    document.querySelectorAll('.portfolio-table thead th').forEach(th => th.classList.remove('sorted'));
    const th = document.querySelector(`.portfolio-table thead th[data-col="${col}"]`);
    if (th) {
        th.classList.add('sorted');
        th.querySelector('.sort-icon').textContent = currentSort.dir === 'asc' ? '↑' : '↓';
    }

    applySorting();
    renderTable();
}

function applySorting() {
    const { col, dir } = currentSort;
    reportData.sort((a, b) => {
        let va, vb;
        switch (col) {
            case 'idx': va = 0; vb = 0; break;
            case 'name': va = a.name; vb = b.name; break;
            case 'sales': va = a.totalSales; vb = b.totalSales; break;
            case 'paid': va = a.totalPaid; vb = b.totalPaid; break;
            case 'balance': va = a.balance; vb = b.balance; break;
            case 'lastTxn': va = a.lastTxnDate || ''; vb = b.lastTxnDate || ''; break;
            case 'status':
                const order = { overdue: 3, partial: 2, none: 1, paid: 0 };
                va = order[a.status] || 0; vb = order[b.status] || 0; break;
            case 'perf': va = a.collectionPct; vb = b.collectionPct; break;
            default: va = a.balance; vb = b.balance;
        }
        if (typeof va === 'string') {
            return dir === 'asc' ? va.localeCompare(vb, 'ar') : vb.localeCompare(va, 'ar');
        }
        return dir === 'asc' ? va - vb : vb - va;
    });
}

// ── Table Quick Filter ──
function filterTable() {
    const q = (document.getElementById('tableSearch').value || '').trim().toLowerCase();
    document.querySelectorAll('#tableBody tr.data-row').forEach(tr => {
        const idx = parseInt(tr.dataset.idx);
        const d = reportData[idx];
        if (!d) return;
        const match = !q || d.name.toLowerCase().includes(q) || (d.phone || '').includes(q);
        tr.style.display = match ? '' : 'none';
        const detailRow = document.getElementById(`detail-${idx}`);
        if (detailRow && !match) detailRow.classList.remove('open');
        if (detailRow) detailRow.style.display = match && detailRow.classList.contains('open') ? '' : 'none';
    });
}

// ═══════════════════════════════════════════════════════════════
// EXPORT FUNCTIONS
// ═══════════════════════════════════════════════════════════════

// Direct print bypasses the app.js global print approval workflow
function directPrint() {
    if (reportData.length === 0) { showToast('⚠️ قم بتشغيل التقرير أولاً', 'warning'); return; }
    // Use the native browser print (HTMLIFrameElement trick to bypass window.print override)
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    const nativePrint = iframe.contentWindow.print.bind(window);
    document.body.removeChild(iframe);
    nativePrint.call(window);
}

function exportExcel() {
    if (reportData.length === 0) { showToast('⚠️ لا توجد بيانات للتصدير', 'warning'); return; }
    if (typeof XLSX === 'undefined') { showToast('⚠️ مكتبة Excel غير محملة بعد', 'error'); return; }

    const rows = [['#', 'اسم العميل', 'إجمالي المبيعات', 'المبالغ المسددة', 'الرصيد المتبقي', 'آخر عملية', 'حالة السداد', 'نسبة التحصيل %']];
    reportData.forEach((d, i) => {
        const statusText = d.status === 'paid' ? 'مسدد بالكامل' : d.status === 'partial' ? 'سداد جزئي' : d.status === 'overdue' ? 'متأخر' : 'بدون حركة';
        rows.push([i + 1, d.name, d.totalSales, d.totalPaid, d.balance, d.lastTxnDate || '—', statusText, Math.round(d.collectionPct) + '%']);
    });

    // Add totals row
    const ts = reportData.reduce((s, d) => s + d.totalSales, 0);
    const tp = reportData.reduce((s, d) => s + d.totalPaid, 0);
    const tb = reportData.reduce((s, d) => s + d.balance, 0);
    rows.push(['', 'الإجمالي', ts, tp, tb, '', '', '']);

    const ws = XLSX.utils.aoa_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'الموقف المالي');
    XLSX.writeFile(wb, 'customer-portfolio-' + new Date().toISOString().slice(0, 10) + '.xlsx');
    showToast('✅ تم تصدير الملف بنجاح', 'success');
}

function exportPDF() {
    if (reportData.length === 0) { showToast('⚠️ لا توجد بيانات للتصدير', 'warning'); return; }
    if (typeof html2pdf === 'undefined') { showToast('⚠️ مكتبة PDF غير محملة بعد', 'error'); return; }

    const element = document.getElementById('tablePanel');
    const opt = {
        margin: 10,
        filename: 'customer-portfolio-' + new Date().toISOString().slice(0, 10) + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a3', orientation: 'landscape' }
    };

    showToast('📄 جاري إنشاء ملف PDF...', 'success');
    html2pdf().set(opt).from(element).save().then(() => {
        showToast('✅ تم تصدير PDF بنجاح', 'success');
    });
}
</script>

</body>
</html>
