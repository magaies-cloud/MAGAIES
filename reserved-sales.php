<?php $page_title = 'المبيعات - المبيعات المحجوزة'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المبيعات - المبيعات المحجوزة</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
    <style>
        /* Toast Notifications */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast {
            padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem;
            display:flex; align-items:center; gap:8px; pointer-events:auto;
            backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5);
            animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both;
            font-family:'Cairo',sans-serif; max-width:90vw;
        }
        .toast.success { background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.warning { background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.4); color:#fbbf24; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }


        /* Custom Dropdown for Customers */
        .custom-dropdown { position: relative; width: 100%; display: flex; gap: 6px; }
        .custom-dropdown-input-wrap { position: relative; flex: 1; }
        .custom-dropdown-list {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px;
            max-height: 300px; overflow-y: auto;
            background: var(--surface-color);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.5);
            z-index: 99999;
            display: none; flex-direction: column;
            min-width: 350px;
        }
        .custom-dropdown-list.show { display: flex; }
        /* Fix dropdown occlusion dynamically */
        td:has(.custom-dropdown-list.show),
        .m-card-full:has(.custom-dropdown-list.show),
        .custom-dropdown-input-wrap:has(.custom-dropdown-list.show) {
            z-index: 99999 !important;
        }
        .custom-dropdown-item {
            padding: 10px 12px; cursor: pointer; font-size: 0.85rem; font-weight: 600;
            color: var(--text-primary); border-bottom: 1px solid var(--glass-border);
            transition: background 0.2s;
        }
        .custom-dropdown-item:hover, .custom-dropdown-item.selected {
            background: rgba(56,189,248,0.15); color: var(--primary-accent);
        }
        .custom-dropdown-item:last-child { border-bottom: none; }
        .custom-dropdown-empty { padding: 10px 12px; font-size: 0.8rem; color: var(--text-muted); text-align: center; }
        
        .th-small { width: 65px !important; font-size: 0.75rem !important; }
        .td-small { width: 65px !important; font-size: 0.8rem !important; font-weight: 700; text-align: center; }
        
        .th-fc { width: 90px !important; color: #38bdf8 !important; }
    
        
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

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- --- Add Customer Modal --- -->
    <div class="modal-overlay" id="add-customer-modal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>إضافة عميل جديد</h3>
                <button class="modal-close" onclick="closeModal('add-customer-modal')">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="label">اسم العميل</label>
                    <input type="text" class="ctrl-input" id="new-cust-name" placeholder="اسم العميل...">
                </div>
                <div class="form-group">
                    <label class="label">رقم الهاتف</label>
                    <input type="tel" class="ctrl-input" id="new-cust-phone" placeholder="09XXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="label">سقف ائتمان العميل (الحد الأقصى)</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" id="new-cust-credit" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('add-customer-modal')">إلغاء الأمر</button>
                <button class="btn btn-primary" onclick="addNewCustomer()">✓ حفظ العميل</button>
            </div>
        </div>
    </div>

    <div class="pos-wrapper">

        <!-- --- BREADCRUMB NAV --- -->
        <div class="page-breadcrumb">
            <button onclick="window.location.href='dashboard-pro.php'" class="bc-link" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); cursor:pointer; margin-left:8px;" title="عودة للرئيسية">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
            <a data-href="dashboard-pro.php" onclick="window.location.href='dashboard-pro.php'" class="bc-link" title="المبيعات">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                المبيعات
            </a>
            <span class="bc-sep">/</span>
            <span class="bc-current" style="color: #38bdf8; font-weight: 700; text-shadow: 0 0 10px rgba(56,189,248,0.5);">المبيعات المحجوزة</span>
        </div>

        <!-- --- 1. TOP ACTION BAR --- -->
        <div class="top-action-bar">
            <div class="action-tabs" id="sale-type-tabs">
                <a data-href="direct-sales.php" onclick="window.location.href='direct-sales.php'" class="action-tab " style="text-decoration:none;">
                    <span class="tab-icon">📄</span><span class="tab-label">فواتير المبيعات</span>
                </a>
                <a data-href="reserved-sales.php" onclick="window.location.href='reserved-sales.php'" class="action-tab active" style="text-decoration:none; background: rgba(245,158,11,0.2); border-color: #f59e0b; color: #f59e0b;">
                    فواتير المبيعات المحجوزة (المعلقة)
                </a>
                <a data-href="sales-delivery.php" onclick="window.location.href='sales-delivery.php'" class="action-tab" style="text-decoration:none; background: rgba(16,185,129,0.15); border-color: #10b981; color: #10b981;">
                    تسليم المبيعات (المنصرف)
                </a>
                <a data-href="sales-invoice.php" onclick="window.location.href='sales-invoice.php'" class="action-tab " style="text-decoration:none;">
                    <span class="tab-icon">↩️</span><span class="tab-label">مرتجع مبيعات (مردود)</span>
                </a>
            </div>
            <div class="top-bar-meta" style="display:flex; align-items:center; gap:10px;">
                <!-- Date moved to top left -->
                <input type="date" class="ctrl-input" id="hdr-date" style="padding: 4px 8px; font-size: 0.85rem; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; color: var(--text-primary); cursor: pointer;" title="تاريخ الفاتورة">
                
                <div class="inv-nav" style="display:flex; align-items:center; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); padding: 4px;">
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadPreviousInvoice()" title="الفاتورة السابقة">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <div class="inv-badge" id="inv-number" style="margin:0 5px;">000001</div>
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadNextInvoice()" title="الفاتورة التالية">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                </div>
                <!-- Hidden input to preserve JS functionality -->
                <input type="hidden" id="hdr-inv-no">
                
                <button class="btn-icon" id="btn-gps" onclick="toggleGPS()" title="تسجيل موقع العميل (GPS Stamp)" style="padding:4px 8px; color:var(--text-muted); cursor:pointer; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.05); border-radius:8px; transition: 0.2s; display:flex; align-items:center; gap:5px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span id="gps-status" style="font-size: 0.75rem; font-weight: 700;">GPS معطل</span>
                </button>
                <input type="hidden" id="hdr-gps-lat">
                <input type="hidden" id="hdr-gps-lng">
                
                <button class="btn-icon" onclick="toggleFullScreen()" title="ملء الشاشة" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                </button>
                <button class="back-arrow-btn" onclick="window.location.href='dashboard-pro.php'" title="عودة للرئيسية">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5"></path>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        <!-- --- 2. HEADER SECTION (Two Columns) --- -->
        <div class="header-grid">
            <!-- Right Column: Basic Invoice Data -->
            <div class="panel" id="invoice-data-panel" style="z-index: 60;">
                <div class="panel-title">البيانات الأساسية</div>
                <div class="header-form">
                    <!-- Invoice No and Date moved to top left bar -->
                    <div class="ctrl-group">
                        <label class="label">اسم العميل</label>
                        <div class="custom-dropdown">
                            <div class="custom-dropdown-input-wrap">
                                <input type="text" class="ctrl-input" id="hdr-customer-search" placeholder="ابحث عن اسم العميل..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown()">
                                <input type="hidden" id="hdr-customer" onchange="onCustomerChange()">
                                <div class="custom-dropdown-list" id="customer-dropdown-list">
                                    <!-- Populated via JS -->
                                </div>
                            </div>
                            <button class="btn-icon-add" onclick="openModal('add-customer-modal')" title="إضافة عميل جديد">+</button>
                        </div>
                    </div>
                    <div class="ctrl-group">
                        <label class="label">طريقة الدفع</label>
                        <select class="ctrl-input" id="hdr-payment-method" onchange="togglePaymentAccount()">
                            <option value="cash">نقدي مباشر</option>
                            <option value="credit">آجل (ذمم مدينة)</option>
                            <option value="bank">تحويل بنكي (شبكة)</option>
                            <option value="cheque">شيك</option>
                        </select>
                    </div>
                    <div class="ctrl-group" id="payment-account-group">
                        <label class="label">تاريخ الدفع / الاستحقاق</label>
                        <select class="ctrl-input" id="hdr-payment-account">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="ctrl-group" id="hdr-due-date-group" style="display: none;">
                        <label class="label" style="color: var(--danger-color);">سقف ائتمان العميل (الحد الأقصى)</label>
                        <input type="date" class="ctrl-input" id="hdr-due-date">
                    </div>
                    <div class="ctrl-group">
                        <label class="label">العملة</label>
                        <select class="ctrl-input system-currency-select" id="hdr-currency" onchange="document.getElementById('hdr-exchange-rate').disabled = (this.value === 'SDG'); document.getElementById('hdr-exchange-rate').value = (this.value === 'SDG') ? '1' : ''; recalcAll();"
                            <option value="SDG">جنيه سوداني محلي (SDG)</option>
                            <option value="USD">دولار أمريكي أجنبي (USD)</option>
                            <option value="SAR">ريال سعودي أجنبي (SAR)</option>
                            <option value="AED">درهم إماراتي أجنبي (AED)</option>
                            <option value="EGP">جنيه مصري أجنبي (EGP)</option>
                        </select>
                    </div>
                    <div class="ctrl-group">
                        <label class="label">سعر الصرف (مقابل المحلي)</label>
                        <input type="number" class="ctrl-input" id="hdr-exchange-rate" value="1" disabled placeholder="سعر الصرف" oninput="recalcAll()">
                    </div>
                    <div class="ctrl-group ctrl-full">
                        <label class="label">البيان / الوصف</label>
                        <input type="text" class="ctrl-input" id="hdr-description" placeholder="شرح الفاتورة...">
                    </div>
                    <div class="ctrl-group">
                        <label class="label">مركز التكلفة (اختياري)</label>
                        <select class="ctrl-input" id="hdr-cost-center" onchange="onMainCostCenterChange()">
                            <option value="">-- اختر مركز تكلفة --</option>
                        </select>
                    </div>
                    <div class="ctrl-group">
                        <label class="label">الضريبة الإضافية</label>
                        <select class="ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange()">
                        </select>
                    </div>
                    <div class="ctrl-group">
                        <label class="label">المستند المرجعي (رقم الحجز)</label>
                        <input type="text" class="ctrl-input" id="hdr-ref-doc" placeholder="رقم الحجز..." readonly style="color: #fbbf24; font-weight: bold; background: rgba(0,0,0,0.3);">
                    </div>
                </div>
            </div>

            <!-- Left Column: Customer Financial Status -->
            <div class="panel financial-panel" id="financial-panel">
                <div class="panel-title">الأرصدة المالية للعميل</div>
                <div class="fin-grid">
                    <div class="fin-card">
                        <div class="fin-label">رصيد العميل</div>
                        <div class="fin-value" id="fin-ledger">0</div>
                    </div>
                    <div class="fin-card">
                        <div class="fin-label">رصيد العميل</div>
                        <div class="fin-value" id="fin-checks">0</div>
                    </div>
                    <div class="fin-card warn">
                        <div class="fin-label">الضريبة الإضافية</div>
                        <div class="fin-value" id="fin-total-debt">0</div>
                    </div>
                    <div class="fin-card success">
                        <div class="fin-label">سقف ائتمان العميل (الحد الأقصى)</div>
                        <div class="fin-value" id="fin-credit-limit">0</div>
                    </div>
                    <div class="fin-card highlight">
                        <div class="fin-label">الرصيد المتاح</div>
                        <div class="fin-value" id="fin-remaining">0</div>
                    </div>
                </div>
                <div class="fin-status" id="fin-status-msg">
                    <span class="status-dot neutral"></span> تم فتح شاشة الحجز
                </div>
            </div>
        </div>

        <!-- --- 3. DATA GRID (Invoice Items Table) --- -->
        <div class="panel" id="items-panel" style="z-index: 50;">
            <div class="panel-header-row" style="display: flex; align-items: center;">
                <div class="panel-title">بنود الفاتورة</div>
                
                <!-- Quick Add Item Box -->
                <div class="quick-add-box" style="flex: 1; max-width: 350px; margin-right: 20px; position: relative; z-index: 99999;">
                    <input type="text" class="g-input" id="quick-item-search" placeholder="ابحث بالاسم، الكود، أو باركود الأصناف..."
                        style="background: rgba(56,189,248,0.1); border-color: rgba(56,189,248,0.4); color: var(--text-primary);"
                        onfocus="this.select(); showQuickItemDropdown()" 
                        onblur="hideQuickItemDropdownDelayed()" 
                        oninput="filterQuickItemDropdown()" autocomplete="new-password">
                    <div class="custom-dropdown-list" id="quick-item-dropdown-list" style="text-align:right;">
                        <!-- populated by JS -->
                    </div>
                </div>

                <button class="btn btn-primary btn-sm" onclick="addRow()" id="add-row-btn" style="margin-right: auto;">
                    <span>+</span> إضافة صنف
                </button>
            </div>

            <!-- Desktop Table -->
            <div class="grid-scroll-wrap">
                <table class="pos-grid" id="pos-grid">
                    <thead>
                        <tr>
                            <th class="th-num">#</th>
                            <th class="th-small">كود الصنف</th>
                            <th class="th-item">اسم الصنف</th>
                            <th class="th-unit">الوحدة</th>
                            <th class="th-factor">المعامل</th>
                            <th class="th-qty">الكمية المطلوبة</th>
                            <th class="th-fc">السعر (أجنبي)</th>
                            <th class="th-price">السعر (محلي)</th>
                            <th class="th-actual" style="color:#a855f7;">فعلي</th>
                            <th class="th-reserved th-small" style="color:#f59e0b;">محجوز</th>
                            <th class="th-avail" style="color:#10b981;">متاح</th>
                            <th class="th-wh">تاريخ الصلاحية</th>
                            <th class="th-wh">المخزن</th>
                            <th class="th-shila" style="width:7%; min-width:70px; text-align:center;">الشيلة X</th>
                            
                            <th class="th-fc">الإجمالي (أجنبي)</th>
                            <th class="th-total">الإجمالي (محلي)</th>
                            <th class="th-del"></th>
                        </tr>
                    </thead>
                    <tbody id="grid-body"></tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="mobile-cards" id="mobile-cards"></div>

            <!-- Empty State -->
            <div class="empty-state" id="empty-state">
                <div class="empty-icon">🛒</div>
                <p>لا توجد أصناف مضافة حتى الآن</p>
                <span class="empty-hint">اضغط <strong>"+ إضافة"</strong> أو <kbd>Ctrl+N</kbd></span>
            </div>
        </div>

        <!-- --- 4. BOTTOM SECTION (Totals & Adjustments) --- -->
        <div style="display: flex; gap: 14px; flex-wrap: wrap;">

            <!-- Left: Final Calculations (Full Width) -->
            <div class="panel totals-final-panel" id="totals-panel" style="flex: 1; min-width: 320px;">
                <div class="panel-title">الإجماليات والضريبة</div>
                <div class="totals-stack">
                    <div class="total-line">
                        <span class="tl-label">الإجمالي الفرعي</span>
                        <span class="tl-value" id="t-invoice-total">0</span>
                    </div>
                    <div class="total-line editable">
                        <span class="tl-label">قيمة الخصم / الإضافات</span>
                        <input type="text" inputmode="decimal" class="tl-input" id="t-labor" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                    <div class="total-line editable">
                        <span class="tl-label">نسبة الخصم / الإضافات</span>
                        <input type="text" inputmode="decimal" class="tl-input" id="t-transport" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                    <div class="total-line sub">
                        <span class="tl-label">الإجمالي</span>
                        <span class="tl-value plus" id="t-additions">+ 0</span>
                    </div>
                    <div class="total-line sub">
                        <span class="tl-label">الضريبة</span>
                        <span class="tl-value tax" id="t-tax">+ 0</span>
                    </div>
                    <div class="total-line sub">
                        <span class="tl-label">الصافي</span>
                        <span class="tl-value discount" id="t-discount">- 0</span>
                    </div>
                    <div class="total-line grand">
                        <span class="tl-label">الإجمالي النهائي</span>
                        <span class="tl-value" id="t-grand">0</span>
                    </div>
                </div>

                <div class="ctrl-group" style="margin-top:20px; border-top: 1px solid rgba(255,255,255,0.05); padding-top:15px;">
                    <label class="label">ملاحظات إضافية</label>
                    <textarea class="ctrl-input ctrl-textarea" id="inv-notes" rows="2" placeholder="اكتب أي ملاحظات إضافية هنا..."></textarea>
                    
                    <div style="margin-top: 15px; padding: 10px; background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3); border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">⚠️</span>
                        <p style="margin: 0; color: #fbbf24; font-size: 0.85rem; font-weight: bold;">ملاحظة الحجز: الكميات المضافة سيتم حجزها من المخزن فور حفظ الفاتورة كمحجوزة.</p>
                    </div>
                </div>
            </div>

            <!-- Hidden Adjustments (kept for JS compatibility) -->
            <div id="adjustments-panel" style="display: none;">
                <input type="text" id="discount-amt" value="0">
                <input type="number" id="discount-pct" value="0">
                <input type="text" id="tax-amt" value="0">
                <input type="number" id="tax-pct" value="0">
                <input type="text" id="addition-amt" value="0">
                <input type="number" id="addition-pct" value="0">
            </div>
        </div>

        <!-- --- STICKY FOOTER --- -->
        <div class="sticky-footer">
            <div class="footer-total">
                <span class="ft-label">المجموع:</span>
                <span class="ft-value" id="footer-grand">0</span>
            </div>
            <div class="footer-actions">
                <button class="btn btn-ghost" onclick="onSuccessNewInvoice()">إلغاء الأمر</button>
                <button class="btn btn-info" onclick="previewReservation()">معاينة الطباعة</button>
                <button class="btn btn-ghost" onclick="printReservation()">طباعة فاتورة مبيعات حجز</button>
                <button class="btn btn-warning" onclick="editReservation()">حفظ كمسودة (مبيعات محجوزة)</button>
                <button class="btn btn-success" onclick="submitReservedInvoice()" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 15px rgba(245,158,11,0.4);">حفظ فاتورة المبيعات نهائياً</button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054969.46011"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/reserved-sales.js?v=3"></script>
</body>
</html>
