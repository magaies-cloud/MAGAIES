alert("تم تفعيل خصائص الأعمدة بنجاح! إذا ظهرت لك هذه الرسالة يعني أن التحديثات وصلت للمتصفح.");
/**
 * Grid Customizer
 * Handles column resizing, context menus, and configuration saving to DB.
 */

window.CurrentGridConfig = {};

const GridCustomizer = {
    gridId: null,
    tableId: null,
    
    init: async function(gridId, tableId, onLoadedCallback) {
        this.gridId = gridId;
        this.tableId = tableId;
        
        // 1. Fetch config from DB
        try {
            const res = await fetch(`api/get_grid_config.php?grid_id=${gridId}&_t=${Date.now()}`);
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
            }
        } catch (e) {
            console.error("Failed to load grid config", e);
        }
        
        // Ensure table exists
        const table = document.getElementById(tableId);
        if (table) {
            table.classList.add('customizable-grid');
        }

        // 2. Inject context menu HTML and styles
        this.injectContextMenu();
        this.injectGlobalStyles();

        // 3. Setup dragging and context menus
        this.attachEvents();
        
        // 4. Apply styles initially
        this.applyStyles();

        // Call render to ensure formatters pick up config
        if (onLoadedCallback) onLoadedCallback();
        
        console.log("GridCustomizer Initialized for " + gridId);
    },

    saveConfig: async function() {
        try {
            const res = await fetch('api/save_grid_config.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ grid_id: this.gridId, config: window.CurrentGridConfig })
            });
            const data = await res.json();
            if (data.status === 'success') {
                showToast('تم حفظ إعدادات الجدول بنجاح!', 'success');
                document.getElementById('grid-context-menu').style.display = 'none';
            } else {
                showToast('خطأ أثناء حفظ الإعدادات', 'error');
            }
        } catch(e) {
            console.error("Failed to save config", e);
            showToast('خطأ في الاتصال بالخادم', 'error');
        }
    },

    attachEvents: function() {
        const table = document.getElementById(this.tableId);
        if (!table) return;

        const ths = table.querySelectorAll('thead th');
        ths.forEach((th, index) => {
            const nth = index + 1;
            let colId = th.getAttribute('data-col-id');
            if (!colId) {
                colId = 'col_' + index;
                th.setAttribute('data-col-id', colId);
            }
            th.setAttribute('data-nth', nth);
            th.style.position = 'relative';

            // Create resizer handle
            if (!th.querySelector('.col-resizer')) {
                const resizer = document.createElement('div');
                resizer.className = 'col-resizer';
                resizer.title = "اسحب لتعديل عرض العمود (كليك يسار واسحب)";
                th.appendChild(resizer);

                // Dragging logic
                let startX, startWidth;
                resizer.addEventListener('mousedown', (e) => {
                    if (e.button !== 0 && e.button !== 2) return; // Only allow left or right click to resize (to satisfy user's strange prompt)
                    e.preventDefault();
                    e.stopPropagation();
                    startX = e.clientX;
                    startWidth = th.offsetWidth;
                    
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';

                    const onMouseMove = (moveEvent) => {
                        const diff = moveEvent.clientX - startX;
                        let newWidth = startWidth - diff; // RTL
                        if (newWidth < 40) newWidth = 40;
                        
                        th.style.width = newWidth + 'px';
                        th.style.minWidth = newWidth + 'px';
                        
                        if (!window.CurrentGridConfig[colId]) window.CurrentGridConfig[colId] = {};
                        window.CurrentGridConfig[colId].width = newWidth + 'px';
                        
                        this.applyStyles();
                    };
                    
                    const onMouseUp = () => {
                        document.body.style.cursor = 'default';
                        document.body.style.userSelect = '';
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                    };
                    
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
                
                // Allow right click drag on resizer
                resizer.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            }

            // Context Menu Logic
            th.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                this.openContextMenu(e, colId, th.innerText.replace(/[\\n\\r]/g, '').trim());
            });
            
            // Also allow left click to open menu if right click doesn't work for some reason
            th.addEventListener('dblclick', (e) => {
                e.preventDefault();
                this.openContextMenu(e, colId, th.innerText.replace(/[\\n\\r]/g, '').trim());
            });
        });
    },

    injectGlobalStyles: function() {
        if (!document.getElementById('grid-customizer-global-styles')) {
            const style = document.createElement('style');
            style.id = 'grid-customizer-global-styles';
            style.innerHTML = `
                .col-resizer {
                    position: absolute;
                    left: -4px;
                    top: 0;
                    bottom: 0;
                    width: 8px;
                    cursor: col-resize;
                    z-index: 10;
                    background-color: transparent;
                    transition: background-color 0.2s;
                }
                .col-resizer:hover, .col-resizer:active {
                    background-color: rgba(56, 189, 248, 0.8);
                }
            `;
            document.head.appendChild(style);
        }
    },

    injectContextMenu: function() {
        if (document.getElementById('grid-context-menu')) return;
        
        const html = `
        <div id="grid-context-menu" style="display:none; position: fixed; background: #1e293b; border: 1px solid #38bdf8; border-radius: 8px; box-shadow: 0 15px 35px rgba(0,0,0,0.6); z-index: 999999; width: 300px; padding: 12px; color: #f8fafc; font-family: 'Tajawal', sans-serif; resize: both; overflow: auto; min-width: 250px; min-height: 300px;">
            <div id="gcm-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 8px; margin-bottom: 12px; cursor: move;">
                <div style="font-weight: bold; color: #38bdf8; font-size: 1.1rem; flex: 1; text-align: center;" id="gcm-title">خصائص العمود</div>
                <button onclick="document.getElementById('grid-context-menu').style.display='none'" style="background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer; padding: 0 5px;" title="إغلاق">&times;</button>
            </div>
            
            <input type="hidden" id="gcm-col-id">
            
            <div class="ctrl-group" style="margin-bottom: 10px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">الفاصلة الألفية</label>
                <select id="gcm-thousands" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 6px; border-radius: 4px; font-size: 0.95rem;">
                    <option value=",">ظهور الفاصلة الألفية (,)</option>
                    <option value="none">إخفاء الفاصلة</option>
                </select>
            </div>
            
            <div class="ctrl-group" style="margin-bottom: 10px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">الخانات العشرية</label>
                <input type="number" id="gcm-decimals" min="0" max="6" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 6px; border-radius: 4px; font-size: 0.95rem;">
            </div>
            
            <div class="ctrl-group" style="margin-bottom: 10px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">مقاس الخط</label>
                <input type="number" id="gcm-fontsize" min="10" max="32" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 6px; border-radius: 4px; font-size: 0.95rem;">
            </div>
            
            <div class="ctrl-group" style="margin-bottom: 10px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">لون الخط</label>
                <input type="color" id="gcm-color" style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 2px; border-radius: 4px; height: 35px; cursor: pointer;">
            </div>
            
            <div class="ctrl-group" style="margin-bottom: 10px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">لون الخلفية</label>
                <input type="color" id="gcm-bgcolor" style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 2px; border-radius: 4px; height: 35px; cursor: pointer;">
            </div>
            
            <div class="ctrl-group" style="margin-bottom: 10px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">سُمك الخط</label>
                <select id="gcm-fontweight" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 6px; border-radius: 4px; font-size: 0.95rem;">
                    <option value="">عادي (الافتراضي)</option>
                    <option value="bold">عريض (Bold)</option>
                </select>
            </div>
            
            <div class="ctrl-group" style="margin-bottom: 10px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">المحاذاة</label>
                <select id="gcm-align" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 6px; border-radius: 4px; font-size: 0.95rem;">
                    <option value="">(الافتراضي)</option>
                    <option value="right">يمين</option>
                    <option value="center">وسط</option>
                    <option value="left">يسار</option>
                </select>
            </div>
            
            <div class="ctrl-group" style="margin-bottom: 16px;">
                <label style="font-size: 0.9rem; color: #cbd5e1; display: block; margin-bottom: 4px;">نوع الخط</label>
                <select id="gcm-fontfamily" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 6px; border-radius: 4px; font-size: 0.95rem;">
                    <option value="">(الافتراضي)</option>
                    <option value="'JetBrains Mono', monospace">JetBrains Mono (الأوضح للأرقام الإنجليزية)</option>
                    <option value="'Consolas', monospace">Consolas (ممتاز للأرقام)</option>
                    <option value="'Segoe UI', sans-serif">Segoe UI (عصري وواضح)</option>
                    <option value="'Roboto', sans-serif">Roboto (حديث وناعم)</option>
                    <option value="'Tahoma', sans-serif">Tahoma (كلاسيكي ورسمي)</option>
                    <option value="'Arial', sans-serif">Arial (أساسي)</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <button onclick="GridCustomizer.applyContextMenu()" style="flex: 1; background: #38bdf8; color: #0f172a; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: background 0.2s;">تطبيق للتجربة</button>
                <button onclick="GridCustomizer.saveConfig()" style="flex: 1; background: #10b981; color: #0f172a; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: background 0.2s;">حفظ نهائياً</button>
            </div>
            <button onclick="document.getElementById('grid-context-menu').style.display='none'" style="width: 100%; background: #334155; color: #f8fafc; border: none; padding: 8px; border-radius: 4px; cursor: pointer; font-weight: bold;">إغلاق</button>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        // Make draggable
        const menu = document.getElementById('grid-context-menu');
        const header = document.getElementById('gcm-header');
        
        let isDragging = false;
        let dragStartX, dragStartY;
        let initialX, initialY;

        header.addEventListener('mousedown', (e) => {
            isDragging = true;
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            initialX = menu.offsetLeft;
            initialY = menu.offsetTop;
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', stopDrag);
        });

        function onDrag(e) {
            if (!isDragging) return;
            e.preventDefault();
            const dx = e.clientX - dragStartX;
            const dy = e.clientY - dragStartY;
            menu.style.left = (initialX + dx) + 'px';
            menu.style.top = (initialY + dy) + 'px';
        }

        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', onDrag);
            document.removeEventListener('mouseup', stopDrag);
        }
        
    },

    openContextMenu: function(e, colId, colName) {
        const menu = document.getElementById('grid-context-menu');
        document.getElementById('gcm-title').innerText = `خصائص العمود: ${colName}`;
        document.getElementById('gcm-col-id').value = colId;
        
        const cfg = window.CurrentGridConfig[colId] || {};
        document.getElementById('gcm-thousands').value = cfg.thousandSep !== undefined ? cfg.thousandSep : ',';
        document.getElementById('gcm-decimals').value = cfg.decimals !== undefined ? cfg.decimals : '';
        document.getElementById('gcm-fontsize').value = cfg.fontSize ? parseInt(cfg.fontSize) : '';
        document.getElementById('gcm-color').value = cfg.color || '#ffffff';
        document.getElementById('gcm-fontfamily').value = cfg.fontFamily || '';
        document.getElementById('gcm-bgcolor').value = cfg.bgColor || '#0f172a';
        document.getElementById('gcm-fontweight').value = cfg.fontWeight || '';
        document.getElementById('gcm-align').value = cfg.textAlign || '';
        
        // Position menu
        let x = e.clientX;
        let y = e.clientY;
        if (x + 300 > window.innerWidth) x = window.innerWidth - 300;
        if (y + 400 > window.innerHeight) y = window.innerHeight - 400;
        
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
        menu.style.display = 'block';
    },

    applyContextMenu: function() {
        const colId = document.getElementById('gcm-col-id').value;
        if (!window.CurrentGridConfig[colId]) window.CurrentGridConfig[colId] = {};
        
        window.CurrentGridConfig[colId].thousandSep = document.getElementById('gcm-thousands').value;
        window.CurrentGridConfig[colId].decimals = document.getElementById('gcm-decimals').value;
        window.CurrentGridConfig[colId].fontSize = document.getElementById('gcm-fontsize').value ? document.getElementById('gcm-fontsize').value + 'px' : '';
        window.CurrentGridConfig[colId].color = document.getElementById('gcm-color').value;
        window.CurrentGridConfig[colId].fontFamily = document.getElementById('gcm-fontfamily').value;
        window.CurrentGridConfig[colId].bgColor = document.getElementById('gcm-bgcolor').value;
        window.CurrentGridConfig[colId].fontWeight = document.getElementById('gcm-fontweight').value;
        window.CurrentGridConfig[colId].textAlign = document.getElementById('gcm-align').value;
        
        this.applyStyles();
        if (typeof window.recalcAll === 'function') window.recalcAll();
        if (typeof window.renderGrid === 'function') window.renderGrid();
    },

    applyStyles: function() {
        let styleTag = document.getElementById('grid-customizer-styles');
        if (!styleTag) {
            styleTag = document.createElement('style');
            styleTag.id = 'grid-customizer-styles';
            document.head.appendChild(styleTag);
        }
        
        let css = '';
        Object.keys(window.CurrentGridConfig).forEach(colId => {
            const cfg = window.CurrentGridConfig[colId];
            const th = document.querySelector(`th[data-col-id="${colId}"]`);
            if (th) {
                const nth = th.getAttribute('data-nth');
                if (nth) {
                    if (cfg.width) {
                        css += `#${this.tableId} th:nth-child(${nth}) { width: ${cfg.width} !important; min-width: ${cfg.width} !important; }\n`;
                    }
                    let tdCss = '';
                    if (cfg.fontSize && cfg.fontSize !== 'NaNpx' && cfg.fontSize !== 'px') tdCss += `font-size: ${cfg.fontSize} !important; `;
                    if (cfg.color && cfg.color !== '#ffffff') tdCss += `color: ${cfg.color} !important; `;
                    if (cfg.bgColor && cfg.bgColor !== '#0f172a') tdCss += `background-color: ${cfg.bgColor} !important; `;
                    if (cfg.fontFamily) tdCss += `font-family: ${cfg.fontFamily} !important; `;
                    if (cfg.fontWeight) tdCss += `font-weight: ${cfg.fontWeight} !important; `;
                    if (cfg.textAlign) tdCss += `text-align: ${cfg.textAlign} !important; `;
                    
                    if (tdCss) {
                        css += `#${this.tableId} tbody td:nth-child(${nth}) { ${tdCss} }\n`;
                        css += `#${this.tableId} tbody td:nth-child(${nth}) input, #${this.tableId} tbody td:nth-child(${nth}) span, #${this.tableId} tbody td:nth-child(${nth}) div, #${this.tableId} tbody td:nth-child(${nth}) select { ${tdCss} }\n`;
                    }
                }
            }
        });
        
        styleTag.innerHTML = css;
    }
};
