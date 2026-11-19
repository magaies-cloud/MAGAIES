        // Fallback definitions for fmtNum/parseNum in case direct-sales-v3.js hasn't loaded yet
        if (typeof window.fmtNum !== 'function') {
            window.fmtNum = function(val) {
                const n = parseFloat(val) || 0;
                if (n === 0) return '0';
                let decimals = 2;
                try {
                    const stored = localStorage.getItem('erp_grid_customizer_v1');
                    if (stored) {
                        const settings = JSON.parse(stored);
                        if (settings.decimalPlaces !== undefined) decimals = parseInt(settings.decimalPlaces);
                    }
                } catch(e) {}
                return n.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            };
        }
        if (typeof window.parseNum !== 'function') {
            window.parseNum = function(val) {
                if (!val) return 0;
                return parseFloat(String(val).replace(/,/g, '')) || 0;
            };
        }
        // Make them globally accessible for inline HTML onblur handlers
        var fmtNum = window.fmtNum;
        var parseNum = window.parseNum;

        // Sales Hub Logic
        let currentSalesView = 'hub'; // 'hub' or 'editor'
        let hubCurrentPage = 1;
        const HUB_PAGE_SIZE = 50;
        let hubFilteredInvoices = [];

        function handleCurrencyChange(val) {
            const rateInput = document.getElementById('hdr-exchange-rate');
            if (!rateInput) return;
            rateInput.disabled = false;
            const targetCurr = (val === 'SDG' ? 'USD' : val);
            if (targetCurr === 'USD' && window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 1 && rateInput.dataset.userEdited !== '1') {
                rateInput.value = parseFloat(window.SYSTEM_USD_RATE);
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            // Step 1: Try to get rate from local settings function
            let rate = 0;
            if (window.getExchangeRateForCurrency) {
                rate = window.getExchangeRateForCurrency(targetCurr);
            }
            
            if (rate > 0) {
                rateInput.value = rate;
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            
            // Step 2: Try to read directly from localStorage settings (any mode)
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (val === 'USD' && cfg.rateUSD) { rateInput.value = cfg.rateUSD; if (typeof recalcAll === 'function') recalcAll(); return; }
                if (val === 'SAR' && cfg.rateSAR) { rateInput.value = cfg.rateSAR; if (typeof recalcAll === 'function') recalcAll(); return; }
                if (val === 'AED' && cfg.rateAED) { rateInput.value = cfg.rateAED; if (typeof recalcAll === 'function') recalcAll(); return; }
            } catch(e) {}
            
            // Step 3: Try to fetch from API directly (central rate)
            rateInput.value = '';
            rateInput.placeholder = '⏳ جاري التحميل...';
            
            fetch('api_index.php/exchange-rate')
                .then(r => r.json())
                .then(data => {
                    if (data && data.rate) {
                        const usdRate = parseFloat(data.rate);
                        if (usdRate > 0) {
                            window._cachedGlobalRate = usdRate;
                            let finalRate = usdRate;
                            if (val === 'SAR') finalRate = parseFloat((usdRate / 3.75).toFixed(4));
                            if (val === 'AED') finalRate = parseFloat((usdRate / 3.67).toFixed(4));
                            if (val === 'EGP') finalRate = parseFloat((usdRate / 50.0).toFixed(4));
                            
                            rateInput.value = finalRate;
                            if (typeof recalcAll === 'function') recalcAll();
                            return;
                        }
                    }
                    // No rate found from API either
                    rateInput.value = '';
                    rateInput.placeholder = 'أدخل سعر الصرف';
                })
                .catch(() => {
                    rateInput.value = '';
                    rateInput.placeholder = 'أدخل سعر الصرف';
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // No need for auto-init - currency defaults to SDG
            // handleCurrencyChange only needed when user explicitly changes currency
        });

        function showSalesHub() {
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('sales-hub-view').style.display = 'flex';
            currentSalesView = 'hub';
            
            // Restore header title to "المبيعات المباشرة"
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">✂️🚚</span> فاتورة تقسيم وتسليم المبيعات';
            }
            
            loadHubTransactions();
            
            // Adjust header Back button behavior
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { 
                    if (document.referrer && document.referrer.indexOf('general-ledger.php') !== -1) { 
                        history.back(); 
                    } else { 
                        window.location.href = 'dashboard-pro.php'; 
                    } 
                };
                backBtn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>';
            }
        }

        function openInvoiceEditor(type = 'pending_delivery', isEdit = false, isInitialBoot = false) {
            if (isInitialBoot && window._initialEditorBootDone) {
                return;
            }
            if (isInitialBoot) {
                window._initialEditorBootDone = true;
            }
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'flex';
            currentSalesView = 'editor';
            currentSaleType = type;
            
            // Adjust header Back button behavior to return to hub
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { showSalesHub(); };
                backBtn.innerHTML = '<span style="font-weight:bold; font-size:0.8rem; padding: 0 4px;">العودة للقائمة</span>';
                backBtn.style.width = 'auto';
                backBtn.style.padding = '0 10px';
            }

            // Set active tab
            document.querySelectorAll('#sale-type-tabs .action-tab').forEach(t => {
                t.classList.remove('active');
            });
            const activeTab = document.getElementById('tab-' + type);
            if(activeTab) activeTab.classList.add('active');

            // Handle Preliminary UI changes
            const editorView = document.getElementById('invoice-editor-view');
            const whTreasuryRow = document.getElementById('warehouse-treasury-row');
            
            const headerTitle = document.querySelector('.header-page-title');
            if (type === 'preliminary') {
                if (headerTitle) headerTitle.innerHTML = '<span style="color:#c084fc;">📋 فاتورة مبدئية</span> <span style="font-size:0.7em;color:#f87171;">(غير ملزمة)</span>';
                if (editorView) editorView.classList.add('preliminary-active');
                if (whTreasuryRow) whTreasuryRow.style.display = 'none';
            } else {
                if (headerTitle) headerTitle.innerHTML = '<span class="header-page-title-icon">🛒</span> فاتورة مبيعات';
                if (editorView) editorView.classList.remove('preliminary-active');
                if (whTreasuryRow) whTreasuryRow.style.display = 'flex';
            }
            
            // If creating a new invoice, reset the editor form and load the next invoice number
            const alreadyFreshFromBoot = isInitialBoot && window._salesAppInitialized && typeof rows !== 'undefined' && rows.length === 1 && !rows[0].itemId && !currentViewedInvoiceNo;
            if (!isEdit && !alreadyFreshFromBoot && typeof onSuccessNewInvoice === 'function') {
                onSuccessNewInvoice(true);
            } else if (alreadyFreshFromBoot && typeof peekNextInvoiceNumber === 'function') {
                const nextNo = peekNextInvoiceNumber(currentSaleType);
                if (document.getElementById('inv-number')) document.getElementById('inv-number').textContent = nextNo;
                if (document.getElementById('hdr-inv-no')) document.getElementById('hdr-inv-no').value = nextNo;
            }
            
            requestAnimationFrame(() => {
                if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
                if (typeof window.autoFitGridCells === 'function') window.autoFitGridCells();
            });
        }

        window.unifiedHubFilterMode = window.unifiedHubFilterMode || 'all';

        window.setUnifiedHubFilter = function(mode = 'all', scrollSmooth = false) {
            window.unifiedHubFilterMode = mode;
            hubCurrentPage = 1;
            const modes = ['all', 'pending', 'split_only', 'delivered', 'preliminary'];
            modes.forEach(m => {
                const btn = document.getElementById('uhub-tab-' + m);
                if (!btn) return;
                const isActive = (m === mode) || (mode === 'split' && m === 'pending');
                if (isActive) {
                    btn.style.border = '1.5px solid #38bdf8';
                    btn.style.background = 'rgba(56,189,248,0.22)';
                    btn.style.color = '#ffffff';
                } else {
                    btn.style.border = '1px solid rgba(255,255,255,0.12)';
                    btn.style.background = 'rgba(255,255,255,0.04)';
                    btn.style.color = '#cbd5e1';
                }
            });
            showSalesHub(true);
            if (scrollSmooth) {
                const card = document.getElementById('unified-sales-table-card');
                if (card && typeof card.scrollIntoView === 'function') {
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        };

        function openDeliveryHub() {
            window.setUnifiedHubFilter('pending', false);
        }

        function openSplitInvoiceView(showBoardDirectly = true) {
            if (showBoardDirectly) {
                const hubEl = document.getElementById('sales-hub-view');
                const edEl = document.getElementById('invoice-editor-view');
                const delEl = document.getElementById('delivery-hub-view');
                const splEl = document.getElementById('split-invoice-view');
                if (hubEl) hubEl.style.display = 'none';
                if (edEl) edEl.style.display = 'none';
                if (delEl) delEl.style.display = 'none';
                if (splEl) splEl.style.display = 'flex';
                currentSalesView = 'split';
                const headerTitle = document.querySelector('.header-page-title');
                if (headerTitle) {
                    headerTitle.innerHTML = '<span class="header-page-title-icon">✂️</span> تقسيم الفاتورة / الإذن (بالوحدة الأساسية والفرعية)';
                }
                if (!window.activeSplitInvoice) {
                    const selStep = document.getElementById('split-select-step');
                    const brdStep = document.getElementById('split-board-step');
                    if (selStep) selStep.style.display = 'flex';
                    if (brdStep) brdStep.style.display = 'none';
                }
                if (typeof loadSplitInvoices === 'function' && (!window._lastSplitLoadTs || (Date.now() - window._lastSplitLoadTs) > 250)) {
                    window._lastSplitLoadTs = Date.now();
                    loadSplitInvoices(true);
                }
                return;
            }
            window.setUnifiedHubFilter('pending', false);
        }

        function loadHubTransactions() {
            const tbody = document.getElementById('hub-transactions-body');
            const emptyState = document.getElementById('hub-empty-state');
            if (!tbody) return;
            
            // Fetch from localStorage with cache
            window._hubSalesCache = window._hubSalesCache || { raw: null, obj: [] };
            const savedData = localStorage.getItem('erp_sales_invoices_v1');
            let invoices = window._hubSalesCache.obj;
            if (savedData !== null && window._hubSalesCache.raw !== savedData) {
                try { invoices = JSON.parse(savedData) || []; } catch(e) {}
                window._hubSalesCache.raw = savedData;
                window._hubSalesCache.obj = invoices;
            }
            
            // Active (non-deleted, non-archived-split) invoices
            invoices = (invoices || []).filter(inv => !inv.deleted && !inv.wasSplit);

            // Calculate live counters for filter tabs
            let cntAll = invoices.length;
            let cntPending = 0;
            let cntSplit = 0;
            let cntDelivered = 0;
            let cntPrelim = 0;

            invoices.forEach(inv => {
                const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                const isDel = !isPrelim && (inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed');
                const isSplitFam = !!(inv.isSplit || inv.parentInvoice || inv.rootInvoice);
                if (isPrelim) cntPrelim++;
                else if (isDel) cntDelivered++;
                else cntPending++;
                if (isSplitFam) cntSplit++;
            });

            const elAll = document.getElementById('uhub-cnt-all');
            const elPend = document.getElementById('uhub-cnt-pending');
            const elSpl = document.getElementById('uhub-cnt-split');
            const elDel = document.getElementById('uhub-cnt-delivered');
            const elPre = document.getElementById('uhub-cnt-prelim');
            if (elAll) elAll.textContent = cntAll;
            if (elPend) elPend.textContent = cntPending;
            if (elSpl) elSpl.textContent = cntSplit;
            if (elDel) elDel.textContent = cntDelivered;
            if (elPre) elPre.textContent = cntPrelim;

            // Filter by unified tab mode
            const fMode = window.unifiedHubFilterMode || 'all';
            if (fMode === 'pending' || fMode === 'split') {
                invoices = invoices.filter(inv => {
                    const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                    const isDel = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed';
                    return !isPrelim && !isDel;
                });
            } else if (fMode === 'split_only') {
                invoices = invoices.filter(inv => !!(inv.isSplit || inv.parentInvoice || inv.rootInvoice));
            } else if (fMode === 'delivered') {
                invoices = invoices.filter(inv => {
                    const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                    return !isPrelim && (inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed');
                });
            } else if (fMode === 'preliminary') {
                invoices = invoices.filter(inv => inv.saleType === 'preliminary' || inv.status === 'preliminary');
            }
            
            if (!invoices || invoices.length === 0) {
                tbody.innerHTML = '';
                if (emptyState) emptyState.style.display = 'flex';
                const pagEl = document.getElementById('hub-pagination-bar');
                if (pagEl) pagEl.style.display = 'none';
                return;
            }

            // Load customers to map IDs to names
            window._hubCustCache = window._hubCustCache || { raw: null, obj: [] };
            let rawCusts = localStorage.getItem('erp_customers_v1');
            let customers = window._hubCustCache.obj;
            if (rawCusts !== null && window._hubCustCache.raw !== rawCusts) {
                try { customers = typeof rawCusts === 'string' ? JSON.parse(rawCusts) : rawCusts; } catch(e) { customers = []; }
                window._hubCustCache.raw = rawCusts;
                window._hubCustCache.obj = customers || [];
            }

            function getCustomerName(id) {
                if (id === 'walk-in' || !id) return 'عميل نقدي';
                const c = customers.find(x => x && x.id === id);
                return c ? c.name : id;
            }

            // Load warehouses to map IDs to names
            window._hubWhCache = window._hubWhCache || { raw: null, obj: [] };
            let rawWh = localStorage.getItem('erp_warehouses_v1');
            let warehouses = window._hubWhCache.obj;
            if (rawWh !== null && window._hubWhCache.raw !== rawWh) {
                try { warehouses = typeof rawWh === 'string' ? JSON.parse(rawWh) : rawWh; } catch(e) { warehouses = []; }
                window._hubWhCache.raw = rawWh;
                window._hubWhCache.obj = warehouses || [];
            }
            if (!warehouses || warehouses.length === 0) {
                warehouses = [{ id: 1, name: 'المخزن الرئيسي' }];
            }

            function getWarehouseName(id) {
                if (!id) return warehouses[0]?.name || 'المخزن الرئيسي';
                const w = warehouses.find(x => x && (String(x.id) === String(id) || x.name === id));
                return w ? w.name : id;
            }

            // Load items to map IDs to names
            window._hubItemsCache = window._hubItemsCache || { raw: null, obj: [] };
            let rawItems = localStorage.getItem('erp_items_v1');
            let items = window._hubItemsCache.obj;
            if (rawItems !== null && window._hubItemsCache.raw !== rawItems) {
                try { items = typeof rawItems === 'string' ? JSON.parse(rawItems) : rawItems; } catch(e) { items = []; }
                window._hubItemsCache.raw = rawItems;
                window._hubItemsCache.obj = items || [];
            }

            function getItemName(id) {
                const item = items.find(x => x && x.id === id);
                return item ? item.name : id || 'صنف غير معروف';
            }

            // Apply search/filter options in memory
            const query = document.getElementById('hub-search')?.value.trim().toLowerCase() || '';
            if (query) {
                invoices = invoices.filter(inv => {
                    const itemsNames = (inv.items || []).map(it => it.itemName || it.name || getItemName(it.itemId)).join(' ');
                    const text = [
                        inv.invoiceNo,
                        inv.rootInvoice,
                        inv.parentInvoice,
                        inv.deliveryNo,
                        inv.customerName,
                        inv.receiverName,
                        inv.truckPlate,
                        getCustomerName(inv.customer || inv.customerId),
                        itemsNames,
                        inv.date
                    ].join(' ').toLowerCase();
                    return text.includes(query);
                });
            }

            if (invoices.length === 0) {
                tbody.innerHTML = '';
                if (emptyState) emptyState.style.display = 'flex';
                const pagEl = document.getElementById('hub-pagination-bar');
                if (pagEl) pagEl.style.display = 'none';
                return;
            }

            if (emptyState) emptyState.style.display = 'none';
            tbody.innerHTML = '';
            
            // Sort by date/time descending
            invoices.sort((a,b) => {
                const dateA = new Date(a.timestamp || a.date || 0).getTime();
                const dateB = new Date(b.timestamp || b.date || 0).getTime();
                return (isNaN(dateB) ? 0 : dateB) - (isNaN(dateA) ? 0 : dateA);
            });

            // Pagination
            const totalPages = Math.ceil(invoices.length / HUB_PAGE_SIZE);
            if (hubCurrentPage > totalPages) hubCurrentPage = totalPages || 1;
            const startIdx = (hubCurrentPage - 1) * HUB_PAGE_SIZE;
            const pageInvoices = invoices.slice(startIdx, startIdx + HUB_PAGE_SIZE);
            
            // Render pagination bar
            const pagEl = document.getElementById('hub-pagination-bar');
            if (pagEl) {
                if (totalPages <= 1) {
                    pagEl.style.display = 'none';
                } else {
                    pagEl.style.display = 'flex';
                    pagEl.innerHTML = `
                        <button onclick="hubGoToPage(${hubCurrentPage - 1})" ${hubCurrentPage <= 1 ? 'disabled style="opacity:0.3; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); color:#94a3b8; padding:6px 14px; border-radius:8px; cursor:default;"' : 'style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer;"'}>→ السابق</button>
                        <span style="color:#94a3b8; font-size:0.9rem;">صفحة <strong style="color:#fff;">${hubCurrentPage}</strong> من <strong style="color:#fff;">${totalPages}</strong></span>
                        <button onclick="hubGoToPage(${hubCurrentPage + 1})" ${hubCurrentPage >= totalPages ? 'disabled style="opacity:0.3; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); color:#94a3b8; padding:6px 14px; border-radius:8px; cursor:default;"' : 'style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer;"'}>التالي ←</button>
                    `;
                }
            }
            
            const fragment = document.createDocumentFragment();
            pageInvoices.forEach(inv => {
                const tr = document.createElement('tr');
                const invNo = String(inv.invoiceNo || inv.id || '');
                const escHubNo = invNo.replace(/'/g, "\\'");
                const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                const isDelivered = !isPrelim && (inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed');
                const isPartial = !isPrelim && !isDelivered && inv.deliveryStatus === 'partial';
                const isUndelivered = !isPrelim && !isDelivered;
                const isSplitFamily = !!(inv.isSplit || inv.parentInvoice || inv.rootInvoice);

                const defaultWhObj = warehouses.find(w => String(w.id) === String(inv.warehouseId || inv.warehouse || 1) || w.name === (inv.warehouseId || inv.warehouse)) || warehouses[0];
                const whOptions = warehouses.map(w => `<option value="${w.id}" ${String(w.id) === String(defaultWhObj.id) ? 'selected' : ''}>${w.name}</option>`).join('');

                const lines = Array.isArray(inv.items) ? inv.items : [];
                let totalInvRemQty = 0;
                let totalInvRemSubQty = 0;
                let hasAnyMultiUnit = false;

                const itemsPillsHtml = lines.map((line, itemIdx) => {
                    const uMeta = (typeof window.resolveItemMeta === 'function') ? window.resolveItemMeta(line, defaultWhObj) : null;
                    const iName = uMeta ? uMeta.name : (line.itemName || line.name || getItemName(line.itemId));
                    const pUnit = uMeta ? uMeta.unit : (line.selectedUnit || line.unit || 'وحدة');
                    const bUnit = uMeta ? uMeta.baseUnit : 'وحدة';
                    const factor = uMeta ? (uMeta.factor || 1) : 1;
                    if (factor > 1) hasAnyMultiUnit = true;
                    const qVal = parseFloat(line.quantity ?? line.qty ?? 0) || 0;
                    const delQ = parseFloat(line.deliveredQty || 0) || 0;
                    const remQ = isDelivered ? qVal : Math.max(0, +(qVal - delQ).toFixed(3));
                    const remSub = +(remQ * factor).toFixed(2);
                    totalInvRemQty += remQ;
                    totalInvRemSubQty += remSub;

                    return `
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; flex-wrap:wrap; background:rgba(15,23,42,0.75); border:1px solid rgba(56,189,248,0.22); padding:5px 8px; border-radius:9px; margin-bottom:4px;">
                            <div style="display:flex; align-items:center; gap:6px; flex:1; min-width:140px;">
                                <span style="background:rgba(56,189,248,0.18); color:#38bdf8; width:20px; height:20px; border-radius:5px; font-size:0.72rem; font-weight:900; display:inline-flex; align-items:center; justify-content:center;">${itemIdx + 1}</span>
                                <span style="font-weight:900; color:#f8fafc; font-size:0.88rem;">${iName}</span>
                                ${factor > 1 ? `<span style="font-size:0.7rem; color:#38bdf8; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); padding:1px 6px; border-radius:5px; font-weight:800;">⚖️ 1 ${pUnit} = ${factor} ${bUnit}</span>` : ''}
                            </div>
                            <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                                <button type="button" onclick="${isUndelivered ? `window.openDeliveryForInvoiceFromHub('${escHubNo}', ${itemIdx}, 'primary')` : ''}" style="background:linear-gradient(135deg, #10b981, #059669); color:#ffffff; border:1px solid #34d399; padding:3px 9px; border-radius:6px; font-size:0.78rem; font-weight:900; cursor:${isUndelivered ? 'pointer' : 'default'}; display:inline-flex; align-items:center; gap:4px;" title="اضغط للتسليم بالوحدة الأساسية (${pUnit})">
                                    📦 ${fmtNum(remQ)} ${pUnit}
                                </button>
                                ${factor > 1 ? `
                                <button type="button" onclick="${isUndelivered ? `window.openDeliveryForInvoiceFromHub('${escHubNo}', ${itemIdx}, 'base')` : ''}" style="background:rgba(99,102,241,0.22); color:#c4b5fd; border:1px solid rgba(129,140,248,0.5); padding:3px 9px; border-radius:6px; font-size:0.76rem; font-weight:900; cursor:${isUndelivered ? 'pointer' : 'default'}; display:inline-flex; align-items:center; gap:4px;" title="اضغط للتسليم بالوحدة الفرعية (${bUnit})">
                                    🔢 ⇄ ${fmtNum(remSub)} ${bUnit}
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }).join('');

                const statusBadge = isPrelim
                    ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(168,85,247,0.16); border:1px solid rgba(192,132,252,0.4); color:#e9d5ff; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">📋 عرض مبدئي</span>`
                    : (isDelivered
                        ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.35); color:#6ee7b7; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">✅ تم الصرف (${inv.deliveryNo || 'GDN'})</span>`
                        : (isPartial
                            ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(245,158,11,0.16); border:1px solid rgba(251,191,36,0.35); color:#fcd34d; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">🌗 تسليم جزئي</span>`
                            : `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(249,115,22,0.16); border:1px solid rgba(251,146,60,0.35); color:#fdba74; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">⏳ بانتظار التسليم</span>`));

                const splitBadgeHtml = (typeof window.renderSplitGenealogyBadge === 'function') ? window.renderSplitGenealogyBadge(inv, true) : '';
                const serialColor = isPrelim ? '#c084fc' : '#38bdf8';

                let isTargetDate = false;
                if (inv.date === '2026-06-27' || (inv.timestamp && inv.timestamp.startsWith('2026-06-27'))) {
                    isTargetDate = true;
                }
                const numColor = isTargetDate ? '#ef4444' : '#10b981';

                let actionBtns = `
                    <div style="display:inline-flex; align-items:center; gap:5px; flex-wrap:wrap; justify-content:center;">
                        ${isUndelivered ? `
                            <button onclick="window.openDeliveryForInvoiceFromHub('${escHubNo}', 0, 'primary')" class="kinetic-glow-arm kinetic-arm-emerald" style="border-radius:8px; padding:0 11px; height:34px; cursor:pointer; font-size:0.8rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="تسليم وصرف البضاعة (بالوحدة الأساسية أو الفرعية)">📦 تسليم</button>
                            <button onclick="window.openSplitForInvoiceFromDelivery('${escHubNo}')" class="kinetic-glow-arm kinetic-arm-pink" style="border-radius:8px; padding:0 11px; height:34px; cursor:pointer; font-size:0.8rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="تقسيم الفاتورة / الإذن لجزئين (100/17/1 و 100/17/2) بالوحدة الأساسية أو الفرعية">✂️ تقسيم</button>
                            <button onclick="executeInstantOneClickDelivery('${escHubNo}')" style="background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.4); color:#6ee7b7; border-radius:8px; padding:0 9px; height:34px; cursor:pointer; font-size:0.76rem; font-weight:900; display:inline-flex; align-items:center; gap:3px;" title="صرف مخزني فوري بكامل الكمية">⚡ فوري</button>
                        ` : (!isPrelim ? `
                            <button onclick="window.openDeliveryForInvoiceFromHub('${escHubNo}', 0, 'primary')" style="background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; border-radius:8px; padding:0 10px; height:34px; cursor:pointer; font-size:0.78rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="عرض إذن الصرف">👁️ الإذن</button>
                            <button onclick="undoGdnDelivery('${escHubNo}')" style="background:rgba(239,68,68,0.14); border:1px solid rgba(239,68,68,0.35); color:#fca5a5; border-radius:8px; padding:0 9px; height:34px; cursor:pointer; font-size:0.76rem; font-weight:900; display:inline-flex; align-items:center; gap:3px;" title="تراجع عن التسليم وإعادة الكمية للمخزن">↩️ تراجع</button>
                        ` : '')}
                        ${isSplitFamily ? `
                            <button onclick="if(typeof window.printSplitFamilyVouchers==='function') window.printSplitFamilyVouchers('${escHubNo}')" class="kinetic-glow-arm kinetic-arm-cyan" style="border-radius:8px; padding:0 9px; height:34px; cursor:pointer; font-size:0.76rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="طباعة الجزئين / جميع الأذونات المقسمة">🖨️ الجزئين</button>
                            <button onclick="if(typeof window.openSplitGenealogyModal==='function') window.openSplitGenealogyModal('${escHubNo}')" style="background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.38); color:#38bdf8; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="شجرة التقسيم والنسب">🌳</button>
                        ` : ''}
                        ${!isPrelim ? `
                            <button onclick="showInvoiceDeliveryQrModal('${escHubNo}')" style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="باركود QR للتسليم">📱</button>
                            <button onclick="printOfficialGdnGatePass('${escHubNo}')" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#f8fafc; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="طباعة إذن الصرف">🖨️</button>
                        ` : ''}
                        <button onclick="editHubTransaction('${escHubNo}')" style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض الفاتورة">👁️</button>
                        <button onclick="deleteHubTransaction('${escHubNo}')" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
                    </div>
                `;

                if (isPrelim) {
                    actionBtns = `
                        <button onclick="convertToOfficial('${escHubNo}')" style="background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.4); color:#10b981; border-radius:7px; padding:0 10px; height:34px; cursor:pointer; margin-left:4px; font-weight:900; font-size:0.78rem; display:inline-flex; align-items:center; gap:4px;" title="تحويل لفاتورة رسمية">✅ اعتماد رسمية</button>
                    ` + actionBtns;
                }

                const custDisplayName = inv.customerName || getCustomerName(inv.customer || inv.customerId) || 'عميل نقدي';
                const payBadge = inv.paymentMethod === 'credit'
                    ? `<span style="background:rgba(245,158,11,0.16); border:1px solid rgba(245,158,11,0.35); color:#fcd34d; padding:1px 7px; border-radius:6px; font-size:0.72rem; font-weight:800;">✍️ آجل</span>`
                    : `<span style="background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.35); color:#6ee7b7; padding:1px 7px; border-radius:6px; font-size:0.72rem; font-weight:800;">💵 نقدي</span>`;

                tr.innerHTML = `
                    <td style="text-align:right; font-weight:800; color:${serialColor};">
                        <div style="font-family:'Outfit',monospace; font-size:1.05rem; font-weight:900;">#${invNo}</div>
                        ${splitBadgeHtml ? `<div style="margin-top:3px;">${splitBadgeHtml}</div>` : ''}
                        <div style="margin-top:4px;">${statusBadge}</div>
                    </td>
                    <td style="text-align:center; color:var(--text-dim); font-size:0.84rem; font-weight:700;">
                        ${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG-u-nu-latn')}
                    </td>
                    <td style="text-align:right;">
                        <div style="font-weight:900; color:#f8fafc; font-size:0.94rem;">👤 ${custDisplayName}</div>
                        <div style="margin-top:3px; display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                            ${payBadge}
                            ${inv.receiverName ? `<span style="font-size:0.73rem; color:#34d399; font-weight:700;">المستلم: ${inv.receiverName}</span>` : ''}
                        </div>
                    </td>
                    <td style="text-align:right;">
                        ${itemsPillsHtml || '<span style="color:#94a3b8; font-size:0.8rem;">بدون أصناف</span>'}
                    </td>
                    <td style="text-align:center;">
                        <div style="font-weight:800; color:#e2e8f0; font-size:0.82rem; margin-bottom:4px;">
                            📦 <strong style="color:#34d399;">${fmtNum(totalInvRemQty)}</strong>
                            ${hasAnyMultiUnit ? `<div style="font-size:0.74rem; color:#c4b5fd;">(🔢 ⇄ ${fmtNum(totalInvRemSubQty)} فرعي)</div>` : ''}
                        </div>
                        <select id="del-wh-${invNo}" class="gdn-borderless-select" style="width:100%; font-size:0.78rem; padding:4px 6px;" ${isDelivered ? 'disabled' : ''}>
                            ${whOptions}
                        </select>
                    </td>
                    <td style="text-align:center; font-weight:900; direction:ltr; color:${numColor}; font-family:'Outfit',monospace; font-size:1.05rem;">
                        ${fmtNum(inv.totals?.grandTotal || inv.grandTotal || inv.total || 0)} <span style="color:#a855f7; font-weight:700; font-size:0.78rem;">${inv.currency || 'SDG'}</span>
                    </td>
                    <td style="text-align:center;">
                        ${actionBtns}
                    </td>
                `;
                fragment.appendChild(tr);
            });
            tbody.appendChild(fragment);
        }
        
        function hubGoToPage(page) {
            if (page < 1) return;
            hubCurrentPage = page;
            loadHubTransactions();
        }
        
        function filterHubTransactions() {
            hubCurrentPage = 1;
            loadHubTransactions();
        }

        function editHubTransaction(invNo) {
            try {
                let allSales = [];
                try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []; } catch(e) {}
                let inv = allSales.find(i => (i.invoiceNo || i.id) === invNo);
                if (!inv) {
                    let str = localStorage.getItem('erp_inv_' + invNo) || localStorage.getItem('erp_draft_' + invNo);
                    if (str) try { inv = JSON.parse(str); } catch(e) {}
                }

                const isPosted = !!(inv && (inv.status === 'posted' || inv.effectsApplied));
                let wantsToEdit = false;

                if (isPosted) {
                    // Posted invoice: STRICT READ-ONLY. No edits allowed under any circumstances!
                    wantsToEdit = false;
                    showToast('🔒 الفاتورة مرحلة محاسبياً في دفتر الأستاذ - وضع المعاينة فقط (ممنوع التعديل نهائياً)', 'warning', 6000);
                } else if (inv) {
                    // Saved unposted invoice: Ask user if they want to edit
                    wantsToEdit = confirm('⚠️ تنبيه:\n\nهذه الفاتورة (رقم: ' + invNo + ') تم حفظها مسبقاً في النظام.\n\nهل تريد تعديل بيانات هذه الفاتورة؟\n\n- اضغط [موافق / OK] لفتح الفاتورة وتعديلها\n- اضغط [إلغاء / Cancel] للمعاينة والقراءة فقط بدون تعديل');
                    if (!wantsToEdit) {
                        showToast('👁️ وضع المعاينة فقط - الحقول مغلقة ضد التعديل', 'info', 4000);
                    }
                }

                openInvoiceEditor(undefined, true);
                if(typeof loadInvoiceFromStorage === 'function') {
                    setTimeout(() => {
                        try {
                            const success = loadInvoiceFromStorage(invNo, {
                                isLocked: isPosted ? true : (!wantsToEdit),
                                isPosted: isPosted
                            });
                            if (!success) {
                                showToast('❌ فشل تحميل بيانات الفاتورة من التخزين', 'error');
                            }
                        } catch (innerErr) {
                            console.error(innerErr);
                            alert('خطأ أثناء تحميل تفاصيل الفاتورة: ' + innerErr.message + '\n' + innerErr.stack);
                        }
                    }, 100);
                } else {
                    alert('خطأ: لم يتم العثور على دالة تحميل الفاتورة!');
                }
            } catch (err) {
                console.error(err);
                alert('خطأ في محرر الفواتير: ' + err.message);
            }
        }
        let currentDeleteInvoiceNo = null;

        function closeInvoiceDeleteModal() {
            const modal = document.getElementById('invoice-delete-modal');
            if (modal) modal.style.display = 'none';
            currentDeleteInvoiceNo = null;
        }

        async function deleteHubTransaction(invNo) {
            currentDeleteInvoiceNo = invNo;
            const modal = document.getElementById('invoice-delete-modal');
            
            // Set loading state
            document.getElementById('inv-del-target-no').innerText = invNo;
            
            const iconWrap = document.getElementById('inv-del-modal-icon-wrap');
            const iconEl = document.getElementById('inv-del-modal-icon');
            const titleEl = document.getElementById('inv-del-modal-title');
            const subtitleEl = document.getElementById('inv-del-modal-subtitle');
            const statusBox = document.getElementById('inv-del-status-box');
            const vouchersContainer = document.getElementById('inv-del-vouchers-container');
            const footerEl = document.getElementById('inv-del-modal-footer');

            iconWrap.style.background = 'rgba(56,189,248,0.15)';
            iconWrap.style.borderColor = 'rgba(56,189,248,0.3)';
            iconWrap.style.boxShadow = '0 0 20px rgba(56,189,248,0.2)';
            iconEl.innerText = '🔍';
            
            titleEl.innerText = 'جاري فحص ارتباطات الفاتورة...';
            titleEl.style.color = '#fff';
            subtitleEl.innerText = 'يتم التحقق من الدفعات والقيود المحاسبية';
            
            statusBox.style.background = 'rgba(56,189,248,0.08)';
            statusBox.style.borderColor = 'rgba(56,189,248,0.2)';
            statusBox.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:center; gap:12px; color:#38bdf8; font-weight:800; font-size:1.1rem;">
                    <span style="animation:spin 1s infinite linear;">⏳</span>
                    <span>جاري فحص قاعدة البيانات والسجلات...</span>
                </div>
            `;
            
            vouchersContainer.style.display = 'none';
            footerEl.innerHTML = `
                <button type="button" onclick="closeInvoiceDeleteModal()" class="btn-action" style="padding:8px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; cursor:pointer;">إلغاء</button>
            `;

            modal.style.display = 'flex';

            // Local Usage Check (Receipts, Delivery Notes, Cheques, Journal, Stock)
            let localUsages = [];
            try {
                // 1. Check single receipts
                const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                sReceipts.forEach(sr => {
                    if (sr && (sr.againstInvoice == invNo || sr.invoiceNo == invNo)) {
                        localUsages.push({ type: 'سند قبض مالي', doc_no: sr.id || sr.voucherNo, label: 'سند قبض مالي رقم ' + (sr.id || sr.voucherNo) + (sr.amount ? ' بمبلغ ' + sr.amount : '') });
                    }
                });
                // 2. Check multi receipts
                const mReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
                mReceipts.forEach(mr => {
                    if (mr && mr.invoices && Array.isArray(mr.invoices)) {
                        if (mr.invoices.some(i => (i.invoiceNo || i.id) == invNo)) {
                            localUsages.push({ type: 'سند قبض مجمع', doc_no: mr.id || mr.receiptNo, label: 'سند قبض مجمع رقم ' + (mr.id || mr.receiptNo) });
                        }
                    }
                });
                // 3. Check delivery notes / warehouse dispatches
                const dNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1')) || [];
                dNotes.forEach(dn => {
                    if (dn && (dn.invoiceNo == invNo || dn.sourceDocNo == invNo)) {
                        localUsages.push({ type: 'إذن صرف / تسليم مخزني', doc_no: dn.noteNo || dn.id, label: 'سند تسليم بضاعة من المخزن رقم ' + (dn.noteNo || dn.id) });
                    }
                });
                // 4. Check cheques
                const cheques = JSON.parse(localStorage.getItem('erp_cheques_v1')) || [];
                cheques.forEach(ch => {
                    if (ch && (ch.invoiceNo == invNo || ch.refNo == invNo || ch.againstInvoice == invNo)) {
                        localUsages.push({ type: 'شيك بنكي مرتبط', doc_no: ch.chequeNo || ch.id, label: 'شيك بنكي رقم ' + (ch.chequeNo || ch.id) });
                    }
                });
                // 5. Check journal vouchers
                const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
                jvs.forEach(jv => {
                    if (jv && (jv.invoiceNo == invNo || jv.reference == invNo || jv.refNo == invNo)) {
                        localUsages.push({ type: 'قيد محاسبي مرحل', doc_no: jv.id || jv.voucherNo, label: 'قيد يومية عامة رقم ' + (jv.id || jv.voucherNo) });
                    }
                });
                // 6. Check invoice posting & warehouse status
                const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
                const targetInv = allSales.find(inv => (inv.invoiceNo || inv.id) == invNo);
                if (targetInv && (targetInv.status === 'posted' || targetInv.status === 'confirmed' || targetInv.effectsApplied)) {
                    localUsages.push({ type: 'حركة مخزنية ومالية معتمدة', doc_no: invNo, label: 'الفاتورة معتمدة ومؤثرة على أرصدة المخزون والحسابات' });
                }
            } catch(e) {
                console.warn('Local usage check error:', e);
            }

            // Backend Check
            let backendUsages = [];
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                const res = await fetch('api/check_invoice_usage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': token ? ('Bearer ' + token) : '' },
                    body: JSON.stringify({ invoiceNo: invNo })
                });
                const resJson = await res.json();
                if (resJson && resJson.success && resJson.is_used) {
                    backendUsages = resJson.usages || [];
                }
            } catch(e) {
                console.warn('Backend usage check error:', e);
            }

            // Merge unique usages
            const allUsagesMap = new Map();
            [...localUsages, ...backendUsages].forEach(u => {
                const key = (u.type || '') + '_' + (u.doc_no || '');
                if (!allUsagesMap.has(key)) allUsagesMap.set(key, u);
            });
            const allUsages = Array.from(allUsagesMap.values());
            const isUsed = allUsages.length > 0;

            // Render Result
            if (isUsed) {
                iconWrap.style.background = 'rgba(239, 68, 68, 0.18)';
                iconWrap.style.borderColor = 'rgba(239, 68, 68, 0.45)';
                iconWrap.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.35)';
                iconEl.innerText = '🚫';

                titleEl.innerText = 'لا يمكن حذف هذه الفاتورة نهائياً';
                titleEl.style.color = '#f87171';
                subtitleEl.innerText = 'مرتبطة بحركات مخزنية أو سندات ومبالغ مالية';

                statusBox.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(185, 28, 28, 0.08))';
                statusBox.style.borderColor = 'rgba(239, 68, 68, 0.35)';
                statusBox.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; align-items:center; gap:8px; color:#fca5a5; font-weight:900; font-size:1.1rem;">
                            <span>⚠️</span>
                            <span>تنبيه نظام الضبط المخزني والمالي:</span>
                        </div>
                        <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                            لا يمكن حذف الفاتورة <span style="color:#38bdf8; font-weight:800;">${invNo}</span> نسبةً لوجود عدد <span style="color:#f87171; font-weight:900; font-size:1.15rem; background:rgba(239,68,68,0.2); padding:1px 8px; border-radius:6px;">${allUsages.length}</span> حركة مخزنية أو سند مالي مرتبط بها.
                        </p>
                        <div style="background:rgba(0,0,0,0.3); border-radius:10px; padding:12px 14px; margin-top:4px; border-right:3px solid #ef4444;">
                            <span style="color:#cbd5e1; font-size:0.88rem; font-weight:700; line-height:1.6; display:block;">
                                🔒 <strong>حماية البيانات:</strong> تم صرف بضاعة أو تسجيل دفعات مالية/قيود على هذه الفاتورة. لإلغائها، يجب أولاً إلغاء الحركات المخزنية وسندات القبض/الدفع المرتبطة بها.
                            </span>
                        </div>
                    </div>
                `;

                document.getElementById('inv-del-vouchers-count').innerText = `${allUsages.length} حركة / سند`;
                document.getElementById('inv-del-vouchers-list').innerHTML = allUsages.map((u, idx) => `
                    <div style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:8px 12px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="color:#f87171; font-weight:bold; font-size:0.85rem;">${idx + 1}.</span>
                            <span style="color:#e2e8f0; font-weight:700; font-size:0.9rem;">${u.label || (u.type + ' رقم ' + u.doc_no)}</span>
                        </div>
                        <span style="background:rgba(239,68,68,0.15); color:#fca5a5; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:4px;">${u.type}</span>
                    </div>
                `).join('');
                vouchersContainer.style.display = 'block';

                footerEl.innerHTML = `
                    <button type="button" onclick="closeInvoiceDeleteModal()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #3b82f6, #1d4ed8); border:none; color:#fff; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(59,130,246,0.4);">حسناً، فهمت</button>
                `;
            } else {
                iconWrap.style.background = 'rgba(245, 158, 11, 0.18)';
                iconWrap.style.borderColor = 'rgba(245, 158, 11, 0.45)';
                iconWrap.style.boxShadow = '0 0 25px rgba(245, 158, 11, 0.35)';
                iconEl.innerText = '⚠️';

                titleEl.innerText = 'تأكيد حذف الفاتورة نهائياً';
                titleEl.style.color = '#fbbf24';
                subtitleEl.innerText = 'الفاتورة غير مرتبطة بأي سندات';

                statusBox.style.background = 'linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(239, 68, 68, 0.06))';
                statusBox.style.borderColor = 'rgba(245, 158, 11, 0.3)';
                statusBox.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; align-items:center; gap:8px; color:#34d399; font-weight:800; font-size:1rem;">
                            <span>✅</span>
                            <span>تم التحقق: هذه الفاتورة غير مرتبطة بأي دفعات أو سندات صرف أو قيود.</span>
                        </div>
                        <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                            هل أنت متأكد من رغبتك في حذف الفاتورة (<span style="color:#38bdf8; font-weight:800;">${invNo}</span>)؟
                        </p>
                        <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.35); border-radius:10px; padding:12px 14px; margin-top:4px;">
                            <div style="display:flex; align-items:center; gap:8px; color:#f87171; font-weight:900; font-size:0.95rem; margin-bottom:4px;">
                                <span>🚨</span>
                                <span>تحذير نهائي:</span>
                            </div>
                            <span style="color:#fecaca; font-size:0.88rem; font-weight:700; line-height:1.5; display:block;">
                                سيتم حذف الفاتورة <strong>نهائياً وبشكل دائم من النظام</strong> ولن يمكن استرجاعها.
                            </span>
                        </div>
                    </div>
                `;

                vouchersContainer.style.display = 'none';

                footerEl.innerHTML = `
                    <button type="button" onclick="closeInvoiceDeleteModal()" class="btn-action" style="padding:10px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:800; cursor:pointer;">إلغاء الأمر</button>
                    <button type="button" onclick="confirmExecuteDeleteInvoice()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #ef4444, #dc2626); border:1px solid rgba(239,68,68,0.5); color:#fff; font-weight:900; font-size:1rem; cursor:pointer; box-shadow:0 6px 20px rgba(239,68,68,0.4);">🗑️ نعم، احذف نهائياً</button>
                `;
            }
        }

        async function confirmExecuteDeleteInvoice() {
            if (!currentDeleteInvoiceNo) return;
            const invNo = currentDeleteInvoiceNo;
            
            // Delete from Database if API exists for it (usually handled by save_sales_invoice.php with action=delete or similar).
            // Currently the system uses localStorage mostly, but we'll try to sync deletion to DB if there's an endpoint.
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                // Since this system uses save_sales_invoice.php or generic save APIs, let's just mark it soft deleted or hard deleted
                // The current code just deletes it from localStorage. Let's do that + DB if possible.
                await fetch('api_index.php?route=sales', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': token ? ('Bearer ' + token) : '' },
                    body: JSON.stringify({ action: 'delete', invoiceNo: invNo })
                });
            } catch(e) { console.warn('DB delete sync failed:', e); }

            // Delete from LocalStorage
            let savedData = localStorage.getItem('erp_sales_invoices_v1');
            if (savedData) {
                let invoices = JSON.parse(savedData);
                invoices = invoices.filter(i => i.invoiceNo !== invNo);
                localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(invoices));
            }
            
            closeInvoiceDeleteModal();
            showToast('تم حذف الفاتورة نهائياً بنجاح', 'success');
            loadHubTransactions();
        }

        // Initialize view on load (Respects ?action=new & ?id=... URL parameters)
        document.addEventListener('DBReady', () => {
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const actionParam = urlParams.get('action');
                const idParam = urlParams.get('id');
                if (idParam) {
                    return;
                }
                if (actionParam === 'split') {
                    openSplitInvoiceView(true);
                    if (typeof loadSplitInvoices === 'function') loadSplitInvoices(true);
                } else if (actionParam === 'new' || actionParam === 'direct' || actionParam === 'pending_delivery' || actionParam === 'preliminary' || actionParam === 'proforma') {
                    openInvoiceEditor(actionParam === 'new' || actionParam === 'direct' ? 'pending_delivery' : (actionParam === 'proforma' ? 'preliminary' : actionParam), false, true);
                } else {
                    showSalesHub();
                }
            }, 10);
        });

        // ═══════════════════════════════════════
        //  SHARE MENU TOGGLE
        // ═══════════════════════════════════════
        let shareMenuOpen = false;

        function toggleShareMenu(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('share-popup-menu');
            if (!menu) return;
            
            if (shareMenuOpen) {
                closeShareMenu();
            } else {
                if (menu.parentElement !== document.body) {
                    document.body.appendChild(menu);
                    menu.style.position = 'fixed';
                    menu.style.zIndex = '999999999';
                }
                const btn = document.getElementById('btn-share-invoice');
                if (btn) {
                    const rect = btn.getBoundingClientRect();
                    menu.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
                    menu.style.left = (rect.left + rect.width / 2) + 'px';
                }
                menu.style.display = 'block';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        menu.style.opacity = '1';
                        menu.style.transform = 'translateX(-50%) scale(1)';
                    });
                });
                shareMenuOpen = true;
            }
        }

        function closeShareMenu() {
            const menu = document.getElementById('share-popup-menu');
            if (!menu) return;
            menu.style.opacity = '0';
            menu.style.transform = 'translateX(-50%) scale(0.9)';
            setTimeout(() => {
                menu.style.display = 'none';
            }, 250);
            shareMenuOpen = false;
        }

        // Close share menu on outside click
        document.addEventListener('click', function(e) {
            if (shareMenuOpen) {
                const wrapper = document.getElementById('share-btn-wrapper');
                const menu = document.getElementById('share-popup-menu');
                if ((!wrapper || !wrapper.contains(e.target)) && (!menu || !menu.contains(e.target))) {
                    closeShareMenu();
                }
            }
        });

        async function sendCurrentInvoiceWA() {
            let invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : (window.currentViewedInvoiceNo || window._lastSubmittedSalesInvoiceNo || document.getElementById('hdr-inv-no')?.value);
            if (invNo && invNo !== '---' && invNo !== 'SAL-NEW' && invNo !== 'جديد') {
                if (typeof sendInvoiceWA === 'function') {
                    await sendInvoiceWA(invNo);
                } else if (typeof openSalesWhatsAppModal === 'function') {
                    openSalesWhatsAppModal(invNo);
                }
            } else {
                showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
            }
        }

        async function sendCurrentInvoiceEmail() {
            let invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : (window.currentViewedInvoiceNo || window._lastSubmittedSalesInvoiceNo || document.getElementById('hdr-inv-no')?.value);
            if (invNo && invNo !== '---' && invNo !== 'SAL-NEW' && invNo !== 'جديد') {
                showToast('جاري تجهيز الفاتورة للإرسال عبر البريد الإلكتروني...', 'success');
                const mailtoLink = `mailto:?subject=فاتورة مبيعات رقم ${invNo}&body=مرحباً، مرفق تفاصيل الفاتورة.`;
                window.location.href = mailtoLink;
            } else {
                showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
            }
        }
