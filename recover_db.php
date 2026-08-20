<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>أداة استعادة بيانات مقاييس</title>
    <script src="js/localforage.min.js"></script>
    <style>
        body { font-family: Tahoma, sans-serif; margin: 20px; background: #f8fafc; color: #0f172a; }
        .box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        button { background: #0284c7; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0369a1; }
        #log { background: #1e293b; color: #38bdf8; padding: 15px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; height: 300px; overflow-y: auto; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h2>أداة الطوارئ لاستعادة البيانات (Data Recovery Tool)</h2>
        <p>تقوم هذه الأداة بالبحث في جميع طبقات الذاكرة العميقة (الأساسية، البيئة الحالية، والنسخ الاحتياطية) واستخراج أكبر وأحدث نسخة من بياناتك وإعادتها.</p>
        <button onclick="startRecovery()">ابدأ الاستعادة الآن</button>
    </div>
    <div id="log">في انتظار بدء الاستعادة...</div>

    <script>
        function log(msg, type = '') {
            const l = document.getElementById('log');
            l.innerHTML += `<span class="${type}">${msg}</span>
`;
            l.scrollTop = l.scrollHeight;
        }

        async function startRecovery() {
            log("بدأ فحص الذاكرة...");
            const keysToRecover = [
                'erp_items_v1', 
                'erp_sales_invoices_v1', 
                'erp_purchase_invoices_v1',
                'erp_warehouses_v1',
                'erp_warehouse_transfers_v1'
            ];

            const tenantPrefix = 'tenant_1_';

            for (let baseKey of keysToRecover) {
                log(`
--- جاري البحث عن بيانات: ${baseKey} ---`);
                const tKey = tenantPrefix + baseKey;
                
                let datasets = [];

                // 1. Check localStorage base key
                const lsBase = localStorage.getItem(baseKey);
                if (lsBase && lsBase !== '[]' && lsBase !== '{}') datasets.push({ source: 'localStorage (Base)', data: JSON.parse(lsBase), len: JSON.parse(lsBase).length });

                // 2. Check localStorage tenant key
                const lsTenant = localStorage.getItem(tKey);
                if (lsTenant && lsTenant !== '[]' && lsTenant !== '{}') datasets.push({ source: 'localStorage (Tenant)', data: JSON.parse(lsTenant), len: JSON.parse(lsTenant).length });

                // 3. Check localforage base key
                try {
                    const lfBase = await localforage.getItem(baseKey);
                    if (lfBase && lfBase !== '[]' && lfBase !== '{}') datasets.push({ source: 'IndexedDB (Base)', data: JSON.parse(lfBase), len: JSON.parse(lfBase).length });
                } catch(e){}

                // 4. Check localforage tenant key
                try {
                    const lfTenant = await localforage.getItem(tKey);
                    if (lfTenant && lfTenant !== '[]' && lfTenant !== '{}') datasets.push({ source: 'IndexedDB (Tenant)', data: JSON.parse(lfTenant), len: JSON.parse(lfTenant).length });
                } catch(e){}

                // 5. Check auto backups
                const backupsStr = localStorage.getItem('erp_auto_backups');
                if (backupsStr) {
                    try {
                        const backups = JSON.parse(backupsStr);
                        backups.forEach((b, i) => {
                            if (b.data && b.data[baseKey]) {
                                const bd = b.data[baseKey];
                                if (bd && bd.length > 0) datasets.push({ source: `Backup ${i+1} (${b.date}) (Base)`, data: bd, len: bd.length });
                            }
                            if (b.data && b.data[tKey]) {
                                const bd = b.data[tKey];
                                if (bd && bd.length > 0) datasets.push({ source: `Backup ${i+1} (${b.date}) (Tenant)`, data: bd, len: bd.length });
                            }
                        });
                    } catch(e){}
                }

                if (datasets.length === 0) {
                    log(`لم يتم العثور على أي بيانات مسجلة لـ ${baseKey}!`, 'error');
                    continue;
                }

                // Sort datasets by length descending to find the largest/most complete data
                datasets.sort((a, b) => b.len - a.len);

                log(`تم العثور على ${datasets.length} نسخة.`);
                datasets.forEach(d => log(`- ${d.source}: يحتوي على ${d.len} سجل`));

                const best = datasets[0];
                log(`>> سيتم استعادة النسخة الأكبر من ${best.source} (${best.len} سجل)`, 'success');

                const finalStr = JSON.stringify(best.data);
                
                // FORCE SAVE TO BOTH TENANT AND BASE EVERYWHERE
                localStorage.setItem(tKey, finalStr);
                localStorage.setItem(baseKey, finalStr);
                try { await localforage.setItem(tKey, finalStr); } catch(e){}
                try { await localforage.setItem(baseKey, finalStr); } catch(e){}

                log(`تمت الاستعادة بنجاح!`, 'success');
            }

            log(`
===========================================`);
            log(`اكتملت عملية الاستعادة الشاملة بنجاح!`, 'success');
            log(`يمكنك الآن الرجوع إلى شاشة المخزن (inventory-report.php) وتحديث الصفحة (Ctrl+F5).`);
        }
    </script>
</body>
</html>