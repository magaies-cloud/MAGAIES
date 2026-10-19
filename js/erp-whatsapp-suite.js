/**
 * ══════════════════════════════════════════════════════════════════════════════
 * ERP WhatsApp Suite & Auto-Dispatch Engine
 * ══════════════════════════════════════════════════════════════════════════════
 * Features:
 *  1. WhatsApp Dispatch for Sales (direct-sales.php):
 *     - Official Delivery Order (إذن استلام وتسليم بضاعة رسمي - كميات فقط بدون أسعار)
 *     - Delivery Order Text Notification (إذن استلام كتابة كمعلومة بالكميات والأسعار)
 *     - Financial Sales Invoice & Customer Statement (فاتورة مبيعات وكشف حساب)
 *  2. WhatsApp Dispatch for Purchases (purchase.php):
 *     - Official Goods Receipt Order (إذن توريد واستلام مخزني رسمي - كميات فقط)
 *     - Goods Receipt Text Notification (إذن توريد كتابة كمعلومة)
 *     - Financial Purchase Invoice & Supplier Statement (فاتورة الشراء وحساب المورد)
 *  3. WhatsApp Dispatch for General Ledger / Statement (general-ledger.php):
 *     - Account Statement Summary Text Notification (ملخص كشف الحساب)
 *     - Official Statement of Account PDF Dispatch (كشف الحساب المعتمد)
 *  4. Unified Auto-Send System (زر الإرسال التلقائي مع الحفظ في كل الشاشات)
 * ══════════════════════════════════════════════════════════════════════════════
 */

(function(window, document) {
    'use strict';

    // ═══ Global Constants & Storage Keys ═══
    const KEY_SALES_AUTO_SEND = 'erp_auto_send_sales_v1';
    const KEY_PURCHASE_AUTO_SEND = 'erp_auto_send_purchase_v1';

    // ═══ Inject Unified Styles ═══
    function injectSuiteStyles() {
        if (document.getElementById('erp-wa-suite-styles')) return;

        const css = `
            /* ═══ WhatsApp Icon Button ═══ */
            .btn-whatsapp-icon-only {
                --tab-border-color: #25d366;
                --tab-glow-color: rgba(37, 211, 102, 0.85);
                background: linear-gradient(135deg, rgba(37, 211, 102, 0.22), rgba(18, 140, 126, 0.45)) !important;
                border: 2px solid #25d366 !important;
                border-radius: 10px !important;
                color: #ffffff !important;
                width: 44px !important;
                height: 42px !important;
                min-width: 44px !important;
                padding: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), 0 0 10px rgba(37, 211, 102, 0.3) !important;
                flex-shrink: 0 !important;
                transition: all 0.25s ease !important;
            }
            .btn-whatsapp-icon-only:hover {
                background: #090d16 !important;
                border-color: #25d366 !important;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 18px rgba(37, 211, 102, 0.85) !important;
                transform: translateY(-2px) scale(1.05) !important;
            }

            /* ═══ Auto-Send Control Wrapper & Buttons ═══ */
            .auto-send-control-box {
                display: inline-flex !important;
                align-items: center !important;
                position: relative !important;
                direction: rtl !important;
                flex-shrink: 0 !important;
            }
            .btn-auto-send-toggle-unit {
                height: 42px !important;
                font-size: 0.88rem !important;
                padding: 0 12px 0 10px !important;
                border-radius: 0 10px 10px 0 !important;
                border-left: none !important;
                font-weight: 800 !important;
                cursor: pointer !important;
                white-space: nowrap !important;
                transition: all 0.25s ease !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                font-family: inherit !important;
            }
            .btn-auto-send-options-unit {
                height: 42px !important;
                width: 28px !important;
                min-width: 28px !important;
                padding: 0 !important;
                border-radius: 10px 0 0 10px !important;
                border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                font-size: 0.75rem !important;
                transition: all 0.25s ease !important;
            }
            .btn-auto-send-toggle-unit.active,
            .btn-auto-send-options-unit.active {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(5, 150, 105, 0.5) 100%) !important;
                border: 1.8px solid #10b981 !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px rgba(0,0,0,0.4), 0 0 12px rgba(16, 185, 129, 0.35) !important;
            }
            .btn-auto-send-toggle-unit.active:hover,
            .btn-auto-send-options-unit.active:hover {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.42) 0%, rgba(5, 150, 105, 0.65) 100%) !important;
                box-shadow: 0 6px 18px rgba(0,0,0,0.5), 0 0 18px rgba(16, 185, 129, 0.55) !important;
                transform: translateY(-2px) !important;
            }
            .btn-auto-send-toggle-unit.inactive,
            .btn-auto-send-options-unit.inactive {
                background: linear-gradient(135deg, rgba(51, 65, 85, 0.5) 0%, rgba(30, 41, 59, 0.7) 100%) !important;
                border: 1.8px solid rgba(148, 163, 184, 0.4) !important;
                color: #cbd5e1 !important;
                box-shadow: 0 3px 10px rgba(0,0,0,0.2) !important;
            }
            .btn-auto-send-toggle-unit.inactive:hover,
            .btn-auto-send-options-unit.inactive:hover {
                background: linear-gradient(135deg, rgba(71, 85, 105, 0.7) 0%, rgba(51, 65, 85, 0.85) 100%) !important;
                border-color: #94a3b8 !important;
                color: #ffffff !important;
                transform: translateY(-2px) !important;
            }
            .auto-send-menu-dropdown {
                display: none;
                position: absolute;
                bottom: 50px;
                left: 0;
                background: #0f172a;
                border: 1.5px solid #334155;
                border-radius: 12px;
                padding: 6px;
                min-width: 290px;
                box-shadow: 0 14px 35px rgba(0,0,0,0.8), 0 0 20px rgba(0,0,0,0.6);
                z-index: 99999;
                direction: rtl;
                text-align: right;
                backdrop-filter: blur(14px);
            }
            .auto-send-menu-item {
                padding: 8px 12px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 0.83rem;
                font-weight: 800;
                color: #cbd5e1;
                display: flex;
                align-items: center;
                gap: 9px;
                margin-bottom: 3px;
                transition: all 0.2s ease;
            }
            .auto-send-menu-item:hover {
                background: rgba(255, 255, 255, 0.08);
                color: #ffffff;
                transform: translateX(-3px);
            }

            /* Light theme overrides */
            :root.light-theme .btn-auto-send-toggle-unit.active,
            :root.light-theme .btn-auto-send-options-unit.active {
                background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
                border-color: #059669 !important;
                color: #065f46 !important;
            }
            :root.light-theme .btn-auto-send-toggle-unit.inactive,
            :root.light-theme .btn-auto-send-options-unit.inactive {
                background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
                border-color: #cbd5e1 !important;
                color: #475569 !important;
            }
            :root.light-theme .auto-send-menu-dropdown {
                background: #ffffff !important;
                border-color: #cbd5e1 !important;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
            }
            :root.light-theme .auto-send-menu-item {
                color: #334155 !important;
            }
            :root.light-theme .auto-send-menu-item:hover {
                background: #f1f5f9 !important;
                color: #0f172a !important;
            }

            /* ═══ Global Suite Modal ═══ */
            #erp-wa-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(15, 23, 42, 0.85);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                z-index: 2000000 !important;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 15px;
                box-sizing: border-box;
                direction: rtl;
                font-family: 'Tajawal', sans-serif;
            }
            .erp-wa-card {
                background: linear-gradient(160deg, #1e293b 0%, #0f172a 100%);
                border: 2px solid #25d366;
                border-radius: 20px;
                width: 100%;
                max-width: 680px;
                max-height: 92vh;
                display: flex;
                flex-direction: column;
                box-shadow: 0 25px 60px rgba(0,0,0,0.8), 0 0 35px rgba(37, 211, 102, 0.3);
                overflow: hidden;
                animation: erp-wa-pop 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes erp-wa-pop {
                from { opacity: 0; transform: scale(0.92) translateY(20px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            .erp-wa-header {
                padding: 14px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: rgba(0,0,0,0.25);
            }
            .erp-wa-body {
                padding: 16px 20px;
                overflow-y: auto;
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 14px;
            }
            .erp-wa-footer {
                padding: 12px 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                background: rgba(0,0,0,0.3);
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
            }
            .erp-wa-tab-btn {
                flex: 1;
                min-width: 130px;
                padding: 9px 12px;
                border-radius: 10px;
                background: rgba(51, 65, 85, 0.4);
                border: 1.5px solid rgba(148, 163, 184, 0.25);
                color: #cbd5e1;
                font-weight: 800;
                font-size: 0.85rem;
                cursor: pointer;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                font-family: inherit;
            }
            .erp-wa-tab-btn.active {
                background: linear-gradient(135deg, rgba(37, 211, 102, 0.25) 0%, rgba(18, 140, 126, 0.4) 100%);
                border-color: #25d366;
                color: #ffffff;
                box-shadow: 0 0 14px rgba(37, 211, 102, 0.35);
            }
            .erp-wa-preview-box {
                background: #090d16;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 12px;
                padding: 12px;
                color: #e2e8f0;
                font-size: 0.86rem;
                line-height: 1.6;
                white-space: pre-wrap;
                max-height: 220px;
                overflow-y: auto;
                font-family: 'JetBrains Mono', monospace, 'Tajawal', sans-serif;
            }
        `;

        const styleEl = document.createElement('style');
        styleEl.id = 'erp-wa-suite-styles';
        styleEl.textContent = css;
        document.head.appendChild(styleEl);
    }

    // ═══ Phone Number Helper ═══
    function cleanPhoneNumber(phone) {
        if (!phone) return '';
        let str = String(phone).trim();
        if (str.includes('@lid') || str.includes('@s.whatsapp.net')) return str;
        // Normalize Eastern Arabic numerals to Western digits
        let cleaned = str.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
        cleaned = cleaned.replace(/[^\d+]/g, '');
        if (cleaned.startsWith('+')) cleaned = cleaned.substring(1);
        if (cleaned.startsWith('00')) cleaned = cleaned.substring(2);
        // If phone starts with 0 and has 10 digits (e.g. Sudan 09xxxxxxxx or 01xxxxxxxx)
        if (cleaned.startsWith('0') && cleaned.length === 10) {
            cleaned = '249' + cleaned.substring(1);
        } else if (cleaned.length === 9 && (cleaned.startsWith('9') || cleaned.startsWith('1'))) {
            cleaned = '249' + cleaned;
        }
        return cleaned;
    }

    // ═══ Robust Arabic Text Normalizer ═══
    function normalizeArabic(s) {
        return String(s || '').trim().toLowerCase()
            .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/[\u064B-\u065F]/g, '') // remove diacritics / tashkeel
            .replace(/^\[\d+\]\s*/, '')      // remove [123] prefix
            .replace(/^\d+\s*[-–]\s*/, '')   // remove 123 - prefix
            .replace(/[^\u0600-\u06FFa-zA-Z0-9]/g, ' ')
            .replace(/\s+/g, ' ');
    }

    function extractPhoneFromEntity(obj) {
        if (!obj) return '';
        return cleanPhoneNumber(obj.whatsapp || obj.phone || obj.mobile || obj.tel || obj.phone1 || obj.contactNumber || obj.whatsappLid || '');
    }

    // ═══ Universal Party & Phone Resolver ═══
    function resolvePartyInfo(partyIdOrName) {
        const result = { name: '', phone: '', type: 'party', id: '', statementPin: '' };
        if (!partyIdOrName) return result;

        const rawStr = String(partyIdOrName).trim();
        const normQuery = normalizeArabic(rawStr).trim();

        function getStoredList(key) {
            let list = [];
            try { list = JSON.parse(localStorage.getItem(key)) || []; } catch(e){}
            if ((!list || list.length === 0) && typeof getTenantKey === 'function') {
                try { list = JSON.parse(localStorage.getItem(getTenantKey(key))) || []; } catch(e){}
            }
            return Array.isArray(list) ? list.filter(x => x && !x.deleted) : [];
        }

        const customers = getStoredList('erp_customers_v1');
        const suppliers = getStoredList('erp_suppliers_v1');

        // 1. Direct ID or statementPin or receiptCodeNum match
        let cleanId = rawStr.replace(/^cust-/, '').replace(/^supp-/, '').replace(/^#/, '').trim();
        if (rawStr.startsWith('cust-') || !rawStr.startsWith('supp-')) {
            const found = customers.find(c =>
                String(c.id) === cleanId ||
                String(c.id) === rawStr ||
                (c.statementPin && String(c.statementPin).trim() === cleanId) ||
                (c.receiptCodeNum && String(c.receiptCodeNum).trim() === cleanId)
            );
            if (found) {
                result.name = found.name;
                result.phone = extractPhoneFromEntity(found);
                result.type = 'customer';
                result.id = found.id;
                result.statementPin = found.statementPin || '';
                return result;
            }
        }
        if (rawStr.startsWith('supp-') || !result.phone) {
            const found = suppliers.find(s =>
                String(s.id) === cleanId ||
                String(s.id) === rawStr ||
                (s.statementPin && String(s.statementPin).trim() === cleanId)
            );
            if (found) {
                result.name = found.name;
                result.phone = extractPhoneFromEntity(found);
                result.type = 'supplier';
                result.id = found.id;
                result.statementPin = found.statementPin || '';
                return result;
            }
        }

        // 2. Name-based match (exact & token search)
        if (normQuery && normQuery !== 'all' && normQuery !== 'جميع الحسابات' && normQuery !== 'كشف حساب عام') {
            let found = customers.find(c => normalizeArabic(c.name) === normQuery);
            if (!found) {
                const qTokens = normQuery.split(' ').filter(t => t.length > 1);
                found = customers.find(c => {
                    const cNorm = normalizeArabic(c.name);
                    if (cNorm.includes(normQuery) || normQuery.includes(cNorm)) return true;
                    return qTokens.length > 0 && qTokens.every(tok => cNorm.includes(tok));
                });
            }
            if (found) {
                result.name = found.name;
                result.phone = extractPhoneFromEntity(found);
                result.type = 'customer';
                result.id = found.id;
                result.statementPin = found.statementPin || '';
                return result;
            }

            found = suppliers.find(s => normalizeArabic(s.name) === normQuery);
            if (!found) {
                const qTokens = normQuery.split(' ').filter(t => t.length > 1);
                found = suppliers.find(s => {
                    const sNorm = normalizeArabic(s.name);
                    if (sNorm.includes(normQuery) || normQuery.includes(sNorm)) return true;
                    return qTokens.length > 0 && qTokens.every(tok => sNorm.includes(tok));
                });
            }
            if (found) {
                result.name = found.name;
                result.phone = extractPhoneFromEntity(found);
                result.type = 'supplier';
                result.id = found.id;
                result.statementPin = found.statementPin || '';
                return result;
            }
        }

        return result;
    }

    // ═══ Persist Entered/Updated WhatsApp Phone to Customer/Supplier in MySQL & LocalStorage ═══
    async function savePartyPhoneToMasterData(contextData, newPhone) {
        if (!newPhone || !contextData) return;
        const cleanPh = cleanPhoneNumber(newPhone);
        if (!cleanPh) return;

        const getTenantKey = window.getTenantKey || ((k) => k);
        const rawPartyId = String(contextData.accountId || contextData.customerId || contextData.supplierId || contextData.partyId || '').trim();
        const rawPartyName = String(contextData.accountName || contextData.customerName || contextData.supplierName || contextData.recipientName || '').trim();
        const resolved = resolvePartyInfo(rawPartyId || rawPartyName);
        const targetType = resolved.type === 'supplier' || rawPartyId.startsWith('supp-') ? 'supplier' : 'customer';
        const storageKey = targetType === 'supplier' ? 'erp_suppliers_v1' : 'erp_customers_v1';
        const tenantStorageKey = getTenantKey(storageKey);

        let list = [];
        try {
            list = JSON.parse(localStorage.getItem(tenantStorageKey) || localStorage.getItem(storageKey) || '[]');
            if (!Array.isArray(list)) list = [];
        } catch (e) { list = []; }

        const cleanTargetId = String(resolved.id || rawPartyId).replace(/^(cust|supp)-/, '').trim();
        const normTargetName = normalizeArabic(resolved.name || rawPartyName);

        let idx = list.findIndex(item => item && !item.deleted && (
            (cleanTargetId && String(item.id) === cleanTargetId) ||
            (normTargetName && normalizeArabic(item.name) === normTargetName)
        ));

        if (idx > -1) {
            list[idx].whatsapp = cleanPh;
            if (!list[idx].phone) list[idx].phone = cleanPh;
            try {
                localStorage.setItem(tenantStorageKey, JSON.stringify(list));
                localStorage.setItem(storageKey, JSON.stringify(list));
            } catch (e) {}

            try {
                const tId = (window.SERVER_TENANT && window.SERVER_TENANT.id) || (JSON.parse(localStorage.getItem('user') || '{}').tenant_id) || '12';
                await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: targetType === 'supplier' ? 'save_supplier' : 'save_customer',
                        tenant_id: tId,
                        data: list[idx]
                    })
                });
            } catch (err) {
                console.warn('Auto-save party WhatsApp phone warning:', err);
            }
        }
    }

    // ═══ Get Auto-Send Settings ═══
    function getSalesAutoSend() {
        try {
            return JSON.parse(localStorage.getItem(KEY_SALES_AUTO_SEND)) || { enabled: false, mode: 'disabled' };
        } catch(e) {
            return { enabled: false, mode: 'disabled' };
        }
    }
    function saveSalesAutoSend(s) {
        localStorage.setItem(KEY_SALES_AUTO_SEND, JSON.stringify(s));
        syncSalesAutoSendUI();
    }

    function getPurchaseAutoSend() {
        try {
            return JSON.parse(localStorage.getItem(KEY_PURCHASE_AUTO_SEND)) || { enabled: false, mode: 'disabled' };
        } catch(e) {
            return { enabled: false, mode: 'disabled' };
        }
    }
    function savePurchaseAutoSend(s) {
        localStorage.setItem(KEY_PURCHASE_AUTO_SEND, JSON.stringify(s));
        syncPurchaseAutoSendUI();
    }

    // ═══ Sync UI for Sales Auto-Send ═══
    function syncSalesAutoSendUI() {
        const s = getSalesAutoSend();
        const toggleBtn = document.getElementById('btn-toggle-auto-send-sales');
        const optBtn = document.getElementById('btn-auto-send-options-sales');
        const icon = document.getElementById('auto-send-sales-icon');
        const pill = document.getElementById('auto-send-sales-status-pill');

        if (!toggleBtn) return;

        if (s.enabled && s.mode !== 'disabled') {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle-unit active';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options-unit active';
            if (icon) icon.innerHTML = '⚡';
            
            let label = 'مفعّل (إذن استلام)';
            if (s.mode === 'invoice_doc') label = 'مفعّل (فاتورة مصممة)';
            else if (s.mode === 'invoice') label = 'مفعّل (فاتورة وكشف)';
            else if (s.mode === 'delivery_order_text') label = 'مفعّل (إذن نصي)';
            else if (s.mode === 'delivery_order_doc') label = 'مفعّل (إذن رسمي)';

            if (pill) {
                pill.textContent = label;
                pill.style.background = '#10b981';
                pill.style.color = '#ffffff';
                pill.style.boxShadow = '0 0 10px rgba(16,185,129,0.5)';
            }
            toggleBtn.title = `الإرسال التلقائي مفعّل (${label}) - سيتم الإرسال فور الحفظ. انقر للتعطيل`;
        } else {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle-unit inactive';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options-unit inactive';
            if (icon) icon.innerHTML = '💾';
            if (pill) {
                pill.textContent = 'معطّل (حفظ فقط)';
                pill.style.background = 'rgba(148, 163, 184, 0.25)';
                pill.style.color = '#cbd5e1';
                pill.style.boxShadow = 'none';
            }
            toggleBtn.title = 'حفظ فقط (بدون إرسال) - انقر لتفعيل الإرسال التلقائي';
        }
    }

    // ═══ Sync UI for Purchase Auto-Send ═══
    function syncPurchaseAutoSendUI() {
        const s = getPurchaseAutoSend();
        const toggleBtn = document.getElementById('btn-toggle-auto-send-purchase');
        const optBtn = document.getElementById('btn-auto-send-options-purchase');
        const icon = document.getElementById('auto-send-purchase-icon');
        const pill = document.getElementById('auto-send-purchase-status-pill');

        if (!toggleBtn) return;

        if (s.enabled && s.mode !== 'disabled') {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle-unit active';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options-unit active';
            if (icon) icon.innerHTML = '⚡';
            
            let label = 'مفعّل (إذن توريد)';
            if (s.mode === 'invoice_doc') label = 'مفعّل (فاتورة شراء مصممة)';
            else if (s.mode === 'invoice') label = 'مفعّل (فاتورة الشراء)';
            else if (s.mode === 'receipt_order_text') label = 'مفعّل (إذن نصي)';
            else if (s.mode === 'receipt_order_doc') label = 'مفعّل (إذن رسمي)';

            if (pill) {
                pill.textContent = label;
                pill.style.background = '#10b981';
                pill.style.color = '#ffffff';
                pill.style.boxShadow = '0 0 10px rgba(16,185,129,0.5)';
            }
            toggleBtn.title = `الإرسال التلقائي مفعّل (${label}) - سيتم الإرسال للمورد فور الحفظ. انقر للتعطيل`;
        } else {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle-unit inactive';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options-unit inactive';
            if (icon) icon.innerHTML = '💾';
            if (pill) {
                pill.textContent = 'معطّل (حفظ فقط)';
                pill.style.background = 'rgba(148, 163, 184, 0.25)';
                pill.style.color = '#cbd5e1';
                pill.style.boxShadow = 'none';
            }
            toggleBtn.title = 'حفظ فقط (بدون إرسال) - انقر لتفعيل الإرسال التلقائي';
        }
    }

    // ═══ Modal DOM Management ═══
    let activeModalContext = null; // { screen: 'sales'|'purchase'|'ledger', data: {...}, currentTab: '...' }

    function initSuiteModal() {
        if (document.getElementById('erp-wa-modal-overlay')) return;

        const html = `
            <div id="erp-wa-modal-overlay" onclick="window.closeSuiteWAModal(event)">
                <div class="erp-wa-card" onclick="event.stopPropagation()">
                    <!-- Header -->
                    <div class="erp-wa-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" style="display:block;">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 0C5.373 0 0 5.373 0 12c0 2.115.552 4.103 1.516 5.832L.057 24l6.34-1.662A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z" fill="#25D366"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="#ffffff"/>
                            </svg>
                            <div>
                                <h3 id="erp-wa-title" style="margin:0; font-size:1.15rem; color:#f8fafc; font-weight:800;">إرسال المستند عبر واتساب</h3>
                                <span id="erp-wa-subtitle" style="font-size:0.75rem; color:#94a3b8;">إرسال معتمد وفوري للعملاء والموردين</span>
                            </div>
                        </div>
                        <button type="button" onclick="window.closeSuiteWAModal()" style="background:none; border:none; color:#94a3b8; font-size:1.4rem; cursor:pointer; padding:4px 8px; line-height:1; border-radius:6px;">✕</button>
                    </div>

                    <!-- Body -->
                    <div class="erp-wa-body">
                        <!-- Recipient Details Bar -->
                        <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 10px 14px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:1.1rem;">👤</span>
                                <div>
                                    <div style="font-size:0.75rem; color:#94a3b8;">المستلم / الطرف المعني:</div>
                                    <strong id="erp-wa-recipient-name" style="font-size:0.95rem; color:#38bdf8;">—</strong>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px; flex:1; max-width:280px;">
                                <span style="font-size:1.1rem;">📱</span>
                                <div style="width:100%;">
                                    <div style="font-size:0.75rem; color:#94a3b8;">رقم الواتساب:</div>
                                    <input type="text" id="erp-wa-phone-input" style="width:100%; height:32px; background:rgba(0,0,0,0.4); border:1px solid #334155; border-radius:6px; color:#fff; padding:0 8px; font-weight:bold; font-family:'JetBrains Mono', monospace; direction:ltr; text-align:right;" placeholder="249xxxxxxxxx">
                                </div>
                            </div>
                        </div>

                        <!-- Document Selector Tabs -->
                        <div>
                            <div style="font-size:0.8rem; font-weight:800; color:#cbd5e1; margin-bottom:6px;">نوع المستند المراد إرساله:</div>
                            <div id="erp-wa-tabs-container" style="display:flex; gap:8px; flex-wrap:wrap;">
                                <!-- Rendered dynamically based on context -->
                            </div>
                        </div>

                        <!-- Description Banner -->
                        <div id="erp-wa-mode-desc" style="font-size:0.78rem; padding:8px 12px; border-radius:8px; background:rgba(56, 189, 248, 0.1); border:1px solid rgba(56, 189, 248, 0.25); color:#7dd3fc;">
                            —
                        </div>

                        <!-- Live Preview Box -->
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <span style="font-size:0.8rem; font-weight:800; color:#cbd5e1;">معاينة المستند / الرسالة:</span>
                                <span id="erp-wa-char-count" style="font-size:0.72rem; color:#94a3b8;">—</span>
                            </div>
                            <div id="erp-wa-preview-text" class="erp-wa-preview-box">جاري التحضير...</div>
                            <div id="erp-wa-preview-doc-wrap" style="display:none; max-height:220px; overflow-y:auto; background:#fff; border-radius:8px; padding:10px; color:#000;">
                                <!-- Document mini preview -->
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="erp-wa-footer">
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <button type="button" class="btn" onclick="window.copySuiteWAMessage()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:10px; padding:8px 14px; font-weight:bold; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                <span>📋</span> نسخ النص
                            </button>
                            <button type="button" class="btn" onclick="window.shareActiveSuiteDocument()" style="background:linear-gradient(135deg, rgba(59,130,246,0.25), rgba(29,78,216,0.35)); border:1.5px solid #3b82f6; color:#93c5fd; border-radius:10px; padding:8px 14px; font-weight:bold; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px;" title="مشاركة المستند عبر مختلف التطبيقات (واتساب، إيميل، تيليجرام...)">
                                <span>🔗</span> مشاركة
                            </button>
                            <button type="button" id="erp-wa-btn-print-doc" class="btn" onclick="window.printSuiteWADocument()" style="display:none; background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8; border-radius:10px; padding:8px 14px; font-weight:bold; font-size:0.85rem; cursor:pointer; align-items:center; gap:6px;">
                                <span>🖨️</span> طباعة / PDF
                            </button>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button type="button" class="btn" onclick="window.executeSuiteWASend('web')" style="background:linear-gradient(135deg, #25d366, #128c7e); border:none; color:#fff; border-radius:10px; padding:9px 18px; font-weight:900; font-size:0.92rem; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 14px rgba(37,211,102,0.4);">
                                <span>🚀</span> إرسال عبر واتساب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
    }

    // ═══ Close Modal ═══
    window.closeSuiteWAModal = function(e) {
        if (e && e.target && e.target.id !== 'erp-wa-modal-overlay') return;
        const el = document.getElementById('erp-wa-modal-overlay');
        if (el) el.style.display = 'none';
        activeModalContext = null;
    };

    // ═══ Copy Message ═══
    window.copySuiteWAMessage = function() {
        const textEl = document.getElementById('erp-wa-preview-text');
        if (!textEl) return;
        const text = textEl.textContent || textEl.innerText;
        navigator.clipboard.writeText(text).then(() => {
            if (typeof showToast === 'function') {
                showToast('✅ تم نسخ نص الرسالة إلى الحافظة بنجاح', 'success');
            } else {
                alert('✅ تم نسخ نص الرسالة بنجاح');
            }
        });
    };

    // ═══ Share Document via Web Share API ═══
    window.shareActiveSuiteDocument = async function() {
        if (!activeModalContext) return;
        const textEl = document.getElementById('erp-wa-preview-text');
        const messageText = textEl ? (textEl.textContent || textEl.innerText) : '';
        const phoneInput = document.getElementById('erp-wa-phone-input');
        const phone = cleanPhoneNumber(phoneInput ? phoneInput.value : '');

        let title = 'مستند نظام السجانة المحاسبي';
        if (activeModalContext.screen === 'sales') {
            title = `مستند مبيعات #${activeModalContext.data?.invoiceNo || ''}`;
        } else if (activeModalContext.screen === 'purchase') {
            title = `مستند مشتريات #${activeModalContext.data?.invoiceNo || ''}`;
        } else if (activeModalContext.screen === 'ledger') {
            title = `كشف حساب - ${activeModalContext.data?.accountName || ''}`;
        }

        if (navigator.share) {
            try {
                await navigator.share({
                    title: title,
                    text: messageText
                });
                if (typeof showToast === 'function') {
                    showToast('✅ تمت المشاركة بنجاح عبر التطبيقات', 'success');
                }
                return;
            } catch(e) {
                if (e.name === 'AbortError') return;
            }
        }

        // Fallback: If phone present, trigger direct WhatsApp; else copy text
        if (phone) {
            window.executeSuiteWASend('web');
        } else {
            window.copySuiteWAMessage();
        }
    };

    // ═══ Print / PDF Document ═══
    window.printSuiteWADocument = function() {
        if (activeModalContext && activeModalContext.screen === 'ledger' && activeModalContext.currentTab === 'statement_pdf') {
            if (typeof window.openPreviewDesign === 'function') {
                window.openPreviewDesign();
                return;
            }
        }
        if (!activeModalContext || !activeModalContext.docHtml) return;
        const printWin = window.open('', '_blank');
        printWin.document.write(`
            <!DOCTYPE html>
            <html dir="rtl" lang="ar">
            <head>
                <meta charset="utf-8">
                <title>طباعة مستند رسمي</title>
                <style>
                    body { font-family: 'Tajawal', Arial, sans-serif; margin: 20px; background: #fff; color: #000; direction: rtl; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                ${activeModalContext.docHtml}
                <script>
                    window.onload = function() { window.print(); };
                </script>
            </body>
            </html>
        `);
        printWin.document.close();
    };

    // ═══ Execute WhatsApp Send (Auto-Save Phone to DB + Direct WA Service Delivery) ═══
    window.executeSuiteWASend = async function(method = 'direct') {
        if (!activeModalContext) return;

        const phoneInput = document.getElementById('erp-wa-phone-input');
        const phone = cleanPhoneNumber(phoneInput ? phoneInput.value : '');

        if (!phone) {
            if (typeof showToast === 'function') {
                showToast('⚠️ يرجى إدخال رقم الواتساب أولاً للمستلم ليتم حفظه والإرسال إليه', 'warning');
            } else {
                alert('⚠️ يرجى إدخال رقم الواتساب أولاً للمستلم');
            }
            if (phoneInput) phoneInput.focus();
            return;
        }

        const textEl = document.getElementById('erp-wa-preview-text');
        const messageText = textEl ? (textEl.textContent || textEl.innerText) : '';
        const ctxData = activeModalContext.data || {};

        // 1. Auto-save the entered/updated WhatsApp phone to Customer/Supplier in MySQL & LocalStorage
        savePartyPhoneToMasterData(ctxData, phone);

        // 2. Try Direct Background WhatsApp Service (wa_tenant_12) first unless explicitly forced to 'web_only'
        if (method !== 'web_only') {
            try {
                const userId = (typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12')));
                const formData = new FormData();
                formData.append('userId', userId);
                formData.append('phone', phone);
                formData.append('message', messageText);

                const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${encodeURIComponent(userId)}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data && data.success) {
                    if (typeof showToast === 'function') {
                        showToast(`⚡ تم حفظ رقم الواتساب (${phone}) وإرسال المستند مباشرة للعميل بنجاح!`, 'success', 6000);
                    }
                    window.closeSuiteWAModal();
                    return;
                }
            } catch (err) {
                console.warn('Direct WA send fallback to Web:', err);
            }
        }

        // 3. Fallback: Universal Direct WhatsApp Link (Web, Desktop & Mobile)
        const waUrl = `https://api.whatsapp.com/send?phone=${encodeURIComponent(phone.replace(/\D/g, ''))}&text=${encodeURIComponent(messageText)}`;
        window.open(waUrl, '_blank');

        if (typeof showToast === 'function') {
            showToast('🚀 تم حفظ الرقم وفتح محادثة الواتساب وتجهيز المستند بنجاح', 'success');
        }
        window.closeSuiteWAModal();
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 1. SALES SCREEN LOGIC (direct-sales.php)
    // ══════════════════════════════════════════════════════════════════════════

    window.toggleSalesAutoSendMode = function() {
        const s = getSalesAutoSend();
        s.enabled = !s.enabled;
        if (s.enabled && (!s.mode || s.mode === 'disabled')) {
            s.mode = 'delivery_order_doc'; // Default to official delivery order
        }
        saveSalesAutoSend(s);
    };

    window.toggleSalesAutoSendDropdown = function(e) {
        if (e) { e.stopPropagation(); e.preventDefault(); }
        const menu = document.getElementById('auto-send-sales-dropdown-menu');
        if (!menu) return;
        
        if (menu.parentElement !== document.body) {
            document.body.appendChild(menu);
            menu.style.position = 'fixed';
            menu.style.zIndex = '999999999';
        }
        
        if (menu.style.display !== 'block') {
            const btn = document.getElementById('btn-auto-send-options-sales');
            if (btn) {
                const rect = btn.getBoundingClientRect();
                menu.style.bottom = (window.innerHeight - rect.top + 5) + 'px';
                menu.style.left = (rect.left) + 'px';
            }
            menu.style.display = 'block';
        } else {
            menu.style.display = 'none';
        }
    };

    window.selectSalesAutoSendOption = function(mode) {
        const menu = document.getElementById('auto-send-sales-dropdown-menu');
        if (menu) menu.style.display = 'none';

        const s = getSalesAutoSend();
        s.mode = mode;
        s.enabled = (mode !== 'disabled');
        saveSalesAutoSend(s);

        if (typeof showToast === 'function') {
            const desc = (mode === 'disabled') ? 'تم إيقاف الإرسال التلقائي (حفظ فقط)' : 'تم ضبط وضع الإرسال التلقائي بنجاح ⚡';
            showToast(desc, s.enabled ? 'success' : 'info');
        }
    };

    // Open Sales WhatsApp Modal
    window.openSalesWhatsAppModal = function(invoiceNo = null, invoiceData = null) {
        initSuiteStyles();
        initSuiteModal();

        // 1. Resolve Invoice Data
        let inv = invoiceData;
        if (!inv && typeof window.getSalesInvoiceDataForWA === 'function') {
            try { inv = window.getSalesInvoiceDataForWA(invoiceNo); } catch(e){}
        }
        if (!inv && invoiceNo) {
            try { inv = JSON.parse(localStorage.getItem('erp_inv_' + invoiceNo)); } catch(e){}
            if (!inv) {
                let allSales = [];
                try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []; } catch(e){}
                inv = allSales.find(i => String(i.invoiceNo) === String(invoiceNo));
            }
        }

        // If no invoice found from storage, read from active form
        if (!inv) {
            const invNoVal = document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || 'SAL-NEW';
            const custId = document.getElementById('hdr-customer')?.value || '';
            const custName = document.getElementById('hdr-customer-search')?.value || 'عميل نقدي';
            const rowsData = (window.rows && Array.isArray(window.rows)) ? window.rows : [];
            const grandTotal = document.getElementById('t-grand-dynamic-loc')?.textContent || 
                               document.getElementById('t-grand')?.textContent || 
                               document.getElementById('t-grand-local')?.textContent || '0.00';
            const cur = (typeof window.getCurrency === 'function') ? window.getCurrency() : (document.getElementById('hdr-currency')?.value || 'SDG');

            inv = {
                invoiceNo: invNoVal,
                date: document.getElementById('hdr-date')?.value || new Date().toISOString().split('T')[0],
                customer: custId,
                customerName: custName,
                currency: cur,
                items: rowsData,
                grandTotal: grandTotal,
                notes: document.getElementById('inv-notes')?.value || ''
            };
        }

        // Resolve customer details & phone
        let custPhone = inv.customerPhone || '';
        let custName = inv.customerName || 'عميل نقدي';
        if (!custPhone || !custName || custName === 'عميل نقدي') {
            const info = resolvePartyInfo(inv.customer || custName);
            if (info) {
                if (info.phone && !custPhone) custPhone = info.phone;
                if (info.name && (!custName || custName === 'عميل نقدي')) custName = info.name;
            }
        }
        if (!custPhone) {
            const searchVal = document.getElementById('hdr-customer-search')?.value;
            if (searchVal) {
                const info = resolvePartyInfo(searchVal);
                if (info && info.phone) custPhone = info.phone;
            }
        }

        activeModalContext = {
            screen: 'sales',
            data: inv,
            customerPhone: custPhone,
            customerName: custName,
            currentTab: 'delivery_order_doc'
        };

        // Populate Modal UI
        document.getElementById('erp-wa-title').textContent = 'إرسال مستندات المبيعات عبر واتساب';
        document.getElementById('erp-wa-subtitle').textContent = `فاتورة رقم #${inv.invoiceNo} | ${custName}`;
        document.getElementById('erp-wa-recipient-name').textContent = custName;
        document.getElementById('erp-wa-phone-input').value = custPhone;

        // Render Tabs
        const tabsContainer = document.getElementById('erp-wa-tabs-container');
        tabsContainer.innerHTML = `
            <button type="button" class="erp-wa-tab-btn active" data-tab="delivery_order_doc" id="tab-sales-do-doc" onclick="window.switchSuiteWATab('delivery_order_doc')">
                <span>📄</span> إذن استلام رسمي (كميات فقط)
            </button>
            <button type="button" class="erp-wa-tab-btn" data-tab="delivery_order_text" id="tab-sales-do-txt" onclick="window.switchSuiteWATab('delivery_order_text')">
                <span>📝</span> إذن استلام نصي كمعلومة
            </button>
            <button type="button" class="erp-wa-tab-btn" data-tab="invoice_doc" id="tab-sales-inv-doc" onclick="window.switchSuiteWATab('invoice_doc')">
                <span>🧾</span> فاتورة مبيعات مصممة (رسمي)
            </button>
            <button type="button" class="erp-wa-tab-btn" data-tab="invoice" id="tab-sales-inv" onclick="window.switchSuiteWATab('invoice')">
                <span>💰</span> نص الفاتورة وكشف الحساب
            </button>
        `;

        window.switchSuiteWATab('delivery_order_doc');
        document.getElementById('erp-wa-modal-overlay').style.display = 'flex';
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 2. PURCHASES SCREEN LOGIC (purchase.php)
    // ══════════════════════════════════════════════════════════════════════════

    window.togglePurchaseAutoSendMode = function() {
        const s = getPurchaseAutoSend();
        s.enabled = !s.enabled;
        if (s.enabled && (!s.mode || s.mode === 'disabled')) {
            s.mode = 'receipt_order_doc'; // Default to official goods receipt order
        }
        savePurchaseAutoSend(s);
    };

    window.togglePurchaseAutoSendDropdown = function(e) {
        if (e) { e.stopPropagation(); e.preventDefault(); }
        const menu = document.getElementById('auto-send-purchase-dropdown-menu');
        if (!menu) return;
        
        if (menu.parentElement !== document.body) {
            document.body.appendChild(menu);
            menu.style.position = 'fixed';
            menu.style.zIndex = '999999999';
        }
        
        if (menu.style.display !== 'block') {
            const btn = document.getElementById('btn-auto-send-options-purchase');
            if (btn) {
                const rect = btn.getBoundingClientRect();
                menu.style.bottom = (window.innerHeight - rect.top + 5) + 'px';
                menu.style.left = (rect.left) + 'px';
            }
            menu.style.display = 'block';
        } else {
            menu.style.display = 'none';
        }
    };

    window.selectPurchaseAutoSendOption = function(mode) {
        const menu = document.getElementById('auto-send-purchase-dropdown-menu');
        if (menu) menu.style.display = 'none';

        const s = getPurchaseAutoSend();
        s.mode = mode;
        s.enabled = (mode !== 'disabled');
        savePurchaseAutoSend(s);

        if (typeof showToast === 'function') {
            const desc = (mode === 'disabled') ? 'تم إيقاف الإرسال التلقائي للمشتريات' : 'تم تفعيل وضع الإرسال التلقائي للمورد بنجاح ⚡';
            showToast(desc, s.enabled ? 'success' : 'info');
        }
    };

    // Open Purchase WhatsApp Modal
    window.openPurchaseWhatsAppModal = function(invoiceNo = null, invoiceData = null) {
        initSuiteStyles();
        initSuiteModal();

        let inv = invoiceData;
        if (!inv && typeof window.getPurchaseInvoiceDataForWA === 'function') {
            try { inv = window.getPurchaseInvoiceDataForWA(invoiceNo); } catch(e){}
        }
        if (!inv && invoiceNo) {
            try { inv = JSON.parse(localStorage.getItem('erp_pur_' + invoiceNo)); } catch(e){}
            if (!inv) {
                let allPurchases = [];
                try { allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []; } catch(e){}
                inv = allPurchases.find(i => String(i.invoiceNo) === String(invoiceNo));
            }
        }

        if (!inv) {
            const invNoVal = document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || 'PUR-NEW';
            const suppId = document.getElementById('hdr-customer')?.value || document.getElementById('hdr-supplier')?.value || '';
            const suppName = document.getElementById('hdr-customer-search')?.value || document.getElementById('hdr-supplier-search')?.value || 'مورد عام';
            const rowsData = (window.rows && Array.isArray(window.rows)) ? window.rows : [];
            const grandTotal = document.getElementById('t-grand-dynamic-loc')?.textContent || 
                               document.getElementById('t-grand-local')?.textContent || 
                               document.getElementById('t-grand')?.textContent || '0.00';
            const cur = (typeof window.getCurrency === 'function') ? window.getCurrency() : (document.getElementById('hdr-currency')?.value || 'SDG');

            inv = {
                invoiceNo: invNoVal,
                date: document.getElementById('hdr-date')?.value || new Date().toISOString().split('T')[0],
                supplier: suppId,
                supplierName: suppName,
                currency: cur,
                items: rowsData,
                grandTotal: grandTotal,
                notes: document.getElementById('inv-notes')?.value || ''
            };
        }

        let suppPhone = inv.supplierPhone || '';
        let suppName = inv.supplierName || 'مورد عام';
        if (!suppPhone || !suppName || suppName === 'مورد عام') {
            const info = resolvePartyInfo(inv.supplier || suppName);
            if (info) {
                if (info.phone && !suppPhone) suppPhone = info.phone;
                if (info.name && (!suppName || suppName === 'مورد عام')) suppName = info.name;
            }
        }
        if (!suppPhone) {
            const searchVal = document.getElementById('hdr-supplier-search')?.value || document.getElementById('hdr-customer-search')?.value;
            if (searchVal) {
                const info = resolvePartyInfo(searchVal);
                if (info && info.phone) suppPhone = info.phone;
            }
        }

        activeModalContext = {
            screen: 'purchase',
            data: inv,
            supplierPhone: suppPhone,
            supplierName: suppName,
            currentTab: 'receipt_order_doc'
        };

        // Populate Modal UI
        document.getElementById('erp-wa-title').textContent = 'إرسال مستندات المشتريات والتوريد عبر واتساب';
        document.getElementById('erp-wa-subtitle').textContent = `فاتورة شراء #${inv.invoiceNo} | المورد: ${suppName}`;
        document.getElementById('erp-wa-recipient-name').textContent = suppName;
        document.getElementById('erp-wa-phone-input').value = suppPhone;

        // Render Tabs
        const tabsContainer = document.getElementById('erp-wa-tabs-container');
        tabsContainer.innerHTML = `
            <button type="button" class="erp-wa-tab-btn active" data-tab="receipt_order_doc" id="tab-purchase-ro-doc" onclick="window.switchSuiteWATab('receipt_order_doc')">
                <span>📄</span> إذن توريد رسمي (كميات فقط)
            </button>
            <button type="button" class="erp-wa-tab-btn" data-tab="receipt_order_text" id="tab-purchase-ro-txt" onclick="window.switchSuiteWATab('receipt_order_text')">
                <span>📝</span> إذن توريد نصي كمعلومة
            </button>
            <button type="button" class="erp-wa-tab-btn" data-tab="invoice_doc" id="tab-purchase-inv-doc" onclick="window.switchSuiteWATab('invoice_doc')">
                <span>🧾</span> فاتورة شراء مصممة (رسمي)
            </button>
            <button type="button" class="erp-wa-tab-btn" data-tab="invoice" id="tab-purchase-inv" onclick="window.switchSuiteWATab('invoice')">
                <span>💰</span> نص فاتورة الشراء وحساب المورد
            </button>
        `;

        window.switchSuiteWATab('receipt_order_doc');
        document.getElementById('erp-wa-modal-overlay').style.display = 'flex';
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 3. GENERAL LEDGER / STATEMENT LOGIC (general-ledger.php)
    // ══════════════════════════════════════════════════════════════════════════

    window.openLedgerWhatsAppModal = function(paramAccountName = null, paramPhone = null) {
        initSuiteStyles();
        initSuiteModal();

        const accountId = document.getElementById('filter-account')?.value || 'ALL';
        let accountName = paramAccountName || document.getElementById('chip-name')?.innerText || '';
        if (!accountName || accountName === 'جميع الحسابات') {
            const inputVal = document.getElementById('account-search-input')?.value || '';
            if (inputVal && !inputVal.includes('Enter')) {
                accountName = inputVal.trim();
            }
        }
        if (!accountName) accountName = 'كشف حساب عام';

        const dateFrom = document.getElementById('filter-date-from')?.value || 'البداية';
        const dateTo = document.getElementById('filter-date-to')?.value || 'الآن';

        const totalDebit = document.getElementById('launcher-total-debit')?.innerText || '0.00';
        const totalCredit = document.getElementById('launcher-total-credit')?.innerText || '0.00';
        const totalBalance = document.getElementById('launcher-total-balance')?.innerText || '0.00';

        // Detect Phone Number
        let partyPhone = paramPhone || '';
        if (!partyPhone) {
            // First check by accountId if present
            if (accountId && accountId !== 'ALL') {
                const info = resolvePartyInfo(accountId);
                if (info && info.phone) {
                    partyPhone = info.phone;
                    if (info.name && (!accountName || accountName === 'كشف حساب عام')) accountName = info.name;
                }
            }
            // If still no phone, check by accountName
            if (!partyPhone && accountName && accountName !== 'كشف حساب عام') {
                const info = resolvePartyInfo(accountName);
                if (info && info.phone) {
                    partyPhone = info.phone;
                    if (info.name) accountName = info.name;
                }
            }
            // If still no phone, check search input
            if (!partyPhone) {
                const searchVal = document.getElementById('account-search-input')?.value || '';
                if (searchVal && searchVal.length > 2) {
                    const info = resolvePartyInfo(searchVal);
                    if (info && info.phone) {
                        partyPhone = info.phone;
                        if (info.name && accountName === 'كشف حساب عام') accountName = info.name;
                    }
                }
            }
        }

        activeModalContext = {
            screen: 'ledger',
            data: {
                accountId,
                accountName,
                dateFrom,
                dateTo,
                totalDebit,
                totalCredit,
                totalBalance
            },
            currentTab: 'statement_text'
        };

        // Populate Modal UI
        document.getElementById('erp-wa-title').textContent = 'إرسال كشف الحساب عبر واتساب';
        document.getElementById('erp-wa-subtitle').textContent = `كشف حساب: ${accountName} | الفترة: ${dateFrom} إلى ${dateTo}`;
        document.getElementById('erp-wa-recipient-name').textContent = accountName;
        document.getElementById('erp-wa-phone-input').value = partyPhone;

        // Render Tabs
        const tabsContainer = document.getElementById('erp-wa-tabs-container');
        tabsContainer.innerHTML = `
            <button type="button" class="erp-wa-tab-btn active" data-tab="statement_text" id="tab-ledger-txt" onclick="window.switchSuiteWATab('statement_text')">
                <span>📝</span> ملخص كشف الحساب كنص سريع
            </button>
            <button type="button" class="erp-wa-tab-btn" data-tab="statement_pdf" id="tab-ledger-pdf" onclick="window.switchSuiteWATab('statement_pdf')">
                <span>📄</span> كشف حساب معتمد (تصميم رسمي / PDF)
            </button>
        `;

        window.switchSuiteWATab('statement_text');
        document.getElementById('erp-wa-modal-overlay').style.display = 'flex';
    };

    // ══════════════════════════════════════════════════════════════════════════
    // Switch Modal Tabs & Render Dynamic Content
    // ══════════════════════════════════════════════════════════════════════════

    window.switchSuiteWATab = function(tabName) {
        if (!activeModalContext) return;
        activeModalContext.currentTab = tabName;

        // Update active class on tab buttons safely
        document.querySelectorAll('.erp-wa-tab-btn').forEach(btn => {
            const btnTab = btn.getAttribute('data-tab');
            const btnOnclick = btn.getAttribute('onclick') || '';
            if (btnTab === tabName || btnOnclick.includes(`'${tabName}'`)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        const descEl = document.getElementById('erp-wa-mode-desc');
        const textEl = document.getElementById('erp-wa-preview-text');
        const docWrap = document.getElementById('erp-wa-preview-doc-wrap');
        const btnPrint = document.getElementById('erp-wa-btn-print-doc');
        const charCountEl = document.getElementById('erp-wa-char-count');

        // Reset visibility
        textEl.style.display = 'block';
        docWrap.style.display = 'none';
        if (btnPrint) {
            btnPrint.style.display = 'none';
            btnPrint.innerHTML = '<span>🖨️</span> طباعة / PDF';
        }

        if (activeModalContext.screen === 'sales') {
            const inv = activeModalContext.data;
            const items = inv.items || [];

            if (tabName === 'delivery_order_doc') {
                descEl.innerHTML = '📄 <strong>إذن استلام وتسليم رسمي معتمد:</strong> يحتوي على الأصناف والكميات فقط (بدون أسعار ومبالغ مالية)، مخصص للمستودع والسائق والتوقيعات الرسمية.';
                const docHtml = generateDeliveryOrderHTML(inv, 'sales');
                activeModalContext.docHtml = docHtml;

                // Show visual doc preview
                docWrap.innerHTML = docHtml;
                docWrap.style.display = 'block';
                if (btnPrint) btnPrint.style.display = 'inline-flex';

                const isAmended = !!(inv.isAmended || inv.amendmentCount);

                // Accompanying WhatsApp text
                const waText = isAmended
                    ? `*⚠️ إشعار تعديل إذن استلام وتسليم بضاعة* 📄\n` +
                      `*(نسخة معدّلة - تعديل رقم ${inv.amendmentCount || 1})*\n` +
                      `*رقم الإذن:* #DO-${inv.invoiceNo}\n` +
                      `*العميل الكريم:* ${activeModalContext.customerName}\n` +
                      `*التاريخ:* ${inv.date}\n\n` +
                      `نحيطكم علماً بأنه تم إجراء تعديل رسمي على إذن الاستلام والتسليم.\n` +
                      `مرفق طيه النسخة المعدلة للاعتماد والاستلام بموجبها.\n` +
                      `شكراً لحسن تعاونكم معنا 🌟`
                    : `*إذن استلام وتسليم بضاعة رسمي* 📄\n` +
                      `*رقم الإذن:* #DO-${inv.invoiceNo}\n` +
                      `*العميل:* ${activeModalContext.customerName}\n` +
                      `*التاريخ:* ${inv.date}\n\n` +
                      `مرفق طيه إذن الاستلام والتسليم المعتمد للأصناف والكميات المسلمة.\n` +
                      `يرجى مراجعة وتوقيع الإذن عند استلام الشحنة.\n` +
                      `شكراً لتعاملكم معنا 🌟`;
                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'delivery_order_text') {
                descEl.innerHTML = '📝 <strong>إذن استلام كتابة كمعلومة:</strong> رسالة نصية تفصيلية سريعة بالكميات والأسعار (مثال: اشترى 4 طن بسعر كذا، بإجمالي كذا...).';
                
                const isAmended = !!(inv.isAmended || inv.amendmentCount);
                let itemLines = (items && items.length > 0) ? items.map((it, idx) => {
                    const name = it.name || it.itemName || 'صنف';
                    const qty = it.quantity || it.qty || 1;
                    const unit = it.unit || 'وحدة';
                    const price = it.price || it.unitPrice || 0;
                    const total = it.total || (qty * price);
                    return `${idx + 1}. *${name}* ← الكمية: ${qty} ${unit} | السعر: ${price} ${inv.currency || 'SDG'} (الإجمالي: ${total})`;
                }).join('\n') : '• (لا توجد أصناف مدخلة بعد)';

                const waText = isAmended
                    ? `*⚠️ إشعار تعديل إذن استلام بضاعة* 📋\n` +
                      `*(نسخة معدّلة - تعديل رقم ${inv.amendmentCount || 1})*\n` +
                      `*رقم الفاتورة/الإذن:* #${inv.invoiceNo}\n` +
                      `*التاريخ:* ${inv.date}\n` +
                      `*السيد/السادة:* ${activeModalContext.customerName}\n\n` +
                      `نحيطكم علماً بأنه تم تعديل إذن الاستلام والتسليم كالتالي:\n` +
                      `${itemLines}\n\n` +
                      `──────────────────\n` +
                      `*صافي القيمة بعد التعديل:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n` +
                      (inv.notes ? `*ملاحظات التسليم:* ${inv.notes}\n` : '') +
                      `نرجو اعتماد هذه النسخة المعدلة. شاكرين ومقدرين حسن تعاملكم معنا 🌟`
                    : `*إشعار صدور إذن استلام بضاعة* 📋\n` +
                      `*رقم الفاتورة/الإذن:* #${inv.invoiceNo}\n` +
                      `*التاريخ:* ${inv.date}\n` +
                      `*السيد/السادة:* ${activeModalContext.customerName}\n\n` +
                      `نحيطكم علماً بصدور إذن استلام بضاعة بالبيانات التالية:\n` +
                      `${itemLines}\n\n` +
                      `──────────────────\n` +
                      `*صافي القيمة الإجمالية:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n` +
                      (inv.notes ? `*ملاحظات التسليم:* ${inv.notes}\n` : '') +
                      `شاكرين ومقدرين حسن تعاملكم معنا 🌟`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'invoice_doc') {
                descEl.innerHTML = '🧾 <strong>فاتورة مبيعات مصممة (تصميم رسمي معتمد):</strong> مستند مالي كامل بكافة البيانات، الأصناف، الأسعار، الضرائب، الصافي والتوقيعات الرسمية، جاهز للطباعة والتصدير والإرسال.';
                const docHtml = generateInvoiceHTML(inv, 'sales');
                activeModalContext.docHtml = docHtml;

                docWrap.innerHTML = docHtml;
                docWrap.style.display = 'block';
                if (btnPrint) btnPrint.style.display = 'inline-flex';

                const isAmended = !!(inv.isAmended || inv.amendmentCount);
                const waText = isAmended
                    ? `*⚠️ إشعار تعديل فاتورة مبيعات معتمدة* 🧾\n` +
                      `*(نسخة معدّلة - تعديل رقم ${inv.amendmentCount || 1})*\n` +
                      `*رقم الفاتورة:* #${inv.invoiceNo}\n` +
                      `*التاريخ:* ${inv.date}\n` +
                      `*العميل الكريم:* ${activeModalContext.customerName}\n` +
                      `*إجمالي الفاتورة بعد التعديل:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n\n` +
                      `نحيطكم علماً بأنه تم إجراء تعديل معتمد على بنود وتفاصيل الفاتورة رقم #${inv.invoiceNo}.\n` +
                      `مرفق طيه التصميم الرسمي المعتمد للفاتورة بعد التعديل للاعتماد والعمل بموجبها.\n` +
                      `شاكرين لكم حسن تعاونكم وتفهمكم ✨`
                    : `*فاتورة مبيعات معتمدة مصممة* 🧾\n` +
                      `*رقم الفاتورة:* #${inv.invoiceNo}\n` +
                      `*التاريخ:* ${inv.date}\n` +
                      `*العميل الكريم:* ${activeModalContext.customerName}\n` +
                      `*إجمالي الفاتورة:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n\n` +
                      `مرفق طيه التصميم الرسمي المعتمد للفاتورة بكافة التفاصيل والبنود.\n` +
                      `شاكرين لكم حسن تعاملكم معنا ✨`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'invoice') {
                descEl.innerHTML = '💰 <strong>فاتورة المبيعات وكشف الحساب:</strong> تفاصيل مالية شاملة للأصناف، الكميات، الأسعار، الضرائب، الأرصدة، وطريقة الدفع.';
                
                const isAmended = !!(inv.isAmended || inv.amendmentCount);
                let itemLines = (items && items.length > 0) ? items.map((it, idx) => {
                    const name = it.name || it.itemName || 'صنف';
                    const qty = it.quantity || it.qty || 1;
                    const unit = it.unit || 'وحدة';
                    const price = it.price || it.unitPrice || 0;
                    const total = it.total || (qty * price);
                    return `• ${name}: ${qty} ${unit} × ${price} = *${total} ${inv.currency || 'SDG'}*`;
                }).join('\n') : '• (لا توجد أصناف مدخلة بعد)';

                const waText = isAmended
                    ? `*⚠️ إشعار تعديل فاتورة مبيعات وكشف حساب رسمي* 🧾\n` +
                      `*(نسخة معدّلة - تعديل رقم ${inv.amendmentCount || 1})*\n` +
                      `*رقم الفاتورة:* #${inv.invoiceNo}\n` +
                      `*التاريخ:* ${inv.date}\n` +
                      `*العميل:* ${activeModalContext.customerName}\n\n` +
                      `*تفاصيل الفاتورة بعد التعديل:* \n` +
                      `${itemLines}\n\n` +
                      `──────────────────\n` +
                      `*المبلغ الإجمالي بعد التعديل:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n` +
                      `*طريقة السداد:* ${inv.paymentMethod === 'credit' ? 'آجل' : 'نقدي / بنكي'}\n` +
                      `──────────────────\n` +
                      `نرجو اعتماد هذه النسخة المعدلة. شاكرين لكم ثقتكم الدائمة بنا ✨`
                    : `*فاتورة مبيعات وكشف حساب رسمي* 🧾\n` +
                      `*رقم الفاتورة:* #${inv.invoiceNo}\n` +
                      `*التاريخ:* ${inv.date}\n` +
                      `*العميل:* ${activeModalContext.customerName}\n\n` +
                      `*تفاصيل الفاتورة:* \n` +
                      `${itemLines}\n\n` +
                      `──────────────────\n` +
                      `*المبلغ الإجمالي الصافي:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n` +
                      `*طريقة السداد:* ${inv.paymentMethod === 'credit' ? 'آجل' : 'نقدي / بنكي'}\n` +
                      `──────────────────\n` +
                      `شاكرين لكم ثقتكم الدائمة بنا ✨`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            }
        } else if (activeModalContext.screen === 'purchase') {
            const inv = activeModalContext.data;
            const items = inv.items || [];

            if (tabName === 'receipt_order_doc') {
                descEl.innerHTML = '📄 <strong>إذن توريد واستلام مخزني رسمي:</strong> يحتوي على الأصناف والكميات فقط (بدون أسعار)، مخصص لأمين المخزن وتأكيد استلام البضاعة.';
                const docHtml = generateDeliveryOrderHTML(inv, 'purchase');
                activeModalContext.docHtml = docHtml;

                docWrap.innerHTML = docHtml;
                docWrap.style.display = 'block';
                if (btnPrint) btnPrint.style.display = 'inline-flex';

                const waText = `*إذن توريد واستلام مخزني رسمي* 📦\n` +
                    `*رقم الإذن:* #GRN-${inv.invoiceNo}\n` +
                    `*المورد:* ${activeModalContext.supplierName}\n` +
                    `*التاريخ:* ${inv.date}\n\n` +
                    `مرفق طيه إذن الاستلام والتوريد المخزني بالكميات الموردة.\n` +
                    `شاكرين لكم التزامكم وحسن تعاونكم 🌟`;
                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'receipt_order_text') {
                descEl.innerHTML = '📝 <strong>إذن توريد كتابة كمعلومة:</strong> رسالة نصية سريعة للمورد بتفاصيل الكميات والأسعار المعتمدة.';
                let itemLines = (items && items.length > 0) ? items.map((it, idx) => {
                    const name = it.name || it.itemName || 'صنف';
                    const qty = it.quantity || it.qty || 1;
                    const unit = it.unit || 'وحدة';
                    const price = it.price || it.unitPrice || 0;
                    return `${idx + 1}. *${name}* ← الكمية: ${qty} ${unit} | السعر: ${price}`;
                }).join('\n') : '• (لا توجد أصناف مدخلة بعد)';

                const waText = `*إشعار اعتماد توريد واستلام بضاعة* 📦\n` +
                    `*رقم الفاتورة:* #${inv.invoiceNo}\n` +
                    `*التاريخ:* ${inv.date}\n` +
                    `*المورد الكريم:* ${activeModalContext.supplierName}\n\n` +
                    `تم اعتماد استلام وتوريد الأصناف التالية في المستودع:\n` +
                    `${itemLines}\n\n` +
                    `──────────────────\n` +
                    `*إجمالي القيمة:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n` +
                    `شاكرين لكم حسن تعاونكم الدائم 🌟`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'invoice_doc') {
                descEl.innerHTML = '🧾 <strong>فاتورة شراء مصممة (تصميم رسمي معتمد):</strong> مستند مالي كامل بكافة الأصناف وأسعار الشراء وإجمالي المبالغ المقيدة لحساب المورد.';
                const docHtml = generateInvoiceHTML(inv, 'purchase');
                activeModalContext.docHtml = docHtml;

                docWrap.innerHTML = docHtml;
                docWrap.style.display = 'block';
                if (btnPrint) btnPrint.style.display = 'inline-flex';

                const waText = `*فاتورة شراء معتمدة مصممة* 🧾\n` +
                    `*رقم الفاتورة:* #${inv.invoiceNo}\n` +
                    `*التاريخ:* ${inv.date}\n` +
                    `*المورد:* ${activeModalContext.supplierName}\n` +
                    `*صافي القيمة:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n\n` +
                    `مرفق طيه فاتورة الشراء المعتمدة مصممة بالمبالغ المقيدة لحسابكم.\n` +
                    `مع تحيات الإدارة المالية 🏢`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'invoice') {
                descEl.innerHTML = '💰 <strong>فاتورة الشراء وحساب المورد:</strong> تفاصيل الفاتورة المالية والمبالغ المقيدة لحساب المورد.';
                const waText = `*فاتورة شراء وحساب مورد* 💼\n` +
                    `*رقم الفاتورة:* #${inv.invoiceNo}\n` +
                    `*التاريخ:* ${inv.date}\n` +
                    `*المورد:* ${activeModalContext.supplierName}\n\n` +
                    `*صافي إجمالي الفاتورة المقيد:* ${inv.grandTotal} ${inv.currency || 'SDG'}\n` +
                    `──────────────────\n` +
                    `تم قيد وتسجيل الفاتورة في النظام المحاسبي.\n` +
                    `مع تحيات الإدارة المالية 🏢`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            }
        } else if (activeModalContext.screen === 'ledger') {
            const d = activeModalContext.data;
            if (tabName === 'statement_text') {
                descEl.innerHTML = '📝 <strong>كشف الحساب المختصر المعتمد:</strong> صيغة كشف الحساب المباشرة للواتساب مع تمايز ألوان الجنيه السوداني (🟡)، الدولار (💵)، والرصيد التراكمي (🔷/🔶).';
                
                const sysRate = parseFloat(localStorage.getItem('erp_exchange_rate_usd') || window.systemExchangeRate || 6000) || 6000;
                const nowStr = new Date().toISOString().slice(0, 16).replace('T', ' ');
                const periodLbl = (d.dateFrom === 'البداية' && d.dateTo === 'الآن') ? 'كامل الفترة' : `من ${d.dateFrom} إلى ${d.dateTo}`;

                const fmtBalTag = (netVal, netUsdOverride) => {
                    const absSdg = Math.round(Math.abs(netVal)).toLocaleString('en-US');
                    const usdVal = (netUsdOverride !== undefined && netUsdOverride !== null) ? Math.abs(netUsdOverride) : (sysRate > 0 ? Math.abs(netVal) / sysRate : 0);
                    const usdStr = '$' + usdVal.toFixed(1) + ' USD';
                    if (netVal > 0.009) return `🔶 *${absSdg} ج.س  〘 \`${usdStr}\` 〙 (مدين عليه)*`;
                    if (netVal < -0.009) return `🔷 *${absSdg} ج.س  〘 \`${usdStr}\` 〙 (دائن له)*`;
                    return `⚪ *0 ج.س  〘 \`$0.0 USD\` 〙 (مسدد / متوازن)*`;
                };

                let movLines = [];
                let runNet = 0;
                let runNetUsd = 0;
                const txList = (window.currentTxns && Array.isArray(window.currentTxns)) ? window.currentTxns : ((window.opener && Array.isArray(window.opener.currentTxns)) ? window.opener.currentTxns : []);
                if (txList.length > 0) {
                    txList.forEach((t, idx) => {
                        const deb = parseFloat(t.debit) || 0;
                        const crd = parseFloat(t.credit) || 0;
                        const txRate = parseFloat(t.exchangeRate || t.rate || sysRate) || sysRate;
                        const debUsd = parseFloat(t.debitForeign) || (deb > 0 ? deb / txRate : 0);
                        const crdUsd = parseFloat(t.creditForeign) || (crd > 0 ? crd / txRate : 0);
                        runNet += (deb - crd);
                        runNetUsd += (debUsd - crdUsd);
                        const icon = deb > 0 ? '🔴' : '🟢';
                        const rateDiff = Math.abs(txRate - sysRate) >= 1 ? ` | الصرف: ${Math.round(txRate)} ج.س` : '';
                        const amtLine = deb > 0
                            ? `مدين (عليه): 🟡 *${Math.round(deb).toLocaleString('en-US')} ج.س* 〘 💵 \`$${debUsd.toFixed(1)}\`${rateDiff} 〙`
                            : `دائن (له): 🟡 *${Math.round(crd).toLocaleString('en-US')} ج.س* 〘 💵 \`$${crdUsd.toFixed(1)}\`${rateDiff} 〙`;
                        const stmt = t.statement || t.desc || '-';
                        let blk = `${icon} *(${idx + 1}) ${t.date || '-'} |*\n   • ${amtLine}\n   • البيان: ${stmt}\n`;
                        if (t.transferNo || t.bankName) {
                            const det = [];
                            if (t.bankName) det.push(`البنك: ${t.bankName}`);
                            if (t.transferNo) det.push(`رقم العملية: ${t.transferNo}`);
                            blk += `   • التفاصيل: ${det.join(' | ')}\n`;
                        }
                        blk += `   • الرصيد التراكمي: ${fmtBalTag(runNet, runNetUsd)}`;
                        movLines.push(blk);
                    });
                }

                const balNum = parseFloat(String(d.totalBalance).replace(/,/g, '')) || runNet;
                const waText = `🏛️ *مـنـظـومـة مـقـايـيـس الـمـحـاسـبـيـة الـعـالـمـيـة*\n` +
                    `══════════\n` +
                    `   📊 *كَـشْـف حِـسَـاب*\n` +
                    `${nowStr}\n` +
                    ` ${periodLbl}\n` +
                    `الصرف: ${Math.round(sysRate)}  ج س \n` +
                    ` *الاسم:* *${d.accountName}*\n` +
                    `  \n` +
                    `🏁 *الرصيد الافتتاحي:* 🟡 0 ج.س 〘 💵 \`$0.0 USD\` 〙\n` +
                    (movLines.length > 0 ? movLines.join('\n') + '\n' : '') +
                    `══════════\n` +
                    `• الرصيد الختامي: ${fmtBalTag(balNum, runNetUsd || (sysRate > 0 ? balNum / sysRate : 0))}`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'statement_pdf' || tabName === 'statement_doc') {
                descEl.innerHTML = '📄 <strong>كشف الحساب المعتمد (تصميم رسمي / PDF):</strong> كشف الحساب المالي المعتمد بكافة الحركات والأرصدة والتوقيعات الرسمية.';
                const docHtml = generateStatementHTML(d);
                activeModalContext.docHtml = docHtml;

                docWrap.innerHTML = docHtml;
                docWrap.style.display = 'block';
                if (btnPrint) {
                    btnPrint.style.display = 'inline-flex';
                    btnPrint.innerHTML = '<span>🖨️</span> طباعة / PDF';
                }

                const waText = `*كشف حساب تفصيلي معتمد* 📊\n` +
                    `*الحساب:* ${d.accountName}\n` +
                    `*الفترة:* من ${d.dateFrom} إلى ${d.dateTo}\n` +
                    `*الرصيد الحالي:* ${d.totalBalance} SDG\n\n` +
                    `مرفق طيه كشف الحساب المالي التفصيلي المعتمد بكافة العمليات.\n` +
                    `الإدارة المالية 🏢`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            }
        }
    };

    // ══════════════════════════════════════════════════════════════════════════
    // HTML Template Generator for Official Delivery Orders (Quantities Only)
    // ══════════════════════════════════════════════════════════════════════════

    function generateDeliveryOrderHTML(doc, type = 'sales') {
        const isSales = (type === 'sales');
        const title = isSales ? 'إذن استلام وتسليم بضاعة رسمي' : 'إذن توريد واستلام مخزني رسمي';
        const partyLabel = isSales ? 'اسم العميل / المستلم:' : 'اسم المورد:';
        const partyName = isSales ? (doc.customerName || 'العميل') : (doc.supplierName || 'المورد');
        const refNo = isSales ? `DO-${doc.invoiceNo}` : `GRN-${doc.invoiceNo}`;
        const items = doc.items || [];

        let rowsHtml = '';
        let totalUnits = 0;

        if (items.length === 0) {
            rowsHtml = `
                <tr>
                    <td colspan="5" style="padding: 18px; text-align: center; color: #64748b; font-weight: bold; font-size: 0.95rem;">لا توجد أصناف مسجلة حتى الآن</td>
                </tr>
            `;
        } else {
            items.forEach((it, idx) => {
                const name = it.name || it.itemName || 'صنف';
                const unit = it.unit || it.selectedUnit || 'وحدة';
                const qty = parseFloat(it.quantity || it.qty || 0);
                totalUnits += qty;
                const notes = it.notes || it.itemNotes || '-';

                rowsHtml += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 8px 10px; text-align: center; font-weight: bold;">${idx + 1}</td>
                        <td style="padding: 8px 10px; font-weight: bold; color: #0f172a;">${name}</td>
                        <td style="padding: 8px 10px; text-align: center; color: #475569;">${unit}</td>
                        <td style="padding: 8px 10px; text-align: center; font-weight: 900; font-size: 1.05rem; color: #0284c7;">${qty.toLocaleString()}</td>
                        <td style="padding: 8px 10px; text-align: center; color: #64748b; font-size: 0.85rem;">${notes}</td>
                    </tr>
                `;
            });
        }

        return `
            <div style="font-family: 'Tajawal', Arial, sans-serif; direction: rtl; padding: 18px; border: 2px solid #0f172a; border-radius: 12px; background: #ffffff; color: #0f172a;">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0ea5e9; padding-bottom: 12px; margin-bottom: 15px;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.35rem; color: #0f172a; font-weight: 900;">${title}</h2>
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: bold;">مستند رسمي معتمد - كميات بضاعة فقط (بدون أسعار)</span>
                    </div>
                    <div style="text-align: left;">
                        <div style="font-weight: 900; font-size: 1.15rem; color: #0ea5e9; font-family: monospace;">#${refNo}</div>
                        <div style="font-size: 0.82rem; color: #475569;">التاريخ: ${doc.date || ''}</div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 15px; font-size: 0.88rem;">
                    <div><strong>${partyLabel}</strong> ${partyName}</div>
                    <div><strong>المستودع / الفرع:</strong> ${doc.warehouse || 'المستودع الرئيسي'}</div>
                    <div><strong>رقم الفاتورة المرجعية:</strong> #${doc.invoiceNo}</div>
                    <div><strong>حالة المستند:</strong> <span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px; font-weight:bold;">جاهز للتسليم / الاستلام</span></div>
                </div>

                <!-- Items Table (NO PRICES) -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: #0f172a; color: #ffffff;">
                            <th style="padding: 8px 10px; width: 40px; text-align: center;">#</th>
                            <th style="padding: 8px 10px; text-align: right;">بيان الصنف والمواصفات</th>
                            <th style="padding: 8px 10px; width: 80px; text-align: center;">الوحدة</th>
                            <th style="padding: 8px 10px; width: 110px; text-align: center;">الكمية المسلّمة</th>
                            <th style="padding: 8px 10px; width: 140px; text-align: center;">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                    <tfoot>
                        <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #0f172a;">
                            <td colspan="3" style="padding: 8px 10px; text-align: left;">إجمالي عدد الوحدات والكميات:</td>
                            <td style="padding: 8px 10px; text-align: center; color: #0284c7; font-size: 1.1rem;">${totalUnits.toLocaleString()}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Signatures -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; border-top: 1px dashed #cbd5e1; padding-top: 15px; text-align: center; font-size: 0.82rem;">
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">توقيع أمين المستودع / الصرف:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 80%; margin: 0 auto;"></div>
                    </div>
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">توقيع السائق / مندوب التوصيل:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 80%; margin: 0 auto;"></div>
                    </div>
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">إقرار وتوقيع المستلم بالاستلام سليم:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 80%; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        `;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HTML Template Generator for Official Financial Invoices (With Prices & Taxes)
    // ══════════════════════════════════════════════════════════════════════════

    function generateInvoiceHTML(doc, type = 'sales') {
        const isSales = (type === 'sales');
        const isAmended = !!(doc.isAmended || doc.amendmentCount);
        const title = isSales 
            ? (isAmended ? `فاتورة مبيعات معتمدة (نسخة معدّلة - تعديل #${doc.amendmentCount || 1})` : 'فاتورة مبيعات ضريبية معتمدة') 
            : 'فاتورة شراء وتوريد معتمدة';
        const partyLabel = isSales ? 'اسم العميل / المشتري:' : 'اسم المورد:';
        const partyName = isSales ? (doc.customerName || 'عميل نقدي') : (doc.supplierName || 'مورد عام');
        const invNo = doc.invoiceNo || 'INV-001';
        const currency = doc.currency || 'SDG';
        const items = doc.items || [];

        let rowsHtml = '';
        let grandCalc = 0;

        if (items.length === 0) {
            rowsHtml = `
                <tr>
                    <td colspan="6" style="padding: 18px; text-align: center; color: #64748b; font-weight: bold;">لا توجد أصناف مدخلة بعد</td>
                </tr>
            `;
        } else {
            items.forEach((it, idx) => {
                const name = it.name || it.itemName || 'صنف';
                const unit = it.unit || it.selectedUnit || 'وحدة';
                const qty = parseFloat(it.quantity || it.qty || 0);
                const price = parseFloat(it.price || it.unitPrice || 0);
                const total = parseFloat(it.total) || (qty * price);
                grandCalc += total;

                rowsHtml += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 8px 10px; text-align: center; font-weight: bold;">${idx + 1}</td>
                        <td style="padding: 8px 10px; font-weight: bold; color: #0f172a;">${name}</td>
                        <td style="padding: 8px 10px; text-align: center; color: #475569;">${unit}</td>
                        <td style="padding: 8px 10px; text-align: center; font-weight: 800; color: #0284c7;">${qty.toLocaleString()}</td>
                        <td style="padding: 8px 10px; text-align: center; font-family: monospace; font-weight: bold;">${price.toLocaleString()} ${currency}</td>
                        <td style="padding: 8px 10px; text-align: center; font-family: monospace; font-weight: 900; color: #10b981;">${total.toLocaleString()} ${currency}</td>
                    </tr>
                `;
            });
        }

        const grandFinal = doc.grandTotal ? String(doc.grandTotal) : grandCalc.toLocaleString();

        return `
            <div style="font-family: 'Tajawal', Arial, sans-serif; direction: rtl; padding: 20px; border: 2px solid #0f172a; border-radius: 12px; background: #ffffff; color: #0f172a;">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ca8a04; padding-bottom: 12px; margin-bottom: 15px;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.4rem; color: #0f172a; font-weight: 900;">${title}</h2>
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: bold;">مستند مالي رسمي معتمد - نظام السجانة المحاسبي</span>
                    </div>
                    <div style="text-align: left;">
                        <div style="font-weight: 900; font-size: 1.2rem; color: #ca8a04; font-family: monospace;">#${invNo}</div>
                        <div style="font-size: 0.82rem; color: #475569;">التاريخ: ${doc.date || ''}</div>
                    </div>
                </div>

                <!-- Party & Metadata Box -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 15px; font-size: 0.88rem;">
                    <div><strong>${partyLabel}</strong> ${partyName}</div>
                    <div><strong>العملة:</strong> ${currency}</div>
                    <div><strong>طريقة السداد:</strong> ${doc.paymentMethod === 'credit' ? 'آجل' : 'نقدي / بنكي'}</div>
                    <div><strong>حالة المستند:</strong> <span style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:4px; font-weight:bold;">معتمد ومقيد بالدفاتر</span></div>
                </div>

                <!-- Items Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: #0f172a; color: #ffffff;">
                            <th style="padding: 8px 10px; width: 40px; text-align: center;">#</th>
                            <th style="padding: 8px 10px; text-align: right;">بيان الصنف والمواصفات</th>
                            <th style="padding: 8px 10px; width: 70px; text-align: center;">الوحدة</th>
                            <th style="padding: 8px 10px; width: 90px; text-align: center;">الكمية</th>
                            <th style="padding: 8px 10px; width: 110px; text-align: center;">سعر الوحدة</th>
                            <th style="padding: 8px 10px; width: 130px; text-align: center;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                    <tfoot>
                        <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #0f172a;">
                            <td colspan="5" style="padding: 10px; text-align: left; font-size: 1rem;">صافي القيمة الإجمالية:</td>
                            <td style="padding: 10px; text-align: center; color: #10b981; font-size: 1.25rem; font-family: monospace;">${grandFinal} ${currency}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Signatures -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; border-top: 1px dashed #cbd5e1; padding-top: 15px; text-align: center; font-size: 0.82rem;">
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">المحاسب المسؤول:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 80%; margin: 0 auto;"></div>
                    </div>
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">اعتماد الإدارة المالية:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 80%; margin: 0 auto;"></div>
                    </div>
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">توقيع المستلم:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 80%; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        `;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HTML Template Generator for Official Statement of Account (General Ledger)
    // ══════════════════════════════════════════════════════════════════════════

    function generateStatementHTML(d) {
        const balNum = parseFloat(String(d.totalBalance).replace(/,/g, '')) || 0;
        const balType = (balNum > 0) ? 'مدين (عليه)' : ((balNum < 0) ? 'دائن (له)' : 'متزن (0.00)');
        const balColor = (balNum > 0) ? '#dc2626' : ((balNum < 0) ? '#16a34a' : '#0284c7');

        let rowsHtml = '';
        const txns = (window.currentTxns && Array.isArray(window.currentTxns)) ? window.currentTxns.slice(-25) : [];
        if (txns.length === 0) {
            rowsHtml = `
                <tr>
                    <td colspan="5" style="padding: 18px; text-align: center; color: #64748b; font-weight: bold;">لا توجد حركات مسجلة خلال الفترة المحددة</td>
                </tr>
            `;
        } else {
            txns.forEach((t, idx) => {
                const date = t.date || '-';
                const stmt = t.statement || t.desc || t.memo || 'حركة حساب';
                const deb = parseFloat(t.debit) || 0;
                const crd = parseFloat(t.credit) || 0;

                rowsHtml += `
                    <tr style="border-bottom: 1px solid #e2e8f0; font-size: 0.85rem;">
                        <td style="padding: 6px 8px; text-align: center; font-weight: bold;">${idx + 1}</td>
                        <td style="padding: 6px 8px; text-align: center;">${date}</td>
                        <td style="padding: 6px 8px; font-weight: bold; color: #0f172a;">${stmt}</td>
                        <td style="padding: 6px 8px; text-align: center; font-family: monospace; color: #dc2626;">${deb > 0 ? deb.toLocaleString() : '-'}</td>
                        <td style="padding: 6px 8px; text-align: center; font-family: monospace; color: #16a34a;">${crd > 0 ? crd.toLocaleString() : '-'}</td>
                    </tr>
                `;
            });
        }

        return `
            <div style="font-family: 'Tajawal', Arial, sans-serif; direction: rtl; padding: 18px; border: 2px solid #0f172a; border-radius: 12px; background: #ffffff; color: #0f172a;">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0284c7; padding-bottom: 10px; margin-bottom: 14px;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.35rem; color: #0f172a; font-weight: 900;">كشف حساب مالي معتمد</h2>
                        <span style="font-size: 0.82rem; color: #64748b; font-weight: bold;">دفتر الأستاذ العام - نظام السجانة المحاسبي</span>
                    </div>
                    <div style="text-align: left;">
                        <div style="font-size: 0.82rem; color: #475569;">تاريخ الطباعة: ${new Date().toISOString().split('T')[0]}</div>
                    </div>
                </div>

                <!-- Account & Period Details -->
                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 12px; font-size: 0.88rem;">
                    <div><strong>اسم الحساب:</strong> <span style="font-weight: 900; color: #0284c7;">${d.accountName}</span></div>
                    <div><strong>الفترة:</strong> من ${d.dateFrom} إلى ${d.dateTo}</div>
                    <div><strong>إجمالي المدين:</strong> ${d.totalDebit} SDG</div>
                    <div><strong>إجمالي الدائن:</strong> ${d.totalCredit} SDG</div>
                </div>

                <!-- Balance Banner -->
                <div style="background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 900; font-size: 0.95rem;">الرصيد النهائي المستحق:</span>
                    <span style="font-family: monospace; font-size: 1.25rem; font-weight: 900; color: ${balColor};">${Math.abs(balNum).toLocaleString()} SDG (${balType})</span>
                </div>

                <!-- Transactions Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #0f172a; color: #ffffff; font-size: 0.82rem;">
                            <th style="padding: 7px; width: 35px; text-align: center;">#</th>
                            <th style="padding: 7px; width: 90px; text-align: center;">التاريخ</th>
                            <th style="padding: 7px; text-align: right;">البيان / الحركة</th>
                            <th style="padding: 7px; width: 100px; text-align: center;">مدين (+)</th>
                            <th style="padding: 7px; width: 100px; text-align: center;">دائن (-)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>

                <!-- Signatures -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px dashed #cbd5e1; padding-top: 12px; text-align: center; font-size: 0.8rem;">
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">توقيع المحاسب / الإدارة المالية:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 70%; margin: 0 auto;"></div>
                    </div>
                    <div>
                        <div style="font-weight: bold; margin-bottom: 25px; color: #475569;">توقيع العميل / المصادقة على الرصيد:</div>
                        <div style="border-bottom: 1px dotted #94a3b8; width: 70%; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        `;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Automatic Send Hook Triggers (Post-Submit)
    // ══════════════════════════════════════════════════════════════════════════

    window.triggerSalesAutoSendOnSave = function(invoiceNo, invoiceData = null) {
        const s = getSalesAutoSend();
        if (!s.enabled || s.mode === 'disabled') return;

        setTimeout(() => {
            if (typeof sendInvoiceWA === 'function') {
                sendInvoiceWA(invoiceNo).then(() => {
                    if (typeof showToast === 'function') {
                        showToast('🚀 الإرسال التلقائي: تم الإرسال للعميل عبر واتساب بنجاح', 'success');
                    }
                }).catch(e => console.error(e));
            } else {
                window.openSalesWhatsAppModal(invoiceNo, invoiceData);
                if (s.mode) {
                    window.switchSuiteWATab(s.mode);
                }
                if (typeof showToast === 'function') {
                    showToast('🚀 الإرسال التلقائي: تم تحضير المستند للإرسال الفوري عبر واتساب', 'success');
                }
            }
        }, 300);
    };

    window.triggerPurchaseAutoSendOnSave = function(invoiceNo, invoiceData = null) {
        const s = getPurchaseAutoSend();
        if (!s.enabled || s.mode === 'disabled') return;

        setTimeout(() => {
            if (typeof sendInvoiceWA === 'function') {
                sendInvoiceWA(invoiceNo).then(() => {
                    if (typeof showToast === 'function') {
                        showToast('🚀 الإرسال التلقائي: تم الإرسال للمورد عبر واتساب بنجاح', 'success');
                    }
                }).catch(e => console.error(e));
            } else {
                window.openPurchaseWhatsAppModal(invoiceNo, invoiceData);
                if (s.mode) window.switchSuiteWATab(s.mode);
            }
        }, 300);
    };

    // ═══ Direct DOM Button Event Binding ═══
    function bindWhatsAppButtons() {
        const salesBtn = document.getElementById('btn-whatsapp-master-sales');
        if (salesBtn && !salesBtn._waBound) {
            salesBtn._waBound = true;
            salesBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.openSalesWhatsAppModal();
            });
        }
        const purBtn = document.getElementById('btn-whatsapp-master-purchase');
        if (purBtn && !purBtn._waBound) {
            purBtn._waBound = true;
            purBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.openPurchaseWhatsAppModal();
            });
        }
        const ledBtn = document.getElementById('btn-whatsapp-master-ledger');
        if (ledBtn && !ledBtn._waBound) {
            ledBtn._waBound = true;
            ledBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.openLedgerWhatsAppModal();
            });
        }
    }

    // ═══ Close Dropdowns on Outside Click ═══
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#auto-send-control-wrapper-sales') && !e.target.closest('#auto-send-sales-dropdown-menu')) {
            const menu = document.getElementById('auto-send-sales-dropdown-menu');
            if (menu) menu.style.display = 'none';
        }
        if (!e.target.closest('#auto-send-control-wrapper-purchase') && !e.target.closest('#auto-send-purchase-dropdown-menu')) {
            const menu = document.getElementById('auto-send-purchase-dropdown-menu');
            if (menu) menu.style.display = 'none';
        }
    });

    // ═══ Initialize UI on DOM Ready ═══
    function initAllSuiteFeatures() {
        injectSuiteStyles();
        syncSalesAutoSendUI();
        syncPurchaseAutoSendUI();
        bindWhatsAppButtons();
    }

    // Export Globals
    window.resolvePartyInfo = resolvePartyInfo;
    if (typeof window.shareCurrentLedgerReport !== 'function') {
        window.shareCurrentLedgerReport = async function(presetIndex = 0, presetName = 'افتراضي') {
            let accountName = document.getElementById('chip-name')?.innerText || '';
            if (!accountName || accountName === 'جميع الحسابات') {
                const inputVal = document.getElementById('account-search-input')?.value || '';
                if (inputVal && !inputVal.includes('Enter')) accountName = inputVal.trim();
            }
            if (!accountName) accountName = 'كشف حساب عام';

            const totalBalance = document.getElementById('launcher-total-balance')?.innerText || '0.00';
            const balNum = parseFloat(String(totalBalance).replace(/,/g, '')) || 0;
            const balType = (balNum > 0) ? 'مدين (عليه)' : ((balNum < 0) ? 'دائن (له)' : 'متزن (0.00)');

            const shareText = `*كشف حساب معتمد* 📊\n` +
                `*الحساب:* ${accountName}\n` +
                `*الرصيد النهائي الحالي:* ${Math.abs(balNum).toLocaleString()} SDG (${balType})\n` +
                `نظام السجانة للحلول المحاسبية 🏢`;

            const shareUrl = window.location.href.split('?')[0].replace('general-ledger.php', 'preview-ledger.php') + `?presetIndex=${presetIndex || 0}`;

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: `كشف حساب معتمد - ${accountName}`,
                        text: shareText,
                        url: shareUrl
                    });
                    if (typeof showToast === 'function') showToast('✅ تمت المشاركة بنجاح', 'success');
                    return;
                } catch(e) {
                    if (e.name === 'AbortError') return;
                }
            }
            if (typeof window.openLedgerWhatsAppModal === 'function') {
                window.openLedgerWhatsAppModal(accountName);
            }
        };
    }

})(window, document);
