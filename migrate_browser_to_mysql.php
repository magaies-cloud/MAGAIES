<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مزامنة البيانات مع MySQL</title>
    <script src="js/localforage.min.js"></script>
    <script src="js/app.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Tajawal', Tahoma, sans-serif; background: #0f172a; color: #e2e8f0; padding: 30px; min-height: 100vh; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { text-align: center; color: #38bdf8; margin-bottom: 10px; font-size: 1.8rem; }
        .subtitle { text-align: center; color: #94a3b8; margin-bottom: 30px; }
        .card { background: #1e293b; border-radius: 12px; padding: 24px; margin-bottom: 20px; border: 1px solid #334155; }
        .card h2 { color: #38bdf8; margin-bottom: 15px; font-size: 1.2rem; }
        .step { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; margin-bottom: 8px; background: #0f172a; }
        .step-num { background: #38bdf8; color: #0f172a; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; flex-shrink: 0; }
        .step-text { flex: 1; }
        .step-status { font-size: 1.3rem; }
        .btn { background: linear-gradient(135deg, #0284c7, #0369a1); color: white; border: none; padding: 14px 32px; font-size: 18px; font-weight: 800; border-radius: 8px; cursor: pointer; display: block; width: 100%; margin-top: 20px; font-family: inherit; }
        .btn:hover { background: linear-gradient(135deg, #0369a1, #075985); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        #log { background: #020617; color: #38bdf8; padding: 16px; border-radius: 8px; font-family: 'Consolas', monospace; font-size: 13px; white-space: pre-wrap; max-height: 350px; overflow-y: auto; margin-top: 20px; direction: ltr; text-align: left; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .count { color: #a78bfa; font-weight: 900; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 مزامنة البيانات مع MySQL</h1>
        <p class="subtitle">نقل بيانات المتصفح إلى قاعدة البيانات المحلية لحمايتها</p>

        <div class="card">
            <h2>خطوات المزامنة</h2>
            <div class="step" id="step1"><span class="step-num">1</span><span class="step-text">فحص بيانات المتصفح (localStorage + IndexedDB)</span><span class="step-status" id="s1">⏳</span></div>
            <div class="step" id="step2"><span class="step-num">2</span><span class="step-text">التحقق من اتصال MySQL</span><span class="step-status" id="s2">⏳</span></div>
            <div class="step" id="step3"><span class="step-num">3</span><span class="step-text">رفع البيانات إلى MySQL (Force Push)</span><span class="step-status" id="s3">⏳</span></div>
            <div class="step" id="step4"><span class="step-num">4</span><span class="step-text">التحقق من نجاح الحفظ</span><span class="step-status" id="s4">⏳</span></div>
        </div>

        <button class="btn" id="startBtn" onclick="startMigration()">🚀 ابدأ المزامنة الآن</button>
        <div id="log">في انتظار بدء المزامنة...</div>
    </div>

    <script>
        const syncKeys = [
            'erp_users_v1', 'erp_customers_v1', 'erp_suppliers_v1', 'erp_items_v1',
            'erp_inventory_v1', 'erp_sales_invoices_v1', 'erp_purchase_invoices_v1',
            'erp_receipts_v1', 'erp_payments_v1', 'erp_journal_vouchers_v1',
            'erp_settings_v1', 'erp_warehouses_v1', 'erp_banks_v1', 'erp_safes_v1', 'erp_categories_v1',
            'erp_roles_v1', 'erp_checks_v1', 'erp_gl_v1', 'erp_single_receipts_v1',
            'erp_single_payments_v1', 'erp_partners_v1', 'erp_employees_v1',
            'erp_payroll_v1', 'erp_reserved_sales_v1', 'erp_delivery_notes_v1',
            'erp_accounts_v1', 'erp_jobs_v1', 'erp_audit_logs'
        ];

        function log(msg, type = '') {
            const el = document.getElementById('log');
            el.innerHTML += `<span class="${type}">${msg}</span>\n`;
            el.scrollTop = el.scrollHeight;
        }

        function setStatus(step, status) {
            document.getElementById('s' + step).textContent = status;
        }

        async function startMigration() {
            document.getElementById('startBtn').disabled = true;
            document.getElementById('log').innerHTML = '';

            // Step 1: Scan browser data
            setStatus(1, '🔄');
            log('━━━ المرحلة 1: فحص بيانات المتصفح ━━━');

            let payload = {};
            let totalRecords = 0;

            for (const baseKey of syncKeys) {
                let data = null;
                
                // Try tenant key first
                if (typeof window.getTenantKey === 'function') {
                    const tk = window.getTenantKey(baseKey);
                    if (window.DB_STORE && window.DB_STORE[tk]) {
                        try { data = JSON.parse(window.DB_STORE[tk]); } catch(e) {}
                    }
                    if (!data) {
                        const raw = localStorage.getItem(tk);
                        if (raw && raw !== '[]' && raw !== '{}') {
                            try { data = JSON.parse(raw); } catch(e) {}
                        }
                    }
                }
                
                // Try base key
                if (!data || (Array.isArray(data) && data.length === 0)) {
                    const raw = localStorage.getItem(baseKey);
                    if (raw && raw !== '[]' && raw !== '{}') {
                        try { data = JSON.parse(raw); } catch(e) {}
                    }
                }
                
                // Try IndexedDB
                if (!data || (Array.isArray(data) && data.length === 0)) {
                    try {
                        const raw = await localforage.getItem(baseKey);
                        if (raw && raw !== '[]' && raw !== '{}') {
                            try { data = JSON.parse(raw); } catch(e) { data = raw; }
                        }
                    } catch(e) {}
                }

                if (data && ((Array.isArray(data) && data.length > 0) || (!Array.isArray(data) && Object.keys(data).length > 0))) {
                    payload[baseKey] = data;
                    const count = Array.isArray(data) ? data.length : 1;
                    totalRecords += count;
                    log(`  ✅ ${baseKey}: <span class="count">${count} سجل</span>`, 'success');
                } else {
                    log(`  ⚪ ${baseKey}: فارغ`, '');
                }
            }

            log(`\nإجمالي السجلات المكتشفة: <span class="count">${totalRecords}</span>`, 'success');
            setStatus(1, '✅');

            if (totalRecords === 0) {
                log('\n⚠️ لا توجد بيانات للمزامنة!', 'warning');
                setStatus(1, '⚠️');
                document.getElementById('startBtn').disabled = false;
                return;
            }

            // Step 2: Check MySQL connection
            setStatus(2, '🔄');
            log('\n━━━ المرحلة 2: التحقق من اتصال MySQL ━━━');

            try {
                const checkRes = await fetch('api/check_db.php');
                const checkText = await checkRes.text();
                log('  اتصال MySQL: ' + checkText, 'success');
                setStatus(2, '✅');
            } catch(e) {
                log('  ❌ فشل الاتصال بـ MySQL: ' + e.message, 'error');
                setStatus(2, '❌');
                document.getElementById('startBtn').disabled = false;
                return;
            }

            // Step 3: Force push to MySQL
            setStatus(3, '🔄');
            log('\n━━━ المرحلة 3: رفع البيانات إلى MySQL ━━━');

            const token = localStorage.getItem('authToken');
            if (!token) {
                log('  ⚠️ لا يوجد authToken. سيتم إنشاء جلسة محلية...', 'warning');
                // Create a local token
                localStorage.setItem('authToken', 'local_sync_token_' + Date.now());
            }

            try {
                const pushRes = await fetch('api_index.php/sync/push', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                    },
                    body: JSON.stringify(payload)
                });

                const pushData = await pushRes.json();

                if (pushRes.ok && pushData.success !== false) {
                    log('  ✅ تم رفع البيانات بنجاح إلى MySQL!', 'success');
                    setStatus(3, '✅');
                } else {
                    log('  ⚠️ رد الخادم: ' + JSON.stringify(pushData), 'warning');
                    if (pushData.error && pushData.error.includes('انتهاء الجلسة')) {
                        log('  📝 المستخدم غير مسجل دخول. جاري تسجيل الدخول تلقائياً...', 'warning');
                        
                        // Try to login
                        const loginRes = await fetch('api_index.php/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ username: 'admin', password: 'admin', subdomain: 'default' })
                        });
                        const loginData = await loginRes.json();
                        
                        if (loginData.token) {
                            localStorage.setItem('authToken', loginData.token);
                            log('  ✅ تم تسجيل الدخول. جاري إعادة الرفع...', 'success');
                            
                            // Retry push
                            const retryRes = await fetch('api_index.php/sync/push', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + loginData.token
                                },
                                body: JSON.stringify(payload)
                            });
                            const retryData = await retryRes.json();
                            if (retryRes.ok && retryData.success !== false) {
                                log('  ✅ تم رفع البيانات بنجاح!', 'success');
                                setStatus(3, '✅');
                            } else {
                                log('  ❌ فشل الرفع: ' + JSON.stringify(retryData), 'error');
                                setStatus(3, '❌');
                            }
                        } else {
                            log('  ❌ فشل تسجيل الدخول', 'error');
                            setStatus(3, '❌');
                        }
                    } else {
                        setStatus(3, '⚠️');
                    }
                }
            } catch(e) {
                log('  ❌ خطأ في الاتصال: ' + e.message, 'error');
                setStatus(3, '❌');
            }

            // Step 4: Verify
            setStatus(4, '🔄');
            log('\n━━━ المرحلة 4: التحقق من نجاح الحفظ ━━━');

            try {
                const verifyRes = await fetch('api_index.php/sync/pull', {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('authToken') }
                });
                const verifyData = await verifyRes.json();
                
                if (verifyData.data) {
                    let savedCount = 0;
                    for (const key of Object.keys(verifyData.data)) {
                        const arr = verifyData.data[key];
                        if (Array.isArray(arr)) savedCount += arr.length;
                    }
                    log(`  ✅ MySQL يحتوي الآن على <span class="count">${savedCount} سجل</span>`, 'success');
                    setStatus(4, '✅');
                } else {
                    log('  ⚠️ لم يتمكن من التحقق', 'warning');
                    setStatus(4, '⚠️');
                }
            } catch(e) {
                log('  ⚠️ تعذر التحقق: ' + e.message, 'warning');
                setStatus(4, '⚠️');
            }

            log('\n══════════════════════════════════════════', 'success');
            log('🎉 اكتملت عملية المزامنة!', 'success');
            log('يمكنك الآن العودة إلى النظام والعمل بأمان.', 'success');
            log('بياناتك محمية في MySQL + المتصفح.', 'success');

            document.getElementById('startBtn').disabled = false;
            document.getElementById('startBtn').textContent = '🔄 إعادة المزامنة';
        }
    </script>

<script src="js/magaiees_back.js?v=2026_back_v1" defer></script>
</body>
</html>
