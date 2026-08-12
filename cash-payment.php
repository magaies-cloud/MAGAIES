<?php $page_title = 'سند صرف نقدية'; ?>
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
    <title>سند صرف نقدية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css"> <!-- Reusing existing pos layout -->
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
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Batch Header colors */
        .top-action-bar { background: rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.2); }
        .action-tab.active { background: #ef4444; color: #fff; border-color: #ef4444; }

        /* Data Grid for Bulk Payments */
        .items-grid {
            width: 100%; border-collapse: separate; border-spacing: 0;
            background: rgba(15,23,42,0.6); backdrop-filter: blur(20px);
            border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);
        }
        .items-grid th {
            background: rgba(0,0,0,0.3); padding: 12px;
            font-size: 0.8rem; font-weight: 700; color: var(--text-muted);
            text-align: right; border-bottom: 1px solid rgba(255,255,255,0.05);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .items-grid td {
            padding: 8px 12px; border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
        }
        .grid-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-primary); padding: 8px 12px; border-radius: 8px;
            font-family: inherit; font-size: 0.85rem; transition: all 0.2s;
        }
        .grid-input:focus { border-color: #ef4444; background: rgba(0,0,0,0.4); outline: none; }
        .grid-input.amount { color: #f87171; font-weight: 700; font-family: monospace; font-size: 0.95rem; }
        
        .btn-add-row {
            width: 100%; margin-top: 15px; padding: 12px;
            background: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2);
            color: var(--text-muted); border-radius: 12px; cursor: pointer;
            transition: all 0.2s; font-family: 'Cairo', sans-serif; font-weight: 600;
        }
        .btn-add-row:hover { background: rgba(255,255,255,0.1); color: var(--text-primary); border-color: rgba(255,255,255,0.4); }

        .btn-del-row { background: none; border: none; color: #f87171; cursor: pointer; font-size: 1.1rem; padding: 4px; border-radius: 6px; transition: 0.2s; }
        .btn-del-row:hover { background: rgba(239,68,68,0.1); }
        
        /* AutoComplete Dropdown Style */
        .autocomplete-container { position: relative; }
        .autocomplete-list {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px;
            background: #1e293b; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 1000;
            display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .autocomplete-item { padding: 8px 12px; cursor: pointer; font-size: 0.85rem; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .autocomplete-item:hover { background: rgba(239,68,68,0.2); color: #fca5a5; }

        .form-section-title { font-size: 0.95rem; font-weight: 700; color: #f87171; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    
        
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

    <div class="pos-wrapper">
        <!-- BREADCRUMB -->
        <!-- Unified Header -->
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- HEADER -->
        <div class="top-action-bar">
            <div class="action-tabs">
                <button class="action-tab active">
                    <span class="tab-icon">📤</span><span class="tab-label">سند صرف نقدية</span>
                </button>
            </div>
            <div class="top-bar-meta" style="display:flex; align-items:center; gap:10px;">
                <input type="date" class="ctrl-input" id="hdr-date" style="padding: 4px 8px; font-size: 0.85rem; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; color: var(--text-primary);">
                <div class="inv-nav" style="display:flex; align-items:center; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); padding: 4px;">
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadPreviousVoucher()" title="السند السابق">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <div class="inv-badge" id="voucher-number" style="margin:0 10px; color:#f87171; min-width:60px; text-align:center;">000001</div>
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadNextVoucher()" title="السند التالي">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN LAYOUT -->
        <div class="pos-grid" style="grid-template-columns: 1fr; margin-top:15px; gap: 15px;">
            
            <!-- CREDIT ACCOUNT DETAILS -->
            <div class="glass-panel" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                    <div style="flex: 1; min-width: 300px;">
                        <h3 class="form-section-title">بيانات السند الأساسية</h3>
                        <div class="ctrl-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label class="label">من حساب (صندوق / بنك)</label>
                                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="from-account">
                                    <input type="text" class="form-input input-glow-focus hover-glow" id="from-account-search" placeholder="ابحث عن حساب الصرف (خزينة/بنك)..." onfocus="showAccountDropdown('from')" onblur="hideAccountDropdownDelayed('from')" oninput="filterAccountDropdown('from')" style="width: 100%; padding: 12px 15px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; border: 1px solid rgba(16, 185, 129, 0.5); background: rgba(0,0,0,0.3); color: #fff;">
                                    <div class="custom-dropdown-list" id="from-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                                </div>
                            </div>
                            <div>
                                <label class="label">البيان العام</label>
                                <input type="text" class="ctrl-input" id="global-statement" placeholder="مثال: صرف عهدة نقدية...">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grand Total Summary Cards (Multi-Currency) -->
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                        <!-- SDG Total -->
                        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 15px 20px; text-align: center; min-width: 140px; box-shadow: inset 0 0 20px rgba(0,0,0,0.2);">
                            <h4 style="margin: 0 0 5px 0; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">الإجمالي (SDG)</h4>
                            <div style="font-size: 1.8rem; font-weight: 900; color: #f87171; text-shadow: 0 0 10px rgba(239, 68, 68, 0.5);" id="header-grand-sdg">0.00</div>
                        </div>
                        
                        <!-- USD Total -->
                        <div style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 15px 20px; text-align: center; min-width: 120px;">
                            <h4 style="margin: 0 0 5px 0; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">دولار (USD)</h4>
                            <div style="font-size: 1.4rem; font-weight: 900; color: #38bdf8;" id="header-grand-usd">0.00</div>
                        </div>

                        <!-- AED Total -->
                        <div style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); border-radius: 12px; padding: 15px 20px; text-align: center; min-width: 120px;">
                            <h4 style="margin: 0 0 5px 0; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">درهم (AED)</h4>
                            <div style="font-size: 1.4rem; font-weight: 900; color: #34d399;" id="header-grand-aed">0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BENEFICIARIES GRID -->
            <div class="glass-panel" style="padding: 20px;">
                <h3 class="form-section-title"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg> المستفيدون (الطرف المدين)</h3>
                <table class="items-grid" id="beneficiary-table">
                    <thead>
                        <tr>
                            <th style="width: 20%">الحساب المستفيد</th>
                            <th style="width: 14%">المبلغ (عملة الدفع)</th>
                            <th style="width: 10%">العملة</th>
                            <th style="width: 10%">سعر الصرف</th>
                            <th style="width: 13%">المعادل المحلي</th>
                            <th style="width: 12%">مركز التكلفة</th>
                            <th style="width: 16%">البيان</th>
                            <th style="width: 5%">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="grid-body">
                        <!-- Rows will be injected by JS -->
                    </tbody>
                </table>
                <button class="btn-add-row" onclick="addRow()">+ إضافة مستفيد جديد</button>
            </div>

        </div>

        <!-- STICKY FOOTER -->
        <div class="sticky-footer" style="background: rgba(15,23,42,0.95); border-top: 1px solid rgba(239,68,68,0.3);">
            <div class="footer-total">
                <span class="ft-label">الإجمالي الكلي:</span>
                <span class="ft-value" id="footer-grand" style="color: #f87171;">0.00</span>
            </div>
            <div class="footer-actions">
                <button class="btn btn-ghost" onclick="resetVoucher()">جديد</button>
                <button class="btn btn-ghost" style="color: #f87171; border-color: rgba(239,68,68,0.3);" onclick="confirmDelete()">حذف السند</button>
                <button class="btn btn-ghost" onclick="printVoucher()">حفظ وطباعة</button>
                <button class="btn btn-success" style="background: #ef4444;" onclick="submitVoucher()">✓ اعتماد السند وترحيله</button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?= time() ?>"></script>
    <script defer src="js/cash-payment.js"></script>
</body>
</html>
