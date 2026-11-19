(function() {
    if (window.__SPLIT_CONSTITUTION8_LOCK__) return;
    window.__SPLIT_CONSTITUTION8_LOCK__ = true;

    const PARKED_KEY = 'erp_split_parked_vouchers_v1';
    const RECENT_COMMITS_KEY = 'erp_split_recent_commits_v1';

    // 1. الآلة الحاسبة المدمجة داخل الخانات الرقمية (Inline Math Calculator: 12*24, 5000+1500, 100/4)
    window.evalSplitInlineMath = function(rawExpr) {
        if (rawExpr === null || rawExpr === undefined) return 0;
        let s = String(rawExpr).trim();
        if (!s) return 0;
        if (window.normalizeSplitNumericString) {
            s = window.normalizeSplitNumericString(s, true, true);
        }
        // إزالة أي عامل حسابي في آخر النص أثناء الكتابة الحية مثل "12*" -> "12"
        const cleanForLive = s.replace(/[+\-*/.(]+$/, '');
        if (!cleanForLive) return 0;
        if (!/^[0-9.+\-*/()\s]+$/.test(cleanForLive)) {
            return parseFloat(cleanForLive) || 0;
        }
        try {
            const val = Function('"use strict"; return (' + cleanForLive + ');')();
            if (typeof val === 'number' && isFinite(val)) {
                return +val.toFixed(4);
            }
        } catch (err) {}
        return parseFloat(cleanForLive) || 0;
    };

    window.commitSplitInlineMathInput = function(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        const id = el.id || '';
        if (id.indexOf('split-qty-') !== 0 && id.indexOf('split-subqty-') !== 0 && id !== 'split-operation-seq-input') return false;
        const raw = String(el.value || '').trim();
        if (!/[+\-*/()]/.test(raw)) return false;

        const evaluated = window.evalSplitInlineMath(raw);
        const finalVal = id === 'split-operation-seq-input' ? Math.max(1, Math.round(evaluated)) : Math.max(0, evaluated);
        el.value = String(finalVal);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        if (typeof showToast === 'function') {
            showToast('🧮 الآلة الحاسبة المدمجة: ' + raw + ' = ' + finalVal, 'info');
        }
        return true;
    };

    // تنفيذ المعادلة الحسابية لحظة ضغط Enter أو الخروج من الخانة (blur)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target && e.target.closest && e.target.closest('#split-invoice-view')) {
            window.commitSplitInlineMathInput(e.target);
        }
    }, true);

    document.addEventListener('blur', function(e) {
        if (e.target && e.target.closest && e.target.closest('#split-invoice-view')) {
            window.commitSplitInlineMathInput(e.target);
        }
    }, true);

    // 2. محرك التفقيط العربي اللحظي المعتمد لمنع خداع الأصفار (Instant Amount-in-Words)
    const onesAr = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
    const tensAr = ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    const hundredsAr = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

    function convertThreeDigitsAr(n) {
        n = Math.floor(n % 1000);
        if (n === 0) return '';
        const h = Math.floor(n / 100);
        const rem = n % 100;
        const parts = [];
        if (h > 0) parts.push(hundredsAr[h]);
        if (rem > 0) {
            if (rem < 20) {
                parts.push(onesAr[rem]);
            } else {
                const u = rem % 10;
                const t = Math.floor(rem / 10);
                if (u > 0) parts.push(onesAr[u] + ' و' + tensAr[t]);
                else parts.push(tensAr[t]);
            }
        }
        return parts.join(' و');
    }

    function convertIntegerToArabicWords(num) {
        num = Math.floor(Math.abs(Number(num) || 0));
        if (num === 0) return 'صفر';
        const groups = [
            { scale: 1000000000, single: 'مليار', dual: 'ملياران', plural: 'مليارات', acc: 'ملياراً' },
            { scale: 1000000, single: 'مليون', dual: 'مليونان', plural: 'ملايين', acc: 'مليوناً' },
            { scale: 1000, single: 'ألف', dual: 'ألفان', plural: 'آلاف', acc: 'ألفاً' }
        ];
        const parts = [];
        let rem = num;
        for (let i = 0; i < groups.length; i++) {
            const g = groups[i];
            const count = Math.floor(rem / g.scale);
            rem = rem % g.scale;
            if (count > 0) {
                if (count === 1) parts.push(g.single);
                else if (count === 2) parts.push(g.dual);
                else if (count >= 3 && count <= 10) parts.push(convertThreeDigitsAr(count) + ' ' + g.plural);
                else parts.push(convertThreeDigitsAr(count) + ' ' + g.acc);
            }
        }
        if (rem > 0) {
            parts.push(convertThreeDigitsAr(rem));
        }
        return parts.join(' و');
    }

    window.tafqeetSplitAmountArabic = function(amount, currencyCode) {
        const val = Math.max(0, Number(amount) || 0);
        const intPart = Math.floor(val);
        const decPart = Math.round((val - intPart) * 100);
        const isUsd = String(currencyCode || '').toUpperCase() === 'USD' || String(currencyCode || '').indexOf('$') !== -1;
        const curName = isUsd ? 'دولار أمريكي' : 'جنيه سوداني';
        const subCurName = isUsd ? 'سنت' : 'قرش';

        let words = 'فقط ' + convertIntegerToArabicWords(intPart) + ' ' + curName;
        if (decPart > 0) {
            words += ' و' + convertIntegerToArabicWords(decPart) + ' ' + subCurName;
        }
        return words + ' لا غير';
    };

    // 3. حارس الإرهاق الذكي لكشف الأرقام الشاذة وأخطاء الأصابع (Fat-Finger Anomaly Guard)
    let lastAnomalyToastAt = 0;
    window.triggerSplitFatFingerAnomaly = function(inputEl, typedVal, maxAllowed, unitLabel) {
        if (inputEl) {
            inputEl.classList.remove('c8-anomaly-amber-pulse');
            void inputEl.offsetWidth;
            inputEl.classList.add('c8-anomaly-amber-pulse');
            setTimeout(function() {
                inputEl.classList.remove('c8-anomaly-amber-pulse');
            }, 2800);
        }
        const now = Date.now();
        if (now - lastAnomalyToastAt > 1800 && typeof showToast === 'function') {
            lastAnomalyToastAt = now;
            showToast(`🛡️ تنبيه تركيز: الرقم المدخل (${typedVal}) يتجاوز المتاح (${maxAllowed} ${unitLabel || ''}) — تم ضبطه للحد الأقصى`, 'warning');
        }
    };

    // 4. ميزة "تعليق الفاتورة بضغطة زر" واسترجاعها (One-Click Park & Resume)
    window.getParkedSplitVouchers = function() {
        try {
            return JSON.parse(localStorage.getItem(PARKED_KEY) || '[]') || [];
        } catch (e) { return []; }
    };

    window.renderParkedSplitVouchersBar = function() {
        const bar = document.getElementById('split-parked-vouchers-bar');
        if (!bar) return;
        const list = window.getParkedSplitVouchers();
        if (!list.length) {
            bar.style.display = 'none';
            bar.innerHTML = '';
            return;
        }
        bar.style.display = 'flex';
        const pillsHtml = list.map(function(p) {
            const escNo = String(p.invoiceNo).replace(/'/g, "\\'");
            return `<div style="display:inline-flex; align-items:center; gap:6px; background:rgba(2,6,23,0.82); border:1.8px solid #f59e0b; padding:4px 10px; border-radius:99px; font-size:0.8rem; font-weight:900; color:#fde68a;">
                <button type="button" onclick="window.resumeParkedSplitInvoice('${escNo}')" style="background:transparent; border:none !important; min-height:auto !important; padding:0 !important; color:#fde68a; font-weight:900; cursor:pointer; box-shadow:none !important;">
                    ▶️ استئناف #${p.invoiceNo} (${p.customerName || 'عميل نقدي'} • ${p.sectionsCount || 2} أقسام)
                </button>
                <button type="button" onclick="window.removeParkedSplitInvoiceByNo('${escNo}')" title="إزالة من المعلقات" style="background:rgba(239,68,68,0.25); border:1px solid #ef4444 !important; min-height:22px !important; width:22px !important; padding:0 !important; border-radius:50% !important; color:#fca5a5; font-size:0.72rem; cursor:pointer;">✕</button>
            </div>`;
        }).join('');

        bar.innerHTML = `
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-weight:900; font-size:0.85rem; color:#fbbf24;">⏸️ فواتير معلقة بانتظار الاستكمال (${list.length}):</span>
                ${pillsHtml}
            </div>
        `;
    };

    window.parkCurrentSplitInvoice = function() {
        if (!window.activeSplitInvoice) {
            if (typeof showToast === 'function') showToast('ℹ️ لا توجد فاتورة مفتوحة حالياً لتعليقها', 'info');
            return;
        }
        if (typeof window.saveCurrentSplitDraftState === 'function') {
            window.saveCurrentSplitDraftState();
        }
        if (typeof window.persistActiveSplitDraftToStorage === 'function') {
            window.persistActiveSplitDraftToStorage();
        }
        const inv = window.activeSplitInvoice;
        const invNo = String(inv.invoiceNo || inv.id || '');
        const list = window.getParkedSplitVouchers().filter(function(x) { return String(x.invoiceNo) !== invNo; });
        list.unshift({
            invoiceNo: invNo,
            customerName: inv.customerName || 'عميل نقدي',
            grandTotal: parseFloat(inv.grandTotal || inv.total || 0) || 0,
            currency: inv.currency || 'SDG',
            sectionsCount: window.splitSectionsCount || 2,
            splitSeq: window.currentSplitOperationSeq || 1,
            unitMode: window.splitUnitDisplayMode || 'both',
            qtys: JSON.parse(JSON.stringify(inv._splitDraftQtys || {})),
            meta: JSON.parse(JSON.stringify(window._splitSectionMeta || {})),
            parkedAt: Date.now()
        });
        localStorage.setItem(PARKED_KEY, JSON.stringify(list.slice(0, 15)));

        window.activeSplitInvoice = null;
        if (typeof activeSplitInvoice !== 'undefined') activeSplitInvoice = null;
        const boardStep = document.getElementById('split-board-step');
        const selectStep = document.getElementById('split-select-step');
        if (boardStep) boardStep.style.display = 'none';
        if (selectStep) selectStep.style.display = 'block';
        window.renderParkedSplitVouchersBar();
        if (typeof loadSplitInvoices === 'function') loadSplitInvoices(true);
        const searchInp = document.getElementById('split-invoice-search');
        if (searchInp) {
            searchInp.focus();
            if (searchInp.select) try { searchInp.select(); } catch (e) {}
        }
        if (typeof showToast === 'function') {
            showToast(`⏸️ تم تعليق تقسيم الفاتورة #${invNo} مؤقتاً في الشريط العلوي لخدمة العميل التالي`, 'success');
        }
    };

    window.resumeParkedSplitInvoice = function(invoiceNo) {
        const list = window.getParkedSplitVouchers();
        const found = list.find(function(x) { return String(x.invoiceNo) === String(invoiceNo); });
        if (typeof prepareInvoiceForSplit === 'function') {
            prepareInvoiceForSplit(invoiceNo, found ? found.unitMode : undefined);
        }
        if (found && window.activeSplitInvoice) {
            window.splitSectionsCount = found.sectionsCount || 2;
            window.currentSplitOperationSeq = found.splitSeq || window.currentSplitOperationSeq || 1;
            window.activeSplitInvoice._splitDraftQtys = JSON.parse(JSON.stringify(found.qtys || {}));
            window._splitSectionMeta = JSON.parse(JSON.stringify(found.meta || {}));
            if (typeof window.renderWorldClassSplitBoard === 'function') {
                window.renderWorldClassSplitBoard();
            }
            if (typeof window.autoFitSplitInputs === 'function') {
                window.autoFitSplitInputs();
            }
        }
        window.renderParkedSplitVouchersBar();
        if (typeof showToast === 'function') {
            showToast(`▶️ تم استرجاع الفاتورة المعلقة #${invoiceNo} بكامل بياناتها وتوزيعاتها`, 'success');
        }
    };

    window.removeParkedSplitInvoiceByNo = function(invoiceNo) {
        const list = window.getParkedSplitVouchers().filter(function(x) { return String(x.invoiceNo) !== String(invoiceNo); });
        localStorage.setItem(PARKED_KEY, JSON.stringify(list));
        window.renderParkedSplitVouchersBar();
    };

    // اختصار Alt+P لتعليق الفاتورة فوراً بالكيبورد
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        if (e.altKey && (e.key === 'p' || e.key === 'P' || e.key === 'ح') && window.activeSplitInvoice) {
            e.preventDefault();
            window.parkCurrentSplitInvoice();
        }
    });

    // 5. رادار منع تكرار نفس الفاتورة أو التقسيم بالخطأ في نفس اليوم (Anti-Duplicate Transaction Guard)
    function buildSplitFingerprint(inv, sectionItems, S) {
        const cust = String(inv.customerName || inv.customer || 'walk-in').trim();
        const grand = +(parseFloat(inv.grandTotal || inv.total || 0)).toFixed(2);
        const secSig = (sectionItems || []).map(function(arr) {
            return (arr || []).map(function(it) { return (it.itemId || it.name) + ':' + (+parseFloat(it.qty || it.quantity || 0).toFixed(3)); }).join(',');
        }).join('|');
        return cust + '||' + grand + '||' + S + '||' + secSig;
    }

    window.checkSplitDuplicateTransactionGuard = function(inv, sectionItems, S) {
        try {
            const fp = buildSplitFingerprint(inv, sectionItems, S);
            const todayStr = new Date().toISOString().slice(0, 10);
            const commits = JSON.parse(localStorage.getItem(RECENT_COMMITS_KEY) || '[]') || [];
            const match = commits.find(function(c) {
                return c.date === todayStr && c.fingerprint === fp && (Date.now() - (c.timestamp || 0)) < 12 * 3600 * 1000;
            });
            return match || null;
        } catch (e) { return null; }
    };

    window.recordSplitCommitForDuplicateGuard = function(inv, sectionItems, S, refVoucherNo) {
        try {
            const fp = buildSplitFingerprint(inv, sectionItems, S);
            const todayStr = new Date().toISOString().slice(0, 10);
            const commits = (JSON.parse(localStorage.getItem(RECENT_COMMITS_KEY) || '[]') || []).slice(0, 40);
            commits.unshift({
                fingerprint: fp,
                voucherNo: refVoucherNo,
                customerName: inv.customerName || 'عميل نقدي',
                date: todayStr,
                timestamp: Date.now()
            });
            localStorage.setItem(RECENT_COMMITS_KEY, JSON.stringify(commits));
        } catch (e) {}
    };

    window.showSplitDuplicateGuardModal = function(dupInfo, autoPrintVouchers) {
        let modal = document.getElementById('split-c8-duplicate-guard-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-c8-duplicate-guard-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:flex; position:fixed; inset:0; background:rgba(2,6,23,0.82); z-index:999980; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
        modal.innerHTML = `
            <div class="modal-card" style="max-width:520px; width:100%; background:#0f172a; border:2.5px solid #f59e0b; border-radius:18px; padding:22px; box-shadow:0 20px 50px rgba(0,0,0,0.65); text-align:center;">
                <div style="font-size:2.4rem; margin-bottom:8px;">🛡️</div>
                <h3 style="margin:0 0 10px; color:#fbbf24; font-size:1.18rem; font-weight:900;">تنبيه تركيز: منع تكرار الحركة بالسهو</h3>
                <p style="margin:0 0 18px; color:#f8fafc; font-size:0.95rem; font-weight:800; line-height:1.65;">
                    تنبيه تركيز: توجد حركة مطابقة تماماً تم حفظها لهذا العميل (${dupInfo.customerName || 'عميل نقدي'}) قبل قليل برقم (<strong style="color:#38bdf8; font-family:monospace;">#${dupInfo.voucherNo}</strong>)، هل أنت متأكد من تكرارها؟
                </p>
                <div style="display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap;">
                    <button type="button" onclick="document.getElementById('split-c8-duplicate-guard-modal').style.display='none';" class="btn-action" style="width:auto; padding:8px 20px; border-radius:10px; background:rgba(16,185,129,0.2); border:2px solid #10b981; color:#6ee7b7; font-weight:900; cursor:pointer;">🛑 لا، إيقاف التكرار</button>
                    <button type="button" onclick="document.getElementById('split-c8-duplicate-guard-modal').style.display='none'; window._bypassSplitDuplicateConfirmOnce=true; submitInvoiceSplit(${autoPrintVouchers ? 'true' : 'false'});" class="btn-action" style="width:auto; padding:8px 18px; border-radius:10px; background:rgba(245,158,11,0.2); border:2px solid #f59e0b; color:#fde68a; font-weight:900; cursor:pointer;">✅ نعم، متأكد من الحفظ</button>
                </div>
            </div>
        `;
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.renderParkedSplitVouchersBar();
    });
    setTimeout(window.renderParkedSplitVouchersBar, 200);
})();
