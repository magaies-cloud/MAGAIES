        var allPurchases = [];
        var itemsDb = [];
        var suppliersDb = [];
        var warehousesDb = [];
        var usersDb = [];
        var pendingMap = {}; // originalInvoiceId -> array of pending items
        var currentInvoiceId = null;
        var receiptLines = [];
        var editingGRNId = null; // Currently edited GRN ID

        // Formatting utilities
        function fmtNum(n) { return Number(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2}); }
        function parseNum(str) { if(!str) return 0; return parseFloat(String(str).replace(/,/g, '')) || 0; }
        function resolveGrnElsajanaSteelFactor(unitName, curFactor) {
            const u = String(unitName || '').trim();
            if (u === 'طن عددي' || u.includes('طن عددي')) return 135;
            if (u === 'طن وزني' || u.includes('طن وزني') || u === 'طن') return (curFactor && curFactor > 1 && curFactor !== 166) ? curFactor : 140;
            if (u === 'شيلة' || u.includes('شيلة')) return 15;
            return (curFactor && curFactor > 0) ? curFactor : 1;
        }
        function showToast(msg, type='info') {
            if (window.ElsajanaAccountingBrain && typeof window.ElsajanaAccountingBrain.floatingToast === 'function') {
                return window.ElsajanaAccountingBrain.floatingToast(msg, type, 3800);
            }
            const container = document.getElementById('toast-container');
            const t = document.createElement('div');
            t.style.padding = '12px 20px'; t.style.borderRadius = '8px'; t.style.color = '#fff'; t.style.fontSize = '0.9rem'; t.style.fontWeight = '700'; t.style.boxShadow = '0 5px 15px rgba(0,0,0,0.4)'; t.style.animation = 'slideDown 0.3s ease forwards';
            if(type === 'error') t.style.background = 'rgba(239, 68, 68, 0.9)';
            else if(type === 'success') t.style.background = 'rgba(16, 185, 129, 0.9)';
            else t.style.background = 'rgba(56, 189, 248, 0.9)';
            t.textContent = msg;
            container.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; setTimeout(()=>t.remove(), 300); }, 3000);
        }

        // --- Pagination Logic ---
        let pendingDisplayCount = 50;
        let receivedDisplayCount = 50;

        window.loadMorePending = function() {
            pendingDisplayCount += 50;
            renderPendingInvoices(false);
        };

        window.loadMoreReceived = function() {
            receivedDisplayCount += 50;
            renderRecentGRNs(false);
        };

        let currentHubView = 'pending';
        function toggleHubViewBySwitch(isReceivedView) {
            if (isReceivedView) {
                currentHubView = 'received';
                document.getElementById('hub-pending-container').style.display = 'none';
                document.getElementById('hub-received-container').style.display = 'block';
                renderRecentGRNs();
            } else {
                currentHubView = 'pending';
                document.getElementById('hub-pending-container').style.display = 'block';
                document.getElementById('hub-received-container').style.display = 'none';
                renderPendingInvoices();
            }
        }


        
        async function loadGridPrefsFromDB() {
            if (!navigator.onLine) return;
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                const uid = String(u.id || u.username || 'default_user');
                const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                const tId = ctrl ? setTimeout(() => ctrl.abort(), 4000) : null;
                const res = await fetch('api/get_user_settings.php?user_id=' + encodeURIComponent(uid), {
                    signal: ctrl ? ctrl.signal : undefined
                });
                if (tId) clearTimeout(tId);
                const data = await res.json();
                const prefsData = (data && data.status === 'success' && data.data && data.data['erp_grn_col_prefs_v2'])
                    ? data.data['erp_grn_col_prefs_v2']
                    : null;

                if (prefsData) {
                    const prefsStr = typeof prefsData === 'string' ? prefsData : JSON.stringify(prefsData);
                    localStorage.setItem('erp_grn_col_prefs_v2', prefsStr);
                    if (document.getElementById('view-editor') && document.getElementById('view-editor').classList.contains('active')) {
                        renderReceiptLines();
                    }
                }
            } catch(e) {}
        }

        function initData() {
            loadGridPrefsFromDB();

            function loadKey(key) {
                let raw = localStorage.getItem(key);
                if (typeof raw === 'string') {
                    try { return JSON.parse(raw) || []; } catch(e) { return []; }
                } else if (raw && typeof raw === 'object') {
                    return raw;
                }
                return [];
            }
            const mergedPurMap = new Map();
            ['erp_purchase_invoices_v1', 'erp_purchases_v5', 'magaies_local_purchase_invoices_v1'].forEach(k => {
                (loadKey(k) || []).forEach(p => {
                    if (!p || typeof p !== 'object') return;
                    const pid = String(p.id || p.invoiceNo || '').trim();
                    if (!pid) return;
                    mergedPurMap.set(pid, mergedPurMap.has(pid) ? Object.assign({}, mergedPurMap.get(pid), p) : p);
                });
            });
            allPurchases = Array.from(mergedPurMap.values());
            itemsDb = loadKey('erp_items_v1');
            suppliersDb = loadKey('erp_suppliers_v1');
            if (!Array.isArray(suppliersDb)) suppliersDb = [];
            warehousesDb = loadKey('erp_warehouses_v1');
            usersDb = loadKey('erp_users_v1');
            if(warehousesDb.length === 0) warehousesDb = [{ id:'w-main', name:'المخزن الرئيسي' }];

            // Auto-migrate and fix any old GRN sequences or duplicates
            if (!localStorage.getItem('grns_renumbered_clean_z2')) {
                let allGrns = allPurchases.filter(p => p.isGRN);
                if (allGrns.length > 0) {
                    allGrns.sort((a,b) => new Date(a.date).getTime() - new Date(b.date).getTime());
                    let c = {};
                    let changed = false;
                    allGrns.forEach(grn => {
                        const d = new Date(grn.date);
                        if(isNaN(d.getTime())) return;
                        const yy = d.getFullYear().toString().slice(-2);
                        const mm = (d.getMonth() + 1).toString().padStart(2, '0');
                        const prefix = `Z${yy}${mm}`;
                        if (!c[prefix]) c[prefix] = 0;
                        const newId = prefix + c[prefix].toString().padStart(5, '0');
                        c[prefix]++;
                        
                        if (grn.id !== newId) {
                            const idx = allPurchases.findIndex(x => x.id === grn.id);
                            if (idx > -1) {
                                allPurchases[idx].id = newId;
                                changed = true;
                            }
                        }
                    });
                    if (changed) {
                        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
                        if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
                    }
                }
                localStorage.setItem('grns_renumbered_clean_z2', 'true');
            }

            populateWarehouseSelect();
            calculatePendingGoods();
            renderPendingInvoices();
            setTimeout(syncPurchaseReceiptsWithMariaDB, 120);
        }

        let _isSyncingPurReceiptsDB = false;
        async function syncPurchaseReceiptsWithMariaDB() {
            if (_isSyncingPurReceiptsDB || !navigator.onLine) return;
            _isSyncingPurReceiptsDB = true;
            const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const tId = ctrl ? setTimeout(() => ctrl.abort(), 4500) : null;
            try {
                const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now(), {
                    signal: ctrl ? ctrl.signal : undefined
                });
                if (tId) clearTimeout(tId);
                if (!res.ok) return;
                const data = await res.json();
                if (data && data.success && Array.isArray(data.purchases)) {
                    let changed = false;
                    let deletedGrnIds = [];
                    try { deletedGrnIds = JSON.parse(localStorage.getItem('erp_deleted_grns_v1') || '[]'); } catch (_ed) {}
                    const deletedSet = new Set(Array.isArray(deletedGrnIds) ? deletedGrnIds.map(String) : []);
                    data.purchases.forEach(remoteInv => {
                        const rNo = remoteInv.invoiceNo || remoteInv.id;
                        if (!rNo || deletedSet.has(String(rNo))) return;
                        const existIdx = allPurchases.findIndex(p => (p.invoiceNo || p.id) === rNo);
                        if (existIdx === -1) {
                            allPurchases.unshift(remoteInv);
                            changed = true;
                        } else {
                            if (remoteInv.receiptStatus === 'received' && allPurchases[existIdx].receiptStatus !== 'received') {
                                allPurchases[existIdx].receiptStatus = 'received';
                                allPurchases[existIdx].status = 'received';
                                changed = true;
                            }
                        }
                    });
                    if (changed) {
                        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
                        calculatePendingGoods();
                        renderPendingInvoices(false);
                        if (typeof renderReceivedInvoices === 'function' && currentHubView === 'received') renderReceivedInvoices(false);
                    }
                }
            } catch (e) {
                if (tId) clearTimeout(tId);
            } finally {
                _isSyncingPurReceiptsDB = false;
            }
        }

        function getUserName(id) {
            if (!id || id === 'unknown') return 'مدير النظام';
            const u = usersDb.find(x => x.id === id || String(x.id) === String(id));
            return u ? u.name : id;
        }

        let _grnInitTimer = null;
        function scheduleGrnFastInit() {
            if (_grnInitTimer) clearTimeout(_grnInitTimer);
            _grnInitTimer = setTimeout(() => { initData(); }, 90);
        }
        initData();
        document.addEventListener('DBReady', () => {
            scheduleGrnFastInit();
            setTimeout(() => { if (typeof window.pullFromCloud === 'function') window.pullFromCloud(true); }, 1800);
        });
        window.addEventListener('cloudDataRestored', scheduleGrnFastInit);
        window.addEventListener('strictDataReady', scheduleGrnFastInit);

        function populateWarehouseSelect() {
            const select = document.getElementById('actual-warehouse');
            select.innerHTML = '';
            // Only actual warehouses
            warehousesDb.forEach(wh => {
                if (wh.id !== 'PENDING_RECEIPT') {
                    const opt = document.createElement('option');
                    opt.value = wh.id;
                    opt.text = wh.name;
                    select.appendChild(opt);
                }
            });
        }

        // Extremely critical: calculate actual pending balances
        // For each invoice, sum quantities where warehouseId == 'PENDING_RECEIPT' or unreceived Mobile purchase invoices.
        // Original invoices add positive pending qty. Returns subtract pending qty. GRNs subtract pending qty.
        function calculatePendingGoods() {
            pendingMap = {};
            const activePur = (allPurchases || []).filter(p => p && !p.deleted && !p.isDeleted && p.id !== editingGRNId);
            const grnsByRoot = {};
            activePur.filter(p => p.isGRN || p.isReturn).forEach(doc => {
                const rootId = String(doc.originalInvoiceId || '').trim();
                if (!rootId) return;
                if (!grnsByRoot[rootId]) grnsByRoot[rootId] = { byKey: {}, count: 0 };
                if (doc.isGRN) grnsByRoot[rootId].count++;
                const lines = doc.items || doc.lines || [];
                const hasNegReversal = lines.some(l => (parseFloat(l.quantity ?? l.qty ?? 0) || 0) < 0);
                lines.forEach(line => {
                    const rawQ = parseFloat(line.quantity ?? line.qty ?? 0) || 0;
                    if (hasNegReversal && rawQ > 0) return;
                    const deductQ = Math.abs(rawQ);
                    const k = String(line.itemId || line.id || line.name || line.itemName || 'صنف').trim();
                    grnsByRoot[rootId].byKey[k] = (grnsByRoot[rootId].byKey[k] || 0) + deductQ;
                });
            });

            activePur.filter(inv => !inv.isGRN && !inv.isReturn && inv.status !== 'draft' && inv.status !== 'proforma').forEach(inv => {
                const rootInvId = String(inv.invoiceNo || inv.id || '').trim();
                if (!rootInvId) return;
                const recInfo = grnsByRoot[rootInvId] || { byKey: {}, count: 0 };
                const items = inv.items || inv.lines || [];
                const globalWh = inv.warehouseId || inv.warehouse || '';
                const isAlreadyDirectRcvd = (inv.receiptStatus === 'received' || inv.status === 'received') && recInfo.count === 0 && globalWh && globalWh !== 'PENDING_RECEIPT' && !String(globalWh).includes('قيد الاستلام');
                if (isAlreadyDirectRcvd) return;

                const linesMap = {};
                items.forEach(line => {
                    const origQ = parseFloat(line.quantity ?? line.qty ?? 0) || 0;
                    if (origQ <= 0) return;
                    const resolvedItemId = String(line.itemId || line.id || line.name || line.itemName || 'صنف').trim();
                    const resolvedUnit = line.unitId || line.selectedUnit || line.unit || line.unitName || 'حبة';
                    const lineKey = resolvedItemId + '|' + resolvedUnit;
                    const prevRec = Math.min(origQ, parseFloat(recInfo.byKey[resolvedItemId] || line.receivedQty || 0) || 0);
                    const remQ = Math.round(Math.max(0, origQ - prevRec) * 100) / 100;
                    if (remQ <= 0) return;
                    if (!linesMap[lineKey]) {
                        linesMap[lineKey] = {
                            itemId: resolvedItemId,
                            itemName: line.name || line.itemName || '',
                            unitId: resolvedUnit,
                            unitName: line.unitName || line.selectedUnit || line.unit || 'حبة',
                            factor: line.customFactor || line.factor || line.equiv || line.conversionFactor || 1,
                            price: parseFloat(line.price ?? line.unitPrice ?? 0) || 0,
                            origQty: 0,
                            pendingQty: 0
                        };
                    }
                    linesMap[lineKey].origQty += origQ;
                    linesMap[lineKey].pendingQty = Math.round((linesMap[lineKey].pendingQty + remQ) * 100) / 100;
                });

                if (Object.keys(linesMap).length > 0) {
                    pendingMap[rootInvId] = {
                        supplierId: inv.customer || inv.customerId || inv.supplierId || inv.supplierName || inv.supplier,
                        supplierName: inv.supplierName || inv.vendorName || inv.supplier || '',
                        date: (inv.date || inv.savedAt || '').slice(0, 10),
                        createdBy: inv.createdBy || inv.createdByName || 'unknown',
                        lines: linesMap
                    };
                }
            });
        }

        function getSupplierName(id) {
            if(!id) return 'مورد نقدي';
            const sup = suppliersDb.find(s => s.id === id || s.name === id);
            if (sup) return sup.name;
            return String(id).startsWith('SUP-') || String(id).startsWith('vend-') ? 'مورد نقدي' : String(id);
        }

        function getItemName(id) {
            if (!id) return 'صنف غير معروف';
            const it = itemsDb.find(i => i.id === id || i.name === id);
            return it ? it.name : String(id);
        }

        window.normalizeGrnArabic = function(str) {
            if (!str) return '';
            const arabicIndic = '٠١٢٣٤٥٦٧٨٩';
            return String(str)
                .replace(/[٠-٩]/g, d => String(arabicIndic.indexOf(d)))
                .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
                .replace(/[أإآٱ]/g, 'ا')
                .replace(/ة/g, 'ه')
                .replace(/ى/g, 'ي')
                .replace(/ؤ/g, 'و')
                .replace(/ئ/g, 'ي')
                .toLowerCase()
                .trim();
        };

        function renderPendingInvoices(resetCount = true) {
            if (resetCount) pendingDisplayCount = 50;
            
            const tbody = document.getElementById('invoices-tbody');
            const rawSearch = document.getElementById('search-inv') ? document.getElementById('search-inv').value : '';
            const search = window.normalizeGrnArabic(rawSearch);
            const fDateFrom = document.getElementById('grn-filter-date-from')?.value || '';
            const fDateTo   = document.getElementById('grn-filter-date-to')?.value || '';
            const fSup      = document.getElementById('grn-filter-supplier')?.value || '';
            tbody.innerHTML = '';
            
            const invIds = Object.keys(pendingMap);

            const filteredIds = invIds.filter(id => {
                const invObj = pendingMap[id] || {};
                if (fSup && String(invObj.supplierId) !== String(fSup)) return false;
                if (fDateFrom || fDateTo) {
                    const dIso = (invObj.date || '').slice(0, 10);
                    if (fDateFrom && dIso && dIso < fDateFrom) return false;
                    if (fDateTo && dIso && dIso > fDateTo) return false;
                }
                if (search) {
                    const supName = window.normalizeGrnArabic(getSupplierName(invObj.supplierId));
                    const normId = window.normalizeGrnArabic(id);
                    const itemNamesStr = window.normalizeGrnArabic(
                        Object.values(invObj.lines || {}).map(l => getItemName(l.itemId)).join(' ')
                    );
                    return normId.includes(search) || supName.includes(search) || itemNamesStr.includes(search);
                }
                return true;
            });

            if (filteredIds.length === 0) {
                document.getElementById('empty-hub').style.display = 'flex';
                let btn = document.getElementById('pending-load-more');
                if (btn) btn.style.display = 'none';
            } else {
                document.getElementById('empty-hub').style.display = 'none';
                
                // Sort by date desc (safely)
                filteredIds.sort((a,b) => {
                    let tA = new Date(pendingMap[a].date).getTime();
                    let tB = new Date(pendingMap[b].date).getTime();
                    if(isNaN(tA)) tA = 0;
                    if(isNaN(tB)) tB = 0;
                    if (tA === tB) return b.localeCompare(a);
                    return tB - tA;
                });
                
                const itemsToRender = filteredIds.slice(0, pendingDisplayCount);
                
                itemsToRender.forEach(id => {
                    const invData = pendingMap[id];
                    const supName = invData.supplierName || getSupplierName(invData.supplierId);
                    
                    const linesArr = Object.values(invData.lines);
                    const totalPendingItems = linesArr.length;
                    const exRate = (typeof window.getGrnActiveExchangeRate === 'function' ? window.getGrnActiveExchangeRate() : 2650) || 2650;
                    const pendingValSdg = linesArr.reduce((acc, l) => acc + ((parseFloat(l.pendingQty) || 0) * (parseFloat(l.price) || 0)), 0);
                    const valText = window._grnHubInUsd
                        ? ('$' + (pendingValSdg / exRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
                        : (Math.round(pendingValSdg).toLocaleString('en-US') + ' ج.س');
                    const itemsBarsHtml = linesArr.map(l => {
                        const nm = l.itemName || getItemName(l.itemId);
                        const origQ = Math.max(0.0001, parseFloat(l.origQty || l.pendingQty) || 1);
                        const pendQ = Math.max(0, parseFloat(l.pendingQty) || 0);
                        const pct = Math.max(0, Math.min(100, Math.round((pendQ / origQ) * 100)));
                        const lVal = Math.round(pendQ * (parseFloat(l.price) || 0));
                        const bClr = pct === 0 ? '#10b981' : (pct < 100 ? '#38bdf8' : '#f59e0b');
                        return `<div style="display:flex;align-items:center;gap:7px;padding:3px 7px;margin:2px 0;border-radius:7px;background:rgba(2,6,23,0.48);border:1px solid rgba(148,163,184,0.25);">
                            <span style="font-size:0.74rem;font-weight:800;color:#fff;min-width:105px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${nm} <span style="color:#7dd3fc;">(${pendQ})</span></span>
                            <div style="flex:1;min-width:65px;height:7px;background:rgba(148,163,184,0.22);border-radius:99px;overflow:hidden;"><div style="width:${pct}%;height:100%;background:${bClr};border-radius:99px;"></div></div>
                            <span style="font-size:0.69rem;font-weight:900;color:${bClr};min-width:30px;">${pct}%</span>
                            <span style="font-size:0.72rem;font-weight:900;color:#fde047;font-family:'Outfit',monospace;white-space:nowrap;">${lVal.toLocaleString('en-US')}</span>
                        </div>`;
                    }).join('');

                    const tr = document.createElement('tr');
                    tr.setAttribute('data-rc-invoice-no', id);
                    tr.setAttribute('data-rc-invoice-mode', 'purchases');
                    tr.innerHTML = `
                        <td style="text-align:center; font-family:'Outfit','Cairo',sans-serif; font-size:0.84rem; font-weight:900;">${new Date(invData.date).toLocaleDateString('en-GB')}</td>
                        <td class="grn-hub-col-inv" data-rc-invoice-no="${id}" data-rc-invoice-mode="purchases" style="text-align:center; font-family:'Outfit','Cairo',sans-serif; color:#38bdf8; font-size:0.92rem; font-weight:900; cursor:pointer; text-decoration:underline; text-underline-offset:3px;" onclick="event.stopPropagation(); if(typeof window.__openErpRightClickChoiceMenu==='function'){ window.__openErpRightClickChoiceMenu(event); } else if(window.openGrnDrillDownModal){ window.openGrnDrillDownModal('${id}'); }" title="اضغط أو كليك يمين للشرح والذهاب إلى أصل الفاتورة">#${id}</td>
                        <td data-rc-account-name="${String(supName || '').replace(/"/g, '&quot;')}" data-rc-account-type="supplier" data-rc-invoice-no="${id}" data-rc-invoice-mode="purchases" style="text-align:center; font-weight:900; font-size:0.88rem;">${supName}</td>
                        <td style="text-align:center; min-width: 280px;">${itemsBarsHtml}</td>
                        <td style="text-align:center; font-family:'Outfit','Cairo',sans-serif; font-weight:900; font-size:0.88rem;">${totalPendingItems} صنف (${valText})</td>
                        <td style="text-align:center;">
                            <div class="grn-row-action-capsule" data-inv-id="${id}">
                                <button type="button" class="btn-action grn-pending-inspect-btn grn-kinetic-btn grn-light-clear-btn" data-inv-id="${id}" onclick="event.stopPropagation(); if(window.triggerGrnKineticFeedback) window.triggerGrnKineticFeedback(this,'inspect'); openReceiptEditor('${id}')">📥 فحص واستلام</button>
                                <button type="button" class="btn-action grn-quick-hub-rcv grn-kinetic-btn grn-light-clear-btn" data-inv-id="${id}" title="فحص واستلام كامل المتبقي في الفاتورة فوراً بضغطة واحدة إلى المستودع الرئيسي ⚡" onclick="event.stopPropagation(); if(window.triggerGrnKineticFeedback) window.triggerGrnKineticFeedback(this,'instant'); window.quickReceiveInvoiceFromHub('${id}')">⚡ استلام فوري</button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                if (typeof window.updateGrnHubFinStrip === 'function') window.updateGrnHubFinStrip(filteredIds);
                
                let loadMoreBtn = document.getElementById('pending-load-more');
                if (!loadMoreBtn) {
                    loadMoreBtn = document.createElement('div');
                    loadMoreBtn.id = 'pending-load-more';
                    loadMoreBtn.style = 'text-align: center; margin-top: 15px; margin-bottom: 20px;';
                    loadMoreBtn.innerHTML = `<button class="btn-action" style="padding: 10px 30px; font-size: 1rem; border-radius: 30px; background: linear-gradient(135deg, #f59e0b, #d97706);" onclick="loadMorePending()">عرض 50 فاتورة أخرى ⬇️</button>`;
                    document.getElementById('hub-pending-container').appendChild(loadMoreBtn);
                }
                if (filteredIds.length > pendingDisplayCount) {
                    loadMoreBtn.style.display = 'block';
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            }
            
            if (currentHubView === 'pending') {
                document.getElementById('hub-title-text').innerHTML = `قيد الاستلام`;
                const counterContainer = document.getElementById('hub-counter-badge');
                counterContainer.innerHTML = `<span class="counter-animate-abs" style="background: rgba(245, 158, 11, 0.18); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.45); box-shadow: 0 4px 12px rgba(180, 83, 9, 0.18); font-family: 'Cairo', 'Outfit', 'Tajawal', sans-serif; white-space: nowrap;">${filteredIds.length} إذن لم يستلم</span>`;
            }
        }

        function showHub() {
            document.getElementById('view-editor').classList.remove('active');
            document.getElementById('view-hub').classList.add('active');
            currentInvoiceId = null;
            receiptLines = [];
            editingGRNId = null; // Clear edit mode
            calculatePendingGoods();
            renderPendingInvoices();
        }

        function openReceiptEditor(invId) {
            currentInvoiceId = invId;
            const invData = pendingMap[invId];
            if (!invData) return showToast('خطأ في البيانات', 'error');

            document.getElementById('lbl-inv-no').textContent = invId;
            document.getElementById('lbl-supplier').textContent = getSupplierName(invData.supplierId);
            document.getElementById('lbl-date').textContent = new Date(invData.date).toLocaleString('ar-EG-u-nu-latn');
            document.getElementById('lbl-creator').textContent = getUserName(invData.createdBy);

            const defaultWh = document.getElementById('actual-warehouse').value || (warehousesDb.find(x => x.id !== 'PENDING_RECEIPT') || {}).id || 'w-main';

            receiptLines = Object.values(invData.lines).map((line, index) => {
                let initialFactor = 1;
                if (line.factor && parseFloat(line.factor) > 0) {
                    initialFactor = parseFloat(line.factor);
                } else if (line.customFactor && parseFloat(line.customFactor) > 0) {
                    initialFactor = parseFloat(line.customFactor);
                } else if (typeof itemsDb !== 'undefined') {
                    const itemObj = itemsDb.find(i => String(i.id) === String(line.itemId) || i.name === line.itemId || i.name === getItemName(line.itemId));
                    if (itemObj) {
                        const uName = String(line.unitName || line.unit || '').trim();
                        if (itemObj.units && Array.isArray(itemObj.units)) {
                            const u = itemObj.units.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName) || (x.id && String(x.id) === String(line.unitId)));
                            if (u && (u.factor || u.equiv)) initialFactor = parseFloat(u.factor || u.equiv) || 1;
                        } else if (itemObj.unitsArray && Array.isArray(itemObj.unitsArray)) {
                            const u = itemObj.unitsArray.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName));
                            if (u && (u.factor || u.equiv)) initialFactor = parseFloat(u.factor || u.equiv) || 1;
                        }
                    }
                }
                if (isNaN(initialFactor) || initialFactor <= 0) initialFactor = 1;
                initialFactor = resolveGrnElsajanaSteelFactor(line.unitName || line.unit, initialFactor);

                return {
                    id: 'L' + index,
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    factor: initialFactor,
                    customFactor: initialFactor,
                    price: line.price || 0,
                    origQty: line.origQty || line.pendingQty,
                    maxPending: line.pendingQty,
                    receiveQty: line.pendingQty, // Default to fully receiving what's pending
                    warehouseId: defaultWh,
                    lineGroup: line.itemId + '|' + (line.unitId || ''),
                    isSplit: false
                };
            });

            document.getElementById('view-hub').classList.remove('active');
            document.getElementById('view-editor').classList.add('active');
            if (typeof window.updateGrnSupplierFinStrip === 'function') window.updateGrnSupplierFinStrip(invData.supplierId);
            renderReceiptLines();
            renderRecentGRNs(invId);
        }

        
        function updateSummary() {
            let totalItems = 0;
            let totalQty = 0;
            let totalVal = 0;
            
            const distinctItems = new Set();
            receiptLines.forEach(line => {
                const rQty = Math.round((parseFloat(line.receiveQty) || 0) * 1000) / 1000;
                if (rQty > 0) {
                    distinctItems.add(line.itemId);
                    totalQty = Math.round((totalQty + rQty) * 1000) / 1000;
                    totalVal = Math.round((totalVal + rQty * (parseFloat(line.price) || 0)) * 100) / 100;
                }
            });
            totalItems = distinctItems.size;

            const elItems = document.getElementById('lbl-total-received-items');
            const elQty = document.getElementById('lbl-total-received-qty');
            const elVal = document.getElementById('lbl-total-received-val');
            const elValUsd = document.getElementById('lbl-total-received-val-usd');

            if (elItems) elItems.textContent = totalItems + ' أصناف';
            if (elQty) elQty.textContent = fmtNum(totalQty);
            if (elVal) elVal.textContent = fmtNum(totalVal) + ' ج.س';
            if (elValUsd) {
                const rate = (typeof window.getGrnActiveExchangeRate === 'function' ? window.getGrnActiveExchangeRate() : 2650) || 2650;
                const usdVal = totalVal / rate;
                elValUsd.textContent = '$' + usdVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }


        // ══════════════════════════════════════════════════════════
        // ADVANCED GRID FORMATTING (PER-COLUMN & GLOBAL CONTROLS)
        // ══════════════════════════════════════════════════════════
        const DEFAULT_GRID_PREFS = {
            global: {
                font: "Cairo",
                size: "16",
                weight: "700",
                color: "#ffffff",
                decimals: 0,
                thousands: true,
                align: "center"
            },
            cols: {
                origQty:     { font: "", size: "17", weight: "800", color: "#34d399", decimals: 0, thousands: true, align: "center" },
                item:        { font: "", size: "15", weight: "700", color: "#f8fafc", decimals: 0, thousands: true, align: "right" },
                unit:        { font: "", size: "15", weight: "600", color: "#93c5fd", decimals: 0, thousands: true, align: "center" },
                remQty:      { font: "", size: "17", weight: "800", color: "#fbbf24", decimals: 0, thousands: true, align: "center" },
                factor:      { font: "", size: "16", weight: "700", color: "#c084fc", decimals: 0, thousands: true, align: "center" },
                pendingBase: { font: "", size: "16", weight: "700", color: "#38bdf8", decimals: 0, thousands: true, align: "center" },
                rcvQty:      { font: "", size: "18", weight: "900", color: "#ffffff", decimals: 0, thousands: true, align: "center" },
                rcvBase:     { font: "", size: "17", weight: "800", color: "#34d399", decimals: 0, thousands: true, align: "center" },
                warehouse:   { font: "", size: "14", weight: "600", color: "#ffffff", decimals: 0, thousands: true, align: "center" }
            }
        };

        function getStoredGridPrefs() {
            let p = JSON.parse(JSON.stringify(DEFAULT_GRID_PREFS));
            try {
                const raw = localStorage.getItem("erp_grn_col_prefs_v2");
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed.global) p.global = { ...p.global, ...parsed.global };
                    if (parsed.cols) p.cols = { ...p.cols, ...parsed.cols };
                }
            } catch(e) {}
            return p;
        }

        function getColStyleObj(colKey, fallbackAlign="center") {
            const prefs = getStoredGridPrefs();
            const g = prefs.global || DEFAULT_GRID_PREFS.global;
            const c = (prefs.cols && prefs.cols[colKey]) ? prefs.cols[colKey] : {};

            const font = (c.font && c.font !== "") ? c.font : g.font;
            const size = (c.size && c.size !== "") ? c.size : g.size;
            const weight = (c.weight && c.weight !== "") ? c.weight : g.weight;
            const color = (c.color && c.color !== "") ? c.color : g.color;
            const align = (c.align && c.align !== "") ? c.align : (g.align || fallbackAlign);
            const decimals = (c.decimals !== undefined && c.decimals !== "") ? parseInt(c.decimals) : parseInt(g.decimals || 0);
            const thousands = (c.thousands !== undefined) ? c.thousands : (g.thousands !== false);

            const justify = align === "right" ? "flex-start" : (align === "left" ? "flex-end" : "center");
            const boxStyle = `background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(56, 189, 248, 0.22); border-radius: 9px; height: 48px; display: flex; align-items: center; justify-content: ${justify}; font-family: '${font}', 'Cairo', 'Tajawal', sans-serif !important; font-size: ${size}px !important; font-weight: 700 !important; color: ${color}; padding: 0 10px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); transition: all 0.2s ease;`;

            return {
                boxStyle,
                font, size, weight, color, align, decimals, thousands
            };
        }

        function fmtColVal(colKey, n) {
            const cs = getColStyleObj(colKey);
            const num = Number(n) || 0;
            if (cs.thousands) {
                return num.toLocaleString("en-US", { minimumFractionDigits: cs.decimals, maximumFractionDigits: cs.decimals });
            } else {
                return num.toFixed(cs.decimals);
            }
        }

        function renderReceiptLines() {
            const scrollWrap = document.querySelector('#view-editor .grid-scroll-wrap');
            const savedScrollTop = scrollWrap ? scrollWrap.scrollTop : 0;

            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = '';

            const rcvCol = getColStyleObj('rcvQty');
            const inputStyle = `font-family: '${rcvCol.font}', 'Cairo', 'Tajawal', sans-serif !important; font-size: ${rcvCol.size}px !important; font-weight: 800 !important; color: ${rcvCol.color}; text-align: ${rcvCol.align} !important; height: 48px; width: 100%; background: rgba(2, 6, 23, 0.85); border: 1px solid rgba(56, 189, 248, 0.28); border-radius: 9px; outline: none; padding-right: 28px; padding-left: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); transition: all 0.25s ease;`;

            receiptLines.forEach(line => {
                const tr = document.createElement('tr');
                const lineTotal = (line.price || 0) * (line.receiveQty || 0);
                
                // Build warehouse options
                let whOptions = '';
                warehousesDb.forEach(wh => {
                    if (wh.id !== 'PENDING_RECEIPT') {
                        const selected = wh.id === line.warehouseId ? 'selected' : '';
                        whOptions += `<option value="${wh.id}" ${selected}>${wh.name}</option>`;
                    }
                });

                // Calculate remaining quantity
                const otherSplits = receiptLines.filter(x => x.lineGroup === line.lineGroup && x.id !== line.id);
                const totalAssigned = otherSplits.reduce((acc, x) => acc + (x.receiveQty || 0), 0) + (line.receiveQty || 0);
                const remainingQty = Math.round((line.maxPending - totalAssigned) * 100) / 100;
                
                let factor = line.factor !== undefined ? parseFloat(line.factor) : (line.customFactor !== undefined ? parseFloat(line.customFactor) : 1);
                if (isNaN(factor) || factor <= 0) {
                    if (typeof itemsDb !== 'undefined') {
                        const itemObj = itemsDb.find(i => String(i.id) === String(line.itemId) || i.name === line.itemId || i.name === getItemName(line.itemId));
                        if (itemObj) {
                            const uName = String(line.unitName || line.unit || '').trim();
                            if (itemObj.units && Array.isArray(itemObj.units)) {
                                const u = itemObj.units.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName) || (x.id && String(x.id) === String(line.unitId)));
                                if (u && (u.factor || u.equiv)) factor = parseFloat(u.factor || u.equiv) || 1;
                            } else if (itemObj.unitsArray && Array.isArray(itemObj.unitsArray)) {
                                const u = itemObj.unitsArray.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName));
                                if (u && (u.factor || u.equiv)) factor = parseFloat(u.factor || u.equiv) || 1;
                            }
                        }
                    }
                }
                if (isNaN(factor) || factor <= 0) factor = 1;
                line.factor = factor;
                line.customFactor = factor;

                const baseQty = (line.maxPending * factor);
                const rcvBaseQty = ((line.receiveQty || 0) * factor);
                const whCol = getColStyleObj('warehouse');
                const factorCol = getColStyleObj('factor');
                const factorInputStyle = `font-family: '${factorCol.font}', 'Cairo', 'Tajawal', sans-serif !important; font-size: ${factorCol.size}px !important; font-weight: 800 !important; color: ${factorCol.color}; text-align: ${factorCol.align} !important; height: 48px; width: 100%; background: rgba(2, 6, 23, 0.85); border: 1px solid rgba(56, 189, 248, 0.28); border-radius: 9px; outline: none; padding: 0 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); transition: all 0.25s ease;`;
                
                tr.innerHTML = `
                    <td style="display:none;" class="grn-col-item">${line.price}</td>
                    <td style="display:none;" class="grn-col-item">${lineTotal}</td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('origQty').boxStyle}" class="grn-col-origQty" title="كمية الشراء الأصلية بالفاتورة">${fmtColVal('origQty', line.origQty || line.maxPending)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('item', 'right').boxStyle}; justify-content: space-between; gap: 6px; overflow: hidden;" class="grn-col-item" title="${getItemName(line.itemId)}">
                            <span style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden; display: inline-flex; align-items: center; gap: 5px;">📦 ${getItemName(line.itemId)}</span>
                            ${typeof window.buildGrnPriceRadarBadgeHtml === 'function' ? window.buildGrnPriceRadarBadgeHtml(line) : ''}
                        </div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('unit').boxStyle}" class="grn-col-unit">${line.unitName || line.unit || '—'}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('remQty').boxStyle}" class="grn-col-qty" title="الكمية المتبقية للاستلام">${fmtColVal('remQty', line.maxPending)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="position: relative; width: 100%; display: flex; align-items: center;">
                            <input type="text" inputmode="decimal" class="factor-input grn-col-factor write-indicator-box" value="${factor}" placeholder="التعادل..." title="معامل التعادل (يمكن تعديله لتغيير الكمية المخزنية)" onfocus="this.select(); this.classList.add('focused');" onblur="this.classList.remove('focused'); if(!this.value.trim() || parseFloat(this.value) <= 0) this.value='1';" style="${factorInputStyle}">
                        </div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('pendingBase').boxStyle}" class="grn-col-pendingBase">${fmtColVal('pendingBase', baseQty)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="position: relative; width: 100%; display: flex; align-items: center;">
                            <span style="position: absolute; right: 10px; font-size: 1rem; color: #38bdf8; pointer-events: none; opacity: 0.9; z-index: 2;" title="حقل كتابة الكمية المستلمة">✏️</span>
                            <input type="text" inputmode="decimal" class="qty-input grn-col-rcvQty write-indicator-box" value="${line.receiveQty !== undefined ? line.receiveQty : line.maxPending}" placeholder="اكتب..." onfocus="this.select(); this.classList.add('focused');" onblur="this.classList.remove('focused'); if(!this.value.trim()) this.value='0';" style="${inputStyle}">
                        </div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div class="base-rcv-qty grn-col-baseQty" style="${getColStyleObj('rcvBase').boxStyle}">${fmtColVal('rcvBase', rcvBaseQty)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="display:flex; align-items:center; gap:4px;">
                            <select class="wh-select grn-col-warehouse" style="flex:1; height: 48px; padding: 0 8px; background: rgba(2, 6, 23, 0.85); border: 1px solid rgba(56, 189, 248, 0.28); font-family: '${whCol.font}', 'Cairo', 'Tajawal', sans-serif !important; color: ${whCol.color}; font-size: ${whCol.size}px !important; font-weight: 700 !important; text-align: ${whCol.align} !important; border-radius: 9px; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);">${whOptions}</select>
                            ${line.isSplit
                                ? `<button type="button" onclick="removeSplit(event, '${line.id}')" title="دمج مع السطر الأصلي" style="height:48px; padding:0 9px; border-radius:9px; border:1px solid rgba(248,113,113,0.5); background:rgba(239,68,68,0.18); color:#fca5a5; font-weight:900; cursor:pointer;">✕</button>`
                                : `<button type="button" onclick="splitLine(event, '${line.id}')" title="تقسيم الكمية بين مخزنين (مثلاً 7 و 3)" style="height:48px; padding:0 9px; border-radius:9px; border:1px solid rgba(56,189,248,0.5); background:rgba(14,165,233,0.18); color:#7dd3fc; font-weight:900; cursor:pointer; white-space:nowrap;">✂️ قسّم</button>`}
                        </div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <input type="text" class="notes-input" value="${line.notes || ''}" placeholder="ملحوظة الاستلام..." style="width: 100%; height: 48px; padding: 0 10px; background: rgba(2, 6, 23, 0.85); border: 1px solid rgba(56, 189, 248, 0.28); font-family: 'Cairo', 'Tajawal', sans-serif; color: #f8fafc; font-size: 13px; font-weight: 700; border-radius: 9px; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); transition: border-color 0.2s;">
                    </td>
                `;

                const factorInput = tr.querySelector('.factor-input');
                const qtyInput = tr.querySelector('.qty-input');
                const baseRcvCell = tr.querySelector('.base-rcv-qty');
                const pendingBaseCell = tr.querySelector('.grn-col-pendingBase');

                factorInput.addEventListener('input', function() {
                    let clean = this.value.replace(/[^0-9.]/g, '');
                    const parts = clean.split('.');
                    if (parts.length > 2) {
                        clean = parts[0] + '.' + parts.slice(1).join('');
                    }
                    if (this.value !== clean) {
                        this.value = clean;
                    }
                    let fVal = parseFloat(clean);
                    if (isNaN(fVal) || fVal <= 0) {
                        fVal = 1;
                    }
                    line.factor = fVal;
                    line.customFactor = fVal;

                    if (pendingBaseCell) {
                        pendingBaseCell.textContent = fmtColVal('pendingBase', (line.maxPending || 0) * fVal);
                    }
                    if (baseRcvCell) {
                        baseRcvCell.textContent = fmtColVal('rcvBase', (line.receiveQty || 0) * fVal);
                    }
                    updateSummary();
                });

                qtyInput.addEventListener('input', function() {
                    let clean = this.value.replace(/[^0-9.]/g, '');
                    const parts = clean.split('.');
                    if (parts.length > 2) {
                        clean = parts[0] + '.' + parts.slice(1).join('');
                    }
                    if (this.value !== clean) {
                        this.value = clean;
                    }
                    let val = parseFloat(clean);
                    if (isNaN(val) || val < 0) val = 0;
                    const siblings = receiptLines.filter(x => x.lineGroup === line.lineGroup && x.id !== line.id);
                    if (siblings.length === 1) {
                        if (val > line.maxPending) { val = line.maxPending; this.value = String(val); }
                        line.receiveQty = val;
                        siblings[0].receiveQty = Math.max(0, +(line.maxPending - val).toFixed(4));
                        const curFactor = (line.factor !== undefined && line.factor !== null) ? parseFloat(line.factor) : factor;
                        if (baseRcvCell) baseRcvCell.textContent = fmtColVal('rcvBase', val * curFactor);
                        const sibIdx = receiptLines.findIndex(x => x.id === siblings[0].id);
                        const sibTr = tbody.children[sibIdx];
                        if (sibTr) {
                            const sInp = sibTr.querySelector('.qty-input'); if (sInp) sInp.value = String(siblings[0].receiveQty);
                            const sBase = sibTr.querySelector('.base-rcv-qty'); if (sBase) sBase.textContent = fmtColVal('rcvBase', siblings[0].receiveQty * (siblings[0].factor || curFactor));
                        }
                    } else {
                        const otherQty = siblings.reduce((acc, x) => acc + (parseFloat(x.receiveQty) || 0), 0);
                        const maxAllowed = Math.max(0, +(line.maxPending - otherQty).toFixed(4));
                        if (val > maxAllowed) {
                            if (typeof showToast === 'function') showToast('تم ضبط السقف تلقائياً للمتبقي المتاح (' + maxAllowed + ')', 'warning');
                            val = maxAllowed; this.value = String(val);
                        }
                        line.receiveQty = val;
                        const curFactor = (line.factor !== undefined && line.factor !== null) ? parseFloat(line.factor) : factor;
                        if (baseRcvCell) baseRcvCell.textContent = fmtColVal('rcvBase', val * curFactor);
                    }
                    updateSummary();
                });

                const whSelect = tr.querySelector('.wh-select');
                whSelect.addEventListener('change', function() {
                    line.warehouseId = this.value;
                });

                const notesInput = tr.querySelector('.notes-input');
                if (notesInput) {
                    notesInput.addEventListener('input', function() {
                        line.notes = this.value;
                    });
                }

                tbody.appendChild(tr);
            });
                // Update summary details
            updateSummary();

            if (scrollWrap) {
                scrollWrap.scrollTop = savedScrollTop;
            }
        }

        function updateLineWarehouse(lineId, whId) {
            const line = receiptLines.find(x => x.id === lineId);
            if (line) {
                line.warehouseId = whId;
            }
        }

        function changeQtyStep(lineId, step) {
            const line = receiptLines.find(x => x.id === lineId);
            if (line) {
                const newVal = Math.max(0, (line.receiveQty || 0) + step);
                updateReceiveQty(lineId, String(newVal));
            }
        }

        function splitLine(e, lineId) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const line = receiptLines.find(x => x.id === lineId);
            if (!line) return;
            
            const parentIndex = receiptLines.findIndex(x => x.id === lineId);
            
            // Calculate total unassigned qty for this group
            const groupSplits = receiptLines.filter(x => x.lineGroup === line.lineGroup);
            const totalAssigned = groupSplits.reduce((acc, x) => acc + (parseFloat(x.receiveQty) || 0), 0);
            let remaining = Math.max(0, Math.round((line.maxPending - totalAssigned) * 100) / 100);

            // If parent line currently holds the full pending qty, automatically split 50/50 between the two warehouses!
            if (remaining === 0 && (parseFloat(line.receiveQty) || 0) > 1) {
                const curQty = parseFloat(line.receiveQty) || 0;
                const half = Math.floor((curQty / 2) * 100) / 100;
                remaining = Math.round((curQty - half) * 100) / 100;
                line.receiveQty = half;
            }

            // Pick a different warehouse by default if multiple warehouses exist
            const altWh = (warehousesDb || []).find(w => w.id !== 'PENDING_RECEIPT' && w.id !== line.warehouseId);
            const targetWhId = altWh ? altWh.id : line.warehouseId;

            const newLine = {
                id: line.lineGroup + '_split_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                itemId: line.itemId,
                unitId: line.unitId,
                unitName: line.unitName,
                factor: line.factor || line.customFactor || 1,
                customFactor: line.customFactor || line.factor || 1,
                origQty: line.origQty || line.maxPending,
                price: line.price || 0,
                maxPending: line.maxPending,
                receiveQty: remaining,
                warehouseId: targetWhId,
                lineGroup: line.lineGroup,
                isSplit: true
            };
            
            receiptLines.splice(parentIndex + 1, 0, newLine);
            renderReceiptLines();
        }

        function removeSplit(e, lineId) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const removedLine = receiptLines.find(x => x.id === lineId);
            receiptLines = receiptLines.filter(x => x.id !== lineId);
            if (removedLine && (parseFloat(removedLine.receiveQty) || 0) > 0) {
                const parentLine = receiptLines.find(x => x.lineGroup === removedLine.lineGroup && !x.isSplit) || receiptLines.find(x => x.lineGroup === removedLine.lineGroup);
                if (parentLine) {
                    const groupSum = receiptLines.filter(x => x.lineGroup === removedLine.lineGroup && x.id !== parentLine.id).reduce((s, x) => s + (parseFloat(x.receiveQty) || 0), 0);
                    parentLine.receiveQty = Math.min(parentLine.maxPending - groupSum, (parseFloat(parentLine.receiveQty) || 0) + (parseFloat(removedLine.receiveQty) || 0));
                }
            }
            renderReceiptLines();
        }

        function updateReceiveQty(lineId, valStr) {
            const val = parseNum(valStr);
            const line = receiptLines.find(x => x.id === lineId);
            if (line) {
                // Sum all splits for this item group EXCEPT the current one
                const otherSplits = receiptLines.filter(x => x.lineGroup === line.lineGroup && x.id !== lineId);
                const otherQty = otherSplits.reduce((acc, x) => acc + (x.receiveQty || 0), 0);
                const maxAllowed = line.maxPending - otherQty;

                if (val > maxAllowed) {
                    showToast(`الكمية المستلمة لا يمكن أن تتجاوز الكمية المعلقة للملف (${line.maxPending}). المتبقي المتاح: ${maxAllowed}`, 'error');
                    line.receiveQty = Math.max(0, maxAllowed);
                } else if (val < 0) {
                    line.receiveQty = 0;
                } else {
                    line.receiveQty = val;
                }
            }
            renderReceiptLines();
        }

        function generateGRNNumber() {
            const d = new Date();
            const yy = d.getFullYear().toString().slice(-2);
            const mm = (d.getMonth() + 1).toString().padStart(2, '0');
            const prefix = `Z${yy}${mm}`;
            
            let maxSeq = -1;
            if (typeof allPurchases !== 'undefined' && allPurchases) {
                allPurchases.forEach(p => {
                    if (p.isGRN && p.id && p.id.startsWith(prefix)) {
                        const seqStr = p.id.substring(prefix.length);
                        const seqNum = parseInt(seqStr, 10);
                        if (!isNaN(seqNum) && seqNum > maxSeq) {
                            maxSeq = seqNum;
                        }
                    }
                });
            }
            const nextSeq = maxSeq === -1 ? 0 : maxSeq + 1;
            return prefix + nextSeq.toString().padStart(5, '0');
        }

        function saveReceiptNote() {
            const receivingItems = receiptLines.filter(x => x.receiveQty > 0);
            if (receivingItems.length === 0) {
                const firstQty = document.querySelector('#items-tbody input.qty-input');
                if (window.ElsajanaAccountingBrain && firstQty) return window.ElsajanaAccountingBrain.shakeAndFocus(firstQty, 'يجب تحديد كمية استلام لصنف واحد على الأقل', 'error');
                return showToast('يجب تحديد كمية استلام لصنف واحد على الأقل', 'error');
            }

            const hasEmptyWh = receivingItems.some(x => !x.warehouseId);
            if (hasEmptyWh) {
                const whSel = document.querySelector('#items-tbody select');
                if (window.ElsajanaAccountingBrain && whSel) return window.ElsajanaAccountingBrain.shakeAndFocus(whSel, 'يرجى تحديد المستودع لجميع الأصناف المستلمة', 'error');
                return showToast('يرجى تحديد المستودع لجميع الأصناف المستلمة', 'error');
            }

            // Same-Day Accidental Duplicate Receipt Guard (Point 6)
            const todayKey = new Date().toISOString().slice(0, 10);
            const saveSig = `${currentInvoiceId}|${todayKey}|${receivingItems.map(x => `${x.itemId}:${x.receiveQty}`).join(',')}`;
            if (!editingGRNId && window._lastGrnSameDaySig === saveSig && !window._allowDupGrnOnce) {
                window._allowDupGrnOnce = true;
                return showToast('⚠️ تنبيه ذكي: تم اعتماد نفس مذكرة الاستلام بنفس الكميات اليوم! اضغط حفظ مرة أخرى إذا كان استلاماً متعمداً.', 'warning');
            }
            window._lastGrnSameDaySig = saveSig;
            window._allowDupGrnOnce = false;

            const invData = pendingMap[currentInvoiceId];
            
            const newItems = [];
            receivingItems.forEach(line => {
                // احسب عامل التحويل من بيانات السطر أو الصنف
                let lineFactor = 1;
                if (line.factor !== undefined && parseFloat(line.factor) > 0) {
                    lineFactor = parseFloat(line.factor);
                } else if (line.customFactor !== undefined && parseFloat(line.customFactor) > 0) {
                    lineFactor = parseFloat(line.customFactor);
                } else if (typeof itemsDb !== 'undefined') {
                    const itemObj = itemsDb.find(i => String(i.id) === String(line.itemId) || i.name === line.itemId);
                    if (itemObj) {
                        const uName = String(line.unitName || line.unit || '').trim();
                        if (itemObj.units && Array.isArray(itemObj.units)) {
                            const u = itemObj.units.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName) || (x.id && String(x.id) === String(line.unitId)));
                            if (u && (u.factor || u.equiv)) lineFactor = parseFloat(u.factor || u.equiv) || 1;
                        } else if (itemObj.unitsArray && Array.isArray(itemObj.unitsArray)) {
                            const u = itemObj.unitsArray.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName));
                            if (u && (u.factor || u.equiv)) lineFactor = parseFloat(u.factor || u.equiv) || 1;
                        }
                    }
                }

                lineFactor = resolveGrnElsajanaSteelFactor(line.unitName || line.unit, lineFactor);
                const baseQtyReceived = (parseFloat(line.receiveQty) || 0) * lineFactor;

                // Negative (Take out of PENDING)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: -line.receiveQty,
                    factor: lineFactor,
                    customFactor: lineFactor,
                    customBaseQty: -baseQtyReceived,
                    warehouseId: 'PENDING_RECEIPT',
                    price: line.price || 0,
                    taxRate: 0,
                    notes: line.notes || '',
                    total: -((line.price || 0) * line.receiveQty)
                });
                // Positive (Put into ACTUAL)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: line.receiveQty,
                    factor: lineFactor,
                    customFactor: lineFactor,
                    customBaseQty: baseQtyReceived,
                    warehouseId: line.warehouseId,
                    price: line.price || 0,
                    taxRate: 0,
                    notes: line.notes || '',
                    total: (line.price || 0) * line.receiveQty
                });
            });

            const firstWh = receivingItems[0].warehouseId;

            // Generate combined notes
            const lineNotesList = receivingItems.map(l => l.notes ? `${getItemName(l.itemId)}: ${l.notes}` : '').filter(Boolean);
            const notesSummary = lineNotesList.length > 0 ? ` (${lineNotesList.join(' | ')})` : '';

            const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };

            const grnInv = {
                id: generateGRNNumber(),
                isGRN: true,
                originalInvoiceId: currentInvoiceId,
                type: 'purchase',
                customerId: invData.supplierId,
                date: new Date().toISOString(),
                warehouse: firstWh,
                subtotal: 0,
                taxValue: 0,
                discountValue: 0,
                grandTotal: 0,
                items: newItems,
                status: 'completed',
                notes: `مذكرة استلام بضاعة للفاتورة ${currentInvoiceId}${notesSummary}`,
                createdBy: curUser.id,
                createdByName: curUser.fullName || curUser.name,
                createdByUsername: curUser.username
            };

            // Revert stock of the old GRN first if we are in edit mode
            let currentInvData = [];
            try {
                currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            } catch(e) {}

            if (editingGRNId) {
                const oldGrn = allPurchases.find(p => p.id === editingGRNId);
                if (oldGrn) {
                    const oldPositiveItems = oldGrn.items.filter(item => item.quantity > 0);
                    oldPositiveItems.forEach(pItem => {
                        const whObj = warehousesDb.find(w => w.id === pItem.warehouseId);
                        const whName = whObj ? whObj.name : 'المخزن الرئيسي';
                        let itemInv = currentInvData.find(inv => inv.itemId === pItem.itemId && inv.warehouse === whName);
                        if (itemInv) {
                            let qtyToRevert = pItem.customBaseQty !== undefined ? parseFloat(pItem.customBaseQty) : ((parseFloat(pItem.quantity) || 0) * (parseFloat(pItem.customFactor || pItem.factor) || 1));
                            itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - qtyToRevert;
                            if (itemInv.qty < 0) itemInv.qty = 0;
                        }
                    });
                }
            }

            // Update actual stock in erp_inventory_v1 for new quantities (Feeding Warehouse: Qty * Factor)
            receivingItems.forEach(line => {
                const whObj = warehousesDb.find(w => w.id === line.warehouseId);
                const whName = whObj ? whObj.name : 'المخزن الرئيسي';
                
                let lineFactor = 1;
                if (line.factor !== undefined && parseFloat(line.factor) > 0) {
                    lineFactor = parseFloat(line.factor);
                } else if (line.customFactor !== undefined && parseFloat(line.customFactor) > 0) {
                    lineFactor = parseFloat(line.customFactor);
                }
                const baseQtyToAdd = (parseFloat(line.receiveQty) || 0) * lineFactor;

                // We add to actual stock level
                let itemInv = currentInvData.find(inv => inv.itemId === line.itemId && inv.warehouse === whName);
                if (itemInv) {
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) + baseQtyToAdd;
                } else {
                    currentInvData.push({
                        itemId: line.itemId,
                        warehouse: whName,
                        qty: baseQtyToAdd,
                        minQty: 10
                    });
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

            // Refresh from storage to prevent stale data overwrites (Concurrency fix)
            let freshPurchases = [];
            try {
                freshPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            } catch(e) {}

            if (editingGRNId) {
                // Edit mode: overwrite old GRN in freshPurchases
                const grnIdx = freshPurchases.findIndex(p => p.id === editingGRNId);
                if (grnIdx > -1) {
                    freshPurchases[grnIdx].items = newItems;
                    freshPurchases[grnIdx].date = new Date().toISOString();
                    freshPurchases[grnIdx].warehouse = firstWh;
                    freshPurchases[grnIdx].notes = `تعديل مذكرة استلام بضاعة للفاتورة ${currentInvoiceId}`;
                    freshPurchases[grnIdx].updatedBy = curUser.id;
                    freshPurchases[grnIdx].updatedByName = curUser.fullName || curUser.name;
                    freshPurchases[grnIdx].updatedAt = new Date().toISOString();
                }
                showToast(`تم تعديل الإذن ${editingGRNId} بنجاح`, 'success');
                if (window.logSystemAction) {
                    window.logSystemAction('تعديل استلام بضاعة', 'المشتريات / المخازن', editingGRNId, `تعديل إذن استلام بضاعة رقم ${editingGRNId} لفاتورة المشتريات ${currentInvoiceId}`);
                }
                editingGRNId = null; // Clear edit mode
            } else {
                // New mode: add new GRN
                freshPurchases.push(grnInv);
                showToast(`تم حفظ مذكرة الاستلام وتحديث المخزون الفعلي بنجاح`, 'success');
                if (window.logSystemAction) {
                    window.logSystemAction('استلام بضاعة', 'المشتريات / المخازن', grnInv.id, `إصدار إذن استلام بضاعة رقم ${grnInv.id} لفاتورة المشتريات ${currentInvoiceId}`);
                }
            }

            localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(freshPurchases));
            allPurchases = freshPurchases; // Update local memory reference
            if (window.ElsajanaAccountingBrain && typeof window.ElsajanaAccountingBrain.syncLogisticsReceiptAtomically === 'function') {
                const grnSyncItems = receivingItems.map(l => {
                    const f = resolveGrnElsajanaSteelFactor(l.unitName || l.unit, parseFloat(l.factor || l.customFactor) || 1);
                    return { itemId: l.itemId, itemName: getItemName(l.itemId), qtyReceived: l.receiveQty, baseQtyAdded: (parseFloat(l.receiveQty) || 0) * f, warehouseId: l.warehouseId };
                });
                const grnRec = { grnNo: grnInv.id, originalInvoiceNo: currentInvoiceId, date: grnInv.date.slice(0, 10), supplierName: getSupplierName(invData?.supplierId), items: grnSyncItems, notes: grnInv.notes };
                try {
                    const rList = JSON.parse(localStorage.getItem('erp_purchase_receipts_v1') || '[]');
                    rList.unshift(grnRec);
                    localStorage.setItem('erp_purchase_receipts_v1', JSON.stringify(rList.slice(0, 3000)));
                } catch (_) {}
                window.ElsajanaAccountingBrain.syncLogisticsReceiptAtomically(grnRec, invData, grnSyncItems);
            }
            
            if (window._isQuickHubReceiving) {
                calculatePendingGoods();
                renderPendingInvoices(false);
            } else {
                setTimeout(() => {
                    calculatePendingGoods();
                    renderPendingInvoices(false);
                    showHub(true);
                }, 450);
            }
        }

        function renderRecentGRNs(resetCount = true) {
            if (resetCount) receivedDisplayCount = 50;
            
            const container = document.getElementById('recent-grns-container');
            if (!container) return;

            // Load all GRNs for all invoices
            const grns = allPurchases.filter(p => p.isGRN);
            
            // Sort by date descending (newest first - safely)
            grns.sort((a, b) => {
                let tA = new Date(a.date).getTime();
                let tB = new Date(b.date).getTime();
                if(isNaN(tA)) tA = 0;
                if(isNaN(tB)) tB = 0;
                if (tA === tB) return (b.id || "").localeCompare(a.id || "");
                return tB - tA;
            });

            const rawSearch = document.getElementById('search-inv') ? document.getElementById('search-inv').value : '';
            const search = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic(rawSearch) : rawSearch.toLowerCase().trim();
            const fDateFrom = document.getElementById('grn-filter-date-from')?.value || '';
            const fDateTo   = document.getElementById('grn-filter-date-to')?.value || '';
            const fSup      = document.getElementById('grn-filter-supplier')?.value || '';
            const fWh       = document.getElementById('grn-filter-warehouse')?.value || '';
            const filteredGrns = grns.filter(grn => {
                let supId = grn.customer || grn.customerId || grn.supplierId;
                if (!supId && grn.originalInvoiceId) {
                    const orig = allPurchases.find(p => p.id === grn.originalInvoiceId || p.invoiceNo === grn.originalInvoiceId);
                    if (orig) supId = orig.customer || orig.customerId || orig.supplierId;
                }
                if (fSup && String(supId) !== String(fSup)) return false;
                if (fDateFrom || fDateTo) {
                    const dIso = (grn.date || '').slice(0, 10);
                    if (fDateFrom && dIso && dIso < fDateFrom) return false;
                    if (fDateTo && dIso && dIso > fDateTo) return false;
                }
                if (fWh) {
                    const hasWh = String(grn.warehouse) === String(fWh) || (grn.items || []).some(it => it.quantity > 0 && String(it.warehouseId) === String(fWh));
                    if (!hasWh) return false;
                }
                if (!search) return true;
                const normFn = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic : (s => String(s || '').toLowerCase());
                const supName = normFn(getSupplierName(supId));
                const grnId = normFn(grn.id || '');
                const origId = normFn(grn.originalInvoiceId || '');
                const notesStr = normFn(grn.notes || '');
                const itemNamesStr = normFn((grn.items || []).filter(i => i.quantity > 0).map(i => getItemName(i.itemId)).join(' '));
                return grnId.includes(search) || origId.includes(search) || supName.includes(search) || notesStr.includes(search) || itemNamesStr.includes(search);
            });
            
            if (currentHubView === 'received') {
                document.getElementById('hub-title-text').innerHTML = `الأذونات المستلمة`;
                const counterContainer = document.getElementById('hub-counter-badge');
                counterContainer.innerHTML = `<span class="counter-animate-abs" style="background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.45); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.18); font-family: 'Cairo', 'Outfit', 'Tajawal', sans-serif; white-space: nowrap;">${filteredGrns.length} إذن مستلم</span>`;
            }
            
            if (filteredGrns.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: #64748b; padding: 20px; font-size: 0.85rem;">
                        لا توجد عمليات استلام مطابقة في النظام.
                    </div>
                `;
                return;
            }

            let html = `
                <table class="pos-grid" style="width: 100%; font-size: 0.8rem; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الاستلام</th>
                            <th style="text-align:center; width:13%;">الفاتورة الأصلية</th>
                            <th style="text-align:right; width:15%;">المورد</th>
                            <th style="text-align:center;">التاريخ والوقت</th>
                            <th style="text-align:right;">الأصناف المستلمة</th>
                            <th style="text-align:center;">المستودع</th>
                            <th style="text-align:center;">إجمالي القيمة</th>
                            <th style="text-align:center;">بواسطة</th>
                            <th style="text-align:center; width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            const itemsToRender = filteredGrns.slice(0, receivedDisplayCount);

            itemsToRender.forEach(grn => {
                const isEditingThis = grn.id === editingGRNId;
                const rowBg = isEditingThis ? 'background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4);' : '';
                const editBadge = isEditingThis ? '<span style="background: #f59e0b; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: bold; margin-right: 8px;">قيد التعديل ✏️</span>' : '';

                let supId = grn.customer || grn.customerId || grn.supplierId;
                if (!supId && grn.originalInvoiceId) {
                    const orig = allPurchases.find(p => p.id === grn.originalInvoiceId || p.invoiceNo === grn.originalInvoiceId);
                    if (orig) supId = orig.customer || orig.customerId || orig.supplierId;
                }
                const supName = getSupplierName(supId);

                const positiveItems = (grn.items || []).filter(item => item.quantity > 0);
                let totalValue = 0;
                let grnWarehouses = new Set();

                let itemsSummary = positiveItems.map(item => {
                    const itemObj = itemsDb.find(i => i.id === item.itemId);
                    const name = itemObj ? itemObj.name : 'صنف غير معروف';
                    
                    totalValue += (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                    
                    if (item.warehouseId && item.warehouseId !== 'PENDING_RECEIPT') {
                        const whObj = warehousesDb.find(w => w.id === item.warehouseId);
                        if (whObj) grnWarehouses.add(whObj.name);
                        else grnWarehouses.add(item.warehouseId);
                    } else if (grn.warehouse) {
                        const whObj = warehousesDb.find(w => w.id === grn.warehouse);
                        if (whObj) grnWarehouses.add(whObj.name);
                        else grnWarehouses.add(grn.warehouse);
                    }

                    const notePart = item.notes ? ` <span style="color:#38bdf8; font-size:0.75rem; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); padding:1px 6px; border-radius:5px; font-weight:600;">📝 ${item.notes}</span>` : '';
                    return `${name} (${item.quantity} ${item.unitName || 'وحدة'})${notePart}`;
                }).join('<br>');
                
                let warehouseStr = Array.from(grnWarehouses).join('، ') || 'المخزن الرئيسي';

                let dateStr = '—';
                try {
                    dateStr = new Date(grn.date).toLocaleString('ar-EG-u-nu-latn', {
                        year: 'numeric', month: 'numeric', day: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                } catch(e) {}

                const creatorUser = usersDb.find(u => u.id === grn.createdBy || u.username === grn.createdBy);
                const creatorName = creatorUser ? creatorUser.name : grn.createdBy || 'غير معروف';

                html += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); ${rowBg}">
                        <td style="color: #38bdf8; font-weight: 800; font-family: 'Outfit';">${grn.id} ${editBadge}</td>
                        <td style="text-align:center; color: #fbbf24; font-weight: bold; font-family: 'Outfit'; cursor: pointer;" onclick="window.open('purchase.php?action=direct&inv='+encodeURIComponent('${grn.originalInvoiceId}'), '_blank')" title="انقر لفتح الفاتورة الأصلية بلمسة واحدة">${grn.originalInvoiceId}</td>
                        <td style="text-align:right; font-weight: 700; color: #fbbf24;">${supName}</td>
                        <td style="text-align:center; font-family: 'Outfit';">${dateStr}</td>
                        <td style="color:#e2e8f0; line-height: 1.4;">${itemsSummary}</td>
                        <td style="text-align:center; font-weight: 600; color: #94a3b8;">${warehouseStr}</td>
                        <td style="text-align:center; font-family: 'Outfit'; font-weight: 800; color: #fbbf24;">${fmtNum(totalValue)} SDG</td>
                        <td style="text-align:center; color:#a855f7; font-weight: 600;">${creatorName}</td>
                        <td style="text-align:center;">
                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                <button class="btn-action" style="padding: 5px 10px; min-height: 32px !important; font-size: 0.78rem; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 2px solid #60a5fa;" onclick="editGRN('${grn.id}')" title="تعديل إذن الاستلام">✏️ تعديل</button>
                                <button class="btn-action" style="padding: 5px 10px; min-height: 32px !important; font-size: 0.78rem; background: linear-gradient(135deg, #059669, #047857); border: 2px solid #34d399;" onclick="window.shareGrnByIdWhatsApp && window.shareGrnByIdWhatsApp('${grn.id}')" title="إرسال كشف الاستلام المفهرس عبر واتساب بضغطة واحدة">💬 واتساب</button>
                                <button class="btn-action" style="padding: 5px 10px; min-height: 32px !important; font-size: 0.78rem; margin-inline-start: 10px; background: linear-gradient(135deg, #ef4444, #dc2626); border: 2px solid #f87171;" onclick="deleteGRN('${grn.id}')" title="حذف إذن الاستلام وإعادة البضاعة لقيد الاستلام">🗑️ حذف</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;
            
            if (filteredGrns.length > receivedDisplayCount) {
                html += `
                <div style="text-align: center; margin-top: 15px; margin-bottom: 20px;">
                    <button class="btn-action" style="padding: 10px 30px; font-size: 1rem; border-radius: 30px; background: linear-gradient(135deg, #10b981, #059669);" onclick="loadMoreReceived()">عرض 50 إذن آخر ⬇️</button>
                </div>`;
            }
            container.innerHTML = html;
        }

        window.deleteGRN = function(grnId, skipConfirm) {
            if (!skipConfirm && typeof window.openGrnConfirmModal === 'function') {
                return window.openGrnConfirmModal({
                    title: '🗑️ تأكيد حذف إذن الاستلام',
                    message: `هل أنت متأكد من حذف إذن الاستلام (${grnId})؟ سيتم إعادة الكميات المستلمة فوراً كبضاعة قيد الاستلام.`,
                    confirmText: '🗑️ نعم، حذف الإذن',
                    isDanger: true,
                    onConfirm: () => window.deleteGRN(grnId, true)
                });
            }

            const grn = allPurchases.find(p => p.id === grnId);
            if (!grn) return;

            // Revert actual stock in erp_inventory_v1
            let currentInvData = [];
            try {
                currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            } catch(e) {}

            const positiveItems = grn.items.filter(item => item.quantity > 0);
            positiveItems.forEach(pItem => {
                const whObj = warehousesDb.find(w => w.id === pItem.warehouseId);
                const whName = whObj ? whObj.name : 'المخزن الرئيسي';

                let itemInv = currentInvData.find(inv => inv.itemId === pItem.itemId && inv.warehouse === whName);
                if (itemInv) {
                    let qtyToRevert = pItem.customBaseQty !== undefined ? parseFloat(pItem.customBaseQty) : ((parseFloat(pItem.quantity) || 0) * (parseFloat(pItem.customFactor || pItem.factor) || 1));
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - qtyToRevert;
                    if (itemInv.qty < 0) itemInv.qty = 0;
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

            const index = allPurchases.findIndex(p => p.id === grnId);
            if (index > -1) {
                allPurchases.splice(index, 1);
                localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
                if (window.logSystemAction) {
                    window.logSystemAction('حذف إذن استلام بضاعة', 'المشتريات / المخازن', grnId, `تم حذف إذن استلام البضاعة رقم ${grnId}`);
                }
                showToast('تم حذف عملية الاستلام وتحديث البضاعة المعلقة بنجاح', 'success');
                
                if (editingGRNId === grnId) {
                    editingGRNId = null;
                }

                calculatePendingGoods();
                renderPendingInvoices();
                
                // If we are currently editing an invoice, refresh the editor
                if (currentInvoiceId) {
                    openReceiptEditor(currentInvoiceId);
                } else {
                    showHub();
                }
            }
        };

        window.editGRN = function(grnId) {
            const grn = allPurchases.find(p => p.id === grnId);
            if (!grn) return;

            editingGRNId = grnId; // Enter edit mode

            // Recalculate pending goods ignoring this GRN
            calculatePendingGoods();
            renderPendingInvoices();
            
            // Open editor for the original invoice of this GRN
            openReceiptEditor(grn.originalInvoiceId);
            
            // Pre-fill target quantities, factors, split warehouses, and logistics fields from this GRN
            const positiveItems = (grn.items || []).filter(item => item.quantity > 0);
            setTimeout(() => {
                const wbEl = document.getElementById('grn-waybill-no'); if (wbEl) wbEl.value = grn.waybillNo || '';
                const trEl = document.getElementById('grn-truck-no'); if (trEl) trEl.value = grn.truckNo || '';
                const drEl = document.getElementById('grn-driver-info'); if (drEl) drEl.value = grn.driverInfo || '';
                const inEl = document.getElementById('grn-inspector-name'); if (inEl && grn.inspectorName) inEl.value = grn.inspectorName;
                window._currentGrnPhotoDataUrl = grn.inspectionPhoto || null;

                // Clear any default receiptLines quantities first
                receiptLines.forEach(l => {
                    l.receiveQty = 0;
                    l._filledInEdit = false;
                });

                positiveItems.forEach((pItem, pIdx) => {
                    const baseLine = receiptLines.find(l => l.itemId === pItem.itemId && !l._filledInEdit);
                    if (baseLine) {
                        baseLine._filledInEdit = true;
                        baseLine.receiveQty = pItem.quantity;
                        if (pItem.customFactor || pItem.factor) {
                            const f = parseFloat(pItem.customFactor || pItem.factor);
                            if (f > 0) {
                                baseLine.factor = f;
                                baseLine.customFactor = f;
                            }
                        }
                        baseLine.warehouseId = pItem.warehouseId;
                        baseLine.notes = pItem.notes || '';
                    } else {
                        const parentLine = receiptLines.find(l => l.itemId === pItem.itemId);
                        if (parentLine) {
                            const f = parseFloat(pItem.customFactor || pItem.factor || parentLine.factor) || 1;
                            receiptLines.push({
                                ...parentLine,
                                id: parentLine.id + '_split_' + pIdx,
                                receiveQty: pItem.quantity,
                                factor: f,
                                customFactor: f,
                                warehouseId: pItem.warehouseId || parentLine.warehouseId,
                                notes: pItem.notes || '',
                                isSplit: true,
                                _filledInEdit: true
                            });
                        }
                    }
                });
                renderReceiptLines();
                showToast(`تم تحميل إذن الاستلام ${grnId} للتعديل`, 'warning');
            }, 150);
        };

        document.getElementById('actual-warehouse').addEventListener('change', (e) => {
            const wh = e.target.value;
            receiptLines.forEach(line => {
                line.warehouseId = wh;
            });
            renderReceiptLines();
        });

        // --- Grid Formatting Context Menu ---
        function showGridContextMenu(e) {
            e.preventDefault();
            const menu = document.getElementById('grid-context-menu');
            menu.style.display = 'block';
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('grid-context-menu');
            if (menu && menu.style.display === 'block') {
                menu.style.display = 'none';
            }
        });

        let currentEditingTarget = 'all'; // 'all' or col key

        function openGridSettings(targetCol = 'all') {
            populateColSelectDropdown();
            currentEditingTarget = targetCol;
            document.getElementById('grid-target-col').value = targetCol;
            loadTargetSettingsToUI(targetCol);
            updateGridPreview();
            document.getElementById('grid-settings-modal').style.display = 'flex';
        }

        function populateColSelectDropdown() {
            const sel = document.getElementById('grid-target-col');
            sel.innerHTML = `
                <option value="all">🌐 جميع الأعمدة (ضبط شامل موحد)</option>
                <option value="rcvQty">✍️ 7️⃣ الكمية المستلمة (اكتب هنا) ⭐</option>
                <option value="origQty">1️⃣ كمية الشراء (المرجع)</option>
                <option value="item">2️⃣ اسم الصنف</option>
                <option value="unit">3️⃣ وحدة الشراء</option>
                <option value="remQty">4️⃣ الكمية المتبقية</option>
                <option value="factor">5️⃣ التعادل</option>
                <option value="pendingBase">6️⃣ الكمية بالوحدة الأساسية</option>
                <option value="rcvBase">8️⃣ الكمية بالوحدة المستلمة</option>
                <option value="warehouse">9️⃣ المستودع المُستلِم</option>
            `;
        }

        function onTargetColChange() {
            const target = document.getElementById('grid-target-col').value;
            currentEditingTarget = target;
            loadTargetSettingsToUI(target);
            updateGridPreview();
        }

        function loadTargetSettingsToUI(target) {
            const prefs = getStoredGridPrefs();
            let p = {};
            if (target === 'all') {
                p = prefs.global || DEFAULT_GRID_PREFS.global;
            } else {
                p = (prefs.cols && prefs.cols[target]) ? prefs.cols[target] : {};
                const g = prefs.global || DEFAULT_GRID_PREFS.global;
                if (!p.font) p.font = g.font;
                if (!p.size) p.size = g.size;
                if (!p.weight) p.weight = g.weight;
                if (!p.color) p.color = g.color;
                if (!p.align) p.align = g.align;
                if (p.decimals === undefined) p.decimals = g.decimals;
                if (p.thousands === undefined) p.thousands = g.thousands;
            }

            const currentFont = p.font || 'Tajawal';
            const currentSize = String(p.size || '16');
            const currentWeight = String(p.weight || '700');
            const currentColor = p.color || '#ffffff';

            document.getElementById('grid-font-family').value = currentFont;
            
            const sizeSelect = document.getElementById('grid-font-size');
            sizeSelect.value = currentSize;
            if (!sizeSelect.value) {
                const opt = document.createElement('option');
                opt.value = currentSize;
                opt.text = currentSize + 'px';
                sizeSelect.appendChild(opt);
                sizeSelect.value = currentSize;
            }
            document.getElementById('grid-font-size-custom').value = currentSize;

            document.getElementById('grid-font-weight').value = currentWeight;
            document.getElementById('grid-text-color').value = currentColor;
            document.getElementById('grid-text-color-hex').value = currentColor;
            document.getElementById('grid-decimals').value = (p.decimals !== undefined) ? p.decimals : 0;
            document.getElementById('grid-thousands-sep').checked = (p.thousands !== false);
            document.getElementById('grid-text-align').value = p.align || 'center';
        }

        function syncFontSizeFromSelect() {
            const s = document.getElementById('grid-font-size').value;
            document.getElementById('grid-font-size-custom').value = s;
            updateGridPreview();
        }

        function syncFontSizeFromCustom() {
            const val = parseInt(document.getElementById('grid-font-size-custom').value);
            if (!isNaN(val) && val >= 10 && val <= 48) {
                document.getElementById('grid-font-size').value = String(val);
                updateGridPreview();
            }
        }

        function syncColorFromPicker(val) {
            document.getElementById('grid-text-color-hex').value = val;
            updateGridPreview();
        }

        function syncColorFromHex(val) {
            if (/^#[0-9A-F]{6}$/i.test(val) || /^#[0-9A-F]{3}$/i.test(val)) {
                document.getElementById('grid-text-color').value = val;
                updateGridPreview();
            }
        }

        function setPresetColor(c) {
            document.getElementById('grid-text-color').value = c;
            document.getElementById('grid-text-color-hex').value = c;
            updateGridPreview();
        }

        function updateGridPreview() {
            const p = document.getElementById('grid-preview-box');
            if (!p) return;
            const font = document.getElementById('grid-font-family').value;
            const size = document.getElementById('grid-font-size-custom').value || document.getElementById('grid-font-size').value || '16';
            const weight = document.getElementById('grid-font-weight').value;
            const color = document.getElementById('grid-text-color').value;
            const decimals = parseInt(document.getElementById('grid-decimals').value) || 0;
            const thousands = document.getElementById('grid-thousands-sep').checked;
            const align = document.getElementById('grid-text-align').value;

            let sampleNum = 12500.75;
            let formatted = thousands 
                ? sampleNum.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
                : sampleNum.toFixed(decimals);

            if (currentEditingTarget === 'item') formatted = '📦 سيخ 4 لينيا الأسعد';
            else if (currentEditingTarget === 'unit') formatted = 'طن عددي';
            else if (currentEditingTarget === 'warehouse') formatted = 'المخزن الرئيسي';
            else if (currentEditingTarget === 'rcvQty') {
                formatted = '✍️ 250 (الكمية المستلمة)';
                p.style.border = '2px solid #38bdf8';
                p.style.boxShadow = '0 0 15px rgba(56, 189, 248, 0.4)';
            } else {
                p.style.border = '1px solid rgba(255,255,255,0.1)';
                p.style.boxShadow = 'none';
            }

            p.style.fontFamily = `'${font}', 'Tajawal', sans-serif`;
            p.style.fontSize = size + 'px';
            p.style.fontWeight = weight;
            p.style.color = color;
            p.style.textAlign = align;
            p.textContent = formatted;
        }

        async function saveGridSettingsToDB(prefs) {
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                const userTargets = new Set();
                if (u.id) userTargets.add(String(u.id));
                if (u.username) userTargets.add(String(u.username));
                userTargets.add('default_user');

                const savePromises = Array.from(userTargets).map(uid => 
                    fetch('api/save_user_settings.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            user_id: uid,
                            setting_key: 'erp_grn_col_prefs_v2',
                            setting_value: prefs
                        })
                    })
                );

                await Promise.all(savePromises);
                console.log('✅ Grid preferences permanently saved to MySQL database for targets:', Array.from(userTargets));
            } catch(e) {
                console.error('❌ Error saving grid prefs to DB:', e);
            }
        }

        async function saveGridSettings() {
            const prefs = getStoredGridPrefs();
            const font = document.getElementById('grid-font-family').value;
            const size = document.getElementById('grid-font-size-custom').value || document.getElementById('grid-font-size').value || '16';
            const weight = document.getElementById('grid-font-weight').value;
            const color = document.getElementById('grid-text-color').value;
            const decimals = parseInt(document.getElementById('grid-decimals').value) || 0;
            const thousands = document.getElementById('grid-thousands-sep').checked;
            const align = document.getElementById('grid-text-align').value;

            const targetObj = { font, size, weight, color, decimals, thousands, align };

            if (currentEditingTarget === 'all') {
                prefs.global = targetObj;
            } else {
                if (!prefs.cols) prefs.cols = {};
                prefs.cols[currentEditingTarget] = targetObj;
            }

            localStorage.setItem('erp_grn_col_prefs_v2', JSON.stringify(prefs));
            document.getElementById('grid-settings-modal').style.display = 'none';
            renderReceiptLines();

            // Permanent database persistence
            await saveGridSettingsToDB(prefs);
            showToast('تم حفظ التنسيق نهائياً في قاعدة البيانات بنجاح! 💾', 'success');
        }

        async function applyCurrentToAllCols(confirmedByModal) {
            if (!confirmedByModal && typeof window.openGrnConfirmModal === 'function') {
                return window.openGrnConfirmModal({
                    title: '🎨 تعميم تنسيق الأعمدة',
                    message: 'هل تريد تعميم هذه الإعدادات على جميع أعمدة الجدول معاً وحفظها نهائياً؟',
                    confirmText: '✅ نعم، تعميم وحفظ',
                    isDanger: false,
                    onConfirm: () => applyCurrentToAllCols(true)
                });
            }
            const font = document.getElementById('grid-font-family').value;
            const size = document.getElementById('grid-font-size-custom').value || document.getElementById('grid-font-size').value || '16';
            const weight = document.getElementById('grid-font-weight').value;
            const color = document.getElementById('grid-text-color').value;
            const decimals = parseInt(document.getElementById('grid-decimals').value) || 0;
            const thousands = document.getElementById('grid-thousands-sep').checked;
            const align = document.getElementById('grid-text-align').value;

            const targetObj = { font, size, weight, color, decimals, thousands, align };
            const prefs = {
                global: targetObj,
                cols: {
                    origQty:     { ...targetObj },
                    item:        { ...targetObj, align: align === 'center' ? 'right' : align },
                    unit:        { ...targetObj },
                    remQty:      { ...targetObj },
                    factor:      { ...targetObj },
                    pendingBase: { ...targetObj },
                    rcvQty:      { ...targetObj },
                    rcvBase:     { ...targetObj },
                    warehouse:   { ...targetObj }
                }
            };
            localStorage.setItem('erp_grn_col_prefs_v2', JSON.stringify(prefs));
            document.getElementById('grid-settings-modal').style.display = 'none';
            renderReceiptLines();

            // Permanent database persistence
            await saveGridSettingsToDB(prefs);
            showToast('تم تعميم الضبط وحفظه نهائياً في قاعدة البيانات بنجاح! 💾', 'success');
        }

        async function resetGridSettingsToDefault(confirmedByModal) {
            if (!confirmedByModal && typeof window.openGrnConfirmModal === 'function') {
                return window.openGrnConfirmModal({
                    title: '🔄 استعادة الضبط الافتراضي',
                    message: 'هل تريد استعادة الضبط الافتراضي لجميع أعمدة جدول الاستلام؟',
                    confirmText: '🔄 استعادة الافتراضي',
                    isDanger: false,
                    onConfirm: () => resetGridSettingsToDefault(true)
                });
            }
            localStorage.removeItem('erp_grn_col_prefs_v2');
            
            const defaultPrefs = DEFAULT_GRID_PREFS;
            await saveGridSettingsToDB(defaultPrefs);

            document.getElementById('grid-settings-modal').style.display = 'none';
            renderReceiptLines();
            showToast('تمت استعادة الضبط الافتراضي وحفظه في قاعدة البيانات بنجاح!', 'info');
        }
