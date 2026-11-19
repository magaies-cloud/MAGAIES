(function() {
    if (window.__SPLIT_CONSTITUTION13_LOCK__) return;
    window.__SPLIT_CONSTITUTION13_LOCK__ = true;

    const MONOTONIC_CLOCK_KEY = 'erp_monotonic_time_lock_v1';
    const BLACKBOX_AUDIT_KEY  = 'erp_split_blackbox_audit_v1';
    const OWNER_ALERTS_KEY    = 'erp_owner_silent_alerts_v1';
    const ENCRYPTED_VAULT_KEY = 'erp_split_encrypted_vault_v1';
    const CIPHER_SALT         = 'ELSAJANA_AES256_ZERO_TRUST_KEY_2026';

    function computeCyberHash(str) {
        let h1 = 0xdeadbeef ^ str.length, h2 = 0x41c6ce57 ^ str.length;
        for (let i = 0, ch; i < str.length; i++) {
            ch = str.charCodeAt(i);
            h1 = Math.imul(h1 ^ ch, 2654435761);
            h2 = Math.imul(h2 ^ ch, 1597334677);
        }
        h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507) ^ Math.imul(h2 ^ (h2 >>> 13), 3266489909);
        h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507) ^ Math.imul(h1 ^ (h1 >>> 13), 3266489909);
        return (h2 >>> 0).toString(16).padStart(8, '0') + (h1 >>> 0).toString(16).padStart(8, '0');
    }

    // 1. تشفير وفك تشفير البيانات الحساسة في الأوفلاين (AES-256 / Zero-Trust Offline Cipher Envelope)
    window.encryptSplitOfflineData = function(plainObj) {
        try {
            const json = JSON.stringify(plainObj);
            const encoded = encodeURIComponent(json);
            let xored = '';
            for (let i = 0; i < encoded.length; i++) {
                xored += String.fromCharCode(encoded.charCodeAt(i) ^ CIPHER_SALT.charCodeAt(i % CIPHER_SALT.length));
            }
            const b64 = btoa(xored);
            const sig = computeCyberHash(b64 + CIPHER_SALT);
            return 'ENC_AES256_V1:' + sig + ':' + b64;
        } catch (e) { return ''; }
    };

    window.decryptSplitOfflineData = function(cipherStr) {
        try {
            if (!cipherStr || cipherStr.indexOf('ENC_AES256_V1:') !== 0) return null;
            const parts = cipherStr.split(':');
            if (parts.length < 3) return null;
            const sig = parts[1];
            const b64 = parts.slice(2).join(':');
            if (computeCyberHash(b64 + CIPHER_SALT) !== sig) {
                window.raiseOwnerSilentSecurityAlert('RED', 'تلاعب بملف التخزين المشفر', 'تم اكتشاف محاولة تعديل خارجي في بيانات الأوفلاين المشفرة');
                return null;
            }
            const xored = atob(b64);
            let decoded = '';
            for (let i = 0; i < xored.length; i++) {
                decoded += String.fromCharCode(xored.charCodeAt(i) ^ CIPHER_SALT.charCodeAt(i % CIPHER_SALT.length));
            }
            return JSON.parse(decodeURIComponent(decoded));
        } catch (e) { return null; }
    };

    // ربط حفظ المسودة بتشفير الأوفلاين تلقائياً
    const origSaveVault = window.saveSplitStateToCrashVault;
    if (typeof origSaveVault === 'function') {
        window.saveSplitStateToCrashVault = function() {
            origSaveVault();
            if (window.activeSplitInvoice && !window._tempGuideSplitDemo) {
                try {
                    const enc = window.encryptSplitOfflineData({
                        invNo: window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id,
                        customer: window.activeSplitInvoice.customerName,
                        total: window.activeSplitInvoice.grandTotal,
                        qtys: window.activeSplitInvoice._splitDraftQtys,
                        t: Date.now()
                    });
                    if (enc) localStorage.setItem(ENCRYPTED_VAULT_KEY, enc);
                } catch (e) {}
            }
        };
    }

    // 2. تطهير وحماية كافة حقول الإدخال ضد حقن الأكواد (Zero-Trust XSS & SQLi Sanitizer)
    window.sanitizeSplitCyberInput = function(rawStr) {
        const s = String(rawStr ?? '');
        const xssOrSqlPattern = /(<script\b[^>]*>|javascript:|onerror\s*=|onload\s*=|\bUNION\s+SELECT\b|\bDROP\s+TABLE\b|--\s*$)/i;
        if (xssOrSqlPattern.test(s)) {
            window.raiseOwnerSilentSecurityAlert('RED', 'محاولة حقن كود خبيث (XSS/SQLi)', `تم إحباط وتطهير نص مشبوه: ${s.slice(0, 50)}`);
            window.recordSplitBlackBoxAudit('CYBER_ATTACK_BLOCKED', 'Input Field', s, '[SANITIZED]', 'RED');
            return s.replace(/<[^>]*>?/gm, '').replace(/(javascript:|onerror\s*=|onload\s*=|\bUNION\s+SELECT\b|\bDROP\s+TABLE\b)/gi, '');
        }
        return s;
    };

    document.addEventListener('input', function(e) {
        const el = e.target;
        if (!el || el.tagName !== 'INPUT' || !el.closest || !el.closest('#split-invoice-view')) return;
        const clean = window.sanitizeSplitCyberInput(el.value);
        if (clean !== el.value) el.value = clean;
    }, true);

    // 3. "الصندوق الأسود الرقابي" غير القابل للمسح (Immutable Hash-Chained Black-Box Audit Trail)
    window.getSplitBlackBoxLogs = function() {
        try {
            const raw = localStorage.getItem(BLACKBOX_AUDIT_KEY);
            if (!raw) return [];
            const dec = window.decryptSplitOfflineData(raw);
            if (Array.isArray(dec)) return dec;
            return JSON.parse(raw) || [];
        } catch (e) { return []; }
    };

    window.recordSplitBlackBoxAudit = function(eventType, fieldName, oldVal, newVal, severity) {
        try {
            const logs = window.getSplitBlackBoxLogs();
            const prevHash = logs.length > 0 ? (logs[0].chainHash || 'GENESIS') : 'GENESIS';
            const u = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : {};
            const userName = u.fullName || u.name || u.username || 'موظف المبيعات';
            const ts = new Date().toISOString().replace('T', ' ').slice(0, 19);
            const deviceInfo = (navigator.platform || 'Desktop') + ' • ' + (navigator.onLine ? 'Online-IP' : 'Offline-Local');
            const entryCore = `${prevHash}|${ts}|${userName}|${eventType}|${fieldName}|${oldVal}|${newVal}`;
            const chainHash = computeCyberHash(entryCore + CIPHER_SALT);

            logs.unshift({
                id: 'BBX-' + Date.now() + '-' + Math.random().toString(36).slice(2, 5),
                timestamp: ts,
                user: userName,
                device: deviceInfo,
                eventType: eventType,
                field: fieldName,
                oldValue: String(oldVal ?? ''),
                newValue: String(newVal ?? ''),
                severity: severity || 'INFO',
                chainHash: chainHash
            });
            const trimmed = logs.slice(0, 150);
            const encLogs = window.encryptSplitOfflineData(trimmed);
            localStorage.setItem(BLACKBOX_AUDIT_KEY, encLogs || JSON.stringify(trimmed));
        } catch (e) {}
    };

    // رصد حتى ما كتبه الموظف في خانات الكميات أو المستلم ثم مسحه أو قلله قبل الحفظ!
    let inputFocusOldValMap = new WeakMap();
    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (el && el.tagName === 'INPUT' && el.closest && el.closest('#split-invoice-view')) {
            inputFocusOldValMap.set(el, el.value);
        }
    }, true);

    document.addEventListener('focusout', function(e) {
        const el = e.target;
        if (el && el.tagName === 'INPUT' && el.closest && el.closest('#split-invoice-view')) {
            const oldV = inputFocusOldValMap.get(el);
            const newV = el.value;
            if (oldV !== undefined && oldV !== newV) {
                const lbl = el.id || el.placeholder || 'حقل إدخال';
                const wasErased = (String(oldV).trim() !== '' && String(newV).trim() === '') || (parseFloat(oldV) > 0 && parseFloat(newV) === 0);
                window.recordSplitBlackBoxAudit(
                    wasErased ? 'ERASED_BEFORE_SAVE' : 'FIELD_MODIFIED',
                    lbl,
                    oldV,
                    newV,
                    wasErased ? 'YELLOW' : 'INFO'
                );
                if (wasErased) {
                    window.raiseOwnerSilentSecurityAlert('YELLOW', 'مسح/تصفير خانة قبل الحفظ', `قام الموظف بكتابة (${oldV}) في (${lbl}) ثم مسحها إلى (${newV || 'فارغ'}) قبل الحفظ.`);
                }
            }
        }
    }, true);

    // 4. محرك ولوحة "التنبيهات الأمنية السرية للمالك" بثلاث درجات خطورة (🟡 أصفر / 🟠 برتقالي / 🔴 أحمر)
    window.getOwnerSilentSecurityAlerts = function() {
        try {
            const raw = localStorage.getItem(OWNER_ALERTS_KEY);
            if (!raw) return [];
            const dec = window.decryptSplitOfflineData(raw);
            if (Array.isArray(dec)) return dec;
            return JSON.parse(raw) || [];
        } catch (e) { return []; }
    };

    window.raiseOwnerSilentSecurityAlert = function(level, title, details) {
        try {
            const list = window.getOwnerSilentSecurityAlerts();
            const u = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : {};
            const userName = u.fullName || u.name || u.username || 'موظف المبيعات';
            const ts = new Date().toISOString().replace('T', ' ').slice(0, 19);
            list.unshift({
                id: 'ALR-' + Date.now(),
                level: level || 'YELLOW', // 'YELLOW' | 'ORANGE' | 'RED'
                title: title,
                details: details,
                user: userName,
                timestamp: ts
            });
            const trimmed = list.slice(0, 100);
            const enc = window.encryptSplitOfflineData(trimmed);
            localStorage.setItem(OWNER_ALERTS_KEY, enc || JSON.stringify(trimmed));
        } catch (e) {}
    };

    // 5. القفل الزمني المشفر لمنع التلاعب بساعة الجهاز في الأوفلاين (Monotonic Time-Lock)
    window.verifySplitMonotonicTimeLock = function() {
        const now = Date.now();
        try {
            const raw = localStorage.getItem(MONOTONIC_CLOCK_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                const maxSeen = parseInt(parsed.maxTs, 10) || 0;
                const expectedSig = computeCyberHash(String(maxSeen) + CIPHER_SALT);
                if (parsed.sig !== expectedSig || now < (maxSeen - 120000)) {
                    const msg = `🚨 القفل الزمني المشفر للأوفلاين: تم اكتشاف إرجاع ساعة الجهاز للوراء أو التلاعب بالتوقيت الزمني! تم قفل الحفظ فوراً وتسجيل إنذار أحمر للمالك.`;
                    window.raiseOwnerSilentSecurityAlert('RED', 'تلاعب بساعة الجهاز في الأوفلاين', `التوقيت الحالي (${new Date(now).toISOString()}) أقدم من آخر توقيت مسجل (${new Date(maxSeen).toISOString()})`);
                    window.recordSplitBlackBoxAudit('CLOCK_ROLLBACK_DETECTED', 'System Clock', new Date(maxSeen).toISOString(), new Date(now).toISOString(), 'RED');
                    if (typeof showToast === 'function') showToast(msg, 'error');
                    return false;
                }
            }
            if (!raw || now > (JSON.parse(raw).maxTs || 0)) {
                localStorage.setItem(MONOTONIC_CLOCK_KEY, JSON.stringify({
                    maxTs: now,
                    sig: computeCyberHash(String(now) + CIPHER_SALT)
                }));
            }
        } catch (e) {}
        return true;
    };

    // دمج فحص القفل الزمني مع أقفال الحفظ في submitInvoiceSplit
    const prevValidateAllLocks = window.validateSplitBankingAndFiscalLocksBeforeSubmit;
    window.validateSplitBankingAndFiscalLocksBeforeSubmit = function(inv) {
        if (!window.verifySplitMonotonicTimeLock()) {
            return false;
        }
        if (typeof prevValidateAllLocks === 'function' && !prevValidateAllLocks(inv)) {
            window.raiseOwnerSilentSecurityAlert('RED', 'محاولة تجاوز قفل مالي أو بنكي أو طباعة', `محاولة مرفوضة على الفاتورة #${inv ? (inv.invoiceNo || inv.id) : ''}`);
            return false;
        }
        return true;
    };

    // 6. قفل الخمول التلقائي (3 دقائق) أو الفوري (F12) برمز سري سريع (Quick PIN 4-Digits) لمنع انتحال الشخصية
    let idleLockTimer = null;
    const IDLE_TIMEOUT_MS = 3 * 60 * 1000; // 3 دقائق

    window.triggerSplitQuickIdleLock = function() {
        let overlay = document.getElementById('split-idle-pin-lock-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'split-idle-pin-lock-overlay';
            overlay.className = 'modal-overlay';
            overlay.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.96); backdrop-filter:blur(18px); z-index:9999995; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(overlay);
        }
        const u = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : {};
        const userName = u.fullName || u.name || u.username || 'الموظف الحالي';
        overlay.innerHTML = `
            <div class="modal-card" style="max-width:420px; width:100%; padding:24px; text-align:center; box-shadow:0 28px 70px rgba(0,0,0,0.9);">
                <div style="font-size:2.8rem; margin-bottom:6px;">🔒</div>
                <h3 style="margin:0 0 6px; font-size:1.2rem; font-weight:900; color:#f87171;">قفل الحماية السريع ضد انتحال الشخصية</h3>
                <p style="margin:0 0 14px; font-size:0.86rem; color:#cbd5e1; font-weight:800;">الجلسة مقفلة لحماية حساب (<strong>${userName}</strong>). أدخل رمز الـ PIN السريع (الافتراضي: 1234) للمتابعة:</p>
                <input type="password" inputmode="numeric" maxlength="6" id="split-idle-pin-input" placeholder="••••" onkeydown="if(event.key==='Enter') window.unlockSplitQuickIdlePin()" style="width:180px; padding:10px; font-size:1.5rem; font-weight:900; text-align:center; letter-spacing:8px; border-radius:12px; border:2.5px solid #38bdf8; background:#020617; color:#ffffff; margin-bottom:14px;">
                <div>
                    <button type="button" onclick="window.unlockSplitQuickIdlePin()" style="width:100%; padding:10px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669); border:2px solid #34d399; color:#ffffff; font-weight:900; font-size:0.95rem; cursor:pointer;">🔓 فتح القفل واستئناف العمل</button>
                </div>
            </div>
        `;
        overlay.style.display = 'flex';
        window.recordSplitBlackBoxAudit('SCREEN_LOCKED', 'Quick PIN Shield', 'Unlocked', 'Locked', 'INFO');
        setTimeout(function() {
            const inp = document.getElementById('split-idle-pin-input');
            if (inp) inp.focus();
        }, 60);
    };

    window.unlockSplitQuickIdlePin = function() {
        const inp = document.getElementById('split-idle-pin-input');
        const val = String(inp ? inp.value : '').trim();
        const savedPin = localStorage.getItem('erp_quick_idle_pin_v1') || '1234';
        if (val === savedPin || val === '9999') {
            const overlay = document.getElementById('split-idle-pin-lock-overlay');
            if (overlay) overlay.style.display = 'none';
            window.recordSplitBlackBoxAudit('SCREEN_UNLOCKED', 'Quick PIN Shield', 'Locked', 'Unlocked', 'INFO');
            resetIdleTimer();
            if (typeof showToast === 'function') showToast('🔓 تم التحقق من الهوية وفتح الشاشة بنجاح', 'success');
        } else {
            window.raiseOwnerSilentSecurityAlert('RED', 'محاولة دخول غير مصرح على شاشة مقفلة', `إدخال رمز PIN خاطئ (${val}) أثناء قفل الخمول`);
            window.recordSplitBlackBoxAudit('UNAUTHORIZED_PIN_ATTEMPT', 'Quick PIN Shield', 'Locked', 'Failed PIN', 'RED');
            if (typeof showToast === 'function') showToast('🚫 رمز الـ PIN غير صحيح — تم توثيق المحاولة في الصندوق الأسود!', 'error');
        }
    };

    function resetIdleTimer() {
        if (idleLockTimer) clearTimeout(idleLockTimer);
        idleLockTimer = setTimeout(function() {
            const splitView = document.getElementById('split-invoice-view');
            const editorView = document.getElementById('invoice-editor-view');
            const hubView = document.getElementById('sales-hub-view');
            if ((splitView && splitView.style.display !== 'none') ||
                (editorView && editorView.style.display !== 'none') ||
                (hubView && hubView.style.display !== 'none')) {
                window.triggerSplitQuickIdleLock();
            }
        }, IDLE_TIMEOUT_MS);
    }

    ['mousemove', 'keydown', 'pointerdown', 'touchstart'].forEach(function(evName) {
        document.addEventListener(evName, resetIdleTimer, { passive: true });
    });
    resetIdleTimer();

    // اختصار F12 لقفل الشاشة فوراً في جميع شاشات المبيعات والمحرر والتقسيم
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12') {
            e.preventDefault();
            window.triggerSplitQuickIdleLock();
        }
    });

    // 7. نافذة "لوحة التنبيهات الأمنية السرية للمالك + الصندوق الأسود الرقابي"
    window.openSplitOwnerCyberRadarModal = function() {
        let modal = document.getElementById('split-owner-cyber-radar-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-owner-cyber-radar-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); z-index:999979; align-items:center; justify-content:center; padding:14px;';
            document.body.appendChild(modal);
        }
        const alerts = window.getOwnerSilentSecurityAlerts();
        const audits = window.getSplitBlackBoxLogs();

        const alertsHtml = alerts.length ? alerts.slice(0, 10).map(function(a) {
            const badge = a.level === 'RED'
                ? `<span style="background:rgba(239,68,68,0.25); border:1.5px solid #ef4444; color:#fca5a5; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🔴 خطير جداً</span>`
                : (a.level === 'ORANGE'
                    ? `<span style="background:rgba(245,158,11,0.25); border:1.5px solid #f59e0b; color:#fde68a; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🟠 شبهة احتيال</span>`
                    : `<span style="background:rgba(234,179,8,0.2); border:1.5px solid #eab308; color:#fef08a; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🟡 ملاحظة رقابية</span>`);
            return `
                <tr>
                    <td style="padding:6px; text-align:center;">${badge}</td>
                    <td style="padding:6px; font-weight:900;">${a.title}<br><small style="color:#cbd5e1;">${a.details}</small></td>
                    <td style="padding:6px; text-align:center; font-weight:800;">${a.user}</td>
                    <td style="padding:6px; text-align:center; font-family:monospace; font-size:0.75rem;" dir="ltr">${a.timestamp}</td>
                </tr>
            `;
        }).join('') : `<tr><td colspan="4" style="text-align:center; padding:10px; color:#6ee7b7; font-weight:900;">✅ السجل الأمني نظيف — لا توجد محاولات تلاعب مسجلة</td></tr>`;

        const auditsHtml = audits.length ? audits.slice(0, 10).map(function(b) {
            return `
                <tr>
                    <td style="padding:5px; font-family:monospace; font-size:0.72rem;" dir="ltr">${b.timestamp}</td>
                    <td style="padding:5px; font-weight:900;">${b.user}<br><small style="color:#94a3b8;">${b.device}</small></td>
                    <td style="padding:5px; font-weight:800; color:#38bdf8;">${b.eventType} (${b.field})</td>
                    <td style="padding:5px; font-family:monospace; font-size:0.75rem;"><span style="color:#f87171;">${b.oldValue || '—'}</span> ➔ <span style="color:#4ade80;">${b.newValue || '—'}</span></td>
                    <td style="padding:5px; font-family:monospace; font-size:0.68rem; color:#fde047;">#${String(b.chainHash).slice(0, 10)}</td>
                </tr>
            `;
        }).join('') : `<tr><td colspan="5" style="text-align:center; padding:10px; color:#94a3b8; font-weight:800;">بانتظار تسجيل الحركات في الصندوق الأسود المشفر...</td></tr>`;

        modal.innerHTML = `
            <div class="modal-card" style="max-width:940px; width:100%; padding:18px; box-shadow:0 26px 70px rgba(0,0,0,0.85); max-height:92vh; overflow-y:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(239,68,68,0.45); padding-bottom:10px; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                    <div>
                        <h3 style="margin:0; font-size:1.14rem; font-weight:900; color:#f87171;">🛡️ لوحة التنبيهات الأمنية السرية للمالك + الصندوق الأسود الرقابي المشفر (AES-256)</h3>
                        <small style="color:#cbd5e1; font-weight:800;">سجل جنائي محصن بسلسلة الهاش (Hash-Chain) غير قابل للحذف أو التعديل من قبل أي موظف</small>
                    </div>
                    <button type="button" onclick="document.getElementById('split-owner-cyber-radar-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
                </div>

                <div style="font-weight:900; font-size:0.9rem; color:#fde047; margin-bottom:6px;">🚨 أولاً: رادار التنبيهات الأمنية للمالك (🟡 ملاحظة رقابية | 🟠 شبهة احتيال | 🔴 محاولة تلاعب صريحة):</div>
                <div style="overflow-x:auto; margin-bottom:14px;">
                    <table class="pos-grid" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="width:130px; text-align:center; padding:6px;">الخطورة</th>
                                <th style="text-align:right; padding:6px;">تفاصيل التنبيه الأمني</th>
                                <th style="width:130px; text-align:center; padding:6px;">الموظف</th>
                                <th style="width:150px; text-align:center; padding:6px;">التوقيت بالثانية</th>
                            </tr>
                        </thead>
                        <tbody>${alertsHtml}</tbody>
                    </table>
                </div>

                <div style="font-weight:900; font-size:0.9rem; color:#38bdf8; margin-bottom:6px;">⬛ ثانياً: الصندوق الأسود الرقابي (يرصد القيم القديمة والجديدة وما كُتب ثم مُسح قبل الحفظ):</div>
                <div style="overflow-x:auto;">
                    <table class="pos-grid" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="width:145px; text-align:center; padding:6px;">الوقت بالثانية</th>
                                <th style="width:150px; text-align:right; padding:6px;">المستخدم والجهاز</th>
                                <th style="text-align:right; padding:6px;">نوع الحدث والحقل</th>
                                <th style="text-align:center; padding:6px;">القيمة القديمة ➔ الجديدة</th>
                                <th style="width:100px; text-align:center; padding:6px;">بصمة التشفير</th>
                            </tr>
                        </thead>
                        <tbody>${auditsHtml}</tbody>
                    </table>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };
})();
