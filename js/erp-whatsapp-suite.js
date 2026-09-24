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
                z-index: 1000000;
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
        let cleaned = String(phone).replace(/[^\d+]/g, '');
        // If phone starts with 0 and has 10 digits (e.g., Sudan 09xxxxxxx or 01xxxxxxx)
        if (cleaned.startsWith('0') && cleaned.length === 10) {
            cleaned = '249' + cleaned.substring(1);
        } else if (cleaned.startsWith('+')) {
            cleaned = cleaned.substring(1);
        }
        return cleaned;
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
            if (s.mode === 'invoice') label = 'مفعّل (فاتورة وكشف)';
            else if (s.mode === 'delivery_order_text') label = 'مفعّل (إذن نصي)';

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
            if (s.mode === 'invoice') label = 'مفعّل (فاتورة شراء)';
            else if (s.mode === 'receipt_order_text') label = 'مفعّل (إذن نصي)';

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
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn" onclick="window.copySuiteWAMessage()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:10px; padding:8px 14px; font-weight:bold; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                <span>📋</span> نسخ النص
                            </button>
                            <button type="button" id="erp-wa-btn-print-doc" class="btn" onclick="window.printSuiteWADocument()" style="display:none; background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8; border-radius:10px; padding:8px 14px; font-weight:bold; font-size:0.85rem; cursor:pointer; align-items:center; gap:6px;">
                                <span>🖨️</span> طباعة / PDF
                            </button>
                        </div>
                        <div style="display:flex; gap:8px;">
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

    // ═══ Execute WhatsApp Send ═══
    window.executeSuiteWASend = function(method = 'web') {
        if (!activeModalContext) return;

        const phoneInput = document.getElementById('erp-wa-phone-input');
        const phone = cleanPhoneNumber(phoneInput ? phoneInput.value : '');

        if (!phone) {
            if (typeof showToast === 'function') {
                showToast('⚠️ يرجى إدخال رقم الواتساب أولاً للمستلم', 'warning');
            } else {
                alert('⚠️ يرجى إدخال رقم الواتساب أولاً للمستلم');
            }
            if (phoneInput) phoneInput.focus();
            return;
        }

        const textEl = document.getElementById('erp-wa-preview-text');
        const messageText = textEl ? (textEl.textContent || textEl.innerText) : '';

        // If official doc mode and user has whatsapp-integration.js connected
        if (activeModalContext.currentTab.endsWith('_doc') && typeof sendPDFViaWhatsApp === 'function') {
            const docWrap = document.getElementById('erp-wa-preview-doc-wrap');
            if (docWrap && docWrap.innerHTML.trim() !== '') {
                // Try sending PDF via tenant if available
                sendPDFViaWhatsApp(docWrap, phone, messageText.substring(0, 300)).catch(err => {
                    console.warn("Direct PDF send fallback to web:", err);
                    const waUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(messageText)}`;
                    window.open(waUrl, '_blank');
                });
                window.closeSuiteWAModal();
                return;
            }
        }

        // Direct WhatsApp Link
        const waUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(messageText)}`;
        window.open(waUrl, '_blank');

        if (typeof showToast === 'function') {
            showToast('🚀 تم فتح محادثة الواتساب بنجاح', 'success');
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
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
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
    window.openSalesWhatsAppModal = function(invoiceNo = null) {
        initSuiteStyles();
        initSuiteModal();

        // 1. Resolve Invoice Data
        let inv = null;
        if (invoiceNo) {
            let allSales = [];
            try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []; } catch(e){}
            inv = allSales.find(i => String(i.invoiceNo) === String(invoiceNo));
            if (!inv) {
                try { inv = JSON.parse(localStorage.getItem('erp_inv_' + invoiceNo)); } catch(e){}
            }
        }

        // If no invoice found from storage, read from active form
        if (!inv) {
            const invNoVal = document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || 'NEW';
            const custId = document.getElementById('hdr-customer')?.value || '';
            const custName = document.getElementById('hdr-customer-search')?.value || 'عميل نقدي';
            const rowsData = (window.rows && Array.isArray(window.rows)) ? window.rows : [];
            const grandTotal = document.getElementById('t-grand')?.textContent || '0.00';
            const cur = (typeof window.getCurrency === 'function') ? window.getCurrency() : 'SDG';

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
        let custPhone = '';
        let custName = inv.customerName || 'عميل نقدي';
        if (inv.customer && inv.customer !== 'walk-in') {
            let customers = [];
            try { customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || []; } catch(e){}
            const custObj = customers.find(c => String(c.id) === String(inv.customer));
            if (custObj) {
                custPhone = custObj.whatsapp || custObj.phone || custObj.mobile || '';
                custName = custObj.name || custName;
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
            <button type="button" class="erp-wa-tab-btn active" id="tab-sales-do-doc" onclick="window.switchSuiteWATab('delivery_order_doc')">
                <span>📄</span> إذن استلام رسمي (كميات فقط)
            </button>
            <button type="button" class="erp-wa-tab-btn" id="tab-sales-do-txt" onclick="window.switchSuiteWATab('delivery_order_text')">
                <span>📝</span> إذن استلام نصي كمعلومة
            </button>
            <button type="button" class="erp-wa-tab-btn" id="tab-sales-inv" onclick="window.switchSuiteWATab('invoice')">
                <span>💰</span> الفاتورة وكشف الحساب
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
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
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
    window.openPurchaseWhatsAppModal = function(invoiceNo = null) {
        initSuiteStyles();
        initSuiteModal();

        let inv = null;
        if (invoiceNo) {
            let allPurchases = [];
            try { allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []; } catch(e){}
            inv = allPurchases.find(i => String(i.invoiceNo) === String(invoiceNo));
        }

        if (!inv) {
            const invNoVal = document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || 'PUR-NEW';
            const suppId = document.getElementById('hdr-supplier')?.value || '';
            const suppName = document.getElementById('hdr-supplier-search')?.value || 'مورد عام';
            const rowsData = (window.rows && Array.isArray(window.rows)) ? window.rows : [];
            const grandTotal = document.getElementById('t-grand')?.textContent || '0.00';
            const cur = (typeof window.getCurrency === 'function') ? window.getCurrency() : 'SDG';

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

        let suppPhone = '';
        let suppName = inv.supplierName || 'مورد';
        if (inv.supplier) {
            let suppliers = [];
            try { suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []; } catch(e){}
            const suppObj = suppliers.find(s => String(s.id) === String(inv.supplier));
            if (suppObj) {
                suppPhone = suppObj.whatsapp || suppObj.phone || suppObj.mobile || '';
                suppName = suppObj.name || suppName;
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
            <button type="button" class="erp-wa-tab-btn active" id="tab-purchase-ro-doc" onclick="window.switchSuiteWATab('receipt_order_doc')">
                <span>📄</span> إذن توريد رسمي (كميات فقط)
            </button>
            <button type="button" class="erp-wa-tab-btn" id="tab-purchase-ro-txt" onclick="window.switchSuiteWATab('receipt_order_text')">
                <span>📝</span> إذن توريد نصي كمعلومة
            </button>
            <button type="button" class="erp-wa-tab-btn" id="tab-purchase-inv" onclick="window.switchSuiteWATab('invoice')">
                <span>💰</span> فاتورة الشراء وحساب المورد
            </button>
        `;

        window.switchSuiteWATab('receipt_order_doc');
        document.getElementById('erp-wa-modal-overlay').style.display = 'flex';
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 3. GENERAL LEDGER / STATEMENT LOGIC (general-ledger.php)
    // ══════════════════════════════════════════════════════════════════════════

    window.openLedgerWhatsAppModal = function() {
        initSuiteStyles();
        initSuiteModal();

        const accountId = document.getElementById('filter-account')?.value || 'ALL';
        const accountName = document.getElementById('chip-name')?.innerText || 'كشف حساب عام';
        const dateFrom = document.getElementById('filter-date-from')?.value || 'البداية';
        const dateTo = document.getElementById('filter-date-to')?.value || 'الآن';

        const totalDebit = document.getElementById('launcher-total-debit')?.innerText || '0.00';
        const totalCredit = document.getElementById('launcher-total-credit')?.innerText || '0.00';
        const totalBalance = document.getElementById('launcher-total-balance')?.innerText || '0.00';

        // Detect Phone Number
        let partyPhone = '';
        if (accountId.startsWith('cust-')) {
            const cId = accountId.replace('cust-', '');
            let custs = [];
            try { custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || []; } catch(e){}
            const c = custs.find(x => String(x.id) === String(cId));
            if (c) partyPhone = c.whatsapp || c.phone || '';
        } else if (accountId.startsWith('supp-')) {
            const sId = accountId.replace('supp-', '');
            let supps = [];
            try { supps = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []; } catch(e){}
            const s = supps.find(x => String(x.id) === String(sId));
            if (s) partyPhone = s.whatsapp || s.phone || '';
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
            <button type="button" class="erp-wa-tab-btn active" id="tab-ledger-txt" onclick="window.switchSuiteWATab('statement_text')">
                <span>📝</span> ملخص كشف الحساب كنص سريع
            </button>
            <button type="button" class="erp-wa-tab-btn" id="tab-ledger-pdf" onclick="window.switchSuiteWATab('statement_pdf')">
                <span>📄</span> كشف حساب معتمد (PDF)
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

        // Update active class on tab buttons
        document.querySelectorAll('.erp-wa-tab-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`[onclick*="'${tabName}'"]`);
        if (activeBtn) activeBtn.classList.add('active');

        const descEl = document.getElementById('erp-wa-mode-desc');
        const textEl = document.getElementById('erp-wa-preview-text');
        const docWrap = document.getElementById('erp-wa-preview-doc-wrap');
        const btnPrint = document.getElementById('erp-wa-btn-print-doc');
        const charCountEl = document.getElementById('erp-wa-char-count');

        // Reset visibility
        textEl.style.display = 'block';
        docWrap.style.display = 'none';
        if (btnPrint) btnPrint.style.display = 'none';

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

                // Accompanying WhatsApp text
                const waText = `*إذن استلام وتسليم بضاعة رسمي* 📄\n` +
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
                
                let itemLines = items.map((it, idx) => {
                    const name = it.name || it.itemName || 'صنف';
                    const qty = it.quantity || it.qty || 1;
                    const unit = it.unit || 'وحدة';
                    const price = it.price || it.unitPrice || 0;
                    const total = it.total || (qty * price);
                    return `${idx + 1}. *${name}* ← الكمية: ${qty} ${unit} | السعر: ${price} ${inv.currency || 'SDG'} (الإجمالي: ${total})`;
                }).join('\n');

                const waText = `*إشعار صدور إذن استلام بضاعة* 📋\n` +
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
            } else if (tabName === 'invoice') {
                descEl.innerHTML = '💰 <strong>فاتورة المبيعات وكشف الحساب:</strong> تفاصيل مالية شاملة للأصناف، الكميات، الأسعار، الضرائب، الأرصدة، وطريقة الدفع.';
                
                let itemLines = items.map((it, idx) => {
                    const name = it.name || it.itemName || 'صنف';
                    const qty = it.quantity || it.qty || 1;
                    const unit = it.unit || 'وحدة';
                    const price = it.price || it.unitPrice || 0;
                    const total = it.total || (qty * price);
                    return `• ${name}: ${qty} ${unit} × ${price} = *${total} ${inv.currency || 'SDG'}*`;
                }).join('\n');

                const waText = `*فاتورة مبيعات وكشف حساب رسمي* 🧾\n` +
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
                let itemLines = items.map((it, idx) => {
                    const name = it.name || it.itemName || 'صنف';
                    const qty = it.quantity || it.qty || 1;
                    const unit = it.unit || 'وحدة';
                    const price = it.price || it.unitPrice || 0;
                    return `${idx + 1}. *${name}* ← الكمية: ${qty} ${unit} | السعر: ${price}`;
                }).join('\n');

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
                descEl.innerHTML = '📝 <strong>ملخص كشف الحساب:</strong> إرسال ملخص مالي دقيق بالأرصدة والمديونيات للفترة المحددة مباشرة للعميل/المورد.';
                
                const balNum = parseFloat(String(d.totalBalance).replace(/,/g, '')) || 0;
                const balType = (balNum > 0) ? 'مدين (عليه)' : ((balNum < 0) ? 'دائن (له)' : 'متزن (0.00)');

                let txnsSnippet = '';
                if (window.currentTxns && Array.isArray(window.currentTxns) && window.currentTxns.length > 0) {
                    const recent = window.currentTxns.slice(-4);
                    txnsSnippet = '\n*أحدث الحركات المسجلة:*\n' + recent.map(t => {
                        const date = t.date || '-';
                        const stmt = (t.statement || t.desc || '-').substring(0, 30);
                        const deb = parseFloat(t.debit) || 0;
                        const crd = parseFloat(t.credit) || 0;
                        const amtStr = deb > 0 ? `مدين: ${deb.toLocaleString()}` : `دائن: ${crd.toLocaleString()}`;
                        return `• ${date} | ${stmt} (${amtStr})`;
                    }).join('\n') + '\n';
                }

                const waText = `*كشف حساب معتمد* 📊\n` +
                    `*السيد/السادة:* ${d.accountName}\n` +
                    `*الفترة:* من ${d.dateFrom} إلى ${d.dateTo}\n\n` +
                    `*الملخص المحاسبي والمالي:*\n` +
                    `• إجمالي المدين: ${d.totalDebit} SDG\n` +
                    `• إجمالي الدائن: ${d.totalCredit} SDG\n` +
                    `• *الرصيد النهائي الحالي:* ${Math.abs(balNum).toLocaleString()} SDG (${balType})\n` +
                    txnsSnippet +
                    `──────────────────\n` +
                    `يرجى مراجعة الرصيد ومطابقته. للاستفسار نرجو التواصل مع الإدارة المالية.\n` +
                    `شاكرين ومقدرين حسن تعاونكم الدائم 🌟`;

                textEl.textContent = waText;
                charCountEl.textContent = `${waText.length} حرف`;
            } else if (tabName === 'statement_pdf') {
                descEl.innerHTML = '📄 <strong>كشف الحساب المعتمد (PDF):</strong> معاينة وتصميم وطباعة التقرير الشامل تمهيداً للإرسال أو التنزيل.';
                if (btnPrint) {
                    btnPrint.style.display = 'inline-flex';
                    btnPrint.innerHTML = '<span>👁️</span> فتح استوديو المعاينة والطباعة';
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

        items.forEach((it, idx) => {
            const name = it.name || it.itemName || 'صنف';
            const unit = it.unit || 'وحدة';
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
    // Automatic Send Hook Triggers (Post-Submit)
    // ══════════════════════════════════════════════════════════════════════════

    window.triggerSalesAutoSendOnSave = function(invoiceNo) {
        const s = getSalesAutoSend();
        if (!s.enabled || s.mode === 'disabled') return;

        // Give storage 200ms to ensure invoice is saved
        setTimeout(() => {
            window.openSalesWhatsAppModal(invoiceNo);
            if (s.mode) {
                window.switchSuiteWATab(s.mode);
            }
            if (typeof showToast === 'function') {
                showToast('🚀 الإرسال التلقائي: تم تحضير المستند للإرسال الفوري عبر واتساب', 'success');
            }
        }, 350);
    };

    window.triggerPurchaseAutoSendOnSave = function(invoiceNo) {
        const s = getPurchaseAutoSend();
        if (!s.enabled || s.mode === 'disabled') return;

        setTimeout(() => {
            window.openPurchaseWhatsAppModal(invoiceNo);
            if (s.mode) {
                window.switchSuiteWATab(s.mode);
            }
            if (typeof showToast === 'function') {
                showToast('🚀 الإرسال التلقائي: تم تحضير إذن التوريد للإرسال للمورد عبر واتساب', 'success');
            }
        }, 350);
    };

    // ═══ Initialize UI on DOM Ready ═══
    document.addEventListener('DOMContentLoaded', () => {
        injectSuiteStyles();
        syncSalesAutoSendUI();
        syncPurchaseAutoSendUI();
    });

    // Run immediately if DOM is already parsed
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        injectSuiteStyles();
        syncSalesAutoSendUI();
        syncPurchaseAutoSendUI();
    }

})(window, document);
