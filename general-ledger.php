<?php $page_title = 'دفتر الأستاذ العام الذكي (SGL)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفتر الأستاذ العام الذكي (SGL) | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <style>
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; font-family:'Cairo',sans-serif; }
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.warning { background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.4); color:#fbbf24; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }

        /* SGL Theme */
        .ledger-header { background: linear-gradient(135deg, rgba(192,132,252,0.15), rgba(15,23,42,0.9)); border: 1px solid rgba(192,132,252,0.2); border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .badge-sgl { background: #c084fc; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 900; letter-spacing: 1px; display: inline-block; vertical-align: middle; margin-right: 10px; }
        
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-group label { color: #94a3b8; font-size: 0.85rem; font-weight: 700; }
        .filter-input { background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: #f8fafc; padding: 10px 15px; border-radius: 10px; font-family: inherit; font-size: 0.95rem; outline: none; transition: all 0.2s; width: 100%; box-sizing: border-box; }
        .filter-input:focus { border-color: rgba(192,132,252,0.5); box-shadow: 0 0 0 3px rgba(192,132,252,0.1); }
        
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .summary-card { background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); position: relative; overflow: hidden; }
        .summary-card::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(45deg, transparent, rgba(255,255,255,0.02), transparent); transform: translateX(-100%); transition: transform 0.5s; }
        .summary-card:hover::after { transform: translateX(100%); }
        .s-label { color: #64748b; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; }
        .s-val { font-size: 1.5rem; font-weight: 900; letter-spacing: 0.5px; }

        .ledger-table-wrap { overflow-x: auto; background: rgba(15,23,42,0.5); border-radius: 12px; }
        .ledger-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .ledger-table th, .ledger-table td { padding: 12px 15px; text-align: right; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
        .ledger-table th { background: rgba(0,0,0,0.3); color: #94a3b8; font-weight: 700; white-space: nowrap; }
        .ledger-table tbody tr:hover { background: rgba(255,255,255,0.02); }
        .ledger-table tbody tr { transition: background 0.15s; }
        
        .td-debit { color: #34d399; font-weight: 700; font-family: monospace; }
        .td-credit { color: #f87171; font-weight: 700; font-family: monospace; }
        .td-balance { color: #c084fc; font-weight: 800; font-family: monospace; }
        .td-empty { color: rgba(255,255,255,0.15); }

        .btn-run { background: #c084fc; color: white; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; font-family: inherit; font-size: 0.95rem; }
        .btn-run:hover { background: #a855f7; transform: translateY(-1px); }
        .btn-action { background: rgba(255,255,255,0.05); color: #f8fafc; border: 1px solid rgba(255,255,255,0.1); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-family: inherit; }
        .btn-action:hover { background: rgba(255,255,255,0.1); }
        .btn-attach { background: rgba(56,189,248,0.1); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); padding: 4px 8px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; }
        .btn-attach:hover { background: rgba(56,189,248,0.2); }

        .empty-state { text-align: center; padding: 60px 20px; color: #475569; }

        /* Autocomplete Search Box */
        .search-wrap { position: relative; }
        .search-wrap .filter-input { padding-right: 36px; }
        .search-clear-btn {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #64748b; cursor: pointer;
            font-size: 1.1rem; display: none; padding: 0;
        }
        .autocomplete-list {
            position: absolute; top: calc(100% + 4px); right: 0; left: 0;
            background: #1e293b; border: 1px solid rgba(192,132,252,0.4);
            border-radius: 10px; z-index: 999; max-height: 220px;
            overflow-y: auto; box-shadow: 0 12px 30px rgba(0,0,0,0.6);
            display: none;
        }
        .autocomplete-list li {
            list-style: none; padding: 10px 14px; cursor: pointer;
            color: #e2e8f0; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 8px; transition: background 0.15s;
        }
        .autocomplete-list li:hover, .autocomplete-list li.active { background: rgba(192,132,252,0.15); color: #c084fc; }
        .autocomplete-list li .ac-badge { font-size: 0.72rem; padding: 2px 6px; border-radius: 6px; background: rgba(255,255,255,0.08); }
        .selected-account-chip {
            display: none; align-items: center; gap: 8px; background: rgba(192,132,252,0.1);
            border: 1px solid rgba(192,132,252,0.3); border-radius: 8px;
            padding: 8px 12px; margin-top: 6px; font-size: 0.88rem; color: #c084fc;
        }
        .chip-remove { background: none; border: none; cursor: pointer; color: #f87171; font-size: 1rem; padding: 0; }

        .audit-stamp {
            margin-top: 20px; padding: 15px; background: rgba(52,211,153,0.05);
            border: 1px dashed rgba(52,211,153,0.3); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: #34d399; font-weight: bold; font-size: 0.9rem;
        }

        /* Attachment Modal */
        .modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); display:none; justify-content:center; align-items:center; z-index:100000; backdrop-filter: blur(5px); }
        .modal-content { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; text-align: center; }
        .modal-title { color: white; margin-bottom: 20px; font-size: 1.2rem; font-weight: bold; }
        .modal-btn { background: #38bdf8; color: #0f172a; padding: 8px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; margin-top: 15px; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .ledger-table thead th { background: #eee !important; color: #333 !important; }
            .ledger-table tbody td { color: #000 !important; border-color: #ddd !important; }
            .audit-stamp { border-color: #333; color: #333; }
        }
    </style>

    <!-- PREMIUM SGL CSS OVERRIDES -->
    <style>
        /* Global Glow & Smoothness */
        body {
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a 40%, #020617 100%) !important;
            color: #e2e8f0;
        }

        /* Animated Gradient Header */
        .premium-header {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.15) 0%, rgba(59, 130, 246, 0.15) 100%) !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important;
            box-shadow: 0 10px 40px rgba(168, 85, 247, 0.1) !important;
            position: relative;
            overflow: hidden;
            border-radius: 20px !important;
        }
        .premium-header::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(168,85,247,0.1) 0%, transparent 60%);
            animation: rotateGlow 15s linear infinite;
        }
        @keyframes rotateGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Glassmorphism Summary Cards */
        .summary-card {
            background: rgba(30, 41, 59, 0.6) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border-radius: 16px !important;
        }
        .summary-card:hover {
            transform: translateY(-8px) scale(1.02) !important;
            background: rgba(30, 41, 59, 0.8) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(168, 85, 247, 0.5) !important;
        }
        .summary-card .s-val {
            background: linear-gradient(to right, #c084fc, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(192, 132, 252, 0.3);
        }

        /* Filter Inputs - Neon Focus */
        .filter-input {
            background: rgba(15, 23, 42, 0.7) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            transition: all 0.3s ease !important;
            border-radius: 12px !important;
        }
        .filter-input:focus {
            border-color: #a855f7 !important;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15), inset 0 0 10px rgba(168, 85, 247, 0.1) !important;
            background: rgba(15, 23, 42, 0.9) !important;
            transform: translateY(-1px);
        }

        /* Themed Buttons */
        .btn-run {
            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%) !important;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4) !important;
            border-radius: 12px !important;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .btn-run::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #9333ea 0%, #6d28d9 100%);
            z-index: -1;
            transition: opacity 0.3s ease;
            opacity: 0;
        }
        .btn-run:hover::after { opacity: 1; }
        .btn-run:hover {
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.6) !important;
        }

        /* Table Enhancements & Entry Animation */
        .ledger-table-wrap {
            border-radius: 16px !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
            background: rgba(15, 23, 42, 0.4) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3) !important;
        }
        .ledger-table th {
            background: rgba(30, 41, 59, 0.8) !important;
            color: #cbd5e1 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem !important;
            border-bottom: 2px solid rgba(168, 85, 247, 0.3) !important;
        }
        .ledger-table tbody tr {
            animation: fadeInRow 0.5s ease backwards;
        }
        .ledger-table tbody tr:hover {
            background: rgba(168, 85, 247, 0.08) !important;
            transform: scale(1.002);
            box-shadow: inset 4px 0 0 #a855f7;
        }
        @keyframes fadeInRow {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Generate stagger delays for rows */
        .ledger-table tbody tr:nth-child(1) { animation-delay: 0.05s; }
        .ledger-table tbody tr:nth-child(2) { animation-delay: 0.10s; }
        .ledger-table tbody tr:nth-child(3) { animation-delay: 0.15s; }
        .ledger-table tbody tr:nth-child(4) { animation-delay: 0.20s; }
        .ledger-table tbody tr:nth-child(5) { animation-delay: 0.25s; }
        .ledger-table tbody tr:nth-child(6) { animation-delay: 0.30s; }
        .ledger-table tbody tr:nth-child(7) { animation-delay: 0.35s; }
        .ledger-table tbody tr:nth-child(8) { animation-delay: 0.40s; }
        .ledger-table tbody tr:nth-child(9) { animation-delay: 0.45s; }
        .ledger-table tbody tr:nth-child(10) { animation-delay: 0.50s; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); }
        ::-webkit-scrollbar-thumb { background: rgba(168, 85, 247, 0.5); border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(168, 85, 247, 0.8); }

        /* AI Button Glow */
        #btn-smart-search {
            animation: pulse-border 2s infinite;
        }
        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
        }
    </style>
    \n</head>
<body>
<div class="toast-container" id="toast-container"></div>

<!-- Modal -->
<div class="modal-overlay" id="attach-modal">
    <div class="modal-content">
        <div style="font-size:3rem; margin-bottom:10px;">📎</div>
        <div class="modal-title">المرفقات الحالية</div>
        <p style="color:#94a3b8; font-size:0.9rem;">هنا تظهر جميع المرفقات المرتبطة بالقيد، يمكنك عرض أي منها أو إزالتها.</p>
        <button class="modal-btn" onclick="document.getElementById('attach-modal').style.display='none'">إغلاق</button>
    </div>
</div>

<div style="max-width: 1600px; margin: 0 auto; padding: 20px 30px; padding-bottom: 80px;">

    <!-- BREADCRUMB -->
    <div class="page-breadcrumb no-print" style="display:inline-flex; align-items:center; background:rgba(0,0,0,0.2); padding:8px 15px; border-radius:8px; margin-bottom:20px;">
        <button onclick="history.back()" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); cursor:pointer; padding:4px 8px; border-radius:4px; margin-left:8px; color:#94a3b8; display:flex;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <a data-href="financial-reports.php" onclick="window.location.href='financial-reports.php'" style="color:#c084fc; text-decoration:none; font-weight:700;">التقارير المالية</a>
        <span style="margin:0 10px; color:#475569;">/</span>
        <span style="color:#f8fafc;">دفتر الأستاذ العام الذكي (SGL)</span>
    </div>

    <!-- PAGE TITLE -->
    <div style="margin-bottom: 25px;" class="no-print">
        <h1 style="color:#fff; margin:0 0 5px 0; font-size:2rem; font-weight:900;">
            دفتر الأستاذ العام الذكي 
            <span class="badge-sgl">SGL PRO</span>
        </h1>
        <p style="color:#64748b; margin:0;">Smart General Ledger — تتبع الحركات المحاسبية من وإلى الحسابات الرئيسية</p>
    </div>

    <!-- FILTER SECTION -->
    <div class="ledger-header no-print premium-header">
        <div class="filter-row">
            <div class="filter-group">
                <label>الحساب الرئيسي (أو الفرعي)</label>
                <div class="search-wrap" style="display:flex; gap:5px; align-items: stretch; position: relative;">
                    <div style="position:relative; flex:1;">
                        <input type="text" class="filter-input" id="account-search-input" placeholder="ابحث باسم أو كود الحساب..." autocomplete="off" oninput="onAccountSearch()" onkeyup="onAccountSearchKey(event)">
                        <button class="search-clear-btn" id="search-clear-btn">✕</button>
                        <ul class="autocomplete-list" id="autocomplete-list"></ul>
                    </div>
                    <button type="button" class="btn-action" onclick="runAISmartSearch()" id="btn-smart-search" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color:white; border:none; padding: 0 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(139, 92, 246, 0.3); font-weight: bold; cursor: pointer; transition: 0.3s;" title="البحث بالمعنى (الذكاء الاصطناعي)">🤖 ذكي</button>
                </div>
                <div class="selected-account-chip" id="selected-chip">
                    <span id="chip-icon">🏷️</span>
                    <span id="chip-name"></span>
                    <button class="chip-remove" onclick="clearAccountSearch()" title="إلغاء">✕</button>
                </div>
                <input type="hidden" id="filter-account" value="ALL">
            </div>
            <div class="filter-group">
                <label>من تاريخ</label>
                <input type="date" class="filter-input" id="filter-from">
            </div>
            <div class="filter-group">
                <label>إلى تاريخ</label>
                <input type="date" class="filter-input" id="filter-to">
            </div>
        </div>
        <div class="filter-row">
            <div class="filter-group">
                <label>نوع الحركة</label>
                <select class="filter-input" id="filter-type">
                    <option value="ALL">جميع الحركات</option>
                    <option value="receipt">سند قبض</option>
                    <option value="payment">سند صرف</option>
                    <option value="jv">قيد يومية</option>
                    <option value="invoice">فاتورة مبيعات</option>
                    <option value="purchase">فاتورة مشتريات</option>
                </select>
            </div>
            <div class="filter-group">
                <label>مركز التكلفة (Cost Center)</label>
                <select class="filter-input" id="filter-costcenter">
                    <option value="ALL">الكل</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <label>العملة (Currency)</label>
                <select class="filter-input" id="filter-currency">
                    <option value="ALL">جميع العملات</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:20px; align-items:center; flex-wrap:wrap;">
            <button class="btn-run" onclick="runReport()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                تشغيل التقرير الذكي
            </button>
            <button class="btn-action" onclick="previewGL()">عرض للطباعة</button>
            <button class="btn-action" onclick="window.print()">طباعة مباشرة</button>
            <button class="btn-action" onclick="window.openExportModal(exportCSV, {element: document.getElementById('gl-table-wrap'), filename: 'General_Ledger.pdf', orientation: 'landscape'})">تصدير PDF/Excel</button>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="summary-cards" id="summary-cards" style="display:none;">
        <div class="summary-card" style="border-top:3px solid #34d399;">
            <div class="s-label">إجمالي المدين (Debit)</div>
            <div class="s-val td-debit" id="sum-debit">0.00</div>
        </div>
        <div class="summary-card" style="border-top:3px solid #f87171;">
            <div class="s-label">إجمالي الدائن (Credit)</div>
            <div class="s-val td-credit" id="sum-credit">0.00</div>
        </div>
        <div class="summary-card" style="border-top:3px solid #c084fc;">
            <div class="s-label">الرصيد النهائي (Balance)</div>
            <div class="s-val" id="sum-balance" style="color:#c084fc;">0.00</div>
        </div>
    </div>

    <!-- LEDGER TABLE -->
    <div class="glass-panel" id="ledger-panel" style="padding:0; overflow:hidden;">
        <!-- Print Header -->
        <div style="padding:20px 20px 0 20px; display:none;" id="print-header">
            <h2 style="text-align:center; margin:0 0 5px 0;">التقرير العام للحسابات</h2>
            <h3 style="text-align:center; color:#555; margin:0 0 20px 0;">دفتر الأستاذ العام الذكي (SGL)</h3>
            <div id="print-meta" style="display:flex; justify-content:space-between; border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:15px; font-size:0.9rem;"></div>
        </div>

        <div class="ledger-table-wrap">
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>التاريخ والوقت</th>
                        <th>رقم القيد / المرجع</th>
                        <th>نوع الحركة</th>
                        <th>البيان (وصف الحركة)</th>
                        <th>مدين (Debit)</th>
                        <th>دائن (Credit)</th>
                        <th>الرصيد (Balance)</th>
                        <th>المرفقات</th>
                    </tr>
                </thead>
                <tbody id="ledger-tbody">
                    <tr>
                        <td colspan="9" class="empty-state">
                            <span style="font-size:3rem; display:block; margin-bottom:15px;">🔍</span>
                            <div>يرجى تحديد الحساب والفترة ثم الضغط على "تشغيل التقرير الذكي"</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Audit Stamp -->
        <div class="audit-stamp" id="audit-stamp" style="display:none;">
            <span>✔️</span>
            <span>تدقيق معتمد: الأرصدة صحيحة ومطابقة وفق المعايير المحاسبية المعتمدة (Blockchain Ready).</span>
        </div>
    </div>

</div>

<script src="js/app.js?v=20260626_4"></script>
<script src="js/general-ledger.js"></script>
<script>
function runAISmartSearch() {
    const input = document.getElementById('account-search-input');
    const q = input.value.trim();
    const btn = document.getElementById('btn-smart-search');
    
    if (!q) {
        showToast('الرجاء كتابة وصف للحساب أولاً', 'warning');
        input.focus();
        return;
    }

    if (typeof ALL_ACCOUNTS === 'undefined' || ALL_ACCOUNTS.length <= 1) {
        showToast('قائمة الحسابات غير متوفرة بعد', 'error');
        return;
    }

    btn.innerHTML = '⏳...';
    btn.disabled = true;

    let accountsList = ALL_ACCOUNTS.filter(a => a.id !== 'ALL').map(a => `${a.id} - ${a.name} (${a.type})`).join('\n');
    
    let promptMsg = "أنت مساعد بحث ذكي في نظام محاسبي. المستخدم يبحث عن الحساب الأقرب للمعنى التالي: '" + q + "'.\nإليك قائمة الحسابات المتاحة:\n" + accountsList + "\n\nاستخرج فقط وفقط 'ID' (الكود) الخاص بأقرب حساب يطابق نية المستخدم. لا تكتب أي كلمة أخرى، فقط الـ ID.";

    fetch('api/ai-assistant.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: promptMsg, context: '' })
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '🤖 ذكي';
        btn.disabled = false;
        
        if (data.reply) {
            let matchId = data.reply.trim().replace(/[^a-zA-Z0-9-]/g, '');
            let matchedAccount = ALL_ACCOUNTS.find(a => a.id === matchId);
            
            if (matchedAccount) {
                if (typeof selectAccount === 'function') {
                    selectAccount(matchedAccount.id, matchedAccount.name, matchedAccount.icon || '🏷️');
                    showToast('تم إيجاد الحساب الذكي: ' + matchedAccount.name);
                }
            } else {
                showToast('لم يتم العثور على حساب مطابق بدقة.', 'warning');
            }
        } else {
            showToast('حدث خطأ في البحث الذكي.', 'error');
        }
    })
    .catch(err => {
        btn.innerHTML = '🤖 ذكي';
        btn.disabled = false;
        showToast('تعذر الاتصال بالخادم.', 'error');
    });
}
</script>
</body>
</html>
