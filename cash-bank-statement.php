<?php $page_title = 'دفتر أستاذ الخزن والبنوك'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفتر أستاذ الخزن والبنوك | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    
    <style>
        :root {
            --glass-bg: rgba(18, 15, 25, 0.65);
            --glass-border: rgba(255, 255, 255, 0.08);
            --neon-blue: #38bdf8;
            --neon-green: #34d399;
            --neon-red: #fb7185;
            --neon-purple: #c084fc;
        }

        body {
            background: var(--bg-color);
            color: var(--text-main);
            font-family: 'Tajawal', sans-serif;
            overflow-x: hidden;
            margin: 0; padding: 0;
        }

        /* Animated Ambient Background */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            z-index: -1; overflow: hidden; background: #0f0a18;
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px);
            animation: float 20s infinite ease-in-out alternate;
        }
        .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(56,189,248,0.15), transparent 70%); top: -100px; right: -100px; animation-delay: 0s; }
        .orb-2 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(168,85,247,0.12), transparent 70%); bottom: -200px; left: -100px; animation-delay: -5s; }
        .orb-3 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(16,185,129,0.1), transparent 70%); top: 40%; left: 30%; animation-delay: -10s; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(100px, -50px) scale(1.2); }
        }

        .page-wrapper {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Header Styling */
        .header-section {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 35px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px;
        }
        
        .header-icon-box {
            width: 60px; height: 60px; border-radius: 18px;
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; box-shadow: 0 10px 25px rgba(56,189,248,0.4);
            position: relative; overflow: hidden;
        }
        .header-icon-box::after {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: rotate(45deg); animation: shine 3s infinite;
        }
        @keyframes shine { 0% { left: -100%; } 20% { left: 100%; } 100% { left: 100%; } }

        /* Glass Filter Panel */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border-radius: 24px; border: 1px solid var(--glass-border);
            padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.5), inset 0 1px 1px rgba(255,255,255,0.05);
            margin-bottom: 30px; position: relative; overflow: hidden;
        }

        .filter-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px;
        }

        .input-group { position: relative; }
        .input-group label {
            position: absolute; top: -10px; right: 15px; background: #161122; padding: 0 8px;
            font-size: 0.8rem; font-weight: 700; color: var(--neon-blue); border-radius: 4px;
            transition: all 0.3s; pointer-events: none;
        }
        
        .glass-input {
            width: 100%; padding: 16px 20px; background: rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; color: #fff;
            font-family: inherit; font-size: 1.05rem; font-weight: 500; transition: all 0.3s;
            box-sizing: border-box; outline: none;
        }
        .glass-input:focus {
            border-color: var(--neon-blue); background: rgba(56,189,248,0.05);
            box-shadow: 0 0 0 4px rgba(56,189,248,0.15);
        }

        /* Action Buttons */
        .btn-run {
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            border: none; color: white; padding: 15px 35px;
            font-size: 1.15rem; font-weight: 800; border-radius: 16px;
            cursor: pointer; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex; align-items: center; gap: 10px; font-family: inherit;
            box-shadow: 0 10px 25px rgba(56,189,248,0.4); position: relative; overflow: hidden;
        }
        .btn-run:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 35px rgba(56,189,248,0.6); }
        .btn-run:active { transform: translateY(1px) scale(0.98); }
        
        .btn-print {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: #fff; padding: 15px 30px; font-size: 1.1rem; font-weight: 700;
            border-radius: 16px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px; font-family: inherit;
        }
        .btn-print:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); }

        /* KPI Cards (Staggered Animation) */
        .kpi-wrapper {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;
        }
        .kpi-card {
            background: linear-gradient(145deg, rgba(30,41,59,0.5) 0%, rgba(15,23,42,0.8) 100%);
            backdrop-filter: blur(20px); border-radius: 20px; padding: 25px;
            border: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); transition: all 0.4s;
            opacity: 0; transform: translateY(20px);
        }
        .kpi-card.show { opacity: 1; transform: translateY(0); }
        .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.15); }
        
        .kpi-card::before {
            content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%;
        }
        .kpi-blue::before { background: var(--neon-blue); box-shadow: 0 0 15px var(--neon-blue); }
        .kpi-green::before { background: var(--neon-green); box-shadow: 0 0 15px var(--neon-green); }
        .kpi-red::before { background: var(--neon-red); box-shadow: 0 0 15px var(--neon-red); }
        .kpi-purple::before { background: var(--neon-purple); box-shadow: 0 0 15px var(--neon-purple); }

        .kpi-title { display: block; font-size: 0.95rem; color: #94a3b8; font-weight: 700; margin-bottom: 8px; }
        .kpi-value { font-size: 2.2rem; font-weight: 900; font-family: 'Inter', sans-serif; letter-spacing: -0.5px; }
        .kpi-icon { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-size: 3.5rem; opacity: 0.05; transition: 0.5s; }
        .kpi-card:hover .kpi-icon { transform: translateY(-50%) scale(1.2); opacity: 0.1; }

        /* Ledger Table */
        .table-container {
            border-radius: 24px; overflow: hidden; border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5); background: rgba(0,0,0,0.3);
            backdrop-filter: blur(15px);
        }
        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: right; color: #fff; }
        .premium-table th {
            background: rgba(15,23,42,0.9); padding: 20px 15px; font-weight: 800; font-size: 1rem;
            color: #94a3b8; border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .premium-table td {
            padding: 18px 15px; border-bottom: 1px solid rgba(255,255,255,0.03);
            font-family: 'Inter', 'Tajawal', sans-serif; font-size: 1.05rem; font-weight: 500;
            transition: all 0.3s ease;
        }
        
        /* Table Row Animation */
        .premium-table tbody tr { opacity: 0; transform: translateX(20px); transition: all 0.3s; }
        .premium-table tbody tr.show-row { opacity: 1; transform: translateX(0); }
        
        .premium-table tbody tr:hover td { background: rgba(255,255,255,0.03); }
        .premium-table tbody tr:hover { box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05); }

        .tag-ref { background: rgba(56,189,248,0.1); color: #38bdf8; padding: 4px 10px; border-radius: 8px; font-size: 0.9rem; font-weight: 700; border: 1px solid rgba(56,189,248,0.2); }
        .val-in { color: var(--neon-green); font-weight: 800; text-shadow: 0 0 10px rgba(52,211,153,0.3); }
        .val-out { color: var(--neon-red); font-weight: 800; text-shadow: 0 0 10px rgba(244,63,94,0.3); }
        .val-bal { color: var(--neon-purple); font-weight: 900; font-size: 1.15rem; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }

        /* Loader */
        .loader {
            width: 24px; height: 24px; border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Print Styles */
        .print-only { display: none; }
        @media print {
            body { background: #fff !important; color: #000 !important; }
            .ambient-bg, .filter-panel, .header-section, .btn-print, .btn-run { display: none !important; }
            .kpi-card { border: 1px solid #ccc; background: none; box-shadow: none; break-inside: avoid; }
            .kpi-title, .kpi-value { color: #000 !important; text-shadow: none; }
            .kpi-card::before { display: none; }
            .table-container { box-shadow: none; border: none; }
            .premium-table th { background: #f0f0f0 !important; color: #000 !important; border: 1px solid #ddd; }
            .premium-table td { border: 1px solid #ddd; color: #000 !important; text-shadow: none; }
            .tag-ref { background: none; color: #000; border: 1px solid #000; }
            .print-only { display: block; text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 20px; }
            .page-wrapper { padding: 0; }
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

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="page-wrapper">
        
        <!-- Premium Header -->
        <div class="header-section">
            <div style="display:flex; align-items:center; gap:20px;">
                <div class="header-icon-box">🏦</div>
                <div>
                    <h1 style="margin:0 0 5px 0; font-weight:900; color:#fff; font-size:2.2rem; letter-spacing:-0.5px;">دفتر أستاذ البنوك والخزن</h1>
                    <span style="color:#94a3b8; font-size:1.1rem; font-weight:500;">مراقبة تفصيلية لحركة النقدية والأرصدة التراكمية بذكاء</span>
                </div>
            </div>
            <button class="btn-print" onclick="window.location.href='financial-reports.php'">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                رجوع للتقارير
            </button>
        </div>

        <div class="print-only">
            <h1 style="margin:0 0 10px 0;">كشف حساب الخزن والبنوك</h1>
            <p id="print-subtitle" style="font-size:1.2rem; color:#555;"></p>
        </div>

        <!-- Filter Panel -->
        <div class="glass-panel filter-panel">
            <div class="filter-grid">
                <div class="input-group">
                    <label>اختر الخزنة / البنك</label>
                    <select class="glass-input" id="account-select">
                        <optgroup label="الخزن (الصناديق)">
                            <option value="safe-1">الخزينة الرئيسية (المركز الرئيسي)</option>
                            <option value="safe-2">صندوق المصروفات النثرية</option>
                            <option value="safe-3">خزينة المعرض - الفرع الأول</option>
                        </optgroup>
                        <optgroup label="البنوك">
                            <option value="bank-1">بنك الخرطوم - حساب جاري (SDG)</option>
                            <option value="bank-2">بنك فيصل الإسلامي - حساب استثماري</option>
                            <option value="bank-3">بنك أمدرمان الوطني</option>
                        </optgroup>
                    </select>
                </div>
                <div class="input-group">
                    <label>الفترة (من تاريخ)</label>
                    <input type="date" class="glass-input" id="date-from">
                </div>
                <div class="input-group">
                    <label>الفترة (إلى تاريخ)</label>
                    <input type="date" class="glass-input" id="date-to">
                </div>
            </div>
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 25px;">
                <div style="display:flex; gap: 15px;">
                    <button class="btn-run" id="btnRun" onclick="generateReport()">
                        <span id="run-icon">✨</span>
                        <span id="run-text">استخراج الكشف الذكي</span>
                        <div class="loader" id="run-loader"></div>
                    </button>
                    <button class="btn-print" onclick="window.print()">
                        🖨️ طباعة السجل
                    </button>
                </div>
                <span style="color:#64748b; font-size:0.9rem; display:flex; align-items:center; gap:5px;">
                    <span style="display:inline-block; width:8px; height:8px; background:#10b981; border-radius:50%; box-shadow:0 0 10px #10b981;"></span> مزامنة حية
                </span>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-wrapper">
            <div class="kpi-card kpi-blue">
                <span class="kpi-icon">📊</span>
                <span class="kpi-title">رصيد افتتاحي (سابق)</span>
                <div class="kpi-value" style="color:var(--neon-blue);" id="kpi-opening">0.00</div>
            </div>
            <div class="kpi-card kpi-green">
                <span class="kpi-icon">📥</span>
                <span class="kpi-title">إجمالي الوارد (مقبوضات)</span>
                <div class="kpi-value" style="color:var(--neon-green);" id="kpi-in">0.00</div>
            </div>
            <div class="kpi-card kpi-red">
                <span class="kpi-icon">📤</span>
                <span class="kpi-title">إجمالي المنصرف (مدفوعات)</span>
                <div class="kpi-value" style="color:var(--neon-red);" id="kpi-out">0.00</div>
            </div>
            <div class="kpi-card kpi-purple">
                <span class="kpi-icon">💰</span>
                <span class="kpi-title">الرصيد النهائي (الحالي)</span>
                <div class="kpi-value" style="color:var(--neon-purple);" id="kpi-balance">0.00</div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="table-container">
            <div style="overflow-x: auto;">
                <table class="premium-table" id="ledgerTable">
                    <thead>
                        <tr>
                            <th style="width: 12%;">التاريخ</th>
                            <th style="width: 15%;">المرجع المستندي</th>
                            <th style="width: 33%;">البيان والتفاصيل</th>
                            <th style="width: 12%;">وارد (مدين)</th>
                            <th style="width: 12%;">منصرف (دائن)</th>
                            <th style="width: 16%;">الرصيد التراكمي</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-body">
                        <tr><td colspan="6" style="text-align:center; padding:50px; color:#64748b; font-size:1.1rem;">
                            <div style="font-size:3rem; margin-bottom:15px; opacity:0.5;">🔍</div>
                            يرجى تحديد الحساب واختيار الفترة، ثم الضغط على "استخراج الكشف" لعرض السجلات.
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>

        // Set Default Dates to First Day of current month and Today
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        document.getElementById('date-from').valueAsDate = firstDay;
        document.getElementById('date-to').valueAsDate = today;

        // Load Accounts (Banks/Safes) dynamically
        document.addEventListener('DOMContentLoaded', () => {
            const banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
            const select = document.getElementById('account-select');
            select.innerHTML = '<option value="">اختر الحساب...</option>';
            banks.forEach(b => {
                select.innerHTML += `<option value="${b.id}">${b.name} ${b.type === 'safe' ? '(خزينة)' : '(بنك)'}</option>`;
            });
            if(banks.length > 0) select.value = banks[0].id;
        });

        function parseNum(n) { return parseFloat(String(n || '0').replace(/,/g, '')) || 0; }

        // Animated Number Counter
        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const currentVal = Math.floor(progress * (end - start) + start);
                obj.innerHTML = currentVal.toLocaleString();
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    obj.innerHTML = end.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            };
            window.requestAnimationFrame(step);
        }

        function generateReport() {
            const accountId = parseInt(document.getElementById('account-select').value);
            if (!accountId) return alert('الرجاء اختيار حساب!');
            
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;
            
            if (!dateFrom || !dateTo) return alert('الرجاء تحديد فترة التاريخ!');

            const btnText = document.getElementById('run-text');
            const btnIcon = document.getElementById('run-icon');
            const loader = document.getElementById('run-loader');
            const tbody = document.getElementById('ledger-body');
            const kpiCards = document.querySelectorAll('.kpi-card');
            
            btnText.innerText = 'جاري المعالجة...';
            btnIcon.style.display = 'none';
            loader.style.display = 'block';
            kpiCards.forEach(card => card.classList.remove('show'));

            const accSelect = document.getElementById('account-select');
            const accName = accSelect.options[accSelect.selectedIndex].text;
            document.getElementById('print-subtitle').innerText = 'الحساب: ' + accName + ' | الفترة من: ' + dateFrom + ' إلى: ' + dateTo;

            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:40px; color:#38bdf8;">جاري جلب السجلات وتدقيق الأرصدة... ⏳</td></tr>';

            setTimeout(() => {
                // Fetch Data from Local Storage
                let txns = [];
                
                // Single Receipts (Cash In)
                const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                receipts.forEach(r => {
                    if (parseInt(r.toAccount) === accountId && r.toType === 'bank') {
                        txns.push({
                            date: r.date,
                            ref: 'س.ق-' + r.id,
                            note: r.statement || 'مقبوضات',
                            in: parseNum(r.amountLocal),
                            out: 0
                        });
                    }
                });

                // Single Payments (Cash Out)
                const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
                payments.forEach(p => {
                    const fromAcc = p.fromAccount || '';
                    if (p.paymentMethod === 'mobile' && p.bankTransfers && p.bankTransfers.length > 0) {
                        p.bankTransfers.forEach(bt => {
                            if (parseInt(fromAcc) === accountId || String(fromAcc).replace(/^(safe|bank)-/, '') == accountId) {
                                txns.push({
                                    date: p.date,
                                    ref: 'س.ص-' + p.id,
                                    note: bt.statement || p.statement || 'مدفوعات تحويل',
                                    in: 0,
                                    out: parseNum(bt.amount)
                                });
                            }
                        });
                    } else if (p.paymentMethod === 'check' && p.cheques && p.cheques.length > 0) {
                        p.cheques.forEach(ch => {
                            if (parseInt(fromAcc) === accountId || String(fromAcc).replace(/^(safe|bank)-/, '') == accountId) {
                                txns.push({
                                    date: p.date,
                                    ref: 'س.ص-' + p.id,
                                    note: ch.statement || p.statement || 'مدفوعات شيك',
                                    in: 0,
                                    out: parseNum(ch.amount)
                                });
                            }
                        });
                    } else {
                        if (parseInt(fromAcc) === accountId) {
                            txns.push({
                                date: p.date,
                                ref: 'س.ص-' + p.id,
                                note: p.statement || 'مدفوعات',
                                in: 0,
                                out: parseNum(p.amountLocal)
                            });
                        }
                    }
                });

                // Multiple Receipts (Cash In)
                const multiReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
                multiReceipts.forEach(mr => {
                    const mainAcc = mr.mainAccount || mr.toAccount || '';
                    if (mr.items && Array.isArray(mr.items)) {
                        mr.items.forEach(item => {
                            const pms = item.paymentMethods || [item.paymentMethod || 'cash'];
                            
                            // Cash
                            if (pms.includes('cash') && parseNum(item.cashAmount) > 0) {
                                const safeAcct = item.cashSafe || mainAcc;
                                if (parseInt(safeAcct) === accountId || String(safeAcct).replace(/^(safe|bank)-/, '') == accountId) {
                                    txns.push({
                                        date: mr.date,
                                        ref: 'س.ق.م-' + mr.id,
                                        note: item.cashStatement || item.statement || mr.description || 'مقبوضات نقداً مجمعة',
                                        in: parseNum(item.cashAmount),
                                        out: 0
                                    });
                                }
                            }
                            
                            // Bank transfers
                            if (pms.includes('bank') && parseNum(item.bankAmount) > 0) {
                                const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: item.bankAmount, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                                transfers.forEach(bt => {
                                    const bankAcct = bt.bankName || mainAcc;
                                    if (parseInt(bankAcct) === accountId || String(bankAcct).replace(/^(safe|bank)-/, '') == accountId) {
                                        txns.push({
                                            date: mr.date,
                                            ref: 'س.ق.م-' + mr.id,
                                            note: bt.statement || item.statement || mr.description || 'مقبوضات تحويل مجمعة',
                                            in: parseNum(bt.amount),
                                            out: 0
                                        });
                                    }
                                });
                            }
                            
                            // Cheques
                            if (pms.includes('cheque') && parseNum(item.chequeAmount) > 0) {
                                const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: item.chequeAmount, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, statement: item.chequeStatement || '' }];
                                cheques.forEach(ch => {
                                    const chequeAcct = ch.chequeBank || mainAcc;
                                    if (parseInt(chequeAcct) === accountId || String(chequeAcct).replace(/^(safe|bank)-/, '') == accountId) {
                                        txns.push({
                                            date: mr.date,
                                            ref: 'س.ق.م-' + mr.id,
                                            note: ch.statement || item.statement || mr.description || 'مقبوضات شيك مجمعة',
                                            in: parseNum(ch.amount),
                                            out: 0
                                        });
                                    }
                                });
                            }
                        });
                    } else if (parseInt(mainAcc) === accountId) {
                        txns.push({
                            date: mr.date,
                            ref: 'س.ق.م-' + mr.id,
                            note: mr.description || 'مقبوضات مجمعة',
                            in: parseNum(mr.totalAmount),
                            out: 0
                        });
                    }
                });

                // Multiple Payments (Cash Out)
                const multiPayments = JSON.parse(localStorage.getItem('erp_payments_v1')) || [];
                multiPayments.forEach(mp => {
                    const mainAcc = mp.mainAccount || mp.fromAccount || '';
                    if (mp.items && Array.isArray(mp.items)) {
                        mp.items.forEach(item => {
                            const pms = item.paymentMethods || [item.paymentMethod || 'cash'];
                            
                            // Cash
                            if (pms.includes('cash') && parseNum(item.cashAmount) > 0) {
                                const safeAcct = item.cashSafe || mainAcc;
                                if (parseInt(safeAcct) === accountId || String(safeAcct).replace(/^(safe|bank)-/, '') == accountId) {
                                    txns.push({
                                        date: mp.date,
                                        ref: 'س.ص.م-' + mp.id,
                                        note: item.cashStatement || item.statement || mp.description || 'مدفوعات نقداً مجمعة',
                                        in: 0,
                                        out: parseNum(item.cashAmount)
                                    });
                                }
                            }
                            
                            // Bank transfers
                            if (pms.includes('bank') && parseNum(item.bankAmount) > 0) {
                                const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: item.bankAmount, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                                transfers.forEach(bt => {
                                    const bankAcct = bt.bankName || mainAcc;
                                    if (parseInt(bankAcct) === accountId || String(bankAcct).replace(/^(safe|bank)-/, '') == accountId) {
                                        txns.push({
                                            date: mp.date,
                                            ref: 'س.ص.م-' + mp.id,
                                            note: bt.statement || item.statement || mp.description || 'مدفوعات تحويل مجمعة',
                                            in: 0,
                                            out: parseNum(bt.amount)
                                        });
                                    }
                                });
                            }
                            
                            // Cheques
                            if (pms.includes('cheque') && parseNum(item.chequeAmount) > 0) {
                                const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: item.chequeAmount, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, statement: item.chequeStatement || '' }];
                                cheques.forEach(ch => {
                                    const chequeAcct = ch.chequeBank || mainAcc;
                                    if (parseInt(chequeAcct) === accountId || String(chequeAcct).replace(/^(safe|bank)-/, '') == accountId) {
                                        txns.push({
                                            date: mp.date,
                                            ref: 'س.ص.م-' + mp.id,
                                            note: ch.statement || item.statement || mp.description || 'مدفوعات شيك مجمعة',
                                            in: 0,
                                            out: parseNum(ch.amount)
                                        });
                                    }
                                });
                            }
                        });
                    } else if (parseInt(mainAcc) === accountId) {
                        txns.push({
                            date: mp.date,
                            ref: 'س.ص.م-' + mp.id,
                            note: mp.description || 'مدفوعات مجمعة',
                            in: 0,
                            out: parseNum(mp.totalAmount)
                        });
                    }
                });

                // Journal Vouchers (can be both)
                const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
                jvs.forEach(v => {
                    if (!v.lines) return;
                    v.lines.forEach(line => {
                        if (parseInt(line.account) === accountId && line.type === 'bank') {
                            let deb = parseNum(line.debit);
                            let cred = parseNum(line.credit);
                            // In a Bank/Safe ledger: Debit = Cash In, Credit = Cash Out
                            txns.push({
                                date: v.date,
                                ref: 'ق.ي-' + v.id,
                                note: v.statement || line.note || 'تسوية يومية',
                                in: deb,
                                out: cred
                            });
                        }
                    });
                });

                // Calculate Opening Balance (all txns BEFORE dateFrom)
                let openingBal = 0;
                let filteredTxns = [];
                
                // Get bank opening balance from definition if available
                const banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                const b = banks.find(x => x.id == accountId);
                if (b && b.openingBalance) {
                    openingBal += parseNum(b.openingBalance);
                }

                txns.forEach(t => {
                    if (t.date < dateFrom) {
                        openingBal += (t.in - t.out);
                    } else if (t.date <= dateTo) {
                        filteredTxns.push(t);
                    }
                });

                filteredTxns.sort((a, b) => new Date(a.date) - new Date(b.date));

                let totalIn = 0, totalOut = 0, currentBal = openingBal;
                tbody.innerHTML = '';

                // Insert Opening Balance Row
                let openingTr = document.createElement('tr');
                openingTr.style.transitionDelay = '0s';
                openingTr.style.background = 'rgba(56,189,248,0.05)';
                openingTr.innerHTML = `
                    <td>${dateFrom}</td>
                    <td><span class="tag-ref" style="background:rgba(255,255,255,0.1); color:#fff; border-color:rgba(255,255,255,0.2);">رصيد افتتاحي</span></td>
                    <td style="color:#94a3b8; font-weight:600;">رصيد مرحل من الفترة السابقة</td>
                    <td>—</td>
                    <td>—</td>
                    <td class="val-bal">${currentBal.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                `;
                tbody.appendChild(openingTr);
                setTimeout(() => { openingTr.classList.add('show-row'); }, 50);

                // Build Transaction Rows
                filteredTxns.forEach((row, index) => {
                    let tr = document.createElement('tr');
                    tr.style.transitionDelay = `${(index + 1) * 0.05}s`;
                    
                    currentBal = currentBal + row.in - row.out;
                    totalIn += row.in; totalOut += row.out;
                    
                    tr.innerHTML = `
                        <td><span style="color:#cbd5e1;">${row.date}</span></td>
                        <td><span class="tag-ref">${row.ref}</span></td>
                        <td style="color:#e2e8f0;">${row.note}</td>
                        <td class="val-in">${row.in > 0 ? '+' + row.in.toLocaleString(undefined, {minimumFractionDigits:2}) : '—'}</td>
                        <td class="val-out">${row.out > 0 ? '-' + row.out.toLocaleString(undefined, {minimumFractionDigits:2}) : '—'}</td>
                        <td class="val-bal">${currentBal.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                    `;
                    
                    tbody.appendChild(tr);
                    setTimeout(() => { tr.classList.add('show-row'); }, 50 + ((index + 1) * 50));
                });

                if(filteredTxns.length === 0) {
                    tbody.innerHTML += '<tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">لا توجد حركات خلال هذه الفترة.</td></tr>';
                }

                btnText.innerText = 'استخراج الكشف الذكي';
                btnIcon.style.display = 'inline';
                loader.style.display = 'none';

                kpiCards.forEach((card, index) => {
                    setTimeout(() => { card.classList.add('show'); }, index * 100);
                });

                animateValue(document.getElementById('kpi-opening'), 0, openingBal, 1000);
                animateValue(document.getElementById('kpi-in'), 0, totalIn, 1200);
                animateValue(document.getElementById('kpi-out'), 0, totalOut, 1400);
                animateValue(document.getElementById('kpi-balance'), 0, currentBal, 1800);

            }, 800);
        }

    </script>

<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
