function applyStylingPrefs() {
        const prefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');
        const color = prefs.color || '';
        const size = prefs.size || '';
        const font = prefs.font || '';

        let css = '';
        if (color) {
            css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { color: ${color}; }\n`;
        }
        if (size) {
            css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { font-size: ${size}; }\n`;
        }
        if (font) {
            let fontStack = '';
            if (font === 'Inter') fontStack = "'Inter', sans-serif";
            else if (font === 'Outfit') fontStack = "'Outfit', sans-serif";
            else if (font === 'JetBrains Mono') fontStack = "'JetBrains Mono', monospace";
            
            if (fontStack) {
                css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { font-family: ${fontStack}; }\n`;
            }
        }

        let styleEl = document.getElementById('custom-num-styles');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'custom-num-styles';
            document.head.appendChild(styleEl);
        }
        styleEl.innerHTML = css;
    }

    window.setNumStyle = function(prop, val) {
        const prefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');
        prefs[prop] = val;
        localStorage.setItem('num_style_prefs', JSON.stringify(prefs));
        applyStylingPrefs();
    };

    window.setNumFormat = function(prop, val) {
        const prefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
        prefs[prop] = val;
        localStorage.setItem('num_format_prefs', JSON.stringify(prefs));
        if (typeof window.recalcAll === 'function') window.recalcAll();
    };

    window.openNumContextMenu = function(e) {
        e.preventDefault();
        const menu = document.getElementById('num-context-menu');
        if (!menu) return;

        menu.style.display = 'block';
        
        let left = e.clientX;
        let top = e.clientY;
        if (left + 280 > window.innerWidth) left = window.innerWidth - 290;
        if (top + 380 > window.innerHeight) top = window.innerHeight - 390;
        
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';

        const formatPrefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
        const stylePrefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');

        const sizeSelect = menu.querySelector('select[onchange*="size"]');
        if (sizeSelect && stylePrefs.size !== undefined) sizeSelect.value = stylePrefs.size;

        const fontSelect = menu.querySelector('select[onchange*="font"]');
        if (fontSelect && stylePrefs.font !== undefined) fontSelect.value = stylePrefs.font;

        const thousandSelect = menu.querySelector('select[onchange*="thousandSep"]');
        if (thousandSelect && formatPrefs.thousandSep !== undefined) thousandSelect.value = formatPrefs.thousandSep;

        const decimalSelect = menu.querySelector('select[onchange*="decimalSep"]');
        if (decimalSelect && formatPrefs.decimalSep !== undefined) decimalSelect.value = formatPrefs.decimalSep;

        const decimalsSelect = menu.querySelector('select[onchange*="decimals"]');
        if (decimalsSelect && formatPrefs.decimals !== undefined) decimalsSelect.value = formatPrefs.decimals;
    };

    // Context menu for number style customization disabled per user request
    document.addEventListener('contextmenu', function(e) {
        const menu = document.getElementById('num-context-menu');
        if (menu) menu.style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('num-context-menu');
        if (menu && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    applyStylingPrefs();

    window.toggleTotRow = function(type) {
        const btn = document.getElementById('btn-toggle-' + type);
        const row = document.getElementById('row-tot-' + type);
        if(!btn || !row) return;

        if (row.style.display === 'none' || row.style.display === '') {
            row.style.display = 'table-row';
            btn.classList.add('active');
            // focus the local input
            const inputLoc = document.getElementById('t-' + type);
            if(inputLoc) inputLoc.focus();
        } else {
            row.style.display = 'none';
            btn.classList.remove('active');
            // reset values to zero when hidden
            const inputLoc = document.getElementById('t-' + type);
            const inputFc = document.getElementById('t-' + type + '-fc') || document.getElementById('t-' + type + '-loc');
            
            if(inputLoc) { inputLoc.value = '0'; }
            if(inputFc && type !== 'transport' && type !== 'labor') { 
                // tax, discount, additions use -loc/-fc in IDs
                const locTax = document.getElementById('t-' + type + '-loc');
                const fcTax = document.getElementById('t-' + type + '-fc');
                if(locTax) locTax.value = '0';
                if(fcTax) fcTax.value = '0';
            } else if (inputFc) {
                inputFc.value = '0';
            }
            if(typeof recalcAll === 'function') recalcAll();
        }
        // Reposition grand total based on visible expenses
        repositionGrandTotal();
    };

    // Dynamically update grand total border when expenses are visible
    window.repositionGrandTotal = function() {
        const grandRow = document.getElementById('row-grand-total') || document.getElementById('grand-total-row');
        if (!grandRow) return;

        const visibleRows = document.querySelectorAll('#grid-foot .tot-row');
        let anyVisible = false;
        visibleRows.forEach(r => { if (r.style.display === 'table-row') anyVisible = true; });

        if (anyVisible) {
            grandRow.style.borderTop = '1px solid rgba(255, 255, 255, 0.08)';
        } else {
            grandRow.style.borderTop = 'none';
        }
    };

    window.syncTotalsVisibility = function() {
        const curEl = document.getElementById('hdr-currency');
        const isSDG = (curEl && curEl.value === 'SDG');
        
        // Hide/Show FC columns in tfoot via CSS class on body
        if (isSDG) {
            document.body.classList.add('cur-sdg');
        } else {
            document.body.classList.remove('cur-sdg');
        }

        // Update Dynamic Grand Total
        const dynLoc = document.getElementById('t-grand-dynamic-loc');
        const dynFc = document.getElementById('t-grand-dynamic-fc');
        const tLocal = document.getElementById('t-grand-local');
        const tFc = document.getElementById('t-grand-fc');
        
        if (tLocal && tFc) {
            if (dynLoc && dynLoc.textContent !== tLocal.textContent) {
                dynLoc.textContent = tLocal.textContent;
                dynLoc.classList.remove('animate-total');
                void dynLoc.offsetWidth; // force reflow
                dynLoc.classList.add('animate-total');
            }
            if (dynFc && dynFc.textContent !== tFc.textContent) {
                dynFc.textContent = tFc.textContent;
                dynFc.classList.remove('animate-total');
                void dynFc.offsetWidth; // force reflow
                dynFc.classList.add('animate-total');
            }
        }
    };

    // Override the global recalcAll to call syncTotalsVisibility after it runs.
    // recalcAll is a global function declaration in purchase-pro-v5.js, so it's on window.
    // We need to wait for it to be defined, then wrap it.
    function wrapRecalcAll() {
        if (typeof recalcAll === 'function' && !recalcAll._wrapped) {
            const origRecalc = recalcAll;
            // Override the global function
            window.recalcAll = function() {
                origRecalc();
                if (typeof window.syncTotalsVisibility === 'function') {
                    window.syncTotalsVisibility();
                }
            };
            window.recalcAll._wrapped = true;
            // Also call sync now
            window.syncTotalsVisibility();
        }
    }

    // Try immediately
    wrapRecalcAll();
    // Also try after a delay (in case JS loads async)
    setTimeout(wrapRecalcAll, 300);
    setTimeout(wrapRecalcAll, 1000);
    setTimeout(wrapRecalcAll, 2000);

    // Fallback: MutationObserver on the hidden spans so we always catch changes
    const observeTarget = document.getElementById('t-grand-local');
    if (observeTarget) {
        const observer = new MutationObserver(() => {
            if (typeof window.syncTotalsVisibility === 'function') {
                window.syncTotalsVisibility();
            }
        });
        observer.observe(observeTarget, { childList: true, characterData: true, subtree: true });
    }
    const observeTargetFc = document.getElementById('t-grand-fc');
    if (observeTargetFc) {
        const observer2 = new MutationObserver(() => {
            if (typeof window.syncTotalsVisibility === 'function') {
                window.syncTotalsVisibility();
            }
        });
        observer2.observe(observeTargetFc, { childList: true, characterData: true, subtree: true });
    }

    // Call once on load
    setTimeout(() => {
        if (typeof window.syncTotalsVisibility === 'function') {
            window.syncTotalsVisibility();
        }
    }, 500);
