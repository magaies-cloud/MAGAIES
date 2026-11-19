    (function() {
        window._grnFinStripInUsd = false;
        window._grnHubInUsd = false;
        window._grnCurrentSupplierId = null;

        window.getGrnActiveExchangeRate = function() {
            const saved = parseFloat(localStorage.getItem('magaies_usd_exchange_rate') || localStorage.getItem('usd_exchange_rate') || '2650');
            return (saved && saved > 1) ? saved : 2650;
        };

        // 3-Level Role Switcher (1. المدير, 2. مدير المخزن, 3. المخزنجي QR) — Dark Buttons Always Keep Crisp Light Text
        window.setGrnReceiptLevel = function(lvl) {
            const activeLvl = Number(lvl) || 1;
            window._grnActiveReceiptLevel = activeLvl;
            try { localStorage.setItem('magaies_grn_receipt_level', String(activeLvl)); } catch (_e) {}
            [1, 2, 3].forEach(n => {
                const btn = document.getElementById('grn-lvl-' + n);
                if (!btn) return;
                const isAct = (n === activeLvl);
                btn.classList.toggle('active-lvl', isAct);
                btn.style.background = isAct
                    ? 'linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%)'
                    : 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)';
                btn.style.border = isAct ? '1.5px solid #c4b5fd' : '1.5px solid #475569';
                btn.style.color = '#ffffff';
                btn.style.webkitTextFillColor = '#ffffff';
                btn.style.boxShadow = isAct ? '0 0 12px rgba(124, 58, 237, 0.55)' : 'none';
            });
            const names = { 1: '👑 المستوى 1: المدير (صلاحية كاملة)', 2: '🏢 المستوى 2: مدير المخزن (فحص واعتماد مخزني)', 3: '📦 المستوى 3: المخزنجي (استلام وفحص بالباركود QR)' };
            if (typeof showToast === 'function') showToast(names[activeLvl] || names[1], 'info');
        };

        // Mark 1-Step Return to purchase-receipt.php when navigating to sibling screens
        window.markGrnReturnStep = function() {
            try {
                sessionStorage.setItem('magaies_grn_return_url', window.location.href);
                sessionStorage.setItem('magaies_grn_return_ts', String(Date.now()));
            } catch (_e) {}
        };

        // 🔊 Kinetic, Audio & Visual Gesture Engine (فحص واستلام / استلام فوري / ماسح متصل / ميزان القبّان / رجوع)
        window.triggerGrnAudioChime = function(mode) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                if (!window._grnAudioCtx) window._grnAudioCtx = new AudioCtx();
                const ctx = window._grnAudioCtx;
                if (ctx.state === 'suspended') ctx.resume();
                const now = ctx.currentTime;
                const notes = mode === 'instant' ? [587.33, 880, 1174.66]
                    : mode === 'inspect' ? [523.25, 659.25, 783.99]
                    : mode === 'scan' ? [783.99, 1046.5]
                    : mode === 'weigh' ? [440, 554.37, 659.25]
                    : mode === 'back' ? [587.33, 440]
                    : [659.25, 880];
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = mode === 'weigh' ? 'triangle' : 'sine';
                    osc.frequency.setValueAtTime(freq, now + idx * 0.055);
                    gain.gain.setValueAtTime(0.09, now + idx * 0.055);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.055 + 0.14);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now + idx * 0.055);
                    osc.stop(now + idx * 0.055 + 0.15);
                });
                if (navigator.vibrate) navigator.vibrate(18);
            } catch (_ea) {}
        };

        window.triggerGrnKineticFeedback = function(btn, mode) {
            if (!btn) return;
            window.triggerGrnAudioChime(mode || 'action');
            btn.classList.remove('grn-kinetic-active-pop');
            void btn.offsetWidth;
            btn.classList.add('grn-kinetic-active-pop');
            const wave = document.createElement('span');
            wave.className = 'grn-kinetic-ripple-wave';
            wave.style.left = 'calc(50% - 18px)';
            wave.style.top = 'calc(50% - 18px)';
            btn.appendChild(wave);
            setTimeout(() => { wave.remove(); btn.classList.remove('grn-kinetic-active-pop'); }, 540);
        };

        document.addEventListener('click', function(ev) {
            const btn = ev.target && ev.target.closest ? ev.target.closest('#btn-grn-continuous-scan, #btn-grn-weighbridge, #btn-grn-editor-lighting, #btn-grn-fill-all, #btn-grn-zero-all, .grn-btn-save-primary, .grn-nav-pill, #view-editor .grn-scanner-bar button, #view-editor .neo-card-title button') : null;
            if (!btn) return;
            const id = btn.id || '';
            const mode = id === 'btn-grn-continuous-scan' ? 'scan'
                : id === 'btn-grn-weighbridge' ? 'weigh'
                : (btn.classList.contains('grn-btn-save-primary') || id === 'btn-grn-fill-all') ? 'instant'
                : (btn.getAttribute('onclick') || '').indexOf('showHub') !== -1 ? 'back'
                : 'action';
            window.triggerGrnKineticFeedback(btn, mode);
        }, true);

        // 🛠️ Organize & Group Cluttered Editor Buttons into Flagship Bar + Collapsible Advanced Drawer
        window.organizeGrnButtonsAndToolbars = function() {
            const scanBar = document.getElementById('grn-scanner-bar');
            if (!scanBar) return;
            let drawer = document.getElementById('grn-advanced-tools-drawer');
            if (!drawer) {
                drawer = document.createElement('div');
                drawer.id = 'grn-advanced-tools-drawer';
                scanBar.appendChild(drawer);
            }
            const secondaryIds = [
                'btn-grn-qr-card', 'btn-grn-signature', 'btn-grn-landed-cost', 'btn-grn-supplier-claim',
                'btn-grn-batch-labels', 'btn-grn-voice-cmd', 'btn-grn-mastery-quiz', 'btn-grn-expiry-radar',
                'btn-grn-ocr-matcher', 'btn-grn-wh-heatmap', 'btn-grn-undo', 'btn-grn-park-receipt',
                'btn-grn-c9-qc-sort-modal', 'btn-grn-c9-wh-transfer', 'btn-grn-c10-lifecycle',
                'btn-grn-c10-return', 'btn-grn-c11-cheques', 'btn-grn-c11-governance',
                'btn-grn-c9-staged-history', 'btn-grn-c9-stock-radar',
                'btn-grn-c4-impact-pill', 'btn-grn-c5-role-pill', 'btn-grn-c6-sync-pill', 'btn-grn-c7-eye-pill'
            ];
            let movedCount = 0;
            secondaryIds.forEach(id => {
                const el = document.getElementById(id);
                if (el && el.parentElement !== drawer) {
                    drawer.appendChild(el);
                }
                if (el) movedCount++;
            });
            const toolRow = scanBar.children[3];
            if (toolRow) {
                let toggleBtn = document.getElementById('btn-grn-toggle-adv-drawer');
                if (!toggleBtn) {
                    toggleBtn = document.createElement('button');
                    toggleBtn.type = 'button';
                    toggleBtn.id = 'btn-grn-toggle-adv-drawer';
                    toggleBtn.className = 'btn-action grn-light-clear-btn';
                    toggleBtn.style.cssText = 'padding:9px 14px;font-size:0.86rem;border-radius:11px;background:linear-gradient(135deg,#4f46e5,#3730a3);border:2px solid #a5b4fc;color:#fff;font-weight:900;display:inline-flex;align-items:center;gap:6px;cursor:pointer;';
                    toggleBtn.innerHTML = `<span>🛠️</span><span>أدوات متقدمة (${movedCount || 16}) ▾</span>`;
                    toggleBtn.onclick = function() {
                        const isOpen = drawer.classList.toggle('open');
                        ['grn-c9-staged-banner', 'grn-c10-commercial-bar'].forEach(bid => {
                            const b = document.getElementById(bid);
                            if (b) b.classList.toggle('adv-visible', isOpen);
                        });
                        this.querySelector('span:last-child').textContent = isOpen ? 'إخفاء الأدوات ▲' : `أدوات متقدمة (${drawer.children.length}) ▾`;
                    };
                    toolRow.appendChild(toggleBtn);
                } else if (!drawer.classList.contains('open')) {
                    const sp = toggleBtn.querySelector('span:last-child');
                    if (sp) sp.textContent = `أدوات متقدمة (${drawer.children.length}) ▾`;
                }
            }
        };
        setTimeout(window.organizeGrnButtonsAndToolbars, 450);
        setTimeout(window.organizeGrnButtonsAndToolbars, 950);

        // ↩️ Unified 1-Step Back Navigation Inside purchase-receipt.php (Modal -> Editor -> Hub Pending -> Exit)
        window.handleGrnOneStepBack = function(ev) {
            const openModals = Array.from(document.querySelectorAll('.modal-overlay, [id$="-modal"], .grn-child-modal-backdrop')).filter(m => {
                if (m.id === 'master7ThemesModal') return false;
                const st = window.getComputedStyle(m);
                return st.display !== 'none' && st.visibility !== 'hidden' && st.opacity !== '0';
            });
            if (openModals.length > 0) {
                if (ev) { ev.preventDefault(); ev.stopPropagation(); if (ev.stopImmediatePropagation) ev.stopImmediatePropagation(); }
                const topModal = openModals[openModals.length - 1];
                topModal.style.display = 'none';
                window.triggerGrnAudioChime('back');
                return true;
            }
            const ed = document.getElementById('view-editor');
            if (ed && ed.classList.contains('active') && typeof window.showHub === 'function') {
                if (ev) { ev.preventDefault(); ev.stopPropagation(); if (ev.stopImmediatePropagation) ev.stopImmediatePropagation(); }
                window.triggerGrnAudioChime('back');
                window.showHub(true);
                if (typeof showToast === 'function') showToast('↩️ تم الرجوع خطوة واحدة إلى الشاشة الرئيسية لاستلام البضائع', 'info');
                return true;
            }
            if (typeof currentHubView !== 'undefined' && currentHubView === 'received' && typeof window.toggleHubViewBySwitch === 'function') {
                if (ev) { ev.preventDefault(); ev.stopPropagation(); if (ev.stopImmediatePropagation) ev.stopImmediatePropagation(); }
                const sw = document.getElementById('uiverse-toggle');
                if (sw) sw.checked = false;
                window.triggerGrnAudioChime('back');
                window.toggleHubViewBySwitch(false);
                if (typeof showToast === 'function') showToast('↩️ تم الرجوع خطوة واحدة إلى قائمة قيد الاستلام', 'info');
                return true;
            }
            return false;
        };

        const _origOpenReceiptEditorStep = window.openReceiptEditor;
        if (typeof _origOpenReceiptEditorStep === 'function' && !_origOpenReceiptEditorStep._stepWrapped) {
            const stepWrapped = function(invId) {
                try {
                    if (!window._isQuickHubReceiving && (!history.state || history.state.grnView !== 'editor')) {
                        history.pushState({ grnView: 'editor', invId: invId }, '', window.location.href);
                    }
                } catch (_eh) {}
                const res = _origOpenReceiptEditorStep.apply(this, arguments);
                setTimeout(window.organizeGrnButtonsAndToolbars, 60);
                return res;
            };
            stepWrapped._stepWrapped = true;
            window.openReceiptEditor = stepWrapped;
        }

        const _origMagaieesGoBack = window.magaieesGoBack;
        window.magaieesGoBack = function( fallbackUrl ) {
            if (window.handleGrnOneStepBack()) return false;
            if (typeof _origMagaieesGoBack === 'function') return _origMagaieesGoBack(fallbackUrl);
            window.location.href = fallbackUrl || 'index.php';
        };

        document.addEventListener('click', function(ev) {
            const backBtn = ev.target && ev.target.closest ? ev.target.closest('#magaiees-master-back-btn, .magaiees-back-btn') : null;
            if (backBtn) {
                if (window.handleGrnOneStepBack(ev)) return false;
            }
        }, true);

        window.addEventListener('popstate', function(ev) {
            const ed = document.getElementById('view-editor');
            if (ed && ed.classList.contains('active') && typeof window.showHub === 'function') {
                window.triggerGrnAudioChime('back');
                window.showHub();
            }
        });

        document.addEventListener('keydown', function(ev) {
            if (ev.key === 'Escape' && !ev.defaultPrevented) {
                window.handleGrnOneStepBack(ev);
            }
        });

        function ensureConstitutionStyleLast() {
            ['grn-c15-master-constitution-styles', 'grn-c16-zero-error-styles'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.remove();
            });
            const savedTone = (document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/)||[])[1] || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || '';
            if (savedTone) {
                const decTone = decodeURIComponent(savedTone);
                if (document.documentElement.getAttribute('data-light-tone') !== decTone) {
                    document.documentElement.setAttribute('data-light-tone', decTone);
                }
                if (document.body && document.body.getAttribute('data-light-tone') !== decTone) {
                    document.body.setAttribute('data-light-tone', decTone);
                }
            }
            const st = document.getElementById('grn-purchase-constitution-2027-style');
            const targetParent = document.body || document.head;
            if (st && targetParent && targetParent.lastElementChild !== st) {
                targetParent.appendChild(st);
            }
        }
        ensureConstitutionStyleLast();
        setTimeout(ensureConstitutionStyleLast, 720);
        window.addEventListener('themeChanged', function() {
            ensureConstitutionStyleLast();
        });
        if (typeof BroadcastChannel !== 'undefined') {
            try {
                const bcTone = new BroadcastChannel('elsajana_sovereign_10x_viewport_v1');
                bcTone.addEventListener('message', function(ev) {
                    if (ev && ev.data && ev.data.type === 'TONE_SYNC' && ev.data.toneId) {
                        if (typeof window.applyGlobalErpToneOrTheme === 'function') {
                            window.applyGlobalErpToneOrTheme(ev.data.toneId, false);
                        }
                        ensureConstitutionStyleLast();
                    }
                });
            } catch (_e) {}
        }

        // Hub Financial Strip Updater + Currency Toggle (#view-hub)
        window.updateGrnHubFinStrip = function(filteredIds) {
            try {
                const ids = Array.isArray(filteredIds) ? filteredIds : (typeof pendingMap !== 'undefined' ? Object.keys(pendingMap) : []);
                let totalPendingValSdg = 0;
                ids.forEach(id => {
                    const invObj = (typeof pendingMap !== 'undefined' && pendingMap[id]) ? pendingMap[id] : null;
                    if (invObj && invObj.lines) {
                        Object.values(invObj.lines).forEach(l => {
                            totalPendingValSdg += (parseFloat(l.pendingQty) || 0) * (parseFloat(l.price) || 0);
                        });
                    }
                });
                const rcvCount = (typeof grnsDb !== 'undefined' && Array.isArray(grnsDb)) ? grnsDb.length : 0;
                const rate = window.getGrnActiveExchangeRate();
                const usdVal = totalPendingValSdg / rate;

                const elP = document.getElementById('grn-hub-stat-pending');
                const elR = document.getElementById('grn-hub-stat-received');
                const elSdg = document.getElementById('grn-hub-stat-val-sdg');
                const elUsd = document.getElementById('grn-hub-stat-val-usd');
                const elLbl = document.getElementById('grn-hub-curr-lbl');

                if (elP) elP.textContent = ids.length;
                if (elR) elR.textContent = rcvCount;
                if (elSdg) elSdg.textContent = Math.round(totalPendingValSdg).toLocaleString('en-US') + ' ج.س';
                if (elUsd) elUsd.textContent = '$' + usdVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (elLbl) elLbl.textContent = window._grnHubInUsd ? 'دولار ⇄' : 'جنيه ⇄';
            } catch (e) {}
        };

        window.toggleGrnHubCurrency = function() {
            window._grnHubInUsd = !window._grnHubInUsd;
            if (typeof renderPendingInvoices === 'function') renderPendingInvoices(false);
        };

        // 1. Price Radar 3D Lamp + Red/Green Arrow Builder for Receipt Lines
        window.buildGrnPriceRadarBadgeHtml = function(line) {
            try {
                const curPrice = parseFloat(line.price) || 0;
                let refPrice = curPrice;
                let stockBal = 0;
                if (typeof itemsDb !== 'undefined' && Array.isArray(itemsDb)) {
                    const it = itemsDb.find(i => String(i.id) === String(line.itemId) || i.name === line.itemId);
                    if (it) {
                        refPrice = parseFloat(it.costPrice || it.buyPrice || it.lastBuyPrice || it.price || curPrice) || curPrice;
                        stockBal = parseFloat(it.stock || it.qty || it.balance || 0) || 0;
                    }
                }
                if (curPrice <= 0 && refPrice <= 0) {
                    return `<span class="pur-price-lamp lamp-blue" title="رصيد الصنف: ${stockBal}"></span>`;
                }
                const diff = curPrice - refPrice;
                const pct = refPrice > 0 ? Math.round((Math.abs(diff) / refPrice) * 100) : 0;
                let lampCls = 'lamp-green';
                let subCls = 'var-same';
                let arrowHtml = `● ${curPrice.toLocaleString('en-US')}`;
                if (diff > 0.01) {
                    lampCls = pct >= 15 ? 'lamp-red' : 'lamp-amber';
                    subCls = 'var-up';
                    arrowHtml = `▲ +${pct}% (${curPrice.toLocaleString('en-US')})`;
                } else if (diff < -0.01) {
                    lampCls = 'lamp-green';
                    subCls = 'var-down';
                    arrowHtml = `▼ -${pct}% (${curPrice.toLocaleString('en-US')})`;
                }
                return `<span style="display:inline-flex; align-items:center; gap:5px; flex-shrink:0;" onclick="window.openGrnAIAuditModal && window.openGrnAIAuditModal()">
                    <span class="pur-price-subtxt ${subCls}" title="سعر الفاتورة: ${curPrice.toLocaleString('en-US')} | آخر تكلفة مرجعية: ${refPrice.toLocaleString('en-US')} | الرصيد: ${stockBal}">${arrowHtml}</span>
                    <span class="pur-price-lamp ${lampCls}" title="رادار السعر — انقر لفتح تحليل الذكاء الاصطناعي للأسعار"></span>
                </span>`;
            } catch (e) {
                return '';
            }
        };

        // 2. Supplier Financial Strip Live Updater + SDG/USD Toggle
        window.updateGrnSupplierFinStrip = function(supplierId) {
            if (supplierId !== undefined && supplierId !== null) {
                window._grnCurrentSupplierId = supplierId;
            }
            const supId = window._grnCurrentSupplierId;
            let balance = 0, debt = 0, cheques = 0, limit = 0;
            if (typeof suppliersDb !== 'undefined' && Array.isArray(suppliersDb) && supId) {
                const s = suppliersDb.find(x => String(x.id) === String(supId) || x.name === supId);
                if (s) {
                    balance = parseFloat(s.balance ?? s.currentBalance ?? 0) || 0;
                    debt = parseFloat(s.debt ?? Math.max(0, balance)) || 0;
                    cheques = parseFloat(s.cheques ?? s.pendingCheques ?? 0) || 0;
                    limit = parseFloat(s.creditLimit ?? s.limit ?? 0) || 0;
                }
            }
            const rate = window.getGrnActiveExchangeRate();
            const isUsd = !!window._grnFinStripInUsd;
            const conv = (v) => isUsd ? ('$' + (v / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : (Math.round(v).toLocaleString('en-US') + ' ج.س');

            const elBal = document.getElementById('grn-fin-balance');
            const elDebt = document.getElementById('grn-fin-debt');
            const elChq = document.getElementById('grn-fin-cheques');
            const elLim = document.getElementById('grn-fin-limit');
            const elAvl = document.getElementById('grn-fin-avail');
            const elBtn = document.getElementById('grn-fin-curr-lbl');

            if (elBal) elBal.textContent = conv(balance);
            if (elDebt) elDebt.textContent = conv(debt);
            if (elChq) elChq.textContent = conv(cheques);
            if (elLim) elLim.textContent = limit > 0 ? conv(limit) : 'مفتوح';
            if (elAvl) elAvl.textContent = limit > 0 ? conv(Math.max(0, limit - debt - cheques)) : 'مفتوح';
            if (elBtn) elBtn.textContent = isUsd ? 'دولار ⇄' : 'جنيه ⇄';
        };

        window.toggleGrnFinStripCurrency = function() {
            window._grnFinStripInUsd = !window._grnFinStripInUsd;
            window.updateGrnSupplierFinStrip();
        };

        window.openGrnSupplierLedger = function() {
            const supId = window._grnCurrentSupplierId || '';
            const url = supId ? ('suppliers.php?ledger=' + encodeURIComponent(supId)) : 'suppliers.php';
            window.open(url, '_blank');
        };

        // 3. AI Price Audit Modal (Works in both Hub & Editor views)
        window.openGrnAIAuditModal = function() {
            const modal = document.getElementById('grn-ai-audit-modal');
            const body = document.getElementById('grn-ai-audit-body');
            if (!modal || !body) return;
            let lines = (typeof receiptLines !== 'undefined' && Array.isArray(receiptLines) && receiptLines.length) ? receiptLines : [];
            if (!lines.length && typeof pendingMap !== 'undefined') {
                Object.values(pendingMap).slice(0, 10).forEach(inv => {
                    if (inv && inv.lines) {
                        Object.values(inv.lines).forEach(l => lines.push(l));
                    }
                });
            }
            if (!lines.length) {
                body.innerHTML = `<div style="padding:24px; text-align:center; font-weight:900; font-size:1.1rem;">لا توجد أصناف قيد الاستلام حالياً لمراجعتها بالذكاء الاصطناعي.</div>`;
            } else {
                let rowsHtml = lines.map((l, idx) => {
                    const name = (typeof getItemName === 'function') ? getItemName(l.itemId) : l.itemId;
                    const curP = parseFloat(l.price) || 0;
                    let refP = curP, sellP = Math.round(curP * 1.18);
                    if (typeof itemsDb !== 'undefined' && Array.isArray(itemsDb)) {
                        const it = itemsDb.find(i => String(i.id) === String(l.itemId) || i.name === l.itemId);
                        if (it) {
                            refP = parseFloat(it.costPrice || it.buyPrice || curP) || curP;
                            sellP = parseFloat(it.sellPrice || it.price || Math.round(curP * 1.18)) || Math.round(curP * 1.18);
                        }
                    }
                    const diff = curP - refP;
                    const pct = refP > 0 ? Math.round((Math.abs(diff) / refP) * 100) : 0;
                    const statusBadge = diff > 0.01
                        ? `<span class="pur-price-subtxt var-up">▲ ارتفع +${pct}%</span>`
                        : (diff < -0.01 ? `<span class="pur-price-subtxt var-down">▼ انخفض -${pct}%</span>` : `<span class="pur-price-subtxt var-same">● مستقر</span>`);
                    const lampCls = diff > 0.01 ? (pct >= 15 ? 'lamp-red' : 'lamp-amber') : 'lamp-green';
                    return `<tr style="border-bottom:1px solid rgba(148,163,184,0.25);">
                        <td style="padding:10px; font-weight:900;">${idx + 1}</td>
                        <td style="padding:10px; font-weight:900;">📦 ${name}</td>
                        <td style="padding:10px; text-align:center; font-family:'Outfit'; font-weight:900;">${refP.toLocaleString('en-US')}</td>
                        <td style="padding:10px; text-align:center; font-family:'Outfit'; font-weight:900;">${curP.toLocaleString('en-US')}</td>
                        <td style="padding:10px; text-align:center;">${statusBadge} <span class="pur-price-lamp ${lampCls}"></span></td>
                        <td style="padding:10px; text-align:center; font-family:'Outfit'; font-weight:900; color:#10b981;">${sellP.toLocaleString('en-US')}</td>
                    </tr>`;
                }).join('');
                body.innerHTML = `<table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:rgba(124,58,237,0.18); border-bottom:2px solid #7c3aed;">
                            <th style="padding:10px; text-align:right;">#</th>
                            <th style="padding:10px; text-align:right;">الصنف</th>
                            <th style="padding:10px; text-align:center;">آخر تكلفة</th>
                            <th style="padding:10px; text-align:center;">سعر الفاتورة</th>
                            <th style="padding:10px; text-align:center;">رادار التغير</th>
                            <th style="padding:10px; text-align:center;">سعر البيع المقترح</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>`;
            }
            modal.style.display = 'flex';
        };

        // 4. Sync Faint Zero Class (.is-zero-val) & Color Picker Input Events
        function syncGrnZeroInputs() {
            document.querySelectorAll('#items-tbody input.qty-input, #items-tbody input.factor-input').forEach(inp => {
                const v = parseFloat(inp.value);
                if (!inp.value || v === 0) inp.classList.add('is-zero-val');
                else inp.classList.remove('is-zero-val');
            });
        }
        document.addEventListener('input', function(e) {
            if (e.target && (e.target.classList.contains('qty-input') || e.target.classList.contains('factor-input'))) {
                const v = parseFloat(e.target.value);
                if (!e.target.value || v === 0) e.target.classList.add('is-zero-val');
                else e.target.classList.remove('is-zero-val');
            }
            if (e.target && e.target.id === 'grid-text-color' && typeof syncColorFromPicker === 'function') {
                syncColorFromPicker(e.target.value);
            }
        });

        const origRenderLines = window.renderReceiptLines;
        if (typeof origRenderLines === 'function' && !origRenderLines._constitutionWrapped) {
            const wrapped = function() {
                const res = origRenderLines.apply(this, arguments);
                syncGrnZeroInputs();
                if (typeof updateSummary === 'function') updateSummary();
                return res;
            };
            wrapped._constitutionWrapped = true;
            window.renderReceiptLines = wrapped;
        }
        setTimeout(() => {
            syncGrnZeroInputs();
            window.updateGrnHubFinStrip();
        }, 350);
    })();
