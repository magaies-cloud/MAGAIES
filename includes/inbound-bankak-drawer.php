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
    <div id="inbound-bankak-drawer-panel" style="width:100%; max-width:650px; height:100vh; background:linear-gradient(145deg, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 0.99) 100%); border-left:2px solid rgba(56, 189, 248, 0.35); box-shadow:-15px 0 50px rgba(0,0,0,0.85), 0 0 30px rgba(56,189,248,0.2); display:flex; flex-direction:column; color:#fff; font-family:'Tajawal',sans-serif; direction:rtl; box-sizing:border-box; position:relative; overflow:hidden; animation:slideLeftDrawer 0.35s cubic-bezier(0.16, 1, 0.3, 1);">

        <!-- Ambient Glow Elements -->
        <div style="position:absolute; top:-100px; left:-100px; width:300px; height:300px; background:radial-gradient(circle, rgba(56,189,248,0.18) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>
        <div style="position:absolute; bottom:-100px; right:-100px; width:300px; height:300px; background:radial-gradient(circle, rgba(16,185,129,0.15) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>

        <!-- 1. Drawer Header -->
        <div style="padding:14px 18px 10px 18px; border-bottom:1.5px solid rgba(255,255,255,0.1); background:rgba(15,23,42,0.75); display:flex; flex-direction:column; gap:9px; flex-shrink:0;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(16,185,129,0.25)); border:1.8px solid #38bdf8; display:flex; align-items:center; justify-content:center; font-size:1.4rem; box-shadow:0 0 15px rgba(56,189,248,0.35);">
                        📥
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.14rem; font-weight:900; color:#f8fafc; display:flex; align-items:center; gap:8px;">
                            <span>صينية استلام ومراجعة وتمرير إشعارات الواتساب</span>
                            <span id="drawer-live-pulse" style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 8px #10b981; animation:pulseScale 1.4s infinite alternate;"></span>
                        </h3>
                        <span style="font-size:0.76rem; color:#94a3b8; font-weight:700;">فحص فوري (≥ 6 أرقام + منع التكرار) • تحديد البنك المستلم • تمرير فردي أو جماعي</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <a href="posting-accounts.php?filter=whatsapp" style="background:rgba(168,85,247,0.2); border:1px solid rgba(168,85,247,0.45); color:#e9d5ff; border-radius:8px; height:34px; padding:0 10px; text-decoration:none; display:inline-flex; align-items:center; gap:5px; font-size:0.76rem; font-weight:800;" title="فتح الإشعارات في شاشة الترحيل المحاسبية">
                        <span>🚀</span> <span>شاشة الترحيل</span>
                    </a>
                    <button type="button" onclick="InboundBankakManager.refreshReceipts()" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#7dd3fc; border-radius:8px; width:34px; height:34px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.05rem;" title="تحديث القائمة الآن">
                        🔄
                    </button>
                    <button type="button" onclick="InboundBankakManager.closeDrawer()" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#fca5a5; border-radius:8px; width:34px; height:34px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.15rem; font-weight:900;" title="إغلاق الصينية">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Target Bank Selection & Bulk Pass Bar -->
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; background:rgba(15,23,42,0.9); border:1px solid rgba(245,158,11,0.35); padding:7px 10px; border-radius:10px;">
                <div style="display:flex; align-items:center; gap:6px; flex:1; min-width:210px;">
                    <span style="font-size:0.76rem; color:#fcd34d; font-weight:900; white-space:nowrap;">🏦 البنك المستلم:</span>
                    <select id="drawer-target-bank-select" onchange="InboundBankakManager.onDefaultDrawerBankChange(this.value)" style="flex:1; height:30px; background:rgba(0,0,0,0.45); color:#fff; border:1px solid rgba(245,158,11,0.45); border-radius:7px; padding:0 8px; font-family:'Tajawal',sans-serif; font-size:0.78rem; font-weight:800;">
                        <option value="">🏦 تلقائي (بنك الخرطوم / حسب حساب العميل)</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <button type="button" onclick="InboundBankakManager.approveAllPendingReceipts(this)" style="height:30px; padding:0 10px; border-radius:7px; background:linear-gradient(135deg, #10b981, #059669); border:1px solid #34d399; color:#fff; font-weight:900; font-size:0.75rem; cursor:pointer; display:inline-flex; align-items:center; gap:4px; box-shadow:0 0 10px rgba(16,185,129,0.3);" title="تمرير وترحيل جميع الإشعارات السليمة التي بانتظار المراجعة إلى الحسابات والبنك المحدد">
                        <span>✔️</span> <span>تمرير الكل للحسابات</span>
                    </button>
                    <button type="button" onclick="InboundBankakManager.loadBatchIntoMultiVoucher()" style="height:30px; padding:0 10px; border-radius:7px; background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#e0f2fe; font-weight:800; font-size:0.75rem; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" title="تفريغ دفعة الإشعارات دفعة واحدة داخل شاشة حركة مالية مجمعة (كأنك أدخلتها يدوياً)">
                        <span>📥</span> <span>إدراج الدفعة في حركة مجمعة</span>
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div style="display:flex; gap:6px; align-items:center; justify-content:space-between; background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.08); padding:6px 10px; border-radius:10px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:4px; font-size:0.76rem; font-weight:800; color:#a7f3d0;">
                    <span>🟢 معتمد ومرحّل:</span>
                    <strong id="stat-verified-count" style="font-family:'Consolas',monospace; font-size:0.9rem;">0</strong>
                </div>
                <div style="display:flex; align-items:center; gap:4px; font-size:0.76rem; font-weight:800; color:#fcd34d;">
                    <span>👁️ بانتظار التمرير:</span>
                    <strong id="stat-awaiting-count" style="font-family:'Consolas',monospace; font-size:0.9rem;">0</strong>
                </div>
                <div style="display:flex; align-items:center; gap:4px; font-size:0.76rem; font-weight:800; color:#fca5a5;">
                    <span>🔴 مكرر مرفوض:</span>
                    <strong id="stat-duplicate-count" style="font-family:'Consolas',monospace; font-size:0.9rem;">0</strong>
                </div>
                <div style="display:flex; align-items:center; gap:4px; font-size:0.76rem; font-weight:800; color:#fef08a;">
                    <span>🟡 قيد الفحص:</span>
                    <strong id="stat-pending-count" style="font-family:'Consolas',monospace; font-size:0.9rem;">0</strong>
                </div>
            </div>

            <!-- Tab Filters -->
            <div style="display:flex; gap:5px; margin-top:2px; flex-wrap:wrap;">
                <button type="button" class="drawer-tab-btn active" data-filter="all" onclick="InboundBankakManager.filterTab('all', this)" style="flex:1; min-width:80px; padding:6px 6px; border-radius:8px; border:1px solid rgba(56,189,248,0.4); background:rgba(56,189,248,0.2); color:#e0f2fe; font-weight:900; font-size:0.78rem; cursor:pointer;">
                    الكل (<span id="tab-all-count">0</span>)
                </button>
                <button type="button" class="drawer-tab-btn" data-filter="awaiting_approval" onclick="InboundBankakManager.filterTab('awaiting_approval', this)" style="flex:1.2; min-width:110px; padding:6px 6px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:#fcd34d; font-weight:800; font-size:0.78rem; cursor:pointer;">
                    👁️ بانتظار التمرير (<span id="tab-awaiting-count">0</span>)
                </button>
                <button type="button" class="drawer-tab-btn" data-filter="verified" onclick="InboundBankakManager.filterTab('verified', this)" style="flex:1; min-width:95px; padding:6px 6px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.78rem; cursor:pointer;">
                    🟢 مرحّل بالحساب
                </button>
                <button type="button" class="drawer-tab-btn" data-filter="duplicate_rejected" onclick="InboundBankakManager.filterTab('duplicate_rejected', this)" style="flex:0.9; min-width:75px; padding:6px 6px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; font-size:0.78rem; cursor:pointer;">
                    🔴 مكرر
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
                <label style="font-size:0.80rem; color:#38bdf8; font-weight:800; margin-bottom:4px; display:block;">👤 اسم العميل أو كود الحساب الرقمي (مثال: 105): *</label>
                <input type="text" id="manualCustName" required oninput="InboundBankakManager.onManualCodeOrNameInput(this.value)" placeholder="مثال: 105 أو اسم العميل" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.4); border:1px solid rgba(56,189,248,0.4); border-radius:8px; padding:8px 12px; color:#fff; font-weight:800; font-family:'Tajawal',sans-serif;">
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
                    <span style="font-size:0.75rem; color:#94a3b8; font-weight:700;">ابحث بالاسم أو الباسورد السري أو عدّل رقم الواتساب (يُحفظ تلقائياً للعميل)</span>
                </div>
            </div>
            <button type="button" onclick="InboundBankakManager.closeCustomerAlertModal()" style="background:none; border:none; color:#94a3b8; font-size:1.3rem; cursor:pointer; font-weight:900;">✕</button>
        </div>

        <input type="hidden" id="cust-alert-receipt-id">
        <input type="hidden" id="cust-alert-customer-id">

        <!-- Quick Lookup by Customer Password/Code or Name -->
        <div style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.35); border-radius:10px; padding:8px 12px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; position:relative;">
            <span style="font-size:0.78rem; color:#34d399; font-weight:800;">🎯 اختيار العميل (بالاسم أو الباسورد):</span>
            <input type="text" id="cust-alert-code-lookup" autocomplete="off" oninput="InboundBankakManager.lookupCustomerForAlertByCode(this.value)" onfocus="InboundBankakManager.lookupCustomerForAlertByCode(this.value)" placeholder="اكتب باسورد العميل (مثلاً: 127) أو اسمه لجلب رقم الواتساب تلقائياً..." style="flex:1; min-width:180px; background:rgba(0,0,0,0.45); border:1px solid rgba(56,189,248,0.4); border-radius:7px; padding:6px 10px; color:#fff; font-family:'Tajawal',sans-serif; font-size:0.84rem; font-weight:800;">
            <div id="cust-alert-autocomplete" style="display:none; position:absolute; top:100%; right:0; left:0; background:#0f172a; border:1.5px solid #38bdf8; border-radius:10px; max-height:190px; overflow-y:auto; z-index:99999; margin-top:4px; box-shadow:0 12px 30px rgba(0,0,0,0.85);"></div>
        </div>

        <!-- Target Customer Info & Editable WhatsApp Phone -->
        <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <div style="flex:1; min-width:160px;">
                <span style="font-size:0.75rem; color:#94a3b8;">العميل المستهدف:</span>
                <strong id="cust-alert-name" style="font-size:0.95rem; color:#ffd700; margin-right:4px;"></strong>
                <span id="cust-alert-phone-display" style="display:none;"></span>
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex:1.2; min-width:210px;">
                <span style="font-size:0.76rem; color:#34d399; font-weight:800; white-space:nowrap;">📱 رقم واتساب العميل:</span>
                <input type="tel" id="cust-alert-phone" placeholder="09xxxxxxxx أو 01xxxxxxxx" style="flex:1; background:rgba(0,0,0,0.55); border:1.5px solid rgba(37,211,102,0.55); border-radius:8px; padding:5px 10px; color:#7dd3fc; font-family:'Consolas',monospace; font-size:0.92rem; font-weight:900; direction:ltr; text-align:right;">
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
                <span>🚀 حفظ الرقم وإرسال لواتساب الزبون الآن</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal: توجيه الإشعار لحساب عميل بالاسم أو الباسورد السري (Interactive Assign Customer Modal) -->
<div id="inbound-assign-customer-modal" style="display:none; position:fixed; inset:0; background:rgba(2, 6, 23, 0.86); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:9999999; justify-content:center; align-items:center; padding:16px; font-family:'Tajawal',sans-serif; direction:rtl;" onclick="if(event.target===this) InboundBankakManager.closeAssignCustomerModal()">
    <div style="width:100%; max-width:520px; background:linear-gradient(145deg, rgba(15,23,42,0.99), rgba(17,24,39,0.99)); border:2px solid #10b981; border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,0.92), 0 0 35px rgba(16,185,129,0.3); padding:20px 22px; color:#fff; display:flex; flex-direction:column; gap:12px; position:relative;">
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1.5px solid rgba(255,255,255,0.12); padding-bottom:10px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.5rem;">🏷️</span>
                <div>
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#34d399;">توجيه الإشعار إلى حساب عميل بدقة متناهية</h3>
                    <span id="assign-modal-subtitle" style="font-size:0.75rem; color:#94a3b8; font-weight:700;">اختر العميل بالاسم أو الباسورد السري وسيتم قيد الإشعار في كشف حسابه فوراً</span>
                </div>
            </div>
            <button type="button" onclick="InboundBankakManager.closeAssignCustomerModal()" style="background:none; border:none; color:#94a3b8; font-size:1.3rem; cursor:pointer; font-weight:900;">✕</button>
        </div>

        <input type="hidden" id="assign-modal-receipt-id">
        <input type="hidden" id="assign-modal-selected-code">

        <div style="position:relative;">
            <label style="font-size:0.82rem; font-weight:900; color:#38bdf8; margin-bottom:5px; display:block;">
                🔍 ابحث باسم العميل أو الباسورد السري (مثال: 127) أو الكود الرقمي: *
            </label>
            <input type="text" id="assign-cust-search-input" autocomplete="off" oninput="InboundBankakManager.onAssignCustomerSearch(this.value)" onfocus="InboundBankakManager.onAssignCustomerSearch(this.value)" placeholder="اكتب اسم العميل أو الباسورد السري (مثل 127)..." style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.5); border:1.5px solid rgba(56,189,248,0.5); border-radius:10px; padding:9px 12px; color:#ffd700; font-size:0.95rem; font-weight:900; font-family:'Tajawal',sans-serif; outline:none;">
            <div id="assign-cust-autocomplete" style="display:none; position:absolute; top:100%; right:0; left:0; background:#0f172a; border:1.5px solid #10b981; border-radius:10px; max-height:210px; overflow-y:auto; z-index:999999; margin-top:4px; box-shadow:0 15px 35px rgba(0,0,0,0.9);"></div>
            <div id="assign-cust-matched-banner" style="display:none; margin-top:6px; padding:7px 10px; border-radius:8px; background:rgba(16,185,129,0.14); border:1px solid rgba(16,185,129,0.45); color:#6ee7b7; font-size:0.82rem; font-weight:800;"></div>
        </div>

        <div>
            <label style="font-size:0.80rem; font-weight:800; color:#34d399; margin-bottom:4px; display:block;">
                📱 رقم واتساب العميل (يُحفظ في بطاقة العميل ويرسل إليه إشعار التأكيد فوراً):
            </label>
            <input type="tel" id="assign-cust-phone-input" placeholder="09xxxxxxxx أو 01xxxxxxxx" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.45); border:1.5px solid rgba(37,211,102,0.45); border-radius:9px; padding:8px 12px; color:#7dd3fc; font-family:'Consolas',monospace; font-size:0.92rem; font-weight:900; direction:ltr; text-align:right;">
        </div>

        <label style="display:flex; align-items:center; gap:8px; font-size:0.80rem; color:#cbd5e1; font-weight:700; cursor:pointer; background:rgba(255,255,255,0.04); padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
            <input type="checkbox" id="assign-link-sender-phone" checked style="width:16px; height:16px; accent-color:#10b981;">
            <span>ربط رقم المرسل بحساب هذا العميل تلقائياً للإشعارات القادمة</span>
        </label>

        <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px; margin-top:6px;">
            <button type="button" onclick="InboundBankakManager.closeAssignCustomerModal()" style="height:38px; padding:0 16px; border-radius:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; font-weight:800; cursor:pointer;">إلغاء</button>
            <button type="button" id="btn-confirm-assign-cust" onclick="InboundBankakManager.confirmAssignCustomerModal()" style="height:38px; padding:0 20px; border-radius:8px; background:linear-gradient(135deg, #10b981, #059669); border:1.5px solid #34d399; color:#fff; font-weight:900; font-size:0.9rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 0 18px rgba(16,185,129,0.45);">
                <span>🚀 اعتماد التوجيه وتنزيل بكشف الحساب</span>
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
    banks: [],
    policy: {},
    selectedBankId: '',
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
        this.syncMasterDataFromServer();
        this.refreshReceipts();
        
        // Real-Time Ultra-Fast Polling every 2.5 seconds for live detection
        setInterval(() => {
            this.refreshReceipts(true);
        }, 2500);

        // Check URL parameters for Deep Linking (e.g. ?action=inspect_receipt&receipt_id=123)
        const params = new URLSearchParams(window.location.search);
        if (params.get('action') === 'inspect_receipt' || params.get('receipt_id')) {
            const rId = params.get('receipt_id');
            setTimeout(() => {
                this.inspectById(rId);
            }, 800);
        }

        // Check if a batch of WhatsApp receipts was queued to load into multi-cash-payment.php
        try {
            const pendingBatchJson = sessionStorage.getItem('erp_wa_batch_to_load');
            if (pendingBatchJson && document.getElementById('accounts-blocks-container')) {
                sessionStorage.removeItem('erp_wa_batch_to_load');
                const batchIds = JSON.parse(pendingBatchJson);
                if (Array.isArray(batchIds) && batchIds.length > 0) {
                    setTimeout(() => {
                        this._populateBatchIntoCurrentMultiVoucher(batchIds);
                    }, 900);
                }
            }
        } catch (e) {}
    },

    getAvailableBanks: function() {
        if (Array.isArray(this.banks) && this.banks.length > 0) return this.banks;
        try {
            const getTenantKey = window.getTenantKey || ((k) => k);
            const raw = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1') || '[]');
            if (Array.isArray(raw)) {
                return raw.filter(b => b && !b.deleted && !b.isDeactivated && b.type !== 'safe');
            }
        } catch (e) {}
        return [];
    },

    populateHeaderBankDropdown: function() {
        const sel = document.getElementById('drawer-target-bank-select');
        if (!sel) return;
        const banks = this.getAvailableBanks();
        const prev = this.selectedBankId || sel.value || (this.policy && this.policy.defaultReceiptBankId) || '';
        sel.innerHTML = `<option value="">🏦 تلقائي (بنك الخرطوم / حسب حساب العميل)</option>` +
            banks.map(b => `<option value="${escapeHtml(String(b.id))}">🏦 ${escapeHtml(b.name || '')}${b.accountNo ? ' (' + escapeHtml(b.accountNo) + ')' : ''}</option>`).join('');
        sel.value = prev;
        this.selectedBankId = sel.value;
    },

    onDefaultDrawerBankChange: function(bankId) {
        this.selectedBankId = bankId || '';
        try {
            const pol = JSON.parse(localStorage.getItem('erp_whatsapp_statement_policy_v1') || '{}');
            const banks = this.getAvailableBanks();
            const found = banks.find(b => String(b.id) === String(bankId));
            pol.defaultReceiptBankId = this.selectedBankId;
            pol.defaultReceiptBankName = found ? found.name : '';
            localStorage.setItem('erp_whatsapp_statement_policy_v1', JSON.stringify(pol));
            if (typeof window.syncToBackend === 'function') {
                window.syncToBackend('whatsapp_statement_policy', pol);
            }
            if (typeof showToast === 'function') {
                showToast(found ? `🏦 تم تحديد (${found.name}) كبنك مستلم لإشعارات الواتساب` : '🏦 تم ضبط البنك المستلم على الوضع التلقائي', 'info');
            }
        } catch (e) {}
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

    // Sync a posted or staged inbound receipt into localStorage (erp_receipts_v1, erp_customers_v1, erp_banks_v1) and General Ledger
    syncPostedReceiptToLocalErp: function(r, postingData = null, options = {}) {
        if (!r || (!r.voucher_no && !(postingData && postingData.voucher_no))) return false;
        const vNo = (postingData && postingData.voucher_no) ? postingData.voucher_no : r.voucher_no;
        if (!vNo) return false;

        const isAwaitingApproval = (postingData && postingData.approval_mode === 'review_first') ||
            (postingData && postingData.voucher && postingData.voucher.isPosted === false) ||
            (r.issue_reason === 'awaiting_posting_approval' && r.status === 'pending' && !(postingData && postingData.status === 'verified'));
        const isActuallyPosted = !isAwaitingApproval;

        if (this.syncedVoucherNos.has(vNo) && !postingData) {
            return false;
        }

        const getTenantKey = window.getTenantKey || ((k) => k);
        const rKey = getTenantKey('erp_receipts_v1');
        let storedReceipts = [];
        try {
            storedReceipts = JSON.parse(localStorage.getItem(rKey) || localStorage.getItem('erp_receipts_v1') || '[]');
            if (!Array.isArray(storedReceipts)) storedReceipts = [];
        } catch (e) { storedReceipts = []; }

        let rawCustId = (postingData && postingData.customer_id) ? postingData.customer_id : (r.customer_id || '');
        rawCustId = String(rawCustId).replace(/^cust-/, '');
        const custName = (postingData && postingData.customer_name) ? postingData.customer_name : (r.customer_name || 'عميل بنكك');
        const amt = parseFloat(r.amount || (postingData && postingData.voucher && postingData.voucher.totalAmount) || 0) || 0;

        const existingLocalVoucher = storedReceipts.find(v => v && (v.voucherNo === vNo || v.id === vNo));
        const alreadyInLocal = Boolean(existingLocalVoucher);
        const wasAlreadyPostedInLocal = Boolean(existingLocalVoucher && existingLocalVoucher.isPosted);
        if (alreadyInLocal && !postingData && wasAlreadyPostedInLocal === isActuallyPosted) {
            const existingItem = existingLocalVoucher.items && existingLocalVoucher.items[0];
            const existingCustMatch = !rawCustId || (existingItem && String(existingItem.accountId || existingItem.account || '').replace(/^cust-/, '') === rawCustId);
            const existingAmtMatch = Math.abs((parseFloat(existingLocalVoucher.totalAmount) || 0) - amt) < 0.01;
            if (existingCustMatch && existingAmtMatch) {
                this.syncedVoucherNos.add(vNo);
                return false;
            }
        }
        this.syncedVoucherNos.add(vNo);

        // 1. Sync Customer in erp_customers_v1
        const cKey = getTenantKey('erp_customers_v1');
        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem(cKey) || localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch (e) { custs = []; }

        let cIdx = custs.findIndex(c => c && (String(c.id) === rawCustId || (c.name && c.name.trim() === custName.trim())));
        if (cIdx > -1) {
            rawCustId = String(custs[cIdx].id);
            if (isActuallyPosted) {
                if (postingData && postingData.customer && postingData.customer.currentBalance !== undefined) {
                    custs[cIdx].currentBalance = parseFloat(postingData.customer.currentBalance) || 0;
                } else if (!wasAlreadyPostedInLocal && amt > 0) {
                    custs[cIdx].currentBalance = (parseFloat(custs[cIdx].currentBalance) || 0) - amt;
                }
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
                currentBalance: isActuallyPosted ? -amt : 0,
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

        let rawBankId = (postingData && postingData.bank_id) ? postingData.bank_id : (r.bank_code || '');
        rawBankId = String(rawBankId).replace(/^bank-/, '');
        let bIdx = banks.findIndex(b => b && ((rawBankId && String(b.id) === rawBankId) || (b.type !== 'safe' && b.name && ((r.bank_name && r.bank_name.includes(b.name)) || b.name.includes('الخرطوم')))));
        if (bIdx === -1) {
            bIdx = banks.findIndex(b => b && b.type !== 'safe');
        }
        let resolvedBankName = (postingData && postingData.bank_name) || r.bank_name || 'بنك الخرطوم';
        if (bIdx > -1) {
            rawBankId = String(banks[bIdx].id);
            resolvedBankName = banks[bIdx].name || resolvedBankName;
            if (isActuallyPosted) {
                if (postingData && postingData.bank && postingData.bank.currentBalance !== undefined) {
                    banks[bIdx].currentBalance = parseFloat(postingData.bank.currentBalance) || 0;
                } else if (!wasAlreadyPostedInLocal && amt > 0) {
                    banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + amt;
                }
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
        const voucherDesc = `سداد إشعار تحويل بنكي (${resolvedBankName}) رقم العملية: ${r.transfer_no || ''} - العميل: ${custName}`;
        const voucherObj = (postingData && postingData.voucher) ? postingData.voucher : {
            id: vNo,
            voucherNo: vNo,
            type: 'receipt',
            source: 'whatsapp_inbound',
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
            receiptImage: r.receipt_image || '',
            items: [
                {
                    account: 'cust-' + rawCustId,
                    accountId: 'cust-' + rawCustId,
                    accountName: custName,
                    amount: amt,
                    statement: `إشعار تحويل ${resolvedBankName} رقم ${r.transfer_no || ''}`,
                    costCenter: '',
                    costCenterId: '',
                    costCenterName: '',
                    paymentMethods: ['bank'],
                    paymentMethod: 'bank',
                    cashAmount: 0,
                    cashSafe: '',
                    cashStatement: '',
                    bankAmount: amt,
                    bankId: rawBankId,
                    bankTransfers: [
                        {
                            amount: amt,
                            bankId: rawBankId,
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
            isPosted: isActuallyPosted,
            createdAt: new Date().toISOString()
        };

        // Remove any older voucher that was previously created for the same inboundReceiptId before reassignment
        if (r.id) {
            for (let i = storedReceipts.length - 1; i >= 0; i--) {
                const sv = storedReceipts[i];
                if (sv && Number(sv.inboundReceiptId) === Number(r.id) && sv.id !== vNo && sv.voucherNo !== vNo) {
                    storedReceipts.splice(i, 1);
                }
            }
        }

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

        // 4. Rebuild Accounts Dropdown & General Ledger (unless batched via options.skipRebuild)
        if (!options || !options.skipRebuild) {
            if (typeof window.buildAccountsArray === 'function') {
                try { window.buildAccountsArray(); } catch (e) {}
            }
            if (isActuallyPosted && typeof window.rebuildGLFromAllTables === 'function') {
                try { window.rebuildGLFromAllTables(false); } catch (e) {}
            }
            if (typeof window.refreshVoucherNavigationList === 'function') {
                try { window.refreshVoucherNavigationList(); } catch (e) {}
            }
        }
        return true;
    },

    pruneOrphanedInboundVouchers: function(activeVoucherNos, deletedVoucherNo = '', deletedReceiptId = 0) {
        try {
            const getTenantKey = window.getTenantKey || ((k) => k);
            const rKey = getTenantKey('erp_receipts_v1');
            const gKey = getTenantKey('erp_gl_v1');
            const activeSet = Array.isArray(activeVoucherNos) ? new Set(activeVoucherNos.map(v => String(v).trim())) : null;

            let storedReceipts = JSON.parse(localStorage.getItem(rKey) || localStorage.getItem('erp_receipts_v1') || '[]');
            if (Array.isArray(storedReceipts)) {
                const beforeLen = storedReceipts.length;
                storedReceipts = storedReceipts.filter(v => {
                    if (!v) return false;
                    const vid = String(v.id || v.voucherNo || '').trim();
                    if (deletedVoucherNo && vid === String(deletedVoucherNo).trim()) return false;
                    if (deletedReceiptId && Number(v.inboundReceiptId) === Number(deletedReceiptId)) return false;
                    if (activeSet && (v.source === 'whatsapp_inbound' || v.inboundReceiptId)) {
                        return activeSet.has(vid);
                    }
                    return true;
                });
                if (storedReceipts.length !== beforeLen) {
                    localStorage.setItem(rKey, JSON.stringify(storedReceipts));
                    localStorage.setItem('erp_receipts_v1', JSON.stringify(storedReceipts));
                }
            }

            let storedGl = JSON.parse(localStorage.getItem(gKey) || localStorage.getItem('erp_gl_v1') || '[]');
            if (Array.isArray(storedGl)) {
                const beforeGlLen = storedGl.length;
                storedGl = storedGl.filter(g => {
                    if (!g) return false;
                    const gid = String(g.id || '').trim();
                    const vno = String(g.voucherNo || g.refNo || g.voucherId || '').trim();
                    if (deletedVoucherNo && (vno === String(deletedVoucherNo).trim() || gid.includes(String(deletedVoucherNo).trim()))) {
                        return false;
                    }
                    if (activeSet && /^GL-M\d+/i.test(gid) && vno) {
                        return activeSet.has(vno);
                    }
                    return true;
                });
                if (storedGl.length !== beforeGlLen) {
                    localStorage.setItem(gKey, JSON.stringify(storedGl));
                    localStorage.setItem('erp_gl_v1', JSON.stringify(storedGl));
                }
            }
        } catch (e) {}
    },

    _isBatchScanning: false,

    refreshReceipts: function(isSilent = false) {
        const tId = this.getActiveTenantId();
        fetch('api/inbound_receipts.php?action=list&tenant_id=' + encodeURIComponent(tId))
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    if (Array.isArray(res.banks) && res.banks.length > 0) {
                        this.banks = res.banks;
                    }
                    if (res.policy && typeof res.policy === 'object') {
                        this.policy = res.policy;
                        try {
                            const locPol = JSON.parse(localStorage.getItem('erp_whatsapp_statement_policy_v1') || '{}') || {};
                            if (locPol.voucherStampImage && String(locPol.voucherStampImage).length >= 150 && (!res.policy.voucherStampImage || String(res.policy.voucherStampImage).length < 150)) {
                                const mergedSyncPol = Object.assign({}, res.policy, locPol, { voucherStampImage: locPol.voucherStampImage });
                                this.policy = mergedSyncPol;
                                localStorage.setItem('erp_whatsapp_statement_policy_v1', JSON.stringify(mergedSyncPol));
                                fetch('api/save_master_data.php?tenant_id=' + encodeURIComponent(tId), {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-Tenant-Id': String(tId) },
                                    body: JSON.stringify({ type: 'whatsapp_statement_policy', data: mergedSyncPol })
                                }).catch(() => {});
                            }
                        } catch (e) {}
                    }
                    this.populateHeaderBankDropdown();

                    if (Array.isArray(res.active_voucher_nos)) {
                        this.pruneOrphanedInboundVouchers(res.active_voucher_nos);
                    }

                    // Sync any backend-posted or staged receipts to local ERP storage & GL (batched rebuild once at end)
                    let anySynced = false;
                    res.data.forEach(r => {
                        if (r.voucher_no && (r.status === 'verified' || r.status === 'reconciled' || r.issue_reason === 'awaiting_posting_approval')) {
                            if (this.syncPostedReceiptToLocalErp(r, null, { skipRebuild: true })) {
                                anySynced = true;
                            }
                        }
                    });
                    if (anySynced) {
                        if (typeof window.buildAccountsArray === 'function') {
                            try { window.buildAccountsArray(); } catch (e) {}
                        }
                        if (typeof window.rebuildGLFromAllTables === 'function') {
                            try { window.rebuildGLFromAllTables(false); } catch (e) {}
                        }
                        if (typeof window.refreshVoucherNavigationList === 'function') {
                            try { window.refreshVoucherNavigationList(); } catch (e) {}
                        }
                    }

                    // Auto-scan any pending receipts in batch (excluding forgery check)
                    const pendingWithImg = res.data.filter(r => r.status === 'pending' && r.issue_reason !== 'awaiting_posting_approval' && r.receipt_image && (!r.amount || parseFloat(r.amount) <= 0 || !r.transfer_no));
                    if (pendingWithImg.length > 0 && !this._isBatchScanning) {
                        this._isBatchScanning = true;
                        fetch('api/inbound_receipts.php?action=scan_pending_batch&tenant_id=' + encodeURIComponent(tId))
                            .then(r2 => r2.json())
                            .then(batchRes => {
                                this._isBatchScanning = false;
                                if (batchRes && batchRes.success && Number(batchRes.processed_count || batchRes.scanned_count || 0) > 0) {
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
        const awaitingList = batchReceipts.filter(r => r.issue_reason === 'awaiting_posting_approval');
        const custName = batchReceipts[0].customer_name || 'عميل واتساب';
        const custId = batchReceipts[0].customer_id || '';
        const bankName = batchReceipts[0].bank_name || 'بنك الخرطوم';
        const totalAmt = verifiedList.reduce((acc, r) => acc + (parseFloat(r.amount) || 0), 0);
        const toastId = 'admin-batch-toast-' + Date.now();
        const div = document.createElement('div');
        div.id = toastId;
        div.style.cssText = `
            position: fixed; top: 24px; right: 24px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            border: 2px solid ${awaitingList.length > 0 ? '#f59e0b' : '#10b981'}; border-radius: 16px; padding: 16px 20px; color: #fff;
            box-shadow: 0 15px 40px rgba(0,0,0,0.85), 0 0 25px rgba(16,185,129,0.5);
            z-index: 99999999; display: flex; flex-direction: column; gap: 8px;
            font-family: 'Tajawal', sans-serif; direction: rtl; max-width: 450px;
            animation: slideInRightToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        `;
        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong style="color:${awaitingList.length > 0 ? '#fcd34d' : '#34d399'}; font-size:1rem;">🔔 ${awaitingList.length > 0 ? 'إشعارات واتساب بانتظار المراجعة والتمرير' : 'إشعار مدير النظام — إضافة إشعارات بالحسابات'}</strong>
                <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:#94a3b8; font-size:1.1rem; cursor:pointer;">✕</button>
            </div>
            <div style="font-size:0.86rem; color:#f8fafc; line-height:1.5;">
                تم استلام وفحص <strong>(${verifiedList.length || batchReceipts.length}) إشعار بنكي</strong> من حساب:
                <strong style="color:#ffd700;">${escapeHtml(custName)}</strong> (${escapeHtml(bankName)})
                بإجمالي <strong style="color:#34d399;">${totalAmt.toLocaleString('en-US')} SDG</strong>.
            </div>
            <div style="display:flex; gap:8px; margin-top:4px;">
                ${awaitingList.length > 0
                    ? `<a href="posting-accounts.php?filter=whatsapp" style="flex:1; text-align:center; padding:6px 10px; border-radius:8px; background:#f59e0b; color:#0f172a; font-weight:900; font-size:0.8rem; text-decoration:none;">👁️ مراجعة وتمرير في الترحيل</a>`
                    : `<a href="general-ledger.php?accountId=${encodeURIComponent(custId ? ('cust-' + String(custId).replace(/^cust-/, '')) : '')}&name=${encodeURIComponent(custName)}" style="flex:1; text-align:center; padding:6px 10px; border-radius:8px; background:#10b981; color:#fff; font-weight:900; font-size:0.8rem; text-decoration:none;">📊 فتح كشف حساب ${escapeHtml(custName)}</a>`
                }
                <button type="button" onclick="InboundBankakManager.toggleDrawer(); this.parentElement.parentElement.remove();" style="padding:6px 12px; border-radius:8px; background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#e0f2fe; font-weight:800; font-size:0.8rem; cursor:pointer;">📥 صينية الاستلام</button>
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
        const isAwaiting = receipt.issue_reason === 'awaiting_posting_approval';
        const toastId = 'inbound-toast-' + Date.now();
        const div = document.createElement('div');
        div.id = toastId;
        div.style.cssText = `
            position: fixed;
            top: 24px;
            right: 24px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            border: 2px solid ${isAwaiting ? '#f59e0b' : '#10b981'};
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
        const vBadge = isAwaiting
            ? `<span style="font-size:0.75rem; background:rgba(245,158,11,0.25); border:1px solid #f59e0b; color:#fde047; padding:1px 6px; border-radius:6px;">👁️ بانتظار التمرير (${escapeHtml(receipt.voucher_no || '')})</span>`
            : (receipt.voucher_no ? `<span style="font-size:0.75rem; background:rgba(16,185,129,0.25); border:1px solid #10b981; color:#6ee7b7; padding:1px 6px; border-radius:6px;">نزل بالحسابات (${escapeHtml(receipt.voucher_no)})</span>` : `<span style="font-size:0.75rem; background:rgba(56,189,248,0.2); color:#7dd3fc; padding:1px 6px; border-radius:6px;">بنكك</span>`);
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
                <span style="font-size:0.80rem; color:#cbd5e1;">المبلغ: <strong style="color:#fef08a;">${Number(receipt.amount||0).toLocaleString()} SDG</strong> | انقر للمراجعة 🚀</span>
            </div>
            <button type="button" onclick="event.stopPropagation(); this.parentElement.remove()" style="background:none; border:none; color:#94a3b8; font-size:1.2rem; cursor:pointer; padding:0 4px;">✕</button>
        `;
        div.onclick = () => {
            div.remove();
            this.toggleDrawer();
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
        const awaiting = (stats.awaiting_approval !== undefined)
            ? stats.awaiting_approval
            : this.receipts.filter(r => r.issue_reason === 'awaiting_posting_approval' || (r.status === 'pending' && parseFloat(r.amount || 0) > 0)).length;

        // Top bar badge
        const badge = document.getElementById('inbound-receipts-badge-count');
        if (badge) {
            badge.textContent = total;
            badge.style.display = total > 0 ? 'inline-block' : 'none';
            if (dup > 0) {
                badge.style.background = '#ef4444';
            } else if (awaiting > 0) {
                badge.style.background = '#f59e0b';
            } else {
                badge.style.background = '#10b981';
            }
        }

        // Header Drawer stats
        const elVer = document.getElementById('stat-verified-count');
        if (elVer) elVer.textContent = verified;
        const elAw = document.getElementById('stat-awaiting-count');
        if (elAw) elAw.textContent = awaiting;
        const elTabAw = document.getElementById('tab-awaiting-count');
        if (elTabAw) elTabAw.textContent = awaiting;
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
            } else if (this.activeFilter === 'awaiting_approval') {
                filtered = this.receipts.filter(r => r.issue_reason === 'awaiting_posting_approval' || (r.status === 'pending' && parseFloat(r.amount || 0) > 0));
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

        const banks = this.getAvailableBanks();
        let html = '';
        filtered.forEach(r => {
            const isDup = r.status === 'duplicate_rejected';
            const isVer = r.status === 'verified' || r.status === 'reconciled';
            const isAwaiting = r.issue_reason === 'awaiting_posting_approval' || (!isVer && !isDup && parseFloat(r.amount || 0) > 0);
            const isPosted = isVer && !!(r.voucher_no);
            const cardClass = isDup ? 'card-duplicate' : (isVer ? 'card-verified' : 'card-pending');

            const statusBadge = isDup 
                ? '<span style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">⚠️ إشعار مكرر (مرفوض)</span>'
                : (isPosted
                    ? `<span style="background:rgba(16,185,129,0.25); border:1px solid #10b981; color:#6ee7b7; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">✅ معتمد ومرحّل (${escapeHtml(r.voucher_no)})</span>`
                    : (isAwaiting
                        ? `<span style="background:rgba(245,158,11,0.25); border:1px solid #f59e0b; color:#fde047; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">👁️ بانتظار التمرير والترحيل${r.voucher_no ? ' (' + escapeHtml(r.voucher_no) + ')' : ''}</span>`
                        : '<span style="background:rgba(245,158,11,0.2); border:1px solid #f59e0b; color:#fde047; padding:2px 8px; border-radius:6px; font-size:0.72rem; font-weight:900;">⏳ بانتظار الفحص</span>'));

            const formattedAmt = parseFloat(r.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

            const currentBankCode = String(r.bank_code || '').replace(/^bank-/, '');
            const bankOptionsHtml = `<option value="">🏦 ${escapeHtml(r.bank_name || 'بنك الخرطوم (افتراضي)')}</option>` +
                banks.map(b => {
                    const selAttr = (currentBankCode && String(b.id) === currentBankCode) || (r.bank_name && b.name && r.bank_name === b.name) ? 'selected' : '';
                    return `<option value="${escapeHtml(String(b.id))}" ${selAttr}>🏦 ${escapeHtml(b.name || '')}</option>`;
                }).join('');

            html += `
                <div class="inbound-receipt-card ${cardClass}">
                    <!-- Top Info Row -->
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                            <span style="font-size:1.1rem;">👤</span>
                            <div style="display:flex; flex-direction:column; min-width:0;">
                                <strong style="font-size:0.92rem; color:#f8fafc; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(r.customer_name)} <small style="color:#94a3b8; font-weight:700;">#${r.id}</small></strong>
                                <span style="font-size:0.74rem; color:#94a3b8; font-family:'Consolas',monospace; direction:ltr; text-align:right;">${escapeHtml(r.customer_phone || 'بدون هاتف')}</span>
                            </div>
                        </div>
                        <div>${statusBadge}</div>
                    </div>

                    <!-- Details & Bank Selector Row -->
                    <div style="background:rgba(0,0,0,0.35); border-radius:8px; padding:7px 10px; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; font-size:0.82rem;">
                        <div>
                            <span style="color:#94a3b8; font-size:0.74rem;">المبلغ:</span>
                            <strong style="color:#38bdf8; font-size:1.02rem; font-family:'Consolas',monospace; margin-right:4px;">${formattedAmt}</strong>
                            <span style="font-size:0.72rem; color:#cbd5e1;">${escapeHtml(r.currency || 'SDG')}</span>
                        </div>
                        <div>
                            <span style="color:#94a3b8; font-size:0.74rem;">رقم العملية (≥6):</span>
                            <strong style="color:#fef08a; font-family:'Consolas',monospace; margin-right:3px;">${escapeHtml(r.transfer_no || 'غير محدد')}</strong>
                        </div>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <select onchange="InboundBankakManager.changeTargetBank(${r.id}, this.value)" title="تحديد أو تغيير حساب البنك المستلم لهذا الإشعار" style="height:26px; background:rgba(15,23,42,0.9); color:#fcd34d; border:1px solid rgba(245,158,11,0.45); border-radius:6px; padding:0 6px; font-family:'Tajawal',sans-serif; font-size:0.73rem; font-weight:800; max-width:175px;">
                                ${bankOptionsHtml}
                            </select>
                        </div>
                    </div>

                    ${r.notes ? `<div style="font-size:0.75rem; color:${isDup ? '#fca5a5' : '#cbd5e1'}; background:rgba(0,0,0,0.2); padding:4px 8px; border-radius:6px; line-height:1.3;">${escapeHtml(r.notes)}</div>` : ''}

                    <!-- Actions Row -->
                    <div style="display:flex; gap:5px; margin-top:2px; align-items:center; flex-wrap:wrap;">
                        ${(!isPosted && !isDup && parseFloat(r.amount || 0) > 0) ? `
                        <button type="button" onclick="InboundBankakManager.postToAccountsDirect(${r.id}, this)" style="flex:1.2; height:32px; padding:0 10px; border-radius:7px; background:linear-gradient(135deg, #10b981, #059669); border:1.2px solid #34d399; color:#ffffff; font-size:0.77rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; box-shadow:0 0 12px rgba(16,185,129,0.35);" title="تمرير وترحيل فوري في حساب العميل وحساب البنك المحدد">
                            <span>✔️</span> <span>تمرير وترحيل للحساب</span>
                        </button>
                        ` : ''}

                        <!-- زر الفحص والتنزيل في السند والحسابات -->
                        <button type="button" onclick="InboundBankakManager.inspectInVoucher(${r.id})" style="height:32px; padding:0 10px; border-radius:7px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(14,165,233,0.4)); border:1.2px solid #38bdf8; color:#ffffff; font-size:0.75rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;" title="فتح وتفريغ الإشعار داخل شاشة السند">
                            <span>🔍</span> <span>${isPosted ? 'عرض بالسند' : 'فتح بالسند'}</span>
                        </button>

                        ${r.receipt_image ? `
                        <button type="button" onclick="InboundBankakManager.previewReceiptImage('${escapeHtml(r.receipt_image)}', 'إشعار #${r.id} — ${escapeHtml(r.customer_name)} (${formattedAmt} SDG)')" style="height:32px; padding:0 8px; border-radius:7px; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.4); color:#bae6fd; font-size:0.74rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="معاينة صورة الإشعار الأصلية">
                            <span>🖼️</span> <span>الصورة</span>
                        </button>
                        ` : ''}

                        <!-- زر فحص التزوير عند الطلب فقط -->
                        <button type="button" onclick="InboundBankakManager.checkForgeryOnDemand(${r.id}, this)" style="height:32px; padding:0 8px; border-radius:7px; background:linear-gradient(135deg, rgba(168,85,247,0.25), rgba(126,34,206,0.4)); border:1.2px solid #a855f7; color:#e9d5ff; font-size:0.74rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="فحص التزوير والتلاعب الجنائي بالصورة (عند الطلب فقط)">
                            <span>🕵️</span> <span>تزوير</span>
                        </button>

                        ${(!r.amount || parseFloat(r.amount) <= 0 || !r.transfer_no) ? `
                        <button type="button" onclick="InboundBankakManager.triggerOCR(${r.id}, this)" style="height:32px; padding:0 8px; border-radius:7px; background:linear-gradient(135deg, rgba(245,158,11,0.25), rgba(217,119,6,0.35)); border:1.2px solid #f59e0b; color:#fde047; font-size:0.74rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="استخراج المبلغ ورقم العملية بالذكاء الاصطناعي">
                            <span>⚡</span> <span>فحص AI</span>
                        </button>
                        ` : ''}

                        <!-- زر توجيه الإشعار لحساب عميل بالباسورد أو الاسم -->
                        <button type="button" onclick="InboundBankakManager.promptAssignByCode(${r.id})" style="height:32px; padding:0 8px; border-radius:7px; background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.45); color:#6ee7b7; font-size:0.74rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="تحويل وتوجيه الإشعار لحساب شخص آخر بالباسورد السري (مثل 532) أو الاسم">
                            <span>🏷️</span> <span>توجيه لحساب</span>
                        </button>

                        <!-- زر كشف حساب العميل -->
                        <a href="general-ledger.php?accountId=${encodeURIComponent(r.customer_id ? ('cust-' + String(r.customer_id).replace(/^cust-/, '')) : '')}&name=${encodeURIComponent(r.customer_name || '')}" style="height:32px; padding:0 8px; border-radius:7px; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.35); color:#7dd3fc; font-size:0.74rem; font-weight:800; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:3px;" title="فتح كشف حساب هذا العميل">
                            <span>📊</span> <span>كشف</span>
                        </a>

                        <!-- زر إشعار الزبون عبر واتساب -->
                        <button type="button" onclick="InboundBankakManager.openCustomerAlertModal(${r.id})" style="height:32px; padding:0 8px; border-radius:7px; background:linear-gradient(135deg, rgba(37,211,102,0.25), rgba(16,185,129,0.35)); border:1.2px solid #25d366; color:#a7f3d0; font-size:0.74rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px;" title="إرسال تنبيه رسمي للزبون عبر واتساب">
                            <span>📲</span>
                        </button>

                        <!-- زر حذف / تجاهل -->
                        <button type="button" onclick="InboundBankakManager.dismissReceipt(${r.id})" style="width:28px; height:32px; border-radius:7px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; font-size:0.82rem; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="أرشفة أو تجاهل الإشعار">
                            ✕
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    },

    previewReceiptImage: function(imgUrl, title) {
        if (!imgUrl) return;
        const modalId = 'inbound-receipt-img-preview-modal';
        if (document.getElementById(modalId)) document.getElementById(modalId).remove();
        const div = document.createElement('div');
        div.id = modalId;
        div.style.cssText = 'position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(8px); z-index:999999999; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px; font-family:Tajawal,sans-serif; direction:rtl;';
        div.onclick = (e) => { if (e.target === div) div.remove(); };
        div.innerHTML = `
            <div style="background:#0f172a; border:2px solid #38bdf8; border-radius:16px; padding:14px; max-width:92vw; max-height:90vh; display:flex; flex-direction:column; gap:10px; box-shadow:0 25px 60px rgba(0,0,0,0.9);">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:8px;">
                    <strong style="color:#f8fafc; font-size:0.95rem;">🖼️ ${escapeHtml(title || 'صورة الإشعار البنكي')}</strong>
                    <button type="button" onclick="document.getElementById('${modalId}').remove()" style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; border-radius:8px; width:32px; height:32px; cursor:pointer; font-weight:900;">✕</button>
                </div>
                <div style="overflow:auto; max-height:78vh; display:flex; justify-content:center; align-items:center;">
                    <img src="${escapeHtml(imgUrl)}" alt="Receipt" style="max-width:100%; max-height:75vh; border-radius:10px; object-fit:contain;">
                </div>
            </div>
        `;
        document.body.appendChild(div);
    },

    changeTargetBank: async function(receiptId, bankId) {
        if (!bankId) return;
        const tId = this.getActiveTenantId();
        try {
            const res = await fetch('api/inbound_receipts.php?action=change_target_bank&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'change_target_bank',
                    tenant_id: tId,
                    id: receiptId,
                    bank_id: bankId
                })
            });
            const data = await res.json();
            if (data && data.success) {
                const r = this.receipts.find(x => Number(x.id) === Number(receiptId));
                if (r) {
                    r.bank_code = data.bank_id;
                    r.bank_name = data.bank_name;
                    if (r.voucher_no) this.syncPostedReceiptToLocalErp(r);
                }
                if (typeof showToast === 'function') {
                    showToast(data.message || `🏦 تم تحديد البنك المستلم: ${data.bank_name}`, 'success');
                }
                this.refreshReceipts(true);
            } else {
                alert(data?.error || 'تعذر تغيير البنك المستلم');
            }
        } catch (e) {
            alert('خطأ في الاتصال: ' + e.message);
        }
    },

    approveAllPendingReceipts: async function(btnEl) {
        const candidates = this.receipts.filter(r =>
            r.status !== 'duplicate_rejected' &&
            parseFloat(r.amount || 0) > 0 &&
            (r.issue_reason === 'awaiting_posting_approval' || r.status === 'pending' || !r.voucher_no)
        );
        if (candidates.length === 0) {
            if (typeof showToast === 'function') {
                showToast('✅ جميع الإشعارات السليمة معتمدة ومرحّلة بالفعل في الحسابات!', 'info');
            } else {
                alert('جميع الإشعارات السليمة معتمدة ومرحّلة بالفعل في الحسابات.');
            }
            return;
        }

        const bankId = document.getElementById('drawer-target-bank-select')?.value || this.selectedBankId || '';
        const origHtml = btnEl ? btnEl.innerHTML : '';
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '<span>⏳</span> <span>جاري تمرير الكل...</span>';
        }
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch('api/inbound_receipts.php?action=approve_batch&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'approve_batch',
                    tenant_id: tId,
                    ids: candidates.map(c => Number(c.id)),
                    bank_id: bankId
                })
            });
            const data = await res.json();
            if (data && data.success) {
                if (Array.isArray(data.results)) {
                    data.results.forEach(postRes => {
                        if (postRes && postRes.success && postRes.voucher_no) {
                            const rObj = this.receipts.find(x => Number(x.id) === Number(postRes.receipt_id)) || { id: postRes.receipt_id, voucher_no: postRes.voucher_no };
                            rObj.status = 'verified';
                            rObj.issue_reason = null;
                            rObj.voucher_no = postRes.voucher_no;
                            this.syncPostedReceiptToLocalErp(rObj, postRes);
                        }
                    });
                }
                this.refreshReceipts();
                if (typeof showToast === 'function') {
                    showToast(data.message || `✅ تم تمرير وترحيل (${data.approved_count}) إشعار للحسابات بنجاح!`, 'success', 6000);
                }
            } else {
                alert(data?.error || 'تعذر تمرير الدفعة');
            }
        } catch (e) {
            alert('خطأ أثناء تمرير الإشعارات: ' + e.message);
        } finally {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = origHtml || '<span>✔️</span> <span>تمرير الكل للحسابات</span>';
            }
        }
    },

    loadBatchIntoMultiVoucher: function() {
        let candidates = this.receipts.filter(r =>
            r.status !== 'duplicate_rejected' &&
            parseFloat(r.amount || 0) > 0 &&
            (this.activeFilter === 'all' ? (r.issue_reason === 'awaiting_posting_approval' || r.status === 'pending' || r.status === 'verified') : true)
        );
        const awaitingOnly = candidates.filter(r => r.issue_reason === 'awaiting_posting_approval' || r.status === 'pending');
        if (awaitingOnly.length > 0) {
            candidates = awaitingOnly;
        } else {
            candidates = candidates.slice(0, 20);
        }

        if (candidates.length === 0) {
            alert('لا توجد إشعارات صالحة لإدراجها في شاشة الحركة المالية المجمعة.');
            return;
        }

        const ids = candidates.map(r => Number(r.id));
        if (!document.getElementById('accounts-blocks-container')) {
            sessionStorage.setItem('erp_wa_batch_to_load', JSON.stringify(ids));
            window.location.href = 'multi-cash-payment.php?from_wa_batch=1';
            return;
        }

        this.closeDrawer();
        this._populateBatchIntoCurrentMultiVoucher(ids);
    },

    _populateBatchIntoCurrentMultiVoucher: function(receiptIds) {
        if (!Array.isArray(receiptIds) || receiptIds.length === 0) return;
        const list = receiptIds.map(id => this.receipts.find(r => Number(r.id) === Number(id))).filter(Boolean);
        if (list.length === 0) return;

        if (typeof window.setVoucherType === 'function') {
            window.setVoucherType('receipt');
        }
        if (typeof window.buildAccountsArray === 'function') {
            try { window.buildAccountsArray(); } catch (e) {}
        }

        // Set Main Receiving Bank
        const chosenBankId = document.getElementById('drawer-target-bank-select')?.value || this.selectedBankId || list[0].bank_code || '';
        const mainAccInput = document.getElementById('main-account');
        const mainAccSearch = document.getElementById('main-account-search');
        if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
            let bankAcc = ACCOUNTS.find(a => a.id && a.id.startsWith('bank-') && (
                (chosenBankId && a.id === ('bank-' + String(chosenBankId).replace(/^bank-/, ''))) ||
                (list[0].bank_name && a.name && list[0].bank_name.includes(a.name)) ||
                (a.name && a.name.includes('الخرطوم'))
            )) || ACCOUNTS.find(a => a.id && a.id.startsWith('bank-'));

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

        const container = document.getElementById('accounts-blocks-container');
        if (!container) return;

        // Ensure we have enough account blocks for each receipt in the batch
        list.forEach((r, idx) => {
            let blocks = container.querySelectorAll('.account-block');
            if (idx >= blocks.length && typeof window.addAccountBlock === 'function') {
                window.addAccountBlock();
                blocks = container.querySelectorAll('.account-block');
            }
            const targetBlock = blocks[idx];
            if (!targetBlock) return;
            const blockId = targetBlock.getAttribute('data-block-id');

            const rawCustId = String(r.customer_id || '').replace(/^cust-/, '');
            let fullCustAccId = rawCustId ? ('cust-' + rawCustId) : '';
            if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
                const matchedCustAcc = ACCOUNTS.find(a =>
                    (fullCustAccId && String(a.id) === fullCustAccId) ||
                    (r.customer_name && a.name && a.name.trim() === r.customer_name.trim())
                );
                if (matchedCustAcc) fullCustAccId = matchedCustAcc.id;
            }

            const accSearch = document.getElementById(`account-${blockId}-search`);
            const accHidden = document.getElementById(`account-${blockId}`);
            const accId = document.getElementById(`account-${blockId}-id`);
            if (accSearch) accSearch.value = r.customer_name || '';
            if (accHidden) accHidden.value = fullCustAccId || r.customer_name || '';
            if (accId) accId.value = fullCustAccId || r.customer_name || '';

            const btnCash = document.getElementById(`pm-cash-${blockId}`);
            const btnBank = document.getElementById(`pm-bank-${blockId}`);
            if (btnCash && btnCash.classList.contains('active') && typeof window.toggleBlockPayment === 'function') {
                window.toggleBlockPayment(blockId, 'cash');
            }
            if (btnBank && !btnBank.classList.contains('active') && typeof window.toggleBlockPayment === 'function') {
                window.toggleBlockPayment(blockId, 'bank');
            }

            const subFields = document.getElementById(`sub-fields-${blockId}`);
            const subBank = document.getElementById(`sub-bank-${blockId}`);
            if (subFields) subFields.style.display = 'block';
            if (subBank) subBank.style.display = 'block';

            let bankRow = targetBlock.querySelector('.bank-row');
            if (!bankRow && typeof window.addBankRow === 'function') {
                window.addBankRow(blockId);
                bankRow = targetBlock.querySelector('.bank-row');
            }
            const cleanAmt = parseFloat(r.amount || 0) || 0;
            const formattedAmtStr = (typeof fmtNum === 'function') ? fmtNum(cleanAmt) : cleanAmt.toLocaleString('en-US');

            if (bankRow) {
                const bAmt = bankRow.querySelector('.block-bank-amount');
                if (bAmt) {
                    bAmt.value = formattedAmtStr;
                    bAmt.dispatchEvent(new Event('input', { bubbles: true }));
                }
                const bTransfer = bankRow.querySelector('.block-transfer-no-input');
                if (bTransfer) {
                    bTransfer.value = r.transfer_no || '';
                    bTransfer.dispatchEvent(new Event('input', { bubbles: true }));
                }
                const bName = bankRow.querySelector('.block-bank-name-input');
                if (bName) {
                    bName.value = r.bank_name || 'بنك الخرطوم (بنكك)';
                }
                const bDesc = bankRow.querySelector('.block-bank-desc-input');
                if (bDesc) {
                    bDesc.value = `إشعار واتساب #${r.id} - ${r.customer_name || ''}`;
                }
            }

            const mainBlockAmt = document.getElementById(`amount-${blockId}`);
            if (mainBlockAmt) mainBlockAmt.value = formattedAmtStr;

            const stmtInput = document.getElementById(`statement-${blockId}`);
            if (stmtInput) stmtInput.value = `إشعار واتساب (${r.bank_name || 'بنكك'}) رقم العملية ${r.transfer_no || ''}`;

            if (typeof window.recalcBlockTotal === 'function') window.recalcBlockTotal(blockId);
            if (typeof window.updateBlockTafqeetAndTier === 'function') window.updateBlockTafqeetAndTier(blockId);
        });

        const vDescInput = document.getElementById('voucher-desc');
        if (vDescInput) {
            vDescInput.value = `دفعة إشعارات واتساب مجمعة (${list.length} إشعار)`;
        }
        if (typeof window.calcTotal === 'function') window.calcTotal();
        if (typeof showToast === 'function') {
            showToast(`📥 تم إدراج (${list.length}) إشعار واتساب داخل شاشة الحركة المالية المجمعة بنجاح!`, 'success', 6000);
        }
    },

    syncMasterDataFromServer: async function() {
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch('api/get_master_data.php?tenant_id=' + encodeURIComponent(tId) + '&_t=' + Date.now());
            const data = await res.json();
            if (data && data.status === 'success') {
                const getTenantKey = window.getTenantKey || ((k) => k);
                if (Array.isArray(data.customers)) {
                    localStorage.setItem(getTenantKey('erp_customers_v1'), JSON.stringify(data.customers));
                    localStorage.setItem('erp_customers_v1', JSON.stringify(data.customers));
                }
                if (Array.isArray(data.suppliers)) {
                    localStorage.setItem(getTenantKey('erp_suppliers_v1'), JSON.stringify(data.suppliers));
                    localStorage.setItem('erp_suppliers_v1', JSON.stringify(data.suppliers));
                }
                if (typeof window.buildAccountsArray === 'function') {
                    try { window.buildAccountsArray(); } catch (e) {}
                }
                if (typeof window.buildAccountsList === 'function') {
                    try { window.buildAccountsList(); } catch (e) {}
                }
            }
        } catch (e) {
            console.warn('syncMasterDataFromServer warning:', e);
        }
    },

    normalizeDigitsAndArabic: function(str) {
        if (!str) return '';
        const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        let q = String(str).trim();
        for (let i = 0; i < 10; i++) {
            q = q.replace(new RegExp(ar[i], 'g'), String(i)).replace(new RegExp(fa[i], 'g'), String(i));
        }
        return q.toLowerCase()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/^#/, '')
            .trim();
    },

    getAllActiveCustomersAndSuppliers: function() {
        const getTenantKey = window.getTenantKey || ((k) => k);
        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch(e) { custs = []; }
        let sups = [];
        try {
            sups = JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1') || '[]');
            if (!Array.isArray(sups)) sups = [];
        } catch(e) { sups = []; }
        const list = [];
        custs.filter(c => c && !c.deleted).forEach(c => list.push(Object.assign({}, c, { _entityType: 'customer' })));
        sups.filter(s => s && !s.deleted).forEach(s => list.push(Object.assign({}, s, { _entityType: 'supplier' })));
        return list;
    },

    searchCustomersByCodeOrName: function(rawQuery) {
        const qNorm = this.normalizeDigitsAndArabic(rawQuery);
        const all = this.getAllActiveCustomersAndSuppliers();
        if (!qNorm) return all.slice(0, 30);

        const qCleanCode = qNorm.replace(/^(كود|باسورد|رمز|الرمز|كلمه السر|كشف|رصيد)\s+/i, '').trim();
        const scored = [];

        for (const c of all) {
            const cPin = this.normalizeDigitsAndArabic(c.statementPin || '');
            const cNum = this.normalizeDigitsAndArabic(c.receiptCodeNum || '');
            const cWord = this.normalizeDigitsAndArabic(c.receiptCodeWord || '');
            const cName = this.normalizeDigitsAndArabic(c.name || '');
            const cPhone = String(c.whatsapp || c.phone || '').replace(/\D/g, '');
            const qDigits = qCleanCode.replace(/\D/g, '');

            let score = 0;
            if (cPin && (cPin === qCleanCode || cPin === qNorm)) score = 1000;
            else if (cNum && (cNum === qCleanCode || cNum === qNorm)) score = 950;
            else if (cWord && (cWord === qCleanCode || cWord === qNorm)) score = 920;
            else if (cName && cName === qNorm) score = 900;
            else if (cName && cName.startsWith(qNorm)) score = 800;
            else if (cPin && qCleanCode && cPin.startsWith(qCleanCode)) score = 750;
            else if (cNum && qCleanCode && cNum.startsWith(qCleanCode)) score = 700;
            else if (cName && qNorm.split(/\s+/).every(tok => cName.includes(tok))) score = 650;
            else if (qDigits.length >= 4 && cPhone.includes(qDigits)) score = 600;

            if (score > 0) {
                scored.push({ item: c, score });
            }
        }
        scored.sort((a, b) => b.score - a.score);
        return scored.map(x => x.item).slice(0, 25);
    },

    findCustomerByCodeOrName: function(rawQuery) {
        if (!rawQuery) return null;
        const matches = this.searchCustomersByCodeOrName(rawQuery);
        return matches.length > 0 ? matches[0] : null;
    },

    saveCustomerPhoneToBackend: async function(customerId, newPhone, customerName = '') {
        if (!newPhone) return;
        const cleanRaw = String(newPhone).trim();
        if (!cleanRaw) return;
        const getTenantKey = window.getTenantKey || ((k) => k);
        const cKey = getTenantKey('erp_customers_v1');
        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem(cKey) || localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch(e) { custs = []; }

        const cleanId = String(customerId || '').replace(/^cust-/, '').trim();
        const normName = this.normalizeDigitsAndArabic(customerName);
        let idx = custs.findIndex(c => c && !c.deleted && (
            (cleanId && String(c.id) === cleanId) ||
            (normName && this.normalizeDigitsAndArabic(c.name) === normName)
        ));

        if (idx > -1) {
            custs[idx].whatsapp = cleanRaw;
            if (!custs[idx].phone) custs[idx].phone = cleanRaw;
            try {
                localStorage.setItem(cKey, JSON.stringify(custs));
                localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
            } catch (e) {}

            try {
                const tId = this.getActiveTenantId();
                await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_customer',
                        tenant_id: tId,
                        data: custs[idx]
                    })
                });
            } catch (err) {
                console.warn('Auto-save customer WhatsApp phone warning:', err);
            }
        }
    },

    onManualCodeOrNameInput: function(val) {
        const hintEl = document.getElementById('manualCustCodeHint');
        if (!hintEl) return;
        const matched = this.findCustomerByCodeOrName(val);
        if (matched) {
            hintEl.style.display = 'block';
            const pinBadge = matched.statementPin ? ` | 🔐 الباسورد: ${escapeHtml(String(matched.statementPin))}` : '';
            hintEl.innerHTML = `✅ تم التعرف على الحساب: <strong>${escapeHtml(matched.name)}</strong> (كود #${matched.receiptCodeNum || '-'}${pinBadge})`;
            const phoneEl = document.getElementById('manualCustPhone');
            if (phoneEl && (matched.whatsapp || matched.phone)) {
                phoneEl.value = matched.whatsapp || matched.phone;
            }
        } else {
            hintEl.style.display = 'none';
            hintEl.innerHTML = '';
        }
    },

    lookupCustomerForAlertByCode: function(val) {
        const dropdown = document.getElementById('cust-alert-autocomplete');
        const q = String(val || '').trim();
        if (!q) {
            if (dropdown) dropdown.style.display = 'none';
            return;
        }
        const matches = this.searchCustomersByCodeOrName(q);
        if (dropdown) {
            if (matches.length > 0) {
                dropdown.innerHTML = matches.slice(0, 12).map(c => {
                    const pinText = c.statementPin ? `<span style="background:rgba(16,185,129,0.2); color:#34d399; padding:1px 6px; border-radius:4px; font-family:monospace; font-size:0.75rem;">🔐 ${escapeHtml(String(c.statementPin))}</span>` : '';
                    const codeText = c.receiptCodeNum ? `<span style="background:rgba(56,189,248,0.18); color:#38bdf8; padding:1px 6px; border-radius:4px; font-family:monospace; font-size:0.75rem;">#${escapeHtml(String(c.receiptCodeNum))}</span>` : '';
                    const phText = escapeHtml(c.whatsapp || c.phone || 'بدون رقم');
                    return `<div onclick="InboundBankakManager.selectAlertCustomer('${escapeHtml(String(c.id))}')" style="padding:8px 12px; cursor:pointer; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center; gap:8px;" onmouseover="this.style.background='rgba(16,185,129,0.15)'" onmouseout="this.style.background='transparent'">
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <strong style="color:#fff; font-size:0.86rem;">${escapeHtml(c.name || '')}</strong>
                            ${pinText}
                            ${codeText}
                        </div>
                        <span style="color:#94a3b8; font-size:0.75rem; font-family:monospace;" dir="ltr">${phText}</span>
                    </div>`;
                }).join('');
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }
        if (matches.length > 0) {
            this.applyAlertCustomerToFields(matches[0]);
        }
    },

    selectAlertCustomer: function(custId) {
        const all = this.getAllActiveCustomersAndSuppliers();
        const found = all.find(c => String(c.id) === String(custId));
        const dropdown = document.getElementById('cust-alert-autocomplete');
        if (dropdown) dropdown.style.display = 'none';
        if (found) {
            const input = document.getElementById('cust-alert-code-lookup');
            if (input) input.value = found.name;
            this.applyAlertCustomerToFields(found);
        }
    },

    applyAlertCustomerToFields: function(matched) {
        if (!matched) return;
        const targetPhone = matched.whatsapp || matched.phone || '';
        const nameEl = document.getElementById('cust-alert-name');
        const phoneInput = document.getElementById('cust-alert-phone');
        const phoneDisp = document.getElementById('cust-alert-phone-display');
        const custIdHidden = document.getElementById('cust-alert-customer-id');
        const pinLabel = matched.statementPin ? ` | باسورد: ${matched.statementPin}` : '';
        if (nameEl) nameEl.textContent = `${matched.name} (#${matched.receiptCodeNum || '-'}${pinLabel})`;
        if (phoneInput && targetPhone) phoneInput.value = targetPhone;
        if (phoneDisp) phoneDisp.textContent = targetPhone || 'يمكنك كتابته بالأسفل';
        if (custIdHidden) custIdHidden.value = matched.id || '';
        if (this.currentReceiptForAlert) {
            this.currentReceiptForAlert.customer_name = matched.name;
            this.currentReceiptForAlert.customer_id = matched.id;
            if (targetPhone) this.currentReceiptForAlert.customer_phone = targetPhone;
            const activeBtn = document.querySelector('.alert-type-btn.active');
            const activeType = activeBtn ? activeBtn.getAttribute('data-type') : 'reconciled';
            this.selectAlertTemplate(activeType, activeBtn);
        }
    },

    promptAssignByCode: function(receiptId) {
        const r = this.receipts.find(x => Number(x.id) === Number(receiptId));
        if (!r) return;
        this.syncMasterDataFromServer();

        const modal = document.getElementById('inbound-assign-customer-modal');
        if (!modal) return;

        document.getElementById('assign-cust-receipt-id').value = r.id;
        document.getElementById('assign-cust-selected-id').value = '';
        document.getElementById('assign-cust-selected-code').value = '';

        const subtitle = document.getElementById('assign-cust-receipt-subtitle');
        if (subtitle) {
            const amtTxt = r.amount ? `${parseFloat(r.amount).toLocaleString('en-US')} ${r.currency || 'SDG'}` : 'قيد الفحص';
            subtitle.textContent = `إشعار #${r.id} — رقم العملية: ${r.transfer_no || '---'} — المبلغ: ${amtTxt}`;
        }

        const searchInput = document.getElementById('assign-cust-search-input');
        if (searchInput) {
            searchInput.value = (r.customer_name && r.customer_name !== 'عميل واتساب' && r.customer_name !== 'عميل بنكك') ? r.customer_name : '';
        }

        const phoneInput = document.getElementById('assign-cust-phone-input');
        if (phoneInput) {
            phoneInput.value = r.customer_phone || '';
        }

        const matchedCard = document.getElementById('assign-cust-matched-card');
        if (matchedCard) matchedCard.style.display = 'none';

        modal.style.display = 'flex';
        this.onAssignCustomerSearch(searchInput ? searchInput.value : '');
        setTimeout(() => { if (searchInput) searchInput.focus(); }, 80);
    },

    closeAssignCustomerModal: function() {
        const modal = document.getElementById('inbound-assign-customer-modal');
        if (modal) modal.style.display = 'none';
    },

    onAssignCustomerSearch: function(val) {
        const dropdown = document.getElementById('assign-cust-autocomplete');
        if (!dropdown) return;
        const matches = this.searchCustomersByCodeOrName(val);
        if (matches.length === 0) {
            dropdown.innerHTML = `<div style="padding:10px 14px; color:#fca5a5; font-size:0.82rem; font-weight:700;">لا يوجد عميل مطابق في القائمة — يمكنك كتابة اسم العميل الجديد مباشرة وسيتم إنشاؤه وتوجيه الإشعار له فوراً.</div>`;
            dropdown.style.display = 'block';
            return;
        }
        dropdown.innerHTML = matches.map(c => {
            const pinBadge = c.statementPin ? `<span style="background:rgba(16,185,129,0.22); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:1px 7px; border-radius:5px; font-family:monospace; font-size:0.76rem; font-weight:900;">🔐 باسورد: ${escapeHtml(String(c.statementPin))}</span>` : '';
            const codeBadge = c.receiptCodeNum ? `<span style="background:rgba(56,189,248,0.18); color:#38bdf8; padding:1px 7px; border-radius:5px; font-family:monospace; font-size:0.75rem;">#${escapeHtml(String(c.receiptCodeNum))}</span>` : '';
            const ph = escapeHtml(c.whatsapp || c.phone || 'بدون رقم');
            return `<div onclick="InboundBankakManager.selectAssignCustomerItem('${escapeHtml(String(c.id))}')" style="padding:9px 12px; cursor:pointer; border-bottom:1px solid rgba(255,255,255,0.07); display:flex; justify-content:space-between; align-items:center; gap:8px;" onmouseover="this.style.background='rgba(56,189,248,0.15)'" onmouseout="this.style.background='transparent'">
                <div style="display:flex; align-items:center; gap:7px; flex-wrap:wrap;">
                    <span style="font-size:1rem;">👤</span>
                    <strong style="color:#fff; font-size:0.9rem;">${escapeHtml(c.name || '')}</strong>
                    ${pinBadge}
                    ${codeBadge}
                </div>
                <span style="color:#94a3b8; font-size:0.76rem; font-family:monospace;" dir="ltr">${ph}</span>
            </div>`;
        }).join('');
        dropdown.style.display = 'block';

        // If exact match on PIN or code or name, highlight in card immediately
        const qNorm = this.normalizeDigitsAndArabic(val);
        if (qNorm) {
            const top = matches[0];
            const topPin = this.normalizeDigitsAndArabic(top.statementPin || '');
            const topCode = this.normalizeDigitsAndArabic(top.receiptCodeNum || '');
            const topName = this.normalizeDigitsAndArabic(top.name || '');
            if (qNorm === topPin || qNorm === topCode || qNorm === topName) {
                this.applySelectedAssignCustomer(top, false);
            }
        }
    },

    selectAssignCustomerItem: function(custId) {
        const all = this.getAllActiveCustomersAndSuppliers();
        const found = all.find(c => String(c.id) === String(custId));
        if (!found) return;
        this.applySelectedAssignCustomer(found, true);
    },

    applySelectedAssignCustomer: function(cust, hideDropdown = true) {
        if (!cust) return;
        document.getElementById('assign-cust-selected-id').value = cust.id || '';
        document.getElementById('assign-cust-selected-code').value = cust.statementPin || cust.receiptCodeNum || cust.name || '';
        const searchInput = document.getElementById('assign-cust-search-input');
        if (searchInput && hideDropdown) {
            searchInput.value = cust.name || '';
        }
        const dropdown = document.getElementById('assign-cust-autocomplete');
        if (dropdown && hideDropdown) dropdown.style.display = 'none';

        const card = document.getElementById('assign-cust-matched-card');
        const nameEl = document.getElementById('assign-cust-matched-name');
        const metaEl = document.getElementById('assign-cust-matched-meta');
        if (card && nameEl && metaEl) {
            nameEl.textContent = `✅ ${cust.name}`;
            metaEl.textContent = `🔐 الباسورد: ${cust.statementPin || 'غير محدد'} | 🔢 الكود: #${cust.receiptCodeNum || '-'} | 📱 واتساب: ${cust.whatsapp || cust.phone || 'غير مسجل'}`;
            card.style.display = 'block';
        }
        const phoneInput = document.getElementById('assign-cust-phone-input');
        if (phoneInput && (cust.whatsapp || cust.phone)) {
            phoneInput.value = cust.whatsapp || cust.phone;
        }
    },

    confirmAssignCustomerModal: async function() {
        const receiptId = document.getElementById('assign-cust-receipt-id')?.value;
        const selectedId = document.getElementById('assign-cust-selected-id')?.value || '';
        const selectedCode = document.getElementById('assign-cust-selected-code')?.value || '';
        const rawSearch = document.getElementById('assign-cust-search-input')?.value?.trim() || '';
        const phoneVal = document.getElementById('assign-cust-phone-input')?.value?.trim() || '';
        const linkPhone = Boolean(document.getElementById('assign-cust-link-phone')?.checked);

        const codeOrName = selectedCode || rawSearch;
        if (!codeOrName && !selectedId) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار العميل أو كتابة اسمه أو الباسورد أولاً', 'warning');
            else alert('يرجى اختيار العميل أو كتابة اسمه أو الباسورد أولاً');
            return;
        }

        const btn = document.getElementById('btn-confirm-assign-cust');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>⏳ جاري التوجيه والتنزيل الفوري في كشف الحساب...</span>';
        }

        const tId = this.getActiveTenantId();
        const bankSel = document.getElementById('drawer-target-bank-select');
        const bankId = (bankSel && bankSel.value) ? bankSel.value : (this.selectedBankId || '');

        try {
            const res = await fetch('api/inbound_receipts.php?action=assign_customer_by_code&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'assign_customer_by_code',
                    tenant_id: tId,
                    id: receiptId,
                    customer_id: selectedId,
                    customer_code: codeOrName,
                    customer_phone: phoneVal,
                    bank_id: bankId,
                    link_sender_phone: linkPhone
                })
            });
            const data = await res.json();
            if (data && data.success) {
                const getTenantKey = window.getTenantKey || ((k) => k);
                if (data.customer && data.customer.id) {
                    try {
                        const cKey = getTenantKey('erp_customers_v1');
                        let custs = JSON.parse(localStorage.getItem(cKey) || localStorage.getItem('erp_customers_v1') || '[]');
                        if (!Array.isArray(custs)) custs = [];
                        const idx = custs.findIndex(c => c && String(c.id) === String(data.customer.id));
                        if (idx > -1) custs[idx] = data.customer;
                        else custs.push(data.customer);
                        localStorage.setItem(cKey, JSON.stringify(custs));
                        localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
                    } catch(e) {}
                }
                const r = this.receipts.find(x => Number(x.id) === Number(receiptId)) || { id: receiptId };
                if (data.customer) {
                    r.customer_id = data.customer.id;
                    r.customer_name = data.customer.name;
                    if (phoneVal) r.customer_phone = phoneVal;
                }
                if (data.posting && data.posting.voucher_no) {
                    r.voucher_no = data.posting.voucher_no;
                    r.status = 'verified';
                    this.syncPostedReceiptToLocalErp(r, data.posting);
                }
                this.closeAssignCustomerModal();
                this.refreshReceipts();
                if (typeof window.applyLedgerFilters === 'function') {
                    try { window.buildAccountsList(); window.extractFilters(); window.applyLedgerFilters(); } catch (e) {}
                }
                if (typeof showToast === 'function') {
                    showToast(data.message || '✅ تم توجيه الإشعار وتنزيله في كشف حساب العميل بنجاح!', 'success', 6500);
                } else {
                    alert(data.message);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast('⚠️ ' + (data?.error || 'تعذر العثور على العميل بهذا الكود أو الاسم'), 'error', 6000);
                } else {
                    alert('⚠️ ' + (data?.error || 'تعذر العثور على العميل بهذا الكود أو الاسم'));
                }
            }
        } catch (err) {
            alert('خطأ في الاتصال: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
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

    // ═══ تنزيل وتمرير فوري في الحسابات بنقرة واحدة ═══
    postToAccountsDirect: async function(receiptId, btnElement) {
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span>🔄</span> <span>جاري التمرير...</span>';
        }
        try {
            const tId = this.getActiveTenantId();
            const bankSel = document.getElementById('drawer-target-bank-select');
            const bankId = (bankSel && bankSel.value) ? bankSel.value : (this.selectedBankId || this.selectedTargetBankId || '');
            const url = `api/inbound_receipts.php?action=post_to_accounts&id=${receiptId}&force_post=1&bank_id=${encodeURIComponent(bankId)}&tenant_id=${encodeURIComponent(tId)}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.success && data.voucher_no) {
                const r = this.receipts.find(x => x.id == receiptId) || { id: receiptId, voucher_no: data.voucher_no };
                r.voucher_no = data.voucher_no;
                r.status = 'verified';
                r.issue_reason = null;
                this.syncPostedReceiptToLocalErp(r, data);
                this.refreshReceipts();
                if (typeof window.applyLedgerFilters === 'function') {
                    try { window.buildAccountsList(); window.extractFilters(); window.applyLedgerFilters(); } catch (e) {}
                }
                if (typeof showToast === 'function') {
                    showToast(`✅ تم تمرير وتنزيل الإشعار في حساب (${data.customer_name}) وبنك (${data.bank_name || 'البنك'}) بالسند رقم (${data.voucher_no})!`, 'success');
                }
            } else {
                alert(data.error || 'تعذر تنزيل الإشعار في الحسابات');
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<span>✔️</span> <span>تمرير وترحيل للحساب</span>';
                }
            }
        } catch (e) {
            alert('خطأ أثناء التنزيل في الحسابات: ' + e.message);
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = '<span>✔️</span> <span>تمرير وترحيل للحساب</span>';
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
            const tId = this.getActiveTenantId();
            const res = await fetch(`api/inbound_receipts.php?action=scan_receipt&id=${receiptId}&tenant_id=${encodeURIComponent(tId)}`);
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
        this.syncMasterDataFromServer();
        this.currentReceiptForAlert = Object.assign({}, r);

        let phoneToUse = r.customer_phone || '';
        let matchedCust = null;
        if (r.customer_id || r.customer_name) {
            matchedCust = this.findCustomerByCodeOrName(r.customer_id || r.customer_name);
            if (matchedCust && !phoneToUse) {
                phoneToUse = matchedCust.whatsapp || matchedCust.phone || '';
            }
        }

        document.getElementById('cust-alert-receipt-id').value = r.id;
        const custIdEl = document.getElementById('cust-alert-customer-id');
        if (custIdEl) custIdEl.value = (matchedCust && matchedCust.id) ? matchedCust.id : (r.customer_id || '');
        const lookupEl = document.getElementById('cust-alert-code-lookup');
        if (lookupEl) lookupEl.value = '';
        const dropEl = document.getElementById('cust-alert-autocomplete');
        if (dropEl) dropEl.style.display = 'none';

        document.getElementById('cust-alert-phone').value = phoneToUse;
        document.getElementById('cust-alert-name').textContent = r.customer_name || (matchedCust ? matchedCust.name : 'عميل واتساب');
        document.getElementById('cust-alert-phone-display').textContent = phoneToUse || 'أدخل الرقم بالأسفل ليتم حفظه وإرساله';

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
        const phone = document.getElementById('cust-alert-phone')?.value?.trim() || '';
        const msg = document.getElementById('cust-alert-message-text')?.value || '';
        const custId = document.getElementById('cust-alert-customer-id')?.value || (this.currentReceiptForAlert && this.currentReceiptForAlert.customer_id) || '';
        const custName = (this.currentReceiptForAlert && this.currentReceiptForAlert.customer_name) || '';

        if (!phone) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال رقم هاتف الواتساب للعميل أولاً!', 'warning');
            else alert('رقم هاتف العميل غير مسجل! يرجى إدخال رقم الهاتف أولاً.');
            const phEl = document.getElementById('cust-alert-phone');
            if (phEl) phEl.focus();
            return;
        }

        // Persist the entered/updated WhatsApp phone to the customer's record in MySQL & localStorage (non-blocking)
        this.saveCustomerPhoneToBackend(custId, phone, custName);

        // Clean and format phone for WhatsApp (preserve @lid if present, otherwise Sudan 249)
        let cleanPhone = phone.trim();
        if (!cleanPhone.includes('@')) {
            cleanPhone = cleanPhone.replace(/\D/g, '');
            if (cleanPhone.startsWith('00')) cleanPhone = cleanPhone.substring(2);
            if (cleanPhone.startsWith('0') && cleanPhone.length === 10) {
                cleanPhone = '249' + cleanPhone.substring(1);
            } else if (cleanPhone.length === 9 && (cleanPhone.startsWith('9') || cleanPhone.startsWith('1'))) {
                cleanPhone = '249' + cleanPhone;
            }
        }

        const sendBtn = document.getElementById('btn-cust-alert-send-wa');
        const origBtnText = sendBtn ? sendBtn.innerHTML : '';
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span>⏳ جاري حفظ الرقم والإرسال المباشر عبر الواتساب...</span>';
        }

        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));

        try {
            const formData = new FormData();
            formData.append('userId', userId);
            formData.append('phone', cleanPhone);
            formData.append('message', msg);

            const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${encodeURIComponent(userId)}`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data && data.success) {
                this.closeCustomerAlertModal();
                if (typeof showToast === 'function') {
                    showToast(`⚡ تم حفظ الرقم وإرسال الإشعار مباشرة للعميل (${cleanPhone}) عبر واتساب بنجاح!`, 'success', 6500);
                }
            } else {
                throw new Error(data?.error || 'فشل إرسال الرسالة من خادم الواتساب');
            }
        } catch (err) {
            console.error('WA Direct Alert Error:', err);
            if (typeof showToast === 'function') {
                showToast(`⚠️ تعذر الإرسال المباشر (${err.message}) — جاري فتح واتساب ويب كبديل...`, 'warning', 5000);
            }
            const waUrl = `https://api.whatsapp.com/send?phone=${encodeURIComponent(cleanPhone.replace(/\D/g, ''))}&text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
            this.closeCustomerAlertModal();
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
                this.pruneOrphanedInboundVouchers(null, res.deleted_voucher_no || '', id);
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
