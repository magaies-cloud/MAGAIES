(function() {
    if (window.__SPLIT_CONSTITUTION15_LOCK__) return;
    window.__SPLIT_CONSTITUTION15_LOCK__ = true;

    // 1. الزوم الهندسي المتناسب (6 إلى 8) وحفظ مقاس الشاشة في localStorage وتوريثه للشاشات المشابهة والوليدة (حتى 10x)
    const ZOOM_KEYS = ['magaies_global_master_scale_v1', 'sales_c1_prop_zoom', 'erp_split_zoom_scale_v1', 'erp_global_laptop_zoom_scale_v1'];
    function getSavedProportionalZoom() {
        for (let i = 0; i < ZOOM_KEYS.length; i++) {
            const v = parseFloat(localStorage.getItem(ZOOM_KEYS[i]) || '');
            if (!isNaN(v) && v >= 0.25 && v <= 10.0) return v;
        }
        return 1;
    }

    let currentScale = getSavedProportionalZoom();

    window.applySalesProportionalZoom = function(scaleVal, showHudToast) {
        if (typeof window.setSalesFc1ProportionalZoom === 'function') {
            return window.setSalesFc1ProportionalZoom(scaleVal, 0, false);
        }
        currentScale = Math.min(10.0, Math.max(0.25, +(parseFloat(scaleVal) || 1).toFixed(2)));
        ZOOM_KEYS.forEach(function(k) {
            try { localStorage.setItem(k, String(currentScale)); } catch (e) {}
        });
        const zoomBadge = document.getElementById('split-c15-zoom-badge');
        if (zoomBadge) {
            zoomBadge.textContent = '🔍 ' + Math.round(currentScale * 100) + '%';
        }
    };

    // دعم Ctrl + 0 لإعادة ضبط الزوم إلى 100% فوراً، واختصارات اللابتوب الشاملة (F4, F8, F9)
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;

        if (e.ctrlKey && (e.key === '0' || e.code === 'Digit0' || e.code === 'Numpad0')) {
            e.preventDefault();
            window.applySalesProportionalZoom(1.0, true);
            return;
        }
        // F4 أو علامة (+ خارج حقول الإدخال الرقمية): فتح نافذة إضافة عميل جديد فوراً
        if (e.key === 'F4' || (e.key === '+' && (!e.target || (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA')))) {
            e.preventDefault();
            if (typeof window.openModal === 'function') {
                window.openModal('add-customer-modal');
            }
            return;
        }
        // F8: فتح التقرير العالمي وكشف حساب التقسيم (Drill-Down)
        if (e.key === 'F8') {
            e.preventDefault();
            if (typeof window.openSplitGlobalLedgerReportModal === 'function') {
                window.openSplitGlobalLedgerReportModal();
            }
            return;
        }
        // F9: توزيع الكميات بالتساوي (50% / 50%)
        if (e.key === 'F9') {
            e.preventDefault();
            if (window.activeSplitInvoice && typeof window.distributeSplitEqually === 'function') {
                window.distributeSplitEqually();
            }
            return;
        }
    });

    // 2. الإضافة العالمية (1): التوزيع الذكي التلقائي حسب سعة الشاحنة الأولى (Smart Split by Truck Capacity)
    window.splitByTruckCapacityPrompt = function() {
        if (!window.activeSplitInvoice || !Array.isArray(window.activeSplitInvoice.items)) return;
        const capStr = window.prompt('🚛 أدخل الحمولة القصوى للشاحنة الأولى (بالوحدة الأساسية للفاتورة، وسيتم توجيه الفائض للشاحنة التالية تلقائياً):', '10');
        if (capStr === null) return;
        const maxCap = parseFloat(window.normalizeSplitDigitsAndMath ? window.normalizeSplitDigitsAndMath(capStr, true) : capStr);
        if (isNaN(maxCap) || maxCap <= 0) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال سعة شاحنة صحيحة أكبر من صفر', 'warning');
            return;
        }
        if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot('توزيع بسعة الشاحنة');

        let remainingTruckCap = maxCap;
        const S = window.splitSectionsCount || 2;
        (window.activeSplitInvoice.items || []).forEach(function(item, idx) {
            const meta = window.resolveSplitItemUnitMeta(item);
            const rem = meta.deliverableQty;
            const firstTake = Math.min(rem, Math.max(0, +remainingTruckCap.toFixed(4)));
            remainingTruckCap = Math.max(0, +(remainingTruckCap - firstTake).toFixed(4));
            const restTotal = Math.max(0, +(rem - firstTake).toFixed(4));

            const arr = [firstTake];
            const otherCount = Math.max(1, S - 1);
            let sumOthers = 0;
            for (let s = 1; s < S; s++) {
                if (s === S - 1) {
                    arr.push(Math.max(0, +(restTotal - sumOthers).toFixed(4)));
                } else {
                    const share = +(restTotal / otherCount).toFixed(3);
                    arr.push(share);
                    sumOthers += share;
                }
            }
            window.activeSplitInvoice._splitDraftQtys[idx] = arr;
        });

        if (typeof window.renderWorldClassSplitBoard === 'function') window.renderWorldClassSplitBoard();
        if (typeof showToast === 'function') {
            showToast('🚛 تم تعبئة القسم الأول حتى سعة (' + maxCap + ') وتوجيه الفائض للأقسام التالية بنجاح', 'success');
        }
    };

    // 3. الإضافة العالمية (2): نسخ ملخص التقسيم المنسق للحافظة بضغطة زر
    window.copySplitSummaryToClipboard = function() {
        const inv = window.activeSplitInvoice;
        if (!inv) return;
        const S = window.splitSectionsCount || 2;
        const rootNo = inv.rootInvoiceNo || inv.invoiceNo || inv.id;
        const lines = [
            '✂️ ملخص تقسيم الفاتورة / الإذن المرجعي #' + rootNo,
            '👤 العميل: ' + (inv.customerName || 'عميل نقدي') + ' | 📅 التاريخ: ' + (inv.date || new Date().toISOString().slice(0, 10)),
            '────────────────────────────────────'
        ];
        for (let s = 0; s < S; s++) {
            const secNo = rootNo + '/' + (s + 1);
            const rec = (document.getElementById('split-sec-receiver-' + s)?.value || (window._splitSectionMeta && window._splitSectionMeta[s]?.receiver) || 'غير محدد');
            const trk = (document.getElementById('split-sec-truck-' + s)?.value || (window._splitSectionMeta && window._splitSectionMeta[s]?.truck) || 'غير محدد');
            const secItems = [];
            let secTotal = 0;
            (inv.items || []).forEach(function(it, idx) {
                const m = window.resolveSplitItemUnitMeta(it);
                const q = parseFloat((inv._splitDraftQtys && inv._splitDraftQtys[idx] && inv._splitDraftQtys[idx][s]) || 0) || 0;
                if (q > 0) {
                    secItems.push(m.name + ': ' + q + ' ' + m.primaryUnit);
                    secTotal += q * m.price;
                }
            });
            lines.push('📦 القسم (' + (s + 1) + ') [#' + secNo + '] — الصافي: ' + secTotal.toLocaleString() + ' ' + (inv.currency || 'SDG'));
            lines.push('   👷 المستلم: ' + rec + ' | 🚛 المركبة: ' + trk);
            lines.push('   📋 الأصناف: ' + (secItems.join(' + ') || 'لا توجد كميات'));
        }
        const text = lines.join('\n');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof showToast === 'function') showToast('📋 تم نسخ ملخص التقسيم المنسق إلى الحافظة بنجاح', 'success');
            });
        }
    };

    // 4. الإضافة العالمية (3 و 4): رادار توازن الحمولة والأوزان + شريط اختصارات الكيبورد ومقاس الزوم
    window.renderC15ShortcutsAndPayloadStrip = function() {
        const boardStep = document.getElementById('split-board-step');
        const liveBar = document.getElementById('split-live-balance-bar');
        if (!boardStep || !liveBar || !window.activeSplitInvoice) return;

        let strip = document.getElementById('split-c15-shortcuts-weight-strip');
        if (!strip) {
            strip = document.createElement('div');
            strip.id = 'split-c15-shortcuts-weight-strip';
            liveBar.insertAdjacentElement('afterend', strip);
        }

        const inv = window.activeSplitInvoice;
        const S = window.splitSectionsCount || 2;
        const weightBadges = [];
        for (let s = 0; s < S; s++) {
            let secUnitsSum = 0;
            (inv.items || []).forEach(function(it, idx) {
                const q = parseFloat((inv._splitDraftQtys && inv._splitDraftQtys[idx] && inv._splitDraftQtys[idx][s]) || 0) || 0;
                secUnitsSum += q;
            });
            weightBadges.push('<span style="padding:2px 8px; border-radius:6px; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.4); color:#7dd3fc;">🚛 حمولة قسم (' + (s + 1) + '): <strong style="font-family:monospace;">' + (+secUnitsSum.toFixed(2)) + '</strong> وحدة</span>');
        }

        strip.innerHTML = `
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <span style="color:#fbbf24;">⚖️ رادار الحمولة:</span>
                ${weightBadges.join('')}
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F2 بحث</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F4 عميل</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F8 كشف</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F9 بالتساوي</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F10 اعتماد</span>
                <span id="split-c15-zoom-badge" onclick="window.applySalesProportionalZoom(1.0, true)" style="cursor:pointer; padding:2px 8px; border-radius:5px; background:rgba(16,185,129,0.18); border:1px solid #10b981; color:#6ee7b7;" title="Ctrl + رولة الماوس للتكبير والتصغير المتناسب (اضغط لإعادة الضبط 100%)">🔍 ${Math.round(currentScale * 100)}%</span>
            </div>
        `;
    };

    const origRecalcC15 = window.recalcSplitLiveBalance;
    if (typeof origRecalcC15 === 'function' && !origRecalcC15._c15Wrapped) {
        const wrappedRecalcC15 = function() {
            const res = origRecalcC15.apply(this, arguments);
            try { window.renderC15ShortcutsAndPayloadStrip(); } catch (e) {}
            return res;
        };
        wrappedRecalcC15._c15Wrapped = true;
        window.recalcSplitLiveBalance = wrappedRecalcC15;
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.applySalesProportionalZoom(currentScale, false);
    });
})();
