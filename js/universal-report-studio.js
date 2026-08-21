// ═══════════════════════════════════════════════════════════════════
// universal-report-studio.js — Unified Report Studio Engine
// Works with studio-settings-panel.php on ALL report screens
// ═══════════════════════════════════════════════════════════════════

(function() {
    'use strict';

    const URS_VERSION = 'v2';
    let ursTable = null;
    let ursColumns = [];
    let ursColumnOrder = [];
    let ursColumnSettings = {};
    let ursSelectedColIdx = null;
    let ursPresets = [];
    let ursCurrentPreset = 0;
    let ursStorageKey = '';
    let ursLongPressTimer = null;
    let ursLongPressStart = 0;

    // ── Initialization ──
    function init() {
        ursTable = document.querySelector('.dash-table') ||
                   document.querySelector('.excel-table') ||
                   document.querySelector('table.report-table') ||
                   document.querySelector('#report-table-wrap table') ||
                   document.querySelector('table');
        if (!ursTable) return;

        // Derive storage key from page path
        const pageName = location.pathname.split('/').pop().replace('.php', '');
        ursStorageKey = 'urs_presets_' + pageName + '_' + URS_VERSION;

        discoverColumns();
        loadPresets();
        applyPreset(ursCurrentPreset);
        setupLongPressOnPresets();
    }

    // ── Column Discovery ──
    function discoverColumns() {
        ursColumns = [];
        ursColumnOrder = [];
        const thead = ursTable.querySelector('thead');
        if (!thead) return;
        const headerRow = thead.querySelector('tr:last-child') || thead.querySelector('tr');
        if (!headerRow) return;
        const ths = headerRow.querySelectorAll('th');
        ths.forEach(function(th, idx) {
            ursColumns.push({
                idx: idx,
                defaultLabel: th.textContent.trim(),
                label: th.textContent.trim(),
                visible: true,
                align: 'center',
                width: '',
                fs: 0.9,
                fontWeight: '800',
                fontFamily: 'inherit',
                color: '',
                bgColor: '',
                thousandSep: ',',
                decimals: 2,
                showDecSep: true,
                rounding: 'nearest',
                isNum: false
            });
            ursColumnOrder.push(idx);
        });
        // Detect numeric columns
        const firstRow = ursTable.querySelector('tbody tr');
        if (firstRow) {
            const tds = firstRow.querySelectorAll('td');
            tds.forEach(function(td, idx) {
                if (idx < ursColumns.length) {
                    const txt = td.textContent.trim().replace(/[,،.\s]/g, '');
                    if (txt && !isNaN(txt)) {
                        ursColumns[idx].isNum = true;
                    }
                }
            });
        }
    }

    // ── Presets Management ──
    function loadPresets() {
        try {
            const raw = localStorage.getItem(ursStorageKey);
            if (raw) {
                ursPresets = JSON.parse(raw);
            }
        } catch(e) {}
        if (!ursPresets || ursPresets.length === 0) {
            ursPresets = [
                { name: '\u0627\u0641\u062a\u0631\u0627\u0636\u064a 1', settings: null },
                { name: '\u062a\u062e\u0635\u064a\u0635 2', settings: null },
                { name: '\u062a\u062e\u0635\u064a\u0635 3', settings: null }
            ];
        }
    }

    function savePresets() {
        try {
            localStorage.setItem(ursStorageKey, JSON.stringify(ursPresets));
        } catch(e) {}
    }

    function applyPreset(index) {
        ursCurrentPreset = index;
        const preset = ursPresets[index];
        if (preset && preset.settings) {
            // Apply column settings
            if (preset.settings.columnSettings) {
                ursColumnSettings = JSON.parse(JSON.stringify(preset.settings.columnSettings));
                ursColumns.forEach(function(col) {
                    if (ursColumnSettings[col.idx]) {
                        Object.assign(col, ursColumnSettings[col.idx]);
                    }
                });
            }
            if (preset.settings.columnOrder) {
                ursColumnOrder = preset.settings.columnOrder.slice();
            }
        }
        applyColumnVisibility();
        updatePresetButtons();
        populateStudioControls();
    }

    function saveCurrentToPreset(index) {
        if (!ursPresets[index]) return;
        const settings = {
            columnSettings: {},
            columnOrder: ursColumnOrder.slice()
        };
        ursColumns.forEach(function(col) {
            settings.columnSettings[col.idx] = {
                label: col.label,
                visible: col.visible,
                align: col.align,
                width: col.width,
                fs: col.fs,
                fontWeight: col.fontWeight,
                fontFamily: col.fontFamily,
                color: col.color,
                bgColor: col.bgColor
            };
        });
        ursPresets[index].settings = settings;
        savePresets();
        showToast('\u2705 \u062a\u0645 \u062d\u0641\u0638 \u0627\u0644\u062a\u0635\u0645\u064a\u0645 \u0641\u064a \u0627\u0644\u0642\u0627\u0644\u0628 ' + (index + 1));
    }

    // ── Apply Column Visibility & Styles to Table ──
    function applyColumnVisibility() {
        if (!ursTable) return;
        const allRows = ursTable.querySelectorAll('tr');
        ursColumns.forEach(function(col) {
            allRows.forEach(function(row) {
                const cells = row.querySelectorAll('th, td');
                if (cells[col.idx]) {
                    cells[col.idx].style.display = col.visible ? '' : 'none';
                    if (col.visible) {
                        if (col.align) cells[col.idx].style.textAlign = col.align;
                        if (col.width) cells[col.idx].style.width = col.width + '%';
                        if (col.fs) cells[col.idx].style.fontSize = col.fs + 'rem';
                        if (col.fontWeight) cells[col.idx].style.fontWeight = col.fontWeight;
                        if (col.fontFamily && col.fontFamily !== 'inherit') cells[col.idx].style.fontFamily = col.fontFamily;
                        if (col.color) cells[col.idx].style.color = col.color;
                        if (col.bgColor && col.bgColor !== '#ffffff') cells[col.idx].style.backgroundColor = col.bgColor;
                    }
                }
            });
            // Update header label
            const thead = ursTable.querySelector('thead');
            if (thead) {
                const headerRow = thead.querySelector('tr:last-child') || thead.querySelector('tr');
                if (headerRow) {
                    const ths = headerRow.querySelectorAll('th');
                    if (ths[col.idx] && col.label) {
                        ths[col.idx].textContent = col.label;
                    }
                }
            }
        });
    }

    // ── Update Preset Buttons UI ──
    function updatePresetButtons() {
        document.querySelectorAll('.preset-pill-btn').forEach(function(btn) {
            const idx = parseInt(btn.getAttribute('data-preset'));
            btn.classList.toggle('active', idx === ursCurrentPreset);
        });
    }

    // ── Long Press on Preset Buttons (8 seconds to save) ──
    function setupLongPressOnPresets() {
        document.querySelectorAll('.preset-pill-btn').forEach(function(btn) {
            const idx = parseInt(btn.getAttribute('data-preset'));
            let pressTimer = null;
            let pressInterval = null;
            let elapsed = 0;

            btn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                elapsed = 0;
                btn.classList.add('is-pressing');
                showHUD(idx);
                pressInterval = setInterval(function() {
                    elapsed += 100;
                    updateHUDProgress(elapsed / 8000 * 100, (8 - elapsed/1000).toFixed(1));
                    if (elapsed >= 8000) {
                        clearInterval(pressInterval);
                        pressInterval = null;
                        btn.classList.remove('is-pressing');
                        hideHUD();
                        saveCurrentToPreset(idx);
                    }
                }, 100);
            });

            function cancelPress() {
                if (pressInterval) {
                    clearInterval(pressInterval);
                    pressInterval = null;
                }
                btn.classList.remove('is-pressing');
                hideHUD();
            }

            btn.addEventListener('mouseup', cancelPress);
            btn.addEventListener('mouseleave', cancelPress);
            btn.addEventListener('touchend', cancelPress);
            btn.addEventListener('touchcancel', cancelPress);

            btn.addEventListener('touchstart', function(e) {
                elapsed = 0;
                btn.classList.add('is-pressing');
                showHUD(idx);
                pressInterval = setInterval(function() {
                    elapsed += 100;
                    updateHUDProgress(elapsed / 8000 * 100, (8 - elapsed/1000).toFixed(1));
                    if (elapsed >= 8000) {
                        clearInterval(pressInterval);
                        pressInterval = null;
                        btn.classList.remove('is-pressing');
                        hideHUD();
                        saveCurrentToPreset(idx);
                    }
                }, 100);
            }, {passive: true});
        });
    }

    function showHUD(idx) {
        var hud = document.getElementById('long-press-hud');
        if (hud) { hud.style.display = 'block'; }
    }
    function hideHUD() {
        var hud = document.getElementById('long-press-hud');
        if (hud) { hud.style.display = 'none'; }
        var bar = document.getElementById('hud-progress-bar');
        if (bar) bar.style.width = '0%';
    }
    function updateHUDProgress(pct, remaining) {
        var bar = document.getElementById('hud-progress-bar');
        if (bar) bar.style.width = Math.min(pct, 100) + '%';
        var timer = document.getElementById('hud-timer');
        if (timer) timer.textContent = remaining + ' \u062b\u0627\u0646\u064a\u0629 \u0645\u062a\u0628\u0642\u064a\u0629';
    }

    function showToast(msg) {
        var container = document.getElementById('studio-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'studio-toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.style.cssText = 'pointer-events:auto;min-width:250px;max-width:350px;direction:rtl;font-family:Tajawal,sans-serif;color:#fff;padding:12px 24px;border-radius:12px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.3);font-weight:800;font-size:0.95rem;opacity:0;transform:translateY(-20px) scale(0.95);transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);background:linear-gradient(135deg,#10b981,#059669);border:1px solid #34d399;';
        toast.textContent = msg;
        container.appendChild(toast);
        requestAnimationFrame(function() {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0) scale(1)';
        });
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px) scale(0.95)';
            setTimeout(function() { toast.remove(); }, 400);
        }, 3000);
    }

    // ── Populate Studio Settings Panel Controls ──
    function populateStudioControls() {
        var colSelect = document.getElementById('selected-col-select');
        if (!colSelect) return;
        colSelect.innerHTML = '';
        ursColumns.forEach(function(col) {
            var opt = document.createElement('option');
            opt.value = col.idx;
            opt.textContent = col.label || col.defaultLabel;
            if (!col.visible) opt.textContent = '\u274c ' + opt.textContent;
            colSelect.appendChild(opt);
        });

        // Column chips
        var chipsContainer = document.getElementById('studio-columns-chips-container');
        if (chipsContainer) {
            chipsContainer.innerHTML = '';
            ursColumns.forEach(function(col) {
                var chip = document.createElement('label');
                chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:8px;font-size:0.78rem;font-weight:800;cursor:pointer;transition:all 0.2s;border:1px solid ' + (col.visible ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.3)') + ';background:' + (col.visible ? 'rgba(16,185,129,0.08)' : 'rgba(239,68,68,0.06)') + ';color:' + (col.visible ? '#10b981' : '#ef4444') + ';';
                var cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.checked = col.visible;
                cb.style.cssText = 'cursor:pointer;';
                cb.addEventListener('change', function() {
                    col.visible = this.checked;
                    applyColumnVisibility();
                    populateStudioControls();
                });
                chip.appendChild(cb);
                chip.appendChild(document.createTextNode(col.label || col.defaultLabel));
                chipsContainer.appendChild(chip);
            });
        }

        // Hidden columns dropdown
        var hiddenSelect = document.getElementById('restore-hidden-col-select');
        if (hiddenSelect) {
            var hiddenCount = ursColumns.filter(function(c) { return !c.visible; }).length;
            hiddenSelect.innerHTML = '<option value="">\ud83d\udc41\ufe0f \u0627\u0644\u0645\u062e\u0641\u064a (' + hiddenCount + ')</option>';
            ursColumns.forEach(function(col) {
                if (!col.visible) {
                    var opt = document.createElement('option');
                    opt.value = col.idx;
                    opt.textContent = col.label || col.defaultLabel;
                    hiddenSelect.appendChild(opt);
                }
            });
        }

        if (ursSelectedColIdx !== null) {
            selectColumnInternal(ursSelectedColIdx);
        } else if (ursColumns.length > 0) {
            selectColumnInternal(0);
        }
    }

    function selectColumnInternal(idx) {
        ursSelectedColIdx = idx;
        var col = ursColumns.find(function(c) { return c.idx === idx; });
        if (!col) return;

        var el;
        el = document.getElementById('selected-col-select'); if (el) el.value = idx;
        el = document.getElementById('col-visible-chk'); if (el) el.checked = col.visible;
        el = document.getElementById('col-label-input'); if (el) el.value = col.label || col.defaultLabel;
        el = document.getElementById('col-align-select'); if (el) el.value = col.align || 'center';
        el = document.getElementById('col-width-input'); if (el) el.value = col.width || '';
        el = document.getElementById('col-width-range'); if (el) el.value = col.width || 15;
        el = document.getElementById('col-fs-range'); if (el) el.value = col.fs || 0.9;
        el = document.getElementById('col-fs-val'); if (el) el.textContent = (col.fs || 0.9) + 'rem';
        el = document.getElementById('col-fw-select'); if (el) el.value = col.fontWeight || '800';
        el = document.getElementById('col-font-select'); if (el) el.value = col.fontFamily || 'inherit';
        el = document.getElementById('col-color-picker'); if (el) el.value = col.color || '#000000';
        el = document.getElementById('col-bg-picker'); if (el) el.value = col.bgColor || '#ffffff';

        // Highlight selected column in table
        if (ursTable) {
            ursTable.querySelectorAll('th').forEach(function(th) { th.classList.remove('selected-col'); });
            var thead = ursTable.querySelector('thead');
            if (thead) {
                var headerRow = thead.querySelector('tr:last-child') || thead.querySelector('tr');
                if (headerRow) {
                    var ths = headerRow.querySelectorAll('th');
                    if (ths[idx]) ths[idx].classList.add('selected-col');
                }
            }
        }
    }

    function updateColumnProperty(prop, value) {
        if (ursSelectedColIdx === null) return;
        var col = ursColumns.find(function(c) { return c.idx === ursSelectedColIdx; });
        if (!col) return;

        if (prop === 'visible') col.visible = value;
        else if (prop === 'label') col.label = value;
        else if (prop === 'align') col.align = value;
        else if (prop === 'width') col.width = value;
        else if (prop === 'fs') {
            col.fs = value;
            var fsVal = document.getElementById('col-fs-val');
            if (fsVal) fsVal.textContent = value + 'rem';
        }
        else if (prop === 'fontWeight') col.fontWeight = value;
        else if (prop === 'fontFamily') col.fontFamily = value;
        else if (prop === 'color') col.color = value;
        else if (prop === 'bgColor') col.bgColor = value;
        else if (prop === 'thousandSep') col.thousandSep = value;
        else if (prop === 'decimals') col.decimals = value;
        else if (prop === 'showDecSep') col.showDecSep = value;
        else if (prop === 'rounding') col.rounding = value;

        applyColumnVisibility();
        if (prop === 'visible' || prop === 'label') populateStudioControls();
    }

    // ── Navigate to Standalone Preview Page ──
    function openStudioPreviewPage() {
        try {
            var pageName = location.pathname.split('/').pop().replace('.php', '');
            var table = document.querySelector('#report-table-wrap table') ||
                        document.querySelector('.dash-table') ||
                        document.querySelector('.excel-table') ||
                        document.querySelector('table.report-table') ||
                        document.querySelector('.table-wrapper table');
            if (table) {
                var tableClone = table.cloneNode(true);
                tableClone.querySelectorAll('input, select, button, .btn-action, .btn-link').forEach(function(el) {
                    if (el.tagName === 'INPUT' || el.tagName === 'SELECT') {
                        var span = document.createElement('span');
                        span.textContent = el.value || el.textContent || '';
                        el.replaceWith(span);
                    } else {
                        el.remove();
                    }
                });
                sessionStorage.setItem('urs_preview_table_html', tableClone.outerHTML);
            }
            sessionStorage.setItem('urs_preview_source', pageName);
            var titleEl = document.querySelector('.content-title h2') || document.querySelector('h2') || document.querySelector('h3');
            sessionStorage.setItem('urs_preview_title', titleEl ? titleEl.textContent.trim() : document.title);
            window.open('preview-inventory-report.php?source=' + pageName, '_blank');
        } catch(e) {
            console.error('openStudioPreviewPage error:', e);
            window.open('preview-inventory-report.php', '_blank');
        }
    }

    // ── Toggle Settings Panel ──
    function togglePanel() {
        var panel = document.getElementById('settings-panel');
        if (!panel) return;
        panel.classList.toggle('show');
        if (panel.classList.contains('show')) {
            populateStudioControls();
        }
    }

    // ── Tab Switching (studio-settings-panel.php tabs) ──
    function switchTab(tabName) {
        ['cols', 'paper', 'presets'].forEach(function(t) {
            var tabEl = document.getElementById('studio-tab-' + t);
            var btnEl = document.getElementById('tab-btn-' + t);
            if (tabEl) tabEl.style.display = (t === tabName) ? 'block' : 'none';
            if (btnEl) btnEl.classList.toggle('active', t === tabName);
        });
    }

    // ── Table Theme ──
    function setTheme(theme) {
        if (!ursTable) return;
        ursTable.className = ursTable.className.replace(/theme-\S+/g, '').trim();
        if (theme) ursTable.classList.add(theme);
    }

    // ── Table Width ──
    function setWidth(val) {
        if (!ursTable) return;
        ursTable.style.width = val ? val + '%' : '100%';
        var label = document.getElementById('table-width-label');
        if (label) label.textContent = (val || 100) + '%';
    }

    // ── Number Font ──
    function setNumFont(fontClass) {
        if (!ursTable) return;
        ['font-newsons','font-ribot','font-firacode','font-ultra','font-jakarta','font-roboto','font-outfit','font-tajawal','font-cairo'].forEach(function(cls) {
            ursTable.classList.remove(cls);
        });
        if (fontClass) ursTable.classList.add(fontClass);
    }

    // ── Set All Columns Visibility ──
    function setAllVisible(visible) {
        ursColumns.forEach(function(col) { col.visible = visible; });
        applyColumnVisibility();
        populateStudioControls();
    }

    // ── Reset Column Order ──
    function resetOrder() {
        ursColumns.forEach(function(col) {
            col.label = col.defaultLabel;
            col.visible = true;
            col.align = 'center';
            col.width = '';
            col.fs = 0.9;
            col.fontWeight = '800';
            col.fontFamily = 'inherit';
            col.color = '';
            col.bgColor = '';
        });
        applyColumnVisibility();
        populateStudioControls();
        showToast('\ud83d\udd04 \u062a\u0645 \u0625\u0639\u0627\u062f\u0629 \u0627\u0644\u0636\u0628\u0637 \u0627\u0644\u0627\u0641\u062a\u0631\u0627\u0636\u064a');
    }

    // ── Restore Hidden Column ──
    function restoreHidden(val) {
        if (!val && val !== 0) return;
        var idx = parseInt(val);
        var col = ursColumns.find(function(c) { return c.idx === idx; });
        if (col) {
            col.visible = true;
            applyColumnVisibility();
            populateStudioControls();
        }
    }

    // ── Move Selected Column ──
    function moveCol(dir) {
        // Reordering not implemented for in-page tables (would require DOM manipulation)
        showToast('\u2139\ufe0f \u0625\u0639\u0627\u062f\u0629 \u0627\u0644\u062a\u0631\u062a\u064a\u0628 \u0645\u062a\u0627\u062d\u0629 \u0641\u064a \u0635\u0641\u062d\u0629 \u0627\u0644\u0627\u0633\u062a\u0648\u062f\u064a\u0648 \u0627\u0644\u0645\u0646\u0641\u0635\u0644\u0629');
    }

    // ── Paper & Header (for inline preview & print engine) ──
    function setPaper(size) { if (typeof applyPaperSimulation === 'function') applyPaperSimulation(size, null, null); }
    function setOrientation(orient) { if (typeof applyPaperSimulation === 'function') applyPaperSimulation(null, orient, null); }
    function setMargins(margin) { if (typeof applyPaperSimulation === 'function') applyPaperSimulation(null, null, margin); }
    function toggleHeader(type, checked) {
        if (type === 'logo') {
            var el = document.getElementById('company-logo-block');
            if (el) el.style.display = checked ? '' : 'none';
        }
        if (type === 'info') {
            var el = document.getElementById('company-info-block');
            if (el) el.style.display = checked ? '' : 'none';
        }
    }
    function setLayout(layout) { /* Layout variant */ }

    // ── Filter Table Rows (Quick Search) ──
    function filterRows(query) {
        if (!ursTable) return;
        var q = (query || '').toLowerCase().trim();
        var rows = ursTable.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            if (!q) { row.style.display = ''; return; }
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    }

    // ── Save Current Preset (from studio panel save button) ──
    function saveCurrentPresetFromPanel() {
        saveCurrentToPreset(ursCurrentPreset);
    }

    // ── Add New Preset ──
    function addNewPreset() {
        if (ursPresets.length >= 7) {
            showToast('\u26a0\ufe0f \u0627\u0644\u062d\u062f \u0627\u0644\u0623\u0642\u0635\u0649 7 \u0642\u0648\u0627\u0644\u0628');
            return;
        }
        ursPresets.push({ name: '\u0642\u0627\u0644\u0628 ' + (ursPresets.length + 1), settings: null });
        savePresets();
        showToast('\u2795 \u062a\u0645 \u0625\u0636\u0627\u0641\u0629 \u0642\u0627\u0644\u0628 \u062c\u062f\u064a\u062f');
    }

    // ── Expose Global Functions ──
    // These are called by studio-settings-panel.php and the ribbon buttons
    window.toggleReportColumnStudio = togglePanel;
    window.toggleSettingsPanel = togglePanel;
    window.applyReportPreset = function(index, btn) { applyPreset(index); };
    window.switchStudioTab = switchTab;
    window.saveCurrentPreset = saveCurrentPresetFromPanel;
    window.selectColumn = selectColumnInternal;
    window.updateSelectedCol = updateColumnProperty;
    window.setAllColumnsVisibility = setAllVisible;
    window.resetDefaultColumnOrder = resetOrder;
    window.restoreHiddenColumn = restoreHidden;
    window.moveSelectedCol = moveCol;
    window.setTableWidth = setWidth;
    window.setTableTheme = setTheme;
    window.setLayoutTheme = setLayout;
    window.setNumberFont = setNumFont;
    window.toggleHeaderElement = toggleHeader;
    window.setPaperSize = setPaper;
    window.setPageOrientation = setOrientation;
    window.setPageMargins = setMargins;
    window.filterTableRows = filterRows;
    window.addNewPresetPrompt = addNewPreset;

    function resetPresetToFactory(idx) {
        // Reset to default column configs
        if (ursPresets[idx]) {
            ursPresets[idx].settings = null;
            savePresets();
            applyPreset(idx);
            showToast('🏭 تم استرجاع ضبط المصنع للقالب بنجاح');
        }
    }
    function resetAllPresetsToFactory() {
        if (!confirm('هل أنت متأكد من استرجاع كافة القوالب لضبط المصنع؟')) return;
        ursPresets = [
            { name: '💎 افتراضي 1', settings: null },
            { name: '⭐ افتراضي 2', settings: null },
            { name: '🎨 افتراضي 3', settings: null },
            { name: '🔥 افتراضي 4', settings: null }
        ];
        savePresets();
        applyPreset(0);
        showToast('🏭 تم استرجاع كافة القوالب لضبط المصنع');
    }
    window.resetPresetToFactory = resetPresetToFactory;
    window.resetAllPresetsToFactory = resetAllPresetsToFactory;

    window.openStudioPreviewPage = openStudioPreviewPage;

    // renderTable is expected by some screens
    window.renderTable = function() { applyColumnVisibility(); };

    // executePrintWithPermission (used by studio panel's print)
    window.executePrintWithPermission = function() { window.print(); };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();