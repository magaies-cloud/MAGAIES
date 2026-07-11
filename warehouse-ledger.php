<?php $page_title = 'مقاييس (MAGAIEES) - كشف المخزن التحليلي'; ?>
<!-- c:\mynewapp\frontend-pwa\warehouse-ledger.html -->
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس (MAGAIEES) - كشف المخزن التحليلي</title>
    <link rel="stylesheet" href="css/theme.css?v=neon2">
    <link rel="stylesheet" href="css/components.css?v=neon2">
        <style>
        html { background: var(--bg-color); scroll-behavior: smooth; }
        body { padding: 0; min-height: 100vh; overflow-x: hidden; font-family: 'Inter', sans-serif; background: var(--bg-color); }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); }
        ::-webkit-scrollbar-thumb { background: rgba(56, 189, 248, 0.4); border-radius: 10px; }

        .ledger-container { display: flex; flex-direction: column; gap: 20px; padding: 15px; width: 100%; min-height: 100vh; box-sizing: border-box; }
        @media (min-width: 768px) { .ledger-container { padding: 25px 35px 40px 25px; gap: 25px; } }

        .neon-glass-panel { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); border-radius: 16px; border: 1px solid var(--glass-border); }

        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .input-group label { display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; }
        .input-group select, .input-group input { width: 100%; padding: 10px 15px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--glass-border); border-radius: 10px; color: #fff; font-size: 0.95rem; outline: none; }
        .input-group select:focus, .input-group input:focus { border-color: var(--primary-accent); }

        .pro-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .pro-table th { background: rgba(15, 76, 129, 0.2); color: var(--text-secondary); font-weight: 600; padding: 10px; text-align: right; border-bottom: 1px solid var(--glass-border); }
        .pro-table td { padding: 10px; color: #f8fafc; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        
        .type-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: bold; }
        .type-in { background: rgba(52, 211, 153, 0.15); color: #34d399; }
        .type-out { background: rgba(248, 113, 113, 0.15); color: #f87171; }

        .nav-tabs { display:flex; gap:15px; margin-bottom:10px; flex-wrap:wrap; }
        .nav-tab { padding:10px 20px; border-radius:10px; text-decoration:none; font-weight:700; color:#fff; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); transition:0.3s; }
        .nav-tab:hover { background:rgba(255,255,255,0.1); }
        .nav-tab.active { background:var(--primary-accent); border-color:var(--primary-accent); }
    
        
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


    <div class="ledger-container">
        <!-- Quick Nav -->
        <div class="nav-tabs">
            <a data-href="inventory-report.php" onclick="window.location.href='inventory-report.php'" class="nav-tab">جرد المخزون الفعلي</a>
            <a data-href="item-ledger.php" onclick="window.location.href='item-ledger.php'" class="nav-tab">كارت صنف (حركة منتج)</a>
            <a data-href="warehouse-ledger.php" onclick="window.location.href='warehouse-ledger.php'" class="nav-tab active">كشف حركة المخازن</a>
        </div>

        <!-- Header -->
        <?php $header_theme_class = 'header-theme-inventory'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Filters -->
        <div class="neon-glass-panel filter-row" style="padding:20px;">
            <div class="input-group" style="grid-column: span 2;">
                <label>اختر المخزن</label>
                <select id="warehouse-select" onchange="loadLedger()">
                    <option value="">-- يرجى اختيار مخزن --</option>
                </select>
            </div>
            <div class="input-group">
                <label>من تاريخ</label>
                <input type="date" id="date-from" onchange="loadLedger()">
            </div>
            <div class="input-group">
                <label>إلى تاريخ</label>
                <input type="date" id="date-to" onchange="loadLedger()">
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="neon-glass-panel" style="padding:1px; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="pro-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم المستند</th>
                            <th>المستند / البيان</th>
                            <th>الصنف</th>
                            <th style="color:#34d399;">وارد (دخول)</th>
                            <th style="color:#f87171;">منصرف (خروج)</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-tbody">
                        <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-secondary);">الرجاء اختيار مخزن وتحديد الفترة للبحث</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054970.52149"></script>
    <script>
        document.addEventListener('DBReady', () => {
            // Load Warehouses
            const whSelect = document.getElementById('warehouse-select');
            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            warehousesDb.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.id;
                opt.text = wh.name;
                whSelect.appendChild(opt);
            });

            const today = new Date();
            document.getElementById('date-to').valueAsDate = today;
        });

        function buildRealWarehouseHistory(warehouseId) {
            let history = [];
            
            // Get item names dictionary for faster lookup
            let itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            let getItemName = (id) => {
                let item = itemsDb.find(i => i.id === id);
                return item ? item.name : 'صنف غير معروف';
            };

            // 1. Get Purchases
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                let wh = inv.warehouseId || inv.warehouse;
                if (wh === warehouseId) {
                    (inv.items || inv.lines || []).forEach(line => {
                        let itemId = line.itemId || line.productId;
                        let baseQty = 0;
                        if (line.customBaseQty !== undefined) {
                            baseQty = line.customBaseQty;
                        } else {
                            let item = itemsDb.find(i => i.id === itemId);
                            let factor = line.customFactor !== undefined ? line.customFactor : 1;
                            let conv = item ? (item.conversionFactor || 1) : 1;
                            baseQty = (parseFloat(line.quantity) || 0) * factor * conv;
                        }

                        history.push({
                            date: new Date(inv.date || inv.receiveDate || Date.now()),
                            typeCode: 'in',
                            typeName: 'فاتورة مشتريات',
                            ref: inv.invoiceNo || inv.poNumber || inv.id,
                            docId: inv.invoiceNo || inv.id, // Fixed docId for drill-down
                            itemName: getItemName(itemId), // Support new schema
                            in: baseQty,
                            out: 0
                        });
                    });
                }
            });

            // 2. Get Sales
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted);
            sales.forEach(inv => {
                let hdrWh = inv.warehouse;
                
                (inv.items || []).forEach(line => {
                    let lineWh = line.warehouseId || hdrWh;
                    
                    if (lineWh === warehouseId) {
                        let itemId = line.itemId;
                        let baseQty = 0;
                        if (line.customBaseQty !== undefined) {
                            baseQty = line.customBaseQty;
                        } else {
                            let item = itemsDb.find(i => i.id === itemId);
                            let factor = line.customFactor !== undefined ? line.customFactor : 1;
                            let conv = item ? (item.conversionFactor || 1) : 1;
                            baseQty = (parseFloat(line.quantity) || 0) * factor * conv;
                        }

                        history.push({
                            date: new Date(inv.date || Date.now()),
                            typeCode: 'out',
                            typeName: 'فاتورة مبيعات',
                            ref: inv.invoiceNo || 'INV',
                            docId: inv.invoiceNo || inv.id, // Fixed docId for drill-down
                            itemName: getItemName(itemId),
                            in: 0,
                            out: baseQty
                        });
                    }
                });
            });

            return history.sort((a,b) => a.date - b.date);
        }

        function loadLedger() {
            const wh = document.getElementById('warehouse-select').value;
            const tbody = document.getElementById('ledger-tbody');
            
            if (!wh) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-secondary);">الرجاء اختيار مخزن وتحديد الفترة للبحث</td></tr>`;
                return;
            }

            let history = buildRealWarehouseHistory(wh);

            const fromDateStr = document.getElementById('date-from').value;
            const toDateStr = document.getElementById('date-to').value;
            
            if (fromDateStr) {
                let d = new Date(fromDateStr); d.setHours(0,0,0,0);
                history = history.filter(h => h.date >= d);
            }
            if (toDateStr) {
                let d = new Date(toDateStr); d.setHours(23,59,59,999);
                history = history.filter(h => h.date <= d);
            }

            tbody.innerHTML = '';

            if (history.length === 0) {
                 tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد حركات لهذا المخزن في هذه الفترة</td></tr>`;
            }

            history.forEach(row => {
                let typeBadge = row.typeCode === 'in' 
                    ? `<span class="type-badge type-in">${row.typeName}</span>`
                    : `<span class="type-badge type-out">${row.typeName}</span>`;

                const dStr = row.date.toISOString().split('T')[0];

                const tr = document.createElement('tr');
                let target = '';
                if (row.typeCode === 'out') target = `sales-invoice.php?id=${row.docId}`;
                else if (row.typeCode === 'in') target = `purchase.php?id=${row.docId}`;

                let refHtml = row.ref;
                if (row.docId && target) {
                    tr.style.cursor = 'pointer';
                    tr.title = 'عرض تفاصيل المستند الأصلي';
                    tr.onclick = () => { window.open(target, '_blank'); };
                    tr.onmouseover = () => tr.style.background = 'rgba(56, 189, 248, 0.05)';
                    tr.onmouseout = () => tr.style.background = '';
                    
                    refHtml = `
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span>${row.ref}</span>
                            <button onclick="event.stopPropagation(); window.open('${target}', '_blank')" style="background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; padding:3px 8px; cursor:pointer; font-size:0.75rem; font-weight:bold; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#38bdf8'; this.style.color='#fff';" onmouseout="this.style.background='rgba(56,189,248,0.1)'; this.style.color='#38bdf8';">
                                لم يتم العثور
                            </button>
                        </div>
                    `;
                }

                tr.innerHTML = `
                    <td>${dStr}</td>
                    <td>${typeBadge}</td>
                    <td style="font-weight:600;">${refHtml}</td>
                    <td style="color:#38bdf8; font-weight:700;">${row.itemName}</td>
                    <td style="color:#34d399; font-weight:700;">${row.in > 0 ? '+ ' + row.in : '-'}</td>
                    <td style="color:#f87171; font-weight:700;">${row.out > 0 ? '- ' + row.out : '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    </script>
</body>
</html>

