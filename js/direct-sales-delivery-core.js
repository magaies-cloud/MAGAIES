    (function initWorldClassSalesDeliverySuite() {
        window.gdnCurrentFilter = 'pending'; // 'pending' | 'delivered' | 'all'
        window.gdnActiveRowIndex = -1;
        let qrVideoStream = null;
        let qrScanInterval = null;

        function parseInvoiceDateTimeParts(inv) {
            const rawDt = String(inv.createdAt || inv.timestamp || inv.time || inv.deliveredAt || inv.date || '');
            const dateStr = String(inv.date || rawDt.slice(0, 10) || new Date().toISOString().slice(0, 10));
            let timeStr = '';
            const timeMatch = rawDt.match(/(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM|ص|م)?)/i);
            if (timeMatch) {
                timeStr = timeMatch[1];
            } else if (inv.time) {
                timeStr = inv.time;
            } else {
                timeStr = '12:00 PM';
            }
            return { dateStr, timeStr };
        }

        function getWarehousesList() {
            const whs = JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]');
            return whs.length > 0 ? whs : [{ id: 1, name: 'المخزن الرئيسي' }];
        }

        function resolveItemMeta(it, defaultWhObj) {
            const erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
            const warehouses = getWarehousesList();
            const itemId = it.itemId || it.id || '';
            const rawItemName = String(it.itemName || it.name || '').trim();

            // 1. Exact or smart fuzzy match in erp_items_v1
            let master = erpItems.find(x =>
                String(x.id) === String(itemId) ||
                (rawItemName && String(x.name || '').trim() === rawItemName)
            );
            if (!master && rawItemName) {
                const normSearch = rawItemName.replace(/ه/g, 'ة').toLowerCase();
                master = erpItems.find(x => {
                    const n = String(x.name || '').replace(/ه/g, 'ة').toLowerCase();
                    return n && (n.includes(normSearch) || normSearch.includes(n));
                });
            }

            const name = rawItemName || (master ? master.name : (itemId || 'صنف عام'));
            const unit = String(it.selectedUnit || it.unit || it.unitName || (master ? (master.unit || master.baseUnit || 'قطعة') : 'قطعة')).trim();
            const code = it.code || it.itemCode || (master ? (master.code || master.barcode || itemId) : itemId);

            // 2. Determine true base unit (الوحدة الصغرى مثل سيخة / كيس / حبة) & ton/unit factors
            const normName = name.toLowerCase();
            const normUnit = unit.replace(/ه/g, 'ة').toLowerCase();
            const isSteel = normName.includes('سيخ') || normName.includes('لين') || normName.includes('حديد') || normName.includes('تسليح') || normName.includes('مللي') || normName.includes('ملم');
            const isCement = normName.includes('أسمنت') || normName.includes('اسمنت');
            const isBrick = normName.includes('طوب') || normName.includes('بلوك');

            let baseUnit = (master && (master.baseUnit || master.mainUnit || master.unit)) ? String(master.baseUnit || master.mainUnit || master.unit).trim() : '';
            if (isSteel || normBackupIsTon(baseUnit)) {
                if (isSteel) baseUnit = 'سيخة';
                else if (isCement) baseUnit = 'كيس';
                else if (isBrick) baseUnit = 'حبة';
                else if (!baseUnit || normBackupIsTon(baseUnit)) baseUnit = 'وحدة';
            }
            if (!baseUnit) baseUnit = 'وحدة';
            if (baseUnit === 'سيخه') baseUnit = 'سيخة';

            function normBackupIsTon(uStr) {
                const s = String(uStr || '').toLowerCase();
                return s.includes('طن') || s.includes('شيل') || s.includes('ربط') || s.includes('قلاب') || s.includes('ألف') || s.includes('الف');
            }

            // 3. Extract units matrix from master item + Steel/Construction standard factors
            let uList = [];
            if (master && typeof window.extractItemUnits === 'function') {
                try { uList = window.extractItemUnits(master) || []; } catch (e) {}
            }
            if (master && Array.isArray(master.unitsArray)) {
                master.unitsArray.forEach(u => {
                    if (u && (u.name || u.unitName) && !uList.some(x => (x.name || x.unitName) === (u.name || u.unitName))) {
                        uList.push(u);
                    }
                });
            }
            if (master && Array.isArray(master.units)) {
                master.units.forEach(u => {
                    if (u && (u.name || u.unitName) && !uList.some(x => (x.name || x.unitName) === (u.name || u.unitName))) {
                        uList.push(u);
                    }
                });
            }

            // Built-in Steel & Materials reference factors (طن عددي / طن عادي / طن وزني / شيلة)
            let countTonFactor = 0;
            let weightTonFactor = 0;
            let bundleStdFactor = 0;
            if (isSteel) {
                if (normName.includes('2.5') || normName.includes('8 مل') || normName.includes('8مل')) {
                    countTonFactor = 200; weightTonFactor = 222; bundleStdFactor = 20;
                } else if (normName.includes('3') || normName.includes('ثلاث') || normName.includes('10 مل') || normName.includes('10مل')) {
                    countTonFactor = 135; weightTonFactor = 166; bundleStdFactor = 15;
                } else if (normName.includes('4') || normName.includes('اربع') || normName.includes('أربع') || normName.includes('12 مل') || normName.includes('12مل')) {
                    countTonFactor = 92; weightTonFactor = 110; bundleStdFactor = 10;
                } else if (normName.includes('5') || normName.includes('خمس') || normName.includes('16 مل') || normName.includes('16مل')) {
                    countTonFactor = 52; weightTonFactor = 59; bundleStdFactor = 5;
                } else if (normName.includes('6') || normName.includes('ست') || normName.includes('20 مل') || normName.includes('20مل')) {
                    countTonFactor = 33; weightTonFactor = 40; bundleStdFactor = 3;
                } else if (normName.includes('7') || normName.includes('22 مل')) {
                    countTonFactor = 28; weightTonFactor = 33; bundleStdFactor = 3;
                } else if (normName.includes('8') || normName.includes('25 مل')) {
                    countTonFactor = 22; weightTonFactor = 26; bundleStdFactor = 2;
                } else {
                    countTonFactor = 135; weightTonFactor = 166; bundleStdFactor = 15;
                }
                // Check if master has custom factors for طن عددي or طن وزني
                const mCount = uList.find(u => String(u.name || u.unitName || '').includes('عددي') || String(u.name || u.unitName || '').includes('عادي'));
                if (mCount && parseFloat(mCount.factor) > 1) countTonFactor = parseFloat(mCount.factor);
                const mWeight = uList.find(u => String(u.name || u.unitName || '').includes('وزني'));
                if (mWeight && parseFloat(mWeight.factor) > 1) weightTonFactor = parseFloat(mWeight.factor);
            } else if (isCement) {
                countTonFactor = 20; weightTonFactor = 20;
            } else if (isBrick) {
                countTonFactor = 1000; weightTonFactor = 1000;
            }

            // 4. Resolve exact factor for the invoice's selected unit
            let factor = parseFloat(it.customFactor ?? it.factor ?? it.unitFactor ?? 0);
            const isBulkOrTonUnit = normBackupIsTon(normUnit) || normUnit.includes('كرتون') || normUnit.includes('طرد') || normUnit.includes('جوال') || normUnit.includes('شوال');

            // Never allow factor=1 if the unit is a Ton/Bundle unit (e.g. طن عددي / طن عادي / طن وزني / شيلة)
            if (!factor || isNaN(factor) || factor <= 0 || (isBulkOrTonUnit && factor <= 1)) {
                factor = 1;
                const foundU = uList.find(u => {
                    const un = String(u.name || u.unitName || '').replace(/ه/g, 'ة').trim().toLowerCase();
                    return un === normUnit || (normUnit.includes('عادي') && un.includes('عددي')) || (normUnit.includes('عددي') && un.includes('عادي'));
                });
                if (foundU && parseFloat(foundU.factor) > 1) {
                    factor = parseFloat(foundU.factor);
                } else if (isSteel) {
                    if (normUnit.includes('وزني')) factor = weightTonFactor || 166;
                    else if (normUnit.includes('طن') || normUnit.includes('عددي') || normUnit.includes('عادي')) factor = countTonFactor || 135;
                    else if (normUnit.includes('شيل') || normUnit.includes('ربط')) factor = bundleStdFactor || 15;
                } else if (isCement && normUnit.includes('طن')) {
                    factor = 20;
                } else if (isBrick && (normUnit.includes('ألف') || normUnit.includes('الف') || normUnit.includes('طن'))) {
                    factor = 1000;
                } else if (normUnit.includes('طن')) {
                    const anyTonU = uList.find(u => String(u.name || u.unitName || '').includes('طن') && parseFloat(u.factor) > 1);
                    if (anyTonU) factor = parseFloat(anyTonU.factor);
                    else if (master && parseFloat(master.bundleFactor) > 1) factor = parseFloat(master.bundleFactor);
                    else if (baseUnit.includes('كجم') || baseUnit.includes('كيلو')) factor = 1000;
                    else if (baseUnit.includes('كيس') || baseUnit.includes('شوال') || baseUnit.includes('جوال')) factor = 20;
                }
            }

            const qty = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            const conv = (master && parseFloat(master.conversionFactor) > 0) ? parseFloat(master.conversionFactor) : 1;
            const savedBase = parseFloat(it.customBaseQty ?? it.baseQty ?? 0) || 0;
            const computedBase = qty * factor * conv;
            // If savedBase was mistakenly equal to qty (when factor > 1), use computedBase
            const baseQty = (factor > 1 && savedBase <= qty) ? computedBase : (savedBase > 0 ? savedBase : computedBase);

            const price = parseFloat(it.price ?? it.unitPrice ?? it.rate ?? (master ? (master.sellingPrice || master.price || 0) : 0)) || 0;
            const total = parseFloat(it.total ?? it.grossTotal ?? it.subtotal ?? (qty * price)) || (qty * price);

            const rawWh = it.warehouseId || it.warehouse || (defaultWhObj ? defaultWhObj.id : 1);
            const whObj = warehouses.find(w => String(w.id) === String(rawWh) || String(w.name).trim() === String(rawWh).trim()) || defaultWhObj || warehouses[0];

            // Build human-readable unit & ton breakdown (طن عددي / طن وزني / كمية بالوحدات)
            const hasMultiUnits = factor > 1 || unit !== baseUnit;
            const unitEquivLabel = hasMultiUnits
                ? `${ (qty * factor).toLocaleString('en-US', { maximumFractionDigits: 2 }) } ${baseUnit}`
                : `${ qty.toLocaleString('en-US', { maximumFractionDigits: 2 }) } ${baseUnit}`;

            let tonRefHint = '';
            if (isSteel && countTonFactor > 1) {
                tonRefHint = `طن عددي = ${countTonFactor} ${baseUnit}` + (weightTonFactor > 1 ? ` | طن وزني = ${weightTonFactor} ${baseUnit}` : '');
            } else if (factor > 1) {
                tonRefHint = `1 ${unit} = ${factor} ${baseUnit}`;
            }

            return {
                name,
                unit,
                baseUnit,
                code,
                master,
                factor,
                qty,
                baseQty,
                price,
                total,
                hasMultiUnits,
                unitEquivLabel,
                tonRefHint,
                countTonFactor,
                weightTonFactor,
                whId: whObj ? whObj.id : rawWh,
                whName: whObj ? whObj.name : String(rawWh || 'المخزن الرئيسي')
            };
        }
        window.resolveItemMeta = resolveItemMeta;

        window.openDeliveryForInvoiceFromHub = function(invoiceNo, focusItemIdx = 0, focusUnitMode = 'primary') {
            if (!invoiceNo) return;
            if (typeof window.openWorldClassDeliveryModal === 'function') {
                window.openWorldClassDeliveryModal(String(invoiceNo), focusItemIdx, focusUnitMode);
            }
        };

        window.gdnHubState = {
            whFilter: 'all',
            dateFilter: 'all',
            pageLimit: 100
        };

        function readPersistentGdnJson(keysList) {
            for (const k of keysList) {
                try {
                    const v = localStorage.getItem(k);
                    if (v) {
                        const parsed = JSON.parse(v);
                        if (parsed && typeof parsed === 'object') return parsed;
                    }
                } catch (e) {}
            }
            try {
                for (let i = 0; i < localStorage.length; i++) {
                    const sk = localStorage.key(i);
                    if (!sk) continue;
                    if (keysList.some(k => sk === k || sk.endsWith('_' + k))) {
                        const raw = localStorage.getItem(sk);
                        if (raw) {
                            const parsed = JSON.parse(raw);
                            if (parsed && typeof parsed === 'object') return parsed;
                        }
                    }
                }
            } catch (e) {}
            return null;
        }

        function writePersistentGdnJson(primaryKey, legacyKey, obj) {
            const jsonStr = JSON.stringify(obj);
            try { localStorage.setItem(primaryKey, jsonStr); } catch (e) {}
            try { if (legacyKey) localStorage.setItem(legacyKey, jsonStr); } catch (e) {}
            try {
                if (typeof window.getTenantKey === 'function') {
                    localStorage.setItem(window.getTenantKey(primaryKey), jsonStr);
                    if (legacyKey) localStorage.setItem(window.getTenantKey(legacyKey), jsonStr);
                }
            } catch (e) {}
        }

        function getGdnHubColsConfig() {
            const saved = readPersistentGdnJson(['magaies_gdn_hub_cols_v2', 'erp_gdn_hub_cols_v1']);
            if (saved && typeof saved === 'object') {
                return {
                    colInvNo: saved.colInvNo !== false,
                    colDateTime: saved.colDateTime !== false,
                    colCustomer: saved.colCustomer !== false,
                    colItems: saved.colItems !== false,
                    colItemWh: saved.colItemWh !== false,
                    colItemSpec: saved.colItemSpec !== false,
                    colItemBaseUnit: saved.colItemBaseUnit !== false,
                    colWarehouse: saved.colWarehouse !== false,
                    colActions: saved.colActions !== false
                };
            }
            return {
                colInvNo: true,
                colDateTime: true,
                colCustomer: true,
                colItems: true,
                colItemWh: true,
                colItemSpec: true,
                colItemBaseUnit: true,
                colWarehouse: true,
                colActions: true
            };
        }

        window.toggleGdnHubCol = function(colKey, isChecked) {
            const cfg = getGdnHubColsConfig();
            cfg[colKey] = !!isChecked;
            writePersistentGdnJson('magaies_gdn_hub_cols_v2', 'erp_gdn_hub_cols_v1', cfg);
            window.renderWorldClassDeliveryHub();
        };

        window.lockAndSaveGdnHubCols = function() {
            const cfg = getGdnHubColsConfig();
            document.querySelectorAll('#gdn-hub-cols-drawer input[data-hub-col]').forEach(chk => {
                cfg[chk.dataset.hubCol] = !!chk.checked;
            });
            writePersistentGdnJson('magaies_gdn_hub_cols_v2', 'erp_gdn_hub_cols_v1', cfg);
            const drawer = document.getElementById('gdn-hub-cols-drawer');
            if (drawer) drawer.style.display = 'none';
            window.renderWorldClassDeliveryHub();
            if (typeof showToast === 'function') showToast('✅ تم تثبيت وحفظ ضبط الكولومات نهائياً ولن تتغير إلا عند تعديلها يدوياً', 'success');
        };

        window.setGdnHubFilterState = function(key, val) {
            window.gdnHubState[key] = val;
            window.renderWorldClassDeliveryHub();
        };

        function getGdnOptionalColsConfig() {
            const saved = readPersistentGdnJson(['magaies_gdn_optional_cols_v3', 'erp_gdn_optional_cols_v2', 'erp_gdn_optional_cols_v1']);
            if (saved && typeof saved === 'object') {
                return {
                    showWarehouse: saved.showWarehouse !== false,
                    showItemName: saved.showItemName !== false,
                    showItemSpec: saved.showItemSpec !== false,
                    showPrimaryUnit: saved.showPrimaryUnit !== false,
                    showOrderedQty: saved.showOrderedQty !== false,
                    showUnitsCount: saved.showUnitsCount !== false,
                    showPriceTotal: saved.showPriceTotal === true,
                    showAvailStock: saved.showAvailStock !== false,
                    showReceivePrimary: saved.showReceivePrimary !== false,
                    showReceiveBase: saved.showReceiveBase !== false,
                    showNetSummary: saved.showNetSummary !== false,
                    colWidthScale: saved.colWidthScale || 'wide'
                };
            }
            return {
                showWarehouse: true,
                showItemName: true,
                showItemSpec: true,
                showPrimaryUnit: true,
                showOrderedQty: true,
                showUnitsCount: true,
                showPriceTotal: false,
                showAvailStock: true,
                showReceivePrimary: true,
                showReceiveBase: true,
                showNetSummary: true,
                colWidthScale: 'wide'
            };
        }

        function refreshModalWithDraftPreserved() {
            const draftQtys = {};
            const draftWhs = {};
            document.querySelectorAll('.gdn-modal-deliver-qty').forEach(inp => {
                draftQtys[inp.dataset.itemIdx] = inp.value;
            });
            document.querySelectorAll('.gdn-modal-item-wh').forEach(sel => {
                draftWhs[sel.dataset.itemIdx] = sel.value;
            });
            if (typeof window.renderModalOriginColumnsTable === 'function') {
                window.renderModalOriginColumnsTable(draftQtys, draftWhs);
            }
        }

        window.toggleGdnOptionalCol = function(colKey, isChecked) {
            const cfg = getGdnOptionalColsConfig();
            cfg[colKey] = !!isChecked;
            writePersistentGdnJson('magaies_gdn_optional_cols_v3', 'erp_gdn_optional_cols_v2', cfg);
            refreshModalWithDraftPreserved();
        };

        window.setGdnModalWidthScale = function(scaleMode) {
            const cfg = getGdnOptionalColsConfig();
            cfg.colWidthScale = scaleMode;
            writePersistentGdnJson('magaies_gdn_optional_cols_v3', 'erp_gdn_optional_cols_v2', cfg);
            refreshModalWithDraftPreserved();
        };

        window.applyGdnColPreset = function(preset) {
            const cfg = getGdnOptionalColsConfig();
            if (preset === 'storekeeper_focus') {
                cfg.showWarehouse = true;
                cfg.showItemName = true;
                cfg.showItemSpec = false;
                cfg.showPrimaryUnit = false;
                cfg.showOrderedQty = true;
                cfg.showUnitsCount = true;
                cfg.showPriceTotal = false;
                cfg.showAvailStock = false;
                cfg.showReceivePrimary = true;
                cfg.showReceiveBase = true;
                cfg.showNetSummary = true;
                cfg.colWidthScale = 'ultrawide';
            } else {
                cfg.showWarehouse = true;
                cfg.showItemName = true;
                cfg.showItemSpec = true;
                cfg.showPrimaryUnit = true;
                cfg.showOrderedQty = true;
                cfg.showUnitsCount = true;
                cfg.showAvailStock = true;
                cfg.showReceivePrimary = true;
                cfg.showReceiveBase = true;
                cfg.showNetSummary = true;
            }
            writePersistentGdnJson('magaies_gdn_optional_cols_v3', 'erp_gdn_optional_cols_v2', cfg);
            refreshModalWithDraftPreserved();
        };

        window.lockAndSaveGdnModalCols = function() {
            const cfg = getGdnOptionalColsConfig();
            const mapIds = {
                'chk-gdn-col-wh': 'showWarehouse',
                'chk-gdn-col-item': 'showItemName',
                'chk-gdn-col-spec': 'showItemSpec',
                'chk-gdn-col-punit': 'showPrimaryUnit',
                'chk-gdn-col-oqty': 'showOrderedQty',
                'chk-gdn-col-units': 'showUnitsCount',
                'chk-gdn-col-avail': 'showAvailStock',
                'chk-gdn-col-price': 'showPriceTotal',
                'chk-gdn-col-recp': 'showReceivePrimary',
                'chk-gdn-col-recb': 'showReceiveBase',
                'chk-gdn-col-net': 'showNetSummary'
            };
            Object.keys(mapIds).forEach(id => {
                const el = document.getElementById(id);
                if (el) cfg[mapIds[id]] = !!el.checked;
            });
            writePersistentGdnJson('magaies_gdn_optional_cols_v3', 'erp_gdn_optional_cols_v2', cfg);
            const drawer = document.getElementById('gdn-modal-cols-drawer');
            if (drawer) drawer.style.display = 'none';
            refreshModalWithDraftPreserved();
            if (typeof showToast === 'function') showToast('✅ تم تثبيت وحفظ ضبط كولومات البطاقة نهائياً ولن تتغير إلا عند تعديلها يدوياً', 'success');
        };

        window.filterModalOriginRows = function(query) {
            const q = String(query || '').trim().toLowerCase();
            document.querySelectorAll('.gdn-origin-columns-table tbody tr').forEach(tr => {
                if (!q) {
                    tr.style.display = '';
                    return;
                }
                const txt = (tr.textContent || '').toLowerCase();
                tr.style.display = txt.includes(q) ? '' : 'none';
            });
        };

        function resolveCustomerName(inv) {
            if (inv.customerName && inv.customerName !== 'walk-in') return inv.customerName;
            const cId = inv.customer || inv.customerId || '';
            if (!cId || cId === 'walk-in') return 'عميل نقدي';
            const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
            const found = custs.find(c => c && String(c.id) === String(cId));
            return found ? found.name : cId;
        }

        function getAvailableStockForItem(item, whId) {
            const { name, master } = resolveItemMeta(item);
            if (master && master.actualStock && master.actualStock[whId] !== undefined) {
                return parseFloat(master.actualStock[whId]) || 0;
            }
            if (master && master.stock && master.stock[whId] !== undefined) {
                return parseFloat(master.stock[whId]) || 0;
            }
            const invList = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
            const entry = invList.find(x => (String(x.itemId || x.id) === String(item.itemId || item.id) || String(x.name || '').trim() === name.trim()) && (String(x.warehouseId || x.warehouse || 1) === String(whId)));
            if (entry) return parseFloat(entry.qty ?? entry.quantity ?? 0) || 0;
            return master && master.quantity !== undefined ? (parseFloat(master.quantity) || 0) : null;
        }

        // Upgrade Delivery Hub Header, Table Structure & Ultra-Wide Modal
        function upgradeDeliveryHubDOM() {
            const hubView = document.getElementById('delivery-hub-view');
            if (!hubView) return;

            if (localStorage.getItem('erp_delivery_sunlight_mode') === '1') {
                document.body.classList.add('gdn-sunlight-mode');
            }

            const warehouses = getWarehousesList();
            const hubCols = getGdnHubColsConfig();
            const whFilterOpts = `<option value="all">🏢 كل المخازن (تركيز شامل)</option>` +
                warehouses.map(w => `<option value="${w.id}" ${String(window.gdnHubState.whFilter) === String(w.id) ? 'selected' : ''}>🏢 ${w.name}</option>`).join('');

            // 1. Inject High-Speed 1000+ Vouchers Search, Filter & Column Controller Bar
            const headerDiv = hubView.querySelector('.neo-card-title') || hubView.querySelector('.hub-header');
            if (headerDiv && (!document.getElementById('gdn-world-class-toolbar') || headerDiv.dataset.hubToolbarVersion !== '3')) {
                headerDiv.dataset.hubToolbarVersion = '3';
                const oldBar = document.getElementById('gdn-world-class-toolbar');
                if (oldBar) oldBar.remove();

                const extraBar = document.createElement('div');
                extraBar.id = 'gdn-world-class-toolbar';
                extraBar.style.cssText = 'margin: 10px 0 12px 0; display: flex; flex-direction: column; gap: 8px;';
                extraBar.innerHTML = `
                    <!-- Row 1: KPIs + Super Search (1000+ Vouchers) + Warehouse Focus + QR Scanner -->
                    <div style="background:linear-gradient(135deg, rgba(15,23,42,0.92), rgba(6,78,59,0.28)); border:1.5px solid rgba(16,185,129,0.35); padding:12px 16px; border-radius:14px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span style="background:rgba(245,158,11,0.16); border:1px solid rgba(245,158,11,0.4); color:#fbbf24; padding:6px 12px; border-radius:9px; font-size:0.84rem; font-weight:900;">
                                ⏳ قيد التسليم: <strong id="gdn-kpi-pending">0</strong>
                            </span>
                            <span style="background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.4); color:#34d399; padding:6px 12px; border-radius:9px; font-size:0.84rem; font-weight:900;">
                                ✅ المسلمة: <strong id="gdn-kpi-delivered">0</strong>
                            </span>
                            <span style="background:rgba(56,189,248,0.16); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; padding:6px 12px; border-radius:9px; font-size:0.84rem; font-weight:900;">
                                📊 الكميات المتبقية: <strong id="gdn-kpi-qty">0</strong>
                            </span>
                            <span id="gdn-kpi-match-count" style="background:rgba(168,85,247,0.16); border:1px solid rgba(168,85,247,0.4); color:#d8b4fe; padding:6px 12px; border-radius:9px; font-size:0.82rem; font-weight:900;">
                                🔍 النتائج: 0
                            </span>
                        </div>

                        <!-- Super Search & Mobile QR Input for 1000+ Vouchers -->
                        <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:280px; max-width:650px;">
                            <button onclick="openDeliveryQrScannerModal()" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #059669, #0284c7); color:#fff; border:1px solid #34d399; white-space:nowrap; padding:8px 14px; box-shadow:0 0 15px rgba(16,185,129,0.35);">
                                <span id="gdn-hub-bridge-dot" style="width:8px; height:8px; border-radius:50%; background:#fcd34d; display:inline-block;"></span>
                                📱 ربط كاميرا الجوال (F2)
                            </button>
                            <input type="text" id="gdn-quick-scan-input" class="gdn-borderless-input" placeholder="🔍 ابحث في +1000 إذن (رقم الإذن، العميل، الصنف، المستلم) أو اضغط Enter للفتح/الخصم الفوري..." style="width:100%; padding:8px 12px !important; font-size:0.9rem !important; border-color:rgba(56,189,248,0.45) !important;" oninput="const s=document.getElementById('delivery-search'); if(s) s.value=this.value; window.renderWorldClassDeliveryHub();" onkeydown="if(event.key==='Enter'){event.preventDefault(); processScannedDeliveryCode(this.value, true);}">
                        </div>

                        <!-- Filters: Warehouse Focus + Date + Status + Limit + Column Settings Toggle -->
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <select id="gdn-hub-wh-filter" class="gdn-borderless-select" onchange="setGdnHubFilterState('whFilter', this.value)" style="color:#fcd34d !important; font-size:0.82rem !important; padding:6px 10px !important;" title="تركيز على مخزن محدد">
                                ${whFilterOpts}
                            </select>
                            <select id="gdn-hub-date-filter" class="gdn-borderless-select" onchange="setGdnHubFilterState('dateFilter', this.value)" style="color:#7dd3fc !important; font-size:0.82rem !important; padding:6px 10px !important;">
                                <option value="all">📅 كل التواريخ</option>
                                <option value="today">📅 أذونات اليوم</option>
                                <option value="yesterday">📅 أذونات أمس</option>
                                <option value="week">📅 آخر 7 أيام</option>
                            </select>
                            <select id="gdn-hub-limit-select" class="gdn-borderless-select" onchange="setGdnHubFilterState('pageLimit', parseInt(this.value,10)||100)" style="color:#a7f3d0 !important; font-size:0.82rem !important; padding:6px 10px !important;" title="عدد الأذونات المعروضة لسرعة التصفح">
                                <option value="50">⚡ عرض 50 إذن</option>
                                <option value="100" selected>⚡ عرض 100 إذن</option>
                                <option value="250">⚡ عرض 250 إذن</option>
                                <option value="500">⚡ عرض 500 إذن</option>
                                <option value="99999">📋 عرض الكل (+1000)</option>
                            </select>
                            <button id="gdn-tab-pending" onclick="setGdnFilter('pending')" class="gdn-kinetic-btn" style="background:#10b981; color:#ffffff; border:none;">⏳ قيد التسليم</button>
                            <button id="gdn-tab-delivered" onclick="setGdnFilter('delivered')" class="gdn-kinetic-btn" style="background:rgba(255,255,255,0.08); color:#e2e8f0; border:1px solid rgba(255,255,255,0.15);">✅ المسلمة</button>
                            <button id="gdn-tab-all" onclick="setGdnFilter('all')" class="gdn-kinetic-btn" style="background:rgba(255,255,255,0.08); color:#e2e8f0; border:1px solid rgba(255,255,255,0.15);">📋 الكل</button>
                            <button type="button" onclick="window._splitReturnToView='delivery'; openSplitInvoiceView();" class="gdn-kinetic-btn kinetic-glow-arm kinetic-arm-pink" style="padding:6px 14px;" title="تقسيم أي إذن مبيعات إلى أقسام متسلسلة (100/17/1 و 100/17/2)">
                                ✂️ تقسيم إذن / فاتورة
                            </button>
                            <button type="button" onclick="const d=document.getElementById('gdn-hub-cols-drawer'); if(d) d.style.display=(d.style.display==='none'?'flex':'none');" class="gdn-kinetic-btn" style="background:rgba(168,85,247,0.18); border:1px solid rgba(192,132,252,0.45); color:#e9d5ff;" title="إظهار أو إخفاء شريط ضبط الكولومات (الأعمدة محفوظة تلقائياً)">
                                ⚙️ ضبط الكولومات (محفوظ ✅)
                            </button>
                            <a href="sales-delivery.php" class="gdn-kinetic-btn" style="background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.35); color:#38bdf8; text-decoration:none;">🚛 بوابة الصرف (كلاسيكي)</a>
                            <a href="smart-logistics.php?mode=sales" class="gdn-kinetic-btn" style="background:linear-gradient(135deg,rgba(16,185,129,0.28),rgba(14,165,233,0.28)); border:1.5px solid #34d399; color:#ffffff; text-decoration:none; box-shadow:0 0 14px rgba(16,185,129,0.35);">🚀 البوابة الموحدة الذكية</a>
                            <button onclick="toggleDeliverySunlightMode()" id="gdn-sunlight-btn" class="gdn-kinetic-btn" style="background:#fef08a; color:#000000; border:none;" title="وضع الشمس للمخزنجي (F4)">☀️</button>
                        </div>
                    </div>

                    <!-- Live Continuous QR Scan Banner in Delivery Hub -->
                    <div id="gdn-live-continuous-banner" style="display:none; padding:12px 18px; border-radius:14px; font-weight:900; font-size:1rem; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; box-shadow:0 10px 25px rgba(0,0,0,0.45);">
                        <div id="gdn-live-continuous-banner-text"></div>
                        <button type="button" onclick="document.getElementById('gdn-live-continuous-banner').style.display='none'" style="background:rgba(255,255,255,0.2); border:none; color:#fff; border-radius:8px; padding:3px 10px; cursor:pointer; font-weight:900;">✕</button>
                    </div>

                    <!-- Row 2: Collapsible Main Outer Table Column Visibility Drawer (يفتح فقط عند الرغبة في التعديل ويحفظ تلقائياً) -->
                    <div id="gdn-hub-cols-drawer" style="display:none; background:rgba(15,23,42,0.88); border:1.5px solid rgba(168,85,247,0.4); padding:10px 14px; border-radius:12px; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; box-shadow:0 8px 24px rgba(0,0,0,0.45);">
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span style="font-size:0.8rem; font-weight:900; color:#e9d5ff; margin-left:4px;">⚙️ اختر الكولومات التي تريد إظهارها في الشاشة الرئيسية (تُحفظ تلقائياً وتثبت في محلها):</span>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colInvNo" ${hubCols.colInvNo ? 'checked' : ''} onchange="toggleGdnHubCol('colInvNo', this.checked)"><span>📄 رقم الإذن</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colDateTime" ${hubCols.colDateTime ? 'checked' : ''} onchange="toggleGdnHubCol('colDateTime', this.checked)"><span>📅 التاريخ والوقت</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colCustomer" ${hubCols.colCustomer ? 'checked' : ''} onchange="toggleGdnHubCol('colCustomer', this.checked)"><span>👤 العميل والمستلم</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colItems" ${hubCols.colItems ? 'checked' : ''} onchange="toggleGdnHubCol('colItems', this.checked)"><span>📦 عمود الأصناف</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colItemWh" ${hubCols.colItemWh ? 'checked' : ''} onchange="toggleGdnHubCol('colItemWh', this.checked)"><span>🏢 المخزن بالصنف</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colItemSpec" ${hubCols.colItemSpec ? 'checked' : ''} onchange="toggleGdnHubCol('colItemSpec', this.checked)"><span>⚖️ البيان/المعامل</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colItemBaseUnit" ${hubCols.colItemBaseUnit ? 'checked' : ''} onchange="toggleGdnHubCol('colItemBaseUnit', this.checked)"><span>🔢 الوحدة الفرعية</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colWarehouse" ${hubCols.colWarehouse ? 'checked' : ''} onchange="toggleGdnHubCol('colWarehouse', this.checked)"><span>🏛️ المخزن والكميات</span></label>
                            <label class="gdn-col-toggle-chip"><input type="checkbox" data-hub-col="colActions" ${hubCols.colActions ? 'checked' : ''} onchange="toggleGdnHubCol('colActions', this.checked)"><span>⚡ الإجراءات</span></label>
                        </div>
                        <button type="button" onclick="lockAndSaveGdnHubCols()" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:1px solid #34d399; padding:5px 14px; font-size:0.8rem;">
                            💾 تثبيت وإخفاء الشريط
                        </button>
                    </div>
                `;
                headerDiv.insertAdjacentElement('afterend', extraBar);
            }

            // Always sync Hub Drawer Checkboxes with saved config
            document.querySelectorAll('#gdn-hub-cols-drawer input[data-hub-col]').forEach(chk => {
                const k = chk.dataset.hubCol;
                if (k && hubCols[k] !== undefined) chk.checked = !!hubCols[k];
            });

            // 2. Dynamic Main Table Headers (Respecting Show/Hide Column Config Permanently)
            const theadTr = hubView.querySelector('table thead tr');
            if (theadTr) {
                theadTr.innerHTML = `
                    <th style="width:11%; padding:10px; text-align:right; ${hubCols.colInvNo ? '' : 'display:none !important;'}">رقم الإذن والحالة</th>
                    <th style="width:12%; padding:10px; text-align:center; ${hubCols.colDateTime ? '' : 'display:none !important;'}">التاريخ والوقت</th>
                    <th style="width:14%; padding:10px; text-align:right; ${hubCols.colCustomer ? '' : 'display:none !important;'}">العميل والمستلم</th>
                    <th style="width:36%; padding:10px; text-align:right; ${hubCols.colItems ? '' : 'display:none !important;'}">الأصناف (المخزن • الصنف • البيان • الوحدة الأساسية • الوحدة الفرعية)</th>
                    <th style="width:12%; padding:10px; text-align:center; ${hubCols.colWarehouse ? '' : 'display:none !important;'}">المخزن والكميات</th>
                    <th style="width:15%; padding:10px; text-align:center; ${hubCols.colActions ? '' : 'display:none !important;'}">الإجراءات والباركود</th>
                `;
            }

            // 3. Ultra-Wide, Royal Structured Confirmation Modal (#delivery-confirmation-modal) — FULL COLUMN & WIDTH CONTROL
            const modalOverlay = document.getElementById('delivery-confirmation-modal');
            if (modalOverlay && modalOverlay.dataset.smartAiModalUpgraded !== '7') {
                modalOverlay.dataset.smartAiModalUpgraded = '7';
                const optCols = getGdnOptionalColsConfig();
                modalOverlay.innerHTML = `
                    <div class="gdn-smart-modal-card" style="width:99.6vw; max-width:1920px; height:96vh; max-height:97vh; display:flex; flex-direction:column; padding:18px 24px; border-radius:20px; border:2px solid rgba(16,185,129,0.5); background:linear-gradient(165deg, #070c18 0%, #0f172a 60%, #051e17 100%); position:relative; overflow:hidden; box-shadow:0 35px 100px rgba(0,0,0,0.92), 0 0 45px rgba(16,185,129,0.18);">
                        
                        <!-- Royal Modal Header -->
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1.5px solid rgba(16,185,129,0.28); padding-bottom:10px; flex-wrap:wrap; gap:12px;">
                            <div style="display:flex; gap:14px; align-items:center;">
                                <div style="width:46px; height:46px; border-radius:14px; background:linear-gradient(135deg, rgba(16,185,129,0.25), rgba(5,150,105,0.4)); border:1.5px solid rgba(52,211,153,0.6); display:flex; align-items:center; justify-content:center; font-size:1.6rem; box-shadow:0 0 20px rgba(16,185,129,0.3);">🚚</div>
                                <div>
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <h2 style="margin:0; color:#ffffff; font-size:1.3rem; font-weight:900; letter-spacing:0.3px;">بطاقة وإذن تسليم وصرف بضاعة (مرونة الوحدتين: استلم هنا أو هنا)</h2>
                                        <span id="del-modal-gdn-badge" style="background:rgba(56,189,248,0.16); border:1px solid rgba(56,189,248,0.45); color:#38bdf8; font-size:0.84rem; font-weight:900; padding:3px 12px; border-radius:99px;">GDN-AUTO</span>
                                        <span id="del-modal-fulfillment-pill" style="background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#6ee7b7; font-size:0.82rem; font-weight:900; padding:3px 12px; border-radius:99px;">🎯 جاهزية الصرف: 100%</span>
                                    </div>
                                    <p style="margin:3px 0 0 0; color:#a7f3d0; font-size:0.84rem; font-weight:700;">تحكم كامل في إظهار/إخفاء الأعمدة وعرض الخانات • تركيز بصري للمخازن • استلام متزامن بالوحدة الأساسية أو الفرعية</p>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <button type="button" onclick="const d=document.getElementById('gdn-modal-cols-drawer'); if(d) d.style.display=(d.style.display==='none'?'flex':'none');" class="gdn-kinetic-btn" style="background:rgba(168,85,247,0.18); border:1px solid rgba(192,132,252,0.5); color:#e9d5ff; font-size:0.8rem; padding:6px 12px;" title="فتح أو إغلاق شريط ضبط الكولومات (يُحفظ تلقائياً)">
                                    ⚙️ ضبط الكولومات (محفوظ ✅)
                                </button>
                                <button type="button" onclick="if(window.pendingDeliveryContext && window.pendingDeliveryContext.invIdentifier) window.openSplitForInvoiceFromDelivery(window.pendingDeliveryContext.invIdentifier);" class="gdn-kinetic-btn kinetic-glow-arm kinetic-arm-pink" style="font-size:0.84rem; padding:6px 14px;" title="تقسيم هذا الإذن إلى أقسام (100/17/1 و 100/17/2)">
                                    ✂️ تقسيم هذا الإذن
                                </button>
                                <button type="button" onclick="openOriginalInvoiceInSalesEditor()" class="gdn-kinetic-btn" style="background:rgba(56,189,248,0.16); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; font-size:0.84rem; padding:6px 14px;">
                                    👁️ أصل الفاتورة
                                </button>
                                <button type="button" onclick="closeDeliveryConfirmationModal()" class="gdn-kinetic-btn" style="background:rgba(239,68,68,0.16); border:1px solid rgba(239,68,68,0.35); color:#fca5a5; font-size:1rem; padding:6px 14px;">✕</button>
                            </div>
                        </div>

                        <!-- Unified Smooth Scrollable Modal Body -->
                        <div style="flex:1; overflow-y:auto; -webkit-overflow-scrolling:touch; overscroll-behavior:contain; padding-left:4px; display:flex; flex-direction:column; gap:12px;">
                            
                            <!-- Structured KPI Info Strip + Live Psychological Fulfillment Bar -->
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(185px, 1fr)); gap:10px; background:rgba(15,23,42,0.85); border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.22);">
                                <div style="background:rgba(2,6,23,0.55); padding:7px 12px; border-radius:10px; border-right:3px solid #38bdf8;">
                                    <span style="color:#94a3b8; font-size:0.74rem; font-weight:800; display:block;">📄 رقم الإذن / الفاتورة:</span>
                                    <strong id="del-modal-inv-no" style="color:#38bdf8; font-size:1.12rem; font-family:'Outfit',sans-serif; font-weight:900;">-</strong>
                                </div>
                                <div style="background:rgba(2,6,23,0.55); padding:7px 12px; border-radius:10px; border-right:3px solid #a855f7;">
                                    <span style="color:#94a3b8; font-size:0.74rem; font-weight:800; display:block;">📅 التاريخ والوقت:</span>
                                    <strong id="del-modal-datetime" style="color:#f8fafc; font-size:0.95rem; font-weight:800;">-</strong>
                                </div>
                                <div style="background:rgba(2,6,23,0.55); padding:7px 12px; border-radius:10px; border-right:3px solid #10b981;">
                                    <span style="color:#94a3b8; font-size:0.74rem; font-weight:800; display:block;">👤 العميل:</span>
                                    <strong id="del-modal-cust-name" style="color:#6ee7b7; font-size:1rem; font-weight:900;">-</strong>
                                </div>
                                <div style="background:rgba(2,6,23,0.55); padding:7px 12px; border-radius:10px; border-right:4px solid #f59e0b; box-shadow:inset 0 0 12px rgba(245,158,11,0.12);">
                                    <span style="color:#fcd34d; font-size:0.74rem; font-weight:900; display:block;">🏢 مركز التركيز (مخزن الصرف):</span>
                                    <span id="del-modal-wh-name" style="color:#fbbf24; font-size:1.02rem; font-weight:900;">-</span>
                                </div>
                                <div style="background:rgba(2,6,23,0.55); padding:7px 12px; border-radius:10px; border-right:3px solid #ec4899;">
                                    <span style="color:#94a3b8; font-size:0.74rem; font-weight:800; display:block;">💰 إجمالي الفاتورة:</span>
                                    <strong id="del-modal-amount" style="color:#ffffff; font-size:1.05rem; font-weight:900;">0 SDG</strong>
                                </div>
                            </div>

                            <!-- Precision Items Table Card -->
                            <div style="background:rgba(15,23,42,0.68); border:1.5px solid rgba(148,163,184,0.28); border-radius:16px; padding:14px; display:flex; flex-direction:column; gap:10px;">
                                <!-- Top Bar: Title + Search Inside Items + Master Full Delivery Actions -->
                                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <span style="font-weight:900; color:#ffffff; font-size:1.02rem;">📦 جدول تسليم الأصناف المرن (خانات ووحدات مفصلة بسطور واضحة)</span>
                                        <span id="del-modal-items-count-badge" style="background:rgba(16,185,129,0.18); border:1px solid rgba(52,211,153,0.45); color:#6ee7b7; padding:3px 12px; border-radius:99px; font-size:0.82rem; font-weight:900;">0 أصناف</span>
                                        <input type="text" id="gdn-modal-item-filter" class="gdn-borderless-input" placeholder="🔍 بحث سريع في أصناف البطاقة..." oninput="filterModalOriginRows(this.value)" style="width:210px; padding:4px 10px !important; font-size:0.8rem !important;">
                                    </div>

                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <button type="button" onclick="const d=document.getElementById('gdn-modal-cols-drawer'); if(d) d.style.display=(d.style.display==='none'?'flex':'none');" class="gdn-kinetic-btn" style="background:rgba(168,85,247,0.18); border:1px solid rgba(192,132,252,0.5); color:#e9d5ff; padding:6px 12px; font-size:0.8rem;">
                                            ⚙️ ضبط الكولومات (محفوظ ✅)
                                        </button>
                                        <button type="button" onclick="fillAllModalDeliveryQtys('primary')" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #10b981, #059669); border:1px solid #34d399; color:#ffffff; padding:6px 14px; font-size:0.82rem;">
                                            ✅ استلام الكل (بالوحدة الأساسية)
                                        </button>
                                        <button type="button" onclick="fillAllModalDeliveryQtys('base')" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #4f46e5, #6366f1); border:1px solid #818cf8; color:#ffffff; padding:6px 14px; font-size:0.82rem;">
                                            🔢 استلام الكل (بالوحدة الفرعية)
                                        </button>
                                    </div>
                                </div>

                                <!-- Collapsible Granular Column Visibility Strip (يفتح فقط عند الضغط على ضبط الكولومات ويحفظ تلقائياً ويثبت نهائياً) -->
                                <div id="gdn-modal-cols-drawer" style="display:none; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; background:rgba(2,6,23,0.88); padding:10px 14px; border-radius:12px; border:1.5px solid rgba(168,85,247,0.45); box-shadow:0 8px 24px rgba(0,0,0,0.5);">
                                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                        <span style="font-size:0.78rem; font-weight:900; color:#e9d5ff; margin-left:4px;">⚙️ اختر أعمدة البطاقة (تُحفظ وتثبت نهائياً):</span>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-wh" ${optCols.showWarehouse ? 'checked' : ''} onchange="toggleGdnOptionalCol('showWarehouse', this.checked)"><span>🏢 المخزن</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-item" ${optCols.showItemName ? 'checked' : ''} onchange="toggleGdnOptionalCol('showItemName', this.checked)"><span>📦 الصنف</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-spec" ${optCols.showItemSpec ? 'checked' : ''} onchange="toggleGdnOptionalCol('showItemSpec', this.checked)"><span>⚖️ البيان/المعامل</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-punit" ${optCols.showPrimaryUnit ? 'checked' : ''} onchange="toggleGdnOptionalCol('showPrimaryUnit', this.checked)"><span>🏷️ الوحدة الأساسية</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-oqty" ${optCols.showOrderedQty ? 'checked' : ''} onchange="toggleGdnOptionalCol('showOrderedQty', this.checked)"><span>📊 الكمية بالإذن</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-units" ${optCols.showUnitsCount ? 'checked' : ''} onchange="toggleGdnOptionalCol('showUnitsCount', this.checked)"><span>🔢 الوحدة الفرعية والمعادل</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-avail" ${optCols.showAvailStock ? 'checked' : ''} onchange="toggleGdnOptionalCol('showAvailStock', this.checked)"><span>🏛️ المتاح بالمخزن</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-price" ${optCols.showPriceTotal ? 'checked' : ''} onchange="toggleGdnOptionalCol('showPriceTotal', this.checked)"><span>💰 السعر والإجمالي</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-recp" ${optCols.showReceivePrimary ? 'checked' : ''} onchange="toggleGdnOptionalCol('showReceivePrimary', this.checked)"><span>📥 استلام بالأساسية</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-recb" ${optCols.showReceiveBase ? 'checked' : ''} onchange="toggleGdnOptionalCol('showReceiveBase', this.checked)"><span>🔄 استلام بالفرعية</span></label>
                                        <label class="gdn-col-toggle-chip"><input type="checkbox" id="chk-gdn-col-net" ${optCols.showNetSummary ? 'checked' : ''} onchange="toggleGdnOptionalCol('showNetSummary', this.checked)"><span>✅ الصافي المستلم</span></label>
                                        <div style="display:inline-flex; align-items:center; gap:4px; background:rgba(15,23,42,0.9); padding:3px 8px; border-radius:8px; border:1px solid rgba(148,163,184,0.3);">
                                            <span style="font-size:0.74rem; color:#94a3b8; font-weight:800;">📏 العرض:</span>
                                            <button type="button" onclick="setGdnModalWidthScale('normal')" class="gdn-kinetic-btn" style="padding:2px 7px; font-size:0.72rem; background:rgba(255,255,255,0.06); color:#e2e8f0; border:none;">قياسي</button>
                                            <button type="button" onclick="setGdnModalWidthScale('wide')" class="gdn-kinetic-btn" style="padding:2px 7px; font-size:0.72rem; background:rgba(56,189,248,0.18); color:#38bdf8; border:none;">عريض</button>
                                            <button type="button" onclick="setGdnModalWidthScale('ultrawide')" class="gdn-kinetic-btn" style="padding:2px 7px; font-size:0.72rem; background:rgba(16,185,129,0.22); color:#6ee7b7; border:none;">أقصى توسعة</button>
                                        </div>
                                        <button type="button" onclick="applyGdnColPreset('all')" class="gdn-kinetic-btn" style="background:rgba(16,185,129,0.15); border:1px solid rgba(52,211,153,0.4); color:#6ee7b7; font-size:0.75rem; padding:4px 10px;">👁️ إظهار الكل</button>
                                        <button type="button" onclick="applyGdnColPreset('storekeeper_focus')" class="gdn-kinetic-btn" style="background:rgba(245,158,11,0.16); border:1px solid rgba(251,191,36,0.45); color:#fcd34d; font-size:0.75rem; padding:4px 10px;">🎯 وضع تركيز المخزنجي</button>
                                    </div>
                                    <button type="button" onclick="lockAndSaveGdnModalCols()" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:1px solid #34d399; padding:5px 14px; font-size:0.8rem;">
                                        💾 تثبيت وإخفاء الشريط
                                    </button>
                                </div>

                                <!-- Table Container -->
                                <div id="delivery-modal-items-preview" style="overflow-x:auto; border-radius:14px; width:100%;">
                                    <!-- Injected by renderModalOriginColumnsTable() -->
                                </div>
                            </div>

                            <!-- Structured Logistics & Gate Pass Fields -->
                            <div id="gdn-logistics-fields" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; background:rgba(15,23,42,0.75); padding:12px 16px; border-radius:14px; border:1px solid rgba(148,163,184,0.22);">
                                <div>
                                    <label style="display:block; font-size:0.78rem; font-weight:900; color:#6ee7b7; margin-bottom:4px;">👤 اسم المستلم الفعلي:</label>
                                    <input type="text" id="gdn-receiver-name" class="gdn-borderless-input" placeholder="اسم المستلم..." style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.78rem; font-weight:900; color:#7dd3fc; margin-bottom:4px;">🚚 اسم السائق / الدفار:</label>
                                    <input type="text" id="gdn-driver-name" class="gdn-borderless-input" placeholder="اسم السائق..." style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.78rem; font-weight:900; color:#fcd34d; margin-bottom:4px;">🔢 رقم المركبة / اللوحة:</label>
                                    <input type="text" id="gdn-truck-plate" class="gdn-borderless-input" placeholder="رقم اللوحة..." style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.78rem; font-weight:900; color:#c084fc; margin-bottom:4px;">🎫 تصريح البوابة (Gate Pass):</label>
                                    <input type="text" id="gdn-gate-pass" class="gdn-borderless-input" placeholder="تلقائي..." style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.78rem; font-weight:900; color:#cbd5e1; margin-bottom:4px;">📝 ملاحظات التسليم والمخزن:</label>
                                    <input type="text" id="gdn-warehouse-notes" class="gdn-borderless-input" placeholder="ملاحظات المخزن..." style="width:100%;">
                                </div>
                            </div>
                        </div>

                        <!-- Royal Modal Footer -->
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-top:1.5px solid rgba(148,163,184,0.22); padding-top:12px; margin-top:10px;">
                            <button type="button" onclick="closeDeliveryConfirmationModal()" class="gdn-kinetic-btn" style="padding:9px 22px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#e2e8f0; font-size:0.92rem;">
                                ❌ إغلاق النافذة
                            </button>
                            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                <button type="button" onclick="confirmAndPrintDeliveryAction()" class="gdn-kinetic-btn" style="padding:9px 22px; background:linear-gradient(135deg, #0284c7, #0369a1); border:1px solid #38bdf8; color:#ffffff; font-size:0.94rem;">
                                    🖨️ اعتماد وطباعة إذن الصرف
                                </button>
                                <button type="button" onclick="confirmExecuteDeliveryAction()" class="gdn-kinetic-btn" style="padding:9px 28px; background:linear-gradient(135deg, #10b981, #059669); border:1px solid #34d399; color:#ffffff; font-size:1.02rem; box-shadow:0 6px 20px rgba(16,185,129,0.4);">
                                    📦 تأكيد التسليم والصرف الآن
                                </button>
                            </div>
                        </div>

                    </div>
                `;
            }
        }

        window.openOriginalInvoiceInSalesEditor = function() {
            const ctx = window.pendingDeliveryContext;
            if (!ctx || !ctx.invIdentifier) return;
            if (typeof closeDeliveryConfirmationModal === 'function') closeDeliveryConfirmationModal();
            if (typeof window.editInvoice === 'function') {
                window.editInvoice(ctx.invIdentifier);
            } else if (typeof window.openInvoiceEditor === 'function') {
                window.openInvoiceEditor('pending_delivery');
            }
        };

        window.fillAllModalDeliveryQtys = function(mode = 'primary') {
            document.querySelectorAll('.gdn-modal-deliver-qty').forEach(inp => {
                const idx = inp.dataset.itemIdx;
                if (inp.dataset.maxRem !== undefined) {
                    inp.value = inp.dataset.maxRem;
                }
                const factor = parseFloat(inp.dataset.unitFactor || 1) || 1;
                const baseInp = document.querySelector(`.gdn-modal-deliver-base-qty[data-item-idx="${idx}"]`);
                if (baseInp && inp.dataset.maxRem !== undefined) {
                    baseInp.value = +(parseFloat(inp.dataset.maxRem) * factor).toFixed(2);
                }
                window.setRowDeliveryUnitMode(idx, mode, false);
            });
            window.recalcModalDeliverySummaryFoot();
            if (typeof showToast === 'function') {
                showToast(mode === 'base' ? '✅ تم ضبط استلام الكل بالوحدات الفرعية (السيخ / الباكو)' : '✅ تم ضبط استلام الكل بوحدة الإذن الأساسية', 'success');
            }
        };

        window.setRowDeliveryUnitMode = function(idx, mode = 'primary', fillFull = false) {
            const inp = document.querySelector(`.gdn-modal-deliver-qty[data-item-idx="${idx}"]`);
            const baseInp = document.querySelector(`.gdn-modal-deliver-base-qty[data-item-idx="${idx}"]`);
            if (!inp) return;

            inp.dataset.activeUnitMode = mode;
            const maxRem = parseFloat(inp.dataset.maxRem || 0);
            const factor = parseFloat(inp.dataset.unitFactor || 1) || 1;

            if (fillFull) {
                inp.value = +maxRem.toFixed(4);
                if (baseInp) baseInp.value = +(maxRem * factor).toFixed(2);
            }
            window.recalcModalDeliverySummaryFoot();
        };

        window.onPrimaryDeliverQtyInput = function(idx) {
            const inp = document.querySelector(`.gdn-modal-deliver-qty[data-item-idx="${idx}"]`);
            const baseInp = document.querySelector(`.gdn-modal-deliver-base-qty[data-item-idx="${idx}"]`);
            if (!inp) return;
            const maxRem = parseFloat(inp.dataset.maxRem || 999999);
            const factor = parseFloat(inp.dataset.unitFactor || 1) || 1;
            let q = parseFloat(inp.value);
            if (isNaN(q) || q < 0) q = 0;
            if (q > maxRem) {
                q = maxRem;
                inp.value = +q.toFixed(4);
            }
            if (baseInp) {
                baseInp.value = +(q * factor).toFixed(2);
            }
            window.setRowDeliveryUnitMode(idx, 'primary', false);
        };

        window.onBaseDeliverQtyInput = function(idx) {
            const inp = document.querySelector(`.gdn-modal-deliver-qty[data-item-idx="${idx}"]`);
            const baseInp = document.querySelector(`.gdn-modal-deliver-base-qty[data-item-idx="${idx}"]`);
            if (!inp || !baseInp) return;
            const maxRem = parseFloat(inp.dataset.maxRem || 999999);
            const factor = parseFloat(inp.dataset.unitFactor || 1) || 1;
            const maxBase = +(maxRem * factor).toFixed(4);
            let bVal = parseFloat(baseInp.value);
            if (isNaN(bVal) || bVal < 0) bVal = 0;
            if (bVal > maxBase) {
                bVal = maxBase;
                baseInp.value = +bVal.toFixed(2);
            }
            const eqPrimary = factor > 0 ? +(bVal / factor).toFixed(4) : bVal;
            inp.value = eqPrimary;
            window.setRowDeliveryUnitMode(idx, 'base', false);
        };

        window.adjustModalItemQty = function(idx, delta, targetMode = 'primary') {
            const inp = document.querySelector(`.gdn-modal-deliver-qty[data-item-idx="${idx}"]`);
            const baseInp = document.querySelector(`.gdn-modal-deliver-base-qty[data-item-idx="${idx}"]`);
            if (!inp) return;
            const maxRem = parseFloat(inp.dataset.maxRem || 999999);
            const factor = parseFloat(inp.dataset.unitFactor || 1) || 1;
            if (targetMode === 'base' && baseInp) {
                const maxBase = +(maxRem * factor).toFixed(4);
                const curB = parseFloat(baseInp.value || 0);
                const nextB = Math.min(maxBase, Math.max(0, +(curB + delta).toFixed(2)));
                baseInp.value = nextB;
                inp.value = factor > 0 ? +(nextB / factor).toFixed(4) : nextB;
                window.setRowDeliveryUnitMode(idx, 'base', false);
            } else {
                const cur = parseFloat(inp.value || 0);
                const next = Math.min(maxRem, Math.max(0, +(cur + delta).toFixed(4)));
                inp.value = next;
                if (baseInp) baseInp.value = +(next * factor).toFixed(2);
                window.setRowDeliveryUnitMode(idx, 'primary', false);
            }
        };

        window.recalcModalDeliverySummaryFoot = function() {
            let sumDeliverNow = 0;
            let sumMaxRem = 0;
            let sumBaseNow = 0;
            document.querySelectorAll('.gdn-modal-deliver-qty').forEach(inp => {
                const q = parseFloat(inp.value || 0) || 0;
                const maxRem = parseFloat(inp.dataset.maxRem || q || 0) || 0;
                const factor = parseFloat(inp.dataset.unitFactor || 1) || 1;
                const primaryUnit = inp.dataset.primaryUnit || 'وحدة';
                const baseUnit = inp.dataset.baseUnit || 'وحدة';
                const rowBase = q * factor;
                const pct = maxRem > 0 ? Math.min(100, Math.round((q / maxRem) * 100)) : 100;
                sumDeliverNow += q;
                sumMaxRem += maxRem;
                sumBaseNow += rowBase;
                const rowBaseEl = document.getElementById('gdn-row-baseqty-' + inp.dataset.itemIdx);
                if (rowBaseEl) {
                    rowBaseEl.textContent = rowBase.toLocaleString('en-US', { maximumFractionDigits: 2 });
                }
                const liveEqEl = document.getElementById('gdn-deliver-live-eq-' + inp.dataset.itemIdx);
                if (liveEqEl) {
                    const pctColor = pct >= 100 ? '#10b981' : (pct > 0 ? '#f59e0b' : '#ef4444');
                    liveEqEl.innerHTML = `
                        <div style="display:flex; flex-direction:column; gap:4px; align-items:center; justify-content:center;">
                            <span style="background:rgba(16,185,129,0.18); border:1px solid rgba(52,211,153,0.45); color:#34d399; padding:3px 10px; border-radius:7px; font-size:0.9rem; font-weight:900; white-space:nowrap;">
                                📦 ${q.toLocaleString('en-US', { maximumFractionDigits: 3 })} ${primaryUnit}
                            </span>
                            <span style="background:rgba(99,102,241,0.18); border:1px solid rgba(129,140,248,0.45); color:#c4b5fd; padding:2px 10px; border-radius:7px; font-size:0.82rem; font-weight:900; white-space:nowrap;">
                                🔢 ⇄ ${rowBase.toLocaleString('en-US', { maximumFractionDigits: 2 })} ${baseUnit}
                            </span>
                            <div style="width:100%; background:rgba(255,255,255,0.08); height:5px; border-radius:99px; overflow:hidden; margin-top:2px;">
                                <div style="width:${pct}%; height:100%; background:${pctColor}; transition:width 0.2s;"></div>
                            </div>
                        </div>
                    `;
                }
            });
            const footDelEl = document.getElementById('gdn-foot-deliver-now');
            if (footDelEl) footDelEl.textContent = sumDeliverNow.toLocaleString('en-US', { maximumFractionDigits: 3 });
            const footBaseEl = document.getElementById('gdn-foot-base-now');
            if (footBaseEl) footBaseEl.textContent = sumBaseNow.toLocaleString('en-US', { maximumFractionDigits: 2 });
            const footDelBaseEl = document.getElementById('gdn-foot-deliver-base-now');
            if (footDelBaseEl) footDelBaseEl.textContent = sumBaseNow.toLocaleString('en-US', { maximumFractionDigits: 2 });
            const fulfillPill = document.getElementById('del-modal-fulfillment-pill');
            if (fulfillPill) {
                const totalPct = sumMaxRem > 0 ? Math.min(100, Math.round((sumDeliverNow / sumMaxRem) * 100)) : 100;
                fulfillPill.textContent = totalPct >= 100 ? `✅ جاهزية الصرف الكامل: 100%` : `🌗 تسليم جزئي: ${totalPct}%`;
                fulfillPill.style.borderColor = totalPct >= 100 ? '#10b981' : '#f59e0b';
                fulfillPill.style.color = totalPct >= 100 ? '#6ee7b7' : '#fcd34d';
            }
        };

        window.toggleDeliverySunlightMode = function() {
            const isSun = document.body.classList.toggle('gdn-sunlight-mode');
            localStorage.setItem('erp_delivery_sunlight_mode', isSun ? '1' : '0');
            if (typeof showToast === 'function') {
                showToast(isSun ? '☀️ تم تفعيل وضع الشمس للمخزنجي' : '✨ تم تفعيل الوضع العادي', 'success');
            }
        };

        window.setGdnFilter = function(filterMode) {
            window.gdnCurrentFilter = filterMode;
            ['pending', 'delivered', 'all'].forEach(mode => {
                const btn = document.getElementById('gdn-tab-' + mode);
                if (btn) {
                    if (mode === filterMode) {
                        btn.style.background = '#10b981';
                        btn.style.color = '#ffffff';
                    } else {
                        btn.style.background = 'rgba(255,255,255,0.08)';
                        btn.style.color = '#e2e8f0';
                    }
                }
            });
            window.loadDeliveryTransactions();
        };

        // World-Class Table Rendering (targets #delivery-transactions-body — Optimized for 1,000+ Vouchers)
        window.renderWorldClassDeliveryHub = function() {
            upgradeDeliveryHubDOM();
            const tbody = document.getElementById('delivery-transactions-body') || document.getElementById('delivery-hub-tbody');
            const emptyState = document.getElementById('delivery-empty-state');
            if (!tbody) return;

            const hubCols = getGdnHubColsConfig();
            const searchVal = (document.getElementById('delivery-search')?.value || document.getElementById('gdn-quick-scan-input')?.value || document.getElementById('delivery-hub-search')?.value || '').trim().toLowerCase();
            const allInvoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]').filter(inv => !inv.deleted && inv.saleType !== 'preliminary' && inv.status !== 'preliminary');
            const warehouses = getWarehousesList();

            let pendingCount = 0;
            let deliveredCount = 0;
            let pendingTotalQty = 0;

            allInvoices.forEach(inv => {
                const isDel = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Delivered_Pending_Admin' || inv.deliveryStatus === 'Admin_Closed';
                if (isDel) {
                    deliveredCount++;
                } else {
                    pendingCount++;
                    (inv.items || []).forEach(it => {
                        const rem = Math.max(0, (parseFloat(it.qty ?? it.quantity ?? 0) - parseFloat(it.deliveredQty || 0)));
                        pendingTotalQty += rem;
                    });
                }
            });

            const kpiPending = document.getElementById('gdn-kpi-pending');
            const kpiDelivered = document.getElementById('gdn-kpi-delivered');
            const kpiQty = document.getElementById('gdn-kpi-qty');
            if (kpiPending) kpiPending.textContent = pendingCount.toLocaleString('en-US');
            if (kpiDelivered) kpiDelivered.textContent = deliveredCount.toLocaleString('en-US');
            if (kpiQty) kpiQty.textContent = pendingTotalQty.toLocaleString('en-US');

            if (emptyState) emptyState.style.display = 'none';

            const todayIso = new Date().toISOString().slice(0, 10);
            const yesterdayIso = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
            const weekAgoMs = Date.now() - 7 * 86400000;
            const whFilter = window.gdnHubState?.whFilter || 'all';
            const dateFilter = window.gdnHubState?.dateFilter || 'all';

            const filtered = allInvoices.filter(inv => {
                const isDel = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Delivered_Pending_Admin' || inv.deliveryStatus === 'Admin_Closed';
                if (window.gdnCurrentFilter === 'pending' && isDel) return false;
                if (window.gdnCurrentFilter === 'delivered' && !isDel) return false;

                if (whFilter !== 'all') {
                    const invWh = String(inv.warehouseId || inv.warehouse || 1);
                    const hasWhItem = (inv.items || []).some(it => String(it.warehouseId || it.warehouse || invWh) === String(whFilter));
                    if (invWh !== String(whFilter) && !hasWhItem) return false;
                }

                if (dateFilter !== 'all') {
                    const { dateStr } = parseInvoiceDateTimeParts(inv);
                    if (dateFilter === 'today' && dateStr !== todayIso) return false;
                    if (dateFilter === 'yesterday' && dateStr !== yesterdayIso) return false;
                    if (dateFilter === 'week' && new Date(dateStr).getTime() < weekAgoMs) return false;
                }

                if (!searchVal) return true;
                const invNo = String(inv.invoiceNo || inv.serial || inv.id || '').toLowerCase();
                const gdnNo = String(inv.deliveryNo || '').toLowerCase();
                const cust = resolveCustomerName(inv).toLowerCase();
                const rec = String(inv.receiverName || inv.driverName || inv.truckPlate || '').toLowerCase();
                const itemsText = (inv.items || []).map(i => {
                    const m = resolveItemMeta(i);
                    return `${m.name} ${m.unit} ${m.baseUnit} ${m.code} ${m.whName}`;
                }).join(' ').toLowerCase();
                return invNo.includes(searchVal) || gdnNo.includes(searchVal) || cust.includes(searchVal) || rec.includes(searchVal) || itemsText.includes(searchVal);
            });

            filtered.sort((a, b) => new Date(b.timestamp || b.date || 0) - new Date(a.timestamp || a.date || 0));

            const pageLimit = window.gdnHubState?.pageLimit || 100;
            const visibleSlice = filtered.slice(0, pageLimit);
            const matchBadge = document.getElementById('gdn-kpi-match-count');
            if (matchBadge) {
                matchBadge.textContent = `🔍 المعروض: ${visibleSlice.length.toLocaleString('en-US')} من ${filtered.length.toLocaleString('en-US')} إذن`;
            }

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px 20px; color:#94a3b8;">
                            <div style="font-size:2.4rem; margin-bottom:8px;">${window.gdnCurrentFilter === 'pending' ? '🎉' : '📭'}</div>
                            <div style="font-weight:800; font-size:1.05rem; color:#f8fafc;">
                                ${window.gdnCurrentFilter === 'pending' ? 'لا توجد فواتير معلقة بانتظار التسليم حالياً' : 'لا توجد سجلات مطابقة للبحث'}
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            const rowsMarkup = visibleSlice.map((inv, idx) => {
                const invNo = String(inv.invoiceNo || inv.serial || inv.id || '');
                const { dateStr, timeStr } = parseInvoiceDateTimeParts(inv);
                const isDel = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Delivered_Pending_Admin' || inv.deliveryStatus === 'Admin_Closed';
                const isPartial = !isDel && inv.deliveryStatus === 'partial';
                const custName = resolveCustomerName(inv);
                const defaultWhObj = warehouses.find(w => String(w.id) === String(inv.warehouseId || inv.warehouse || 1) || w.name === (inv.warehouseId || inv.warehouse)) || warehouses[0];
                const whOptions = warehouses.map(w => `<option value="${w.id}" ${String(w.id) === String(defaultWhObj.id) ? 'selected' : ''}>${w.name}</option>`).join('');

                const items = Array.isArray(inv.items) ? inv.items : [];
                let totalInvQty = 0;
                let totalInvBaseUnits = 0;
                const itemsPillsHtml = items.map((it, itemIdx) => {
                    const meta = resolveItemMeta(it, defaultWhObj);
                    const q = meta.qty;
                    const delQ = parseFloat(it.deliveredQty || 0);
                    const remQ = isDel ? q : Math.max(0, q - delQ);
                    const remBaseQ = remQ * meta.factor;
                    totalInvQty += remQ;
                    totalInvBaseUnits += remBaseQ;
                    return `
                        <div class="gdn-item-pill" style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;" onclick="event.stopPropagation(); openWorldClassDeliveryModal('${invNo}', ${itemIdx}, 'primary')" title="اضغط لفتح بطاقة التسليم والوحدات">
                            <div style="display:flex; align-items:center; gap:7px; flex:1; min-width:180px;">
                                <span style="background:rgba(56,189,248,0.18); color:#38bdf8; width:24px; height:24px; border-radius:6px; font-size:0.76rem; font-weight:900; display:inline-flex; align-items:center; justify-content:center;">${itemIdx + 1}</span>
                                <span style="font-weight:900; color:#f8fafc; font-size:0.93rem;">${meta.name}</span>
                                ${hubCols.colItemSpec ? `<span style="font-size:0.73rem; color:#38bdf8; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); padding:2px 7px; border-radius:5px; font-weight:800;">⚖️ 1 ${meta.unit} = ${meta.factor} ${meta.baseUnit}</span>` : ''}
                            </div>
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                ${hubCols.colItemWh ? `<span style="color:#fcd34d; font-size:0.75rem; font-weight:800; background:rgba(245,158,11,0.14); border:1px solid rgba(245,158,11,0.3); padding:3px 8px; border-radius:6px; white-space:nowrap;">🏢 ${meta.whName}</span>` : ''}
                                <button type="button" onclick="event.stopPropagation(); openWorldClassDeliveryModal('${invNo}', ${itemIdx}, 'primary')" title="استلم بالـ ${meta.unit}" style="background:linear-gradient(135deg, #10b981, #059669); color:#ffffff; border:1px solid #34d399; padding:4px 10px; border-radius:7px; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">
                                    📦 ${remQ.toLocaleString('en-US', { maximumFractionDigits: 3 })} ${meta.unit}
                                </button>
                                ${hubCols.colItemBaseUnit ? `
                                <button type="button" onclick="event.stopPropagation(); openWorldClassDeliveryModal('${invNo}', ${itemIdx}, 'base')" title="استلم بالـ ${meta.baseUnit}" style="background:rgba(99,102,241,0.2); color:#c4b5fd; border:1px solid rgba(129,140,248,0.5); padding:4px 10px; border-radius:7px; font-size:0.8rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">
                                    🔢 ${remBaseQ.toLocaleString('en-US', { maximumFractionDigits: 2 })} ${meta.baseUnit}
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }).join('');

                const statusBadge = isDel
                    ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.35); color:#6ee7b7; padding:3px 8px; border-radius:99px; font-size:0.74rem; font-weight:800;">✅ تم الصرف (${inv.deliveryNo || 'GDN'})</span>`
                    : (isPartial
                        ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(245,158,11,0.16); border:1px solid rgba(251,191,36,0.35); color:#fcd34d; padding:3px 8px; border-radius:99px; font-size:0.74rem; font-weight:800;">🌗 تسليم جزئي</span>`
                        : `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(249,115,22,0.16); border:1px solid rgba(251,146,60,0.35); color:#fdba74; padding:3px 8px; border-radius:99px; font-size:0.74rem; font-weight:800;">⏳ بانتظار التسليم</span>`);

                const splitGenealogyHtml = (typeof window.renderSplitGenealogyBadge === 'function') ? window.renderSplitGenealogyBadge(inv) : '';
                const escInvNoForJs = invNo.replace(/'/g, "\\'");

                return `
                    <tr class="gdn-row" data-row-idx="${idx}" data-inv-no="${invNo}" onclick="highlightGdnRow(${idx})">
                        <td style="padding:10px; text-align:right; ${hubCols.colInvNo ? '' : 'display:none;'}">
                            <div style="font-weight:900; color:#38bdf8; font-size:1.05rem; font-family:'Outfit',monospace; direction:ltr; display:inline-block;">#${invNo}</div>
                            ${splitGenealogyHtml}
                            <div style="margin-top:4px;">${statusBadge}</div>
                        </td>
                        <td style="padding:10px; text-align:center; ${hubCols.colDateTime ? '' : 'display:none;'}">
                            <div class="gdn-datetime-wrap">
                                <span class="gdn-date-chip">📅 ${dateStr}</span>
                                <span class="gdn-time-chip">🕒 ${timeStr}</span>
                            </div>
                        </td>
                        <td style="padding:10px; text-align:right; ${hubCols.colCustomer ? '' : 'display:none;'}">
                            <div style="font-weight:800; color:#f8fafc; font-size:0.93rem;">👤 ${custName}</div>
                            ${inv.receiverName ? `<div style="font-size:0.75rem; color:#34d399; font-weight:700; margin-top:2px;">المستلم: ${inv.receiverName}</div>` : ''}
                        </td>
                        <td style="padding:10px; text-align:right; ${hubCols.colItems ? '' : 'display:none;'}">
                            <div class="gdn-items-container">
                                ${itemsPillsHtml || '<span style="color:#94a3b8; font-size:0.8rem;">لا توجد أصناف</span>'}
                            </div>
                        </td>
                        <td style="padding:10px; text-align:center; ${hubCols.colWarehouse ? '' : 'display:none;'}">
                            <div style="font-weight:800; color:#e2e8f0; font-size:0.84rem; margin-bottom:4px;">
                                الكمية: <strong style="color:#34d399;">${totalInvQty.toLocaleString('en-US', { maximumFractionDigits: 2 })}</strong>
                                ${totalInvBaseUnits !== totalInvQty ? `<div style="font-size:0.76rem; color:#a5b4fc;">(${totalInvBaseUnits.toLocaleString('en-US', { maximumFractionDigits: 2 })} بالوحدات)</div>` : ''}
                            </div>
                            <select id="del-wh-${invNo}" class="gdn-borderless-select" style="width:100%; font-size:0.82rem;" ${isDel ? 'disabled' : ''}>
                                ${whOptions}
                            </select>
                        </td>
                        <td style="padding:10px; text-align:center; ${hubCols.colActions ? '' : 'display:none;'}">
                            <div style="display:flex; flex-wrap:wrap; gap:5px; justify-content:center;">
                                ${!isDel ? `
                                    <button onclick="event.stopPropagation(); openWorldClassDeliveryModal('${escInvNoForJs}')" class="gdn-kinetic-btn kinetic-glow-arm kinetic-arm-emerald" style="border:none;">
                                        📦 تسليم
                                    </button>
                                    <button onclick="event.stopPropagation(); window.openSplitForInvoiceFromDelivery('${escInvNoForJs}')" class="gdn-kinetic-btn kinetic-glow-arm kinetic-arm-pink" title="تقسيم الإذن إلى أقسام متسلسلة (100/17/1 و 100/17/2)">
                                        ✂️ تقسيم
                                    </button>
                                    <button onclick="event.stopPropagation(); executeInstantOneClickDelivery('${escInvNoForJs}')" class="gdn-kinetic-btn" style="background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.35); color:#6ee7b7;" title="صرف مخزني فوري">
                                        ⚡ فوري
                                    </button>
                                ` : `
                                    <button onclick="event.stopPropagation(); openWorldClassDeliveryModal('${escInvNoForJs}')" class="gdn-kinetic-btn" style="background:rgba(56,189,248,0.16); border:1px solid rgba(56,189,248,0.35); color:#38bdf8;">
                                        👁️ الإذن
                                    </button>
                                    <button onclick="event.stopPropagation(); undoGdnDelivery('${escInvNoForJs}')" class="gdn-kinetic-btn" style="background:rgba(239,68,68,0.16); border:1px solid rgba(239,68,68,0.35); color:#fca5a5;">
                                        ↩️ تراجع
                                    </button>
                                `}
                                ${(inv.isSplit || inv.parentInvoice) ? `
                                <button onclick="event.stopPropagation(); if(typeof window.printSplitFamilyVouchers==='function') window.printSplitFamilyVouchers('${escInvNoForJs}')" class="gdn-kinetic-btn kinetic-glow-arm kinetic-arm-pink" title="طباعة الجزئين / جميع الأذونات المقسمة">
                                    🖨️ الجزئين
                                </button>
                                <button onclick="event.stopPropagation(); window.openSplitGenealogyModal('${escInvNoForJs}')" class="gdn-kinetic-btn kinetic-glow-arm kinetic-arm-cyan" title="عرض شجرة النسب والتقسيم">
                                    🌳
                                </button>` : ''}
                                <button onclick="event.stopPropagation(); showInvoiceDeliveryQrModal('${invNo}')" class="gdn-kinetic-btn" style="background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.3); color:#38bdf8;" title="باركود QR">
                                    📱
                                </button>
                                <button onclick="event.stopPropagation(); printOfficialGdnGatePass('${invNo}')" class="gdn-kinetic-btn" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#f8fafc;" title="طباعة إذن الصرف">
                                    🖨️
                                </button>
                                <button onclick="event.stopPropagation(); shareGdnOnWhatsApp('${invNo}')" class="gdn-kinetic-btn" style="background:rgba(34,197,94,0.16); border:1px solid rgba(34,197,94,0.35); color:#4ade80;" title="واتساب">
                                    💬
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            const loadMoreRow = filtered.length > visibleSlice.length ? `
                <tr>
                    <td colspan="6" style="text-align:center; padding:14px;">
                        <button type="button" onclick="setGdnHubFilterState('pageLimit', (window.gdnHubState.pageLimit || 100) + 200)" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #0284c7, #0369a1); color:#ffffff; border:1px solid #38bdf8; padding:8px 24px; font-size:0.9rem;">
                            ⬇️ عرض 200 إذن إضافي (المتبقي: ${(filtered.length - visibleSlice.length).toLocaleString('en-US')} إذن)
                        </button>
                    </td>
                </tr>
            ` : '';

            tbody.innerHTML = rowsMarkup + loadMoreRow;
        };

        window.highlightGdnRow = function(idx) {
            window.gdnActiveRowIndex = idx;
            document.querySelectorAll('#delivery-transactions-body tr.gdn-row').forEach((tr, i) => {
                tr.classList.toggle('gdn-active-row', i === idx);
            });
        };

        // Render Origin Voucher Items as a Precision Row-by-Row & Cell-by-Cell Table with Full Column Show/Hide & Width Control
        window.renderModalOriginColumnsTable = function(draftQtys = {}, draftWhs = {}) {
            const ctx = window.pendingDeliveryContext;
            if (!ctx || !ctx.invoiceObj) return;
            const inv = ctx.invoiceObj;
            const selectedWhObj = { id: ctx.selectedWhId, name: ctx.selectedWhName };
            const warehouses = getWarehousesList();
            const optCols = getGdnOptionalColsConfig();

            const chkMap = {
                'chk-gdn-col-wh': optCols.showWarehouse,
                'chk-gdn-col-item': optCols.showItemName,
                'chk-gdn-col-spec': optCols.showItemSpec,
                'chk-gdn-col-punit': optCols.showPrimaryUnit,
                'chk-gdn-col-oqty': optCols.showOrderedQty,
                'chk-gdn-col-units': optCols.showUnitsCount,
                'chk-gdn-col-avail': optCols.showAvailStock,
                'chk-gdn-col-price': optCols.showPriceTotal,
                'chk-gdn-col-recp': optCols.showReceivePrimary,
                'chk-gdn-col-recb': optCols.showReceiveBase,
                'chk-gdn-col-net': optCols.showNetSummary
            };
            Object.keys(chkMap).forEach(id => {
                const el = document.getElementById(id);
                if (el) el.checked = !!chkMap[id];
            });

            const itemsContainer = document.getElementById('delivery-modal-items-preview');
            const countBadge = document.getElementById('del-modal-items-count-badge');
            const items = Array.isArray(inv.items) ? inv.items : [];
            const currencyLabel = inv.currency || 'SDG';

            if (!itemsContainer) return;
            if (items.length === 0) {
                itemsContainer.innerHTML = `<div style="text-align:center; padding:24px; color:#fca5a5; font-weight:800;">⚠️ لا توجد أصناف مسجلة داخل هذه الفاتورة</div>`;
                if (countBadge) countBadge.textContent = '0 أصناف';
                return;
            }

            let totalOrigQty = 0;
            let totalRemUnits = 0;
            let totalBaseUnits = 0;
            let totalAmountSum = 0;

            const rowsHtml = items.map((item, idx) => {
                const meta = resolveItemMeta(item, selectedWhObj);
                const totalQ = meta.qty;
                const prevDel = parseFloat(item.deliveredQty || 0) || 0;
                const isAlreadyDel = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Delivered_Pending_Admin' || inv.deliveryStatus === 'Admin_Closed';
                const remQ = isAlreadyDel ? totalQ : Math.max(0, totalQ - prevDel);
                const currentDelVal = draftQtys[idx] !== undefined ? parseFloat(draftQtys[idx]) : remQ;
                const safeDelVal = isNaN(currentDelVal) ? remQ : currentDelVal;
                const currentWhId = draftWhs[idx] !== undefined ? draftWhs[idx] : meta.whId;
                const liveBaseNow = safeDelVal * meta.factor;
                const remBaseMax = +(remQ * meta.factor).toFixed(2);
                const pct = remQ > 0 ? Math.min(100, Math.round((safeDelVal / remQ) * 100)) : 100;
                const pctColor = pct >= 100 ? '#10b981' : (pct > 0 ? '#f59e0b' : '#ef4444');

                totalOrigQty += totalQ;
                totalRemUnits += safeDelVal;
                totalBaseUnits += liveBaseNow;
                totalAmountSum += meta.total;

                const availStock = getAvailableStockForItem(item, currentWhId);
                const isLowStock = availStock !== null && availStock < safeDelVal;
                const whSelectOptions = warehouses.map(w =>
                    `<option value="${w.id}" ${String(w.id) === String(currentWhId) || w.name === String(currentWhId) ? 'selected' : ''}>${w.name}</option>`
                ).join('');

                return `
                    <tr>
                        <!-- 1. # -->
                        <td style="text-align:center; width:44px;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:rgba(56,189,248,0.15); color:#38bdf8; font-weight:900; font-size:0.86rem;">${idx + 1}</span>
                        </td>

                        <!-- 2. المخزن -->
                        <td style="text-align:right; width:160px; min-width:145px; ${optCols.showWarehouse ? '' : 'display:none !important;'}">
                            <select class="gdn-borderless-select gdn-modal-item-wh" data-item-idx="${idx}" onchange="refreshModalWithDraftPreserved()" style="width:100%; padding:7px 10px !important; font-size:0.86rem !important; color:#fcd34d !important; background:#020617 !important; border-color:rgba(245,158,11,0.4) !important;">
                                ${whSelectOptions}
                            </select>
                        </td>

                        <!-- 3. الصنف -->
                        <td style="text-align:right; min-width:190px; ${optCols.showItemName ? '' : 'display:none !important;'}">
                            <div style="font-weight:900; color:#ffffff; font-size:1.02rem; line-height:1.35;">${meta.name}</div>
                            ${meta.code ? `<div style="font-size:0.72rem; color:#94a3b8; font-family:'Outfit',sans-serif;">#${meta.code}</div>` : ''}
                        </td>

                        <!-- 4. البيان ومعادلة العبوة -->
                        <td style="text-align:center; min-width:155px; ${optCols.showItemSpec ? '' : 'display:none !important;'}">
                            <div style="display:inline-flex; align-items:center; gap:5px; font-size:0.8rem; color:#38bdf8; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); padding:4px 10px; border-radius:8px; font-weight:900; white-space:nowrap;">
                                ⚖️ ${meta.tonRefHint || (`1 ${meta.unit} = ${meta.factor} ${meta.baseUnit}`)}
                            </div>
                        </td>

                        <!-- 5. الوحدة الأساسية بالإذن -->
                        <td style="text-align:center; width:115px; ${optCols.showPrimaryUnit ? '' : 'display:none !important;'}">
                            <span class="gdn-unit-badge">
                                🏷️ ${meta.unit}
                            </span>
                        </td>

                        <!-- 6. الكمية بالإذن -->
                        <td style="text-align:center; width:105px; background:rgba(56,189,248,0.05); ${optCols.showOrderedQty ? '' : 'display:none !important;'}">
                            <div style="font-size:1.18rem; font-weight:900; color:#38bdf8; font-family:'Outfit',sans-serif;">
                                ${totalQ.toLocaleString('en-US', { maximumFractionDigits: 3 })}
                            </div>
                            <div style="font-size:0.74rem; color:#94a3b8; font-weight:800;">${meta.unit}</div>
                            ${prevDel > 0 && !isAlreadyDel ? `<div style="font-size:0.72rem; color:#34d399; margin-top:2px;">سُلّم: ${prevDel}</div>` : ''}
                        </td>

                        <!-- 7 & 8. الوحدة الفرعية وعدد الوحدات -->
                        <td style="text-align:center; width:125px; padding:10px !important; background:rgba(99,102,241,0.04); ${optCols.showUnitsCount ? '' : 'display:none !important;'}">
                            <div class="gdn-receive-unit-box units-mirror-box" onclick="setRowDeliveryUnitMode(${idx}, 'base', false)" style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; padding:8px 10px; border-radius:12px; cursor:pointer;" title="اضغط أو مرر المؤشر للتفعيل بالوحدة الفرعية">
                                <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:8px; background:rgba(99,102,241,0.18); border:1px solid rgba(129,140,248,0.4); color:#c4b5fd; font-size:0.84rem; font-weight:900;">
                                    🔢 ${meta.baseUnit}
                                </span>
                                <div style="font-size:0.72rem; color:#818cf8; font-weight:800;">المعامل: ×${meta.factor}</div>
                            </div>
                        </td>
                        <td style="text-align:center; width:135px; padding:10px !important; background:rgba(99,102,241,0.06); ${optCols.showUnitsCount ? '' : 'display:none !important;'}">
                            <div class="gdn-receive-unit-box units-mirror-box" onclick="setRowDeliveryUnitMode(${idx}, 'base', true)" style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px; padding:8px 10px; border-radius:12px; cursor:pointer;" title="اضغط لاستلام كامل المعادل بالوحدات الفرعية">
                                <div id="gdn-row-baseqty-${idx}" style="font-size:1.15rem; font-weight:900; color:#c4b5fd; font-family:'Outfit',sans-serif;">
                                    ${liveBaseNow.toLocaleString('en-US', { maximumFractionDigits: 2 })}
                                </div>
                                <div style="font-size:0.74rem; color:#a5b4fc; font-weight:800;">${meta.baseUnit}</div>
                            </div>
                        </td>

                        <!-- 9 & 10. السعر والإجمالي -->
                        <td style="text-align:center; width:100px; ${optCols.showPriceTotal ? '' : 'display:none !important;'}">
                            <div style="font-size:0.95rem; font-weight:900; color:#fcd34d; font-family:'Outfit',sans-serif;">
                                ${meta.price.toLocaleString('en-US', { maximumFractionDigits: 2 })}
                            </div>
                        </td>
                        <td style="text-align:center; width:115px; ${optCols.showPriceTotal ? '' : 'display:none !important;'}">
                            <div style="font-size:0.96rem; font-weight:900; color:#6ee7b7; font-family:'Outfit',sans-serif;">
                                ${meta.total.toLocaleString('en-US', { maximumFractionDigits: 2 })}
                            </div>
                        </td>

                        <!-- 11. الرصيد المتاح بالمخزن -->
                        <td style="text-align:center; width:100px; ${optCols.showAvailStock ? '' : 'display:none !important;'}">
                            <span style="font-size:0.94rem; color:${isLowStock ? '#fca5a5' : '#7dd3fc'}; font-weight:900; font-family:'Outfit',sans-serif; background:${isLowStock ? 'rgba(239,68,68,0.18)' : 'transparent'}; padding:3px 8px; border-radius:6px;">
                                ${availStock !== null ? availStock.toLocaleString('en-US', { maximumFractionDigits: 2 }) : '—'}
                            </span>
                        </td>

                        <!-- 12. الخانة الأولى للاستلام: بالوحدة الأساسية (استلم هنا) -->
                        <td style="text-align:center; width:264px; min-width:264px; max-width:264px; padding:10px !important; overflow:visible !important; background:rgba(16,185,129,0.03); ${optCols.showReceivePrimary ? '' : 'display:none !important;'}">
                            <div id="gdn-unit-box-primary-${idx}" class="gdn-receive-unit-box primary-box" onmouseenter="setRowDeliveryUnitMode(${idx}, 'primary', false)">
                                <div style="display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:6px; width:100%;">
                                    <button type="button" id="gdn-mode-btn-primary-${idx}" onclick="setRowDeliveryUnitMode(${idx}, 'primary', false)" class="gdn-kinetic-btn gdn-unit-fixed-pill" style="height:29px !important; width:100% !important; padding:0 8px !important; font-size:0.76rem !important; font-weight:900 !important; background:#020617 !important; color:#ffffff !important; border:1.5px solid rgba(52,211,153,0.55) !important; border-radius:7px !important; white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important;">
                                        📦 بالـ ${meta.unit}
                                    </button>
                                    <button type="button" onclick="setRowDeliveryUnitMode(${idx}, 'primary', true)" class="gdn-kinetic-btn gdn-unit-fixed-pill" style="height:29px !important; width:100% !important; padding:0 8px !important; font-size:0.75rem !important; font-weight:900 !important; background:#020617 !important; color:#ffffff !important; border:1.5px solid rgba(56,189,248,0.55) !important; border-radius:7px !important; white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important;" title="استلام كامل المتبقي بالـ ${meta.unit}">
                                        ⚡ الكل (${remQ})
                                    </button>
                                </div>
                                <div style="display:flex; align-items:center; justify-content:center; gap:6px; direction:ltr; width:100%; height:38px;">
                                    <button type="button" onclick="adjustModalItemQty(${idx}, -1, 'primary')" class="gdn-qty-step-btn" style="width:36px !important; height:36px !important; min-width:36px !important; background:#1e1b2e !important; color:#fca5a5 !important; border-color:rgba(248,113,113,0.45) !important;">−</button>
                                    <input type="text" inputmode="decimal" class="gdn-qty-large-input gdn-modal-deliver-qty" data-item-idx="${idx}" data-max-rem="${remQ}" data-unit-factor="${meta.factor}" data-primary-unit="${meta.unit}" data-base-unit="${meta.baseUnit}" data-active-unit-mode="primary" value="${safeDelVal}" oninput="onPrimaryDeliverQtyInput(${idx})" style="width:124px !important; min-width:124px !important; max-width:124px !important; height:36px !important; color:#ffffff !important; background:#020617 !important; border:1.5px solid #10b981 !important;" title="اكتب الكمية المستلمة بالـ ${meta.unit}">
                                    <button type="button" onclick="adjustModalItemQty(${idx}, 1, 'primary')" class="gdn-qty-step-btn" style="width:36px !important; height:36px !important; min-width:36px !important; background:#06281e !important; color:#ffffff !important; border-color:rgba(52,211,153,0.5) !important;">+</button>
                                </div>
                                <div class="gdn-box-caption" style="height:20px; display:flex; align-items:center; justify-content:center; font-size:0.74rem; color:#ffffff !important; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">✨ استلم هنا بالوحدة الأساسية (${meta.unit})</div>
                            </div>
                        </td>

                        <!-- 13. الخانة الثانية للاستلام: بالوحدة الفرعية (أو استلم هنا) -->
                        <td style="text-align:center; width:264px; min-width:264px; max-width:264px; padding:10px !important; overflow:visible !important; background:rgba(56,189,248,0.03); ${optCols.showReceiveBase ? '' : 'display:none !important;'}">
                            <div id="gdn-unit-box-base-${idx}" class="gdn-receive-unit-box base-box" onmouseenter="setRowDeliveryUnitMode(${idx}, 'base', false)">
                                <div style="display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:6px; width:100%;">
                                    <button type="button" id="gdn-mode-btn-base-${idx}" onclick="setRowDeliveryUnitMode(${idx}, 'base', false)" class="gdn-kinetic-btn gdn-unit-fixed-pill" style="height:29px !important; width:100% !important; padding:0 8px !important; font-size:0.76rem !important; font-weight:900 !important; background:#020617 !important; color:#ffffff !important; border:1.5px solid rgba(56,189,248,0.55) !important; border-radius:7px !important; white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important;">
                                        🔢 بالـ ${meta.baseUnit}
                                    </button>
                                    <button type="button" onclick="setRowDeliveryUnitMode(${idx}, 'base', true)" class="gdn-kinetic-btn gdn-unit-fixed-pill" style="height:29px !important; width:100% !important; padding:0 8px !important; font-size:0.75rem !important; font-weight:900 !important; background:#020617 !important; color:#ffffff !important; border:1.5px solid rgba(129,140,248,0.55) !important; border-radius:7px !important; white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important;" title="استلام كامل المتبقي بالـ ${meta.baseUnit}">
                                        ⚡ الكل (${remBaseMax})
                                    </button>
                                </div>
                                <div style="display:flex; align-items:center; justify-content:center; gap:6px; direction:ltr; width:100%; height:38px;">
                                    <button type="button" onclick="adjustModalItemQty(${idx}, -1, 'base')" class="gdn-qty-step-btn" style="width:36px !important; height:36px !important; min-width:36px !important; background:#1e1b2e !important; color:#fca5a5 !important; border-color:rgba(248,113,113,0.45) !important;">−</button>
                                    <input type="text" inputmode="decimal" class="gdn-qty-large-input gdn-modal-deliver-base-qty" data-item-idx="${idx}" data-max-base="${+(remQ * meta.factor).toFixed(4)}" data-unit-factor="${meta.factor}" value="${+liveBaseNow.toFixed(2)}" oninput="onBaseDeliverQtyInput(${idx})" style="width:124px !important; min-width:124px !important; max-width:124px !important; height:36px !important; color:#ffffff !important; background:#020617 !important; border:1.5px solid #38bdf8 !important;" title="اكتب الكمية المستلمة بالـ ${meta.baseUnit}">
                                    <button type="button" onclick="adjustModalItemQty(${idx}, 1, 'base')" class="gdn-qty-step-btn" style="width:36px !important; height:36px !important; min-width:36px !important; background:#06281e !important; color:#ffffff !important; border-color:rgba(52,211,153,0.5) !important;">+</button>
                                </div>
                                <div class="gdn-box-caption" style="height:20px; display:flex; align-items:center; justify-content:center; font-size:0.74rem; color:#ffffff !important; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">✨ استلم هنا بالوحدة الفرعية (${meta.baseUnit})</div>
                            </div>
                        </td>

                        <!-- 14. الصافي المستلم فعلياً الآن + شريط التقدم البصري -->
                        <td style="text-align:center; width:180px; min-width:165px; background:rgba(2,6,23,0.45); ${optCols.showNetSummary ? '' : 'display:none !important;'}">
                            <div id="gdn-deliver-live-eq-${idx}">
                                <div style="display:flex; flex-direction:column; gap:4px; align-items:center; justify-content:center;">
                                    <span style="background:rgba(16,185,129,0.18); border:1px solid rgba(52,211,153,0.45); color:#34d399; padding:3px 10px; border-radius:7px; font-size:0.9rem; font-weight:900; white-space:nowrap;">
                                        📦 ${safeDelVal.toLocaleString('en-US', { maximumFractionDigits: 3 })} ${meta.unit}
                                    </span>
                                    <span style="background:rgba(99,102,241,0.18); border:1px solid rgba(129,140,248,0.45); color:#c4b5fd; padding:2px 10px; border-radius:7px; font-size:0.82rem; font-weight:900; white-space:nowrap;">
                                        🔢 ⇄ ${liveBaseNow.toLocaleString('en-US', { maximumFractionDigits: 2 })} ${meta.baseUnit}
                                    </span>
                                    <div style="width:100%; background:rgba(255,255,255,0.08); height:5px; border-radius:99px; overflow:hidden; margin-top:2px;">
                                        <div style="width:${pct}%; height:100%; background:${pctColor}; transition:width 0.2s;"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            const scaleClass = optCols.colWidthScale === 'ultrawide' ? 'gdn-scale-ultrawide' : (optCols.colWidthScale === 'wide' ? 'gdn-scale-wide' : '');

            itemsContainer.innerHTML = `
                <table class="gdn-origin-columns-table ${scaleClass}">
                    <thead>
                        <tr>
                            <th style="text-align:center; width:44px;">#</th>
                            <th style="text-align:right; width:160px; ${optCols.showWarehouse ? '' : 'display:none !important;'}">🏢 المخزن</th>
                            <th style="text-align:right; min-width:190px; ${optCols.showItemName ? '' : 'display:none !important;'}">📦 الصنف</th>
                            <th style="text-align:center; min-width:155px; ${optCols.showItemSpec ? '' : 'display:none !important;'}">⚖️ البيان / المعامل</th>
                            <th style="text-align:center; width:115px; ${optCols.showPrimaryUnit ? '' : 'display:none !important;'}">🏷️ الوحدة الأساسية</th>
                            <th style="text-align:center; width:105px; color:#38bdf8 !important; ${optCols.showOrderedQty ? '' : 'display:none !important;'}">📊 الكمية بالإذن</th>
                            <th style="text-align:center; width:110px; color:#c4b5fd !important; ${optCols.showUnitsCount ? '' : 'display:none !important;'}">🔢 الوحدة الفرعية</th>
                            <th style="text-align:center; width:120px; color:#a5b4fc !important; ${optCols.showUnitsCount ? '' : 'display:none !important;'}">📐 المعادل بالوحدات</th>
                            <th style="text-align:center; width:100px; color:#fcd34d !important; ${optCols.showPriceTotal ? '' : 'display:none !important;'}">💰 السعر</th>
                            <th style="text-align:center; width:115px; color:#6ee7b7 !important; ${optCols.showPriceTotal ? '' : 'display:none !important;'}">💎 الإجمالي</th>
                            <th style="text-align:center; width:100px; color:#7dd3fc !important; ${optCols.showAvailStock ? '' : 'display:none !important;'}">🏛️ المتاح</th>
                            <th style="text-align:center; width:255px; color:#34d399 !important; ${optCols.showReceivePrimary ? '' : 'display:none !important;'}">📥 استلام بالوحدة الأساسية (استلم هنا)</th>
                            <th style="text-align:center; width:255px; color:#c4b5fd !important; ${optCols.showReceiveBase ? '' : 'display:none !important;'}">🔄 استلام بالوحدة الفرعية (أو استلم هنا)</th>
                            <th style="text-align:center; width:180px; color:#6ee7b7 !important; ${optCols.showNetSummary ? '' : 'display:none !important;'}">✅ الصافي المستلم الآن</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="text-align:center; color:#a7f3d0;">∑</td>
                            <td style="text-align:right; color:#fcd34d; ${optCols.showWarehouse ? '' : 'display:none !important;'}">إجمالي البنود:</td>
                            <td style="text-align:right; color:#ffffff; ${optCols.showItemName ? '' : 'display:none !important;'}"><strong>${items.length} أصناف</strong></td>
                            <td style="text-align:center; color:#94a3b8; ${optCols.showItemSpec ? '' : 'display:none !important;'}">-</td>
                            <td style="text-align:center; color:#94a3b8; ${optCols.showPrimaryUnit ? '' : 'display:none !important;'}">-</td>
                            <td style="text-align:center; color:#38bdf8; font-family:'Outfit',sans-serif; font-size:1.05rem; ${optCols.showOrderedQty ? '' : 'display:none !important;'}">
                                ${totalOrigQty.toLocaleString('en-US', { maximumFractionDigits: 3 })}
                            </td>
                            <td style="text-align:center; color:#94a3b8; ${optCols.showUnitsCount ? '' : 'display:none !important;'}">-</td>
                            <td id="gdn-foot-base-now" style="text-align:center; color:#c4b5fd; font-family:'Outfit',sans-serif; font-size:1.05rem; ${optCols.showUnitsCount ? '' : 'display:none !important;'}">
                                ${totalBaseUnits.toLocaleString('en-US', { maximumFractionDigits: 2 })}
                            </td>
                            <td style="text-align:center; ${optCols.showPriceTotal ? '' : 'display:none !important;'}">-</td>
                            <td style="text-align:center; color:#6ee7b7; font-family:'Outfit',sans-serif; ${optCols.showPriceTotal ? '' : 'display:none !important;'}">
                                ${totalAmountSum.toLocaleString('en-US', { maximumFractionDigits: 2 })} ${currencyLabel}
                            </td>
                            <td style="text-align:center; ${optCols.showAvailStock ? '' : 'display:none !important;'}">-</td>
                            <td style="text-align:center; color:#34d399; font-family:'Outfit',sans-serif; font-size:1.1rem; ${optCols.showReceivePrimary ? '' : 'display:none !important;'}">
                                📦 <span id="gdn-foot-deliver-now">${totalRemUnits.toLocaleString('en-US', { maximumFractionDigits: 3 })}</span>
                            </td>
                            <td style="text-align:center; color:#c4b5fd; font-family:'Outfit',sans-serif; font-size:1.1rem; ${optCols.showReceiveBase ? '' : 'display:none !important;'}">
                                🔢 <span id="gdn-foot-deliver-base-now">${totalBaseUnits.toLocaleString('en-US', { maximumFractionDigits: 2 })}</span>
                            </td>
                            <td style="text-align:center; color:#6ee7b7; ${optCols.showNetSummary ? '' : 'display:none !important;'}">
                                ✅ جاهز للصرف
                            </td>
                        </tr>
                    </tfoot>
                </table>
            `;

            if (countBadge) {
                countBadge.textContent = `${items.length} أصناف | إجمالي الوحدات الفرعية: ${totalBaseUnits.toLocaleString('en-US', { maximumFractionDigits: 2 })}`;
            }
        };

        // Smart AI Modal Opener with Full Item Inspection, Stock Check & Partial/Full Controls
        window.openWorldClassDeliveryModal = function(invIdentifier, targetItemIdx = null, preferredUnitMode = 'primary') {
            upgradeDeliveryHubDOM();
            const invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const inv = invoices.find(i => String(i.invoiceNo || i.serial || i.id) === String(invIdentifier));
            if (!inv) {
                if (typeof showToast === 'function') showToast('❌ لم يتم العثور على الفاتورة', 'error');
                return;
            }

            const invNo = String(inv.invoiceNo || inv.serial || inv.id);
            const whSelect = document.getElementById(`del-wh-${invIdentifier}`);
            const warehouses = getWarehousesList();
            const selectedWhId = whSelect ? whSelect.value : (inv.warehouseId || inv.warehouse || warehouses[0].id);
            const selectedWhObj = warehouses.find(w => String(w.id) === String(selectedWhId) || w.name === selectedWhId) || warehouses[0];
            const custName = resolveCustomerName(inv);
            const { dateStr, timeStr } = parseInvoiceDateTimeParts(inv);

            window.pendingDeliveryContext = {
                invIdentifier: invNo,
                selectedWhId: selectedWhObj.id,
                selectedWhName: selectedWhObj.name,
                invoiceObj: inv
            };

            const invEl = document.getElementById('del-modal-inv-no');
            const dtEl = document.getElementById('del-modal-datetime');
            const custEl = document.getElementById('del-modal-cust-name');
            const whEl = document.getElementById('del-modal-wh-name');
            const amtEl = document.getElementById('del-modal-amount');
            const gdnBadge = document.getElementById('del-modal-gdn-badge');

            if (invEl) invEl.textContent = '#' + invNo;
            if (dtEl) dtEl.textContent = `${dateStr} | ${timeStr}`;
            if (custEl) custEl.textContent = custName;
            if (whEl) whEl.textContent = selectedWhObj.name;
            if (amtEl) {
                const totalVal = parseFloat(inv.grandTotal ?? inv.total ?? inv.totals?.grandTotal ?? 0) || 0;
                amtEl.textContent = totalVal.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ' + (inv.currency || 'SDG');
            }
            if (gdnBadge) gdnBadge.textContent = inv.deliveryNo || ('GDN-' + Date.now().toString().slice(-5));

            const recInput = document.getElementById('gdn-receiver-name');
            const drvInput = document.getElementById('gdn-driver-name');
            const trkInput = document.getElementById('gdn-truck-plate');
            const gateInput = document.getElementById('gdn-gate-pass');
            const notesInput = document.getElementById('gdn-warehouse-notes');
            if (recInput) recInput.value = inv.receiverName || custName || '';
            if (drvInput) drvInput.value = inv.driverName || '';
            if (trkInput) trkInput.value = inv.truckPlate || '';
            if (gateInput) gateInput.value = inv.gatePassNo || ('GP-' + Date.now().toString().slice(-5));
            if (notesInput) notesInput.value = inv.warehouseNotes || inv.notes || '';

            window.renderModalOriginColumnsTable();

            const modal = document.getElementById('delivery-confirmation-modal');
            if (modal) modal.style.display = 'flex';

            if (targetItemIdx !== null && targetItemIdx !== undefined) {
                setTimeout(() => {
                    window.setRowDeliveryUnitMode(targetItemIdx, preferredUnitMode, true);
                    const targetInput = preferredUnitMode === 'base'
                        ? document.querySelector(`.gdn-modal-deliver-base-qty[data-item-idx="${targetItemIdx}"]`)
                        : document.querySelector(`.gdn-modal-deliver-qty[data-item-idx="${targetItemIdx}"]`);
                    if (targetInput) {
                        targetInput.focus();
                        targetInput.select();
                    }
                }, 60);
            }
        };

        window.confirmAndPrintDeliveryAction = async function() {
            const ctx = window.pendingDeliveryContext;
            const invNo = ctx ? ctx.invIdentifier : null;
            await window.confirmExecuteDeliveryAction();
            if (invNo) {
                setTimeout(() => window.printOfficialGdnGatePass(invNo), 250);
            }
        };

        // Instant 1-Click or QR Scan Delivery Deduction without extra clicks
        window.executeInstantOneClickDelivery = function(invIdentifier) {
            const invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const inv = invoices.find(i => String(i.invoiceNo || i.serial || i.id) === String(invIdentifier));
            if (!inv) {
                if (typeof showToast === 'function') showToast('❌ الفاتورة غير موجودة', 'error');
                return;
            }
            const whSelect = document.getElementById(`del-wh-${invIdentifier}`);
            const warehouses = getWarehousesList();
            const selectedWhId = whSelect ? whSelect.value : (inv.warehouseId || inv.warehouse || warehouses[0].id);
            const selectedWhObj = warehouses.find(w => String(w.id) === String(selectedWhId)) || warehouses[0];

            window.pendingDeliveryContext = {
                invIdentifier,
                selectedWhId,
                selectedWhName: selectedWhObj.name,
                instantFullDelivery: true
            };
            window.confirmExecuteDeliveryAction();
        };

        // Override confirmExecuteDeliveryAction to connect 100% with erp_delivery_notes_v1, erp_items_v1, erp_inventory_v1, and api/mirror_sync.php
        window.confirmExecuteDeliveryAction = async function() {
            if (!window.pendingDeliveryContext) return;
            const { invIdentifier, selectedWhId, selectedWhName, instantFullDelivery } = window.pendingDeliveryContext;

            let invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const invIndex = invoices.findIndex(i => String(i.invoiceNo || i.serial || i.id) === String(invIdentifier));
            if (invIndex === -1) return;

            const inv = invoices[invIndex];
            const invNo = String(inv.invoiceNo || inv.serial || inv.id);

            // Read quantities and per-row warehouses to deliver now (supports partial or full delivery)
            const qtyInputs = document.querySelectorAll('.gdn-modal-deliver-qty');
            const whSelects = document.querySelectorAll('.gdn-modal-item-wh');
            const warehouses = getWarehousesList();
            let isPartialDelivery = false;
            const deliveredItemsPayload = [];

            const invItems = Array.isArray(inv.items) ? inv.items : [];
            invItems.forEach((item, idx) => {
                const origQty = parseFloat(item.qty || item.quantity || 0);
                const prevDel = parseFloat(item.deliveredQty || 0);
                const maxRem = Math.max(0, origQty - prevDel);
                let deliverNow = maxRem;

                if (!instantFullDelivery && qtyInputs[idx]) {
                    const parsed = parseFloat(qtyInputs[idx].value);
                    if (!isNaN(parsed)) deliverNow = Math.min(maxRem, Math.max(0, parsed));
                }

                const rowWhId = (!instantFullDelivery && whSelects[idx]) ? whSelects[idx].value : (item.warehouseId || item.warehouse || selectedWhId);
                const rowWhObj = warehouses.find(w => String(w.id) === String(rowWhId) || w.name === String(rowWhId)) || { id: selectedWhId, name: selectedWhName };

                item.deliveredQty = prevDel + deliverNow;
                if (item.deliveredQty < origQty - 0.0001) {
                    isPartialDelivery = true;
                }

                if (deliverNow > 0) {
                    const meta = resolveItemMeta(item, rowWhObj);
                    deliveredItemsPayload.push({
                        ...item,
                        name: meta.name,
                        itemName: meta.name,
                        unit: meta.unit,
                        selectedUnit: meta.unit,
                        qty: deliverNow,
                        quantity: deliverNow,
                        orderedQty: origQty,
                        warehouseId: rowWhObj.id,
                        warehouseName: rowWhObj.name
                    });
                }
            });

            if (deliveredItemsPayload.length === 0) {
                if (typeof showToast === 'function') showToast('⚠️ يرجى تحديد كمية أكبر من صفر للتسليم', 'warning');
                return;
            }

            const gdnNo = 'GDN-' + Date.now().toString().slice(-6);
            const nowIso = new Date().toISOString();
            const todayDate = nowIso.slice(0, 10);
            const nowTime = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            const custResolved = resolveCustomerName(inv);
            const receiverName = document.getElementById('gdn-receiver-name')?.value || custResolved || 'العميل';
            const driverName = document.getElementById('gdn-driver-name')?.value || '';
            const truckPlate = document.getElementById('gdn-truck-plate')?.value || '';
            const gatePassNo = document.getElementById('gdn-gate-pass')?.value || ('GP-' + Date.now().toString().slice(-5));
            const warehouseNotes = document.getElementById('gdn-warehouse-notes')?.value || '';

            // 1. Update Invoice Status & Fields
            inv.isDelivered = !isPartialDelivery;
            inv.deliveryStatus = isPartialDelivery ? 'partial' : 'delivered';
            inv.status = isPartialDelivery ? 'partial' : 'delivered';
            inv.deliveryNo = gdnNo;
            inv.deliveredWarehouse = selectedWhId;
            inv.deliveredWarehouseName = selectedWhName;
            inv.deliveredAt = nowIso;
            inv.deliveryDate = todayDate;
            inv.deliveryTime = nowTime;
            inv.receiverName = receiverName;
            inv.driverName = driverName;
            inv.truckPlate = truckPlate;
            inv.gatePassNo = gatePassNo;
            inv.warehouseNotes = warehouseNotes;
            invoices[invIndex] = inv;
            localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(invoices));
            localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));

            // 2. Deduct Stock from BOTH erp_inventory_v1 AND erp_items_v1
            let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
            let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

            deliveredItemsPayload.forEach(dItem => {
                const deductQty = parseFloat(dItem.qty || 0);
                const itemNameClean = String(dItem.name || '').trim();
                const targetWhId = dItem.warehouseId || selectedWhId;

                // Update erp_inventory_v1
                let invEntry = inventory.find(x =>
                    (String(x.id) === String(dItem.itemId || dItem.id) || String(x.name || '').trim() === itemNameClean) &&
                    String(x.warehouseId || 1) === String(targetWhId)
                );
                if (invEntry) {
                    invEntry.qty = (parseFloat(invEntry.qty || 0) - deductQty);
                } else {
                    inventory.push({
                        id: dItem.itemId || dItem.id || Date.now(),
                        name: itemNameClean,
                        warehouseId: targetWhId,
                        qty: -deductQty,
                        unit: dItem.unit || 'قطعة'
                    });
                }

                // Update erp_items_v1 (stock & actualStock per warehouse + total quantity)
                const masterIdx = erpItems.findIndex(x =>
                    String(x.id) === String(dItem.itemId || dItem.id) ||
                    String(x.name || '').trim() === itemNameClean
                );
                if (masterIdx !== -1) {
                    const mItem = erpItems[masterIdx];
                    if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
                    if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
                    mItem.stock[targetWhId] = (parseFloat(mItem.stock[targetWhId]) || 0) - deductQty;
                    mItem.actualStock[targetWhId] = (parseFloat(mItem.actualStock[targetWhId]) || 0) - deductQty;
                    if (mItem.quantity !== undefined) {
                        mItem.quantity = (parseFloat(mItem.quantity) || 0) - deductQty;
                    }
                    erpItems[masterIdx] = mItem;
                }
            });

            localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
            localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));
            if (typeof window.items !== 'undefined' && Array.isArray(erpItems)) {
                window.items = erpItems;
            }

            // 3. Create Official GDN Record in erp_delivery_notes_v1 (for Item Ledger, Warehouse Ledger & Reports)
            const gdnRecord = {
                id: gdnNo,
                deliveryNo: gdnNo,
                invoiceNo: invNo,
                relatedInvoice: invNo,
                date: todayDate,
                time: nowTime,
                createdAt: nowIso,
                customer: custResolved,
                customerName: custResolved,
                warehouse: selectedWhName,
                warehouseId: selectedWhId,
                receiverName,
                driverName,
                truckPlate,
                gatePassNo,
                notes: warehouseNotes,
                warehouseNotes,
                isPartialDelivery,
                status: 'delivered',
                items: deliveredItemsPayload,
                deleted: 0
            };

            const deliveryNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
            deliveryNotes.unshift(gdnRecord);
            localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(deliveryNotes));

            // 4. Sync with MariaDB via api/mirror_sync.php?action=save_delivery_note
            try {
                fetch('api/mirror_sync.php?action=save_delivery_note', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ...gdnRecord,
                        updatedInvoiceItems: inv.items
                    })
                }).catch(() => {});
                if (typeof window.pushToCloud === 'function') {
                    window.pushToCloud(true);
                }
            } catch (e) {}

            if (typeof closeDeliveryConfirmationModal === 'function') {
                closeDeliveryConfirmationModal();
            } else {
                const modal = document.getElementById('delivery-confirmation-modal');
                if (modal) modal.style.display = 'none';
            }

            window.loadDeliveryTransactions();
            if (typeof loadSplitInvoices === 'function') loadSplitInvoices();

            if (typeof showToast === 'function') {
                showToast(`✅ تم تسليم البضاعة وخصم المخزون بنجاح (إذن صرف رقم ${gdnNo})`, 'success');
            }
        };

        // QR Code Generator Modal for Any Invoice
        window.showInvoiceDeliveryQrModal = function(invNo) {
            const invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const inv = invoices.find(i => String(i.invoiceNo || i.serial || i.id) === String(invNo));
            if (!inv) return;

            const qrTargetUrl = `${window.location.origin}${window.location.pathname}?action=delivery&qr_deliver=${encodeURIComponent(invNo)}&auto_deduct=1`;
            const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=8&data=${encodeURIComponent(qrTargetUrl)}`;

            document.getElementById('gdn-qr-display-title').textContent = `باركود تسليم فاتورة #${invNo}`;
            document.getElementById('gdn-qr-display-sub').textContent = `العميل: ${resolveCustomerName(inv)} — امسح بالموبايل للخصم الفوري`;
            document.getElementById('gdn-qr-display-img').src = qrImgUrl;

            const itemsHtml = (inv.items || []).map(it => {
                const m = resolveItemMeta(it);
                return `<div>• ${m.name} — <b>${it.qty ?? it.quantity ?? 0} ${m.unit}</b></div>`;
            }).join('');
            document.getElementById('gdn-qr-display-items').innerHTML = itemsHtml || 'لا توجد أصناف';

            const instantBtn = document.getElementById('gdn-qr-instant-btn');
            if (instantBtn) {
                instantBtn.onclick = function() {
                    document.getElementById('gdn-qr-display-modal').style.display = 'none';
                    executeInstantOneClickDelivery(invNo);
                };
            }

            document.getElementById('gdn-qr-display-modal').style.display = 'flex';
        };

        // Continuous Zero-Click QR State & Mobile-to-Laptop Live Bridge
        const gdnQrCooldownMap = {};
        let gdnBatchDeliveredCount = 0;
        let gdnBatchCancelledCount = 0;
        let gdnBridgePollInterval = null;
        const gdnProcessedBridgeScans = {};
        let gdnBroadcastChan = null;
        const GDN_BRIDGE_CHANNEL = 'default_gate';

        window.playGdnCancelledAlarm = function() {
            try {
                if (navigator.vibrate) navigator.vibrate([190, 70, 230]);
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(280, ctx.currentTime);
                osc.frequency.setValueAtTime(180, ctx.currentTime + 0.14);
                gain.gain.setValueAtTime(0.32, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.42);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.42);
            } catch (e) {}
        };

        function updateGdnContinuousScanUI(type, message) {
            const modalBanner = document.getElementById('gdn-scanner-live-banner');
            const hubBanner = document.getElementById('gdn-live-continuous-banner');
            const hubBannerTxt = document.getElementById('gdn-live-continuous-banner-text');
            const counterEl = document.getElementById('gdn-modal-scan-counter');
            const feedEl = document.getElementById('gdn-scanner-live-feed');
            const viewport = document.getElementById('gdn-camera-viewport');

            if (counterEl) {
                counterEl.textContent = `✅ المستلم: ${gdnBatchDeliveredCount} | 🚫 الملغي: ${gdnBatchCancelledCount}`;
            }

            if (modalBanner) {
                modalBanner.style.background = type === 'ok'
                    ? 'linear-gradient(135deg, rgba(6,78,59,0.95), rgba(5,150,105,0.9))'
                    : 'linear-gradient(135deg, rgba(127,29,29,0.96), rgba(220,38,38,0.92))';
                modalBanner.style.borderColor = type === 'ok' ? '#34d399' : '#fca5a5';
                modalBanner.style.color = '#ffffff';
                modalBanner.innerHTML = message;
            }

            if (hubBanner && hubBannerTxt) {
                hubBanner.style.display = 'flex';
                hubBanner.style.background = type === 'ok'
                    ? 'linear-gradient(135deg, rgba(6,78,59,0.95), rgba(5,150,105,0.9))'
                    : 'linear-gradient(135deg, rgba(127,29,29,0.96), rgba(220,38,38,0.92))';
                hubBanner.style.border = type === 'ok' ? '2px solid #34d399' : '2px solid #fca5a5';
                hubBanner.style.color = '#ffffff';
                hubBannerTxt.innerHTML = `${message} &nbsp; <span style="background:rgba(0,0,0,0.3); padding:2px 10px; border-radius:99px; font-size:0.8rem;">✅ المستلم: ${gdnBatchDeliveredCount} | 🚫 الملغي: ${gdnBatchCancelledCount}</span>`;
            }

            if (viewport) {
                viewport.style.borderColor = type === 'ok' ? '#22c55e' : '#ef4444';
                viewport.style.boxShadow = type === 'ok' ? '0 0 45px rgba(34,197,94,0.85)' : '0 0 45px rgba(239,68,68,0.85)';
                setTimeout(() => {
                    viewport.style.borderColor = '#10b981';
                    viewport.style.boxShadow = 'none';
                }, 950);
            }

            if (feedEl) {
                if (feedEl.textContent.includes('سيظهر هنا')) feedEl.innerHTML = '';
                const row = document.createElement('div');
                row.style.cssText = `padding:5px 10px; margin-bottom:4px; border-radius:8px; font-weight:800; color:${type === 'ok' ? '#6ee7b7' : '#fecaca'}; background:${type === 'ok' ? 'rgba(16,185,129,0.16)' : 'rgba(239,68,68,0.2)'}; border-right:3px solid ${type === 'ok' ? '#10b981' : '#ef4444'};`;
                row.innerHTML = `${message} <span style="float:left; font-size:0.72rem; opacity:0.8;">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}</span>`;
                feedEl.prepend(row);
            }
        }

        async function setupMobileBridgePairingInModal() {
            let baseOrigin = window.location.origin;
            const pathDir = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
            try {
                const res = await fetch(`api/qr_live_bridge.php?action=info&channel=${encodeURIComponent(GDN_BRIDGE_CHANNEL)}`);
                const info = await res.json();
                if (info && info.lanIp && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
                    const portPart = window.location.port ? `:${window.location.port}` : '';
                    baseOrigin = `${window.location.protocol}//${info.lanIp}${portPart}`;
                }
            } catch (e) {}

            const mobileGunUrl = `${baseOrigin}${pathDir}mobile-qr-gun.php?channel=${encodeURIComponent(GDN_BRIDGE_CHANNEL)}`;
            const qrImg = document.getElementById('gdn-mobile-bridge-qr-img');
            if (qrImg) {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=5&data=${encodeURIComponent(mobileGunUrl)}`;
            }
            const linkEl = document.getElementById('gdn-mobile-bridge-link');
            if (linkEl) {
                linkEl.href = mobileGunUrl;
            }
        }

        function startDirectSalesMobileBridgeListener() {
            if (!gdnBroadcastChan) {
                try {
                    gdnBroadcastChan = new BroadcastChannel('elsajana_qr_live_bridge');
                    gdnBroadcastChan.onmessage = (ev) => {
                        const msg = ev.data;
                        if (!msg || msg.channel !== GDN_BRIDGE_CHANNEL) return;
                        if (msg.type === 'MOBILE_CONNECTED') {
                            const dot = document.getElementById('gdn-hub-bridge-dot');
                            if (dot) { dot.style.background = '#22c55e'; dot.style.boxShadow = '0 0 8px #22c55e'; }
                        } else if (msg.type === 'QR_SCANNED' && msg.rawCode) {
                            processScannedDeliveryCode(msg.rawCode, true);
                        }
                    };
                } catch (e) {}
            }

            if (gdnBridgePollInterval) return;
            gdnBridgePollInterval = setInterval(async () => {
                try {
                    const res = await fetch(`api/qr_live_bridge.php?action=poll&channel=${encodeURIComponent(GDN_BRIDGE_CHANNEL)}`);
                    const data = await res.json();
                    if (!data || !data.ok) return;

                    const dot = document.getElementById('gdn-hub-bridge-dot');
                    if (dot) {
                        dot.style.background = data.mobileConnected ? '#22c55e' : '#fcd34d';
                        dot.style.boxShadow = data.mobileConnected ? '0 0 8px #22c55e' : 'none';
                    }

                    const scans = Array.isArray(data.unprocessedScans) ? data.unprocessedScans : [];
                    for (const s of scans) {
                        if (!s.scanId || gdnProcessedBridgeScans[s.scanId]) continue;
                        gdnProcessedBridgeScans[s.scanId] = true;

                        const result = processScannedDeliveryCode(s.rawCode, true) || {
                            status: 'already_delivered_cancelled',
                            message: 'تمت معالجة المسحة',
                            invoiceNo: s.rawCode
                        };

                        fetch(`api/qr_live_bridge.php?action=ack_scan&channel=${encodeURIComponent(GDN_BRIDGE_CHANNEL)}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                scanId: s.scanId,
                                resultStatus: result.status === 'delivered' ? 'delivered' : 'already_delivered_cancelled',
                                resultMessage: result.message || '',
                                invoiceNo: result.invoiceNo || '',
                                customerName: result.customerName || ''
                            })
                        }).catch(() => {});
                    }
                } catch (e) {}
            }, 550);
        }
        startDirectSalesMobileBridgeListener();

        // Live Mobile Camera QR Scanner Modal
        window.openDeliveryQrScannerModal = async function() {
            const modal = document.getElementById('gdn-qr-scanner-modal');
            if (modal) modal.style.display = 'flex';
            setupMobileBridgePairingInModal();
            startDirectSalesMobileBridgeListener();

            const statusEl = document.getElementById('gdn-camera-status');
            const video = document.getElementById('gdn-qr-video');
            const manualInput = document.getElementById('gdn-manual-qr-input');
            if (manualInput) {
                manualInput.value = '';
                setTimeout(() => manualInput.focus(), 150);
            }

            try {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    qrVideoStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } }
                    });
                    if (video) {
                        video.srcObject = qrVideoStream;
                        await video.play();
                    }
                    if (statusEl) statusEl.textContent = '🟢 الكاميرا في وضع المسح المتتابع — مرّر الـ 10 أذونات تباعاً بدون ضغط أي زر';

                    if ('BarcodeDetector' in window) {
                        const detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });
                        if (qrScanInterval) clearInterval(qrScanInterval);
                        qrScanInterval = setInterval(async () => {
                            try {
                                if (video && video.readyState >= 2) {
                                    const codes = await detector.detect(video);
                                    if (codes && codes.length > 0 && codes[0].rawValue) {
                                        // Continuous stream: keep camera open!
                                        processScannedDeliveryCode(codes[0].rawValue, true);
                                    }
                                }
                            } catch (e) {}
                        }, 300);
                    } else if (statusEl) {
                        statusEl.textContent = '📱 امسح كود ربط الجوال على اليسار أو استخدم قارئ الباركود السريع بالأسفل';
                    }
                }
            } catch (err) {
                if (statusEl) statusEl.textContent = '📱 امسح كود ربط الجوال الظاهر بجانبك لتشغيل كاميرا الجوال فوراً';
            }
        };

        window.closeDeliveryQrScannerModal = function() {
            const modal = document.getElementById('gdn-qr-scanner-modal');
            if (modal) modal.style.display = 'none';
            if (qrScanInterval) {
                clearInterval(qrScanInterval);
                qrScanInterval = null;
            }
            if (qrVideoStream) {
                qrVideoStream.getTracks().forEach(t => t.stop());
                qrVideoStream = null;
            }
        };

        window.handleQrImageFileCapture = async function(inputEl) {
            const file = inputEl?.files?.[0];
            if (!file) return;
            if ('BarcodeDetector' in window) {
                try {
                    const bmp = await createImageBitmap(file);
                    const detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });
                    const codes = await detector.detect(bmp);
                    if (codes && codes.length > 0 && codes[0].rawValue) {
                        processScannedDeliveryCode(codes[0].rawValue, true);
                        return;
                    }
                } catch (e) {}
            }
            if (typeof showToast === 'function') showToast('💡 اكتب رقم الفاتورة الظاهر على الورقة للتنفيذ الفوري', 'info');
        };

        window.processScannedDeliveryCode = function(rawCode, keepScannerOpen = true) {
            if (!rawCode) return null;
            let cleaned = String(rawCode).trim();

            // Extract qr_deliver or inv parameter if full URL was scanned
            try {
                if (cleaned.includes('qr_deliver=')) {
                    const u = new URL(cleaned, window.location.origin);
                    cleaned = u.searchParams.get('qr_deliver') || cleaned;
                } else if (cleaned.includes('inv=')) {
                    const u = new URL(cleaned, window.location.origin);
                    cleaned = u.searchParams.get('inv') || cleaned;
                }
            } catch (e) {}

            cleaned = cleaned.replace(/^#/, '').trim();
            if (!cleaned) return null;

            const keyLower = cleaned.toLowerCase();
            const now = Date.now();
            // 2.2s cooldown per code so moving paper doesn't double-trigger in the same second
            if (gdnQrCooldownMap[keyLower] && (now - gdnQrCooldownMap[keyLower]) < 2200) {
                return null;
            }
            gdnQrCooldownMap[keyLower] = now;

            if (!keepScannerOpen) {
                closeDeliveryQrScannerModal();
            }
            const quickInput = document.getElementById('gdn-quick-scan-input');
            if (quickInput) quickInput.value = '';
            const manualInput = document.getElementById('gdn-manual-qr-input');
            if (manualInput) manualInput.value = '';

            const invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const inv = invoices.find(i =>
                String(i.invoiceNo || i.serial || i.id || '').toLowerCase() === keyLower ||
                String(i.deliveryNo || '').toLowerCase() === keyLower
            );
            if (!inv) {
                gdnBatchCancelledCount++;
                window.playGdnCancelledAlarm();
                const errMsg = `❌ لم يتم العثور على إذن برقم: #${cleaned}`;
                updateGdnContinuousScanUI('cancelled', errMsg);
                if (typeof showToast === 'function') showToast(errMsg, 'error');
                return { status: 'not_found', message: errMsg, invoiceNo: cleaned };
            }

            const invNo = String(inv.invoiceNo || inv.serial || inv.id);
            const custName = resolveCustomerName(inv);
            const autoDeduct = document.getElementById('gdn-qr-auto-deduct-chk')?.checked !== false;

            const isAlreadyDelivered = (
                inv.isDelivered === true ||
                inv.deliveryStatus === 'delivered' ||
                inv.deliveryStatus === 'Delivered_Pending_Admin' ||
                inv.deliveryStatus === 'Admin_Closed' ||
                inv.status === 'delivered' ||
                inv.status === 'Delivered_Pending_Admin' ||
                inv.status === 'Admin_Closed'
            );

            // 2ND SCAN OR ALREADY DELIVERED -> CANCELLED ALERT!
            if (isAlreadyDelivered) {
                gdnBatchCancelledCount++;
                window.playGdnCancelledAlarm();
                const cancelMsg = `🚫 تم تسليم هذا الإذن (#${invNo} — ${custName}) مسبقاً — الإذن ملغي!`;
                updateGdnContinuousScanUI('cancelled', cancelMsg);
                if (typeof showToast === 'function') showToast(cancelMsg, 'error');
                return {
                    status: 'already_delivered_cancelled',
                    message: `تم تسليم هذا الإذن (#${invNo}) مسبقاً — الإذن ملغي!`,
                    invoiceNo: invNo,
                    customerName: custName
                };
            }

            // 1ST SCAN -> INSTANT AUTO-DELIVERY WITHOUT PRESSING ANY BUTTON!
            if (autoDeduct) {
                executeInstantOneClickDelivery(invNo);
                gdnBatchDeliveredCount++;
                window.playGdnSuccessBeep();
                const okMsg = `✅ [${gdnBatchDeliveredCount}] تم استلام وخصم الإذن #${invNo} (${custName}) بالـ QR فوراً — مرّر الإذن التالي...`;
                updateGdnContinuousScanUI('ok', okMsg);
                return {
                    status: 'delivered',
                    message: `تم استلام وتسليم وخصم الإذن #${invNo} (${custName}) فوراً!`,
                    invoiceNo: invNo,
                    customerName: custName
                };
            } else {
                executeDelivery(invNo);
                return {
                    status: 'opened',
                    message: `تم فتح الإذن #${invNo}`,
                    invoiceNo: invNo,
                    customerName: custName
                };
            }
        };

        // Official Printable GDN Gate Pass with QR Code
        window.printOfficialGdnGatePass = function(invNo) {
            const invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const inv = invoices.find(i => String(i.invoiceNo || i.serial || i.id) === String(invNo));
            if (!inv) return;

            const { dateStr, timeStr } = parseInvoiceDateTimeParts(inv);
            const qrTargetUrl = `${window.location.origin}${window.location.pathname}?action=delivery&qr_deliver=${encodeURIComponent(invNo)}&auto_deduct=1`;
            const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=4&data=${encodeURIComponent(qrTargetUrl)}`;

            const custResolved = resolveCustomerName(inv);
            const rowsHtml = (inv.items || []).map((it, idx) => {
                const m = resolveItemMeta(it);
                const q = parseFloat(it.deliveredQty || it.qty || it.quantity || 0) || 0;
                const baseUnitsVal = +(q * m.factor).toFixed(2);
                return `
                <tr>
                    <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:800;">${idx + 1}</td>
                    <td style="padding:8px; border:1px solid #0f172a; font-weight:900;">${m.name}</td>
                    <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:900;">${q} ${m.unit}</td>
                    <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:900;">${m.hasMultiUnits ? `${baseUnitsVal.toLocaleString('en-US')} ${m.baseUnit} <span style="font-size:0.78rem; color:#475569;">(1 ${m.unit} = ${m.factor} ${m.baseUnit})</span>` : `${q} ${m.baseUnit}`}</td>
                </tr>
            `;
            }).join('');

            const w = window.open('', '_blank', 'width=850,height=700');
            if (!w) return;
            w.document.write(`
                <!DOCTYPE html>
                <html dir="rtl" lang="ar">
                <head>
                    <meta charset="UTF-8">
                    <title>إذن صرف وتسليم بضاعة (Gate Pass) - #${invNo}</title>
                    <style>
                        body { font-family: 'Cairo', 'Tajawal', sans-serif; padding: 24px; color: #000; }
                        .header { display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #000; padding-bottom:14px; margin-bottom:18px; }
                        table { width:100%; border-collapse:collapse; margin-top:14px; }
                        th { background:#064e3b; color:#fff; padding:10px; border:1px solid #000; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div>
                            <h2 style="margin:0; font-size:1.5rem;">🚚 إذن صرف وتسليم بضاعة (GDN / Gate Pass)</h2>
                            <p style="margin:6px 0 0; font-weight:800;">رقم الفاتورة / الإذن: <span style="font-family:monospace; direction:ltr; display:inline-block;">#${invNo}</span> &nbsp;|&nbsp; رقم إذن الصرف: ${inv.deliveryNo || 'قيد الصرف'}</p>
                            ${(inv.isSplit || inv.parentInvoice) ? `<p style="margin:4px 0 0; font-weight:900; color:#0f172a;">📌 الإذن المرجعي الأصلي: #${inv.rootInvoice || inv.parentInvoice} &nbsp;|&nbsp; رقم عملية التقسيم: #${inv.splitBatchNo || 1} &nbsp;|&nbsp; القسم: ${inv.splitBranchIndex || 1}/${inv.splitTotalBranches || 2}</p>` : ''}
                            <p style="margin:4px 0 0; font-weight:800;">📅 التاريخ: ${dateStr} &nbsp;&nbsp;&nbsp;&nbsp; 🕒 الوقت: ${timeStr}</p>
                            <p style="margin:4px 0 0; font-weight:800;">👤 العميل: ${custResolved} &nbsp;|&nbsp; المستلم: ${inv.receiverName || custResolved}</p>
                        </div>
                        <div style="text-align:center;">
                            <img src="${qrImgUrl}" style="width:120px; height:120px; border:2px solid #000; border-radius:10px;">
                            <div style="font-size:0.72rem; font-weight:900; margin-top:3px;">امسح بالموبايل للخصم الفوري</div>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr><th>#</th><th>الصنف</th><th>الكمية بالوحدة (الإذن)</th><th>عدد الوحدات الفعلية</th></tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                    <div style="display:flex; justify-content:space-between; margin-top:40px; font-weight:900;">
                        <div>توقيع أمين المخزن: ________________</div>
                        <div>توقيع السائق / المستلم: ________________</div>
                        <div>ختم بوابة الخروج: ________________</div>
                    </div>
                    <script>window.onload = () => { setTimeout(() => window.print(), 350); };<\/script>
                </body>
                </html>
            `);
            w.document.close();
        };

        window.shareGdnOnWhatsApp = function(invNo) {
            const invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const inv = invoices.find(i => String(i.invoiceNo || i.serial || i.id) === String(invNo));
            if (!inv) return;
            const { dateStr, timeStr } = parseInvoiceDateTimeParts(inv);
            const custResolved = resolveCustomerName(inv);
            const itemsList = (inv.items || []).map(i => {
                const m = resolveItemMeta(i);
                const q = parseFloat(i.deliveredQty || i.qty || i.quantity || 0) || 0;
                const eqStr = m.hasMultiUnits ? ` (تعادل ${(q * m.factor).toLocaleString('en-US', { maximumFractionDigits: 2 })} ${m.baseUnit})` : '';
                return `- ${m.name}: ${q} ${m.unit}${eqStr}`;
            }).join('\n');
            const msg = `🚚 *إشعار تسليم وصرف مبيعات*\nرقم الفاتورة: #${invNo}\n📅 التاريخ: ${dateStr}   🕒 الوقت: ${timeStr}\n👤 العميل: ${custResolved}\n📦 الأصناف:\n${itemsList}\n✅ الحالة: ${inv.isDelivered ? 'تم التسليم والصرف من المخزن' : 'جاهز للتسليم بالـ QR'}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
        };

        // 2D Keyboard Navigation & Shortcuts inside Delivery Hub
        document.addEventListener('keydown', function(e) {
            const hubView = document.getElementById('delivery-hub-view');
            if (!hubView || hubView.style.display === 'none') return;

            if (e.key === 'F2') {
                e.preventDefault();
                openDeliveryQrScannerModal();
                return;
            }
            if (e.key === 'F4') {
                e.preventDefault();
                toggleDeliverySunlightMode();
                return;
            }

            const rows = Array.from(document.querySelectorAll('#delivery-transactions-body tr.gdn-row'));
            if (rows.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                window.gdnActiveRowIndex = Math.min(rows.length - 1, window.gdnActiveRowIndex + 1);
                highlightGdnRow(window.gdnActiveRowIndex);
                rows[window.gdnActiveRowIndex]?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                window.gdnActiveRowIndex = Math.max(0, window.gdnActiveRowIndex - 1);
                highlightGdnRow(window.gdnActiveRowIndex);
                rows[window.gdnActiveRowIndex]?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });

        // Acoustic & Haptic Feedback for Outdoor Storekeeper
        window.playGdnSuccessBeep = function() {
            try {
                if (navigator.vibrate) navigator.vibrate([120, 50, 140]);
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.setValueAtTime(1320, ctx.currentTime + 0.09);
                gain.gain.setValueAtTime(0.22, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.26);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.26);
            } catch (e) {}
        };

        // Hook sound into confirmExecuteDeliveryAction
        const _baseConfirmDel = window.confirmExecuteDeliveryAction;
        window.confirmWorldClassDeliveryAction = async function() {
            await _baseConfirmDel.apply(this, arguments);
            if (window._hubSalesCache) window._hubSalesCache.raw = null;
            if (typeof loadHubTransactions === 'function') loadHubTransactions();
            window.playGdnSuccessBeep();
        };

        // Guarantee World-Class functions override deferred js/direct-sales-v3.js
        function bindWorldClassGdnOverrides() {
            window.loadDeliveryTransactions = function() {
                if (window._hubSalesCache) window._hubSalesCache.raw = null;
                if (typeof loadHubTransactions === 'function') loadHubTransactions();
                if (typeof window.renderWorldClassDeliveryHub === 'function') window.renderWorldClassDeliveryHub();
            };
            window.filterDeliveryTransactions = window.loadDeliveryTransactions;
            window.executeDelivery = window.openWorldClassDeliveryModal;
            window.confirmExecuteDeliveryAction = window.confirmWorldClassDeliveryAction;
            window.openDeliveryHub = function() {
                upgradeDeliveryHubDOM();
                if (typeof window.setUnifiedHubFilter === 'function') {
                    window.setUnifiedHubFilter('pending', false);
                }
            };
        }

        bindWorldClassGdnOverrides();
        document.addEventListener('DOMContentLoaded', bindWorldClassGdnOverrides);
        window.addEventListener('load', bindWorldClassGdnOverrides);
        document.addEventListener('DBReady', () => setTimeout(bindWorldClassGdnOverrides, 60));

        // Auto-Route ?action=delivery & ?qr_deliver=... on page load
        function checkDeliveryUrlRouting() {
            bindWorldClassGdnOverrides();
            const params = new URLSearchParams(window.location.search);
            const action = (params.get('action') || '').toLowerCase();
            const qrDeliver = params.get('qr_deliver');

            if (action === 'delivery' || action === 'gdn' || qrDeliver) {
                window.openDeliveryHub();
                setTimeout(() => {
                    bindWorldClassGdnOverrides();
                    window.openDeliveryHub();
                    if (qrDeliver) {
                        processScannedDeliveryCode(qrDeliver);
                    }
                }, 250);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkDeliveryUrlRouting);
        } else {
            checkDeliveryUrlRouting();
        }

        // Undo / Reverse Delivery & Restore Warehouse Stock
        window.undoGdnDelivery = function(invNo) {
            if (!confirm(`هل أنت متأكد من إلغاء تسليم الفاتورة #${invNo} وإعادة الكميات المصروفة إلى رصيد المخزن؟`)) return;
            let invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const idx = invoices.findIndex(i => String(i.invoiceNo || i.serial || i.id) === String(invNo));
            if (idx === -1) return;

            const inv = invoices[idx];
            const whId = inv.deliveredWarehouse || inv.warehouseId || inv.warehouse || 1;
            let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
            let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

            (inv.items || []).forEach(it => {
                const retQty = parseFloat(it.deliveredQty || it.qty || it.quantity || 0);
                it.deliveredQty = 0;
                const nameClean = String(it.name || it.itemName || '').trim();

                let invEntry = inventory.find(x =>
                    (String(x.id) === String(it.itemId || it.id) || String(x.name || '').trim() === nameClean) &&
                    String(x.warehouseId || 1) === String(whId)
                );
                if (invEntry) invEntry.qty = (parseFloat(invEntry.qty || 0) + retQty);

                const mIdx = erpItems.findIndex(x =>
                    String(x.id) === String(it.itemId || it.id) || String(x.name || '').trim() === nameClean
                );
                if (mIdx !== -1) {
                    if (!erpItems[mIdx].stock) erpItems[mIdx].stock = {};
                    if (!erpItems[mIdx].actualStock) erpItems[mIdx].actualStock = {};
                    erpItems[mIdx].stock[whId] = (parseFloat(erpItems[mIdx].stock[whId]) || 0) + retQty;
                    erpItems[mIdx].actualStock[whId] = (parseFloat(erpItems[mIdx].actualStock[whId]) || 0) + retQty;
                    if (erpItems[mIdx].quantity !== undefined) {
                        erpItems[mIdx].quantity = (parseFloat(erpItems[mIdx].quantity) || 0) + retQty;
                    }
                }
            });

            inv.isDelivered = false;
            inv.deliveryStatus = 'pending';
            inv.status = 'pending_delivery';
            invoices[idx] = inv;

            localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(invoices));
            localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
            localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

            let delNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
            delNotes = delNotes.filter(n => String(n.invoiceNo || n.relatedInvoice) !== String(invNo));
            localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(delNotes));

            if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
            if (window._hubSalesCache) window._hubSalesCache.raw = null;
            if (typeof loadHubTransactions === 'function') loadHubTransactions();
            window.loadDeliveryTransactions();
            if (typeof showToast === 'function') showToast(`↩️ تم إلغاء صرف الفاتورة #${invNo} وإعادة الكميات للمخزن بنجاح`, 'info');
        };

        window.addEventListener('load', checkDeliveryUrlRouting);
    })();