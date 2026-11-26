<?php
if (isset($_GET['api']) && ($_GET['api'] === 'set_posted_batch' || $_GET['api'] === 'fetch_all_movements')) {
    header('Content-Type: application/json; charset=UTF-8');
    require_once __DIR__ . '/api/db.php';
    if (!isset($pdo) || !$pdo) { require_once __DIR__ . '/db_bridge.php'; $pdo = getDBConnection(); }
    $tblMap = [
        'erp_sales_invoices_v1' => 'sales_invoices',
        'erp_purchase_invoices_v1' => 'purchase_invoices',
        'erp_single_receipts_v1' => 'single_receipts',
        'erp_receipts_v1' => 'receipts',
        'erp_single_payments_v1' => 'single_payments',
        'erp_payments_v1' => 'payments',
        'erp_journal_vouchers_v1' => 'journal_vouchers',
        'erp_bank_transfers_v1' => 'bank_transfers',
        'erp_safe_transfers_v1' => 'safe_transfers',
        'erp_purchase_returns_v1' => 'purchase_returns',
        'erp_sales_returns_v1' => 'sales_returns'
    ];
    if ($_GET['api'] === 'fetch_all_movements') {
        $out = [];
        if ($pdo) {
            $existingTbls = array_flip($pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN));
            foreach ($tblMap as $metaKey => $tbl) {
                $out[$metaKey] = [];
                if (!isset($existingTbls[$tbl])) continue;
                try {
                    // Fetch latest 220 rows + any unposted rows directly in SQL for <15ms speed
                    $rows = $pdo->query('SELECT id, data FROM `' . $tbl . '` ORDER BY id DESC LIMIT 350')->fetchAll(PDO::FETCH_ASSOC);
                    $seenIds = [];
                    foreach ($rows as $r) {
                        $rid = (int)$r['id'];
                        if (isset($seenIds[$rid])) continue;
                        $seenIds[$rid] = true;
                        $d = json_decode($r['data'] ?? '', true);
                        if (is_array($d) && empty($d['deleted'])) {
                            if (empty($d['id'])) $d['id'] = (string)$rid;
                            if (isset($d['items']) && is_array($d['items']) && count($d['items']) > 25) {
                                $d['_totalItemsCount'] = count($d['items']);
                                $d['items'] = array_slice($d['items'], 0, 25);
                            }
                            unset($d['gpsLocation'], $d['auditTrail'], $d['rawOcrText']);
                            $out[$metaKey][] = $d;
                        }
                    }
                } catch (Exception $e) {}
            }
        }
        echo json_encode(['success' => true, 'movements' => $out], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
    $updated = 0;
    $nowMs = (int)round(microtime(true) * 1000);
    if ($pdo && $items) {
        $byTbl = [];
        foreach ($items as $it) {
            $mk = (string)($it['metaKey'] ?? '');
            if (!isset($tblMap[$mk])) continue;
            $tbl = $tblMap[$mk];
            $isP = !empty($it['isPosted']);
            foreach ((array)($it['refs'] ?? []) as $rf) {
                $rf = trim((string)$rf);
                if ($rf !== '') $byTbl[$tbl][$rf] = $isP;
            }
        }
        foreach ($byTbl as $tbl => $refMap) {
            try {
                $rows = $pdo->query("SELECT id, data FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);
                $upd = $pdo->prepare("UPDATE `{$tbl}` SET data = ? WHERE id = ?");
                foreach ($rows as $r) {
                    $d = json_decode($r['data'], true);
                    if (!is_array($d)) continue;
                    $keys = array_filter([
                        (string)($d['id'] ?? ''), (string)($d['invoiceNo'] ?? ''), (string)($d['voucherNo'] ?? ''),
                        (string)($d['paymentNo'] ?? ''), (string)($d['receiptNo'] ?? ''), (string)$r['id']
                    ], fn($v) => $v !== '');
                    $matched = null;
                    foreach ($keys as $k) { if (array_key_exists($k, $refMap)) { $matched = $refMap[$k]; break; } }
                    if ($matched === null) continue;
                    $d['isPosted'] = $matched;
                    $d['postedViaHub'] = $matched;
                    if ($tbl === 'sales_invoices' || $tbl === 'purchase_invoices') {
                        $d['status'] = $matched ? 'posted' : 'pending';
                    }
                    $d['updatedAtMs'] = $nowMs;
                    $upd->execute([json_encode($d, JSON_UNESCAPED_UNICODE), $r['id']]);
                    $updated++;
                }
            } catch (Exception $e) {}
        }
    }
    echo json_encode(['success' => true, 'updated' => $updated], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php $page_title = 'ترحيل الحسابات'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js?v=<?= @filemtime(__DIR__ . '/js/lang.js') ?: '2027' ?>"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en' || l==='zh') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang',l);
    }
})();
</script>
<head>
<script>
(function initScreenMaster13ToneEarly() {
  var ckM = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/);
  var rawTone = (ckM ? decodeURIComponent(ckM[1]) : '') || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || '';
  var rawTheme = localStorage.getItem('magaies_active_theme') || localStorage.getItem('magaiees_global_theme') || localStorage.getItem('ui_theme') || 'warm_cream';
  var tMap = {dark_midnight:'midnight_obsidian',pure_oled:'pure_oled',mocha_brown:'mocha_brown',slate_twilight:'slate_twilight',ivory_default:'warm_cream',royal_bronze:'warm_cream',platinum_gray:'exec_gray',crystal_silver:'ultra_white',sage_pearl:'warm_cream',sky_alabaster:'ultra_white',rose_blossom:'warm_cream',warm_mocha_dark:'warm_cream',deep_slate_dark:'exec_gray'};
  var activeTone = tMap[rawTone] ? rawTone : (['dark_midnight','pure_oled','mocha_brown','slate_twilight','midnight_obsidian','dark','black','navy'].indexOf(rawTheme) !== -1 ? 'dark_midnight' : 'ivory_default');
  var activeKey = tMap[activeTone] || 'warm_cream';
  var isLight = ['dark_midnight','pure_oled','mocha_brown','slate_twilight'].indexOf(activeTone) === -1;
  document.documentElement.setAttribute('data-light-tone', activeTone);
  document.documentElement.setAttribute('data-theme', activeKey);
  document.documentElement.classList.toggle('light-theme', isLight);
  document.documentElement.classList.toggle('dark-theme', !isLight);
  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) { document.body.setAttribute('data-light-tone', activeTone); document.body.setAttribute('data-theme', activeKey); document.body.classList.toggle('light-theme', isLight); document.body.classList.toggle('dark-theme', !isLight); }
  });
})();
</script>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ترحيل الحسابات | السيطرة (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=<?php echo file_exists('css/components.css') ? filemtime('css/components.css') : 1; ?>">
<link rel="stylesheet" href="css/screens/posting-accounts-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/posting-accounts-s1.css') ?: '2027' ?>">
</head>
<body>

    <div class="toast-container" id="toast-container"></div>

    <!-- Ambient Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="page-wrapper" id="pa-proportional-root">
        <!-- Unified Premium Header -->
        <?php $header_theme_class = 'header-theme-reports'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Filters -->
        <div class="filters-wrapper pa-inbound-twin-bar">
            <div class="pa-twin-header-row">
                <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                    <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#0284c7,#10b981);border:1.5px solid #7dd3fc;display:flex;align-items:center;justify-content:center;font-size:1.05rem;">📑</div>
                    <h3 style="margin:0;font-size:1.08rem;font-weight:900;color:#ffffff;display:flex;align-items:center;gap:8px;white-space:nowrap;">
                        <span>ترحيل الحسابات والقيود المجمعة</span>
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981;box-shadow:0 0 8px #10b981;"></span>
                    </h3>
                </div>

                <div class="status-toggle" style="height:36px;padding:3px;border-radius:9px;background:#060b16;border:1.5px solid rgba(56,189,248,0.35);">
                    <button class="status-btn active unposted" id="status-unposted" onclick="setStatus('unposted')" style="height:28px;padding:0 14px;font-size:0.85rem;font-weight:900;">⏳ معلقة (<span id="pa-kpi-unposted-count">0</span>)</button>
                    <button class="status-btn posted" id="status-posted" onclick="setStatus('posted')" style="height:28px;padding:0 14px;font-size:0.85rem;font-weight:900;">✅ مرحّلة (<span id="pa-kpi-posted-count">0</span>)</button>
                </div>

                <div class="search-box" style="flex:1;min-width:210px;max-width:340px;">
                    <span class="icon" style="font-size:0.95rem;">🔍</span>
                    <input type="text" id="search-input" placeholder="بحث فوري بالمرجع أو الحساب أو المبلغ..." oninput="window.debouncedPaFilter ? window.debouncedPaFilter() : handleFilterChange()" style="height:36px;padding:0 34px 0 10px;border-radius:8px;background:#0f172a;border:1.6px solid rgba(56,189,248,0.45);font-size:0.86rem;font-weight:900;">
                </div>

                <div class="date-filters" style="gap:6px;">
                    <span style="font-size:0.82rem;font-weight:900;color:#94a3b8;">من</span>
                    <input type="date" id="date-from" class="date-input" onchange="handleFilterChange()" style="height:36px;padding:0 8px;border-radius:8px;background:#0f172a;border:1.5px solid rgba(148,163,184,0.32);font-size:0.84rem;font-weight:900;">
                    <span style="font-size:0.82rem;font-weight:900;color:#94a3b8;">إلى</span>
                    <input type="date" id="date-to" class="date-input" onchange="handleFilterChange()" style="height:36px;padding:0 8px;border-radius:8px;background:#0f172a;border:1.5px solid rgba(148,163,184,0.32);font-size:0.84rem;font-weight:900;">
                </div>

                <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                    <button type="button" onclick="if(window.InboundBankakManager){InboundBankakManager.toggleDrawer(true);InboundBankakManager.setFilter('awaiting_approval');}" style="height:36px;padding:0 13px;border-radius:8px;background:linear-gradient(135deg,#059669,#0d9488);border:1.6px solid #34d399;color:#ffffff!important;font-family:'Cairo','Tajawal',sans-serif;font-size:0.86rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
                        <span>📥</span><span>إشعارات واتساب</span>
                    </button>
                    <button type="button" onclick="toggleAllRows({checked:true});postAllChecked();" style="height:36px;padding:0 14px;border-radius:8px;background:linear-gradient(135deg,#0e7490,#0f766e);border:1.6px solid #2dd4bf;color:#f0fdfa!important;font-family:'Cairo','Tajawal',sans-serif;font-size:0.86rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;" title="تحديد وترحيل كافة الحركات المعلقة المعروضة">
                        <span>✔️</span><span>ترحيل الكل</span>
                    </button>
                    <button type="button" onclick="if(window.resetPaFiltersAndSearch)window.resetPaFiltersAndSearch();loadAllTransactions();" style="height:36px;width:36px;border-radius:8px;background:rgba(56,189,248,0.16);border:1.5px solid #38bdf8;color:#7dd3fc;font-size:0.95rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;" title="تحديث وتصفير الفلاتر">🔄</button>
                </div>
            </div>

            <div class="pa-twin-tabs-row" id="type-filters">
                <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                    <button class="type-chip active" id="type-all" onclick="setType('all')">الكل</button>
                    <button class="type-chip" id="type-whatsapp" onclick="setType('whatsapp')" style="border-color:rgba(16,185,129,0.5);color:#34d399;">📲 إشعارات واتساب</button>
                    <button class="type-chip" id="type-sales" onclick="setType('sales')">🛒 المبيعات</button>
                    <button class="type-chip" id="type-purchases" onclick="setType('purchases')">📦 المشتريات</button>
                    <button class="type-chip" id="type-receipt" onclick="setType('receipt')">📥 حركة مالية / قبض مجمع</button>
                    <button class="type-chip" id="type-payment" onclick="setType('payment')">📤 حركة مالية / صرف مجمع</button>
                    <button class="type-chip" id="type-transfer" onclick="setType('transfer')">🏦 تحويلات بنكية وخزن</button>
                    <button class="type-chip" id="type-journal" onclick="setType('journal')">📒 قيود يومية</button>
                </div>
                <div style="display:flex;align-items:center;gap:10px;font-size:0.85rem;font-weight:900;font-variant-numeric:tabular-nums;">
                    <span style="padding:5px 11px;border-radius:8px;background:#0f172a;border:1.5px solid rgba(245,158,11,0.45);color:#fde047;">💰 إجمالي المعلق: <strong id="pa-kpi-unposted-sum">0.00</strong></span>
                    <span id="pa-kpi-balance-seal" style="padding:5px 10px;border-radius:8px;background:rgba(16,185,129,0.16);border:1.5px solid #10b981;color:#34d399;">⚖️ القيد المزدوج متوازن 100%</span>
                    <span id="pa-kpi-unposted-tafqeet" style="display:none;"></span>
                </div>
            </div>
            <div id="pa-parked-batches-bar" style="display:none;"></div>
        </div>

        <!-- Split Layout -->
        <div class="split-layout">
            
            <!-- Master Pane (Right) -->
            <div class="master-pane">
                <div class="table-scroll">
                    <table class="erp-table" id="data-table">
                        <thead>
                            <tr onclick="resetSelection()" style="pointer-events: auto; cursor: pointer;" title="اضغط هنا لإلغاء التحديد وتكبير الجدول">
                                <th style="width: 40px; text-align: center;" onclick="event.stopPropagation()">
                                    <input type="checkbox" id="check-all-rows" onchange="toggleAllRows(this)" onclick="event.stopPropagation()" title="تحديد الكل">
                                </th>
                                <th style="width: 100px;">النوع</th>
                                <th style="width: 100px;">التاريخ</th>
                                <th style="width: 130px;">رقم المرجع</th>
                                <th>العميل / المورد / البيان</th>
                                <th class="extra-column" style="width: 200px;">الصنف</th>
                                <th class="extra-column" style="width: 70px; text-align: center;">الكمية</th>
                                <th class="extra-column" style="width: 100px; text-align: left;">سعر الوحدة</th>
                                <th style="width: 110px;">الاستحقاق</th>
                                <th style="width: 110px;">طريقة الدفع</th>
                                <th style="width: 140px; text-align: left;">المبلغ الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Rows injected via JS -->
                        </tbody>
                    </table>

                    <div id="no-data-msg" style="display: none; text-align: center; padding: 36px 20px; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="font-size: 3rem; margin-bottom: 12px;">📭</div>
                        <h2 style="font-size: 1.2rem; margin: 0 0 14px 0;">لا توجد عمليات مطابقة</h2>
                        <button onclick="injectDummyData()" style="background: rgba(59,130,246,0.18); color: #60a5fa; border: 2.2px solid #3b82f6; padding: 8px 18px; border-radius: 10px; cursor: pointer; font-family: 'Tajawal', sans-serif; font-weight: 900; font-size: 0.9rem;">
                            ➕ بيانات تجريبية
                        </button>
                    </div>
                </div>

                <!-- Sticky Action Bar for Master Pane -->
                <div class="sticky-action-bar" id="sticky-bar">
                    <div style="font-size: 0.95rem; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                        المحدد: <span class="selected-count" id="selected-count">0</span>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <button class="btn-post-batch pa-dna-btn pa-dna-save" onclick="postAllChecked()">
                            <span>✔️</span> <span>ترحيل المحدد</span> <kbd class="pa-kbd">F10</kbd>
                        </button>
                        <button type="button" class="pa-dna-btn pa-dna-delete" onclick="resetSelection()">
                            <span>✖</span> <span>إلغاء التحديد</span> <kbd class="pa-kbd">Esc</kbd>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Detail Pane (Left) -->
            <div class="detail-pane" id="detail-pane">
                <div class="empty-detail" id="empty-detail">
                    <div class="empty-detail-icon">📑</div>
                    <h3 style="margin-bottom:6px; font-size: 1.2rem;">اختر عملية للمراجعة</h3>
                </div>
                
                <div id="detail-content" class="detail-inner" style="display: none;">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ CUSTOM CONFIRM & POSTING ANIMATION MODAL ═══ -->
    <div id="custom-confirm-modal" class="modal-overlay">
        <div class="modal-box">
            <!-- Close Button -->
            <button class="modal-close" onclick="closeConfirmModal()">✖️</button>
            
            <!-- Icon/Title -->
            <div class="modal-header">
                <div class="modal-icon">📋</div>
                <h3 class="modal-title" id="modal-title">تأكيد ترحيل الحسابات</h3>
            </div>
            
            <!-- Body Content -->
            <div class="modal-body" id="modal-body">
                <!-- Filled dynamically -->
            </div>
            
            <!-- Actions -->
            <div class="modal-actions" id="modal-actions" style="display:flex; justify-content:space-between; gap:10px;">
                <button class="modal-btn confirm pa-dna-btn pa-dna-save" id="modal-btn-confirm"><span>✔️</span> <span>تأكيد واعتماد الترحيل</span> <kbd class="pa-kbd">F10</kbd></button>
                <button class="modal-btn cancel pa-dna-btn pa-dna-delete" onclick="closeConfirmModal()"><span>✖</span> <span>تراجع وإلغاء</span> <kbd class="pa-kbd">Esc</kbd></button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : 1; ?>"></script>
    <script defer src="js/posting-accounts.js?v=<?php echo file_exists('js/posting-accounts.js') ? filemtime('js/posting-accounts.js') : 1; ?>"></script>
    <script defer src="js/elsajana-universal-ui-2027.js?v=<?php echo file_exists('js/elsajana-universal-ui-2027.js') ? filemtime('js/elsajana-universal-ui-2027.js') : 1; ?>"></script>
    <script>
        function injectDummyData() {
            const dummySales = [{
                id: 'INV-1001', invoiceNo: 'INV-1001', date: new Date().toISOString().split('T')[0],
                customerId: 'CUST-001', customerName: 'شركة الآفاق الحديثة',
                totalAmount: 12500, paymentMethod: 'credit', paymentAccountId: '',
                dueDate: '2026-07-15', description: 'مبيعات آجلة - توريد أجهزة مكتبية', isPosted: false,
                items: [
                    { itemName: 'حاسب آلي مكتبي', qty: 2, price: 4000, total: 8000 },
                    { itemName: 'طابعة ليزر', qty: 3, price: 1500, total: 4500 }
                ]
            }];
            const dummyPayment = [{
                id: 'PAY-205', voucherNo: 'PAY-205', date: new Date().toISOString().split('T')[0],
                toAccount: 'exp-rent', fromAccount: 'bank-002',
                amountLocal: 4000, toStatement: 'دفع إيجار المقر بشيك', isPosted: false,
                paymentMethod: 'cheque', dueDate: '2026-06-30',
                description: 'سداد الإيجار الشهري عبر شيك بنكي'
            }];
            localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(dummySales));
            localStorage.setItem('erp_single_payments_v1', JSON.stringify(dummyPayment));
            if (window.showToast) window.showToast('تم حقن الفاتورة والسند التجريبي بنجاح!', 'success');
            setTimeout(() => location.reload(), 600);
        }

        /* محرك الدستور الأول (1 من 14) لشاشة ترحيل الحسابات */
        (function initPostingAccountsConstitution1() {
            if (document.documentElement.dataset.paC1Applied === '1') return;
            document.documentElement.dataset.paC1Applied = '1';

            const PA_TONES = [
                { id: 'dark_midnight', name: 'الداكن الملكي الأصلي', themeKey: 'midnight_obsidian', isDark: true, bg: '#010308', card: '#152342' },
                { id: 'pure_oled', name: 'الأسود الفحمي (OLED)', themeKey: 'pure_oled', isDark: true, bg: '#000000', card: '#0d1322' },
                { id: 'mocha_brown', name: 'البني الموكا الفخم', themeKey: 'mocha_brown', isDark: true, bg: '#1a0f0a', card: '#2b1b12' },
                { id: 'slate_twilight', name: 'الكحلي التنفيذي', themeKey: 'slate_twilight', isDark: true, bg: '#0d1526', card: '#192642' },
                { id: 'ivory_default', name: 'العاجي الذهبي الدافئ', themeKey: 'warm_cream', isDark: false, bg: '#f6f1e7', card: '#fbf8f1' },
                { id: 'royal_bronze', name: 'الرملي البرونزي', themeKey: 'warm_cream', isDark: false, bg: '#ecdec8', card: '#f7efe2' },
                { id: 'platinum_gray', name: 'البلاتينيوم الرمادي', themeKey: 'exec_gray', isDark: false, bg: '#e2e8f0', card: '#f1f5f9' },
                { id: 'crystal_silver', name: 'الفضي الكريستالي', themeKey: 'ultra_white', isDark: false, bg: '#f1f5f9', card: '#ffffff' },
                { id: 'sage_pearl', name: 'اللؤلؤي الأخضر الهادئ', themeKey: 'warm_cream', isDark: false, bg: '#dfece6', card: '#eef6f2' },
                { id: 'sky_alabaster', name: 'الرخامي السماوي', themeKey: 'ultra_white', isDark: false, bg: '#e0eef9', card: '#f0f7fd' },
                { id: 'warm_mocha_dark', name: 'الكاكاو الدافئ', themeKey: 'warm_cream', isDark: false, bg: '#dfd1c5', card: '#efe6de' },
                { id: 'deep_slate_dark', name: 'الرصاصي الداكن المريح', themeKey: 'exec_gray', isDark: false, bg: '#cbd5e1', card: '#e2e8f0' }
            ];
            const bc = typeof BroadcastChannel !== 'undefined' ? new BroadcastChannel('elsajana_global_appearance_viewport_sync') : null;
            if (bc) {
                bc.onmessage = function(ev) {
                    if (!ev || !ev.data) return;
                    if (ev.data.type === 'TONE_SYNC' && ev.data.toneId) window.applyPaC1Tone(ev.data.toneId, true);
                    if (ev.data.type === 'ZOOM_SYNC' && ev.data.scale) window.applyPaC1ProportionalZoom(ev.data.scale, true);
                };
            }
            window.applyPaC1Tone = function(toneId, fromRemote) {
                if (window.ElsajanaUniversalUI2027 && typeof window.ElsajanaUniversalUI2027.applyUnifiedSystemTone === 'function') {
                    window.ElsajanaUniversalUI2027.applyUnifiedSystemTone(toneId, !fromRemote);
                } else {
                    const t = PA_TONES.find(function(x) { return x.id === toneId || x.themeKey === toneId; }) || PA_TONES[0];
                    document.documentElement.setAttribute('data-theme', t.themeKey);
                    document.documentElement.setAttribute('data-light-tone', t.id);
                    document.documentElement.classList.toggle('light-theme', !t.isDark);
                }
                if (!fromRemote && bc) { try { bc.postMessage({ type: 'TONE_SYNC', toneId: toneId }); } catch (e) {} }
            };
            window.openPaC1TonePopover = function(btn) {
                if (window.ElsajanaUniversalUI2027 && typeof window.ElsajanaUniversalUI2027.openUniversalErpTonePopover === 'function') {
                    return window.ElsajanaUniversalUI2027.openUniversalErpTonePopover(btn);
                }
            };
            window.applyPaC1ProportionalZoom = function(rawScale, fromRemote) {
                let s = Math.max(0.5, Math.min(10.0, Math.round((parseFloat(rawScale) || 1.0) * 100) / 100));
                if (window.ElsajanaUniversalUI2027 && typeof window.ElsajanaUniversalUI2027.setTelescopicZoom === 'function') {
                    window.ElsajanaUniversalUI2027.setTelescopicZoom(s);
                } else {
                    const stg = document.getElementById('pa-zoom-stage');
                    if (stg) stg.style.zoom = String(s);
                    const bdg = document.getElementById('pa-c1-zoom-badge');
                    if (bdg) bdg.textContent = '📐 أبعاد (6:8) ' + Math.round(s * 100) + '%';
                }
                try { localStorage.setItem('magaies_telescopic_zoom_scale', String(s)); localStorage.setItem('erp_pa_c15_zoom_scale', String(s)); } catch (e) {}
                if (!fromRemote && bc) { try { bc.postMessage({ type: 'ZOOM_SYNC', scale: s }); } catch (e) {} }
            };
            window.addEventListener('DOMContentLoaded', function() {
                const savedZoom = parseFloat(localStorage.getItem('magaies_telescopic_zoom_scale') || localStorage.getItem('erp_pa_c15_zoom_scale') || '1') || 1.0;
                try{localStorage.removeItem('erp_pa_c15_zoom_scale');localStorage.removeItem('magaies_telescopic_zoom_scale');document.body.classList.remove('pa-mask-balances','pa-zero-header-100dvh');}catch(e){}
            });
        })();
    </script>
    <?php include_once 'includes/inbound-bankak-drawer.php'; ?>
<script src="js/screens/posting-accounts-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/posting-accounts-j1.js') ?: '2027' ?>"></script>
</body>
</html>

