(function() {
    if (window.__SPLIT_CONSTITUTION3_LOCK__) return;
    window.__SPLIT_CONSTITUTION3_LOCK__ = true;

    // 1. النبضة الاهتزازية التفاعلية لحظة الضغط على الأزرار (Tactile Haptic & Visual Pulse)
    document.addEventListener('pointerdown', function(e) {
        const btn = e.target && e.target.closest && e.target.closest('#split-invoice-view button, #post-split-success-modal button, #split-genealogy-tree-modal button');
        if (!btn || btn.disabled) return;
        if (navigator.vibrate) {
            try { navigator.vibrate(10); } catch (err) {}
        }
    }, { passive: true });

    // 2. الدستور الموحد للرسائل الطائرة + هز الخانة المعيبة وانتقال الكيرسر إليها
    window.triggerSplitInputErrorShake = function(inputEl, message) {
        if (typeof showToast === 'function' && message) {
            showToast(message, 'error');
        }
        if (inputEl) {
            inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            inputEl.classList.remove('c3-input-shake');
            void inputEl.offsetWidth;
            inputEl.classList.add('c3-input-shake');
            inputEl.focus();
            if (inputEl.select) try { inputEl.select(); } catch (e) {}
            setTimeout(function() {
                inputEl.classList.remove('c3-input-shake');
            }, 600);
        }
    };

    // 3. التأكيد المدمج السريع لزر الإلغاء لمنع الخروج بالخطأ
    window.confirmAndCancelCurrentSplit = function(btnEl) {
        if (!window.activeSplitInvoice) {
            if (typeof cancelCurrentSplit === 'function') cancelCurrentSplit();
            return;
        }
        if (btnEl && btnEl.dataset.confirmArmed !== '1') {
            btnEl.dataset.confirmArmed = '1';
            const origHtml = btnEl.innerHTML;
            btnEl.innerHTML = '⚠️ اضغط لتأكيد الإلغاء';
            if (typeof showToast === 'function') {
                showToast('⚠️ اضغط مرة ثانية على زر الإلغاء للعودة، أو استمر في التقسيم', 'warning');
            }
            setTimeout(function() {
                if (btnEl) {
                    btnEl.dataset.confirmArmed = '0';
                    btnEl.innerHTML = origHtml;
                }
            }, 3000);
            return;
        }
        if (btnEl) btnEl.dataset.confirmArmed = '0';
        if (typeof cancelCurrentSplit === 'function') cancelCurrentSplit();
    };

    // 4. مشاركة تفاصيل التقسيم عبر واتساب بضغطة واحدة (Formatted WhatsApp Split Summary + الوزن بالطن والسيخ والشيلة)
    window.shareCurrentSplitViaWhatsApp = function() {
        const inv = window.activeSplitInvoice || (window._lastCreatedSplitVouchers && window._lastCreatedSplitVouchers.list && window._lastCreatedSplitVouchers.list[0]);
        if (!inv) {
            if (typeof window.sendSalesFc3WhatsAppVisualReceiptCard === 'function') {
                window.sendSalesFc3WhatsAppVisualReceiptCard();
                return;
            }
            if (typeof showToast === 'function') showToast('⚠️ اختر فاتورة للتقسيم أولاً', 'warning');
            return;
        }
        if (typeof window.saveCurrentSplitDraftState === 'function') {
            window.saveCurrentSplitDraftState();
        }
        const rootNo = window.getRootInvoiceNo(inv);
        const splitSeq = window.currentSplitOperationSeq || 1;
        const S = window.splitSectionsCount || 2;
        const currency = inv.currency || 'SDG';
        const grandTotal = parseFloat(inv.grandTotal || inv.total || 0) || 0;
        const paidAmt = parseFloat(inv.paidAmount || inv.cashAmount || grandTotal) || 0;
        const remAmt = Math.max(0, grandTotal - paidAmt);
        const lines = [
            `✂️ *بطاقة إيصال وتقسيم فاتورة مبيعات #${rootNo} — السيطرة السجانة ERP*`,
            `👤 العميل: ${inv.customerName || 'عميل نقدي'}`,
            `📅 التاريخ: ${inv.date || new Date().toISOString().slice(0, 10)}`,
            `💵 الإجمالي الكلي: ${grandTotal.toLocaleString('en-US')} ${currency} | المدفوع: ${paidAmt.toLocaleString('en-US')} | المتبقي: ${remAmt.toLocaleString('en-US')}`,
            `🏗️ معيار حديد السجانة: الطن العددي = 135 سيخة | الطن الوزني = 140 سيخة | الشيلة = 15 سيخة`,
            `──────────────────`
        ];
        for (let s = 0; s < S; s++) {
            const secNo = `${rootNo}/${splitSeq}/${s + 1}`;
            const meta = (window._splitSectionMeta && window._splitSectionMeta[s]) || {};
            const itemParts = [];
            let secTotal = 0;
            (inv.items || []).forEach((it, idx) => {
                const m = window.resolveSplitItemUnitMeta(it);
                const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
                const q = inp ? (parseFloat(inp.value) || 0) : ((inv._splitDraftQtys && inv._splitDraftQtys[idx] && inv._splitDraftQtys[idx][s]) || 0);
                if (q > 0) {
                    itemParts.push(`• ${m.name}: ${q.toLocaleString('en-US')} ${m.primaryUnit}`);
                    secTotal += q * m.price;
                }
            });
            lines.push(`📄 *القسم (${s + 1}) رقم #${secNo}*`);
            if (meta.receiverName) lines.push(`🤝 المستلم: ${meta.receiverName}`);
            if (meta.truckPlate) lines.push(`🚛 المركبة: ${meta.truckPlate}`);
            if (itemParts.length) lines.push(itemParts.join('\n'));
            lines.push(`💰 القيمة: ${secTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`);
            lines.push(`──────────────────`);
        }
        const text = lines.join('\n');
        const waUrl = 'https://wa.me/?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank', 'noopener');
        if (typeof showToast === 'function') {
            showToast('✅ تم تجهيز رسالة التقسيم للواتساب بفواصل الآلاف', 'success');
        }
    };

    // 5. اختصار F10 لاعتماد التقسيم فوراً مع قفل منع التكرار
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        if (e.key === 'F10' && window.activeSplitInvoice) {
            e.preventDefault();
            if (typeof submitInvoiceSplit === 'function') submitInvoiceSplit(false);
        }
    });
})();
