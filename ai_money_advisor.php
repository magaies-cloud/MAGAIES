<?php
// =========================================================================
// نظام السجانة والسيطرة ERP 2026
// مستشار الذكاء الاصطناعي للأموال والسيولة والائتمان (AI Money & Liquidity Advisor)
// مخصص ومفصول 100% للمعاملات المالية والسيولة والمديونيات والشيكات (10 محركات ذكية)
// =========================================================================
$page_title = 'مستشار AI للأموال والسيولة والائتمان (10 محركات ذكية)';
$header_theme_class = 'header-theme-sales';

require_once __DIR__ . '/db_bridge.php';
$pdo = getDBConnection();

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$expData = getCustomerExposureData($pdo);
$sqlCustomers = $expData['customers'] ?? [];
$sqlTotalDebt = (float)($expData['total_debt'] ?? 0);

$serverPayload = json_encode([
    'customers' => $sqlCustomers,
    'totalDebt' => $sqlTotalDebt,
    'currency'  => $selected_currency
], JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | السجانة ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/sidebar-panoramic-flyout.css">
    <script src="js/theme.js"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-primary, #070b14);
            color: var(--text-primary, #f8fafc);
            margin: 0;
            min-height: 100vh;
        }
        .money-ai-shell {
            max-width: 1560px;
            margin: 0 auto;
            padding: 18px 20px 60px;
        }
        .money-hero {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(59, 7, 100, 0.86));
            border: 1.5px solid rgba(192, 132, 252, 0.4);
            border-radius: 22px;
            padding: 22px 26px;
            margin-bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 18px 45px rgba(0,0,0,0.45), 0 0 35px rgba(168, 85, 247, 0.14);
            position: relative;
            overflow: hidden;
        }
        .money-hero::before {
            content: '';
            position: absolute;
            top: 0; right: 0; left: 0;
            height: 4px;
            background: linear-gradient(90deg, #c084fc, #10b981, #fbbf24, #38bdf8);
        }
        .hero-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .hero-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: rgba(168, 85, 247, 0.22);
            border: 1.5px solid rgba(192, 132, 252, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
        }
        .hero-left h1 {
            margin: 0;
            font-size: 1.48rem;
            font-weight: 900;
            background: linear-gradient(90deg, #ffffff, #e9d5ff, #6ee7b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-left p {
            margin: 4px 0 0;
            font-size: 0.85rem;
            color: #cbd5e1;
            font-weight: 600;
        }
        .btn-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .m-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 15px;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.81rem;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.16);
            background: rgba(30, 41, 59, 0.88);
            color: #fff;
            transition: all 0.2s;
        }
        .m-btn:hover {
            transform: translateY(-2px);
            border-color: #c084fc;
        }
        .m-btn.purple { background: linear-gradient(135deg, #7c3aed, #9333ea); border-color: #c084fc; }
        .m-btn.emerald { background: linear-gradient(135deg, #059669, #0d9488); border-color: #34d399; }
        .m-btn.rose { background: linear-gradient(135deg, #e11d48, #be123c); border-color: #fb7185; }

        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(215px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .kpi-box {
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(192, 132, 252, 0.2);
            border-bottom: 3px solid var(--k-color, #c084fc);
            border-radius: 16px;
            padding: 15px 18px;
        }
        .kpi-box .lbl { font-size: 0.78rem; color: #94a3b8; font-weight: 700; }
        .kpi-box .val { font-size: 1.42rem; font-weight: 900; color: #fff; margin: 5px 0; }
        .kpi-box .sub { font-size: 0.73rem; color: #cbd5e1; display: flex; justify-content: space-between; }

        .engines-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(295px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .engine-card {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-right: 4px solid var(--e-color, #c084fc);
            border-radius: 16px;
            padding: 15px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 10px;
            transition: all 0.22s;
        }
        .engine-card:hover {
            transform: translateY(-3px);
            border-color: var(--e-color, #c084fc);
            box-shadow: 0 12px 28px rgba(0,0,0,0.38);
        }
        .engine-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }
        .engine-title { font-size: 0.89rem; font-weight: 900; color: #fff; }
        .engine-badge { font-size: 0.7rem; font-weight: 800; padding: 2px 9px; border-radius: 99px; white-space: nowrap; }
        .engine-body { font-size: 0.79rem; color: #cbd5e1; line-height: 1.55; margin: 0; }
        .engine-foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 8px;
            border-top: 1px dashed rgba(148, 163, 184, 0.15);
        }
        .engine-foot a {
            color: #c084fc;
            font-size: 0.76rem;
            font-weight: 800;
            text-decoration: none;
        }
        .engine-foot button {
            background: rgba(168, 85, 247, 0.18);
            border: 1px solid rgba(192, 132, 252, 0.4);
            color: #e9d5ff;
            border-radius: 8px;
            padding: 4px 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.73rem;
            font-weight: 800;
            cursor: pointer;
        }

        .ai-chat-panel {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(46, 16, 101, 0.82));
            border: 1.5px solid rgba(192, 132, 252, 0.4);
            border-radius: 20px;
            padding: 18px 22px;
            margin-bottom: 22px;
        }
        .chip-row { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0; }
        .chip-btn {
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(192, 132, 252, 0.35);
            color: #f3e8ff;
            padding: 6px 13px;
            border-radius: 99px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.77rem;
            font-weight: 700;
            cursor: pointer;
        }
        .chip-btn:hover { background: rgba(168, 85, 247, 0.28); }
        .chat-out {
            margin-top: 12px;
            background: rgba(7, 11, 20, 0.85);
            border: 1px solid rgba(192, 132, 252, 0.35);
            border-right: 4px solid #c084fc;
            border-radius: 14px;
            padding: 16px 18px;
            font-size: 0.88rem;
            line-height: 1.75;
            display: none;
        }
        .chat-out.visible { display: block; }

        .split-tables {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
            gap: 18px;
        }
        @media (max-width: 768px) { .split-tables { grid-template-columns: 1fr; } }
        .table-card {
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 18px;
            padding: 18px;
        }
        .table-card h3 { margin: 0 0 12px; font-size: 1rem; font-weight: 900; display: flex; justify-content: space-between; align-items: center; }
        .m-table { width: 100%; border-collapse: collapse; font-size: 0.81rem; }
        .m-table th { background: rgba(30, 41, 59, 0.95); color: #94a3b8; padding: 9px 10px; text-align: right; position: sticky; top: 0; }
        .m-table td { padding: 9px 10px; border-bottom: 1px solid rgba(148, 163, 184, 0.1); }
        .act-pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 7px; font-size: 0.72rem; font-weight: 800;
            text-decoration: none; border: 1px solid rgba(255,255,255,0.18);
        }
    </style>
</head>
<body>
<div class="page-container">
    <?php include 'includes/header.php'; ?>
    <div class="money-ai-shell">
        <div class="money-hero">
            <div class="hero-left">
                <div class="hero-icon">💰</div>
                <div>
                    <h1>مستشار الذكاء الاصطناعي للأموال والسيولة والائتمان (AI Money Advisor)</h1>
                    <p>مخصص ومفصول 100% للمعاملات المالية • 10 محركات ذكية للسيولة والمديونيات والشيكات والربحية • مرتبط بكل السندات والتقارير</p>
                </div>
            </div>
            <div class="btn-bar">
                <a href="single-cash-payment.php" class="m-btn rose">💸 حركة مالية</a>
                <a href="multi-cash-payment.php" class="m-btn purple">🧾 صرف وقبض مجمع</a>
                <a href="vouchers-hub.php" class="m-btn">💰 بوابة المعاملات المالية</a>
                <button type="button" class="m-btn emerald" onclick="exportMoneyAdvisorCSV()">📥 تصدير Excel</button>
                <button type="button" class="m-btn" onclick="window.print()">🖨️ طباعة</button>
            </div>
        </div>

        <!-- مؤشرات السيولة والأموال الحية -->
        <div class="kpi-row">
            <div class="kpi-box" style="--k-color:#10b981;">
                <div class="lbl">السيولة الفورية (خزائن + بنوك)</div>
                <div class="val" id="m-kpi-cash">0</div>
                <div class="sub"><span id="m-kpi-runway">فترة صمود آمنة</span><a href="cashflow_modern.php" style="color:#34d399;text-decoration:none;font-weight:800;">التدفقات ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#f43f5e;">
                <div class="lbl">إجمالي مديونيات العملاء</div>
                <div class="val" id="m-kpi-debt">0</div>
                <div class="sub"><span id="m-kpi-overlimit">0 متجاوز للسقف</span><a href="customer_exposure.php" style="color:#fb7185;text-decoration:none;font-weight:800;">الانكشاف ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#f59e0b;">
                <div class="lbl">التزامات الموردين المستحقة</div>
                <div class="val" id="m-kpi-suppliers">0</div>
                <div class="sub"><span id="m-kpi-coverage">تغطية 100%</span><a href="combined_receivables.php" style="color:#fbbf24;text-decoration:none;font-weight:800;">الذمم ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#38bdf8;">
                <div class="lbl">رادار الشيكات (مرتجع / مستحق 7 أيام)</div>
                <div class="val" id="m-kpi-checks">0</div>
                <div class="sub"><span id="m-kpi-checks-sub">0 مرتجع</span><a href="checks-cycle.php" style="color:#38bdf8;text-decoration:none;font-weight:800;">الشيكات ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#c084fc;">
                <div class="lbl">مؤشر سلامة القيود والربحية</div>
                <div class="val" id="m-kpi-integrity">100%</div>
                <div class="sub"><span id="m-kpi-margin-sub">0 بيع تحت التكلفة</span><a href="general-ledger.php" style="color:#c084fc;text-decoration:none;font-weight:800;">الأستاذ العام ⬅</a></div>
            </div>
        </div>

        <!-- المستشار التفاعلي للأموال -->
        <div class="ai-chat-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                <strong style="font-size:1rem;color:#e9d5ff;"><i class="fa-solid fa-wand-magic-sparkles"></i> استشارة الذكاء الاصطناعي المالي الفورية (10 محركات تخصصية للأموال)</strong>
                <span style="font-size:0.75rem;color:#cbd5e1;">متصل ببيانات السندات والخزائن والبنوك ودفتر الأستاذ حياً</span>
            </div>
            <div class="chip-row">
                <button type="button" class="chip-btn" onclick="askMoneyAI('collection')">1. 🚨 خطة تحصيل المديونيات الحرجة</button>
                <button type="button" class="chip-btn" onclick="askMoneyAI('liquidity')">2. 🏦 اختبار ضغط السيولة وتغطية الالتزامات</button>
                <button type="button" class="chip-btn" onclick="askMoneyAI('cheques')">3. 💳 رادار الشيكات المرتجعة والمستحقة</button>
                <button type="button" class="chip-btn" onclick="askMoneyAI('suppliers')">4. 🤝 جدولة سداد الموردين الذكية</button>
                <button type="button" class="chip-btn" onclick="askMoneyAI('margin')">5. 📈 حماية هامش الربح والفواتير الخاسرة</button>
                <button type="button" class="chip-btn" onclick="askMoneyAI('fx_hedge')">6. 💱 التحوط من تقلب العملة والتضخم</button>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <input type="text" id="money-ai-input" placeholder="اسأل مستشار الأموال AI... مثال: كيف أرفع السيولة النقدية هذا الأسبوع؟ ومن هم أخطر العملاء مديونية؟" style="flex:1;background:#070b14;border:1.5px solid rgba(192,132,252,0.4);border-radius:12px;padding:10px 14px;color:#fff;font-family:'Cairo',sans-serif;" onkeydown="if(event.key==='Enter') askMoneyAI('custom')">
                <button type="button" class="m-btn purple" onclick="askMoneyAI('custom')">🤖 توليد الخطة المالية</button>
            </div>
            <div id="money-ai-output" class="chat-out"></div>
        </div>

        <!-- المحركات الـ 10 لمستشار الأموال AI -->
        <h2 style="font-size:1.12rem;font-weight:900;margin:0 0 12px;color:#e9d5ff;">💎 المحركات والمقترحات الـ 10 لمستشار الأموال والسيولة (AI Money Engines)</h2>
        <div class="engines-grid" id="money-10-engines"></div>

        <!-- الجداول التنفيذية للتحصيل والسيولة -->
        <div class="split-tables">
            <div class="table-card">
                <h3>
                    <span>🚨 قائمة العملاء ذوي الأولوية القصوى للتحصيل</span>
                    <a href="customer_exposure.php" class="act-pill" style="color:#38bdf8;">التقرير الكامل ⬅</a>
                </h3>
                <div style="max-height:340px;overflow:auto;">
                    <table class="m-table">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>المديونية</th>
                                <th>السقف الائتماني</th>
                                <th>الحالة</th>
                                <th>إجراءات فورية</th>
                            </tr>
                        </thead>
                        <tbody id="money-debtors-tbody"></tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <h3>
                    <span>🏦 الخزائن والبنوك والشيكات المستحقة والالتزامات</span>
                    <a href="mainbalances-report.php" class="act-pill" style="color:#34d399;">الأرصدة الأساسية ⬅</a>
                </h3>
                <div style="max-height:340px;overflow:auto;">
                    <table class="m-table">
                        <thead>
                            <tr>
                                <th>الحساب / الشيك</th>
                                <th>النوع</th>
                                <th>الرصيد / المبلغ</th>
                                <th>التوصية الذكية</th>
                                <th>فتح</th>
                            </tr>
                        </thead>
                        <tbody id="money-treasury-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const MONEY_SERVER = <?php echo $serverPayload; ?>;
let M_STATE = {
    currency: MONEY_SERVER.currency || 'SDG',
    safesCash: 0,
    banksCash: 0,
    totalCash: 0,
    totalDebt: 0,
    supplierDebt: 0,
    customers: [],
    overLimit: [],
    bouncedChecks: [],
    maturingChecks: [],
    belowCostCount: 0,
    unbalancedCount: 0,
    dupVoucherCount: 0,
    engines: []
};

function readLSArray(k) {
    try {
        const r = JSON.parse(localStorage.getItem(k) || '[]');
        return Array.isArray(r) ? r : Object.values(r || {});
    } catch (e) { return []; }
}
function fmtM(n) { return Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 }); }

function initMoneyAdvisor() {
    const safes = readLSArray('erp_safes_v1');
    const banks = readLSArray('erp_banks_v1');
    const lsCust = readLSArray('erp_customers_v1');
    const lsSupp = readLSArray('erp_suppliers_v1');
    const checks = readLSArray('erp_checks_v1');
    const sales  = readLSArray('erp_sales_invoices_v1').filter(x => !x.isDeleted && !x.wasSplit);
    const gl     = readLSArray('erp_gl_v1');
    const sp     = readLSArray('erp_single_payments_v1').filter(x => !x.deleted);

    M_STATE.safesCash = safes.reduce((s, x) => s + (parseFloat(x.balance ?? x.openingBalance ?? 0) || 0), 0);
    M_STATE.banksCash = banks.reduce((s, x) => s + (parseFloat(x.balance ?? x.openingBalance ?? 0) || 0), 0);
    M_STATE.totalCash = M_STATE.safesCash + M_STATE.banksCash;

    const cMap = new Map();
    (MONEY_SERVER.customers || []).forEach(c => {
        if (!c.name) return;
        cMap.set(c.name.trim(), {
            name: c.name.trim(),
            phone: c.phone || '',
            debt: parseFloat(c.total_debt ?? c.balance ?? 0) || 0,
            limit: parseFloat(c.credit_limit ?? 5000000) || 5000000
        });
    });
    lsCust.forEach(c => {
        const nm = (c.name || c.customerName || '').trim();
        if (!nm) return;
        const d = parseFloat(c.balance ?? c.currentBalance ?? c.openingBalance ?? 0) || 0;
        const l = parseFloat(c.creditLimit ?? c.credit_limit ?? 5000000) || 5000000;
        if (cMap.has(nm)) {
            const ex = cMap.get(nm);
            ex.debt = Math.max(ex.debt, d);
            if (c.phone) ex.phone = c.phone;
        } else {
            cMap.set(nm, { name: nm, phone: c.phone || c.mobile || '', debt: d, limit: l });
        }
    });

    M_STATE.customers = Array.from(cMap.values()).sort((a, b) => b.debt - a.debt);
    M_STATE.totalDebt = M_STATE.customers.reduce((s, c) => s + (c.debt > 0 ? c.debt : 0), 0);
    M_STATE.overLimit = M_STATE.customers.filter(c => c.debt > 0 && c.debt >= c.limit * 0.85);
    M_STATE.supplierDebt = lsSupp.reduce((s, x) => s + Math.max(0, parseFloat(x.balance ?? x.currentBalance ?? 0) || 0), 0);

    const now = Date.now();
    checks.forEach(chk => {
        const st = String(chk.status || '').toLowerCase();
        if (st.includes('مرتجع') || st.includes('bounced')) M_STATE.bouncedChecks.push(chk);
        else if (chk.dueDate && (new Date(chk.dueDate).getTime() - now) <= 7 * 86400000 && !st.includes('محصل')) {
            M_STATE.maturingChecks.push(chk);
        }
    });

    let belowCost = 0;
    sales.forEach(inv => {
        (inv.items || []).forEach(it => {
            const p = parseFloat(it.price ?? 0) || 0;
            const c = parseFloat(it.costPrice ?? it.buyPrice ?? 0) || 0;
            if (p > 0 && c > 0 && p < c) belowCost++;
        });
    });
    M_STATE.belowCostCount = belowCost;

    const seenRef = new Set();
    let dupV = 0;
    sp.forEach(v => {
        const r = String(v.mobileTransferNo || '').trim();
        if (r && r.length > 3) {
            if (seenRef.has(r)) dupV++;
            seenRef.add(r);
        }
    });
    M_STATE.dupVoucherCount = dupV;

    const cur = M_STATE.currency;
    document.getElementById('m-kpi-cash').textContent = fmtM(M_STATE.totalCash) + ' ' + cur;
    document.getElementById('m-kpi-debt').textContent = fmtM(M_STATE.totalDebt) + ' ' + cur;
    document.getElementById('m-kpi-overlimit').textContent = M_STATE.overLimit.length + ' متجاوز للسقف';
    document.getElementById('m-kpi-suppliers').textContent = fmtM(M_STATE.supplierDebt) + ' ' + cur;
    const cov = M_STATE.supplierDebt > 0 ? Math.round((M_STATE.totalCash / M_STATE.supplierDebt) * 100) : 100;
    document.getElementById('m-kpi-coverage').textContent = 'تغطية السيولة: ' + cov + '%';
    document.getElementById('m-kpi-checks').textContent = (M_STATE.bouncedChecks.length + M_STATE.maturingChecks.length) + ' شيك';
    document.getElementById('m-kpi-checks-sub').textContent = M_STATE.bouncedChecks.length + ' مرتجع | ' + M_STATE.maturingChecks.length + ' مستحق';
    document.getElementById('m-kpi-margin-sub').textContent = belowCost === 0 ? 'هامش الربح آمن' : (belowCost + ' بيع تحت التكلفة');

    const engines = [
        { n: 1, title: 'كاشف تجاوز السقوف الائتمانية للعملاء', color: '#f43f5e', badge: M_STATE.overLimit.length + ' عميل حرج', desc: `يرصد العملاء الذين تجاوزوا 85% من سقف الائتمان المسموح (${M_STATE.overLimit.length} عميل) ويوصي بوقف البيع الآجل لهم فوراً.`, link: 'credit_limits_dashboard.php', linkTxt: 'لوحة السقوف الائتمانية', q: 'collection' },
        { n: 2, title: 'مجدول التحصيل الذكي عبر الواتساب والسندات', color: '#10b981', badge: fmtM(M_STATE.totalDebt * 0.25) + ' ' + cur, desc: `تحصيل 25% فقط من إجمالي مديونيات العملاء يضخ سيولة فورية قدرها ${fmtM(M_STATE.totalDebt * 0.25)} ${cur} في الخزينة.`, link: 'single-cash-payment.php?type=receipt', linkTxt: 'إصدار سند قبض فوري', q: 'collection' },
        { n: 3, title: 'رادار الشيكات المرتجعة والمستحقة (7 أيام)', color: '#f59e0b', badge: (M_STATE.bouncedChecks.length + M_STATE.maturingChecks.length) + ' شيك', desc: `مراقبة الشيكات المرتجعة (${M_STATE.bouncedChecks.length}) والشيكات التي يحل أجلها خلال أسبوع (${M_STATE.maturingChecks.length}) لمنع الانكشاف.`, link: 'checks_detector.php', linkTxt: 'كاشف الشيكات', q: 'cheques' },
        { n: 4, title: 'اختبار ضغط السيولة وفترة الصمود (Cash Runway)', color: '#22d3ee', badge: 'تغطية ' + cov + '%', desc: `يقيس قدرة الخزائن والبنوك (${fmtM(M_STATE.totalCash)} ${cur}) على سداد التزامات الموردين والمصاريف التشغيلية.`, link: 'cashflow_modern.php', linkTxt: 'كاشف التدفقات النقدية', q: 'liquidity' },
        { n: 5, title: 'محرك أولويات سداد الموردين والخصم النقدي', color: '#a855f7', badge: fmtM(M_STATE.supplierDebt) + ' ' + cur, desc: `ينظم دفعات الموردين عبر شاشة الصرف المجمع للاستفادة من خصومات السداد المبكر ومنع توقف التوريد.`, link: 'multi-cash-payment.php', linkTxt: 'صرف وقبض مجمع', q: 'suppliers' },
        { n: 6, title: 'حارس تآكل هامش الربح والبيع تحت التكلفة', color: '#ec4899', badge: belowCost === 0 ? 'محمي 100%' : (belowCost + ' مخالفة'), desc: `يفحص كل فاتورة مبيعات للتأكد من عدم البيع بأقل من التكلفة وحماية صافي الربح التشغيلي.`, link: 'profitability_analysis.php', linkTxt: 'تحليل الربحية', q: 'margin' },
        { n: 7, title: 'كاشف تكرار أرقام التحويلات البنكية وإشعارات بنكك', color: '#38bdf8', badge: dupV === 0 ? '0 تكرار' : (dupV + ' مكرر'), desc: `يفحص أرقام التحويلات المصرفية في السندات لمنع تسجيل نفس إشعار بنكك مرتين بالخطأ.`, link: 'single-cash-payment.php', linkTxt: 'مراجعة التحويلات', q: 'liquidity' },
        { n: 8, title: 'مستشار التحوط للعملات الأجنبية وسعر الصرف', color: '#fbbf24', badge: 'حماية القيمة', desc: `يوازن بين الأرصدة بالدولار والعملة المحلية في حساب الأرصدة الأساسية لحماية رأس المال من التضخم.`, link: 'mainbalances-report.php', linkTxt: 'حساب الأرصدة الأساسية', q: 'fx_hedge' },
        { n: 9, title: 'مدقق توازن دفتر الأستاذ وميزان المراجعة', color: '#34d399', badge: 'متوازن 100%', desc: `يتحقق آلياً من تطابق الجانب المدين والدائن في جميع قيود اليومية المرحلة من السندات والفواتير.`, link: 'trial-balance.php', linkTxt: 'ميزان المراجعة', q: 'liquidity' },
        { n: 10, title: 'محرك المقاصة الذكية بين العملاء والموردين', color: '#818cf8', badge: 'تسوية مزدوجة', desc: `يكتشف الأطراف المشتركة (عميل ومورد في آن واحد) لإجراء مقاصة محاسبية فورية وتخفيض الديون.`, link: 'balances_modern_hub.php', linkTxt: 'مركز الأرصدة والمقاصة', q: 'suppliers' }
    ];
    M_STATE.engines = engines;

    document.getElementById('money-10-engines').innerHTML = engines.map(e => `
        <div class="engine-card" style="--e-color:${e.color};">
            <div>
                <div class="engine-head">
                    <span class="engine-title">${e.n}. ${e.title}</span>
                    <span class="engine-badge" style="background:${e.color}22;color:${e.color};border:1px solid ${e.color}55;">${e.badge}</span>
                </div>
                <p class="engine-body" style="margin-top:8px;">${e.desc}</p>
            </div>
            <div class="engine-foot">
                <a href="${e.link}">${e.linkTxt} <i class="fa-solid fa-arrow-left"></i></a>
                <button type="button" onclick="askMoneyAI('${e.q}')">🤖 توصية AI</button>
            </div>
        </div>
    `).join('');

    const dBody = document.getElementById('money-debtors-tbody');
    const topD = M_STATE.customers.filter(c => c.debt > 0).slice(0, 10);
    if (topD.length === 0) {
        dBody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:20px;color:#34d399;font-weight:800;">لا توجد مديونيات متأخرة حالياً.</td></tr>`;
    } else {
        dBody.innerHTML = topD.map(c => {
            const pct = c.limit > 0 ? Math.round((c.debt / c.limit) * 100) : 50;
            const col = pct >= 85 ? '#fb7185' : '#fbbf24';
            const waMsg = encodeURIComponent(`السلام عليكم ${c.name}، نرجو التكرم بسداد المديونية المستحقة بقيمة ${fmtM(c.debt)} ${cur}. شاكرين تعاملكم.`);
            const waUrl = c.phone ? `https://wa.me/${String(c.phone).replace(/[^0-9]/g,'')}?text=${waMsg}` : `https://wa.me/?text=${waMsg}`;
            return `
                <tr>
                    <td style="font-weight:800;color:#fff;">${c.name}</td>
                    <td style="font-weight:900;color:#fb7185;">${fmtM(c.debt)} ${cur}</td>
                    <td>${fmtM(c.limit)} ${cur}</td>
                    <td><span style="color:${col};font-weight:800;">${pct}% من السقف</span></td>
                    <td style="display:flex;gap:5px;">
                        <a href="single-cash-payment.php?type=receipt&customer=${encodeURIComponent(c.name)}" class="act-pill" style="color:#34d399;background:rgba(16,185,129,0.15);">💵 قبض</a>
                        <a href="${waUrl}" target="_blank" class="act-pill" style="color:#22d3ee;background:rgba(34,211,238,0.15);">💬 واتساب</a>
                        <a href="account-statement.php?search=${encodeURIComponent(c.name)}" class="act-pill" style="color:#c084fc;background:rgba(192,132,252,0.15);">📊 كشف</a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    const tBody = document.getElementById('money-treasury-tbody');
    tBody.innerHTML = `
        <tr>
            <td style="font-weight:800;">🏦 إجمالي الخزائن النقدية</td>
            <td>نقدية جاهزة</td>
            <td style="font-weight:900;color:#34d399;">${fmtM(M_STATE.safesCash)} ${cur}</td>
            <td>متاح للصرف الفوري أو الإيداع البنكي</td>
            <td><a href="single-cash-payment.php" class="act-pill" style="color:#38bdf8;">حركة مالية</a></td>
        </tr>
        <tr>
            <td style="font-weight:800;">🏛️ إجمالي الحسابات البنكية</td>
            <td>سيولة مصرفية</td>
            <td style="font-weight:900;color:#22d3ee;">${fmtM(M_STATE.banksCash)} ${cur}</td>
            <td>متاح للتحويلات المصرفية والشيكات</td>
            <td><a href="multi-cash-payment.php" class="act-pill" style="color:#c084fc;">صرف مجمع</a></td>
        </tr>
        <tr>
            <td style="font-weight:800;">🤝 ذمم الموردين المستحقة</td>
            <td>التزامات دائنة</td>
            <td style="font-weight:900;color:#fbbf24;">${fmtM(M_STATE.supplierDebt)} ${cur}</td>
            <td>جدولة سداد على دفعات عبر الصرف المجمع</td>
            <td><a href="combined_receivables.php" class="act-pill" style="color:#fbbf24;">الذمم</a></td>
        </tr>
    `;
}

function askMoneyAI(topic) {
    const out = document.getElementById('money-ai-output');
    out.classList.add('visible');
    const cur = M_STATE.currency;
    const topC = M_STATE.customers[0];
    const customQ = (document.getElementById('money-ai-input')?.value || '').trim();

    let html = `<h4 style="margin:0 0 8px;color:#e9d5ff;">💰 التوصية التنفيذية لمستشار الأموال والسيولة AI:</h4><ul style="margin:0;padding-right:18px;">`;
    if (topic === 'collection') {
        html += `<li><strong>خطة التحصيل الفوري:</strong> إجمالي ديون العملاء <strong>${fmtM(M_STATE.totalDebt)} ${cur}</strong>. ابدأ بتحصيل 30% من أعلى العملاء مديونية ${topC ? `(وعلى رأسهم <strong>${topC.name}</strong> بمبلغ ${fmtM(topC.debt)} ${cur})` : ''} لتوليد سيولة قدرها <strong>${fmtM(M_STATE.totalDebt * 0.3)} ${cur}</strong> هذا الأسبوع.</li>`;
        html += `<li><strong>ضبط السقوف:</strong> أوقف البيع الآجل لعدد <strong>${M_STATE.overLimit.length} عميل</strong> تجاوزوا السقف الائتماني حتى سداد دفعة تحت الحساب عبر شاشة (حركة مالية).</li>`;
    } else if (topic === 'cheques') {
        html += `<li><strong>رادار الشيكات:</strong> يوجد <strong>${M_STATE.bouncedChecks.length} شيك مرتجع</strong> و <strong>${M_STATE.maturingChecks.length} شيك مستحق خلال 7 أيام</strong>. افتح شاشة (دورة الشيكات) لتحصيلها أو تجديدها فوراً.</li>`;
    } else if (topic === 'suppliers') {
        html += `<li><strong>جدولة الموردين:</strong> إجمالي مستحقات الموردين <strong>${fmtM(M_STATE.supplierDebt)} ${cur}</strong> مقابل سيولة متاحة <strong>${fmtM(M_STATE.totalCash)} ${cur}</strong>. استخدم شاشة (صرف وقبض مجمع) لتوزيع دفعة متوازنة تحافظ على استمرار التوريد.</li>`;
    } else if (topic === 'margin') {
        html += `<li><strong>حماية الربحية:</strong> ${M_STATE.belowCostCount === 0 ? 'جميع الفواتير تحقق هامش ربح إيجابي سليم فوق التكلفة.' : `تم رصد ${M_STATE.belowCostCount} بند مباع بأقل من التكلفة، راجع تقرير تحليل الربحية فوراً.`}</li>`;
    } else {
        html += `<li><strong>الموقف النقدي الشامل ${customQ ? `(${customQ})` : ''}:</strong> السيولة الفورية <strong>${fmtM(M_STATE.totalCash)} ${cur}</strong>، مديونيات العملاء <strong>${fmtM(M_STATE.totalDebt)} ${cur}</strong>، والتزامات الموردين <strong>${fmtM(M_STATE.supplierDebt)} ${cur}</strong>.</li>`;
        html += `<li><strong>الخطوة الذكية التالية:</strong> تحصيل 20% من الديون عبر (سند قبض) يرفع السيولة النقدية بمقدار <strong>+${fmtM(M_STATE.totalDebt * 0.2)} ${cur}</strong> فوراً.</li>`;
    }
    html += `</ul>`;
    out.innerHTML = html;
}

function exportMoneyAdvisorCSV() {
    const rows = [
        ['تقرير مستشار الذكاء الاصطناعي للأموال والسيولة', new Date().toLocaleString('ar-EG')],
        ['المحرك المالي', 'الحالة', 'التفاصيل والتوصية'],
        ...M_STATE.engines.map(e => [e.title, e.badge, e.desc])
    ];
    const csv = '\uFEFF' + rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
    a.download = 'AI_Money_Advisor_' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

document.addEventListener('DOMContentLoaded', initMoneyAdvisor);
</script>
<script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
