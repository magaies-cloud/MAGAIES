    (function initDesktopPurchaseMirrorSync() {
        let isSyncing = false;
        async function syncPurchaseWithMariaDB() {
            if (isSyncing) return;
            const purInput = document.getElementById('hdr-inv-no');
            const purSpan = document.getElementById('inv-number');
            const isCreatingNew = !window.currentViewedInvoiceNo || window.currentViewedInvoiceNo === '---';
            if (!navigator.onLine) {
                if (isCreatingNew && window.MagaiesOffline && typeof window.MagaiesOffline.peekNextSequentialNumber === 'function' && purInput && purSpan) {
                    if (!purInput.value || purInput.value === '---') {
                        const offNext = window.MagaiesOffline.peekNextSequentialNumber('purchase');
                        purInput.value = offNext;
                        purSpan.textContent = offNext;
                        if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
                    }
                }
                return;
            }
            isSyncing = true;
            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 6000) : null;
            try {
                if (window.MagaiesOffline && typeof window.MagaiesOffline.flushQueue === 'function') {
                    await window.MagaiesOffline.flushQueue();
                }
                const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now(), {
                    signal: controller ? controller.signal : undefined
                });
                if (timeoutId) clearTimeout(timeoutId);
                const data = await res.json();
                if (data && data.success && Array.isArray(data.purchases)) {
                    let localPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
                    if (!Array.isArray(localPurchases)) localPurchases = [];
                    let localMap = new Map();
                    localPurchases.forEach(inv => {
                        const no = inv && (inv.invoiceNo || inv.id);
                        if (no) localMap.set(String(no), inv);
                    });

                    let hasChanges = false;
                    data.purchases.forEach(serverInv => {
                        if (!serverInv || typeof serverInv !== 'object') return;
                        const pNo = String(serverInv.invoiceNo || serverInv.id || '').trim();
                        if (!pNo) return;
                        if (!localMap.has(pNo)) {
                            localPurchases.push(serverInv);
                            localMap.set(pNo, serverInv);
                            try { localStorage.setItem('erp_pur_' + pNo, JSON.stringify(serverInv)); } catch(e) {}
                            hasChanges = true;
                        } else {
                            const existing = localMap.get(pNo);
                            const merged = Object.assign({}, serverInv, existing);
                            if ((!merged.items || !merged.items.length) && Array.isArray(serverInv.items) && serverInv.items.length) {
                                merged.items = serverInv.items;
                                hasChanges = true;
                            }
                            if (existing.isOffline) {
                                merged.isOffline = false;
                                hasChanges = true;
                            }
                            localMap.set(pNo, merged);
                            try { localStorage.setItem('erp_pur_' + pNo, JSON.stringify(merged)); } catch(e) {}
                        }
                    });

                    if (hasChanges) {
                        const finalArr = Array.from(localMap.values());
                        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(finalArr));
                        localStorage.setItem('erp_purchases_v1', JSON.stringify(finalArr));
                        localStorage.setItem('magaies_local_purchase_invoices_v1', JSON.stringify(finalArr));
                        window._cachedPurchaseInvoicesRaw = null;
                        window._cachedPurchaseList = null;
                        if (typeof loadHubTransactions === 'function' && typeof currentPurchaseView !== 'undefined' && currentPurchaseView === 'hub') {
                            loadHubTransactions();
                        }
                    }

                    if (data.nextPurchaseNo) {
                        window._mirrorNextPurchaseNo = data.nextPurchaseNo;
                    }
                    const bestNextPurNo = (typeof peekNextInvoiceNumber === 'function') ? peekNextInvoiceNumber() : (data.nextPurchaseNo || '');
                    if (isCreatingNew && bestNextPurNo && purInput && purSpan) {
                        if (purInput.value !== bestNextPurNo) {
                            purInput.value = bestNextPurNo;
                            purSpan.textContent = bestNextPurNo;
                            if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
                        }
                    }
                }
            } catch(e) {
                console.warn('Desktop purchase mirror sync check:', e);
            } finally {
                isSyncing = false;
            }
        }
        window.initDesktopPurchaseMirrorSync = syncPurchaseWithMariaDB;

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(syncPurchaseWithMariaDB, 400);
        });
                window.addEventListener('online', syncPurchaseWithMariaDB);
    })();

    // ═══════════════════════════════════════════════════════════════════════════════
    // 🏛️ الدستور الحاكم الشامل لشاشة المشتريات (purchase.php?action=direct)
    // 1. مرشد الشاشة المعزول والمفصل من الألف إلى الياء (خانات + أزرار + مسار التسميع)
    // 2. انسيابية المؤشر 60fps والتنقل بزر Enter واختصارات الكيبورد (Ctrl+S / F2)
    // 3. منع النقرات المزدوجة + طابور عدم الاتصال الذكي + التسميع اللحظي عبر BroadcastChannel
    // ═══════════════════════════════════════════════════════════════════════════════
    (function initPurchaseConstitutionEngine() {
        const headerOrder = [
            '#hdr-date',
            '#hdr-customer-search',
            '#hdr-currency',
            '#hdr-exchange-rate',
            '#hdr-warehouse',
            '#hdr-cost-center',
            '#hdr-description',
            '#quick-item-search'
        ];
        const addSuppOrder = ['#new-cust-name', '#new-cust-phone', '#new-cust-credit'];

        const addCustModalEl = document.getElementById('add-customer-modal');
        if (addCustModalEl && typeof MutationObserver !== 'undefined') {
            new MutationObserver(() => {
                if (addCustModalEl.classList.contains('active') || addCustModalEl.style.display === 'flex' || addCustModalEl.style.display === 'block') {
                    setTimeout(() => {
                        const nm = document.getElementById('new-cust-name');
                        if (nm) { try { nm.focus(); } catch (_e) {} }
                    }, 50);
                }
            }).observe(addCustModalEl, { attributes: true, attributeFilter: ['class', 'style'] });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = [
                    'add-customer-modal',
                    'grid-settings-modal',
                    'ocr-review-modal',
                    'barcode-scanner-modal',
                    'success-modal-overlay'
                ];
                for (const mId of openModals) {
                    const mEl = document.getElementById(mId);
                    if (mEl && (mEl.classList.contains('active') || (mEl.style.display && mEl.style.display !== 'none'))) {
                        e.preventDefault();
                        if (mId === 'barcode-scanner-modal' && typeof stopCameraScanner === 'function') {
                            stopCameraScanner();
                        } else if (typeof closeModal === 'function' && mEl.classList.contains('active')) {
                            closeModal(mId);
                        } else {
                            mEl.style.display = 'none';
                            mEl.classList.remove('active');
                        }
                        return;
                    }
                }
                ['customer-dropdown-list', 'quick-item-dropdown-list', 'share-popup-menu', 'auto-send-purchase-dropdown-menu'].forEach(ddId => {
                    const dd = document.getElementById(ddId);
                    if (dd && dd.style.display !== 'none') dd.style.display = 'none';
                });
                return;
            }

            if (addCustModalEl && (addCustModalEl.classList.contains('active') || addCustModalEl.style.display === 'flex')) {
                if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                    const t = e.target;
                    const idx = addSuppOrder.findIndex(sel => t && t.matches(sel));
                    if (idx !== -1) {
                        e.preventDefault();
                        if (idx < addSuppOrder.length - 1) {
                            const nxt = document.querySelector(addSuppOrder[idx + 1]);
                            if (nxt) { nxt.focus(); if (typeof nxt.select === 'function') nxt.select(); }
                        } else if (typeof addNewCustomer === 'function') {
                            addNewCustomer();
                        }
                        return;
                    }
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'س')) {
                    e.preventDefault();
                    if (typeof addNewCustomer === 'function') addNewCustomer();
                    return;
                }
            }

            const editorView = document.getElementById('purchase-editor-view');
            const isEditorVisible = editorView && editorView.style.display !== 'none';
            if (!isEditorVisible) return;

            // F10 or Ctrl+S => Save & Approve Invoice (with smooth mandatory field focus)
            if (e.key === 'F10' || ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'س'))) {
                e.preventDefault();
                if (typeof window.smartValidateAndSubmitPurchase === 'function') {
                    window.smartValidateAndSubmitPurchase();
                } else if (typeof submitInvoice === 'function') {
                    submitInvoice();
                }
                return;
            }
            // F2 => Focus Quick Item Search
            if (e.key === 'F2') {
                e.preventDefault();
                const qSearch = document.getElementById('quick-item-search');
                if (qSearch) { qSearch.focus(); qSearch.select(); }
                return;
            }
            // F4 => Quick Add Supplier Modal
            if (e.key === 'F4') {
                e.preventDefault();
                if (typeof openModal === 'function') openModal('add-customer-modal');
                return;
            }
            // F8 => Print Current Ezn (No Prices)
            if (e.key === 'F8') {
                e.preventDefault();
                if (typeof window.printPurchaseEznNoPrices === 'function') window.printPurchaseEznNoPrices();
                else if (typeof printCurrentInvoice === 'function') printCurrentInvoice();
                return;
            }
            // Enter Navigation across Header Fields
            if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                const t = e.target;
                if (!t) return;
                const curIdx = headerOrder.findIndex(sel => t.matches(sel));
                if (curIdx !== -1 && curIdx < headerOrder.length - 1) {
                    // Allow customer dropdown selection if open, then move next
                    setTimeout(() => {
                        for (let i = curIdx + 1; i < headerOrder.length; i++) {
                            const nextEl = document.querySelector(headerOrder[i]);
                            if (nextEl && !nextEl.disabled && nextEl.offsetParent !== null) {
                                nextEl.focus();
                                if (typeof nextEl.select === 'function' && nextEl.tagName === 'INPUT') nextEl.select();
                                break;
                            }
                        }
                    }, 40);
                }
            }
        });

        // 🎯 Smooth Mandatory Field Auto-Focus & Visual Save Pulse (Order 4 & Order 8)
        window.smartValidateAndSubmitPurchase = function() {
            const custId = (document.getElementById('hdr-customer')?.value || '').trim();
            const custSearch = document.getElementById('hdr-customer-search');
            if (!custId && custSearch && !(custSearch.value || '').trim()) {
                custSearch.focus();
                custSearch.classList.add('input-error-shake');
                setTimeout(() => custSearch.classList.remove('input-error-shake'), 600);
                if (typeof showCustomerDropdown === 'function') showCustomerDropdown();
                if (typeof showToast === 'function') showToast('👤 يرجى اختيار المورد أولاً', 'warning', 2200);
                return;
            }
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
            if (activeRows.length === 0) {
                const firstItemInp = document.querySelector('#pos-grid input[id^="item-search-"]') || document.getElementById('quick-item-search');
                if (firstItemInp) {
                    firstItemInp.focus();
                    if (typeof firstItemInp.select === 'function') firstItemInp.select();
                }
                if (typeof showToast === 'function') showToast('📦 يرجى اختيار صنف واحد على الأقل وتحديد الكمية', 'warning', 2200);
                return;
            }
            const saveBtn = document.getElementById('btn-submit-purchase-invoice');
            if (saveBtn) {
                saveBtn.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
                saveBtn.style.transform = 'scale(0.97)';
                saveBtn.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.55), 0 0 25px rgba(16, 185, 129, 0.8)';
                setTimeout(() => {
                    saveBtn.style.transform = '';
                    saveBtn.style.boxShadow = '';
                }, 500);
            }
            if (typeof submitInvoice === 'function') submitInvoice();
        };

        // 🔢 Auto-Select ANY Numeric Field on Focus (Order 4: التظليل التلقائي للأرقام)
        document.addEventListener('focusin', function(e) {
            const t = e.target;
            if (!t || t.tagName !== 'INPUT') return;
            const isNumericField = t.type === 'number' ||
                t.getAttribute('inputmode') === 'decimal' ||
                t.hasAttribute('data-qty-input') ||
                t.hasAttribute('data-price-input') ||
                t.classList.contains('num-input') ||
                t.classList.contains('tot-input') ||
                t.id === 'pay-cash-amount' ||
                t.id === 'new-cust-credit' ||
                t.id === 'hdr-exchange-rate' ||
                t.id.startsWith('t-') ||
                t.id.startsWith('pay-');
            if (isNumericField && !t.readOnly) {
                setTimeout(() => { try { t.select(); } catch(_e){} }, 10);
            }
        });

        // 🛡️ Double-Click Prevention across Purchase Action Buttons
        document.addEventListener('click', function(e) {
            const btn = e.target && e.target.closest('button[onclick*="submitInvoice"], button[onclick*="executeDeleteTransaction"], button[onclick*="postInvoice"]');
            if (!btn) return;
            if (btn.dataset.purBusy === '1') {
                e.stopImmediatePropagation();
                e.preventDefault();
                return;
            }
            btn.dataset.purBusy = '1';
            setTimeout(() => { delete btn.dataset.purBusy; }, 1400);
        }, true);

        // 🌐 Weak Internet (2G) Micro-Packet Auto-Flush & Sub-Second Mobile ↔ Laptop Live Sync (Order 2 & 3)
        let _isFlushingPurOffline = false;
        async function flushPurchaseOfflineQueue() {
            if (!navigator.onLine || _isFlushingPurOffline) return;
            _isFlushingPurOffline = true;
            try {
                if (window.MagaiesOffline && typeof window.MagaiesOffline.flushQueue === 'function') {
                    await window.MagaiesOffline.flushQueue();
                }
                let pq = JSON.parse(localStorage.getItem('erp_purchase_offline_queue_v1') || '[]');
                if (Array.isArray(pq) && pq.length > 0) {
                    const remaining = [];
                    for (const item of pq) {
                        if (!item || !item.payload) continue;
                        try {
                            const action = item.type === 'purchase_split' ? 'save_purchase_split' : 'save_purchase_invoice';
                            const res = await fetch('api/mirror_sync.php?action=' + action, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                keepalive: true,
                                body: JSON.stringify(item.payload)
                            }).then(r => r.json());
                            if (!res || !res.success) remaining.push(item);
                        } catch (_ef) {
                            remaining.push(item);
                        }
                    }
                    localStorage.setItem('erp_purchase_offline_queue_v1', JSON.stringify(remaining));
                    if (remaining.length === 0 && typeof showToast === 'function') {
                        showToast('☁️ تم مزامنة الفواتير المحفوظة أوفلاين مع السيرفر تلقائياً', 'success', 2000);
                    }
                }
                if (typeof window.initDesktopPurchaseMirrorSync === 'function') {
                    window.initDesktopPurchaseMirrorSync();
                }
            } catch (_eFlush) {} finally {
                _isFlushingPurOffline = false;
            }
        }
        window.addEventListener('online', flushPurchaseOfflineQueue);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) flushPurchaseOfflineQueue(); });
        if (navigator.connection && typeof navigator.connection.addEventListener === 'function') {
            navigator.connection.addEventListener('change', flushPurchaseOfflineQueue);
        }
        window.addEventListener('storage', function(ev) {
            if (!ev || !ev.key) return;
            if (ev.key.includes('erp_purchase') || ev.key.includes('erp_suppliers') || ev.key.includes('erp_items')) {
                window._cachedPurchaseInvoicesRaw = null;
                window._cachedPurchaseList = null;
                if (typeof loadHubTransactions === 'function' && typeof currentPurchaseView !== 'undefined' && currentPurchaseView === 'hub') {
                    loadHubTransactions();
                }
            }
        });
        setTimeout(flushPurchaseOfflineQueue, 2000);

        // ⚖️ Landed Cost Allocation Mode Toggle (Value vs Quantity)
        window.toggleLandedCostAllocationMode = function(forceMode) {
            const inp = document.getElementById('hdr-landed-cost-mode');
            const lbl = document.getElementById('lbl-landed-cost-mode');
            const btn = document.getElementById('btn-landed-cost-mode');
            if (!inp || !lbl) return;
            const nextMode = forceMode || (inp.value === 'qty' ? 'value' : 'qty');
            inp.value = nextMode;
            if (nextMode === 'qty') {
                lbl.textContent = 'بالكمية 📦';
                lbl.style.color = '#34d399';
                if (btn) {
                    btn.style.background = 'rgba(16, 185, 129, 0.14)';
                    btn.style.borderColor = 'rgba(16, 185, 129, 0.45)';
                    btn.style.color = '#a7f3d0';
                }
                if (!forceMode && typeof showToast === 'function') {
                    showToast('📦 تم ضبط توزيع مصاريف الترحيل والعمالة على الأصناف حسب (الكمية)', 'info', 2800);
                }
            } else {
                lbl.textContent = 'بالقيمة 💰';
                lbl.style.color = '#fde68a';
                if (btn) {
                    btn.style.background = 'rgba(56, 189, 248, 0.12)';
                    btn.style.borderColor = 'rgba(56, 189, 248, 0.35)';
                    btn.style.color = '#38bdf8';
                }
                if (!forceMode && typeof showToast === 'function') {
                    showToast('💰 تم ضبط توزيع مصاريف الترحيل والعمالة على الأصناف حسب (القيمة المالية)', 'info', 2800);
                }
            }
        };

        // 🛡️ Live 3-Way Matching Engine (Invoice vs GRN / Split Deliveries)
        window.updatePurchaseThreeWayMatchBadge = function() {
            const badge = document.getElementById('purchase-3way-match-badge');
            const iconEl = document.getElementById('purchase-3way-match-icon');
            const textEl = document.getElementById('purchase-3way-match-text');
            if (!badge || !textEl) return;

            const invNo = (document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
            const invTotalQty = activeRows.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);

            let linkedGrns = [];
            ['erp_grn_v1', 'erp_purchase_grn_v1', 'erp_split_deliveries_v1', 'erp_warehouse_receipts_v1'].forEach(k => {
                try {
                    const arr = JSON.parse(localStorage.getItem(k) || '[]');
                    if (Array.isArray(arr) && invNo && invNo !== '---') {
                        arr.forEach(g => {
                            if (g && !g.deleted && g.status !== 'cancelled' && (
                                String(g.invoiceNo) === invNo ||
                                String(g.refInvoice) === invNo ||
                                String(g.parentInvoiceNo) === invNo ||
                                String(g.purchaseInvoiceNo) === invNo
                            )) {
                                linkedGrns.push(g);
                            }
                        });
                    }
                } catch (_e) {}
            });

            if (linkedGrns.length > 0) {
                let grnTotalQty = 0;
                linkedGrns.forEach(g => {
                    if (Array.isArray(g.items)) {
                        g.items.forEach(it => { grnTotalQty += (parseFloat(it.receivedQty ?? it.qty ?? it.quantity ?? 0) || 0); });
                    } else {
                        grnTotalQty += (parseFloat(g.totalQty ?? g.qty ?? 0) || 0);
                    }
                });
                const diff = Math.abs(invTotalQty - grnTotalQty);
                if (diff < 0.001 || grnTotalQty === 0) {
                    badge.style.background = 'rgba(16, 185, 129, 0.16)';
                    badge.style.borderColor = 'rgba(16, 185, 129, 0.45)';
                    badge.style.color = '#34d399';
                    if (iconEl) iconEl.textContent = '✅';
                    textEl.textContent = `المطابقة الثلاثية: مطابقة مع إذن الاستلام (${linkedGrns.length}) بنسبة 100%`;
                } else {
                    badge.style.background = 'rgba(245, 158, 11, 0.18)';
                    badge.style.borderColor = 'rgba(245, 158, 11, 0.5)';
                    badge.style.color = '#fbbf24';
                    if (iconEl) iconEl.textContent = '⚠️';
                    textEl.textContent = `المطابقة الثلاثية: الفاتورة (${invTotalQty}) ≠ المستلم مخزنياً (${grnTotalQty})`;
                }
            } else {
                badge.style.background = 'rgba(16, 185, 129, 0.12)';
                badge.style.borderColor = 'rgba(16, 185, 129, 0.35)';
                badge.style.color = '#34d399';
                if (iconEl) iconEl.textContent = '🛡️';
                textEl.textContent = activeRows.length > 0
                    ? `المطابقة الثلاثية: توريد مخزني مباشر (${activeRows.length} صنف / إجمالي كمية ${invTotalQty} متطابق 100%)`
                    : 'المطابقة الثلاثية: توريد مخزني مباشر (متطابق 100%)';
            }
        };

        window.inspectThreeWayMatchDetails = function() {
            window.updatePurchaseThreeWayMatchBadge();
            const txt = document.getElementById('purchase-3way-match-text')?.textContent || '';
            if (typeof showToast === 'function') {
                showToast(`🛡️ ${txt}`, 'info', 4000);
            }
        };

        document.addEventListener('input', function(e) {
            if (e.target && e.target.closest('#purchase-editor-view')) {
                window.updatePurchaseThreeWayMatchBadge();
            }
        });

        // 📈 Smart Purchase Price Variance Alert (>10% above lastPurchaseCost)
        document.addEventListener('change', function(e) {
            const inp = e.target;
            if (!inp || !inp.closest('#pos-grid td[data-col-id="price"]')) return;
            try {
                const tr = inp.closest('tr');
                if (!tr) return;
                const rowIdx = Array.from(tr.parentElement.children).indexOf(tr);
                const rowObj = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows[rowIdx] : null;
                if (!rowObj || !rowObj.itemId) return;
                const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                const itemObj = itemsList.find(i => String(i.id) === String(rowObj.itemId));
                if (!itemObj) return;
                const prevCost = parseFloat(itemObj.lastPurchaseCost || itemObj.costPrice || itemObj.cost || 0) || 0;
                const newPrice = parseFloat(String(inp.value).replace(/,/g, '')) || 0;
                if (prevCost > 0 && newPrice > prevCost * 1.10) {
                    const diffPct = Math.round(((newPrice - prevCost) / prevCost) * 100);
                    if (typeof showToast === 'function') {
                        showToast(`⚠️ تنبيه انحراف سعر: سعر شراء (${itemObj.name}) زاد بنسبة +${diffPct}% عن آخر تكلفة (${prevCost.toLocaleString()})`, 'warning', 4200);
                    }
                }
            } catch (_eVar) {}
        });

        // 🔄 One-Click Proforma-to-Direct Purchase Invoice Converter
        window.convertProformaToDirectInvoice = function() {
            if (typeof currentPurchaseType !== 'undefined') currentPurchaseType = 'direct';
            window.currentViewedInvoiceNo = null;
            const nextNo = (typeof peekNextInvoiceNumber === 'function') ? peekNextInvoiceNumber('direct') : (window._mirrorNextPurchaseNo || 'P2600001');
            const invSpan = document.getElementById('inv-number');
            const invInput = document.getElementById('hdr-inv-no');
            const invLbl = document.getElementById('hdr-inv-label');
            if (invSpan) invSpan.textContent = nextNo;
            if (invInput) invInput.value = nextNo;
            if (invLbl) invLbl.textContent = 'الرقم:';
            const payPanel = document.getElementById('payment-panel');
            if (payPanel) payPanel.style.display = 'block';
            if (typeof showToast === 'function') {
                showToast(`✅ تم تحويل العرض المبدئي إلى فاتورة مشتريات فعلية رقم #${nextNo} — جاهزة للحفظ والاعتماد`, 'success', 3800);
            }
        };

        window.convertHubProformaToDirect = function(invNo) {
            if (typeof editHubTransaction === 'function') {
                editHubTransaction(invNo);
                setTimeout(() => {
                    if (typeof window.convertProformaToDirectInvoice === 'function') {
                        window.convertProformaToDirectInvoice();
                    }
                }, 120);
            }
        };

        setTimeout(window.updatePurchaseThreeWayMatchBadge, 900);

        // 🛡️ Inline Non-Blocking Delete / Clear Confirmation (Zero native confirm/alert)
        window.handleSafePurchaseDeleteOrClear = function(btn) {
            if (window.currentViewedInvoiceNo && typeof confirmDeleteTransaction === 'function') {
                confirmDeleteTransaction();
                return;
            }
            const lbl = btn ? btn.querySelector('.del-btn-label') : null;
            if (btn && btn.dataset.confirmClear !== '1') {
                btn.dataset.confirmClear = '1';
                if (lbl) lbl.textContent = 'تأكيد المسح؟';
                if (typeof showToast === 'function') {
                    showToast('⚠️ اضغط مرة أخرى خلال 3 ثوانٍ لتأكيد تفريغ الفاتورة الحالية', 'warning', 2800);
                }
                setTimeout(() => {
                    if (btn) delete btn.dataset.confirmClear;
                    if (lbl) lbl.textContent = 'حذف';
                }, 3200);
                return;
            }
            if (btn) delete btn.dataset.confirmClear;
            if (lbl) lbl.textContent = 'حذف';
            if (typeof onSuccessNewInvoice === 'function') {
                onSuccessNewInvoice(false);
            }
            if (typeof showToast === 'function') {
                showToast('🧹 تم تفريغ حقول الفاتورة وتجهيز فاتورة جديدة', 'info', 2200);
            }
        };

        // 🛡️ Unsaved Invoice Guard (beforeunload protection when active rows exist)
        window.addEventListener('beforeunload', function(e) {
            try {
                const editorEl = document.getElementById('purchase-editor-view');
                if (!editorEl || editorEl.style.display === 'none') return;
                if (window.currentViewedInvoiceNo) return;
                const hasUnsavedRows = (typeof rows !== 'undefined' && Array.isArray(rows)) &&
                    rows.some(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0);
                if (hasUnsavedRows) {
                    try { if (typeof saveDraft === 'function') saveDraft(true); } catch (_ed) {}
                    e.preventDefault();
                    e.returnValue = '';
                }
            } catch (_eb) {}
        });

        if (typeof BroadcastChannel !== 'undefined') {
            try {
                const purSyncChannel = new BroadcastChannel('magaiees_sync_channel');
                purSyncChannel.onmessage = function(ev) {
                    const msg = ev && ev.data;
                    if (!msg) return;
                    if (msg.module === 'master_data' || msg.type === 'master_data_saved' || msg.type === 'purchase_invoice_saved' || msg.type === 'purchase_invoice_deleted') {
                        window._cachedPurchaseInvoicesRaw = null;
                        window._cachedPurchaseList = null;
                        if (typeof loadHubTransactions === 'function' && typeof currentPurchaseView !== 'undefined' && currentPurchaseView === 'hub') {
                            loadHubTransactions();
                        }
                    }
                };
            } catch (_ebc) {}
        }

        // === 8. Smart Supplier Proposals: Last Supply Badge, 1-Click Clone Last Invoice & Credit Limit Scheduler ===
        window.getPurchaseHistoryPool = function() {
            if (typeof getPurchaseInvoicesList === 'function') {
                const list = getPurchaseInvoicesList();
                if (Array.isArray(list) && list.length > 0) return list;
            }
            let pool = [];
            ['erp_purchase_invoices_v1', 'erp_purchases_v5', 'magaies_local_purchase_invoices_v1'].forEach(k => {
                try {
                    const arr = JSON.parse(localStorage.getItem(k) || '[]');
                    if (Array.isArray(arr)) pool = pool.concat(arr);
                } catch (_e) {}
            });
            return pool;
        };

        window.resolvePurItemMeta = function(row) {
            if (!row) return { id: '', code: '', name: '', unit: 'قطعة', cost: 0 };
            const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
            const found = itemsList.find(i => String(i.id) === String(row.itemId));
            return {
                id: row.itemId || (found ? found.id : ''),
                code: row.itemCode || (found ? (found.code || found.id) : ''),
                name: row.itemName || (found ? found.name : ''),
                unit: row.selectedUnit || row.unitName || (found ? (found.baseUnit || found.mainUnit || found.unit || 'قطعة') : 'قطعة'),
                cost: parseFloat(found ? (found.lastPurchaseCost || found.costPrice || found.price || 0) : 0) || 0
            };
        };

        window.findSupplierLastItemHistory = function(supplierId, supplierName, itemId, itemName) {
            const pool = window.getPurchaseHistoryPool();
            const norm = window.normHubAr || function(t) { return String(t || '').trim().toLowerCase(); };
            const targetSupName = norm(supplierName);
            const targetItemName = norm(itemName);

            let bestSameSupplier = null;
            let bestAnySupplier = null;

            for (let i = pool.length - 1; i >= 0; i--) {
                const inv = pool[i];
                if (!inv || !Array.isArray(inv.items)) continue;
                const invSupId = inv.supplierId || inv.vendorId || inv.customerId || inv.supplier_id || '';
                const invSupName = norm(inv.supplierName || inv.supplier || inv.vendorName || inv.customerName || '');
                const isSameSup = (supplierId && String(invSupId) === String(supplierId)) ||
                                  (targetSupName && invSupName && invSupName === targetSupName);
                const invDate = String(inv.date || inv.invoiceDate || inv.invoice_date || inv.created_at || '').split(' ')[0] || '—';

                for (let j = 0; j < inv.items.length; j++) {
                    const it = inv.items[j];
                    if (!it) continue;
                    const itId = it.itemId || it.item_id || it.id || '';
                    const itName = norm(it.itemName || it.item_name || it.name || '');
                    const matchItem = (itemId && String(itId) === String(itemId)) ||
                                      (targetItemName && itName && itName === targetItemName);
                    if (!matchItem) continue;
                    const price = parseFloat(it.price || it.unit_price || it.cost || 0) || 0;
                    if (price <= 0) continue;
                    const rec = {
                        price: price,
                        date: invDate,
                        qty: parseFloat(it.quantity || it.qty || 1) || 1,
                        unit: it.selectedUnit || it.unitName || it.unit || '',
                        invoiceNo: inv.invoiceNo || inv.invoice_number || inv.id || '',
                        sameSupplier: isSameSup
                    };
                    if (isSameSup && !bestSameSupplier) bestSameSupplier = rec;
                    if (!bestAnySupplier) bestAnySupplier = rec;
                    if (bestSameSupplier) break;
                }
                if (bestSameSupplier) break;
            }
            return bestSameSupplier || bestAnySupplier || null;
        };

        window.refreshSupplierLastSupplyHints = function() {
            if (typeof rows === 'undefined' || !Array.isArray(rows)) return;
            const supId = document.getElementById('hdr-customer')?.value || '';
            const supName = document.getElementById('hdr-customer-search')?.value || '';

            rows.forEach(function(row) {
                if (!row) return;
                const itemInput = document.getElementById('item-search-' + row.id);
                if (!itemInput || !itemInput.parentElement) return;
                let badge = itemInput.parentElement.querySelector('.supplier-last-supply-badge');

                const meta = window.resolvePurItemMeta(row);
                if (!meta.id && !meta.name) {
                    if (badge) badge.style.display = 'none';
                    return;
                }

                const hist = window.findSupplierLastItemHistory(supId, supName, meta.id, meta.name);
                const fallbackPrice = meta.cost;

                if (!hist && !(fallbackPrice > 0)) {
                    if (badge) badge.style.display = 'none';
                    return;
                }

                if (!badge) {
                    badge = document.createElement('div');
                    badge.className = 'supplier-last-supply-badge';
                    badge.style.cssText = 'display:flex; align-items:center; gap:6px; margin-top:3px; padding:2px 7px; border-radius:6px; font-size:0.71rem; font-weight:900; background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.35); color:#10b981; cursor:pointer; width:fit-content; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;';
                    itemInput.parentElement.appendChild(badge);
                }

                const lastPrice = hist ? hist.price : parseFloat(fallbackPrice);
                const lastDate = hist ? hist.date : 'سعر أساسي';
                const tagLabel = (hist && hist.sameSupplier) ? 'من نفس المورد' : (hist ? 'آخر توريد عام' : 'تكلفة البطاقة');
                const fmtPrice = (typeof fmtNum === 'function') ? fmtNum(lastPrice) : lastPrice.toLocaleString('en-US');

                badge.style.display = 'inline-flex';
                badge.title = 'اضغط لتطبيق سعر آخر شراء (' + fmtPrice + ')';
                badge.innerHTML = '<span>📅 ' + lastDate + ' (' + tagLabel + ')</span><span>•</span><span>💰 آخر شراء: <b>' + fmtPrice + '</b></span>';
                badge.onclick = function(ev) {
                    ev.stopPropagation();
                    if (typeof updateRow === 'function') {
                        updateRow(row.id, 'price', lastPrice);
                    } else {
                        row.price = lastPrice;
                    }
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                    if (typeof showToast === 'function') showToast('تم تطبيق سعر آخر شراء: ' + fmtPrice, 'success');
                };
            });
        };

        window.cloneLastSupplierPurchaseInvoice = async function() {
            const supId = document.getElementById('hdr-customer')?.value || '';
            const supName = (document.getElementById('hdr-customer-search')?.value || '').trim();
            if (!supId && !supName) {
                if (typeof showToast === 'function') showToast('يرجى اختيار المورد أولاً لنسخ أصناف آخر فاتورة له', 'warning');
                const sInp = document.getElementById('hdr-customer-search');
                if (sInp) sInp.focus();
                return;
            }

            let pool = window.getPurchaseHistoryPool();
            const norm = window.normHubAr || function(t) { return String(t || '').trim().toLowerCase(); };
            const targetSupName = norm(supName);
            const defaultWh = document.getElementById('hdr-warehouse')?.value || '';

            let lastInv = null;
            for (let i = pool.length - 1; i >= 0; i--) {
                const inv = pool[i];
                if (!inv || !Array.isArray(inv.items) || inv.items.length === 0) continue;
                const invSupId = inv.supplierId || inv.vendorId || inv.customerId || inv.supplier_id || '';
                const invSupName = norm(inv.supplierName || inv.supplier || inv.vendorName || inv.customerName || '');
                if ((supId && String(invSupId) === String(supId)) || (targetSupName && invSupName && invSupName === targetSupName)) {
                    lastInv = inv;
                    break;
                }
            }

            if (!lastInv || !Array.isArray(lastInv.items) || lastInv.items.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد فاتورة مشتريات سابقة مسجلة لهذا المورد', 'info');
                return;
            }

            if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                rows.length = 0;
                const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
                lastInv.items.forEach(function(it) {
                    const rawId = it.itemId || it.item_id || it.id || '';
                    const rawName = norm(it.itemName || it.item_name || it.name || '');
                    let matchedItem = itemsList.find(x => String(x.id) === String(rawId));
                    if (!matchedItem && rawName) {
                        matchedItem = itemsList.find(x => norm(x.name) === rawName);
                    }
                    const finalItemId = matchedItem ? matchedItem.id : rawId;
                    const priceVal = parseFloat(it.price || it.unit_price || it.cost || (matchedItem ? matchedItem.costPrice || matchedItem.price : 0) || 0) || 0;
                    const qtyVal = parseFloat(it.quantity || it.qty || 1) || 1;
                    const unitVal = it.selectedUnit || it.unitName || it.unit || (matchedItem ? (matchedItem.baseUnit || matchedItem.mainUnit || matchedItem.unit || '') : '');
                    if (typeof rowIdCounter !== 'undefined') rowIdCounter++;
                    rows.push({
                        id: (typeof rowIdCounter !== 'undefined') ? rowIdCounter : (Date.now() + Math.floor(Math.random() * 1000)),
                        itemId: finalItemId,
                        quantity: qtyVal,
                        price: priceVal,
                        warehouseId: it.warehouseId || defaultWh,
                        costCenterId: it.costCenterId || '',
                        qrCode: it.qrCode || '',
                        selectedUnit: unitVal
                    });
                });
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                setTimeout(function() {
                    window.refreshSupplierLastSupplyHints();
                    window.checkSupplierCreditLimitAndSchedule();
                    const firstQty = document.querySelector('#grid-body [data-qty-input], #grid-body td[data-col-id="quantity"] input');
                    if (firstQty) { firstQty.focus(); firstQty.select(); }
                }, 60);
                if (typeof showToast === 'function') {
                    showToast('📋 تم نسخ (' + lastInv.items.length + ') أصناف من آخر فاتورة للمورد — عدّل الكميات فقط', 'success');
                }
            }
        };

        window.checkSupplierCreditLimitAndSchedule = function() {
            const alertBar = document.getElementById('supplier-credit-limit-alert-bar');
            if (!alertBar) return;

            const supId = document.getElementById('hdr-customer')?.value || '';
            const vendorsList = (typeof VENDORS !== 'undefined' && Array.isArray(VENDORS)) ? VENDORS : [];
            if (!supId || vendorsList.length === 0) {
                alertBar.style.display = 'none';
                return;
            }

            const sup = vendorsList.find(c => String(c.id) === String(supId));
            if (!sup) {
                alertBar.style.display = 'none';
                return;
            }

            const creditLimit = parseFloat(sup.creditLimit || sup.credit_limit || 0) || 0;
            const currentDebt = Math.max(0, parseFloat(sup.totalDebt || sup.balance || 0) || 0);
            const grandTotalText = (document.getElementById('t-grand-dynamic-loc')?.textContent || document.getElementById('t-grand-local')?.textContent || '0').replace(/[^0-9.-]/g, '');
            const grandTotal = parseFloat(grandTotalText) || 0;
            const paidCash = parseFloat(String(document.getElementById('pay-cash-amount')?.value || '0').replace(/,/g, '')) || 0;
            let paidBank = 0;
            document.querySelectorAll('#bank-transfers-list input[type="number"], #bank-transfers-list .bank-amount-input').forEach(el => {
                paidBank += (parseFloat(el.value) || 0);
            });
            const newCreditRemainder = Math.max(0, grandTotal - paidCash - paidBank);
            const projectedDebt = currentDebt + newCreditRemainder;

            if (creditLimit > 0 && projectedDebt > creditLimit && newCreditRemainder > 0) {
                const excess = projectedDebt - creditLimit;
                const fmtExcess = (typeof fmtNum === 'function') ? fmtNum(excess) : excess.toLocaleString('en-US');
                const fmtLimit = (typeof fmtNum === 'function') ? fmtNum(creditLimit) : creditLimit.toLocaleString('en-US');
                alertBar.style.display = 'flex';
                alertBar.innerHTML =
                    '<span>⚠️ تنبيه ائتماني: المديونية المتوقعة تتجاوز السقف الائتماني للمورد (' + fmtLimit + ') بمقدار <b>' + fmtExcess + '</b></span>' +
                    '<button type="button" onclick="window.scheduleSupplierCreditExcess(' + excess + ')" style="background:#ef4444; color:#fff; border:none; border-radius:6px; padding:3px 9px; font-size:0.73rem; font-weight:900; cursor:pointer;">📅 جدولة الدفعة وتغطية الفارق</button>';
            } else {
                alertBar.style.display = 'none';
            }
        };

        window.scheduleSupplierCreditExcess = function(excessAmount) {
            const payPanel = document.getElementById('payment-panel');
            if (payPanel) payPanel.style.display = 'block';
            const payLayout = document.getElementById('payment-layout');
            if (payLayout) payLayout.style.display = 'flex';
            const creditBtn = document.getElementById('toggle-credit');
            if (creditBtn && !creditBtn.classList.contains('active') && typeof togglePaymentSection === 'function') {
                togglePaymentSection('credit');
            }
            const cashBtn = document.getElementById('toggle-cash');
            if (cashBtn && !cashBtn.classList.contains('active') && typeof togglePaymentSection === 'function') {
                togglePaymentSection('cash');
            }
            const cashInp = document.getElementById('pay-cash-amount');
            if (cashInp && excessAmount > 0) {
                cashInp.value = Math.ceil(excessAmount);
                if (typeof onPaymentAmountInput === 'function') onPaymentAmountInput();
            }
            const dueDateInp = document.getElementById('pay-credit-due-date');
            if (dueDateInp) {
                const d = new Date();
                d.setDate(d.getDate() + 30);
                dueDateInp.value = d.toISOString().split('T')[0];
            }
            window.checkSupplierCreditLimitAndSchedule();
            if (typeof showToast === 'function') {
                showToast('📅 تم فتح جدولة الدفعة وتخصيص الفارق الائتماني وتحديد الاستحقاق (+30 يوماً)', 'success');
            }
        };

        const _origOnCustomerChange = window.onCustomerChange;
        if (typeof _origOnCustomerChange === 'function' && !_origOnCustomerChange._wrappedProposals) {
            window.onCustomerChange = function() {
                const r = _origOnCustomerChange.apply(this, arguments);
                setTimeout(function() {
                    window.refreshSupplierLastSupplyHints();
                    window.checkSupplierCreditLimitAndSchedule();
                }, 40);
                return r;
            };
            window.onCustomerChange._wrappedProposals = true;
        }

        setTimeout(function() {
            window.refreshSupplierLastSupplyHints();
            window.checkSupplierCreditLimitAndSchedule();
        }, 400);

        // === 10. Excel/CSV Export, A4 & 80mm Thermal Print, Barcode Labels & Journal Preview ===
        window.exportCurrentPurchaseInvoiceExcel = function() {
            const invNo = (document.getElementById('inv-number')?.textContent || 'PUR').trim();
            const supName = (document.getElementById('hdr-customer-search')?.value || 'مورد عام').trim();
            const invDate = (document.getElementById('hdr-date')?.value || '').trim();
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
            if (activeRows.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد أصناف في الفاتورة لتصديرها إلى Excel', 'warning');
                return;
            }
            const csvRows = [
                ['رقم الفاتورة', invNo, 'التاريخ', invDate, 'المورد', supName, 'العملة', cur],
                [],
                ['#', 'كود الصنف', 'اسم الصنف', 'الوحدة', 'الكمية', 'سعر الوحدة', 'الإجمالي']
            ];
            activeRows.forEach((r, idx) => {
                const meta = window.resolvePurItemMeta(r);
                const q = parseFloat(r.quantity) || 0;
                const p = parseFloat(r.price) || 0;
                csvRows.push([
                    idx + 1,
                    meta.code,
                    meta.name,
                    meta.unit,
                    q,
                    p,
                    (q * p).toFixed(2)
                ]);
            });
            const grandTxt = (document.getElementById('t-grand-dynamic-loc')?.textContent || document.getElementById('t-grand-local')?.textContent || '0').trim();
            csvRows.push([]);
            csvRows.push(['', '', '', '', '', 'صافي الفاتورة الكلي', grandTxt]);

            const csvContent = '\uFEFF' + csvRows.map(row => row.map(c => '"' + String(c ?? '').replace(/"/g, '""') + '"').join(',')).join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Purchase_Invoice_' + invNo + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (typeof showToast === 'function') showToast('📊 تم تصدير فاتورة المشتريات إلى ملف Excel بنجاح', 'success');
        };

        window.exportPurchaseHubToExcel = function() {
            const trs = Array.from(document.querySelectorAll('#hub-transactions-body tr'));
            if (trs.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد فواتير معروضة حالياً لتصديرها', 'warning');
                return;
            }
            const csvRows = [['رقم الفاتورة', 'التاريخ', 'المورد', 'الأصناف', 'الإجمالي', 'الحالة', 'المخزن']];
            trs.forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 7) {
                    csvRows.push([
                        tds[0].innerText.trim(),
                        tds[1].innerText.trim(),
                        tds[2].innerText.trim(),
                        tds[3].innerText.replace(/\s+/g, ' ').trim(),
                        tds[4].innerText.trim(),
                        tds[5].innerText.trim(),
                        tds[6].innerText.trim()
                    ]);
                }
            });
            const csvContent = '\uFEFF' + csvRows.map(row => row.map(c => '"' + String(c ?? '').replace(/"/g, '""') + '"').join(',')).join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Purchase_Registry_' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (typeof showToast === 'function') showToast('📊 تم تصدير سجل المشتريات إلى Excel بنجاح', 'success');
        };

        window.printPurchaseHubReportA4 = function() {
            window.print();
        };

        window.printThermalPurchaseReceipt80mm = function() {
            const invNo = (document.getElementById('inv-number')?.textContent || 'PUR').trim();
            const supName = (document.getElementById('hdr-customer-search')?.value || 'مورد عام').trim();
            const invDate = (document.getElementById('hdr-date')?.value || '').trim();
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const grandTxt = (document.getElementById('t-grand-dynamic-loc')?.textContent || document.getElementById('t-grand-local')?.textContent || '0').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];

            const w = window.open('', '_blank', 'width=360,height=640');
            if (!w) {
                if (typeof printCurrentInvoice === 'function') printCurrentInvoice();
                return;
            }
            const rowsHtml = activeRows.map(r => {
                const meta = window.resolvePurItemMeta(r);
                const q = parseFloat(r.quantity) || 0;
                const p = parseFloat(r.price) || 0;
                return `<tr>
                    <td style="padding:4px 2px; border-bottom:1px dashed #94a3b8;">${meta.name}</td>
                    <td style="padding:4px 2px; text-align:center; border-bottom:1px dashed #94a3b8;">${q} ${meta.unit}</td>
                    <td style="padding:4px 2px; text-align:left; border-bottom:1px dashed #94a3b8; font-weight:800;">${(q * p).toLocaleString('en-US')}</td>
                </tr>`;
            }).join('');
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>إيصال مشتريات #${invNo}</title>
                <style>
                    @page { size: 80mm auto; margin: 3mm; }
                    body { font-family: 'Cairo', 'Tajawal', sans-serif; width: 74mm; margin: 0 auto; color: #000; font-size: 12px; }
                    h3 { margin: 4px 0; text-align: center; font-size: 15px; }
                    .meta { font-size: 11px; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 5px 0; margin: 6px 0; }
                    table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
                    th { border-bottom: 1.5px solid #000; padding: 4px 2px; text-align: right; }
                    .tot { margin-top: 8px; padding-top: 6px; border-top: 2px solid #000; font-size: 14px; font-weight: 900; display: flex; justify-content: space-between; }
                </style></head><body onload="window.print(); setTimeout(()=>window.close(), 600);">
                <h3>🧾 إيصال فاتورة مشتريات</h3>
                <div class="meta">
                    <div><b>رقم الفاتورة:</b> ${invNo}</div>
                    <div><b>التاريخ:</b> ${invDate}</div>
                    <div><b>المورد:</b> ${supName}</div>
                </div>
                <table>
                    <thead><tr><th>الصنف</th><th style="text-align:center;">الكمية</th><th style="text-align:left;">الإجمالي</th></tr></thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
                <div class="tot"><span>الصافي (${cur}):</span><span>${grandTxt}</span></div>
            </body></html>`);
            w.document.close();
        };

        window.printPurchaseItemsBarcodeLabels = function() {
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0)
                : [];
            if (activeRows.length === 0) {
                if (typeof showToast === 'function') showToast('أضف أصنافاً أولاً لطباعة ملصقات الباركود الخاصة بها', 'warning');
                return;
            }
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const w = window.open('', '_blank', 'width=420,height=640');
            if (!w) return;
            const labelsHtml = activeRows.map(r => {
                const meta = window.resolvePurItemMeta(r);
                const code = String(meta.code || '1001').slice(0, 14);
                const qty = Math.max(1, Math.min(20, Math.round(parseFloat(r.quantity) || 1)));
                const price = (parseFloat(r.price) || 0).toLocaleString('en-US');
                return `<div style="border:1.5px solid #000; border-radius:6px; padding:6px 8px; margin:6px auto; width:52mm; text-align:center; page-break-inside:avoid;">
                    <div style="font-size:11px; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${meta.name}</div>
                    <div style="font-family:monospace; font-size:15px; letter-spacing:3px; font-weight:900; margin:4px 0; border-top:2px solid #000; border-bottom:2px solid #000; padding:2px 0;">||| |||| || |||</div>
                    <div style="font-size:10px; font-weight:800;">#${code} • الكمية: ${qty} ${meta.unit}</div>
                    <div style="font-size:11px; font-weight:900; margin-top:2px;">السعر: ${price} ${cur}</div>
                </div>`;
            }).join('');
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>ملصقات باركود المشتريات</title>
                <style>body{font-family:'Cairo','Tajawal',sans-serif; margin:0; padding:6px; color:#000;}</style>
                </head><body onload="window.print(); setTimeout(()=>window.close(), 600);">${labelsHtml}</body></html>`);
            w.document.close();
        };
    })();
