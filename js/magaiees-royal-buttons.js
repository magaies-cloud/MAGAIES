/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 👑 MAGAIEES ROYAL LIQUID-CRYSTAL BUTTON ENGINE (محرك الأزرار الكريستالية الموحد)
 * - Pointer-tracking glass spotlight (--royal-mx, --royal-my)
 * - Luminous click wave ripple + subtle mobile haptic pulse
 * - Staggered periodic crystal shimmer waves
 * - Auto-classification of core semantic buttons across Desktop & Mobile
 * - Universal Desktop Sync Shield (🛡️) Modal & Long-Press Diagnostics
 * ═══════════════════════════════════════════════════════════════════════════
 */
(function () {
    'use strict';

    if (window.__MAGAIEES_ROYAL_BTN_ENGINE_ACTIVE__) return;
    window.__MAGAIEES_ROYAL_BTN_ENGINE_ACTIVE__ = true;

    const BTN_SELECTOR = [
        '.royal-crystal-btn',
        '.crystal-btn',
        '.items-toolbar .btn-action',
        '.unified-footer-bar .btn',
        '#magaiees-master-back-btn',
        '#magaiees-master-themes-btn',
        '.luxury-action-btn',
        '.btn-add',
        '#btnSaveVoucher',
        '#btnNewVoucher',
        '#btnOpenVoucher',
        '#btnEditVoucher',
        '#btnSendWhatsApp',
        '#btnPrintVoucher',
        '#btnDeleteVoucher',
        '#btnBackVoucher'
    ].join(', ');

    function classifyAndEnhanceButtons(root) {
        const scope = root || document;
        const allButtons = scope.querySelectorAll('button, a.btn, .crystal-btn, .btn-action, .btn-add');
        let idx = 0;

        allButtons.forEach((btn) => {
            if (btn.dataset.royalEnhanced === '1') return;

            const id = (btn.id || '').toLowerCase();
            const cls = (btn.className || '').toLowerCase();
            const txt = (btn.innerText || btn.textContent || '').trim();
            const onclickAttr = (btn.getAttribute('onclick') || '');

            // Top modal close X buttons
            if (
                txt === '✕' || txt === '❌' ||
                onclickAttr.includes('closeModal(') ||
                onclickAttr.includes('closeGLColumnModal') ||
                onclickAttr.includes('closeWAVoucherModal')
            ) {
                if (txt === '✕' || txt === '❌') {
                    btn.classList.add('royal-modal-close-x');
                    btn.dataset.royalEnhanced = '1';
                    return;
                }
            }

            // Determine if this button belongs to an action toolbar, modal footer, or primary operation
            const isCoreBtn =
                btn.matches(BTN_SELECTOR) ||
                cls.includes('royal-') ||
                cls.includes('btn-save') ||
                cls.includes('btn-new') ||
                cls.includes('btn-exit') ||
                cls.includes('btn-delete') ||
                cls.includes('btn-submit') ||
                cls.includes('btn-share') ||
                cls.includes('btn-preview') ||
                cls.includes('btn-print') ||
                onclickAttr.includes('submit') ||
                onclickAttr.includes('save') ||
                onclickAttr.includes('SafeInvoiceGuard.requestExit') ||
                onclickAttr.includes('exitToMainDashboard') ||
                onclickAttr.includes('goBackOrExit');

            if (!isCoreBtn) return;

            btn.classList.add('royal-crystal-btn');
            btn.style.setProperty('--shimmer-delay', ((idx * 0.45) % 4.5).toFixed(2) + 's');
            idx++;

            // Apply unified semantic color class if not explicitly set
            const hasSemantic =
                btn.classList.contains('royal-btn-save') ||
                btn.classList.contains('royal-btn-new') ||
                btn.classList.contains('royal-btn-open') ||
                btn.classList.contains('royal-btn-ledger') ||
                btn.classList.contains('royal-btn-edit') ||
                btn.classList.contains('royal-btn-print') ||
                btn.classList.contains('royal-btn-whatsapp') ||
                btn.classList.contains('royal-btn-share') ||
                btn.classList.contains('royal-btn-delete') ||
                btn.classList.contains('royal-btn-exit') ||
                btn.classList.contains('royal-btn-back');

            if (!hasSemantic) {
                if (
                    id.includes('save') || id.includes('submit') ||
                    cls.includes('btn-save') || cls.includes('btn-submit') ||
                    txt.includes('حفظ') || txt.includes('اعتماد') || txt.includes('ترحيل')
                ) {
                    if (!txt.includes('إرسال تلقائي') && !txt.includes('مسودة')) {
                        btn.classList.add('royal-btn-save');
                    }
                } else if (
                    id.includes('new') || cls.includes('btn-new') ||
                    txt.includes('جديد') || txt.includes('تفريغ الحقول')
                ) {
                    btn.classList.add('royal-btn-new');
                } else if (
                    id.includes('delete') || cls.includes('btn-delete') ||
                    txt.includes('حذف') || txt === 'مسح' || txt.includes('❌ إلغاء')
                ) {
                    btn.classList.add('royal-btn-delete');
                } else if (
                    id.includes('exit') || cls.includes('btn-exit') || cls.includes('tab-exit') ||
                    txt.includes('خروج')
                ) {
                    btn.classList.add('royal-btn-exit');
                } else if (
                    id === 'magaiees-master-back-btn' || txt.includes('الرجوع للخلف') || txt === 'رجوع' || txt.includes('↩️ رجوع')
                ) {
                    btn.classList.add('royal-btn-back');
                } else if (
                    id.includes('print') || id.includes('preview') ||
                    cls.includes('btn-print') || cls.includes('btn-preview') ||
                    txt.includes('طباعة') || txt.includes('معاينة')
                ) {
                    btn.classList.add('royal-btn-print');
                } else if (
                    id.includes('whatsapp') || id.includes('share') ||
                    cls.includes('btn-share') || cls.includes('btn-whatsapp') ||
                    txt.includes('واتساب') || txt.includes('مشاركة')
                ) {
                    btn.classList.add('royal-btn-whatsapp');
                } else if (
                    id.includes('open') || id.includes('ledger') ||
                    txt.includes('افتح') || txt.includes('كشف حساب') || txt.includes('أستاذ')
                ) {
                    btn.classList.add('royal-btn-open');
                } else if (
                    id.includes('edit') || cls.includes('btn-draft') ||
                    txt.includes('تعديل') || txt.includes('مسودة')
                ) {
                    btn.classList.add('royal-btn-edit');
                }
            }

            btn.dataset.royalEnhanced = '1';
        });
    }

    // Track pointer position for interactive glass spotlight & ripple wave
    document.addEventListener('pointermove', function (e) {
        const btn = e.target && e.target.closest ? e.target.closest('.royal-crystal-btn') : null;
        if (!btn) return;
        const rect = btn.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / Math.max(rect.width, 1)) * 100;
        const y = ((e.clientY - rect.top) / Math.max(rect.height, 1)) * 100;
        btn.style.setProperty('--royal-mx', x.toFixed(1) + '%');
        btn.style.setProperty('--royal-my', y.toFixed(1) + '%');
    }, { passive: true });

    document.addEventListener('pointerdown', function (e) {
        const btn = e.target && e.target.closest ? e.target.closest('.royal-crystal-btn') : null;
        if (!btn) return;
        const rect = btn.getBoundingClientRect();
        const wave = document.createElement('span');
        wave.className = 'royal-click-wave';
        wave.style.left = (e.clientX - rect.left) + 'px';
        wave.style.top = (e.clientY - rect.top) + 'px';
        btn.appendChild(wave);
        setTimeout(() => {
            if (wave.parentNode) wave.parentNode.removeChild(wave);
        }, 680);

        // Subtle tactile micro-vibration on mobile devices
        if (e.pointerType === 'touch' && navigator.vibrate) {
            try { navigator.vibrate(10); } catch (_) {}
        }
    }, { passive: true });

    // ─── Universal Desktop Sync Shield Modal & Long-Press Inspector ───
    window.openDesktopSyncAuditModal = function () {
        if (typeof window.openSyncAuditLogModal === 'function') {
            window.openSyncAuditLogModal();
            return;
        }
        let modal = document.getElementById('magaieesDesktopSyncShieldModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'magaieesDesktopSyncShieldModal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(2,6,23,0.85);backdrop-filter:blur(10px);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Tajawal,sans-serif;';
            modal.innerHTML = `
                <div style="background:linear-gradient(145deg,#0f172a,#1e293b);border:2px solid #10b981;border-radius:20px;width:100%;max-width:620px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.8),0 0 30px rgba(16,185,129,0.25);overflow:hidden;">
                    <div style="padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:space-between;background:rgba(16,185,129,0.1);">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:1.5rem;">🛡️</span>
                            <div>
                                <h3 style="margin:0;color:#fff;font-size:1.1rem;font-weight:900;">درع الحماية وسجل المزامنة التلقائية</h3>
                                <div id="desktopSyncShieldSub" style="color:#6ee7b7;font-size:0.78rem;font-weight:700;">مزامنة خلفية فورية 100% لجميع السندات والفواتير</div>
                            </div>
                        </div>
                        <button type="button" class="royal-modal-close-x" onclick="document.getElementById('magaieesDesktopSyncShieldModal').style.display='none'">✕</button>
                    </div>
                    <div id="desktopSyncShieldBody" style="padding:16px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:10px;color:#e2e8f0;font-size:0.88rem;"></div>
                    <div style="padding:12px 18px;border-top:1px solid rgba(255,255,255,0.1);display:flex;gap:10px;justify-content:space-between;background:rgba(15,23,42,0.9);">
                        <button type="button" class="royal-crystal-btn royal-btn-save" style="flex:1;padding:9px 14px;border-radius:10px;cursor:pointer;" onclick="window.forceDesktopImmediateSync()">⚡ دفع ومزامنة فورية الآن</button>
                        <button type="button" class="royal-crystal-btn royal-btn-exit" style="padding:9px 18px;border-radius:10px;cursor:pointer;" onclick="document.getElementById('magaieesDesktopSyncShieldModal').style.display='none'">🚪 إغلاق</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        renderDesktopSyncShieldContent();
        modal.style.display = 'flex';
    };

    function renderDesktopSyncShieldContent() {
        const body = document.getElementById('desktopSyncShieldBody');
        if (!body) return;
        let q1 = [], q2 = [], audit = [];
        try { q1 = JSON.parse(localStorage.getItem('magaies_offline_sync_queue_v2') || '[]'); } catch (_) {}
        try { q2 = JSON.parse(localStorage.getItem('magaiees_offline_vouchers_queue') || '[]'); } catch (_) {}
        try { audit = JSON.parse(localStorage.getItem('magaiees_sync_audit_log_v1') || '[]'); } catch (_) {}
        const pendingCount = q1.length + q2.length;
        const isOnline = navigator.onLine;

        let html = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);border-radius:12px;padding:10px;text-align:center;">
                    <div style="font-size:0.78rem;color:#94a3b8;font-weight:800;">حالة الشبكة والمزامنة</div>
                    <div style="font-size:1.05rem;font-weight:900;color:${isOnline ? '#34d399' : '#fbbf24'};margin-top:4px;">
                        ${isOnline ? '🟢 متصل (مزامنة حية)' : '🟠 أوفلاين (حفظ محلي محمي)'}
                    </div>
                </div>
                <div style="background:rgba(56,189,248,0.12);border:1px solid rgba(56,189,248,0.35);border-radius:12px;padding:10px;text-align:center;">
                    <div style="font-size:0.78rem;color:#94a3b8;font-weight:800;">عمليات بانتظار الرفع</div>
                    <div style="font-size:1.25rem;font-weight:900;color:${pendingCount > 0 ? '#fbbf24' : '#38bdf8'};margin-top:4px;">
                        ${pendingCount} عملية
                    </div>
                </div>
            </div>
        `;

        if (audit.length === 0) {
            html += `<div style="text-align:center;padding:24px;color:#94a3b8;background:rgba(255,255,255,0.03);border-radius:12px;">✅ جميع العمليات متزامنة ومطابقة لقاعدة البيانات المركزية 100%</div>`;
        } else {
            html += `<div style="font-weight:900;color:#fbbf24;margin-top:4px;">📋 أحدث عمليات الدرع والمزامنة (${audit.length}):</div>`;
            audit.slice(0, 15).forEach((item) => {
                const ok = item.status === 'synced';
                html += `
                    <div style="background:rgba(255,255,255,0.04);border:1px solid ${ok ? 'rgba(16,185,129,0.35)' : 'rgba(245,158,11,0.4)'};border-radius:10px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:800;color:#fff;">${item.type || 'سند / فاتورة'} ${item.ref ? ('#' + item.ref) : ''}</div>
                            <div style="font-size:0.75rem;color:#94a3b8;">${item.time || ''} ${item.party ? ('• ' + item.party) : ''}</div>
                        </div>
                        <span style="padding:3px 10px;border-radius:8px;font-size:0.75rem;font-weight:900;background:${ok ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)'};color:${ok ? '#34d399' : '#fbbf24'};">
                            ${ok ? '✓ تم الرفع' : '⏳ بالانتظار'}
                        </span>
                    </div>
                `;
            });
        }
        body.innerHTML = html;
    }

    window.forceDesktopImmediateSync = async function () {
        if (window.MagaiesOfflineSync && typeof window.MagaiesOfflineSync.flushQueue === 'function') {
            await window.MagaiesOfflineSync.flushQueue();
        }
        if (typeof window.syncOfflineVouchersQueue === 'function') {
            await window.syncOfflineVouchersQueue();
        }
        renderDesktopSyncShieldContent();
        updateDesktopShieldBadge();
    };

    function updateDesktopShieldBadge() {
        const badge = document.getElementById('desktop-sync-shield-badge');
        const btn = document.getElementById('desktop-sync-shield-btn');
        if (!btn) return;
        let q1 = [], q2 = [];
        try { q1 = JSON.parse(localStorage.getItem('magaies_offline_sync_queue_v2') || '[]'); } catch (_) {}
        try { q2 = JSON.parse(localStorage.getItem('magaiees_offline_vouchers_queue') || '[]'); } catch (_) {}
        const total = q1.length + q2.length;
        if (badge) {
            badge.textContent = String(total);
            badge.style.display = total > 0 ? 'flex' : 'none';
        }
        if (total > 0) {
            btn.style.borderColor = '#fbbf24';
            btn.style.color = '#fbbf24';
        } else {
            btn.style.borderColor = 'rgba(16, 185, 129, 0.55)';
            btn.style.color = '#34d399';
        }
    }

    function init() {
        classifyAndEnhanceButtons(document);
        updateDesktopShieldBadge();
        setInterval(updateDesktopShieldBadge, 4000);

        // Long-press on Desktop Sync Shield
        const shieldBtn = document.getElementById('desktop-sync-shield-btn');
        if (shieldBtn && !shieldBtn.dataset.lpBound) {
            shieldBtn.dataset.lpBound = '1';
            let lpTimer = null;
            shieldBtn.addEventListener('pointerdown', () => {
                lpTimer = setTimeout(() => {
                    window.openDesktopSyncAuditModal();
                    window.forceDesktopImmediateSync();
                }, 600);
            });
            ['pointerup', 'pointerleave', 'pointercancel'].forEach((ev) => {
                shieldBtn.addEventListener(ev, () => {
                    if (lpTimer) { clearTimeout(lpTimer); lpTimer = null; }
                });
            });
        }

        // Observe DOM mutations so dynamically rendered buttons also get Royal Crystal enhancements
        if (window.MutationObserver && document.body) {
            const obs = new MutationObserver((mutations) => {
                for (let i = 0; i < mutations.length; i++) {
                    if (mutations[i].addedNodes && mutations[i].addedNodes.length > 0) {
                        classifyAndEnhanceButtons(document);
                        break;
                    }
                }
            });
            obs.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
