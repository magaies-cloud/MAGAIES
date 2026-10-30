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
                    <span id="assign-cust-receipt-subtitle" style="font-size:0.75rem; color:#94a3b8; font-weight:700;">اختر العميل بالاسم أو الباسورد السري وسيتم قيد الإشعار في كشف حسابه فوراً</span>
                </div>
            </div>
            <button type="button" onclick="InboundBankakManager.closeAssignCustomerModal()" style="background:none; border:none; color:#94a3b8; font-size:1.3rem; cursor:pointer; font-weight:900;">✕</button>
        </div>

        <input type="hidden" id="assign-cust-receipt-id">
        <input type="hidden" id="assign-cust-selected-id">
        <input type="hidden" id="assign-cust-selected-code">
        <input type="hidden" id="assign-modal-selected-code">

        <div style="position:relative;">
            <label style="font-size:0.82rem; font-weight:900; color:#38bdf8; margin-bottom:5px; display:block;">
                🔍 ابحث باسم العميل أو الباسورد السري (مثال: 127) أو الكود الرقمي: *
            </label>
            <input type="text" id="assign-cust-search-input" autocomplete="off" oninput="InboundBankakManager.onAssignCustomerSearch(this.value)" onfocus="InboundBankakManager.onAssignCustomerSearch(this.value)" placeholder="اكتب اسم العميل أو الباسورد السري (مثل 127)..." style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.5); border:1.5px solid rgba(56,189,248,0.5); border-radius:10px; padding:9px 12px; color:#ffd700; font-size:0.95rem; font-weight:900; font-family:'Tajawal',sans-serif; outline:none;">
            <div id="assign-cust-autocomplete" style="display:none; position:absolute; top:100%; right:0; left:0; background:#0f172a; border:1.5px solid #10b981; border-radius:10px; max-height:210px; overflow-y:auto; z-index:999999; margin-top:4px; box-shadow:0 15px 35px rgba(0,0,0,0.9);"></div>
            <div id="assign-cust-matched-card" style="display:none; margin-top:6px; padding:7px 10px; border-radius:8px; background:rgba(16,185,129,0.14); border:1px solid rgba(16,185,129,0.45); color:#6ee7b7; font-size:0.82rem; font-weight:800;"><div id="assign-cust-matched-name" style="font-weight:900;"></div><div id="assign-cust-matched-meta" style="font-size:0.74rem; color:#a7f3d0; margin-top:2px;"></div></div>
        </div>

        <div>
            <label style="font-size:0.80rem; font-weight:800; color:#34d399; margin-bottom:4px; display:block;">
                📱 رقم واتساب العميل (يُحفظ في بطاقة العميل ويرسل إليه إشعار التأكيد فوراً):
            </label>
            <input type="tel" id="assign-cust-phone-input" placeholder="09xxxxxxxx أو 01xxxxxxxx" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.45); border:1.5px solid rgba(37,211,102,0.45); border-radius:9px; padding:8px 12px; color:#7dd3fc; font-family:'Consolas',monospace; font-size:0.92rem; font-weight:900; direction:ltr; text-align:right;">
        </div>

        <label style="display:flex; align-items:center; gap:8px; font-size:0.80rem; color:#cbd5e1; font-weight:700; cursor:pointer; background:rgba(255,255,255,0.04); padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
            <input type="checkbox" id="assign-cust-link-phone" checked style="width:16px; height:16px; accent-color:#10b981;">
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

<script>window.INBOUND_ACTIVE_TENANT = '<?php echo addslashes($currentActiveTenant); ?>';</script>
<script defer src="js/inbound-bankak-drawer-core.js?v=20261029_fast"></script>
