<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تشخيص أسعار الصرف</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #e2e8f0; padding: 30px; direction: rtl; }
        .card { background: #1e293b; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #334155; }
        h2 { color: #38bdf8; margin-top: 0; }
        .pass { color: #10b981; font-weight: bold; }
        .fail { color: #ef4444; font-weight: bold; }
        .warn { color: #f59e0b; font-weight: bold; }
        pre { background: #0f172a; padding: 10px; border-radius: 8px; overflow-x: auto; font-size: 13px; max-height: 200px; overflow-y: auto; }
        button { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; margin: 5px; }
        button:hover { background: #2563eb; }
        .test-row { padding: 8px 0; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; }
        .test-label { flex: 1; }
        .test-result { flex: 1; text-align: left; }
        #results { white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>🔧 صفحة تشخيص أسعار الصرف</h1>

    <div class="card">
        <h2>📦 1. فحص localStorage</h2>
        <div id="ls-check"></div>
    </div>

    <div class="card">
        <h2>🌐 2. فحص API سعر الصرف</h2>
        <div id="api-check"></div>
    </div>

    <div class="card">
        <h2>⚙️ 3. فحص دالة getExchangeRateForCurrency</h2>
        <div id="func-check"></div>
    </div>

    <div class="card">
        <h2>🧪 4. اختبار تعيين سعر يدوي وحفظه</h2>
        <button onclick="testManualSave()">اختبار: حفظ سعر USD = 600 يدوياً</button>
        <button onclick="testGlobalSave()">اختبار: حفظ الوضع المركزي</button>
        <div id="save-check"></div>
    </div>

    <div class="card">
        <h2>📋 5. محتوى erp_settings_v1 كامل</h2>
        <pre id="full-settings"></pre>
    </div>

    <!-- Load app.js to test the function -->
    <script src="js/app.js?v=diag_<?php echo time(); ?>"></script>

    <script>
    function log(targetId, msg, type) {
        const el = document.getElementById(targetId);
        const div = document.createElement('div');
        div.className = 'test-row';
        div.innerHTML = `<span class="test-label">${msg.label}</span><span class="test-result ${type}">${msg.value}</span>`;
        el.appendChild(div);
    }

    // ============ TEST 1: localStorage ============
    (function() {
        const raw = localStorage.getItem('erp_settings_v1');
        if (!raw) {
            log('ls-check', {label: 'erp_settings_v1 موجود؟', value: '❌ غير موجود في localStorage!'}, 'fail');
            return;
        }
        log('ls-check', {label: 'erp_settings_v1 موجود؟', value: '✅ نعم'}, 'pass');

        try {
            const cfg = JSON.parse(raw);

            log('ls-check', {label: 'exchangeMode', value: cfg.exchangeMode || '(غير محدد - الافتراضي: global)'}, cfg.exchangeMode ? 'pass' : 'warn');
            log('ls-check', {label: 'rateUSD', value: cfg.rateUSD !== null && cfg.rateUSD !== undefined ? cfg.rateUSD : '❌ NULL أو غير موجود'}, cfg.rateUSD ? 'pass' : 'fail');
            log('ls-check', {label: 'rateSAR', value: cfg.rateSAR !== null && cfg.rateSAR !== undefined ? cfg.rateSAR : '❌ NULL أو غير موجود'}, cfg.rateSAR ? 'pass' : 'fail');
            log('ls-check', {label: 'rateAED', value: cfg.rateAED !== null && cfg.rateAED !== undefined ? cfg.rateAED : '❌ NULL أو غير موجود'}, cfg.rateAED ? 'pass' : 'fail');
            log('ls-check', {label: 'نوع rateUSD', value: typeof cfg.rateUSD + ' = ' + JSON.stringify(cfg.rateUSD)}, typeof cfg.rateUSD === 'number' ? 'pass' : 'warn');
            log('ls-check', {label: 'activeCurrencies', value: JSON.stringify(cfg.activeCurrencies || [])}, (cfg.activeCurrencies && cfg.activeCurrencies.length > 0) ? 'pass' : 'warn');

            document.getElementById('full-settings').textContent = JSON.stringify(cfg, null, 2);
        } catch(e) {
            log('ls-check', {label: 'خطأ في التحليل', value: e.message}, 'fail');
        }
    })();

    // ============ TEST 2: API ============
    fetch('api_index.php/exchange-rate')
        .then(r => r.json())
        .then(data => {
            log('api-check', {label: 'استجابة API', value: JSON.stringify(data)}, 'pass');
            log('api-check', {label: 'data.visible', value: String(data.visible)}, data.visible ? 'pass' : 'warn');
            log('api-check', {label: 'data.rate', value: String(data.rate)}, (data.rate && parseFloat(data.rate) > 0) ? 'pass' : 'fail');
        })
        .catch(err => {
            log('api-check', {label: 'خطأ في API', value: err.message}, 'fail');
        });

    // ============ TEST 3: Function ============
    setTimeout(() => {
        if (typeof window.getExchangeRateForCurrency === 'function') {
            log('func-check', {label: 'الدالة موجودة؟', value: '✅ نعم'}, 'pass');

            const usd = window.getExchangeRateForCurrency('USD');
            const sar = window.getExchangeRateForCurrency('SAR');
            const sdg = window.getExchangeRateForCurrency('SDG');

            log('func-check', {label: 'getExchangeRateForCurrency("USD")', value: String(usd)}, usd > 0 ? 'pass' : 'fail');
            log('func-check', {label: 'getExchangeRateForCurrency("SAR")', value: String(sar)}, sar > 0 ? 'pass' : 'fail');
            log('func-check', {label: 'getExchangeRateForCurrency("SDG")', value: String(sdg)}, sdg === 1 ? 'pass' : 'fail');

            // Test with cached rate
            log('func-check', {label: 'window._cachedGlobalRate', value: String(window._cachedGlobalRate || 'غير موجود')}, window._cachedGlobalRate ? 'pass' : 'warn');
        } else {
            log('func-check', {label: 'الدالة موجودة؟', value: '❌ الدالة غير موجودة! app.js لم يُحمّل بعد'}, 'fail');
        }
    }, 1500);

    // ============ TEST 4: Manual Save ============
    function testManualSave() {
        const el = document.getElementById('save-check');
        el.innerHTML = '';

        const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        cfg.exchangeMode = 'manual';
        cfg.rateUSD = 600;
        cfg.rateSAR = 160;
        cfg.rateAED = 163.49;
        localStorage.setItem('erp_settings_v1', JSON.stringify(cfg));

        // Verify
        const verify = JSON.parse(localStorage.getItem('erp_settings_v1'));
        log('save-check', {label: 'الوضع بعد الحفظ', value: verify.exchangeMode}, verify.exchangeMode === 'manual' ? 'pass' : 'fail');
        log('save-check', {label: 'rateUSD بعد الحفظ', value: String(verify.rateUSD)}, verify.rateUSD === 600 ? 'pass' : 'fail');
        log('save-check', {label: 'rateSAR بعد الحفظ', value: String(verify.rateSAR)}, verify.rateSAR === 160 ? 'pass' : 'fail');

        // Test the function again
        if (typeof window.getExchangeRateForCurrency === 'function') {
            const usd = window.getExchangeRateForCurrency('USD');
            log('save-check', {label: 'getExchangeRateForCurrency("USD") بعد الحفظ', value: String(usd)}, usd === 600 ? 'pass' : 'fail');
        }

        log('save-check', {label: '✅ تم الحفظ!', value: 'اذهب الآن لشاشة المشتريات وغيّر العملة لـ USD'}, 'pass');
    }

    function testGlobalSave() {
        const el = document.getElementById('save-check');
        el.innerHTML = '';

        const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        cfg.exchangeMode = 'global';
        localStorage.setItem('erp_settings_v1', JSON.stringify(cfg));

        const verify = JSON.parse(localStorage.getItem('erp_settings_v1'));
        log('save-check', {label: 'الوضع بعد الحفظ', value: verify.exchangeMode}, verify.exchangeMode === 'global' ? 'pass' : 'fail');
        log('save-check', {label: 'rateUSD محفوظ', value: String(verify.rateUSD)}, 'pass');
        log('save-check', {label: '✅ تم الحفظ!', value: 'اذهب الآن لشاشة المشتريات وغيّر العملة لـ USD'}, 'pass');
    }
    </script>
</body>
</html>
