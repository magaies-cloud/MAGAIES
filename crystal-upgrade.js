const fs = require('fs');

function upgradeAppJs() {
    const appJsPath = 'c:/ELSAJANA/magaies/js/app.js';
    let appJs = fs.readFileSync(appJsPath, 'utf8');

    // Remove old contextmenu listener block
    const menuStart = appJs.indexOf("document.addEventListener('contextmenu', (e) => {");
    if (menuStart === -1) {
        console.log("Could not find contextmenu in app.js");
        return;
    }

    const funcStart = appJs.indexOf("function initSystemNumberFormatting() {");
    const funcEnd = appJs.indexOf("})();\\n\\n\\n// ==========================================\\n// GLOBAL PRINT");
    const alternateEnd = appJs.indexOf("})();\n\n\n// ==========================================\n// GLOBAL PRINT");
    const endPos = funcEnd !== -1 ? funcEnd : alternateEnd;
    
    if (funcStart !== -1 && endPos !== -1) {
        const newFunc = `
    function initSystemNumberFormatting() {
        let oldMenu = document.getElementById('sys-number-format-menu');
        if (oldMenu) oldMenu.remove();

        const menuHTML = \`
        <div id="sys-number-format-menu" style="display:none; position:absolute; z-index:999999; background:rgba(15,23,42,0.95); backdrop-filter:blur(16px); border:1px solid rgba(56,189,248,0.4); border-radius:12px; padding:15px; box-shadow:0 10px 30px rgba(0,0,0,0.5), inset 0 0 15px rgba(56,189,248,0.1); flex-direction:column; gap:12px; width:280px; direction:rtl; font-family:'Cairo', sans-serif;">
            <div style="font-weight:bold; color:#38bdf8; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:8px; margin-bottom:5px; display:flex; align-items:center; gap:8px;">
                <span>✨ خصائص الرقم المتقدمة</span>
            </div>
            <div id="sys-num-target-id" style="font-size:0.75rem; color:#94a3b8; word-break:break-all;"></div>
            
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size:0.8rem; color:#cbd5e1;">اللون:</label>
                <input type="color" id="sys-num-color" style="width:100%; height:35px; border:none; border-radius:6px; cursor:pointer; background:transparent;">
            </div>
            
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size:0.8rem; color:#cbd5e1;">حجم الخط (Size):</label>
                <div style="display:flex; gap:5px;">
                    <button id="sys-num-size-dec" style="flex:1; background:rgba(255,255,255,0.1); border:none; color:#fff; border-radius:6px; cursor:pointer;">-</button>
                    <input type="number" id="sys-num-size" style="flex:2; text-align:center; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px; outline:none;" readonly>
                    <button id="sys-num-size-inc" style="flex:1; background:rgba(255,255,255,0.1); border:none; color:#fff; border-radius:6px; cursor:pointer;">+</button>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size:0.8rem; color:#cbd5e1;">نوع الخط (Font):</label>
                <select id="sys-num-font" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px; padding:5px; outline:none; font-family:sans-serif;">
                    <option value="" style="color:#000">الافتراضي (Default)</option>
                    <option value="'Inter', sans-serif" style="color:#000">Inter (عصري وواضح)</option>
                    <option value="'Roboto Mono', monospace" style="color:#000">Roboto Mono (رقمي دقيق)</option>
                    <option value="'Impact', sans-serif" style="color:#000">Impact (بارز وعريض)</option>
                    <option value="'Orbitron', sans-serif" style="color:#000">Orbitron (إلكتروني كريستالي)</option>
                </select>
            </div>

            <div style="display:flex; gap:10px;">
                <div style="display:flex; flex-direction:column; gap:5px; flex:1;">
                    <label style="font-size:0.8rem; color:#cbd5e1;">الخانات العشرية:</label>
                    <input type="number" id="sys-num-decimals" min="0" max="6" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px; padding:5px; outline:none; text-align:center;">
                </div>
            </div>

            <div style="display:flex; gap:10px;">
                <div style="display:flex; flex-direction:column; gap:5px; flex:1;">
                    <label style="font-size:0.8rem; color:#cbd5e1;">فاصلة الآلاف:</label>
                    <select id="sys-num-thousands-sep" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px; padding:5px; outline:none;">
                        <option value="," style="color:#000">فاصلة ( , )</option>
                        <option value="." style="color:#000">نقطة ( . )</option>
                        <option value=" " style="color:#000">مسافة (   )</option>
                        <option value="none" style="color:#000">بدون</option>
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px; flex:1;">
                    <label style="font-size:0.8rem; color:#cbd5e1;">فاصلة عشرية:</label>
                    <select id="sys-num-decimal-sep" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px; padding:5px; outline:none;">
                        <option value="." style="color:#000">نقطة ( . )</option>
                        <option value="," style="color:#000">فاصلة ( , )</option>
                    </select>
                </div>
            </div>
            
            <button id="sys-num-close" style="margin-top:10px; background:linear-gradient(135deg, #0ea5e9, #0284c7); color:#fff; border:none; padding:8px; border-radius:6px; cursor:pointer; font-weight:bold;">✅ إغلاق وحفظ</button>
        </div>
        \`;
        
        const wrapper = document.createElement('div');
        wrapper.innerHTML = menuHTML;
        document.body.appendChild(wrapper.firstElementChild);

        const menu = document.getElementById('sys-number-format-menu');
        const cp = document.getElementById('sys-num-color');
        const si = document.getElementById('sys-num-size');
        const font = document.getElementById('sys-num-font');
        const sdDec = document.getElementById('sys-num-size-dec');
        const sdInc = document.getElementById('sys-num-size-inc');
        const ds = document.getElementById('sys-num-decimals');
        const ts = document.getElementById('sys-num-thousands-sep');
        const ds_sep = document.getElementById('sys-num-decimal-sep');
        const targetIdLabel = document.getElementById('sys-num-target-id');
        const closeBtn = document.getElementById('sys-num-close');

        function saveAndApply() {
            if (!window.activePrefKey) return;
            const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
            allPrefs[window.activePrefKey] = {
                color: cp.value,
                size: parseInt(si.value),
                font: font.value,
                decimals: parseInt(ds.value),
                thousandsSep: ts.value,
                decimalSep: ds_sep.value
            };
            localStorage.setItem('systemNumberPrefs', JSON.stringify(allPrefs));
            window.applySystemNumberFormatting();
        }

        cp.addEventListener('input', saveAndApply);
        font.addEventListener('change', saveAndApply);
        ds.addEventListener('change', saveAndApply);
        ts.addEventListener('change', saveAndApply);
        ds_sep.addEventListener('change', saveAndApply);
        
        sdDec.addEventListener('click', () => { si.value = Math.max(10, parseInt(si.value) - 1); saveAndApply(); });
        sdInc.addEventListener('click', () => { si.value = Math.min(64, parseInt(si.value) + 1); saveAndApply(); });
        
        closeBtn.addEventListener('click', () => {
            menu.style.display = 'none';
        });

        document.addEventListener('contextmenu', (e) => {
            const isNumeric = e.target.closest('input[type="number"], input[inputmode="numeric"], input[inputmode="decimal"], .amount, .sub-amount, .col-fc, .total-val, #total-amount, #exchange-rate, #amount-foreign, .g-readonly');
            if (isNumeric) {
                const key = getPrefKey(isNumeric);
                if (key) {
                    e.preventDefault();
                    window.activePrefKey = key;
                    window.activeElement = isNumeric;
                    
                    const selectorOnly = key.substring(getPageName().length + 1);
                    targetIdLabel.innerText = 'الهدف: ' + selectorOnly + ' (في هذه الشاشة فقط)';
                    
                    const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
                    const prefs = allPrefs[key] || { color: '#ffffff', size: parseInt(window.getComputedStyle(isNumeric).fontSize) || 16, font: '', decimals: 2, thousandsSep: ',', decimalSep: '.' };
                    
                    cp.value = prefs.color || '#ffffff';
                    si.value = prefs.size || 16;
                    font.value = prefs.font || '';
                    ds.value = prefs.decimals !== undefined ? prefs.decimals : 2;
                    ts.value = prefs.thousandsSep || ',';
                    ds_sep.value = prefs.decimalSep || '.';

                    menu.style.display = 'flex';
                    menu.style.top = e.pageY + 'px';
                    menu.style.left = e.pageX + 'px';
                    
                    const rect = menu.getBoundingClientRect();
                    if (rect.right > window.innerWidth) menu.style.left = (window.innerWidth - rect.width - 10) + 'px';
                    if (rect.bottom > window.innerHeight) menu.style.top = (window.innerHeight - rect.height - 10) + 'px';
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (menu.style.display === 'flex' && !menu.contains(e.target) && e.button !== 2) {
                menu.style.display = 'none';
            }
        });

        document.addEventListener('focusout', (e) => {
            if (e.target.tagName === 'INPUT') {
                const key = getPrefKey(e.target);
                if (key) {
                    const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
                    if (allPrefs[key]) {
                        window.applySystemNumberFormatting();
                    }
                }
            }
        });
        
        setTimeout(window.applySystemNumberFormatting, 500);
        setInterval(window.applySystemNumberFormatting, 2000);
    }
`;
        appJs = appJs.substring(0, funcStart) + newFunc + "\n" + appJs.substring(endPos);
    }
    
    const formatStart = appJs.indexOf("window.applySystemNumberFormatting = function() {");
    const formatEnd = appJs.indexOf("function initSystemNumberFormatting() {");
    if (formatStart !== -1 && formatEnd !== -1) {
        const newFormatFunc = `
    window.applySystemNumberFormatting = function() {
        const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
        const currentPage = getPageName();

        Object.keys(allPrefs).forEach(key => {
            if (!key.startsWith(currentPage + '_')) return;
            const selector = key.substring(currentPage.length + 1);
            const prefs = allPrefs[key];
            
            try {
                const els = document.querySelectorAll(selector);
                els.forEach(el => {
                    if (prefs.color) el.style.setProperty('color', prefs.color, 'important');
                    if (prefs.size) el.style.setProperty('font-size', prefs.size + 'px', 'important');
                    if (prefs.font) el.style.setProperty('font-family', prefs.font, 'important');
                    
                    function formatCustomNum(val) {
                        let dec = prefs.decimals !== undefined ? parseInt(prefs.decimals) : 2;
                        let fixed = val.toFixed(dec);
                        let parts = fixed.split('.');
                        let intPart = parts[0];
                        let decPart = parts.length > 1 ? parts[1] : '';
                        
                        let tSep = prefs.thousandsSep !== undefined ? prefs.thousandsSep : ',';
                        if (tSep === 'none') tSep = '';
                        
                        let dSep = prefs.decimalSep !== undefined ? prefs.decimalSep : '.';
                        
                        if (tSep) {
                            intPart = intPart.replace(/\\B(?=(\\d{3})+(?!\\d))/g, tSep);
                        }
                        
                        return dec > 0 ? intPart + dSep + decPart : intPart;
                    }

                    if (el.tagName === 'INPUT' && document.activeElement !== el) {
                        const isSearch = el.id && el.id.toLowerCase().includes('search');
                        const isDesc = el.className.includes('desc') || el.className.includes('note') || el.className.includes('center');
                        if (isSearch || isDesc) return;
                        if (el.placeholder && el.placeholder.includes('ابحث')) return;
                        
                        const val = window.cleanNumGlobal(el.value);
                        if (val !== 0 || el.value !== '') {
                            el.value = formatCustomNum(val);
                        }
                    } else if (el.tagName !== 'INPUT' && el.textContent.trim() !== '') {
                        const txt = el.textContent;
                        if (/^[0-9.,\\s'-]+$/.test(txt.trim())) {
                            const val = window.cleanNumGlobal(txt);
                            el.textContent = formatCustomNum(val);
                        }
                    }
                });
            } catch(e) { }
        });
    };

    `;
        appJs = appJs.substring(0, formatStart) + newFormatFunc + appJs.substring(formatEnd);
    }
    
    fs.writeFileSync(appJsPath, appJs);
    console.log("Upgraded app.js with Advanced Number Formatting");
}

function injectCrystalCSS() {
    const cssPath = 'c:/ELSAJANA/magaies/css/index.css';
    let css = fs.readFileSync(cssPath, 'utf8');
    const crystalCSS = fs.readFileSync('c:/ELSAJANA/magaies/crystal-buttons.css', 'utf8');

    if (!css.includes("2027 Crystal Glassmorphism Buttons")) {
        fs.appendFileSync(cssPath, "\\n" + crystalCSS);
        console.log("Injected Crystal Glassmorphism CSS into index.css");
    } else {
        console.log("Crystal CSS already exists.");
    }
}

try {
    upgradeAppJs();
    injectCrystalCSS();
} catch (e) {
    console.error("Error applying crystal upgrade:", e);
}
