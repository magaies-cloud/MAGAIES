<?php $page_title = 'سند صرف'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>سند صرف | السجانة ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
        <style>
        body { background: var(--bg-color); }
        .page-container { padding: 0 0 80px 0; }

        /* Top Action Bar */
        .action-bar {
            display: flex; gap: 8px; padding: 10px 15px;
            background: var(--surface-color);
            border-bottom: 1px solid var(--glass-border);
            overflow-x: auto;
        }
        .action-bar-btn {
            display: flex; flex-direction: column; align-items: center;
            gap: 3px; padding: 8px 14px; border-radius: 8px;
            background: var(--bg-color); border: 1px solid var(--glass-border);
            font-size: 0.7rem; font-weight: 700; color: var(--text-primary);
            cursor: pointer; white-space: nowrap; min-width: 55px;
        }
        .action-bar-btn.green { background: #16a34a; color: white; border-color: #16a34a; }
        .action-bar-btn.blue  { background: #0369a1; color: white; border-color: #0369a1; }
        .action-bar-btn.red   { background: var(--danger-color); color: white; border-color: var(--danger-color); }

        /* Voucher Number Strip */
        .voucher-meta {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 0; border-bottom: 2px solid var(--primary-accent);
        }
        .meta-cell {
            padding: 8px 12px;
            border-left: 1px solid var(--glass-border);
            display: flex; align-items: center; gap: 8px;
            background: var(--surface-color);
        }
        .meta-cell label { font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; }
        .meta-cell input, .meta-cell select {
            flex: 1; background: transparent; border: none;
            color: var(--text-primary); font-weight: 700;
            font-size: 0.9rem; outline: none;
        }

        /* Amount Box */
        .amount-box {
            padding: 15px;
            background: var(--surface-color);
            border-bottom: 1px solid var(--glass-border);
            display: flex; align-items: center; gap: 10px;
        }
        .amount-box label { font-weight: 800; font-size: 1rem; color: var(--primary-accent); white-space: nowrap; }
        .amount-box input {
            flex: 1; text-align: left; font-size: 2rem; font-weight: 900;
            color: var(--danger-color); background: transparent;
            border: none; outline: none; border-bottom: 2px solid var(--danger-color);
        }

        /* Section */
        .form-section {
            margin: 10px 12px;
            border: 1px solid var(--glass-border);
            border-radius: 10px; overflow: hidden;
            background: var(--surface-color);
        }
        .section-title {
            background: var(--primary-accent);
            color: white; padding: 6px 14px;
            font-weight: 800; font-size: 0.9rem;
        }
        .field-row {
            display: flex; align-items: center;
            border-bottom: 1px solid var(--glass-border);
            min-height: 44px;
        }
        .field-row:last-child { border-bottom: none; }
        .field-label {
            padding: 0 12px; min-width: 90px;
            font-size: 0.8rem; color: var(--primary-accent);
            font-weight: 700; border-left: 1px solid var(--glass-border);
            align-self: stretch; display: flex; align-items: center;
        }
        .field-input {
            flex: 1; padding: 10px 12px;
            background: transparent; border: none; outline: none;
            color: var(--text-primary); font-size: 0.95rem;
        }
        .field-input:focus { background: rgba(var(--primary-accent-rgb, 56, 189, 248), 0.05); }
        .browse-btn {
            padding: 8px 12px; background: var(--bg-color);
            border: none; border-right: 1px solid var(--glass-border);
            cursor: pointer; color: var(--primary-accent); font-size: 1rem;
        }
        .field-row.two-col { display: grid; grid-template-columns: 1fr 1fr; }
        .field-row.two-col .sub-field {
            display: flex; align-items: center;
            border-left: 1px solid var(--glass-border);
        }
        .field-row.two-col .sub-field:first-child { border-left: none; }

        /* Payment method radios */
        .pay-method-row {
            display: flex; gap: 25px; padding: 12px 15px;
            background: var(--bg-color);
            border-bottom: 1px solid var(--glass-border);
            align-items: center;
        }
        .pay-method-row label { display: flex; align-items: center; gap: 6px; font-weight: 700; cursor: pointer; }
        .pay-method-row input[type=radio] { accent-color: var(--primary-accent); width: 16px; height: 16px; }

        /* Invoices Table */
        .invoices-section { margin: 10px 12px 20px; }
        .invoices-title {
            background: #1e3a5f; color: white;
            padding: 8px 14px; border-radius: 8px 8px 0 0;
            font-weight: 800; display: flex; align-items: center; justify-content: space-between;
        }
        .invoices-table { width: 100%; border-collapse: collapse; overflow: hidden; }
        .invoices-table th {
            background: var(--surface-color); padding: 8px 6px;
            font-size: 0.75rem; color: var(--text-secondary);
            border-bottom: 2px solid var(--primary-accent);
            text-align: center;
        }
        .invoices-table td {
            padding: 8px 6px; text-align: center;
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.8rem;
        }
        .invoices-table tr:nth-child(even) td { background: rgba(56,189,248,0.04); }
        .invoice-checkbox { width: 16px; height: 16px; accent-color: var(--primary-accent); }
        .amount-cell { color: var(--success-color); font-weight: 700; }
        .remaining-cell { color: var(--danger-color); font-weight: 700; }

        /* Submit Button */
        .submit-area {
            position: fixed; bottom: 0; left: 0; right: 0;
            padding: 12px 15px;
            background: var(--surface-color);
            border-top: 1px solid var(--glass-border);
            display: flex; gap: 10px;
        }
        .btn-save {
            flex: 2; padding: 14px; border-radius: 10px;
            background: var(--primary-accent); color: white;
            font-weight: 900; font-size: 1.1rem;
            border: none; cursor: pointer;
        }
        .btn-print {
            flex: 1; padding: 14px; border-radius: 10px;
            background: var(--surface-color); color: var(--text-primary);
            font-weight: 700; border: 1px solid var(--glass-border); cursor: pointer;
        }

        /* Check fields */
        #check-fields { display: none; }
    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


    <!-- Navbar -->
    <nav class="navbar glass-panel" style="padding: 10px 15px;">
        <button onclick="history.back()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">🔙</button>
        <h2 style="margin:0; color:var(--primary-accent); font-size:1.1rem;">تسجيل سند صرف</h2>
        <div id="voucher-number" style="font-weight:900; color:var(--text-secondary); font-size:0.9rem;">
            رقم: <span style="color:var(--danger-color);" id="vnum">0001</span>
        </div>
    </nav>

    <!-- Top Action Toolbar -->
    <div class="action-bar">
        <button class="action-bar-btn green" onclick="submitPayment()">✓ حفظ</button>
        <button class="action-bar-btn blue" onclick="newVoucher()">تفريغ الحقول</button>
        <button class="action-bar-btn" onclick="alert('غير معتمد')">غير معتمد</button>
        <button class="action-bar-btn red" onclick="confirmDelete()">حذف القيد</button>
        <button class="action-bar-btn" onclick="alert('طباعة القيد')">طباعة القيد</button>
        <button class="action-bar-btn" onclick="window.print()">طباعة القيد</button>
        <button class="action-bar-btn blue" onclick="generateVoucher()">توليد القيد تلقائياً</button>
    </div>

    <div class="page-container">

        <!-- Meta Fields -->
        <div class="voucher-meta">
            <div class="meta-cell">
                <label>التاريخ:</label>
                <input type="text" id="doc-number" value="0001" readonly>
            </div>
            <div class="meta-cell">
                <label>التاريخ:</label>
                <input type="date" id="doc-date">
            </div>
            <div class="meta-cell">
                <label>المرجع:</label>
                <input type="text" id="reference" placeholder="المرجع...">
            </div>
            <div class="meta-cell">
                <label>رقم الفاتورة:</label>
                <input type="text" id="ref-number" placeholder="...">
            </div>
            <div class="meta-cell">
                <label>المشروع:</label>
                <select id="project">
                    <option value="">-- اختر المشروع --</option>
                    <option value="proj1">مشروع العمارة السكنية</option>
                    <option value="proj2">مشروع المركز التجاري</option>
                </select>
            </div>
            <div class="meta-cell">
                <label>المندوب:</label>
                <select id="agent">
                    <option value="">-- اختر المندوب --</option>
                </select>
            </div>
        </div>

        <!-- Amount Box -->
        <div class="amount-box">
            <label>المبلغ:</label>
            <input type="number" id="amount" placeholder="0" min="0" step="0.01">
        </div>

        <!-- Section: To (مستلم) -->
        <div class="form-section">
            <div class="section-title">إلى حساب (الطرف المستلم - مدين)</div>

            <div class="field-row">
                <span class="field-label">نوع الصرف</span>
                <button class="browse-btn" onclick="browseAccount('to')">🔎</button>
                <select class="field-input" id="to-account">
                    <option value="SUPPLIER_PAYMENT">سداد مورد (ذمم دائنة)</option>
                    <option value="EXPENSE_RENT">مصروف الإيجارات</option>
                    <option value="EXPENSE_SALARY">مصروف الرواتب</option>
                    <option value="EXPENSE_ELECTRICITY">فاتورة كهرباء</option>
                    <option value="EXPENSE_OTHER">مصروفات أخرى</option>
                </select>
            </div>

            <div class="field-row" id="supplier-row" style="display:none;">
                <span class="field-label">الحساب</span>
                <button class="browse-btn" onclick="browsePartner()">🔎</button>
                <select class="field-input" id="supplier-id">
                    <option value="p-100">مصنع الإسمنت الحديث</option>
                    <option value="p-101">شركة الحديد والصلب</option>
                    <option value="p-102">شركة المقاولات العامة</option>
                </select>
            </div>

            <div class="field-row">
                <span class="field-label">ملاحظة</span>
                <input class="field-input" type="text" id="to-desc" placeholder="ملاحظة السطر...">
            </div>

            <div class="field-row two-col">
                <div class="sub-field">
                    <span class="field-label" style="min-width:70px;">العملة</span>
                    <select class="field-input" id="currency" style="flex:1;">
                        <option value="curr-sdg">جنيه سوداني</option>
                        <option value="curr-usd">دولار أمريكي</option>
                        <option value="curr-sar">ريال سعودي</option>
                    </select>
                </div>
                <div class="sub-field" style="border-right: 1px solid var(--glass-border);">
                    <span class="field-label" style="min-width:60px;">الصرف</span>
                    <input class="field-input" type="number" id="exchange-rate" value="1" step="0.01">
                </div>
            </div>

            <div class="field-row">
                <span class="field-label">المستفيد</span>
                <input class="field-input" type="text" id="beneficiary" placeholder="اسم المستلم أو الجهة...">
            </div>
        </div>

        <!-- Section: From (دافع) -->
        <div class="form-section">
            <div class="section-title">من حساب (جهة الدفع - دائن)</div>

            <div class="field-row">
                <span class="field-label">الخزينة/البنك</span>
                <button class="browse-btn">🔎</button>
                <select class="field-input" id="from-account">
                    <option value="CASH_MAIN">الخزينة الرئيسية (نقدية)</option>
                    <option value="BANK_1">بنك (حساب 1)</option>
                    <option value="BANK_2">بنك الخرطوم</option>
                </select>
            </div>

            <div class="field-row">
                <span class="field-label">بيان السطر</span>
                <input class="field-input" type="text" id="from-desc" placeholder="بيان جهة الدفع...">
            </div>

            <!-- Payment Method -->
            <div class="pay-method-row">
                <label><input type="radio" name="pay_method" value="cash" checked onchange="togglePayMethod(this.value)"> نقداً</label>
                <label><input type="radio" name="pay_method" value="check" onchange="togglePayMethod(this.value)"> شيك</label>
                <label><input type="radio" name="pay_method" value="bank" onchange="togglePayMethod(this.value)"> تحويل/شبكة</label>
            </div>

            <!-- Check / Transfer Fields -->
            <div id="check-fields">
                <div class="field-row">
                    <span class="field-label">رقم الشيك</span>
                    <input class="field-input" type="text" id="check-number" placeholder="0000000000">
                </div>
                <div class="field-row two-col">
                    <div class="sub-field">
                        <span class="field-label" style="min-width:90px;">تاريخ الشيك</span>
                        <input class="field-input" type="date" id="check-date">
                    </div>
                    <div class="sub-field" style="border-right:1px solid var(--glass-border);">
                        <span class="field-label" style="min-width:60px;">ت. الاستحقاق</span>
                        <input class="field-input" type="date" id="due-date">
                    </div>
                </div>
                <div class="field-row two-col">
                    <div class="sub-field">
                        <span class="field-label" style="min-width:70px;">المرجع</span>
                        <input class="field-input" type="text" id="from-ref" placeholder="...">
                    </div>
                    <div class="sub-field" style="border-right:1px solid var(--glass-border);">
                        <span class="field-label" style="min-width:80px;">رقم الإيصال</span>
                        <input class="field-input" type="text" id="from-ref-num" placeholder="...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Section -->
        <div class="invoices-section">
            <div class="invoices-title">
                <span>تم تسديد الفواتير (للتخصيص عند ربط الفواتير المفتوحة)</span>
                <button onclick="addInvoiceRow()" style="background:none;border:none;color:#93c5fd;cursor:pointer;font-size:1.2rem;">+</button>
            </div>
            <div style="overflow-x:auto; border: 1px solid var(--glass-border); border-top: none; border-radius: 0 0 8px 8px;">
                <table class="invoices-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تحديد</th>
                            <th>الفاتورة</th>
                            <th>التاريخ</th>
                            <th>قيمة الفاتورة</th>
                            <th>المبلغ المسدد</th>
                            <th>المتبقي بعد السداد</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody id="invoices-body">
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Submit Bar -->
    <div class="submit-area">
        <button class="btn-print" onclick="window.print()">طباعة القيد</button>
        <button class="btn-save" onclick="submitPayment()">✓ حفظ</button>
    </div>

    <script src="js/localforage.min.js"></script>
    <script src="js/app.js?v=1783054968.79721"></script>
    <script>
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) {
                window.location.href = 'login.php';
            }
            // Set today's date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('doc-date').value = today;
            document.getElementById('check-date').value = today;
            document.getElementById('due-date').value = today;

            // Auto-generate voucher number
            const nextNum = (parseInt(localStorage.getItem('pv_counter') || '0') + 1).toString().padStart(4, '0');
            document.getElementById('doc-number').value = nextNum;
            document.getElementById('vnum').textContent = nextNum;

            // Handle to-account change
            document.getElementById('to-account').addEventListener('change', (e) => {
                document.getElementById('supplier-row').style.display =
                    e.target.value === 'SUPPLIER_PAYMENT' ? 'flex' : 'none';
            });
            
            // Populate agents dynamically
            const agentSelect = document.getElementById('agent');
            if (agentSelect) {
                let employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                employees.forEach(emp => {
                    if (emp.status === 'active' || emp.status === 'نشط') {
                        const opt = document.createElement('option');
                        opt.value = emp.id;
                        opt.textContent = emp.name;
                        agentSelect.appendChild(opt);
                    }
                });
            }
        });

        function togglePayMethod(val) {
            document.getElementById('check-fields').style.display =
                (val === 'check' || val === 'bank') ? 'block' : 'none';
        }

        function calcTotal() {
            const checked = document.querySelectorAll('.invoice-checkbox:checked');
            let total = 0;
            checked.forEach(cb => {
                const row = cb.closest('tr');
                const amtText = row.cells[5].textContent.replace(/,/g, '');
                total += parseFloat(amtText) || 0;
            });
            if (total > 0) document.getElementById('amount').value = total;
        }

        function browseAccount(type) { alert('(ميزة استعراض الحسابات غير متاحة)'); }
        function browsePartner()     { alert('(ميزة استعراض الشركاء)'); }
        function newVoucher()        { location.reload(); }
        function confirmDelete()     { if (confirm('هل أنت متأكد من حذف القيد؟')) history.back(); }
        function generateVoucher()   { alert('توليد القيد تلقائياً...'); }
        function addInvoiceRow() {
            const body = document.getElementById('invoices-body');
            const idx = body.rows.length + 1;
            body.insertAdjacentHTML('beforeend', \`<tr>
                <td>\${idx}</td>
                <td><input type="checkbox" class="invoice-checkbox" onchange="calcTotal()"></td>
                <td><input style="border:none;background:transparent;width:80px;" placeholder="رقم الفاتورة"></td>
                <td><input style="border:none;background:transparent;width:60px;" placeholder="التاريخ"></td>
                <td><input style="border:none;background:transparent;width:80px;" placeholder="المبلغ"></td>
                <td><input type="number" style="border:none;background:transparent;width:70px;" placeholder="0" class="amount-cell"></td>
                <td class="remaining-cell">-</td>
                <td>-</td>
            </tr>\`);
        }

        async function submitPayment() {
            const amount = parseFloat(document.getElementById('amount').value);
            if (!amount || amount <= 0) { alert('يرجى إدخال المبلغ'); return; }

            const accountId = document.getElementById('to-account').value;
            const partnerId = document.getElementById('supplier-id')?.value || null;

            try {
                const res = await fetch('http://localhost:3000/api/v1/financials/payment-voucher', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'x-device-uuid': localStorage.getItem('deviceUuid') || 'DEV' },
                    body: JSON.stringify({ amount, currencyId: 'curr-sdg', partnerId, accountId })
                });
                const data = res.ok ? await res.json() : null;
                const ref = data?.data?.entryId || 'PV-' + Date.now();
                localStorage.setItem('pv_counter', parseInt(localStorage.getItem('pv_counter') || '0') + 1);
                alert(\`✓ تم حفظ سند الصرف بنجاح!\nرقم السند: \${ref}\`);
                history.back();
            } catch {
                localStorage.setItem('pv_counter', parseInt(localStorage.getItem('pv_counter') || '0') + 1);
                alert(\`✓ تم الحفظ (سند صرف افتراضي)\nرقم: PV-\${Date.now()}\`);
                history.back();
            }
        }
    </script>
</body>
</html>
