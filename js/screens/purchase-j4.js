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
            if (!s || s.startsWith('PMT-')) return 0;
            if (s.startsWith(prefix)) {
                const n = parseInt(s.slice(prefix.length), 10);
                if (!isNaN(n) && n > 0 && n < 1000000) return n;
            }
            const m = s.match(/^(?:E|M|BP|BR)\d{3,4}(\d{3,6})$/i);
            return m ? (parseInt(m[1], 10) || 0) : 0;
        };
        let max = 0;
        if (window.DB_NEXT_FIN_VOUCHER_NO) {
            const dbSq = extractSeq(window.DB_NEXT_FIN_VOUCHER_NO);
            if (dbSq - 1 > max) max = dbSq - 1;
        }
        ['erp_single_payments_v1', 'erp_payments_v1', 'erp_receipts_v1'].forEach(k => {
            try {
                const arr = JSON.parse(localStorage.getItem(k) || '[]');
                if (Array.isArray(arr)) arr.forEach(v => { if (v && !v.deleted) { const sq = extractSeq(v.voucherNo || v.id); if (sq > max) max = sq; } });
            } catch (_) {}
        });
        try {
            const kn = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
            if (kn.finance && parseInt(kn.finance, 10) > max) max = parseInt(kn.finance, 10);
        } catch (_) {}
        return prefix + String(max + 1).padStart(4, '0');
    };

    window.refreshPurchaseFinSeqBadge = function() {
        const valEl = document.getElementById('pur-fin-mov-seq-val');
        const btnEl = document.getElementById('pur-fin-mov-seq-badge');
        if (!valEl) return;
        let linkedNo = '';
        try {
            const curInvNo = (document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '').trim();
            if (curInvNo && curInvNo !== '---') {
                const invs = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                const found = Array.isArray(invs) ? invs.find(x => x && (String(x.invoiceNo) === curInvNo || String(x.id) === curInvNo)) : null;
                if (found && found.linkedFinancialVoucherNo) linkedNo = String(found.linkedFinancialVoucherNo);
                if (!linkedNo) {
                    const pLog = JSON.parse(localStorage.getItem('erp_single_payments_v1') || '[]');
                    const pFound = Array.isArray(pLog) ? pLog.find(p => p && String(p.refInvoice || '') === curInvNo && p.voucherNo) : null;
                    if (pFound) linkedNo = String(pFound.voucherNo);
                }
            }
        } catch (_) {}
        const targetNo = linkedNo || window.computeNextPurchaseFinVoucherNo();
        valEl.textContent = targetNo;
        if (btnEl) btnEl.dataset.targetVoucher = targetNo;
    };

    window.openPurchaseLinkedFinMovement = function() {
        const btnEl = document.getElementById('pur-fin-mov-seq-badge');
        const vNo = (btnEl && btnEl.dataset.targetVoucher) || document.getElementById('pur-fin-mov-seq-val')?.textContent?.trim() || '';
        if (vNo && vNo !== '---') {
            try {
                const getTK = window.getTenantKey || ((k) => k);
                const keys = [getTK('erp_single_payments_v1'), 'erp_single_payments_v1', 'magaies_vouchers_cache'];
                let found = null;
                for (const k of keys) {
                    const arr = JSON.parse(localStorage.getItem(k) || '[]');
                    if (Array.isArray(arr)) {
                        found = arr.find(p => p && (String(p.voucherNo) === vNo || String(p.id) === vNo));
                        if (found) break;
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

    // Also pull latest live server finance sequence once on load so purchase matches single-cash-payment 100%
    fetch('single-cash-payment.php?api=flash_delta_sync', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{}' })
        .then(r => r.json()).then(d => {
            if (d && Array.isArray(d.records)) {
                localStorage.setItem('erp_single_payments_v1', JSON.stringify(d.records));
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
})();
