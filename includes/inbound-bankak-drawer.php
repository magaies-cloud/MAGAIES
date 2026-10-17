<?php
// =============================================================
// MAGAIEES ERP 2026 - Inbound Bankak Receipts Drawer & Controller
// صينية مراجعة وفحص إشعارات بنكك الواردة من العملاء والربط مع السند المتعدد
// =============================================================
global $GLOBAL_TENANT_ID;
$currentActiveTenant = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '12';
?>
<!-- Inbound Bankak Receipts Floating Drawer Overlay -->
<div id="inbound-bankak-drawer-overlay" style="display:none; position:fixed; inset:0; background:rgba(2, 6, 23, 0.75); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:999999; justify-content:flex-end; align-items:stretch; transition:all 0.3s ease;" onclick="if(event.target===this) InboundBankakManager.closeDrawer()">
    
    <!-- Drawer Panel (Slides from Right in RTL) -->
    <div id="inbound-bankak-drawer-panel" style="width:100%; max-width:580px; height:100vh; background:linear-gradient(145deg, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 0.99) 100%); border-left:2px solid rgba(56, 189, 248, 0.35); box-shadow:-15px 0 50px rgba(0,0,0,0.85), 0 0 30px rgba(56,189,248,0.2); display:flex; flex-direction:column; color:#fff; font-family:'Tajawal',sans-serif; direction:rtl; box-sizing:border-box; position:relative; overflow:hidden; animation:slideLeftDrawer 0.35s cubic-bezier(0.16, 1, 0.3, 1);">

        <!-- Ambient Glow Elements -->
        <div style="position:absolute; top:-100px; left:-100px; width:300px; height:300px; background:radial-gradient(circle, rgba(56,189,248,0.18) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>
        <div style="position:absolute; bottom:-100px; right:-100px; width:300px; height:300px; background:radial-gradient(circle, rgba(16,185,129,0.15) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>

        <!-- 1. Drawer Header -->
        <div style="padding:16px 20px 12px 20px; border-bottom:1.5px solid rgba(255,255,255,0.1); background:rgba(15,23,42,0.6); display:flex; flex-direction:column; gap:10px; flex-shrink:0;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(16,185,129,0.25)); border:1.8px solid #38bdf8; display:flex; align-items:center; justify-content:center; font-size:1.4rem; box-shadow:0 0 15px rgba(56,189,248,0.35);">
                        📥
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.18rem; font-weight:900; color:#f8fafc; display:flex; align-items:center; gap:8px;">
                            <span>إشعارات بنكك والتحصيلات الواردة</span>
                            <span id="drawer-live-pulse" style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 8px #10b981; animation:pulseScale 1.4s infinite alternate;"></span>
                        </h3>
                        <span style="font-size:0.78rem; color:#94a3b8; font-weight:700;">فحص تلقائي بالذكاء الاصطناعي وتنزيل فوري في الحسابات</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <button type="button" onclick="InboundBankakManager.refreshReceipts()" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#7dd3fc; border-radius:8px; width:34px; height:34px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.05rem;" title="تحديث القائمة الآن">
                        🔄
                    </button>
                    <button type="button" onclick="InboundBankakManager.closeDrawer()" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#fca5a5; border-radius:8px; width:34px; height:34px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.15rem; font-weight:900;" title="إغلاق الصينية">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div style="display:flex; gap:8px; align-items:center; justify-content:space-between; background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.08); padding:6px 12px; border-radius:10px;">
                <div style="display:flex; align-items:center; gap:6px; font-size:0.8rem; font-weight:800; color:#a7f3d0;">
                    <span>🟢 سليم ومنزل بالحسابات:</span>
                    <strong id="stat-verified-count" style="font-family:'Consolas',monospace; font-size:0.95rem;">0</strong>
                </div>
                <div style="display:flex; align-items:center; gap:6px; font-size:0.8rem; font-weight:800; color:#fca5a5;">
                    <span>🔴 إنذار تكرار:</span>
                    <strong id="stat-duplicate-count" style="font-family:'Consolas',monospace; font-size:0.95rem;">0</strong>
                </div>
                <div style="display:flex; align-items:center; gap:6px; font-size:0.8rem; font-weight:800; color:#fef08a;">
                    <span>🟡 قيد الفحص:</span>
                    <strong id="stat-pending-count" style="font-family:'Consolas',monospace; font-size:0.95rem;">0</strong>
                </div>
            </div>

            <!-- Tab Filters -->
            <div style="display:flex; gap:6px; margin-top:2px;">
                <button type="button" class="drawer-tab-btn active" data-filter="all" onclick="InboundBankakManager.filterTab('all', this)" style="flex:1; padding:6px 8px; border-radius:8px; border:1px solid rgba(56,189,248,0.4); background:rgba(56,189,248,0.2); color:#e0f2fe; font-weight:900; font-size:0.82rem; cursor:pointer;">
                    الكل (<span id="tab-all-count">0</span>)
                </button>
                <button type="button" class="drawer-tab-btn" data-filter="verified" onclick="InboundBankakManager.filterTab('verified', this)" style="flex:1; padding:6px 8px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.82rem; cursor:pointer;">
                    🟢 سليم / منزل
                </button>
                <button type="button" class="drawer-tab-btn" data-filter="duplicate_rejected" onclick="InboundBankakManager.filterTab('duplicate_rejected', this)" style="flex:1; padding:6px 8px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.82rem; cursor:pointer;">
                    🔴 مكرر
                </button>
                <button type="button" class="drawer-tab-btn" data-filter="pending" onclick="InboundBankakManager.filterTab('pending', this)" style="flex:1; padding:6px 8px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.82rem; cursor:pointer;">
                    🟡 جديد
                </button>
            </div>
        </div>

        <!-- 2. Drawer Body (Scrollable Receipts List) -->
        <div id="inbound-receipts-list-container" style="flex:1; overflow-y:auto; padding:14px 18px; display:flex; flex-direction:column; gap:12px; scrollbar-width:thin;">
            <div style="display:flex; justify-content:center; align-items:center; padding:40px 0; color:#94a3b8;">
                <span style="animation:spin 1s linear infinite; font-size:1.5rem; margin-left:8px;">🔄</span> جاري تحميل إشعارات التحصيل...
            </div>
        </div>

        <!-- 3. Drawer Footer Actions -->
        <div style="padding:12px 18px; border-top:1.5px solid rgba(255,255,255,0.1); background:rgba(15,23,42,0.8); display:flex; flex-direction:column; gap:8px; flex-shrink:0;">
            <div style="display:flex; gap:8px;">
                <button type="button" onclick="InboundBankakManager.openDirectUploadModal()" style="flex:1; height:38px; border-radius:10px; background:linear-gradient(135deg, rgba(16,185,129,0.25), rgba(5,150,105,0.4)); border:1.5px solid #10b981; color:#a7f3d0; font-weight:900; font-size:0.86rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 0 12px rgba(16,185,129,0.3);">
                    <span>➕</span> <span>رفع إشعار تحويل يدوي</span>
                </button>
                <button type="button" onclick="InboundBankakManager.simulateIncomingReceipt()" style="flex:1.1; height:38px; border-radius:10px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(14,165,233,0.4)); border:1.5px solid #38bdf8; color:#e0f2fe; font-weight:900; font-size:0.84rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 0 12px rgba(56,189,248,0.3);" title="إرسال إشعار تجريبي فوري لمحاكاة وصول إشعار العميل حياً">
                    <span>⚡</span> <span>محاكاة إشعار فوري (تجربة)</span>
                </button>
            </div>
            <a href="send-receipt.php?tenant_id=<?php echo urlencode($currentActiveTenant); ?>" target="_blank" style="text-align:center; color:#38bdf8; font-size:0.78rem; text-decoration:none; font-weight:800; padding:2px;">
                🔗 فتح بوابة الزبائن الخارجية (send-receipt.php)
            </a>
        </div>
    </div>
</div>

<!-- Modal: رفع إشعار تحويل يدوي (Manual Upload Modal) -->
<div id="inbound-manual-upload-modal" style="display:none; position:fixed; inset:0; background:rgba(2, 6, 23, 0.85); backdrop-filter:blur(10px); z-index:9999999; justify-content:center; align-items:center; padding:16px; font-family:'Tajawal',sans-serif; direction:rtl;" onclick="if(event.target===this) InboundBankakManager.closeManualUploadModal()">
    <div style="width:100%; max-width:480px; background:linear-gradient(145deg, rgba(15,23,42,0.98), rgba(30,41,59,0.98)); border:2px solid #10b981; border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,0.9); padding:20px 22px; color:#fff; display:flex; flex-direction:column; gap:12px;">
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:8px;">
            <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#34d399;">📥 رفع وفحص إشعار تحويل جديد</h3>
            <button type="button" onclick="InboundBankakManager.closeManualUploadModal()" style="background:none; border:none; color:#94a3b8; font-size:1.2rem; cursor:pointer;">✕</button>
        </div>
        <form id="inboundManualUploadForm" onsubmit="InboundBankakManager.handleManualUploadSubmit(event)" style="display:flex; flex-direction:column; gap:10px;">
            <div>
                <label style="font-size:0.80rem; color:#38bdf8; font-weight:800; margin-bottom:4px; display:block;">👤 اسم العميل أو كود الإشعارات (رقمي مثل 105 أو نصي مثل بون): *</label>
                <input type="text" id="manualCustName" required oninput="InboundBankakManager.onManualCodeOrNameInput(this.value)" placeholder="مثال: 105 أو بون أو كوستابون" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.4); border:1px solid rgba(56,189,248,0.4); border-radius:8px; padding:8px 12px; color:#fff; font-weight:800; font-family:'Tajawal',sans-serif;">
                <div id="manualCustCodeHint" style="font-size:0.75rem; color:#34d399; margin-top:3px; display:none; font-weight:800;"></div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <div>
                    <label style="font-size:0.80rem; color:#cbd5e1; font-weight:800; margin-bottom:4px; display:block;">المبلغ (SDG):</label>
                    <input type="number" step="any" id="manualAmount" placeholder="يُقرأ تلقائياً من الصورة" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:8px 12px; color:#fff; font-family:'Consolas',monospace;">
                </div>
                <div>
                    <label style="font-size:0.80rem; color:#cbd5e1; font-weight:800; margin-bottom:4px; display:block;">رقم المعاملة / العملية:</label>
                    <input type="text" id="manualTransferNo" placeholder="يُقرأ تلقائياً من الصورة" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:8px 12px; color:#fff; font-family:'Consolas',monospace;">
                </div>
            </div>
            <div>
                <label style="font-size:0.80rem; color:#cbd5e1; font-weight:800; margin-bottom:4px; display:block;">رقم هاتف الواتساب:</label>
                <input type="tel" id="manualCustPhone" placeholder="09xxxxxxxx" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:8px 12px; color:#fff; font-family:'Consolas',monospace;">
            </div>
            <div>
                <label style="font-size:0.80rem; color:#cbd5e1; font-weight:800; margin-bottom:4px; display:block;">صورة الإشعار (للفحص بالذكاء الاصطناعي):</label>
                <input type="file" id="manualReceiptFile" accept="image/*" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:6px; color:#fff; font-size:0.82rem;">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
                <button type="button" onclick="InboundBankakManager.closeManualUploadModal()" style="height:36px; padding:0 16px; border-radius:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; font-weight:800; cursor:pointer;">إلغاء</button>
                <button type="submit" id="btnSubmitManualUpload" style="height:36px; padding:0 20px; border-radius:8px; background:linear-gradient(135deg, #10b981, #059669); border:1.5px solid #34d399; color:#fff; font-weight:900; cursor:pointer;">فحص وتنزيل في الحسابات 🚀</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: إخطار الزبون عبر واتساب بمشكلة الإشعار (Customer WhatsApp Notification Modal) -->
<div id="inbound-customer-alert-modal" style="display:none; position:fixed; inset:0; background:rgba(2, 6, 23, 0.85); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:9999999; justify-content:center; align-items:center; padding:16px; font-family:'Tajawal',sans-serif; direction:rtl;" onclick="if(event.target===this) InboundBankakManager.closeCustomerAlertModal()">
    <div style="width:100%; max-width:540px; background:linear-gradient(145deg, rgba(15,23,42,0.98) 0%, rgba(30,16,56,0.98) 60%, rgba(15,23,42,0.98) 100%); border:2px solid #38bdf8; border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,0.9), 0 0 40px rgba(56,189,248,0.3); padding:20px 22px; color:#fff; display:flex; flex-direction:column; gap:14px; position:relative; animation:ocrModalPopIn 0.25s ease-out;">
        
        <!-- Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1.5px solid rgba(255,255,255,0.12); padding-bottom:10px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.6rem;">📲</span>
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#25d366;">إرسال تنبيه رسمي للزبون عبر واتساب</h3>
                    <span style="font-size:0.75rem; color:#94a3b8; font-weight:700;">إبلاغ العميل بخلل أو تأكيد إشعار بنكك المرسل بنقرة واحدة أو عبر كود العميل</span>
                </div>
            </div>
            <button type="button" onclick="InboundBankakManager.closeCustomerAlertModal()" style="background:none; border:none; color:#94a3b8; font-size:1.3rem; cursor:pointer; font-weight:900;">✕</button>
        </div>

        <input type="hidden" id="cust-alert-receipt-id">
        <input type="hidden" id="cust-alert-phone">

        <!-- Quick Lookup by Customer Code (Numeric or Word) -->
        <div style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.35); border-radius:10px; padding:8px 12px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span style="font-size:0.78rem; color:#34d399; font-weight:800;">🎯 إرسال إلى كود عميل:</span>
            <input type="text" id="cust-alert-code-lookup" oninput="InboundBankakManager.lookupCustomerForAlertByCode(this.value)" placeholder="اكتب كود العميل (مثلاً: 105 أو بون) لجلب رقمه تلقائياً..." style="flex:1; min-width:180px; background:rgba(0,0,0,0.45); border:1px solid rgba(56,189,248,0.4); border-radius:7px; padding:5px 10px; color:#fff; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:800;">
        </div>

        <!-- Target Customer Info -->
        <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:8px 14px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <div>
                <span style="font-size:0.75rem; color:#94a3b8;">العميل:</span>
                <strong id="cust-alert-name" style="font-size:0.92rem; color:#ffffff; margin-right:4px;"></strong>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="font-size:0.75rem; color:#94a3b8;">الهاتف:</span>
                <strong id="cust-alert-phone-display" style="font-size:0.90rem; color:#7dd3fc; font-family:'Consolas',monospace; direction:ltr; display:inline-block;"></strong>
            </div>
        </div>

        <!-- Notification Reason Selector -->
        <div>
            <label style="font-size:0.82rem; font-weight:900; color:#f8fafc; margin-bottom:6px; display:block;">
                اختر نوع التنبيه المطلوب إرساله:
            </label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <button type="button" class="alert-type-btn active" data-type="duplicate" onclick="InboundBankakManager.selectAlertTemplate('duplicate', this)" style="padding:8px 10px; border-radius:8px; border:1.5px solid #ef4444; background:rgba(239,68,68,0.2); color:#fca5a5; font-weight:900; font-size:0.80rem; cursor:pointer; text-align:right;">
                    🔴 إشعار مكرر (احتيال)
                </button>
                <button type="button" class="alert-type-btn" data-type="unclear" onclick="InboundBankakManager.selectAlertTemplate('unclear', this)" style="padding:8px 10px; border-radius:8px; border:1.5px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.80rem; cursor:pointer; text-align:right;">
                    🟡 صورة غير واضحة
                </button>
                <button type="button" class="alert-type-btn" data-type="amount_mismatch" onclick="InboundBankakManager.selectAlertTemplate('amount_mismatch', this)" style="padding:8px 10px; border-radius:8px; border:1.5px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.80rem; cursor:pointer; text-align:right;">
                    🟠 اختلاف في المبلغ
                </button>
                <button type="button" class="alert-type-btn" data-type="reconciled" onclick="InboundBankakManager.selectAlertTemplate('reconciled', this)" style="padding:8px 10px; border-radius:8px; border:1.5px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.80rem; cursor:pointer; text-align:right;">
                    🟢 تم استلام واعتماد السند
                </button>
            </div>
        </div>

        <!-- Editable Message Body -->
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <label style="font-size:0.82rem; font-weight:900; color:#f8fafc;">نص الرسالة المرسلة للزبون (قابلة للتعديل):</label>
                <span style="font-size:0.72rem; color:#94a3b8;">يمكنك الإضافة أو التعديل بحرية</span>
            </div>
            <textarea id="cust-alert-message-text" rows="5" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.5); border:1.5px solid rgba(56,189,248,0.4); border-radius:10px; color:#fff; font-family:'Tajawal',sans-serif; font-size:0.88rem; padding:10px; resize:vertical; line-height:1.5; outline:none;"></textarea>
        </div>

        <!-- Modal Actions -->
        <div style="display:flex; align-items:center; gap:10px; justify-content:flex-end; margin-top:4px;">
            <button type="button" onclick="InboundBankakManager.closeCustomerAlertModal()" style="height:38px; padding:0 16px; border-radius:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; font-weight:800; font-size:0.85rem; cursor:pointer;">
                إلغاء
            </button>
            <button type="button" id="btn-cust-alert-send-wa" onclick="InboundBankakManager.dispatchWhatsAppAlert()" style="height:38px; padding:0 22px; border-radius:8px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:1.8px solid #34d399; color:#ffffff; font-weight:900; font-size:0.92rem; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 0 20px rgba(16,185,129,0.5);">
                <span>🚀 إرسال لواتساب الزبون الآن</span>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes slideLeftDrawer {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideInRightToast {
    from { transform: translateX(120%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.inbound-receipt-card {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.85) 100%);
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: all 0.25s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
}
.inbound-receipt-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.6);
}
.inbound-receipt-card.card-verified {
    border-color: rgba(16, 185, 129, 0.45);
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(15, 23, 42, 0.9) 100%);
}
.inbound-receipt-card.card-duplicate {
    border-color: rgba(239, 68, 68, 0.55);
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.12) 0%, rgba(15, 23, 42, 0.95) 100%);
}
.inbound-receipt-card.card-pending {
    border-color: rgba(245, 158, 11, 0.4);
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(15, 23, 42, 0.9) 100%);
}
</style>

<script>
// =============================================================
// InboundBankakManager - Client-Side Controller
// =============================================================
window.InboundBankakManager = {
    receipts: [],
    knownReceiptIds: new Set(),
    syncedVoucherNos: new Set(),
    _hasInitialLoaded: false,
    activeFilter: 'all',
    currentReceiptForAlert: null,

    getActiveTenantId: function() {
        return (window.SERVER_TENANT && window.SERVER_TENANT.id) 
            || (JSON.parse(localStorage.getItem('user')||'{}').tenant_id) 
            || '<?php echo $currentActiveTenant; ?>' 
            || '12';
    },

    init: function() {
        this.refreshReceipts();
        
        // Real-Time Fast Polling every 4 seconds for live detection
        setInterval(() => {
            this.refreshReceipts(true);
        }, 4000);

        // Check URL parameters for Deep Linking (e.g. ?action=inspect_receipt&receipt_id=123)
        const params = new URLSearchParams(window.location.search);
        if (params.get('action') === 'inspect_receipt' || params.get('receipt_id')) {
            const rId = params.get('receipt_id');
            setTimeout(() => {
                this.inspectById(rId);
            }, 800);
        }
    },

    toggleDrawer: function() {
        const overlay = document.getElementById('inbound-bankak-drawer-overlay');
        if (!overlay) return;
        const isShown = overlay.style.display === 'flex';
        if (isShown) {
            this.closeDrawer();
        } else {
            overlay.style.display = 'flex';
            this.refreshReceipts();
        }
    },

    closeDrawer: function() {
        const overlay = document.getElementById('inbound-bankak-drawer-overlay');
        if (overlay) overlay.style.display = 'none';
    },

    // Sync a posted inbound receipt into localStorage (erp_receipts_v1, erp_customers_v1, erp_banks_v1) and General Ledger
    syncPostedReceiptToLocalErp: function(r, postingData = null) {
        if (!r || (!r.voucher_no && !(postingData && postingData.voucher_no))) return;
        const vNo = (postingData && postingData.voucher_no) ? postingData.voucher_no : r.voucher_no;
        if (!vNo) return;

        const getTenantKey = window.getTenantKey || ((k) => k);
        const rKey = getTenantKey('erp_receipts_v1');
        let storedReceipts = [];
        try {
            storedReceipts = JSON.parse(localStorage.getItem(rKey) || localStorage.getItem('erp_receipts_v1') || '[]');
            if (!Array.isArray(storedReceipts)) storedReceipts = [];
        } catch (e) { storedReceipts = []; }

        const alreadyInLocal = storedReceipts.some(v => v && (v.voucherNo === vNo || v.id === vNo));
        if (alreadyInLocal && this.syncedVoucherNos.has(vNo) && !postingData) {
            return;
        }
        this.syncedVoucherNos.add(vNo);

        // 1. Sync Customer in erp_customers_v1
        const cKey = getTenantKey('erp_customers_v1');
        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem(cKey) || localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch (e) { custs = []; }

        let rawCustId = (postingData && postingData.customer_id) ? postingData.customer_id : (r.customer_id || '');
        rawCustId = String(rawCustId).replace(/^cust-/, '');
        const custName = (postingData && postingData.customer_name) ? postingData.customer_name : (r.customer_name || 'عميل بنكك');
        const amt = parseFloat(r.amount || (postingData && postingData.voucher && postingData.voucher.totalAmount) || 0) || 0;

        let cIdx = custs.findIndex(c => c && (String(c.id) === rawCustId || (c.name && c.name.trim() === custName.trim())));
        if (cIdx > -1) {
            rawCustId = String(custs[cIdx].id);
            if (postingData && postingData.customer && postingData.customer.currentBalance !== undefined) {
                custs[cIdx].currentBalance = parseFloat(postingData.customer.currentBalance) || 0;
            } else if (!alreadyInLocal && amt > 0) {
                custs[cIdx].currentBalance = (parseFloat(custs[cIdx].currentBalance) || 0) - amt;
            }
        } else if (postingData && postingData.customer) {
            rawCustId = String(postingData.customer.id || rawCustId || ('CUST-' + Date.now()));
            custs.push(postingData.customer);
        } else if (custName) {
            rawCustId = rawCustId || ('CUST-' + Date.now());
            custs.push({
                id: rawCustId,
                name: custName,
                phone: r.customer_phone || '',
                whatsapp: r.customer_phone || '',
                currentBalance: -amt,
                status: 'active'
            });
        }
        try {
            localStorage.setItem(cKey, JSON.stringify(custs));
            localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
        } catch (e) {}

        // 2. Sync Bank in erp_banks_v1
        const bKey = getTenantKey('erp_banks_v1');
        let banks = [];
        try {
            banks = JSON.parse(localStorage.getItem(bKey) || localStorage.getItem('erp_banks_v1') || '[]');
            if (!Array.isArray(banks)) banks = [];
        } catch (e) { banks = []; }

        let rawBankId = (postingData && postingData.bank_id) ? postingData.bank_id : '';
        rawBankId = String(rawBankId).replace(/^bank-/, '');
        let bIdx = banks.findIndex(b => b && ((rawBankId && String(b.id) === rawBankId) || (b.type !== 'safe' && b.name && (b.name.includes('الخرطوم') || (r.bank_name && r.bank_name.includes(b.name))))));
        if (bIdx === -1) {
            bIdx = banks.findIndex(b => b && b.type !== 'safe');
        }
        let resolvedBankName = r.bank_name || 'بنك الخرطوم';
        if (bIdx > -1) {
            rawBankId = String(banks[bIdx].id);
            resolvedBankName = banks[bIdx].name || resolvedBankName;
            if (postingData && postingData.bank && postingData.bank.currentBalance !== undefined) {
                banks[bIdx].currentBalance = parseFloat(postingData.bank.currentBalance) || 0;
            } else if (!alreadyInLocal && amt > 0) {
                banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + amt;
            }
            try {
                localStorage.setItem(bKey, JSON.stringify(banks));
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            } catch (e) {}
        } else if (postingData && postingData.bank) {
            rawBankId = String(postingData.bank.id || 'B-1700000000001');
            resolvedBankName = postingData.bank.name || resolvedBankName;
            banks.push(postingData.bank);
            try {
                localStorage.setItem(bKey, JSON.stringify(banks));
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            } catch (e) {}
        }

        // 3. Sync Voucher in erp_receipts_v1
        const sysRate = (typeof window.getSystemUsdRate === 'function' ? window.getSystemUsdRate() : 5999) || 5999;
        const voucherDesc = `سداد إشعار تحويل بنكي (${r.bank_name || 'بنكك'}) رقم العملية: ${r.transfer_no || ''} - العميل: ${custName}`;
        const voucherObj = (postingData && postingData.voucher) ? postingData.voucher : {
            id: vNo,
            voucherNo: vNo,
            type: 'receipt',
            isGrouped: true,
            mainAccount: 'bank-' + (rawBankId || 'B-1700000000001'),
            toAccount: 'bank-' + (rawBankId || 'B-1700000000001'),
            fromAccount: 'حسابات فرعية متعددة',
            date: (r.created_at ? r.created_at.substring(0, 10) : new Date().toISOString().substring(0, 10)),
            time: new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
            description: voucherDesc,
            notes: voucherDesc,
            totalAmount: amt,
            amountLocal: amt,
            foreignAmount: +(amt / sysRate).toFixed(2),
            amountForeign: +(amt / sysRate).toFixed(2),
            currency: r.currency || 'SDG',
            exchangeRate: 1,
            systemExchangeRate: sysRate,
            paymentMethod: 'hybrid',
            inboundReceiptId: Number(r.id),
            items: [
                {
                    account: 'cust-' + rawCustId,
                    accountId: 'cust-' + rawCustId,
                    accountName: custName,
                    amount: amt,
                    statement: `إشعار تحويل ${r.bank_name || 'بنكك'} رقم ${r.transfer_no || ''}`,
                    costCenter: '',
                    costCenterId: '',
                    costCenterName: '',
                    paymentMethods: ['bank'],
                    paymentMethod: 'bank',
                    cashAmount: 0,
                    cashSafe: '',
                    cashStatement: '',
                    bankAmount: amt,
                    bankTransfers: [
                        {
                            amount: amt,
                            bankName: resolvedBankName,
                            transferNo: r.transfer_no || '',
                            statement: `إشعار وارد #${r.id} - ${custName}`
                        }
                    ],
                    chequeAmount: 0,
                    cheques: [],
                    bankName: resolvedBankName,
                    transferNo: r.transfer_no || '',
                    chequeBank: '',
                    chequeNo: '',
                    chequeDate: '',
                    chequeDueDate: ''
                }
            ],
            isPosted: true,
            createdAt: new Date().toISOString()
        };

        const vIdx = storedReceipts.findIndex(v => v && (v.voucherNo === vNo || v.id === vNo));
        if (vIdx > -1) {
            storedReceipts[vIdx] = voucherObj;
        } else {
            storedReceipts.push(voucherObj);
        }
        try {
            localStorage.setItem(rKey, JSON.stringify(storedReceipts));
            localStorage.setItem('erp_receipts_v1', JSON.stringify(storedReceipts));
        } catch (e) {}

        // 4. Rebuild Accounts Dropdown & General Ledger
        if (typeof window.buildAccountsArray === 'function') {
            try { window.buildAccountsArray(); } catch (e) {}
        }
        if (typeof window.rebuildGLFromAllTables === 'function') {
            try { window.rebuildGLFromAllTables(false); } catch (e) {}
        }
        if (typeof window.refreshVoucherNavigationList === 'function') {
            try { window.refreshVoucherNavigationList(); } catch (e) {}
        }
    },

    _isBatchScanning: false,

    refreshReceipts: function(isSilent = false) {
        const tId = this.getActiveTenantId();
        fetch('api/inbound_receipts.php?action=list&tenant_id=' + encodeURIComponent(tId))
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    // Sync any backend-posted receipts to local ERP storage & GL
                    res.data.forEach(r => {
                        if (r.voucher_no && (r.status === 'verified' || r.status === 'reconciled')) {
                            this.syncPostedReceiptToLocalErp(r);
                        }
                    });

                    // Auto-scan any pending receipts in batch (excluding forgery check)
                    const pendingWithImg = res.data.filter(r => r.status === 'pending' && r.receipt_image && (!r.amount || parseFloat(r.amount) <= 0 || !r.transfer_no));
                    if (pendingWithImg.length > 0 && !this._isBatchScanning) {
                        this._isBatchScanning = true;
                        fetch('api/inbound_receipts.php?action=scan_pending_batch&tenant_id=' + encodeURIComponent(tId))
                            .then(r2 => r2.json())
                            .then(batchRes => {
                                this._isBatchScanning = false;
                                if (batchRes && batchRes.success && Number(batchRes.scanned_count || 0) > 0) {
                                    this.refreshReceipts(true);
                                }
                            })
                            .catch(() => { this._isBatchScanning = false; });
                    }

                    // Check if new receipts arrived in real-time
                    if (this._hasInitialLoaded) {
                        const newReceipts = res.data.filter(r => !this.knownReceiptIds.has(Number(r.id)));
                        if (newReceipts.length > 0) {
                            newReceipts.forEach(r => this.knownReceiptIds.add(Number(r.id)));
                            this.playNotificationChime();
                            if (newReceipts.length === 1) {
                                this.showLiveInboundToast(newReceipts[0]);
                            } else {
                                this.showAdminBatchNotification(newReceipts);
                            }
                        }
                    } else {
                        res.data.forEach(r => this.knownReceiptIds.add(Number(r.id)));
                        this._hasInitialLoaded = true;
                    }

                    this.receipts = res.data;
                    this.updateStats(res.stats);
                    this.renderList();
                }
            })
            .catch(err => {
                if (!isSilent) console.error('Failed to load inbound receipts:', err);
            });
    },

    showAdminBatchNotification: function(batchReceipts) {
        if (!Array.isArray(batchReceipts) || batchReceipts.length === 0) return;
        const verifiedList = batchReceipts.filter(r => r.status === 'verified' || r.voucher_no);
        const custName = batchReceipts[0].customer_name || 'عميل واتساب';
        const custId = batchReceipts[0].customer_id || '';
        const totalAmt = verifiedList.reduce((acc, r) => acc + (parseFloat(r.amount) || 0), 0);
        const toastId = 'admin-batch-toast-' + Date.now();
        const div = document.createElement('div');
        div.id = toastId;
        div.style.cssText = `
            position: fixed; top: 24px; right: 24px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            border: 2px solid #10b981; border-radius: 16px; padding: 16px 20px; color: #fff;
            box-shadow: 0 15px 40px rgba(0,0,0,0.85), 0 0 25px rgba(16,185,129,0.5);
            z-index: 99999999; display: flex; flex-direction: column; gap: 8px;
            font-family: 'Tajawal', sans-serif; direction: rtl; max-width: 440px;
            animation: slideInRightToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        `;
        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong style="color:#34d399; font-size:1rem;">🔔 إشعار مدير النظام — إضافة إشعارات بالحسابات</strong>
                <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:#94a3b8; font-size:1.1rem; cursor:pointer;">✕</button>
            </div>
            <div style="font-size:0.86rem; color:#f8fafc; line-height:1.5;">
                تم فحص وإضافة <strong>(${verifiedList.length || batchReceipts.length}) إشعار بنكي</strong> تلقائياً من حساب الزبون:
                <strong style="color:#ffd700;">${escapeHtml(custName)}</strong> إلى حساب <strong style="color:#38bdf8;">بنك الخرطوم</strong>
                بإجمالي <strong style="color:#34d399;">${totalAmt.toLocaleString('en-US')} SDG</strong>.
            </div>
            <div style="display:flex; gap:8px; margin-top:4px;">
                <a href="general-ledger.php?accountId=${encodeURIComponent(custId ? ('cust-' + String(custId).replace(/^cust-/, '')) : '')}&name=${encodeURIComponent(custName)}" style="flex:1; text-align:center; padding:6px 10px; border-radius:8px; background:#10b981; color:#fff; font-weight:900; font-size:0.8rem; text-decoration:none;">📊 فتح كشف حساب ${escapeHtml(custName)}</a>
                <button type="button" onclick="InboundBankakManager.toggleDrawer(); this.parentElement.parentElement.remove();" style="padding:6px 12px; border-radius:8px; background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#e0f2fe; font-weight:800; font-size:0.8rem; cursor:pointer;">📥 عرض الإشعارات</button>
            </div>
        `;
        document.body.appendChild(div);
        setTimeout(() => { if (div.parentElement) div.remove(); }, 18000);
    },

    playNotificationChime: function() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            // Two-tone cheerful bank chime: 880Hz then 1320Hz
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.setValueAtTime(1320, ctx.currentTime + 0.12);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.5);
        } catch(e) {}
    },

    showLiveInboundToast: function(receipt) {
        if (!receipt) return;
        const toastId = 'inbound-toast-' + Date.now();
        const div = document.createElement('div');
        div.id = toastId;
        div.style.cssText = `
            position: fixed;
            top: 24px;
            right: 24px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            border: 2px solid #10b981;
            border-radius: 16px;
            padding: 14px 18px;
            color: #fff;
            box-shadow: 0 15px 40px rgba(0,0,0,0.8), 0 0 25px rgba(16,185,129,0.5);
            z-index: 99999999;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            animation: slideInRightToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        `;
        const vBadge = receipt.voucher_no ? `<span style="font-size:0.75rem; background:rgba(16,185,129,0.25); border:1px solid #10b981; color:#6ee7b7; padding:1px 6px; border-radius:6px;">نزل بالحسابات (${escapeHtml(receipt.voucher_no)})</span>` : `<span style="font-size:0.75rem; background:rgba(56,189,248,0.2); color:#7dd3fc; padding:1px 6px; border-radius:6px;">بنكك</span>`;
        div.innerHTML = `
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(16,185,129,0.25); border:1.8px solid #34d399; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0;">
                📥
            </div>
            <div style="display:flex; flex-direction:column; gap:2px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <strong style="color:#34d399; font-size:0.95rem;">⚡ إشعار بنكك جديد تم فحصه!</strong>
                    ${vBadge}
                </div>
                <span style="font-size:0.86rem; color:#f8fafc; font-weight:800;">من: ${escapeHtml(receipt.customer_name)}</span>
                <span style="font-size:0.80rem; color:#cbd5e1;">المبلغ: <strong style="color:#fef08a;">${Number(receipt.amount||0).toLocaleString()} SDG</strong> | انقر للعرض بالسند 🚀</span>
            </div>
            <button type="button" onclick="event.stopPropagation(); this.parentElement.remove()" style="background:none; border:none; color:#94a3b8; font-size:1.2rem; cursor:pointer; padding:0 4px;">✕</button>
        `;
        div.onclick = () => {
            div.remove();
            this.inspectInVoucher(receipt.id);
        };
        document.body.appendChild(div);
        setTimeout(() => { if (div.parentElement) div.remove(); }, 12000);
    },

    updateStats: function(stats) {
        if (!stats) return;
        const total = stats.total || 0;
        const verified = (stats.verified || 0);
        const dup = stats.duplicate_rejected || 0;
        const pending = stats.pending || 0;

        // Top bar badge
        const badge = document.getElementById('inbound-receipts-badge-count');
        if (badge) {
            badge.textContent = total;
            badge.style.display = total > 0 ? 'inline-block' : 'none';
            if (dup > 0) {
                badge.style.background = '#ef4444';
            } else {
                badge.style.background = '#10b981';
            }
        }

        // Header Drawer stats
        const elVer = document.getElementById('stat-verified-count');
        if (elVer) elVer.textContent = verified;
        const elDup = document.getElementById('stat-duplicate-count');
        if (elDup) elDup.textContent = dup;
        const elPen = document.getElementById('stat-pending-count');
        if (elPen) elPen.textContent = pending;
        const elAll = document.getElementById('tab-all-count');
        if (elAll) elAll.textContent = total;
    },

    filterTab: function(filter, btn) {
        this.activeFilter = filter;
        document.querySelectorAll('.drawer-tab-btn').forEach(b => {
            b.classList.remove('active');
            b.style.borderColor = 'rgba(255,255,255,0.1)';
            b.style.background = 'rgba(255,255,255,0.04)';
            b.style.color = '#94a3b8';
        });
        if (btn) {
            btn.classList.add('active');
            btn.style.borderColor = 'rgba(56,189,248,0.4)';
            btn.style.background = 'rgba(56,189,248,0.2)';
            btn.style.color = '#e0f2fe';
        }
        this.renderList();
    },

    renderList: function() {
        const container = document.getElementById('inbound-receipts-list-container');
        if (!container) return;

        let filtered = this.receipts;
        if (this.activeFilter !== 'all') {
            if (this.activeFilter === 'verified') {
                filtered = this.receipts.filter(r => r.status === 'verified' || r.status === 'reconciled');
            } else {
                filtered = this.receipts.filter(r => r.status === this.activeFilter);
            }
        }

        if (filtered.length === 0) {
            container.innerHTML = `
                <div style="text-align:center; padding:40px 10px; color:#94a3b8;">
                    <div style="font-size:2.5rem; margin-bottom:8px;">📭</div>
                    <strong style="font-size:1rem; color:#f8fafc; display:block;">لا توجد إشعارات في هذا التصنيف</strong>
                    <span style="font-size:0.80rem;">سيظهر أي إشعار بنكك جديد يرسله العميل هنا فورياً</span>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.forEach(r => {
            const isDup = r.status === 'duplicate_rejected';
            const isVer = r.status === 'verified' || r.status === 'reconciled';
            const isPosted = !!(r.voucher_no);
            const cardClass = isDup ? 'card-duplicate' : (isVer ? 'card-verified' : 'card-pending');

            const statusBadge = isDup 
                ? '<span style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">⚠️ إشعار مكرر (خطر)</span>'
                : (isPosted
                    ? `<span style="background:rgba(16,185,129,0.25); border:1px solid #10b981; color:#6ee7b7; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">✅ تم الفحص والتنزيل (${escapeHtml(r.voucher_no)})</span>`
                    : (isVer 
                        ? '<span style="background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#6ee7b7; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">✅ سليم وجاهز للتنزيل</span>'
                        : '<span style="background:rgba(245,158,11,0.2); border:1px solid #f59e0b; color:#fde047; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">⏳ بانتظار الفحص</span>'));

            const formattedAmt = parseFloat(r.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

            html += `
                <div class="inbound-receipt-card ${cardClass}">
                    <!-- Top Info Row -->
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                            <span style="font-size:1.1rem;">👤</span>
                            <div style="display:flex; flex-direction:column; min-width:0;">
                                <strong style="font-size:0.92rem; color:#f8fafc; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(r.customer_name)}</strong>
                                <span style="font-size:0.74rem; color:#94a3b8; font-family:'Consolas',monospace; direction:ltr; text-align:right;">${escapeHtml(r.customer_phone || 'بدون هاتف')}</span>
                            </div>
                        </div>
                        <div>${statusBadge}</div>
                    </div>

                    <!-- Details Row -->
                    <div style="background:rgba(0,0,0,0.3); border-radius:8px; padding:6px 10px; display:flex; justify-content:space-between; align-items:center; font-size:0.82rem;">
                        <div>
                            <span style="color:#94a3b8; font-size:0.74rem;">المبلغ:</span>
                            <strong style="color:#38bdf8; font-size:1.02rem; font-family:'Consolas',monospace; margin-right:4px;">${formattedAmt}</strong>
                            <span style="font-size:0.72rem; color:#cbd5e1;">${escapeHtml(r.currency || 'SDG')}</span>
                        </div>
                        <div>
                            <span style="color:#94a3b8; font-size:0.74rem;">رقم العملية:</span>
                            <strong style="color:#fef08a; font-family:'Consolas',monospace; margin-right:3px;">${escapeHtml(r.transfer_no || 'غير محدد')}</strong>
                        </div>
                    </div>

                    ${r.notes ? `<div style="font-size:0.75rem; color:${isDup ? '#fca5a5' : '#cbd5e1'}; background:rgba(0,0,0,0.2); padding:4px 8px; border-radius:6px; line-height:1.3;">${escapeHtml(r.notes)}</div>` : ''}

                    <!-- Actions Row -->
                    <div style="display:flex; gap:6px; margin-top:2px; align-items:center; flex-wrap:wrap;">
                        <!-- زر الفحص والتنزيل في السند والحسابات -->
                        <button type="button" onclick="InboundBankakManager.inspectInVoucher(${r.id})" style="flex:1.2; height:32px; border-radius:7px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(14,165,233,0.4)); border:1.2px solid #38bdf8; color:#ffffff; font-size:0.76rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; box-shadow:0 0 10px rgba(56,189,248,0.2);" title="فحص الإشعار وتفريغ بياناته في السند وتنزيله في الحسابات">
                            <span>🔍</span> <span>${isPosted ? 'عرض بالسند' : 'فحص وتنزيل'}</span>
                        </button>

                        <!-- زر فحص التزوير عند الطلب فقط -->
                        <button type="button" onclick="InboundBankakManager.checkForgeryOnDemand(${r.id}, this)" style="height:32px; padding:0 9px; border-radius:7px; background:linear-gradient(135deg, rgba(168,85,247,0.25), rgba(126,34,206,0.4)); border:1.2px solid #a855f7; color:#e9d5ff; font-size:0.75rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="فحص التزوير والتلاعب الجنائي بالصورة (عند الطلب فقط)">
                            <span>🕵️</span> <span>فحص التزوير</span>
                        </button>

                        ${(!isPosted && !isDup && parseFloat(r.amount || 0) > 0) ? `
                        <button type="button" onclick="InboundBankakManager.postToAccountsDirect(${r.id}, this)" style="height:32px; padding:0 10px; border-radius:7px; background:linear-gradient(135deg, rgba(16,185,129,0.3), rgba(5,150,105,0.5)); border:1.2px solid #10b981; color:#a7f3d0; font-size:0.76rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="تنزيل فوري في حساب العميل وحساب بنك الخرطوم وإصدار سند قبض">
                            <span>⚡</span> <span>تنزيل بالحسابات</span>
                        </button>
                        ` : ''}

                        ${(!r.amount || parseFloat(r.amount) <= 0 || !r.transfer_no) ? `
                        <button type="button" onclick="InboundBankakManager.triggerOCR(${r.id}, this)" style="height:32px; padding:0 8px; border-radius:7px; background:linear-gradient(135deg, rgba(245,158,11,0.25), rgba(217,119,6,0.35)); border:1.2px solid #f59e0b; color:#fde047; font-size:0.75rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="استخراج المبلغ ورقم العملية بالذكاء الاصطناعي وتنزيله في الحسابات">
                            <span>⚡</span> <span>فحص AI</span>
                        </button>
                        ` : ''}

                        <!-- زر توجيه الإشعار لحساب عميل بالكود الرقمي أو النصي وربط رقم المرسل -->
                        <button type="button" onclick="InboundBankakManager.promptAssignByCode(${r.id})" style="height:32px; padding:0 8px; border-radius:7px; background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.45); color:#6ee7b7; font-size:0.74rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="توجيه الإشعار لحساب عميل بواسطة الكود الرقمي (مثل 105) أو النصي (مثل بون) مع خيار ربط رقم الموبايل بالحساب">
                            <span>🏷️</span> <span>توجيه بكود</span>
                        </button>

                        <!-- زر كشف حساب العميل -->
                        <a href="general-ledger.php?accountId=${encodeURIComponent(r.customer_id ? ('cust-' + String(r.customer_id).replace(/^cust-/, '')) : '')}&name=${encodeURIComponent(r.customer_name || '')}" style="height:32px; padding:0 8px; border-radius:7px; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.35); color:#7dd3fc; font-size:0.74rem; font-weight:800; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:3px;" title="فتح كشف حساب هذا العميل">
                            <span>📊</span> <span>كشف الحساب</span>
                        </a>

                        <!-- زر إشعار الزبون عبر واتساب -->
                        <button type="button" onclick="InboundBankakManager.openCustomerAlertModal(${r.id})" style="height:32px; padding:0 8px; border-radius:7px; background:linear-gradient(135deg, rgba(37,211,102,0.25), rgba(16,185,129,0.35)); border:1.2px solid #25d366; color:#a7f3d0; font-size:0.75rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="إرسال تنبيه رسمي للزبون عبر واتساب بالخلل أو التأكيد">
                            <span>📲</span> <span>إشعار</span>
                        </button>

                        <!-- زر حذف / تجاهل -->
                        <button type="button" onclick="InboundBankakManager.dismissReceipt(${r.id})" style="width:30px; height:32px; border-radius:7px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="أرشفة أو تجاهل الإشعار">
                            ✕
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    },

    findCustomerByCodeOrName: function(rawQuery) {
        if (!rawQuery) return null;
        const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        let q = String(rawQuery).trim();
        for (let i = 0; i < 10; i++) {
            q = q.replace(new RegExp(ar[i], 'g'), String(i)).replace(new RegExp(fa[i], 'g'), String(i));
        }
        const qLower = q.toLowerCase().replace(/^#/, '').trim();
        if (!qLower) return null;

        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch(e) { custs = []; }
        custs = custs.filter(c => c && !c.deleted);

        // 1. Exact match by receiptCodeNum or receiptCodeWord or both
        for (const c of custs) {
            const cNum = c.receiptCodeNum ? String(c.receiptCodeNum).trim() : '';
            const cWord = (c.receiptCodeWord || '').trim().toLowerCase();
            if (cNum && (qLower === cNum || qLower === ('كود ' + cNum))) return c;
            if (cWord && qLower === cWord) return c;
            if (cNum && cWord && (qLower === `${cNum} ${cWord}` || qLower === `${cWord} ${cNum}`)) return c;
        }
        // 2. Exact name match
        for (const c of custs) {
            if ((c.name || '').trim().toLowerCase() === qLower) return c;
        }
        // 3. Partial word code or name match
        for (const c of custs) {
            const cWord = (c.receiptCodeWord || '').trim().toLowerCase();
            if (cWord && qLower.split(/\s+/).includes(cWord)) return c;
        }
        return null;
    },

    onManualCodeOrNameInput: function(val) {
        const hintEl = document.getElementById('manualCustCodeHint');
        if (!hintEl) return;
        const matched = this.findCustomerByCodeOrName(val);
        if (matched) {
            hintEl.style.display = 'block';
            hintEl.innerHTML = `✅ تم التعرف على الحساب: <strong>${escapeHtml(matched.name)}</strong> (كود #${matched.receiptCodeNum || '-'}${matched.receiptCodeWord ? ' | ' + escapeHtml(matched.receiptCodeWord) : ''})`;
            const phoneEl = document.getElementById('manualCustPhone');
            if (phoneEl && !phoneEl.value && (matched.whatsapp || matched.phone)) {
                phoneEl.value = matched.whatsapp || matched.phone;
            }
        } else {
            hintEl.style.display = 'none';
            hintEl.innerHTML = '';
        }
    },

    lookupCustomerForAlertByCode: function(val) {
        const matched = this.findCustomerByCodeOrName(val);
        if (!matched) return;
        const targetPhone = matched.whatsapp || matched.phone || '';
        const nameEl = document.getElementById('cust-alert-name');
        const phoneHidden = document.getElementById('cust-alert-phone');
        const phoneDisp = document.getElementById('cust-alert-phone-display');
        if (nameEl) nameEl.textContent = `${matched.name} (#${matched.receiptCodeNum || '-'}${matched.receiptCodeWord ? ' | ' + matched.receiptCodeWord : ''})`;
        if (phoneHidden) phoneHidden.value = targetPhone;
        if (phoneDisp) phoneDisp.textContent = targetPhone || 'غير مسجل';
    },

    promptAssignByCode: async function(receiptId) {
        const r = this.receipts.find(x => Number(x.id) === Number(receiptId));
        if (!r) return;
        const codeInput = prompt(
            `🎯 توجيه الإشعار #${receiptId} إلى حساب عميل:\n` +
            `أدخل كود العميل الرقمي (مثال: 105) أو الكود النصي (مثال: بون) أو كليهما معاً أو اسم العميل:`,
            ''
        );
        if (!codeInput || !codeInput.trim()) return;

        let linkPhone = false;
        if (r.customer_phone && String(r.customer_phone).replace(/\D/g, '').length >= 7) {
            linkPhone = confirm(`هل تريد أيضاً ربط رقم المرسل (${r.customer_phone}) بحساب هذا العميل دائماً بحيث تنزل أي إشعارات قادمة من هذا الرقم في نفس الحساب تلقائياً؟`);
        }

        const tId = this.getActiveTenantId();
        try {
            const res = await fetch('api/inbound_receipts.php?action=assign_customer_by_code&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'assign_customer_by_code',
                    tenant_id: tId,
                    id: receiptId,
                    customer_code: codeInput.trim(),
                    link_sender_phone: linkPhone
                })
            });
            const data = await res.json();
            if (data && data.success) {
                if (data.customer && data.customer.id) {
                    try {
                        let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                        if (!Array.isArray(custs)) custs = [];
                        const idx = custs.findIndex(c => c && String(c.id) === String(data.customer.id));
                        if (idx > -1) custs[idx] = data.customer;
                        else custs.push(data.customer);
                        localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
                    } catch(e) {}
                }
                if (data.posting && data.posting.voucher_no) {
                    this.syncPostedReceiptToLocalErp(r, data.posting);
                }
                this.refreshReceipts();
                if (typeof showToast === 'function') {
                    showToast(data.message || '✅ تم توجيه الإشعار وتنزيله بالحساب بنجاح', 'success', 6000);
                } else {
                    alert(data.message);
                }
            } else {
                alert('⚠️ ' + (data?.error || 'تعذر العثور على العميل بهذا الكود'));
            }
        } catch (err) {
            alert('خطأ في الاتصال: ' + err.message);
        }
    },

    // ═══ فحص التزوير والتلاعب الجنائي عند الطلب فقط (On-Demand Forgery Inspection) ═══
    checkForgeryOnDemand: async function(receiptId, btnElement) {
        const origHtml = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span>⏳</span> <span>جاري الفحص الجنائي...</span>';
        }
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch(`api/inbound_receipts.php?action=check_forgery&id=${receiptId}&tenant_id=${encodeURIComponent(tId)}`);
            const data = await res.json();
            if (data.success && data.forgery_check) {
                const fc = data.forgery_check;
                const isSusp = fc.is_suspicious || Number(fc.tampering_score || 0) >= 50;
                const reasonsHtml = (Array.isArray(fc.tampering_reasons) && fc.tampering_reasons.length > 0)
                    ? fc.tampering_reasons.map(x => `<li style="margin-bottom:4px;">${escapeHtml(x)}</li>`).join('')
                    : '<li>لم يتم رصد أي آثار تعديل برمجي (Photoshop / Canva) في بيانات الصورة.</li><li>بنية الخطوط والأرقام متطابقة مع القالب القياسي للبنك.</li>';

                const modalId = 'forgery-report-modal';
                if (document.getElementById(modalId)) document.getElementById(modalId).remove();
                const div = document.createElement('div');
                div.id = modalId;
                div.style.cssText = 'position:fixed; inset:0; background:rgba(2,6,23,0.85); backdrop-filter:blur(8px); z-index:99999999; display:flex; align-items:center; justify-content:center; padding:16px; font-family:Tajawal,sans-serif; direction:rtl;';
                div.innerHTML = `
                    <div style="width:100%; max-width:480px; background:linear-gradient(145deg, #0f172a, #1e293b); border:2px solid ${isSusp ? '#ef4444' : '#10b981'}; border-radius:18px; padding:20px 22px; color:#fff; box-shadow:0 25px 60px rgba(0,0,0,0.9);">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.12); padding-bottom:10px; margin-bottom:12px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:1.6rem;">${isSusp ? '🚨' : '🛡️'}</span>
                                <div>
                                    <h3 style="margin:0; font-size:1.1rem; font-weight:900; color:${isSusp ? '#fca5a5' : '#34d399'};">تقرير فحص التزوير الجنائي (عند الطلب)</h3>
                                    <span style="font-size:0.75rem; color:#94a3b8;">إشعار رقم #${receiptId} — ${escapeHtml(data.data?.customer_name || '')}</span>
                                </div>
                            </div>
                            <button type="button" onclick="document.getElementById('${modalId}').remove()" style="background:none; border:none; color:#94a3b8; font-size:1.2rem; cursor:pointer;">✕</button>
                        </div>
                        <div style="background:rgba(0,0,0,0.35); border-radius:10px; padding:10px 14px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:0.85rem; color:#cbd5e1; font-weight:700;">درجة الاشتباه بالتلاعب:</span>
                            <strong style="font-size:1.15rem; color:${isSusp ? '#ef4444' : '#10b981'}; font-family:Consolas,monospace;">${Number(fc.tampering_score || 0)}% (${isSusp ? 'اشتباه تزوير' : 'سليم وأصلي'})</strong>
                        </div>
                        <div style="font-size:0.85rem; color:#e2e8f0; line-height:1.6; margin-bottom:16px;">
                            <strong style="color:#38bdf8; display:block; margin-bottom:4px;">نتائج الفحص الجنائي للصورة:</strong>
                            <ul style="margin:0; padding-right:18px;">${reasonsHtml}</ul>
                        </div>
                        <div style="display:flex; justify-content:flex-end;">
                            <button type="button" onclick="document.getElementById('${modalId}').remove()" style="height:36px; padding:0 20px; border-radius:8px; background:${isSusp ? '#ef4444' : '#10b981'}; border:none; color:#fff; font-weight:900; cursor:pointer;">إغلاق التقرير</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(div);
                this.refreshReceipts(true);
            } else {
                alert(data.error || 'تعذر إجراء فحص التزوير');
            }
        } catch (e) {
            alert('خطأ أثناء فحص التزوير: ' + e.message);
        } finally {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = origHtml || '<span>🕵️</span> <span>فحص التزوير</span>';
            }
        }
    },

    // ═══ تنزيل فوري في الحسابات بنقرة واحدة ═══
    postToAccountsDirect: async function(receiptId, btnElement) {
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span>🔄</span> <span>جاري التنزيل...</span>';
        }
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch(`api/inbound_receipts.php?action=post_to_accounts&id=${receiptId}&tenant_id=${encodeURIComponent(tId)}`);
            const data = await res.json();
            if (data.success && data.voucher_no) {
                const r = this.receipts.find(x => x.id == receiptId) || { id: receiptId, voucher_no: data.voucher_no };
                r.voucher_no = data.voucher_no;
                this.syncPostedReceiptToLocalErp(r, data);
                this.refreshReceipts();
                if (typeof showToast === 'function') {
                    showToast(`✅ تم تنزيل الإشعار في حساب العميل (${data.customer_name}) وحساب البنك بنجاح بالسند رقم (${data.voucher_no})!`, 'success');
                }
            } else {
                alert(data.error || 'تعذر تنزيل الإشعار في الحسابات');
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<span>⚡</span> <span>تنزيل بالحسابات</span>';
                }
            }
        } catch (e) {
            alert('خطأ أثناء التنزيل في الحسابات: ' + e.message);
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = '<span>⚡</span> <span>تنزيل بالحسابات</span>';
            }
        }
    },

    // ═══ فحص سريع بالذكاء الاصطناعي من داخل الصينية ═══
    triggerOCR: async function(receiptId, btnElement) {
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span>🔄</span> <span>جاري الفحص...</span>';
        }
        try {
            const res = await fetch(`api/inbound_receipts.php?action=scan_receipt&id=${receiptId}`);
            const data = await res.json();
            if (data.success && data.data) {
                if (data.data.posting && data.data.posting.voucher_no) {
                    this.syncPostedReceiptToLocalErp(data.data, data.data.posting);
                }
                if (typeof showToast === 'function') {
                    const vMsg = data.data.voucher_no ? ` وتم تنزيله في الحسابات بالسند (${data.data.voucher_no})` : '';
                    showToast(`✅ تم فحص الإشعار بالـ AI: المبلغ (${parseFloat(data.data.amount||0).toLocaleString()} SDG) برقم (${data.data.transfer_no})${vMsg}`, 'success');
                }
                this.refreshReceipts();
            } else {
                alert(data.error || 'فشل فحص الإشعار بالذكاء الاصطناعي');
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<span>⚡</span> <span>فحص AI</span>';
                }
            }
        } catch(e) {
            alert('خطأ في الاتصال بالذكاء الاصطناعي: ' + e.message);
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = '<span>⚡</span> <span>فحص AI</span>';
            }
        }
    },

    // ═══ الفحص الذكي والتنزيل في السند المتعدد والحسابات (Deep Link & Auto-Fill in Multi-Cash-Payment) ═══
    inspectInVoucher: async function(receiptId) {
        let r = this.receipts.find(x => x.id == receiptId);
        if (!r) return;

        const tId = this.getActiveTenantId();

        // 1. If amount is 0 or transfer_no is missing, scan it now via API
        if ((!r.amount || parseFloat(r.amount) <= 0 || !r.transfer_no) && r.receipt_image) {
            if (typeof showToast === 'function') {
                showToast('⏳ جاري فحص الإشعار بالذكاء الاصطناعي واستخراج المبلغ وتنزيله بالحسابات...', 'info');
            }
            try {
                const scanRes = await fetch(`api/inbound_receipts.php?action=scan_receipt&id=${r.id}&tenant_id=${encodeURIComponent(tId)}`);
                const scanData = await scanRes.json();
                if (scanData.success && scanData.data) {
                    r = Object.assign(r, scanData.data);
                    if (scanData.data.posting && scanData.data.posting.voucher_no) {
                        this.syncPostedReceiptToLocalErp(r, scanData.data.posting);
                    }
                    this.refreshReceipts(true);
                }
            } catch (e) {
                console.warn('Scan receipt failed:', e);
            }
        }

        // 2. If receipt is valid and not yet posted to accounts, post it now!
        let postDetails = null;
        if (r.status !== 'duplicate_rejected' && parseFloat(r.amount || 0) > 0 && !r.voucher_no) {
            try {
                const postRes = await fetch(`api/inbound_receipts.php?action=post_to_accounts&id=${r.id}&tenant_id=${encodeURIComponent(tId)}`);
                const postData = await postRes.json();
                if (postData && postData.success && postData.voucher_no) {
                    r.voucher_no = postData.voucher_no;
                    r.customer_id = postData.customer_id || r.customer_id;
                    r.customer_name = postData.customer_name || r.customer_name;
                    postDetails = postData;
                    this.syncPostedReceiptToLocalErp(r, postData);
                    this.refreshReceipts(true);
                }
            } catch (e) {
                console.warn('Auto post to accounts warning:', e);
            }
        } else if (r.voucher_no) {
            this.syncPostedReceiptToLocalErp(r);
        }

        // 3. Close drawer & remember active inbound receipt ID
        this.closeDrawer();
        window._activeInboundReceiptId = r.id;

        // 4. Set Voucher Type to Receipt (سند قبض / استلام من عميل)
        if (typeof window.setVoucherType === 'function') {
            window.setVoucherType('receipt');
        } else {
            const vTypeEl = document.getElementById('voucher-type');
            if (vTypeEl) vTypeEl.value = 'receipt';
        }

        // 5. Dynamically resolve the real .account-block data-block-id (NEVER hardcode '1')
        setTimeout(() => {
            const container = document.getElementById('accounts-blocks-container');
            let targetBlock = container ? container.querySelector('.account-block') : document.querySelector('.account-block');
            if (!targetBlock && typeof window.addAccountBlock === 'function') {
                window.addAccountBlock();
                targetBlock = container ? container.querySelector('.account-block') : document.querySelector('.account-block');
            }
            if (!targetBlock) return;

            const blockId = targetBlock.getAttribute('data-block-id');

            // Set voucher number if already assigned in DB so saving updates the same voucher
            if (r.voucher_no) {
                const vNoEl = document.getElementById('voucher-no-display');
                if (vNoEl) vNoEl.value = r.voucher_no;
            }

            // Rebuild ACCOUNTS so newly matched/created customer & bank are available
            if (typeof window.buildAccountsArray === 'function') {
                try { window.buildAccountsArray(); } catch (e) {}
            }

            // 6. Populate Main Account (Receiving Bank)
            const mainAccInput = document.getElementById('main-account');
            const mainAccSearch = document.getElementById('main-account-search');
            if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
                let bankAcc = ACCOUNTS.find(a => a.id && a.id.startsWith('bank-') && (
                    (r.bank_name && a.name && r.bank_name.includes(a.name)) ||
                    (a.name && a.name.includes('الخرطوم'))
                ));
                if (!bankAcc) {
                    bankAcc = ACCOUNTS.find(a => a.id && a.id.startsWith('bank-'));
                }
                if (bankAcc) {
                    if (mainAccInput) {
                        mainAccInput.value = bankAcc.id;
                        mainAccInput.setAttribute('data-id', bankAcc.id);
                    }
                    if (mainAccSearch) {
                        mainAccSearch.value = bankAcc.name;
                    }
                }
            }

            // 7. Populate Customer Account in Block
            const rawCustId = String(r.customer_id || '').replace(/^cust-/, '');
            let fullCustAccId = rawCustId ? ('cust-' + rawCustId) : '';
            if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
                const matchedCustAcc = ACCOUNTS.find(a =>
                    (fullCustAccId && String(a.id) === fullCustAccId) ||
                    (r.customer_name && a.name && a.name.trim() === r.customer_name.trim())
                );
                if (matchedCustAcc) {
                    fullCustAccId = matchedCustAcc.id;
                }
            }

            const accSearch = document.getElementById(`account-${blockId}-search`);
            const accHidden = document.getElementById(`account-${blockId}`);
            const accId = document.getElementById(`account-${blockId}-id`);
            if (accSearch) {
                accSearch.value = r.customer_name || '';
            }
            if (accHidden) {
                accHidden.value = fullCustAccId || r.customer_name || '';
            }
            if (accId) {
                accId.value = fullCustAccId || r.customer_name || '';
            }

            // 8. Activate Bank Payment Method in targetBlock (and deactivate Cash if active)
            const btnCash = document.getElementById(`pm-cash-${blockId}`);
            const btnBank = document.getElementById(`pm-bank-${blockId}`);
            if (btnCash && btnCash.classList.contains('active') && typeof window.toggleBlockPayment === 'function') {
                window.toggleBlockPayment(blockId, 'cash');
            }
            if (btnBank && !btnBank.classList.contains('active')) {
                if (typeof window.toggleBlockPayment === 'function') {
                    window.toggleBlockPayment(blockId, 'bank');
                } else {
                    btnBank.click();
                }
            }

            // Ensure sub-fields and sub-bank containers are visible
            const subFields = document.getElementById(`sub-fields-${blockId}`);
            const subBank = document.getElementById(`sub-bank-${blockId}`);
            if (subFields) subFields.style.display = 'block';
            if (subBank) subBank.style.display = 'block';

            // 9. Fill Bank Transfer Row Details & Totals
            setTimeout(() => {
                let bankRow = targetBlock.querySelector('.bank-row');
                if (!bankRow && typeof window.addBankRow === 'function') {
                    window.addBankRow(blockId);
                    bankRow = targetBlock.querySelector('.bank-row');
                }

                const cleanAmt = parseFloat(r.amount || 0) || 0;
                const formattedAmtStr = (typeof fmtNum === 'function') ? fmtNum(cleanAmt) : cleanAmt.toLocaleString('en-US');

                if (bankRow) {
                    const bAmt = bankRow.querySelector('.block-bank-amount');
                    if (bAmt && cleanAmt > 0) {
                        bAmt.value = formattedAmtStr;
                        bAmt.dispatchEvent(new Event('input', { bubbles: true }));
                        bAmt.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const bTransfer = bankRow.querySelector('.block-transfer-no-input');
                    if (bTransfer) {
                        bTransfer.value = r.transfer_no || '';
                        bTransfer.dispatchEvent(new Event('input', { bubbles: true }));
                        bTransfer.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const bName = bankRow.querySelector('.block-bank-name-input');
                    if (bName) {
                        bName.value = r.bank_name || 'بنك الخرطوم (بنكك)';
                        bName.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    const bDesc = bankRow.querySelector('.block-bank-desc-input');
                    if (bDesc) {
                        bDesc.value = `إشعار وارد #${r.id} - ${r.customer_name || ''}`;
                    }
                }

                const mainBlockAmt = document.getElementById(`amount-${blockId}`);
                if (mainBlockAmt && cleanAmt > 0) {
                    mainBlockAmt.value = formattedAmtStr;
                }

                const stmtInput = document.getElementById(`statement-${blockId}`);
                if (stmtInput) {
                    stmtInput.value = `سداد إشعار ${r.bank_name || 'بنكك'} رقم العملية ${r.transfer_no || ''}`;
                }

                const vDescInput = document.getElementById('voucher-desc');
                if (vDescInput) {
                    vDescInput.value = `إشعار تحويل بنكي (${r.bank_name || 'بنكك'}) رقم العملية: ${r.transfer_no || ''} - العميل: ${r.customer_name || ''}`;
                }

                if (typeof window.recalcBlockTotal === 'function') {
                    window.recalcBlockTotal(blockId);
                }
                if (typeof window.updateBlockTafqeetAndTier === 'function') {
                    window.updateBlockTafqeetAndTier(blockId);
                }
                if (typeof window.calcTotal === 'function') {
                    window.calcTotal();
                }

                // If duplicate, show alert banner
                if (r.status === 'duplicate_rejected') {
                    if (typeof showCenterError === 'function') {
                        showCenterError(`⚠️ تنبيه رقابي صارم: إشعار التحويل برقم ( ${r.transfer_no} ) للعميل ( ${r.customer_name} ) تم صرفه مسبقاً!<br><br>استخدم زر (إشعار الزبون) لإبلاغه فورياً عبر واتساب.`, 'duplicate');
                    }
                } else if (typeof showToast === 'function') {
                    const vInfo = r.voucher_no ? ` وتم تنزيله في الحسابات بالسند (${r.voucher_no})` : '';
                    showToast(`✅ تم فحص إشعار (${r.customer_name}) بمبلغ (${cleanAmt.toLocaleString()} SDG) وتفريغه بالسند${vInfo}!`, 'success');
                }
            }, 200);
        }, 120);
    },

    inspectById: function(rId) {
        if (!rId) return;
        const tId = this.getActiveTenantId();
        fetch('api/inbound_receipts.php?action=list&tenant_id=' + encodeURIComponent(tId))
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    this.receipts = res.data;
                    this.inspectInVoucher(rId);
                }
            });
    },

    // ═══ نافذة إخطار الزبون عبر واتساب (Customer Feedback Loop) ═══
    openCustomerAlertModal: function(receiptId) {
        const r = this.receipts.find(x => x.id == receiptId);
        if (!r) return;
        this.currentReceiptForAlert = r;

        document.getElementById('cust-alert-receipt-id').value = r.id;
        document.getElementById('cust-alert-phone').value = r.customer_phone || '';
        document.getElementById('cust-alert-name').textContent = r.customer_name;
        document.getElementById('cust-alert-phone-display').textContent = r.customer_phone || 'غير مسجل';

        // Select initial template based on receipt status
        const initType = (r.status === 'duplicate_rejected') ? 'duplicate' : ((r.status === 'verified' || r.status === 'reconciled') ? 'reconciled' : 'unclear');
        const activeBtn = document.querySelector(`.alert-type-btn[data-type="${initType}"]`) || document.querySelector('.alert-type-btn');
        this.selectAlertTemplate(initType, activeBtn);

        const modal = document.getElementById('inbound-customer-alert-modal');
        if (modal) modal.style.display = 'flex';
    },

    closeCustomerAlertModal: function() {
        const modal = document.getElementById('inbound-customer-alert-modal');
        if (modal) modal.style.display = 'none';
        this.currentReceiptForAlert = null;
    },

    selectAlertTemplate: function(type, btn) {
        document.querySelectorAll('.alert-type-btn').forEach(b => {
            b.classList.remove('active');
            b.style.borderColor = 'rgba(255,255,255,0.15)';
            b.style.background = 'rgba(255,255,255,0.04)';
            b.style.color = '#94a3b8';
        });
        if (btn) {
            btn.classList.add('active');
            if (type === 'duplicate') {
                btn.style.borderColor = '#ef4444';
                btn.style.background = 'rgba(239,68,68,0.2)';
                btn.style.color = '#fca5a5';
            } else if (type === 'reconciled') {
                btn.style.borderColor = '#10b981';
                btn.style.background = 'rgba(16,185,129,0.2)';
                btn.style.color = '#a7f3d0';
            } else {
                btn.style.borderColor = '#f59e0b';
                btn.style.background = 'rgba(245,158,11,0.2)';
                btn.style.color = '#fde047';
            }
        }

        const r = this.currentReceiptForAlert || {};
        const custName = r.customer_name || 'عزيزنا العميل';
        const tNo = r.transfer_no || '---';
        const amt = r.amount ? parseFloat(r.amount).toLocaleString('en-US') : '0.00';
        const curr = r.currency || 'SDG';
        const vNoLine = r.voucher_no ? `\n• رقم سند القبض: *${r.voucher_no}*` : '';

        let msg = '';
        if (type === 'duplicate') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\nنود إحاطتكم بأن إشعار التحويل البنكي المرسل برقم العملية: *(${tNo})*\n⚠️ *مكرر وسبق تسجيله واعتماده في نظامنا المالي مسبقاً*.\n\nيرجى التكرم بمراجعة حسابك البنكي وإرسال إشعار التحويل الصحيح حتى نتمكن من قيد المبلغ في حسابكم.\n\nشاكرين ومقدرين حسن تعاونكم،\n*إدارة الحسابات والمالية* 🏢`;
        } else if (type === 'unclear') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\nبخصوص إشعار التحويل البنكي الأخير، تعذر على نظام الفحص المالي قراءة البيانات بدقة لعدم وضوح الصورة أو اقتصاص رقم العملية أو المبلغ.\n\nيرجى التكرم بإعادة إرسال لقطة شاشة واضحة وكاملة للإشعار ليتسنى لنا اعتمادها فوراً.\n\nشكراً لتعاملكم الراقي،\n*إدارة الحسابات والمالية* 🏢`;
        } else if (type === 'amount_mismatch') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\nنود إعلامكم بأن المبلغ الوارد في إشعار التحويل هو: *(${amt} ${curr})* برقم عملية *(${tNo})*، ويوجد تباين مع الرصيد المطلوب للمطابقة.\n\nيرجى مراجعة الإدارة المالية لتسوية الفاتورة.\n\nمع فائق الاحترام والتقدير،\n*إدارة الحسابات* 🏢`;
        } else if (type === 'reconciled') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\n✅ *تم بنجاح استلام وفحص وتنزيل تحويلكم البنكي في الحسابات*:\n• رقم العملية: *${tNo}*\n• المبلغ المقيد: *${amt} ${curr}*${vNoLine}\n• تم قيد المبلغ بحسابكم وإصدار سند القبض الرسمي.\n\nسعداء دائماً بخدمتكم،\n*إدارة الحسابات والمالية* 🏢✨`;
        }

        const txtArea = document.getElementById('cust-alert-message-text');
        if (txtArea) txtArea.value = msg;
    },

    dispatchWhatsAppAlert: async function() {
        const phone = document.getElementById('cust-alert-phone')?.value || '';
        const msg = document.getElementById('cust-alert-message-text')?.value || '';

        if (!phone) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال رقم هاتف العميل أولاً!', 'warning');
            else alert('رقم هاتف العميل غير مسجل! يرجى إدخال رقم الهاتف أولاً.');
            return;
        }

        // Clean and format phone for WhatsApp
        let cleanPhone = phone.replace(/\D/g, '');
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '249' + cleanPhone.substring(1);
        } else if (cleanPhone.length === 9) {
            cleanPhone = '249' + cleanPhone;
        }

        const sendBtn = document.getElementById('btn-cust-alert-send-wa');
        const origBtnText = sendBtn ? sendBtn.innerHTML : '';
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span>⏳ جاري الإرسال المباشر عبر الواتساب...</span>';
        }

        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));

        try {
            const formData = new FormData();
            formData.append('userId', userId);
            formData.append('phone', cleanPhone);
            formData.append('message', msg);

            const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data && data.success) {
                this.closeCustomerAlertModal();
                if (typeof showToast === 'function') {
                    showToast(`⚡ تم إرسال التنبيه مباشرة للعميل (${cleanPhone}) عبر واتساب بنجاح دون فتح أي تطبيقات!`, 'success', 6500);
                }
            } else {
                throw new Error(data?.error || 'فشل إرسال الرسالة من خادم الواتساب');
            }
        } catch (err) {
            console.error('WA Direct Alert Error:', err);
            if (typeof showToast === 'function') {
                showToast(`⚠️ تعذر الإرسال المباشر: ${err.message}. يرجى التأكد من ربط خادم الواتساب.`, 'error', 7000);
            } else {
                alert(`⚠️ تعذر الإرسال: ${err.message}`);
            }
        } finally {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = origBtnText;
            }
        }
    },

    dismissReceipt: function(id) {
        if (!confirm('هل تريد بالتأكيد أرشفة أو تجاهل هذا الإشعار من قائمة الانتظار؟')) return;
        const tId = this.getActiveTenantId();
        fetch('api/inbound_receipts.php?action=dismiss&tenant_id=' + encodeURIComponent(tId), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, tenant_id: tId })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                this.refreshReceipts();
                if (typeof showToast === 'function') showToast('تمت أرشفة الإشعار بنجاح', 'info');
            }
        });
    },

    openDirectUploadModal: function() {
        const modal = document.getElementById('inbound-manual-upload-modal');
        if (modal) modal.style.display = 'flex';
    },

    closeManualUploadModal: function() {
        const modal = document.getElementById('inbound-manual-upload-modal');
        if (modal) modal.style.display = 'none';
    },

    handleManualUploadSubmit: async function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('manualReceiptFile');
        const amtVal = document.getElementById('manualAmount').value.trim();
        const tnoVal = document.getElementById('manualTransferNo').value.trim();
        const hasFile = fileInput && fileInput.files && fileInput.files[0];

        if (!hasFile && (!amtVal || !tnoVal)) {
            alert('يرجى إما اختيار صورة الإشعار لفحصها بالذكاء الاصطناعي أو إدخال المبلغ ورقم العملية.');
            return;
        }

        const btn = document.getElementById('btnSubmitManualUpload');
        btn.disabled = true;
        btn.innerHTML = 'جاري الفحص والتنزيل في الحسابات... ⏳';

        const tId = this.getActiveTenantId();
        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('tenant_id', tId);
        formData.append('customer_name', document.getElementById('manualCustName').value.trim());
        formData.append('amount', amtVal);
        formData.append('transfer_no', tnoVal);
        formData.append('customer_phone', document.getElementById('manualCustPhone').value.trim());

        if (hasFile) {
            formData.append('receipt_file', fileInput.files[0]);
        }

        try {
            const res = await fetch('api/inbound_receipts.php?action=upload', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (data.posting && data.posting.voucher_no) {
                    this.syncPostedReceiptToLocalErp(data.receipt || data, data.posting);
                }
                this.closeManualUploadModal();
                this.refreshReceipts();
                this.playNotificationChime();
                if (typeof showToast === 'function') {
                    const msg = data.voucher_no
                        ? `✅ تم فحص الإشعار وتنزيله في الحسابات بنجاح بالسند رقم (${data.voucher_no})!`
                        : '✅ تم حفظ الإشعار بنجاح ونزل في صندوق العمليات!';
                    showToast(msg, 'success');
                }
                // Also populate into current voucher screen immediately
                if (data.id) {
                    setTimeout(() => this.inspectInVoucher(data.id), 300);
                }
            } else {
                alert('خطأ: ' + (data.error || 'فشل الحفظ'));
            }
        } catch(err) {
            alert('حدث خطأ في الاتصال: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'فحص وتنزيل في الحسابات 🚀';
        }
    },

    // ═══ محاكاة إشعار بنكك فوري للاختبار والتجربة الحية ═══
    simulateIncomingReceipt: async function() {
        const sampleNames = [
            'مهدي مكي',
            'همس مهدي شيريهان مكي',
            'دكتور شول',
            'مؤسسة التضامن الهندسية',
            'شركة الرواد للاستيراد والتصدير'
        ];
        const randomName = sampleNames[Math.floor(Math.random() * sampleNames.length)];
        const randomAmt = (Math.floor(Math.random() * 50) + 10) * 10000; // e.g. 100,000 to 600,000
        const randomNo = String(Math.floor(Math.random() * 89999999) + 10000000); // 8-digit transfer no
        const tId = this.getActiveTenantId();

        try {
            const res = await fetch('api/inbound_receipts.php?action=upload', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'upload',
                    tenant_id: tId,
                    customer_name: randomName,
                    customer_phone: '09' + Math.floor(Math.random() * 89999999 + 10000000),
                    amount: randomAmt,
                    transfer_no: randomNo,
                    bank_name: 'بنك الخرطوم (بنكك)',
                    bank_code: '170',
                    receipt_image: 'assets/sample_receipt_bok.png',
                    notes: 'تحويل بنكك تم استلامه وفحصه وتنزيله في الحسابات فورياً'
                })
            });
            const data = await res.json();
            if (data.success) {
                if (data.posting && data.posting.voucher_no) {
                    this.syncPostedReceiptToLocalErp(data.receipt || data, data.posting);
                }
                // Instantly refresh & trigger live sound and toast
                this.refreshReceipts();
            }
        } catch(err) {
            console.error('Simulation error:', err);
        }
    }
};

// Global Helper to toggle drawer
window.toggleInboundReceiptsDrawer = function() {
    window.InboundBankakManager.toggleDrawer();
};

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Auto-initialize when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.InboundBankakManager.init());
} else {
    window.InboundBankakManager.init();
}
</script>
