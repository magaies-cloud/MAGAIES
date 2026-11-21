(function() {
    if (window.__SPLIT_CONSTITUTION7_LOCK__) return;
    window.__SPLIT_CONSTITUTION7_LOCK__ = true;

    const arabicDigitsMap = {
        '٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9',
        '۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'
    };

    // دالة التطبيع الفوري للأرقام (العربية/الفارسية -> الإنجليزية) والفاصلة العشرية (و / ، / , / ٫ -> .) مع دعم الآلة الحاسبة المدمجة (+ - * / ( ))
    window.normalizeSplitNumericString = function(rawVal, allowDecimal, allowMath) {
        if (rawVal === null || rawVal === undefined) return '';
        if (allowMath === undefined) allowMath = true;
        let s = String(rawVal)
            // إزالة الحروف الخفية وحروف التحكم من واتساب وإكسل
            .replace(/[\u200B-\u200F\u202A-\u202E\u2060-\u206F\uFEFF]/g, '')
            // تحويل الأرقام العربية والفارسية إلى إنجليزية
            .replace(/[٠-٩۰-۹]/g, function(d) { return arabicDigitsMap[d] || d; })
            // تحويل رموز الضرب والقسمة إلى قياسية
            .replace(/[×✕]/g, '*')
            .replace(/[÷]/g, '/')
            // تحويل مفاتيح الفاصلة العربية والإنجليزية (و، ،، ,، ٫، ٬) إلى نقطة عشرية قياسية
            .replace(/[و،,٫٬]/g, '.');

        if (!allowDecimal) {
            return allowMath ? s.replace(/[^0-9+\-*/()]/g, '') : s.replace(/[^0-9]/g, '');
        }
        if (allowMath && /[+\-*/()]/.test(s)) {
            return s.replace(/[^0-9.+\-*/()]/g, '');
        }
        // إبقاء الأرقام والنقطة العشرية فقط مع منع تكرار النقطة العشرية
        s = s.replace(/[^0-9.]/g, '');
        const parts = s.split('.');
        if (parts.length > 2) {
            s = parts[0] + '.' + parts.slice(1).join('');
        }
        return s;
    };

    // دالة تنظيف النصوص من الحروف الخفية والمسافات الزائدة
    window.cleanSplitTextValue = function(rawVal, trimEdges) {
        let s = String(rawVal ?? '')
            .replace(/[\u200B-\u200F\u202A-\u202E\u2060-\u206F\uFEFF]/g, '')
            .replace(/[٠-٩۰-۹]/g, function(d) { return arabicDigitsMap[d] || d; })
            .replace(/\s{2,}/g, ' ');
        return trimEdges ? s.trim() : s;
    };

    function isSplitNumericInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        const id = el.id || '';
        return id.indexOf('split-qty-') === 0 || id.indexOf('split-subqty-') === 0 || id === 'split-operation-seq-input';
    }

    // 1. اعتراض الكتابة الفورية في خانات الأرقام والكميات لتحويل الأرقام العربية والفاصلة (و / ، / ,) في نفس اللحظة
    document.addEventListener('input', function(e) {
        const el = e.target;
        if (!el || !el.closest || !el.closest('#split-invoice-view')) return;

        if (isSplitNumericInput(el)) {
            const allowDec = el.id !== 'split-operation-seq-input';
            const cleaned = window.normalizeSplitNumericString(el.value, allowDec);
            if (el.value !== cleaned) {
                const pos = el.selectionStart;
                el.value = cleaned;
                try {
                    if (typeof pos === 'number') el.setSelectionRange(pos, pos);
                } catch (err) {}
            }
        } else if (el.tagName === 'INPUT' && el.type === 'text') {
            const cleaned = window.cleanSplitTextValue(el.value, false);
            if (el.value !== cleaned) {
                el.value = cleaned;
            }
        }
    }, true);

    // 2. التنظيف التلقائي للمسافات الزائدة (Auto-Trim) عند الخروج من الخانة (blur / change)
    document.addEventListener('change', function(e) {
        const el = e.target;
        if (!el || el.tagName !== 'INPUT' || !el.closest || !el.closest('#split-invoice-view')) return;
        if (!isSplitNumericInput(el) && el.type === 'text') {
            const trimmed = window.cleanSplitTextValue(el.value, true);
            if (el.value !== trimmed) {
                el.value = trimmed;
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }, true);

    // 3. اللصق الذكي النظيف (Smart Paste Ctrl+V) من واتساب أو إكسل مع تصفية العملات والفواصل
    document.addEventListener('paste', function(e) {
        const el = e.target;
        if (!el || el.tagName !== 'INPUT' || !el.closest || !el.closest('#split-invoice-view')) return;
        const clip = (e.clipboardData || window.clipboardData);
        if (!clip) return;
        const pastedText = clip.getData('text');
        if (!pastedText) return;

        e.preventDefault();
        if (isSplitNumericInput(el)) {
            if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
            // إزالة فواصل الآلاف إذا كانت متبوعة بـ 3 أرقام مثل 12,500 ثم تطبيع الأرقام والفاصلة العشرية
            let preCleaned = String(pastedText)
                .replace(/[٠-٩۰-۹]/g, function(d) { return arabicDigitsMap[d] || d; })
                .replace(/(\d),(?=\d{3}(\D|$))/g, '$1');
            const allowDec = el.id !== 'split-operation-seq-input';
            const cleanNum = window.normalizeSplitNumericString(preCleaned, allowDec);
            el.value = cleanNum || '0';
            el.dispatchEvent(new Event('input', { bubbles: true }));
            if (typeof showToast === 'function') {
                showToast('📋 تم اللصق الذكي وتصفية الرقم تلقائياً: ' + el.value, 'info');
            }
        } else {
            const cleanTxt = window.cleanSplitTextValue(pastedText, true);
            el.value = cleanTxt;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }, true);

    // 4. منظومة التراجع الفوري المتعدد (Ctrl + Z) + زر التصفير وإعادة الضبط
    window._splitUndoHistoryStack = [];

    window.captureSplitStateObject = function() {
        if (!window.activeSplitInvoice) return null;
        if (typeof window.saveCurrentSplitDraftState === 'function') {
            window.saveCurrentSplitDraftState();
        }
        return {
            invoiceNo: String(window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id || ''),
            sectionsCount: window.splitSectionsCount || 2,
            splitSeq: window.currentSplitOperationSeq || 1,
            unitMode: window.splitUnitDisplayMode || 'both',
            qtys: JSON.parse(JSON.stringify(window.activeSplitInvoice._splitDraftQtys || {})),
            meta: JSON.parse(JSON.stringify(window._splitSectionMeta || {}))
        };
    };

    window.pushSplitUndoSnapshot = function() {
        const snap = window.captureSplitStateObject();
        if (!snap) return;
        const stack = window._splitUndoHistoryStack;
        const last = stack.length ? stack[stack.length - 1] : null;
        if (last && JSON.stringify(last.qtys) === JSON.stringify(snap.qtys) && last.sectionsCount === snap.sectionsCount && JSON.stringify(last.meta) === JSON.stringify(snap.meta)) {
            return;
        }
        stack.push(snap);
        if (stack.length > 30) stack.shift();
    };

    // حفظ لقطة قبل تعديل أي خانة كمية عند التركيز عليها (focusin)
    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (el && el.closest && el.closest('#split-board-step') && el.tagName === 'INPUT') {
            window.pushSplitUndoSnapshot();
        }
    }, true);

    window.undoLastSplitChange = function() {
        if (!window.activeSplitInvoice) return;
        const stack = window._splitUndoHistoryStack || [];
        const currentSnap = window.captureSplitStateObject();
        let targetSnap = null;
        while (stack.length > 0) {
            const candidate = stack.pop();
            if (!currentSnap || candidate.invoiceNo !== currentSnap.invoiceNo) continue;
            if (JSON.stringify(candidate.qtys) !== JSON.stringify(currentSnap.qtys) ||
                candidate.sectionsCount !== currentSnap.sectionsCount ||
                JSON.stringify(candidate.meta) !== JSON.stringify(currentSnap.meta)) {
                targetSnap = candidate;
                break;
            }
        }
        if (!targetSnap) {
            if (typeof showToast === 'function') showToast('ℹ️ لا توجد خطوة سابقة للتراجع عنها', 'info');
            return;
        }
        window.splitSectionsCount = targetSnap.sectionsCount || 2;
        window.currentSplitOperationSeq = targetSnap.splitSeq || 1;
        window.splitUnitDisplayMode = targetSnap.unitMode || 'both';
        window.activeSplitInvoice._splitDraftQtys = JSON.parse(JSON.stringify(targetSnap.qtys));
        window._splitSectionMeta = JSON.parse(JSON.stringify(targetSnap.meta));
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof window.autoFitSplitInputs === 'function') {
            window.autoFitSplitInputs();
        }
        if (typeof showToast === 'function') {
            showToast('↩️ تم التراجع واستعادة الخطوة السابقة بنجاح (Ctrl+Z)', 'success');
        }
    };

    window.resetCurrentSplitBoard = function() {
        if (!window.activeSplitInvoice) return;
        window.pushSplitUndoSnapshot();
        window.splitSectionsCount = 2;
        window._splitSectionMeta = {};
        window.activeSplitInvoice._splitDraftQtys = {};
        (window.activeSplitInvoice.items || []).forEach(function(item, idx) {
            const meta = window.resolveSplitItemUnitMeta(item);
            const rem = meta.deliverableQty;
            let q1 = rem > 1 ? Math.ceil(rem / 2) : +(rem / 2).toFixed(3);
            if (rem === 5) q1 = 3;
            let q2 = Math.max(0, +(rem - q1).toFixed(4));
            window.activeSplitInvoice._splitDraftQtys[idx] = [q1, q2];
        });
        if (typeof window.clearSplitCrashVaultForInvoice === 'function') {
            window.clearSplitCrashVaultForInvoice(window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id);
        }
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof window.autoFitSplitInputs === 'function') {
            window.autoFitSplitInputs();
        }
        if (typeof showToast === 'function') {
            showToast('🧹 تم تصفير وإعادة ضبط توزيع الفاتورة (يمكنك التراجع بـ Ctrl+Z)', 'info');
        }
    };

    // اختصار الكيبورد العالمي (Ctrl+Z) داخل شاشة التقسيم
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        if ((e.ctrlKey || e.metaKey) && (e.key === 'z' || e.key === 'Z' || e.key === 'ئ') && !e.shiftKey) {
            if (window.activeSplitInvoice) {
                e.preventDefault();
                window.undoLastSplitChange();
            }
        }
    });

    // 5. دعم قارئ الباركود السريع (Barcode Scanner Support) لفتح الفاتورة أو التركيز على الصنف تلقائياً
    let barcodeBuffer = '';
    let lastBarcodeKeyTime = 0;
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        const activeTag = document.activeElement ? document.activeElement.tagName : '';
        if (activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT') return;

        const now = performance.now();
        if (now - lastBarcodeKeyTime > 85) {
            barcodeBuffer = '';
        }
        lastBarcodeKeyTime = now;

        if (e.key === 'Enter' && barcodeBuffer.length >= 3) {
            e.preventDefault();
            const scannedCode = window.cleanSplitTextValue(barcodeBuffer, true);
            barcodeBuffer = '';
            if (!window.activeSplitInvoice) {
                const searchInp = document.getElementById('split-invoice-search');
                if (searchInp) {
                    searchInp.value = scannedCode;
                    if (typeof filterSplitInvoices === 'function') filterSplitInvoices();
                    const firstSplitBtn = document.querySelector('#split-invoices-list button[onclick*="prepareInvoiceForSplit"]');
                    if (firstSplitBtn) firstSplitBtn.click();
                }
            } else {
                const items = window.activeSplitInvoice.items || [];
                const foundIdx = items.findIndex(function(it) {
                    return String(it.barcode || it.itemCode || it.id || it.name || '').toLowerCase().indexOf(scannedCode.toLowerCase()) !== -1;
                });
                if (foundIdx !== -1) {
                    const qtyInp = document.getElementById('split-qty-s0-i' + foundIdx);
                    if (qtyInp) {
                        qtyInp.focus();
                        if (qtyInp.select) try { qtyInp.select(); } catch (err) {}
                        if (typeof showToast === 'function') showToast('🔫 تم تحديد الصنف بالباركود: ' + (items[foundIdx].name || scannedCode), 'success');
                    }
                }
            }
        } else if (e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            barcodeBuffer += e.key;
        }
    });

    // 6. تحديث مؤشر الاتصال والمزامنة المطمئن لحظياً
    function updateSplitConnectionIndicator() {
        const badge = document.getElementById('split-c7-conn-badge');
        const dot = document.getElementById('split-c7-conn-dot');
        const txt = document.getElementById('split-c7-conn-text');
        if (!badge || !dot || !txt) return;
        const online = navigator.onLine !== false;
        if (online) {
            badge.style.borderColor = '#10b981';
            badge.style.background = 'rgba(16,185,129,0.14)';
            dot.style.background = '#10b981';
            dot.style.boxShadow = '0 0 8px #10b981';
            txt.textContent = 'متصل ومتزامن';
        } else {
            badge.style.borderColor = '#f59e0b';
            badge.style.background = 'rgba(245,158,11,0.16)';
            dot.style.background = '#f59e0b';
            dot.style.boxShadow = '0 0 8px #f59e0b';
            txt.textContent = 'أوفلاين (محفوظ محلياً)';
        }
    }
    window.addEventListener('online', updateSplitConnectionIndicator);
    window.addEventListener('offline', updateSplitConnectionIndicator);
    document.addEventListener('DOMContentLoaded', updateSplitConnectionIndicator);
    setTimeout(updateSplitConnectionIndicator, 150);
})();
