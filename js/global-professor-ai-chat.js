(function () {
    'use strict';
    if (window._magaiesGlobalProfAiLoaded) return;
    window._magaiesGlobalProfAiLoaded = true;

    let voiceMuted = false;
    let recognition = null;
    let isListening = false;

    function speakArabic(text) {
        if (voiceMuted || !text) return;
        const clean = String(text).replace(/[*#_`~]/g, '').replace(/ج\.س/g, ' جنيه سوداني ').replace(/\$/g, ' دولار ');
        if (typeof window.speakMdExplanationPanelText === 'function') {
            window.speakMdExplanationPanelText(clean);
            return;
        }
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const u = new SpeechSynthesisUtterance(clean);
            u.lang = 'ar-SA';
            u.rate = 1.03;
            window.speechSynthesis.speak(u);
        }
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
        return { title, sal, pur, liq, prf, ar, ap, mkt };
    }

    function generateSmartAnswer(qRaw) {
        const q = String(qRaw || '').trim();
        const ctx = readLiveContext();
        if (/مطابق|غرف القياد|ميزاني/i.test(q)) {
            return 'بطاقة «غرف القيادة وميزانية المطابقة» تقارن لحظياً بين الرصيد الدفتري المسجل في النظام والرصيد الفعلي في الخزائن والبنوك والميزان، وتضم 4 تبويبات: (1) المطابقة لكشف أي فرق أو عجز فوري، (2) الدخل والربح لمعرفة صافي الربح بعد المصروفات، (3) الميزانية لعرض الأصول والالتزامات وحقوق الملكية، (4) تدقيق ذكي لفحص القيود آلياً.';
        }
        if (/ربح آمن|معدل الدوران|دوران/i.test(q)) {
            return 'مؤشر «ربح آمن» يعني أن جميع الفواتير محمية فوق سعر التكلفة وسعر الإحلال بدون أي فاتورة خاسرة. أما «معدل الدوران 2.4x» فيعني أن المخزون ورأس المال يدوران ويتجددان بالكامل 2.4 مرة شهرياً (دورة بيع وتجديد كل 12 يوماً تقريباً).';
        }
        if (/واتس|اشعار|إشعار|رصيد|249123005622/i.test(q)) {
            return 'منظومة الواتساب الذكية مربوطة على رقم الشركة (0123005622 / 249123005622): عند إرسال العميل لصورة إشعار بنكي يرد النظام فوراً بـ «تم استلام إشعار التحويل البنكي بنجاح وجاري تدقيقه الفوري»، وعند إرسال كلمة «رصيد» أو حرف «ر» أو «ك» يرسل كشف الرصيد التلقائي. تأكد من أن حالة الربط في نافذة الواتساب «متصل CONNECTED».';
        }
        if (/مبيع|ارباح|أرباح|سيول|ديون|مشتري|ملخص|حالة/i.test(q)) {
            if (ctx.sal) {
                return `ملخص المؤشرات الحية الآن: المبيعات ${ctx.sal}، المشتريات ${ctx.pur}، السيولة الجاهزة ${ctx.liq}، صافي الأرباح ${ctx.prf}، القيمة السوقية للمخزون ${ctx.mkt || 'محدثة'}، ديون العملاء ${ctx.ar}، ومستحقات الموردين ${ctx.ap}.`;
            }
            return `أنت الآن في شاشة «${ctx.title}». يمكنك سؤال البروفيسور عن أي زر أو حقل أو الانتقال للوحة القيادة لعرض المؤشرات العشرة الحية.`;
        }
        if (/ذكا|مركز|حارس|رادار/i.test(q)) {
            return 'مركز الذكاء الاصطناعي يعمل كمحرك رقابي وتحليلي فوري على الكمبيوتر والموبايل: يراقب الأسعار وهوامش الربح، يتنبأ بنفاد الأصناف قبل 7 أيام، يدقق الإشعارات البنكية عبر الواتساب، ويجيب على استفساراتك صوتياً وكتابياً في جميع شاشات البرنامج.';
        }
        if (ctx.sal) {
            return `بناءً على بيانات «${ctx.title}» الحالية: المبيعات ${ctx.sal}، صافي الربح ${ctx.prf}، السيولة ${ctx.liq}، وديون العملاء ${ctx.ar}. يمكنك سؤالي عن (غرف القيادة والمطابقة، الأصناف الأكثر مبيعاً، التنبؤ الذكي، أو ربط إشعارات الواتساب).`;
        }
        return `مرحباً بك في دردشة البروفيسور الذكي لشاشة «${ctx.title}» (يعمل على الكمبيوتر والموبايل بالصوت والكتابة). اسألني عن أي بطاقة أو مؤشر مالي أو طريقة عمل أي ميزة في النظام وسأشرحها لك فوراً.`;
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

    function sendUserQuery(customText) {
        const inp = document.getElementById('prof-ai-chat-input');
        const q = (customText !== undefined ? customText : (inp ? inp.value : '')).trim();
        if (!q) return;
        if (inp && customText === undefined) inp.value = '';
        appendMsg('user', q);
        setTimeout(() => {
            const ans = generateSmartAnswer(q);
            appendMsg('ai', ans);
            speakArabic(ans);
        }, 180);
    }

    function toggleVoiceInput() {
        const micBtn = document.getElementById('prof-ai-mic-btn');
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) {
            alert('المتصفح الحالي لا يدعم الإملاء الصوتي المباشر، يمكنك الكتابة في مربع الدردشة.');
            return;
        }
        if (isListening && recognition) {
            recognition.stop();
            return;
        }
        recognition = new SR();
        recognition.lang = 'ar-SA';
        recognition.interimResults = false;
        recognition.onstart = () => {
            isListening = true;
            if (micBtn) { micBtn.innerHTML = '🔴 جاري الاستماع...'; micBtn.style.background = '#e11d48'; }
        };
        recognition.onresult = (e) => {
            const transcript = e.results?.[0]?.[0]?.transcript || '';
            if (transcript) sendUserQuery(transcript);
        };
        recognition.onend = () => {
            isListening = false;
            if (micBtn) { micBtn.innerHTML = '🎤 تحدث بالصوت'; micBtn.style.background = 'linear-gradient(135deg,#059669,#047857)'; }
        };
        recognition.onerror = () => {
            isListening = false;
            if (micBtn) { micBtn.innerHTML = '🎤 تحدث بالصوت'; micBtn.style.background = 'linear-gradient(135deg,#059669,#047857)'; }
        };
        recognition.start();
    }

    window.openGlobalProfessorAiChatModal = function (initialQuestion) {
        let modal = document.getElementById('global-prof-ai-chat-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'global-prof-ai-chat-modal';
            modal.style.cssText = 'position:fixed;inset:0;z-index:2147483600;background:rgba(2,6,23,0.78);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;padding:12px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
            modal.innerHTML = `
                <div style="width:100%;max-width:620px;max-height:90vh;background:linear-gradient(165deg,#0f172a 0%,#020617 100%);border:2px solid #38bdf8;border-radius:22px;box-shadow:0 24px 60px rgba(0,0,0,0.85),0 0 30px rgba(56,189,248,0.3);display:flex;flex-direction:column;overflow:hidden;">
                    <div style="padding:14px 18px;background:linear-gradient(90deg,#0284c7,#059669);display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:24px;">🎓</span>
                            <div>
                                <div style="font-size:16.5px;font-weight:900;color:#fff;">مركز دردشة البروفيسور والذكاء الاصطناعي</div>
                                <div style="font-size:11.5px;font-weight:700;color:#e0f2fe;">حوار تفاعلي بالصوت والكتابة • متوافق مع الموبايل والكمبيوتر</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <button id="prof-ai-mute-btn" type="button" style="padding:6px 11px;border-radius:10px;background:rgba(15,23,42,0.55);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:12px;font-weight:900;cursor:pointer;">🔊 الصوت مفعل</button>
                            <button id="prof-ai-close-btn" type="button" style="width:34px;height:34px;border-radius:10px;background:rgba(225,29,72,0.85);color:#fff;border:none;font-size:16px;font-weight:900;cursor:pointer;">✕</button>
                        </div>
                    </div>
                    <div style="padding:8px 12px;background:rgba(15,23,42,0.9);border-bottom:1px solid rgba(148,163,184,0.2);display:flex;flex-wrap:wrap;gap:6px;">
                        <button type="button" class="prof-quick-q" data-q="اشرح لي غرف القيادة وميزانية المطابقة" style="padding:5px 10px;border-radius:99px;background:#1e293b;color:#38bdf8;border:1px solid #38bdf8;font-size:12px;font-weight:800;cursor:pointer;">⚖️ شرح غرف القيادة والمطابقة</button>
                        <button type="button" class="prof-quick-q" data-q="ملخص حالة المبيعات والأرباح والسيولة الآن" style="padding:5px 10px;border-radius:99px;background:#1e293b;color:#34d399;border:1px solid #34d399;font-size:12px;font-weight:800;cursor:pointer;">📊 ملخص المبيعات والسيولة</button>
                        <button type="button" class="prof-quick-q" data-q="اشرح مركز الذكاء الاصطناعي" style="padding:5px 10px;border-radius:99px;background:#1e293b;color:#fde047;border:1px solid #fde047;font-size:12px;font-weight:800;cursor:pointer;">🤖 شرح مركز الذكاء الاصطناعي</button>
                        <button type="button" class="prof-quick-q" data-q="حالة ربط الواتساب والرد الآلي للإشعارات وكلمة رصيد" style="padding:5px 10px;border-radius:99px;background:#1e293b;color:#c084fc;border:1px solid #c084fc;font-size:12px;font-weight:800;cursor:pointer;">💬 الواتساب وإشعارات الرصيد</button>
                    </div>
                    <div id="prof-ai-chat-messages" style="flex:1;min-height:250px;max-height:52vh;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;"></div>
                    <div style="padding:12px;background:#090f1d;border-top:1px solid rgba(148,163,184,0.25);display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                        <input id="prof-ai-chat-input" type="text" placeholder="اكتب سؤالك هنا أو اضغط 🎤 للتحدث بالصوت..." style="flex:1;min-width:180px;height:42px;padding:0 14px;border-radius:12px;background:#0f172a;color:#fff;border:1.5px solid #38bdf8;font-size:14px;font-weight:800;outline:none;" />
                        <button id="prof-ai-send-btn" type="button" style="height:42px;padding:0 16px;border-radius:12px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;font-size:14px;font-weight:900;cursor:pointer;">إرسال 🚀</button>
                        <button id="prof-ai-mic-btn" type="button" style="height:42px;padding:0 14px;border-radius:12px;background:linear-gradient(135deg,#059669,#047857);color:#fff;border:none;font-size:13.5px;font-weight:900;cursor:pointer;">🎤 تحدث بالصوت</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.getElementById('prof-ai-close-btn').onclick = () => {
                if ('speechSynthesis' in window) window.speechSynthesis.cancel();
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
            modal.querySelectorAll('.prof-quick-q').forEach(btn => {
                btn.onclick = () => sendUserQuery(btn.getAttribute('data-q'));
            });
            appendMsg('ai', 'مرحباً بك! أنا البروفيسور الذكي لنظام السجانة (متاح على الكمبيوتر والموبايل). يمكنك التحدث معي بالصوت 🎤 أو الكتابة لشرح أي بطاقة أو شاشة أو مؤشر مالي.');
        }
        modal.style.display = 'flex';
        if (initialQuestion) sendUserQuery(initialQuestion);
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('global-prof-ai-fab')) {
            const fab = document.createElement('button');
            fab.id = 'global-prof-ai-fab';
            fab.type = 'button';
            fab.title = 'دردشة البروفيسور والذكاء الاصطناعي (صوت وكتابة)';
            fab.innerHTML = '🤖';
            fab.style.cssText = 'position:fixed;bottom:82px;left:14px;z-index:99995;width:44px;height:44px;padding:0;border-radius:50%;background:linear-gradient(135deg,#059669,#0284c7);color:#ffffff;border:2px solid #fde047;font-size:22px;line-height:1;cursor:pointer;box-shadow:0 8px 22px rgba(0,0,0,0.65);display:inline-flex;align-items:center;justify-content:center;';
            fab.onclick = () => window.openGlobalProfessorAiChatModal();
            document.body.appendChild(fab);
        }
    });
})();
