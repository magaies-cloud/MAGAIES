<?php $page_title = 'سند قبض شامل'; ?>
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
    <title>سند قبض شامل | مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    <style>
        :root {
            --accent: #38bdf8;
            --accent-glow: rgba(56, 189, 248, 0.4);
        }
        body {
            background-color: #050308;
            color: #f8fafc;
            min-height: 100vh;
            margin: 0;
            padding: 2vh 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Tajawal', sans-serif;
            background-image: radial-gradient(circle at top right, rgba(56, 189, 248, 0.1), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(139, 92, 246, 0.1), transparent 40%);
            box-sizing: border-box;
        }
        .main-container {
            width: 96%;
            max-width: 1400px;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), inset 0 1px 1px rgba(255,255,255,0.1);
            display: flex;
            flex-direction: column;
            animation: slideUp 0.6s ease-out;
            /* Remove fixed height to allow expansion, but keep max-height safe */
            max-height: 96vh;
            overflow-y: auto;
        }

        /* Custom Scrollbar */
        .main-container::-webkit-scrollbar { width: 6px; }
        .main-container::-webkit-scrollbar-track { background: transparent; }
        .main-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            padding: 15px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, rgba(56,189,248,0.1), transparent);
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        .header h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header h1 span {
            font-size: 1.8rem;
        }

        .header-meta {
            display: flex;
            gap: 12px;
        }
        .meta-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.05));
            border: 1px solid rgba(16, 185, 129, 0.3);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);
            padding: 5px 12px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .meta-box label {
            font-size: 0.7rem;
            color: #a7f3d0;
            margin-bottom: 2px;
        }
        .meta-box span {
            font-size: 0.95rem;
            font-weight: 700;
            color: #10b981;
            font-family: 'Outfit', sans-serif;
            text-shadow: 0 0 5px rgba(16, 185, 129, 0.5);
        }

        .content-grid {
            padding: 15px 25px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px 20px; /* smaller vertical gap, slightly wider horizontal */
            flex: 1;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 4px; /* Reduced gap */
        }
        .input-group label {
            font-size: 0.8rem; /* Slightly smaller label */
            color: #cbd5e1;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px; /* Reduced padding */
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.08);
            border-color: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
        }

        .amount-display {
            font-size: 1.5rem;
            font-weight: 900;
            color: #10b981;
            font-family: 'Outfit', sans-serif;
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
            text-align: left;
            direction: ltr;
            background: rgba(16, 185, 129, 0.05);
            border-color: rgba(16, 185, 129, 0.3);
        }

        /* Payment Type Toggle - Small & Compact */
        .payment-methods-wrapper {
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 8px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .type-btn {
            padding: 6px 4px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 6px;
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .type-btn:hover {
            background: rgba(255,255,255,0.08);
        }
        .type-btn.active {
            background: linear-gradient(135deg, var(--accent), #818cf8);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 3px 10px var(--accent-glow);
            transform: translateY(-1px);
        }

        /* Dynamic Section */
        .dynamic-section {
            grid-column: 1 / -1;
            background: rgba(16, 185, 129, 0.05);
            border: 1px dashed rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 15px;
            display: none;
            grid-template-columns: repeat(4, 1fr); /* 4 cols to fit all in one line */
            gap: 12px;
            animation: fadeIn 0.3s;
            margin-top: 5px;
        }
        .dynamic-section.active {
            display: grid;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Footer */
        .footer {
            padding: 15px 25px;
            border-top: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0,0,0,0.3);
            position: sticky;
            bottom: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-family: inherit;
        }
        .btn-cancel {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.2); }
        .btn-save {
            background: #10b981;
            color: #fff;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        /* Datalist smart filter wrapper */
        .search-wrapper { position: relative; }
        .search-results {
            position: absolute; top: 100%; left: 0; right: 0;
            background: #1e293b; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; max-height: 180px; overflow-y: auto;
            z-index: 100; display: none; box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            margin-top: 4px;
        }
        .search-item {
            padding: 8px 12px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #fff; display: flex; justify-content: space-between; font-size: 0.85rem;
        }
        .search-item:hover { background: rgba(56,189,248,0.2); }
        .search-item-type { font-size: 0.7rem; color: #94a3b8; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; }

    
        
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
    <div class="main-container">
        <!-- Header -->
        <div class="header">
            <h1><span>📑</span> سند قبض شامل</h1>
            <div class="header-meta">
                <div class="meta-box">
                    <label>الرقم المتسلسل</label>
                    <span id="serial-number">CR-1000</span>
                </div>
                <div class="meta-box">
                    <label>التاريخ</label>
                    <span id="current-date">2026-06-18</span>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            
            <!-- Row 1: Core Financials -->
            <div class="input-group search-wrapper">
                <label>👤 القبض من حساب (ذكي)</label>
                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                    <input type="hidden" id="from-account">
                    <input type="text" class="form-control" id="from-account-search" placeholder="بحث اسم العميل أو الحساب..." onfocus="showAccountDropdown('from')" onblur="hideAccountDropdownDelayed('from')" oninput="filterAccountDropdown('from')" style="width: 100%; box-sizing: border-box; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);" autocomplete="off">
                    <div class="custom-dropdown-list" id="from-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                </div>
            </div>
            
            <div class="input-group">
                <label>📝 البيان الرئيسي</label>
                <input type="text" class="form-control" id="main-desc" placeholder="عن دفعة من الحساب...">
            </div>

            <div class="input-group">
                <label>💰 المبلغ المستلم</label>
                <input type="number" step="0.01" class="form-control amount-display" id="amount" placeholder="0.00">
            </div>

            <!-- Row 2: Secondary Details -->
            <div class="input-group">
                <label>🏢 مركز التكلفة</label>
                <select class="form-control" id="cost-center">
                    <option value="">-- بدون مركز تكلفة --</option>
                    <option value="main">الفرع الرئيسي</option>
                    <option value="project-a">مشروع أ</option>
                    <option value="project-b">مشروع ب</option>
                </select>
            </div>

            <div class="input-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="input-group">
                    <label>💵 العملة</label>
                    <select class="form-control system-currency-select" id="currency"
                        <option value="SDG">جنيه سوداني (SDG)</option>
                        <option value="USD">دولار (USD)</option>
                        <option value="SAR">ريال (SAR)</option>
                        <option value="AED">درهم (AED)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>⚖️ التعادل</label>
                    <input type="number" step="0.01" class="form-control" id="exchange-rate" value="1.00">
                </div>
            </div>

            <div class="input-group">
                <label>🤝 المستفيد</label>
                <input type="text" class="form-control" id="beneficiary" placeholder="اسم المستلم الفعلي (اختياري)">
            </div>

            <!-- Row 3: Depository & Payment Method Toggle -->
            <div class="input-group">
                <label>🏦 إيداع في (الخزينة/البنك)</label>
                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                    <input type="hidden" id="deposit-account">
                    <input type="text" class="form-control" id="deposit-account-search" placeholder="-- اختر الحساب المعني --" onfocus="showAccountDropdown('deposit')" onblur="hideAccountDropdownDelayed('deposit')" oninput="filterAccountDropdown('deposit')" style="width: 100%; box-sizing: border-box; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);" autocomplete="off">
                    <div class="custom-dropdown-list" id="deposit-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                </div>
            </div>
            
            <div class="input-group">
                <label>📄 بيان الإيداع</label>
                <input type="text" class="form-control" id="deposit-desc" placeholder="ملاحظات حول الإيداع...">
            </div>

            <!-- The Payment Method Toggle is now compact and sits to the right of Deposit Desc -->
            <div class="input-group">
                <label>💳 طريقة السداد</label>
                <div class="payment-methods-wrapper">
                    <button class="type-btn active" data-type="cash" onclick="setType('cash')">💵 نقدي</button>
                    <button class="type-btn" data-type="check" onclick="setType('check')">🧾 شيك</button>
                    <button class="type-btn" data-type="bank" onclick="setType('bank')">🏦 بنكي</button>
                    <button class="type-btn" data-type="mobile" onclick="setType('mobile')">📱 جوال</button>
                </div>
            </div>

            <!-- Dynamic Sections (Row 4 spanning all cols) -->
            <!-- Check Section -->
            <div id="section-check" class="dynamic-section">
                <div class="input-group">
                    <label>الخزينة/الدرج (لحفظ الشيك)</label>
                    <select class="form-control" id="check-safe">
                        <option value="">-- اختر الخزينة --</option>
                        <!-- Will be populated by JS -->
                    </select>
                </div>
                <div class="input-group">
                    <label>رقم الشيك</label>
                    <input type="text" class="form-control" id="check-number" placeholder="أدخل رقم الشيك">
                </div>
                <div class="input-group">
                    <label>تاريخ الشيك</label>
                    <input type="date" class="form-control" id="check-date">
                </div>
                <div class="input-group">
                    <label>تاريخ الاستحقاق</label>
                    <input type="date" class="form-control" id="check-due-date">
                </div>
                <div class="input-group">
                    <label>البنك المسحوب عليه</label>
                    <input type="text" class="form-control" id="check-bank" placeholder="اسم البنك">
                </div>
            </div>

            <!-- Bank Transfer Section -->
            <div id="section-bank" class="dynamic-section">
                <div class="input-group">
                    <label>اسم البنك المحول منه</label>
                    <select class="form-control" id="bank-source-name">
                        <option value="">-- اختر البنك --</option>
                        <!-- Will be populated by JS -->
                    </select>
                </div>
                <div class="input-group">
                    <label>رقم الحوالة / الإيصال</label>
                    <input type="text" class="form-control" id="bank-ref" placeholder="رقم المرجع">
                </div>
                <div class="input-group">
                    <label>تاريخ التحويل</label>
                    <input type="date" class="form-control" id="bank-date">
                </div>
                <div class="input-group" style="grid-column: 1 / -1;">
                    <label>بيان التحويلة</label>
                    <input type="text" class="form-control" id="bank-desc" placeholder="أدخل بيان التحويلة (اختياري)">
                </div>
            </div>

            <!-- Mobile Transfer Section -->
            <div id="section-mobile" class="dynamic-section">
                <div class="input-group">
                    <label>المحفظة المحول منها</label>
                    <select class="form-control" id="mobile-wallet">
                        <option value="bankok">بنكك (Bankak)</option>
                        <option value="fawry">فوري (Fawry)</option>
                        <option value="o-cash">أوكاش (O-Cash)</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>رقم هاتف المحول</label>
                    <input type="text" class="form-control" id="mobile-number" placeholder="09xxxxxxx">
                </div>
                <div class="input-group">
                    <label>رقم الإشعار / المرجع</label>
                    <input type="text" class="form-control" id="mobile-ref" placeholder="رقم الإشعار">
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <button class="btn btn-cancel" onclick="window.location.href='vouchers-hub.php'">إلغاء</button>
            <button class="btn btn-save" onclick="saveVoucher()">💾 حفظ واعتماد السند</button>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js"></script>
    <script defer src="js/comprehensive-receipt.js"></script>

<script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
