<?php $page_title = 'ميزان المراجعة'; ?>
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
    <title>ميزان المراجعة | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Tajawal', sans-serif; }
        
        .report-container { max-width: 1600px; margin: 0 auto; padding: 20px 30px; }
        
        .header-panel {
            background: linear-gradient(135deg, #1e3a8a, #312e81);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 25px; margin-bottom: 25px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative; overflow: hidden;
        }

        .header-panel::after {
            content: '⚖️'; position: absolute; left: -20px; top: -40px; font-size: 15rem; opacity: 0.05; pointer-events: none;
        }

        .header-title { font-size: 1.8rem; font-weight: 900; color: #fff; margin: 0; display: flex; align-items: center; gap: 10px; }
        .header-title span { background: rgba(255,255,255, 0.1); padding: 10px; border-radius: 12px; }

        .summary-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
        .s-card { background: rgba(30,41,59,0.6); border-radius: 16px; padding: 25px; border: 1px solid rgba(255,255,255,0.05); text-align: center; backdrop-filter: blur(10px); display: flex; flex-direction: column; justify-content: center; align-items: center;}
        .s-card h4 { margin: 0 0 10px 0; color: #94a3b8; font-size: 1.1rem; font-weight: 700; }
        .s-card .val { font-size: 2.2rem; font-weight: 900; font-family: 'Outfit'; }
        
        .val-debit { color: #38bdf8; text-shadow: 0 0 15px rgba(56,189,248,0.4); }
        .val-credit { color: #ec4899; text-shadow: 0 0 15px rgba(236,72,153,0.4); }

        .data-panel {
            background: rgba(18, 15, 25, 0.65); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 25px; backdrop-filter: blur(20px);
        }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: rgba(0,0,0,0.3); color: #cbd5e1; padding: 15px; text-align: center; font-weight: 800; border-bottom: 2px solid rgba(255,255,255,0.1); font-size: 1.1rem; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #fff; font-weight: 600; font-family: 'Outfit', 'Tajawal'; text-align: center; font-size: 1.05rem; }
        tr:hover td { background: rgba(255,255,255,0.05); }

        .th-group { background: rgba(255,255,255,0.02); }
        
        .row-total { background: rgba(30,41,59,0.8) !important; border-top: 2px solid #8b5cf6; }
        .row-total td { font-size: 1.2rem; font-weight: 900; color: #c084fc; }

        .btn-action { background: #3b82f6; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Tajawal'; font-weight: bold; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-action:hover { background: #2563eb; transform: translateY(-2px); }

        .balance-status {
            margin-top: 20px; padding: 15px; border-radius: 12px; text-align: center; font-weight: 900; font-size: 1.2rem; display: none;
        }
        .status-balanced { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; display: block; }
        .status-unbalanced { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; display: block; }

    
        
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


    <div class="report-container">
        
        <div style="margin-bottom: 20px;">
            <button onclick="history.back()" class="btn-action" style="background: rgba(255,255,255,0.1);">↩ رجوع</button>
        </div>

        <div class="premium-header">
            <h1 class="header-title"><span>⚖️</span> ميزان المراجعة (Trial Balance)</h1>
            <div>
                <button class="btn-action btn-print" onclick="previewReport()">👁️ معاينة</button>
            <button class="btn-action btn-print" onclick="window.print()">🖨️ طباعة</button>
            <button class="btn-action btn-print" onclick="window.openExportModal(null, {element: document.getElementById('report-table-wrap'), filename: 'trial-balance.pdf', orientation: 'landscape'})">📥 تصدير PDF</button>
            </div>
        </div>

        <div class="summary-cards">
            <div class="s-card" style="border-bottom: 4px solid #38bdf8;">
                <h4>إجمالي الأرصدة المدينة (Debit)</h4>
                <div class="val val-debit" id="kpi-debit">0.00</div>
            </div>
            <div class="s-card" style="border-bottom: 4px solid #ec4899;">
                <h4>إجمالي الأرصدة الدائنة (Credit)</h4>
                <div class="val val-credit" id="kpi-credit">0.00</div>
            </div>
        </div>

        <div id="balance-indicator" class="balance-status">
            <!-- Injected by JS -->
        </div>

        <div class="data-panel" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0; color:#fff; font-size: 1.4rem;">🧾 تفصيل الأرصدة حسب الحسابات</h3>
            </div>
            <div style="overflow-x: auto;">
                <table id="tbTable">
                    <thead>
                        <tr>
                            <th rowspan="2" style="text-align: right;">اسم الحساب (Account Name)</th>
                            <th colspan="2" class="th-group" style="color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.2);">الحركة (Transactions)</th>
                            <th colspan="2" class="th-group" style="color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.2);">الرصيد (Balance)</th>
                        </tr>
                        <tr>
                            <th style="color: #38bdf8;">مدين (Dr)</th>
                            <th style="color: #ec4899;">دائن (Cr)</th>
                            <th style="color: #38bdf8;">مدين (Dr)</th>
                            <th style="color: #ec4899;">دائن (Cr)</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Data injected via JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) { window.location.href = 'login.php'; return; }
            generateTrialBalance();
        });

        function generateTrialBalance() {
            // Fetch General Ledger Entries
            const glEntries = (JSON.parse(localStorage.getItem('erp_gl_v1')) || []).filter(x => !x.deleted);

            const accounts = {};

            // Aggregate Data
            glEntries.forEach(entry => {
                const acc = entry.account || 'حساب غير معروف';
                if (!accounts[acc]) {
                    accounts[acc] = { name: acc, debit: 0, credit: 0 };
                }
                
                if (entry.type === 'debit' || entry.type === 'in' || entry.type === 'مدين') {
                    accounts[acc].debit += Number(entry.amount) || 0;
                } else if (entry.type === 'credit' || entry.type === 'out' || entry.type === 'دائن') {
                    accounts[acc].credit += Number(entry.amount) || 0;
                }
            });

            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            let totalDebitMovement = 0;
            let totalCreditMovement = 0;
            let totalDebitBalance = 0;
            let totalCreditBalance = 0;

            const accountsArr = Object.values(accounts).sort((a,b) => a.name.localeCompare(b.name));

            accountsArr.forEach(acc => {
                totalDebitMovement += acc.debit;
                totalCreditMovement += acc.credit;

                let balDebit = 0;
                let balCredit = 0;
                
                const diff = acc.debit - acc.credit;
                if (diff > 0) {
                    balDebit = diff;
                } else if (diff < 0) {
                    balCredit = Math.abs(diff);
                }

                totalDebitBalance += balDebit;
                totalCreditBalance += balCredit;

                // Don't show accounts with absolute zero movement
                if(acc.debit === 0 && acc.credit === 0) return;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="text-align: right; color: #e2e8f0;">${acc.name}</td>
                    <td style="color: #7dd3fc;">${acc.debit > 0 ? acc.debit.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
                    <td style="color: #f472b6;">${acc.credit > 0 ? acc.credit.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
                    <td style="color: #38bdf8; font-weight: 800;">${balDebit > 0 ? balDebit.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
                    <td style="color: #ec4899; font-weight: 800;">${balCredit > 0 ? balCredit.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
                `;
                tbody.appendChild(tr);
            });

            if (accountsArr.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="padding: 30px; color: #64748b;">لا توجد حركات مسجلة في دفتر الأستاذ العام.</td></tr>';
                return;
            }

            // Render Totals Row
            const trTotal = document.createElement('tr');
            trTotal.className = 'row-total';
            trTotal.innerHTML = `
                <td style="text-align: right;">الإجمالي المتطابق</td>
                <td>${totalDebitMovement.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                <td>${totalCreditMovement.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                <td style="color: #38bdf8;">${totalDebitBalance.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                <td style="color: #ec4899;">${totalCreditBalance.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
            `;
            tbody.appendChild(trTotal);

            // Update KPIs
            document.getElementById('kpi-debit').innerText = totalDebitBalance.toLocaleString(undefined, {minimumFractionDigits:2});
            document.getElementById('kpi-credit').innerText = totalCreditBalance.toLocaleString(undefined, {minimumFractionDigits:2});

            // Balance Check
            const indicator = document.getElementById('balance-indicator');
            // Allow a small floating point difference (epsilon)
            if (Math.abs(totalDebitBalance - totalCreditBalance) < 0.01) {
                indicator.className = 'balance-status status-balanced';
                indicator.innerHTML = '✅ الميزان متطابق بشكل مثالي (Balanced)';
            } else {
                indicator.className = 'balance-status status-unbalanced';
                const diff = Math.abs(totalDebitBalance - totalCreditBalance);
                indicator.innerHTML = `❌ يوجد خلل في التطابق! الفرق: ${diff.toLocaleString(undefined, {minimumFractionDigits:2})}`;
            }
        }
    </script>

<script src="js/magaiees_back.js?v=2026_back_v1" defer></script>
</body>
</html>
