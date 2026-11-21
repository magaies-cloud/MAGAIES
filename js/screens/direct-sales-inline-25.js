(function() {
    if (window.__SPLIT_CONSTITUTION2_LOCK__) return;
    window.__SPLIT_CONSTITUTION2_LOCK__ = true;

    // 1. البحت العربي الذكي المتسامح وتحويل الأرقام الهندية (٠-٩ -> 0-9)
    const arabicDigitsMap = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'};
    window.normalizeSplitDigits = function(str) {
        return String(str ?? '').replace(/[٠-٩۰-۹]/g, d => arabicDigitsMap[d] || d);
    };
    window.normalizeArabicForSplitSearch = function(text) {
        return window.normalizeSplitDigits(text)
            .toLowerCase()
            .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
            .replace(/[أإآٱ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/[ىئ]/g, 'ي')
            .replace(/ؤ/g, 'و')
            .replace(/\s+/g, ' ')
            .trim();
    };

    // 2. التظليل التلقائي للأرقام عند دخول الكيرسر (Auto-Select on Focus)
    document.addEventListener('focusin', function(e) {
        const t = e.target;
        if (!t || !t.closest || !t.closest('#split-invoice-view')) return;
        if (t.tagName === 'INPUT' && (t.type === 'number' || t.id.indexOf('split-qty-') === 0 || t.id.indexOf('split-subqty-') === 0 || t.id === 'split-operation-seq-input')) {
            setTimeout(function() {
                try { t.select(); } catch (err) {}
            }, 10);
        }
    }, true);

    // تحويل الأرقام الهندية لحظياً داخل خانات البحث والكميات
    document.addEventListener('input', function(e) {
        const t = e.target;
        if (!t || !t.closest || !t.closest('#split-invoice-view')) return;
        if (t.tagName === 'INPUT' && /[٠-٩۰-۹]/.test(t.value)) {
            const norm = window.normalizeSplitDigits(t.value);
            if (norm !== t.value) t.value = norm;
        }
    }, true);

    // 3. انسيابية الكيرسر بلوحة المفاتيح (Enter / الأسهم الأربع / F9 / Ctrl+Enter / Escape)
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;

        // Enter inside #split-invoice-search -> open first visible invoice immediately
        if (e.target && e.target.id === 'split-invoice-search' && e.key === 'Enter') {
            e.preventDefault();
            const firstSplitBtn = document.querySelector('#split-invoices-body button[onclick^="startInvoiceSplit"]');
            if (firstSplitBtn) firstSplitBtn.click();
            return;
        }

        // F9 inside #split-board-step -> distribute equally
        if (e.key === 'F9' && window.activeSplitInvoice) {
            e.preventDefault();
            if (typeof window.distributeSplitEqually === 'function') window.distributeSplitEqually();
            return;
        }

        // Ctrl+Enter inside #split-board-step -> submit split immediately
        if (e.ctrlKey && e.key === 'Enter' && window.activeSplitInvoice) {
            e.preventDefault();
            if (typeof submitInvoiceSplit === 'function') submitInvoiceSplit(false);
            return;
        }

        // Escape inside child modals -> close child modal first
        if (e.key === 'Escape') {
            const genModal = document.getElementById('split-genealogy-tree-modal');
            if (genModal && genModal.style.display === 'flex') {
                e.preventDefault();
                genModal.style.display = 'none';
                return;
            }
            const postModal = document.getElementById('post-split-success-modal');
            if (postModal && postModal.style.display === 'flex') {
                e.preventDefault();
                postModal.style.display = 'none';
                return;
            }
        }

        // Arrow & Enter navigation across split grid inputs
        const t = e.target;
        if (!t || !t.closest || !t.closest('#split-board-step')) return;
        if (t.tagName !== 'INPUT') return;

        const allInputs = Array.from(document.querySelectorAll('#split-board-step input:not([type="hidden"])')).filter(el => el.offsetParent !== null && !el.disabled);
        const curIdx = allInputs.indexOf(t);
        if (curIdx === -1) return;

        if (e.key === 'Enter') {
            e.preventDefault();
            const nextEl = allInputs[curIdx + 1] || document.querySelector('#split-board-step button[onclick*="submitInvoiceSplit(false)"]');
            if (nextEl) {
                nextEl.focus();
                if (nextEl.select) try { nextEl.select(); } catch (err) {}
            }
        } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const m = t.id.match(/^(split-(?:sub)?qty-s(\d+)-i)(\d+)$/);
            if (m) {
                e.preventDefault();
                const prefix = m[1];
                const nextRow = parseInt(m[3], 10) + (e.key === 'ArrowDown' ? 1 : -1);
                const targetInp = document.getElementById(prefix + nextRow);
                if (targetInp && targetInp.offsetParent !== null) {
                    targetInp.focus();
                    try { targetInp.select(); } catch (err) {}
                }
            }
        }
    });

    // 4. حماية المدخلات ضد الإغلاق الخاطئ (Unsaved Split Guard)
    window.addEventListener('beforeunload', function(e) {
        const boardStep = document.getElementById('split-board-step');
        if (window.activeSplitInvoice && boardStep && boardStep.offsetParent !== null) {
            if (typeof window.persistActiveSplitDraftToStorage === 'function') {
                window.persistActiveSplitDraftToStorage();
            }
        }
    });
})();
