(function() {
    if (window.MagaiesGuardianVaultLoaded) return;
    window.MagaiesGuardianVaultLoaded = true;

    let vaultState = { pending_count: 0, has_custom_pass: false, unlocked: false, active_tab: 'audit', last_data: null };

    function ensureSidebarBadgeUnderMobile() {
        const nav = document.querySelector('.global-sidebar .sidebar-nav');
        if (!nav || nav.querySelector('.mgv-sidebar-slot')) return;
        const slot = document.createElement('a');
        slot.href = '#';
        slot.setAttribute('data-href', 'system-self-audit.php');
        slot.className = 'sidebar-item mgv-sidebar-slot magaiees-guardian-red-box';
        slot.title = 'درع السيادة — غرفة القيادة والسيطرة';
        slot.onclick = function(e) { if (e) e.preventDefault(); if (window.openMagaiesGuardianVault) window.openMagaiesGuardianVault(); return false; };
        slot.innerHTML = `<span class="sidebar-icon mgv-shield-icon" style="color:#10b981"><span style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;"><svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 3l7 3v5.5c0 4.8-3.2 8.3-7 9.5-3.8-1.2-7-4.7-7-9.5V6l7-3z"/></svg><span class="mgv-num" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:inherit;font-weight:900;font-size:8.5px;line-height:1;padding-top:1px;color:currentColor;">${vaultState.pending_count}</span></span></span><span class="sidebar-text" style="font-family:inherit !important;font-weight:900 !important;">درع السيادة</span>`;
        const mobLink = nav.querySelector('a[data-href="mobile-dashboard.php"], a[href*="mobile-dashboard.php"]');
        if (mobLink && mobLink.nextSibling) nav.insertBefore(slot, mobLink.nextSibling);
        else nav.appendChild(slot);
    }

    function updateRedBadge(count) {
        vaultState.pending_count = Number(count) || 0;
        ensureSidebarBadgeUnderMobile();
        document.querySelectorAll('.magaiees-guardian-red-box').forEach(el => {
            const numEl = el.querySelector('.mgv-num');
            if (numEl) numEl.textContent = vaultState.pending_count;
            const iconEl = el.querySelector('.mgv-shield-icon') || el.querySelector('.sidebar-icon');
            if (iconEl) {
                iconEl.style.color = vaultState.pending_count > 0 ? '#fb7185' : '#10b981';
            }
            el.style.background = '';
            el.style.borderColor = '';
            el.style.boxShadow = '';
        });
    }

    window.toggleMgvEye = function(inputId, btnEl) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        if (btnEl) btnEl.textContent = show ? '🙈' : '👁️';
    };

    async function fetchCount() {
        ensureSidebarBadgeUnderMobile();
        try {
            const r = await fetch('api/guardian_vault_api.php?action=count&_t=' + Date.now(), { cache: 'no-store' });
            const d = await r.json();
            if (d && d.success) {
                vaultState.has_custom_pass = !!d.has_custom_pass;
                vaultState.unlocked = !!d.unlocked;
                updateRedBadge(d.pending_count);
            }
        } catch (e) {}
    }

    function ensureModal() {
        let m = document.getElementById('mgv-owner-modal');
        if (m) return m;
        if (!document.getElementById('mgv-sovereign-imperial-css')) {
            const st = document.createElement('style');
            st.id = 'mgv-sovereign-imperial-css';
            st.textContent = `
                #mgv-owner-modal .mgv-imp-card { background: radial-gradient(circle at 50% 0%, #4c0519 0%, #160409 48%, #040406 100%) !important; border: 3px solid #facc15 !important; box-shadow: 0 32px 90px rgba(0,0,0,0.95), 0 0 55px rgba(225,29,72,0.45), inset 0 0 28px rgba(250,204,21,0.18) !important; color: #ffffff !important; }
                #mgv-owner-modal .mgv-imp-decree { background: linear-gradient(135deg, rgba(159,18,57,0.42) 0%, rgba(9,9,11,0.94) 60%, rgba(120,53,15,0.38) 100%) !important; border: 2px solid rgba(250,204,21,0.75) !important; border-right: 6px solid #e11d48 !important; color: #fef08a !important; }
                #mgv-owner-modal .mgv-imp-inp { background: #050508 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: 2px solid #d97706 !important; box-shadow: inset 0 3px 10px rgba(0,0,0,0.9) !important; }
                #mgv-owner-modal .mgv-imp-inp:focus { border-color: #fde047 !important; box-shadow: 0 0 18px rgba(225,29,72,0.55), 0 0 8px rgba(250,204,21,0.65) !important; outline: none !important; }
                #mgv-owner-modal .mgv-imp-inp::placeholder { color: rgba(253,224,71,0.55) !important; -webkit-text-fill-color: rgba(253,224,71,0.55) !important; }
                #mgv-owner-modal .mgv-imp-btn { background: linear-gradient(135deg, #9f1239 0%, #e11d48 45%, #881337 100%) !important; border: 2.5px solid #fde047 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 12px 30px rgba(225,29,72,0.55), inset 0 1px 2px rgba(254,240,138,0.65) !important; transition: transform 0.18s ease, filter 0.18s ease !important; }
                #mgv-owner-modal .mgv-imp-btn:hover { transform: translateY(-2px) scale(1.015) !important; filter: brightness(1.14) !important; }
                #mgv-owner-modal .mgv-imp-close { background: linear-gradient(135deg, #18181b 0%, #4c0519 100%) !important; border: 2px solid #facc15 !important; color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; transition: all 0.18s ease !important; }
                #mgv-owner-modal .mgv-imp-close:hover { background: linear-gradient(135deg, #be123c 0%, #e11d48 100%) !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; transform: scale(1.06) !important; }
            `;
            document.head.appendChild(st);
        }
        m = document.createElement('div');
        m.id = 'mgv-owner-modal';
        m.setAttribute('data-dark-capsule', '1');
        m.style.cssText = 'position:fixed;inset:0;z-index:2147483600;background:radial-gradient(circle at center,rgba(24,4,10,0.88) 0%,rgba(2,2,5,0.96) 100%);backdrop-filter:blur(10px);display:none;align-items:center;justify-content:center;padding:14px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
        document.body.appendChild(m);
        return m;
    }

    function isLightTone() {
        return document.body.classList.contains('light-mode') || document.documentElement.classList.contains('light-theme') || !!localStorage.getItem('magaies_light_tone');
    }

    window.openMagaiesGuardianVault = async function() {
        await fetchCount();
        const modal = ensureModal();
        modal.style.display = 'flex';
        if (vaultState.unlocked) await loadUnlockedInbox();
        else renderPasswordGate();
    };

    function renderPasswordGate(errMsg = '') {
        const modal = ensureModal();
        const isFirstSetup = !vaultState.has_custom_pass;
        const savedRep = localStorage.getItem('mgv_owner_rep_name') || '';
        modal.innerHTML = `
            <div class="mgv-imp-card" data-dark-capsule="1" style="width:100%;max-width:600px;border-radius:26px;padding:28px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#facc15,#e11d48,#fde047,#be123c,#facc15);"></div>
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;border-bottom:2px solid rgba(250,204,21,0.35);padding-bottom:16px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:14px;">
                        <div style="width:58px;height:58px;border-radius:16px;background:linear-gradient(145deg,#9f1239,#09090b);border:2.5px solid #fde047;box-shadow:0 0 24px rgba(225,29,72,0.65);display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;">🛡️</div>
                        <div>
                            <div style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(90deg,#881337,#09090b);border:1.5px solid #facc15;color:#fde047 !important;-webkit-text-fill-color:#fde047 !important;font-weight:900;padding:3px 13px;border-radius:999px;font-size:12px;margin-bottom:6px;">👑 درع السيادة السيادي • تنبيهات الرقابة (${vaultState.pending_count})</div>
                            <h3 style="margin:0;font-size:1.55rem;font-weight:900;color:#fde047 !important;-webkit-text-fill-color:#fde047 !important;text-shadow:0 2px 14px rgba(250,204,21,0.45);letter-spacing:0.2px;">غرفة القيادة والسيطرة</h3>
                        </div>
                    </div>
                    <button type="button" class="mgv-imp-close" onclick="document.getElementById('mgv-owner-modal').style.display='none'" title="إغلاق المقصورة" style="padding:7px 14px;border-radius:12px;font-family:inherit;font-size:0.88rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:6px;flex-shrink:0;"><span>إغلاق</span><span>✕</span></button>
                </div>
                <div class="mgv-imp-decree" data-dark-capsule="1" style="margin:0 0 18px;padding:14px 16px;border-radius:15px;font-size:0.98rem;font-weight:900;line-height:1.8;">
                    <div style="color:#fb7185 !important;-webkit-text-fill-color:#fb7185 !important;font-size:0.86rem;margin-bottom:4px;">⛔ منطقة سيادية محظورة — تصريح من المستوى الأعلى فقط</div>
                    <div style="color:#ffffff !important;-webkit-text-fill-color:#ffffff !important;">هذه المقصورة مخصصة حصرياً <span style="color:#fde047 !important;-webkit-text-fill-color:#fde047 !important;">للكيان المؤسسي المالك ومجلس إدارته والشركات المساهمة</span>؛ حيث تُدار الشفرات السيادية العليا، ومفاتيح الحوكمة، والسيطرة المركزية الشاملة للنظام.</div>
                </div>
                ${errMsg ? `<div style="margin-bottom:14px;padding:10px 14px;border-radius:12px;background:rgba(225,29,72,0.28);border:2px solid #fb7185;color:#fecdd3 !important;-webkit-text-fill-color:#fecdd3 !important;font-size:0.92rem;font-weight:900;">⚠️ ${errMsg}</div>` : ''}
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <input id="mgv-rep-name" class="mgv-imp-inp" type="text" value="${savedRep}" placeholder="👤 اسم المفوض المعتمد / عضو مجلس الإدارة أو الشركة المساهمة..." oninput="try{localStorage.setItem('mgv_owner_rep_name',this.value)}catch(e){}" style="width:100%;box-sizing:border-box;padding:13px 15px;border-radius:13px;font-family:inherit;font-size:1rem;font-weight:900;">
                    <div style="position:relative;display:flex;align-items:center;">
                        <input id="mgv-pass-input" class="mgv-imp-inp" type="password" placeholder="${isFirstSetup ? '🔑 عيّن الشفرة السيادية العليا (4 خانات فأكثر)' : '🔑 أدخل الشفرة السيادية لغرفة القيادة والسيطرة'}" style="width:100%;box-sizing:border-box;padding:13px 15px 13px 46px;border-radius:13px;font-family:inherit;font-size:1.04rem;font-weight:900;" onkeydown="if(event.key==='Enter')window.submitGuardianPass(${isFirstSetup})">
                        <button type="button" onclick="window.toggleMgvEye('mgv-pass-input', this)" title="إظهار / إخفاء الشفرة" style="position:absolute;left:10px;background:transparent;border:none;font-size:19px;cursor:pointer;padding:4px;">👁️</button>
                    </div>
                    ${isFirstSetup ? `<input id="mgv-wa-input" class="mgv-imp-inp" type="text" dir="ltr" placeholder="📲 رقم واتساب مجلس الإدارة المعتمد (اختياري: 2499...)" style="width:100%;box-sizing:border-box;padding:12px 15px;border-radius:13px;font-family:inherit;font-size:0.95rem;font-weight:900;">` : ''}
                    <button type="button" class="mgv-imp-btn" onclick="window.submitGuardianPass(${isFirstSetup})" style="width:100%;padding:14px;border-radius:14px;font-family:inherit;font-size:1.08rem;font-weight:900;cursor:pointer;letter-spacing:0.3px;">
                        ${isFirstSetup ? '👑 اعتماد الشفرة السيادية وفتح غرفة القيادة والسيطرة' : '👑 فتح البوابة السيادية — غرفة القيادة والسيطرة'}
                    </button>
                </div>
            </div>`;
        setTimeout(() => document.getElementById(savedRep ? 'mgv-pass-input' : 'mgv-rep-name')?.focus(), 50);
    }

    window.submitGuardianPass = async function(isFirstSetup) {
        const pass = (document.getElementById('mgv-pass-input')?.value || '').trim();
        const wa = (document.getElementById('mgv-wa-input')?.value || '').trim();
        if (!pass) return renderPasswordGate('يرجى إدخال كلمة المرور.');
        try {
            const r = await fetch('api/guardian_vault_api.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: isFirstSetup ? 'setup_first_pass' : 'unlock', password: pass, owner_whatsapp: wa })
            });
            const d = await r.json();
            if (!d || !d.success) return renderPasswordGate((d && d.error) || 'كلمة المرور غير صحيحة.');
            vaultState.unlocked = true; vaultState.has_custom_pass = true; vaultState.last_data = d;
            updateRedBadge(d.pending_count);
            renderInboxData(d);
        } catch (e) { renderPasswordGate('تعذر الاتصال بالخادم.'); }
    };

    async function loadUnlockedInbox(extraAction = 'list', payload = {}) {
        try {
            const r = await fetch('api/guardian_vault_api.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.assign({ action: extraAction }, payload))
            });
            const d = await r.json();
            if (d && d.locked) { vaultState.unlocked = false; return renderPasswordGate(); }
            if (d && d.success) { vaultState.last_data = d; updateRedBadge(d.pending_count); renderInboxData(d); }
            else if (d && d.error) alert('⚠️ ' + d.error);
        } catch (e) {}
    }

    window.switchMgvTab = function(tab) {
        vaultState.active_tab = tab;
        if (vaultState.last_data) renderInboxData(vaultState.last_data);
    };
    window.decideGuardianProposal = function(id, decision) {
        const cardEl = document.getElementById('mgv-card-' + id);
        if (cardEl && decision !== 'pending') { cardEl.style.opacity = '0.25'; cardEl.style.transform = 'scale(0.97)'; }
        loadUnlockedInbox('decide', { id, decision });
    };
    window.runGuardianLiveScan = function() { loadUnlockedInbox('scan'); };
    window.lockGuardianVault = async function() {
        try { await fetch('api/guardian_vault_api.php?action=lock'); } catch (e) {}
        vaultState.unlocked = false;
        document.getElementById('mgv-owner-modal').style.display = 'none';
    };

    function collectFourWaSlots() {
        const vals = [1, 2, 3, 4].map(i => (document.getElementById('mgv-wa-slot-' + i)?.value || '').trim()).filter(Boolean);
        if (vals.length) return vals.join(', ');
        return (vaultState.last_data && vaultState.last_data.owner_whatsapp) || '';
    }

    window.runGuardianAtoZSim = async function() {
        const out = document.getElementById('mgv-ai-verdict-box');
        if (out) out.innerHTML = '⏳ جاري تشغيل محاكاة العميل والمورد من الألف للياء ومطابقة التسميع في كل الشاشات...';
        try {
            const r = await fetch('api/guardian_vault_api.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'simulate_atoz' })
            });
            const d = await r.json();
            if (d && d.steps && out) {
                out.innerHTML = `🧪 شهادة المحاكاة الشاملة لعميل ومورد من الألف للياء (${d.all_passed ? '✅ تطابق 100%' : '⚠️ تنبيه'} • ${d.elapsed_ms}ms):\n\n` +
                    d.steps.map(s => `[${s.status === 'PASS' ? '✔' : '✖'}] ${s.step}\n   ↳ ${s.detail}`).join('\n\n');
            }
        } catch (e) { if (out) out.innerHTML = '⚠️ تعذر تشغيل المحاكاة.'; }
    };

    window.saveGuardianOwnerSettings = async function() {
        const wa = collectFourWaSlots();
        const np = (document.getElementById('mgv-cfg-pass')?.value || '').trim();
        const gk = (document.getElementById('mgv-cfg-gemini')?.value ?? '').trim();
        const ok = (document.getElementById('mgv-cfg-openai')?.value ?? '').trim();
        const dk = (document.getElementById('mgv-cfg-deepseek')?.value ?? '').trim();
        await loadUnlockedInbox('save_settings', { owner_whatsapp: wa, new_password: np, gemini_key: gk, openai_key: ok, deepseek_key: dk });
        alert('✅ تم حفظ كلمة المرور وأرقام الواتساب ومفاتيح الذكاء الاصطناعي بنجاح.');
    };

    window.askGuardianAiCouncil = async function() {
        const q = (document.getElementById('mgv-ai-q')?.value || '').trim();
        const out = document.getElementById('mgv-ai-verdict-box');
        if (out) out.innerHTML = '⏳ جاري استشارة نماذج الذكاء الفائق (Gemini Pro + GPT-4o + DeepSeek R1) وفحص القيود الحية...';
        try {
            const r = await fetch('api/guardian_vault_api.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'ai_council', question: q })
            });
            const d = await r.json();
            vaultState.ai_council = d;
            if (vaultState.last_data) renderInboxData(vaultState.last_data);
        } catch (e) { if (out) out.innerHTML = '⚠️ تعذر الاتصال بمحرك الذكاء السيادي.'; }
    };

    window.sendGuardianToWhatsApp = async function() {
        let phone = collectFourWaSlots();
        if (!phone) {
            vaultState.active_tab = 'settings';
            if (vaultState.last_data) renderInboxData(vaultState.last_data);
            setTimeout(() => document.getElementById('mgv-wa-slot-1')?.focus(), 60);
            alert('📲 اكتب رقم واتساب واحد إلى 4 أرقام في الخانات المخصصة أمامك ثم اضغط إرسال.');
            return;
        }
        try {
            const r = await fetch('api/guardian_vault_api.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'send_whatsapp', owner_whatsapp: phone })
            });
            const d = await r.json();
            if (d && !d.success && d.error) return alert('⚠️ ' + d.error);
            if (d && d.success) {
                if (d.sent_direct) alert('✅ تم الإرسال مباشرةً إلى أرقامك المعتمدة: (' + (d.saved_list || d.phone) + ')');
                else if (d.wa_url) window.open(d.wa_url, '_blank');
                await loadUnlockedInbox('list');
            }
        } catch (e) {}
    };

    function renderInboxData(d) {
        const modal = ensureModal();
        const lt = isLightTone();
        const bg = lt ? '#f8f4ec' : '#0b1120', card = lt ? '#ffffff' : '#152342', ink = lt ? '#120500' : '#ffffff', sub = lt ? '#431407' : '#cbd5e1', brd = lt ? '#5c2808' : 'rgba(56,189,248,0.42)';
        const allProps = d.proposals || [];
        // Active tabs ONLY show pending items (decided & auto-healed items move immediately to Archive!)
        const auditPending = allProps.filter(p => p.status === 'pending' && !String(p.code || '').startsWith('G'));
        const radarPending = allProps.filter(p => p.status === 'pending' && String(p.code || '').startsWith('G'));
        const archiveItems = allProps.filter(p => p.status !== 'pending');
        const slots = d.owner_wa_slots || [];
        const tab = vaultState.active_tab || 'audit';

        const renderCards = (arr, isArchive = false) => {
            if (!arr.length) {
                return `<div style="grid-column:1/-1;text-align:center;padding:36px 20px;background:${card};border:2px dashed ${brd};border-radius:16px;font-size:1.05rem;font-weight:900;color:#059669;">${isArchive ? '📜 الأرشيف فارغ حالياً.' : '🎉 ممتاز! لا توجد عناصر معلقة في هذا القسم — جميع القرارات محفوظة في تبويب (📜 الأرشيف).'}</div>`;
            }
            return arr.map(p => {
                const stBadge = p.status === 'auto_healed'
                    ? '<span style="background:#0284c7;color:#fff;padding:4px 11px;border-radius:999px;font-size:12px;font-weight:900;">⚡ شفاء ذاتي تلقائي</span>'
                    : (p.status === 'approved'
                        ? '<span style="background:#059669;color:#fff;padding:4px 11px;border-radius:999px;font-size:12px;font-weight:900;">✅ معتمد ومنفذ</span>'
                        : (p.status === 'rejected'
                            ? '<span style="background:#64748b;color:#fff;padding:4px 11px;border-radius:999px;font-size:12px;font-weight:900;">✖ مرفوض</span>'
                            : '<span style="background:#e11d48;color:#fff;padding:4px 11px;border-radius:999px;font-size:12px;font-weight:900;">🔴 بانتظار قرارك</span>'));
                const logLine = isArchive ? `<div style="font-size:0.82rem;font-weight:900;color:#059669;background:rgba(16,185,129,0.1);padding:6px 10px;border-radius:8px;">🕒 سجل الأرشيف: ${p.exec_log || (p.status === 'approved' ? 'تم الاعتماد من المالك وحفظه في سجل السياسات' : 'تم الرفض من المالك')} ${p.decided_at ? '• ' + p.decided_at : ''}</div>` : '';
                return `
                    <div id="mgv-card-${p.id}" style="background:${card};border:2px solid ${p.status === 'pending' ? '#e11d48' : brd};border-radius:16px;padding:16px 18px;display:flex;flex-direction:column;gap:10px;transition:all 0.2s ease;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:12.5px;font-weight:900;color:${sub};">📂 ${p.category} • <code style="font-size:11.5px;">${p.target_files}</code></span>
                            ${stBadge}
                        </div>
                        <div style="font-size:1.06rem;font-weight:900;color:${ink};line-height:1.45;">${p.title}</div>
                        <div style="font-size:0.92rem;font-weight:700;color:${sub};line-height:1.65;">${p.study_summary}</div>
                        ${logLine}
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:2px;">
                            ${!isArchive ? `
                                <button type="button" onclick="window.decideGuardianProposal(${p.id}, 'approved')" style="padding:7px 16px;border-radius:10px;border:none;background:#059669;color:#fff;font-family:inherit;font-size:0.86rem;font-weight:900;cursor:pointer;">✅ موافقة ونقل للأرشيف</button>
                                <button type="button" onclick="window.decideGuardianProposal(${p.id}, 'rejected')" style="padding:7px 16px;border-radius:10px;border:none;background:#dc2626;color:#fff;font-family:inherit;font-size:0.86rem;font-weight:900;cursor:pointer;">✖ رفض ونقل للأرشيف</button>
                            ` : (p.status !== 'auto_healed' ? `<button type="button" onclick="window.decideGuardianProposal(${p.id}, 'pending')" style="padding:6px 12px;border-radius:9px;border:1.5px solid ${brd};background:transparent;color:${ink};font-family:inherit;font-size:0.8rem;font-weight:800;cursor:pointer;">↩ إعادة لصندوق المراجعة</button>` : '')}
                        </div>
                    </div>`;
            }).join('');
        };

        const settingsHtml = `
            <div style="display:flex;flex-direction:column;gap:16px;max-width:860px;margin:0 auto;width:100%;">
                <div style="background:${card};border:2px solid #16a34a;border-radius:16px;padding:18px 20px;">
                    <h4 style="margin:0 0 6px;font-size:1.06rem;font-weight:900;color:${ink};">📲 أرقام واتساب المالك وفريق الرقابة (حتى 4 أرقام — مستقلة عن واتساب النظام)</h4>
                    <p style="margin:0 0 14px;font-size:0.88rem;font-weight:700;color:${sub};">تُرسل إليها تقارير الفحص والمخاطر ورادار التطور العالمي فوراً:</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;">
                        ${[1, 2, 3, 4].map(n => `
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                <label style="font-size:0.84rem;font-weight:900;color:${ink};">رقم واتساب (${n}) ${slots[n - 1] ? '<span style="color:#059669;">✔ مؤكد</span>' : ''}</label>
                                <input id="mgv-wa-slot-${n}" type="text" dir="ltr" value="${slots[n - 1] || ''}" placeholder="مثال: 249912345678 أو 09..." style="padding:10px 12px;border-radius:10px;border:1.8px solid ${slots[n - 1] ? '#059669' : brd};background:${bg};color:${ink};font-family:inherit;font-size:0.94rem;font-weight:900;">
                            </div>`).join('')}
                    </div>
                </div>
                <div style="background:${card};border:2px solid #d97706;border-radius:16px;padding:18px 20px;">
                    <h4 style="margin:0 0 8px;font-size:1.06rem;font-weight:900;color:${ink};">🔐 كلمة المرور السرية للمالك (اضغط على العين 👁️ لرؤية الباسورد والتأكد منه أو تغييره)</h4>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <div style="position:relative;flex:1;min-width:260px;display:flex;align-items:center;">
                            <input id="mgv-cfg-pass" type="password" value="${d.owner_pass_plain || ''}" placeholder="اكتب كلمة مرور سرية جديدة (4 خانات فأكثر)" style="width:100%;box-sizing:border-box;padding:11px 14px 11px 44px;border-radius:10px;border:2px solid #d97706;background:${bg};color:${ink};font-family:inherit;font-size:1rem;font-weight:900;">
                            <button type="button" onclick="window.toggleMgvEye('mgv-cfg-pass', this)" title="إظهار / إخفاء كلمة المرور الحالية للتأكد منها" style="position:absolute;left:8px;background:transparent;border:none;font-size:19px;cursor:pointer;padding:4px;">👁️</button>
                        </div>
                        <button type="button" onclick="window.saveGuardianOwnerSettings()" style="padding:11px 22px;border-radius:10px;border:none;background:linear-gradient(135deg,#059669,#047857);color:#fff;font-family:inherit;font-size:0.95rem;font-weight:900;cursor:pointer;">💾 حفظ وتأكيد الإعدادات</button>
                    </div>
                </div>
                <div style="background:${card};border:2px solid #7c3aed;border-radius:16px;padding:18px 20px;">
                    <h4 style="margin:0 0 8px;font-size:1.02rem;font-weight:900;color:${ink};">🧠 مفاتيح نماذج الذكاء الفائقة الخاصة (اختياري — يعمل المحرك السحابي المزدوج تلقائياً بدونها)</h4>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;">
                        <input id="mgv-cfg-gemini" type="password" dir="ltr" value="${d.gemini_key_plain || ''}" placeholder="Gemini 2.5 Pro Key (AIza...)" style="padding:10px 12px;border-radius:10px;border:1.8px solid ${brd};background:${bg};color:${ink};font-size:0.88rem;font-weight:800;">
                        <input id="mgv-cfg-openai" type="password" dir="ltr" value="${d.openai_key_plain || ''}" placeholder="OpenAI GPT-4o Key (sk-...)" style="padding:10px 12px;border-radius:10px;border:1.8px solid ${brd};background:${bg};color:${ink};font-size:0.88rem;font-weight:800;">
                        <input id="mgv-cfg-deepseek" type="password" dir="ltr" value="${d.deepseek_key_plain || ''}" placeholder="DeepSeek R1 Key (sk-...)" style="padding:10px 12px;border-radius:10px;border:1.8px solid ${brd};background:${bg};color:${ink};font-size:0.88rem;font-weight:800;">
                    </div>
                </div>
            </div>`;
        const fm = d.forensic_metrics || {}, aiRes = vaultState.ai_council || null;
        const aiCouncilHtml = `<div style="grid-column:1/-1;display:flex;flex-direction:column;gap:14px;">
            <div style="background:${card};border:2px solid #7c3aed;border-radius:16px;padding:16px 18px;display:flex;flex-direction:column;gap:10px;">
                <div style="font-size:1.05rem;font-weight:900;color:${ink};">🧠 مجلس الذكاء السيادي + التعلّم الاتحادي التدريجي وتطوير البنية (10/10)</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;font-size:0.83rem;font-weight:900;">
                    <span style="background:rgba(16,185,129,0.14);border:1.5px solid #059669;color:#059669;padding:4px 10px;border-radius:8px;">🌐 التعلّم الاتحادي: ${fm.federated_rounds || 0} جولة (${fm.federated_nodes || 1} عقدة • تسريب 0%)</span>
                    <span style="background:rgba(2,132,199,0.14);border:1.5px solid #0284c7;color:#0284c7;padding:4px 10px;border-radius:8px;">🏗️ تطوير البنية الذاتي: ${fm.structural_evolutions || 0} فهرس رقابي مسرّع</span>
                </div>
                <div style="font-size:0.88rem;font-weight:800;color:${sub};">المبيعات: ${fm.sales_count || 0} فاتورة • المشتريات: ${fm.purchase_count || 0} • فرق الأستاذ: ${fm.gl_diff || 0} • مخزون سالب: ${fm.negative_stock_items || 0} • تحت التكلفة: ${fm.below_cost_items || 0}</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input id="mgv-ai-q" type="text" placeholder="اسأل مجلس الذكاء السيادي أي سؤال جنائي أو مالي (أو اتركه فارغاً للتشخيص الشامل)..." style="flex:1;min-width:260px;padding:10px 14px;border-radius:10px;border:2px solid #7c3aed;background:${bg};color:${ink};font-family:inherit;font-size:0.94rem;font-weight:800;" onkeydown="if(event.key==='Enter')window.askGuardianAiCouncil()">
                    <button type="button" onclick="window.askGuardianAiCouncil()" style="padding:10px 18px;border-radius:10px;border:none;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;font-family:inherit;font-size:0.92rem;font-weight:900;cursor:pointer;">🚀 استشارة المجلس الفائق الآن</button>
                    <button type="button" onclick="window.runGuardianAtoZSim()" style="padding:10px 18px;border-radius:10px;border:none;background:linear-gradient(135deg,#059669,#047857);color:#fff;font-family:inherit;font-size:0.92rem;font-weight:900;cursor:pointer;">🧪 محاكاة عميل ومورد (أ-ي)</button>
                </div>
                <div id="mgv-ai-verdict-box" style="white-space:pre-wrap;line-height:1.75;font-size:0.95rem;font-weight:800;padding:14px;border-radius:12px;background:${bg};border:1.5px solid ${brd};color:${ink};">${aiRes ? ('🤖 النماذج المشاركة (' + (aiRes.models_consulted || []).join(' + ') + ' • ' + aiRes.elapsed_ms + 'ms):\n\n' + aiRes.verdict) : 'اضغط «استشارة المجلس الفائق الآن» لتشغيل Gemini Pro / GPT-4o / DeepSeek R1 بالتوازي على بيانات شركتك الحية.'}</div>
            </div></div>`;

        const tabBtn = (id, lbl, badge, activeClr) => `
            <button type="button" onclick="window.switchMgvTab('${id}')" style="padding:8px 14px;border-radius:11px;border:2px solid ${tab === id ? activeClr : brd};background:${tab === id ? activeClr : 'transparent'};color:${tab === id ? '#fff' : ink};font-family:inherit;font-size:0.88rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                <span>${lbl}</span>${badge !== null ? `<span style="background:rgba(0,0,0,0.28);padding:1px 8px;border-radius:999px;font-size:0.78rem;">${badge}</span>` : ''}
            </button>`;

        modal.innerHTML = `
            <div style="width:100%;max-width:1060px;max-height:92vh;display:flex;flex-direction:column;background:${bg};color:${ink};border:2.5px solid ${brd};border-radius:22px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,0.8);">
                <div style="padding:14px 20px;background:${card};border-bottom:2px solid ${brd};display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <span style="background:#e11d48;color:#fff;padding:4px 12px;border-radius:999px;font-weight:900;font-size:0.9rem;">🔴 معلق: ${d.pending_count}</span>
                        <span style="background:#059669;color:#fff;padding:4px 12px;border-radius:999px;font-weight:900;font-size:0.85rem;">📜 بالأرشيف: ${archiveItems.length}</span>
                        <h3 style="margin:0;font-size:1.18rem;font-weight:900;color:${ink};">🏛️ غرفة القيادة والسيطرة — درع السيادة للمجموعة المالكة</h3>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <button type="button" onclick="window.runGuardianLiveScan()" style="padding:8px 13px;border-radius:10px;border:none;background:#7c3aed;color:#fff;font-family:inherit;font-size:0.84rem;font-weight:900;cursor:pointer;">⚡ فحص وشفاء الآن</button>
                        <button type="button" onclick="window.sendGuardianToWhatsApp()" style="padding:8px 13px;border-radius:10px;border:none;background:#16a34a;color:#fff;font-family:inherit;font-size:0.84rem;font-weight:900;cursor:pointer;">📲 إرسال للواتساب (${slots.length}/4)</button>
                        <a href="system-self-audit.php" style="padding:8px 13px;border-radius:10px;background:#0284c7;color:#fff;text-decoration:none;font-size:0.84rem;font-weight:900;">📊 شهادة 360°</a>
                        <button type="button" onclick="window.lockGuardianVault()" style="padding:8px 13px;border-radius:10px;border:none;background:#e11d48;color:#fff;font-family:inherit;font-size:0.84rem;font-weight:900;cursor:pointer;">🔒 قفل</button>
                    </div>
                </div>
                <div style="padding:10px 20px;background:${card};border-bottom:2px solid #059669;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
                        ${tabBtn('audit', '🛡️ يحتاج موافقتك', auditPending.length, '#e11d48')}
                        ${tabBtn('ai', '🧠 المجلس الفائق (AI Max)', (d.forensic_anomalies || []).length, '#6d28d9')}
                        ${tabBtn('radar', '🌐 رادار التطور العالمي', radarPending.length, '#7c3aed')}
                        ${tabBtn('archive', '📜 الأرشيف (تم التنفيذ)', archiveItems.length, '#0284c7')}
                        ${tabBtn('settings', '🔐 الباسورد والمفاتيح', slots.length + '/4', '#d97706')}
                    </div>
                    <span style="font-size:0.82rem;font-weight:900;color:#059669;">🟢 التفكير العميق متعدد النماذج + الشفاء الآمن مفعلان</span>
                </div>
                <div style="padding:18px 20px;overflow-y:auto;display:${tab === 'settings' ? 'block' : 'grid'};grid-template-columns:repeat(auto-fit,minmax(420px,1fr));gap:14px;">
                    ${tab === 'settings' ? settingsHtml : (tab === 'ai' ? aiCouncilHtml : (tab === 'radar' ? renderCards(radarPending, false) : (tab === 'archive' ? renderCards(archiveItems, true) : renderCards(auditPending, false))))}
                </div>
            </div>`;
    }

    let contTimer = null;
    function syncContScan() {
        if (contTimer) clearInterval(contTimer);
        const on = localStorage.getItem('magaies_continuous_deep_scan') === '1';
        const b1 = document.getElementById('sa-cont-btn'), b2 = document.getElementById('mgv-cont-btn');
        if (b1) b1.textContent = on ? '🔄 الفحص العميق المتوازي المستمر: مفعل' : '⏸️ الفحص العميق المستمر: متوقف';
        if (b2) b2.textContent = on ? '🔄 مستمر: مفعل' : '⏸️ مستمر: متوقف';
        if (on) contTimer = setInterval(() => { if (!document.hidden) { fetchCount(); if (typeof window.runFullSelfAudit === 'function') window.runFullSelfAudit(false); } }, 60000);
    }
    window.toggleContinuousDeepScan = function() {
        const cur = localStorage.getItem('magaies_continuous_deep_scan') === '1';
        localStorage.setItem('magaies_continuous_deep_scan', cur ? '0' : '1');
        syncContScan();
    };

    const boot = () => {
        ensureSidebarBadgeUnderMobile();
        setTimeout(() => { if (!document.hidden) fetchCount(); }, 2200);
        syncContScan();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
    setInterval(() => { if (!document.hidden) fetchCount(); }, 300000);
})();
