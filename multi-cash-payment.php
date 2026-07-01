<?php $page_title = 'حركة مالية مجمعة'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حركة مالية مجمعة | السجانة ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <!-- Choices.js for select -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
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
    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
    </div>
    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="max-width: 1600px; margin: 0 auto; padding: 20px 30px; padding-bottom: 100px;">
        <?php include 'includes/header.php'; ?>

        <div class="top-action-bar" style="background: transparent; border: none; padding: 0; margin-bottom: 25px;">
            <div style="display: flex; gap: 25px; width: 100%;">
                <button id="btn-payment" class="crystal-btn active-payment" onclick="setVoucherType('payment')">
                    <span style="font-size: 1.5rem;">🔴</span> سند صرف مجمع
                </button>
                <button id="btn-receipt" class="crystal-btn" onclick="setVoucherType('receipt')">
                    <span style="font-size: 1.5rem;">🟢</span> سند استلام مجمع
                </button>
            </div>
            <input type="hidden" id="voucher-type" value="payment">
        </div>

        <div class="split-layout-wrapper" id="main-layout-wrapper">
            <!-- Left Side Panel (Hidden by default, slides in when Check/Transfer is active) -->
            <div class="side-panel" id="payment-details-panel">
                <h3 class="form-section-title" style="color: #fbbf24; border-color: rgba(251,191,36,0.2);">
                    <span style="display:inline-block; margin-left:8px; font-size:1.3rem;">📋</span> 
                    تفاصيل <span id="panel-title-type">الشيك</span>
                </h3>
                
                <!-- MOBILE TRANSFER FIELDS -->
                <div id="section-mobile" class="dynamic-section" style="display:none; padding:0; background:none; border:none; margin:0;">
                    <div class="form-grid" style="grid-template-columns: 1fr; gap: 15px; margin-bottom: 0;">
                        <div>
                            <label class="label" style="color:#94a3b8; font-size:0.85rem; margin-bottom:5px; display:block;">رقم حساب التحويل</label>
                            <input type="text" class="form-input" id="mobile-transfer-no" placeholder="رقم الحساب..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label" style="color:#94a3b8; font-size:0.85rem; margin-bottom:5px; display:block;">ملاحظات التحويل...</label>
                            <input type="text" class="form-input" id="mobile-note" placeholder="ملاحظات التحويل..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                    </div>
                </div>

                <!-- CHECK FIELDS -->
                <div id="section-check" class="dynamic-section" style="display:none; padding:0; background:none; border:none; margin:0;">
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 0;">
                        <div>
                            <label class="label" style="color:#94a3b8; font-size:0.85rem; margin-bottom:5px; display:block;">رقم الشيك</label>
                            <input type="text" class="form-input" id="check-no" placeholder="000000" style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label" style="color:#94a3b8; font-size:0.85rem; margin-bottom:5px; display:block;">اسم البنك</label>
                            <input type="text" class="form-input" id="check-branch" placeholder="الفرع..." style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label" style="color:#94a3b8; font-size:0.85rem; margin-bottom:5px; display:block;">تاريخ الشيك</label>
                            <input type="date" class="form-input" id="check-date" style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                        <div>
                            <label class="label" style="color:#94a3b8; font-size:0.85rem; margin-bottom:5px; display:block;">تاريخ الاستحقاق</label>
                            <input type="date" class="form-input" id="check-due-date" style="padding: 10px 12px; background: rgba(0,0,0,0.3);">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side (Main Content area) -->
            <div class="main-panel glass-panel" id="main-form-panel" style="padding: 25px; position: relative;">
                
                <div class="form-section-title"><span>👤</span> بيانات الطرف الرئيسي</div>
                
                <!-- Payment Methods Row -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 25px; background: rgba(255,255,255,0.02); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label class="label" style="color:#cbd5e1; display:flex; align-items:center; gap:5px; margin-bottom:0; font-size:1.1rem; font-weight:bold;">
                            <span style="font-size:1.1rem; opacity:0.8;">💳</span> طريقة الدفع
                        </label>
                    </div>
                    <div class="payment-methods-tabs" style="margin:0; box-shadow:none; background: transparent; padding: 0; display: flex; gap: 8px;">
                        <label class="pm-tab" style="flex: 0 0 auto; margin: 0;">
                            <input type="radio" name="payment_method" value="cash" checked onchange="togglePaymentMethod()">
                            <div class="pm-tab-content" style="padding: 6px 15px; gap: 6px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); flex-direction: row; height: 44px;">
                                <span class="pm-icon" style="font-size: 1.2rem;">💵</span>
                                <span class="pm-label" style="font-size: 0.9rem; margin-top: 0;">نقدي</span>
                            </div>
                        </label>
                        <label class="pm-tab" style="flex: 0 0 auto; margin: 0;">
                            <input type="radio" name="payment_method" value="check" onchange="togglePaymentMethod()">
                            <div class="pm-tab-content" style="padding: 6px 15px; gap: 6px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); flex-direction: row; height: 44px;">
                                <span class="pm-icon" style="font-size: 1.2rem;">🧾</span>
                                <span class="pm-label" style="font-size: 0.9rem; margin-top: 0;">شيك</span>
                            </div>
                        </label>
                        <label class="pm-tab" style="flex: 0 0 auto; margin: 0;">
                            <input type="radio" name="payment_method" value="mobile" onchange="togglePaymentMethod()">
                            <div class="pm-tab-content" style="padding: 6px 15px; gap: 6px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); flex-direction: row; height: 44px;">
                                <span class="pm-icon" style="font-size: 1.2rem;">📱</span>
                                <span class="pm-label" style="font-size: 0.9rem; margin-top: 0;">تحويل</span>
                            </div>
                        </label>
                    </div>
                    
                    <div style="flex-grow: 1; display: flex; justify-content: flex-end; align-items: center;">
                        <input type="text" id="voucher-ref" class="form-input" placeholder="رقم المرجع (اختياري)" style="height: 44px; max-width: 200px; background: rgba(0,0,0,0.3);">
                    </div>
                </div>

                <div class="form-grid">

                <div>
                    <label id="main-account-label" style="color: #ffffff; font-size: 0.85rem; margin-bottom: 5px; display: block; font-weight: bold;">من حساب (صرف)</label>
                    <select id="main-account" class="form-select" data-choices>
                        <option value="">-- اختر الحساب --</option>
                    </select>
                </div>
                <div>
                    <label style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; display: block;">تاريخ العملية</label>
                    <input type="date" id="voucher-date" class="form-input">
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
                    <label style="color: #ef4444; font-size: 0.85rem; margin-bottom: 5px; display: block; font-weight: bold;">💵 القيمة (المعادل)</label>
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
                </style>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    <script src="js/app.js?v=20260626_4"></script>
    <!-- Choices.js -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="js/multi-cash-payment.js"></script>
</body>
</html>
