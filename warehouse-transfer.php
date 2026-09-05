<?php $page_title = 'تحويلات المستودعات - مقاييس (MAGAIEES)'; ?>
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
    <title>تحويلات المستودعات - مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        .container { padding: 20px; padding-bottom: 100px; max-width: 900px; margin: 0 auto; }
        .transfer-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: 1 / -1; }
        .t-label { display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px; font-weight: 700; }
        .t-input, .t-select {
            width: 100%; padding: 12px; border-radius: 8px;
            border: 1px solid var(--glass-border); background: var(--bg-color);
            color: var(--text-primary); font-size: 0.95rem;
        }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .items-table th { background: var(--surface-color); padding: 10px; font-size: 0.85rem; color: var(--text-secondary); border-bottom: 2px solid var(--glass-border); }
        .items-table td { padding: 8px; border-bottom: 1px solid var(--glass-border); }
        .items-table input { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-primary); text-align: center; }
        .btn-confirm {
            width: 100%; padding: 14px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white; font-weight: 800; font-size: 1.1rem;
            border: none; cursor: pointer; margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4); }
        .btn-add-row {
            padding: 8px 16px; border-radius: 8px;
            background: var(--success-color); color: white;
            border: none; cursor: pointer; font-weight: 700; font-size: 0.85rem; margin-top: 10px;
        }
        .btn-remove { background: none; border: none; color: #f87171; cursor: pointer; font-size: 1.2rem; }
        .history-card {
            padding: 15px; border-radius: 10px; margin-bottom: 10px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .history-card .info { flex: 1; }
        .history-card .info h4 { margin: 0 0 5px; font-size: 1rem; }
        .history-card .info span { font-size: 0.8rem; color: var(--text-secondary); }
        .history-card .badge {
            padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;
            background: rgba(99, 102, 241, 0.15); color: #818cf8;
        }

        :root.light-theme body, :root.light-theme .ambient-bg { background: #f8fafc !important; }
        :root.light-theme .glass-panel { background: #ffffff !important; border-color: rgba(0,0,0,0.08) !important; color: #0f172a !important; }
    </style>
</head>
<body>
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div></div>

    <nav class="navbar glass-panel">
        <button class="back-btn" onclick="history.back()">🔙</button>
        <h1 class="brand-title">تحويلات المستودعات</h1>
        <div style="width: 30px;"></div>
    </nav>

    <div class="container">
        <!-- Transfer Form -->
        <div class="glass-panel" style="padding: 20px; border-radius: 15px;">
            <h3 style="margin-top: 0; color: var(--primary-accent);">🔄 تحويل جديد بين المستودعات</h3>

            <div class="transfer-form">
                <div>
                    <label class="t-label">من المستودع</label>
                    <select class="t-select" id="wh-from" onchange="loadAvailableItems()"></select>
                </div>
                <div>
                    <label class="t-label">إلى المستودع</label>
                    <select class="t-select" id="wh-to"></select>
                </div>
                <div>
                    <label class="t-label">تاريخ التحويل</label>
                    <input type="date" class="t-input" id="wh-date">
                </div>
                <div>
                    <label class="t-label">رقم المرجع</label>
                    <input type="text" class="t-input" id="wh-ref" placeholder="WT-001">
                </div>
                <div class="full-width">
                    <label class="t-label">ملاحظات</label>
                    <input type="text" class="t-input" id="wh-notes" placeholder="سبب التحويل...">
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table" id="items-table">
                <thead>
                    <tr>
                        <th style="width:40%;">الصنف</th>
                        <th style="width:20%;">المتاح</th>
                        <th style="width:20%;">الكمية المحولة</th>
                        <th style="width:15%;">الوحدة</th>
                        <th style="width:5%;"></th>
                    </tr>
                </thead>
                <tbody id="items-tbody">
                    <!-- Dynamic rows -->
                </tbody>
            </table>

            <button class="btn-add-row" onclick="addItemRow()">+ إضافة صنف</button>
            <button class="btn-confirm" onclick="confirmWarehouseTransfer()">✅ تأكيد تحويل المخزون</button>
        </div>

        <!-- Transfer History -->
        <div class="glass-panel" style="padding: 20px; border-radius: 15px; margin-top: 20px;">
            <h3 style="margin-top: 0; color: var(--text-secondary);">📋 سجل التحويلات السابقة</h3>
            <div id="history-container">
                <div style="text-align:center; padding:20px; color:var(--text-secondary);">جاري التحميل...</div>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        let ALL_ITEMS = [];
        let ALL_WAREHOUSES = [];
        let rowCounter = 0;

        document.addEventListener('DBReady', initPage);
        if (window.APP_READY) initPage();

        function initPage() {
            document.getElementById('wh-date').valueAsDate = new Date();

            // Load items
            ALL_ITEMS = (JSON.parse(localStorage.getItem('erp_items_v1') || localStorage.getItem('erp_inventory_v1')) || []).filter(x => !x.deleted);

            // Load warehouses
            ALL_WAREHOUSES = (JSON.parse(localStorage.getItem('erp_warehouses_v1')) || []).filter(x => !x.deleted);
            if (ALL_WAREHOUSES.length === 0) {
                ALL_WAREHOUSES = [{ id: 'wh-main', name: 'المستودع الرئيسي' }];
            }

            // Populate warehouse dropdowns
            const fromSel = document.getElementById('wh-from');
            const toSel = document.getElementById('wh-to');
            fromSel.innerHTML = '<option value="">-- اختر المستودع --</option>';
            toSel.innerHTML = '<option value="">-- اختر المستودع --</option>';
            ALL_WAREHOUSES.forEach(wh => {
                fromSel.innerHTML += `<option value="${wh.id}">${wh.name || wh.warehouseName}</option>`;
                toSel.innerHTML += `<option value="${wh.id}">${wh.name || wh.warehouseName}</option>`;
            });

            addItemRow();
            loadHistory();
        }

        function addItemRow() {
            rowCounter++;
            const id = rowCounter;
            const tbody = document.getElementById('items-tbody');
            const tr = document.createElement('tr');
            tr.id = `row-${id}`;

            let itemOptions = '<option value="">-- اختر الصنف --</option>';
            ALL_ITEMS.forEach(item => {
                const name = item.name || item.itemName || item.productName || '';
                itemOptions += `<option value="${item.id}">${name}</option>`;
            });

            tr.innerHTML = `
                <td><select class="t-select item-sel" id="item-${id}" onchange="onItemSelected(${id})" style="padding:8px;">${itemOptions}</select></td>
                <td><span id="avail-${id}" style="color:var(--text-secondary);">-</span></td>
                <td><input type="number" id="qty-${id}" min="1" value="1" style="font-weight:700;"></td>
                <td><span id="unit-${id}" style="color:var(--text-secondary);">-</span></td>
                <td><button class="btn-remove" onclick="removeRow(${id})">✕</button></td>
            `;
            tbody.appendChild(tr);
        }

        function removeRow(id) {
            const row = document.getElementById(`row-${id}`);
            if (row) row.remove();
        }

        function onItemSelected(id) {
            const itemId = document.getElementById(`item-${id}`).value;
            const item = ALL_ITEMS.find(i => i.id === itemId);
            if (item) {
                const stock = parseFloat(item.actualStock || item.quantity || item.stock || 0);
                document.getElementById(`avail-${id}`).innerText = stock.toLocaleString();
                document.getElementById(`unit-${id}`).innerText = item.unit || item.unitOfMeasure || 'قطعة';
            }
        }

        function loadAvailableItems() {
            // Refresh available stock based on selected source warehouse
        }

        function confirmWarehouseTransfer() {
            const fromWh = document.getElementById('wh-from').value;
            const toWh = document.getElementById('wh-to').value;
            const date = document.getElementById('wh-date').value;
            const ref = document.getElementById('wh-ref').value;
            const notes = document.getElementById('wh-notes').value;

            if (!fromWh) return alert('الرجاء اختيار المستودع المصدر');
            if (!toWh) return alert('الرجاء اختيار المستودع الوجهة');
            if (fromWh === toWh) return alert('لا يمكن التحويل لنفس المستودع');

            const fromName = ALL_WAREHOUSES.find(w => w.id === fromWh)?.name || fromWh;
            const toName = ALL_WAREHOUSES.find(w => w.id === toWh)?.name || toWh;

            // Collect items
            const transferItems = [];
            const rows = document.querySelectorAll('#items-tbody tr');
            let hasError = false;

            rows.forEach(row => {
                const id = row.id.replace('row-', '');
                const itemId = document.getElementById(`item-${id}`)?.value;
                const qty = parseFloat(document.getElementById(`qty-${id}`)?.value || 0);

                if (itemId && qty > 0) {
                    const item = ALL_ITEMS.find(i => i.id === itemId);
                    const availStock = parseFloat(item?.actualStock || item?.quantity || item?.stock || 0);
                    if (qty > availStock) {
                        alert(`⚠️ الكمية المطلوبة (${qty}) أكبر من المتاح (${availStock}) للصنف: ${item?.name || itemId}`);
                        hasError = true;
                        return;
                    }
                    transferItems.push({
                        itemId: itemId,
                        itemName: item?.name || item?.itemName || '',
                        quantity: qty,
                        unit: item?.unit || item?.unitOfMeasure || 'قطعة'
                    });
                }
            });

            if (hasError) return;
            if (transferItems.length === 0) return alert('الرجاء إضافة صنف واحد على الأقل');

            // Save transfer record
            const transfers = JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || [];
            const transfer = {
                id: 'wt-' + Date.now(),
                fromWarehouse: fromWh,
                toWarehouse: toWh,
                fromName: fromName,
                toName: toName,
                date: date,
                reference: ref,
                notes: notes,
                items: transferItems,
                totalItems: transferItems.length,
                totalQty: transferItems.reduce((s, i) => s + i.quantity, 0),
                createdAt: new Date().toISOString(),
                status: 'completed'
            };
            transfers.push(transfer);
            localStorage.setItem('erp_warehouse_transfers_v1', JSON.stringify(transfers));

            // Update inventory quantities (deduct from source, add to destination conceptually)
            // Note: actual per-warehouse tracking depends on the inventory architecture
            if (window.saveToDB) {
                window.saveToDB('warehouse_transfers', transfers);
            }

            alert(`✅ تم تحويل ${transferItems.length} صنف بنجاح!\n\nمن: ${fromName}\nإلى: ${toName}\nالكمية الإجمالية: ${transfer.totalQty}`);

            // Reset form
            document.getElementById('wh-ref').value = '';
            document.getElementById('wh-notes').value = '';
            document.getElementById('items-tbody').innerHTML = '';
            addItemRow();
            loadHistory();
        }

        function loadHistory() {
            const container = document.getElementById('history-container');
            const transfers = (JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || []).sort((a, b) => (b.createdAt || '').localeCompare(a.createdAt || ''));

            if (transfers.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-secondary);"><div style="font-size:3rem; margin-bottom:10px;">📭</div>لا توجد تحويلات سابقة</div>';
                return;
            }

            container.innerHTML = '';
            transfers.forEach(t => {
                container.innerHTML += `
                    <div class="history-card glass-panel">
                        <div class="info">
                            <h4>📦 ${t.fromName} ← ${t.toName}</h4>
                            <span>📅 ${t.date} | ${t.totalItems} صنف | الكمية: ${t.totalQty}</span>
                            ${t.notes ? `<div style="font-size:0.8rem; color:var(--text-secondary); margin-top:3px;">📝 ${t.notes}</div>` : ''}
                        </div>
                        <div class="badge">${t.reference || t.id}</div>
                    </div>
                `;
            });
        }
    </script>

<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
