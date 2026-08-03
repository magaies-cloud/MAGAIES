<?php $page_title = 'النسخ الاحتياطي'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>النسخ الاحتياطي | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Tajawal', sans-serif; }
        
        .backup-container { max-width: 900px; margin: 50px auto; padding: 20px; }
        
        .header-panel {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.8), rgba(2, 132, 199, 0.9));
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 30px; margin-bottom: 30px;
            text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative; overflow: hidden;
        }

        .header-title { font-size: 2rem; font-weight: 900; color: #fff; margin: 0; }
        .header-subtitle { color: #e0f2fe; margin-top: 10px; font-size: 1.1rem; }

        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        
        .action-card {
            background: rgba(30,41,59,0.6); border-radius: 20px; padding: 40px; 
            border: 1px solid rgba(255,255,255,0.05); text-align: center; 
            backdrop-filter: blur(10px); transition: all 0.3s;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .action-card:hover { transform: translateY(-5px); background: rgba(30,41,59,0.8); }

        .icon-circle {
            width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; margin-bottom: 20px;
        }

        .export-card .icon-circle { background: rgba(16, 185, 129, 0.2); color: #34d399; box-shadow: 0 0 20px rgba(16,185,129,0.3); }
        .import-card .icon-circle { background: rgba(245, 158, 11, 0.2); color: #fbbf24; box-shadow: 0 0 20px rgba(245,158,11,0.3); }

        .btn-massive {
            padding: 15px 30px; border: none; border-radius: 12px; font-family: 'Tajawal'; font-size: 1.2rem; font-weight: 800;
            color: white; cursor: pointer; transition: 0.2s; width: 100%; margin-top: 20px;
        }
        .btn-export { background: #10b981; }
        .btn-export:hover { background: #059669; }
        .btn-import { background: #f59e0b; }
        .btn-import:hover { background: #d97706; }

        .file-input { display: none; }
        
        .warning-box {
            background: rgba(239, 68, 68, 0.1); border: 1px dashed #ef4444; border-radius: 12px; padding: 20px;
            margin-top: 30px; color: #fca5a5; display: flex; align-items: center; gap: 15px; font-weight: 600;
        }
        
        /* Universal Light Theme Overrides */
        :root.light-theme body,
        :root.light-theme .ambient-bg { background: #f8fafc !important; }
        :root.light-theme .glass-panel,
        :root.light-theme .neo-card,
        :root.light-theme .action-card {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
        }
    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="backup-container">
        
        <div style="margin-bottom: 20px;">
            <a data-href="settings.php" onclick="window.location.href='settings.php'" class="btn-action" style="background: rgba(255,255,255,0.1); text-decoration:none; padding:8px 15px; border-radius:8px; color:white; cursor:pointer;">↩ العودة للإعدادات</a>
        </div>

        <div class="premium-header">
            <h1 class="header-title">🛡️ مركز الأمان والنسخ الاحتياطي</h1>
            <p class="header-subtitle">قم بحماية بيانات شركتك وحفظها في جهازك كملف آمن</p>
        </div>

        <div class="cards-grid">
            
            <!-- Export Card -->
            <div class="action-card export-card" style="border-top: 4px solid #10b981; padding: 25px;">
                <div class="icon-circle">⬇️</div>
                <h3 style="margin:0; color:#fff; font-size:1.3rem;">إنشاء نسخة احتياطية</h3>
                <p style="color:#94a3b8; font-size:0.88rem; margin:15px 0 0 0; line-height:1.6; min-height: 80px;">يقوم بسحب كافة البيانات (عملاء، فواتير، مستودعات، قيود) وتجميعها في ملف واحد محمي للتحميل.</p>
                <button class="btn-massive btn-export" onclick="exportDatabase()">تنزيل النسخة الآن</button>
            </div>

            <!-- Import Card -->
            <div class="action-card import-card" style="border-top: 4px solid #f59e0b; padding: 25px;">
                <div class="icon-circle">⬆️</div>
                <h3 style="margin:0; color:#fff; font-size:1.3rem;">استعادة نسخة محفوظة</h3>
                <p style="color:#94a3b8; font-size:0.88rem; margin:15px 0 0 0; line-height:1.6; min-height: 80px;">يقوم بمسح البيانات الحالية واستبدالها بالبيانات الموجودة في ملف النسخة الاحتياطية الذي ستختاره.</p>
                <button class="btn-massive btn-import" onclick="document.getElementById('importFile').click()">رفع ملف النسخة الاحتياطية</button>
                <input type="file" id="importFile" class="file-input" accept=".json,.bak" onchange="importDatabase(event)">
            </div>

            <!-- Cloud Export Card -->
            <div class="action-card cloud-export-card" style="border-top: 4px solid #38bdf8; padding: 25px;">
                <div class="icon-circle">☁️</div>
                <h3 style="margin:0; color:#fff; font-size:1.3rem;">حفظ نسخة سحابية</h3>
                <p style="color:#94a3b8; font-size:0.88rem; margin:15px 0 0 0; line-height:1.6; min-height: 80px;">يقوم برفع وحفظ نسخة كاملة من بياناتك الحالية بشكل آمن على خوادمنا السحابية المشفرة للوصول لها بأي وقت.</p>
                <button id="btn-cloud-export" class="btn-massive" style="background: #38bdf8; color:#0f172a;" onclick="exportCloud()">رفع النسخة للسحاب</button>
            </div>

            <!-- Cloud Import Card -->
            <div class="action-card cloud-import-card" style="border-top: 4px solid #a855f7; padding: 25px;">
                <div class="icon-circle">🔄</div>
                <h3 style="margin:0; color:#fff; font-size:1.3rem;">استعادة نسخة سحابية</h3>
                <p style="color:#94a3b8; font-size:0.88rem; margin:15px 0 0 0; line-height:1.6; min-height: 80px;">يقوم بجلب أحدث نسخة احتياطية قمت برفعها للسحاب واستعادة جميع بياناتك منها فوراً.</p>
                <button id="btn-cloud-import" class="btn-massive" style="background: #a855f7;" onclick="importCloud()">تحميل واستعادة من السحاب</button>
            </div>

        </div>

        <div class="warning-box">
            <span style="font-size: 2rem;">⚠️</span>
            <div>
                <strong>تحذير هام جداً:</strong> يرجى القيام بعمل نسخة احتياطية (تنزيل) في نهاية كل يوم عمل. عند قيامك بعمل "استعادة" سيتم استبدال وحذف بياناتك الحالية فوراً ولن تتمكن من التراجع!
            </div>
        </div>

    </div>

    <!-- Required for DBReady and IndexedDB -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260803.27798"></script>
    <script>
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) { window.location.href = 'login.php'; return; }
            
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (user.role !== 'admin' && user.securityLevel < 9) {
                alert('صلاحيات غير كافية! هذه الشاشة مخصصة لمدير النظام فقط.');
                window.location.href = 'dashboard-pro.php';
            }
        });

    async function exportDatabase() {
        try {
            const completeStore = {};

            const parseIfJSON = (val) => {
                if (typeof val === 'string') {
                    try { return JSON.parse(val); } catch(e) { return val; }
                }
                return val;
            };

            const mergeData = (key, val) => {
                const parsedVal = parseIfJSON(val);
                if (!completeStore[key]) {
                    completeStore[key] = parsedVal;
                    return;
                }
                // If both are arrays, keep the one with more records to prevent data truncation
                if (Array.isArray(completeStore[key]) && Array.isArray(parsedVal)) {
                    if (parsedVal.length > completeStore[key].length) {
                        completeStore[key] = parsedVal;
                    }
                } else if (typeof parsedVal === 'object' && parsedVal !== null && !Array.isArray(parsedVal)) {
                    if (Object.keys(parsedVal).length >= Object.keys(completeStore[key]).length) {
                        completeStore[key] = parsedVal;
                    }
                } else {
                    completeStore[key] = parsedVal;
                }
            };

            // 1. Gather all keys from localStorage
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k) {
                    const val = localStorage.getItem(k);
                    if (val !== null) mergeData(k, val);
                }
            }

            // 2. Overlay DB_STORE memory proxy
            if (window.DB_STORE) {
                for (let [k, v] of Object.entries(window.DB_STORE)) {
                    if (v !== null && v !== undefined) {
                        mergeData(k, v);
                    }
                }
            }

            // 3. Gather all keys from localforage (async) - Highest priority for massive data
            if (typeof localforage !== 'undefined') {
                try {
                    const lfKeys = await localforage.keys();
                    for (let k of lfKeys) {
                        const val = await localforage.getItem(k);
                        if (val !== null) {
                            mergeData(k, val);
                        }
                    }
                } catch(e) {
                    console.error("Error reading localforage:", e);
                }
            }

            // Clean up empty data
            const cleanStore = {};
            let recordCount = 0;
            for (let [k, v] of Object.entries(completeStore)) {
                if (v && (Array.isArray(v) ? v.length > 0 : (typeof v === 'object' ? Object.keys(v).length > 0 : true))) {
                    cleanStore[k] = v;
                    if (Array.isArray(v)) recordCount += v.length;
                }
            }

            if (Object.keys(cleanStore).length === 0) {
                alert("⚠️ لا توجد بيانات مسجلة بالنظام للتصدير حالياً.");
                return;
            }

            if (!confirm(`سيتم الآن تجميع كافة بيانات النظام وتنزيلها كملف احتياطي (يحتوي على ${recordCount} سجل). هل ترغب بالاستمرار؟`)) return;

            const dateStr = new Date().toISOString().split('T')[0];
            const fileName = `MAGAIEES_ERP_Backup_${dateStr}.bak.json`;
            const backupDataStr = JSON.stringify(cleanStore, null, 2);

            const blob = new Blob([backupDataStr], { type: "application/json;charset=utf-8" });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            if (typeof window.logSystemAction === 'function') {
                window.logSystemAction('Database Export', 'system', 'تصدير نسخة احتياطية لقاعدة البيانات بالكامل');
            }
            alert(`✅ تم تصدير النسخة الاحتياطية وتنزيلها بنجاح! (تم تصدير ${recordCount} سجل)`);
        } catch(e) {
                console.error(e);
                alert("❌ حدث خطأ أثناء تجميع وتنزيل النسخة الاحتياطية: " + e.message);
            }
        }

        function importDatabase(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!confirm("⚠️ تحذير خطير: استعادة النسخة الاحتياطية ستمسح كافة البيانات الحالية وتستبدلها ببيانات الملف المرفوع. هل أنت متأكد 100% أنك تريد الاستمرار؟")) {
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = async function(e) {
                let loader = null;
                try {
                    const content = e.target.result;
                    const data = JSON.parse(content);
                    
                    if (typeof data !== 'object' || Array.isArray(data) || data === null) {
                        throw new Error("ملف غير صالح");
                    }

                    // Show loader
                    loader = document.createElement('div');
                    loader.id = 'import-loader';
                    loader.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.95); backdrop-filter:blur(15px); z-index:999999; display:flex; flex-direction:column; align-items:center; justify-content:center; color:white; font-family:Tajawal; text-align:center; padding:20px;';
                    loader.innerHTML = `
                        <div style="font-size:3.5rem; margin-bottom:15px; animation:spin 2s linear infinite;">🔄</div>
                        <h2 style="font-size:1.8rem; font-weight:900; margin:0 0 10px 0; color:#38bdf8;">جاري استعادة البيانات والتهيأة...</h2>
                        <p style="color:#94a3b8; font-size:1rem; margin:0;">يرجى عدم إغلاق المتصفح حتى إكتمال العملية.</p>
                    `;
                    document.body.appendChild(loader);

                    // 1. Preserve critical session tokens
                    const sessionKeys = ['authToken', 'saas_token', 'user', 'erp_lang', 'magaiees_global_theme'];
                    const preservedSession = {};
                    sessionKeys.forEach(k => {
                        const val = localStorage.getItem(k);
                        if (val !== null) preservedSession[k] = val;
                    });

                    // 2. Clear current localforage
                    const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                    try {
                        if (typeof localforage !== 'undefined') {
                            const keys = await localforage.keys();
                            for (let k of keys) {
                                if (k.startsWith(prefix) || prefix === '') {
                                    await localforage.removeItem(k);
                                }
                            }
                        }
                    } catch(err) {}

                    // 3. Clear current localStorage erp_ & tenant_ keys
                    const lsKeys = [];
                    for (let i = 0; i < localStorage.length; i++) {
                        lsKeys.push(localStorage.key(i));
                    }
                    for (let k of lsKeys) {
                        if (k && !sessionKeys.includes(k) && (k.startsWith('erp_') || k.startsWith('tenant_') || k.startsWith(prefix))) {
                            localStorage.removeItem(k);
                        }
                    }

                    // 4. Populate new data into localStorage, localforage, & window.DB_STORE
                    let restoredCount = 0;
                    for (let [key, val] of Object.entries(data)) {
                        const valStr = typeof val === 'string' ? val : JSON.stringify(val);
                        
                        try { localStorage.setItem(key, valStr); } catch(err) {}
                        try {
                            if (typeof localforage !== 'undefined') {
                                await localforage.setItem(key, valStr);
                            }
                        } catch(err) {}

                        if (window.DB_STORE) {
                            window.DB_STORE[key] = valStr;
                        }
                        restoredCount++;
                    }

                    // 5. Restore preserved session tokens
                    Object.entries(preservedSession).forEach(([k, v]) => {
                        localStorage.setItem(k, v);
                    });

                    if (typeof window.logSystemAction === 'function') {
                        window.logSystemAction('Database Import', 'system', 'استعادة نسخة احتياطية من ملف خارجي بنجاح');
                    }

                    alert(`🎉 تم استعادة البيانات بنجاح (${restoredCount} حقول/جداول)! سيتم إعادة تحميل النظام الآن.`);
                    window.location.href = 'dashboard-pro.php';
                } catch (err) {
                    console.error(err);
                    alert("❌ خطأ: ملف النسخة الاحتياطية تالف أو غير مدعوم (" + err.message + ")");
                    event.target.value = '';
                    if(loader) loader.remove();
                }
            };
            reader.readAsText(file);
        }

        async function exportCloud() {
            if (Object.keys(window.DB_STORE).length === 0) {
                alert("لا توجد بيانات للنسخ!");
                return;
            }
            if(!confirm("سيتم رفع قاعدة بياناتك وتخزينها على السحابة بشكل آمن. هل ترغب بالاستمرار؟")) return;

            const btn = document.getElementById('btn-cloud-export');
            btn.disabled = true;
            btn.innerText = 'جاري الرفع... ☁️';

            try {
                const token = localStorage.getItem('authToken');
                const res = await fetch('api_index.php?r=cloud-backup', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ backup: window.DB_STORE })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.logSystemAction('Cloud Database Export', 'system', 'تصدير نسخة احتياطية سحابية لقاعدة البيانات');
                    alert("✅ تم رفع النسخة الاحتياطية للسحاب بنجاح وحمايتها!");
                } else {
                    throw new Error(data.error || 'فشل الرفع للسيرفر');
                }
            } catch (err) {
                alert("❌ خطأ: " + err.message);
            } finally {
                btn.disabled = false;
                btn.innerText = 'رفع النسخة للسحاب';
            }
        }

        async function importCloud() {
            if(!confirm("⚠️ تحذير خطير: استعادة النسخة الاحتياطية السحابية ستمسح كافة البيانات الحالية وتستبدلها. هل أنت متأكد 100% أنك تريد الاستمرار؟")) {
                return;
            }

            const btn = document.getElementById('btn-cloud-import');
            btn.disabled = true;
            btn.innerText = 'جاري التحميل... 🔄';

            try {
                const token = localStorage.getItem('authToken');
                const res = await fetch('api_index.php?r=cloud-backup', {
                    method: 'GET',
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                
                if (!res.ok || !data.success) {
                    throw new Error(data.error || 'فشل تحميل النسخة من السحاب');
                }
                
                if (!data.backup || Object.keys(data.backup).length === 0) {
                    throw new Error('لا توجد نسخة احتياطية سحابية محفوظة لهذه الشركة حتى الآن.');
                }

                // Show loader
                const loader = document.createElement('div');
                loader.id = 'import-loader';
                loader.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.95); backdrop-filter:blur(15px); z-index:999999; display:flex; flex-direction:column; align-items:center; justify-content:center; color:white; font-family:Tajawal; text-align:center; padding:20px;';
                loader.innerHTML = `
                    <div style="font-size:3.5rem; margin-bottom:15px; animation:spin 2s linear infinite;">🔄</div>
                    <h2 style="font-size:1.8rem; font-weight:900; margin:0 0 10px 0; color:#38bdf8;">جاري استعادة البيانات من السحاب...</h2>
                    <p style="color:#94a3b8; font-size:1rem; margin:0;">يرجى عدم إغلاق المتصفح حتى إكتمال العملية.</p>
                `;
                document.body.appendChild(loader);

                // 1. Clear Current localforage completely for this tenant
                const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                const keys = await localforage.keys();
                for (let k of keys) {
                    if (k.startsWith(prefix) || prefix === '') {
                        await localforage.removeItem(k);
                    }
                }

                // 2. Populate new data
                for (let [key, val] of Object.entries(data.backup)) {
                    const valStr = typeof val === 'string' ? val : JSON.stringify(val);
                    try { localStorage.setItem(key, valStr); } catch(e) {}
                    await localforage.setItem(key, valStr);
                    if (window.DB_STORE) window.DB_STORE[key] = valStr;
                }

                window.logSystemAction('Cloud Database Import', 'system', 'استعادة نسخة احتياطية من السحاب');

                alert("🎉 تم استعادة البيانات من السحاب بنجاح! سيتم إعادة تحميل النظام الآن.");
                window.location.href = 'dashboard-pro.php';
            } catch (err) {
                console.error(err);
                alert("❌ خطأ: " + err.message);
                btn.disabled = false;
                btn.innerText = 'تحميل واستعادة من السحاب';
                const loader = document.getElementById('import-loader');
                if(loader) loader.remove();
            }
        }
    </script>
</body>
</html>
