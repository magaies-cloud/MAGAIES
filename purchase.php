<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php 
$page_title = 'مشتريات'; 
$db_purchase_grid_config = null;
$preloadedUnitsJson = '[]';
$preloadedItemsJson = '[]';
$purNextFinNo = 'E' . date('ym') . '0001';
try {
    require_once __DIR__ . '/api/db.php';
    if (file_exists(__DIR__ . '/db_bridge.php')) require_once __DIR__ . '/db_bridge.php';
    $systemUsdRate = function_exists('getSystemUsdExchangeRate') ? getSystemUsdExchangeRate($pdo ?? null) : 5999.0;
    if (isset($pdo)) {
        if (function_exists('getDBConnection')) { $pdo = getDBConnection(); }
        $activePurchaseTenantId = function_exists('resolveCurrentTenantId') ? resolveCurrentTenantId($pdo ?? null) : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 12);
        try {
            $purFinPrefix = 'E' . date('ym');
            $purMaxFinSeq = 0;
            $stFinTop = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ?");
            $stFinTop->execute([$activePurchaseTenantId ?: 12]);
            while ($rFinTop = $stFinTop->fetch(PDO::FETCH_ASSOC)) {
                $dFinTop = json_decode($rFinTop['data'], true);
                if (!empty($dFinTop['deleted'])) continue;
                $idFinTop = isset($dFinTop['voucherNo']) && $dFinTop['voucherNo'] !== '' ? (string)$dFinTop['voucherNo'] : (isset($dFinTop['id']) ? (string)$dFinTop['id'] : '');
                if (strpos($idFinTop, $purFinPrefix) === 0) {
                    $sqTop = (int)substr($idFinTop, strlen($purFinPrefix));
                    if ($sqTop > $purMaxFinSeq && $sqTop < 999999) $purMaxFinSeq = $sqTop;
                }
            }
            $purNextFinNo = $purFinPrefix . str_pad($purMaxFinSeq + 1, 4, '0', STR_PAD_LEFT);
        } catch (Throwable $_ef) {}
        $tGridKey = 'tenant_' . $activePurchaseTenantId . '_grid_config_purchase_grid';
        $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN (" . $pdo->quote($tGridKey) . ", 'grid_config_purchase_grid') ORDER BY (setting_key = " . $pdo->quote($tGridKey) . ") DESC LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['setting_value'])) {
            $db_purchase_grid_config = json_decode($row['setting_value'], true);
        }

        if (function_exists('getDBConnection')) { $pdo = getDBConnection(); }
        $activePurchaseTenantId = function_exists('resolveCurrentTenantId') ? resolveCurrentTenantId($pdo ?? null) : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 1);
        $tUnitsKey = 'tenant_' . $activePurchaseTenantId . '_erp_global_units_v1';
        $stmtUnits = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN (?, 'erp_global_units_v1') ORDER BY (setting_key = ?) DESC LIMIT 1");
        $stmtUnits->execute([$tUnitsKey, $tUnitsKey]);
        $uVal = $stmtUnits ? $stmtUnits->fetchColumn() : null;
        if ($uVal) $preloadedUnitsJson = $uVal;

        $stmtItems = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtItems->execute([$activePurchaseTenantId ?? 1]);
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
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="has-sidebar">
<script src="js/lang.js"></script>
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
        try { localStorage.removeItem('erp_items_v1'); } catch(_e) {}
        if (tId) localStorage.setItem('tenant_' + tId + '_erp_items_v1', iStr);
        else localStorage.setItem('erp_items_v1', iStr);
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

<script src="js/screens/purchase-inline-20.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-inline-20.js') ?: '2027' ?>"></script>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشتريات | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=20261029_fast">
    <link rel="stylesheet" href="css/theme.css?v=20261101_crystal">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css?v=20261101_crystal">
    <link rel="stylesheet" href="css/print-invoice.css">
    <link rel="stylesheet" href="css/purchase-module-v2.css?v=<?php echo file_exists('css/purchase-module-v2.css') ? filemtime('css/purchase-module-v2.css') : '20261104_v2'; ?>">
<link rel="stylesheet" href="css/screens/purchase-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-s1.css') ?: '2027' ?>">

    <link rel="stylesheet" href="css/screens/purchase-inline-10.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-10.css') ?: '2027' ?>">
    <link rel="stylesheet" href="css/screens/purchase-inline-11.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-11.css') ?: '2027' ?>">
    <link rel="stylesheet" href="css/screens/purchase-inline-12.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-12.css') ?: '2027' ?>">

<link rel="stylesheet" href="css/screens/purchase-s2.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-s2.css') ?: '2027' ?>">

    <!-- Server-Side Pre-Rendered Grid Configuration from MySQL Database -->
    <script>
        window.CurrentGridConfig = <?php echo json_encode($db_purchase_grid_config, JSON_UNESCAPED_UNICODE) ?: 'null'; ?>;
    </script>
    <style id="dynamic-pos-grid-style">
    <?php
    if ($db_purchase_grid_config && isset($db_purchase_grid_config['columns'])) {
        $cfg = $db_purchase_grid_config;
        $fFamily = $cfg['fontFamily'] ?? "'Cairo', sans-serif";
        $fSize = ($cfg['fontSize'] ?? 16) . 'px';
        $fWeight = $cfg['fontWeight'] ?? 'normal';
        $fColorRaw = $cfg['fontColor'] ?? '#ffffff'; $fColor = (strtolower(trim($fColorRaw)) === '#ffffff' || strtolower(trim($fColorRaw)) === '#fff') ? 'var(--text-primary, #ffffff)' : $fColorRaw;
        $tAlign = $cfg['align'] ?? 'center';
        
        echo "#pos-grid, #pos-grid *, #pos-grid thead th, #pos-grid tbody td, #pos-grid tbody td input, #pos-grid tbody td select, #pos-grid tbody td span { font-family: " . $fFamily . " !important; }\n";
        echo "#pos-grid thead th { font-size: " . $fSize . " !important; text-align: " . $tAlign . " !important; }\n";
        echo "#pos-grid tbody td { font-size: " . $fSize . " !important; font-weight: " . $fWeight . " !important; color: " . $fColor . " !important; text-align: " . $tAlign . " !important; }\n";
        echo "#pos-grid tbody td input, #pos-grid tbody td select, #pos-grid tbody td span:not(.btn-icon):not(.add-row-btn), #pos-grid tbody td div:not(.custom-dropdown-list), #pos-grid tbody td .g-input, #pos-grid tbody td .g-select, #pos-grid tbody td .g-readonly { font-size: " . $fSize . " !important; font-weight: " . $fWeight . " !important; color: " . $fColor . " !important; text-align: " . $tAlign . " !important; }\n";
        
        foreach ($cfg['columns'] as $col) {
            $colId = $col['id'];
            $colVisible = !isset($col['visible']) || $col['visible'];
            $colColorRaw = $col['color'] ?? $fColorRaw; $colColor = (strtolower(trim($colColorRaw)) === '#ffffff' || strtolower(trim($colColorRaw)) === '#fff') ? 'var(--text-primary, #ffffff)' : $colColorRaw;
            $colSize = isset($col['fontSize']) ? ($col['fontSize'] . 'px') : $fSize;
            $colWeight = $col['fontWeight'] ?? $fWeight;
            $colAlign = $col['align'] ?? $tAlign;
            $colWidth = (!empty($col['width']) && $col['width'] !== 'auto') ? $col['width'] : (($colId === 'item' || $colId === 'col_item') ? 'auto' : '100px');
            
            if (!$colVisible) {
                echo "#pos-grid [data-col-id='" . $colId . "'] { display: none !important; }\n";
            } else {
                $wStyle = ($colWidth !== 'auto') ? ("width: " . $colWidth . "; min-width: 0 !important; max-width: 100% !important;") : "width: auto; min-width: 140px;";
                $flexJustify = ($colAlign === 'center') ? 'center' : (($colAlign === 'right') ? 'flex-start' : 'flex-end');
                echo "#pos-grid [data-col-id='" . $colId . "'] { display: table-cell !important; " . $wStyle . " text-align: " . $colAlign . " !important; color: " . $colColor . " !important; font-size: " . $colSize . " !important; font-weight: " . $colWeight . " !important; }\n";
                echo "#pos-grid th[data-col-id='" . $colId . "'] { color: #38bdf8 !important; font-weight: bold !important; text-align: center !important; font-size: " . $colSize . " !important; " . $wStyle . " }\n";
                echo "#pos-grid td[data-col-id='" . $colId . "'] { text-align: " . $colAlign . " !important; color: " . $colColor . " !important; font-size: " . $colSize . " !important; font-weight: " . $colWeight . " !important; " . $wStyle . " }\n";
                echo "#pos-grid td[data-col-id='" . $colId . "'] input, #pos-grid td[data-col-id='" . $colId . "'] select, #pos-grid td[data-col-id='" . $colId . "'] span:not(.btn-icon):not(.add-row-btn), #pos-grid td[data-col-id='" . $colId . "'] div:not(.custom-dropdown-list), #pos-grid td[data-col-id='" . $colId . "'] .g-input, #pos-grid td[data-col-id='" . $colId . "'] .g-select, #pos-grid td[data-col-id='" . $colId . "'] .g-readonly, #pos-grid td[data-col-id='" . $colId . "'] .total-val, #pos-grid td[data-col-id='" . $colId . "'] .avail-val { text-align: " . $colAlign . " !important; color: " . $colColor . " !important; font-size: " . $colSize . " !important; font-weight: " . $colWeight . " !important; }\n";
                echo "#pos-grid td[data-col-id='" . $colId . "'] > div { display: flex; justify-content: " . $flexJustify . " !important; align-items: center; }\n";
            }
        }
    }
    ?>
    </style>

    <!-- ═══ ULTRA-CONTRAST TYPOGRAPHY, FAINT HINTS/ZEROS & CLEAR REGIONS ENGINE (2027) ═══ -->
<link rel="stylesheet" id="invoice-ultra-contrast-2027" href="css/screens/purchase-s3.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-s3.css') ?: '2027' ?>">
    <script id="invoice-faint-zero-hint-engine" src="js/screens/purchase-inline-21.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-inline-21.js') ?: '2027' ?>"></script>
</head>
<body class="has-sidebar">
    <div class="toast-container" id="toast-container"></div>

    <!-- ═══ Add Supplier Modal ═══ -->
    <div class="modal-overlay" id="add-customer-modal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>➕ إضافة مورد جديد</h3>
                <button class="modal-close" onclick="closeModal('add-customer-modal')" style="color:#fff;">✕</button>
            </div>
            <div class="modal-body">
                <div class="v-form-group">
                    <label class="v-label">اسم المورد</label>
                    <input type="text" class="v-input ctrl-input" id="new-cust-name" placeholder="اسم المورد...">
                </div>
                <div class="v-form-group">
                    <label class="v-label">رقم الهاتف</label>
                    <input type="tel" class="v-input ctrl-input" id="new-cust-phone" placeholder="09XXXXXXXX">
                </div>
                <div class="v-form-group">
                    <label class="v-label">الرصيد الافتتاحي (له علينا + / لنا عليه -)</label>
                    <input type="text" inputmode="decimal" class="v-input ctrl-input" id="new-cust-opening" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
                <div class="v-form-group">
                    <label class="v-label">حد الائتمان</label>
                    <input type="text" inputmode="decimal" class="v-input ctrl-input" id="new-cust-credit" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-action btn-draft" style="width:auto; padding:8px 16px;" onclick="closeModal('add-customer-modal')">إلغاء</button>
                <button class="btn-action btn-submit" style="width:auto; padding:8px 16px;" onclick="addNewCustomer()">💾 حفظ المورد</button>
            </div>
        </div>
    </div>

    <!-- ═══ VELVET NAV ═══ -->
<div class="ambient-bg"></div>
<div class="dash-main" id="pur-proportional-root">
    
        <?php $header_theme_class = 'header-theme-purchase'; ?>
        <?php include 'includes/header.php'; ?>

    <?php $purDirectEditorOnLoad = (isset($_GET['action']) && in_array($_GET['action'], ['new', 'direct', 'edit', 'preliminary'], true)) || isset($_GET['id']) || isset($_GET['inv']); ?>
    <!-- ═══ PURCHASE HUB VIEW ═══ -->
    <div id="purchase-hub-view" style="display: <?= $purDirectEditorOnLoad ? 'none' : 'flex' ?>; flex-direction: column; flex: 1; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 10px 0; max-width: 100%; margin: 0; overflow-y: auto;">
        
        <!-- Action Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">

            <!-- Direct Purchase Invoice -->
            <button onclick="openPurchaseEditor('direct')" class="hub-action-card card-orange">
                <div class="text-content">
                    <div class="title">فاتورة مشتريات</div>
                </div>
            </button>

            <!-- Goods Receipt Note -->
            <button onclick="window.location.href='purchase-receipt.php'" class="hub-action-card card-blue">
                <div class="text-content">
                    <div class="title">استلام بضاعة</div>
                </div>
            </button>

            <!-- Unified Smart Logistics Portal -->
            <button onclick="window.location.href='smart-logistics.php?mode=purchases'" class="hub-action-card card-teal" style="border-color: rgba(56,189,248,0.55); box-shadow: 0 0 22px rgba(14,165,233,0.3);">
                <div class="text-content">
                    <div class="title">بوابة استلام المشتريات</div>
                </div>
            </button>



            <!-- Purchase Return -->
            <button onclick="window.location.href='purchase-return.php'" class="hub-action-card card-pink">
                <div class="text-content">
                    <div class="title">استرداد بضاعة</div>
                </div>
            </button>

            <!-- Proforma Invoice -->
            <button onclick="openPurchaseEditor('proforma')" class="hub-action-card card-purple">
                <div class="text-content">
                    <div class="title">فاتورة مبدئية</div>
                </div>
            </button>

            <!-- Supplier Reliability 360 Card -->
            <button onclick="window.location.href='supplier_reliability.php'" class="hub-action-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.35)); border: 1px solid rgba(251, 191, 36, 0.5); cursor: pointer;">
                <div class="text-content">
                    <div class="title" style="color: #fbbf24; font-weight: 800;">اعتمادية الموردين 360°</div>
                </div>
            </button>

            <!-- OCR Scanner Card -->
            <button onclick="triggerInvoiceOCR()" class="hub-action-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.35)); border: 1px solid rgba(52, 211, 153, 0.5); cursor: pointer;">
                <div class="text-content">
                    <div class="title" style="color: #34d399; font-weight: 800;">مسح فاتورة</div>
                </div>
            </button>

            <!-- AI Price Spike & Selling Margin Radar -->
            <button onclick="if(window.openPurchaseAIAuditModal) window.openPurchaseAIAuditModal();" class="hub-action-card" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.22), rgba(168, 85, 247, 0.38)); border: 1px solid rgba(168, 85, 247, 0.55); cursor: pointer;">
                <div class="text-content">
                    <div class="title" style="color: #d8b4fe; font-weight: 800;">الأسعار</div>
                </div>
            </button>
        </div>

        <!-- Smart Filter Bar (شريط الفلترة الذكي) -->
        <div class="neo-card smart-filter-bar" style="margin-bottom: 20px; padding: 15px; background: linear-gradient(135deg, rgba(30, 27, 50, 0.7), rgba(15, 12, 30, 0.7)); border: 1px solid rgba(56, 189, 248, 0.15); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); border-radius: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; direction: rtl;">
                
                <!-- Right Side: Dual Filters Category + Value -->
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1; min-width: 300px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.1rem; filter: drop-shadow(0 0 5px rgba(56,189,248,0.5));">🔍</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: #38bdf8; text-shadow: 0 0 8px rgba(56,189,248,0.2);">تصفية الفواتير:</span>
                    </div>
                    
                    <!-- Column 1: Filter Type Select -->
                    <div style="position: relative; min-width: 160px;">
                        <select id="hub-filter-type" class="v-input ctrl-input" onchange="updateHubFilterValueUI()" style="width: 100%; cursor: pointer;">
                            <option value="all">🔍 بحث عام</option>
                            <option value="invoiceNo">📄 رقم الفاتورة</option>
                            <option value="status">⏳ الحالة (مرحلة / مسودة)</option>
                            <option value="warehouse">📦 المخزن (أو قيد الاستلام)</option>
                            <option value="supplier">👤 المورد</option>
                            <option value="item">🏷️ الصنف</option>
                            <option value="date">📅 التاريخ</option>
                        </select>
                    </div>
                    
                    <!-- Arrow separator -->
                    <span style="color: rgba(255,255,255,0.2); font-weight: bold; font-size: 1rem;">←</span>
                    
                    <!-- Column 2: Dynamic Filter Value -->
                    <div id="hub-filter-value-container" style="display: flex; align-items: center; min-width: 220px; flex: 0.5;">
                        <!-- Dynamic Element -->
                        <input type="text" id="hub-filter-value" class="v-input ctrl-input" placeholder="بحث عام..." style="width: 200px;" oninput="filterHubTransactions()">
                    </div>
                </div>

                <!-- Left Side: Time Period Group + Refresh Button -->
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <!-- Time Buttons container -->
                    <div class="filter-time-group" style="display: flex; background: rgba(0,0,0,0.3); padding: 4px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                        <button class="hub-time-btn" data-period="all" onclick="setHubTimePeriod('all')" style="background:rgba(56,189,248,0.15); border:none; color:#38bdf8; padding:6px 16px; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; transition: all 0.25s; font-family:'Tajawal',sans-serif;">الكل</button>
                        <button class="hub-time-btn" data-period="today" onclick="setHubTimePeriod('today')" style="background:transparent; border:none; color:#94a3b8; padding:6px 16px; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; transition: all 0.25s; font-family:'Tajawal',sans-serif;">اليوم</button>
                        <button class="hub-time-btn" data-period="week" onclick="setHubTimePeriod('week')" style="background:transparent; border:none; color:#94a3b8; padding:6px 16px; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; transition: all 0.25s; font-family:'Tajawal',sans-serif;">آخر 7 أيام</button>
                        <button class="hub-time-btn" data-period="month" onclick="setHubTimePeriod('month')" style="background:transparent; border:none; color:#94a3b8; padding:6px 16px; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; transition: all 0.25s; font-family:'Tajawal',sans-serif;">الشهر الحالي</button>
                    </div>

                    <!-- Refresh + Export Excel + Print Report buttons -->
                    <button class="btn-action refresh-btn-premium" style="width: auto; height: 38px; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 0 14px; font-size: 0.84rem; font-weight: 800; background: linear-gradient(135deg, rgba(56,189,248,0.15), rgba(99,102,241,0.15)); border: 1px solid rgba(56,189,248,0.35); color: #38bdf8; border-radius: 8px; cursor: pointer; transition: all 0.25s;" onclick="loadHubTransactions()" title="تحديث ومزامنة السجل">
                        <span>🔄 تحديث</span>
                    </button>
                    <button type="button" class="btn-action" style="width: auto; height: 38px; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 0 13px; font-size: 0.82rem; font-weight: 800; background: rgba(16,185,129,0.16); border: 1px solid rgba(16,185,129,0.45); color: #34d399; border-radius: 8px; cursor: pointer;" onclick="window.exportPurchaseHubToExcel()" title="تصدير سجل المشتريات إلى Excel">
                        <span>📊 Excel</span>
                    </button>
                    <button type="button" class="btn-action" style="width: auto; height: 38px; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 0 13px; font-size: 0.82rem; font-weight: 800; background: rgba(245,158,11,0.16); border: 1px solid rgba(245,158,11,0.45); color: #fbbf24; border-radius: 8px; cursor: pointer;" onclick="window.printPurchaseHubReportA4()" title="طباعة تقرير سجل المشتريات A4">
                        <span>🖨️ طباعة السجل</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: hidden; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.15rem; font-weight: 900; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: 2px solid rgba(56,189,248,0.25); padding-bottom: 12px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span style="font-size: 1.22rem; font-weight: 900;">📦 سجل فواتير المشتريات</span>
                    <button type="button" onclick="if(window.openPurchaseAIAuditModal) window.openPurchaseAIAuditModal();" style="height: 34px; padding: 0 13px; border-radius: 9px; background: linear-gradient(135deg, #7c3aed, #4f46e5); border: 2px solid #c4b5fd; color: #ffffff !important; font-size: 0.85rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 3px 10px rgba(124,58,237,0.35);">🤖 الذكاء الاصطناعي للأسعار</button>
                    
                </div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-right:auto;">
                    <div id="hub-summary-bar" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 0.9rem; font-weight: 900;"></div>
                    <button type="button" class="unified-col-props-btn" onclick="if(typeof openGridSettingsModal==='function') openGridSettingsModal();" style="display:inline-flex !important; align-items:center !important; justify-content:center !important; gap:7px !important; height:43px !important; padding:0 18px !important; border-radius:10px !important; background:linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important; border:1.8px solid #38bdf8 !important; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; font-family:'Cairo','Tajawal',sans-serif !important; font-size:0.92rem !important; font-weight:900 !important; cursor:pointer !important; box-shadow:0 4px 12px rgba(14,165,233,0.25) !important; white-space:nowrap !important; flex-shrink:0 !important;"><span>⚙️</span><span>خصائص الأعمدة</span></button>
                </div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid" id="hub-pos-grid" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">📄 الرقم</th>
                            <th style="text-align:right;">📅 التاريخ</th>
                            <th style="text-align:right;">👤 المورد</th>
                            <th style="text-align:right;">📦 الأصناف</th>
                            <th style="text-align:center;">💰 الجملة (ج.س / $)</th>
                            <th style="text-align:center;">⏳ الحالة</th>
                            <th style="text-align:center;">🏢 المخزن</th>
                            <th style="text-align:center;">⚡ الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="hub-transactions-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <div class="empty-state" id="hub-empty-state" style="display: none;">
                    <div class="icon">📭</div>
                    <h3>لا توجد مشتريات</h3>
                </div>
            </div>

            <!-- Pagination Bar -->
            <div id="hub-pagination-bar" style="display: none; justify-content: center; align-items: center; gap: 15px; padding: 10px 0; border-top: 1px solid rgba(255,255,255,0.05);"></div>
        </div>
    </div>

    <!-- ═══ PURCHASE EDITOR VIEW ═══ -->
    <div id="purchase-editor-view" style="display: <?= $purDirectEditorOnLoad ? 'flex' : 'none' ?>; flex-direction: column; flex: 1; overflow-y: auto; padding-bottom: 30px;">
    

    <!-- Form Fields (Now first in DOM, rendering on the Right in RTL) -->
        
<!-- WRAPPER FOR TOP SECTION -->
<link rel="stylesheet" id="purchase-worldclass-ux-styles" href="css/screens/purchase-inline-13.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-13.css') ?: '2027' ?>">
<div id="purchase-top-container" class="form-expanded" style="display: flex; gap: 0; align-items: stretch; transition: all 0.5s ease; width: 100%; margin: 0; padding: 0;">
<div class="neo-card expanded-form purchase-glass-card" id="form-fields-card" style="margin: 0; width: 100%; box-sizing: border-box;">
            <div style="display: flex; flex-direction: column; gap: 9px;">
                
                <!-- ROW 0: ONE SINGLE UNBROKEN LINE (الرقم مع السهمين + التاريخ + جنيه ⇄ + الرصيد + المديونية + الشيكات + الحد + المتاح) -->
                <div id="purchase-row0-bar" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; width: 100%; margin: 0;">
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
                        <div id="purchase-user-audit-container" style="display: none !important;"></div>
                    </div>

                    <div id="customer-financial-card" class="fin-strip-inline" style="flex: 1 1 0%; min-width: 0; width: auto; opacity: 1; overflow: visible; display: flex; align-items: center;">
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
                            <div id="supplier-performance-rating-pill" style="display:none !important;"><span id="supplier-rating-text"></span></div>
                        </div>
                    </div>
                </div>

                <!-- ROW 1: المورد (+ أستاذ + إضافة بدون آخر فاتورة) + زر جنيه/دولار + سعر الصرف الموسّع + المخزن في مكانه الأصلي -->
                <div id="purchase-row1-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; margin: 0; width: 100%; align-items: center;">
                    <div class="h-form-group group-supplier" style="flex: 4.5 1 0%; min-width: 0; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
                        <label class="h-label" style="color: #fde047; margin:0;">المورد</label>
                        <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                            <div class="custom-dropdown" style="flex:1; min-width: 0;">
                                <div class="custom-dropdown-input-wrap" style="position: relative; width: 100%;">
                                    <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم المورد..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width:100%; padding-left: 78px; padding-right: 12px; color: #fde047; font-size: 1.08rem; font-weight: 900; background-color: rgba(251,191,36,0.06); border: 1px solid rgba(148,163,184,0.32);" autocomplete="one-time-code">
                                    <button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('hdr-customer').value)" class="ledger-btn-2027" title="دفتر الأستاذ العام">أستاذ</button>
                                    <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();">
                                    <div class="custom-dropdown-list" id="customer-dropdown-list"></div>
                                </div>
                            </div>
                            <button type="button" id="btn-quick-add-supplier" class="add-row-btn" onclick="openModal('add-customer-modal')" title="إضافة مورد جديد (F4)" style="flex-shrink: 0; width: 38px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                        </div>
                    </div>

                    <div class="h-form-group group-currency" style="flex: 0 0 auto; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
                        <select class="v-input ctrl-input system-currency-select" id="hdr-currency" title="العملة" onchange="handleCurrencyChange(this.value); var c=(this.value==='USD'?'USD':'SDG'); window._finStripCurrency=c; try{localStorage.setItem('pur_fin_strip_cur',c);}catch(_e){} var b=document.getElementById('hdr-currency-toggle-btn'); if(b){b.textContent=(c==='USD'?'دولار':'جنيه'); b.dataset.cur=c;} if(typeof window.onCustomerChange==='function') window.onCustomerChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="display:none !important;">
                            <option value="SDG">🇸🇩 جنيه</option>
                            <option value="USD">🇺🇸 دولار</option>
                            <option value="SAR">🇸🇦 ريال</option>
                            <option value="AED">🇦🇪 درهم</option>
                            <option value="EGP">🇪🇬 مصري</option>
                        </select>
                        <button type="button" id="hdr-currency-toggle-btn" data-cur="SDG" onclick="var s=document.getElementById('hdr-currency'); if(s){s.value=(s.value==='USD'?'SDG':'USD'); this.textContent=(s.value==='USD'?'دولار':'جنيه'); this.dataset.cur=s.value; s.dispatchEvent(new Event('change'));}" title="اضغط للتبديل الفوري بين الجنيه والدولار" style="min-width: 68px; padding: 0 10px; height: 36px; border-radius: 9px; background: linear-gradient(180deg, #0284c7 0%, #1d4ed8 100%); border: 1.8px solid #7dd3fc; color: #ffffff; font-family: 'Cairo', sans-serif; font-size: 0.92rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; flex-shrink: 0;">جنيه</button>
                        <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" placeholder="سعر الصرف" readonly style="width: 124px; min-width: 114px; text-align: center; cursor:pointer; padding: 0 8px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; this.dataset.userEdited='1'; if(window.autoFitHeaderFields) window.autoFitHeaderFields(); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
                    </div>

                    <div class="h-form-group group-warehouse" style="flex: 1.65 1 0%; min-width: 180px; max-width: 280px; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
                        <label class="h-label" style="margin:0; white-space:nowrap;">📦 المخزن</label>
                        <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                            <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width: 100%; font-weight: 800; padding: 0 10px;"></select>
                        </div>
                    </div>
                </div>

                <div id="supplier-credit-limit-alert-bar" style="display:none; margin-top:2px; padding:5px 10px; border-radius:8px; background:rgba(239,68,68,0.14); border:1px solid rgba(239,68,68,0.45); color:#fca5a5; font-size:0.88rem; font-weight:900; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;"></div>

                <!-- ROW 2: البيان + مركز الكلفة -->
                <div id="purchase-row2-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; margin: 0; width: 100%; align-items: center;">
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
                                <option value="">-- بدون مركز --</option>
                                <option value="C1">المركز الرئيسي</option>
                                <option value="C2">الفرع الأول</option>
                                <option value="C3">المشروع أ</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div> <!-- End purchase-top-row -->

    <link rel="stylesheet" href="css/screens/purchase-inline-14.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-14.css') ?: '2027' ?>">
    
    <!-- BOTTOM PANEL (Items Table) -->
    <div class="purchase-bottom" style="flex: none; display: flex; flex-direction: column; padding: 0 0 15px 0; overflow: visible; margin-top: 8px; width: 100%; box-sizing: border-box;">
        
        <div class="panel-header-row" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 27, 50, 0.75)); border: 1px solid rgba(56, 189, 248, 0.18); border-radius: 12px; padding: 8px 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <div class="panel-title" style="white-space: nowrap; font-weight: 900; color: #38bdf8; font-size: 1.05rem; display: flex; align-items: center; gap: 6px; text-shadow: 0 0 10px rgba(56,189,248,0.25);">
                    <span>📦</span>
                    <span>أصناف الفاتورة</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <button type="button" id="btn-purchase-alsajana" onclick="window.openPurchaseAlSajanaModal && window.openPurchaseAlSajanaModal()" style="background: linear-gradient(135deg, #b45309, #78350f); border: 1.5px solid #fbbf24; color: #fef3c7; border-radius: 8px; height: 34px; padding: 0 12px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.86rem; white-space: nowrap;" title="حاسبة البسكول ومعيار الحديد (135/140/15)">
                    <span>⚖️</span>
                    <span>البسكول</span>
                </button>
                <button type="button" id="btn-purchase-ai-prices" onclick="if(window.openPurchaseAIAuditModal) window.openPurchaseAIAuditModal();" style="background: linear-gradient(180deg, #7e22ce 0%, #6b21a8 52%, #4c1d95 100%); border: 1.5px solid #a855f7; border-bottom: 3px solid #2e1065; color: #ffffff; border-radius: 9px; height: 34px; padding: 0 14px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.9rem; white-space: nowrap;" title="رادار فحص الأسعار وتحديث هامش البيع">
                    <span>💰</span>
                    <span>الأسعار</span>
                </button>
                <button type="button" class="unified-col-props-btn" onclick="openGridSettingsModal()" style="display:inline-flex !important; align-items:center !important; justify-content:center !important; gap:7px !important; height:43px !important; padding:0 18px !important; border-radius:10px !important; background:linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important; border:1.8px solid #38bdf8 !important; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; font-family:'Cairo','Tajawal',sans-serif !important; font-size:0.92rem !important; font-weight:900 !important; cursor:pointer !important; box-shadow:0 4px 12px rgba(14,165,233,0.25) !important; white-space:nowrap !important; flex-shrink:0 !important;" title="خصائص الأعمدة"><span>⚙️</span><span>خصائص الأعمدة</span></button>
            </div>
        </div>
        
        <!-- items-toolbar was moved below the table-container -->

        <div class="table-container">
            <div class="grid-scroll-wrap">
                <table class="pos-grid" id="pos-grid">
                    <thead id="grid-head">
                    </thead>
                    <tbody id="grid-body"></tbody>
                                        <tfoot id="grid-foot">
                        <!-- Transport -->
                        <tr class="tot-row" id="row-tot-transport" style="display: none; background: rgba(249, 115, 22, 0.08); border-top: 1px dashed rgba(249, 115, 22, 0.3);">
                            <td colspan="5" style="border: none;"></td>
                            <td colspan="2" style="text-align: left; vertical-align: middle; padding-right: 15px; border: none;">
                                <span style="color: #f97316; font-size: 1.3rem; font-weight: 900;">🚚 الترحيل</span>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-transport" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('transport')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(249, 115, 22, 0.15); border: 1.5px solid rgba(249, 115, 22, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="t-transport-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('transport')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>
                        <!-- Labor -->
                        <tr class="tot-row" id="row-tot-labor" style="display: none; background: rgba(59, 130, 246, 0.08); border-top: 1px dashed rgba(59, 130, 246, 0.3);">
                            <td colspan="5" style="border: none;"></td>
                            <td colspan="2" style="text-align: left; vertical-align: middle; padding-right: 15px; border: none;">
                                <span style="color: #38bdf8; font-size: 1.3rem; font-weight: 900;">💪 العمالة</span>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-labor" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('labor')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(59, 130, 246, 0.15); border: 1.5px solid rgba(59, 130, 246, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="t-labor-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('labor')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>
                        <!-- Additions -->
                        <tr class="tot-row" id="row-tot-additions" style="display: none; background: rgba(234, 179, 8, 0.08); border-top: 1px dashed rgba(234, 179, 8, 0.3);">
                            <td colspan="5" style="border: none;"></td>
                            <td colspan="2" style="text-align: left; vertical-align: middle; padding-right: 15px; border: none;">
                                <span style="color: #eab308; font-size: 1.3rem; font-weight: 900;">➕ إضافات</span>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-additions-loc" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('additions')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(234, 179, 8, 0.15); border: 1.5px solid rgba(234, 179, 8, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="t-additions-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('additions')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>
                        <!-- Tax -->
                        <tr class="tot-row" id="row-tot-tax" style="display: none; background: rgba(239, 68, 68, 0.08); border-top: 1px dashed rgba(239, 68, 68, 0.3);">
                            <td colspan="5" style="border: none;"></td>
                            <td colspan="2" style="text-align: left; vertical-align: middle; padding-right: 15px; border: none;">
                                <span style="color: #ef4444; font-size: 1.3rem; font-weight: 900;">🏛️ ضريبة</span>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-tax-loc" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('tax')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(239, 68, 68, 0.15); border: 1.5px solid rgba(239, 68, 68, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="t-tax-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('tax')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>
                        <!-- Discount -->
                        <tr class="tot-row" id="row-tot-discount" style="display: none; background: rgba(34, 197, 94, 0.08); border-top: 1px dashed rgba(34, 197, 94, 0.3);">
                            <td colspan="5" style="border: none;"></td>
                            <td colspan="2" style="text-align: left; vertical-align: middle; padding-right: 15px; border: none;">
                                <span style="color: #22c55e; font-size: 1.3rem; font-weight: 900;">➖ خصم</span>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="padding: 6px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-discount-loc" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('discount')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(34, 197, 94, 0.15); border: 1.5px solid rgba(34, 197, 94, 0.5); border-radius: 8px; color: #fff; font-size: 1.7rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount hide-if-sdg" id="t-discount-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('discount')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="flex: 1; height: 48px; text-align: center; background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #38bdf8; font-size: 1.4rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif;">
                                </div>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>
                        <!-- Spacer -->
                        <tr>
                            <td colspan="13" style="border: none; height: 10px;"></td>
                        </tr>
                        <!-- Grand Total -->
                        <tr id="row-grand-total" style="background: rgba(15, 23, 42, 0.95); border-top: 2px solid rgba(56, 189, 248, 0.4);">
                            <td colspan="5" style="border: none;"></td>
                            <td colspan="2" style="text-align: left; vertical-align: middle; padding-right: 15px; border: none;">
                                <span id="lbl-grand-dynamic" style="color: #38bdf8; font-size: 1.5rem; font-weight: 900; text-shadow: 0 0 10px rgba(56,189,248,0.4);">الإجمالي الكلي:</span>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="padding: 10px 4px; border: none; min-width: 240px;">
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span class="val amount" id="t-grand-dynamic-loc" style="flex: 1; display: flex; align-items: center; justify-content: center; width: 100%; height: 52px; font-size: 2.1rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; color: #ffffff; text-align: center; background: linear-gradient(135deg, rgba(16,185,129,0.25), rgba(5,150,105,0.35)); border: 1.5px solid rgba(16,185,129,0.6); border-radius: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.25), inset 0 1px 2px rgba(255,255,255,0.2); letter-spacing: 0.5px;">0</span>
                                    <span class="val amount hide-if-sdg" id="t-grand-dynamic-fc" style="flex: 1; display: flex; align-items: center; justify-content: center; width: 100%; height: 52px; font-size: 1.6rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; color: #10b981 !important; text-align: center; background: rgba(16, 185, 129, 0.15); border: 1.5px solid rgba(16,185,129,0.4); border-radius: 8px;">0</span>
                                </div>
                            </td>
                            <td class="hide-if-sdg" style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="empty-state" id="empty-state">
                    <div class="icon">📦</div>
                    <h3>القائمة فارغة</h3>
                </div>
            </div>
            <!-- Centered Add Row Button (Hidden as button is now inside each row) -->
            <div id="add-row-container" style="display: none !important;">
                <button type="button" onclick="addRow()" title="إضافة سطر">
                    <span style="font-size: 1.2rem; font-weight: bold; line-height: 1;">➕</span>
                </button>
            </div>
            <div class="mobile-cards" id="mobile-cards"></div>
        </div>

        <!-- ══ INLINE TOTALS & EXTRA COSTS ══ -->
        <link rel="stylesheet" href="css/screens/purchase-inline-15.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-15.css') ?: '2027' ?>">
        <div id="inline-totals-container" style="margin-top: 15px; display: flex; justify-content: space-between; align-items: flex-start; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 15px;">
            
            <!-- Left Side (Visual Right in RTL): Toggles -->
            <div id="toggles-area" style="display: flex; flex-wrap: nowrap; gap: 5px; align-items: center;">
                <button type="button" class="tot-toggle-btn tot-btn-transport" onclick="toggleTotRow('transport')" id="btn-toggle-transport">ترحيل</button>
                <button type="button" class="tot-toggle-btn tot-btn-labor" onclick="toggleTotRow('labor')" id="btn-toggle-labor">عمالة</button>
                <button type="button" class="tot-toggle-btn tot-btn-additions" onclick="toggleTotRow('additions')" id="btn-toggle-additions">إضافات</button>
                <button type="button" class="tot-toggle-btn tot-btn-tax" onclick="toggleTotRow('tax')" id="btn-toggle-tax">ضريبة</button>
                <button type="button" class="tot-toggle-btn tot-btn-discount" onclick="toggleTotRow('discount')" id="btn-toggle-discount">خصم</button>
                <input type="hidden" id="hdr-landed-cost-mode" value="value">
                <button type="button" id="btn-landed-cost-mode" onclick="window.toggleLandedCostAllocationMode && window.toggleLandedCostAllocationMode()" style="height: 26px; padding: 0 9px; border-radius: 7px; background: #0369a1; border: 2px solid #38bdf8; color: #ffffff; font-family: 'Cairo', sans-serif; font-size: 0.76rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                    <span>التوزيع:</span><span id="lbl-landed-cost-mode" style="color:#fde047;">بالقيمة</span>
                </button>
            </div>

            <!-- 3-Way Matching Live Status Pill -->
            <div id="purchase-3way-match-badge" onclick="window.inspectThreeWayMatchDetails && window.inspectThreeWayMatchDetails()" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 9px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; font-size: 0.76rem; font-weight: 800; cursor: pointer; white-space: nowrap; transition: all 0.25s ease;">
                <span id="purchase-3way-match-icon">🛡️</span>
                <span id="purchase-3way-match-text">مطابقة 100%</span>
            </div>

            <!-- Right Side is now handled inside tfoot -->
            <div id="amounts-area" style="display: none;"></div>

            <!-- Original spans needed by recalcAll, kept hidden -->
            <div id="hidden-recalc-spans" style="display:none;">
                <span id="t-invoice-total">0</span>
                <span id="t-invoice-total-fc">0</span>
                <span id="lbl-grand-local"></span>
                <span id="t-grand-local">0</span>
                <span id="lbl-grand-fc"></span>
                <span id="t-grand-fc">0</span>
            </div>
        </div>


            <!-- ══ FULL-WIDTH: تفاصيل الدفع ══ -->
            <div class="panel" id="payment-panel" style="display: none; z-index: 40; margin-top: 15px; padding: 0 !important;">
                <link rel="stylesheet" href="css/screens/purchase-inline-16.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-16.css') ?: '2027' ?>">
                <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 10px 18px; background: rgba(15,23,42,0.4); border-bottom: 1px solid rgba(255,255,255,0.06); border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 0;">
                    <div style="display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <div class="panel-title" style="background: linear-gradient(135deg, #ff4d4d, #f87171); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.12rem; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; margin: 0; white-space: nowrap;">
                            <span>💳</span> طريقة وتفاصيل الدفع
                        </div>
                        <button type="button" id="pur-fin-mov-seq-badge" onclick="window.openPurchaseLinkedFinMovement && window.openPurchaseLinkedFinMovement()" title="اضغط للانتقال المباشر إلى شاشة الحركة المالية لهذا الرقم" style="display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 13px; border-radius: 9px; background: linear-gradient(180deg, #1e3a5f 0%, #0f172a 100%); border: 1.6px solid #7dd3fc; color: #ffffff; cursor: pointer; font-family: 'Outfit','Cairo',sans-serif; font-size: 0.9rem; font-weight: 900; white-space: nowrap;">
                            <span style="color:#bae6fd; font-size:0.82rem;">حركة مالية:</span>
                            <span id="pur-fin-mov-seq-val" style="color:#ffffff; font-size:1.02rem; direction:ltr; letter-spacing:0.5px;"><?= htmlspecialchars($purNextFinNo) ?></span>
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
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="toggle-credit" onclick="togglePaymentSection('credit')">آجل</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="toggle-cash" onclick="togglePaymentSection('cash')">نقدي</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="toggle-bank" onclick="togglePaymentSection('bank')">تحويل</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')">شيك</button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="btn-financial-qr" onclick="if(window.FinancialOCR && typeof FinancialOCR.openMobileQRBridge==='function'){FinancialOCR.openMobileQRBridge({ target: 'purchase' });}" title="ربط الجوال عبر رمز QR"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg><span>ربط الجوال</span></button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="btn-financial-wa-tray" onclick="window.openPurchaseInboundReceiptsDrawer && window.openPurchaseInboundReceiptsDrawer()" title="فتح ترحيل الإشعارات مباشرة"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg><span>ترحيل الإشعارات</span></button>
                        <button type="button" class="pay-toggle-btn pur-calm-pay-btn" id="btn-purchase-posting-accounts" onclick="window.location.href='posting-accounts.php'" title="الانتقال إلى شاشة ترحيل الحسابات"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg><span>ترحيل الحسابات</span></button>
                    </div>
                </div>
                
                <div class="payment-layout" id="payment-layout" style="display: none; gap: 15px; padding: 15px; flex-wrap: wrap;">
                    <!-- Right Section: Payment Details Forms -->
                    <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Credit payment box (Toggled off by default) -->
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

                        <!-- Cash payment box (Visible by default) -->
                        <div id="pay-cash-section" style="background: rgba(15, 23, 42, 0.96); border: 2px solid rgba(16, 185, 129, 0.85); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; display: flex; flex-direction: column; width: 100%;">
                            <div class="payment-row-item" style="display: grid; grid-template-columns: 85px 22% 18% 18% minmax(0, 1fr) 30px; gap: 10px; align-items: center; width: 100%; margin-bottom: 0px;">
                                <div class="pay-method-row-lbl" style="display: flex; align-items: center; justify-content: flex-start; color: #6ee7b7; font-weight: 900; font-size: 1.05rem; gap: 6px; text-shadow: 0 0 8px rgba(16, 185, 129, 0.4);"><span>💵</span> كاش</div>
                                <input type="number" class="v-input ctrl-input dynamic-focus-input dynamic-focus-amount" id="pay-cash-amount" value="0" step="1" oninput="onPaymentAmountInput()" placeholder="المبلغ" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.5); border-radius: 8px; text-align: center; font-size: 2.35rem; font-weight: 900; color: #ffffff; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; min-width: 0; box-sizing: border-box; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                                <select class="v-input ctrl-input" id="hdr-payment-account" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; text-align: center; font-size: 1.05rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%; min-width: 0; box-sizing: border-box;">
                                </select>
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

        <!-- Posting Status Bar (visible only in manual posting mode) -->
        <div id="posting-status-bar" style="display: none; align-items: center; justify-content: space-between; padding: 12px 20px; border-radius: 12px; border: 1px solid rgba(251,191,36,0.3); margin-top: 15px; direction: rtl; font-family: 'Tajawal', sans-serif; transition: all 0.3s ease;"></div>

        <link rel="stylesheet" id="purchase-bottom-toolbar-autofit-styles" href="css/screens/purchase-inline-17.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-17.css') ?: '2027' ?>">
        <div class="items-toolbar" style="display: flex; align-items: center; justify-content: center; padding: 10px 12px; border-radius: 14px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(18, 15, 28, 0.92)); border: 1px solid rgba(56, 189, 248, 0.16); margin-top: 14px; box-shadow: 0 8px 28px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.06);">
            
            <div class="toolbar-actions" style="display: flex; gap: 7px; width: 100%; align-items: center; flex-wrap: nowrap;">
                
                <!-- 1-Step LIFO Back Button (رجوع خطوة بخطوة مع حفظ الحالة) -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-back btn-back-lifo" style="flex: 0.78 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px; background: linear-gradient(135deg, #d97706, #b45309) !important; border: 1.5px solid #fde047 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;" onclick="if(typeof window.magaiesGoBackStepLIFO === 'function') { window.magaiesGoBackStepLIFO(); } else if(typeof showPurchaseHub === 'function' && document.getElementById('purchase-hub-view')) { showPurchaseHub(); } else if(typeof magaieesGoBack === 'function') { magaieesGoBack(); }" title="رجوع خطوة واحدة للخلف مع حفظ حالة الشاشة (LIFO)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                    <span class="btn-fit-label" style="color:#ffffff !important;-webkit-text-fill-color:#ffffff !important;">رجوع</span>
                </button>

                <!-- Direct Exit to Main Dashboard Button (خروج للشاشة الرئيسية) -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-exit btn-exit" style="flex: 0.78 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px;" onclick="if(typeof window.magaiesExitToMainDashboard === 'function') { window.magaiesExitToMainDashboard(); } else { window.location.href='magaiees_unified_dashboard.php'; }" title="الخروج المباشر إلى الشاشة الرئيسية مع حفظ حالة الشاشة">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span class="btn-fit-label">خروج</span>
                </button>

                <!-- Delete / Cancel Button (Isolated & Inline 2-Click Confirmed) -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-delete btn-delete" id="btn-delete-purchase-invoice" style="flex: 0.85 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px; background: linear-gradient(135deg, rgba(225, 29, 72, 0.85), rgba(159, 18, 57, 0.92)) !important; border: 1px solid rgba(251, 113, 133, 0.45) !important; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.28) !important;" onclick="window.handleSafePurchaseDeleteOrClear(this)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span class="del-btn-label btn-fit-label">حذف</span>
                </button>

                <!-- Ezn Print Button (Items & Quantities Only — No Prices) -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-print btn-preview" id="btn-preview-invoice" style="flex: 0.95 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 10px;" onclick="window.printPurchaseEznNoPrices ? window.printPurchaseEznNoPrices() : printCurrentInvoice()" title="طباعة إذن بالأصناف والكميات فقط (بدون أسعار)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span class="btn-fit-label">إذن</span>
                    <kbd style="font-size:0.62rem; padding:1px 4px; border-radius:4px; background:rgba(0,0,0,0.28); border:1px solid rgba(255,255,255,0.22); font-family:monospace; color:#e2e8f0; flex-shrink:0;">F8</kbd>
                </button>

                <!-- Financial Statement Button (Replaces OCR Scan — Full Financial Breakdown) -->
                <button type="button" class="btn-action royal-crystal-btn btn-ocr" id="btn-purchase-fin-statement" style="flex: 1.15 1 0%; min-width: 0; height: 44px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.9), rgba(37, 99, 235, 0.95)) !important; color: #fff !important; border: 1px solid rgba(125, 211, 252, 0.55) !important; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important; font-weight: 900; border-radius: 11px; cursor: pointer; transition: all 0.25s ease; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 10px;" onmouseover="this.style.boxShadow='0 6px 22px rgba(56, 189, 248, 0.55)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 4px 15px rgba(14, 165, 233, 0.3)'; this.style.transform='none';" onclick="window.printPurchaseFinancialStatement && window.printPurchaseFinancialStatement()" title="كشف حساب مالي كامل للأصناف والأسعار والمصروفات والسداد">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span class="btn-fit-label">كشف حساب</span>
                </button>
                
                <!-- Share Button: Professional with dropdown -->
                <div style="position: relative; flex: 1.02 1 0%; min-width: 0;" id="share-btn-wrapper">
                    <button type="button" class="btn-action royal-crystal-btn royal-btn-whatsapp btn-share" id="btn-share-invoice" style="width: 100%; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px;" onclick="toggleShareMenu(event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <span class="btn-fit-label">مشاركة</span>
                        <svg class="share-chevron-svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.8; width: 11px !important; height: 11px !important;"><polyline points="6 9 12 15 18 9"/></svg>
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
                            <span>واتساب</span>
                        </button>
                        <!-- Divider -->
                        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.2), transparent); margin: 4px 12px;"></div>
                        <!-- Email Option -->
                        <button type="button" onclick="closeShareMenu(); sendCurrentInvoiceEmail();" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: transparent; border: none; border-radius: 12px; color: #e2e8f0; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'Tajawal', sans-serif; direction: rtl;" onmouseover="this.style.background='rgba(245, 158, 11, 0.12)'; this.style.color='#f59e0b';" onmouseout="this.style.background='transparent'; this.style.color='#e2e8f0';">
                            <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 3px 10px rgba(245, 158, 11, 0.6);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </span>
                            <span>إيميل</span>
                        </button>
                        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.2), transparent); margin: 4px 12px;"></div>
                        <!-- Export Excel Option -->
                        <button type="button" onclick="closeShareMenu(); window.exportCurrentPurchaseInvoiceExcel();" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 10px 16px; background: transparent; border: none; border-radius: 12px; color: #e2e8f0; font-size: 0.92rem; font-weight: 700; cursor: pointer; font-family: 'Tajawal', sans-serif; direction: rtl;">
                            <span style="width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg, #10b981, #047857); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color:#fff; font-size:1rem;">📊</span>
                            <span>تصدير Excel فوري</span>
                        </button>
                        <!-- Thermal 80mm Receipt Option -->
                        <button type="button" onclick="closeShareMenu(); window.printThermalPurchaseReceipt80mm();" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 10px 16px; background: transparent; border: none; border-radius: 12px; color: #e2e8f0; font-size: 0.92rem; font-weight: 700; cursor: pointer; font-family: 'Tajawal', sans-serif; direction: rtl;">
                            <span style="width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg, #38bdf8, #0284c7); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color:#fff; font-size:1rem;">🧾</span>
                            <span>طباعة حرارية 80mm</span>
                        </button>
                        <!-- Print Barcode Labels Option -->
                        <button type="button" onclick="closeShareMenu(); window.printPurchaseItemsBarcodeLabels();" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 10px 16px; background: transparent; border: none; border-radius: 12px; color: #e2e8f0; font-size: 0.92rem; font-weight: 700; cursor: pointer; font-family: 'Tajawal', sans-serif; direction: rtl;">
                            <span style="width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg, #a855f7, #7e22ce); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color:#fff; font-size:1rem;">🏷️</span>
                            <span>طباعة ملصقات الباركود</span>
                        </button>
                    </div>
                </div>
                
                <!-- New Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-new btn-new" style="flex: 0.82 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px;" onclick="onSuccessNewInvoice(false);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span class="btn-fit-label">جديد</span>
                </button>

                <!-- زر التحكم في الإرسال التلقائي -->
                <div class="auto-send-control-box" id="auto-send-control-wrapper-purchase" style="height: 44px;">
                    <button type="button" class="btn crystal-btn btn-auto-send-toggle-unit inactive" id="btn-toggle-auto-send-purchase" style="height: 44px; white-space: nowrap;" onclick="window.togglePurchaseAutoSendMode()">
                        <span id="auto-send-purchase-icon" style="font-size: 0.95rem;">💾</span>
                        <span id="auto-send-purchase-label" class="btn-fit-label" style="font-weight: 800; font-size: 0.8rem;">إرسال تلقائي:</span>
                        <span id="auto-send-purchase-status-pill" style="padding: 2px 6px; border-radius: 6px; font-size: 0.7rem; font-weight: 900;">حفظ فقط</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-auto-send-options-unit inactive" id="btn-auto-send-options-purchase" style="height: 44px;" onclick="window.togglePurchaseAutoSendDropdown(event)">
                        ▼
                    </button>
                    
                    <div class="auto-send-menu-dropdown" id="auto-send-purchase-dropdown-menu" style="display: none;">
                        <div class="auto-send-menu-header">
                            <span style="display: inline-flex; align-items: center; gap: 7px;">
                                <span style="font-size: 1rem;">⚙️</span>
                                <span>الإرسال التلقائي</span>
                            </span>
                        </div>
                        <div class="auto-send-menu-item active-option" data-mode="disabled" onclick="window.selectPurchaseAutoSendOption('disabled')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(100, 116, 139, 0.35), rgba(51, 65, 85, 0.6)) !important; border-color: rgba(148, 163, 184, 0.4) !important;">💾</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #f1f5f9;">حفظ فقط</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="receipt_order_doc" onclick="window.selectPurchaseAutoSendOption('receipt_order_doc')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(2, 132, 199, 0.55)) !important; border-color: rgba(56, 189, 248, 0.45) !important;">📄</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #38bdf8;">مستند استلام</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="receipt_order_text" onclick="window.selectPurchaseAutoSendOption('receipt_order_text')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.55)) !important; border-color: rgba(251, 191, 36, 0.45) !important;">📝</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #fbbf24;">إذن نصي</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="invoice_doc" onclick="window.selectPurchaseAutoSendOption('invoice_doc')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(126, 34, 206, 0.55)) !important; border-color: rgba(192, 132, 252, 0.45) !important;">🧾</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #c084fc;">صورة الفاتورة</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="invoice" onclick="window.selectPurchaseAutoSendOption('invoice')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(5, 150, 105, 0.55)) !important; border-color: rgba(52, 211, 153, 0.45) !important;">💰</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #34d399;">نص الفاتورة والرصيد</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <span style="display:none;" id="footer-grand"></span>
                <button type="button" class="btn-action royal-crystal-btn royal-btn-save btn-submit" id="btn-submit-purchase-invoice" style="flex: 1.32 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap; padding: 0 12px;" onclick="window.smartValidateAndSubmitPurchase ? window.smartValidateAndSubmitPurchase() : submitInvoice()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span class="btn-fit-label">حفظ واعتماد</span>
                    <kbd style="font-size:0.62rem; padding:1px 4px; border-radius:4px; background:rgba(0,0,0,0.28); border:1px solid rgba(255,255,255,0.25); font-family:monospace; color:#fef08a; flex-shrink:0;">F10</kbd>
                </button>
            </div>
        </div>

        <!-- Hidden Adjustments & Notes -->
        <div id="adjustments-panel" style="display: none;">
            <input type="hidden" id="inv-notes" value="">
            <input type="text" id="discount-amt" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
            <input type="number" id="discount-pct" value="0">
            <input type="text" id="tax-amt" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
            <input type="number" id="tax-pct" value="0">
            <input type="text" id="addition-amt" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
            <input type="number" id="addition-pct" value="0">
        </div>

    </div> <!-- End purchase-bottom -->

    </div>
</div>
    <script defer src="js/localforage.min.js"></script>
    <script>window.addEventListener('load',function(){var f=function(){if(!window.html2pdf&&!document.getElementById('lazy-h2p')){var s=document.createElement('script');s.id='lazy-h2p';s.src='js/html2pdf.bundle.min.js';document.body.appendChild(s);}};if(window.requestIdleCallback)requestIdleCallback(f,{timeout:1800});else setTimeout(f,900);},{once:true});</script>
    <script defer src="js/whatsapp-integration.js?v=20261029_fast"></script>
    <script defer src="js/erp-whatsapp-suite.js?v=20261029_fast"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/accounting-engine.js?v=<?php echo file_exists('js/accounting-engine.js') ? filemtime('js/accounting-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/purchase-pro-v5.js?v=<?php echo file_exists('js/purchase-pro-v5.js') ? filemtime('js/purchase-pro-v5.js') : '20261030_v5'; ?>"></script>

<script src="js/screens/purchase-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-j1.js') ?: '2027' ?>"></script>
    <script src="js/screens/purchase-inline-22.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-inline-22.js') ?: '2027' ?>"></script>

<!-- Barcode Scanner Modal -->
<div id="barcode-scanner-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85);  z-index: 999999; justify-content: center; align-items: center; direction: rtl;">
    <div class="glass-panel" style="width: 90%; max-width: 480px; padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); position: relative; text-align: center; background: rgba(30,41,59,0.9);">
        <button onclick="stopCameraScanner()" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #f43f5e; font-size: 1.8rem; cursor: pointer;">&times;</button>
        <h3 style="color: #38bdf8; margin-bottom: 15px; font-weight: 800;">📷 قارئ الباركود عبر الكاميرا</h3>
        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 20px;">وجه مربع الكاميرا نحو الرمز الشريطي (Barcode) للمنتج لقراءته تلقائياً.</p>
        <div id="scanner-viewport" style="width: 100%; aspect-ratio: 4/3; background: #000; border-radius: 12px; overflow: hidden; border: 2px solid rgba(56,189,248,0.4); position: relative;"></div>
        <div id="scanner-status" style="margin-top: 15px; color: #fbbf24; font-weight: bold; font-size: 0.9rem;">جاري تهيئة الكاميرا...</div>
    </div>
</div>

<script src="js/screens/purchase-inline-23.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-inline-23.js') ?: '2027' ?>"></script>


<!-- Numeric Custom Context Menu -->
<div id="num-context-menu" class="glass-panel" style="
    display: none;
    position: fixed;
    z-index: 20000;
    width: 280px;
    padding: 15px;
    border-radius: 16px;
    background: rgba(15, 12, 22, 0.95);
    
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    direction: rtl;
    font-family: 'Outfit', sans-serif;
">
    <div style="font-weight: bold; color: #38bdf8; margin-bottom: 12px; font-size: 0.9rem; border-bottom: 1px dashed rgba(255,255,255,0.15); padding-bottom: 5px; display:flex; align-items:center; gap:6px;">
        <span>⚙️</span> خصائص عرض الأرقام
    </div>
    
    <!-- Font Color Selector -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">لون النص:</div>
        <div style="display: flex; gap: 8px;">
            <button onclick="setNumStyle('color', '#ffffff')" style="width:20px; height:20px; border-radius:50%; background:#ffffff; border:1px solid #fff; cursor:pointer;" title="أبيض"></button>
            <button onclick="setNumStyle('color', '#38bdf8')" style="width:20px; height:20px; border-radius:50%; background:#38bdf8; border:none; cursor:pointer;" title="لبني"></button>
            <button onclick="setNumStyle('color', '#10b981')" style="width:20px; height:20px; border-radius:50%; background:#10b981; border:none; cursor:pointer;" title="أخضر"></button>
            <button onclick="setNumStyle('color', '#fbbf24')" style="width:20px; height:20px; border-radius:50%; background:#fbbf24; border:none; cursor:pointer;" title="أصفر"></button>
            <button onclick="setNumStyle('color', '#ef4444')" style="width:20px; height:20px; border-radius:50%; background:#ef4444; border:none; cursor:pointer;" title="أحمر"></button>
            <button onclick="setNumStyle('color', '#c084fc')" style="width:20px; height:20px; border-radius:50%; background:#c084fc; border:none; cursor:pointer;" title="بنفسجي"></button>
        </div>
    </div>

    <!-- Font Size Selector -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">حجم الخط:</div>
        <select onchange="setNumStyle('size', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value="">افتراضي</option>
            <option value="0.75rem">صغير جداً (0.75)</option>
            <option value="0.85rem">صغير (0.85)</option>
            <option value="1rem">متوسط (1.0)</option>
            <option value="1.15rem">كبير (1.15)</option>
            <option value="1.3rem">كبير جداً (1.3)</option>
        </select>
    </div>

    <!-- Font Family Selector -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">نوع الخط (أوضح 3 خطوط للأرقام):</div>
        <select onchange="setNumStyle('font', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value="Inter">1. Inter (خط واجهة مستخدم نقي)</option>
            <option value="Outfit">2. Outfit (هندسي عصري)</option>
            <option value="JetBrains Mono">3. JetBrains Mono (خطي مونو دقيق جداً للأرقام)</option>
        </select>
    </div>

    <!-- Thousands Separator -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">فاصل الآلاف:</div>
        <select onchange="setNumFormat('thousandSep', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value=",">فاصلة ( , )</option>
            <option value=" ">مسافة فارغة (   )</option>
            <option value="none">بدون فاصل</option>
        </select>
    </div>

    <!-- Decimal Separator -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">الفاصل العشري:</div>
        <select onchange="setNumFormat('decimalSep', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value=".">نقطة ( . )</option>
            <option value=",">فاصلة ( , )</option>
        </select>
    </div>

    <!-- Rounding Decimals -->
    <div style="margin-bottom: 5px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">خانة التقريب العشري:</div>
        <select onchange="setNumFormat('decimals', parseInt(this.value))" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value="0">بدون فواصل (0)</option>
            <option value="1">منزلة واحدة (1)</option>
            <option value="2">منزلتين (2)</option>
            <option value="3">3 منازل (3)</option>
            <option value="4">4 منازل (4)</option>
        </select>
    </div>
</div>

<script src="js/screens/purchase-inline-24.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-inline-24.js') ?: '2027' ?>"></script>

<script src="js/screens/purchase-inline-25.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-inline-25.js') ?: '2027' ?>"></script>

    
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
                        <h3 style="margin:0; font-size:1.35rem; font-weight:900; color:#38bdf8;">تخصيص جدول المشتريات</h3>
                    </div>
                </div>
                <button type="button" onclick="closeGridSettingsModal()" style="background:rgba(255,255,255,0.08); border:none; color:#fff; font-size:1.4rem; cursor:pointer; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:background 0.2s;">✕</button>
            </div>
            
            <!-- Modal Body (2 Columns Layout) -->
            <div style="flex:1; overflow-y:auto; padding-right:4px; display:grid; grid-template-columns:1.1fr 1.35fr; gap:20px;">
                
                <!-- Right Side: Column Selector & Reorder List -->
                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:14px; display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:8px;">
                        <h4 style="margin:0; color:#38bdf8; font-size:1.05rem; font-weight:bold; display:flex; align-items:center; gap:6px;">📋 قائمة الأعمدة</h4>
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
                                    <input type="number" id="sel-col-fontsize" min="11" max="36" oninput="onSelectedColPropChange('fontSize', parseInt(this.value)||16)" style="width:60px; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:6px; text-align:center; font-weight:bold;">
                                    <input type="range" id="sel-col-fontsize-range" min="11" max="36" oninput="document.getElementById('sel-col-fontsize').value=this.value; onSelectedColPropChange('fontSize', parseInt(this.value));" style="flex:1; cursor:pointer; accent-color:#38bdf8;">
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
                                    <input type="color" id="sel-col-color" oninput="document.getElementById('sel-col-color-text').value=this.value; onSelectedColPropChange('color', this.value);" onchange="document.getElementById('sel-col-color-text').value=this.value; onSelectedColPropChange('color', this.value);" style="border:none; width:45px; height:34px; cursor:pointer; background:transparent; border-radius:6px;">
                                    <input type="text" id="sel-col-color-text" oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value.trim())){document.getElementById('sel-col-color').value=this.value.trim();} onSelectedColPropChange('color', this.value.trim());" onchange="onSelectedColPropChange('color', this.value.trim());" style="width:85px; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:6px; color:#fff; padding:5px; text-align:center; font-family:monospace; font-size:0.85rem;">
                                    <button type="button" onclick="const defC = document.documentElement.classList.contains('light-theme') ? '#120500' : '#ffffff'; onSelectedColPropChange('color', defC); document.getElementById('sel-col-color').value=defC; document.getElementById('sel-col-color-text').value=defC;" style="background:rgba(255,255,255,0.1); border:none; border-radius:6px; padding:5px 8px; font-size:0.75rem; color:#fff; cursor:pointer;">افتراضي</button>
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
                                <label style="color:#cbd5e1; font-weight:bold; font-size:0.82rem; display:block; margin-bottom:3px;">حجم الخط العام الافتراضي:</label>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <input type="number" id="grid-setting-font-size" min="11" max="32" value="16" style="width:55px; background:#0f172a; border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:#fff; padding:5px; text-align:center; font-weight:bold;">
                                    <input type="range" min="11" max="32" value="16" oninput="document.getElementById('grid-setting-font-size').value=this.value" style="flex:1; cursor:pointer; accent-color:#38bdf8;">
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

<script src="js/screens/purchase-j2.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-j2.js') ?: '2027' ?>"></script>

    <!-- Hidden OCR File/Camera Input -->
    <input type="file" id="ocr-camera-input" accept="image/*" capture="environment" style="display:none;" onchange="handleOCRFileSelect(event)">

    <!-- OCR Scanning Loading Overlay -->
    <div id="ocr-loading-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.88);  z-index:999999; flex-direction:column; align-items:center; justify-content:center; color:#fff; font-family:'Tajawal',sans-serif; direction:rtl;">
        <div style="width:70px; height:70px; border:4px solid rgba(56,189,248,0.2); border-top-color:#38bdf8; border-radius:50%; animation:ocrSpin 1s linear infinite; margin-bottom:20px;"></div>
        <h2 style="margin:0 0 8px 0; font-size:1.4rem; color:#38bdf8; font-weight:800;">جاري فحص وقراءة الفاتورة بالرؤية الحاسوبية... 🔍</h2>
        <p style="margin:0; color:#94a3b8; font-size:0.95rem;">يتم الآن استخراج المورد، الأصناف، الكميات، والأسعار عبر محرك الذكاء الاصطناعي</p>
    </div>

    <!-- OCR Review & Mapping Modal -->
    <div id="ocr-review-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85);  z-index:999998; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
        <div style="background:#1e293b; border:1px solid rgba(56,189,248,0.4); border-radius:16px; width:100%; max-width:880px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,0.7); overflow:hidden;">
            <!-- Header -->
            <div style="padding:16px 20px; background:linear-gradient(135deg, rgba(14,165,233,0.2), rgba(15,23,42,0.6)); border-bottom:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:1.6rem;">📄</span>
                    <div>
                        <h3 style="margin:0; color:#fff; font-size:1.2rem; font-weight:800;">مراجعة بيانات الفاتورة الممسوحة (AI OCR)</h3>
                        <p style="margin:0; color:#94a3b8; font-size:0.8rem;">تأكد من مطابقة الأصناف والأسعار قبل تفريغها في جدول المشتريات</p>
                    </div>
                </div>
                <button type="button" onclick="closeOCRModal()" style="background:transparent; border:none; color:#94a3b8; font-size:1.5rem; cursor:pointer; padding:5px 10px;">✕</button>
            </div>
            <!-- Body -->
            <div id="ocr-modal-body" style="padding:20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:16px;">
                <!-- Dynamically generated via JS -->
            </div>
            <!-- Footer -->
            <div style="padding:14px 20px; background:rgba(15,23,42,0.8); border-top:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <button type="button" onclick="closeOCRModal()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; padding:8px 18px; border-radius:8px; font-weight:bold; cursor:pointer;">إلغاء</button>
                <button type="button" id="btn-apply-ocr" onclick="applyScannedInvoiceToGrid()" style="background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; padding:10px 24px; border-radius:8px; font-size:1.05rem; font-weight:bold; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,0.4); display:flex; align-items:center; gap:8px;">
                    <span>✓ اعتماد وتفريغ في جدول المشتريات</span>
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes ocrSpin { to { transform: rotate(360deg); } }
    </style>
    
    <!-- Financial OCR Modal & Script -->
    <?php
    $purFinPrefix = 'E' . date('ym');
    $purMaxFinSeq = 0;
    try {
        if (isset($pdo) && $pdo) {
            $effT = !empty($activePurchaseTenantId) ? (int)$activePurchaseTenantId : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 12);
            $stFin = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ?");
            $stFin->execute([$effT]);
            while ($rFin = $stFin->fetch(PDO::FETCH_ASSOC)) {
                $dFin = json_decode($rFin['data'], true);
                if (!empty($dFin['deleted'])) continue;
                $idFin = isset($dFin['voucherNo']) && $dFin['voucherNo'] !== '' ? (string)$dFin['voucherNo'] : (isset($dFin['id']) ? (string)$dFin['id'] : '');
                if (strpos($idFin, $purFinPrefix) === 0) {
                    $sq = (int)substr($idFin, strlen($purFinPrefix));
                    if ($sq > $purMaxFinSeq && $sq < 999999) $purMaxFinSeq = $sq;
                }
            }
        }
    } catch (Throwable $e) {}
    $purNextFinNo = $purFinPrefix . str_pad($purMaxFinSeq + 1, 4, '0', STR_PAD_LEFT);
    ?>
    <script>window.DB_NEXT_FIN_VOUCHER_NO = <?= json_encode($purNextFinNo) ?>;</script>
    <?php if (file_exists(__DIR__ . '/includes/inbound-bankak-drawer.php')) include_once __DIR__ . '/includes/inbound-bankak-drawer.php'; ?>
    <?php include 'includes/financial-ocr-modal.php'; ?>
    <script defer src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
<script defer src="js/screens/purchase-j3.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-j3.js') ?: '2027' ?>"></script>

<link rel="stylesheet" id="pur-clean-theme-style" href="css/screens/purchase-s4.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-s4.css') ?: '2027' ?>">
<script defer id="pur-fin-strip-and-tfoot-organizer" src="js/screens/purchase-j4.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-j4.js') ?: '2027' ?>"></script>
    <!-- ══════════════════════════════════════════════════════════════════════════
         PURCHASE DESIGN CONSTITUTION 2027 — HUB VIEW (#purchase-hub-view)
         9 Tones + 3px Sculpted Borders + Crisp White Inputs + Dual Currency Toggle
         ══════════════════════════════════════════════════════════════════════════ -->
    <link rel="stylesheet" id="purchase-hub-constitution-2027-style" href="css/screens/purchase-inline-18.css?v=<?= @filemtime(__DIR__ . '/css/screens/purchase-inline-18.css') ?: '2027' ?>">

    <script defer id="purchase-hub-constitution-2027-script" src="js/screens/purchase-inline-26.js?v=<?= @filemtime(__DIR__ . '/js/screens/purchase-inline-26.js') ?: '2027' ?>"></script>
<link rel="stylesheet" href="css/pay-amount-typing-2027.css?v=<?= @filemtime(__DIR__ . '/css/pay-amount-typing-2027.css') ?: '2027' ?>">
</body>
</html>
