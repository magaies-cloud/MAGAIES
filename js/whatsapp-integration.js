/**
 * WhatsApp Integration Logic
 * Requires: html2pdf.bundle.min.js to be loaded in the HTML
 */

const WA_SERVER_URL = 'api_index.php?r=wa-tenant&endpoint=';

// Add WhatsApp Modal to the DOM
function initWhatsAppModal() {
    if (document.getElementById('wa-modal-overlay')) return;

    const modalHTML = `
        <div id="wa-modal-overlay" class="modal-overlay" style="z-index: 100000; background: rgba(0,0,0,0.8);">
            <div class="modal-card" style="max-width: 400px; text-align: center; border-color: #25D366;">
                <div class="modal-header" style="border-bottom-color: rgba(37, 211, 102, 0.2);">
                    <h3 style="color: #25D366; display:flex; align-items:center; gap:8px; justify-content:center; margin:0;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="#25d366" style="vertical-align: middle;">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                        </svg>
                        ربط واتساب
                    </h3>
                    <button class="modal-close" onclick="closeWAModal()">✕</button>
                </div>
                <div class="modal-body" style="color: #f1f5f9;">
                    <p id="wa-status-text" style="margin-bottom: 15px; font-weight: bold;">جاري التحقق من حالة الاتصال...</p>
                    <div id="wa-qr-container" style="background: white; padding: 15px; border-radius: 12px; display: inline-block; min-width: 250px; min-height: 250px; margin-bottom: 15px; box-sizing: border-box; vertical-align: middle;">
                    </div>
                    <p id="wa-instruction-text" style="font-size: 0.85rem; color: #94a3b8;">امسح هذا الرمز باستخدام تطبيق واتساب في هاتفك لربط حسابك بالنظام المحاسبي.</p>
                    
                    <div class="wa-help-section" style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 15px; text-align: center;">
                        <button onclick="toggleWAInstructions()" style="background: transparent; border: none; color: #25d366; font-size: 0.85rem; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; outline: none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                                <path d="M17 17h.01"></path>
                            </svg>
                            📖 تعليمات ربط الأجهزة (أندرويد وآيفون)
                        </button>
                        <div id="wa-instructions-panel" style="display: none; text-align: right; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px; margin-top: 10px; font-size: 0.8rem; line-height: 1.6; animation: wa-scale-up 0.3s ease-out; max-height: 140px; overflow-y: auto;">
                            <div style="font-weight: bold; color: #25d366; margin-bottom: 8px; display: flex; align-items: center; gap: 5px;">
                                📱 خطوات ربط رقم الواتساب بالشركة:
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>🍏 لمستخدمي آيفون (iPhone):</strong><br>
                                افتح واتساب ← <b>الإعدادات</b> ← <b>الأجهزة المرتبطة</b> ← اضغط على <b>ربط جهاز</b>.
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>🤖 لمستخدمي أندرويد (Android):</strong><br>
                                افتح واتساب ← اضغط على <b>النقاط الثلاث (⋮)</b> بأعلى اليسار ← <b>الأجهزة المرتبطة</b> ← اضغط على <b>ربط جهاز</b>.
                            </div>
                            <div style="color: #fbbf24; font-weight: 500; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 8px; margin-top: 8px;">
                                ⚠️ <strong>ملاحظة هامة جداً:</strong><br>
                                يجب إبقاء شاشة الهاتف وشاشة الكمبيوتر مفتوحتين دون إغلاق ومتابعة عملية المزامنة حتى اكتمال الربط بالكامل وظهور علامة النجاح.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="display:flex; justify-content:center; gap:10px; border-top-color: rgba(37, 211, 102, 0.2);">
                    <button class="btn btn-primary" id="wa-logout-btn" style="background: #ef4444; border-color: #ef4444; display: none;" onclick="logoutWhatsApp()">إلغاء الربط (تسجيل خروج)</button>
                    <button class="btn btn-ghost" id="wa-refresh-btn" onclick="checkWAStatus()">تحديث الحالة</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Inject custom CSS styles for the animations
    if (!document.getElementById('wa-custom-styles')) {
        const styleHTML = `
            <style id="wa-custom-styles">
                @keyframes rotate-sync {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @keyframes wa-pulse {
                    0% { transform: scale(0.9); opacity: 0.8; }
                    50% { transform: scale(1.15); opacity: 0; }
                    100% { transform: scale(0.9); opacity: 0; }
                }
                @keyframes wa-scale-up {
                    0% { transform: scale(0.6); opacity: 0; }
                    100% { transform: scale(1); opacity: 1; }
                }
                #wa-instructions-panel::-webkit-scrollbar {
                    width: 5px;
                }
                #wa-instructions-panel::-webkit-scrollbar-track {
                    background: rgba(255, 255, 255, 0.02);
                    border-radius: 4px;
                }
                #wa-instructions-panel::-webkit-scrollbar-thumb {
                    background: rgba(255, 255, 255, 0.15);
                    border-radius: 4px;
                }
                #wa-instructions-panel::-webkit-scrollbar-thumb:hover {
                    background: rgba(255, 255, 255, 0.3);
                }

                /* ── Light Mode overrides for WhatsApp Modal ── */
                :root.light-theme #wa-modal-overlay .modal-card {
                    background: #ffffff !important;
                    border: 2px solid #25D366 !important;
                    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
                }
                :root.light-theme #wa-modal-overlay .modal-header {
                    border-bottom: 1px solid rgba(37, 211, 102, 0.15) !important;
                }
                :root.light-theme #wa-modal-overlay .modal-header h3 {
                    color: #075e54 !important;
                }
                :root.light-theme #wa-modal-overlay .modal-header .modal-close {
                    color: #475569 !important;
                }
                :root.light-theme #wa-modal-overlay .modal-body {
                    color: #1e293b !important;
                }
                :root.light-theme #wa-modal-overlay #wa-status-text {
                    color: #0f172a !important;
                }
                :root.light-theme #wa-modal-overlay #wa-instruction-text {
                    color: #475569 !important;
                }
                :root.light-theme #wa-modal-overlay #wa-qr-container {
                    border: 1px solid #e2e8f0 !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
                }
                :root.light-theme #wa-modal-overlay .wa-help-section {
                    border-top: 1px solid rgba(0, 0, 0, 0.08) !important;
                }
                :root.light-theme #wa-modal-overlay .wa-help-section button {
                    color: #075e54 !important;
                }
                :root.light-theme #wa-modal-overlay #wa-instructions-panel {
                    background: #f8fafc !important;
                    border: 1px solid #e2e8f0 !important;
                    color: #334155 !important;
                }
                :root.light-theme #wa-modal-overlay #wa-instructions-panel div,
                :root.light-theme #wa-modal-overlay #wa-instructions-panel strong {
                    color: #334155 !important;
                }
                :root.light-theme #wa-modal-overlay .modal-footer {
                    border-top: 1px solid rgba(37, 211, 102, 0.15) !important;
                }
                :root.light-theme #wa-modal-overlay #wa-refresh-btn {
                    background: #f8fafc !important;
                    border: 1px solid #cbd5e1 !important;
                    color: #0f172a !important;
                    padding: 8px 16px !important;
                    border-radius: 8px !important;
                    font-weight: bold !important;
                    cursor: pointer !important;
                    transition: all 0.2s !important;
                    display: inline-block !important;
                }
                :root.light-theme #wa-modal-overlay #wa-refresh-btn:hover {
                    background: #f1f5f9 !important;
                    border-color: #94a3b8 !important;
                }
                :root.light-theme #wa-modal-overlay .modal-close {
                    color: #64748b !important;
                    background: transparent !important;
                    border: none !important;
                    font-size: 1.2rem !important;
                    cursor: pointer !important;
                    opacity: 0.7 !important;
                    transition: opacity 0.2s !important;
                }
                :root.light-theme #wa-modal-overlay .modal-close:hover {
                    opacity: 1 !important;
                }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', styleHTML);
    }

    setWAContainerState('LOADING');
}

window.toggleWAInstructions = function() {
    const panel = document.getElementById('wa-instructions-panel');
    if (!panel) return;
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
};

function openWAModal() {
    initWhatsAppModal();
    document.getElementById('wa-modal-overlay').classList.add('visible');
    checkWAStatus();
}

function closeWAModal() {
    const modal = document.getElementById('wa-modal-overlay');
    if (modal) modal.classList.remove('visible');
}

function formatPhoneForDisplay(phone) {
    if (!phone) return '';
    let cleaned = phone.replace(/[^0-9]/g, '');
    if (cleaned.length === 0) return phone;
    if (!cleaned.startsWith('+')) {
        cleaned = '+' + cleaned;
    }
    return cleaned;
}

let currentWAState = 'LOADING';
let isLoggingOut = false;

function setWAContainerState(state, data = '') {
    const container = document.getElementById('wa-qr-container');
    if (!container) return;

    currentWAState = state;
    let html = '';
    switch (state) {
        case 'LOADING':
            html = `
                <div class="wa-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 220px; color: #1e293b;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="animation: rotate-sync 1.5s infinite linear; margin-bottom: 15px;">
                        <line x1="12" y1="2" x2="12" y2="6"></line>
                        <line x1="12" y1="18" x2="12" y2="22"></line>
                        <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                        <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                        <line x1="2" y1="12" x2="6" y2="12"></line>
                        <line x1="18" y1="12" x2="22" y2="12"></line>
                        <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                        <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                    </svg>
                    <div style="font-weight: bold; font-size: 0.95rem; color: #1e293b;">جاري توليد رمز الـ QR...</div>
                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 5px;">امسح الرمز فور ظهوره لربط الواتساب</div>
                </div>
            `;
            break;
        case 'QR_CODE':
            html = `
                <img id="wa-qr-image" src="${data}" alt="QR Code" style="width: 220px; height: 220px; display: block; margin: 0 auto; animation: wa-scale-up 0.4s ease-out;" />
            `;
            break;
        case 'AUTHENTICATING':
            html = `
                <div class="wa-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 220px; color: #1e293b; text-align: center; padding: 10px;">
                    <div class="wa-pulse-wrapper" style="position: relative; display: flex; align-items: center; justify-content: center; width: 80px; height: 80px; margin-bottom: 18px;">
                        <div style="position: absolute; width: 100px; height: 100px; border: 4px solid #25d366; border-radius: 50%; opacity: 0.4; animation: wa-pulse 1.8s infinite ease-in-out;"></div>
                        <div style="position: absolute; width: 75px; height: 75px; border: 4px solid #25d366; border-radius: 50%; opacity: 0.7; animation: wa-pulse 1.8s infinite ease-in-out; animation-delay: 0.4s;"></div>
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="z-index: 2; animation: rotate-sync 2.5s infinite linear;">
                            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                        </svg>
                    </div>
                    <div style="font-weight: 800; font-size: 1.1rem; color: #075e54; margin-bottom: 6px;">🔄 جاري ربط الجهازين...</div>
                    <div style="font-size: 0.82rem; color: #475569; font-weight: 600; max-width: 290px; line-height: 1.4;">يرجى إبقاء الهاتفين مفتوحين لتكتمل المزامنة والربط بنجاح</div>
                </div>
            `;
            break;
        case 'CONNECTED':
            const formattedPhone = formatPhoneForDisplay(data);
            const phoneBadge = formattedPhone ? `
                <div style="margin-top: 10px; background: rgba(37, 211, 102, 0.12); border: 1px solid rgba(37, 211, 102, 0.35); padding: 10px 18px; border-radius: 12px; display: inline-flex; align-items: center; gap: 10px; direction: ltr;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#25D366"><path d="M20 15.5c-1.2 0-2.4-.2-3.6-.6-.3-.1-.7 0-1 .2l-2.2 2.2c-2.8-1.4-5.1-3.8-6.6-6.6l2.2-2.2c.3-.3.4-.7.2-1-.4-1.2-.6-2.4-.6-3.6 0-.6-.5-1-1-1H4c-.6 0-1 .5-1 1 0 9.4 7.6 17 17 17 .6 0 1-.5 1-1v-3.5c0-.6-.5-1-1-1z"/></svg>
                    <span style="color: #25d366; font-family: 'Segoe UI', Tahoma, monospace; font-size: 1.15rem; font-weight: 800; letter-spacing: 0.5px;">${formattedPhone}</span>
                </div>
            ` : '';

            html = `
                <div class="wa-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 220px; color: #1e293b; text-align: center; padding: 10px;">
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 75px; height: 75px; border-radius: 50%; background: linear-gradient(135deg, rgba(37, 211, 102, 0.2), rgba(18, 140, 126, 0.2)); border: 3px solid #25d366; margin-bottom: 12px; box-shadow: 0 0 25px rgba(37, 211, 102, 0.35); animation: wa-scale-up 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: #15803d; margin-bottom: 2px;">
                        الواتساب مربوط بحسابك ومفعل بنجاح
                    </div>
                    ${phoneBadge}
                    <div style="font-size: 0.82rem; color: #64748b; font-weight: 500; margin-top: 12px; line-height: 1.5; max-width: 290px;">
                        حساب الواتساب جاهز ومفعل حالياً لإرسال الفواتير والتقارير المالية مباشرة للعملاء.
                    </div>
                </div>
            `;
            break;
        case 'CONFIRM_LOGOUT':
            html = `
                <div class="wa-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 220px; color: #1e293b; text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 70px; height: 70px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); border: 3px solid #ef4444; margin-bottom: 15px; animation: wa-scale-up 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <svg width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div style="font-weight: bold; font-size: 0.95rem; color: #1e293b; margin-bottom: 15px;">هل أنت متأكد من إلغاء ربط الرقم؟</div>
                    <div style="display: flex; gap: 10px; width: 100%; justify-content: center;">
                        <button onclick="confirmAndExecuteLogout()" class="btn" style="background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: bold; font-size: 0.85rem; cursor: pointer; transition: background 0.2s;">نعم، إلغاء</button>
                        <button onclick="cancelLogout()" class="btn" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-weight: bold; font-size: 0.85rem; cursor: pointer; transition: background 0.2s;">تراجع</button>
                    </div>
                </div>
            `;
            break;
        case 'LOGGED_OUT_SUCCESS':
            html = `
                <div class="wa-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 220px; color: #1e293b; text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 75px; height: 75px; border-radius: 50%; background: rgba(34, 197, 94, 0.1); border: 3px solid #22c55e; margin-bottom: 20px; animation: wa-scale-up 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div style="font-weight: bold; font-size: 1.1rem; color: #15803d; margin-bottom: 5px;">تم إلغاء الرقم بنجاح</div>
                    <div style="font-size: 0.78rem; color: #64748b; font-weight: 500;">جاري إعداد رمز QR لربط رقم آخر...</div>
                </div>
            `;
            break;
        case 'ERROR':
        case 'QR_FAILED':
            html = `
                <div class="wa-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 220px; color: #1e293b; text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 75px; height: 75px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); border: 3px solid #f59e0b; margin-bottom: 16px; animation: wa-scale-up 0.4s ease-out;">
                        <svg width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div style="font-weight: bold; font-size: 0.95rem; color: #92400e; margin-bottom: 6px;">تعذر توليد رمز QR حالياً</div>
                    <div style="font-size: 0.78rem; color: #64748b; font-weight: 500; margin-bottom: 16px; max-width: 280px; line-height: 1.5;">خادم واتساب لم يستجب. يرجى المحاولة مرة أخرى بعد قليل.</div>
                    <button onclick="checkWAStatus()" class="btn" style="background: linear-gradient(135deg, #25D366, #128C7E); color: white; border: none; padding: 10px 28px; border-radius: 10px; font-weight: bold; font-size: 0.9rem; cursor: pointer; box-shadow: 0 4px 12px rgba(37,211,102,0.3); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">🔄 إعادة المحاولة</button>
                </div>
            `;
            break;
    }
    container.innerHTML = html;
}

async function checkWAStatus(retryCount = 0) {
    const userId = getWAUserId();
    const statusText = document.getElementById('wa-status-text');
    const instructionText = document.getElementById('wa-instruction-text');
    const logoutBtn = document.getElementById('wa-logout-btn');
    const refreshBtn = document.getElementById('wa-refresh-btn');

    if (logoutBtn) logoutBtn.style.display = 'none';
    if (refreshBtn) refreshBtn.style.display = 'inline-block';

    try {
        const res = await fetch(`${WA_SERVER_URL}status&userId=${userId}`);
        const data = await res.json();
        if (isLoggingOut) return;

        if (data.status === 'OFFLINE_CLOUD') {
            if (statusText) {
                statusText.textContent = '🌐 اللابتوب غير متصل بالإنترنت — واتساب السحابة يعمل 24/7 للعملاء';
                statusText.style.color = '#38bdf8';
            }
            if (instructionText) {
                instructionText.style.display = 'block';
                instructionText.textContent = 'خادم الواتساب السحابي (c.elsajana.com) مستمر في استقبال الإشعارات والرد التلقائي على الأرصدة، وأي رسالة ترسلها من اللابتوب الآن ستُحفظ وتُرسل تلقائياً فور عودة الإنترنت.';
            }
            const container = document.getElementById('wa-qr-container');
            if (container) {
                container.innerHTML = `
                    <div class="wa-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 220px; color: #1e293b; text-align: center; padding: 10px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">☁️⚡</div>
                        <div style="font-weight: 800; font-size: 1rem; color: #0284c7; margin-bottom: 6px;">الربط السحابي الدائم نشط (24/7)</div>
                        <div style="font-size: 0.8rem; color: #475569; line-height: 1.5;">اللابتوب يعمل حالياً بدون إنترنت. ستُرسل رسائلك المعلقة تلقائياً بمجرد عودة الشبكة.</div>
                    </div>
                `;
            }
            return;
        }

        if ((data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') || (data.status !== 'QR_READY' && data.status !== 'FAILED' && data.phoneNumber && data.phoneNumber.length > 5)) {
            if (currentWAState === 'QR_CODE') {
                // Artificial delay to show "Pairing..." after scanning
                setWAContainerState('AUTHENTICATING');
                if (statusText) {
                    statusText.textContent = '🔄 جاري ربط الجهازين... يرجى إبقاء الهاتفين مفتوحين';
                    statusText.style.color = '#fbbf24';
                }
                if (instructionText) instructionText.style.display = 'none';
                
                setTimeout(() => {
                    if (statusText) {
                        statusText.textContent = '✅ الواتساب مربوط بحسابك ومفعل.';
                        statusText.style.color = '#34d399';
                    }
                    setWAContainerState('CONNECTED', data.phoneNumber || '');
                    if (logoutBtn) logoutBtn.style.display = 'inline-block';
                }, 3500);
                return;
            }

            if (statusText) {
                statusText.textContent = '✅ الواتساب مربوط بحسابك ومفعل.';
                statusText.style.color = '#34d399';
            }
            setWAContainerState('CONNECTED', data.phoneNumber || '');
            if (logoutBtn) logoutBtn.style.display = 'inline-block';
            if (instructionText) instructionText.style.display = 'none';
        } else if (data.status === 'CONNECTING' || data.status === 'AUTHENTICATING') {
            setWAContainerState('AUTHENTICATING');
            if (statusText) {
                statusText.textContent = '🔄 جاري ربط الجهازين... يرجى إبقاء الهاتف مفتوحاً';
                statusText.style.color = '#fbbf24';
            }
            if (instructionText) instructionText.style.display = 'none';
            setTimeout(() => {
                if (document.getElementById('wa-modal-overlay')?.classList.contains('visible')) {
                    checkWAStatus();
                }
            }, 2000);
        } else {
            // Not connected with phone number: fetch and display QR code for linking
            fetchWAQRCode();
        }
    } catch (err) {
        if (retryCount < 3) {
            console.warn(`checkWAStatus failed (attempt ${retryCount + 1}/3), retrying...`, err.message);
            setTimeout(() => {
                if (document.getElementById('wa-modal-overlay')?.classList.contains('visible')) {
                    checkWAStatus(retryCount + 1);
                }
            }, 2000);
            return;
        }
        if (statusText) {
            statusText.textContent = '❌ تعذر الاتصال بخادم الواتساب المحلي.';
            statusText.style.color = '#ef4444';
        }
        setWAContainerState('ERROR');
    }
}

async function fetchWAQRCode(qrRetryCount = 0) {
    if (isLoggingOut) return;
    const userId = getWAUserId();
    const instructionText = document.getElementById('wa-instruction-text');
    const statusText = document.getElementById('wa-status-text');

    // After 8 retries (~20 seconds), show error with manual retry button
    if (qrRetryCount >= 8) {
        if (statusText) {
            statusText.textContent = '⚠️ تعذر توليد رمز QR حالياً';
            statusText.style.color = '#ef4444';
        }
        setWAContainerState('QR_FAILED');
        return;
    }

    try {
        const res = await fetch(`${WA_SERVER_URL}qr&userId=${userId}`);
        const data = await res.json();
        if (isLoggingOut) return;

        if ((data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') || (data.status !== 'QR_READY' && data.status !== 'FAILED' && data.phoneNumber && data.phoneNumber.length > 5)) {
            if (currentWAState === 'QR_CODE') {
                // Artificial delay to show "Pairing..." after scanning
                setWAContainerState('AUTHENTICATING');
                if (statusText) {
                    statusText.textContent = '🔄 جاري ربط الجهازين... يرجى إبقاء الهاتفين مفتوحين';
                    statusText.style.color = '#fbbf24';
                }
                if (instructionText) instructionText.style.display = 'none';
                
                setTimeout(() => {
                    if (statusText) {
                        statusText.textContent = '✅ الواتساب مربوط بحسابك ومفعل.';
                        statusText.style.color = '#34d399';
                    }
                    setWAContainerState('CONNECTED', data.phoneNumber || '');
                    const logoutBtn = document.getElementById('wa-logout-btn');
                    if (logoutBtn) logoutBtn.style.display = 'inline-block';
                }, 3500);
                return;
            }

            setWAContainerState('CONNECTED', data.phoneNumber);
            if (statusText) {
                statusText.textContent = '✅ الواتساب مربوط بحسابك ومفعل.';
                statusText.style.color = '#34d399';
            }
            const logoutBtn = document.getElementById('wa-logout-btn');
            if (logoutBtn) logoutBtn.style.display = 'inline-block';
            if (instructionText) instructionText.style.display = 'none';
        } else if (data.qrCode) {
            // QR code available! Show it immediately
            setWAContainerState('QR_CODE', data.qrCode);
            if (instructionText) instructionText.style.display = 'block';
            if (statusText) {
                statusText.textContent = 'يرجى مسح الرمز أدناه لربط الواتساب:';
                statusText.style.color = '#fbbf24';
            }

            setTimeout(() => {
                if (document.getElementById('wa-modal-overlay')?.classList.contains('visible')) {
                    checkWAStatus();
                }
            }, 3000);
        } else if (data.status === 'CONNECTING' || data.status === 'AUTHENTICATING') {
            setWAContainerState('AUTHENTICATING');
            if (statusText) {
                statusText.textContent = '🔄 جاري ربط الجهازين... يرجى إبقاء الهاتف مفتوحاً';
                statusText.style.color = '#fbbf24';
            }
            if (instructionText) instructionText.style.display = 'none';
            setTimeout(() => {
                if (document.getElementById('wa-modal-overlay')?.classList.contains('visible')) {
                    checkWAStatus();
                }
            }, 2000);
        } else if (data.status === 'FAILED') {
            // Server gave up trying — show error
            if (statusText) {
                statusText.textContent = '⚠️ تعذر الاتصال بخادم واتساب';
                statusText.style.color = '#ef4444';
            }
            setWAContainerState('QR_FAILED');
        } else {
            // Still connecting/initializing — show loading and retry
            if (qrRetryCount === 0) {
                setWAContainerState('LOADING');
            }
            if (statusText) {
                statusText.textContent = '🔄 جاري توليد رمز الـ QR لربط الواتساب...';
                statusText.style.color = '#fbbf24';
            }
            if (instructionText) instructionText.style.display = 'none';
            setTimeout(() => {
                if (document.getElementById('wa-modal-overlay')?.classList.contains('visible')) {
                    fetchWAQRCode(qrRetryCount + 1);
                }
            }, 2500);
        }
    } catch (err) {
        console.error('QR fetch error', err);
        setTimeout(() => {
            if (document.getElementById('wa-modal-overlay')?.classList.contains('visible')) {
                fetchWAQRCode(qrRetryCount + 1);
            }
        }, 3000);
    }
}

async function logoutWhatsApp() {
    // Prevent background polling from interfering with the logout flow
    isLoggingOut = true;
    
    // Show custom confirmation state inside the container
    setWAContainerState('CONFIRM_LOGOUT');
    const logoutBtn = document.getElementById('wa-logout-btn');
    const refreshBtn = document.getElementById('wa-refresh-btn');
    if (logoutBtn) logoutBtn.style.display = 'none';
    if (refreshBtn) refreshBtn.style.display = 'none';
}

function cancelLogout() {
    isLoggingOut = false;
    checkWAStatus();
}

async function confirmAndExecuteLogout() {
    isLoggingOut = true;
    const userId = getWAUserId();
    const statusText = document.getElementById('wa-status-text');
    
    setWAContainerState('LOADING');
    if (statusText) {
        statusText.textContent = '🔄 جاري إلغاء ربط الرقم...';
        statusText.style.color = '#fbbf24';
    }

    try {
        await fetch(`${WA_SERVER_URL}logout&userId=${userId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userId })
        });
        
        setWAContainerState('LOGGED_OUT_SUCCESS');
        if (statusText) {
            statusText.textContent = '✅ تم إلغاء الرقم بنجاح. جاري استدعاء رمز الـ QR لربط رقم جديد...';
            statusText.style.color = '#34d399';
        }
        
        // After 1.2 seconds, fetch fresh QR code directly so user can scan a new number immediately
        setTimeout(() => {
            isLoggingOut = false; // Allow polling again
            if (document.getElementById('wa-modal-overlay')?.classList.contains('visible')) {
                fetchWAQRCode(0);
            }
        }, 1200);
        
    } catch (err) {
        console.error('Logout error:', err);
        setWAContainerState('ERROR');
        isLoggingOut = false;
    }
}

function getWAUserId() {
    try {
        const userStr = localStorage.getItem('user') || '{}';
        const user = JSON.parse(userStr);
        const tenantId = user.tenant_id || localStorage.getItem('currentTenantId') || localStorage.getItem('tenant_id') || localStorage.getItem('erp_tenant_id') || '12';
        return 'wa_tenant_' + tenantId;
    } catch (e) {
        return 'wa_tenant_12';
    }
}
window.getWAUserId = getWAUserId;

/**
 * Convert HTML element to PDF and send via WhatsApp
 * @param {HTMLElement} elementToPrint - The DOM element to convert to PDF
 * @param {String} phoneNumber - Customer's WhatsApp number
 * @param {String} message - Text message to send along with PDF
 */
async function sendPDFViaWhatsApp(elementToPrint, phoneNumber, message) {
    if (!phoneNumber) {
        alert('يرجى إدخال رقم الهاتف (يجب أن يحتوي على المفتاح الدولي).');
        return;
    }

    const queueFallback = () => {
        if (typeof window.enqueueWhatsAppOutboxMessage === 'function') {
            window.enqueueWhatsAppOutboxMessage({
                userId: getWAUserId(),
                phone: phoneNumber,
                message: message || 'تم إصدار مستند مالي جديد في حسابكم.'
            });
        } else {
            try {
                const q = JSON.parse(localStorage.getItem('erp_wa_outbox_queue_v1') || '[]');
                q.push({
                    id: 'waq_' + Date.now(),
                    userId: getWAUserId(),
                    phone: phoneNumber,
                    message: message || 'تم إصدار مستند مالي جديد في حسابكم.',
                    createdAt: new Date().toISOString()
                });
                localStorage.setItem('erp_wa_outbox_queue_v1', JSON.stringify(q));
            } catch (e) {}
        }
        if (typeof showToast === 'function') {
            showToast('📥 اللابتوب غير متصل بالإنترنت — تم حفظ الرسالة في طابور الانتظار وسيتم إرسالها تلقائياً فور عودة الإنترنت', 'info', 6000);
        }
    };

    if (navigator.onLine === false) {
        queueFallback();
        return;
    }
    
    // Check status first
    try {
        const statusRes = await fetch(`${WA_SERVER_URL}status&userId=${getWAUserId()}`);
        const statusData = await statusRes.json();
        if (statusData.status === 'OFFLINE_CLOUD') {
            queueFallback();
            return;
        }
        if (statusData.status !== 'CONNECTED' && statusData.status !== 'AUTHENTICATED') {
            alert('يجب ربط الواتساب أولاً قبل إرسال الفاتورة.');
            openWAModal();
            return;
        }
    } catch (err) {
        if (navigator.onLine === false) {
            queueFallback();
            return;
        }
        alert('خادم الواتساب غير متصل حالياً.');
        return;
    }

    // Show loading
    showToast('جاري إرسال المستند عبر الواتساب...', 'warning');

    try {
        const formData = new FormData();
        formData.append('userId', getWAUserId());
        formData.append('phone', phoneNumber);
        formData.append('message', message || '');

        if (elementToPrint instanceof File) {
            formData.append('file', elementToPrint);
        } else if (elementToPrint instanceof Blob) {
            const isVoucher = (typeof message === 'string' && (message.includes('سند') || message.includes('حركة')));
            const defaultName = isVoucher ? "voucher.pdf" : "document.pdf";
            const file = new File([elementToPrint], defaultName, { type: "application/pdf" });
            formData.append('file', file);
        } else if (elementToPrint && elementToPrint.tagName === 'CANVAS') {
            const blob = await new Promise(res => elementToPrint.toBlob(res, 'image/png'));
            if (blob) {
                const file = new File([blob], "voucher.png", { type: "image/png" });
                formData.append('file', file);
            }
        } else if (elementToPrint && typeof html2pdf !== 'undefined') {
            // CRITICAL FIX: html2canvas respects opacity. If elementToPrint has opacity < 0.5 (e.g. 0.01), html2canvas renders it blank!
            if (elementToPrint.style && parseFloat(elementToPrint.style.opacity) < 0.5) {
                elementToPrint.style.opacity = '1';
            }
            const isVoucher = (typeof message === 'string' && (message.includes('سند') || message.includes('حركة')));
            const defaultName = isVoucher ? "voucher.pdf" : "invoice.pdf";
            const opt = {
                margin: [6, 6, 6, 6],
                filename: defaultName,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            const pdfBlob = await html2pdf().set(opt).from(elementToPrint).output('blob');
            const file = new File([pdfBlob], defaultName, { type: "application/pdf" });
            formData.append('file', file);
        }

        const sendRes = await fetch(`${WA_SERVER_URL}send&userId=${getWAUserId()}`, {
            method: 'POST',
            body: formData
        });

        const sendData = await sendRes.json();
        if (sendData.success) {
            showToast('✅ تم إرسال المستند بنجاح عبر الواتساب!', 'success', 4000);
        } else if (sendData.offline_queued) {
            queueFallback();
        } else {
            alert('فشل الإرسال: ' + (sendData.error || 'Unknown error'));
        }
    } catch (err) {
        console.error('Error sending PDF', err);
        if (navigator.onLine === false) {
            queueFallback();
        } else {
            alert('حدث خطأ غير متوقع أثناء الإرسال.');
        }
    }
}

function showWAConnectPrompt() {
    return new Promise((resolve) => {
        const overlayId = 'wa-custom-prompt-overlay';
        if (document.getElementById(overlayId)) return resolve(false);

        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);">
            <div style="background:linear-gradient(145deg, #1e293b, #0f172a); border:1px solid rgba(37,211,102,0.3); border-radius:24px; padding:30px; width:90%; max-width:400px; text-align:center; transform:translateY(30px) scale(0.95); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
                <div style="width:70px; height:70px; background:rgba(37,211,102,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#25d366;">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h3 style="color:#f8fafc; font-size:1.4rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0;">الواتساب غير مربوط</h3>
                <p style="color:#94a3b8; font-size:1rem; margin-bottom:25px; line-height:1.6; font-family:'Tajawal',sans-serif;">رقم الواتساب الخاص بك غير مربوط بالنظام حالياً. هل تريد ربط رقمك الآن لتتمكن من إرسال الفواتير؟</p>
                
                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-yes" style="background:#25d366; color:#fff; border:none; padding:12px 25px; border-radius:10px; font-weight:bold; font-size:1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s; box-shadow:0 4px 15px rgba(37,211,102,0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">نعم، ربط الآن</button>
                    <button id="wa-prompt-no" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:10px; font-weight:bold; font-size:1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">لا، لاحقاً</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        
        // Animate in
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0) scale(1)';
        });

        const closePrompt = (result) => {
            overlay.style.opacity = '0';
            modal.style.transform = 'translateY(30px) scale(0.95)';
            setTimeout(() => {
                overlay.remove();
                resolve(result);
            }, 300);
        };

        document.getElementById('wa-prompt-yes').onclick = () => closePrompt(true);
        document.getElementById('wa-prompt-no').onclick = () => closePrompt(false);
    });
}
