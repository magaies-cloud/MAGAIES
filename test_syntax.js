
        // Unregister service workers to avoid aggressive caching of API
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
            });
        }

        let SAAS_TOKEN = localStorage.getItem('saas_token');
        let allTenants = [];
        let allMessagesData = [];
        let allPlans = [];
        let allPartners = [];
        // Must be declared before initDashboard() is called (Nginx: PATH_INFO not supported)
        let useQueryParams = true;
        
        

        if (SAAS_TOKEN) {
            document.getElementById('login-modal').style.display = 'none';
            document.getElementById('dashboard').style.display = 'flex';
            initDashboard();
        } else {
            document.getElementById('login-modal').style.display = 'flex';
        }

        async function loginSaas() {
            const pass = document.getElementById('saas-pass').value.trim();
            if (!pass) return alert('الرجاء إدخال الرمز');
            
            const btn = document.querySelector('.login-box button');
            const oldText = btn.innerText;
            btn.innerText = 'جاري التحقق...';
            btn.disabled = true;

            try {
                const res = await fetch('api_saas_auth.php?_=' + Date.now(), {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pass })
                });
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    alert('استجابة غير مفهومة من الخادم: ' + text.substring(0, 60));
                    throw e;
                }
                
                if (data.success) {
                    SAAS_TOKEN = data.token;
                    localStorage.setItem('saas_token', SAAS_TOKEN);
                    alert("تم التحقق بنجاح! جاري تحويلك للوحة التحكم...");
                    location.reload();
                } else {
                    alert(data.error || 'رمز خاطئ');
                }
            } catch (e) { 
                console.error(e);
            } finally {
                btn.innerText = oldText;
                btn.disabled = false;
            }
        }

        function logoutSaas() { localStorage.removeItem('saas_token'); location.reload(); }

        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }

        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        
        function toggleDropdown(id) {
            const el = document.getElementById('drop-' + id);
            if(el.classList.contains('show')) el.classList.remove('show');
            else {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('show'));
                el.classList.add('show');
            }
        }
        
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.btn-outline')) {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('show'));
            }
        });

        async function initDashboard() {
            await loadTenants();
            loadGlobalSettings();
            loadMessages();
            try {
                const resPub = await fetch('api_index.php?r=public');
                const dataPub = await resPub.json();
                allPlans = dataPub.plans || [];
                allPartners = dataPub.partners || [];
            } catch(e) {}
            renderPlans();
            renderPartners();
        }

        // Helper: build API URL using query params (Nginx doesn't support PHP PATH_INFO)
        function apiUrl(resource, action, subAction) {
            let url = `api_index.php?r=${resource}&a=${action}`;
            if (subAction) url += `&a2=${subAction}`;
            return url;
        }

        async function loadTenants() {
            const tbody = document.getElementById('tenants-body');
            try {
                const url = apiUrl('saas', 'tenants');
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                console.log('Tenants API response:', res.status, data);

                if (Array.isArray(data)) {
                    allTenants = data;
                    renderTenantsTable();
                    populateUserTenantSelect();
                    updateKPIs();
                } else {
                    const msg = data.error || JSON.stringify(data);
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">❌ خطأ: ${msg}</td></tr>`;
                    console.error('API error:', data);
                }
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">❌ فشل الاتصال: ${e.message}</td></tr>`;
                console.error('Fetch error:', e);
            }
        }

        function updateKPIs() {
            let active = 0, suspended = 0, expiring = 0, revenue = 0;
            const now = new Date();
            const next30 = new Date(); next30.setDate(now.getDate() + 30);
            
            allTenants.forEach(t => {
                if(t.status === 'active') active++;
                if(t.status === 'suspended') suspended++;
                if(t.valid_until && new Date(t.valid_until) < next30 && new Date(t.valid_until) > now) expiring++;
                
                // Mock revenue calculation based on plans
                let planId = localStorage.getItem('tenant_plan_' + t.id) || 1;
                let plan = allPlans.find(p => p.id == planId);
                if(plan && t.status === 'active') revenue += parseInt(plan.price);
            });
            
            document.getElementById('kpi-total-tenants').innerText = allTenants.length;
            document.getElementById('kpi-active-tenants').innerText = active;
            document.getElementById('kpi-suspended-tenants').innerText = suspended;
            document.getElementById('kpi-revenue').innerText = '$' + revenue;
        }

        function filterTenants() { renderTenantsTable(); }

        function renderTenantsTable() {
            const tbody = document.getElementById('tenants-body');
            tbody.innerHTML = '';
            const search = (document.getElementById('tenant-search').value || '').toLowerCase();
            const filterEl = document.getElementById('tenant-filter-status');
            const statusFilter = filterEl ? filterEl.value : 'all';

            const filtered = allTenants.filter(t => {
                // Null-safe: some DB records may have null company_name or subdomain
                const companyName = (t.company_name || '').toLowerCase();
                const subdomain   = (t.subdomain || '').toLowerCase();
                const mSearch = companyName.includes(search) || subdomain.includes(search);
                const mStatus = statusFilter === 'all' || t.status === statusFilter;
                return mSearch && mStatus;
            });

            filtered.forEach(t => {
                try {
                    const statusBadge = t.status === 'active'
                        ? '<span class="badge badge-active">نشط</span>'
                        : '<span class="badge badge-suspended">موقوف</span>';
                    let validStr = '<span style="color:#ef4444">غير محدد</span>';
                    if(t.valid_until) {
                        const validDate = new Date(t.valid_until);
                        validStr = `<span style="color:${validDate < new Date() ? '#ef4444' : '#10b981'}; direction:ltr; display:inline-block;">${validDate.toLocaleDateString()}</span>`;
                    }

                    let planId = localStorage.getItem('tenant_plan_' + t.id) || 1;
                    let plan = allPlans.find(p => p.id == planId) || allPlans[0] || {name: 'غير محدد', maxUsers: '-', storage: '-'};
                    let maxUsers = localStorage.getItem('tenant_max_users_' + t.id) || plan.maxUsers || '-';

                    let storageBytes = parseInt(t.storage_used) || 0;
                    let storageStr = storageBytes + ' B';
                    if (storageBytes >= 1073741824) storageStr = (storageBytes / 1073741824).toFixed(2) + ' GB';
                    else if (storageBytes >= 1048576) storageStr = (storageBytes / 1048576).toFixed(2) + ' MB';
                    else if (storageBytes >= 1024) storageStr = (storageBytes / 1024).toFixed(2) + ' KB';

                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${t.company_name || '—'}</strong><br><small style="color:var(--text-secondary)">${t.subdomain || '—'}.elsajana.com</small></td>
                            <td><span style="color:var(--primary-accent); font-weight: bold;">${t.admin_username || 'غير متوفر'}</span></td>
                            <td>${t.owner_name || '-'}<br><small>${t.owner_phone || ''}</small></td>
                            <td><span class="badge badge-plan">${plan.name}</span><br><small style="color:var(--text-secondary)">الحد: ${maxUsers} مستخدم</small></td>
                            <td style="direction: ltr;">${storageStr} <br><small style="color:var(--text-secondary)">الحد: ${plan.storage || '-'} GB</small></td>
                            <td>${statusBadge}</td>
                            <td>${validStr}</td>
                            <td>
                                <div class="dropdown" id="drop-${t.id}">
                                    <button class="btn-outline" onclick="toggleDropdown(${t.id}); event.stopPropagation();">إجراءات ▼</button>
                                    <div class="dropdown-content">
                                        <button onclick="openEditModal(${t.id})">⚙️ تعديل الصلاحية والباقة</button>
                                        <button onclick="deleteTenant(${t.id})" style="color:var(--danger-color)">🗑️ حذف</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                } catch(rowErr) {
                    console.error('Error rendering tenant row:', t, rowErr);
                }
            });
            if(filtered.length === 0) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">لا يوجد بيانات</td></tr>';
        }

        // Add User & Reset Pass Logic (Simulated for SaaS)
        function populateUserTenantSelect() {
            const s = document.getElementById('users-tenant-select');
            s.innerHTML = '<option value="">-- اختر شركة --</option>';
            allTenants.forEach(t => {
                s.innerHTML += `<option value="${t.id}">${t.company_name}</option>`;
            });
        }
        
        function loadTenantUsers() {
            const tenantId = document.getElementById('users-tenant-select').value;
            const tbody = document.getElementById('tenant-users-body');
            if(!tenantId) { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">اختر شركة לעرض مستخدميها</td></tr>'; return; }
            
            // Mock users for the tenant
            let users = JSON.parse(localStorage.getItem('saas_users_t' + tenantId) || '[]');
            if(users.length === 0) {
                users = [{id: 1, username: 'admin', fullName: 'مدير النظام', role: 'admin'}];
                localStorage.setItem('saas_users_t' + tenantId, JSON.stringify(users));
            }
            
            tbody.innerHTML = '';
            users.forEach(u => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${u.username}</strong></td>
                        <td>${u.fullName}</td>
                        <td><span class="badge ${u.role==='admin'?'badge-plan':'badge-active'}">${u.role}</span></td>
                        <td><button class="btn-primary" style="padding:5px 10px; font-size:0.8rem;" onclick="openResetPassModal(${tenantId}, ${u.id})">إعادة تعيين كلمة السر</button></td>
                    </tr>
                `;
            });
        }

        function openAddUserModal() {
            if(!document.getElementById('users-tenant-select').value) return alert('الرجاء اختيار الشركة أولاً من قائمة المستخدمين');
            document.getElementById('new-user-username').value = '';
            document.getElementById('new-user-fullname').value = '';
            document.getElementById('new-user-pass').value = '';
            document.getElementById('add-user-modal').style.display = 'flex';
        }
        
        function saveNewUser() {
            const tenantId = document.getElementById('users-tenant-select').value;
            let users = JSON.parse(localStorage.getItem('saas_users_t' + tenantId) || '[]');
            users.push({
                id: Date.now(),
                username: document.getElementById('new-user-username').value,
                fullName: document.getElementById('new-user-fullname').value,
                role: document.getElementById('new-user-role').value
            });
            localStorage.setItem('saas_users_t' + tenantId, JSON.stringify(users));
            closeModal('add-user-modal');
            loadTenantUsers();
            alert('تمت إضافة المستخدم للشركة بنجاح!');
        }

        function openResetPassModal(tenantId, userId) {
            document.getElementById('target-user-id').value = userId;
            document.getElementById('user-action-modal').style.display = 'flex';
        }
        function saveUserPassword() {
            alert('تم تغيير كلمة المرور بنجاح!');
            closeModal('user-action-modal');
        }

        // Plans Logic
        function renderPlans() {
            const tbody = document.getElementById('plans-body');
            tbody.innerHTML = '';
            allPlans.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong style="color:var(--primary-accent)">${p.name}</strong></td>
                        <td>$${p.price}</td>
                        <td>${p.maxUsers} مستخدم</td>
                        <td>${p.storage} GB</td>
                        <td><button class="btn-primary" style="padding:5px 10px; font-size:0.8rem;" onclick="editPlan(${p.id})">تعديل</button></td>
                    </tr>
                `;
            });
            
            const pSelect = document.getElementById('edit-tenant-plan');
            if(pSelect) {
                pSelect.innerHTML = '';
                allPlans.forEach(p => { pSelect.innerHTML += `<option value="${p.id}">${p.name}</option>`; });
            }
        }
        
        function openPlanModal() {
            document.getElementById('plan-id').value = '';
            document.getElementById('plan-name').value = '';
            document.getElementById('plan-price').value = '';
            document.getElementById('plan-users').value = '';
            document.getElementById('plan-storage').value = '';
            document.getElementById('plan-modal').style.display = 'flex';
        }
        function editPlan(id) {
            let p = allPlans.find(x => x.id === id);
            document.getElementById('plan-id').value = p.id;
            document.getElementById('plan-name').value = p.name;
            document.getElementById('plan-price').value = p.price;
            document.getElementById('plan-users').value = p.maxUsers;
            document.getElementById('plan-storage').value = p.storage;
            document.getElementById('plan-modal').style.display = 'flex';
        }
        async function savePlan() {
            let id = document.getElementById('plan-id').value;
            let p = {
                id: id ? parseInt(id) : Date.now(),
                name: document.getElementById('plan-name').value,
                price: document.getElementById('plan-price').value,
                maxUsers: document.getElementById('plan-users').value,
                storage: document.getElementById('plan-storage').value
            };
            if(id) {
                let idx = allPlans.findIndex(x => x.id == id);
                allPlans[idx] = p;
            } else { allPlans.push(p); }
            try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}
            closeModal('plan-modal');
            renderPlans();
            updateKPIs();
        }

        function updateMaxUsersFromPlan() {
            let planId = document.getElementById('edit-tenant-plan').value;
            let plan = allPlans.find(p => p.id == planId);
            if(plan) {
                document.getElementById('edit-tenant-max-users').value = plan.maxUsers;
            }
        }

        
        function renderPartners() {
            const tbody = document.getElementById('partners-body');
            tbody.innerHTML = '';
            allPartners.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${p.name}</strong></td>
                        <td><img src="${p.logo}" height="30" alt="logo"></td>
                        <td><a href="${p.link}" target="_blank" style="color:var(--primary-accent)">رابط</a></td>
                        <td>
                            <button class="btn-primary" style="padding:5px 10px; font-size:0.8rem;" onclick="editPartner(${p.id})">تعديل</button>
                            <button class="btn-danger" style="padding:5px 10px; font-size:0.8rem;" onclick="deletePartner(${p.id})">حذف</button>
                        </td>
                    </tr>
                `;
            });
        }
        function openPartnerModal() {
            document.getElementById('partner-id').value = '';
            document.getElementById('partner-name').value = '';
            document.getElementById('partner-logo').value = '';
            document.getElementById('partner-link').value = '';
            document.getElementById('partner-modal').style.display = 'flex';
        }
        function editPartner(id) {
            let p = allPartners.find(x => x.id === id);
            document.getElementById('partner-id').value = p.id;
            document.getElementById('partner-name').value = p.name;
            document.getElementById('partner-logo').value = p.logo;
            document.getElementById('partner-link').value = p.link;
            document.getElementById('partner-modal').style.display = 'flex';
        }
        async function savePartner() {
            let id = document.getElementById('partner-id').value;
            let p = {
                id: id ? parseInt(id) : Date.now(),
                name: document.getElementById('partner-name').value,
                logo: document.getElementById('partner-logo').value,
                link: document.getElementById('partner-link').value
            };
            if(id) {
                let idx = allPartners.findIndex(x => x.id == id);
                allPartners[idx] = p;
            } else { allPartners.push(p); }
            
            try { await fetch(apiUrl('saas', 'settings', 'partners'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ partners: allPartners }) }); } catch(e){}
            closeModal('partner-modal');
            renderPartners();
        }
        async function deletePartner(id) {
            if(!confirm('هل أنت متأكد من الحذف؟')) return;
            allPartners = allPartners.filter(x => x.id !== id);
            try { await fetch(apiUrl('saas', 'settings', 'partners'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ partners: allPartners }) }); } catch(e){}
            renderPartners();
        }

        // Original API calls ported
        function openEditModal(id) {
            const t = allTenants.find(x => x.id == id);
            if(t) {
                document.getElementById('edit-tenant-id').value = t.id;
                document.getElementById('edit-status').value = t.status || 'active';
                let planId = localStorage.getItem('tenant_plan_' + t.id) || 1;
                document.getElementById('edit-tenant-plan').value = planId;
                let plan = allPlans.find(p => p.id == planId) || allPlans[0];
                document.getElementById('edit-tenant-max-users').value = localStorage.getItem('tenant_max_users_' + t.id) || plan.maxUsers;
                if(t.valid_until) {
                    const d = new Date(t.valid_until);
                    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                    document.getElementById('edit-valid-until').value = d.toISOString().slice(0,16);
                } else document.getElementById('edit-valid-until').value = '';
                document.getElementById('edit-modal').style.display = 'flex';
            }
        }

        async function saveTenantUpdate() {
            const id = document.getElementById('edit-tenant-id').value;
            const status = document.getElementById('edit-status').value;
            let valid_until = document.getElementById('edit-valid-until').value;
            if(valid_until) valid_until = new Date(valid_until).toISOString().slice(0, 19).replace('T', ' ');
            
            localStorage.setItem('tenant_plan_' + id, document.getElementById('edit-tenant-plan').value);
            localStorage.setItem('tenant_max_users_' + id, document.getElementById('edit-tenant-max-users').value);

            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'update'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, status, valid_until })
                });
                const data = await res.json();
                if(data.success) { closeModal('edit-modal'); loadTenants(); }
                else alert(data.error);
            } catch(e) { alert('فشل الاتصال'); }
        }

        async function deleteTenant(id) {
            if(!confirm('هل أنت متأكد من الحذف النهائي؟')) return;
            try {
                const res = await fetch(`api_index.php?r=saas&a=tenants&a2=delete&id=${id}`, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                if(data.success) { alert('تم الحذف'); loadTenants(); }
            } catch(e) {}
        }

        async function loadGlobalSettings() {
            try {
                const res = await fetch(apiUrl('saas', 'settings', 'exchange-rate'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }});
                const data = await res.json();
                if(data) {
                    document.getElementById('global-exchange-rate').value = data.rate || 1.0;
                    document.getElementById('global-exchange-visible').checked = data.visible === true || data.visible === 1;
                }
            } catch(e) {}
        }

        async function saveGlobalSettings() {
            const rate = document.getElementById('global-exchange-rate').value;
            const visible = document.getElementById('global-exchange-visible').checked;
            try {
                const res = await fetch(apiUrl('saas', 'settings', 'exchange-rate'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ rate, visible })
                });
                alert('تم حفظ الإعدادات');
            } catch(e) {}
        }

        async function saveMasterToken() {
            const oldToken = document.getElementById('old-master-token').value;
            const token = document.getElementById('new-master-token').value;
            if(!oldToken || !token) return alert('أدخل الرمزين');
            try {
                const res = await fetch(apiUrl('saas', 'master-token'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ oldToken, token })
                });
                const data = await res.json();
                if(data.success) { alert('تم التغيير! سجل الدخول مجددا'); logoutSaas(); }
            } catch(e) {}
        }

        async function loadMessages() {
            try {
                const res = await fetch(apiUrl('saas', 'messages'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                allMessagesData = data || [];
                const tbody = document.getElementById('messages-body');
                tbody.innerHTML = '';
                if(allMessagesData.length === 0) tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">لا توجد رسائل</td></tr>';
                allMessagesData.forEach(m => {
                    tbody.innerHTML += `<tr><td>${m.title}</td><td>الجميع</td><td>${m.theme}</td><td><button class="btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="deleteMessage(${m.id})">حذف</button></td></tr>`;
                });
            } catch (e) {}
        }
        function openMessageModal() { document.getElementById('message-modal').style.display = 'flex'; }
        async function saveMessage() {
            const title = document.getElementById('msg-title').value;
            const body = document.getElementById('msg-body').value;
            try {
                await fetch(apiUrl('saas', 'messages'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ title, message: body, target_tenants: ['all'], theme: document.getElementById('msg-theme').value, show_once: document.getElementById('msg-show-once').checked ? 1 : 0 })
                });
                closeModal('message-modal'); loadMessages();
            } catch(e) {}
        }
        async function deleteMessage(id) {
            if(!confirm('حذف الرسالة؟')) return;
            try {
                await fetch(apiUrl('saas', 'messages'), { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ id }) });
                loadMessages();
            } catch(e) {}
        }
    