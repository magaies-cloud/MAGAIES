<?php $page_title = 'حركة مالية مجمعة'; ?>
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
    <title>حركة مالية مجمعة | مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
        .top-action-bar { background: rgba(249, 115, 22, 0.1); border-bottom: 1px solid rgba(249, 115, 22, 0.2); }
        .action-tab.active { background: #f97316; color: #fff; border-color: #f97316; }

        .form-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px;
        }
        
        .form-input, .form-select {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: #fff; padding: 10px 12px; border-radius: 8px;
            font-family: inherit; font-size: 0.95rem; transition: all 0.2s;
        }
        .form-input:focus, .form-select:focus { border-color: #f97316; background: rgba(0,0,0,0.4); outline: none; }
        
        .form-section-title { 
            font-size: 1.1rem; font-weight: 700; color: #f97316; 
            margin-bottom: 15px; display: flex; align-items: center; gap: 8px; 
            border-bottom: 1px solid rgba(249,115,22,0.2); padding-bottom: 10px; 
        }

        /* Grid */
        .multi-table { width: 100%; border-collapse: separate; border-spacing: 0 5px; }
        .multi-table th { text-align: right; padding: 12px 10px; color: #94a3b8; font-weight: 600; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.05); white-space: nowrap; }
        .multi-row { transition: all 0.2s; }
        .multi-row:hover { background: rgba(255,255,255,0.02); }
        .multi-row td { padding: 5px; }
        .multi-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: #f8fafc; padding: 8px 10px; border-radius: 6px; font-size: 0.9rem; min-width: 100px;
        }
        .multi-input:focus { border-color: #f97316; outline: none; background: rgba(0,0,0,0.4); }
        
        .btn-add-row {
            background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px dashed #f97316;
            padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700;
            width: 100%; margin-top: 10px; transition: all 0.2s; font-family: 'Tajawal', sans-serif;
        }
        .btn-add-row:hover { background: rgba(249, 115, 22, 0.2); }

        .btn-save {
            background: #10b981; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; font-family: 'Tajawal', sans-serif; transition: 0.2s; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .btn-save:hover { background: #059669; transform: translateY(-2px); }

        .btn-del { background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer; padding: 5px; transition: 0.2s; }
        .btn-del:hover { transform: scale(1.2); }

        /* Choices custom overrides */
        .choices__inner { background-color: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #fff !important; border-radius: 8px !important; min-height: 42px !important; }
        .choices__list--dropdown { background-color: #1e293b !important; color: #fff !important; border: 1px solid #f97316 !important; }
        .choices__item--selectable.is-highlighted { background-color: #f97316 !important; }
        /* Toast */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; }
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Context Menu */
        .context-menu {
            position: absolute; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 10px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5); z-index: 1000;
            display: none; min-width: 200px;
        }
        .context-menu.active { display: flex; flex-direction: column; gap: 8px; animation: fadeIn 0.2s; }
        .context-menu-item {
            display: flex; align-items: center; gap: 10px; color: #fff;
            padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 0.9rem;
        }
        .context-menu-item:hover { background: rgba(255,255,255,0.05); }
        .context-menu-item input[type="checkbox"] { accent-color: #f97316; width: 16px; height: 16px; cursor: pointer; }

        /* Dynamic Columns */
        .col-cost-center, .col-note, .col-check-no, .col-check-date, .col-check-due { display: none; }
        table.show-col-cost-center .col-cost-center { display: table-cell; }
        table.show-col-note .col-note { display: table-cell; }
        table.show-col-check-no .col-check-no { display: table-cell; }
        table.show-col-check-date .col-check-date { display: table-cell; }
        table.show-col-check-due .col-check-due { display: table-cell; }

        /* Sticky Footer */
        .sticky-footer { position: sticky; bottom: 0; background: rgba(15,23,42,0.9); border-top: 1px solid rgba(249,115,22,0.3); padding: 15px 25px; border-radius: 0 0 16px 16px; display: flex; justify-content: space-between; align-items: center; z-index: 50; backdrop-filter: blur(10px); margin-top: 20px; margin-left: -25px; margin-right: -25px; margin-bottom: -25px; }

        /* Crystal Buttons */
        .crystal-btn {
            flex: 1; padding: 15px 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); color: #94a3b8;
            font-size: 1.3rem; font-weight: 900; font-family: 'Tajawal', sans-serif; cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .crystal-btn::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
            transform: skewX(-20deg); transition: 0.7s;
        }
        .crystal-btn:hover::before { left: 150%; }
        .crystal-btn:hover { transform: translateY(-3px); background: rgba(255,255,255,0.08); }
        .crystal-btn.active-payment {
            background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.5); color: #fca5a5;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4), inset 0 0 15px rgba(239, 68, 68, 0.2);
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.8); transform: translateY(-2px) scale(1.02);
        }
        .crystal-btn.active-receipt {
            background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.5); color: #6ee7b7;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4), inset 0 0 15px rgba(16, 185, 129, 0.2);
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.8); transform: translateY(-2px) scale(1.02);
        }

        /* Advanced Layout & Animations */
        .split-layout-wrapper {
            display: flex; flex-direction: row-reverse; gap: 0; margin-top: 15px; width: 100%; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
        }
        
        /* Side Panel */
        .side-panel {
            width: 0; opacity: 0; overflow: hidden; transform: translateX(-80px) scale(0.95);
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(25px); border-radius: 16px;
            border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1); flex-shrink: 0;
            padding: 0; /* Padding is 0 when hidden */
        }
        
        /* When side panel is active */
        .split-layout-wrapper.has-sidebar { gap: 20px; }
        .split-layout-wrapper.has-sidebar .side-panel {
            width: 380px; opacity: 1; transform: translateX(0) scale(1); padding: 25px;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        /* Main Panel */
        .main-panel {
            flex-grow: 1; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
            background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);
            border-radius: 16px; padding: 25px; border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transform-origin: left center;
        }

        @media (max-width: 900px) {
            .split-layout-wrapper.has-sidebar { flex-direction: column; }
            .split-layout-wrapper.has-sidebar .side-panel { width: 100%; transform: translateX(0) translateY(-20px); }
        }

        /* Payment Methods Tabs */
        .payment-methods-tabs {
            display: flex; gap: 10px; background: rgba(0,0,0,0.4); padding: 8px;
            border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
        }
        .pm-tab { flex: 1; cursor: pointer; }
        .pm-tab input { display: none; }
        .pm-tab-content {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 15px 5px; gap: 10px; border-radius: 10px; transition: all 0.4s cubic-bezier(0.25, 1, 0.2, 1);
            color: #94a3b8; font-weight: 700; font-size: 0.9rem;
        }
        .pm-icon { font-size: 1.8rem; transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s; filter: grayscale(100%); opacity: 0.5; }
        
        .pm-tab input:checked + .pm-tab-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); 
            color: #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            transform: translateY(-4px);
        }
        .pm-tab input:checked + .pm-tab-content .pm-icon {
            transform: scale(1.3); filter: grayscale(0%); opacity: 1;
            text-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }
        /* Custom Searchable Dropdown */
        .custom-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            max-height: 280px;
            overflow-y: auto;
            text-align: right;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            display: none;
            margin-top: 6px;
        }
        .custom-dropdown-list.show {
            display: block;
            animation: slideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .custom-dropdown-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .custom-dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .custom-dropdown-item:last-child {
            border-bottom: none;
        }

        /* Voucher Type Toggle Buttons (صرف/استلام مجمع) */
        .voucher-type-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #94a3b8;
        }
        .voucher-type-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .voucher-type-btn.active-payment {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }
        .voucher-type-btn.active-receipt {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
    </div>
    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="max-width: 98%; margin: 0 auto; padding: 15px 10px; padding-bottom: 100px;">
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>

        <input type="hidden" id="voucher-type" value="payment">

        <div class="split-layout-wrapper" id="main-layout-wrapper" style="display: block; width: 100%;">
            <!-- Main Content area (Full width) -->
            <div class="main-panel glass-panel" id="main-form-panel" style="padding: 25px; position: relative; width: 100%;">
                
                <div class="form-section-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 8px;"><span>👤</span> بيانات الطرف الرئيسي</div>
                    <div style="display: flex; gap: 8px; margin-left: 5px;">
                        <button id="btn-payment" class="voucher-type-btn active-payment" onclick="setVoucherType('payment')" style="padding: 12px 36px; font-size: 1.2rem; border-radius: 12px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 12px; font-family: 'Tajawal', sans-serif; min-width: 240px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                            <span style="width: 16px; height: 16px; border-radius: 50%; background: #ef4444; display: inline-block; box-shadow: 0 0 12px #ef4444, 0 0 25px #ef4444;"></span> صرف مجمع
                        </button>
                        <button id="btn-receipt" class="voucher-type-btn" onclick="setVoucherType('receipt')" style="padding: 12px 36px; font-size: 1.2rem; border-radius: 12px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 12px; font-family: 'Tajawal', sans-serif; min-width: 240px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                            <span style="width: 16px; height: 16px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 12px #10b981, 0 0 25px #10b981;"></span> استلام مجمع
                        </button>
                    </div>
                </div>
                
                <!-- Payment Methods Row -->
                <div id="payment-methods-row" style="display: flex; align-items: center; justify-content: flex-start; gap: 20px; margin-bottom: 25px; background: rgba(239, 68, 68, 0.08); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.2); flex-wrap: wrap; transition: all 0.4s ease;">
                    <!-- 1. رقم السند والملاحة -->
                    <div style="display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; height: 44px; padding: 0 8px;">
                        <button type="button" onclick="navigateVoucher(-1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 1.2rem; padding: 0 5px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند السابق">◀</button>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span style="color: #94a3b8; font-size: 0.9rem; font-weight: bold; white-space: nowrap;">📄 السند:</span>
                            <input type="text" id="voucher-no-display" readonly style="background: transparent; border: none; color: #fff; font-weight: bold; width: 140px; text-align: center; outline: none; font-size:1rem;" value="">
                        </div>
                        <button type="button" onclick="navigateVoucher(1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 1.2rem; padding: 0 5px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند التالي">▶</button>
                    </div>

                    <!-- 2. التاريخ -->
                    <div style="display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; height: 44px; padding: 0 12px;">
                        <span style="color: #94a3b8; font-size: 0.9rem; font-weight: bold; white-space: nowrap;">📅 التاريخ:</span>
                        <input type="date" id="voucher-date" class="form-input" style="background: transparent; border: none; color: #fff; font-weight: bold; padding: 0; margin: 0; height: 100%; width: 135px; outline: none; box-shadow: none;">
                    </div>

                    <!-- 4. الرقم المرجعي الاختياري -->
                    <div style="flex-grow: 1; display: flex; justify-content: flex-end; align-items: center;">
                        <input type="text" id="voucher-ref" class="form-input" placeholder="رقم المرجع (اختياري)" style="height: 44px; max-width: 200px; background: rgba(0,0,0,0.3);">
                    </div>
                </div>

                <div class="form-grid">

                <div>
                    <label id="main-account-label" style="color: #f87171 !important; font-size: 1.1rem; margin-bottom: 5px; display: block; font-weight: bold; transition: color 0.3s;">من حساب (صرف)</label>
                    <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                        <input type="hidden" id="main-account">
                        <input type="text" class="form-control form-select" id="main-account-search" placeholder="ابحث عن حساب الصرف (خزينة/بنك)..." onfocus="showAccountDropdown('main-account')" onblur="hideAccountDropdownDelayed('main-account')" oninput="filterAccountDropdown('main-account')" style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                        <div class="custom-dropdown-list" id="main-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                    </div>
                </div>

                <div>
                    <label style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: block;">العملة</label>
                    <select id="currency" class="form-select" onchange="document.getElementById('exchange-rate').focus()">
                        <option value="SDG">جنيه (SDG)</option>
                        <option value="USD">دولار (USD)</option>
                        <option value="AED">درهم (AED)</option>
                    </select>
                </div>
                <div>
                    <label style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: block;">التعادل</label>
                    <input type="text" id="exchange-rate" class="form-input" value="1.0" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); typeof calcTotal === 'function' ? calcTotal() : null;" style="font-weight: bold; font-family: monospace;">
                </div>
                <div>
                    <label style="color: #10b981; font-size: 0.85rem; margin-bottom: 5px; display: block; font-weight: bold;">💵 الإجمالي بالمحلي</label>
                    <input type="text" id="amount-local-hdr" class="form-input" value="0.00" readonly style="font-weight: 900; font-family: monospace; color: #10b981; background: rgba(255,255,255,0.05);">
                </div>
                <div>
                    <label style="color: #ef4444; font-size: 0.85rem; margin-bottom: 5px; display: block; font-weight: bold;">💵 الإجمالي بالأجنبي</label>
                    <input type="text" id="amount-foreign" class="form-input" value="0.00" readonly style="font-weight: 900; font-family: monospace; color: #ef4444; background: rgba(255,255,255,0.05);">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; display: block;">البيان العام / الوصف</label>
                    <input type="text" id="voucher-desc" class="form-input" placeholder="وصف عام للحركة المجمعة...">
                </div>
            </div>

            <div class="form-section-title" id="sub-accounts-title" style="margin-top: 30px;"><span>👥</span> إلى حساب (استلام)</div>
            
            <table class="multi-table" id="voucher-table">
                <thead id="table-header" style="cursor: context-menu;" title="اضغط كليك يمين للتحكم بالأعمدة">
                    <tr>
                        <th id="sub-account-header" style="width: 25%;">إلى حساب (استلام)</th>
                        <th style="width: 15%;">المبلغ</th>
                        <th class="col-desc" style="width: 30%;">البيان الفرعي</th>
                        <th class="col-cost-center" style="width: 15%;">مركز التكلفة</th>
                        <th class="col-note" style="width: 20%;">ملحوظة</th>
                        <th class="col-check-no" style="width: 10%;">رقم الشيك</th>
                        <th class="col-check-date" style="width: 10%;">تاريخ الشيك</th>
                        <th class="col-check-due" style="width: 10%;">الاستحقاق</th>
                        <th style="width: 5%;">إجراء</th>
                    </tr>
                </thead>
                <tbody id="entries-body">
                    <!-- Rows will be added dynamically -->
                </tbody>
            </table>
            
            <button class="btn-add-row" onclick="addRow()">+ إضافة حساب جديد</button>

            <!-- Hidden radios to prevent legacy JS checks breaking -->
            <div style="display: none;">
                <input type="radio" name="payment_method" value="cash" id="radio-cash" checked>
                <input type="radio" name="payment_method" value="check" id="radio-check">
                <input type="radio" name="payment_method" value="mobile" id="radio-mobile">
            </div>

            <!-- Unified Payment details panel styled like Sales/Purchase -->
            <style>
                .pay-toggle-btn {
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    color: #94a3b8;
                    padding: 6px 14px;
                    border-radius: 20px;
                    font-size: 0.9rem;
                    font-weight: 800;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }
                .pay-toggle-btn.active {
                    background: rgba(56, 189, 248, 0.2) !important;
                    border-color: #38bdf8 !important;
                    color: #38bdf8 !important;
                    box-shadow: 0 0 12px rgba(56, 189, 248, 0.3);
                }
                #toggle-cash.active {
                    background: rgba(16, 185, 129, 0.2) !important;
                    border-color: #34d399 !important;
                    color: #34d399 !important;
                    box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
                }
                #toggle-bank.active {
                    background: rgba(59, 130, 246, 0.2) !important;
                    border-color: #60a5fa !important;
                    color: #60a5fa !important;
                    box-shadow: 0 0 12px rgba(59, 130, 246, 0.3);
                }
                #toggle-cheque.active {
                    background: rgba(139, 92, 246, 0.2) !important;
                    border-color: #c084fc !important;
                    color: #c084fc !important;
                    box-shadow: 0 0 12px rgba(139, 92, 246, 0.3);
                }
                .payment-method-box {
                    transition: all 0.3s ease;
                }
            </style>
            <div id="payment-panel" style="display: none; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; margin-top: 20px; margin-bottom: 20px;">
                <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 12px 20px; background: rgba(15,23,42,0.4); border-bottom: 1px solid rgba(255,255,255,0.06); border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 0;">
                    <div class="panel-title" style="background: linear-gradient(135deg, #ff4d4d, #f87171); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.15rem; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; margin: 0; white-space: nowrap; flex: none;">
                        <span>💳</span> طريقة وتفاصيل الدفع للمستند المجمع
                    </div>
                    
                    <!-- Sleek Payment Progress Bar (Inline) -->
                    <div class="payment-progress-container" style="flex: 1; display: flex; align-items: center; gap: 12px; min-width: 280px; padding: 0 10px;">
                        <div class="progress-bar-wrapper" style="height: 10px; flex: 1; background: rgba(255,255,255,0.05); border-radius: 5px; overflow: hidden; display: flex; box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); min-width: 120px;">
                            <div id="prog-cash" style="width: 0%; height: 100%; background: linear-gradient(90deg, #10b981, #34d399); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(16,185,129,0.3);"></div>
                            <div id="prog-bank" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #60a5fa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(59,130,246,0.3);"></div>
                            <div id="prog-cheque" style="width: 0%; height: 100%; background: linear-gradient(90deg, #8b5cf6, #a78bfa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(139,92,246,0.3);"></div>
                        </div>
                        <span id="payment-status-badge" style="font-size: 0.78rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; transition: all 0.3s ease; white-space: nowrap; background: rgba(16, 185, 129, 0.15); color: #34d399; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);" data-status="fully-paid">💳 مدفوعة بالكامل</span>
                    </div>

                    <div class="payment-toggles" style="display: flex; gap: 8px; flex: none;">
                        <button type="button" class="pay-toggle-btn active" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">💵 نقدي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">🏦 تحويل بنكي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">✍️ شيك</button>
                    </div>
                </div>
                
                <div class="payment-layout" id="payment-layout" style="display: flex; gap: 15px; padding: 15px; flex-wrap: wrap;">
                    <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Cash section -->
                        <div id="pay-cash-section" class="payment-method-box" style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.18); border-radius: 12px; padding: 12px 15px; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px; min-width: 200px; width: 200px; flex: none;">
                                <span style="font-size: 1.2rem;">💵</span>
                                <span style="font-weight: 800; color: #34d399; font-size: 0.95rem;">الدفع النقدي (كاش)</span>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 320px; flex-wrap: wrap;">
                                <span style="color: #94a3b8; font-size: 0.9rem;">سيتم السداد نقداً بالكامل للمستند المجمع.</span>
                            </div>
                        </div>

                        <!-- Bank Transfer section -->
                        <div id="pay-bank-section" class="payment-method-box" style="background: rgba(59, 130, 246, 0.04); border: 1px solid rgba(59, 130, 246, 0.18); border-radius: 12px; padding: 12px 15px; display: none; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(59,130,246,0.1); padding-bottom: 8px; margin-bottom: 5px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 1.2rem;">🏦</span>
                                    <span style="font-weight: 800; color: #60a5fa; font-size: 0.95rem;">تفاصيل التحويلات البنكية للمجمع</span>
                                </div>
                                <button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(59,130,246,0.2); border-color:#60a5fa; color:#60a5fa; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; height: 30px;">➕ إضافة تحويل</button>
                            </div>
                            <div id="transfers-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <!-- Dynamic rows -->
                            </div>
                        </div>

                        <!-- Cheque section -->
                        <div id="pay-cheque-section" class="payment-method-box" style="background: rgba(139, 92, 246, 0.04); border: 1px solid rgba(139, 92, 246, 0.18); border-radius: 12px; padding: 12px 15px; display: none; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(139,92,246,0.1); padding-bottom: 8px; margin-bottom: 5px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 1.2rem;">✍️</span>
                                    <span style="font-weight: 800; color: #c084fc; font-size: 0.95rem;">تفاصيل الشيكات</span>
                                </div>
                                <button type="button" onclick="addChequeRow()" class="pay-toggle-btn" style="background: rgba(139,92,246,0.2); border-color:#c084fc; color:#c084fc; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; height: 30px;">➕ إضافة شيك</button>
                            </div>
                            <div id="cheques-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <!-- Dynamic rows -->
                            </div>
                        </div>

                        <!-- Hidden legacy inputs to prevent crashes in old scripts -->
                        <div style="display: none;">
                            <input type="text" id="mobile-transfer-no">
                            <input type="text" id="mobile-note">
                            <input type="text" id="check-no">
                            <input type="text" id="check-branch">
                            <input type="date" id="check-date">
                            <input type="date" id="check-due-date">
                        </div>

            <!-- Sticky Footer for Totals & Save -->
            <div class="sticky-footer" style="background: rgba(15,23,42,0.85); backdrop-filter: blur(30px); -webkit-backdrop-filter: blur(30px); border-top: 1px solid rgba(56, 189, 248, 0.4); border-left: 1px solid rgba(56, 189, 248, 0.4); border-right: 1px solid rgba(56, 189, 248, 0.4); border-radius: 20px 20px 0 0; padding: 8px 15px; position: sticky; bottom: 0; z-index: 100; box-shadow: 0 -10px 40px rgba(0,0,0,0.6), inset 0 2px 15px rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                
                <!-- Totals Section -->
                <div style="display: flex; gap: 20px; align-items: center;">
                    <div style="color: #94a3b8; font-size: 0.9rem;">إجمالي الأسطر: <span id="total-rows" style="color: #fff; font-weight: bold; font-family: monospace;">0</span></div>
                    <div style="font-size: 1.3rem; color: #f97316;">الإجمالي الكلي: <span id="total-amount" style="color: #fff; font-weight: 900; font-family: monospace; letter-spacing: 1px;">0.00</span></div>
                </div>

                <!-- Action Buttons Section -->
                <style>
                    .footer-actions .btn { font-size: 0.85rem !important; padding: 8px 15px !important; }
                    .footer-actions .btn-save-master { font-size: 0.95rem !important; padding: 8px 20px !important; font-weight: bold; }
                
        
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
                <div class="footer-actions" style="display: flex; gap: 8px; flex-wrap: wrap; flex-grow: 1; justify-content: flex-end;">
                    <button type="button" class="btn crystal-btn btn-exit" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة">🚪 خروج</button>
                    <button type="button" class="btn crystal-btn btn-new" onclick="resetVoucher()">✨ مستند جديد</button>
                    <button type="button" class="btn crystal-btn btn-print" onclick="printVoucher()">🖨️ طباعة</button>
                    <button type="button" class="btn crystal-btn btn-delete" onclick="confirmDelete()">🗑️ حذف المستند</button>
                    <button type="button" class="btn crystal-btn btn-save-master" id="btn-save" onclick="saveVoucher()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">✅ حفظ واعتماد السند</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div class="context-menu" id="column-context-menu">
        <div style="color: #fbbf24; font-size: 0.8rem; margin-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px;">إظهار / إخفاء الأعمدة</div>
        <label class="context-menu-item"><input type="checkbox" value="cost-center" onchange="toggleColumn('cost-center', this.checked)"> مركز التكلفة</label>
        <label class="context-menu-item"><input type="checkbox" value="note" onchange="toggleColumn('note', this.checked)"> ملحوظة</label>
        <label class="context-menu-item"><input type="checkbox" value="check-no" onchange="toggleColumn('check-no', this.checked)"> رقم الشيك</label>
        <label class="context-menu-item"><input type="checkbox" value="check-date" onchange="toggleColumn('check-date', this.checked)"> تاريخ الشيك</label>
        <label class="context-menu-item"><input type="checkbox" value="check-due" onchange="toggleColumn('check-due', this.checked)"> الاستحقاق</label>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054968.65812"></script>
    <script defer src="js/multi-cash-payment.js"></script>
</body>
</html>

