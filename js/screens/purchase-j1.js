        // Real-time synchronization of local and foreign adjustments
        window.syncFc = function(name) {
            let localId = '';
            let fcId = '';
            if (name === 'transport') { localId = 't-transport'; fcId = 't-transport-fc'; }
            else if (name === 'labor') { localId = 't-labor'; fcId = 't-labor-fc'; }
            else if (name === 'additions') { localId = 't-additions-loc'; fcId = 't-additions-fc'; }
            else if (name === 'tax') { localId = 't-tax-loc'; fcId = 't-tax-fc'; }
            else if (name === 'discount') { localId = 't-discount-loc'; fcId = 't-discount-fc'; }

            if (!localId || !fcId) return;

            const rateInput = document.getElementById('hdr-exchange-rate');
            let rate = rateInput ? (parseFloat(rateInput.value) || 1) : 1;
            const curEl = document.getElementById('hdr-currency');
            const isBaseSDG = !curEl || curEl.value === 'SDG';

            // If invoice is in SDG, the rate input is locked to 1. Fetch real USD rate for the foreign column.
            if (isBaseSDG && rate === 1) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD) rate = parseFloat(cfg.rateUSD) || 1;
                } catch(e) {}
            }

            const localEl = document.getElementById(localId);
            const fcEl = document.getElementById(fcId);
            
            if (localEl && fcEl) {
                const pNum = typeof window.parseNum === 'function' ? window.parseNum : function(v) { return parseFloat(v.toString().replace(/,/g, '')) || 0; };
                const fNum = typeof window.fmtNum === 'function' ? window.fmtNum : function(v) { return v % 1 === 0 ? v : parseFloat(v.toFixed(4)); };
                
                const col1Val = pNum(localEl.value);
                const col2Val = isBaseSDG ? (rate > 0 ? col1Val / rate : 0) : (col1Val * rate);
                
                fcEl.value = fNum(col2Val);
                if (typeof window.recalcAll === 'function') window.recalcAll();
            }
        };

        window.syncLocal = function(name) {
            let localId = '';
            let fcId = '';
            if (name === 'transport') { localId = 't-transport'; fcId = 't-transport-fc'; }
            else if (name === 'labor') { localId = 't-labor'; fcId = 't-labor-fc'; }
            else if (name === 'additions') { localId = 't-additions-loc'; fcId = 't-additions-fc'; }
            else if (name === 'tax') { localId = 't-tax-loc'; fcId = 't-tax-fc'; }
            else if (name === 'discount') { localId = 't-discount-loc'; fcId = 't-discount-fc'; }

            if (!localId || !fcId) return;

            const rateInput = document.getElementById('hdr-exchange-rate');
            let rate = rateInput ? (parseFloat(rateInput.value) || 1) : 1;
            const curEl = document.getElementById('hdr-currency');
            const isBaseSDG = !curEl || curEl.value === 'SDG';

            // If invoice is in SDG, the rate input is locked to 1. Fetch real USD rate for the foreign column.
            if (isBaseSDG && rate === 1) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD) rate = parseFloat(cfg.rateUSD) || 1;
                } catch(e) {}
            }

            const localEl = document.getElementById(localId);
            const fcEl = document.getElementById(fcId);
            
            if (localEl && fcEl) {
                const pNum = typeof window.parseNum === 'function' ? window.parseNum : function(v) { return parseFloat(v.toString().replace(/,/g, '')) || 0; };
                const fNum = typeof window.fmtNum === 'function' ? window.fmtNum : function(v) { return v % 1 === 0 ? v : parseFloat(v.toFixed(4)); };
                
                const col2Val = pNum(fcEl.value);
                const col1Val = isBaseSDG ? (col2Val * rate) : (rate > 0 ? col2Val / rate : 0);
                
                localEl.value = fNum(col1Val);
                if (typeof window.recalcAll === 'function') window.recalcAll();
            }
        };

        // Purchase Hub Logic
        let currentPurchaseView = 'hub';

        function handleCurrencyChange(val) {
            const rateInput = document.getElementById('hdr-exchange-rate');
            const curSelect = document.getElementById('hdr-currency');
            const prevCurrency = curSelect ? (curSelect.dataset.prevVal || 'SDG') : 'SDG';
            if (curSelect) curSelect.dataset.prevVal = val;
            
            if (!rateInput) return;
            
            // Always enable editing for all currencies (including SDG)
            rateInput.disabled = false;

            // When switching to USD, delete / clear previous SDG prices from existing rows
            if (val === 'USD' && prevCurrency !== 'USD') {
                if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                    rows.forEach(r => {
                        r.price = '';
                        r.fcPrice = '';
                    });
                }
            }
            
            // If current rate is already valid and > 1, and we are switching between SDG and USD, keep it!
            const currentVal = parseFloat(rateInput.value) || 0;
            if (currentVal > 1 && currentVal !== 3000 && rateInput.dataset.userEdited === '1' && (val === 'SDG' || val === 'USD') && (prevCurrency === 'SDG' || prevCurrency === 'USD')) {
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }

            // For SDG, show USD exchange rate (e.g. 3000)
            const targetCurr = (val === 'SDG' ? 'USD' : val);
            
            // Step 1: Try to get rate from local settings function
            let rate = 0;
            if (window.getExchangeRateForCurrency) {
                rate = window.getExchangeRateForCurrency(targetCurr);
            }
            
            if (rate > 0) {
                rateInput.value = rate;
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            
            // Step 2: Try to read directly from localStorage settings (any mode)
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (targetCurr === 'USD' && cfg.rateUSD) { rateInput.value = cfg.rateUSD; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
                if (targetCurr === 'SAR' && cfg.rateSAR) { rateInput.value = cfg.rateSAR; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
                if (targetCurr === 'AED' && cfg.rateAED) { rateInput.value = cfg.rateAED; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
                const dedicatedUsd = parseFloat(localStorage.getItem('erp_rate_usd'));
                if (targetCurr === 'USD' && dedicatedUsd > 0) { rateInput.value = dedicatedUsd; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
            } catch(e) {}
            
            // Step 3: Use server-injected SYSTEM_USD_RATE immediately without network roundtrip if available
            if (window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 0) {
                const usdRate = parseFloat(window.SYSTEM_USD_RATE);
                window._cachedGlobalRate = usdRate;
                let finalRate = usdRate;
                if (targetCurr === 'SAR') finalRate = parseFloat((usdRate / 3.75).toFixed(4));
                if (targetCurr === 'AED') finalRate = parseFloat((usdRate / 3.67).toFixed(4));
                if (targetCurr === 'EGP') finalRate = parseFloat((usdRate / 50.0).toFixed(4));
                rateInput.value = finalRate;
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            rateInput.placeholder = '⏳ جاري التحميل...';
            
            fetch('api_index.php/exchange-rate')
                .then(r => r.json())
                .then(data => {
                    if (data && data.rate) {
                        const usdRate = parseFloat(data.rate);
                        if (usdRate > 0) {
                            window._cachedGlobalRate = usdRate;
                            let finalRate = usdRate;
                            if (targetCurr === 'SAR') finalRate = parseFloat((usdRate / 3.75).toFixed(4));
                            if (targetCurr === 'AED') finalRate = parseFloat((usdRate / 3.67).toFixed(4));
                            if (targetCurr === 'EGP') finalRate = parseFloat((usdRate / 50.0).toFixed(4));
                            
                            rateInput.value = finalRate;
                            if (typeof renderGrid === 'function') renderGrid();
                            if (typeof recalcAll === 'function') recalcAll();
                            return;
                        }
                    }
                    if (!rateInput.value || parseFloat(rateInput.value) <= 0) {
                        rateInput.value = window.SYSTEM_USD_RATE || 5999;
                    }
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                })
                .catch(() => {
                    if (!rateInput.value || parseFloat(rateInput.value) <= 0) {
                        rateInput.value = window.SYSTEM_USD_RATE || 5999;
                    }
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadHubTransactions();
            const curEl = document.getElementById('hdr-currency');
            if (curEl && typeof handleCurrencyChange === 'function') {
                handleCurrencyChange(curEl.value || 'SDG');
            }
        });

        if (window.APP_READY) {
            loadHubTransactions();
        } else {
            document.addEventListener('DBReady', () => {
                loadHubTransactions();
            });
        }

        window.addEventListener('storage', (ev) => {
            if (!ev.key || ev.key.includes('erp_purchase_invoices_v1')) {
                loadHubTransactions();
            }
        });


        function showPurchaseHub() {
            document.getElementById('purchase-editor-view').style.display = 'none';
            document.getElementById('purchase-hub-view').style.display = 'flex';
            currentPurchaseView = 'hub';
            document.body.classList.remove('proforma-mode');
            loadHubTransactions();
            
            const pageTitleEl = document.querySelector('.header-page-title');
            if (pageTitleEl && window.originalPageTitleHTML) {
                pageTitleEl.innerHTML = window.originalPageTitleHTML;
            }
            
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { 
                    if (document.referrer && document.referrer.indexOf('general-ledger.php') !== -1) { 
                        history.back(); 
                    } else { 
                        window.location.href = 'magaiees_unified_dashboard.php'; 
                    } 
                };
                backBtn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>';
            }
        }

        function openPurchaseEditor(type = 'direct', isEdit = false) {
            document.getElementById('purchase-hub-view').style.display = 'none';
            document.getElementById('purchase-editor-view').style.display = 'flex';
            currentPurchaseView = 'editor';
            
            const pageTitleEl = document.querySelector('.header-page-title');
            if (pageTitleEl) {
                if (!window.originalPageTitleHTML) {
                    window.originalPageTitleHTML = pageTitleEl.innerHTML;
                }
                let badge = document.getElementById('current-sale-type-badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.id = 'current-sale-type-badge';
                    badge.style.background = 'rgba(132, 204, 22, 0.15)';
                    badge.style.border = '1px solid rgba(132, 204, 22, 0.3)';
                    badge.style.padding = '4px 12px';
                    badge.style.borderRadius = '20px';
                    badge.style.fontSize = '0.9rem';
                    badge.style.color = '#bef264';
                    badge.style.boxShadow = '0 0 10px rgba(132, 204, 22, 0.2)';
                    badge.style.marginLeft = '10px';
                    badge.style.marginRight = '10px';
                    badge.style.display = 'none';
                }
            }
            
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { showPurchaseHub(); };
                backBtn.innerHTML = '<span style="font-weight:bold; font-size:0.8rem; padding: 0 4px;">العودة للقائمة</span>';
                backBtn.style.width = 'auto';
                backBtn.style.padding = '0 10px';
            }

            if (type === 'proforma') {
                document.body.classList.add('proforma-mode');
            } else {
                document.body.classList.remove('proforma-mode');
            }
            
            setSaleType(type);
            
            if (!isEdit) {
                if (typeof onSuccessNewInvoice === 'function') {
                    onSuccessNewInvoice(false);
                }
                if (!window.__mdInteractiveGuideActive) {
                    setTimeout(() => {
                        const firstInp = document.getElementById('hdr-customer-search') || document.getElementById('quick-item-search');
                        if (firstInp) {
                            try { firstInp.focus(); } catch (_e) {}
                        }
                    }, 60);
                }
            }
            
            window.dispatchEvent(new Event('resize'));
        }

        // ═══════════════════════════════════════
        // ═══════════════════════════════════════
        //  HUB TRANSACTIONS — Enhanced with Pagination + Dynamic Dual Filters
        // ═══════════════════════════════════════
        let hubCurrentPage = 1;
        const HUB_PAGE_SIZE = 50;
        let hubFilteredInvoices = [];
        let hubTimePeriod = 'all';

        function updateHubFilterValueUI() {
            const filterType = document.getElementById('hub-filter-type').value;
            const container = document.getElementById('hub-filter-value-container');
            if (!container) return;

            container.innerHTML = '';

            const elementWidth = '220px';

            if (filterType === 'all' || filterType === 'invoiceNo') {
                const input = document.createElement('input');
                input.type = 'text';
                input.id = 'hub-filter-value';
                input.className = 'v-input ctrl-input';
                input.style.width = elementWidth;
                input.placeholder = filterType === 'all' ? 'بحث عام...' : 'أدخل رقم الفاتورة...';
                input.oninput = filterHubTransactions;
                container.appendChild(input);
            } else if (filterType === 'warehouse') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                let warehouses = [];
                try { warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || []; } catch(e) {}
                
                let html = '<option value="">كل المخازن</option>';
                html += '<option value="PENDING_RECEIPT">⏳ قيد الاستلام</option>';
                warehouses.forEach(w => {
                    html += `<option value="${w.id}">📦 ${w.name}</option>`;
                });
                select.innerHTML = html;
                container.appendChild(select);
            } else if (filterType === 'status') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                select.innerHTML = `
                    <option value="">كل الحالات</option>
                    <option value="posted">🔒 مرحلة</option>
                    <option value="confirmed">⏳ مؤكدة</option>
                    <option value="draft">📝 مسودة</option>
                `;
                container.appendChild(select);
            } else if (filterType === 'supplier') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                let suppliers = [];
                try { suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []; } catch(e) {}
                
                let html = '<option value="">كل الموردين</option>';
                suppliers.forEach(s => {
                    if (!s.deleted) html += `<option value="${s.id}">👤 ${s.name}</option>`;
                });
                select.innerHTML = html;
                container.appendChild(select);
            } else if (filterType === 'item') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                let items = [];
                try { items = JSON.parse(localStorage.getItem('erp_items_v1')) || []; } catch(e) {}
                
                let html = '<option value="">كل الأصناف</option>';
                items.forEach(i => {
                    if (!i.deleted) html += `<option value="${i.id}">🏷️ ${i.name}</option>`;
                });
                select.innerHTML = html;
                container.appendChild(select);
            } else if (filterType === 'date') {
                const input = document.createElement('input');
                input.type = 'date';
                input.id = 'hub-filter-value';
                input.className = 'v-input ctrl-input';
                input.style.width = elementWidth;
                input.onchange = filterHubTransactions;
                container.appendChild(input);
            }
        }

        if (typeof window.fmtNum !== 'function') {
            window.fmtNum = function(val) {
                const n = parseFloat(val) || 0;
                return n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            };
        }

        function loadHubTransactions() {
            const tbody = document.getElementById('hub-transactions-body');
            const emptyState = document.getElementById('hub-empty-state');
            if (!tbody) return;
            
            const savedData = localStorage.getItem('erp_purchase_invoices_v1');
            let invoices = [];
            if (savedData) {
                try { 
                    let parsed = typeof savedData === 'string' ? JSON.parse(savedData) : savedData; 
                    if (Array.isArray(parsed)) {
                        invoices = parsed.filter(inv => inv && !inv.deleted && !inv.isGRN && !inv.isReturn);
                    }
                } catch(e) { invoices = []; }
            }
            
            invoices = [...invoices];
            
            // Apply time period filter
            if (hubTimePeriod !== 'all') {
                const now = new Date();
                const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                invoices = invoices.filter(inv => {
                    const invDate = new Date(inv.timestamp || inv.date);
                    if (isNaN(invDate.getTime())) return false;
                    if (hubTimePeriod === 'today') return invDate >= todayStart;
                    if (hubTimePeriod === 'week') {
                        const weekStart = new Date(todayStart);
                        weekStart.setDate(weekStart.getDate() - 7);
                        return invDate >= weekStart;
                    }
                    if (hubTimePeriod === 'month') {
                        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
                        return invDate >= monthStart;
                    }
                    return true;
                });
            }

            // Load lookup data
            window._hubSuppCache = window._hubSuppCache || { raw: null, obj: [] };
            let suppliers = window._hubSuppCache.obj;
            let rawSupps = localStorage.getItem('erp_suppliers_v1');
            if (rawSupps !== null && window._hubSuppCache.raw !== rawSupps) {
                try { suppliers = typeof rawSupps === 'string' ? JSON.parse(rawSupps) : rawSupps; } catch(e) { suppliers = []; }
                window._hubSuppCache.raw = rawSupps;
                window._hubSuppCache.obj = suppliers || [];
            }
            function getSupplierName(id) { const s = suppliers.find(x => String(x.id) === String(id)); return s ? s.name : id; }

            window._hubWhCache = window._hubWhCache || { raw: null, obj: [] };
            let warehouses = window._hubWhCache.obj;
            let rawWh = localStorage.getItem('erp_warehouses_v1');
            if (rawWh !== null && window._hubWhCache.raw !== rawWh) {
                try { warehouses = typeof rawWh === 'string' ? JSON.parse(rawWh) : rawWh; } catch(e) { warehouses = []; }
                window._hubWhCache.raw = rawWh;
                window._hubWhCache.obj = warehouses || [];
            }
            function getWarehouseName(id) {
                if (id === 'PENDING_RECEIPT' || !id) return '<span style="color: #f97316; font-weight: 700;">قيد الاستلام</span>';
                const w = warehouses.find(x => x.id === id); return w ? w.name : id;
            }

            let items = [];
            try { let rawItems = localStorage.getItem('erp_items_v1'); if (typeof rawItems === 'string') items = JSON.parse(rawItems) || []; } catch(e) {}
            function getItemName(id) { const item = items.find(x => x.id === id); return item ? item.name : id || 'صنف غير معروف'; }

            if (!invoices || invoices.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'flex';
                updateHubSummary([], 0, 0);
                updateHubPagination(0);
                return;
            }

            // Apply search/filter options
            const filterType = document.getElementById('hub-filter-type')?.value || 'all';
            const filterVal = document.getElementById('hub-filter-value')?.value || '';

            if (filterVal) {
                const normHubAr = (s) => String(s || '').toLowerCase()
                    .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
                    .replace(/[أإآٱ]/g, 'ا')
                    .replace(/ة/g, 'ه')
                    .replace(/[ىئ]/g, 'ي')
                    .replace(/ؤ/g, 'و')
                    .trim();
                if (filterType === 'all') {
                    const query = normHubAr(filterVal);
                    invoices = invoices.filter(inv => {
                        const itemNames = Array.isArray(inv.items) ? inv.items.map(r => r.itemName || r.name || getItemName(r.itemId)).join(' ') : '';
                        const text = normHubAr([inv.invoiceNo, inv.supplierName, getSupplierName(inv.customer || inv.customerId), itemNames, inv.notes, inv.date].join(' '));
                        return text.includes(query);
                    });
                } else if (filterType === 'invoiceNo') {
                    const query = normHubAr(filterVal);
                    invoices = invoices.filter(inv => normHubAr(inv.invoiceNo).includes(query));
                } else if (filterType === 'status') {
                    invoices = invoices.filter(inv => (inv.status || 'confirmed') === filterVal);
                } else if (filterType === 'warehouse') {
                    invoices = invoices.filter(inv => {
                        if (filterVal === 'PENDING_RECEIPT') {
                            return !inv.warehouse || inv.warehouse === 'PENDING_RECEIPT';
                        }
                        return inv.warehouse === filterVal;
                    });
                } else if (filterType === 'supplier') {
                    invoices = invoices.filter(inv => String(inv.customer || inv.customerId || inv.supplierId) === filterVal);
                } else if (filterType === 'item') {
                    invoices = invoices.filter(inv => {
                        const itemsList = inv.items || [];
                        return itemsList.some(row => String(row.itemId) === filterVal);
                    });
                } else if (filterType === 'date') {
                    invoices = invoices.filter(inv => {
                        const invDate = inv.date || '';
                        return invDate.includes(filterVal);
                    });
                }
            }

            emptyState.style.display = 'none';
            
            // Sort by date descending
            invoices.sort((a,b) => {
                const dateA = new Date(a.timestamp || a.date || 0).getTime();
                const dateB = new Date(b.timestamp || b.date || 0).getTime();
                return (isNaN(dateB) ? 0 : dateB) - (isNaN(dateA) ? 0 : dateA);
            });

            hubFilteredInvoices = invoices;

            // Calculate summary
            const postedCount = invoices.filter(i => i.status === 'posted').length;
            const pendingCount = invoices.filter(i => i.status === 'confirmed').length;
            updateHubSummary(invoices, postedCount, pendingCount);

            // Pagination
            const totalPages = Math.ceil(invoices.length / HUB_PAGE_SIZE);
            if (hubCurrentPage > totalPages) hubCurrentPage = totalPages || 1;
            const startIdx = (hubCurrentPage - 1) * HUB_PAGE_SIZE;
            const pageInvoices = invoices.slice(startIdx, startIdx + HUB_PAGE_SIZE);
            
            updateHubPagination(totalPages);

            // Render rows
            tbody.innerHTML = '';
            const fragment = document.createDocumentFragment();
            pageInvoices.forEach(inv => {
                const tr = document.createElement('tr');
                
                let itemText = 'بدون أصناف';
                const lines = inv.items || [];
                if (lines.length === 1) {
                    itemText = lines[0].itemName || lines[0].name || getItemName(lines[0].itemId);
                } else if (lines.length > 1) {
                    itemText = lines.length + ' أصناف';
                }

                // Status badge
                let statusBadge = '';
                const st = inv.status || 'confirmed';
                const recSt = inv.receiptStatus || '';
                const isProforma = inv.saleType === 'proforma' || String(inv.invoiceNo || '').startsWith('PF');
                if (isProforma) {
                    statusBadge = '<span style="background:rgba(168,85,247,0.18); color:#d8b4fe; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(168,85,247,0.4);">📋 مبدئية</span>';
                } else if (st === 'received' || recSt === 'received') {
                    statusBadge = '<span style="background:rgba(16,185,129,0.2); color:#34d399; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:800; border:1px solid rgba(16,185,129,0.45);">✅ مستلمة بالكامل</span>';
                } else if (st === 'partial_received' || recSt === 'partial_received' || recSt === 'partially_received') {
                    statusBadge = '<span style="background:rgba(56,189,248,0.18); color:#38bdf8; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:800; border:1px solid rgba(56,189,248,0.45);">📦 مستلمة جزئياً</span>';
                } else if (st === 'posted') {
                    statusBadge = '<span style="background:rgba(16,185,129,0.15); color:#34d399; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(16,185,129,0.3);">🔒 مرحلة</span>';
                } else if (st === 'confirmed') {
                    statusBadge = '<span style="background:rgba(251,191,36,0.15); color:#fbbf24; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(251,191,36,0.3);">⏳ مؤكدة</span>';
                } else if (st === 'draft') {
                    statusBadge = '<span style="background:rgba(148,163,184,0.15); color:#94a3b8; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(148,163,184,0.3);">📝 مسودة</span>';
                } else {
                    statusBadge = `<span style="background:rgba(148,163,184,0.15); color:#e2e8f0; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700;">${st}</span>`;
                }

                const invCurrency = inv.currency || 'SDG';
                const convertBtnHtml = isProforma
                    ? `<button onclick="convertHubProformaToDirect('${inv.invoiceNo}')" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.45); color:#34d399; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="تحويل لفاتورة مشتريات">🔄</button>`
                    : `<button onclick="window.location.href='smart-logistics.php?mode=purchases&inv=${encodeURIComponent(inv.invoiceNo)}'" style="background:rgba(14,165,233,0.15); border:1px solid rgba(56,189,248,0.45); color:#38bdf8; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="فتح في بوابة استلام المشتريات الذكية">📥</button>
                       <button onclick="window.location.href='purchase-receipt.php?inv=${encodeURIComponent(inv.invoiceNo)}'" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="إصدار إذن استلام مخزني (GRN)">🚛</button>
                       <button onclick="window.location.href='purchase-return.php?inv=${encodeURIComponent(inv.invoiceNo)}'" style="background:rgba(244,63,94,0.12); border:1px solid rgba(244,63,94,0.35); color:#fb7185; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="مرتجع مشتريات">↩️</button>`;

                const rawGrand = parseFloat(inv.totals?.grandTotal || inv.grandTotal || 0) || 0;
                const exRate = (typeof window.getHubExchangeRate === 'function' ? window.getHubExchangeRate() : 2650) || 2650;
                const dispGrand = window._hubShowInUsd
                    ? ('$' + (invCurrency === 'USD' ? rawGrand : rawGrand / exRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
                    : (fmtNum(invCurrency === 'USD' ? rawGrand * exRate : rawGrand) + ' <span class="hub-curr-tag" style="color:#a855f7; font-weight:900;">ج.س</span>');
                const supResolvedName = inv.supplierName || getSupplierName(inv.customer || inv.customerId || inv.supplierId) || 'مورد غير محدد';
                const supResolvedId = inv.customer || inv.customerId || inv.supplierId || '';

                tr.innerHTML = `
                    <td class="hub-col-invno" style="text-align:right; font-weight:900; font-size:1.22rem; font-family:'Outfit','Cairo',sans-serif; color:var(--gold-primary);">${inv.invoiceNo}</td>
                    <td class="hub-col-date" style="text-align:right; font-weight:900; font-size:1.05rem; font-family:'Outfit','Cairo',sans-serif;">${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG-u-nu-latn')}</td>
                    <td class="hub-col-supplier" style="text-align:right; font-weight:900; font-size:1.02rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                            <span>${supResolvedName}</span>
                            <button type="button" onclick="event.stopPropagation(); window.open('account-statement.php?id=${encodeURIComponent(supResolvedId)}','_blank');" title="كشف حساب المورد (أستاذ)" style="height:26px; padding:0 8px; border-radius:6px; background:linear-gradient(135deg,#0284c7,#0369a1); border:1.5px solid #38bdf8; color:#fff !important; font-size:0.74rem; font-weight:900; cursor:pointer; flex-shrink:0;">📖 أستاذ</button>
                        </div>
                    </td>
                    <td class="hub-col-items" style="text-align:right; font-weight:800;">${itemText}</td>
                    <td class="hub-col-total" style="text-align:center; font-weight:900; font-size:1.18rem; font-family:'Outfit','Cairo',sans-serif; direction:ltr;">
                        <span style="display:inline-flex; align-items:center; gap:6px;">
                            <span class="pur-price-lamp ${rawGrand > 0 ? 'lamp-green' : 'lamp-amber'}" style="width:16px !important; height:16px !important; min-width:16px !important;"></span>
                            <span>${dispGrand}</span>
                        </span>
                    </td>
                    <td style="text-align:center;">${statusBadge}</td>
                    <td class="hub-col-wh" style="text-align:center; font-weight:900;">${getWarehouseName(inv.warehouse)}${inv.grnNo ? `<div style="font-size:0.72rem; color:#10b981; font-weight:900;">GRN: ${inv.grnNo}</div>` : ''}</td>
                    <td style="text-align:center; white-space:nowrap;">
                        ${convertBtnHtml}
                        <button onclick="editHubTransaction('${inv.invoiceNo}')" style="background:rgba(56,189,248,0.16); border:1.5px solid rgba(56,189,248,0.45); color:#38bdf8; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض">👁️</button>
                        <button onclick="deleteHubTransaction('${inv.invoiceNo}')" style="background:rgba(239,68,68,0.16); border:1.5px solid rgba(239,68,68,0.45); color:#ef4444; border-radius:6px; width:32px; height:32px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
                    </td>
                `;
                fragment.appendChild(tr);
            });
            tbody.appendChild(fragment);
        }

        function updateHubSummary(invoices, postedCount, pendingCount) {
            let summaryEl = document.getElementById('hub-summary-bar');
            if (!summaryEl) return;
            const total = invoices.length;
            const totalAmount = invoices.reduce((sum, inv) => sum + (parseFloat(inv.totals?.grandTotal || inv.grandTotal) || 0), 0);
            const exRate = (typeof window.getHubExchangeRate === 'function' ? window.getHubExchangeRate() : 2650) || 2650;
            const totalUsd = totalAmount / exRate;
            const currBtnLbl = window._hubShowInUsd ? 'دولار ⇄' : 'جنيه ⇄';
            summaryEl.innerHTML = `
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#334155,#1e293b); border:2px solid #94a3b8; color:#fff !important; padding:4px 11px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">📊 الفواتير: <strong style="color:#fff !important; font-size:1.1rem; font-family:'Outfit';">${total}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#059669,#047857); border:2px solid #6ee7b7; color:#fff !important; padding:4px 11px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">🔒 المرحلة: <strong style="color:#fff !important; font-size:1.1rem; font-family:'Outfit';">${postedCount}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#d97706,#b45309); border:2px solid #fcd34d; color:#fff !important; padding:4px 11px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">⏳ الانتظار: <strong style="color:#fff !important; font-size:1.1rem; font-family:'Outfit';">${pendingCount}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#e11d48,#be123c); border:2px solid #fda4af; color:#fff !important; padding:4px 12px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">💰 الجملة (ج.س): <strong style="color:#fff !important; font-size:1.18rem; font-family:'Outfit';">${fmtNum(totalAmount)}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#0284c7,#0369a1); border:2px solid #7dd3fc; color:#fff !important; padding:4px 12px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">💵 بالدولار ($): <strong style="color:#fff !important; font-size:1.18rem; font-family:'Outfit';">$${totalUsd.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}</strong></span>
                <button type="button" id="hub-fin-curr-toggle-btn" onclick="window.toggleHubSummaryCurrency && window.toggleHubSummaryCurrency()" style="height:32px; padding:0 12px; border-radius:9px; background:linear-gradient(135deg,#7c3aed,#5b21b6); border:2px solid #c4b5fd; color:#fff !important; font-weight:900; font-size:0.84rem; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">💱 ${currBtnLbl}</button>
            `;
        }

        function updateHubPagination(totalPages) {
            let pagEl = document.getElementById('hub-pagination-bar');
            if (!pagEl) return;
            if (totalPages <= 1) { pagEl.style.display = 'none'; return; }
            pagEl.style.display = 'flex';
            pagEl.innerHTML = `
                <button onclick="hubGoToPage(${hubCurrentPage - 1})" ${hubCurrentPage <= 1 ? 'disabled style="opacity:0.3;"' : ''} style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer; font-family:'Tajawal',sans-serif;">→ السابق</button>
                <span style="color:#94a3b8; font-size:0.9rem;">صفحة <strong style="color:#fff;">${hubCurrentPage}</strong> من <strong style="color:#fff;">${totalPages}</strong></span>
                <button onclick="hubGoToPage(${hubCurrentPage + 1})" ${hubCurrentPage >= totalPages ? 'disabled style="opacity:0.3;"' : ''} style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer; font-family:'Tajawal',sans-serif;">التالي ←</button>
            `;
        }

        function hubGoToPage(page) {
            if (page < 1) return;
            hubCurrentPage = page;
            loadHubTransactions();
        }

        function setHubTimePeriod(period) {
            hubTimePeriod = period;
            hubCurrentPage = 1;
            // Update active button
            document.querySelectorAll('.hub-time-btn').forEach(btn => {
                const isActive = btn.dataset.period === period;
                if (isActive) {
                    btn.style.background = 'rgba(56,189,248,0.15)';
                    btn.style.color = '#38bdf8';
                } else {
                    btn.style.background = 'transparent';
                    btn.style.color = '#94a3b8';
                }
            });
            loadHubTransactions();
        }
        
        function filterHubTransactions() {
            hubCurrentPage = 1;
            loadHubTransactions();
        }

        function editHubTransaction(invNo) {
            openPurchaseEditor('direct', true);
            if(typeof loadInvoiceFromStorage === 'function') {
                setTimeout(() => loadInvoiceFromStorage(invNo), 100);
            }
        }
        
        function deleteHubTransaction(invNo) {
            // Check if invoice is posted — must unpost first
            let invStr = localStorage.getItem('erp_pur_' + invNo);
            let inv = null;
            if (invStr) try { inv = JSON.parse(invStr); } catch(e) {}
            if (!inv) {
                let savedData = localStorage.getItem('erp_purchase_invoices_v1');
                if (savedData) {
                    try {
                        let invoices = JSON.parse(savedData);
                        inv = invoices.find(i => String(i.invoiceNo) === String(invNo) || String(i.id) === String(invNo));
                    } catch(e) {}
                }
            }
            
            if (inv && inv.status === 'posted') {
                showToast('⛔ لا يمكن حذف فاتورة مرحلة — يجب عكس الترحيل أولاً', 'error', 4000);
                return;
            }
            // Check if any GRN warehouse receipt is linked to this invoice
            try {
                const grnList = JSON.parse(localStorage.getItem('erp_grn_v1') || '[]');
                const hasLinkedGrn = Array.isArray(grnList) && grnList.some(g => g && !g.deleted && g.status !== 'cancelled' && (String(g.invoiceNo) === String(invNo) || String(g.refInvoice) === String(invNo)));
                if (hasLinkedGrn) {
                    showToast('⛔ لا يمكن حذف فاتورة مرتبطة بإذن استلام مخزني (GRN) معتمد', 'error', 4000);
                    return;
                }
            } catch (_eg) {}

            if (window._pendingHubDeleteInvNo !== String(invNo)) {
                window._pendingHubDeleteInvNo = String(invNo);
                if (typeof showToast === 'function') {
                    showToast('⚠️ اضغط زر الحذف مرة أخرى خلال 3 ثوانٍ لتأكيد حذف الفاتورة ' + invNo, 'warning', 2800);
                }
                clearTimeout(window._pendingHubDeleteTimer);
                window._pendingHubDeleteTimer = setTimeout(() => { window._pendingHubDeleteInvNo = null; }, 3200);
                return;
            }
            window._pendingHubDeleteInvNo = null;

            // Revert accounting effects if invoice was posted (auto mode)
            if (inv && (inv.status === 'posted' || inv.status === 'confirmed')) {
                if (typeof revertOldInvoiceEffects === 'function') {
                    revertOldInvoiceEffects(invNo);
                }
            }
            
            // Remove individual record
            localStorage.removeItem('erp_pur_' + invNo);
            localStorage.removeItem('erp_draft_' + invNo);

            // Remove from global array
            let savedData = localStorage.getItem('erp_purchase_invoices_v1');
            if (savedData) {
                let invoices = JSON.parse(savedData);
                invoices = invoices.filter(i => i.invoiceNo !== invNo);
                localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(invoices));
            }
            window._cachedPurchaseInvoicesRaw = null;
            window._cachedPurchaseList = null;
            try {
                if (typeof window.recordPurchaseAuditFingerprint === 'function') {
                    window.recordPurchaseAuditFingerprint('DELETE_INVOICE', invNo);
                }
                if (typeof BroadcastChannel !== 'undefined') {
                    const bc = new BroadcastChannel('magaiees_sync_channel');
                    bc.postMessage({ type: 'purchase_invoice_deleted', module: 'purchase', invoiceNo: invNo, timestamp: Date.now() });
                    bc.close();
                }
            } catch (_ebc) {}
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true).catch(e => console.warn("Delete push failed:", e));
            }
            
            showToast('🗑️ تم حذف الفاتورة وعكس تأثيراتها بنجاح', 'success');
            loadHubTransactions();
        }

        document.addEventListener('DBReady', () => {
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const actionParam = urlParams.get('action');
                const idParam = urlParams.get('id');
                if (idParam || localStorage.getItem('cfo_order_draft')) {
                    return;
                }
                if (actionParam === 'direct' || actionParam === 'proforma') {
                    openPurchaseEditor(actionParam);
                } else {
                    showPurchaseHub();
                }
            }, 50);
        });

        // Refresh invoice list after cloud sync completes (smart merge may add records)
        window.addEventListener('cloudDataRestored', () => {
            if (currentPurchaseView === 'hub') {
                loadHubTransactions();
            }
        });
