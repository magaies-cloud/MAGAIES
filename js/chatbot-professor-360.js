// ============================================================================
// MAGAIEES ERP — PROFESSOR AI 360° GUARDIAN ENGINE (الحارس الأمين والمساعد الشامل)
// Automatically indexes every screen, button, field, table column, Smart Guide,
// Double-Right-Click card, and 4K Cinema Video Recorder now and in the future.
// ============================================================================
(function () {
    'use strict';

    window.__chatbotAutoSpeak = localStorage.getItem('magaies_chatbot_autospeak') !== '0';
    window.__chatbotLastIndexedElements = [];

    const MASTER_SCREENS_CATALOG = [
        { title: 'لوحة التحكم الرئيسية والبوصلة', url: 'magaiees_unified_dashboard.php', keywords: ['لوحة التحكم', 'الرئيسية', 'داشبورد', 'بوصلة'] },
        { title: 'فاتورة المشتريات الذكية', url: 'purchase.php', keywords: ['فاتورة مشتريات', 'شاشة المشتريات', 'شراء بضاعة'] },
        { title: 'استلام البضاعة المخزني (GRN)', url: 'purchase-receipt.php', keywords: ['استلام بضاعة', 'سند استلام', 'اذن استلام', 'grn'] },
        { title: 'مرتجع المشتريات', url: 'purchase-return.php', keywords: ['مرتجع مشتريات', 'رد بضاعة للمورد'] },
        { title: 'فاتورة المبيعات ونقطة البيع', url: 'sales.php', keywords: ['فاتورة مبيعات', 'شاشة المبيعات', 'مبيعات مباشرة'] },
        { title: 'الكاشير والمبيعات السريعة', url: 'direct-sales.php', keywords: ['كاشير', 'نقطة بيع', 'بيع مباشر'] },
        { title: 'اللوجستيات الذكية وتسليم البضاعة (GDN)', url: 'smart-logistics.php', keywords: ['لوجستيات', 'تسليم بضاعة', 'مذكرة تسليم', 'تجزئة الفواتير', 'gdn'] },
        { title: 'سند القبض والصرف الموحد', url: 'single-cash-payment.php', keywords: ['سند قبض', 'سند صرف', 'توريد نقدية', 'صرف نقدية'] },
        { title: 'السندات المتعددة والقيود المركبة', url: 'multi-cash-payment.php', keywords: ['سند متعدد', 'صرف متعدد', 'قبض متعدد'] },
        { title: 'إشعارات بنكك والتحصيلات المصرفية الواردة', url: 'inbound-Single-Receipt.php', keywords: ['اشعارات بنكك', 'بنكك', 'تحصيلات واردة', 'فحص الاشعارات'] },
        { title: 'البيانات الأساسية (العملاء، الموردين، الأصناف، المخازن، الخزائن)', url: 'master-data.php', keywords: ['البيانات الاساسية', 'تعريف صنف', 'تعريف عميل', 'تعريف مورد', 'تعريف مخزن', 'تعريف خزينة'] },
        { title: 'كشف الحساب التفصيلي والذكي', url: 'account-statement.php', keywords: ['كشف حساب', 'حركة حساب', 'كشف عميل', 'كشف مورد'] },
        { title: 'مركز الأرصدة والمديونيات الحديث', url: 'balances_modern_hub.php', keywords: ['مركز الارصدة', 'ارصدة العملاء', 'ارصدة الموردين', 'مديونيات'] },
        { title: 'حركة الأصناف وجرد المخزون', url: 'inventory-movement.php', keywords: ['حركة صنف', 'حركة المخزون', 'جرد المخزن', 'كارت صنف'] },
        { title: 'أعمار الديون والتحصيل', url: 'aging-report.php', keywords: ['اعمار الديون', 'تقادم الديون', 'الديون المتأخرة'] },
        { title: 'قائمة الأرباح والخسائر والدخل', url: 'pl-statement.php', keywords: ['ارباح وخسائر', 'قائمة الدخل', 'صافي الربح'] },
        { title: 'العقل الذكي الموحد (Unified Brain)', url: 'unified-brain.php', keywords: ['العقل الذكي', 'تحليل شامل', 'مراجع الحسابات'] },
        { title: 'الأدوات الهندسية وموسوعة الفتاوى', url: 'engineering_tools.php', keywords: ['ادوات هندسية', 'فتاوى', 'زكاة', 'اوزان الحديد', 'سويفت'] },
        { title: 'الإعدادات العامة والربط والواتساب', url: 'settings.php', keywords: ['الاعدادات', 'ربط الواتساب', 'اعدادات النظام'] }
    ];

    function cleanLabel(str) {
        return String(str || '').replace(/\s+/g, ' ').trim();
    }

    function getElementLabel(el) {
        if (!el) return '';
        let lbl = el.getAttribute('aria-label') || el.getAttribute('title') || el.getAttribute('data-md-tip') || el.getAttribute('placeholder') || '';
        if (!lbl && el.id) {
            const forLbl = document.querySelector(`label[for="${el.id}"]`);
            if (forLbl) lbl = forLbl.innerText || forLbl.textContent || '';
        }
        if (!lbl && el.closest) {
            const grp = el.closest('.form-group, .input-group, .h-form-group, .pro-field-card, td, th');
            if (grp) {
                const l = grp.querySelector('label, .field-label, .lbl');
                if (l && l !== el) lbl = l.innerText || l.textContent || '';
            }
        }
        if (!lbl && (el.tagName === 'BUTTON' || el.tagName === 'A' || el.tagName === 'TH' || el.classList.contains('fin-metric-pill') || el.classList.contains('md-tab-btn'))) {
            lbl = el.innerText || el.textContent || '';
        }
        return cleanLabel(lbl).slice(0, 70);
    }

    function getElementRichExplanation(el) {
        try {
            const raw = (typeof window.explainUnifiedDashboardDomElement === 'function' && window.explainUnifiedDashboardDomElement(el))
                || (typeof window.explainMultiCashPaymentDomElement === 'function' && window.explainMultiCashPaymentDomElement(el))
                || (typeof window.explainSmartLogisticsDomElement === 'function' && window.explainSmartLogisticsDomElement(el))
                || (typeof window.explainPurchaseReceiptDomElement === 'function' && window.explainPurchaseReceiptDomElement(el))
                || (typeof window.explainDirectSalesDomElement === 'function' && window.explainDirectSalesDomElement(el))
                || (typeof window.explainPurchaseDomElement === 'function' && window.explainPurchaseDomElement(el))
                || (typeof window.explainMasterDataDomElement === 'function' && window.explainMasterDataDomElement(el))
                || (typeof window.autoSynthesizeElementExplanation === 'function' && window.autoSynthesizeElementExplanation(el));
            if (raw) {
                const enriched = (typeof window.enrichProfessor6Pillars === 'function') ? window.enrichProfessor6Pillars(raw, el) : raw;
                return { title: cleanLabel(enriched.title || getElementLabel(el)), desc: cleanLabel(enriched.desc || '') };
            }
        } catch (_) {}
        const fallbackTitle = getElementLabel(el) || el.id || el.name || el.tagName;
        return {
            title: fallbackTitle,
            desc: `عنصر تفاعلي (${el.tagName}) في شاشة «${document.title || location.pathname}». اضغط دبل كليك يمين عليه لسماع شرحه التفصيلي بالصوت.`
        };
    }

    // Live auto-scanner of all buttons, inputs, selects, tabs, table headers, and links on the current screen
    window.scanLiveScreenUniverse = function () {
        const selector = 'button, input:not([type="hidden"]), select, textarea, th, .fin-metric-pill, .md-tab-btn, .qa-master-btn, .hub-action-card, [data-col-id], a[href$=".php"]';
        const nodes = Array.from(document.querySelectorAll(selector)).filter(el => {
            if (el.closest('#magaees-chatbot-container, #msg-smart-hover-tooltip, #msg-universal-modal, #ai-hub-menu')) return false;
            const r = el.getBoundingClientRect();
            return r.width > 12 && r.height > 10;
        });
        const items = [];
        const seen = new Set();
        nodes.forEach((el) => {
            const label = getElementLabel(el);
            if (!label || label.length < 2 || seen.has(label)) return;
            seen.add(label);
            let kind = 'عنصر';
            if (el.tagName === 'BUTTON' || el.getAttribute('role') === 'button') kind = 'زر';
            else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') kind = 'خانة إدخال';
            else if (el.tagName === 'SELECT') kind = 'قائمة اختيار';
            else if (el.tagName === 'TH' || el.hasAttribute('data-col-id')) kind = 'عمود جدول';
            else if (el.tagName === 'A') kind = 'رابط شاشة';
            let val = '';
            try {
                if (el.tagName === 'SELECT' && el.options && el.selectedIndex >= 0) val = cleanLabel(el.options[el.selectedIndex].text);
                else if ((el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') && el.type !== 'password') val = cleanLabel(el.value);
            } catch (_) {}
            items.push({ el, label, kind, val });
        });
        window.__chatbotLastIndexedElements = items;
        return items;
    };

    window.highlightAndExplainChatElement = function (idx) {
        const item = window.__chatbotLastIndexedElements[idx];
        if (!item || !item.el) return;
        try {
            item.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof window.showUnifiedDoubleRightClickCard === 'function') {
                window.showUnifiedDoubleRightClickCard({ type: 'dblclick', target: item.el, preventDefault() {}, stopPropagation() {} }, item.el);
            } else {
                item.el.style.outline = '3px solid #f59e0b';
                setTimeout(() => { item.el.style.outline = ''; }, 3000);
            }
        } catch (_) {}
    };

    window.speakChatbotReply = function (htmlText) {
        if (!window.__chatbotAutoSpeak) return;
        const plain = String(htmlText || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        if (!plain) return;
        if (typeof window.speakMdExplanationPanelText === 'function') {
            window.speakMdExplanationPanelText(plain.slice(0, 650));
        }
    };

    // Build concise summary of current screen + Smart Guide + live elements for AI context
    window.buildLiveScreenAiContext = function () {
        const items = window.scanLiveScreenUniverse();
        const pageTitle = document.title || location.pathname;
        const btns = items.filter(x => x.kind === 'زر').slice(0, 25).map(x => x.label).join('، ');
        const fields = items.filter(x => x.kind === 'خانة إدخال' || x.kind === 'قائمة اختيار').slice(0, 25).map(x => x.label + (x.val ? ` (${x.val})` : '')).join('، ');
        const cols = items.filter(x => x.kind === 'عمود جدول').slice(0, 20).map(x => x.label).join('، ');
        let guideInfo = '';
        if (window.MagaiesSmartGuides) {
            const keys = Object.keys(window.MagaiesSmartGuides);
            const activeGuide = window.MagaiesSmartGuides['universal-screen'] || window.MagaiesSmartGuides[keys[0]];
            if (activeGuide && activeGuide.title) {
                const stepsTxt = (activeGuide.autopilotSteps || []).slice(0, 8).map(s => s.badge || s.cursorLabel || '').filter(Boolean).join(' -> ');
                const dictTxt = (activeGuide.buttonsDict || []).slice(0, 10).map(b => `${b.label}: ${b.whatItDoes}`).join(' ؛ ');
                guideInfo = `المرشد الذكي للشاشة: ${activeGuide.title} (${activeGuide.subtitle || ''}) | خطوات المرشد: ${stepsTxt} | قاموس الأزرار: ${dictTxt}`;
            }
        }
        return [
            `الشاشة المفتوحة الآن: ${pageTitle} (${location.pathname})`,
            `دستور الشرح المعتمد: 14 ركناً (الهوية، الأهمية، تفكيك الرقم الحي، المصدر، المصب والتقارير، الأثر المحاسبي، المحاسن، المخاطر، الضوابط، مثال عملي، خطوات المبتدئ، الصلاحيات، التصحيح الآمن، النقطة الذهبية)`,
            guideInfo,
            btns ? `الأزرار الظاهرة بالشاشة: ${btns}` : '',
            fields ? `الخانات والحقول الظاهرة: ${fields}` : '',
            cols ? `أعمدة الجدول: ${cols}` : ''
        ].filter(Boolean).join(' | ');
    };

    // Intercept queries about current screen, buttons, fields, Smart Guide, Double-Right-Click, Video Recording, or ERP Screens
    window.tryHandleProfessor360Query = function (query) {
        const q = String(query || '').trim();
        const ql = q.toLowerCase();
        const items = window.scanLiveScreenUniverse();

        // 1. Video / Cinema 4K Recording questions
        if (/(تسجيل|فيديو|فيديوهات|تصوير|سينما|4k|ميديا|يوتيوب)/i.test(q)) {
            return {
                html: `🎬 <b>منظومة التسجيل السينمائي والفيديوهات (Cinema 4K):</b><br>
                1️⃣ يمكنك تسجيل فيديو احترافي 4K للشاشة بالكامل مع حركة المؤشر الليزري والشرح الصوتي الرجالي الموحد بضغطة واحدة.<br>
                2️⃣ يمكنك أيضاً تصوير أي زر أو خانة مفردة (Micro-Cinema) من داخل بطاقة الدبل كليك يمين بالضغط على زر <b>🎬 تصوير</b>.<br>
                3️⃣ بعد انتهاء الشرح، يقوم المتصفح بحفظ ملف الفيديو تلقائياً بجودة قياسية جاهزة للمشاركة مع العملاء.`,
                btnHtml: `<div class="chat-options" style="margin-top:8px;">
                    <button type="button" class="chat-action-btn" onclick="if(window.startAutomaticCinema4KMediaRecorder)window.startAutomaticCinema4KMediaRecorder();">🎬 بدء التسجيل السينمائي 4K الآن</button>
                    <button type="button" class="chat-action-btn" onclick="if(window.openSmartGuide)window.openSmartGuide();">🎓 فتح استوديو المرشد الذكي</button>
                </div>`
            };
        }

        // 2. Double-Right-Click & Smart Guide questions
        if (/(دبل كليك|كليك يمين|المرشد الذكي|المرشد|الممثل الالي|الممثل الآلي|اشرح لي كيف يعمل)/i.test(q)) {
            return {
                html: `🎓 <b>منظومة المرشد الذكي والدبل كليك يمين (Professor 360°):</b><br>
                • <b>🖱️ الدبل كليك يمين:</b> اضغط مرتين بزر الماوس الأيمن (أو دبل كليك) على <b>أي زر أو خانة أو عمود جدول</b> في أي شاشة، فتفتح لك فوراً بطاقة الشرح التفصيلية (14 محوراً: بالبلدي، الوظيفة، الأثر المحاسبي، القراءة الحية، خطأ شائع، ومثال عملي) مع نطق صوتي فوري وإمكانية التحكم بالصوت والسرعة والـ 5 درجات للذكاء.<br>
                • <b>🎓 المرشد الذكي والممثل الآلي:</b> يشرح الشاشة خطوة بخطوة ويكتب البيانات الحية أمامك تلقائياً مع إمكانية الملخص السريع في 30 ثانية.`,
                btnHtml: `<div class="chat-options" style="margin-top:8px;">
                    <button type="button" class="chat-action-btn" onclick="if(window.openSmartGuide)window.openSmartGuide();">🎓 تشغيل المرشد الذكي</button>
                    <button type="button" class="chat-action-btn" onclick="if(window.speak30SecScreenSummary)window.speak30SecScreenSummary();">⚡ ملخص الشاشة في 30 ثانية</button>
                    ${items.length ? `<button type="button" class="chat-action-btn" onclick="window.highlightAndExplainChatElement(0)">🖱️ تجربة الدبل كليك يمين الآن</button>` : ''}
                </div>`
            };
        }

        // 3. List all buttons and fields on the current screen
        if (/(ازرار|أزرار|خانات|حقول|عناصر).*(الشاشة|الصفحة|هنا)|اشرح لي هذه الشاشة|اشرح هذه الشاشة/i.test(q)) {
            const pageTitle = document.title || location.pathname;
            const topItems = items.slice(0, 18);
            const pills = topItems.map((it, idx) =>
                `<span class="chat-pill" onclick="window.highlightAndExplainChatElement(${idx})" title="اضغط للإشارة إلى العنصر وشرحه بالدبل كليك يمين">${it.kind === 'زر' ? '🔘' : (it.kind === 'عمود جدول' ? '📊' : '📝')} ${it.label.slice(0, 28)}</span>`
            ).join('');
            return {
                html: `🧭 <b>فحص الشاشة الحية الآن: «${pageTitle}»</b><br>
                تم رصد <b>${items.length}</b> زراً وخانة وعموداً تفاعلياً في هذه الشاشة.<br>
                اضغط على أي زر أو خانة بالأسفل لكي أشير لك إليها بالليزر وأفتح بطاقة شرحها الصوتية فوراً:<br>
                <div class="chat-options" style="max-height:190px;overflow-y:auto;margin-top:8px;">${pills}</div>`,
                btnHtml: `<div class="chat-options" style="margin-top:8px;">
                    <button type="button" class="chat-action-btn" onclick="if(window.openSmartGuide)window.openSmartGuide();">🎓 فتح المرشد الكامل للشاشة</button>
                    <button type="button" class="chat-action-btn" onclick="if(window.speak30SecScreenSummary)window.speak30SecScreenSummary();">🎙️ سماع ملخص الشاشة 30 ثانية</button>
                </div>`
            };
        }

        // 4. Directory of all ERP screens
        if (/(جميع شاشات|كل شاشات|دليل شاشات|ما هي شاشات|صفحات البرنامج)/i.test(q)) {
            const linksHtml = MASTER_SCREENS_CATALOG.map(s =>
                `<a href="${s.url}" class="chat-pill" style="text-decoration:none;display:inline-block;">🔗 ${s.title}</a>`
            ).join('');
            return {
                html: `🗂️ <b>الدليل الشامل لجميع شاشات منظومة السجانة:</b><br>اضغط على أي شاشة للانتقال إليها مباشرة أو اسألني عن أي زر بداخلها:<div class="chat-options" style="max-height:220px;overflow-y:auto;margin-top:8px;">${linksHtml}</div>`,
                btnHtml: ''
            };
        }

        // 5. Direct question about a specific button, input, risk, benefit, or number on the current screen
        if (/(اشرح|ما هو|ما هي|اين|أين|وظيفة|فائدة|مخاطر|محاسن|اثر|أثر|ضوابط|قاعدة|رقم|زر|خانة|حقل|عمود)/i.test(q)) {
            const cleanQ = q.replace(/(اشرح|لي|ما هو|ما هي|اين|أين|وظيفة|فائدة|مخاطر|محاسن|اثر|أثر|ضوابط|قاعدة|رقم|زر|خانة|حقل|عمود|في|هذه|الشاشة|\؟|\?)/gi, '').trim();
            if (cleanQ.length >= 2) {
                const foundIdx = items.findIndex(it => it.label.includes(cleanQ) || cleanQ.includes(it.label));
                if (foundIdx !== -1) {
                    const targetItem = items[foundIdx];
                    const exp = getElementRichExplanation(targetItem.el);
                    const formattedDesc = String(exp.desc || '').split(' • ').slice(0, 14).map(p => `• ${p}`).join('<br>');
                    return {
                        html: `🎯 <b>${exp.title}</b> (${targetItem.kind})${targetItem.val ? `<br>📌 <b>القيمة/الرقم الحي الآن:</b> ${targetItem.val}` : ''}<br><br>${formattedDesc}`,
                        btnHtml: `<br><button type="button" class="chat-action-btn" onclick="window.highlightAndExplainChatElement(${foundIdx})">🎯 أشِر إلى العنصر وافتح بطاقة الـ 14 ركناً بالصوت</button>`
                    };
                }
            }
        }

        return null;
    };

    // Set up MutationObserver so any newly created modal, button, or row is indexed automatically
    let scanTimer = null;
    const observer = new MutationObserver(() => {
        if (scanTimer) clearTimeout(scanTimer);
        scanTimer = setTimeout(() => { window.scanLiveScreenUniverse(); }, 600);
    });
    if (document.body) {
        observer.observe(document.body, { childList: true, subtree: true });
    }
    setTimeout(() => { window.scanLiveScreenUniverse(); }, 400);
})();
