<?php
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); }
 
$db_sales_grid_config = null;
$preloadedUnitsJson = '[]';
$preloadedItemsJson = '[]';
$salNextFinNo = 'R' . date('ym') . '0001';
try {
    require_once __DIR__ . '/api/db.php';
    if (file_exists(__DIR__ . '/db_bridge.php')) require_once __DIR__ . '/db_bridge.php';
    $systemUsdRate = function_exists('getSystemUsdExchangeRate') ? getSystemUsdExchangeRate($pdo ?? null) : 5999.0;
    if (isset($pdo)) {
        if (function_exists('getDBConnection')) { $pdo = getDBConnection(); }
        $activeSalesTenantId = function_exists('resolveCurrentTenantId') ? resolveCurrentTenantId($pdo ?? null) : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 1);
        try {
            $salFinPrefix = 'R' . date('ym');
            $salMaxFinSeq = 0;
            $stFinTop = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ?");
            $stFinTop->execute([$activeSalesTenantId ?: 1]);
            while ($rFinTop = $stFinTop->fetch(PDO::FETCH_ASSOC)) {
                $dFinTop = json_decode($rFinTop['data'], true);
                if (!empty($dFinTop['deleted'])) continue;
                $idFinTop = isset($dFinTop['voucherNo']) && $dFinTop['voucherNo'] !== '' ? (string)$dFinTop['voucherNo'] : (isset($dFinTop['id']) ? (string)$dFinTop['id'] : '');
                if (strpos($idFinTop, $salFinPrefix) === 0) {
                    $sqTop = (int)substr($idFinTop, strlen($salFinPrefix));
                    if ($sqTop > $salMaxFinSeq && $sqTop < 999999) $salMaxFinSeq = $sqTop;
                }
            }
            $salNextFinNo = $salFinPrefix . str_pad($salMaxFinSeq + 1, 4, '0', STR_PAD_LEFT);
        } catch (\Throwable $_ef) {}
        $tGridKey = 'tenant_' . $activeSalesTenantId . '_grid_config_sales_grid';
        $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN (" . $pdo->quote($tGridKey) . ", 'grid_config_sales_grid') ORDER BY (setting_key = " . $pdo->quote($tGridKey) . ") DESC LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['setting_value'])) {
            $db_sales_grid_config = json_decode($row['setting_value'], true);
        }

        if (function_exists('getDBConnection')) { $pdo = getDBConnection(); }
        $activeSalesTenantId = function_exists('resolveCurrentTenantId') ? resolveCurrentTenantId($pdo ?? null) : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 1);
        $tUnitsKey = 'tenant_' . $activeSalesTenantId . '_erp_global_units_v1';
        $stmtUnits = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN (?, 'erp_global_units_v1') ORDER BY (setting_key = ?) DESC LIMIT 1");
        $stmtUnits->execute([$tUnitsKey, $tUnitsKey]);
        $uVal = $stmtUnits ? $stmtUnits->fetchColumn() : null;
        if ($uVal) $preloadedUnitsJson = $uVal;

        $stmtItems = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtItems->execute([$activeSalesTenantId ?? 1]);
        $pItems = [];
        while ($r = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($r['data'])) {
                $d = json_decode($r['data'], true);
                if ($d && empty($d['deleted'])) $pItems[] = $d;
            }
        }
        $preloadedItemsJson = json_encode($pItems, JSON_UNESCAPED_UNICODE);
    }
} catch (\Throwable $e) {}
?>
<?php $page_title = 'المبيعات'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="has-sidebar">
<script src="js/lang.js"></script>
<script defer src="js/magaies-offline-sync.js?v=<?php echo file_exists('js/magaies-offline-sync.js') ? filemtime('js/magaies-offline-sync.js') : '20261104'; ?>"></script>
<script>
window._PRELOADED_UNITS = <?= $preloadedUnitsJson ?: '[]' ?>;
window._PRELOADED_ITEMS = <?= $preloadedItemsJson ?: '[]' ?>;
try {
    let tId = '<?= isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : '' ?>';
    if (!tId) {
        try {
            const u = JSON.parse(localStorage.getItem('user') || '{}');
            tId = u.tenant_id || localStorage.getItem('currentTenantId') || 'default';
        } catch(e) { tId = 'default'; }
    }
    if (Array.isArray(window._PRELOADED_UNITS) && window._PRELOADED_UNITS.length > 0) {
        const uStr = JSON.stringify(window._PRELOADED_UNITS);
        localStorage.setItem('erp_global_units_v1', uStr);
        if (tId) localStorage.setItem('tenant_' + tId + '_erp_global_units_v1', uStr);
    }
    if (Array.isArray(window._PRELOADED_ITEMS) && window._PRELOADED_ITEMS.length > 0) {
        const iStr = JSON.stringify(window._PRELOADED_ITEMS);
        try { localStorage.removeItem("erp_items_v1"); } catch(_e) {}
        if (tId) localStorage.setItem("tenant_" + tId + "_erp_items_v1", iStr);
        else localStorage.setItem("erp_items_v1", iStr);
    }
} catch(e) {}
</script>
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

<script>
(function initScreenMaster7ThemeEarly() {
  var getCk = function(n) { var m = document.cookie.match(new RegExp("(?:^|; )" + n + "=([^;]*)")); return m ? decodeURIComponent(m[1]) : ""; };
  var rawTone = getCk("magaies_light_tone") || localStorage.getItem("magaies_light_tone") || localStorage.getItem("pur_light_tone_palette") || "";
  var raw = getCk("magaies_active_theme") || localStorage.getItem("magaies_active_theme") || localStorage.getItem("magaiees_global_theme") || localStorage.getItem("magaiees_mobile_theme") || localStorage.getItem("ui_theme") || "warm_cream";
  var aliasMap = {
    "dark_midnight": "midnight_obsidian", "dark_mocha": "mocha_brown", "dark_oled": "pure_oled", "dark_slate": "slate_twilight",
    "ivory_default": "warm_cream", "royal_bronze": "warm_cream", "platinum_gray": "exec_gray", "crystal_silver": "ultra_white",
    "sage_pearl": "warm_cream", "sky_alabaster": "ultra_white", "warm_mocha_dark": "warm_cream", "deep_slate_dark": "exec_gray",
    "warm-alabaster": "warm_cream", "beige": "warm_cream", "ivory": "warm_cream", "light-linen": "warm_cream",
    "pure-platinum": "ultra_white", "offwhite": "ultra_white", "white": "ultra_white", "light": "ultra_white",
    "pearl-platinum": "exec_gray", "nordic-paper": "warm_cream", "solar_contrast": "warm_cream",
    "midnight-executive": "midnight_obsidian", "navy-kohl": "midnight_obsidian", "royal-blue": "midnight_obsidian", "ocean-navy": "midnight_obsidian", "navy": "midnight_obsidian",
    "sepia-brown": "mocha_brown", "desert-mocha": "mocha_brown", "brown": "mocha_brown",
    "obsidian-charcoal": "pure_oled", "midnight": "pure_oled", "black": "pure_oled", "dark": "pure_oled",
    "twilight-indigo": "slate_twilight", "emerald-forest": "slate_twilight", "forest": "slate_twilight"
  };
  var activeKey = (rawTone && aliasMap[rawTone]) ? aliasMap[rawTone] : (aliasMap[raw] || raw);
  var valid7 = ["warm_cream", "ultra_white", "exec_gray", "midnight_obsidian", "mocha_brown", "pure_oled", "slate_twilight"];
  if (!valid7.includes(activeKey)) activeKey = "warm_cream";
  var isLight = ["warm_cream", "ultra_white", "exec_gray"].includes(activeKey);
  var effTone = isLight ? (rawTone && rawTone !== "dark_midnight" ? rawTone : "ivory_default") : "dark_midnight";

  document.documentElement.setAttribute("data-theme", activeKey);
  document.documentElement.setAttribute("data-light-tone", effTone);
  if (isLight) { document.documentElement.classList.add("light-theme"); document.documentElement.classList.remove("dark-theme"); }
  else { document.documentElement.classList.remove("light-theme"); document.documentElement.classList.add("dark-theme"); }

  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) {
      document.body.setAttribute("data-theme", activeKey);
      document.body.setAttribute("data-light-tone", effTone);
      if (isLight) { document.body.classList.add("light-theme"); document.body.classList.remove("dark-theme"); }
      else { document.body.classList.remove("light-theme"); document.body.classList.add("dark-theme"); }
    }
  });
})();
</script>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المبيعات | Al-Sajana Protocol</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : '12'; ?>">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
<link rel="stylesheet" href="css/screens/direct-sales-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-s1.css') ?: '2027' ?>">


    <link rel="stylesheet" href="css/screens/direct-sales-inline-10.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-10.css') ?: '2027' ?>">
<link rel="stylesheet" href="css/screens/direct-sales-s2.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-s2.css') ?: '2027' ?>">
    
<link rel="stylesheet" href="css/choices.min.css?v=<?php echo file_exists('css/choices.min.css') ? filemtime('css/choices.min.css') : '11.2.3'; ?>" />
<script defer src="js/choices.min.js?v=<?php echo file_exists('js/choices.min.js') ? filemtime('js/choices.min.js') : '11.2.3'; ?>"></script>
 <link rel="stylesheet" href="css/screens/direct-sales-inline-11.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-11.css') ?: '2027' ?>">

    <!-- Server-Side Pre-Rendered Grid Configuration from MySQL Database -->
    <script>
        window.CurrentGridConfig = <?php echo json_encode($db_sales_grid_config, JSON_UNESCAPED_UNICODE) ?: 'null'; ?>;
        window.parseNum = window.parseNum || function(v) {
            if (typeof v === 'number') return isNaN(v) ? 0 : v;
            if (!v) return 0;
            const str = String(v).replace(/[^0-9.-]/g, '');
            const n = parseFloat(str);
            return isNaN(n) ? 0 : n;
        };
        window.fmtNum = window.fmtNum || function(n, colId = null) {
            if (typeof n === 'string') n = window.parseNum(n);
            if (isNaN(n) || n === null || n === undefined) n = 0;
            const cfg = window.CurrentGridConfig || {};
            let decimals = (cfg.decimalPlaces !== undefined && cfg.decimalPlaces !== '' && cfg.decimalPlaces !== null) ? parseInt(cfg.decimalPlaces) : 2;
            let separator = (cfg.thousandSep !== undefined && cfg.thousandSep !== '' && cfg.thousandSep !== null && cfg.thousandSep !== 'default') ? cfg.thousandSep : ',';
            if (colId && Array.isArray(cfg.columns)) {
                const colCfg = cfg.columns.find(c => String(c.id) === String(colId));
                if (colCfg) {
                    if (colCfg.decimals !== undefined && colCfg.decimals !== '' && colCfg.decimals !== null) decimals = parseInt(colCfg.decimals);
                    if (colCfg.thousandSep !== undefined && colCfg.thousandSep !== '' && colCfg.thousandSep !== null && colCfg.thousandSep !== 'default') separator = colCfg.thousandSep;
                }
            }
            if (separator === 'default') separator = ',';
            let parts = Number(n).toFixed(decimals).split('.');
            if (separator !== 'none') {
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, separator);
            }
            return parts.join('.');
        };
    </script>
    <style id="dynamic-pos-grid-style">
    <?php
    if ($db_sales_grid_config && isset($db_sales_grid_config['columns'])) {
        $cfg = $db_sales_grid_config;
        $fFamily = $cfg['fontFamily'] ?? "'Cairo', sans-serif";
        $fSize = ($cfg['fontSize'] ?? 16) . 'px';
        $fWeight = $cfg['fontWeight'] ?? 'normal';
        $fColorRaw = $cfg['fontColor'] ?? '#ffffff';
        $fColor = (strtolower(trim($fColorRaw)) === '#ffffff' || strtolower(trim($fColorRaw)) === '#fff') ? 'var(--text-primary, #ffffff)' : $fColorRaw;
        $tAlign = $cfg['align'] ?? 'center';
        
        echo "#pos-grid, #pos-grid *, #pos-grid thead th, #pos-grid tbody td, #pos-grid tbody td input, #pos-grid tbody td select, #pos-grid tbody td span { font-family: " . $fFamily . " !important; }\n";
        echo "#pos-grid thead th { font-size: " . $fSize . " !important; text-align: " . $tAlign . " !important; }\n";
        echo "#pos-grid tbody td { font-size: " . $fSize . " !important; font-weight: " . $fWeight . " !important; color: " . $fColor . " !important; text-align: " . $tAlign . " !important; }\n";
        echo "#pos-grid tbody td input, #pos-grid tbody td select, #pos-grid tbody td span:not(.btn-icon), #pos-grid tbody td div:not(.custom-dropdown-list), #pos-grid tbody td .g-input, #pos-grid tbody td .g-select, #pos-grid tbody td .g-readonly, #pos-grid tbody td .total-val { font-size: " . $fSize . " !important; font-weight: " . $fWeight . " !important; color: " . $fColor . " !important; text-align: " . $tAlign . " !important; }\n";
        
        foreach ($cfg['columns'] as $col) {
            $colId = $col['id'];
            $colVisible = !isset($col['visible']) || $col['visible'];
            $colColorRaw = $col['color'] ?? $fColorRaw;
            $colColor = (strtolower(trim($colColorRaw)) === '#ffffff' || strtolower(trim($colColorRaw)) === '#fff') ? 'var(--text-primary, #ffffff)' : $colColorRaw;
            $colSize = isset($col['fontSize']) ? ($col['fontSize'] . 'px') : $fSize;
            $colWeight = $col['fontWeight'] ?? $fWeight;
            $colAlign = $col['align'] ?? $tAlign;
            $colWidth = (isset($col['width']) && $col['width'] !== 'auto') ? $col['width'] : (($colId === 'item' || $colId === 'col_item') ? 'auto' : '100px');
            $flexJustify = ($colAlign === 'center') ? 'center' : (($colAlign === 'right') ? 'flex-start' : 'flex-end');
            
            if (!$colVisible) {
                echo "#pos-grid [data-col-id='" . $colId . "'] { display: none !important; }\n";
            } else {
                if ($colId === 'item' || $colId === 'col_item') { $colAlign = 'center'; $flexJustify = 'center'; }
                $wStyle = ($colWidth !== 'auto') ? ("width: " . $colWidth . "; min-width: 0 !important; max-width: 100% !important;") : "width: auto; min-width: 130px;";
                echo "#pos-grid [data-col-id='" . $colId . "'] { display: table-cell !important; " . $wStyle . " text-align: " . $colAlign . " !important; color: " . $colColor . " !important; font-size: " . $colSize . " !important; font-weight: " . $colWeight . " !important; }\n";
                echo "#pos-grid th[data-col-id='" . $colId . "'] { color: #38bdf8 !important; font-weight: bold !important; text-align: " . $colAlign . " !important; font-size: " . $colSize . " !important; " . $wStyle . " }\n";
                echo "#pos-grid td[data-col-id='" . $colId . "'] { text-align: " . $colAlign . " !important; color: " . $colColor . " !important; font-size: " . $colSize . " !important; font-weight: " . $colWeight . " !important; " . $wStyle . " }\n";
                echo "#pos-grid td[data-col-id='" . $colId . "'] input, #pos-grid td[data-col-id='" . $colId . "'] select, #pos-grid td[data-col-id='" . $colId . "'] span:not(.btn-icon), #pos-grid td[data-col-id='" . $colId . "'] div:not(.custom-dropdown-list), #pos-grid td[data-col-id='" . $colId . "'] .g-input, #pos-grid td[data-col-id='" . $colId . "'] .g-select, #pos-grid td[data-col-id='" . $colId . "'] .g-readonly, #pos-grid td[data-col-id='" . $colId . "'] .total-val { text-align: " . $colAlign . " !important; color: " . $colColor . " !important; font-size: " . $colSize . " !important; font-weight: " . $colWeight . " !important; }\n";
                echo "#pos-grid td[data-col-id='" . $colId . "'] > div { display: flex; justify-content: " . $flexJustify . " !important; align-items: center; }\n";
            }
        }
    }
    ?>
    </style>
<!-- ═══ ULTRA-CONTRAST TYPOGRAPHY, FAINT HINTS/ZEROS & CLEAR REGIONS ENGINE (2027) ═══ -->
<link rel="stylesheet" id="invoice-ultra-contrast-2027" href="css/screens/direct-sales-s3.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-s3.css') ?: '2027' ?>">
    <script id="invoice-faint-zero-hint-engine" src="js/screens/direct-sales-inline-20.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-20.js') ?: '2027' ?>"></script>
</head>
<body class="has-sidebar">
    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- ═══ Add Customer Modal ═══ -->
    <div class="modal-overlay" id="add-customer-modal">
        <div class="modal-card" style="max-width: 600px;">
            <div class="modal-header">
                <h3>➕ إضافة عميل جديد</h3>
                <button class="modal-close" onclick="closeModal('add-customer-modal')">✕</button>
            </div>
            <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="label">اسم العميل</label>
                    <input type="text" class="ctrl-input" id="new-cust-name" placeholder="اسم العميل...">
                </div>
                <div class="form-group">
                    <label class="label">رقم الهاتف</label>
                    <input type="tel" class="ctrl-input" id="new-cust-phone" placeholder="09XXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="label">الواتساب</label>
                    <input type="tel" class="ctrl-input" id="new-cust-whatsapp" placeholder="رقم الواتساب">
                </div>
                <div class="form-group">
                    <label class="label">الإيميل</label>
                    <input type="email" class="ctrl-input" id="new-cust-email" placeholder="example@email.com">
                </div>
                <div class="form-group">
                    <label class="label">درجة السرية</label>
                    <select class="ctrl-input" id="new-cust-security">
                        <option value="1">1 (الأقل سرية)</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5 (الأعلى سرية)</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="label">العنوان</label>
                    <input type="text" class="ctrl-input" id="new-cust-address" placeholder="عنوان العميل...">
                </div>
                <div class="form-group">
                    <label class="label">الرصيد الافتتاحي</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" id="new-cust-opening" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
                <div class="form-group">
                    <label class="label">حد الائتمان (المسموح)</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" id="new-cust-credit" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('add-customer-modal')">إلغاء</button>
                <button class="btn btn-primary" onclick="addNewCustomer()">💾 حفظ العميل</button>
            </div>
        </div>
    </div>

    <div id="sales-proportional-root" class="pos-wrapper dash-main">

        <!-- ═══ BREADCRUMB + TOP BAR ═══ -->
                <!-- Unified Header -->
        
    <!-- Ambient Animated Background -->
    <div class="ambient-bg"></div>

    <!-- Unified Premium Header -->
        <?php
        $header_theme_class = 'header-theme-sales';
        $initial_action_param = strtolower(trim((string)($_GET['action'] ?? '')));
        $is_initial_split_view = ($initial_action_param === 'split');
        $is_initial_editor_view = in_array($initial_action_param, ['new', 'direct', 'pending_delivery', 'preliminary', 'proforma'], true) || isset($_GET['id']);
        if ($is_initial_editor_view) {
            if ($initial_action_param === 'preliminary' || $initial_action_param === 'proforma') {
                $header_title = 'فاتورة مبدئية';
                $header_icon = '📋';
            } else {
                $header_title = 'فاتورة مبيعات';
                $header_icon = '🛒';
            }
        }
        ?>
        <?php include 'includes/header.php'; ?>


        
    <!-- ═══ SALES HUB VIEW (UNIFIED SALES, DELIVERY & SPLIT COMMAND CENTER) ═══ -->
    <div id="sales-hub-view" class="animate-fade" style="display: <?php echo ($is_initial_split_view || $is_initial_editor_view) ? 'none' : 'flex'; ?>; flex-direction: column; height: 100%; gap: 16px; padding: 0;">
        <!-- Action Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(195px, 1fr)); gap: 14px;">
            <button onclick="openInvoiceEditor('pending_delivery')" class="hub-action-card card-blue animate-scale stagger-1">
                <div class="text-content">
                    <div class="title">فاتورة مبيعات</div>
                </div>
            </button>
            <button onclick="window.location.href='sales-delivery.php'" class="hub-action-card card-green animate-scale stagger-2">
                <div class="text-content">
                    <div class="title">بوابة تسليم المبيعات</div>
                </div>
            </button>
            <button onclick="if(typeof window.openDeliveryHub === 'function') window.openDeliveryHub();" class="hub-action-card card-teal animate-scale stagger-2" style="background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.35)); border: 1px solid rgba(52,211,153,0.5);">
                <div class="text-content">
                    <div class="title">فاتورة تقسيم وتسليم المبيعات</div>
                </div>
            </button>
            <button onclick="window.location.href='sales-invoice.php'" class="hub-action-card card-orange animate-scale stagger-3">
                <div class="text-content">
                    <div class="title">مرتجع مبيعات</div>
                </div>
            </button>
            <button onclick="openInvoiceEditor('preliminary')" class="hub-action-card card-purple animate-scale stagger-4">
                <div class="text-content">
                    <div class="title">فاتورة مبدئية</div>
                </div>
            </button>
            <button onclick="window.location.href='reserved-sales.php'" class="hub-action-card card-purple animate-scale stagger-4">
                <div class="text-content">
                    <div class="title">حجوزات المبيعات</div>
                </div>
            </button>
            <button onclick="if(window.openSalesAIAuditModal) window.openSalesAIAuditModal();" class="hub-action-card animate-scale stagger-4" style="background: linear-gradient(135deg, rgba(99,102,241,0.22), rgba(168,85,247,0.38)); border: 1px solid rgba(168,85,247,0.55); cursor: pointer;">
                <div class="text-content">
                    <div class="title" style="color: #e9d5ff; font-weight: 800;">رادار ربحية المبيعات والائتمان AI</div>
                </div>
            </button>
        </div>

        <!-- Unified Sales, Delivery & Split Table -->
        <div class="neo-card" id="unified-sales-table-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.05rem; display: flex; flex-direction: column; gap: 10px; align-items: stretch;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span style="color: #38bdf8 !important; font-weight: 900; font-size: 1.12rem;">✂️🚚 فاتورة تقسيم وتسليم المبيعات</span>
                        <span style="background:rgba(16,185,129,0.14); border:1px solid rgba(52,211,153,0.4); color:#6ee7b7; padding:3px 10px; border-radius:99px; font-size:0.76rem; font-weight:800;">✨ جدول عالمي موحد: تسليم وتقسيم وطباعة الجزئين بالوحدة الأساسية والفرعية</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <button type="button" onclick="if(typeof openDeliveryQrScannerModal==='function') openDeliveryQrScannerModal();" class="btn-action" style="width:auto; padding:6px 12px; font-size:0.8rem; font-weight:900; background:linear-gradient(135deg,#d97706,#b45309); color:#fff; border:none; border-radius:9px; cursor:pointer;">📷 مسح باركود (QR)</button>
                        <input type="text" id="hub-search" class="v-input" placeholder="🔍 ابحث برقم الفاتورة، العميل، الصنف، المستلم..." style="width: 270px; margin:0;" oninput="filterHubTransactions()">
                        <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.06); border-radius:9px;" onclick="loadHubTransactions()">🔄 تحديث</button>
                    </div>
                </div>

                <!-- Unified Quick Filter Tabs Bar -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; background: rgba(2,6,23,0.65); border: 1px solid rgba(148,163,184,0.2); padding: 8px 12px; border-radius: 12px;">
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;" id="unified-hub-filter-pills">
                        <button type="button" id="uhub-tab-all" onclick="setUnifiedHubFilter('all')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1.5px solid #38bdf8; background:rgba(56,189,248,0.22); color:#fff;">
                            📋 كل المبيعات (<span id="uhub-cnt-all">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-pending" onclick="setUnifiedHubFilter('pending')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            🚚 قيد التسليم والتقسيم (<span id="uhub-cnt-pending">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-split_only" onclick="setUnifiedHubFilter('split_only')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            ✂️ الأذونات المقسمة / طباعة الجزئين (<span id="uhub-cnt-split">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-delivered" onclick="setUnifiedHubFilter('delivered')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            ✅ المسلمة للمخزن (<span id="uhub-cnt-delivered">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-preliminary" onclick="setUnifiedHubFilter('preliminary')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            💜 عروض مبدئية (<span id="uhub-cnt-prelim">0</span>)
                        </button>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; font-size:0.78rem; color:#94a3b8; font-weight:800;">
                        <span>💡 اضغط <b>📦 تسليم</b> للصرف المخزني أو <b>✂️ تقسيم</b> لفك الفاتورة لجزئين وطباعتهما</span>
                    </div>
                </div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid hub-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right; min-width:150px;">📄 رقم الفاتورة والحالة</th>
                            <th style="text-align:center; width:135px;">📅 التاريخ والوقت</th>
                            <th style="text-align:right; min-width:155px;">👤 العميل والدفع</th>
                            <th style="text-align:right; min-width:280px;">📦 الأصناف والوحدات (أساسي ⇄ فرعي)</th>
                            <th style="text-align:center; width:150px;">🏢 المخزن والكمية</th>
                            <th style="text-align:center; width:125px;">💰 الإجمالي</th>
                            <th style="text-align:center; min-width:250px;">⚡ الإجراءات المباشرة (تسليم / تقسيم / طباعة)</th>
                        </tr>
                    </thead>
                    <tbody id="hub-transactions-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <div class="empty-state" id="hub-empty-state" style="display: none;">
                    <div class="icon">📭</div>
                    <h3>لا توجد عمليات مطابقة</h3>
                    <p>اضغط على "فاتورة مبيعات جديدة" للبدء أو غيّر فلتر العرض.</p>
                </div>
            </div>
            <!-- Pagination Bar -->
            <div id="hub-pagination-bar" style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding: 5px 0;"></div>
        </div>
    </div>

    <!-- ═══ SPLIT INVOICE VIEW ═══ -->
    <div id="split-invoice-view" style="display: <?php echo $is_initial_split_view ? 'flex' : 'none'; ?>; flex-direction: column; height: 100%; gap: 12px; padding: 0; margin: 0; width: 100%; max-width: 100%; box-sizing: border-box; position: relative; z-index: 9999; background: var(--bg-main);">
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px; gap: 10px; width: 100%; max-width: 100%; box-sizing: border-box;">

            <!-- 👑 شريط القيادة الذكي للدستورين (1/2 و 2/2) وبوابة الحسم الختامية (10x + تنصيف ليزري بعد الشريط + زيرو خطأ) لبوابة تقسيم الفواتير (?action=split) -->
            <div id="split-fc1-ai-cockpit-bar" style="background: #081220; border: 1.5px solid rgba(56, 189, 248, 0.42); border-radius: 12px; padding: 8px 12px; display: flex; flex-direction: column; gap: 7px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 6px; flex: 1; min-width: 260px; background: #000000; border: 1.5px solid rgba(56, 189, 248, 0.55); border-radius: 9px; padding: 3px 8px;">
                        <span style="font-size: 0.95rem;" title="الإدخال الذكي لتقسيم الفواتير باللغة الطبيعية صوتاً وكتابةً (0ms AI Split Copilot)">✂️🤖</span>
                        <input type="text" id="split-fc1-nlp-input" list="split-fc1-smart-datalist" onkeydown="if(event.key==='Enter'){event.preventDefault();window.executeSplitFc1NaturalLanguageCommand && window.executeSplitFc1NaturalLanguageCommand();}" placeholder="أمر تقسيم ذكي (صوتاً أو كتابةً): مثلاً «تقسيم بالتساوي 50%» أو «بمبلغ مستهدف 500000» أو «بسعة شاحنة 15» أو اسم العميل..." style="flex: 1; background: #000000 !important; color: #FFFFFF !important; border: none !important; font-size: 0.8rem; font-weight: 800; outline: none; height: 28px !important; padding: 2px 6px;" data-guide-title="الأمر الذكي لتقسيم الفاتورة باللغة الطبيعية صوتاً وكتابةً (0ms)" data-guide-desc="اكتب أو انطق أمر التقسيم مثل (تقسيم بالتساوي 50%) أو (توزيع بمبلغ مستهدف 500000) أو (توزيع بسعة الشاحنة 15) أو اسم العميل ليقوم الذكاء الاصطناعي بالتنفيذ الفوري." data-guide-rule="الأمر الذكي يقسم الفاتورة ويحسب الضرائب والخصومات النسبية للأقسام في 0ms.">
                        <button type="button" id="btn-split-fc1-voice" onclick="window.startSplitFc1VoiceCommand && window.startSplitFc1VoiceCommand()" style="height: 26px; padding: 0 9px; border-radius: 7px; background: rgba(239,68,68,0.22); color: #fca5a5; border: 1px solid rgba(239,68,68,0.5); font-size: 0.74rem; font-weight: 900; cursor: pointer;">🎤 صوتي</button>
                        <button type="button" onclick="window.executeSplitFc1NaturalLanguageCommand && window.executeSplitFc1NaturalLanguageCommand()" style="height: 26px; padding: 0 11px; border-radius: 7px; background: linear-gradient(135deg,#0284c7,#2563eb); color: #fff; border: 1px solid #38bdf8; font-size: 0.74rem; font-weight: 900; cursor: pointer;">⚡ تنفيذ فوري</button>
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                        <button type="button" onclick="if(typeof window.distributeSplitEqually==='function') window.distributeSplitEqually(); else if(window.executeSplitFc1NaturalLanguageCommand) window.executeSplitFc1NaturalLanguageCommand('50%');" style="height: 29px; padding: 0 9px; border-radius: 8px; background: rgba(16,185,129,0.18); color: #6ee7b7; border: 1px solid #10b981; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="توزيع الكميات بالتساوي 50% / 50% بين الأقسام (F9)" data-guide-title="التوزيع الذكي بالتساوي 50% / 50% (F9)" data-guide-desc="يقسم كميات كافة الأصناف مناصفة بين الأقسام مع موازنة الكسور والضرائب والخصومات آلياً." data-guide-rule="موازنة الكسور الذرية تمنع ضياع أي كسر عشري بين أذونات الصرف.">⚖️ مناصفة 50% (F9)</button>
                        <button type="button" onclick="window.splitByTargetMonetaryAmountPrompt && window.splitByTargetMonetaryAmountPrompt()" style="height: 29px; padding: 0 9px; border-radius: 8px; background: rgba(245,158,11,0.18); color: #fde68a; border: 1px solid #f59e0b; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="التوزيع الذكي حسب مبلغ مالي مستهدف للقسم الأول" data-guide-title="التوزيع الذكي بالمبلغ المالي المستهدف (AI Target Amount Split)" data-guide-desc="أدخل المبلغ المالي المطلوب للقسم الأول ليقوم الذكاء الاصطناعي بحساب وتوزيع الكميات المعادلة له فوراً." data-guide-rule="التوزيع بالمبلغ المستهدف يوائم بين سقف الدفعة النقدية والكمية المسلمة.">💰 بمبلغ مستهدف</button>
                        <button type="button" onclick="window.splitByTruckCapacityPrompt && window.splitByTruckCapacityPrompt()" style="height: 29px; padding: 0 9px; border-radius: 8px; background: rgba(56,189,248,0.18); color: #7dd3fc; border: 1px solid #38bdf8; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="التوزيع الذكي حسب سعة الشاحنة الأولى" data-guide-title="التوزيع الذكي حسب سعة وحمولة الشاحنة (AI Truck Capacity Split)" data-guide-desc="يملا القسم الأول حتى السعة القصوى للشاحنة الأولى ويوجه الفائض للشاحنة الثانية تلقائياً." data-guide-rule="ضبط الحمولة المسبقة يمنع غرامات الموازين وتأخير التسليم.">🚛 بسعة الشاحنة</button>
                        <button type="button" onclick="window.splitByAiWarehousePriority && window.splitByAiWarehousePriority()" style="height: 29px; padding: 0 9px; border-radius: 8px; background: rgba(20,184,166,0.18); color: #5eead4; border: 1px solid #14b8a6; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="التوزيع الذكي للأصناف حسب وفرة المخازن وتوازن الوزن والربحية (0ms AI)" data-guide-title="موزع المخازن وتوازن الحمولة بالذكاء الاصطناعي (AI Warehouse & Weight Optimizer)" data-guide-desc="يحلل أصناف الفاتورة ويوزعها ذكياً بين الأقسام والمخازن لتحقيق أفضل توازن وزني وأسرع صرف مخزني." data-guide-rule="توزيع الأصناف حسب وفرة المخزن يقلل زمن التحميل بنسبة 60%.">🏭 موازنة المخازن AI</button>
                        <button type="button" onclick="window.toggleSalesFc1SplitScreenStudio && window.toggleSalesFc1SplitScreenStudio()" style="height: 29px; padding: 0 9px; border-radius: 8px; background: rgba(6,182,212,0.18); color: #67e8f9; border: 1px solid #06b6d4; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="نظام النصفين (يمين وشمال) لفتح شاشتين حيتين أو منطقة جوال حرة 100%" data-guide-title="نظام النصفين (Split-Screen Studio)" data-guide-desc="فتح شاشة ثانية بجوار شاشة التقسيم مع تسميع حي فوري أو فتح منطقة جوال حرة (بنكك، واتساب، استوديو صور، يوتيوب).">🌗 النصفين</button>
                        <button type="button" onclick="document.body.classList.toggle('sales-fc2-masked');window.notifySalesFc2UnifiedToast && window.notifySalesFc2UnifiedToast('👁️ تم تبديل عين الخصوصية (****) لإخفاء/إظهار المبالغ!','success');" style="height: 29px; padding: 0 9px; border-radius: 8px; background: rgba(139,92,246,0.18); color: #ddd6fe; border: 1px solid #8b5cf6; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="عين الخصوصية (****) لإخفاء الأرصدة والإجماليات الحساسة">👁️ ****</button>
                        <button type="button" onclick="window.copySplitSummaryToClipboard && window.copySplitSummaryToClipboard()" style="height: 29px; padding: 0 9px; border-radius: 8px; background: rgba(168,85,247,0.18); color: #e9d5ff; border: 1px solid #a855f7; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="نسخ ملخص التقسيم المنسق للحافظة">📋 نسخ الملخص</button>
                        <button type="button" onclick="window.openSalesFc1SupremeConstitutionModal && window.openSalesFc1SupremeConstitutionModal()" style="height: 29px; padding: 0 10px; border-radius: 8px; background: linear-gradient(135deg,#10b981,#047857); color: #fff; border: 1px solid #6ee7b7; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="بوابة الدستور الختامي الأول (1 من 2 — 10 نقاط جوهرية 100%)">👑 الدستور (1/2)</button>
                        <button type="button" onclick="window.openSalesFc2SupremeConstitution2Modal && window.openSalesFc2SupremeConstitution2Modal()" style="height: 29px; padding: 0 10px; border-radius: 8px; background: linear-gradient(135deg,#f59e0b,#b45309); color: #fff; border: 1px solid #fde047; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="بوابة الدستور الختامي الثاني (2 من 2 — الذكاء المحاسبي والتحليلي والأمني 10/10)">🛡️ الدستور (2/2)</button>
                        <button type="button" onclick="window.openSalesFc2MasterAudit10AdditionsModal && window.openSalesFc2MasterAudit10AdditionsModal()" style="height: 29px; padding: 0 10px; border-radius: 8px; background: linear-gradient(135deg,#0284c7,#1d4ed8); color: #fff; border: 1px solid #7dd3fc; font-size: 0.74rem; font-weight: 900; cursor: pointer;" title="بوابة الحسم والفحص الختامي الشامل (تنصيف الشاشة بالليزر بعد الشريط + تكبير 10x بالرولة + 10 إضافات AI + زيرو خطأ 100%)">🏆 بوابة الحسم (10x + زيرو خطأ)</button>
                    </div>
                </div>
                <!-- 🛡️ شريط الدستور الختامي الثاني والثالث (2 و 3 من 3): السباعية العالمية للمخططات، التسميع الذري، مفسر النتائج، الشيكات وبنكك، رادار المالك، وأقفال الأمان لبوابة التقسيم -->
                <div id="split-fc2-sovereign-row" style="display: flex; align-items: center; justify-content: space-between; gap: 5px; flex-wrap: wrap; border-top: 1px dashed rgba(56,189,248,0.28); padding-top: 6px;">
                    <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                        <button type="button" onclick="window.openSalesFc2AtomicSyncAndDrillDownModal && window.openSalesFc2AtomicSyncAndDrillDownModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(16,185,129,0.18); color: #34d399; border: 1px solid rgba(16,185,129,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="التسميع الذري اللحظي والارتداد العكسي Drill-Down">🔗 التسميع الذري و Drill-Down</button>
                        <button type="button" onclick="window.openSalesFc2AiExecutiveInsightsModal && window.openSalesFc2AiExecutiveInsightsModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(56,189,248,0.18); color: #38bdf8; border: 1px solid rgba(56,189,248,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 2: مفسر النتائج والتقارير بالذكاء الاصطناعي والتنبؤ وسؤال التقرير">📊 مفسر النتائج وسؤال التقرير AI</button>
                        <button type="button" onclick="window.openSalesFc2DocumentaryAndLandedCostModal && window.openSalesFc2DocumentaryAndLandedCostModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(168,85,247,0.18); color: #d8b4fe; border: 1px solid rgba(168,85,247,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 4: الفرز الجزئي (سليم/تالف/مرفوض) + التكلفة المضافة Landed Cost + تحويل الدورة المستندية">📦 فرز + Landed Cost + دورة مستندية</button>
                        <button type="button" onclick="window.openSalesFc2ChequesAndBankakRadarModal && window.openSalesFc2ChequesAndBankakRadarModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(245,158,11,0.18); color: #fde047; border: 1px solid rgba(245,158,11,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 5: الشيكات (5 مراحل) + كاشف إشعارات بنكك وفوري 40KB">🏦 الشيكات وكاشف بنكك AI</button>
                        <button type="button" onclick="window.openSalesFc2ProactiveAlertsCenterModal && window.openSalesFc2ProactiveAlertsCenterModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(236,72,153,0.18); color: #f9a8d4; border: 1px solid rgba(236,72,153,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 6: التنبيهات الاستباقية وحارس الأصفار الزائدة والاهتزاز البصري Shake">🔔 التنبيهات وحارس الأصفار</button>
                        <button type="button" onclick="window.openSalesFc2ForensicOwnerRadarModal && window.openSalesFc2ForensicOwnerRadarModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid rgba(239,68,68,0.55); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 7: رادار المالك الجنائي (أصفر/برتقالي/أحمر) والصندوق الأسود BlackBox">🕵️‍♂️ رادار المالك (🟡🟠🔴)</button>
                        <button type="button" onclick="window.openSalesFc2AntiEmbezzlementLocksModal && window.openSalesFc2AntiEmbezzlementLocksModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(14,165,233,0.18); color: #7dd3fc; border: 1px solid rgba(14,165,233,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 8: قفل الطبعة الأصلية (1) وختم بدل فاقد + الجرد الأعمى + قفل الفترات">🔒 الطبعة #1 والجرد الأعمى</button>
                        <button type="button" onclick="window.openSalesFc2CyberBiometricAndPrintStudioModal && window.openSalesFc2CyberBiometricAndPrintStudioModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(20,184,166,0.18); color: #5eead4; border: 1px solid rgba(20,184,166,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 9: البصمة البيومترية + القفل الزمني + طباعة 80mm / A4 / Excel وكرت واتساب">🧬 البصمة و 80mm وواتساب</button>
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                        <span id="split-laser-symmetry-badge" onclick="window.verifySalesLaserCenterSymmetry && window.verifySalesLaserCenterSymmetry();window.openSalesFc2MasterAudit10AdditionsModal && window.openSalesFc2MasterAudit10AdditionsModal();" style="height: 25px; padding: 0 9px; border-radius: 999px; background: rgba(16,185,129,0.16); color: #34d399; border: 1px solid rgba(16,185,129,0.5); font-size: 0.7rem; font-weight: 900; display: inline-flex; align-items: center; cursor: pointer;" title="المسطرة الليزرية لتنصيف الشاشة بعد الشريط الجانبي الأيمن (الهامش الأيمن === الهامش الأيسر بالمليمتر)">🎯 تنصيف ليزري (بعد الشريط): يمين (12px) = يسار (12px)</span>
                        <span id="split-sovereign-10x-badge" onclick="window.setSalesFc1ProportionalZoom && window.setSalesFc1ProportionalZoom(1.0)" style="height: 25px; padding: 0 9px; border-radius: 999px; background: rgba(56,189,248,0.16); color: #38bdf8; border: 1px solid rgba(56,189,248,0.5); font-size: 0.7rem; font-weight: 900; display: inline-flex; align-items: center; cursor: pointer;" title="محرك التكبير التلسكوبي الموحد بالرولة (Ctrl + Wheel من 0.25x حتى 10x) — اضغط لإعادة الضبط 100% على قد اللابتوب">🔭 مقاس موحد: 100% (1x)</span>
                        <span id="split-fc2-crdt-pill" onclick="window.triggerSalesFc2FlashSyncCRDT && window.triggerSalesFc2FlashSyncCRDT()" style="height: 25px; padding: 0 9px; border-radius: 999px; background: rgba(16,185,129,0.16); color: #34d399; border: 1px solid rgba(16,185,129,0.45); font-size: 0.7rem; font-weight: 900; display: inline-flex; align-items: center; cursor: pointer;" title="النقطة 3: التزامن الصاروخي CRDT وحماية مسودة التقسيم ضد انقطاع الكهرباء أو الشبكة">⚡ CRDT 0ms: محمي 100%</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; font-size: 0.74rem; border-top: 1px dashed rgba(148,163,184,0.25); padding-top: 5px;">
                    <span id="split-fc1-live-tafqeet-pill" style="color: #fde047; font-weight: 900;">🗣️ التفقيط اللحظي للأقسام: اختر فاتورة لتقسيمها أو أدخل الكميات ليظهر التفقيط الفوري لقسم (أ) وقسم (ب)</span>
                    <span id="split-fc2-live-ai-exec-summary" style="color: #38bdf8; font-weight: 800;">🧠 الملخص التنفيذي للتقسيم (AI Result Reader): جاهز لقراءة وتحليل الفواتير المقسمة وتوازن الأقسام لحظياً (0ms)</span>
                    <div style="display: inline-flex; align-items: center; gap: 5px;">
                        <input type="text" id="split-fc2-ask-report-inp" placeholder="اسأل التقرير بالذكاء الاصطناعي (مثلاً: ربح، مصروف، مخزون، تحصيل)..." onkeydown="if(event.key==='Enter'){event.preventDefault();window.askSplitFc2QuickReportQuestion && window.askSplitFc2QuickReportQuestion();}" style="width: 240px; height: 26px !important; padding: 0 8px; border-radius: 7px; font-size: 0.74rem; font-weight: 800;">
                        <button type="button" onclick="window.askSplitFc2QuickReportQuestion && window.askSplitFc2QuickReportQuestion()" style="height: 26px; padding: 0 9px; border-radius: 7px; background: linear-gradient(135deg,#7c3aed,#5b21b6); color: #fff; border: 1px solid #d8b4fe; font-size: 0.72rem; font-weight: 900; cursor: pointer;">🤖 اسأل التقرير</button>
                    </div>
                </div>
                <div id="split-fc3-inline-7charts-body" style="display: none; padding-top: 8px; border-top: 1px solid rgba(56,189,248,0.3);"></div>
                <datalist id="split-fc1-smart-datalist">
                    <option value="تقسيم بالتساوي 50% بين القسم أ والقسم ب"></option>
                    <option value="توزيع بمبلغ مستهدف 500000"></option>
                    <option value="توزيع بسعة شاحنة 15"></option>
                    <option value="إضافة قسم ثالث جديد"></option>
                    <option value="تعليق مؤقت للتقسيم الحالي"></option>
                </datalist>
            </div>
            
            <!-- Step 1: Select Invoice -->
            <div id="split-parked-vouchers-bar" style="display:none; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:8px; background:rgba(245,158,11,0.14); border:2px solid #f59e0b; padding:8px 14px; border-radius:12px; margin-bottom:4px;"></div>
            <div id="split-select-step" style="display: flex; flex-direction: column; flex: 1; gap: 12px;">
                <div id="split-search-toolbar" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; background: rgba(255,255,255,0.02); padding: 10px 14px; border-radius: 12px; border: 2px solid rgba(148,163,184,0.35);">
                    <div style="flex: 1; min-width: 240px; display: flex; align-items: center; gap: 8px;">
                        <label for="split-invoice-search" style="white-space: nowrap; font-size: 0.9rem; color: #94a3b8; font-weight: 900;">🔍 بحث (F2):</label>
                        <input type="text" id="split-invoice-search" class="v-input" placeholder="رقم الفاتورة، العميل، الصنف، أو المبلغ..." oninput="filterSplitInvoices()" style="height: 38px !important;">
                    </div>
                    <div id="split-eligible-kpi-strip" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;"></div>
                    <span id="split-c7-conn-badge" title="حالة الاتصال والمزامنة اللحظية" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 900; background: rgba(16,185,129,0.14); border: 2px solid #10b981; color: #6ee7b7; white-space: nowrap;"><span id="split-c7-conn-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981; display: inline-block;"></span><span id="split-c7-conn-text">متصل ومتزامن</span></span>
                    <button type="button" class="add-row-btn" onclick="if(window.saveCurrentSplitDraftState) window.saveCurrentSplitDraftState(); openModal('add-customer-modal');" title="إضافة عميل جديد فوراً دون فقدان البيانات (F4)" style="height: 38px; padding: 0 12px; border-radius: 9px; display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; font-weight: 900; cursor: pointer;">➕ عميل</button>
                    <button type="button" id="btn-split-global-report-top" class="btn-action kinetic-glow-arm" style="width: auto; height: 38px; padding: 0 12px; border-radius: 9px; font-weight: 900; background: rgba(56,189,248,0.18); border: 2px solid #38bdf8; color: #7dd3fc;" onclick="window.openSplitGlobalLedgerReportModal && window.openSplitGlobalLedgerReportModal()" title="التقرير العالمي وكشف حركة تقسيم الفواتير بالرصيد التراكمي والفلاتر اللحظية">📊 كشف التقسيم</button>
                    <button type="button" id="btn-split-export-excel-top" class="btn-action kinetic-glow-arm" style="width: auto; height: 38px; padding: 0 12px; border-radius: 9px; font-weight: 900; background: rgba(16,185,129,0.18); border: 2px solid #10b981; color: #6ee7b7;" onclick="window.exportSplitReportToExcel && window.exportSplitReportToExcel()" title="تصدير فوري نظيف إلى Excel">📗 Excel</button>
                    <button class="btn-action royal-crystal-btn royal-btn-new" style="width: auto; height: 38px; padding: 0 14px; border-radius: 9px; font-weight: 900;" onclick="loadSplitInvoices()">🔄 تحديث</button>
                    <button class="btn-action royal-crystal-btn royal-btn-back" style="width: auto; height: 38px; padding: 0 16px; border-radius: 9px; font-weight: 900;" onclick="window.salesConstitutionalBack ? window.salesConstitutionalBack() : showSalesHub()" title="رجوع متسلسل خطوة للخلف (Esc)">↩️ رجوع <kbd class="c3-kbd">Esc</kbd></button>
                    <button class="btn-action royal-crystal-btn royal-btn-delete" style="width: auto; height: 38px; padding: 0 14px; border-radius: 9px; font-weight: 900;" onclick="window.salesConstitutionalExit ? window.salesConstitutionalExit() : (window.location.href='magaiees_unified_dashboard.php')" title="خروج مباشر للشاشة الرئيسية مع حفظ المسودة">🚪 خروج</button>
                </div>
                
                <div class="grid-scroll-wrap" style="flex: 1; max-height: none !important; overflow-y: visible !important; overflow-x: auto; margin-top: 6px;">
                    <table class="pos-grid" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align:right;">رقم الفاتورة</th>
                                <th style="text-align:right;">التاريخ</th>
                                <th style="text-align:right;">العميل</th>
                                <th style="text-align:center;">طريقة الدفع</th>
                                <th style="text-align:left;">المبلغ الإجمالي</th>
                                <th style="text-align:center;">الحالة</th>
                                <th style="text-align:center;">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody id="split-invoices-body">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                    <div class="empty-state" id="split-empty-state" style="display: none;">
                        <div class="icon">📭</div>
                        <h3>لا توجد فواتير صالحة للتقسيم</h3>
                        <p>تأكد من وجود فواتير مبيعات نشطة وغير مسلمة بعد.</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Split Board -->
            <div id="split-board-step" style="display: none; flex-direction: column; gap: 6px;">
                <div id="split-header-info" style="display: flex; background: linear-gradient(135deg, rgba(236,72,153,0.15), rgba(99,102,241,0.05)); padding: 8px; border-radius: 12px; border: 1px solid rgba(236,72,153,0.2); width: 100%;">
                    <!-- Filled dynamically -->
                </div>
                <div id="split-fc2-board-sajana-strip" style="display: flex; align-items: center; justify-content: space-between; gap: 6px; flex-wrap: wrap; padding: 7px 11px; border-radius: 10px; background: rgba(15,23,42,0.82); border: 1.5px solid rgba(245,158,11,0.5);">
                    <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <span style="font-size: 0.78rem; font-weight: 900; color: #fde047;">⚖️ البسكول ومعيار الحديد للأقسام:</span>
                        <button type="button" onclick="window.applySplitAlSajanaQuickPreset && window.applySplitAlSajanaQuickPreset(135)" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(245,158,11,0.2); color: #fde047; border: 1px solid #f59e0b; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="تحويل وموازنة كميات التقسيم وفق الطن العددي = 135 سيخة">طن عددي = 135 سيخة</button>
                        <button type="button" onclick="window.applySplitAlSajanaQuickPreset && window.applySplitAlSajanaQuickPreset(140)" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(56,189,248,0.2); color: #38bdf8; border: 1px solid #38bdf8; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="تحويل وموازنة كميات التقسيم وفق الطن الوزني = 140 سيخة">طن وزني = 140 سيخة</button>
                        <button type="button" onclick="window.applySplitAlSajanaQuickPreset && window.applySplitAlSajanaQuickPreset(15)" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid #10b981; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="تحويل وموازنة كميات التقسيم وفق الشيلة = 15 سيخة">الشيلة = 15 سيخة</button>
                        <button type="button" onclick="window.openSalesFc2DocumentaryAndLandedCostModal && window.openSalesFc2DocumentaryAndLandedCostModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(168,85,247,0.2); color: #d8b4fe; border: 1px solid #a855f7; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="حساب فرق البسكول وتوزيع مصروفات العتالة والنقل (Landed Cost) والفرز الجزئي">⚖️ البسكول + Landed Cost + فرز</button>
                    </div>
                    <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <button type="button" onclick="window.convertSalesFc2DocumentaryCycle && window.convertSalesFc2DocumentaryCycle('INVOICE_TO_WAREHOUSE_ORDER')" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(14,165,233,0.2); color: #7dd3fc; border: 1px solid #0ea5e9; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="تحويل الفاتورة المقسمة إلى إذن صرف مخزني بلمسة زر">📦⬅ إذن مخزن</button>
                        <button type="button" onclick="window.convertSalesFc2DocumentaryCycle && window.convertSalesFc2DocumentaryCycle('ORDER_TO_TREASURY_VOUCHER')" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(16,185,129,0.22); color: #6ee7b7; border: 1px solid #10b981; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="توليد سند قبض خزينة آلي للأقسام بلمسة زر">💵⬅ سند قبض</button>
                        <button type="button" onclick="window.convertSalesFc2DocumentaryCycle && window.convertSalesFc2DocumentaryCycle('SALES_RETURN_REVERSE_ENTRY')" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid #ef4444; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="ضبط مرتجع مبيعات مع قيد عكسي آلي">↩️ مرتجع + قيد عكسي</button>
                        <button type="button" onclick="window.openSalesFc2ChequesAndBankakRadarModal && window.openSalesFc2ChequesAndBankakRadarModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(245,158,11,0.22); color: #fde047; border: 1px solid #f59e0b; font-size: 0.73rem; font-weight: 900; cursor: pointer;" title="فحص إشعار بنكك/فوري AI (<= 40KB) ودورة الشيكات الخماسية">🏦 بنكك/فوري AI (40KB)</button>
                    </div>
                </div>

                <div class="grid-scroll-wrap" style="max-height: none !important; overflow-y: visible !important; overflow-x: auto; background: rgba(0,0,0,0.15); border-radius: 12px; border: 2px solid rgba(148,163,184,0.35);">
                    <table class="pos-grid" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align:right;">اسم الصنف</th>
                                <th style="text-align:center; width: 110px;">الكمية الأصلية</th>
                                <th style="text-align:left; width: 130px;">السعر</th>
                                <th style="text-align:center; width: 230px; color: #38bdf8;">كمية الفاتورة (أ)</th>
                                <th style="text-align:center; width: 230px; color: #ec4899;">كمية الفاتورة (ب)</th>
                                <th style="text-align:left; width: 160px;">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody id="split-items-body">
                            <!-- Filled dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Summaries & Actions -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 8px;">
                    <!-- Invoice A Card -->
                    <div class="neo-card" style="border: 1px solid rgba(56, 189, 248, 0.6); background: rgba(56, 189, 248, 0.02); padding: 8px 12px; border-radius: 12px; position: relative; overflow: hidden; margin: 0;">
                        <div style="position: absolute; top:0; right:0; width:4px; height:100%; background:#38bdf8;"></div>
                        <h4 style="color: #38bdf8; margin: 0 0 8px 0; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                            <span>📄</span> الفاتورة الفرعية الأولى (أ)
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الإجمالي قبل الخصم:</span><span id="split-subtotal-a" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الخصم (النسبي):</span><span id="split-discount-a" style="font-family: monospace; font-weight: 800; font-size: 1.05rem; color: #f87171;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الضريبة (النسبية):</span><span id="split-tax-a" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <hr style="border-color: rgba(255,255,255,0.05); margin: 4px 0;">
                            <div style="display: flex; justify-content: space-between; font-weight: 900; color: #38bdf8; font-size: 1.25rem;"><span>الصافي النهائي:</span><span id="split-grand-a" style="font-family: monospace;">0.00</span></div>
                        </div>
                    </div>

                    <!-- Invoice B Card -->
                    <div class="neo-card" style="border: 1px solid rgba(236, 72, 153, 0.3); background: rgba(236, 72, 153, 0.02); padding: 8px 12px; border-radius: 12px; position: relative; overflow: hidden; margin: 0;">
                        <div style="position: absolute; top:0; right:0; width:4px; height:100%; background:#ec4899;"></div>
                        <h4 style="color: #ec4899; margin: 0 0 8px 0; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                            <span>📄</span> الفاتورة الفرعية الثانية (ب)
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الإجمالي قبل الخصم:</span><span id="split-subtotal-b" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الخصم (النسبي):</span><span id="split-discount-b" style="font-family: monospace; font-weight: 800; font-size: 1.05rem; color: #f87171;">0.00</span></div>
                            <div style="display: flex; justify-content: space-between; color: #cbd5e1; font-weight: bold; font-size: 0.95rem;"><span>الضريبة (النسبية):</span><span id="split-tax-b" style="font-family: monospace; font-weight: 800; font-size: 1.05rem;">0.00</span></div>
                            <hr style="border-color: rgba(255,255,255,0.05); margin: 4px 0;">
                            <div style="display: flex; justify-content: space-between; font-weight: 900; color: #ec4899; font-size: 1.25rem;"><span>الصافي النهائي:</span><span id="split-grand-b" style="font-family: monospace;">0.00</span></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(255,255,255,0.08);">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <button type="button" id="btn-split-cancel" class="btn-action royal-crystal-btn royal-btn-delete kinetic-glow-arm" style="width: auto; padding: 8px 18px; border-radius: 10px;" onclick="window.confirmAndCancelCurrentSplit ? window.confirmAndCancelCurrentSplit(this) : cancelCurrentSplit()">↩️ إلغاء <kbd class="c3-kbd">Esc</kbd></button>
                        <button type="button" id="btn-split-park-bottom" class="btn-action kinetic-glow-arm" style="width: auto; padding: 8px 16px; border-radius: 10px; font-size: 0.88rem; background: rgba(245,158,11,0.2); border: 2px solid #f59e0b; color: #fde68a; font-weight: 900;" onclick="window.parkCurrentSplitInvoice && window.parkCurrentSplitInvoice()" title="تعليق تقسيم هذه الفاتورة مؤقتاً لخدمة عميل مستعجل واسترجاعها بضغطة زر (Alt+P)">⏸️ تعليق مؤقت</button>
                        <button type="button" class="btn-action kinetic-glow-arm kinetic-arm-emerald" style="width: auto; padding: 8px 16px; border-radius: 10px; font-size: 0.88rem;" onclick="if(window.activeSplitInvoice) window.openDeliveryForInvoiceFromHub(window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id)">🚚 للتسليم</button>
                        <button type="button" class="add-row-btn" onclick="if(window.saveCurrentSplitDraftState) window.saveCurrentSplitDraftState(); openModal('add-customer-modal');" title="إضافة عميل جديد فوراً" style="height: 40px; padding: 0 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 5px; font-size: 0.86rem; font-weight: 900; cursor: pointer;">➕ عميل</button>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <button type="button" class="btn-action kinetic-glow-arm kinetic-arm-cyan" style="width: auto; padding: 8px 14px; border-radius: 10px; font-size: 0.88rem;" onclick="addSplitSection()">➕ إضافة قسم</button>
                        <button type="button" class="btn-action kinetic-glow-arm" style="width: auto; padding: 8px 12px; border-radius: 10px; font-size: 0.84rem; background:rgba(14,165,233,0.18); border:2px solid #0ea5e9; color:#7dd3fc; font-weight:900;" onclick="window.printSalesFc2Thermal80mm && window.printSalesFc2Thermal80mm()" title="طباعة حرارية فورية 80mm بباركود وحيد الاستعمال">🧾 80mm</button>
                        <button type="button" class="btn-action kinetic-glow-arm" style="width: auto; padding: 8px 12px; border-radius: 10px; font-size: 0.84rem; background:rgba(245,158,11,0.18); border:2px solid #f59e0b; color:#fde047; font-weight:900;" onclick="window.openSalesFc2AntiEmbezzlementLocksModal && window.openSalesFc2AntiEmbezzlementLocksModal()" title="الجرد الأعمى وقفل الطبعة رقم (1)">⚖️ الجرد الأعمى</button>
                        <button type="button" class="btn-action kinetic-glow-arm" style="width: auto; padding: 8px 14px; border-radius: 10px; font-size: 0.88rem; background:rgba(34,197,94,0.18); border:2px solid #22c55e; color:#4ade80; font-weight:900;" onclick="window.shareCurrentSplitViaWhatsApp && window.shareCurrentSplitViaWhatsApp()">📲 واتساب</button>
                        <button type="button" class="btn-action kinetic-glow-arm" style="width: auto; padding: 8px 14px; border-radius: 10px; font-size: 0.88rem; background:rgba(56,189,248,0.16); border:2px solid rgba(56,189,248,0.55); color:#38bdf8; font-weight:900;" onclick="if(typeof window.previewCurrentSplitVouchersBeforeSave==='function') window.previewCurrentSplitVouchersBeforeSave()">👁️ معاينة</button>
                        <button type="button" id="btn-split-approve" class="btn-action kinetic-glow-arm kinetic-arm-pink" style="width: auto; padding: 8px 20px; border-radius: 10px; font-size: 0.92rem;" onclick="submitInvoiceSplit(false)">✅ اعتماد التقسيم <kbd class="c3-kbd">F10</kbd></button>
                        <button type="button" id="btn-split-approve-print" class="btn-action royal-crystal-btn royal-btn-save kinetic-glow-arm kinetic-arm-emerald" style="width: auto; padding: 8px 22px; border-radius: 10px; font-size: 0.94rem;" onclick="submitInvoiceSplit(true)">🖨️ اعتماد وطباعة</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DELIVERY HUB VIEW ═══ -->
    <div id="delivery-hub-view" style="display: none; flex-direction: column; height: 100%; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 0 5px;">
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #10b981;">🚚 فواتير قيد التسليم (Pending Delivery)</span>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="delivery-search" class="v-input" placeholder="بحث..." style="width: 250px;" oninput="filterDeliveryTransactions()">
                    <button class="btn-action royal-crystal-btn royal-btn-new" style="width: auto; padding: 6px 14px; font-size: 0.85rem; border-radius: 10px;" onclick="loadDeliveryTransactions()">🔄 تحديث</button>
                    <button class="btn-action royal-crystal-btn royal-btn-back" style="width: auto; padding: 6px 16px; font-size: 0.85rem; border-radius: 10px;" onclick="showSalesHub()">↩️ رجوع <kbd class="c3-kbd">Esc</kbd></button>
                </div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الفاتورة</th>
                            <th style="text-align:right;">التاريخ</th>
                            <th style="text-align:right;">العميل</th>
                            <th style="text-align:center;">المستودع</th>
                            <th style="text-align:center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="delivery-transactions-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <div class="empty-state" id="delivery-empty-state" style="display: none;">
                    <div class="icon">📭</div>
                    <h3>لا توجد فواتير قيد التسليم</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ INVOICE EDITOR VIEW ═══ -->
    <div id="invoice-editor-view" style="display: <?php echo !empty($is_initial_editor_view) ? 'flex' : 'none'; ?>; flex-direction: column; height: 100%; flex: 1;">
    
        <!-- ═══ MAIN BODY ═══ -->
        <div class="ds-body" style="padding-top: 5px;">

            <!-- ══ TOP ROW: بيانات الفاتورة (مدمجة) ══ -->
            <div id="sales-top-container">

                
                <!-- Form Fields (Cloned 1:1 from Purchase Card) -->
                <div class="neo-card expanded-form purchase-glass-card" id="form-fields-card" style="margin: 0; width: 100%; box-sizing: border-box;">
                    <div style="display: flex; flex-direction: column; gap: 9px; position:relative; z-index:1;">
                        <!-- Saved Invoice Edit Advisory Banner -->
                        <div id="saved-invoice-edit-banner" style="display:none; margin-bottom: 4px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(180, 83, 9, 0.25)); border: 1.5px solid rgba(245, 158, 11, 0.6); border-radius: 12px; padding: 10px 16px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="font-size: 1.6rem;">⚠️</span>
                                    <div>
                                        <div style="color: #fbbf24; font-size: 1.1rem; font-weight: 900;">
                                            تنبيه: أنت الآن في وضع تعديل فاتورة محفوظة ومعتمدة مسبقاً (<span id="edit-banner-inv-no" style="color:#ffffff; font-family:'Outfit',sans-serif; text-decoration: underline;">-</span>)
                                        </div>
                                        <div style="color: #fde68a; font-size: 0.88rem; font-weight: 600; margin-top: 2px;">
                                            أصل الفاتورة تم اعتماده مسبقاً في النظام. حفظ أي تعديلات سيقوم بتحديث الحسابات وحركات المخزن المرتبطة بها تلقائياً.
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <button type="button" onclick="onSuccessNewInvoice()" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 6px 14px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.85rem; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                                        ➕ إلغاء التعديل وبدء فاتورة جديدة
                                    </button>
                                </div>
                            </div>
                        </div>

        <!-- ROW 0 WRAPPER (Unified Slim Level: Title Badge, Invoice No, Date, Financial Strip with SDG/USD Toggle) -->
    <link rel="stylesheet" id="sales-worldclass-ux-styles" href="css/screens/direct-sales-inline-12.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-12.css') ?: '2027' ?>">

    <!-- ROW 0: ONE SINGLE UNBROKEN LINE (الرقم مع السهمين + التاريخ + جنيه ⇄ + الرصيد + المديونية + الشيكات + الحد + المتاح) -->
    <div id="sales-row0-bar" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; width: 100%; margin: 0;">
        <div id="invoice-info-box" style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; padding: 3px 7px; border-radius: 10px;">
            <div class="h-form-group group-invoice" style="display:flex; align-items:center; gap:2px; margin:0;">
                <div class="pro-serial-container" style="display:inline-flex; align-items:center; gap:3px; width:max-content; padding:0 5px; overflow:hidden;">
                    <button class="pro-serial-btn" type="button" onclick="loadPreviousInvoice()" title="الفاتورة السابقة" style="background: rgba(56,189,248,0.22); border: 1.5px solid #38bdf8; border-radius: 4px; color: #38bdf8; font-size: 0.56rem; font-weight: 900; cursor: pointer; width: 14px; height: 18px; display:inline-flex !important; align-items:center; justify-content:center; flex-shrink:0; padding:0;">❯</button>
                    <span id="inv-number" data-auto-fit-voucher="1" class="pro-serial-number" style="min-width: 0; padding: 0 4px; text-align:center; font-size:15.5px; font-weight:900;">---</span>
                    <input type="hidden" id="hdr-inv-no">
                    <button class="pro-serial-btn" type="button" onclick="loadNextInvoice()" title="الفاتورة التالية" style="background: rgba(56,189,248,0.22); border: 1.5px solid #38bdf8; border-radius: 4px; color: #38bdf8; font-size: 0.56rem; font-weight: 900; cursor: pointer; width: 14px; height: 18px; display:inline-flex !important; align-items:center; justify-content:center; flex-shrink:0; padding:0;">❮</button>
                </div>
            </div>
            <div style="width: 1px; height: 20px; background: rgba(255,255,255,0.22); flex-shrink: 0;"></div>
            <div class="h-form-group group-date" style="display:flex; align-items:center; gap:0; margin:0;">
                <input type="date" class="v-input ctrl-input" id="hdr-date" title="تاريخ الفاتورة" style="color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; font-weight:800;">
            </div>
            <div id="sales-user-audit-container" style="display: none !important;"></div>
        </div>

        <div id="customer-financial-card" class="fin-strip-inline" style="flex: 1 1 0%; min-width: 0; width: auto; opacity: 1; overflow: visible; display: flex; align-items: center;">
            <div id="fin-strip" style="display:none;"></div>
            <div class="fin-strip-inner">
                <button type="button" id="fin-strip-currency-btn" style="display:none !important;">جنيه ⇄</button>
                <div id="fin-wrap-ledger" class="fin-metric-pill" style="background: rgba(15,23,42,0.82); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(148,163,184,0.65);">
                    <span class="fin-lbl" style="font-size:0.65rem; color:#e2e8f0; font-weight:900;">الرصيد</span>
                    <span id="fin-ledger" class="fin-val" style="font-weight:900; color:#ffffff; font-size:1.05rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:24px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-debt" class="fin-metric-pill" style="background: rgba(127,29,29,0.62); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(251,113,133,0.75);">
                    <span class="fin-lbl" style="font-size:0.65rem; color:#fecdd3; font-weight:900;">المديونية</span>
                    <span id="fin-total-debt" class="fin-val" style="font-weight:900; color:#ffffff; font-size:1.05rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:24px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-checks" class="fin-metric-pill" style="background: rgba(120,53,15,0.62); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(251,191,36,0.78);">
                    <span class="fin-lbl" style="font-size:0.65rem; color:#fef08a; font-weight:900;">الشيكات</span>
                    <span id="fin-checks" class="fin-val" style="font-weight:900; color:#ffffff; font-size:1.05rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:24px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-limit" class="fin-metric-pill" style="background: rgba(12,74,110,0.62); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(56,189,248,0.78);">
                    <span class="fin-lbl" style="font-size:0.65rem; color:#bae6fd; font-weight:900;">الحد</span>
                    <span id="fin-credit-limit" class="fin-val" style="font-weight:900; color:#ffffff; font-size:1.05rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:24px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-remaining" class="fin-metric-pill" style="background: rgba(6,78,59,0.72); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(52,211,153,0.85);">
                    <span class="fin-lbl" style="font-size:0.65rem; color:#a7f3d0; font-weight:900;">المتاح</span>
                    <span id="fin-remaining" class="fin-val" style="font-weight:900; color:#ffffff; font-size:1.05rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <span id="fin-status-badge" style="display:none;"><span class="status-dot neutral"></span><span id="fin-status-txt"></span></span>
            </div>
        </div>
    </div>

    <!-- ROW 1: العميل (+ أستاذ + إضافة بدون آخر فاتورة) + زر جنيه/دولار + سعر الصرف الموسّع + المخزن في مكانه الأصلي -->
    <div id="sales-row1-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; margin: 0; width: 100%; align-items: center;">
        <div class="h-form-group group-supplier" style="flex: 5.2 1 0%; min-width: 0; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="color: #fde047; margin:0;">العميل</label>
            <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                <div class="custom-dropdown" style="flex:1; min-width: 0;">
                    <div class="custom-dropdown-input-wrap" style="position: relative; width: 100%;">
                        <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم العميل..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width:100%; padding-left: 78px; padding-right: 12px; color: var(--amber, #fbbf24); font-size: 1.08rem; font-weight: 900; background-color: rgba(251,191,36,0.06); border: 1px solid rgba(148,163,184,0.32);" autocomplete="one-time-code">
                        <button type="button" id="hdr-customer-ledger-btn" onclick="if(typeof window.openCustomerLedgerFromHeader==='function'){window.openCustomerLedgerFromHeader();}else{openGeneralLedgerForAccount('cust-' + document.getElementById('hdr-customer').value);}" class="ledger-btn-2027" title="دفتر الأستاذ العام">أستاذ</button>
                        <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();">
                        <div class="custom-dropdown-list" id="customer-dropdown-list"></div>
                    </div>
                </div>
                <button type="button" id="btn-quick-add-customer" class="add-row-btn" onclick="openModal('add-customer-modal')" title="إضافة عميل جديد (F4)" style="flex-shrink: 0; width: 38px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
            </div>
        </div>

        <div class="h-form-group group-currency" style="flex: 0 0 auto; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
            <select class="v-input ctrl-input system-currency-select" id="hdr-currency" onchange="handleCurrencyChange(this.value); var c=(this.value==='USD'?'USD':'SDG'); window._finStripCurrency=c; try{localStorage.setItem('sales_fin_strip_cur',c);}catch(_e){} var b=document.getElementById('hdr-currency-toggle-btn'); if(b){b.textContent=(c==='USD'?'دولار':'جنيه'); b.dataset.cur=c;} if(typeof window.onCustomerChange==='function') window.onCustomerChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="display:none !important;">
                <option value="SDG">جنيه</option>
                <option value="USD">دولار</option>
                <option value="SAR">ريال</option>
                <option value="AED">درهم</option>
                <option value="EGP">مصري</option>
            </select>
            <button type="button" id="hdr-currency-toggle-btn" data-cur="SDG" onclick="var s=document.getElementById('hdr-currency'); if(s){s.value=(s.value==='USD'?'SDG':'USD'); this.textContent=(s.value==='USD'?'دولار':'جنيه'); this.dataset.cur=s.value; s.dispatchEvent(new Event('change'));}" title="اضغط للتبديل الفوري بين الجنيه والدولار" style="min-width: 68px; padding: 0 10px; height: 36px; border-radius: 9px; background: linear-gradient(180deg, #0284c7 0%, #1d4ed8 100%); border: 1.8px solid #7dd3fc; color: #ffffff; font-family: 'Cairo', sans-serif; font-size: 0.92rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; flex-shrink: 0;">جنيه</button>
            <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" placeholder="سعر الصرف" readonly style="width: 102px; min-width: 96px; text-align: center; cursor:pointer; padding: 0 6px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; this.dataset.userEdited='1'; if(window.autoFitHeaderFields) window.autoFitHeaderFields(); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
        </div>

        <button type="button" id="btn-neg-sales-lock" data-unlocked="0" onclick="window.handleNegSalesLockClick && window.handleNegSalesLockClick()" title="صلاحية البيع بالسالب (مقفول — اضغط لإدخال كلمة مرور المدير للفتح)" aria-label="صلاحية البيع بالسالب" style="flex: 0 0 36px; width: 36px; min-width: 36px; height: 38px; border-radius: 9px; background: linear-gradient(180deg, #ef4444 0%, #b91c1c 100%); border: 1.8px solid #fca5a5; color: #ffffff; font-size: 0.95rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; padding: 0; position: relative; box-shadow: 0 3px 10px rgba(220,38,38,0.45);">🔒</button>

        <div class="h-form-group group-warehouse" style="flex: 1.15 1 0%; min-width: 155px; max-width: 215px; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="margin:0; white-space:nowrap;">📦 المخزن</label>
            <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width: 100%; font-weight: 800; padding: 0 10px;"></select>
            </div>
        </div>
    </div>

    <!-- ROW 2: البيان + مركز الكلفة -->
    <div id="sales-row2-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; margin: 0; width: 100%; align-items: center;">
        <div class="h-form-group group-description" style="flex: 3.0 1 0%; min-width: 0; display:flex; align-items:center; gap:6px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="margin: 0; white-space: nowrap; font-weight: 900;">📝 البيان</label>
            <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                <input type="text" class="v-input ctrl-input" id="hdr-description" placeholder="ملاحظات، أرقام مرجعية..." style="width: 100%; border-radius: 9px;">
            </div>
        </div>
        <div class="h-form-group group-cost-center" style="flex: 1.55 1 0%; min-width: 190px; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="margin:0; white-space:nowrap;">🏷️ مركز الكلفة</label>
            <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                <select class="v-input ctrl-input" id="hdr-cost-center" onchange="if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width: 100%; font-weight: 800; padding: 0 10px;">
                    <option value="cc-001">الفرع الرئيسي</option>
                    <option value="cc-002">فرع بحري</option>
                </select>
            </div>
        </div>
    </div>
<!-- Due Date (hidden normally) -->
                        <div class="h-form-group" id="due-date-group" style="display:none; border-color: rgba(245,158,11,0.3); background: rgba(245,158,11,0.05); padding:10px; border-radius:8px;">
                            <label class="h-label" style="color: #f59e0b;">الاستحقاق</label>
                            <div class="h-input-wrapper">
                                <input type="date" class="v-input ctrl-input" id="hdr-due-date" style="color:#f59e0b; background: rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.2);">
                            </div>
                        </div>

                    </div>
                </div>


            </div><!-- end ds-top-row -->


            <!-- ══ FULL-WIDTH: جدول الأصناف ══ -->
            <div class="panel" id="items-panel" style="z-index: 50;">
                <div class="panel-header-row" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 27, 50, 0.75)); border: 1px solid rgba(56, 189, 248, 0.18); border-radius: 12px; padding: 8px 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <div class="panel-title" style="white-space: nowrap; font-weight: 900; color: #38bdf8; font-size: 1.05rem; display: flex; align-items: center; gap: 6px; text-shadow: 0 0 10px rgba(56,189,248,0.25);">
                            <span>📦</span>
                            <span>أصناف الفاتورة</span>
                        </div>
                        <div class="quick-add-box" style="display: none !important;">
                            <input type="text" class="g-input" id="quick-item-search" placeholder="بحث صنف أو باركود...">
                            <div class="custom-dropdown-list" id="quick-item-dropdown-list"></div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <button type="button" id="btn-sales-baskool" onclick="window.openSalesFc2DocumentaryAndLandedCostModal && window.openSalesFc2DocumentaryAndLandedCostModal()" style="background: linear-gradient(135deg, #b45309, #78350f); border: 1.5px solid #fbbf24; color: #fef3c7; border-radius: 9px; height: 34px; padding: 0 12px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.88rem; white-space: nowrap;" title="حاسبة البسكول ومعيار الحديد والتكلفة المضافة">
                            <span>⚖️</span>
                            <span>البسكول</span>
                        </button>
                        <button type="button" id="btn-sales-ai-prices" onclick="if(window.openSalesAIAuditModal) window.openSalesAIAuditModal();" style="background: linear-gradient(135deg, #3730a3, #312e81); border: 1.5px solid #818cf8; color: #e0e7ff; border-radius: 8px; height: 34px; padding: 0 12px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.88rem; white-space: nowrap; transition: all 0.2s;" title="الأسعار وهامش البيع">
                            <span>💰</span>
                            <span>الأسعار</span>
                        </button>
                        <button type="button" class="unified-col-props-btn" onclick="openGridSettingsModal()" style="display:inline-flex !important; align-items:center !important; justify-content:center !important; gap:7px !important; height:43px !important; padding:0 18px !important; border-radius:10px !important; background:linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important; border:1.8px solid #38bdf8 !important; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; font-family:'Cairo','Tajawal',sans-serif !important; font-size:0.92rem !important; font-weight:900 !important; cursor:pointer !important; box-shadow:0 4px 12px rgba(14,165,233,0.25) !important; white-space:nowrap !important; flex-shrink:0 !important;" title="خصائص الأعمدة"><span>⚙️</span><span>خصائص الأعمدة</span></button>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="grid-scroll-wrap">
                    <table class="pos-grid" id="pos-grid" style="width: 100%; min-width: 1000px;">
                        <thead>
                            <tr style="background: rgba(15, 23, 42, 0.8); border-bottom: 2px solid rgba(56, 189, 248, 0.6);">
                                <th data-col-id="col_num" class="th-num" style="width: 3%;">#</th>
                                <th data-col-id="col_item" class="th-item" style="width: 18%;">الصنف</th>
                                <th data-col-id="col_unit" class="th-unit" style="width: 7%;">الوحدات</th>
                                <th data-col-id="col_factor" class="th-factor" style="width: 5%;">التعادل</th>
                                <th data-col-id="col_qty" class="th-qty" style="width: 7%;">الكمية</th>
                                <th data-col-id="col_price" class="th-price" style="width: 8%;">السعر</th>
                                <th data-col-id="col_baseqty" class="th-baseqty" style="width: 6%;">العدد</th>
                                <th data-col-id="col_avail" class="th-avail" style="width: 6%; color:#0ea5e9;">المتاح</th>
                                <th data-col-id="col_wh" class="th-wh" style="width: 8%;">المخزن</th>
                                <th data-col-id="col_note" class="th-note" style="width: 10%;">ملاحظة</th>
                                <th data-col-id="col_total" class="th-total" style="width: 240px; min-width: 240px;">الإجمالي</th>
                                <th data-col-id="col_del" class="th-del" style="width: 90px; min-width: 90px; max-width: 90px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="grid-body"></tbody>

                                        <tfoot id="grid-foot">
                        <!-- Transport -->
                        <tr class="tot-row" id="row-tot-transport" style="display: none; background: rgba(249, 115, 22, 0.08); border-top: 1px dashed rgba(249, 115, 22, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #f97316; font-size: 1.3rem; font-weight: 900;">🚚 الترحيل</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-transport" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('transport')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(249, 115, 22, 0.15); border: 1.5px solid rgba(249, 115, 22, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="t-transport-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('transport')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Labor -->
                        <tr class="tot-row" id="row-tot-labor" style="display: none; background: rgba(59, 130, 246, 0.08); border-top: 1px dashed rgba(59, 130, 246, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #38bdf8; font-size: 1.3rem; font-weight: 900;">💪 العمالة</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-labor" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('labor')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(59, 130, 246, 0.15); border: 1.5px solid rgba(59, 130, 246, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="t-labor-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('labor')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Additions -->
                        <tr class="tot-row" id="row-tot-additions" style="display: none; background: rgba(234, 179, 8, 0.08); border-top: 1px dashed rgba(234, 179, 8, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #eab308; font-size: 1.3rem; font-weight: 900;">➕ إضافات</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="addition-amt" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('additions')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(234, 179, 8, 0.15); border: 1.5px solid rgba(234, 179, 8, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="addition-amt-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('additions')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Tax -->
                        <tr class="tot-row" id="row-tot-tax" style="display: none; background: rgba(239, 68, 68, 0.08); border-top: 1px dashed rgba(239, 68, 68, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #ef4444; font-size: 1.3rem; font-weight: 900;">🏛️ ضريبة</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="tax-amt" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('tax')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(239, 68, 68, 0.15); border: 1.5px solid rgba(239, 68, 68, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="tax-amt-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('tax')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Discount -->
                        <tr class="tot-row" id="row-tot-discount" style="display: none; background: rgba(34, 197, 94, 0.08); border-top: 1px dashed rgba(34, 197, 94, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #22c55e; font-size: 1.3rem; font-weight: 900;">➖ خصم</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="discount-amt" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('discount')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(34, 197, 94, 0.15); border: 1.5px solid rgba(34, 197, 94, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="discount-amt-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('discount')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Spacer -->
                        <tr style="border: none;">
                            <td colspan="12" style="border: none; height: 8px;"></td>
                        </tr>
                        <!-- Grand Total (Dual SDG & USD Card matching purchase.php) -->
                        <tr id="row-grand-total" data-compact-normalized="1" style="background: transparent !important; border-top: none !important;">
                            <td colspan="12" style="border: none !important; padding: 10px 0 4px 0 !important; text-align: left !important; vertical-align: middle !important;">
                                <div class="pur-compact-tot-card">
                                    <div id="toggles-area" style="display: inline-flex; flex-wrap: nowrap; gap: 5px; align-items: center; max-width: none; margin-left: 8px;">
                                        <button type="button" class="tot-toggle-btn tot-btn-transport" onclick="toggleTotRow('transport')" id="btn-toggle-transport">ترحيل</button>
                                        <button type="button" class="tot-toggle-btn tot-btn-labor" onclick="toggleTotRow('labor')" id="btn-toggle-labor">عمالة</button>
                                        <button type="button" class="tot-toggle-btn tot-btn-additions" onclick="toggleTotRow('additions')" id="btn-toggle-additions">إضافات</button>
                                        <button type="button" class="tot-toggle-btn tot-btn-tax" onclick="toggleTotRow('tax')" id="btn-toggle-tax">ضريبة</button>
                                        <button type="button" class="tot-toggle-btn tot-btn-discount" onclick="toggleTotRow('discount')" id="btn-toggle-discount">خصم</button>
                                        <input type="hidden" id="hdr-landed-cost-mode" value="value">
                                        <button type="button" id="btn-landed-cost-mode" onclick="this.dataset.mode=(this.dataset.mode==='qty'?'value':'qty'); document.getElementById('lbl-landed-cost-mode').textContent=(this.dataset.mode==='qty'?'بالكمية':'بالقيمة');" style="height: 26px; padding: 0 9px; border-radius: 7px; background: #0369a1; border: 2px solid #38bdf8; color: #ffffff; font-family: 'Cairo', sans-serif; font-size: 0.76rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                                            <span>التوزيع:</span><span id="lbl-landed-cost-mode" style="color:#fde047;">بالقيمة</span>
                                        </button>
                                    </div>
                                    <span id="lbl-grand-dynamic">الإجمالي الكلي:</span>
                                    <div style="display: inline-flex; direction: rtl; flex-wrap: nowrap; gap: 14px; align-items: center; justify-content: flex-end;">
                                        <span class="val amount" id="t-grand-dynamic-fc" data-cur-unit="$">0.00</span>
                                        <span class="val amount" id="t-grand-dynamic-loc" data-cur-unit="ج.س">0</span>
                                    </div>
                                    <span id="t-grand-currency-badge" style="display:none;">SDG</span>
                                    <span id="t-grand" style="display:none;">0</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    </table>
                </div>

                <!-- Centered Add Row Button -->
                <div id="add-row-container" style="display: none !important;">
                    <button type="button" onclick="addRow()" id="add-row-btn" style="
                        width: 40px;
                        height: 40px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: linear-gradient(135deg, #10b981, #059669);
                        color: white;
                        border: none;
                        border-radius: 50%;
                        cursor: pointer;
                        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
                        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        outline: none;
                    " onmouseover="this.style.transform='scale(1.15) rotate(90deg)'; this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='scale(1) rotate(0deg)'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.25)';" title="إضافة سطر">
                        <span style="font-size: 1.2rem; font-weight: bold; line-height: 1;">➕</span>
                    </button>
                </div>

                <!-- Mobile Cards -->
                <div class="mobile-cards" id="mobile-cards"></div>

                <!-- Empty State -->
                <div class="empty-state" id="empty-state">
                    <p>لم يتم إضافة أصناف بعد</p>
                    <span class="empty-hint">اضغط <strong>"+ صنف"</strong> أو <kbd>Ctrl+N</kbd></span>
                </div>
            </div><!-- end items-panel -->

            <!-- ══ INLINE TOTALS & EXTRA COSTS ══ -->
            <link rel="stylesheet" href="css/screens/direct-sales-inline-13.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-13.css') ?: '2027' ?>">
            <div id="inline-totals-container" style="display: none !important;">
                <!-- Right Side is now handled inside tfoot -->
                <div id="amounts-area" style="display: none;"></div>

                <!-- Hidden Spans needed for JS compatibility -->
                <div id="hidden-recalc-spans" style="display:none;">
                    <span id="t-invoice-total">0</span>
                    <span id="t-invoice-total-fc">0</span>
                    <span id="lbl-grand-local"></span>
                    <span id="t-grand-local">0</span>
                    <span id="lbl-grand-fc"></span>
                    <span id="t-grand-fc">0</span>
                </div>
            </div>

            <script src="js/screens/direct-sales-inline-21.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-21.js') ?: '2027' ?>"></script>

            
    <link rel="stylesheet" id="grand-total-dynamic-anim-css" href="css/screens/direct-sales-inline-14.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-14.css') ?: '2027' ?>">

            <!-- ══ FULL-WIDTH: تفاصيل الدفع (نسخة مطابقة 100% للمشتريات) ══ -->
            <div class="panel" id="payment-panel" style="display: none; z-index: 40; margin-top: 15px; padding: 0 !important; background: rgba(15, 23, 42, 0.95) !important; border: 1px solid rgba(234, 179, 8, 0.22) !important;">
                <link rel="stylesheet" href="css/screens/direct-sales-inline-15.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-15.css') ?: '2027' ?>">
                <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 10px 18px; background: rgba(15,23,42,0.4); border-bottom: 1px solid rgba(255,255,255,0.06); border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 0;">
                    <div style="display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <div class="panel-title" style="background: linear-gradient(135deg, #ff4d4d, #f87171); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.12rem; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; margin: 0; white-space: nowrap;">
                            <span>💳</span> طريقة وتفاصيل الدفع
                        </div>
                        <button type="button" id="sal-fin-mov-seq-badge" onclick="window.openSalesLinkedFinMovement && window.openSalesLinkedFinMovement()" title="اضغط للانتقال المباشر إلى شاشة الحركة المالية لهذا الرقم" style="display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 13px; border-radius: 9px; background: linear-gradient(180deg, #1e3a5f 0%, #0f172a 100%); border: 1.6px solid #7dd3fc; color: #ffffff; cursor: pointer; font-family: 'Outfit','Cairo',sans-serif; font-size: 0.9rem; font-weight: 900; white-space: nowrap;">
                            <span style="color:#bae6fd; font-size:0.82rem;">حركة مالية:</span>
                            <span id="sal-fin-mov-seq-val" style="color:#ffffff; font-size:1.02rem; direction:ltr; letter-spacing:0.5px;"><?= htmlspecialchars($salNextFinNo) ?></span>
                            <span style="color:#7dd3fc; font-size:0.82rem;">↗</span>
                        </button>
                    </div>
                    <!-- Sleek Payment Progress Bar (Inline) -->
                    <div class="payment-progress-container" style="flex: 1; display: flex; align-items: center; gap: 10px; min-width: 200px; padding: 0 8px;">
                        <div class="progress-bar-wrapper" style="height: 8px; flex: 1; background: rgba(255,255,255,0.08); border-radius: 5px; overflow: hidden; display: flex; min-width: 90px;">
                            <div id="prog-cash" style="width: 0%; height: 100%; background: #10b981;"></div>
                            <div id="prog-bank" style="width: 0%; height: 100%; background: #38bdf8;"></div>
                            <div id="prog-cheque" style="width: 0%; height: 100%; background: #94a3b8;"></div>
                            <div id="prog-credit" style="width: 100%; height: 100%; background: #64748b;"></div>
                        </div>
                        <span id="payment-status-badge" style="font-size: 0.78rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; white-space: nowrap;">⏳ آجلة بالكامل</span>
                    </div>
                    <div class="payment-toggles" style="display: flex; gap: 6px; flex: none; flex-wrap: wrap; align-items: center;">
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn active" id="toggle-credit" onclick="togglePaymentSection('credit')">آجل</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="toggle-cash" onclick="togglePaymentSection('cash')">نقدي</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="toggle-bank" onclick="togglePaymentSection('bank')">تحويل</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')">شيك</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="btn-financial-qr" onclick="if(window.FinancialOCR && typeof FinancialOCR.openMobileQRBridge==='function'){FinancialOCR.openMobileQRBridge({ target: 'sales' });}" title="ربط الجوال عبر رمز QR"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg><span>ربط الجوال</span></button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="btn-financial-wa-tray" onclick="window.openSalesInboundReceiptsDrawer && window.openSalesInboundReceiptsDrawer()" title="فتح ترحيل الإشعارات مباشرة"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg><span>ترحيل الإشعارات</span></button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="btn-sales-posting-accounts" onclick="window.location.href='posting-accounts.php'" title="الانتقال إلى شاشة ترحيل الحسابات"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg><span>ترحيل الحسابات</span></button>
                    </div>
                </div>
                
                <div class="payment-layout" id="payment-layout" style="display: none; gap: 15px; padding: 15px; flex-wrap: wrap;">
                    <!-- Right Section: Payment Details Forms -->
                    <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">
                        <!-- Credit payment box -->
                        <div id="pay-credit-section" class="payment-method-box" style="background: rgba(15, 23, 42, 0.96); border: 2px solid rgba(217, 119, 6, 0.85); border-radius: 12px; padding: 10px 14px; display: none; align-items: center; justify-content: flex-start; gap: 12px; flex-wrap: nowrap;">
                            <div class="pay-credit-title-pill" style="display: inline-flex; align-items: center; justify-content: center; padding: 5px 12px; border-radius: 9px; flex: 0 0 auto;">
                                <span class="pay-credit-lbl-txt" style="font-weight: 900; font-size: 0.96rem; white-space: nowrap;">آجل على الحساب</span>
                            </div>
                            <input type="number" class="v-input ctrl-input" id="pay-credit-amount" value="0.00" step="any" readonly placeholder="المبلغ الآجل..." oninput="if(window.autoFitSingleInput) window.autoFitSingleInput(this);" style="flex: 0 1 175px; min-width: 135px; max-width: 195px; width: 175px; background: #0b1120; border: 2px solid rgba(180, 83, 9, 0.75); height: 40px; text-align: center; font-size: 1.15rem; font-weight: 900; color: #fcd34d !important;">
                            <div id="pay-credit-due-group" style="flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px;">
                                <label class="pay-due-lbl-txt" style="font-size: 0.94rem; font-weight: 900; white-space: nowrap; padding: 4px 8px; border-radius: 7px; margin: 0;">تاريخ الاستحقاق:</label>
                                <input type="date" class="v-input ctrl-input" id="pay-credit-due-date" style="background: #0b1120; border: 2px solid rgba(255,255,255,0.32); height: 40px; font-size: 1.05rem; font-weight: 900; color: #ffffff !important; text-align: center; padding: 0 8px; width: 168px; min-width: 162px; max-width: 176px;">
                            </div>
                            <button type="button" class="btn-action pur-promissory-preview-btn" onclick="previewCurrentPromissoryNote()" style="flex: 0 0 auto; width: auto !important; min-width: 0 !important; max-width: none !important; height: 38px !important; padding: 0 12px !important; margin-right: auto; border-radius: 9px; font-size: 0.86rem !important; cursor: pointer; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap;"><span>📄</span><span>معاينة الكمبيالة</span></button>
                        </div>

                        <!-- Cash payment box -->
                        <div id="pay-cash-section" style="background: rgba(15, 23, 42, 0.96); border: 2px solid rgba(16, 185, 129, 0.85); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; display: none; flex-direction: column; width: 100%;">
                            <div class="payment-row-item" style="display: grid; grid-template-columns: 85px 22% 18% 18% minmax(0, 1fr) 30px; gap: 10px; align-items: center; width: 100%; margin-bottom: 0px;">
                                <div class="pay-method-row-lbl" style="display: flex; align-items: center; justify-content: flex-start; color: #6ee7b7; font-weight: 900; font-size: 1.05rem; gap: 6px; text-shadow: 0 0 8px rgba(16, 185, 129, 0.4);"><span>💵</span> كاش</div>
                                <input type="number" class="v-input ctrl-input dynamic-focus-input dynamic-focus-amount" id="pay-cash-amount" value="0" step="1" oninput="onPaymentAmountInput()" placeholder="المبلغ" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.5); border-radius: 8px; text-align: center; font-size: 2.35rem; font-weight: 900; color: #ffffff; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; min-width: 0; box-sizing: border-box; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                                <select class="v-input ctrl-input" id="hdr-payment-account" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; text-align: center; font-size: 1.05rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%; min-width: 0; box-sizing: border-box;"></select>
                                <input type="text" class="v-input ctrl-input dynamic-focus-input dynamic-focus-number" id="pay-cash-ref" placeholder="رقم المرجع" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; text-align: center; font-size: 2.35rem; font-weight: 900; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%; min-width: 0; box-sizing: border-box;">
                                <input type="text" class="v-input ctrl-input" id="pay-cash-note" placeholder="الملحوظة" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; text-align: center; font-size: 1.05rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%; min-width: 0; box-sizing: border-box;">
                                <div></div>
                            </div>
                        </div>

                        <!-- Bank Transfers box -->
                        <div id="pay-bank-section" style="display: none; flex-direction: column; background: rgba(15, 23, 42, 0.96); border: 2px solid rgba(6, 182, 212, 0.85); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; width: 100%;">
                            <div id="bank-transfers-list" style="display: flex; flex-direction: column; gap: 0px; width: 100%;"></div>
                        </div>

                        <!-- Cheques box -->
                        <div id="pay-cheque-section" style="display: none; flex-direction: column; background: rgba(15, 23, 42, 0.96); border: 2px solid rgba(168, 85, 247, 0.85); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; width: 100%;">
                            <div id="cheques-list" style="display: flex; flex-direction: column; gap: 0px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ NOTES BAR: ملاحظات الفاتورة (full width below table) ══ -->
            <div class="ds-notes-bar" style="display: none;">
                <label class="label">📝 ملاحظات الفاتورة</label>
                <textarea class="ctrl-input" id="inv-notes" rows="2" placeholder="أي ملاحظات إضافية على الفاتورة..."></textarea>
            </div>

        </div><!-- end ds-body -->


        <!-- Posting Status Bar -->
        <div id="posting-status-bar" style="display: none; align-items: center; justify-content: space-between; padding: 12px 20px; border-radius: 12px; background: rgba(18, 15, 25, 0.8); border: 1px solid rgba(255,255,255,0.05); margin-top: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span id="posting-status-icon" style="font-size: 1.5rem;"></span>
                <div>
                    <div id="posting-status-text" style="color: #fff; font-weight: bold; font-size: 1.1rem;"></div>
                    <div id="posting-status-desc" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;"></div>
                </div>
            </div>
            <div id="posting-status-actions">
                <!-- Buttons injected dynamically -->
            </div>
        </div>
        <!-- Dynamic Studio Styles for Invoice Grid -->
        <style id="dynamic-studio-styles"></style>


        <link rel="stylesheet" id="sales-buttons-typography-2027" href="css/screens/direct-sales-inline-16.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-16.css') ?: '2027' ?>">

        <div class="items-toolbar">
            <div class="toolbar-actions">
                <!-- Back Button (رجوع متسلسل) -->
                <button type="button" id="btn-back-invoice" class="btn-action royal-crystal-btn royal-btn-back btn-back" style="flex: 0.78 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px;" onclick="if(typeof window.salesConstitutionalBack === 'function') { window.salesConstitutionalBack(); } else if(typeof showSalesHub === 'function' && document.getElementById('sales-hub-view')) { showSalesHub(); } else if(typeof magaieesGoBack === 'function') { magaieesGoBack(); }" title="القانون (4): الرجوع المتسلسل خطوة للخلف (Esc)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 19 12 12 5"/></svg>
                    <span class="btn-fit-label">رجوع</span>
                </button>

                <!-- Exit Button (خروج مباشر للشاشة الرئيسية) -->
                <button type="button" id="btn-exit-invoice" class="btn-action royal-crystal-btn royal-btn-exit btn-exit" style="flex: 0.78 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px;" onclick="if(typeof window.salesConstitutionalExit === 'function') { window.salesConstitutionalExit(); } else { window.location.href='magaiees_unified_dashboard.php'; }" title="القانون (4): الخروج المباشر للشاشة الرئيسية">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span class="btn-fit-label">خروج</span>
                </button>

                <!-- Delete / Cancel Button -->
                <button type="button" id="btn-delete-invoice" class="btn-action royal-crystal-btn royal-btn-delete btn-delete" style="flex: 0.85 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; background: linear-gradient(135deg, rgba(225, 29, 72, 0.85), rgba(159, 18, 57, 0.92)) !important; border: 1px solid rgba(251, 113, 133, 0.45) !important; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.28) !important;" onclick="confirmDeleteTransaction()" title="حذف أو إلغاء الفاتورة الحالية">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span class="del-btn-label btn-fit-label">حذف</span>
                </button>

                <!-- Unified Print Studio & Preview Button -->
                <button type="button" id="btn-preview-invoice" class="btn-action royal-crystal-btn royal-btn-print btn-preview" style="flex: 1.05 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px;" onclick="openStandalonePrintPreview();" title="استوديو الطباعة والمعاينة الحية">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span class="btn-fit-label">طباعة</span>
                    <kbd style="font-size:0.62rem; padding:1px 4px; border-radius:4px; background:rgba(0,0,0,0.28); border:1px solid rgba(255,255,255,0.22); font-family:monospace; color:#e2e8f0; flex-shrink:0;">F8</kbd>
                </button>

                <div class="preset-selector-container" style="flex: 1.05; min-width: 120px; display: flex; gap: 4px;">
                    <select id="preset-selector" class="ctrl-input" style="margin: 0; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(56, 189, 248, 0.4); color: #fff; flex: 1;" onchange="loadPreset(this.value)">
                        <option value="">⚙️ التخصيصات (0)</option>
                    </select>
                </div>
                
                <!-- Share Button: Professional with dropdown -->
                <div style="position: relative; flex: 0.95; min-width: 104px;" id="share-btn-wrapper">
                    <button type="button" class="btn-action royal-crystal-btn royal-btn-whatsapp btn-share" id="btn-share-invoice" style="width: 100%;" onclick="toggleShareMenu(event)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <span>مشاركة</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.75;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <!-- Share Popup Menu -->
                    <div id="share-popup-menu" style="display: none; position: absolute; bottom: 55px; left: 50%; transform: translateX(-50%) scale(0.9); opacity: 0; min-width: 220px; background: linear-gradient(160deg, rgba(30,27,50,0.98), rgba(15,12,30,0.98)); border: 1px solid rgba(139, 92, 246, 0.35); border-radius: 16px; padding: 8px; box-shadow: 0 15px 40px rgba(0,0,0,0.5), 0 0 30px rgba(139, 92, 246, 0.15);   z-index: 9999; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">
                        <!-- Arrow -->
                        <div style="position: absolute; bottom: -7px; left: 50%; transform: translateX(-50%) rotate(45deg); width: 14px; height: 14px; background: rgba(15,12,30,0.98); border-right: 1px solid rgba(139, 92, 246, 0.35); border-bottom: 1px solid rgba(139, 92, 246, 0.35);"></div>
                        <!-- WhatsApp Option -->
                        <button type="button" onclick="closeShareMenu(); sendCurrentInvoiceWA();" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: transparent; border: none; border-radius: 12px; color: #e2e8f0; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'Tajawal', sans-serif; direction: rtl;" onmouseover="this.style.background='rgba(37, 211, 102, 0.12)'; this.style.color='#25d366';" onmouseout="this.style.background='transparent'; this.style.color='#e2e8f0';">
                            <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #25d366, #128C7E); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 3px 10px rgba(37, 211, 102, 0.3);">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </span>
                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 2px;">
                                <span>إرسال عبر واتساب</span>
                                <span style="font-size: 0.7rem; color: #64748b; font-weight: 400;">إرسال PDF مع رسالة</span>
                            </div>
                        </button>
                        <!-- Divider -->
                        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.2), transparent); margin: 4px 12px;"></div>
                        <!-- Email Option -->
                        <button type="button" onclick="closeShareMenu(); sendCurrentInvoiceEmail();" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: transparent; border: none; border-radius: 12px; color: #e2e8f0; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'Tajawal', sans-serif; direction: rtl;" onmouseover="this.style.background='rgba(245, 158, 11, 0.12)'; this.style.color='#f59e0b';" onmouseout="this.style.background='transparent'; this.style.color='#e2e8f0';">
                            <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 3px 10px rgba(245, 158, 11, 0.6);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </span>
                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 2px;">
                                <span>إرسال عبر الإيميل</span>
                                <span style="font-size: 0.7rem; color: #64748b; font-weight: 400;">فتح تطبيق البريد</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- New Button: Unified Cyan-Sky Crystal Gradient -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-new btn-new" id="btn-new-invoice" style="flex: 0.82 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px;" onclick="onSuccessNewInvoice(false);" title="بدء فاتورة مبيعات جديدة وتفريغ الحقول (+)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span class="btn-fit-label">جديد</span>
                </button>

                <!-- OCR Scanner Button -->
                <button type="button" class="btn-action royal-crystal-btn btn-ocr" style="flex: 1.05 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px;" onclick="if(typeof triggerInvoiceOCR==='function') triggerInvoiceOCR(); else if(typeof window.openSalesFc1AiOcrAndPriceAdvisorModal==='function') window.openSalesFc1AiOcrAndPriceAdvisorModal();">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span class="btn-fit-label">مسح فاتورة</span>
                </button>

                <!-- زر التحكم في الإرسال التلقائي مع الحفظ -->
                <div class="auto-send-control-box" id="auto-send-control-wrapper-sales">
                    <button type="button" class="btn crystal-btn btn-auto-send-toggle-unit inactive" id="btn-toggle-auto-send-sales" onclick="window.toggleSalesAutoSendMode()" title="التحكم في الإرسال التلقائي: انقر للتبديل بين الحفظ مع الإرسال، أو الحفظ فقط بدون إرسال">
                        <span id="auto-send-sales-icon" style="font-size: 1.02rem;">💾</span>
                        <span id="auto-send-sales-label" style="font-weight: 800; font-size: 0.85rem;">إرسال تلقائي:</span>
                        <span id="auto-send-sales-status-pill" style="padding: 2px 7px; border-radius: 6px; font-size: 0.73rem; font-weight: 900;">معطّل (حفظ فقط)</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-auto-send-options-unit inactive" id="btn-auto-send-options-sales" onclick="window.toggleSalesAutoSendDropdown(event)" title="خيارات نوع الإرسال التلقائي (إذن استلام، فاتورة وكشف)">
                        ▼
                    </button>
                    
                    <!-- القائمة المنسدلة الملكية للخيارات السريعة -->
                    <div class="auto-send-menu-dropdown" id="auto-send-sales-dropdown-menu" style="display: none;">
                        <div class="auto-send-menu-header">
                            <span style="display: inline-flex; align-items: center; gap: 7px;">
                                <span style="font-size: 1rem;">⚙️</span>
                                <span>نوع الإرسال التلقائي عند الحفظ</span>
                            </span>
                            <span style="font-size: 0.68rem; padding: 2px 8px; border-radius: 20px; background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">واتساب ذكي</span>
                        </div>
                        <div class="auto-send-menu-item active-option" data-mode="disabled" onclick="window.selectSalesAutoSendOption('disabled')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(100, 116, 139, 0.35), rgba(51, 65, 85, 0.6)) !important; border-color: rgba(148, 163, 184, 0.4) !important;">💾</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #f1f5f9;">حفظ فقط (بدون إرسال)</span>
                                <span class="auto-send-item-sub">اعتماد وحفظ الفاتورة دون إرسال رسائل</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="delivery_order_doc" onclick="window.selectSalesAutoSendOption('delivery_order_doc')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(2, 132, 199, 0.55)) !important; border-color: rgba(56, 189, 248, 0.45) !important;">📄</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #38bdf8;">إذن استلام وتسليم رسمي (كميات فقط)</span>
                                <span class="auto-send-item-sub">مستند استلام مخزني معتمد بدون أسعار</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="delivery_order_text" onclick="window.selectSalesAutoSendOption('delivery_order_text')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.55)) !important; border-color: rgba(251, 191, 36, 0.45) !important;">📝</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #fbbf24;">إذن استلام كتابة كمعلومة (نص سريع)</span>
                                <span class="auto-send-item-sub">رسالة نصية سريعة بتفاصيل الأصناف</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="invoice_doc" onclick="window.selectSalesAutoSendOption('invoice_doc')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(126, 34, 206, 0.55)) !important; border-color: rgba(192, 132, 252, 0.45) !important;">🧾</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #c084fc;">فاتورة مبيعات مصممة (تصميم رسمي)</span>
                                <span class="auto-send-item-sub">إرسال صورة/مستند الفاتورة الكاملة للعميل</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="invoice" onclick="window.selectSalesAutoSendOption('invoice')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(5, 150, 105, 0.55)) !important; border-color: rgba(52, 211, 153, 0.45) !important;">💰</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #34d399;">نص الفاتورة وكشف الحساب</span>
                                <span class="auto-send-item-sub">ملخص مالي للفاتورة مع الرصيد وكشف الحساب</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button (F10 Single-Click Save Lock) -->
                <span style="display:none;" id="footer-grand"></span>
                <button type="button" id="btn-submit-invoice" class="btn-action royal-crystal-btn royal-btn-save btn-submit" style="flex: 1.35; min-width: 148px;" onclick="submitInvoice()" title="حفظ واعتماد الفاتورة مع التسميع الذري (F10 — قفل الحفظ مرة واحدة)">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>حفظ واعتماد <kbd class="c3-kbd" style="font-size:0.72rem;padding:1px 5px;border-radius:4px;background:rgba(255,255,255,0.2);">F10</kbd></span>
                </button>
            </div>
        </div>

        <div id="adjustments-panel" style="display: none;">
            <!-- removed amt inputs, kept pcts just in case -->
            <input type="number" id="discount-pct" value="0">
            <input type="number" id="tax-pct" value="0">
            <input type="number" id="addition-pct" value="0">
        </div>

        <!-- ═══ STICKY FOOTER ═══ -->
        </div>
</div>
    </div>
    
    <script src="js/localforage.min.js"></script>
    <script src="js/direct-sales-studio.js?v=<?php echo file_exists('js/direct-sales-studio.js') ? filemtime('js/direct-sales-studio.js') : '1.0.0'; ?>"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js?v=<?php echo file_exists('js/whatsapp-integration.js') ? filemtime('js/whatsapp-integration.js') : '1.0.0'; ?>"></script>
    <script defer src="js/erp-whatsapp-suite.js?v=<?php echo file_exists('js/erp-whatsapp-suite.js') ? filemtime('js/erp-whatsapp-suite.js') : '1.0.0'; ?>"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '20261104'; ?>"></script>
    <script defer src="js/accounting-engine.js?v=<?php echo file_exists('js/accounting-engine.js') ? filemtime('js/accounting-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/print-engine.js?v=<?php echo file_exists('js/print-engine.js') ? filemtime('js/print-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/direct-sales-v3.js?v=<?php echo file_exists('js/direct-sales-v3.js') ? filemtime('js/direct-sales-v3.js') : '20261104'; ?>"></script>
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('./sw.js', { scope: './' }).catch(() => {});
    }
    </script>

<script src="js/screens/direct-sales-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j1.js') ?: '2027' ?>"></script>
    
    
    <!-- Context Menu for Columns -->
    <div id="col-context-menu" class="grid-context-menu" style="display:none; position:fixed; z-index:999999; background:#1e293b; border:1px solid #38bdf8; border-radius:10px; box-shadow:0 15px 35px rgba(0,0,0,0.7); min-width:240px; padding:8px 0; color:#fff; font-family:'Tajawal', sans-serif;">
        <div class="grid-context-title" style="padding:8px 16px; font-weight:bold; color:#38bdf8; border-bottom:1px solid rgba(255,255,255,0.1); font-size:1rem;">🛠️ خيارات وتخصيص الجدول</div>
        <div class="context-item-custom" onclick="openGridSettingsModal()" style="padding:10px 16px; cursor:pointer; display:flex; align-items:center; gap:8px; font-weight:bold;">⚙️ تخصيص وترتيب وتنسيق الأعمدة...</div>
        <hr class="context-item-hr" style="border:none; border-top:1px solid rgba(255,255,255,0.1); margin:4px 0;">
        <div class="context-item-custom" onclick="quickAlignGrid('right')" style="padding:8px 16px; cursor:pointer;">➡️ محاذاة النص لليمين</div>
        <div class="context-item-custom" onclick="quickAlignGrid('center')" style="padding:8px 16px; cursor:pointer;">↕️ محاذاة النص للوسط</div>
        <div class="context-item-custom" onclick="quickAlignGrid('left')" style="padding:8px 16px; cursor:pointer;">⬅️ محاذاة النص لليسار</div>
        <hr class="context-item-hr" style="border:none; border-top:1px solid rgba(255,255,255,0.1); margin:4px 0;">
        <div class="context-item-custom" onclick="quickResetGrid()" style="padding:8px 16px; cursor:pointer; color:#f87171;">🔄 استعادة الضبط الافتراضي</div>
    </div>

    <!-- Advanced Modal for Grid Customization (With Interactive Column Selection & DB Persistence) -->
    <div id="grid-settings-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85);  z-index:999999; justify-content:center; align-items:center; direction:rtl; font-family:'Tajawal', sans-serif;">
        <div class="glass-panel" style="width:96%; max-width:1200px; max-height:94vh; display:flex; flex-direction:column; padding:22px; border-radius:20px; border:1.5px solid rgba(56, 189, 248, 0.4); background:rgba(15,23,42,0.98); color:#fff; box-shadow:0 25px 60px rgba(0,0,0,0.85);">
            
            <!-- Modal Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.12); padding-bottom:12px; margin-bottom:14px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; border-radius:10px; width:42px; height:42px; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">⚙️</div>
                    <div>
                        <h3 style="margin:0; font-size:1.35rem; font-weight:900; color:#38bdf8;">تخصيص وتنسيق جدول المبيعات المباشرة (اختيار عمود أو ضبط عام)</h3>
                        <span style="font-size:0.85rem; color:#94a3b8;">اضغط على أي عمود لاختياره وتعديل حجم خطه، لونه، سُمكه، عرضه وفواصله، أو اضبط الإعدادات العامة لكامل الجدول</span>
                    </div>
                </div>
                <button type="button" onclick="closeGridSettingsModal()" style="background:rgba(255,255,255,0.08); border:none; color:#fff; font-size:1.4rem; cursor:pointer; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:background 0.2s;">✕</button>
            </div>
            
            <!-- Modal Body (2 Columns Layout) -->
            <div style="flex:1; overflow-y:auto; padding-right:4px; display:grid; grid-template-columns:1.1fr 1.35fr; gap:20px;">
                
                <!-- Right Side: Column Selector & Reorder List -->
                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:14px; display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:8px;">
                        <h4 style="margin:0; color:#38bdf8; font-size:1.05rem; font-weight:bold; display:flex; align-items:center; gap:6px;">📋 قائمة الأعمدة (اضغط للاختيار والتعديل)</h4>
                        <span style="font-size:0.78rem; color:#cbd5e1;">▲ ▼ للترتيب</span>
                    </div>
                    
                    <div id="col-settings-list" style="display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:55vh; padding-left:4px;">
                        <!-- Dynamically populated -->
                    </div>
                </div>
                
                <!-- Left Side: Interactive Selected Column Editor & Global Settings -->
                <div style="display:flex; flex-direction:column; gap:14px;">
                    
                    <!-- Box 1: Selected Column Detailed Properties -->
                    <div id="selected-col-panel" style="background:linear-gradient(135deg, rgba(56,189,248,0.08), rgba(15,23,42,0.6)); border:1.5px solid rgba(56,189,248,0.4); border-radius:14px; padding:14px; box-shadow:inset 0 1px 3px rgba(255,255,255,0.05);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(56,189,248,0.3); padding-bottom:8px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:1.2rem;">🎯</span>
                                <h4 style="margin:0; color:#38bdf8; font-size:1.05rem; font-weight:900;">خصائص العمود المحدد: <span id="lbl-selected-col-name" style="color:#facc15; font-size:1.15rem;"></span></h4>
                            </div>
                            <span id="badge-selected-col-id" style="font-size:0.75rem; background:rgba(56,189,248,0.2); color:#38bdf8; padding:3px 8px; border-radius:6px; font-family:monospace;"></span>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <!-- Column Title / Alias -->
                            <div style="grid-column:1 / -1;">
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">عنوان واسم العمود في الرأس:</label>
                                <input type="text" id="sel-col-name" oninput="onSelectedColPropChange('name', this.value)" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:7px 10px; font-weight:bold; font-size:0.95rem;">
                            </div>

                            <!-- Column Font Size -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">حجم خط هذا العمود:</label>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="number" id="sel-col-fontsize" min="8" max="42" oninput="document.getElementById('sel-col-fontsize-range').value=this.value; onSelectedColPropChange('fontSize', parseInt(this.value)||14)" style="width:64px; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:6px; text-align:center; font-weight:bold;">
                                    <input type="range" id="sel-col-fontsize-range" min="8" max="42" oninput="document.getElementById('sel-col-fontsize').value=this.value; onSelectedColPropChange('fontSize', parseInt(this.value)||14);" style="flex:1; cursor:pointer; accent-color:#38bdf8;">
                                    <span style="font-size:0.8rem; color:#38bdf8; font-family:monospace;">px</span>
                                </div>
                            </div>

                            <!-- Column Font Weight -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">سُمك خط هذا العمود:</label>
                                <select id="sel-col-fontweight" onchange="onSelectedColPropChange('fontWeight', this.value)" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:7px 10px; font-size:0.9rem;">
                                    <option value="">افتراضي (نفس الجدول)</option>
                                    <option value="normal">عادي (Normal)</option>
                                    <option value="bold">عريض (Bold)</option>
                                    <option value="800">سميك جداً (Extra Bold)</option>
                                </select>
                            </div>

                            <!-- Column Color -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">لون خط هذا العمود:</label>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="color" id="sel-col-color" oninput="onSelectedColPropChange('color', this.value)" style="border:none; width:45px; height:34px; cursor:pointer; background:transparent; border-radius:6px;">
                                    <input type="text" id="sel-col-color-text" oninput="onSelectedColPropChange('color', this.value); document.getElementById('sel-col-color').value=this.value;" style="width:85px; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:6px; color:#fff; padding:5px; text-align:center; font-family:monospace; font-size:0.85rem;">
                                    <button type="button" onclick="onSelectedColPropChange('color', '#ffffff'); document.getElementById('sel-col-color').value='#ffffff'; document.getElementById('sel-col-color-text').value='#ffffff';" style="background:rgba(255,255,255,0.1); border:none; border-radius:6px; padding:5px 8px; font-size:0.75rem; color:#fff; cursor:pointer;">أبيض</button>
                                </div>
                            </div>

                            <!-- Column Alignment -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">محاذاة هذا العمود:</label>
                                <select id="sel-col-align" onchange="onSelectedColPropChange('align', this.value)" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:7px 10px; font-size:0.9rem;">
                                    <option value="center">وسط (Center)</option>
                                    <option value="right">يمين (Right)</option>
                                    <option value="left">يسار (Left)</option>
                                </select>
                            </div>

                            <!-- Column Width -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">عرض العمود:</label>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="range" id="sel-col-width-range" min="40" max="450" oninput="document.getElementById('sel-col-width').value=this.value; onSelectedColPropChange('width', this.value+'px');" style="flex:1; cursor:pointer; accent-color:#38bdf8;">
                                    <input type="number" id="sel-col-width" min="40" max="450" oninput="document.getElementById('sel-col-width-range').value=this.value; onSelectedColPropChange('width', this.value+'px');" style="width:65px; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:5px; text-align:center; font-weight:bold;">
                                    <span style="font-size:0.8rem; color:#38bdf8; font-family:monospace;">px</span>
                                </div>
                            </div>

                            <!-- Column Decimals -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">الخانات العشرية لهذا العمود:</label>
                                <select id="sel-col-decimals" onchange="onSelectedColPropChange('decimals', this.value !== '' ? parseInt(this.value) : '')" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#38bdf8; padding:7px 10px; font-size:0.9rem;">
                                    <option value="">افتراضي (حسب الضبط العام)</option>
                                    <option value="0">0 (بدون كسور: 120)</option>
                                    <option value="1">1 (خانة واحدة: 120.5)</option>
                                    <option value="2">2 (خانتان: 120.50)</option>
                                    <option value="3">3 (ثلاث خانات: 120.500)</option>
                                    <option value="4">4 (أربع خانات: 120.5000)</option>
                                </select>
                            </div>

                            <!-- Column Thousands Separator -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.85rem; display:block; margin-bottom:4px;">الفاصلة الألفية لهذا العمود:</label>
                                <select id="sel-col-thousandsep" onchange="onSelectedColPropChange('thousandSep', this.value)" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#38bdf8; padding:7px 10px; font-size:0.9rem;">
                                    <option value="default">افتراضي (حسب الضبط العام)</option>
                                    <option value=",">ظهور الفاصلة (1,234.50)</option>
                                    <option value="none">إخفاء الفاصلة (1234.50)</option>
                                </select>
                            </div>

                            <!-- Apply To All Button -->
                            <div style="grid-column:1 / -1; margin-top:4px;">
                                <button type="button" onclick="applySelectedColStyleToAll()" style="background:rgba(99,102,241,0.2); color:#a5b4fc; border:1px solid rgba(99,102,241,0.4); border-radius:8px; padding:8px 12px; width:100%; font-weight:bold; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s;">
                                    ⚡ تطبيق حجم ولون وسُمك هذا العمود على جميع الأعمدة الأخرى
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Box 2: Global Table Settings -->
                    <div style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:14px;">
                        <h4 style="margin:0 0 10px 0; color:#38bdf8; font-size:0.98rem; font-weight:bold; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:6px;">🌐 الإعدادات والخطوط العامة للجدول بالكامل</h4>
                        
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <!-- Font Family -->
                            <div style="grid-column:1 / -1;">
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">نوع الخط (Font Family):</label>
                                <select id="grid-setting-font-family" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:6px 10px; font-size:0.9rem;">
                                    <option value="'Cairo', sans-serif">Cairo (الافتراضي - كلاسيكي وواضح)</option>
                                    <option value="'Tajawal', sans-serif">Tajawal (عصري وناعم)</option>
                                    <option value="'JetBrains Mono', monospace">JetBrains Mono (فائق الوضوح للأرقام)</option>
                                    <option value="'Consolas', monospace">Consolas (ممتاز للأرقام)</option>
                                    <option value="'Segoe UI', sans-serif">Segoe UI (رسمي وأنيق)</option>
                                    <option value="'Roboto', sans-serif">Roboto (حديث)</option>
                                    <option value="'Arial', sans-serif">Arial (قياسي)</option>
                                </select>
                            </div>

                            <!-- Global Font Size -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">حجم الخط العام الافتراضي (لكل المقاسات):</label>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <input type="number" id="grid-setting-font-size" min="8" max="42" value="16" oninput="const v=parseInt(this.value)||14; const r=document.getElementById('grid-setting-font-size-range'); if(r)r.value=v; if(window.onGlobalGridFontSizeLive)window.onGlobalGridFontSizeLive(v);" style="width:60px; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:5px; text-align:center; font-weight:bold;">
                                    <input type="range" id="grid-setting-font-size-range" min="8" max="42" value="16" oninput="const v=parseInt(this.value)||14; document.getElementById('grid-setting-font-size').value=v; if(window.onGlobalGridFontSizeLive)window.onGlobalGridFontSizeLive(v);" style="flex:1; cursor:pointer; accent-color:#38bdf8;">
                                </div>
                            </div>

                            <!-- Global Font Weight -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">سُمك الخط العام الافتراضي:</label>
                                <select id="grid-setting-font-weight" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:6px 10px; font-size:0.85rem;">
                                    <option value="normal">عادي (Normal - 400)</option>
                                    <option value="bold">عريض (Bold - 700)</option>
                                    <option value="800">سميك جداً (Extra Bold - 800)</option>
                                    <option value="300">خفيف (Light - 300)</option>
                                </select>
                            </div>

                            <!-- Global Font Color -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">لون الخط العام الافتراضي:</label>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <input type="color" id="grid-setting-font-color" value="#ffffff" style="border:none; width:38px; height:30px; cursor:pointer; background:#0f172a; border-radius:6px;">
                                    <button type="button" onclick="document.getElementById('grid-setting-font-color').value='#ffffff'" style="background:rgba(255,255,255,0.1); border:none; border-radius:6px; padding:5px 8px; font-size:0.75rem; color:#fff; cursor:pointer;">أبيض</button>
                                </div>
                            </div>

                            <!-- Global Align -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">المحاذاة العامة الافتراضية:</label>
                                <select id="grid-setting-align" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:6px 10px; font-size:0.85rem;">
                                    <option value="center">وسط (Center)</option>
                                    <option value="right">يمين (Right)</option>
                                    <option value="left">يسار (Left)</option>
                                </select>
                            </div>

                            <!-- Global Thousands Separator -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">الفاصلة الألفية العامة:</label>
                                <select id="grid-setting-thousand-sep" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:6px 10px; font-size:0.85rem;">
                                    <option value=",">ظهور الفاصلة (1,234.50)</option>
                                    <option value="none">إخفاء الفاصلة (1234.50)</option>
                                </select>
                            </div>

                            <!-- Global Decimals -->
                            <div>
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">الخانات العشرية العامة:</label>
                                <select id="grid-setting-decimals" style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:6px 10px; font-size:0.85rem;">
                                    <option value="0">0 (بدون كسور: 120)</option>
                                    <option value="1">1 (خانة واحدة: 120.5)</option>
                                    <option value="2" selected>2 (خانتان: 120.50)</option>
                                    <option value="3">3 (ثلاث خانات: 120.500)</option>
                                    <option value="4">4 (أربع خانات: 120.5000)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer / Actions -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; border-top:1px solid rgba(255,255,255,0.12); padding-top:12px;">
                <button type="button" onclick="quickResetGrid()" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); border-radius:8px; padding:8px 16px; font-weight:bold; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    🔄 استعادة الافتراضي
                </button>
                
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="previewGridSettings()" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); border-radius:8px; padding:8px 18px; font-weight:bold; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        👁️ تجربة فورية
                    </button>
                    <button type="button" onclick="closeGridSettingsModal()" style="background:rgba(255,255,255,0.1); color:#fff; border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:8px 18px; cursor:pointer;">
                        إلغاء
                    </button>
                    <button type="button" onclick="saveAndApplyGridSettingsToDB()" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; border-radius:8px; padding:8px 24px; font-weight:900; font-size:1.05rem; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,0.4); display:flex; align-items:center; gap:8px;">
                        💾 حفظ نهائي في قاعدة البيانات
                    </button>
                </div>
            </div>
        </div>
    </div>

<script src="js/screens/direct-sales-j2.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j2.js') ?: '2027' ?>"></script>
    
    <!-- Invoice Deletion Modal -->
    <div id="invoice-delete-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85);  z-index:999999; justify-content:center; align-items:center; direction:rtl; font-family:'Tajawal', sans-serif;">
        <div class="glass-panel" style="width:90%; max-width:600px; padding:30px; border-radius:24px; border:1px solid rgba(255,255,255,0.1); background:rgba(15,23,42,0.95); box-shadow:0 25px 50px -12px rgba(0,0,0,0.8);">
            
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                <div style="display:flex; gap:15px; align-items:center;">
                    <div id="inv-del-modal-icon-wrap" style="width:60px; height:60px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:2rem; background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); box-shadow:0 0 20px rgba(56,189,248,0.2);">
                        <span id="inv-del-modal-icon">🔍</span>
                    </div>
                    <div>
                        <h2 id="inv-del-modal-title" style="margin:0 0 5px 0; color:#fff; font-size:1.4rem; font-weight:900;">جاري فحص ارتباطات السند...</h2>
                        <p id="inv-del-modal-subtitle" style="margin:0; color:#94a3b8; font-size:0.9rem;">يتم التحقق من الدفعات والقيود المحاسبية</p>
                    </div>
                </div>
            </div>

            <div style="background:rgba(0,0,0,0.4); border-radius:16px; padding:15px; margin-bottom:20px; border:1px solid rgba(255,255,255,0.05);">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span style="color:#94a3b8; font-size:0.9rem;">رقم الفاتورة:</span>
                    <strong id="inv-del-target-no" style="color:#38bdf8; font-size:1.1rem;">-</strong>
                </div>
            </div>

            <div id="inv-del-status-box" style="border-radius:12px; padding:15px; margin-bottom:20px; border:1px solid rgba(56,189,248,0.2); background:rgba(56,189,248,0.08);">
                <div style="display:flex; align-items:center; justify-content:center; gap:12px; color:#38bdf8; font-weight:800; font-size:1.1rem;">
                    <span style="animation:spin 1s infinite linear;">⏳</span>
                    <span>جاري فحص قاعدة البيانات والسجلات...</span>
                </div>
            </div>

            <div id="inv-del-vouchers-container" style="display:none; margin-bottom:25px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h4 id="inv-del-vouchers-header-label" style="margin:0; color:#e2e8f0; font-size:1rem;">📋 السجلات المرتبطة بهذا السند:</h4>
                    <span id="inv-del-vouchers-count" style="background:rgba(239,68,68,0.2); color:#fca5a5; padding:4px 10px; border-radius:12px; font-weight:bold; font-size:0.85rem;">0 سند</span>
                </div>
                <div id="inv-del-vouchers-list" style="max-height:200px; overflow-y:auto; padding-right:5px; display:flex; flex-direction:column; gap:8px;">
                    <!-- Vouchers will be injected here -->
                </div>
            </div>

            <div id="inv-del-modal-footer" style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;">
                <button type="button" onclick="closeInvoiceDeleteModal()" class="btn-action" style="padding:8px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; cursor:pointer;">إلغاء</button>
            </div>
            
        </div>
    </div>
    
    <!-- Beautiful Modern Delivery Confirmation Modal -->
    <div id="delivery-confirmation-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85);  z-index:999999; justify-content:center; align-items:center; direction:rtl; font-family:'Tajawal', sans-serif;">
        <div class="glass-panel" style="width:90%; max-width:560px; padding:28px; border-radius:24px; border:1.5px solid rgba(16,185,129,0.4); background:linear-gradient(145deg, rgba(15,23,42,0.98), rgba(6,78,59,0.25)); box-shadow:0 25px 60px -12px rgba(0,0,0,0.9), 0 0 30px rgba(16,185,129,0.15);">
            
            <!-- Header with Emerald Badge Icon -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                <div style="display:flex; gap:16px; align-items:center;">
                    <div style="width:64px; height:64px; border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:2.2rem; background:linear-gradient(135deg, rgba(16,185,129,0.25), rgba(5,150,105,0.4)); border:2px solid rgba(16,185,129,0.6); box-shadow:0 0 25px rgba(16,185,129,0.4);">
                        <span>📦</span>
                    </div>
                    <div>
                        <h2 style="margin:0 0 4px 0; color:#ffffff; font-size:1.45rem; font-weight:900; letter-spacing:0.5px;">تأكيد تسليم وصرف البضاعة</h2>
                        <p style="margin:0; color:#a7f3d0; font-size:0.92rem; font-weight:600;">اعتماد تسليم الفاتورة وخصم الكميات من المخزن المحدد</p>
                    </div>
                </div>
                <button type="button" onclick="closeDeliveryConfirmationModal()" style="background:transparent; border:none; color:#94a3b8; font-size:1.5rem; cursor:pointer; padding:4px 8px; line-height:1; transition:0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">✕</button>
            </div>

            <!-- Invoice Details Card -->
            <div style="background:rgba(0,0,0,0.45); border-radius:16px; padding:18px; margin-bottom:18px; border:1px solid rgba(255,255,255,0.08); display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:10px;">
                    <span style="color:#94a3b8; font-size:0.92rem; font-weight:700;">رقم الفاتورة:</span>
                    <strong id="del-modal-inv-no" style="color:#38bdf8; font-size:1.2rem; font-family:'Outfit',sans-serif; letter-spacing:1px; background:rgba(56,189,248,0.12); padding:2px 10px; border-radius:8px; border:1px solid rgba(56,189,248,0.3);">-</strong>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:10px;">
                    <span style="color:#94a3b8; font-size:0.92rem; font-weight:700;">العميل:</span>
                    <strong id="del-modal-cust-name" style="color:#6ee7b7; font-size:1.15rem; font-weight:800;">-</strong>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:10px;">
                    <span style="color:#94a3b8; font-size:0.92rem; font-weight:700;">مخزن الصرف:</span>
                    <span id="del-modal-wh-name" style="color:#fbbf24; font-size:1.05rem; font-weight:800; background:rgba(245,158,11,0.12); padding:2px 10px; border-radius:8px; border:1px solid rgba(245,158,11,0.3);">-</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#94a3b8; font-size:0.92rem; font-weight:700;">إجمالي المبلغ:</span>
                    <strong id="del-modal-amount" style="color:#ffffff; font-size:1.25rem; font-weight:900; font-family:'Cairo',sans-serif;">0 SDG</strong>
                </div>
            </div>

            <!-- Action Notice -->
            <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); border-radius:12px; padding:12px 16px; margin-bottom:22px; display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.3rem;">✨</span>
                <span style="color:#d1fae5; font-size:0.9rem; font-weight:700; line-height:1.5;">
                    سيتم خصم كميات البضاعة تلقائياً من رصيد المخزن المذكور وتحديث حالة الفاتورة كـ <strong>مسلّمة ومعتمدة</strong>.
                </span>
            </div>

            <!-- Footer Buttons -->
            <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid rgba(255,255,255,0.1); padding-top:18px;">
                <button type="button" onclick="closeDeliveryConfirmationModal()" style="padding:10px 22px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#cbd5e1; font-weight:800; font-size:0.95rem; cursor:pointer; transition:0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                    ❌ تراجع / إلغاء
                </button>
                <button type="button" onclick="confirmExecuteDeliveryAction()" style="padding:10px 28px; border-radius:10px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:1px solid rgba(16,212,143,0.5); color:#ffffff; font-weight:900; font-size:1.05rem; cursor:pointer; box-shadow:0 6px 20px rgba(16,185,129,0.4); display:flex; align-items:center; gap:8px; transition:0.25s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(16,185,129,0.6)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 20px rgba(16,185,129,0.4)'">
                    <span>📦 تأكيد التسليم والصرف الآن</span>
                </button>
            </div>
            
        </div>
    </div>
    
    <!-- Financial OCR Modal & Script -->
    <?php include 'includes/financial-ocr-modal.php'; ?>
    <script defer src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
    <script src="js/screens/direct-sales-inline-22.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-22.js') ?: '2027' ?>"></script>
    <!-- ══════════════════════════════════════════════════════════════════════════════ -->
    <!-- LIGHTWEIGHT, ULTRA-SMOOTH SALES DELIVERY HUB (GDN) & UNIT EQUIVALENCE ENGINE   -->
    <!-- ══════════════════════════════════════════════════════════════════════════════ -->
<link rel="stylesheet" id="world-class-sales-delivery-styles" href="css/screens/direct-sales-s4.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-s4.css') ?: '2027' ?>">

    <!-- QR Scanner, Continuous Zero-Click Stream & Mobile-to-Laptop Bridge Modal -->
    <div id="gdn-qr-scanner-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88);  z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#0f172a; color:#f8fafc; width:100%; max-width:640px; border-radius:26px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.75); border:2px solid #10b981;">
            <div style="background:linear-gradient(135deg, #064e3b, #0284c7); padding:16px 22px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:1.8rem;">📱</span>
                    <div>
                        <h3 style="margin:0; font-size:1.2rem; font-weight:900;">ربط كاميرا الجوال والمسح المتتابع الفوري (Zero-Click QR)</h3>
                        <p style="margin:3px 0 0; font-size:0.8rem; color:#a7f3d0; font-weight:700;">مرّر 10 أذونات واحداً تلو الآخر أمام الكاميرا — يستلم ويخصم تلقائياً بدون ضغط أي زر!</p>
                    </div>
                </div>
                <button onclick="closeDeliveryQrScannerModal()" style="background:rgba(255,255,255,0.18); border:none; color:#fff; width:38px; height:38px; border-radius:12px; font-size:1.2rem; font-weight:900; cursor:pointer;">✕</button>
            </div>
            <div style="padding:18px; display:flex; flex-direction:column; gap:12px; max-height:84vh; overflow-y:auto;">
                <!-- Mode Switcher: Mobile Link vs Direct Camera -->
                <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; justify-content:space-between; background:rgba(16,185,129,0.12); padding:10px 14px; border-radius:14px; border:1px solid rgba(52,211,153,0.35);">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:900; color:#6ee7b7; cursor:pointer; font-size:0.88rem;">
                        <input type="checkbox" id="gdn-qr-auto-deduct-chk" checked style="width:18px; height:18px; accent-color:#10b981;">
                        <span>⚡ استلام وخصم متتابع فوري بمجرد قراءة الـ QR (بدون ضغط أي زر)</span>
                    </label>
                    <span id="gdn-modal-scan-counter" style="background:#065f46; color:#fff; padding:3px 10px; border-radius:99px; font-size:0.78rem; font-weight:900;">✅ المستلم: 0 | 🚫 الملغي: 0</span>
                </div>

                <!-- Live Status Banner inside Scanner Modal -->
                <div id="gdn-scanner-live-banner" style="padding:12px 14px; border-radius:14px; background:rgba(30,41,59,0.9); border:1.5px solid rgba(56,189,248,0.4); font-weight:900; font-size:0.94rem; text-align:center; color:#e2e8f0;">
                    📷 الكاميرا وقناة ربط الجوال نشطة — مرّر باركود الإذن الأول للاستلام والخصم الفوري...
                </div>

                <!-- Two Columns: Direct Camera Stream + Mobile Phone Pairing QR -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:12px;">
                    <!-- Direct Camera Viewport -->
                    <div id="gdn-camera-viewport" style="position:relative; width:100%; height:235px; background:#020617; border-radius:18px; overflow:hidden; display:flex; align-items:center; justify-content:center; border:3px solid #10b981;">
                        <video id="gdn-qr-video" playsinline muted style="width:100%; height:100%; object-fit:cover;"></video>
                        <div style="position:absolute; width:170px; height:170px; border:3px dashed #34d399; border-radius:20px; box-shadow:0 0 0 9999px rgba(0,0,0,0.38); pointer-events:none;"></div>
                        <div id="gdn-camera-status" style="position:absolute; bottom:8px; left:8px; right:8px; background:rgba(0,0,0,0.75); color:#6ee7b7; padding:5px 10px; border-radius:8px; font-size:0.76rem; font-weight:800; text-align:center;">جاري تشغيل الكاميرا المباشرة...</div>
                    </div>

                    <!-- Mobile Phone Direct Link QR -->
                    <div style="background:rgba(15,23,42,0.95); border:1.5px solid rgba(56,189,248,0.4); border-radius:18px; padding:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; gap:6px;">
                        <div style="font-size:0.82rem; font-weight:900; color:#38bdf8;">📱 ربط كاميرا الجوال باللابتوب مباشرة</div>
                        <div style="background:#fff; padding:8px; border-radius:12px; border:2px solid #10b981;">
                            <img id="gdn-mobile-bridge-qr-img" src="" alt="Mobile Bridge QR" style="width:135px; height:135px; display:block;">
                        </div>
                        <div style="font-size:0.73rem; color:#cbd5e1; font-weight:700;">امسح هذا الكود بجوالك مرة واحدة ليتحول الجوال إلى عدسة ليزر مربوطة باللابتوب</div>
                        <a id="gdn-mobile-bridge-link" href="mobile-qr-gun.php" target="_blank" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #059669, #0284c7); color:#fff; text-decoration:none; padding:5px 12px; font-size:0.76rem; width:100%; justify-content:center;">🚀 فتح شاشة عدسة الجوال</a>
                    </div>
                </div>

                <!-- Manual / Wireless Barcode Gun / Camera Photo Fallback -->
                <div style="display:flex; gap:8px;">
                    <input type="text" id="gdn-manual-qr-input" class="gdn-borderless-input" placeholder="أو امسح بالـ Scanner أو اكتب رقم الإذن هنا ثم Enter للاستلام المتتابع..." style="flex:1; font-size:0.95rem;" onkeydown="if(event.key==='Enter'){event.preventDefault(); processScannedDeliveryCode(this.value, true);}">
                    <button onclick="processScannedDeliveryCode(document.getElementById('gdn-manual-qr-input').value, true)" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff;">
                        <span class="gdn-icon">⚡</span> استلام فوري
                    </button>
                </div>

                <!-- Live Continuous Feed Log -->
                <div id="gdn-scanner-live-feed" style="background:#020617; border:1px solid rgba(148,163,184,0.25); border-radius:12px; padding:8px 12px; max-height:110px; overflow-y:auto; font-size:0.8rem; color:#94a3b8;">
                    📋 سجل الأذونات الممسوحة بالتتابع سيظهر هنا لحظياً...
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                    <label class="gdn-kinetic-btn" style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8; flex:1; justify-content:center;">
                        <span class="gdn-icon">📸</span> التقط صورة باركود بالكاميرا
                        <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="handleQrImageFileCapture(this)">
                    </label>
                    <button onclick="closeDeliveryQrScannerModal()" class="gdn-kinetic-btn" style="background:rgba(239,68,68,0.18); border:1px solid rgba(239,68,68,0.4); color:#fca5a5;">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Preview Modal for Invoice (عرض باركود الفاتورة للمخزنجي ليصوره بالموبايل) -->
    <div id="gdn-qr-display-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.82);  z-index:99998; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#fff; width:100%; max-width:420px; border-radius:26px; padding:24px; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.45); border:3px solid #10b981;">
            <h3 id="gdn-qr-display-title" style="margin:0 0 6px; font-size:1.3rem; font-weight:900; color:#064e3b;">باركود التسليم والخصم الفوري</h3>
            <p id="gdn-qr-display-sub" style="margin:0 0 16px; font-size:0.88rem; color:#64748b; font-weight:700;">امسح هذا الرمز بكاميرا موبايل المخزنجي للخصم المباشر من المخزن</p>
            <div style="background:#f8fafc; padding:18px; border-radius:20px; display:inline-block; border:2px dashed #10b981; margin-bottom:16px;">
                <img id="gdn-qr-display-img" src="" alt="QR Code" style="width:230px; height:230px; display:block;">
            </div>
            <div id="gdn-qr-display-items" style="text-align:right; background:#f0fdf4; padding:12px; border-radius:14px; font-size:0.85rem; font-weight:800; color:#065f46; margin-bottom:16px; max-height:130px; overflow-y:auto;"></div>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button id="gdn-qr-instant-btn" class="gdn-kinetic-btn" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; flex:1;">
                    <span class="gdn-icon">⚡</span> خصم وتسليم الآن
                </button>
                <button onclick="document.getElementById('gdn-qr-display-modal').style.display='none'" class="gdn-kinetic-btn" style="background:#f1f5f9; color:#334155;">إغلاق</button>
            </div>
        </div>
    </div>

<script src="js/screens/direct-sales-j3.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j3.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="pur-clean-theme-style" href="css/screens/direct-sales-s5.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-s5.css') ?: '2027' ?>">
    <script id="sales-fin-strip-and-tfoot-organizer" src="js/screens/direct-sales-inline-23.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-23.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution1-engine-style" href="css/screens/direct-sales-inline-17.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-17.css') ?: '2027' ?>">

<script id="split-constitution1-engine-script" src="js/screens/direct-sales-inline-24.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-24.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution2-silk-style" href="css/screens/direct-sales-inline-18.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-18.css') ?: '2027' ?>">
<script id="split-constitution2-silk-engine" src="js/screens/direct-sales-inline-25.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-25.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution3-dna-style" href="css/screens/direct-sales-inline-19.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-19.css') ?: '2027' ?>">

<script id="split-constitution3-dna-engine" src="js/screens/direct-sales-inline-26.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-26.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution4-microhud-style" href="css/screens/direct-sales-inline-20.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-20.css') ?: '2027' ?>">
<style id="split-constitution5-spotlight-style">
    /* الدستور الخامس (5 من 14): البؤرة الضوئية المركزة (Spotlight) على العنصر المشروح */
    #msg-live-target-ring {
        border: 3px solid #fbbf24 !important;
        box-shadow: 0 0 0 5px rgba(251, 191, 36, 0.32), 0 0 32px rgba(56, 189, 248, 0.75) !important;
        border-radius: 12px !important;
        transition: top 0.22s cubic-bezier(0.22, 1, 0.36, 1), left 0.22s cubic-bezier(0.22, 1, 0.36, 1), width 0.22s ease, height 0.22s ease !important;
        pointer-events: none !important;
    }
</style>
<script id="split-constitution5-dynamic-guide-engine" src="js/screens/direct-sales-inline-27.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-27.js') ?: '2027' ?>"></script>
<script id="split-constitution6-flash-sync-engine" src="js/screens/direct-sales-inline-28.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-28.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution7-optical-style" href="css/screens/direct-sales-inline-21.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-21.css') ?: '2027' ?>">

<script id="split-constitution7-foundations-engine" src="js/screens/direct-sales-inline-29.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-29.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution8-cognitive-style" href="css/screens/direct-sales-inline-22.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-22.css') ?: '2027' ?>">

<script id="split-constitution8-cognitive-engine" src="js/screens/direct-sales-j4.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j4.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution9-wms-style" href="css/screens/direct-sales-inline-23.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-23.css') ?: '2027' ?>">

<script id="split-constitution9-wms-engine" src="js/screens/direct-sales-inline-30.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-30.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution10-commercial-style" href="css/screens/direct-sales-inline-24.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-24.css') ?: '2027' ?>">

<script id="split-constitution10-commercial-engine" src="js/screens/direct-sales-j5.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j5.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution11-banking-style" href="css/screens/direct-sales-inline-25.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-25.css') ?: '2027' ?>">

<script id="split-constitution11-banking-engine" src="js/screens/direct-sales-j6.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j6.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution12-security-style" href="css/screens/direct-sales-inline-26.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-26.css') ?: '2027' ?>">

<script id="split-constitution12-security-engine" src="js/screens/direct-sales-inline-31.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-31.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution13-cyber-style" href="css/screens/direct-sales-inline-27.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-27.css') ?: '2027' ?>">

<script id="split-constitution13-cyber-engine" src="js/screens/direct-sales-j7.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j7.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution14-reports-mobile-style" href="css/screens/direct-sales-inline-28.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-28.css') ?: '2027' ?>">

<script id="split-constitution14-reports-mobile-engine" src="js/screens/direct-sales-j8.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j8.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution15-zero-error-audit-style" href="css/screens/direct-sales-inline-29.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-29.css') ?: '2027' ?>">

<script id="split-constitution15-zero-error-audit-engine" src="js/screens/direct-sales-inline-32.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-inline-32.js') ?: '2027' ?>"></script>
<link rel="stylesheet" id="split-constitution16-zero-error-master-style" href="css/screens/direct-sales-inline-30.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-inline-30.css') ?: '2027' ?>">

<script id="split-constitution16-zero-error-master-engine" src="js/screens/direct-sales-j9.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j9.js') ?: '2027' ?>"></script>
<script src="js/split-purchase-constitution-2027.js?v=2027_clean_v2"></script>
<script>window.DB_NEXT_SALES_FIN_VOUCHER_NO = <?= json_encode($salNextFinNo) ?>;</script>
<?php if (file_exists(__DIR__ . '/includes/inbound-bankak-drawer.php')) include_once __DIR__ . '/includes/inbound-bankak-drawer.php'; ?>
<link rel="stylesheet" id="sales-cloned-purchase-card-2027" href="css/screens/sales-cloned-purchase-card.css?v=<?= @filemtime(__DIR__ . '/css/screens/sales-cloned-purchase-card.css') ?: '2027' ?>">
<link rel="stylesheet" id="sales-payment-panel-2027" href="css/screens/sales-payment-panel-2027.css?v=<?= @filemtime(__DIR__ . '/css/screens/sales-payment-panel-2027.css') ?: '2027' ?>">
<link rel="stylesheet" href="css/pay-amount-typing-2027.css?v=<?= @filemtime(__DIR__ . '/css/pay-amount-typing-2027.css') ?: '2027' ?>">
</body>
</html>