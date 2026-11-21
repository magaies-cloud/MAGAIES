(function() {
        window._finStripCurrency = localStorage.getItem('sales_fin_strip_cur') || 'SDG';
        window.getFinStripCurrency = function() {
            const sel = document.getElementById('hdr-currency');
            return (sel && sel.value === 'USD') ? 'USD' : (window._finStripCurrency === 'USD' ? 'USD' : 'SDG');
        };
        window.convFinStripVal = function(sdgVal) {
            const n = parseFloat(sdgVal) || 0;
            if (window.getFinStripCurrency() === 'USD') {
                const exRate = parseFloat(document.getElementById('hdr-exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
                return exRate > 0 ? parseFloat((n / exRate).toFixed(2)) : n;
            }
            return n;
        };
        window.syncFinStripCurrencyBtnUI = function(cur) {
            const btn = document.getElementById('fin-strip-currency-btn');
            if (!btn) return;
            const c = cur || window.getFinStripCurrency();
            btn.setAttribute('data-cur-mode', c);
            btn.textContent = (c === 'USD') ? 'دولار ⇄' : 'جنيه ⇄';
        };
        window.toggleFinStripCurrency = function() {
            window._finStripCurrency = (window.getFinStripCurrency() === 'SDG') ? 'USD' : 'SDG';
            try { localStorage.setItem('sales_fin_strip_cur', window._finStripCurrency); } catch (_e) {}
            window.syncFinStripCurrencyBtnUI(window._finStripCurrency);
            if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
            if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
        };

        window.cloneLastCustomerSalesInvoice = async function() {
            const custId = document.getElementById('hdr-customer')?.value || '';
            const custName = (document.getElementById('hdr-customer-search')?.value || '').trim();
            if (!custId && !custName) {
                if (typeof showToast === 'function') showToast('يرجى اختيار العميل أولاً لنسخ أصناف آخر فاتورة له', 'warning');
                const sInp = document.getElementById('hdr-customer-search');
                if (sInp) sInp.focus();
                return;
            }
            const norm = function(t) { return String(t || '').trim().toLowerCase(); };
            const targetName = norm(custName);
            const defaultWh = document.getElementById('hdr-warehouse')?.value || '';
            let pool = [];
            try {
                if (Array.isArray(window.allHubTransactions) && window.allHubTransactions.length) pool = pool.concat(window.allHubTransactions);
                const ls1 = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
                if (Array.isArray(ls1)) pool = pool.concat(ls1);
                const ls2 = JSON.parse(localStorage.getItem('sales_invoices') || '[]');
                if (Array.isArray(ls2)) pool = pool.concat(ls2);
            } catch (_e) {}
            let lastInv = null;
            for (let i = pool.length - 1; i >= 0; i--) {
                const inv = pool[i];
                if (!inv || !Array.isArray(inv.items) || inv.items.length === 0) continue;
                const invCustId = inv.customerId || inv.customer_id || '';
                const invCustName = norm(inv.customerName || inv.customer || inv.customer_name || '');
                if ((custId && String(invCustId) === String(custId)) || (targetName && invCustName && invCustName === targetName)) {
                    lastInv = inv;
                    break;
                }
            }
            if (!lastInv || !Array.isArray(lastInv.items) || lastInv.items.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد فاتورة مبيعات سابقة مسجلة لهذا العميل', 'warning');
                return;
            }
            if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                rows.length = 0;
                const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
                lastInv.items.forEach(function(it) {
                    const rawId = it.itemId || it.item_id || it.id || '';
                    const rawName = norm(it.itemName || it.item_name || it.name || '');
                    let matchedItem = itemsList.find(x => String(x.id) === String(rawId)) || (rawName ? itemsList.find(x => norm(x.name) === rawName) : null);
                    const finalItemId = matchedItem ? matchedItem.id : rawId;
                    const priceVal = parseFloat(it.price || it.unit_price || (matchedItem ? matchedItem.price : 0) || 0) || 0;
                    const qtyVal = parseFloat(it.quantity || it.qty || 1) || 1;
                    const unitVal = it.selectedUnit || it.unitName || it.unit || (matchedItem ? (matchedItem.baseUnit || matchedItem.mainUnit || matchedItem.unit || '') : '');
                    if (typeof rowIdCounter !== 'undefined') rowIdCounter++;
                    rows.push({
                        id: (typeof rowIdCounter !== 'undefined') ? rowIdCounter : (Date.now() + Math.floor(Math.random() * 1000)),
                        itemId: finalItemId,
                        quantity: qtyVal,
                        price: priceVal,
                        warehouseId: it.warehouseId || defaultWh,
                        selectedUnit: unitVal,
                        unitFactor: parseFloat(it.unitFactor || 1) || 1,
                        note: it.note || ''
                    });
                });
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                if (typeof showToast === 'function') {
                    showToast('📋 تم نسخ (' + lastInv.items.length + ') أصناف من آخر فاتورة للعميل', 'success');
                }
            }
        };

        window.alignGrandTotalUnderTotalColumn = function() {
            try {
                const table = document.getElementById('pos-grid');
                const card = document.querySelector('#grid-foot .pur-compact-tot-card');
                const dynLoc = document.getElementById('t-grand-dynamic-loc');
                const dynFc = document.getElementById('t-grand-dynamic-fc');
                if (!table || !card || !dynLoc || !dynFc) return;

                const curEl = document.getElementById('hdr-currency');
                const isSDG = (!curEl || curEl.value === 'SDG');
                const primaryBox = isSDG ? dynLoc : dynFc;

                const totalCol = table.querySelector('tbody tr td[data-col-id="col_total"]')
                              || table.querySelector('thead tr th[data-col-id="col_total"]')
                              || table.querySelector('tbody tr td[data-col-id="total"]')
                              || table.querySelector('thead tr th[data-col-id="total"]');
                if (!totalCol) return;

                const cardRect = card.getBoundingClientRect();
                const totalRect = totalCol.getBoundingClientRect();
                if (!cardRect.width || !totalRect.width) return;

                const boxW = primaryBox.offsetWidth || 175;
                const totalCenter = totalRect.left + (totalRect.width / 2);
                const targetBoxLeft = totalCenter - (boxW / 2);
                const padLeft = Math.max(10, Math.round(targetBoxLeft - cardRect.left));
                card.style.setProperty('padding-left', padLeft + 'px', 'important');
            } catch (_e) {}
        };

        window.formatGrandTotalsDualSdgUsd = function() {
            const curEl = document.getElementById('hdr-currency');
            const isSDG = (!curEl || curEl.value === 'SDG');
            let exRate = parseFloat(document.getElementById('hdr-exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 1;
            if (isSDG && exRate <= 1) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD && parseFloat(cfg.rateUSD) > 1) exRate = parseFloat(cfg.rateUSD);
                    else if (parseFloat(window.SYSTEM_USD_RATE) > 1) exRate = parseFloat(window.SYSTEM_USD_RATE);
                } catch (_e) {}
            }
            if (isSDG) document.body.classList.add('cur-sdg');
            else document.body.classList.remove('cur-sdg');

            const dynLoc = document.getElementById('t-grand-dynamic-loc');
            const dynFc = document.getElementById('t-grand-dynamic-fc');
            const tGrand = document.getElementById('t-grand');
            if (!dynLoc || !dynFc) return;

            const parseRaw = (txt) => parseFloat(String(txt || '0').replace(/[^0-9.-]/g, '')) || 0;
            const rawGrand = parseRaw(tGrand ? tGrand.textContent : '0');

            const sdgVal = isSDG ? rawGrand : (rawGrand * exRate);
            const usdVal = isSDG ? (exRate > 0 ? (rawGrand / exRate) : 0) : rawGrand;

            const fmtSDG = Math.round(sdgVal).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            const fmtUSD = usdVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            dynLoc.classList.remove('hide-if-sdg');
            dynLoc.setAttribute('data-cur-unit', 'ج.س');
            if (dynLoc.textContent !== fmtSDG) dynLoc.textContent = fmtSDG;
            const lenSdg = fmtSDG.length;
            const sdgPx = lenSdg > 15 ? 20 : (lenSdg > 11 ? 24 : 27);
            dynLoc.style.setProperty('font-size', sdgPx + 'px', 'important');

            dynFc.classList.remove('hide-if-sdg');
            dynFc.setAttribute('data-cur-unit', '$');
            if (dynFc.textContent !== fmtUSD) dynFc.textContent = fmtUSD;
            const lenUsd = fmtUSD.length;
            const usdPx = lenUsd > 13 ? 19 : (lenUsd > 10 ? 22 : (isSDG ? 24 : 27));
            dynFc.style.setProperty('font-size', usdPx + 'px', 'important');

            const valsWrap = dynLoc.parentElement;
            if (valsWrap && valsWrap === dynFc.parentElement) {
                if (isSDG) {
                    if (valsWrap.firstElementChild !== dynFc) {
                        valsWrap.appendChild(dynFc);
                        valsWrap.appendChild(dynLoc);
                    }
                } else {
                    if (valsWrap.firstElementChild !== dynLoc) {
                        valsWrap.appendChild(dynLoc);
                        valsWrap.appendChild(dynFc);
                    }
                }
            }
            window.alignGrandTotalUnderTotalColumn();
        };
        window.syncTotalsVisibility = window.formatGrandTotalsDualSdgUsd;

        document.addEventListener('DOMContentLoaded', function() {
            window.syncFinStripCurrencyBtnUI();
            window.formatGrandTotalsDualSdgUsd();
            if (typeof window.onCustomerChange === 'function') {
                setTimeout(() => window.onCustomerChange(), 80);
            }
            const exInput = document.getElementById('hdr-exchange-rate');
            if (exInput) {
                exInput.addEventListener('change', () => {
                    if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                    window.formatGrandTotalsDualSdgUsd();
                });
                exInput.addEventListener('input', () => {
                    if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                    window.formatGrandTotalsDualSdgUsd();
                });
            }
            const curSel = document.getElementById('hdr-currency');
            if (curSel) {
                curSel.addEventListener('change', () => setTimeout(() => window.formatGrandTotalsDualSdgUsd(), 30));
            }
            const finCardEl = document.getElementById('customer-financial-card');
            if (finCardEl && typeof MutationObserver !== 'undefined') {
                let _finFitTimer = 0;
                new MutationObserver(() => {
                    cancelAnimationFrame(_finFitTimer);
                    _finFitTimer = requestAnimationFrame(() => { if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields(); });
                }).observe(finCardEl, { childList: true, characterData: true, subtree: true });
            }
            window.addEventListener('resize', () => window.alignGrandTotalUnderTotalColumn());
            const tableEl = document.getElementById('pos-grid');
            if (tableEl && typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(() => window.alignGrandTotalUnderTotalColumn()).observe(tableEl);
            }
        });
        window.syncFinStripCurrencyBtnUI();
        setTimeout(() => { window.formatGrandTotalsDualSdgUsd(); if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields(); }, 120);
    })();
