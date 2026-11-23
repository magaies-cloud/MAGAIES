        (function() {
            window._finStripCurrency = localStorage.getItem('pur_fin_strip_cur') || 'SDG';
            window.getFinStripCurrency = function() {
                const sel = document.getElementById('hdr-currency');
                return (sel && sel.value === 'USD') ? 'USD' : (window._finStripCurrency === 'USD' ? 'USD' : 'SDG');
            };
            window.convFinStripVal = function(sdgVal) {
                const n = parseFloat(sdgVal) || 0;
                if (window.getFinStripCurrency() === 'USD') {
                    const exRate = parseFloat(document.getElementById('hdr-exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
                    return exRate > 0 ? parseFloat((n / exRate).toFixed(2)) : n;
                }
                return n;
            };
            window.syncFinStripCurrencyBtnUI = function(cur) {
                const btn = document.getElementById('fin-strip-currency-btn');
                if (!btn) return;
                const c = cur || window.getFinStripCurrency();
                btn.setAttribute('data-cur-mode', c);
                if (c === 'USD') {
                    btn.textContent = 'دولار ⇄';
                } else {
                    btn.textContent = 'جنيه ⇄';
                }
            };
            window.toggleFinStripCurrency = function() {
                window._finStripCurrency = (window.getFinStripCurrency() === 'SDG') ? 'USD' : 'SDG';
                try { localStorage.setItem('pur_fin_strip_cur', window._finStripCurrency); } catch (_e) {}
                window.syncFinStripCurrencyBtnUI(window._finStripCurrency);
                if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
            };

            // 🛡️ Silent 3-Way Match Monitor: Badge stays hidden; alerts via toast ONLY on actual discrepancy
            let _last3WayMismatchKey = '';
            window.updatePurchaseThreeWayMatchBadge = function() {
                const badge = document.getElementById('purchase-3way-match-badge');
                if (badge) badge.style.setProperty('display', 'none', 'important');

                const invNo = (document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '').trim();
                const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
                const invTotalQty = activeRows.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);

                let linkedGrns = [];
                ['erp_grn_v1', 'erp_purchase_grn_v1', 'erp_split_deliveries_v1', 'erp_warehouse_receipts_v1'].forEach(k => {
                    try {
                        const arr = JSON.parse(localStorage.getItem(k) || '[]');
                        if (Array.isArray(arr) && invNo && invNo !== '---') {
                            arr.forEach(g => {
                                if (g && !g.deleted && g.status !== 'cancelled' && (
                                    String(g.invoiceNo) === invNo ||
                                    String(g.refInvoice) === invNo ||
                                    String(g.parentInvoiceNo) === invNo ||
                                    String(g.purchaseInvoiceNo) === invNo
                                )) {
                                    linkedGrns.push(g);
                                }
                            });
                        }
                    } catch (_e) {}
                });

                if (linkedGrns.length > 0 && invTotalQty > 0) {
                    let grnTotalQty = 0;
                    linkedGrns.forEach(g => {
                        if (Array.isArray(g.items)) {
                            g.items.forEach(it => { grnTotalQty += (parseFloat(it.receivedQty ?? it.qty ?? it.quantity ?? 0) || 0); });
                        } else {
                            grnTotalQty += (parseFloat(g.totalQty ?? g.qty ?? 0) || 0);
                        }
                    });
                    const diff = Math.abs(invTotalQty - grnTotalQty);
                    if (grnTotalQty > 0 && diff >= 0.001) {
                        const key = `${invNo}:${invTotalQty}:${grnTotalQty}`;
                        if (_last3WayMismatchKey !== key && typeof showToast === 'function') {
                            _last3WayMismatchKey = key;
                            showToast(`⚠️ تنبيه خطأ في المطابقة الثلاثية: كمية الفاتورة (${invTotalQty}) لا تطابق المستلم مخزنياً (${grnTotalQty})`, 'warning', 4500);
                        }
                    } else {
                        _last3WayMismatchKey = '';
                    }
                }
            };

            // 🎯 Align Primary Grand Total Directly Underneath the Invoice "الجملة / الإجمالي" (data-col-id="total") Column
            window.alignGrandTotalUnderTotalColumn = function() {
                try {
                    const table = document.getElementById('pos-grid');
                    const card = document.querySelector('#grid-foot .pur-compact-tot-card');
                    const dynLoc = document.getElementById('t-grand-dynamic-loc');
                    const dynFc = document.getElementById('t-grand-dynamic-fc');
                    if (!table || !card || !dynLoc || !dynFc) return;

                    const curEl = document.getElementById('hdr-currency');
                    const isSDG = (!curEl || curEl.value === 'SDG');
                    const primaryBox = isSDG ? dynLoc : dynFc;

                    const totalCol = table.querySelector('tbody tr td[data-col-id="total"]') || table.querySelector('thead tr th[data-col-id="total"]');
                    if (!totalCol) return;

                    const cardRect = card.getBoundingClientRect();
                    const totalRect = totalCol.getBoundingClientRect();
                    if (!cardRect.width || !totalRect.width) return;

                    const boxW = primaryBox.offsetWidth || 175;
                    const totalCenter = totalRect.left + (totalRect.width / 2);
                    const targetBoxLeft = totalCenter - (boxW / 2);
                    const padLeft = Math.max(10, Math.round(targetBoxLeft - cardRect.left));
                    card.style.setProperty('padding-left', padLeft + 'px', 'important');
                } catch (_e) {}
            };

            window.formatGrandTotalsDualSdgUsd = function() {
                const curEl = document.getElementById('hdr-currency');
                const isSDG = (!curEl || curEl.value === 'SDG');
                const exRate = parseFloat(document.getElementById('hdr-exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 1;
                if (isSDG) document.body.classList.add('cur-sdg');
                else document.body.classList.remove('cur-sdg');

                const dynLoc = document.getElementById('t-grand-dynamic-loc');
                const dynFc = document.getElementById('t-grand-dynamic-fc');
                const tLocal = document.getElementById('t-grand-local');
                const tFc = document.getElementById('t-grand-fc');
                if (!dynLoc || !dynFc) return;

                const parseRaw = (txt) => parseFloat(String(txt || '0').replace(/[^0-9.-]/g, '')) || 0;
                const rawLocal = parseRaw(tLocal ? tLocal.textContent : '0');
                const rawFc = parseRaw(tFc ? tFc.textContent : '0');

                const sdgVal = isSDG ? rawLocal : (rawFc > 0 ? rawFc : (rawLocal * exRate));
                const usdVal = isSDG ? (rawFc > 0 ? rawFc : (exRate > 0 ? rawLocal / exRate : 0)) : rawLocal;

                const fmtSDG = Math.round(sdgVal).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                const fmtUSD = usdVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                dynLoc.classList.remove('hide-if-sdg');
                dynLoc.setAttribute('data-cur-unit', 'ج.س');
                if (dynLoc.textContent !== fmtSDG) {
                    dynLoc.textContent = fmtSDG;
                }
                const lenSdg = fmtSDG.length;
                const sdgPx = lenSdg > 15 ? 20 : (lenSdg > 11 ? 24 : 27);
                dynLoc.style.setProperty('font-size', sdgPx + 'px', 'important');

                dynFc.classList.remove('hide-if-sdg');
                dynFc.setAttribute('data-cur-unit', '$');
                if (dynFc.textContent !== fmtUSD) {
                    dynFc.textContent = fmtUSD;
                }
                const lenUsd = fmtUSD.length;
                const usdPx = lenUsd > 13 ? 19 : (lenUsd > 10 ? 22 : (isSDG ? 24 : 27));
                dynFc.style.setProperty('font-size', usdPx + 'px', 'important');

                // Order in RTL:
                // - When invoice is SDG: first USD (dynFc), then at the end directly under "الجملة" column -> SDG (dynLoc)
                // - When invoice is USD (or non-SDG): reversed -> first SDG (dynLoc), then at the end directly under "الجملة" column -> USD (dynFc)
                const valsWrap = dynLoc.parentElement;
                if (valsWrap && valsWrap === dynFc.parentElement) {
                    if (isSDG) {
                        if (valsWrap.firstElementChild !== dynFc) {
                            valsWrap.appendChild(dynFc);
                            valsWrap.appendChild(dynLoc);
                        }
                    } else {
                        if (valsWrap.firstElementChild !== dynLoc) {
                            valsWrap.appendChild(dynLoc);
                            valsWrap.appendChild(dynFc);
                        }
                    }
                }
                window.alignGrandTotalUnderTotalColumn();
            };
            window.syncTotalsVisibility = window.formatGrandTotalsDualSdgUsd;

            window.normalizePurchaseTfootLayout = function() {
                const tfoot = document.getElementById('grid-foot');
                if (!tfoot) return;
                tfoot.querySelectorAll('tr > td.hide-if-sdg').forEach(td => td.remove());
                tfoot.querySelectorAll('tr > td[colspan="13"]').forEach(td => td.setAttribute('colspan', '9'));
                const grandRow = document.getElementById('row-grand-total');
                if (grandRow && !grandRow.dataset.compactNormalized) {
                    const lbl = document.getElementById('lbl-grand-dynamic');
                    const loc = document.getElementById('t-grand-dynamic-loc');
                    const fc = document.getElementById('t-grand-dynamic-fc');
                    const toggles = document.getElementById('toggles-area');
                    if (lbl && loc && fc) {
                        loc.classList.remove('hide-if-sdg');
                        fc.classList.remove('hide-if-sdg');
                        loc.setAttribute('data-cur-unit', 'ج.س');
                        fc.setAttribute('data-cur-unit', '$');
                        grandRow.innerHTML = '';
                        const tdFull = document.createElement('td');
                        tdFull.colSpan = 9;
                        tdFull.style.cssText = 'border: none !important; padding: 10px 0 4px 0 !important; text-align: left !important; vertical-align: middle !important;';
                        const card = document.createElement('div');
                        card.className = 'pur-compact-tot-card';
                        if (toggles) card.appendChild(toggles);
                        card.appendChild(lbl);
                        const valsWrap = document.createElement('div');
                        valsWrap.style.cssText = 'display: inline-flex; direction: rtl; flex-wrap: nowrap; gap: 14px; align-items: center; justify-content: flex-end;';
                        valsWrap.appendChild(fc);
                        valsWrap.appendChild(loc);
                        card.appendChild(valsWrap);
                        tdFull.appendChild(card);
                        grandRow.appendChild(tdFull);
                        grandRow.dataset.compactNormalized = '1';
                    }
                }
                tfoot.querySelectorAll('tr.tot-row').forEach(tr => {
                    if (tr.dataset.compactNormalized) return;
                    const tds = Array.from(tr.children);
                    if (tds.length >= 4) {
                        tds[0].colSpan = 4;
                        tds[1].colSpan = 2;
                        tds[2].colSpan = 2;
                        tds[2].style.minWidth = '170px';
                        tds[3].colSpan = 1;
                        tr.dataset.compactNormalized = '1';
                    }
                });
                window.formatGrandTotalsDualSdgUsd();
            };

            document.addEventListener('DOMContentLoaded', function() {
                window.syncFinStripCurrencyBtnUI();
                window.normalizePurchaseTfootLayout();
                if (typeof window.onCustomerChange === 'function') {
                    setTimeout(() => window.onCustomerChange(), 80);
                }
                const exInput = document.getElementById('hdr-exchange-rate');
                if (exInput) {
                    exInput.addEventListener('change', () => {
                        if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                        window.formatGrandTotalsDualSdgUsd();
                    });
                    exInput.addEventListener('input', () => {
                        if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                        window.formatGrandTotalsDualSdgUsd();
                    });
                }
                const curSel = document.getElementById('hdr-currency');
                if (curSel) {
                    curSel.addEventListener('change', () => setTimeout(() => window.formatGrandTotalsDualSdgUsd(), 30));
                }
                window.addEventListener('resize', () => window.alignGrandTotalUnderTotalColumn());
                const tableEl = document.getElementById('pos-grid');
                if (tableEl && typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(() => window.alignGrandTotalUnderTotalColumn()).observe(tableEl);
                }
                // Redirect Enter key from #hdr-description / #hdr-cost-center and F2 key to first grid item input
                document.addEventListener('keydown', function(ev) {
                    if (ev.key === 'F2') {
                        const firstItem = document.querySelector('#pos-grid input[id^="item-search-"]');
                        if (firstItem) {
                            ev.preventDefault();
                            ev.stopImmediatePropagation();
                            firstItem.focus();
                            if (typeof firstItem.select === 'function') firstItem.select();
                        }
                    } else if (ev.key === 'Enter' && !ev.shiftKey && !ev.ctrlKey && !ev.altKey) {
                        if (ev.target && (ev.target.id === 'hdr-description' || ev.target.id === 'hdr-cost-center')) {
                            setTimeout(() => {
                                if (document.activeElement === document.getElementById('quick-item-search') || document.activeElement === ev.target) {
                                    const firstItem = document.querySelector('#pos-grid input[id^="item-search-"]');
                                    if (firstItem) {
                                        firstItem.focus();
                                        if (typeof firstItem.select === 'function') firstItem.select();
                                    }
                                }
                            }, 50);
                        }
                    }
                }, true);
            });
            window.syncFinStripCurrencyBtnUI();
            window.normalizePurchaseTfootLayout();
        })();
        (function() {
            const TONES = [
                { id: 'dark_midnight',   name: 'داكن كحلي ملكي (Dark)',         bg: '#0f172a', fg: '#1e293b', brd: '#38bdf8', isDark: true, themeKey: 'midnight_obsidian' },
                { id: 'pure_oled',       name: 'أسود ناصع للشاشات (OLED)',      bg: '#010308', fg: '#090d16', brd: '#0284c7', isDark: true, themeKey: 'pure_oled' },
                { id: 'mocha_brown',     name: 'بني قهوة فخم (Mocha Dark)',     bg: '#1f120d', fg: '#3e2723', brd: '#f59e0b', isDark: true, themeKey: 'mocha_brown' },
                { id: 'slate_twilight',  name: 'رمادي معدني داكن (Slate)',       bg: '#1e293b', fg: '#334155', brd: '#94a3b8', isDark: true, themeKey: 'slate_twilight' },
                { id: 'ivory_default',   name: 'عاجي دافئ (فاتح افتراضي)',       bg: '#d9c39e', fg: '#f3e5ab', brd: '#854d0e', themeKey: 'warm_cream' },
                { id: 'royal_bronze',    name: '👑 الذهبي الملكي (برونزي فاخر)', bg: '#b07239', fg: '#d9a066', brd: '#78350f', themeKey: 'warm_cream' },
                { id: 'platinum_gray',   name: 'رمادي بلاتيني احترافي',          bg: '#64748b', fg: '#94a3b8', brd: '#334155', themeKey: 'exec_gray' },
                { id: 'crystal_silver',  name: 'فضي جليدي ناصع',                 bg: '#cbd5e1', fg: '#e2e8f0', brd: '#64748b', themeKey: 'ultra_white' },
                { id: 'sage_pearl',      name: 'لؤلؤي زمردي هادئ',               bg: '#10b981', fg: '#6ee7b7', brd: '#047857', themeKey: 'warm_cream' },
                { id: 'sky_alabaster',   name: 'سماوي لازوردي فاتح',             bg: '#0284c7', fg: '#38bdf8', brd: '#0369a1', themeKey: 'ultra_white' },
                { id: 'rose_blossom',    name: 'زهري ملكي ناعم (Rose)',          bg: '#ec4899', fg: '#fbcfe8', brd: '#9d174d', themeKey: 'warm_cream' },
                { id: 'warm_mocha_dark', name: 'موكا صحراوي غامق',               bg: '#6b4226', fg: '#9c6b43', brd: '#451a03', themeKey: 'warm_cream' },
                { id: 'deep_slate_dark', name: 'رصاصي فحمي غامق',                bg: '#334155', fg: '#475569', brd: '#0f172a', themeKey: 'exec_gray' }
            ];
            window.applyPurchaseLightTone = function(toneId, notify) {
                const t = TONES.find(x => x.id === toneId) || (['dark_midnight','midnight_obsidian','dark','pure_oled','mocha_brown','slate_twilight'].includes(toneId) ? TONES[0] : TONES[4]);
                const targetTheme = t.themeKey || (t.isDark ? 'midnight_obsidian' : 'warm_cream');
                document.documentElement.setAttribute('data-light-tone', t.id);
                document.documentElement.setAttribute('data-theme', targetTheme);
                document.documentElement.classList.toggle('light-theme', !t.isDark);
                document.documentElement.classList.toggle('dark-theme', !!t.isDark);
                if (document.body) {
                    document.body.setAttribute('data-light-tone', t.id);
                    document.body.setAttribute('data-theme', targetTheme);
                    document.body.classList.toggle('light-theme', !t.isDark);
                    document.body.classList.toggle('dark-theme', !!t.isDark);
                }
                try {
                    document.cookie = 'magaies_light_tone=' + encodeURIComponent(t.id) + '; path=/; max-age=315360000; SameSite=Lax';
                    document.cookie = 'magaies_active_theme=' + encodeURIComponent(targetTheme) + '; path=/; max-age=315360000; SameSite=Lax';
                } catch (_e) {}
                const _s = (k, v) => { try { localStorage.setItem(k, v); } catch(_e) { try { ['erp_items_v1','erp_global_units_v1','erp_auto_backups'].forEach(rk => localStorage.removeItem(rk)); localStorage.setItem(k, v); } catch(_e2) {} } };
                _s('pur_light_tone_palette', t.id);
                _s('magaies_light_tone', t.id);
                ['magaies_active_theme','magaiees_global_theme','magaiees_mobile_theme','ui_theme'].forEach(k => _s(k, targetTheme));
                _s('erp_theme', t.isDark ? 'dark' : 'light');
                if (notify) {
                    if (typeof window.applyMasterTheme === 'function') window.applyMasterTheme(targetTheme, false);
                    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: targetTheme, tone: t.id, isLight: !t.isDark } }));
                    if (typeof showToast === 'function') showToast((t.isDark ? '🌙 ' : '🎨 ') + 'تم تثبيت المظهر في كل البرنامج: ' + t.name, 'success', 2600);
                }
            };
            const getCkTone = () => { const m = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/); return m ? decodeURIComponent(m[1]) : ''; };
            const savedTone = getCkTone() || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || document.documentElement.getAttribute('data-light-tone') || 'ivory_default';
            window.applyPurchaseLightTone(savedTone, false);
            window.openPurchaseLightTonePopover = function(anchorEl) {
                if (typeof window.openUniversalErpTonePopover === 'function' && window.openUniversalErpTonePopover !== window.openPurchaseLightTonePopover) {
                    window.openUniversalErpTonePopover(anchorEl);
                    return;
                }
                let pop = document.getElementById('pur-light-tone-popover');
                if (!pop) {
                    pop = document.createElement('div');
                    pop.id = 'pur-light-tone-popover';
                    document.body.appendChild(pop);
                    document.addEventListener('click', (e) => {
                        const isThemeBtn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
                        if (pop.style.display !== 'none' && !pop.contains(e.target) && !isThemeBtn) pop.style.display = 'none';
                    });
                }
                if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
                const isDarkNow = !document.documentElement.classList.contains('light-theme');
                pop.style.cssText = 'position:fixed;z-index:9999999;background:' + (isDarkNow ? 'linear-gradient(145deg,#0f172a,#1e293b)' : 'linear-gradient(145deg,#fffbf2,#f3ead8)') + ';border:2px solid #fbbf24;border-radius:14px;padding:10px;box-shadow:0 18px 42px rgba(0,0,0,0.65),0 0 22px rgba(245,158,11,0.35);min-width:270px;max-height:86vh;overflow-y:auto;direction:rtl;font-family:Tajawal,Cairo,sans-serif;';
                const rawToneAttr = document.documentElement.getAttribute('data-light-tone') || localStorage.getItem('magaies_light_tone') || (isDarkNow ? 'dark_midnight' : 'ivory_default');
                const cur = TONES.some(x => x.id === rawToneAttr) ? rawToneAttr : (isDarkNow ? 'dark_midnight' : 'ivory_default');
                const hdrCol = isDarkNow ? '#fde047' : '#000000';
                pop.innerHTML = `<div style="color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;font-weight:900;font-size:0.92rem;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid rgba(148,163,184,0.35);display:flex;justify-content:space-between;align-items:center;"><span>🎨 المظهر ونغمات النظام (13 لوناً)</span><span style="font-size:0.75rem;color:${isDarkNow?'#94a3b8':'#000000'}!important;-webkit-text-fill-color:${isDarkNow?'#94a3b8':'#000000'}!important;">0ms موحد 100%</span></div>` +
                    TONES.map(t => {
                        const txtCol = t.isDark ? '#ffffff' : '#000000';
                        const btnBg = t.isDark ? `linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%)` : `linear-gradient(135deg,#ffffff 0%,${t.fg} 100%)`;
                        return `<button type="button" data-tone-dark="${t.isDark?'1':'0'}" onclick="window.applyPurchaseLightTone('${t.id}',true);if(window.playGlobalErpSystemSoundTone)window.playGlobalErpSystemSoundTone();document.getElementById('pur-light-tone-popover').style.display='none';" style="width:100%;display:flex;align-items:center;gap:10px;padding:7px 10px;margin-bottom:5px;border-radius:9px;cursor:pointer;background:${btnBg}!important;border:${cur===t.id?'2.5px solid #d97706':'1.5px solid '+t.brd}!important;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;font-size:0.88rem;text-align:right;"><span style="width:28px;height:28px;border-radius:7px;background:linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%);border:2px solid ${t.brd};flex-shrink:0;"></span><span style="flex:1;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;">${t.name}</span>${cur===t.id?`<span style="color:${t.isDark?'#fde047':'#047857'}!important;-webkit-text-fill-color:${t.isDark?'#fde047':'#047857'}!important;">✓</span>`:''}</button>`;
                    }).join('');
                const r = anchorEl ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
                pop.style.top = Math.max(10, Math.min(window.innerHeight - 450, r.bottom + 8)) + 'px';
                pop.style.left = Math.max(12, Math.min(window.innerWidth - 280, r.left)) + 'px';
                pop.style.display = 'block';
            };
            document.addEventListener('click', function(e) {
                const btn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
                if (!btn) return;
                e.preventDefault();
                e.stopImmediatePropagation();
                window.openPurchaseLightTonePopover(btn);
            }, true);
            document.addEventListener('contextmenu', function(e) {
                const btn = e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn');
                if (btn) { e.preventDefault(); window.openPurchaseLightTonePopover(btn); }
            }, true);
            // Interactive Kinetic Light Gestures (Cursor Specular Spotlight & Tactile Light Wave Burst)
            let _rafLight = 0;
            /* pointermove spotlight disabled */
            const triggerKineticPulse = (el) => {
                if (!el) return;
                el.classList.remove('pur-kinetic-pulse');
                void el.offsetWidth;
                el.classList.add('pur-kinetic-pulse');
            };
            document.addEventListener('pointerdown', function(e) {
                const t = e.target && e.target.closest && e.target.closest('#purchase-editor-view button, .fin-metric-pill, .h-form-group, #pos-grid td[data-col-id="available"], #pos-grid td[data-col-id="warehouse"], #pos-grid td[data-col-id="unit"], #pos-grid td[data-col-id="price"]');
                if (t) triggerKineticPulse(t);
            }, { passive: true });
            document.addEventListener('change', function(e) {
                const z = e.target && e.target.closest && e.target.closest('.group-currency, .group-warehouse, #pos-grid td[data-col-id="warehouse"], #pos-grid td[data-col-id="unit"], #pos-grid td[data-col-id="price"]');
                if (z) triggerKineticPulse(z);
            }, { passive: true });
        })();

(function initPurchaseFinancialMovementBridge() {
    window.computeNextPurchaseFinVoucherNo = function() {
        const now = new Date();
        const ym = String(now.getFullYear()).slice(-2) + String(now.getMonth() + 1).padStart(2, '0');
        const prefix = 'E' + ym;
        const extractSeq = (raw) => {
            const s = String(raw || '').trim();
            if (!s || !s.startsWith(prefix)) return 0;
            const n = parseInt(s.slice(prefix.length), 10);
            return (!isNaN(n) && n > 0 && n < 999999) ? n : 0;
        };
        let max = 0;
        if (window.DB_NEXT_FIN_VOUCHER_NO) {
            const dbSq = extractSeq(window.DB_NEXT_FIN_VOUCHER_NO);
            if (dbSq - 1 > max) max = dbSq - 1;
        }
        const getTK = window.getTenantKey || ((k) => k);
        Array.from(new Set([getTK('erp_single_payments_v1'), 'erp_single_payments_v1'])).forEach(k => {
            try {
                const arr = JSON.parse(localStorage.getItem(k) || '[]');
                if (Array.isArray(arr)) arr.forEach(v => { if (v && !v.deleted) { const sq = extractSeq(v.voucherNo || v.id); if (sq > max) max = sq; } });
            } catch (_) {}
        });
        return prefix + String(max + 1).padStart(4, '0');
    };

    window.findPurchaseLinkedFinRecord = function(invNo) {
        const cleanInv = String(invNo || '').trim().replace(/^#/, '');
        if (!cleanInv || cleanInv === '---') return { isSavedInvoice: false, voucherNo: '', record: null };
        const getTK = window.getTenantKey || ((k) => k);
        let isSavedInvoice = (typeof window.currentViewedInvoiceNo !== 'undefined' && String(window.currentViewedInvoiceNo || '').trim().replace(/^#/, '') === cleanInv);
        let foundInv = null;
        try {
            const directRaw = localStorage.getItem('erp_pur_' + cleanInv);
            if (directRaw) { foundInv = JSON.parse(directRaw); isSavedInvoice = true; }
        } catch (_) {}
        if (!foundInv) {
            for (const k of ['erp_purchase_invoices_v1', 'erp_purchases_v1', 'magaies_local_purchase_invoices_v1']) {
                try {
                    const invs = JSON.parse(localStorage.getItem(k) || '[]');
                    if (Array.isArray(invs)) {
                        const m = invs.find(x => x && (String(x.invoiceNo || '').trim().replace(/^#/, '') === cleanInv || String(x.id || '').trim().replace(/^#/, '') === cleanInv));
                        if (m) { foundInv = m; isSavedInvoice = true; break; }
                    }
                } catch (_) {}
            }
        }
        let linkedNo = (foundInv && foundInv.linkedFinancialVoucherNo) ? String(foundInv.linkedFinancialVoucherNo).trim() : '';
        let linkedRec = null;
        const payKeys = Array.from(new Set([getTK('erp_single_payments_v1'), 'erp_single_payments_v1', 'magaies_vouchers_cache', 'erp_payments_v1']));
        for (const k of payKeys) {
            try {
                const pLog = JSON.parse(localStorage.getItem(k) || '[]');
                if (!Array.isArray(pLog)) continue;
                const pFound = pLog.find(p => {
                    if (!p || p.deleted) return false;
                    const pNo = String(p.voucherNo || p.id || '').trim();
                    const pRef = String(p.refInvoice || p.invoiceNo || '').trim().replace(/^#/, '');
                    if (linkedNo && pNo === linkedNo) return true;
                    if (pRef && pRef === cleanInv) return true;
                    if (p.autoFromPurchase && String(p.statement || '').includes('#' + cleanInv)) return true;
                    return false;
                });
                if (pFound) {
                    linkedRec = pFound;
                    if (!linkedNo) linkedNo = String(pFound.voucherNo || pFound.id || '').trim();
                    break;
                }
            } catch (_) {}
        }
        return { isSavedInvoice, voucherNo: linkedNo, record: linkedRec };
    };

    window.refreshPurchaseFinSeqBadge = function() {
        const valEl = document.getElementById('pur-fin-mov-seq-val');
        const btnEl = document.getElementById('pur-fin-mov-seq-badge');
        if (!valEl) return;
        const curInvNo = (document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '').trim();
        const info = window.findPurchaseLinkedFinRecord(curInvNo);
        if (info.isSavedInvoice) {
            if (info.voucherNo) {
                valEl.textContent = info.voucherNo;
                if (btnEl) {
                    btnEl.dataset.targetVoucher = info.voucherNo;
                    btnEl.dataset.hasVoucher = '1';
                    btnEl.style.opacity = '1';
                    btnEl.title = 'حركة مالية مرتبطة بالفاتورة — اضغط للفتح: ' + info.voucherNo;
                }
            } else {
                valEl.textContent = '—';
                if (btnEl) {
                    btnEl.dataset.targetVoucher = '';
                    btnEl.dataset.hasVoucher = '0';
                    btnEl.style.opacity = '0.68';
                    btnEl.title = 'هذه الفاتورة ليس لها حركة سداد مسجلة من شاشة المشتريات';
                }
            }
            return;
        }
        const nextNo = window.computeNextPurchaseFinVoucherNo();
        valEl.textContent = nextNo;
        if (btnEl) {
            btnEl.dataset.targetVoucher = nextNo;
            btnEl.dataset.hasVoucher = 'new';
            btnEl.style.opacity = '1';
            btnEl.title = 'رقم الحركة المالية القادمة عند السداد من هذه الفاتورة: ' + nextNo;
        }
    };

    window.openPurchaseLinkedFinMovement = function(forInvoiceNo = '', allowFallbackToPaymentScreen = false) {
        const btnEl = document.getElementById('pur-fin-mov-seq-badge');
        let vNo = '';
        let linkedRec = null;
        if (forInvoiceNo && typeof window.findPurchaseLinkedFinRecord === 'function') {
            const info = window.findPurchaseLinkedFinRecord(forInvoiceNo);
            if (info && info.voucherNo) { vNo = info.voucherNo; linkedRec = info.record || null; }
        }
        if (!vNo) {
            if (!allowFallbackToPaymentScreen && btnEl && btnEl.dataset.hasVoucher === '0') {
                if (typeof showToast === 'function') showToast('ℹ️ هذه الفاتورة لا تحتوي على سداد مسجل من شاشة المشتريات', 'info', 3000);
                return;
            }
            vNo = (btnEl && btnEl.dataset.targetVoucher) || document.getElementById('pur-fin-mov-seq-val')?.textContent?.trim() || '';
        }
        if (vNo && vNo !== '---' && vNo !== '—') {
            try {
                let found = linkedRec ? Object.assign({}, linkedRec) : null;
                if (!found) {
                    const getTK = window.getTenantKey || ((k) => k);
                    const keys = [getTK('erp_single_payments_v1'), 'erp_single_payments_v1', 'magaies_vouchers_cache'];
                    for (const k of keys) {
                        const arr = JSON.parse(localStorage.getItem(k) || '[]');
                        if (Array.isArray(arr)) {
                            const hit = arr.find(p => p && (String(p.voucherNo) === vNo || String(p.id) === vNo));
                            if (hit) { found = Object.assign({}, hit); break; }
                        }
                    }
                }
                const curInvNo = String(forInvoiceNo || document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '').trim().replace(/^#/, '');
                if (found && curInvNo) {
                    let srcInv = null;
                    try { const rawInv = localStorage.getItem('erp_pur_' + curInvNo); if (rawInv) srcInv = JSON.parse(rawInv); } catch (_) {}
                    if (!srcInv) {
                        for (const ik of ['erp_purchase_invoices_v1', 'erp_purchases_v1', 'magaies_local_purchase_invoices_v1']) {
                            try {
                                const arr = JSON.parse(localStorage.getItem(ik) || '[]');
                                if (Array.isArray(arr)) { srcInv = arr.find(x => x && String(x.invoiceNo || x.id || '').trim().replace(/^#/, '') === curInvNo); if (srcInv) break; }
                            } catch (_) {}
                        }
                    }
                    if (srcInv) {
                        if ((!Array.isArray(found.bankTransfers) || found.bankTransfers.length === 0) && Array.isArray(srcInv.bankTransfers) && srcInv.bankTransfers.length > 0) {
                            found.bankTransfers = srcInv.bankTransfers.map(bt => ({ bankAccountId: bt.bankAccountId || '', bankName: bt.bankName || '', note: bt.bankName || bt.note || '', transferNo: String(bt.reference || bt.transferNo || '').trim(), reference: String(bt.reference || bt.transferNo || '').trim(), amount: parseFloat(bt.amount) || 0, statement: bt.notes || found.statement || '' }));
                        }
                        if ((!Array.isArray(found.cheques) || found.cheques.length === 0) && Array.isArray(srcInv.cheques) && srcInv.cheques.length > 0) {
                            found.cheques = srcInv.cheques.map(ch => ({ bankAccountId: ch.bankAccountId || '', checkBank: ch.bankName || ch.checkBank || '', checkBranch: ch.bankName || ch.checkBranch || '', checkNo: ch.chequeNo || ch.checkNo || '', chequeNo: ch.chequeNo || ch.checkNo || '', checkDate: ch.dueDate || ch.checkDate || '', dueDate: ch.dueDate || ch.checkDate || '', amount: parseFloat(ch.amount) || 0, statement: found.statement || '' }));
                        }
                        if (!(parseFloat(found.cashAmount) > 0) && parseFloat(srcInv.cashAmount || 0) > 0) {
                            found.cashAmount = parseFloat(srcInv.cashAmount) || 0;
                        }
                    }
                }
                if (found) sessionStorage.setItem('_edit_voucher_data', JSON.stringify(found));
            } catch (_) {}
            window.location.href = 'single-cash-payment.php?id=' + encodeURIComponent(vNo);
            return;
        }
        window.location.href = 'single-cash-payment.php';
    };

    window.openPurchaseInboundReceiptsDrawer = function() {
        const ov = document.getElementById('inbound-bankak-drawer-overlay');
        if (ov && ov.parentElement !== document.body) document.body.appendChild(ov);
        if (window.InboundBankakManager && typeof window.InboundBankakManager.openDrawer === 'function') {
            window.InboundBankakManager.openDrawer();
            return;
        }
        if (ov) ov.style.display = 'flex';
        let sc = document.querySelector('script[src*="inbound-bankak-drawer-core.js"]');
        if (!sc) {
            sc = document.createElement('script');
            sc.src = 'js/inbound-bankak-drawer-core.js?v=' + Date.now();
            sc.onload = () => { if (window.InboundBankakManager && window.InboundBankakManager.openDrawer) window.InboundBankakManager.openDrawer(); };
            document.body.appendChild(sc);
        } else {
            setTimeout(() => { if (window.InboundBankakManager && window.InboundBankakManager.openDrawer) window.InboundBankakManager.openDrawer(); }, 180);
        }
    };

    // Pull latest live server finance sequence once on load & merge cleanly without wiping local linked vouchers
    fetch('single-cash-payment.php?api=flash_delta_sync', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{}' })
        .then(r => r.json()).then(d => {
            if (d && d.nextVoucherNo) window.DB_NEXT_FIN_VOUCHER_NO = d.nextVoucherNo;
            if (d && Array.isArray(d.records)) {
                const getTK = window.getTenantKey || ((k) => k);
                const tkSP = getTK('erp_single_payments_v1');
                let localArr = [];
                try { localArr = JSON.parse(localStorage.getItem(tkSP) || localStorage.getItem('erp_single_payments_v1') || '[]') || []; } catch (_) {}
                const byId = new Map();
                localArr.forEach(r => { if (r && (r.voucherNo || r.id)) byId.set(String(r.voucherNo || r.id), r); });
                d.records.forEach(r => {
                    if (!r || !(r.voucherNo || r.id)) return;
                    const k = String(r.voucherNo || r.id);
                    const prev = byId.get(k);
                    if (!prev || (Number(r.updatedAtMs || 0) >= Number(prev.updatedAtMs || 0))) byId.set(k, Object.assign({}, prev || {}, r));
                });
                const mergedArr = Array.from(byId.values());
                localStorage.setItem('erp_single_payments_v1', JSON.stringify(mergedArr));
                if (tkSP !== 'erp_single_payments_v1') localStorage.setItem(tkSP, JSON.stringify(mergedArr));
                window.refreshPurchaseFinSeqBadge();
            }
        }).catch(() => {});

    document.addEventListener('DOMContentLoaded', () => {
        window.refreshPurchaseFinSeqBadge();
        const invSpan = document.getElementById('inv-number');
        if (invSpan && typeof MutationObserver !== 'undefined') {
            new MutationObserver(() => setTimeout(window.refreshPurchaseFinSeqBadge, 30)).observe(invSpan, { childList: true, characterData: true, subtree: true });
        }
    });
    setTimeout(window.refreshPurchaseFinSeqBadge, 50);

    // ═══ طباعة الإذن (ترويسة الشركة + الأصناف والكميات فقط بدون أسعار) + كشف الحساب المالي الكامل ═══
    function _getPurchaseCompanyLetterhead() {
        let cfg = {};
        for (const k of ['erp_settings_v1', 'erp_company_info_v1', 'magaies_company_settings']) {
            try { const o = JSON.parse(localStorage.getItem(k) || '{}'); if (o && typeof o === 'object') cfg = Object.assign(cfg, o); } catch (_) {}
        }
        return {
            name: cfg.companyName || cfg.company_name || cfg.name || 'شركة السجانة لمواد البناء والتجارة',
            sub: cfg.companySubtitle || cfg.tagline || cfg.activity || 'تجارة الحديد والأسمنت ومواد البناء',
            phone: cfg.companyPhone || cfg.phone || cfg.mobile || '',
            address: cfg.companyAddress || cfg.address || '',
            logo: cfg.companyLogo || cfg.logo || cfg.logoUrl || ''
        };
    }
    function _collectCurrentPurchasePrintData() {
        const pn = (v) => { const n = parseFloat(String(v || '0').replace(/,/g, '')); return isNaN(n) ? 0 : n; };
        const fn = (n) => (Math.round((parseFloat(n) || 0) * 100) / 100).toLocaleString('en-US');
        const invNo = (document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '---').trim();
        const date = document.getElementById('hdr-date')?.value || new Date().toISOString().split('T')[0];
        const supplier = (document.getElementById('hdr-customer-search')?.value || 'مورد عام').trim();
        const whEl = document.getElementById('hdr-warehouse');
        const warehouse = whEl && whEl.selectedOptions && whEl.selectedOptions[0] ? whEl.selectedOptions[0].textContent.trim() : (whEl?.value || 'المستودع الرئيسي');
        const ccEl = document.getElementById('hdr-cost-center');
        const costCenter = ccEl && ccEl.selectedOptions && ccEl.selectedOptions[0] ? ccEl.selectedOptions[0].textContent.trim() : '';
        const currency = document.getElementById('hdr-currency')?.value || 'SDG';
        const curLabel = currency === 'USD' ? 'دولار ($)' : (currency === 'SDG' ? 'جنيه (ج.س)' : currency);
        const rate = pn(document.getElementById('hdr-exchange-rate')?.value || 1) || 1;
        const desc = (document.getElementById('hdr-description')?.value || document.getElementById('inv-notes')?.value || '').trim();
        const finMovNo = (document.getElementById('pur-fin-mov-seq-val')?.textContent || '').trim();
        const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows) ? rows : []).filter(r => r && (r.itemId || r.itemName || r.name) && (parseFloat(r.quantity) || 0) > 0).map(r => {
            const itm = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS.find(x => String(x.id) === String(r.itemId)) : null;
            const name = r.itemName || r.name || (itm ? itm.name : String(r.itemId || 'صنف'));
            const unit = r.selectedUnit || r.unit || (itm ? itm.unit : 'وحدة');
            const qty = parseFloat(r.quantity) || 0;
            const price = parseFloat(r.price) || 0;
            const total = qty * price;
            const whM = (typeof WAREHOUSES !== 'undefined' && Array.isArray(WAREHOUSES)) ? WAREHOUSES.find(w => String(w.id) === String(r.warehouseId || r.warehouse)) : null;
            return { name, unit, qty, price, total, warehouse: whM ? whM.name : (r.warehouse || warehouse), note: r.note || r.notes || '' };
        });
        const itemsSub = activeRows.reduce((s, r) => s + r.total, 0);
        const transport = pn(document.getElementById('t-transport')?.value);
        const labor = pn(document.getElementById('t-labor')?.value);
        const additions = pn(document.getElementById('t-additions-loc')?.value);
        const tax = pn(document.getElementById('t-tax-loc')?.value);
        const discount = pn(document.getElementById('t-discount-loc')?.value);
        const grand = pn(document.getElementById('t-grand-local')?.textContent) || (itemsSub + transport + labor + additions + tax - discount);
        const cashAmt = document.getElementById('toggle-cash')?.classList.contains('active') ? pn(document.getElementById('pay-cash-amount')?.value) : 0;
        const creditAmt = pn(document.getElementById('pay-credit-amount')?.value);
        const transfers = [];
        if (document.getElementById('toggle-bank')?.classList.contains('active')) {
            document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
                const amt = pn(row.querySelector('.bt-amount-input')?.value);
                if (amt > 0) {
                    const sel = row.querySelector('.bt-bank-select');
                    const bank = sel && sel.selectedOptions && sel.selectedOptions[0] ? sel.selectedOptions[0].textContent.trim() : '';
                    const ref = (row.querySelector('.bt-ref-input')?.value || '').trim();
                    const note = (row.querySelector('.bt-note-input')?.value || '').trim();
                    transfers.push({ amt, bank, ref, note });
                }
            });
        }
        const cheques = [];
        if (document.getElementById('toggle-cheque')?.classList.contains('active')) {
            document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
                const amt = pn(row.querySelector('.ch-amount-input')?.value);
                if (amt > 0) {
                    const sel = row.querySelector('.ch-bank-select');
                    const bank = sel && sel.selectedOptions && sel.selectedOptions[0] ? sel.selectedOptions[0].textContent.trim() : '';
                    const no = (row.querySelector('.ch-no-input')?.value || '').trim();
                    const due = (row.querySelector('.ch-date-input')?.value || '').trim();
                    cheques.push({ amt, bank, no, due });
                }
            });
        }
        return { invNo, date, supplier, warehouse, costCenter, currency, curLabel, rate, desc, finMovNo, items: activeRows, itemsSub, transport, labor, additions, tax, discount, grand, cashAmt, creditAmt, transfers, cheques, fn };
    }
    function _openPurchasePrintDoc(title, bodyHtml) {
        const w = window.open('', '_blank', 'width=940,height=780');
        if (!w) { if (typeof showToast === 'function') showToast('⚠️ يرجى السماح بالنوافذ المنبثقة للطباعة', 'warning'); return; }
        w.document.open();
        w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>${title}</title>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&display=swap" rel="stylesheet">
        <style>
            *{box-sizing:border-box} body{margin:0;padding:18px;background:#eef2f7;font-family:'Cairo',Tahoma,sans-serif;color:#0f172a;direction:rtl}
            .sheet{max-width:820px;margin:0 auto;background:#fff;border:2px solid #0f172a;border-radius:12px;padding:22px 26px;box-shadow:0 12px 35px rgba(15,23,42,0.14)}
            .lh{display:flex;align-items:center;justify-content:space-between;gap:14px;border-bottom:3px double #0f172a;padding-bottom:12px;margin-bottom:14px}
            .lh-logo{width:72px;height:72px;object-fit:contain;border-radius:10px;border:1.5px solid #cbd5e1;padding:4px}
            .lh-name{font-size:1.45rem;font-weight:900;color:#0f172a;margin:0}
            .lh-sub{font-size:0.86rem;font-weight:700;color:#475569;margin-top:2px}
            .doc-badge{background:#0f172a;color:#fff;padding:7px 18px;border-radius:10px;font-size:1.05rem;font-weight:900;text-align:center;white-space:nowrap}
            .meta{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:10px;padding:10px 12px;font-size:0.88rem}
            .meta b{color:#0f172a}
            table{width:100%;border-collapse:collapse;margin-bottom:14px;font-size:0.9rem}
            th{background:#0f172a;color:#fff;padding:9px 8px;font-weight:900;border:1px solid #0f172a;text-align:center}
            td{padding:8px;border:1px solid #cbd5e1;font-weight:700;text-align:center}
            tbody tr:nth-child(even){background:#f8fafc}
            .tot-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px}
            .box{border:1.5px solid #0f172a;border-radius:10px;padding:10px 14px;background:#f8fafc}
            .box h4{margin:0 0 8px;font-size:0.95rem;border-bottom:1.5px solid #cbd5e1;padding-bottom:4px}
            .row-kv{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dashed #cbd5e1;font-size:0.88rem;font-weight:800}
            .row-kv.grand{border-bottom:none;border-top:2px solid #0f172a;margin-top:6px;padding-top:8px;font-size:1.08rem;color:#0f172a}
            .sigs{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:28px;text-align:center;font-weight:800;font-size:0.88rem}
            .sigs div{border-top:1.5px dashed #475569;padding-top:8px}
            .actions{max-width:820px;margin:0 auto 14px;display:flex;justify-content:center;gap:10px}
            .actions button{height:40px;padding:0 22px;border-radius:9px;border:none;font-family:'Cairo',sans-serif;font-weight:900;font-size:0.95rem;cursor:pointer}
            .btn-p{background:#0284c7;color:#fff} .btn-c{background:#475569;color:#fff}
            @media print{body{background:#fff;padding:0}.actions{display:none!important}.sheet{border:none;box-shadow:none;max-width:100%;padding:8px}}
        </style></head><body>
        <div class="actions"><button class="btn-p" onclick="window.print()">🖨️ طباعة الآن</button><button class="btn-c" onclick="window.close()">✖ إغلاق</button></div>
        <div class="sheet">${bodyHtml}</div></body></html>`);
        w.document.close();
    }

    window.printPurchaseEznNoPrices = function() {
        const d = _collectCurrentPurchasePrintData();
        if (!d.items.length) { if (typeof showToast === 'function') showToast('⚠️ لا توجد أصناف لطباعة الإذن', 'warning'); return; }
        const co = _getPurchaseCompanyLetterhead();
        const totalQty = d.items.reduce((s, r) => s + r.qty, 0);
        const rowsHtml = d.items.map((r, i) => `<tr><td>${i + 1}</td><td style="text-align:right;font-weight:900;">${r.name}</td><td>${r.unit}</td><td style="font-size:1.02rem;font-weight:900;">${d.fn(r.qty)}</td><td>${r.warehouse}</td><td>${r.note || '—'}</td></tr>`).join('');
        const html = `
            <div class="lh">
                <div style="display:flex;align-items:center;gap:12px;">
                    ${co.logo ? `<img class="lh-logo" src="${co.logo}" alt="logo">` : ''}
                    <div><h1 class="lh-name">${co.name}</h1><div class="lh-sub">${co.sub}${co.phone ? ' | ' + co.phone : ''}${co.address ? ' | ' + co.address : ''}</div></div>
                </div>
                <div class="doc-badge">📄 إذن استلام بضاعة<div style="font-size:0.82rem;font-weight:700;margin-top:2px;direction:ltr;">#${d.invNo}</div></div>
            </div>
            <div class="meta">
                <div><b>المورد:</b> ${d.supplier}</div>
                <div><b>التاريخ:</b> <span style="direction:ltr;display:inline-block;">${d.date}</span></div>
                <div><b>المخزن:</b> ${d.warehouse}</div>
                ${d.costCenter ? `<div><b>مركز الكلفة:</b> ${d.costCenter}</div>` : ''}
                ${d.desc ? `<div style="grid-column:span 2;"><b>البيان:</b> ${d.desc}</div>` : ''}
            </div>
            <table>
                <thead><tr><th style="width:42px;">#</th><th style="text-align:right;">الصنف</th><th style="width:110px;">الوحدة</th><th style="width:110px;">الكمية</th><th style="width:130px;">المخزن</th><th>ملاحظات</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
                <tfoot><tr style="background:#e2e8f0;font-weight:900;"><td colspan="3">إجمالي الأصناف (${d.items.length} صنف)</td><td style="font-size:1.05rem;">${d.fn(totalQty)}</td><td colspan="2">إذن كميات فقط — بدون أسعار</td></tr></tfoot>
            </table>
            <div class="sigs"><div>أمين المخزن</div><div>المستلم / السائق</div><div>الإدارة والاعتماد</div></div>`;
        _openPurchasePrintDoc('إذن بضاعة #' + d.invNo, html);
    };

    window.printPurchaseFinancialStatement = function() {
        const d = _collectCurrentPurchasePrintData();
        if (!d.items.length) { if (typeof showToast === 'function') showToast('⚠️ لا توجد أصناف لطباعة كشف الحساب المالي', 'warning'); return; }
        const co = _getPurchaseCompanyLetterhead();
        const rowsHtml = d.items.map((r, i) => `<tr><td>${i + 1}</td><td style="text-align:right;font-weight:900;">${r.name}</td><td>${r.unit}</td><td>${d.fn(r.qty)}</td><td>${d.fn(r.price)}</td><td style="font-weight:900;">${d.fn(r.total)}</td></tr>`).join('');
        const payLines = [];
        if (d.cashAmt > 0) payLines.push(`<div class="row-kv"><span>💵 سداد نقدي (كاش):</span><span>${d.fn(d.cashAmt)} ${d.curLabel}</span></div>`);
        d.transfers.forEach((t, i) => payLines.push(`<div class="row-kv"><span>🏦 تحويلة #${i + 1} (${t.bank}${t.ref ? ' — ' + t.ref : ''}):</span><span>${d.fn(t.amt)} ${d.curLabel}</span></div>`));
        d.cheques.forEach((c, i) => payLines.push(`<div class="row-kv"><span>✍️ شيك #${i + 1} (${c.bank}${c.no ? ' — ' + c.no : ''}):</span><span>${d.fn(c.amt)} ${d.curLabel}</span></div>`));
        if (d.creditAmt > 0) payLines.push(`<div class="row-kv"><span>⏳ المتبقي آجل على الحساب:</span><span>${d.fn(d.creditAmt)} ${d.curLabel}</span></div>`);
        const totalPaid = d.cashAmt + d.transfers.reduce((s, t) => s + t.amt, 0) + d.cheques.reduce((s, c) => s + c.amt, 0);
        const usdNote = (d.currency === 'SDG' && d.rate > 1) ? `<div class="row-kv"><span>المعادل بالدولار (صرف ${d.fn(d.rate)}):</span><span>$${d.fn(d.grand / d.rate)}</span></div>` : '';
        const html = `
            <div class="lh">
                <div style="display:flex;align-items:center;gap:12px;">
                    ${co.logo ? `<img class="lh-logo" src="${co.logo}" alt="logo">` : ''}
                    <div><h1 class="lh-name">${co.name}</h1><div class="lh-sub">${co.sub}${co.phone ? ' | ' + co.phone : ''}${co.address ? ' | ' + co.address : ''}</div></div>
                </div>
                <div class="doc-badge">📊 كشف حساب مالي<div style="font-size:0.82rem;font-weight:700;margin-top:2px;direction:ltr;">#${d.invNo}</div></div>
            </div>
            <div class="meta">
                <div><b>المورد:</b> ${d.supplier}</div>
                <div><b>التاريخ:</b> <span style="direction:ltr;display:inline-block;">${d.date}</span></div>
                <div><b>العملة / الصرف:</b> ${d.curLabel} (${d.fn(d.rate)})</div>
                <div><b>المخزن:</b> ${d.warehouse}</div>
                <div><b>حركة مالية:</b> ${d.finMovNo && d.finMovNo !== '—' ? d.finMovNo : 'بدون سداد فوري'}</div>
                <div><b>البيان:</b> ${d.desc || '—'}</div>
            </div>
            <table>
                <thead><tr><th style="width:40px;">#</th><th style="text-align:right;">الصنف</th><th style="width:95px;">الوحدة</th><th style="width:85px;">الكمية</th><th style="width:110px;">السعر</th><th style="width:125px;">الجملة</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <div class="tot-grid">
                <div class="box">
                    <h4>💰 الملخص المالي والمصروفات</h4>
                    <div class="row-kv"><span>إجمالي الأصناف:</span><span>${d.fn(d.itemsSub)}</span></div>
                    <div class="row-kv"><span>🚚 الترحيل:</span><span>${d.fn(d.transport)}</span></div>
                    <div class="row-kv"><span>👷 العتالة / العمالة:</span><span>${d.fn(d.labor)}</span></div>
                    <div class="row-kv"><span>➕ إضافات أخرى:</span><span>${d.fn(d.additions)}</span></div>
                    <div class="row-kv"><span>🏛️ الضريبة:</span><span>${d.fn(d.tax)}</span></div>
                    <div class="row-kv"><span>🏷️ الخصم:</span><span>- ${d.fn(d.discount)}</span></div>
                    <div class="row-kv grand"><span>الإجمالي النهائي الصافي:</span><span>${d.fn(d.grand)} ${d.curLabel}</span></div>
                    ${usdNote}
                </div>
                <div class="box">
                    <h4>💳 تفاصيل السداد والحركة المالية</h4>
                    ${payLines.length ? payLines.join('') : '<div class="row-kv"><span>آجل بالكامل على حساب المورد:</span><span>' + d.fn(d.grand) + ' ' + d.curLabel + '</span></div>'}
                    <div class="row-kv grand"><span>إجمالي المدفوع الآن:</span><span>${d.fn(totalPaid)} ${d.curLabel}</span></div>
                </div>
            </div>
            <div class="sigs"><div>المحاسب</div><div>المورد / المستلم</div><div>المدير المالي</div></div>`;
        _openPurchasePrintDoc('كشف حساب مالي #' + d.invNo, html);
    };
})();
