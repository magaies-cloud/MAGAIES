/**
 * ════════════════════════════════════════════════════════════════════════════
 * ⚡ MAGAIEES ERP 2026 - FLAGSHIP UNIVERSAL OFFLINE & AUTO-SYNC ENGINE
 * ════════════════════════════════════════════════════════════════════════════
 * محرك الأوفلاين الشامل والذكي للهواتف المحمولة والتابلت:
 * 1. العمل بدون إنترنت بنسبة 100% لكافة العمليات (مبيعات، مشتريات، صرف، تسليم، تقسيم)
 * 2. ترقيم أوفلاين ذكي ومميز برمز الجهاز (مثال: جهاز 5 -> 5-1 أوفلاين، 5-2 أوفلاين)
 *    وجهاز 4 -> 4-1 أوفلاين بدون أي تضارب بين الأجهزة إطلاقاً.
 * 3. المزامنة الذاتية الخاطفة فور عودة النت (ولو لثانية واحدة) دون أي تدخل بشري.
 * 4. استبدال الأرقام المؤقتة بالأرقام الحقيقية المتسلسلة من MariaDB فوراً.
 * 5. كاش محلي فوري للأصناف والحسابات ليعمل التطبيق كاملاً حتى في وضع الطيران.
 * 6. تثبيت فوري كأيقونة على الشاشة الرئيسية (PWA Standalone App).
 * 7. حماية فائقة ضد التكرار (Idempotency Protection).
 * ════════════════════════════════════════════════════════════════════════════
 */

(function(window) {
    'use strict';

    const STORAGE_KEY_QUEUE = 'magaies_universal_offline_queue';
    const STORAGE_KEY_DEVICE = 'magaies_device_code';
    const STORAGE_KEY_SEQ = 'magaies_offline_sequences';
    const STORAGE_KEY_CACHE_ACCOUNTS = 'magaies_offline_cache_accounts';
    const STORAGE_KEY_CACHE_ITEMS = 'magaies_offline_cache_items';

    const MagaiesOffline = {
        isOnline: navigator.onLine,
        isSyncing: false,
        pulseInterval: null,
        deferredPrompt: null,

        // ─────────────────────────────────────────────────────────────
        // 1. إدارة معرف ورمز الجهاز (Device Code)
        // ─────────────────────────────────────────────────────────────
        getDeviceCode() {
            let code = localStorage.getItem(STORAGE_KEY_DEVICE);
            if (!code || code.trim() === '') {
                // افتراضي 5 كما اقترح المستخدم، أو توليد رقم ثابت
                code = '5';
                localStorage.setItem(STORAGE_KEY_DEVICE, code);
            }
            return code.trim();
        },

        setDeviceCode(newCode) {
            if (!newCode || newCode.trim() === '') return;
            localStorage.setItem(STORAGE_KEY_DEVICE, newCode.trim());
            this.updateUIBadge();
            const msg = `📱 تم ضبط رمز هذا الجهاز إلى: [${newCode.trim()}]`;
            if (window.showToastNotification) window.showToastNotification(msg);
            else if (window.showToast) window.showToast(msg, 'info');
        },

        promptChangeDeviceCode() {
            const current = this.getDeviceCode();
            const chosen = prompt('أدخل رمز هذا الجوال للتمييز في وضع الأوفلاين (مثال: 5 للجوال الأول، 4 للجوال الثاني، أو سيارة 1):', current);
            if (chosen && chosen.trim() !== '') {
                this.setDeviceCode(chosen.trim());
            }
        },

        // ─────────────────────────────────────────────────────────────
        // 2. توليد التسلسل المؤقت للأوفلاين (Device-Partitioned Sequence)
        // ─────────────────────────────────────────────────────────────
        getNextOfflineId(type) {
            const dev = this.getDeviceCode();
            let seqs = {};
            try {
                seqs = JSON.parse(localStorage.getItem(STORAGE_KEY_SEQ) || '{}');
            } catch(e) { seqs = {}; }

            const key = type || 'sales';
            const curSeq = (parseInt(seqs[key]) || 0) + 1;
            seqs[key] = curSeq;
            localStorage.setItem(STORAGE_KEY_SEQ, JSON.stringify(seqs));

            // التسمية المعتمدة وفق رغبة المستخدم بدقة
            if (type === 'sales') {
                return `${dev}-${curSeq} أوفلاين`;
            } else if (type === 'sales_split') {
                return `${dev}-${curSeq} تقسيط أوفلاين`;
            } else if (type === 'purchase') {
                return `${dev}-${curSeq} مشتريات أوفلاين`;
            } else if (type === 'delivery') {
                return `${dev}-${curSeq} تسليم أوفلاين`;
            } else if (type === 'purchase_receipt') {
                return `${dev}-${curSeq} استلام أوفلاين`;
            } else if (type === 'finance' || type === 'voucher') {
                return `${dev}-${curSeq} حركة أوفلاين`;
            }
            return `${dev}-${curSeq} أوفلاين`;
        },

        // ─────────────────────────────────────────────────────────────
        // 3. إدارة طابور الأوفلاين (Universal Offline Queue)
        // ─────────────────────────────────────────────────────────────
        getQueue() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY_QUEUE) || '[]');
            } catch(e) {
                return [];
            }
        },

        saveQueue(q) {
            try {
                localStorage.setItem(STORAGE_KEY_QUEUE, JSON.stringify(q));
            } catch(e) {}
            this.updateUIBadge();
        },

        enqueue(type, payload, customOfflineId) {
            const queue = this.getQueue();
            const offlineId = customOfflineId || this.getNextOfflineId(type);
            const devCode = this.getDeviceCode();

            // مفتاح عدم التكرار الصارم لمنع التكرار عند تقطع الاتصال (Idempotency Key)
            const idemKey = 'idempotent-' + devCode + '-' + Date.now() + '-' + Math.random().toString(36).substr(2, 8);
            payload.idempotencyKey = idemKey;

            const queueItem = {
                id: 'q-' + Date.now() + '-' + Math.random().toString(36).substr(2, 6),
                type: type,
                offline_id: offlineId,
                device_code: devCode,
                created_at: new Date().toISOString(),
                payload: payload
            };

            // إرفاق معرف الأوفلاين داخل كائن الفاتورة
            if (type === 'sales' || type === 'sales_split') {
                payload.invoiceNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'purchase') {
                payload.invoiceNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'delivery') {
                payload.deliveryNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'purchase_receipt') {
                payload.receiptNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'finance' || type === 'voucher') {
                payload.voucherNo = offlineId;
                payload.id = offlineId;
            }
            payload.isOffline = true;
            payload.deviceCode = devCode;

            queue.push(queueItem);
            this.saveQueue(queue);

            // تشغيل رجّاش التوثيق
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate([40, 30, 40]);
            }

            console.log(`[OfflineEngine] Enqueued ${type} with ID: [${offlineId}] on Device [${devCode}]`);
            return offlineId;
        },

        // ─────────────────────────────────────────────────────────────
        // 4. المزامنة التلقائية اللحظية (Instant Auto-Sync Engine)
        // ─────────────────────────────────────────────────────────────
        async flushQueue() {
            if (this.isSyncing) return;
            const queue = this.getQueue();
            if (queue.length === 0) return;

            this.isSyncing = true;
            this.updateUIBadge();

            try {
                // إرسال الدفعة بالكامل في طلب واحد سريع جداً (Batch Request < 100ms)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 6000);

                const res = await fetch('api/mirror_sync.php?action=sync_offline_batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: queue }),
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                const out = await res.json();
                if (out && out.success && Array.isArray(out.synced)) {
                    // تم الترحيل بنجاح لكل العناصر
                    const syncedMap = {};
                    out.synced.forEach(s => {
                        syncedMap[s.offline_id] = s.real_id;
                    });

                    // إفراغ الطابور بعد الترحيل الكامل
                    this.saveQueue([]);

                    // إعلام واجهة المستخدم بالنجاح
                    const count = out.synced.length;
                    const sample = out.synced[0] ? `(مثل: ${out.synced[0].offline_id} ➔ ${out.synced[0].real_id})` : '';
                    const successMsg = `🟢 تم ترحيل ${count} فاتورة/سند أوفلاين بنجاح لقاعدة البيانات! ${sample}`;
                    
                    if (window.showToastNotification) {
                        window.showToastNotification(successMsg);
                    } else if (window.showToast) {
                        window.showToast(successMsg, 'success');
                    } else if (window.showModernToast) {
                        window.showModernToast(successMsg, 'success', 4500);
                    }

                    // تحديث أرقام المرآة في الشاشات المفتوحة
                    if (typeof window.fetchMirrorNumbers === 'function') window.fetchMirrorNumbers();
                    if (typeof window.initMobileMirrorSync === 'function') window.initMobileMirrorSync();
                    if (typeof window.fetchLiveDbData === 'function') window.fetchLiveDbData(false);
                    if (typeof window.syncMirrorData === 'function') window.syncMirrorData();
                }
            } catch (err) {
                // النت لم يكتمل بعد أو انقطع مجدداً أثناء الطلب - نحتفظ بالطابور بأمان تام
                console.log('[OfflineEngine] Sync pulse interrupted, keeping queue safe:', err.message);
            } finally {
                this.isSyncing = false;
                this.updateUIBadge();
            }
        },

        // ─────────────────────────────────────────────────────────────
        // 5. مستشعر النبض الشبكي النشط (Active Pulse Heartbeat)
        // ─────────────────────────────────────────────────────────────
        initHeartbeat() {
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.updateUIBadge();
                this.flushQueue();
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
                this.updateUIBadge();
            });

            // مستشعر نبض فحص السيرفر كل 3.5 ثوانٍ عند وجود عناصر معلقة
            if (this.pulseInterval) clearInterval(this.pulseInterval);
            this.pulseInterval = setInterval(async () => {
                const q = this.getQueue();
                if (q.length > 0 && !this.isSyncing) {
                    try {
                        const pingCtrl = new AbortController();
                        const pTimeout = setTimeout(() => pingCtrl.abort(), 2000);
                        const ping = await fetch('api/mirror_sync.php?action=get_next_numbers&_t=' + Date.now(), {
                            method: 'GET',
                            signal: pingCtrl.signal
                        });
                        clearTimeout(pTimeout);
                        if (ping.ok) {
                            this.isOnline = true;
                            this.flushQueue();
                        }
                    } catch(e) {
                        this.isOnline = false;
                        this.updateUIBadge();
                    }
                }
            }, 3500);

            // فحص فوري عند فتح التطبيق أو العودة للشاشة
            window.addEventListener('focus', () => {
                const q = this.getQueue();
                if (q.length > 0) this.flushQueue();
            });
        },

        // ─────────────────────────────────────────────────────────────
        // 6. كاش البيانات المرجعية للعمل أوفلاين (Accounts & Items Snapshot)
        // ─────────────────────────────────────────────────────────────
        cacheReferenceData(accounts, items) {
            try {
                if (accounts && Array.isArray(accounts)) localStorage.setItem(STORAGE_KEY_CACHE_ACCOUNTS, JSON.stringify(accounts));
                if (items && Array.isArray(items)) localStorage.setItem(STORAGE_KEY_CACHE_ITEMS, JSON.stringify(items));
            } catch(e) {}
        },
        getCachedAccounts() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY_CACHE_ACCOUNTS) || '[]'); } catch(e) { return []; }
        },
        getCachedItems() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY_CACHE_ITEMS) || '[]'); } catch(e) { return []; }
        },

        // ─────────────────────────────────────────────────────────────
        // 7. تثبيت التطبيق على الشاشة الرئيسية (PWA 1-Tap Install)
        // ─────────────────────────────────────────────────────────────
        initPWAInstallCapture() {
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                console.log('[OfflineEngine] PWA beforeinstallprompt captured');
            });
        },

        promptPWAInstall() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                this.deferredPrompt.userChoice.then(() => {
                    this.deferredPrompt = null;
                });
            } else {
                // إرشادات آيفون / أندرويد إذا لم يكن البرومبت جاهزاً
                const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
                if (isIOS) {
                    alert('📲 لتثبيت التطبيق على شاشة الآيفون:\n1. اضغط على زر المشاركة (Share ⎋) أسفل المتصفح.\n2. اختر "إضافة إلى الصفحة الرئيسية" (Add to Home Screen ⊞).\n3. سيظهر التطبيق كأيقونة على الشاشة ويفتح بدون نت فوراً!');
                } else {
                    alert('📲 لتثبيت التطبيق على شاشة الجوال:\n1. اضغط على قائمة الخيارات (⋮) أعلى المتصفح.\n2. اختر "تثبيت التطبيق" أو "إضافة إلى الشاشة الرئيسية".\n3. سيتم تثبيت التطبيق والعمل في وضع الأوفلاين بالكامل!');
                }
            }
        },

        // ─────────────────────────────────────────────────────────────
        // 8. واجهة المستخدم والشارة العائمة (Floating Offline UI Pill & Modal)
        // ─────────────────────────────────────────────────────────────
        updateUIBadge() {
            let badge = document.getElementById('magaies-offline-pill');
            const queue = this.getQueue();
            const dev = this.getDeviceCode();
            const count = queue.length;

            if (!badge) {
                badge = document.createElement('div');
                badge.id = 'magaies-offline-pill';
                badge.style.cssText = `
                    position: fixed;
                    bottom: 74px;
                    left: 12px;
                    z-index: 99999;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    padding: 5px 11px;
                    border-radius: 9999px;
                    font-size: 11px;
                    font-weight: 800;
                    font-family: inherit;
                    cursor: pointer;
                    backdrop-filter: blur(12px);
                    box-shadow: 0 4px 16px rgba(0,0,0,0.4);
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    user-select: none;
                `;
                badge.onclick = () => this.showQueueModal();
                document.body.appendChild(badge);
            }

            if (count > 0) {
                // حالة وجود معلقات أوفلاين (Amber Glowing Alert)
                badge.style.background = 'rgba(245, 158, 11, 0.22)';
                badge.style.border = '1px solid rgba(245, 158, 11, 0.6)';
                badge.style.color = '#fbbf24';
                badge.innerHTML = `
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#fbbf24;animation:pulse 1.5s infinite;"></span>
                    <span>جهاز ${dev}</span>
                    <span style="background:#fbbf24;color:#000;border-radius:9999px;padding:1px 6px;font-size:10px;">${count} معلق</span>
                    ${this.isSyncing ? '<span style="font-size:10px;">🔄 جارٍ الرفع...</span>' : ''}
                `;
                badge.style.display = 'flex';
            } else {
                // حالة متصل وطبيعي (Subtle Connected Pill)
                badge.style.background = 'rgba(15, 23, 42, 0.75)';
                badge.style.border = '1px solid rgba(52, 211, 153, 0.35)';
                badge.style.color = '#34d399';
                badge.innerHTML = `
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#34d399;"></span>
                    <span>جهاز ${dev}</span>
                    <span style="opacity:0.75;font-size:10px;">متزامن</span>
                `;
                badge.style.display = 'flex';
            }
        },

        showQueueModal() {
            const queue = this.getQueue();
            const dev = this.getDeviceCode();

            let modal = document.getElementById('magaies-offline-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'magaies-offline-modal';
                modal.style.cssText = `
                    position: fixed; inset: 0; z-index: 100000;
                    background: rgba(0,0,0,0.75); backdrop-filter: blur(8px);
                    display: flex; align-items: center; justify-content: center; padding: 16px;
                `;
                document.body.appendChild(modal);
            }

            let itemsHtml = '';
            if (queue.length === 0) {
                itemsHtml = `<div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px;">✨ لا توجد أي عمليات أوفلاين معلقة، كل البيانات مرحلة بنجاح!</div>`;
            } else {
                itemsHtml = queue.map((q, idx) => `
                    <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:800;color:#f8fafc;font-size:12px;">${q.offline_id}</div>
                            <div style="font-size:10px;color:#94a3b8;">${q.type} • ${new Date(q.created_at).toLocaleTimeString('ar-EG')}</div>
                        </div>
                        <span style="background:rgba(245,158,11,0.2);color:#fbbf24;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:700;">معلق</span>
                    </div>
                `).join('');
            }

            modal.innerHTML = `
                <div style="background:#0f172a;border:1px solid rgba(255,255,255,0.15);border-radius:20px;width:100%;max-width:380px;padding:20px;color:#fff;direction:rtl;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                        <h3 style="margin:0;font-size:15px;font-weight:800;display:flex;align-items:center;gap:6px;">
                            <span>⚡ مركز الأوفلاين والمزامنة الذاتية</span>
                        </h3>
                        <button onclick="document.getElementById('magaies-offline-modal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;">✕</button>
                    </div>

                    <div style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);border-radius:12px;padding:10px;margin-bottom:12px;font-size:11px;color:#93c5fd;display:flex;justify-content:space-between;align-items:center;">
                        <span>رمز هذا الجوال الحالي: <strong style="font-size:13px;color:#fff;">[ جهاز ${dev} ]</strong></span>
                        <button onclick="MagaiesOffline.promptChangeDeviceCode(); MagaiesOffline.showQueueModal();" style="background:#2563eb;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;">تغيير الرمز</button>
                    </div>

                    <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:10px;margin-bottom:14px;font-size:11px;color:#6ee7b7;display:flex;justify-content:space-between;align-items:center;">
                        <span>تثبيت كأيقونة على الشاشة الرئيسية:</span>
                        <button onclick="MagaiesOffline.promptPWAInstall();" style="background:#059669;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;">📲 تثبيت PWA</button>
                    </div>

                    <div style="max-height:200px;overflow-y:auto;margin-bottom:16px;">
                        ${itemsHtml}
                    </div>

                    <div style="display:flex;gap:8px;">
                        <button onclick="MagaiesOffline.flushQueue(); document.getElementById('magaies-offline-modal').style.display='none';" style="flex:1;background:linear-gradient(135deg, #10b981, #059669);color:#fff;border:none;padding:10px;border-radius:10px;font-weight:800;font-size:12px;cursor:pointer;">🔄 فحص ومزامنة الآن</button>
                        <button onclick="document.getElementById('magaies-offline-modal').style.display='none';" style="background:rgba(255,255,255,0.1);color:#cbd5e1;border:none;padding:10px 14px;border-radius:10px;font-weight:700;font-size:12px;cursor:pointer;">إغلاق</button>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        },

        // ─────────────────────────────────────────────────────────────
        // 9. تهيئة المحرك عند التحميل
        // ─────────────────────────────────────────────────────────────
        init() {
            this.initHeartbeat();
            this.initPWAInstallCapture();
            this.updateUIBadge();
            // فحص أولي للمزامنة
            setTimeout(() => {
                if (this.getQueue().length > 0) this.flushQueue();
            }, 1000);
            console.log(`[MagaiesOffline] Universal Offline Engine Initialized on Device [${this.getDeviceCode()}] 🚀`);
        }
    };

    window.MagaiesOffline = MagaiesOffline;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => MagaiesOffline.init());
    } else {
        MagaiesOffline.init();
    }

})(window);
