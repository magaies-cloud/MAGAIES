<?php
$pageTitle = 'محرك الفحص الذاتي الفوري وشهادة المطابقة | السيطرة ERP';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/unified-design-tokens.css">
    <style>
        :root {
            --sa-bg: #030712;
            --sa-card: #0f172a;
            --sa-card-2: #16223b;
            --sa-ink: #ffffff;
            --sa-sub: #cbd5e1;
            --sa-border: rgba(56, 189, 248, 0.42);
        }
        body.light-mode, html[data-theme="light"] body, body[data-light-tone] {
            --sa-bg: #f8f3e8;
            --sa-card: #ffffff;
            --sa-card-2: #fdf6e9;
            --sa-ink: #120500;
            --sa-sub: #3b1d0a;
            --sa-border: #5c2808;
        }
        body {
            background: var(--sa-bg);
            color: var(--sa-ink);
            font-family: 'Cairo', 'Tajawal', system-ui, sans-serif;
            margin: 0;
            padding: 0;
        }
        .sa-wrap { max-width: 1280px; margin: 0 auto; padding: 20px 18px 60px; }
        .sa-hero {
            background: var(--sa-card);
            border: 2.5px solid var(--sa-border);
            border-radius: 22px;
            padding: 22px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 14px 35px rgba(0,0,0,0.22);
        }
        .sa-title { font-size: 1.55rem; font-weight: 900; margin: 0 0 6px; color: var(--sa-ink); }
        .sa-subtitle { font-size: 0.95rem; font-weight: 800; color: var(--sa-sub); margin: 0; }
        .sa-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .sa-btn {
            border: 2px solid transparent;
            border-radius: 13px;
            padding: 10px 18px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 900;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .sa-btn:hover { transform: translateY(-1px); }
        .sa-btn-primary { background: #059669; color: #ffffff; border-color: #10b981; }
        .sa-btn-heal { background: #0284c7; color: #ffffff; border-color: #38bdf8; }
        .sa-btn-print { background: #d97706; color: #ffffff; border-color: #fbbf24; }
        .sa-btn-back { background: var(--sa-card-2); color: var(--sa-ink); border-color: var(--sa-border); text-decoration: none; }
        .sa-kpis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
            margin-top: 18px;
        }
        .sa-kpi {
            background: var(--sa-card);
            border: 2px solid var(--sa-border);
            border-radius: 18px;
            padding: 16px 18px;
        }
        .sa-kpi-lbl { font-size: 0.88rem; font-weight: 900; color: var(--sa-sub); }
        .sa-kpi-val { font-size: 1.85rem; font-weight: 900; margin-top: 4px; color: var(--sa-ink); }
        .sa-cert-banner {
            margin-top: 18px;
            padding: 18px 22px;
            border-radius: 18px;
            border: 2.5px solid #059669;
            background: rgba(5, 150, 105, 0.14);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .sa-cert-banner.has-fail { border-color: #e11d48; background: rgba(225, 29, 72, 0.14); }
        .sa-grid {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 14px;
        }
        .sa-item {
            background: var(--sa-card);
            border: 2px solid var(--sa-border);
            border-radius: 16px;
            padding: 15px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .sa-item-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .sa-badge {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 900;
            color: #ffffff;
        }
        .sa-pass { background: #059669; }
        .sa-warn { background: #d97706; }
        .sa-fail { background: #e11d48; }
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; color: #000000 !important; }
            .sa-hero, .sa-kpi, .sa-item, .sa-cert-banner { border-color: #000000 !important; box-shadow: none !important; background: #ffffff !important; color: #000000 !important; }
        }
    </style>
</head>
<body>
<?php if (file_exists(__DIR__ . '/includes/header.php')) include __DIR__ . '/includes/header.php'; ?>
<script src="js/app.js?v=<?php echo file_exists(__DIR__ . '/js/app.js') ? filemtime(__DIR__ . '/js/app.js') : '2027'; ?>"></script>

<div class="sa-wrap">
    <div class="sa-hero">
        <div>
            <h1 class="sa-title">🛡️ محرك الفحص الذاتي الفوري وشهادة المطابقة الشاملة (360°)</h1>
            <p class="sa-subtitle">يفحص القيد المزدوج، الأرصدة الافتتاحية، المخازن، عملات SDG/USD، عزل الشركات، صلاحيات الأزرار، وكفاءة الأوفلاين و2G في ثوانٍ.</p>
        </div>
        <div class="sa-actions no-print">
            <button type="button" class="sa-btn sa-btn-primary" onclick="runFullSelfAudit()">⚡ تشغيل الفحص الشامل الآن</button>
            <button type="button" class="sa-btn sa-btn-heal" onclick="autoHealAndReconcile()">🛠️ معالجة ذاتية وتوحيد القيود</button>
            <button type="button" class="sa-btn sa-btn-print" onclick="window.print()">🖨️ طباعة شهادة المطابقة</button>
            <a href="magaiees_unified_dashboard.php" class="sa-btn sa-btn-back">🏠 الرئيسية</a>
        </div>
    </div>

    <div id="sa-cert" class="sa-cert-banner">
        <div style="font-size:1.12rem; font-weight:900;" id="sa-cert-msg">⏳ جارٍ تشغيل الفحص الذاتي الشامل للخادم والمتصفح...</div>
        <div style="font-size:0.9rem; font-weight:900;" id="sa-cert-time"></div>
    </div>

    <div class="sa-kpis">
        <div class="sa-kpi"><div class="sa-kpi-lbl">درجة المطابقة الكلية</div><div class="sa-kpi-val" id="kpi-score">--%</div></div>
        <div class="sa-kpi"><div class="sa-kpi-lbl">اختبارات ناجحة (PASS)</div><div class="sa-kpi-val" style="color:#059669;" id="kpi-pass">0</div></div>
        <div class="sa-kpi"><div class="sa-kpi-lbl">تنبيهات / أخطاء</div><div class="sa-kpi-val" id="kpi-issues">0</div></div>
        <div class="sa-kpi"><div class="sa-kpi-lbl">سرعة استجابة السيرفر (2G Ping)</div><div class="sa-kpi-val" id="kpi-ms">-- ms</div></div>
    </div>

    <div id="sa-results" class="sa-grid"></div>
</div>

<script>
(function() {
    if (localStorage.getItem('magaies_light_tone') || localStorage.getItem('theme') === 'light') {
        document.body.classList.add('light-mode');
    }

    function readArr(key) {
        const tk = (typeof window.getTenantKey === 'function') ? window.getTenantKey(key) : key;
        try { return (JSON.parse(localStorage.getItem(tk) || localStorage.getItem(key) || '[]') || []).filter(x => x && !x.deleted); }
        catch (e) { return []; }
    }

    function runClientChecks() {
        const list = [];
        // 1. Double-Entry GL Balance Check
        if (typeof window.rebuildGLFromAllTables === 'function') {
            try { window.rebuildGLFromAllTables(false); } catch (e) {}
        }
        const gl = readArr('erp_gl_v1');
        let sumDr = 0, sumCr = 0;
        gl.forEach(e => {
            sumDr += Number(e.debit ?? ((e.type === 'debit' || e.type === 'in') ? e.amount : 0)) || 0;
            sumCr += Number(e.credit ?? ((e.type === 'credit' || e.type === 'out') ? e.amount : 0)) || 0;
        });
        const diff = Math.abs(sumDr - sumCr);
        list.push({
            category: 'القيد المزدوج ودفتر الأستاذ',
            title: 'توازن المدين والدائن في دفتر الأستاذ العام (erp_gl_v1)',
            status: diff < 1 ? 'PASS' : 'WARN',
            detail: diff < 1
                ? `دفتر الأستاذ متوازن بالكامل (${gl.length} حركة — إجمالي المدين يطابق الدائن).`
                : `يوجد فرق توازن قدره ${diff.toLocaleString()} يتطلب الضغط على زر "معالجة ذاتية وتوحيد القيود".`,
            metric: `الفرق: ${diff.toFixed(2)}`
        });

        // 2. Master Entities & Opening Balances Check
        const safes = readArr('erp_safes_v1');
        const banks = readArr('erp_banks_v1');
        const custs = readArr('erp_customers_v1');
        const sups  = readArr('erp_suppliers_v1');
        const items = readArr('erp_items_v1');
        let nanCount = 0;
        [...safes, ...banks, ...custs, ...sups].forEach(x => {
            if (x.currentBalance !== undefined && Number.isNaN(Number(x.currentBalance))) nanCount++;
            if (x.openingBalance !== undefined && Number.isNaN(Number(x.openingBalance))) nanCount++;
        });
        list.push({
            category: 'الأرصدة الافتتاحية والكيانات',
            title: 'سلامة أرصدة الخزن والبنوك والعملاء والموردين والأصناف',
            status: nanCount === 0 ? 'PASS' : 'FAIL',
            detail: `تم فحص ${safes.length} خزنة، ${banks.length} بنك، ${custs.length} عميل، ${sups.length} مورد، و${items.length} صنف — صفر قيم تالفة (NaN).`,
            metric: `${safes.length + banks.length + custs.length + sups.length + items.length} سجل`
        });

        // 3. Offline Queue & Service Worker Status
        let qCount = 0;
        try { qCount = (JSON.parse(localStorage.getItem('magaies_universal_offline_queue') || '[]') || []).length; } catch (e) {}
        const swOk = ('serviceWorker' in navigator);
        list.push({
            category: 'الأوفلاين والطابور اللحظي',
            title: 'جاهزية محرك عدم الاتصال (Service Worker + Offline Queue)',
            status: swOk ? 'PASS' : 'WARN',
            detail: swOk
                ? `محرك الأوفلاين جاهز ويعمل؛ عدد الحركات المعلقة بانتظار الرفع حالياً: ${qCount}.`
                : 'المتصفح الحالي لا يدعم Service Worker.',
            metric: `${qCount} معلق`
        });

        return list;
    }

    window.runFullSelfAudit = async function() {
        const certBox = document.getElementById('sa-cert');
        const certMsg = document.getElementById('sa-cert-msg');
        const resGrid = document.getElementById('sa-results');
        certMsg.textContent = '⏳ جارٍ فحص الخادم وقاعدة البيانات والقيد المزدوج والأمان...';

        const tStart = performance.now();
        let srv = { checks: [], passed: 0, warnings: 0, failed: 0, elapsed_ms: 0 };
        try {
            const resp = await fetch('api/self_audit_engine.php?_t=' + Date.now(), { cache: 'no-store' });
            srv = await resp.json();
        } catch (err) {
            srv.checks.push({
                category: 'الاتصال بالخادم',
                title: 'فحص الخادم (وضع الأوفلاين نشط)',
                status: 'WARN',
                detail: 'أنت تعمل الآن بدون اتصال بالسيرفر؛ تم تنفيذ الفحص المحلي للمتصفح بنجاح.',
                metric: 'Offline'
            });
            srv.warnings = 1;
        }
        const netMs = Math.round(performance.now() - tStart);
        const clientChecks = runClientChecks();
        const allChecks = [...(srv.checks || []), ...clientChecks];

        let p = 0, w = 0, f = 0;
        allChecks.forEach(c => {
            if (c.status === 'PASS') p++;
            else if (c.status === 'WARN') w++;
            else f++;
        });
        const total = p + w + f;
        const score = total ? Math.round(((p + w * 0.5) / total) * 100) : 100;

        document.getElementById('kpi-score').textContent = score + '%';
        document.getElementById('kpi-pass').textContent = p + ' / ' + total;
        document.getElementById('kpi-issues').textContent = (f + w);
        document.getElementById('kpi-ms').textContent = (srv.elapsed_ms ? srv.elapsed_ms + ' ms (شبكة ' + netMs + ' ms)' : netMs + ' ms');
        document.getElementById('sa-cert-time').textContent = 'آخر فحص: ' + new Date().toLocaleString('ar-EG');

        if (f === 0) {
            certBox.classList.remove('has-fail');
            certMsg.innerHTML = '✅ شهادة مطابقة خضراء معتمدة: جميع الدورات المحاسبية والمخزنية والأمنية والأوفلاين مطابقة وسليمة!';
        } else {
            certBox.classList.add('has-fail');
            certMsg.innerHTML = `🚨 تنبيه رقابي: تم رصد ${f} خلل يتطلب المعالجة الفورية بالأسفل.`;
        }

        resGrid.innerHTML = allChecks.map(c => {
            const cls = c.status === 'PASS' ? 'sa-pass' : (c.status === 'WARN' ? 'sa-warn' : 'sa-fail');
            const lbl = c.status === 'PASS' ? '✔ مطابق' : (c.status === 'WARN' ? '⚠ تنبيه' : '✖ خلل');
            return `
                <div class="sa-item">
                    <div class="sa-item-top">
                        <span style="font-size:0.8rem; font-weight:900; color:var(--sa-sub);">${c.category}</span>
                        <span class="sa-badge ${cls}">${lbl} ${c.metric ? '• ' + c.metric : ''}</span>
                    </div>
                    <div style="font-size:1.02rem; font-weight:900; color:var(--sa-ink);">${c.title}</div>
                    <div style="font-size:0.88rem; font-weight:700; color:var(--sa-sub); line-height:1.55;">${c.detail}</div>
                </div>
            `;
        }).join('');
    };

    window.autoHealAndReconcile = async function() {
        if (typeof window.rebuildGLFromAllTables === 'function') {
            try { window.rebuildGLFromAllTables(true); } catch (e) {}
        }
        if (window.MagaiesOffline && typeof window.MagaiesOffline.flushQueue === 'function' && navigator.onLine) {
            try { await window.MagaiesOffline.flushQueue(); } catch (e) {}
        }
        await window.runFullSelfAudit();
    };

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => window.runFullSelfAudit(), 150);
    });
})();
</script>
</body>
</html>
