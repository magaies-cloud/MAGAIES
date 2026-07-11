<?php $page_title = 'سند قبض نقدية وشيك'; ?>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سند قبض نقدية وشيك | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <style>
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
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Green Theme for Receipts */
        .top-action-bar { background: rgba(16, 185, 129, 0.1); border-bottom: 1px solid rgba(16, 185, 129, 0.2); }
        .action-tab.active { background: #10b981; color: #fff; border-color: #10b981; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .form-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-primary); padding: 12px 15px; border-radius: 8px;
            font-family: inherit; font-size: 1rem; transition: all 0.2s;
        }
        .form-input:focus { border-color: #10b981; background: rgba(0,0,0,0.4); outline: none; }
        .form-input.amount { color: #34d399; font-weight: 700; font-family: monospace; font-size: 1.1rem; }
        
        .form-section-title { 
            font-size: 1.1rem; font-weight: 700; color: #34d399; 
            margin-bottom: 15px; display: flex; align-items: center; gap: 20px; 
            border-bottom: 1px solid rgba(16,185,129,0.2); padding-bottom: 5px; margin-bottom: 15px; 
        }

        .balance-summary {
            background: rgba(249, 115, 22, 0.1);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 8px;
            display: flex;
            padding: 8px 15px;
            gap: 20px;
            font-size: 1rem;
            color: #fdba74;
            float: left; /* Left align for RTL */
            margin-top: -5px;
        }
        .bal-item strong { color: #f97316; margin-right: 5px; }

        /* Custom Radio Buttons */
        .radio-group {
            display: flex; gap: 20px; align-items: center; padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 15px;
        }
        .radio-label {
            display: flex; align-items: center; gap: 20px; cursor: pointer;
            font-weight: 600; color: var(--text-primary);
        }
        .radio-label input[type="radio"] {
            width: 18px; height: 18px; cursor: pointer;
            accent-color: #10b981;
        }

        .dynamic-section { display: none; background: rgba(0,0,0,0.1); padding: 20px; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.1); margin-bottom: 15px; }
        .dynamic-section.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .checkbox-label {
            display: flex; align-items: center; gap: 20px; cursor: pointer; font-size: 0.9rem; margin-top: 10px;
        }
        .checkbox-label input { width: 16px; height: 16px; accent-color: #10b981; }
    
        /* Dashboard Layout CSS */
        .dashboard-layout {
            min-height: calc(100vh - 220px);
            align-items: stretch;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 15px;
        }
        @media (max-width: 1024px) {
            .dashboard-layout { grid-template-columns: 1fr; }
        }
        .main-column { display: flex; flex-direction: column; gap: 20px; flex: 1; }
        .main-column > .glass-panel { flex: 1; display: flex; flex-direction: column; justify-content: space-evenly; }
        .sidebar-column { display: flex; flex-direction: column; gap: 20px; }
        
        .balance-summary-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
        }
        .balance-summary-card h4 { color: #38bdf8; margin: 0 0 15px 0; font-size: 1.1rem; display: flex; align-items: center; gap: 20px;}
        .balance-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center; }
        .balance-box { background: rgba(0,0,0,0.3); padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); }
        .balance-box span { display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 15px; }
        .balance-box strong { display: block; font-size: 1.1rem; font-family: monospace; }
        .balance-box.debt strong { color: #f87171; }
        .balance-box.credit strong { color: #34d399; }
        .balance-box.checks strong { color: #fbbf24; }
    
        
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

    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="max-width: 1400px; width: 95%; margin: 0 auto; padding-bottom: 60px;">
        <!-- BREADCRUMB -->
        <div class="page-breadcrumb">
            <button onclick="if(document.referrer){history.back();}else{window.location.href='dashboard-pro.php';}" class="bc-link" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); cursor:pointer; margin-left:8px;" title="الرجوع للشاشة السابقة">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
            <a data-href="dashboard-pro.php" onclick="if(document.referrer){history.back();}else{window.location.href='dashboard-pro.php';}" class="bc-link" title="الرجوع للشاشة السابقة">الرئيسية</a>
            <span class="bc-sep">‹</span>
            <span class="bc-current">سند قبض مفرد</span>
        </div>

        <!-- HEADER -->
        <div class="top-action-bar">
            <div class="action-tabs">
                <button class="action-tab active">
                    <span class="tab-icon">💵</span><span class="tab-label">سند قبض نقدية وشيك</span>
                </button>
            </div>
            <div class="top-bar-meta" style="display:flex; align-items:center; gap:10px;">
                <div class="inv-nav" style="display:flex; align-items:center; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); padding: 2px;">
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadPreviousVoucher()" title="السند السابق">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <div class="inv-badge" id="voucher-number" style="margin:0 10px; color:#34d399; min-width:60px; text-align:center; font-size: 1.1rem;">000001</div>
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadNextVoucher()" title="السند التالي">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN FORM -->
        
        <!-- MAIN DASHBOARD LAYOUT -->
        <div class="dashboard-layout">
            
            <!-- MAIN COLUMN -->
            <div class="main-column">
                <div class="glass-panel" style="padding: 20px;">
                    <!-- SECTION 1: PAYER & BASIC INFO -->
            <div style="margin-bottom: 15px;">
                
                
                <h3 class="form-section-title">👤 البيانات الأساسية للدافع (العميل)</h3>
                
                <div class="form-grid">
                    <div>
                        <label class="label">تاريخ القبض</label>
                        <input type="date" class="form-input" id="hdr-date">
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <label class="label" style="margin-bottom: 0;">من حساب (الدافع)</label>
                            <button type="button" onclick="openGeneralLedgerForAccount(document.getElementById('from-account').value)" style="background:none; border:none; color:#38bdf8; font-size:0.85rem; cursor:pointer; font-family:inherit; font-weight:700; display:flex; align-items:center; gap:4px;" title="عرض كشف حساب العميل المحدد">📄 كشف حساب</button>
                        </div>
                        <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="from-account">
                                    <input type="text" class="form-input input-glow-focus hover-glow" id="from-account-search" placeholder="ابحث عن حساب الاستلام..." onfocus="showAccountDropdown('from')" onblur="hideAccountDropdownDelayed('from')" oninput="filterAccountDropdown('from')" style="width: 100%; padding: 12px 15px 12px 95px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; border: 1px solid rgba(16, 185, 129, 0.5); background: rgba(0,0,0,0.3); color: #fff;">
                                    <button type="button" id="from-account-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('from-account').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.8rem; padding: 4px 8px; height: 32px; display: none;" title="دفتر الأستاذ العام">📖 دفتر الأستاذ</button>
                                    <div class="custom-dropdown-list" id="from-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px;"></div>
                                </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="label">المبلغ (دائن محلي)</label>
                        <input type="text" class="form-input amount" id="amount-local" value="0" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv('local')" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                    <div>
                        <label class="label">العملة</label>
                        <select class="form-input system-currency-select" id="currency" onchange="updateCurrency()"
                            <option value="SDG">جنيه (SDG)</option>
                            <option value="USD">دولار (USD)</option>
                            <option value="AED">درهم (AED)</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">التعادل</label>
                        <input type="text" class="form-input" id="exchange-rate" value="1.0" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv('rate')" readonly>
                    </div>
                    <div>
                        <label class="label">المعادل</label>
                        <input type="text" class="form-input" id="amount-foreign" value="0" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv('foreign')" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div>
                        <label class="label">البيان</label>
                        <input type="text" class="form-input" id="from-statement" placeholder="مثال: سداد دفعة من الحساب...">
                    </div>
                    <div>
                        <label class="label">مندوب التحصيل (أو المستلم الفعلي)</label>
                        <input type="text" class="form-input" id="collector-name" placeholder="اسم المندوب...">
                    </div>
                </div>
            </div>

            
                </div>
            </div>

            <!-- SIDEBAR COLUMN -->
            <div class="sidebar-column">
                <!-- NEW BALANCE CARD -->
                <div class="balance-summary-card">
                    <h4>📊 ملخص العميل المختار</h4>
                    <div class="balance-grid">
                        <div class="balance-box debt">
                            <span>المديونية</span>
                            <strong id="bal-debt">0</strong>
                        </div>
                        <div class="balance-box checks">
                            <span>شيكات تحت التحصيل</span>
                            <strong id="bal-checks">0</strong>
                        </div>
                        <div class="balance-box credit">
                            <span>رصيد دائن</span>
                            <strong id="bal-credit">0</strong>
                        </div>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 20px;">
                    <!-- SECTION 2: RECEIVE TO -->
            <div style="margin-bottom: 15px;">
                <h3 class="form-section-title" style="color: #38bdf8; border-color: rgba(56,189,248,0.2);">🏦 استلام إلى (الصندوق/البنك)</h3>
                <div class="form-grid">
                    <div>
                        <label class="label">إلى حساب (الخزنة / البنك المستلم)</label>
                        <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="to-account">
                                    <input type="text" class="form-input input-glow-focus hover-glow" id="to-account-search" placeholder="ابحث عن حساب الإيداع..." onfocus="showAccountDropdown('to')" onblur="hideAccountDropdownDelayed('to')" oninput="filterAccountDropdown('to')" style="width: 100%; padding: 12px 15px 12px 95px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; border: 1px solid rgba(239, 68, 68, 0.5); background: rgba(0,0,0,0.3); color: #fff;">
                                    <button type="button" id="to-account-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('to-account').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.8rem; padding: 4px 8px; height: 32px; display: none;" title="دفتر الأستاذ العام">📖 دفتر الأستاذ</button>
                                    <div class="custom-dropdown-list" id="to-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px;"></div>
                                </div>
                    </div>
                    <div>
                        <label class="label">البيان</label>
                        <input type="text" class="form-input" id="to-statement" placeholder="بيان الخزنة...">
                    </div>
                </div>
            </div>

            
                </div>

                <div class="glass-panel" style="padding: 20px;">
                    <!-- SECTION 3: PAYMENT METHOD -->
            <div>
                <h3 class="form-section-title" style="color: #fbbf24; border-color: rgba(251,191,36,0.2);">💳 طريقة القبض</h3>
                
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="payment_method" value="cash" checked onchange="togglePaymentMethod()">
                        نقدي
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="payment_method" value="check" onchange="togglePaymentMethod()">
                        شيك
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="payment_method" value="mobile" onchange="togglePaymentMethod()">
                        حوالة جوال
                    </label>
                </div>

                <!-- MOBILE TRANSFER FIELDS -->
                <div id="section-mobile" class="dynamic-section">
                    <div class="form-grid">
                        <div>
                            <label class="label">رقم تحويل الجوال</label>
                            <input type="text" class="form-input" id="mobile-transfer-no" placeholder="رقم العملية...">
                        </div>
                        <div>
                            <label class="label">ملاحظة</label>
                            <input type="text" class="form-input" id="mobile-note" placeholder="ملاحظات التحويل...">
                        </div>
                    </div>
                </div>

                <!-- CHECK FIELDS -->
                <div id="section-check" class="dynamic-section">
                    <div class="form-grid">
                        <div>
                            <label class="label">رقم الشيك</label>
                            <input type="text" class="form-input" id="check-no" placeholder="000000">
                        </div>
                        <div>
                            <label class="label">تاريخ الشيك</label>
                            <input type="date" class="form-input" id="check-date">
                        </div>
                        <div>
                            <label class="label">تاريخ الاستحقاق</label>
                            <input type="date" class="form-input" id="check-due-date">
                        </div>
                        <div>
                            <label class="label">المرجع</label>
                            <input type="text" class="form-input" id="check-ref" placeholder="اسم المرجع...">
                        </div>
                        <div>
                            <label class="label">رقم المرجع</label>
                            <input type="text" class="form-input" id="check-ref-no" placeholder="رقم المرجع...">
                        </div>
                        <div>
                            <label class="label">فرع البنك</label>
                            <input type="text" class="form-input" id="check-branch" placeholder="الفرع...">
                        </div>
                    </div>
                    <label class="checkbox-label">
                        <input type="checkbox" id="check-settle-local">
                        يسوى للبنك المحلي
                    </label>
                </div>
            </div>

        
                </div>
            </div>
            
        </div>


        <!-- STICKY FOOTER -->
        <div class="sticky-footer" style="background: rgba(15,23,42,0.95); border-top: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px 12px 0 0; padding: 20px; position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;">
            <div class="footer-actions" style="width: 100%; justify-content: center; max-width: 1400px; margin: 0 auto; gap: 15px;">
                <button class="btn btn-ghost" style="color:#f87171; border-color:rgba(239,68,68,0.3);" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة">🚪 خروج</button>
                <button class="btn btn-ghost" onclick="resetVoucher()">📄 جديد</button>
                <button class="btn btn-ghost" style="color: #f87171; border-color: rgba(239,68,68,0.3);" onclick="confirmDelete()">🗑️ حذف</button>
                <button class="btn btn-ghost" id="btn-preview-invoice" style="display: none;" onclick="printVoucher()">🖨️ طباعة المعاينة</button>
                <button class="btn btn-whatsapp" id="btn-whatsapp-invoice" style="display: none; background: #25d366; color: #fff; border-color: #25d366; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold; cursor: pointer; padding: 10px 20px; border-radius: 8px;" onclick="sendCurrentVoucherWA()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>إرسال للواتساب</span>
                </button>
                <button class="btn btn-success" style="background: #10b981; min-width: 200px;" onclick="submitVoucher()">✅ اعتماد وحفظ السند</button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054970.13975"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', () => {
          const fromAccEl = document.getElementById('from-account');
          if (fromAccEl) {
              fromAccEl.dispatchEvent(new Event('change', { bubbles: true }));
          }
      });
    </script>
    <script defer src="js/single-cash-receipt.js?v=20260612_7"></script>
</body>
</html>

