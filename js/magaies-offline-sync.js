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

    if (window.__MAGAIES_OFFLINE_ENGINE_LOADED__) return;
    window.__MAGAIES_OFFLINE_ENGINE_LOADED__ = true;

    const STORAGE_KEY_QUEUE = 'magaies_universal_offline_queue';
    const STORAGE_KEY_DEVICE = 'magaies_device_code';
    const STORAGE_KEY_SEQ = 'magaies_offline_sequences';
    const STORAGE_KEY_SYNCED_HISTORY = 'magaies_offline_synced_history';
    const STORAGE_KEY_CACHE_ACCOUNTS = 'magaies_offline_cache_accounts';
    const STORAGE_KEY_CACHE_ITEMS = 'magaies_offline_cache_items';

    const MagaiesOffline = {
        isOnline: navigator.onLine,
        isSyncing: false,
        pulseInterval: null,
        deferredPrompt: null,

        normalizeType(type) {
            const t = String(type || 'sales').toLowerCase().trim();
            if (t === 'sales_invoice' || t === 'sale' || t === 'pos') return 'sales';
            if (t === 'salessplit' || t === 'sales_split' || t === 'split_sales') return 'sales_split';
            if (t === 'purchase_invoice' || t === 'purchases') return 'purchase';
            if (t === 'purchasesplit' || t === 'purchase_split' || t === 'split_purchase') return 'purchase_split';
            if (t === 'sales_delivery' || t === 'delivery_note' || t === 'salesdelivery') return 'delivery';
            if (t === 'purchasereceive' || t === 'purchase_receive' || t === 'purchase_receipt' || t === 'grn') return 'purchase_receipt';
            if (t === 'voucher' || t === 'cash_payment' || t === 'cash_receipt' || t === 'single_payment' || t === 'single_receipt' || t === 'financial') return 'finance';
            if (t === 'journal' || t === 'journal_voucher' || t === 'journal_entry') return 'journal_entry';
            if (t === 'stock_adjustment' || t === 'adjustment' || t === 'inventory_adjustment') return 'stock_adjustment';
            if (t === 'warehouse_transfer' || t === 'transfer') return 'warehouse_transfer';
            return t;
        },

        // ─────────────────────────────────────────────────────────────
        // 1. إدارة معرف ورمز الجهاز (Device Code)
        // ─────────────────────────────────────────────────────────────
        getDeviceCode() {
            let code = localStorage.getItem(STORAGE_KEY_DEVICE);
            if (!code || code.trim() === '') {
                code = '1';
                localStorage.setItem(STORAGE_KEY_DEVICE, code);
            }
            return code.trim();
        },

        setDeviceCode(newCode) {
            if (!newCode || String(newCode).trim() === '') return;
            localStorage.setItem(STORAGE_KEY_DEVICE, String(newCode).trim());
            this.updateUIBadge();
            const msg = `📱 تم ضبط رقم هذا الجوال إلى: [جوال ${String(newCode).trim()}]`;
            if (window.showToastNotification) window.showToastNotification(msg);
            else if (window.showToast) window.showToast(msg, 'info');
        },

        promptChangeDeviceCode() {
            const current = this.getDeviceCode();
            const chosen = prompt('أدخل رقم هذا الجوال للتمييز في وضع الأوفلاين (مثال: 1 للجوال الأول، 2 للجوال الثاني، وهكذا):', current);
            if (chosen && chosen.trim() !== '') {
                this.setDeviceCode(chosen.trim());
            }
        },

        // ─────────────────────────────────────────────────────────────
        // 2. توليد التسلسل الدقيق المتصل للأوفلاين والأونلاين (Strict Universal Sequence)
        // ─────────────────────────────────────────────────────────────
        getPrefixForType(rawType) {
            const type = this.normalizeType(rawType);
            const now = new Date();
            const yymm = String(now.getFullYear()).slice(-2) + String(now.getMonth() + 1).padStart(2, '0');
            if (type === 'sales' || type === 'sales_split') return { letter: 'S', prefix: 'S' + yymm };
            if (type === 'purchase' || type === 'purchase_split') return { letter: 'P', prefix: 'P' + yymm };
            if (type === 'finance') return { letter: 'E', prefix: 'E' + yymm };
            if (type === 'delivery') return { letter: 'D', prefix: 'D' + yymm };
            if (type === 'purchase_receipt') return { letter: 'R', prefix: 'R' + yymm };
            if (type === 'journal_entry') return { letter: 'J', prefix: 'J' + yymm };
            if (type === 'stock_adjustment') return { letter: 'I', prefix: 'I' + yymm };
            if (type === 'warehouse_transfer') return { letter: 'T', prefix: 'T' + yymm };
            return { letter: 'S', prefix: 'S' + yymm };
        },

        extractUniversalSeq(rawNo, letter) {
            const s = String(rawNo || '').trim().toUpperCase();
            if (!s) return 0;
            if (s === 'S26101012' || s === 'P26100045' || s === '840' || s === '841' || s === '842') return 0;
            const L = String(letter || 'S').toUpperCase();
            // تجاهل الأرقام العشوائية القديمة المكونة من 5 خانات بعد السنة والشهر (مثل S261048392)
            if (new RegExp('^' + L + '\\d{4}\\d{5,}$').test(s)) return 0;
            // صيغة قياسية: S26100625 أو P26100126 أو E26100525
            let m = s.match(new RegExp('^' + L + '\\d{4}(\\d{4})$'));
            if (m) return parseInt(m[1], 10) || 0;
            // صيغة مختصرة مثل P2600126 أو S2600625
            m = s.match(new RegExp('^' + L + '\\d{3}(\\d{4})$'));
            if (m) return parseInt(m[1], 10) || 0;
            // صيغة رقمية خالصة مثل 00625 أو 00126
            if (/^\d{1,6}$/.test(s)) {
                const v = parseInt(s, 10) || 0;
                return (v > 0 && v < 100000) ? v : 0;
            }
            m = s.match(new RegExp('^' + L + '[-_]?(\\d{1,5})$'));
            if (m) return parseInt(m[1], 10) || 0;
            return 0;
        },

        updateKnownSequencesFromMirror(mirrorData) {
            if (!mirrorData || typeof mirrorData !== 'object') return;
            try {
                const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                const pairs = [
                    { type: 'sales', letter: 'S', node: mirrorData.sales, field: 'lastInvoiceNo', nextField: 'nextInvoiceNo' },
                    { type: 'purchase', letter: 'P', node: mirrorData.purchase, field: 'lastInvoiceNo', nextField: 'nextInvoiceNo' },
                    { type: 'finance', letter: 'E', node: mirrorData.finance, field: 'lastVoucherNo', nextField: 'nextVoucherNo' }
                ];
                pairs.forEach(p => {
                    if (!p.node) return;
                    const s1 = this.extractUniversalSeq(p.node[p.field], p.letter);
                    const s2 = this.extractUniversalSeq(p.node[p.nextField], p.letter);
                    const serverMax = Math.max(s1, s2 > 0 ? s2 - 1 : 0, parseInt(p.node.maxSeq) || 0);
                    if (serverMax > 0) {
                        known[p.type] = Math.max(parseInt(known[p.type]) || 0, serverMax);
                        if (p.type === 'sales') known['sales_split'] = Math.max(parseInt(known['sales_split']) || 0, serverMax);
                        if (p.type === 'purchase') known['purchase_split'] = Math.max(parseInt(known['purchase_split']) || 0, serverMax);
                    }
                });
                localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
            } catch (e) {}
        },

        getHighestKnownSeq(rawType) {
            const type = this.normalizeType(rawType);
            const { letter } = this.getPrefixForType(type);
            let maxSeq = 0;

            try {
                const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                const relatedTypes = (type === 'sales' || type === 'sales_split')
                    ? ['sales', 'sales_split']
                    : (type === 'purchase' || type === 'purchase_split')
                        ? ['purchase', 'purchase_split']
                        : [type];
                relatedTypes.forEach(rt => {
                    if (known[rt] && parseInt(known[rt], 10) > maxSeq) {
                        maxSeq = parseInt(known[rt], 10);
                    }
                });
            } catch (e) {}

            // فحص الكاش الحي من السيرفر إن وجد
            try {
                if (window._mirrorCache) {
                    if ((type === 'sales' || type === 'sales_split') && window._mirrorCache.sales) {
                        const s = this.extractUniversalSeq(window._mirrorCache.sales.nextInvoiceNo, 'S');
                        if (s > 1 && (s - 1) > maxSeq) maxSeq = s - 1;
                        const sLast = this.extractUniversalSeq(window._mirrorCache.sales.lastInvoiceNo, 'S');
                        if (sLast > maxSeq) maxSeq = sLast;
                    } else if ((type === 'purchase' || type === 'purchase_split') && window._mirrorCache.purchase) {
                        const s = this.extractUniversalSeq(window._mirrorCache.purchase.nextInvoiceNo, 'P');
                        if (s > 1 && (s - 1) > maxSeq) maxSeq = s - 1;
                        const sLast = this.extractUniversalSeq(window._mirrorCache.purchase.lastInvoiceNo, 'P');
                        if (sLast > maxSeq) maxSeq = sLast;
                    } else if (type === 'finance' && window._mirrorCache.finance) {
                        const s = this.extractUniversalSeq(window._mirrorCache.finance.nextVoucherNo, 'E');
                        if (s > 1 && (s - 1) > maxSeq) maxSeq = s - 1;
                        const sLast = this.extractUniversalSeq(window._mirrorCache.finance.lastVoucherNo, 'E');
                        if (sLast > maxSeq) maxSeq = sLast;
                    }
                }
            } catch (e) {}

            // فحص السجلات المحلية في المتصفح (اللابتوب + الجوال + الطابور)
            const keysToScan = [];
            if (type === 'sales' || type === 'sales_split') {
                keysToScan.push('magaies_local_sales_invoices_v1', 'erp_sales_invoices_v1', 'erp_sales_v3');
            } else if (type === 'purchase' || type === 'purchase_split') {
                keysToScan.push('magaies_local_purchase_invoices_v1', 'erp_purchase_invoices_v1', 'erp_purchases_v5');
            } else if (type === 'finance') {
                keysToScan.push('magaiees_vouchers_history', 'erp_single_payments_v1', 'magaies_mobile_offline_queue');
            } else if (type === 'delivery') {
                keysToScan.push('erp_delivery_orders_v1');
            } else if (type === 'purchase_receipt') {
                keysToScan.push('erp_purchase_receipts_v1');
            }

            keysToScan.forEach(k => {
                try {
                    const getTK = window.getTenantKey || (x => x);
                    const raw = localStorage.getItem(getTK(k)) || localStorage.getItem(k);
                    if (raw) {
                        const arr = JSON.parse(raw);
                        if (Array.isArray(arr)) {
                            arr.forEach(item => {
                                if (!item || item.deleted) return;
                                const docNo = item.invoiceNo || item.voucherNo || item.deliveryNo || item.receiptNo || item.id || '';
                                const seq = this.extractUniversalSeq(docNo, letter);
                                if (seq > maxSeq) maxSeq = seq;
                            });
                        }
                    }
                } catch (e) {}
            });

            // فحص طابور الأوفلاين الموحد
            try {
                const q = this.getQueue();
                q.forEach(qItem => {
                    const qType = this.normalizeType(qItem.type);
                    const samePool = (qType === type) ||
                        ((type === 'sales' || type === 'sales_split') && (qType === 'sales' || qType === 'sales_split')) ||
                        ((type === 'purchase' || type === 'purchase_split') && (qType === 'purchase' || qType === 'purchase_split'));
                    if (samePool) {
                        const seq = this.extractUniversalSeq(qItem.offline_id, letter);
                        if (seq > maxSeq) maxSeq = seq;
                    }
                });
            } catch (e) {}

            return maxSeq;
        },

        peekNextSequentialNumber(rawType) {
            const type = this.normalizeType(rawType);
            const { prefix } = this.getPrefixForType(type);
            const maxSeq = this.getHighestKnownSeq(type);
            return prefix + String(maxSeq + 1).padStart(4, '0');
        },

        getNextSequentialNumber(rawType, commitIncrement = true) {
            const type = this.normalizeType(rawType);
            const { prefix } = this.getPrefixForType(type);
            const nextSeq = this.getHighestKnownSeq(type) + 1;
            if (commitIncrement) {
                try {
                    const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                    known[type] = nextSeq;
                    if (type === 'sales' || type === 'sales_split') {
                        known['sales'] = nextSeq;
                        known['sales_split'] = nextSeq;
                    } else if (type === 'purchase' || type === 'purchase_split') {
                        known['purchase'] = nextSeq;
                        known['purchase_split'] = nextSeq;
                    }
                    localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
                } catch (e) {}
            }
            return prefix + String(nextSeq).padStart(4, '0');
        },

        getNextOfflineId(rawType) {
            // استخدام الترقيم المتسلسل الدقيق المتصل بالسيرفر واللابتوب (مثال: S26100626 أو P26100127 أو E26100526)
            return this.getNextSequentialNumber(rawType, true);
        },

        // ─────────────────────────────────────────────────────────────
        // 3. إدارة طابور الأوفلاين وسجل الاعتماد (Universal Offline Queue)
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

        getSyncedHistory() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY_SYNCED_HISTORY) || '[]');
            } catch(e) {
                return [];
            }
        },

        recordSyncedHistory(syncedItems) {
            if (!Array.isArray(syncedItems) || syncedItems.length === 0) return;
            try {
                const hist = this.getSyncedHistory();
                syncedItems.forEach(item => {
                    hist.unshift({
                        offline_id: item.offline_id,
                        real_id: item.real_id,
                        type: item.type || 'sales',
                        synced_at: item.synced_at || new Date().toISOString()
                    });
                    // تحديث الترقيم المحلي ليواكب الرقم المعتمد من السيرفر فوراً
                    const normType = this.normalizeType(item.type || 'sales');
                    const { letter } = this.getPrefixForType(normType);
                    const realSeq = this.extractUniversalSeq(item.real_id, letter);
                    if (realSeq > 0) {
                        const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                        known[normType] = Math.max(parseInt(known[normType]) || 0, realSeq);
                        localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
                    }
                });
                localStorage.setItem(STORAGE_KEY_SYNCED_HISTORY, JSON.stringify(hist.slice(0, 30)));
            } catch(e) {}
        },

        computePayloadFingerprint(type, payload) {
            if (!payload || typeof payload !== 'object') return '';
            const normType = this.normalizeType(type);
            const party = String(payload.customerName || payload.customer || payload.supplierName || payload.supplier || payload.toAccountName || payload.toAccount || '').trim();
            const fromAcc = String(payload.fromAccountName || payload.fromAccount || payload.warehouse || '').trim();
            const total = Number(payload.total || payload.netTotal || payload.amountLocal || payload.amount || 0).toFixed(2);
            const ref = String(payload.mobileTransferNo || payload.refNo || '').trim();
            const notes = String(payload.notes || '').trim();
            let itemsSig = '';
            if (Array.isArray(payload.items)) {
                itemsSig = payload.items.map(it => `${it.name || it.itemName || ''}:${it.qty || it.quantity || 0}:${it.price || 0}`).join('|');
            }
            return `${normType}::${party}::${fromAcc}::${total}::${ref}::${notes}::${itemsSig}`;
        },

        enqueue(rawType, payload, customOfflineId) {
            const type = this.normalizeType(rawType);
            const queue = this.getQueue();
            const devCode = this.getDeviceCode();
            const fingerprint = this.computePayloadFingerprint(type, payload);
            const nowMs = Date.now();

            // فحص صارم لمنع تكرار نفس الفاتورة/السند عند الضغط المتكرر على حفظ في الأوفلاين
            if (!this._recentFingerprints) this._recentFingerprints = {};
            if (fingerprint && this._recentFingerprints[fingerprint] && (nowMs - this._recentFingerprints[fingerprint].time < 15000)) {
                console.warn('[OfflineEngine] Duplicate save click blocked (fingerprint match):', this._recentFingerprints[fingerprint].id);
                return this._recentFingerprints[fingerprint].id;
            }

            const existingInQueue = queue.find(q => {
                if (payload.idempotencyKey && q.idempotency_key === payload.idempotencyKey) return true;
                if (this.normalizeType(q.type) === type && fingerprint) {
                    const qFp = this.computePayloadFingerprint(q.type, q.payload);
                    const qTime = new Date(q.created_at || 0).getTime();
                    if (qFp === fingerprint && (nowMs - qTime < 25000)) return true;
                }
                return false;
            });
            if (existingInQueue) {
                console.warn('[OfflineEngine] Duplicate queue item prevented, returning existing ID:', existingInQueue.offline_id);
                return existingInQueue.offline_id;
            }

            const { letter } = this.getPrefixForType(type);
            let offlineId = customOfflineId;
            if (!offlineId || this.extractUniversalSeq(offlineId, letter) <= 0) {
                offlineId = this.getNextSequentialNumber(type, true);
            } else {
                const customSeq = this.extractUniversalSeq(offlineId, letter);
                const highest = this.getHighestKnownSeq(type);
                if (customSeq <= highest) {
                    offlineId = this.getNextSequentialNumber(type, true);
                } else {
                    try {
                        const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                        known[type] = Math.max(parseInt(known[type]) || 0, customSeq);
                        localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
                    } catch (e) {}
                }
            }

            // مفتاح عدم التكرار الصارم لمنع التكرار عند تقطع الاتصال (Idempotency Key)
            const idemKey = payload.idempotencyKey || ('idempotent-dev' + devCode + '-' + Date.now() + '-' + Math.random().toString(36).substr(2, 8));
            payload.idempotencyKey = idemKey;

            const queueItem = {
                id: 'q-' + Date.now() + '-' + Math.random().toString(36).substr(2, 6),
                type: type,
                offline_id: offlineId,
                tempId: offlineId,
                device_code: devCode,
                idempotency_key: idemKey,
                created_at: new Date().toISOString(),
                payload: payload
            };

            // إرفاق المعرف المتسلسل داخل كائن الفاتورة/السند
            if (type === 'sales' || type === 'sales_split') {
                payload.invoiceNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'purchase' || type === 'purchase_split') {
                payload.invoiceNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'delivery') {
                payload.deliveryNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'purchase_receipt') {
                payload.receiptNo = offlineId;
                payload.id = offlineId;
            } else if (type === 'finance') {
                payload.voucherNo = offlineId;
                payload.id = offlineId;
            }
            payload.isOffline = true;
            payload.offline_id = offlineId;
            payload.tempId = offlineId;
            payload.deviceCode = devCode;

            queue.push(queueItem);
            this.saveQueue(queue);

            if (fingerprint) {
                this._recentFingerprints[fingerprint] = { id: offlineId, time: nowMs };
            }

            // تشغيل رجّاش التوثيق
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate([40, 30, 40]);
            }

            console.log(`[OfflineEngine] Enqueued ${type} with Sequential ID: [${offlineId}] on Device [${devCode}]`);
            return offlineId;
        },

        // ─────────────────────────────────────────────────────────────
        // 4. المزامنة التلقائية اللحظية (Instant Auto-Sync Engine)
        // ─────────────────────────────────────────────────────────────
        syncAllPending() {
            return this.flushQueue();
        },

        syncNow() {
            return this.flushQueue();
        },

        async flushQueue() {
            if (typeof window.flushOfflineQueue === 'function') {
                try { window.flushOfflineQueue(); } catch (e) {}
            }
            if (this.isSyncing) return;
            const queue = this.getQueue();
            if (queue.length === 0) return;

            this.isSyncing = true;
            this.updateUIBadge();

            try {
                // إرسال الدفعة بالكامل في طلب واحد سريع جداً (Batch Request < 100ms) لأضعف شبكة 2G
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 8500);

                const res = await fetch('api/mirror_sync.php?action=sync_offline_batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: queue, device_code: this.getDeviceCode() }),
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                const out = await res.json();
                if (out && out.success && Array.isArray(out.synced)) {
                    this.isOnline = true;
                    // حفظ سجل التحويل من رقم أوفلاين إلى رقم السيستم الرسمي
                    this.recordSyncedHistory(out.synced);

                    // تحديث الأرقام وكامل البيانات في السجلات المحلية (العامة والخاصة بالشركة Tenant) عند الدمج في السيرفر
                    try {
                        const getTK = window.getTenantKey || ((k) => k);
                        out.synced.forEach(sItem => {
                            if (!sItem.offline_id || !sItem.real_id) return;
                            const sType = this.normalizeType(sItem.type || 'sales');
                            const serverRec = (sItem.record && typeof sItem.record === 'object') ? sItem.record : null;

                            const updateLocalArr = (baseKey, allowInsert = false) => {
                                const keysToUpdate = [baseKey];
                                const tk = getTK(baseKey);
                                if (tk && tk !== baseKey) keysToUpdate.push(tk);
                                keysToUpdate.forEach(storageKey => {
                                    let arr = [];
                                    try {
                                        const raw = localStorage.getItem(storageKey);
                                        if (raw) arr = JSON.parse(raw);
                                        if (!Array.isArray(arr)) arr = [];
                                    } catch (e) { arr = []; }

                                    let matched = false;
                                    for (let i = 0; i < arr.length; i++) {
                                        const rec = arr[i];
                                        if (!rec) continue;
                                        const recId = rec.id || rec.invoiceNo || rec.voucherNo || rec.deliveryNo || rec.receiptNo || rec.docNo || '';
                                        if (recId === sItem.offline_id || rec.offline_id === sItem.offline_id || recId === sItem.real_id) {
                                            if (serverRec) {
                                                arr[i] = Object.assign({}, rec, serverRec, {
                                                    id: sItem.real_id,
                                                    isOffline: false,
                                                    synced: true
                                                });
                                            } else {
                                                if (rec.id === sItem.offline_id) rec.id = sItem.real_id;
                                                if (rec.invoiceNo === sItem.offline_id) rec.invoiceNo = sItem.real_id;
                                                if (rec.voucherNo === sItem.offline_id) rec.voucherNo = sItem.real_id;
                                                if (rec.deliveryNo === sItem.offline_id) rec.deliveryNo = sItem.real_id;
                                                if (rec.receiptNo === sItem.offline_id) rec.receiptNo = sItem.real_id;
                                                rec.isOffline = false;
                                                rec.synced = true;
                                            }
                                            matched = true;
                                        }
                                    }
                                    if (!matched && allowInsert && serverRec) {
                                        arr.unshift(Object.assign({}, serverRec, { id: sItem.real_id, isOffline: false, synced: true }));
                                    }
                                    if (matched || (allowInsert && serverRec)) {
                                        localStorage.setItem(storageKey, JSON.stringify(arr));
                                    }
                                });
                            };

                            if (sType === 'sales' || sType === 'sales_split') {
                                updateLocalArr('magaies_local_sales_invoices_v1', true);
                                updateLocalArr('erp_sales_invoices_v1', true);
                                updateLocalArr('erp_sales_v3', false);
                                if (serverRec) {
                                    try { localStorage.setItem('erp_inv_' + sItem.real_id, JSON.stringify(serverRec)); } catch (e) {}
                                }
                            } else if (sType === 'purchase' || sType === 'purchase_split') {
                                updateLocalArr('magaies_local_purchase_invoices_v1', true);
                                updateLocalArr('erp_purchase_invoices_v1', true);
                                updateLocalArr('erp_purchases_v5', false);
                                if (serverRec) {
                                    try { localStorage.setItem('erp_pur_' + sItem.real_id, JSON.stringify(serverRec)); } catch (e) {}
                                }
                            } else if (sType === 'finance') {
                                updateLocalArr('magaiees_vouchers_history', true);
                                updateLocalArr('magaies_vouchers_cache', true);
                                updateLocalArr('erp_single_payments_v1', true);
                                updateLocalArr('erp_payments_v1', false);
                                updateLocalArr('erp_receipts_v1', false);
                            } else if (sType === 'delivery') {
                                updateLocalArr('magaies_local_deliveries_v1', true);
                                updateLocalArr('erp_delivery_orders_v1', true);
                            } else if (sType === 'purchase_receipt') {
                                updateLocalArr('magaies_local_receipts_v1', true);
                                updateLocalArr('erp_purchase_receipts_v1', true);
                            } else if (sType === 'journal_entry') {
                                updateLocalArr('magaies_local_journals_v1', true);
                            } else if (sType === 'stock_adjustment' || sType === 'warehouse_transfer') {
                                updateLocalArr('magaies_local_inventory_docs_v1', true);
                            }
                        });
                    } catch (e) {}

                    // إفراغ الطابور بعد الترحيل الكامل
                    this.saveQueue([]);

                    // إعلام واجهة المستخدم بالنجاح والتحول للسيريال الرسمي
                    const count = out.synced.length;
                    const sample = out.synced[0] ? (out.synced[0].offline_id === out.synced[0].real_id ? `(اعتمدت برقم #${out.synced[0].real_id})` : `(${out.synced[0].offline_id} ➔ اعتمدت برقم #${out.synced[0].real_id})`) : '';
                    const successMsg = `🟢 تم تلقائياً اعتماد وترحيل ${count} حركة أوفلاين بكامل بياناتها في السيستم! ${sample}`;
                    
                    if (window.showFloatingToast) {
                        window.showFloatingToast(successMsg);
                    } else if (window.showToastNotification) {
                        window.showToastNotification(successMsg);
                    } else if (window.showToast) {
                        window.showToast(successMsg, 'success');
                    } else if (window.showModernToast) {
                        window.showModernToast(successMsg, 'success', 4500);
                    }

                    // تحديث أرقام المرآة والبيانات السحابية في الشاشات المفتوحة فوراً
                    if (typeof window.pushToCloud === 'function') {
                        try { window.pushToCloud(true); } catch (e) {}
                    }
                    if (typeof window.fetchMirrorNumbers === 'function') window.fetchMirrorNumbers();
                    if (typeof window.initMobileMirrorSync === 'function') window.initMobileMirrorSync();
                    if (typeof window.fetchLiveDbData === 'function') window.fetchLiveDbData(false, true);
                    if (typeof window.syncMirrorData === 'function') window.syncMirrorData();
                    if (window.SuiteSalesPOS && typeof window.SuiteSalesPOS.loadRecentInvoices === 'function') {
                        try { window.SuiteSalesPOS.loadRecentInvoices(); } catch (e) {}
                    }
                    if (window.SuitePurchasePOS && typeof window.SuitePurchasePOS.loadRecentInvoices === 'function') {
                        try { window.SuitePurchasePOS.loadRecentInvoices(); } catch (e) {}
                    }
                    if (typeof window.initDesktopPurchaseMirrorSync === 'function') {
                        try { window.initDesktopPurchaseMirrorSync(); } catch (e) {}
                    }
                    if (typeof window.initDesktopMirrorSync === 'function') {
                        try { window.initDesktopMirrorSync(); } catch (e) {}
                    }
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
        // 5. مستشعر النبض الشبكي النشط (Active Micro-Pulse Heartbeat)
        // ─────────────────────────────────────────────────────────────
        triggerAllSyncEngines() {
            if (this.getQueue().length > 0 && !this.isSyncing) {
                this.flushQueue();
            }
            if (typeof window.flushOfflineQueue === 'function') {
                try { window.flushOfflineQueue(); } catch (e) {}
            }
            if (typeof window.pushToCloud === 'function' && navigator.onLine !== false) {
                try { window.pushToCloud(); } catch (e) {}
            }
        },

        initHeartbeat() {
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.updateUIBadge();
                this.triggerAllSyncEngines();
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
                this.updateUIBadge();
            });

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    this.triggerAllSyncEngines();
                }
            });

            window.addEventListener('pageshow', () => {
                this.triggerAllSyncEngines();
            });

            // فحص تلقائي نشط للطابور كل 3.5 ثوانٍ عند وجود عناصر معلقة (يعمل حتى لو تأخر حدث online في الجوال)
            if (this.pulseInterval) clearInterval(this.pulseInterval);
            this.pulseInterval = setInterval(() => {
                const q = this.getQueue();
                let hasMobileVoucherQueue = false;
                try {
                    const mq = JSON.parse(localStorage.getItem('magaies_mobile_offline_queue') || '[]');
                    hasMobileVoucherQueue = Array.isArray(mq) && mq.length > 0;
                } catch (e) {}

                if (q.length > 0 && !this.isSyncing) {
                    this.flushQueue();
                }
                if (hasMobileVoucherQueue && typeof window.flushOfflineQueue === 'function') {
                    try { window.flushOfflineQueue(); } catch (e) {}
                }
            }, 3500);

            // فحص فوري عند فتح التطبيق أو العودة للشاشة
            window.addEventListener('focus', () => {
                this.triggerAllSyncEngines();
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

        clearQueue() {
            try {
                localStorage.removeItem(STORAGE_KEY_QUEUE);
                localStorage.removeItem('magaies_mobile_offline_queue');
                this.updateUIBadge();
            } catch (e) {}
        },

        // ─────────────────────────────────────────────────────────────
        // 8. واجهة المستخدم والشارة العائمة (تم سحب وإخفاء الشارة السفلية تحت الواتساب لمنع التشتيت)
        // ─────────────────────────────────────────────────────────────
        updateUIBadge() {
            const existing = document.getElementById('magaies-offline-pill');
            if (existing) {
                existing.style.display = 'none';
                existing.remove();
            }
            // الحفاظ الكامل على جميع الحركات المسجلة في طابور الأوفلاين حتى يتم رفعها للسيرفر مهما طالت مدة انقطاع الإنترنت
        },

        showQueueModal() {
            const queue = this.getQueue();
            const syncedHist = this.getSyncedHistory();
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
                itemsHtml = `<div style="text-align:center;padding:16px;color:#94a3b8;font-size:12px;">✨ لا توجد أي عمليات أوفلاين معلقة، كل البيانات مرحلة ومعتمدة بنجاح!</div>`;
            } else {
                itemsHtml = queue.map((q) => `
                    <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);border-radius:10px;padding:10px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:800;color:#fbbf24;font-size:12px;">${q.offline_id}</div>
                            <div style="font-size:10px;color:#94a3b8;">${q.type} • ${new Date(q.created_at).toLocaleTimeString('ar-EG')}</div>
                        </div>
                        <span style="background:rgba(245,158,11,0.2);color:#fbbf24;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:700;">⏳ بانتظار الشبكة</span>
                    </div>
                `).join('');
            }

            let histHtml = '';
            if (syncedHist.length > 0) {
                histHtml = `
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,0.1);">
                        <div style="font-size:11px;font-weight:800;color:#34d399;margin-bottom:6px;">✅ آخر حركات الأوفلاين التي أخذت سيريال السيستم:</div>
                        ${syncedHist.slice(0, 6).map(h => `
                            <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);border-radius:8px;padding:6px 10px;margin-bottom:5px;display:flex;justify-content:space-between;align-items:center;font-size:11px;">
                                <span style="color:#cbd5e1;">${h.offline_id}</span>
                                <span style="color:#34d399;font-weight:900;">➔ #${h.real_id}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            modal.innerHTML = `
                <div style="background:#0f172a;border:1px solid rgba(255,255,255,0.15);border-radius:20px;width:100%;max-width:390px;padding:18px;color:#fff;direction:rtl;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <h3 style="margin:0;font-size:14px;font-weight:800;display:flex;align-items:center;gap:6px;">
                            <span>⚡ نظام الأوفلاين الذكي والترقيم الفوري</span>
                        </h3>
                        <button onclick="document.getElementById('magaies-offline-modal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;">✕</button>
                    </div>

                    <div style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);border-radius:12px;padding:10px;margin-bottom:10px;font-size:11px;color:#93c5fd;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <span>رقم هذا الجوال: <strong style="font-size:13px;color:#fff;">[ جوال رقم ${dev} ]</strong></span>
                            <button onclick="MagaiesOffline.promptChangeDeviceCode(); MagaiesOffline.showQueueModal();" style="background:#2563eb;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;">تخصيص</button>
                        </div>
                        <div style="display:flex;gap:5px;">
                            ${['1','2','3','4','5'].map(n => `
                                <button onclick="MagaiesOffline.setDeviceCode('${n}'); MagaiesOffline.showQueueModal();" style="flex:1;padding:4px 0;border-radius:6px;border:1px solid ${dev===n ? '#60a5fa' : 'rgba(255,255,255,0.15)'};background:${dev===n ? '#2563eb' : 'rgba(255,255,255,0.05)'};color:#fff;font-size:10px;font-weight:800;cursor:pointer;">جوال ${n}</button>
                            `).join('')}
                        </div>
                    </div>

                    <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:9px 10px;margin-bottom:12px;font-size:11px;color:#6ee7b7;display:flex;justify-content:space-between;align-items:center;">
                        <span>تثبيت كأيقونة أوفلاين على الشاشة:</span>
                        <button onclick="MagaiesOffline.promptPWAInstall();" style="background:#059669;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;">📲 تثبيت PWA</button>
                    </div>

                    <div style="max-height:210px;overflow-y:auto;margin-bottom:14px;">
                        ${itemsHtml}
                        ${histHtml}
                    </div>

                    <div style="display:flex;gap:8px;">
                        <button onclick="MagaiesOffline.flushQueue(); document.getElementById('magaies-offline-modal').style.display='none';" style="flex:1;background:linear-gradient(135deg, #10b981, #059669);color:#fff;border:none;padding:10px;border-radius:10px;font-weight:800;font-size:12px;cursor:pointer;">⚡ مزامنة وترقيم الآن</button>
                        ${queue.length > 0 ? `<button onclick="MagaiesOffline.clearQueue(); MagaiesOffline.showQueueModal();" style="background:rgba(239,68,68,0.2);color:#fca5a5;border:1px solid rgba(239,68,68,0.4);padding:10px 12px;border-radius:10px;font-weight:700;font-size:11px;cursor:pointer;">🗑️ مسح المعلق</button>` : ''}
                        <button onclick="document.getElementById('magaies-offline-modal').style.display='none';" style="background:rgba(255,255,255,0.1);color:#cbd5e1;border:none;padding:10px 14px;border-radius:10px;font-weight:700;font-size:12px;cursor:pointer;">إغلاق</button>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        },

        // ─────────────────────────────────────────────────────────────
        // 9. تسجيل Service Worker الخفيف للعمل أوفلاين وعلى شبكة 2G
        // ─────────────────────────────────────────────────────────────
        initServiceWorkerAndCache() {
            if (!('serviceWorker' in navigator)) return;
            try {
                navigator.serviceWorker.register('./sw.js', { scope: './' }).catch(() => {});
            } catch (e) {}
        },

        // ─────────────────────────────────────────────────────────────
        // 10. محرك التصغير التلقائي الذكي لرقم السند/الفاتورة في كافة الشاشات
        //     (بدون أي حلقات مفرغة أو استهلاك لمعالج الجوال)
        // ─────────────────────────────────────────────────────────────
        autoFitVoucherNumbers() {
            try {
                if (!document.getElementById('magaies-autofit-voucher-css')) {
                    const style = document.createElement('style');
                    style.id = 'magaies-autofit-voucher-css';
                    style.textContent = `
                        #inv-number,
                        #voucher-number,
                        #hdr-inv-no,
                        #lbl-inv-no,
                        #currentVoucherDisplay,
                        #opSalesInvoiceNoPill,
                        #opPurchaseNoPill,
                        #opSalesSplitInvoiceNoPill,
                        #opPurchaseSplitNoPill,
                        #opDeliveryNoPill,
                        #opReceiptNoPill,
                        #finTransferVoucherNo,
                        #finTransferCashReceiptNo,
                        #voucherDocNumber,
                        .order-id,
                        .voucher-id-badge,
                        .inv-no-badge,
                        [data-auto-fit-voucher] {
                            white-space: nowrap !important;
                            text-overflow: clip !important;
                            overflow: visible !important;
                            letter-spacing: 0px !important;
                            font-variant-numeric: tabular-nums !important;
                        }
                        #inv-number {
                            min-width: 110px !important;
                            padding-left: 4px !important;
                            padding-right: 4px !important;
                            text-align: center !important;
                            direction: ltr !important;
                        }
                        #voucher-number,
                        #finTransferVoucherNo,
                        #finTransferCashReceiptNo,
                        #voucherDocNumber {
                            padding-left: 4px !important;
                            padding-right: 4px !important;
                            text-align: center !important;
                            direction: ltr !important;
                        }
                        #opSalesInvoiceNoPill,
                        #opPurchaseNoPill,
                        #opSalesSplitInvoiceNoPill,
                        #opPurchaseSplitNoPill,
                        #opDeliveryNoPill,
                        #opReceiptNoPill,
                        #currentVoucherDisplay {
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            max-width: 100% !important;
                            direction: ltr !important;
                        }
                    `;
                    (document.head || document.documentElement).appendChild(style);
                }

                const invInput = document.getElementById('inv-number');
                if (invInput && invInput.parentElement) {
                    const parent = invInput.parentElement;
                    if (parent.dataset.fitExpanded !== '1') {
                        if (parent.style && parent.style.flex && parent.style.flex.includes('85px')) {
                            parent.style.flex = '0 0 auto';
                            parent.style.minWidth = '114px';
                        } else if (parent.offsetWidth > 0 && parent.offsetWidth < 105) {
                            parent.style.minWidth = '112px';
                        }
                        parent.dataset.fitExpanded = '1';
                    }
                }

                const selectors = [
                    '#inv-number',
                    '#voucher-number',
                    '#hdr-inv-no',
                    '#lbl-inv-no',
                    '#currentVoucherDisplay',
                    '#opSalesInvoiceNoPill',
                    '#opPurchaseNoPill',
                    '#opSalesSplitInvoiceNoPill',
                    '#opPurchaseSplitNoPill',
                    '#opDeliveryNoPill',
                    '#opReceiptNoPill',
                    '#finTransferVoucherNo',
                    '#finTransferCashReceiptNo',
                    '#voucherDocNumber',
                    '.order-id',
                    '.voucher-id-badge',
                    '.inv-no-badge',
                    '[data-auto-fit-voucher]'
                ].join(',');

                const elements = document.querySelectorAll(selectors);
                elements.forEach(el => {
                    if (!el) return;
                    if (el.id === 'currentVoucherDisplay' && typeof window.fitVoucherDisplaySize === 'function') {
                        window.fitVoucherDisplaySize();
                        return;
                    }
                    if (window.SmartPOSFontAI && typeof window.SmartPOSFontAI.scaleVoucherPill === 'function') {
                        const suiteIds = ['opSalesInvoiceNoPill', 'opPurchaseNoPill', 'opSalesSplitInvoiceNoPill', 'opPurchaseSplitNoPill', 'opDeliveryNoPill', 'opReceiptNoPill', 'finTransferVoucherNo', 'finTransferCashReceiptNo', 'voucherDocNumber'];
                        if (suiteIds.includes(el.id)) {
                            window.SmartPOSFontAI.scaleVoucherPill(el);
                            return;
                        }
                    }
                    const isInput = (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA');
                    const rawText = String(isInput ? (el.value || el.placeholder || '') : (el.textContent || '')).trim();
                    if (!rawText) return;

                    const clientW = el.clientWidth;
                    const fitKey = rawText + '|' + clientW;
                    if (el.dataset.lastFitKey === fitKey) return;
                    el.dataset.lastFitKey = fitKey;

                    if (el.title !== rawText) el.title = rawText;

                    if (!el.dataset.baseFontPx) {
                        const computed = window.getComputedStyle(el);
                        const parsed = parseFloat(computed.fontSize) || 23;
                        el.dataset.baseFontPx = String(Math.max(parsed, 22));
                    }

                    const basePx = parseFloat(el.dataset.baseFontPx) || 23;
                    const len = rawText.length;

                    let targetPx = basePx;
                    if (len <= 6) targetPx = Math.max(basePx, 24);
                    else if (len >= 18) targetPx = Math.min(basePx, 11.5);
                    else if (len >= 15) targetPx = Math.min(basePx, 13.5);
                    else if (len >= 13) targetPx = Math.min(basePx, 15.5);
                    else if (len >= 11) targetPx = Math.min(basePx, 17.5);
                    else if (len >= 9) targetPx = Math.min(basePx, 20.5);

                    el.style.setProperty('font-size', targetPx + 'px', 'important');

                    if (clientW > 10) {
                        const minPx = 10.0;
                        while (el.scrollWidth > clientW + 1 && targetPx > minPx) {
                            targetPx -= 0.5;
                            el.style.setProperty('font-size', targetPx + 'px', 'important');
                        }
                    }
                });
            } catch (e) {}
        },

        initAutoFitVoucherObserver() {
            const runFit = () => {
                if (this._fitTimer) clearTimeout(this._fitTimer);
                this._fitTimer = setTimeout(() => {
                    this.autoFitVoucherNumbers();
                }, 80);
            };
            this.autoFitVoucherNumbers();
            window.addEventListener('resize', runFit, { passive: true });
            document.addEventListener('input', (e) => {
                if (e && e.target && (e.target.id === 'inv-number' || e.target.id === 'voucher-number' || e.target.id === 'finTransferVoucherNo' || e.target.id === 'finTransferCashReceiptNo')) {
                    runFit();
                }
            }, { capture: true, passive: true });

            if (typeof MutationObserver !== 'undefined') {
                const startObs = () => {
                    if (!document.body) return;
                    const obs = new MutationObserver(() => runFit());
                    obs.observe(document.body, {
                        childList: true,
                        subtree: true,
                        characterData: true,
                        attributes: true,
                        attributeFilter: ['value']
                    });
                };
                if (document.body) startObs();
                else document.addEventListener('DOMContentLoaded', startObs);
            }
        },

        // ─────────────────────────────────────────────────────────────
        // 11. تهيئة المحرك عند التحميل
        // ─────────────────────────────────────────────────────────────
        init() {
            this.initServiceWorkerAndCache();
            this.initHeartbeat();
            this.initPWAInstallCapture();
            this.updateUIBadge();
            this.initAutoFitVoucherObserver();
            // فحص أولي للمزامنة
            setTimeout(() => {
                if (this.getQueue().length > 0) this.flushQueue();
            }, 1000);
            console.log(`[MagaiesOffline] Universal Offline Engine Initialized on Device [${this.getDeviceCode()}] 🚀`);
        }
    };

    window.MagaiesOffline = MagaiesOffline;
    window.MagaiesOfflineSync = MagaiesOffline;
    window.autoFitVoucherNumbers = () => MagaiesOffline.autoFitVoucherNumbers();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => MagaiesOffline.init());
    } else {
        MagaiesOffline.init();
    }

})(window);
