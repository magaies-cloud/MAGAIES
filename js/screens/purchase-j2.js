    // ══════════════════════════════════════════════════════════════════════════
    //  INTERACTIVE DATABASE-PERSISTENT GRID CUSTOMIZER SYSTEM (purchase_grid)
    // ══════════════════════════════════════════════════════════════════════════

    const GRID_ID = 'purchase_grid';

    const DEFAULT_GRID_CONFIG = {
        "grid_id": "purchase_grid",
        "fontSize": 16,
        "fontWeight": "normal",
        "fontFamily": "'Cairo', sans-serif",
        "fontColor": "#ffffff",
        "align": "center",
        "decimalPlaces": 2,
        "thousandSep": ",",
        "columns": [
            {
                "id": "col_index",
                "name": "#",
                "visible": true,
                "width": "45px",
                "align": "center",
                "color": "#94a3b8",
                "fontSize": 15
            },
            {
                "id": "available",
                "name": "المتاح",
                "visible": true,
                "width": "90px",
                "align": "center",
                "color": "#38bdf8",
                "fontSize": 20,
                "decimals": 2
            },
            {
                "id": "warehouse",
                "name": "المخزن",
                "visible": true,
                "width": "130px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 22,
                "fontWeight": "800"
            },
            {
                "id": "item",
                "name": "الصنف",
                "visible": true,
                "width": "324px",
                "align": "right",
                "color": "#ffdc7a",
                "fontSize": 26,
                "fontWeight": "800"
            },
            {
                "id": "unit",
                "name": "الوحدة",
                "visible": true,
                "width": "107px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 20,
                "fontWeight": "bold"
            },
            {
                "id": "quantity",
                "name": "الكمية",
                "visible": true,
                "width": "121px",
                "align": "center",
                "color": "#facc15",
                "fontSize": 36,
                "fontWeight": "800",
                "decimals": 2
            },
            {
                "id": "price",
                "name": "السعر",
                "visible": true,
                "width": "206px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 36,
                "fontWeight": "800",
                "decimals": 0,
                "thousandSep": "default"
            },
            {
                "id": "total",
                "name": "الجملة",
                "visible": true,
                "width": "240px",
                "align": "center",
                "color": "#fe9095",
                "fontSize": 36,
                "fontWeight": "800",
                "decimals": 0,
                "thousandSep": ","
            },
            {
                "id": "action",
                "name": "إجراء",
                "visible": true,
                "width": "90px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 14
            }
        ]
    };

    let tempGridConfig = null;
    let selectedColIndex = 0;

    function renderModalColumnsList() {
        const listEl = document.getElementById('col-settings-list');
        listEl.innerHTML = '';
        
        tempGridConfig.columns.forEach((col, index) => {
            const item = document.createElement('div');
            const isSelected = (index === selectedColIndex);
            
            item.style.cssText = `
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                padding: 9px 12px;
                border-radius: 10px;
                cursor: pointer;
                transition: all 0.2s;
                background: ${isSelected ? 'rgba(56, 189, 248, 0.18)' : 'rgba(255,255,255,0.03)'};
                border: ${isSelected ? '2px solid #38bdf8' : '1px solid rgba(255,255,255,0.07)'};
                box-shadow: ${isSelected ? '0 0 12px rgba(56,189,248,0.3)' : 'none'};
            `;
            
            item.onclick = (e) => {
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') return;
                selectedColIndex = index;
                renderModalColumnsList();
                populateSelectedColPanel();
            };

            const isLightNow = document.documentElement.classList.contains('light-theme');
            const colColor = col.color || tempGridConfig.fontColor || '#ffffff';
            const displayColColor = (isLightNow && ['#ffffff', '#fff'].includes(String(colColor).toLowerCase())) ? '#000000' : colColor;
            const colSize = col.fontSize || tempGridConfig.fontSize || 16;
            const colWidth = col.width || '100px';
            const btnBg = isLightNow ? '#dfd2bc' : 'rgba(255,255,255,0.1)';
            const btnClr = isLightNow ? '#000000' : '#ffffff';
            const btnBrd = isLightNow ? '1.5px solid #78350f' : 'none';

            item.innerHTML = `
                <!-- Move ▲ ▼ & Checkbox & Name -->
                <div style="display:flex; align-items:center; gap:8px; flex:1;">
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <button type="button" class="col-move-arrow-btn" onclick="moveModalColumn(${index}, 'up'); event.stopPropagation();" style="background:${btnBg}; border:${btnBrd}; border-radius:4px; width:24px; height:19px; cursor:pointer; color:${btnClr}; -webkit-text-fill-color:${btnClr}; font-weight:900; font-size:0.72rem; display:flex; align-items:center; justify-content:center; ${index === 0 ? 'opacity:0.25;' : ''}" ${index === 0 ? 'disabled' : ''}>▲</button>
                        <button type="button" class="col-move-arrow-btn" onclick="moveModalColumn(${index}, 'down'); event.stopPropagation();" style="background:${btnBg}; border:${btnBrd}; border-radius:4px; width:24px; height:19px; cursor:pointer; color:${btnClr}; -webkit-text-fill-color:${btnClr}; font-weight:900; font-size:0.72rem; display:flex; align-items:center; justify-content:center; ${index === tempGridConfig.columns.length - 1 ? 'opacity:0.25;' : ''}" ${index === tempGridConfig.columns.length - 1 ? 'disabled' : ''}>▼</button>
                    </div>
                    
                    <input type="checkbox" id="modal-col-chk-${col.id}" ${col.visible ? 'checked' : ''} onchange="toggleModalColVis(${index}, this.checked); event.stopPropagation();" style="cursor:pointer; width:17px; height:17px; accent-color:#10b981;">
                    <div style="display:flex; flex-direction:column;">
                        <span class="col-custom-color-preview" style="font-weight:900; font-size:0.95rem; color:${displayColColor}; -webkit-text-fill-color:${displayColColor};">${col.name}</span>
                        <span style="font-size:0.75rem; color:${isLightNow ? '#334155' : '#94a3b8'};">${colWidth} | ${colSize}px</span>
                    </div>
                </div>

                <!-- Active Indicator Badge -->
                <div style="display:flex; align-items:center; gap:6px;">
                    <div style="width:14px; height:14px; border-radius:50%; background:${colColor}; border:1.5px solid ${isLightNow ? '#000' : 'rgba(255,255,255,0.4)'};" title="لون الخط"></div>
                    ${isSelected ? '<span style="font-size:0.75rem; color:#38bdf8; font-weight:bold; background:rgba(56,189,248,0.2); padding:2px 6px; border-radius:4px;">👈 محدد</span>' : ''}
                </div>
            `;
            listEl.appendChild(item);
        });
    }

    function populateSelectedColPanel() {
        const col = tempGridConfig.columns[selectedColIndex];
        if (!col) return;

        document.getElementById('lbl-selected-col-name').textContent = col.name;
        document.getElementById('badge-selected-col-id').textContent = col.id;
        document.getElementById('sel-col-name').value = col.name || '';
        
        const fSize = col.fontSize || tempGridConfig.fontSize || 16;
        document.getElementById('sel-col-fontsize').value = fSize;
        document.getElementById('sel-col-fontsize-range').value = fSize;
        
        document.getElementById('sel-col-fontweight').value = col.fontWeight || '';
        
        const fColor = col.color || tempGridConfig.fontColor || '#ffffff';
        document.getElementById('sel-col-color').value = fColor;
        document.getElementById('sel-col-color-text').value = fColor;
        
        document.getElementById('sel-col-align').value = col.align || tempGridConfig.align || 'center';
        
        const widthVal = parseInt(col.width) || (col.id === 'item' || col.id === 'col_item' ? 260 : 100);
        document.getElementById('sel-col-width').value = widthVal;
        document.getElementById('sel-col-width-range').value = widthVal;
        
        document.getElementById('sel-col-decimals').value = col.decimals !== undefined ? col.decimals : '';
        document.getElementById('sel-col-thousandsep').value = col.thousandSep || 'default';
    }

    window.onSelectedColPropChange = function(prop, val) {
        const col = tempGridConfig.columns[selectedColIndex];
        if (!col) return;

        col[prop] = val;
        if (prop === 'color') col.customColor = true;
        renderModalColumnsList();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        if (typeof applyGridSettings === 'function') applyGridSettings();
        if (prop === 'decimals' || prop === 'thousandSep' || prop === 'name') {
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        }
    };

    window.applySelectedColStyleToAll = function() {
        const sourceCol = tempGridConfig.columns[selectedColIndex];
        if (!sourceCol) return;

        tempGridConfig.columns.forEach(col => {
            if (sourceCol.fontSize) col.fontSize = sourceCol.fontSize;
            if (sourceCol.fontWeight) col.fontWeight = sourceCol.fontWeight;
            if (sourceCol.color) { col.color = sourceCol.color; col.customColor = true; }
            if (sourceCol.align) col.align = sourceCol.align;
        });

        renderModalColumnsList();
        populateSelectedColPanel();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        if (typeof applyGridSettings === 'function') applyGridSettings();
        showToast("⚡ تم نسخ حجم وسُمك ولون ومحاذاة العمود المحدد إلى كافة الأعمدة بنجاح", "info", 3000);
    };

    window.toggleModalColVis = function(index, checked) {
        tempGridConfig.columns[index].visible = checked;
        renderModalColumnsList();
    };

    window.moveModalColumn = function(index, direction) {
        const list = tempGridConfig.columns;
        if (direction === 'up' && index > 0) {
            const temp = list[index];
            list[index] = list[index - 1];
            list[index - 1] = temp;
            if (selectedColIndex === index) selectedColIndex = index - 1;
            else if (selectedColIndex === index - 1) selectedColIndex = index;
        } else if (direction === 'down' && index < list.length - 1) {
            const temp = list[index];
            list[index] = list[index + 1];
            list[index + 1] = temp;
            if (selectedColIndex === index) selectedColIndex = index + 1;
            else if (selectedColIndex === index + 1) selectedColIndex = index;
        }
        renderModalColumnsList();
        populateSelectedColPanel();
    };

    function readModalGeneralSettings() {
        const col = tempGridConfig && tempGridConfig.columns ? tempGridConfig.columns[selectedColIndex] : null;
        if (col) {
            const nmEl = document.getElementById('sel-col-name');
            const fsEl = document.getElementById('sel-col-fontsize');
            const fwEl = document.getElementById('sel-col-fontweight');
            const clTxtEl = document.getElementById('sel-col-color-text');
            const clEl = document.getElementById('sel-col-color');
            const alEl = document.getElementById('sel-col-align');
            const wdEl = document.getElementById('sel-col-width');
            const dcEl = document.getElementById('sel-col-decimals');
            const tsEl = document.getElementById('sel-col-thousandsep');
            if (nmEl && nmEl.value.trim()) col.name = nmEl.value.trim();
            if (fsEl && parseInt(fsEl.value)) col.fontSize = parseInt(fsEl.value);
            if (fwEl) col.fontWeight = fwEl.value;
            const pickedClr = (clTxtEl && clTxtEl.value.trim()) ? clTxtEl.value.trim() : (clEl ? clEl.value : '');
            if (pickedClr) {
                if (col.color !== pickedClr) col.customColor = true;
                col.color = pickedClr;
            }
            if (alEl && alEl.value) col.align = alEl.value;
            if (wdEl && parseInt(wdEl.value)) col.width = parseInt(wdEl.value) + 'px';
            if (dcEl) col.decimals = dcEl.value !== '' ? parseInt(dcEl.value) : '';
            if (tsEl && tsEl.value) col.thousandSep = tsEl.value;
        }
        tempGridConfig.grid_id = 'purchase_grid';
        tempGridConfig.fontFamily = document.getElementById('grid-setting-font-family').value;
        tempGridConfig.fontSize = parseInt(document.getElementById('grid-setting-font-size').value) || 16;
        tempGridConfig.fontWeight = document.getElementById('grid-setting-font-weight').value;
        tempGridConfig.fontColor = document.getElementById('grid-setting-font-color').value;
        tempGridConfig.align = document.getElementById('grid-setting-align').value;
        tempGridConfig.decimalPlaces = parseInt(document.getElementById('grid-setting-decimals').value);
        tempGridConfig.thousandSep = document.getElementById('grid-setting-thousand-sep').value;
    }

    window.previewGridSettings = function() {
        readModalGeneralSettings();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        try { localStorage.setItem('erp_grid_config_purchase_grid', JSON.stringify(window.CurrentGridConfig)); } catch (_e) {}
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        showToast("👁️ تم تطبيق التنسيق للمعاينة المباشرة على الجدول", "info", 2000);
    };

    window.saveAndApplyGridSettingsToDB = async function() {
        readModalGeneralSettings();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        window.CurrentGridConfig.grid_id = 'purchase_grid';
        try { localStorage.setItem('erp_grid_config_purchase_grid', JSON.stringify(window.CurrentGridConfig)); } catch (_e) {}
        
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        
        closeGridSettingsModal();
        await saveGridConfigToDB(window.CurrentGridConfig);
        if (typeof showToast === 'function') showToast("✅ تم حفظ وتطبيق تخصيص الجدول نهائياً بنجاح", "success", 2500);
    };

    window.loadGridConfigFromDB = async function(forceRemote) {
        try {
            if (!forceRemote) {
                const cachedCfg = localStorage.getItem('erp_grid_config_purchase_grid');
                if (cachedCfg) {
                    const parsed = JSON.parse(cachedCfg);
                    if (parsed && Array.isArray(parsed.columns)) {
                        window.CurrentGridConfig = parsed;
                        if (typeof applyGridSettings === 'function') applyGridSettings();
                        if (typeof renderGrid === 'function') renderGrid();
                        if (typeof recalcAll === 'function') recalcAll();
                        return;
                    }
                }
            }
            const res = await fetch('api/get_grid_config.php?grid_id=purchase_grid');
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
                try { localStorage.setItem('erp_grid_config_purchase_grid', JSON.stringify(data.config)); } catch (_e) {}
            } else {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
            }
            if (typeof applyGridSettings === 'function') applyGridSettings();
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        } catch (e) {
            if (!window.CurrentGridConfig) {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
                if (typeof applyGridSettings === 'function') applyGridSettings();
            }
        }
    };
    // Smart Auto-Fit Engine for Header Inputs (Supplier, Exchange Rate, Warehouse, Cost Center, Invoice No)
    let _headerMeasureCanvas = null;
    function measureTextWidthPx(text, fontStr) {
        try {
            if (!_headerMeasureCanvas) _headerMeasureCanvas = document.createElement('canvas');
            const ctx = _headerMeasureCanvas.getContext('2d');
            ctx.font = fontStr;
            return ctx.measureText(text || '').width;
        } catch (e) {
            return (text ? text.length : 0) * 10;
        }
    }

    window.autoFitHeaderFields = function() {
        try {
            // 1. Invoice Number (#inv-number) — Exact Unified Font Matching Date (#hdr-date)
            const invEl = document.getElementById('inv-number');
            const dtEl = document.getElementById('hdr-date');
            if (invEl) {
                const dtSize = dtEl ? (window.getComputedStyle(dtEl).fontSize || '15.5px') : '15.5px';
                invEl.style.setProperty('font-size', dtSize, 'important');
            }

            // 2. Supplier Input (#hdr-customer-search) - Auto-fit based on supplier name length & box width
            const supInput = document.getElementById('hdr-customer-search');
            if (supInput) {
                const val = (supInput.value || '').trim();
                const availW = Math.max(120, (supInput.clientWidth || 260) - 72);
                let px = 19.5;
                if (val) {
                    while (px > 12.5 && measureTextWidthPx(val, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                supInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 3. Exchange Rate (#hdr-exchange-rate) - Prominent bold number fitted to compact box
            const exInput = document.getElementById('hdr-exchange-rate');
            if (exInput) {
                const val = (exInput.value || '').trim();
                const availW = Math.max(54, (exInput.clientWidth || 82) - 10);
                let px = 19.5;
                if (val) {
                    while (px > 13.5 && measureTextWidthPx(val, `900 ${px}px 'Outfit', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                exInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 4. Warehouse (#hdr-warehouse) & Cost Center (#hdr-cost-center) selects
            ['hdr-warehouse', 'hdr-cost-center'].forEach(selId => {
                const sel = document.getElementById(selId);
                if (!sel) return;
                const optText = (sel.options && sel.selectedIndex >= 0) ? (sel.options[sel.selectedIndex].text || '').trim() : '';
                const availW = Math.max(85, (sel.clientWidth || 175) - 32);
                let px = 16.5;
                if (optText) {
                    while (px > 12 && measureTextWidthPx(optText, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                sel.style.setProperty('font-size', px + 'px', 'important');
            });

            // 4b. Auto-fit Financial Strip (#customer-financial-card .fin-strip-inner) so all metrics (الرصيد، المديونية، شيكات، الحد، المتاح) are 100% visible
            const finInner = document.querySelector('#customer-financial-card .fin-strip-inner');
            if (finInner && finInner.clientWidth > 100) {
                const lbls = finInner.querySelectorAll('.fin-lbl');
                const vals = finInner.querySelectorAll('.fin-val');
                let valPx = 13.5;
                let lblPx = 11.8;
                let gapPx = 6;
                finInner.style.setProperty('gap', gapPx + 'px', 'important');
                finInner.style.setProperty('padding', '0 8px', 'important');
                lbls.forEach(el => el.style.setProperty('font-size', lblPx + 'px', 'important'));
                vals.forEach(el => el.style.setProperty('font-size', valPx + 'px', 'important'));
                while (finInner.scrollWidth > finInner.clientWidth && valPx > 9.5) {
                    valPx -= 0.5;
                    lblPx = Math.max(9.0, lblPx - 0.4);
                    gapPx = Math.max(3, gapPx - 0.5);
                    finInner.style.setProperty('gap', gapPx + 'px', 'important');
                    lbls.forEach(el => el.style.setProperty('font-size', lblPx + 'px', 'important'));
                    vals.forEach(el => el.style.setProperty('font-size', valPx + 'px', 'important'));
                }
            }

            // 5. Auto-Fit Bottom Action Buttons (Scales text up & down automatically with button width/height)
            if (typeof window.autoFitPurchaseBottomButtons === 'function') {
                window.autoFitPurchaseBottomButtons();
            }
        } catch (e) {}
    };

    window.autoFitPurchaseBottomButtons = function() {
        try {
            const buttons = document.querySelectorAll('#purchase-editor-view .items-toolbar .toolbar-actions button.btn-action, #purchase-editor-view #btn-toggle-auto-send-purchase');
            buttons.forEach(btn => {
                const btnW = btn.clientWidth;
                if (!btnW || btnW < 30) return;

                const labelSpan = btn.querySelector('.btn-fit-label') || btn.querySelector('span:not([id*="icon"]):not([id*="pill"])');
                const text = (labelSpan ? labelSpan.textContent : btn.textContent || '').trim();
                if (!text) return;

                // Calculate fixed width taken by icons, kbd badges, pills, gaps & horizontal padding
                let fixedExtra = 20; // left + right padding (~18px) + safety margin
                btn.querySelectorAll('svg').forEach(svg => {
                    fixedExtra += (svg.classList.contains('share-chevron-svg') ? 12 : 16) + 5;
                });
                btn.querySelectorAll('kbd').forEach(k => {
                    fixedExtra += (k.offsetWidth || 24) + 5;
                });
                if (btn.id === 'btn-toggle-auto-send-purchase') {
                    const pill = document.getElementById('auto-send-purchase-status-pill');
                    const ico = document.getElementById('auto-send-purchase-icon');
                    fixedExtra += (pill ? (pill.offsetWidth || 48) : 48) + (ico ? (ico.offsetWidth || 16) : 16) + 10;
                } else {
                    btn.querySelectorAll('span').forEach(sp => {
                        if (sp !== labelSpan && sp.textContent.trim().length <= 3) {
                            fixedExtra += (sp.offsetWidth || 16) + 5;
                        }
                    });
                }

                const availTextW = Math.max(24, btnW - fixedExtra);
                // Scale target max font-size proportionally with button width (grows when button is wide, shrinks when narrow)
                let px = Math.min(16.5, Math.max(11, btnW * 0.135));
                while (px > 10.5 && measureTextWidthPx(text, `900 ${px}px 'Tajawal', 'Cairo', sans-serif`) > availTextW) {
                    px -= 0.5;
                }
                btn.style.setProperty('font-size', px + 'px', 'important');
                if (labelSpan) {
                    labelSpan.style.setProperty('font-size', px + 'px', 'important');
                }
            });
        } catch (_e) {}
    };

    if (typeof ResizeObserver !== 'undefined') {
        const _purBtnResizeObs = new ResizeObserver(() => {
            if (typeof window.autoFitPurchaseBottomButtons === 'function') {
                window.autoFitPurchaseBottomButtons();
            }
        });
        window.addEventListener('DOMContentLoaded', () => {
            const tb = document.querySelector('#purchase-editor-view .items-toolbar .toolbar-actions');
            if (tb) _purBtnResizeObs.observe(tb);
            document.querySelectorAll('#purchase-editor-view .items-toolbar .toolbar-actions button').forEach(b => _purBtnResizeObs.observe(b));
            setTimeout(() => { if (window.autoFitPurchaseBottomButtons) window.autoFitPurchaseBottomButtons(); }, 80);
        });
    }

    // Smart Auto-Fit Engine for Grid Cells (Scales with Column Font Size + Auto-Shrinks Long Item/Warehouse Names)
    window.autoFitGridCells = function() {
        try {
            const cfg = window.CurrentGridConfig;
            const table = document.getElementById('pos-grid');
            if (!cfg || !cfg.columns || !table) return;
            const fontFam = cfg.fontFamily || "'Cairo', sans-serif";

            cfg.columns.forEach(col => {
                if (col.visible === false || col.visible === 'false' || col.visible === 0) return;
                const isNumCol = ['quantity', 'price', 'available', 'total', 'shila', 'shilaRem', 'fcPrice', 'totalFc'].includes(String(col.id));
                const rawBase = parseFloat(col.fontSize) || parseFloat(cfg.fontSize) || 18;
                const basePx = rawBase;
                const weight = col.fontWeight || cfg.fontWeight || '800';

                // 1. Fit Column Header (th) to basePx (scale down slightly only if column is too narrow for header word)
                const th = table.querySelector(`thead th[data-col-id="${col.id}"]`);
                if (th && col.id !== 'action') {
                    const thText = (th.textContent || col.name || '').trim();
                    const thAvailW = Math.max(36, (th.clientWidth || parseInt(col.width) || 100) - 18);
                    let thPx = Math.min(rawBase, 26);
                    if (thText && thAvailW > 25) {
                        while (thPx > 12 && measureTextWidthPx(thText, `800 ${thPx}px ${fontFam}`) > thAvailW) {
                            thPx -= 0.5;
                        }
                    }
                    th.style.setProperty('font-size', thPx + 'px', 'important');
                }

                // 2. Fit Cells (td inputs, selects, spans) to basePx & auto-fit long names to column width
                const tds = table.querySelectorAll(`tbody td[data-col-id="${col.id}"]`);
                tds.forEach(td => {
                    const ctrl = td.querySelector('input.g-input, select.g-select, .avail-val, .total-val, .row-num');
                    if (!ctrl) return;
                    let textVal = '';
                    if (ctrl.tagName === 'SELECT') {
                        textVal = (ctrl.options && ctrl.selectedIndex >= 0) ? (ctrl.options[ctrl.selectedIndex].text || '').trim() : '';
                    } else if (ctrl.tagName === 'INPUT') {
                        textVal = (ctrl.value || '').trim();
                    } else {
                        textVal = (ctrl.textContent || '').trim();
                    }

                    const tdW = ctrl.clientWidth || td.clientWidth || parseInt(col.width) || 140;
                    const paddingOffset = (col.id === 'item') ? 54 : (ctrl.tagName === 'SELECT' ? 28 : (col.id === 'price' ? 38 : (isNumCol ? 16 : 18)));
                    const availW = Math.max(32, tdW - paddingOffset);

                    let targetPx = basePx;
                    if (textVal && availW > 20) {
                        while (targetPx > 7.5 && (measureTextWidthPx(textVal, `${weight} ${targetPx}px 'Outfit', ${fontFam}`) * 1.12) > availW) {
                            targetPx -= 0.5;
                        }
                    }
                    ctrl.style.setProperty('font-size', targetPx + 'px', 'important');
                });
            });
            ['pay-credit-amount'].forEach(id => {
                const el = document.getElementById(id);
                if (el && typeof window.autoFitSingleInput === 'function') window.autoFitSingleInput(el);
            });
        } catch (e) {}
    };

    window.autoFitSingleInput = function(el, maxPx, minPx) {
        try {
            if (!el) return;
            const val = (el.value !== undefined ? String(el.value) : (el.textContent || '')).trim();
            const isPaymentBig = (el.id === 'pay-cash-amount');
            const isCreditAmt = (el.id === 'pay-credit-amount');
            let base = maxPx || (isPaymentBig ? 32 : (isCreditAmt ? 25 : 24.5));
            let colId = '';
            if (el.classList && el.classList.contains('g-input')) {
                const td = el.closest('td[data-col-id]');
                colId = td ? td.getAttribute('data-col-id') : '';
                const cfg = window.CurrentGridConfig;
                const colCfg = (cfg && cfg.columns) ? cfg.columns.find(c => String(c.id) === String(colId)) : null;
                const rawBase = parseFloat(colCfg?.fontSize) || parseFloat(cfg?.fontSize) || 18;
                base = rawBase;
            }
            const floorPx = minPx || 7.5;
            const boxW = el.clientWidth || (el.parentElement ? el.parentElement.clientWidth : 150) || 150;
            const padDeduct = (colId === 'price') ? 38 : 16;
            const availW = Math.max(32, boxW - padDeduct);
            let px = base;
            if (val && availW > 20) {
                while (px > floorPx && (measureTextWidthPx(val, `900 ${px}px 'Outfit', 'Cairo', 'Tajawal', sans-serif`) * 1.12) > availW) {
                    px -= 0.5;
                }
            }
            el.style.setProperty('font-size', px + 'px', 'important');
        } catch (_e) {}
    };

    window.applyGridSettings = function() {
        let cfg = window.CurrentGridConfig;
        if (!cfg || !cfg.columns) return;
        
        // Clear server-side pre-rendered style tag so live changes take immediate priority
        const ssrStyle = document.getElementById('dynamic-pos-grid-style');
        if (ssrStyle) ssrStyle.innerHTML = '';

        let style = document.getElementById('dynamic-grid-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-grid-style';
        }
        (document.body || document.head).appendChild(style);
        
        const globalFont = cfg.fontFamily || "'Cairo', sans-serif";
        const globalSize = (cfg.fontSize || 16) + 'px';
        const globalWeight = cfg.fontWeight || 'normal';
        const globalColorRaw = cfg.fontColor || '#ffffff';
        const globalColor = (globalColorRaw.toLowerCase() === '#ffffff' || globalColorRaw.toLowerCase() === '#fff') ? 'var(--text-primary, #ffffff)' : globalColorRaw;
        const globalAlign = cfg.align || 'center';

        let css = `
            body #pos-grid,
            body #pos-grid thead th,
            body #pos-grid tbody td,
            body #pos-grid tbody td input,
            body #pos-grid tbody td select,
            body #pos-grid tbody td span {
                font-family: ${globalFont} !important;
            }
            body #pos-grid {
                font-size: ${globalSize} !important;
                font-weight: ${globalWeight} !important;
                color: ${globalColor} !important;
            }
        `;
        
        const visCols = cfg.columns.filter(c => !(c.visible === false || c.visible === "false" || c.visible === 0));
        const colPxMap = {};
        let totalPxW = 0;
        visCols.forEach(c => {
            const raw = (c.width && c.width !== 'auto') ? parseFloat(String(c.width).replace(/[^\d.]/g, '')) : 0;
            const px = (raw > 20) ? raw : ((c.id === 'item' || c.id === 'col_item') ? 260 : ((c.id === 'index' || c.id === 'actions' || c.id === 'col_actions') ? 54 : 95));
            colPxMap[c.id] = px;
            totalPxW += px;
        });
        if (totalPxW <= 0) totalPxW = 1000;

        cfg.columns.forEach(col => {
            if (col.visible === false || col.visible === "false" || col.visible === 0) {
                css += `
                    body #pos-grid th[data-col-id="${col.id}"],
                    body #pos-grid td[data-col-id="${col.id}"] {
                        display: none !important;
                    }
                `;
            } else {
                let pctW = ((colPxMap[col.id] || 95) / totalPxW * 100).toFixed(2) + '%';
                let widthCss = `width: ${pctW} !important; min-width: 0 !important; max-width: ${pctW} !important;`;
                let align = (col.id === 'item' || col.id === 'col_item') ? 'center' : (col.align ? col.align : globalAlign);
                let fontSize = col.fontSize ? (String(col.fontSize).replace('px','') + 'px') : globalSize;
                let colColorRaw = String(col.color ? col.color : globalColorRaw).trim();
                let colColorLc = colColorRaw.toLowerCase();
                let isDefDarkClr = ['#ffffff', '#fff', '#ffdc7a', '#facc15', '#fe9095', '#38bdf8', '#94a3b8'].includes(colColorLc) && !col.customColor;
                let darkColor = (colColorLc === '#ffffff' || colColorLc === '#fff') ? '#ffffff' : colColorRaw;
                let lightColor = isDefDarkClr ? '#120500' : ((colColorLc === '#ffffff' || colColorLc === '#fff') ? '#120500' : colColorRaw);
                let fontWeight = col.fontWeight ? col.fontWeight : globalWeight;
                let flexJustify = (align === 'center') ? 'center' : ((align === 'right') ? 'flex-start' : 'flex-end');
                
                css += `
                    html body #purchase-editor-view #pos-grid#pos-grid thead tr th[data-col-id="${col.id}"] {
                        display: table-cell !important; ${widthCss} text-align: ${align} !important;
                        color: ${darkColor} !important; -webkit-text-fill-color: ${darkColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight === 'normal' ? '800' : fontWeight} !important;
                    }
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] {
                        display: table-cell !important; ${widthCss} text-align: ${align} !important;
                        color: ${darkColor} !important; -webkit-text-fill-color: ${darkColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight} !important;
                    }
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input.is-zero-val:not(:focus),
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:hover,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:focus,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select.is-default-hint:not(:focus),
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:hover,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:focus,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val.is-zero-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val.is-zero-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .row-num {
                        text-align: ${align} !important; text-align-last: ${align} !important; justify-content: ${flexJustify} !important;
                        color: ${darkColor} !important; -webkit-text-fill-color: ${darkColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight} !important;
                    }
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid thead tr th[data-col-id="${col.id}"] {
                        color: ${lightColor} !important; -webkit-text-fill-color: ${lightColor} !important;
                        font-weight: ${fontWeight === 'normal' ? '900' : fontWeight} !important; text-align: ${align} !important;
                    }
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"],
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input.is-zero-val:not(:focus),
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:hover,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:focus,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select.is-default-hint:not(:focus),
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:hover,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:focus,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val.is-zero-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val.is-zero-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .row-num {
                        color: ${lightColor} !important; -webkit-text-fill-color: ${lightColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight === 'normal' ? '400' : fontWeight} !important;
                        text-align: ${align} !important; text-align-last: ${align} !important; justify-content: ${flexJustify} !important;
                    }
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"]:not([data-col-id="item"]):not([data-col-id="action"]) > div {
                        display: flex; justify-content: ${flexJustify} !important; align-items: center;
                    }
                `;
            }
        });
        
        style.innerHTML = css;
        if (document.body) document.body.appendChild(style);
        if (window.syncInvoiceFaintHintsAndZeros) window.syncInvoiceFaintHintsAndZeros(document);

        // DOM Reordering & Header Name Synchronization
        const table = document.getElementById('pos-grid');
        if (!table) return;
        cfg.columns.forEach(col => {
            const fSz = col.fontSize ? (String(col.fontSize).replace('px','') + 'px') : globalSize;
            const fWt = col.fontWeight ? col.fontWeight : globalWeight;
            table.querySelectorAll(`tbody td[data-col-id="${col.id}"], tbody td[data-col-id="${col.id}"] input, tbody td[data-col-id="${col.id}"] select, tbody td[data-col-id="${col.id}"] .total-val, tbody td[data-col-id="${col.id}"] .avail-val, tbody td[data-col-id="${col.id}"] .g-readonly, tbody td[data-col-id="${col.id}"] .row-num`).forEach(el => {
                el.style.setProperty('font-size', fSz, 'important');
                el.style.setProperty('font-weight', fWt, 'important');
            });
        });

        // Reorder thead & sync column header names
        const theadTr = table.querySelector('thead tr');
        if (theadTr) {
            cfg.columns.forEach(col => {
                const th = theadTr.querySelector(`th[data-col-id="${col.id}"]`);
                if (th) {
                    if (col.name && col.id !== 'price' && col.id !== 'total') {
                        const resizer = th.querySelector('.col-resizer');
                        th.textContent = col.name;
                        if (resizer) th.appendChild(resizer);
                    }
                    theadTr.appendChild(th);
                }
            });
        }

        // Reorder tbody
        const tbodyRows = table.querySelectorAll('tbody tr');
        tbodyRows.forEach(tr => {
            if (tr.children.length === 1 && tr.children[0].colSpan > 1) return;
            cfg.columns.forEach(col => {
                const td = tr.querySelector(`td[data-col-id="${col.id}"]`);
                if (td) {
                    tr.appendChild(td);
                }
            });
        });
        
        // Reorder tfoot (if any)
        const tfootTr = table.querySelector('tfoot tr');
        if (tfootTr && tfootTr.children.length > 1) {
            cfg.columns.forEach(col => {
                const td = tfootTr.querySelector(`th[data-col-id="${col.id}"], td[data-col-id="${col.id}"]`);
                if (td) {
                    tfootTr.appendChild(td);
                }
            });
        }

        if (typeof makeTableResizable === 'function') makeTableResizable();
        if (typeof window.autoFitGridCells === 'function') {
            requestAnimationFrame(() => window.autoFitGridCells());
        }
    };

    window.addEventListener('resize', () => {
        if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
        if (typeof window.autoFitGridCells === 'function') window.autoFitGridCells();
    });
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
            if (typeof window.autoFitGridCells === 'function') window.autoFitGridCells();
        }, 180);
    });





    window.saveGridConfigToDB = async function(config) {
        if (!config || !config.grid_id) return;
        try {
            const res = await fetch('api/save_grid_config.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ grid_id: config.grid_id, config: config })
            });
            const data = await res.json();
            if (data && data.status === 'success') {
                console.log("Grid config saved to DB.");
                // Update localStorage backup if you want
                localStorage.setItem('erp_grid_config_' + config.grid_id, JSON.stringify(config));
            } else {
                console.error("Failed to save grid config", data);
            }
        } catch (e) {
            console.error("Error saving grid config:", e);
        }
    };



        window.openGridSettingsModal = function() {
        if (!window.CurrentGridConfig) return;
        tempGridConfig = JSON.parse(JSON.stringify(window.CurrentGridConfig));
        
        // General settings
        if(document.getElementById('grid-setting-font-family')) document.getElementById('grid-setting-font-family').value = tempGridConfig.fontFamily || "'Cairo', sans-serif";
        if(document.getElementById('grid-setting-font-size')) document.getElementById('grid-setting-font-size').value = tempGridConfig.fontSize || 16;
        if(document.getElementById('grid-setting-font-weight')) document.getElementById('grid-setting-font-weight').value = tempGridConfig.fontWeight || 'normal';
        if(document.getElementById('grid-setting-font-color')) document.getElementById('grid-setting-font-color').value = tempGridConfig.fontColor || '#ffffff';
        if(document.getElementById('grid-setting-align')) document.getElementById('grid-setting-align').value = tempGridConfig.align || 'center';
        if(document.getElementById('grid-setting-decimals')) document.getElementById('grid-setting-decimals').value = tempGridConfig.decimalPlaces !== undefined ? tempGridConfig.decimalPlaces : 2;
        if(document.getElementById('grid-setting-thousand-sep')) document.getElementById('grid-setting-thousand-sep').value = tempGridConfig.thousandSep || ',';
        
        selectedColIndex = 0;
        if (typeof renderModalColumnsList === 'function') renderModalColumnsList();
        if (typeof populateSelectedColPanel === 'function') populateSelectedColPanel();
        
        document.getElementById('grid-settings-modal').style.display = 'flex';
    };



    window.closeGridSettingsModal = function() {
        document.getElementById('grid-settings-modal').style.display = 'none';
    };

    window.quickAlignGrid = function(alignMode) {
        window.CurrentGridConfig.align = alignMode;
        window.CurrentGridConfig.columns.forEach(col => {
            col.align = alignMode;
        });
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        saveGridConfigToDB(window.CurrentGridConfig);
        const menu = document.getElementById('col-context-menu');
        if (menu) menu.style.display = 'none';
        showToast(`تم ضبط المحاذاة إلى ${alignMode === 'right' ? 'اليمين' : alignMode === 'center' ? 'الوسط' : 'اليسار'}`, "success");
    };

    window.quickResetGrid = async function() {
        const menu = document.getElementById('col-context-menu');
        if (menu) menu.style.display = 'none';
        try {
            const res = await fetch('api/get_grid_config.php?grid_id=purchase_grid_default&_t=' + Date.now());
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
                window.CurrentGridConfig.grid_id = 'purchase_grid';
            } else {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
            }
        } catch(e) {
            window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
        }
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        saveGridConfigToDB(window.CurrentGridConfig);
        if (tempGridConfig) {
            tempGridConfig = JSON.parse(JSON.stringify(window.CurrentGridConfig));
            if (typeof renderModalColumnsList === 'function') renderModalColumnsList();
            if (typeof populateSelectedColPanel === 'function') populateSelectedColPanel();
        }
        showToast("تم استعادة الضبط الافتراضي وحفظه في قاعدة البيانات بنجاح ✅", "success");
    };

    // 7. Column Header Dragging and Resizing
    function makeTableResizable() {
        const table = document.getElementById('pos-grid');
        if (!table) return;
        const row = table.querySelector('thead tr');
        const cols = row ? row.children : [];
        if (!cols) return;

        table.querySelectorAll('.col-resizer').forEach(r => r.remove());

        for (let i = 0; i < cols.length; i++) {
            const th = cols[i];
            const colId = th.getAttribute('data-col-id');
            if (colId === "col_del" || colId === "action") continue;

            const resizer = document.createElement('div');
            resizer.className = 'col-resizer';
            resizer.style.cssText = 'position:absolute; left:0; top:0; width:7px; height:100%; cursor:col-resize; user-select:none; z-index:15; transition: background-color 0.2s;';
            resizer.addEventListener('mouseenter', () => resizer.style.backgroundColor = 'rgba(56, 189, 248, 0.6)');
            resizer.addEventListener('mouseleave', () => resizer.style.backgroundColor = 'transparent');
            th.appendChild(resizer);

            let startX, startWidth;

            resizer.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                startX = e.clientX;
                startWidth = th.offsetWidth;

                function doDrag(ev) {
                    const dx = ev.clientX - startX;
                    const newWidth = Math.max(40, startWidth - dx); // RTL
                    
                    th.style.width = newWidth + 'px';
                    th.style.minWidth = newWidth + 'px';
                    th.style.maxWidth = newWidth + 'px';

                    const tds = table.querySelectorAll(`tbody td[data-col-id="${colId}"]`);
                    tds.forEach(td => {
                        td.style.width = newWidth + 'px';
                        td.style.minWidth = newWidth + 'px';
                        td.style.maxWidth = newWidth + 'px';
                    });
                }

                function stopDrag() {
                    document.removeEventListener('mousemove', doDrag);
                    document.removeEventListener('mouseup', stopDrag);

                    const cfg = window.CurrentGridConfig;
                    const colSetting = cfg.columns.find(c => String(c.id) === String(colId));
                    if (colSetting) {
                        colSetting.width = th.style.width;
                        saveGridConfigToDB(cfg);
                    }
                }

                document.addEventListener('mousemove', doDrag);
                document.addEventListener('mouseup', stopDrag);
            });

            // Context Menu on Right Click
            th.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                const menu = document.getElementById('col-context-menu');
                if (menu) {
                    menu.style.display = 'block';
                    menu.style.left = Math.min(window.innerWidth - 250, e.clientX) + 'px';
                    menu.style.top = Math.min(window.innerHeight - 200, e.clientY) + 'px';
                }
            });
        }
    }

    // Close Context Menu on outside click
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('col-context-menu');
        if (menu && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    // Initialize Grid Config from DB on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadGridConfigFromDB();
    });

    // Also trigger on DBReady if applicable
    document.addEventListener('DBReady', () => {
        loadGridConfigFromDB();
    });
