        window.showToast = function(msg, type = 'success', duration = 2000) {
            let container = document.getElementById('md-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'md-toast-container';
                container.style.cssText = 'position:fixed; bottom:30px; left:50%; transform:translateX(-50%); z-index:9999999; display:flex; flex-direction:column; gap:10px; pointer-events:none;';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            const isError = type === 'error';
            const bg = isSuccess ? 'linear-gradient(135deg, #10b981, #059669)' : (isError ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #f59e0b, #d97706)');
            const borderCol = isSuccess ? '#34d399' : (isError ? '#f87171' : '#fbbf24');
            toast.style.cssText = `background:${bg}; color:#ffffff !important; padding:12px 24px; border-radius:12px; font-weight:900; font-size:0.96rem; box-shadow:0 12px 30px rgba(0,0,0,0.55); pointer-events:auto; font-family:'Tajawal', sans-serif; text-align:center; display:flex; align-items:center; gap:8px; border:2px solid ${borderCol}; transition:all 0.22s cubic-bezier(0.22, 1, 0.36, 1); transform:translateY(14px); opacity:0;`;
            toast.innerHTML = (isSuccess ? '<span>✅</span> ' : (isError ? '<span>🚫</span> ' : '<span>⚠️</span> ')) + `<span style="color:#ffffff !important;">${msg}</span>`;
            container.appendChild(toast);
            try {
                if (navigator.vibrate) navigator.vibrate(isError ? [35, 25, 35] : 12);
            } catch (_) {}
            requestAnimationFrame(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            });
            const ms = isSuccess ? 2000 : Math.min(Math.max(Number(duration) || 2200, 1500), 2600);
            setTimeout(() => {
                toast.style.transform = 'translateY(14px)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 220);
            }, ms);
        };

        function triggerMasterSavePulse(containerSelector) {
            try {
                const el = typeof containerSelector === 'string' ? document.querySelector(containerSelector) : containerSelector;
                const target = el || document.querySelector('.md-tab-content.active .md-card');
                if (!target) return;
                target.classList.remove('md-save-flash');
                void target.offsetWidth;
                target.classList.add('md-save-flash');
                setTimeout(() => target.classList.remove('md-save-flash'), 700);
            } catch (e) {}
        }

        window.openQuickMasterPromptModal = function(titleOrOpts, placeholder, defaultVal, onConfirm) {
            if (titleOrOpts && typeof titleOrOpts === 'object') {
                const opts = titleOrOpts;
                titleOrOpts = opts.title || 'إدخال سريع';
                placeholder = opts.placeholder || '';
                defaultVal = opts.defaultValue !== undefined ? opts.defaultValue : (opts.defaultVal || '');
                onConfirm = opts.onConfirm;
            }
            const title = titleOrOpts;
            let modal = document.getElementById('md-quick-prompt-modal');
            if (!modal) {
                document.body.insertAdjacentHTML('beforeend', `
                    <div id="md-quick-prompt-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:9999995; background:rgba(2,6,23,0.75); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:18px;">
                        <div class="md-child-modal-card" style="width:100%; max-width:420px; background:var(--card-bg, #0f172a); border:1px solid rgba(56,189,248,0.4); border-radius:16px; padding:18px; box-shadow:0 20px 50px rgba(0,0,0,0.6);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <h4 id="md-quick-prompt-title" style="margin:0; font-size:1rem; font-weight:900;">إضافة سريعة</h4>
                                <button type="button" id="md-quick-prompt-cancel-top" style="background:transparent; border:none; font-size:1.1rem; cursor:pointer; font-weight:900;">✖</button>
                            </div>
                            <div id="md-quick-prompt-fields" style="display:flex; flex-direction:column; gap:10px; margin-bottom:14px;">
                                <input type="text" id="md-quick-prompt-input" style="width:100%; height:40px; padding:0 12px; border-radius:10px; border:1px solid rgba(56,189,248,0.4); font-family:'Tajawal',sans-serif; font-size:0.95rem; font-weight:800;">
                                <input type="text" id="md-quick-prompt-input2" style="display:none; width:100%; height:40px; padding:0 12px; border-radius:10px; border:1px solid rgba(56,189,248,0.3); font-family:'Tajawal',sans-serif; font-size:0.92rem; font-weight:700;">
                            </div>
                            <div style="display:flex; justify-content:flex-end; gap:8px;">
                                <button type="button" id="md-quick-prompt-cancel" class="btn-text" style="padding:7px 14px; border-radius:9px; font-weight:800; cursor:pointer;">إلغاء <span class="kbd-hint">Esc</span></button>
                                <button type="button" id="md-quick-prompt-ok" class="btn-add" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff !important; padding:7px 18px; border-radius:9px; font-weight:900; cursor:pointer; border:none;">💾 حفظ</button>
                            </div>
                        </div>
                    </div>
                `);
                modal = document.getElementById('md-quick-prompt-modal');
            }
            const titleEl = document.getElementById('md-quick-prompt-title');
            const inp1 = document.getElementById('md-quick-prompt-input');
            const inp2 = document.getElementById('md-quick-prompt-input2');
            const okBtn = document.getElementById('md-quick-prompt-ok');
            const cancelBtn = document.getElementById('md-quick-prompt-cancel');
            const cancelTop = document.getElementById('md-quick-prompt-cancel-top');

            titleEl.textContent = title || 'إدخال سريع';
            inp1.placeholder = placeholder || '';
            inp1.value = defaultVal || '';
            inp2.style.display = 'none';
            inp2.value = '';
            modal.style.display = 'flex';
            setTimeout(() => { inp1.focus(); inp1.select(); }, 20);

            const close = () => { modal.style.display = 'none'; };
            cancelBtn.onclick = close;
            cancelTop.onclick = close;
            const submit = () => {
                const val = inp1.value.trim();
                const val2 = inp2.value.trim();
                close();
                if (typeof onConfirm === 'function') onConfirm(val, val2);
            };
            okBtn.onclick = submit;
            inp1.onkeydown = (e) => {
                if (e.key === 'Enter') { e.preventDefault(); if (inp2.style.display !== 'none') inp2.focus(); else submit(); }
                else if (e.key === 'Escape') { e.preventDefault(); close(); }
            };
            inp2.onkeydown = (e) => {
                if (e.key === 'Enter') { e.preventDefault(); submit(); }
                else if (e.key === 'Escape') { e.preventDefault(); close(); }
            };
            return { inp1, inp2, close };
        };

        window.quickAddKeeperModal = function() {
            const ui = window.openQuickMasterPromptModal('➕ إضافة أمين مخزن / موظف سريع', 'اسم الأمين أو الموظف *', '', (name, phone) => {
                if (!name) return focusRequiredMasterField('warehouse-keeper', '⚠️ يرجى كتابة اسم الأمين');
                let users = [];
                try { users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]') || []; } catch (e) {}
                const newEmp = {
                    id: 'EMP-' + Date.now(),
                    name: name,
                    phone: phone || '',
                    role: 'storekeeper',
                    securityLevel: 1,
                    status: 'active'
                };
                users.push(newEmp);
                setMasterLocalData('erp_users_v1', users);
                setMasterLocalData('erp_employees_v1', users);
                syncToBackend('save_employee', newEmp);
                populateWarehouseKeepersDatalist();
                renderEmployees();
                const keeperInp = document.getElementById('warehouse-keeper');
                if (keeperInp) keeperInp.value = name;
                if (phone && document.getElementById('warehouse-phone') && !document.getElementById('warehouse-phone').value) {
                    document.getElementById('warehouse-phone').value = phone;
                }
                showToast(`✅ تمت إضافة الأمين (${name}) وربطه بالمخزن`, 'success', 2000);
            });
            if (ui && ui.inp2) {
                ui.inp2.style.display = 'block';
                ui.inp2.placeholder = 'رقم الهاتف (اختياري)';
            }
        };

        window.quickAddBankModal = function(targetSelectId) {
            const ui = window.openQuickMasterPromptModal('➕ إضافة بنك سريع', 'اسم البنك (مثال: بنك الخرطوم) *', '', (bankName, accNo) => {
                if (!bankName) return showToast('⚠️ يرجى إدخال اسم البنك', 'warning', 2000);
                let banks = [];
                try { banks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]') || []; } catch (e) {}
                const newBank = {
                    id: 'BNK-' + Date.now(),
                    code: accNo || String(banks.filter(b => !b.deleted).length + 1),
                    name: bankName,
                    branch: 'الفرع الرئيسي',
                    openingBalance: 0,
                    currentBalance: 0,
                    glAccount: accNo || '',
                    accountNo: accNo || '',
                    currency: 'SDG',
                    exchangeRate: 1,
                    confidentiality: 1,
                    paymentMethods: ['cash', 'pos', 'transfer'],
                    isDeactivated: false
                };
                banks.push(newBank);
                setMasterLocalData('erp_banks_v1', banks, true);
                syncToBackend('save_bank', newBank);
                if (typeof renderBanks === 'function') renderBanks();
                populateWaReceiptBankDropdowns();
                const sel = document.getElementById(targetSelectId);
                if (sel) sel.value = newBank.id;
                showToast(`✅ تمت إضافة البنك (${bankName}) وتحديده`, 'success', 2000);
            });
            if (ui && ui.inp2) {
                ui.inp2.style.display = 'block';
                ui.inp2.placeholder = 'رقم الحساب البنكي (اختياري)';
            }
        };

        window.quickAddEmployeeModal = function(targetSelectId) {
            const ui = window.openQuickMasterPromptModal('➕ إضافة مسؤول / موظف سريع', 'اسم المسؤول *', '', (empName, empPhone) => {
                if (!empName) return showToast('⚠️ يرجى إدخال اسم المسؤول', 'warning', 2000);
                let users = [];
                try { users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]') || []; } catch (e) {}
                const newEmp = {
                    id: 'EMP-' + Date.now(),
                    name: empName,
                    phone: empPhone || '',
                    role: 'cashier',
                    securityLevel: 1,
                    status: 'active'
                };
                users.push(newEmp);
                setMasterLocalData('erp_users_v1', users);
                setMasterLocalData('erp_employees_v1', users);
                syncToBackend('save_employee', newEmp);
                renderEmployees();
                if (typeof populateSafeResponsibleDropdown === 'function') populateSafeResponsibleDropdown();
                const sel = document.getElementById(targetSelectId);
                if (sel) {
                    if (!Array.from(sel.options).some(o => o.value === empName)) {
                        sel.insertAdjacentHTML('beforeend', `<option value="${empName}">${empName}</option>`);
                    }
                    sel.value = empName;
                }
                showToast(`✅ تمت إضافة المسؤول (${empName})`, 'success', 2000);
            });
            if (ui && ui.inp2) {
                ui.inp2.style.display = 'block';
                ui.inp2.placeholder = 'رقم الهاتف (اختياري)';
            }
        };

        // ================= BACKEND MYSQL PERSISTENCE + ATOMIC SYNC & OFFLINE QUEUE =================
        const __mdSyncChannel = (typeof BroadcastChannel !== 'undefined') ? new BroadcastChannel('magaiees_sync_channel') : null;
        const __mdLiveChannel = (typeof BroadcastChannel !== 'undefined') ? new BroadcastChannel('erp_live_sync') : null;
        let __mdLastPulseTs = 0;

        function updateMasterSyncStatusBadge(state, detailText) {
            const el = document.getElementById('md-global-sync-indicator');
            if (!el) return;
            if (state === 'syncing') {
                el.style.color = '#fbbf24';
                el.style.background = 'rgba(245,158,11,0.14)';
                el.style.borderColor = 'rgba(245,158,11,0.4)';
                el.textContent = detailText || '⏳ مزامنة...';
            } else if (state === 'offline_queued') {
                el.style.color = '#fcd34d';
                el.style.background = 'rgba(245,158,11,0.18)';
                el.style.borderColor = 'rgba(245,158,11,0.45)';
                el.textContent = detailText || '📡 محفوظ محلياً';
            } else if (state === 'error') {
                el.style.color = '#f87171';
                el.style.background = 'rgba(239,68,68,0.16)';
                el.style.borderColor = 'rgba(239,68,68,0.45)';
                el.textContent = detailText || '⚠️ خطأ مزامنة';
            } else {
                el.style.color = '#34d399';
                el.style.background = 'rgba(16,185,129,0.12)';
                el.style.borderColor = 'rgba(16,185,129,0.3)';
                el.textContent = detailText || '⚡ متزامن';
            }
        }

        function broadcastMasterDataInstantSync(type, data) {
            try {
                const now = Date.now();
                __mdLastPulseTs = now;
                const payload = { type: 'MASTER_DATA_UPDATED', subType: type, timestamp: now };
                if (__mdSyncChannel) __mdSyncChannel.postMessage(payload);
                if (__mdLiveChannel) __mdLiveChannel.postMessage(payload);
                localStorage.setItem('erp_master_last_sync_pulse', JSON.stringify(payload));
                if (typeof window.rebuildGLFromAllTables === 'function' && !String(type).startsWith('check_')) {
                    setTimeout(() => { try { window.rebuildGLFromAllTables(); } catch (e) {} }, 25);
                }
            } catch (e) {}
        }

        function handleIncomingMasterSyncPulse(payload) {
            if (!payload || payload.timestamp === __mdLastPulseTs) return;
            __mdLastPulseTs = payload.timestamp || Date.now();
            renderAllMasterData();
            if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
            if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
        }

        if (__mdSyncChannel) __mdSyncChannel.addEventListener('message', (ev) => { if (ev && ev.data) handleIncomingMasterSyncPulse(ev.data); });
        if (__mdLiveChannel) __mdLiveChannel.addEventListener('message', (ev) => { if (ev && ev.data) handleIncomingMasterSyncPulse(ev.data); });
        window.addEventListener('storage', (ev) => {
            if (!ev || !ev.key) return;
            if (ev.key === 'erp_master_last_sync_pulse' || ev.key.startsWith('erp_')) {
                try {
                    const p = ev.key === 'erp_master_last_sync_pulse' ? JSON.parse(ev.newValue || '{}') : { timestamp: Date.now() };
                    handleIncomingMasterSyncPulse(p);
                } catch (e) {}
            } else if (ev.key === 'erp_preferred_theme' && typeof window.applyTheme === 'function') {
                window.applyTheme(ev.newValue, false);
            }
        });

        // ── الدستور السادس (6 من 14): معرف الجهاز الفريد ومحرك الدمج الذري الخالي من التضارب (Zero-Conflict CRDT Merge) ──
        const __mdDeviceId = (() => {
            try {
                let id = localStorage.getItem('erp_md_crdt_device_id');
                if (!id) {
                    id = 'DEV-' + Math.random().toString(36).substring(2, 8).toUpperCase() + '-' + Date.now().toString(36).toUpperCase();
                    localStorage.setItem('erp_md_crdt_device_id', id);
                }
                return id;
            } catch (e) { return 'DEV-LOCAL'; }
        })();
        window.__mdDeviceId = __mdDeviceId;

        function stampCrdtMetadata(data) {
            if (!data || typeof data !== 'object' || Array.isArray(data)) return data;
            const now = Date.now();
            data._crdtTs = now;
            data.updatedAt = data.updatedAt || new Date(now).toISOString();
            data._crdtDevice = __mdDeviceId;
            if (!data._crdtUuid) {
                data._crdtUuid = `${__mdDeviceId}-${now}-${Math.random().toString(36).substring(2, 7)}`;
            }
            return data;
        }

        function mergeCrdtEntityLists(localList, serverList, pendingQueue = []) {
            if (!Array.isArray(serverList)) return Array.isArray(localList) ? localList : [];
            if (!Array.isArray(localList) || localList.length === 0) return serverList;
            const pendingIds = new Set(
                (Array.isArray(pendingQueue) ? pendingQueue : [])
                    .map(q => q && q.data && q.data.id ? String(q.data.id) : '')
                    .filter(Boolean)
            );
            const localMap = new Map();
            localList.forEach(item => {
                if (item && item.id != null) localMap.set(String(item.id), item);
            });
            const merged = [];
            const seenIds = new Set();

            serverList.forEach(srvItem => {
                if (!srvItem || srvItem.id == null) return;
                const idStr = String(srvItem.id);
                seenIds.add(idStr);
                const locItem = localMap.get(idStr);
                if (!locItem) {
                    merged.push(srvItem);
                    return;
                }
                const locTs = Number(locItem._crdtTs || Date.parse(locItem.updatedAt || '') || 0);
                const srvTs = Number(srvItem._crdtTs || Date.parse(srvItem.updatedAt || '') || 0);
                if (pendingIds.has(idStr) || (locTs > 0 && srvTs > 0 && locTs > srvTs)) {
                    merged.push(Object.assign({}, srvItem, locItem));
                } else {
                    merged.push(Object.assign({}, locItem, srvItem));
                }
            });

            // Preserve any newly created offline items not yet on server
            localList.forEach(locItem => {
                if (!locItem || locItem.id == null) return;
                const idStr = String(locItem.id);
                if (!seenIds.has(idStr) && (pendingIds.has(idStr) || locItem._crdtOfflinePending)) {
                    merged.push(locItem);
                }
            });
            return merged;
        }
        window.mergeCrdtEntityLists = mergeCrdtEntityLists;

        function enqueueMasterOfflineSync(type, data) {
            try {
                if (String(type).startsWith('check_') || String(type).startsWith('delete_')) return;
                stampCrdtMetadata(data);
                if (data && typeof data === 'object') data._crdtOfflinePending = true;
                let q = JSON.parse(localStorage.getItem('erp_master_offline_queue_v1') || '[]');
                if (!Array.isArray(q)) q = [];
                const itemKey = (data && data.id) ? `${type}::${data.id}` : `${type}::bulk`;
                const existingIdx = q.findIndex(entry => entry.k === itemKey);
                const packet = { k: itemKey, type, data, ts: Date.now(), uuid: (data && data._crdtUuid) || `${__mdDeviceId}-${Date.now()}` };
                if (existingIdx > -1) {
                    q[existingIdx] = Object.assign({}, q[existingIdx], packet, { data: Object.assign({}, q[existingIdx].data || {}, data) });
                } else {
                    q.push(packet);
                }
                const sliced = q.slice(-250);
                const jsonQ = JSON.stringify(sliced);
                localStorage.setItem('erp_master_offline_queue_v1', jsonQ);
                if (typeof localforage !== 'undefined' && localforage && typeof localforage.setItem === 'function') {
                    localforage.setItem('erp_master_offline_queue_v1', jsonQ).catch(() => {});
                }
                updateMasterSyncStatusBadge('offline_queued', `📡 بالانتظار (${sliced.length})`);
            } catch (e) {}
        }

        let __mdFlushingQueue = false;
        async function flushMasterOfflineSyncQueue() {
            if (!navigator.onLine || __mdFlushingQueue) return;
            try {
                let rawQ = localStorage.getItem('erp_master_offline_queue_v1');
                if (!rawQ && typeof localforage !== 'undefined' && localforage && typeof localforage.getItem === 'function') {
                    try { rawQ = await localforage.getItem('erp_master_offline_queue_v1'); } catch (_) {}
                }
                const q = JSON.parse(rawQ || '[]');
                if (!Array.isArray(q) || q.length === 0) return;
                __mdFlushingQueue = true;
                // Sub-Second Parallel Nano-Packet Burst Upload for brief 2G connectivity sparks
                const results = await Promise.allSettled(
                    q.map(item => syncToBackend(item.type, item.data, { isRetry: true }))
                );
                const remaining = [];
                results.forEach((r, idx) => {
                    const val = r.status === 'fulfilled' ? r.value : null;
                    if (!val || val.networkOffline) {
                        remaining.push(q[idx]);
                    }
                });
                if (remaining.length > 0) {
                    const remStr = JSON.stringify(remaining);
                    localStorage.setItem('erp_master_offline_queue_v1', remStr);
                    if (typeof localforage !== 'undefined' && localforage && typeof localforage.setItem === 'function') {
                        localforage.setItem('erp_master_offline_queue_v1', remStr).catch(() => {});
                    }
                    updateMasterSyncStatusBadge('offline_queued', `📡 بالانتظار (${remaining.length})`);
                } else {
                    localStorage.removeItem('erp_master_offline_queue_v1');
                    if (typeof localforage !== 'undefined' && localforage && typeof localforage.removeItem === 'function') {
                        localforage.removeItem('erp_master_offline_queue_v1').catch(() => {});
                    }
                    updateMasterSyncStatusBadge('ok');
                    await loadMasterDataFromBackend();
                }
            } catch (e) {} finally {
                __mdFlushingQueue = false;
            }
        }
        window.flushMasterOfflineSyncQueue = flushMasterOfflineSyncQueue;

        let __mdLoadingFromBackend = false;
        // البند 1 من الدستور السادس: اقتناص شرارة الإنترنت فور قدومها (Split-Second Flash Sync)
        window.addEventListener('online', async () => {
            await flushMasterOfflineSyncQueue();
            await loadMasterDataFromBackend();
        });
        const __mdNetConn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (__mdNetConn && typeof __mdNetConn.addEventListener === 'function') {
            __mdNetConn.addEventListener('change', () => {
                if (navigator.onLine) flushMasterOfflineSyncQueue();
            });
        }
        window.addEventListener('focus', () => {
            if (navigator.onLine && !window.__mdFormDirty && !document.querySelector('input:focus, select:focus, textarea:focus')) {
                flushMasterOfflineSyncQueue();
            }
        });
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && navigator.onLine && !window.__mdFormDirty) {
                flushMasterOfflineSyncQueue();
            }
        });
        // حارس شرارة الإنترنت السريع (يفحص الطابور المحلي في 0ms كل 3 ثوانٍ ولا يرسل للشبكة إلا عند وجود حركات معلقة)
        setInterval(() => {
            if (!navigator.onLine || __mdFlushingQueue) return;
            const hasPending = localStorage.getItem('erp_master_offline_queue_v1');
            if (hasPending && hasPending !== '[]') {
                flushMasterOfflineSyncQueue();
            }
        }, 3000);
        setInterval(() => {
            if (!navigator.onLine) return;
            flushMasterOfflineSyncQueue();
            const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            const isSlow2G = Boolean(conn && (conn.saveData || /2g/i.test(conn.effectiveType || '')));
            if (!isSlow2G && document.visibilityState === 'visible' && !window.__mdFormDirty && !document.querySelector('input:focus, select:focus, textarea:focus')) {
                loadMasterDataFromBackend();
            }
        }, 25000);

        async function syncToBackend(type, data, opts = {}) {
            stampCrdtMetadata(data);
            if (!String(type).startsWith('check_') && !String(type).startsWith('delete_') && typeof window.recordMdC8SameDayTransaction === 'function') {
                window.recordMdC8SameDayTransaction(type, data);
            }
            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 5000) : null;
            try {
                if (!String(type).startsWith('check_')) {
                    updateMasterSyncStatusBadge('syncing');
                }
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const headers = {
                    'Content-Type': 'application/json'
                };
                if (token) headers['Authorization'] = 'Bearer ' + token;
                if (tenantId) headers['X-Tenant-Id'] = String(tenantId);

                const bodyPayload = JSON.stringify({ type, data, crdtDevice: __mdDeviceId, crdtTs: Date.now() });
                const res = await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: headers,
                    body: bodyPayload,
                    keepalive: bodyPayload.length < 60000,
                    signal: controller ? controller.signal : undefined
                });
                if (timeoutId) clearTimeout(timeoutId);
                const result = await res.json();
                if (result && result.success) {
                    if (data && typeof data === 'object') delete data._crdtOfflinePending;
                    if (!String(type).startsWith('check_')) {
                        updateMasterSyncStatusBadge('ok');
                        broadcastMasterDataInstantSync(type, data);
                    }
                } else if (result && result.error) {
                    console.warn(`[Master Data Sync] Server responded with error for ${type}:`, result.error);
                    if (!String(type).startsWith('check_') && !String(type).startsWith('delete_')) {
                        updateMasterSyncStatusBadge('error', `⚠️ ${result.error}`);
                        if (typeof showToast === 'function') showToast(`🚫 ${result.error}`, 'error', 2000);
                    }
                }
                return result;
            } catch(err) {
                if (timeoutId) clearTimeout(timeoutId);
                console.warn(`[Master Data Sync] Failed to save ${type} to MySQL (queued for 2G/offline):`, err);
                if (!opts.isRetry) {
                    enqueueMasterOfflineSync(type, data);
                    broadcastMasterDataInstantSync(type, data);
                }
                return { success: false, networkOffline: true, error: err ? err.message : 'Network error' };
            }
        }
        window.syncMasterDataToBackend = syncToBackend;

        async function loadMasterDataFromBackend() {
            if (__mdLoadingFromBackend) return;
            // Ensure any pending offline sparks are flushed first before pulling server state
            if (navigator.onLine && !__mdFlushingQueue) {
                const pendingRaw = localStorage.getItem('erp_master_offline_queue_v1');
                if (pendingRaw && pendingRaw !== '[]') {
                    await flushMasterOfflineSyncQueue();
                }
            }
            __mdLoadingFromBackend = true;
            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 7000) : null;
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const headers = {};
                if (token) headers['Authorization'] = 'Bearer ' + token;
                if (tenantId) headers['X-Tenant-Id'] = String(tenantId);

                const res = await fetch('api/get_master_data.php', {
                    headers,
                    signal: controller ? controller.signal : undefined
                });
                if (timeoutId) clearTimeout(timeoutId);
                const json = await res.json();
                if (json && json.success && json.data) {
                    const d = json.data;
                    let pendingQ = [];
                    try { pendingQ = JSON.parse(localStorage.getItem('erp_master_offline_queue_v1') || '[]') || []; } catch (_) {}

                    if (Array.isArray(d.units) && d.units.length > 0) {
                        setMasterLocalData('erp_global_units_v1', d.units);
                    }
                    if (Array.isArray(d.categories)) {
                        setMasterLocalData('erp_categories_v1', d.categories);
                    }
                    if (Array.isArray(d.items)) {
                        const locItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                        setMasterLocalData('erp_items_v1', mergeCrdtEntityLists(locItems, d.items, pendingQ));
                    }
                    if (Array.isArray(d.warehouses)) {
                        const locWh = JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]');
                        setMasterLocalData('erp_warehouses_v1', mergeCrdtEntityLists(locWh, d.warehouses, pendingQ));
                    }
                    if (Array.isArray(d.customers)) {
                        const locCust = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                        setMasterLocalData('erp_customers_v1', mergeCrdtEntityLists(locCust, d.customers, pendingQ));
                    }
                    if (Array.isArray(d.suppliers)) {
                        const locSup = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
                        setMasterLocalData('erp_suppliers_v1', mergeCrdtEntityLists(locSup, d.suppliers, pendingQ));
                    }
                    if (Array.isArray(d.banks)) {
                        const locBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
                        setMasterLocalData('erp_banks_v1', mergeCrdtEntityLists(locBanks, d.banks, pendingQ));
                    }
                    if (Array.isArray(d.safes)) {
                        const locSafes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
                        setMasterLocalData('erp_safes_v1', mergeCrdtEntityLists(locSafes, d.safes, pendingQ));
                    }
                    if (Array.isArray(d.cost_centers)) {
                        const locCc = JSON.parse(localStorage.getItem('erp_cost_centers_v1') || '[]');
                        const mergedCc = mergeCrdtEntityLists(locCc, d.cost_centers, pendingQ);
                        setMasterLocalData('erp_cost_centers_v1', mergedCc);
                        setMasterLocalData('erp_jobs_v1', mergedCc);
                    }
                    if (Array.isArray(d.employees) && d.employees.length > 0) {
                        const locEmp = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
                        const mergedEmp = mergeCrdtEntityLists(locEmp, d.employees, pendingQ);
                        setMasterLocalData('erp_users_v1', mergedEmp);
                        setMasterLocalData('erp_employees_v1', mergedEmp);
                    }
                    if (d.currency_rates) {
                        setMasterLocalData('erp_currency_rates_v1', d.currency_rates);
                    }
                    if (Array.isArray(d.master_audit_log) && d.master_audit_log.length > 0) {
                        setMasterLocalData('erp_master_audit_log_v1', d.master_audit_log);
                    }
                    if (typeof updateAuditBadgeCount === 'function') updateAuditBadgeCount();
                    if (typeof window.rebuildGLFromAllTables === 'function') {
                        try { window.rebuildGLFromAllTables(false); } catch(e) {}
                    }
                    if (d.whatsapp_statement_policy) {
                        const srvPol = d.whatsapp_statement_policy;
                        if (srvPol.voucherStampImage && String(srvPol.voucherStampImage).length < 150) {
                            srvPol.voucherStampImage = '';
                        }
                        if (srvPol.voucherLogoImage && String(srvPol.voucherLogoImage).length < 100) {
                            srvPol.voucherLogoImage = '';
                        }
                        let localPol = {};
                        try {
                            localPol = JSON.parse(localStorage.getItem('erp_whatsapp_statement_policy_v1') || '{}') || {};
                        } catch (e) {}
                        let needsResync = false;
                        const mergedPol = Object.assign({}, localPol, srvPol);
                        if ((!srvPol.voucherStampImage || String(srvPol.voucherStampImage).length < 150) && localPol.voucherStampImage && String(localPol.voucherStampImage).length >= 150) {
                            mergedPol.voucherStampImage = localPol.voucherStampImage;
                            needsResync = true;
                        }
                        if ((!srvPol.voucherLogoImage || String(srvPol.voucherLogoImage).length < 100) && localPol.voucherLogoImage && String(localPol.voucherLogoImage).length >= 100) {
                            mergedPol.voucherLogoImage = localPol.voucherLogoImage;
                            needsResync = true;
                        }
                        if (!srvPol.voucherCompanyName && localPol.voucherCompanyName) {
                            mergedPol.voucherCompanyName = localPol.voucherCompanyName;
                            needsResync = true;
                        }
                        localStorage.setItem('erp_whatsapp_statement_policy_v1', JSON.stringify(mergedPol));
                        if (needsResync && typeof syncToBackend === 'function') {
                            syncToBackend('whatsapp_statement_policy', mergedPol);
                        }
                    }
                    renderAllMasterData();
                    if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
                    if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
                }
            } catch(err) {
                if (timeoutId) clearTimeout(timeoutId);
                console.warn('[Master Data] Failed to load from backend (2G/Offline cache active):', err);
            } finally {
                __mdLoadingFromBackend = false;
            }
        }
        function renderAllMasterData() {
            if (typeof renderAdminWaPolicyUI === 'function') renderAdminWaPolicyUI();
            renderCustomers();
            renderSuppliers();
            const custIdEl = document.getElementById('cust-id');
            const codeNumEl = document.getElementById('cust-receipt-code-num');
            if (codeNumEl && (!custIdEl || !custIdEl.value)) {
                codeNumEl.value = getNextAvailableCustomerReceiptCode('');
                const custPinEl = document.getElementById('cust-statement-pin');
                if (custPinEl && !custPinEl.value) {
                    custPinEl.value = getNextUniqueStatementPin('');
                    updateCombinedPinPreview('cust');
                }
            }
            const supIdEl = document.getElementById('sup-id');
            const supCodeNumEl = document.getElementById('sup-receipt-code-num');
            if (supCodeNumEl && (!supIdEl || !supIdEl.value)) {
                supCodeNumEl.value = getNextAvailableCustomerReceiptCode('');
                const supPinEl = document.getElementById('sup-statement-pin');
                if (supPinEl && !supPinEl.value) {
                    supPinEl.value = getNextUniqueStatementPin('');
                    updateCombinedPinPreview('sup');
                }
            }
            renderEmployees();
            if(typeof renderBanks === 'function') renderBanks();
            if(typeof renderSafes === 'function') renderSafes();
            if(typeof renderItems === 'function') renderItems();
            renderWarehouses();
            if(typeof renderCostCenters === 'function') renderCostCenters();
        }

        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) window.location.href = 'login.php';
            if (window.location.hash) {
                const tabId = window.location.hash.substring(1);
                const tabBtn = document.querySelector(`button[onclick*="${tabId}"]`);
                if (tabBtn) tabBtn.click();
            }
            // Render local cache immediately for instant 2G responsiveness
            clearCustomerForm();
            clearSupplierForm();
            renderAllMasterData();
            // Refresh from backend asynchronously without blocking UI
            loadMasterDataFromBackend();
        });

        // Re-render when cloud sync completes (essential for iOS Safari first load/fresh sessions)
        window.addEventListener('cloudDataRestored', () => {
            console.log("[Master Data] Cloud sync completed. Re-rendering lists...");
            renderAllMasterData();
            if (typeof deduplicateEntities === 'function') deduplicateEntities();
            if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
            if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
        });
        window.addEventListener('strictDataReady', () => {
            console.log("[Master Data] Strict data ready. Re-rendering lists...");
            renderAllMasterData();
            if (typeof deduplicateEntities === 'function') deduplicateEntities();
            if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
            if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
        });

        function openTab(evt, tabId) {
            document.querySelectorAll('.md-tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.md-tab-btn').forEach(b => b.classList.remove('active'));
            const panel = document.getElementById(tabId);
            if (panel) panel.classList.add('active');
            const btn = (evt && evt.currentTarget) ? evt.currentTarget : document.querySelector(`.md-tab-btn[onclick*="${tabId}"]`);
            if (btn) btn.classList.add('active');
            if (tabId === 'tab-banks') {
                const bf = document.getElementById('bank-form');
                if (bf && bf.style.display === 'none' && typeof openBankForm === 'function') openBankForm();
                else if (bf) bf.style.display = 'block';
            } else if (tabId === 'tab-employees') {
                const ef = document.getElementById('employee-form');
                if (ef) ef.style.display = 'block';
            }
            requestAnimationFrame(() => {
                if (!panel) return;
                const firstFocus = panel.querySelector('#cust-name, #sup-name, input[id*="search"], input:not([type="hidden"]):not([disabled])');
                if (firstFocus && firstFocus.offsetParent !== null) {
                    try { firstFocus.focus({ preventScroll: true }); } catch (e) {}
                }
            });
        }

        function openInnerTab(evt, tabId) {
            const container = (evt && evt.currentTarget && evt.currentTarget.closest('.md-card')) || document.getElementById('tab-items')?.querySelector('.md-card') || document;
            container.querySelectorAll('.inner-tab-content').forEach(c => c.classList.remove('active'));
            container.querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
            const innerEl = document.getElementById(tabId);
            if (innerEl) innerEl.classList.add('active');
            if (evt && evt.currentTarget) evt.currentTarget.classList.add('active');
        }

        function setMasterLocalData(key, data, triggerGLRebuild = false) {
            const jsonStr = JSON.stringify(data);
            try {
                localStorage.setItem(key, jsonStr);
                if (window.DB_STORE && typeof window.DB_STORE === 'object') {
                    window.DB_STORE[key] = jsonStr;
                }
                if (typeof localforage !== 'undefined' && localforage && typeof localforage.setItem === 'function') {
                    localforage.setItem(key, jsonStr).catch(() => {});
                }
                if (window.dirtyKeys && typeof window.dirtyKeys.add === 'function') {
                    window.dirtyKeys.add(key);
                }
                if (typeof window.getTenantKey === 'function') {
                    const tKey = window.getTenantKey(key);
                    if (tKey && tKey !== key) {
                        localStorage.setItem(tKey, jsonStr);
                        if (window.DB_STORE && typeof window.DB_STORE === 'object') {
                            window.DB_STORE[tKey] = jsonStr;
                        }
                        if (typeof localforage !== 'undefined' && localforage && typeof localforage.setItem === 'function') {
                            localforage.setItem(tKey, jsonStr).catch(() => {});
                        }
                    }
                }
            } catch(e) {
                console.warn('setMasterLocalData error:', e);
            }
            if (triggerGLRebuild) {
                if (typeof window.computeOpeningEquilibriumSummary === 'function') {
                    try { window.computeOpeningEquilibriumSummary(); } catch(e) {}
                }
                if (typeof window.rebuildGLFromAllTables === 'function') {
                    try { window.rebuildGLFromAllTables(false); } catch(e) {}
                }
            }
        }

        // ================= GLOBAL ERP AUDIT TRAIL & EXCEL IMPORT/EXPORT ENGINE =================
        function getMasterAuditLogs() {
            try {
                const logs = JSON.parse(localStorage.getItem('erp_master_audit_log_v1') || '[]');
                return Array.isArray(logs) ? logs : [];
            } catch(e) { return []; }
        }

        function updateAuditBadgeCount() {
            const badge = document.getElementById('md-audit-count-badge');
            if (badge) badge.innerText = String(getMasterAuditLogs().length);
        }

        function getMasterDeviceFingerprint() {
            try {
                const ua = navigator.userAgent || '';
                const isMob = /Android|iPhone|iPad|iPod|Mobile/i.test(ua);
                const os = /Windows/i.test(ua) ? 'Windows' : (/Android/i.test(ua) ? 'Android' : (/iPhone|iPad/i.test(ua) ? 'iOS' : (/Mac/i.test(ua) ? 'Mac' : 'Web')));
                return `${isMob ? '📱 موبايل' : '💻 لابتوب'} (${os})`;
            } catch (e) {
                return '💻 جهاز النظام';
            }
        }

        function logMasterDataAudit(actionType, entityLabel, entityName, detailsStr = '') {
            try {
                let userStr = 'مدير النظام';
                try {
                    const u = JSON.parse(localStorage.getItem('user') || '{}');
                    if (u && (u.name || u.fullName || u.username)) userStr = u.name || u.fullName || u.username;
                } catch(e) {}
                const deviceStr = getMasterDeviceFingerprint();
                const logs = getMasterAuditLogs();
                logs.unshift({
                    id: 'AUD-' + Date.now() + '-' + Math.floor(Math.random() * 1000),
                    timestamp: new Date().toISOString(),
                    user: userStr,
                    device: deviceStr,
                    action: actionType, // 'إضافة' | 'تعديل' | 'حذف' | 'استيراد Excel'
                    entityLabel: entityLabel,
                    entityName: entityName,
                    details: detailsStr
                });
                const trimmed = logs.slice(0, 500);
                setMasterLocalData('erp_master_audit_log_v1', trimmed);
                updateAuditBadgeCount();
                if (typeof window.appendMdC13BlackBoxEntry === 'function') {
                    window.appendMdC13BlackBoxEntry(actionType, entityLabel + ': ' + entityName, '-', detailsStr, userStr, deviceStr);
                }
                if (typeof syncToBackend === 'function') {
                    syncToBackend('master_audit_log', trimmed);
                }
            } catch(e) { console.warn('Audit log error:', e); }
        }

        function openMasterAuditLogModal() {
            let modal = document.getElementById('md-audit-trail-modal');
            if (!modal) {
                document.body.insertAdjacentHTML('beforeend', `
                    <div id="md-audit-trail-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(2,6,23,0.82); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
                        <div class="md-child-modal-card" style="width:100%; max-width:980px; max-height:85vh; background:linear-gradient(145deg, #0f172a, #1e293b); border:1px solid rgba(168,85,247,0.45); border-radius:18px; box-shadow:0 25px 60px rgba(0,0,0,0.65); display:flex; flex-direction:column; overflow:hidden;">
                            <div style="padding:14px 20px; background:rgba(168,85,247,0.12); border-bottom:1px solid rgba(168,85,247,0.3); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="font-size:1.35rem;">🛡️</span>
                                    <h3 style="margin:0; color:#e9d5ff; font-size:1.1rem; font-weight:900;">سجل الرقابة والبصمة الأمنية للأجهزة</h3>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <button type="button" onclick="window.printMasterHtmlReport('سجل الرقابة والبصمة الأمنية', document.getElementById('md-audit-trail-body')?.innerHTML || '', 'a4')" style="background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:6px 12px; border-radius:8px; font-weight:800; cursor:pointer;">🖨️ طباعة A4</button>
                                    <button type="button" onclick="document.getElementById('md-audit-trail-modal').style.display='none'" style="background:rgba(239,68,68,0.18); color:#f87171; border:1px solid rgba(239,68,68,0.4); padding:6px 14px; border-radius:8px; font-weight:800; cursor:pointer;">✖ إغلاق <span class="kbd-hint">Esc</span></button>
                                </div>
                            </div>
                            <div id="md-audit-trail-body" style="padding:18px; overflow-y:auto; flex:1;"></div>
                        </div>
                    </div>
                `);
                modal = document.getElementById('md-audit-trail-modal');
            }
            const body = document.getElementById('md-audit-trail-body');
            const logs = getMasterAuditLogs();
            if (logs.length === 0) {
                body.innerHTML = `<div style="text-align:center; padding:40px; color:#94a3b8; font-weight:bold;">لا توجد حركات مسجلة في سجل الرقابة حتى الآن.</div>`;
            } else {
                body.innerHTML = `
                    <table class="pro-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>التاريخ والوقت</th>
                                <th>المستخدم</th>
                                <th>الجهاز</th>
                                <th>العملية</th>
                                <th>القسم</th>
                                <th>العنصر / الحساب</th>
                                <th>التفاصيل والبيان</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${logs.map(l => {
                                const dt = l.timestamp ? l.timestamp.replace('T', ' ').substring(0, 19) : '-';
                                const actColor = l.action.includes('حذف') ? '#f87171' : (l.action.includes('تعديل') ? '#fbbf24' : '#34d399');
                                return `<tr>
                                    <td style="font-family:monospace; font-size:0.8rem; direction:ltr; text-align:center;">${dt}</td>
                                    <td style="font-weight:bold; color:#38bdf8;">${l.user || 'مدير النظام'}</td>
                                    <td style="font-size:0.78rem; font-weight:800;">${l.device || '💻 جهاز النظام'}</td>
                                    <td><span style="background:rgba(255,255,255,0.06); color:${actColor}; padding:2px 8px; border-radius:6px; font-weight:800; font-size:0.8rem;">${l.action}</span></td>
                                    <td style="font-weight:bold;">${l.entityLabel || '-'}</td>
                                    <td style="font-weight:900; color:#fff;">${l.entityName || '-'}</td>
                                    <td style="font-size:0.82rem; color:#cbd5e1;">${l.details || '-'}</td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                `;
            }
            modal.style.display = 'flex';
        }

        function getActiveMasterTabId() {
            const activeEl = document.querySelector('.md-tab-content.active');
            return activeEl ? activeEl.id : 'tab-warehouses';
        }

        function exportActiveTabToExcel() {
            const tabId = getActiveMasterTabId();
            let rows = [];
            let filename = 'master_data';
            const esc = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';

            if (tabId === 'tab-warehouses') {
                filename = 'warehouses_المخازن';
                const list = (JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]') || []).filter(x => !x.deleted);
                rows.push(['الكود', 'اسم المخزن', 'النوع', 'أمين المخزن', 'الهاتف', 'الموقع', 'الحالة', 'درجة السرية']);
                list.forEach(w => rows.push([w.id, w.name, w.type, w.keeper || '', w.phone || '', w.location || '', w.status || 'active', w.securityLevel || 1]));
            } else if (tabId === 'tab-customers') {
                filename = 'customers_العملاء';
                const list = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]') || []).filter(x => !x.deleted);
                rows.push(['الكود', 'اسم العميل', 'الباسورد السري', 'رقم الواتساب', 'الرصيد الافتتاحي', 'الرصيد الحالي', 'حد الائتمان', 'شريحة التسعير', 'فترة السداد (يوم)', 'العملة', 'المسؤول', 'العنوان', 'الحالة']);
                list.forEach(c => rows.push([c.id, c.name, c.statementPin || '', c.whatsapp || c.phone || '', (c.openingDebit || 0) - (c.openingCredit || 0), c.currentBalance || 0, c.creditLimit || 0, c.priceTier || 'cash', c.paymentTermsDays || 0, c.currency || 'SDG', c.contactPerson || '', c.address || '', c.status || 'active']));
            } else if (tabId === 'tab-suppliers') {
                filename = 'suppliers_الموردين';
                const list = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]') || []).filter(x => !x.deleted);
                rows.push(['الكود', 'اسم المورد', 'الباسورد السري', 'رقم الواتساب', 'الرصيد الافتتاحي', 'الرصيد الحالي', 'حد الائتمان', 'فترة السداد (يوم)', 'العملة', 'العنوان', 'الحالة']);
                list.forEach(s => rows.push([s.id, s.name, s.statementPin || '', s.whatsapp || s.phone || '', (s.openingCredit || 0) - (s.openingDebit || 0), s.currentBalance || 0, s.creditLimit || 0, s.paymentTermsDays || 0, s.currency || 'SDG', s.address || '', s.status || 'active']));
            } else if (tabId === 'tab-banks') {
                filename = 'banks_البنوك';
                const list = (JSON.parse(localStorage.getItem('erp_banks_v1') || '[]') || []).filter(x => !x.deleted);
                rows.push(['الرقم', 'اسم البنك', 'الفرع', 'رقم الحساب', 'IBAN', 'العملة', 'الرصيد الافتتاحي', 'الرصيد الحالي']);
                list.forEach(b => rows.push([b.id, b.name, b.branch || '', b.glAccount || '', b.iban || '', b.currency || 'SDG', b.openingBalance || 0, b.currentBalance || 0]));
            } else if (tabId === 'tab-safes') {
                filename = 'safes_الخزن';
                const list = (JSON.parse(localStorage.getItem('erp_safes_v1') || '[]') || []).filter(x => !x.deleted);
                rows.push(['الرقم', 'اسم الخزينة', 'المسؤول', 'العملة', 'الرصيد الافتتاحي', 'الرصيد الحالي', 'الحد الأعلى', 'الحد الأدنى']);
                list.forEach(s => rows.push([s.id, s.name, s.responsible || '', s.currency || 'SDG', s.openingBalance || 0, s.currentBalance || 0, s.maxLimit || 0, s.minLimit || 0]));
            } else if (tabId === 'tab-items') {
                filename = 'items_الأصناف';
                const list = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]') || []).filter(x => !x.deleted);
                rows.push(['كود الصنف', 'اسم الصنف', 'الفئة', 'الوحدة الأساسية', 'سعر البيع النقدي', 'سعر الآجل', 'أقل سعر', 'الباركود', 'الكمية الافتتاحية', 'الحد الأدنى', 'الرف / الموقع', 'الحالة']);
                list.forEach(i => {
                    const u0 = (i.units && i.units[0]) ? i.units[0] : {};
                    rows.push([i.id, i.name, i.category || '', i.baseUnit || u0.unitName || 'قطعة', i.sellingPrice || u0.cashPrice || 0, u0.creditPrice || 0, u0.minPrice || 0, i.barcode || u0.barcode || '', i.openingQty || 0, i.minLimit || 0, i.binLocation || '', i.status || 'active']);
                });
            } else if (tabId === 'tab-employees') {
                filename = 'employees_الموظفين';
                const list = (JSON.parse(localStorage.getItem('erp_users_v1') || '[]') || []).filter(x => !x.deleted);
                rows.push(['الكود', 'الاسم', 'الدور', 'الهاتف', 'درجة السرية', 'الحالة']);
                list.forEach(e => rows.push([e.id, e.name, e.role || '', e.phone || '', e.securityLevel || 1, e.status || 'active']));
            } else {
                filename = 'cost_centers_مراكز_التكلفة';
                const list = (JSON.parse(localStorage.getItem('erp_jobs_v1') || '[]') || []);
                rows.push(['الكود', 'اسم مركز التكلفة', 'الوصف']);
                list.forEach(c => rows.push([c.id, c.name || c.title || '', c.desc || '']));
            }

            const csvContent = '\uFEFF' + rows.map(r => r.map(esc).join(',')).join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (typeof showToast === 'function') showToast(`📤 تم تصدير ملف Excel (${filename})`, 'success', 2000);
        }

        function triggerActiveTabExcelImport() {
            const inp = document.getElementById('md-excel-import-input');
            if (inp) {
                inp.value = '';
                inp.click();
            }
        }

        function parseCSVRow(line) {
            const res = [];
            let cur = '';
            let inQuotes = false;
            for (let i = 0; i < line.length; i++) {
                const ch = line[i];
                if (ch === '"') {
                    if (inQuotes && line[i + 1] === '"') { cur += '"'; i++; }
                    else inQuotes = !inQuotes;
                } else if ((ch === ',' || ch === ';') && !inQuotes) {
                    res.push(cur.trim());
                    cur = '';
                } else {
                    cur += ch;
                }
            }
            res.push(cur.trim());
            return res;
        }

        async function handleActiveTabExcelImport(event, bypassPreview = false, preloadedData = null) {
            let file = preloadedData ? preloadedData.file : (event && event.target && event.target.files ? event.target.files[0] : null);
            if (!file) return;
            const text = preloadedData ? preloadedData.text : await file.text();
            const lines = text.replace(/^\uFEFF/, '').split(/\r?\n/).filter(l => l.trim() !== '');
            if (lines.length < 2) {
                if (typeof showToast === 'function') showToast('⚠️ الملف فارغ أو لا يحتوي على صفوف بيانات', 'warning', 2000);
                return;
            }
            const tabId = preloadedData ? preloadedData.tabId : getActiveMasterTabId();
            if (!bypassPreview && typeof window.openPreImportValidationModal === 'function') {
                window.openPreImportValidationModal(file, text, lines, tabId, () => {
                    handleActiveTabExcelImport(null, true, { file, text, tabId });
                });
                return;
            }
            if (typeof window.saveMasterImportRollbackSnapshot === 'function') {
                window.saveMasterImportRollbackSnapshot(tabId, file.name);
            }
            let addedCount = 0;
            let updatedCount = 0;

            if (tabId === 'tab-items') {
                let items = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]') || []).filter(i => !i.deleted);
                for (let idx = 1; idx < lines.length; idx++) {
                    const cols = parseCSVRow(lines[idx]);
                    const code = cols[0] || ('ITEM-' + Date.now() + '-' + idx);
                    const name = (cols[1] || '').trim();
                    if (!name) continue;
                    const category = cols[2] || 'مواد بناء';
                    const baseUnit = cols[3] || 'قطعة';
                    const cashPrice = parseFloat(cols[4]) || 0;
                    const creditPrice = parseFloat(cols[5]) || 0;
                    const minPrice = parseFloat(cols[6]) || 0;
                    const barcode = cols[7] || '';
                    const openingQty = parseFloat(cols[8]) || 0;
                    const minLimit = parseFloat(cols[9]) || 0;
                    const binLocation = cols[10] || '';
                    const existingIdx = items.findIndex(i => i.id === code || (i.name && i.name.trim() === name));
                    const itemObj = {
                        id: existingIdx > -1 ? items[existingIdx].id : code,
                        name, category, baseUnit, unit: baseUnit, sellingPrice: cashPrice, barcode,
                        openingQty, minLimit, binLocation, status: 'active', nature: 'مخزني',
                        units: [{ unitName: baseUnit, factor: 1, barcode, cashPrice, creditPrice, minPrice, weight: 0, stock: openingQty }],
                        unitsArray: [{ name: baseUnit, unitName: baseUnit, factor: 1, price: cashPrice, cashPrice, creditPrice, minPrice, barcode }]
                    };
                    if (existingIdx > -1) { items[existingIdx] = Object.assign({}, items[existingIdx], itemObj); updatedCount++; }
                    else { items.push(itemObj); addedCount++; }
                }
                setMasterLocalData('erp_items_v1', items, true);
                syncToBackend('items_bulk', items);
                renderItems();
                logMasterDataAudit('استيراد Excel', 'الأصناف', file.name, `إضافة: ${addedCount} | تحديث: ${updatedCount}`);
            } else if (tabId === 'tab-customers') {
                let custs = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]') || []).filter(c => !c.deleted);
                for (let idx = 1; idx < lines.length; idx++) {
                    const cols = parseCSVRow(lines[idx]);
                    const code = cols[0] || ('CUST-' + Date.now() + '-' + idx);
                    const name = (cols[1] || '').trim();
                    if (!name) continue;
                    const pin = (cols[2] || '').replace(/[^0-9]/g, '') || getNextUniqueStatementPin('');
                    const wa = cols[3] || '';
                    const opBal = parseFloat(cols[4]) || 0;
                    const limit = parseFloat(cols[6]) || 0;
                    const priceTier = cols[7] || 'cash';
                    const payTerms = parseInt(cols[8], 10) || 0;
                    const existingIdx = custs.findIndex(c => c.id === code || (c.name && c.name.trim() === name));
                    const cObj = {
                        id: existingIdx > -1 ? custs[existingIdx].id : code,
                        name, phone: wa, whatsapp: wa, statementPin: pin,
                        openingDebit: opBal >= 0 ? opBal : 0, openingCredit: opBal < 0 ? Math.abs(opBal) : 0,
                        currentBalance: opBal, creditLimit: limit, priceTier, paymentTermsDays: payTerms,
                        currency: cols[9] || 'SDG', contactPerson: cols[10] || '', address: cols[11] || '', status: 'active'
                    };
                    if (existingIdx > -1) { custs[existingIdx] = Object.assign({}, custs[existingIdx], cObj); updatedCount++; }
                    else { custs.push(cObj); addedCount++; }
                    syncToBackend('save_customer', cObj);
                }
                setMasterLocalData('erp_customers_v1', custs, true);
                renderCustomers();
                logMasterDataAudit('استيراد Excel', 'العملاء', file.name, `إضافة: ${addedCount} | تحديث: ${updatedCount}`);
            } else if (tabId === 'tab-suppliers') {
                let sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]') || []).filter(s => !s.deleted);
                for (let idx = 1; idx < lines.length; idx++) {
                    const cols = parseCSVRow(lines[idx]);
                    const code = cols[0] || ('SUP-' + Date.now() + '-' + idx);
                    const name = (cols[1] || '').trim();
                    if (!name) continue;
                    const pin = (cols[2] || '').replace(/[^0-9]/g, '') || getNextUniqueStatementPin('');
                    const wa = cols[3] || '';
                    const opBal = parseFloat(cols[4]) || 0;
                    const limit = parseFloat(cols[6]) || 0;
                    const payTerms = parseInt(cols[7], 10) || 0;
                    const existingIdx = sups.findIndex(s => s.id === code || (s.name && s.name.trim() === name));
                    const sObj = {
                        id: existingIdx > -1 ? sups[existingIdx].id : code,
                        name, phone: wa, whatsapp: wa, statementPin: pin,
                        openingCredit: opBal >= 0 ? opBal : 0, openingDebit: opBal < 0 ? Math.abs(opBal) : 0,
                        currentBalance: opBal, creditLimit: limit, paymentTermsDays: payTerms,
                        currency: cols[8] || 'SDG', address: cols[9] || '', status: 'active'
                    };
                    if (existingIdx > -1) { sups[existingIdx] = Object.assign({}, sups[existingIdx], sObj); updatedCount++; }
                    else { sups.push(sObj); addedCount++; }
                    syncToBackend('save_supplier', sObj);
                }
                setMasterLocalData('erp_suppliers_v1', sups, true);
                renderSuppliers();
                logMasterDataAudit('استيراد Excel', 'الموردين', file.name, `إضافة: ${addedCount} | تحديث: ${updatedCount}`);
            } else if (tabId === 'tab-warehouses') {
                let whs = (JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]') || []).filter(w => !w.deleted);
                for (let idx = 1; idx < lines.length; idx++) {
                    const cols = parseCSVRow(lines[idx]);
                    const code = cols[0] || ('WH-' + Date.now() + '-' + idx);
                    const name = (cols[1] || '').trim();
                    if (!name) continue;
                    const wObj = { id: code, name, type: cols[2] || 'مستودع رئيسي', keeper: cols[3] || '', phone: cols[4] || '', location: cols[5] || '', status: cols[6] || 'active', securityLevel: parseInt(cols[7]) || 1 };
                    const existingIdx = whs.findIndex(w => w.id === code || (w.name && w.name.trim() === name));
                    if (existingIdx > -1) { whs[existingIdx] = Object.assign({}, whs[existingIdx], wObj); updatedCount++; }
                    else { whs.push(wObj); addedCount++; }
                    syncToBackend('save_warehouse', wObj);
                }
                setMasterLocalData('erp_warehouses_v1', whs);
                renderWarehouses();
                logMasterDataAudit('استيراد Excel', 'المخازن', file.name, `إضافة: ${addedCount} | تحديث: ${updatedCount}`);
            } else {
                if (typeof showToast === 'function') showToast('⚠️ افتح تبويب الأصناف أو العملاء أو الموردين أو المخازن للاستيراد', 'warning', 2000);
                return;
            }
            if (typeof showToast === 'function') showToast(`📥 تم الاستيراد (إضافة: ${addedCount} | تحديث: ${updatedCount})`, 'success', 2000);
        }

        // ================= WAREHOUSES LOGIC =================
        function populateWarehouseKeepersDatalist() {
            const dl = document.getElementById('wh-keepers-datalist');
            if (!dl) return;
            try {
                const emps = (JSON.parse(localStorage.getItem('erp_users_v1') || '[]') || []).filter(e => !e.deleted && e.status !== 'inactive');
                dl.innerHTML = emps.map(e => `<option value="${e.name || e.fullName || e.username || ''}"></option>`).join('');
            } catch(e) {}
        }

        function focusRequiredMasterField(inputId, message) {
            const el = document.getElementById(inputId);
            if (el) {
                try {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.focus();
                } catch (e) {}
                const wrap = el.closest('.pro-field-card, .input-group') || el;
                const prevBorder = wrap.style.borderColor;
                const prevShadow = wrap.style.boxShadow;
                wrap.classList.remove('md-c3-error-shake');
                el.classList.remove('md-c3-error-shake');
                void wrap.offsetWidth;
                wrap.classList.add('md-c3-error-shake');
                el.classList.add('md-c3-error-shake');
                wrap.style.borderColor = '#ef4444';
                wrap.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.42), 0 0 18px rgba(239, 68, 68, 0.3)';
                setTimeout(() => {
                    wrap.classList.remove('md-c3-error-shake');
                    el.classList.remove('md-c3-error-shake');
                    wrap.style.borderColor = prevBorder;
                    wrap.style.boxShadow = prevShadow;
                }, 2000);
            }
            if (typeof showToast === 'function' && message) {
                showToast(message, 'error', 2200);
            }
            return false;
        }

        function saveWarehouse() {
            const id = document.getElementById('warehouse-id').value || 'WH-' + Date.now();
            const name = document.getElementById('warehouse-name').value;
            if(!name.trim()) { return focusRequiredMasterField('warehouse-name', '⚠️ الرجاء إدخال اسم المخزن'); }
            if(isDuplicateName('erp_warehouses_v1', name, id)) { return focusRequiredMasterField('warehouse-name', '⚠️ اسم المخزن موجود مسبقاً'); }
            
            const type = document.getElementById('warehouse-type').value;
            const keeper = (document.getElementById('warehouse-keeper')?.value || '').trim();
            const phone = (document.getElementById('warehouse-phone')?.value || '').trim();
            const location = (document.getElementById('warehouse-location')?.value || '').trim();
            const status = document.getElementById('warehouse-status')?.value || 'active';
            const security = parseInt(document.getElementById('warehouse-security').value) || 1;

            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            const existingIdx = warehouses.findIndex(w => w.id === id);

            const whData = { id, name, type, keeper, phone, location, status, securityLevel: security };

            if (existingIdx > -1) {
                warehouses[existingIdx] = whData;
            } else {
                warehouses.push(whData);
            }

            setMasterLocalData('erp_warehouses_v1', warehouses);
            syncToBackend('save_warehouse', whData);
            logMasterDataAudit(existingIdx > -1 ? 'تعديل' : 'إضافة', 'المخازن', name, `النوع: ${type} | الأمين: ${keeper || '-'} | الحالة: ${status}`);
            cancelEditWarehouse();
            renderWarehouses();
            triggerMasterSavePulse('#tab-warehouses .md-card');
            
            if(typeof showToast === 'function') showToast('✅ تم حفظ المخزن بنجاح', 'success', 2000);
        }

        function renderWarehouses() {
            populateWarehouseKeepersDatalist();
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
            const qRaw = (document.getElementById('warehouse-search-input')?.value || '').trim();
            if (qRaw) {
                const norm = typeof window.normalizeArabicFuzzyText === 'function' ? window.normalizeArabicFuzzyText : (s => String(s || '').toLowerCase());
                const q = norm(qRaw);
                warehouses = warehouses.filter(w =>
                    norm(w.name).includes(q) ||
                    norm(w.keeper).includes(q) ||
                    norm(w.location).includes(q) ||
                    norm(w.phone).includes(q) ||
                    norm(w.type).includes(q)
                );
            }
            tbody.innerHTML = '';
            
            if(warehouses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 15px; color: var(--text-secondary);">لا توجد مخازن مطابقة</td></tr>';
                return;
            }

            const statusBadgeMap = {
                'active': '<span style="background:rgba(52,211,153,0.15); color:#34d399; padding:3px 8px; border-radius:8px; font-size:0.78rem; font-weight:800;">🟢 نشط</span>',
                'inventory_lock': '<span style="background:rgba(245,158,11,0.18); color:#fbbf24; padding:3px 8px; border-radius:8px; font-size:0.78rem; font-weight:800;">🟠 جرد</span>',
                'inactive': '<span style="background:rgba(239,68,68,0.15); color:#f87171; padding:3px 8px; border-radius:8px; font-size:0.78rem; font-weight:800;">🔴 موقوف</span>'
            };

            tbody.innerHTML = warehouses.map(w => {
                const stBadge = statusBadgeMap[w.status || 'active'] || statusBadgeMap['active'];
                return `<tr>
                    <td><strong>${w.name}</strong></td>
                    <td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${w.type}</span></td>
                    <td>${w.keeper || '-'}</td>
                    <td style="direction:ltr; font-family:monospace;">${w.phone || '-'}</td>
                    <td>${w.location || '-'}</td>
                    <td>${stBadge}</td>
                    <td>${w.securityLevel || 1}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn-text" style="color:#34d399; font-weight:800; margin-left:6px;" onclick="window.location.href='warehouse-ledger.php?warehouse='+encodeURIComponent('${(w.name || '').replace(/'/g, "\\'")}')" title="فتح دفتر حركات المخزن">📊 دفتر المخزن</button>
                        <button class="btn-text" style="color:#fbbf24; font-weight:800; margin-left:6px;" onclick="window.location.href='inventory-stocktake.php'" title="المطابقة والجرد الفعلي للمخزن">📋 جرد</button>
                        <button class="btn-text" style="color:#38bdf8; font-weight:bold;" onclick="editWarehouse('${w.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); font-weight:bold; margin-right:10px;" onclick="deleteWarehouse('${w.id}')">🗑️ حذف</button>
                    </td>
                </tr>`;
            }).join('');
        }

        function editWarehouse(id) {
            populateWarehouseKeepersDatalist();
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
            const form = document.getElementById('warehouse-form');
            if (form) {
                form.style.display = 'block';
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            document.getElementById('warehouse-id').value = wh.id;
            document.getElementById('warehouse-name').value = wh.name;
            document.getElementById('warehouse-type').value = wh.type || 'مستودع رئيسي';
            if (document.getElementById('warehouse-keeper')) document.getElementById('warehouse-keeper').value = wh.keeper || '';
            if (document.getElementById('warehouse-phone')) document.getElementById('warehouse-phone').value = wh.phone || '';
            if (document.getElementById('warehouse-location')) document.getElementById('warehouse-location').value = wh.location || '';
            if (document.getElementById('warehouse-status')) document.getElementById('warehouse-status').value = wh.status || 'active';
            document.getElementById('warehouse-security').value = wh.securityLevel || '1';
            document.getElementById('btn-cancel-warehouse').style.display = 'inline-block';
        }

        function cancelEditWarehouse() {
            document.getElementById('warehouse-form').style.display = 'none';
            document.getElementById('warehouse-id').value = '';
            document.getElementById('warehouse-name').value = '';
            document.getElementById('warehouse-type').value = 'مستودع رئيسي';
            if (document.getElementById('warehouse-keeper')) document.getElementById('warehouse-keeper').value = '';
            if (document.getElementById('warehouse-phone')) document.getElementById('warehouse-phone').value = '';
            if (document.getElementById('warehouse-location')) document.getElementById('warehouse-location').value = '';
            if (document.getElementById('warehouse-status')) document.getElementById('warehouse-status').value = 'active';
            document.getElementById('warehouse-security').value = '1';
        }
        window.closeWarehouseForm = cancelEditWarehouse;
        window.closeEmployeeForm = function() {
            const ef = document.getElementById('employee-form');
            if (ef) ef.style.display = 'none';
        };

        function openWarehouseForm() {
            cancelEditWarehouse();
            document.getElementById('warehouse-form').style.display = 'block';
            setTimeout(() => document.getElementById('warehouse-name')?.focus(), 30);
        }

        function deleteWarehouse(id) {
            genericDeleteEntity('warehouse', id);
        }

        function isDuplicateName(storageKey, name, currentId) {
            if (!name) return false;
            try {
                const items = JSON.parse(localStorage.getItem(storageKey) || '[]');
                const normFn = typeof window.normalizeArabicFuzzyText === 'function' ? window.normalizeArabicFuzzyText : (s => String(s || '').trim().toLowerCase());
                const nameNorm = normFn(name);
                for (let i = 0; i < items.length; i++) {
                    if (items[i].deleted) continue;
                    if (items[i].id !== currentId && normFn(items[i].name || '') === nameNorm) {
                        return true;
                    }
                }
            } catch(e) {}
            return false;
        }

        // ================= CUSTOMERS LOGIC =================
        function normalizeDigitsToAscii(str) {
            if (str === null || str === undefined) return '';
            const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            let s = String(str);
            for (let i = 0; i < 10; i++) {
                s = s.replace(new RegExp(ar[i], 'g'), String(i)).replace(new RegExp(fa[i], 'g'), String(i));
            }
            return s;
        }

        function isSystemAdminForStatementControl(showDenyToast = true) {
            try {
                const u = JSON.parse(localStorage.getItem('user') || localStorage.getItem('erp_current_user') || '{}');
                const role = String(u.role || u.userRole || localStorage.getItem('userRole') || '').trim().toLowerCase();
                if (!role) return true;
                const adminRoles = ['admin', 'system_admin', 'super_admin', 'superadmin', 'manager', 'owner', 'مدير النظام', 'مدير عام', 'مدير', 'المدير العام'];
                const isAdmin = adminRoles.some(r => role.includes(r));
                if (!isAdmin && showDenyToast && typeof showToast === 'function') {
                    showToast('⛔ هذه الصلاحية لمدير النظام فقط', 'error', 2000);
                }
                return isAdmin;
            } catch (e) {
                return true;
            }
        }

        function getEntityStatementMode(obj) {
            if (!obj) return 'disabled';
            if (obj.statementAccessMode && ['disabled', 'balance_only', 'detailed'].includes(obj.statementAccessMode)) {
                return obj.statementAccessMode;
            }
            return obj.allowStatementRequest ? 'detailed' : 'disabled';
        }

        function getEntityReceiptApprovalMode(obj) {
            if (!obj) return 'auto_post';
            if (obj.receiptApprovalMode && ['auto_post', 'review_first'].includes(obj.receiptApprovalMode)) {
                return obj.receiptApprovalMode;
            }
            return 'auto_post';
        }

        function getActiveSystemBanksList() {
            try {
                const list = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
                if (Array.isArray(list)) {
                    return list.filter(b => b && !b.deleted && !b.isDeactivated);
                }
            } catch (e) {}
            return [];
        }

        function populateWaReceiptBankDropdowns() {
            const banks = getActiveSystemBanksList();
            const policy = getAdminWaPolicy();

            document.querySelectorAll('.wa-policy-default-bank-id').forEach(sel => {
                const prev = sel.value || policy.defaultReceiptBankId || '';
                sel.innerHTML = `<option value="">🏦 تلقائي</option>` +
                    banks.map(b => `<option value="${b.id}">🏦 ${b.name}${b.accountNo ? ' (' + b.accountNo + ')' : ''}</option>`).join('');
                sel.value = prev;
            });

            ['cust', 'sup'].forEach(prefix => {
                const sel = document.getElementById(`${prefix}-default-bank-id`);
                if (!sel) return;
                const prev = sel.value || '';
                sel.innerHTML = `<option value="">🏦 البنك الافتراضي</option>` +
                    banks.map(b => `<option value="${b.id}">🏦 ${b.name}</option>`).join('');
                sel.value = prev;
            });
        }

        function getAdminWaPolicy() {
            let fallbackCompany = '';
            try {
                const sysCfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                fallbackCompany = (sysCfg.companyName || sysCfg.company_name || '').trim();
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
                const saved = JSON.parse(localStorage.getItem('erp_whatsapp_statement_policy_v1') || '{}');
                if (saved && typeof saved === 'object') {
                    const merged = Object.assign({}, defaults, saved);
                    if (!merged.voucherCompanyName && fallbackCompany) {
                        merged.voucherCompanyName = fallbackCompany;
                    }
                    if (!merged.voucherStampText) {
                        merged.voucherStampText = 'ختم مصادقة مالية إلكترونية';
                    }
                    return merged;
                }
            } catch (e) {}
            return defaults;
        }

        function getNextUniqueStatementPin(excludeId = '', extraUsedSet = null) {
            let customers = [];
            let suppliers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) { customers = []; }
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) { suppliers = []; }

            const usedPins = new Set(extraUsedSet ? Array.from(extraUsedSet) : []);
            customers.forEach(c => {
                if (!c || c.deleted) return;
                if (excludeId && String(c.id) === String(excludeId)) return;
                const p = normalizeDigitsToAscii(c.statementPin || '').replace(/[^0-9]/g, '');
                if (p) usedPins.add(p);
            });
            suppliers.forEach(s => {
                if (!s || s.deleted) return;
                if (excludeId && String(s.id) === String(excludeId)) return;
                const p = normalizeDigitsToAscii(s.statementPin || '').replace(/[^0-9]/g, '');
                if (p) usedPins.add(p);
            });

            for (let attempt = 0; attempt < 900; attempt++) {
                const cand = String(Math.floor(100 + Math.random() * 900));
                if (!usedPins.has(cand)) return cand;
            }
            let seq = 100;
            while (usedPins.has(String(seq))) {
                seq++;
            }
            return String(seq);
        }

        function getEntityFull5DigitPin(waOrPhone, rawPin) {
            const pin3 = normalizeDigitsToAscii(rawPin || '').replace(/[^0-9]/g, '');
            const waDigits = normalizeDigitsToAscii(waOrPhone || '').replace(/[^0-9]/g, '');
            const last2 = waDigits.length >= 2 ? waDigits.slice(-2) : '';
            const full5 = (pin3 && last2 && pin3.length === 3) ? (last2 + pin3) : pin3;
            return { pin3, last2, full5 };
        }
        window.getEntityFull5DigitPin = getEntityFull5DigitPin;

        function updateCombinedPinPreview(prefix) {
            const pinEl = document.getElementById(`${prefix}-statement-pin`);
            const prevEl = document.getElementById(`${prefix}-pin-combined-preview`);
            const rawPin = normalizeDigitsToAscii(pinEl ? pinEl.value : '').replace(/[^0-9]/g, '');
            if (pinEl && pinEl.value !== rawPin) pinEl.value = rawPin;
            if (prevEl) {
                prevEl.innerHTML = '';
                prevEl.style.display = 'none';
            }
        }

        function generateRandomFormPin(prefix) {
            const pinEl = document.getElementById(`${prefix}-statement-pin`);
            if (!pinEl) return;
            const currentId = document.getElementById(`${prefix}-id`)?.value || '';
            pinEl.value = getNextUniqueStatementPin(currentId);
            updateCombinedPinPreview(prefix);
        }

        function bulkGenerateEntityPins(entityType) {
            if (!isSystemAdminForStatementControl(true)) return;
            const isCust = entityType === 'customer';
            const storageKey = isCust ? 'erp_customers_v1' : 'erp_suppliers_v1';
            const otherStorageKey = isCust ? 'erp_suppliers_v1' : 'erp_customers_v1';
            const bulkSyncKey = isCust ? 'customers_bulk' : 'suppliers_bulk';
            const roleLabel = isCust ? 'العملاء' : 'الموردين';

            let list = [];
            let otherList = [];
            try {
                list = JSON.parse(localStorage.getItem(storageKey)) || [];
                if (!Array.isArray(list)) list = [];
            } catch (e) { list = []; }
            try {
                otherList = JSON.parse(localStorage.getItem(otherStorageKey)) || [];
                if (!Array.isArray(otherList)) otherList = [];
            } catch (e) { otherList = []; }

            const usedPins = new Set();
            otherList.forEach(item => {
                if (!item || item.deleted) return;
                const p = normalizeDigitsToAscii(item.statementPin || '').replace(/[^0-9]/g, '');
                if (p) usedPins.add(p);
            });
            list.forEach(item => {
                if (!item || item.deleted) return;
                const p = normalizeDigitsToAscii(item.statementPin || '').replace(/[^0-9]/g, '');
                if (p && !usedPins.has(p)) {
                    usedPins.add(p);
                    item.statementPin = p;
                } else {
                    item.statementPin = '';
                }
            });

            let generatedCount = 0;
            list.forEach(item => {
                if (!item || item.deleted) return;
                const cleanPin = normalizeDigitsToAscii(item.statementPin || '').replace(/[^0-9]/g, '');
                if (!cleanPin) {
                    const newPin = getNextUniqueStatementPin(item.id, usedPins);
                    item.statementPin = newPin;
                    usedPins.add(newPin);
                    generatedCount++;
                }
            });

            if (generatedCount === 0) {
                if (confirm(`جميع حسابات ${roleLabel} لديها رقم سري (باسورد) فريد مسجل مسبقاً.\nهل ترغب في إعادة توليد أرقام سرية عشوائية جديدة وفريدة لجميع ${roleLabel}؟`)) {
                    const rebuildPins = new Set();
                    otherList.forEach(item => {
                        if (!item || item.deleted) return;
                        const p = normalizeDigitsToAscii(item.statementPin || '').replace(/[^0-9]/g, '');
                        if (p) rebuildPins.add(p);
                    });
                    list.forEach(item => {
                        if (!item || item.deleted) return;
                        const newPin = getNextUniqueStatementPin(item.id, rebuildPins);
                        item.statementPin = newPin;
                        rebuildPins.add(newPin);
                        generatedCount++;
                    });
                } else {
                    return;
                }
            }

            setMasterLocalData(storageKey, list);
            if (typeof syncToBackend === 'function') {
                syncToBackend(bulkSyncKey, list);
            }
            if (isCust) {
                renderCustomers(document.getElementById('customer-search-input')?.value || '');
            } else {
                renderSuppliers(document.getElementById('supplier-search-input')?.value || '');
            }
            if (typeof showToast === 'function') {
                showToast(`🔐 تم توليد وحفظ باسورد سري فريد لـ (${generatedCount}) من ${roleLabel} بنجاح`, 'success');
            }
        }

        function buildWhatsAppCardText(isCust, name, codeNum, pin, waPhone = '') {
            const info = getEntityFull5DigitPin(waPhone, pin);
            const cleanPin = info.pin3;
            const fullPin = info.full5 || cleanPin;
            const roleTitle = isCust ? 'عميلنا العزيز' : 'موردنا العزيز';
            const pinExplain = (info.last2 && cleanPin.length === 3)
                ? `🔐 الرقم السري للاستعلام (5 أرقام): *${fullPin}* (يتكون من آخر رقمين من واتسابكم *${info.last2}* + الرمز السري *${cleanPin}*)\n`
                : (cleanPin ? `🔐 الباسورد السري الخاص بحسابكم: *${cleanPin}*\n` : '');
            return (
                `📋 *بطاقة الخدمة الذاتية عبر الواتساب*\n` +
                `━━━━━━━━━━━━━━\n` +
                `مرحباً بك ${roleTitle}: *${name || 'الكريم'}*\n` +
                pinExplain +
                `━━━━━━━━━━━━━━\n` +
                `🟢 *أولاً: من رقمكم المعتمد المسجل لدينا (مباشر بدون باسورد):*\n` +
                `• لإرسال إشعار (أو 10 إشعارات دفعة واحدة): أرسل *صور الإشعارات فقط* وستنزل في حسابكم فوراً\n` +
                `• لسحب كشف الحساب المفصل أرسل كلمة: *كشف*\n` +
                `• لمعرفة الرصيد الصافي فقط أرسل كلمة: *رصيد*\n` +
                (fullPin
                    ? `━━━━━━━━━━━━━━\n` +
                      `🟣 *ثانياً: من أي رقم آخر غير مسجل (أو لتنزيل الإشعارات في حسابكم):*\n` +
                      `• لإرسال إشعار واحد أو 10 إشعارات دفعة واحدة: اكتب الرقم السري *${fullPin}* *مرة واحدة فقط* (في تعليق أول صورة أو برسالة قبل/بعد الصور)\n` +
                      `• لسحب كشف الحساب من رقم آخر أرسل: *${fullPin} كشف* (أو *${fullPin}* فقط)\n` +
                      `• لمعرفة الرصيد الصافي من رقم آخر أرسل: *${fullPin} رصيد*`
                    : '')
            );
        }

        function copyEntityWhatsAppCard(entityType, id) {
            const isCust = entityType === 'customer';
            const storageKey = isCust ? 'erp_customers_v1' : 'erp_suppliers_v1';
            let list = [];
            try {
                list = JSON.parse(localStorage.getItem(storageKey)) || [];
            } catch (e) { list = []; }
            const item = list.find(x => x && String(x.id) === String(id));
            if (!item) return;
            const codeNum = item.receiptCodeNum || '';
            const pin = item.statementPin || '';
            const wa = item.whatsapp || item.phone || '';
            const info = getEntityFull5DigitPin(wa, pin);
            const cardText = buildWhatsAppCardText(isCust, item.name, codeNum, pin, wa);

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(cardText).then(() => {
                    if (typeof showToast === 'function') showToast(`📋 تم نسخ بطاقة الواتساب لـ "${item.name}" (🔐 ${info.full5 || pin}) بنجاح`, 'success');
                });
            }
        }

        function copyCurrentFormWhatsAppCard(prefix) {
            const isCust = prefix === 'cust';
            const name = (document.getElementById(`${prefix}-name`)?.value || '').trim();
            const codeNum = normalizeDigitsToAscii(document.getElementById(`${prefix}-receipt-code-num`)?.value || '').replace(/[^0-9]/g, '');
            const wa = (document.getElementById(`${prefix}-whatsapp`)?.value || '').trim();
            let pin = normalizeDigitsToAscii(document.getElementById(`${prefix}-statement-pin`)?.value || '').replace(/[^0-9]/g, '');
            if (!pin) {
                generateRandomFormPin(prefix);
                pin = normalizeDigitsToAscii(document.getElementById(`${prefix}-statement-pin`)?.value || '').replace(/[^0-9]/g, '');
            }
            const info = getEntityFull5DigitPin(wa, pin);
            const cardText = buildWhatsAppCardText(isCust, name || (isCust ? 'عميلنا الكريم' : 'موردنا الكريم'), codeNum, pin, wa);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(cardText).then(() => {
                    if (typeof showToast === 'function') showToast(`📋 تم نسخ بطاقة الواتساب للباسورد (🔐 ${info.full5 || pin}) بنجاح`, 'success');
                });
            }
        }

        function renderAdminWaPolicyUI() {
            populateWaReceiptBankDropdowns();
            const policy = getAdminWaPolicy();
            document.querySelectorAll('.wa-policy-master-mode').forEach(el => { el.value = policy.masterMode || 'per_account'; });
            document.querySelectorAll('.wa-policy-receipt-approval-mode').forEach(el => { el.value = policy.receiptApprovalMode || 'per_account'; });
            document.querySelectorAll('.wa-policy-default-bank-id').forEach(el => { el.value = policy.defaultReceiptBankId || ''; });
            document.querySelectorAll('.wa-policy-company-name').forEach(el => { el.value = policy.voucherCompanyName || ''; });
            document.querySelectorAll('.wa-policy-logo-preview').forEach(img => {
                if (policy.voucherLogoImage) {
                    img.src = policy.voucherLogoImage;
                    img.style.display = 'inline-block';
                } else {
                    img.src = '';
                    img.style.display = 'none';
                }
            });
            document.querySelectorAll('.wa-policy-logo-clear-btn').forEach(btn => {
                btn.style.display = policy.voucherLogoImage ? 'inline-block' : 'none';
            });
            document.querySelectorAll('.wa-policy-stamp-text').forEach(el => { el.value = policy.voucherStampText || 'ختم مصادقة مالية إلكترونية'; });
            document.querySelectorAll('.wa-policy-stamp-preview').forEach(img => {
                if (policy.voucherStampImage) {
                    img.src = policy.voucherStampImage;
                    img.style.display = 'inline-block';
                } else {
                    img.src = '';
                    img.style.display = 'none';
                }
            });
            document.querySelectorAll('.wa-policy-stamp-clear-btn').forEach(btn => {
                btn.style.display = policy.voucherStampImage ? 'inline-block' : 'none';
            });
            document.querySelectorAll('.wa-policy-phone-verification').forEach(el => { el.value = policy.phoneVerification || 'phone_or_pin'; });
            document.querySelectorAll('.wa-policy-statement-scope').forEach(el => { el.value = policy.statementScope || 'all'; });
            document.querySelectorAll('.wa-policy-include-items').forEach(el => { el.checked = policy.includeInvoiceItems !== false; });
            document.querySelectorAll('.wa-policy-notify-admin').forEach(el => { el.checked = Boolean(policy.notifyAdminOnRequest); });
            document.querySelectorAll('.wa-policy-antispam').forEach(el => { el.checked = policy.antiSpamCooldown !== false; });

            const badgeConfig = {
                'per_account': { text: '🎯 الوضع الحالي: حسب صلاحية كل حساب', bg: 'rgba(16, 185, 129, 0.15)', color: '#34d399', border: 'rgba(16, 185, 129, 0.35)' },
                'force_detailed_all': { text: '📊 قرار المدير العام: كشف مفصل للجميع', bg: 'rgba(56, 189, 248, 0.18)', color: '#38bdf8', border: 'rgba(56, 189, 248, 0.45)' },
                'force_balance_only_all': { text: '💰 قرار المدير العام: معرفة الرصيد فقط للجميع', bg: 'rgba(245, 158, 11, 0.18)', color: '#fbbf24', border: 'rgba(245, 158, 11, 0.45)' },
                'force_disabled_all': { text: '🔒 قرار المدير العام: الخدمة موقوفة كلياً عن الجميع', bg: 'rgba(239, 68, 68, 0.18)', color: '#f87171', border: 'rgba(239, 68, 68, 0.45)' }
            };
            const cfg = badgeConfig[policy.masterMode] || badgeConfig['per_account'];
            document.querySelectorAll('.wa-policy-status-badge').forEach(badge => {
                badge.textContent = cfg.text;
                badge.style.background = cfg.bg;
                badge.style.color = cfg.color;
                badge.style.borderColor = cfg.border;
            });
        }

        function toggleAdminWaPolicyDrawer() {
            if (!isSystemAdminForStatementControl(true)) return;
            document.querySelectorAll('.admin-wa-policy-drawer').forEach(drawer => {
                drawer.style.display = (drawer.style.display === 'none' || !drawer.style.display) ? 'block' : 'none';
            });
            renderAdminWaPolicyUI();
        }

        function syncAdminWaPolicyFromUI(sourceEl, extraPatch = null) {
            if (!isSystemAdminForStatementControl(true)) {
                renderAdminWaPolicyUI();
                return;
            }
            const curPolicy = getAdminWaPolicy();
            const rootPanel = sourceEl ? (sourceEl.closest('[id^="admin-wa-policy-panel-"]') || document) : document;
            const drawerPanel = rootPanel.querySelector('.admin-wa-policy-drawer') || document.querySelector('.admin-wa-policy-drawer') || rootPanel;
            const selectedBankId = drawerPanel.querySelector('.wa-policy-default-bank-id')?.value ?? curPolicy.defaultReceiptBankId ?? '';
            const banks = getActiveSystemBanksList();
            const foundBank = banks.find(b => String(b.id) === String(selectedBankId));
            const compInput = rootPanel.querySelector('.wa-policy-company-name') || document.querySelector('.wa-policy-company-name');
            const stampTextInput = rootPanel.querySelector('.wa-policy-stamp-text') || document.querySelector('.wa-policy-stamp-text');

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
                masterMode: drawerPanel.querySelector('.wa-policy-master-mode')?.value || curPolicy.masterMode || 'per_account',
                receiptApprovalMode: drawerPanel.querySelector('.wa-policy-receipt-approval-mode')?.value || curPolicy.receiptApprovalMode || 'per_account',
                defaultReceiptBankId: selectedBankId,
                defaultReceiptBankName: foundBank ? foundBank.name : '',
                voucherCompanyName: compInput ? compInput.value.trim() : (curPolicy.voucherCompanyName || ''),
                voucherLogoImage: domLogoImg,
                voucherStampText: stampTextInput ? (stampTextInput.value.trim() || 'ختم مصادقة مالية إلكترونية') : (curPolicy.voucherStampText || 'ختم مصادقة مالية إلكترونية'),
                voucherStampImage: domStampImg,
                phoneVerification: drawerPanel.querySelector('.wa-policy-phone-verification')?.value || curPolicy.phoneVerification || 'phone_or_pin',
                statementScope: drawerPanel.querySelector('.wa-policy-statement-scope')?.value || curPolicy.statementScope || 'all',
                includeInvoiceItems: drawerPanel.querySelector('.wa-policy-include-items') ? Boolean(drawerPanel.querySelector('.wa-policy-include-items').checked) : (curPolicy.includeInvoiceItems !== false),
                notifyAdminOnRequest: drawerPanel.querySelector('.wa-policy-notify-admin') ? Boolean(drawerPanel.querySelector('.wa-policy-notify-admin').checked) : Boolean(curPolicy.notifyAdminOnRequest),
                antiSpamCooldown: drawerPanel.querySelector('.wa-policy-antispam') ? Boolean(drawerPanel.querySelector('.wa-policy-antispam').checked) : (curPolicy.antiSpamCooldown !== false)
            }, extraPatch || {});

            const syncPayload = Object.assign({}, policy);
            delete policy._forceClearStamp;
            delete policy._forceClearLogo;
            localStorage.setItem('erp_whatsapp_statement_policy_v1', JSON.stringify(policy));
            renderAdminWaPolicyUI();
            if (typeof syncToBackend === 'function') {
                syncToBackend('whatsapp_statement_policy', syncPayload);
            }
            renderCustomers(document.getElementById('customer-search-input')?.value || '');
            renderSuppliers(document.getElementById('supplier-search-input')?.value || '');
            if (typeof showToast === 'function') {
                showToast('🏛️ تم حفظ اسم المنشأة والشعار والتوقيع وإعدادات السندات بنجاح', 'success');
            }
        }

        function handleWaVoucherLogoUpload(inputEl) {
            if (!isSystemAdminForStatementControl(true)) return;
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
            if (!isSystemAdminForStatementControl(true)) return;
            document.querySelectorAll('.wa-policy-logo-preview').forEach(pImg => {
                pImg.setAttribute('src', '');
                pImg.style.display = 'none';
            });
            syncAdminWaPolicyFromUI(null, { voucherLogoImage: '', _forceClearLogo: true });
        }

        function handleWaVoucherStampUpload(inputEl) {
            if (!isSystemAdminForStatementControl(true)) return;
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

                    // AI Document Ink & Stamp Cleaner: adaptive background removal, despeckle, dark stroke sharpening, and tight auto-crop
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
            if (!isSystemAdminForStatementControl(true)) return;
            document.querySelectorAll('.wa-policy-stamp-preview').forEach(pImg => {
                pImg.setAttribute('src', '');
                pImg.style.display = 'none';
            });
            syncAdminWaPolicyFromUI(null, { voucherStampImage: '', _forceClearStamp: true });
        }

        function setFormReceiptApprovalMode(prefix, mode, triggerSaveIfEditing = false) {
            if (triggerSaveIfEditing && !isSystemAdminForStatementControl(true)) return;
            const normalizedMode = (mode === 'review_first') ? 'review_first' : 'auto_post';
            const modeEl = document.getElementById(`${prefix}-receipt-approval-mode`);
            if (modeEl) modeEl.value = normalizedMode;

            const btnConfigs = {
                'auto_post': {
                    activeBg: 'rgba(16, 185, 129, 0.28)',
                    activeColor: '#6ee7b7',
                    activeBorder: 'rgba(16, 185, 129, 0.85)',
                    activeShadow: '0 0 10px rgba(16, 185, 129, 0.3)'
                },
                'review_first': {
                    activeBg: 'rgba(245, 158, 11, 0.28)',
                    activeColor: '#fcd34d',
                    activeBorder: 'rgba(245, 158, 11, 0.85)',
                    activeShadow: '0 0 10px rgba(245, 158, 11, 0.3)'
                }
            };

            ['auto_post', 'review_first'].forEach(m => {
                const btn = document.getElementById(`${prefix}-rcpt-btn-${m}`);
                if (!btn) return;
                btn.classList.toggle('is-active-seg', m === normalizedMode);
                if (m === normalizedMode) {
                    const c = btnConfigs[m];
                    btn.style.background = c.activeBg;
                    btn.style.color = c.activeColor;
                    btn.style.border = `2px solid ${c.activeBorder}`;
                    btn.style.boxShadow = c.activeShadow;
                } else {
                    btn.style.background = 'rgba(148, 163, 184, 0.08)';
                    btn.style.color = '#64748b';
                    btn.style.border = '1.6px solid rgba(148, 163, 184, 0.45)';
                    btn.style.boxShadow = 'none';
                }
            });

            if (triggerSaveIfEditing) {
                const idEl = document.getElementById(`${prefix}-id`);
                const currentId = idEl ? idEl.value.trim() : '';
                if (currentId) {
                    setEntityReceiptApprovalMode(prefix === 'cust' ? 'customer' : 'supplier', currentId, normalizedMode);
                }
            }
        }

        function setEntityReceiptApprovalMode(entityType, id, targetMode = null) {
            if (!isSystemAdminForStatementControl(true)) return;
            const isCust = entityType === 'customer';
            const storageKey = isCust ? 'erp_customers_v1' : 'erp_suppliers_v1';
            const syncAction = isCust ? 'save_customer' : 'save_supplier';
            let list = [];
            try {
                list = JSON.parse(localStorage.getItem(storageKey)) || [];
                if (!Array.isArray(list)) list = [];
            } catch (e) { list = []; }

            const idx = list.findIndex(item => item && String(item.id) === String(id));
            if (idx === -1) return;

            const currentMode = getEntityReceiptApprovalMode(list[idx]);
            const nextMode = (targetMode && ['auto_post', 'review_first'].includes(targetMode))
                ? targetMode
                : (currentMode === 'auto_post' ? 'review_first' : 'auto_post');

            list[idx].receiptApprovalMode = nextMode;
            setMasterLocalData(storageKey, list);

            if (typeof syncToBackend === 'function') {
                syncToBackend(syncAction, list[idx]);
            }

            const prefix = isCust ? 'cust' : 'sup';
            const openId = document.getElementById(`${prefix}-id`)?.value || '';
            if (String(openId) === String(id)) {
                setFormReceiptApprovalMode(prefix, nextMode, false);
            }

            if (isCust) {
                renderCustomers(document.getElementById('customer-search-input')?.value || '');
            } else {
                renderSuppliers(document.getElementById('supplier-search-input')?.value || '');
            }

            if (typeof showToast === 'function') {
                if (nextMode === 'review_first') {
                    showToast(`👁️ تم ضبط إشعارات "${list[idx].name}" على: (مراجعة وتمرير يدوي في شاشة الترحيل)`, 'info');
                } else {
                    showToast(`⚡ تم ضبط إشعارات "${list[idx].name}" على: (اعتماد وترحيل فوري طوالي بالحساب)`, 'success');
                }
            }
        }

        function setFormStatementMode(prefix, mode, triggerSaveIfEditing = false) {
            if (triggerSaveIfEditing && !isSystemAdminForStatementControl(true)) return;
            const normalizedMode = ['disabled', 'balance_only', 'detailed'].includes(mode) ? mode : (mode ? 'detailed' : 'disabled');
            const modeEl = document.getElementById(`${prefix}-statement-mode`);
            const allowEl = document.getElementById(`${prefix}-allow-statement`);
            if (modeEl) modeEl.value = normalizedMode;
            if (allowEl) allowEl.value = (normalizedMode !== 'disabled') ? '1' : '0';

            const btnConfigs = {
                'disabled': {
                    activeBg: 'linear-gradient(135deg, #be123c, #e11d48)',
                    activeColor: '#ffffff',
                    activeBorder: '#fda4af',
                    activeShadow: '0 4px 12px rgba(225, 29, 72, 0.45), inset 0 1px 2px rgba(255,255,255,0.65)'
                },
                'balance_only': {
                    activeBg: 'linear-gradient(135deg, #b45309, #d97706)',
                    activeColor: '#ffffff',
                    activeBorder: '#fde68a',
                    activeShadow: '0 4px 12px rgba(217, 119, 6, 0.45), inset 0 1px 2px rgba(255,255,255,0.65)'
                },
                'detailed': {
                    activeBg: 'linear-gradient(135deg, #047857, #059669)',
                    activeColor: '#ffffff',
                    activeBorder: '#6ee7b7',
                    activeShadow: '0 4px 12px rgba(5, 150, 105, 0.45), inset 0 1px 2px rgba(255,255,255,0.65)'
                }
            };

            ['disabled', 'balance_only', 'detailed'].forEach(m => {
                const btn = document.getElementById(`${prefix}-mode-btn-${m}`);
                if (!btn) return;
                btn.classList.toggle('is-active-seg', m === normalizedMode);
                if (m === normalizedMode) {
                    const c = btnConfigs[m];
                    btn.style.background = c.activeBg;
                    btn.style.color = c.activeColor;
                    btn.style.border = `2px solid ${c.activeBorder}`;
                    btn.style.boxShadow = c.activeShadow;
                } else {
                    btn.style.background = 'linear-gradient(145deg, rgba(30,41,59,0.85), rgba(15,23,42,0.92))';
                    btn.style.color = '#e2e8f0';
                    btn.style.border = '1.8px solid rgba(148, 163, 184, 0.55)';
                    btn.style.boxShadow = 'inset 0 1px 1px rgba(255,255,255,0.25)';
                }
            });

            if (triggerSaveIfEditing) {
                const idEl = document.getElementById(`${prefix}-id`);
                const currentId = idEl ? idEl.value.trim() : '';
                if (currentId) {
                    setEntityStatementMode(prefix === 'cust' ? 'customer' : 'supplier', currentId, normalizedMode);
                }
            }
        }

        function setFormStatementBtnUI(prefix, modeOrBool) {
            const mode = ['disabled', 'balance_only', 'detailed'].includes(modeOrBool)
                ? modeOrBool
                : (modeOrBool ? 'detailed' : 'disabled');
            setFormStatementMode(prefix, mode, false);
        }

        function setEntityStatementMode(entityType, id, targetMode = null) {
            if (!isSystemAdminForStatementControl(true)) return;
            const isCust = entityType === 'customer';
            const storageKey = isCust ? 'erp_customers_v1' : 'erp_suppliers_v1';
            const syncAction = isCust ? 'save_customer' : 'save_supplier';
            let list = [];
            try {
                list = JSON.parse(localStorage.getItem(storageKey)) || [];
                if (!Array.isArray(list)) list = [];
            } catch (e) { list = []; }

            const idx = list.findIndex(item => item && String(item.id) === String(id));
            if (idx === -1) return;

            const currentMode = getEntityStatementMode(list[idx]);
            const cycleOrder = ['disabled', 'balance_only', 'detailed'];
            const nextMode = (targetMode && cycleOrder.includes(targetMode))
                ? targetMode
                : cycleOrder[(cycleOrder.indexOf(currentMode) + 1) % cycleOrder.length];

            list[idx].statementAccessMode = nextMode;
            list[idx].allowStatementRequest = (nextMode !== 'disabled');
            setMasterLocalData(storageKey, list);

            if (typeof syncToBackend === 'function') {
                syncToBackend(syncAction, list[idx]);
            }

            const prefix = isCust ? 'cust' : 'sup';
            const openId = document.getElementById(`${prefix}-id`)?.value || '';
            if (String(openId) === String(id)) {
                setFormStatementMode(prefix, nextMode, false);
            }

            if (isCust) {
                renderCustomers(document.getElementById('customer-search-input')?.value || '');
            } else {
                renderSuppliers(document.getElementById('supplier-search-input')?.value || '');
            }

            if (typeof showToast === 'function') {
                const pinLabel = list[idx].statementPin ? `🔐 ${list[idx].statementPin}` : '';
                if (nextMode === 'detailed') {
                    showToast(`📊 تم تفعيل (كشف حساب مفصل) لـ "${list[idx].name}" ${pinLabel}`, 'success');
                } else if (nextMode === 'balance_only') {
                    showToast(`💰 تم تحديد صلاحية (معرفة الرصيد فقط) لـ "${list[idx].name}" ${pinLabel}`, 'info');
                } else {
                    showToast(`🔒 تم إيقاف خدمة استعلام الواتساب لـ "${list[idx].name}"`, 'info');
                }
            }
        }

        function toggleEntityStatementRequest(entityType, id, forcedState = null) {
            if (forcedState === true) return setEntityStatementMode(entityType, id, 'detailed');
            if (forcedState === false) return setEntityStatementMode(entityType, id, 'disabled');
            return setEntityStatementMode(entityType, id, null);
        }

        function bulkSetEntityStatementMode(entityType, mode) {
            if (!isSystemAdminForStatementControl(true)) return;
            if (!['disabled', 'balance_only', 'detailed'].includes(mode)) return;
            const isCust = entityType === 'customer';
            const storageKey = isCust ? 'erp_customers_v1' : 'erp_suppliers_v1';
            const bulkSyncKey = isCust ? 'customers_bulk' : 'suppliers_bulk';
            const roleLabel = isCust ? 'العملاء' : 'الموردين';
            const modeLabel = mode === 'detailed' ? '📊 كشف حساب مفصل' : (mode === 'balance_only' ? '💰 معرفة الرصيد فقط' : '🔒 إيقاف الخدمة');

            let list = [];
            try {
                list = JSON.parse(localStorage.getItem(storageKey)) || [];
                if (!Array.isArray(list)) list = [];
            } catch (e) { list = []; }

            let updatedCount = 0;
            list.forEach(item => {
                if (!item || item.deleted) return;
                item.statementAccessMode = mode;
                item.allowStatementRequest = (mode !== 'disabled');
                updatedCount++;
            });

            setMasterLocalData(storageKey, list);
            if (typeof syncToBackend === 'function') {
                syncToBackend(bulkSyncKey, list);
            }

            if (isCust) {
                renderCustomers(document.getElementById('customer-search-input')?.value || '');
            } else {
                renderSuppliers(document.getElementById('supplier-search-input')?.value || '');
            }

            if (typeof showToast === 'function') {
                showToast(`🛡️ تم تعميم صلاحية (${modeLabel}) على جميع ${roleLabel} (${updatedCount} حساب) بنجاح`, 'success');
            }
        }

        function buildTableStatementControlHtml(entityType, item) {
            const mode = getEntityStatementMode(item);
            const rcptMode = getEntityReceiptApprovalMode(item);
            const policy = getAdminWaPolicy();
            const masterMode = policy.masterMode || 'per_account';
            const policyRcptMode = policy.receiptApprovalMode || 'per_account';

            const btnStyle = (targetMode) => {
                const isActive = (mode === targetMode);
                if (targetMode === 'disabled') {
                    return isActive
                        ? 'background:rgba(239,68,68,0.22); color:#fca5a5; border:1px solid rgba(239,68,68,0.55); font-weight:900;'
                        : 'background:rgba(255,255,255,0.03); color:#64748b; border:1px solid rgba(148,163,184,0.18); opacity:0.75;';
                }
                if (targetMode === 'balance_only') {
                    return isActive
                        ? 'background:rgba(245,158,11,0.24); color:#fcd34d; border:1px solid rgba(245,158,11,0.6); font-weight:900; box-shadow:0 0 8px rgba(245,158,11,0.22);'
                        : 'background:rgba(255,255,255,0.03); color:#64748b; border:1px solid rgba(148,163,184,0.18); opacity:0.75;';
                }
                return isActive
                    ? 'background:rgba(16,185,129,0.24); color:#6ee7b7; border:1px solid rgba(16,185,129,0.6); font-weight:900; box-shadow:0 0 8px rgba(16,185,129,0.22);'
                    : 'background:rgba(255,255,255,0.03); color:#64748b; border:1px solid rgba(148,163,184,0.18); opacity:0.75;';
            };

            const effectiveRcptMode = policyRcptMode === 'review_first_all' ? 'review_first' : (policyRcptMode === 'auto_post_all' ? 'auto_post' : rcptMode);
            const roleWord = entityType === 'supplier' ? 'المورد' : 'العميل';
            const rcptPillHtml = effectiveRcptMode === 'review_first'
                ? `<button type="button" onclick="setEntityReceiptApprovalMode('${entityType}', '${item.id}')" data-md-tip-title="👁️ مراجعة الإشعارات" data-md-tip="الإشعارات البنكية التي يرسلها هذا ${roleWord} تُراجع أولاً قبل اعتمادها في الحساب (اضغط للتبديل إلى الاعتماد الفوري)." style="margin-top:4px; background:rgba(245,158,11,0.2); color:#fcd34d; border:1px solid rgba(245,158,11,0.5); padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">👁️ مراجعة</button>`
                : `<button type="button" onclick="setEntityReceiptApprovalMode('${entityType}', '${item.id}')" data-md-tip-title="⚡ الاعتماد الفوري للإشعارات" data-md-tip="الإشعارات البنكية التي يرسلها هذا ${roleWord} عبر الواتساب تُعتمد اعتماداً فورياً وتُقيد في حسابه تلقائياً (اضغط للتبديل إلى المراجعة)." style="margin-top:4px; background:rgba(16,185,129,0.15); color:#6ee7b7; border:1px solid rgba(16,185,129,0.4); padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">⚡ تلقائي</button>`;

            const customBankBadge = item.defaultBankName
                ? `<div data-md-tip-title="🏦 البنك الافتراضي (${item.defaultBankName})" data-md-tip="حينما يرسل هذا ${roleWord} إشعار تحويل عبر الواتساب يُعتمد ويُودع في هذا البنك تلقائياً." style="margin-top:2px; font-size:0.68rem; color:#fcd34d; font-weight:800;">🏦 ${item.defaultBankName}</div>`
                : '';

            return `
                <div style="display:inline-flex; align-items:center; gap:3px; background:rgba(15,23,42,0.65); padding:3px; border-radius:8px; border:1px solid rgba(148,163,184,0.2);">
                    <button type="button" onclick="setEntityStatementMode('${entityType}', '${item.id}', 'disabled')" data-md-tip-title="🔒 موقوف" data-md-tip="إيقاف هذا ${roleWord} من طلب الرصيد أو كشف الحساب عبر الواتساب." style="${btnStyle('disabled')} padding:3px 6px; border-radius:6px; font-size:0.72rem; cursor:pointer; white-space:nowrap;">🔒 موقوف</button>
                    <button type="button" onclick="setEntityStatementMode('${entityType}', '${item.id}', 'balance_only')" data-md-tip-title="💰 الرصيد فقط" data-md-tip="يسمح لهذا ${roleWord} بطلب معرفة الرصيد فقط عبر الواتساب." style="${btnStyle('balance_only')} padding:3px 6px; border-radius:6px; font-size:0.72rem; cursor:pointer; white-space:nowrap;">💰 الرصيد</button>
                    <button type="button" onclick="setEntityStatementMode('${entityType}', '${item.id}', 'detailed')" data-md-tip-title="📊 كشف حساب" data-md-tip="يسمح لهذا ${roleWord} بطلب كشف حساب مفصل عن طريق الواتساب." style="${btnStyle('detailed')} padding:3px 6px; border-radius:6px; font-size:0.72rem; cursor:pointer; white-space:nowrap;">📊 كشف</button>
                </div>
                <div>${rcptPillHtml}</div>
                ${customBankBadge}
            `;
        }

        function ensureCustomerReceiptCodes() {
            let customers = [];
            let suppliers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) { customers = []; }
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) { suppliers = []; }

            const usedNums = new Set();
            const usedPins = new Set();
            customers.forEach(c => {
                if (!c || c.deleted) return;
                const n = parseInt(normalizeDigitsToAscii(c.receiptCodeNum), 10);
                if (!isNaN(n) && n >= 1 && !usedNums.has(n)) {
                    usedNums.add(n);
                    c.receiptCodeNum = n;
                } else {
                    c.receiptCodeNum = null;
                }
                const p = normalizeDigitsToAscii(c.statementPin || '').replace(/[^0-9]/g, '');
                if (p && !usedPins.has(p)) {
                    usedPins.add(p);
                    c.statementPin = p;
                } else {
                    c.statementPin = '';
                }
            });
            suppliers.forEach(s => {
                if (!s || s.deleted) return;
                const n = parseInt(normalizeDigitsToAscii(s.receiptCodeNum), 10);
                if (!isNaN(n) && n >= 1 && !usedNums.has(n)) {
                    usedNums.add(n);
                }
                const p = normalizeDigitsToAscii(s.statementPin || '').replace(/[^0-9]/g, '');
                if (p && !usedPins.has(p)) {
                    usedPins.add(p);
                }
            });

            let changed = false;
            let nextCandidate = 1;
            customers.forEach(c => {
                if (!c || c.deleted) return;
                if (!c.receiptCodeNum || c.receiptCodeNum < 1) {
                    while (usedNums.has(nextCandidate)) {
                        nextCandidate++;
                    }
                    c.receiptCodeNum = nextCandidate;
                    usedNums.add(nextCandidate);
                    changed = true;
                }
                if (!c.statementPin) {
                    const newPin = getNextUniqueStatementPin(c.id, usedPins);
                    c.statementPin = newPin;
                    usedPins.add(newPin);
                    changed = true;
                }
            });

            if (changed) {
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
                if (typeof syncToBackend === 'function') {
                    syncToBackend('customers_bulk', customers);
                }
            }
            return customers;
        }

        function ensureSupplierReceiptCodes() {
            let customers = ensureCustomerReceiptCodes();
            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) { suppliers = []; }

            const usedNums = new Set();
            const usedPins = new Set();
            customers.forEach(c => {
                if (!c || c.deleted) return;
                const n = parseInt(normalizeDigitsToAscii(c.receiptCodeNum), 10);
                if (!isNaN(n) && n >= 1) usedNums.add(n);
                const p = normalizeDigitsToAscii(c.statementPin || '').replace(/[^0-9]/g, '');
                if (p) usedPins.add(p);
            });

            suppliers.forEach(s => {
                if (!s || s.deleted) return;
                const n = parseInt(normalizeDigitsToAscii(s.receiptCodeNum), 10);
                if (!isNaN(n) && n >= 1 && !usedNums.has(n)) {
                    usedNums.add(n);
                    s.receiptCodeNum = n;
                } else {
                    s.receiptCodeNum = null;
                }
                const p = normalizeDigitsToAscii(s.statementPin || '').replace(/[^0-9]/g, '');
                if (p && !usedPins.has(p)) {
                    usedPins.add(p);
                    s.statementPin = p;
                } else {
                    s.statementPin = '';
                }
            });

            let changed = false;
            let nextCandidate = 1;
            suppliers.forEach(s => {
                if (!s || s.deleted) return;
                if (!s.receiptCodeNum || s.receiptCodeNum < 1) {
                    while (usedNums.has(nextCandidate)) {
                        nextCandidate++;
                    }
                    s.receiptCodeNum = nextCandidate;
                    usedNums.add(nextCandidate);
                    changed = true;
                }
                if (!s.statementPin) {
                    const newPin = getNextUniqueStatementPin(s.id, usedPins);
                    s.statementPin = newPin;
                    usedPins.add(newPin);
                    changed = true;
                }
            });

            if (changed) {
                localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
                if (typeof syncToBackend === 'function') {
                    syncToBackend('suppliers_bulk', suppliers);
                }
            }
            return suppliers;
        }

        function getNextAvailableCustomerReceiptCode(excludeId = '') {
            let customers = [];
            let suppliers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) { customers = []; }
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) { suppliers = []; }

            const usedNums = new Set();
            customers.forEach(c => {
                if (!c || c.deleted) return;
                if (excludeId && String(c.id) === String(excludeId)) return;
                const n = parseInt(normalizeDigitsToAscii(c.receiptCodeNum), 10);
                if (!isNaN(n) && n >= 1) {
                    usedNums.add(n);
                }
            });
            suppliers.forEach(s => {
                if (!s || s.deleted) return;
                if (excludeId && String(s.id) === String(excludeId)) return;
                const n = parseInt(normalizeDigitsToAscii(s.receiptCodeNum), 10);
                if (!isNaN(n) && n >= 1) {
                    usedNums.add(n);
                }
            });

            let candidate = 1;
            while (usedNums.has(candidate)) {
                candidate++;
            }
            return candidate;
        }

        function addCustomerSubPhone(data = null) {
            const container = document.getElementById('cust-sub-phones-container');
            if (!container) return;
            const row = document.createElement('div');
            row.className = 'cust-sub-phone-row';
            row.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(56,189,248,0.2); border-radius: 10px; align-items: end;';
            const phoneVal = data ? (typeof data === 'string' ? data : (data.phone || '')) : '';
            const labelVal = data && typeof data === 'object' ? (data.label || '') : '';
            row.innerHTML = `
                <div class="input-group" style="margin-bottom:0;" data-md-tip-title="📱 خانة رقم هاتف / واتساب المندوب أو التابع" data-md-tip="اكتب هنا رقم هاتف أو واتساب المندوب أو الموظف أو الفرع التابع لهذا العميل (مثال: 0912345678). يتمتع هذا الرقم بنفس صلاحيات حساب العميل الأساسي تماماً؛ فعندما يرسل المندوب إشعار تحويل بنكي من هذا الرقم عبر الواتساب يقرؤه الذكاء الاصطناعي ويُقيده مباشرة في حساب العميل، وعندما يطلب كشف حساب أو استعلام رصيد يرد عليه البوت آلياً بكشف أو رصيد العميل حسب الصلاحية المحددة للحساب.">
                    <label style="color:#38bdf8;">📱 رقم الهاتف</label>
                    <input type="text" class="sub-phone-number" placeholder="0912345678" value="${phoneVal}" style="direction:ltr; text-align:center; font-weight:800;">
                </div>
                <div class="input-group" style="margin-bottom:0;" data-md-tip-title="👤 خانة اسم المندوب أو صفته أو الفرع" data-md-tip="اكتب هنا اسم المندوب أو صفته الوظيفية أو اسم الفرع التابع للعميل (مثال: المندوب علي، أو محاسب الفرع، أو سائق التوصيل). يفيدك هذا الاسم في توثيق هوية المرسل داخل سجل الإشعارات الواردة وسندات القبض الآلية وكشف الحساب، لتعرف بالضبط أي مندوب قام بإرسال التحويل أو طلب الكشف.">
                    <label style="color:#94a3b8;">👤 الاسم / الصفة</label>
                    <input type="text" class="sub-phone-label" placeholder="المندوب / الفرع" value="${labelVal}">
                </div>
                <button type="button" class="btn-text" onclick="this.closest('.cust-sub-phone-row').remove()" data-md-tip-title="🗑️ زر حذف رقم المندوب من الحساب" data-md-tip="يقوم بإزالة رقم هذا المندوب وإلغاء ارتباطه بحساب العميل؛ وبعد الضغط على (💾 حفظ F10) لن يتمكن هذا الرقم من طلب كشف الحساب أو الرصيد أو إرسال إشعارات باسم هذا العميل." style="color: #f43f5e; border-color: rgba(244,63,94,0.3); background: rgba(244,63,94,0.12);">🗑️ حذف</button>
            `;
            container.appendChild(row);
        }

        async function saveCustomer() {
            const id = document.getElementById('cust-id').value || 'CUST-' + Date.now();
            const name = document.getElementById('cust-name').value;
            if(!name.trim()) { return focusRequiredMasterField('cust-name', '⚠️ الرجاء إدخال اسم العميل'); }
            if(isDuplicateName('erp_customers_v1', name, id)) { return focusRequiredMasterField('cust-name', '⚠️ اسم العميل موجود مسبقاً، يرجى اختيار اسم آخر'); }
            const rawWhatsapp = (document.getElementById('cust-whatsapp')?.value || document.getElementById('cust-phone')?.value || '').trim();
            const whatsapp = rawWhatsapp;
            const phone = rawWhatsapp;
            const email = document.getElementById('cust-email').value;
            const contactPerson = document.getElementById('cust-contact').value;
            const address = document.getElementById('cust-address').value;
            const opBalance = parseFloat(document.getElementById('cust-opening-balance').value) || 0;
            const openingDebit = opBalance >= 0 ? opBalance : 0;
            const openingCredit = opBalance < 0 ? Math.abs(opBalance) : 0;
            // Read exchange rate from form input or fallback to settings
            const currency = document.getElementById('cust-currency').value || 'SDG';
            let exRate = parseFloat(document.getElementById('cust-ex-rate')?.value) || 1;
            if (currency === 'SDG') {
                exRate = 1;
            } else if (!document.getElementById('cust-ex-rate')?.value) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD) exRate = parseFloat(cfg.rateUSD) || 1;
                } catch(e) {}
            }
            const taxNumber = document.getElementById('cust-tax').value || '';
            const accountNo = document.getElementById('cust-account-no').value || '';
            const limit = parseFloat(document.getElementById('cust-limit').value) || 0;
            const priceTier = document.getElementById('cust-price-tier')?.value || 'cash';
            const paymentTermsDays = parseInt(document.getElementById('cust-payment-terms')?.value, 10) || 0;
            const security = parseInt(document.getElementById('cust-security').value) || 1;
            const status = document.getElementById('cust-status').value || 'active';
            const notes = document.getElementById('cust-notes').value;
            const rawCustMode = document.getElementById('cust-statement-mode')?.value || '';
            const statementAccessMode = ['disabled', 'balance_only', 'detailed'].includes(rawCustMode)
                ? rawCustMode
                : 'detailed';
            const allowStatementRequest = (statementAccessMode !== 'disabled');
            const receiptApprovalMode = (document.getElementById('cust-receipt-approval-mode')?.value === 'review_first') ? 'review_first' : 'auto_post';
            const defaultBankId = (document.getElementById('cust-default-bank-id')?.value || '').trim();
            const foundDefaultBank = getActiveSystemBanksList().find(b => String(b.id) === String(defaultBankId));
            const defaultBankName = foundDefaultBank ? foundDefaultBank.name : '';

            const bankAccounts = [];
            document.querySelectorAll('.cust-bank-row').forEach(row => {
                const bName = (row.querySelector('.bank-name')?.value || '').trim();
                const bBranch = (row.querySelector('.bank-branch')?.value || '').trim();
                const bAcc = (row.querySelector('.bank-acc')?.value || '').trim();
                if (bName || bBranch || bAcc) {
                    bankAccounts.push({
                        bankName: bName,
                        branch: bBranch,
                        accountNo: bAcc,
                        securityLevel: parseInt(row.querySelector('.bank-security')?.value) || 1,
                        isDeactivated: Boolean(row.querySelector('.bank-deactive')?.checked)
                    });
                }
            });

            const subPhones = [];
            document.querySelectorAll('.cust-sub-phone-row').forEach(row => {
                const spNum = (row.querySelector('.sub-phone-number')?.value || '').trim();
                const spLabel = (row.querySelector('.sub-phone-label')?.value || '').trim();
                if (spNum) {
                    subPhones.push({ phone: spNum, label: spLabel });
                }
            });

            if (!name) { return focusRequiredMasterField('cust-name', '⚠️ الرجاء إدخال اسم العميل'); }

            // Check for duplicate names in Customers and Suppliers
            const currentName = name.trim().toLowerCase();
            let allCusts = []; let allSups = [];
            try {
                allCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                allSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if(!Array.isArray(allCusts)) allCusts = [];
                if(!Array.isArray(allSups)) allSups = [];
            } catch(e) {}
            
            const isCustDup = allCusts.some(c => !c.deleted && c.name && c.name.trim().toLowerCase() === currentName && c.id !== id);
            const isSupDup = allSups.some(s => !s.deleted && s.name && s.name.trim().toLowerCase() === currentName && s.id !== id);
            
            if (isCustDup || isSupDup) {
                return focusRequiredMasterField('cust-name', '⚠️ هذا الاسم موجود مسبقاً (كعميل أو مورد)');
            }

            // Maintain background receiptCodeNum automatically
            const rawCodeNumInput = normalizeDigitsToAscii(document.getElementById('cust-receipt-code-num')?.value || '').trim();
            let receiptCodeNum = parseInt(rawCodeNumInput, 10);
            const dupCodeNumCust = allCusts.find(c => !c.deleted && c.id !== id && parseInt(normalizeDigitsToAscii(c.receiptCodeNum), 10) === receiptCodeNum);
            const dupCodeNumSup = allSups.find(s => !s.deleted && s.id !== id && parseInt(normalizeDigitsToAscii(s.receiptCodeNum), 10) === receiptCodeNum);
            if (isNaN(receiptCodeNum) || receiptCodeNum < 1 || dupCodeNumCust || dupCodeNumSup) {
                receiptCodeNum = getNextAvailableCustomerReceiptCode(id);
            }

            const receiptCodeWord = '';
            let statementPin = normalizeDigitsToAscii(document.getElementById('cust-statement-pin')?.value || '').replace(/[^0-9]/g, '');
            if (!statementPin) {
                statementPin = getNextUniqueStatementPin(id);
            } else {
                const dupPinCust = allCusts.find(c => !c.deleted && c.id !== id && normalizeDigitsToAscii(c.statementPin || '').replace(/[^0-9]/g, '') === statementPin);
                const dupPinSup = allSups.find(s => !s.deleted && s.id !== id && normalizeDigitsToAscii(s.statementPin || '').replace(/[^0-9]/g, '') === statementPin);
                if (dupPinCust || dupPinSup) {
                    const dupOwner = dupPinCust ? `العميل "${dupPinCust.name}"` : `المورد "${dupPinSup.name}"`;
                    return focusRequiredMasterField('cust-statement-pin', `⚠️ الرقم السري (${statementPin}) مسجل لـ ${dupOwner}`);
                }
            }

            let customers = allCusts;
            const existingIdx = customers.findIndex(c => c.id === id);
            
            const todayStr = new Date().toISOString().split('T')[0];
            const nowIso = new Date().toISOString();
            
            const custData = {
                id, name, phone, whatsapp, email, contactPerson, address, 
                receiptCodeNum,
                receiptCodeWord,
                statementPin,
                statementAccessMode,
                allowStatementRequest,
                receiptApprovalMode,
                defaultBankId,
                defaultBankName,
                subPhones,
                openingDebit, openingCredit,
                openingBalance: (openingDebit - openingCredit),
                opening_balance: (openingDebit - openingCredit),
                currency, exRate, exchangeRate: exRate, taxNumber, accountNo,
                currentBalance: (openingDebit - openingCredit),
                balance: (openingDebit - openingCredit),
                creditLimit: limit,
                priceTier, paymentTermsDays,
                securityLevel: security, status: status, notes,
                bankAccounts: bankAccounts,
                date: existingIdx > -1 ? (customers[existingIdx].date || todayStr) : todayStr,
                createdAt: existingIdx > -1 ? (customers[existingIdx].createdAt || nowIso) : nowIso
            };

            const prevCustomersSnapshot = JSON.parse(JSON.stringify(customers));
            if (existingIdx > -1) {
                const oldOp = (parseFloat(customers[existingIdx].openingDebit) || 0) - (parseFloat(customers[existingIdx].openingCredit) || 0);
                const newOp = (openingDebit - openingCredit);
                custData.currentBalance = (parseFloat(customers[existingIdx].currentBalance) || 0) + (newOp - oldOp);
                custData.balance = custData.currentBalance;
                if (customers[existingIdx].whatsappLid && !custData.whatsappLid) {
                    custData.whatsappLid = customers[existingIdx].whatsappLid;
                }
                customers[existingIdx] = custData;
            } else {
                customers.push(custData);
            }

            setMasterLocalData('erp_customers_v1', customers, true);
            logMasterDataAudit(existingIdx > -1 ? 'تعديل' : 'إضافة', 'العملاء', name, `رصيد افتتاحي: ${opBalance} | شريحة: ${priceTier} | أجل سداد: ${paymentTermsDays} يوم`);
            clearCustomerForm();
            renderCustomers();
            triggerMasterSavePulse('#customer-form');
            if (typeof showToast === 'function') showToast(`✅ تم حفظ العميل "${name}" (🔐 ${statementPin})`, 'success', 2000);

            const backendRes = await syncToBackend('save_customer', custData);
            if (backendRes && backendRes.success === false && !backendRes.networkOffline) {
                setMasterLocalData('erp_customers_v1', prevCustomersSnapshot, true);
                renderCustomers();
                return;
            }
            if (backendRes && backendRes.success && backendRes.data) {
                const srvCust = backendRes.data;
                const sIdx = customers.findIndex(c => c.id === id);
                if (sIdx > -1) {
                    customers[sIdx] = Object.assign({}, custData, srvCust);
                    setMasterLocalData('erp_customers_v1', customers, true);
                }
            }
        }

        function renderCustomers(query = '') {
            populateWaReceiptBankDropdowns();
            const tbody = document.getElementById('customers-tbody');
            if(!tbody) return;
            let customers = ensureCustomerReceiptCodes();
            customers = customers.filter(c => !c.deleted);

            if (query) {
                const norm = typeof window.normalizeArabicFuzzyText === 'function' ? window.normalizeArabicFuzzyText : (s => normalizeDigitsToAscii(s || '').trim().toLowerCase());
                const q = norm(query);
                const qCleanCode = normalizeDigitsToAscii(query).trim().replace(/^#/, '');
                customers = customers.filter(c => {
                    const pInfo = getEntityFull5DigitPin(c.whatsapp || c.phone || '', c.statementPin);
                    const pinStr = pInfo.pin3;
                    const full5Str = pInfo.full5;
                    const subPhonesMatch = Array.isArray(c.subPhones) && c.subPhones.some(sp => {
                        const p = typeof sp === 'string' ? sp : (sp.phone || '');
                        const l = typeof sp === 'object' ? (sp.label || '') : '';
                        return norm(p).includes(q) || norm(l).includes(q);
                    });
                    return norm(c.name).includes(q) || 
                           norm(c.phone).includes(q) || 
                           norm(c.whatsapp).includes(q) ||
                           norm(c.email).includes(q) ||
                           norm(c.contactPerson).includes(q) ||
                           (pinStr && pinStr.includes(qCleanCode)) ||
                           (full5Str && full5Str.includes(qCleanCode)) ||
                           subPhonesMatch;
                });
            }

            customers.sort((a, b) => (parseInt(a.receiptCodeNum, 10) || 999999) - (parseInt(b.receiptCodeNum, 10) || 999999));

            const maxShow = window.__mdCustRenderLimit || 100;
            const visibleCustomers = customers.slice(0, maxShow);
            const rowsHtml = [];
            visibleCustomers.forEach(c => {
                const statusMap = {'vip': '<span style="background:rgba(168,85,247,0.15); color:#a855f7; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🌟 VIP</span>', 'blacklisted': '<span style="background:rgba(239,68,68,0.15); color:#ef4444; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🔴 محظور</span>'};
                const statusBadge = statusMap[c.status] || '<span style="background:rgba(52,211,153,0.15); color:#34d399; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🟢 نشط</span>';
                const bal = (typeof window.computeLiveEntityLedgerBalance === 'function')
                    ? window.computeLiveEntityLedgerBalance('customer', c)
                    : (parseFloat(c.currentBalance) || ((parseFloat(c.openingDebit) || 0) - (parseFloat(c.openingCredit) || 0)));
                const lim = parseFloat(c.creditLimit) || 0;
                const isOverLimit = lim > 0 && bal > lim;
                const balColor = bal > 0 ? '#f87171' : (bal < 0 ? '#34d399' : '#94a3b8');
                const balLabel = bal > 0 ? 'عليه' : (bal < 0 ? 'له' : 'مسدد');
                const overLimitBadge = isOverLimit ? ' <span style="background:rgba(239,68,68,0.22); color:#fca5a5; border:1px solid rgba(239,68,68,0.5); padding:1px 5px; border-radius:5px; font-size:0.68rem; font-weight:900;" title="تجاوز سقف الائتمان المسموح">⚠️ سقف</span>' : '';
                const agingBadge = (typeof window.computeEntityDebtAgingBadge === 'function') ? window.computeEntityDebtAgingBadge('customer', c, bal) : '';
                const balDisplay = `<span onclick="openEntityDrillDownStatementModal('customer', '${c.id}')" style="color:${balColor}; font-weight:900; font-family:monospace; cursor:pointer; text-decoration:underline dotted;" title="اضغط لفتح كشف الحساب التفصيلي والحركات (Drill-Down)">${Math.abs(bal).toLocaleString()}</span> <small style="color:${balColor}; opacity:0.8;">${balLabel}</small>${overLimitBadge}${agingBadge}`;
                
                const pInfo = getEntityFull5DigitPin(c.whatsapp || c.phone || '', c.statementPin);
                const cleanPin = pInfo.pin3;
                const pinLabelHtml = (pInfo.last2 && cleanPin.length === 3)
                    ? `<span style="color:#4ade80;">${pInfo.last2}</span><span>${cleanPin}</span>`
                    : (cleanPin || '-');
                const pinTip = (pInfo.last2 && cleanPin.length === 3)
                    ? `الرقم السري (5 أرقام): ${pInfo.full5} = آخر رقمين من الواتساب (${pInfo.last2}) + الثلاثة أرقام السرية (${cleanPin}) — اضغط للنسخ`
                    : `الرقم السري: ${cleanPin || '-'} (أضف رقم الواتساب ليصبح 5 أرقام)`;
                const pinBadge = `<span onclick="copyEntityWhatsAppCard('customer', '${c.id}')" title="${pinTip}" style="background:rgba(168,85,247,0.18); color:#e9d5ff; border:1px solid rgba(168,85,247,0.45); padding:3px 10px; border-radius:8px; font-weight:900; font-family:monospace; font-size:0.88rem; display:inline-block; cursor:pointer; direction:ltr;">🔐 ${pinLabelHtml} 📋</span>`;
                
                const subCount = Array.isArray(c.subPhones) ? c.subPhones.length : 0;
                const subPhonesBadge = subCount > 0 ? `<div style="margin-top:3px;"><span style="background:rgba(56,189,248,0.12); color:#38bdf8; padding:1px 6px; border-radius:6px; font-size:0.72rem; font-weight:700;">📱 +${subCount}</span></div>` : '';

                const stmtToggleBtn = buildTableStatementControlHtml('customer', c);

                rowsHtml.push(`<tr>
                    <td style="text-align:center;">${pinBadge}</td>
                    <td style="font-weight:bold;">${c.name}</td>
                    <td style="text-align:center; direction:ltr;"><div style="font-family:monospace; font-weight:800;">${c.whatsapp || c.phone || '-'}</div>${subPhonesBadge}</td>
                    <td style="text-align:center;">${stmtToggleBtn}</td>
                    <td style="text-align:center;">${statusBadge}</td>
                    <td style="text-align:center;">${balDisplay}</td>
                    <td style="text-align:center; cursor:pointer;" ondblclick="startInlineMasterEdit(this, 'customer_limit', '${c.id}', ${parseFloat(c.creditLimit) || 0})" title="انقر مرتين لتعديل حد الائتمان مباشرة">${(c.creditLimit || 0).toLocaleString()} ${c.currency || 'SDG'} <small style="opacity:0.45;">✏️</small></td>
                    <td style="font-size:0.85rem; text-align:center;">${c.contactPerson || '-'}</td>
                    <td style="text-align:center;">${c.securityLevel || 1}</td>
                    <td style="text-align:center; white-space:nowrap;"><button class="btn-text" style="color:#fbbf24; font-weight:800; margin-left:5px;" onclick="openEntityDrillDownStatementModal('customer', '${c.id}')" title="كشف الحساب التفصيلي والحركات (Drill-Down)">📊</button><button class="btn-text" style="color:#38bdf8; font-weight:800; margin-left:5px;" onclick="window.location.href='account-statement.php?partner='+encodeURIComponent('${(c.name || '').replace(/'/g, "\\'")}')" title="كشف الحساب الرسمي الشامل">📄</button><button class="btn-text" style="color:#c084fc; font-weight:800; margin-left:5px;" onclick="window.location.href='customer-portfolio.php'" title="محفظة العميل 360°">💼</button><button class="btn-text" style="color:#34d399; font-weight:800; margin-left:6px;" onclick="openEntityQrCardModal('customer', '${c.id}')" title="بطاقة QR">🪪</button><button class="btn-text" style="color:#38bdf8; font-weight:800;" onclick="editCustomer('${c.id}')">✏️ تعديل</button><button class="btn-text" style="color:var(--danger-color); font-weight:800; margin-right:8px;" onclick="deleteCustomer('${c.id}')">🗑️ حذف</button></td>
                </tr>`);
            });
            if (!query && customers.length > maxShow) {
                rowsHtml.push(`<tr><td colspan="10" style="text-align:center; padding:12px;"><button type="button" class="btn-action" style="background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:6px 18px; border-radius:8px; font-weight:800; cursor:pointer;" onclick="window.__mdCustRenderLimit=(window.__mdCustRenderLimit||80)+100; renderCustomers();">⬇️ عرض المزيد (${maxShow} / ${customers.length})</button></td></tr>`);
            }
            tbody.innerHTML = rowsHtml.join('');
            if (typeof window.refreshMasterDataHealthAndEquilibrium === 'function') window.refreshMasterDataHealthAndEquilibrium();
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
                <div class="input-group" data-md-tip-title="🏦 خانة اسم البنك الخاص بالعميل" data-md-tip="حدد أو اكتب اسم البنك أو التطبيق المصرفي الذي يتعامل به العميل (مثال: بنك الخرطوم - بنكك، أو فوري، أو أمدرمان الوطني). يُستخدم هذا البيان لمطابقة الحوالات والإشعارات الواردة من حساب العميل آلياً."><label>اسم البنك</label><input type="text" autocomplete="off" list="master-banks-datalist" class="bank-name" placeholder="مثال: بنك الخرطوم" value="${data ? (data.bankName || '') : ''}"></div>
                <div class="input-group" data-md-tip-title="📍 خانة فرع البنك للعميل" data-md-tip="اكتب اسم الفرع البنكي الذي يتبع له حساب العميل (مثال: فرع الرياض أو الفرع الرئيسي) لتوثيق بيانات الحساب البنكي بدقة عند التحويل أو المراجعة."><label>الفرع</label><input type="text" class="bank-branch" placeholder="مثال: فرع الرياض" value="${data ? (data.branch || '') : ''}"></div>
                <div class="input-group" data-md-tip-title="🔢 خانة رقم الحساب البنكي للعميل" data-md-tip="اكتب رقم حساب العميل البنكي أو رقم حساب تطبيق (بنكك / فوري). عندما يرسل العميل أو مندوبه إشعار تحويل يظهر فيه هذا الرقم، يتعرف عليه الذكاء الاصطناعي فوراً ويربط الإشعار بحساب العميل تلقائياً."><label>رقم الحساب</label><input type="text" class="bank-acc" placeholder="رقم الحساب البنكي" value="${data ? (data.accountNo || '') : ''}"></div>
                <div class="input-group" data-md-tip-title="🛡️ درجة سرية الحساب البنكي للعميل" data-md-tip="تحدد مستوى الصلاحية المطلوب للاطلاع على تفاصيل رقم حساب العميل البنكي من 1 (عادي) إلى 10 (سري للغاية للإدارة العليا فقط)."><label>السرية</label>
                    <select class="bank-security">
                        <option value="1" ${data && data.securityLevel == 1 ? 'selected' : ''}>1</option>
                        <option value="2" ${data && data.securityLevel == 2 ? 'selected' : ''}>2</option>
                        <option value="3" ${data && data.securityLevel == 3 ? 'selected' : ''}>3</option>
                        <option value="4" ${data && data.securityLevel == 4 ? 'selected' : ''}>4</option>
                        <option value="5" ${data && data.securityLevel == 5 ? 'selected' : ''}>5</option>
                        <option value="6" ${data && data.securityLevel == 6 ? 'selected' : ''}>6</option>
                        <option value="7" ${data && data.securityLevel == 7 ? 'selected' : ''}>7</option>
                        <option value="8" ${data && data.securityLevel == 8 ? 'selected' : ''}>8</option>
                        <option value="9" ${data && data.securityLevel == 9 ? 'selected' : ''}>9</option>
                        <option value="10" ${data && data.securityLevel == 10 ? 'selected' : ''}>10</option>
                    </select>
                </div>
                <div class="input-group" style="display:flex; align-items:flex-end; gap: 10px;">
                    <label data-md-tip-title="⛔ خيار إيقاف الحساب البنكي للعميل" data-md-tip="عند تفعيل هذا الخيار يتم تجميد التعامل مع رقم الحساب البنكي هذا مؤقتاً دون حذفه من سجل العميل التاريخي." style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:bold; color:var(--danger-color); margin-bottom:10px;">
                        <input type="checkbox" class="bank-deactive" style="width:18px; height:18px; accent-color:var(--danger-color);" ${data && data.isDeactivated ? 'checked' : ''}> إيقاف
                    </label>
                    <button type="button" data-md-tip-title="🗑️ زر حذف الحساب البنكي للعميل" data-md-tip="يحذف سطر الحساب البنكي هذا من بطاقة العميل الحالية؛ اضغط بعدها على (💾 حفظ F10) لاعتماد التعديل." onclick="this.closest('.cust-bank-row').remove()" style="background: rgba(244,63,94,0.15); color: #f43f5e; border: 1px solid rgba(244,63,94,0.3); border-radius: 6px; cursor: pointer; padding: 6px 12px; margin-bottom: 5px; margin-right: auto;">🗑️ حذف</button>
                </div>
            `;
            container.appendChild(row);
        }

        function clearCustomerForm() {
            ensureCustomerReceiptCodes();
            populateWaReceiptBankDropdowns();
            document.getElementById('cust-id').value = '';
            document.getElementById('cust-name').value = '';
            const codeNumEl = document.getElementById('cust-receipt-code-num');
            if (codeNumEl) codeNumEl.value = getNextAvailableCustomerReceiptCode('');
            const codeWordEl = document.getElementById('cust-receipt-code-word');
            if (codeWordEl) codeWordEl.value = '';
            const pinEl = document.getElementById('cust-statement-pin');
            if (pinEl) pinEl.value = getNextUniqueStatementPin('');
            updateCombinedPinPreview('cust');
            setFormStatementMode('cust', 'detailed', false);
            setFormReceiptApprovalMode('cust', 'auto_post', false);
            const defBankEl = document.getElementById('cust-default-bank-id');
            if (defBankEl) defBankEl.value = '';
            document.getElementById('cust-phone').value = '';
            document.getElementById('cust-whatsapp').value = '';
            document.getElementById('cust-email').value = '';
            document.getElementById('cust-contact').value = '';
            document.getElementById('cust-address').value = '';
            document.getElementById('cust-opening-balance').value = '';
            document.getElementById('cust-currency').value = 'SDG';

            document.getElementById('cust-tax').value = '';
            document.getElementById('cust-account-no').value = '';
            document.getElementById('cust-limit').value = '';
            if (document.getElementById('cust-price-tier')) document.getElementById('cust-price-tier').value = 'cash';
            if (document.getElementById('cust-payment-terms')) document.getElementById('cust-payment-terms').value = '';
            document.getElementById('cust-security').value = '1';
            document.getElementById('cust-status').value = 'active';
            document.getElementById('cust-notes').value = '';
            
            const subPhonesContainer = document.getElementById('cust-sub-phones-container');
            if (subPhonesContainer) {
                subPhonesContainer.innerHTML = '';
            }

            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) {
                bankContainer.innerHTML = '';
            }
            if (typeof updateCustExchangeRate === 'function') updateCustExchangeRate();
            window.__mdFormDirty = false;
        }
        window.closeCustomerForm = function() {
            clearCustomerForm();
        };

        function editCustomer(id) {
            populateWaReceiptBankDropdowns();
            let customers = ensureCustomerReceiptCodes();
            customers = customers.filter(c => !c.deleted);
            const cust = customers.find(c => c.id === id);
            if(!cust) return;
            document.getElementById('cust-id').value = cust.id;
            document.getElementById('cust-name').value = cust.name;
            const codeNumEl = document.getElementById('cust-receipt-code-num');
            if (codeNumEl) codeNumEl.value = cust.receiptCodeNum || getNextAvailableCustomerReceiptCode(cust.id);
            const codeWordEl = document.getElementById('cust-receipt-code-word');
            if (codeWordEl) codeWordEl.value = '';
            const pinEl = document.getElementById('cust-statement-pin');
            if (pinEl) pinEl.value = cust.statementPin || getNextUniqueStatementPin(cust.id);
            updateCombinedPinPreview('cust');
            setFormStatementMode('cust', getEntityStatementMode(cust), false);
            setFormReceiptApprovalMode('cust', getEntityReceiptApprovalMode(cust), false);
            const defBankEl = document.getElementById('cust-default-bank-id');
            if (defBankEl) defBankEl.value = cust.defaultBankId || '';
            const unifiedWa = cust.whatsapp || cust.phone || '';
            document.getElementById('cust-phone').value = unifiedWa;
            document.getElementById('cust-whatsapp').value = unifiedWa;
            document.getElementById('cust-email').value = cust.email || '';
            document.getElementById('cust-contact').value = cust.contactPerson || '';
            document.getElementById('cust-address').value = cust.address || '';
            const bal = (cust.openingDebit || 0) - (cust.openingCredit || 0);
            document.getElementById('cust-opening-balance').value = bal || '';
            document.getElementById('cust-currency').value = cust.currency || 'SDG';
            if (typeof updateCustExchangeRate === 'function') updateCustExchangeRate();
            if (cust.exRate && document.getElementById('cust-ex-rate')) {
                document.getElementById('cust-ex-rate').value = cust.exRate;
            }

            document.getElementById('cust-tax').value = cust.taxNumber || '';
            document.getElementById('cust-account-no').value = cust.accountNo || '';
            document.getElementById('cust-limit').value = cust.creditLimit || '';
            if (document.getElementById('cust-price-tier')) document.getElementById('cust-price-tier').value = cust.priceTier || 'cash';
            if (document.getElementById('cust-payment-terms')) document.getElementById('cust-payment-terms').value = cust.paymentTermsDays || '';
            document.getElementById('cust-security').value = cust.securityLevel || '1';
            document.getElementById('cust-status').value = cust.status || 'active';
            document.getElementById('cust-notes').value = cust.notes || '';
            
            const subPhonesContainer = document.getElementById('cust-sub-phones-container');
            if (subPhonesContainer) {
                subPhonesContainer.innerHTML = '';
                if (Array.isArray(cust.subPhones) && cust.subPhones.length > 0) {
                    cust.subPhones.forEach(sp => addCustomerSubPhone(sp));
                }
            }

            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) bankContainer.innerHTML = '';
            
            if (cust.bankAccounts && cust.bankAccounts.length > 0) {
                const validBanks = cust.bankAccounts.filter(acc => acc && (acc.bankName || acc.branch || acc.accountNo));
                validBanks.forEach(acc => addCustomerBankAccount(acc));
            } else if (cust.bankDetails && (cust.bankDetails.bankName || cust.bankDetails.accountNo)) {
                addCustomerBankAccount(cust.bankDetails);
            }
            
            document.getElementById('customer-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteCustomer(id) {
            genericDeleteEntity('customer', id);
        }

        // ================= SUPPLIERS LOGIC =================
        function addSupplierSubPhone(data = null) {
            const container = document.getElementById('sup-sub-phones-container');
            if (!container) return;
            const row = document.createElement('div');
            row.className = 'sup-sub-phone-row';
            row.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(56,189,248,0.2); border-radius: 10px; align-items: end;';
            const phoneVal = data ? (typeof data === 'string' ? data : (data.phone || '')) : '';
            const labelVal = data && typeof data === 'object' ? (data.label || '') : '';
            row.innerHTML = `
                <div class="input-group" style="margin-bottom:0;" data-md-tip-title="📱 خانة رقم هاتف / واتساب مندوب المورد" data-md-tip="اكتب هنا رقم هاتف أو واتساب المندوب أو المحاسب أو الفرع التابع لهذا المورد (مثال: 0912345678). يتمتع هذا الرقم بنفس صلاحيات حساب المورد الأساسي تماماً؛ فعندما يرسل المندوب إشعاراً أو يطلب كشف الحساب أو الرصيد من هذا الرقم عبر الواتساب، يربطه النظام فوراً بحساب المورد ويرسل له الكشف أو الرصيد تلقائياً.">
                    <label style="color:#38bdf8;">📱 رقم الهاتف</label>
                    <input type="text" class="sub-phone-number" placeholder="0912345678" value="${phoneVal}" style="direction:ltr; text-align:center; font-weight:800;">
                </div>
                <div class="input-group" style="margin-bottom:0;" data-md-tip-title="👤 خانة اسم مندوب المورد أو صفته" data-md-tip="اكتب هنا اسم المندوب أو صفته الوظيفية أو اسم الفرع التابع للمورد (مثال: مندوب المبيعات أو محاسب الشركة) ليظهر اسمه مرتبطاً بحساب المورد في سجلات الواتساب والمطابقات المالية.">
                    <label style="color:#94a3b8;">👤 الاسم / الصفة</label>
                    <input type="text" class="sub-phone-label" placeholder="المندوب / الفرع" value="${labelVal}">
                </div>
                <button type="button" class="btn-text" data-md-tip-title="🗑️ زر حذف رقم مندوب المورد" data-md-tip="يقوم بإزالة رقم هذا المندوب وإلغاء ارتباطه بحساب المورد؛ اضغط بعدها على (💾 حفظ F10) لاعتماد التعديل." onclick="this.closest('.sup-sub-phone-row').remove()" style="color: #f43f5e; border-color: rgba(244,63,94,0.3); background: rgba(244,63,94,0.12);">🗑️ حذف</button>
            `;
            container.appendChild(row);
        }

        async function saveSupplier() {
            const id = document.getElementById('sup-id').value || 'SUP-' + Date.now();
            const name = document.getElementById('sup-name').value;
            if(!name.trim()) { return focusRequiredMasterField('sup-name', '⚠️ الرجاء إدخال اسم المورد'); }
            if(isDuplicateName('erp_suppliers_v1', name, id)) { return focusRequiredMasterField('sup-name', '⚠️ اسم المورد موجود مسبقاً'); }
            const rawWhatsapp = (document.getElementById('sup-whatsapp')?.value || document.getElementById('sup-phone')?.value || '').trim();
            const whatsapp = rawWhatsapp;
            const phone = rawWhatsapp;
            const email = document.getElementById('sup-email').value;
            const address = document.getElementById('sup-address').value;
            const opBalance = parseFloat(document.getElementById('sup-opening-balance').value) || 0;
            const openingCredit = opBalance >= 0 ? opBalance : 0;
            const openingDebit = opBalance < 0 ? Math.abs(opBalance) : 0;
            // Read exchange rate from form input or fallback to settings
            const currency = document.getElementById('sup-currency').value || 'SDG';
            let exRate = parseFloat(document.getElementById('sup-ex-rate')?.value) || 1;
            if (currency === 'SDG') {
                exRate = 1;
            } else if (!document.getElementById('sup-ex-rate')?.value) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD) exRate = parseFloat(cfg.rateUSD) || 1;
                } catch(e) {}
            }
            const taxNumber = document.getElementById('sup-tax').value || '';
            const accountNo = document.getElementById('sup-account-no').value || '';
            const limit = parseFloat(document.getElementById('sup-limit').value) || 0;
            const paymentTermsDays = parseInt(document.getElementById('sup-payment-terms')?.value, 10) || 0;
            const security = parseInt(document.getElementById('sup-security').value) || 1;
            const status = document.getElementById('sup-status').value || 'active';
            const notes = document.getElementById('sup-notes').value;
            const rawSupMode = document.getElementById('sup-statement-mode')?.value || '';
            const statementAccessMode = ['disabled', 'balance_only', 'detailed'].includes(rawSupMode)
                ? rawSupMode
                : 'detailed';
            const allowStatementRequest = (statementAccessMode !== 'disabled');
            const receiptApprovalMode = (document.getElementById('sup-receipt-approval-mode')?.value === 'review_first') ? 'review_first' : 'auto_post';
            const defaultBankId = (document.getElementById('sup-default-bank-id')?.value || '').trim();
            const foundDefaultBank = getActiveSystemBanksList().find(b => String(b.id) === String(defaultBankId));
            const defaultBankName = foundDefaultBank ? foundDefaultBank.name : '';

            const subPhones = [];
            document.querySelectorAll('.sup-sub-phone-row').forEach(row => {
                const spNum = (row.querySelector('.sub-phone-number')?.value || '').trim();
                const spLabel = (row.querySelector('.sub-phone-label')?.value || '').trim();
                if (spNum) {
                    subPhones.push({ phone: spNum, label: spLabel });
                }
            });

            if (!name) { return focusRequiredMasterField('sup-name', '⚠️ الرجاء إدخال اسم المورد'); }

            // Check for duplicate names in Customers and Suppliers
            const currentName = name.trim().toLowerCase();
            let allCusts = []; let allSups = [];
            try {
                allCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                allSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if(!Array.isArray(allCusts)) allCusts = [];
                if(!Array.isArray(allSups)) allSups = [];
            } catch(e) {}
            
            const isCustDup = allCusts.some(c => !c.deleted && c.name && c.name.trim().toLowerCase() === currentName && c.id !== id);
            const isSupDup = allSups.some(s => !s.deleted && s.name && s.name.trim().toLowerCase() === currentName && s.id !== id);
            
            if (isCustDup || isSupDup) {
                return focusRequiredMasterField('sup-name', '⚠️ هذا الاسم موجود مسبقاً (كعميل أو مورد)');
            }

            // Maintain background receiptCodeNum automatically
            const rawCodeNumInput = normalizeDigitsToAscii(document.getElementById('sup-receipt-code-num')?.value || '').trim();
            let receiptCodeNum = parseInt(rawCodeNumInput, 10);
            const dupCodeNumCust = allCusts.find(c => !c.deleted && c.id !== id && parseInt(normalizeDigitsToAscii(c.receiptCodeNum), 10) === receiptCodeNum);
            const dupCodeNumSup = allSups.find(s => !s.deleted && s.id !== id && parseInt(normalizeDigitsToAscii(s.receiptCodeNum), 10) === receiptCodeNum);
            if (isNaN(receiptCodeNum) || receiptCodeNum < 1 || dupCodeNumCust || dupCodeNumSup) {
                receiptCodeNum = getNextAvailableCustomerReceiptCode(id);
            }

            const receiptCodeWord = '';
            let statementPin = normalizeDigitsToAscii(document.getElementById('sup-statement-pin')?.value || '').replace(/[^0-9]/g, '');
            if (!statementPin) {
                statementPin = getNextUniqueStatementPin(id);
            } else {
                const dupPinCust = allCusts.find(c => !c.deleted && c.id !== id && normalizeDigitsToAscii(c.statementPin || '').replace(/[^0-9]/g, '') === statementPin);
                const dupPinSup = allSups.find(s => !s.deleted && s.id !== id && normalizeDigitsToAscii(s.statementPin || '').replace(/[^0-9]/g, '') === statementPin);
                if (dupPinCust || dupPinSup) {
                    const dupOwner = dupPinCust ? `العميل "${dupPinCust.name}"` : `المورد "${dupPinSup.name}"`;
                    return focusRequiredMasterField('sup-statement-pin', `⚠️ الرقم السري (${statementPin}) مسجل لـ ${dupOwner}`);
                }
            }

            let suppliers = allSups;
            const existingIdx = suppliers.findIndex(s => s.id === id);
            
            const todayStr = new Date().toISOString().split('T')[0];
            const nowIso = new Date().toISOString();

            const supData = {
                id, name, phone, whatsapp, email, address, 
                receiptCodeNum,
                receiptCodeWord,
                statementPin,
                statementAccessMode,
                allowStatementRequest,
                receiptApprovalMode,
                defaultBankId,
                defaultBankName,
                subPhones,
                openingDebit, openingCredit,
                openingBalance: (openingCredit - openingDebit),
                opening_balance: (openingCredit - openingDebit),
                currency, exRate, exchangeRate: exRate, taxNumber, accountNo,
                currentBalance: (openingCredit - openingDebit),
                balance: (openingCredit - openingDebit),
                creditLimit: limit,
                paymentTermsDays,
                securityLevel: security, status: status, notes,
                date: existingIdx > -1 ? (suppliers[existingIdx].date || todayStr) : todayStr,
                createdAt: existingIdx > -1 ? (suppliers[existingIdx].createdAt || nowIso) : nowIso
            };

            if (existingIdx > -1) {
                const oldOp = (parseFloat(suppliers[existingIdx].openingCredit) || 0) - (parseFloat(suppliers[existingIdx].openingDebit) || 0);
                const newOp = (openingCredit - openingDebit);
                supData.currentBalance = (parseFloat(suppliers[existingIdx].currentBalance) || 0) + (newOp - oldOp);
                supData.balance = supData.currentBalance;
                suppliers[existingIdx] = supData;
            } else {
                suppliers.push(supData);
            }

            setMasterLocalData('erp_suppliers_v1', suppliers, true);
            logMasterDataAudit(existingIdx > -1 ? 'تعديل' : 'إضافة', 'الموردين', name, `رصيد افتتاحي: ${opBalance} | أجل سداد: ${paymentTermsDays} يوم`);
            clearSupplierForm();
            renderSuppliers();
            triggerMasterSavePulse('#supplier-form');
            if (typeof showToast === 'function') showToast(`✅ تم حفظ المورد "${name}" (🔐 ${statementPin})`, 'success', 2000);

            const backendRes = await syncToBackend('save_supplier', supData);
            if (backendRes && backendRes.success && backendRes.data) {
                const srvSup = backendRes.data;
                const sIdx = suppliers.findIndex(s => s.id === id);
                if (sIdx > -1) {
                    suppliers[sIdx] = Object.assign({}, supData, srvSup);
                    setMasterLocalData('erp_suppliers_v1', suppliers, true);
                }
            }
        }

        function renderSuppliers(query = '') {
            populateWaReceiptBankDropdowns();
            const tbody = document.querySelector('#tab-suppliers-table tbody');
            if(!tbody) return;
            let suppliers = ensureSupplierReceiptCodes();
            suppliers = suppliers.filter(s => !s.deleted);
            if (suppliers.length === 0 && !query) {
                suppliers = [
    { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, creditLimit: 50000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, creditLimit: 30000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, creditLimit: 20000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, creditLimit: 100000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, creditLimit: 15000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, creditLimit: 25000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, creditLimit: 40000000, status: 'active', securityLevel: 1 }
];
                try { localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers)); } catch(e){}
                suppliers = ensureSupplierReceiptCodes().filter(s => !s.deleted);
            }

            if (query) {
                const norm = typeof window.normalizeArabicFuzzyText === 'function' ? window.normalizeArabicFuzzyText : (s => normalizeDigitsToAscii(s || '').trim().toLowerCase());
                const q = norm(query);
                const qCleanCode = normalizeDigitsToAscii(query).trim().replace(/^#/, '');
                suppliers = suppliers.filter(s => {
                    const pInfo = getEntityFull5DigitPin(s.whatsapp || s.phone || '', s.statementPin);
                    const pinStr = pInfo.pin3;
                    const full5Str = pInfo.full5;
                    const subPhonesMatch = Array.isArray(s.subPhones) && s.subPhones.some(sp => {
                        const p = typeof sp === 'string' ? sp : (sp.phone || '');
                        const l = typeof sp === 'object' ? (sp.label || '') : '';
                        return norm(p).includes(q) || norm(l).includes(q);
                    });
                    return norm(s.name).includes(q) ||
                           norm(s.phone).includes(q) ||
                           norm(s.whatsapp).includes(q) ||
                           (pinStr && pinStr.includes(qCleanCode)) ||
                           (full5Str && full5Str.includes(qCleanCode)) ||
                           subPhonesMatch;
                });
            }

            suppliers.sort((a, b) => (parseInt(a.receiptCodeNum, 10) || 999999) - (parseInt(b.receiptCodeNum, 10) || 999999));

            const maxShowSup = window.__mdSupRenderLimit || 100;
            const visibleSuppliers = suppliers.slice(0, maxShowSup);
            const rowsHtmlSup = [];
            visibleSuppliers.forEach(s => {
                const statusMap = {'vip': '<span style="background:rgba(168,85,247,0.15); color:#a855f7; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🌟 VIP</span>', 'blacklisted': '<span style="background:rgba(239,68,68,0.15); color:#ef4444; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🔴 محظور</span>'};
                const statusBadge = statusMap[s.status] || '<span style="background:rgba(52,211,153,0.15); color:#34d399; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:700;">🟢 نشط</span>';
                const bal = (typeof window.computeLiveEntityLedgerBalance === 'function')
                    ? window.computeLiveEntityLedgerBalance('supplier', s)
                    : (parseFloat(s.currentBalance) || ((parseFloat(s.openingCredit) || 0) - (parseFloat(s.openingDebit) || 0)));
                const balColor = bal > 0 ? '#34d399' : (bal < 0 ? '#f87171' : '#94a3b8');
                const balLabel = bal > 0 ? 'له' : (bal < 0 ? 'عليه' : 'مسدد');
                const agingBadgeSup = (typeof window.computeEntityDebtAgingBadge === 'function') ? window.computeEntityDebtAgingBadge('supplier', s, bal) : '';
                const balDisplay = `<span onclick="openEntityDrillDownStatementModal('supplier', '${s.id}')" style="color:${balColor}; font-weight:900; font-family:monospace; cursor:pointer; text-decoration:underline dotted;" title="اضغط لفتح كشف الحساب التفصيلي والحركات (Drill-Down)">${Math.abs(bal).toLocaleString()}</span> <small style="color:${balColor}; opacity:0.8;">${balLabel}</small>${agingBadgeSup}`;
                
                const pInfo = getEntityFull5DigitPin(s.whatsapp || s.phone || '', s.statementPin);
                const cleanPin = pInfo.pin3;
                const pinLabelHtml = (pInfo.last2 && cleanPin.length === 3)
                    ? `<span style="color:#4ade80;">${pInfo.last2}</span><span>${cleanPin}</span>`
                    : (cleanPin || '-');
                const pinTip = (pInfo.last2 && cleanPin.length === 3)
                    ? `الرقم السري (5 أرقام): ${pInfo.full5} = آخر رقمين من الواتساب (${pInfo.last2}) + الثلاثة أرقام السرية (${cleanPin}) — اضغط للنسخ`
                    : `الرقم السري: ${cleanPin || '-'} (أضف رقم الواتساب ليصبح 5 أرقام)`;
                const pinBadge = `<span onclick="copyEntityWhatsAppCard('supplier', '${s.id}')" title="${pinTip}" style="background:rgba(168,85,247,0.18); color:#e9d5ff; border:1px solid rgba(168,85,247,0.45); padding:3px 10px; border-radius:8px; font-weight:900; font-family:monospace; font-size:0.88rem; display:inline-block; cursor:pointer; direction:ltr;">🔐 ${pinLabelHtml} 📋</span>`;

                const subCount = Array.isArray(s.subPhones) ? s.subPhones.length : 0;
                const subPhonesBadge = subCount > 0 ? `<div style="margin-top:3px;"><span style="background:rgba(56,189,248,0.12); color:#38bdf8; padding:1px 6px; border-radius:6px; font-size:0.72rem; font-weight:700;">📱 +${subCount}</span></div>` : '';

                const stmtToggleBtn = buildTableStatementControlHtml('supplier', s);

                rowsHtmlSup.push(`<tr>
                    <td style="text-align:center;">${pinBadge}</td>
                    <td style="font-weight:bold;">${s.name}</td>
                    <td style="text-align:center; direction:ltr;"><div style="font-family:monospace; font-weight:800;">${s.whatsapp || s.phone || '-'}</div>${subPhonesBadge}</td>
                    <td style="text-align:center;">${stmtToggleBtn}</td>
                    <td style="text-align:center;">${statusBadge}</td>
                    <td style="text-align:center;">${balDisplay}</td>
                    <td style="text-align:center; cursor:pointer;" ondblclick="startInlineMasterEdit(this, 'supplier_limit', '${s.id}', ${parseFloat(s.creditLimit) || 0})">${(s.creditLimit || 0).toLocaleString()} SDG <small style="opacity:0.45;">✏️</small></td>
                    <td style="text-align:center;">${s.securityLevel || 1}</td>
                    <td style="text-align:center; white-space:nowrap;"><button class="btn-text" style="color:#fbbf24; font-weight:800; margin-left:5px;" onclick="openEntityDrillDownStatementModal('supplier', '${s.id}')" title="كشف الحساب التفصيلي والحركات (Drill-Down)">📊</button><button class="btn-text" style="color:#38bdf8; font-weight:800; margin-left:5px;" onclick="window.location.href='account-statement.php?partner='+encodeURIComponent('${(s.name || '').replace(/'/g, "\\'")}')" title="كشف الحساب الرسمي الشامل">📄</button><button class="btn-text" style="color:#f59e0b; font-weight:800; margin-left:5px;" onclick="window.location.href='supplier_reliability.php'" title="تقييم وموثوقية المورد 360°">🏭</button><button class="btn-text" style="color:#34d399; font-weight:800; margin-left:6px;" onclick="openEntityQrCardModal('supplier', '${s.id}')" title="بطاقة QR">🪪</button><button class="btn-text" style="color:#38bdf8; font-weight:800;" onclick="editSupplier('${s.id}')">✏️ تعديل</button><button class="btn-text" style="color:var(--danger-color); font-weight:800; margin-right:8px;" onclick="deleteSupplier('${s.id}')">🗑️ حذف</button></td>
                </tr>`);
            });
            if (!query && suppliers.length > maxShowSup) {
                rowsHtmlSup.push(`<tr><td colspan="9" style="text-align:center; padding:12px;"><button type="button" class="btn-action" style="background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:6px 18px; border-radius:8px; font-weight:800; cursor:pointer;" onclick="window.__mdSupRenderLimit=(window.__mdSupRenderLimit||80)+100; renderSuppliers();">⬇️ عرض المزيد (${maxShowSup} / ${suppliers.length})</button></td></tr>`);
            }
            tbody.innerHTML = rowsHtmlSup.join('');
            if (typeof window.refreshMasterDataHealthAndEquilibrium === 'function') window.refreshMasterDataHealthAndEquilibrium();
        }

        function filterSuppliers() {
            const query = document.getElementById('supplier-search-input')?.value || '';
            renderSuppliers(query);
        }
        
        function clearSupplierForm() {
            ensureSupplierReceiptCodes();
            populateWaReceiptBankDropdowns();
            document.getElementById('sup-id').value = '';
            document.getElementById('sup-name').value = '';
            const codeNumEl = document.getElementById('sup-receipt-code-num');
            if (codeNumEl) codeNumEl.value = getNextAvailableCustomerReceiptCode('');
            const codeWordEl = document.getElementById('sup-receipt-code-word');
            if (codeWordEl) codeWordEl.value = '';
            const pinEl = document.getElementById('sup-statement-pin');
            if (pinEl) pinEl.value = getNextUniqueStatementPin('');
            updateCombinedPinPreview('sup');
            setFormStatementMode('sup', 'detailed', false);
            setFormReceiptApprovalMode('sup', 'auto_post', false);
            const defBankEl = document.getElementById('sup-default-bank-id');
            if (defBankEl) defBankEl.value = '';
            document.getElementById('sup-phone').value = '';
            document.getElementById('sup-whatsapp').value = '';
            document.getElementById('sup-email').value = '';
            document.getElementById('sup-address').value = '';
            document.getElementById('sup-opening-balance').value = '';
            document.getElementById('sup-currency').value = 'SDG';

            document.getElementById('sup-tax').value = '';
            document.getElementById('sup-account-no').value = '';
            document.getElementById('sup-limit').value = '';
            if (document.getElementById('sup-payment-terms')) document.getElementById('sup-payment-terms').value = '';
            document.getElementById('sup-security').value = '1';
            document.getElementById('sup-status').value = 'active';
            document.getElementById('sup-notes').value = '';
            const subPhonesContainer = document.getElementById('sup-sub-phones-container');
            if (subPhonesContainer) {
                subPhonesContainer.innerHTML = '';
            }
            if (typeof updateSupExchangeRate === 'function') updateSupExchangeRate();
            window.__mdFormDirty = false;
        }
        window.closeSupplierForm = function() {
            clearSupplierForm();
        };
        
        function editSupplier(id) {
            populateWaReceiptBankDropdowns();
            let suppliers = ensureSupplierReceiptCodes();
            suppliers = suppliers.filter(s => !s.deleted);
            const sup = suppliers.find(s => s.id === id);
            if(!sup) return;
            document.getElementById('sup-id').value = sup.id;
            document.getElementById('sup-name').value = sup.name;
            const codeNumEl = document.getElementById('sup-receipt-code-num');
            if (codeNumEl) codeNumEl.value = sup.receiptCodeNum || getNextAvailableCustomerReceiptCode(sup.id);
            const codeWordEl = document.getElementById('sup-receipt-code-word');
            if (codeWordEl) codeWordEl.value = '';
            const pinEl = document.getElementById('sup-statement-pin');
            if (pinEl) pinEl.value = sup.statementPin || getNextUniqueStatementPin(sup.id);
            updateCombinedPinPreview('sup');
            setFormStatementMode('sup', getEntityStatementMode(sup), false);
            setFormReceiptApprovalMode('sup', getEntityReceiptApprovalMode(sup), false);
            const defBankEl = document.getElementById('sup-default-bank-id');
            if (defBankEl) defBankEl.value = sup.defaultBankId || '';
            const unifiedWa = sup.whatsapp || sup.phone || '';
            document.getElementById('sup-phone').value = unifiedWa;
            document.getElementById('sup-whatsapp').value = unifiedWa;
            document.getElementById('sup-email').value = sup.email || '';
            document.getElementById('sup-address').value = sup.address || '';
            const bal = (sup.openingCredit || 0) - (sup.openingDebit || 0);
            document.getElementById('sup-opening-balance').value = bal || '';
            document.getElementById('sup-currency').value = sup.currency || 'SDG';
            if (typeof updateSupExchangeRate === 'function') updateSupExchangeRate();
            if (sup.exRate && document.getElementById('sup-ex-rate')) {
                document.getElementById('sup-ex-rate').value = sup.exRate;
            }

            document.getElementById('sup-tax').value = sup.taxNumber || '';
            document.getElementById('sup-account-no').value = sup.accountNo || '';
            document.getElementById('sup-limit').value = sup.creditLimit || '';
            if (document.getElementById('sup-payment-terms')) document.getElementById('sup-payment-terms').value = sup.paymentTermsDays || '';
            document.getElementById('sup-security').value = sup.securityLevel || '1';
            document.getElementById('sup-status').value = sup.status || 'active';
            document.getElementById('sup-notes').value = sup.notes || '';
            const subPhonesContainer = document.getElementById('sup-sub-phones-container');
            if (subPhonesContainer) {
                subPhonesContainer.innerHTML = '';
                if (Array.isArray(sup.subPhones) && sup.subPhones.length > 0) {
                    sup.subPhones.forEach(sp => addSupplierSubPhone(sp));
                }
            }
            
            document.getElementById('supplier-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteSupplier(id) {
            genericDeleteEntity('supplier', id);
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

            const qRaw = (document.getElementById('employee-search-input')?.value || '').trim();
            if (qRaw) {
                const norm = typeof window.normalizeArabicFuzzyText === 'function' ? window.normalizeArabicFuzzyText : (s => String(s || '').toLowerCase());
                const q = norm(qRaw);
                employees = employees.filter(emp =>
                    norm(emp.name || emp.fullName || emp.username).includes(q) ||
                    norm(emp.phone).includes(q) ||
                    norm(emp.role).includes(q)
                );
            }

            const roleMapAr = { 'admin': '👑 مدير نظام', 'accountant': '📊 محاسب مالي', 'cashier': '💰 أمين خزينة / كاشير', 'storekeeper': '📦 أمين مخزن', 'sales': '🛒 مسؤول مبيعات', 'viewer': '👁️ مراقب / مستعرض' };
            tbody.innerHTML = employees.map(emp => {
                const roleBadge = roleMapAr[emp.role] || emp.role || '-';
                const stBadge = (emp.status === 'active' || emp.status === 'نشط')
                    ? '<span style="background:rgba(16,185,129,0.16); color:#34d399; padding:2px 8px; border-radius:8px; font-size:0.78rem; font-weight:800;">🟢 نشط</span>'
                    : '<span style="background:rgba(239,68,68,0.16); color:#f87171; padding:2px 8px; border-radius:8px; font-size:0.78rem; font-weight:800;">🔴 موقوف</span>';
                return `<tr>
                    <td style="font-weight:bold;">${emp.name || emp.fullName || emp.username || '-'}</td>
                    <td><span style="background:rgba(56,189,248,0.12); color:#38bdf8; padding:2px 8px; border-radius:6px; font-size:0.8rem; font-weight:800;">${roleBadge}</span></td>
                    <td style="direction:ltr; font-family:monospace;">${emp.phone||'-'}</td>
                    <td>${emp.securityLevel || 1}</td>
                    <td>${stBadge}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn-text" style="color:#10b981; font-weight:900; margin-left:6px;" onclick="window.shareSingleEmployeeWhatsApp && window.shareSingleEmployeeWhatsApp('${emp.id}')" title="مراسلة الموظف أو إرسال بياناته عبر الواتساب">💬 واتساب</button>
                        <button class="btn-text" style="color:#34d399; font-weight:800; margin-left:6px;" onclick="window.location.href='payroll.php'" title="مسير الرواتب والاستحقاقات">💵 الرواتب</button>
                        <button class="btn-text" style="color:#fbbf24; font-weight:800; margin-left:6px;" onclick="window.location.href='jobs.php'" title="المهام التنفيذية للموظف">📋 المهام</button>
                        <button class="btn-text" style="color:#a855f7; font-weight:bold; margin-left:8px;" onclick="editEmployee('${emp.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); font-weight:bold;" onclick="deleteEmployee('${emp.id}')">🗑️ حذف</button>
                    </td>
                </tr>`;
            }).join('');
        }
        window.shareMasterItemsWhatsApp = function () {
            const name = (document.getElementById('item-name')?.value || '').trim();
            const code = (document.getElementById('item-id')?.value || '').trim();
            const price = (document.getElementById('item-price')?.value || '').trim();
            let msg = '';
            if (name) {
                msg = `📦 *بطاقة صنف*\n• الصنف: ${name}\n• الكود: ${code || '-'}\n• السعر: ${price || '0'} ج.س`;
            } else {
                const list = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]') || []).filter(x => !x.deleted).slice(0, 30);
                msg = `📦 *قائمة الأصناف والأسعار (${list.length})*\n` + list.map((x, idx) => `${idx + 1}. ${x.name} — ${(Number(x.sellingPrice) || 0).toLocaleString()} ج.س`).join('\n');
            }
            window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
        };
        window.shareSingleEmployeeWhatsApp = function (empId) {
            const list = (JSON.parse(localStorage.getItem('erp_users_v1') || '[]') || []).filter(x => !x.deleted);
            const emp = list.find(x => String(x.id) === String(empId));
            if (!emp) return;
            const ph = String(emp.phone || '').replace(/[^0-9]/g, '');
            const msg = `👥 *بيانات الموظف*\n• الاسم: ${emp.name || emp.username}\n• الدور: ${emp.role || '-'}\n• الهاتف: ${emp.phone || '-'}`;
            window.open('https://wa.me/' + ph + '?text=' + encodeURIComponent(msg), '_blank');
        };
        window.shareMasterEmployeesWhatsApp = function () {
            const name = (document.getElementById('emp-name')?.value || '').trim();
            const phRaw = (document.getElementById('emp-phone')?.value || '').trim();
            if (name) {
                const ph = phRaw.replace(/[^0-9]/g, '');
                const msg = `👥 *بطاقة موظف*\n• الاسم: ${name}\n• الهاتف: ${phRaw || '-'}\n• الدور: ${document.getElementById('emp-role')?.value || '-'}`;
                window.open('https://wa.me/' + ph + '?text=' + encodeURIComponent(msg), '_blank');
                return;
            }
            const list = (JSON.parse(localStorage.getItem('erp_users_v1') || '[]') || []).filter(x => !x.deleted);
            const msg = `👥 *قائمة الموظفين (${list.length})*\n` + list.map((e, i) => `${i + 1}. ${e.name || e.username} (${e.role || '-'}) — ${e.phone || '-'}`).join('\n');
            window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
        };
        
        function deleteEmployee(id) {
            genericDeleteEntity('employee', id);
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
            setTimeout(() => document.getElementById('emp-name')?.focus(), 30);
        }
        
        function saveEmployee() {
            const id = document.getElementById('emp-id').value || 'EMP-' + Date.now();
            const name = document.getElementById('emp-name').value;
            if(!name.trim()) { return focusRequiredMasterField('emp-name', '⚠️ الرجاء إدخال اسم الموظف'); }
            if(isDuplicateName('erp_employees_v1', name, id)) { return focusRequiredMasterField('emp-name', '⚠️ اسم الموظف موجود مسبقاً'); }
            
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            
            const existingIdx = employees.findIndex(e => e.id === id);
            
            const rawPassword = document.getElementById('emp-password').value;
            const existingPassword = (existingIdx > -1 && employees[existingIdx].password) ? employees[existingIdx].password : '123456';
            const empData = {
                id,
                name,
                username: (name || '').trim().toLowerCase(),
                fullName: name,
                phone: document.getElementById('emp-phone').value,
                password: rawPassword ? rawPassword : existingPassword,
                role: document.getElementById('emp-role').value,
                securityLevel: parseInt(document.getElementById('emp-security').value) || 1,
                status: document.getElementById('emp-status').value
            };
            
            if (existingIdx > -1) {
                employees[existingIdx] = empData;
            } else {
                employees.push(empData);
            }
            
            setMasterLocalData('erp_users_v1', employees);
            setMasterLocalData('erp_employees_v1', employees);

            syncToBackend('save_employee', empData);
            logMasterDataAudit(existingIdx > -1 ? 'تعديل' : 'إضافة', 'الموظفين', name, `الدور: ${empData.role} | السرية: ${empData.securityLevel}`);
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true).catch(console.error);
            }
            document.getElementById('employee-form').style.display='none';
            renderEmployees();
            triggerMasterSavePulse('#tab-employees .md-card');
            if (typeof showToast === 'function') showToast('✅ تم حفظ الموظف بنجاح', 'success', 2000);
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
            const empForm = document.getElementById('employee-form');
            if (empForm) {
                empForm.style.display = 'block';
                empForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

    
        // ================= BANKS & SAFES LOGIC =================
        
    function autoUpdateMasterBankIBAN() {
        const bankName = document.getElementById('bank-name')?.value || '';
        const accNo = document.getElementById('bank-gl-account')?.value || document.getElementById('bank-code')?.value || '';
        const branch = document.getElementById('bank-branch')?.value || '00001';
        const ibanField = document.getElementById('bank-iban');
        
        if (ibanField && accNo) {
            const bban = typeof calculateSudanBBAN === 'function' ? calculateSudanBBAN(bankName, accNo, branch) : '';
            if (bban) {
                ibanField.value = bban;
            }
        }
    }

        function openBankForm() {
            const form = document.getElementById('bank-form');
            if (!form) return;
            form.style.display = 'block';
            document.getElementById('bank-code').value = 'B-' + Date.now();
            document.getElementById('bank-name').value = '';
            document.getElementById('bank-branch').value = '';
            document.getElementById('bank-opening-balance').value = '';
            document.getElementById('bank-opening-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('bank-gl-account').value = '';
            document.getElementById('bank-iban').value = '';
            document.getElementById('bank-swift').value = '';
            document.getElementById('bank-currency').value = 'SDG';
            if (document.getElementById('bank-ex-rate')) {
                document.getElementById('bank-ex-rate').value = 1;
            }
            document.getElementById('bank-confidentiality').value = '1';
            document.getElementById('bank-inactive').checked = false;
            document.querySelectorAll('.bank-payment-method').forEach(cb => cb.checked = true);
            const title = document.getElementById('bank-form-title');
            if (title) title.innerText = 'إضافة حساب بنكي';
            if (typeof updateBankExchangeRate === 'function') updateBankExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(() => document.getElementById('bank-name')?.focus(), 30);
        }

        function closeBankForm() {
            const form = document.getElementById('bank-form');
            if (form) form.style.display = 'none';
        }

        function saveBank() {
            const id = document.getElementById('bank-code').value || 'B-' + Date.now();
            const name = document.getElementById('bank-name').value.trim();
            if(!name) { return focusRequiredMasterField('bank-name', '⚠️ الرجاء إدخال اسم الحساب البنكي'); }
            if(isDuplicateName('erp_banks_v1', name, id)) { return focusRequiredMasterField('bank-name', '⚠️ اسم البنك موجود مسبقاً'); }

            const type = 'bank';
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            
            const existingIdx = banks.findIndex(b => b.id === id);

            const paymentMethods = [];
            document.querySelectorAll('.bank-payment-method:checked').forEach(cb => {
                paymentMethods.push(cb.value);
            });

            const exRate = parseFloat(document.getElementById('bank-ex-rate')?.value) || 1;

            const opBalVal = parseFloat(document.getElementById('bank-opening-balance').value) || 0;
            const bankData = {
                id, code: id, name, bankName: name, type,
                branch: document.getElementById('bank-branch').value.trim(),
                openingBalance: opBalVal,
                opening_balance: opBalVal,
                currentBalance: opBalVal,
                balance: opBalVal,
                openingDate: document.getElementById('bank-opening-date').value,
                glAccount: document.getElementById('bank-gl-account').value.trim(),
                iban: document.getElementById('bank-iban').value.trim(),
                accountNumber: document.getElementById('bank-iban').value.trim(),
                swift: document.getElementById('bank-swift').value.trim(),
                responsible: '',
                currency: document.getElementById('bank-currency').value,
                exchangeRate: exRate,
                exRate: exRate,
                paymentMethods: paymentMethods,
                maxLimit: 0,
                minLimit: 0,
                confidentiality: document.getElementById('bank-confidentiality').value,
                securityLevel: parseInt(document.getElementById('bank-confidentiality').value) || 1,
                isInactive: document.getElementById('bank-inactive').checked,
                status: document.getElementById('bank-inactive').checked ? 'inactive' : 'active',
                isModifier: false
            };

            if (existingIdx > -1) {
                const oldOp = parseFloat(banks[existingIdx].openingBalance) || 0;
                const newOp = bankData.openingBalance;
                bankData.currentBalance = (parseFloat(banks[existingIdx].currentBalance) || 0) + (newOp - oldOp);
                bankData.balance = bankData.currentBalance;
                banks[existingIdx] = bankData;
            } else {
                banks.push(bankData);
            }

            setMasterLocalData('erp_banks_v1', banks, true);
            syncToBackend('save_bank', bankData);
            logMasterDataAudit(existingIdx > -1 ? 'تعديل' : 'إضافة', 'البنوك', name, `رصيد افتتاحي: ${opBalVal} ${bankData.currency || 'SDG'}`);
            if (window.MasterBanksHelper) window.MasterBanksHelper.refresh();
            closeBankForm();
            renderBanks();
            triggerMasterSavePulse('#tab-banks .md-card');
            if (typeof showToast === 'function') showToast('✅ تم حفظ البنك بنجاح', 'success', 2000);
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
            const qRaw = (document.getElementById('bank-search-input')?.value || '').trim();
            if (qRaw) {
                const norm = typeof window.normalizeArabicFuzzyText === 'function' ? window.normalizeArabicFuzzyText : (s => String(s || '').toLowerCase());
                const q = norm(qRaw);
                banks = banks.filter(b =>
                    norm(b.name).includes(q) ||
                    norm(b.id).includes(q) ||
                    norm(b.branch).includes(q) ||
                    norm(b.glAccount).includes(q) ||
                    norm(b.iban).includes(q)
                );
            }
            let chequesList = [];
            try {
                const c1 = JSON.parse(localStorage.getItem('erp_checks_v1') || '[]') || [];
                const c2 = JSON.parse(localStorage.getItem('elsajana_md_c11_cheques') || '[]') || [];
                chequesList = [...c1, ...c2];
            } catch (_) {}
            tbody.innerHTML = banks.map(b => {
                const typeLabel = '🏦 حساب بنكي';
                const liveBal = (typeof window.computeLiveEntityLedgerBalance === 'function')
                    ? window.computeLiveEntityLedgerBalance('bank', b)
                    : (parseFloat(b.currentBalance ?? b.balance ?? b.openingBalance) || 0);
                const balFormatted = liveBal.toLocaleString('en-US', { minimumFractionDigits: 2 });
                const pendingChq = chequesList.filter(c => c && (c.bankId === b.id || c.bankName === b.name || c.bank === b.name) && (c.status === 'portfolio' || c.status === 'under_collection' || c.status === 'في الخزينة' || c.status === 'تحت التحصيل'));
                const pendingBadge = pendingChq.length > 0
                    ? ` <span onclick="window.location.href='checks-cycle.php'" style="background:rgba(245,158,11,0.18);color:#fde047;border:1px solid rgba(245,158,11,0.45);padding:1px 6px;border-radius:6px;font-size:0.72rem;font-weight:900;cursor:pointer;" title="شيكات بالحافظة/تحت التحصيل — اضغط لفتح دورة الشيكات">📝 حافظة: ${pendingChq.length}</span>`
                    : '';
                return `<tr>
                    <td style="color:#38bdf8; font-weight:bold;">${b.id}</td>
                    <td style="font-weight:bold;">${b.name} <br><small style="color:#94a3b8;">${b.branch || ''}</small></td>
                    <td>${typeLabel}${pendingBadge}</td>
                    <td><span class="badge" style="background:rgba(56,189,248,0.2); color:#38bdf8; padding:3px 8px; border-radius:4px;">${b.currency || 'SDG'}</span></td>
                    <td style="font-weight:bold; color:#34d399; cursor:pointer; text-decoration:underline dotted;" onclick="openEntityDrillDownStatementModal('bank', '${b.id}')" title="اضغط لفتح كشف حركات البنك التفصيلي">${balFormatted}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn-text" style="color:#34d399; margin-left:6px; font-weight:800;" onclick="openEntityDrillDownStatementModal('bank', '${b.id}')" title="كشف حركات البنك التفصيلي">📊 كشف</button>
                        <button class="btn-text" style="color:#fbbf24; margin-left:6px; font-weight:800;" onclick="window.location.href='checks-cycle.php'" title="حافظة ودورة الشيكات لهذا البنك">📝 الشيكات</button>
                        <button class="btn-text" style="color:#38bdf8;" onclick="editBank('${b.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteBank('${b.id}')">🗑️ حذف</button>
                    </td>
                </tr>`;
            }).join('');
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
            
            const form = document.getElementById('bank-form');
            if (!form) return;
            form.style.display = 'block';

            document.getElementById('bank-code').value = bank.id;
            document.getElementById('bank-name').value = bank.name || '';
            document.getElementById('bank-branch').value = bank.branch || '';
            document.getElementById('bank-opening-balance').value = bank.openingBalance !== undefined ? bank.openingBalance : '';
            document.getElementById('bank-opening-date').value = bank.openingDate || '';
            document.getElementById('bank-gl-account').value = bank.glAccount || '';
            document.getElementById('bank-iban').value = bank.iban || '';
            document.getElementById('bank-swift').value = bank.swift || '';
            document.getElementById('bank-currency').value = bank.currency || 'SDG';
            if (document.getElementById('bank-ex-rate')) {
                document.getElementById('bank-ex-rate').value = bank.exchangeRate || 1;
            }
            document.getElementById('bank-confidentiality').value = bank.confidentiality || '1';
            document.getElementById('bank-inactive').checked = bank.isInactive || false;
            
            const methods = bank.paymentMethods || ['cash', 'pos', 'transfer', 'check'];
            document.querySelectorAll('.bank-payment-method').forEach(cb => {
                cb.checked = methods.includes(cb.value);
            });

            const title = document.getElementById('bank-form-title');
            if (title) title.innerText = 'تعديل حساب بنكي: ' + (bank.name || '');
            if (typeof updateBankExchangeRate === 'function') updateBankExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function deleteBank(id) {
            genericDeleteEntity('bank', id);
            if (window.MasterBanksHelper) window.MasterBanksHelper.refresh();
        }

        // ================= SAFES (خزن) LOGIC =================
        function openSafeForm() {
            const form = document.getElementById('safe-form');
            if (!form) return;
            form.style.display = 'block';
            document.getElementById('safe-code').value = 'SAFE-' + Date.now();
            document.getElementById('safe-name').value = '';
            document.getElementById('safe-opening-balance').value = '';
            document.getElementById('safe-opening-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('safe-gl-account').value = '';
            document.getElementById('safe-responsible').value = '';
            document.getElementById('safe-currency').value = 'SDG';
            if (document.getElementById('safe-ex-rate')) {
                document.getElementById('safe-ex-rate').value = 1;
            }
            document.getElementById('safe-max-limit').value = '';
            document.getElementById('safe-min-limit').value = '';
            document.getElementById('safe-confidentiality').value = '1';
            document.getElementById('safe-inactive').checked = false;
            document.getElementById('safe-modifier').checked = false;
            document.querySelectorAll('.safe-payment-method').forEach(cb => cb.checked = true);
            const title = document.getElementById('safe-form-title');
            if (title) title.innerText = 'إضافة خزينة جديدة';
            if (typeof updateSafeExchangeRate === 'function') updateSafeExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(() => document.getElementById('safe-name')?.focus(), 30);
        }

        function closeSafeForm() {
            const form = document.getElementById('safe-form');
            if (form) form.style.display = 'none';
        }

        function saveSafe() {
            const id = document.getElementById('safe-code').value || 'SAFE-' + Date.now();
            const name = document.getElementById('safe-name').value.trim();
            if(!name) { return focusRequiredMasterField('safe-name', '⚠️ الرجاء إدخال اسم الخزينة'); }
            if(isDuplicateName('erp_safes_v1', name, id)) { return focusRequiredMasterField('safe-name', '⚠️ اسم الخزينة موجود مسبقاً'); }

            const type = 'safe';
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            
            const isDupSafeName = safes.some(b => b.name && b.name.trim().toLowerCase() === name.toLowerCase() && b.id !== id);
            if (isDupSafeName) {
                return focusRequiredMasterField('safe-name', '⚠️ هذا الاسم مستخدم لخزنة أخرى');
            }

            const existingIdx = safes.findIndex(b => b.id === id);

            const paymentMethods = [];
            document.querySelectorAll('.safe-payment-method:checked').forEach(cb => {
                paymentMethods.push(cb.value);
            });

            const exRate = parseFloat(document.getElementById('safe-ex-rate')?.value) || 1;

            const opSafeBal = parseFloat(document.getElementById('safe-opening-balance').value) || 0;
            const safeData = {
                id, code: id, name, safeName: name, type,
                branch: '',
                openingBalance: opSafeBal,
                opening_balance: opSafeBal,
                currentBalance: opSafeBal,
                balance: opSafeBal,
                openingDate: document.getElementById('safe-opening-date').value,
                glAccount: document.getElementById('safe-gl-account').value.trim(),
                iban: '',
                swift: '',
                responsible: document.getElementById('safe-responsible').value,
                currency: document.getElementById('safe-currency').value,
                exchangeRate: exRate,
                exRate: exRate,
                paymentMethods: paymentMethods,
                maxLimit: parseFloat(document.getElementById('safe-max-limit').value) || 0,
                minLimit: parseFloat(document.getElementById('safe-min-limit').value) || 0,
                confidentiality: document.getElementById('safe-confidentiality').value,
                securityLevel: parseInt(document.getElementById('safe-confidentiality').value) || 1,
                isInactive: document.getElementById('safe-inactive').checked,
                status: document.getElementById('safe-inactive').checked ? 'inactive' : 'active',
                isModifier: document.getElementById('safe-modifier').checked
            };

            if (existingIdx > -1) {
                const oldOp = parseFloat(safes[existingIdx].openingBalance) || 0;
                const newOp = safeData.openingBalance;
                safeData.currentBalance = (parseFloat(safes[existingIdx].currentBalance) || 0) + (newOp - oldOp);
                safeData.balance = safeData.currentBalance;
                safes[existingIdx] = safeData;
            } else {
                safes.push(safeData);
            }

            setMasterLocalData('erp_safes_v1', safes, true);
            syncToBackend('save_safe', safeData);
            logMasterDataAudit(existingIdx > -1 ? 'تعديل' : 'إضافة', 'الخزن', name, `رصيد افتتاحي: ${opSafeBal} ${safeData.currency || 'SDG'}`);
            closeSafeForm();
            renderSafes();
            triggerMasterSavePulse('#tab-safes .md-card');
            if (typeof showToast === 'function') showToast('✅ تم حفظ الخزينة بنجاح', 'success', 2000);
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
            const qRaw = (document.getElementById('safe-search-input')?.value || '').trim();
            if (qRaw) {
                const norm = typeof window.normalizeArabicFuzzyText === 'function' ? window.normalizeArabicFuzzyText : (s => String(s || '').toLowerCase());
                const q = norm(qRaw);
                safes = safes.filter(b =>
                    norm(b.name).includes(q) ||
                    norm(b.id).includes(q) ||
                    norm(b.responsible).includes(q) ||
                    norm(b.glAccount).includes(q)
                );
            }
            tbody.innerHTML = safes.map(b => {
                const actualBalNum = (typeof window.computeLiveEntityLedgerBalance === 'function')
                    ? window.computeLiveEntityLedgerBalance('safe', b)
                    : (parseFloat(b.currentBalance ?? b.balance ?? b.openingBalance) || 0);
                const isBlindRole = (window.__mdActiveRole === 'cashier' || window.__mdActiveRole === 'sales' || window.__mdC12BlindMaskActive === true);
                const balFormatted = isBlindRole
                    ? '<span style="color:#fbbf24;font-weight:900;" title="مخفي طبقاً لنظام الجرد الأعمى (الدستور 12)">🙈 مخفي للجرد الأعمى</span>'
                    : actualBalNum.toLocaleString('en-US', { minimumFractionDigits: 2 });
                return `<tr>
                    <td style="color:#4ade80; font-weight:bold;">${b.id}</td>
                    <td style="font-weight:bold;">${b.name}</td>
                    <td><span class="badge" style="background:rgba(74,222,128,0.2); color:#4ade80; padding:3px 8px; border-radius:4px;">${b.currency || 'SDG'}</span></td>
                    <td style="font-weight:bold; font-family:monospace; cursor:pointer; text-decoration:underline dotted;" onclick="openEntityDrillDownStatementModal('safe', '${b.id}')" title="اضغط لفتح كشف حركات الخزينة التفصيلي">${balFormatted}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn-text" style="color:#38bdf8;" onclick="editSafe('${b.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSafe('${b.id}')">🗑️ حذف</button>
                    </td>
                </tr>`;
            }).join('');
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
            
            const form = document.getElementById('safe-form');
            if (!form) return;
            form.style.display = 'block';

            document.getElementById('safe-code').value = safe.id;
            document.getElementById('safe-name').value = safe.name || '';
            document.getElementById('safe-opening-balance').value = safe.openingBalance !== undefined ? safe.openingBalance : '';
            document.getElementById('safe-opening-date').value = safe.openingDate || '';
            document.getElementById('safe-gl-account').value = safe.glAccount || '';
            document.getElementById('safe-responsible').value = safe.responsible || '';
            document.getElementById('safe-currency').value = safe.currency || 'SDG';
            if (document.getElementById('safe-ex-rate')) {
                document.getElementById('safe-ex-rate').value = safe.exchangeRate || 1;
            }
            document.getElementById('safe-max-limit').value = safe.maxLimit !== undefined ? safe.maxLimit : '';
            document.getElementById('safe-min-limit').value = safe.minLimit !== undefined ? safe.minLimit : '';
            document.getElementById('safe-confidentiality').value = safe.confidentiality || '1';
            document.getElementById('safe-inactive').checked = safe.isInactive || false;
            document.getElementById('safe-modifier').checked = safe.isModifier || false;

            const methods = safe.paymentMethods || ['cash', 'pos', 'transfer', 'check'];
            document.querySelectorAll('.safe-payment-method').forEach(cb => {
                cb.checked = methods.includes(cb.value);
            });

            const title = document.getElementById('safe-form-title');
            if (title) title.innerText = 'تعديل الخزينة: ' + (safe.name || '');
            if (typeof updateSafeExchangeRate === 'function') updateSafeExchangeRate();
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function deleteSafe(id) {
            genericDeleteEntity('safe', id);
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
            window.openQuickMasterPromptModal('➕ إضافة فئة جديدة', 'اسم الفئة (مثال: أسمنت، حديد)...', '', (name) => {
                if(!name) return;
                let cats = [];
                try {
                    cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                    if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
                } catch(e) {
                    cats = ['مواد بناء','أدوات صحية','كهرباء'];
                }
                if(!cats.includes(name)) {
                    cats.push(name);
                    localStorage.setItem('erp_categories_v1', JSON.stringify(cats));
                    syncToBackend('categories', cats);
                }
                updateCategoryDropdowns();
                document.getElementById('item-category').value = name;
                renderCategoriesList();
                showToast(`✅ تمت إضافة الفئة (${name})`, 'success', 2000);
            });
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value.trim();
            if(!name) { return focusRequiredMasterField('item-name-input', '⚠️ الرجاء إدخال اسم الصنف'); }
            const baseUnit = document.getElementById('item-base-unit').value.trim();
            if(!baseUnit) { return focusRequiredMasterField('item-base-unit', '⚠️ الرجاء اختيار الوحدة الأساسية للصنف'); }
            const id = document.getElementById('item-code').value.trim() || 'ITEM-' + Date.now();
            if(isDuplicateName('erp_items_v1', name, id)) { return focusRequiredMasterField('item-name-input', '⚠️ اسم الصنف موجود مسبقاً، يرجى اختيار اسم آخر'); }
            
            const sellingPriceInput = parseFloat(document.getElementById('item-selling-price').value) || 0;
            const itemBarcode = document.getElementById('item-barcode').value.trim();

            const units = [];
            const unitsArray = [];

            const rows = document.querySelectorAll('#units-matrix-body tr');
            rows.forEach((row, idx) => {
                const tds = row.querySelectorAll('td');
                if (tds.length >= 8) {
                    let uName = '';
                    if (idx === 0) {
                        uName = baseUnit;
                    } else {
                        const sel = tds[1].querySelector('select, input');
                        uName = sel ? sel.value.trim() : '';
                    }
                    if (!uName) uName = (idx === 0 ? baseUnit : 'وحدة ' + (idx + 1));

                    const uFactor = (idx === 0) ? 1 : (parseFloat(tds[2].querySelector('input')?.value) || 1);
                    const uBarcode = tds[3].querySelector('input')?.value.trim() || (idx === 0 ? itemBarcode : '');
                    
                    let uCash = parseFloat(tds[4].querySelector('input')?.value) || 0;
                    if (idx === 0 && uCash === 0 && sellingPriceInput > 0) {
                        uCash = sellingPriceInput;
                    }
                    const uCredit = parseFloat(tds[5].querySelector('input')?.value) || 0;
                    const uMin = parseFloat(tds[6].querySelector('input')?.value) || 0;
                    const uWeight = parseFloat(tds[7].querySelector('input')?.value) || 0;
                    
                    units.push({
                        unitName: uName,
                        factor: uFactor,
                        barcode: uBarcode,
                        cashPrice: uCash,
                        creditPrice: uCredit,
                        minPrice: uMin,
                        weight: uWeight,
                        stock: 0
                    });

                    unitsArray.push({
                        name: uName,
                        unitName: uName,
                        factor: uFactor,
                        price: uCash,
                        cashPrice: uCash,
                        creditPrice: uCredit,
                        minPrice: uMin,
                        barcode: uBarcode
                    });
                }
            });

            if (units.length === 0) {
                units.push({
                    unitName: baseUnit,
                    factor: 1,
                    barcode: itemBarcode,
                    cashPrice: sellingPriceInput,
                    creditPrice: 0,
                    minPrice: 0,
                    weight: 0,
                    stock: 0
                });
                unitsArray.push({
                    name: baseUnit,
                    unitName: baseUnit,
                    factor: 1,
                    price: sellingPriceInput,
                    cashPrice: sellingPriceInput,
                    barcode: itemBarcode
                });
            }

            const finalSellingPrice = (units[0] && units[0].cashPrice > 0) ? units[0].cashPrice : sellingPriceInput;
            const finalBarcode = (units[0] && units[0].barcode) ? units[0].barcode : itemBarcode;

            const newBarcodes = [finalBarcode, ...units.map(u => u.barcode)].filter(b => b && b.trim() !== '');

            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const opQtyVal = parseFloat(document.getElementById('item-opening-qty').value) || 0;
            const opCostVal = parseFloat(document.getElementById('item-opening-value').value) || 0;
            const unitCostPrice = (units[0] && parseFloat(units[0].purchasePrice) > 0) ? parseFloat(units[0].purchasePrice) : opCostVal;
            const itemData = {
                id,
                code: id,
                name,
                nameLatin: document.getElementById('item-name-latin').value.trim(),
                barcode: finalBarcode,
                category: document.getElementById('item-category').value,
                subCategory: document.getElementById('item-sub-category').value,
                nature: document.getElementById('item-nature').value,
                brand: document.getElementById('item-brand').value.trim(),
                status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive',
                sellingPrice: finalSellingPrice,
                price: finalSellingPrice,
                salePrice: finalSellingPrice,
                costPrice: unitCostPrice,
                cost: unitCostPrice,
                purchasePrice: unitCostPrice,
                maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0,
                taxable: document.getElementById('item-taxable').checked,
                taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0,
                costMethod: costRadio ? costRadio.value : 'lastPurchase',
                openingQty: opQtyVal,
                openingStock: opQtyVal,
                openingBalance: opQtyVal,
                opening_balance: opQtyVal,
                openingValue: opCostVal,
                binLocation: document.getElementById('item-bin-location').value.trim(),
                maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0,
                minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0,
                reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0,
                baseUnit: baseUnit,
                unit: baseUnit,
                bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1,
                units: units,
                unitsArray: unitsArray,
                imageUrl: document.getElementById('item-image-data')?.value || '',
                description: document.getElementById('item-description').value
            };

            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const duplicateName = items.find(i => i.name.trim() === name && i.id !== id);
            if (duplicateName) { return focusRequiredMasterField('item-name-input', '⚠️ هذا الصنف مسجل مسبقاً بنفس الاسم'); }
            
            for (const b of newBarcodes) {
                const duplicateBarcode = items.find(i => i.id !== id && (i.barcode === b || (i.units && i.units.some(u => u.barcode === b))));
                if (duplicateBarcode) {
                    return focusRequiredMasterField('item-barcode', '⚠️ الباركود (' + b + ') مستخدم مسبقاً في صنف آخر: ' + duplicateBarcode.name);
                }
            }
            
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) {
                const oldOpQty = parseFloat(items[ei].openingQty ?? items[ei].openingStock ?? items[ei].openingBalance) || 0;
                const deltaOpQty = opQtyVal - oldOpQty;
                if(items[ei].units) {
                    itemData.units.forEach((u,i) => {
                        if(items[ei].units[i]) {
                            u.stock = (parseFloat(items[ei].units[i].stock) || 0) + (i === 0 ? deltaOpQty : 0);
                        }
                    });
                }
                itemData.stock = parseFloat(itemData.units[0]?.stock) || opQtyVal;
                itemData.currentStock = itemData.stock;
                itemData.quantity = itemData.stock;
                items[ei] = itemData;
            } else {
                itemData.units[0].stock = opQtyVal;
                itemData.stock = opQtyVal;
                itemData.currentStock = opQtyVal;
                itemData.quantity = opQtyVal;
                items.push(itemData);
            }
            
            setMasterLocalData('erp_items_v1', items, true);
            // Synchronize erp_inventory_v1 (with itemId, qty, warehouse) & Item Price History Log (erp_item_price_history_v1)
            try {
                let whList = JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]');
                const defaultWhName = (Array.isArray(whList) && whList[0] && whList[0].name) ? whList[0].name : 'المخزن الرئيسي';
                let invList = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
                if (!Array.isArray(invList)) invList = [];
                const invIdx = invList.findIndex(iv => iv && (String(iv.id) === String(id) || String(iv.itemId) === String(id) || String(iv.code) === String(id) || iv.name === name));
                const invEntry = {
                    id: id,
                    itemId: id,
                    code: id,
                    name: name,
                    warehouse: defaultWhName,
                    category: itemData.category,
                    unit: baseUnit,
                    baseUnit: baseUnit,
                    openingBalance: opQtyVal,
                    opening_balance: opQtyVal,
                    openingQty: opQtyVal,
                    qty: itemData.stock,
                    stock: itemData.stock,
                    currentStock: itemData.stock,
                    quantity: itemData.stock,
                    costPrice: unitCostPrice,
                    cost: unitCostPrice,
                    sellingPrice: finalSellingPrice,
                    price: finalSellingPrice,
                    minLimit: itemData.minLimit,
                    reorderLevel: itemData.reorderLevel
                };
                if (invIdx > -1) {
                    invList[invIdx] = Object.assign({}, invList[invIdx], invEntry);
                } else {
                    invList.push(invEntry);
                }
                setMasterLocalData('erp_inventory_v1', invList, false);

                // Item Price History Log
                let priceHist = JSON.parse(localStorage.getItem('erp_item_price_history_v1') || '[]');
                if (!Array.isArray(priceHist)) priceHist = [];
                priceHist.unshift({
                    itemId: id,
                    itemName: name,
                    costPrice: unitCostPrice,
                    sellingPrice: finalSellingPrice,
                    source: 'البيانات الأساسية',
                    date: new Date().toISOString()
                });
                if (priceHist.length > 500) priceHist = priceHist.slice(0, 500);
                localStorage.setItem('erp_item_price_history_v1', JSON.stringify(priceHist));
            } catch (invErr) {}
            syncToBackend('save_item', itemData);
            logMasterDataAudit(ei > -1 ? 'تعديل' : 'إضافة', 'الأصناف', name, `السعر: ${finalSellingPrice} | الوحدة: ${baseUnit} | الافتتاحي: ${itemData.openingQty}`);
            closeItemForm();
            renderItems();
            if (typeof window.triggerMasterSavePulse === 'function') window.triggerMasterSavePulse('#tab-items .md-card');
            if (typeof showToast === 'function') {
                showToast('✅ تم حفظ الصنف ووحداته بنجاح', 'success', 2000);
            }
        }

        function updateLiveItemQRPreview(forceQR = false) {
            const box = document.getElementById('item-qr-box');
            if (!box) return;
            const imgData = document.getElementById('item-image-data')?.value || '';
            if (imgData && !forceQR) {
                box.innerHTML = `<img src="${imgData}" alt="Item" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">`;
                return;
            }
            const code = (document.getElementById('item-code')?.value || '').trim();
            const barcode = (document.getElementById('item-barcode')?.value || '').trim();
            const name = (document.getElementById('item-name-input')?.value || '').trim();
            const qrPayload = barcode || code || name;
            if (!qrPayload) {
                box.innerHTML = `<span style="font-size:2rem;">📱</span><span style="font-size:0.72rem; color:#38bdf8; margin-top:4px; font-weight:bold;">QR تلقائي</span>`;
                return;
            }
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=95x95&data=' + encodeURIComponent(qrPayload);
            box.innerHTML = `<img src="${qrUrl}" alt="QR" style="width:92px; height:92px; border-radius:8px; background:#fff; padding:3px;">`;
        }

        function handleItemImageUpload(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const maxDim = 220;
                    let w = img.width, h = img.height;
                    if (w > h && w > maxDim) { h = Math.round(h * maxDim / w); w = maxDim; }
                    else if (h > maxDim) { w = Math.round(w * maxDim / h); h = maxDim; }
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                    const hiddenInput = document.getElementById('item-image-data');
                    if (hiddenInput) hiddenInput.value = dataUrl;
                    updateLiveItemQRPreview(false);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function printItemBarcodeFromForm() {
            const id = (document.getElementById('item-code')?.value || '').trim();
            const name = (document.getElementById('item-name-input')?.value || '').trim();
            const barcode = (document.getElementById('item-barcode')?.value || id || '').trim();
            const price = parseFloat(document.getElementById('item-selling-price')?.value) || 0;
            if (!name && !id) {
                return focusRequiredMasterField('item-name-input', '⚠️ الرجاء إدخال اسم الصنف أولاً لطباعة الباركود');
            }
            const params = new URLSearchParams();
            if (id) params.set('id', id);
            if (name) params.set('name', name);
            if (barcode) params.set('barcode', barcode);
            if (price) params.set('price', String(price));
            window.open('barcode-printer.php?' + params.toString(), '_blank');
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
            
            const resolveItemTotalStock = (it) => {
                if (!it) return 0;
                if (typeof it.stock === 'object' && it.stock !== null) {
                    const sumObj = Object.values(it.stock).reduce((s, v) => s + (parseFloat(v) || 0), 0);
                    if (sumObj > 0) return sumObj;
                } else if (it.stock !== undefined && it.stock !== null && it.stock !== '') {
                    const numStock = parseFloat(it.stock);
                    if (!isNaN(numStock)) return numStock;
                }
                return parseFloat(it.openingQty ?? (it.units && it.units[0] ? it.units[0].stock : 0)) || 0;
            };

            let lowStock = 0;
            items.forEach(i => {
                let stock = resolveItemTotalStock(i);
                let reorder = parseFloat(i.reorderLevel || i.minLimit || 5) || 5;
                if (stock <= reorder) lowStock++;
            });
            const lowEl = document.getElementById('kpi-low-stock');
            if(lowEl) lowEl.innerText = lowStock + ' صنف';
            
            if (currentItemSearchQuery) {
                const normItemQ = window.normalizeArabicFuzzyText(currentItemSearchQuery);
                items = items.filter(i => {
                    const unitBarcodes = i.units ? i.units.map(u => u.barcode || '').join(' ') : '';
                    const str = window.normalizeArabicFuzzyText(i.id + ' ' + i.name + ' ' + (i.category||'') + ' ' + (i.barcode||'') + ' ' + unitBarcodes);
                    return str.includes(normItemQ);
                });
            }
            
            const totalPages = Math.ceil(items.length / itemsPerPage) || 1;
            if (currentPageItems > totalPages) currentPageItems = totalPages;
            const start = (currentPageItems - 1) * itemsPerPage;
            const paginatedItems = items.slice(start, start + itemsPerPage);
            
            const rowsHtmlItems = [];
            paginatedItems.forEach(i => {
                let bu = i.baseUnit || (i.units && i.units[0] ? i.units[0].unitName : '-');
                let unitsCount = Array.isArray(i.units) && i.units.length > 1 ? ` <span style="background:rgba(168,85,247,0.18);color:#d8b4fe;border:1px solid rgba(168,85,247,0.4);padding:1px 6px;border-radius:6px;font-size:0.72rem;font-weight:800;" title="متعدد الوحدات (${i.units.length} وحدات)">📐×${i.units.length}</span>` : '';
                let stockVal = resolveItemTotalStock(i);
                let reservedVal = parseFloat(i.reservedQty || 0) || 0;
                let netAvail = Math.max(0, stockVal - reservedVal);
                let reorderLvl = parseFloat(i.reorderLevel || i.minLimit || 5) || 5;
                let stockPill = netAvail <= 0
                    ? ` <span style="background:rgba(239,68,68,0.18);color:#fca5a5;border:1px solid rgba(239,68,68,0.45);padding:1px 6px;border-radius:6px;font-size:0.72rem;font-weight:800;">🔴 رصيد: 0</span>`
                    : (netAvail <= reorderLvl
                        ? ` <span style="background:rgba(245,158,11,0.2);color:#fde047;border:1px solid rgba(245,158,11,0.5);padding:1px 6px;border-radius:6px;font-size:0.72rem;font-weight:800;" title="وصل لحد الطلب (${reorderLvl})">🟠 متاح: ${netAvail}</span>`
                        : ` <span style="background:rgba(16,185,129,0.16);color:#34d399;border:1px solid rgba(16,185,129,0.4);padding:1px 6px;border-radius:6px;font-size:0.72rem;font-weight:800;">🟢 متاح: ${netAvail}</span>`);
                let price = parseFloat(i.sellingPrice || (i.units && i.units[0] ? i.units[0].cashPrice : 0)) || 0;
                let cost = parseFloat(i.costPrice || i.cost || i.openingValue || (i.units && i.units[0] ? i.units[0].purchasePrice : 0)) || 0;
                const isMarginWarn = (price <= 0 || (cost > 0 && price <= cost));
                const marginBadge = isMarginWarn ? ` <span style="background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.45); padding:1px 6px; border-radius:6px; font-size:0.72rem; font-weight:800;" title="تنبيه تسعير: سعر البيع صفر أو أقل/يساوي التكلفة (${cost.toLocaleString()})">⚠️ راجع السعر</span>` : '';
                const badge = i.status === 'inactive' ? '<span style="color:var(--danger-color); font-size:0.8rem; font-weight:bold; background:rgba(239,68,68,0.1); padding:3px 8px; border-radius:6px;">🔴 موقوف</span>' : '<span style="color:var(--success-color); font-size:0.8rem; font-weight:bold; background:rgba(52,211,153,0.1); padding:3px 8px; border-radius:6px;">🟢 نشط</span>';
                const subCat = i.subCategory ? ' <small style="color:var(--text-secondary);">(' + i.subCategory + ')</small>' : '';
                rowsHtmlItems.push(`<tr class="item-row"><td class="item-id-cell" style="font-weight:bold; color:#38bdf8;">${i.id}</td><td class="item-name-cell" style="font-weight:bold;">${i.name}${subCat}</td><td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${i.category || '-'}</span></td><td>${bu}${unitsCount}${stockPill}</td><td style="font-weight:bold; color:var(--success-color); cursor:pointer;" ondblclick="startInlineMasterEdit(this, 'item_price', '${i.id}', ${price})" title="انقر مرتين للتعديل المباشر لسعر البيع مثل Excel ثم اضغط Enter">${price.toLocaleString()} SDG <small style="opacity:0.45;">✏️</small>${marginBadge}</td><td>${badge}</td><td style="white-space:nowrap;"><button class="btn-text" style="color:#fbbf24; font-size:1rem; margin-left:5px;" onclick="window.location.href='item-ledger.php?item='+encodeURIComponent('${(i.name || '').replace(/'/g, "\\'")}')" title="فتح كرت الصنف التفصيلي وحركاته">📦</button><button class="btn-text" style="color:#34d399; font-size:1rem; margin-left:5px;" onclick="if(typeof window.openMdInspectionAndPartialReceiptModal==='function')window.openMdInspectionAndPartialReceiptModal('${i.id}')" title="فحص واستلام بضاعة (سليم / تالف / مرفوض)">🔬</button><button class="btn-text" style="color:#38bdf8; font-size:1.05rem; margin-left:6px;" onclick="openItemPriceHistoryModal('${i.id}')" title="سجل تاريخ الأسعار">📜</button><button class="btn-text" style="color:#a855f7; font-size:1.1rem; margin-left:8px;" onclick="editItem('${i.id}')" title="تعديل">✏️</button><button class="btn-text" style="color:var(--danger-color); font-size:1.1rem;" onclick="deleteItem('${i.id}')" title="حذف">🗑️</button></td></tr>`);
            });
            tbody.innerHTML = rowsHtmlItems.join('');
            
            renderItemsPagination(totalPages);
            renderCategoriesList();
            if (typeof window.refreshMasterDataHealthAndEquilibrium === 'function') window.refreshMasterDataHealthAndEquilibrium();
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
                if (totalPages <= 1) {
                    pagContainer.style.display = 'none';
                } else {
                    pagContainer.style.display = 'flex';
                    pagContainer.innerHTML = `
                        <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems>1){currentPageItems--; renderItems();}">السابق</button>
                        <span style="color:var(--text-secondary); font-weight:bold;">صفحة ${currentPageItems} من ${totalPages}</span>
                        <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems<${totalPages}){currentPageItems++; renderItems();}">التالي</button>
                    `;
                }
            }
        }

        let currentPageItems = 1;
        const itemsPerPage = 100;
        let currentItemSearchQuery = '';

        function filterItemsTable() {
            currentItemSearchQuery = (document.getElementById('item-search')?.value || '').trim();
            currentPageItems = 1;
            renderItems();
        }

        function quickAddUnit() {
            window.openQuickMasterPromptModal({
                title: '➕ إضافة وحدة قياس جديدة',
                placeholder: 'أدخل اسم الوحدة الجديدة...',
                onConfirm: function(cleanName) {
                    let units = [];
                    try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
                    if(!units.includes(cleanName)) {
                        units.push(cleanName);
                        setMasterLocalData('erp_global_units_v1', units);
                        syncToBackend('units', units);
                        renderGlobalUnitsList();
                        updateUnitDropdowns(cleanName);
                        if (typeof showToast === 'function') showToast('✅ تم إضافة الوحدة بنجاح', 'success', 2000);
                    }
                }
            });
        }

        function buildUnitRow(num, u, isBase) {
            const dis = isBase ? 'disabled' : '';
            const bg = isBase ? 'style="background:rgba(52,211,153,0.08); border-right:3px solid var(--success-color);"' : '';
            const act = isBase ? '<span style="font-size:0.75rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); padding:3px 8px; border-radius:4px;">أساسية</span>' : "<button type=\"button\" class=\"btn-text\" style=\"color:var(--danger-color);\" onclick=\"if(typeof window.removeMdUnitMatrixRowWithUndo==='function'){window.removeMdUnitMatrixRowWithUndo(this);}else{this.closest('tr').remove();}\">✖</button>";
            const idAttr = isBase ? ' id="base-unit-name-display"' : ' class="unit-name-input"';
            
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            const val = u.unitName || '';
            if (val && !units.includes(val)) units.push(val); // Ensure backward compat display
            
            let opts = `<option value="">-- اختر --</option>` + units.map(un => `<option value="${un}" ${un === val ? 'selected' : ''}>${un}</option>`).join('');
            
            let unitField = isBase ? 
                `<input type="text" id="base-unit-name-display" value="${val}" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; padding:10px; border-radius:8px; outline:none; font-size:1.1rem;">` : 
                `<select ${idAttr} style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:bold; text-align:center; padding:10px; border-radius:8px; outline:none;">${opts}</select>`;

            return '<tr ' + bg + '><td style="text-align:center; font-weight:bold;">' + num + '</td><td>' + unitField + '</td><td><input type="number" value="' + (u.factor || 1) + '" ' + dis + ' style="width:100%; background:transparent; border:' + (isBase ? 'none' : '1px solid rgba(255,255,255,0.15)') + '; color:' + (isBase ? 'var(--success-color)' : '#fff') + '; font-weight:bold; text-align:center; padding:10px; border-radius:8px; outline:none; font-size:1.2rem;"></td><td><input type="text" value="' + (u.barcode || '') + '" placeholder="باركود..." style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:10px; border-radius:8px; outline:none;"></td><td><input type="number" value="' + (u.cashPrice || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#34d399; padding:10px; border-radius:8px; font-weight:bold; outline:none;"></td><td><input type="number" value="' + (u.creditPrice || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#60a5fa; padding:10px; border-radius:8px; font-weight:bold; outline:none;"></td><td><input type="number" value="' + (u.minPrice || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#f87171; padding:10px; border-radius:8px; font-weight:bold; outline:none;"></td><td><input type="number" value="' + (u.weight || '') + '" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:10px; border-radius:8px; outline:none;"></td><td style="text-align:center;">' + act + '</td></tr>';
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
            
            const baseUnit = item.baseUnit || item.unit || 'قطعة';
            updateUnitDropdowns(baseUnit);
            document.getElementById('item-base-unit').value = baseUnit;
            document.getElementById('item-bundle-factor').value = item.bundleFactor || 1;
            document.getElementById('item-description').value = item.description || '';
            if (document.getElementById('item-image-data')) {
                document.getElementById('item-image-data').value = item.imageUrl || '';
            }
            updateLiveItemQRPreview(false);
            updateBaseUnitRow();
            
            const tbody = document.getElementById('units-matrix-body');
            tbody.innerHTML = '';
            
            let matrixUnits = [];
            if (Array.isArray(item.units) && item.units.length > 0) {
                matrixUnits = item.units;
            } else if (Array.isArray(item.unitsArray) && item.unitsArray.length > 0) {
                matrixUnits = item.unitsArray.map(u => ({
                    unitName: u.name || u.unitName || baseUnit,
                    factor: u.factor || 1,
                    barcode: u.barcode || '',
                    cashPrice: u.price || u.cashPrice || item.sellingPrice || 0,
                    creditPrice: u.creditPrice || 0,
                    minPrice: u.minPrice || 0,
                    weight: u.weight || 0
                }));
            }
            if (matrixUnits.length === 0) {
                matrixUnits = [{
                    unitName: baseUnit,
                    factor: 1,
                    barcode: item.barcode || '',
                    cashPrice: item.sellingPrice || 0,
                    creditPrice: 0,
                    minPrice: 0,
                    weight: 0
                }];
            }
            
            matrixUnits.forEach((u, idx) => {
                tbody.insertAdjacentHTML('beforeend', buildUnitRow(idx + 1, u, idx === 0));
            });
            
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        // ================= ITEM DELETION & USAGE CHECK LOGIC =================
        let currentDeleteItemData = null;

        // ================= UNIVERSAL MASTER DATA DELETION & VOUCHER USAGE CHECK ENGINE =================
        const ENTITY_CONFIG = {
            warehouse: {
                type: 'warehouse',
                label: 'المخزن',
                labelPlural: 'المخازن',
                icon: '🏢',
                localKey: 'erp_warehouses_v1',
                backendType: 'delete_warehouse',
                render: () => { if (typeof renderWarehouses === 'function') renderWarehouses(); }
            },
            customer: {
                type: 'customer',
                label: 'العميل',
                labelPlural: 'العملاء',
                icon: '👥',
                localKey: 'erp_customers_v1',
                backendType: 'delete_customer',
                render: () => { if (typeof renderCustomers === 'function') renderCustomers(); }
            },
            supplier: {
                type: 'supplier',
                label: 'المورد',
                labelPlural: 'الموردين',
                icon: '🏭',
                localKey: 'erp_suppliers_v1',
                backendType: 'delete_supplier',
                render: () => { if (typeof renderSuppliers === 'function') renderSuppliers(); }
            },
            bank: {
                type: 'bank',
                label: 'البنك',
                labelPlural: 'البنوك',
                icon: '🏦',
                localKey: 'erp_banks_v1',
                backendType: 'delete_bank',
                render: () => { if (typeof renderBanks === 'function') renderBanks(); }
            },
            safe: {
                type: 'safe',
                label: 'الخزينة',
                labelPlural: 'الخزن',
                icon: '💰',
                localKey: 'erp_safes_v1',
                backendType: 'delete_safe',
                render: () => { if (typeof renderSafes === 'function') renderSafes(); }
            },
            item: {
                type: 'item',
                label: 'الصنف',
                labelPlural: 'الأصناف',
                icon: '📦',
                localKey: 'erp_items_v1',
                backendType: 'delete_item',
                render: () => { if (typeof renderItems === 'function') renderItems(); }
            },
            employee: {
                type: 'employee',
                label: 'الموظف',
                labelPlural: 'الموظفين',
                icon: '👤',
                localKey: 'erp_users_v1',
                backendType: 'delete_employee',
                render: () => { if (typeof renderEmployees === 'function') renderEmployees(); }
            },
            unit: {
                type: 'unit',
                label: 'وحدة القياس',
                labelPlural: 'الوحدات',
                icon: '📐',
                localKey: 'erp_global_units_v1',
                backendType: 'delete_unit',
                render: () => {
                    if (typeof renderGlobalUnitsList === 'function') renderGlobalUnitsList();
                    if (typeof updateUnitDropdowns === 'function') updateUnitDropdowns();
                    if (typeof renderItems === 'function') renderItems();
                }
            },
            cost_center: {
                type: 'cost_center',
                label: 'مركز التكلفة',
                labelPlural: 'مراكز التكلفة',
                icon: '📊',
                localKey: 'erp_jobs_v1',
                backendType: 'delete_cost_center',
                render: () => { if (typeof renderCostCenters === 'function') renderCostCenters(); }
            },
            category: {
                type: 'category',
                label: 'الفئة',
                labelPlural: 'الفئات',
                icon: '🏷️',
                localKey: 'erp_categories_v1',
                backendType: 'delete_category',
                render: () => {
                    if (typeof renderCategoriesList === 'function') renderCategoriesList();
                    if (typeof updateCategoryDropdowns === 'function') updateCategoryDropdowns();
                    if (typeof renderItems === 'function') renderItems();
                }
            }
        };

        let currentDeleteTarget = null;

        function closeEntityDeleteModal() {
            const modal = document.getElementById('master-data-delete-modal') || document.getElementById('item-delete-modal');
            if (modal) modal.style.display = 'none';
            currentDeleteTarget = null;
        }
        function closeItemDeleteModal() { closeEntityDeleteModal(); }

        function openEntityDeleteModalLoading(cfg, entity) {
            const modal = document.getElementById('master-data-delete-modal') || document.getElementById('item-delete-modal');
            if (!modal) return;

            const nameEl = document.getElementById('md-del-target-name') || document.getElementById('item-del-target-name');
            const codeEl = document.getElementById('md-del-target-code') || document.getElementById('item-del-target-code');
            const typeLabelEl = document.getElementById('md-del-target-type-label');
            const codeLabelEl = document.getElementById('md-del-target-code-label');

            if (nameEl) nameEl.innerText = entity.name || entity.id || 'عنصر غير محدد';
            if (codeEl) codeEl.innerText = entity.id || entity.name || '-';
            if (typeLabelEl) typeLabelEl.innerText = `${cfg.icon} اسم ${cfg.label}:`;
            if (codeLabelEl) codeLabelEl.innerText = `🏷️ كود ${cfg.label}:`;

            const iconWrap = document.getElementById('md-del-modal-icon-wrap') || document.getElementById('item-del-modal-icon-wrap');
            const iconEl = document.getElementById('md-del-modal-icon') || document.getElementById('item-del-modal-icon');
            if (iconWrap) {
                iconWrap.style.background = 'rgba(56,189,248,0.15)';
                iconWrap.style.borderColor = 'rgba(56,189,248,0.3)';
                iconWrap.style.boxShadow = '0 0 20px rgba(56,189,248,0.2)';
            }
            if (iconEl) iconEl.innerText = '🔍';

            const titleEl = document.getElementById('md-del-modal-title') || document.getElementById('item-del-modal-title');
            const subtitleEl = document.getElementById('md-del-modal-subtitle') || document.getElementById('item-del-modal-subtitle');
            if (titleEl) {
                titleEl.innerText = `جاري فحص ارتباطات ${cfg.label} بالسندات...`;
                titleEl.style.color = '#fff';
            }
            if (subtitleEl) subtitleEl.innerText = `يتم التحقق من الفواتير، السندات، والقيود المحاسبية وحركات المخزون`;

            const statusBox = document.getElementById('md-del-status-box') || document.getElementById('item-del-status-box');
            if (statusBox) {
                statusBox.style.background = 'rgba(56,189,248,0.08)';
                statusBox.style.borderColor = 'rgba(56,189,248,0.2)';
                statusBox.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:center; gap:12px; padding:15px; color:#38bdf8; font-weight:800; font-size:1.1rem;">
                        <span style="display:inline-block; animation:spin 1s infinite linear;">⏳</span>
                        <span>جاري فحص السجلات المحاسبية والمخزنية وقاعدة البيانات...</span>
                    </div>
                `;
            }

            const vouchersContainer = document.getElementById('md-del-vouchers-container') || document.getElementById('item-del-vouchers-container');
            if (vouchersContainer) vouchersContainer.style.display = 'none';

            const footerEl = document.getElementById('md-del-modal-footer') || document.getElementById('item-del-modal-footer');
            if (footerEl) {
                footerEl.innerHTML = `
                    <button type="button" onclick="closeEntityDeleteModal()" class="btn-action" style="padding:8px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; cursor:pointer;">إلغاء</button>
                `;
            }

            modal.style.display = 'flex';
        }

        function scanEntityLocalUsages(entityType, entity) {
            const usages = [];
            const entId = String(entity.id || '');
            const entName = String(entity.name || '').trim().toLowerCase();
            const barcodes = [entity.barcode || ''];
            if (Array.isArray(entity.units)) {
                entity.units.forEach(u => { if (u && u.barcode) barcodes.push(u.barcode); });
            }

            const isMatch = (val) => {
                if (val === null || val === undefined || val === '') return false;
                const s = String(val).trim();
                const sl = s.toLowerCase();
                if (entId && s === entId) return true;
                if (entName && sl === entName) return true;
                if (entId && (s === 'cust-' + entId || s === 'supp-' + entId || s === 'wh-' + entId || s === 'safe-' + entId || s === 'bank-' + entId)) return true;
                if (barcodes.includes(s)) return true;
                return false;
            };

            // 1. Sales Invoices
            try {
                const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
                if (Array.isArray(sales)) {
                    sales.forEach(s => {
                        if (!s) return;
                        let matched = false;
                        if (entityType === 'warehouse') {
                            if (isMatch(s.warehouse) || isMatch(s.warehouseId) || isMatch(s.deliveryWarehouse)) matched = true;
                            if (Array.isArray(s.items)) {
                                s.items.forEach(it => { if (isMatch(it.warehouseId) || isMatch(it.warehouse)) matched = true; });
                            }
                        } else if (entityType === 'customer') {
                            if (isMatch(s.customer) || isMatch(s.customerId) || isMatch(s.customerName) || isMatch(s.client)) matched = true;
                        } else if (entityType === 'bank') {
                            if (isMatch(s.paymentAccountId) || isMatch(s.bankId) || isMatch(s.paymentAccount) || isMatch(s.bank)) matched = true;
                        } else if (entityType === 'safe') {
                            if (isMatch(s.safe) || isMatch(s.safeId) || isMatch(s.paymentAccountId) || isMatch(s.cashBox)) matched = true;
                        } else if (entityType === 'item') {
                            if (Array.isArray(s.items)) {
                                s.items.forEach(it => {
                                    if (isMatch(it.itemId) || isMatch(it.id) || isMatch(it.itemName) || isMatch(it.name) || isMatch(it.barcode) || isMatch(it.qrCode)) matched = true;
                                });
                            }
                        } else if (entityType === 'unit') {
                            if (Array.isArray(s.items)) {
                                s.items.forEach(it => { if (isMatch(it.selectedUnit) || isMatch(it.unit)) matched = true; });
                            }
                        } else if (entityType === 'cost_center') {
                            if (isMatch(s.costCenter) || isMatch(s.costCenterId)) matched = true;
                        } else if (entityType === 'employee') {
                            if (isMatch(s.createdBy) || isMatch(s.salesRep) || isMatch(s.salesman) || isMatch(s.driver) || isMatch(s.user)) matched = true;
                        }

                        if (matched) {
                            const invNo = s.invoiceNo || s.id || '';
                            usages.push({
                                type: 'فاتورة مبيعات',
                                doc_no: String(invNo),
                                date: s.date || '',
                                label: `فاتورة مبيعات رقم ${invNo}` + (s.date ? ` بتاريخ ${s.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 2. Purchase Invoices
            try {
                const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
                if (Array.isArray(purchases)) {
                    purchases.forEach(p => {
                        if (!p) return;
                        let matched = false;
                        if (entityType === 'warehouse') {
                            if (isMatch(p.warehouse) || isMatch(p.warehouseId)) matched = true;
                            if (Array.isArray(p.items)) {
                                p.items.forEach(it => { if (isMatch(it.warehouseId) || isMatch(it.warehouse)) matched = true; });
                            }
                        } else if (entityType === 'supplier') {
                            if (isMatch(p.supplier) || isMatch(p.supplierId) || isMatch(p.supplierName) || isMatch(p.customer)) matched = true;
                        } else if (entityType === 'bank') {
                            if (isMatch(p.paymentAccountId) || isMatch(p.bankId) || isMatch(p.paymentAccount) || isMatch(p.bank)) matched = true;
                        } else if (entityType === 'safe') {
                            if (isMatch(p.safe) || isMatch(p.safeId) || isMatch(p.paymentAccountId) || isMatch(p.cashBox)) matched = true;
                        } else if (entityType === 'item') {
                            if (Array.isArray(p.items)) {
                                p.items.forEach(it => {
                                    if (isMatch(it.itemId) || isMatch(it.id) || isMatch(it.itemName) || isMatch(it.name) || isMatch(it.barcode) || isMatch(it.qrCode)) matched = true;
                                });
                            }
                        } else if (entityType === 'unit') {
                            if (Array.isArray(p.items)) {
                                p.items.forEach(it => { if (isMatch(it.selectedUnit) || isMatch(it.unit)) matched = true; });
                            }
                        } else if (entityType === 'cost_center') {
                            if (isMatch(p.costCenter) || isMatch(p.costCenterId)) matched = true;
                        } else if (entityType === 'employee') {
                            if (isMatch(p.createdBy) || isMatch(p.user)) matched = true;
                        }

                        if (matched) {
                            const invNo = p.invoiceNo || p.id || '';
                            usages.push({
                                type: 'فاتورة مشتريات',
                                doc_no: String(invNo),
                                date: p.date || '',
                                label: `فاتورة مشتريات رقم ${invNo}` + (p.date ? ` بتاريخ ${p.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 3. Single Payments & Receipts
            try {
                const singlePay = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
                if (Array.isArray(singlePay)) {
                    singlePay.forEach(sp => {
                        if (!sp) return;
                        let matched = false;
                        if (['customer', 'supplier', 'bank', 'safe'].includes(entityType)) {
                            if (isMatch(sp.fromAccount) || isMatch(sp.toAccount) || isMatch(sp.accountId) || isMatch(sp.partyId)) matched = true;
                        } else if (entityType === 'cost_center' && (isMatch(sp.costCenter) || isMatch(sp.costCenterId))) {
                            matched = true;
                        } else if (entityType === 'employee' && (isMatch(sp.createdBy) || isMatch(sp.paidBy))) {
                            matched = true;
                        }
                        if (matched) {
                            const no = sp.id || sp.voucherNo || '';
                            usages.push({
                                type: 'سند صرف',
                                doc_no: String(no),
                                date: sp.date || '',
                                label: `سند صرف رقم ${no}` + (sp.date ? ` بتاريخ ${sp.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            try {
                const singleRec = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                if (Array.isArray(singleRec)) {
                    singleRec.forEach(sr => {
                        if (!sr) return;
                        let matched = false;
                        if (['customer', 'supplier', 'bank', 'safe'].includes(entityType)) {
                            if (isMatch(sr.fromAccount) || isMatch(sr.toAccount) || isMatch(sr.accountId) || isMatch(sr.partyId)) matched = true;
                        } else if (entityType === 'cost_center' && (isMatch(sr.costCenter) || isMatch(sr.costCenterId))) {
                            matched = true;
                        } else if (entityType === 'employee' && (isMatch(sr.createdBy) || isMatch(sr.receivedBy) || isMatch(sr.collectorName))) {
                            matched = true;
                        }
                        if (matched) {
                            const no = sr.id || sr.voucherNo || '';
                            usages.push({
                                type: 'سند قبض',
                                doc_no: String(no),
                                date: sr.date || '',
                                label: `سند قبض رقم ${no}` + (sr.date ? ` بتاريخ ${sr.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 4. Delivery Notes
            try {
                const dNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1')) || [];
                if (Array.isArray(dNotes)) {
                    dNotes.forEach(dn => {
                        if (!dn) return;
                        let matched = false;
                        if (entityType === 'warehouse' && (isMatch(dn.warehouse) || isMatch(dn.warehouseId) || isMatch(dn.targetWarehouse))) matched = true;
                        if (entityType === 'customer' && (isMatch(dn.customer) || isMatch(dn.customerId) || isMatch(dn.customerName))) matched = true;
                        if (entityType === 'employee' && (isMatch(dn.driver) || isMatch(dn.deliveredBy) || isMatch(dn.receivedBy) || isMatch(dn.createdBy))) matched = true;
                        if (entityType === 'item' && Array.isArray(dn.items)) {
                            dn.items.forEach(it => { if (isMatch(it.itemId) || isMatch(it.id) || isMatch(it.itemName) || isMatch(it.name)) matched = true; });
                        }
                        if (entityType === 'unit' && Array.isArray(dn.items)) {
                            dn.items.forEach(it => { if (isMatch(it.selectedUnit) || isMatch(it.unit)) matched = true; });
                        }
                        if (matched) {
                            const no = dn.noteNo || dn.id || '';
                            usages.push({
                                type: 'سند تسليم / إذن صرف',
                                doc_no: String(no),
                                date: dn.date || '',
                                label: `سند تسليم رقم ${no}` + (dn.date ? ` بتاريخ ${dn.date}` : '')
                            });
                        }
                    });
                }
            } catch(e) {}

            // 5. Items table for Unit & Category usage
            if (entityType === 'unit' || entityType === 'category') {
                try {
                    const items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                    if (Array.isArray(items)) {
                        items.forEach(it => {
                            if (!it || it.deleted) return;
                            if (entityType === 'unit') {
                                let matched = isMatch(it.baseUnit) || isMatch(it.unit);
                                if (Array.isArray(it.units)) {
                                    it.units.forEach(u => { if (u && isMatch(u.unitName)) matched = true; });
                                }
                                if (matched) {
                                    usages.push({
                                        type: 'بطاقة صنف',
                                        doc_no: String(it.id || it.name),
                                        date: '',
                                        label: `مستخدم كوحدة في بطاقة صنف: ${it.name || it.id}`
                                    });
                                }
                            } else if (entityType === 'category') {
                                if (isMatch(it.category) || isMatch(it.subCategory)) {
                                    usages.push({
                                        type: 'بطاقة صنف',
                                        doc_no: String(it.id || it.name),
                                        date: '',
                                        label: `مستخدم كفئة للصنف: ${it.name || it.id}`
                                    });
                                }
                            }
                        });
                    }
                } catch(e) {}
            }

            return usages;
        }

        function renderEntityDeleteModalResult(cfg, entity, isUsed, allUsages) {
            const iconWrap = document.getElementById('md-del-modal-icon-wrap') || document.getElementById('item-del-modal-icon-wrap');
            const iconEl = document.getElementById('md-del-modal-icon') || document.getElementById('item-del-modal-icon');
            const titleEl = document.getElementById('md-del-modal-title') || document.getElementById('item-del-modal-title');
            const subtitleEl = document.getElementById('md-del-modal-subtitle') || document.getElementById('item-del-modal-subtitle');
            const statusBox = document.getElementById('md-del-status-box') || document.getElementById('item-del-status-box');
            const vouchersContainer = document.getElementById('md-del-vouchers-container') || document.getElementById('item-del-vouchers-container');
            const vouchersList = document.getElementById('md-del-vouchers-list') || document.getElementById('item-del-vouchers-list');
            const vouchersCount = document.getElementById('md-del-vouchers-count') || document.getElementById('item-del-vouchers-count');
            const vouchersHeader = document.getElementById('md-del-vouchers-header-label');
            const footerEl = document.getElementById('md-del-modal-footer') || document.getElementById('item-del-modal-footer');

            const displayName = entity.name || entity.id || cfg.label;

            if (isUsed) {
                // STATE 1: USED IN VOUCHER -> PREVENT DELETION
                if (iconWrap) {
                    iconWrap.style.background = 'rgba(239, 68, 68, 0.18)';
                    iconWrap.style.borderColor = 'rgba(239, 68, 68, 0.45)';
                    iconWrap.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.35)';
                }
                if (iconEl) iconEl.innerText = '🚫';

                if (titleEl) {
                    titleEl.innerText = `لا يمكن حذف هذا ${cfg.label} نهائياً`;
                    titleEl.style.color = '#f87171';
                }
                if (subtitleEl) subtitleEl.innerText = `${cfg.label} مرتبط بسجلات وسندات وفواتير رسمية بالنظام`;

                if (statusBox) {
                    statusBox.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(185, 28, 28, 0.08))';
                    statusBox.style.borderColor = 'rgba(239, 68, 68, 0.35)';
                    statusBox.innerHTML = `
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <div style="display:flex; align-items:center; gap:8px; color:#fca5a5; font-weight:800; font-size:1.05rem;">
                                <span>⚠️</span>
                                <span>تنبيه نظام مقاييس المحاسبي:</span>
                            </div>
                            <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                                تم استخدام ${cfg.label} (<span style="color:#38bdf8; font-weight:800;">${displayName}</span>) في عدد <span style="color:#f87171; font-weight:900; font-size:1.15rem; background:rgba(239,68,68,0.2); padding:1px 8px; border-radius:6px;">${allUsages.length}</span> سند / حركة / سجل مسجل.
                            </p>
                            <div style="background:rgba(0,0,0,0.3); border-radius:10px; padding:10px 14px; margin-top:4px; border-right:3px solid #ef4444;">
                                <span style="color:#cbd5e1; font-size:0.88rem; font-weight:700;">
                                    🔒 طبقاً لمعايير المحاسبة والرقابة والضبط المالي الصارمة، <strong style="color:#f87171;">لا يمكن حذف أي ${cfg.label} له حركات أو سندات مرتبطة</strong> نهائياً، وذلك لحماية سلامة القوائم المالية، الحسابات، والتقارير الرسمية.
                                </span>
                            </div>
                        </div>
                    `;
                }

                // Render Vouchers List
                if (vouchersHeader) vouchersHeader.innerText = `📋 السندات والفواتير المرتبطة بهذا ${cfg.label}:`;
                if (vouchersCount) vouchersCount.innerText = `${allUsages.length} سند / سجل`;
                if (vouchersList) {
                    vouchersList.innerHTML = allUsages.map((u, idx) => `
                        <div style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:8px 12px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="color:#f87171; font-weight:bold; font-size:0.85rem;">${idx + 1}.</span>
                                <span style="color:#e2e8f0; font-weight:700; font-size:0.9rem;">${u.label || (u.type + ' رقم ' + u.doc_no)}</span>
                            </div>
                            <span style="background:rgba(239,68,68,0.15); color:#fca5a5; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:4px;">${u.type}</span>
                        </div>
                    `).join('');
                }
                if (vouchersContainer) vouchersContainer.style.display = 'block';

                // Footer with Close button only
                if (footerEl) {
                    footerEl.innerHTML = `
                        <button type="button" onclick="closeEntityDeleteModal()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #3b82f6, #1d4ed8); border:none; color:#fff; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(59,130,246,0.4);">حسناً، فهمت</button>
                    `;
                }

            } else {
                // STATE 2: NOT USED IN ANY VOUCHER -> ALLOW HARD DELETE
                if (iconWrap) {
                    iconWrap.style.background = 'rgba(245, 158, 11, 0.18)';
                    iconWrap.style.borderColor = 'rgba(245, 158, 11, 0.45)';
                    iconWrap.style.boxShadow = '0 0 25px rgba(245, 158, 11, 0.35)';
                }
                if (iconEl) iconEl.innerText = '⚠️';

                if (titleEl) {
                    titleEl.innerText = `تأكيد حذف ${cfg.label} نهائياً`;
                    titleEl.style.color = '#fbbf24';
                }
                if (subtitleEl) subtitleEl.innerText = `${cfg.label} غير مستخدم في أي سند أو حركة`;

                if (statusBox) {
                    statusBox.style.background = 'linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(239, 68, 68, 0.06))';
                    statusBox.style.borderColor = 'rgba(245, 158, 11, 0.3)';
                    statusBox.innerHTML = `
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div style="display:flex; align-items:center; gap:8px; color:#34d399; font-weight:800; font-size:1rem;">
                                <span>✅</span>
                                <span>تم التحقق: هذا ${cfg.label} غير مستخدم في أي سند أو فاتورة أو حركة.</span>
                            </div>
                            <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                                هل أنت متأكد من رغبتك في حذف ${cfg.label} (<span style="color:#38bdf8; font-weight:800;">${displayName}</span>)؟
                            </p>
                            <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.35); border-radius:10px; padding:12px 14px; margin-top:4px;">
                                <div style="display:flex; align-items:center; gap:8px; color:#f87171; font-weight:900; font-size:0.95rem; margin-bottom:4px;">
                                    <span>🚨</span>
                                    <span>تحذير نهائي لا يمكن الرجوع فيه:</span>
                                </div>
                                <span style="color:#fecaca; font-size:0.88rem; font-weight:700; line-height:1.5; display:block;">
                                    سيتم حذف ${cfg.label} <strong>نهائياً وبشكل دائم من قاعدة البيانات (MySQL) ومن المتصفح</strong>، ولن يمكن استرجاعه أو الرجوع فيه ثانية.
                                </span>
                            </div>
                        </div>
                    `;
                }

                if (vouchersContainer) vouchersContainer.style.display = 'none';

                // Footer with Confirm Delete and Cancel buttons
                if (footerEl) {
                    footerEl.innerHTML = `
                        <button type="button" onclick="closeEntityDeleteModal()" class="btn-action" style="padding:10px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:800; cursor:pointer;">إلغاء الأمر</button>
                        <button type="button" id="md-del-confirm-btn" onclick="confirmExecuteHardDeleteEntity()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #ef4444, #dc2626); border:1px solid rgba(239,68,68,0.5); color:#fff; font-weight:900; font-size:1rem; cursor:pointer; box-shadow:0 6px 20px rgba(239,68,68,0.4);">🗑️ نعم، احذف ${cfg.label} نهائياً الآن</button>
                    `;
                }
            }
        }

        async function genericDeleteEntity(entityType, id, customName = null) {
            const cfg = ENTITY_CONFIG[entityType];
            if (!cfg) return;

            let entity = { id: String(id), name: customName || String(id) };

            // Find entity details from localStorage
            try {
                const list = JSON.parse(localStorage.getItem(cfg.localKey)) || [];
                if (Array.isArray(list)) {
                    if (entityType === 'unit' || entityType === 'category') {
                        const found = list.find(u => String(u) === String(id));
                        if (found) entity = { id: found, name: found };
                    } else {
                        const found = list.find(item => item && (
                            String(item.id) === String(id) || 
                            String(item.frontend_id) === String(id) || 
                            String(item.name || item.title || item.username) === String(id)
                        ));
                        if (found) {
                            entity = {
                                ...found,
                                id: found.id || found.frontend_id || id,
                                name: found.name || found.fullName || found.title || found.username || customName || id
                            };
                        }
                    }
                }
            } catch(e) {}

            currentDeleteTarget = { entityType, entity, cfg };

            // 1. Open modal loading state
            openEntityDeleteModalLoading(cfg, entity);

            // 2. Scan local usages
            const localUsages = scanEntityLocalUsages(entityType, entity);

            // 3. Scan backend MySQL usages (with 5s timeout for 2G resilience)
            let backendUsages = [];
            const chkCtrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const chkTimer = chkCtrl ? setTimeout(() => chkCtrl.abort(), 5000) : null;
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const chkHeaders = { 'Content-Type': 'application/json' };
                if (token) chkHeaders['Authorization'] = 'Bearer ' + token;
                if (tenantId) chkHeaders['X-Tenant-Id'] = String(tenantId);

                const res = await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: chkHeaders,
                    body: JSON.stringify({
                        type: 'check_entity_usage',
                        data: {
                            entity_type: entityType,
                            id: entity.id,
                            name: entity.name,
                            barcode: entity.barcode || ''
                        }
                    }),
                    signal: chkCtrl ? chkCtrl.signal : undefined
                });
                if (chkTimer) clearTimeout(chkTimer);
                const resJson = await res.json();
                if (resJson && resJson.success) {
                    backendUsages = resJson.usages || [];
                }
            } catch(e) {
                if (chkTimer) clearTimeout(chkTimer);
                console.warn(`Backend ${entityType} usage check error (2G/Offline fallback to local scan):`, e);
            }

            // 4. Merge unique usages
            const allUsagesMap = new Map();
            [...localUsages, ...backendUsages].forEach(u => {
                const key = (u.type || '') + '_' + (u.doc_no || '') + '_' + (u.date || '');
                if (!allUsagesMap.has(key)) {
                    allUsagesMap.set(key, u);
                }
            });

            const allUsages = Array.from(allUsagesMap.values());
            const isUsed = allUsages.length > 0;

            renderEntityDeleteModalResult(cfg, entity, isUsed, allUsages);
        }

        function deleteItem(id) {
            genericDeleteEntity('item', id);
        }

        async function confirmExecuteHardDeleteEntity() {
            if (!currentDeleteTarget) return;
            const { entityType, entity, cfg } = currentDeleteTarget;

            const btn = document.getElementById('md-del-confirm-btn') || document.getElementById('item-del-confirm-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '⏳ جاري الحذف...';
            }

            const delPayload = {
                id: entity.id,
                name: entity.name,
                barcode: entity.barcode || ''
            };

            try {
                // 1. Send hard delete request to backend MySQL (with 5s timeout for 2G resilience)
                const delCtrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                const delTimer = delCtrl ? setTimeout(() => delCtrl.abort(), 5000) : null;
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                let tenantId = localStorage.getItem('currentTenantId') || '';
                if (!tenantId) {
                    try {
                        const u = JSON.parse(localStorage.getItem('user') || '{}');
                        if (u && u.tenant_id) tenantId = u.tenant_id;
                    } catch(e) {}
                }
                const delHeaders = { 'Content-Type': 'application/json' };
                if (token) delHeaders['Authorization'] = 'Bearer ' + token;
                if (tenantId) delHeaders['X-Tenant-Id'] = String(tenantId);

                try {
                    const res = await fetch('api/save_master_data.php', {
                        method: 'POST',
                        headers: delHeaders,
                        body: JSON.stringify({
                            type: cfg.backendType,
                            data: delPayload
                        }),
                        signal: delCtrl ? delCtrl.signal : undefined
                    });
                    if (delTimer) clearTimeout(delTimer);
                    const json = await res.json();
                    if (!json || (!json.success && json.error)) {
                        const errMsg = (json && json.error) ? json.error : 'فشل الحذف من قاعدة البيانات';
                        if (typeof showToast === 'function') showToast('🚫 خطأ: ' + errMsg, 'error', 2000);
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = `🗑️ نعم، احذف ${cfg.label}`;
                        }
                        return;
                    }
                } catch (netErr) {
                    if (delTimer) clearTimeout(delTimer);
                    enqueueMasterOfflineSync(cfg.backendType, delPayload);
                }

                // 2. Push snapshot to Undo Vault (Ctrl+Z — Constitution 7) before permanent removal
                if (typeof window.pushMdUndoSnapshot === 'function') {
                    window.pushMdUndoSnapshot({
                        kind: 'entity_delete',
                        entityType: entityType,
                        localKey: cfg.localKey,
                        label: cfg.label,
                        entity: JSON.parse(JSON.stringify(entity)),
                        deletedAt: Date.now()
                    });
                }

                // 3. Remove permanently from localStorage (Hard delete from array)
                try {
                    let list = JSON.parse(localStorage.getItem(cfg.localKey)) || [];
                    if (Array.isArray(list)) {
                        if (entityType === 'unit' || entityType === 'category') {
                            list = list.filter(u => String(u).trim() !== String(entity.name || entity.id).trim());
                        } else {
                            list = list.filter(item => {
                                if (!item) return false;
                                if (String(item.id) === String(entity.id)) return false;
                                if (item.frontend_id && String(item.frontend_id) === String(entity.id)) return false;
                                if (entity.name && item.name && item.name.trim() === entity.name.trim()) return false;
                                return true;
                            });
                        }
                        setMasterLocalData(cfg.localKey, list, true);
                    }
                } catch(e) {}

                // Handle secondary localStorage keys if any
                if (entityType === 'item') {
                    try {
                        let inv2 = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
                        if (Array.isArray(inv2)) {
                            inv2 = inv2.filter(iv => iv && String(iv.id) !== String(entity.id) && String(iv.itemId) !== String(entity.id) && String(iv.name) !== String(entity.name));
                            setMasterLocalData('erp_inventory_v1', inv2, false);
                        }
                    } catch(e) {}
                } else if (entityType === 'cost_center') {
                    try {
                        let ccs2 = JSON.parse(localStorage.getItem('erp_cost_centers_v1')) || [];
                        if (Array.isArray(ccs2)) {
                            ccs2 = ccs2.filter(c => c && String(c.id) !== String(entity.id) && String(c.name) !== String(entity.name));
                            setMasterLocalData('erp_cost_centers_v1', ccs2);
                        }
                    } catch(e) {}
                } else if (entityType === 'employee') {
                    try {
                        let emps2 = JSON.parse(localStorage.getItem('erp_employees_v1')) || [];
                        if (Array.isArray(emps2)) {
                            emps2 = emps2.filter(e => e && String(e.id) !== String(entity.id) && String(e.name) !== String(entity.name));
                            setMasterLocalData('erp_employees_v1', emps2);
                        }
                    } catch(e) {}
                }

                // 3. Close modal & re-render
                logMasterDataAudit('حذف نهائي', cfg.labelPlural || cfg.label, entity.name || entity.id, `تم الحذف النهائي بعد التحقق من عدم وجود حركات`);
                closeEntityDeleteModal();
                cfg.render();

                const successMsg = `✅ تم حذف ${cfg.label} (${entity.name || entity.id}) نهائياً بنجاح`;
                if (typeof showToast === 'function') {
                    showToast(successMsg, 'success', 2000);
                }
            } catch(err) {
                console.error(`Error hard deleting ${entityType}:`, err);
                if (typeof showToast === 'function') showToast(`🚫 خطأ أثناء حذف ${cfg.label}: ` + err.message, 'error', 2000);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `🗑️ نعم، احذف ${cfg.label} نهائياً الآن`;
                }
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
        const DEFAULT_STANDARD_UNITS = [
            'طن', 'طن وزني', 'طن عددي', 'طن العددي',
            'سيخه', 'سيخة / عود', 'كيس', 'حبة', 'ألف حبة',
            'متر مكعب', 'متر مربع', 'متر طولي',
            'كرتونة', 'كرتونة (4 جالون)', 'درزن', 'جالون',
            'لتر', 'كيلو', 'قطعة', 'عددي', 'قلاب 12متر'
        ];

        function sanitizeGlobalUnits() {
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
                if (!Array.isArray(units)) units = [];
            } catch(e) { units = []; }

            // 1. Purge corrupted question mark strings
            let cleaned = units.filter(u => {
                if (!u || typeof u !== 'string') return false;
                let trimmed = u.trim();
                if (trimmed === '' || trimmed.includes('?')) return false;
                return true;
            });

            // 2. Ensure standard defaults are present
            DEFAULT_STANDARD_UNITS.forEach(def => {
                if (!cleaned.includes(def)) {
                    cleaned.push(def);
                }
            });

            // 3. Clean any corrupted units in erp_items_v1
            try {
                let items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                let itemsModified = false;
                items.forEach(item => {
                    if (item.baseUnit && typeof item.baseUnit === 'string' && item.baseUnit.includes('?')) {
                        item.baseUnit = 'حبة';
                        itemsModified = true;
                    }
                    if (item.unit && typeof item.unit === 'string' && item.unit.includes('?')) {
                        item.unit = 'حبة';
                        itemsModified = true;
                    }
                    if (Array.isArray(item.units)) {
                        item.units = item.units.filter(u => u && (!u.unitName || !u.unitName.includes('?')));
                        itemsModified = true;
                    }
                    if (Array.isArray(item.unitsArray)) {
                        item.unitsArray = item.unitsArray.filter(u => u && (!u.name || !u.name.includes('?')));
                        itemsModified = true;
                    }
                });
                if (itemsModified) {
                    localStorage.setItem('erp_items_v1', JSON.stringify(items));
                }
            } catch(e) {}

            localStorage.setItem('erp_global_units_v1', JSON.stringify(cleaned));
            return cleaned;
        }

        function saveGlobalUnit() {
            const input = document.getElementById('unit-name-input');
            const name = input ? input.value.trim() : '';
            if(!name) { return focusRequiredMasterField('unit-name-input', '⚠️ الرجاء إدخال اسم الوحدة'); }
            if(name.includes('?')) { return focusRequiredMasterField('unit-name-input', '⚠️ اسم الوحدة يحتوي على رموز غير صالحة'); }
            
            let units = sanitizeGlobalUnits();
            if(!units.includes(name)) { 
                units.push(name); 
                setMasterLocalData('erp_global_units_v1', units); 
                syncToBackend('units', units);
                if (typeof logMasterDataAudit === 'function') logMasterDataAudit('إضافة وحدة قياس', name, 'وحدات القياس');
            }
            if (input) input.value = '';
            renderGlobalUnitsList();
            updateUnitDropdowns();
            if (typeof showToast === 'function') showToast('✅ تم حفظ الوحدة بنجاح', 'success', 2000);
        }

        function editGlobalUnit(oldName) {
            window.openQuickMasterPromptModal({
                title: '✏️ تعديل اسم الوحدة',
                defaultValue: oldName,
                placeholder: 'أدخل الاسم الجديد للوحدة...',
                onConfirm: function(cleanNew) {
                    if (!cleanNew || cleanNew === oldName) return;
                    if (cleanNew.includes('?')) {
                        if (typeof showToast === 'function') showToast('⚠️ الاسم يحتوي على رموز غير صالحة', 'error', 2000);
                        return;
                    }
                    let units = sanitizeGlobalUnits();
                    const idx = units.indexOf(oldName);
                    if(idx > -1) {
                        units[idx] = cleanNew;
                        setMasterLocalData('erp_global_units_v1', units);
                        syncToBackend('units', units);
                        if (typeof logMasterDataAudit === 'function') logMasterDataAudit('تعديل وحدة قياس', `${oldName} ⬅️ ${cleanNew}`, 'وحدات القياس');
                    }

                    try {
                        let items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                        let itemsChanged = false;
                        items.forEach(item => {
                            if(item.baseUnit === oldName) { item.baseUnit = cleanNew; itemsChanged = true; }
                            if(item.unit === oldName) { item.unit = cleanNew; itemsChanged = true; }
                            if(Array.isArray(item.units)) {
                                item.units.forEach(u => { if(u.unitName === oldName) { u.unitName = cleanNew; itemsChanged = true; } });
                            }
                            if(Array.isArray(item.unitsArray)) {
                                item.unitsArray.forEach(u => { if(u.name === oldName) { u.name = cleanNew; itemsChanged = true; } });
                            }
                        });
                        if (itemsChanged) {
                            setMasterLocalData('erp_items_v1', items);
                            syncToBackend('items_bulk', items);
                        }
                    } catch(e) {}

                    renderGlobalUnitsList();
                    updateUnitDropdowns();
                    if (typeof renderItems === 'function') renderItems();
                    if (typeof showToast === 'function') showToast('✅ تم تعديل الوحدة وتحديث الأصناف المرتبطة', 'success', 2000);
                }
            });
        }

        function deleteGlobalUnit(name) {
            genericDeleteEntity('unit', name, name);
        }

        function cleanAndResetUnits() {
            if(confirm('هل تريد تنظيف وإزالة كافة الوحدات التالفة والرموز غير الصالحة واستعادة الوحدات القياسية؟')) {
                localStorage.removeItem('erp_global_units_v1');
                const cleanU = sanitizeGlobalUnits();
                syncToBackend('units', cleanU);
                renderGlobalUnitsList();
                updateUnitDropdowns();
                if (typeof showToast === 'function') showToast('✅ تم تنظيف وإصلاح مصفوفة الوحدات بنجاح', 'success', 2000);
            }
        }

        function renderGlobalUnitsList() {
            const tbody = document.getElementById('units-table-body');
            if(!tbody) return;
            let units = sanitizeGlobalUnits();
            tbody.innerHTML = '';
            units.forEach(u => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="font-weight:bold; font-size:1.1rem; color:#38bdf8; padding:12px;">${u}</td>
                    <td style="text-align:center; padding:12px; white-space:nowrap;">
                        <button class="btn-text" style="color:#38bdf8; margin-left:12px; font-weight:bold;" onclick="editGlobalUnit('${u}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); font-weight:bold;" onclick="deleteGlobalUnit('${u}')">🗑️ حذف</button>
                    </td>
                </tr>`);
            });
        }

        function importLegacyUnits() {
            // Import units that were manually typed in items without importing corrupted '?' strings
            let items = [];
            try { items = JSON.parse(localStorage.getItem('erp_items_v1')) || []; } catch(e) {}
            if(items.length === 0) return;
            let units = sanitizeGlobalUnits();
            let added = false;
            items.forEach(i => {
                if(i.baseUnit && typeof i.baseUnit === 'string' && !i.baseUnit.includes('?') && !units.includes(i.baseUnit.trim())) { 
                    units.push(i.baseUnit.trim()); 
                    added = true; 
                }
                if(i.units && Array.isArray(i.units)) {
                    i.units.forEach(u => {
                        if(u.unitName && typeof u.unitName === 'string' && !u.unitName.includes('?') && !units.includes(u.unitName.trim())) { 
                            units.push(u.unitName.trim()); 
                            added = true; 
                        }
                    });
                }
            });
            if(added) {
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            }
        }

        function updateUnitDropdowns(selectedValue = '') {
            let units = sanitizeGlobalUnits();
            
            const options = units.map(u => `<option value="${u}">${u}</option>`).join('');
            const baseSelect = document.getElementById('item-base-unit');
            if(baseSelect) {
                const current = selectedValue || baseSelect.value;
                baseSelect.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                if(current && units.includes(current)) baseSelect.value = current;
            }
            
            // Also update any dynamic selects in the matrix
            document.querySelectorAll('.unit-name-input').forEach(sel => {
                if(sel.tagName === 'SELECT') {
                    const current = sel.value;
                    sel.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                    if(current && units.includes(current)) sel.value = current;
                }
            });
        }

        // ================= CATEGORY LOGIC =================
        function saveCategory() {
            const name = (document.getElementById('cat-name-input')?.value || '').trim();
            if(!name) { return focusRequiredMasterField('cat-name-input', '⚠️ أدخل اسم الفئة'); }
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) {
                cats.push(name);
                setMasterLocalData('erp_categories_v1', cats);
                syncToBackend('categories', cats);
                if (typeof logMasterDataAudit === 'function') logMasterDataAudit('إضافة فئة أصناف', name, 'الفئات');
            }
            document.getElementById('cat-name-input').value = '';
            renderCategoriesList();
            updateCategoryDropdowns();
            if (typeof showToast === 'function') showToast('✅ تم حفظ الفئة بنجاح', 'success', 2000);
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
            window.openQuickMasterPromptModal({
                title: '✏️ تعديل اسم الفئة',
                defaultValue: oldName,
                placeholder: 'أدخل الاسم الجديد للفئة...',
                onConfirm: function(cleanNew) {
                    if(!cleanNew || cleanNew === oldName) return;
                    let cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || [];
                    const idx = cats.indexOf(oldName);
                    if(idx > -1) {
                        cats[idx] = cleanNew;
                        setMasterLocalData('erp_categories_v1', cats);
                    }
                    let items = [];
                    try {
                        items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                        if (!Array.isArray(items)) items = [];
                    } catch(e) {
                        items = [];
                    }
                    items = items.filter(i => !i.deleted);
                    items.forEach(i => { if(i.category === oldName) i.category = cleanNew; });
                    setMasterLocalData('erp_items_v1', items);
                    syncToBackend('categories', cats);
                    syncToBackend('items_bulk', items);
                    if (typeof logMasterDataAudit === 'function') logMasterDataAudit('تعديل فئة أصناف', `${oldName} ⬅️ ${cleanNew}`, 'الفئات');
                    renderCategoriesList();
                    updateCategoryDropdowns();
                    renderItems();
                    if (typeof showToast === 'function') showToast('✅ تم تعديل الفئة وتحديث الأصناف بنجاح', 'success', 2000);
                }
            });
        }

        function deleteCategory(name) {
            genericDeleteEntity('category', name, name);
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
            const name = document.getElementById('costcenter-name').value.trim();
            const desc = document.getElementById('costcenter-desc').value.trim();
            
            if(!name) { return focusRequiredMasterField('costcenter-name', '⚠️ الرجاء إدخال اسم مركز التكلفة'); }
            if(isDuplicateName('erp_cost_centers_v1', name, id)) { return focusRequiredMasterField('costcenter-name', '⚠️ اسم مركز التكلفة موجود مسبقاً، يرجى اختيار اسم آخر'); }

            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_cost_centers_v1') || localStorage.getItem('erp_jobs_v1') || '[]');
                if (!Array.isArray(ccs)) ccs = [];
            } catch(e) { ccs = []; }
            
            const existingIdx = ccs.findIndex(c => c.id === id);
            
            const ccData = {
                id, code: id, name, title: name, desc,
                createdAt: Date.now()
            };

            if (existingIdx > -1) {
                ccs[existingIdx] = { ...ccs[existingIdx], ...ccData };
            } else {
                ccs.push(ccData);
            }

            setMasterLocalData('erp_jobs_v1', ccs);
            setMasterLocalData('erp_cost_centers_v1', ccs);
            syncToBackend('cost_centers', ccs);
            logMasterDataAudit(existingIdx > -1 ? 'تعديل' : 'إضافة', 'مراكز التكلفة', name, `الكود: ${id}`);
            closeCostCenterForm();
            renderCostCenters();
            if (typeof window.triggerMasterSavePulse === 'function') window.triggerMasterSavePulse('#tab-costcenters .md-card');
            if (typeof showToast === 'function') showToast('✅ تم حفظ مركز التكلفة بنجاح', 'success', 2000);
        }

        function renderCostCenters() {
            const tbody = document.getElementById('costcenters-tbody');
            if(!tbody) return;
            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_cost_centers_v1') || localStorage.getItem('erp_jobs_v1') || '[]');
                if (!Array.isArray(ccs)) ccs = [];
            } catch(e) { ccs = []; }
            ccs = ccs.filter(c => c && !c.deleted);
            const ccQuery = window.normalizeArabicFuzzyText(document.getElementById('costcenter-search-input')?.value || '');
            if (ccQuery) {
                ccs = ccs.filter(c => window.normalizeArabicFuzzyText(`${c.id || ''} ${c.name || ''} ${c.title || ''} ${c.desc || ''}`).includes(ccQuery));
            }
            
            tbody.innerHTML = '';
            ccs.forEach(c => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="color:#38bdf8; font-weight:bold;">${c.id}</td>
                    <td style="font-weight:bold;">${c.name || c.title || ''}</td>
                    <td>${c.desc || ''}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn-text" style="color:#34d399; font-weight:800; margin-left:6px;" onclick="window.openCostCenterDrillDownModal('${c.id}')" title="كشف حركات ومصروفات وإيرادات مركز التكلفة">📊 كشف المركز</button>
                        <button class="btn-text" style="color:#fbbf24; font-weight:800; margin-left:6px;" onclick="window.location.href='financial-reports.php'" title="التقارير المالية الموحدة">📈 التقارير</button>
                        <button class="btn-text" style="color:#38bdf8;" onclick="editCostCenter('${c.id}')">✏️ تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:8px;" onclick="deleteCostCenter('${c.id}')">🗑️ حذف</button>
                    </td>
                </tr>`);
            });
        }

        window.openItemPriceHistoryModal = function(itemId) {
            let items = [];
            let hist = [];
            try { items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]'); } catch(e) {}
            try { hist = JSON.parse(localStorage.getItem('erp_item_price_history_v1') || '[]'); } catch(e) {}
            const itm = items.find(i => String(i.id) === String(itemId));
            const itemHist = hist.filter(h => h && String(h.itemId) === String(itemId));
            let modal = document.getElementById('md-item-price-history-modal');
            if (!modal) {
                document.body.insertAdjacentHTML('beforeend', `
                    <div id="md-item-price-history-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.84); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                        <div class="md-child-modal-card" style="background:#0f172a; border:1.5px solid rgba(56,189,248,0.45); border-radius:16px; width:100%; max-width:620px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.85);">
                            <div style="padding:14px 18px; background:rgba(56,189,248,0.14); border-bottom:1px solid rgba(56,189,248,0.3); display:flex; justify-content:space-between; align-items:center;">
                                <h3 id="md-price-hist-title" style="margin:0; color:#38bdf8; font-size:1.05rem; font-weight:900;">📜 سجل تاريخ الأسعار</h3>
                                <button type="button" onclick="document.getElementById('md-item-price-history-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 12px; font-weight:800; cursor:pointer;">✖</button>
                            </div>
                            <div id="md-price-hist-body" style="padding:16px 18px; max-height:60vh; overflow-y:auto; color:#f8fafc; font-size:0.88rem;"></div>
                        </div>
                    </div>
                `);
                modal = document.getElementById('md-item-price-history-modal');
            }
            const titleEl = document.getElementById('md-price-hist-title');
            const bodyEl = document.getElementById('md-price-hist-body');
            if (titleEl) titleEl.textContent = `📜 سجل تاريخ أسعار: ${itm ? itm.name : itemId}`;
            if (bodyEl) {
                if (itemHist.length === 0) {
                    const curCost = parseFloat(itm?.costPrice || itm?.cost || itm?.openingValue || 0);
                    const curSale = parseFloat(itm?.sellingPrice || itm?.price || 0);
                    bodyEl.innerHTML = `<div style="padding:12px; text-align:center; color:#cbd5e1;">السعر الحالي — التكلفة: <b style="color:#fbbf24;">${curCost.toLocaleString()}</b> | البيع: <b style="color:#34d399;">${curSale.toLocaleString()}</b></div>`;
                } else {
                    bodyEl.innerHTML = `<table style="width:100%; border-collapse:collapse; text-align:right;">
                        <thead><tr style="background:rgba(255,255,255,0.06); color:#38bdf8;"><th style="padding:8px;">التاريخ</th><th style="padding:8px;">المصدر</th><th style="padding:8px;">التكلفة</th><th style="padding:8px;">سعر البيع</th></tr></thead>
                        <tbody>${itemHist.map(h => `<tr>
                            <td style="padding:7px; border-bottom:1px solid rgba(255,255,255,0.06); direction:ltr; font-family:monospace;">${new Date(h.date).toLocaleString('ar-EG-u-nu-latn')}</td>
                            <td style="padding:7px; border-bottom:1px solid rgba(255,255,255,0.06);">${h.source || '-'}</td>
                            <td style="padding:7px; border-bottom:1px solid rgba(255,255,255,0.06); color:#fbbf24; font-weight:800;">${(parseFloat(h.costPrice || h.lastPurchaseCost) || 0).toLocaleString()}</td>
                            <td style="padding:7px; border-bottom:1px solid rgba(255,255,255,0.06); color:#34d399; font-weight:800;">${(parseFloat(h.sellingPrice) || 0).toLocaleString()}</td>
                        </tr>`).join('')}</tbody>
                    </table>`;
                }
            }
            modal.style.display = 'flex';
        };

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
            genericDeleteEntity('cost_center', id);
        }

        // ================= CURRENCIES LOGIC =================
        function getCurrencyRates() {
            let rates = {};
            try {
                rates = JSON.parse(localStorage.getItem('erp_currency_rates_v1')) || {};
            } catch(e) {}
            
            let manualUsd = 0;
            try {
                const sysSettings = JSON.parse(localStorage.getItem('erp_settings_v1')) || {};
                manualUsd = parseFloat(sysSettings.rateUSD || sysSettings.usd_rate || sysSettings.usdRate || sysSettings.exchangeRate || 0);
            } catch(e){}

            if (!manualUsd && typeof window.getExchangeRateForCurrency === 'function') {
                const sysUsd = window.getExchangeRateForCurrency('USD');
                if (sysUsd && sysUsd > 1) manualUsd = sysUsd;
            }

            if (!manualUsd) {
                manualUsd = parseFloat(localStorage.getItem('erp_rate_usd') || 0);
            }

            const defaults = {
                'SDG': 1,
                'USD': manualUsd > 0 ? manualUsd : 600,
                'SAR': 160,
                'AED': 163,
                'EGP': 12
            };
            
            for (let cur in defaults) {
                if (rates[cur] === undefined || (cur === 'USD' && manualUsd > 0)) {
                    rates[cur] = defaults[cur];
                }
            }
            return rates;
        }

        function populateSystemCurrencySelects() {
            const selects = document.querySelectorAll('.system-currency-select');
            
            const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
            const baseCur = cfg.currency || 'SDG';
            const foreignCur = cfg.foreignCurrency || 'USD';
            
            const currencyLabels = {
                'SDG': 'SDG - جنيه سوداني (محلي)',
                'USD': 'USD - دولار أمريكي',
                'SAR': 'SAR - ريال سعودي',
                'AED': 'AED - درهم إماراتي',
                'EGP': 'EGP - جنيه مصري',
                'QAR': 'QAR - ريال قطري'
            };
            
            selects.forEach(select => {
                const currentVal = select.value;
                select.innerHTML = '';
                
                const optBase = document.createElement('option');
                optBase.value = baseCur;
                optBase.textContent = currencyLabels[baseCur] || baseCur;
                select.appendChild(optBase);
                
                if (foreignCur && foreignCur !== baseCur) {
                    const optForeign = document.createElement('option');
                    optForeign.value = foreignCur;
                    optForeign.textContent = currencyLabels[foreignCur] || foreignCur;
                    select.appendChild(optForeign);
                }
                
                if (currentVal && (currentVal === baseCur || currentVal === foreignCur)) {
                    select.value = currentVal;
                } else {
                    select.value = baseCur;
                }
            });
        }

        function updateExchangeRateField(selectId, inputId, groupId, labelId) {
            const curSelect = document.getElementById(selectId);
            const exInput = document.getElementById(inputId);
            const exGroup = document.getElementById(groupId);
            const exLabel = document.getElementById(labelId);
            if (!curSelect || !exInput) return;
            
            const cur = curSelect.value || 'SDG';
            const rates = getCurrencyRates();
            const defaultRate = rates[cur] || 1;
            const isProCard = exGroup && exGroup.classList.contains('pro-field-card');
            
            if (cur !== 'SDG' && cur !== '') {
                if (exGroup) exGroup.style.display = isProCard ? 'flex' : 'flex';
                if (exLabel) {
                    exLabel.innerHTML = `<span>💵 سعر الصرف (1 ${cur})</span>`;
                }
                if (!exInput.value || exInput.value == 1 || exInput.value == 600) {
                    exInput.value = defaultRate;
                }
                if (groupId === 'cust-ex-rate-group') {
                    const notesCard = document.getElementById('cust-notes-card');
                    if (notesCard) notesCard.style.gridColumn = 'span 2';
                } else if (groupId === 'sup-ex-rate-group') {
                    const notesCard = document.getElementById('sup-notes-card');
                    if (notesCard) notesCard.style.gridColumn = 'span 1';
                }
            } else {
                if (exGroup) exGroup.style.display = 'none';
                exInput.value = 1;
                if (groupId === 'cust-ex-rate-group') {
                    const notesCard = document.getElementById('cust-notes-card');
                    if (notesCard) notesCard.style.gridColumn = '1 / -1';
                } else if (groupId === 'sup-ex-rate-group') {
                    const notesCard = document.getElementById('sup-notes-card');
                    if (notesCard) notesCard.style.gridColumn = '1 / -1';
                }
            }
        }

        function updateCustExchangeRate() {
            updateExchangeRateField('cust-currency', 'cust-ex-rate', 'cust-ex-rate-group', 'cust-ex-rate-label');
        }

        function updateSupExchangeRate() {
            updateExchangeRateField('sup-currency', 'sup-ex-rate', 'sup-ex-rate-group', 'sup-ex-rate-label');
        }

        function updateBankExchangeRate() {
            updateExchangeRateField('bank-currency', 'bank-ex-rate', 'bank-ex-rate-group', 'bank-ex-rate-label');
        }

        function updateSafeExchangeRate() {
            updateExchangeRateField('safe-currency', 'safe-ex-rate', 'safe-ex-rate-group', 'safe-ex-rate-label');
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                populateSystemCurrencySelects();
                updateCustExchangeRate();
                updateSupExchangeRate();
                updateBankExchangeRate();
                updateSafeExchangeRate();
            }, 100);
        });

        function goBackOrExit() {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                window.history.back();
            } else {
                window.location.href = 'index.php';
            }
        }
function deduplicateEntities() {
    try {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        let suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
        if(!Array.isArray(customers)) customers = [];
        if(!Array.isArray(suppliers)) suppliers = [];
        
        let uniqueCustNames = new Set();
        let newCusts = customers.filter(c => {
            if(!c || !c.name) return false;
            if(c.deleted) return true;
            let n = c.name.trim().toLowerCase();
            if(uniqueCustNames.has(n)) return false;
            uniqueCustNames.add(n);
            return true;
        });
        
        let uniqueSupNames = new Set();
        let newSups = suppliers.filter(s => {
            if(!s || !s.name) return false;
            if(s.deleted) return true;
            let n = s.name.trim().toLowerCase();
            if(uniqueSupNames.has(n)) return false;
            uniqueSupNames.add(n);
            return true;
        });
        
        if (newCusts.length !== customers.length) setMasterLocalData('erp_customers_v1', newCusts, false);
        if (newSups.length !== suppliers.length) setMasterLocalData('erp_suppliers_v1', newSups, false);
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', () => {
    deduplicateEntities();
    // Units Init
    importLegacyUnits();
    renderGlobalUnitsList();
    updateUnitDropdowns();
    flushMasterOfflineSyncQueue();
    
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

// ════════════════════════════════════════════════════════════════════════════
// 👑 1. DOUBLE-CLICK PREVENTION & ATOMIC BUTTON EXECUTION LOCK
// ════════════════════════════════════════════════════════════════════════════
(function initMasterDataDoubleClickGuard() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        const onclickAttr = (btn.getAttribute('onclick') || '').trim();
        const isCriticalBtn = btn.classList.contains('btn-add') ||
            /^(save|delete|bulk|export|trigger|cleanAndReset)/.test(onclickAttr);
        if (!isCriticalBtn) return;

        if (btn.getAttribute('data-md-busy') === '1') {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
        btn.setAttribute('data-md-busy', '1');
        setTimeout(() => {
            btn.removeAttribute('data-md-busy');
        }, 650);
    }, true);
})();

// ════════════════════════════════════════════════════════════════════════════
// 👑 2. SILKY-SMOOTH CURSOR, ENTER/F10/F2/ESC NAVIGATION, AUTO-SELECT & LIVE NUMBER FORMATTER
// ════════════════════════════════════════════════════════════════════════════
(function initMasterDataCursorAndKeyboardEngine() {
    window.__mdFormDirty = false;

    function isMasterNumericInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        return (
            el.type === 'number' ||
            el.inputMode === 'decimal' ||
            el.inputMode === 'numeric' ||
            el.classList.contains('pro-num-input') ||
            /price|balance|limit|qty|value|rate|factor|discount|weight|days|pin|phone|whatsapp/i.test(el.id || '')
        );
    }

    // 1. Auto-select ANY existing numeric value on focus or mouse click for instant overwrite without Backspace
    document.addEventListener('focusin', function (e) {
        const el = e.target;
        if (isMasterNumericInput(el) && (el.value || '').trim() !== '') {
            el.dataset.mdJustFocused = '1';
            setTimeout(() => { try { el.select(); } catch (err) {} }, 10);
        }
    });
    document.addEventListener('mouseup', function (e) {
        const el = e.target;
        if (isMasterNumericInput(el) && el.dataset.mdJustFocused === '1') {
            delete el.dataset.mdJustFocused;
            if ((el.value || '').trim() !== '') {
                e.preventDefault();
                try { el.select(); } catch (err) {}
            }
        }
    });

    // 2. Live thousands-separator formatting badge + Dirty-form tracking
    document.addEventListener('input', function (e) {
        const el = e.target;
        if (!el || !el.closest('.md-content-area')) return;
        if (el.closest('#warehouse-form, #customer-form, #supplier-form, #bank-form, #safe-form, #item-form, #employee-form, #costcenter-form')) {
            window.__mdFormDirty = true;
        }
        if (el.tagName === 'INPUT' && (el.type === 'number' || /price|balance|limit|qty|value|rate/i.test(el.id || ''))) {
            let badge = el.parentElement ? el.parentElement.querySelector('.md-num-live-badge') : null;
            const rawVal = parseFloat(String(el.value || '').replace(/,/g, ''));
            if (!isNaN(rawVal) && Math.abs(rawVal) >= 1000) {
                if (!badge && el.parentElement) {
                    badge = document.createElement('div');
                    badge.className = 'md-num-live-badge';
                    el.parentElement.appendChild(badge);
                }
                if (badge) {
                    badge.textContent = '🔢 ' + rawVal.toLocaleString('en-US');
                    badge.style.display = 'inline-block';
                }
            } else if (badge) {
                badge.style.display = 'none';
            }
        }
    });

    // Protect unsaved changes before leaving page
    window.addEventListener('beforeunload', function (e) {
        if (window.__mdFormDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Clear dirty flag whenever any save or cancel button is clicked
    document.addEventListener('click', function (e) {
        const btn = e.target && e.target.closest ? e.target.closest('button') : null;
        if (!btn) return;
        const oc = btn.getAttribute('onclick') || '';
        if (/save|close|clear|cancel/i.test(oc)) {
            window.__mdFormDirty = false;
        }
    }, true);

    // 3. Keyboard Shortcuts: F10 (Save), F2 (New/Add), Ctrl+K (Search Palette), Alt+N (Quick Add), Escape (Close), Enter / Arrows (Smooth Cursor)
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            if (typeof window.openMdQuickJumpPaletteModal === 'function') {
                e.preventDefault();
                window.openMdQuickJumpPaletteModal();
                return;
            }
        }
        if (e.altKey && (e.key === 'n' || e.key === 'N' || e.key === 'ى')) {
            if (typeof window.openUniversalQuickEntityModal === 'function') {
                e.preventDefault();
                window.openUniversalQuickEntityModal();
                return;
            }
        }
        if (e.key === 'Escape') {
            for (const dynId of ['md-universal-quick-add-modal', 'md-quick-jump-palette-modal', 'md-c1-health-scanner-modal', 'md-c1-vault-modal', 'md-c1-officer-modal']) {
                const dynEl = document.getElementById(dynId);
                if (dynEl) { dynEl.remove(); return; }
            }
            const quickModal = document.getElementById('md-quick-prompt-modal');
            if (quickModal && quickModal.style.display !== 'none') {
                quickModal.style.display = 'none';
                return;
            }
            const delModal = document.getElementById('master-data-delete-modal');
            if (delModal && delModal.style.display !== 'none') {
                if (typeof closeEntityDeleteModal === 'function') closeEntityDeleteModal();
                return;
            }
            for (const mId of ['md-origin-voucher-modal', 'md-entity-drilldown-modal', 'md-print-share-hub-modal', 'md-excel-preimport-modal', 'md-audit-trail-modal', 'md-opening-eq-modal', 'md-item-price-history-modal', 'md-bulk-price-modal', 'md-entity-qr-card-modal', 'md-clipboard-paste-modal']) {
                const mEl = document.getElementById(mId);
                if (mEl && mEl.style.display !== 'none') {
                    mEl.style.display = 'none';
                    return;
                }
            }
            const camModal = document.getElementById('md-webrtc-barcode-modal');
            if (camModal && camModal.style.display !== 'none') {
                if (typeof window.closeWebRtcBarcodeScanner === 'function') window.closeWebRtcBarcodeScanner();
                else camModal.style.display = 'none';
                return;
            }
            // Close active open child form if any
            const openFormCloseMap = [
                ['warehouse-form', 'closeWarehouseForm'],
                ['customer-form', 'closeCustomerForm'],
                ['supplier-form', 'closeSupplierForm'],
                ['bank-form', 'closeBankForm'],
                ['safe-form', 'closeSafeForm'],
                ['item-form', 'closeItemForm'],
                ['employee-form', 'closeEmployeeForm'],
                ['costcenter-form', 'closeCostCenterForm']
            ];
            for (const [fId, fnName] of openFormCloseMap) {
                const fEl = document.getElementById(fId);
                if (fEl && fEl.style.display !== 'none' && typeof window[fnName] === 'function') {
                    window[fnName]();
                    window.__mdFormDirty = false;
                    return;
                }
            }
            return;
        }

        // F10 or Ctrl+S / Ctrl+Enter saves the active open form
        if (e.key === 'F10' || ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'Enter'))) {
            const target = e.target;
            const activeTab = document.querySelector('.md-content-area .md-tab-content.active, .md-content-area .tab-section.active');
            const container = (target && target.closest ? target.closest('.md-child-modal-card, .md-c1-modal-card, #warehouse-form, #customer-form, #supplier-form, #bank-form, #safe-form, #item-form, #employee-form, #costcenter-form, #inner-categories, #inner-units') : null) || activeTab;
            if (container) {
                const saveBtns = Array.from(container.querySelectorAll('button[onclick*="save"], #md-uqa-save-btn, #md-quick-prompt-ok')).filter(b => b.offsetParent !== null);
                if (saveBtns.length > 0) {
                    e.preventDefault();
                    window.__mdFormDirty = false;
                    saveBtns[0].click();
                    return;
                }
            }
        }

        // F2 opens/resets the Add Form in the active tab
        if (e.key === 'F2') {
            const activeTab = document.querySelector('.md-content-area .md-tab-content.active, .md-content-area .tab-section.active');
            if (activeTab) {
                e.preventDefault();
                const tabId = activeTab.id;
                if (tabId === 'tab-warehouses' && typeof openWarehouseForm === 'function') openWarehouseForm();
                else if (tabId === 'tab-customers' && typeof clearCustomerForm === 'function') { clearCustomerForm(); document.getElementById('cust-name')?.focus(); }
                else if (tabId === 'tab-suppliers' && typeof clearSupplierForm === 'function') { clearSupplierForm(); document.getElementById('sup-name')?.focus(); }
                else if (tabId === 'tab-banks' && typeof openBankForm === 'function') openBankForm();
                else if (tabId === 'tab-safes' && typeof openSafeForm === 'function') openSafeForm();
                else if (tabId === 'tab-items' && typeof openItemForm === 'function') openItemForm();
                else if (tabId === 'tab-employees' && typeof openNewEmployeeForm === 'function') openNewEmployeeForm();
                else if (tabId === 'tab-costcenters' && typeof openCostCenterForm === 'function') openCostCenterForm();
                return;
            }
        }

        const target = e.target;
        if (!target || (!target.closest('.md-content-area') && !target.closest('.md-child-modal') && !target.closest('.md-c1-modal-card'))) return;

        // Smooth Enter / ArrowDown / ArrowUp cursor flow from field to field inside forms & child modals
        const isArrowMove = (e.key === 'ArrowDown' || e.key === 'ArrowUp') && (e.altKey || (target.tagName === 'INPUT' && target.type !== 'number'));
        const isNavKey = (e.key === 'Enter' || isArrowMove);
        if (isNavKey && (target.tagName === 'INPUT' || target.tagName === 'SELECT') && target.type !== 'file' && target.type !== 'button' && target.type !== 'submit') {
            if (target.id && target.id.includes('search')) return;
            const formBox = target.closest('.md-child-modal-card, .md-c1-modal-card, #warehouse-form, #customer-form, #supplier-form, #bank-form, #safe-form, #item-form, #employee-form, #costcenter-form, #inner-categories, #inner-units');
            if (!formBox) return;

            e.preventDefault();
            const focusables = Array.from(formBox.querySelectorAll('input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled])')).filter(el => {
                return el.offsetParent !== null && getComputedStyle(el).visibility !== 'hidden';
            });
            const idx = focusables.indexOf(target);
            const step = (e.key === 'ArrowUp') ? -1 : 1;
            const nextIdx = idx + step;
            if (idx > -1 && nextIdx >= 0 && nextIdx < focusables.length) {
                const nextEl = focusables[nextIdx];
                nextEl.focus();
                try { nextEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (err) {}
                if (nextEl.tagName === 'INPUT' && nextEl.type !== 'checkbox') {
                    try { nextEl.select(); } catch (err) {}
                }
            } else if (idx === focusables.length - 1 && step === 1) {
                const saveBtn = formBox.querySelector('button[onclick*="save"], #md-uqa-save-btn, #md-quick-prompt-ok');
                if (saveBtn) saveBtn.focus();
            }
        }
    });

    // Auto-focus first field whenever a form becomes visible
    if (typeof MutationObserver !== 'undefined') {
        const formIds = ['warehouse-form', 'customer-form', 'supplier-form', 'bank-form', 'safe-form', 'item-form', 'employee-form', 'costcenter-form'];
        const obs = new MutationObserver((mutations) => {
            for (const m of mutations) {
                if (m.type === 'attributes' && m.attributeName === 'style') {
                    const el = m.target;
                    if (el && formIds.includes(el.id) && el.style.display !== 'none') {
                        setTimeout(() => {
                            const firstInput = el.querySelector('input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled])');
                            if (firstInput && firstInput.offsetParent !== null) {
                                firstInput.focus();
                            }
                        }, 60);
                    }
                }
            }
        });
        document.addEventListener('DOMContentLoaded', () => {
            formIds.forEach(fid => {
                const fEl = document.getElementById(fid);
                if (fEl) obs.observe(fEl, { attributes: true, attributeFilter: ['style'] });
            });
        });
    }
})();

// ════════════════════════════════════════════════════════════════════════════
// 👑 3. ISOLATED MASTER DATA SMART GUIDE («مرشد شاشة البيانات الأساسية المعزول»)
// ════════════════════════════════════════════════════════════════════════════
(function registerIsolatedMasterDataGuide() {
    window.MagaiesSmartGuides = window.MagaiesSmartGuides || {};

    const SALAWAT = 'اللهم صل وسلم على نبينا محمد.';

    function closeAnyGuideChildModals() {
        [
            'md-opening-eq-modal', 'md-bulk-price-modal', 'md-clipboard-paste-modal',
            'md-entity-qr-modal', 'md-audit-log-modal', 'md-c2-quick-add-modal',
            'md-c2-omni-search-modal', 'md-c1-scanner-modal', 'md-c1-vault-modal',
            'md-c3-whatsapp-modal', 'md-c4-impact-map-modal', 'md-c1-officer-modal',
            'md-c5-role-sim-modal', 'md-c5-golden-catalog-modal'
        ].forEach(id => {
            const m = document.getElementById(id);
            if (m) m.style.display = 'none';
        });
    }

    window.MagaiesSmartGuides['master-data'] = {
        title: 'المرشد الذكي الشامل: شاشة البيانات الأساسية (8 شاشات متداخلة)',
        subtitle: 'شرح مصوّر ومدبلج بالصوت الرجالي الفخيم يوضح الملخص العام والمدخلات والمخرجات والقاعدة الذهبية ثم يفتح كل قسم وشاشة وليدة بالترتيب',
        screenHierarchy: [
            'الشاشة الرئيسية: البيانات الأساسية (8 أقسام مترابطة)',
            '0️⃣ الملخص العام (المدخلات والمخرجات والقاعدة الذهبية) ⬅ 1️⃣ المخازن ⬅ 2️⃣ العملاء',
            '3️⃣ الموردين ⬅ 4️⃣ البنوك ⬅ 5️⃣ الخزن ⬅ 6️⃣ الأصناف والوحدات ⬅ 7️⃣ الموظفين ⬅ 8️⃣ مراكز التكلفة'
        ],
        autopilotSteps: [
            {
                badge: '0. الملخص العام الشامل: وظيفة شاشة البيانات الأساسية، مصادر مدخلاتها، أين تُسمّع وتؤثر، والقاعدة الذهبية',
                cursorLabel: 'الملخص العام وخريطة الترابط والقاعدة الذهبية 🏛️📊',
                screenPath: 'الملخص العام ⬅ من أين تأتي المعلومة (المدخلات) ⬅ عمل الشاشة والقاعدة الذهبية ⬅ أين تُسمّع وتؤثر في التقارير والشاشات',
                targetSelector: '#md-c1-command-bar, .md-tabs-sidebar',
                diagramType: 'c5-impact-map',
                c5ImpactMeta: {
                    screenTitle: 'شاشة البيانات الأساسية الموحدة (المخازن • العملاء • الموردين • البنوك • الخزن • الأصناف • الموظفين • مراكز التكلفة)',
                    inputs: ['📥 دفاتر الجرد وأرصدة أول المدة التأسيسية', '📋 استيراد ولصق ذكي من Excel والواتساب', '📷 كاميرا الباركود والإضافة السريعة الفورية'],
                    funcSummary: 'تأسيس البطاقات التعريفية الثمانية وشرائح الأسعار والوحدات وضبط توازن القيد الافتتاحي عبر حساب التسوية 3109',
                    goldenRule: 'أسِّس بطاقاتك ووحدات أصنافك وأرصدتك الافتتاحية بدقة مرة واحدة، وتأكد من توازن القيد الافتتاحي (حساب 3109)، ينضبط لك النظام المحاسبي والمخزني بالكامل تلقائياً.',
                    reassurance: 'اطمئن تماماً يا أخي المحاسب ولا تخشَ الخطأ؛ فالشاشة محمية بدرع الأمان الذكي الذي يمنع حذف أي حساب مرتبط بحركات سابقة، ويحفظ مسوداتك تلقائياً، ويتيح لك التراجع الفوري بضغطة زر.',
                    reports: ['📊 ميزان المراجعة ودفتر الأستاذ العام (3109)', '📖 كشوفات حسابات العملاء والموردين وأعمار الديون', '📦 تقارير جرد وتقييم المخزون وحركة الصندوق والبنوك'],
                    screens: ['🧾 فواتير المبيعات المباشرة وتجزئة الفواتير', '🛒 فواتير المشتريات وبوابة اللوجستيات (GRN/GDN)', '🏦 سندات القبض والصرف وتطبيق الموبايل والواتساب']
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-warehouses');
                    if (b) { try { b.click(); } catch (e) {} }
                },
                speech: () => `${SALAWAT} هذه شاشة البيانات الأساسية الموحدة؛ وهي حجر الأساس للمنظومة المحاسبية والمخزنية، وظيفتها وفائدتها تأسيس البطاقات التعريفية للأقسام الثمانية: المخازن، العملاء، الموردين، البنوك، الخزن، الأصناف ووحداتها، الموظفين، ومراكز التكلفة لمرة واحدة فقط لتعمل بها كل الفواتير والسندات تلقائياً. وتأتي المعلومات إلى هذه الصفحة من دفاتر الجرد التأسيسي وأرصدة أول المدة، أو عبر الاستيراد واللصق الذكي من ملفات إكسل والواتساب، أو مسح الباركود بالكاميرا. وبمجرد الحفظ، تُسمّع هذه الشاشة فوراً في ميزان المراجعة، ودفتر الأستاذ العام، وحساب تسوية الأرصدة الافتتاحية ثلاثة آلاف ومئة وتسعة، وكشوفات حسابات العملاء والموردين، وتقارير الجرد وتقييم المخزون، وحركة الصندوق والبنوك؛ حيث تزيد أرصدة الأصول والمخزون والمدينين أو تزيد أرصدة الدائنين بدقة متوازنة، كما تؤثر لحظياً في شاشات المبيعات وتجزئة الفواتير والمشتريات وبوابة اللوجستيات وسندات القبض والصرف وتطبيق الموبايل. والقاعدة الذهبية لهذه الشاشة هي: أسِّس بطاقاتك ووحدات أصنافك وأرصدتك الافتتاحية بدقة مرة واحدة، وتأكد من توازن القيد الافتتاحي عبر حساب ثلاثة آلاف ومئة وتسعة، ينضبط لك النظام المحاسبي والمخزني بالكامل تلقائياً. واطمئن تماماً يا أخي المحاسب ولا تخشَ الخطأ؛ فالشاشة محمية بدرع الأمان الذكي الذي يمنع الحذف العشوائي لأي حساب مرتبط بحركات، ويحفظ مسوداتك تلقائياً، ويتيح لك التراجع الفوري بضغطة زر.`
            },
            {
                badge: 'الخطوة 1 — شاشة المخازن وفتح بطاقة المستودع والقوائم المنسدلة',
                cursorLabel: 'بطاقة المخازن وقائمة النوع 🏭',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (المخازن) ⬅ فتح بطاقة إضافة مخزن وقائمة النوع والحالة',
                targetSelector: '#warehouse-type, #warehouse-form, .tab-warehouses',
                diagramType: 'dropdown-open',
                diagramMeta: {
                    parentScreen: 'شاشة المخازن (#tab-warehouses)',
                    actionLabel: 'فتح بطاقة المخزن وقوائم النوع والحالة:',
                    childItems: ['🏭 اسم المخزن والنوع (مستودع رئيسي • معرض • مخزن فرعي)', '👷‍♂️ أمين المخزن المسؤول ورقم الهاتف والعنوان', '🟢 حالة المخزن (نشط • جرد • موقوف) ودرجة السرية (1–10)', '⚡ يظهر فوراً في قوائم المشتريات والمبيعات وبوابة الاستلام والجرد'],
                    effectLabel: 'بطاقة المخزن وقائمتها المنسدلة مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-warehouses');
                    if (b) { try { b.click(); } catch (e) {} }
                    if (typeof openWarehouseForm === 'function') {
                        try { openWarehouseForm(); } catch (e) {}
                    }
                    const sel = document.getElementById('warehouse-type');
                    if (sel) { try { sel.focus(); } catch (e) {} }
                },
                speech: () => `نبدأ بالقسم الأول وهو المخازن؛ وكما يظهر أمامك الآن عند فتح بطاقة المخزن تسجل اسم المستودع، وتختار نوعه من القائمة المنسدلة المفتوحة أمامك مستودعاً رئيسياً أو معرضاً، وتحدد أمين المخزن وحالة المستودع ودرجة السرية، ليظهر فوراً في جميع الفواتير وتقارير الجرد. والقاعدة الذهبية للمخازن هي: افصل دائماً بين المستودع الرئيسي ومعرض البيع ومخزن التوالف، وحوّل حالة المخزن إلى جرد أثناء الجرد الفعلي لمنع أي تداخل في الأرصدة.`
            },
            {
                badge: 'الخطوة 2 — شاشة العملاء وسياسة الواتساب والسقف الائتماني',
                cursorLabel: 'بطاقة العملاء وشرائح التسعير 🤝',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (العملاء) ⬅ فتح سياسة الواتساب وبطاقة العميل وشرائح التسعير',
                targetSelector: '#cust-price-tier, #customer-form, .tab-customers',
                diagramType: 'dropdown-open',
                diagramMeta: {
                    parentScreen: 'شاشة العملاء (#tab-customers)',
                    actionLabel: 'فتح بطاقة العميل ولوحة سياسة الواتساب:',
                    childItems: ['👤 اسم العميل والرقم السري (3 أرقام) ورقم الواتساب', '💰 الرصيد الافتتاحي (موجب عليه • سالب له) وحد الائتمان', '🏷️ قائمة شريحة التسعير (نقدي • آجل • أقل سعر) والعملة', '👑 القاعدة الذهبية: حدد سقف الائتمان والرقم السري لكل عميل آجل'],
                    effectLabel: 'شاشة العملاء وسياسة الواتساب مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-customers');
                    if (b) { try { b.click(); } catch (e) {} }
                    const drawer = document.querySelector('#admin-wa-policy-panel-cust .admin-wa-policy-drawer');
                    if (drawer) drawer.style.display = 'block';
                    const tierSel = document.getElementById('cust-price-tier');
                    if (tierSel) { try { tierSel.focus(); } catch (e) {} }
                },
                speech: () => `القسم الثاني هو العملاء: وكما ترى أمامك على الشاشة تفتح لوحة سياسة الواتساب وبطاقة العميل لتسجيل اسم العميل ورقمه السري المكون من ثلاثة أرقام ورقم الواتساب، مع تحديد الرصيد الافتتاحي والسقف الائتماني واختيار شريحة التسعير من القائمة المنسدلة، ليُسمّع في نفس اللحظة في المبيعات وكشف الحساب وتطبيق الموبايل. والقاعدة الذهبية لبطاقة العميل هي: اكتب الرصيد الافتتاحي بالموجب إذا كان على العميل دين وبالسالب إذا كان له رصيد، وحدد له سقف ائتمان واضحاً ليحميك النظام تلقائياً من تجاوز المديونية.`
            },
            {
                badge: 'الخطوة 3 — شاشة الموردين وأرصدة وحسابات المشتريات',
                cursorLabel: 'بطاقة الموردين وقائمة العملة 🚛',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (الموردين) ⬅ فتح بطاقة المورد والعملة وفترة السداد',
                targetSelector: '#sup-currency, #supplier-form, .tab-suppliers',
                diagramType: 'dropdown-open',
                diagramMeta: {
                    parentScreen: 'شاشة الموردين (#tab-suppliers)',
                    actionLabel: 'بطاقة المورد والقوائم المرتبطة بها:',
                    childItems: ['🏭 اسم المورد أو الشركة والرقم السري والواتساب', '💵 الرصيد الافتتاحي وسقف الائتمان وفترة السداد بالأيام', '💱 قائمة العملة وسعر الصرف ودرجة السرية', '👑 القاعدة الذهبية: اربط كل مورد بعملة التعامل وفترة السداد الدقيقة'],
                    effectLabel: 'شاشة الموردين وقائمة العملة مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-suppliers');
                    if (b) { try { b.click(); } catch (e) {} }
                    const supCur = document.getElementById('sup-currency');
                    if (supCur) { try { supCur.focus(); } catch (e) {} }
                },
                speech: () => `القسم الثالث هو الموردين: وتظهر أمامك بطاقة المورد لتسجيل الشركات والمصانع التي تشتري منها البضاعة، وتحديد الرصيد الافتتاحي واختيار عملة التعامل من القائمة المنسدلة وفترة السداد وأرقام المناديب، ليرتبط المورد تلقائياً بفواتير المشتريات وسندات الصرف وكشف الحساب. والقاعدة الذهبية للموردين هي: ثبّت عملة حساب المورد وفترة السداد بالأيام بدقة، لينبهك النظام بمواعيد استحقاق الفواتير وفروق العملة قبل السداد.`
            },
            {
                badge: 'الخطوة 4 — شاشة البنوك وفتح بطاقة الحساب المصرفي',
                cursorLabel: 'بطاقة البنوك وقائمة العملة 🏦',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (البنوك) ⬅ فتح بطاقة إضافة بنك وطرق الدفع',
                targetSelector: '#bank-currency, #bank-form, .tab-banks',
                diagramType: 'dropdown-open',
                diagramMeta: {
                    parentScreen: 'شاشة البنوك (#tab-banks)',
                    actionLabel: 'فتح بطاقة الحساب البنكي (#bank-form):',
                    childItems: ['🏦 اسم البنك (بنكك • فوري • أوكاش) والفرع ورقم الحساب', '💰 الرصيد الافتتاحي وتاريخ الرصيد والعملة وسعر الصرف', '💳 طرق الدفع المتاحة (كاش • شبكة • تحويل • شيكات)', '👑 القاعدة الذهبية: سجّل رقم الحساب البنكي بدقة لمطابقة إشعارات الواتساب آلياً'],
                    effectLabel: 'بطاقة البنك وقائمة العملة مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-banks');
                    if (b) { try { b.click(); } catch (e) {} }
                    if (typeof openBankForm === 'function') {
                        try { openBankForm(); } catch (e) {}
                    }
                    const bankCur = document.getElementById('bank-currency');
                    if (bankCur) { try { bankCur.focus(); } catch (e) {} }
                },
                speech: () => `القسم الرابع هو البنوك: وقد فتحنا أمامك الآن بطاقة إضافة حساب بنكي؛ وفيها تسجل اسم البنك مثل بنك الخرطوم بنكك أو فوري، ورقم الحساب والآيبان والرصيد الافتتاحي وتختار العملة من القائمة المنسدلة وطرق الدفع، ليظهر الحساب مباشرة في الفواتير والسندات ودرج إشعارات بنكك. والقاعدة الذهبية للبنوك هي: أدخل رقم الحساب المصرفي المطابق لإشعارات التحويل البنكي، ليقوم محرك الذكاء الاصطناعي بفرز ومطابقة إشعارات الواتساب الواردة على هذا البنك تلقائياً.`
            },
            {
                badge: 'الخطوة 5 — شاشة الخزن النقدية وفتح بطاقة الخزينة',
                cursorLabel: 'بطاقة الخزن وقائمة المسؤول 🗄️',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (الخزن) ⬅ فتح بطاقة إضافة خزينة وتحديد المسؤول والحدود',
                targetSelector: '#safe-responsible, #safe-form, .tab-safes',
                diagramType: 'dropdown-open',
                diagramMeta: {
                    parentScreen: 'شاشة الخزن النقدية (#tab-safes)',
                    actionLabel: 'فتح بطاقة الخزينة النقدية (#safe-form):',
                    childItems: ['🗄️ اسم الخزينة (الخزينة الرئيسية • خزينة الكاشير • خزينة الدولار)', '💵 الرصيد الافتتاحي وتاريخ الرصيد ورقم حساب الأستاذ', '👨‍💼 قائمة الموظف المسؤول عن الخزينة والحد الأعلى والأدنى', '👑 القاعدة الذهبية: اربط كل خزينة بالموظف المسؤول وحد السيولة الآمن'],
                    effectLabel: 'بطاقة الخزينة النقدية وقائمة المسؤول مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-safes');
                    if (b) { try { b.click(); } catch (e) {} }
                    if (typeof openSafeForm === 'function') {
                        try { openSafeForm(); } catch (e) {}
                    }
                    const safeResp = document.getElementById('safe-responsible');
                    if (safeResp) { try { safeResp.focus(); } catch (e) {} }
                },
                speech: () => `القسم الخامس هو الخزن النقدية: وتظهر أمامك الآن بطاقة الخزينة لتسجيل الخزينة الرئيسية أو الفرعية، وتحديد رصيدها الافتتاحي واختيار الموظف المسؤول عنها من القائمة المنسدلة والحد الأعلى والأدنى للسيولة وعملة الخزينة. والقاعدة الذهبية للخزن هي: خصص لكل كاشير أو عملة خزينة مستقلة واربطها بالموظف المسؤول عنها، لضمان دقة الجرد اليومي للصندوق ومنع اختلاط العهد النقدية.`
            },
            {
                badge: 'الخطوة 6 — شاشة الأصناف وفتح بطاقة الصنف الشاملة ومصفوفة الوحدات',
                cursorLabel: 'بطاقة الصنف وقائمة الوحدات 📦',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (الأصناف) ⬅ فتح بطاقة الصنف الشاملة وجدول الوحدات والتسعير',
                targetSelector: '#item-base-unit, #item-form, .tab-items',
                diagramType: 'dropdown-open',
                diagramMeta: {
                    parentScreen: 'شاشة الأصناف والوحدات (#tab-items)',
                    actionLabel: 'فتح بطاقة الصنف الشاملة (#item-form):',
                    childItems: ['📋 اسم الصنف والكود والباركود بالماسح الضوئي للكاميرا والفئة', '💰 سعر البيع وأقصى خصم والضريبة وطريقة حساب التكلفة', '🏭 الكمية الافتتاحية وتكلفة الوحدة وحدود المخزون (الأعلى • الأدنى • حد الطلب)', '👑 القاعدة الذهبية: اضبط الوحدة الأساسية ومعامل التحويل وأقل سعر بيع بدقة'],
                    effectLabel: 'بطاقة الصنف ومصفوفة الوحدات مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-items');
                    if (b) { try { b.click(); } catch (e) {} }
                    if (typeof openItemForm === 'function') {
                        try { openItemForm(); } catch (e) {}
                    }
                    const unitSel = document.getElementById('item-base-unit');
                    if (unitSel) { try { unitSel.focus(); } catch (e) {} }
                },
                speech: () => `القسم السادس هو الأصناف: وقد فتحت أمامك الآن بطاقة الصنف الشاملة؛ وفيها تعرف اسم الصنف والباركود بالماسح الضوئي للكاميرا، وتختار الفئة والوحدة الأساسية من القائمة المنسدلة، وتحدد سعر البيع والكمية الافتتاحية وتكلفة الوحدة، وفي الأسفل تضبط مصفوفة الوحدات كالطن والسيخة أو الكرتون والحبة ومعامل التحويل بينهما. والقاعدة الذهبية للأصناف هي: اجعل الوحدة الصغرى أو القياسية هي الوحدة الأساسية واضبط معامل التحويل للوحدات الكبرى وحدد أقل سعر بيع، ليحسب النظام الكميات والأرباح ويمنع البيع بالخسارة تلقائياً.`
            },
            {
                badge: 'الخطوة 7 — شاشة الموظفين والصلاحيات ودرجات السرية',
                cursorLabel: 'بطاقة الموظفين وقائمة الأدوار 👥',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (الموظفين) ⬅ فتح بطاقة الموظف وقائمة الدور والسرية',
                targetSelector: '#emp-role, #employee-form, .tab-employees',
                diagramType: 'dropdown-open',
                diagramMeta: {
                    parentScreen: 'شاشة الموظفين (#tab-employees)',
                    actionLabel: 'فتح بطاقة الموظف وقوائم الصلاحيات:',
                    childItems: ['👤 اسم الموظف ورقم الهاتف وكلمة المرور', '🛡️ قائمة الدور الوظيفي (مدير • محاسب • كاشير • مبيعات • أمين مخزن)', '🔐 قائمة درجة السرية من 1 إلى 10 لحجب الحسابات الحساسة', '👑 القاعدة الذهبية: امنح كل موظف الدور ودرجة السرية بقدر مهامه الفعلية فقط'],
                    effectLabel: 'بطاقة الموظف وقوائم الصلاحيات مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-employees');
                    if (b) { try { b.click(); } catch (e) {} }
                    if (typeof openNewEmployeeForm === 'function') {
                        try { openNewEmployeeForm(); } catch (e) {}
                    }
                    const roleSel = document.getElementById('emp-role');
                    if (roleSel) { try { roleSel.focus(); } catch (e) {} }
                },
                speech: () => `القسم السابع هو الموظفين: وتظهر أمامك بطاقة الموظف لتسجيل اسمه وكلمة المرور، واختيار دوره الوظيفي من القائمة المنسدلة المفتوحة أمامك مديراً أو محاسباً أو كاشيراً أو أمين مخزن، وتحديد درجة السرية المسموحة له من واحد إلى عشرة. والقاعدة الذهبية لصلاحيات الموظفين هي: امنح كل مستخدم الدور الوظيفي ودرجة السرية على قدر مسؤوليته فقط، ليخفي النظام عنه تلقائياً الحسابات وتكاليف الشراء والأزرار غير المصرح له بها.`
            },
            {
                badge: 'الخطوة 8 — شاشة مراكز التكلفة وفتح الشاشة الوليدة للقيد الافتتاحي المتوازن (3109)',
                cursorLabel: 'مراكز التكلفة والشاشة الوليدة للقيد الافتتاحي ⚖️',
                screenPath: 'البيانات الأساسية ⬅ ضغط تبويب (مركز التكلفة) ⬅ فتح الشاشة الوليدة للقيد الافتتاحي (حساب 3109)',
                targetSelector: '#md-opening-eq-modal > div, #costcenter-form, .tab-costcenters',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'شاشة مراكز التكلفة والشاشات الوليدة المنبثقة',
                    actionLabel: 'فتح الشاشة الوليدة لتوازن القيد الافتتاحي (حساب 3109):',
                    childItems: ['📊 إضافة مركز تكلفة (مشروع • فرع • شاحنة) لقياس صافي ربحه', '⚖️ الشاشة الوليدة (القيد الافتتاحي): توازن الأرصدة آلياً عبر حساب 3109', '📈 مطابقة فورية لإجمالي الأصول والخصوم وحقوق الملكية', '👑 القاعدة الذهبية: اعتمد القيد الافتتاحي بعد إتمام إدخال كافة أرصدة أول المدة'],
                    effectLabel: 'الشاشة الوليدة للقيد الافتتاحي مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    const b = document.querySelector('.tab-costcenters');
                    if (b) { try { b.click(); } catch (e) {} }
                    if (typeof openCostCenterForm === 'function') {
                        try { openCostCenterForm(); } catch (e) {}
                    }
                    if (typeof window.openOpeningEquilibriumModal === 'function') {
                        try { window.openOpeningEquilibriumModal(); } catch (e) {}
                    }
                },
                speech: () => `القسم الثامن هو مراكز التكلفة لتأسيس المشاريع والفروع؛ وكما فتحنا أمامك الآن الشاشة الوليدة للقيد الافتتاحي المتوازن، يجمع النظام تلقائياً الأرصدة الافتتاحية للعملاء والموردين والبنوك والخزن والمخزون ويوازنها آلياً عبر حساب تسوية الأرصدة الافتتاحية ثلاثة آلاف ومئة وتسعة. والقاعدة الذهبية للقيد الافتتاحي هي: راجع هذه الشاشة الوليدة فور انتهائك من إدخال أرصدة أول المدة للتأكد من تطابق المدين والدائن بنسبة مئة بالمئة في ميزان المراجعة.`
            },
            {
                badge: 'الخطوة 9 — المحاكاة الحية للشاشات الوليدة: الإضافة السريعة (+) وتحديث الأسعار الجماعي وفاحص التكرارات',
                cursorLabel: 'الشاشة الوليدة للإضافة السريعة وتحديث الأسعار ⚡',
                screenPath: 'شريط الإجراءات الموحد ⬅ فتح الشاشة الوليدة للإضافة السريعة الشاملة (+) وتحديث الأسعار الجماعي',
                targetSelector: '#md-c2-quick-add-modal .md-child-modal-card, #btn-md-c2-quick-add',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'الشاشات الوليدة الذكية بشريط الإجراءات',
                    actionLabel: 'فتح الشاشة الوليدة للإضافة السريعة الشاملة (+):',
                    childItems: ['➕ شاشة الإضافة السريعة (Alt+N): إضافة عميل أو مورد أو صنف أو مخزن أو بنك فوراً', '📈 شاشة تحديث الأسعار الجماعي: تعديل أسعار فئة كاملة بنسبة أو مبلغ ثابت', '📋 شاشة اللصق الذكي من Excel والواتساب وفاحص التكرارات والنواقص', '👑 القاعدة الذهبية: استخدم الإضافة السريعة واللصق الجماعي لتوفير 90% من وقت الإدخال'],
                    effectLabel: 'الشاشة الوليدة للإضافة السريعة مفتوحة الآن أمامك!'
                },
                action: () => {
                    closeAnyGuideChildModals();
                    if (typeof window.openUniversalQuickEntityModal === 'function') {
                        try { window.openUniversalQuickEntityModal('customer'); } catch (e) {}
                    }
                },
                speech: () => `وفي الخطوة التاسعة نفتح أمامك حياً الشاشة الوليدة للإضافة السريعة الشاملة؛ والتي تتيح لك بضغطة واحدة أو باختصار ألت مع إن إضافة عميل أو مورد أو صنف أو مخزن أو بنك في ثوانٍ دون ضياع أي بيانات مكتوبة في البطاقة الحالية، وبجانبها شاشات تحديث الأسعار الجماعي واللصق الذكي من إكسل وفاحص التكرارات. والقاعدة الذهبية للشاشات الوليدة هي: استخدم الإضافة السريعة أثناء العمل اليومي لتغذية القوائم المنسدلة فوراً دون مغادرة شاشتك، وافحص التكرارات دورياً لضمان نقاء قاعدة البيانات.`
            }
        ],
        visualSteps: [
            {
                stepBadge: '1️⃣ ما هي شاشة البيانات الأساسية؟ وكيف تعمل؟',
                title: 'حجر الأساس المحاسبي والمخزني والقاعدة الذهبية',
                desc: 'هذه الشاشة هي نقطة البداية لأي محاسب أو مستخدم جديد: تسجل فيها البطاقات التعريفية (المخازن، العملاء، الموردين، البنوك، الخزن، الأصناف، الموظفين، مراكز التكلفة) لمرة واحدة فقط، وبعدها تصبح متاحة تلقائياً في كل الفواتير والسندات والتقارير والموبايل دون إعادة كتابتها.',
                svg: `<svg viewBox="0 0 360 115" width="100%" height="100%"><rect x="10" y="12" width="340" height="90" rx="14" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/><text x="180" y="45" fill="#fef08a" font-size="14" font-weight="900" text-anchor="middle">🌐 8 أقسام تأسيسية مترابطة 100%</text><text x="180" y="75" fill="#34d399" font-size="12" font-weight="bold" text-anchor="middle">المخازن • العملاء • الموردين • البنوك • الخزن • الأصناف</text></svg>`
            },
            {
                stepBadge: '2️⃣ شرح كل خانة (حقل) بالتفصيل المبسط',
                title: 'وظيفة الحقول الحساسة: الرصيد الافتتاحي • الوحدات • الباسورد • السرية',
                desc: '• الرصيد الافتتاحي: هو المبلغ أو الكمية الموجودة قبل بدء العمل بالبرنامج (في العملاء: موجب يعني على العميل دين، وسالب يعني للعميل رصيد دائن).\n• الوحدة الأساسية والفرعية: مثل (طن = 95 سيخة) أو (كرتونة = 12 قطعة) ليبيع الكاشير بأي وحدة ويخصم البرنامج بدقة.\n• الباسورد السري (3 أرقام): يتيح للعميل أو المورد إرسال الرقم بالواتساب ليصله كشف حسابه أو رصيده آلياً.\n• درجة السرية (1–10): تحجب الحسابات الحساسة عن الموظفين ذوي الصلاحيات الأقل.',
                svg: `<svg viewBox="0 0 360 115" width="100%" height="100%"><rect x="10" y="12" width="340" height="90" rx="14" fill="#064e3b" stroke="#10b981" stroke-width="2"/><text x="180" y="45" fill="#ffffff" font-size="14" font-weight="900" text-anchor="middle">📝 انتقال سلس بزر Enter بين جميع الخانات</text><text x="180" y="75" fill="#a7f3d0" font-size="12" font-weight="bold" text-anchor="middle">Ctrl + S للحفظ الفوري • Esc للإغلاق • حماية من التكرار</text></svg>`
            },
            {
                stepBadge: '3️⃣ شرح كافة الأزرار والحماية من الحذف العشوائي',
                title: 'ماذا يفعل كل زر؟ وكيف يحميك النظام من الأخطاء؟',
                desc: '• زر (+ إضافة جديد): يفتح البطاقة ويضع المؤشر تلقائياً في أول خانة.\n• زر (💾 حفظ): ينفذ حفظاً ذرياً (Atomic) في قاعدة البيانات ويمنع النقرات المزدوجة.\n• زر (🗑️ حذف): لا يحذف عشوائياً أبداً! بل يفحص أولاً جميع الفواتير والسندات؛ فإذا كان العنصر مستخدماً يعرض لك قائمة السندات المرتبطة به ويمنع حذفه لحماية توازن الميزانية.',
                svg: `<svg viewBox="0 0 360 115" width="100%" height="100%"><rect x="10" y="12" width="340" height="90" rx="14" fill="#31102f" stroke="#c084fc" stroke-width="2"/><text x="180" y="45" fill="#f5d0fe" font-size="14" font-weight="900" text-anchor="middle">🛡️ درع الحماية المحاسبي ضد الحذف العشوائي</text><text x="180" y="75" fill="#fbbf24" font-size="12" font-weight="bold" text-anchor="middle">فحص آلي للسندات والفواتير قبل السماح بأي حذف</text></svg>`
            },
            {
                stepBadge: '4️⃣ مسار العمل والتسميع الفوري (التقارير + الموبايل)',
                title: 'أين تُسمّع وتؤثر بيانات هذه الشاشة؟',
                desc: 'بمجرد ضغط (حفظ) في أي قسم:\n1) تسمع فوراً في شاشات المبيعات والمشتريات والسندات وبوابة اللوجستيات.\n2) تسمع الأرصدة الافتتاحية فوراً في كشف الحساب، دفتر الأستاذ العام، وميزان المراجعة.\n3) تتزامن لحظياً مع تطبيق المحاسبة في الموبايل (Mobile ERP) ومع بوت الواتساب الآلي.',
                svg: `<svg viewBox="0 0 360 115" width="100%" height="100%"><rect x="10" y="12" width="340" height="90" rx="14" fill="#1e1b4b" stroke="#fbbf24" stroke-width="2"/><text x="180" y="45" fill="#fde047" font-size="14" font-weight="900" text-anchor="middle">⚡ تسميع لحظي 100% في التقارير وتطبيق الموبايل</text><text x="180" y="75" fill="#38bdf8" font-size="12" font-weight="bold" text-anchor="middle">طابور مزامنة ذكي يعمل حتى عند ضعف أو انقطاع الإنترنت</text></svg>`
            }
        ],
        buttonsDict: [
            {
                label: '🏢 تبويب المخازن والمستودعات',
                style: 'background:#0f172a; color:#38bdf8; border:1px solid #38bdf8;',
                whatItDoes: 'يعرض ويدير كافة المخازن والمستودعات والمعارض ونقاط البيع الخاصة بالشركة.',
                whatHappensIfClicked: 'يفتح قائمة المخازن لإضافة مخزن جديد أو تعديل أمين المخزن أو قفل المخزن مؤقتاً للجرد، ويسمع فوراً في فواتير البيع والشراء وتقارير الجرد.',
                targetSelector: '.tab-warehouses'
            },
            {
                label: '🤝 تبويب العملاء (Customers)',
                style: 'background:#064e3b; color:#34d399; border:1px solid #10b981;',
                whatItDoes: 'يدير ملفات العملاء، أرصدتهم الافتتاحية، السقف الائتماني، أرقام الواتساب، والباسورد السري لكشف الحساب.',
                whatHappensIfClicked: 'يفتح سجل العملاء ولوحة سياسة الواتساب، وأي عميل يُحفظ هنا يظهر فوراً في المبيعات وسندات القبض وكشف الحساب وتطبيق الموبايل.',
                targetSelector: '.tab-customers'
            },
            {
                label: '🚛 تبويب الموردين (Suppliers)',
                style: 'background:#1e1b4b; color:#a5b4fc; border:1px solid #6366f1;',
                whatItDoes: 'يدير ملفات الموردين والمصانع، أرصدتهم الافتتاحية، عملات التعامل، وحساباتهم البنكية.',
                whatHappensIfClicked: 'يفتح سجل الموردين، ويُسمّع مباشرة في فواتير المشتريات وسندات الصرف وكشف حساب الموردين.',
                targetSelector: '.tab-suppliers'
            },
            {
                label: '🏦 تبويب البنوك والحسابات المصرفية',
                style: 'background:#0c4a6e; color:#7dd3fc; border:1px solid #0284c7;',
                whatItDoes: 'يسجل الحسابات البنكية (بنكك، فوري، أوكاش، إلخ)، أرقام الحسابات، العملة، والرصيد الافتتاحي.',
                whatHappensIfClicked: 'يفتح قائمة البنوك، ويسمع الرصيد الافتتاحي في ميزان المراجعة وشاشة سندات القبض والصرف ودرج إشعارات بنكك.',
                targetSelector: '.tab-banks'
            },
            {
                label: '🗄️ تبويب الخزن النقدية (Safes)',
                style: 'background:#422006; color:#fde047; border:1px solid #eab308;',
                whatItDoes: 'يسجل الخزن النقدية (الخزينة الرئيسية، خزينة الكاشير، خزينة الدولار) وأرصدتها الافتتاحية.',
                whatHappensIfClicked: 'يفتح سجل الخزن النقدية، ويسمع فوراً في شاشات الصرف والقبض وتقرير حركة الصندوق اليومية والموبايل.',
                targetSelector: '.tab-safes'
            },
            {
                label: '📦 تبويب الأصناف والوحدات والفئات',
                style: 'background:#3b0764; color:#e9d5ff; border:1px solid #a855f7;',
                whatItDoes: 'يدير دليل الأصناف، الوحدات الأساسية والفرعية، معاملات التحويل، أسعار البيع والشراء، والأرصدة الافتتاحية بالمخازن.',
                whatHappensIfClicked: 'يفتح شجرة الأصناف والوحدات والفئات، ويسمع لحظياً في نقاط البيع والمشتريات وتقييم المخزون.',
                targetSelector: '.tab-items'
            },
            {
                label: '👥 تبويب الموظفين والصلاحيات',
                style: 'background:#1f2937; color:#f9fafb; border:1px solid #9ca3af;',
                whatItDoes: 'يدير حسابات المستخدمين، كلمات المرور، الأدوار الوظيفية (مدير، محاسب، كاشير، أمين مخزن)، ودرجة السرية (1–10).',
                whatHappensIfClicked: 'يفتح قائمة الموظفين لضبط صلاحيات الدخول ومستوى السرية المسموح لكل موظف.',
                targetSelector: '.tab-employees'
            },
            {
                label: '📊 تبويب مراكز التكلفة والمشاريع',
                style: 'background:#065f46; color:#6ee7b7; border:1px solid #10b981;',
                whatItDoes: 'ينشئ مراكز تكلفة للمشاريع أو الفروع أو السيارات لربط المصروفات والإيرادات بكل مشروع.',
                whatHappensIfClicked: 'يفتح قائمة مراكز التكلفة، وتظهر فوراً في الفواتير والسندات وتقارير أرباح وخسائر المشاريع.',
                targetSelector: '.tab-costcenters'
            },
            {
                label: '📤 زر تصدير Excel للتبويب الحالي',
                style: 'background:rgba(16,185,129,0.2); color:#34d399; border:1px solid #10b981;',
                whatItDoes: 'يستخرج جدول القسم المفتوح حالياً بالكامل إلى ملف Excel (CSV) يدعم اللغة العربية.',
                whatHappensIfClicked: 'يقوم بتحميل ملف إكسل جاهز على جهازك فوراً للاحتفاظ بنسخة أو مراجعتها.',
                targetSelector: 'button[onclick*="exportActiveTabToExcel"]'
            },
            {
                label: '📥 زر استيراد Excel للتبويب الحالي',
                style: 'background:rgba(56,189,248,0.2); color:#38bdf8; border:1px solid #38bdf8;',
                whatItDoes: 'يستورد بيانات جماعية (أصناف، عملاء، موردين، مخازن) من ملف Excel (CSV) دفعة واحدة.',
                whatHappensIfClicked: 'يفتح نافذة اختيار الملف، ويفحص البيانات ويحفظها ذرياً في قاعدة البيانات ويحدث الشاشة فوراً.',
                targetSelector: 'button[onclick*="triggerActiveTabExcelImport"]'
            },
            {
                label: '🛡️ زر سجل الرقابة والتعديلات',
                style: 'background:rgba(168,85,247,0.22); color:#e9d5ff; border:1px solid #a855f7;',
                whatItDoes: 'يعرض السجل الأمني الزمني لكافة عمليات الإضافة والتعديل والحذف التي تمت في البيانات الأساسية.',
                whatHappensIfClicked: 'يفتح نافذة الرقابة لعرض التاريخ والوقت والقسم والعنصر وتفاصيل التعديل بدقة.',
                targetSelector: 'button[onclick*="openMasterAuditLogModal"]'
            }
        ],
        scenarios: [
            {
                q: '💡 أنا مستخدم جديد، بأي قسم أبدأ في هذه الشاشة؟',
                a: 'ابدأ أولاً بتسجيل (1. المخازن) ثم (2. الخزن والبنوك) ثم (3. الأصناف ووحداتها) ثم (4. العملاء والموردين). وبمجرد حفظها ستجد البرنامج جاهزاً لإصدار الفواتير والسندات فوراً.'
            },
            {
                q: '💡 كيف أكتب الرصيد الافتتاحي للعميل أو المورد بشكل صحيح؟',
                a: 'في بطاقة العميل: إذا كان العميل مديناً لك (عليه مال) اكتب المبلغ بالموجب مثل (500000)، وإذا كان دائناً (له مال عندك) اكتب قبله علامة سالب (-500000). وفي بطاقة المورد: الرقم الموجب يعني للمورد دين عليك.'
            },
            {
                q: '💡 ما فائدة الوحدة الأساسية والوحدة الفرعية في بطاقة الصنف؟',
                a: 'تسمح لك بالشراء بالوحدة الكبرى (مثل الطن أو الكرتونة) والبيع بالحبة أو السيخة أو الكيلو بدون أي آلة حاسبة؛ فقط حدد معامل التحويل (مثلاً الكرتونة = 24 حبة) وسيقوم البرنامج بحساب الكميات والأرصدة تلقائياً.'
            },
            {
                q: '💡 ماذا يحدث لو حاولت حذف صنف أو عميل له فواتير أو سندات سابقة؟',
                a: 'يرفض النظام الحذف العشوائي تماماً! وتظهر لك نافذة الفحص الذكي لتعرض لك أرقام وتواريخ السندات والفواتير المرتبطة بهذا العنصر، وتقترح عليك تحويل حالته إلى (موقوف) بدلاً من حذفه لحماية دقة التقارير.'
            },
            {
                q: '💡 كيف أتنقل بسرعة بين الخانات أثناء الإدخال بدون استخدام الماوس؟',
                a: 'اضغط مفتاح (Enter) في لوحة المفاتيح للانتقال السلس من خانة إلى الخانة التي تليها، واضغط (Ctrl + S) في أي لحظة لحفظ البطاقة فوراً.'
            },
            {
                q: '💡 ماذا يحدث لو ضعفت شبكة الإنترنت أثناء حفظ الأصناف أو العملاء؟',
                a: 'النظام مزود بطابور مزامنة ذكي (Offline-First Queue)؛ يحفظ بياناتك محلياً في نفس اللحظة دون تعليق الشاشة، وبمجرد استقرار الإنترنت يرسلها لقاعدة البيانات والموبايل تلقائياً.'
            }
        ]
    };

    // Detailed Field-by-Field & Button-by-Button Inspector for Hover & Click (الدستور 5: مع القاعدة الذهبية لكل عنصر)
    function attachGoldenRuleToMasterExplanation(el, info) {
        if (!info || !info.desc) return info;
        if (info.desc.includes('القاعدة الذهبية')) return info;
        const customRule = el && el.getAttribute ? el.getAttribute('data-golden-rule') : '';
        if (customRule) {
            return { title: info.title, desc: `${info.desc} 👑 القاعدة الذهبية: ${customRule}` };
        }
        const t = String(info.title || '');
        let rule = 'تأكد من دقة البيانات المدخلة في هذا العنصر قبل ضغط حفظ F10 ليسمّع في التقارير والفواتير بلا أخطاء.';
        if (t.includes('الرصيد الافتتاحي') || t.includes('القيد الافتتاحي')) {
            rule = 'أدخل رصيد أول المدة واقعياً من واقع الجرد الفعلي، وتأكد من مطابقته في شاشة القيد الافتتاحي المتوازن (حساب 3109).';
        } else if (t.includes('الائتمان') || t.includes('السداد')) {
            rule = 'حدد سقف ائتمان وفترة سداد تتناسب مع الملاءة المالية للطرف؛ ليمنع النظام تجاوز المديونية آلياً.';
        } else if (t.includes('الوحدة') || t.includes('الصنف') || t.includes('سعر')) {
            rule = 'اضبط الوحدة الأساسية ومعامل التحويل وأقل سعر بيع بدقة؛ ليحسب النظام الكميات ويمنع البيع بأقل من التكلفة.';
        } else if (t.includes('السرية') || t.includes('الدور') || t.includes('الموظف')) {
            rule = 'امنح كل مستخدم الدور ودرجة السرية على قدر مسؤوليته فقط لحماية البيانات المالية الحساسة.';
        } else if (t.includes('حذف')) {
            rule = 'لا تحذف حساباً له حركات سابقة؛ بل حوّل حالته إلى (موقوف) للحفاظ على سلامة كشوفات الحساب وميزان المراجعة.';
        } else if (t.includes('واتساب') || t.includes('الرقم السري')) {
            rule = 'أدخل رقم الواتساب بصيغته الصحيحة وولّد رقماً سرياً فريداً ليعمل الاستعلام الآلي وإشعارات الفواتير بكفاءة.';
        } else if (t.includes('حفظ')) {
            rule = 'راجع الحقول الإلزامية المميزة بنجمة (*) واضغط F10 للحفظ الذري الفوري دون مضاعفة السجلات.';
        }
        return { title: info.title, desc: `${info.desc} 👑 القاعدة الذهبية لهذا العنصر: ${rule}` };
    }

    window.explainMasterDataDomElement = function (el) {
        if (!el) return null;
        if (typeof window.isElementAllowedForCurrentUserRole === 'function' && !window.isElementAllowedForCurrentUserRole(el)) {
            return null;
        }
        const guideTitle = el.getAttribute && el.getAttribute('data-guide-title');
        const guideDesc = el.getAttribute && el.getAttribute('data-guide-desc');
        if (guideTitle && guideDesc) {
            return attachGoldenRuleToMasterExplanation(el, { title: guideTitle, desc: guideDesc });
        }
        const id = el.id || el.querySelector('input, select, textarea')?.id || '';
        const onclick = el.getAttribute('onclick') || '';

        const fieldMap = {
            'warehouse-name': { title: 'خانة اسم المخزن أو المعرض', desc: 'تكتب هنا الاسم الرسمي للمستودع أو المعرض (مثل: مخزن مقاييس الرئيسي). بمجرد حفظه يظهر فوراً في فواتير المشتريات والمبيعات وبوابة الاستلام والتسليم وتقارير الجرد.' },
            'warehouse-type': { title: 'قائمة نوع المخزن', desc: 'تحدد تصنيف المستودع: مستودع رئيسي، معرض نقطة بيع، مخزن فرعي، أو مخزن بضاعة تالفة.' },
            'warehouse-location': { title: 'خانة الموقع الجغرافي للمخزن', desc: 'تحدد عنوان أو منطقة المستودع لتسهيل توجيه السائقين وأوامر التحويل المخزني بين الفروع.' },
            'warehouse-keeper': { title: 'خانة أمين المخزن المسؤول', desc: 'تختار الموظف المسؤول عهدةً عن استلام وصرف البضاعة في هذا المخزن ليظهر اسمه في أذونات الصرف والاستلام.' },
            'warehouse-phone': { title: 'خانة هاتف المخزن', desc: 'رقم الهاتف المباشر للتواصل مع أمين المستودع.' },
            'warehouse-status': { title: 'قائمة حالة المخزن التشغيلية', desc: 'تتحكم في حركة المخزن: (نشط) يقبل البيع والشراء، (جرد) يوقف الصرف والإضافة أثناء الجرد، و(موقوف) يخفيه من الفواتير الجديدة.' },
            'warehouse-security': { title: 'قائمة درجة سرية المخزن (1–10)', desc: 'تحدد مستوى صلاحية الاطلاع على المخزن؛ لا يراه إلا الموظف الذي يمتلك درجة سرية مساوية أو أعلى.' },
            'cust-name': { title: 'خانة اسم العميل الرباعي أو التجاري', desc: 'تكتب هنا الاسم الكامل للعميل أو الشركة. يمنع النظام تكرار الاسم لضمان عدم تشتت المديونية في حسابين، ويسمع فوراً في المبيعات وسندات القبض وكشف الحساب.' },
            'cust-phone': { title: 'خانة رقم الهاتف الأساسي للعميل', desc: 'رقم الاتصال المباشر بالعميل للرجوع إليه في الفواتير والمتابعة.' },
            'cust-whatsapp': { title: 'خانة رقم الواتساب المعتمد للعميل', desc: 'الرقم الذي يستقبل عليه العميل إشعارات الفواتير وسندات القبض، ويمكنه من خلاله إرسال الباسورد السري ليصله كشف حسابه أو رصيده آلياً.' },
            'cust-statement-pin': { title: 'خانة الرقم السري للعميل (3 أرقام)', desc: 'كود سري فريد (مثل 532) يُولد تلقائياً أو يدوياً؛ عندما يرسله العميل للواتساب يرد عليه النظام بكشف حسابه أو رصيده حسب الصلاحية المحددة له.' },
            'cust-opening-balance': { title: 'خانة الرصيد الافتتاحي للعميل', desc: 'مديونية العميل السابقة قبل بدء النظام: اكتب الرقم بالموجب إذا كان (عليه دين)، أو بالسالب إذا كان (له رصيد دائن). يُسمّع فوراً في كشف الحساب وميزان المراجعة وحساب التسوية 3109.' },
            'cust-currency': { title: 'قائمة عملة حساب العميل', desc: 'تحدد عملة التعامل مع العميل (جنيه سوداني SDG أو دولار USD وغيرها)، وعند اختيار عملة أجنبية يظهر حقل سعر الصرف التلقائي.' },
            'cust-ex-rate': { title: 'خانة سعر الصرف التأسيسي للعميل', desc: 'قيمة تحويل العملة الأجنبية إلى العملة المحلية لتقييم الرصيد الافتتاحي للعميل بدقة في ميزان المراجعة.' },
            'cust-limit': { title: 'خانة حد الائتمان (سقف المديونية)', desc: 'أقصى مبلغ دين مسموح به للعميل في المبيعات الآجلة؛ إذا تجاوزه العميل ينبهك النظام فوراً عند إصدار فاتورة مبيعات.' },
            'cust-price-tier': { title: 'قائمة شريحة تسعير العميل', desc: 'تحدد هل يعامل العميل بسعر النقدي أو الآجل أو أقل سعر فينزل السعر المخصص له تلقائياً في الفاتورة.' },
            'cust-payment-terms': { title: 'خانة فترة السداد بالأيام', desc: 'عدد الأيام المسموحة للعميل لسداد الفواتير الآجلة (مثلاً 15 أو 30 يوماً) لمتابعة أعمار الديون والاستحقاق.' },
            'cust-default-bank-id': { title: 'قائمة البنك الافتراضي لإشعارات الواتساب', desc: 'تحدد الحساب البنكي الذي يحول عليه هذا العميل عادةً (مثل بنكك) ليقوم النظام بتوجيه إشعاراته الواردة عبر الواتساب إلى هذا البنك تلقائياً.' },
            'cust-status': { title: 'قائمة تصنيف وحالة العميل', desc: 'تحدد حالة الحساب: (نشط) للتعامل الطبيعي، (VIP) لكبار العملاء، أو (محظور) لإيقاف البيع الآجل له.' },
            'cust-security': { title: 'قائمة درجة سرية حساب العميل (1–10)', desc: 'تحمي حسابات كبار العملاء بحيث لا يطلع على كشف حسابهم إلا الموظف الذي يملك درجة سرية مماثلة أو أعلى.' },
            'sup-name': { title: 'خانة اسم المورد أو المصنع', desc: 'تسجل فيها الاسم الرسمي للمورد أو الشركة الموردة، وتظهر فوراً في فواتير المشتريات وسندات الصرف وكشف حساب الموردين.' },
            'sup-whatsapp': { title: 'خانة رقم واتساب المورد', desc: 'الرقم المعتمد لإرسال إشعارات السداد وطلبات الشراء وكشف الحساب للمورد عبر الواتساب.' },
            'sup-statement-pin': { title: 'خانة الرقم السري للمورد (3 أرقام)', desc: 'كود سري فريد للمورد يتيح له الاستعلام الآلي عن رصيده أو كشف حسابه عبر الواتساب حسب الصلاحية الممنوحة له.' },
            'sup-opening-balance': { title: 'خانة الرصيد الافتتاحي للمورد', desc: 'المبلغ المستحق للمورد قبل بدء العمل بالنظام (بالموجب إذا كان له دين علينا، وبالسالب إذا كان لنا دفعة مقدمة عنده)، ويُرحّل فوراً لميزان المراجعة.' },
            'sup-currency': { title: 'قائمة عملة حساب المورد', desc: 'عملة التعامل مع المورد (محلية أو أجنبية) مع احتساب المعادل بالعملة المحلية في التقارير الختامية.' },
            'sup-limit': { title: 'خانة حد الائتمان مع المورد', desc: 'الحد الأعلى للمشتريات الآجلة المتفق عليها مع هذا المورد.' },
            'sup-payment-terms': { title: 'خانة فترة السداد للمورد بالأيام', desc: 'فترة السماح المتفق عليها لسداد فواتير المشتريات الآجلة لهذا المورد.' },
            'bank-name': { title: 'خانة اسم البنك أو التطبيق المصرفي', desc: 'تكتب هنا اسم البنك (مثل: بنك الخرطوم - بنكك، بنك فيصل - فوري) ليظهر في سندات القبض والصرف والفواتير ومطابقة الإشعارات.' },
            'bank-branch': { title: 'خانة اسم الفرع البنكي', desc: 'اسم أو كود فرع البنك لتمييز الحسابات المتعددة داخل نفس البنك.' },
            'bank-iban': { title: 'خانة رقم الحساب البنكي / IBAN', desc: 'رقم الحساب المصرفي المعتمد الذي يستقبل التحويلات، ويستخدمه الذكاء الاصطناعي في مطابقة إشعارات بنكك الواردة.' },
            'bank-opening-balance': { title: 'خانة الرصيد الافتتاحي للبنك', desc: 'الرصيد الفعلي الموجود في الحساب البنكي عند بدء النظام، ويُسمّع مباشرة في دفتر الأستاذ العام وميزان المراجعة.' },
            'bank-currency': { title: 'قائمة عملة الحساب البنكي', desc: 'عملة الحساب المصرفي (جنيه، دولار، ريال...) لتقييم الأرصدة البنكية بدقة.' },
            'safe-name': { title: 'خانة اسم الخزينة النقدية', desc: 'اسم الخزينة (مثل: الخزينة الرئيسية، خزينة الكاشير، خزينة الدولار) لمتابعة السيولة النقدية في كل خزنة بشكل مستقل.' },
            'safe-opening-balance': { title: 'خانة الرصيد الافتتاحي للخزينة', desc: 'النقدية الفعلية الموجودة في الخزينة عند التأسيس، وتُسمّع فوراً في شاشة السندات وتقرير حركة الصندوق وميزان المراجعة.' },
            'safe-responsible': { title: 'قائمة المسؤول عن الخزينة', desc: 'الصراف أو المحاسب المسؤول عن عهدة هذه الخزينة النقدية.' },
            'item-name-input': { title: 'خانة اسم الصنف التجاري', desc: 'الاسم الواضح للبضاعة أو المنتج (مثل: سيخ 3 لنيه). يظهر فوراً في الفواتير والباركود وتقارير المخزون.' },
            'item-code': { title: 'خانة كود الصنف', desc: 'الرمز التعريفي للصنف؛ يُولد تلقائياً أو يُكتب يدوياً لسرعة البحث والوصول.' },
            'item-barcode': { title: 'خانة الباركود الدولي للصنف', desc: 'رقم الباركود المقروء بالماسح الضوئي لسرعة البيع في الكاشير والاستلام المخزني.' },
            'item-category': { title: 'قائمة الفئة الرئيسية للصنف', desc: 'تصنيف الصنف (مثل: حديد تسليح، أسمنت، مواد صحية) لفرز تقارير المبيعات والأرباح حسب كل فئة.' },
            'item-base-unit': { title: 'قائمة الوحدة الأساسية للصنف', desc: 'الوحدة الأساسية التي يُقاس بها المخزون (مثل: طن، سيخة، كرتون، حبة).' },
            'item-selling-price': { title: 'خانة سعر البيع الافتراضي', desc: 'السعر الذي ينزل تلقائياً في فاتورة المبيعات ونقطة البيع عند اختيار هذا الصنف.' },
            'item-opening-qty': { title: 'خانة الكمية الافتتاحية للصنف', desc: 'الكمية الفعلية الموجودة من هذا الصنف في المخزن عند بدء النظام؛ تُسمّع فوراً في تقرير الجرد وتقييم المخزون.' },
            'item-opening-value': { title: 'خانة تكلفة الوحدة للرصيد الافتتاحي', desc: 'سعر تكلفة الوحدة الواحدة من مخزون أول المدة، ويُضرب في الكمية الافتتاحية ليُسمّع تلقائياً في قيمة المخزون بميزان المراجعة.' },
            'item-min-limit': { title: 'خانة الحد الأدنى للمخزون', desc: 'أقل كمية مسموحة للصنف في المخزن؛ إذا نزل الرصيد عنها ينبهك النظام فوراً في تقارير النواقص.' },
            'item-reorder-level': { title: 'خانة حد الطلب', desc: 'الكمية التي عند وصول المخزون إليها يجب إصدار أمر شراء جديد من المورد قبل نفاد البضاعة.' },
            'emp-name': { title: 'خانة اسم الموظف أو المستخدم', desc: 'الاسم الكامل للموظف الذي سيستخدم النظام أو تُربط به العهد والصلاحيات.' },
            'emp-role': { title: 'قائمة الدور الوظيفي والصلاحيات', desc: 'تحدد وظيفة المستخدم (مدير، محاسب، كاشير، مبيعات، أمين مخزن) لضبط الشاشات المسموح له فتحها.' },
            'emp-security': { title: 'قائمة درجة سرية الموظف (1–10)', desc: 'تحدد سقف السرية المسموح للموظف رؤيته؛ فالموظف بدرجة 3 لا يرى الحسابات أو المخازن ذات السرية 4 فأعلى.' },
            'costcenter-name': { title: 'خانة اسم مركز التكلفة أو المشروع', desc: 'اسم المشروع أو الفرع أو الآلية لربط المصروفات والإيرادات به واستخراج كشف أرباح وخسائر مستقل لكل مشروع.' }
        };

        if (id && fieldMap[id]) return attachGoldenRuleToMasterExplanation(el, fieldMap[id]);

        const group = el.closest('.input-group, .pro-field-card');
        if (group) {
            const lblEl = group.querySelector('label, .pro-field-label');
            const inpEl = group.querySelector('input, select, textarea');
            if (inpEl && inpEl.id && fieldMap[inpEl.id]) return attachGoldenRuleToMasterExplanation(el, fieldMap[inpEl.id]);
            if (lblEl) {
                const lblTxt = lblEl.innerText.replace(/\s+/g, ' ').trim();
                return attachGoldenRuleToMasterExplanation(el, {
                    title: `خانة: ${lblTxt}`,
                    desc: `تُستخدم هذه الخانة لإدخال (${lblTxt}) ضمن البطاقة الحالية، ويمكنك الضغط على مفتاح Enter للانتقال السلس للخانة التالية، وعند الحفظ تُسمّع فوراً في جميع التقارير والشاشات المرتبطة.`
                });
            }
        }

        if (onclick.includes('openTab')) {
            const tabTxt = (el.innerText || '').trim();
            return attachGoldenRuleToMasterExplanation(el, {
                title: `قسم ${tabTxt}`,
                desc: `ينقلك فوراً إلى قسم (${tabTxt}) لإضافة أو تعديل البطاقات التأسيسية الخاصة به، مع تسميع لحظي مباشر في الفواتير والسندات والتقارير.`
            });
        }
        if (onclick.includes('openOpeningEquilibriumModal')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '⚖️ زر القيد الافتتاحي المتوازن (حساب 3109)',
                desc: 'يفتح كشف توازن الأرصدة الافتتاحية للعملاء والموردين والبنوك والخزن والمخزون، ويعرض كيف يوازنها النظام تلقائياً عبر حساب تسوية الأرصدة الافتتاحية 3109.'
            });
        }
        if (onclick.includes('openWarehouseForm') || onclick.includes('openBankForm') || onclick.includes('openSafeForm') || onclick.includes('openItemForm') || onclick.includes('openNewEmployeeForm') || onclick.includes('openCostCenterForm')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: `➕ زر ${(el.innerText || 'إضافة بطاقة جديدة').trim()}`,
                desc: 'يفتح نموذج الإضافة الفوري لهذا القسم ويضع المؤشر تلقائياً في الخانة الأولى للبدء السريع في الكتابة.'
            });
        }
        if (onclick.includes('bulkSetEntityStatementMode')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: `📊 زر ${(el.innerText || 'تعميم صلاحية الكشف').trim()}`,
                desc: 'يُطبق صلاحية كشف الحساب عبر الواتساب (كشف مفصل أو رصيد فقط أو إيقاف) على جميع الحسابات في هذا القسم بضغطة واحدة.'
            });
        }
        if (onclick.includes('bulkGenerateEntityPins') || onclick.includes('generateRandomFormPin')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '🔐 زر توليد الأرقام السرية (PIN)',
                desc: 'يُولّد أرقاماً سرية فريدة من 3 أرقام للحسابات لتمكين الاستعلام الآلي الآمن عن الرصيد أو كشف الحساب عبر الواتساب.'
            });
        }
        if (onclick.includes('toggleAdminWaPolicyDrawer')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '⚙️ زر سياسة الواتساب والاعتماد الآلي',
                desc: 'يفتح إعدادات صلاحية الكشف، واعتماد إشعارات الدفع، والبنك الافتراضي، والتحقق الأمني، ونطاق الكشف.'
            });
        }
        if (onclick.includes('save')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '💾 زر الحفظ الذري والتسميع الفوري',
                desc: 'يفحص صحة البيانات ويمنع التكرار، ثم يحفظ البطاقة فوراً في قاعدة البيانات ويُسمّع التعديل في نفس اللحظة في التقارير والفواتير وميزان المراجعة.'
            });
        }
        if (onclick.includes('delete')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '🗑️ زر الفحص والحذف المحمي',
                desc: 'يفحص أولاً جميع الفواتير والسندات؛ فإذا كان العنصر مرتبطاً بحركات مالية سابقة يعرضها لك ويمنع الحذف لحماية توازن الميزانية.'
            });
        }
        if (onclick.includes('edit')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '✏️ زر تعديل البطاقة',
                desc: 'يفتح بيانات هذا السجل داخل النموذج العلوي لتعديل أي خانة فيه ثم حفظ التغييرات وتسميعها في كافة الشاشات.'
            });
        }
        if (onclick.includes('exportActiveTabToExcel') || onclick.includes('exportMasterEntityCsv')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '📥 زر التصدير الجماعي الذكي (Excel / CSV)',
                desc: 'يستخرج كافة سجلات هذا القسم (الأصناف أو العملاء أو الموردين) بضغطة واحدة إلى ملف Excel / CSV جاهز للطباعة أو التعديل الجماعي.'
            });
        }
        if (onclick.includes('triggerActiveTabExcelImport') || onclick.includes('triggerMasterEntityCsvImport')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '📤 زر الاستيراد الجماعي الذكي (Excel / CSV)',
                desc: 'يتيح رفع ملف Excel / CSV لإدخال أو تحديث مئات الأصناف أو العملاء أو الموردين دفعة واحدة مع التسميع الفوري في قاعدة البيانات.'
            });
        }
        if (onclick.includes('openMasterAuditLogModal')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '🛡️ زر سجل الرقابة والتعديلات',
                desc: 'يعرض السجل الأمني الكامل لكل عملية إضافة أو تعديل أو حذف تمت في شاشة البيانات الأساسية بالتاريخ والوقت.'
            });
        }
        if (onclick.includes('openBulkPriceUpdaterModal')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '📈 زر محرك تحديث الأسعار الجماعي الذكي',
                desc: 'يفتح نافذة تعديل أسعار البيع والشراء والآجل والنقدي وأقل سعر بنسبة مئوية أو بمبلغ ثابت لفئة محددة أو لجميع الأصناف بضغطة واحدة.'
            });
        }
        if (onclick.includes('openSmartClipboardPasteModal')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '📋 زر اللصق الجماعي الذكي من الحافظة (Excel / WhatsApp)',
                desc: 'يتيح لصق قوائم الأصناف أو العملاء أو الموردين مباشرة من إكسل أو واتساب مع معاينة فورية للجدول قبل الحفظ الذري.'
            });
        }
        if (onclick.includes('openWebRtcBarcodeScanner')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '📷 زر قارئ الباركود الحي بالكاميرا (WebRTC)',
                desc: 'يفتح كاميرا الموبايل أو اللابتوب لقراءة باركود الصنف ضوئياً وتعبئة خانة الباركود في جزء من الثانية.'
            });
        }
        if (onclick.includes('openEntityQrCardModal')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '🪪 زر بطاقة الهوية الذكية والـ QR للعميل أو المورد',
                desc: 'يصدر بطاقة تعريف رقمية تتضمن رمز QR والرقم السري المكون من 3 أرقام لإرسالها عبر الواتساب بضغطة واحدة.'
            });
        }
        if (onclick.includes('quickAddKeeperModal') || onclick.includes('quickAddBankModal') || onclick.includes('quickAddEmployeeModal') || onclick.includes('quickAddCategory') || onclick.includes('quickAddUnit')) {
            return attachGoldenRuleToMasterExplanation(el, {
                title: '⚡ زر الإضافة الفورية السريعة (+)',
                desc: 'يفتح شاشة وليدة مصغرة لإضافة عنصر جديد (فئة، وحدة، موظف، أو بنك) وتحديث القائمة المنسدلة فوراً دون مغادرة البطاقة الحالية.'
            });
        }
        if (el.tagName === 'TH') {
            const thTxt = (el.innerText || '').trim();
            if (thTxt) {
                return attachGoldenRuleToMasterExplanation(el, {
                    title: `عمود الجدول: ${thTxt}`,
                    desc: `يعرض هذا العمود (${thTxt}) لكل سجل في الجدول بدقة ومقاس موحد لسهولة المراجعة والمقارنة السريعة.`
                });
            }
        }

        return null;
    };

    window.openMasterDataIsolatedGuide = function () {
        if (typeof window.openSmartGuide === 'function') {
            window.openSmartGuide('master-data');
        }
    };
})();

// ════════════════════════════════════════════════════════════════════════════
// 👑 4. INLINE GRID EDITING ENGINE + OPENING ENTRY EQUILIBRIUM (3109) + DATA HEALTH SCORE
// ════════════════════════════════════════════════════════════════════════════
window.startInlineMasterEdit = function (tdEl, editType, entityId, currentVal) {
    if (!tdEl || tdEl.querySelector('input')) return;
    const origHtml = tdEl.innerHTML;
    const input = document.createElement('input');
    input.type = 'number';
    input.step = 'any';
    input.value = parseFloat(currentVal) || 0;
    input.style.cssText = 'width:115px; height:30px; padding:2px 8px; border-radius:7px; border:2px solid #38bdf8; background:#0f172a; color:#fef08a; font-weight:900; font-family:monospace; font-size:0.95rem; text-align:center;';
    tdEl.innerHTML = '';
    tdEl.appendChild(input);
    input.focus();
    input.select();

    let committed = false;
    async function commitInlineChange() {
        if (committed) return;
        committed = true;
        const newVal = Math.round((parseFloat(input.value) || 0) * 100) / 100;
        if (newVal === (parseFloat(currentVal) || 0)) {
            tdEl.innerHTML = origHtml;
            return;
        }
        try {
            if (editType === 'item_price') {
                const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                const idx = items.findIndex(x => String(x.id) === String(entityId));
                if (idx > -1) {
                    items[idx].sellingPrice = newVal;
                    if (Array.isArray(items[idx].units) && items[idx].units[0]) {
                        items[idx].units[0].cashPrice = newVal;
                    }
                    setMasterLocalData('erp_items_v1', items, true);
                    await syncToBackend('save_item', items[idx]);
                    if (typeof logMasterDataAudit === 'function') {
                        logMasterDataAudit('تعديل سريع بالجدول', 'الأصناف', items[idx].name, `سعر البيع الجديد: ${newVal.toLocaleString()} SDG`);
                    }
                    renderItems();
                    if (typeof showToast === 'function') showToast(`✅ تم تحديث سعر البيع للصنف "${items[idx].name}" إلى ${newVal.toLocaleString()} SDG`, 'success');
                }
            } else if (editType === 'customer_limit') {
                const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                const idx = custs.findIndex(x => String(x.id) === String(entityId));
                if (idx > -1) {
                    custs[idx].creditLimit = newVal;
                    setMasterLocalData('erp_customers_v1', custs, true);
                    await syncToBackend('save_customer', custs[idx]);
                    renderCustomers(document.getElementById('customer-search-input')?.value || '');
                    if (typeof showToast === 'function') showToast(`✅ تم تحديث السقف الائتماني للعميل "${custs[idx].name}"`, 'success');
                }
            } else if (editType === 'supplier_limit') {
                const sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
                const idx = sups.findIndex(x => String(x.id) === String(entityId));
                if (idx > -1) {
                    sups[idx].creditLimit = newVal;
                    setMasterLocalData('erp_suppliers_v1', sups, true);
                    await syncToBackend('save_supplier', sups[idx]);
                    renderSuppliers(document.getElementById('supplier-search-input')?.value || '');
                    if (typeof showToast === 'function') showToast(`✅ تم تحديث السقف الائتماني للمورد "${sups[idx].name}"`, 'success');
                }
            }
        } catch (err) {
            tdEl.innerHTML = origHtml;
        }
    }

    input.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            ev.stopPropagation();
            commitInlineChange();
        } else if (ev.key === 'Escape') {
            ev.preventDefault();
            ev.stopPropagation();
            committed = true;
            tdEl.innerHTML = origHtml;
        }
    });
    input.addEventListener('blur', function () {
        commitInlineChange();
    });
};

window.computeOpeningEquilibriumSummary = function () {
    const custs = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => x && !x.deleted);
    const sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(x => x && !x.deleted);
    const banks = (JSON.parse(localStorage.getItem('erp_banks_v1') || '[]')).filter(x => x && !x.deleted);
    const safes = (JSON.parse(localStorage.getItem('erp_safes_v1') || '[]')).filter(x => x && !x.deleted);
    const items = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]')).filter(x => x && !x.deleted);

    let custDebit = 0, custCredit = 0;
    custs.forEach(c => {
        const rate = parseFloat(c.exchangeRate || c.exRate) || 1;
        custDebit += (parseFloat(c.openingDebit) || 0) * (c.currency && c.currency !== 'SDG' ? rate : 1);
        custCredit += (parseFloat(c.openingCredit) || 0) * (c.currency && c.currency !== 'SDG' ? rate : 1);
    });

    let supDebit = 0, supCredit = 0;
    sups.forEach(s => {
        const rate = parseFloat(s.exchangeRate || s.exRate) || 1;
        supDebit += (parseFloat(s.openingDebit) || 0) * (s.currency && s.currency !== 'SDG' ? rate : 1);
        supCredit += (parseFloat(s.openingCredit) || 0) * (s.currency && s.currency !== 'SDG' ? rate : 1);
    });

    let bankDebit = 0, bankCredit = 0;
    banks.forEach(b => {
        const rate = parseFloat(b.exchangeRate || b.exRate) || 1;
        const ob = (parseFloat(b.openingBalance !== undefined ? b.openingBalance : b.balance) || 0) * (b.currency && b.currency !== 'SDG' ? rate : 1);
        if (ob >= 0) bankDebit += ob; else bankCredit += Math.abs(ob);
    });

    let safeDebit = 0, safeCredit = 0;
    safes.forEach(sf => {
        const rate = parseFloat(sf.exchangeRate || sf.exRate) || 1;
        const ob = (parseFloat(sf.openingBalance !== undefined ? sf.openingBalance : sf.balance) || 0) * (sf.currency && sf.currency !== 'SDG' ? rate : 1);
        if (ob >= 0) safeDebit += ob; else safeCredit += Math.abs(ob);
    });

    let invOpeningValue = 0;
    items.forEach(it => {
        const stock = parseFloat(it.openingStock ?? it.openingQty ?? it.openingBalance ?? (it.units && it.units[0] ? it.units[0].stock : 0)) || 0;
        const cost = parseFloat(it.costPrice || it.openingValue || it.cost || (it.units && it.units[0] ? it.units[0].purchasePrice : 0)) || 0;
        if (stock > 0 && cost > 0) invOpeningValue += (stock * cost);
    });

    const totalDebitBeforeEq = Math.round((custDebit + supDebit + bankDebit + safeDebit + invOpeningValue) * 100) / 100;
    const totalCreditBeforeEq = Math.round((custCredit + supCredit + bankCredit + safeCredit) * 100) / 100;
    const netDiff = Math.round((totalDebitBeforeEq - totalCreditBeforeEq) * 100) / 100;

    // Account 3109: حساب تسوية الأرصدة الافتتاحية / رأس المال العامل
    const eqDebit = netDiff < 0 ? Math.abs(netDiff) : 0;
    const eqCredit = netDiff > 0 ? netDiff : 0;
    const finalBalancedTotal = Math.max(totalDebitBeforeEq, totalCreditBeforeEq);

    const summary = {
        accountCode: '3109',
        accountName: 'حساب تسوية الأرصدة الافتتاحية / رأس المال العامل',
        custDebit, custCredit,
        supDebit, supCredit,
        bankDebit, bankCredit,
        safeDebit, safeCredit,
        invOpeningValue,
        totalDebitBeforeEq,
        totalCreditBeforeEq,
        eqDebit,
        eqCredit,
        finalBalancedTotal,
        updatedAt: new Date().toISOString()
    };
    try {
        localStorage.setItem('erp_opening_balance_equilibrium_v1', JSON.stringify(summary));
    } catch (e) {}
    return summary;
};

window.refreshMasterDataHealthAndEquilibrium = function () {
    try {
        const eq = window.computeOpeningEquilibriumSummary();
        const eqEl = document.getElementById('md-health-opening-eq');
        if (eqEl) {
            eqEl.textContent = `متوازن: ${eq.finalBalancedTotal.toLocaleString()} SDG`;
        }

        const custs = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => x && !x.deleted);
        const sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(x => x && !x.deleted);
        const items = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]')).filter(x => x && !x.deleted);
        const whs = (JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]')).filter(x => x && !x.deleted);

        // Pricing check
        let mispricedCount = 0;
        items.forEach(i => {
            const p = parseFloat(i.sellingPrice || (i.units && i.units[0] ? i.units[0].cashPrice : 0)) || 0;
            const c = parseFloat(i.costPrice || i.cost || (i.units && i.units[0] ? i.units[0].purchasePrice : 0)) || 0;
            if (p <= 0 || (c > 0 && p <= c)) mispricedCount++;
        });
        const prText = document.getElementById('md-health-pricing-text');
        const prBadge = document.getElementById('md-health-pricing-badge');
        if (prText && prBadge) {
            if (mispricedCount > 0) {
                prText.textContent = `${mispricedCount} صنف يحتاج مراجعة تسعير`;
                prBadge.textContent = `تنبيه ⚠️`;
                prBadge.style.color = '#fbbf24';
            } else {
                prText.textContent = `جميع الأصناف مسعرة بربحية (${items.length})`;
                prBadge.textContent = `ممتاز ✅`;
                prBadge.style.color = '#34d399';
            }
        }

        // WhatsApp & PIN coverage
        const totalEntities = custs.length + sups.length;
        let withWaAndPin = 0;
        [...custs, ...sups].forEach(e => {
            const hasPhone = Boolean((e.whatsapp || e.phone || '').trim());
            const hasPin = Boolean(String(e.statementPin || '').trim());
            if (hasPhone && hasPin) withWaAndPin++;
        });
        const waPct = totalEntities > 0 ? Math.round((withWaAndPin / totalEntities) * 100) : 100;
        const waText = document.getElementById('md-health-wa-text');
        const waBadge = document.getElementById('md-health-wa-badge');
        if (waText && waBadge) {
            waText.textContent = `${withWaAndPin} من ${totalEntities} حساب مكتمل`;
            waBadge.textContent = `${waPct}%`;
        }

        // Overall Data Health Score
        const itemHealth = items.length > 0 ? Math.round(((items.length - mispricedCount) / items.length) * 100) : 100;
        const whHealth = whs.length > 0 ? 100 : 80;
        const totalScore = Math.round((itemHealth * 0.45) + (waPct * 0.35) + (whHealth * 0.20));
        const scText = document.getElementById('md-health-score-text');
        const scBadge = document.getElementById('md-health-score-badge');
        if (scText && scBadge) {
            scText.textContent = totalScore >= 90 ? 'جودة بيانات عالمية ممتازة' : (totalScore >= 75 ? 'جيدة جداً — راجع التنبيهات' : 'تحتاج استكمال بعض الحقول');
            scBadge.textContent = `${totalScore}%`;
        }
    } catch (e) {}
};

window.openOpeningEquilibriumModal = function () {
    const eq = window.computeOpeningEquilibriumSummary();
    let modal = document.getElementById('md-opening-eq-modal');
    if (!modal) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="md-opening-eq-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.84); backdrop-filter:blur(10px); align-items:center; justify-content:center; padding:16px;">
                <div class="md-child-modal-card" style="background:#0f172a; border:1.5px solid rgba(16,185,129,0.5); border-radius:18px; width:100%; max-width:680px; overflow:hidden; box-shadow:0 20px 55px rgba(0,0,0,0.85); font-family:'Tajawal',sans-serif;">
                    <div style="padding:16px 20px; background:rgba(16,185,129,0.14); border-bottom:1px solid rgba(16,185,129,0.3); display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0; color:#34d399; font-size:1.1rem; font-weight:900;">⚖️ كشف توازن القيد الافتتاحي (${eq.accountCode})</h3>
                        <button type="button" onclick="document.getElementById('md-opening-eq-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 12px; font-weight:800; cursor:pointer;">✖ إغلاق <span class="kbd-hint">Esc</span></button>
                    </div>
                    <div id="md-opening-eq-body" style="padding:18px 20px; color:#f8fafc; font-size:0.9rem;"></div>
                </div>
            </div>
        `);
        modal = document.getElementById('md-opening-eq-modal');
    }
    const body = document.getElementById('md-opening-eq-body');
    if (body) {
        body.innerHTML = `
            <table style="width:100%; border-collapse:collapse; font-size:0.88rem; text-align:right;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.06); color:#38bdf8;">
                        <th style="padding:9px; border-bottom:1px solid rgba(255,255,255,0.12);">البند التأسيسي</th>
                        <th style="padding:9px; border-bottom:1px solid rgba(255,255,255,0.12); text-align:center;">إجمالي المدين (Debit)</th>
                        <th style="padding:9px; border-bottom:1px solid rgba(255,255,255,0.12); text-align:center;">إجمالي الدائن (Credit)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.06);">🤝 أرصدة العملاء الافتتاحية</td><td style="padding:8px; text-align:center; font-family:monospace; color:#34d399;">${eq.custDebit.toLocaleString()}</td><td style="padding:8px; text-align:center; font-family:monospace; color:#f87171;">${eq.custCredit.toLocaleString()}</td></tr>
                    <tr><td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.06);">🚛 أرصدة الموردين الافتتاحية</td><td style="padding:8px; text-align:center; font-family:monospace; color:#34d399;">${eq.supDebit.toLocaleString()}</td><td style="padding:8px; text-align:center; font-family:monospace; color:#f87171;">${eq.supCredit.toLocaleString()}</td></tr>
                    <tr><td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.06);">🏦 أرصدة البنوك الافتتاحية</td><td style="padding:8px; text-align:center; font-family:monospace; color:#34d399;">${eq.bankDebit.toLocaleString()}</td><td style="padding:8px; text-align:center; font-family:monospace; color:#f87171;">${eq.bankCredit.toLocaleString()}</td></tr>
                    <tr><td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.06);">🗄️ أرصدة الخزن النقدية الافتتاحية</td><td style="padding:8px; text-align:center; font-family:monospace; color:#34d399;">${eq.safeDebit.toLocaleString()}</td><td style="padding:8px; text-align:center; font-family:monospace; color:#f87171;">${eq.safeCredit.toLocaleString()}</td></tr>
                    <tr><td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.06);">📦 تقييم مخزون أول المدة</td><td style="padding:8px; text-align:center; font-family:monospace; color:#34d399;">${eq.invOpeningValue.toLocaleString()}</td><td style="padding:8px; text-align:center; font-family:monospace;">0</td></tr>
                    <tr style="background:rgba(251,191,36,0.12); font-weight:900; color:#fef08a;">
                        <td style="padding:9px;">⚖️ ${eq.accountName} (${eq.accountCode})</td>
                        <td style="padding:9px; text-align:center; font-family:monospace;">${eq.eqDebit.toLocaleString()}</td>
                        <td style="padding:9px; text-align:center; font-family:monospace;">${eq.eqCredit.toLocaleString()}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:rgba(16,185,129,0.2); font-weight:900; color:#ffffff; font-size:0.95rem;">
                        <td style="padding:10px;">✅ إجمالي القيد الافتتاحي المتوازن</td>
                        <td style="padding:10px; text-align:center; font-family:monospace; color:#34d399;">${eq.finalBalancedTotal.toLocaleString()} SDG</td>
                        <td style="padding:10px; text-align:center; font-family:monospace; color:#34d399;">${eq.finalBalancedTotal.toLocaleString()} SDG</td>
                    </tr>
                </tfoot>
            </table>
        `;
    }
    modal.style.display = 'flex';
};

window.filterMispricedItemsQuick = function () {
    const itemsTabBtn = document.querySelector('.tab-items');
    if (itemsTabBtn) itemsTabBtn.click();
    if (typeof showToast === 'function') {
        showToast('💡 انقر مرتين على سعر البيع لتعديله مباشرة', 'success', 2000);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (typeof window.refreshMasterDataHealthAndEquilibrium === 'function') {
            window.refreshMasterDataHealthAndEquilibrium();
        }
        if (typeof window.prewarmSmartGuideScreen === 'function') {
            window.prewarmSmartGuideScreen('master-data');
        }
        if (typeof window.initMasterFuzzyDuplicateGuard === 'function') {
            window.initMasterFuzzyDuplicateGuard();
        }
    }, 250);
});

// ════════════════════════════════════════════════════════════════════════════
// 👑 5. SMART BULK EXCEL/CSV EXPORT & IMPORT + REAL-TIME FUZZY DUPLICATE GUARD
// ════════════════════════════════════════════════════════════════════════════
window.exportMasterEntityCsv = function (entityType) {
    const tabMap = {
        customer: 'tab-customers',
        supplier: 'tab-suppliers',
        item: 'tab-items',
        warehouse: 'tab-warehouses'
    };
    const targetTabId = tabMap[entityType];
    if (targetTabId) {
        const curActive = document.querySelector('.md-tab-content.active');
        if (!curActive || curActive.id !== targetTabId) {
            const tabBtn = document.querySelector(`.md-tab-btn[onclick*="${targetTabId}"]`);
            if (tabBtn) { try { tabBtn.click(); } catch (e) {} }
        }
    }
    if (typeof exportActiveTabToExcel === 'function') {
        exportActiveTabToExcel();
    }
};

window.triggerMasterEntityCsvImport = function (entityType) {
    const tabMap = {
        customer: 'tab-customers',
        supplier: 'tab-suppliers',
        item: 'tab-items',
        warehouse: 'tab-warehouses'
    };
    const targetTabId = tabMap[entityType];
    if (targetTabId) {
        const curActive = document.querySelector('.md-tab-content.active');
        if (!curActive || curActive.id !== targetTabId) {
            const tabBtn = document.querySelector(`.md-tab-btn[onclick*="${targetTabId}"]`);
            if (tabBtn) { try { tabBtn.click(); } catch (e) {} }
        }
    }
    if (typeof triggerActiveTabExcelImport === 'function') {
        triggerActiveTabExcelImport();
    }
};

window.normalizeArabicFuzzyText = function (str) {
    if (!str) return '';
    const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
    const extArabicDigits = '۰۱۲۳۴۵۶۷۸۹';
    return String(str)
        .replace(/[٠-٩]/g, d => String(arabicDigits.indexOf(d)))
        .replace(/[۰-۹]/g, d => String(extArabicDigits.indexOf(d)))
        .replace(/[\u064B-\u065F\u0670\u0640]/g, '') // strip tashkeel & tatweel
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/[ىئ]/g, 'ي')
        .replace(/ؤ/g, 'و')
        .replace(/[^\p{L}\p{N}\s]/gu, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
};

window.normalizePhoneCoreDigits = function (phoneStr) {
    if (!phoneStr) return '';
    const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
    const extArabicDigits = '۰۱۲۳۴۵۶۷۸۹';
    const digits = String(phoneStr)
        .replace(/[٠-٩]/g, d => String(arabicDigits.indexOf(d)))
        .replace(/[۰-۹]/g, d => String(extArabicDigits.indexOf(d)))
        .replace(/[^0-9]/g, '');
    if (digits.length < 7) return '';
    return digits.length > 9 ? digits.slice(-9) : digits;
};

window.computeFuzzyNameSimilarity = function (a, b) {
    const na = window.normalizeArabicFuzzyText(a);
    const nb = window.normalizeArabicFuzzyText(b);
    if (!na || !nb) return 0;
    if (na === nb) return 1.0;
    const compactA = na.replace(/\s+/g, '');
    const compactB = nb.replace(/\s+/g, '');
    if (compactA === compactB) return 0.98;
    if (compactA.length >= 4 && compactB.length >= 4 && (compactA.includes(compactB) || compactB.includes(compactA))) {
        const ratio = Math.min(compactA.length, compactB.length) / Math.max(compactA.length, compactB.length);
        if (ratio >= 0.68) return 0.88;
    }
    // Bigram Dice coefficient
    const bigrams = (s) => {
        const map = new Map();
        for (let i = 0; i < s.length - 1; i++) {
            const bg = s.substring(i, i + 2);
            map.set(bg, (map.get(bg) || 0) + 1);
        }
        return map;
    };
    if (compactA.length < 2 || compactB.length < 2) return 0;
    const bgA = bigrams(compactA);
    const bgB = bigrams(compactB);
    let intersection = 0;
    bgA.forEach((count, bg) => {
        if (bgB.has(bg)) intersection += Math.min(count, bgB.get(bg));
    });
    return (2.0 * intersection) / ((compactA.length - 1) + (compactB.length - 1));
};

window.renderFuzzyGuardAlert = function (inputEl, alertData) {
    if (!inputEl) return;
    const host = inputEl.closest('.pro-field-card, .input-group') || inputEl.parentElement;
    if (!host) return;
    let badge = host.querySelector('.md-fuzzy-guard-badge');
    if (!alertData) {
        if (badge) badge.remove();
        inputEl.style.borderColor = '';
        return;
    }
    if (!badge) {
        badge = document.createElement('div');
        badge.className = 'md-fuzzy-guard-badge';
        badge.style.cssText = 'margin-top:6px; padding:6px 10px; border-radius:8px; font-size:0.78rem; font-weight:800; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; line-height:1.4; transition:all 0.2s ease;';
        host.appendChild(badge);
    }
    const isExact = alertData.isExact;
    badge.style.background = isExact ? 'rgba(239, 68, 68, 0.18)' : 'rgba(245, 158, 11, 0.16)';
    badge.style.border = isExact ? '1px solid rgba(239, 68, 68, 0.5)' : '1px solid rgba(245, 158, 11, 0.45)';
    badge.style.color = isExact ? '#fca5a5' : '#fde68a';
    inputEl.style.borderColor = isExact ? '#ef4444' : '#f59e0b';

    let actionBtnHtml = '';
    if (alertData.actionJs) {
        actionBtnHtml = `<button type="button" onclick="${alertData.actionJs}" style="background:rgba(56,189,248,0.2); color:#38bdf8; border:1px solid rgba(56,189,248,0.45); border-radius:6px; padding:2px 8px; font-size:0.74rem; font-weight:900; cursor:pointer; white-space:nowrap;">✏️ فتح السجل</button>`;
    }
    badge.innerHTML = `<span>${isExact ? '🚫' : '⚠️'} ${alertData.message}</span>${actionBtnHtml}`;
};

window.initMasterFuzzyDuplicateGuard = function () {
    if (window.__mdFuzzyGuardInitialized) return;
    window.__mdFuzzyGuardInitialized = true;

    const getActiveLists = () => {
        let custs = [], sups = [], items = [];
        try { custs = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { items = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        return { custs, sups, items };
    };

    const checkEntityNameFuzzy = (inputEl, mode) => {
        const val = (inputEl.value || '').trim();
        if (val.length < 2) {
            window.renderFuzzyGuardAlert(inputEl, null);
            return;
        }
        const { custs, sups, items } = getActiveLists();
        if (mode === 'item') {
            const curCode = (document.getElementById('item-code')?.value || '').trim();
            let bestMatch = null;
            let bestScore = 0;
            for (const it of items) {
                if (curCode && String(it.id) === String(curCode)) continue;
                const score = window.computeFuzzyNameSimilarity(val, it.name || '');
                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = it;
                }
            }
            if (bestMatch && bestScore >= 0.80) {
                const isExact = bestScore >= 0.97;
                const pct = Math.round(bestScore * 100);
                const safeId = String(bestMatch.id).replace(/'/g, "\\'");
                window.renderFuzzyGuardAlert(inputEl, {
                    isExact,
                    message: isExact
                        ? `اسم الصنف مسجل مسبقاً بالكامل: "${bestMatch.name}" (كود: ${bestMatch.id})`
                        : `تنبيه تشابه (${pct}%): يوجد صنف مشابه باسم "${bestMatch.name}" (السعر: ${(parseFloat(bestMatch.sellingPrice) || 0).toLocaleString()})`,
                    actionJs: typeof editItem === 'function' ? `editItem('${safeId}')` : ''
                });
            } else {
                window.renderFuzzyGuardAlert(inputEl, null);
            }
        } else {
            const curId = (document.getElementById(mode === 'customer' ? 'cust-id' : 'sup-id')?.value || '').trim();
            const combined = [
                ...custs.map(c => ({ ...c, _kind: 'عميل', _editFn: 'editCustomer' })),
                ...sups.map(s => ({ ...s, _kind: 'مورد', _editFn: 'editSupplier' }))
            ];
            let bestMatch = null;
            let bestScore = 0;
            for (const ent of combined) {
                if (curId && String(ent.id) === String(curId)) continue;
                const score = window.computeFuzzyNameSimilarity(val, ent.name || '');
                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = ent;
                }
            }
            if (bestMatch && bestScore >= 0.82) {
                const isExact = bestScore >= 0.97;
                const pct = Math.round(bestScore * 100);
                const safeId = String(bestMatch.id).replace(/'/g, "\\'");
                window.renderFuzzyGuardAlert(inputEl, {
                    isExact,
                    message: isExact
                        ? `الاسم مسجل مسبقاً كـ${bestMatch._kind}: "${bestMatch.name}"`
                        : `تنبيه تشابه (${pct}%): يوجد ${bestMatch._kind} مشابه باسم "${bestMatch.name}"`,
                    actionJs: `${bestMatch._editFn}('${safeId}')`
                });
            } else {
                window.renderFuzzyGuardAlert(inputEl, null);
            }
        }
    };

    const checkPhoneDuplicate = (inputEl, mode) => {
        const core = window.normalizePhoneCoreDigits(inputEl.value || '');
        if (!core) {
            window.renderFuzzyGuardAlert(inputEl, null);
            return;
        }
        const curId = (document.getElementById(mode === 'customer' ? 'cust-id' : 'sup-id')?.value || '').trim();
        const { custs, sups } = getActiveLists();
        const combined = [
            ...custs.map(c => ({ ...c, _kind: 'عميل', _editFn: 'editCustomer' })),
            ...sups.map(s => ({ ...s, _kind: 'مورد', _editFn: 'editSupplier' }))
        ];
        let matchedEntity = null;
        for (const ent of combined) {
            if (curId && String(ent.id) === String(curId)) continue;
            const mainCore = window.normalizePhoneCoreDigits(ent.whatsapp || ent.phone || '');
            if (mainCore && mainCore === core) {
                matchedEntity = ent;
                break;
            }
            if (Array.isArray(ent.subPhones)) {
                const subHit = ent.subPhones.find(sp => window.normalizePhoneCoreDigits(typeof sp === 'string' ? sp : (sp?.phone || '')) === core);
                if (subHit) {
                    matchedEntity = ent;
                    break;
                }
            }
        }
        if (matchedEntity) {
            const safeId = String(matchedEntity.id).replace(/'/g, "\\'");
            window.renderFuzzyGuardAlert(inputEl, {
                isExact: true,
                message: `رقم الهاتف مسجل مسبقاً لـ${matchedEntity._kind}: "${matchedEntity.name}" (${matchedEntity.whatsapp || matchedEntity.phone || core})`,
                actionJs: `${matchedEntity._editFn}('${safeId}')`
            });
        } else {
            window.renderFuzzyGuardAlert(inputEl, null);
        }
    };

    const checkBarcodeDuplicate = (inputEl) => {
        const bc = (inputEl.value || '').trim();
        if (bc.length < 3) {
            window.renderFuzzyGuardAlert(inputEl, null);
            return;
        }
        const curCode = (document.getElementById('item-code')?.value || '').trim();
        const { items } = getActiveLists();
        const hit = items.find(it => {
            if (curCode && String(it.id) === String(curCode)) return false;
            if (it.barcode && String(it.barcode).trim() === bc) return true;
            if (Array.isArray(it.units) && it.units.some(u => u && String(u.barcode || '').trim() === bc)) return true;
            return false;
        });
        if (hit) {
            const safeId = String(hit.id).replace(/'/g, "\\'");
            window.renderFuzzyGuardAlert(inputEl, {
                isExact: true,
                message: `هذا الباركود مسجل مسبقاً للصنف: "${hit.name}"`,
                actionJs: typeof editItem === 'function' ? `editItem('${safeId}')` : ''
            });
        } else {
            window.renderFuzzyGuardAlert(inputEl, null);
        }
    };

    document.addEventListener('input', (e) => {
        const t = e.target;
        if (!t || t.tagName !== 'INPUT') return;
        if (t.id === 'item-name-input') checkEntityNameFuzzy(t, 'item');
        else if (t.id === 'cust-name') checkEntityNameFuzzy(t, 'customer');
        else if (t.id === 'sup-name') checkEntityNameFuzzy(t, 'supplier');
        else if (t.id === 'cust-whatsapp' || t.id === 'cust-phone') checkPhoneDuplicate(t, 'customer');
        else if (t.id === 'sup-whatsapp' || t.id === 'sup-phone') checkPhoneDuplicate(t, 'supplier');
        else if (t.id === 'item-barcode') checkBarcodeDuplicate(t);
        else if (t.classList.contains('sub-phone-number')) {
            const isSup = Boolean(t.closest('#supplier-form'));
            checkPhoneDuplicate(t, isSup ? 'supplier' : 'customer');
        }
    });
};

// ════════════════════════════════════════════════════════════════════════════
// 👑 5. WORLD-CLASS INNOVATION PACK (4 APPROVED ENGINES)
//    1) WebRTC Camera Barcode Scanner
//    2) Bulk Price Updater (Category / % / USD Exchange Rate)
//    3) Customer & Supplier Digital QR + PIN Card (Download & WhatsApp)
//    4) Smart Excel Clipboard Paste Grid (Ctrl+V Direct Import)
// ════════════════════════════════════════════════════════════════════════════

// --- 5.1 WebRTC Camera Barcode Scanner ---
window.__mdBarcodeStream = null;
window.__mdBarcodeScanTimer = null;

window.closeWebRtcBarcodeScanner = function () {
    if (window.__mdBarcodeScanTimer) {
        clearInterval(window.__mdBarcodeScanTimer);
        window.__mdBarcodeScanTimer = null;
    }
    if (window.__mdBarcodeStream) {
        try {
            window.__mdBarcodeStream.getTracks().forEach(t => t.stop());
        } catch (e) {}
        window.__mdBarcodeStream = null;
    }
    const modal = document.getElementById('md-webrtc-barcode-modal');
    if (modal) modal.style.display = 'none';
};

window.openWebRtcBarcodeScanner = async function (targetInputId = 'item-barcode') {
    let modal = document.getElementById('md-webrtc-barcode-modal');
    if (!modal) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="md-webrtc-barcode-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.85); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                <div class="md-child-modal-card" style="background:#0f172a; border:1.5px solid rgba(56,189,248,0.5); border-radius:16px; width:100%; max-width:460px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.85);">
                    <div style="padding:12px 16px; background:rgba(56,189,248,0.14); border-bottom:1px solid rgba(56,189,248,0.3); display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0; color:#38bdf8; font-size:1rem; font-weight:900;">📷 مسح الباركود بالكاميرا</h3>
                        <button type="button" onclick="closeWebRtcBarcodeScanner()" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 10px; font-weight:800; cursor:pointer;">✖ <span class="kbd-hint">Esc</span></button>
                    </div>
                    <div style="padding:14px 16px; display:flex; flex-direction:column; gap:10px; align-items:center;">
                        <div style="position:relative; width:100%; height:240px; background:#020617; border-radius:12px; overflow:hidden; border:1px solid rgba(56,189,248,0.35); display:flex; align-items:center; justify-content:center;">
                            <video id="md-webrtc-video" playsinline muted style="width:100%; height:100%; object-fit:cover;"></video>
                            <div style="position:absolute; inset:20% 12%; border:2px dashed rgba(52,211,153,0.85); border-radius:10px; pointer-events:none; box-shadow:0 0 0 9999px rgba(0,0,0,0.32);"></div>
                            <div id="md-webrtc-status" style="position:absolute; bottom:8px; right:10px; left:10px; text-align:center; font-size:0.78rem; font-weight:800; color:#38bdf8; background:rgba(2,6,23,0.75); padding:4px 8px; border-radius:6px;">وجّه الكاميرا نحو الباركود...</div>
                        </div>
                        <div style="display:flex; gap:8px; width:100%;">
                            <input type="text" id="md-webrtc-manual-code" placeholder="أو اكتب/امسح الباركود هنا ثم Enter..." style="flex:1; height:38px; padding:0 12px; border-radius:9px; border:1px solid rgba(56,189,248,0.4); font-weight:800; text-align:center;">
                            <button type="button" id="md-webrtc-apply-btn" style="height:38px; padding:0 16px; border-radius:9px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; font-weight:900; cursor:pointer;">إدراج</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        modal = document.getElementById('md-webrtc-barcode-modal');
    }
    modal.style.display = 'flex';
    const video = document.getElementById('md-webrtc-video');
    const statusEl = document.getElementById('md-webrtc-status');
    const manualInp = document.getElementById('md-webrtc-manual-code');
    const applyBtn = document.getElementById('md-webrtc-apply-btn');

    const applyDetectedBarcode = (codeStr) => {
        const clean = String(codeStr || '').trim();
        if (!clean) return;
        const targetEl = document.getElementById(targetInputId);
        if (targetEl) {
            targetEl.value = clean;
            targetEl.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (typeof updateLiveItemQRPreview === 'function') updateLiveItemQRPreview();
        window.closeWebRtcBarcodeScanner();
        if (typeof showToast === 'function') showToast(`✅ تم قراءة الباركود: ${clean}`, 'success', 2000);
    };

    if (manualInp) {
        manualInp.value = '';
        manualInp.onkeydown = (ev) => {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                applyDetectedBarcode(manualInp.value);
            }
        };
        setTimeout(() => manualInp.focus(), 50);
    }
    if (applyBtn) {
        applyBtn.onclick = () => applyDetectedBarcode(manualInp ? manualInp.value : '');
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        if (statusEl) statusEl.textContent = '⚠️ الكاميرا غير متاحة في هذا المتصفح — استخدم الإدخال المباشر بالأسفل';
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' }, width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        });
        window.__mdBarcodeStream = stream;
        if (video) {
            video.srcObject = stream;
            await video.play();
        }
        if ('BarcodeDetector' in window) {
            const detector = new window.BarcodeDetector({
                formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'qr_code', 'upc_a', 'upc_e', 'itf']
            });
            window.__mdBarcodeScanTimer = setInterval(async () => {
                if (!video || video.readyState < 2) return;
                try {
                    const barcodes = await detector.detect(video);
                    if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                        applyDetectedBarcode(barcodes[0].rawValue);
                    }
                } catch (err) {}
            }, 220);
        } else if (statusEl) {
            statusEl.textContent = '📷 الكاميرا نشطة — امسح بالقارئ أو اكتب الكود بالأسفل';
        }
    } catch (camErr) {
        if (statusEl) statusEl.textContent = '⚠️ تعذر فتح الكاميرا — يمكنك كتابة أو لصق الباركود بالأسفل';
    }
};

// --- 5.2 Bulk Price Updater (التعديل الجماعي السريع للأسعار) ---
window.openBulkPriceUpdaterModal = function () {
    let modal = document.getElementById('md-bulk-price-modal');
    if (!modal) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="md-bulk-price-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.85); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                <div class="md-child-modal-card" style="background:#0f172a; border:1.5px solid rgba(245,158,11,0.5); border-radius:16px; width:100%; max-width:540px; overflow:hidden; box-shadow:0 20px 55px rgba(0,0,0,0.85);">
                    <div style="padding:14px 18px; background:rgba(245,158,11,0.15); border-bottom:1px solid rgba(245,158,11,0.3); display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0; color:#fbbf24; font-size:1.05rem; font-weight:900;">📈 التعديل الجماعي السريع للأسعار</h3>
                        <button type="button" onclick="document.getElementById('md-bulk-price-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 12px; font-weight:800; cursor:pointer;">✖ <span class="kbd-hint">Esc</span></button>
                    </div>
                    <div style="padding:16px 18px; display:flex; flex-direction:column; gap:12px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div class="input-group" style="margin:0;">
                                <label style="font-size:0.82rem; font-weight:800;">الفئة المستهدفة</label>
                                <select id="md-bulk-price-cat" onchange="previewBulkPriceUpdate()" style="height:38px; border-radius:9px; padding:0 10px; font-weight:800;"></select>
                            </div>
                            <div class="input-group" style="margin:0;">
                                <label style="font-size:0.82rem; font-weight:800;">طريقة التعديل</label>
                                <select id="md-bulk-price-mode" onchange="previewBulkPriceUpdate()" style="height:38px; border-radius:9px; padding:0 10px; font-weight:800;">
                                    <option value="percent">نسبة مئوية (% زيادة أو خصم)</option>
                                    <option value="usd_rate">وفق تغير سعر صرف الدولار</option>
                                    <option value="fixed">مبلغ ثابت (+ أو -)</option>
                                </select>
                            </div>
                        </div>
                        <div id="md-bulk-price-inputs-wrap" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div class="input-group" style="margin:0;" id="md-bulk-val1-box">
                                <label id="md-bulk-val1-label" style="font-size:0.82rem; font-weight:800;">النسبة % (مثال: 10 أو -5)</label>
                                <input type="number" id="md-bulk-val1" step="any" value="10" oninput="previewBulkPriceUpdate()" style="height:38px; border-radius:9px; padding:0 10px; font-weight:800; text-align:center;">
                            </div>
                            <div class="input-group" style="margin:0; display:none;" id="md-bulk-val2-box">
                                <label style="font-size:0.82rem; font-weight:800;">سعر الصرف الجديد</label>
                                <input type="number" id="md-bulk-val2" step="any" value="1" oninput="previewBulkPriceUpdate()" style="height:38px; border-radius:9px; padding:0 10px; font-weight:800; text-align:center;">
                            </div>
                            <div class="input-group" style="margin:0;">
                                <label style="font-size:0.82rem; font-weight:800;">تقريب السعر الجديد</label>
                                <select id="md-bulk-round" onchange="previewBulkPriceUpdate()" style="height:38px; border-radius:9px; padding:0 10px; font-weight:800;">
                                    <option value="1">بدون تقريب (أقرب 1)</option>
                                    <option value="10">لأقرب 10</option>
                                    <option value="50">لأقرب 50</option>
                                    <option value="100" selected>لأقرب 100</option>
                                    <option value="500">لأقرب 500</option>
                                </select>
                            </div>
                        </div>
                        <div id="md-bulk-price-preview" style="padding:10px 12px; border-radius:10px; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); font-size:0.84rem; font-weight:800; text-align:center;"></div>
                        <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:4px;">
                            <button type="button" onclick="document.getElementById('md-bulk-price-modal').style.display='none'" style="height:38px; padding:0 14px; border-radius:9px; background:rgba(148,163,184,0.15); color:#cbd5e1; border:1px solid rgba(148,163,184,0.3); font-weight:800; cursor:pointer;">إلغاء</button>
                            <button type="button" onclick="executeBulkPriceUpdate()" style="height:38px; padding:0 20px; border-radius:9px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; font-weight:900; cursor:pointer;">⚡ اعتماد وتحديث الأسعار</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        modal = document.getElementById('md-bulk-price-modal');
    }
    const catSel = document.getElementById('md-bulk-price-cat');
    if (catSel) {
        let cats = [];
        try { cats = JSON.parse(localStorage.getItem('erp_categories_v1') || '[]'); } catch (e) {}
        catSel.innerHTML = `<option value="__ALL__">📦 كل الفئات والأصناف</option>` + cats.map(c => `<option value="${c}">${c}</option>`).join('');
    }
    modal.style.display = 'flex';
    window.previewBulkPriceUpdate();
};

window.previewBulkPriceUpdate = function () {
    const cat = document.getElementById('md-bulk-price-cat')?.value || '__ALL__';
    const mode = document.getElementById('md-bulk-price-mode')?.value || 'percent';
    const v1Box = document.getElementById('md-bulk-val1-box');
    const v2Box = document.getElementById('md-bulk-val2-box');
    const v1Label = document.getElementById('md-bulk-val1-label');
    if (mode === 'usd_rate') {
        if (v1Label) v1Label.textContent = 'سعر الصرف السابق';
        if (v2Box) v2Box.style.display = 'block';
    } else if (mode === 'fixed') {
        if (v1Label) v1Label.textContent = 'المبلغ المضاف (+ أو -)';
        if (v2Box) v2Box.style.display = 'none';
    } else {
        if (v1Label) v1Label.textContent = 'النسبة % (مثال: 10 أو -5)';
        if (v2Box) v2Box.style.display = 'none';
    }
    let items = [];
    try { items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]').filter(i => i && !i.deleted); } catch (e) {}
    const matched = cat === '__ALL__' ? items : items.filter(i => i.category === cat);
    const previewEl = document.getElementById('md-bulk-price-preview');
    if (previewEl) {
        previewEl.textContent = `سيتم تحديث أسعار (${matched.length}) صنف ووحداتها الفرعية فوراً مع حفظ السجل التاريخي`;
    }
};

window.executeBulkPriceUpdate = function () {
    const cat = document.getElementById('md-bulk-price-cat')?.value || '__ALL__';
    const mode = document.getElementById('md-bulk-price-mode')?.value || 'percent';
    const v1 = parseFloat(document.getElementById('md-bulk-val1')?.value) || 0;
    const v2 = parseFloat(document.getElementById('md-bulk-val2')?.value) || 0;
    const roundStep = parseFloat(document.getElementById('md-bulk-round')?.value) || 1;

    if (mode === 'usd_rate' && (v1 <= 0 || v2 <= 0)) {
        if (typeof showToast === 'function') showToast('⚠️ أدخل سعر الصرف السابق والجديد بشكل صحيح', 'error', 2000);
        return;
    }
    if (mode !== 'usd_rate' && v1 === 0) {
        if (typeof showToast === 'function') showToast('⚠️ أدخل قيمة التعديل أولاً', 'error', 2000);
        return;
    }

    const calcNew = (oldPrice) => {
        const p = parseFloat(oldPrice) || 0;
        if (p <= 0) return 0;
        let next = p;
        if (mode === 'percent') next = p * (1 + (v1 / 100));
        else if (mode === 'usd_rate') next = p * (v2 / v1);
        else if (mode === 'fixed') next = p + v1;
        if (next < 0) next = 0;
        return roundStep > 1 ? Math.round(next / roundStep) * roundStep : Math.round(next * 100) / 100;
    };

    let items = [];
    try { items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]'); } catch (e) {}
    let invList = [];
    try { invList = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]'); } catch (e) {}
    let priceHist = [];
    try { priceHist = JSON.parse(localStorage.getItem('erp_item_price_history_v1') || '[]'); } catch (e) {}

    let updatedCount = 0;
    const nowIso = new Date().toISOString();

    items.forEach(item => {
        if (!item || item.deleted) return;
        if (cat !== '__ALL__' && item.category !== cat) return;
        const oldSell = parseFloat(item.sellingPrice || item.price || (item.units && item.units[0] ? item.units[0].cashPrice : 0)) || 0;
        const newSell = calcNew(oldSell);
        if (newSell > 0) {
            item.sellingPrice = newSell;
            item.price = newSell;
        }
        if (Array.isArray(item.units)) {
            item.units.forEach(u => {
                if (!u) return;
                if (parseFloat(u.cashPrice) > 0) u.cashPrice = calcNew(u.cashPrice);
                if (parseFloat(u.creditPrice) > 0) u.creditPrice = calcNew(u.creditPrice);
                if (parseFloat(u.minPrice) > 0) u.minPrice = calcNew(u.minPrice);
            });
        }
        const invHit = invList.find(iv => iv && (String(iv.id) === String(item.id) || String(iv.itemId) === String(item.id)));
        if (invHit && newSell > 0) {
            invHit.sellingPrice = newSell;
            invHit.price = newSell;
        }
        priceHist.unshift({
            itemId: item.id,
            itemName: item.name,
            costPrice: parseFloat(item.costPrice || item.cost || 0) || 0,
            sellingPrice: newSell,
            source: 'تعديل جماعي للأسعار',
            date: nowIso
        });
        updatedCount++;
    });

    if (priceHist.length > 500) priceHist = priceHist.slice(0, 500);
    setMasterLocalData('erp_items_v1', items, true);
    setMasterLocalData('erp_inventory_v1', invList, false);
    localStorage.setItem('erp_item_price_history_v1', JSON.stringify(priceHist));
    syncToBackend('items_bulk', items);
    logMasterDataAudit('تعديل جماعي للأسعار', 'الأصناف', cat === '__ALL__' ? 'كل الفئات' : cat, `تحديث ${updatedCount} صنف`);

    const modal = document.getElementById('md-bulk-price-modal');
    if (modal) modal.style.display = 'none';
    if (typeof renderItems === 'function') renderItems();
    if (typeof window.triggerMasterSavePulse === 'function') window.triggerMasterSavePulse('#tab-items .md-card');
    if (typeof showToast === 'function') showToast(`✅ تم تحديث أسعار ${updatedCount} صنف بنجاح`, 'success', 2000);
};

// --- 5.3 Customer & Supplier Digital QR + PIN Card ---
window.openEntityQrCardModal = function (prefixOrType = 'cust', entityId = '') {
    const isSup = (prefixOrType === 'sup' || prefixOrType === 'supplier');
    let name = '', pin = '', phone = '', code = '';

    if (entityId) {
        const listKey = isSup ? 'erp_suppliers_v1' : 'erp_customers_v1';
        let list = [];
        try { list = JSON.parse(localStorage.getItem(listKey) || '[]'); } catch (e) {}
        const found = list.find(x => x && String(x.id) === String(entityId));
        if (found) {
            name = found.name || '';
            pin = String(found.statementPin || found.receiptCodeNum || '');
            phone = found.whatsapp || found.phone || '';
            code = found.id || found.code || '';
        }
    } else {
        const p = isSup ? 'sup' : 'cust';
        name = (document.getElementById(`${p}-name`)?.value || '').trim();
        pin = (document.getElementById(`${p}-statement-pin`)?.value || '').trim();
        phone = (document.getElementById(`${p}-whatsapp`)?.value || document.getElementById(`${p}-phone`)?.value || '').trim();
        code = (document.getElementById(`${p}-id`)?.value || '').trim() || (isSup ? 'SUP-NEW' : 'CUST-NEW');
    }

    if (!name) {
        const fieldId = isSup ? 'sup-name' : 'cust-name';
        return focusRequiredMasterField(fieldId, `⚠️ اختر أو اكتب اسم ${isSup ? 'المورد' : 'العميل'} أولاً لإصدار بطاقة QR`);
    }
    if (!pin) pin = getNextUniqueStatementPin('');

    let modal = document.getElementById('md-entity-qr-card-modal');
    if (!modal) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="md-entity-qr-card-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.85); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                <div class="md-child-modal-card" style="background:#0f172a; border:1.5px solid rgba(16,185,129,0.5); border-radius:16px; width:100%; max-width:440px; overflow:hidden; box-shadow:0 20px 55px rgba(0,0,0,0.85);">
                    <div style="padding:12px 16px; background:rgba(16,185,129,0.15); border-bottom:1px solid rgba(16,185,129,0.3); display:flex; justify-content:space-between; align-items:center;">
                        <h3 id="md-qr-card-title" style="margin:0; color:#34d399; font-size:1rem; font-weight:900;">🪪 بطاقة QR التعريفية</h3>
                        <button type="button" onclick="document.getElementById('md-entity-qr-card-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 10px; font-weight:800; cursor:pointer;">✖ <span class="kbd-hint">Esc</span></button>
                    </div>
                    <div style="padding:16px; display:flex; flex-direction:column; align-items:center; gap:12px;">
                        <div id="md-qr-card-visual" style="width:100%; border-radius:14px; padding:16px; background:linear-gradient(135deg,#0f172a,#1e293b); border:2px solid #10b981; text-align:center; display:flex; flex-direction:column; align-items:center; gap:8px;">
                            <div style="font-size:0.8rem; font-weight:800; color:#38bdf8;">بطاقة الحساب الذكية • كشف الحساب الفوري</div>
                            <div id="md-qr-card-name" style="font-size:1.15rem; font-weight:900; color:#ffffff;"></div>
                            <div style="background:#ffffff; padding:8px; border-radius:12px; display:inline-block;">
                                <img id="md-qr-card-img" src="" alt="QR" style="width:135px; height:135px; display:block;">
                            </div>
                            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top:4px;">
                                <span id="md-qr-card-pin" style="background:rgba(16,185,129,0.2); color:#34d399; border:1px solid rgba(16,185,129,0.45); padding:4px 12px; border-radius:8px; font-weight:900; font-family:monospace; font-size:0.95rem;"></span>
                                <span id="md-qr-card-phone" style="background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:4px 12px; border-radius:8px; font-weight:800; font-family:monospace; font-size:0.88rem; direction:ltr;"></span>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px; width:100%; justify-content:center; flex-wrap:wrap;">
                            <button type="button" id="md-qr-send-wa-btn" style="flex:1; height:38px; border-radius:9px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; font-weight:900; cursor:pointer;">💬 إرسال بالواتساب</button>
                            <button type="button" id="md-qr-print-btn" style="height:38px; padding:0 14px; border-radius:9px; background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); font-weight:800; cursor:pointer;">🖨️ طباعة</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        modal = document.getElementById('md-entity-qr-card-modal');
    }

    const qrPayload = `MAGAIES|${isSup ? 'SUP' : 'CUST'}|${code}|${name}|PIN:${pin}`;
    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(qrPayload);

    document.getElementById('md-qr-card-title').textContent = `🪪 بطاقة QR — ${isSup ? 'مورد' : 'عميل'}`;
    document.getElementById('md-qr-card-name').textContent = name;
    document.getElementById('md-qr-card-img').src = qrUrl;
    document.getElementById('md-qr-card-pin').textContent = `🔐 الرقم السري: ${pin}`;
    document.getElementById('md-qr-card-phone').textContent = phone || code;

    const waBtn = document.getElementById('md-qr-send-wa-btn');
    if (waBtn) {
        waBtn.onclick = () => {
            const msg = `*بطاقة الحساب الذكية* 🪪\nالاسم: *${name}*\nالرقم السري لكشف الحساب الآلي: *${pin}*\nأرسل الرقم السري (${pin}) في أي وقت ليصلك كشف حسابك فوراً.`;
            const cleanDigits = String(phone || '').replace(/\D/g, '').replace(/^00/, '');
            const waUrl = cleanDigits ? `https://wa.me/${cleanDigits}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
        };
    }
    const prBtn = document.getElementById('md-qr-print-btn');
    if (prBtn) {
        prBtn.onclick = () => {
            const cardHtml = document.getElementById('md-qr-card-visual')?.outerHTML || '';
            const w = window.open('', '_blank', 'width=460,height=520');
            if (w) {
                w.document.write(`<html dir="rtl"><head><title>بطاقة QR - ${name}</title><style>body{font-family:'Tajawal',sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:#fff;}</style></head><body onload="window.print()">${cardHtml}</body></html>`);
                w.document.close();
            }
        };
    }

    modal.style.display = 'flex';
};

// --- 5.4 Smart Excel Clipboard Paste Grid (الاستيراد الذكي بالنسخ واللصق المباشر من Excel) ---
window.openSmartClipboardPasteModal = function (defaultType = '') {
    if (!defaultType) {
        const activeTab = document.querySelector('.md-content-area .md-tab-content.active');
        const tid = activeTab ? activeTab.id : '';
        if (tid === 'tab-customers') defaultType = 'customer';
        else if (tid === 'tab-suppliers') defaultType = 'supplier';
        else if (tid === 'tab-warehouses') defaultType = 'warehouse';
        else defaultType = 'item';
    }
    let modal = document.getElementById('md-clipboard-paste-modal');
    if (!modal) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="md-clipboard-paste-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.85); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                <div class="md-child-modal-card" style="background:#0f172a; border:1.5px solid rgba(56,189,248,0.5); border-radius:16px; width:100%; max-width:680px; overflow:hidden; box-shadow:0 20px 55px rgba(0,0,0,0.85);">
                    <div style="padding:14px 18px; background:rgba(56,189,248,0.15); border-bottom:1px solid rgba(56,189,248,0.3); display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0; color:#38bdf8; font-size:1.05rem; font-weight:900;">📋 الاستيراد الذكي باللصق المباشر من Excel (Ctrl+V)</h3>
                        <button type="button" onclick="document.getElementById('md-clipboard-paste-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 12px; font-weight:800; cursor:pointer;">✖ <span class="kbd-hint">Esc</span></button>
                    </div>
                    <div style="padding:16px 18px; display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                            <select id="md-paste-entity-type" onchange="updateClipboardPasteHint()" style="height:36px; border-radius:9px; padding:0 12px; font-weight:800; min-width:190px;">
                                <option value="item">📦 الأصناف (الاسم | الفئة | الوحدة | السعر | الكمية)</option>
                                <option value="customer">🤝 العملاء (الاسم | الهاتف | الرصيد الافتتاحي | حد الائتمان)</option>
                                <option value="supplier">🚛 الموردين (الاسم | الهاتف | الرصيد الافتتاحي | حد الائتمان)</option>
                                <option value="warehouse">🏢 المخازن (اسم المخزن | النوع | الموقع | أمين المخزن)</option>
                            </select>
                            <span id="md-paste-count-badge" style="font-size:0.82rem; font-weight:900; color:#34d399;">0 صف جاهز للاستيراد</span>
                        </div>
                        <textarea id="md-paste-raw-area" oninput="previewClipboardPasteRows()" placeholder="انسخ الصفوف من Excel ثم اضغط Ctrl+V هنا..." style="width:100%; height:115px; border-radius:10px; padding:10px; font-family:monospace; font-size:0.85rem; resize:vertical;"></textarea>
                        <div id="md-paste-preview-box" style="max-height:170px; overflow:auto; border-radius:10px; border:1px solid rgba(148,163,184,0.25);"></div>
                        <div style="display:flex; justify-content:flex-end; gap:8px;">
                            <button type="button" onclick="document.getElementById('md-clipboard-paste-modal').style.display='none'" style="height:38px; padding:0 14px; border-radius:9px; background:rgba(148,163,184,0.15); color:#cbd5e1; border:1px solid rgba(148,163,184,0.3); font-weight:800; cursor:pointer;">إلغاء</button>
                            <button type="button" onclick="executeSmartClipboardImport()" style="height:38px; padding:0 20px; border-radius:9px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; font-weight:900; cursor:pointer;">📥 استيراد وحفظ الصفوف</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        modal = document.getElementById('md-clipboard-paste-modal');
    }
    const typeSel = document.getElementById('md-paste-entity-type');
    if (typeSel) typeSel.value = defaultType;
    const rawArea = document.getElementById('md-paste-raw-area');
    if (rawArea) rawArea.value = '';
    modal.style.display = 'flex';
    window.updateClipboardPasteHint();
    setTimeout(() => rawArea?.focus(), 40);
};

window.parseClipboardRawLines = function () {
    const raw = (document.getElementById('md-paste-raw-area')?.value || '').trim();
    if (!raw) return [];
    return raw.split(/\r?\n/).map(line => {
        const sep = line.includes('\t') ? '\t' : (line.includes(';') ? ';' : ',');
        return line.split(sep).map(c => c.trim().replace(/^"|"$/g, ''));
    }).filter(cols => cols.length > 0 && cols[0] !== '');
};

window.updateClipboardPasteHint = function () {
    window.previewClipboardPasteRows();
};

window.previewClipboardPasteRows = function () {
    const rows = window.parseClipboardRawLines();
    const badge = document.getElementById('md-paste-count-badge');
    const box = document.getElementById('md-paste-preview-box');
    if (badge) badge.textContent = `${rows.length} صف جاهز للاستيراد`;
    if (!box) return;
    if (rows.length === 0) {
        box.innerHTML = `<div style="padding:12px; text-align:center; font-size:0.82rem; opacity:0.75;">انسخ أي جدول من Excel والصقه بالأعلى لمعاينته فوراً</div>`;
        return;
    }
    const previewSlice = rows.slice(0, 15);
    box.innerHTML = `<table style="width:100%; border-collapse:collapse; font-size:0.8rem; text-align:right;">
        <tbody>${previewSlice.map((r, idx) => `<tr style="border-bottom:1px solid rgba(148,163,184,0.15);">
            <td style="padding:5px 8px; font-weight:900; color:#38bdf8; width:34px;">#${idx + 1}</td>
            ${r.slice(0, 5).map(cell => `<td style="padding:5px 8px;">${cell}</td>`).join('')}
        </tr>`).join('')}</tbody>
    </table>`;
};

window.executeSmartClipboardImport = function () {
    const type = document.getElementById('md-paste-entity-type')?.value || 'item';
    const rows = window.parseClipboardRawLines();
    if (rows.length === 0) {
        if (typeof showToast === 'function') showToast('⚠️ الصق صفاً واحداً على الأقل من Excel', 'error', 2000);
        return;
    }

    let added = 0;
    if (type === 'item') {
        let items = [];
        try { items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]'); } catch (e) {}
        rows.forEach((r, i) => {
            const name = (r[0] || '').trim();
            if (!name || items.some(x => x && !x.deleted && x.name === name)) return;
            const category = (r[1] || 'عام').trim();
            const baseUnit = (r[2] || 'حبة').trim();
            const sellingPrice = parseFloat(String(r[3] || '0').replace(/,/g, '')) || 0;
            const openingQty = parseFloat(String(r[4] || '0').replace(/,/g, '')) || 0;
            const costPrice = parseFloat(String(r[5] || '0').replace(/,/g, '')) || 0;
            const id = 'ITM-' + (Date.now() + i);
            items.push({
                id, code: id, name, category, baseUnit, unit: baseUnit,
                sellingPrice, price: sellingPrice, costPrice, cost: costPrice,
                openingQty, stock: openingQty, currentStock: openingQty, quantity: openingQty,
                status: 'active',
                units: [{ unitName: baseUnit, factor: 1, cashPrice: sellingPrice, creditPrice: sellingPrice, minPrice: sellingPrice, stock: openingQty }]
            });
            added++;
        });
        setMasterLocalData('erp_items_v1', items, true);
        syncToBackend('items_bulk', items);
        if (typeof renderItems === 'function') renderItems();
    } else if (type === 'customer' || type === 'supplier') {
        const isSup = type === 'supplier';
        const key = isSup ? 'erp_suppliers_v1' : 'erp_customers_v1';
        let list = [];
        try { list = JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) {}
        rows.forEach((r, i) => {
            const name = (r[0] || '').trim();
            if (!name || list.some(x => x && !x.deleted && x.name === name)) return;
            const phone = (r[1] || '').trim();
            const opBal = parseFloat(String(r[2] || '0').replace(/,/g, '')) || 0;
            const limit = parseFloat(String(r[3] || '0').replace(/,/g, '')) || 0;
            const id = (isSup ? 'SUP-' : 'CUST-') + (Date.now() + i);
            const pin = getNextUniqueStatementPin('');
            list.push({
                id, code: id, name, phone, whatsapp: phone,
                statementPin: pin,
                openingDebit: (!isSup && opBal > 0) || (isSup && opBal < 0) ? Math.abs(opBal) : 0,
                openingCredit: (!isSup && opBal < 0) || (isSup && opBal > 0) ? Math.abs(opBal) : 0,
                currentBalance: opBal, balance: opBal, creditLimit: limit,
                currency: 'SDG', status: 'active', securityLevel: 1,
                allowWhatsAppStatement: true, statementMode: 'detailed'
            });
            added++;
        });
        setMasterLocalData(key, list, true);
        syncToBackend(isSup ? 'suppliers' : 'customers', list);
        if (isSup && typeof renderSuppliers === 'function') renderSuppliers();
        if (!isSup && typeof renderCustomers === 'function') renderCustomers();
    } else if (type === 'warehouse') {
        let whs = [];
        try { whs = JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]'); } catch (e) {}
        rows.forEach((r, i) => {
            const name = (r[0] || '').trim();
            if (!name || whs.some(x => x && !x.deleted && x.name === name)) return;
            const id = 'WH-' + (Date.now() + i);
            whs.push({
                id, name,
                type: (r[1] || 'مستودع رئيسي').trim(),
                location: (r[2] || '').trim(),
                keeper: (r[3] || '').trim(),
                status: 'active', security: '1'
            });
            added++;
        });
        setMasterLocalData('erp_warehouses_v1', whs, true);
        syncToBackend('warehouses', whs);
        if (typeof renderWarehouses === 'function') renderWarehouses();
    }

    logMasterDataAudit('استيراد لصق مباشر', type, `إضافة ${added} سجل`, `من أصل ${rows.length} صف ملصوق`);
    const modal = document.getElementById('md-clipboard-paste-modal');
    if (modal) modal.style.display = 'none';
    if (typeof showToast === 'function') showToast(`✅ تم استيراد وحفظ (${added}) سجل جديد بنجاح`, 'success', 2000);
};

// ════════════════════════════════════════════════════════════════════════════
// 👑 6. THIRD CONSTITUTION ENGINE:
//    - Live Dynamic Ledger Balance & Running Cumulative Statement (0% Rounding Error)
//    - Nested Drill-Down Statement & Origin Document Inspector (Screen inside Screen)
//    - World-Class Print Engine (A4 Official + Thermal 80mm + PDF + Excel + WhatsApp)
//    - Instant Unit Factor & Opening Inventory Total Auto-Calculator (0ms)
// ════════════════════════════════════════════════════════════════════════════
(function initThirdConstitutionMasterEngines() {
    const round2 = (n) => Math.round((parseFloat(n) || 0) * 100) / 100;

    window.collectEntityLedgerMovements = function (entityType, entity) {
        if (!entity) return { openingDebit: 0, openingCredit: 0, openingNet: 0, rows: [] };
        const isSup = (entityType === 'supplier' || entityType === 'sup');
        const opD = round2(entity.openingDebit || (!isSup && parseFloat(entity.openingBalance) > 0 ? entity.openingBalance : (isSup && parseFloat(entity.openingBalance) < 0 ? Math.abs(entity.openingBalance) : 0)));
        const opC = round2(entity.openingCredit || (!isSup && parseFloat(entity.openingBalance) < 0 ? Math.abs(entity.openingBalance) : (isSup && parseFloat(entity.openingBalance) > 0 ? entity.openingBalance : 0)));
        const openingNet = isSup ? round2(opC - opD) : round2(opD - opC);

        const entId = String(entity.id || '');
        const entName = String(entity.name || '').trim().toLowerCase();
        const rows = [];
        const seenDocKeys = new Set();

        const isValMatch = (val) => {
            if (!val) return false;
            const s = String(val).trim();
            if (entId && (s === entId || s === 'cust-' + entId || s === 'supp-' + entId)) return true;
            if (entName && s.toLowerCase() === entName) return true;
            return false;
        };

        const matchesEntity = (doc, idFields, nameFields) => {
            if (!doc || doc.deleted || doc.isDeleted) return false;
            for (const f of idFields) {
                if (isValMatch(doc[f])) return true;
            }
            for (const nf of nameFields) {
                if (isValMatch(doc[nf])) return true;
            }
            return false;
        };

        try {
            const isBankOrSafe = (entityType === 'bank' || entityType === 'safe');
            if (isBankOrSafe) {
                const vouchers = JSON.parse(localStorage.getItem('erp_vouchers_v1') || '[]') || [];
                vouchers.forEach(v => {
                    if (!matchesEntity(v, ['safeId', 'bankId', 'treasuryId', 'fromAccount', 'toAccount', 'accountId'], ['safeName', 'bankName', 'treasuryName', 'paymentMethodName'])) return;
                    const amt = round2(v.amount ?? v.total ?? 0);
                    if (amt <= 0) return;
                    const isReceipt = (v.type === 'receipt' || v.voucherType === 'receipt' || String(v.type || '').includes('قبض'));
                    const docNo = v.voucherNo || v.voucherNumber || v.number || v.id || 'VCH';
                    const dedupKey = `BS_${isReceipt ? 'REC' : 'PAY'}_${docNo}_${amt}`;
                    if (seenDocKeys.has(dedupKey)) return;
                    seenDocKeys.add(dedupKey);
                    const dt = (v.date || v.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                    rows.push({
                        date: dt, docNo, type: isReceipt ? 'إيداع / قبض' : 'سحب / صرف',
                        debit: isReceipt ? amt : 0, credit: isReceipt ? 0 : amt,
                        note: v.description || v.notes || v.note || (v.partyName || v.entityName || ''),
                        raw: v
                    });
                });
            } else if (!isSup) {
                const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || localStorage.getItem('erp_invoices_v1') || '[]') || [];
                sales.forEach(inv => {
                    if (!matchesEntity(inv, ['customerId', 'clientId', 'entityId', 'customer', 'client'], ['customerName', 'clientName', 'customer', 'client'])) return;
                    const total = round2(inv.netTotal ?? inv.grandTotal ?? inv.total ?? inv.amount ?? 0);
                    const paid = round2(inv.paidAmount ?? inv.paid ?? ((inv.paymentType === 'cash' || inv.paymentMethod === 'cash') ? total : 0));
                    const dt = (inv.date || inv.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                    const docNo = inv.invoiceNo || inv.invoiceNumber || inv.number || inv.id || 'INV';
                    if (total > 0) {
                        rows.push({ date: dt, docNo, type: 'فاتورة مبيعات', debit: total, credit: 0, note: inv.notes || 'مبيعات بضاعة', raw: inv });
                    }
                    if (paid > 0) {
                        rows.push({ date: dt, docNo: `${docNo}-سداد`, type: 'سداد فاتورة', debit: 0, credit: paid, note: 'دفعة مسددة مع الفاتورة', raw: inv });
                    }
                });
                const salesReturns = JSON.parse(localStorage.getItem('erp_sales_returns_v1') || '[]') || [];
                salesReturns.forEach(sr => {
                    if (!matchesEntity(sr, ['customerId', 'clientId', 'entityId', 'customer'], ['customerName', 'clientName', 'customer'])) return;
                    const total = round2(sr.netTotal ?? sr.grandTotal ?? sr.total ?? sr.amount ?? 0);
                    if (total > 0) {
                        const dt = (sr.date || sr.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                        const docNo = sr.returnNo || sr.invoiceNo || sr.id || 'SR';
                        rows.push({ date: dt, docNo, type: 'مرتجع مبيعات', debit: 0, credit: total, note: sr.notes || 'مرتجع بضاعة من العميل', raw: sr });
                    }
                });
            } else {
                const p1 = JSON.parse(localStorage.getItem('erp_purchases_v1') || '[]') || [];
                const p2 = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]') || [];
                const seenPur = new Set();
                [...p1, ...p2].forEach(pur => {
                    if (!pur) return;
                    const docNo = pur.invoiceNo || pur.invoiceNumber || pur.number || pur.id || 'PUR';
                    if (seenPur.has(docNo)) return;
                    seenPur.add(docNo);
                    if (!matchesEntity(pur, ['supplierId', 'vendorId', 'entityId', 'supplier', 'customer'], ['supplierName', 'vendorName', 'supplier', 'customer'])) return;
                    const total = round2(pur.netTotal ?? pur.grandTotal ?? pur.total ?? pur.amount ?? 0);
                    const paid = round2(pur.paidAmount ?? pur.paid ?? ((pur.paymentType === 'cash' || pur.paymentMethod === 'cash') ? total : 0));
                    const dt = (pur.date || pur.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                    if (total > 0) {
                        rows.push({ date: dt, docNo, type: 'فاتورة مشتريات', debit: 0, credit: total, note: pur.notes || 'توريد مشتريات', raw: pur });
                    }
                    if (paid > 0) {
                        rows.push({ date: dt, docNo: `${docNo}-صرف`, type: 'سداد للمورد', debit: paid, credit: 0, note: 'دفعة مسددة للمورد', raw: pur });
                    }
                });
                const purReturns = JSON.parse(localStorage.getItem('erp_purchase_returns_v1') || '[]') || [];
                purReturns.forEach(pr => {
                    if (!matchesEntity(pr, ['supplierId', 'vendorId', 'entityId', 'supplier'], ['supplierName', 'vendorName', 'supplier'])) return;
                    const total = round2(pr.netTotal ?? pr.grandTotal ?? pr.total ?? pr.amount ?? 0);
                    if (total > 0) {
                        const dt = (pr.date || pr.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                        const docNo = pr.returnNo || pr.invoiceNo || pr.id || 'PR';
                        rows.push({ date: dt, docNo, type: 'مرتجع مشتريات', debit: total, credit: 0, note: pr.notes || 'رد بضاعة للمورد', raw: pr });
                    }
                });
            }

            if (!isBankOrSafe) {
                // Collect Single & Multi Receipts
                const recKeys = ['erp_single_receipts_v1', 'erp_receipts_v1'];
                recKeys.forEach(rk => {
                    const recs = JSON.parse(localStorage.getItem(rk) || '[]') || [];
                    if (!Array.isArray(recs)) return;
                    recs.forEach(r => {
                        if (!matchesEntity(r, ['fromAccount', 'accountId', 'partyId', 'customerId', 'supplierId', 'entityId'], ['fromAccountName', 'partyName', 'customerName', 'supplierName', 'entityName'])) return;
                        const amt = round2(r.amount ?? r.total ?? 0);
                        if (amt <= 0) return;
                        const docNo = r.voucherNo || r.voucherNumber || r.number || r.id || 'REC';
                        const dedupKey = `REC_${docNo}_${amt}`;
                        if (seenDocKeys.has(dedupKey)) return;
                        seenDocKeys.add(dedupKey);
                        const dt = (r.date || r.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                        rows.push({
                            date: dt, docNo, type: 'سند قبض',
                            debit: 0, credit: amt,
                            note: r.description || r.notes || r.note || 'تحصيل نقدي / بنكي',
                            raw: r
                        });
                    });
                });

                // Collect Single & Multi Payments
                const payKeys = ['erp_single_payments_v1', 'erp_payments_v1'];
                payKeys.forEach(pk => {
                    const pays = JSON.parse(localStorage.getItem(pk) || '[]') || [];
                    if (!Array.isArray(pays)) return;
                    pays.forEach(p => {
                        if (!matchesEntity(p, ['toAccount', 'accountId', 'partyId', 'customerId', 'supplierId', 'entityId'], ['toAccountName', 'partyName', 'customerName', 'supplierName', 'entityName'])) return;
                        const amt = round2(p.amount ?? p.total ?? 0);
                        if (amt <= 0) return;
                        const docNo = p.voucherNo || p.voucherNumber || p.number || p.id || 'PAY';
                        const dedupKey = `PAY_${docNo}_${amt}`;
                        if (seenDocKeys.has(dedupKey)) return;
                        seenDocKeys.add(dedupKey);
                        const dt = (p.date || p.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                        rows.push({
                            date: dt, docNo, type: 'سند صرف',
                            debit: amt, credit: 0,
                            note: p.description || p.notes || p.note || 'صرف نقدي / بنكي',
                            raw: p
                        });
                    });
                });

                const vouchers = JSON.parse(localStorage.getItem('erp_vouchers_v1') || '[]') || [];
                vouchers.forEach(v => {
                    if (!matchesEntity(v, ['entityId', 'customerId', 'supplierId', 'accountId', 'partyId', 'fromAccount', 'toAccount'], ['entityName', 'customerName', 'supplierName', 'partyName'])) return;
                    const amt = round2(v.amount ?? v.total ?? 0);
                    if (amt <= 0) return;
                    const isReceipt = (v.type === 'receipt' || v.voucherType === 'receipt' || String(v.type || '').includes('قبض'));
                    const docNo = v.voucherNo || v.voucherNumber || v.number || v.id || 'VCH';
                    const dedupKey = `${isReceipt ? 'REC' : 'PAY'}_${docNo}_${amt}`;
                    if (seenDocKeys.has(dedupKey)) return;
                    seenDocKeys.add(dedupKey);
                    const dt = (v.date || v.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                    rows.push({
                        date: dt,
                        docNo,
                        type: isReceipt ? 'سند قبض' : 'سند صرف',
                        debit: isReceipt ? 0 : amt,
                        credit: isReceipt ? amt : 0,
                        note: v.description || v.notes || v.note || '',
                        raw: v
                    });
                });
            }

            // Collect Journal Vouchers (including Netting JV-NET-..., Check Cycle JV-CHK-..., and Manual JVs)
            const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]') || [];
            jvs.forEach(jv => {
                if (!jv || jv.deleted || !Array.isArray(jv.lines)) return;
                const docNo = jv.voucherNo || jv.id || 'JV';
                const dt = (jv.date || jv.createdAt || '').toString().slice(0, 10) || new Date().toISOString().slice(0, 10);
                jv.lines.forEach((ln, lIdx) => {
                    const accStr = String(ln.accountName || ln.account || ln.partyName || '').trim().toLowerCase();
                    const idStr = String(ln.accountId || ln.partyId || ln.entityId || '').trim();
                    const isHit = (entId && idStr === entId) || (entName && accStr && (accStr === entName || accStr.includes(entName)));
                    if (!isHit) return;
                    const d = round2(ln.debit || 0);
                    const c = round2(ln.credit || 0);
                    if (d <= 0 && c <= 0) return;
                    const dedupKey = `JV_${docNo}_${lIdx}_${d}_${c}`;
                    if (seenDocKeys.has(dedupKey)) return;
                    seenDocKeys.add(dedupKey);
                    rows.push({
                        date: dt, docNo, type: 'قيد يومية / تسوية',
                        debit: d, credit: c,
                        note: ln.description || jv.description || jv.notes || 'قيد محاسبي',
                        raw: jv
                    });
                });
            });
        } catch (e) {}

        rows.sort((a, b) => String(a.date).localeCompare(String(b.date)));
        return { openingDebit: opD, openingCredit: opC, openingNet, rows };
    };

    window.computeLiveEntityLedgerBalance = function (entityType, entity) {
        if (!entity) return 0;
        const isSup = (entityType === 'supplier' || entityType === 'sup');
        const data = window.collectEntityLedgerMovements(entityType, entity);
        if (data.rows.length === 0) {
            const fallback = parseFloat(entity.currentBalance ?? entity.balance ?? entity.openingBalance);
            if (!isNaN(fallback) && fallback !== 0) return round2(fallback);
            return data.openingNet;
        }
        let running = data.openingNet;
        data.rows.forEach(r => {
            if (isSup) running = round2(running + (r.credit - r.debit));
            else running = round2(running + (r.debit - r.credit));
        });
        return running;
    };

    window.openEntityDrillDownStatementModal = function (entityType, entityId) {
        const isSup = (entityType === 'supplier' || entityType === 'sup');
        const listKeyMap = {
            'supplier': 'erp_suppliers_v1',
            'sup': 'erp_suppliers_v1',
            'customer': 'erp_customers_v1',
            'bank': 'erp_banks_v1',
            'safe': 'erp_safes_v1'
        };
        const listKey = listKeyMap[entityType] || 'erp_customers_v1';
        let list = [];
        try { list = JSON.parse(localStorage.getItem(listKey) || '[]') || []; } catch (e) {}
        const entity = list.find(x => x && String(x.id) === String(entityId));
        if (!entity) return;

        let modal = document.getElementById('md-entity-drilldown-modal');
        if (!modal) {
            document.body.insertAdjacentHTML('beforeend', `
                <div id="md-entity-drilldown-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.85); backdrop-filter:blur(9px); align-items:center; justify-content:center; padding:14px; direction:rtl; font-family:'Tajawal',sans-serif;">
                    <div class="md-child-modal-card" style="width:100%; max-width:960px; max-height:90vh; border:2.5px solid #38bdf8; border-radius:18px; box-shadow:0 25px 65px rgba(0,0,0,0.85); display:flex; flex-direction:column; overflow:hidden;">
                        <div class="md-modal-drag-handle" style="padding:13px 18px; background:rgba(56,189,248,0.14); border-bottom:2px solid rgba(56,189,248,0.35); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <h3 id="md-dd-title" style="margin:0; font-size:1.05rem; font-weight:900; color:#38bdf8;">📊 كشف الحساب التفصيلي والحركات (Drill-Down)</h3>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <button type="button" onclick="window.printCurrentEntityDrillDown('a4')" style="padding:5px 11px; border-radius:8px; background:rgba(56,189,248,0.2); color:#38bdf8; border:2px solid rgba(56,189,248,0.55); font-weight:800; font-size:0.78rem; cursor:pointer;">🖨️ طباعة A4 / PDF</button>
                                <button type="button" onclick="window.printCurrentEntityDrillDown('thermal')" style="padding:5px 11px; border-radius:8px; background:rgba(245,158,11,0.18); color:#fbbf24; border:2px solid rgba(245,158,11,0.55); font-weight:800; font-size:0.78rem; cursor:pointer;">🧾 كاشير 80mm</button>
                                <button type="button" onclick="window.exportCurrentEntityDrillDownExcel()" style="padding:5px 11px; border-radius:8px; background:rgba(16,185,129,0.2); color:#34d399; border:2px solid rgba(16,185,129,0.55); font-weight:800; font-size:0.78rem; cursor:pointer;">📤 Excel</button>
                                <button type="button" onclick="window.shareCurrentEntityDrillDownWhatsApp()" style="padding:5px 11px; border-radius:8px; background:linear-gradient(135deg,#10b981,#059669); color:#fff !important; border:none; font-weight:900; font-size:0.78rem; cursor:pointer;">💬 واتساب مفهرس</button>
                                <button type="button" onclick="document.getElementById('md-entity-drilldown-modal').style.display='none'" style="padding:5px 12px; border-radius:8px; background:rgba(239,68,68,0.2); color:#fca5a5; border:2px solid rgba(239,68,68,0.55); font-weight:800; font-size:0.78rem; cursor:pointer;">✖ <span class="kbd-hint">Esc</span></button>
                            </div>
                        </div>
                        <div style="padding:10px 18px; border-bottom:1.5px solid rgba(148,163,184,0.22); display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <input type="date" id="md-dd-from" onchange="window.renderEntityDrillDownTable()" style="height:34px; border-radius:8px; padding:0 8px; font-weight:800; font-size:0.8rem;">
                            <input type="date" id="md-dd-to" onchange="window.renderEntityDrillDownTable()" style="height:34px; border-radius:8px; padding:0 8px; font-weight:800; font-size:0.8rem;">
                            <input type="text" id="md-dd-search" oninput="window.renderEntityDrillDownTable()" placeholder="🔍 فلتر لحظي برقم المستند أو البيان أو المستخدم..." style="flex:1; min-width:180px; height:34px; border-radius:8px; padding:0 10px; font-weight:800; font-size:0.82rem;">
                        </div>
                        <div id="md-dd-body" style="padding:14px 18px; overflow-y:auto; flex:1;"></div>
                    </div>
                </div>
            `);
            modal = document.getElementById('md-entity-drilldown-modal');
        }
        window.__mdActiveDrillDown = { entityType, entity };
        const typeTitleAr = entityType === 'bank' ? 'البنك' : (entityType === 'safe' ? 'الخزينة' : (isSup ? 'المورد' : 'العميل'));
        document.getElementById('md-dd-title').textContent = `📊 كشف حساب وحركات ${typeTitleAr}: ${entity.name} (${entity.currency || 'SDG'})`;
        document.getElementById('md-dd-from').value = '';
        document.getElementById('md-dd-to').value = '';
        document.getElementById('md-dd-search').value = '';
        modal.style.display = 'flex';
        window.renderEntityDrillDownTable();
    };

    window.renderEntityDrillDownTable = function () {
        const ctx = window.__mdActiveDrillDown;
        if (!ctx || !ctx.entity) return;
        const { entityType, entity } = ctx;
        const isSup = (entityType === 'supplier' || entityType === 'sup');
        const data = window.collectEntityLedgerMovements(entityType, entity);
        const fromDt = document.getElementById('md-dd-from')?.value || '';
        const toDt = document.getElementById('md-dd-to')?.value || '';
        const q = (document.getElementById('md-dd-search')?.value || '').trim().toLowerCase();

        // البند 1 من الدستور 14: ضبط دقة رصيد أول المدة المرحّل لما قبل تاريخ البداية والرصيد التراكمي المتحرك والإجماليات بنسبة 100%
        let effOpeningDebit = data.openingDebit;
        let effOpeningCredit = data.openingCredit;
        let effOpeningNet = data.openingNet;

        data.rows.forEach((r) => {
            if (fromDt && r.date < fromDt) {
                effOpeningDebit = round2(effOpeningDebit + r.debit);
                effOpeningCredit = round2(effOpeningCredit + r.credit);
                if (isSup) effOpeningNet = round2(effOpeningNet + (r.credit - r.debit));
                else effOpeningNet = round2(effOpeningNet + (r.debit - r.credit));
            }
        });

        let running = effOpeningNet;
        let totDebit = effOpeningDebit;
        let totCredit = effOpeningCredit;
        const renderedRows = [];

        data.rows.forEach((r, idx) => {
            if (fromDt && r.date < fromDt) return;
            if (toDt && r.date > toDt) return;
            if (q && !(`${r.docNo} ${r.type} ${r.note}`).toLowerCase().includes(q)) return;

            if (isSup) running = round2(running + (r.credit - r.debit));
            else running = round2(running + (r.debit - r.credit));
            totDebit = round2(totDebit + r.debit);
            totCredit = round2(totCredit + r.credit);

            renderedRows.push({ ...r, runningBalance: running, idx });
        });

        ctx.lastComputed = { openingDebit: effOpeningDebit, openingCredit: effOpeningCredit, openingNet: effOpeningNet, totDebit, totCredit, finalBalance: running, renderedRows };

        const body = document.getElementById('md-dd-body');
        if (!body) return;
        body.innerHTML = `
            <table class="pro-table" style="width:100%; border-collapse:collapse; font-size:0.84rem;">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>رقم المستند (Drill-Down)</th>
                        <th>نوع الحركة</th>
                        <th>مدين (Debit)</th>
                        <th>دائن (Credit)</th>
                        <th>الرصيد التراكمي المتحرك</th>
                        <th>البيان</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:rgba(251,191,36,0.12); font-weight:900;">
                        <td style="text-align:center;">${fromDt || '—'}</td>
                        <td style="text-align:center;">OPEN-BAL</td>
                        <td>🏁 رصيد أول المدة ${fromDt ? '(مرحّل لما قبل ' + fromDt + ')' : 'الافتتاحي'}</td>
                        <td style="text-align:center; font-family:monospace;">${effOpeningDebit.toLocaleString()}</td>
                        <td style="text-align:center; font-family:monospace;">${effOpeningCredit.toLocaleString()}</td>
                        <td style="text-align:center; font-family:monospace; font-weight:900;">${effOpeningNet.toLocaleString()}</td>
                        <td>الرصيد التأسيسي المسجل بالبطاقة</td>
                    </tr>
                    ${renderedRows.map(r => `
                        <tr onclick="window.openOriginDocumentDrillDownModal(${r.idx})" style="cursor:pointer;" title="اضغط لفتح أصل المستند كشاشة وليدة بنفس السمة وسُمك الحواف (+2)">
                            <td style="text-align:center; font-family:monospace;">${r.date}</td>
                            <td style="text-align:center; font-family:monospace; font-weight:900; color:#38bdf8; text-decoration:underline;">🔍 ${r.docNo}</td>
                            <td style="font-weight:800;">${r.type}</td>
                            <td style="text-align:center; font-family:monospace; color:#34d399; font-weight:800;">${r.debit > 0 ? r.debit.toLocaleString() : '-'}</td>
                            <td style="text-align:center; font-family:monospace; color:#f87171; font-weight:800;">${r.credit > 0 ? r.credit.toLocaleString() : '-'}</td>
                            <td style="text-align:center; font-family:monospace; font-weight:900;">${r.runningBalance.toLocaleString()}</td>
                            <td>${r.note || '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr style="background:rgba(16,185,129,0.18); font-weight:900; font-size:0.9rem;">
                        <td colspan="3">✅ الإجمالي الختامي والرصيد النهائي المطابق 100%</td>
                        <td style="text-align:center; font-family:monospace;">${totDebit.toLocaleString()}</td>
                        <td style="text-align:center; font-family:monospace;">${totCredit.toLocaleString()}</td>
                        <td colspan="2" style="text-align:center; font-family:monospace; color:#34d399;">الصافي: ${running.toLocaleString()} ${entity.currency || 'SDG'}</td>
                    </tr>
                </tfoot>
            </table>
        `;
    };

    window.openOriginDocumentDrillDownModal = function (rowIdx) {
        const ctx = window.__mdActiveDrillDown;
        if (!ctx || !ctx.entity) return;
        const data = window.collectEntityLedgerMovements(ctx.entityType, ctx.entity);
        const row = data.rows[rowIdx];
        if (!row) return;
        let modal = document.getElementById('md-origin-voucher-modal');
        if (!modal) {
            document.body.insertAdjacentHTML('beforeend', `
                <div id="md-origin-voucher-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:1000005; background:rgba(2,6,23,0.86); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                    <div class="md-child-modal-card" style="width:100%; max-width:540px; border:2.5px solid #fbbf24; border-radius:16px; padding:18px; box-shadow:0 25px 60px rgba(0,0,0,0.9);">
                        <div class="md-modal-drag-handle" style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(251,191,36,0.35); padding-bottom:10px; margin-bottom:12px;">
                            <h4 id="md-origin-doc-title" style="margin:0; font-size:1rem; font-weight:900; color:#fbbf24;">📄 أصل المستند</h4>
                            <button type="button" onclick="document.getElementById('md-origin-voucher-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:2px solid rgba(239,68,68,0.5); border-radius:8px; padding:4px 10px; font-weight:800; cursor:pointer;">✖</button>
                        </div>
                        <div id="md-origin-doc-body" style="font-size:0.88rem; line-height:1.7;"></div>
                    </div>
                </div>
            `);
            modal = document.getElementById('md-origin-voucher-modal');
        }
        document.getElementById('md-origin-doc-title').textContent = `📄 أصل المستند: ${row.type} (${row.docNo})`;
        document.getElementById('md-origin-doc-body').innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:10px;border-radius:10px;border:2px solid rgba(148,163,184,0.3);margin-bottom:10px;">
                <div><b>الحساب:</b> ${ctx.entity.name}</div>
                <div><b>التاريخ:</b> <span style="font-family:monospace;font-weight:900;">${row.date}</span></div>
                <div><b>رقم المستند:</b> <span style="font-family:monospace;font-weight:900;color:#38bdf8;">${row.docNo}</span></div>
                <div><b>نوع الحركة:</b> <span style="font-weight:900;">${row.type}</span></div>
                <div><b>مدين (Debit):</b> <span style="font-family:monospace;font-weight:900;color:#34d399;">${row.debit.toLocaleString()}</span></div>
                <div><b>دائن (Credit):</b> <span style="font-family:monospace;font-weight:900;color:#f87171;">${row.credit.toLocaleString()}</span></div>
            </div>
            <div style="padding:8px 10px;border-radius:8px;border:1.5px solid rgba(148,163,184,0.25);"><b>البيان التفصيلي:</b> ${row.note || '-'}</div>
            <div style="margin-top:12px;display:flex;justify-content:space-between;gap:8px;">
                <button type="button" class="btn-add" onclick="window.printMasterHtmlReport('أصل المستند ' + '${row.docNo}', document.getElementById('md-origin-doc-body').innerHTML, 'thermal', '${row.docNo}')">🧾 طباعة إيصال 80mm</button>
                <button type="button" class="btn-cancel-std" onclick="document.getElementById('md-origin-voucher-modal').style.display='none'">✖ إغلاق</button>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.printMasterHtmlReport = function (title, htmlContent, format = 'a4', docKeyOverride = '') {
        // البند 1 من الدستور 12: منع طباعة أي فاتورة أو سند أو بطاقة قبل الحفظ والترحيل الفعلي في قاعدة البيانات
        const openUnsavedForm = document.querySelector('#wh-form[style*="block"], #product-form[style*="block"], #cust-form[style*="block"], #sup-form[style*="block"], #emp-form[style*="block"], #bank-form[style*="block"], #safe-form[style*="block"]');
        if (openUnsavedForm && !window.__mdAllowPrintBypassUnsaved) {
            if (typeof showToast === 'function') {
                showToast('🚫 قفل الطباعة الأمني (الدستور 12): يُمنع طباعة أي مستند أو بطاقة قبل حفظها وترحيلها فعلياً في قاعدة البيانات!', 'error', 4200);
            } else {
                alert('🚫 قفل الطباعة الأمني (الدستور 12): يُمنع طباعة أي مستند أو بطاقة قبل حفظها وترحيلها فعلياً في قاعدة البيانات!');
            }
            return;
        }

        const PRINT_LOCK_KEY = 'magaies_md_c12_print_lock_v1';
        let printReg = {};
        try { printReg = JSON.parse(localStorage.getItem(PRINT_LOCK_KEY) || '{}') || {}; } catch (_) { printReg = {}; }

        const docKey = String(docKeyOverride || title || 'DOC-GENERAL').trim();
        const prevEntry = printReg[docKey] || { count: 0, history: [] };
        const nextPrintNo = (parseInt(prevEntry.count, 10) || 0) + 1;
        const nowStr = new Date().toLocaleString('ar-EG', { hour12: false });
        const empName = window.__mdActiveUserName || (window.__mdActiveRole === 'admin' ? 'المدير العام' : ('موظف (' + (window.__mdActiveRole || 'محاسب') + ')'));
        let reprintReason = 'الطبعة الأصلية الأولى';

        // إذا كانت طبعة ثانية أو أكثر: قفل الطباعة أمام الموظف وإلزام الرقم السري للمدير + تسجيل السبب
        if (nextPrintNo > 1) {
            const cfgPin = (function () {
                try {
                    const c = JSON.parse(localStorage.getItem('magaies_md_c12_sec_cfg_v1') || '{}');
                    return String(c.managerPin || '1234');
                } catch (_) { return '1234'; }
            })();
            const enteredPin = window.prompt(`🔒 قفل منع إعادة الطباعة للغش (الدستور 12)\nتمت طباعة (${docKey}) مسبقاً (${prevEntry.count} مرة).\nأدخل الرقم السري للمدير للسماح بطباعة نسخة بدل فاقد رقم (${nextPrintNo}):`, '');
            if (enteredPin === null || String(enteredPin).trim() !== cfgPin) {
                if (typeof window.logMdC12TamperAttempt === 'function') {
                    window.logMdC12TamperAttempt('محاولة إعادة طباعة بدون صلاحية المدير', docKey, `محاولة طباعة رقم (${nextPrintNo}) برقم سري خاطئ`);
                }
                if (typeof showToast === 'function') showToast('🚫 تم رفض إعادة الطباعة: الرقم السري للمدير غير صحيح وتم توثيق المحاولة في سجل الرقابة الجنائية!', 'error', 4500);
                return;
            }
            const reasonInput = window.prompt(`📝 سبب إعادة الطباعة لنسخة بدل فاقد رقم (${nextPrintNo}) (إلزامي للتوثيق الجنائي):`, 'تلف الورقة الأصلية أثناء الطباعة');
            if (!reasonInput || !reasonInput.trim()) {
                if (typeof showToast === 'function') showToast('⚠️ يُمنع إخراج نسخة بدل فاقد بدون كتابة سبب إعادة الطباعة!', 'error', 3500);
                return;
            }
            reprintReason = reasonInput.trim();
        }

        // توليد باركود الصرف ذي الاستعمال الواحد في الطبعة الأولى (البند 3 من الدستور 12)
        let dispatchQrCode = prevEntry.dispatchQrCode || ('DSP-' + Date.now().toString(36).toUpperCase() + '-' + Math.floor(1000 + Math.random() * 9000));
        if (nextPrintNo === 1 && typeof window.registerMdC12SingleUseDispatchQr === 'function') {
            window.registerMdC12SingleUseDispatchQr(dispatchQrCode, docKey);
        }

        prevEntry.count = nextPrintNo;
        prevEntry.dispatchQrCode = dispatchQrCode;
        prevEntry.lastPrintedAt = nowStr;
        prevEntry.lastPrintedBy = empName;
        prevEntry.history = Array.isArray(prevEntry.history) ? prevEntry.history : [];
        prevEntry.history.unshift({ printNo: nextPrintNo, by: empName, at: nowStr, reason: reprintReason });
        printReg[docKey] = prevEntry;
        try { localStorage.setItem(PRINT_LOCK_KEY, JSON.stringify(printReg)); } catch (_) {}

        // قفل السجل بعد الطباعة لمنع التعديل أو الحذف (البند 2 من الدستور 12)
        if (typeof window.markMdEntityImmutableAfterPrint === 'function') {
            window.markMdEntityImmutableAfterPrint(docKey);
        }

        const isThermal = (format === 'thermal');
        const w = window.open('', '_blank', isThermal ? 'width=360,height=640' : 'width=960,height=720');
        if (!w) return;

        const copyBannerHtml = (nextPrintNo === 1)
            ? `<div style="border:2px solid #059669; background:#ecfdf5; color:#065f46; padding:6px 10px; border-radius:8px; font-weight:900; font-size:12px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                <span>✅ نسخة أصلية — طبعة رقم (1)</span>
                <span style="font-family:monospace;">باركود الصرف وحيد الاستعمال: [${dispatchQrCode}]</span>
                <span>بواسطة: ${empName} | ${nowStr}</span>
               </div>`
            : `<div style="border:2.5px solid #dc2626; background:#fef2f2; color:#991b1b; padding:7px 10px; border-radius:8px; font-weight:900; font-size:12px; margin-bottom:8px;">
                <div>⚠️ نسخة مكررة رقم (${nextPrintNo}) — بدل فاقد — لا يُصرف ولا يُسلّم بها بضاعة</div>
                <div style="font-size:11px; margin-top:3px;">طُبعت بواسطة: ${empName} | التاريخ والوقت بالثانية: ${nowStr} | السبب: ${reprintReason}</div>
               </div>`;

        const diagonalWatermarkHtml = (nextPrintNo > 1)
            ? `<div style="position:fixed; top:48%; left:50%; transform:translate(-50%, -50%) rotate(-32deg); width:115%; text-align:center; font-size:${isThermal ? '16px' : '30px'}; font-weight:900; color:rgba(220,38,38,0.22); border:4px dashed rgba(220,38,38,0.28); padding:14px 10px; pointer-events:none; z-index:99999; line-height:1.4;">
                نسخة مكررة رقم (${nextPrintNo}) — بدل فاقد — لا يُصرف ولا يُسلّم بها بضاعة<br>
                <span style="font-size:0.55em;">الموظف: ${empName} | ${nowStr} | السبب: ${reprintReason}</span>
               </div>`
            : '';

        const pageCss = isThermal
            ? `@page { size: 80mm auto; margin: 2.5mm; } * { box-sizing: border-box; } body { position:relative; width: 74mm; max-width: 74mm; margin: 0 auto; font-family: 'Tajawal', sans-serif; font-size: 11px; color: #000; background: #fff; } table { width: 100%; border-collapse: collapse; font-size: 9.8px; table-layout: auto; word-wrap: break-word; overflow-wrap: break-word; } tr { page-break-inside: avoid; } th, td { border-bottom: 1px dashed #000; padding: 3px 2px; text-align: right; color: #000 !important; font-variant-numeric: tabular-nums; } button, .no-print { display: none !important; }`
            : `@page { size: A4 portrait; margin: 8mm; } * { box-sizing: border-box; } body { position:relative; font-family: 'Tajawal', sans-serif; font-size: 12px; color: #000; background: #fff; margin: 0; } table { width: 100%; border-collapse: collapse; font-size: 11.2px; table-layout: auto; word-wrap: break-word; overflow-wrap: break-word; } thead { display: table-header-group; } tfoot { display: table-footer-group; } tr { page-break-inside: avoid; } th, td { border: 1.5px solid #1e293b; padding: 5px 7px; text-align: right; color: #000 !important; font-variant-numeric: tabular-nums; } th { background: #f1f5f9 !important; font-weight: 900; } button, .no-print { display: none !important; }`;
        w.document.write(`<html dir="rtl"><head><title>${title}</title><style>${pageCss}</style></head><body onload="setTimeout(()=>{window.print();},180)">${diagonalWatermarkHtml}${copyBannerHtml}<h3 style="text-align:center; margin:0 0 8px 0; border-bottom:2px solid #000; padding-bottom:6px;">${title}</h3>${htmlContent}</body></html>`);
        w.document.close();
    };

    window.printCurrentEntityDrillDown = function (format = 'a4') {
        const title = document.getElementById('md-dd-title')?.textContent || 'كشف حساب';
        const html = document.getElementById('md-dd-body')?.innerHTML || '';
        window.printMasterHtmlReport(title, html, format);
    };

    window.exportCurrentEntityDrillDownExcel = function () {
        if (typeof window.verifyMdC13EncryptedExportAuth === 'function' && !window.verifyMdC13EncryptedExportAuth('تصدير كشف حساب Excel')) {
            return;
        }
        const ctx = window.__mdActiveDrillDown;
        if (!ctx || !ctx.lastComputed) return;
        const { entity } = ctx;
        const { openingDebit, openingCredit, openingNet, renderedRows } = ctx.lastComputed;
        const rows = [
            ['التاريخ', 'رقم المستند', 'نوع الحركة', 'مدين', 'دائن', 'الرصيد التراكمي', 'البيان'],
            ['-', 'OPEN-BAL', 'رصيد أول المدة', openingDebit, openingCredit, openingNet, 'رصيد تأسيسي']
        ];
        renderedRows.forEach(r => rows.push([r.date, r.docNo, r.type, r.debit, r.credit, r.runningBalance, r.note || '']));
        const csv = '\uFEFF' + rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `statement_${entity.name || entity.id}.csv`;
        a.click();
    };

    window.shareCurrentEntityDrillDownWhatsApp = function () {
        const ctx = window.__mdActiveDrillDown;
        if (!ctx || !ctx.lastComputed || !ctx.entity) return;
        const { entity } = ctx;
        const { openingNet, totDebit, totCredit, finalBalance } = ctx.lastComputed;
        const msg = `*📊 كشف حساب مختصر — ${entity.name}*\n• رصيد أول المدة: *${openingNet.toLocaleString()}*\n• إجمالي المدين: *${totDebit.toLocaleString()}*\n• إجمالي الدائن: *${totCredit.toLocaleString()}*\n• *الرصيد الختامي الصافي: ${finalBalance.toLocaleString()} ${entity.currency || 'SDG'}*`;
        const phone = String(entity.whatsapp || entity.phone || '').replace(/\D/g, '').replace(/^00/, '');
        window.open(phone ? `https://wa.me/${phone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
    };

    window.openActiveTabPrintAndShareModal = function () {
        const activeTab = document.querySelector('.md-tab-content.active');
        if (!activeTab) return;
        const title = activeTab.querySelector('.md-card-title')?.textContent?.trim() || 'تقرير البيانات الأساسية';
        const tableEl = activeTab.querySelector('table.pro-table');
        const tableHtml = tableEl ? tableEl.outerHTML : '';
        let modal = document.getElementById('md-print-share-hub-modal');
        if (!modal) {
            document.body.insertAdjacentHTML('beforeend', `
                <div id="md-print-share-hub-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.85); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                    <div class="md-child-modal-card" style="width:100%; max-width:460px; background:#0f172a; border:1.5px solid rgba(56,189,248,0.45); border-radius:16px; padding:18px; box-shadow:0 20px 55px rgba(0,0,0,0.85);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                            <h3 id="md-psh-title" style="margin:0; font-size:1rem; font-weight:900; color:#38bdf8;">🖨️ مركز الطباعة والتصدير والمشاركة</h3>
                            <button type="button" onclick="document.getElementById('md-print-share-hub-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 10px; font-weight:800; cursor:pointer;">✖</button>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <button type="button" id="md-psh-a4" style="height:42px; border-radius:10px; background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.45); font-weight:900; cursor:pointer;">🖨️ طباعة A4 و PDF</button>
                            <button type="button" id="md-psh-thermal" style="height:42px; border-radius:10px; background:rgba(245,158,11,0.18); color:#fbbf24; border:1px solid rgba(245,158,11,0.45); font-weight:900; cursor:pointer;">🧾 طباعة كاشير 80mm</button>
                            <button type="button" onclick="exportActiveTabToExcel(); document.getElementById('md-print-share-hub-modal').style.display='none';" style="height:42px; border-radius:10px; background:rgba(16,185,129,0.18); color:#34d399; border:1px solid rgba(16,185,129,0.45); font-weight:900; cursor:pointer;">📤 تصدير Excel فوري</button>
                            <button type="button" id="md-psh-wa" style="height:42px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669); color:#fff !important; border:none; font-weight:900; cursor:pointer;">💬 مشاركة واتساب</button>
                        </div>
                    </div>
                </div>
            `);
            modal = document.getElementById('md-print-share-hub-modal');
        }
        document.getElementById('md-psh-title').textContent = `🖨️ طباعة وتصدير: ${title}`;
        document.getElementById('md-psh-a4').onclick = () => { modal.style.display = 'none'; window.printMasterHtmlReport(title, tableHtml, 'a4'); };
        document.getElementById('md-psh-thermal').onclick = () => { modal.style.display = 'none'; window.printMasterHtmlReport(title, tableHtml, 'thermal'); };
        document.getElementById('md-psh-wa').onclick = () => {
            modal.style.display = 'none';
            const rowCount = activeTab.querySelectorAll('tbody tr').length;
            const msg = `*📋 ملخص ${title}*\nعدد السجلات المعروضة: *${rowCount}*\nتاريخ التقرير: *${new Date().toISOString().slice(0, 10)}*`;
            window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
        };
        modal.style.display = 'flex';
    };

    // Instant 0ms Unit Factor & Opening Inventory Total Auto-Calculator
    document.addEventListener('input', function (e) {
        const t = e.target;
        if (!t) return;
        if (t.id === 'item-opening-qty' || t.id === 'item-opening-value') {
            const qty = parseFloat(document.getElementById('item-opening-qty')?.value) || 0;
            const cost = parseFloat(document.getElementById('item-opening-value')?.value) || 0;
            const totalVal = round2(qty * cost);
            const host = document.getElementById('item-opening-value')?.parentElement;
            if (host) {
                let b = host.querySelector('.md-opening-total-live');
                if (totalVal > 0) {
                    if (!b) {
                        b = document.createElement('div');
                        b.className = 'md-opening-total-live';
                        b.style.cssText = 'margin-top:4px; font-size:0.76rem; font-weight:900; color:#34d399;';
                        host.appendChild(b);
                    }
                    b.textContent = `💰 إجمالي قيمة مخزون أول المدة: ${totalVal.toLocaleString()} SDG`;
                } else if (b) {
                    b.remove();
                }
            }
        }
        if (t.id === 'item-selling-price' || t.closest?.('#units-matrix-body')) {
            const basePrice = parseFloat(document.getElementById('item-selling-price')?.value) || 0;
            const rows = document.querySelectorAll('#units-matrix-body tr');
            rows.forEach((tr, i) => {
                const tds = tr.querySelectorAll('td');
                if (tds.length < 7) return;
                const factorInp = tds[2]?.querySelector('input');
                const cashInp = tds[4]?.querySelector('input');
                const creditInp = tds[5]?.querySelector('input');
                const minInp = tds[6]?.querySelector('input');
                if (!factorInp || !cashInp || !creditInp || !minInp) return;
                const factor = parseFloat(factorInp.value) || 1;
                if (i === 0 && t.id === 'item-selling-price' && basePrice > 0) {
                    cashInp.value = round2(basePrice);
                    if (!parseFloat(creditInp.value)) creditInp.value = round2(basePrice);
                    if (!parseFloat(minInp.value)) minInp.value = round2(basePrice);
                } else if (i > 0 && t === factorInp && basePrice > 0 && factor > 0) {
                    const computed = round2(basePrice * factor);
                    cashInp.value = computed;
                    if (!parseFloat(creditInp.value)) creditInp.value = computed;
                    if (!parseFloat(minInp.value)) minInp.value = computed;
                }
            });
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // 🏆 7. APPROVED WORLD-CLASS PROPOSALS PACK:
    //    1) Debt Aging Analysis Badge (30 / 60 / 90+ Days)
    //    2) Auto Multi-Unit EAN-13 Barcode Generator (Modulo-10 Check Digit)
    //    3) Pre-Import Validation & Diff Preview Modal + 1-Click Rollback
    // ════════════════════════════════════════════════════════════════════════
    window.computeEntityDebtAgingBadge = function (entityType, entity, liveBal) {
        const bal = parseFloat(liveBal) || 0;
        if (bal <= 0 || !entity) return '';
        const termsDays = parseInt(entity.paymentTermsDays, 10) || 30;
        let oldestDateStr = '';
        try {
            const movs = window.collectEntityLedgerMovements(entityType, entity);
            if (movs && Array.isArray(movs.rows) && movs.rows.length > 0) {
                const unpaidCandidates = movs.rows.filter(r => (entityType === 'supplier' ? r.credit > 0 : r.debit > 0) && r.date);
                if (unpaidCandidates.length > 0) {
                    oldestDateStr = unpaidCandidates[unpaidCandidates.length - 1].date;
                }
            }
        } catch (e) {}
        if (!oldestDateStr && entity.createdAt) {
            try { oldestDateStr = new Date(entity.createdAt).toISOString().slice(0, 10); } catch (e) {}
        }
        let daysElapsed = 0;
        if (oldestDateStr) {
            const diffMs = Date.now() - new Date(oldestDateStr).getTime();
            if (!isNaN(diffMs) && diffMs > 0) {
                daysElapsed = Math.floor(diffMs / 86400000);
            }
        }
        if (daysElapsed >= 90 || (daysElapsed > termsDays + 30)) {
            return ` <span style="background:rgba(239,68,68,0.22); color:#fca5a5; border:1px solid rgba(239,68,68,0.5); padding:1px 5px; border-radius:5px; font-size:0.66rem; font-weight:900;" title="عمر المديونية: ${daysElapsed} يوم (تجاوز فترة السداد ${termsDays} يوم)">🔴 +90ي</span>`;
        }
        if (daysElapsed >= 60 || daysElapsed > termsDays) {
            return ` <span style="background:rgba(245,158,11,0.22); color:#fde047; border:1px solid rgba(245,158,11,0.5); padding:1px 5px; border-radius:5px; font-size:0.66rem; font-weight:900;" title="عمر المديونية: ${daysElapsed} يوم (فترة السداد: ${termsDays} يوم)">🟠 60ي</span>`;
        }
        if (daysElapsed >= 30) {
            return ` <span style="background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.45); padding:1px 5px; border-radius:5px; font-size:0.66rem; font-weight:900;" title="عمر المديونية: ${daysElapsed} يوم">🔵 30ي</span>`;
        }
        return ` <span style="background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:1px 5px; border-radius:5px; font-size:0.66rem; font-weight:800;" title="مديونية حديثة ضمن فترة السداد (${termsDays} يوم)">🟢 حديث</span>`;
    };

    function buildValidEan13Code(prefix12Digits) {
        const clean12 = String(prefix12Digits).replace(/\D/g, '').padEnd(12, '0').slice(0, 12);
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            const d = parseInt(clean12[i], 10) || 0;
            sum += (i % 2 === 0) ? d : d * 3;
        }
        const checkDigit = (10 - (sum % 10)) % 10;
        return clean12 + String(checkDigit);
    }

    window.generateMultiUnitEan13Barcodes = function () {
        const seed = String(Date.now()).slice(-7);
        const mainEan = buildValidEan13Code('62810' + seed + '0');
        const mainInp = document.getElementById('item-barcode');
        if (mainInp && !mainInp.value.trim()) {
            mainInp.value = mainEan;
            mainInp.dispatchEvent(new Event('input', { bubbles: true }));
        }
        const rows = document.querySelectorAll('#units-matrix-body tr');
        let generatedCount = 0;
        rows.forEach((tr, idx) => {
            const tds = tr.querySelectorAll('td');
            const bcInp = tds[3] ? tds[3].querySelector('input') : null;
            if (!bcInp) return;
            const unitEan = (idx === 0 && mainInp && mainInp.value.trim())
                ? mainInp.value.trim()
                : buildValidEan13Code('62810' + seed + String((idx + 1) % 10));
            bcInp.value = unitEan;
            generatedCount++;
        });
        if (typeof updateLiveItemQRPreview === 'function') updateLiveItemQRPreview();
        if (typeof showToast === 'function') {
            showToast(`⚡ تم توليد (${generatedCount}) باركود دولي EAN-13 للوحدات بنجاح`, 'success', 2000);
        }
    };

    function refreshRollbackButtonVisibility() {
        const btn = document.getElementById('md-btn-rollback-import');
        if (!btn) return;
        const hasSnap = Boolean(localStorage.getItem('erp_master_import_rollback_v1'));
        btn.style.display = hasSnap ? 'inline-flex' : 'none';
    }

    window.saveMasterImportRollbackSnapshot = function (tabId, fileName) {
        const keyMap = {
            'tab-items': 'erp_items_v1',
            'tab-customers': 'erp_customers_v1',
            'tab-suppliers': 'erp_suppliers_v1',
            'tab-warehouses': 'erp_warehouses_v1'
        };
        const storageKey = keyMap[tabId];
        if (!storageKey) return;
        const snapshot = {
            tabId,
            storageKey,
            fileName: fileName || 'Excel',
            timestamp: new Date().toISOString(),
            rawJson: localStorage.getItem(storageKey) || '[]'
        };
        try {
            localStorage.setItem('erp_master_import_rollback_v1', JSON.stringify(snapshot));
            refreshRollbackButtonVisibility();
        } catch (e) {}
    };

    window.rollbackLastMasterImport = function () {
        const raw = localStorage.getItem('erp_master_import_rollback_v1');
        if (!raw) {
            if (typeof showToast === 'function') showToast('⚠️ لا توجد عملية استيراد سابقة للتراجع عنها', 'warning', 2000);
            return;
        }
        try {
            const snap = JSON.parse(raw);
            const restoredList = JSON.parse(snap.rawJson || '[]');
            setMasterLocalData(snap.storageKey, restoredList, true);
            if (snap.tabId === 'tab-items') { syncToBackend('items_bulk', restoredList); renderItems(); }
            else if (snap.tabId === 'tab-customers') { syncToBackend('customers', restoredList); renderCustomers(); }
            else if (snap.tabId === 'tab-suppliers') { syncToBackend('suppliers', restoredList); renderSuppliers(); }
            else if (snap.tabId === 'tab-warehouses') { syncToBackend('warehouses', restoredList); renderWarehouses(); }
            localStorage.removeItem('erp_master_import_rollback_v1');
            refreshRollbackButtonVisibility();
            logMasterDataAudit('تراجع عن استيراد Excel', snap.tabId, snap.fileName, `استعادة (${restoredList.length}) سجل للحالة السابقة`);
            if (typeof showToast === 'function') showToast(`↩️ تم التراجع عن استيراد (${snap.fileName}) واستعادة البيانات بنجاح`, 'success', 2200);
        } catch (e) {}
    };

    window.openPreImportValidationModal = function (file, text, lines, tabId, onConfirmImport) {
        const keyMap = {
            'tab-items': { key: 'erp_items_v1', label: 'الأصناف' },
            'tab-customers': { key: 'erp_customers_v1', label: 'العملاء' },
            'tab-suppliers': { key: 'erp_suppliers_v1', label: 'الموردين' },
            'tab-warehouses': { key: 'erp_warehouses_v1', label: 'المخازن' }
        };
        const cfg = keyMap[tabId];
        if (!cfg) {
            onConfirmImport();
            return;
        }
        let existingList = [];
        try { existingList = (JSON.parse(localStorage.getItem(cfg.key) || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}

        let newCount = 0, updateCount = 0, skipCount = 0;
        const previewRows = [];
        for (let idx = 1; idx < lines.length; idx++) {
            const cols = parseCSVRow(lines[idx]);
            const code = (cols[0] || '').trim();
            const name = (cols[1] || '').trim();
            if (!name) { skipCount++; continue; }
            const hit = existingList.find(x => (code && String(x.id) === code) || (x.name && x.name.trim() === name));
            const newVal = cols[4] || cols[2] || '-';
            if (hit) {
                updateCount++;
                const oldVal = hit.sellingPrice ?? hit.currentBalance ?? hit.type ?? '-';
                if (previewRows.length < 25) {
                    previewRows.push({ status: '🔄 تحديث', name, code: hit.id, diff: `${oldVal} ⬅️ ${newVal}` });
                }
            } else {
                newCount++;
                if (previewRows.length < 25) {
                    previewRows.push({ status: '✨ جديد', name, code: code || 'تلقائي', diff: `قيمة/سعر: ${newVal}` });
                }
            }
        }

        let modal = document.getElementById('md-excel-preimport-modal');
        if (!modal) {
            document.body.insertAdjacentHTML('beforeend', `
                <div id="md-excel-preimport-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.86); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                    <div class="md-child-modal-card" style="width:100%; max-width:680px; background:#0f172a; border:1.5px solid rgba(56,189,248,0.5); border-radius:16px; overflow:hidden; box-shadow:0 20px 55px rgba(0,0,0,0.85);">
                        <div style="padding:14px 18px; background:rgba(56,189,248,0.14); border-bottom:1px solid rgba(56,189,248,0.3); display:flex; justify-content:space-between; align-items:center;">
                            <h3 id="md-preimp-title" style="margin:0; font-size:1.02rem; font-weight:900; color:#38bdf8;">🔍 الفحص المسبق ومعاينة الفروقات قبل استيراد Excel</h3>
                            <button type="button" onclick="document.getElementById('md-excel-preimport-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 10px; font-weight:800; cursor:pointer;">✖</button>
                        </div>
                        <div style="padding:16px 18px; display:flex; flex-direction:column; gap:12px;">
                            <div id="md-preimp-summary" style="display:flex; gap:10px; flex-wrap:wrap;"></div>
                            <div id="md-preimp-table-wrap" style="max-height:220px; overflow:auto; border-radius:10px; border:1px solid rgba(148,163,184,0.25);"></div>
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                                <span style="font-size:0.76rem; font-weight:800; color:#34d399;">🛡️ يتم حفظ نقطة استرجاع تلقائية للتراجع بضغطة واحدة (Rollback)</span>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="document.getElementById('md-excel-preimport-modal').style.display='none'" style="height:38px; padding:0 14px; border-radius:9px; background:rgba(148,163,184,0.15); color:#cbd5e1; border:1px solid rgba(148,163,184,0.3); font-weight:800; cursor:pointer;">إلغاء</button>
                                    <button type="button" id="md-preimp-confirm-btn" style="height:38px; padding:0 20px; border-radius:9px; background:linear-gradient(135deg,#10b981,#059669); color:#fff !important; border:none; font-weight:900; cursor:pointer;">✅ اعتماد وتنفيذ الاستيراد</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            modal = document.getElementById('md-excel-preimport-modal');
        }
        document.getElementById('md-preimp-title').textContent = `🔍 فحص ملف (${file.name}) — قسم ${cfg.label}`;
        document.getElementById('md-preimp-summary').innerHTML = `
            <span style="background:rgba(16,185,129,0.18); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:4px 12px; border-radius:8px; font-weight:900; font-size:0.82rem;">✨ سجلات جديدة: ${newCount}</span>
            <span style="background:rgba(245,158,11,0.18); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); padding:4px 12px; border-radius:8px; font-weight:900; font-size:0.82rem;">🔄 سجلات ستُحدّث: ${updateCount}</span>
            <span style="background:rgba(148,163,184,0.16); color:#cbd5e1; border:1px solid rgba(148,163,184,0.35); padding:4px 12px; border-radius:8px; font-weight:800; font-size:0.82rem;">⏭️ متخطاة: ${skipCount}</span>
        `;
        document.getElementById('md-preimp-table-wrap').innerHTML = `
            <table class="pro-table" style="width:100%; font-size:0.8rem;">
                <thead><tr><th>الحالة</th><th>الكود</th><th>الاسم</th><th>الفروقات / القيمة</th></tr></thead>
                <tbody>${previewRows.map(r => `<tr>
                    <td style="font-weight:900;">${r.status}</td>
                    <td style="font-family:monospace;">${r.code}</td>
                    <td style="font-weight:800;">${r.name}</td>
                    <td style="font-family:monospace; direction:ltr; text-align:center;">${r.diff}</td>
                </tr>`).join('')}</tbody>
            </table>
        `;
        document.getElementById('md-preimp-confirm-btn').onclick = () => {
            modal.style.display = 'none';
            onConfirmImport();
        };
        modal.style.display = 'flex';
    };

    document.addEventListener('DOMContentLoaded', refreshRollbackButtonVisibility);
    setTimeout(refreshRollbackButtonVisibility, 200);

    // ══════════════════════════════════════════════════════════════════════════
    // 🛡️ CONSTITUTIONAL AUTO-INHERITANCE ENGINE (توريث الشروط تلقائياً لأي عنصر أو حقل يُضاف مستقبلاً)
    // ══════════════════════════════════════════════════════════════════════════
    window.applyConstitutionalRulesToSubtree = function (root = document.body) {
        if (!root || !root.querySelectorAll) return;
        try {
            // 1. Ensure any newly injected modal/overlay inherits .md-child-modal for 100% theme sync
            root.querySelectorAll('[id*="modal"], .modal-overlay, .popup-overlay').forEach(m => {
                if (!m.classList.contains('md-child-modal')) m.classList.add('md-child-modal');
            });

            // 2. Ensure any newly added input/select/textarea gets Enter-key navigation & Arabic-to-ASCII digit normalization
            root.querySelectorAll('input:not([data-md-const-bound]), select:not([data-md-const-bound]), textarea:not([data-md-const-bound])').forEach(ctrl => {
                ctrl.setAttribute('data-md-const-bound', '1');
                if (ctrl.tagName === 'INPUT' && (ctrl.type === 'number' || ctrl.classList.contains('pro-num-input'))) {
                    ctrl.addEventListener('input', function () {
                        const ascii = normalizeDigitsToAscii(this.value);
                        if (ascii !== this.value) this.value = ascii;
                    });
                }
            });
        } catch (e) {}
    };

    if (typeof MutationObserver !== 'undefined') {
        let constObserverTimer = null;
        const constObserver = new MutationObserver((mutations) => {
            let hasAddedNodes = false;
            for (const m of mutations) {
                if (m.addedNodes && m.addedNodes.length > 0) { hasAddedNodes = true; break; }
            }
            if (!hasAddedNodes) return;
            if (constObserverTimer) clearTimeout(constObserverTimer);
            constObserverTimer = setTimeout(() => {
                window.applyConstitutionalRulesToSubtree(document.body);
                if (typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
                    window.syncDynamicDomElementsIntoSmartGuide();
                }
            }, 120);
        });
        const startConstObserver = () => {
            window.applyConstitutionalRulesToSubtree(document.body);
            if (document.body) constObserver.observe(document.body, { childList: true, subtree: true });
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', startConstObserver);
        else startConstObserver();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 📊 كشف حركات ومصروفات وإيرادات مركز التكلفة 360°
    // ══════════════════════════════════════════════════════════════════════════
    window.openCostCenterDrillDownModal = function (ccId) {
        let ccs = [];
        try { ccs = JSON.parse(localStorage.getItem('erp_cost_centers_v1') || localStorage.getItem('erp_jobs_v1') || '[]') || []; } catch (e) {}
        const cc = ccs.find(x => x && String(x.id) === String(ccId)) || { id: ccId, name: ccId };
        const ccName = String(cc.name || cc.title || ccId).trim().toLowerCase();

        const rows = [];
        let totExpense = 0, totRevenue = 0;
        try {
            const vouchers = JSON.parse(localStorage.getItem('erp_vouchers_v1') || '[]') || [];
            vouchers.forEach(v => {
                if (!v || v.deleted) return;
                const vCc = String(v.costCenterId || v.costCenter || v.jobId || '').trim().toLowerCase();
                if (vCc !== String(ccId).toLowerCase() && (!ccName || vCc !== ccName)) return;
                const amt = parseFloat(v.amount ?? v.total) || 0;
                const isRec = (v.type === 'receipt' || String(v.type || '').includes('قبض'));
                if (isRec) totRevenue += amt; else totExpense += amt;
                rows.push({ date: (v.date || '').slice(0, 10), doc: v.voucherNo || v.id || 'VCH', type: isRec ? 'سند قبض (إيراد)' : 'سند صرف (مصروف)', exp: isRec ? 0 : amt, rev: isRec ? amt : 0, note: v.description || v.notes || '-' });
            });
            const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]') || [];
            jvs.forEach(jv => {
                if (!jv || jv.deleted || !Array.isArray(jv.lines)) return;
                jv.lines.forEach(ln => {
                    const lCc = String(ln.costCenterId || ln.costCenter || jv.costCenter || '').trim().toLowerCase();
                    if (lCc !== String(ccId).toLowerCase() && (!ccName || lCc !== ccName)) return;
                    const d = parseFloat(ln.debit) || 0;
                    const c = parseFloat(ln.credit) || 0;
                    totExpense += d; totRevenue += c;
                    rows.push({ date: (jv.date || '').slice(0, 10), doc: jv.voucherNo || jv.id || 'JV', type: 'قيد يومية', exp: d, rev: c, note: ln.description || jv.description || '-' });
                });
            });
        } catch (e) {}

        let modal = document.getElementById('md-cc-drilldown-modal');
        if (!modal) {
            document.body.insertAdjacentHTML('beforeend', `
                <div id="md-cc-drilldown-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.86); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                    <div class="md-child-modal-card" style="width:100%; max-width:780px; background:#0f172a; border:2px solid #38bdf8; border-radius:16px; overflow:hidden; box-shadow:0 20px 55px rgba(0,0,0,0.85);">
                        <div style="padding:14px 18px; background:rgba(56,189,248,0.14); border-bottom:1px solid rgba(56,189,248,0.3); display:flex; justify-content:space-between; align-items:center;">
                            <h3 id="md-cc-dd-title" style="margin:0; font-size:1.02rem; font-weight:900; color:#38bdf8;">📊 كشف حركات مركز التكلفة</h3>
                            <button type="button" onclick="document.getElementById('md-cc-drilldown-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 10px; font-weight:800; cursor:pointer;">✖</button>
                        </div>
                        <div id="md-cc-dd-body" style="padding:16px 18px; max-height:70vh; overflow-y:auto;"></div>
                    </div>
                </div>
            `);
            modal = document.getElementById('md-cc-drilldown-modal');
        }
        document.getElementById('md-cc-dd-title').textContent = `📊 كشف مركز التكلفة: ${cc.name || cc.title || ccId} (${cc.id})`;
        document.getElementById('md-cc-dd-body').innerHTML = `
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px;">
                <div style="padding:10px; border-radius:10px; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); text-align:center;">
                    <div style="font-size:0.78rem; color:#fca5a5;">إجمالي المصروفات / المدين</div>
                    <div style="font-size:1.1rem; font-weight:900; color:#f87171; font-family:monospace;">${totExpense.toLocaleString()} SDG</div>
                </div>
                <div style="padding:10px; border-radius:10px; background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); text-align:center;">
                    <div style="font-size:0.78rem; color:#6ee7b7;">إجمالي الإيرادات / الدائن</div>
                    <div style="font-size:1.1rem; font-weight:900; color:#34d399; font-family:monospace;">${totRevenue.toLocaleString()} SDG</div>
                </div>
                <div style="padding:10px; border-radius:10px; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); text-align:center;">
                    <div style="font-size:0.78rem; color:#7dd3fc;">صافي مركز التكلفة</div>
                    <div style="font-size:1.1rem; font-weight:900; color:#38bdf8; font-family:monospace;">${(totRevenue - totExpense).toLocaleString()} SDG</div>
                </div>
            </div>
            ${rows.length === 0 ? '<div style="padding:18px; text-align:center; color:#94a3b8; font-weight:700;">لا توجد قيود أو سندات مرتبطة بمركز التكلفة هذا حتى الآن. يمكنك ربطه من شاشة السندات أو قيد اليومية.</div>' : `
            <table class="pro-table" style="width:100%; font-size:0.82rem;">
                <thead><tr><th>التاريخ</th><th>المستند</th><th>النوع</th><th>مصروف / مدين</th><th>إيراد / دائن</th><th>البيان</th></tr></thead>
                <tbody>${rows.map(r => `<tr><td>${r.date}</td><td style="font-family:monospace; color:#38bdf8;">${r.doc}</td><td>${r.type}</td><td style="color:#f87171; font-family:monospace;">${r.exp.toLocaleString()}</td><td style="color:#34d399; font-family:monospace;">${r.rev.toLocaleString()}</td><td>${r.note}</td></tr>`).join('')}</tbody>
            </table>`}
        `;
        modal.style.display = 'flex';
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 🤖 المستشار الذكي والتنبؤي الشامل للبيانات الأساسية (AI Master Data & Predictive Advisor 360°)
    // ══════════════════════════════════════════════════════════════════════════
    window.autoFixNegativeItemMarginsAI = function () {
        let items = [];
        try { items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]') || []; } catch (e) {}
        let fixed = 0;
        items.forEach(it => {
            if (!it || it.deleted) return;
            const cost = parseFloat(it.costPrice || it.cost || it.openingValue || (it.units && it.units[0] ? it.units[0].purchasePrice : 0)) || 0;
            const price = parseFloat(it.sellingPrice || (it.units && it.units[0] ? it.units[0].cashPrice : 0)) || 0;
            if (cost > 0 && price <= cost) {
                const newPrice = Math.ceil(cost * 1.15);
                it.sellingPrice = newPrice;
                if (Array.isArray(it.units) && it.units[0]) it.units[0].cashPrice = newPrice;
                fixed++;
            }
        });
        if (fixed > 0) {
            if (typeof setMasterLocalData === 'function') setMasterLocalData('erp_items_v1', items, false);
            else localStorage.setItem('erp_items_v1', JSON.stringify(items));
            if (typeof renderItems === 'function') renderItems();
            window.openMasterDataAIPredictiveModal();
            if (typeof showToast === 'function') showToast(`✅ تم تصحيح تسعير ${fixed} صنف تلقائياً بهامش ربح آمن (+15%)`, 'success');
        }
    };

    window.openMasterDataAIPredictiveModal = function () {
        let warehouses = [], customers = [], suppliers = [], banks = [], safes = [], items = [], employees = [], ccs = [], sales = [];
        try { warehouses = (JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { customers = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { banks = (JSON.parse(localStorage.getItem('erp_banks_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { safes = (JSON.parse(localStorage.getItem('erp_safes_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { items = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { employees = (JSON.parse(localStorage.getItem('erp_users_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { ccs = (JSON.parse(localStorage.getItem('erp_cost_centers_v1') || localStorage.getItem('erp_jobs_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}
        try { sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || localStorage.getItem('erp_invoices_v1') || '[]') || []).filter(x => x && !x.deleted); } catch (e) {}

        // 1. Predictive Stock & Pricing Analysis
        let badMarginItems = 0, lowStockItems = 0, totalStockValue = 0;
        const salesQtyByItem = {};
        sales.forEach(inv => {
            (inv.items || []).forEach(ln => {
                const k = String(ln.itemId || ln.name || '').trim().toLowerCase();
                if (k) salesQtyByItem[k] = (salesQtyByItem[k] || 0) + (parseFloat(ln.qty || ln.quantity) || 0);
            });
        });

        const stockoutPredictions = [];
        items.forEach(it => {
            const stock = (typeof it.stock === 'object' && it.stock !== null)
                ? Object.values(it.stock).reduce((s, v) => s + (parseFloat(v) || 0), 0)
                : (parseFloat(it.stock ?? it.openingQty ?? (it.units && it.units[0] ? it.units[0].stock : 0)) || 0);
            const cost = parseFloat(it.costPrice || it.cost || it.openingValue || (it.units && it.units[0] ? it.units[0].purchasePrice : 0)) || 0;
            const price = parseFloat(it.sellingPrice || (it.units && it.units[0] ? it.units[0].cashPrice : 0)) || 0;
            const reorder = parseFloat(it.reorderLevel || it.minLimit || 5) || 5;
            totalStockValue += stock * cost;
            if (cost > 0 && price <= cost) badMarginItems++;
            if (stock <= reorder) lowStockItems++;
            const soldQty = salesQtyByItem[String(it.id || '').toLowerCase()] || salesQtyByItem[String(it.name || '').trim().toLowerCase()] || 0;
            const dailyVel = soldQty / 30;
            if (dailyVel > 0 && stock > 0) {
                const daysLeft = Math.round(stock / dailyVel);
                if (daysLeft <= 14 && stockoutPredictions.length < 4) {
                    stockoutPredictions.push(`${it.name} (متبقي ${stock} — يكفي ~${daysLeft} يوم)`);
                }
            }
        });

        // 2. Predictive Credit & Dual-Role Partners Analysis
        let overLimitCustomers = 0, missingPhones = 0;
        const supNamesSet = new Set(suppliers.map(s => String(s.name || '').trim().toLowerCase()));
        let dualRoleCount = 0;
        customers.forEach(c => {
            const bal = typeof window.computeLiveEntityLedgerBalance === 'function' ? window.computeLiveEntityLedgerBalance('customer', c) : (parseFloat(c.currentBalance) || 0);
            const lim = parseFloat(c.creditLimit) || 0;
            if (lim > 0 && bal > lim) overLimitCustomers++;
            if (!c.phone && !c.whatsapp) missingPhones++;
            if (supNamesSet.has(String(c.name || '').trim().toLowerCase())) dualRoleCount++;
        });
        suppliers.forEach(s => { if (!s.phone && !s.whatsapp) missingPhones++; });

        let modal = document.getElementById('md-ai-predictive-modal');
        if (!modal) {
            document.body.insertAdjacentHTML('beforeend', `
                <div id="md-ai-predictive-modal" class="md-child-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.88); backdrop-filter:blur(10px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Tajawal',sans-serif;">
                    <div class="md-child-modal-card" style="width:100%; max-width:860px; max-height:90vh; background:#0f172a; border:2px solid #8b5cf6; border-radius:18px; overflow:hidden; box-shadow:0 25px 65px rgba(0,0,0,0.85); display:flex; flex-direction:column;">
                        <div style="padding:14px 20px; background:linear-gradient(135deg,rgba(139,92,246,0.25),rgba(56,189,248,0.15)); border-bottom:1.5px solid rgba(139,92,246,0.4); display:flex; justify-content:space-between; align-items:center;">
                            <h3 style="margin:0; font-size:1.08rem; font-weight:900; color:#e9d5ff;">🤖 المستشار الذكي والتنبؤي للبيانات الأساسية (AI Master Data & Predictive Advisor 360°)</h3>
                            <button type="button" onclick="document.getElementById('md-ai-predictive-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.45); border-radius:8px; padding:4px 12px; font-weight:800; cursor:pointer;">✖ إغلاق</button>
                        </div>
                        <div id="md-ai-predictive-body" style="padding:18px 20px; overflow-y:auto; display:flex; flex-direction:column; gap:14px;"></div>
                    </div>
                </div>
            `);
            modal = document.getElementById('md-ai-predictive-modal');
        }

        document.getElementById('md-ai-predictive-body').innerHTML = `
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:10px;">
                <div style="padding:12px; border-radius:12px; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3);">
                    <div style="font-size:0.76rem; color:#93c5fd;">📦 الأصناف والمخازن</div>
                    <div style="font-size:1.05rem; font-weight:900; color:#f8fafc; margin-top:4px;">${items.length} صنف / ${warehouses.length} مخزن</div>
                    <div style="font-size:0.75rem; color:#34d399; margin-top:3px;">قيمة المخزون: ${Math.round(totalStockValue).toLocaleString()} SDG</div>
                </div>
                <div style="padding:12px; border-radius:12px; background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.3);">
                    <div style="font-size:0.76rem; color:#d8b4fe;">🤝 العملاء والموردون</div>
                    <div style="font-size:1.05rem; font-weight:900; color:#f8fafc; margin-top:4px;">${customers.length} عميل / ${suppliers.length} مورد</div>
                    <div style="font-size:0.75rem; color:#fbbf24; margin-top:3px;">مشترك للمقاصة: ${dualRoleCount} شريك</div>
                </div>
                <div style="padding:12px; border-radius:12px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3);">
                    <div style="font-size:0.76rem; color:#6ee7b7;">🏦 البنوك والخزائن</div>
                    <div style="font-size:1.05rem; font-weight:900; color:#f8fafc; margin-top:4px;">${banks.length} بنك / ${safes.length} خزينة</div>
                    <div style="font-size:0.75rem; color:#38bdf8; margin-top:3px;">مربوطة بدفتر الأستاذ 100%</div>
                </div>
                <div style="padding:12px; border-radius:12px; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3);">
                    <div style="font-size:0.76rem; color:#fde047;">👥 الموظفون ومراكز التكلفة</div>
                    <div style="font-size:1.05rem; font-weight:900; color:#f8fafc; margin-top:4px;">${employees.length} موظف / ${ccs.length} مركز</div>
                    <div style="font-size:0.75rem; color:#cbd5e1; margin-top:3px;">نواقص الهواتف: ${missingPhones}</div>
                </div>
            </div>

            <div style="padding:14px; border-radius:12px; background:rgba(15,23,42,0.75); border:1px solid rgba(139,92,246,0.35);">
                <h4 style="margin:0 0 10px 0; color:#c084fc; font-size:0.95rem;">🔮 التنبؤات الذكية والتوصيات الفورية (Predictive ERP Intelligence):</h4>
                <ul style="margin:0; padding-right:18px; color:#e2e8f0; font-size:0.85rem; line-height:1.8;">
                    <li><b>تحليل التسعير وهوامش الربح:</b> ${badMarginItems > 0 ? `<span style="color:#fbbf24; font-weight:800;">يوجد ${badMarginItems} صنف سعر بيعها أقل أو يساوي التكلفة!</span> <button type="button" onclick="window.autoFixNegativeItemMarginsAI()" style="margin-right:8px; padding:2px 10px; border-radius:6px; background:#10b981; color:#fff; border:none; font-weight:800; cursor:pointer; font-size:0.76rem;">⚡ تصحيح تلقائي (+15%)</button>` : '<span style="color:#34d399; font-weight:800;">✅ كافة الأصناف المسعّرة تحقق هامش ربح إيجابي سليم.</span>'}</li>
                    <li><b>التنبؤ بنفاد المخزون (Stockout Forecast):</b> يوجد <b>${lowStockItems}</b> صنف وصل لحد الطلب.${stockoutPredictions.length > 0 ? ` توقع النفاد القريب حسب سرعة المبيعات: <span style="color:#fca5a5;">${stockoutPredictions.join(' ، ')}</span>.` : ''}</li>
                    <li><b>مخاطر الائتمان والمقاصة:</b> ${overLimitCustomers > 0 ? `<span style="color:#f87171; font-weight:800;">يوجد ${overLimitCustomers} عميل تجاوزوا سقف الائتمان المسموح.</span>` : '<span style="color:#34d399;">لا يوجد تجاوز خطير لسقوف الائتمان.</span>'} ${dualRoleCount > 0 ? `كما يوجد <b>${dualRoleCount}</b> شريك يعمل كعميل ومورد معاً جاهز لتسوية المقاصة بضغطة زر.` : ''}</li>
                </ul>
            </div>

            <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:space-between; align-items:center; padding-top:6px; border-top:1px solid rgba(148,163,184,0.2);">
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <button type="button" onclick="document.getElementById('md-ai-predictive-modal').style.display='none'; openOpeningEquilibriumModal();" style="padding:7px 12px; border-radius:8px; background:rgba(16,185,129,0.18); color:#34d399; border:1px solid rgba(16,185,129,0.45); font-weight:800; font-size:0.8rem; cursor:pointer;">⚖️ معالج القيد الافتتاحي</button>
                    <button type="button" onclick="document.getElementById('md-ai-predictive-modal').style.display='none'; openBulkPriceUpdaterModal();" style="padding:7px 12px; border-radius:8px; background:rgba(245,158,11,0.18); color:#fbbf24; border:1px solid rgba(245,158,11,0.45); font-weight:800; font-size:0.8rem; cursor:pointer;">📈 تحديث الأسعار الجماعي</button>
                    <button type="button" onclick="document.getElementById('md-ai-predictive-modal').style.display='none'; window.openMdHealthAndDuplicateScannerModal();" style="padding:7px 12px; border-radius:8px; background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.45); font-weight:800; font-size:0.8rem; cursor:pointer;">🔍 فاحص التكرارات والنواقص</button>
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <a href="ai_executive_advisor.php" style="padding:7px 12px; border-radius:8px; background:linear-gradient(135deg,#8b5cf6,#6d28d9); color:#fff; text-decoration:none; font-weight:800; font-size:0.8rem;">🧠 المستشار التنفيذي الشامل</a>
                    <a href="ai_goods_advisor.php" style="padding:7px 12px; border-radius:8px; background:rgba(59,130,246,0.2); color:#93c5fd; border:1px solid rgba(59,130,246,0.4); text-decoration:none; font-weight:800; font-size:0.8rem;">📦 مستشار البضائع التنبؤي</a>
                    <a href="credit_limits_dashboard.php" style="padding:7px 12px; border-radius:8px; background:rgba(239,68,68,0.18); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); text-decoration:none; font-weight:800; font-size:0.8rem;">🛡️ مراقبة الائتمان</a>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    // Right-Click (contextmenu) Definition & Options Menu for WhatsApp Controls, Permissions & Delegates
    (function initMdRightClickDefinitionMenu() {
        let menuBox = null;
        let currentVoiceRate = parseFloat(localStorage.getItem('msg_male_speed_v6') || '1.0') || 1.0;

        window.__makePopupElementDraggable = window.__makePopupElementDraggable || function(el) {
            if (!el || el.__isDraggableBound) return;
            el.__isDraggableBound = true;
            let dragging = false, startX = 0, startY = 0, origLeft = 0, origTop = 0;
            const onStart = (cx, cy, target) => {
                if (target && target.closest && target.closest('button, input, select, textarea, a, label')) return false;
                const r = el.getBoundingClientRect();
                dragging = true; startX = cx; startY = cy; origLeft = r.left; origTop = r.top;
                el.style.right = 'auto'; el.style.bottom = 'auto'; el.style.margin = '0';
                el.style.left = origLeft + 'px'; el.style.top = origTop + 'px';
                el.style.cursor = 'grabbing';
                return true;
            };
            const onMove = (cx, cy) => {
                if (!dragging) return;
                const nx = Math.max(2, Math.min(window.innerWidth - 60, origLeft + (cx - startX)));
                const ny = Math.max(2, Math.min(window.innerHeight - 40, origTop + (cy - startY)));
                el.style.left = nx + 'px'; el.style.top = ny + 'px';
            };
            const onEnd = () => { if (dragging) { dragging = false; el.style.cursor = ''; } };
            el.addEventListener('mousedown', (e) => { if (e.button === 0 && onStart(e.clientX, e.clientY, e.target)) e.preventDefault(); });
            window.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY), { passive: true });
            window.addEventListener('mouseup', onEnd, { passive: true });
            el.addEventListener('touchstart', (e) => { if (e.touches.length === 1) onStart(e.touches[0].clientX, e.touches[0].clientY, e.target); }, { passive: true });
            window.addEventListener('touchmove', (e) => { if (dragging && e.touches.length === 1) onMove(e.touches[0].clientX, e.touches[0].clientY); }, { passive: true });
            window.addEventListener('touchend', onEnd, { passive: true });
        };

        function isLightThemeActiveNow() {
            const h = document.documentElement;
            const t = h.getAttribute('data-theme') || (document.body && document.body.getAttribute('data-theme')) || '';
            return h.classList.contains('light-theme') || (document.body && document.body.classList.contains('light-theme')) || ['light','warm_cream','ultra_white','exec_gray','pearl-white'].includes(t);
        }

        function ensureMenuBox() {
            if (menuBox) return menuBox;
            menuBox = document.createElement('div');
            menuBox.id = 'md-rightclick-def-menu';
            menuBox.className = 'md-child-modal-card';
            menuBox.style.cssText = 'position:fixed; z-index:2147483600; width:430px; max-width:94vw; padding:14px 16px; border-radius:15px; font-family:Tajawal,sans-serif; font-size:0.86rem; font-weight:800; line-height:1.68; box-shadow:0 18px 45px rgba(0,0,0,0.65); display:none; direction:rtl; text-align:right; user-select:none;';
            document.body.appendChild(menuBox);
            window.__makePopupElementDraggable(menuBox);
            return menuBox;
        }

        let lastRightClickTs = 0;
        let lastRightClickEl = null;
        let currentSpokenFullText = '';

        function stripNativeHoverTitles(root = document) {
            root.querySelectorAll('[title]').forEach(el => {
                const t = el.getAttribute('title');
                if (t) el.setAttribute('data-md-orig-title', t);
                el.removeAttribute('title');
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => stripNativeHoverTitles());
        else stripNativeHoverTitles();

        window.stopMdDefPanelVoice = function() {
            if (typeof window.stopMdExplanationPanelAudio === 'function') window.stopMdExplanationPanelAudio();
            try { if ('speechSynthesis' in window) window.speechSynthesis.cancel(); } catch (_) {}
            const btn = document.getElementById('md-def-mic-read-btn');
            if (btn) btn.innerHTML = '🔊 تشغيل الصوت';
            const pBtn = document.getElementById('md-def-pause-btn');
            if (pBtn) pBtn.innerHTML = '⏸️ مؤقت';
        };

        window.togglePauseMdDefPanelVoice = function() {
            const pBtn = document.getElementById('md-def-pause-btn');
            if (typeof window.togglePauseResumeAudio === 'function') {
                window.togglePauseResumeAudio();
                if (pBtn) pBtn.innerHTML = (pBtn.innerHTML.includes('استئناف') ? '⏸️ مؤقت' : '▶️ استئناف');
                return;
            }
            try {
                if ('speechSynthesis' in window) {
                    if (window.speechSynthesis.paused) { window.speechSynthesis.resume(); if (pBtn) pBtn.innerHTML = '⏸️ مؤقت'; }
                    else if (window.speechSynthesis.speaking) { window.speechSynthesis.pause(); if (pBtn) pBtn.innerHTML = '▶️ استئناف'; }
                }
            } catch (_) {}
        };

        window.setMdDefPanelVoiceSpeed = function(rate) {
            currentVoiceRate = parseFloat(rate) || 1.0;
            localStorage.setItem('msg_male_speed_v6', currentVoiceRate.toFixed(2));
            const isLight = isLightThemeActiveNow();
            document.querySelectorAll('.md-voice-spd-btn').forEach(b => {
                const isAct = Math.abs(parseFloat(b.getAttribute('data-spd')) - currentVoiceRate) < 0.05;
                if (isLight) {
                    b.style.background = isAct ? 'var(--lt-hdr-2, #d4a679)' : 'var(--lt-sub-1, #ffffff)';
                    b.style.borderColor = 'var(--lt-border, #4a2106)';
                    b.style.color = 'var(--lt-ink, #140600)';
                } else {
                    b.style.background = isAct ? 'rgba(56,189,248,0.32)' : 'rgba(30,41,59,0.75)';
                    b.style.borderColor = isAct ? '#38bdf8' : 'rgba(148,163,184,0.35)';
                    b.style.color = isAct ? '#fde047' : '#cbd5e1';
                }
            });
            if (typeof window.onMsgSpeedChange === 'function') window.onMsgSpeedChange(currentVoiceRate, false);
            window.speakMdDefPanelVoice(currentSpokenFullText);
        };

        window.speakMdDefPanelVoice = function(customText) {
            const txt = String(customText || currentSpokenFullText || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            if (!txt) return;
            if (typeof window.onMsgSpeedChange === 'function') window.onMsgSpeedChange(currentVoiceRate, false);
            const btn = document.getElementById('md-def-mic-read-btn');
            if (btn) btn.innerHTML = '🔊 يقرأ الآن...';
            const pBtn = document.getElementById('md-def-pause-btn');
            if (pBtn) pBtn.innerHTML = '⏸️ مؤقت';
            const onDone = () => { const b = document.getElementById('md-def-mic-read-btn'); if (b) b.innerHTML = '🔁 إعادة الصوت'; };
            if (typeof window.speakMdExplanationPanelText === 'function') {
                window.speakMdExplanationPanelText(txt, onDone);
                return;
            }
            try {
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const u = new SpeechSynthesisUtterance(txt);
                    u.lang = 'ar-SA'; u.rate = currentVoiceRate; u.onend = onDone; u.onerror = onDone;
                    window.speechSynthesis.speak(u);
                }
            } catch (_) { onDone(); }
        };

        function resolveMdElementFullExplanation(rawEl) {
            if (!rawEl) return null;
            const directCtrl = rawEl.closest ? rawEl.closest('button, input, select, textarea, a, th') : null;
            const mdInfo = (typeof window.explainMasterDataDomElement === 'function' && window.explainMasterDataDomElement(directCtrl || rawEl))
                || (typeof window.autoSynthesizeElementExplanation === 'function' && window.autoSynthesizeElementExplanation(directCtrl || rawEl));
            const tipEl = (directCtrl && (directCtrl.hasAttribute('data-md-tip') || directCtrl.hasAttribute('data-guide-desc')))
                ? directCtrl
                : (rawEl.closest ? rawEl.closest('[data-md-tip], [data-guide-desc]') : null);
            const isContainerTipOverridingButton = directCtrl && directCtrl.tagName === 'BUTTON' && tipEl && tipEl !== directCtrl && !tipEl.contains(directCtrl.parentElement);
            const title = (!isContainerTipOverridingButton && mdInfo && mdInfo.title)
                || (tipEl && (tipEl.getAttribute('data-md-tip-title') || tipEl.getAttribute('data-guide-title')))
                || (rawEl.getAttribute && (rawEl.getAttribute('data-md-orig-title') || rawEl.getAttribute('placeholder')))
                || '📖 الشرح التفصيلي الشامل';
            const tipDesc = tipEl && (tipEl.getAttribute('data-md-tip') || tipEl.getAttribute('data-guide-desc'));
            const mdDesc = mdInfo && mdInfo.desc;
            const desc = (mdDesc && tipDesc && mdDesc !== tipDesc && !mdDesc.includes(tipDesc) && (!directCtrl || tipEl === directCtrl || tipEl.classList.contains('input-group') || tipEl.classList.contains('pro-field-card')))
                ? `${mdDesc} — ${tipDesc}`
                : (mdDesc || tipDesc || 'هذه الخانة أو الأداة مخصصة لإدخال وضبط البيانات الأساسية وحفظها مع التسميع المحاسبي والمخزني الفوري.');
            return { title, desc };
        }

        function openMdExplanationPanelAt(targetEl, clientX, clientY) {
            const info = resolveMdElementFullExplanation(targetEl);
            if (!info) return;
            const colPop = document.getElementById('md-col-context-popover');
            if (colPop) colPop.style.display = 'none';
            const box = ensureMenuBox();
            const isLight = isLightThemeActiveNow();
            box.style.background = isLight
                ? 'linear-gradient(145deg, var(--lt-card-1, #fdf6ee) 0%, var(--lt-card-2, #f3e2ce) 100%)'
                : 'linear-gradient(145deg, var(--dk-card-1, #0f172a) 0%, var(--dk-card-2, #152342) 100%)';
            box.style.border = isLight ? '2.5px solid var(--lt-border, #4a2106)' : '2px solid var(--dk-border, #38bdf8)';
            box.style.color = isLight ? 'var(--lt-ink, #140600)' : '#ffffff';
            currentSpokenFullText = `${info.title}. ${info.desc}`;
            const spdBtnStyle = (r) => {
                const act = Math.abs(currentVoiceRate - r) < 0.05;
                if (isLight) {
                    return `flex:1;padding:5px 6px;border-radius:7px;background:${act ? 'var(--lt-hdr-2, #d4a679)' : 'var(--lt-sub-1, #ffffff)'};color:var(--lt-ink, #140600);border:1.5px solid var(--lt-border, #4a2106);font-size:0.75rem;font-weight:900;cursor:pointer;`;
                }
                return `flex:1;padding:5px 6px;border-radius:7px;background:${act ? 'rgba(56,189,248,0.32)' : 'rgba(30,41,59,0.75)'};color:${act ? '#fde047' : '#cbd5e1'};border:1px solid ${act ? '#38bdf8' : 'rgba(148,163,184,0.35)'};font-size:0.75rem;font-weight:900;cursor:pointer;`;
            };
            const descBoxStyle = isLight
                ? 'padding:11px 13px;border-radius:10px;background:var(--lt-sub-1, #fef9f3);border:2px solid var(--lt-border, #4a2106);color:var(--lt-ink, #140600);line-height:1.75;font-size:0.87rem;font-weight:900;'
                : 'padding:11px 13px;border-radius:10px;background:rgba(56,189,248,0.14);border:1.5px solid rgba(56,189,248,0.45);color:#ffffff;line-height:1.75;font-size:0.87rem;';
            box.innerHTML = `
                <div class="popup-drag-handle" title="اسحب لتحريك نافذة الشرح لأي مكان" style="display:flex;justify-content:space-between;align-items:center;gap:8px;border-bottom:1.5px solid ${isLight ? 'var(--lt-border, #4a2106)' : 'rgba(56,189,248,0.35)'};padding-bottom:8px;margin-bottom:9px;cursor:grab;">
                    <span style="color:${isLight ? 'var(--lt-ink, #140600)' : '#fde047'};font-size:0.96rem;font-weight:900;">✋ ${info.title}</span>
                    <button type="button" onclick="window.stopMdDefPanelVoice();document.getElementById('md-rightclick-def-menu').style.display='none'" style="background:rgba(239,68,68,0.22);color:${isLight ? '#991b1b' : '#fca5a5'};border:1px solid rgba(239,68,68,0.55);border-radius:6px;padding:2px 9px;cursor:pointer;font-weight:900;">✖</button>
                </div>
                <div style="display:flex;gap:5px;margin-bottom:6px;flex-wrap:wrap;">
                    <button type="button" id="md-def-mic-read-btn" onclick="window.speakMdDefPanelVoice()" style="flex:1.3;padding:6px 9px;border-radius:8px;background:linear-gradient(135deg,#0284c7,#2563eb);color:#ffffff !important;-webkit-text-fill-color:#ffffff !important;border:1px solid #38bdf8;font-size:0.79rem;font-weight:900;cursor:pointer;">🔊 تشغيل الشرح</button>
                    <button type="button" id="md-def-pause-btn" onclick="window.togglePauseMdDefPanelVoice()" style="flex:0.9;padding:6px 8px;border-radius:8px;background:${isLight ? 'rgba(245,158,11,0.28)' : 'rgba(245,158,11,0.2)'};color:${isLight ? '#78350f' : '#fde047'};border:1.5px solid rgba(245,158,11,0.65);font-size:0.77rem;font-weight:900;cursor:pointer;">⏸️ مؤقت</button>
                    <button type="button" onclick="window.stopMdDefPanelVoice()" style="flex:0.9;padding:6px 8px;border-radius:8px;background:rgba(239,68,68,0.2);color:${isLight ? '#991b1b' : '#fca5a5'};border:1.5px solid rgba(239,68,68,0.55);font-size:0.77rem;font-weight:900;cursor:pointer;">⏹️ إيقاف</button>
                </div>
                <div style="display:flex;gap:5px;margin-bottom:10px;">
                    <button type="button" class="md-voice-spd-btn" data-spd="0.75" onclick="window.setMdDefPanelVoiceSpeed(0.75)" style="${spdBtnStyle(0.75)}">🐢 بطيء</button>
                    <button type="button" class="md-voice-spd-btn" data-spd="1.0" onclick="window.setMdDefPanelVoiceSpeed(1.0)" style="${spdBtnStyle(1.0)}">⚖️ عادي</button>
                    <button type="button" class="md-voice-spd-btn" data-spd="1.28" onclick="window.setMdDefPanelVoiceSpeed(1.28)" style="${spdBtnStyle(1.28)}">⚡ سريع</button>
                    <button type="button" class="md-voice-spd-btn" data-spd="1.5" onclick="window.setMdDefPanelVoiceSpeed(1.5)" style="${spdBtnStyle(1.5)}">🚀 سريع جداً</button>
                </div>
                <div style="${descBoxStyle}">${info.desc}</div>
            `;
            box.style.display = 'block';
            const bw = box.offsetWidth || 430;
            const bh = box.offsetHeight || 240;
            let left = clientX || (window.innerWidth / 2 - bw / 2);
            let top = clientY || (window.innerHeight / 2 - bh / 2);
            if (left + bw > window.innerWidth - 10) left = window.innerWidth - bw - 10;
            if (left < 10) left = 10;
            if (top + bh > window.innerHeight - 10) top = window.innerHeight - bh - 10;
            if (top < 6) top = 6;
            box.style.left = left + 'px';
            box.style.top = top + 'px';
            window.speakMdDefPanelVoice(currentSpokenFullText);
        }
        window.openMdExplanationPanelGlobal = openMdExplanationPanelAt;

        const EXPLAINABLE_SEL = '[data-md-tip], [data-guide-desc], .md-tab-btn, .pro-field-card, .input-group, button, input, select, textarea, th, td, a, #admin-wa-policy-panel-cust, #admin-wa-policy-panel-sup';

        document.addEventListener('contextmenu', function(e) {
            if (menuBox && menuBox.contains(e.target)) return;
            const now = Date.now();
            const isDoubleRight = (now - lastRightClickTs <= 650);
            const tblTh = e.target && e.target.closest ? e.target.closest('table.pro-table th, .table-responsive table th') : null;
            const inActionBtn = e.target && e.target.closest ? e.target.closest('button, a, input, select, textarea') : null;
            if (tblTh && !inActionBtn && !isDoubleRight && typeof window.openMdTableColContextMenu === 'function') {
                e.preventDefault();
                e.stopPropagation();
                lastRightClickTs = now;
                window.openMdTableColContextMenu(e, tblTh);
                return;
            }
            const target = e.target && e.target.closest ? e.target.closest(EXPLAINABLE_SEL) : null;
            if (!target) return;
            e.preventDefault();
            lastRightClickTs = now;
            openMdExplanationPanelAt(e.target, e.clientX, e.clientY);
        });

        document.addEventListener('dblclick', function(e) {
            if (menuBox && menuBox.contains(e.target)) return;
            if (e.target && e.target.closest && e.target.closest('[ondblclick*="startInlineMasterEdit"]')) return;
            const fldTarget = e.target && e.target.closest ? e.target.closest(EXPLAINABLE_SEL) : null;
            if (!fldTarget) return;
            openMdExplanationPanelAt(e.target, e.clientX, e.clientY);
        });

        document.addEventListener('mousedown', function(e) {
            if (menuBox && menuBox.style.display === 'block' && !menuBox.contains(e.target)) {
                window.stopMdDefPanelVoice();
                menuBox.style.display = 'none';
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && menuBox) {
                window.stopMdDefPanelVoice();
                menuBox.style.display = 'none';
            }
        });
    })();

    // Clean auto-fit table widths (prevent locked horizontal overflow)
    (function initCleanAutoFitMasterTables() {
        try {
            Object.keys(localStorage).forEach(k => {
                if (k.indexOf('erp_md_locked_col_widths_v2_') === 0) localStorage.removeItem(k);
            });
        } catch (_) {}
    })();
})();
