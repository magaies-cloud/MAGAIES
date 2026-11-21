(function() {
    if (window.__SPLIT_CONSTITUTION1_LOCK__) return;
    window.__SPLIT_CONSTITUTION1_LOCK__ = true;

    // 1. Proportional Geometric Scaling (6:8 Aspect-Ratio Lock on Ctrl + Mouse Wheel up to 10x)
    let currentScale = parseFloat(localStorage.getItem('magaies_global_master_scale_v1') || localStorage.getItem('sales_c1_prop_zoom') || '1') || 1;
    currentScale = Math.min(10.0, Math.max(0.25, currentScale));

    window.applySalesProportionalZoom = function(scaleVal) {
        if (typeof window.setSalesFc1ProportionalZoom === 'function') {
            return window.setSalesFc1ProportionalZoom(scaleVal);
        }
        currentScale = Math.min(10.0, Math.max(0.25, +(parseFloat(scaleVal) || 1).toFixed(2)));
        localStorage.setItem('sales_c1_prop_zoom', String(currentScale));
        localStorage.setItem('magaies_global_master_scale_v1', String(currentScale));
    };

    // 2. Auto-Fit Inputs in Split Screen (Zero-Truncation)
    window.autoFitSplitInputs = function(root) {
        const scope = root || document.getElementById('split-invoice-view') || document;
        const inputs = scope.querySelectorAll('input[id^="split-qty-"], input[id^="split-subqty-"], #split-operation-seq-input, input[id^="split-sec-receiver-"], input[id^="split-sec-truck-"], #split-invoice-search');
        inputs.forEach(inp => {
            const len = String(inp.value || inp.placeholder || '').length;
            if (inp.id && (inp.id.indexOf('split-qty-') === 0 || inp.id.indexOf('split-subqty-') === 0)) {
                const chWidth = Math.max(108, Math.min(175, (len + 4) * 12));
                inp.style.width = chWidth + 'px';
            }
        });
    };

    document.addEventListener('input', function(e) {
        if (e.target && e.target.closest && e.target.closest('#split-invoice-view')) {
            window.autoFitSplitInputs(document.getElementById('split-invoice-view'));
            if (typeof window.persistActiveSplitDraftToStorage === 'function') {
                window.persistActiveSplitDraftToStorage();
            }
        }
    }, true);

    // 3. F2 Shortcut to Focus Split Search Input Instantly
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        if (e.key === 'F2') {
            const searchInp = document.getElementById('split-invoice-search');
            if (searchInp && searchInp.offsetParent !== null) {
                e.preventDefault();
                searchInp.focus();
                searchInp.select();
            }
        }
    });

    // 4. Register Split Screen & Child Modals with Smart Guide Automatically
    window.registerSplitViewWithSmartGuide = function() {
        if (window.MAGAIES_SMART_GUIDE_CUSTOM_TIPS) {
            window.MAGAIES_SMART_GUIDE_CUSTOM_TIPS['split-invoice-view'] = {
                title: '✂️ بوابة تقسيم الفواتير والأذونات الذكية بالوحدتين',
                desc: 'تتيح تقسيم أي فاتورة مبيعات إلى قسمين أو أكثر بالوحدة الأساسية أو الفرعية مع توزيع نسبي آلي للخصم والضريبة وحفظ المسودة تلقائياً.'
            };
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.applySalesProportionalZoom(currentScale);
        window.autoFitSplitInputs();
        window.registerSplitViewWithSmartGuide();
    });
})();
