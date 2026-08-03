<?php $page_title = 'قائمة الأرباح والخسائر'; ?>
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
    <title>قائمة الأرباح والخسائر | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Tajawal', sans-serif; }
        
        .report-container { max-width: 1600px; margin: 0 auto; padding: 20px 30px; }
        
        .header-panel {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.8), rgba(6, 78, 59, 0.9));
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 25px; margin-bottom: 25px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative; overflow: hidden;
        }

        .header-panel::after {
            content: '💰'; position: absolute; left: -20px; top: -40px; font-size: 15rem; opacity: 0.05; pointer-events: none;
        }

        .header-title { font-size: 1.8rem; font-weight: 900; color: #fff; margin: 0; display: flex; align-items: center; gap: 10px; z-index: 1; }
        .header-title span { background: rgba(255,255,255, 0.1); padding: 10px; border-radius: 12px; }

        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .s-card { background: rgba(30,41,59,0.6); border-radius: 16px; padding: 25px; border: 1px solid rgba(255,255,255,0.05); text-align: center; backdrop-filter: blur(10px); position: relative; overflow: hidden; }
        .s-card h4 { margin: 0 0 10px 0; color: #94a3b8; font-size: 1.1rem; font-weight: 700; }
        .s-card .val { font-size: 2.2rem; font-weight: 900; font-family: 'Outfit'; }
        
        .val-revenue { color: #34d399; text-shadow: 0 0 15px rgba(52,211,153,0.4); }
        .val-expense { color: #f87171; text-shadow: 0 0 15px rgba(248,113,113,0.4); }
        .val-profit { color: #38bdf8; text-shadow: 0 0 15px rgba(56,189,248,0.4); }
        .val-loss { color: #ef4444; text-shadow: 0 0 15px rgba(239,68,68,0.6); }

        .data-panel {
            background: rgba(18, 15, 25, 0.65); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 25px; backdrop-filter: blur(20px); margin-bottom: 30px;
        }

        .section-title { font-size: 1.4rem; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-top: 0; margin-bottom: 20px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th { background: rgba(0,0,0,0.3); color: #cbd5e1; padding: 12px 15px; text-align: right; font-weight: 800; border-bottom: 2px solid rgba(255,255,255,0.1); }
        td { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #fff; font-weight: 600; font-family: 'Outfit', 'Tajawal'; }
        .td-amount { text-align: left; font-family: 'Outfit'; font-size: 1.1rem; }
        tr:hover td { background: rgba(255,255,255,0.05); }

        .row-subtotal { background: rgba(255,255,255,0.05) !important; border-top: 1px dashed rgba(255,255,255,0.2); }
        .row-subtotal td { font-size: 1.1rem; font-weight: 800; }
        
        .row-grand-total { background: rgba(16, 185, 129, 0.1) !important; border-top: 2px solid #10b981; border-bottom: 2px solid #10b981; }
        .row-grand-total.loss { background: rgba(239, 68, 68, 0.1) !important; border-color: #ef4444; }
        .row-grand-total td { font-size: 1.5rem; font-weight: 900; color: #10b981; }
        .row-grand-total.loss td { color: #ef4444; }

        .btn-action { background: #10b981; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Tajawal'; font-weight: bold; transition: 0.2s; text-decoration: none; display: inline-block; z-index: 2; position: relative;}
        .btn-action:hover { background: #059669; transform: translateY(-2px); }

        .chart-container { height: 300px; width: 100%; display: flex; justify-content: center; }

    
        
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
            <h1 class="header-title"><span>📊</span> قائمة الأرباح والخسائر (P&L)</h1>
            <div>
                <button class="btn-action btn-print" onclick="previewReport()">👁️ معاينة</button>
            <button class="btn-action btn-print" onclick="window.print()">🖨️ طباعة</button>
            <button class="btn-action btn-print" onclick="window.openExportModal(null, {element: document.getElementById('report-table-wrap'), filename: 'pl-statement.pdf', orientation: 'landscape'})">📥 تصدير PDF</button>
            <button class="btn-action" onclick="runAIPL()" style="background: linear-gradient(135deg, #c084fc, #9333ea); color: white; border: none; box-shadow: 0 4px 15px rgba(147, 51, 234, 0.4);">🤖 تحليل ذكي</button>
    
            </div>
        </div>

        <div class="summary-cards">
            <div class="s-card" style="border-bottom: 4px solid #34d399;">
                <h4>إجمالي الإيرادات (Revenue)</h4>
                <div class="val val-revenue" id="kpi-revenue">0.00</div>
            </div>
            <div class="s-card" style="border-bottom: 4px solid #f87171;">
                <h4>إجمالي المصروفات (Expenses)</h4>
                <div class="val val-expense" id="kpi-expense">0.00</div>
            </div>
            <div class="s-card" id="card-net" style="border-bottom: 4px solid #38bdf8;">
                <h4 id="kpi-net-title">صافي الربح (Net Profit)</h4>
                <div class="val val-profit" id="kpi-net">0.00</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="data-panel">
                <h3 class="section-title">التفاصيل المالية للفترة</h3>
                
                <table id="plTable">
                    <!-- Injected via JS -->
                </table>

            </div>

            <div class="data-panel" style="display: flex; flex-direction: column;">
                <h3 class="section-title">المؤشر العام للربحية</h3>
                <div class="chart-container">
                    <canvas id="plChart"></canvas>
                </div>
                <div style="margin-top: auto; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 12px; border: 1px dashed rgba(255,255,255,0.1); font-size: 0.9rem; color: #cbd5e1; line-height: 1.6;">
                    💡 <strong>ملاحظة:</strong> يتم حساب الأرباح والخسائر بناءً على القيود اليومية المرحلة لدفتر الأستاذ العام وتصنيف الحسابات التلقائي (إيرادات، تكلفة مبيعات، مصروفات).
                </div>
            </div>
        </div>

    </div>

    <!-- Required for DBReady and IndexedDB -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260803.01245"></script>
    <script>
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) { window.location.href = 'login.php'; return; }
            generatePLStatement();
        });

        function generatePLStatement() {
            const glEntries = (JSON.parse(localStorage.getItem('erp_gl_v1')) || []).filter(x => !x.deleted);
            
            const revenueAccounts = {};
            const cogsAccounts = {}; // Cost of Goods Sold
            const expenseAccounts = {};

            // Categorize based on Keywords
            glEntries.forEach(entry => {
                const acc = entry.account || 'غير محدد';
                const name = acc.toLowerCase();
                const amount = Number(entry.amount) || 0;
                
                // Determine nature of entry
                const isDebit = entry.type === 'debit' || entry.type === 'in' || entry.type === 'مدين';
                const isCredit = entry.type === 'credit' || entry.type === 'out' || entry.type === 'دائن';
                
                // Signed Amount: Revenue increases with Credit, Expenses increase with Debit
                
                if (name.includes('مبيعات') || name.includes('إيراد') || name.includes('ايراد')) {
                    if (!revenueAccounts[acc]) revenueAccounts[acc] = 0;
                    revenueAccounts[acc] += isCredit ? amount : -amount;
                } 
                else if (name.includes('مشتريات') || name.includes('تكلفة')) {
                    if (!cogsAccounts[acc]) cogsAccounts[acc] = 0;
                    cogsAccounts[acc] += isDebit ? amount : -amount;
                }
                else if (name.includes('مصروف') || name.includes('راتب') || name.includes('رواتب') || name.includes('أجور') || name.includes('كهرباء') || name.includes('ايجار') || name.includes('إيجار') || name.includes('نثريات')) {
                    if (!expenseAccounts[acc]) expenseAccounts[acc] = 0;
                    expenseAccounts[acc] += isDebit ? amount : -amount;
                }
                // Balance sheet accounts (Cash, Bank, AP, AR, Inventory) are ignored in P&L
            });

            // Sums
            const sumRevenue = Object.values(revenueAccounts).reduce((a,b) => a+b, 0);
            const sumCOGS = Object.values(cogsAccounts).reduce((a,b) => a+b, 0);
            const sumExpenses = Object.values(expenseAccounts).reduce((a,b) => a+b, 0);

            const grossProfit = sumRevenue - sumCOGS;
            const netProfit = grossProfit - sumExpenses;

            // Render Table
            const table = document.getElementById('plTable');
            let html = '';

            // 1. Revenues
            html += `<tr><th colspan="2" style="text-align:right; color:#34d399; font-size:1.2rem;">الإيرادات (Revenues)</th></tr>`;
            if (Object.keys(revenueAccounts).length === 0) {
                html += `<tr><td style="color:#64748b;">لا توجد إيرادات مسجلة</td><td class="td-amount">0.00</td></tr>`;
            } else {
                for (let [acc, val] of Object.entries(revenueAccounts)) {
                    if (val === 0) continue;
                    html += `<tr><td>${acc}</td><td class="td-amount">${val.toLocaleString(undefined, {minimumFractionDigits:2})}</td></tr>`;
                }
            }
            html += `<tr class="row-subtotal"><td style="color:#34d399;">إجمالي الإيرادات</td><td class="td-amount" style="color:#34d399;">${sumRevenue.toLocaleString(undefined, {minimumFractionDigits:2})}</td></tr>`;

            // 2. COGS
            html += `<tr><th colspan="2" style="text-align:right; color:#fbbf24; font-size:1.2rem; padding-top:20px;">تكلفة المبيعات (Cost of Goods Sold)</th></tr>`;
            if (Object.keys(cogsAccounts).length === 0) {
                html += `<tr><td style="color:#64748b;">لا توجد تكاليف مسجلة</td><td class="td-amount">0.00</td></tr>`;
            } else {
                for (let [acc, val] of Object.entries(cogsAccounts)) {
                    if (val === 0) continue;
                    html += `<tr><td>${acc}</td><td class="td-amount">${val.toLocaleString(undefined, {minimumFractionDigits:2})}</td></tr>`;
                }
            }
            html += `<tr class="row-subtotal"><td style="color:#fbbf24;">إجمالي تكلفة المبيعات</td><td class="td-amount" style="color:#fbbf24;">${sumCOGS.toLocaleString(undefined, {minimumFractionDigits:2})}</td></tr>`;

            // 3. Gross Profit
            html += `<tr style="background: rgba(255,255,255,0.08); border-top: 2px solid rgba(255,255,255,0.3);"><td style="font-size:1.2rem; font-weight:800; color:#38bdf8;">مجمل الربح (Gross Profit)</td><td class="td-amount" style="font-size:1.2rem; font-weight:900; color:#38bdf8;">${grossProfit.toLocaleString(undefined, {minimumFractionDigits:2})}</td></tr>`;

            // 4. Expenses
            html += `<tr><th colspan="2" style="text-align:right; color:#f87171; font-size:1.2rem; padding-top:20px;">مصروفات التشغيل (Operating Expenses)</th></tr>`;
            if (Object.keys(expenseAccounts).length === 0) {
                html += `<tr><td style="color:#64748b;">لا توجد مصروفات مسجلة</td><td class="td-amount">0.00</td></tr>`;
            } else {
                for (let [acc, val] of Object.entries(expenseAccounts)) {
                    if (val === 0) continue;
                    html += `<tr><td>${acc}</td><td class="td-amount">${val.toLocaleString(undefined, {minimumFractionDigits:2})}</td></tr>`;
                }
            }
            html += `<tr class="row-subtotal"><td style="color:#f87171;">إجمالي المصروفات</td><td class="td-amount" style="color:#f87171;">${sumExpenses.toLocaleString(undefined, {minimumFractionDigits:2})}</td></tr>`;

            // 5. Net Profit
            const isLoss = netProfit < 0;
            html += `<tr class="row-grand-total ${isLoss ? 'loss' : ''}">
                        <td>صافي ${isLoss ? 'الخسارة' : 'الربح'} (Net ${isLoss ? 'Loss' : 'Profit'})</td>
                        <td class="td-amount">${Math.abs(netProfit).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                     </tr>`;

            table.innerHTML = html;

            // Update KPIs
            document.getElementById('kpi-revenue').innerText = sumRevenue.toLocaleString(undefined, {minimumFractionDigits:2});
            document.getElementById('kpi-expense').innerText = (sumCOGS + sumExpenses).toLocaleString(undefined, {minimumFractionDigits:2});
            
            const netCard = document.getElementById('card-net');
            const netVal = document.getElementById('kpi-net');
            const netTitle = document.getElementById('kpi-net-title');
            
            if (isLoss) {
                netCard.style.borderBottomColor = '#ef4444';
                netTitle.innerText = 'صافي الخسارة (Net Loss)';
                netTitle.style.color = '#f87171';
                netVal.className = 'val val-loss';
            } else {
                netCard.style.borderBottomColor = '#38bdf8';
                netTitle.innerText = 'صافي الربح (Net Profit)';
                netTitle.style.color = '#38bdf8';
                netVal.className = 'val val-profit';
            }
            netVal.innerText = Math.abs(netProfit).toLocaleString(undefined, {minimumFractionDigits:2});

            // Render Chart
            renderChart(sumRevenue, sumCOGS, sumExpenses, netProfit);
        }

        function renderChart(rev, cogs, exp, net) {
            const ctx = document.getElementById('plChart').getContext('2d');
            Chart.defaults.color = '#cbd5e1';
            Chart.defaults.font.family = "'Tajawal', sans-serif";

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['تكلفة المبيعات', 'المصروفات', net >= 0 ? 'صافي الربح' : 'صافي الخسارة'],
                    datasets: [{
                        data: [cogs, exp, Math.abs(net)],
                        backgroundColor: [
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(248, 113, 113, 0.8)',
                            net >= 0 ? 'rgba(56, 189, 248, 0.9)' : 'rgba(153, 27, 27, 0.9)'
                        ],
                        borderWidth: 2,
                        borderColor: '#1e293b'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, font: {size: 13} } }
                    }
                }
            });
        }
    </script>

        <!-- AI P&L Modal -->
        <div id="aiPL-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; direction: rtl;">
            <div class="glass-panel" style="width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto; padding: 25px; position: relative; border: 1px solid rgba(147, 51, 234, 0.3);">
                <button onclick="document.getElementById('aiPL-modal').style.display='none'" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #f43f5e; font-size: 1.5rem; cursor: pointer;">&times;</button>
                <h3 style="color: #c084fc; margin-bottom: 20px; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                    <span style="font-size: 1.5rem;">🤖</span> التحليل المالي للأرباح والخسائر
                </h3>
                <div id="aiPL-content" style="color: #e2e8f0; line-height: 1.8; font-size: 1.05rem;">
                    جاري قراءة تقرير الأرباح وتحليله... يرجى الانتظار ⏳
                </div>
            </div>
        </div>

    <script>
    function runAIPL() {
        document.getElementById('aiPL-modal').style.display = 'flex';
        document.getElementById('aiPL-content').innerHTML = 'جاري قراءة التقرير وتحليله... يرجى الانتظار ⏳';
        
        // Extract data from the table
        let reportData = [];
        let rows = document.querySelectorAll('#pl-tbody tr');
        rows.forEach(r => {
            let cols = r.querySelectorAll('td');
            if(cols.length >= 2) {
                let name = cols[0].innerText.trim();
                let amount = cols[1].innerText.trim();
                if(name && amount) {
                    reportData.push(name + ": " + amount);
                }
            }
        });

        let totalProfit = document.getElementById('net-profit-val') ? document.getElementById('net-profit-val').innerText : '';

        let context = "بيانات قائمة الدخل:\n" + reportData.join("\n") + "\nالنتيجة النهائية: " + totalProfit;

        let promptMsg = "أنت محلل مالي خبير. اقرأ بيانات قائمة الدخل (الأرباح والخسائر) التالية. اكتب تقريراً تحليلياً سردياً للإدارة (3-4 فقرات) تقيم فيه كفاءة التشغيل، وتوضح أكبر بنود المصروفات التي تلتهم الإيرادات، وتقدم توصية لزيادة الربحية.";

        fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: promptMsg, context: context })
        })
        .then(res => res.json())
        .then(data => {
            if (data.reply) {
                document.getElementById('aiPL-content').innerHTML = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong style="color:#c084fc">$1</strong>').replace(/\n/g, '<br>');
            } else {
                document.getElementById('aiPL-content').innerHTML = '<span style="color:#f43f5e">حدث خطأ أثناء الاستخراج.</span>';
            }
        })
        .catch(err => {
            document.getElementById('aiPL-content').innerHTML = '<span style="color:#f43f5e">تعذر الاتصال بالخادم.</span>';
        });
    }
    </script>
    
</body>
</html>
