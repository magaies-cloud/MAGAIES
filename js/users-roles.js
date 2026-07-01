// c:\mynewapp\frontend-pwa\js\users-roles.js

// ════════════════════════════════════════
//  DATA INITIALIZATION
// ════════════════════════════════════════
const MODULES = [
    { id: 'dashboard', name: 'لوحة التحكم' },
    { id: 'sales', name: 'المبيعات' },
    { id: 'purchase', name: 'المشتريات' },
    { id: 'inventory', name: 'المخزون والجرد' },
    { id: 'finance', name: 'المعاملات المالية' },
    { id: 'master_data', name: 'البيانات الأساسية' },
    { id: 'reports', name: 'التقارير التحليلية' },
    { id: 'settings', name: 'الإعدادات والصلاحيات' },
    { id: 'inventory_rules', name: 'قواعد المخزون والعمليات' }
];

function safeGetJSON(key, defaultVal) {
    try {
        let val = localStorage.getItem(key);
        if (val && val !== 'undefined') return JSON.parse(val);
    } catch(e) {}
    return defaultVal;
}

let ROLES = [];
let USERS = [];

function loadData() {
    ROLES = safeGetJSON('erp_roles_v1', []);
    USERS = safeGetJSON('erp_users_v1', []);

    let changes = false;
    // Seed Default Roles and Admin User if empty
    if (ROLES.length === 0) {
        const defaultPermissions = {};
        MODULES.forEach(m => {
            defaultPermissions[m.id] = { view: true, add: true, edit: true, delete: true };
        });
        
        ROLES.push({
            id: 'role_admin_default',
            name: 'مدير نظام',
            securityLevel: 5,
            permissions: defaultPermissions,
            isSystem: true // cannot be deleted
        });
        changes = true;
    }

    if (USERS.length === 0) {
        let defaultUsername = 'admin';
        try {
            const currentUserStr = localStorage.getItem('user');
            if (currentUserStr) {
                const cu = JSON.parse(currentUserStr);
                if (cu.username) defaultUsername = cu.username;
            }
        } catch(e) {}

        USERS.push({
            id: 'u_admin_default',
            username: defaultUsername,
            password: '', // Should be set by user securely
            fullName: 'مدير النظام',
            roleId: ROLES[0].id,
            warehouse: 'all',
            status: 'active',
            isSystem: true // cannot be deleted
        });
        changes = true;
    }

    if (changes) {
        localStorage.setItem('erp_roles_v1', JSON.stringify(ROLES));
        localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
    }
}

// ════════════════════════════════════════
//  DOM CONTENT LOADED
// ════════════════════════════════════════
document.addEventListener('DBReady', () => {
    // Basic Authentication Check
    if (!localStorage.getItem('authToken')) {
        window.location.href = 'login.php';
        return;
    }

    // Load data safely now that DB is ready
    loadData();

    // Initialize Dropdowns
    populateRoleDropdown();
    populateWarehouseDropdown();

    // Render Tables
    renderRolesTable();
    renderUsersTable();
});

// ════════════════════════════════════════
//  TOAST NOTIFICATION (Utility)
// ════════════════════════════════════════
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    let icon = type === 'success' ? '✅' : type === 'error' ? '❌' : '⚠️';
    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }, duration);
}

function flashError(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    el.classList.add('error-flash');
    setTimeout(() => el.classList.remove('error-flash'), 800);
}

// ════════════════════════════════════════
//  USERS MANAGEMENT
// ════════════════════════════════════════
function populateRoleDropdown() {
    const select = document.getElementById('user-role');
    select.innerHTML = '<option value="">اختر الدور الوظيفي...</option>';
    ROLES.forEach(r => {
        select.innerHTML += `<option value="${r.id}">${r.name} (أمان: ${r.securityLevel})</option>`;
    });
}

function populateWarehouseDropdown() {
    const select = document.getElementById('user-warehouse');
    select.innerHTML = '<option value="all">الكل (بدون تقييد)</option>';
    const WAREHOUSES = safeGetJSON('erp_warehouses_v1', []);
    WAREHOUSES.forEach(w => {
        select.innerHTML += `<option value="${w.id}">${w.name}</option>`;
    });
}

function renderUsersTable() {
    const tbody = document.getElementById('users-table-body');
    tbody.innerHTML = '';

    let currentUser = { isSystem: true };
    try {
        const currentUserStr = localStorage.getItem('user');
        if(currentUserStr && currentUserStr !== 'undefined') currentUser = JSON.parse(currentUserStr);
    } catch(e) { console.error("Error parsing user:", e); }

    USERS.forEach(user => {
        const role = ROLES.find(r => r.id === user.roleId) || { name: 'غير معروف' };
        let statusBadge = user.status === 'active' 
            ? `<span class="badge badge-user">نشط</span>` 
            : user.status === 'forced_logout' ? `<span class="badge" style="background:#f59e0b; color:#fff; border-color:#f59e0b;">مطرود</span>` : `<span class="badge badge-admin">موقوف</span>`;
            
        let warehouseName = 'الكل';
        if (user.warehouse !== 'all') {
            const WAREHOUSES = safeGetJSON('erp_warehouses_v1', []);
            const w = WAREHOUSES.find(wh => wh.id === user.warehouse);
            if (w) warehouseName = w.name;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight: bold; color: var(--primary-accent);">${user.username}</td>
            <td>${user.fullName}</td>
            <td>${role.name}</td>
            <td>${warehouseName}</td>
            <td>${statusBadge}</td>
            <td>
                <button class="action-btn edit-btn" onclick="editUser('${user.id}')" title="تعديل">✏️</button>
                ${!user.isSystem ? `<button class="action-btn del-btn" onclick="deleteUser('${user.id}')" title="حذف">🗑️</button>` : ''}
                ${(!user.isSystem && user.status === 'active') ? `<button class="action-btn" style="color:#f59e0b;" onclick="forceLogoutUser('${user.id}')" title="إجبار على الخروج">🚪</button>` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function showUserForm() {
    document.getElementById('user-form-container').style.display = 'block';
    document.getElementById('user-form-title').innerText = 'إضافة مستخدم جديد';
    
    // Clear form
    document.getElementById('user-id').value = '';
    document.getElementById('user-fullname').value = '';
    document.getElementById('user-username').value = '';
    document.getElementById('user-password').value = '';
    document.getElementById('user-phone').value = '';
    document.getElementById('user-role').value = '';
    document.getElementById('user-warehouse').value = 'all';
    document.getElementById('user-status').value = 'active';
    
    // Clear Geo
    document.getElementById('user-geo-restricted').checked = false;
    document.getElementById('user-geo-lat').value = '';
    document.getElementById('user-geo-lng').value = '';
    document.getElementById('user-geo-radius').value = '100';
    toggleGeoFields();

    // Clear Device
    document.getElementById('user-device-restricted').checked = false;
    document.getElementById('user-device-uuid').value = '';
    toggleDeviceFields();

    // Clear Time
    document.getElementById('user-time-restricted').checked = false;
    document.getElementById('user-time-from').value = '08:00';
    document.getElementById('user-time-to').value = '17:00';
    document.querySelectorAll('#user-allowed-days input').forEach(cb => {
        cb.checked = (cb.value !== "5"); // All except Friday
    });
    toggleTimeFields();
    
    if(document.getElementById('user-data-scope')) document.getElementById('user-data-scope').value = 'all';
    renderPermissionsMatrix(null, 'user-permissions-matrix-body');
}

function hideUserForm() {
    document.getElementById('user-form-container').style.display = 'none';
}

function toggleGeoFields() {
    const isRestricted = document.getElementById('user-geo-restricted').checked;
    document.getElementById('geo-fields').style.display = isRestricted ? 'block' : 'none';
}

function toggleDeviceFields() {
    const isRestricted = document.getElementById('user-device-restricted').checked;
    document.getElementById('device-fields').style.display = isRestricted ? 'block' : 'none';
}

function toggleTimeFields() {
    const isRestricted = document.getElementById('user-time-restricted').checked;
    document.getElementById('time-fields').style.display = isRestricted ? 'block' : 'none';
}

function getCurrentGPS() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('user-geo-lat').value = position.coords.latitude;
            document.getElementById('user-geo-lng').value = position.coords.longitude;
            showToast('تم تحديد الموقع بنجاح', 'success');
        }, function(error) {
            showToast('حدث خطأ أثناء محاولة جلب الموقع. يرجى تفعيل الـ GPS في متصفحك.', 'error');
        });
    } else {
        showToast('متصفحك لا يدعم تحديد الموقع (GPS)', 'error');
    }
}

async function saveUser() {
    const id = document.getElementById('user-id').value;
    const fullName = document.getElementById('user-fullname').value.trim();
    const username = document.getElementById('user-username').value.trim().toLowerCase();
    const password = document.getElementById('user-password').value;
    const phone = document.getElementById('user-phone').value.trim();
    const roleId = document.getElementById('user-role').value;
    const warehouse = document.getElementById('user-warehouse').value;
    const status = document.getElementById('user-status').value;

    const geoRestricted = document.getElementById('user-geo-restricted').checked;
    const geoLat = parseFloat(document.getElementById('user-geo-lat').value) || null;
    const geoLng = parseFloat(document.getElementById('user-geo-lng').value) || null;
    const geoRadius = parseInt(document.getElementById('user-geo-radius').value) || 100;

    const deviceRestricted = document.getElementById('user-device-restricted').checked;
    const deviceUuid = document.getElementById('user-device-uuid').value.trim();

    const timeRestricted = document.getElementById('user-time-restricted').checked;
    const timeFrom = document.getElementById('user-time-from').value;
    const timeTo = document.getElementById('user-time-to').value;
    const allowedDays = Array.from(document.querySelectorAll('#user-allowed-days input:checked')).map(cb => parseInt(cb.value));

    const dataScope = document.getElementById('user-data-scope') ? document.getElementById('user-data-scope').value : 'all';
    const permissionsOverrides = {};
    if(document.getElementById('user-permissions-matrix-body')) {
        document.querySelectorAll('#user-permissions-matrix-body tr').forEach(tr => {
            const viewCb = tr.querySelector('.perm-view');
            if(viewCb) {
                const modId = viewCb.dataset.module;
                permissionsOverrides[modId] = {
                    view: viewCb.checked,
                    add: tr.querySelector('.perm-add').checked,
                    edit: tr.querySelector('.perm-edit').checked,
                    delete: tr.querySelector('.perm-delete').checked,
                    print: tr.querySelector('.perm-print').checked,
                    approve: tr.querySelector('.perm-approve').checked
                };
            }
        });
    }

    let hasError = false;
    if (!fullName) { flashError('user-fullname'); hasError = true; }
    if (!username) { flashError('user-username'); hasError = true; }
    if (!roleId) { flashError('user-role'); hasError = true; }
    if (!id && !password) { flashError('user-password'); hasError = true; }

    if (hasError) {
        showToast('يرجى تعبئة الحقول المحددة باللون الأحمر', 'warning');
        return;
    }
    
    if (geoRestricted && (!geoLat || !geoLng)) {
        showToast('يرجى إدخال خط العرض والطول لتقييد الموقع، أو استخدم التحديد التلقائي', 'warning');
        return;
    }

    // Check for duplicate username locally
    const duplicate = USERS.find(u => u.username === username && u.id !== id);
    if (duplicate) {
        showToast('اسم المستخدم هذا مستخدم مسبقاً في شركتك!', 'error');
        return;
    }

    // Check for duplicate username globally (SaaS requirement)
    const existingUser = id ? USERS.find(u => u.id === id) : null;
    if (!existingUser || existingUser.username !== username) {
        if (!navigator.onLine) {
            showToast('يجب الاتصال بالإنترنت للتحقق من توافر اسم المستخدم الجديد', 'error');
            return;
        }
        try {
            const saveBtn = document.querySelector('#user-form-container .btn-save');
            const originalText = saveBtn.innerText;
            saveBtn.innerText = 'جاري التحقق...';
            saveBtn.disabled = true;
            
            const res = await fetch('api_index.php/public/check-username?username=' + encodeURIComponent(username));
            const data = await res.json();
            
            saveBtn.innerText = originalText;
            saveBtn.disabled = false;
            
            if (data.exists) {
                showToast('اسم المستخدم هذا محجوز لشركة أو مستخدم آخر في النظام! الرجاء اختيار اسم مختلف.', 'error');
                flashError('user-username');
                return;
            }
        } catch (e) {
            const saveBtn = document.querySelector('#user-form-container .btn-save');
            if (saveBtn) {
                saveBtn.innerText = '💾 حفظ المستخدم';
                saveBtn.disabled = false;
            }
            showToast('حدث خطأ أثناء الاتصال بالخادم للتحقق من الاسم', 'error');
            return;
        }
    }

    if (id) {
        // Edit existing
        const userIndex = USERS.findIndex(u => u.id === id);
        if (userIndex > -1) {
            USERS[userIndex].fullName = fullName;
            USERS[userIndex].username = username;
            if (password) USERS[userIndex].password = password; // Only update if new password provided
            USERS[userIndex].phone = phone;
            USERS[userIndex].roleId = roleId;
            USERS[userIndex].warehouse = warehouse;
            USERS[userIndex].status = status;
            USERS[userIndex].geoRestricted = geoRestricted;
            USERS[userIndex].geoLat = geoLat;
            USERS[userIndex].geoLng = geoLng;
            USERS[userIndex].geoRadius = geoRadius;
            USERS[userIndex].deviceRestricted = deviceRestricted;
            USERS[userIndex].deviceUuid = deviceUuid;
            USERS[userIndex].timeRestricted = timeRestricted;
            USERS[userIndex].timeFrom = timeFrom;
            USERS[userIndex].timeTo = timeTo;
            USERS[userIndex].allowedDays = allowedDays;
            USERS[userIndex].dataScope = dataScope;
            USERS[userIndex].permissionsOverrides = permissionsOverrides;
        }
    } else {
        // Add new
        if (!password) {
            showToast('يجب إدخال كلمة مرور للمستخدم الجديد', 'warning');
            return;
        }
        USERS.push({
            id: 'u_' + Date.now(),
            username,
            password,
            fullName,
            phone,
            roleId,
            warehouse,
            status,
            geoRestricted,
            geoLat,
            geoLng,
            geoRadius,
            deviceRestricted,
            deviceUuid,
            timeRestricted,
            timeFrom,
            timeTo,
            allowedDays,
            dataScope,
            permissionsOverrides,
            isSystem: false
        });
    }

    localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
    
    if (window.logSystemAction) {
        window.logSystemAction(id ? 'تعديل مستخدم' : 'إضافة مستخدم', 'settings', `تم حفظ بيانات المستخدم: ${fullName} (${username})`);
    }

    hideUserForm();
    renderUsersTable();
    showToast('تم حفظ المستخدم بنجاح!', 'success');
}

function editUser(id) {
    const user = USERS.find(u => u.id === id);
    if (!user) return;

    showUserForm();
    document.getElementById('user-form-title').innerText = 'تعديل بيانات المستخدم';
    document.getElementById('user-id').value = user.id;
    document.getElementById('user-fullname').value = user.fullName;
    document.getElementById('user-username').value = user.username;
    document.getElementById('user-password').value = ''; // Don't show existing password
    document.getElementById('user-password').placeholder = 'اتركه فارغاً للاحتفاظ بكلمة المرور الحالية';
    document.getElementById('user-phone').value = user.phone || '';
    document.getElementById('user-role').value = user.roleId;
    document.getElementById('user-warehouse').value = user.warehouse;
    document.getElementById('user-status').value = user.status;
    
    if(document.getElementById('user-data-scope')) document.getElementById('user-data-scope').value = user.dataScope || 'all';
    renderPermissionsMatrix(user.permissionsOverrides || null, 'user-permissions-matrix-body');
    
    // Geo load
    document.getElementById('user-geo-restricted').checked = !!user.geoRestricted;
    document.getElementById('user-geo-lat').value = user.geoLat || '';
    document.getElementById('user-geo-lng').value = user.geoLng || '';
    document.getElementById('user-geo-radius').value = user.geoRadius || 100;
    toggleGeoFields();

    // Device load
    document.getElementById('user-device-restricted').checked = !!user.deviceRestricted;
    document.getElementById('user-device-uuid').value = user.deviceUuid || '';
    toggleDeviceFields();

    // Time load
    document.getElementById('user-time-restricted').checked = !!user.timeRestricted;
    document.getElementById('user-time-from').value = user.timeFrom || '08:00';
    document.getElementById('user-time-to').value = user.timeTo || '17:00';
    document.querySelectorAll('#user-allowed-days input').forEach(cb => {
        cb.checked = (user.allowedDays || [0,1,2,3,4,6]).includes(parseInt(cb.value));
    });
    toggleTimeFields();
}

function deleteUser(id) {
    if (confirm('هل أنت متأكد من حذف هذا المستخدم؟')) {
        const userToDelete = USERS.find(u => u.id === id);
        USERS = USERS.filter(u => u.id !== id);
        localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
        
        if (window.logSystemAction && userToDelete) {
            window.logSystemAction('حذف مستخدم', 'settings', `تم حذف المستخدم: ${userToDelete.fullName}`);
        }

        renderUsersTable();
        showToast('تم حذف المستخدم بنجاح', 'success');
    }
}


// ════════════════════════════════════════
//  ROLES & PERMISSIONS MANAGEMENT
// ════════════════════════════════════════
function renderRolesTable() {
    const tbody = document.getElementById('roles-table-body');
    tbody.innerHTML = '';

    ROLES.forEach(role => {
        const usersCount = USERS.filter(u => u.roleId === role.id).length;
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight: bold; color: var(--primary-accent);">${role.name}</td>
            <td>مستوى ${role.securityLevel}</td>
            <td><span class="badge" style="background: rgba(255,255,255,0.1);">${usersCount} مستخدمين</span></td>
            <td>
                <button class="action-btn edit-btn" onclick="editRole('${role.id}')" title="تعديل الصلاحيات">✏️</button>
                ${!role.isSystem && usersCount === 0 ? `<button class="action-btn del-btn" onclick="deleteRole('${role.id}')" title="حذف الدور">🗑️</button>` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderPermissionsMatrix(existingPermissions = null, targetBodyId = 'permissions-matrix-body') {
    const tbody = document.getElementById(targetBodyId);
    if(!tbody) return;
    tbody.innerHTML = '';

    MODULES.forEach(mod => {
        const perms = existingPermissions ? existingPermissions[mod.id] || {} : {};
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${mod.name}</td>
            <td><label class="toggle-switch"><input type="checkbox" class="perm-checkbox perm-view" data-module="${mod.id}" ${perms.view ? 'checked' : ''}><span class="slider"></span></label></td>
            <td><label class="toggle-switch"><input type="checkbox" class="perm-checkbox perm-add" data-module="${mod.id}" ${perms.add ? 'checked' : ''}><span class="slider"></span></label></td>
            <td><label class="toggle-switch"><input type="checkbox" class="perm-checkbox perm-edit" data-module="${mod.id}" ${perms.edit ? 'checked' : ''}><span class="slider"></span></label></td>
            <td><label class="toggle-switch"><input type="checkbox" class="perm-checkbox perm-delete" data-module="${mod.id}" ${perms.delete ? 'checked' : ''}><span class="slider"></span></label></td>
            <td><label class="toggle-switch"><input type="checkbox" class="perm-checkbox perm-print" data-module="${mod.id}" ${perms.print ? 'checked' : ''}><span class="slider"></span></label></td>
            <td><label class="toggle-switch"><input type="checkbox" class="perm-checkbox perm-approve" data-module="${mod.id}" ${perms.approve ? 'checked' : ''}><span class="slider"></span></label></td>
        `;
        tbody.appendChild(tr);
    });
    
    // Add logic: if user unchecks 'view', uncheck others. If user checks 'add', check 'view'.
    tbody.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const tr = this.closest('tr');
            const viewCb = tr.querySelector('.perm-view');
            
            if (this.classList.contains('perm-view') && !this.checked) {
                // Unchecked view -> uncheck all
                tr.querySelectorAll('input[type="checkbox"]').forEach(i => i.checked = false);
            } else if (!this.classList.contains('perm-view') && this.checked) {
                // Checked add/edit/del -> check view
                viewCb.checked = true;
            }
        });
    });
}

function showRoleForm() {
    document.getElementById('role-form-container').style.display = 'block';
    document.getElementById('role-form-title').innerText = 'إضافة دور جديد';
    
    document.getElementById('role-id').value = '';
    document.getElementById('role-name').value = '';
    document.getElementById('role-security-level').value = '1';
    
    document.getElementById('role-hide-costs').checked = false;
    document.getElementById('role-hide-balances').checked = false;

    renderPermissionsMatrix(); // Blank matrix
}

function hideRoleForm() {
    document.getElementById('role-form-container').style.display = 'none';
}

function saveRole() {
    const id = document.getElementById('role-id').value;
    const name = document.getElementById('role-name').value.trim();
    const securityLevel = parseInt(document.getElementById('role-security-level').value);

    if (!name) {
        flashError('role-name');
        showToast('يرجى إدخال اسم الدور الوظيفي (مظلل باللون الأحمر)', 'warning');
        return;
    }

    // Gather Permissions
    const permissions = {};
    document.querySelectorAll('#permissions-matrix-body tr').forEach(tr => {
        const modId = tr.querySelector('.perm-view').dataset.module;
        permissions[modId] = {
            view: tr.querySelector('.perm-view').checked,
            add: tr.querySelector('.perm-add').checked,
            edit: tr.querySelector('.perm-edit').checked,
            delete: tr.querySelector('.perm-delete').checked,
            print: tr.querySelector('.perm-print').checked,
            approve: tr.querySelector('.perm-approve').checked
        };
    });

    const hideCosts = document.getElementById('role-hide-costs').checked;
    const hideBalances = document.getElementById('role-hide-balances').checked;

    if (id) {
        // Edit
        const roleIndex = ROLES.findIndex(r => r.id === id);
        if (roleIndex > -1) {
            ROLES[roleIndex].name = name;
            ROLES[roleIndex].securityLevel = securityLevel;
            ROLES[roleIndex].permissions = permissions;
            ROLES[roleIndex].hideCosts = hideCosts;
            ROLES[roleIndex].hideBalances = hideBalances;
        }
    } else {
        // Add
        ROLES.push({
            id: 'role_' + Date.now(),
            name,
            securityLevel,
            permissions,
            hideCosts,
            hideBalances,
            isSystem: false
        });
    }

    localStorage.setItem('erp_roles_v1', JSON.stringify(ROLES));
    
    if (window.logSystemAction) {
        window.logSystemAction(id ? 'تعديل دور وظيفي' : 'إضافة دور وظيفي', 'settings', `تم حفظ بيانات الدور الوظيفي: ${name}`);
    }

    populateRoleDropdown(); // Update users tab dropdown
    hideRoleForm();
    renderRolesTable();
    showToast('تم حفظ الدور والصلاحيات بنجاح!', 'success');
}

function editRole(id) {
    const role = ROLES.find(r => r.id === id);
    if (!role) return;

    showRoleForm();
    document.getElementById('role-form-title').innerText = 'تعديل الدور والصلاحيات';
    document.getElementById('role-id').value = role.id;
    document.getElementById('role-name').value = role.name;
    document.getElementById('role-security-level').value = role.securityLevel;
    
    document.getElementById('role-hide-costs').checked = !!role.hideCosts;
    document.getElementById('role-hide-balances').checked = !!role.hideBalances;

    renderPermissionsMatrix(role.permissions);
}

function deleteRole(id) {
    const usersCount = USERS.filter(u => u.roleId === id).length;
    if (usersCount > 0) {
        showToast('لا يمكن حذف هذا الدور لوجود مستخدمين مرتبطين به!', 'error');
        return;
    }
    
    if (confirm('هل أنت متأكد من حذف هذا الدور؟')) {
        const roleToDelete = ROLES.find(r => r.id === id);
        ROLES = ROLES.filter(r => r.id !== id);
        localStorage.setItem('erp_roles_v1', JSON.stringify(ROLES));
        
        if (window.logSystemAction && roleToDelete) {
            window.logSystemAction('حذف دور وظيفي', 'settings', `تم حذف الدور الوظيفي: ${roleToDelete.name}`);
        }

        populateRoleDropdown();
        renderRolesTable();
        showToast('تم حذف الدور بنجاح', 'success');
    }
}

// ════════════════════════════════════════
//  KEYBOARD NAVIGATION (Enter Key to move next)
// ════════════════════════════════════════
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const target = e.target;
        if (target.tagName === 'INPUT' || target.tagName === 'SELECT') {
            e.preventDefault();
            
            // Get all focusable elements in the current form/card
            const container = target.closest('.md-card') || document;
            const focusable = Array.from(container.querySelectorAll('input:not([type="hidden"]):not([disabled]), select:not([disabled]), button.btn-save'));
            
            const index = focusable.indexOf(target);
            if (index > -1) {
                let nextElement = null;
                // Find next visible element
                for (let i = index + 1; i < focusable.length; i++) {
                    const el = focusable[i];
                    // Check visibility (offsetParent is null if element or ancestor is display:none)
                    if (el.offsetParent !== null) {
                        nextElement = el;
                        break;
                    }
                }
                
                if (nextElement) {
                    nextElement.focus();
                    if (nextElement.tagName === 'INPUT' && ['text', 'number', 'password', 'time'].includes(nextElement.type)) {
                        try {
                            nextElement.select();
                        } catch (err) {}
                    }
                }
            }
        }
    }
});

// Utility Functions
window.forceLogoutUser = function(id) {
    if(confirm('هل أنت متأكد من إجبار هذا المستخدم على تسجيل الخروج فوراً؟')) {
        const userIndex = USERS.findIndex(u => u.id === id);
        if(userIndex > -1) {
            USERS[userIndex].status = 'forced_logout';
            localStorage.setItem('force_logout_' + USERS[userIndex].username, 'true');
            localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
            renderUsersTable();
            showToast('تم إرسال أمر الخروج للمستخدم بنجاح', 'success');
        }
    }
};

window.selectAllRolePermissions = function() {
    const tbody = document.getElementById('permissions-matrix-body');
    if(!tbody) return;
    const viewCheckboxes = Array.from(tbody.querySelectorAll('.perm-view'));
    const allChecked = viewCheckboxes.every(cb => cb.checked);
    
    tbody.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = !allChecked);
};

window.selectAllUserPermissions = function() {
    const tbody = document.getElementById('user-permissions-matrix-body');
    if(!tbody) return;
    const viewCheckboxes = Array.from(tbody.querySelectorAll('.perm-view'));
    const allChecked = viewCheckboxes.every(cb => cb.checked);
    
    tbody.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = !allChecked);
};

window.addEventListener('cloudDataRestored', () => {
    loadData();
    renderRolesTable();
    renderUsersTable();
});

