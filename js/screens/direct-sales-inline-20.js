(function() {
            function isZeroLike(val) {
                if (val === null || val === undefined) return true;
                const s = String(val).trim();
                if (s === '' || s === '—' || s === '-') return true;
                const cleaned = s.replace(/,/g, '');
                if (!/^-?\d+(\.\d+)?$/.test(cleaned)) return false;
                return parseFloat(cleaned) === 0;
            }
            window.syncInvoiceFaintHintsAndZeros = function(root) {
                const scope = root && root.querySelectorAll ? root : document;
                // 1. Numeric inputs in grid and totals footer
                const numInputs = scope.querySelectorAll('#pos-grid input.g-input:not([id^="item-search-"]), .tot-row input.tot-input');
                numInputs.forEach(inp => {
                    if (isZeroLike(inp.value)) inp.classList.add('is-zero-val');
                    else inp.classList.remove('is-zero-val');
                });
                // 2. Readonly total / available spans
                const spans = scope.querySelectorAll('#pos-grid .total-val, #pos-grid .avail-val, body #pos-grid tbody tr td[data-col-id="col_avail"] .g-readonly');
                spans.forEach(sp => {
                    if (isZeroLike(sp.textContent)) sp.classList.add('is-zero-val');
                    else sp.classList.remove('is-zero-val');
                });
                // 3. Warehouse & Cost Center selects with default hint ("نفس الفاتورة ↩", "-- بدون مركز --")
                const selects = scope.querySelectorAll('#pos-grid select.g-select'); const ccEl = document.getElementById('hdr-cost-center'); if (ccEl) ccEl.classList.remove('is-default-hint');
                selects.forEach(sel => {
                    if (!sel.value || String(sel.value).trim() === '') sel.classList.add('is-default-hint');
                    else sel.classList.remove('is-default-hint');
                });
            };
            document.addEventListener('DOMContentLoaded', function() {
                const editorScope = document.getElementById('invoice-editor-view') || document;
                window.syncInvoiceFaintHintsAndZeros(editorScope);
                document.addEventListener('input', function(e) {
                    if (e.target && (e.target.matches('#pos-grid input.g-input, .tot-row input.tot-input, #pos-grid select.g-select, #hdr-cost-center'))) {
                        window.syncInvoiceFaintHintsAndZeros(e.target.closest('tr') || e.target.parentElement || editorScope);
                    }
                }, true);
                document.addEventListener('change', function(e) {
                    if (e.target && (e.target.matches('#pos-grid input.g-input, .tot-row input.tot-input, #pos-grid select.g-select, #hdr-cost-center'))) {
                        window.syncInvoiceFaintHintsAndZeros(e.target.closest('tr') || e.target.parentElement || editorScope);
                    }
                }, true);
                document.addEventListener('blur', function(e) {
                    if (e.target && e.target.closest && e.target.closest('#invoice-editor-view')) {
                        const rowScope = e.target.closest('tr') || editorScope;
                        setTimeout(function() { window.syncInvoiceFaintHintsAndZeros(rowScope); }, 30);
                    }
                }, true);
                const gridBody = document.getElementById('grid-body');
                if (gridBody && window.MutationObserver) {
                    let _faintRaf = 0;
                    const obs = new MutationObserver(function() {
                        if (_faintRaf) return;
                        _faintRaf = requestAnimationFrame(function() {
                            _faintRaf = 0;
                            window.syncInvoiceFaintHintsAndZeros(gridBody);
                        });
                    });
                    obs.observe(gridBody, { childList: true });
                }
            });
        })();
