<?php $page_title = 'دورة الشيكات'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دورة الشيكات | Al-Sajana ERP</title>
    <!-- Fonts -->
        <!-- Base Styles -->
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            transition: background 0.3s, color 0.3s;
        }

        .checks-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface-color);
            padding: 15px 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
        }

        .page-title-box h1 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--primary-accent);
            font-weight: 800;
        }
        .page-title-box p {
            margin: 5px 0 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* Tabs */
        .tabs-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 12px 25px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            background: var(--surface-color);
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(99,102,241,0.08));
            border-color: rgba(59,130,246,0.5);
            color: var(--primary-accent);
            box-shadow: 0 0 16px rgba(59,130,246,0.12);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(56,189,248,0.06);
            color: var(--text-primary);
        }

        /* Filters */
        .filters-panel {
            background: var(--surface-color);
            padding: 15px;
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .filter-input {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            background: var(--bg-color);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .filter-input:focus {
            border-color: var(--primary-accent);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--primary-accent);
            color: #fff;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

        /* Data Grid */
        .table-container {
            background: var(--surface-color);
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .checks-table {
            width: 100%;
            border-collapse: collapse;
        }

        .checks-table th {
            background: rgba(0,0,0,0.03);
            padding: 15px;
            text-align: right;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--glass-border);
            white-space: nowrap;
        }

        .checks-table td {
            padding: 15px;
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.95rem;
            font-weight: 600;
            vertical-align: middle;
        }

        .checks-table tbody tr:hover {
            background: rgba(56,189,248,0.03);
        }

        /* Checkboxes */
        .row-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-block;
        }
        .status-in-safe { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .status-collection { background: rgba(245,158,11,0.1); color: #f59e0b; }
        .status-cleared { background: rgba(16,185,129,0.1); color: #10b981; }
        .status-bounced { background: rgba(239,68,68,0.1); color: #ef4444; }
        .status-endorsed { background: rgba(168,85,247,0.1); color: #a855f7; }

        /* Actions Bar */
        .actions-bar {
            background: var(--surface-color);
            padding: 15px 25px;
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            position: sticky;
            bottom: 20px;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.1);
        }

        .btn-bank { background: #3b82f6; color: white; }
        .btn-clear { background: #10b981; color: white; }
        .btn-bounce { background: #ef4444; color: white; }
        .btn-endorse { background: #a855f7; color: white; }

        .amount-col {
            color: #10b981;
            font-weight: 800;
            font-family: monospace;
            font-size: 1.1rem;
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
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


    <div class="checks-wrapper">
        <!-- Top Bar -->
                <!-- Unified Header -->
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>
        <!-- Tabs -->
        <div class="tabs-container">
            <button class="tab-btn active" onclick="switchTab('incoming')" id="tab-incoming">
                <span>📥</span> شيكات مقبوضة (أوراق قبض)
            </button>
            <button class="tab-btn" onclick="switchTab('outgoing')" id="tab-outgoing">
                <span>📤</span> شيكات مصدرة (أوراق دفع)
            </button>
        </div>

        <!-- Filters -->
        <div class="filters-panel">
            <div class="filter-group">
                <label>تصفية حسب الحالة (Status)</label>
                <select class="filter-input" id="filter-status" onchange="renderTable()">
                    <option value="all">الكل (All)</option>
                    <option value="في الخزينة">في الخزينة (In Safe)</option>
                    <option value="تحت التحصيل">تحت التحصيل (Under Collection)</option>
                    <option value="محصل">محصل (Cleared)</option>
                    <option value="مرتجع">مرتجع (Bounced)</option>
                    <option value="مجير">مجيّر (Endorsed)</option>
                </select>
            </div>
            <div class="filter-group">
                <label>تاريخ الاستحقاق إلى (Due Date To)</label>
                <input type="date" class="filter-input" id="filter-date" onchange="renderTable()">
            </div>
            <div class="filter-group" style="flex: 0;">
                <button class="btn btn-primary" style="height: 44px; margin-bottom: 2px;" onclick="renderTable()">بحث</button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="checks-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll" class="row-checkbox" onchange="toggleSelectAll()"></th>
                        <th>رقم الشيك (Check No.)</th>
                        <th id="th-partner">العميل (Customer)</th>
                        <th>البنك (Bank)</th>
                        <th>المبلغ (Amount)</th>
                        <th>تاريخ الاستحقاق (Due Date)</th>
                        <th>الحالة (Status)</th>
                    </tr>
                </thead>
                <tbody id="checks-tbody">
                    <!-- Rows will be injected by JS -->
                </tbody>
            </table>
        </div>

        <!-- Actions -->
        <div class="actions-bar" id="incoming-actions">
            <button class="btn btn-bank" onclick="changeSelectedStatus('تحت التحصيل')">
                إرسال للبنك (Send to Bank)
            </button>
            <button class="btn btn-clear" onclick="changeSelectedStatus('محصل')">
                تأكيد التحصيل (Clear Check)
            </button>
            <button class="btn btn-bounce" onclick="changeSelectedStatus('مرتجع')">
                إرجاع (Bounce)
            </button>
            <button class="btn btn-endorse" onclick="changeSelectedStatus('مجير')">
                تجيير لجهة أخرى (Endorse)
            </button>
        </div>

        <div class="actions-bar" id="outgoing-actions" style="display: none;">
            <button class="btn btn-clear" onclick="changeSelectedStatus('محصل')">
                تم الصرف (Cleared from Bank)
            </button>
            <button class="btn btn-bounce" onclick="changeSelectedStatus('مرتجع')">
                مرتجع (Returned)
            </button>
        </div>

    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260803.76188"></script>
    <script defer src="js/checks-cycle.js"></script>
</body>
</html>
