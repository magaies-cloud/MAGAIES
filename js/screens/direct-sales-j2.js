    // ══════════════════════════════════════════════════════════════════════════
    //  INTERACTIVE DATABASE-PERSISTENT GRID CUSTOMIZER SYSTEM (sales_grid)
    // ══════════════════════════════════════════════════════════════════════════

    const GRID_ID = 'sales_grid';

    const DEFAULT_GRID_CONFIG = {
        "grid_id": "sales_grid",
        "fontSize": 16,
        "fontWeight": "normal",
        "fontFamily": "'Cairo', sans-serif",
        "fontColor": "#ffffff",
        "align": "center",
        "decimalPlaces": 2,
        "thousandSep": ",",
        "columns": [
            {
                "id": "col_num",
                "name": "#",
                "visible": true,
                "width": "40px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 20,
                "fontWeight": "800"
            },
            {
                "id": "col_avail",
                "name": "المتاح",
                "visible": true,
                "width": "83px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 16,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_wh",
                "name": "المخزن",
                "visible": true,
                "width": "148px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 18,
                "fontWeight": "800"
            },
            {
                "id": "col_factor",
                "name": "التعادل",
                "visible": true,
                "width": "90px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 16,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_item",
                "name": "الصنف",
                "visible": true,
                "width": "334px",
                "align": "center",
                "color": "#fedd67",
                "fontSize": 22,
                "fontWeight": "800"
            },
            {
                "id": "col_unit",
                "name": "الوحدات",
                "visible": true,
                "width": "119px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 18,
                "fontWeight": "800"
            },
            {
                "id": "col_qty",
                "name": "الكمية",
                "visible": true,
                "width": "134px",
                "align": "center",
                "color": "#7cfedd",
                "fontSize": 22,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_price",
                "name": "السعر",
                "visible": true,
                "width": "222px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 22,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_total",
                "name": "الإجمالي",
                "visible": true,
                "width": "162px",
                "align": "center",
                "color": "#f96c6c",
                "fontSize": 26,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_baseqty",
                "name": "عدد الوحدة",
                "visible": true,
                "width": "163px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 18,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_note",
                "name": "ملاحظة",
                "visible": true,
                "width": "126px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 20,
                "fontWeight": "800"
            },
            {
                "id": "col_del",
                "name": "الإجراءات",
                "visible": true,
                "width": "81px",
                "align": "center",
                "fontSize": 20,
                "fontWeight": "800",
                "color": "#fafafa"
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

            const colColor = col.color || tempGridConfig.fontColor || '#ffffff';
            const colSize = col.fontSize || tempGridConfig.fontSize || 16;
            const colWidth = col.width || '100px';

            item.innerHTML = `
                <!-- Move ▲ ▼ & Checkbox & Name -->
                <div style="display:flex; align-items:center; gap:8px; flex:1;">
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <button type="button" onclick="moveModalColumn(${index}, 'up'); event.stopPropagation();" style="background:rgba(255,255,255,0.1); border:none; border-radius:4px; width:22px; height:18px; cursor:pointer; color:#fff; font-size:0.65rem; display:flex; align-items:center; justify-content:center;" ${index === 0 ? 'disabled style="opacity:0.2;"' : ''}>▲</button>
                        <button type="button" onclick="moveModalColumn(${index}, 'down'); event.stopPropagation();" style="background:rgba(255,255,255,0.1); border:none; border-radius:4px; width:22px; height:18px; cursor:pointer; color:#fff; font-size:0.65rem; display:flex; align-items:center; justify-content:center;" ${index === tempGridConfig.columns.length - 1 ? 'disabled style="opacity:0.2;"' : ''}>▼</button>
                    </div>
                    
                    <input type="checkbox" id="modal-col-chk-${col.id}" ${col.visible ? 'checked' : ''} onchange="toggleModalColVis(${index}, this.checked); event.stopPropagation();" style="cursor:pointer; width:17px; height:17px; accent-color:#10b981;">
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:bold; font-size:0.95rem; color:${colColor};">${col.name}</span>
                        <span style="font-size:0.75rem; color:#94a3b8;">${colWidth} | ${colSize}px</span>
                    </div>
                </div>

                <!-- Active Indicator Badge -->
                <div style="display:flex; align-items:center; gap:6px;">
                    <div style="width:14px; height:14px; border-radius:50%; background:${colColor}; border:1px solid rgba(255,255,255,0.4);" title="لون الخط"></div>
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
        renderModalColumnsList();
    };

    window.applySelectedColStyleToAll = function() {
        const sourceCol = tempGridConfig.columns[selectedColIndex];
        if (!sourceCol) return;

        tempGridConfig.columns.forEach(col => {
            if (sourceCol.fontSize) col.fontSize = sourceCol.fontSize;
            if (sourceCol.fontWeight) col.fontWeight = sourceCol.fontWeight;
            if (sourceCol.color) col.color = sourceCol.color;
            if (sourceCol.align) col.align = sourceCol.align;
        });

        renderModalColumnsList();
        populateSelectedColPanel();
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
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        showToast("👁️ تم تطبيق التنسيق للمعاينة المباشرة على الجدول", "info", 2000);
    };

    window.saveAndApplyGridSettingsToDB = async function() {
        readModalGeneralSettings();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        
        await saveGridConfigToDB(window.CurrentGridConfig);
        closeGridSettingsModal();
    };

    window.loadGridConfigFromDB = async function() {
        try {
            const gridId = (window.CurrentGridConfig && window.CurrentGridConfig.grid_id) ? window.CurrentGridConfig.grid_id : 'sales_grid';
            const res = await fetch('api/get_grid_config.php?grid_id=' + gridId + '&_t=' + Date.now());
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
            } else {
                // No saved config in DB — use factory default
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
            }
            if (typeof applyGridSettings === 'function') applyGridSettings();
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        } catch (e) {
            console.error("Failed to load grid config", e);
            // Fallback to hardcoded default on error
            if (!window.CurrentGridConfig) {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
                if (typeof applyGridSettings === 'function') applyGridSettings();
            }
        }
    };
    // Smart Canvas Text-Width Measurement Engine (2027 World-Class Precision)
    let _salesHeaderMeasureCanvas = null;
    function measureSalesTextWidthPx(text, fontStr) {
        try {
            if (!_salesHeaderMeasureCanvas) _salesHeaderMeasureCanvas = document.createElement('canvas');
            const ctx = _salesHeaderMeasureCanvas.getContext('2d');
            ctx.font = fontStr;
            return ctx.measureText(text || '').width;
        } catch (e) {
            return (text ? text.length : 0) * 10;
        }
    }

    window.applyGridSettings = function() {
        let cfg = window.CurrentGridConfig;
        if (!cfg || !cfg.columns) return;

        // Clear initial SSR dynamic style so live customization rules take 100% precedence
        const ssrStyle = document.getElementById('dynamic-pos-grid-style');
        if (ssrStyle) ssrStyle.innerHTML = '';
        
        let style = document.getElementById('dynamic-grid-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-grid-style';
            document.head.appendChild(style);
        }
        
        const baseFontSize = (cfg.fontSize || 16) + 'px';
        const baseFontWeight = cfg.fontWeight || 'normal';
        const baseFontFamily = cfg.fontFamily || "'Cairo', sans-serif";
        const baseFontColor = cfg.fontColor || '#ffffff';

        let css = `
            body #pos-grid,
            body #pos-grid .g-input,
            body #pos-grid .g-select,
            body #pos-grid .total-val,
            body #pos-grid .g-readonly {
                font-size: ${baseFontSize} !important;
                font-weight: ${baseFontWeight} !important;
                font-family: ${baseFontFamily} !important;
                color: ${baseFontColor} !important;
            }
        `;
        
        const visCols = cfg.columns.filter(c => !(c.visible === false || c.visible === "false" || c.visible === 0));
        const colPxMap = {};
        let totalPxW = 0;
        visCols.forEach(c => {
            const raw = (c.width && c.width !== 'auto') ? parseFloat(String(c.width).replace(/[^\d.]/g, '')) : 0;
            const px = (raw > 20) ? raw : ((c.id === 'item' || c.id === 'col_item') ? 240 : ((c.id === 'index' || c.id === 'col_num' || c.id === 'actions' || c.id === 'col_actions') ? 52 : 90));
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
                let pctW = ((colPxMap[col.id] || 90) / totalPxW * 100).toFixed(2) + '%';
                let widthCss = `width: ${pctW} !important; min-width: 0 !important; max-width: ${pctW} !important;`;
                let align = (col.id === 'item' || col.id === 'col_item') ? 'center' : (col.align ? col.align : (cfg.align || 'center'));
                let fontSize = col.fontSize ? (String(col.fontSize).replace('px','') + 'px') : baseFontSize;
                let color = col.color ? col.color : baseFontColor;
                let fontWeight = col.fontWeight ? col.fontWeight : baseFontWeight;
                
                css += `
                    html body #invoice-editor-view #pos-grid#pos-grid thead tr th[data-col-id="${col.id}"] {
                        display: table-cell !important;
                        ${widthCss}
                        text-align: ${align} !important;
                        font-size: ${fontSize} !important;
                        font-weight: ${fontWeight} !important;
                        color: ${col.color && col.color.toLowerCase() !== '#ffffff' && col.color.toLowerCase() !== '#fafafa' ? color : '#38bdf8'} !important;
                    }
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"],
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input,
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select,
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .g-input,
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .g-select,
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val,
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .g-readonly,
                    :root.light-theme body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input,
                    :root.light-theme body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select,
                    :root.light-theme body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    :root.light-theme body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .g-readonly {
                        text-align: ${align} !important;
                        color: ${color} !important;
                        font-size: ${fontSize} !important;
                        font-weight: ${fontWeight} !important;
                        font-family: ${baseFontFamily} !important;
                    }
                    html body #invoice-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] {
                        display: table-cell !important;
                        ${widthCss}
                    }
                `;
            }
        });
        
        style.innerHTML = css;
        if (document.body && style.parentNode !== document.body) document.body.appendChild(style);
        else if (document.body) document.body.appendChild(style);

        // DOM Reordering & Header Name Synchronization
        const table = document.getElementById('pos-grid');
        if (!table) return;
        cfg.columns.forEach(col => {
            const fSz = col.fontSize ? (String(col.fontSize).replace('px','') + 'px') : baseFontSize;
            const fWt = col.fontWeight ? col.fontWeight : baseFontWeight;
            table.querySelectorAll(`tbody td[data-col-id="${col.id}"], tbody td[data-col-id="${col.id}"] input, tbody td[data-col-id="${col.id}"] select, tbody td[data-col-id="${col.id}"] .total-val, tbody td[data-col-id="${col.id}"] .avail-val, tbody td[data-col-id="${col.id}"] .g-readonly`).forEach(el => {
                el.style.setProperty('font-size', fSz, 'important');
                el.style.setProperty('font-weight', fWt, 'important');
            });
        });

        // Reorder thead & sync column names
        const theadTr = table.querySelector('thead tr');
        if (theadTr) {
            cfg.columns.forEach(col => {
                const th = theadTr.querySelector(`th[data-col-id="${col.id}"]`);
                if (th) {
                    if (col.name && col.id !== 'col_price' && col.id !== 'col_total') {
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
        
        // Properly adjust tfoot colSpans so col_total aligns directly beneath the total column
        const tfoot = table.querySelector('tfoot');
        if (tfoot) {
            const visibleCols = cfg.columns.filter(c => c.visible !== false && c.visible !== "false" && c.visible !== 0);
            const totalIndex = visibleCols.findIndex(c => c.id === 'col_total' || c.id === 'total');
            const colsBefore = totalIndex >= 0 ? totalIndex : Math.max(1, visibleCols.length - 2);
            const colsAfter = Math.max(1, visibleCols.length - colsBefore - 1);

            tfoot.querySelectorAll('tr').forEach(tr => {
                const totalTd = tr.querySelector('[data-col-id="col_total"]');
                if (!totalTd) return;
                
                const firstTd = tr.firstElementChild;
                if (firstTd && firstTd !== totalTd) {
                    firstTd.colSpan = colsBefore;
                }
                const lastTd = tr.lastElementChild;
                if (lastTd && lastTd !== totalTd && lastTd !== firstTd) {
                    lastTd.colSpan = colsAfter;
                }
            });
        }

        if (typeof window.initSalesColumnResizers === 'function') window.initSalesColumnResizers();
        if (typeof window.autoFitGridCells === 'function') {
            requestAnimationFrame(() => window.autoFitGridCells());
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════
    //  2027 SMART AUTO-FIT FONT SCALING (Header Fields + Grid Cells via Canvas)
    // ═══════════════════════════════════════════════════════════════════════════
    window.autoFitHeaderFields = function() {
        try {
            // 1. Invoice Number (#inv-number) — Exact Unified Font Matching Date (#hdr-date)
            const invNoEl = document.getElementById('inv-number');
            const dtEl = document.getElementById('hdr-date');
            if (invNoEl) {
                const dtSize = dtEl ? (window.getComputedStyle(dtEl).fontSize || '15.5px') : '15.5px';
                invNoEl.style.setProperty('font-size', dtSize, 'important');
            }

            // 2. Customer Search Input (#hdr-customer-search) — Scales with name length & box width
            const custInput = document.getElementById('hdr-customer-search');
            if (custInput) {
                const val = (custInput.value || '').trim();
                const availW = Math.max(120, (custInput.clientWidth || 280) - 82);
                let px = 19.5;
                if (val) {
                    while (px > 12.5 && measureSalesTextWidthPx(val, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                custInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 3. Exchange Rate (#hdr-exchange-rate) — Prominent Bold Number Fitted to Box
            const rateInput = document.getElementById('hdr-exchange-rate');
            if (rateInput) {
                const val = String(rateInput.value || '').trim();
                const availW = Math.max(60, (rateInput.clientWidth || 100) - 14);
                let px = 22;
                if (val) {
                    while (px > 14.5 && measureSalesTextWidthPx(val, `900 ${px}px 'Outfit', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                rateInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 4. Currency Select (#hdr-currency)
            const curSel = document.getElementById('hdr-currency');
            if (curSel && curSel.options && curSel.selectedIndex >= 0) {
                const optText = (curSel.options[curSel.selectedIndex].text || '').trim();
                const availW = Math.max(55, (curSel.clientWidth || 88) - 24);
                let px = 15.5;
                if (optText) {
                    while (px > 11.5 && measureSalesTextWidthPx(optText, `800 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                curSel.style.setProperty('font-size', px + 'px', 'important');
            }

            // 5. Warehouse (#hdr-warehouse) & Sub-branch / Cost Center (#hdr-cost-center)
            ['hdr-warehouse', 'hdr-cost-center'].forEach(selId => {
                const sel = document.getElementById(selId);
                if (!sel || !sel.options || sel.selectedIndex < 0) return;
                const optText = (sel.options[sel.selectedIndex].text || '').trim();
                const availW = Math.max(75, (sel.clientWidth || 140) - 30);
                let px = 15.5;
                if (optText) {
                    while (px > 11.5 && measureSalesTextWidthPx(optText, `800 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                sel.style.setProperty('font-size', px + 'px', 'important');
            });

            // 6. Auto-Fit Financial Strip Capsules (الرصيد، المديونية، الشيكات، الحد، المتاح) so all 5 + Currency Button fit 100% with compact labels & unclipped numbers
            const finStrip = document.querySelector('#customer-financial-card .fin-strip-inner');
            if (finStrip) {
                const pills = Array.from(finStrip.querySelectorAll('.fin-metric-pill'));
                const stripW = finStrip.clientWidth || 580;
                const curBtn = document.getElementById('fin-strip-currency-btn');
                const curBtnW = curBtn ? (curBtn.offsetWidth || 72) : 72;
                const totalAvail = Math.max(260, stripW - curBtnW - 28);
                const totalChars = pills.reduce((acc, p) => acc + ((p.textContent || '').replace(/\s+/g, '').length || 6), 0);
                pills.forEach(pill => {
                    const lbl = pill.querySelector('.fin-lbl');
                    const val = pill.querySelector('.fin-val');
                    const lblTxt = lbl ? (lbl.textContent || '').trim() : '';
                    const valTxt = val ? (val.textContent || '').trim() : '';
                    const charShare = Math.max(0.14, ((lblTxt.length + valTxt.length) || 6) / Math.max(1, totalChars));
                    const allocW = Math.max(52, Math.floor(totalAvail * charShare));
                    let lblPx = valTxt.length > 9 ? 9.5 : 10.5;
                    let valPx = pill.id === 'fin-wrap-remaining' ? 15.5 : 14.0;
                    if (valTxt.length > 8) valPx = Math.min(valPx, 12.5);
                    if (valTxt.length > 11) valPx = Math.min(valPx, 11.0);
                    const maxTxtW = Math.max(42, (pill.clientWidth || allocW) - 10);
                    while (valPx > 8.5 && (measureSalesTextWidthPx(lblTxt, `900 ${lblPx}px 'Cairo', sans-serif`) + measureSalesTextWidthPx(valTxt, `900 ${valPx}px 'Outfit', sans-serif`) + 5) > maxTxtW) {
                        valPx -= 0.5;
                        if (lblPx > 8.5) lblPx -= 0.25;
                    }
                    if (lbl) lbl.style.setProperty('font-size', lblPx + 'px', 'important');
                    if (val) val.style.setProperty('font-size', valPx + 'px', 'important');
                });
            }

            // 7. Auto-Fit Text Inside Action & Toggle Buttons (#invoice-editor-view)
            const editorBtns = document.querySelectorAll('#invoice-editor-view .toolbar-actions .btn-action, #invoice-editor-view .pay-toggle-btn, #invoice-editor-view .tot-toggle-btn');
            editorBtns.forEach(btn => {
                const btnW = btn.clientWidth;
                if (!btnW || btnW < 40) return;
                const txt = (btn.textContent || '').replace(/\s+/g, ' ').trim();
                if (!txt) return;
                const hasSvg = !!btn.querySelector('svg');
                const iconSpace = hasSvg ? 44 : 28;
                const availW = Math.max(36, btnW - iconSpace);
                let px = btn.classList.contains('btn-submit') ? 16 : 15;
                while (px > 11 && measureSalesTextWidthPx(txt, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                    px -= 0.5;
                }
                btn.style.setProperty('font-size', px + 'px', 'important');
            });
        } catch (e) {}
    };

    window.onGlobalGridFontSizeLive = function(v) {
        const px = Math.max(8, Math.min(42, parseInt(v) || 15));
        if (typeof tempGridConfig !== 'undefined' && tempGridConfig) {
            tempGridConfig.fontSize = px;
            if (Array.isArray(tempGridConfig.columns)) {
                tempGridConfig.columns.forEach(c => { c.fontSize = px; });
            }
            const selFs = document.getElementById('sel-col-fontsize');
            const selFsR = document.getElementById('sel-col-fontsize-range');
            if (selFs) selFs.value = px;
            if (selFsR) selFsR.value = px;
            window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
            if (typeof applyGridSettings === 'function') applyGridSettings();
            if (typeof window.autoFitGridCells === 'function') window.autoFitGridCells();
        }
    };

    window.autoFitGridCells = function(targetRow) {
        try {
            const table = document.getElementById('pos-grid');
            const cfg = window.CurrentGridConfig;
            if (!table || !cfg || !cfg.columns) return;
            const fontFam = cfg.fontFamily || "'Cairo', sans-serif";
            const baseGlobalFs = parseFloat(cfg.fontSize) || 18;

            cfg.columns.forEach(col => {
                if (col.visible === false || col.visible === 'false' || col.visible === 0) return;
                const basePx = parseFloat(col.fontSize) || baseGlobalFs;
                const weight = col.fontWeight || cfg.fontWeight || '800';

                // 1. Synchronize Column Header (th) with basePx so enlarging/shrinking scales both header & cell numbers
                if (!targetRow) {
                    const th = table.querySelector(`thead th[data-col-id="${col.id}"]`);
                    if (th && col.id !== 'col_del' && col.id !== 'action') {
                        const thText = (th.textContent || col.name || '').trim();
                        const thAvailW = Math.max(28, (th.clientWidth || parseInt(col.width) || 90) - 12);
                        let thPx = basePx;
                        if (thText && thAvailW > 20) {
                            while (thPx > 8 && measureSalesTextWidthPx(thText, `800 ${thPx}px ${fontFam}`) > thAvailW) {
                                thPx -= 0.5;
                            }
                        }
                        th.style.setProperty('font-size', thPx + 'px', 'important');
                    }
                }

                // 2. Synchronize Cell Inputs/Selects/Values to basePx & auto-fit long values to column width
                const rowsToScan = targetRow ? [targetRow] : Array.from(table.querySelectorAll('tbody tr'));
                rowsToScan.forEach(tr => {
                    const td = tr.querySelector(`td[data-col-id="${col.id}"]`);
                    if (!td) return;
                    const ctrl = td.querySelector('input.g-input, select.g-select, .total-val, .avail-val, .g-readonly');
                    if (!ctrl) return;

                    let textVal = '';
                    if (ctrl.tagName === 'SELECT') {
                        textVal = (ctrl.options && ctrl.selectedIndex >= 0) ? (ctrl.options[ctrl.selectedIndex].text || '').trim() : '';
                    } else if (ctrl.tagName === 'INPUT') {
                        textVal = (ctrl.value || '').trim();
                    } else {
                        textVal = (ctrl.textContent || '').trim();
                    }

                    const tdW = td.clientWidth || parseInt(col.width) || 100;
                    const paddingOffset = (col.id === 'col_item' || col.id === 'item') ? 24 : (ctrl.tagName === 'SELECT' ? 22 : (col.id === 'col_price' ? 28 : 12));
                    const availW = Math.max(28, tdW - paddingOffset);

                    let targetPx = basePx;
                    if (textVal && availW > 24) {
                        while (targetPx > 8 && measureSalesTextWidthPx(textVal, `${weight} ${targetPx}px ${fontFam}`) > availW) {
                            targetPx -= 0.5;
                        }
                    }
                    ctrl.style.setProperty('font-size', targetPx + 'px', 'important');
                });
            });
        } catch (e) {}
    };

    // Interactive Column Resizer with Proportional Font Scaling for Price & Qty
    window.initSalesColumnResizers = function() {
        const table = document.getElementById('pos-grid');
        if (!table) return;
        const row = table.querySelector('thead tr');
        const cols = row ? row.children : [];
        if (!cols) return;

        table.querySelectorAll('.col-resizer').forEach(r => r.remove());

        for (let i = 0; i < cols.length; i++) {
            const th = cols[i];
            const colId = th.getAttribute('data-col-id');
            if (colId === 'col_del' || colId === 'action') continue;

            const resizer = document.createElement('div');
            resizer.className = 'col-resizer';
            resizer.style.cssText = 'position:absolute; left:0; top:0; width:8px; height:100%; cursor:col-resize; user-select:none; z-index:20; transition: background-color 0.2s;';
            resizer.addEventListener('mouseenter', () => resizer.style.backgroundColor = 'rgba(56, 189, 248, 0.75)');
            resizer.addEventListener('mouseleave', () => resizer.style.backgroundColor = 'transparent');
            th.appendChild(resizer);

            let startX, startWidth, startFontSize;

            resizer.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                startX = e.clientX;
                startWidth = th.offsetWidth || parseInt(th.style.width) || 120;
                const cfg = window.CurrentGridConfig;
                const colSetting = cfg && cfg.columns ? cfg.columns.find(c => String(c.id) === String(colId)) : null;
                startFontSize = colSetting ? (parseFloat(colSetting.fontSize) || 20) : 20;

                function doDrag(ev) {
                    const dx = ev.clientX - startX;
                    const newWidth = Math.max(50, startWidth - dx); // RTL

                    th.style.setProperty('width', newWidth + 'px', 'important');
                    th.style.setProperty('min-width', newWidth + 'px', 'important');
                    th.style.setProperty('max-width', newWidth + 'px', 'important');

                    const tds = table.querySelectorAll(`tbody td[data-col-id="${colId}"]`);
                    tds.forEach(td => {
                        td.style.setProperty('width', newWidth + 'px', 'important');
                        td.style.setProperty('min-width', newWidth + 'px', 'important');
                        td.style.setProperty('max-width', newWidth + 'px', 'important');
                    });

                    if (colSetting) {
                        colSetting.width = newWidth + 'px';
                        // Proportional header + number scaling when widening/narrowing Price or Qty columns
                        if (colId === 'col_price' || colId === 'col_qty') {
                            const ratio = newWidth / Math.max(60, startWidth);
                            const scaledFs = Math.max(15, Math.min(34, Math.round(startFontSize * (0.65 + 0.35 * ratio))));
                            colSetting.fontSize = scaledFs;
                        }
                        if (window.autoFitGridCells) window.autoFitGridCells();
                    }
                }

                function stopDrag() {
                    document.removeEventListener('mousemove', doDrag);
                    document.removeEventListener('mouseup', stopDrag);
                    if (window.CurrentGridConfig) {
                        applyGridSettings();
                        saveGridConfigToDB(window.CurrentGridConfig);
                    }
                }

                document.addEventListener('mousemove', doDrag);
                document.addEventListener('mouseup', stopDrag);
            });
        }
    };

    // Ensure modal column property changes immediately preview on the grid
    const _origOnSelectedColPropChange = window.onSelectedColPropChange;
    window.onSelectedColPropChange = function(prop, val) {
        if (typeof _origOnSelectedColPropChange === 'function') {
            _origOnSelectedColPropChange(prop, val);
        }
        if (tempGridConfig) {
            window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
            applyGridSettings();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (window.autoFitHeaderFields) window.autoFitHeaderFields();
            if (window.autoFitGridCells) window.autoFitGridCells();
        }, 250);
    });
    window.addEventListener('resize', () => {
        if (window.autoFitHeaderFields) window.autoFitHeaderFields();
        if (window.autoFitGridCells) window.autoFitGridCells();
    });
    document.addEventListener('input', (e) => {
        if (e.target && e.target.closest && e.target.closest('#pos-grid')) {
            const tr = e.target.closest('tr');
            if (window.autoFitGridCells) window.autoFitGridCells(tr);
        }
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
        const okReset = typeof window.showCenteredConfirmModal === 'function'
            ? await window.showCenteredConfirmModal({ title: 'استعادة تنسيق الجدول', badge: 'الوضع الافتراضي', icon: '🔄', tone: 'warning', message: 'هل أنت متأكد من استعادة التنسيق والترتيب الافتراضي لجدول المبيعات المباشرة وحفظه في قاعدة البيانات؟', confirmText: '✅ نعم، استعادة', cancelText: '✕ إلغاء' })
            : confirm("هل أنت متأكد من استعادة التنسيق والترتيب الافتراضي لجدول المبيعات المباشرة وحفظه في قاعدة البيانات؟");
        if (okReset) {
            try {
                // Try to fetch factory default from DB
                const res = await fetch('api/get_grid_config.php?grid_id=sales_grid_default&_t=' + Date.now());
                const data = await res.json();
                if (data && data.config) {
                    window.CurrentGridConfig = data.config;
                    window.CurrentGridConfig.grid_id = 'sales_grid'; // Ensure correct grid_id for saving
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
        }
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
