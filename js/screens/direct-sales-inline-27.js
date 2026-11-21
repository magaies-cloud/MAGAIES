(function() {
    if (window.__SPLIT_CONSTITUTION5_LOCK__) return;
    window.__SPLIT_CONSTITUTION5_LOCK__ = true;

    // 1. القاعدة الذهبية لكل عنصر عند التأشير (Hover 650ms) أو الضغط المباشر (Alt + Click) أثناء عمل المرشد
    let hoverTimer = null;
    function getGoldenRuleExplanationForEl(el) {
        if (!el) return null;
        const lbl = (el.getAttribute('title') || el.innerText || el.placeholder || el.id || 'هذا العنصر').replace(/\s+/g, ' ').trim().slice(0, 65);
        const id = (el.id || '').toLowerCase();
        let rule = 'القاعدة الذهبية: تحقق من توازن الكميات بين الأقسام قبل الحفظ.';
        if (id.includes('search')) rule = 'القاعدة الذهبية للبحث: اكتب أول حرفين فقط واضغط Enter لفتح الفاتورة للتقسيم فوراً.';
        else if (id.includes('qty') || id.includes('subqty')) rule = 'القاعدة الذهبية للكمية: أدخل كمية القسم الأول وسيتكفل النظام بحساب القسم المتبقي تلقائياً.';
        else if (id.includes('receiver')) rule = 'القاعدة الذهبية للمستلم: دوّن اسم المستلم الفعلي لكل قسم لضمان دقة التسليم المخزني.';
        else if (id.includes('truck')) rule = 'القاعدة الذهبية للمركبة: دوّن رقم اللوحة لربط إذن البوابة بالشاحنة.';
        else if (id.includes('approve')) rule = 'القاعدة الذهبية للاعتماد: اعتمد التقسيم بعد ظهور شارة (متوازن 100%) الخضراء.';
        else if (id.includes('cancel')) rule = 'القاعدة الذهبية للإلغاء: زر الإلغاء محمي بتأكيد مدمج سريع لحماية مدخلاتك.';
        else if (id.includes('park')) rule = 'القاعدة الذهبية للتعليق المؤقت: علّق الفاتورة الحالية بضغطة واحدة لخدمة عميل مستعجل واسترجعها فوراً من الشريط العلوي.';
        else if (id.includes('undo')) rule = 'القاعدة الذهبية للتراجع: اضغط Ctrl+Z لاستعادة آخر خطوة توزيع فوراً.';
        else if (id.includes('reset')) rule = 'القاعدة الذهبية للتصفير: يعيد ضبط التوزيع للوضع الافتراضي مع حفظ خطوة التراجع.';
        return { title: lbl, text: `${lbl}. ${rule}` };
    }

    document.addEventListener('mouseover', function(e) {
        const coachBar = document.getElementById('msg-live-coach-bar');
        if (!coachBar || coachBar.style.display === 'none') return;
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        const target = e.target && e.target.closest && e.target.closest('#split-invoice-view button, #split-invoice-view input, #split-invoice-view select');
        if (!target) return;
        if (hoverTimer) clearTimeout(hoverTimer);
        hoverTimer = setTimeout(function() {
            const info = getGoldenRuleExplanationForEl(target);
            if (info && typeof window.pointGhostCursorAt === 'function') {
                if (!target.id) target.id = 'split-dyn-el-' + Math.random().toString(36).slice(2, 7);
                window.pointGhostCursorAt('#' + target.id, info.title, info.text);
            }
        }, 700);
    }, true);

    document.addEventListener('mouseout', function() {
        if (hoverTimer) {
            clearTimeout(hoverTimer);
            hoverTimer = null;
        }
    }, true);

    // Click-to-Jump (Alt + Click on any element while guide is active jumps immediately to explain it)
    document.addEventListener('click', function(e) {
        if (!e.altKey) return;
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        const target = e.target && e.target.closest && e.target.closest('#split-invoice-view button, #split-invoice-view input, #split-invoice-view select, #split-invoice-view th');
        if (!target) return;
        e.preventDefault();
        e.stopPropagation();
        const info = getGoldenRuleExplanationForEl(target);
        if (info && typeof window.pointGhostCursorAt === 'function') {
            if (!target.id) target.id = 'split-dyn-el-' + Math.random().toString(36).slice(2, 7);
            window.pointGhostCursorAt('#' + target.id, info.title, info.text);
        }
    }, true);

    // 2. تسجيل المرشد الذكي عند الطلب فقط بدون مراقب شجري ثقيل (0ms Zero-Jank)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.registerSplitViewWithSmartGuide === 'function') {
            window.registerSplitViewWithSmartGuide();
        }
    });
})();
