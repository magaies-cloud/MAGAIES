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
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
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
        
    
        
        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body,
        :root.light-theme .ambient-bg {
            background: #f8fafc !important;
        }
        :root.light-theme .glass-panel,
        :root.light-theme .neo-card,
        :root.light-theme .summary-card,
        :root.light-theme .filter-panel,
        :root.light-theme .chart-card,
        :root.light-theme .kpi-card,
        :root.light-theme .premium-header,
        :root.light-theme .top-header,
        :root.light-theme .header-area {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
        }
        :root.light-theme .glass-panel *,
        :root.light-theme .neo-card *,
        :root.light-theme .summary-card * {
            text-shadow: none !important;
        }
        :root.light-theme table.pro-table th,
        :root.light-theme .pos-grid thead th,
        :root.light-theme .ledger-table th,
        :root.light-theme .report-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        :root.light-theme table.pro-table td,
        :root.light-theme .pos-grid tbody td,
        :root.light-theme .ledger-table td,
        :root.light-theme .report-table td {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(0,0,0,0.08) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td,
        :root.light-theme .pos-grid tbody tr:hover td,
        :root.light-theme .ledger-table tbody tr:hover td,
        :root.light-theme .report-table tbody tr:hover td {
            background: rgba(0,0,0,0.02) !important;
        }
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .tot-lbl,
        :root.light-theme .lbl,
        :root.light-theme label,
        :root.light-theme .table-header {
            color: #334155 !important;
        }
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #0f172a !important;
        }
        :root.light-theme .h-form-group,
        :root.light-theme .control-panel {
            background: transparent !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme .modern-btn {
            color: #1e293b !important;
        }
        :root.light-theme .modern-btn:hover {
            color: #0f172a !important;
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }
\n</style>
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
            <a data-href="settings.php" onclick="window.location.href='settings.php'" class="btn-action" style="background: rgba(255,255,255,0.1); text-decoration:none; padding:8px 15px; border-radius:8px; color:white;">↩ العودة للإعدادات</a>
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
    <script defer src="js/app.js?v=1783054966.27798"></script>
    <script>
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) { window.location.href = 'login.php'; return; }
            
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (user.role !== 'admin' && user.securityLevel < 9) {
                alert('صلاحيات غير كافية! هذه الشاشة مخصصة لمدير النظام فقط.');
                window.location.href = 'dashboard-pro.php';
            }
        });

        function exportDatabase() {
            if (Object.keys(window.DB_STORE).length === 0) {
                alert("لا توجد بيانات للنسخ!");
                return;
            }

            // Confirm
            if(!confirm("سيتم الآن تجميع قاعدة البيانات وتنزيلها كملف. هل ترغب بالاستمرار؟")) return;

            const dateStr = new Date().toISOString().split('T')[0];
            const fileName = `ERP_Backup_${dateStr}.bak.json`;

            // Prepare Data from Memory Store
            const backupData = JSON.stringify(window.DB_STORE);

            // Create Blob and trigger download
            const blob = new Blob([backupData], { type: "application/json" });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            window.logSystemAction('Database Export', 'system', 'تصدير نسخة احتياطية لقاعدة البيانات بالكامل');
            alert("✅ تم تصدير النسخة الاحتياطية بنجاح!");
        }

        function importDatabase(event) {
            const file = event.target.files[0];
            if (!file) return;

            if(!confirm("⚠️ تحذير خطير: استعادة النسخة الاحتياطية ستمسح كافة البيانات الحالية التي أُدخلت بعد تاريخ النسخة. هل أنت متأكد 100% أنك تريد الاستمرار؟")) {
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = async function(e) {
                try {
                    const data = JSON.parse(e.target.result);
                    
                    if (typeof data !== 'object' || Array.isArray(data)) {
                        throw new Error("ملف غير صالح");
                    }

                    // Show loader
                    document.body.innerHTML += `<div id="loader" style="position:fixed; top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:99999;display:flex;align-items:center;justify-content:center;color:white;font-family:Tajawal;font-size:2rem;font-weight:bold;">جاري مسح القديم واستعادة البيانات... الرجاء الانتظار</div>`;

                    // 1. Clear Current localforage completely for this tenant
                    const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                    const keys = await localforage.keys();
                    for (let k of keys) {
                        if (k.startsWith(prefix) || prefix === '') {
                            await localforage.removeItem(k);
                        }
                    }

                    // 2. Populate new data
                    for (let [key, val] of Object.entries(data)) {
                        // val is already parsed if it was stored parsed? No, the system stores stringified JSON.
                        // wait, DB_STORE contains stringified JSON.
                        await localforage.setItem(key, val);
                    }

                    window.logSystemAction('Database Import', 'system', 'استعادة نسخة احتياطية من ملف خارجي');

                    alert("🎉 تم استعادة البيانات بنجاح! سيتم إعادة تحميل النظام الآن.");
                    window.location.href = 'dashboard-pro.php';
                } catch (err) {
                    console.error(err);
                    alert("❌ خطأ: ملف النسخة الاحتياطية تالف أو غير مدعوم.");
                    event.target.value = '';
                    const loader = document.getElementById('loader');
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
                document.body.innerHTML += `<div id="loader" style="position:fixed; top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:99999;display:flex;align-items:center;justify-content:center;color:white;font-family:Tajawal;font-size:2rem;font-weight:bold;">جاري مسح القديم واستعادة البيانات من السحاب... الرجاء الانتظار</div>`;

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
                    await localforage.setItem(key, val);
                }

                window.logSystemAction('Cloud Database Import', 'system', 'استعادة نسخة احتياطية من السحاب');

                alert("🎉 تم استعادة البيانات من السحاب بنجاح! سيتم إعادة تحميل النظام الآن.");
                window.location.href = 'dashboard-pro.php';
            } catch (err) {
                console.error(err);
                alert("❌ خطأ: " + err.message);
                btn.disabled = false;
                btn.innerText = 'تحميل واستعادة من السحاب';
                const loader = document.getElementById('loader');
                if(loader) loader.remove();
            }
        }
    </script>
</body>
</html>
