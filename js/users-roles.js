// c:\mynewapp\frontend-pwa\js\users-roles.js

// ════════════════════════════════════════
//  DATA INITIALIZATION
// ════════════════════════════════════════
const MODULES = [
    { id: 'dashboard', name: 'لوحة التحكم' },
    { id: 'sales', name: 'المبيعات' },
    { id: 'purchase', name: 'المشتريات' },
    { id: 'inventory', name: 'تقارير المخزون والجرد' },
    { id: 'finance', name: 'المعاملات المالية' },
    { id: 'finance_single', name: 'حركة مالية' },
    { id: 'finance_multi', name: 'حركة مالية مجمعة' },
    { id: 'master_data', name: 'البيانات الأساسية' },
    { id: 'reports', name: 'التقارير التحليلية' },
    { id: 'settings', name: 'الإعدادات والصلاحيات' },
    { id: 'inventory_rules', name: 'قواعد المخازن' },
    { id: 'posting_accounts', name: 'ترحيل واعتماد الحسابات' },
    { id: 'exchange_rates', name: 'تعديل أسعار الصرف اليومية' }
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

    if (!USERS.some(u => u.username === 'joumana')) {
        USERS.push({
            id: 'u_joumana',
            username: 'joumana',
            password: '123456',
            fullName: 'جمانة مهدي',
            userType: 'user',
            roleId: ROLES[0] ? ROLES[0].id : '',
            role: 'cashier',
            securityLevel: 4,
            warehouse: 'all',
            status: 'active',
            isSystem: false
        });
        changes = true;
    }

    if (changes) {
        localStorage.setItem('erp_roles_v1', JSON.stringify(ROLES));
        localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
        try {
            if (typeof window.getTenantKey === 'function') {
                const tk = window.getTenantKey('erp_users_v1');
                if (tk !== 'erp_users_v1') localStorage.setItem(tk, JSON.stringify(USERS));
            }
        } catch(e) {}
    }
}

// ════════════════════════════════════════
//  DOM CONTENT LOADED
// ════════════════════════════════════════
let appInitialized = false;
function initUsersRolesApp() {
    if (appInitialized) return;
    appInitialized = true;

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
}

if (window.APP_READY) {
    initUsersRolesApp();
} else {
    document.addEventListener('DBReady', initUsersRolesApp);
    document.addEventListener('DOMContentLoaded', () => setTimeout(initUsersRolesApp, 100));
    setTimeout(initUsersRolesApp, 400);
}

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
        const role = ROLES.find(r => r.id === user.roleId) || { name: (user.role === 'cashier' ? 'كاشير' : (user.role === 'accountant' ? 'محاسب' : (user.role === 'admin' ? 'مدير نظام' : (user.role || 'مستخدم')))) };
        const isAdmin = (user.userType === 'admin' || user.role === 'admin' || user.roleId === 'role_admin_default');
        const userTypeBadge = isAdmin 
            ? `<span class="badge" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid #ef4444; font-weight:800;">👑 مدير نظام</span>` 
            : `<span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid #38bdf8; font-weight:800;">👤 مستخدم عادي</span>`;

        let statusBadge = user.status === 'active' 
            ? `<span class="badge" style="background:rgba(34,197,94,0.15); color:#22c55e; border:1px solid #22c55e; font-weight:800;">نشط</span>` 
            : user.status === 'forced_logout' 
                ? `<span class="badge" style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid #f59e0b; font-weight:800;">مطرود</span>` 
                : `<span class="badge" style="background:rgba(239,68,68,0.15); color:#ef4444; border:1px solid #ef4444; font-weight:800;">⏸️ معطل (موقوف)</span>`;
            
        let warehouseName = 'الكل';
        if (user.warehouse !== 'all') {
            const WAREHOUSES = safeGetJSON('erp_warehouses_v1', []);
            const w = WAREHOUSES.find(wh => wh.id === user.warehouse);
            if (w) warehouseName = w.name;
        }

        const secLevel = (user.securityLevel !== undefined) 
            ? user.securityLevel 
            : (isAdmin ? 10 : (role.securityLevel || 1));
        const secBadge = `<span class="badge" style="background:rgba(168,85,247,0.15); color:#c084fc; border:1px solid #a855f7; font-weight:800;">مستوى ${secLevel}</span>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight: bold; color: var(--primary-accent);">${user.username}</td>
            <td>${user.fullName}</td>
            <td>${userTypeBadge}</td>
            <td>${role.name}</td>
            <td>${secBadge}</td>
            <td>${warehouseName}</td>
            <td>${statusBadge}</td>
            <td style="white-space:nowrap;">
                <button class="action-btn edit-btn" onclick="editUser('${user.id}')" title="تعديل بيانات المستخدم">✏️</button>
                ${!user.isSystem ? (
                    user.status === 'inactive'
                    ? `<button class="action-btn" style="background:rgba(34,197,94,0.15); color:#22c55e; border:1px solid #22c55e; padding:4px 9px; border-radius:6px; font-weight:800; cursor:pointer; margin:0 2px;" onclick="toggleUserActiveState('${user.id}')" title="تنشيط المستخدم للسماح له بالدخول والعمل">▶️ تنشيط</button>`
                    : `<button class="action-btn" style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid #f59e0b; padding:4px 9px; border-radius:6px; font-weight:800; cursor:pointer; margin:0 2px;" onclick="toggleUserActiveState('${user.id}')" title="تعطيل المستخدم (إيقاف عن العمل ومنع الدخول)">⏸️ تعطيل</button>`
                ) : ''}
                ${!user.isSystem ? `<button class="action-btn del-btn" onclick="deleteUser('${user.id}')" title="حذف مستخدم">🗑️</button>` : ''}
                ${(!user.isSystem && user.status === 'active') ? `<button class="action-btn" style="color:#ef4444; margin:0 2px;" onclick="forceLogoutUser('${user.id}')" title="طرد فوري (إجبار على الخروج)">🚪</button>` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function showUserForm(isEdit = false) {
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
    
    if (!isEdit) {
        const studioContainer = document.getElementById('user-permissions-studio-container');
        if (studioContainer && window.MagaieesPermissions) {
            window.MagaieesPermissions.renderPermissionsStudioUI(studioContainer, {
                userType: 'user',
                securityLevel: 1,
                allowedScreens: { 'direct-sales.php': true },
                screenButtons: { 'direct-sales.php': { save: true, new: true, print: true, share: true } },
                allowedReports: { 'rep_statement': true }
            });
        }
    }
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
    if (!("geolocation" in navigator)) {
        showToast('متصفحك لا يدعم تحديد الموقع (GPS)', 'error');
        return;
    }
    showToast('🔔 يرجى الضغط على (سماح / Allow) في إشعار ويندوز أو المتصفح للوصول لموقعك...', 'info');
    navigator.geolocation.getCurrentPosition(function(position) {
        document.getElementById('user-geo-lat').value = position.coords.latitude;
        document.getElementById('user-geo-lng').value = position.coords.longitude;
        showToast('✅ تم تأكيد إذن ويندوز وتحديد موقعك الحالي بنجاح!', 'success');
    }, function(error) {
        let msg = 'حدث خطأ أثناء محاولة جلب الموقع.';
        if (error.code === 1) msg = 'تم رفض الإذن في ويندوز أو المتصفح. يرجى تفعيل الموقع في ويندوز أولاً.';
        else if (error.code === 3) msg = 'انتهت مهلة انتظار الإذن من ويندوز.';
        showToast('❌ ' + msg, 'error');
    }, { timeout: 25000, enableHighAccuracy: true, maximumAge: 0 });
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

    let studioPerms = {
        userType: 'user',
        allowedScreens: {},
        screenButtons: {},
        allowedReports: {}
    };
    if (window.MagaieesPermissions && typeof window.MagaieesPermissions.collectStudioPermissionsData === 'function') {
        studioPerms = window.MagaieesPermissions.collectStudioPermissionsData();
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

    // ---------------------------------------------------------
    // USER LIMIT CHECK (CLIENT SIDE)
    // ---------------------------------------------------------
    let userObj = {};
    try { userObj = JSON.parse(localStorage.getItem('user') || '{}'); } catch(e){}
    const maxUsers = parseInt(userObj.max_users || 5);

    if (!id) {
        // Adding a new user
        const currentActiveUsers = USERS.filter(u => u.status !== 'inactive').length;
        if (currentActiveUsers >= maxUsers) {
            showToast(`لقد تجاوزت الحد الأقصى للمستخدمين المتاح لشركتك وهو (${maxUsers}) مستخدمين. يرجى ترقية باقة الاشتراك أو التواصل مع الإدارة.`, 'error');
            return;
        }
    } else {
        // Editing existing user - check if switching from inactive to active
        const existing = USERS.find(u => u.id === id);
        if (existing && existing.status === 'inactive' && status !== 'inactive') {
            const currentActiveUsers = USERS.filter(u => u.status !== 'inactive').length;
            if (currentActiveUsers >= maxUsers) {
                showToast(`لا يمكن تنشيط هذا المستخدم. لقد تجاوزت الحد الأقصى للمستخدمين المتاح وهو (${maxUsers}) مستخدمين.`, 'error');
                return;
            }
        }
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
            USERS[userIndex].deviceRestricted = (studioPerms && studioPerms.deviceRestricted !== undefined) ? studioPerms.deviceRestricted : deviceRestricted;
            USERS[userIndex].maxDevices = (studioPerms && studioPerms.maxDevices !== undefined) ? studioPerms.maxDevices : (USERS[userIndex].maxDevices || 1);
            USERS[userIndex].trustedDevices = (studioPerms && studioPerms.trustedDevices !== undefined) ? studioPerms.trustedDevices : (USERS[userIndex].trustedDevices || []);
            USERS[userIndex].deviceUuid = (studioPerms && studioPerms.deviceUuid) ? studioPerms.deviceUuid : deviceUuid;
            USERS[userIndex].timeRestricted = timeRestricted;
            USERS[userIndex].timeFrom = timeFrom;
            USERS[userIndex].timeTo = timeTo;
            USERS[userIndex].allowedDays = allowedDays;
            USERS[userIndex].dataScope = dataScope;
            USERS[userIndex].permissionsOverrides = permissionsOverrides;
            USERS[userIndex].userType = studioPerms.userType;
            USERS[userIndex].role = studioPerms.userType === 'admin' ? 'admin' : (roleId || USERS[userIndex].role || 'user');
            USERS[userIndex].securityLevel = (studioPerms.securityLevel !== undefined) ? studioPerms.securityLevel : (studioPerms.userType === 'admin' ? 10 : 1);
            USERS[userIndex].allowedScreens = studioPerms.allowedScreens;
            USERS[userIndex].screenButtons = studioPerms.screenButtons;
            USERS[userIndex].allowedReports = studioPerms.allowedReports;
            USERS[userIndex].dashboardPermissions = studioPerms.dashboardPermissions;
            USERS[userIndex].postingPermissions = studioPerms.postingPermissions;
            USERS[userIndex].masterDataPermissions = studioPerms.masterDataPermissions;
            USERS[userIndex].alertsPermissions = studioPerms.alertsPermissions;
            USERS[userIndex].toolsPermissions = studioPerms.toolsPermissions;
            USERS[userIndex].financialLimits = studioPerms.financialLimits;
            USERS[userIndex].dataMasking = studioPerms.dataMasking;
            if (studioPerms.managerPin) USERS[userIndex].managerPin = studioPerms.managerPin;
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
            deviceRestricted: (studioPerms && studioPerms.deviceRestricted !== undefined) ? studioPerms.deviceRestricted : deviceRestricted,
            maxDevices: (studioPerms && studioPerms.maxDevices !== undefined) ? studioPerms.maxDevices : 1,
            trustedDevices: (studioPerms && studioPerms.trustedDevices !== undefined) ? studioPerms.trustedDevices : [],
            deviceUuid: (studioPerms && studioPerms.deviceUuid) ? studioPerms.deviceUuid : deviceUuid,
            timeRestricted,
            timeFrom,
            timeTo,
            allowedDays,
            dataScope,
            permissionsOverrides,
            userType: studioPerms.userType,
            role: studioPerms.userType === 'admin' ? 'admin' : (roleId || 'user'),
            securityLevel: (studioPerms.securityLevel !== undefined) ? studioPerms.securityLevel : (studioPerms.userType === 'admin' ? 10 : 1),
            allowedScreens: studioPerms.allowedScreens,
            screenButtons: studioPerms.screenButtons,
            allowedReports: studioPerms.allowedReports,
            dashboardPermissions: studioPerms.dashboardPermissions,
            postingPermissions: studioPerms.postingPermissions,
            masterDataPermissions: studioPerms.masterDataPermissions,
            alertsPermissions: studioPerms.alertsPermissions,
            toolsPermissions: studioPerms.toolsPermissions,
            financialLimits: studioPerms.financialLimits,
            dataMasking: studioPerms.dataMasking,
            managerPin: studioPerms.managerPin || '',
            isSystem: false
        });
    }

    try {
        const currentActive = JSON.parse(localStorage.getItem('user') || '{}');
        if (currentActive && (currentActive.id === id || currentActive.username === username)) {
            currentActive.userType = studioPerms.userType;
            currentActive.role = studioPerms.userType === 'admin' ? 'admin' : (currentActive.role || 'user');
            currentActive.securityLevel = (studioPerms.securityLevel !== undefined) ? studioPerms.securityLevel : (studioPerms.userType === 'admin' ? 10 : 1);
            currentActive.allowedScreens = studioPerms.allowedScreens;
            currentActive.screenButtons = studioPerms.screenButtons;
            currentActive.allowedReports = studioPerms.allowedReports;
            currentActive.dashboardPermissions = studioPerms.dashboardPermissions;
            currentActive.postingPermissions = studioPerms.postingPermissions;
            currentActive.masterDataPermissions = studioPerms.masterDataPermissions;
            currentActive.alertsPermissions = studioPerms.alertsPermissions;
            currentActive.toolsPermissions = studioPerms.toolsPermissions;
            currentActive.financialLimits = studioPerms.financialLimits;
            currentActive.dataMasking = studioPerms.dataMasking;
            if (studioPerms.managerPin) currentActive.managerPin = studioPerms.managerPin;
            localStorage.setItem('user', JSON.stringify(currentActive));
        }
    } catch(e) {}

    // Real-time Live Cross-Tab Sync via MagaieesPermissions
    if (window.MagaieesPermissions && typeof window.MagaieesPermissions.broadcastSecurityUpdate === 'function') {
        window.MagaieesPermissions.broadcastSecurityUpdate({
            userId: id || (USERS[USERS.length - 1] ? USERS[USERS.length - 1].id : null),
            username: username,
            securityLevel: (studioPerms.securityLevel !== undefined) ? studioPerms.securityLevel : 1,
            updatedAt: new Date().toISOString()
        });
    }

    localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
    try {
        if (typeof window.getTenantKey === 'function') {
            const tk = window.getTenantKey('erp_users_v1');
            if (tk !== 'erp_users_v1') {
                localStorage.setItem(tk, JSON.stringify(USERS));
            }
        }
    } catch(e) {}

    // Direct MySQL database sync for immediate user and password persistence
    const savedUser = USERS.find(u => u.username === username);
    if (savedUser) {
        try {
            await fetch('api/save_master_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: 'save_user',
                    data: Object.assign({}, savedUser, password ? { password: password } : {})
                })
            });
        } catch(apiErr) {
            console.warn("Direct save_user notice:", apiErr);
        }
    }

    if (typeof window.pushToCloud === 'function') {
        try {
            await window.pushToCloud(true);
        } catch(err) {
            console.warn("Immediate cloud sync notice:", err);
        }
    }
    
    if (window.logSystemAction) {
        window.logSystemAction(id ? 'تعديل مستخدم' : 'إضافة مستخدم', 'settings', `تم حفظ بيانات المستخدم: ${fullName} (${username})`);
    }

    hideUserForm();
    renderUsersTable();
    showToast('تم حفظ المستخدم وتطبيق مستوى الأمان بنجاح!', 'success');
}

function editUser(id) {
    const user = USERS.find(u => u.id === id);
    if (!user) return;

    showUserForm(true);
    document.getElementById('user-form-title').innerText = 'تعديل بيانات المستخدم';
    document.getElementById('user-id').value = user.id;
    document.getElementById('user-fullname').value = user.fullName || '';
    document.getElementById('user-username').value = user.username || '';
    document.getElementById('user-password').value = ''; // Don't show existing password
    document.getElementById('user-password').placeholder = 'اتركه فارغاً للاحتفاظ بكلمة المرور الحالية';
    document.getElementById('user-phone').value = user.phone || '';
    document.getElementById('user-role').value = user.roleId || user.role || '';
    document.getElementById('user-warehouse').value = user.warehouse || 'all';
    document.getElementById('user-status').value = user.status || 'active';
    
    const studioContainer = document.getElementById('user-permissions-studio-container');
    if (studioContainer && window.MagaieesPermissions) {
        window.MagaieesPermissions.renderPermissionsStudioUI(studioContainer, user);
    }
    
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

    const formEl = document.getElementById('user-form-container');
    if (formEl) {
        formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ════════════════════════════════════════
//  USER DEACTIVATION & ACTIVATION TOGGLE
// ════════════════════════════════════════
async function toggleUserActiveState(id) {
    const userIndex = USERS.findIndex(u => u.id === id);
    if (userIndex === -1) return;
    const user = USERS[userIndex];

    if (user.isSystem || ['admin', 'c'].includes(user.username.toLowerCase())) {
        showToast('لا يمكن تعديل حالة مدير النظام الرئيسي أو الحساب السيادي!', 'warning');
        return;
    }

    if (user.status === 'inactive') {
        // Activating user: Check subscription user limits
        let userObj = {};
        try { userObj = JSON.parse(localStorage.getItem('user') || '{}'); } catch(e){}
        const maxUsers = parseInt(userObj.max_users || 5);
        const currentActiveUsers = USERS.filter(u => u.status !== 'inactive').length;
        if (currentActiveUsers >= maxUsers) {
            showToast(`لا يمكن تنشيط هذا المستخدم. لقد تجاوزت الحد الأقصى للمستخدمين المتاح لباقة اشتراكك وهو (${maxUsers}) مستخدمين. يرجى ترقية الباقة.`, 'error');
            return;
        }

        user.status = 'active';
        localStorage.removeItem('force_logout_' + user.username);
        showToast(`تم تنشيط المستخدم "${user.fullName || user.username}" بنجاح، ويمكنه الآن الدخول والعمل.`, 'success');
        if (window.logSystemAction) {
            window.logSystemAction('تنشيط مستخدم', 'settings', `تم تنشيط المستخدم: ${user.fullName || user.username}`);
        }
    } else {
        // Deactivating user
        if (!confirm(`هل أنت متأكد من رغبتك في تعطيل المستخدم "${user.fullName || user.username}"؟\nسيتم إيقافه فوراً عن العمل ولن يتمكن من الدخول إلى البرنامج حتى يتم تنشيطه مجدداً.`)) {
            return;
        }

        user.status = 'inactive';
        localStorage.setItem('force_logout_' + user.username, 'true');
        
        // Broadcast instant session termination to any tab/window running this user
        if (window.MagaieesPermissions && typeof window.MagaieesPermissions.broadcastSecurityUpdate === 'function') {
            window.MagaieesPermissions.broadcastSecurityUpdate({
                type: 'USER_DEACTIVATED',
                targetUserId: user.id,
                targetUsername: user.username,
                message: 'تم تعطيل حسابك من قبل الإدارة.'
            });
        }

        showToast(`تم تعطيل حساب المستخدم "${user.fullName || user.username}" ومنعه من العمل بنجاح.`, 'warning');
        if (window.logSystemAction) {
            window.logSystemAction('تعطيل مستخدم', 'settings', `تم تعطيل حساب المستخدم: ${user.fullName || user.username}`);
        }
    }

    // Persist locally
    localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
    try {
        if (typeof window.getTenantKey === 'function') {
            const tk = window.getTenantKey('erp_users_v1');
            if (tk !== 'erp_users_v1') localStorage.setItem(tk, JSON.stringify(USERS));
        }
    } catch(e) {}

    // Persist directly to backend database
    try {
        await fetch('api/save_master_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: 'toggle_user_status',
                data: { id: user.id, status: user.status }
            })
        });
    } catch(err) {
        console.warn("Direct status sync notice:", err);
    }

    // Cloud push
    if (typeof window.pushToCloud === 'function') {
        window.pushToCloud(true).catch(console.error);
    }

    renderUsersTable();
}

// ════════════════════════════════════════
//  SMART TRANSACTION USAGE CHECKER & SAFE DELETION
// ════════════════════════════════════════
async function checkUserUsageComprehensive(user) {
    const usages = [];
    const targetId = String(user.id);
    const targetUsername = String(user.username || '').toLowerCase();
    const targetName = String(user.fullName || user.name || '').toLowerCase();

    const matchesUser = (val) => {
        if (!val) return false;
        const s = String(val).trim().toLowerCase();
        return s === targetId || s === targetUsername || s === targetName;
    };

    // 1. Sales Invoices
    const sales = safeGetJSON('erp_sales_invoices_v1', []);
    const salesCount = sales.filter(inv => matchesUser(inv.createdBy) || matchesUser(inv.user) || matchesUser(inv.cashier) || matchesUser(inv.salesRep)).length;
    if (salesCount > 0) usages.push({ type: 'فواتير مبيعات', count: salesCount });

    // 2. Purchase Invoices
    const purchases = safeGetJSON('erp_purchase_invoices_v1', []);
    const purchasesCount = purchases.filter(inv => matchesUser(inv.createdBy) || matchesUser(inv.user) || matchesUser(inv.buyer)).length;
    if (purchasesCount > 0) usages.push({ type: 'فواتير مشتريات', count: purchasesCount });

    // 3. Receipts & Single Receipts
    const receipts = [...safeGetJSON('erp_receipts_v1', []), ...safeGetJSON('erp_single_receipts_v1', [])];
    const receiptsCount = receipts.filter(rec => matchesUser(rec.createdBy) || matchesUser(rec.user) || matchesUser(rec.receivedBy)).length;
    if (receiptsCount > 0) usages.push({ type: 'سندات قبض', count: receiptsCount });

    // 4. Payments & Single Payments
    const payments = [...safeGetJSON('erp_payments_v1', []), ...safeGetJSON('erp_single_payments_v1', [])];
    const paymentsCount = payments.filter(pay => matchesUser(pay.createdBy) || matchesUser(pay.user) || matchesUser(pay.paidBy)).length;
    if (paymentsCount > 0) usages.push({ type: 'سندات صرف', count: paymentsCount });

    // 5. Journal Vouchers & GL Entries
    const jvs = safeGetJSON('erp_journal_vouchers_v1', []);
    const gls = safeGetJSON('erp_gl_v1', []);
    const jvCount = jvs.filter(jv => matchesUser(jv.createdBy) || matchesUser(jv.user)).length +
                    gls.filter(g => matchesUser(g.createdBy) || matchesUser(g.user)).length;
    if (jvCount > 0) usages.push({ type: 'قيود يومية وحركات دفتر أستاذ', count: jvCount });

    // 6. Delivery Notes
    const notes = safeGetJSON('erp_delivery_notes_v1', []);
    const noteCount = notes.filter(dn => matchesUser(dn.createdBy) || matchesUser(dn.driver) || matchesUser(dn.deliveredBy)).length;
    if (noteCount > 0) usages.push({ type: 'سندات تسليم وإذن استلام', count: noteCount });

    // 7. Audit Logs
    const audits = safeGetJSON('erp_audit_logs', []);
    const auditCount = audits.filter(a => matchesUser(a.userId) || matchesUser(a.username) || matchesUser(a.user)).length;
    if (auditCount > 0) usages.push({ type: 'سجلات تدقيق ورقابة نظام', count: auditCount });

    // 8. Server-side MySQL Database Check
    try {
        const res = await fetch('api/save_master_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: 'check_entity_usage',
                data: {
                    entity_type: 'user',
                    id: user.id,
                    name: user.username
                }
            })
        });
        const serverData = await res.json();
        if (serverData && serverData.is_used && Array.isArray(serverData.usages)) {
            serverData.usages.forEach(u => {
                if (!usages.some(existing => existing.type === u.type)) {
                    usages.push(u);
                }
            });
        }
    } catch(e) {
        console.warn("Server usage check notice:", e);
    }

    return usages;
}

function showBlockedUserDeletionModal(user, usages, totalCount) {
    let existingModal = document.getElementById('blocked-user-delete-modal');
    if (existingModal) existingModal.remove();

    const usagesListHtml = usages.map(u => `
        <li style="display:flex; justify-content:space-between; align-items:center; padding:9px 14px; background:rgba(239,68,68,0.06); border-radius:8px; border-right:4px solid #ef4444; margin-bottom:8px;">
            <span style="font-weight:700; color:var(--text-primary); font-size:0.92rem;">📌 ${u.type}</span>
            <span style="background:#ef4444; color:#fff; border-radius:12px; padding:2px 12px; font-weight:800; font-size:0.85rem;">${u.count || 'مسجل'}</span>
        </li>
    `).join('');

    const modalHtml = `
    <div id="blocked-user-delete-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.78); backdrop-filter:blur(6px); display:flex; align-items:center; justify-content:center; z-index:999999; direction:rtl; padding:15px; box-sizing:border-box;">
        <div style="background:var(--card-bg, #1e293b); border:1px solid rgba(239,68,68,0.45); box-shadow:0 25px 60px rgba(0,0,0,0.5), 0 0 35px rgba(239,68,68,0.25); border-radius:18px; width:100%; max-width:550px; overflow:hidden;">
            <div style="background:linear-gradient(135deg, rgba(239,68,68,0.25), rgba(185,28,28,0.35)); padding:20px 24px; border-bottom:1px solid rgba(239,68,68,0.3); display:flex; align-items:center; gap:14px;">
                <div style="font-size:2.2rem; background:rgba(239,68,68,0.2); width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; border:1px solid rgba(239,68,68,0.5);">🚫</div>
                <div>
                    <h3 style="margin:0; color:#f87171; font-size:1.25rem; font-weight:800;">تنبيه رقابي: لا يمكن حذف هذا المستخدم!</h3>
                    <p style="margin:4px 0 0 0; color:rgba(255,255,255,0.75); font-size:0.88rem;">المستخدم مرتبط بسجلات وحركات محاسبية ومالية في النظام</p>
                </div>
            </div>
            
            <div style="padding:22px 24px; max-height:60vh; overflow-y:auto;">
                <div style="background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.35); border-radius:10px; padding:12px 16px; margin-bottom:18px;">
                    <p style="margin:0; font-size:0.92rem; color:var(--text-primary); line-height:1.6;">
                        المستخدم <strong style="color:var(--primary-accent);">${user.fullName || user.username}</strong> قام بإجراء <strong style="color:#f87171;">(${totalCount}) عملية/حركة</strong> مسجلة في النظام. حفاظاً على سلامة الدفاتر المحاسبية والتدقيق المالي والضرائب، يُمنع منعاً باتاً حذف حسابه نهائياً.
                    </p>
                </div>

                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--text-primary); font-weight:700;">سجل العمليات المرتبطة بهذا المستخدم:</h4>
                <ul style="list-style:none; padding:0; margin:0 0 18px 0;">
                    ${usagesListHtml}
                </ul>

                <div style="background:rgba(59,130,246,0.08); border:1px dashed rgba(59,130,246,0.4); border-radius:10px; padding:12px 16px;">
                    <p style="margin:0; font-size:0.86rem; color:var(--text-secondary); line-height:1.5;">
                        💡 <strong>الحل النظامي المعتمد:</strong> يمكنك تعطيل حساب المستخدم فوراً. سيؤدي التعطيل إلى طرده ومنعه من تسجيل الدخول أو إجراء أي عمليات مستقبلية مع الاحتفاظ بالبيانات التاريخية للتدقيق.
                    </p>
                </div>
            </div>

            <div style="background:rgba(0,0,0,0.25); padding:16px 24px; border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <button type="button" onclick="document.getElementById('blocked-user-delete-modal').remove()" style="background:transparent; border:1px solid rgba(255,255,255,0.25); color:var(--text-primary); padding:10px 18px; border-radius:8px; cursor:pointer; font-weight:700; font-size:0.9rem;">
                    إغلاق
                </button>
                <button type="button" onclick="document.getElementById('blocked-user-delete-modal').remove(); toggleUserActiveState('${user.id}');" style="background:linear-gradient(135deg, #f59e0b, #d97706); color:#fff; border:none; padding:10px 22px; border-radius:8px; cursor:pointer; font-weight:800; font-size:0.92rem; box-shadow:0 4px 15px rgba(245,158,11,0.35); display:flex; align-items:center; gap:8px;">
                    <span>⏸️</span> تعطيل حساب المستخدم الآن
                </button>
            </div>
        </div>
    </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

async function deleteUser(id) {
    const userToDelete = USERS.find(u => u.id === id);
    if (!userToDelete) return;

    if (userToDelete.isSystem || ['admin', 'c'].includes(userToDelete.username.toLowerCase())) {
        showToast('لا يمكن حذف المستخدم السيادي للنظام!', 'error');
        return;
    }

    showToast('جاري التحقق من سجلات وعمليات المستخدم في النظام...', 'info', 2000);

    const usages = await checkUserUsageComprehensive(userToDelete);
    const totalTransactions = usages.reduce((sum, u) => sum + (u.count || 1), 0);

    if (totalTransactions > 0) {
        showBlockedUserDeletionModal(userToDelete, usages, totalTransactions);
        return;
    }

    if (confirm(`المستخدم "${userToDelete.fullName || userToDelete.username}" ليس لديه أي عمليات أو فواتير أو حركات مسجلة بالنظام.\n\nهل أنت متأكد من حذفه نهائياً من قاعدة البيانات؟`)) {
        USERS = USERS.filter(u => u.id !== id);
        localStorage.setItem('erp_users_v1', JSON.stringify(USERS));
        try {
            if (typeof window.getTenantKey === 'function') {
                const tk = window.getTenantKey('erp_users_v1');
                if (tk !== 'erp_users_v1') localStorage.setItem(tk, JSON.stringify(USERS));
            }
        } catch(e) {}

        try {
            await fetch('api/save_master_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: 'delete_user',
                    data: { id: userToDelete.id, username: userToDelete.username }
                })
            });
        } catch(e) {
            console.warn("Backend user deletion notice:", e);
        }

        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud(true).catch(err => console.error("Immediate sync failed:", err));
        }
        
        if (window.logSystemAction) {
            window.logSystemAction('حذف مستخدم', 'settings', `تم حذف المستخدم نهائياً لعدم وجود حركات: ${userToDelete.fullName} (${userToDelete.username})`);
        }

        renderUsersTable();
        showToast(`✅ تم حذف المستخدم "${userToDelete.fullName || userToDelete.username}" نهائياً بنجاح.`, 'success');
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
    if (typeof window.pushToCloud === 'function') {
        window.pushToCloud().catch(err => console.error("Immediate sync failed:", err));
    }
    
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
        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud().catch(err => console.error("Immediate sync failed:", err));
        }
        
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
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud().catch(err => console.error("Immediate sync failed:", err));
            }
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

window.toggleUserActiveState = toggleUserActiveState;
window.deleteUser = deleteUser;

window.addEventListener('cloudDataRestored', () => {
    loadData();
    renderRolesTable();
    renderUsersTable();
});


