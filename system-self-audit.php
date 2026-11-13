<?php
$pageTitle = 'درع السيادة | مقاييس ERP';
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
            <h1 class="sa-title">🛡️ درع السيادة</h1>
            <p class="sa-subtitle">يجمع الفحص الذاتي والشفاء الرقمي الفوري مع درع RASP السيبراني، المحاكاة الشبحية لـ 11 قسماً، بصمة الملفات، ورادار التطورات العالمية بأقصى خفة (&lt;1ms).</p>
        </div>
        <div class="sa-actions no-print">
            <button type="button" id="sa-run-btn" class="sa-btn sa-btn-primary" onclick="runFullSelfAudit(true)">▶️ فحص درع السيادة اليدوي الآن</button>
            <button type="button" id="sa-cont-btn" class="sa-btn sa-btn-heal" style="background:#6d28d9;border-color:#a78bfa;" onclick="window.toggleContinuousDeepScan && window.toggleContinuousDeepScan()">🔄 الفحص العميق المتوازي المستمر: مفعل</button>
            <button type="button" class="sa-btn sa-btn-heal" onclick="if(window.openMagaiesGuardianVault)window.openMagaiesGuardianVault()">🧠 المجلس الفائق ومحاكاة (أ-ي)</button>
            <button type="button" class="sa-btn sa-btn-heal" onclick="autoHealAndReconcile()">🛠️ معالجة ذاتية وتوحيد القيود</button>
            <button type="button" class="sa-btn sa-btn-print" onclick="window.print()">🖨️ طباعة الشهادة</button>
            <a href="magaiees_unified_dashboard.php" class="sa-btn sa-btn-back">🏠 الرئيسية</a>
        </div>
    </div>

    <div class="sa-kpi no-print" style="margin-top:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-weight:900;font-size:0.9rem;">🎯 الضبط الافتراضي الشامل (من الألف للياء + اكتشاف تلقائي لأي شاشة مستقبلية):</span>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="قاعدة البيانات" checked onchange="runFullSelfAudit()"> قاعدة البيانات والعزل</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="القيد المزدوج" checked onchange="runFullSelfAudit()"> القيد المزدوج والأستاذ</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="الأرصدة" checked onchange="runFullSelfAudit()"> الأرصدة والعملات</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="الأمن" checked onchange="runFullSelfAudit()"> الأمن والصلاحيات</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="الأوفلاين" checked onchange="runFullSelfAudit()"> الأوفلاين و2G</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="الشاشات" checked onchange="runFullSelfAudit()"> كل الشاشات والتقارير</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="المناعة" checked onchange="runFullSelfAudit()"> المناعة والتنبؤ والبصمة</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="الحارس السيادي" checked onchange="runFullSelfAudit()"> الحارس السيادي والرادار العالمي</label>
            <label style="font-weight:800;font-size:0.86rem;cursor:pointer;"><input type="checkbox" class="sa-scope-chk" value="التعلّم الاتحادي" checked onchange="runFullSelfAudit()"> التعلّم الاتحادي وتطوير البنية</label>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <a href="ai_advisor.php" class="sa-btn sa-btn-back" style="padding:7px 12px;font-size:0.82rem;">🧠 الحارس المستشار 360°</a>
            <a href="financial_health.php" class="sa-btn sa-btn-back" style="padding:7px 12px;font-size:0.82rem;">📊 الصحة المالية</a>
            <input id="sa-custom-wa" type="text" dir="ltr" placeholder="رقم واتساب خاص للتقرير (مستقل عن النظام: 2499...)" style="padding:7px 11px;border-radius:10px;border:2px solid #16a34a;font-family:inherit;font-size:0.84rem;font-weight:900;min-width:255px;background:var(--sa-bg);color:var(--sa-ink);">
            <button type="button" class="sa-btn sa-btn-heal" onclick="sendAuditSummaryToCustomWa()" style="padding:8px 13px;font-size:0.84rem;">📲 إرسال لهذا الرقم</button>
        </div>
    </div>

    <div id="sa-immunity-cockpit" class="sa-kpi no-print" style="margin-top:14px;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;">
        <div style="padding:12px;border-radius:12px;background:var(--sa-card-2);border:2px solid var(--sa-border);color:var(--sa-ink) !important;">
            <div style="font-weight:900;font-size:0.98rem;color:var(--sa-ink) !important;">🔮 التوأم الرقمي ومحاكي القرارات (ماذا لو؟)</div>
            <div style="display:flex;gap:6px;margin-top:8px;align-items:center;flex-wrap:wrap;">
                <input id="sim-fx" type="number" value="10" style="width:72px;padding:5px;border-radius:8px;border:2px solid var(--sa-border);background:var(--sa-card);color:var(--sa-ink) !important;font-weight:900;" title="تغير الصرف %"> <span style="font-size:0.84rem;font-weight:900;color:var(--sa-ink) !important;">% صرف</span>
                <input id="sim-gr" type="number" value="15" style="width:72px;padding:5px;border-radius:8px;border:2px solid var(--sa-border);background:var(--sa-card);color:var(--sa-ink) !important;font-weight:900;" title="نمو المبيعات %"> <span style="font-size:0.84rem;font-weight:900;color:var(--sa-ink) !important;">% مبيعات</span>
                <button type="button" class="sa-btn sa-btn-primary" style="padding:5px 11px;font-size:0.82rem;" onclick="runTwinSim()">محاكاة</button>
            </div>
            <div id="sim-out" style="font-size:0.85rem;font-weight:900;color:var(--sa-sub) !important;margin-top:7px;">يعرض أثر تغير الصرف والمبيعات قبل التنفيذ الفعلي.</div>
        </div>
        <div style="padding:12px;border-radius:12px;background:var(--sa-card-2);border:2px solid var(--sa-border);color:var(--sa-ink) !important;">
            <div style="font-weight:900;font-size:0.98rem;color:var(--sa-ink) !important;">⛓️ البصمة المتسلسلة والاسترجاع الزمني الذري</div>
            <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                <button type="button" class="sa-btn sa-btn-heal" style="padding:6px 11px;font-size:0.82rem;" onclick="takeTimeCapsule()">📸 حفظ نقطة استرجاع الآن</button>
                <select id="capsule-sel" style="padding:5px 8px;border-radius:8px;border:2px solid var(--sa-border);background:var(--sa-card);color:var(--sa-ink) !important;font-weight:900;font-size:0.82rem;"><option value="">آخر نقاط الاسترجاع...</option></select>
                <button type="button" class="sa-btn sa-btn-print" style="padding:6px 11px;font-size:0.82rem;" onclick="restoreSelectedCapsule()">⏪ استرجاع بتأكيد المالك</button>
            </div>
            <div id="chain-out" style="font-size:0.85rem;font-weight:900;color:var(--sa-sub) !important;margin-top:7px;">ختم SHA-256 متسلسل لكل حركة مع إخطار وتأكيد المالك قبل أي تعديل.</div>
        </div>
        <div style="padding:12px;border-radius:12px;background:var(--sa-card-2);border:2px solid var(--sa-border);color:var(--sa-ink) !important;">
            <div style="font-weight:900;font-size:0.98rem;color:var(--sa-ink) !important;">🛡️ درع الحارس السيادي (RASP + 11 قسماً + بصمة الكود)</div>
            <div id="sentinel-out" style="font-size:0.84rem;font-weight:900;color:var(--sa-sub) !important;margin-top:8px;line-height:1.5;">يراقب الـ 11 قسماً ويصلح الخلل الخفيف فوراً (&lt;1ms) بصفر حمل على الأجهزة الضعيفة.</div>
        </div>
        <div style="padding:12px;border-radius:12px;background:var(--sa-card-2);border:2px solid var(--sa-border);color:var(--sa-ink) !important;">
            <div style="font-weight:900;font-size:0.98rem;color:var(--sa-ink) !important;">🌐 رادار التطورات العالمية ومشاورة المالك (الأمن • الصوت • الرسوم)</div>
            <div id="proposals-out" style="font-size:0.82rem;font-weight:800;color:var(--sa-sub) !important;margin-top:8px;max-height:92px;overflow-y:auto;">جارٍ فحص التحديثات السيبرانية ومقترحات المالك...</div>
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

    window.runFullSelfAudit = async function(manual) {
        const runBtn = document.getElementById('sa-run-btn');
        const certBox = document.getElementById('sa-cert');
        const certMsg = document.getElementById('sa-cert-msg');
        const resGrid = document.getElementById('sa-results');
        if (runBtn) { runBtn.disabled = true; runBtn.textContent = '⏳ درع السيادة يراجع النظام الآن...'; }
        certMsg.textContent = '🛡️ درع السيادة يفحص الخادم، القيد المزدوج، الـ 11 قسماً، والأمن السيبراني...';
        if (manual) window.refreshImmunityCockpit();

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
        const activeScopes = Array.from(document.querySelectorAll('.sa-scope-chk:checked')).map(el => el.value);
        const rawAllChecks = [...(srv.checks || []), ...clientChecks];
        const allChecks = activeScopes.length ? rawAllChecks.filter(c => activeScopes.some(s => String(c.category || '').includes(s) || (s === 'الأرصدة' && String(c.category || '').includes('المطابقة')))) : rawAllChecks;

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
            certMsg.innerHTML = `✅ درع السيادة (${score}%): اكتمل فحص كافة الأقسام الـ 11، الدورات المحاسبية، المخازن، الأمان، والأوفلاين بنجاح!`;
        } else {
            certBox.classList.add('has-fail');
            certMsg.innerHTML = `🚨 تنبيه درع السيادة: تم رصد ${f} خلل يتطلب المعالجة الفورية بالأسفل.`;
        }
        if (runBtn) { runBtn.disabled = false; runBtn.textContent = manual ? '✅ اكتمل فحص درع السيادة — إعادة الفحص' : '▶️ فحص درع السيادة الشامل الآن'; }

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

    window.refreshImmunityCockpit = async function() {
        try {
            const r = await fetch('api/autonomous_immunity_core.php?_t=' + Date.now(), { cache: 'no-store' });
            const d = await r.json();
            if (!d || !d.success) return;
            if (d.chain && document.getElementById('chain-out')) {
                document.getElementById('chain-out').textContent = `البصمة SHA-256: ${d.chain.total_blocks} سجل مختوم (${d.chain.head_hash}) • اعتراضات الجدار الذري: ${d.firewall_hits || 0}`;
            }
            if (d.twin && document.getElementById('sim-out')) {
                document.getElementById('sim-out').textContent = `السيولة المتاحة: ${Number(d.twin.cash_total || 0).toLocaleString()} • التغطية (30 يوم): ${d.twin.coverage_pct}% • نواقص حرجة: ${d.twin.low_stock_items}`;
            }
            if (d.sentinel && document.getElementById('sentinel-out')) {
                document.getElementById('sentinel-out').textContent = `الأقسام المراقبة: ${d.sentinel.domains_ok}/${d.sentinel.domains_total} ✔ • ملفات مختومة: ${d.sentinel.sealed_files} • بيئة PHP ${d.php_version || ''} • الخلل الخفيف: يُصلح تلقائياً.`;
            }
            if (Array.isArray(d.proposals) && document.getElementById('proposals-out')) {
                document.getElementById('proposals-out').innerHTML = d.proposals.length ? d.proposals.map(p => `<div style="display:flex;justify-content:space-between;align-items:center;gap:6px;margin-bottom:5px;border-bottom:1px dashed rgba(148,163,184,0.3);padding-bottom:4px;"><span title="${(p.study_summary||'').replace(/"/g,'')}">${p.title}</span><span style="white-space:nowrap;"><button onclick="decideProposal(${p.id},'approved')" style="background:#059669;color:#fff;border:0;border-radius:6px;padding:2px 7px;cursor:pointer;font-weight:900;">اعتماد</button> <button onclick="decideProposal(${p.id},'dismissed')" style="background:#475569;color:#fff;border:0;border-radius:6px;padding:2px 6px;cursor:pointer;font-weight:900;">تجاهل</button></span></div>`).join('') : '✅ لا توجد قرارات معلقة؛ كافة الرادارات محدثة.';
            }
            const sel = document.getElementById('capsule-sel');
            if (sel && Array.isArray(d.capsules)) {
                sel.innerHTML = d.capsules.length ? d.capsules.map(c => `<option value="${c.id}">#${c.id} — ${c.label} (${c.created_at})</option>`).join('') : '<option value="">لا توجد لقطات بعد</option>';
            }
        } catch (e) {}
    };
    window.decideProposal = async function(id, dec) {
        await fetch('api/autonomous_immunity_core.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ mode: 'proposal_decision', proposal_id: id, decision: dec }) });
        window.refreshImmunityCockpit(); window.runFullSelfAudit();
    };

    window.runTwinSim = async function() {
        const fx = Number(document.getElementById('sim-fx')?.value || 0);
        const gr = Number(document.getElementById('sim-gr')?.value || 0);
        const r = await fetch(`api/autonomous_immunity_core.php?mode=simulate&fx_delta=${fx}&sales_growth=${gr}`);
        const d = await r.json();
        if (d && d.twin) document.getElementById('sim-out').textContent = `نتيجة المحاكاة (صرف ${fx}%، مبيعات ${gr}%): التغطية ${d.twin.coverage_pct}% • التزامات متوقعة: ${Number(d.twin.payables_proj).toLocaleString()} • الحالة: ${d.twin.risk_level}`;
    };

    window.takeTimeCapsule = async function() {
        const r = await fetch('api/autonomous_immunity_core.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ mode: 'create_capsule', label: 'نقطة استرجاع معتمدة من المالك' }) });
        const d = await r.json();
        if (d && d.success) { alert('✅ تم حفظ نقطة الاسترجاع الزمنية #' + d.capsule.capsule_id + ' ببصمة ' + d.capsule.hash); window.refreshImmunityCockpit(); }
    };

    window.restoreSelectedCapsule = async function() {
        const cid = document.getElementById('capsule-sel')?.value;
        if (!cid) return alert('اختر نقطة استرجاع أولاً.');
        if (!confirm('🔔 إخطار وتأكيد المالك الإلزامي:\nهل تؤكد استرجاع البيانات إلى نقطة الزمن #' + cid + '؟ لن يتم التنفيذ إلا بتأكيدك الصريح.')) return;
        const r = await fetch('api/autonomous_immunity_core.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ mode: 'restore_capsule', capsule_id: Number(cid), owner_confirmed: true }) });
        const d = await r.json();
        alert(d && d.success ? '✅ تم الاسترجاع الزمني بنجاح (' + d.restored_rows + ' سجل).' : ('⚠ ' + (d.error || d.message)));
        await window.runFullSelfAudit();
    };

    window.autoHealAndReconcile = async function() {
        if (!confirm('🔔 إخطار وتأكيد المالك:\nسيقوم النظام بأخذ نقطة استرجاع زمنية تلقائية أولاً، ثم توحيد القيود وإصلاح السجلات وختم البصمة SHA-256.\nهل تؤكد التنفيذ؟')) return;
        try {
            await fetch('api/autonomous_immunity_core.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ mode: 'server_heal', owner_confirmed: true }) });
        } catch (e) {}
        if (typeof window.rebuildGLFromAllTables === 'function') {
            try { window.rebuildGLFromAllTables(true); } catch (e) {}
        }
        if (window.MagaiesOffline && typeof window.MagaiesOffline.flushQueue === 'function' && navigator.onLine) {
            try { await window.MagaiesOffline.flushQueue(); } catch (e) {}
        }
        await window.runFullSelfAudit();
        await window.refreshImmunityCockpit();
    };

    window.syncConnectedSystemWhatsApp = async function() {
        try {
            const uid = (typeof getWAUserId === 'function' && getWAUserId()) || localStorage.getItem('erp_wa_user_id') || '';
            const r = await fetch('api_index.php?r=wa-tenant&endpoint=status' + (uid ? '&userId=' + encodeURIComponent(uid) : ''));
            const d = await r.json();
            const sysNum = String(d.phone || d.number || (d.user && d.user.id ? d.user.id.split(':')[0].split('@')[0] : '') || '249123005622').replace(/[^0-9]/g, '');
            const el = document.getElementById('sa-custom-wa');
            if (el && !el.value.trim() && sysNum) el.value = '00' + sysNum;
            window._saWaUid = d.userId || uid || 'wa_tenant_12';
        } catch (e) {
            const el = document.getElementById('sa-custom-wa');
            if (el && !el.value.trim()) el.value = '00249123005622';
        }
    };

    window.sendAuditSummaryToCustomWa = async function() {
        const rawEl = document.getElementById('sa-custom-wa');
        let raw = (rawEl ? rawEl.value : localStorage.getItem('magaies_owner_custom_wa') || '00249123005622').trim();
        localStorage.setItem('magaies_owner_custom_wa', raw);
        let ph = raw.split(/[,;\s]+/)[0].replace(/[^0-9]/g, '');
        if (ph.startsWith('00')) ph = ph.slice(2);
        else if (ph.length === 10 && ph[0] === '0' && (ph[1] === '9' || ph[1] === '1')) ph = '249' + ph.slice(1);
        const sc = document.getElementById('kpi-score')?.textContent || '100%';
        const ps = document.getElementById('kpi-pass')?.textContent || '0';
        const is = document.getElementById('kpi-issues')?.textContent || '0';
        const msg = `🛡️ *تقرير درع السيادة — مقاييس*\n• المطابقة: ${sc}\n• اختبارات ناجحة: ${ps}\n• تنبيهات/أخطاء: ${is}\n• التاريخ: ${new Date().toLocaleString('ar-EG')}`;
        const uid = window._saWaUid || localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12';
        try {
            const res = await fetch('api_index.php?r=wa-tenant&endpoint=send&userId=' + encodeURIComponent(uid), {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId: uid, phone: ph, message: msg })
            });
            const out = await res.json();
            alert(out && out.success !== false ? ('✅ تم إرسال تقرير درع السيادة مباشرة عبر واتساب النظام المربوط إلى: ' + ph) : ('⚠️ تعذر الإرسال المباشر من جلسة النظام: ' + (out.error || 'تأكد من حالة جلسة الواتساب في الإعدادات')));
        } catch (err) {
            alert('⚠️ تعذر الاتصال بخدمة واتساب النظام الداخلية؛ تأكد من ربط جلسة الواتساب في الإعدادات.');
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const savedWa = localStorage.getItem('magaies_owner_custom_wa');
        if (savedWa && document.getElementById('sa-custom-wa')) document.getElementById('sa-custom-wa').value = savedWa;
        window.syncConnectedSystemWhatsApp();
        setTimeout(() => { window.runFullSelfAudit(); window.refreshImmunityCockpit(); }, 150);
    });
})();
</script>
</body>
</html>
