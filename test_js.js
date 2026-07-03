
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) window.location.href = 'login.php';
            if (window.location.hash) {
                const tabId = window.location.hash.substring(1);
                const tabBtn = document.querySelector(`button[onclick*="${tabId}"]`);
                if (tabBtn) tabBtn.click();
            }
            clearCustomerForm();
            clearSupplierForm();
            renderCustomers();
            renderSuppliers();
            renderEmployees();
            if(typeof renderBanks === 'function') renderBanks();
            if(typeof renderSafes === 'function') renderSafes();
            if(typeof renderItems === 'function') renderItems();
            renderWarehouses();
            if(typeof renderCostCenters === 'function') renderCostCenters();
        });

        function openTab(evt, tabId) {
            document.querySelectorAll('.md-tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.md-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        function openInnerTab(evt, tabId) {
            const container = evt.currentTarget.closest('.md-card');
            container.querySelectorAll('.inner-tab-content').forEach(c => c.classList.remove('active'));
            container.querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        function switchSupplierTab(event, tabId) {
            const form = document.getElementById('supplier-form');
            form.querySelectorAll('.inner-tab-btn').forEach(btn => btn.classList.remove('active'));
            form.querySelectorAll('.inner-tab-content').forEach(tab => tab.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        // ================= WAREHOUSES LOGIC =================
        function saveWarehouse() {
            const name = document.getElementById('warehouse-name').value;
            if(!name) { alert('الرجاء إدخال اسم المخزن'); return; }
            
            const type = document.getElementById('warehouse-type').value;
            const security = parseInt(document.getElementById('warehouse-security').value) || 1;
            const id = document.getElementById('warehouse-id').value || 'WH-' + Date.now();

            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            const existingIdx = warehouses.findIndex(w => w.id === id);

            const whData = { id, name, type, securityLevel: security };

            if (existingIdx > -1) {
                warehouses[existingIdx] = whData;
            } else {
                warehouses.push(whData);
            }

            localStorage.setItem('erp_warehouses_v1', JSON.stringify(warehouses));
            cancelEditWarehouse();
            renderWarehouses();
            
            if(typeof showToast === 'function') showToast('تم حفظ المخزن بنجاح', 'success');
        }

        function renderWarehouses() {
            const tbody = document.getElementById('warehouses-tbody');
            if(!tbody) return;
            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            tbody.innerHTML = '';
            
            if(warehouses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 15px; color: var(--text-secondary);">لا توجد مخازن مضافة بعد</td></tr>';
                return;
            }

            warehouses.forEach(w => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td><strong>${w.name}</strong></td>
                    <td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${w.type}</span></td>
                    <td>
                        <button class="btn-text" onclick="editWarehouse('${w.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteWarehouse('${w.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editWarehouse(id) {
            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            const wh = warehouses.find(w => w.id === id);
            if(!wh) return;
            document.getElementById('warehouse-id').value = wh.id;
            document.getElementById('warehouse-name').value = wh.name;
            document.getElementById('warehouse-type').value = wh.type || 'مستودع رئيسي';
            document.getElementById('warehouse-security').value = wh.securityLevel || '1';
            document.getElementById('btn-cancel-warehouse').style.display = 'inline-block';
        }

        function cancelEditWarehouse() {
            document.getElementById('warehouse-form').style.display = 'none';
            document.getElementById('warehouse-id').value = '';
            document.getElementById('warehouse-name').value = '';
            document.getElementById('warehouse-type').value = 'مستودع رئيسي';
            document.getElementById('warehouse-security').value = '1';
        }

        function openWarehouseForm() {
            cancelEditWarehouse();
            document.getElementById('warehouse-form').style.display = 'block';
        }

        function deleteWarehouse(id) {
            if(confirm('هل أنت متأكد من حذف هذا المخزن؟ سيتم حذف المخزن فقط، لن يتم حذف البضائع الموجودة.')) {
                let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
                const idx = warehouses.findIndex(w => w.id === id); if(idx > -1) { warehouses[idx].deleted = true; warehouses[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_warehouses_v1', JSON.stringify(warehouses));
                renderWarehouses();
            }
        }

        // ================= CUSTOMERS LOGIC =================
        function saveCustomer() {
            const id = document.getElementById('cust-id').value || 'CUST-' + Date.now();
            const name = document.getElementById('cust-name').value;
            const phone = document.getElementById('cust-phone').value;
            const whatsapp = document.getElementById('cust-whatsapp').value;
            const email = document.getElementById('cust-email').value;
            const contactPerson = document.getElementById('cust-contact').value;
            const address = document.getElementById('cust-address').value;
            const openingDebit = parseFloat(document.getElementById('cust-opening-debit').value) || 0;
            const openingCredit = parseFloat(document.getElementById('cust-opening-credit').value) || 0;
            const currency = document.getElementById('cust-currency').value || 'SDG';
            const taxNumber = document.getElementById('cust-tax').value || '';
            const accountNo = document.getElementById('cust-account-no').value || '';
            const limit = parseFloat(document.getElementById('cust-limit').value) || 0;
            const security = parseInt(document.getElementById('cust-security').value) || 1;
            const status = document.getElementById('cust-status').value || 'active';
            const notes = document.getElementById('cust-notes').value;

            const bankAccounts = [];
            document.querySelectorAll('.cust-bank-row').forEach(row => {
                bankAccounts.push({
                    bankName: row.querySelector('.bank-name').value,
                    branch: row.querySelector('.bank-branch').value,
                    accountNo: row.querySelector('.bank-acc').value,
                    securityLevel: parseInt(row.querySelector('.bank-security').value) || 1,
                    isDeactivated: row.querySelector('.bank-deactive').checked
                });
            });

            if (!name) { alert('الرجاء إدخال اسم العميل'); return; }

            // Check for duplicate names in Customers and Suppliers
            const currentName = name.trim().toLowerCase();
            let allCusts = []; let allSups = [];
            try {
                allCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                allSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
            } catch(e) {}
            
            const isCustDup = allCusts.some(c => c.name && c.name.trim().toLowerCase() === currentName && c.id !== id);
            const isSupDup = allSups.some(s => s.name && s.name.trim().toLowerCase() === currentName && s.id !== id);
            
            if (isCustDup || isSupDup) {
                alert('هذا الاسم موجود مسبقاً (كعميل أو مورد). لا يمكن تكرار الأسماء!');
                return;
            }

            let customers = allCusts;
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            
            const existingIdx = customers.findIndex(c => c.id === id);
            
            const custData = {
                id, name, phone, whatsapp, email, contactPerson, address, 
                openingDebit, openingCredit, 
                currency, taxNumber, accountNo,
                currentBalance: (openingDebit - openingCredit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes,
                bankAccounts: bankAccounts
            };

            if (existingIdx > -1) {
                custData.currentBalance = customers[existingIdx].currentBalance;
                customers[existingIdx] = custData;
            } else {
                customers.push(custData);
            }

            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            clearCustomerForm();
            renderCustomers();
        }

        function renderCustomers(query = '') {
            const tbody = document.getElementById('customers-tbody');
            if(!tbody) return;
            let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);

            if (query) {
                const q = query.toLowerCase();
                customers = customers.filter(c => 
                    (c.name && c.name.toLowerCase().includes(q)) || 
                    (c.phone && c.phone.toLowerCase().includes(q)) || 
                    (c.email && c.email.toLowerCase().includes(q))
                );
            }

            tbody.innerHTML = '';
            customers.forEach(c => {
                const statusBadge = c.status === 'vip' ? '🌟 VIP' : (c.status === 'blacklisted' ? '🔴 موقوف' : '🟢 نشط');
                tbody.insertAdjacentHTML('beforeend', `<tr><td>${c.name} <small>${statusBadge}</small></td><td>${c.phone || '-'}</td><td>${c.currentBalance} SDG</td><td>${c.creditLimit} SDG</td><td><button class="btn-text" onclick="editCustomer('${c.id}')">تعديل</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCustomer('${c.id}')">حذف</button></td></tr>`);
            });
        }

        function filterCustomers() {
            const query = document.getElementById('customer-search-input').value;
            renderCustomers(query);
        }

        function addCustomerBankAccount(data = null) {
            const container = document.getElementById('cust-bank-accounts-container');
            if(!container) return;
            const row = document.createElement('div');
            row.className = 'cust-bank-row';
            row.style.cssText = 'display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; padding: 15px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; position: relative; animation: fadeIn 0.3s ease-out;';
            
            row.innerHTML = `
                <div class="input-group"><label>اسم البنك</label><input type="text" class="bank-name" placeholder="مثال: بنك الخرطوم" value="${data ? (data.bankName || '') : ''}"></div>
                <div class="input-group"><label>الفرع</label><input type="text" class="bank-branch" placeholder="مثال: فرع الرياض" value="${data ? (data.branch || '') : ''}"></div>
                <div class="input-group"><label>رقم الحساب</label><input type="text" class="bank-acc" placeholder="رقم الحساب البنكي" value="${data ? (data.accountNo || '') : ''}"></div>
                <div class="input-group"><label>درجة سرية البنك</label>
                    <select class="bank-security">
                        <option value="1" ${data && data.securityLevel == 1 ? 'selected' : ''}>1 (الأقل سرية)</option>
                        <option value="2" ${data && data.securityLevel == 2 ? 'selected' : ''}>2</option>
                        <option value="3" ${data && data.securityLevel == 3 ? 'selected' : ''}>3</option>
                        <option value="4" ${data && data.securityLevel == 4 ? 'selected' : ''}>4</option>
                        <option value="5" ${data && data.securityLevel == 5 ? 'selected' : ''}>5</option>
                        <option value="6" ${data && data.securityLevel == 6 ? 'selected' : ''}>6</option>
                        <option value="7" ${data && data.securityLevel == 7 ? 'selected' : ''}>7</option>
                        <option value="8" ${data && data.securityLevel == 8 ? 'selected' : ''}>8</option>
                        <option value="9" ${data && data.securityLevel == 9 ? 'selected' : ''}>9</option>
                        <option value="10" ${data && data.securityLevel == 10 ? 'selected' : ''}>10 (الأعلى سرية)</option>
                    </select>
                </div>
                <div class="input-group" style="display:flex; align-items:flex-end; gap: 10px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:bold; color:var(--danger-color); margin-bottom:10px;">
                        <input type="checkbox" class="bank-deactive" style="width:18px; height:18px; accent-color:var(--danger-color);" ${data && data.isDeactivated ? 'checked' : ''}> إيقاف
                    </label>
                    <button type="button" onclick="this.closest('.cust-bank-row').remove()" style="background: rgba(244,63,94,0.15); color: #f43f5e; border: 1px solid rgba(244,63,94,0.3); border-radius: 6px; cursor: pointer; padding: 6px 12px; margin-bottom: 5px; margin-right: auto;" title="حذف الحساب">🗑️ حذف</button>
                </div>
            `;
            container.appendChild(row);
        }

        function clearCustomerForm() {
            document.getElementById('cust-id').value = '';
            document.getElementById('cust-name').value = '';
            document.getElementById('cust-phone').value = '';
            document.getElementById('cust-whatsapp').value = '';
            document.getElementById('cust-email').value = '';
            document.getElementById('cust-contact').value = '';
            document.getElementById('cust-address').value = '';
            document.getElementById('cust-opening-debit').value = '';
            document.getElementById('cust-opening-credit').value = '';
            document.getElementById('cust-currency').value = 'SDG';
            document.getElementById('cust-tax').value = '';
            document.getElementById('cust-account-no').value = '';
            document.getElementById('cust-limit').value = '';
            document.getElementById('cust-security').value = '1';
            document.getElementById('cust-status').value = 'active';
            document.getElementById('cust-notes').value = '';
            
            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) {
                bankContainer.innerHTML = '';
                addCustomerBankAccount();
            }
        }

        function editCustomer(id) {
            let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);
            const cust = customers.find(c => c.id === id);
            if(!cust) return;
            document.getElementById('cust-id').value = cust.id;
            document.getElementById('cust-name').value = cust.name;
            document.getElementById('cust-phone').value = cust.phone || '';
            document.getElementById('cust-whatsapp').value = cust.whatsapp || '';
            document.getElementById('cust-email').value = cust.email || '';
            document.getElementById('cust-contact').value = cust.contactPerson || '';
            document.getElementById('cust-address').value = cust.address || '';
            document.getElementById('cust-opening-debit').value = cust.openingDebit || '';
            document.getElementById('cust-opening-credit').value = cust.openingCredit || '';
            document.getElementById('cust-currency').value = cust.currency || 'SDG';
            document.getElementById('cust-tax').value = cust.taxNumber || '';
            document.getElementById('cust-account-no').value = cust.accountNo || '';
            document.getElementById('cust-limit').value = cust.creditLimit || '';
            document.getElementById('cust-security').value = cust.securityLevel || '1';
            document.getElementById('cust-status').value = cust.status || 'active';
            document.getElementById('cust-notes').value = cust.notes || '';
            
            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) bankContainer.innerHTML = '';
            
            if (cust.bankAccounts && cust.bankAccounts.length > 0) {
                cust.bankAccounts.forEach(acc => addCustomerBankAccount(acc));
            } else if (cust.bankDetails) {
                addCustomerBankAccount(cust.bankDetails);
            } else {
                addCustomerBankAccount();
            }
            
            document.getElementById('customer-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteCustomer(id) {
            if(confirm('هل أنت متأكد من حذف هذا العميل؟')) {
                let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);
                const idx = customers.findIndex(c => c.id === id); if(idx > -1) { customers[idx].deleted = true; customers[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
                renderCustomers();
            }
        }

        // ================= SUPPLIERS LOGIC =================
        function saveSupplier() {
            const id = document.getElementById('sup-id').value || 'SUP-' + Date.now();
            const name = document.getElementById('sup-name').value;
            const phone = document.getElementById('sup-phone').value;
            const whatsapp = document.getElementById('sup-whatsapp').value;
            const email = document.getElementById('sup-email').value;
            const address = document.getElementById('sup-address').value;
            const openingDebit = parseFloat(document.getElementById('sup-opening-debit').value) || 0;
            const openingCredit = parseFloat(document.getElementById('sup-opening-credit').value) || 0;
            const currency = document.getElementById('sup-currency').value || 'SDG';
            const taxNumber = document.getElementById('sup-tax').value || '';
            const accountNo = document.getElementById('sup-account-no').value || '';
            const limit = parseFloat(document.getElementById('sup-limit').value) || 0;
            const security = parseInt(document.getElementById('sup-security').value) || 1;
            const status = document.getElementById('sup-status').value || 'active';
            const notes = document.getElementById('sup-notes').value;

            if (!name) { alert('الرجاء إدخال اسم المورد'); return; }

            // Check for duplicate names in Customers and Suppliers
            const currentName = name.trim().toLowerCase();
            let allCusts = []; let allSups = [];
            try {
                allCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                allSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
            } catch(e) {}
            
            const isCustDup = allCusts.some(c => c.name && c.name.trim().toLowerCase() === currentName && c.id !== id);
            const isSupDup = allSups.some(s => s.name && s.name.trim().toLowerCase() === currentName && s.id !== id);
            
            if (isCustDup || isSupDup) {
                alert('هذا الاسم موجود مسبقاً (كعميل أو مورد). لا يمكن تكرار الأسماء!');
                return;
            }

            let suppliers = allSups;
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            
            const existingIdx = suppliers.findIndex(s => s.id === id);
            
            const supData = {
                id, name, phone, whatsapp, email, address, 
                openingDebit, openingCredit, 
                currency, taxNumber, accountNo,
                currentBalance: (openingDebit - openingCredit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes
            };

            if (existingIdx > -1) {
                supData.currentBalance = suppliers[existingIdx].currentBalance;
                suppliers[existingIdx] = supData;
            } else {
                suppliers.push(supData);
            }

            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
            clearSupplierForm();
            renderSuppliers();
        }

        function renderSuppliers() {
            const tbody = document.querySelector('#tab-suppliers-table tbody');
            if(!tbody) return;
            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
            tbody.innerHTML = '';
            suppliers.forEach(s => {
                const statusBadge = s.status === 'blacklisted' ? '🔴 موقوف' : '🟢 نشط';
                tbody.insertAdjacentHTML('beforeend', `<tr><td>${s.name} <small>${statusBadge}</small></td><td>${s.phone||'-'}</td><td>${s.currentBalance} SDG</td><td>${s.creditLimit} SDG</td><td><button class="btn-text" onclick="editSupplier('${s.id}')">تعديل</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSupplier('${s.id}')">حذف</button></td></tr>`);
            });
        }
        
        function clearSupplierForm() {
            document.getElementById('sup-id').value = '';
            document.getElementById('sup-name').value = '';
            document.getElementById('sup-phone').value = '';
            document.getElementById('sup-whatsapp').value = '';
            document.getElementById('sup-email').value = '';
            document.getElementById('sup-address').value = '';
            document.getElementById('sup-opening-debit').value = '';
            document.getElementById('sup-opening-credit').value = '';
            document.getElementById('sup-currency').value = 'SDG';
            document.getElementById('sup-tax').value = '';
            document.getElementById('sup-account-no').value = '';
            document.getElementById('sup-limit').value = '';
            document.getElementById('sup-security').value = '1';
            document.getElementById('sup-status').value = 'active';
            document.getElementById('sup-notes').value = '';
        }
        
        function editSupplier(id) {
            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
            const sup = suppliers.find(s => s.id === id);
            if(!sup) return;
            document.getElementById('sup-id').value = sup.id;
            document.getElementById('sup-name').value = sup.name;
            document.getElementById('sup-phone').value = sup.phone || '';
            document.getElementById('sup-whatsapp').value = sup.whatsapp || '';
            document.getElementById('sup-email').value = sup.email || '';
            document.getElementById('sup-address').value = sup.address || '';
            document.getElementById('sup-opening-debit').value = sup.openingDebit || '';
            document.getElementById('sup-opening-credit').value = sup.openingCredit || '';
            document.getElementById('sup-currency').value = sup.currency || 'SDG';
            document.getElementById('sup-tax').value = sup.taxNumber || '';
            document.getElementById('sup-account-no').value = sup.accountNo || '';
            document.getElementById('sup-limit').value = sup.creditLimit || '';
            document.getElementById('sup-security').value = sup.securityLevel || '1';
            document.getElementById('sup-status').value = sup.status || 'active';
            document.getElementById('sup-notes').value = sup.notes || '';
            
            document.getElementById('supplier-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteSupplier(id) {
            if(confirm('هل أنت متأكد من حذف هذا المورد؟')) {
                let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
                const idx = suppliers.findIndex(s => s.id === id); if(idx > -1) { suppliers[idx].deleted = true; suppliers[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
                renderSuppliers();
            }
        }

        function renderEmployees() {
            const tbody = document.getElementById('tab-employees-table');
            if(!tbody) return;
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            employees = employees.filter(e => !e.deleted);
            tbody.innerHTML = '';
            
            const safeSelect = document.getElementById('safe-responsible');
            if (safeSelect) {
                const currentVal = safeSelect.value;
                safeSelect.innerHTML = '<option value="">-- اختر --</option>';
                employees.forEach(emp => {
                    if (emp.status === 'active' || emp.status === 'نشط') {
                        const opt = document.createElement('option');
                        opt.value = emp.id || emp.username || emp.name;
                        opt.textContent = emp.name || emp.fullName || emp.username || 'بدون اسم';
                        safeSelect.appendChild(opt);
                    }
                });
                safeSelect.value = currentVal;
            }

            employees.forEach(emp => {
                tbody.insertAdjacentHTML('beforeend', `<tr><td>${emp.name}</td><td>${emp.role}</td><td>${emp.phone||'-'}</td><td>${emp.securityLevel}</td><td>${emp.status}</td><td><button class="btn-text" onclick="editEmployee('${emp.id}')">تعديل</button></td></tr>`);
            });
        }
        
        function openNewEmployeeForm() {
            document.getElementById('emp-id').value = '';
            document.getElementById('emp-name').value = '';
            document.getElementById('emp-phone').value = '';
            document.getElementById('emp-password').value = '';
            document.getElementById('emp-role').value = 'cashier';
            document.getElementById('emp-security').value = '1';
            document.getElementById('emp-status').value = 'active';
            document.getElementById('employee-form').style.display = 'block';
        }
        
        function saveEmployee() {
            const id = document.getElementById('emp-id').value || 'EMP-' + Date.now();
            const name = document.getElementById('emp-name').value;
            if(!name) { alert('الرجاء إدخال اسم الموظف'); return; }
            
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            
            const existingIdx = employees.findIndex(e => e.id === id);
            
            const empData = {
                id,
                name,
                phone: document.getElementById('emp-phone').value,
                password: document.getElementById('emp-password').value,
                role: document.getElementById('emp-role').value,
                securityLevel: parseInt(document.getElementById('emp-security').value) || 1,
                status: document.getElementById('emp-status').value
            };
            
            if (existingIdx > -1) {
                employees[existingIdx] = empData;
            } else {
                employees.push(empData);
            }
            
            localStorage.setItem('erp_users_v1', JSON.stringify(employees));
            document.getElementById('employee-form').style.display='none';
            renderEmployees();
        }
        
        function editEmployee(id) {
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            employees = employees.filter(e => !e.deleted);
            const emp = employees.find(e => e.id === id);
            if(!emp) return;
            document.getElementById('emp-id').value = emp.id;
            document.getElementById('emp-name').value = emp.name;
            document.getElementById('emp-phone').value = emp.phone || '';
            document.getElementById('emp-password').value = emp.password || '';
            document.getElementById('emp-role').value = emp.role || 'cashier';
            document.getElementById('emp-security').value = emp.securityLevel || '1';
            document.getElementById('emp-status').value = emp.status || 'active';
            document.getElementById('employee-form').style.display='block';
        }

    
        // ================= BANKS & SAFES LOGIC =================
        function openBankForm() {
            document.getElementById('bank-form').style.display = 'block';
            document.getElementById('bank-code').value = 'B-' + Date.now();
        }

        function closeBankForm() {
            document.getElementById('bank-form').style.display = 'none';
        }

        

        function saveBank() {
            const id = document.getElementById('bank-code').value;
            const name = document.getElementById('bank-name').value;
            if(!name) { alert('الرجاء إدخال اسم الحساب/الخزينة'); return; }

            const type = 'bank';
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            
            const existingIdx = banks.findIndex(b => b.id === id);

            const bankData = {
                id, name, type,
                branch: document.getElementById('bank-branch').value,
                openingBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                openingDate: document.getElementById('bank-opening-date').value,
                glAccount: document.getElementById('bank-gl-account').value,
                iban: document.getElementById('bank-iban').value,
                swift: document.getElementById('bank-swift').value,
                responsible: '',
                currency: document.getElementById('bank-currency').value,
                maxLimit: 0,
                minLimit: 0,
                confidentiality: document.getElementById('bank-confidentiality').value,
                isInactive: document.getElementById('bank-inactive').checked,
                isModifier: false
            };

            if (existingIdx > -1) {
                bankData.currentBalance = banks[existingIdx].currentBalance;
                banks[existingIdx] = bankData;
            } else {
                banks.push(bankData);
            }

            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            closeBankForm();
            renderBanks();
        }

        function renderBanks() {
            const tbody = document.getElementById('banks-tbody');
            if(!tbody) return;
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
            tbody.innerHTML = '';
            banks.forEach(b => {
                const typeLabel = b.type === 'bank' ? '🏦 حساب بنكي' : '💰 خزينة';
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td>${b.id}</td>
                    <td>${b.name} <br><small>${b.branch || ''}</small></td>
                    <td>${typeLabel}</td>
                    <td>${b.currency}</td>
                    <td>${b.currentBalance}</td>
                    <td>
                        <button class="btn-text" onclick="editBank('${b.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteBank('${b.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editBank(id) {
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
            const bank = banks.find(b => b.id === id);
            if(!bank) return;
            document.querySelector(`input[name="bank_type"][value="${bank.type}"]`).checked = true;
            toggleBankFields();
            document.getElementById('bank-code').value = bank.id;
            document.getElementById('bank-name').value = bank.name;
            document.getElementById('bank-branch').value = bank.branch || '';
            document.getElementById('bank-opening-balance').value = bank.openingBalance || '';
            document.getElementById('bank-opening-date').value = bank.openingDate || '';
            document.getElementById('bank-gl-account').value = bank.glAccount || '';
            document.getElementById('bank-iban').value = bank.iban || '';
            document.getElementById('bank-swift').value = bank.swift || '';
            document.getElementById('bank-responsible').value = bank.responsible || '';
            document.getElementById('bank-currency').value = bank.currency || 'SDG';
            document.getElementById('safe-max-limit').value = bank.maxLimit || '';
            document.getElementById('safe-min-limit').value = bank.minLimit || '';
            document.getElementById('bank-confidentiality').value = bank.confidentiality || '1';
            document.getElementById('bank-inactive').checked = bank.isInactive || false;
            
            openBankForm();
        }

        function deleteBank(id) {
            if(confirm('هل أنت متأكد من حذف هذا الحساب؟')) {
                let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
                const idx = banks.findIndex(b => b.id === id); if(idx > -1) { banks[idx].deleted = true; banks[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
                renderBanks();
            }
        }

                // ================= ITEMS LOGIC =================
        function closeItemForm() { document.getElementById('item-form').style.display = 'none'; }

        function updateToggleLabel() {
            const t = document.getElementById('item-active-toggle');
            const l = document.getElementById('item-active-label');
            if(t.checked) { l.innerText = '🟢 نشط'; l.style.color = 'var(--success-color)'; }
            else { l.innerText = '🔴 موقوف'; l.style.color = 'var(--danger-color)'; }
        }

        function updateBaseUnitRow() {
            const v = document.getElementById('item-base-unit').value;
            const d = document.getElementById('base-unit-name-display');
            if(d) d.value = v;
        }

        function quickAddCategory() {
            const name = prompt('أدخل اسم الفئة الجديدة:');
            if(!name) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            updateCategoryDropdowns();
            document.getElementById('item-category').value = name;
            renderCategoriesList();
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value;
            if(!name) { alert('الرجاء إدخال اسم الصنف'); return; }
            const baseUnit = document.getElementById('item-base-unit').value;
            if(!baseUnit) { alert('الرجاء إدخال الوحدة الأساسية'); return; }
            const id = document.getElementById('item-code').value || 'ITEM-' + Date.now();
            const units = [];
            document.querySelectorAll('#units-matrix-body tr').forEach((row) => {
                const inputs = row.querySelectorAll('input');
                if(inputs.length >= 7) {
                    units.push({ unitName: inputs[0].value || baseUnit, factor: parseFloat(inputs[1].value) || 1, barcode: inputs[2].value, cashPrice: parseFloat(inputs[3].value) || 0, creditPrice: parseFloat(inputs[4].value) || 0, minPrice: parseFloat(inputs[5].value) || 0, weight: parseFloat(inputs[6].value) || 0, stock: 0 });
                }
            });
            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const itemData = { id, name, nameLatin: document.getElementById('item-name-latin').value, barcode: document.getElementById('item-barcode').value, category: document.getElementById('item-category').value, subCategory: document.getElementById('item-sub-category').value, nature: document.getElementById('item-nature').value, brand: document.getElementById('item-brand').value, status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive', sellingPrice: parseFloat(document.getElementById('item-selling-price').value) || 0, maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0, taxable: document.getElementById('item-taxable').checked, taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0, costMethod: costRadio ? costRadio.value : 'lastPurchase', openingQty: parseFloat(document.getElementById('item-opening-qty').value) || 0, openingValue: parseFloat(document.getElementById('item-opening-value').value) || 0, binLocation: document.getElementById('item-bin-location').value, maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0, minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0, reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0, baseUnit, bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1, units, description: document.getElementById('item-description').value };
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) { if(items[ei].units) { itemData.units.forEach((u,i) => { if(items[ei].units[i]) u.stock = items[ei].units[i].stock || 0; }); } items[ei] = itemData; }
            else { itemData.units[0].stock = itemData.openingQty; items.push(itemData); }
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            closeItemForm(); renderItems();
        }

        

function openSafeForm() {
            document.getElementById('safe-form').style.display = 'block';
            document.getElementById('safe-code').value = 'B-' + Date.now();
        }

        function closeSafeForm() {
            document.getElementById('safe-form').style.display = 'none';
        }

        

        function saveSafe() {
            const id = document.getElementById('safe-code').value;
            const name = document.getElementById('safe-name').value;
            if(!name) { alert('الرجاء إدخال اسم الخزينة'); return; }

            const type = 'safe';
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            
            const isDuplicateName = safes.some(b => b.name.trim() === name.trim() && b.id !== id);
            if (isDuplicateName) {
                alert('هذا الاسم مستخدم لخزنة أخرى، الرجاء إدخال اسم مختلف.');
                return;
            }

            const existingIdx = safes.findIndex(b => b.id === id);

            const safeData = {
                id, name, type,
                branch: '',
                openingBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                openingDate: document.getElementById('safe-opening-date').value,
                glAccount: document.getElementById('safe-gl-account').value,
                iban: '',
                swift: '',
                responsible: document.getElementById('safe-responsible').value,
                currency: document.getElementById('safe-currency').value,
                maxLimit: parseFloat(document.getElementById('safe-max-limit').value) || 0,
                minLimit: parseFloat(document.getElementById('safe-min-limit').value) || 0,
                confidentiality: document.getElementById('safe-confidentiality').value,
                isInactive: document.getElementById('safe-inactive').checked,
                isModifier: document.getElementById('safe-modifier').checked
            };

            if (existingIdx > -1) {
                safeData.currentBalance = safes[existingIdx].currentBalance;
                safes[existingIdx] = safeData;
            } else {
                safes.push(safeData);
            }

            localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
            closeSafeForm();
            renderSafes();
        }

        function renderSafes() {
            const tbody = document.getElementById('safes-tbody');
            if(!tbody) return;
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            tbody.innerHTML = '';
            safes.forEach(b => {
                const typeLabel = b.type === 'safe' ? '💰 خزينة' : '💰 خزينة';
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td>${b.id}</td>
                    <td>${b.name} <br><small>${b.branch || ''}</small></td>
                    <td>${typeLabel}</td>
                    <td>${b.currency}</td>
                    <td>${b.currentBalance}</td>
                    <td>
                        <button class="btn-text" onclick="editSafe('${b.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSafe('${b.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editSafe(id) {
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            const safe = safes.find(b => b.id === id);
            if(!safe) return;
            document.querySelector(`input[name="safe_type"][value="${safe.type}"]`).checked = true;
            toggleSafeFields();
            document.getElementById('safe-code').value = safe.id;
            document.getElementById('safe-name').value = safe.name;
            
            document.getElementById('safe-opening-balance').value = safe.openingBalance || '';
            document.getElementById('safe-opening-date').value = safe.openingDate || '';
            document.getElementById('safe-gl-account').value = safe.glAccount || '';
            
            
            document.getElementById('safe-responsible').value = safe.responsible || '';
            document.getElementById('safe-currency').value = safe.currency || 'SDG';
            document.getElementById('safe-max-limit').value = safe.maxLimit || '';
            document.getElementById('safe-min-limit').value = safe.minLimit || '';
            document.getElementById('safe-confidentiality').value = safe.confidentiality || '1';
            document.getElementById('safe-inactive').checked = safe.isInactive || false;
            document.getElementById('safe-modifier').checked = safe.isModifier || false;
            openSafeForm();
        }

        function deleteSafe(id) {
            if(confirm('هل أنت متأكد من حذف هذا الحساب؟')) {
                let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
                const idx = safes.findIndex(b => b.id === id); if(idx > -1) { safes[idx].deleted = true; safes[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
                renderSafes();
            }
        }

                // ================= ITEMS LOGIC =================
        function closeItemForm() { document.getElementById('item-form').style.display = 'none'; }

        function updateToggleLabel() {
            const t = document.getElementById('item-active-toggle');
            const l = document.getElementById('item-active-label');
            if(t.checked) { l.innerText = '🟢 نشط'; l.style.color = 'var(--success-color)'; }
            else { l.innerText = '🔴 موقوف'; l.style.color = 'var(--danger-color)'; }
        }

        function updateBaseUnitRow() {
            const v = document.getElementById('item-base-unit').value;
            const d = document.getElementById('base-unit-name-display');
            if(d) d.value = v;
        }

        function quickAddCategory() {
            const name = prompt('أدخل اسم الفئة الجديدة:');
            if(!name) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            updateCategoryDropdowns();
            document.getElementById('item-category').value = name;
            renderCategoriesList();
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value;
            if(!name) { alert('الرجاء إدخال اسم الصنف'); return; }
            const baseUnit = document.getElementById('item-base-unit').value;
            if(!baseUnit) { alert('الرجاء إدخال الوحدة الأساسية'); return; }
            const id = document.getElementById('item-code').value || 'ITEM-' + Date.now();
            const units = [];
            document.querySelectorAll('#units-matrix-body tr').forEach((row) => {
                const unitNameElem = row.querySelector('input[type="text"]:first-of-type, select.unit-name-input');
                const inputs = row.querySelectorAll('input[type="number"], input[type="text"]:not(#base-unit-name-display)');
                
                // inputs now has:
                // [number factor, text barcode, number cash, number credit, number min, number weight]
                // but wait, let's just get them by specific selectors or index correctly to avoid bugs.
                
                const factorInput = row.querySelector('input[type="number"]:nth-of-type(1)'); // wait, nth-of-type(1) might be wrong if there are multiple td's.
                // It's safer to get all inputs AND selects and map them, or use specific classes/indices.
                // Let's use the td index.
                const tds = row.querySelectorAll('td');
                if (tds.length >= 8) {
                    const uName = tds[1].querySelector('input, select')?.value || baseUnit;
                    const uFactor = parseFloat(tds[2].querySelector('input')?.value) || 1;
                    const uBarcode = tds[3].querySelector('input')?.value || '';
                    const uCash = parseFloat(tds[4].querySelector('input')?.value) || 0;
                    const uCredit = parseFloat(tds[5].querySelector('input')?.value) || 0;
                    const uMin = parseFloat(tds[6].querySelector('input')?.value) || 0;
                    const uWeight = parseFloat(tds[7].querySelector('input')?.value) || 0;
                    
                    units.push({ unitName: uName, factor: uFactor, barcode: uBarcode, cashPrice: uCash, creditPrice: uCredit, minPrice: uMin, weight: uWeight, stock: 0 });
                }
            });
            const itemBarcode = document.getElementById('item-barcode').value;
            const newBarcodes = [itemBarcode, ...units.map(u => u.barcode)].filter(b => b && b.trim() !== '');

            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const itemData = { id, name, nameLatin: document.getElementById('item-name-latin').value, barcode: itemBarcode, category: document.getElementById('item-category').value, subCategory: document.getElementById('item-sub-category').value, nature: document.getElementById('item-nature').value, brand: document.getElementById('item-brand').value, status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive', sellingPrice: parseFloat(document.getElementById('item-selling-price').value) || 0, maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0, taxable: document.getElementById('item-taxable').checked, taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0, costMethod: costRadio ? costRadio.value : 'lastPurchase', openingQty: parseFloat(document.getElementById('item-opening-qty').value) || 0, openingValue: parseFloat(document.getElementById('item-opening-value').value) || 0, binLocation: document.getElementById('item-bin-location').value, maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0, minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0, reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0, baseUnit, bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1, units, description: document.getElementById('item-description').value };
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const duplicateName = items.find(i => i.name.trim() === name.trim() && i.id !== id);
            if (duplicateName) { alert('هذا الصنف مسجل مسبقاً بنفس الاسم.'); return; }
            
            for (const b of newBarcodes) {
                const duplicateBarcode = items.find(i => i.id !== id && (i.barcode === b || (i.units && i.units.some(u => u.barcode === b))));
                if (duplicateBarcode) {
                    alert('الباركود (' + b + ') مستخدم مسبقاً في صنف آخر: ' + duplicateBarcode.name);
                    return;
                }
            }
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) { if(items[ei].units) { itemData.units.forEach((u,i) => { if(items[ei].units[i]) u.stock = items[ei].units[i].stock || 0; }); } items[ei] = itemData; }
            else { itemData.units[0].stock = itemData.openingQty; items.push(itemData); }
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            closeItemForm(); renderItems();
        }

        function renderItems() {
            const tbody = document.getElementById('items-table-body');
            if(!tbody) return;
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const totalEl = document.getElementById('kpi-total-items');
            if(totalEl) totalEl.innerText = items.length + ' صنف';
            
            let lowStock = 0;
            items.forEach(i => {
                let stock = i.units && i.units[0] ? (i.units[0].stock || 0) : 0;
                if(stock <= (i.minLimit || 5)) lowStock++;
            });
            const lowEl = document.getElementById('kpi-low-stock');
            if(lowEl) lowEl.innerText = lowStock + ' صنف';
            
            if (currentItemSearchQuery) {
                items = items.filter(i => {
                    const unitBarcodes = i.units ? i.units.map(u => u.barcode || '').join(' ') : '';
                    const str = (i.id + ' ' + i.name + ' ' + (i.category||'') + ' ' + (i.barcode||'') + ' ' + unitBarcodes).toLowerCase();
                    return str.includes(currentItemSearchQuery);
                });
            }
            
            const totalPages = Math.ceil(items.length / itemsPerPage) || 1;
            if (currentPageItems > totalPages) currentPageItems = totalPages;
            const start = (currentPageItems - 1) * itemsPerPage;
            const paginatedItems = items.slice(start, start + itemsPerPage);
            
            tbody.innerHTML = '';
            paginatedItems.forEach(i => {
                let bu = i.baseUnit || (i.units && i.units[0] ? i.units[0].unitName : '-');
                let price = i.sellingPrice || (i.units && i.units[0] ? i.units[0].cashPrice : 0);
                const badge = i.status === 'inactive' ? '<span style="color:var(--danger-color); font-size:0.8rem; font-weight:bold; background:rgba(239,68,68,0.1); padding:3px 8px; border-radius:6px;">🔴 موقوف</span>' : '<span style="color:var(--success-color); font-size:0.8rem; font-weight:bold; background:rgba(52,211,153,0.1); padding:3px 8px; border-radius:6px;">🟢 نشط</span>';
                const subCat = i.subCategory ? ' <small style="color:var(--text-secondary);">(' + i.subCategory + ')</small>' : '';
                tbody.insertAdjacentHTML('beforeend', `<tr class="item-row"><td class="item-id-cell" style="font-weight:bold; color:#38bdf8;">${i.id}</td><td class="item-name-cell" style="font-weight:bold;">${i.name}${subCat}</td><td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${i.category || '-'}</span></td><td>${bu}</td><td style="font-weight:bold; color:var(--success-color);">${price.toLocaleString()} SDG</td><td>${badge}</td><td><button class="btn-text" style="color:#a855f7; font-size:1.1rem; margin-left:8px;" onclick="editItem('${i.id}')" title="تعديل">✏️</button><button class="btn-text" style="color:var(--danger-color); font-size:1.1rem;" onclick="deleteItem('${i.id}')" title="حذف">🗑️</button></td></tr>`);
            });
            
            renderItemsPagination(totalPages);
            renderCategoriesList();
        }
        
        function renderItemsPagination(totalPages) {
            let pagContainer = document.getElementById('items-pagination-container');
            if (!pagContainer) {
                const tableWrap = document.querySelector('#inner-products .table-responsive');
                if (tableWrap) {
                    tableWrap.insertAdjacentHTML('afterend', '<div id="items-pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding:10px 20px; background:rgba(0,0,0,0.2); border-radius:10px;"></div>');
                    pagContainer = document.getElementById('items-pagination-container');
                }
            }
            if (pagContainer) {
                pagContainer.innerHTML = `
                    <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems>1){currentPageItems--; renderItems();}">السابق</button>
                    <span style="color:var(--text-secondary); font-weight:bold;">صفحة ${currentPageItems} من ${totalPages}</span>
                    <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems<${totalPages}){currentPageItems++; renderItems();}">التالي</button>
                `;
            }
        }

        let currentPageItems = 1;
        const itemsPerPage = 20;
        let currentItemSearchQuery = '';

        let itemSearchTimeout = null;
        function filterItemsTable() {
            clearTimeout(itemSearchTimeout);
            itemSearchTimeout = setTimeout(() => {
                currentItemSearchQuery = (document.getElementById('item-search').value || '').toLowerCase();
                currentPageItems = 1;
                renderItems();
            }, 300);
        }

        function quickAddUnit() {
            const name = prompt('أدخل اسم الوحدة الجديدة:');
            if(name) {
                let units = [];
                try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
                if(!units.includes(name)) {
                    units.push(name);
                    localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
                    renderGlobalUnitsList();
                    updateUnitDropdowns(name);
                }
            }
        }

        function buildUnitRow(num, u, isBase) {
            const dis = isBase ? 'disabled' : '';
            const bg = isBase ? 'style="background:rgba(52,211,153,0.08); border-right:3px solid var(--success-color);"' : '';
            const act = isBase ? '<span style="font-size:0.75rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); padding:3px 8px; border-radius:4px;">أساسية</span>' : "<button class=\"btn-text\" style=\"color:var(--danger-color);\" onclick=\"this.closest('tr').remove()\">✖</button>";
            const idAttr = isBase ? ' id="base-unit-name-display"' : ' class="unit-name-input"';
            
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            const val = u.unitName || '';
            if (val && !units.includes(val)) units.push(val); // Ensure backward compat display
            
            let opts = `<option value="">-- اختر --</option>` + units.map(un => `<option value="${un}" ${un === val ? 'selected' : ''}>${un}</option>`).join('');
            
            let unitField = isBase ? 
                `<input type="text" id="base-unit-name-display" value="${val}" disabled style="width:100px; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; padding:5px; border-radius:6px;">` : 
                `<select ${idAttr} style="width:120px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:bold; text-align:center; padding:5px; border-radius:6px; outline:none;">${opts}</select>`;

            return '<tr ' + bg + '><td>' + num + '</td><td>' + unitField + '</td><td><input type="number" value="' + (u.factor || 1) + '" ' + dis + ' style="width:60px; background:transparent; border:' + (isBase ? 'none' : '1px solid rgba(255,255,255,0.15)') + '; color:' + (isBase ? 'var(--success-color)' : '#fff') + '; font-weight:bold; text-align:center; padding:5px; border-radius:6px;"></td><td><input type="text" value="' + (u.barcode || '') + '" placeholder="باركود..." style="width:100px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.cashPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px; font-weight:bold;"></td><td><input type="number" value="' + (u.creditPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.minPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.weight || '') + '" placeholder="0.00" style="width:60px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td>' + act + '</td></tr>';
        }

        function editItem(id) {
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const item = items.find(i => i.id === id);
            if(!item) return;
            document.getElementById('form-mode-indicator').innerText = 'تعديل صنف: ' + item.name;
            document.getElementById('item-code').value = item.id;
            document.getElementById('item-name-input').value = item.name;
            document.getElementById('item-name-latin').value = item.nameLatin || '';
            document.getElementById('item-barcode').value = item.barcode || '';
            updateCategoryDropdowns();
            document.getElementById('item-category').value = item.category || '';
            document.getElementById('item-sub-category').value = item.subCategory || '';
            document.getElementById('item-nature').value = item.nature || 'مخزني';
            document.getElementById('item-brand').value = item.brand || '';
            document.getElementById('item-active-toggle').checked = item.status !== 'inactive';
            updateToggleLabel();
            document.getElementById('item-selling-price').value = item.sellingPrice || '';
            document.getElementById('item-max-discount').value = item.maxDiscount || '';
            document.getElementById('item-taxable').checked = item.taxable || false;
            document.getElementById('item-tax-rate').value = item.taxRate || '';
            document.getElementById('item-tax-rate').disabled = !item.taxable;
            const cm = document.querySelector('input[name="cost_method"][value="' + (item.costMethod || 'lastPurchase') + '"]');
            if(cm) cm.checked = true;
            document.getElementById('item-opening-qty').value = item.openingQty || '';
            document.getElementById('item-opening-value').value = item.openingValue || '';
            document.getElementById('item-bin-location').value = item.binLocation || '';
            document.getElementById('item-max-limit').value = item.maxLimit || '';
            document.getElementById('item-min-limit').value = item.minLimit || '';
            document.getElementById('item-reorder-level').value = item.reorderLevel || '';
            document.getElementById('item-base-unit').value = item.baseUnit || '';
            document.getElementById('item-bundle-factor').value = item.bundleFactor || 1;
            document.getElementById('item-description').value = item.description || '';
            updateBaseUnitRow();
            const tbody = document.getElementById('units-matrix-body');
            tbody.innerHTML = '';
            if(item.units && item.units.length > 0) { item.units.forEach((u, idx) => { tbody.insertAdjacentHTML('beforeend', buildUnitRow(idx + 1, u, idx === 0)); }); }
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        function deleteItem(id) {
            if(confirm('هل أنت متأكد من حذف هذا الصنف؟')) {
                let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
                const idx = items.findIndex(i => i.id === id); if(idx > -1) { items[idx].deleted = true; items[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_items_v1', JSON.stringify(items));
                renderItems();
            }
        }

        function openItemForm() {
            const tabItems = document.getElementById('tab-items');
            if(tabItems) {
                tabItems.querySelectorAll('.inner-tab-content').forEach(c => c.classList.remove('active'));
                tabItems.querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
                const productsTab = document.getElementById('inner-products');
                if(productsTab) productsTab.classList.add('active');
                const productsBtn = tabItems.querySelector('button[onclick*="inner-products"]');
                if(productsBtn) productsBtn.classList.add('active');
            }
            
            document.getElementById('form-mode-indicator').innerText = 'إضافة صنف جديد';
            document.getElementById('item-code').value = '';
            document.getElementById('item-name-input').value = '';
            document.getElementById('item-name-latin').value = '';
            document.getElementById('item-barcode').value = '';
            document.getElementById('item-sub-category').value = '';
            document.getElementById('item-brand').value = '';
            document.getElementById('item-active-toggle').checked = true;
            updateToggleLabel();
            document.getElementById('item-selling-price').value = '';
            document.getElementById('item-max-discount').value = '';
            document.getElementById('item-taxable').checked = false;
            document.getElementById('item-tax-rate').value = '';
            document.getElementById('item-tax-rate').disabled = true;
            document.querySelector('input[name="cost_method"][value="lastPurchase"]').checked = true;
            document.getElementById('item-opening-qty').value = '';
            document.getElementById('item-opening-value').value = '';
            document.getElementById('item-bin-location').value = '';
            document.getElementById('item-max-limit').value = '';
            document.getElementById('item-min-limit').value = '';
            document.getElementById('item-reorder-level').value = '';
            document.getElementById('item-base-unit').value = '';
            document.getElementById('item-bundle-factor').value = '1';
            document.getElementById('item-description').value = '';
            updateCategoryDropdowns();
            document.getElementById('units-matrix-body').innerHTML = buildUnitRow(1, { unitName: '', factor: 1 }, true);
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        function addUnitRow() {
            const tbody = document.getElementById('units-matrix-body');
            if(!tbody) return;
            const count = tbody.querySelectorAll('tr').length + 1;
            tbody.insertAdjacentHTML('beforeend', buildUnitRow(count, { unitName: '', factor: 1 }, false));
        }

        // ================= UNITS LOGIC =================
        function saveGlobalUnit() {
            const name = document.getElementById('unit-name-input').value;
            if(!name) { alert('أدخل اسم الوحدة'); return; }
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            if(!units.includes(name)) { 
                units.push(name); 
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units)); 
            }
            document.getElementById('unit-name-input').value = '';
            renderGlobalUnitsList();
            updateUnitDropdowns();
        }

        function renderGlobalUnitsList() {
            const tbody = document.getElementById('units-table-body');
            if(!tbody) return;
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            tbody.innerHTML = '';
            units.forEach(u => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td><strong style="color:#38bdf8;">${u}</strong></td>
                    <td><button class="btn-text" style="color:var(--danger-color);" onclick="deleteGlobalUnit('${u}')">حذف</button></td>
                </tr>`);
            });
        }

        function deleteGlobalUnit(name) {
            if(!confirm('هل أنت متأكد من حذف هذه الوحدة؟')) return;
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            units = units.filter(u => u !== name);
            localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            renderGlobalUnitsList();
            updateUnitDropdowns();
        }

        function importLegacyUnits() {
            // Import units that were manually typed in older items
            let items = [];
            try { items = JSON.parse(localStorage.getItem('erp_items_v1')) || []; } catch(e) {}
            if(items.length === 0) return;
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            let added = false;
            items.forEach(i => {
                if(i.baseUnit && !units.includes(i.baseUnit)) { units.push(i.baseUnit); added = true; }
                if(i.units) {
                    i.units.forEach(u => {
                        if(u.unitName && !units.includes(u.unitName)) { units.push(u.unitName); added = true; }
                    });
                }
            });
            if(added) {
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            }
        }

        function updateUnitDropdowns(selectedValue = '') {
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            
            const options = units.map(u => `<option value="${u}">${u}</option>`).join('');
            const baseSelect = document.getElementById('item-base-unit');
            if(baseSelect) {
                const current = selectedValue || baseSelect.value;
                baseSelect.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                if(current) baseSelect.value = current;
            }
            
            // Also update any dynamic selects in the matrix
            document.querySelectorAll('.unit-name-input').forEach(sel => {
                if(sel.tagName === 'SELECT') {
                    const current = sel.value;
                    sel.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                    if(current) sel.value = current;
                }
            });
        }

        // ================= CATEGORY LOGIC =================
        function saveCategory() {
            const name = document.getElementById('cat-name-input').value;
            if(!name) { alert('أدخل اسم الفئة'); return; }
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            document.getElementById('cat-name-input').value = '';
            renderCategoriesList();
            updateCategoryDropdowns();
        }

        function renderCategoriesList() {
            const tbody = document.getElementById('categories-table-body');
            if(!tbody) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const kpi = document.getElementById('kpi-total-categories');
            if(kpi) kpi.innerText = cats.length + ' فئة';
            tbody.innerHTML = '';
            cats.forEach(c => {
                const count = items.filter(i => i.category === c).length;
                const subs = [...new Set(items.filter(i => i.category === c && i.subCategory).map(i => i.subCategory))];
                const subHtml = subs.length > 0 ? '<br><small style="color:#a855f7;">' + subs.join(' | ') + '</small>' : '';
                tbody.insertAdjacentHTML('beforeend', '<tr><td style="font-weight:bold; color:#a855f7;">' + c + subHtml + '</td><td><span style="background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px;">' + count + '</span></td><td><button class="btn-text" style="color:#38bdf8; margin-left:10px;" onclick="editCategory(\'' + c + '\')">✏️ تعديل</button><button class="btn-text" style="color:var(--danger-color);" onclick="deleteCategory(\'' + c + '\')">🗑️ حذف</button></td></tr>');
            });
        }

        function editCategory(oldName) {
            const newName = prompt('تعديل اسم الفئة:', oldName);
            if(!newName || newName === oldName) return;
            let cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || [];
            const idx = cats.indexOf(oldName);
            if(idx > -1) { cats[idx] = newName; localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            // Update all items that use this category
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            items.forEach(i => { if(i.category === oldName) i.category = newName; });
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            renderCategoriesList();
            updateCategoryDropdowns();
            renderItems();
        }

        function deleteCategory(name) {
            if(confirm('هل أنت متأكد من حذف الفئة؟')) {
                let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
                cats = cats.filter(c => c !== name);
                localStorage.setItem('erp_categories_v1', JSON.stringify(cats));
                renderCategoriesList();
                updateCategoryDropdowns();
            }
        }

        function updateCategoryDropdowns() {
            const select = document.getElementById('item-category');
            if(!select) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            const val = select.value;
            select.innerHTML = '';
            cats.forEach(c => { select.insertAdjacentHTML('beforeend', '<option value="' + c + '">' + c + '</option>'); });
            if(cats.includes(val)) select.value = val;
        }


        // ================= COST CENTERS LOGIC =================
        function openCostCenterForm() {
            document.getElementById('costcenter-form').style.display = 'block';
            document.getElementById('btn-cancel-costcenter').style.display = 'block';
            document.getElementById('costcenter-id').value = '';
            document.getElementById('costcenter-code').value = 'CC-' + Date.now();
            document.getElementById('costcenter-name').value = '';
            document.getElementById('costcenter-desc').value = '';
        }

        function closeCostCenterForm() {
            document.getElementById('costcenter-form').style.display = 'none';
        }

        function saveCostCenter() {
            const id = document.getElementById('costcenter-id').value || document.getElementById('costcenter-code').value || 'CC-' + Date.now();
            const name = document.getElementById('costcenter-name').value;
            const desc = document.getElementById('costcenter-desc').value;
            
            if(!name) { alert('الرجاء إدخال اسم مركز التكلفة'); return; }

            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || [];
            } catch(e) { ccs = []; }
            
            const existingIdx = ccs.findIndex(c => c.id === id);
            
            const ccData = {
                id, name, desc,
                createdAt: Date.now()
            };

            if (existingIdx > -1) {
                ccs[existingIdx] = { ...ccs[existingIdx], ...ccData };
            } else {
                ccs.push(ccData);
            }

            localStorage.setItem('erp_jobs_v1', JSON.stringify(ccs));
            closeCostCenterForm();
            renderCostCenters();
        }

        function renderCostCenters() {
            const tbody = document.getElementById('costcenters-tbody');
            if(!tbody) return;
            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || [];
            } catch(e) { ccs = []; }
            
            tbody.innerHTML = '';
            ccs.forEach(c => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="color:#38bdf8; font-weight:bold;">${c.id}</td>
                    <td style="font-weight:bold;">${c.name || c.title || ''}</td>
                    <td>${c.desc || ''}</td>
                    <td>
                        <button class="btn-text" onclick="editCostCenter('${c.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCostCenter('${c.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editCostCenter(id) {
            let ccs = [];
            try { ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) { ccs = []; }
            const cc = ccs.find(c => c.id === id);
            if(!cc) return;
            
            document.getElementById('costcenter-id').value = cc.id;
            document.getElementById('costcenter-code').value = cc.id;
            document.getElementById('costcenter-name').value = cc.name || cc.title || '';
            document.getElementById('costcenter-desc').value = cc.desc || '';
            
            document.getElementById('costcenter-form').style.display = 'block';
            document.getElementById('btn-cancel-costcenter').style.display = 'block';
            document.getElementById('costcenter-form').scrollIntoView({ behavior: 'smooth' });
        }

        function deleteCostCenter(id) {
            if(confirm('هل أنت متأكد من حذف مركز التكلفة؟')) {
                let ccs = [];
                try { ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) { ccs = []; }
                ccs = ccs.filter(c => c.id !== id);
                localStorage.setItem('erp_jobs_v1', JSON.stringify(ccs));
                renderCostCenters();
            }
        }

        function goBackOrExit() {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                window.history.back();
            } else {
                window.location.href = 'index.php';
            }
        }


document.addEventListener('DOMContentLoaded', () => {
    // Units Init
    importLegacyUnits();
    renderGlobalUnitsList();
    updateUnitDropdowns();
    
    setTimeout(() => {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get('tab');
        if (tabParam) {
            const tabId = 'tab-' + tabParam;
            const targetBtn = document.querySelector(`button[onclick*="'${tabId}'"]`);
            if (targetBtn) {
                targetBtn.click();
            }
        }
    }, 100);
});
