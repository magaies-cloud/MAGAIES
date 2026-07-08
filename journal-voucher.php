<?php $page_title = 'قيد يومية'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قيد يومية | السجانة ERP</title>
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
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.warning { background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.4); color:#fbbf24; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Purple Theme for Journal Vouchers */
        .top-action-bar { background: rgba(192, 132, 252, 0.1); border-bottom: 1px solid rgba(192, 132, 252, 0.2); }
        .action-tab.active { background: #c084fc; color: #fff; border-color: #c084fc; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-primary); padding: 10px 12px; border-radius: 8px;
            font-family: inherit; font-size: 0.95rem; transition: all 0.2s;
        }
        .form-input:focus { border-color: #c084fc; background: rgba(0,0,0,0.4); outline: none; }
        
        .form-section-title { 
            font-size: 1.1rem; font-weight: 700; color: #c084fc; 
            margin-bottom: 15px; display: flex; align-items: center; gap: 8px; 
            border-bottom: 1px solid rgba(192,132,252,0.2); padding-bottom: 10px; 
        }

        /* JV Grid */
        .jv-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 5px;
        }
        .jv-table th {
            text-align: right; padding: 12px 10px; color: #94a3b8;
            font-weight: 600; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.05);
            white-space: nowrap;
        }
        .jv-row { transition: all 0.2s; }
        .jv-row:hover { background: rgba(255,255,255,0.02); }
        .jv-row td { padding: 5px; }
        .jv-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: #f8fafc; padding: 8px 10px; border-radius: 6px; font-size: 0.9rem;
            min-width: 100px;
        }
        .jv-input:focus { border-color: #c084fc; outline: none; background: rgba(0,0,0,0.4); }
        
        .jv-input.debit { color: #34d399; font-weight: 700; font-family: monospace; }
        .jv-input.credit { color: #f87171; font-weight: 700; font-family: monospace; }

        .jv-del-btn {
            background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer;
            padding: 5px; opacity: 0.7; transition: opacity 0.2s;
        }
        .jv-del-btn:hover { opacity: 1; transform: scale(1.1); }

        /* Floating Balance Summary Box */
        .balance-box {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(192, 132, 252, 0.3);
            border-radius: 12px;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            margin-bottom: 25px;
            position: sticky;
            top: 15px;
            z-index: 50;
            backdrop-filter: blur(10px);
        }
        .bal-stat { text-align: center; }
        .bal-stat .lbl { display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 5px; }
        .bal-stat .val { font-size: 1.3rem; font-weight: 900; font-family: monospace; }
        
        .val-debit { color: #34d399; }
        .val-credit { color: #f87171; }
        .val-diff { color: #fbbf24; }
        .val-diff.balanced { color: #34d399; }
        
        .btn-add-row {
            background: rgba(192, 132, 252, 0.1);
            color: #c084fc;
            border: 1px dashed #c084fc;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn-add-row:hover { background: rgba(192, 132, 252, 0.2); }
    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="max-width: 1600px; margin: 0 auto; padding: 20px 30px; padding-bottom: 100px;">
        <!-- BREADCRUMB -->
                <!-- Unified Header -->
        <?php include 'includes/header.php'; ?>

        <!-- HEADER -->
        <div class="top-action-bar">
            <div class="action-tabs">
                <button class="action-tab active">
                    <span class="tab-icon">📝</span><span class="tab-label">قيد يومية عام (Journal Voucher)</span>
                </button>
            </div>
            <div class="top-bar-meta" style="display:flex; align-items:center; gap:10px;">
                <div class="inv-nav" style="display:flex; align-items:center; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); padding: 4px;">
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#c084fc'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadPreviousVoucher()" title="السابق">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <div class="inv-badge" id="voucher-number" style="margin:0 10px; color:#c084fc; min-width:60px; text-align:center; font-size: 1.1rem;">000001</div>
                    <button class="btn-icon" style="padding:4px; color:var(--text-muted); cursor:pointer; background:none; border:none; transition: color 0.2s;" onmouseover="this.style.color='#c084fc'" onmouseout="this.style.color='var(--text-muted)'" onclick="loadNextVoucher()" title="التالي">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN FORM -->
        <div class="glass-panel" style="padding: 25px; margin-top: 15px;">
            
            <!-- STICKY BALANCE BOX -->
            <div class="balance-box">
                <div class="bal-stat">
                    <span class="lbl">إجمالي المدين (Debit)</span>
                    <span class="val val-debit" id="tot-debit">0.00</span>
                </div>
                <div style="font-size: 1.5rem; color: rgba(255,255,255,0.2);">|</div>
                <div class="bal-stat">
                    <span class="lbl">إجمالي الدائن (Credit)</span>
                    <span class="val val-credit" id="tot-credit">0.00</span>
                </div>
                <div style="font-size: 1.5rem; color: rgba(255,255,255,0.2);">|</div>
                <div class="bal-stat">
                    <span class="lbl">الفرق (Difference)</span>
                    <span class="val val-diff" id="tot-diff">0.00</span>
                </div>
            </div>

            <!-- SECTION 1: HEADER INFO -->
            <div style="margin-bottom: 25px;">
                <h3 class="form-section-title">البيانات الأساسية للقيد</h3>
                <div class="form-grid">
                    <div>
                        <label class="label">تاريخ القيد</label>
                        <input type="date" class="form-input" id="hdr-date">
                    </div>
                    <div>
                        <label class="label">المرجع / رقم المستند</label>
                        <input type="text" class="form-input" id="hdr-ref" placeholder="مثال: فاتورة مورد رقم 10...">
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="label">البيان العام (شرح القيد)</label>
                        <input type="text" class="form-input" id="hdr-statement" placeholder="شرح مختصر لسبب تسجيل القيد...">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: JV GRID -->
            <div style="margin-bottom: 25px; overflow-x: auto;">
                <h3 class="form-section-title">تفاصيل القيد (المدين والدائن)</h3>
                <table class="jv-table" id="jv-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">الحساب (Account)</th>
                            <th style="width: 12%;">مدين (Debit)</th>
                            <th style="width: 12%;">دائن (Credit)</th>
                            <th style="width: 8%;">المركز</th>
                            <th style="width: 8%;">المشروع</th>
                            <th style="width: 15%;">جهة الاتصال</th>
                            <th style="width: 20%;">ملاحظة السطر (Note)</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="jv-tbody">
                        <!-- Rows injected via JS -->
                    </tbody>
                </table>
                <button class="btn-add-row" onclick="addGridRow()">+ إضافة سطر جديد (طرف للقيد)</button>
            </div>

        </div>

        <!-- STICKY FOOTER -->
        <div class="sticky-footer" style="background: rgba(15,23,42,0.95); border-top: 1px solid rgba(192,132,252,0.3); border-radius: 12px 12px 0 0; padding: 15px; position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;">
            <div class="footer-actions" style="width: 100%; justify-content: center; max-width: 1100px; margin: 0 auto; gap: 10px; display: flex;">
                <button class="btn btn-ghost" style="color:#f87171; border-color:rgba(239,68,68,0.3);" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة">🚪 خروج</button>
                <button class="btn btn-ghost" onclick="resetVoucher()">إفراغ الحقول</button>
                <button class="btn btn-ghost" style="color: #f87171; border-color: rgba(239,68,68,0.3);" onclick="confirmDelete()">حذف القيد</button>
                <button class="btn btn-ghost" onclick="printVoucher()">طباعة القيد</button>
                <button class="btn btn-success" style="background: #c084fc; border-color: #c084fc; min-width: 200px;" onclick="submitVoucher()">✓ حفظ واعتماد القيد</button>
            </div>
        </div>
    </div>

    <script src="js/localforage.min.js"></script>
    <script src="js/app.js?v=1783054968.10515"></script>
    <script src="js/journal-voucher.js"></script>
</body>
</html>
