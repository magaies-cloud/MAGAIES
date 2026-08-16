// js/direct-sales-preview-studio.js

let invoiceData = null;
let dsSelectedColIndex = null;
const styleEl = document.createElement('style');
styleEl.id = 'direct-sales-preview-dynamic-styles';
document.head.appendChild(styleEl);

let dsColConfigs = {};
let dsColumnOrder = [1, 2, 3, 4, 5, 6]; // Track column display order
let currentTableWidth = 100; // Table width percentage
const PRINT_COLUMNS = [
    { index: 1, label: 'م (رقم)' },
    { index: 2, label: 'الصنف / البيان' },
    { index: 3, label: 'الوحدة' },
    { index: 4, label: 'الكمية' },
    { index: 5, label: 'السعر' },
    { index: 6, label: 'الإجمالي' }
];

let dsPresets = [];
let currentTableTheme = '';
let currentLayoutTheme = '';
let currentNumFont = 'font-ultra';
let currentCopyLabel = 'نسخة العميل';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Load invoice data
    const raw = localStorage.getItem('erp_current_print_invoice');
    if (raw) {
        try { invoiceData = JSON.parse(raw); } catch (e) { console.error(e); }
    }
    
    if (!invoiceData) {
        document.getElementById('studio-live-preview-container').innerHTML = '<h3 style="text-align:center; margin-top:50px;">لا توجد بيانات فاتورة لمعاينتها. الرجاء العودة لشاشة المبيعات.</h3>';
        return;
    }

    // 2. Load presets from LocalStorage and merge defaults
    try {
        const p = localStorage.getItem('erp_ds_print_presets');
        if (p) dsPresets = JSON.parse(p);
    } catch(e){}

    const defaultPresets = [
        {
            id: 1001,
            name: 'نسخة العميل (الأساسية)',
            copyLabel: 'نسخة العميل',
            tableTheme: 'theme-classic-navy',
            layoutTheme: '',
            numFont: 'font-jakarta',
            columnOrder: [1, 2, 3, 4, 5, 6],
            tableWidth: 100,
            columns: {
                "1": { label: 'م (رقم)', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "2": { label: 'الصنف / البيان', visible: true, width: '40%', color: '', bg: '', align: 'right', fontWeight: 'bold', fontFamily: '' },
                "3": { label: 'الوحدة', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "4": { label: 'الكمية', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "5": { label: 'السعر', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "6": { label: 'الإجمالي', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'bold', fontFamily: '' }
            }
        },
        {
            id: 1002,
            name: 'نسخة المستودع / المخزن',
            copyLabel: 'إذن صرف مخزني',
            tableTheme: 'theme-slate',
            layoutTheme: 'layout-modern',
            numFont: 'font-newsons',
            columnOrder: [1, 2, 3, 4, 5, 6],
            tableWidth: 85,
            columns: {
                "1": { label: 'م (رقم)', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "2": { label: 'الصنف / البيان', visible: true, width: '60%', color: '', bg: '', align: 'right', fontWeight: 'bold', fontFamily: '' },
                "3": { label: 'الوحدة', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "4": { label: 'الكمية', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'bold', fontFamily: '' },
                "5": { label: 'السعر', visible: false, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "6": { label: 'الإجمالي', visible: false, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' }
            }
        },
        {
            id: 1003,
            name: 'نسخة كشف الحساب',
            copyLabel: 'كشف حساب العميل',
            tableTheme: 'theme-emerald-pro',
            layoutTheme: 'layout-split',
            numFont: 'font-roboto',
            columnOrder: [1, 2, 6, 3, 4, 5],
            tableWidth: 95,
            columns: {
                "1": { label: 'م (رقم)', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "2": { label: 'البيان', visible: true, width: '40%', color: '', bg: '', align: 'right', fontWeight: 'normal', fontFamily: '' },
                "3": { label: 'الوحدة', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "4": { label: 'الكمية', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "5": { label: 'السعر', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "6": { label: 'الإجمالي', visible: true, width: '', color: '#065f46', bg: '#d1fae5', align: 'center', fontWeight: 'bold', fontFamily: '' }
            }
        },
        {
            id: 1004,
            name: 'نسخة المراجعة والتدقيق',
            copyLabel: 'نسخة المراجعة الداخلية',
            tableTheme: 'theme-luxury-black',
            layoutTheme: 'layout-compact',
            numFont: 'font-firacode',
            columnOrder: [1, 2, 3, 4, 5, 6],
            tableWidth: 100,
            columns: {
                "1": { label: 'م (رقم)', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "2": { label: 'الصنف / البيان', visible: true, width: '40%', color: '', bg: '', align: 'right', fontWeight: 'normal', fontFamily: '' },
                "3": { label: 'الوحدة', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "4": { label: 'الكمية', visible: true, width: '', color: '', bg: '', align: 'center', fontWeight: 'normal', fontFamily: '' },
                "5": { label: 'السعر', visible: true, width: '', color: '#dc2626', bg: '', align: 'center', fontWeight: 'bold', fontFamily: '' },
                "6": { label: 'الإجمالي', visible: true, width: '', color: '#dc2626', bg: '', align: 'center', fontWeight: 'bold', fontFamily: '' }
            }
        }
    ];

    let updated = false;
    defaultPresets.forEach(dp => {
        if (!dsPresets.find(p => p.id === dp.id)) {
            dsPresets.push(dp);
            updated = true;
        }
    });

    if (updated) {
        dsPresets.sort((a, b) => a.id - b.id);
        localStorage.setItem('erp_ds_print_presets', JSON.stringify(dsPresets));
    }

    // 3. Render initial HTML
    renderInvoice();
    
    // 4. Initialize studio
    initColumnsDropdown();
    updateHiddenColsList();
    renderPresetsDropdown();
    renderPresetsManagementList();
});

function renderInvoice() {
    if (window.InvoicePrinter && window.InvoicePrinter.buildSingleCopy) {
        const html = window.InvoicePrinter.buildSingleCopy(invoiceData, currentCopyLabel);
        document.getElementById('studio-live-preview-container').innerHTML = html;
        // Apply column order if non-default
        if (dsColumnOrder && dsColumnOrder.join(',') !== '1,2,3,4,5,6') {
            reorderTableColumns();
        }
        applyCustomLabels();
        applyDynamicGridCSS();
        applyTableTheme(currentTableTheme);
        applyLayoutTheme(currentLayoutTheme);
        applyNumberFont(currentNumFont);
        if (currentTableWidth && currentTableWidth !== 100) {
            setTableWidth(currentTableWidth);
        }
    }
}

function setCopyLabel(val) {
    currentCopyLabel = val || 'نسخة العميل';
    const labelEl = document.getElementById('studio-copy-label-text');
    if (labelEl) labelEl.textContent = '📄 ' + currentCopyLabel;
}

function toggleSettingsPanel() {
    const p = document.getElementById('settings-panel');
    const icon = document.getElementById('ribbon-toggle-icon');
    if (!p) return;
    
    const isOpening = (p.style.display === 'none' || p.style.display === '');
    if (isOpening) {
        p.classList.add('show');
        p.style.display = 'flex';
        if (icon) icon.innerText = '▲';
        if (dsSelectedColIndex === null) {
            selectColumn(5); // Default to Price column
        }
    } else {
        p.classList.remove('show');
        p.style.display = 'none';
        if (icon) icon.innerText = '▼';
    }
}

function initColumnsDropdown() {
    const sel = document.getElementById('selected-col-select');
    if (!sel) return;
    
    sel.innerHTML = '<option value="">-- اختر عموداً --</option>';
    PRINT_COLUMNS.forEach(c => {
        if (!dsColConfigs[c.index]) {
            dsColConfigs[c.index] = { 
                label: c.label, 
                visible: true, 
                width: '', 
                color: '', 
                bg: '', 
                align: 'center', 
                fontWeight: 'normal',
                fontFamily: ''
            };
        }
        sel.innerHTML += `<option value="${c.index}">${dsColConfigs[c.index].label}</option>`;
    });
}

function selectColumn(idx) {
    dsSelectedColIndex = idx;
    const sel = document.getElementById('selected-col-select');
    if (sel) sel.value = idx;
    
    const conf = dsColConfigs[idx];
    if (!conf) return;

    document.getElementById('col-visible-chk').checked = conf.visible !== false;
    document.getElementById('col-label-input').value = conf.label || '';
    document.getElementById('col-align-select').value = conf.align || 'center';
    
    // Width: extract numeric value (strip % or px)
    const widthVal = conf.width ? parseInt(conf.width) : '';
    const widthInput = document.getElementById('col-width-input');
    const widthRange = document.getElementById('col-width-range');
    if (widthInput) widthInput.value = widthVal || '';
    if (widthRange) widthRange.value = widthVal || 15;
    
    document.getElementById('col-color-input').value = conf.color || '#000000';
    document.getElementById('col-bg-input').value = conf.bg || '#ffffff';
    
    if (document.getElementById('col-fw-select')) document.getElementById('col-fw-select').value = conf.fontWeight || 'normal';
    if (document.getElementById('col-font-select')) document.getElementById('col-font-select').value = conf.fontFamily || '';
    
    // Highlight selected column visually
    highlightSelectedColumn(idx);
}

function updateSelectedCol(prop, val) {
    if (!dsSelectedColIndex || !dsColConfigs[dsSelectedColIndex]) return;
    const conf = dsColConfigs[dsSelectedColIndex];

    if (prop === 'visible') {
        conf.visible = val;
        updateHiddenColsList();
    }
    if (prop === 'label') conf.label = val;
    if (prop === 'align') conf.align = val;
    if (prop === 'width') conf.width = val ? val + '%' : '';
    if (prop === 'color') conf.color = val;
    if (prop === 'bg') conf.bg = val;
    if (prop === 'fontWeight') conf.fontWeight = val;
    if (prop === 'fontFamily') conf.fontFamily = val;

    applyDynamicGridCSS();
    
    if (prop === 'label') {
        applyCustomLabels();
        initColumnsDropdown();
    }
}

function applyCustomLabels() {
    const tables = document.querySelectorAll('.inv-table.main-table');
    tables.forEach(table => {
        Object.keys(dsColConfigs).forEach(idx => {
            const conf = dsColConfigs[idx];
            if (conf.label) {
                const th = table.querySelector(`th:nth-child(${idx})`);
                if (th) th.innerText = conf.label;
            }
        });
    });
}

function applyDynamicGridCSS() {
    let css = '';
    
    Object.keys(dsColConfigs).forEach(idx => {
        const conf = dsColConfigs[idx];
        const selector = `.inv-table.main-table th:nth-child(${idx}), .inv-table.main-table td:nth-child(${idx})`;
        
        if (conf.visible === false) {
            css += `${selector} { display: none !important; }\n`;
        } else {
            if (conf.width) css += `${selector} { width: ${conf.width} !important; max-width: ${conf.width} !important; }\n`;
            if (conf.color) css += `${selector} { color: ${conf.color} !important; }\n`;
            if (conf.bg && conf.bg !== '#ffffff') css += `${selector} { background-color: ${conf.bg} !important; }\n`;
            if (conf.align) css += `${selector} { text-align: ${conf.align} !important; }\n`;
            if (conf.fontWeight) css += `${selector} { font-weight: ${conf.fontWeight} !important; }\n`;
            if (conf.fontFamily) css += `${selector} { font-family: ${conf.fontFamily} !important; }\n`;
        }
    });

    styleEl.innerHTML = css;
}

// ── Column Reordering ──
function moveSelectedCol(direction) {
    if (!dsSelectedColIndex) return;
    const idx = dsColumnOrder.indexOf(parseInt(dsSelectedColIndex));
    if (idx === -1) return;
    
    const newIdx = idx + direction;
    if (newIdx < 0 || newIdx >= dsColumnOrder.length) return;
    
    // Swap in order array
    [dsColumnOrder[idx], dsColumnOrder[newIdx]] = [dsColumnOrder[newIdx], dsColumnOrder[idx]];
    
    // Reorder actual DOM columns
    reorderTableColumns();
    applyCustomLabels();
    applyDynamicGridCSS();
    highlightSelectedColumn(dsSelectedColIndex);
}

function reorderTableColumns() {
    const tables = document.querySelectorAll('.inv-table.main-table');
    tables.forEach(table => {
        // Reorder thead
        const headerRow = table.querySelector('thead tr');
        if (headerRow) {
            const ths = Array.from(headerRow.children);
            const orderedThs = dsColumnOrder.map(colIdx => ths[colIdx - 1]).filter(Boolean);
            orderedThs.forEach(th => headerRow.appendChild(th));
        }
        // Reorder tbody rows
        const bodyRows = table.querySelectorAll('tbody tr');
        bodyRows.forEach(row => {
            const tds = Array.from(row.children);
            if (tds.length === dsColumnOrder.length) {
                const orderedTds = dsColumnOrder.map(colIdx => tds[colIdx - 1]).filter(Boolean);
                orderedTds.forEach(td => row.appendChild(td));
            }
        });
    });
}

// ── Hidden Columns Management ──
function updateHiddenColsList() {
    const sel = document.getElementById('restore-hidden-col-select');
    if (!sel) return;
    
    const hidden = [];
    Object.keys(dsColConfigs).forEach(idx => {
        if (dsColConfigs[idx].visible === false) {
            hidden.push({ idx: idx, label: dsColConfigs[idx].label });
        }
    });
    
    sel.innerHTML = `<option value="">👁️ الأعمدة المخفية (${hidden.length})</option>`;
    hidden.forEach(h => {
        sel.innerHTML += `<option value="${h.idx}">🔴 ${h.label} — اضغط للإظهار</option>`;
    });
    
    sel.style.background = hidden.length > 0 ? '#fef3c7' : '#f0fdf4';
    sel.style.borderColor = hidden.length > 0 ? '#f59e0b' : '#22c55e';
}

function restoreHiddenColumn(idxStr) {
    if (!idxStr) return;
    const idx = parseInt(idxStr);
    if (dsColConfigs[idx]) {
        dsColConfigs[idx].visible = true;
        applyDynamicGridCSS();
        updateHiddenColsList();
        selectColumn(idx);
    }
    // Reset dropdown
    const sel = document.getElementById('restore-hidden-col-select');
    if (sel) sel.value = '';
}

// ── Table Width Control ──
function setTableWidth(percent) {
    currentTableWidth = parseInt(percent);
    const tables = document.querySelectorAll('.inv-table.main-table');
    tables.forEach(t => {
        t.style.width = currentTableWidth + '%';
        t.style.margin = currentTableWidth < 100 ? '0 auto 18px auto' : '';
    });
    const label = document.getElementById('table-width-label');
    if (label) label.textContent = currentTableWidth + '%';
}

// ── Column Highlight ──
function highlightSelectedColumn(idx) {
    // Remove old highlights
    document.querySelectorAll('.inv-table.main-table .col-highlight').forEach(el => {
        el.classList.remove('col-highlight');
    });
    if (!idx) return;
    // Add highlight to selected column
    document.querySelectorAll('.inv-table.main-table').forEach(table => {
        const cells = table.querySelectorAll(`th:nth-child(${idx}), td:nth-child(${idx})`);
        cells.forEach(c => c.classList.add('col-highlight'));
    });
}

function switchStudioTab(tab) {
    document.querySelectorAll('.studio-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-btn-' + tab)?.classList.add('active');
    
    document.getElementById('studio-tab-cols').style.display = 'none';
    document.getElementById('studio-tab-paper').style.display = 'none';
    document.getElementById('studio-tab-presets').style.display = 'none';
    
    document.getElementById('studio-tab-' + tab).style.display = 'block';
}

function executePrintWithPermission() {
    window.print();
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}

// ── Global Themes & Fonts ──
function setTableTheme(theme) {
    currentTableTheme = theme;
    applyTableTheme(theme);
}
function applyTableTheme(theme) {
    const tables = document.querySelectorAll('.inv-table');
    tables.forEach(table => {
        // Preserve main-table and wh-table classes
        const isMain = table.classList.contains('main-table');
        const isWh = table.classList.contains('wh-table');
        table.className = 'inv-table';
        if (isMain) table.classList.add('main-table');
        if (isWh) table.classList.add('wh-table');
        if (theme) table.classList.add(theme);
        if (currentNumFont) table.classList.add(currentNumFont);
    });
}
function setLayoutTheme(theme) {
    currentLayoutTheme = theme;
    applyLayoutTheme(theme);
}
function applyLayoutTheme(theme) {
    const docs = document.querySelectorAll('.inv-doc');
    docs.forEach(doc => {
        doc.classList.remove('layout-modern', 'layout-split', 'layout-compact');
        if (theme) doc.classList.add(theme);
    });
}
function setNumberFont(fontClass) {
    currentNumFont = fontClass;
    applyNumberFont(fontClass);
}
function applyNumberFont(fontClass) {
    const tables = document.querySelectorAll('.inv-table');
    tables.forEach(table => {
        table.classList.forEach(c => {
            if (c.startsWith('font-')) table.classList.remove(c);
        });
        if (fontClass) table.classList.add(fontClass);
    });
}

// ── Presets Management ──
function saveCurrentPreset() {
    let name = prompt("أدخل اسماً لهذا التصميم (مثال: تصميم الكاشير الأساسي):");
    if (!name) return;
    
    const preset = {
        id: Date.now(),
        name: name,
        copyLabel: currentCopyLabel,
        columns: JSON.parse(JSON.stringify(dsColConfigs)),
        columnOrder: [...dsColumnOrder],
        tableWidth: currentTableWidth,
        tableTheme: document.getElementById('table-theme-select')?.value || '',
        layoutTheme: document.getElementById('layout-theme-select')?.value || '',
        numFont: document.getElementById('num-font-select')?.value || 'font-ultra'
    };
    
    dsPresets.push(preset);
    localStorage.setItem('erp_ds_print_presets', JSON.stringify(dsPresets));
    
    renderPresetsDropdown();
    renderPresetsManagementList();
    
    // Highlight save button
    const saveBtn = document.querySelector('button[onclick="saveCurrentPreset()"]');
    if (saveBtn) {
        const oldText = saveBtn.innerText;
        saveBtn.innerText = "✅ تم الحفظ";
        saveBtn.style.background = "#059669";
        setTimeout(() => {
            saveBtn.innerText = oldText;
            saveBtn.style.background = "linear-gradient(135deg, #10b981, #059669)";
        }, 2000);
    }
}

function loadPreset(idStr) {
    if (!idStr) return;
    const id = parseInt(idStr);
    const p = dsPresets.find(x => x.id === id);
    if (!p) return;
    
    dsColConfigs = JSON.parse(JSON.stringify(p.columns));
    dsColumnOrder = p.columnOrder ? [...p.columnOrder] : [1,2,3,4,5,6];
    currentTableWidth = p.tableWidth || 100;
    currentCopyLabel = p.copyLabel || 'نسخة العميل';
    currentTableTheme = p.tableTheme || '';
    currentLayoutTheme = p.layoutTheme || '';
    currentNumFont = p.numFont || 'font-ultra';
    
    if (document.getElementById('table-theme-select')) document.getElementById('table-theme-select').value = currentTableTheme;
    if (document.getElementById('layout-theme-select')) document.getElementById('layout-theme-select').value = currentLayoutTheme;
    if (document.getElementById('num-font-select')) document.getElementById('num-font-select').value = currentNumFont;
    if (document.getElementById('copy-label-input')) document.getElementById('copy-label-input').value = currentCopyLabel;
    if (document.getElementById('table-width-range')) document.getElementById('table-width-range').value = currentTableWidth;
    
    // Re-render invoice from scratch to apply column order
    renderInvoice();
    setTableWidth(currentTableWidth);
    updateHiddenColsList();
    
    if (dsSelectedColIndex) selectColumn(dsSelectedColIndex);
}

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
    const list = document.getElementById('presets-management-list');
    if (!list) return;
    list.innerHTML = '';
    if (dsPresets.length === 0) {
        list.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8;">لا توجد تخصيصات محفوظة</div>';
        return;
    }
    
    dsPresets.forEach((p, idx) => {
        const d = document.createElement('div');
        d.className = 'preset-card';
        const visibleCols = Object.values(p.columns || {}).filter(c => c.visible !== false).length;
        const totalCols = Object.keys(p.columns || {}).length;
        const layoutName = {'': 'كلاسيكي', 'layout-modern': 'مودرن', 'layout-split': 'منفصل', 'layout-compact': 'مضغوط'}[p.layoutTheme || ''] || 'كلاسيكي';
        const tw = p.tableWidth || 100;
        d.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; color:#1e293b;">💎 ${p.name}</h4>
                <button class="ribbon-btn" onclick="applyPreset(${p.id})" style="padding:4px 14px; background:linear-gradient(135deg, #0284c7, #0369a1); color:#fff; font-size:0.85rem;">✨ تطبيق</button>
            </div>
            <div style="margin:8px 0 0; display:flex; flex-wrap:wrap; gap:6px;">
                <span style="font-size:0.75rem; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:10px; font-weight:700;">🏗️ ${layoutName}</span>
                <span style="font-size:0.75rem; background:#f0fdf4; color:#166534; padding:2px 8px; border-radius:10px; font-weight:700;">📊 ${visibleCols}/${totalCols} أعمدة</span>
                <span style="font-size:0.75rem; background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:10px; font-weight:700;">📐 عرض ${tw}%</span>
            </div>
            <div style="margin-top:10px; text-align:left;">
                <button class="ribbon-btn" onclick="deletePreset(${p.id})" style="padding:4px 10px; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid #ef4444; font-size:0.8rem;">❌ حذف</button>
            </div>
        `;
        list.appendChild(d);
    });
}

function applyPreset(id) {
    loadPreset(id);
    switchStudioTab('cols');
}

function deletePreset(id) {
    if(!confirm('هل أنت متأكد من حذف هذا التخصيص؟')) return;
    dsPresets = dsPresets.filter(x => x.id !== id);
    localStorage.setItem('erp_ds_print_presets', JSON.stringify(dsPresets));
    renderPresetsDropdown();
    renderPresetsManagementList();
}

function addNewPresetPrompt() {
    switchStudioTab('cols');
    alert("قم بضبط التعديلات والألوان من هذه الشاشة، ثم اضغط على زر 'حفظ الإعدادات' في الأعلى.");
}
