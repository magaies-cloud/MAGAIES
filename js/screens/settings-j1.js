    // ─── Load Settings on Page Open ───────────────────────────
    let _settingsPageInited = false;
    function initSettingsPage() {
        loadSettings();
        if (_settingsPageInited) return;
        _settingsPageInited = true;
        if (!localStorage.getItem('authToken')) {
            window.location.href = 'login.php';
            return;
        }

        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const perms = window.resolveUserPermissions ? window.resolveUserPermissions(user) : {};
        
        const role = String(user.role || '').toLowerCase();
        const username = String(user.username || '').toLowerCase();
        const roleId = String(user.roleId || '').toLowerCase();

        const isAdmin = !user.role || 
            username === 'admin' || 
            username === 'c' ||
            user.userType === 'admin' ||
            role.includes('admin') || 
            role.includes('مدير') || 
            role.includes('أدمن') || 
            role.includes('manager') || 
            roleId === 'admin_fallback' || 
            roleId === 'role_admin_default' ||
            (user.securityLevel && Number(user.securityLevel) >= 8);
        
        const hasSettings = isAdmin || !perms.settings || perms.settings.view !== false;
        const hasInvRules = isAdmin || !perms.inventory_rules || perms.inventory_rules.view !== false;
        
        if (!hasSettings && !hasInvRules) {
            window.location.href = 'dashboard-pro.php';
            return;
        }
        
        if (!hasSettings) {
            const tabsToHide = ['company', 'finance', 'ui', 'users', 'system', 'printing', 'email', 'whatsapp'];
            tabsToHide.forEach(t => {
                const btn = document.querySelector(`button[onclick*="'${t}'"]`);
                if (btn) btn.style.display = 'none';
            });
            const pTitle = document.querySelector('.page-title');
            if (pTitle) pTitle.innerText = 'قواعد المخزون والعمليات';
        }
        if (!hasInvRules && !hasSettings) {
            const invBtn = document.querySelector('button[onclick*="\'inventory\'"]');
            if (invBtn) invBtn.style.display = 'none';
        }

        const canEditSettings = isAdmin || !perms.settings || perms.settings.edit !== false;
        const canEditInvRules = isAdmin || !perms.inventory_rules || perms.inventory_rules.edit !== false;
        const canEditRates = isAdmin || !perms.exchange_rates || perms.exchange_rates.edit !== false;
        
        if (!canEditSettings && !canEditInvRules) {
            const mainSaveBtn = document.querySelector('.save-btn-main');
            if (mainSaveBtn) mainSaveBtn.style.display = 'none';
        }

        // Exchange Rate Logic setup
        const modeSelect = document.getElementById('cfg-exchange-mode');
        const rateInputs = document.querySelectorAll('.rate-input');
        const usdInput = document.getElementById('cfg-rate-usd');
        const sarInput = document.getElementById('cfg-rate-sar');
        const aedInput = document.getElementById('cfg-rate-aed');
        const rateWarning = document.getElementById('exchange-rate-warning');

        // Enforce exchange rate edit permissions
        rateInputs.forEach(i => {
            i.disabled = !canEditRates;
            i.readOnly = !canEditRates;
            i.style.opacity = canEditRates ? '1' : '0.5';
            i.style.pointerEvents = canEditRates ? 'auto' : 'none';
            i.style.cursor = canEditRates ? 'text' : 'not-allowed';
        });

        if (modeSelect) {
            modeSelect.disabled = !canEditRates;
            modeSelect.style.opacity = canEditRates ? '1' : '0.5';
            modeSelect.style.cursor = canEditRates ? 'pointer' : 'not-allowed';
            
            modeSelect.addEventListener('change', function() {
                if (!canEditRates) return;
                
                if (this.value === 'global') {
                    fetch('api_index.php/exchange-rate')
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.visible && data.rate) {
                                const usdRate = parseFloat(data.rate);
                                if (usdInput && (!usdInput.value || usdInput.value == 0)) {
                                    usdInput.value = usdRate;
                                }
                            }
                        }).catch(() => {});
                }
            });
        }

        if (usdInput) {

            const autoSaveRate = function() {
                const rawVal = usdInput.value;
                if (modeSelect && modeSelect.value === 'global') {
                    modeSelect.value = 'manual';
                }
                const usdRate = parseNum(rawVal);
                if (usdRate > 0) {
                    if (sarInput) sarInput.value = (usdRate / 3.75).toFixed(2);
                    if (aedInput) aedInput.value = (usdRate / 3.67).toFixed(2);

                    // Instant Persistence to dedicated and settings keys
                    const origSet = window.originalSetItem || Storage.prototype.setItem;
                    try { origSet.call(localStorage, 'erp_rate_usd', usdRate.toString()); } catch(e){}
                    try { localStorage.setItem('erp_rate_usd', usdRate.toString()); } catch(e){}
                    try { origSet.call(localStorage, 'erp_manual_rate_usd', usdRate.toString()); } catch(e){}
                    try { localStorage.setItem('erp_manual_rate_usd', usdRate.toString()); } catch(e){}

                    const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
                    try {
                        let currentCfg = JSON.parse(localStorage.getItem(settingsKey) || localStorage.getItem('erp_settings_v1') || '{}');
                        currentCfg.rateUSD = usdRate;
                        currentCfg.exchangeMode = 'manual';
                        const str = JSON.stringify(currentCfg);
                        try { origSet.call(localStorage, settingsKey, str); } catch(e){}
                        try { origSet.call(localStorage, 'erp_settings_v1', str); } catch(e){}
                        if (window.DB_STORE) {
                            window.DB_STORE[settingsKey] = str;
                            window.DB_STORE['erp_settings_v1'] = str;
                        }
                    } catch(e) {}
                }
            };

            usdInput.addEventListener('input', autoSaveRate);
            usdInput.addEventListener('change', autoSaveRate);
            usdInput.addEventListener('blur', autoSaveRate);
        }

        // Dynamic visibility of exchange rates based on selected active currencies
        const activeCurrenciesCbs = document.querySelectorAll('.active-currency-cb');
        function updateExchangeRateVisibility() {
            activeCurrenciesCbs.forEach(cb => {
                const group = document.querySelector('.rate-group-' + cb.value.toLowerCase());
                if (group) {
                    group.style.display = cb.checked ? 'block' : 'none';
                }
            });
        }
        activeCurrenciesCbs.forEach(cb => cb.addEventListener('change', updateExchangeRateVisibility));
        window.updateExchangeRateVisibility = updateExchangeRateVisibility; // Expose for loadSettings
        updateExchangeRateVisibility();

        loadSettings();

        // Show save button only when changes are made
        const settingsContainer = document.querySelector('.settings-container');
        if (settingsContainer) {
            function showSaveAlert(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
                    const btn = document.getElementById('save-settings-btn');
                    if (btn && btn.style.display !== 'block') {
                        btn.style.display = 'block';
                        btn.style.animation = 'none';
                        setTimeout(() => {
                            btn.style.animation = 'fadeIn 0.4s ease-out';
                        }, 10);
                        setTimeout(() => {
                            btn.classList.add('pulse-alert');
                        }, 400);
                    }
                }
            }

            // Auto open requested tab via URL query (?tab=system) or hash (#system)
            const urlParams = new URLSearchParams(window.location.search);
            const reqTab = urlParams.get('tab') || window.location.hash.replace('#', '');
            if (reqTab && document.getElementById('tab-' + reqTab)) {
                switchTab(reqTab);
            }

            if (settingsContainer) {
                settingsContainer.addEventListener('input', showSaveAlert);
                settingsContainer.addEventListener('change', showSaveAlert);
            }
        }
    }

    function switchTab(tabId, elem) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            content.style.display = 'none';
        });
        
        let targetBtn = elem;
        if (!targetBtn && typeof event !== 'undefined' && event && event.currentTarget) {
            targetBtn = event.currentTarget;
        }
        if (!targetBtn) {
            targetBtn = document.querySelector(`.tab-btn[onclick*="'${tabId}'"]`);
        }
        
        if (targetBtn) targetBtn.classList.add('active');
        
        const targetTab = document.getElementById('tab-' + tabId);
        if (targetTab) {
            targetTab.classList.add('active');
            targetTab.style.display = 'block';
            if (tabId === 'users' && typeof window.initSettingsUsersStudio === 'function') {
                window.initSettingsUsersStudio();
            }
            if (tabId === 'screen' && typeof window.renderSettingsErgoStudio === 'function') {
                window.renderSettingsErgoStudio();
            }
            if ((tabId === 'fonts' || tabId === 'ui') && window.MagaieesTypography && typeof window.MagaieesTypography.syncUI === 'function') {
                window.MagaieesTypography.syncUI();
            }
        }
    }

    function switchPrintTab(tabId, btn) {
        document.querySelectorAll('.sub-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.sub-tab-content').forEach(content => content.classList.remove('active'));
        
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
    window.switchTab = switchTab;
    window.switchPrintTab = switchPrintTab;

    // ═══════════════════════════════════════════════════════════════════
    // SETTINGS USERS & PERMISSIONS STUDIO CONTROLLER
    // ═══════════════════════════════════════════════════════════════════
    window.initSettingsUsersStudio = function() {
        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}

        if (!users || users.length === 0) {
            users = [
                { id: 'u_admin_default', username: 'admin', fullName: 'مدير النظام', userType: 'admin', role: 'admin', securityLevel: 10, isSystem: true },
                { id: 'u_c_default', username: 'c', fullName: 'المستخدم الرئيسي (c)', userType: 'admin', role: 'admin', securityLevel: 10, isSystem: true },
                { id: 'u_joumana', username: 'joumana', password: '123456', fullName: 'جمانة مهدي', userType: 'user', role: 'cashier', securityLevel: 4, isSystem: false }
            ];
            localStorage.setItem('erp_users_v1', JSON.stringify(users));
            try {
                if (typeof window.getTenantKey === 'function') {
                    const tk = window.getTenantKey('erp_users_v1');
                    if (tk !== 'erp_users_v1') localStorage.setItem(tk, JSON.stringify(users));
                }
            } catch(e) {}
        } else if (!users.some(u => u.username === 'joumana')) {
            // Seed sample employee Joumana Mahdi (Level 4) for immediate testing
            users.push({ id: 'u_joumana', username: 'joumana', password: '123456', fullName: 'جمانة مهدي', userType: 'user', role: 'cashier', securityLevel: 4, isSystem: false });
            localStorage.setItem('erp_users_v1', JSON.stringify(users));
            try {
                if (typeof window.getTenantKey === 'function') {
                    const tk = window.getTenantKey('erp_users_v1');
                    if (tk !== 'erp_users_v1') localStorage.setItem(tk, JSON.stringify(users));
                }
            } catch(e) {}
        }

        const select = document.getElementById('settings-user-select');
        if (!select) return;
        const currentSelected = select.value;
        select.innerHTML = '';
        users.forEach(u => {
            const isAdmin = (u.userType === 'admin' || u.role === 'admin');
            const typeLabel = isAdmin ? '👑 مدير نظام' : '👤 مستخدم عادي';
            const secLvl = (u.securityLevel !== undefined) ? u.securityLevel : (isAdmin ? 10 : 1);
            select.innerHTML += `<option value="${u.id}">${u.fullName || u.username} (${u.username}) - [${typeLabel}] [مستوى أمان: ${secLvl}]</option>`;
        });

        if (currentSelected && users.some(u => u.id === currentSelected)) {
            select.value = currentSelected;
            window.loadUserSettingsStudio(currentSelected);
            window.updateCopySourceDropdown(currentSelected);
        } else if (users.length > 0) {
            select.value = users[0].id;
            window.loadUserSettingsStudio(users[0].id);
            window.updateCopySourceDropdown(users[0].id);
        }
    };

    window.updateCopySourceDropdown = function(targetUserId) {
        const copySelect = document.getElementById('settings-copy-source-select');
        if (!copySelect) return;
        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}
        const prevVal = copySelect.value;
        copySelect.innerHTML = '<option value="">-- اختر المستخدم المصدر لنسخ صلاحياته --</option>';
        users.forEach(u => {
            if (u.id !== targetUserId) {
                const isAdmin = (u.userType === 'admin' || u.role === 'admin');
                const typeLabel = isAdmin ? '👑 مدير نظام' : '👤 مستخدم عادي';
                copySelect.innerHTML += `<option value="${u.id}">${u.fullName || u.username} (${u.username}) - [${typeLabel}]</option>`;
            }
        });
        if (prevVal && prevVal !== targetUserId && users.some(u => u.id === prevVal)) {
            copySelect.value = prevVal;
        }
    };

    window.copyPermissionsToSelectedUser = async function() {
        const targetSelect = document.getElementById('settings-user-select');
        const sourceSelect = document.getElementById('settings-copy-source-select');
        if (!targetSelect || !targetSelect.value) {
            alert('يرجى اختيار المستخدم المراد نسخ الصلاحيات إليه أولاً.');
            return;
        }
        if (!sourceSelect || !sourceSelect.value) {
            alert('يرجى اختيار المستخدم المصدر الذي تريد نسخ الصلاحيات منه.');
            return;
        }
        const targetUserId = targetSelect.value;
        const sourceUserId = sourceSelect.value;

        if (targetUserId === sourceUserId) {
            alert('لا يمكن نسخ الصلاحيات من وإلى نفس المستخدم!');
            return;
        }

        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}

        const targetUser = users.find(u => u.id === targetUserId);
        const sourceUser = users.find(u => u.id === sourceUserId);

        if (!targetUser || !sourceUser) {
            alert('تعذر العثور على بيانات أحد المستخدمين.');
            return;
        }

        const confirmMsg = `هل أنت متأكد من نسخ كامل الصلاحيات والشاشات والأزرار والورديات الليلية وسقوف المبالغ والـ GPS:\nمن: [${sourceUser.fullName || sourceUser.username}]\nإلى: [${targetUser.fullName || targetUser.username}]؟\n\nسيتم استبدال صلاحيات (${targetUser.fullName || targetUser.username}) الحالية بصلاحيات (${sourceUser.fullName || sourceUser.username}).`;
        if (!confirm(confirmMsg)) return;

        // Clone all permissions fields deeply
        targetUser.userType = sourceUser.userType || 'user';
        targetUser.role = sourceUser.role || (sourceUser.userType === 'admin' ? 'admin' : 'user');
        targetUser.securityLevel = sourceUser.securityLevel !== undefined ? sourceUser.securityLevel : (sourceUser.userType === 'admin' ? 10 : 1);
        targetUser.allowedScreens = JSON.parse(JSON.stringify(sourceUser.allowedScreens || {}));
        targetUser.screenButtons = JSON.parse(JSON.stringify(sourceUser.screenButtons || {}));
        targetUser.allowedReports = JSON.parse(JSON.stringify(sourceUser.allowedReports || {}));
        targetUser.dashboardPermissions = JSON.parse(JSON.stringify(sourceUser.dashboardPermissions || {}));
        targetUser.postingPermissions = JSON.parse(JSON.stringify(sourceUser.postingPermissions || {}));
        targetUser.masterDataPermissions = JSON.parse(JSON.stringify(sourceUser.masterDataPermissions || {}));
        targetUser.alertsPermissions = JSON.parse(JSON.stringify(sourceUser.alertsPermissions || {}));
        targetUser.toolsPermissions = JSON.parse(JSON.stringify(sourceUser.toolsPermissions || {}));
        targetUser.financialLimits = JSON.parse(JSON.stringify(sourceUser.financialLimits || {}));
        targetUser.dataMasking = JSON.parse(JSON.stringify(sourceUser.dataMasking || {}));
        targetUser.timeRestricted = !!sourceUser.timeRestricted;
        targetUser.timeFrom = sourceUser.timeFrom || '08:00';
        targetUser.timeTo = sourceUser.timeTo || '03:00';
        targetUser.allowedDays = Array.isArray(sourceUser.allowedDays) ? [...sourceUser.allowedDays] : [0, 1, 2, 3, 4, 5, 6];
        targetUser.geoRestricted = !!sourceUser.geoRestricted;
        targetUser.geoLat = sourceUser.geoLat !== undefined ? sourceUser.geoLat : null;
        targetUser.geoLng = sourceUser.geoLng !== undefined ? sourceUser.geoLng : null;
        targetUser.geoRadius = sourceUser.geoRadius !== undefined ? sourceUser.geoRadius : 500;
        if (sourceUser.managerPin) targetUser.managerPin = sourceUser.managerPin;

        // Save to localStorage
        localStorage.setItem('erp_users_v1', JSON.stringify(users));
        try {
            if (typeof window.getTenantKey === 'function') {
                const tk = window.getTenantKey('erp_users_v1');
                if (tk !== 'erp_users_v1') localStorage.setItem(tk, JSON.stringify(users));
            }
        } catch(e) {}

        // If currently active logged-in user is targetUser, update active user object
        try {
            const currentActive = JSON.parse(localStorage.getItem('user') || '{}');
            if (currentActive && (currentActive.id === targetUserId || currentActive.username === targetUser.username)) {
                Object.assign(currentActive, {
                    userType: targetUser.userType,
                    role: targetUser.role,
                    securityLevel: targetUser.securityLevel,
                    allowedScreens: targetUser.allowedScreens,
                    screenButtons: targetUser.screenButtons,
                    allowedReports: targetUser.allowedReports,
                    dashboardPermissions: targetUser.dashboardPermissions,
                    postingPermissions: targetUser.postingPermissions,
                    masterDataPermissions: targetUser.masterDataPermissions,
                    alertsPermissions: targetUser.alertsPermissions,
                    toolsPermissions: targetUser.toolsPermissions,
                    financialLimits: targetUser.financialLimits,
                    dataMasking: targetUser.dataMasking,
                    timeRestricted: targetUser.timeRestricted,
                    timeFrom: targetUser.timeFrom,
                    timeTo: targetUser.timeTo,
                    allowedDays: targetUser.allowedDays,
                    geoRestricted: targetUser.geoRestricted,
                    geoLat: targetUser.geoLat,
                    geoLng: targetUser.geoLng,
                    geoRadius: targetUser.geoRadius,
                    managerPin: targetUser.managerPin
                });
                localStorage.setItem('user', JSON.stringify(currentActive));
            }
        } catch(e) {}

        // Direct MySQL sync
        try {
            await fetch('api/save_master_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: 'save_user',
                    data: targetUser
                })
            });
        } catch(apiErr) {
            console.warn("Direct save_user sync notice:", apiErr);
        }

        // Broadcast Security Update
        if (window.MagaieesPermissions && typeof window.MagaieesPermissions.broadcastSecurityUpdate === 'function') {
            window.MagaieesPermissions.broadcastSecurityUpdate({
                userId: targetUserId,
                username: targetUser.username,
                securityLevel: targetUser.securityLevel,
                updatedAt: new Date().toISOString()
            });
        }

        // Push to cloud
        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud(true).catch(console.error);
        }

        // Reload Studio UI for targetUser
        window.loadUserSettingsStudio(targetUserId);

        const successMsg = `✅ تم بنجاح نسخ كامل الصلاحيات من (${sourceUser.fullName || sourceUser.username}) إلى (${targetUser.fullName || targetUser.username}) وتطبيقها فوراً!`;
        if (typeof showToast === 'function') {
            showToast(successMsg, 'success');
        } else {
            alert(successMsg);
        }
    };

    window.loadUserSettingsStudio = function(userId) {
        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}
        const targetUser = users.find(u => u.id === userId) || users[0];
        if (!targetUser) return;

        window.updateCopySourceDropdown(userId);

        const container = document.getElementById('settings-permissions-studio-container');
        if (container) {
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.renderPermissionsStudioUI === 'function') {
                window.MagaieesPermissions.renderPermissionsStudioUI(container, targetUser);
            } else {
                setTimeout(() => window.loadUserSettingsStudio(userId), 150);
            }
        }
    };

    window.saveUserSettingsPermissions = function() {
        const select = document.getElementById('settings-user-select');
        if (!select || !select.value) return;
        const userId = select.value;

        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}
        const userIndex = users.findIndex(u => u.id === userId);
        if (userIndex === -1) return;

        if (!window.MagaieesPermissions) return;
        const permsData = window.MagaieesPermissions.collectStudioPermissionsData();

        users[userIndex].userType = permsData.userType;
        users[userIndex].role = permsData.userType === 'admin' ? 'admin' : (users[userIndex].role || 'user');
        users[userIndex].securityLevel = (permsData.securityLevel !== undefined) ? permsData.securityLevel : (permsData.userType === 'admin' ? 10 : 1);
        users[userIndex].allowedScreens = permsData.allowedScreens;
        users[userIndex].screenButtons = permsData.screenButtons;
        users[userIndex].allowedReports = permsData.allowedReports;
        users[userIndex].dashboardPermissions = permsData.dashboardPermissions;
        users[userIndex].postingPermissions = permsData.postingPermissions;
        users[userIndex].masterDataPermissions = permsData.masterDataPermissions;
        users[userIndex].alertsPermissions = permsData.alertsPermissions;
        users[userIndex].toolsPermissions = permsData.toolsPermissions;
        users[userIndex].financialLimits = permsData.financialLimits;
        users[userIndex].dataMasking = permsData.dataMasking;
        users[userIndex].timeRestricted = permsData.timeRestricted !== undefined ? permsData.timeRestricted : false;
        users[userIndex].timeFrom = permsData.timeFrom || '08:00';
        users[userIndex].timeTo = permsData.timeTo || '03:00';
        users[userIndex].allowedDays = permsData.allowedDays || [0, 1, 2, 3, 4, 5, 6];
        users[userIndex].geoRestricted = permsData.geoRestricted !== undefined ? permsData.geoRestricted : false;
        users[userIndex].geoLat = permsData.geoLat !== undefined ? permsData.geoLat : null;
        users[userIndex].geoLng = permsData.geoLng !== undefined ? permsData.geoLng : null;
        users[userIndex].geoRadius = permsData.geoRadius !== undefined ? permsData.geoRadius : 500;
        users[userIndex].deviceRestricted = permsData.deviceRestricted !== undefined ? permsData.deviceRestricted : false;
        users[userIndex].maxDevices = permsData.maxDevices !== undefined ? permsData.maxDevices : 1;
        users[userIndex].trustedDevices = permsData.trustedDevices || [];
        users[userIndex].deviceUuid = permsData.deviceUuid || '';
        if (permsData.aiSmartLevel) users[userIndex].aiSmartLevel = permsData.aiSmartLevel;
        if (permsData.managerPin) users[userIndex].managerPin = permsData.managerPin;

        localStorage.setItem('erp_users_v1', JSON.stringify(users));

        // If currently logged-in user is this user, update active user object in localStorage!
        try {
            const currentActive = JSON.parse(localStorage.getItem('user') || '{}');
            if (currentActive && (currentActive.id === userId || currentActive.username === users[userIndex].username)) {
                currentActive.userType = users[userIndex].userType;
                currentActive.role = users[userIndex].role;
                currentActive.securityLevel = users[userIndex].securityLevel;
                currentActive.allowedScreens = users[userIndex].allowedScreens;
                currentActive.screenButtons = users[userIndex].screenButtons;
                currentActive.allowedReports = users[userIndex].allowedReports;
                currentActive.dashboardPermissions = users[userIndex].dashboardPermissions;
                currentActive.postingPermissions = users[userIndex].postingPermissions;
                currentActive.masterDataPermissions = users[userIndex].masterDataPermissions;
                currentActive.alertsPermissions = users[userIndex].alertsPermissions;
                currentActive.toolsPermissions = users[userIndex].toolsPermissions;
                currentActive.financialLimits = users[userIndex].financialLimits;
                currentActive.dataMasking = users[userIndex].dataMasking;
                currentActive.timeRestricted = users[userIndex].timeRestricted;
                currentActive.timeFrom = users[userIndex].timeFrom;
                currentActive.timeTo = users[userIndex].timeTo;
                currentActive.allowedDays = users[userIndex].allowedDays;
                currentActive.geoRestricted = users[userIndex].geoRestricted;
                currentActive.geoLat = users[userIndex].geoLat;
                currentActive.geoLng = users[userIndex].geoLng;
                currentActive.geoRadius = users[userIndex].geoRadius;
                currentActive.deviceRestricted = users[userIndex].deviceRestricted;
                currentActive.maxDevices = users[userIndex].maxDevices;
                currentActive.trustedDevices = users[userIndex].trustedDevices;
                currentActive.deviceUuid = users[userIndex].deviceUuid;
                if (users[userIndex].aiSmartLevel) currentActive.aiSmartLevel = users[userIndex].aiSmartLevel;
                if (users[userIndex].managerPin) currentActive.managerPin = users[userIndex].managerPin;
                localStorage.setItem('user', JSON.stringify(currentActive));
            }
        } catch(e) {}

        // Direct MySQL sync
        try {
            fetch('api/save_master_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: 'save_user',
                    data: users[userIndex]
                })
            }).catch(console.warn);
        } catch(e) {}

        // Real-time Live Cross-Tab Sync via MagaieesPermissions
        if (window.MagaieesPermissions && typeof window.MagaieesPermissions.broadcastSecurityUpdate === 'function') {
            window.MagaieesPermissions.broadcastSecurityUpdate({
                userId: userId,
                username: users[userIndex].username,
                securityLevel: users[userIndex].securityLevel,
                updatedAt: new Date().toISOString()
            });
        }

        // Cloud sync
        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud(true).catch(console.error);
        }

        if (typeof showToast === 'function') {
            showToast('✅ تم حفظ وتطبيق الصلاحيات ومستوى الأمان بنجاح للمستخدم: ' + (users[userIndex].fullName || users[userIndex].username), 'success');
        } else {
            alert('✅ تم حفظ وتطبيق الصلاحيات ومستوى الأمان بنجاح للمستخدم: ' + (users[userIndex].fullName || users[userIndex].username));
        }

        window.initSettingsUsersStudio();
        select.value = userId;
    };

    window.openNewUserInSettings = function() {
        const modal = document.getElementById('settings-new-user-modal');
        if (modal) {
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
            document.getElementById('new-u-fullname').value = '';
            document.getElementById('new-u-username').value = '';
            document.getElementById('new-u-password').value = '';
        }
    };

    window.confirmCreateNewUserInSettings = function() {
        const fullName = (document.getElementById('new-u-fullname').value || '').trim();
        const username = (document.getElementById('new-u-username').value || '').trim().toLowerCase();
        const password = document.getElementById('new-u-password').value;

        if (!username || !password) {
            alert('يرجى كتابة اسم الدخول وكلمة المرور');
            return;
        }

        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}

        if (users.some(u => u.username === username)) {
            alert('اسم المستخدم هذا مستخدم مسبقاً، يرجى اختيار اسم آخر.');
            return;
        }

        const newUser = {
            id: 'u_' + Date.now(),
            username: username,
            password: password,
            fullName: fullName || username,
            userType: 'user',
            role: 'user',
            securityLevel: 1,
            status: 'active',
            allowedScreens: { 'direct-sales.php': true },
            screenButtons: { 'direct-sales.php': { save: true, new: true, print: true, share: true } },
            allowedReports: { 'rep_statement': true }
        };

        users.push(newUser);
        localStorage.setItem('erp_users_v1', JSON.stringify(users));
        try {
            if (typeof window.getTenantKey === 'function') {
                const tk = window.getTenantKey('erp_users_v1');
                if (tk !== 'erp_users_v1') localStorage.setItem(tk, JSON.stringify(users));
            }
        } catch(e) {}

        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud(true).catch(console.error);
        }

        document.getElementById('settings-new-user-modal').style.display = 'none';
        window.initSettingsUsersStudio();
        document.getElementById('settings-user-select').value = newUser.id;
        window.loadUserSettingsStudio(newUser.id);

        if (typeof showToast === 'function') {
            showToast('تمت إضافة المستخدم بنجاح. يمكنك الآن تخصيص صلاحياته وحفظها.', 'success');
        }
    };

    window.changePasswordInSettings = function() {
        const select = document.getElementById('settings-user-select');
        if (!select || !select.value) return;
        const userId = select.value;

        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}

        const targetUser = users.find(u => u.id === userId);
        if (!targetUser) return;

        const newPass = prompt(`أدخل كلمة المرور الجديدة للمستخدم "${targetUser.fullName || targetUser.username}":`);
        if (!newPass) return;

        const userIndex = users.findIndex(u => u.id === userId);
        users[userIndex].password = newPass;
        localStorage.setItem('erp_users_v1', JSON.stringify(users));

        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud(true).catch(console.error);
        }

        if (typeof showToast === 'function') {
            showToast('✅ تم تحديث كلمة المرور بنجاح للمستخدم: ' + (targetUser.fullName || targetUser.username), 'success');
        } else {
            alert('✅ تم تحديث كلمة المرور بنجاح للمستخدم: ' + (targetUser.fullName || targetUser.username));
        }
    };

    window.deleteUserInSettings = async function() {
        const select = document.getElementById('settings-user-select');
        if (!select || !select.value) return;
        const userId = select.value;

        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
        } catch(e) {}

        const targetUser = users.find(u => u.id === userId);
        if (!targetUser) return;

        if (targetUser.isSystem || targetUser.username === 'c' || targetUser.username === 'admin') {
            alert('لا يمكن حذف المستخدم الرئيسي أو مدير النظام السيادي!');
            return;
        }

        // Check if user has transactions
        let isUsed = false;
        let usageDetails = [];
        try {
            const uId = String(targetUser.id);
            const uName = String(targetUser.username || '').toLowerCase();
            const checkMatch = val => val && [uId, uName].includes(String(val).trim().toLowerCase());
            
            const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const sCount = sales.filter(i => checkMatch(i.createdBy) || checkMatch(i.user)).length;
            if (sCount > 0) usageDetails.push(`${sCount} فاتورة مبيعات`);

            const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
            const pCount = purchases.filter(i => checkMatch(i.createdBy) || checkMatch(i.user)).length;
            if (pCount > 0) usageDetails.push(`${pCount} فاتورة مشتريات`);

            const receipts = JSON.parse(localStorage.getItem('erp_receipts_v1') || '[]');
            const rCount = receipts.filter(i => checkMatch(i.createdBy) || checkMatch(i.user)).length;
            if (rCount > 0) usageDetails.push(`${rCount} سند قبض`);

            const payments = JSON.parse(localStorage.getItem('erp_payments_v1') || '[]');
            const payCount = payments.filter(i => checkMatch(i.createdBy) || checkMatch(i.user)).length;
            if (payCount > 0) usageDetails.push(`${payCount} سند صرف`);

            if (usageDetails.length > 0) isUsed = true;
        } catch(e) {}

        if (isUsed) {
            const deact = confirm(`⚠️ تنبيه رقابي: لا يمكن حذف المستخدم "${targetUser.fullName || targetUser.username}"!\n\nهذا المستخدم مرتبط بعمليات مسجلة بالنظام (${usageDetails.join('، ')}).\nحفاظاً على سلامة الحسابات المالية والتدقيق، يُمنع حذفه نهائياً.\n\nهل ترغب في تعطيل حسابه بدلاً من ذلك لمنعه من العمل والدخول؟`);
            if (deact) {
                targetUser.status = 'inactive';
                localStorage.setItem('erp_users_v1', JSON.stringify(users));
                localStorage.setItem('force_logout_' + targetUser.username, 'true');
                if (typeof window.pushToCloud === 'function') window.pushToCloud(true).catch(console.error);
                try {
                    fetch('api/save_master_data.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ type: 'toggle_user_status', data: { id: targetUser.id, status: 'inactive' } })
                    });
                } catch(e) {}
                alert(`✅ تم تعطيل حساب المستخدم "${targetUser.fullName || targetUser.username}" بنجاح.`);
                window.initSettingsUsersStudio();
            }
            return;
        }

        if (!confirm(`هل أنت متأكد من حذف المستخدم "${targetUser.fullName || targetUser.username}"؟ هذا المستخدم ليس لديه أي حركات مسجلة بالنظام وسيتم حذفه نهائياً.`)) {
            return;
        }

        users = users.filter(u => u.id !== userId);
        localStorage.setItem('erp_users_v1', JSON.stringify(users));

        try {
            fetch('api/save_master_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: 'delete_user', data: { id: targetUser.id, username: targetUser.username } })
            });
        } catch(e) {}

        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud(true).catch(console.error);
        }

        if (window.logSystemAction) {
            window.logSystemAction('حذف مستخدم', 'settings', `تم حذف المستخدم: ${targetUser.fullName || targetUser.username}`);
        }

        if (typeof showToast === 'function') {
            showToast('✅ تم حذف المستخدم بنجاح', 'success');
        } else {
            alert('✅ تم حذف المستخدم بنجاح');
        }

        window.initSettingsUsersStudio();
    };

                window.handleExchangeModeChange = function(select) {
        const rateInput = document.getElementById('cfg-rate-usd');
        const container = document.getElementById('exchange-rates-single-row');
        const modeBadge = document.getElementById('cfg-mode-badge');
        const rateLabel = document.getElementById('cfg-foreign-rate-label');
        if (!rateInput) return;

        const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
        let currentCfg = {};
        try {
            currentCfg = JSON.parse(localStorage.getItem(settingsKey) || localStorage.getItem('erp_settings_v1') || '{}');
            currentCfg.exchangeMode = select.value;
            currentCfg._userExplicitManual = (select.value === 'manual');
            const str = JSON.stringify(currentCfg);
            localStorage.setItem(settingsKey, str);
            localStorage.setItem('erp_settings_v1', str);
            if (window.DB_STORE) {
                window.DB_STORE[settingsKey] = str;
                window.DB_STORE['erp_settings_v1'] = str;
            }
        } catch(e) {}

        const fallbackRate = currentCfg.rateUSD || parseFloat(localStorage.getItem('erp_rate_usd')) || 600;
        const lastManualRate = parseFloat(localStorage.getItem('erp_manual_rate_usd')) || fallbackRate;
        const lastSaasRate = parseFloat(localStorage.getItem('erp_saas_rate_usd')) || (window.getExchangeRateForCurrency ? window.getExchangeRateForCurrency('USD') : 5600) || fallbackRate;
        
        if (select.value === 'global' || select.value === 'saas') {
            // Cyan / Sky Blue High Glow for Central Rate
            if (container) {
                container.style.background = 'linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(8, 20, 45, 0.98))';
                container.style.borderColor = '#38bdf8';
                container.style.boxShadow = '0 0 25px rgba(56, 189, 248, 0.35), inset 0 1px 2px rgba(255,255,255,0.1)';
            }
            if (modeBadge) {
                modeBadge.style.background = 'rgba(56, 189, 248, 0.15)';
                modeBadge.style.border = '1px solid #38bdf8';
                modeBadge.style.color = '#38bdf8';
                modeBadge.innerHTML = '🔒 السعر المركزي معتمد آلياً';
            }
            if (rateLabel) {
                rateLabel.style.color = '#38bdf8';
                rateLabel.style.textShadow = '0 0 10px rgba(56,189,248,0.5)';
            }

            rateInput.readOnly = true;
            rateInput.style.opacity = '0.9';
            rateInput.style.background = 'rgba(15, 23, 42, 0.9)';
            rateInput.style.borderColor = '#38bdf8';
            rateInput.style.color = '#38bdf8';
            rateInput.style.boxShadow = '0 0 15px rgba(56,189,248,0.3)';
            rateInput.style.cursor = 'not-allowed';
            rateInput.value = lastSaasRate;

            // Fetch from SaaS
            const _base = (() => { const o = window.location.origin; if (o === 'https://elsajana.com' || o === 'https://www.elsajana.com') return 'https://c.elsajana.com/'; let d = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')); if (d.endsWith('/')) d = d.slice(0,-1); return o + (d||'') + '/'; })();
            const token = localStorage.getItem('authToken');
            if (token) {
                fetch(_base + 'api_index.php/tenant-exchange-rate', {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(r => r.json()).then(data => {
                    if (data && data.success && data.payload && data.payload.rateUSD && parseFloat(data.payload.rateUSD) > 0) {
                        rateInput.value = data.payload.rateUSD;
                        localStorage.setItem('erp_saas_rate_usd', data.payload.rateUSD);
                        if(typeof window.showToast === 'function') window.showToast('تم سحب السعر اللحظي من المنصة بنجاح', 'success');
                    } else {
                        rateInput.value = lastSaasRate;
                    }
                }).catch(e => {
                    rateInput.value = lastSaasRate;
                    console.error('Error fetching rate:', e);
                });
            } else {
                rateInput.value = lastSaasRate;
            }
        } else {
            // Emerald Green High Glow for Manual Mode
            if (container) {
                container.style.background = 'linear-gradient(135deg, rgba(6, 30, 20, 0.95), rgba(10, 25, 30, 0.98))';
                container.style.borderColor = '#10b981';
                container.style.boxShadow = '0 0 25px rgba(16, 185, 129, 0.35), inset 0 1px 2px rgba(255,255,255,0.1)';
            }
            if (modeBadge) {
                modeBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                modeBadge.style.border = '1px solid #10b981';
                modeBadge.style.color = '#34d399';
                modeBadge.innerHTML = '✏️ تسعير يدوي مخصص (متاح للتعديل)';
            }
            if (rateLabel) {
                rateLabel.style.color = '#34d399';
                rateLabel.style.textShadow = '0 0 10px rgba(16,185,129,0.5)';
            }

            rateInput.readOnly = false;
            rateInput.style.opacity = '1';
            rateInput.style.background = 'rgba(6, 20, 15, 0.95)';
            rateInput.style.borderColor = '#10b981';
            rateInput.style.color = '#34d399';
            rateInput.style.boxShadow = '0 0 20px rgba(16,185,129,0.4)';
            rateInput.style.cursor = 'text';
            rateInput.value = lastManualRate;
            rateInput.dispatchEvent(new Event('input'));
            rateInput.focus();
            rateInput.select();
        }
    };

    window.renderExchangeRateHistory = function() {
            const tbody = document.getElementById('cfg-history-tbody');
            if (!tbody) return;
            const historyKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
            let hist = [];
            try { hist = JSON.parse(localStorage.getItem(historyKey) || '[]'); } catch(e){}
            
            const filter = document.getElementById('cfg-history-filter') ? document.getElementById('cfg-history-filter').value : 'all';
            
            if (filter !== 'all') {
                hist = hist.filter(h => h.source === filter || (filter === 'saas' && (h.source === 'global' || h.source === 1 || h.source === '1')) || (filter === 'manual' && (h.source === 0 || h.source === '0')));
            }
            
            // Sort by most recent
            hist.reverse();
            
            if (hist.length === 0) {
                tbody.innerHTML = '<tr id="empty-history-row"><td colspan="5" style="text-align:center; padding:25px; color:#f87171; font-weight:bold; font-size: 1.1rem; background:rgba(239,68,68,0.05);">⚠️ لا توجد سجلات مطابقة للفلترة المختارة.</td></tr>';
                return;
            }
            
            let html = '';
            hist.forEach(h => {
                const sourceBadge = (h.source === 'saas' || h.source === 'global') 
                    ? '<span style="background:rgba(56,189,248,0.2); color:#38bdf8; padding:3px 8px; border-radius:12px; font-size:0.8rem;">سحب من منصة ساس</span>' 
                    : '<span style="background:rgba(245,158,11,0.2); color:#f59e0b; padding:3px 8px; border-radius:12px; font-size:0.8rem;">إدخال يدوي</span>';
                
                html += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background=''">
                        <td style="text-align:right; padding:10px; color:#10b981; font-weight:bold;">${parseFloat(h.rate).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                        <td style="text-align:right; padding:10px; color:#f8fafc;">${h.date || '-'}</td>
                        <td style="text-align:right; padding:10px; color:#94a3b8;">${h.time || '-'}</td>
                        <td style="text-align:right; padding:10px; color:#f8fafc;">${h.user || '-'}</td>
                        <td style="text-align:right; padding:10px;">${sourceBadge}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        };

        // Hook into initialization to load history
                document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('cfg-history-tbody')) {
                const historyKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
                let hist = [];
                try { hist = JSON.parse(localStorage.getItem(historyKey) || '[]'); } catch(e){}
                
                if (hist.length === 0) {
                    const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
                    const s = JSON.parse(localStorage.getItem(tk) || '{}');
                    if (s.rateUSD) {
                        const now = new Date();
                        hist.push({
                            id: 'HR-' + Date.now(),
                            rate: s.rateUSD,
                            date: now.toISOString().split('T')[0],
                            time: now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12:true}),
                            user: 'مدير النظام',
                            source: s.exchangeMode || 'manual'
                        });
                        localStorage.setItem(historyKey, JSON.stringify(hist));
                    }
                }
                renderExchangeRateHistory();
            }
        });

        function loadSettings() {
        console.log('=== loadSettings() CALLED (IRON-CLAD TENANT ISOLATION) ===');
        const activeTenantId = (window.SERVER_TENANT && window.SERVER_TENANT.id) || (JSON.parse(localStorage.getItem('user')||'{}').tenant_id) || '1';
        const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : ('tenant_' + activeTenantId + '_erp_settings_v1');
        console.log('Strict settingsKey =', settingsKey);
        
        // 🛡️ IRON-CLAD ISOLATION BARRIER:
        // Priority 1: Direct MySQL authoritative data for THIS specific tenant
        // Priority 2: In-Memory Proxy for THIS specific tenantKey
        // Priority 3: LocalStorage for THIS specific tenantKey
        // Priority 4: Scoped tenant cookie backup
        // Strictly prohibit reading from un-prefixed keys of other tenants!
        let cfgRaw = null;
        
        if (window.SERVER_SETTINGS && typeof window.SERVER_SETTINGS === 'object') {
            cfgRaw = window.SERVER_SETTINGS;
            console.log('loadSettings: READ from authoritative window.SERVER_SETTINGS (MySQL)');
        }
        
        if (!cfgRaw && window.DB_STORE && window.DB_STORE[settingsKey]) {
            cfgRaw = window.DB_STORE[settingsKey];
            console.log('loadSettings: READ from DB_STORE[settingsKey]');
        }
        
        if (!cfgRaw) {
            try { cfgRaw = localStorage.getItem(settingsKey); } catch(e) {}
            if (cfgRaw) console.log('loadSettings: READ from localStorage[settingsKey]');
        }
        
        // Scoped tenant cookie backup ONLY
        if (!cfgRaw) {
            const cookieMatch = document.cookie.match(new RegExp('erp_settings_backup_t' + activeTenantId + '=([^;]+)'));
            if (cookieMatch) {
                try {
                    cfgRaw = decodeURIComponent(cookieMatch[1]);
                    console.log('loadSettings: READ from scoped tenant cookie backup');
                } catch(e) {}
            }
        }
        
        if (!cfgRaw) cfgRaw = '{}';
        
        let cfg = {};
        try {
            cfg = typeof cfgRaw === 'string' ? JSON.parse(cfgRaw) : (cfgRaw || {});
        } catch(e) { cfg = {}; }
        
        console.log('loadSettings: cfg.rateUSD =', cfg.rateUSD, ', cfg.companyName =', cfg.companyName, ', cfg.savedAt =', cfg.savedAt);
        
        // Also check dedicated rate key
        let dedicatedRate = null;
        try { dedicatedRate = localStorage.getItem('erp_rate_usd'); } catch(e) {}
        console.log('loadSettings: dedicated erp_rate_usd =', dedicatedRate);
        
        if (cfg.logoBase64) {
            const img = document.getElementById('logo-preview');
            img.src = cfg.logoBase64;
            img.style.display = 'block';
        }
        
        document.getElementById('cfg-logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    window.pendingLogoBase64 = evt.target.result;
                    const img = document.getElementById('logo-preview');
                    img.src = window.pendingLogoBase64;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // -------------------------------------------------------------
        // Company & Subscription Data: Intelligent Multi-Tier Sync
        // -------------------------------------------------------------
        window.applyCompanyAndSubscriptionData = function(srvTenant, srvSettings, localCfg) {
            srvTenant = srvTenant || window.SERVER_TENANT || {};
            srvSettings = srvSettings || window.SERVER_SETTINGS || {};
            localCfg = localCfg || cfg || {};

            const safeSet = (id, val) => { const el = document.getElementById(id); if (el && val !== undefined && val !== null) el.value = val; };

            // Server is single source of truth; falls back to local cache, then tenant defaults
            const finalCompanyName = (srvSettings && srvSettings.companyName) || (srvTenant && srvTenant.company_name) || (localCfg && localCfg.companyName) || '';
            const finalRegNumber = (srvSettings && srvSettings.regNumber) || (localCfg && localCfg.regNumber) || '';
            const finalTaxNumber = (srvSettings && srvSettings.taxNumber) || (localCfg && localCfg.taxNumber) || '';
            const finalPhone = (srvSettings && srvSettings.phone) || (srvTenant && srvTenant.owner_phone) || (localCfg && localCfg.phone) || '';
            const finalWhatsapp = (srvSettings && srvSettings.whatsapp) || (srvTenant && srvTenant.phone2) || (localCfg && localCfg.whatsapp) || '';
            const finalAddress = (srvSettings && srvSettings.address) || (localCfg && localCfg.address) || '';
            const finalLocation = (srvSettings && srvSettings.location) || (localCfg && localCfg.location) || '';
            const finalLogo = (srvSettings && srvSettings.logoBase64) || (localCfg && localCfg.logoBase64) || '';

            safeSet('cfg-company-name', finalCompanyName);
            safeSet('cfg-reg-number', finalRegNumber);
            safeSet('cfg-tax-number', finalTaxNumber);
            safeSet('cfg-phone', finalPhone);
            safeSet('cfg-whatsapp', finalWhatsapp);
            safeSet('cfg-address', finalAddress);
            safeSet('cfg-location', finalLocation);
            if (typeof renderWaVoucherBrandingUI === 'function') renderWaVoucherBrandingUI();

            if (finalLogo) {
                const img = document.getElementById('logo-preview');
                if (img) {
                    img.src = finalLogo;
                    img.style.display = 'block';
                }
            }

            // Subscription Status & Live Tracking
            const userObj = JSON.parse(localStorage.getItem('user') || '{}');
            const validUntilStr = (srvTenant && srvTenant.valid_until) || userObj.valid_until || localStorage.getItem('tenant_valid_until');
            const tenantStatus = (srvTenant && srvTenant.status) || userObj.status || 'active';
            const planName = (srvTenant && srvTenant.plan_name) || 'الباقة المؤسسية المعتمدة';

            // Keep local caches aligned
            if (validUntilStr) {
                localStorage.setItem('tenant_valid_until', validUntilStr);
                if (userObj.username && (!userObj.valid_until || userObj.valid_until !== validUntilStr || userObj.company_name !== finalCompanyName)) {
                    userObj.valid_until = validUntilStr;
                    userObj.company_name = finalCompanyName;
                    localStorage.setItem('user', JSON.stringify(userObj));
                }
            }

            const subStatusEl = document.getElementById('subscription-status');
            const subPlanEl = document.getElementById('sub-plan-name');
            const subAccEl = document.getElementById('sub-account-status');
            const subExpEl = document.getElementById('sub-expiry-date');
            const subDaysEl = document.getElementById('sub-days-remaining');

            if (subPlanEl) subPlanEl.textContent = planName;

            if (validUntilStr) {
                const validDate = new Date(validUntilStr);
                const now = new Date();
                const diffTime = validDate - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const dateStr = validDate.toISOString().split('T')[0];

                if (subExpEl) subExpEl.textContent = dateStr;

                if (diffDays > 0) {
                    let color = 'rgba(16, 185, 129, 0.2)';
                    let textColor = '#10b981';
                    if (diffDays <= 30) { color = 'rgba(245, 158, 11, 0.2)'; textColor = '#f59e0b'; }
                    if (diffDays <= 7) { color = 'rgba(239, 68, 68, 0.2)'; textColor = '#ef4444'; }

                    if (subStatusEl) {
                        subStatusEl.innerHTML = `⏳ تنتهي في: ${dateStr} (متبقي ${diffDays} يوم)`;
                        subStatusEl.style.background = color;
                        subStatusEl.style.color = textColor;
                        subStatusEl.style.border = `1px solid ${textColor}`;
                    }
                    if (subAccEl) {
                        subAccEl.innerHTML = `<span style="color:#10b981; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-circle-check"></i> ${tenantStatus === 'active' ? 'حساب نشط ومعتمد' : tenantStatus}</span>`;
                    }
                    if (subDaysEl) {
                        subDaysEl.innerHTML = `<span style="color:${textColor}; font-size:1.15rem; font-weight:900;">${diffDays} يوم</span>`;
                    }
                } else {
                    if (subStatusEl) {
                        subStatusEl.innerHTML = '⚠️ الاشتراك منتهي!';
                        subStatusEl.style.background = 'rgba(239, 68, 68, 0.2)';
                        subStatusEl.style.color = '#ef4444';
                        subStatusEl.style.border = '1px solid #ef4444';
                    }
                    if (subAccEl) {
                        subAccEl.innerHTML = '<span style="color:#ef4444; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-triangle-exclamation"></i> اشتراك منتهي الصلاحية</span>';
                    }
                    if (subDaysEl) {
                        subDaysEl.innerHTML = '<span style="color:#ef4444; font-size:1.15rem; font-weight:900;">منتهي منذ ' + Math.abs(diffDays) + ' يوم</span>';
                    }
                }
            } else {
                if (subStatusEl) {
                    subStatusEl.innerHTML = '✨ فترة تجريبية مفتوحة';
                    subStatusEl.style.background = 'rgba(56, 189, 248, 0.1)';
                    subStatusEl.style.color = '#38bdf8';
                    subStatusEl.style.border = '1px solid rgba(56, 189, 248, 0.3)';
                }
                if (subExpEl) subExpEl.textContent = 'غير محدد (فترة تجريبية)';
                if (subDaysEl) subDaysEl.textContent = 'مفتوح';
                if (subAccEl) {
                    subAccEl.innerHTML = '<span style="color:#38bdf8; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-circle-check"></i> فترة تجريبية نشطة</span>';
                }
            }
        };

        // Initial sync from server-injected state
        window.applyCompanyAndSubscriptionData(window.SERVER_TENANT, window.SERVER_SETTINGS, cfg);

        // Background live check to ensure up-to-the-minute freshness from MySQL
        try {
            fetch('settings.php?action=get_company_subscription_data')
                .then(r => r.json())
                .then(res => {
                    if (res && res.success && res.tenant) {
                        window.SERVER_TENANT = res.tenant;
                        if (res.settings) window.SERVER_SETTINGS = res.settings;
                        window.applyCompanyAndSubscriptionData(res.tenant, res.settings, cfg);
                        console.log('Live company & subscription data re-synced from MySQL.');
                    }
                }).catch(e => console.warn('Company subscription background fetch:', e));
        } catch(e) {}

        // Financial Currencies
        if (cfg.currency) {
            const baseEl = document.getElementById('cfg-currency');
            if (baseEl) baseEl.value = cfg.currency;
        }
        if (cfg.foreignCurrency) {
            const foreignEl = document.getElementById('cfg-foreign-currency');
            if (foreignEl) foreignEl.value = cfg.foreignCurrency;
        }

        // Safe DOM Setters
        const safeSetVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
        const safeSetCheck = (id, checked) => { const el = document.getElementById(id); if (el) el.checked = checked; };

        // Immutability Lock Verification
        const isLocked = cfg.initialSetupCompleted || localStorage.getItem('tenant_currencies_locked') === 'true';
        if (isLocked) {
            const baseSelect = document.getElementById('cfg-currency');
            const foreignSelect = document.getElementById('cfg-foreign-currency');
            if (baseSelect) { baseSelect.disabled = true; baseSelect.style.cursor = 'not-allowed'; baseSelect.style.opacity = '0.75'; }
            if (foreignSelect) { foreignSelect.disabled = true; foreignSelect.style.cursor = 'not-allowed'; foreignSelect.style.opacity = '0.75'; }

            ['cfg-lock-badge', 'cfg-base-lock-icon', 'cfg-foreign-lock-icon'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'inline-block';
            });
            ['currency-lock-notice', 'cfg-base-select-lock', 'cfg-foreign-select-lock'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'block';
            });
        }

        if (typeof onCurrencySelectionChange === 'function') {
            onCurrencySelectionChange();
        }
        if (cfg.vat !== undefined) safeSetVal('cfg-vat', cfg.vat);
        if (cfg.decimalPlaces !== undefined) safeSetVal('cfg-decimal-places', cfg.decimalPlaces);
        if (cfg.maxDiscountPct !== undefined) safeSetVal('cfg-max-discount-pct', cfg.maxDiscountPct);
        if (cfg.fiscalYear) safeSetVal('cfg-fiscal-year', cfg.fiscalYear);
        if (cfg.accountingLockDate !== undefined) safeSetVal('cfg-accounting-lock-date', cfg.accountingLockDate);

        // Cybersecurity & Psychological UX
        safeSetCheck('cfg-cyber-strict-session', cfg.cyberStrictSession !== false);
        safeSetCheck('cfg-cyber-bruteforce-lock', cfg.cyberBruteforceLock !== false);
        safeSetCheck('cfg-cyber-hash-chain', cfg.cyberHashChain !== false);
        safeSetCheck('cfg-cyber-pin-delete', cfg.cyberPinDelete !== false);
        safeSetCheck('cfg-cyber-single-session', cfg.cyberSingleSession || false);
        safeSetCheck('cfg-psych-eye-comfort', cfg.psychEyeComfort !== false);
        safeSetCheck('cfg-psych-mistake-shield', cfg.psychMistakeShield !== false);
        safeSetCheck('cfg-psych-sound-feedback', cfg.psychSoundFeedback !== false);
        safeSetCheck('cfg-psych-zen-cashier', cfg.psychZenCashier !== false);

        // Exchange Rates
        const usdEl = document.getElementById('cfg-rate-usd');
        if (usdEl) {
            let savedUsd = cfg.rateUSD || cfg.rate_usd || cfg.usdRate || dedicatedRate;
            if (!savedUsd && window.getExchangeRateForCurrency) {
                 savedUsd = window.getExchangeRateForCurrency('USD');
            }
            console.log('loadSettings: savedUsd candidates:', { 'cfg.rateUSD': cfg.rateUSD, 'cfg.rate_usd': cfg.rate_usd, 'cfg.usdRate': cfg.usdRate, 'dedicatedRate': dedicatedRate, 'final': savedUsd });
            const parsed = parseNum(savedUsd);
            console.log('loadSettings: parsed rate =', parsed);
            if (parsed > 0) {
                usdEl.value = parsed;
                console.log('loadSettings: SET usdEl.value =', parsed);
            } else {
                console.log('loadSettings: WARNING - no valid rate found, usdEl stays empty');
            }
        }
        const modeSelect = document.getElementById('cfg-exchange-mode');
        if (modeSelect) {
            let activeMode = cfg.exchangeMode;
            if (!activeMode) {
                activeMode = 'global';
            }
            modeSelect.value = activeMode;
            if (typeof handleExchangeModeChange === 'function') {
                handleExchangeModeChange(modeSelect);
            }
        }
        if (cfg.rateSAR != null) safeSetVal('cfg-rate-sar', cfg.rateSAR);
        if (cfg.rateAED != null) safeSetVal('cfg-rate-aed', cfg.rateAED);

        // Inventory Rules
        safeSetCheck('cfg-allow-negative-stock', cfg.allowNegativeStock || false);
        safeSetCheck('cfg-two-step-sale', cfg.twoStepSale !== false);
        safeSetCheck('cfg-require-customer', cfg.requireCustomer || false);
        safeSetCheck('cfg-prevent-below-cost', cfg.preventBelowCost || false);
        safeSetCheck('cfg-low-stock-alerts', cfg.lowStockAlerts !== false);
        safeSetCheck('cfg-fifo-policy', cfg.fifoPolicy !== false);
        safeSetCheck('cfg-prevent-expired-sale', cfg.preventExpiredSale !== false);
        safeSetCheck('cfg-enable-barcode', cfg.enableBarcode !== false);
        safeSetCheck('cfg-auto-post-gl', cfg.autoPostGl !== false);

        // Printing
        const printCfg = cfg.print || {
            sales: { size: cfg.printSize || 'A4', qr: 'true', logo: true, signatures: false, footer: cfg.invoiceFooter || '' },
            purchases: { size: 'A4', logo: true, signatures: true, footer: '' },
            quotes: { size: 'A4', logo: true, signatures: true, terms: cfg.invoiceTerms || '' },
            reports: { size: 'A4', header: true, timestamp: true }
        };
        
        // Sales
        safeSetVal('cfg-print-sales-size', printCfg.sales.size);
        safeSetVal('cfg-print-sales-qr', printCfg.sales.qr);
        safeSetCheck('cfg-print-sales-logo', printCfg.sales.logo);
        safeSetCheck('cfg-print-sales-signatures', printCfg.sales.signatures);
        safeSetVal('cfg-print-sales-footer', printCfg.sales.footer);
        
        // Purchases
        safeSetVal('cfg-print-purchases-size', printCfg.purchases.size);
        safeSetCheck('cfg-print-purchases-logo', printCfg.purchases.logo);
        safeSetCheck('cfg-print-purchases-signatures', printCfg.purchases.signatures);
        safeSetVal('cfg-print-purchases-footer', printCfg.purchases.footer);
        
        // Quotes
        safeSetVal('cfg-print-quotes-size', printCfg.quotes.size);
        safeSetCheck('cfg-print-quotes-logo', printCfg.quotes.logo);
        safeSetCheck('cfg-print-quotes-signatures', printCfg.quotes.signatures);
        safeSetVal('cfg-print-quotes-terms', printCfg.quotes.terms);
        
        // Reports
        safeSetVal('cfg-print-reports-size', printCfg.reports.size);
        safeSetCheck('cfg-print-reports-header', printCfg.reports.header);
        safeSetCheck('cfg-print-reports-timestamp', printCfg.reports.timestamp);
        
        // SMTP
        if (cfg.smtpServer) safeSetVal('cfg-smtp-server', cfg.smtpServer);
        if (cfg.smtpPort) safeSetVal('cfg-smtp-port', cfg.smtpPort);
        if (cfg.smtpUser) safeSetVal('cfg-smtp-user', cfg.smtpUser);
        if (cfg.smtpPass) safeSetVal('cfg-smtp-pass', cfg.smtpPass);
        if (cfg.backupEmail) safeSetVal('cfg-backup-email', cfg.backupEmail);

        // UI
        if (cfg.fontSize) safeSetVal('cfg-font-size', cfg.fontSize);
        safeSetCheck('cfg-auto-backup', cfg.autoBackup !== false);
        safeSetCheck('cfg-auto-dark', cfg.autoDark !== false);
        safeSetCheck('cfg-show-athkar', cfg.showAthkar !== false);
        if (cfg.autoLogoutTime !== undefined) safeSetVal('cfg-auto-logout', cfg.autoLogoutTime);
    }

        // ─── Save Settings ─────────────────────────────────────────
    function saveSettings() {
        try {
            const getVal = (id, def = '') => { const el = document.getElementById(id); return el ? el.value.trim() : def; };
            const getNum = (id, def = 0) => { const el = document.getElementById(id); return el ? (parseFloat(el.value) || def) : def; };
            const getBool = (id, def = false) => { const el = document.getElementById(id); return el ? !!el.checked : def; };

            const activeCurrencies = Array.from(document.querySelectorAll('#cfg-active-currencies input[type="checkbox"]:checked')).map(cb => cb.value);
            let oldCfg = {};
            const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
            try { oldCfg = JSON.parse(localStorage.getItem(tk) || '{}'); } catch(e) { console.error('Corrupted old settings', e); }

            const baseCurrency = getVal('cfg-currency', oldCfg.currency || 'SDG');
            const foreignCurrency = getVal('cfg-foreign-currency', oldCfg.foreignCurrency || 'USD');

            const cfg = {
                // Company
                logoBase64:        window.pendingLogoBase64 || oldCfg.logoBase64 || '',
                companyName:       getVal('cfg-company-name', oldCfg.companyName || ''),
                regNumber:         getVal('cfg-reg-number', oldCfg.regNumber || ''),
                taxNumber:         getVal('cfg-tax-number', oldCfg.taxNumber || ''),
                phone:             getVal('cfg-phone', oldCfg.phone || ''),
                whatsapp:          getVal('cfg-whatsapp', oldCfg.whatsapp || ''),
                address:           getVal('cfg-address', oldCfg.address || ''),
                location:          getVal('cfg-location', oldCfg.location || ''),

                // Financial
                currency:              baseCurrency,
                foreignCurrency:       foreignCurrency,
                activeCurrencies:      [baseCurrency, foreignCurrency],
                initialSetupCompleted: true,
                vat:                   getNum('cfg-vat', 0),
                decimalPlaces:         parseInt(getVal('cfg-decimal-places', '2')) || 2,
                maxDiscountPct:        getNum('cfg-max-discount-pct', 10),
                fiscalYear:            getVal('cfg-fiscal-year', '1'),
                accountingLockDate:    getVal('cfg-accounting-lock-date', ''),

                // Cybersecurity & Psychological Governance
                cyberStrictSession:    getBool('cfg-cyber-strict-session', true),
                cyberBruteforceLock:   getBool('cfg-cyber-bruteforce-lock', true),
                cyberHashChain:        getBool('cfg-cyber-hash-chain', true),
                cyberPinDelete:        getBool('cfg-cyber-pin-delete', true),
                cyberSingleSession:    getBool('cfg-cyber-single-session', false),
                psychEyeComfort:       getBool('cfg-psych-eye-comfort', true),
                psychMistakeShield:    getBool('cfg-psych-mistake-shield', true),
                psychSoundFeedback:    getBool('cfg-psych-sound-feedback', true),
                psychZenCashier:       getBool('cfg-psych-zen-cashier', true),

                // Exchange Rates
                exchangeMode:      getVal('cfg-exchange-mode', 'global'),
                rateUSD:           (document.getElementById('cfg-rate-usd') && parseNum(document.getElementById('cfg-rate-usd').value) > 0) ? parseNum(document.getElementById('cfg-rate-usd').value) : (oldCfg.rateUSD || 600),
                rateSAR:           (document.getElementById('cfg-rate-sar') && parseNum(document.getElementById('cfg-rate-sar').value) > 0) ? parseNum(document.getElementById('cfg-rate-sar').value) : (oldCfg.rateSAR || 160),
                rateAED:           (document.getElementById('cfg-rate-aed') && parseNum(document.getElementById('cfg-rate-aed').value) > 0) ? parseNum(document.getElementById('cfg-rate-aed').value) : (oldCfg.rateAED || 163),

                // Inventory
                allowNegativeStock: getBool('cfg-allow-negative-stock', false),
                twoStepSale:       getBool('cfg-two-step-sale', true),
                requireCustomer:   getBool('cfg-require-customer', false),
                preventBelowCost:  getBool('cfg-prevent-below-cost', false),
                lowStockAlerts:    getBool('cfg-low-stock-alerts', true),
                fifoPolicy:        getBool('cfg-fifo-policy', true),
                preventExpiredSale: getBool('cfg-prevent-expired-sale', true),
                enableBarcode:     getBool('cfg-enable-barcode', true),
                autoPostGl:        getBool('cfg-auto-post-gl', true),

                // Printing
                print: {
                    sales: {
                        size: getVal('cfg-print-sales-size', 'A4'),
                        qr: getVal('cfg-print-sales-qr', 'true'),
                        logo: getBool('cfg-print-sales-logo', true),
                        signatures: getBool('cfg-print-sales-signatures', false),
                        footer: getVal('cfg-print-sales-footer', '')
                    },
                    purchases: {
                        size: getVal('cfg-print-purchases-size', 'A4'),
                        logo: getBool('cfg-print-purchases-logo', true),
                        signatures: getBool('cfg-print-purchases-signatures', true),
                        footer: getVal('cfg-print-purchases-footer', '')
                    },
                    quotes: {
                        size: getVal('cfg-print-quotes-size', 'A4'),
                        logo: getBool('cfg-print-quotes-logo', true),
                        signatures: getBool('cfg-print-quotes-signatures', true),
                        terms: getVal('cfg-print-quotes-terms', '')
                    },
                    reports: {
                        size: getVal('cfg-print-reports-size', 'A4'),
                        header: getBool('cfg-print-reports-header', true),
                        timestamp: getBool('cfg-print-reports-timestamp', true)
                    }
                },

                // SMTP & Backups
                smtpServer:        getVal('cfg-smtp-server', ''),
                smtpPort:          getVal('cfg-smtp-port', ''),
                smtpUser:          getVal('cfg-smtp-user', ''),
                smtpPass:          getVal('cfg-smtp-pass', ''),
                backupEmail:       getVal('cfg-backup-email', ''),

                // UI
                fontSize:          getVal('cfg-font-size', '16'),
                autoBackup:        getBool('cfg-auto-backup', true),
                autoDark:          getBool('cfg-auto-dark', true),
                showAthkar:        getBool('cfg-show-athkar', true),
                autoLogoutTime:    getVal('cfg-auto-logout', '0'),

                savedAt: new Date().toISOString()
            };

            const activeTenantId = (window.SERVER_TENANT && window.SERVER_TENANT.id) || (JSON.parse(localStorage.getItem('user')||'{}').tenant_id) || '1';
            const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : ('tenant_' + activeTenantId + '_erp_settings_v1');
            const logoKey = window.getTenantKey ? window.getTenantKey('company_logo_base64') : ('tenant_' + activeTenantId + '_company_logo_base64');
            const strVal = JSON.stringify(cfg);
            
            console.log('=== saveSettings() STRICT TENANT ISOLATION ===');
            console.log('saveSettings: activeTenantId =', activeTenantId);
            console.log('saveSettings: settingsKey =', settingsKey);
            
            // 🛡️ Save EXCLUSIVELY to tenant-isolated storage
            try { localStorage.setItem(settingsKey, strVal); } catch(e) {}
            if (window.DB_STORE) {
                window.DB_STORE[settingsKey] = strVal;
            }
            if (typeof localforage !== 'undefined') {
                localforage.setItem(settingsKey, strVal).catch(() => {});
            }
            
            // Isolate dedicated logo key per tenant
            if (cfg.logoBase64) {
                try { localStorage.setItem(logoKey, cfg.logoBase64); } catch(e) {}
            } else {
                try { localStorage.removeItem(logoKey); } catch(e) {}
            }

            // Also save isolated dedicated rate key
            const rateKey = window.getTenantKey ? window.getTenantKey('erp_rate_usd') : ('tenant_' + activeTenantId + '_erp_rate_usd');
            try { localStorage.setItem(rateKey, String(cfg.rateUSD)); } catch(e) {}
            
            // Scoped tenant cookie backup (lightweight without heavy base64)
            try {
                const minCfg = JSON.stringify({ rateUSD: cfg.rateUSD, exchangeMode: cfg.exchangeMode, companyName: cfg.companyName, currency: cfg.currency, savedAt: cfg.savedAt });
                document.cookie = 'erp_settings_backup_t' + activeTenantId + '=' + encodeURIComponent(minCfg) + '; path=/; max-age=31536000; SameSite=Lax';
                document.cookie = 'erp_rate_usd_backup_t' + activeTenantId + '=' + cfg.rateUSD + '; path=/; max-age=31536000; SameSite=Lax';
            } catch(e) {}
            // --- Log Exchange Rate History ---
            try {
                if (cfg.rateUSD && (oldCfg.rateUSD !== cfg.rateUSD || oldCfg.exchangeMode !== cfg.exchangeMode)) {
                    const historyKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
                    let hist = [];
                    try { hist = JSON.parse(localStorage.getItem(historyKey) || '[]'); } catch(e){}
                    
                    const usr = JSON.parse(localStorage.getItem('user')||'{}');
                    const now = new Date();
                    
                    hist.push({
                        id: 'HR-' + Date.now(),
                        rate: cfg.rateUSD,
                        date: now.toISOString().split('T')[0],
                        time: now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12:true}),
                        user: usr.name || usr.username || 'مدير النظام',
                        source: cfg.exchangeMode
                    });
                    localStorage.setItem(historyKey, JSON.stringify(hist));
                    if (typeof window.renderExchangeRateHistory === 'function') {
                        window.renderExchangeRateHistory();
                    }
                }
            } catch(e) { console.error('History log error:', e); }
            // --- End Log ---
            
            // Verify immediately after save
            try {
                const verifyVal = localStorage.getItem('erp_settings_v1');
                const verifyParsed = JSON.parse(verifyVal);
                console.log('saveSettings: VERIFY after save - rateUSD =', verifyParsed.rateUSD, ', savedAt =', verifyParsed.savedAt);
            } catch(e) { console.error('saveSettings: VERIFY FAILED:', e.message); }
            
            localStorage.setItem('tenant_currencies_locked', 'true');

            // Apply immutability lock immediately upon saving
            const baseSelect = document.getElementById('cfg-currency');
            const foreignSelect = document.getElementById('cfg-foreign-currency');
            if (baseSelect) baseSelect.disabled = true;
            if (foreignSelect) foreignSelect.disabled = true;

            const lockBadgeEl = document.getElementById('cfg-lock-badge');
            if (lockBadgeEl) lockBadgeEl.style.display = 'inline-block';

            const noticeEl = document.getElementById('currency-lock-notice');
            if (noticeEl) noticeEl.style.display = 'block';

            if (window.saveToDB) {
                window.saveToDB('erp_settings_v1', cfg);
            }

            // Persist full company & ERP settings to MySQL backend
            try {
                fetch('settings.php?action=save_company_settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(cfg)
                }).then(r => r.json()).then(res => {
                    if (res && res.success) {
                        console.log('Company and ERP settings synced to MySQL successfully.');
                        if (window.SERVER_TENANT) {
                            window.SERVER_TENANT.company_name = cfg.companyName;
                            window.SERVER_TENANT.owner_phone = cfg.phone;
                            window.SERVER_TENANT.phone2 = cfg.whatsapp;
                        }
                        if (window.SERVER_SETTINGS) {
                            Object.assign(window.SERVER_SETTINGS, cfg);
                        } else {
                            window.SERVER_SETTINGS = Object.assign({}, cfg);
                        }
                        try {
                            const u = JSON.parse(localStorage.getItem('user') || '{}');
                            u.company_name = cfg.companyName;
                            localStorage.setItem('user', JSON.stringify(u));
                            localStorage.setItem('companyName', cfg.companyName);
                        } catch(e) {}
                    }
                }).catch(e => console.warn('MySQL company settings sync warning:', e));
            } catch(e) {}
            
            // Send SMTP & Backup settings to backend
            try {
                fetch('api_index.php?r=smtp_settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ payload: { smtpServer: cfg.smtpServer, smtpPort: cfg.smtpPort, smtpUser: cfg.smtpUser, smtpPass: cfg.smtpPass, backupEmail: cfg.backupEmail } })
                }).catch(e => console.error('SMTP sync error:', e));
            } catch(e) {}

            if (cfg.fontSize) {
                document.documentElement.style.fontSize = cfg.fontSize + 'px';
            }

            const badge = document.getElementById('save-badge');
            if (badge) {
                badge.classList.add('show');
                setTimeout(() => badge.classList.remove('show'), 3000);
            }

            if (window.logSystemAction) {
                window.logSystemAction('Settings Update', 'settings', 'تم تحديث إعدادات النظام');
            }

            // Sync posting mode with the posting system
            localStorage.setItem('erp_posting_mode', cfg.autoPostGl ? 'auto' : 'manual');

            // Broadcast tenant exchange rate to server for multi-user sync
            if (cfg.exchangeMode) {
                const payload = {
                    exchangeMode: cfg.exchangeMode,
                    rateUSD: cfg.rateUSD,
                    rateSAR: cfg.rateSAR,
                    rateAED: cfg.rateAED
                };
                fetch('api_index.php/tenant-exchange-rate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('authToken')}` },
                    body: JSON.stringify({ payload: payload })
                }).catch(e => console.error('Error broadcasting rate:', e));
            }

            window.isSettingsDirty = false;
            if (typeof syncAdminWaPolicyFromUI === 'function') {
                syncAdminWaPolicyFromUI(null, null, true);
            }
            const saveBtn = document.getElementById('save-settings-btn');
            if (saveBtn) {
                saveBtn.classList.remove('pulse-alert');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('✅ تم حفظ التعديلات والإعدادات بنجاح!', 'success');
            } else {
                alert('✅ تم حفظ التعديلات والإعدادات بنجاح!');
            }
        } catch(err) {
            console.error('Error saving settings:', err);
            alert('⚠️ حدث خطأ عند حفظ الإعدادات: ' + err.message);
        }
    }

    function getAdminWaPolicy() {
        let fallbackCompany = '';
        try {
            const activeTenantId = (window.SERVER_TENANT && window.SERVER_TENANT.id) || (JSON.parse(localStorage.getItem('user')||'{}').tenant_id) || '1';
            const tk = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : ('tenant_' + activeTenantId + '_erp_settings_v1');
            const sysCfg = JSON.parse(localStorage.getItem(tk) || localStorage.getItem('erp_settings_v1') || '{}');
            fallbackCompany = (sysCfg.companyName || sysCfg.company_name || (window.SERVER_SETTINGS && window.SERVER_SETTINGS.companyName) || (window.SERVER_TENANT && window.SERVER_TENANT.company_name) || '').trim();
        } catch (e) {}
        const defaults = {
            masterMode: 'per_account',
            receiptApprovalMode: 'per_account',
            defaultReceiptBankId: '',
            defaultReceiptBankName: '',
            voucherCompanyName: fallbackCompany,
            voucherLogoImage: '',
            voucherStampText: 'ختم مصادقة مالية إلكترونية',
            voucherStampImage: '',
            phoneVerification: 'phone_or_pin',
            statementScope: 'all',
            includeInvoiceItems: true,
            notifyAdminOnRequest: false,
            antiSpamCooldown: true
        };
        try {
            let localSaved = {};
            try { localSaved = JSON.parse(localStorage.getItem('erp_whatsapp_statement_policy_v1') || '{}') || {}; } catch (e) {}
            const srvSaved = (window.SERVER_WA_POLICY && typeof window.SERVER_WA_POLICY === 'object') ? window.SERVER_WA_POLICY : {};
            const merged = Object.assign({}, defaults, localSaved, srvSaved);
            if ((!srvSaved.voucherStampImage || String(srvSaved.voucherStampImage).length < 150) && localSaved.voucherStampImage && String(localSaved.voucherStampImage).length >= 150) {
                merged.voucherStampImage = localSaved.voucherStampImage;
            }
            if ((!srvSaved.voucherLogoImage || String(srvSaved.voucherLogoImage).length < 100) && localSaved.voucherLogoImage && String(localSaved.voucherLogoImage).length >= 100) {
                merged.voucherLogoImage = localSaved.voucherLogoImage;
            }
            if (!merged.voucherCompanyName && fallbackCompany) {
                merged.voucherCompanyName = fallbackCompany;
            }
            if (!merged.voucherStampText) {
                merged.voucherStampText = 'ختم مصادقة مالية إلكترونية';
            }
            return merged;
        } catch (e) {}
        return defaults;
    }

    function renderWaVoucherBrandingUI() {
        const policy = getAdminWaPolicy();
        document.querySelectorAll('.wa-policy-company-name').forEach(el => { el.value = policy.voucherCompanyName || ''; });
        document.querySelectorAll('.wa-policy-logo-preview').forEach(img => {
            if (policy.voucherLogoImage && String(policy.voucherLogoImage).length >= 100) {
                img.src = policy.voucherLogoImage;
                img.style.display = 'inline-block';
            } else {
                img.src = '';
                img.style.display = 'none';
            }
        });
        document.querySelectorAll('.wa-policy-logo-clear-btn').forEach(btn => {
            btn.style.display = (policy.voucherLogoImage && String(policy.voucherLogoImage).length >= 100) ? 'inline-block' : 'none';
        });
        document.querySelectorAll('.wa-policy-stamp-text').forEach(el => { el.value = policy.voucherStampText || 'ختم مصادقة مالية إلكترونية'; });
        document.querySelectorAll('.wa-policy-stamp-preview').forEach(img => {
            if (policy.voucherStampImage && String(policy.voucherStampImage).length >= 150) {
                img.src = policy.voucherStampImage;
                img.style.display = 'inline-block';
            } else {
                img.src = '';
                img.style.display = 'none';
            }
        });
        document.querySelectorAll('.wa-policy-stamp-clear-btn').forEach(btn => {
            btn.style.display = (policy.voucherStampImage && String(policy.voucherStampImage).length >= 150) ? 'inline-block' : 'none';
        });
    }

    function syncAdminWaPolicyFromUI(sourceEl, extraPatch = null, silent = false) {
        const curPolicy = getAdminWaPolicy();
        const compInput = document.querySelector('.wa-policy-company-name');
        const stampTextInput = document.querySelector('.wa-policy-stamp-text');

        let domLogoImg = curPolicy.voucherLogoImage || '';
        if (!domLogoImg || String(domLogoImg).length < 100) {
            document.querySelectorAll('.wa-policy-logo-preview').forEach(imgEl => {
                const attrSrc = imgEl.getAttribute('src') || '';
                if (attrSrc.startsWith('data:image/') && attrSrc.length >= 100) {
                    domLogoImg = attrSrc;
                }
            });
        }

        let domStampImg = curPolicy.voucherStampImage || '';
        if (!domStampImg || String(domStampImg).length < 150) {
            document.querySelectorAll('.wa-policy-stamp-preview').forEach(imgEl => {
                const attrSrc = imgEl.getAttribute('src') || '';
                if (attrSrc.startsWith('data:image/') && attrSrc.length >= 150) {
                    domStampImg = attrSrc;
                }
            });
        }

        const policy = Object.assign({}, curPolicy, {
            voucherCompanyName: compInput ? compInput.value.trim() : (curPolicy.voucherCompanyName || ''),
            voucherLogoImage: domLogoImg,
            voucherStampText: (stampTextInput ? stampTextInput.value.trim() : '') || 'ختم مصادقة مالية إلكترونية',
            voucherStampImage: domStampImg
        }, extraPatch || {});

        const syncPayload = Object.assign({}, policy);
        delete policy._forceClearStamp;
        delete policy._forceClearLogo;
        window.SERVER_WA_POLICY = Object.assign({}, policy);
        try { localStorage.setItem('erp_whatsapp_statement_policy_v1', JSON.stringify(policy)); } catch (e) {}
        renderWaVoucherBrandingUI();

        fetch('api/save_master_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'whatsapp_statement_policy', data: syncPayload })
        }).catch(err => console.warn('[Settings] Failed to sync WA voucher branding:', err));

        if (!silent) {
            const badge = document.getElementById('save-badge');
            if (badge) {
                badge.classList.add('show');
                setTimeout(() => badge.classList.remove('show'), 3000);
            }
            if (typeof window.showToast === 'function') {
                window.showToast('🏛️ تم حفظ اسم المنشأة والشعار والتوقيع وإعدادات السندات بنجاح', 'success');
            }
        }
    }

    function handleWaVoucherLogoUpload(inputEl) {
        const file = inputEl && inputEl.files && inputEl.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(evt) {
            const img = new Image();
            img.onload = function() {
                const maxDim = 320;
                let w = img.width || 200;
                let h = img.height || 200;
                if (w > maxDim || h > maxDim) {
                    if (w > h) {
                        h = Math.round((h * maxDim) / w);
                        w = maxDim;
                    } else {
                        w = Math.round((w * maxDim) / h);
                        h = maxDim;
                    }
                }
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);
                const dataUrl = canvas.toDataURL('image/png');
                document.querySelectorAll('.wa-policy-logo-preview').forEach(pImg => {
                    pImg.src = dataUrl;
                    pImg.style.display = 'inline-block';
                });
                syncAdminWaPolicyFromUI(inputEl, { voucherLogoImage: dataUrl });
            };
            img.src = evt.target.result;
        };
        reader.readAsDataURL(file);
        inputEl.value = '';
    }

    function clearWaVoucherLogoImage() {
        document.querySelectorAll('.wa-policy-logo-preview').forEach(pImg => {
            pImg.setAttribute('src', '');
            pImg.style.display = 'none';
        });
        syncAdminWaPolicyFromUI(null, { voucherLogoImage: '', _forceClearLogo: true });
    }

    function handleWaVoucherStampUpload(inputEl) {
        const file = inputEl && inputEl.files && inputEl.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(evt) {
            const img = new Image();
            img.onload = function() {
                const maxDim = 800;
                let w = img.width || 400;
                let h = img.height || 250;
                if (w > maxDim || h > maxDim) {
                    if (w > h) {
                        h = Math.round((h * maxDim) / w);
                        w = maxDim;
                    } else {
                        w = Math.round((w * maxDim) / h);
                        h = maxDim;
                    }
                }
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);

                let finalCanvas = canvas;
                try {
                    const imgData = ctx.getImageData(0, 0, w, h);
                    const d = imgData.data;

                    const lums = [];
                    const borderLums = [];
                    for (let y = 0; y < h; y += 2) {
                        for (let x = 0; x < w; x += 2) {
                            const idx = (y * w + x) * 4;
                            if (d[idx + 3] < 30) continue;
                            const lum = 0.299 * d[idx] + 0.587 * d[idx + 1] + 0.114 * d[idx + 2];
                            lums.push(lum);
                            if (x < w * 0.08 || x > w * 0.92 || y < h * 0.08 || y > h * 0.92) {
                                borderLums.push(lum);
                            }
                        }
                    }
                    lums.sort((a, b) => a - b);
                    borderLums.sort((a, b) => a - b);
                    const borderMed = borderLums.length ? borderLums[Math.floor(borderLums.length * 0.5)] : 240;
                    const invertDarkBg = borderMed < 95 && lums.length > 50;

                    if (invertDarkBg) {
                        for (let i = 0; i < d.length; i += 4) {
                            if (d[i + 3] >= 30) {
                                d[i] = 255 - d[i];
                                d[i + 1] = 255 - d[i + 1];
                                d[i + 2] = 255 - d[i + 2];
                            }
                        }
                    }

                    const p75 = lums.length ? (invertDarkBg ? (255 - lums[Math.floor(lums.length * 0.25)]) : lums[Math.floor(lums.length * 0.75)]) : 235;
                    const inkCutoff = Math.min(220, Math.max(135, p75 * 0.85));

                    for (let i = 0; i < d.length; i += 4) {
                        const r = d[i], g = d[i + 1], b = d[i + 2], a = d[i + 3];
                        if (a < 25) {
                            d[i + 3] = 0;
                            continue;
                        }
                        const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                        const maxCh = Math.max(r, g, b);
                        const minCh = Math.min(r, g, b);
                        const chroma = maxCh - minCh;

                        if (lum >= inkCutoff && chroma < 38) {
                            d[i + 3] = 0;
                        } else {
                            const effCutoff = chroma >= 38 ? Math.min(235, inkCutoff + 22) : inkCutoff;
                            const rawStrength = Math.max(0, (effCutoff - lum + chroma * 0.45) / Math.max(35, effCutoff * 0.52));
                            if (rawStrength < 0.16) {
                                d[i + 3] = 0;
                            } else {
                                const cleanAlpha = Math.min(255, Math.round(Math.pow(Math.min(1, rawStrength), 0.55) * 255 * 1.45));
                                d[i + 3] = Math.round((a / 255) * cleanAlpha);
                                if (chroma < 42) {
                                    d[i] = 8;
                                    d[i + 1] = 12;
                                    d[i + 2] = 22;
                                } else {
                                    d[i] = Math.round(r * 0.38);
                                    d[i + 1] = Math.round(g * 0.38);
                                    d[i + 2] = Math.round(b * 0.46);
                                }
                            }
                        }
                    }

                    const alphaCopy = new Uint8Array(w * h);
                    for (let p = 0; p < w * h; p++) alphaCopy[p] = d[p * 4 + 3];
                    for (let y = 1; y < h - 1; y++) {
                        for (let x = 1; x < w - 1; x++) {
                            const p = y * w + x;
                            if (alphaCopy[p] === 0) continue;
                            let nCount = 0;
                            for (let dy = -1; dy <= 1; dy++) {
                                for (let dx = -1; dx <= 1; dx++) {
                                    if ((dx !== 0 || dy !== 0) && alphaCopy[(y + dy) * w + (x + dx)] > 50) nCount++;
                                }
                            }
                            if (nCount < 2) d[p * 4 + 3] = 0;
                        }
                    }
                    ctx.putImageData(imgData, 0, 0);

                    let minX = w, minY = h, maxX = -1, maxY = -1;
                    for (let y = 0; y < h; y++) {
                        for (let x = 0; x < w; x++) {
                            const alpha = d[(y * w + x) * 4 + 3];
                            if (alpha > 45) {
                                if (x < minX) minX = x;
                                if (x > maxX) maxX = x;
                                if (y < minY) minY = y;
                                if (y > maxY) maxY = y;
                            }
                        }
                    }
                    if (maxX >= minX && maxY >= minY) {
                        const pad = 6;
                        const cx = Math.max(0, minX - pad);
                        const cy = Math.max(0, minY - pad);
                        const cw = Math.min(w - cx, (maxX - minX + 1) + pad * 2);
                        const ch = Math.min(h - cy, (maxY - minY + 1) + pad * 2);
                        const cropCanvas = document.createElement('canvas');
                        cropCanvas.width = cw;
                        cropCanvas.height = ch;
                        const cropCtx = cropCanvas.getContext('2d');
                        cropCtx.drawImage(canvas, cx, cy, cw, ch, 0, 0, cw, ch);
                        cropCtx.globalAlpha = 0.45;
                        cropCtx.drawImage(canvas, cx, cy, cw, ch, 0.5, 0, cw, ch);
                        cropCtx.globalAlpha = 1.0;
                        finalCanvas = cropCanvas;
                    }
                } catch (e) {}

                const dataUrl = finalCanvas.toDataURL('image/png');
                document.querySelectorAll('.wa-policy-stamp-preview').forEach(pImg => {
                    pImg.src = dataUrl;
                    pImg.style.display = 'inline-block';
                });
                syncAdminWaPolicyFromUI(inputEl, { voucherStampImage: dataUrl });
            };
            img.src = evt.target.result;
        };
        reader.readAsDataURL(file);
        inputEl.value = '';
    }

    function clearWaVoucherStampImage() {
        document.querySelectorAll('.wa-policy-stamp-preview').forEach(pImg => {
            pImg.setAttribute('src', '');
            pImg.style.display = 'none';
        });
        syncAdminWaPolicyFromUI(null, { voucherStampImage: '', _forceClearStamp: true });
    }

    function previewFontSize(size) {
        if (window.MagaieesTypography && typeof window.MagaieesTypography.setScale === 'function') {
            const pctMap = { '14': 90, '16': 100, '18': 115, '20': 130 };
            const pct = pctMap[String(size)] || Math.round((parseFloat(size) / 16) * 100) || 100;
            window.MagaieesTypography.setScale(pct);
        } else {
            document.documentElement.style.fontSize = size + 'px';
        }
    }

    // ─── Backup Data ───────────────────────────────────────────
    async function backupData() {
        try {
            const completeStore = {};
            const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';

            // 1. Gather all keys from localStorage
            const globalKeys = ['saas_token', 'user', 'erp_lang', 'magaiees_global_theme', 'authToken'];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && (prefix === '' || k.startsWith(prefix) || globalKeys.includes(k))) {
                    const val = localStorage.getItem(k);
                    if (val !== null) completeStore[k] = val;
                }
            }

            // 2. Gather all keys from localforage (async)
            if (typeof localforage !== 'undefined') {
                try {
                    const lfKeys = await localforage.keys();
                    for (let k of lfKeys) {
                        if (prefix === '' || k.startsWith(prefix)) {
                            const val = await localforage.getItem(k);
                            if (val !== null) {
                                try { completeStore[k] = JSON.parse(val); } catch(e) { completeStore[k] = val; }
                            }
                        }
                    }
                } catch(e) { console.error("Error reading localforage:", e); }
            }

            // 3. Overlay DB_STORE memory proxy
            if (window.DB_STORE) {
                for (let [k, v] of Object.entries(window.DB_STORE)) {
                    if (prefix === '' || k.startsWith(prefix)) {
                        if (v !== null && v !== undefined) {
                            completeStore[k] = v;
                        }
                    }
                }
            }

            // Clean up empty data
            const cleanStore = {};
            let recordCount = 0;
            for (let [k, v] of Object.entries(completeStore)) {
                if (v && (Array.isArray(v) ? v.length > 0 : (typeof v === 'object' ? Object.keys(v).length > 0 : true))) {
                    cleanStore[k] = v;
                    if (Array.isArray(v)) recordCount += v.length;
                }
            }

            if (Object.keys(cleanStore).length === 0) {
                alert("⚠️ لا توجد بيانات مسجلة بالنظام للتصدير حالياً.");
                return;
            }

            // 4. Custom UI 6-Digit PIN Prompt
            const pin = await promptCustomPin({
                title: "حماية النسخة الاحتياطية",
                subtitle: "أدخل رقماً سرياً مكوناً من 6 أرقام لحماية ملف النسخة الاحتياطية، أو اختر التنزيل بدون رقم سري:",
                showSkip: true
            });

            if (pin === null) return; // User clicked Cancel

            let pinHash = null;
            if (pin && pin.trim() !== "") {
                const msgBuffer = new TextEncoder().encode(pin.trim());
                const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                pinHash = Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');
            }

            // 5. Custom UI Confirmation
            const confirmed = await confirmCustomModal({
                title: "تأكيد تنزيل النسخة الاحتياطية",
                subtitle: `سيتم تجميع كافة بيانات النظام الخاصة بالشركة الحالية وتنزيلها كملف احتياطي (يحتوي على ${recordCount} سجل)${pinHash ? ' 🔒 محمي برقم سري' : ''}. هل ترغب بالاستمرار؟`,
                confirmText: "تنزيل الآن 🚀",
                icon: "💾",
                isDanger: false
            });

            if (!confirmed) return;

            const refCode = 'REF-' + Math.random().toString(36).substr(2, 6).toUpperCase();

            // Dynamic Token System: We no longer store masterRecoveryHash.
            // Only the refCode and user's pinHash are saved.
            const exportPayload = {
                magaiees_backup_v3: true,
                refCode: refCode,
                isProtected: !!pinHash,
                pinHash: pinHash,
                store: cleanStore
            };

            const dateStr = new Date().toISOString().split('T')[0];
            const fileName = `MAGAIEES_ERP_Backup_${dateStr}.bak.json`;
            const backupDataStr = JSON.stringify(exportPayload, null, 2);

            const blob = new Blob([backupDataStr], { type: "application/json;charset=utf-8" });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            if (typeof window.logSystemAction === 'function') {
                window.logSystemAction('Database Export', 'system', 'تصدير نسخة احتياطية للشركة الحالية');
            }
            alert(`✅ تم تنزيل النسخة الاحتياطية بنجاح! (تم تصدير ${recordCount} سجل خاص بهذه الشركة)`);
        } catch(e) {
            console.error(e);
            alert("❌ حدث خطأ أثناء تنزيل النسخة الاحتياطية: " + e.message);
        }
    }

    async function restoreData(elem) {
        const file = elem.files ? elem.files[0] : null;
        if (!file) return;

        const confirmed = await confirmCustomModal({
            title: "تحذير استعادة البيانات",
            subtitle: "استعادة النسخة الاحتياطية ستقوم بدمج وتحديث كافة البيانات الحالية ببيانات الملف المرفوع. هل أنت متأكد 100% أنك تريد الاستمرار؟",
            confirmText: "تأكيد الاستعادة ⚠️",
            icon: "⚠️",
            isDanger: true
        });

        if (!confirmed) {
            elem.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = async function(e) {
            let loader = null;
            try {
                const content = e.target.result;
                
                // Check if it's a SQL file or JSON file
                if (content.trim().startsWith('--') || content.toUpperCase().includes('INSERT INTO')) {
                    if (typeof restoreFromSqlDump === 'function') {
                        restoreFromSqlDump(content);
                        return;
                    }
                }

                const data = JSON.parse(content);
                if (typeof data !== 'object' || Array.isArray(data) || data === null) {
                    throw new Error("تنسيق الملف غير صالح");
                }

                let payloadStore = data;
                if (data.magaiees_backup_v2 || data.isProtected || data.pinHash) {
                    if (data.isProtected && data.pinHash) {
                        const refCode = data.refCode || '';
                        const enteredPin = await promptCustomPin({
                            title: "الملف محمي برقم سري",
                            subtitle: "هذه النسخة الاحتياطية محمية برقم سري. يرجى إدخال الرقم السري المكون من 6 أرقام (أو المفتاح الماستر للإدارة):",
                            refCode: refCode,
                            showSkip: false
                        });

                        if (!enteredPin) {
                            alert("❌ تم إلغاء عملية الاستعادة لحماية البيانات.");
                            elem.value = '';
                            return;
                        }

                        const cleanPin = enteredPin.trim();

                        // Compute hashes for entered PIN (User's personal PIN)
                        const msgBuffer = new TextEncoder().encode(cleanPin);
                        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                        const enteredHash = Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');

                        // Dynamic Master Token Generation
                        let expectedDynamicToken = "";
                        if (refCode) {
                            const salt = "Magaiees_ERP_2026_SaaS_Secr3t";
                            const dynBuffer = new TextEncoder().encode(refCode + salt);
                            const dynHash = await crypto.subtle.digest('SHA-256', dynBuffer);
                            const hashArray = Array.from(new Uint8Array(dynHash));
                            const num = (hashArray[0] << 24 | hashArray[1] << 16 | hashArray[2] << 8 | hashArray[3]) >>> 0;
                            expectedDynamicToken = (num % 1000000).toString().padStart(6, '0');
                        }

                        // Check 1: Direct PIN match (User's PIN)
                        // Check 2: Dynamic Master Token match (Admin generated)
                        const isPinMatch = (enteredHash === data.pinHash);
                        const isMasterMatch = (cleanPin === expectedDynamicToken && expectedDynamicToken !== "");

                        if (!isPinMatch && !isMasterMatch) {
                            alert(`❌ الرقم السري غير صحيح!\n\nرمز مرجعية الملف: [${refCode || 'N/A'}]\nيرجى التواصل مع إدارة المشتركين وتزويدهم بالرمز لاسترداد كلمة السر.`);
                            elem.value = '';
                            if (loader) loader.remove();
                            return;
                        }

                        if (isMasterMatch && !isPinMatch) {
                            alert("🔓 تم فك تشفير واستعادة النسخة الاحتياطية بنجاح باستخدام المفتاح الماستر للإدارة!");
                        }
                    }
                    payloadStore = data.store || {};
                }

                // Loader screen
                loader = document.createElement('div');
                loader.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.95); backdrop-filter:blur(15px); z-index:999999; display:flex; flex-direction:column; align-items:center; justify-content:center; color:white; font-family:Tajawal; text-align:center; padding:20px;';
                loader.innerHTML = `
                    <div style="font-size:3.5rem; margin-bottom:15px; animation:spin 2s linear infinite;">🔄</div>
                    <h2 style="font-size:1.8rem; font-weight:900; margin:0 0 10px 0; color:#38bdf8;">جاري استعادة البيانات والتهيأة...</h2>
                    <p style="color:#94a3b8; font-size:1rem; margin:0;">يرجى عدم إغلاق المتصفح حتى إكتمال العملية.</p>
                `;
                document.body.appendChild(loader);

                // 1. Preserve critical session tokens
                const sessionKeys = ['authToken', 'saas_token', 'user', 'erp_lang', 'magaiees_global_theme'];
                const preservedSession = {};
                sessionKeys.forEach(k => {
                    const val = localStorage.getItem(k);
                    if (val !== null) preservedSession[k] = val;
                });

                // 2. Clear current localforage
                const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                try {
                    if (typeof localforage !== 'undefined') {
                        const keys = await localforage.keys();
                        for (let k of keys) {
                            if (k.startsWith(prefix) || prefix === '') {
                                await localforage.removeItem(k);
                            }
                        }
                    }
                } catch(err) {}

                // 3. Clear current localStorage erp_ & tenant_ keys
                const lsKeys = [];
                for (let i = 0; i < localStorage.length; i++) {
                    lsKeys.push(localStorage.key(i));
                }
                for (let k of lsKeys) {
                    if (k && !sessionKeys.includes(k) && (k.startsWith('erp_') || k.startsWith('tenant_') || k.startsWith(prefix))) {
                        localStorage.removeItem(k);
                    }
                }

                // 4. Populate imported backup with Smart Deduplicated Merging
                let restoredCount = 0;
                const currentPrefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                const mergedStore = {};

                for (let [key, val] of Object.entries(payloadStore)) {
                    let targetKey = key;
                    if (currentPrefix && key.startsWith('tenant_')) {
                        targetKey = key.replace(/^tenant_[^_]+_/, currentPrefix);
                    } else if (currentPrefix && key.startsWith('erp_')) {
                        targetKey = currentPrefix + key;
                    }

                    // Parse double-stringified JSON if present
                    if (typeof val === 'string' && (val.startsWith('[') || val.startsWith('{'))) {
                        try { val = JSON.parse(val); } catch(e) {}
                    }

                    // Smart Merge to prevent smaller/empty company arrays from overwriting main company data
                    if (mergedStore[targetKey]) {
                        let existing = mergedStore[targetKey];
                        if (Array.isArray(val) && Array.isArray(existing)) {
                            const map = new Map();
                            existing.forEach(item => {
                                const id = item && (item.id || item.code || item.invoiceNo) ? (item.id || item.code || item.invoiceNo) : JSON.stringify(item);
                                map.set(id, item);
                            });
                            val.forEach(item => {
                                const id = item && (item.id || item.code || item.invoiceNo) ? (item.id || item.code || item.invoiceNo) : JSON.stringify(item);
                                map.set(id, item);
                            });
                            mergedStore[targetKey] = Array.from(map.values());
                        } else if (typeof val === 'object' && val !== null) {
                            mergedStore[targetKey] = Object.assign({}, existing, val);
                        } else {
                            if (val) mergedStore[targetKey] = val;
                        }
                    } else {
                        mergedStore[targetKey] = val;
                    }
                }

                // Write mergedStore to localStorage, localforage, and DB_STORE
                for (let [targetKey, val] of Object.entries(mergedStore)) {
                    const valStr = typeof val === 'string' ? val : JSON.stringify(val);
                    try { localStorage.setItem(targetKey, valStr); } catch(err) {}
                    try {
                        if (typeof localforage !== 'undefined') {
                            await localforage.setItem(targetKey, val); // Store as native Object/Array
                        }
                    } catch(err) {}
                    if (window.DB_STORE) {
                        window.DB_STORE[targetKey] = val; // Store as native Object/Array
                    }
                    restoredCount++;
                }

                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('Database Import', 'system', 'استعادة نسخة احتياطية ومعالجة البيانات بنجاح');
                }

                alert(`🎉 تم استعادة كافة البيانات بنجاح (${restoredCount} جدول/سجل معالَج بنجاح)! سيتم تحديث الصفحة الآن.`);
                window.location.reload();
            } catch(err) {
                console.error(err);
                alert("❌ خطأ: ملف النسخة الاحتياطية غير صالح أو تالف (" + err.message + ")");
                elem.value = '';
                if (loader) loader.remove();
            }
        };
        reader.readAsText(file);
    }

    document.addEventListener('DBReady', loadSettings);
    document.addEventListener('strictDataReady', loadSettings);
    document.addEventListener('cloudDataRestored', loadSettings);

    if (window.APP_READY) {
        initSettingsPage();
    } else {
        document.addEventListener('DBReady', initSettingsPage);
        document.addEventListener('DOMContentLoaded', initSettingsPage);
    }
