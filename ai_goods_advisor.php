<?php
// =========================================================================
// نظام السجانة والسيطرة ERP 2026
// مستشار الذكاء الاصطناعي للبضائع والمخازن وسلاسل الإمداد (AI Goods & Inventory Advisor)
// مخصص ومفصول 100% للمخازن والأصناف والطلبيات والرواكد (10 محركات ذكية)
// =========================================================================
$page_title = 'مستشار AI للبضائع والمخازن والطلبيات (10 محركات ذكية)';
$header_theme_class = 'header-theme-inventory';

require_once __DIR__ . '/db_bridge.php';
$pdo = getDBConnection();

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$invData = getInventoryReportData($pdo);
$sqlItems = $invData['items'] ?? [];
$sqlTotalVal = (float)($invData['total_value'] ?? 0);

$serverPayload = json_encode([
    'items'      => $sqlItems,
    'totalValue' => $sqlTotalVal,
    'currency'   => $selected_currency
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
        .goods-ai-shell {
            max-width: 1560px;
            margin: 0 auto;
            padding: 18px 20px 60px;
        }
        .goods-hero {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(6, 78, 59, 0.85));
            border: 1.5px solid rgba(52, 211, 153, 0.4);
            border-radius: 22px;
            padding: 22px 26px;
            margin-bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 18px 45px rgba(0,0,0,0.45), 0 0 35px rgba(16, 185, 129, 0.14);
            position: relative;
            overflow: hidden;
        }
        .goods-hero::before {
            content: '';
            position: absolute;
            top: 0; right: 0; left: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #22d3ee, #fbbf24, #a855f7);
        }
        .hero-left { display: flex; align-items: center; gap: 16px; }
        .hero-icon {
            width: 60px; height: 60px; border-radius: 18px;
            background: rgba(16, 185, 129, 0.22);
            border: 1.5px solid rgba(52, 211, 153, 0.55);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.85rem;
        }
        .hero-left h1 {
            margin: 0; font-size: 1.48rem; font-weight: 900;
            background: linear-gradient(90deg, #ffffff, #a7f3d0, #67e8f9);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-left p { margin: 4px 0 0; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; }
        .btn-bar { display: flex; flex-wrap: wrap; gap: 8px; }
        .g-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 15px; border-radius: 12px;
            font-family: 'Cairo', sans-serif; font-size: 0.81rem; font-weight: 800;
            text-decoration: none; cursor: pointer;
            border: 1px solid rgba(255,255,255,0.16);
            background: rgba(30, 41, 59, 0.88); color: #fff; transition: all 0.2s;
        }
        .g-btn:hover { transform: translateY(-2px); border-color: #34d399; }
        .g-btn.emerald { background: linear-gradient(135deg, #059669, #0d9488); border-color: #34d399; }
        .g-btn.cyan { background: linear-gradient(135deg, #0284c7, #0891b2); border-color: #22d3ee; }
        .g-btn.amber { background: linear-gradient(135deg, #d97706, #ea580c); border-color: #fbbf24; }

        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(215px, 1fr));
            gap: 14px; margin-bottom: 20px;
        }
        .kpi-box {
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(52, 211, 153, 0.2);
            border-bottom: 3px solid var(--k-color, #34d399);
            border-radius: 16px; padding: 15px 18px;
        }
        .kpi-box .lbl { font-size: 0.78rem; color: #94a3b8; font-weight: 700; }
        .kpi-box .val { font-size: 1.42rem; font-weight: 900; color: #fff; margin: 5px 0; }
        .kpi-box .sub { font-size: 0.73rem; color: #cbd5e1; display: flex; justify-content: space-between; }

        .engines-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(295px, 1fr));
            gap: 14px; margin-bottom: 22px;
        }
        .engine-card {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-right: 4px solid var(--e-color, #34d399);
            border-radius: 16px; padding: 15px 16px;
            display: flex; flex-direction: column; justify-content: space-between; gap: 10px;
            transition: all 0.22s;
        }
        .engine-card:hover {
            transform: translateY(-3px);
            border-color: var(--e-color, #34d399);
            box-shadow: 0 12px 28px rgba(0,0,0,0.38);
        }
        .engine-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
        .engine-title { font-size: 0.89rem; font-weight: 900; color: #fff; }
        .engine-badge { font-size: 0.7rem; font-weight: 800; padding: 2px 9px; border-radius: 99px; white-space: nowrap; }
        .engine-body { font-size: 0.79rem; color: #cbd5e1; line-height: 1.55; margin: 0; }
        .engine-foot {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 8px; border-top: 1px dashed rgba(148, 163, 184, 0.15);
        }
        .engine-foot a { color: #34d399; font-size: 0.76rem; font-weight: 800; text-decoration: none; }
        .engine-foot button {
            background: rgba(16, 185, 129, 0.18);
            border: 1px solid rgba(52, 211, 153, 0.4);
            color: #a7f3d0; border-radius: 8px; padding: 4px 10px;
            font-family: 'Cairo', sans-serif; font-size: 0.73rem; font-weight: 800; cursor: pointer;
        }

        .ai-chat-panel {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(6, 78, 59, 0.78));
            border: 1.5px solid rgba(52, 211, 153, 0.4);
            border-radius: 20px; padding: 18px 22px; margin-bottom: 22px;
        }
        .chip-row { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0; }
        .chip-btn {
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(52, 211, 153, 0.35);
            color: #d1fae5; padding: 6px 13px; border-radius: 99px;
            font-family: 'Cairo', sans-serif; font-size: 0.77rem; font-weight: 700; cursor: pointer;
        }
        .chip-btn:hover { background: rgba(16, 185, 129, 0.28); }
        .chat-out {
            margin-top: 12px; background: rgba(7, 11, 20, 0.85);
            border: 1px solid rgba(52, 211, 153, 0.35);
            border-right: 4px solid #34d399; border-radius: 14px;
            padding: 16px 18px; font-size: 0.88rem; line-height: 1.75; display: none;
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
            border-radius: 18px; padding: 18px;
        }
        .table-card h3 { margin: 0 0 12px; font-size: 1rem; font-weight: 900; display: flex; justify-content: space-between; align-items: center; }
        .g-table { width: 100%; border-collapse: collapse; font-size: 0.81rem; }
        .g-table th { background: rgba(30, 41, 59, 0.95); color: #94a3b8; padding: 9px 10px; text-align: right; position: sticky; top: 0; }
        .g-table td { padding: 9px 10px; border-bottom: 1px solid rgba(148, 163, 184, 0.1); }
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
    <div class="goods-ai-shell">
        <div class="goods-hero">
            <div class="hero-left">
                <div class="hero-icon">📦</div>
                <div>
                    <h1>مستشار الذكاء الاصطناعي للبضائع والمخازن وسلاسل الإمداد (AI Goods Advisor)</h1>
                    <p>مخصص ومفصول 100% للمخازن والبضائع • 10 محركات ذكية للطلبيات والرواكد والتسعير ودوران المخزون • مرتبط بكل التقارير</p>
                </div>
            </div>
            <div class="btn-bar">
                <a href="inventory-movement.php" class="g-btn cyan">📦 حركات البضائع</a>
                <a href="smart_inventory_report.php" class="g-btn emerald">🤖 تقارير المخزن الذكية الـ 20</a>
                <a href="purchase.php" class="g-btn amber">🛒 فاتورة مشتريات</a>
                <button type="button" class="g-btn emerald" onclick="exportGoodsAdvisorCSV()">📥 تصدير Excel</button>
                <button type="button" class="g-btn" onclick="window.print()">🖨️ طباعة</button>
            </div>
        </div>

        <!-- مؤشرات المخازن والبضائع الحية -->
        <div class="kpi-row">
            <div class="kpi-box" style="--k-color:#34d399;">
                <div class="lbl">إجمالي القيمة المالية للمخزون</div>
                <div class="val" id="g-kpi-val">0</div>
                <div class="sub"><span id="g-kpi-items-count">0 صنف مسجل</span><a href="inv_rep15_valuation.php" style="color:#34d399;text-decoration:none;font-weight:800;">التقييم ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#f43f5e;">
                <div class="lbl">أصناف ناقصة / تحت حد الطلب</div>
                <div class="val" id="g-kpi-short">0</div>
                <div class="sub"><span id="g-kpi-neg">0 رصيد سالب</span><a href="inv_rep1_limits.php" style="color:#fb7185;text-decoration:none;font-weight:800;">حدود الأمان ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#a855f7;">
                <div class="lbl">قيمة البضائع الراكدة والمجمدة</div>
                <div class="val" id="g-kpi-dead">0</div>
                <div class="sub"><span id="g-kpi-dead-count">0 صنف راكد</span><a href="inv_rep3_deadstock.php" style="color:#c084fc;text-decoration:none;font-weight:800;">الرواكد ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#22d3ee;">
                <div class="lbl">الأصناف النجمية سريعة الدوران (فئة A)</div>
                <div class="val" id="g-kpi-stars">0</div>
                <div class="sub"><span>الأعلى مساهمة بالربح</span><a href="inv_rep19_abc.php" style="color:#22d3ee;text-decoration:none;font-weight:800;">تحليل ABC ⬅</a></div>
            </div>
            <div class="kpi-box" style="--k-color:#fbbf24;">
                <div class="lbl">توصيات أوامر الشراء الفورية (EOQ)</div>
                <div class="val" id="g-kpi-po">0</div>
                <div class="sub"><span>جاهزة للتحويل لمشتريات</span><a href="purchase.php" style="color:#fbbf24;text-decoration:none;font-weight:800;">طلب شراء ⬅</a></div>
            </div>
        </div>

        <!-- المستشار التفاعلي للبضائع -->
        <div class="ai-chat-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                <strong style="font-size:1rem;color:#a7f3d0;"><i class="fa-solid fa-wand-magic-sparkles"></i> استشارة الذكاء الاصطناعي السلعي والمخزني (10 محركات تخصصية للبضائع)</strong>
                <span style="font-size:0.75rem;color:#cbd5e1;">متصل بحركات البضائع والجرد والمشتريات والتقارير الـ 20</span>
            </div>
            <div class="chip-row">
                <button type="button" class="chip-btn" onclick="askGoodsAI('eoq')">1. 🛒 خطة الشراء الذكي والكميات الاقتصادية (EOQ)</button>
                <button type="button" class="chip-btn" onclick="askGoodsAI('deadstock')">2. 🧊 خطة تسييل البضائع الراكدة وتحرير السيولة</button>
                <button type="button" class="chip-btn" onclick="askGoodsAI('abc')">3. ⭐ تحليل الأصناف النجمية (ABC) وتعظيم الربح</button>
                <button type="button" class="chip-btn" onclick="askGoodsAI('bundling')">4. 🎁 ابتكار عروض ترويجية مدمجة (صنف راكد + نجمي)</button>
                <button type="button" class="chip-btn" onclick="askGoodsAI('shrinkage')">5. 🔍 ضبط العجز والرصيد السالب وفروقات الجرد</button>
                <button type="button" class="chip-btn" onclick="askGoodsAI('pricing')">6. 🏷️ التسعير الديناميكي وحماية هامش الصنف</button>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <input type="text" id="goods-ai-input" placeholder="اسأل مستشار البضائع AI... مثال: ما هي الأصناف التي يجب شراؤها اليوم؟ وكيف نصرف البضاعة الراكدة؟" style="flex:1;background:#070b14;border:1.5px solid rgba(52,211,153,0.4);border-radius:12px;padding:10px 14px;color:#fff;font-family:'Cairo',sans-serif;" onkeydown="if(event.key==='Enter') askGoodsAI('custom')">
                <button type="button" class="g-btn emerald" onclick="askGoodsAI('custom')">🤖 توليد الخطة المخزنية</button>
            </div>
            <div id="goods-ai-output" class="chat-out"></div>
        </div>

        <!-- المحركات الـ 10 لمستشار البضائع AI -->
        <h2 style="font-size:1.12rem;font-weight:900;margin:0 0 12px;color:#a7f3d0;">📦 المحركات والمقترحات الـ 10 لمستشار البضائع والمخازن (AI Goods Engines)</h2>
        <div class="engines-grid" id="goods-10-engines"></div>

        <!-- الجداول التنفيذية للطلبيات والرواكد -->
        <div class="split-tables">
            <div class="table-card">
                <h3>
                    <span>🛒 قائمة الطلبيات الذكية العاجلة (أصناف تحت حد الطلب أو سالبة)</span>
                    <a href="inv_rep1_limits.php" class="act-pill" style="color:#38bdf8;">تقرير الحدود ⬅</a>
                </h3>
                <div style="max-height:340px;overflow:auto;">
                    <table class="g-table">
                        <thead>
                            <tr>
                                <th>الصنف</th>
                                <th>الرصيد الحالي</th>
                                <th>حد الأمان</th>
                                <th>الكمية المقترح شراؤها</th>
                                <th>إجراء فوري</th>
                            </tr>
                        </thead>
                        <tbody id="goods-reorder-tbody"></tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <h3>
                    <span>🧊 البضائع الراكدة مقابل الأصناف النجمية (توصيات التسعير والتسييل)</span>
                    <a href="inv_rep3_deadstock.php" class="act-pill" style="color:#c084fc;">مصفوفة الرواكد ⬅</a>
                </h3>
                <div style="max-height:340px;overflow:auto;">
                    <table class="g-table">
                        <thead>
                            <tr>
                                <th>الصنف</th>
                                <th>الرصيد</th>
                                <th>القيمة المالية</th>
                                <th>التصنيف الذكي</th>
                                <th>القرار المقترح</th>
                            </tr>
                        </thead>
                        <tbody id="goods-matrix-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const GOODS_SERVER = <?php echo $serverPayload; ?>;
let G_STATE = {
    currency: GOODS_SERVER.currency || 'SDG',
    items: [],
    totalValue: 0,
    shortages: [],
    negatives: [],
    deadItems: [],
    deadValue: 0,
    starItems: [],
    engines: []
};

function readLSG(k) {
    try {
        const r = JSON.parse(localStorage.getItem(k) || '[]');
        return Array.isArray(r) ? r : Object.values(r || {});
    } catch (e) { return []; }
}
function fmtG(n) { return Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 }); }

function initGoodsAdvisor() {
    const lsItems = readLSG('erp_items_v1');
    const lsSales = readLSG('erp_sales_invoices_v1').filter(x => !x.isDeleted && !x.wasSplit);
    let invMap = {};
    try { invMap = JSON.parse(localStorage.getItem('erp_inventory_v1') || '{}') || {}; } catch (e) {}

    const soldMap = {};
    lsSales.forEach(inv => {
        (inv.items || []).forEach(it => {
            const nm = (it.itemName || it.name || '').trim();
            const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            if (nm) soldMap[nm] = (soldMap[nm] || 0) + q;
        });
    });

    const iMap = new Map();
    (GOODS_SERVER.items || []).forEach(it => {
        const nm = (it.name || '').trim();
        if (!nm) return;
        iMap.set(nm, {
            id: it.id || nm,
            name: nm,
            qty: parseFloat(it.stock_qty ?? it.qty ?? 0) || 0,
            cost: parseFloat(it.cost_price ?? it.buy_price ?? it.price ?? 0) || 0,
            min: parseFloat(it.min_limit ?? 10) || 10,
            sold: parseFloat(it.sold_qty ?? soldMap[nm] ?? 0) || 0
        });
    });
    lsItems.forEach(it => {
        const nm = (it.name || it.itemName || '').trim();
        if (!nm) return;
        let q = parseFloat(it.qty ?? it.stock ?? it.openingStock ?? 0) || 0;
        if (invMap[it.id] !== undefined) {
            const mv = invMap[it.id];
            q = typeof mv === 'object' ? (parseFloat(mv.qty ?? 0) || 0) : (parseFloat(mv) || 0);
        }
        const c = parseFloat(it.buyPrice ?? it.costPrice ?? it.price ?? 0) || 0;
        const m = parseFloat(it.minStock ?? it.reorderLevel ?? 10) || 10;
        iMap.set(nm, {
            id: it.id || nm,
            name: nm,
            qty: q,
            cost: c,
            min: m,
            sold: soldMap[nm] || 0
        });
    });

    const all = Array.from(iMap.values());
    G_STATE.items = all;
    G_STATE.totalValue = all.reduce((s, x) => s + (Math.max(0, x.qty) * x.cost), 0) || GOODS_SERVER.totalValue || 0;
    G_STATE.shortages = all.filter(x => x.qty <= x.min).sort((a, b) => a.qty - b.qty);
    G_STATE.negatives = all.filter(x => x.qty < 0);
    G_STATE.deadItems = all.filter(x => x.qty > x.min && x.sold === 0 && (x.qty * x.cost) > 0).sort((a, b) => (b.qty * b.cost) - (a.qty * a.cost));
    G_STATE.deadValue = G_STATE.deadItems.reduce((s, x) => s + (x.qty * x.cost), 0);
    G_STATE.starItems = all.filter(x => x.sold > 0).sort((a, b) => b.sold - a.sold);

    const cur = G_STATE.currency;
    document.getElementById('g-kpi-val').textContent = fmtG(G_STATE.totalValue) + ' ' + cur;
    document.getElementById('g-kpi-items-count').textContent = all.length + ' صنف نشط';
    document.getElementById('g-kpi-short').textContent = G_STATE.shortages.length + ' صنف';
    document.getElementById('g-kpi-neg').textContent = G_STATE.negatives.length + ' رصيد سالب';
    document.getElementById('g-kpi-dead').textContent = fmtG(G_STATE.deadValue) + ' ' + cur;
    document.getElementById('g-kpi-dead-count').textContent = G_STATE.deadItems.length + ' صنف راكد';
    document.getElementById('g-kpi-stars').textContent = G_STATE.starItems.length + ' صنف نجمي';
    document.getElementById('g-kpi-po').textContent = G_STATE.shortages.length + ' طلبية ذكية';

    const engines = [
        { n: 1, title: 'محرك الكمية الاقتصادية للطلب (AI Smart EOQ)', color: '#10b981', badge: G_STATE.shortages.length + ' أمر شراء', desc: `يحسب الكمية المثلى لإعادة الشراء (${G_STATE.shortages.length} صنف تحت الحد الأدنى) لمنع انقطاع البضاعة وتقليل تكلفة التخزين.`, link: 'inv_rep1_limits.php', linkTxt: 'تقرير حدود الأمان الـ 4', q: 'eoq' },
        { n: 2, title: 'كاشف الأرصدة السالبة والعجز المخزني الفوري', color: '#f43f5e', badge: G_STATE.negatives.length + ' صنف سالب', desc: `يرصد الأصناف التي بيعت قبل تسجيل فاتورة مشترياتها (${G_STATE.negatives.length} صنف) لضبط التسوية الجردية فوراً.`, link: 'inventory-adjustment.php', linkTxt: 'شاشة التسوية الجردية', q: 'shrinkage' },
        { n: 3, title: 'محرك تسييل البضائع الراكدة والأموال المجمدة', color: '#a855f7', badge: fmtG(G_STATE.deadValue) + ' ' + cur, desc: `رصد ${G_STATE.deadItems.length} صنف راكد. تخفيض هامشها بـ 8% يحولها إلى سيولة نقدية لتمويل الأصناف السريعة.`, link: 'inv_rep3_deadstock.php', linkTxt: 'مصفوفة البضاعة الراكدة', q: 'deadstock' },
        { n: 4, title: 'محرك باريتو والتصنيف الثلاثي (ABC Analysis)', color: '#22d3ee', badge: G_STATE.starItems.length + ' صنف فئة A', desc: `يصنف الأصناف حسب مساهمتها في الإيرادات (A/B/C) لضمان توفر الأصناف الاستراتيجية بنسبة 100%.`, link: 'inv_rep19_abc.php', linkTxt: 'تحليل تركيز ABC', q: 'abc' },
        { n: 5, title: 'محرك العائد الاستثماري على المخزون (GMROII)', color: '#fbbf24', badge: 'تعظيم العائد', desc: `يقيس كم جنيهاً من الربح يحققه كل جنيه مستثمر في بضاعة العنابر، ويكتشف الأصناف ضعيفة العائد.`, link: 'inv_rep14_gmroii.php', linkTxt: 'تقرير العائد GMROII', q: 'pricing' },
        { n: 6, title: 'متنبئ أيام التغطية ونفاد المخزون (Days of Supply)', color: '#38bdf8', badge: 'حماية التوريد', desc: `يحسب كم يوماً يكفي الرصيد الحالي لكل صنف بناءً على معدل السحب اليومي الفعلي.`, link: 'inv_rep2_coverage.php', linkTxt: 'تقرير أيام التغطية', q: 'eoq' },
        { n: 7, title: 'رادار انحراف تكلفة الشراء وزمن توريد المصانع', color: '#fb923c', badge: 'رقابة الموردين', desc: `يقارن أسعار الشراء الأخيرة بالمتوسط المرجح ويكتشف أي ارتفاع مفاجئ في تكلفة التوريد.`, link: 'inv_rep12_cost_variance.php', linkTxt: 'انحراف التكلفة المرجحة', q: 'pricing' },
        { n: 8, title: 'محرك توازن العنابر والمناقلات الذكية', color: '#34d399', badge: 'توزيع مثالي', desc: `يقترح مناقلة الأصناف من المستودعات المكدسة إلى الفروع الأكثر سحباً بدلاً من الشراء المكرر.`, link: 'inventory-adjustment.php?tab=transfer', linkTxt: 'المناقلة بين المخازن', q: 'shrinkage' },
        { n: 9, title: 'مبتكر حزم العروض الترويجية (AI Smart Bundling)', color: '#ec4899', badge: 'تسريع المبيعات', desc: `يدمج صنفاً راكداً مع صنف نجمي سريع الدوران في عرض بيعي واحد لتصريف الرواكد بدون خسارة.`, link: 'direct-sales.php', linkTxt: 'إصدار عرض في المبيعات', q: 'bundling' },
        { n: 10, title: 'محاكي سيناريوهات الطلب الموسمي والطاقة الاستيعابية', color: '#818cf8', badge: 'تخطيط استباقي', desc: `يحاكي أثر المواسم وتقلبات السوق على سعة العنابر والاحتياج الشرائي المستقبلي.`, link: 'inv_rep20_simulator.php', linkTxt: 'محاكي سيناريوهات النواقص', q: 'eoq' }
    ];
    G_STATE.engines = engines;

    document.getElementById('goods-10-engines').innerHTML = engines.map(e => `
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
                <button type="button" onclick="askGoodsAI('${e.q}')">🤖 توصية AI</button>
            </div>
        </div>
    `).join('');

    const rBody = document.getElementById('goods-reorder-tbody');
    const topS = G_STATE.shortages.slice(0, 12);
    if (topS.length === 0) {
        rBody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:20px;color:#34d399;font-weight:800;">✅ جميع الأصناف فوق حد الأمان المخزني!</td></tr>`;
    } else {
        rBody.innerHTML = topS.map(it => {
            const orderQty = Math.max(25, (it.min * 2) - it.qty);
            return `
                <tr>
                    <td style="font-weight:800;color:#fff;">${it.name}</td>
                    <td style="font-weight:900;color:${it.qty < 0 ? '#fb7185' : '#fbbf24'};">${fmtG(it.qty)} ${it.qty < 0 ? '(سالب!)' : ''}</td>
                    <td>${fmtG(it.min)}</td>
                    <td style="font-weight:900;color:#34d399;">+${fmtG(orderQty)} وحدة</td>
                    <td style="display:flex;gap:5px;">
                        <a href="purchase.php?item=${encodeURIComponent(it.name)}&qty=${orderQty}" class="act-pill" style="color:#fbbf24;background:rgba(245,158,11,0.16);">🛒 شراء</a>
                        <a href="item-ledger.php?item=${encodeURIComponent(it.name)}" class="act-pill" style="color:#38bdf8;background:rgba(56,189,248,0.16);">🏷️ كرت الصنف</a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    const mBody = document.getElementById('goods-matrix-tbody');
    const rows = [
        ...G_STATE.deadItems.slice(0, 6).map(x => ({ ...x, tag: '🧊 راكد (فئة C)', col: '#fb7185', act: 'تخفيض 8% أو دمجه في عرض ترويجي' })),
        ...G_STATE.starItems.slice(0, 6).map(x => ({ ...x, tag: '⭐ نجمي (فئة A)', col: '#34d399', act: `مباع (${fmtG(x.sold)}) — حافظ على مخزون أمان مضاعف` }))
    ];
    if (rows.length === 0) {
        mBody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;">لا توجد أصناف راكدة حالياً.</td></tr>`;
    } else {
        mBody.innerHTML = rows.map(it => `
            <tr>
                <td style="font-weight:800;color:#fff;">${it.name}</td>
                <td>${fmtG(it.qty)}</td>
                <td style="font-weight:800;color:#22d3ee;">${fmtG(Math.max(0, it.qty) * it.cost)} ${cur}</td>
                <td><span style="color:${it.col};font-weight:800;">${it.tag}</span></td>
                <td style="font-size:0.77rem;color:#cbd5e1;">${it.act}</td>
            </tr>
        `).join('');
    }
}

function askGoodsAI(topic) {
    const out = document.getElementById('goods-ai-output');
    out.classList.add('visible');
    const cur = G_STATE.currency;
    const s0 = G_STATE.shortages[0];
    const d0 = G_STATE.deadItems[0];
    const st0 = G_STATE.starItems[0];

    let html = `<h4 style="margin:0 0 8px;color:#a7f3d0;">📦 التوصية التنفيذية لمستشار البضائع والمخازن AI:</h4><ul style="margin:0;padding-right:18px;">`;
    if (topic === 'eoq') {
        html += `<li><strong>خطة الشراء الفوري (EOQ):</strong> لديك <strong>${G_STATE.shortages.length} صنف</strong> وصل لحد الطلب ${s0 ? `(أهمها: <strong>${s0.name}</strong> برصيد ${fmtG(s0.qty)})` : ''}. اضغط زر (🛒 شراء) بجانب الصنف في الجدول أدناه لتحويله إلى فاتورة مشتريات جاهزة.</li>`;
    } else if (topic === 'deadstock') {
        html += `<li><strong>خطة تسييل الرواكد:</strong> إجمالي رأس المال المجمد في البضائع الراكدة <strong>${fmtG(G_STATE.deadValue)} ${cur}</strong> عبر <strong>${G_STATE.deadItems.length} صنف</strong> ${d0 ? `(أكبرها: <strong>${d0.name}</strong>)` : ''}. تسييل 30% منها يحرر <strong>${fmtG(G_STATE.deadValue * 0.3)} ${cur}</strong> نقداً.</li>`;
    } else if (topic === 'bundling') {
        html += `<li><strong>ابتكار الحزم البيعية (Smart Bundling):</strong> اربط الصنف الراكد ${d0 ? `(<strong>${d0.name}</strong>)` : ''} مع الصنف النجمي الأكثر طلباً ${st0 ? `(<strong>${st0.name}</strong>)` : ''} في فاتورة مبيعات واحدة بخصم مشترك 5% لتصريف المخزون الراكد فوراً.</li>`;
    } else if (topic === 'shrinkage') {
        html += `<li><strong>ضبط الأرصدة السالبة والجرد:</strong> تم رصد <strong>${G_STATE.negatives.length} صنف برصيد سالب</strong>. افتح (شاشة التسوية الجردية) أو سجل فواتير المشتريات المعلقة لتصحيح الأرصدة.</li>`;
    } else {
        html += `<li><strong>ملخص هندسة المخزون:</strong> القيمة الإجمالية <strong>${fmtG(G_STATE.totalValue)} ${cur}</strong>، النواقص الحرجة <strong>${G_STATE.shortages.length} صنف</strong>، والأصناف النجمية السريعة <strong>${G_STATE.starItems.length} صنف</strong>.</li>`;
    }
    html += `</ul>`;
    out.innerHTML = html;
}

function exportGoodsAdvisorCSV() {
    const rows = [
        ['تقرير مستشار الذكاء الاصطناعي للبضائع والمخازن', new Date().toLocaleString('ar-EG')],
        ['المحرك المخزني', 'الحالة', 'التفاصيل والتوصية'],
        ...G_STATE.engines.map(e => [e.title, e.badge, e.desc])
    ];
    const csv = '\uFEFF' + rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
    a.download = 'AI_Goods_Advisor_' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

document.addEventListener('DOMContentLoaded', initGoodsAdvisor);
</script>
<script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
