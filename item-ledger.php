<?php $page_title = 'مقاييس (MAGAIEES) - كارت الصنف (حركة الأصناف)'; ?>
<!-- c:\mynewapp\frontend-pwa\item-ledger.html -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس (MAGAIEES) - كارت الصنف (حركة الأصناف)</title>
    <link rel="stylesheet" href="css/theme.css?v=neon2">
    <link rel="stylesheet" href="css/components.css?v=neon2">
        <style>
        /* SCROLL ALLOWED */
        html { background: var(--bg-color); scroll-behavior: smooth; }
        body { padding: 0; min-height: 100vh; overflow-x: hidden; font-family: 'Inter', sans-serif; background: var(--bg-color); }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); }
        ::-webkit-scrollbar-thumb { background: rgba(56, 189, 248, 0.4); border-radius: 10px; }

        .ledger-container {
            display: flex; flex-direction: column; gap: 20px; padding: 15px 15px 30px 15px; width: 100%; min-height: 100vh; box-sizing: border-box;
        }
        @media (min-width: 768px) { .ledger-container { padding: 25px 35px 40px 25px; gap: 25px; } }

        .neon-glass-panel {
            background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); border-radius: 16px; border: 1px solid var(--glass-border); transition: transform 0.3s;
        }

        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .input-group label { display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; }
        .input-group select, .input-group input { width: 100%; padding: 10px 15px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--glass-border); border-radius: 10px; color: #fff; font-size: 0.95rem; outline: none; }
        .input-group select:focus, .input-group input:focus { border-color: var(--primary-accent); }

        .summary-card { padding: 20px; text-align: center; border: 1px solid var(--glass-border); border-radius: 12px; background: rgba(0,0,0,0.2); }
        .summary-title { color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 10px; }
        .summary-value { font-size: 1.8rem; font-weight: 900; margin: 0; }

        .pro-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .pro-table th { background: rgba(15, 76, 129, 0.2); color: var(--text-secondary); font-weight: 600; padding: 10px; text-align: right; border-bottom: 1px solid var(--glass-border); }
        .pro-table td { padding: 10px; color: #f8fafc; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        
        .type-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: bold; }
        .type-in { background: rgba(52, 211, 153, 0.15); color: #34d399; }
        .type-out { background: rgba(248, 113, 113, 0.15); color: #f87171; }
        .type-bal { background: rgba(56, 189, 248, 0.15); color: #38bdf8; }

        .nav-tabs { display:flex; gap:15px; margin-bottom:10px; flex-wrap:wrap; }
        .nav-tab { padding:10px 20px; border-radius:10px; text-decoration:none; font-weight:700; color:#fff; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); transition:0.3s; }
        .nav-tab:hover { background:rgba(255,255,255,0.1); }
        .nav-tab.active { background:var(--primary-accent); border-color:var(--primary-accent); }

        @media print {
            body { background: #fff !important; color: #000 !important; }
            .neon-glass-panel, .pro-table th, .pro-table td { background: #fff !important; color: #000 !important; border-color: #ccc !important; }
            .global-sidebar, button, .filter-row { display: none !important; }
            .inv-container { padding: 0 !important; }
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


    <div class="ledger-container">
        <!-- Quick Nav -->
        <div class="nav-tabs">
            <a data-href="inventory-report.php" onclick="window.location.href='inventory-report.php'" class="nav-tab">جرد الأرصدة العام</a>
            <a data-href="item-ledger.php" onclick="window.location.href='item-ledger.php'" class="nav-tab active">حركة صنف (كارت الصنف)</a>
            <a data-href="warehouse-ledger.php" onclick="window.location.href='warehouse-ledger.php'" class="nav-tab">حركة مخزن تفصيلية</a>
        </div>

        <!-- Header -->
        <?php $header_theme_class = 'header-theme-inventory'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Filters -->
        <div class="neon-glass-panel filter-row" style="padding:20px;">
            <div class="input-group">
                <label>اختر الصنف المراد جرده</label>
                <select id="item-select" onchange="loadLedger()">
                    <option value="">-- يرجى اختيار صنف --</option>
                </select>
            </div>
            <div class="input-group">
                <label>المخزن (اختياري)</label>
                <select id="warehouse-select" onchange="loadLedger()">
                    <option value="ALL">جميع المخازن</option>
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

        <!-- KPI Summary for Item -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px;">
            <div class="summary-card" style="border-left: 4px solid #38bdf8;">
                <div class="summary-title">الرصيد الافتتاحي (أول المدة)</div>
                <h3 class="summary-value" id="sum-open">0</h3>
            </div>
            <div class="summary-card" style="border-left: 4px solid #34d399;">
                <div class="summary-title">إجمالي الوارد (مشتريات)</div>
                <h3 class="summary-value" id="sum-in" style="color:#34d399;">0</h3>
            </div>
            <div class="summary-card" style="border-left: 4px solid #f87171;">
                <div class="summary-title">إجمالي المنصرف (مبيعات)</div>
                <h3 class="summary-value" id="sum-out" style="color:#f87171;">0</h3>
            </div>
            <div class="summary-card" style="border-left: 4px solid #fbbf24;">
                <div class="summary-title">الرصيد الفعلي الحالي</div>
                <h3 class="summary-value" id="sum-balance" style="color:#fbbf24;">0</h3>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="neon-glass-panel" style="padding:1px; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="pro-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>نوع الحركة</th>
                            <th>المرجع / المستند</th>
                            <th style="color:#34d399; background:rgba(52,211,153,0.05);">الوارد (داخل)</th>
                            <th style="color:#f87171; background:rgba(248,113,113,0.05);">المنصرف (خارج)</th>
                            <th style="color:#fbbf24; background:rgba(251,191,36,0.05);">الرصيد التراكمي</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-tbody">
                        <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-secondary);">يرجى اختيار صنف لعرض حركته التفصيلية</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054967.9791"></script>
    <script>
        const ITEMS_STORAGE_KEY = 'erp_items_v1';
        let itemsDb = [];
        let warehousesDb = [];

        function fmtNum(n) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            return Number(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
        }

        function getWarehouseId(nameOrId) {
            if (!nameOrId) return '';
            const wh = warehousesDb.find(w => w.id === nameOrId || w.name === nameOrId);
            return wh ? wh.id : nameOrId;
        }
        
        document.addEventListener('DBReady', () => {
            // Load Items to Select
            itemsDb = JSON.parse(localStorage.getItem(ITEMS_STORAGE_KEY)) || [];
            const select = document.getElementById('item-select');
            itemsDb.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.text = `[${item.id}] ${item.name} - ${item.category}`;
                select.appendChild(opt);
            });

            // Load Warehouses
            const whSelect = document.getElementById('warehouse-select');
            warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            const displayWh = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];
            displayWh.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.id;
                opt.text = wh.name;
                whSelect.appendChild(opt);
            });

            // Set default dates (1st of month to today)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            document.getElementById('date-to').valueAsDate = today;

            // Check if URL has ?item=ID
            const urlParams = new URLSearchParams(window.location.search);
            const itemId = urlParams.get('item');
            if (itemId) {
                select.value = itemId;
                loadLedger();
            }
        });

        function buildRealHistory(itemId) {
            let history = [];
            
            // 1. Get Purchases
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                (inv.items || inv.lines || []).forEach(line => {
                    if (line.itemId === itemId || line.productId === itemId) {
                        let wh = getWarehouseId(line.warehouseId || inv.warehouseId || inv.warehouse);
                        if (wh === 'PENDING_RECEIPT') {
                            return; // Skip pending items (not in actual warehouse yet)
                        }

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
                            typeName: 'استلام مشتريات',
                            ref: inv.invoiceNo || inv.poNumber || inv.id,
                            docId: inv.invoiceNo || inv.id,
                            warehouse: wh,
                            in: baseQty,
                            out: 0
                        });
                    }
                });
            });

            // 2. Get Sales
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && x.status !== 'draft' && x.status !== 'preliminary' && x.status !== 'pending_delivery');
            sales.forEach(inv => {
                let hdrWh = inv.warehouse;
                
                (inv.items || []).forEach(line => {
                    if (line.itemId === itemId) {
                        let lineWh = getWarehouseId(inv.deliveryWarehouse || line.warehouseId || hdrWh);
                        if (lineWh === 'PENDING_RECEIPT') {
                            return; // Skip pending delivery items
                        }
                        
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
                            docId: inv.invoiceNo || inv.id,
                            warehouse: lineWh,
                            in: 0,
                            out: baseQty
                        });
                    }
                });
            });

            // Sort chronological
            history.sort((a,b) => a.date - b.date);
            return history;
        }

        function loadLedger() {
            const itemId = document.getElementById('item-select').value;
            const tbody = document.getElementById('ledger-tbody');
            
            if (!itemId) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-secondary);">يرجى اختيار صنف لعرض حركته التفصيلية</td></tr>`;
                document.getElementById('sum-open').innerText = '0';
                document.getElementById('sum-in').innerText = '0';
                document.getElementById('sum-out').innerText = '0';
                document.getElementById('sum-balance').innerText = '0';
                return;
            }

            const item = itemsDb.find(i => i.id === itemId);
            
            // 1. Build history
            let history = buildRealHistory(itemId);

            // 2. Filter by warehouse first to calculate the correct running balance for this warehouse
            const filterWh = document.getElementById('warehouse-select').value;
            if (filterWh && filterWh !== 'ALL') {
                history = history.filter(h => getWarehouseId(h.warehouse) === getWarehouseId(filterWh) || h.typeCode === 'open');
            }

            // 3. Sort chronologically
            history.sort((a, b) => new Date(a.date) - new Date(b.date));

            // 4. Calculate true running balances chronologically (unfiltered by date)
            let running = 0;
            history.forEach(row => {
                running += (row.in - row.out);
                row.runningBalance = running;
            });

            // Keep track of the final overall balance (actual current stock)
            const actualCurrentStock = running;

            // 5. Apply Date Filters for display
            const fromDateStr = document.getElementById('date-from').value;
            const toDateStr = document.getElementById('date-to').value;
            
            let filteredHistory = history.map(h => ({ ...h, date: new Date(h.date) }));

            if (fromDateStr) {
                let d = new Date(fromDateStr); d.setHours(0,0,0,0);
                filteredHistory = filteredHistory.filter(h => h.date >= d);
            }
            if (toDateStr) {
                let d = new Date(toDateStr); d.setHours(23,59,59,999);
                filteredHistory = filteredHistory.filter(h => h.date <= d);
            }

            tbody.innerHTML = '';
            let sumIn = 0;
            let sumOut = 0;
            let sumOpen = 0;

            if (filteredHistory.length === 0) {
                 tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد حركات لهذا الصنف في هذه الفترة</td></tr>`;
            } else {
                 // The "opening balance" of the filtered period is the running balance just before the first filtered row
                 const firstFilteredIndex = history.findIndex(h => h.date >= new Date(fromDateStr || '1970-01-01'));
                 if (firstFilteredIndex > 0) {
                     sumOpen = history[firstFilteredIndex - 1].runningBalance;
                 } else {
                     sumOpen = 0;
                 }
            }

            filteredHistory.forEach((row, index) => {
                if (row.typeCode === 'in') sumIn += row.in;
                if (row.typeCode === 'out') sumOut += row.out;

                let typeBadge = '';
                if (row.typeCode === 'in') typeBadge = `<span class="type-badge type-in">${row.typeName}</span>`;
                else if (row.typeCode === 'out') typeBadge = `<span class="type-badge type-out">${row.typeName}</span>`;
                else typeBadge = `<span class="type-badge type-bal">${row.typeName}</span>`;

                const dStr = row.date.toISOString().split('T')[0];

                const tr = document.createElement('tr');
                let target = '';
                if (row.typeCode === 'out') target = `sales-invoice.php?id=${row.docId}`;
                else if (row.typeCode === 'in') target = `purchase.php?id=${row.docId}`;

                let refHtml = row.ref;
                if (row.docId && target) {
                    tr.style.cursor = 'pointer';
                    tr.title = 'انقر لفتح المستند الأصلي';
                    tr.onclick = () => { window.open(target, '_blank'); };
                    tr.onmouseover = () => tr.style.background = 'rgba(56, 189, 248, 0.05)';
                    tr.onmouseout = () => tr.style.background = '';
                    
                    refHtml = `
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span>${row.ref}</span>
                            <button onclick="event.stopPropagation(); window.open('${target}', '_blank')" style="background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; padding:3px 8px; cursor:pointer; font-size:0.75rem; font-weight:bold; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#38bdf8'; this.style.color='#fff';" onmouseout="this.style.background='rgba(56,189,248,0.1)'; this.style.color='#38bdf8';">
                                🔗 عرض الأصل
                            </button>
                        </div>
                    `;
                }

                tr.innerHTML = `
                    <td>${dStr}</td>
                    <td>${typeBadge}</td>
                    <td style="font-weight:600;">${refHtml}</td>
                    <td style="color:#34d399; font-weight:700; background:rgba(52,211,153,0.02);">${row.in > 0 ? fmtNum(row.in) : '-'}</td>
                    <td style="color:#f87171; font-weight:700; background:rgba(248,113,113,0.02);">${row.out > 0 ? fmtNum(row.out) : '-'}</td>
                    <td style="color:#fbbf24; font-weight:900; background:rgba(251,191,36,0.02); font-size:1.1rem;">${fmtNum(row.runningBalance)}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('sum-open').innerText = sumOpen.toLocaleString();
            document.getElementById('sum-in').innerText = sumIn.toLocaleString();
            document.getElementById('sum-out').innerText = sumOut.toLocaleString();
            document.getElementById('sum-balance').innerText = actualCurrentStock.toLocaleString();
        }
    </script>
</body>
</html>

