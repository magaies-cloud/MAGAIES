<?php $page_title = 'المبيعات - المبيعات المحجوزة'; ?>
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
<script>
(function initScreenMaster7ThemeEarly() {
  var raw = localStorage.getItem('magaies_active_theme') ||
            localStorage.getItem('magaiees_global_theme') ||
            localStorage.getItem('magaiees_mobile_theme') ||
            localStorage.getItem('ui_theme') ||
            'warm_cream';
  var aliasMap = {
    'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream',
    'pure-platinum': 'ultra_white', 'offwhite': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white',
    'pearl-platinum': 'exec_gray', 'nordic-paper': 'warm_cream', 'solar_contrast': 'warm_cream',
    'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian', 'ocean-navy': 'midnight_obsidian', 'navy': 'midnight_obsidian',
    'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
    'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled', 'dark': 'pure_oled',
    'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight'
  };
  var activeKey = aliasMap[raw] || raw;
  var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
  if (!valid7.includes(activeKey)) activeKey = 'warm_cream';
  var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);
  document.documentElement.setAttribute('data-theme', activeKey);
  if (isLight) document.documentElement.classList.add('light-theme');
  else document.documentElement.classList.remove('light-theme');
  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) {
      document.body.setAttribute('data-theme', activeKey);
      if (isLight) document.body.classList.add('light-theme');
      else document.body.classList.remove('light-theme');
    }
  });
})();
</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المبيعات - المبيعات المحجوزة</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
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

        <!-- Unified Header -->
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- --- 1. TOP ACTION BAR --- -->
        <div class="top-action-bar">
            <div class="action-tabs" id="sale-type-tabs">
                <a href="direct-sales.php" data-href="direct-sales.php" onclick="window.location.href='direct-sales.php'" class="action-tab " style="text-decoration:none;">
                    <span class="tab-icon">📄</span><span class="tab-label">فواتير المبيعات</span>
                </a>
                <a href="reserved-sales.php" data-href="reserved-sales.php" onclick="window.location.href='reserved-sales.php'" class="action-tab active" style="text-decoration:none; background: rgba(245,158,11,0.2); border-color: #f59e0b; color: #f59e0b;">
                    🔖 حجوزات المبيعات (المعلقة)
                </a>
                <a href="sales-delivery.php" data-href="sales-delivery.php" onclick="window.location.href='sales-delivery.php'" class="action-tab" style="text-decoration:none; background: rgba(16,185,129,0.15); border-color: #10b981; color: #10b981;">
                    📤 بوابة تسليم المبيعات
                </a>
                <a href="sales-invoice.php" data-href="sales-invoice.php" onclick="window.location.href='sales-invoice.php'" class="action-tab " style="text-decoration:none;">
                    <span class="tab-icon">↩️</span><span class="tab-label">مرتجع المبيعات واسترداد البضائع</span>
                </a>
                <a href="customer-portfolio.php" data-href="customer-portfolio.php" onclick="window.location.href='customer-portfolio.php'" class="action-tab" style="text-decoration:none; color:#38bdf8;">
                    <span class="tab-icon">👥</span><span class="tab-label">محفظة العملاء 360°</span>
                </a>
            </div>
            <div class="top-bar-meta" style="display:flex; align-items:center; gap:10px;">
                <input type="date" class="ctrl-input" id="hdr-date" style="padding: 4px 8px; font-size: 0.85rem; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; color: var(--text-primary); cursor: pointer;" title="تاريخ الحجز">
                
                <div class="inv-nav" style="display:flex; align-items:center; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); padding: 4px;">
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadPreviousInvoice()" title="الحجز السابق">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <div class="inv-badge" id="inv-number" style="margin:0 5px;">000001</div>
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadNextInvoice()" title="الحجز التالي">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                </div>
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
            </div>
        </div>

        <!-- --- 1.5 ACTIVE RESERVATIONS COMMAND BAR & HUB DRAWER --- -->
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:linear-gradient(135deg,rgba(245,158,11,0.14),rgba(15,23,42,0.88)); border:1px solid rgba(251,191,36,0.38); border-radius:12px; padding:10px 16px; margin-bottom:14px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span style="font-weight:900; color:#fbbf24; font-size:0.95rem;">🔖 مركز إدارة حجوزات المبيعات ورادار انتهاء المهلة (AI Reservation Guard):</span>
                <button type="button" onclick="toggleReservationsHubDrawer()" style="background:rgba(245,158,11,0.22); border:1px solid #f59e0b; color:#fde68a; padding:6px 14px; border-radius:8px; font-weight:800; font-size:0.84rem; cursor:pointer; font-family:'Tajawal',sans-serif;">
                    📋 سجل الحجوزات النشطة (<span id="res-active-count">0</span>) — تحويل لفاتورة / فك الحجز
                </button>
                <button type="button" onclick="onSuccessNewInvoice()" style="background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; padding:6px 12px; border-radius:8px; font-weight:800; font-size:0.82rem; cursor:pointer; font-family:'Tajawal',sans-serif;">
                    ✨ حجز جديد
                </button>
            </div>
            <div id="res-kpi-summary" style="font-size:0.82rem; color:#cbd5e1; font-weight:700;"></div>
        </div>

        <div id="reservations-hub-drawer" style="display:none; background:rgba(15,23,42,0.95); border:1px solid rgba(251,191,36,0.4); border-radius:14px; padding:16px; margin-bottom:16px; box-shadow:0 12px 32px rgba(0,0,0,0.4);">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap;">
                <h3 style="margin:0; font-size:1rem; font-weight:900; color:#fbbf24;">📋 سجل حجوزات المبيعات (اعتمد التحويل لفاتورة مبيعات نهائية أو فك الحجز لإعادة الرصيد للمتاح)</h3>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input type="text" id="res-hub-search" class="ctrl-input" placeholder="🔍 ابحث برقم الحجز أو العميل أو الصنف..." oninput="renderReservationsHubTable()" style="width:280px; padding:6px 12px; font-size:0.84rem;">
                    <button type="button" class="btn btn-ghost" onclick="toggleReservationsHubDrawer()" style="padding:6px 12px;">✕ إغلاق</button>
                </div>
            </div>
            <div style="max-height:320px; overflow-y:auto; border-radius:10px; border:1px solid rgba(255,255,255,0.08);">
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem; text-align:right;">
                    <thead>
                        <tr style="background:rgba(255,255,255,0.06); color:#fbbf24; border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="padding:9px 12px;">رقم الحجز</th>
                            <th style="padding:9px 12px;">التاريخ / المهلة</th>
                            <th style="padding:9px 12px;">العميل</th>
                            <th style="padding:9px 12px;">الأصناف المحجوزة</th>
                            <th style="padding:9px 12px; text-align:center;">الإجمالي</th>
                            <th style="padding:9px 12px; text-align:center;">الحالة</th>
                            <th style="padding:9px 12px; text-align:center;">الإجراءات الفورية</th>
                        </tr>
                    </thead>
                    <tbody id="res-hub-tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- --- 2. HEADER SECTION (Two Columns) --- -->
        <div class="header-grid">
            <!-- Right Column: Basic Invoice Data -->
            <div class="panel" id="invoice-data-panel" style="z-index: 60;">
                <div class="panel-title">بيانات حجز المبيعات</div>
                <div class="header-form">
                    <div class="ctrl-group">
                        <label class="label">اسم العميل</label>
                        <div class="custom-dropdown">
                            <div class="custom-dropdown-input-wrap">
                                <input type="text" class="ctrl-input" id="hdr-customer-search" placeholder="ابحث عن اسم العميل..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown()">
                                <input type="hidden" id="hdr-customer" onchange="onCustomerChange()">
                                <div class="custom-dropdown-list" id="customer-dropdown-list">
                                </div>
                            </div>
                            <button class="btn-icon-add" onclick="openModal('add-customer-modal')" title="إضافة عميل جديد">+</button>
                        </div>
                    </div>
                    <div class="ctrl-group">
                        <label class="label">طريقة الدفع</label>
                        <select class="ctrl-input" id="hdr-payment-method" onchange="togglePaymentAccount()">
                            <option value="credit">آجل (ذمم مدينة)</option>
                            <option value="cash">نقدي مباشر</option>
                            <option value="bank">تحويل بنكي (شبكة)</option>
                            <option value="cheque">شيك</option>
                        </select>
                    </div>
                    <div class="ctrl-group" id="payment-account-group">
                        <label class="label">حساب الدفع (الخزنة / البنك)</label>
                        <select class="ctrl-input" id="hdr-payment-account">
                        </select>
                    </div>
                    <div class="ctrl-group" id="hdr-due-date-group" style="display: none;">
                        <label class="label" style="color: #fbbf24;">تاريخ الاستحقاق / انتهاء مهلة الحجز</label>
                        <input type="date" class="ctrl-input" id="hdr-due-date">
                    </div>
                    <div class="ctrl-group">
                        <label class="label">العملة</label>
                        <select class="ctrl-input system-currency-select" id="hdr-currency" onchange="document.getElementById('hdr-exchange-rate').disabled = (this.value === 'SDG'); document.getElementById('hdr-exchange-rate').value = (this.value === 'SDG') ? '1' : (window.SYSTEM_USD_RATE || 2700); recalcAll();">
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
                        <label class="label">البيان / شروط الحجز</label>
                        <input type="text" class="ctrl-input" id="hdr-description" placeholder="شروط الحجز، مهلة الاستلام، أو ملاحظات...">
                    </div>
                    <div class="ctrl-group">
                        <label class="label">مركز التكلفة (اختياري)</label>
                        <select class="ctrl-input" id="hdr-cost-center" onchange="onMainCostCenterChange()">
                            <option value="">-- اختر مركز تكلفة --</option>
                        </select>
                    </div>
                    <div class="ctrl-group">
                        <label class="label">المخزن المحجوز منه</label>
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
                <div class="panel-title">الأرصدة المالية والائتمانية للعميل</div>
                <div class="fin-grid">
                    <div class="fin-card">
                        <div class="fin-label">رصيد الدفتر الحالي</div>
                        <div class="fin-value" id="fin-ledger">0</div>
                    </div>
                    <div class="fin-card">
                        <div class="fin-label">الشيكات المؤجلة</div>
                        <div class="fin-value" id="fin-checks">0</div>
                    </div>
                    <div class="fin-card warn">
                        <div class="fin-label">إجمالي المديونية الحالية</div>
                        <div class="fin-value" id="fin-total-debt">0</div>
                    </div>
                    <div class="fin-card success">
                        <div class="fin-label">سقف ائتمان العميل (الحد الأقصى)</div>
                        <div class="fin-value" id="fin-credit-limit">0</div>
                    </div>
                    <div class="fin-card highlight">
                        <div class="fin-label">الرصيد الائتماني المتاح</div>
                        <div class="fin-value" id="fin-remaining">0</div>
                    </div>
                </div>
                <div class="fin-status" id="fin-status-msg">
                    <span class="status-dot neutral"></span> تم فتح شاشة حجز المبيعات — الكميات المحجوزة تُخصم من المتاح وتُحفظ باسم العميل
                </div>
            </div>
        </div>

        <!-- --- 3. DATA GRID (Invoice Items Table) --- -->
        <div class="panel" id="items-panel" style="z-index: 50;">
            <div class="panel-header-row" style="display: flex; align-items: center;">
                <div class="panel-title">بنود الحجز</div>
                
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
                    <span>+</span> إضافة صنف للحجز
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
                            <th class="th-qty">الكمية المحجوزة</th>
                            <th class="th-fc">السعر (أجنبي)</th>
                            <th class="th-price">السعر (محلي)</th>
                            <th class="th-actual" style="color:#a855f7;">فعلي</th>
                            <th class="th-reserved th-small" style="color:#f59e0b;">محجوز</th>
                            <th class="th-avail" style="color:#10b981;">متاح</th>
                            <th class="th-wh">مركز التكلفة</th>
                            <th class="th-wh">المخزن</th>
                            <th class="th-shila" style="width:7%; min-width:70px; text-align:center;">العدد الأساسي</th>
                            
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
                <div class="empty-icon">🔖</div>
                <p>لا توجد أصناف محجوزة حتى الآن</p>
                <span class="empty-hint">اضغط <strong>"+ إضافة صنف للحجز"</strong> أو ابحث بالباركود أعلاه</span>
            </div>
        </div>

        <!-- --- 4. BOTTOM SECTION (Totals & Adjustments) --- -->
        <div style="display: flex; gap: 14px; flex-wrap: wrap;">

            <!-- Left: Final Calculations (Full Width) -->
            <div class="panel totals-final-panel" id="totals-panel" style="flex: 1; min-width: 320px;">
                <div class="panel-title">الإجماليات والضريبة</div>
                <div class="totals-stack">
                    <div class="total-line">
                        <span class="tl-label">الإجمالي الفرعي للأصناف</span>
                        <span class="tl-value" id="t-invoice-total">0</span>
                    </div>
                    <div class="total-line editable">
                        <span class="tl-label">أجور العمالة والتحميل</span>
                        <input type="text" inputmode="decimal" class="tl-input" id="t-labor" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                    <div class="total-line editable">
                        <span class="tl-label">تكلفة الترحيل والنقل</span>
                        <input type="text" inputmode="decimal" class="tl-input" id="t-transport" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                    <div class="total-line sub">
                        <span class="tl-label">الإضافات الأخرى</span>
                        <span class="tl-value plus" id="t-additions">+ 0</span>
                    </div>
                    <div class="total-line sub">
                        <span class="tl-label">الضريبة المضافة</span>
                        <span class="tl-value tax" id="t-tax">+ 0</span>
                    </div>
                    <div class="total-line sub">
                        <span class="tl-label">الخصم الممنوح</span>
                        <span class="tl-value discount" id="t-discount">- 0</span>
                    </div>
                    <div class="total-line grand">
                        <span class="tl-label">الإجمالي النهائي للحجز</span>
                        <span class="tl-value" id="t-grand">0</span>
                    </div>
                </div>

                <div class="ctrl-group" style="margin-top:20px; border-top: 1px solid rgba(255,255,255,0.05); padding-top:15px;">
                    <label class="label">ملاحظات إضافية</label>
                    <textarea class="ctrl-input ctrl-textarea" id="inv-notes" rows="2" placeholder="اكتب أي ملاحظات إضافية هنا..."></textarea>
                    
                    <div style="margin-top: 15px; padding: 10px; background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3); border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">⚠️</span>
                        <p style="margin: 0; color: #fbbf24; font-size: 0.85rem; font-weight: bold;">ملاحظة الحجز: الكميات المضافة يتم حجزها فوراً من الرصيد المتاح في المخزن لحين تحويلها لفاتورة مبيعات أو فك الحجز.</p>
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
                <span class="ft-label">إجمالي الحجز:</span>
                <span class="ft-value" id="footer-grand">0</span>
            </div>
            <div class="footer-actions" style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="button" class="btn btn-ghost" style="color:#f87171; border:1px solid rgba(239,68,68,0.3);" onclick="confirmDeleteTransaction()">🗑️ إلغاء وفك الحجز</button>
                <button type="button" class="btn btn-ghost" onclick="onSuccessNewInvoice()">✨ تفريغ الحقول</button>
                <button type="button" class="btn btn-info" onclick="previewReservation()">👁️ معاينة الطباعة</button>
                <button type="button" class="btn btn-ghost" onclick="printReservation()">🖨️ طباعة إشعار حجز</button>
                <button type="button" class="btn btn-ghost" style="color:#34d399; border:1px solid rgba(16,185,129,0.35);" onclick="shareReservationWhatsApp()">📲 إشعار واتساب</button>
                <button type="button" class="btn btn-warning" onclick="saveDraft()">💾 حفظ كمسودة</button>
                <button type="button" class="btn btn-success" onclick="submitReservedInvoice()" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 15px rgba(245,158,11,0.4);">✓ اعتماد وحجز البضاعة</button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/reserved-sales.js?v=3"></script>

<script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
