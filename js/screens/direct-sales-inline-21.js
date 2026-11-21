window.toggleTotRow = function(type) {
                const btn = document.getElementById('btn-toggle-' + type);
                const row = document.getElementById('row-tot-' + type);
                if (!btn || !row) return;

                if (row.style.display === 'none' || row.style.display === '') {
                    row.style.display = 'table-row';
                    btn.classList.add('active');
                    // Focus local input
                    let mapId = 't-' + type;
                    if (type === 'additions') mapId = 'addition-amt';
                    if (type === 'tax') mapId = 'tax-amt';
                    if (type === 'discount') mapId = 'discount-amt';
                    const inp = document.getElementById(mapId);
                    if (inp) inp.focus();
                } else {
                    row.style.display = 'none';
                    btn.classList.remove('active');
                    // Reset input values when hidden
                    let mapId = 't-' + type;
                    let mapFcId = 't-' + type + '-fc';
                    if (type === 'additions') { mapId = 'addition-amt'; mapFcId = 'addition-amt-fc'; }
                    if (type === 'tax') { mapId = 'tax-amt'; mapFcId = 'tax-amt-fc'; }
                    if (type === 'discount') { mapId = 'discount-amt'; mapFcId = 'discount-amt-fc'; }
                    
                    const inp = document.getElementById(mapId);
                    const inpFc = document.getElementById(mapFcId);
                    if (inp) inp.value = '0';
                    if (inpFc) inpFc.value = '0';
                    if (typeof recalcAll === 'function') recalcAll();
                }
            };

            let _lastDynLocVal = 0;
            let _lastDynFcVal = 0;
            let _animLocReq = null;
            let _animFcReq = null;

            function formatGrandTotalInt(n) {
                const num = Math.round(Number(n) || 0);
                return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            function animateDynNumber(el, startVal, endVal, duration, suffixHtml, isFc) {
                if (!el) return;
                const startTime = performance.now();
                const diff = endVal - startVal;
                
                if (Math.abs(diff) < 0.001) {
                    el.innerHTML = (isFc ? fmtNum(endVal) : formatGrandTotalInt(endVal)) + suffixHtml;
                    return;
                }

                // Trigger dynamic neon glow effect
                el.classList.remove("total-value-updating");
                void el.offsetWidth; // trigger reflow
                el.classList.add("total-value-updating");

                function step(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Easing: easeOutExpo
                    const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                    const current = startVal + (diff * ease);
                    el.innerHTML = (isFc ? fmtNum(current) : formatGrandTotalInt(current)) + suffixHtml;

                    if (progress < 1) {
                        if (isFc) {
                            _animFcReq = requestAnimationFrame(step);
                        } else {
                            _animLocReq = requestAnimationFrame(step);
                        }
                    } else {
                        el.innerHTML = (isFc ? fmtNum(endVal) : formatGrandTotalInt(endVal)) + suffixHtml;
                        setTimeout(() => {
                            el.classList.remove("total-value-updating");
                        }, 250);
                    }
                }
                
                if (isFc) {
                    if (_animFcReq) cancelAnimationFrame(_animFcReq);
                    _animFcReq = requestAnimationFrame(step);
                } else {
                    if (_animLocReq) cancelAnimationFrame(_animLocReq);
                    _animLocReq = requestAnimationFrame(step);
                }
            }

            window.syncTotalsVisibility = function() {
                const curEl = document.getElementById("hdr-currency");
                const isSDG = (!curEl || curEl.value === "SDG");
                
                if (isSDG) {
                    document.body.classList.add("cur-sdg");
                } else {
                    document.body.classList.remove("cur-sdg");
                }
                
                const dynLoc = document.getElementById("t-grand-dynamic-loc");
                const dynFc = document.getElementById("t-grand-dynamic-fc");
                const dynBadge = document.getElementById("t-grand-currency-badge");
                const tGrand = document.getElementById("t-grand");
                const rate = parseNum(document.getElementById("hdr-exchange-rate")?.value) || 1;
                
                if (dynBadge) {
                    dynBadge.textContent = isSDG ? 'SDG' : (curEl?.value || 'SDG');
                }

                if (tGrand && dynLoc) {
                    const cleanVal = tGrand.textContent.replace(/[^0-9.-]/g, "");
                    const numVal = parseNum(cleanVal) || 0;
                    
                    animateDynNumber(dynLoc, _lastDynLocVal, numVal, 450, '', false);
                    _lastDynLocVal = numVal;

                    if (dynFc) {
                        const fcVal = rate > 0 ? (isSDG ? (numVal / rate) : (numVal * rate)) : 0;
                        const suffixFc = ' ' + (isSDG ? '$' : 'SDG');
                        animateDynNumber(dynFc, _lastDynFcVal, fcVal, 450, suffixFc, true);
                        _lastDynFcVal = fcVal;
                    }
                }
            };

            window.syncFc = function(type) {
                const rate = parseNum(document.getElementById('hdr-exchange-rate')?.value) || 1;
                const isSDG = (document.getElementById('hdr-currency')?.value === 'SDG');
                let locId = 't-' + type;
                let fcId = 't-' + type + '-fc';
                if (type === 'additions') { locId = 'addition-amt'; fcId = 'addition-amt-fc'; }
                if (type === 'tax') { locId = 'tax-amt'; fcId = 'tax-amt-fc'; }
                if (type === 'discount') { locId = 'discount-amt'; fcId = 'discount-amt-fc'; }
                
                const elLoc = document.getElementById(locId);
                const elFc = document.getElementById(fcId);
                if (!elLoc || !elFc) return;
                
                const valLoc = parseNum(elLoc.value);
                if (rate > 0) {
                    elFc.value = fmtNum(isSDG ? (valLoc / rate) : (valLoc * rate));
                }
            };

            window.syncLocal = function(type) {
                const rate = parseNum(document.getElementById('hdr-exchange-rate')?.value) || 1;
                const isSDG = (document.getElementById('hdr-currency')?.value === 'SDG');
                let locId = 't-' + type;
                let fcId = 't-' + type + '-fc';
                if (type === 'additions') { locId = 'addition-amt'; fcId = 'addition-amt-fc'; }
                if (type === 'tax') { locId = 'tax-amt'; fcId = 'tax-amt-fc'; }
                if (type === 'discount') { locId = 'discount-amt'; fcId = 'discount-amt-fc'; }
                
                const elLoc = document.getElementById(locId);
                const elFc = document.getElementById(fcId);
                if (!elLoc || !elFc) return;
                
                const valFc = parseNum(elFc.value);
                if (rate > 0) {
                    elLoc.value = fmtNum(isSDG ? (valFc * rate) : (valFc / rate));
                }
            };

            // Hook into recalcAll
            if (typeof window.recalcAll === 'function') {
                const origRecalc = window.recalcAll;
                window.recalcAll = function() {
                    origRecalc();
                    if (typeof window.syncTotalsVisibility === 'function') {
                        window.syncTotalsVisibility();
                    }
                };
            }
            setTimeout(window.syncTotalsVisibility, 300);
