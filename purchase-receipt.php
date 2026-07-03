<?php $page_title = 'استلام البضاعة (GRN)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استلام البضاعة (GRN) | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
        :root {
            --bg-color: #050b14;
            --grn-primary: #38bdf8;
            --grn-hover: #0284c7;
            --grn-glow: rgba(56, 189, 248, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            background-color: var(--bg-color);
            color: #f1f5f9;
            font-family: 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            overflow-x: hidden;
            display: flex;
            height: 100vh;
        }

        body.has-sidebar { padding-right: 0 !important; }

        /* Ambient Animated Background for GRN (Blue/Cyan tones) */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            z-index: -1; overflow: hidden; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #031020 0%, #000000 100%);
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px);
            animation: floatOrb 20s infinite alternate ease-in-out; opacity: 0.15;
        }
        .orb-1 { width: 600px; height: 600px; background: #38bdf8; top: -10%; right: -10%; }
        .orb-2 { width: 500px; height: 500px; background: #0ea5e9; bottom: -10%; left: -10%; animation-delay: -5s; }
        .orb-3 { width: 400px; height: 400px; background: #0284c7; top: 40%; left: 40%; animation-delay: -10s; opacity: 0.1;}

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -50px) scale(1.1); }
        }

        .dash-main {
            flex: 1; display: flex; flex-direction: column; overflow: hidden;
        }

        /* Premium Header */
        @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        
        .premium-header {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(2, 132, 199, 0.15), rgba(14, 165, 233, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15); padding: 12px 25px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden; flex-shrink: 0;
            margin: 15px; border-radius: 20px;
        }

        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 16px;
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #bae6fd; font-weight: 500; }
        
        .header-controls { display: flex; align-items: center; gap: 15px; }
        .icon-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        /* Container Views */
        .view-container {
            display: none; flex-direction: column; height: 100%; flex: 1; padding: 0 15px 15px 15px; overflow-y: auto;
            animation: fadeIn 0.5s ease backwards;
        }
        .view-container.active { display: flex; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Hub View (Invoice Selection) */
        .neo-card {
            background: rgba(15, 23, 42, 0.65); border: 1px solid var(--glass-border); border-radius: 20px;
            padding: 20px; display: flex; flex-direction: column; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); flex: 1;
        }
        .neo-card-title { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }

        .v-input {
            width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px; color: #fff; font-size: 0.9rem; outline: none; transition: 0.3s; font-family: 'Outfit', 'Tajawal', sans-serif;
        }
        .v-input:focus { border-color: var(--grn-primary); box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15); }

        /* Data Grid */
        .grid-scroll-wrap { flex: 1; overflow-y: auto; overflow-x: auto; padding-right: 5px; }
        .grid-scroll-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
        .grid-scroll-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 6px; }
        
        .pos-grid { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        .pos-grid thead th {
            background: rgba(255,255,255,0.02); color: #94a3b8; font-size: 0.85rem; font-weight: 800;
            padding: 12px 15px; text-align: center; position: sticky; top: 0; z-index: 5;
            border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .pos-grid tbody tr {
            background: rgba(0,0,0,0.2); transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover {
            background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-grid tbody td {
            padding: 15px 12px; border-bottom: 1px solid rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.02);
            font-size: 0.9rem; vertical-align: middle;
        }
        .pos-grid tbody td:first-child { border-radius: 12px 0 0 12px; border-right: 1px solid rgba(255,255,255,0.02); }
        .pos-grid tbody td:last-child { border-radius: 0 12px 12px 0; border-left: 1px solid rgba(255,255,255,0.02); }

        .btn-action {
            background: linear-gradient(135deg, var(--grn-primary), var(--grn-hover)); color: #fff;
            border: none; padding: 8px 16px; border-radius: 8px; font-weight: 800; cursor: pointer;
            transition: 0.3s; box-shadow: 0 4px 15px var(--grn-glow); font-family: 'Tajawal', sans-serif;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--grn-glow); }
        .btn-cancel { background: rgba(255,255,255,0.05); box-shadow: none; border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-cancel:hover { background: rgba(255,255,255,0.1); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }

        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-dim); padding: 40px; }
        .empty-state .icon { font-size: 4rem; margin-bottom: 15px; opacity: 0.5; filter: drop-shadow(0 0 10px rgba(56, 189, 248, 0.3)); }

        /* Editor View */
        .editor-layout { display: flex; gap: 20px; height: 100%; }
        .editor-left { flex: 1; display: flex; flex-direction: column; background: rgba(15, 23, 42, 0.65); border: 1px solid var(--glass-border); border-radius: 20px; padding: 20px; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .editor-right { width: 350px; display: flex; flex-direction: column; gap: 20px; }
        
        .info-card { background: rgba(0,0,0,0.4); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); padding: 20px; }
        .info-card h3 { margin: 0 0 15px 0; color: #fff; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; }
        .info-lbl { color: #94a3b8; }
        .info-val { color: #fff; font-weight: 800; font-family: 'Outfit', sans-serif; direction: ltr; }
        
        .qty-input {
            width: 80px; text-align: center; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.5);
            color: #fff; padding: 8px; border-radius: 8px; font-weight: 800; font-family: 'Outfit', sans-serif;
        }
        .qty-input:focus { border-color: #38bdf8; box-shadow: 0 0 10px rgba(56, 189, 248, 0.3); }

        .wh-select {
            width: 100%; padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px; color: #fff; font-size: 0.95rem; font-family: 'Tajawal', sans-serif; margin-bottom: 15px;
        }
        .wh-select:focus { border-color: var(--grn-primary); outline: none; }
        .wh-select option { background: #0f172a; color: #fff; }

    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="dash-main">
        <?php include 'includes/header.php'; ?>

        <!-- View 1: Hub (Select Invoice with Pending Goods) -->
        <div class="view-container active" id="view-hub">
            <div class="neo-card">
                <div class="neo-card-title">
                    <span>⏳ فواتير تحتوي على بضاعة قيد الاستلام</span>
                    <input type="text" id="search-inv" class="v-input" placeholder="ابحث برقم الفاتورة أو المورد..." style="width: 300px;" oninput="renderPendingInvoices()">
                </div>
                
                <div class="grid-scroll-wrap">
                    <table class="pos-grid">
                        <thead>
                            <tr>
                                <th style="text-align:right;">رقم الفاتورة الأصلي</th>
                                <th style="text-align:right;">التاريخ</th>
                                <th style="text-align:right;">المورد</th>
                                <th style="text-align:center;">إجمالي الأصناف المعلقة</th>
                                <th style="text-align:center;">إجراء</th>
                            </tr>
                        </thead>
                        <tbody id="invoices-tbody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                    <div class="empty-state" id="empty-hub" style="display: none;">
                        <div class="icon">✨</div>
                        <h2>لا توجد بضاعة قيد الاستلام</h2>
                        <p>جميع مشترياتك تم استلامها بالكامل وتم إدخالها في المخازن الفعلية.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- View 2: Editor (Receive Items) -->
        <div class="view-container" id="view-editor">
            <div class="editor-layout">
                <!-- Left: Items Table -->
                <div class="editor-left">
                    <div class="neo-card-title">
                        <span>📦 البضاعة المعلقة للفاتورة: <span id="lbl-inv-no" style="color:var(--grn-primary); font-family:'Outfit';"></span></span>
                        <button class="btn-action btn-cancel" style="padding: 6px 12px; font-size: 0.8rem;" onclick="showHub()">إلغاء التحديد</button>
                    </div>
                    
                    <div class="grid-scroll-wrap" style="flex:1;">
                        <table class="pos-grid">
                            <thead>
                                <tr>
                                    <th style="text-align:right; width:35%;">الصنف</th>
                                    <th style="text-align:center;">الوحدة</th>
                                    <th style="text-align:center;">الكمية المعلقة المتبقية</th>
                                    <th style="text-align:center; color:#38bdf8;">الكمية المستلمة (الآن)</th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Totals & Save -->
                <div class="editor-right">
                    <div class="info-card">
                        <h3>تفاصيل المورد</h3>
                        <div class="info-row"><span class="info-lbl">المورد:</span> <span class="info-val" id="lbl-supplier" style="direction:rtl;">-</span></div>
                        <div class="info-row"><span class="info-lbl">تاريخ الفاتورة:</span> <span class="info-val" id="lbl-date">-</span></div>
                    </div>

                    <div class="info-card" style="flex:1;">
                        <h3>إجراء الاستلام</h3>
                        <label style="color:#94a3b8; font-size:0.9rem; margin-bottom:8px; display:block;">تحديد المستودع المُستلِم (الفعلي)</label>
                        <select class="wh-select" id="actual-warehouse">
                            <!-- Populated by JS -->
                        </select>
                        <p style="color:#cbd5e1; font-size:0.8rem; line-height:1.6;">
                            سيتم نقل الأصناف والكميات المحددة من حالة (قيد الاستلام) إلى المستودع الفعلي المختار أعلاه. هذا الإجراء سيؤثر فوراً على الجرد العام وتقارير المخزون.
                        </p>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <button class="btn-action" style="padding: 15px; font-size: 1.1rem; display:flex; align-items:center; justify-content:center; gap:10px;" onclick="saveReceiptNote()">
                            <span>💾</span> حفظ مذكرة الاستلام (GRN)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast Container -->
    <div class="toast-container" id="toast-container" style="position:fixed; bottom:20px; left:20px; z-index:99999; display:flex; flex-direction:column; gap:10px;"></div>

    <script src="js/app.js?v=1783054969.18727"></script>
    <script>
        let allPurchases = [];
        let itemsDb = [];
        let suppliersDb = [];
        let warehousesDb = [];
        let pendingMap = {}; // originalInvoiceId -> array of pending items
        let currentInvoiceId = null;
        let receiptLines = [];

        // Formatting utilities
        function fmtNum(n) { return Number(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2}); }
        function parseNum(str) { if(!str) return 0; return parseFloat(String(str).replace(/,/g, '')) || 0; }
        function showToast(msg, type='info') {
            const container = document.getElementById('toast-container');
            const t = document.createElement('div');
            t.style.padding = '12px 20px'; t.style.borderRadius = '8px'; t.style.color = '#fff'; t.style.fontSize = '0.9rem'; t.style.fontWeight = '700'; t.style.boxShadow = '0 5px 15px rgba(0,0,0,0.4)'; t.style.animation = 'slideDown 0.3s ease forwards';
            if(type === 'error') t.style.background = 'rgba(239, 68, 68, 0.9)';
            else if(type === 'success') t.style.background = 'rgba(16, 185, 129, 0.9)';
            else t.style.background = 'rgba(56, 189, 248, 0.9)';
            t.textContent = msg;
            container.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; setTimeout(()=>t.remove(), 300); }, 3000);
        }

        window.addEventListener('DBReady', () => {
            allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            suppliersDb = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if(warehousesDb.length === 0) warehousesDb = [{ id:'w-main', name:'المخزن الرئيسي (السجانة)' }];

            populateWarehouseSelect();
            calculatePendingGoods();
            renderPendingInvoices();
        });

        function populateWarehouseSelect() {
            const select = document.getElementById('actual-warehouse');
            select.innerHTML = '';
            // Only actual warehouses
            warehousesDb.forEach(wh => {
                if (wh.id !== 'PENDING_RECEIPT') {
                    const opt = document.createElement('option');
                    opt.value = wh.id;
                    opt.text = wh.name;
                    select.appendChild(opt);
                }
            });
        }

        // Extremely critical: calculate actual pending balances
        // For each invoice, sum quantities where warehouseId == 'PENDING_RECEIPT'.
        // Original invoices add positive pending qty. Returns subtract pending qty. GRNs subtract pending qty.
        function calculatePendingGoods() {
            pendingMap = {}; // target: { invId: { supplierId, date, lines: { itemId: { unitId, qty, originalLine } } } }

            // Base tracking by invoice
            allPurchases.filter(inv => !inv.deleted && inv.status !== 'draft' && inv.status !== 'proforma').forEach(inv => {
                const rootInvId = inv.isReturn || inv.isGRN ? inv.originalInvoiceId : inv.id;
                if (!rootInvId) return;

                if (!pendingMap[rootInvId]) {
                    pendingMap[rootInvId] = {
                        supplierId: inv.customerId || inv.supplierId,
                        date: inv.date,
                        lines: {}
                    };
                }

                const items = inv.items || inv.lines || [];
                const globalWh = inv.warehouse;

                items.forEach(line => {
                    const lineWh = line.warehouseId || globalWh;
                    
                    // We only care about items associated with PENDING_RECEIPT
                    if (lineWh === 'PENDING_RECEIPT') {
                        const lineKey = line.itemId + '|' + line.unitId;
                        if (!pendingMap[rootInvId].lines[lineKey]) {
                            pendingMap[rootInvId].lines[lineKey] = {
                                itemId: line.itemId,
                                unitId: line.unitId,
                                unitName: line.unitName || 'حبة',
                                price: parseFloat(line.price) || 0, // needed if we return or value it, though GRN takes 0 value
                                pendingQty: 0
                            };
                        }
                        
                        // If it's the original invoice, quantity is positive (adding to pending).
                        // If it's a GRN, the GRN creates a negative line for PENDING_RECEIPT (subtracting from pending).
                        // If it's a Return, the return creates a negative line for PENDING_RECEIPT (subtracting from pending).
                        // So we just add the quantity!
                        pendingMap[rootInvId].lines[lineKey].pendingQty += (parseFloat(line.quantity) || 0);
                    }
                });
            });

            // Cleanup: remove items with <= 0 pending qty, and invoices with no pending items
            Object.keys(pendingMap).forEach(invId => {
                const lines = pendingMap[invId].lines;
                Object.keys(lines).forEach(lk => {
                    // Precision fix
                    lines[lk].pendingQty = Math.round(lines[lk].pendingQty * 100) / 100;
                    if (lines[lk].pendingQty <= 0) {
                        delete lines[lk];
                    }
                });
                if (Object.keys(lines).length === 0) {
                    delete pendingMap[invId];
                }
            });
        }

        function getSupplierName(id) {
            if(!id) return 'مورد نقدي';
            const sup = suppliersDb.find(s => s.id === id);
            return sup ? sup.name : 'مورد نقدي';
        }

        function getItemName(id) {
            const it = itemsDb.find(i => i.id === id);
            return it ? it.name : 'صنف غير معروف';
        }

        function renderPendingInvoices() {
            const tbody = document.getElementById('invoices-tbody');
            const search = document.getElementById('search-inv').value.toLowerCase();
            tbody.innerHTML = '';
            
            const invIds = Object.keys(pendingMap);

            const filteredIds = invIds.filter(id => {
                if (search) {
                    const supName = getSupplierName(pendingMap[id].supplierId).toLowerCase();
                    return id.toLowerCase().includes(search) || supName.includes(search);
                }
                return true;
            });

            if (filteredIds.length === 0) {
                document.getElementById('empty-hub').style.display = 'flex';
            } else {
                document.getElementById('empty-hub').style.display = 'none';
                
                // Sort by date desc
                filteredIds.sort((a,b) => new Date(pendingMap[b].date) - new Date(pendingMap[a].date));
                
                filteredIds.forEach(id => {
                    const invData = pendingMap[id];
                    const supName = getSupplierName(invData.supplierId);
                    
                    const totalPendingItems = Object.values(invData.lines).length;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="text-align:right; font-family:'Outfit'; color:#38bdf8; font-weight:700;">${id}</td>
                        <td style="text-align:right; font-family:'Outfit';">${new Date(invData.date).toLocaleDateString('en-GB')}</td>
                        <td style="text-align:right; font-weight:700;">${supName}</td>
                        <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${totalPendingItems} صنف</td>
                        <td style="text-align:center;">
                            <button class="btn-action" style="padding:6px 15px; font-size:0.8rem;" onclick="openReceiptEditor('${id}')">استلام البضاعة</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function showHub() {
            document.getElementById('view-editor').classList.remove('active');
            document.getElementById('view-hub').classList.add('active');
            currentInvoiceId = null;
            receiptLines = [];
        }

        function openReceiptEditor(invId) {
            currentInvoiceId = invId;
            const invData = pendingMap[invId];
            if (!invData) return showToast('خطأ في البيانات', 'error');

            document.getElementById('lbl-inv-no').textContent = invId;
            document.getElementById('lbl-supplier').textContent = getSupplierName(invData.supplierId);
            document.getElementById('lbl-date').textContent = new Date(invData.date).toLocaleDateString('en-GB');

            receiptLines = Object.values(invData.lines).map((line, index) => {
                return {
                    id: 'L' + index,
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    maxPending: line.pendingQty,
                    receiveQty: line.pendingQty // Default to fully receiving what's pending
                };
            });

            document.getElementById('view-hub').classList.remove('active');
            document.getElementById('view-editor').classList.add('active');
            renderReceiptLines();
        }

        function renderReceiptLines() {
            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = '';

            receiptLines.forEach(line => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="text-align:right; font-weight:700;">${getItemName(line.itemId)}</td>
                    <td style="text-align:center; color:#94a3b8;">${line.unitName}</td>
                    <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${line.maxPending}</td>
                    <td style="text-align:center;">
                        <input type="text" class="qty-input" inputmode="decimal" value="${line.receiveQty || ''}" placeholder="0" 
                               onfocus="this.select()" onchange="updateReceiveQty('${line.id}', this.value)">
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function updateReceiveQty(lineId, valStr) {
            const val = parseNum(valStr);
            const line = receiptLines.find(x => x.id === lineId);
            if (line) {
                if (val > line.maxPending) {
                    showToast(`الكمية المستلمة لا يمكن أن تتجاوز الكمية المعلقة (${line.maxPending})`, 'error');
                    line.receiveQty = line.maxPending;
                } else if (val < 0) {
                    line.receiveQty = 0;
                } else {
                    line.receiveQty = val;
                }
            }
            renderReceiptLines();
        }

        function saveReceiptNote() {
            const receivingItems = receiptLines.filter(x => x.receiveQty > 0);
            if (receivingItems.length === 0) {
                return showToast('يجب تحديد كمية استلام لصنف واحد على الأقل', 'error');
            }

            const targetWh = document.getElementById('actual-warehouse').value;
            if (!targetWh) return showToast('يرجى تحديد المستودع الفعلي', 'error');

            const invData = pendingMap[currentInvoiceId];

            // Double Entry Inventory Logic for GRN:
            // 1. Negative quantity for PENDING_RECEIPT (Removes it from the pending virtual warehouse)
            // 2. Positive quantity for ACTUAL_WAREHOUSE (Adds it to the real warehouse)
            // Values/Prices are 0 to not affect general accounting totals again.
            
            const newItems = [];
            receivingItems.forEach(line => {
                // Negative (Take out of PENDING)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: -line.receiveQty,
                    warehouseId: 'PENDING_RECEIPT',
                    price: 0, taxRate: 0, total: 0
                });
                // Positive (Put into ACTUAL)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: line.receiveQty,
                    warehouseId: targetWh,
                    price: 0, taxRate: 0, total: 0
                });
            });

            const grnInv = {
                id: 'GRN-' + Date.now(),
                isGRN: true,
                originalInvoiceId: currentInvoiceId,
                type: 'purchase', // ensures it gets picked up by inventory loops parsing purchase invoices
                customerId: invData.supplierId, // needed for reference, though amount is 0
                date: new Date().toISOString(),
                warehouse: targetWh,
                subtotal: 0,
                taxValue: 0,
                discountValue: 0,
                grandTotal: 0,
                items: newItems,
                status: 'completed',
                notes: `مذكرة استلام بضاعة للفاتورة ${currentInvoiceId}`
            };

            allPurchases.push(grnInv);
            localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));

            showToast(`تم حفظ مذكرة الاستلام وتحديث المخزون الفعلي بنجاح`, 'success');
            
            setTimeout(() => {
                // Recalculate pending state globally
                calculatePendingGoods();
                renderPendingInvoices();
                showHub();
            }, 1500);
        }

    </script>
</body>
</html>
