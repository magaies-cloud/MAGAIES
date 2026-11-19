        // Spotlight Effect Mouse Tracker (throttled to 60fps)
        let _spotlightTick = false;
        document.addEventListener('mousemove', e => {
            if (_spotlightTick) return;
            _spotlightTick = true;
            requestAnimationFrame(() => {
                for(const card of document.querySelectorAll('.spotlight-card')) {
                    const rect = card.getBoundingClientRect();
                    card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
                    card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
                }
                _spotlightTick = false;
            });
        }, { passive: true });

        // Dynamic 60fps EaseOutExpo Animated Counter Engine
        function animateValue(elementOrId, start, end, duration = 1200, suffix = '') {
            const obj = typeof elementOrId === 'string' ? document.getElementById(elementOrId) : elementOrId;
            if (!obj) return;
            start = parseFloat(start) || 0;
            end = parseFloat(end) || 0;
            if (start === end) {
                obj.innerText = end.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                return;
            }
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const currentVal = Math.floor(easeProgress * (end - start) + start);
                obj.innerText = currentVal.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    obj.innerText = end.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                }
            };
            window.requestAnimationFrame(step);
        }

        // Check Authentication & Initialize Dashboard App
        function initDashboardApp() {
            let token = localStorage.getItem('authToken') || localStorage.getItem('saas_token');
            if (!token || token === 'local-offline-token' || token.indexOf('offline_token_') === 0) {
                token = 'magaiees-direct-access-token';
                localStorage.setItem('authToken', token);
                localStorage.setItem('saas_token', token);
            }

            // تأكيد فتح القفل الموحد
            if (!sessionStorage.getItem('dashboard_unlocked')) {
                sessionStorage.setItem('dashboard_unlocked', '1');
            }
            
            setupUnifiedAutoLogout();

            // Initialize Charts
            initProCharts();

            // Render Single Row Quick Actions (7 items)
            renderSingleRowQA();

            // Check Due Dates for Notifications
            checkDueDates();

            // Set Company Name dynamically if possible, or fallback
            const companyNameEl = document.getElementById('main-brand-name');
            const companyTopEl = document.getElementById('header-company-name-top');
            
            const storedCompanyName = localStorage.getItem('companyName') || 'نظام إدارة الشركة';
            
            if(companyNameEl) {
                companyNameEl.textContent = storedCompanyName;
            }
            if(companyTopEl) {
                companyTopEl.textContent = storedCompanyName;
            }

            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (cfg.showAthkar !== false) { // Default is true
                    const athkarEl = document.getElementById('dashboard-athkar-container');
                    if (athkarEl) athkarEl.style.display = 'flex';
                }
            } catch (e) {}
        }
        
        // --- Security Lock Modal & Auto Logout ---
        function showUnifiedLock() {
            let modal = document.getElementById('unified-lock-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'unified-lock-modal';
                modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.95); backdrop-filter:blur(10px); z-index:100000; display:flex; align-items:center; justify-content:center; direction:rtl;';
                
                let username = 'مستخدم النظام';
                try {
                    const user = JSON.parse(localStorage.getItem('user'));
                    username = user.fullName || user.name || user.username || username;
                } catch(e) {}
                
                modal.innerHTML = `
                    <div style="width: 90%; max-width: 400px; text-align: center; padding: 40px 35px; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #ea580c); border-radius: 20px; margin: 0 auto 20px auto; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);">🛡️</div>
                        <h3 style="color: #fff; margin-bottom: 5px; font-weight: 700; font-family:'Tajawal',sans-serif;">${username}</h3>
                        <p style="color: #a1a1aa; font-size: 0.9rem; margin-bottom: 25px; font-family:'Tajawal',sans-serif;">أدخل كلمة المرور للمتابعة</p>
                        <input type="password" id="unified-lock-pass" placeholder="........" style="width: 100%; padding: 14px; border-radius: 10px; background: rgba(255,255,255,0.07); color: #fff; border: 1px solid rgba(255,255,255,0.1); font-size: 1.1rem; box-sizing: border-box; margin-bottom: 20px; font-family: 'Tajawal',sans-serif; text-align: center; outline: none;">
                        <button onclick="unlockUnifiedDashboard()" style="width: 100%; font-family:'Tajawal',sans-serif; font-size: 1.1rem; padding: 12px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b, #ea580c); border: none; color: white; cursor: pointer; transition: 0.2s;">تسجيل الدخول</button>
                        <div id="unified-lock-error" style="color: #ef4444; margin-top: 15px; font-size: 0.9rem; display: none; font-family:'Tajawal',sans-serif;">كلمة المرور خاطئة</div>
                    </div>
                `;
                document.body.appendChild(modal);
                
                document.getElementById('unified-lock-pass').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') unlockUnifiedDashboard();
                });
            }
            modal.style.display = 'flex';
            setTimeout(() => document.getElementById('unified-lock-pass').focus(), 100);
        }

        async function unlockUnifiedDashboard() {
            const passEl = document.getElementById('unified-lock-pass');
            const pass = passEl.value.trim();
            const errEl = document.getElementById('unified-lock-error');
            if (!pass) { errEl.innerText = 'الرجاء إدخال كلمة المرور'; errEl.style.display = 'block'; return; }
            
            const btn = document.querySelector('#unified-lock-modal button');
            const oldText = btn.innerText;
            btn.innerText = 'جاري التحقق...';
            btn.disabled = true;
            errEl.style.display = 'none';

            try {
                const userStr = localStorage.getItem('user');
                let username = '';
                if (userStr) username = JSON.parse(userStr).username;
                
                const res = await fetch('api_index.php/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: username, password: pass })
                });
                const data = await res.json().catch(() => ({}));
                
                if (res.ok && data.token) {
                    sessionStorage.setItem('dashboard_unlocked', '1');
                    document.getElementById('unified-lock-modal').style.display = 'none';
                    initDashboardApp();
                } else {
                    errEl.innerText = data.error || data.message || 'كلمة المرور خاطئة';
                    errEl.style.display = 'block';
                }
            } catch (e) { 
                errEl.innerText = 'حدث خطأ في الاتصال';
                errEl.style.display = 'block';
            } finally {
                btn.innerText = oldText;
                btn.disabled = false;
            }
        }

        let unifiedIdleTime = 0;
        let unifiedIdleInterval = null;
        function setupUnifiedAutoLogout() {
            let autoLogoutValue = 0;
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                autoLogoutValue = parseInt(cfg.autoLogoutTime) || 0;
            } catch(e) {}
            
            if (autoLogoutValue > 0) {
                if (unifiedIdleInterval) clearInterval(unifiedIdleInterval);
                unifiedIdleInterval = setInterval(() => {
                    unifiedIdleTime++;
                    if (unifiedIdleTime >= autoLogoutValue) {
                        sessionStorage.removeItem('dashboard_unlocked');
                        window.location.reload(); 
                    }
                }, 60000);
                
                const resetIdle = () => unifiedIdleTime = 0;
                ['mousemove', 'keypress', 'touchstart', 'scroll'].forEach(evt => {
                    document.addEventListener(evt, resetIdle);
                });
            }
        }

        if (window.APP_READY) {
            initDashboardApp();
        } else {
            document.addEventListener('DBReady', initDashboardApp);
        }

        // Re-render when background cloud sync completes without forcing page reload loop
        window.addEventListener('cloudDataRestored', () => {
            console.log("[Dashboard] Cloud sync completed. Updating UI...");
            initDashboardApp();
        });
        window.addEventListener('strictDataReady', () => {
            console.log("[Dashboard] Strict data ready. Updating UI...");
            initDashboardApp();
        });

        // Toggle Notifications Dropdown (Modal)
        function toggleNotifications(tab = 'LIVE') {
            const dropdown = document.getElementById('notifications-dropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'flex';
                setTimeout(() => dropdown.style.opacity = '1', 10);
                switchModalNotifTab(tab);
            } else {
                dropdown.style.opacity = '0';
                setTimeout(() => dropdown.style.display = 'none', 300);
            }
        }

        function switchModalNotifTab(tab) {
            const liveTab = document.getElementById('modal-tab-live');
            const ctrlTab = document.getElementById('modal-tab-ctrl');
            const liveBtn = document.getElementById('notif-tab-live-btn');
            const ctrlBtn = document.getElementById('notif-tab-ctrl-btn');

            if (tab === 'CTRL') {
                if (liveTab) liveTab.style.display = 'none';
                if (ctrlTab) ctrlTab.style.display = 'block';
                if (liveBtn) {
                    liveBtn.style.background = 'rgba(255,255,255,0.05)';
                    liveBtn.style.borderColor = 'rgba(255,255,255,0.1)';
                    liveBtn.style.color = '#94a3b8';
                }
                if (ctrlBtn) {
                    ctrlBtn.style.background = 'rgba(245,158,11,0.25)';
                    ctrlBtn.style.borderColor = '#f59e0b';
                    ctrlBtn.style.color = '#ffffff';
                }
                loadModalAlertRules();
            } else {
                if (liveTab) liveTab.style.display = 'block';
                if (ctrlTab) ctrlTab.style.display = 'none';
                if (liveBtn) {
                    liveBtn.style.background = 'rgba(244,63,94,0.25)';
                    liveBtn.style.borderColor = '#f43f5e';
                    liveBtn.style.color = '#ffffff';
                }
                if (ctrlBtn) {
                    ctrlBtn.style.background = 'rgba(255,255,255,0.05)';
                    ctrlBtn.style.borderColor = 'rgba(255,255,255,0.1)';
                    ctrlBtn.style.color = '#94a3b8';
                }
                checkDueDates();
            }
        }

        let _cachedModalRules = [];

        function loadModalAlertRules() {
            const container = document.getElementById('modal-rules-list');
            if (!container) return;

            fetch('alarm.php?action=get_rules_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.rules) {
                    _cachedModalRules = data.rules;
                    renderModalRulesList(data.rules);
                }
            })
            .catch(() => {
                container.innerHTML = '<div style="color:#f87171; text-align:center; padding:15px; font-size:0.85rem;">تعذر جلب قواعد التنبيهات من الخادم.</div>';
            });
        }

        function renderModalRulesList(rules) {
            const container = document.getElementById('modal-rules-list');
            if (!container) return;

            if (!rules || rules.length === 0) {
                container.innerHTML = '<div style="color:#94a3b8; text-align:center; padding:15px;">لا توجد قواعد متاحة.</div>';
                return;
            }

            const catColors = {
                'FINANCE': '#38bdf8',
                'CREDIT': '#fbbf24',
                'SALES': '#34d399',
                'INVENTORY': '#c084fc',
                'SUPPLY': '#f472b6',
                'SECURITY': '#f87171'
            };

            let html = '';
            rules.forEach(r => {
                const color = catColors[r.category] || '#94a3b8';
                const isChecked = r.is_enabled == 1;
                const screenBadge = r.screen_name ? `<span style="font-size:0.65rem; color:#38bdf8; background:rgba(56,189,248,0.1); padding:1px 6px; border-radius:4px; border:1px solid rgba(56,189,248,0.25);">🏢 ${r.screen_name}</span>` : '';
                html += `
                    <div class="modal-rule-item" data-search="${(r.rule_name + ' ' + (r.screen_name||'') + ' ' + (r.screen_file||'') + ' ' + (r.description||'')).toLowerCase()}" style="padding:10px 14px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:180px;">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <strong style="font-size:0.85rem; color:#fff;">${r.rule_name}</strong>
                                <span style="font-size:0.65rem; color:${color}; background:${color}15; padding:1px 6px; border-radius:4px; border:1px solid ${color}30;">${r.category}</span>
                                ${screenBadge}
                            </div>
                            <div style="font-size:0.72rem; color:#94a3b8; margin-top:3px; line-height:1.4;">${r.description || ''}</div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="display:flex; align-items:center; gap:3px; font-size:0.75rem; background:rgba(0,0,0,0.3); padding:3px 6px; border-radius:8px;">
                                <span style="color:#fbbf24;">⚠️</span>
                                <input type="number" id="mwarn-${r.rule_key}" value="${r.warning_threshold}" style="width:40px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fbbf24; text-align:center; font-weight:800; font-size:0.75rem; border-radius:4px; padding:1px;" onchange="saveModalThresholds('${r.rule_key}')">
                                <span style="color:#f87171; margin-right:3px;">🚨</span>
                                <input type="number" id="mcrit-${r.rule_key}" value="${r.critical_threshold}" style="width:40px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#f87171; text-align:center; font-weight:800; font-size:0.75rem; border-radius:4px; padding:1px;" onchange="saveModalThresholds('${r.rule_key}')">
                            </div>
                            <label style="position:relative; display:inline-block; width:44px; height:24px; margin:0; cursor:pointer;">
                                <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleModalRule('${r.rule_key}', this.checked)" style="opacity:0; width:0; height:0;">
                                <span style="position:absolute; top:0; left:0; right:0; bottom:0; background-color:${isChecked ? '#10b981' : '#334155'}; transition:.3s; border-radius:24px; border:1px solid rgba(255,255,255,0.2);">
                                    <span style="position:absolute; content:''; height:16px; width:16px; left:${isChecked ? '23px' : '4px'}; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function filterModalRules(q) {
            const query = q.trim().toLowerCase();
            const items = document.querySelectorAll('.modal-rule-item');
            items.forEach(it => {
                const text = it.getAttribute('data-search') || '';
                it.style.display = (query === '' || text.includes(query)) ? 'flex' : 'none';
            });
        }

        function toggleModalRule(ruleKey, isChecked) {
            const formData = new FormData();
            formData.append('action', 'toggle_rule_ajax');
            formData.append('rule_key', ruleKey);
            formData.append('is_enabled', isChecked ? 1 : 0);

            fetch('alarm.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    loadModalAlertRules();
                    checkDueDates();
                }
            });
        }

        function saveModalThresholds(ruleKey) {
            const warn = document.getElementById('mwarn-' + ruleKey).value;
            const crit = document.getElementById('mcrit-' + ruleKey).value;

            const formData = new FormData();
            formData.append('action', 'update_thresholds_ajax');
            formData.append('rule_key', ruleKey);
            formData.append('warning_threshold', warn);
            formData.append('critical_threshold', crit);

            fetch('alarm.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    checkDueDates();
                }
            });
        }

        // Check Due Dates
        function checkDueDates() {
            const invoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted);
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            
            let notifications = [];
            let unreadCount = 0;
            const today = new Date();
            today.setHours(0,0,0,0);

            invoices.forEach(inv => {
                if (inv.paymentMethod === 'credit' && inv.dueDate) {
                    const dueDate = new Date(inv.dueDate);
                    dueDate.setHours(0,0,0,0);
                    
                    const diffTime = dueDate - today;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    const cust = customers.find(c => c.id === inv.customer);
                    const custName = cust ? cust.name : inv.customer;
                    const custPhone = cust && cust.phone ? cust.phone : 'غير متوفر';

                    if (diffDays < 0) {
                        // Past due (Red)
                        notifications.push(`
                            <div style="padding:12px; background:rgba(248,113,113,0.1); border-radius:12px; border:1px solid rgba(248,113,113,0.3);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:#f87171; font-size:0.95rem;">🔴 تأخر سداد!</strong>
                                    <span style="font-size:0.75rem; color:var(--text-secondary); background:rgba(248,113,113,0.2); padding:2px 8px; border-radius:10px;">منذ ${Math.abs(diffDays)} أيام</span>
                                </div>
                                <div style="font-size:0.85rem; color:#fff; margin-bottom:6px; line-height: 1.5;">
                                    العميل: <strong style="color:var(--theme-blue);">${custName}</strong><br>
                                    فاتورة: ${inv.invoiceNo}
                                </div>
                                <div style="font-size:0.8rem; color:#fff; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    📞 الاتصال: ${custPhone}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    } else if (diffDays <= 2) {
                        // Nearing due date (Yellow)
                        let timeMsg = diffDays === 0 ? 'اليوم!' : (diffDays === 1 ? 'غداً' : 'بعد يومين');
                        notifications.push(`
                            <div style="padding:12px; background:rgba(251,191,36,0.1); border-radius:12px; border:1px solid rgba(251,191,36,0.3);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:#fbbf24; font-size:0.95rem;">🟡 اقترب موعد السداد</strong>
                                    <span style="font-size:0.75rem; color:var(--text-secondary); background:rgba(251,191,36,0.2); padding:2px 8px; border-radius:10px;">${timeMsg}</span>
                                </div>
                                <div style="font-size:0.85rem; color:#fff; margin-bottom:6px; line-height: 1.5;">
                                    العميل: <strong style="color:var(--theme-blue);">${custName}</strong><br>
                                    فاتورة: ${inv.invoiceNo}
                                </div>
                                <div style="font-size:0.8rem; color:#fff; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    📞 الاتصال: ${custPhone}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    }
                }
            });

            // Fetch live database alerts from alarm.php
            fetch('alarm.php?action=get_live_alerts_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.alerts && data.alerts.length > 0) {
                    data.alerts.forEach(a => {
                        const isRed = a.severity === 'RED';
                        const bg = isRed ? 'rgba(239, 68, 68, 0.15)' : 'rgba(245, 158, 11, 0.15)';
                        const border = isRed ? 'rgba(239, 68, 68, 0.4)' : 'rgba(245, 158, 11, 0.4)';
                        const color = isRed ? '#f87171' : '#fbbf24';
                        const title = isRed ? '🚨 إنذار رقابي حرج' : '⚠️ تنبيه للمتابعة';

                        notifications.unshift(`
                            <div style="padding:12px; background:${bg}; border-radius:12px; border:1px solid ${border};">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:${color}; font-size:0.92rem;">${title}</strong>
                                    <span style="font-size:0.7rem; color:${color}; background:rgba(0,0,0,0.3); padding:2px 6px; border-radius:6px;">نظامي</span>
                                </div>
                                <div style="font-size:0.82rem; color:#fff; line-height: 1.5;">
                                    ${a.message}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    });
                }

                // Add footer link to Alerts Manager
                notifications.push(`
                    <div style="margin-top: 10px; text-align: center;">
                        <a href="alarm.php" style="display: block; padding: 10px; background: rgba(244, 63, 94, 0.2); border: 1px dashed #f43f5e; color: #fda4af; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 0.85rem; transition: background 0.2s;" onmouseover="this.style.background='rgba(244, 63, 94, 0.35)'" onmouseout="this.style.background='rgba(244, 63, 94, 0.2)'">
                            ⚙️ فتح مركز إدارة وتخصيص التنبيهات
                        </a>
                    </div>
                `);

                const badge = document.getElementById('notif-badge');
                const list = document.getElementById('notif-list');

                if (badge) {
                    if (unreadCount > 0) {
                        badge.innerText = unreadCount;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                if (list) {
                    list.innerHTML = notifications.join('');
                }
            })
            .catch(() => {
                const badge = document.getElementById('notif-badge');
                const list = document.getElementById('notif-list');
                if (badge) {
                    if (unreadCount > 0) { badge.innerText = unreadCount; badge.style.display = 'block'; }
                    else { badge.style.display = 'none'; }
                }
                if (list) {
                    if (unreadCount > 0) list.innerHTML = notifications.join('');
                    else list.innerHTML = '<div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px;">لا توجد تنبيهات حالياً 🎉</div>';
                }
            });
        }

        // ═════════════════════════════════════════════════════════════════════
        //  LIVE EXCHANGE RATE & MULTI-CURRENCY COMMAND ENGINE
        // ═════════════════════════════════════════════════════════════════════
        window.dashBaseCurrency = 'SDG';
        window.dashForeignCurrency = 'USD';
        window.dashRateUSD = 1;
        window.dashDisplayCurrency = 'SDG';

        function syncDashboardFXSettings() {
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                window.dashBaseCurrency = cfg.currency || 'SDG';
                window.dashForeignCurrency = cfg.foreignCurrency || 'USD';
                window.dashRateUSD = parseFloat(cfg.rateUSD) || 1;
                if (!window._dashCurrencyInitialized) {
                    window.dashDisplayCurrency = window.dashBaseCurrency;
                    window._dashCurrencyInitialized = true;
                }
                const badge = document.getElementById('dash-live-fx-badge');
                if (badge) {
                    const modeTag = cfg.exchangeMode === 'global' ? '🌐' : '✍️';
                    badge.innerHTML = `${modeTag} 1 ${window.dashForeignCurrency} = ${window.dashRateUSD.toLocaleString('en-US')} ${window.dashBaseCurrency}`;
                }
                const toggleBtn = document.getElementById('dash-cur-toggle-btn');
                if (toggleBtn) {
                    toggleBtn.innerHTML = window.dashDisplayCurrency === window.dashForeignCurrency
                        ? `عرض المؤشرات: ${window.dashForeignCurrency} 💵`
                        : `عرض المؤشرات: ${window.dashBaseCurrency} 🇸🇩`;
                }
            } catch (e) {}
        }

        function convertDashVal(amount, recordCurrency, recordRate) {
            const val = parseFloat(amount) || 0;
            if (val === 0) return 0;
            const recCur = String(recordCurrency || window.dashBaseCurrency).toUpperCase();
            const rate = parseFloat(recordRate) || window.dashRateUSD || 1;
            const inBase = (recCur === window.dashForeignCurrency) ? (val * rate) : val;
            if (window.dashDisplayCurrency === window.dashForeignCurrency) {
                return window.dashRateUSD > 0 ? Math.round((inBase / window.dashRateUSD) * 100) / 100 : inBase;
            }
            return Math.round(inBase);
        }

        function toggleDashboardCurrency() {
            syncDashboardFXSettings();
            window.dashDisplayCurrency = (window.dashDisplayCurrency === window.dashBaseCurrency)
                ? window.dashForeignCurrency
                : window.dashBaseCurrency;
            syncDashboardFXSettings();
            initProCharts();
            showToast(`💱 تم تحويل عرض مؤشرات لوحة التحكم إلى عملة (${window.dashDisplayCurrency})`, 'success');
        }

        window.openDashboardFXModal = function() {
            syncDashboardFXSettings();
            let modal = document.getElementById('dash-fx-command-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'dash-fx-command-modal';
                modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(4,8,18,0.88); backdrop-filter:blur(10px); z-index:999999; align-items:center; justify-content:center; padding:16px; direction:rtl;';
                modal.innerHTML = `
                    <div style="background:#0f172a; border:2px solid #38bdf8; border-radius:20px; width:100%; max-width:650px; max-height:90vh; overflow-y:auto; padding:22px; box-shadow:0 25px 80px rgba(0,0,0,0.9); color:#f8fafc; font-family:'Tajawal',sans-serif;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(148,163,184,0.2); padding-bottom:12px; margin-bottom:16px;">
                            <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#38bdf8; display:flex; align-items:center; gap:8px;">
                                <span>💱</span> بورصة ومحول سعر الصرف والعملات اللحظي 360°
                            </h3>
                            <button type="button" onclick="document.getElementById('dash-fx-command-modal').style.display='none'" style="background:rgba(248,113,113,0.2); border:1px solid #f87171; color:#f87171; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">إغلاق &times;</button>
                        </div>

                        <div style="background:rgba(30,41,59,0.75); border:1px solid rgba(56,189,248,0.3); border-radius:14px; padding:14px; margin-bottom:14px;">
                            <div style="font-size:0.82rem; font-weight:800; color:#fbbf24; margin-bottom:8px;">⚡ تحديث سعر الصرف المعتمد في النظام (يسمّع في كل الفواتير، القيود، والتقارير فوراً):</div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                <span style="font-weight:900; color:#cbd5e1;" id="fx-modal-pair-label">1 USD =</span>
                                <input type="number" step="any" id="fx-modal-rate-input" style="flex:1; min-width:130px; background:#090d16; border:1.5px solid #38bdf8; color:#38bdf8; font-size:1.15rem; font-weight:900; text-align:center; padding:8px; border-radius:10px; font-family:'Outfit',sans-serif;">
                                <span style="font-weight:900; color:#fbbf24;" id="fx-modal-base-label">SDG</span>
                                <button type="button" onclick="fetchGlobalFXRateInModal()" style="background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#38bdf8; padding:8px 12px; border-radius:10px; font-family:'Tajawal'; font-weight:800; cursor:pointer;">🌐 جلب العالمي</button>
                                <button type="button" onclick="saveDashboardFXRate()" style="background:linear-gradient(135deg,#059669,#10b981); border:1px solid #34d399; color:#fff; padding:8px 16px; border-radius:10px; font-family:'Tajawal'; font-weight:900; cursor:pointer;">💾 حفظ واعتماد للكل</button>
                            </div>
                        </div>

                        <div style="background:rgba(15,23,42,0.9); border:1px solid rgba(148,163,184,0.2); border-radius:14px; padding:14px; margin-bottom:14px;">
                            <div style="font-size:0.82rem; font-weight:800; color:#34d399; margin-bottom:8px;">🧮 حاسبة التحويل الفوري المزدوجة:</div>
                            <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:10px; align-items:center;">
                                <div>
                                    <label style="font-size:0.75rem; color:#94a3b8; display:block; margin-bottom:4px;">المبلغ بالعملة الأجنبية (<span id="fx-conv-for-lbl">USD</span>)</label>
                                    <input type="number" step="any" id="fx-conv-usd" oninput="calcFXModalConverter('usd')" placeholder="100" style="width:100%; box-sizing:border-box; background:#1e293b; border:1px solid #475569; color:#fff; padding:8px; border-radius:8px; font-family:'Outfit',sans-serif; font-weight:800;">
                                </div>
                                <div style="font-size:1.2rem; color:#38bdf8; padding-top:16px;">⇄</div>
                                <div>
                                    <label style="font-size:0.75rem; color:#94a3b8; display:block; margin-bottom:4px;">المعادل بالعملة الأساسية (<span id="fx-conv-base-lbl">SDG</span>)</label>
                                    <input type="number" step="any" id="fx-conv-sdg" oninput="calcFXModalConverter('sdg')" placeholder="0" style="width:100%; box-sizing:border-box; background:#1e293b; border:1px solid #475569; color:#fbbf24; padding:8px; border-radius:8px; font-family:'Outfit',sans-serif; font-weight:800;">
                                </div>
                            </div>
                        </div>

                        <div id="fx-modal-exposure-box" style="background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.3); border-radius:14px; padding:12px 14px; margin-bottom:14px; font-size:0.82rem; line-height:1.7;"></div>

                        <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                            <a href="journal-voucher.php?desc=قيد+تسوية+فروق+تقييم+عملة+أجنبية" style="flex:1; text-align:center; background:rgba(168,85,247,0.22); border:1px solid #c084fc; color:#e9d5ff; padding:9px 12px; border-radius:10px; text-decoration:none; font-weight:800; font-size:0.82rem;">⚖️ إثبات قيد فروق عملة</a>
                            <a href="settings.php" style="flex:1; text-align:center; background:rgba(56,189,248,0.18); border:1px solid #38bdf8; color:#38bdf8; padding:9px 12px; border-radius:10px; text-decoration:none; font-weight:800; font-size:0.82rem;">⚙️ إعدادات وسجل أسعار الصرف</a>
                            <button type="button" onclick="toggleDashboardCurrency(); document.getElementById('dash-fx-command-modal').style.display='none';" style="flex:1; background:rgba(245,158,11,0.22); border:1px solid #fbbf24; color:#fbbf24; padding:9px 12px; border-radius:10px; font-family:'Tajawal'; font-weight:800; font-size:0.82rem; cursor:pointer;">🔄 تبديل عملة لوحة التحكم</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            document.getElementById('fx-modal-pair-label').innerText = `1 ${window.dashForeignCurrency} =`;
            document.getElementById('fx-modal-base-label').innerText = window.dashBaseCurrency;
            document.getElementById('fx-conv-for-lbl').innerText = window.dashForeignCurrency;
            document.getElementById('fx-conv-base-lbl').innerText = window.dashBaseCurrency;
            document.getElementById('fx-modal-rate-input').value = window.dashRateUSD;
            document.getElementById('fx-conv-usd').value = 100;
            document.getElementById('fx-conv-sdg').value = Math.round(100 * window.dashRateUSD * 100) / 100;

            const custs = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => !x.deleted);
            const sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(x => !x.deleted);
            const arTotal = custs.reduce((s, c) => s + Math.max(0, parseFloat(c.currentBalance) || 0), 0);
            const apTotal = sups.reduce((s, x) => s + Math.abs(parseFloat(x.currentBalance) || 0), 0);
            const rate = window.dashRateUSD > 0 ? window.dashRateUSD : 1;
            const expBox = document.getElementById('fx-modal-exposure-box');
            if (expBox) {
                expBox.innerHTML = `
                    <strong style="color:#c084fc;">📊 تقييم المركز المالي المزدوج حسب سعر الصرف الحالي (${rate.toLocaleString()}):</strong><br>
                    • إجمالي مديونية العملاء (AR): <strong style="color:#34d399;">${Math.round(arTotal).toLocaleString()} ${window.dashBaseCurrency}</strong> (يعادل <strong style="color:#38bdf8;">${Math.round(arTotal / rate).toLocaleString()} ${window.dashForeignCurrency}</strong>)<br>
                    • إجمالي التزامات الموردين (AP): <strong style="color:#f87171;">${Math.round(apTotal).toLocaleString()} ${window.dashBaseCurrency}</strong> (يعادل <strong style="color:#fbbf24;">${Math.round(apTotal / rate).toLocaleString()} ${window.dashForeignCurrency}</strong>)
                `;
            }
            modal.style.display = 'flex';
        };

        window.calcFXModalConverter = function(dir) {
            const rate = parseFloat(document.getElementById('fx-modal-rate-input')?.value) || window.dashRateUSD || 1;
            const usdEl = document.getElementById('fx-conv-usd');
            const sdgEl = document.getElementById('fx-conv-sdg');
            if (dir === 'usd') {
                const u = parseFloat(usdEl.value) || 0;
                sdgEl.value = Math.round(u * rate * 100) / 100;
            } else {
                const s = parseFloat(sdgEl.value) || 0;
                usdEl.value = rate > 0 ? (Math.round((s / rate) * 100) / 100) : 0;
            }
        };

        window.fetchGlobalFXRateInModal = function() {
            fetch('api_index.php/exchange-rate')
                .then(r => r.json())
                .then(d => {
                    const rVal = parseFloat(d.rate || d.SDG || d.rateUSD);
                    if (rVal > 0) {
                        document.getElementById('fx-modal-rate-input').value = rVal;
                        window.calcFXModalConverter('usd');
                        showToast(`🌐 تم جلب السعر العالمي بنجاح: ${rVal}`, 'success');
                    } else {
                        showToast('تعذر جلب السعر العالمي حالياً، يمكنك إدخاله يدوياً', 'warning');
                    }
                })
                .catch(() => showToast('تعذر الاتصال بخدمة الأسعار العالمية، أدخل السعر يدوياً', 'warning'));
        };

        window.saveDashboardFXRate = function() {
            const newRate = parseFloat(document.getElementById('fx-modal-rate-input')?.value);
            if (!newRate || newRate <= 0) {
                showToast('يرجى إدخال سعر صرف صحيح أكبر من الصفر', 'error');
                return;
            }
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                const oldRate = cfg.rateUSD;
                cfg.rateUSD = newRate;
                cfg.exchangeMode = 'manual';
                cfg.savedAt = new Date().toISOString();
                localStorage.setItem('erp_settings_v1', JSON.stringify(cfg));

                const histKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
                const hist = JSON.parse(localStorage.getItem(histKey) || '[]');
                hist.unshift({
                    date: new Date().toLocaleString('ar-EG'),
                    rate: newRate,
                    oldRate: oldRate || newRate,
                    user: (JSON.parse(localStorage.getItem('user') || '{}').username) || 'الإدارة',
                    source: 'لوحة التحكم'
                });
                localStorage.setItem(histKey, JSON.stringify(hist.slice(0, 100)));

                fetch('api_index.php/tenant-exchange-rate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rateUSD: newRate, exchangeMode: 'manual' })
                }).catch(() => {});

                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('تحديث سعر الصرف من لوحة التحكم', `تم تعديل سعر الصرف من (${oldRate || 1}) إلى (${newRate}) ${window.dashBaseCurrency}`, 'SETTINGS');
                }

                syncDashboardFXSettings();
                initProCharts();
                document.getElementById('dash-fx-command-modal').style.display = 'none';
                showToast(`✅ تم اعتماد سعر الصرف الجديد (${newRate} ${window.dashBaseCurrency}) وتعميمه على كل الشاشات والتقارير!`, 'success');
            } catch (e) {
                showToast('حدث خطأ أثناء حفظ سعر الصرف', 'error');
            }
        };

        // Load Dynamic Dashboard Data
        function loadDynamicDashboardData() {
            syncDashboardFXSettings();
            const curSuffix = window.dashDisplayCurrency || 'SDG';

            // Fetch data
            const invoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
            const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
            const vaults = (JSON.parse(localStorage.getItem('erp_vaults_v1')) || []).filter(x => !x.deleted);
            const items = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
            const gl = (JSON.parse(localStorage.getItem('erp_gl_v1')) || []).filter(x => !x.deleted);
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];

            // Helper to get total actual stock of an item across warehouses (ignoring virtual pending states)
            function getItemActualStock(itemId) {
                return inventory
                    .filter(inv => inv.itemId === itemId && inv.warehouse !== 'PENDING_RECEIPT')
                    .reduce((sum, inv) => sum + (Number(inv.qty) || 0), 0);
            }

            // Get EXACT local date YYYY-MM-DD
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const todayStr = `${year}-${month}-${day}`;

            const sevenDaysAgo = new Date(now);
            sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
            const sevenDaysAgoStr = sevenDaysAgo.toISOString().split('T')[0];

            // Filter invoices by scope
            let scopedInvoices = invoices;
            if (window.currentDashboardScope === 'today') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(todayStr));
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات اليوم';
            } else if (window.currentDashboardScope === 'week') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date >= sevenDaysAgoStr);
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات آخر 7 أيام';
            } else if (window.currentDashboardScope === 'month') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(`${year}-${month}`));
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات الشهر';
            } else {
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'إجمالي المبيعات';
            }

            // 1. Calculate Scoped Sales with Multi-Currency awareness
            const totalScopedSales = scopedInvoices.reduce((sum, inv) => {
                const rawAmt = Number(inv.grandTotal) || Number(inv.totalAmount) || 0;
                return sum + convertDashVal(rawAmt, inv.currency, inv.exchangeRate);
            }, 0);
            animateValue('kpi-sales', 0, totalScopedSales, 1300, curSuffix);

            // 2. Calculate Vault Liquidity & Total Liquidity (reading both erp_vaults_v1 and erp_banks_v1)
            let vaultLiquidity = 0;
            let bankLiquidity = 0;
            vaults.forEach(v => {
                let bal = Number(v.currentBalance) ?? Number(v.balance) ?? Number(v.openingBalance) ?? 0;
                vaultLiquidity += convertDashVal(bal, v.currency, v.exchangeRate);
            });
            banks.forEach(b => {
                let bal = Number(b.currentBalance) ?? Number(b.balance) ?? Number(b.openingBalance) ?? 0;
                if (b.type === 'cash' || String(b.name || '').includes('صندوق') || String(b.name || '').includes('خزينة')) {
                    vaultLiquidity += convertDashVal(bal, b.currency, b.exchangeRate);
                } else {
                    bankLiquidity += convertDashVal(bal, b.currency, b.exchangeRate);
                }
            });
            let totalLiquidity = vaultLiquidity + bankLiquidity;

            animateValue('kpi-vault-liquidity', 0, vaultLiquidity, 1400, curSuffix);
            animateValue('kpi-liquidity', 0, totalLiquidity, 1400, curSuffix);

            // 4. Total Items
            const totalItems = items.length;
            animateValue('kpi-inventory', 0, totalItems, 1200, 'صنف');
            
            let warehousesList = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesList.length === 0) {
                warehousesList = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }
            const activeWhsCount = warehousesList.filter(w => !w.deleted).length;
            const whsCountEl = document.getElementById('kpi-warehouses-count');
            if (whsCountEl) {
                whsCountEl.innerText = `موزعة على ${activeWhsCount} ${activeWhsCount > 2 && activeWhsCount < 11 ? 'مخازن' : 'مخزن'}`;
            }

            // 5. Low Stock computed from actual inventory levels
            const lowStockItems = items.filter(item => {
                let available = getItemActualStock(item.id);
                let safeLimit = Number(item.safeLimit) || Number(item.minQty) || 10;
                return available <= safeLimit;
            });
            animateValue('kpi-alerts', 0, lowStockItems.length, 1200, 'أصناف');

            const lowStockHTML = lowStockItems.slice(0, 5).map(item => {
                const stockVal = getItemActualStock(item.id);
                return `
                <div class="list-item" style="background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2);">
                    <div class="list-icon" style="background:var(--theme-red); color:#fff; box-shadow: 0 3px 10px rgba(248,113,113,0.3);">!</div>
                    <div style="flex:1;">
                        <h4 class="list-title">${item.name}</h4>
                        <span class="list-sub">الرصيد: <strong style="color:var(--theme-red); font-size: 0.9rem;">${stockVal}</strong></span>
                    </div>
                    <a href="purchase.php" data-href="purchase.php" onclick="window.location.href='purchase.php'" class="list-btn" style="box-shadow: 0 2px 8px rgba(99,102,241,0.3);">طلب</a>
                </div>
                `;
            }).join('');
            document.getElementById('low-stock-list').innerHTML = lowStockHTML || '<div style="color:var(--text-secondary); font-size:0.85rem; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 10px; text-align: center;">المخزون آمن حالياً ✅</div>';

            // Dynamic cost calculation
            const _cachedPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
            function getItemCost(itemId) {
                const item = items.find(i => i.id === itemId);
                if (item && item.openingQty > 0 && item.openingValue > 0) {
                    return Number(item.openingValue) / Number(item.openingQty);
                }
                const purchases = _cachedPurchases;
                let lastPrice = null;
                for (let inv of purchases) {
                    if (inv.deleted) continue;
                    const line = (inv.items || inv.lines || []).find(l => l.itemId === itemId || l.productId === itemId);
                    if (line && line.price) {
                        lastPrice = Number(line.price);
                        break;
                    }
                }
                if (lastPrice !== null) return lastPrice;
                if (item) {
                    let sp = item.sellingPrice || (item.units && item.units[0] ? item.units[0].cashPrice : 0) || 0;
                    return sp * 0.7; // default 30% margin fallback
                }
                return 0;
            }

            // Calculate Real Scoped Profit (using actual item costs)
            const todayInvoices = scopedInvoices;
            let todayProfitBase = 0;
            todayInvoices.forEach(inv => {
                let invProfit = 0;
                (inv.items || []).forEach(line => {
                    const cost = getItemCost(line.itemId);
                    const sellPrice = Number(line.price) || 0;
                    const qty = Number(line.quantity) || Number(line.qty) || 0;
                    invProfit += (sellPrice - cost) * qty;
                });
                if (invProfit === 0 && (Number(inv.grandTotal) || Number(inv.totalAmount))) {
                    invProfit = (Number(inv.grandTotal) || Number(inv.totalAmount)) * 0.22;
                }
                const disc = Number(inv.discount) || 0;
                todayProfitBase += (invProfit - disc);
            });
            const todayProfit = convertDashVal(todayProfitBase, window.dashBaseCurrency, window.dashRateUSD);
            animateValue('kpi-monthly-profit', 0, todayProfit, 1500, curSuffix);

            const currentMonthStr = `${year}-${month}`;
            const monthlyInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(currentMonthStr));
            let monthlyProfitBase = 0;
            monthlyInvoices.forEach(inv => {
                let invProfit = 0;
                (inv.items || []).forEach(line => {
                    const cost = getItemCost(line.itemId);
                    const sellPrice = Number(line.price) || 0;
                    const qty = Number(line.quantity) || Number(line.qty) || 0;
                    invProfit += (sellPrice - cost) * qty;
                });
                const disc = Number(inv.discount) || 0;
                monthlyProfitBase += (invProfit - disc);
            });
            const monthlyProfit = convertDashVal(monthlyProfitBase, window.dashBaseCurrency, window.dashRateUSD);

            // Update Ticker
            if (document.getElementById('ticker-sales')) document.getElementById('ticker-sales').innerText = totalScopedSales.toLocaleString('en-US') + ' ' + curSuffix;
            if (document.getElementById('ticker-profit')) document.getElementById('ticker-profit').innerText = todayProfit.toLocaleString('en-US') + ' ' + curSuffix;
            if (document.getElementById('ticker-monthly-profit')) document.getElementById('ticker-monthly-profit').innerText = monthlyProfit.toLocaleString('en-US') + ' ' + curSuffix;
            if (document.getElementById('ticker-liquidity')) document.getElementById('ticker-liquidity').innerText = totalLiquidity.toLocaleString('en-US') + ' ' + curSuffix;

            // Trigger Stock Alerts Toast if any critical items
            const criticalItems = lowStockItems.filter(item => getItemActualStock(item.id) <= 0);
            if (criticalItems.length > 0 && !sessionStorage.getItem('stock_alert_shown')) {
                showToast(`⚠️ تنبيه مخزون: هناك ${criticalItems.length} أصناف نفذت تماماً من المخزن! يرجى مراجعة التنبيهات.`, 'error');
                sessionStorage.setItem('stock_alert_shown', 'true');
            }

            // 4.5 Pending Deliveries
            const pendingDeliveries = scopedInvoices.filter(inv => {
                const s = inv.deliveryStatus;
                return s !== 'delivered' && s !== 'Admin_Closed' && s !== 'Delivered_Pending_Admin';
            }).slice(0, 5);
            
            const pendingContainer = document.getElementById('pending-deliveries-list');
            if (pendingContainer) {
                if (pendingDeliveries.length > 0) {
                    pendingContainer.innerHTML = pendingDeliveries.map(inv => {
                        const custName = customers.find(c => c.id === inv.customerId || c.id === inv.customer)?.name || inv.customerName || inv.customer || inv.customerId || 'عميل';
                        const invDate = inv.date || '-';
                        return `
                        <div class="list-item" style="background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.2);">
                            <div class="list-icon" style="background:var(--theme-orange); color:#fff; box-shadow: 0 3px 10px rgba(245,158,11,0.3);">🚚</div>
                            <div style="flex:1;">
                                <h4 class="list-title">إذن #${inv.invoiceNo || inv.id}</h4>
                                <span class="list-sub"><span style="color:#e2e8f0;">${custName}</span> | ${invDate}</span>
                            </div>
                            <a href="sales-delivery.php" data-href="sales-delivery.php" onclick="window.location.href='sales-delivery.php'" class="list-btn" style="box-shadow: 0 2px 8px rgba(99,102,241,0.3);">تسليم</a>
                        </div>
                        `;
                    }).join('');
                } else {
                    pendingContainer.innerHTML = '<div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding: 15px; background: rgba(255,255,255,0.02); border-radius: 10px;">كل الأذونات مسلمة! 🎉</div>';
                }
            }

            // 4.8 Executive Jobs & Tasks Widget (connected to erp_jobs_v1 / jobs.php)
            const jobsList = (JSON.parse(localStorage.getItem('erp_jobs_v1') || '[]')).filter(j => j.status !== 'done');
            const topJobsCountEl = document.getElementById('dash-top-jobs-count');
            if (topJobsCountEl) topJobsCountEl.innerText = jobsList.length;
            const dashJobsContainer = document.getElementById('dashboard-jobs-list');
            if (dashJobsContainer) {
                if (jobsList.length > 0) {
                    dashJobsContainer.innerHTML = jobsList.slice(0, 4).map(j => {
                        const isCrit = j.priority === 'critical' || j.priority === 'high';
                        return `
                        <div class="list-item" style="background:${isCrit ? 'rgba(244,63,94,0.08)' : 'rgba(168,85,247,0.08)'}; border:1px solid ${isCrit ? 'rgba(244,63,94,0.25)' : 'rgba(168,85,247,0.25)'};">
                            <div class="list-icon" style="background:${isCrit ? '#e11d48' : '#7c3aed'}; color:#fff;">💼</div>
                            <div style="flex:1; min-width:0;">
                                <h4 class="list-title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${j.title}</h4>
                                <span class="list-sub">👤 ${j.user || 'غير مسند'} ${j.dueDate ? '| 📅 ' + j.dueDate : ''}</span>
                            </div>
                            <a href="${j.linkedScreen || 'jobs.php'}" class="list-btn" style="background:rgba(168,85,247,0.25); border:1px solid #c084fc; color:#fff;">تنفيذ</a>
                        </div>
                        `;
                    }).join('');
                } else {
                    dashJobsContainer.innerHTML = `
                        <div style="color:var(--text-secondary); text-align:center; font-size:0.82rem; padding:14px; background:rgba(255,255,255,0.02); border-radius:10px;">
                            لا توجد مهام معلقة حالياً ✅
                            <div style="margin-top:8px;">
                                <a href="jobs.php" style="color:#38bdf8; font-weight:800; text-decoration:none;">➕ توليد مهام ذكية من النظام</a>
                            </div>
                        </div>
                    `;
                }
            }

            // 5. Recent Transactions (with clickable drill-down links)
            let allTx = [];
            scopedInvoices.forEach(i => allTx.push({ id: i.invoiceNo || i.id, type: 'مبيعات', desc: 'فاتورة مبيعات', amount: convertDashVal(i.grandTotal || i.totalAmount, i.currency, i.exchangeRate), date: i.date, href: 'direct-sales.php' }));
            
            const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn);
            purchases.forEach(i => allTx.push({ id: i.invoiceNo || i.id, type: 'مشتريات', desc: 'فاتورة مشتريات', amount: convertDashVal(i.grandTotal || i.totalAmount, i.currency, i.exchangeRate), date: i.date, href: 'purchase.php' }));

            const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
            sReceipts.forEach(i => allTx.push({ id: i.id, type: 'قبض', desc: i.description || 'سند قبض مفرد', amount: convertDashVal(i.amount, i.currency, i.exchangeRate), date: i.date, href: 'single-cash-payment.php' }));

            const sPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
            sPayments.forEach(i => allTx.push({ id: i.id, type: 'صرف', desc: i.description || 'سند صرف مفرد', amount: convertDashVal(i.amount, i.currency, i.exchangeRate), date: i.date, href: 'single-cash-payment.php' }));
            
            const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
            jvs.forEach(i => allTx.push({ id: i.voucherNo || i.id, type: 'قيد يومية', desc: i.statement || 'قيد مالي', amount: convertDashVal(i.totalAmount || i.totalDebit, i.currency, i.exchangeRate), date: i.date, href: `journal-voucher.php?id=${encodeURIComponent(i.id || '')}` }));

            allTx.sort((a, b) => new Date(b.date || 0) - new Date(a.date || 0));

            const recentTx = allTx.slice(0, 5);
            const recentHTML = recentTx.map(entry => {
                let badge = '<span style="background:rgba(16, 185, 129, 0.15); color:var(--theme-green); padding:4px 8px; border-radius:8px; font-size:0.75rem; font-weight: 800; border: 1px solid rgba(16,185,129,0.3);">مكتمل ✅</span>';
                return `
                <tr onclick="window.location.href='${entry.href || 'audit-log.php'}'" style="cursor:pointer;" title="انقر لفتح المستند">
                    <td><strong style="color:var(--theme-blue);">#${entry.id}</strong></td>
                    <td><span class="type-badge">${entry.type}</span></td>
                    <td>${entry.desc || '-'}</td>
                    <td><strong style="color:#fff;">${(Number(entry.amount)||0).toLocaleString()} ${curSuffix}</strong></td>
                    <td>${badge}</td>
                </tr>
                `;
            }).join('');
            document.getElementById('recent-transactions-tbody').innerHTML = recentHTML || '<tr><td colspan="5" style="text-align:center; padding:15px; color:var(--text-secondary); font-size: 0.85rem;">لا توجد حركات مالية مسجلة بعد.</td></tr>';

            if (new URLSearchParams(window.location.search).get('modal') === 'fx' && !window._fxModalAutoOpened) {
                window._fxModalAutoOpened = true;
                setTimeout(() => window.openDashboardFXModal(), 300);
            }

            return { invoices: scopedInvoices, items, customers, banks };
        }

        // Initialize Chart.js Graphs
        function initProCharts() {
            const data = loadDynamicDashboardData();
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            
            const textColor = '#94a3b8';
            const gridColor = 'rgba(255, 255, 255, 0.05)';

            Chart.defaults.color = textColor;
            Chart.defaults.font.family = "'Tajawal', sans-serif";
            Chart.defaults.font.size = 11;

            // 1. Cashflow Chart (Dynamic Weekly) using grandTotal/totalAmount
            const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
            const sPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
            
            let daysLabels = [];
            let receiptsData = [0,0,0,0,0,0,0];
            let paymentsData = [0,0,0,0,0,0,0];
            
            const today = new Date();
            for(let i=6; i>=0; i--) {
                let d = new Date(today);
                d.setDate(d.getDate() - i);
                const dayStr = d.toLocaleDateString('ar-EG', { weekday: 'short' });
                daysLabels.push(dayStr);
                
                const dStr = d.toISOString().split('T')[0];
                
                let dayReceipts = sReceipts.filter(r => (r.date || '').startsWith(dStr) && !r.deleted).reduce((sum, r) => sum + (Number(r.amount) || 0), 0);
                let cashSales = data.invoices.filter(inv => (inv.date || '').startsWith(dStr) && inv.paymentMethod !== 'credit' && !inv.deleted).reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);
                
                let dayPayments = sPayments.filter(p => (p.date || '').startsWith(dStr) && !p.deleted).reduce((sum, p) => sum + (Number(p.amount) || 0), 0);
                const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted);
                let cashPurchases = purchases.filter(inv => (inv.date || '').startsWith(dStr) && inv.paymentMethod !== 'credit').reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);

                receiptsData[6-i] = convertDashVal(dayReceipts + cashSales, window.dashBaseCurrency, window.dashRateUSD);
                paymentsData[6-i] = convertDashVal(dayPayments + cashPurchases, window.dashBaseCurrency, window.dashRateUSD);
            }

            let totalReceiptsInScope = receiptsData.reduce((a, b) => a + b, 0);
            let totalPaymentsInScope = paymentsData.reduce((a, b) => a + b, 0);
            let netCashflow = totalReceiptsInScope - totalPaymentsInScope;
            const cfCur = window.dashDisplayCurrency || 'SDG';

            if (document.getElementById('cf-summary-in')) document.getElementById('cf-summary-in').innerText = totalReceiptsInScope.toLocaleString('en-US') + ' ' + cfCur;
            if (document.getElementById('cf-summary-out')) document.getElementById('cf-summary-out').innerText = totalPaymentsInScope.toLocaleString('en-US') + ' ' + cfCur;
            if (document.getElementById('cf-summary-net')) {
                const netEl = document.getElementById('cf-summary-net');
                netEl.innerText = (netCashflow >= 0 ? '+' : '') + netCashflow.toLocaleString('en-US') + ' ' + cfCur;
                netEl.style.color = netCashflow >= 0 ? '#10b981' : '#f87171';
            }

            window._dashboardChartInstances = window._dashboardChartInstances || {};
            function createOrUpdateChart(id, config) {
                if (window._dashboardChartInstances[id]) {
                    try { window._dashboardChartInstances[id].destroy(); } catch(e) {}
                }
                const canvas = document.getElementById(id);
                if (canvas) {
                    window._dashboardChartInstances[id] = new Chart(canvas.getContext('2d'), config);
                }
            }

            createOrUpdateChart('cashflowChart', {
                type: 'line',
                data: {
                    labels: daysLabels,
                    datasets: [
                        { label: 'المقبوضات', data: receiptsData, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', fill: true, borderWidth: 2, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#10b981' },
                        { label: 'المصروفات', data: paymentsData, borderColor: '#f87171', backgroundColor: 'rgba(248,113,113,0.15)', fill: true, borderWidth: 2, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#f87171' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } } }, scales: { x: { grid: { color: gridColor } }, y: { grid: { color: gridColor } } } }
            });

            // 2. Sales by Category (Dynamic from Sales Invoices)
            const catSales = {};
            data.invoices.forEach(inv => {
                if(inv.items && Array.isArray(inv.items)) {
                    inv.items.forEach(item => {
                        const itemDef = data.items.find(i => i.id === item.itemId);
                        let c = (itemDef ? itemDef.category : '') || 'أخرى';
                        let quantity = Number(item.quantity) || Number(item.qty) || 0;
                        let price = Number(item.price) || 0;
                        let total = quantity * price;
                        catSales[c] = (catSales[c] || 0) + total;
                    });
                }
            });
            const catLabels = Object.keys(catSales).length > 0 ? Object.keys(catSales) : ['بدون مبيعات'];
            const catData = Object.keys(catSales).length > 0 ? Object.values(catSales) : [1];

            createOrUpdateChart('categoryChart', {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{ data: catData, backgroundColor: ['#3b82f6', '#f59e0b', '#f87171', '#10b981', '#c084fc', '#ec4899'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' SDG'; } } } } }
            });

            // 3. Inventory Radar (Dynamic - Lowest Stock Items)
            function getItemActualStock(itemId) {
                return inventory
                    .filter(inv => inv.itemId === itemId && inv.warehouse !== 'PENDING_RECEIPT')
                    .reduce((sum, inv) => sum + (Number(inv.qty) || 0), 0);
            }
            
            let radarItemsList = [...data.items].map(i => {
                let avail = getItemActualStock(i.id);
                let safe = Number(i.safeLimit) || Number(i.minQty) || 10;
                let ratio = safe > 0 ? avail / safe : 1;
                return { ...i, avail, safe, ratio };
            });
            radarItemsList.sort((a, b) => a.ratio - b.ratio);
            const radarItems = radarItemsList.slice(0, 5);
            
            const radarLabels = radarItems.length > 0 ? radarItems.map(i => i.name) : ['لا يوجد'];
            const radarAvail = radarItems.length > 0 ? radarItems.map(i => i.avail) : [0];
            const radarSafe = radarItems.length > 0 ? radarItems.map(i => i.safe) : [0];

            createOrUpdateChart('inventoryChart', {
                type: 'radar',
                data: {
                    labels: radarLabels,
                    datasets: [
                        { label: 'المتوفر', data: radarAvail, backgroundColor: 'rgba(248,113,113,0.2)', borderColor: '#f87171', pointBackgroundColor: '#f87171', borderWidth: 1.5 },
                        { label: 'حد الأمان', data: radarSafe, backgroundColor: 'rgba(59,130,246,0.2)', borderColor: '#3b82f6', pointBackgroundColor: '#3b82f6', borderWidth: 1.5 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } } }, scales: { r: { grid: { color: gridColor }, angleLines: { color: gridColor }, ticks: { display: false } } } }
            });

            // 4. Warehouses Capacity (Actual Item Stock Quantities in Each Warehouse)
            const whTotals = {};
            let warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehouses.length === 0) {
                warehouses = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }
            const activeWarehouses = warehouses.filter(w => !w.deleted);
            activeWarehouses.forEach(w => {
                whTotals[w.name || w.id] = 0;
            });
            inventory.forEach(inv => {
                if (inv.warehouse !== 'PENDING_RECEIPT') {
                    const w = warehouses.find(wh => wh.id === inv.warehouse || wh.name === inv.warehouse);
                    if (w && !w.deleted) {
                        const name = w.name || w.id;
                        whTotals[name] = (whTotals[name] || 0) + (Number(inv.qty) || Number(inv.quantity) || 0);
                    } else if (!w) {
                        const name = inv.warehouse;
                        whTotals[name] = (whTotals[name] || 0) + (Number(inv.qty) || Number(inv.quantity) || 0);
                    }
                }
            });

            const whLabels = Object.keys(whTotals).length > 0 ? Object.keys(whTotals) : ['لا توجد مخازن'];
            const whData = Object.keys(whTotals).length > 0 ? Object.values(whTotals) : [0];

            createOrUpdateChart('warehousesChart', {
                type: 'doughnut',
                data: {
                    labels: whLabels,
                    datasets: [{ data: whData, backgroundColor: ['#f87171', '#3b82f6', '#10b981', '#f59e0b', '#c084fc'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' وحدة'; } } } } }
            });

            // 5. Customer & Supplier Credit & Debt Analysis (Dual Bar Chart)
            let allCreditEntities = [];
            try {
                const cList = (JSON.parse(localStorage.getItem('erp_customers_v1')) || data.customers || []).filter(x => !x.deleted);
                const sList = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
                
                const cMapped = cList.map(c => ({
                    name: c.name || 'عميل',
                    typeLabel: '👤 عميل',
                    creditLimit: parseFloat(c.creditLimit) || 0,
                    debt: Math.abs(parseFloat(c.currentBalance) || 0)
                }));

                const sMapped = sList.map(s => ({
                    name: s.name || 'مورد',
                    typeLabel: '🏭 مورد',
                    creditLimit: parseFloat(s.creditLimit) || 0,
                    debt: Math.abs(parseFloat(s.currentBalance) || 0)
                }));

                allCreditEntities = [...cMapped, ...sMapped];
            } catch(e) {
                allCreditEntities = (data.customers || []).map(c => ({
                    name: c.name || 'عميل',
                    typeLabel: '👤 عميل',
                    creditLimit: parseFloat(c.creditLimit) || 0,
                    debt: Math.abs(parseFloat(c.currentBalance) || 0)
                }));
            }

            let validEntities = allCreditEntities.filter(c => c.creditLimit > 0 || c.debt > 0);

            const topEntities = validEntities.slice(0, 15);
            const creditLabels = topEntities.map(c => `${c.name} (${c.typeLabel})`);
            const creditValues = topEntities.map(c => c.creditLimit);
            const debtValues = topEntities.map(c => c.debt);

            const canvasCust = document.getElementById('customersChart');
            if (canvasCust) {
                const ctxCust = canvasCust.getContext('2d');
                
                const skyGrad = ctxCust.createLinearGradient(0, 0, 0, 320);
                skyGrad.addColorStop(0, '#38bdf8');
                skyGrad.addColorStop(1, '#0284c7');

                const roseGrad = ctxCust.createLinearGradient(0, 0, 0, 320);
                roseGrad.addColorStop(0, '#fb7185');
                roseGrad.addColorStop(1, '#e11d48');

                createOrUpdateChart('customersChart', {
                    type: 'bar',
                    data: {
                        labels: creditLabels,
                        datasets: [
                            {
                                label: '💳 سقف الائتمان (المسموح)',
                                data: creditValues,
                                backgroundColor: skyGrad,
                                borderColor: '#38bdf8',
                                borderWidth: 1,
                                borderRadius: { topLeft: 6, topRight: 6 },
                                barPercentage: 0.45,
                                categoryPercentage: 0.7
                            },
                            {
                                label: '⚠️ المديونية القائمة (المسحوب)',
                                data: debtValues,
                                backgroundColor: roseGrad,
                                borderColor: '#fb7185',
                                borderWidth: 1,
                                borderRadius: { topLeft: 6, topRight: 6 },
                                barPercentage: 0.45,
                                categoryPercentage: 0.7
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'center',
                                labels: {
                                    color: '#e2e8f0',
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { family: "'Tajawal', sans-serif", size: 12, weight: '800' }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                titleColor: '#fff',
                                bodyColor: '#38bdf8',
                                borderColor: 'rgba(56, 189, 248, 0.3)',
                                borderWidth: 1,
                                padding: 12,
                                callbacks: {
                                    title: function(items) {
                                        const entity = topEntities[items[0].dataIndex];
                                        return entity.name + ' (' + entity.typeLabel + ')';
                                    },
                                    label: function(context) {
                                        const entity = topEntities[context.dataIndex];
                                        const val = Number(context.raw).toLocaleString('en-US') + ' SDG';
                                        if (context.datasetIndex === 0) {
                                            return ' 💳 سقف الائتمان: ' + val;
                                        } else {
                                            const rem = Math.max(0, entity.creditLimit - entity.debt);
                                            const usage = entity.creditLimit > 0 ? Math.round((entity.debt / entity.creditLimit) * 100) : 0;
                                            return [
                                                ' ⚠️ المديونية القائمة: ' + val + ' (' + usage + '% من السقف)',
                                                ' 🟢 المتبقي المتاح: ' + rem.toLocaleString('en-US') + ' SDG'
                                            ];
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: '#94a3b8',
                                    maxRotation: 35,
                                    minRotation: 20,
                                    font: { family: "'Tajawal', sans-serif", size: 11, weight: '700' }
                                }
                            },
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { family: "'JetBrains Mono', monospace", size: 11, weight: '700' },
                                    callback: function(val) {
                                        return val.toLocaleString('en-US');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
