<?php $page_title = 'إدارة الشركاء 2026 - مقاييس (MAGAIEES)'; ?>
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
    <title>إدارة الشركاء 2026 - مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
        <style>
        .container { padding: 20px; padding-bottom: 100px; }
        
        .tabs {
            display: flex;
            background: var(--surface-color);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--glass-border);
        }
        .tab-btn {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s;
        }
        .tab-btn.active {
            background: var(--primary-accent);
            color: white;
        }

        .form-group { margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--text-secondary); }
        .form-input, .form-select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            background: var(--bg-color);
            color: var(--text-primary);
        }
        .btn-add {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: var(--success-color);
            color: white;
            font-weight: 800;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        .partners-list { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
        .partner-card {
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 10px;
            animation: fadeIn 0.3s ease-out;
        }
        .partner-name { font-weight: 800; font-size: 1.1rem; }
        .partner-type { font-size: 0.8rem; color: white; padding: 3px 8px; border-radius: 5px; }
        .type-CUSTOMER { background: #38bdf8; }
        .type-SUPPLIER { background: #fbbf24; color: #1e293b; }
        .type-EMPLOYEE { background: #34d399; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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


    <nav class="navbar glass-panel">
        <button class="back-btn" onclick="history.back()">🔙</button>
        <h1 class="brand-title">إدارة جهات الاتصال</h1>
        <div style="width: 30px;"></div>
    </nav>

    <div class="container">
        <!-- Add New Form -->
        <div class="glass-panel" style="padding: 20px; border-radius: 15px;">
            <h3 style="margin-top: 0; color: var(--primary-accent);">إضافة شريك جديد</h3>
            
            <div class="form-group">
                <label class="form-label">الاسم</label>
                <input type="text" class="form-input" id="p-name" placeholder="مثال: أحمد (عميل)، شركة كذا...">
            </div>
            
            <div class="form-group">
                <label class="form-label">النوع</label>
                <select class="form-select" id="p-type">
                    <option value="CUSTOMER">عميل (مبيعات)</option>
                    <option value="SUPPLIER">مورد (مشتريات/مصروفات)</option>
                    <option value="EMPLOYEE">موظف</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">رقم الهاتف (اختياري)</label>
                <input type="text" class="form-input" id="p-phone">
            </div>

            <button class="btn-add" onclick="addPartner()">+ حفظ الشريك</button>
        </div>

        <!-- Filters -->
        <div class="tabs" style="margin-top: 25px;">
            <button class="tab-btn active" onclick="loadPartners('')">الكل</button>
            <button class="tab-btn" onclick="loadPartners('CUSTOMER')">العملاء</button>
            <button class="tab-btn" onclick="loadPartners('SUPPLIER')">الموردون</button>
        </div>

        <!-- List -->
        <div class="partners-list" id="list-container">
            <!-- Populated via JS -->
            <div style="text-align:center; color:var(--text-secondary);">جاري التحميل...</div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        let localPartners = [];

        function loadFromStorage() {
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            let suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
            if (!suppliers || suppliers.length === 0) {
                suppliers = [
    { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, creditLimit: 50000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, creditLimit: 30000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, creditLimit: 20000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, creditLimit: 100000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, creditLimit: 15000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, creditLimit: 25000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, creditLimit: 40000000, status: 'active', securityLevel: 1 }
];
                try { localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers)); } catch(e){}
            }

            localPartners = [];

            customers.forEach(c => {
                localPartners.push({
                    id: c.id,
                    name: c.name || c.customerName,
                    type: 'CUSTOMER',
                    phone: c.phone || c.mobile || '',
                    balance: parseFloat(c.currentBalance || 0),
                    storageKey: 'erp_customers_v1',
                    raw: c
                });
            });

            suppliers.forEach(s => {
                localPartners.push({
                    id: s.id,
                    name: s.name || s.supplierName,
                    type: 'SUPPLIER',
                    phone: s.phone || s.mobile || '',
                    balance: parseFloat(s.currentBalance || 0),
                    storageKey: 'erp_suppliers_v1',
                    raw: s
                });
            });
        }

        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) {
                window.location.href = 'login.php';
            }
            loadFromStorage();
            loadPartners('');
        });

        if (window.APP_READY) {
            loadFromStorage();
            loadPartners('');
        }

        function loadPartners(filterType) {
            // Update active tab UI
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            const currentEvt = (typeof event !== 'undefined') ? event : (window.event || null);
            if(currentEvt && currentEvt.target && currentEvt.target.classList) {
                 event.target.classList.add('active');
            } else {
                 document.querySelector('.tab-btn').classList.add('active');
            }

            const container = document.getElementById('list-container');
            container.innerHTML = '';

            let filtered = localPartners;
            if (filterType) {
                filtered = localPartners.filter(p => p.type === filterType);
            }

            if (filtered.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding: 40px; color:var(--text-secondary);"><div style="font-size:3rem; margin-bottom:10px;">📭</div>لا توجد بيانات</div>';
                return;
            }

            // Sort by name
            filtered.sort((a, b) => (a.name || '').localeCompare(b.name || ''));

            filtered.forEach(p => {
                const typeLabel = p.type === 'CUSTOMER' ? 'عميل' : p.type === 'SUPPLIER' ? 'مورد' : 'موظف';
                const balColor = p.balance > 0 ? '#f87171' : p.balance < 0 ? '#34d399' : 'var(--text-secondary)';
                const balLabel = p.balance !== 0 ? `<div style="font-size: 0.8rem; color: ${balColor}; margin-top:3px; font-weight:700;">الرصيد: ${Math.abs(p.balance).toLocaleString()} ${p.balance > 0 ? '(عليه)' : '(له)'}</div>` : '';

                container.innerHTML += `
                    <div class="partner-card glass-panel" style="cursor:pointer;" onclick="editPartner('${p.id}', '${p.type}')">
                        <div>
                            <div class="partner-name">${p.name}</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top:5px;">📱 ${p.phone || 'لا يوجد هاتف'}</div>
                            ${balLabel}
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="partner-type type-${p.type}">${typeLabel}</span>
                            <button onclick="event.stopPropagation(); deletePartner('${p.id}', '${p.type}')" style="background:none; border:none; font-size:1.2rem; cursor:pointer; opacity:0.5;" title="حذف">🗑️</button>
                        </div>
                    </div>
                `;
            });

            // Add count
            container.insertAdjacentHTML('beforeend', `<div style="text-align:center; padding:10px; color:var(--text-secondary); font-size:0.85rem;">إجمالي: ${filtered.length} شريك</div>`);
        }

        async function addPartner() {
            const name = document.getElementById('p-name').value.trim();
            const type = document.getElementById('p-type').value;
            const phone = document.getElementById('p-phone').value.trim();

            if (!name) {
                alert('يرجى إدخال الاسم');
                return;
            }

            // Duplicate check
            const allCustomers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const allSuppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
            const allNames = [...allCustomers, ...allSuppliers].filter(x => !x.deleted).map(x => (x.name || x.customerName || x.supplierName || '').trim().toLowerCase());
            if (allNames.includes(name.toLowerCase())) {
                alert('⚠️ يوجد شريك بنفس الاسم بالفعل!');
                return;
            }

            const newPartner = {
                id: type === 'CUSTOMER' ? 'cust-' + Date.now() : 'supp-' + Date.now(),
                name: name,
                phone: phone,
                mobile: phone,
                currentBalance: 0,
                openingDebit: 0,
                openingCredit: 0,
                createdAt: new Date().toISOString()
            };

            if (type === 'CUSTOMER') {
                newPartner.customerName = name;
                allCustomers.push(newPartner);
                localStorage.setItem('erp_customers_v1', JSON.stringify(allCustomers));
                if (window.saveToDB) window.saveToDB('customers', allCustomers);
            } else {
                newPartner.supplierName = name;
                allSuppliers.push(newPartner);
                localStorage.setItem('erp_suppliers_v1', JSON.stringify(allSuppliers));
                if (window.saveToDB) window.saveToDB('suppliers', allSuppliers);
            }

            alert('✅ تمت إضافة الشريك بنجاح!');
            document.getElementById('p-name').value = '';
            document.getElementById('p-phone').value = '';
            
            loadFromStorage();
            document.querySelector('.tab-btn.active').click();
        }

        function deletePartner(id, type) {
            if (!confirm('هل تريد حذف هذا الشريك نهائياً؟')) return;

            const key = type === 'CUSTOMER' ? 'erp_customers_v1' : 'erp_suppliers_v1';
            const data = JSON.parse(localStorage.getItem(key)) || [];
            const item = data.find(x => String(x.id) === String(id));
            if (item) {
                item.deleted = true;
                localStorage.setItem(key, JSON.stringify(data));
                if (window.saveToDB) window.saveToDB(key.replace('erp_', '').replace('_v1', ''), data);
            }

            loadFromStorage();
            document.querySelector('.tab-btn.active').click();
        }

        function editPartner(id, type) {
            const partner = localPartners.find(p => String(p.id) === String(id) && p.type === type);
            if (!partner) return;

            const newName = prompt('تعديل الاسم:', partner.name);
            if (newName === null) return;
            if (!newName.trim()) { alert('الاسم لا يمكن أن يكون فارغاً'); return; }

            const newPhone = prompt('تعديل رقم الهاتف:', partner.phone);
            if (newPhone === null) return;

            const key = type === 'CUSTOMER' ? 'erp_customers_v1' : 'erp_suppliers_v1';
            const data = JSON.parse(localStorage.getItem(key)) || [];
            const item = data.find(x => String(x.id) === String(id));
            if (item) {
                item.name = newName.trim();
                if (type === 'CUSTOMER') item.customerName = newName.trim();
                else item.supplierName = newName.trim();
                item.phone = newPhone.trim();
                item.mobile = newPhone.trim();
                localStorage.setItem(key, JSON.stringify(data));
                if (window.saveToDB) window.saveToDB(key.replace('erp_', '').replace('_v1', ''), data);
            }

            loadFromStorage();
            document.querySelector('.tab-btn.active').click();
        }
    </script>

<script src="js/magaiees_back.js?v=2026_back_v1" defer></script>
</body>
</html>

