/**
 * Studio Settings Panel Integration for Direct Sales POS Grid
 * 
 * This script adapts the standard Studio Settings Panel to work with
 * an interactive data-entry grid (#pos-grid) using CSS injection,
 * avoiding the destruction of DOM elements (inputs/selects) that would
 * occur if the table were re-rendered from scratch.
 */

// 1. Define base columns matching the #pos-grid headers
const baseColumns = [
    { idx: 0, field: 'col0', defaultLabel: '#', isNum: false },
    { idx: 1, field: 'col1', defaultLabel: 'الصنف', isNum: false },
    { idx: 2, field: 'col2', defaultLabel: 'الوحدة', isNum: false },
    { idx: 3, field: 'col3', defaultLabel: 'الكمية', isNum: true },
    { idx: 4, field: 'col4', defaultLabel: 'السعر', isNum: true },
    { idx: 5, field: 'col5', defaultLabel: 'العدد', isNum: true },
    { idx: 6, field: 'col6', defaultLabel: 'المتاح', isNum: true },
    { idx: 7, field: 'col7', defaultLabel: 'المتبقي', isNum: true },
    { idx: 8, field: 'col8', defaultLabel: 'سعر العملة', isNum: true },
    { idx: 9, field: 'col9', defaultLabel: 'إجمالي العملة', isNum: true },
    { idx: 10, field: 'col10', defaultLabel: 'المخزن', isNum: false },
    { idx: 11, field: 'col11', defaultLabel: 'الإجمالي', isNum: true },
    { idx: 12, field: 'col12', defaultLabel: 'حذف', isNum: false }
];

let dsColumnOrder = baseColumns.map(c => c.idx);
let dsColumnConfigs = {};
let dsSelectedColIndex = null;
let dsCurrentTheme = 'theme-luxury-black';
let dsCurrentFont = 'font-outfit';

// Preset Management
const PRESETS_STORAGE_KEY = 'direct_sales_presets_v1';
let dsPresets = [];

function initStudioSettings() {
    try {
        const savedPresets = localStorage.getItem(PRESETS_STORAGE_KEY);
        if (savedPresets) dsPresets = JSON.parse(savedPresets);
    } catch(e) {
        dsPresets = [];
    }

    renderPresetsDropdown();
    
    // Load default preset if none is selected
    if (dsPresets.length > 0) {
        loadPreset(dsPresets[0].id);
    } else {
        populateSelectedColDropdown();
        applyDynamicGridCSS();
    }
}

// 2. CSS Injection Engine
function applyDynamicGridCSS() {
    const styleEl = document.getElementById('dynamic-studio-styles');
    if (!styleEl) return;

    let css = '';
    
    // Theme & Font (Applied to the table wrapper or cells)
    const grid = document.getElementById('pos-grid');
    if (grid) {
        grid.className = 'pos-grid hub-table ' + (dsCurrentTheme || '') + ' ' + (dsCurrentFont || '');
    }

    // Apply the same theme and font to the unified live preview
    const previewContainer = document.getElementById('studio-live-preview-container');
    if (previewContainer) {
        previewContainer.className = (dsCurrentTheme || '') + ' ' + (dsCurrentFont || '');
    }

    // Column Configs
    dsColumnOrder.forEach((colIdx) => {
        const conf = dsColumnConfigs[colIdx] || { visible: true };
        const nthChild = colIdx + 1; // 1-indexed for CSS nth-child

        // Hide column
        if (conf.visible === false) {
            css += `
                #pos-grid th:nth-child(${nthChild}),
                #pos-grid td:nth-child(${nthChild}) {
                    display: none !important;
                }
            `;
        } else {
            css += `
                #pos-grid th:nth-child(${nthChild}),
                #pos-grid td:nth-child(${nthChild}) {
                    display: table-cell;
                }
            `;
            
            // Custom Styling
            let cellStyles = '';
            if (conf.fs) cellStyles += `font-size: ${conf.fs}rem !important;`;
            if (conf.align) cellStyles += `text-align: ${conf.align} !important;`;
            if (conf.fontWeight) cellStyles += `font-weight: ${conf.fontWeight} !important;`;
            if (conf.color) cellStyles += `color: ${conf.color} !important;`;
            if (conf.bgColor) cellStyles += `background-color: ${conf.bgColor} !important;`;

            if (cellStyles) {
                css += `
                    #pos-grid td:nth-child(${nthChild}) input,
                    #pos-grid td:nth-child(${nthChild}) select,
                    #pos-grid td:nth-child(${nthChild}) .m-card-val,
                    #pos-grid td:nth-child(${nthChild}) {
                        ${cellStyles}
                    }
                `;
            }
        }
    });

    styleEl.innerHTML = css;
    updateHiddenColumnsDropdown();
}

// This overrides renderTable (called by studio-settings-panel.php)
function renderTable() {
    applyDynamicGridCSS();
}

// 3. Studio Settings Panel Integration Methods
function openStandalonePrintPreview() {
    if (typeof getInvoiceDataForPrint === 'function') {
        const data = getInvoiceDataForPrint();
        // Save to localStorage so the new tab can read it
        localStorage.setItem('erp_current_print_invoice', JSON.stringify(data));
        // Open the standalone preview design page
        window.open('preview-direct-sales-design.php', '_blank');
    } else {
        alert('حدث خطأ في تجميع بيانات الفاتورة.');
    }
}

function toggleSettingsPanel() {
    const p = document.getElementById('settings-panel');
    if (!p) return;
    
    const isOpening = (p.style.display === 'none' || p.style.display === '');
    
    if (isOpening) {
        p.classList.add('show');
        p.style.display = 'flex';
        // Auto-select a column if none selected (e.g. Price column)
        if (dsSelectedColIndex === null) {
            selectColumn(4); // col-4 is Price
        }
    } else {
        p.classList.remove('show');
        p.style.display = 'none';
    }
}

// Custom Print handler for the unified Studio/Preview screen
if (!window.InvoicePrinter) window.InvoicePrinter = {};
window.InvoicePrinter.doPrintStudio = function() {
    const previewContainer = document.getElementById('studio-live-preview-container');
    if (!previewContainer) return;
    
    const previewHtml = previewContainer.innerHTML;
    
    let iframe = document.getElementById('hidden-print-iframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'hidden-print-iframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }
    
    const dynamicStyles = document.getElementById('dynamic-studio-styles') ? document.getElementById('dynamic-studio-styles').innerHTML : '';
    
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <html dir="rtl">
        <head>
            <title>طباعة الفاتورة</title>
            <style>
                /* Essential reset */
                body { margin: 0; padding: 0; background: #fff; font-family: sans-serif; }
                /* Inject dynamic studio styles from the page */
                ${dynamicStyles}
            </style>
        </head>
        <body>
            ${previewHtml}
            <script>
                window.onload = function() { 
                    setTimeout(function(){ 
                        window.print(); 
                    }, 500); 
                };
            </script>
        </body>
        </html>
    `);
    doc.close();
};
function switchStudioTab(tab) {
    const btnCols = document.getElementById('tab-btn-cols');
    const btnPaper = document.getElementById('tab-btn-paper');
    const btnPresets = document.getElementById('tab-btn-dsPresets');
    
    const contentCols = document.getElementById('studio-tab-cols');
    const contentPaper = document.getElementById('studio-tab-paper');
    const contentPresets = document.getElementById('studio-tab-dsPresets');

    if(btnCols) btnCols.classList.remove('active');
    if(btnPaper) btnPaper.classList.remove('active');
    if(btnPresets) btnPresets.classList.remove('active');

    if(contentCols) contentCols.style.display = 'none';
    if(contentPaper) contentPaper.style.display = 'none';
    if(contentPresets) contentPresets.style.display = 'none';

    if (tab === 'cols' && btnCols) {
        btnCols.classList.add('active');
        contentCols.style.display = 'block';
    } else if (tab === 'paper' && btnPaper) {
        btnPaper.classList.add('active');
        contentPaper.style.display = 'block';
    } else if (tab === 'dsPresets' && btnPresets) {
        btnPresets.classList.add('active');
        contentPresets.style.display = 'block';
        renderPresetsManagementList();
    }
}

function selectColumn(idx) {
    dsSelectedColIndex = idx;
    const p = document.getElementById('settings-panel');
    if (p && (p.style.display === 'none' || p.style.display === '')) {
        p.classList.add('show');
        p.style.display = 'flex';
    }
    
    switchStudioTab('cols');
    updateSettingsPanelForCol(idx);
    applyDynamicGridCSS();
}

function updateSettingsPanelForCol(idx) {
    const c = baseColumns.find(x => x.idx === idx);
    if (!c) return;
    
    if (!dsColumnConfigs[idx]) {
        dsColumnConfigs[idx] = { visible: true, label: c.defaultLabel, fs: 0.9, align: 'center' };
    }
    const conf = dsColumnConfigs[idx];

    const selCol = document.getElementById('selected-col-select');
    if (selCol) selCol.value = idx;

    const chkVis = document.getElementById('col-visible-chk');
    if (chkVis) chkVis.checked = (conf.visible !== false);

    const txtLab = document.getElementById('col-label-input');
    if (txtLab) txtLab.value = conf.label || c.defaultLabel;

    const alignSel = document.getElementById('col-align-select');
    if (alignSel) alignSel.value = conf.align || 'center';

    const fsRange = document.getElementById('col-fs-range');
    const fsVal = document.getElementById('col-fs-val');
    if (fsRange && fsVal) {
        fsRange.value = conf.fs || 0.9;
        fsVal.innerText = (conf.fs || 0.9) + 'rem';
    }

    const fwSel = document.getElementById('col-fw-select');
    if (fwSel) fwSel.value = conf.fontWeight || '';

    const colorSel = document.getElementById('col-color-select');
    if (colorSel) colorSel.value = conf.color || '';

    const bgSel = document.getElementById('col-bg-select');
    if (bgSel) bgSel.value = conf.bgColor || '';

    // Advanced Formatting
    const decGroup = document.getElementById('col-decimals-group');
    if (decGroup) decGroup.style.display = c.isNum ? 'inline-flex' : 'none';

    if (c.isNum) {
        const dSel = document.getElementById('col-decimals-select');
        if (dSel) dSel.value = (conf.decimals !== undefined) ? conf.decimals : '2';

        const dsChk = document.getElementById('col-dec-sep-chk');
        if (dsChk) dsChk.checked = (conf.showDecSep !== false);

        const tsSel = document.getElementById('col-thousand-sep');
        if (tsSel) tsSel.value = conf.thousandSep || ',';

        const rSel = document.getElementById('col-rounding-select');
        if (rSel) rSel.value = conf.rounding || 'nearest';
    }
}

function updateSelectedCol(prop, val) {
    if (dsSelectedColIndex === null) return;
    if (!dsColumnConfigs[dsSelectedColIndex]) {
        const c = baseColumns.find(x => x.idx === dsSelectedColIndex);
        dsColumnConfigs[dsSelectedColIndex] = { visible: true, label: c ? c.defaultLabel : '' };
    }
    
    if (prop === 'fs') {
        const fsVal = document.getElementById('col-fs-val');
        if (fsVal) fsVal.innerText = val + 'rem';
    }
    
    dsColumnConfigs[dsSelectedColIndex][prop] = val;
    applyDynamicGridCSS();
    populateSelectedColDropdown();
}

function getDefaultConfigs() {
    let defs = {};
    baseColumns.forEach(c => {
        defs[c.idx] = { visible: true, label: c.defaultLabel, fs: 0.9, align: 'center' };
    });
    return defs;
}

function populateSelectedColDropdown() {
    const sel = document.getElementById('selected-col-select');
    if (!sel) return;
    const currentVal = dsSelectedColIndex;
    sel.innerHTML = '';
    
    baseColumns.forEach(c => {
        const conf = dsColumnConfigs[c.idx] || { visible: true, label: c.defaultLabel };
        const opt = document.createElement('option');
        opt.value = c.idx;
        opt.innerText = (conf.visible === false ? '👁️ (مخفي) ' : '') + (conf.label || c.defaultLabel);
        if (c.idx === currentVal) opt.selected = true;
        sel.appendChild(opt);
    });
    if (currentVal !== null) sel.value = currentVal;
}

function updateHiddenColumnsDropdown() {
    const select = document.getElementById('restore-hidden-col-select');
    if (!select) return;

    let hiddenCols = [];
    dsColumnOrder.forEach(idx => {
        const c = baseColumns.find(x => x.idx === idx);
        const conf = dsColumnConfigs[idx] || { visible: true, label: c.defaultLabel };
        if (c && conf.visible === false) {
            hiddenCols.push({ idx: c.idx, label: conf.label || c.defaultLabel });
        }
    });

    select.innerHTML = `<option value="">👁️ الأعمدة المخفية (${hiddenCols.length})</option>`;
    hiddenCols.forEach(hc => {
        const opt = document.createElement('option');
        opt.value = hc.idx;
        opt.innerText = '➕ إرجاع: ' + hc.label;
        select.appendChild(opt);
    });
}

function restoreHiddenColumn(idxStr) {
    if (idxStr === '' || idxStr === null) return;
    const idx = parseInt(idxStr);
    if (!dsColumnConfigs[idx]) dsColumnConfigs[idx] = { visible: true };
    dsColumnConfigs[idx].visible = true;
    
    selectColumn(idx);
    applyDynamicGridCSS();
}

// 4. dsPresets Management
function renderPresetsDropdown() {
    const sel = document.getElementById('preset-selector');
    if (!sel) return;
    sel.innerHTML = `<option value="">⚙️ التخصيصات (${dsPresets.length})</option>`;
    dsPresets.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.innerText = '💎 ' + p.name;
        sel.appendChild(opt);
    });
}

function renderPresetsManagementList() {
    const list = document.getElementById('dsPresets-list');
    if (!list) return;
    list.innerHTML = '';
    if (dsPresets.length === 0) {
        list.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8;">لا توجد تخصيصات محفوظة</div>';
        return;
    }
    
    dsPresets.forEach(p => {
        const div = document.createElement('div');
        div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.05); padding:10px 15px; border-radius:8px; margin-bottom:8px; border:1px solid rgba(255,255,255,0.1);';
        
        const nameSpan = document.createElement('span');
        nameSpan.innerText = p.name;
        nameSpan.style.cssText = 'font-weight:bold; color:#f8fafc; font-size:1rem; cursor:pointer;';
        nameSpan.onclick = () => loadPreset(p.id);
        
        const btnBox = document.createElement('div');
        
        const loadBtn = document.createElement('button');
        loadBtn.innerText = 'تطبيق';
        loadBtn.className = 'ribbon-btn';
        loadBtn.style.cssText = 'padding:4px 10px; font-size:0.75rem; background:#3b82f6; border:none; color:white; margin-left:5px; border-radius:6px;';
        loadBtn.onclick = () => loadPreset(p.id);
        
        const delBtn = document.createElement('button');
        delBtn.innerText = 'حذف';
        delBtn.className = 'ribbon-btn';
        delBtn.style.cssText = 'padding:4px 10px; font-size:0.75rem; background:#ef4444; border:none; color:white; border-radius:6px;';
        delBtn.onclick = () => deletePreset(p.id);
        
        btnBox.appendChild(loadBtn);
        btnBox.appendChild(delBtn);
        div.appendChild(nameSpan);
        div.appendChild(btnBox);
        list.appendChild(div);
    });
}

function saveCurrentPreset() {
    const pName = prompt('أدخل اسماً للتصميم الحالي (مثال: التصميم الكلاسيكي):', 'تصميم ' + (dsPresets.length + 1));
    if (!pName) return;
    
    const newPreset = {
        id: 'p_' + Date.now(),
        name: pName,
        dsColumnConfigs: JSON.parse(JSON.stringify(dsColumnConfigs)),
        dsColumnOrder: JSON.parse(JSON.stringify(dsColumnOrder)),
        theme: dsCurrentTheme,
        font: dsCurrentFont
    };
    
    dsPresets.push(newPreset);
    localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(dsPresets));
    
    renderPresetsDropdown();
    renderPresetsManagementList();
    
    const sel = document.getElementById('preset-selector');
    if (sel) sel.value = newPreset.id;
    
    alert('✅ تم حفظ التخصيص بنجاح!');
}

function loadPreset(id) {
    if (!id) return;
    const p = dsPresets.find(x => x.id === id);
    if (!p) return;
    
    dsColumnConfigs = JSON.parse(JSON.stringify(p.dsColumnConfigs || {}));
    dsColumnOrder = JSON.parse(JSON.stringify(p.dsColumnOrder || baseColumns.map(c => c.idx)));
    
    dsCurrentTheme = p.theme || 'theme-luxury-black';
    dsCurrentFont = p.font || 'font-outfit';
    
    const themeSel = document.getElementById('table-theme-select');
    if (themeSel) themeSel.value = dsCurrentTheme;
    
    const fontSel = document.getElementById('num-font-select');
    if (fontSel) fontSel.value = dsCurrentFont;
    
    populateSelectedColDropdown();
    if (dsColumnOrder.length > 0) {
        dsSelectedColIndex = dsColumnOrder[0];
        updateSettingsPanelForCol(dsSelectedColIndex);
    }
    
    applyDynamicGridCSS();
}

function deletePreset(id) {
    if (!confirm('هل أنت متأكد من حذف هذا التخصيص؟')) return;
    dsPresets = dsPresets.filter(x => x.id !== id);
    localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(dsPresets));
    renderPresetsDropdown();
    renderPresetsManagementList();
}

function setTableTheme(themeName) {
    dsCurrentTheme = themeName;
    applyDynamicGridCSS();
}

function setNumberFont(fontClass) {
    dsCurrentFont = fontClass;
    applyDynamicGridCSS();
}

// Fix for studio-settings-panel: the button calls switchStudioTab which we defined above.
// Ensure init is called when page loads
document.addEventListener('DOMContentLoaded', () => {
    initStudioSettings();
});
