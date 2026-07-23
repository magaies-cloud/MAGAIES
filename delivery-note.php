<?php $page_title = 'أذونات التسليم 2026 - نظام الإدارة الشاملة'; ?>
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
    <title>أذونات التسليم 2026 - نظام الإدارة الشاملة</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
        <style>
        .container { padding: 20px; padding-bottom: 100px; }

        /* -- Filter Bar -- */
        .filter-bar {
            display: flex; gap: 10px; flex-wrap: wrap;
            background: var(--surface-color); border: 1px solid var(--glass-border);
            border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;
        }
        .filter-bar input, .filter-bar select {
            flex: 1; min-width: 140px; padding: 8px 12px; border-radius: 8px;
            border: 1px solid var(--glass-border); background: var(--bg-color);
            color: var(--text-primary); font-size: 0.85rem;
        }

        /* -- Summary -- */
        .summary-bar {
            display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .sum-chip {
            display: flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;
        }
        .sum-chip.pending { background: rgba(251,191,36,0.15); color: #fbbf24; border: 1px solid rgba(251,191,36,0.3); }
        .sum-chip.done    { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .sum-chip.all     { background: rgba(56,189,248,0.1);  color: #38bdf8; border: 1px solid rgba(56,189,248,0.2); }

        /* -- Order Card -- */
        .order-card {
            padding: 16px; margin-bottom: 14px;
            border-radius: 14px; background: var(--surface-color);
            border: 1px solid var(--glass-border);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .order-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
        .order-card.delivered { border-right: 4px solid #34d399; opacity: 0.75; }
        .order-card.pending   { border-right: 4px solid #fbbf24; }

        .order-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);
        }
        .order-id { font-weight: 900; color: var(--primary-accent); font-size: 1rem; }
        .order-meta { font-size: 0.75rem; color: var(--text-secondary); margin-top: 3px; }
        .order-status {
            font-size: 0.72rem; padding: 4px 10px; border-radius: 10px; font-weight: 700;
        }
        .order-status.pending   { background: rgba(251,191,36,0.2); color: #fbbf24; border: 1px solid rgba(251,191,36,0.3); }
        .order-status.delivered { background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }

        .item-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 5px;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
        }
        .item-name { color: var(--text-primary); }
        .item-qty  { font-weight: 700; color: var(--primary-accent); white-space: nowrap; }

        .order-footer {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 14px; gap: 10px;
        }
        .order-total { font-size: 0.8rem; color: var(--text-secondary); }
        .order-total span { font-weight: 800; color: var(--text-primary); }

        .btn-dispatch {
            display: flex; align-items: center; gap: 6px;
            padding: 10px 18px; border-radius: 10px;
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            color: white; border: none; font-weight: 800; cursor: pointer;
            font-size: 0.85rem; transition: opacity 0.2s; white-space: nowrap;
        }
        .btn-dispatch:hover { opacity: 0.85; }
        .btn-dispatch:disabled { background: var(--glass-border); color: var(--text-muted); cursor: not-allowed; opacity: 0.5; }

        /* -- Empty State -- */
        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--text-secondary);
        }
        .empty-icon { font-size: 4rem; margin-bottom: 16px; }
    
        
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

    <nav class="navbar glass-panel">
        <button class="back-btn" onclick="history.back()">🔙</button>
        <h1 class="brand-title">أذونات تسليم البضائع</h1>
        <div style="width: 30px;"></div>
    </nav>

    <div class="container">

        <!-- الفلاتر والبحث -->
        <div class="filter-bar glass-panel">
            <input type="text" id="search-input" placeholder="ابحث برقم الفاتورة أو العميل..." oninput="renderOrders()">
            <select id="filter-status" onchange="renderOrders()">
                <option value="all">الكل</option>
                <option value="pending" selected>بانتظار التسليم</option>
                <option value="delivered">تم التسليم</option>
            </select>
            <input type="date" id="filter-date-from" onchange="renderOrders()" title="من تاريخ">
            <input type="date" id="filter-date-to" onchange="renderOrders()" title="إلى تاريخ">
        </div>

        <!-- الملخص -->
        <div class="summary-bar">
            <div class="sum-chip all">الكل: <strong id="cnt-all">0</strong></div>
            <div class="sum-chip pending">انتظار: <strong id="cnt-pending">0</strong></div>
            <div class="sum-chip done">مكتمل: <strong id="cnt-done">0</strong></div>
        </div>

        <!-- قائمة الأذونات -->
        <div id="orders-container"></div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054967.34666"></script>
    <script>
        let allInvoices = [];

        document.addEventListener('DBReady', () => {
            loadData();
        });

        function loadData() {
            // جلب المبيعات المحجوزة
            const sales      = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')    || '[]');
            const reserved   = JSON.parse(localStorage.getItem('erp_reserved_sales_v1')    || '[]');
            const deliveries = JSON.parse(localStorage.getItem('erp_delivery_notes_v1')    || '[]');

            const reservedInvs = reserved.filter(inv =>
                inv.status === 'confirmed' || inv.status === 'reserved' || inv.saleType === 'reserved'
            );

            const needDelivery = sales.filter(inv =>
                inv.saleType === 'reserved' || inv.needsDelivery === true
            );

            allInvoices = [...reservedInvs, ...needDelivery].map(inv => {
                const deliveryRec = deliveries.find(d => d.invoiceNo === inv.invoiceNo);
                return {
                    ...inv,
                    deliveryStatus: deliveryRec ? 'delivered' : 'pending',
                    deliveredAt: deliveryRec?.deliveredAt || null,
                    driverName:  deliveryRec?.driverName  || null
                };
            });

            if (allInvoices.length === 0) {
                document.getElementById('orders-container').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <h3>لا توجد فواتير معتمدة</h3>
                        <p>يرجى عمل فاتورة مبيعات معتمدة لتظهر قائمة التسليم هنا.</p>
                        <p style="font-size:0.8rem; margin-top:8px; color:var(--text-muted);">
                            الفواتير تظهر من شاشة <strong>فواتير المبيعات</strong>
                        </p>
                    </div>`;
                updateCounters(0, 0, 0);
                return;
            }

            renderOrders();
        }

        function renderOrders() {
            const searchTerm  = document.getElementById('search-input').value.toLowerCase().trim();
            const statusFilter = document.getElementById('filter-status').value;
            const dateFrom    = document.getElementById('filter-date-from').value;
            const dateTo      = document.getElementById('filter-date-to').value;

            let filtered = allInvoices.filter(inv => {
                if (statusFilter !== 'all' && inv.deliveryStatus !== statusFilter) return false;
                if (dateFrom && inv.date < dateFrom) return false;
                if (dateTo   && inv.date > dateTo)   return false;
                if (searchTerm) {
                    const haystack = `${inv.invoiceNo || ''} ${inv.customerName || ''} ${inv.customer || ''}`.toLowerCase();
                    if (!haystack.includes(searchTerm)) return false;
                }
                return true;
            });

            const pending   = filtered.filter(i => i.deliveryStatus === 'pending').length;
            const delivered = filtered.filter(i => i.deliveryStatus === 'delivered').length;
            updateCounters(filtered.length, pending, delivered);

            const container = document.getElementById('orders-container');
            if (filtered.length === 0) {
                container.innerHTML = `<div class="empty-state"><div class="empty-icon">🔍</div><p>لا توجد طلبات مطابقة</p></div>`;
                return;
            }

            container.innerHTML = filtered.map(inv => {
                const isPending = inv.deliveryStatus === 'pending';
                const items     = (inv.items || []).slice(0, 5); // أقصى حد 5 أصناف

                const itemsHtml = items.length > 0
                    ? items.map(item => `
                        <div class="item-row">
                            <span class="item-name">${item.itemName || item.name || 'صنف'}</span>
                            <span class="item-qty">${(item.quantity || 0)} ${item.selectedUnit || item.unit || ''}</span>
                        </div>`).join('')
                    : '<div class="item-row" style="color:var(--text-muted);">لا توجد أصناف مسجلة</div>';

                const moreItems = (inv.items || []).length > 5
                    ? `<div style="font-size:0.75rem; color:var(--text-muted); text-align:center; padding:4px;">+ ${(inv.items||[]).length - 5} أصناف أخرى...</div>`
                    : '';

                return `
                <div class="order-card glass-panel ${isPending ? 'pending' : 'delivered'}">
                    <div class="order-header">
                        <div>
                            <div class="order-id"># ${inv.invoiceNo || inv.id || 'رقم غير محدد'}</div>
                            <div class="order-meta">
                                👤 ${inv.customerName || inv.customer || 'عميل نقدي'}
                                &nbsp;•&nbsp;
                                📅 ${inv.date || '-'}
                            </div>
                        </div>
                        <span class="order-status ${isPending ? 'pending' : 'delivered'}">
                            ${isPending ? '⏳ بانتظار التسليم' : '✓ تم التسليم'}
                        </span>
                    </div>

                    ${itemsHtml}
                    ${moreItems}

                    <div class="order-footer">
                        <div class="order-total">
                            الإجمالي: <span>${(inv.grandTotal || inv.total || 0).toLocaleString()} ${inv.currency || 'SDG'}</span>
                            ${inv.deliveredAt ? `<br><span style="color:#34d399;">تاريخ التسليم: ${new Date(inv.deliveredAt).toLocaleDateString('ar-SD')}</span>` : ''}
                        </div>
                        <button class="btn-dispatch"
                            onclick="dispatchOrder('${inv.invoiceNo || inv.id}')"
                            ${!isPending ? 'disabled' : ''}>
                            📦 ${isPending ? 'تسليم البضاعة' : 'تم التسليم'}
                        </button>
                    </div>
                </div>`;
            }).join('');
        }

        function updateCounters(all, pending, done) {
            document.getElementById('cnt-all').textContent     = all;
            document.getElementById('cnt-pending').textContent = pending;
            document.getElementById('cnt-done').textContent    = done;
        }

        function dispatchOrder(invoiceNo) {
            const inv = allInvoices.find(i => (i.invoiceNo || i.id) === invoiceNo);
            if (!inv) return;

            const driver = prompt(`تأكيد تسليم الفاتورة ${invoiceNo}\n\nاسم المستلم / السائق (اختياري):`);
            if (driver === null) return; // user cancelled

            // Save delivery record
            const deliveries = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
            deliveries.push({
                id:          'dn_' + Date.now(),
                invoiceNo:   invoiceNo,
                driverName:  driver || '-',
                deliveredAt: new Date().toISOString(),
                deliveredBy: JSON.parse(localStorage.getItem('user') || '{}').fullName || 'system'
            });
            localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(deliveries));

            // Update invoice status in sales invoices
            const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const idx   = sales.findIndex(i => i.invoiceNo === invoiceNo || i.id === invoiceNo);
            if (idx > -1) {
                sales[idx].deliveryStatus = 'delivered';
                sales[idx].deliveredAt    = new Date().toISOString();
                localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(sales));
            }

            // Also in reserved sales
            const reserved = JSON.parse(localStorage.getItem('erp_reserved_sales_v1') || '[]');
            const rIdx     = reserved.findIndex(i => i.invoiceNo === invoiceNo || i.id === invoiceNo);
            if (rIdx > -1) {
                reserved[rIdx].deliveryStatus = 'delivered';
                reserved[rIdx].deliveredAt    = new Date().toISOString();
                localStorage.setItem('erp_reserved_sales_v1', JSON.stringify(reserved));
            }

            // Update inventory (deduct reserved qty)
            if (inv.items && inv.items.length > 0) {
                const invData = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
                inv.items.forEach(item => {
                    const entry = invData.find(e => e.itemId === item.itemId && e.warehouse === inv.warehouseName);
                    if (entry) {
                        entry.reservedQuantity = Math.max(0, (parseFloat(entry.reservedQuantity) || 0) - (item.baseQty || item.quantity || 0));
                    }
                });
                localStorage.setItem('erp_inventory_v1', JSON.stringify(invData));
            }

            if (window.logSystemAction) {
                window.logSystemAction('Delivery', 'sales', `تم تأكيد تسليم الفاتورة ${invoiceNo} - المستلم: ${driver || '-'}`);
            }

            if (window.showToast) {
                window.showToast(`✓ تم تأكيد تسليم الفاتورة ${invoiceNo} بنجاح`, 'success', 4000);
            }

            loadData();
        }
    </script>
</body>
</html>
