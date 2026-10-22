<?php $page_title = 'استلام وتسليم مبيعات - بوابة أذونات المنصرف والأمن المخزني'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
})();
</script>
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استلام وتسليم مبيعات - إدارة أذونات المنصرف والبوابات المخزنية</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
    <style>
        /* ═══ LIGHTWEIGHT, ZERO-JANK SMOOTH SCROLLING & CLEAN DELIVERY DESIGN ═══ */
        html {
            overflow-y: scroll !important;
            overflow-x: hidden !important;
            height: auto !important;
            scroll-behavior: auto !important;
            overscroll-behavior-y: auto !important;
        }
        body {
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
            height: auto !important;
            overscroll-behavior-y: auto !important;
        }
        /* Hide ambient orbs that cause GPU repaint overhead while scrolling */
        .ambient-bg {
            display: none !important;
        }

        .gdn-kinetic-btn, .btn, .level-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 10px !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 800 !important;
            cursor: pointer;
            position: relative !important;
            overflow: hidden !important;
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.22s ease, border-color 0.22s ease, filter 0.2s ease !important;
        }
        .gdn-kinetic-btn::after, .btn::after, .level-tab::after, .gdn-receive-unit-box::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -130%;
            width: 65%;
            height: 200%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.28) 50%, rgba(255,255,255,0) 100%);
            transform: rotate(25deg);
            pointer-events: none;
            transition: left 0.55s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .gdn-kinetic-btn:hover:not(:disabled)::after, .btn:hover:not(:disabled)::after, .level-tab:hover:not(:disabled)::after, .gdn-receive-unit-box:hover::after {
            left: 145%;
        }
        .gdn-kinetic-btn:hover:not(:disabled), .btn:hover:not(:disabled), .level-tab:hover:not(:disabled) {
            transform: translateY(-2.5px) scale(1.045) !important;
            filter: brightness(1.1);
            border-color: rgba(110, 231, 183, 0.85) !important;
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.55), 0 6px 16px rgba(0, 0, 0, 0.45), inset 0 0 10px rgba(255, 255, 255, 0.22) !important;
        }
        .gdn-kinetic-btn:active:not(:disabled), .btn:active:not(:disabled) {
            transform: translateY(0) scale(0.97) !important;
        }
        .gdn-receive-unit-box {
            position: relative !important;
            overflow: hidden !important;
            background: linear-gradient(145deg, #07221c 0%, #041310 100%) !important;
            border: 2px solid rgba(16, 185, 129, 0.45) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
            transform: translateY(0) scale(1) !important;
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s ease, border-color 0.25s ease !important;
            box-sizing: border-box !important;
        }
        .gdn-receive-unit-box::after {
            content: '';
            position: absolute;
            top: -60%;
            left: -135%;
            width: 75%;
            height: 220%;
            background: linear-gradient(105deg, rgba(255,255,255,0) 20%, rgba(255,255,255,0.14) 50%, rgba(255,255,255,0) 80%);
            transform: rotate(24deg);
            pointer-events: none;
            transition: left 0.58s cubic-bezier(0.22, 1, 0.36, 1);
            z-index: 1;
        }
        .gdn-receive-unit-box > * {
            position: relative !important;
            z-index: 2 !important;
        }
        .gdn-receive-unit-box:hover::after {
            left: 155%;
        }
        .gdn-receive-unit-box.primary-box,
        .gdn-receive-unit-box.base-box {
            width: 242px !important;
            min-width: 242px !important;
            max-width: 242px !important;
            height: 114px !important;
            min-height: 114px !important;
            max-height: 114px !important;
            padding: 8px 10px !important;
            border-radius: 13px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: stretch !important;
            gap: 5px !important;
        }
        .gdn-receive-unit-box.primary-box {
            background: linear-gradient(145deg, #07221c 0%, #041410 100%) !important;
            border-color: rgba(16, 185, 129, 0.5) !important;
        }
        .gdn-receive-unit-box.base-box {
            background: linear-gradient(145deg, #0b1d33 0%, #061120 100%) !important;
            border-color: rgba(56, 189, 248, 0.5) !important;
        }
        .gdn-receive-unit-box.primary-box:hover,
        .gdn-receive-unit-box.base-box:hover,
        .gdn-receive-unit-box.units-mirror-box:hover {
            transform: translateY(-4px) scale(1.075) !important;
            border-color: #34d399 !important;
            box-shadow: 0 0 28px rgba(16, 185, 129, 0.88), 0 0 12px rgba(52, 211, 153, 0.8), 0 12px 26px rgba(0, 0, 0, 0.75) !important;
            z-index: 12;
        }
        .gdn-receive-unit-box.primary-box:hover {
            background: linear-gradient(145deg, #082720 0%, #041511 100%) !important;
        }
        .gdn-receive-unit-box.base-box:hover {
            background: linear-gradient(145deg, #0d223b 0%, #071324 100%) !important;
        }
        .gdn-receive-unit-box .gdn-unit-fixed-pill,
        .gdn-receive-unit-box .gdn-unit-fixed-pill:hover,
        .gdn-receive-unit-box .gdn-box-caption {
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.95) !important;
            filter: none !important;
        }

        .glass-shimmer-card {
            background: rgba(15, 23, 42, 0.94);
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 14px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.78rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.35);
        }
        .status-badge.delivered {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.35);
        }
        .status-badge.stage1 {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.35);
        }

        .gdn-item-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(30, 41, 59, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.2);
            color: #f8fafc;
            font-weight: 700;
            font-size: 0.82rem;
        }
        .gdn-unit-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            font-weight: 800;
            font-size: 0.74rem;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }
        .gdn-base-unit-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            background: rgba(99, 102, 241, 0.16);
            color: #a5b4fc;
            font-weight: 800;
            font-size: 0.74rem;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }
        .gdn-borderless-input {
            background: rgba(15, 23, 42, 0.9) !important;
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            color: #f8fafc !important;
            border-radius: 8px !important;
            padding: 7px 11px !important;
            font-weight: 800 !important;
            font-family: 'Cairo', sans-serif !important;
            outline: none !important;
            transition: border-color 0.15s ease !important;
        }
        .gdn-borderless-input:focus {
            border-color: #10b981 !important;
        }

        .pos-grid th, .pos-grid td { font-size: 0.88rem; padding: 9px 10px; }
        .pos-grid td.qty-col { font-weight: 900; color: #38bdf8; font-size: 1rem; }
        .pos-grid tbody tr:hover { background: rgba(56, 189, 248, 0.06) !important; }

        /* Toast */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:10px 18px; border-radius:10px; font-weight:800; font-size:0.86rem; display:flex; align-items:center; gap:8px; pointer-events:auto; background:#0f172a; box-shadow:0 8px 24px rgba(0,0,0,0.45); font-family:'Cairo',sans-serif; max-width:90vw; }
        .toast.success { border:1px solid rgba(16,185,129,0.5); color:#6ee7b7; }
        .toast.error { border:1px solid rgba(239,68,68,0.5); color:#fca5a5; }
        .toast.warning { border:1px solid rgba(245,158,11,0.5); color:#fcd34d; }

        /* Light Theme Support */
        :root.light-theme body { background: #f8fafc !important; }
        :root.light-theme .glass-shimmer-card { background: #ffffff !important; border-color: #cbd5e1 !important; color: #0f172a !important; box-shadow: 0 2px 10px rgba(0,0,0,0.04) !important; }
        :root.light-theme .gdn-item-pill { background: #f1f5f9 !important; color: #0f172a !important; border-color: #cbd5e1 !important; }
        :root.light-theme .gdn-unit-badge { color: #065f46 !important; background: #d1fae5 !important; }
        :root.light-theme .gdn-base-unit-badge { color: #3730a3 !important; background: #e0e7ff !important; }
        :root.light-theme .gdn-borderless-input { background: #ffffff !important; color: #0f172a !important; border-color: #cbd5e1 !important; }
    </style>
</head>
<body style="overflow-y: auto; min-height: 100vh;">

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <!-- QR Scanner Library -->
    <script defer src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="pos-wrapper" style="padding: 14px 16px; max-width: 99.4vw; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 16px;">

        <!-- 1. TOP HEADER & GATE ROLE SWITCHER -->
        <div class="glass-shimmer-card" style="padding: 14px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <button onclick="history.back()" class="gdn-kinetic-btn" title="العودة" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.18); color: #f8fafc; padding: 8px 14px;">
                    ↩️ رجوع
                </button>
                <a href="dashboard-pro.php" class="gdn-kinetic-btn" style="background: rgba(56,189,248,0.14); border: 1px solid rgba(56,189,248,0.35); color: #38bdf8; text-decoration: none; padding: 8px 14px; font-size: 0.86rem;">🏠 الرئيسية</a>
                <a href="direct-sales.php?action=delivery" class="gdn-kinetic-btn" style="background: rgba(16,185,129,0.16); border: 1px solid rgba(16,185,129,0.4); color: #34d399; text-decoration: none; padding: 8px 14px; font-size: 0.86rem;">⚡ شاشة تسليم المبيعات السريع</a>
                <a href="smart-logistics.php?mode=sales" class="gdn-kinetic-btn" style="background: linear-gradient(135deg, rgba(16,185,129,0.28), rgba(14,165,233,0.28)); border: 1.5px solid #38bdf8; color: #ffffff; text-decoration: none; padding: 8px 16px; font-size: 0.88rem; box-shadow: 0 0 18px rgba(56,189,248,0.4);">🚀 البوابة اللوجستية الموحدة (GRN ⇄ GDN)</a>
                <div style="margin-right: 8px;">
                    <h1 style="margin: 0; font-size: 1.25rem; font-weight: 900; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                        <span>📦</span> منظومة إدارة أذونات الاستلام والتسليم المخزنية (بوابات الصرف الـ 4)
                    </h1>
                    <p style="margin: 2px 0 0; font-size: 0.8rem; color: #94a3b8; font-weight: 700;">الفحص الميداني للأصناف، التوثيق بالـ QR والمرفقات، تصاريح خروج البوابة (Gate Pass)، والاعتماد الإداري L4</p>
                </div>
            </div>

            <!-- GATE LEVEL SELECTOR & MOBILE CAMERA BRIDGE -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <button type="button" onclick="openMobileCameraBridgeModal()" id="btn-top-mobile-bridge" class="gdn-kinetic-btn" style="background: linear-gradient(135deg, #059669, #0284c7); border: 1.5px solid #34d399; color: #ffffff; padding: 8px 16px; font-size: 0.88rem; box-shadow: 0 0 18px rgba(16,185,129,0.35);">
                    <span id="mobile-bridge-live-dot" style="width:9px; height:9px; border-radius:50%; background:#fcd34d; display:inline-block;"></span>
                    📱 ربط كاميرا الجوال (مسح متتابع فوري)
                </button>
                <div class="auth-levels-container" id="auth-tabs-container" style="display:flex; gap:8px;"></div>
                <select id="demo-role-select" onchange="simulateUserRole(this.value)" class="gdn-borderless-input" style="padding: 7px 12px !important; font-size: 0.85rem;">
                    <option value="1">🔒 البوابة 1 (كاميرا QR فقط)</option>
                    <option value="2">🔎 البوابة 2 (فحص وفرز الأصناف)</option>
                    <option value="3">🚛 البوابة 3 (أمن البوابة وتصريح الخروج)</option>
                    <option value="4" selected>👑 البوابة 4 (إشراف كامل واعتماد إداري L4)</option>
                </select>
            </div>
        </div>

        <!-- LIVE CONTINUOUS QR SCAN FEEDBACK BANNER (يظهر تلقائياً عند تمرير الأذونات بالتتابع) -->
        <div id="gate-live-qr-banner" style="display:none; padding: 14px 20px; border-radius: 16px; font-weight: 900; font-size: 1.05rem; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; transition: all 0.2s ease; box-shadow: 0 12px 30px rgba(0,0,0,0.45);">
            <div id="gate-live-qr-banner-text" style="display:flex; align-items:center; gap:10px;"></div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span id="gate-live-qr-counter-pill" style="background:rgba(0,0,0,0.35); padding:4px 12px; border-radius:99px; font-size:0.84rem; color:#fff;"></span>
                <button type="button" onclick="document.getElementById('gate-live-qr-banner').style.display='none'" style="background:rgba(255,255,255,0.18); border:none; color:#fff; border-radius:8px; padding:4px 10px; cursor:pointer; font-weight:900;">✕</button>
            </div>
        </div>

        <!-- 2. LIVE KPI SUMMARY BAR -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            <div class="glass-shimmer-card" onclick="setGateRegistryFilter('pending')" style="padding: 16px 20px; border-right: 5px solid #f59e0b; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: #fcd34d;">⏳ فواتير جاهزة للصرف (قيد التسليم)</div>
                    <div id="gate-kpi-pending" style="font-size: 1.8rem; font-weight: 900; color: #fbbf24; margin-top: 4px;">0</div>
                </div>
                <span style="font-size: 2.2rem;">📦</span>
            </div>
            <div class="glass-shimmer-card" onclick="setGateRegistryFilter('stage1')" style="padding: 16px 20px; border-right: 5px solid #38bdf8; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: #7dd3fc;">🚚 خرجت من البوابة (بانتظار اعتماد L4)</div>
                    <div id="gate-kpi-stage1" style="font-size: 1.8rem; font-weight: 900; color: #38bdf8; margin-top: 4px;">0</div>
                </div>
                <span style="font-size: 2.2rem;">🛡️</span>
            </div>
            <div class="glass-shimmer-card" onclick="setGateRegistryFilter('delivered')" style="padding: 16px 20px; border-right: 5px solid #10b981; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: #6ee7b7;">✅ أذونات صرف معتمدة ومكتملة</div>
                    <div id="gate-kpi-delivered" style="font-size: 1.8rem; font-weight: 900; color: #34d399; margin-top: 4px;">0</div>
                </div>
                <span style="font-size: 2.2rem;">📜</span>
            </div>
            <div class="glass-shimmer-card" onclick="setGateRegistryFilter('notes')" style="padding: 16px 20px; border-right: 5px solid #c084fc; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: #e9d5ff;">📊 إجمالي أذونات المنصرف الرسمية (GDN)</div>
                    <div id="gate-kpi-notes" style="font-size: 1.8rem; font-weight: 900; color: #c084fc; margin-top: 4px;">0</div>
                </div>
                <span style="font-size: 2.2rem;">🏷️</span>
            </div>
        </div>

        <!-- 3. INTERACTIVE REGISTRY OF INVOICES & DELIVERY NOTES (سجل أذونات الاستلام والتسليم المباشر - يدعم +1000 إذن) -->
        <div class="glass-shimmer-card" style="padding: 18px 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span style="font-size: 1.4rem;">📋</span>
                    <h3 style="margin: 0; font-size: 1.08rem; font-weight: 900; color: #ffffff;">سجل الفواتير وأذونات الاستلام والتسليم المخزنية (بحث فوري في +1000 إذن وتحكم كامل بالأعمدة)</h3>
                    <span id="gate-reg-match-badge" style="background:rgba(168,85,247,0.18); border:1px solid rgba(168,85,247,0.4); color:#d8b4fe; padding:3px 10px; border-radius:99px; font-size:0.78rem; font-weight:900;">0 إذن</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <input type="text" id="gate-reg-quick-search" class="gdn-borderless-input" placeholder="🔍 ابحث في +1000 إذن (رقم الإذن، العميل، الصنف، المخزن)..." oninput="renderGateRegistryTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); loadInvoiceByNo(this.value.trim());}" style="min-width: 260px; font-size: 0.84rem;">
                    <select id="gate-reg-wh-filter" class="gdn-borderless-input" onchange="renderGateRegistryTable()" style="font-size: 0.8rem; color: #fcd34d !important;">
                        <option value="all">🏢 كل المخازن</option>
                    </select>
                    <select id="gate-reg-limit" class="gdn-borderless-input" onchange="renderGateRegistryTable()" style="font-size: 0.8rem; color: #a7f3d0 !important;">
                        <option value="50">⚡ عرض 50 إذن</option>
                        <option value="100" selected>⚡ عرض 100 إذن</option>
                        <option value="250">⚡ عرض 250 إذن</option>
                        <option value="500">⚡ عرض 500 إذن</option>
                        <option value="99999">📋 عرض الكل (+1000)</option>
                    </select>
                    <button id="reg-tab-pending" onclick="setGateRegistryFilter('pending')" class="gdn-kinetic-btn" style="background: #10b981; color: #fff; padding: 7px 14px; font-size: 0.82rem; border: none;">⏳ بانتظار الصرف</button>
                    <button id="reg-tab-stage1" onclick="setGateRegistryFilter('stage1')" class="gdn-kinetic-btn" style="background: rgba(255,255,255,0.08); color: #e2e8f0; padding: 7px 14px; font-size: 0.82rem; border: 1px solid rgba(255,255,255,0.15);">🚚 بانتظار اعتماد L4</button>
                    <button id="reg-tab-delivered" onclick="setGateRegistryFilter('delivered')" class="gdn-kinetic-btn" style="background: rgba(255,255,255,0.08); color: #e2e8f0; padding: 7px 14px; font-size: 0.82rem; border: 1px solid rgba(255,255,255,0.15);">✅ المسلمة والمعتمدة</button>
                    <button id="reg-tab-all" onclick="setGateRegistryFilter('all')" class="gdn-kinetic-btn" style="background: rgba(255,255,255,0.08); color: #e2e8f0; padding: 7px 14px; font-size: 0.82rem; border: 1px solid rgba(255,255,255,0.15);">📋 الكل</button>
                    <button type="button" onclick="const d=document.getElementById('gate-reg-cols-drawer'); if(d) d.style.display=(d.style.display==='none'?'flex':'none');" class="gdn-kinetic-btn" style="background: rgba(168,85,247,0.18); color: #e9d5ff; padding: 7px 12px; font-size: 0.82rem; border: 1px solid rgba(192,132,252,0.45);">⚙️ ضبط الكولومات (محفوظ ✅)</button>
                    <button onclick="renderGateRegistryTable()" class="gdn-kinetic-btn" style="background: rgba(56,189,248,0.18); color: #38bdf8; padding: 7px 12px; font-size: 0.82rem; border: 1px solid rgba(56,189,248,0.4);">🔄 تحديث</button>
                </div>
            </div>

            <!-- شريط إظهار/إخفاء أعمدة الشاشة الرئيسية الخارجية (يفتح عند الرغبة في التعديل ويحفظ تلقائياً) -->
            <div id="gate-reg-cols-drawer" style="display:none; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; background:rgba(2,6,23,0.75); padding:10px 14px; border-radius:10px; border:1.5px solid rgba(168,85,247,0.45); margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:0.78rem; font-weight:900; color:#e9d5ff;">⚙️ التحكم في أعمدة الجدول الرئيسي (تُحفظ تلقائياً وتثبت في محلها):</span>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="reg-col-inv" checked onchange="toggleGateRegCols(false)" style="accent-color:#10b981;"> 📄 رقم الإذن</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="reg-col-date" checked onchange="toggleGateRegCols(false)" style="accent-color:#10b981;"> 📅 التاريخ والوقت</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="reg-col-cust" checked onchange="toggleGateRegCols(false)" style="accent-color:#10b981;"> 👤 العميل والمستلم</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="reg-col-items" checked onchange="toggleGateRegCols(false)" style="accent-color:#10b981;"> 📦 الأصناف والوحدات</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="reg-col-wh" checked onchange="toggleGateRegCols(false)" style="accent-color:#10b981;"> 🏢 المخزن والحالة</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="reg-col-act" checked onchange="toggleGateRegCols(false)" style="accent-color:#10b981;"> ⚡ إجراءات البوابة</label>
                </div>
                <button type="button" onclick="if(typeof lockAndSaveGateRegCols==='function'){lockAndSaveGateRegCols();}else{document.getElementById('gate-reg-cols-drawer').style.display='none';}" class="gdn-kinetic-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:1px solid #34d399; padding:5px 14px; font-size:0.78rem;">💾 تثبيت وإخفاء الشريط</button>
            </div>

            <div style="overflow-x: auto; border-radius: 10px; border: 1px solid rgba(148,163,184,0.18);">
                <table class="pos-grid" style="width: 100%; margin: 0;">
                    <thead>
                        <tr>
                            <th class="reg-col-inv-cell" style="text-align: right; width: 12%;">رقم الإذن</th>
                            <th class="reg-col-date-cell" style="text-align: center; width: 13%;">التاريخ والوقت</th>
                            <th class="reg-col-cust-cell" style="text-align: right; width: 15%;">العميل والمستلم</th>
                            <th class="reg-col-items-cell" style="text-align: right; width: 32%;">الأصناف (المخزن • الصنف • البيان • الوحدتين)</th>
                            <th class="reg-col-wh-cell" style="text-align: center; width: 12%;">المخزن والحالة</th>
                            <th class="reg-col-act-cell" style="text-align: center; width: 16%;">إجراءات البوابة</th>
                        </tr>
                    </thead>
                    <tbody id="gate-registry-tbody">
                        <!-- Rendered dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. WORKSTATION: SEARCH, ORDER DETAILS, LOGISTICS & CAMERA VERIFICATION -->
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 18px;">
            
            <!-- RIGHT PANEL: OMNIBOX + INVOICE INFO + LOGISTICS FIELDS -->
            <div style="display: flex; flex-direction: column; gap: 16px;">
                
                <!-- OMNIBOX SEARCH -->
                <div id="advanced-search-box" style="position: relative;">
                    <div style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #38bdf8;">
                        🔍
                    </div>
                    <input type="text" id="smart-omnibox" class="gdn-borderless-input" placeholder="⚡ ابحث برقم الفاتورة، رقم إذن الصرف (GDN)، الباركود، اسم العميل، أو اسم الصنف ثم اضغط Enter..." 
                        style="width: 100%; padding: 13px 46px 13px 16px !important; font-size: 0.96rem;"
                        onkeydown="handleSmartSearch(event)" oninput="filterSmartSearch(this.value)">
                    <div id="smart-search-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; margin-top: 6px; border-radius: 12px; background: #0f172a; border: 1px solid #10b981; box-shadow: 0 15px 35px rgba(0,0,0,0.7); z-index: 9999; max-height: 260px; overflow-y: auto;"></div>
                </div>

                <!-- INVOICE & LOGISTICS INFO CARD -->
                <div id="inv-info" class="glass-shimmer-card" style="flex: 1; display: flex; flex-direction: column; padding: 0;">
                    <div style="padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.2);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.3rem;">📄</span>
                            <h3 style="margin: 0; font-size: 1rem; font-weight: 900; color: #ffffff;">بطاقة إذن الاستلام والتسليم وبيانات البوابة اللوجستية</h3>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div id="lbl-admin-status" style="font-size: 0.82rem; font-weight: 800; color: #38bdf8; background: rgba(56,189,248,0.12); padding: 4px 12px; border-radius: 99px; border: 1px solid rgba(56,189,248,0.3);">
                                جاهز للاستدعاء
                            </div>
                        </div>
                    </div>

                    <div style="padding: 18px 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px;">
                        <div style="background: rgba(0,0,0,0.25); padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 800; display: block; margin-bottom: 4px;">📄 رقم الفاتورة / الإذن</label>
                            <div id="lbl-inv-no" style="font-size: 1.2rem; font-weight: 900; color: #38bdf8; font-family: 'Inter', monospace;">---</div>
                        </div>
                        <div style="background: rgba(0,0,0,0.25); padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 800; display: block; margin-bottom: 4px;">📅 تاريخ ووقت الإذن</label>
                            <div id="lbl-date" style="font-size: 0.98rem; font-weight: 800; color: #f8fafc;">---</div>
                        </div>
                        <div style="background: rgba(0,0,0,0.25); padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 800; display: block; margin-bottom: 4px;">🚦 حالة التسليم</label>
                            <div id="lbl-status" class="status-badge">---</div>
                        </div>
                        <div style="background: rgba(0,0,0,0.25); padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 800; display: block; margin-bottom: 4px;">👤 العميل صاحب الفاتورة</label>
                            <div id="lbl-customer" style="font-size: 1.05rem; font-weight: 900; color: #6ee7b7;">---</div>
                        </div>
                    </div>

                    <!-- LOGISTICS & GATE PASS INPUTS -->
                    <div style="padding: 0 20px 18px 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 12px;">
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 800; color: #fcd34d; display: block; margin-bottom: 4px;">🏢 مخزن الصرف:</label>
                            <select id="gate-warehouse-select" class="gdn-borderless-input" style="width: 100%;"></select>
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 800; color: #6ee7b7; display: block; margin-bottom: 4px;">🤝 اسم المستلم الفعلي:</label>
                            <input type="text" id="gate-receiver-name" class="gdn-borderless-input" placeholder="اسم المستلم أو المندوب..." style="width: 100%;">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 800; color: #7dd3fc; display: block; margin-bottom: 4px;">🚚 اسم السائق / الدفار:</label>
                            <input type="text" id="gate-driver-name" class="gdn-borderless-input" placeholder="اسم السائق..." style="width: 100%;">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 800; color: #c084fc; display: block; margin-bottom: 4px;">🔢 رقم المركبة / اللوحة:</label>
                            <input type="text" id="gate-truck-plate" class="gdn-borderless-input" placeholder="رقم اللوحة..." style="width: 100%;">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 800; color: #38bdf8; display: block; margin-bottom: 4px;">🎫 تصريح البوابة (Gate Pass):</label>
                            <input type="text" id="gate-pass-no" class="gdn-borderless-input" placeholder="GP-AUTO..." style="width: 100%;">
                        </div>
                    </div>

                    <div style="margin-top: auto; padding: 10px 20px; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(0,0,0,0.2); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.74rem; color: #94a3b8; font-weight: 700;">🔐 البصمة الأمنية للإذن (Secure Hash):</span>
                        <div id="lbl-hash" style="font-family: 'Inter', monospace; font-size: 0.78rem; color: #38bdf8;">---</div>
                    </div>
                </div>
            </div>

            <!-- LEFT PANEL: CAMERA QR SCANNER & PHOTO ATTACHMENT -->
            <div class="glass-shimmer-card" style="display: flex; flex-direction: column; overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 0.95rem; font-weight: 900; color: #ffffff;">📷 التحقق البصري والـ QR</h3>
                    <div id="verification-badge" style="font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 99px; border: 1px solid rgba(148, 163, 184, 0.3); color: #94a3b8; background: rgba(148, 163, 184, 0.1);">بانتظار التحقق</div>
                </div>

                <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.35); padding:7px 10px; border-radius:10px;">
                        <label style="display:flex; align-items:center; gap:7px; font-size:0.78rem; font-weight:900; color:#6ee7b7; cursor:pointer;">
                            <input type="checkbox" id="gate-continuous-auto-deliver-chk" checked style="width:16px; height:16px; accent-color:#10b981;">
                            <span>⚡ استلام وخصم متتابع فوري بدون ضغط أي زر (10 أذونات تلو بعضها)</span>
                        </label>
                    </div>

                    <div id="qr-reader-wrap" style="display:none; flex-direction:column; gap:8px;">
                        <div id="qr-reader" style="width: 100%; border-radius: 12px; overflow: hidden; border: 2px solid #10b981; aspect-ratio: 4/3; background: #000;"></div>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                            <span style="font-size:0.76rem; color:#34d399; font-weight:900;">🟢 الكاميرا في وضع المسح المتتابع — مرّر الأذونات تباعاً</span>
                            <button type="button" class="gdn-kinetic-btn" onclick="stopCamera()" style="padding:5px 12px; font-size:0.76rem; background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.45); color:#fca5a5;">⏹️ إيقاف الكاميرا</button>
                        </div>
                    </div>
                    
                    <div id="camera-placeholder" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,0.25); border: 1px dashed rgba(56,189,248,0.35); border-radius: 12px; padding: 16px; text-align: center;">
                        <div style="font-size: 2.2rem; margin-bottom: 6px;">📱</div>
                        <span style="font-size: 0.8rem; color: #cbd5e1; font-weight: 700; margin-bottom: 12px;">اربط كاميرا الجوال باللابتوب أو امسح مباشرة للاستلام المتتابع الفوري</span>
                        <div style="display: flex; gap: 8px; width: 100%; flex-wrap: wrap;">
                            <button class="gdn-kinetic-btn" onclick="openMobileCameraBridgeModal()" style="flex: 1; min-width: 150px; font-size: 0.82rem; padding: 9px 12px; background: linear-gradient(135deg, #059669, #0284c7); color: #fff; border: 1px solid #34d399;">
                                📱 ربط كاميرا الجوال
                            </button>
                            <button class="gdn-kinetic-btn" onclick="startCamera()" style="flex: 1; min-width: 140px; font-size: 0.82rem; padding: 9px 12px; background: linear-gradient(135deg, #0284c7, #1d4ed8); color: #fff; border: none;">
                                📷 مسح QR متتابع
                            </button>
                            <button class="gdn-kinetic-btn" onclick="printCurrentGatePass()" style="flex: 1; min-width: 110px; font-size: 0.82rem; padding: 9px 12px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none;">
                                🖨️ طباعة الإذن
                            </button>
                        </div>
                    </div>

                    <div style="padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.08);">
                        <input type="file" id="invoice-image" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">
                        <button class="gdn-kinetic-btn" onclick="document.getElementById('invoice-image').click()" style="width: 100%; font-size: 0.84rem; padding: 9px; background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4); color: #fcd34d;">
                            📎 إرفاق صورة توثيق خروج البضاعة
                        </button>
                        <div id="attachment-preview" style="margin-top: 10px; display: none; text-align: center;">
                            <img id="attachment-thumb" src="" alt="صورة المرفق" style="max-height: 75px; border-radius: 8px; border: 1px solid #10b981; margin-bottom: 4px; width: 100%; object-fit: cover;">
                            <div style="color: #34d399; font-size: 0.75rem; font-weight: 800;">✅ تم إرفاق صورة توثيق البوابة</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. SMART AI ITEMS INSPECTION & PARTIAL/FULL DELIVERY GRID -->
        <div class="glass-shimmer-card" style="padding: 18px 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">📦</span>
                    <div>
                        <h3 style="margin: 0; font-size: 1.08rem; font-weight: 900; color: #ffffff;">بطاقة استلام وصرف البضائع (مرونة الوحدتين: استلم هنا أو هنا — تحكم كامل بالأعمدة والعرض)</h3>
                        <span style="font-size: 0.78rem; color: #a7f3d0; font-weight: 700;">تحكم في إظهار أو إخفاء أي عمود (المخزن، الصنف، البيان، الوحدات الأساسية، الكمية، الوحدات الفرعية، السعر) وتوسيع الخانات</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <button type="button" onclick="const d=document.getElementById('gate-opt-cols-drawer'); if(d) d.style.display=(d.style.display==='none'?'flex':'none');" class="gdn-kinetic-btn" style="background: rgba(168,85,247,0.18); border: 1px solid rgba(192,132,252,0.5); color: #e9d5ff; padding: 6px 12px; font-size: 0.78rem;">
                        ⚙️ ضبط الكولومات (محفوظ ✅)
                    </button>
                    <span id="gate-items-summary-badge" style="background: rgba(16,185,129,0.2); border: 1px solid rgba(52,211,153,0.45); color: #6ee7b7; padding: 5px 14px; border-radius: 99px; font-size: 0.84rem; font-weight: 900;">0 أصناف</span>
                    <button type="button" onclick="fillAllGateDeliverQtys()" class="gdn-kinetic-btn" style="background: rgba(56,189,248,0.16); border: 1px solid rgba(56,189,248,0.4); color: #38bdf8; padding: 6px 14px; font-size: 0.8rem;">
                        ⚡ تسليم كامل الكميات
                    </button>
                </div>
            </div>

            <!-- شريط إظهار أو إخفاء أي عمود في جدول البطاقة (يفتح عند الرغبة في التعديل ويحفظ تلقائياً ويثبت في محله) -->
            <div id="gate-opt-cols-drawer" style="display:none; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; background:rgba(2,6,23,0.8); padding:10px 14px; border-radius:10px; border:1.5px solid rgba(168,85,247,0.45); margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:0.78rem; font-weight:900; color:#e9d5ff;">⚙️ التحكم في أعمدة البطاقة (تُحفظ تلقائياً وتثبت في محلها):</span>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-wh" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> 🏢 المخزن</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-item" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> 📦 الصنف</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-spec" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> ⚖️ البيان / المعامل</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-punit" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> 🏷️ الوحدة الأساسية</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-qty" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> 📊 الكمية</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-units" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> 🔢 عدد الوحدات (المعادل)</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-price" onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> 💰 السعر والإجمالي</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-deliver" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> 📥 استلام هنا أو هنا (الوحدتين)</label>
                    <label style="display:inline-flex; align-items:center; gap:5px; font-size:0.78rem; font-weight:800; color:#e2e8f0; cursor:pointer;"><input type="checkbox" id="gate-chk-col-status" checked onchange="toggleGateOptionalCols(false)" style="accent-color:#10b981;"> ✅ الحالة</label>
                </div>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <div style="display:inline-flex; align-items:center; gap:4px; background:rgba(2,6,23,0.65); padding:4px 8px; border-radius:8px; border:1px solid rgba(148,163,184,0.25);">
                        <span style="font-size:0.75rem; color:#94a3b8; font-weight:800;">📏 عرض الخانات:</span>
                        <button type="button" onclick="setGateTableScale('normal')" class="gdn-kinetic-btn" style="padding:3px 8px; font-size:0.74rem; background:rgba(255,255,255,0.06); color:#e2e8f0; border:none;">قياسي</button>
                        <button type="button" onclick="setGateTableScale('wide')" class="gdn-kinetic-btn" style="padding:3px 8px; font-size:0.74rem; background:rgba(56,189,248,0.18); color:#38bdf8; border:none;">عريض</button>
                        <button type="button" onclick="setGateTableScale('ultrawide')" class="gdn-kinetic-btn" style="padding:3px 8px; font-size:0.74rem; background:rgba(16,185,129,0.22); color:#6ee7b7; border:none;">أقصى توسعة +</button>
                    </div>
                    <button type="button" onclick="applyGateColPreset('focus')" class="gdn-kinetic-btn" style="background: rgba(245,158,11,0.18); border: 1px solid rgba(251,191,36,0.45); color: #fcd34d; padding: 5px 10px; font-size: 0.76rem;">🎯 وضع تركيز المخزنجي</button>
                    <button type="button" onclick="applyGateColPreset('all')" class="gdn-kinetic-btn" style="background: rgba(16,185,129,0.16); border: 1px solid rgba(52,211,153,0.4); color: #6ee7b7; padding: 5px 10px; font-size: 0.76rem;">👁️ إظهار الكل</button>
                    <button type="button" onclick="if(typeof lockAndSaveGateOptCols==='function'){lockAndSaveGateOptCols();}else{document.getElementById('gate-opt-cols-drawer').style.display='none';}" class="gdn-kinetic-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:1px solid #34d399; padding:5px 14px; font-size:0.78rem;">💾 تثبيت وإخفاء الشريط</button>
                </div>
            </div>

            <div class="grid-scroll-wrap" style="overflow-x: auto; border-radius: 10px; border: 1px solid rgba(148,163,184,0.22);">
                <table class="pos-grid" id="delivery-grid" style="width: 100%; min-width: 1380px; margin: 0;">
                    <thead id="delivery-thead">
                        <tr>
                            <th style="width: 42px; text-align: center;">#</th>
                            <th class="gate-opt-col-wh" style="width: 155px; text-align: right;">المخزن</th>
                            <th class="gate-opt-col-item" style="min-width: 210px; text-align: right;">الصنف</th>
                            <th class="gate-opt-col-spec" style="width: 165px; text-align: center;">البيان / المعامل</th>
                            <th class="gate-opt-col-punit" style="width: 135px; text-align: center;">الوحدة الأساسية</th>
                            <th class="gate-opt-col-qty" style="width: 100px; text-align: center;">الكمية</th>
                            <th class="gate-opt-col-units" style="width: 155px; text-align: center; color: #a5b4fc;">عدد الوحدات</th>
                            <th class="gate-opt-col-price" style="width: 105px; text-align: center; color: #fcd34d; display: none;">السعر</th>
                            <th class="gate-opt-col-price" style="width: 115px; text-align: center; color: #6ee7b7; display: none;">الإجمالي</th>
                            <th class="gate-opt-col-deliver" style="min-width: 390px; text-align: center; color: #34d399;">كمية التسليم الآن (استلم بالأساسية ⇄ أو بالفرعية)</th>
                            <th class="gate-opt-col-status" style="width: 115px; text-align: center;">الحالة</th>
                        </tr>
                    </thead>
                    <tbody id="delivery-tbody">
                        <tr><td colspan="11" class="empty-state" style="padding: 36px; text-align: center; color: #94a3b8; font-weight: 800;">👇 اضغط على أي فاتورة من السجل العلوي أو ابحث عنها لعرض أصنافها وتفاصيلها هنا فوراً</td></tr>
                    </tbody>
                    <tfoot id="delivery-tfoot"></tfoot>
                </table>
            </div>
        </div>

        <!-- 6. WAREHOUSE & DRIVER NOTES PANEL -->
        <div id="review-notes-panel" class="glass-shimmer-card" style="display: block; border-color: rgba(251, 191, 36, 0.35);">
            <div style="padding: 12px 20px; border-bottom: 1px solid rgba(251,191,36,0.2); background: rgba(251,191,36,0.08); display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span>📝</span>
                <span style="font-weight: 900; font-size: 0.92rem; color: #fbbf24;">ملاحظات أمين المخزن والسائق وتوثيق حالة التسليم</span>
                <span style="font-size: 0.76rem; color: #cbd5e1; margin-right: auto;">تُحفظ رسمياً في سجل إذن الصرف وتظهر للإدارة</span>
            </div>
            <div style="padding: 16px 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label style="font-size: 0.8rem; font-weight: 800; color: #fbbf24; display: block; margin-bottom: 6px;">📦 ملاحظات أمين المخزن / حالة البضاعة:</label>
                    <textarea id="warehouse-notes" rows="2" class="gdn-borderless-input" placeholder="سجل حالة البضاعة عند التسليم أو أي ملاحظات للمخزن..." style="width: 100%; resize: vertical;" oninput="onReviewNotesChange()"></textarea>
                    <div id="warehouse-notes-hint" style="font-size: 0.74rem; color: #f87171; display: none; margin-top: 4px;">يرجى كتابة ملاحظة المخزن.</div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 800; color: #7dd3fc; display: block; margin-bottom: 6px;">🚚 ملاحظات السائق / المستلم (اختياري):</label>
                    <textarea id="personal-notes" rows="2" class="gdn-borderless-input" placeholder="أي تفاصيل تخص السائق أو المستلم أو بوابة الخروج..." style="width: 100%; resize: vertical;"></textarea>
                </div>
            </div>
        </div>

        <!-- 7. ACTION FOOTER -->
        <div class="glass-shimmer-card" style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display:flex; align-items:center; gap: 10px;">
                <div id="verification-status" style="color: #fbbf24; font-weight: 800; font-size: 0.86rem; padding: 8px 16px; background: rgba(245,158,11,0.12); border-radius: 10px; border: 1px solid rgba(245,158,11,0.35); display: flex; align-items: center; gap: 8px;">
                    ⚡ جاهز لفحص واعتماد التسليم
                </div>
            </div>
            <div class="footer-actions" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="gdn-kinetic-btn" onclick="shareCurrentGateWhatsApp()" style="background: rgba(34,197,94,0.18); border: 1px solid rgba(34,197,94,0.45); color: #4ade80; font-size: 0.88rem; padding: 10px 18px;">
                    💬 إشعار واتساب
                </button>
                <button class="gdn-kinetic-btn" onclick="printCurrentGatePass()" style="background: rgba(56,189,248,0.18); border: 1px solid rgba(56,189,248,0.45); color: #38bdf8; font-size: 0.88rem; padding: 10px 18px;">
                    🖨️ طباعة إذن المنصرف (Gate Pass)
                </button>
                <button class="gdn-kinetic-btn" id="btn-partial" onclick="undoCurrentGateDelivery()" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #fca5a5; font-size: 0.88rem; padding: 10px 18px;">
                    ↩️ إلغاء الصرف وإعادة للمخزن
                </button>
                <button class="gdn-kinetic-btn" id="btn-admin-close" onclick="executeAdminClosureL4()" style="display: none; background: linear-gradient(135deg, #8b5cf6, #6d28d9); border: 1px solid rgba(192,132,252,0.5); color: #fff; font-size: 0.92rem; padding: 10px 22px; box-shadow: 0 6px 20px rgba(139,92,246,0.4);">
                    👑 اعتماد إداري نهائي وإغلاق الإذن (L4)
                </button>
                <button class="gdn-kinetic-btn" id="btn-deliver" onclick="executeDelivery()" style="font-size: 0.98rem; padding: 11px 28px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: 1px solid rgba(52,211,153,0.6); box-shadow: 0 8px 25px rgba(16,185,129,0.4);">
                    🚚 تأكيد واعتماد التسليم والصرف الآن
                </button>
            </div>
        </div>
    </div>

    <!-- MOBILE CAMERA DIRECT BRIDGE MODAL (ربط كاميرا الجوال باللابتوب مباشرة) -->
    <div id="mobile-camera-bridge-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div style="background:linear-gradient(160deg, #0f172a, #020617); width:100%; max-width:620px; border-radius:24px; border:2px solid #10b981; box-shadow:0 30px 70px rgba(0,0,0,0.8); overflow:hidden; color:#f8fafc;">
            <div style="background:linear-gradient(135deg, #064e3b, #0284c7); padding:16px 22px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:1.8rem;">📱</span>
                    <div>
                        <h3 style="margin:0; font-size:1.18rem; font-weight:900; color:#fff;">ربط كاميرا الجوال باللابتوب مباشرة (بث ليزري متتابع)</h3>
                        <p style="margin:3px 0 0; font-size:0.8rem; color:#a7f3d0; font-weight:700;">مرّر الأذونات الـ 10 واحداً تلو الآخر أمام كاميرا الجوال — تُستلم وتُخصم تلقائياً بدون ضغط أي زر!</p>
                    </div>
                </div>
                <button type="button" onclick="closeMobileCameraBridgeModal()" style="background:rgba(255,255,255,0.18); border:none; color:#fff; width:36px; height:36px; border-radius:10px; font-size:1.1rem; font-weight:900; cursor:pointer;">✕</button>
            </div>
            <div style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                <div id="bridge-modal-conn-status" style="background:rgba(16,185,129,0.14); border:1.5px solid rgba(52,211,153,0.45); padding:10px 14px; border-radius:12px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                    <span style="font-weight:900; color:#6ee7b7; font-size:0.9rem;">📡 قناة الربط الفوري نشطة وجاهزة لاستقبال مسحات الجوال</span>
                    <span id="bridge-modal-Counter-badge" style="background:#065f46; color:#fff; padding:3px 10px; border-radius:99px; font-size:0.78rem; font-weight:900;">المستلم الآن: 0</span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:16px; align-items:center;">
                    <div style="background:#ffffff; padding:14px; border-radius:18px; text-align:center; border:3px solid #10b981;">
                        <img id="bridge-pairing-qr-img" src="" alt="Pairing QR" style="width:190px; height:190px; display:block; margin:0 auto;">
                        <div style="color:#064e3b; font-weight:900; font-size:0.82rem; margin-top:6px;">امسح هذا الكود بكاميرا الجوال لربطه فوراً</div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div style="background:rgba(30,41,59,0.65); padding:12px; border-radius:12px; border:1px solid rgba(148,163,184,0.2); font-size:0.83rem; line-height:1.65;">
                            <div><b>1️⃣ المسحة الأولى للإذن:</b> يتم خصم واستلام الإذن بالـ QR فوراً بدون ضغط أي زر.</div>
                            <div><b>2️⃣ تمرير 10 أذونات متتالية:</b> يستلم الواحد تلو الآخر تلقائياً مع نغمة ليزر.</div>
                            <div style="color:#fca5a5;"><b>3️⃣ المسحة الثانية لنفس الإذن:</b> يعطيك تنبيهاً فورياً: <b>«🚫 تم تسليم هذا الإذن — الإذن ملغي!»</b></div>
                        </div>
                        <a id="bridge-direct-open-link" href="mobile-qr-gun.php" target="_blank" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; text-decoration:none; justify-content:center; padding:10px;">
                            🚀 فتح شاشة عدسة الجوال المباشرة الآن
                        </a>
                        <button type="button" onclick="startCamera(); closeMobileCameraBridgeModal();" class="gdn-kinetic-btn" style="background:rgba(56,189,248,0.18); border:1px solid #38bdf8; color:#38bdf8; justify-content:center; padding:9px;">
                            📷 أو تشغيل الكاميرا المباشرة على هذا الجهاز
                        </button>
                    </div>
                </div>

                <!-- Live Feed inside Modal -->
                <div style="background:rgba(2,6,23,0.75); border:1px solid rgba(148,163,184,0.25); border-radius:12px; padding:10px 14px; max-height:150px; overflow-y:auto;">
                    <div style="font-size:0.78rem; color:#94a3b8; font-weight:900; margin-bottom:6px;">⚡ سجل الأذونات الملتقطة مباشرة من كاميرا الجوال:</div>
                    <div id="bridge-modal-live-feed" style="font-size:0.84rem; color:#cbd5e1;">بانتظار تمرير أول إذن على كاميرا الجوال...</div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/sales-delivery.js?v=20261022_live_qr_v7"></script>
    <script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
    <script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
