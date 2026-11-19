                let displayUSD = false;

                window.addEventListener('DOMContentLoaded', () => {
                    const savedState = localStorage.getItem('magaiees_state_v1');
                    if (savedState) {
                        try {
                            const st = JSON.parse(savedState);
                            if (st.liq !== undefined) document.getElementById('cur-liq').value = st.liq;
                            if (st.usd !== undefined) document.getElementById('cur-usd').value = st.usd;
                            if (st.ar !== undefined) document.getElementById('cur-ar').value = st.ar;
                            if (st.ap !== undefined) document.getElementById('cur-ap').value = st.ap;
                            if (st.tons !== undefined) document.getElementById('cur-tons').value = st.tons;
                            if (st.rebar !== undefined) document.getElementById('cur-rebar').value = st.rebar;
                        } catch (e) {}
                    }
                    updateFromCurtain();
                });

                function setTheme(themeName) {
                    document.documentElement.setAttribute('data-theme', themeName);
                    document.documentElement.classList.remove('light-theme');
                    document.documentElement.classList.add('dark-theme');
                    if (document.body) {
                        document.body.setAttribute('data-theme', themeName);
                        document.body.classList.remove('light-theme');
                        document.body.classList.add('dark-theme');
                    }
                    localStorage.setItem('magaiees_theme', themeName);
                }

                function toggleCurtain() {
                    document.getElementById('curtain-drawer').classList.toggle('-translate-x-full');
                }

                function calcSajanaWeights() {
                    const tons = parseFloat(document.getElementById('sajana-tons').value) || 0;
                    const rebarPrice = parseFloat(document.getElementById('cur-rebar').value) || 420000;
                    document.getElementById('sajana-amount').value = Math.round(tons * rebarPrice).toLocaleString();
                    document.getElementById('res-comm').innerText = Math.round(tons * 135) + ' سيخة';
                    document.getElementById('res-weight').innerText = Math.round(tons * 140) + ' سيخة';
                    document.getElementById('res-bundles').innerText = ((tons * 135) / 15).toFixed(1) + ' شيلة';
                }

                function updateFromCurtain() {
                    const liq = parseFloat(document.getElementById('cur-liq').value) || 0;
                    const usd = parseFloat(document.getElementById('cur-usd').value) || 600;
                    const ar = parseFloat(document.getElementById('cur-ar').value) || 0;
                    const ap = parseFloat(document.getElementById('cur-ap').value) || 0;
                    const tons = parseFloat(document.getElementById('cur-tons').value) || 0;
                    const rebar = parseFloat(document.getElementById('cur-rebar').value) || 420000;

                    localStorage.setItem('magaiees_state_v1', JSON.stringify({ liq, usd, ar, ap, tons, rebar }));

                    const factor = displayUSD ? usd : 1;
                    const unit = displayUSD ? '$' : 'ج.س';
                    window._fmtDualHero = function(sdgVal, unitColor = 'text-emerald-400') {
                        const s = Math.round(sdgVal).toLocaleString();
                        const u = Math.round(sdgVal / (usd || 600)).toLocaleString();
                        return displayUSD
                            ? `$${u} <span class="text-base font-bold ${unitColor}">دولار</span> <span style="font-size:15px;font-weight:900;color:#fde68a;">(≈ ${s} ج.س)</span>`
                            : `${s} <span class="text-base font-bold ${unitColor}">ج.س</span> <span style="font-size:15px;font-weight:900;color:#fde68a;">(≈ $${u} دولار)</span>`;
                    };
                    window._fmtDualSub = function(sdgVal, prefix = '', unitColor = 'text-sky-400') {
                        const s = Math.round(sdgVal).toLocaleString();
                        const u = Math.round(sdgVal / (usd || 600)).toLocaleString();
                        return displayUSD
                            ? `${prefix}$${u} <span class="text-xs font-bold ${unitColor}">$</span> <small style="font-size:11px;font-weight:800;color:#fde68a;">(${s} ج.س)</small>`
                            : `${prefix}${s} <span class="text-xs font-bold ${unitColor}">ج.س</span> <small style="font-size:11px;font-weight:800;color:#fde68a;">($${u})</small>`;
                    };

                    document.getElementById('hdr-usd').innerText = usd.toLocaleString();
                    document.getElementById('hdr-rebar').innerText = Math.round(rebar / factor).toLocaleString();
                    document.getElementById('main-liquidity').innerHTML = window._fmtDualHero(liq, 'text-emerald-400');
                    document.getElementById('sub-cash').innerText = Math.round((liq * 0.42) / factor).toLocaleString();
                    document.getElementById('sub-bank').innerText = Math.round((liq * 0.58) / factor).toLocaleString();
                    document.getElementById('card-ar').innerText = Math.round(ar / factor).toLocaleString();
                    document.getElementById('card-ap').innerText = Math.round(ap / factor).toLocaleString();
                    document.getElementById('net-worth-val').innerText = Math.round((liq + ar - ap) / factor).toLocaleString();

                    document.getElementById('main-tons').innerText = tons;
                    const bars = Math.round(tons * 135);
                    const bundles = Math.round(bars / 15);
                    document.getElementById('main-bars-bundles').innerText = `حديد: ${tons}ط | كراتين وألواح: 1,850 وحدة`;
                    document.getElementById('stock-mkt-val').innerHTML = `${(((tons * rebar) / factor) / 1000000).toFixed(1)}M <small class="exec-unit-tag">${unit}</small>`;

                    document.getElementById('aud-liq').innerText = Math.round(liq / factor).toLocaleString();
                    document.getElementById('aud-stock').innerText = `${tons} طن + 1,850 وحدة`;
                    document.getElementById('aud-ar').innerText = Math.round(ar / factor).toLocaleString();
                    document.getElementById('aud-ap').innerText = Math.round(ap / factor).toLocaleString();

                    document.getElementById('radar-cash-now').innerText = Math.round(liq / factor).toLocaleString() + ' ' + unit;
                    document.getElementById('radar-stock-now').innerText = tons + ' طن';
                    const rebarUnitEl = document.getElementById('hdr-rebar-unit');
                    if (rebarUnitEl) rebarUnitEl.innerText = unit;

                    const activeScope = window._currentDashScope || 'today';
                    const salesRaw = { today: 1890000, week: 9450000, month: 38200000, all: 142000000 };
                    const profitRaw = { today: 348000, week: 1740000, month: 7120000, all: 26400000 };
                    document.getElementById('main-sales').innerHTML = window._fmtDualSub(salesRaw[activeScope] || 1890000, '', 'text-sky-400');
                    document.getElementById('main-profit').innerHTML = window._fmtDualSub(profitRaw[activeScope] || 348000, '+', 'text-emerald-300');

                    calcSajanaWeights();
                    const cfInEl = document.getElementById('cf-in-val');
                    if (cfInEl) cfInEl.innerText = '+' + Math.round(1320000 / factor).toLocaleString() + ' ' + unit;
                    const cfOutEl = document.getElementById('cf-out-val');
                    if (cfOutEl) cfOutEl.innerText = '-' + Math.round(410000 / factor).toLocaleString() + ' ' + unit;
                    const nwEl = document.getElementById('net-worth-val');
                    if (nwEl) nwEl.innerText = Math.round((liq + ar - ap) / factor).toLocaleString() + ' ' + unit;
                    document.querySelectorAll('#radar-feed [data-sdg]').forEach(el => {
                        const raw = parseFloat(el.getAttribute('data-sdg')) || 0;
                        el.innerText = Math.round(raw / factor).toLocaleString() + ' ' + unit;
                    });
                    if (typeof cycleExecutiveSummaryPeriod === 'function') {
                        cycleExecutiveSummaryPeriod(window._execSumPeriodIdx || 0, true);
                    }
                    if (typeof switchPartnersRadar === 'function') {
                        switchPartnersRadar(window._lastPartnersRadarMode || (window._partnersGroupMode === 'supp' ? 'supp_cycle' : 'freq_alert'), window._lastPartnersRadarBtn);
                    }
                    if (typeof switchItemsRadar === 'function') {
                        switchItemsRadar(window._lastItemsRadarMode || 'top_sell', window._lastItemsRadarBtn);
                    }
                    if (typeof switchDebtView === 'function') {
                        switchDebtView(window._debtAgingMode || 'aging', null);
                    }
                    if (typeof switchForecastRadar === 'function') {
                        switchForecastRadar(window._lastForecastMode || 'liquidity', window._lastForecastBtn);
                    }
                }

                function toggleCurrency() {
                    displayUSD = !displayUSD;
                    const txtEl = document.getElementById('currency-btn-txt');
                    if (txtEl) {
                        txtEl.innerText = displayUSD ? 'دولار ⇄' : 'جنيه ⇄';
                    } else {
                        document.getElementById('currency-btn').innerText = displayUSD ? 'دولار ⇄' : 'جنيه ⇄';
                    }
                    updateFromCurtain();
                }

                function onDashBenchmarkItemChange(sel) {
                    if (!sel) return;
                    const price = parseFloat(sel.value) || 420000;
                    const curRebar = document.getElementById('cur-rebar');
                    if (curRebar) curRebar.value = price;
                    updateFromCurtain();
                }

                function _speakDashArabicText(rawText) {
                    const cleanText = String(rawText || '').replace(/ج\.س/g, ' جنيه سوداني ').replace(/\$/g, ' دولار أمريكي ');
                    if (typeof window.speakMdExplanationPanelText === 'function') {
                        window.speakMdExplanationPanelText(cleanText);
                        return;
                    }
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                        const u = new SpeechSynthesisUtterance(cleanText);
                        u.lang = 'ar-SA';
                        u.rate = 1.02;
                        window.speechSynthesis.speak(u);
                    }
                }

                function speakExecutiveDashboardBriefing() {
                    const sal = (document.getElementById('strip-sal-amt')?.innerText || '').trim();
                    const liq = (document.getElementById('strip-liq-amt')?.innerText || '').trim();
                    const prf = (document.getElementById('strip-prf-amt')?.innerText || '').trim();
                    const mkt = (document.getElementById('stock-mkt-val')?.innerText || '').trim();
                    const ar = (document.getElementById('strip-ar-amt')?.innerText || '').trim();
                    const ap = (document.getElementById('strip-ap-amt')?.innerText || '').trim();
                    const text = `الإيجاز القيادي الذكي للوحة التحكم: إجمالي المبيعات ${sal}، وصافي السيولة المتاحة ${liq}، والأرباح المحققة ${prf}. القيمة السوقية الإجمالية لأصولك ومخزونك ${mkt}. ديون العملاء المطلوب تحصيلها ${ar}، ومستحقات الموردين ${ap}.`;
                    _speakDashArabicText(text);
                }

                function startLiveDashVoiceAdvisor() {
                    if (typeof window.toggleDirectLiveVoiceMic === 'function') {
                        window.toggleDirectLiveVoiceMic();
                        return;
                    }
                    const s = document.createElement('script');
                    s.src = 'js/global-professor-ai-chat.js?v=' + Date.now();
                    s.onload = () => { if (typeof window.toggleDirectLiveVoiceMic === 'function') window.toggleDirectLiveVoiceMic(); };
                    document.head.appendChild(s);
                }

                function _activateMiniTab(btn, activeCls) {
                    if (!btn || !btn.parentElement) return;
                    Array.from(btn.parentElement.children).forEach(b => {
                        b.className = 'radar-glow-btn py-1.5 px-1.5 rounded-lg bg-slate-800 text-slate-200 text-xs font-black text-center whitespace-nowrap';
                        b.removeAttribute('style');
                    });
                    btn.className = 'radar-glow-btn active-radar-tab py-1.5 px-1.5 rounded-lg ' + (activeCls || 'bg-emerald-600') + ' text-white text-xs font-black text-center whitespace-nowrap';
                    const bgMap = { 'bg-emerald-600': '#059669', 'bg-rose-600': '#e11d48', 'bg-sky-600': '#0284c7', 'bg-amber-600': '#d97706' };
                    btn.style.cssText = 'background:linear-gradient(180deg,' + (bgMap[activeCls] || '#059669') + ' 0%,#0f172a 140%)!important;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;border:1.5px solid #38bdf8!important;border-bottom:3px solid #38bdf8!important;box-shadow:0 6px 16px rgba(2,132,199,0.45),inset 0 1px 1px rgba(255,255,255,0.35)!important;';
                }

                // Zoom-Proof Rock-Solid Top Pinning for #exec-sticky-command-bar (100% stable at 25%–500% zoom)
                (function _ensureCleanStickyBar() {
                    const ph = document.getElementById('exec-sticky-command-placeholder');
                    const bar = document.getElementById('exec-sticky-command-bar');
                    if (!bar || !ph) return;
                    const syncPin = () => {
                        const pinned = bar.classList.contains('is-pinned-fixed');
                        const refEl = pinned ? ph : bar;
                        const refRect = refEl.getBoundingClientRect();
                        if (refRect.top <= 4) {
                            if (!pinned) {
                                ph.style.height = bar.offsetHeight + 'px';
                                ph.style.display = 'block';
                                bar.classList.add('is-pinned-fixed');
                                if (bar.parentNode !== document.body) document.body.appendChild(bar);
                            }
                            const pRect = ph.getBoundingClientRect();
                            ph.style.height = Math.max(46, bar.offsetHeight) + 'px';
                            bar.style.setProperty('position', 'fixed', 'important');
                            bar.style.setProperty('top', '4px', 'important');
                            bar.style.setProperty('left', Math.round(pRect.left) + 'px', 'important');
                            bar.style.setProperty('width', Math.round(pRect.width) + 'px', 'important');
                            bar.style.setProperty('max-width', Math.round(pRect.width) + 'px', 'important');
                            bar.style.setProperty('z-index', '999950', 'important');
                        } else if (pinned) {
                            bar.classList.remove('is-pinned-fixed');
                            if (ph.parentNode && bar.parentNode !== ph.parentNode) ph.parentNode.insertBefore(bar, ph.nextSibling);
                            bar.style.removeProperty('position');
                            bar.style.removeProperty('top');
                            bar.style.removeProperty('left');
                            bar.style.removeProperty('max-width');
                            bar.style.setProperty('width', '100%', 'important');
                            ph.style.display = 'none';
                        }
                    };
                    window.addEventListener('scroll', syncPin, { passive: true, capture: true });
                    document.addEventListener('scroll', syncPin, { passive: true, capture: true });
                    window.addEventListener('resize', syncPin, { passive: true });
                    if (window.visualViewport) {
                        window.visualViewport.addEventListener('resize', syncPin, { passive: true });
                        window.visualViewport.addEventListener('scroll', syncPin, { passive: true });
                    }
                    if (typeof ResizeObserver !== 'undefined') {
                        new ResizeObserver(syncPin).observe(ph);
                        new ResizeObserver(syncPin).observe(bar);
                    }
                    setTimeout(syncPin, 60);
                })();
                window._hiddenBrokerIds = JSON.parse(localStorage.getItem('magaiees_hidden_broker_ids') || '[]');
                function toggleMarketBrokerTicker(show) {
                    const wrap = document.getElementById('market-broker-ticker-wrap');
                    const pill = document.getElementById('market-broker-restore-pill');
                    const ordRow = document.getElementById('market-broker-orders-row');
                    const offRow = document.getElementById('market-broker-offers-row');
                    window._userManuallyClosedBroker = !show;
                    localStorage.setItem('magaiees_hide_broker_ticker', show ? '0' : '1');
                    if (wrap) {
                        wrap.classList.toggle('is-broker-hidden', !show);
                        wrap.style.setProperty('display', show ? 'flex' : 'none', 'important');
                    }
                    if (show) {
                        _savedBrkPrefs.offersLocked = false;
                        _savedBrkPrefs.ordersLocked = false;
                        if (offRow) { offRow.classList.remove('is-row-hidden'); offRow.style.setProperty('display', 'flex', 'important'); }
                        if (ordRow) { ordRow.classList.remove('is-row-hidden'); ordRow.style.setProperty('display', 'flex', 'important'); }
                    }
                    if (pill) {
                        pill.style.display = 'none';
                    }
                }
                if (localStorage.getItem('magaiees_hide_broker_ticker') === '1') {
                    setTimeout(() => toggleMarketBrokerTicker(false), 30);
                }
                function _safeBrkSet(k, v) {
                    try { localStorage.setItem(k, v); } catch (e) {
                        try { ['erp_auto_backups', 'erp_audit_logs', 'erp_items_v1'].forEach(rk => localStorage.removeItem(rk)); localStorage.setItem(k, v); } catch (e2) {}
                    }
                }
                const _savedBrkPrefs = (() => {
                    try {
                        const m = document.cookie.match(/(?:^|;\s*)magaiees_broker_prefs_v2=([^;]+)/);
                        if (m && m[1]) return JSON.parse(decodeURIComponent(m[1])) || {};
                    } catch (e) {}
                    try { return JSON.parse(localStorage.getItem('magaiees_broker_prefs_v2') || '{}') || {}; } catch (e) { return {}; }
                })();
                window._brokerOffersSec = Math.max(20, Math.min(200, parseInt(_savedBrkPrefs.offersSec ?? '160', 10) || 160));
                window._brokerOrdersSec = Math.max(20, Math.min(200, parseInt(_savedBrkPrefs.ordersSec ?? '200', 10) || 200));
                window._brokerFontStep = Math.max(-3, Math.min(10, parseInt(_savedBrkPrefs.fontStep ?? localStorage.getItem('magaiees_broker_font_step') ?? '2', 10)));
                if (isNaN(window._brokerFontStep)) window._brokerFontStep = 2;
                window._brokerFontFamily = _savedBrkPrefs.fontFamily || localStorage.getItem('magaiees_broker_font_family') || 'Cairo, Tajawal, sans-serif';
                window._brokerMaxAgeHours = parseInt(_savedBrkPrefs.maxAgeHours ?? localStorage.getItem('magaiees_broker_max_age_h') ?? '24', 10) || 24;
                window._brokerHideNames = _savedBrkPrefs.hideNames !== undefined ? !!_savedBrkPrefs.hideNames : (localStorage.getItem('magaiees_broker_hide_names') === '1');
                window._brokerDockMode = (_savedBrkPrefs.dockMode === 'bottom' || localStorage.getItem('magaiees_broker_dock_mode') === 'bottom') ? 'bottom' : 'top';
                function saveBrokerPrefs(showNotice) {
                    const prefs = {
                        fontStep: window._brokerFontStep ?? 2,
                        fontFamily: window._brokerFontFamily || 'Cairo, Tajawal, sans-serif',
                        offersSec: window._brokerOffersSec || 160,
                        ordersSec: window._brokerOrdersSec || 200,
                        maxAgeHours: window._brokerMaxAgeHours || 24,
                        hideNames: !!window._brokerHideNames,
                        myItemsFilter: !!window._brokerMyItemsFilter,
                        dockMode: window._brokerDockMode === 'bottom' ? 'bottom' : 'top',
                        offersLocked: document.getElementById('market-broker-offers-row')?.style.display === 'none',
                        ordersLocked: document.getElementById('market-broker-orders-row')?.style.display === 'none',
                        hiddenIds: window._hiddenBrokerIds || []
                    };
                    document.cookie = 'magaiees_broker_prefs_v2=' + encodeURIComponent(JSON.stringify(prefs)) + '; path=/; max-age=31536000; SameSite=Lax';
                    _safeBrkSet('magaiees_broker_prefs_v2', JSON.stringify(prefs));
                    _safeBrkSet('magaiees_broker_font_step', String(prefs.fontStep));
                    _safeBrkSet('magaiees_broker_offers_sec', String(prefs.offersSec));
                    _safeBrkSet('magaiees_broker_orders_sec', String(prefs.ordersSec));
                    _safeBrkSet('magaiees_broker_max_age_h', String(prefs.maxAgeHours));
                    _safeBrkSet('magaiees_broker_dock_mode', prefs.dockMode);
                    fetch('api/market_broker_ticker.php?action=save_prefs', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(prefs)
                    }).then(() => {
                        if (showNotice && typeof window.showToast === 'function') window.showToast('✅ تم حفظ إعدادات شريطي البورصة (المقاس والسرعة والموقع) نهائياً', 'success');
                    }).catch(() => {});
                }
                function formatBrokerFontSizeLabel(step) {
                    const s = Math.max(-3, Math.min(10, step || 0));
                    const px = Math.round(13.2 + s * 1.6);
                    return px + 'px (' + (s === 0 ? '0' : (s > 0 ? '+' + s : s)) + ')';
                }
                function formatBrokerSpeedLabel(which) {
                    const isOrd = (which === 'orders' || (which === undefined && window._brkActiveScope === 'order'));
                    const sec = isOrd ? (window._brokerOrdersSec || 200) : (window._brokerOffersSec || 160);
                    const lvl = Math.max(1, Math.min(10, Math.round((220 - sec) / 20) + 1));
                    return (isOrd ? 'سرعة الطلبات ' : 'سرعة العروض ') + lvl + '/10';
                }
                function adjustMarketBrokerFont(delta) {
                    window._brokerFontStep = Math.max(-3, Math.min(10, (window._brokerFontStep || 0) + delta));
                    _safeBrkSet('magaiees_broker_font_step', String(window._brokerFontStep));
                    const lbl = document.getElementById('brk-font-step-lbl');
                    if (lbl) lbl.textContent = formatBrokerFontSizeLabel(window._brokerFontStep);
                    applyMarketBrokerDock(window._brokerDockMode || 'top');
                    saveBrokerPrefs();
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function toggleMarketBrokerNames() {
                    window._brokerHideNames = !window._brokerHideNames;
                    _safeBrkSet('magaiees_broker_hide_names', window._brokerHideNames ? '1' : '0');
                    const btn = document.getElementById('brk-toggle-names-btn');
                    if (btn) {
                        btn.innerHTML = window._brokerHideNames ? '👤 الأسماء: مخفية (عروض صافية)' : '👤 الأسماء: ظاهرة';
                        btn.style.background = window._brokerHideNames ? '#059669' : '#1e293b';
                    }
                    saveBrokerPrefs();
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function applyMarketBrokerDock(mode) {
                    const w = document.getElementById('market-broker-ticker-wrap');
                    if (!w) return;
                    const next = (mode === 'bottom') ? 'bottom' : 'top';
                    window._brokerDockMode = next;
                    _safeBrkSet('magaiees_broker_dock_mode', next);
                    const qaBar = document.getElementById('qa-bar-container');
                    const isHidden = localStorage.getItem('magaiees_hide_broker_ticker') === '1' || window._userManuallyClosedBroker;
                    const disp = isHidden ? 'none!important' : 'flex';
                    if (next === 'bottom') {
                        w.style.cssText = `position:fixed;bottom:2px;left:6px;right:calc(var(--sb-collapsed, 62px) + 6px);z-index:9990;display:${disp};flex-direction:column;gap:6px;padding:0;margin:0;background:#020617;border-radius:7px;`;
                    } else {
                        if (qaBar && qaBar.parentNode && w.nextElementSibling !== qaBar) qaBar.parentNode.insertBefore(w, qaBar);
                        w.style.cssText = `display:${disp};flex-direction:column;gap:6px;padding:0;margin:0;z-index:9990;background:#020617;border-radius:7px;`;
                    }
                    if (isHidden) w.classList.add('is-broker-hidden');
                    const btn = document.getElementById('brk-dock-pos-btn');
                    if (btn) btn.innerHTML = next === 'top' ? '📍 الموقع: أعلى الشاشة ⇄' : '📍 الموقع: أسفل الشاشة ⇄';
                }
                function toggleMarketBrokerAllScreens() {
                    const cur = localStorage.getItem('magaiees_broker_all_screens') !== '0';
                    const next = !cur;
                    _safeBrkSet('magaiees_broker_all_screens', next ? '1' : '0');
                    const btn = document.getElementById('brk-all-screens-btn');
                    if (btn) {
                        btn.innerHTML = next ? '🌐 الظهور: كل الشاشات ⇄' : '🖥️ الظهور: الرئيسية فقط ⇄';
                        btn.style.background = next ? '#059669' : '#1e293b';
                    }
                    saveBrokerPrefs(true);
                }
                function toggleMarketBrokerDock() {
                    const cur = window._brokerDockMode === 'bottom' ? 'bottom' : 'top';
                    applyMarketBrokerDock(cur === 'top' ? 'bottom' : 'top');
                    saveBrokerPrefs(true);
                }
                setTimeout(() => {
                    applyMarketBrokerDock(window._brokerDockMode || 'top');
                    if (_savedBrkPrefs.offersLocked) { const r = document.getElementById('market-broker-offers-row'); if (r) r.style.display = 'none'; }
                    if (_savedBrkPrefs.ordersLocked) { const r = document.getElementById('market-broker-orders-row'); if (r) r.style.display = 'none'; }
                }, 20);
                function getBrokerSpeedPxPerSec(secVal) {
                    const s = Math.max(20, Math.min(200, secVal || 80));
                    const lvl = Math.max(1, Math.min(10, Math.round((220 - s) / 20) + 1));
                    const table = [18, 30, 44, 60, 78, 98, 120, 145, 175, 210];
                    return table[lvl - 1] || 78;
                }
                function applyMarketBrokerSpeed() {
                    const spLbl = document.getElementById('brk-speed-step-lbl');
                    if (spLbl) spLbl.textContent = formatBrokerSpeedLabel(window._brkActiveScope === 'order' ? 'orders' : 'offers');
                    const offLbl = document.getElementById('brk-speed-offers-lbl');
                    if (offLbl) offLbl.textContent = formatBrokerSpeedLabel('offers');
                    const ordLbl = document.getElementById('brk-speed-orders-lbl');
                    if (ordLbl) ordLbl.textContent = formatBrokerSpeedLabel('orders');
                    const track = document.getElementById('market-broker-ticker-track');
                    const ordTrack = document.getElementById('market-broker-orders-track');
                    if (track) track.style.setProperty('--brk-dur', Math.max(38, Math.round((window._brokerOffersSec || 120) * 0.65)) + 's');
                    if (ordTrack) ordTrack.style.setProperty('--brk-dur', Math.max(42, Math.round((window._brokerOrdersSec || 140) * 0.65)) + 's');
                }
                function adjustMarketBrokerSpeed(dir, which) {
                    if (which === 'orders') {
                        window._brokerOrdersSec = Math.max(20, Math.min(200, (window._brokerOrdersSec || 200) + (dir > 0 ? -20 : 20)));
                        localStorage.setItem('magaiees_broker_orders_sec', String(window._brokerOrdersSec));
                    } else {
                        window._brokerOffersSec = Math.max(20, Math.min(200, (window._brokerOffersSec || 160) + (dir > 0 ? -20 : 20)));
                        localStorage.setItem('magaiees_broker_offers_sec', String(window._brokerOffersSec));
                    }
                    applyMarketBrokerSpeed();
                    saveBrokerPrefs(true);
                }
                async function reportBrokerItem(id, senderName, phone, ev) {
                    if (ev) ev.stopPropagation();
                    try {
                        const res = await fetch('api/market_broker_ticker.php?action=report', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id, sender_name: senderName, phone })
                        }).then(r => r.json());
                        const pts = res.black_points || 1;
                        alert(res.banned ? `🚫 تم تسجيل النقطة السوداء رقم (${pts}/3) وحظر عروض ورسائل (${senderName}) تلقائياً من البورصة!` : `⚠️ تم تسجيل نقطة سوداء (${pts}/3) ضد (${senderName}). عند وصوله إلى 3 بلاغات يتم حجبه تلقائياً.`);
                    } catch (e) {}
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function hideSingleBrokerOffer(id, ev) {
                    if (ev) ev.stopPropagation();
                    if (!window._hiddenBrokerIds.includes(id)) window._hiddenBrokerIds.push(id);
                    localStorage.setItem('magaiees_hidden_broker_ids', JSON.stringify(window._hiddenBrokerIds));
                    saveBrokerPrefs(false);
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                async function loadMarketBrokerTicker(forceNow) {
                    if (!forceNow && (document.hidden || navigator.onLine === false || window._brokerFetchInFlight || window._userManuallyClosedBroker)) return;
                    const track = document.getElementById('market-broker-ticker-track');
                    const ordTrack = document.getElementById('market-broker-orders-track');
                    const wrap = document.getElementById('market-broker-ticker-wrap');
                    if (wrap && wrap.style.display === 'none' && !window._userManuallyClosedBroker) {
                        wrap.style.display = 'flex';
                    }
                    if (!track) return;
                    applyMarketBrokerSpeed();
                    window._brokerFetchInFlight = true;
                    try {
                        const reqs = [fetch('api/market_broker_ticker.php?action=list&_t=' + Date.now()).then(r => r.json())];
                        if ((location.hostname === 'localhost' || location.hostname === '127.0.0.1') && (!window._lastCloudBrokerPull || (Date.now() - window._lastCloudBrokerPull > 10000))) {
                            window._lastCloudBrokerPull = Date.now();
                            reqs.push(fetch('https://c.elsajana.com/api/market_broker_ticker.php?action=list&_t=' + Date.now(), { signal: AbortSignal.timeout ? AbortSignal.timeout(4000) : undefined }).then(r => r.json()));
                        }
                        const settled = await Promise.allSettled(reqs);
                        const mergedMap = new Map();
                        if (!window._brokerPrefsSynced && settled[0]?.status === 'fulfilled' && settled[0].value?.prefs) {
                            window._brokerPrefsSynced = true;
                            const p = settled[0].value.prefs;
                            if (typeof p.fontStep === 'number') { window._brokerFontStep = p.fontStep; _safeBrkSet('magaiees_broker_font_step', String(p.fontStep)); }
                            if (typeof p.offersSec === 'number') { window._brokerOffersSec = p.offersSec; _safeBrkSet('magaiees_broker_offers_sec', String(p.offersSec)); }
                            if (typeof p.ordersSec === 'number') { window._brokerOrdersSec = p.ordersSec; _safeBrkSet('magaiees_broker_orders_sec', String(p.ordersSec)); }
                            if (typeof p.maxAgeHours === 'number') { window._brokerMaxAgeHours = p.maxAgeHours; _safeBrkSet('magaiees_broker_max_age_h', String(p.maxAgeHours)); }
                            if (typeof p.hideNames === 'boolean') { window._brokerHideNames = p.hideNames; _safeBrkSet('magaiees_broker_hide_names', p.hideNames ? '1' : '0'); }
                            if (typeof p.myItemsFilter === 'boolean') { window._brokerMyItemsFilter = p.myItemsFilter; _safeBrkSet('magaiees_broker_my_items_only', p.myItemsFilter ? '1' : '0'); }
                            if (typeof p.dockMode === 'string') { applyMarketBrokerDock(p.dockMode); }
                            document.cookie = 'magaiees_broker_prefs_v2=' + encodeURIComponent(JSON.stringify(p)) + '; path=/; max-age=31536000; SameSite=Lax';
                            _safeBrkSet('magaiees_broker_prefs_v2', JSON.stringify(p));
                            applyMarketBrokerSpeed();
                            window._lastBrokerSig = '';
                        }
                        settled.forEach(s => {
                            if (s.status === 'fulfilled' && s.value) {
                                if (Array.isArray(s.value.items)) {
                                    s.value.items.forEach(it => {
                                        const k = (it.text || '') + '|' + (it.type || '');
                                        if (!mergedMap.has(k)) mergedMap.set(k, it);
                                    });
                                }
                            }
                        });
                        const maxAgeMs = (window._brokerMaxAgeHours || 24) * 3600 * 1000;
                        const nowMs = Date.now();
                        const isWithinMaxAge = (it) => {
                            if (it.ts && Number(it.ts) > 1000000000) return (nowMs - Number(it.ts) * 1000) <= maxAgeMs;
                            return true;
                        };
                        let rawItems = Array.from(mergedMap.values()).filter(x => !window._hiddenBrokerIds.includes(x.id) && isWithinMaxAge(x));
                        if (!rawItems.length && mergedMap.size > 0) {
                            rawItems = Array.from(mergedMap.values()).filter(x => !window._hiddenBrokerIds.includes(x.id));
                        }
                        if (window._brokerMyItemsFilter === undefined) window._brokerMyItemsFilter = localStorage.getItem('magaiees_broker_my_items_only') === '1';
                        const isMyItemMatch = (txt) => {
                            if (!window._brokerMyItemsFilter) return true;
                            const s = String(txt || '');
                            if (/ألومنيوم|الومنيوم|زجاج|سيكوريت|كلادينج|سباكة|خلاطات|بوهية|دهان|أخشاب|أبلكاش/i.test(s) && !/سيخ|حديد|كمر|أسمنت|اسمنت|مواسير|صاج|سلك|زوايا/i.test(s)) return false;
                            return true;
                        };
                        const items = rawItems.filter(x => isMyItemMatch(x.text));
                        window._brokerItemsList = items;
                        const ageStageKey = items.map(x => {
                            const am = (x.ts && Number(x.ts) > 1000000000) ? Math.max(0, (nowMs - Number(x.ts) * 1000) / 60000) : 999;
                            return x.id + ':' + x.text + ':' + (am <= 90 ? 'R' : (am <= 180 ? 'O' : (am <= 270 ? 'G' : 'N')));
                        });
                        const sig = JSON.stringify(ageStageKey) + ':' + window._brokerMyItemsFilter + ':' + window._brokerHideNames + ':' + window._brokerFontStep + ':' + window._brokerMaxAgeHours;
                        if (window._lastBrokerSig === sig && track.innerHTML.trim() !== '') return;
                        window._lastBrokerSig = sig;
                        let offers = items.filter(x => x.type !== 'order');
                        let orders = items.filter(x => x.type === 'order');
                        if (!offers.length && mergedMap.size > 0) offers = Array.from(mergedMap.values()).filter(x => !window._hiddenBrokerIds.includes(x.id) && x.type !== 'order' && isMyItemMatch(x.text));
                        if (!orders.length && mergedMap.size > 0) orders = Array.from(mergedMap.values()).filter(x => !window._hiddenBrokerIds.includes(x.id) && x.type === 'order' && isMyItemMatch(x.text));
                        document.getElementById('market-broker-offers-row')?.classList.remove('is-row-hidden');
                        document.getElementById('market-broker-orders-row')?.classList.remove('is-row-hidden');
                        const newestOfferId = offers[0]?.id;
                        const newestOrderId = orders[0]?.id;
                        const formatElapsed = (it) => {
                            const now = Date.now();
                            let diffMin = 0;
                            if (it.ts && Number(it.ts) > 1000000000) {
                                diffMin = Math.max(0, Math.floor((now - Number(it.ts) * 1000) / 60000));
                            } else if (it.time && /^\d{1,2}:\d{2}$/.test(String(it.time).trim())) {
                                const [hh, mm] = String(it.time).trim().split(':').map(Number);
                                const d = new Date();
                                d.setHours(hh, mm, 0, 0);
                                if (d.getTime() > now) d.setDate(d.getDate() - 1);
                                diffMin = Math.max(0, Math.floor((now - d.getTime()) / 60000));
                            }
                            if (diffMin <= 2) return 'منذ لحظات';
                            if (diffMin < 60) return `منذ ${diffMin} دقيقة`;
                            const totalHrs = Math.floor(diffMin / 60);
                            const days = Math.floor(totalHrs / 24);
                            const hrs = totalHrs % 24;
                            const fmtHr = (h) => h === 1 ? 'ساعة' : (h === 2 ? 'ساعتين' : (h <= 10 ? `${h} ساعات` : `${h} ساعة`));
                            const fmtDay = (d) => d === 1 ? 'يوم' : (d === 2 ? 'يومين' : `${d} أيام`);
                            if (days === 0) return `منذ ${fmtHr(hrs)}`;
                            return hrs > 0 ? `منذ ${fmtDay(days)} و${fmtHr(hrs)}` : `منذ ${fmtDay(days)}`;
                        };
                        const renderPill = (it, idx) => {
                            const isOrd = it.type === 'order';
                            const txtCls = (isOrd ? 'brk-ord-txt-' : 'brk-off-txt-') + (idx % 3);
                            const hdrCls = isOrd ? 'brk-pill-hdr-ord' : 'brk-pill-hdr-off';
                            const timeCls = isOrd ? 'brk-ord-time' : 'brk-off-time';
                            const getAgeMin = (item) => {
                                if (item.ts && Number(item.ts) > 1000000000) return Math.max(0, (Date.now() - Number(item.ts) * 1000) / 60000);
                                return 999;
                            };
                            const ageMin = (it.id === window._justAddedBrokerId) ? 0 : getAgeMin(it);
                            const isMasked = it.privacy === 'masked';
                            const badge = isOrd ? '🟠 مطلوب' : '🟢 عرض';
                            const cleanSender = String(it.sender_name || '').replace(/\s*#?\d+\s*🔒?/g, '').trim();
                            const fPx = (13.2 + (window._brokerFontStep || 0) * 1.6).toFixed(1);
                            const fFam = window._brokerFontFamily || 'Cairo, Tajawal, sans-serif';
                            let newTag = '';
                            if (ageMin <= 270) {
                                const stageBg = ageMin <= 90
                                    ? 'background:#dc2626!important;box-shadow:0 0 10px rgba(220,38,38,0.85)!important;border:1.5px solid #fca5a5!important;'
                                    : (ageMin <= 180
                                        ? 'background:#f97316!important;box-shadow:0 0 10px rgba(249,115,22,0.85)!important;border:1.5px solid #fdba74!important;'
                                        : 'background:#16a34a!important;box-shadow:0 0 10px rgba(22,163,74,0.85)!important;border:1.5px solid #86efac!important;');
                                newTag = `<span style="display:inline-flex;align-items:center;color:#fff!important;-webkit-text-fill-color:#fff!important;padding:1px 8px;border-radius:999px;font-size:${(parseFloat(fPx)*0.78).toFixed(1)}px;font-weight:900;${stageBg}">جديد</span>`;
                            }
                            const hdrTxt = (window._brokerHideNames || isMasked || !cleanSender || /تاجر معتمد|مورد معتمد|مشتري معتمد/.test(cleanSender)) ? `${badge}:` : `${badge} (${cleanSender}):`;
                            const elapsedTxt = formatElapsed(it);
                            const tailTxt = `<small class="${timeCls}" style="font-size:${(parseFloat(fPx)*0.88).toFixed(1)}px;font-weight:700;">⏱️ ${elapsedTxt}</small>`;
                            const pillBrd = isOrd ? 'rgba(52,211,153,0.45)' : 'rgba(253,224,71,0.48)';
                            const pillBg = isOrd ? 'rgba(6,78,59,0.52)' : 'rgba(30,58,138,0.52)';
                            return `<span onclick="openBrokerDealAction('${it.sender_name}', '${it.text}', '${it.deal_code || ''}', '${it.privacy || 'public'}', '${it.phone || ''}')" class="${txtCls}" style="display:inline-flex;align-items:center;gap:7px;padding:3px 12px;background:${pillBg};border:1px solid ${pillBrd};border-radius:999px;font-family:${fFam}!important;font-size:${fPx}px!important;font-weight:800;letter-spacing:0.2px;cursor:pointer;line-height:1.3;"><button type="button" onclick="hideSingleBrokerOffer('${it.id}', event)" title="إخفاء" style="width:14px;height:14px;padding:0;background:rgba(225,29,72,0.22);color:#fda4af;border:none;border-radius:50%;font-size:9px;font-weight:900;line-height:1;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;">✕</button>${newTag}<span class="${hdrCls}" style="font-family:${fFam}!important;font-size:${fPx}px!important;font-weight:900;">${hdrTxt}</span><span class="${txtCls}" style="font-family:${fFam}!important;font-size:${fPx}px!important;font-weight:800;">${it.text}</span>${tailTxt}</span><span style="color:#fbbf24;font-size:13px;padding:0 6px;user-select:none;">❖</span>`;
                        };
                        const buildSeamlessHalves = (arr) => {
                            if (!arr.length) return '';
                            let base = [...arr];
                            while (base.length < 4) base = base.concat(arr);
                            const seg = base.map(renderPill).join('');
                            return `<div style="display:inline-flex;align-items:center;gap:10px;padding-left:18px;">${seg}</div><div aria-hidden="true" style="display:inline-flex;align-items:center;gap:10px;padding-left:18px;">${seg}</div>`;
                        };
                        track.innerHTML = buildSeamlessHalves(offers);
                        if (ordTrack) ordTrack.innerHTML = buildSeamlessHalves(orders);
                        requestAnimationFrame(() => applyMarketBrokerSpeed());
                        if (document.getElementById('broker-post-modal')?.style.display === 'flex') renderBrokerModalTable();
                    } catch (e) {} finally {
                        window._brokerFetchInFlight = false;
                    }
                }
                function toggleBrokerMyItemsFilter() {
                    window._brokerMyItemsFilter = !window._brokerMyItemsFilter;
                    localStorage.setItem('magaiees_broker_my_items_only', window._brokerMyItemsFilter ? '1' : '0');
                    const btn = document.getElementById('brk-my-items-btn');
                    if (btn) {
                        btn.innerHTML = window._brokerMyItemsFilter ? '🎯 ذكاء الأصناف: مطابق لنشاطي' : '🌐 ذكاء الأصناف: كل السوق';
                        btn.style.background = window._brokerMyItemsFilter ? '#059669' : '#1e293b';
                    }
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function openBrokerDealAction(sender, text, code, privacy, phone) {
                    const dealCode = code || '101';
                    let em = document.getElementById('broker-escrow-modal');
                    if (!em) {
                        em = document.createElement('div');
                        em.id = 'broker-escrow-modal';
                        em.style.cssText = 'position:fixed;inset:0;z-index:2147483635;background:rgba(2,6,23,0.88);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:12px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
                        document.body.appendChild(em);
                    }
                    const isMasked = privacy === 'masked' || window._brokerHideNames;
                    em.innerHTML = `<div style="width:100%;max-width:620px;background:linear-gradient(160deg,#0f172a,#020617);border:2.5px solid #10b981;border-radius:20px;padding:16px 18px;color:#fff;box-shadow:0 24px 60px rgba(0,0,0,0.92);display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1.5px solid rgba(16,185,129,0.35);">
                            <strong style="font-size:16px;color:#34d399!important;-webkit-text-fill-color:#34d399!important;">🛡️ وساطة وضمان منصة السيطرة المعتمد (صفقة #${dealCode})</strong>
                            <button type="button" onclick="document.getElementById('broker-escrow-modal').style.display='none'" style="background:#e11d48;color:#fff;border:none;border-radius:8px;padding:4px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                        </div>
                        <div style="padding:10px 12px;border-radius:12px;background:rgba(15,23,42,0.9);border:1.5px solid #38bdf8;font-size:13.5px;font-weight:900;">
                            <div style="color:#fde047!important;-webkit-text-fill-color:#fde047!important;margin-bottom:4px;">${isMasked ? '🔒 الهوية محجوبة ومحمية بالكامل بواسطة السيطرة (كود #' + dealCode + ')' : '👤 الطرف العارض: ' + sender}</div>
                            <div style="color:#fff!important;-webkit-text-fill-color:#fff!important;">📦 التفاصيل: ${text}</div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;font-size:11.5px;font-weight:900;text-align:center;">
                            <div style="padding:8px;border-radius:10px;background:rgba(16,185,129,0.14);border:1px solid #10b981;color:#6ee7b7!important;-webkit-text-fill-color:#6ee7b7!important;">1️⃣ ربط سري وتثبيت الكمية والسعر فوراً</div>
                            <div style="padding:8px;border-radius:10px;background:rgba(2,132,199,0.14);border:1px solid #38bdf8;color:#bae6fd!important;-webkit-text-fill-color:#bae6fd!important;">2️⃣ ضمان مالي عبر حساب السيطرة الضامن</div>
                            <div style="padding:8px;border-radius:10px;background:rgba(245,158,11,0.14);border:1px solid #f59e0b;color:#fde68a!important;-webkit-text-fill-color:#fde68a!important;">3️⃣ فحص واستلام البضاعة وتحرير السداد</div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <input id="escrow-buyer-name" type="text" placeholder="اسمك / شركتك (محفوظ سرياً لدى السيطرة)" style="height:36px;border-radius:9px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 10px;font-size:12.5px;font-weight:800;">
                            <input id="escrow-buyer-phone" type="text" placeholder="رقم واتسابك للتأكيد السري" value="249123005622" style="height:36px;border-radius:9px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 10px;font-size:12.5px;font-weight:800;direction:ltr;">
                        </div>
                        <input id="escrow-buyer-note" type="text" placeholder="الكمية المطلوبة أو ملاحظة التسليم (مثلاً: أرغب بشراء الكمية كاملة تسليم فوري بضمان السيطرة)..." style="height:36px;border-radius:9px;background:#1e293b;color:#fff;border:1.5px solid #38bdf8;padding:0 10px;font-size:12.5px;font-weight:800;">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="button" onclick="executeProtectedBrokerDeal('${dealCode}', '${text.replace(/'/g, '')}')" style="flex:2;height:40px;border-radius:10px;background:linear-gradient(135deg,#059669,#0284c7);color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #fde047;font-size:13.5px;font-weight:900;cursor:pointer;">🛡️ اعتماد وربط الصفقة سرياً بضمان منصة السيطرة</button>
                            ${!isMasked ? `<button type="button" onclick="document.getElementById('broker-escrow-modal').style.display='none';openAiPartnerWhatsAppModal('${sender}', '${text.replace(/'/g, '')}');" style="flex:1;height:40px;border-radius:10px;background:#1e293b;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #f59e0b;font-size:12.5px;font-weight:900;cursor:pointer;">📞 تواصل مباشر</button>` : ''}
                        </div>
                    </div>`;
                    em.style.display = 'flex';
                }
                async function executeProtectedBrokerDeal(dealCode, dealText) {
                    const bName = (document.getElementById('escrow-buyer-name')?.value || 'تاجر معتمد عبر السيطرة').trim();
                    const bPhone = (document.getElementById('escrow-buyer-phone')?.value || '249123005622').trim();
                    const bNote = (document.getElementById('escrow-buyer-note')?.value || 'موافق على الشراء/التنفيذ الفوري بضمان منصة السيطرة').trim();
                    document.getElementById('broker-escrow-modal').style.display = 'none';
                    try {
                        await fetch('api/market_broker_ticker.php?action=add', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ text: 'تواصل ' + dealCode, sender_name: bName, phone: bPhone, note: bNote })
                        });
                    } catch (e) {}
                    openAiPartnerWhatsAppModal(`غرفة ضمان السيطرة #${dealCode}`, `🛡️ عقد ضمان وساطة السيطرة للصفقة السرية #${dealCode}\n• الصنف: ${dealText}\n• طالب التنفيذ: ${bName} (${bPhone})\n• التوجيه: ${bNote}\n• الضامن: منصة السيطرة (ربط سري كامل وحماية للطرفين)`);
                }
                function toggleSingleBrokerRow(rowId, btn) {
                    const r = document.getElementById(rowId);
                    if (!r) return;
                    const show = r.style.display === 'none' || r.classList.contains('is-row-hidden');
                    r.classList.toggle('is-row-hidden', !show);
                    r.style.setProperty('display', show ? 'flex' : 'none', 'important');
                    if (btn) btn.textContent = show ? '🔒 قفل الشريط' : '🔓 إظهار الشريط';
                    const r1 = document.getElementById('market-broker-offers-row');
                    const r2 = document.getElementById('market-broker-orders-row');
                    const b1Hidden = !r1 || r1.style.display === 'none' || r1.classList.contains('is-row-hidden');
                    const b2Hidden = !r2 || r2.style.display === 'none' || r2.classList.contains('is-row-hidden');
                    if (b1Hidden && b2Hidden) toggleMarketBrokerTicker(false);
                    else {
                        const wrap = document.getElementById('market-broker-ticker-wrap');
                        const pill = document.getElementById('market-broker-restore-pill');
                        if (wrap) { wrap.classList.remove('is-broker-hidden'); wrap.style.setProperty('display', 'flex', 'important'); }
                        if (pill) pill.style.display = 'none';
                        window._userManuallyClosedBroker = false;
                        localStorage.setItem('magaiees_hide_broker_ticker', '0');
                    }
                }
                function switchBrokerControlScope(scope) {
                    window._brkActiveScope = (scope === 'order') ? 'order' : 'offer';
                    const isOrd = window._brkActiveScope === 'order';
                    const btnOff = document.getElementById('brk-scope-offer');
                    const btnOrd = document.getElementById('brk-scope-order');
                    if (btnOff) {
                        btnOff.style.background = !isOrd ? 'linear-gradient(135deg,#059669,#047857)' : '#1e293b';
                        btnOff.style.border = !isOrd ? '2px solid #fde047' : '1px solid #475569';
                    }
                    if (btnOrd) {
                        btnOrd.style.background = isOrd ? 'linear-gradient(135deg,#d97706,#b45309)' : '#1e293b';
                        btnOrd.style.border = isOrd ? '2px solid #fde047' : '1px solid #475569';
                    }
                    const selType = document.getElementById('brk-type');
                    if (selType) selType.value = isOrd ? 'order' : 'offer';
                    const inpTxt = document.getElementById('brk-text');
                    if (inpTxt) inpTxt.placeholder = isOrd ? '🟠 اكتب تفاصيل طلب الشراء هنا لينزل فوراً في شريط الطلبات بشارة 🔥 جديد...' : '🟢 اكتب تفاصيل عرض السعر هنا لينزل فوراً في شريط العروض بشارة 🔥 جديد...';
                    const lockBtn = document.getElementById('brk-unified-lock-btn');
                    const targetRow = document.getElementById(isOrd ? 'market-broker-orders-row' : 'market-broker-offers-row');
                    if (lockBtn && targetRow) {
                        const isHidden = targetRow.style.display === 'none';
                        lockBtn.innerHTML = isHidden ? (isOrd ? '🔓 إظهار الطلبات' : '🔓 إظهار العروض') : (isOrd ? '🔒 قفل الطلبات' : '🔒 قفل العروض');
                    }
                    const spLbl = document.getElementById('brk-speed-step-lbl');
                    if (spLbl) spLbl.textContent = formatBrokerSpeedLabel(isOrd ? 'orders' : 'offers');
                    renderBrokerModalTable(window._brkActiveScope);
                }
                function renderBrokerModalTable(filterMode) {
                    if (filterMode) window._brkTblFilter = filterMode;
                    const mode = window._brkTblFilter || window._brkActiveScope || 'offer';
                    const box = document.getElementById('brk-table-body');
                    if (!box) return;
                    const allList = window._brokerItemsList || [];
                    const list = mode === 'offer' ? allList.filter(x => x.type !== 'order') : (mode === 'order' ? allList.filter(x => x.type === 'order') : allList);
                    const newestOff = allList.find(x => x.type !== 'order')?.id;
                    const newestOrd = allList.find(x => x.type === 'order')?.id;
                    const allBtn = document.getElementById('brk-flt-all');
                    if (allBtn) allBtn.style.border = (mode === 'all') ? '2px solid #fde047' : '1px solid #475569';
                    box.innerHTML = list.map((it, idx) => {
                        const isOrd = it.type === 'order';
                        const ageM = (it.id === window._justAddedBrokerId) ? 0 : ((it.ts && Number(it.ts) > 1000000000) ? Math.max(0, (Date.now() - Number(it.ts) * 1000) / 60000) : 999);
                        const isNew = ageM <= 270;
                        const newBg = ageM <= 90 ? '#dc2626' : (ageM <= 180 ? '#f97316' : '#16a34a');
                        const blkBadge = Number(it.black_points || 0) > 0 ? ` <span style="background:#000;color:#fde047!important;-webkit-text-fill-color:#fde047!important;padding:1px 6px;border-radius:6px;font-size:10.5px;border:1px solid #ef4444;">⚫ ${it.black_points}/3</span>` : '';
                        return `<tr style="border-bottom:1px solid rgba(148,163,184,0.22);background:${isNew ? 'rgba(16,185,129,0.14)' : (idx % 2 ? 'rgba(15,23,42,0.5)' : 'transparent')};">
                            <td style="padding:8px 10px;font-weight:900;white-space:nowrap;"><span style="padding:2px 8px;border-radius:7px;background:${isOrd?'#d97706':'#059669'};color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:12px;">${isOrd?'🟠 طلب شراء':'🟢 عرض سعر'}</span> ${isNew?`<span style="background:${newBg};color:#fff!important;-webkit-text-fill-color:#fff!important;padding:1px 7px;border-radius:999px;font-size:10.5px;">جديد</span>`:''}</td>
                            <td class="text-amber-300" style="padding:8px 10px;font-weight:900;font-size:13.5px;white-space:nowrap;">${it.sender_name}${it.deal_code ? ' (#'+it.deal_code+')' : ''}${blkBadge}</td>
                            <td class="text-white" style="padding:8px 10px;font-weight:900;font-size:14px;">${it.text}</td>
                            <td class="text-sky-300" style="padding:8px 10px;font-size:12px;font-weight:800;white-space:nowrap;">${it.time} ${it.privacy==='masked'?'🔒 سري':'📞 مباشر'}</td>
                            <td style="padding:8px 10px;white-space:nowrap;display:flex;gap:5px;">
                                <button type="button" onclick="openBrokerDealAction('${it.sender_name}', '${it.text}', '${it.deal_code || ''}', '${it.privacy || 'public'}', '${it.phone || ''}')" style="padding:4px 10px;border-radius:7px;background:#059669;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-size:12px;font-weight:900;cursor:pointer;">📲 تواصل</button>
                                <button type="button" onclick="reportBrokerItem('${it.id}', '${it.sender_name}', '${it.phone || ''}', event)" title="بلاغ مخالفة (نقطة سوداء - 3 بلاغات = حظر تلقائي)" style="padding:4px 8px;border-radius:7px;background:#7f1d1d;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #ef4444;font-size:11.5px;font-weight:900;cursor:pointer;">🚨 بلاغ</button>
                                <button type="button" onclick="hideSingleBrokerOffer('${it.id}', event);renderBrokerModalTable();" style="padding:4px 8px;border-radius:7px;background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-size:12px;font-weight:900;cursor:pointer;">✕</button>
                            </td>
                        </tr>`;
                    }).join('');
                }
                function openMarketBrokerQuickPostModal(defaultType) {
                    let m = document.getElementById('broker-post-modal');
                    if (!m) {
                        m = document.createElement('div');
                        m.id = 'broker-post-modal';
                        m.style.cssText = 'position:fixed;inset:0;z-index:2147483620;background:rgba(2,6,23,0.86);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:10px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
                        m.innerHTML = `<div style="width:100%;max-width:1120px;max-height:94vh;display:flex;flex-direction:column;gap:8px;background:linear-gradient(160deg,#0f172a,#020617);border:2px solid #38bdf8;border-radius:18px;padding:12px 16px;color:#fff;box-shadow:0 24px 60px rgba(0,0,0,0.92);">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;padding:6px 10px;border-radius:12px;background:#0f172a;border:1.5px solid rgba(56,189,248,0.35);">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <button type="button" id="brk-scope-offer" onclick="switchBrokerControlScope('offer')" style="height:32px;padding:0 14px;border-radius:9px;background:linear-gradient(135deg,#059669,#047857);color:#fff!important;-webkit-text-fill-color:#fff!important;border:2px solid #fde047;font-size:13px;font-weight:900;cursor:pointer;">🟢 العروض</button>
                                    <button type="button" id="brk-scope-order" onclick="switchBrokerControlScope('order')" style="height:32px;padding:0 14px;border-radius:9px;background:#1e293b;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #475569;font-size:13px;font-weight:900;cursor:pointer;">🟠 الطلبات</button>
                                    <button type="button" id="brk-flt-all" onclick="renderBrokerModalTable('all')" style="height:32px;padding:0 12px;border-radius:9px;background:#1e293b;color:#bae6fd!important;-webkit-text-fill-color:#bae6fd!important;border:1px solid #475569;font-size:12px;font-weight:900;cursor:pointer;">📋 عرض الكل</button>
                                </div>
                                <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">
                                    <button type="button" onclick="adjustMarketBrokerSpeed(-1, window._brkActiveScope==='order'?'orders':'offers')" style="height:32px;padding:0 10px;border-radius:9px;background:#1e293b;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #f59e0b;font-size:12px;font-weight:900;cursor:pointer;">🐢 سرعة −</button>
                                    <span id="brk-speed-step-lbl" style="height:32px;padding:0 10px;border-radius:8px;background:#0f172a;color:#38bdf8!important;-webkit-text-fill-color:#38bdf8!important;border:1px solid #38bdf8;font-size:12px;font-weight:900;display:inline-flex;align-items:center;">${formatBrokerSpeedLabel(window._brkActiveScope==='order'?'orders':'offers')}</span>
                                    <button type="button" onclick="adjustMarketBrokerSpeed(1, window._brkActiveScope==='order'?'orders':'offers')" style="height:32px;padding:0 10px;border-radius:9px;background:#0284c7;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #7dd3fc;font-size:12px;font-weight:900;cursor:pointer;">⚡ سرعة +</button>
                                    <button type="button" onclick="adjustMarketBrokerFont(-1)" title="إنقاص الخط حتى -3" style="height:32px;padding:0 9px;border-radius:9px;background:#1e293b;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #475569;font-size:12px;font-weight:900;cursor:pointer;">🔡 خط −</button>
                                    <span id="brk-font-step-lbl" style="height:32px;padding:0 8px;border-radius:8px;background:#0f172a;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #38bdf8;font-size:11.5px;font-weight:900;display:inline-flex;align-items:center;">${formatBrokerFontSizeLabel(window._brokerFontStep)}</span>
                                    <button type="button" onclick="adjustMarketBrokerFont(1)" title="تكبير الخط حتى +10" style="height:32px;padding:0 9px;border-radius:9px;background:#0284c7;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #7dd3fc;font-size:12px;font-weight:900;cursor:pointer;">🔠 خط +</button>
                                    <select id="brk-font-family-sel" onchange="window._brokerFontFamily=this.value;_safeBrkSet('magaiees_broker_font_family',this.value);saveBrokerPrefs(true);window._lastBrokerSig='';loadMarketBrokerTicker();" title="خيارات نوع خط البورصة" style="height:32px;padding:0 8px;border-radius:9px;background:#0f172a;color:#7dd3fc!important;-webkit-text-fill-color:#7dd3fc!important;border:1px solid #38bdf8;font-size:11.5px;font-weight:900;cursor:pointer;">
                                        <option value="Cairo, Tajawal, sans-serif" ${(!window._brokerFontFamily||window._brokerFontFamily.includes('Cairo'))?'selected':''}>✒️ خط القاهرة (Cairo)</option>
                                        <option value="Tajawal, Cairo, sans-serif" ${(window._brokerFontFamily&&window._brokerFontFamily.startsWith('Tajawal'))?'selected':''}>✒️ خط تجوال (Tajawal)</option>
                                        <option value="Almarai, Cairo, sans-serif" ${(window._brokerFontFamily&&window._brokerFontFamily.startsWith('Almarai'))?'selected':''}>✒️ خط المراعي (Almarai)</option>
                                        <option value="'Readex Pro', Cairo, sans-serif" ${(window._brokerFontFamily&&window._brokerFontFamily.includes('Readex'))?'selected':''}>✒️ خط ريدكس (Readex)</option>
                                        <option value="Changa, Cairo, sans-serif" ${(window._brokerFontFamily&&window._brokerFontFamily.startsWith('Changa'))?'selected':''}>✒️ خط تشانغا (Changa)</option>
                                    </select>
                                    <button type="button" id="brk-toggle-names-btn" onclick="toggleMarketBrokerNames()" style="height:32px;padding:0 9px;border-radius:9px;background:${window._brokerHideNames?'#059669':'#1e293b'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #10b981;font-size:11.5px;font-weight:900;cursor:pointer;">${window._brokerHideNames?'👤 الأسماء: مخفية':'👤 الأسماء: ظاهرة'}</button>
                                    <button type="button" id="brk-my-items-btn" onclick="toggleBrokerMyItemsFilter()" style="height:32px;padding:0 9px;border-radius:9px;background:${window._brokerMyItemsFilter!==false?'#059669':'#1e293b'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #34d399;font-size:11.5px;font-weight:900;cursor:pointer;">${window._brokerMyItemsFilter!==false?'🎯 ذكاء الأصناف: نشاطي':'🌐 ذكاء الأصناف: الكل'}</button>
                                    <button type="button" id="brk-unified-lock-btn" onclick="toggleSingleBrokerRow(window._brkActiveScope==='order'?'market-broker-orders-row':'market-broker-offers-row');switchBrokerControlScope(window._brkActiveScope);saveBrokerPrefs();" style="height:32px;padding:0 9px;border-radius:9px;background:#be123c;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fda4af;font-size:11.5px;font-weight:900;cursor:pointer;">🔒 قفل العروض</button>
                                    <select id="brk-max-age-sel" onchange="window._brokerMaxAgeHours=parseInt(this.value,10)||24;saveBrokerPrefs(true);window._lastBrokerSig='';loadMarketBrokerTicker().then(()=>renderBrokerModalTable());" title="تحديد المدة الزمنية لبقاء العروض والطلبات قبل إخفائها تلقائياً" style="height:32px;padding:0 8px;border-radius:9px;background:#0f172a;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1.5px solid #f59e0b;font-size:11.5px;font-weight:900;cursor:pointer;">
                                        <option value="2" ${window._brokerMaxAgeHours===2?'selected':''}>⏱️ التحديث: ساعتان</option>
                                        <option value="4" ${window._brokerMaxAgeHours===4?'selected':''}>⏱️ التحديث: 4 ساعات</option>
                                        <option value="8" ${window._brokerMaxAgeHours===8?'selected':''}>⏱️ التحديث: 8 ساعات</option>
                                        <option value="12" ${window._brokerMaxAgeHours===12?'selected':''}>⏱️ التحديث: 12 ساعة</option>
                                        <option value="24" ${(!window._brokerMaxAgeHours||window._brokerMaxAgeHours===24)?'selected':''}>⏱️ التحديث: كل يوم (24 ساعة)</option>
                                        <option value="48" ${window._brokerMaxAgeHours===48?'selected':''}>⏱️ التحديث: يومان (48 ساعة)</option>
                                        <option value="168" ${window._brokerMaxAgeHours===168?'selected':''}>⏱️ التحديث: أسبوع كامل</option>
                                    </select>
                                    <button type="button" id="brk-dock-pos-btn" onclick="toggleMarketBrokerDock()" style="height:32px;padding:0 9px;border-radius:9px;background:#1e293b;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #f59e0b;font-size:11.5px;font-weight:900;cursor:pointer;">📍 الموقع: ${localStorage.getItem('magaiees_broker_dock_mode')==='bottom'?'أسفل الشاشة ⇄':'أعلى الشاشة ⇄'}</button>
                                    <button type="button" id="brk-all-screens-btn" onclick="toggleMarketBrokerAllScreens()" title="التحكم في إظهار شريط البورصة في كل الشاشات أو في الشاشة الرئيسية فقط" style="height:32px;padding:0 9px;border-radius:9px;background:${localStorage.getItem('magaiees_broker_all_screens')!=='0'?'#059669':'#1e293b'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #34d399;font-size:11.5px;font-weight:900;cursor:pointer;">${localStorage.getItem('magaiees_broker_all_screens')!=='0'?'🌐 الظهور: كل الشاشات ⇄':'🖥️ الظهور: الرئيسية فقط ⇄'}</button>
                                    <button type="button" id="brk-hide-all-btn" onclick="toggleMarketBrokerTicker(false);document.getElementById('broker-post-modal').style.display='none';" title="إخفاء شريطي البورصة بالكامل مع إمكانية استعادتهما في أي وقت" style="height:32px;padding:0 9px;border-radius:9px;background:#881337;color:#fecdd3!important;-webkit-text-fill-color:#fecdd3!important;border:1px solid #f43f5e;font-size:11.5px;font-weight:900;cursor:pointer;">🙈 إخفاء البورصة</button>
                                    <button type="button" onclick="window._hiddenBrokerIds=[];window._brokerFontStep=2;window._brokerOffersSec=160;window._brokerOrdersSec=200;window._brokerMaxAgeHours=24;window._brokerHideNames=false;window._brokerMyItemsFilter=true;const agSel=document.getElementById('brk-max-age-sel');if(agSel)agSel.value='24';localStorage.setItem('magaiees_hidden_broker_ids','[]');applyMarketBrokerDock('top');applyMarketBrokerSpeed();saveBrokerPrefs(true);window._lastBrokerSig='';loadMarketBrokerTicker();" style="height:32px;padding:0 9px;border-radius:9px;background:#0f172a;color:#38bdf8!important;-webkit-text-fill-color:#38bdf8!important;border:1px solid #38bdf8;font-size:11.5px;font-weight:900;cursor:pointer;">♻️ استعادة الافتراضي</button>
                                    <button type="button" onclick="saveBrokerPrefs(true);document.getElementById('broker-post-modal').style.display='none'" style="height:32px;padding:0 12px;border-radius:9px;background:linear-gradient(135deg,#059669,#047857);color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #fde047;font-size:12px;font-weight:900;cursor:pointer;">💾 حفظ وخروج</button>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding:6px 8px;border-radius:10px;background:rgba(15,23,42,0.9);border:1px solid #38bdf8;">
                                <select id="brk-type" onchange="switchBrokerControlScope(this.value)" style="height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1px solid #38bdf8;padding:0 8px;font-size:12px;font-weight:900;">
                                    <option value="offer">🟢 عرض أسعار (بيع)</option>
                                    <option value="order">🟠 طلب شراء (مطلوب)</option>
                                </select>
                                <select id="brk-privacy" style="height:32px;border-radius:8px;background:#1e293b;color:#fde047;border:1px solid #f59e0b;padding:0 8px;font-size:12px;font-weight:900;">
                                    <option value="public">👁️ عام بالاسم</option>
                                    <option value="masked">🔒 سري بكود</option>
                                </select>
                                <input id="brk-name" type="text" placeholder="الاسم (مثلاً: مهدي)" style="width:130px;height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 8px;font-size:12px;font-weight:800;">
                                <input id="brk-phone" type="text" placeholder="رقم الواتساب" value="249123005622" style="width:130px;height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 8px;font-size:12px;font-weight:800;direction:ltr;">
                                <input id="brk-text" type="text" placeholder="🟢 اكتب تفاصيل عرض السعر أو ارفع صورة/مجموعة صور بالزر المجاور..." style="flex:1;min-width:200px;height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1.5px solid #38bdf8;padding:0 10px;font-size:12.5px;font-weight:800;">
                                <input id="brk-ai-vision-file" type="file" accept="image/*" multiple style="display:none;" onchange="analyzeBrokerImageOffer(this)">
                                <button type="button" id="brk-ai-vision-btn" onclick="document.getElementById('brk-ai-vision-file').click()" title="الذكاء البصري للبورصة: ارفع صورة أو مجموعة صور (أصناف وأسعار أو ورق مكتوب بخط اليد) ليقرأها وينشرها فوراً" style="padding:0 12px;height:32px;border-radius:8px;background:linear-gradient(135deg,#7c3aed,#0284c7);color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1.5px solid #fde047;font-size:12px;font-weight:900;cursor:pointer;white-space:nowrap;">📷 قراءة صورة / مجموعة صور</button>
                                <button type="button" onclick="submitBrokerPost()" style="padding:0 16px;height:32px;border-radius:8px;background:linear-gradient(135deg,#059669,#0284c7);color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fde047;font-size:12.5px;font-weight:900;cursor:pointer;white-space:nowrap;">🚀 انشر الآن</button>
                            </div>
                            <div style="flex:1;min-height:0;display:flex;flex-direction:column;border-radius:12px;border:1.5px solid rgba(56,189,248,0.35);overflow:hidden;background:rgba(2,6,23,0.65);">
                                <div style="flex:1;max-height:66vh;min-height:400px;overflow-y:auto;">
                                    <table style="width:100%;border-collapse:collapse;font-size:13.5px;text-align:right;">
                                        <thead style="position:sticky;top:0;z-index:2;"><tr style="background:#0f172a;color:#38bdf8!important;-webkit-text-fill-color:#38bdf8!important;border-bottom:1.5px solid rgba(56,189,248,0.35);"><th style="padding:8px 10px;">النوع</th><th style="padding:8px 10px;">العارض / الكود</th><th style="padding:8px 10px;">الأصناف والتفاصيل والأسعار المعروضة</th><th style="padding:8px 10px;">التوقيت</th><th style="padding:8px 10px;">إجراء</th></tr></thead>
                                        <tbody id="brk-table-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>`;
                        document.body.appendChild(m);
                    }
                    switchBrokerControlScope(defaultType || 'offer');
                    m.style.display = 'flex';
                }
                async function analyzeBrokerImageOffer(inputEl) {
                    const file = inputEl?.files?.[0];
                    if (!file) return;
                    const btn = document.getElementById('brk-ai-vision-btn');
                    const txtInp = document.getElementById('brk-text');
                    if (btn) btn.innerHTML = '⏳ جاري قراءة الصورة بالذكاء البصري...';
                    const reader = new FileReader();
                    reader.onload = async () => {
                        try {
                            const res = await fetch('api/ai-assistant.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    image: reader.result,
                                    message: 'اقرأ هذه الصورة بدقة فائقة (سواء كانت ورقة مكتوبة بخط اليد، أو ورقة مطبوعة، أو صورة منتج/صنف مثل لمبة أو معدة وتحتها سعرها)، واستخرج منها سطراً واحداً مختصراً ومرتباً يصلح للنشر في شريط البورصة يتضمن اسم الصنف المستنتج من الصورة أو النص مع السعر والكمية إن وجدت، بدون أي مقدمات.'
                                })
                            }).then(r => r.json());
                            const extracted = String(res?.reply || '').replace(/[\r\n*#]+/g, ' | ').trim();
                            if (txtInp && extracted) txtInp.value = extracted;
                        } catch (e) {}
                        if (btn) btn.innerHTML = '📷 قراءة صورة / صنف وخط يد';
                        inputEl.value = '';
                    };
                    reader.readAsDataURL(file);
                }
                async function submitBrokerPost() {
                    const txt = (document.getElementById('brk-text')?.value || '').trim();
                    if (!txt) return;
                    const payload = {
                        type: document.getElementById('brk-type').value,
                        privacy: document.getElementById('brk-privacy').value,
                        sender_name: document.getElementById('brk-name').value,
                        phone: document.getElementById('brk-phone').value,
                        text: txt
                    };
                    const out = await fetch('api/market_broker_ticker.php?action=add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    }).then(r => r.json()).catch(() => null);
                    if (/^(localhost|127\.0\.0\.1)$/i.test(location.hostname)) {
                        fetch('https://c.elsajana.com/api/market_broker_ticker.php?action=add', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }).catch(() => {});
                    }
                    if (out?.item?.id) window._justAddedBrokerId = out.item.id;
                    document.getElementById('brk-text').value = '';
                    document.getElementById('broker-post-modal').style.display = 'none';
                    window._lastBrokerSig = '';
                    window._lastCloudBrokerPull = 0;
                    loadMarketBrokerTicker(true);
                }
                loadMarketBrokerTicker(true);
                setInterval(() => { if (!document.hidden && navigator.onLine) loadMarketBrokerTicker(false); }, 60000);
                window._execSumPeriodIdx = 0;
                function cycleExecutiveSummaryPeriod(targetIdx, skipScopeSync) {
                    const periods = [
                        { lbl: 'اليوم ⇄', scope: 'today', mult: 1, mktSdg: 184300000, apSdg: 290000, arSdg: 820000 },
                        { lbl: 'أسبوع ⇄', scope: 'week', mult: 6.5, mktSdg: 186800000, apSdg: 410000, arSdg: 1150000 },
                        { lbl: '10 أيام ⇄', scope: 'week', mult: 9.2, mktSdg: 188400000, apSdg: 480000, arSdg: 1340000 },
                        { lbl: 'شهر ⇄', scope: 'month', mult: 28, mktSdg: 194200000, apSdg: 690000, arSdg: 2400000 },
                        { lbl: '3 شهور ⇄', scope: 'all', mult: 82, mktSdg: 208500000, apSdg: 920000, arSdg: 4100000 },
                        { lbl: '6 شهور ⇄', scope: 'all', mult: 162, mktSdg: 225000000, apSdg: 1180000, arSdg: 5800000 },
                        { lbl: '9 شهور ⇄', scope: 'all', mult: 240, mktSdg: 241600000, apSdg: 1350000, arSdg: 6900000 },
                        { lbl: 'سنة ⇄', scope: 'all', mult: 335, mktSdg: 264000000, apSdg: 1540000, arSdg: 8200000 }
                    ];
                    window._execSumPeriodIdx = (typeof targetIdx === 'number') ? targetIdx : ((window._execSumPeriodIdx + 1) % periods.length);
                    const p = periods[window._execSumPeriodIdx] || periods[0];
                    const usd = parseFloat(document.getElementById('cur-usd')?.value) || 600;
                    const factor = displayUSD ? usd : 1;
                    const unit = displayUSD ? '$' : 'ج.س';
                    const uTag = `<small class="exec-unit-tag">${unit}</small>`;
                    const m = v => `${Math.round(v / factor).toLocaleString()} ${uTag}`;
                    const lblEl = document.getElementById('exec-summary-period-lbl');
                    if (lblEl) lblEl.innerText = p.lbl;
                    const pillsBox = document.getElementById('exec-summary-period-pills');
                    if (pillsBox) {
                        Array.from(pillsBox.children).forEach((b, i) => {
                            if (b.id === 'exec-more-periods-btn') return;
                            const isExtra = [2, 4, 5, 6].includes(i) ? ' exec-period-extra' : '';
                            b.className = (i === window._execSumPeriodIdx ? 'exec-period-pill active-period' : 'exec-period-pill') + isExtra;
                        });
                    }
                    if (typeof window.updateCalmPulseBadge === 'function') setTimeout(window.updateCalmPulseBadge, 50);
                    const rdArr = k => { try { const a = JSON.parse(localStorage.getItem(k) || '[]'); return Array.isArray(a) ? a.filter(x => x && !x.deleted) : []; } catch (_) { return []; } };
                    const liveSafes = rdArr('erp_safes_v1'), liveBanks = rdArr('erp_banks_v1'), liveCusts = rdArr('erp_customers_v1'), liveSups = rdArr('erp_suppliers_v1');
                    const liveLiqSum = [...liveSafes, ...liveBanks].reduce((s, x) => s + ((parseFloat(x.currentBalance ?? x.balance ?? x.openingBalance ?? 0) || 0) * ((x.currency && x.currency !== 'SDG') ? usd : 1)), 0);
                    const liveArSum = liveCusts.reduce((s, c) => s + Math.max(0, parseFloat(c.currentBalance ?? c.balance ?? c.openingBalance ?? 0) || 0), 0);
                    const liveApSum = liveSups.reduce((s, x) => s + Math.max(0, parseFloat(x.currentBalance ?? x.balance ?? x.openingBalance ?? 0) || 0), 0);
                    const effLiq = liveLiqSum > 0 ? liveLiqSum : (3450000 * (1 + (p.mult - 1) * 0.08));
                    const setHtml = (id, v) => { const el = document.getElementById(id); if (el) el.innerHTML = v; };
                    setHtml('strip-sal-amt', m(1890000 * p.mult));
                    setHtml('strip-pur-amt', m(1420000 * p.mult));
                    const liqEl = document.getElementById('strip-liq-amt'); if (liqEl) liqEl.setAttribute('data-sdg', String(Math.round(effLiq)));
                    setHtml('strip-liq-amt', m(effLiq));
                    const prfVal = Math.round(348000 * p.mult);
                    const prfLbl = document.getElementById('strip-prf-lbl');
                    const prfAmt = document.getElementById('strip-prf-amt');
                    if (prfLbl && prfAmt) {
                        if (prfVal >= 0) {
                            prfLbl.innerText = 'الأرباح:';
                            prfAmt.className = 'exec-jewel-val text-emerald-400';
                            prfAmt.style.cssText = 'color:#4ade80!important;-webkit-text-fill-color:#4ade80!important;';
                            prfAmt.innerHTML = '+' + m(prfVal);
                        } else {
                            prfLbl.innerText = 'الخسائر:';
                            prfAmt.className = 'exec-jewel-val text-rose-400';
                            prfAmt.style.cssText = 'color:#fb7185!important;-webkit-text-fill-color:#fb7185!important;';
                            prfAmt.innerHTML = '-' + m(Math.abs(prfVal));
                        }
                    }
                    setHtml('strip-exp-amt', m(42000 * p.mult));
                    setHtml('stock-mkt-val', `${(((p.mktSdg / factor) / 1000000).toFixed(1))}M ${uTag}`);
                    setHtml('strip-in-amt', '+' + m(1320000 * p.mult));
                    setHtml('strip-out-amt', '-' + m(368000 * p.mult));
                    const apEl = document.getElementById('strip-ap-amt'); if (apEl && liveApSum > 0) apEl.setAttribute('data-sdg', String(Math.round(liveApSum)));
                    setHtml('strip-ap-amt', m(liveApSum > 0 ? liveApSum : p.apSdg));
                    setHtml('strip-ar-amt', m(liveArSum > 0 ? liveArSum : p.arSdg));
                    if (!skipScopeSync) {
                        const liqMap = ['day', 'week', 'week', 'month', 'year', 'year', 'year', 'year'];
                        window._liqPeriod = liqMap[window._execSumPeriodIdx] || 'day';
                        if (typeof switchLiquidityCardView === 'function') switchLiquidityCardView(window._liqViewMode || 'overview', null);
                        const spMap = ['day', 'day', 'day', 'month', 'year', 'year', 'year', 'year'];
                        if (typeof switchSalesProfitCardView === 'function') switchSalesProfitCardView(spMap[window._execSumPeriodIdx] || 'day', null);
                        if (typeof switchCenterChartCard === 'function') switchCenterChartCard(window._centerChartMode || 'donuts', null);
                        if (typeof setScope === 'function') setScope(p.scope, null);
                    }
                }
                window._itemsGroup = 'sales';
                function toggleItemsGroup() {
                    window._itemsGroup = window._itemsGroup === 'sales' ? 'stock' : 'sales';
                    const sTabs = document.getElementById('dash-items-tabs-sales');
                    const kTabs = document.getElementById('dash-items-tabs-stock');
                    const btn = document.getElementById('dash-items-group-toggle');
                    if (window._itemsGroup === 'stock') {
                        if (sTabs) sTabs.style.display = 'none';
                        if (kTabs) kTabs.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ المبيعات';
                        const firstBtn = kTabs ? kTabs.querySelector('button') : null;
                        switchItemsRadar('stock_most', firstBtn);
                    } else {
                        if (kTabs) kTabs.style.display = 'none';
                        if (sTabs) sTabs.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ المخازن';
                        const firstBtn = sTabs ? sTabs.querySelector('button') : null;
                        switchItemsRadar('top_sell', firstBtn);
                    }
                }
                window._liqPeriod = 'day';
                window._liqViewMode = 'overview';
                function toggleLiquidityPeriod() {
                    const order = ['day', 'week', 'month', 'year'];
                    const labels = { day: '📅 اليوم ⇄', week: '📆 الأسبوع ⇄', month: '🗓️ الشهر ⇄', year: '🏆 السنة ⇄' };
                    window._liqPeriod = order[(order.indexOf(window._liqPeriod || 'day') + 1) % order.length];
                    const btn = document.getElementById('dash-liq-period-btn');
                    if (btn) btn.innerText = labels[window._liqPeriod];
                    switchLiquidityCardView(window._liqViewMode || 'overview', null);
                }
                function switchLiquidityCardView(mode, btn) {
                    window._liqViewMode = mode;
                    if (btn) _activateMiniTab(btn, mode === 'coverage' ? 'bg-amber-600' : (mode === 'banks' ? 'bg-sky-600' : 'bg-emerald-600'));
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const box = document.getElementById('dash-liq-bottom-boxes');
                    const sub = document.getElementById('main-liquidity-sub');
                    const mainEl = document.getElementById('main-liquidity');
                    const bdgEl = document.getElementById('dash-liq-card-badge');
                    if (!box) return;
                    const p = window._liqPeriod || 'day';
                    const mult = { day: 1, week: 2.4, month: 6.8, year: 34 }[p] || 1;
                    const pLbl = { day: 'اليوم', week: 'الأسبوع', month: 'الشهر', year: 'السنة' }[p];
                    const gLbl = { day: 'نمو +12.4% ▲', week: 'نمو +18.2% ▲', month: 'نمو +24.6% ▲', year: 'نمو +38.5% ▲' }[p];
                    const covRatio = { day: '15.9x آمن ✓', week: '11.8x آمن ✓', month: '9.4x آمن ✓', year: '12.6x آمن ✓' }[p];
                    if (bdgEl) bdgEl.innerText = gLbl;
                    if (mainEl) mainEl.innerHTML = typeof window._fmtDualHero === 'function' ? window._fmtDualHero(Math.round(3450000 * mult), 'text-emerald-400') : `${m(Math.round(3450000 * mult))}`;
                    if (mode === 'banks') {
                        if (sub) sub.innerText = `🏦 توزيع السيولة البنكية (${pLbl}) • اضغط (+) أو (📊) لعرض كل البنوك والخزائن`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🏦 بنك الخرطوم:</span><strong class="text-sky-300 text-sm font-extrabold">${m(Math.round(1250000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🏦 فيصل وأم درمان:</span><strong class="text-sky-300 text-sm font-extrabold">${m(Math.round(750000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💵 الخزنة والفرع:</span><strong class="text-emerald-300 text-sm font-extrabold">${m(Math.round(1450000 * mult))}</strong></div>`;
                    } else if (mode === 'coverage') {
                        if (sub) sub.innerText = `🛡️ كفاية السيولة لتغطية الشيكات الصادرة والتزامات الموردين خلال (${pLbl}): ${covRatio}`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🟢 شيكات واردة:</span><strong class="text-emerald-300 text-sm font-extrabold">${m(Math.round(4250000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🔴 شيكات صادرة:</span><strong class="text-rose-400 text-sm font-extrabold">${m(Math.round(266000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🛡️ نسبة التغطية:</span><strong class="text-amber-300 text-sm font-extrabold">${covRatio}</strong></div>`;
                    } else if (mode === 'ai_cash') {
                        if (sub) sub.innerText = `🤖 معادلة التنبؤ (${pLbl}): الرصيد الحالي + (مبيعات وتحصيل ديون متوقع) - سداد الموردين`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">📈 مبيعات وتحصيل:</span><strong class="text-emerald-300 text-sm font-extrabold">+${m(Math.round(5800000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">📉 سداد موردين:</span><strong class="text-rose-400 text-sm font-extrabold">-${m(Math.round(980000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💎 السيولة المتوقعة:</span><strong class="text-sky-300 text-sm font-extrabold">${m(Math.round(8270000 * mult))}</strong></div>`;
                    } else {
                        if (sub) sub.innerText = `▲ تدفق إيجابي متصاعد (+${m(Math.round(910000 * mult))} صافي ${pLbl} • نسبة التغطية ${covRatio})`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💵 الصندوق:</span><strong class="text-emerald-300 text-sm font-extrabold" id="sub-cash">${m(Math.round(1450000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💳 البنوك:</span><strong class="text-sky-300 text-sm font-extrabold" id="sub-bank">${m(Math.round(2000000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🧾 شيكات بالتحصيل:</span><strong class="text-amber-300 text-sm font-extrabold" id="sub-checks-in">${m(Math.round(4250000 * mult))}</strong></div>`;
                    }
                }
                function switchCashflowCardView(mode, btn) {
                    if (btn) _activateMiniTab(btn, mode === 'stress' ? 'bg-amber-600' : (mode === 'ai_cf' ? 'bg-sky-600' : 'bg-emerald-600'));
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const box = document.getElementById('dash-cashflow-kpis-box');
                    if (!box) return;
                    if (mode === 'ccc') {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📦 دوران المخزون DIO</span><strong class="text-base font-black text-emerald-300">11 يوماً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">👥 تحصيل العملاء DSO</span><strong class="text-base font-black text-sky-300">9 أيام</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🚚 سداد الموردين DPO</span><strong class="text-base font-black text-amber-300">14 يوماً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚡ صافي دورة النقد</span><strong class="text-base font-black text-emerald-300">6 أيام ✓</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🔄 سرعة تدوير السيولة</span><strong class="text-base font-black text-emerald-300">2.4x شهرياً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">💎 عائد الدورة الواحدة</span><strong class="text-base font-black text-sky-300">+18.4%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🧾 تحصيل الشيكات</span><strong class="text-base font-black text-emerald-300">96% منتظم</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛡️ أمان التدفق اليومي</span><strong class="text-base font-black text-emerald-300">100% آمن</strong></div>`;
                    } else if (mode === 'ai_cf') {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🚚 الترحيل والعتالة</span><strong class="text-sm font-black text-amber-300">${m(420000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🏢 التشغيل والرواتب</span><strong class="text-sm font-black text-sky-300">${m(620000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📊 المنصرف للمبيعات</span><strong class="text-sm font-black text-emerald-300">1.9% فقط ✓</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚖️ تغطية التعادل</span><strong class="text-sm font-black text-emerald-300">11.8 ضعفاً ▲</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚡ كهرباء ووقود</span><strong class="text-sm font-black text-amber-300">${m(95000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛠️ صيانة ومهمات</span><strong class="text-sm font-black text-sky-300">${m(68000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📉 وفر ضبط النفقات</span><strong class="text-sm font-black text-emerald-300">+14.2% ▲</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛡️ سقف المصروفات</span><strong class="text-sm font-black text-emerald-300">تحت السيطرة ✓</strong></div>`;
                    } else if (mode === 'stress') {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛡️ تغطية الالتزامات</span><strong class="text-base font-black text-emerald-300">11.8 ضعفاً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⏳ كفاية النقد التشغيلي</span><strong class="text-base font-black text-sky-300">45 يوماً ✓</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">💱 تحوط تذبذب الصرف</span><strong class="text-base font-black text-amber-300">مؤمّن 92%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🚨 مخاطر عجز السيولة</span><strong class="text-base font-black text-emerald-300">0% (معدوم)</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📦 غطاء المخزون السلعي</span><strong class="text-base font-black text-emerald-300">608 ضعفاً ▲</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🏦 سيولة البنوك الفورية</span><strong class="text-base font-black text-sky-300">58% جاهزة</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">💎 فائض إحلال البضاعة</span><strong class="text-base font-black text-emerald-300">+4.2% ربح</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚖️ الملاءة الائتمانية</span><strong class="text-base font-black text-emerald-300">ممتازة AAA</strong></div>`;
                    } else {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 التدفق النقدي</span><strong class="text-base font-black text-emerald-300">84%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 سيولة المبيعات</span><strong class="text-base font-black text-emerald-300">72%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟡 وفرة المخزون</span><strong class="text-base font-black text-amber-300">78%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟡 سعة المخازن</span><strong class="text-base font-black text-amber-300">64%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 كفاءة التحصيل</span><strong class="text-base font-black text-emerald-300">81%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 دوران البضاعة</span><strong class="text-base font-black text-sky-300">2.4x</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 تغطية الشيكات</span><strong class="text-base font-black text-emerald-300">96%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 هامش الأمان</span><strong class="text-base font-black text-emerald-300">88%</strong></div>`;
                    }
                }
                function switchSalesProfitCardView(scope, btn) {
                    if (btn) _activateMiniTab(btn, (scope === 'warehouses' || scope === 'strategy') ? 'bg-amber-600' : 'bg-sky-600');
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const d = typeof window._fmtDualSub === 'function' ? window._fmtDualSub : (v, p='') => p + m(v);
                    const mid = document.getElementById('dash-sales-profit-mid');
                    const bot = document.getElementById('dash-sales-profit-bot');
                    const bdg = document.getElementById('scope-badge');
                    if (!mid || !bot) return;
                    const map = {
                        hour: ['الساعة ▲', 'إجمالي مبيعات الساعة', d(236000), 'صافي أرباح الساعة', d(43500, '+'), 'النقد المحصل بالساعة', d(195000), 'مبيعات آجلة بالساعة:', m(41000), 'الفواتير والأصناف بالساعة:', '6 فواتير (14 صنفاً)', 'الوحدات المباعة بالساعة:', '4.2 طن • 85 كرتونة • 30 لوح'],
                        day: ['اليوم ▲', 'إجمالي مبيعات اليوم', d(1890000), 'صافي أرباح اليوم', d(348000, '+'), 'النقد المحصل اليوم', d(1320000), 'مبيعات آجلة اليوم:', m(570000), 'الفواتير والأصناف اليوم:', '38 فاتورة (20 صنفاً)', 'الوحدات المباعة اليوم:', '32.5 طن • 410 كرتونة • 180 لوح'],
                        month: ['الشهر ▲', 'إجمالي مبيعات الشهر', d(54800000), 'صافي أرباح الشهر', d(9860000, '+'), 'النقد المحصل بالشهر', d(46200000), 'مبيعات آجلة بالشهر:', m(8600000), 'الفواتير والأصناف بالشهر:', '840 فاتورة (20 صنفاً)', 'الوحدات المباعة بالشهر:', '680 طن • 9,400 كرتونة • 3,200 لوح'],
                        year: ['السنة ▲', 'إجمالي مبيعات السنة', d(642000000), 'صافي أرباح السنة', d(118400000, '+'), 'النقد المحصل بالسنة', d(585000000), 'مبيعات آجلة بالسنة:', m(57000000), 'الفواتير والأصناف بالسنة:', '4,820 فاتورة (20 صنفاً)', 'الوحدات المباعة بالسنة:', '7,850 طن • 112 ألف كرتونة • 38 ألف لوح'],
                        purchases: ['المشتريات 🛒', 'إجمالي مشتريات اليوم', d(1420000), 'خصم مكتسب ووثر شراء', d(85000, '+'), 'كمية المشتريات المستلمة', '45.0 طن + 600 جوال', 'مشتريات آجلة للموردين:', m(290000), 'فواتير الشراء والتوريد:', '12 فاتورة شراء (8 موردين)', 'مشتريات بالطريق (تحت الاستلام):', '40 طن سيخ 12مم • 60 طن أسمنت'],
                        warehouses: ['المخازن ⚖️', '🏪 المخزن الرئيسي (12 صنفاً) 🔍', `مبيعات: ${m(1150000)} | ربح: +${m(212000)}`, '🏬 المخزن الفرعي (8 أصناف) 🔍', `مبيعات: ${m(740000)} | ربح: +${m(136000)}`, '🏛️ قيمة بضاعة كل المخازن 🔍', d(176600000), 'إجمالي مبيعات المخازن اليوم:', m(1890000), 'صافي أرباح المخازن اليوم:', '+' + m(348000), 'عرض أصناف المخازن (كل الوحدات 🔍):', '20 صنفاً (وزن • ربطة • كرتونة • لوح • حجم)'],
                        strategy: ['العائد 🧠', '💎 العائد على الاستثمار (ROI)', '+24.6% سنوياً (ممتاز ▲)', '💱 أثر تقلب سعر الصرف', `+4.2% مكسب إحلال (${d(176600000)})`, '🛡️ حماية هامش الربح', '0 فاتورة خاسرة (أدنى ربح 14% ✓)', 'أعلى صنف عائداً للاستثمار (ROI):', 'سيخ 12مم السجانة (+28.4% عائد ▲)', '🔄 معدل دوران المخزون:', '2.4 دورة/شهر (كل 12 يوماً ▲)', 'تنبيه سعر الصرف والإحلال:', 'مغطى بالدولار 100% ضد التقلب ✓']
                    }[scope] || [];
                    if (bdg && map[0]) bdg.innerText = map[0];
                    const clk = scope === 'warehouses' ? `onclick="openFullCardModal('sales')" style="cursor:pointer" title="اضغط لاستعراض كافة أصناف المخزن بالوزن والربطة والكرتونة والحجم والمبيعات والأرباح"` : '';
                    mid.innerHTML = `<div class="inner-box p-3 hover:border-sky-400 transition" ${clk}><span class="text-xs text-slate-300 block mb-1">${map[1]} <b class="text-emerald-400">▲</b></span><div class="${scope==='warehouses'?'text-sm':'text-xl'} font-black text-white">${map[2]}</div></div><div class="inner-box p-3 hover:border-emerald-400 transition" ${clk}><span class="text-xs text-slate-300 block mb-1">${map[3]} <b class="text-emerald-400">▲</b></span><div class="${scope==='warehouses'?'text-sm':'text-xl'} font-black text-emerald-400">${map[4]}</div></div><div class="inner-box p-3 hover:border-amber-400 transition" ${clk}><span class="text-xs text-slate-300 block mb-1">${map[5]}</span><div class="text-xl font-black text-amber-400">${map[6]}</div></div>`;
                    bot.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between" ${clk}><span class="text-slate-300">${map[7]}</span><strong class="text-rose-400 font-bold">${map[8]}</strong></div><div class="inner-box p-2.5 flex items-center justify-between" ${clk}><span class="text-slate-300">${map[9]}</span><strong class="text-amber-300 font-bold">${map[10]}</strong></div><div class="inner-box p-2.5 flex items-center justify-between" ${clk}><span class="text-slate-300">${map[11]}</span><strong class="text-sky-300 font-bold">${map[12]}</strong></div>`;
                }
                function switchAuditCommandView(mode, btn) {
                    if (btn) _activateMiniTab(btn, mode === 'ai_audit' ? 'bg-amber-600' : 'bg-emerald-600');
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const tb = document.getElementById('dash-audit-command-tbody');
                    if (!tb) return;
                    const rows = {
                        trial: [['صافي السيولة بالخزائن والبنوك', m(3450000), 'emerald', 'متطابق 100% ✓'], ['مخزون سيخ التسليح والأسمنت', '420.5 طن (56,767 سيخة)', 'amber', 'مضبوط وزنياً وعدداً ✓'], ['إجمالي ذمم العملاء (المدينون)', m(820000), 'sky', 'مؤكد بالقيود ✓'], ['التزامات الموردين (الدائنون)', m(290000), 'rose', 'مؤكد بالقيود ✓']],
                        pnl: [['إجمالي إيرادات المبيعات المعتمدة', m(54800000), 'sky', 'نمو +18.4% ▲'], ['تكلفة البضاعة المباعة (COGS)', m(43900000), 'amber', 'ضمن السقف المعياري ✓'], ['المصروفات التشغيلية والترحيل', m(1040000), 'rose', '1.9% من المبيعات ✓'], ['صافي الربح القابل للتوزيع', '+' + m(9860000), 'emerald', 'هامش صافي 18.0% ✓']],
                        balance: [['إجمالي الأصول المتداولة والمخزنية', m(180870000), 'emerald', 'مغطى بالكامل ✓'], ['إجمالي الالتزامات والشيكات الصادرة', m(556000), 'rose', '0.3% من الأصول ✓'], ['صافي حقوق الملكية والمركز المالي', m(180314000), 'sky', 'ملاءة ممتازة ✓'], ['مؤشر التوازن المحاسبي العام', '0 فروقات', 'emerald', 'ميزان مراجعة صفري ✓']],
                        ai_audit: [['فحص الفواتير والأسعار تحت التكلفة', '0 فاتورة خاسرة', 'emerald', 'حماية الربح 100% ✓'], ['فحص أرصدة الخزائن والبنوك اللحظي', '0 انحراف نقدي', 'emerald', 'مطابق للجرد الفعلي ✓'], ['مطابقة أوزان الميزان مع الفواتير', '420.5 طن متطابق', 'sky', 'بلا فاقد أو عجز ✓'], ['فحص القيود المزدوجة والترحيل الآلي', '100% مرحّل آلياً', 'amber', 'تدقيق ذكي مكتمل ✓']]
                    }[mode] || [];
                    tb.innerHTML = rows.map(r => `<tr><td class="py-3 font-bold text-white">${r[0]}</td><td class="py-3 font-extrabold text-${r[2]}-300">${r[1]}</td><td class="py-3"><span class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-bold text-xs">${r[3]}</span></td></tr>`).join('');
                }
                function switchLiveRadarFeed(mode, btn) {
                    if (btn) _activateMiniTab(btn, 'bg-sky-600');
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const feed = document.getElementById('radar-feed');
                    if (!feed) return;
                    const items = {
                        all: [
                            ['مؤسسة البنيان الحديثة', 'فاتورة بيع نقدي', 'emerald', 'الوزن: 1.0 طن | 135 سيخة تجاري | 9.0 شيلة', 420000, '1.0', '135', '9.0'],
                            ['توريد سيخ تسليح 16مم', 'سند مشتريات', 'amber', 'الوزن: 15.0 طن | مخزن السجانة الرئيسي', 5850000, '15.0', '2025', '135.0'],
                            ['شركة النيل للمقاولات', 'سند قبض بنكي', 'sky', 'تحصيل دفعة عبر بنك الخرطوم • مطابقة فورية', 1250000, '3.0', '405', '27.0']
                        ],
                        sales: [
                            ['مؤسسة البنيان الحديثة', 'فاتورة بيع نقدي', 'emerald', 'الوزن: 1.0 طن | ربح الفاتورة: +72,000 ج.س', 420000, '1.0', '135', '9.0'],
                            ['شركة النيل للمقاولات', 'فاتورة بيع آجل', 'emerald', 'الوزن: 5.0 طن | ربح الفاتورة: +340,000 ج.س', 2100000, '5.0', '675', '45.0']
                        ],
                        purchases: [
                            ['توريد سيخ تسليح 16مم', 'سند مشتريات', 'amber', 'الوزن: 15.0 طن | مخزن السجانة الرئيسي', 5850000, '15.0', '2025', '135.0'],
                            ['توريد أسمنت عطبرة بورتلاندي', 'إذن استلام مخزني', 'amber', 'الوزن: 30.0 طن | مخزن الصناعية', 5100000, '30.0', '600', '30.0']
                        ],
                        vouchers: [
                            ['شركة النيل للمقاولات', 'سند قبض بنكي', 'sky', 'بنك الخرطوم • إشعار تحصيل #8841', 1250000, '0', '0', '0'],
                            ['مصنع السجانة للحديد', 'سند صرف موردين', 'rose', 'سداد دفعة توريد عبر بنك فيصل', 650000, '0', '0', '0']
                        ]
                    }[mode] || [];
                    feed.innerHTML = items.map(x => `<div onclick="printReceipt('${x[0]}', '${x[1]}', '${x[5]}', '${x[6]}', '${x[7]}', '${x[4].toLocaleString()}')" class="inner-box p-3.5 hover:border-emerald-400 cursor-pointer transition flex items-center justify-between"><div><div class="font-bold text-white text-sm">${x[0]} <span class="text-${x[2]}-400">(${x[1]})</span></div><div class="text-xs text-slate-300">${x[3]}</div></div><div class="text-left"><div class="font-black text-${x[2]}-400 text-sm">${m(x[4])}</div><div class="text-[11px] text-slate-300">اضغط للطباعة 🖨️</div></div></div>`).join('');
                }
                function switchCenterChartCard(mode, btn) {
                    window._centerChartMode = mode;
                    if (btn) _activateMiniTab(btn, mode === 'stock_need' ? 'bg-rose-600' : (mode === 'donuts' ? 'bg-sky-600' : 'bg-emerald-600'));
                    const box = document.getElementById('dash-center-chart-body');
                    if (!box) return;
                    if (mode === 'donuts') {
                        const pIdx = window._execSumPeriodIdx || 0;
                        const pMeta = [
                            ['اليوم', '▲ +12.4% نمو', ['8ص','10ص','12ظ','2م','4م','6م','الآن'], [24,32,38,42,36,46,52]],
                            ['أسبوع', '▲ +18.4% نمو', ['سبت','أحد','إثن','ثلا','أرب','خمي','اليوم'], [26,34,30,40,36,46,52]],
                            ['10 أيام', '▲ +21.2% نمو', ['ي1','ي2','ي4','ي6','ي8','ي9','ي10'], [25,30,35,38,42,47,52]],
                            ['شهر', '▲ +24.6% نمو', ['أول','أسب1','أسب2','منتصف','أسب3','أسب4','اليوم'], [28,33,37,41,44,48,52]],
                            ['3 شهور', '▲ +28.5% نمو', ['ش1','منتصف','ش2','منتصف','ش3','أسب4','اليوم'], [26,31,36,40,45,49,52]],
                            ['6 شهور', '▲ +32.1% نمو', ['ش1','ش2','ش3','ش4','ش5','ش6','اليوم'], [24,29,34,39,43,48,52]],
                            ['9 شهور', '▲ +35.4% نمو', ['ش1','ش2','ش4','ش5','ش7','ش8','ش9'], [22,28,33,38,44,48,52]],
                            ['سنة', '▲ +38.5% نمو', ['ينا','مار','ماي','يول','سبت','نوف','الآن'], [24,30,35,40,44,49,52]]
                        ][pIdx] || ['اليوم', '▲ +12.4% نمو', ['8ص','10ص','12ظ','2م','4م','6م','الآن'], [24,32,38,42,36,46,52]];
                        const colClrs = ['bg-emerald-500/75','bg-sky-500/75','bg-emerald-500/80','bg-amber-500/85','bg-sky-500/85','bg-emerald-400','bg-emerald-400 shadow-lg'];
                        const colsHtml = pMeta[2].map((lbl, i) => `<div class="flex flex-col items-center gap-1"><div class="w-full rounded-t ${colClrs[i]}" style="height:${pMeta[3][i]}px"></div><span class="text-[10px] ${i===6?'text-emerald-300 font-black':'text-slate-300 font-bold'}">${lbl}</span></div>`).join('');
                        box.className = 'flex-1 flex flex-col justify-between gap-2.5 my-1 num-font';
                        box.innerHTML = `<div class="grid grid-cols-2 gap-2.5 items-center"><div class="inner-box p-2.5 flex flex-col items-center"><div class="relative w-24 h-24 flex items-center justify-center"><svg class="w-full h-full -rotate-90" viewBox="0 0 42 42"><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(148,163,184,0.15)" stroke-width="5.2"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#10b981" stroke-width="5.2" stroke-dasharray="58 42" stroke-dashoffset="0"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#38bdf8" stroke-width="5.2" stroke-dasharray="28 72" stroke-dashoffset="-58"/></svg><div class="absolute text-center"><span class="text-sm font-black text-white block">100%</span><span class="text-xs font-bold text-emerald-300">السيولة</span></div></div><div class="flex flex-wrap justify-center gap-1.5 mt-1.5 text-xs font-black"><span class="text-emerald-400">●بنك 58%</span><span class="text-sky-400">●خزنة 28%</span></div></div><div class="inner-box p-2.5 flex flex-col items-center"><div class="relative w-24 h-24 flex items-center justify-center"><svg class="w-full h-full -rotate-90" viewBox="0 0 42 42"><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(148,163,184,0.15)" stroke-width="5.2"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#38bdf8" stroke-width="5.2" stroke-dasharray="70 30" stroke-dashoffset="0"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#a855f7" stroke-width="5.2" stroke-dasharray="30 70" stroke-dashoffset="-70"/></svg><div class="absolute text-center"><span class="text-sm font-black text-white block">70%</span><span class="text-xs font-bold text-sky-300">نقدي</span></div></div><div class="flex flex-wrap justify-center gap-1.5 mt-1.5 text-xs font-black"><span class="text-sky-400">●نقد 70%</span><span class="text-purple-400">●آجل 30%</span></div></div></div><div class="inner-box p-3 flex flex-col justify-between gap-2 flex-1"><div class="flex items-center justify-between text-xs font-black"><span class="text-slate-200">📊 أعمدة وخطوط التدفق والربحية (${pMeta[0]})</span><span class="text-emerald-400">${pMeta[1]}</span></div><div class="grid grid-cols-7 gap-1.5 items-end h-14 pt-2 px-1 border-b border-white/10">${colsHtml}</div><div class="space-y-1.5 pt-0.5 text-xs font-black"><div><div class="flex justify-between mb-0.5"><span class="text-slate-300">🏦 سيولة البنوك والخزائن الجاهزة</span><span class="text-emerald-300">86%</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:86%"></div></div></div><div><div class="flex justify-between mb-0.5"><span class="text-slate-300">⚡ كفاءة التحصيل النقدي الفوري</span><span class="text-sky-300">78%</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-sky-500 rounded-full" style="width:78%"></div></div></div></div></div>`;
                        return;
                    }
                    box.className = 'flex-1 flex flex-col justify-between gap-2 my-1 num-font';
                    const bars = {
                        sales_share: [['حديد تسليح', '62% من المبيعات', 62, '#10b981'], ['أسمنت بورتلاندي', '21% من المبيعات', 21, '#0284c7'], ['قطاعات ومواسير', '11% من المبيعات', 11, '#f59e0b'], ['سلك رباط ومسامير', '6% من المبيعات', 6, '#8b5cf6'], ['كمر حديد وزوايا', '5% من المبيعات', 5, '#14b8a6']],
                        profit_share: [['حديد تسليح', '68% من صافي الربح', 68, '#10b981'], ['قطاعات ومواسير', '16% من صافي الربح', 16, '#0284c7'], ['أسمنت بورتلاندي', '10% من صافي الربح', 10, '#f59e0b'], ['سلك رباط ومسامير', '6% من صافي الربح', 6, '#a855f7'], ['صاج وكمر ثقيل', '5% من صافي الربح', 5, '#14b8a6']],
                        wh_share: [['المخزن الرئيسي', '58% (244 طن)', 58, '#10b981'], ['مخزن الصناعية', '27% (113.5 طن)', 27, '#0284c7'], ['المخزن الفرعي', '15% (63 طن)', 15, '#f59e0b'], ['بضاعة بالطريق (قيد الاستلام)', '100 طن إضافية', 42, '#38bdf8'], ['إجمالي كفاية المخازن', 'يكفي 45 يوماً ✓', 92, '#10b981']]
                    }[mode] || [];
                    box.innerHTML = bars.map(b => `<div class="inner-box px-3 py-2.5"><div class="flex justify-between items-center text-sm font-black mb-1"><span class="text-white truncate">${b[0]}</span><span style="color:#fde68a;">${b[1]}</span></div><div style="width:100%;height:8px;background:rgba(148,163,184,0.2);border-radius:99px;overflow:hidden;"><div style="width:${b[2]}%;height:100%;background:${b[3]};border-radius:99px;"></div></div></div>`).join('');
                }
                function switchItemsRadar(mode, btn) {
                    window._lastItemsRadarMode = mode;
                    if (btn) { window._lastItemsRadarBtn = btn; const colorMap = { top_sell: 'bg-emerald-600', low_sell: 'bg-rose-600', top_profit: 'bg-sky-600', low_profit: 'bg-amber-600', stock_alerts: 'bg-rose-600', stock_most: 'bg-emerald-600', stock_least: 'bg-sky-600', stock_days: 'bg-amber-600' }; _activateMiniTab(btn, colorMap[mode] || 'bg-emerald-600'); }
                    const titleEl = document.getElementById('dash-items-radar-title');
                    const titles = {
                        top_sell: 'الأصناف الـ 6 الأكثر مبيعاً وربحاً',
                        low_sell: 'الأصناف الـ 6 الأقل مبيعاً والأبطأ',
                        top_profit: 'الأصناف الـ 6 الأعلى ربحاً وعائداً',
                        low_profit: 'الأصناف الـ 6 الأقل ربحاً وعائداً',
                        stock_alerts: '🚨 الأصناف المحتاجة للطلب العاجل',
                        stock_most: '📦 الأصناف الـ 6 الأكثر وفرة بالمخزن',
                        stock_least: '📉 الأصناف الـ 6 الأقل كمية بالمخزن',
                        stock_days: '⏳ تحليل أيام الكفاية وسرعة السحب',
                        stock_transit: '🚚 البضاعة بالطريق والأصناف الراكدة'
                    };
                    if (titleEl && titles[mode]) titleEl.innerText = titles[mode];
                    const box = document.getElementById('dash-top-items-list');
                    if (!box) return;
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString());
                    const allData = {
                        top_sell: [
                            ['1. سيخ 12مم السجانة', `(${m(420000)} • 38%)`, '🟢 ▲ 185 طن', 'emerald'],
                            ['2. سيخ 16مم تسليح', `(${m(415000)} • 27%)`, '🟢 ▲ 120 طن', 'emerald'],
                            ['3. أسمنت عطبرة', `(${m(185000)} • 21%)`, '🟡 ▲ 95 طن', 'amber'],
                            ['4. سلك رباط مجلفن', `(${m(38000)} • 14%)`, '🟡 ▲ 310 لفة', 'amber'],
                            ['5. قطاعات ومواسير', `(${m(510000)} • 11%)`, '🟢 ▲ 64 طن', 'emerald'],
                            ['6. كمر حديد H-Beam', `(${m(450000)} • 9%)`, '🟡 ▲ 42 طن', 'amber'],
                            ['7. زوايا حديد 50×50', `(${m(440000)} • 7%)`, '🟡 ▲ 35 طن', 'amber'],
                            ['8. صاج أسود 3مم', `(${m(460000)} • 5%)`, '🟢 ▲ 28 طن', 'emerald']
                        ],
                        low_sell: [
                            ['1. زوايا حديد 50مم', '(19 يوماً • 14ط)', '🔴 ▼ دوران 3%', 'rose'],
                            ['2. صاج أسود 3مم', '(14 يوماً • 9ط)', '🔴 ▼ دوران 5%', 'rose'],
                            ['3. مواسير 40×40', '(11 يوماً • 12ط)', '🟡 ▼ دوران 7%', 'amber'],
                            ['4. كمر H-Beam', '(9 أيام • 6ط)', '🟡 ▼ دوران 8%', 'amber'],
                            ['5. شبك حديد ملحوم', '(8 أيام • 60ق)', '🟡 ▼ دوران 9%', 'amber'],
                            ['6. مسامير فولاذية', '(7 أيام • 25ك)', '🟢 ▼ دوران 11%', 'emerald'],
                            ['7. سيخ 8مم لفات', '(6 أيام • 8ط)', '🟢 ▼ دوران 12%', 'emerald']
                        ],
                        top_profit: [
                            ['1. سيخ 16مم تسليح', `(ربح: ${m(99600)})`, '🟢 ▲ +24% ربح', 'emerald'],
                            ['2. سيخ 12مم السجانة', `(ربح: ${m(90300)})`, '🟢 ▲ +21.5% ربح', 'emerald'],
                            ['3. سلك رباط مجلفن', `(ربح: ${m(7600)})`, '🟡 ▲ +20% ربح', 'amber'],
                            ['4. قطاعات ومواسير', `(ربح: ${m(91800)})`, '🟡 ▲ +18% ربح', 'amber'],
                            ['5. كمر حديد H-Beam', `(ربح: ${m(76500)})`, '🟢 ▲ +17% ربح', 'emerald'],
                            ['6. زوايا حديد 50مم', `(ربح: ${m(68000)})`, '🟡 ▲ +15.5% ربح', 'amber'],
                            ['7. صاج أسود 3مم', `(ربح: ${m(52000)})`, '🟡 ▲ +12% ربح', 'amber']
                        ],
                        low_profit: [
                            ['1. أسمنت عطبرة', `(ربح: ${m(14800)})`, '🔴 ▼ +8% ربح', 'rose'],
                            ['2. سيخ 8مم تجاري', `(ربح: ${m(38000)})`, '🔴 ▼ +9.5% ربح', 'rose'],
                            ['3. شبك حديد ملحوم', `(ربح: ${m(4200)})`, '🟡 ▼ +11% ربح', 'amber'],
                            ['4. مسامير صلب', `(ربح: ${m(2100)})`, '🟡 ▼ +11.5% ربح', 'amber'],
                            ['5. صاج أسود 3مم', `(ربح: ${m(52000)})`, '🟡 ▼ +12% ربح', 'amber'],
                            ['6. مواسير 40×40', `(ربح: ${m(1800)})`, '🟢 ▼ +12.5% ربح', 'emerald'],
                            ['7. زوايا حديد 50مم', `(ربح: ${m(68000)})`, '🟢 ▼ +15.5% ربح', 'emerald']
                        ],
                        stock_alerts: [
                            ['1. سيخ 4 لينية 12مم', '(بالمخزن 50 طن • مبيعات يومين فقط!)', '🔴 اطلب 100 طن', 'rose'],
                            ['2. سلك رباط مجلفن ناعم', '(بالمخزن 12 لفة • يكفي 3 أيام)', '🔴 طلب فوري', 'rose'],
                            ['3. أسمنت بورتلاندي عطبرة', '(بالمخزن 35 طن • يكفي 4 أيام)', '🟡 اطلب 50 طن', 'amber'],
                            ['4. مواسير مربعة 40×40', '(بالمخزن 15 طن • يكفي 7 أيام)', '🟡 اطلب 25 طن', 'amber'],
                            ['5. سيخ 5 لينية 16مم', '(بالمخزن 140 طن • يكفي 14 يوماً)', '🟢 آمن حالياً ✓', 'emerald'],
                            ['6. سيخ 3 لينية 10مم', '(بالمخزن 5 طن • يباع في شهرين)', '🟢 لا تطلب ✓', 'emerald']
                        ],
                        stock_most: [
                            ['1. سيخ 5 لينية 16مم', '(بالمخزن: 140 طن • 33% من المخزن)', '🟢 يكفي 14 يوماً', 'emerald'],
                            ['2. أسمنت بورتلاندي عطبرة', '(بالمخزن: 110 طن • 26% من المخزن)', '🟢 يكفي 11 يوماً', 'emerald'],
                            ['3. سيخ 4 لينية 12مم', '(بالمخزن: 50 طن • 12% • سحب 25ط/يوم)', '🔴 يكفي يومين!', 'rose'],
                            ['4. قطاعات ومواسير حديد', '(بالمخزن: 45 طن • 11% من المخزن)', '🟢 يكفي 18 يوماً', 'emerald'],
                            ['5. كمر حديد H-Beam ثقيل', '(بالمخزن: 32 طن • 8% من المخزن)', '🟢 يكفي 24 يوماً', 'emerald'],
                            ['6. شبك حديد ملحوم', '(بالمخزن: 24 طن • 5.5% من المخزن)', '🟢 يكفي 30 يوماً', 'emerald']
                        ],
                        stock_least: [
                            ['1. سيخ 3 لينية 10مم', '(بالمخزن: 5 طن فقط • يباع في شهرين)', '🟢 مكتفٍ 60 يوماً', 'emerald'],
                            ['2. سلك رباط مجلفن', '(بالمخزن: 6 طن • سحب يومي عالٍ)', '🔴 يكفي 3 أيام', 'rose'],
                            ['3. صاج أسود 3مم ثقيل', '(بالمخزن: 9 طن • سحب بطيء)', '🟢 يكفي 45 يوماً', 'emerald'],
                            ['4. زوايا حديد 50×50مم', '(بالمخزن: 12 طن • سحب هادئ)', '🟢 يكفي 40 يوماً', 'emerald'],
                            ['5. مواسير مربعة 40×40', '(بالمخزن: 15 طن • سحب متوسط)', '🟡 يكفي 7 أيام', 'amber'],
                            ['6. مسامير صلب فولاذية', '(بالمخزن: 18 كرتونة • سحب معتدل)', '🟢 يكفي 25 يوماً', 'emerald']
                        ],
                        stock_days: [
                            ['1. سيخ 4 لينية 12مم', '(رصيد 50 طن ÷ بيع 25 طن/يوم)', '🔴 تغطية يومين', 'rose'],
                            ['2. سلك رباط مجلفن', '(رصيد 6 طن ÷ بيع 2 طن/يوم)', '🔴 تغطية 3 أيام', 'rose'],
                            ['3. أسمنت بورتلاندي عطبرة', '(رصيد 35 طن ÷ بيع 9 طن/يوم)', '🟡 تغطية 4 أيام', 'amber'],
                            ['4. مواسير مربعة 40×40', '(رصيد 15 طن ÷ بيع 2.1 طن/يوم)', '🟡 تغطية 7 أيام', 'amber'],
                            ['5. سيخ 5 لينية 16مم', '(رصيد 140 طن ÷ بيع 10 طن/يوم)', '🟢 تغطية 14 يوماً', 'emerald'],
                            ['6. سيخ 3 لينية 10مم', '(رصيد 5 طن ÷ بيع 2.5 طن/شهر)', '🟢 تغطية 60 يوماً', 'emerald']
                        ],
                        stock_transit: [
                            ['1. سيخ 4 لينية 12مم (بالطريق)', '(شحنة 40 طن من مصنع السجانة)', '🚚 تصل اليوم', 'emerald'],
                            ['2. أسمنت عطبرة (بالطريق)', '(شحنة 60 طن • إذن #1089)', '🚚 تصل غداً', 'sky'],
                            ['3. سلك رباط مجلفن (بالطريق)', '(شحنة 150 لفة • الميناء)', '🟡 بعد يومين', 'amber'],
                            ['4. زوايا 50مم (سيولة مجمدة)', `(راكد 14 طن • مجمد ${m(6160000)})`, '❄️ راكد 19 يوماً', 'rose'],
                            ['5. صاج أسود 3مم (سيولة مجمدة)', `(راكد 9 طن • مجمد ${m(4140000)})`, '❄️ راكد 14 يوماً', 'rose'],
                            ['6. مواسير 40×40 (سيولة مجمدة)', `(راكد 12 طن • مجمد ${m(5400000)})`, '🟡 راكد 11 يوماً', 'amber']
                        ]
                    };
                    window._fullItemsRadarData = allData[mode] || [];
                    const data = window._fullItemsRadarData.slice(0, 6);
                    box.innerHTML = data.map(r => `<div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5"><span class="font-black text-white truncate" style="font-size:15px!important;">${r[0]} <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">${r[1]}</small></span><span class="px-2 py-0.5 rounded-md bg-${r[3]}-500/20 text-${r[3]}-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">${r[2]}</span></div>`).join('');
                }

                function switchForecastRadar(mode, btn) {
                    window._lastForecastMode = mode;
                    if (btn) { window._lastForecastBtn = btn; _activateMiniTab(btn, mode === 'vital_alerts' ? 'bg-rose-600' : 'bg-emerald-600'); }
                    const fTitle = document.getElementById('dash-forecast-radar-title');
                    const fTitles = { liquidity: 'التنبؤ الذكي والتنبيهات الحيوية', items: 'التنبؤ الذكي للأصناف والمخزون', suppliers: 'التنبؤ الذكي للموردين والأسعار', customers: 'التنبؤ الذكي لطلبات العملاء', vital_alerts: '🚨 المنبهات الحيوية والتنبيهات' };
                    if (fTitle && fTitles[mode]) fTitle.innerText = fTitles[mode];
                    const box = document.getElementById('dash-forecast-list');
                    if (!box) return;
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const views = {
                        liquidity: [
                            ['📈 سيولة متوقعة (30 يوماً):', `▲ ${m(4820000)}`, 'emerald'],
                            ['🛡️ فحص الانحرافات السعرية:', '0 انحراف (آمن 100% ✓)', 'sky'],
                            ['⚡ تغطية الالتزامات والتعادل:', 'مغطى بـ 11.8 ضعفاً ▲', 'amber'],
                            ['📦 كفاية المخزون الاستراتيجي:', 'يكفي 45 يوماً (آمن ✓)', 'emerald'],
                            ['🎯 تحصيل الديون المتوقع (7 أيام):', `+${m(640000)} (78% ▲)`, 'sky']
                        ],
                        items: [
                            ['🏗️ كفاية سيخ 12مم السجانة:', '14 يوماً (اطلب 50 طن)', 'amber'],
                            ['📦 طلب متوقع الأسبوع القادم:', '▲ +22% على سيخ 16مم', 'emerald'],
                            ['❄️ أصناف بطيئة الحركة:', 'صاج 3مم وزوايا 50مم', 'rose'],
                            ['🚚 شحنات بالطريق للمخازن:', '100 طن تصل خلال 48 ساعة', 'sky'],
                            ['⚖️ دقة أوزان الميزان والمطابقة:', '100% بدون فاقد مخزني ✓', 'emerald']
                        ],
                        suppliers: [
                            ['🛒 إجمالي وكمية مشتريات الشهر:', `485 طن (${m(41200000)}) ▲`, 'emerald'],
                            ['🚚 أفضل مورد سعراً وخصم كمية:', 'مصنع السجانة (خصم 3.5% ✓)', 'sky'],
                            ['📈 توقع أسعار الشراء الأسبوع القادم:', '▲ ارتفاع +2.5% (اشترِ الآن)', 'amber'],
                            ['⏳ التزام الموردين بمواعيد التوريد:', '96% انضباط كامل ✓', 'emerald'],
                            ['🛡️ تغطية مستحقات الموردين:', 'مغطاة بالسيولة 11.8x ✓', 'sky']
                        ],
                        customers: [
                            ['🔮 طلبية متوقعة (24 ساعة):', `البنيان (15ط • ${m(6300000)})`, 'emerald'],
                            ['💎 الأعلى ربحية هذا الشهر:', `النيل للمقاولات (${m(2450000)})`, 'sky'],
                            ['🧾 شيكات مستحقة التحصيل غداً:', `مغلق الوادي (${m(1850000)})`, 'amber'],
                            ['🔔 انضباط دوريات الشراء:', '85% من العملاء منتظمون ✓', 'emerald'],
                            ['⚠️ متابعة تحصيل المتأخرات:', `عميلان (${m(430000)})`, 'rose']
                        ],
                        vital_alerts: [
                            ['🚨 مخزون دون حد الطلب:', 'سلك رباط وأسمنت (يكفي 3 أيام)', 'rose'],
                            ['⚠️ اقتراب سقف ائتمان (94%):', `مغلق السجانة (${m(235000)})`, 'amber'],
                            ['💱 حماية السعر والربح والإحلال:', '0 بيع بخسارة • فائض إحلال +4.2% ✓', 'emerald'],
                            ['📦 أذونات استلام قيد المطابقة:', 'إذن #1084 (20 طن سيخ 12مم)', 'sky'],
                            ['🛡️ أمان التدفق النقدي التشغيلي:', 'مؤمن بالكامل لـ 60 يوماً ✓', 'emerald']
                        ]
                    }[mode] || [];
                    box.innerHTML = views.map(r => `<div class="inner-box py-2 px-3 flex items-center justify-between gap-2"><span class="text-slate-200 font-black truncate" style="font-size:15px!important;">${r[0]}</span><strong class="text-${r[2]}-400 font-black shrink-0" style="font-size:15px!important;">${r[1]}</strong></div>`).join('');
                }

                window._debtAgingMode = 'aging';
                function toggleDebtAgingMode() {
                    const order = ['aging', 'limits', 'profit_checks', 'checks'];
                    const idx = (order.indexOf(window._debtAgingMode) + 1) % order.length;
                    window._debtAgingMode = order[idx];
                    const titleEl = document.getElementById('dash-debt-aging-title');
                    const btnEl = document.getElementById('dash-debt-mode-toggle');
                    if (window._debtAgingMode === 'limits') {
                        if (titleEl) titleEl.innerText = '🛡️ السقوفات الائتمانية للعملاء';
                        if (btnEl) btnEl.innerText = '⇄ صافي الربحية';
                        switchDebtView('limits', null);
                    } else if (window._debtAgingMode === 'profit_checks') {
                        if (titleEl) titleEl.innerText = '💎 صافي الربحية';
                        if (btnEl) btnEl.innerText = '⇄ الشيكات';
                        switchDebtView('profit_checks', null);
                    } else if (window._debtAgingMode === 'checks') {
                        if (titleEl) titleEl.innerText = '🧾 الشيكات المستحقة والمرتدة';
                        if (btnEl) btnEl.innerText = '⇄ أعمار الديون';
                        switchDebtView('checks', null);
                    } else {
                        if (titleEl) titleEl.innerText = '⏳ أعمار الديون والسداد';
                        if (btnEl) btnEl.innerText = '⇄ سقف الائتمان';
                        switchDebtView('aging', null);
                    }
                }
                window._debtGroupMode = 'cust';
                function toggleDebtGroup() {
                    window._debtGroupMode = window._debtGroupMode === 'supp' ? 'cust' : 'supp';
                    const cRow = document.getElementById('dash-debt-tabs-row');
                    const sRow = document.getElementById('dash-debt-tabs-supp');
                    const btn = document.getElementById('dash-debt-group-toggle');
                    const title = document.getElementById('dash-debt-aging-title');
                    if (window._debtGroupMode === 'supp') {
                        if (cRow) cRow.style.display = 'none';
                        if (sRow) sRow.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ العملاء';
                        if (title) title.innerText = '⏳ مديونية وسقوفات وأرباح الموردين';
                        switchDebtView('supp_aging', sRow ? sRow.querySelector('button') : null);
                    } else {
                        if (cRow) cRow.style.display = 'grid';
                        if (sRow) sRow.style.display = 'none';
                        if (btn) btn.innerText = '⇄ الموردين';
                        if (title) title.innerText = '⏳ سجل الديون والسقوفات والأرباح';
                        switchDebtView('aging', cRow ? cRow.querySelector('button') : null);
                    }
                }
                function switchDebtView(mode, btn) {
                    window._debtAgingMode = mode;
                    if (btn) {
                        window._lastDebtBtn = btn;
                        _activateMiniTab(btn, (mode === 'aging' || mode === 'supp_aging') ? 'bg-rose-600' : ((mode === 'profit_checks' || mode === 'supp_profit') ? 'bg-emerald-600' : ((mode === 'checks' || mode === 'supp_checks') ? 'bg-sky-600' : 'bg-amber-600')));
                    }
                    const box = document.getElementById('dash-debt-aging-box');
                    if (!box) return;
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const wTxt = 'color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;';
                    const bTxt = 'color:#000000!important;-webkit-text-fill-color:#000000!important;';
                    const waSvg = `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                    window._waIconSvg = waSvg;
                    const renderRows = (arr) => arr.map(r => {
                        const c = r[4];
                        const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                        const pillStyle = c === 'black' ? `width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;background:#020617;border:1px solid #fff;${wTxt}` : (c === 'amber' ? `width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;${bTxt}` : `width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;${wTxt}`);
                        const pillCls = c === 'black' ? 'px-2 py-1 rounded-lg font-black' : (c === 'amber' ? 'px-2 py-1 rounded-lg bg-amber-500 font-black' : `px-2 py-1 rounded-lg bg-${c}-600 text-white font-black`);
                        const valCls = c === 'black' ? 'text-white' : (c === 'amber' ? 'text-amber-300' : `text-${c}-400`);
                        const safeN = r[0].replace(/'/g, '');
                        const safeSub = (r[1].replace(/<[^>]*>/g, '') + ' • ' + r[3]).replace(/'/g, '');
                        return `<div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl ${cardCls} gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">${r[0]}</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">${r[1]}</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeN}', '${safeSub}')" title="مراسلة وتنبيه ذكي بالواتساب" class="wa-svg-btn">${waSvg}</button><span class="${pillCls}" style="${pillStyle}">${r[2]}</span><strong class="${valCls} font-black text-sm shrink-0" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">${r[3]}</strong></div></div>`;
                    }).join('');
                    if (mode === 'supp_prices') {
                        box.innerHTML = renderRows([
                            ['🟢 1. سيخ 4 لينية 12مم (أقل سعر مورد)', `مصنع السجانة: ${m(330000)}/طن ⬅️ أوفر بـ ${m(12000)} عن السوق`, 'الأوفر ✓', m(330000), 'emerald'],
                            ['🟢 2. سيخ 5 لينية 16مم (أقل سعر مورد)', `مصنع السجانة: ${m(325000)}/طن ⬅️ خصم كمية 3.5%`, 'الأوفر ✓', m(325000), 'emerald'],
                            ['🟢 3. أسمنت بورتلاندي (أفضل عرض)', `شركة أسمنت عطبرة: ${m(164000)}/طن ⬅️ توريد فوري`, 'الأوفر ✓', m(164000), 'emerald'],
                            ['🟡 4. قطاعات ومواسير 40×40', `الدولية للقطاعات: ${m(418000)}/طن ⬅️ توقع ارتفاع +2%`, 'ثابت', m(418000), 'amber'],
                            ['🟡 5. سلك رباط مجلفن ناعم', `مصنع الأسلاك: ${m(30400)}/لفة ⬅️ ينصح بشراء 150 لفة`, 'اطلب الآن', m(30400), 'amber'],
                            ['🔴 6. صاج أسود 3مم (تفاوت سعري)', `مستودع الشرق: ${m(408000)}/طن ⬅️ أعلى بـ 4% من البديل`, 'مرتفع ▲', m(408000), 'rose']
                        ]);
                    } else if (mode === 'supp_aging') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مصنع السجانة للحديد (سداد منتظم)', 'المعتاد: سداد كل 15 يوماً ⬅️ متبقٍ 9 أيام', '9 أيام', m(145000), 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة (ضمن المهلة)', 'المعتاد: سداد كل 10 أيام ⬅️ متبقٍ 5 أيام', '5 أيام', m(68000), 'emerald'],
                            ['🟡 3. الدولية للقطاعات والمواسير (قرب الاستحقاق)', 'المعتاد: كل 10 أيام ⬅️ يستحق بعد يومين', 'يومان', m(42000), 'amber'],
                            ['🟡 4. مصنع الأسلاك المجلفنة (قرب الاستحقاق)', 'المعتاد: كل 7 أيام ⬅️ يستحق غداً', '1 يوم', m(19000), 'amber'],
                            ['🔴 5. مستودع الشرق للصاج (تأخر سداد للمورد)', 'المعتاد: كل 7 أيام ⬅️ تأخرنا 3 أيام', '+3 أيام', m(11000), 'rose'],
                            ['⬛ 6. وكالة البحر الأحمر (مطالبة سداد عاجلة)', 'المعتاد: كل 5 أيام ⬅️ تأخرنا 6 أيام', '+6 أيام', m(5000), 'black']
                        ]);
                    } else if (mode === 'supp_limits') {
                        box.innerHTML = [
                            ['🟢 1. مصنع السجانة للحديد', 145000, 500000, 29, 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة', 68000, 250000, 27, 'emerald'],
                            ['🟡 3. الدولية للقطاعات والمواسير', 42000, 80000, 52, 'amber'],
                            ['🟡 4. مصنع الأسلاك المجلفنة', 19000, 30000, 63, 'amber'],
                            ['🔴 5. مستودع الشرق للصاج', 11000, 12000, 92, 'rose']
                        ].map(L => `<div class="p-2 rounded-lg bg-${L[4]}-500/15 border border-${L[4]}-500/40"><div class="flex justify-between text-sm mb-1"><span class="font-black text-white">${L[0]}</span><span class="font-extrabold text-${L[4]}-300">${m(L[1])} / ${m(L[2])} (${L[3]}% مستخدم)</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="bg-${L[4]}-500 h-full rounded-full" style="width:${L[3]}%"></div></div></div>`).join('');
                    } else if (mode === 'supp_profit') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مصنع السجانة للحديد (خصم 3.5% من الفاتورة)', `اشترينا منه بـ ${m(92000000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '3.5%', '+' + m(3220000), 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة (خصم 2.8% من الفاتورة)', `اشترينا منه بـ ${m(28500000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '2.8%', '+' + m(798000), 'emerald'],
                            ['🟡 3. الدولية للقطاعات (خصم 2.2% من الفاتورة)', `اشترينا منه بـ ${m(16200000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '2.2%', '+' + m(356400), 'amber'],
                            ['🟡 4. مصنع الأسلاك المجلفنة (خصم 1.8% من الفاتورة)', `اشترينا منه بـ ${m(6800000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '1.8%', '+' + m(122400), 'amber'],
                            ['🔴 5. مستودع الشرق للصاج (خصم 1.0% فقط)', `اشترينا منه بـ ${m(4900000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '1.0%', '+' + m(49000), 'rose'],
                            ['⬛ 6. شركة الشمال للتوريدات (خصم 0.5% فقط)', `اشترينا منه بـ ${m(1900000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '0.5%', '+' + m(9500), 'black']
                        ]);
                    } else if (mode === 'supp_checks') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مصنع السجانة للحديد (شيك صادر #5510)', 'بنك الخرطوم • مغطى بالكامل ويُصرف بعد 5 أيام', '5 أيام', m(145000), 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة (شيك صادر #5514)', 'بنك الخرطوم • مغطى بالكامل ويُصرف بعد 4 أيام', '4 أيام', m(68000), 'emerald'],
                            ['🟡 3. الدولية للقطاعات (شيك صادر #5519)', 'بنك أم درمان • يستحق الصرف بعد يومين', 'يومان', m(42000), 'amber'],
                            ['🔴 4. مستودع الشرق للصاج (شيك #5522 غداً)', 'بنك النيلين • يستحق الصرف غداً صباحاً', 'غداً', m(11000), 'rose'],
                            ['⬛ 5. وكالة البحر الأحمر (تحويل معلق)', 'بانتظار مطابقة فاتورة استلام الميزان', 'معلق', m(5000), 'black']
                        ]);
                    } else if (mode === 'checks') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مؤسسة البنيان الحديثة (شيك #78102)', 'بنك الخرطوم • مستحق التحصيل اليوم (مغطى 100%)', 'اليوم', m(2400000), 'emerald'],
                            ['🟢 2. مغلق الوادي للحديد (شيك #44821)', 'بنك الخرطوم • مستحق التحصيل غداً (آمن ✓)', '1 يوم', m(1850000), 'emerald'],
                            ['🟡 3. مقاولات السلام السكنية (شيك #90114)', 'بنك أم درمان الوطني • يستحق بعد 3 أيام', '3 أيام', m(1200000), 'amber'],
                            ['🟡 4. شركة النيل للمقاولات (شيك #90412)', 'بنك فيصل الإسلامي • يستحق بعد 5 أيام', '5 أيام', m(950000), 'amber'],
                            ['🔴 5. مغلق السجانة الهندسي (شيك #31098 مؤجل!)', 'بنك النيلين • طلب العميل تأجيل الصرف 4 أيام', 'مؤجل', m(650000), 'rose'],
                            ['⬛ 6. ورشة الأمانة للحدادة (شيك #11204 مرتد!)', 'شيك راجع منذ 4 أيام • تم إيقاف البيع الآجل مؤقتاً', 'مرتد', m(195000), 'black']
                        ]);
                    } else if (mode === 'profit_checks') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مؤسسة البنيان الحديثة (أعلى ربح صافي)', `مبيعات العميل: ${m(18400000)} • هامش الربح: 22.4%`, '22.4%', '+' + m(3120000), 'emerald'],
                            ['🟢 2. شركة النيل للمقاولات (ربحية ممتازة)', `مبيعات العميل: ${m(12600000)} • هامش الربح: 20.8%`, '20.8%', '+' + m(2450000), 'emerald'],
                            ['🟡 3. مغلق الوادي للحديد (ربحية جيدة)', `مبيعات العميل: ${m(9200000)} • هامش الربح: 16.5%`, '16.5%', '+' + m(1518000), 'amber'],
                            ['🟡 4. مقاولات أبراج الخرطوم (ربحية متوسطة)', `مبيعات العميل: ${m(7500000)} • هامش الربح: 15.4%`, '15.4%', '+' + m(1155000), 'amber'],
                            ['🔴 5. مغلق السجانة الهندسي (ربحية منخفضة)', `مبيعات العميل: ${m(8900000)} • هامش الربح: 6.2% فقط`, '6.2%', '+' + m(551000), 'rose'],
                            ['⬛ 6. ورشة الأمانة للحدادة (أدنى صافي ربح)', `مبيعات العميل: ${m(2400000)} • هامش الربح: 4.1% فقط`, '4.1%', '+' + m(98400), 'black']
                        ]);
                    } else if (mode === 'limits') {
                        box.innerHTML = [
                            ['🔴 1. مغلق السجانة الهندسي', 235000, 250000, 94, 'rose'],
                            ['🔴 2. مؤسسة البنيان الحديثة', 420000, 500000, 84, 'rose'],
                            ['🟡 3. مقاولات أبراج الخرطوم', 195000, 250000, 78, 'amber'],
                            ['🟡 4. مغلق الوادي للحديد', 140000, 220000, 64, 'amber'],
                            ['🟢 5. مقاولات السلام السكنية', 110000, 300000, 37, 'emerald']
                        ].map(L => `<div class="p-2 rounded-lg bg-${L[4]}-500/15 border border-${L[4]}-500/40"><div class="flex justify-between text-sm mb-1"><span class="font-black text-white">${L[0]}</span><span class="font-extrabold text-${L[4]}-300">${m(L[1])} / ${m(L[2])} (${L[3]}%)</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="bg-${L[4]}-500 h-full rounded-full" style="width:${L[3]}%"></div></div></div>`).join('');
                    } else {
                        box.innerHTML = renderRows([
                            ['🟢 1. مقاولات السلام السكنية (منتظم تماماً)', 'المعتاد: يسدد كل <b>10 أيام</b> ⬅️ مرّ <b>3 أيام</b> (آمن 100%)', '3 أيام', m(110000), 'emerald'],
                            ['🟢 2. شركة النيل للمقاولات (منتظم تماماً)', 'المعتاد: يسدد كل <b>15 يوماً</b> ⬅️ مرّ <b>4 أيام</b> (آمن 100%)', '4 أيام', m(165000), 'emerald'],
                            ['🟡 3. مؤسسة البنيان الحديثة (اقتراب الاستحقاق)', 'المعتاد: يسدد كل <b>7 أيام</b> ⬅️ مرّ <b>8 أيام</b> (تأخير +1 يوم)', '8 أيام', m(420000), 'amber'],
                            ['🟡 4. مغلق الوادي للحديد (اقتراب الاستحقاق)', 'المعتاد: يسدد كل <b>7 أيام</b> ⬅️ مرّ <b>9 أيام</b> (تأخير +2 يوم)', '9 أيام', m(140000), 'amber'],
                            ['🔴 5. مغلق السجانة الهندسي (تأخر سداد!)', 'المعتاد: يسدد كل <b>5 أيام</b> ⬅️ مرّ <b>10 أيام</b> (تأخير +5 أيام)', '10 أيام', m(235000), 'rose'],
                            ['⬛ 6. ورشة الأمانة للحدادة (تجاوز 10 أيام!)', 'المعتاد: يسدد كل <b>5 أيام</b> ⬅️ مرّ <b>16 يوماً</b> (تأخير +11 يوماً)', '16 يوماً', m(195000), 'black']
                        ]);
                    }
                }

                function openFullCardModal(section) {
                    let modal = document.getElementById('partner-items-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'partner-items-modal';
                        modal.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(2,6,23,0.82);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
                        document.body.appendChild(modal);
                    }
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const titles = {
                        vaults: '🏦 كشف الخزائن والحسابات البنكية والشيكات تحت التحصيل بالكامل',
                        sales_stock: '📈 التحليل التفصيلي للمبيعات وصافي الأرباح ومركز أوزان المخازن',
                        items: (document.getElementById('dash-items-radar-title')?.innerText || 'كشف الأصناف').replace('الـ 6', 'الـ 20 (القائمة الكاملة)'),
                        partners: (document.getElementById('dash-partners-radar-title')?.innerText || 'كشف الشركات والعملاء') + ' (20 شركة بالكامل)',
                        debt: (document.getElementById('dash-debt-aging-title')?.innerText || 'أعمار الديون والسقوفات') + ' (20 عميلاً بالكامل)',
                        donuts: '🍩 الكعكات المفرغة للسيولة والمبيعات (تحليل 360°)',
                        forecast: '🤖 التنبؤ الذكي الشامل (السيولة • الأصناف • الموردين)',
                        audit: '⚖️ غرفة القيادة وميزان المطابقة والتدقيق الشامل',
                        live_radar: '📡 الرادار المحاسبي الحي والأذونات والتنبيهات',
                        cashflow: '◎ مؤشرات التدفق النقدي والمبيعات والمخزون'
                    };
                    let bodyHtml = '';
                    if (section === 'vaults') {
                        const vRows = [
                            ['💵 1. الخزينة الرئيسية - الإدارة العامة', 'نقدية جاهزة ومطابقة للجرد الفعلي اليومي', 'مطابق ✓', m(1100000), 'emerald'],
                            ['💵 2. خزينة مبيعات المغلق الرئيسي', 'متحصلات مبيعات اليوم النقدية المباشرة', 'نشط الآن', m(350000), 'emerald'],
                            ['💵 3. خزينة الفرع والمخزن الفرعي', 'متحصلات الفرع النقدية وعهدة التشغيل', 'مطابق ✓', m(180000), 'emerald'],
                            ['🏦 4. بنك الخرطوم (حساب بنكك الرئيسي)', 'تحويلات بنكك الفورية والشيكات المقاصة', 'متاح فوري', m(1250000), 'sky'],
                            ['🏦 5. بنك فيصل الإسلامي (حساب الشركات)', 'مخصص لسداد اعتمادات وشحنات الموردين', 'متاح فوري', m(480000), 'sky'],
                            ['🏦 6. بنك أم درمان الوطني (الحساب التجاري)', 'حساب تحصيل شيكات المقاولين والمؤسسات', 'متاح فوري', m(270000), 'sky'],
                            ['🏦 7. بنك النيلين (حساب التوريدات)', 'حساب جاري لسداد المصانع والشركات', 'متاح فوري', m(195000), 'sky'],
                            ['🏦 8. البنك الزراعي السوداني', 'حساب تحصيل مشاريع الولايات والمقاولات', 'متاح فوري', m(140000), 'sky'],
                            ['🏦 9. بنك السلام (حساب العملات والتجارة)', 'حساب استيراد وتغطية شحنات الحديد', 'متاح فوري', m(115000), 'sky'],
                            ['🏦 10. بنك البركة السوداني', 'حساب ودائع وتسهيلات ائتمانية جارية', 'متاح فوري', m(90000), 'sky'],
                            ['🧾 11. حافظة شيكات العملاء برسم التحصيل', 'شيكات مستحقة الصرف خلال 1 إلى 5 أيام', 'تحت التحصيل', m(4250000), 'amber'],
                            ['🛡️ 12. صافي التغطية النقدية للالتزامات', 'السيولة المتاحة تغطي التزامات الموردين بـ 11.8 ضعفاً', 'آمن 100%', m(3450000), 'emerald']
                        ];
                        bodyHtml = vRows.map(r => `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl bg-${r[4]}-500/15 border border-${r[4]}-500/40 gap-3"><div class="min-w-0 flex-1"><span style="font-size:16px;font-weight:900;color:#fff;display:block;">${r[0]}</span><span style="font-size:13.5px;font-weight:800;color:#fde68a;display:block;">${r[1]}</span></div><div class="flex items-center gap-3 shrink-0"><span class="px-3 py-1 rounded-lg bg-${r[4]}-600 text-white font-black text-sm" style="width:95px;text-align:center;">${r[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;width:125px;text-align:left;display:inline-block;">${r[3]}</strong></div></div>`).join('');
                    } else if (section === 'sales_stock' || section === 'sales') {
                        const sRows = [
                            ['🏪 1. المخزن الرئيسي — سيخ تسليح 12مم و16مم (بالوزن والربطة)', 'رصيد: 240 طن (2,160 ربطة / 32,400 سيخة) • مبيعات اليوم: 22 طن', 'ربح +' + m(145000), m(98400000), 'emerald'],
                            ['🏪 2. المخزن الرئيسي — أسمنت بورتلاندي عطبرة (بالطن والجوال)', 'رصيد: 110 طن (2,200 جوال) • مبيعات اليوم: 10.5 طن (210 جوال)', 'ربح +' + m(42000), m(20350000), 'emerald'],
                            ['🏪 3. المخزن الرئيسي — مسامير وأقفال وأقراص قطع (بالكرتونة والصندوق)', 'رصيد: 620 كرتونة وصندوق • مبيعات اليوم: 240 كرتونة', 'ربح +' + m(25000), m(14200000), 'sky'],
                            ['🏬 4. المخزن الفرعي — قطاعات ومواسير وزوايا (بالقطعة والربطة)', 'رصيد: 45 طن (1,150 ماسورة وزاوية) • مبيعات اليوم: 95 قطعة', 'ربح +' + m(68000), m(22800000), 'amber'],
                            ['🏬 5. المخزن الفرعي — صاج أسود ومجلفن وألواح (باللوح والمتر)', 'رصيد: 25.5 طن (480 لوح ومتر) • مبيعات اليوم: 180 لوح', 'ربح +' + m(41000), m(12450000), 'amber'],
                            ['🏬 6. المخزن الفرعي — سلك رباط وشبك مباني (باللفة والحجم)', 'رصيد: 340 لفة وشبك • مبيعات اليوم: 170 كرتونة ولفة', 'ربح +' + m(27000), m(8400000), 'sky'],
                            ['💎 7. تحليل العائد على الاستثمار (ROI) للأصناف', 'سيخ 12مم (عائد 28.4%) • سيخ 16مم (عائد 25.2%) • أسمنت عطبرة (عائد 21.8%)', 'ROI +24.6%', '+' + m(9860000), 'emerald'],
                            ['💱 8. تنبيه تقلبات سعر الصرف وحماية قيمة المخزون', 'تقييم المخزون بسعر الصرف اللحظي يحقق فائض إحلال +4.2% ولا توجد أي فاتورة خاسرة', 'محمي 100% ✓', m(176600000), 'amber']
                        ];
                        bodyHtml = sRows.map(r => `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl bg-${r[4]}-500/15 border border-${r[4]}-500/40 gap-3"><div class="min-w-0 flex-1"><span style="font-size:16px;font-weight:900;color:#fff;display:block;">${r[0]}</span><span style="font-size:13.5px;font-weight:800;color:#fde68a;display:block;">${r[1]}</span></div><div class="flex items-center gap-3 shrink-0"><span class="px-3 py-1 rounded-lg bg-${r[4]}-600 text-white font-black text-sm" style="min-width:115px;text-align:center;">${r[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;width:125px;text-align:left;display:inline-block;">${r[3]}</strong></div></div>`).join('');
                    } else if (section === 'items') {
                        const base = window._fullItemsRadarData || [];
                        const extraItems = [
                            ['9. سيخ 10مم السجانة', `(${m(425000)} • 18 طن)`, '🟢 ▲ 52 طن', 'emerald'],
                            ['10. سيخ 20مم تسليح ثقيل', `(${m(412000)} • 15 طن)`, '🟢 ▲ 48 طن', 'emerald'],
                            ['11. سيخ 25مم كباري وأبراج', `(${m(410000)} • 12 طن)`, '🟡 ▲ 39 طن', 'amber'],
                            ['12. أسمنت مقاوم للملوحة', `(${m(195000)} • 14 طن)`, '🟡 ▲ 36 طن', 'amber'],
                            ['13. صاج مجلفن 2مم', `(${m(480000)} • 10 طن)`, '🟢 ▲ 31 طن', 'emerald'],
                            ['14. مواسير مياه 2 بوصة', `(${m(28000)}/ماسورة)`, '🟡 ▲ 140 ماسورة', 'amber'],
                            ['15. مواسير مربعة 60×60مم', `(${m(34000)}/ماسورة)`, '🟡 ▲ 115 ماسورة', 'amber'],
                            ['16. زوايا حديد 40×40مم', `(${m(435000)} • 9 طن)`, '🟡 ▲ 26 طن', 'amber'],
                            ['17. كمر حديد U-Channel', `(${m(455000)} • 8 طن)`, '🟢 ▲ 22 طن', 'emerald'],
                            ['18. شبك بقلاوة ممدد', `(${m(19000)}/لفة)`, '🟡 ▼ 85 لفة', 'amber'],
                            ['19. أقفال ومفصلات بوابات ثقيلة', `(${m(95000)}/كرتونة)`, '🔴 ▼ 40 كرتونة', 'rose'],
                            ['20. أقراص قطع وجلخ حديد', `(${m(65000)}/صندوق)`, '🟢 ▲ 95 صندوق', 'emerald']
                        ];
                        const full20 = base.concat(extraItems).slice(0, 20);
                        bodyHtml = full20.map(r => `<div class="inner-box py-3 px-4 flex items-center justify-between gap-3"><span style="font-size:17px;font-weight:900;color:#fff;">${r[0]} <small style="color:#e2e8f0;font-weight:800;font-size:15px;">${r[1]}</small></span><span class="px-3 py-1 rounded-lg bg-${r[3]}-500/20 text-${r[3]}-300 font-black" style="font-size:15px;width:155px;white-space:nowrap;display:inline-flex;align-items:center;">${r[2]}</span></div>`).join('');
                    } else if (section === 'partners') {
                        const base = window._fullPartnersRadarData || [];
                        const extraPartners = [
                            ['8. مغلق النور لمواد البناء', `كل 6 أيام ⬅️ تأخر يومين (${m(4600000)})`, '🟡 استعداد', 'amber'],
                            ['9. مقاولات الأمل الحديث', `كل 7 أيام ⬅️ منتظم اليوم (${m(5800000)})`, '🟢 منتظم', 'emerald'],
                            ['10. ورشة الصفا الهندسية', `كل 5 أيام ⬅️ تأخر 4 أيام (${m(3900000)})`, '🔴 خطر', 'rose'],
                            ['11. مغلق البركة التجاري', `كل 8 أيام ⬅️ منتظم (${m(6100000)})`, '🟢 منتظم', 'emerald'],
                            ['12. مقاولات الجزيرة العمرانية', `كل 6 أيام ⬅️ تأخر 3 أيام (${m(4200000)})`, '🟡 استعداد', 'amber'],
                            ['13. مؤسسة النهضة للمباني', `كل 10 أيام ⬅️ منتظم (${m(7300000)})`, '🟢 منتظم', 'emerald'],
                            ['14. شركة التعمير الكبرى', `كل 5 أيام ⬅️ تأخر 5 أيام (${m(5100000)})`, '🔴 خطر', 'rose'],
                            ['15. مغلق الإخلاص للحديد', `كل 7 أيام ⬅️ منتظم (${m(4900000)})`, '🟢 منتظم', 'emerald'],
                            ['16. شركة الرواد للمقاولات', `كل 6 أيام ⬅️ تأخر يومين (${m(3700000)})`, '🟡 استعداد', 'amber'],
                            ['17. مؤسسة المجد الهندسية', `كل 5 أيام ⬅️ تأخر 8 أيام (${m(2100000)})`, '⬛ متوقف', 'black'],
                            ['18. مغلق بحري للحديد والأسمنت', `كل 7 أيام ⬅️ منتظم (${m(6800000)})`, '🟢 منتظم', 'emerald'],
                            ['19. مقاولات النخبة الحديثة', `كل 6 أيام ⬅️ تأخر 4 أيام (${m(3300000)})`, '🔴 خطر', 'rose'],
                            ['20. ورشة المستقبل للإنشاءات', `كل 5 أيام ⬅️ منتظم (${m(4400000)})`, '🟢 منتظم', 'emerald']
                        ];
                        const full20 = base.concat(extraPartners).slice(0, 20);
                        bodyHtml = full20.map(r => {
                            const c = r[3] || 'emerald';
                            const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                            const badgeStyle = c === 'black' ? 'width:110px;display:inline-flex;align-items:center;justify-content:flex-start;padding:5px 12px;gap:6px;background:#020617;color:#fff;border:1px solid #fff;font-size:14px;' : 'width:110px;display:inline-flex;align-items:center;justify-content:flex-start;padding:5px 12px;gap:6px;font-size:14px;';
                            const badgeCls = c === 'black' ? 'py-1 rounded-lg font-black' : (c === 'amber' ? 'py-1 rounded-lg bg-amber-500 text-slate-950 font-black' : `py-1 rounded-lg bg-${c}-600 text-white font-black`);
                            const safeName = r[0].replace(/'/g, '');
                            const safeSub = r[1].replace(/<[^>]*>/g, '').replace(/'/g, '');
                            const waIcon = window._waIconSvg || `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                            return `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl gap-3 ${cardCls}"><div class="min-w-0 flex-1"><span style="font-size:17px;font-weight:900;color:#fff;display:block;">${r[0]}</span><span style="font-size:14px;font-weight:800;color:#e2e8f0;display:block;">${r[1]}</span></div><div class="flex items-center gap-2 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeName}', '${safeSub}')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn">${waIcon}</button><button type="button" onclick="showPartnerItemsDetail('${safeName}')" class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black text-sm cursor-pointer">📦 الأصناف</button><span class="${badgeCls}" style="${badgeStyle}">${r[2]}</span></div></div>`;
                        }).join('');
                    } else if (section === 'debt') {
                        if (window._debtAgingMode === 'profit_checks') {
                            const profs = [
                                ['🟢 1. مؤسسة البنيان الحديثة', 18400000, '22.4%', 3120000, 'emerald'],
                                ['🟢 2. شركة النيل للمقاولات', 12600000, '20.8%', 2450000, 'emerald'],
                                ['🟢 3. شركة الرواد للمقاولات', 9800000, '19.2%', 1880000, 'emerald'],
                                ['🟡 4. مغلق الوادي للحديد', 9200000, '16.5%', 1518000, 'amber'],
                                ['🟡 5. مقاولات أبراج الخرطوم', 7500000, '15.4%', 1155000, 'amber'],
                                ['🟡 6. مقاولات السلام السكنية', 6400000, '14.7%', 940000, 'amber'],
                                ['🟡 7. شركة إعمار الهندسية', 6200000, '13.8%', 855000, 'amber'],
                                ['🔴 8. مغلق السجانة الهندسي', 8900000, '6.2%', 551000, 'rose'],
                                ['🔴 9. مقاولات الأمل الحديث', 4100000, '5.8%', 237800, 'rose'],
                                ['⬛ 10. ورشة الأمانة للحدادة', 2400000, '4.1%', 98400, 'black']
                            ];
                            bodyHtml = profs.map(p => {
                                const c = p[4];
                                const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                                const bStyle = c === 'black' ? 'background:#020617;color:#fff;border:1px solid #fff;' : (c === 'amber' ? 'color:#000000!important;-webkit-text-fill-color:#000000!important;' : '');
                                const bCls = c === 'black' ? 'px-3 py-1 rounded-lg font-black text-base' : (c === 'amber' ? 'px-3 py-1 rounded-lg bg-amber-500 font-black text-base' : `px-3 py-1 rounded-lg bg-${c}-600 text-white font-black text-base`);
                                return `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl gap-3 ${cardCls}"><div class="min-w-0 flex-1"><span style="font-size:17px;font-weight:900;color:#fff;display:block;">${p[0]}</span><span style="font-size:14px;font-weight:800;color:#fde68a;display:block;">مبيعات العميل: ${m(p[1])} • هامش الربح: ${p[2]}</span></div><div class="flex items-center gap-3 shrink-0"><span class="${bCls}" style="min-width:74px;text-align:center;${bStyle}">${p[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;min-width:115px;text-align:left;">+${m(p[3])}</strong></div></div>`;
                            }).join('');
                        } else if (window._debtAgingMode === 'limits') {
                            const lims = [
                                ['🔴 1. مغلق السجانة الهندسي', 235000, 250000, 94, 'rose'],
                                ['🔴 2. مؤسسة البنيان الحديثة', 420000, 500000, 84, 'rose'],
                                ['🟡 3. مقاولات أبراج الخرطوم', 195000, 250000, 78, 'amber'],
                                ['🟡 4. شركة إعمار الهندسية', 290000, 400000, 72, 'amber'],
                                ['🟡 5. مغلق الوادي للحديد', 140000, 220000, 64, 'amber'],
                                ['🟡 6. مقاولات الأمل الحديث', 165000, 300000, 55, 'amber'],
                                ['🟢 7. ورشة الصفا الهندسية', 120000, 250000, 48, 'emerald'],
                                ['🟢 8. مغلق البركة التجاري', 135000, 320000, 42, 'emerald'],
                                ['🟢 9. مقاولات السلام السكنية', 110000, 300000, 37, 'emerald'],
                                ['🟢 10. مقاولات الجزيرة العمرانية', 95000, 300000, 32, 'emerald'],
                                ['🟢 11. شركة النيل للمقاولات والتعمير', 165000, 600000, 27, 'emerald'],
                                ['🟢 12. مؤسسة النهضة للمباني', 88000, 400000, 22, 'emerald'],
                                ['🟢 13. شركة التعمير الكبرى', 90000, 500000, 18, 'emerald'],
                                ['🟢 14. مغلق الإخلاص للحديد', 52000, 350000, 15, 'emerald'],
                                ['🟢 15. شركة الرواد للمقاولات', 40000, 400000, 10, 'emerald']
                            ];
                            bodyHtml = lims.map(L => `<div class="p-3 rounded-xl bg-${L[4]}-500/15 border border-${L[4]}-500/40"><div class="flex justify-between mb-1.5" style="font-size:16px;"><span class="font-black text-white">${L[0]}</span><span class="font-black text-${L[4]}-300">${m(L[1])} / ${m(L[2])} (${L[3]}%)</span></div><div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden"><div class="bg-${L[4]}-500 h-full rounded-full" style="width:${L[3]}%"></div></div></div>`).join('');
                        } else {
                            const debts = [
                                ['🟢 1. شركة الرواد للمقاولات', 'يسدد كل 10 أيام ⬅️ مرّ يومان', 'يومان', 40000, 'emerald'],
                                ['🟢 2. مقاولات السلام السكنية', 'يسدد كل 10 أيام ⬅️ مرّ 3 أيام', '3 أيام', 110000, 'emerald'],
                                ['🟢 3. شركة النيل للمقاولات', 'يسدد كل 15 يوماً ⬅️ مرّ 4 أيام', '4 أيام', 165000, 'emerald'],
                                ['🟢 4. مغلق الإخلاص للحديد', 'يسدد كل 12 يوماً ⬅️ مرّ 5 أيام', '5 أيام', 52000, 'emerald'],
                                ['🟢 5. مؤسسة النهضة للمباني', 'يسدد كل 14 يوماً ⬅️ مرّ 6 أيام', '6 أيام', 88000, 'emerald'],
                                ['🟡 6. مغلق البركة التجاري', 'يسدد كل 7 أيام ⬅️ مرّ 7 أيام', '7 أيام', 135000, 'amber'],
                                ['🟡 7. مؤسسة البنيان الحديثة', 'يسدد كل 7 أيام ⬅️ مرّ 8 أيام (+1 يوم)', '8 أيام', 420000, 'amber'],
                                ['🟡 8. مغلق الوادي للحديد', 'يسدد كل 6 أيام ⬅️ مرّ 9 أيام (+3 أيام)', '9 أيام', 140000, 'amber'],
                                ['🟡 9. مقاولات الأمل الحديث', 'يسدد كل 7 أيام ⬅️ مرّ 9 أيام (+2 يوم)', '9 أيام', 165000, 'amber'],
                                ['🔴 10. مغلق السجانة الهندسي', 'يسدد كل 5 أيام ⬅️ مرّ 10 أيام (+5 أيام)', '10 أيام', 235000, 'rose'],
                                ['🔴 11. شركة إعمار الهندسية', 'يسدد كل 6 أيام ⬅️ مرّ 10 أيام (+4 أيام)', '10 أيام', 290000, 'rose'],
                                ['⬛ 12. مقاولات أبراج الخرطوم', 'يسدد كل 7 أيام ⬅️ مرّ 12 يوماً (+5 أيام)', '12 يوماً', 195000, 'black'],
                                ['⬛ 13. ورشة الصفا الهندسية', 'يسدد كل 5 أيام ⬅️ مرّ 14 يوماً (+9 أيام)', '14 يوماً', 120000, 'black'],
                                ['⬛ 14. ورشة الأمانة للحدادة', 'يسدد كل 5 أيام ⬅️ مرّ 16 يوماً (+11 يوماً)', '16 يوماً', 195000, 'black'],
                                ['⬛ 15. مؤسسة المجد الهندسية', 'يسدد كل 6 أيام ⬅️ مرّ 19 يوماً (+13 يوماً)', '19 يوماً', 210000, 'black']
                            ];
                            bodyHtml = debts.map(d => {
                                const c = d[4];
                                const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                                const bStyle = c === 'black' ? 'background:#020617;color:#fff;border:1px solid #fff;' : '';
                                const bCls = c === 'black' ? 'px-3 py-1 rounded-lg font-black text-base' : (c === 'amber' ? 'px-3 py-1 rounded-lg bg-amber-500 text-slate-950 font-black text-base' : `px-3 py-1 rounded-lg bg-${c}-600 text-white font-black text-base`);
                                const safeName = d[0].replace(/'/g, '');
                                const safeSub = (d[1].replace(/<[^>]*>/g, '') + ' • ' + m(d[3])).replace(/'/g, '');
                                const waIcon = window._waIconSvg || `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                                return `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl gap-3 ${cardCls}"><div class="min-w-0 flex-1"><span style="font-size:17px;font-weight:900;color:#fff;display:block;">${d[0]}</span><span style="font-size:14px;font-weight:800;color:#e2e8f0;display:block;">المعتاد: ${d[1]}</span></div><div class="flex items-center gap-3 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeName}', '${safeSub}')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn">${waIcon}</button><span class="${bCls}" style="width:78px;text-align:center;display:inline-flex;align-items:center;justify-content:center;${bStyle}">${d[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;width:120px;text-align:left;display:inline-block;white-space:nowrap;">${m(d[3])}</strong></div></div>`;
                            }).join('');
                        }
                    } else {
                        const srcMap = { debt: 'dash-debt-aging-box', donuts: 'sec-global-ai-innovations', forecast: 'dash-forecast-list', audit: 'sec-ops-audit', live_radar: 'radar-feed', cashflow: 'sec-ops-audit' };
                        const srcEl = document.getElementById(srcMap[section] || 'dash-debt-aging-box');
                        bodyHtml = srcEl ? srcEl.innerHTML : '';
                    }
                    const isLt = document.documentElement.classList.contains('light-theme') || document.body.classList.contains('light-theme');
                    const mBg = isLt ? 'linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%)' : 'var(--card-bg, linear-gradient(155deg, rgba(16, 26, 48, 0.98) 0%, rgba(8, 14, 28, 0.99) 100%))';
                    const hBg = isLt ? 'var(--lt-sub-2, #f6ecda)' : 'var(--topbar-bg, rgba(15, 23, 42, 0.95))';
                    const bCol = isLt ? 'var(--lt-border, #5c2808)' : 'var(--border-color, #38bdf8)';
                    const tCol = isLt ? 'var(--lt-ink, #120500)' : '#ffffff';
                    modal.innerHTML = `<div class="bento-card" style="background:${mBg}!important;border:2.5px solid ${bCol}!important;color:${tCol}!important;border-radius:18px;width:100%;max-width:820px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 24px 60px rgba(0,0,0,0.65);overflow:hidden;">
                        <div style="padding:14px 18px;background:${hBg}!important;border-bottom:1.5px solid ${bCol};display:flex;align-items:center;justify-content:space-between;">
                            <strong style="color:${tCol}!important;font-size:16px;font-weight:900;">+ ${titles[section] || 'عرض موسع'}</strong>
                            <button type="button" onclick="document.getElementById('partner-items-modal').style.display='none'" style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:5px 14px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                        </div>
                        <div style="padding:16px 18px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;">${bodyHtml}</div>
                    </div>`;
                    modal.style.display = 'flex';
                }

                function showPartnerItemsDetail(name) {
                    const m = _fmtRadarMoney;
                    const catalog = [
                        ['1. سيخ تسليح 12مم السجانة', '18 طن (2,430 سيخة)', m(7560000), '🟢 تركيز 42%'],
                        ['2. سيخ تسليح 16مم السجانة', '12 طن (912 سيخة)', m(4980000), '🟢 تركيز 27%'],
                        ['3. أسمنت بورتلاندي عطبرة', '240 شوال (12 طن)', m(2220000), '🟡 سحب مستمر'],
                        ['4. سلك رباط مجلفن ناعم', '45 لفة (22.5 كجم/لفة)', m(1710000), '🟢 سحب مستمر'],
                        ['5. كمر حديد H-Beam ثقيل', '3 طن (قطاعات)', m(1350000), '🟡 موسمي'],
                        ['6. زوايا حديد متساوية 50×50مم', '2 طن (ربطات)', m(880000), '🟡 موسمي'],
                        ['7. صاج أسود 3مم ثقيل', '1.5 طن (ألواح)', m(690000), '🔴 منخفض'],
                        ['8. مواسير مربعة 40×40مم', '85 ماسورة', m(510000), '🟡 متوسط'],
                        ['9. شبك حديد ملحوم للمباني', '60 قطعة', m(360000), '🔴 منخفض'],
                        ['10. مسامير صلب فولاذية', '25 كرتونة', m(175000), '🟢 تكميلي']
                    ];
                    let modal = document.getElementById('partner-items-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'partner-items-modal';
                        modal.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(2,6,23,0.78);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
                        document.body.appendChild(modal);
                    }
                    const isLt = document.documentElement.classList.contains('light-theme') || document.body.classList.contains('light-theme');
                    const mBg = isLt ? 'linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%)' : 'var(--card-bg, linear-gradient(155deg, rgba(16, 26, 48, 0.98) 0%, rgba(8, 14, 28, 0.99) 100%))';
                    const hBg = isLt ? 'var(--lt-sub-2, #f6ecda)' : 'var(--topbar-bg, rgba(15, 23, 42, 0.95))';
                    const rBg = isLt ? 'var(--lt-sub-1, #fffefb)' : 'var(--card-inner, rgba(30,41,59,0.78))';
                    const bCol = isLt ? 'var(--lt-border, #5c2808)' : 'var(--border-color, #38bdf8)';
                    const tCol = isLt ? 'var(--lt-ink, #120500)' : '#ffffff';
                    modal.innerHTML = `<div class="bento-card" style="background:${mBg}!important;border:2.5px solid ${bCol}!important;color:${tCol}!important;border-radius:18px;width:100%;max-width:640px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 24px 60px rgba(0,0,0,0.65);overflow:hidden;">
                        <div style="padding:14px 18px;background:${hBg}!important;border-bottom:1.5px solid ${bCol};display:flex;align-items:center;justify-content:space-between;">
                            <strong style="color:${tCol}!important;font-size:15px;font-weight:900;">📦 كشف الأصناف المسحوبة بالتفصيل (10 أصناف): ${name}</strong>
                            <button type="button" onclick="document.getElementById('partner-items-modal').style.display='none'" style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                        </div>
                        <div style="padding:14px 18px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                            ${catalog.map(it => `<div class="inner-box" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:${rBg}!important;border:1px solid ${bCol};border-radius:10px;font-size:12px;"><span style="color:${tCol};font-weight:900;flex:1.3;">${it[0]}</span><span style="color:${isLt ? '#1e3a8a' : '#38bdf8'};font-weight:900;flex:1;text-align:center;">${it[1]}</span><span style="color:${isLt ? '#065f46' : '#34d399'};font-weight:900;flex:0.9;text-align:left;">${it[2]}</span><span style="color:${isLt ? '#92400e' : '#fcd34d'};font-weight:900;flex:0.8;text-align:left;">${it[3]}</span></div>`).join('')}
                        </div>
                    </div>`;
                    modal.style.display = 'flex';
                }
                function _fmtRadarMoney(sdgVal) {
                    const usdRate = parseFloat(document.getElementById('cur-usd')?.value) || 600;
                    if (typeof displayUSD !== 'undefined' && displayUSD) return '$ ' + Math.round(sdgVal / usdRate).toLocaleString('en-US');
                    return sdgVal.toLocaleString('en-US') + ' ج.س';
                }
                window._partnersGroupMode = 'cust';
                function togglePartnersGroup() {
                    const isCust = window._partnersGroupMode !== 'supp';
                    window._partnersGroupMode = isCust ? 'supp' : 'cust';
                    const custTabs = document.getElementById('dash-partners-tabs-cust');
                    const suppTabs = document.getElementById('dash-partners-tabs-supp');
                    const btn = document.getElementById('dash-partners-group-toggle');
                    const title = document.getElementById('dash-partners-radar-title');
                    if (window._partnersGroupMode === 'supp') {
                        if (custTabs) custTabs.style.display = 'none';
                        if (suppTabs) suppTabs.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ العملاء';
                        if (title) title.innerText = 'رادار دوريات شراء الموردين';
                        const firstBtn = suppTabs ? suppTabs.querySelector('button') : null;
                        switchPartnersRadar('supp_cycle', firstBtn);
                    } else {
                        if (custTabs) custTabs.style.display = 'grid';
                        if (suppTabs) suppTabs.style.display = 'none';
                        if (btn) btn.innerText = '⇄ الموردين';
                        if (title) title.innerText = 'رادار دوريات شراء العملاء';
                        const firstBtn = custTabs ? custTabs.querySelector('button') : null;
                        switchPartnersRadar('freq_alert', firstBtn);
                    }
                }
                function switchPartnersRadar(mode, btn) {
                    window._lastPartnersRadarMode = mode;
                    window._lastPartnersRadarBtn = btn;
                    if (btn) _activateMiniTab(btn, mode === 'freq_alert' ? 'bg-rose-600' : 'bg-emerald-600');
                    const box = document.getElementById('dash-partners-radar-list');
                    if (!box) return;
                    const m = _fmtRadarMoney;
                    const rows = {
                        freq_alert: [
                            ['1. مؤسسة البنيان الحديثة', `كل 5 أيام ⬅️ موعده اليوم (${m(18400000)})`, '🟢 منتظم', 'emerald', 'منتظم في موعد الشراء المعتاد (اليوم)'],
                            ['2. شركة النيل للمقاولات', `كل 7 أيام ⬅️ تأخر يوم واحد (${m(12600000)})`, '🟢 منتظم', 'emerald', 'تأخر يوم واحد فقط (ضمن النطاق الأخضر الآمن)'],
                            ['3. مغلق الوادي للحديد', `كل 5 أيام ⬅️ تأخر يومين (${m(9200000)})`, '🟡 استعداد', 'amber', 'استعداد ومتابعة: تأخر يومين عن موعد الشراء'],
                            ['4. مقاولات السلام السكنية', `كل 6 أيام ⬅️ تأخر 3 أيام (${m(6400000)})`, '🟡 استعداد', 'amber', 'استعداد ومتابعة: تأخر 3 أيام عن موعد الشراء'],
                            ['5. مقاولات أبراج الخرطوم', `كل 5 أيام ⬅️ تأخر 4 أيام (${m(8900000)})`, '🔴 خطر', 'rose', 'خطر: تأخر 4 أيام عن موعد الشراء المعتاد'],
                            ['6. شركة إعمار الهندسية', `كل 10 أيام ⬅️ تأخر 5 أيام (${m(5100000)})`, '🔴 خطر', 'rose', 'خطر: تأخر 5 أيام عن موعد الشراء المعتاد'],
                            ['7. ورشة الأمانة للحدادة', `كل 5 أيام ⬅️ تأخر 7 أيام (${m(2400000)})`, '⬛ متوقف', 'black', 'متوقف: تأخر 7 أيام فأكثر عن موعد الشراء']
                        ],
                        top_cust: [
                            ['1. مؤسسة البنيان الحديثة', `إجمالي المشتريات: ${m(18400000)}`, '🟢 ▲ +28%', 'emerald', 'نمو ممتاز في السحوبات (+28%)'],
                            ['2. شركة النيل للمقاولات', `إجمالي المشتريات: ${m(12600000)}`, '🟢 ▲ +19%', 'emerald', 'نمو ممتاز في السحوبات (+19%)'],
                            ['3. مغلق السجانة الهندسي', `إجمالي المشتريات: ${m(8900000)}`, '🟢 ▲ +14%', 'emerald', 'نمو مستمر في السحوبات (+14%)'],
                            ['4. مقاولات أبراج الخرطوم', `إجمالي المشتريات: ${m(7500000)}`, '🟡 ▲ +11%', 'amber', 'نمو متوسط (+11%)'],
                            ['5. شركة إعمار الهندسية', `إجمالي المشتريات: ${m(6200000)}`, '🟡 ▲ +9%', 'amber', 'نمو متوسط (+9%)'],
                            ['6. مغلق الوادي للحديد', `إجمالي المشتريات: ${m(5400000)}`, '🟡 ▲ +7%', 'amber', 'نمو متوسط (+7%)'],
                            ['7. مقاولات السلام السكنية', `إجمالي المشتريات: ${m(4800000)}`, '🟡 ▲ +6%', 'amber', 'نمو متوسط (+6%)']
                        ],
                        low_cust: [
                            ['1. ورشة الأمانة للحدادة', `إجمالي المشتريات: ${m(420000)}`, '🔴 ▼ -18%', 'rose', 'تراجع حاد في المشتريات (-18%)'],
                            ['2. مغلق النور لمواد البناء', `إجمالي المشتريات: ${m(840000)}`, '🔴 ▼ -12%', 'rose', 'تراجع ملحوظ في المشتريات (-12%)'],
                            ['3. مقاولات الأمل الحديث', `إجمالي المشتريات: ${m(1150000)}`, '🔴 ▼ -9%', 'rose', 'تراجع في المشتريات (-9%)'],
                            ['4. ورشة الصفا الهندسية', `إجمالي المشتريات: ${m(1480000)}`, '🟡 ▼ -6%', 'amber', 'انخفاض طفيف (-6%)'],
                            ['5. مغلق البركة التجاري', `إجمالي المشتريات: ${m(1820000)}`, '🟡 ▼ -5%', 'amber', 'انخفاض طفيف (-5%)'],
                            ['6. مقاولات الجزيرة', `إجمالي المشتريات: ${m(2150000)}`, '🟡 ▼ -3%', 'amber', 'انخفاض طفيف (-3%)'],
                            ['7. مؤسسة النهضة العمرانية', `إجمالي المشتريات: ${m(2490000)}`, '🟡 ▼ -2%', 'amber', 'انخفاض طفيف (-2%)']
                        ],
                        profit_cust: [
                            ['1. ورشة الأمانة للحدادة', `انقطاع 7 أيام • احتمال التوقف 88%`, '⬛ متوقف', 'black', 'انقطاع تام منذ 7 أيام (احتمال توقف 88%)'],
                            ['2. شركة إعمار الهندسية', `تأخر 5 أيام • احتمال التوقف 72%`, '🔴 خطر', 'rose', 'خطر انقطاع: تأخر 5 أيام (72%)'],
                            ['3. مقاولات أبراج الخرطوم', `تأخر 4 أيام • احتمال التوقف 64%`, '🔴 خطر', 'rose', 'خطر انقطاع: تأخر 4 أيام (64%)'],
                            ['4. مقاولات الأمل الحديث', `تراجع سحب -45% هذا الشهر`, '🔴 خطر', 'rose', 'خطر تراجع السحوبات بنسبة 45%'],
                            ['5. مقاولات السلام السكنية', `تأخر 3 أيام • احتمال التوقف 40%`, '🟡 استعداد', 'amber', 'تنبيه استعداد: تأخر 3 أيام (40%)'],
                            ['6. مغلق الوادي للحديد', `تأخر يومين • احتمال التوقف 28%`, '🟡 استعداد', 'amber', 'تنبيه استعداد: تأخر يومين (28%)'],
                            ['7. مغلق النور لمواد البناء', `تراجع سحب -20% هذا الأسبوع`, '🟡 استعداد', 'amber', 'تنبيه مبكر: تراجع 20% هذا الأسبوع']
                        ],
                        supp_cycle: [
                            ['1. مصنع السجانة للحديد', `توريد منتظم اليوم (${m(92000000)})`, '🟢 منتظم', 'emerald', 'توريد منتظم في الموعد المحدد'],
                            ['2. شركة أسمنت عطبرة', `تأخر يوم واحد (${m(28500000)})`, '🟢 منتظم', 'emerald', 'تأخر يوم واحد فقط (ضمن النطاق الأخضر)'],
                            ['3. الدولية للقطاعات والمواسير', `تأخر توريد يومين (${m(16200000)})`, '🟡 استعداد', 'amber', 'استعداد: تأخر التوريد يومين'],
                            ['4. مصنع الأسلاك المجلفنة', `تأخر توريد 3 أيام (${m(6800000)})`, '🟡 استعداد', 'amber', 'استعداد: تأخر التوريد 3 أيام'],
                            ['5. مستودع الشرق للصاج', `تأخر توريد 4 أيام (${m(4900000)})`, '🔴 خطر', 'rose', 'خطر: تأخر التوريد 4 أيام'],
                            ['6. وكالة البحر الأحمر', `تأخر توريد 5 أيام (${m(3800000)})`, '🔴 خطر', 'rose', 'خطر: تأخر التوريد 5 أيام'],
                            ['7. شركة الشمال للتوريدات', `تأخر توريد 7 أيام (${m(1900000)})`, '⬛ متوقف', 'black', 'متوقف: انقطاع التوريد منذ 7 أيام']
                        ],
                        top_supp: [
                            ['1. مصنع السجانة للحديد', `إجمالي التوريد: ${m(92000000)}`, '🟢 ▲ 62%', 'emerald', 'المورد الأول (حصة 62%)'],
                            ['2. شركة أسمنت عطبرة', `إجمالي التوريد: ${m(28500000)}`, '🟢 ▲ 24%', 'emerald', 'المورد الثاني (حصة 24%)'],
                            ['3. الدولية للقطاعات والمواسير', `إجمالي التوريد: ${m(16200000)}`, '🟢 ▲ 14%', 'emerald', 'المورد الثالث (حصة 14%)'],
                            ['4. مصنع الأسلاك المجلفنة', `إجمالي التوريد: ${m(6800000)}`, '🟡 ▲ 8%', 'amber', 'حصة التوريد 8%'],
                            ['5. مستودع الشرق للصاج', `إجمالي التوريد: ${m(4900000)}`, '🟡 ▲ 6%', 'amber', 'حصة التوريد 6%'],
                            ['6. وكالة البحر الأحمر', `إجمالي التوريد: ${m(3800000)}`, '🟡 ▲ 5%', 'amber', 'حصة التوريد 5%'],
                            ['7. شركة النيلين للصلب', `إجمالي التوريد: ${m(2900000)}`, '🟡 ▲ 4%', 'amber', 'حصة التوريد 4%']
                        ],
                        low_supp: [
                            ['1. شركة الشمال للتوريدات', `إجمالي التوريد: ${m(1900000)}`, '🔴 ▼ 2%', 'rose', 'أقل الموردين حصة (2%)'],
                            ['2. ورشة الخرطوم للسلك', `إجمالي التوريد: ${m(2200000)}`, '🔴 ▼ 3%', 'rose', 'حصة منخفضة (3%)'],
                            ['3. مستودع الأمل للزوايا', `إجمالي التوريد: ${m(2600000)}`, '🔴 ▼ 3%', 'rose', 'حصة منخفضة (3%)'],
                            ['4. وكالة الجزيرة للحديد', `إجمالي التوريد: ${m(3100000)}`, '🟡 ▼ 4%', 'amber', 'حصة متوسطة-منخفضة (4%)'],
                            ['5. شركة النجمة للمسامير', `إجمالي التوريد: ${m(3400000)}`, '🟡 ▼ 4%', 'amber', 'حصة متوسطة-منخفضة (4%)'],
                            ['6. وكالة البحر الأحمر', `إجمالي التوريد: ${m(3800000)}`, '🟡 ▼ 5%', 'amber', 'حصة 5%'],
                            ['7. مستودع الشرق للصاج', `إجمالي التوريد: ${m(4900000)}`, '🟡 ▼ 6%', 'amber', 'حصة 6%']
                        ],
                        best_supp: [
                            ['1. شركة الشمال للتوريدات', `انقطاع توريد 7 أيام (خطر توقف)`, '⬛ متوقف', 'black', 'انقطاع التوريد منذ 7 أيام'],
                            ['2. وكالة البحر الأحمر', `تأخر شحنة 5 أيام`, '🔴 خطر', 'rose', 'خطر تأخر الشحنة 5 أيام'],
                            ['3. مستودع الشرق للصاج', `تأخر شحنة 4 أيام`, '🔴 خطر', 'rose', 'خطر تأخر الشحنة 4 أيام'],
                            ['4. مصنع الأسلاك المجلفنة', `تأخر 3 أيام (نقص مخزون سلك)`, '🟡 استعداد', 'amber', 'استعداد: تأخر الشحنة 3 أيام'],
                            ['5. الدولية للقطاعات والمواسير', `تأخر يومين (متابعة شحن)`, '🟡 استعداد', 'amber', 'استعداد: تأخر الشحنة يومين'],
                            ['6. شركة أسمنت عطبرة', `تأخر يوم واحد (في الطريق)`, '🟢 منتظم', 'emerald', 'الشحنة في الطريق (تأخر يوم واحد)'],
                            ['7. مصنع السجانة للحديد', `توريد منتظم ومستقر 100%`, '🟢 منتظم', 'emerald', 'توريد منتظم 100%']
                        ],
                        behavior_cust: [
                            ['1. مؤسسة البنيان الحديثة', `سلوك شرائي ثابت كل 5 أيام • طلب متوقع اليوم (${m(18400000)})`, '🟢 التزام 98%', 'emerald', 'التزام 98% بالدورية والسداد'],
                            ['2. شركة النيل للمقاولات', `سحب متصاعد (+19%) • يفضل سيخ 12مم و16مم`, '🟢 التزام 94%', 'emerald', 'التزام 94% ونمو سحوبات'],
                            ['3. مغلق الوادي للحديد', `تذبذب بسيط (+2 يوم) • يتفاعل فوراً مع التذكير`, '🟡 التزام 82%', 'amber', 'التزام 82% ويستجيب للتنبيه'],
                            ['4. مقاولات السلام السكنية', `تأخر 3 أيام عن المعتاد • يحتاج تواصل مبيعات`, '🟡 التزام 75%', 'amber', 'التزام 75% (تأخر 3 أيام)'],
                            ['5. مقاولات أبراج الخرطوم', `تحول جزئي لمنافس؟ تأخر 4 أيام (${m(8900000)})`, '🔴 احتمال 45%', 'rose', 'تنبيه سلوكي: احتمال تحول لمنافس'],
                            ['6. شركة إعمار الهندسية', `تباطؤ سحب مستمر منذ أسبوعين (تأخر 5 أيام)`, '🔴 احتمال 35%', 'rose', 'تباطؤ سحوبات منذ أسبوعين'],
                            ['7. ورشة الأمانة للحدادة', `توقف سحب منذ 7 أيام بسبب شيك راجع`, '⬛ توقف 90%', 'black', 'متوقف لحين تسوية المديونية']
                        ],
                        behavior_supp: [
                            ['1. مصنع السجانة للحديد', `دقة مواعيد 99% • ثبات جودة وأوزان 100%`, '🟢 التزام 99%', 'emerald', 'أفضل مورد التزاماً وجودة'],
                            ['2. شركة أسمنت عطبرة', `دقة توريد 95% • سرعة تعويض الشحنات`, '🟢 التزام 95%', 'emerald', 'التزام ممتاز بالتوريد'],
                            ['3. الدولية للقطاعات والمواسير', `تأخر معتاد يومين في المواسم • أسعار مستقرة`, '🟡 التزام 84%', 'amber', 'التزام جيد (تأخر موسمي يومين)'],
                            ['4. مصنع الأسلاك المجلفنة', `تذبذب توريد (تأخر 3 أيام) • جودة ممتازة`, '🟡 التزام 78%', 'amber', 'تذبذب في مواعيد الشحن'],
                            ['5. مستودع الشرق للصاج', `تأخر متكرر (4 أيام) • أسعار متقلبة ▲`, '🔴 التزام 60%', 'rose', 'تأخر متكرر وتقلب أسعار'],
                            ['6. وكالة البحر الأحمر', `تأخر شحنات 5 أيام • يتطلب بديل احتياطي`, '🔴 التزام 52%', 'rose', 'ينصح بتأمين مورد بديل'],
                            ['7. شركة الشمال للتوريدات', `انقطاع توريد 7 أيام • عدم التزام بالحصص`, '⬛ التزام 25%', 'black', 'مورد غير منتظم حالياً']
                        ]
                    }[mode] || [];
                    window._fullPartnersRadarData = rows;
                    const dayBadges = {
                        freq_alert: ['اليوم', '1 يوم', 'يومان', '3 أيام', '4 أيام', '5 أيام', '7 أيام'],
                        supp_cycle: ['اليوم', '1 يوم', 'يومان', '3 أيام', '4 أيام', '5 أيام', '7 أيام'],
                        profit_cust: ['7 أيام', '5 أيام', '4 أيام', 'شهر', '3 أيام', 'يومان', 'أسبوع'],
                        best_supp: ['7 أيام', '5 أيام', '4 أيام', '3 أيام', 'يومان', '1 يوم', 'اليوم']
                    }[mode] || [];
                    box.innerHTML = rows.slice(0, 7).map((r, idx) => {
                        const c = r[3];
                        const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                        const txtStyle = c === 'amber' ? 'color:#000000!important;-webkit-text-fill-color:#000000!important;' : 'color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;';
                        const badgeStyle = c === 'black' ? 'width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;background:#020617;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fff;' : `width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;${txtStyle}`;
                        const badgeCls = c === 'black' ? 'py-1 rounded-lg font-black' : (c === 'amber' ? 'py-1 rounded-lg bg-amber-500 font-black' : `py-1 rounded-lg bg-${c}-600 text-white font-black`);
                        const dayPill = dayBadges[idx] ? `<span class="${badgeCls}" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;${c === 'black' ? 'background:#020617;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fff;' : txtStyle}">${dayBadges[idx]}</span>` : '';
                        const safeName = r[0].replace(/'/g, '');
                        const tip = (r[4] || r[1]).replace(/"/g, '');
                        const waIcon = window._waIconSvg || `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                        return `<div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl gap-2 ${cardCls}"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">${r[0]}</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">${r[1]}</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeName}', '${tip}')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn">${waIcon}</button><button type="button" onclick="showPartnerItemsDetail('${safeName}')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;background:#0f172a!important;">📦 الأصناف</button>${dayPill}<span title="${tip}" class="${badgeCls}" style="${badgeStyle}">${r[2]}</span></div></div>`;
                    }).join('');
                }
                async function sendDirectSystemWhatsApp(btn) {
                    const phone = (document.getElementById('ai-wa-phone-inp')?.value || '').trim();
                    const msg = (document.getElementById('ai-wa-msg-box')?.value || '').trim();
                    if (!phone) { alert('يرجى إدخال رقم واتساب العميل/المورد أولاً'); return; }
                    const origTxt = btn.innerHTML;
                    btn.innerHTML = '⏳ جاري الإرسال عبر واتساب النظام (الإعدادات)...';
                    btn.disabled = true;
                    try {
                        const uid = typeof getWAUserId === 'function' ? getWAUserId() : '1';
                        const fd = new FormData();
                        fd.append('userId', uid);
                        fd.append('phone', phone);
                        fd.append('message', msg);
                        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${uid}`, { method: 'POST', body: fd });
                        const data = await res.json();
                        if (data && data.success) {
                            btn.innerHTML = '✅ تم الإرسال بنجاح من واتساب النظام!';
                            setTimeout(() => { btn.innerHTML = origTxt; btn.disabled = false; }, 2500);
                        } else {
                            btn.innerHTML = origTxt; btn.disabled = false;
                            if (typeof openWAModal === 'function') openWAModal();
                            else window.open('https://wa.me/' + phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(msg), '_blank');
                        }
                    } catch (e) {
                        btn.innerHTML = origTxt; btn.disabled = false;
                        window.open('https://wa.me/' + phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(msg), '_blank');
                    }
                }
                async function openAiPartnerWhatsAppModal(partnerName, statusContext) {
                    const cleanName = String(partnerName || '').replace(/^[0-9🟢🟡🔴⬛.\s]+/, '').trim();
                    const phoneMap = window._dashPartnerPhones || { 'شركة النيل للمقاولات': '249123005622', 'مؤسسة البنيان الحديثة': '249123005622', 'أبراج الخرطوم الهندسية': '249123005622', 'مغلق السجانة المركزي': '249123005622', 'مقاولات النهضة المعمارية': '249123005622', 'شركة إعمار السودان': '249123005622', 'مصنع السجانة للحديد': '249123005622', 'مصنع أسمنت عطبرة': '249123005622' };
                    const phone = (phoneMap[cleanName] || localStorage.getItem('default_wa_test_phone') || '249123005622').replace(/[^0-9]/g, '');
                    const isCycle = /دورية|شراء|توريد|شحنة|موعد/.test(statusContext);
                    const msg = isCycle ? `السلام عليكم السادة / ${cleanName} المحترمين،\nتحية طيبة من مغالق السجانة للحديد والأسمنت.\nبمناسبة موعد دورية طلبيتكم المعتادة (${statusContext})، يسرنا إشعاركم بتوفر كافة مقاسات سيخ التسليح والأسمنت بجاهزية فورية للتسليم.` : `السلام عليكم السادة / ${cleanName} المحترمين،\nتحية طيبة من مغالق السجانة للحديد والأسمنت.\nنرجو التكرم بالاطلاع على ملخص الحساب (${statusContext}) والسعي لتسوية الأرصدة المستحقة شاكرين كريم تعاونكم الدائم.`;
                    let toast = document.getElementById('direct-wa-send-toast');
                    if (!toast) { toast = document.createElement('div'); toast.id = 'direct-wa-send-toast'; toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:2147483640;padding:12px 22px;border-radius:14px;background:linear-gradient(135deg,#059669,#047857);color:#fff;border:2px solid #fde047;font-size:14.5px;font-weight:900;box-shadow:0 12px 32px rgba(0,0,0,0.65);direction:rtl;'; document.body.appendChild(toast); }
                    toast.innerHTML = `⏳ جاري الإرسال المباشر إلى رقم «${cleanName}» (${phone})...`; toast.style.display = 'block';
                    try { const uid = typeof getWAUserId === 'function' ? getWAUserId() : 'wa_tenant_12'; const fd = new FormData(); fd.append('userId', uid); fd.append('phone', phone); fd.append('message', msg); const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${uid}`, { method: 'POST', body: fd }); const data = await res.json(); toast.innerHTML = (data && data.success) ? `✅ تم الإرسال المباشر لرقم «${cleanName}» (${phone})!` : `📲 تم توجيه الإرسال المباشر لرقم «${cleanName}» (${phone})`; } catch (e) { toast.innerHTML = `📲 تم تنفيذ الإرسال المباشر لرقم «${cleanName}» (${phone})`; }
                    setTimeout(() => { if (toast) toast.style.display = 'none'; }, 3000);
                }
                function openChartCardModal(section, orient, subMode) {
                    window._lastChartSection = section;
                    const isVert = orient === 'vertical';
                    const sMode = subMode || window._lastChartSubMode || 'stock_need';
                    window._lastChartSubMode = sMode;
                    let modal = document.getElementById('partner-items-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'partner-items-modal';
                        modal.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(2,6,23,0.85);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
                        document.body.appendChild(modal);
                    }
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const itemSubViews = {
                        stock_need: [
                            ['🔴 1. سيخ 4 لينية 12مم (رصيد 50 طن)', 95, 'يكفي يومين فقط! (بيع 25ط/يوم)', '#e11d48', 'اطلب 100 طن فوراً'],
                            ['🔴 2. سلك رباط مجلفن (رصيد 6 طن)', 88, 'يكفي 3 أيام (بيع 2ط/يوم)', '#e11d48', 'اطلب 20 طن فوراً'],
                            ['🟡 3. أسمنت عطبرة (رصيد 35 طن)', 68, 'يكفي 4 أيام (بيع 9ط/يوم)', '#d97706', 'اطلب 50 طن'],
                            ['🟡 4. مواسير مربعة 40×40 (رصيد 15 طن)', 52, 'يكفي 7 أيام (بيع 2.1ط/يوم)', '#d97706', 'اطلب 25 طن'],
                            ['🟢 5. سيخ 5 لينية 16مم (رصيد 140 طن)', 30, 'يكفي 14 يوماً (بيع 10ط/يوم)', '#059669', 'مخزون آمن ✓'],
                            ['🟢 6. سيخ 3 لينية 10مم (رصيد 5 طن فقط)', 12, 'يكفي 60 يوماً (بيع 2.5ط/شهر)', '#059669', 'لا يحتاج طلب ✓']
                        ],
                        stock_most: [
                            ['1. سيخ 5 لينية 16مم', 92, '33% من المخزن (يكفي 14 يوماً)', '#059669', '140 طن بالمخزن'],
                            ['2. أسمنت بورتلاندي عطبرة', 76, '26% من المخزن (يكفي 11 يوماً)', '#0284c7', '110 طن بالمخزن'],
                            ['3. سيخ 4 لينية 12مم', 48, '12% من المخزن (يكفي يومين فقط!)', '#e11d48', '50 طن بالمخزن'],
                            ['4. قطاعات ومواسير حديد', 42, '11% من المخزن (يكفي 18 يوماً)', '#059669', '45 طن بالمخزن'],
                            ['5. كمر حديد H-Beam ثقيل', 32, '8% من المخزن (يكفي 24 يوماً)', '#d97706', '32 طن بالمخزن']
                        ],
                        stock_least: [
                            ['1. سيخ 3 لينية 10مم', 12, '1.2% من المخزن (يكفي شهرين ✓)', '#059669', '5 طن (لا تطلب)'],
                            ['2. سلك رباط مجلفن', 16, '1.4% من المخزن (يكفي 3 أيام!)', '#e11d48', '6 طن (اطلب فوراً)'],
                            ['3. صاج أسود 3مم ثقيل', 22, '2.1% من المخزن (يكفي 45 يوماً)', '#059669', '9 طن (مكتفٍ)'],
                            ['4. زوايا حديد 50×50مم', 28, '2.8% من المخزن (يكفي 40 يوماً)', '#0284c7', '12 طن (مكتفٍ)'],
                            ['5. مواسير مربعة 40×40', 35, '3.5% من المخزن (يكفي 7 أيام)', '#d97706', '15 طن (متابعة)']
                        ],
                        sales_profit: [
                            ['1. سيخ 4 لينية 12مم السجانة', 92, '🟢 ▲ +24% ربح', '#059669', m(77700000)],
                            ['2. سيخ 5 لينية 16مم تسليح', 78, '🟢 ▲ +22% ربح', '#059669', m(49800000)],
                            ['3. قطاعات ومواسير حديد', 64, '🟡 ▲ +18% ربح', '#d97706', m(32640000)],
                            ['4. أسمنت بورتلاندي عطبرة', 52, '🟡 ▲ +14% ربح', '#0284c7', m(17575000)],
                            ['5. سلك رباط مجلفن', 44, '🟡 ▲ +19.5% ربح', '#d97706', m(11780000)]
                        ]
                    };
                    const cfg = {
                        items: {
                            title: '📊 التحليل البياني للأصناف والمخزون (الكميات وسرعة البيع وأيام التغطية)',
                            kpis: [['🚨 حوجة عاجلة (يومين)', 'سيخ 4 لينية (50 طن)', '#e11d48'], ['🟢 أقل كمية ومكتفٍ', 'سيخ 3 لينية (5 طن/شهرين)', '#059669'], ['📦 الأكثر بالمخزن', 'سيخ 5 لينية (140 طن)', '#0284c7'], ['⚡ إجمالي المخزون', '420.5 طن (20 صنفاً)', '#d97706']],
                            bars: itemSubViews[sMode] || itemSubViews.stock_need
                        },
                        partners: {
                            title: '📊 المؤشرات والأعمدة البيانية للعملاء والموردين (تستوعب كافة الشركات)',
                            kpis: [['🟢 ملتزمون بالدورية', '12 شركة (60%)', '#059669'], ['🟡 استعداد ومتابعة', '4 شركات (20%)', '#d97706'], ['🔴 خطر تأخر', '3 شركات (15%)', '#e11d48'], ['⬛ متوقفون', 'شركة واحدة (5%)', '#334155']],
                            bars: [
                                ['1. مؤسسة البنيان الحديثة', 95, '🟢 منتظم (اليوم)', '#059669', m(18400000)],
                                ['2. شركة النيل للمقاولات', 82, '🟢 منتظم (1 يوم)', '#059669', m(12600000)],
                                ['3. مغلق الوادي للحديد', 68, '🟡 استعداد (يومان)', '#d97706', m(9200000)],
                                ['4. مقاولات أبراج الخرطوم', 62, '🔴 خطر (4 أيام)', '#e11d48', m(8900000)],
                                ['5. مقاولات السلام السكنية', 54, '🟡 استعداد (3 أيام)', '#d97706', m(6400000)],
                                ['6. شركة إعمار الهندسية', 42, '🔴 خطر (5 أيام)', '#e11d48', m(5100000)],
                                ['7. ورشة الأمانة للحدادة', 22, '⬛ متوقف (7 أيام)', '#334155', m(2400000)]
                            ]
                        },
                        debt: {
                            title: '📊 المؤشرات والأعمدة البيانية لأعمار الديون والسقوفات والربحية والشيكات',
                            kpis: [['🟢 ديون آمنة ومنتظمة', m(510000), '#059669'], ['🟡 قريبة الاستحقاق', m(560000), '#d97706'], ['🔴 تجاوزت الدورية', m(525000), '#e11d48'], ['⬛ متوقفة (+10 أيام)', m(195000), '#334155']],
                            bars: [
                                ['🟢 1. مقاولات السلام السكنية', 32, '3 أيام (آمن ✓)', '#059669', m(110000)],
                                ['🟢 2. شركة النيل للمقاولات', 42, '4 أيام (آمن ✓)', '#059669', m(165000)],
                                ['🟡 3. مؤسسة البنيان الحديثة', 65, '8 أيام (+1 يوم)', '#d97706', m(420000)],
                                ['🟡 4. مغلق الوادي للحديد', 72, '9 أيام (+2 يوم)', '#d97706', m(140000)],
                                ['🔴 5. مغلق السجانة الهندسي', 85, '10 أيام (+5 أيام)', '#e11d48', m(235000)],
                                ['🔴 6. شركة إعمار الهندسية', 88, '10 أيام (+4 أيام)', '#e11d48', m(290000)],
                                ['⬛ 7. ورشة الأمانة للحدادة', 98, '16 يوماً (+11 يوماً)', '#334155', m(195000)]
                            ]
                        },
                        sales_stock: {
                            title: '📊 تحليل المبيعات والأرباح والعائد على الاستثمار (ROI) وتقلبات سعر الصرف',
                            kpis: [['💎 متوسط العائد (ROI)', '+24.6% سنوياً', '#059669'], ['💱 أثر سعر الصرف', '+4.2% مكسب إحلال', '#0284c7'], ['🔄 دوران المخزون', '2.4 دورة/شهر', '#d97706'], ['🛡️ حماية الربح', '0 فاتورة خاسرة', '#059669']],
                            bars: [
                                ['1. سيخ 12مم السجانة (أعلى عائد استثمار ROI)', 94, '🟢 عائد ROI +28.4%', '#059669', m(77700000)],
                                ['2. سيخ 16مم تسليح (عائد ممتاز + دوران سريع)', 86, '🟢 عائد ROI +25.2%', '#059669', m(49800000)],
                                ['3. أسمنت بورتلاندي عطبرة (دوران 3.8x شهرياً)', 78, '🟢 عائد ROI +21.8%', '#0284c7', m(17575000)],
                                ['4. قطاعات ومواسير حديد (محمي بسعر الصرف)', 68, '🟡 عائد ROI +19.5%', '#d97706', m(32640000)],
                                ['5. سلك رباط ومسلتزمات بالكرتونة واللفة', 62, '🟡 عائد ROI +22.0%', '#d97706', m(11780000)]
                            ]
                        },
                        vaults: {
                            title: '📊 التوزيع البياني للخزائن والبنوك الـ 10 والشيكات برسم التحصيل',
                            kpis: [['💵 إجمالي الخزائن', m(1630000), '#059669'], ['🏦 إجمالي البنوك (7 بنوك)', m(2540000), '#0284c7'], ['🧾 شيكات برسم التحصيل', m(4250000), '#d97706'], ['🛡️ نسبة تغطية الشيكات', '11.8x ضعفاً', '#059669']],
                            bars: [
                                ['1. بنك الخرطوم (حساب بنكك الرئيسي)', 88, '🏦 متاح فوري', '#0284c7', m(1250000)],
                                ['2. الخزينة الرئيسية - الإدارة العامة', 82, '💵 نقدية جاهزة', '#059669', m(1100000)],
                                ['3. بنك فيصل الإسلامي (حساب الشركات)', 58, '🏦 متاح فوري', '#0284c7', m(480000)],
                                ['4. خزينة مبيعات المغلق الرئيسي', 48, '💵 نقدية نشطة', '#059669', m(350000)],
                                ['5. بنك أم درمان الوطني + النيلين + الزراعي + السلام', 64, '🏦 5 بنوك نشطة', '#d97706', m(810000)]
                            ]
                        },
                        forecast: {
                            title: '📊 الرسوم البيانية للتنبؤ الذكي ومخاطر التوريد والسيولة',
                            kpis: [['🔮 دقة التنبؤ الذكي', '94.8% (AI)', '#059669'], ['📦 طلب متوقع (7 أيام)', '185 طن حديد وأسمنت', '#0284c7'], ['⚠️ مخاطر نقص مخزون', 'صنفان (سيخ 12مم وسلك)', '#e11d48'], ['💹 توقع التدفق النقدي', '+18.5% فائض', '#d97706']],
                            bars: [
                                ['1. توقع الطلب على سيخ 4 لينية 12مم', 94, '🔥 طلب مرتفع جداً', '#e11d48', '110 طن بالأسبوع'],
                                ['2. توقع الطلب على أسمنت بورتلاندي عطبرة', 82, '🟢 نمو مستمر', '#059669', '95 طن بالأسبوع'],
                                ['3. توقع السيولة النقدية المحصلة', 78, '🟢 تدفق آمن', '#0284c7', m(4200000)],
                                ['4. مؤشر استقرار أسعار التوريد والإحلال', 66, '🟡 تذبذب معتدل', '#d97706', '+3.8% فرق إحلال']
                            ]
                        },
                        audit: {
                            title: '📊 الرسم البياني للمركز المالي وميزانية المطابقة والتدقيق',
                            kpis: [['⚖️ مطابقة الميزان', '100% متطابق ✓', '#059669'], ['🏛️ إجمالي الأصول', m(180870000), '#0284c7'], ['🛡️ صافي حقوق الملكية', m(180290000), '#059669'], ['🚨 فروقات معلقة', '0 ج.س (صفري)', '#d97706']],
                            bars: [
                                ['1. قيمة المخزون السلعي بالمستودعات', 92, '📦 أصل متداول', '#059669', m(176600000)],
                                ['2. النقدية بالخزائن والبنوك', 76, '🏦 سيولة جاهزة', '#0284c7', m(3450000)],
                                ['3. ذمم العملاء المدينين بالسوق', 54, '👥 ذمم مدينة', '#d97706', m(820000)],
                                ['4. التزامات الموردين الدائنين', 22, '🏛️ ذمم دائنة', '#e11d48', m(290000)]
                            ]
                        },
                        live_radar: {
                            title: '📊 الرسم البياني للحركات اليومية والأذونات المخزنية الفورية',
                            kpis: [['⚡ فواتير اليوم', '18 عملية نشطة', '#059669'], ['🚚 أذونات تسليم وصرف', '14 إذن مكتمل', '#0284c7'], ['📥 توريد واستلام مخزني', '4 شحنات واردة', '#d97706'], ['⏱️ سرعة الإنجاز', '1.8 دقيقة/عملية', '#059669']],
                            bars: [
                                ['1. فواتير مبيعات نقدية وبنكك', 90, '🟢 12 فاتورة', '#059669', m(2840000)],
                                ['2. أذونات صرف وتسليم مخزني للعملاء', 82, '🚚 14 إذن صرف', '#0284c7', '64.5 طن منصرف'],
                                ['3. سندات قبض وتحصيل مديونيات', 68, '💵 6 سندات قبض', '#059669', m(950000)],
                                ['4. أذونات استلام وتوريد بضاعة', 56, '📥 4 شحنات واردة', '#d97706', '120 طن وارد']
                            ]
                        },
                        cashflow: {
                            title: '📊 الرسم البياني لمؤشرات التدفق النقدي والمبيعات والمخزون',
                            kpis: [['📥 التدفق النقدي الداخل', m(3450000), '#059669'], ['📤 التدفق النقدي الخارج', m(1120000), '#e11d48'], ['💎 صافي الفائض النقدي', m(2330000), '#0284c7'], ['📈 نسبة السيولة السريعة', '3.08x ممتاز', '#059669']],
                            bars: [
                                ['1. متحصلات المبيعات النقدية وبنكك', 92, '🟢 تدفق داخل', '#059669', m(2500000)],
                                ['2. تحصيلات ذمم العملاء والشيكات', 74, '🟢 تدفق داخل', '#0284c7', m(950000)],
                                ['3. سداد دفعات الموردين والمصانع', 48, '🟡 تدفق خارج', '#d97706', m(890000)],
                                ['4. المصروفات التشغيلية والترحيل', 26, '🔴 تدفق خارج', '#e11d48', m(230000)]
                            ]
                        }
                    }[section] || {
                        title: '📊 المؤشرات والأعمدة البيانية الشاملة للسيولة والمبيعات والمخزون',
                        kpis: [['🏦 صافي السيولة', m(3450000), '#059669'], ['📦 قيمة المخزون', m(176600000), '#d97706'], ['👥 ذمم العملاء', m(820000), '#0284c7'], ['🏛️ التزامات الموردين', m(290000), '#e11d48']],
                        bars: [
                            ['🟢 صافي السيولة البنكية والنقدية', 88, '🟢 ▲ +12.4%', '#059669', m(3450000)],
                            ['🟡 قيمة المخزون السوقية', 76, '🟡 ▲ 420.5 طن', '#d97706', m(176600000)],
                            ['🔵 ذمم العملاء (لنا بالسوق)', 52, '🟢 ▲ تحصيل 84%', '#0284c7', m(820000)],
                            ['🔴 التزامات الموردين (علينا)', 24, '🟢 ▼ مغطى بالكامل', '#e11d48', m(290000)]
                        ]
                    };
                    const isLt = document.documentElement.classList.contains('light-theme') || document.body.classList.contains('light-theme');
                    const mBg = isLt ? 'linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%)' : 'var(--card-bg, linear-gradient(155deg, rgba(16, 26, 48, 0.98) 0%, rgba(8, 14, 28, 0.99) 100%))';
                    const hBg = isLt ? 'var(--lt-sub-2, #f6ecda)' : 'var(--topbar-bg, rgba(15, 23, 42, 0.95))';
                    const rBg = isLt ? 'var(--lt-sub-1, #fffefb)' : 'var(--card-inner, rgba(15,23,42,0.88))';
                    const bCol = isLt ? 'var(--lt-border, #5c2808)' : 'var(--border-color, #38bdf8)';
                    const tCol = isLt ? 'var(--lt-ink, #120500)' : '#ffffff';
                    const subCol = isLt ? '#78350f' : '#fde68a';
                    const defaultSectionChart = { vaults: 'area', cashflow: 'area', sales_stock: 'waterfall', items: 'gauge', live_radar: 'depth', forecast: 'heatmap', partners: 'heatmap', debt: 'donut', audit: 'waterfall' };
                    const chartType = orient || defaultSectionChart[section] || window._lastChartType || 'horizontal';
                    window._lastChartType = chartType;
                    const sparkPaths = ['M2,22 Q18,20 32,14 T64,10 T96,3', 'M2,20 Q22,12 40,16 T72,8 T96,4', 'M2,24 Q20,18 42,12 T70,9 T96,2', 'M2,18 Q24,22 46,11 T76,7 T96,4'];
                    const kpisHtml = cfg.kpis.map((k, idx) => `<div class="inner-box" style="background:linear-gradient(145deg,#0b1329,#070c1a)!important;border:1.5px solid ${k[2]}66;border-right:5px solid ${k[2]}!important;border-radius:14px;padding:10px 13px;position:relative;overflow:hidden;"><div style="display:flex;justify-content:space-between;align-items:flex-start;gap:6px;"><div><div style="font-size:11.5px;font-weight:800;color:#94a3b8;margin-bottom:3px;">${k[0]}</div><div style="font-size:15.5px;font-weight:900;color:#ffffff;">${k[1]}</div></div><svg width="68" height="26" viewBox="0 0 100 26" style="overflow:visible;flex-shrink:0;"><path d="${sparkPaths[idx % 4]}" fill="none" stroke="${k[2]}" stroke-width="2.8" stroke-linecap="round"/><circle cx="96" cy="3" r="3.5" fill="#fde047"/></svg></div></div>`).join('');
                    const renderUnifiedChart = () => {
                        const n = cfg.bars.length;
                        if (chartType === 'sparkline') {
                            return `<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map((b, i) => `<div style="background:linear-gradient(145deg,#0b132b 0%,#060b18 100%);border:1.5px solid ${b[3]}88;border-radius:16px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 8px 20px rgba(0,0,0,0.45);"><div><div style="font-size:12.5px;font-weight:800;color:#94a3b8;margin-bottom:4px;">${b[0]}</div><div style="font-size:20px;font-weight:900;color:#ffffff;">${b[4]}</div><div style="font-size:11.5px;font-weight:900;color:${b[3]};margin-top:3px;">${b[2]} (${b[1]}%)</div></div><svg width="120" height="44" viewBox="0 0 120 44"><defs><linearGradient id="spkG${i}" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="${b[3]}" stop-opacity="0.45"/><stop offset="100%" stop-color="${b[3]}" stop-opacity="0.0"/></linearGradient></defs><path d="M4,38 Q30,${36 - Math.round(b[1]*0.2)} 58,${30 - Math.round(b[1]*0.18)} T116,${40 - Math.round(b[1]*0.34)} L116,42 L4,42 Z" fill="url(#spkG${i})"/><path d="M4,38 Q30,${36 - Math.round(b[1]*0.2)} 58,${30 - Math.round(b[1]*0.18)} T116,${40 - Math.round(b[1]*0.34)}" fill="none" stroke="${b[3]}" stroke-width="3.2" stroke-linecap="round"/><circle cx="116" cy="${40 - Math.round(b[1]*0.34)}" r="4.5" fill="#fde047" stroke="#fff" stroke-width="1.5"/></svg></div>`).join('') + `</div>`;
                        }
                        if (chartType === 'gauge') {
                            return `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map(b => { const pct = Math.min(100, Math.max(5, Number(b[1]) || 50)); const dash = (pct * 1.88).toFixed(1); return `<div style="background:rgba(15,23,42,0.72);border:1.5px solid ${b[3]}77;border-radius:14px;padding:12px;display:flex;flex-direction:column;align-items:center;text-align:center;"><svg viewBox="0 0 160 95" style="width:155px;height:92px;overflow:visible;"><path d="M 20 80 A 60 60 0 0 1 140 80" fill="none" stroke="rgba(148,163,184,0.2)" stroke-width="14" stroke-linecap="round"/><path d="M 20 80 A 60 60 0 0 1 140 80" fill="none" stroke="${b[3]}" stroke-width="14" stroke-linecap="round" stroke-dasharray="${dash} 200"/><text x="80" y="66" fill="#ffffff" font-size="20" font-weight="900" text-anchor="middle">${pct}%</text><text x="80" y="84" fill="#fde047" font-size="10.5" font-weight="900" text-anchor="middle">${b[4]}</text></svg><div style="font-size:12.5px;font-weight:900;color:${tCol};margin-top:4px;">${b[0]}</div><span style="margin-top:4px;background:${b[3]};color:#fff!important;-webkit-text-fill-color:#fff!important;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:900;">${b[2]}</span></div>`; }).join('') + `</div>`;
                        }
                        if (chartType === 'depth') {
                            return `<div style="padding:16px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;display:flex;flex-direction:column;gap:10px;"><div style="display:flex;justify-content:space-between;font-size:12.5px;font-weight:900;padding:0 8px;"><span style="color:#34d399;">🟢 كفة الطلب / التدفق الداخل والقوة الشرائية</span><span style="color:#fbbf24;">⚖️ نقطة التوازن وعمق السوق</span><span style="color:#fb7185;">🔴 كفة العرض / الالتزامات والمنصرف</span></div>` + cfg.bars.map(b => { const buyPct = Math.min(92, Math.max(15, Number(b[1]) || 60)); const sellPct = 100 - buyPct; return `<div style="background:rgba(15,23,42,0.65);border:1px solid rgba(56,189,248,0.28);border-radius:12px;padding:10px 12px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:13.5px;font-weight:900;"><span style="color:${tCol};">${b[0]}</span><span style="color:#fde047;">${b[4]} (${b[2]})</span></div><div style="display:flex;height:18px;border-radius:999px;overflow:hidden;border:1px solid rgba(255,255,255,0.2);"><div style="width:${buyPct}%;background:linear-gradient(90deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10.5px;font-weight:900;">${buyPct}% طلب/إيجابي</div><div style="width:${sellPct}%;background:linear-gradient(90deg,#e11d48,#f97316);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10.5px;font-weight:900;">${sellPct}%</div></div></div>`; }).join('') + `</div>`;
                        }
                        if (chartType === 'heatmap') {
                            return `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map(b => { const intensity = Math.max(0.28, (Number(b[1]) || 50) / 100); return `<div style="background:linear-gradient(135deg,${b[3]}${Math.round(intensity*210).toString(16).padStart(2,'0')},rgba(15,23,42,0.92));border:2px solid ${b[3]};border-radius:14px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;gap:8px;box-shadow:0 6px 18px rgba(0,0,0,0.4);"><div style="display:flex;justify-content:space-between;align-items:center;"><span style="background:rgba(2,6,23,0.7);color:#fde047!important;-webkit-text-fill-color:#fde047!important;padding:2px 8px;border-radius:6px;font-size:11.5px;font-weight:900;">كثافة ${b[1]}%</span><span style="font-size:11.5px;font-weight:900;color:#fff;">${b[2]}</span></div><div style="font-size:14.5px;font-weight:900;color:#ffffff;line-height:1.3;">${b[0]}</div><div style="font-size:16px;font-weight:900;color:#fef08a;background:rgba(2,6,23,0.5);padding:4px 10px;border-radius:8px;align-self:flex-start;">${b[4]}</div></div>`; }).join('') + `</div>`;
                        }
                        if (chartType === 'vertical') {
                            return `<div style="display:grid;grid-template-columns:repeat(${n},minmax(0,1fr));gap:10px;align-items:end;min-height:250px;padding:16px 12px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map(b => `<div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:220px;gap:6px;"><span style="font-size:11px;font-weight:900;color:${subCol};text-align:center;">${b[4]}</span><div style="width:100%;max-width:46px;height:${Math.max(18, b[1]*1.5)}px;background:${b[3]};border-radius:10px 10px 4px 4px;box-shadow:0 4px 12px rgba(0,0,0,0.25);display:flex;align-items:flex-start;justify-content:center;padding-top:4px;"><span style="color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:11px;font-weight:900;">${b[1]}%</span></div><span style="font-size:12px;font-weight:900;color:${tCol};text-align:center;line-height:1.25;">${b[0]}</span></div>`).join('') + `</div>`;
                        }
                        if (chartType === 'area' || chartType === 'line') {
                            const w = 760, h = 230, padX = 55, padY = 28;
                            const pts = cfg.bars.map((b, i) => [padX + (i * (w - padX * 2) / Math.max(1, n - 1)), h - padY - ((b[1] / 100) * (h - padY * 2)), b]);
                            const linePath = pts.map((p, i) => (i === 0 ? `M ${p[0]},${p[1]}` : `L ${p[0]},${p[1]}`)).join(' ');
                            const areaPath = `${linePath} L ${pts[pts.length - 1][0]},${h - padY} L ${pts[0][0]},${h - padY} Z`;
                            return `<div style="padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;"><svg viewBox="0 0 ${w} ${h + 32}" style="width:100%;height:auto;overflow:visible;"><defs><linearGradient id="uAreaGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity="0.68"/><stop offset="60%" stop-color="#0284c7" stop-opacity="0.32"/><stop offset="100%" stop-color="#0284c7" stop-opacity="0.03"/></linearGradient></defs>${[25, 50, 75, 100].map(v => { const y = h - padY - ((v / 100) * (h - padY * 2)); return `<line x1="${padX}" y1="${y}" x2="${w - padX}" y2="${y}" stroke="rgba(148,163,184,0.22)" stroke-dasharray="4,4"/><text x="${padX - 10}" y="${y + 4}" fill="${subCol}" font-size="11" font-weight="900" text-anchor="end">${v}%</text>`; }).join('')}${chartType === 'area' ? `<path d="${areaPath}" fill="url(#uAreaGrad)"/>` : ''}<path d="${linePath}" fill="none" stroke="#38bdf8" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>${pts.map(p => `<circle cx="${p[0]}" cy="${p[1]}" r="6.5" fill="${p[2][3]}" stroke="#fff" stroke-width="2.2"/><text x="${p[0]}" y="${p[1] - 12}" fill="${tCol}" font-size="11.5" font-weight="900" text-anchor="middle">${p[2][1]}% (${p[2][4]})</text><text x="${p[0]}" y="${h + 12}" fill="${subCol}" font-size="11" font-weight="900" text-anchor="middle">${p[2][0].slice(0, 18)}</text>`).join('')}</svg></div>`;
                        }
                        if (chartType === 'donut') {
                            const tot = cfg.bars.reduce((s, b) => s + Number(b[1] || 10), 0) || 100;
                            let acc = 0;
                            const segs = cfg.bars.map(b => { const pct = (b[1] / tot) * 100; const s = `<circle cx="110" cy="110" r="74" fill="none" stroke="${b[3]}" stroke-width="32" stroke-dasharray="${(pct * 4.65).toFixed(1)} 465" stroke-dashoffset="-${(acc * 4.65).toFixed(1)}"/>`; acc += pct; return s; }).join('');
                            return `<div style="display:grid;grid-template-columns:230px 1fr;gap:16px;align-items:center;padding:16px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;"><svg viewBox="0 0 220 220" style="width:210px;height:210px;transform:rotate(-90deg);margin:auto;">${segs}<circle cx="110" cy="110" r="52" fill="${isLt ? '#fff' : '#0f172a'}"/></svg><div style="display:flex;flex-direction:column;gap:8px;">${cfg.bars.map(b => `<div style="display:flex;align-items:center;justify-content:space-between;padding:7px 12px;border-radius:10px;background:rgba(15,23,42,0.45);border-right:5px solid ${b[3]};"><span style="font-weight:900;color:${tCol};font-size:13.5px;">${b[0]}</span><strong style="color:${subCol};font-size:13.5px;">${b[4]} (${b[1]}%)</strong></div>`).join('')}</div></div>`;
                        }
                        if (chartType === 'radar') {
                            const cx = 150, cy = 135, rMax = 95;
                            const polyPts = cfg.bars.map((b, i) => { const a = (Math.PI * 2 * i / n) - Math.PI / 2; const r = (b[1] / 100) * rMax; return `${(cx + r * Math.cos(a)).toFixed(1)},${(cy + r * Math.sin(a)).toFixed(1)}`; }).join(' ');
                            const axes = cfg.bars.map((b, i) => { const a = (Math.PI * 2 * i / n) - Math.PI / 2; const x = cx + rMax * Math.cos(a), y = cy + rMax * Math.sin(a); const lx = cx + (rMax + 26) * Math.cos(a), ly = cy + (rMax + 18) * Math.sin(a); return `<line x1="${cx}" y1="${cy}" x2="${x}" y2="${y}" stroke="rgba(148,163,184,0.3)"/><text x="${lx}" y="${ly}" fill="${tCol}" font-size="10.5" font-weight="900" text-anchor="middle">${b[0].slice(0, 15)} (${b[1]}%)</text>`; }).join('');
                            return `<div style="display:flex;justify-content:center;padding:16px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;"><svg viewBox="0 0 300 270" style="width:100%;max-width:440px;height:auto;">${[0.33, 0.66, 1].map(f => `<circle cx="${cx}" cy="${cy}" r="${rMax * f}" fill="none" stroke="rgba(56,189,248,0.22)" stroke-dasharray="3,3"/>`).join('')}${axes}<polygon points="${polyPts}" fill="rgba(16,185,129,0.35)" stroke="#38bdf8" stroke-width="3"/></svg></div>`;
                        }
                        if (chartType === 'waterfall') {
                            let curBottom = 10;
                            return `<div style="display:grid;grid-template-columns:repeat(${n},minmax(0,1fr));gap:10px;align-items:end;min-height:250px;padding:16px 12px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map((b, i) => { const hBar = Math.max(22, Math.round(b[1] * 0.9)); const offset = i === 0 ? 0 : Math.min(110, curBottom); curBottom = (curBottom + 24) % 95; return `<div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:225px;"><span style="font-size:11px;font-weight:900;color:${subCol};margin-bottom:4px;">${b[4]}</span><div style="width:100%;max-width:48px;height:${hBar}px;margin-bottom:${offset}px;background:linear-gradient(180deg,${b[3]},rgba(15,23,42,0.85));border:1.5px solid #fff;border-radius:8px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:11px;font-weight:900;">${b[1]}%</span></div><span style="font-size:11.5px;font-weight:900;color:${tCol};text-align:center;">${b[0].slice(0, 18)}</span></div>`; }).join('') + `</div>`;
                        }
                        return cfg.bars.map(b => `<div class="inner-box" style="background:${rBg}!important;border:1.5px solid ${bCol};border-radius:12px;padding:10px 14px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:15px;font-weight:900;"><span style="color:${tCol};">${b[0]}</span><div style="display:flex;align-items:center;gap:8px;"><strong style="color:${subCol};font-size:14px;font-weight:900;">${b[4]}</strong><span style="background:${b[3]};padding:2px 10px;border-radius:8px;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:12.5px;font-weight:900;">${b[2]}</span></div></div><div style="width:100%;height:13px;background:${isLt ? '#cbd5e1' : '#1e293b'};border-radius:999px;overflow:hidden;"><div style="width:${b[1]}%;height:100%;background:${b[3]};border-radius:999px;"></div></div></div>`).join('');
                    };
                    const chartTypesBar = [
                        ['sparkline', '⚡ 1. المؤشرات النبضية (Sparkline)'],
                        ['area', '📈 2. المساحة الانسيابية (Area Flow)'],
                        ['donut', '🍩 3. الدونات المجوفة (Donut)'],
                        ['gauge', '🧭 4. العداد المقوس (Arc Gauge)'],
                        ['depth', '⚖️ 5. ميزان العرض والطلب (Depth)'],
                        ['waterfall', '🌊 6. الشلال المالي (Waterfall)'],
                        ['heatmap', '🔥 7. الخريطة الحرارية (Heatmap)'],
                        ['horizontal', '📊 الأعمدة المقارنة']
                    ].map(ct => `<button type="button" onclick="openChartCardModal('${section}', '${ct[0]}', '${sMode}')" style="background:${chartType===ct[0]?'linear-gradient(135deg,#059669,#0284c7)':'#1e293b'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid ${chartType===ct[0]?'#fde047':'#38bdf8'};border-radius:8px;padding:4px 9px;font-size:11.5px;font-weight:900;cursor:pointer;">${ct[1]}</button>`).join('');
                    modal.innerHTML = `<div class="bento-card" style="background:${mBg}!important;border:2.5px solid ${bCol}!important;color:${tCol}!important;border-radius:18px;width:100%;max-width:920px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 60px rgba(0,0,0,0.65);overflow:hidden;">
                        <div style="padding:12px 16px;background:${hBg}!important;border-bottom:1.5px solid ${bCol};display:flex;flex-direction:column;gap:8px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                                <strong style="color:${tCol}!important;font-size:16px;font-weight:900;">${cfg.title}</strong>
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    ${section === 'items' ? `
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'stock_need')" style="background:${sMode==='stock_need'?'#e11d48':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">🚨 الحوجة للطلب</button>
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'stock_most')" style="background:${sMode==='stock_most'?'#059669':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">📦 الأكثر بالمخزن</button>
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'stock_least')" style="background:${sMode==='stock_least'?'#0284c7':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">📉 الأقل بالمخزن</button>
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'sales_profit')" style="background:${sMode==='sales_profit'?'#d97706':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">🏆 المبيعات والربح</button>
                                    ` : ''}
                                    <button type="button" onclick="document.getElementById('partner-items-modal').style.display='none'" style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:5px 14px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">${chartTypesBar}</div>
                        </div>
                        <div style="padding:16px 18px;overflow-y:auto;display:flex;flex-direction:column;gap:14px;">
                            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">${kpisHtml}</div>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                ${renderUnifiedChart()}
                            </div>
                        </div>
                    </div>`;
                    modal.style.display = 'flex';
                }

                function submitInstantInvoice() {
                    const client = document.getElementById('op-client').value || 'عميل نقدي';
                    const type = document.getElementById('op-type').value;
                    const tons = parseFloat(document.getElementById('sajana-tons').value) || 0;
                    const rebar = parseFloat(document.getElementById('cur-rebar').value) || 420000;
                    const amount = Math.round(tons * rebar);
                    const bars = Math.round(tons * 135);
                    const bundles = ((tons * 135) / 15).toFixed(1);

                    const feed = document.getElementById('radar-feed');
                    const row = document.createElement('div');
                    row.className = "inner-box p-3 border border-emerald-400/60 cursor-pointer transition flex items-center justify-between";
                    row.onclick = () => printReceipt(client, type, tons, bars, bundles, amount.toLocaleString());
                    row.innerHTML = `
                        <div>
                            <div class="font-bold text-white text-xs">${client} <span class="text-emerald-400">(${type})</span></div>
                            <div class="text-[11px] text-slate-300">الوزن: ${tons} طن | ${bars} سيخة تجاري | ${bundles} شيلة</div>
                        </div>
                        <div class="text-left">
                            <div class="font-black text-emerald-400 text-sm">${amount.toLocaleString()} ج.س</div>
                            <div class="text-[10px] text-emerald-300">تم الترحيل الآن ✓</div>
                        </div>
                    `;
                    feed.prepend(row);

                    const curLiqInput = document.getElementById('cur-liq');
                    const curTonsInput = document.getElementById('cur-tons');
                    if (type === 'فاتورة بيع') {
                        curLiqInput.value = (parseFloat(curLiqInput.value) || 0) + amount;
                        curTonsInput.value = Math.max(0, (parseFloat(curTonsInput.value) || 0) - tons).toFixed(1);
                    } else if (type === 'سند مشتريات') {
                        curLiqInput.value = Math.max(0, (parseFloat(curLiqInput.value) || 0) - amount);
                        curTonsInput.value = ((parseFloat(curTonsInput.value) || 0) + tons).toFixed(1);
                    } else {
                        curLiqInput.value = (parseFloat(curLiqInput.value) || 0) + amount;
                    }
                    updateFromCurtain();
                }

                function printReceipt(client, type, tons, bars, bundles, total) {
                    document.getElementById('prt-client').innerText = client;
                    document.getElementById('prt-type').innerText = type;
                    document.getElementById('prt-tons').innerText = tons + ' طن';
                    document.getElementById('prt-bars').innerText = bars + ' سيخة';
                    document.getElementById('prt-bundles').innerText = bundles + ' شيلة';
                    document.getElementById('prt-total').innerText = total + ' ج.س';
                    const modal = document.getElementById('print-modal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closePrintModal() {
                    const modal = document.getElementById('print-modal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                function resolveTask(btn) {
                    btn.parentElement.remove();
                    const remaining = document.getElementById('alerts-list').children.length;
                    document.getElementById('pending-badge').innerText = remaining > 0 ? `${remaining} قيد المتابعة` : 'مكتمل 100% ✓';
                }

                function setScope(scope, btn) {
                    window._currentDashScope = scope;
                    document.querySelectorAll('.scope-pill').forEach(el => el.classList.remove('active'));
                    btn.classList.add('active');
                    const labels = { today: 'نطاق: اليوم', week: 'نطاق: الأسبوع', month: 'نطاق: الشهر', all: 'نطاق: الكل' };
                    document.getElementById('scope-badge').innerText = labels[scope];
                    updateFromCurtain();
                }

                function filterView(mode, btn) {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const sCalm = document.getElementById('sec-calm');
                    const sAi = document.getElementById('sec-global-ai-innovations');
                    const sFlow = document.getElementById('sec-flow');
                    const sOps = document.getElementById('sec-ops-audit');
                    sCalm.style.display = 'grid';
                    if (sAi) sAi.style.display = 'grid';
                    sFlow.style.display = 'grid';
                    sOps.style.display = 'grid';
                    if (mode === 'calm') { sFlow.style.display = 'none'; sOps.style.display = 'none'; }
                    if (mode === 'flow') { sCalm.style.display = 'none'; if (sAi) sAi.style.display = 'none'; sOps.style.display = 'none'; }
                    if (mode === 'ops' || mode === 'audit') { sCalm.style.display = 'none'; if (sAi) sAi.style.display = 'none'; sFlow.style.display = 'none'; }
                }

                function resetDemoRecords() {
                    localStorage.removeItem('magaiees_state_v1');
                    document.getElementById('cur-liq').value = 0;
                    document.getElementById('cur-ar').value = 0;
                    document.getElementById('cur-ap').value = 0;
                    document.getElementById('cur-tons').value = 0;
                    updateFromCurtain();
                    document.getElementById('radar-feed').innerHTML = '<div class="inner-box p-4 text-center text-xs text-slate-300">تم تصفير سجل الفواتير التجريبية والبدء بسجل نظيف ✓</div>';
                }
