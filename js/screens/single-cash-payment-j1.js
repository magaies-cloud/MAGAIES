        let lastEnterTime = 0;
        // Perfected Exact Path Enter Navigation & Ctrl+S Instant Save
        document.addEventListener('keydown', function(e) {
            // 🌟 اختصار الحفظ السريع الذكي (Ctrl + S / Cmd + S) من أي مكان بالشاشة
            if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'س')) {
                e.preventDefault();
                if (typeof submitVoucher === 'function') {
                    submitVoucher();
                }
                return;
            }

            if (e.key === 'Enter') {
                let now = Date.now();
                if (now - lastEnterTime < 200) { e.preventDefault(); return; } // prevent rapid double jump
                lastEnterTime = now;
                
                let current = document.activeElement;
                if (current.tagName === 'TEXTAREA' && current.id !== 'from-statement') return; 

                // Determine sequence based on current focus ID
                let id = current.id;
                
                // If focus is inside a choices container, figure out which one (ignore choices inner IDs)
                if (current.closest('.choices')) {
                    let choicesDiv = current.closest('.choices');
                    if (choicesDiv.classList.contains('is-open')) return; // let choices select item
                    
                    let originalSelect = choicesDiv.querySelector('select');
                    if (originalSelect) id = originalSelect.id;
                    else {
                        let prev = choicesDiv.previousElementSibling;
                        if (prev && prev.tagName === 'SELECT') id = prev.id;
                    }
                }

                let nextAction = null;

                if (id === 'amount-local') {
                    nextAction = () => {
                        const fromSearch = document.getElementById('from-account-search');
                        if (fromSearch) { fromSearch.focus(); fromSearch.select(); }
                    };
                } else if (id === 'amount-foreign') {
                    nextAction = () => {
                        const fromSearch = document.getElementById('from-account-search');
                        if (fromSearch) { fromSearch.focus(); fromSearch.select(); }
                    };
                } else if (id === 'currency') {
                    nextAction = () => { let n = document.getElementById('exchange-rate'); if (n) { n.focus(); n.select(); } };
                } else if (id === 'exchange-rate') {
                    nextAction = () => { let n = document.getElementById('amount-foreign'); if (n) { n.focus(); n.select(); } };
                } else if (id === 'from-account-search' || id === 'to-account-search') {
                    nextAction = () => {
                        let type = id.split('-')[0];
                        let list = document.getElementById(type + '-account-dropdown-list');
                        if (list && list.classList.contains('show')) {
                            let firstItem = list.querySelector('.custom-dropdown-item');
                            if (firstItem && typeof firstItem.onmousedown === 'function') {
                                firstItem.onmousedown();
                                return;
                            }
                        }
                        if (id === 'from-account-search') {
                            const toSearch = document.getElementById('to-account-search');
                            if (toSearch) { toSearch.focus(); toSearch.select(); }
                        } else {
                            // انتقل بسلاسة إلى حقل المستفيد أو البيان المحاسبي
                            const benef = document.getElementById('beneficiary-name');
                            const stmt = document.getElementById('to-statement');
                            if (benef && benef.offsetParent !== null) { benef.focus(); benef.select(); }
                            else if (stmt) { stmt.focus(); stmt.select(); }
                            else { document.getElementById('btn-save')?.focus(); }
                        }
                    };
                } else if (id === 'beneficiary-name') {
                    nextAction = () => {
                        const cc = document.getElementById('cost-center');
                        const stmt = document.getElementById('to-statement');
                        if (cc && cc.offsetParent !== null && cc.value === '') { cc.focus(); }
                        else if (stmt) { stmt.focus(); stmt.select(); }
                        else { document.getElementById('btn-save')?.focus(); }
                    };
                } else if (id === 'cost-center') {
                    nextAction = () => {
                        const stmt = document.getElementById('to-statement');
                        if (stmt) { stmt.focus(); stmt.select(); }
                        else { document.getElementById('btn-save')?.focus(); }
                    };
                } else if (id === 'to-statement' || id === 'from-statement') {
                    nextAction = () => {
                        const saveBtn = document.getElementById('btn-save');
                        if (saveBtn) { saveBtn.focus(); }
                    };
                } else if (current.classList.contains('transfer-amount')) {
                    nextAction = () => {
                        const row = current.closest('.transfer-row');
                        if (row) {
                            const bank = row.querySelector('.transfer-note');
                            if (bank) { bank.focus(); bank.select(); }
                        }
                    };
                } else if (current.classList.contains('transfer-note')) {
                    nextAction = () => {
                        const row = current.closest('.transfer-row');
                        if (row) {
                            const no = row.querySelector('.transfer-no');
                            if (no) { no.focus(); no.select(); }
                        }
                    };
                } else if (current.classList.contains('transfer-no')) {
                    nextAction = () => {
                        const row = current.closest('.transfer-row');
                        if (row) {
                            const statement = row.querySelector('.transfer-statement');
                            if (statement) { statement.focus(); statement.select(); }
                        }
                    };
                } else if (current.classList.contains('transfer-statement')) {
                    nextAction = () => {
                        const saveBtn = document.getElementById('btn-save');
                        if (saveBtn) { saveBtn.focus(); }
                    };
                }

                if (nextAction) {
                    e.preventDefault();
                    // Small timeout ensures Choices.js DOM is ready if it just opened
                    setTimeout(nextAction, 10);
                }
            }
        });

        // Sound & Progress Bar Recalculator
        const PlaySound = {
            ctx: null,
            init() {
                if (!this.ctx) {
                    this.ctx = new (window.AudioContext || window.webkitAudioContext)();
                }
            },
            success() {
                try {
                    this.init();
                    const now = this.ctx.currentTime;
                    const osc1 = this.ctx.createOscillator();
                    const osc2 = this.ctx.createOscillator();
                    const gain = this.ctx.createGain();
                    osc1.type = 'sine';
                    osc2.type = 'sine';
                    osc1.frequency.setValueAtTime(523.25, now);
                    osc1.frequency.exponentialRampToValueAtTime(880, now + 0.15);
                    osc2.frequency.setValueAtTime(659.25, now);
                    osc2.frequency.exponentialRampToValueAtTime(1046.50, now + 0.15);
                    gain.gain.setValueAtTime(0.08, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
                    osc1.connect(gain);
                    osc2.connect(gain);
                    gain.connect(this.ctx.destination);
                    osc1.start(now);
                    osc2.start(now);
                    osc1.stop(now + 0.25);
                    osc2.stop(now + 0.25);
                } catch(e) {}
            }
        };

        function recalcPaymentSummary() {
            const amtStr = document.getElementById('amount-local')?.value || '0';
            let amount = parseFloat(amtStr.replace(/,/g, '')) || 0;
            
            const panel = document.getElementById('payment-panel');
            if (!panel) return;
            
            panel.style.display = 'block';
            
            // Get active method
            let method = 'cash';
            const bankActive = document.getElementById('toggle-bank')?.classList.contains('active');
            const chequeActive = document.getElementById('toggle-cheque')?.classList.contains('active');
            
            if (bankActive && chequeActive) method = 'hybrid';
            else if (bankActive) method = 'bank';
            else if (chequeActive) method = 'cheque';
            else method = 'cash';
            
            let tSum = 0;
            if (bankActive) {
                document.querySelectorAll('.transfer-row .transfer-amount').forEach(inp => tSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
            }
            let cSum = 0;
            if (chequeActive) {
                document.querySelectorAll('.cheque-row .cheque-amount').forEach(inp => cSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
            }
            
            const amtElGlobal = document.getElementById('amount-local');
            const amtForGlobal = document.getElementById('amount-foreign');
            
            // Auto-calculate amount-local based on rows if not cash!
            if (method !== 'cash') {
                if (amtElGlobal) amtElGlobal.readOnly = true; // Lock local amount field
                if (amtForGlobal) { amtForGlobal.readOnly = true; amtForGlobal.style.cursor = 'not-allowed'; }
                const totalSum = tSum + cSum;
                if (Math.abs(amount - totalSum) > 0.001) {
                    amount = totalSum;
                    if (amtElGlobal) {
                        // Remember original cursor position if focused on amount-local (unlikely but safe)
                        let parts = totalSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]]; // Don't add .00 if not needed
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        amtElGlobal.value = parts.join('.');
                        
                        if (typeof calcEquiv === 'function') calcEquiv('local');
                    }
                }
            } else {
                if (amtElGlobal) amtElGlobal.readOnly = false; // Unlock local amount field in cash mode
                if (amtForGlobal) { amtForGlobal.readOnly = false; amtForGlobal.style.cursor = 'text'; }
            }
            if (typeof window.updateLiveTafqeetAndBalances === 'function') window.updateLiveTafqeetAndBalances();
            
            // Update unified total box
            const unifiedBox = document.getElementById('unified-total-box');
            if (unifiedBox) {
                if (method !== 'cash') {
                    unifiedBox.style.display = 'block';
                    const uBank = document.getElementById('unified-bank-total');
                    const uCheque = document.getElementById('unified-cheque-total');
                    const uGrand = document.getElementById('unified-grand-total');
                    
                    if (uBank) {
                        let parts = tSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]];
                        uBank.textContent = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (parts[1] ? '.' + parts[1] : '');
                    }
                    if (uCheque) {
                        let parts = cSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]];
                        uCheque.textContent = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (parts[1] ? '.' + parts[1] : '');
                    }
                    if (uGrand) {
                        let totalSum = tSum + cSum;
                        let parts = totalSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]];
                        uGrand.textContent = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (parts[1] ? '.' + parts[1] : '');
                    }
                } else {
                    unifiedBox.style.display = 'none';
                }
            }
            
            // Progress widths
            const elCash = document.getElementById('prog-cash');
            const elBank = document.getElementById('prog-bank');
            const elCheque = document.getElementById('prog-cheque');
            
            if (elCash) elCash.style.width = '0%';
            if (elBank) elBank.style.width = '0%';
            if (elCheque) elCheque.style.width = '0%';
            
            if (method === 'cash') {
                if (elCash) elCash.style.width = '100%';
            } else if (method === 'bank') {
                if (elBank) elBank.style.width = '100%';
            } else if (method === 'cheque') {
                if (elCheque) elCheque.style.width = '100%';
            } else if (method === 'hybrid') {
                const bankPct = amount > 0 ? Math.min(100, (tSum / amount) * 100) : 0;
                const chequePct = amount > 0 ? Math.min(100, (cSum / amount) * 100) : 0;
                
                if (elBank) elBank.style.width = bankPct + '%';
                if (elCheque) elCheque.style.width = chequePct + '%';
            }
            
            // Update status badge
            const badge = document.getElementById('payment-status-badge');
            if (badge) {
                if (method === 'hybrid') {
                    let tSum = 0;
                    document.querySelectorAll('.transfer-row .transfer-amount').forEach(inp => tSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
                    let cSum = 0;
                    document.querySelectorAll('.cheque-row .cheque-amount').forEach(inp => cSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
                    
                    if (Math.abs((tSum + cSum) - amount) < 0.01) {
                        badge.textContent = '💳 مدفوعة بالكامل (تحويل وشيك)';
                        badge.style.background = 'rgba(56, 189, 248, 0.15)';
                        badge.style.color = '#38bdf8';
                        badge.style.boxShadow = '0 0 10px rgba(56, 189, 248, 0.2)';
                    } else {
                        const remaining = amount - (tSum + cSum);
                        let parts = remaining.toFixed(2).split('.');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        badge.textContent = `⏳ متبقي: ${parts.join('.')}`;
                        badge.style.background = 'rgba(245, 158, 11, 0.15)';
                        badge.style.color = '#fbbf24';
                        badge.style.boxShadow = '0 0 10px rgba(245, 158, 11, 0.2)';
                    }
                } else {
                    if (badge.dataset.status !== 'fully-paid') {
                        PlaySound.success();
                        badge.dataset.status = 'fully-paid';
                    }
                    badge.textContent = '💳 مدفوعة بالكامل';
                    badge.style.background = 'rgba(16, 185, 129, 0.15)';
                    badge.style.color = '#34d399';
                    badge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.2)';
                }
            }
        }

        function togglePaymentSection(clickedMethod) {
            const btnCash = document.getElementById('toggle-cash');
            const btnBank = document.getElementById('toggle-bank');
            const btnCheque = document.getElementById('toggle-cheque');

            if (clickedMethod === 'cash') {
                if (btnCash) btnCash.classList.add('active');
                if (btnBank) btnBank.classList.remove('active');
                if (btnCheque) btnCheque.classList.remove('active');
            } else {
                if (btnCash) btnCash.classList.remove('active');
                
                const btn = document.getElementById('toggle-' + clickedMethod);
                if (btn) {
                    btn.classList.toggle('active');
                }
                
                // If both bank and cheque are deactivated, fallback to cash
                const bankActive = btnBank?.classList.contains('active');
                const chequeActive = btnCheque?.classList.contains('active');
                if (!bankActive && !chequeActive) {
                    if (btnCash) btnCash.classList.add('active');
                }
            }

            // Sync with radio buttons
            const bankActive = btnBank?.classList.contains('active');
            const chequeActive = btnCheque?.classList.contains('active');
            
            let radioId = 'radio-cash';
            if (bankActive && chequeActive) radioId = 'radio-hybrid';
            else if (bankActive) radioId = 'radio-mobile';
            else if (chequeActive) radioId = 'radio-check';

            const radio = document.getElementById(radioId);
            if (radio) {
                radio.checked = true;
            }

            // Update payment method boxes visibility
            const isCash = !bankActive && !chequeActive;
            document.getElementById('pay-cash-section').style.display = isCash ? 'flex' : 'none';
            document.getElementById('pay-bank-section').style.display = bankActive ? 'flex' : 'none';
            document.getElementById('pay-cheque-section').style.display = chequeActive ? 'flex' : 'none';
            
            if (document.getElementById('pay-credit-section')) {
                document.getElementById('pay-credit-section').style.display = 'none';
            }
            
            recalcPaymentSummary();
        }

        // Focus delegation to clear zero values instantly on click/focus
        document.addEventListener('focus', function(event) {
            const input = event.target;
            if (input.id === 'amount-local' || input.id === 'amount-foreign') {
                const val = parseFloat(input.value.replace(/,/g, '')) || 0;
                if (val === 0) {
                    input.value = '';
                }
            }
        }, true);

        // Blur delegation to format payment inputs to 2 decimal places with commas
        document.addEventListener('blur', function(event) {
            const input = event.target;
            if (input.id === 'amount-local' || input.id === 'amount-foreign') {
                const val = parseFloat(input.value.replace(/,/g, '')) || 0;
                if (input.value.trim() === '') {
                    input.value = '0.00';
                } else {
                    let parts = val.toFixed(2).split('.');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    input.value = parts.join('.');
                }
                // Recalculate progress
                recalcPaymentSummary();
            }
        }, true);

        document.addEventListener('DOMContentLoaded', () => {
            // Override togglePaymentMethod to handle the inline unified boxes
            window.togglePaymentMethod = function() {
                const methodRadio = document.querySelector('input[name="payment_method"]:checked');
                const method = methodRadio ? methodRadio.value : 'cash';
                
                const btnCash = document.getElementById('toggle-cash');
                const btnBank = document.getElementById('toggle-bank');
                const btnCheque = document.getElementById('toggle-cheque');

                if (btnCash) btnCash.classList.remove('active');
                if (btnBank) btnBank.classList.remove('active');
                if (btnCheque) btnCheque.classList.remove('active');

                if (method === 'mobile') {
                    if (btnBank) btnBank.classList.add('active');
                } else if (method === 'check') {
                    if (btnCheque) btnCheque.classList.add('active');
                } else if (method === 'hybrid') {
                    if (btnBank) btnBank.classList.add('active');
                    if (btnCheque) btnCheque.classList.add('active');
                } else {
                    if (btnCash) btnCash.classList.add('active');
                }

                const bankActive = btnBank?.classList.contains('active');
                const chequeActive = btnCheque?.classList.contains('active');
                const isCash = !bankActive && !chequeActive;

                if (document.getElementById('pay-cash-section')) {
                    document.getElementById('pay-cash-section').style.display = isCash ? 'flex' : 'none';
                    document.getElementById('pay-bank-section').style.display = bankActive ? 'flex' : 'none';
                    document.getElementById('pay-cheque-section').style.display = chequeActive ? 'flex' : 'none';
                }
                
                recalcPaymentSummary();
            };
            
            // Set initial state
            setTimeout(() => {
                if (typeof updateCurrency === 'function') updateCurrency();
                const container = document.getElementById('transfers-container');
                if (container && container.children.length === 0 && typeof addTransferRow === 'function') {
                    addTransferRow('', '', 0);
                }
                window.togglePaymentMethod();
            }, 50);
        });
