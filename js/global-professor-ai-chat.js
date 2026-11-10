(function () {
    'use strict';
    if (window._magaiesGlobalProfAiLoaded) return;
    window._magaiesGlobalProfAiLoaded = true;

    let voiceMuted = false;
    let recognition = null;
    let isListening = false;
    let liveOpenConversation = false;
    let isSpeakingReply = false;

    function updateMicUiState(state) {
        const micBtn = document.getElementById('prof-ai-mic-btn');
        const barBtn = document.getElementById('exec-live-mic-btn');
        const hdrBtn = document.getElementById('header-live-mic-btn');
        const fabBtn = document.getElementById('global-prof-ai-fab');
        const badge = document.getElementById('prof-ai-live-status');
        if (!liveOpenConversation) {
            if (micBtn) { micBtn.className = ''; micBtn.innerHTML = '🎤 مايك الحوار المفتوح'; micBtn.style.background = 'linear-gradient(135deg,#059669,#047857)'; }
            if (barBtn) { barBtn.classList.remove('prof-mic-illuminated-active', 'prof-mic-illuminated-speaking'); barBtn.innerHTML = '<span>🎙️</span><span>مايك البروفيسور</span>'; }
            if (hdrBtn) { hdrBtn.classList.remove('prof-mic-illuminated-active', 'prof-mic-illuminated-speaking'); hdrBtn.innerHTML = '<span style="font-size:15px;line-height:1;">🎙️</span><span style="font-size:11.5px;font-weight:900;color:#ffffff;">الحوار</span>'; }
            if (fabBtn) { fabBtn.classList.remove('prof-mic-illuminated-active', 'prof-mic-illuminated-speaking'); fabBtn.innerHTML = '🎙️'; }
            if (badge) badge.style.display = 'none';
            return;
        }
        if (badge) badge.style.display = 'inline-flex';
        if (state === 'speaking') {
            if (micBtn) { micBtn.className = 'prof-mic-illuminated-speaking'; micBtn.innerHTML = '🔊 يتحدث الآن... (اضغط للإيقاف)'; }
            if (barBtn) { barBtn.classList.remove('prof-mic-illuminated-active'); barBtn.classList.add('prof-mic-illuminated-speaking'); barBtn.innerHTML = '<span>🔊</span><span>يتحدث معك...</span>'; }
            if (hdrBtn) { hdrBtn.classList.remove('prof-mic-illuminated-active'); hdrBtn.classList.add('prof-mic-illuminated-speaking'); hdrBtn.innerHTML = '<span style="font-size:15px;line-height:1;">🔊</span><span style="font-size:11.5px;font-weight:900;color:#ffffff;">يتحدث...</span>'; }
            if (fabBtn) { fabBtn.classList.remove('prof-mic-illuminated-active'); fabBtn.classList.add('prof-mic-illuminated-speaking'); fabBtn.innerHTML = '🔊'; }
            if (badge) badge.innerHTML = '🟡 البروفيسور يجيب الآن... سيعود الاستماع تلقائياً';
        } else {
            if (micBtn) { micBtn.className = 'prof-mic-illuminated-active'; micBtn.innerHTML = '🎙️ المايك مضاء (دردشة مفتوحة مستمرة)'; }
            if (barBtn) { barBtn.classList.remove('prof-mic-illuminated-speaking'); barBtn.classList.add('prof-mic-illuminated-active'); barBtn.innerHTML = '<span>🎙️</span><span>المايك مضاء (تحدث)</span>'; }
            if (hdrBtn) { hdrBtn.classList.remove('prof-mic-illuminated-speaking'); hdrBtn.classList.add('prof-mic-illuminated-active'); hdrBtn.innerHTML = '<span style="font-size:15px;line-height:1;">🎙️</span><span style="font-size:11.5px;font-weight:900;color:#ffffff;">مضاء</span>'; }
            if (fabBtn) { fabBtn.classList.remove('prof-mic-illuminated-speaking'); fabBtn.classList.add('prof-mic-illuminated-active'); fabBtn.innerHTML = '🎙️'; }
            if (badge) badge.innerHTML = '🟢 المايك مضاء ومفتوح — تحدث بحرية في أي وقت';
        }
    }

    let currentProfAudio = null;
    function interruptSpeakingOnly() {
        isSpeakingReply = false;
        if (currentProfAudio) {
            try { currentProfAudio.onended = null; currentProfAudio.onerror = null; currentProfAudio.pause(); currentProfAudio.currentTime = 0; } catch (e) {}
            currentProfAudio = null;
        }
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
        if (liveOpenConversation) updateMicUiState('listening');
    }

    function speakArabic(text, onDone) {
        const finish = () => {
            isSpeakingReply = false;
            if (typeof onDone === 'function') onDone();
            if (liveOpenConversation) {
                updateMicUiState('listening');
                setTimeout(() => startContinuousRecognition(), 70);
            }
        };
        if (voiceMuted || !text) { finish(); return; }
        interruptSpeakingOnly();
        if (recognition) {
            try { recognition.onend = null; recognition.abort(); } catch (e) {}
            recognition = null;
            isListening = false;
        }
        isSpeakingReply = true;
        updateMicUiState('speaking');
        const clean = String(text).replace(/[*#_`~]/g, '').replace(/ج\.س/g, ' جنيه ').replace(/\$/g, ' دولار ').slice(0, 600).trim();
        const chunks = clean.split(/(?<=[.؟!؛])\s+/).map(s => s.trim()).filter(Boolean);
        if (!chunks.length) { finish(); return; }
        const urls = chunks.map(c => 'api/tts_male.php?dialect=warm_professor&text=' + encodeURIComponent(c));
        // Pre-warm all subsequent sentence chunks in parallel for zero-latency human breathing flow
        urls.slice(1).forEach(u => { fetch(u).catch(() => {}); });
        let idx = 0;
        const playNext = () => {
            if (!isSpeakingReply || idx >= urls.length) { finish(); return; }
            const aud = new Audio(urls[idx]);
            aud.volume = 1.0;
            currentProfAudio = aud;
            idx++;
            aud.onended = () => { if (isSpeakingReply) playNext(); };
            aud.onerror = () => { if (isSpeakingReply) playNext(); else finish(); };
            aud.play().catch(() => finish());
        };
        playNext();
    }

    function readLiveContext() {
        const title = document.title || 'نظام السجانة ERP';
        const sal = (document.getElementById('strip-sal-amt')?.innerText || '').trim();
        const pur = (document.getElementById('strip-pur-amt')?.innerText || '').trim();
        const liq = (document.getElementById('strip-liq-amt')?.innerText || '').trim();
        const prf = (document.getElementById('strip-prf-amt')?.innerText || '').trim();
        const ar = (document.getElementById('strip-ar-amt')?.innerText || '').trim();
        const ap = (document.getElementById('strip-ap-amt')?.innerText || '').trim();
        const mkt = (document.getElementById('stock-mkt-val')?.innerText || '').trim();
        const liveElements = [];
        document.querySelectorAll('button, a, [data-explain], [title], h1, h2, h3, th, label').forEach(el => {
            if (liveElements.length >= 65 || !el.offsetParent) return;
            const txt = (el.innerText || el.getAttribute('aria-label') || '').replace(/\s+/g, ' ').trim();
            const hint = (el.getAttribute('data-explain') || el.getAttribute('title') || '').replace(/\s+/g, ' ').trim();
            if (txt && txt.length >= 2 && txt.length <= 60) {
                liveElements.push(hint ? `${txt} (${hint})` : txt);
            }
        });
        return { title, page: location.pathname.split('/').pop() || 'index.php', sal, pur, liq, prf, ar, ap, mkt, live_dom_elements: Array.from(new Set(liveElements)).slice(0, 50) };
    }

    const chatHistory = [];
    function generateSmartAnswer(qRaw) {
        const q = String(qRaw || '').trim();
        const ctx = readLiveContext();
        if (/مبيعات|بيع/i.test(q)) return `إجمالي المبيعات الحالي ${ctx.sal || 'محدث أمامك'} وصافي الأرباح ${ctx.prf || 'محدث'}؛ هل تود مراجعة تفاصيل الفواتير أم تحصيل ديون العملاء البالغة ${ctx.ar || 'تحت التحديث'}؟`;
        if (/مشتريات|شراء|مورد/i.test(q)) return `إجمالي المشتريات الحالي ${ctx.pur || 'محدث أمامك'} ومستحقات الموردين ${ctx.ap || 'محدثة'}؛ هل نراجع فواتير التوريد أم جدولة سداد الموردين؟`;
        if (/شريط الذهب|الشريط الذهبي|شريط الاوامر|شريط الأوامر/i.test(q)) return `الشريط الذهبي هو مركز القيادة العلوي المثبت؛ يتيح لك فلترة الفترات الزمنية بلمسة واحدة، ومتابعة نبض السيولة المطمئنة، وتبديل العملة بين الجنيه والدولار فوراً.`;
        if (/بورص|عروض|طلبات/i.test(q)) return `شريط البورصة يعرض عروض البيع بالأخضر وطلبات الشراء بالبرتقالي لحظياً من الواتساب والذكاء البصري للصور، مما يمنحك أفضلية فورية في اقتناص أسعار السوق.`;
        if (/سيول|خزين|بنك|رصيد/i.test(q)) return `صافي السيولة المتاحة حالياً ${ctx.liq || 'محدث أمامك'} مقابل مستحقات موردين قدرها ${ctx.ap || 'محدثة'}، مما يعطيك رؤية واضحة لتدفقاتك النقدية.`;
        if (/رأي|راي|نصيح|تحليل/i.test(q)) return `من واقع المؤشرات الحية، أنصح بالتركيز على تحصيل ديون العملاء (${ctx.ar || 'الحالية'}) لتعزيز السيولة (${ctx.liq || 'المتاحة'}) واقتناص أفضل عروض البورصة.`;
        return `تحليلي السريع بخصوص «${q}»: مؤشرات النظام تعمل بتوازن ممتاز، والسيولة الحالية ${ctx.liq || 'جاهزة'} والمبيعات ${ctx.sal || 'نشطة'}؛ أي جانب منها تود أن نفصله أكثر؟`;
    }

    function appendMsg(role, text) {
        const box = document.getElementById('prof-ai-chat-messages');
        if (!box) return;
        const div = document.createElement('div');
        const isUser = role === 'user';
        div.style.cssText = `padding:10px 14px;border-radius:14px;max-width:88%;font-size:14.5px;font-weight:800;line-height:1.65;align-self:${isUser ? 'flex-start' : 'flex-end'};background:${isUser ? 'linear-gradient(135deg,#0284c7,#0369a1)' : 'rgba(15,23,42,0.95)'};color:#fff;border:1.5px solid ${isUser ? '#38bdf8' : 'rgba(16,185,129,0.55)'};box-shadow:0 4px 14px rgba(0,0,0,0.35);`;
        div.innerHTML = `<div style="font-size:11.5px;color:${isUser ? '#bae6fd' : '#34d399'};margin-bottom:3px;font-weight:900;">${isUser ? '👤 أنت' : '🎓 البروفيسور الذكي'}</div><div>${text}</div>`;
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }

    let activeQueryCtrl = null;
    async function sendUserQuery(customText) {
        const inp = document.getElementById('prof-ai-chat-input');
        const q = (customText !== undefined ? customText : (inp ? inp.value : '')).trim();
        if (!q) return;
        interruptSpeakingOnly();
        if (inp && customText === undefined) inp.value = '';
        appendMsg('user', q);
        chatHistory.push({ role: 'user', text: q });
        if (chatHistory.length > 8) chatHistory.shift();
        let ans = '';
        try {
            const ctx = readLiveContext();
            ctx.recent_dialogue = chatHistory.slice(-6);
            if (activeQueryCtrl) { try { activeQueryCtrl.abort(); } catch (e) {} }
            activeQueryCtrl = new AbortController();
            const tid = setTimeout(() => activeQueryCtrl.abort(), 14000);
            const res = await fetch('api/ai-assistant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: q, context: JSON.stringify(ctx), screen_context: ctx.title }),
                signal: activeQueryCtrl.signal
            }).then(r => r.json());
            clearTimeout(tid);
            if (res && res.reply) ans = String(res.reply).replace(/\*\*/g, '');
        } catch (e) {}
        if (!liveOpenConversation && customText !== undefined) return;
        if (!ans) ans = generateSmartAnswer(q);
        chatHistory.push({ role: 'ai', text: ans });
        appendMsg('ai', ans);
        speakArabic(ans);
    }

    let interimTimer = null;
    let lastInterimText = '';
    function startContinuousRecognition() {
        if (!liveOpenConversation) return;
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) return;
        if (isListening && recognition) return;
        recognition = new SR();
        recognition.lang = 'ar-SA';
        recognition.interimResults = true;
        recognition.continuous = false;
        recognition.maxAlternatives = 5;
        recognition.onstart = () => {
            isListening = true;
            lastInterimText = '';
            if (!isSpeakingReply) updateMicUiState('listening');
        };
        recognition.onresult = (e) => {
            if (!liveOpenConversation) return;
            if (interimTimer) { clearTimeout(interimTimer); interimTimer = null; }
            let finalTranscript = '';
            let interimTranscript = '';
            for (let i = e.resultIndex; i < e.results.length; i++) {
                const seg = (e.results[i]?.[0]?.transcript || '').trim();
                if (seg.length >= 1 && isSpeakingReply) interruptSpeakingOnly();
                if (e.results[i].isFinal) finalTranscript += ' ' + seg;
                else interimTranscript += ' ' + seg;
            }
            finalTranscript = finalTranscript.trim();
            interimTranscript = interimTranscript.trim();
            if (finalTranscript) {
                lastInterimText = '';
                sendUserQuery(finalTranscript);
            } else if (interimTranscript.length >= 1) {
                lastInterimText = interimTranscript;
                interimTimer = setTimeout(() => {
                    if (lastInterimText && liveOpenConversation) {
                        const toSend = lastInterimText;
                        lastInterimText = '';
                        try { recognition.stop(); } catch (err) {}
                        sendUserQuery(toSend);
                    }
                }, 1100);
            }
        };
        recognition.onend = () => {
            isListening = false;
            if (liveOpenConversation) {
                setTimeout(() => startContinuousRecognition(), 60);
            } else {
                updateMicUiState('off');
            }
        };
        recognition.onerror = () => {
            isListening = false;
            if (liveOpenConversation) {
                setTimeout(() => startContinuousRecognition(), 180);
            }
        };
        try { recognition.start(); } catch (e) {}
    }

    function stopAllVoiceImmediately() {
        liveOpenConversation = false;
        isSpeakingReply = false;
        isListening = false;
        if (activeQueryCtrl) { try { activeQueryCtrl.abort(); } catch (e) {} activeQueryCtrl = null; }
        if (recognition) { try { recognition.onend = null; recognition.abort(); } catch (e) {} recognition = null; }
        if (currentProfAudio) { try { currentProfAudio.onended = null; currentProfAudio.onerror = null; currentProfAudio.pause(); currentProfAudio.currentTime = 0; } catch (e) {} currentProfAudio = null; }
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
        if (typeof window.stopMdExplanationPanelAudio === 'function') window.stopMdExplanationPanelAudio();
        updateMicUiState('off');
    }

    function toggleVoiceInput() {
        if (isSpeakingReply) {
            interruptSpeakingOnly();
            startContinuousRecognition();
            return;
        }
        if (liveOpenConversation || isListening) {
            stopAllVoiceImmediately();
            return;
        }
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) {
            alert('المتصفح الحالي لا يدعم الإملاء الصوتي المباشر، يمكنك الكتابة في مربع الدردشة.');
            return;
        }
        liveOpenConversation = true;
        updateMicUiState('listening');
        speakArabic('اللهم صلِّ على سيدنا محمد، تفضل يا باشمهندس أنا معك.', () => startContinuousRecognition());
    }

    window.openGlobalProfessorAiChatModal = function (initialQuestion) {
        let modal = document.getElementById('global-prof-ai-chat-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'global-prof-ai-chat-modal';
            modal.style.cssText = 'position:fixed;inset:0;z-index:2147483600;background:rgba(2,6,23,0.78);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;padding:12px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
            modal.innerHTML = `
                <style>
                    @keyframes profMicGlow { 0%, 100% { box-shadow: 0 0 18px #10b981, 0 0 34px rgba(253,224,71,0.7), inset 0 0 10px rgba(255,255,255,0.4); border-color: #fde047 !important; } 50% { box-shadow: 0 0 28px #34d399, 0 0 46px rgba(16,185,129,0.95), inset 0 0 14px rgba(253,224,71,0.6); border-color: #ffffff !important; } }
                    .prof-mic-illuminated-active { background: linear-gradient(135deg, #059669, #10b981, #047857) !important; border: 2px solid #fde047 !important; animation: profMicGlow 1.5s infinite ease-in-out !important; color: #ffffff !important; }
                    .prof-mic-illuminated-speaking { background: linear-gradient(135deg, #d97706, #b45309) !important; border: 2px solid #fef08a !important; box-shadow: 0 0 22px rgba(245,158,11,0.85) !important; color: #ffffff !important; }
                </style>
                <div style="width:100%;max-width:620px;max-height:90vh;background:linear-gradient(165deg,#0f172a 0%,#020617 100%);border:2px solid #38bdf8;border-radius:22px;box-shadow:0 24px 60px rgba(0,0,0,0.85),0 0 30px rgba(56,189,248,0.3);display:flex;flex-direction:column;overflow:hidden;">
                    <div style="padding:14px 18px;background:linear-gradient(90deg,#0284c7,#059669);display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:24px;">🎓</span>
                            <div>
                                <div style="font-size:16.5px;font-weight:900;color:#fff;">مركز دردشة البروفيسور والذكاء الاصطناعي</div>
                                <div style="font-size:11.5px;font-weight:700;color:#e0f2fe;">حوار صوتي مفتوح ومستمر بنبرة دافئة وواضحة • كمبيوتر وموبايل</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span id="prof-ai-live-status" style="display:none;padding:5px 10px;border-radius:99px;background:rgba(2,6,23,0.75);color:#fde047;border:1px solid #fde047;font-size:11px;font-weight:900;">🟢 المايك مضاء ومفتوح</span>
                            <button id="prof-ai-mute-btn" type="button" style="padding:6px 11px;border-radius:10px;background:rgba(15,23,42,0.55);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:12px;font-weight:900;cursor:pointer;">🔊 الصوت الدافئ مفعل</button>
                            <button id="prof-ai-close-btn" type="button" style="width:34px;height:34px;border-radius:10px;background:rgba(225,29,72,0.85);color:#fff;border:none;font-size:16px;font-weight:900;cursor:pointer;">✕</button>
                        </div>
                    </div>
                    <div style="padding:14px;background:rgba(15,23,42,0.95);border-bottom:1px solid rgba(56,189,248,0.25);display:flex;align-items:center;justify-content:center;gap:14px;">
                        <button id="prof-ai-mic-btn" type="button" class="prof-mic-illuminated-active" style="height:52px;padding:0 24px;border-radius:999px;background:linear-gradient(135deg,#059669,#10b981,#047857);color:#fff;border:2px solid #fde047;font-size:15px;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:10px;transition:all 0.25s ease;">🎙️ المايك مضاء (دردشة مفتوحة مستمرة)</button>
                    </div>
                    <div id="prof-ai-chat-messages" style="flex:1;min-height:220px;max-height:48vh;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;"></div>
                    <div style="padding:12px;background:#090f1d;border-top:1px solid rgba(148,163,184,0.25);display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                        <input id="prof-ai-chat-input" type="text" placeholder="تحدث مباشرة في المايك المضيء أعلاه أو اكتب سؤالك هنا..." style="flex:1;min-width:180px;height:42px;padding:0 14px;border-radius:12px;background:#0f172a;color:#fff;border:1.5px solid #38bdf8;font-size:14px;font-weight:800;outline:none;" />
                        <button id="prof-ai-send-btn" type="button" style="height:42px;padding:0 16px;border-radius:12px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;font-size:14px;font-weight:900;cursor:pointer;">إرسال 🚀</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.getElementById('prof-ai-close-btn').onclick = () => {
                liveOpenConversation = false;
                isSpeakingReply = false;
                if (recognition) { try { recognition.abort(); } catch (e) {} }
                if (currentProfAudio) { try { currentProfAudio.pause(); } catch (e) {} currentProfAudio = null; }
                if ('speechSynthesis' in window) window.speechSynthesis.cancel();
                if (typeof window.stopMdExplanationPanelAudio === 'function') window.stopMdExplanationPanelAudio();
                updateMicUiState('off');
                modal.style.display = 'none';
            };
            document.getElementById('prof-ai-mute-btn').onclick = function () {
                voiceMuted = !voiceMuted;
                if (voiceMuted && 'speechSynthesis' in window) window.speechSynthesis.cancel();
                this.innerText = voiceMuted ? '🔇 الصوت مكتوم' : '🔊 الصوت مفعل';
            };
            document.getElementById('prof-ai-send-btn').onclick = () => sendUserQuery();
            document.getElementById('prof-ai-mic-btn').onclick = () => toggleVoiceInput();
            document.getElementById('prof-ai-chat-input').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') sendUserQuery();
            });
            appendMsg('ai', 'اللهم صلِّ وسلِّم على نبينا محمد ﷺ. المايك مضاء ومفتوح الآن — تحدث معي مباشرة بحرية.');
        }
        modal.style.display = 'flex';
        if (initialQuestion) {
            sendUserQuery(initialQuestion);
        } else if (!liveOpenConversation) {
            toggleVoiceInput();
        }
    };

    window.toggleDirectLiveVoiceMic = toggleVoiceInput;

    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('prof-mic-global-style')) {
            const st = document.createElement('style');
            st.id = 'prof-mic-global-style';
            st.textContent = `@keyframes profMicGlow { 0%, 100% { box-shadow: 0 0 18px #10b981, 0 0 34px rgba(253,224,71,0.7), inset 0 0 10px rgba(255,255,255,0.4) !important; border-color: #fde047 !important; } 50% { box-shadow: 0 0 28px #34d399, 0 0 46px rgba(16,185,129,0.95), inset 0 0 14px rgba(253,224,71,0.6) !important; border-color: #ffffff !important; } } .prof-mic-illuminated-active { background: linear-gradient(135deg, #059669, #10b981, #047857) !important; border: 2px solid #fde047 !important; animation: profMicGlow 1.5s infinite ease-in-out !important; color: #ffffff !important; } .prof-mic-illuminated-speaking { background: linear-gradient(135deg, #d97706, #b45309) !important; border: 2px solid #fef08a !important; box-shadow: 0 0 22px rgba(245,158,11,0.85) !important; color: #ffffff !important; }`;
            document.head.appendChild(st);
        }
        if (!document.getElementById('global-prof-ai-fab')) {
            const fab = document.createElement('button');
            fab.id = 'global-prof-ai-fab';
            fab.type = 'button';
            fab.title = 'مايك الحوار المباشر مع البروفيسور (اضغط ليضيء المايك وتحدث مباشرة)';
            fab.innerHTML = '🎙️';
            fab.style.cssText = 'position:fixed;bottom:82px;left:14px;z-index:99995;width:46px;height:46px;padding:0;border-radius:50%;background:linear-gradient(135deg,#059669,#0284c7);color:#ffffff;border:2px solid #fde047;font-size:22px;line-height:1;cursor:pointer;box-shadow:0 8px 22px rgba(0,0,0,0.65);display:inline-flex;align-items:center;justify-content:center;transition:all 0.25s ease;';
            fab.onclick = () => toggleVoiceInput();
            document.body.appendChild(fab);
        }
    });
})();
