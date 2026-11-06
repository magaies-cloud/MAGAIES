<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() === 0) { @ob_start('ob_gzhandler'); } ?>
<?php 
$page_title = 'مشتريات'; 
$db_purchase_grid_config = null;
$preloadedUnitsJson = '[]';
$preloadedItemsJson = '[]';
try {
    require_once __DIR__ . '/api/db.php';
    if (file_exists(__DIR__ . '/db_bridge.php')) require_once __DIR__ . '/db_bridge.php';
    $systemUsdRate = function_exists('getSystemUsdExchangeRate') ? getSystemUsdExchangeRate($pdo ?? null) : 5999.0;
    if (isset($pdo)) {
        if (function_exists('getDBConnection')) { $pdo = getDBConnection(); }
        $activePurchaseTenantId = function_exists('resolveCurrentTenantId') ? resolveCurrentTenantId($pdo ?? null) : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 1);
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
        localStorage.setItem('erp_items_v1', iStr);
        if (tId) localStorage.setItem('tenant_' + tId + '_erp_items_v1', iStr);
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
  var getCookieVal = function(name) {
    var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  };
  var raw = getCookieVal('magaies_active_theme') ||
            localStorage.getItem('magaies_active_theme') || 
            localStorage.getItem('magaiees_global_theme') || 
            localStorage.getItem('magaiees_mobile_theme') || 
            localStorage.getItem('ui_theme') || 
            'warm_cream';
  var rawTone = getCookieVal('magaies_light_tone') ||
                localStorage.getItem('magaies_light_tone') || 
                localStorage.getItem('pur_light_tone_palette') || 
                'ivory_default';
  var aliasMap = {
    'dark_midnight': 'midnight_obsidian', 'dark_mocha': 'mocha_brown', 'dark_oled': 'pure_oled', 'dark_slate': 'slate_twilight',
    'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream', 'ivory_default': 'warm_cream',
    'royal_bronze': 'warm_cream', 'platinum_gray': 'exec_gray', 'crystal_silver': 'ultra_white', 'sage_pearl': 'warm_cream', 'sky_alabaster': 'ultra_white', 'warm_mocha_dark': 'warm_cream', 'deep_slate_dark': 'exec_gray',
    'pure-platinum': 'ultra_white', 'offwhite': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white',
    'pearl-platinum': 'exec_gray', 'nordic-paper': 'warm_cream', 'solar_contrast': 'warm_cream',
    'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian', 'ocean-navy': 'midnight_obsidian', 'navy': 'midnight_obsidian',
    'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
    'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled', 'dark': 'pure_oled',
    'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight'
  };
  var activeKey = (rawTone && aliasMap[rawTone]) ? aliasMap[rawTone] : (aliasMap[raw] || raw);
  var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
  if (!valid7.includes(activeKey)) activeKey = 'warm_cream';
  var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);
  if (!isLight) rawTone = 'dark_midnight';

  document.documentElement.setAttribute('data-theme', activeKey);
  document.documentElement.setAttribute('data-light-tone', rawTone);
  if (isLight) { document.documentElement.classList.add('light-theme'); document.documentElement.classList.remove('dark-theme'); }
  else { document.documentElement.classList.remove('light-theme'); document.documentElement.classList.add('dark-theme'); }

  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) {
      document.body.setAttribute('data-theme', activeKey);
      document.body.setAttribute('data-light-tone', rawTone);
      if (isLight) { document.body.classList.add('light-theme'); document.body.classList.remove('dark-theme'); }
      else { document.body.classList.remove('light-theme'); document.body.classList.add('dark-theme'); }
    }
  });
})();
</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشتريات | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=20261029_fast">
    <link rel="stylesheet" href="css/theme.css?v=20261101_crystal">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css?v=20261101_crystal">
    <link rel="stylesheet" href="css/print-invoice.css">
    <link rel="stylesheet" href="css/purchase-module-v2.css?v=<?php echo file_exists('css/purchase-module-v2.css') ? filemtime('css/purchase-module-v2.css') : '20261104_v2'; ?>">
    <style>
        @keyframes magicalInvoiceSlideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .invoice-slide-up-anim {
            animation: magicalInvoiceSlideUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
        }
        /* ═══════════════════════════════════════════════════════
           ✨ RICH GLASSMORPHISM & LAYER ARCHITECTURE
           ═══════════════════════════════════════════════════════ */
        .glass-panel,
        .neon-glass-panel,
        .panel,
        .neo-card,
        .pro-panel,
        .purchase-glass-card,
        .sales-glass-card,
        .smart-filter-bar,
        .toast,
        .custom-dropdown-list {
            backdrop-filter: blur(16px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
        }
        .modal-overlay {
            z-index: 999999 !important;
        }
        .toast-container {
            z-index: 1000000 !important;
        }
        .glass-panel {
            background: rgba(12, 10, 20, 0.96) !important;
        }
        .panel {
            background: rgba(14, 12, 22, 0.97) !important;
        }
        .pro-panel {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(10, 17, 40, 0.97)) !important;
        }
        .purchase-glass-card {
            background: linear-gradient(135deg, rgba(26, 16, 45, 0.95) 0%, rgba(16, 24, 48, 0.96) 50%, rgba(10, 15, 30, 0.98) 100%) !important;
        }
        .toast {
            background: rgba(15, 23, 42, 0.96) !important;
        }
        .toast.success { background: rgba(10, 40, 30, 0.96) !important; }
        .toast.error { background: rgba(50, 15, 15, 0.96) !important; }
        .toast.warning { background: rgba(50, 40, 10, 0.96) !important; }
        .custom-dropdown-list {
            background: rgba(25, 30, 45, 0.96) !important;
        }

        table.pos-grid th, table.pos-grid td { padding: 4px 4px !important; }
        table.pos-grid .g-input, table.pos-grid .g-select { padding: 4px !important; margin: 0 !important; }

        :root {
            --bg-color: #050308;
            --velvet-bg: #050308;
            --velvet-panel: rgba(18, 15, 25, 0.65);
            --velvet-panel-hover: rgba(28, 24, 38, 0.85);
            --gold-primary: #38bdf8;
            --gold-glow: rgba(56, 189, 248, 0.4);
            --gold-border: rgba(255, 255, 255, 0.08);
            --text-main: #f3f4f6;
            --text-dim: #9ca3af;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --primary-accent: #6366f1;
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
        }

        html {
            font-size: 18px;
        }

        /* ═══ PREMIUM FORM CONTROL STYLING ═══ */
        /* Dark Mode Default Controls */
        .ctrl-input, .v-input, .system-currency-select {
            background: rgba(10, 8, 16, 0.85) !important;
            border: 1px solid rgba(56, 189, 248, 0.25) !important;
            border-radius: 8px !important;
            color: #f8fafc !important;
            padding: 8px 12px !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            outline: none !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .ctrl-input:focus, .v-input:focus, .system-currency-select:focus {
            background: #020104 !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.4), inset 0 1px 2px rgba(0,0,0,0.6) !important;
            transform: translateY(-1px);
        }
        /* Custom Ledger button inside supplier input wrapper */
        .ledger-btn-2027 {
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(99, 102, 241, 0.2)) !important;
            border: 1px solid rgba(56, 189, 248, 0.4) !important;
            color: #38bdf8 !important;
            padding: 4px 10px !important;
            border-radius: 6px !important;
            font-size: 0.75rem !important;
            font-weight: bold !important;
            cursor: pointer !important;
            z-index: 10;
            transition: all 0.25s ease !important;
        }
        .ledger-btn-2027:hover {
            background: linear-gradient(135deg, #38bdf8, #6366f1) !important;
            color: #fff !important;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.4) !important;
        }
        /* Labels formatting */
        .h-label {
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            transition: color 0.25s ease !important;
        }
        .h-form-group:focus-within .h-label {
            color: #38bdf8 !important;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.6) !important;
        }

        /* ═══ MULTI-COLOR VISUAL CODING & GLASSMORPHISM ═══ */
        /* Purchase specific top card custom glass style */
        .purchase-glass-card {
            background: linear-gradient(135deg, rgba(26, 16, 45, 0.95) 0%, rgba(16, 24, 48, 0.96) 50%, rgba(10, 15, 30, 0.98) 100%) !important;
            border: 1px solid rgba(139, 92, 246, 0.45) !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65), 0 0 30px rgba(139, 92, 246, 0.25), inset 0 1px 1px rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(25px) !important;
            -webkit-backdrop-filter: blur(25px) !important;
        }
        /* Sales specific top card custom glass style */
        .sales-glass-card {
            background: linear-gradient(135deg, rgba(49, 46, 129, 0.25) 0%, rgba(15, 23, 42, 0.75) 100%) !important;
            border: 1px solid rgba(99, 102, 241, 0.35) !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(99, 102, 241, 0.15), inset 0 1px 2px rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(25px) !important;
            -webkit-backdrop-filter: blur(25px) !important;
        }

        /* Group Specific Color Codings (Dark Mode Default) */
        /* 1. Invoice No (White) */
        .group-invoice .h-label { color: #ffffff !important; }
        .group-invoice .ctrl-input { border-color: rgba(255, 255, 255, 0.25) !important; color: #ffffff !important; }
        .h-form-group.group-invoice:focus-within .h-label { text-shadow: 0 0 8px rgba(255, 255, 255, 0.4) !important; }
        .group-invoice .ctrl-input:focus { border-color: #38bdf8 !important; box-shadow: 0 0 12px rgba(56, 189, 248, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

        /* 2. Date (Soft Rose/Red) */
        .group-date .h-label { color: #f87171 !important; }
        .group-date .ctrl-input { border-color: rgba(248, 113, 113, 0.25) !important; color: #f87171 !important; }
        .h-form-group.group-date:focus-within .h-label { text-shadow: 0 0 8px rgba(248, 113, 113, 0.4) !important; }
        .group-date .ctrl-input:focus { border-color: #f87171 !important; box-shadow: 0 0 12px rgba(248, 113, 113, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

        /* 3. Supplier / Customer (Teal/Emerald Green) */
        .group-supplier .h-label { color: #34d399 !important; }
        .group-supplier .ctrl-input { border-color: rgba(52, 211, 153, 0.25) !important; color: #ffffff !important; }
        .h-form-group.group-supplier:focus-within .h-label { text-shadow: 0 0 8px rgba(52, 211, 153, 0.4) !important; }
        .group-supplier .ctrl-input:focus { border-color: #34d399 !important; box-shadow: 0 0 12px rgba(52, 211, 153, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

        /* 4. Currency / Exchange Rate (Lavender/Indigo) */
        .group-currency .h-label { color: #a78bfa !important; }
        .group-currency .ctrl-input, .group-currency select.system-currency-select { border-color: rgba(167, 139, 250, 0.25) !important; color: #ffffff !important; }
        .h-form-group.group-currency:focus-within .h-label { text-shadow: 0 0 8px rgba(167, 139, 250, 0.4) !important; }
        .group-currency .ctrl-input:focus, .group-currency select.system-currency-select:focus { border-color: #a78bfa !important; box-shadow: 0 0 12px rgba(167, 139, 250, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

        /* 5. Warehouse (Light Yellow) */
        .group-warehouse .h-label { color: #fef08a !important; }
        .group-warehouse .ctrl-input { border-color: rgba(251, 146, 60, 0.25) !important; color: #ffffff !important; }
        .h-form-group.group-warehouse:focus-within .h-label { text-shadow: 0 0 8px rgba(254, 240, 138, 0.4) !important; }
        .group-warehouse .ctrl-input:focus { border-color: #fb923c !important; box-shadow: 0 0 12px rgba(251, 146, 60, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

        /* 6. Cost Center (Light Yellow) */
        .group-cost-center .h-label { color: #fef08a !important; }
        .group-cost-center .ctrl-input { border-color: rgba(163, 230, 53, 0.25) !important; color: #ffffff !important; }
        .h-form-group.group-cost-center:focus-within .h-label { text-shadow: 0 0 8px rgba(254, 240, 138, 0.4) !important; }
        .group-cost-center .ctrl-input:focus { border-color: #a3e635 !important; box-shadow: 0 0 12px rgba(163, 230, 53, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

        /* 7. Description (Slate/Gray) */
        .group-description .h-label { color: #94a3b8 !important; }
        .group-description .ctrl-input { border-color: rgba(148, 163, 184, 0.2) !important; color: #ffffff !important; }
        .h-form-group.group-description:focus-within .h-label { text-shadow: 0 0 8px rgba(148, 163, 184, 0.4) !important; }
        .group-description .ctrl-input:focus { border-color: #94a3b8 !important; box-shadow: 0 0 12px rgba(148, 163, 184, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

        /* Light Theme Overrides for Group Titles/Labels */
        :root.light-theme .group-invoice .h-label { color: #0284c7 !important; }
        :root.light-theme .group-date .h-label { color: #dc2626 !important; }
        :root.light-theme .group-supplier .h-label { color: #059669 !important; }
        :root.light-theme .group-currency .h-label { color: #7c3aed !important; }
        :root.light-theme .group-warehouse .h-label { color: #ea580c !important; }
        :root.light-theme .group-cost-center .h-label { color: #65a30d !important; }
        :root.light-theme .group-description .h-label { color: #475569 !important; }

        :root.light-theme .group-invoice .ctrl-input { border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .group-date .ctrl-input { border-color: #cbd5e1 !important; color: #dc2626 !important; }
        :root.light-theme .group-supplier .ctrl-input { border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .group-currency .ctrl-input, :root.light-theme .group-currency select.system-currency-select { border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .group-warehouse .ctrl-input { border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .group-cost-center .ctrl-input { border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .group-description .ctrl-input { border-color: #cbd5e1 !important; color: #0f172a !important; }

        :root.light-theme .group-invoice .ctrl-input:focus { border-color: #0284c7 !important; box-shadow: 0 0 10px rgba(2, 132, 199, 0.25) !important; }
        :root.light-theme .group-date .ctrl-input:focus { border-color: #dc2626 !important; box-shadow: 0 0 10px rgba(220, 38, 38, 0.25) !important; }
        :root.light-theme .group-supplier .ctrl-input:focus { border-color: #059669 !important; box-shadow: 0 0 10px rgba(5, 150, 105, 0.25) !important; }
        :root.light-theme .group-currency .ctrl-input:focus, :root.light-theme .group-currency select.system-currency-select:focus { border-color: #7c3aed !important; box-shadow: 0 0 10px rgba(124, 58, 237, 0.25) !important; }
        :root.light-theme .group-warehouse .ctrl-input:focus { border-color: #ea580c !important; box-shadow: 0 0 10px rgba(234, 88, 12, 0.25) !important; }
        :root.light-theme .group-cost-center .ctrl-input:focus { border-color: #65a30d !important; box-shadow: 0 0 10px rgba(101, 163, 13, 0.25) !important; }
        :root.light-theme .group-description .ctrl-input:focus { border-color: #475569 !important; box-shadow: 0 0 10px rgba(71, 85, 105, 0.25) !important; }

        /* Light Mode Controls Override */
        :root.light-theme .ctrl-input, 
        :root.light-theme .v-input,
        :root.light-theme .system-currency-select {
            background: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }
        :root.light-theme .ctrl-input:focus,
        :root.light-theme .v-input:focus,
        :root.light-theme .system-currency-select:focus {
            background: #ffffff !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.2), inset 0 1px 2px rgba(0,0,0,0.05) !important;
        }
        :root.light-theme .ledger-btn-2027 {
            background: rgba(59, 130, 246, 0.08) !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
            color: #2563eb !important;
        }
        :root.light-theme .ledger-btn-2027:hover {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }
        :root.light-theme .h-label {
            color: #475569 !important;
        }
        :root.light-theme .h-form-group:focus-within .h-label {
            color: #2563eb !important;
            text-shadow: none !important;
        }

        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: #070f1d !important;
            background-image: none !important;
        }



        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #38bdf8, #818cf8); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2); }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }
        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .clock-container { display: flex; flex-direction: column; align-items: center; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); min-width: 100px; }
        .clock-time { color: #38bdf8; font-size: 1rem; font-weight: 900; font-variant-numeric: tabular-nums; letter-spacing: 1px; text-shadow: 0 0 8px rgba(56,189,248,0.5); font-family: 'Outfit', sans-serif;}
        .clock-date { color: var(--text-secondary); font-size: 0.65rem; margin-top: 1px; }

        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--gold-border); width: 36px; height: 36px; border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 10px rgba(0,0,0,0.3); }

        .user-info { display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }
        
        .dash-main {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 8px 8px 24px 8px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 15px;
            flex: 1;
            animation: fadeIn 0.8s ease-out forwards;
        }

        body {
            background-color: var(--bg-color);
            margin: 0 !important; padding: 0 !important; min-height: 100vh;
            display: flex; flex-direction: column; overflow-x: hidden;
            font-family: 'Inter', 'Tajawal', sans-serif; color: var(--text-main);
        }
        
        body.has-sidebar { padding-right: 0 !important; } /* Override default if needed */

        /* Toast Notifications */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast {
            padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem;
            display:flex; align-items:center; gap:8px; pointer-events:auto;
            backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5);
            animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both;
            font-family:'Cairo',sans-serif; max-width:90vw;
        }
        .toast.success { background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.warning { background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.4); color:#fbbf24; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Custom Dropdown Layering */
        .custom-dropdown { position: relative; width: 100%; display: flex; gap: 6px; }
        .custom-dropdown-input-wrap { position: relative; flex: 1; }
        .custom-dropdown-list {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px;
            max-height: 280px; overflow-y: auto;
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(20px) saturate(200%);
            -webkit-backdrop-filter: blur(20px) saturate(200%);
            border: 1px solid rgba(56, 189, 248, 0.4);
            border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,0.95), 0 0 20px rgba(56, 189, 248, 0.25);
            z-index: 9999999 !important; display: none; flex-direction: column;
            min-width: 320px;
            scrollbar-width: thin;
            scrollbar-color: rgba(56, 189, 248, 0.4) transparent;
        }
        .custom-dropdown-list::-webkit-scrollbar {
            width: 6px;
        }
        .custom-dropdown-list::-webkit-scrollbar-thumb {
            background: rgba(56, 189, 248, 0.4);
            border-radius: 4px;
        }
        .custom-dropdown-list.show { display: flex !important; }
        .purchase-top:has(.custom-dropdown-list.show),
        .purchase-top-row:has(.custom-dropdown-list.show),
        .purchase-glass-card:has(.custom-dropdown-list.show),
        .h-form-group:has(.custom-dropdown-list.show),
        .h-input-wrapper:has(.custom-dropdown-list.show),
        .custom-dropdown:has(.custom-dropdown-list.show),
        .custom-dropdown-input-wrap:has(.custom-dropdown-list.show),
        .purchase-bottom:has(.custom-dropdown-list.show),
        .table-container:has(.custom-dropdown-list.show),
        .grid-scroll-wrap:has(.custom-dropdown-list.show),
        table:has(.custom-dropdown-list.show),
        tbody:has(.custom-dropdown-list.show),
        tr:has(.custom-dropdown-list.show),
        td:has(.custom-dropdown-list.show),
        .m-card:has(.custom-dropdown-list.show),
        .m-card-full:has(.custom-dropdown-list.show) { z-index: 999999 !important; position: relative !important; overflow: visible !important; }

        .custom-dropdown-item {
            padding: 11px 15px; cursor: pointer; font-size: 0.9rem; font-weight: 600;
            color: rgba(241, 245, 249, 0.9); border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .custom-dropdown-item:last-child {
            border-bottom: none;
        }
        .custom-dropdown-item:hover, .custom-dropdown-item.selected {
            background: rgba(251,191,36,0.12); color: var(--gold-primary);
            padding-right: 18px; /* subtle sliding micro-animation */
        }
        .custom-dropdown-empty { padding: 10px 12px; font-size: 0.8rem; color: var(--text-dim); text-align: center; }

        /* TOP NAV (Minimalist) */
        .velvet-nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 20px; background: var(--velvet-panel);
            border-bottom: 1px solid var(--gold-border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            height: 60px; box-sizing: border-box;
        }
        .velvet-nav-title {
            display: flex; align-items: center; gap: 12px;
        }
        .velvet-nav-title .icon-box {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--gold-primary), #b45309);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; box-shadow: 0 0 15px var(--gold-glow);
            color: #fff;
        }
        .velvet-nav-title h2 { margin: 0; font-size: 1.2rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; }

        /* TABS (Direct, Split, Delivery, Proforma) */
        .velvet-tabs { display: flex; gap: 8px; }
        .velvet-tab {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-dim); padding: 6px 14px; border-radius: 8px; cursor: pointer;
            font-weight: 600; font-size: 0.8rem; transition: 0.3s; display: flex; align-items: center; gap: 6px;
        }
        .velvet-tab:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .velvet-tab.active {
            background: rgba(251,191,36,0.15); border-color: var(--gold-primary);
            color: var(--gold-primary); box-shadow: inset 0 0 10px rgba(251,191,36,0.1);
        }

        .nav-controls { display: flex; align-items: center; gap: 12px; }
        .btn-icon-nav {
            width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1); color: var(--text-main);
            display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;
        }
        .btn-icon-nav:hover { background: rgba(251,191,36,0.1); color: var(--gold-primary); border-color: var(--gold-border); }

        /* SPLIT SCREEN LAYOUT */
        .split-layout {
            display: flex; height: calc(100vh - 60px); width: 100%;
        }

        /* RIGHT PANEL: SUPPLIER & TOTALS (320px width) */
        .split-right {
            width: 340px; min-width: 340px; background: transparent;
            display: flex; flex-direction: column;
            z-index: 10;
        }
        .split-right-scroll {
            flex: 1; overflow-y: auto; padding: 15px;
            display: flex; flex-direction: column; gap: 15px;
        }
        .split-right-scroll::-webkit-scrollbar { width: 4px; }
        .split-right-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }

        /* Neumorphic Cards inside Right Panel */
        .neo-card {
            background: rgba(18, 15, 25, 0.65);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--gold-border);
            border-radius: 18px; padding: 15px; margin-bottom: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
        }
        .neo-card-title {
            font-size: 0.75rem; font-weight: 800; color: var(--gold-primary);
            text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* Forms in Right Panel */
        .v-form-group { margin-bottom: 10px; }
        .v-label { display: block; font-size: 0.65rem; color: var(--text-dim); margin-bottom: 4px; font-weight: 600; }
        .v-input {
            width: 100%; padding: 8px 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 6px; color: #fff; font-size: 0.8rem; outline: none; transition: 0.2s;
            box-sizing: border-box; font-family: 'Inter', sans-serif;
        }
        .v-input:focus { border-color: var(--gold-primary); box-shadow: 0 0 0 2px rgba(251,191,36,0.15); }
        .btn-icon-add { padding: 0 10px; border-radius: 6px; background: rgba(251,191,36,0.1); border: 1px solid var(--gold-border); color: var(--gold-primary); cursor: pointer; }

        /* Fin Strip Grid */
        .fin-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;
        }
        .fin-box {
            background: rgba(0,0,0,0.4); border-radius: 8px; padding: 8px;
            border: 1px solid rgba(255,255,255,0.04); text-align: center;
        }
        .fin-box.full { grid-column: span 2; }
        .fin-box.avail { background: linear-gradient(135deg, rgba(251,191,36,0.15), rgba(180,83,9,0.1)); border-color: var(--gold-border); box-shadow: 0 0 15px rgba(251,191,36,0.05); }
        .fin-box-lbl { font-size: 0.6rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 2px; }
        .fin-box-val { font-size: 0.85rem; color: #fff; font-weight: 900; direction: ltr; }
        .fin-box.avail .fin-box-val { color: var(--gold-primary); font-size: 1rem; text-shadow: 0 0 10px rgba(251,191,36,0.4); }
        .fin-box.warn .fin-box-val { color: var(--danger-color); }

        /* Totals Area */
        .tot-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 6px 0; border-bottom: 1px dashed rgba(255,255,255,0.05);
        }
        .tot-row:last-child { border-bottom: none; }
        .tot-lbl { font-size: 0.7rem; color: var(--text-dim); font-weight: 600; }
        .tot-val { font-size: 0.85rem; font-weight: 800; direction: ltr; }
        .tot-input { width: 70px; padding: 2px 6px; font-size: 0.75rem; text-align: left; }
        .tot-grand {
            margin-top: 10px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
            border-radius: 8px; padding: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .tot-grand .lbl { color: var(--success-color); font-weight: 800; font-size: 0.8rem; }
        .tot-grand .val { color: var(--success-color); font-weight: 900; font-size: 1.2rem; direction: ltr; text-shadow: 0 0 15px rgba(16,185,129,0.4); }

        /* Action Footer in Right Panel */
        .right-footer {
            padding: 15px; border-top: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; gap: 8px; background: transparent;
        }
        .btn-action {
            width: 100%; padding: 12px; border-radius: 8px; font-weight: 800; font-size: 0.9rem;
            cursor: pointer; border: none; transition: 0.3s;
        }
        .btn-submit { background: linear-gradient(135deg, #38bdf8, #818cf8); color: #fff; box-shadow: 0 4px 15px var(--gold-glow); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--gold-glow); }
        .btn-draft { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-draft:hover { background: rgba(255,255,255,0.1); }
        .btn-delete { background: rgba(239,68,68,0.1); color: var(--danger-color); border: 1px solid rgba(239,68,68,0.2); }
        .btn-delete:hover { background: rgba(239,68,68,0.2); }

        /* LEFT PANEL: ITEMS & SEARCH (Flex 1) */
        .split-left {
            flex: 1; display: flex; flex-direction: column; padding: 0 0 15px 0 !important;
            overflow: hidden; background: transparent;
        }

        /* Toolbar above table */
        .items-toolbar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
            background: rgba(18, 15, 25, 0.65); padding: 12px 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .quick-search {
            flex: 1; max-width: 400px; position: relative;
        }
        .quick-search input {
            width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.4); border: 1px solid var(--gold-border);
            border-radius: 8px; color: #fff; font-size: 0.9rem; outline: none; transition: 0.3s; box-sizing: border-box;
        }
        .quick-search input:focus { border-color: var(--gold-primary); box-shadow: 0 0 15px rgba(251,191,36,0.1); }

        /* Table Area */
        .table-container {
            background: rgba(18, 15, 25, 0.65); border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            overflow: visible; display: flex; flex-direction: column;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .grid-scroll-wrap { overflow-y: visible; overflow-x: auto; }
        .grid-scroll-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
        .grid-scroll-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 6px; }
        
        .pos-grid { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        .pos-grid thead th {
            background: transparent; color: #94a3b8; font-size: 0.85rem; font-weight: 800;
            padding: 10px 15px; text-align: center; position: sticky; top: 0; z-index: 5;
            border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .pos-grid tbody tr {
            background: rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover {
            background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-grid tbody td {
            padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.02);
            font-size: 0.85rem; vertical-align: middle;
        }
        .pos-grid tbody td:first-child { border-radius: 12px 0 0 12px; border-right: 1px solid rgba(255,255,255,0.02); }
        .pos-grid tbody td:last-child { border-radius: 0 12px 12px 0; border-left: 1px solid rgba(255,255,255,0.02); }
        
        .g-input, .g-select {
            width: 100%; padding: 10px 12px; background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: #f8fafc; text-align: center; outline: none; font-size: 0.9rem; font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        .g-input:focus, .g-select:focus { 
            border-color: #38bdf8; background: rgba(15,23,42,0.8); box-shadow: 0 0 0 2px rgba(56,189,248,0.2), inset 0 2px 4px rgba(0,0,0,0.2); 
        }
        .g-input[type="text"] { text-align: right; }
        
        .g-readonly {
            display: inline-block; padding: 8px 12px; background: rgba(255,255,255,0.05); border-radius: 8px; font-weight: 800; text-align: center; width: 100%; box-sizing: border-box;
        }
        
        .del-btn {
            width: 32px; height: 32px; border-radius: 8px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: var(--danger-color); cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .del-btn:hover { background: var(--danger-color); color: #fff; box-shadow: 0 4px 10px rgba(239,68,68,0.4); transform: scale(1.1); }

        .empty-state {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: var(--text-dim); padding: 40px;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 10px; opacity: 0.5; filter: drop-shadow(0 0 10px var(--gold-glow)); }

        /* Notes Bar at Bottom of Left Panel */
        .notes-bar {
            margin-top: 15px; background: rgba(18, 15, 25, 0.65); padding: 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .notes-bar textarea {
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px; color: #fff; padding: 10px; resize: vertical; min-height: 40px; outline: none;
            box-sizing: border-box; font-family: 'Inter', sans-serif;
        }
        .notes-bar textarea:focus { border-color: var(--gold-primary); }

        /* Modal specific to theme */
        .modal-card { background: var(--velvet-panel); border: 1px solid var(--gold-border); box-shadow: 0 20px 60px rgba(0,0,0,0.8); }
        .modal-header h3 { color: var(--gold-primary); }
        .modal-header { border-bottom-color: rgba(255,255,255,0.05); }
        .modal-footer { border-top-color: rgba(255,255,255,0.05); }

        @media (max-width: 1024px) {
            .split-layout { flex-direction: column; overflow-y: auto; height: auto; }
            .split-right { width: 100%; min-width: 100%; border-left: none; border-bottom: 1px solid var(--gold-border); }
            .split-left { overflow: visible; }
            .table-container { min-height: 400px; }
        }
    
        .fin-strip-inline.visible {
            flex: 0 0 43% !important;
            width: auto !important;
            opacity: 1 !important;
            min-width: 400px !important;
            margin-left: 5px;
        }

    #purchase-top-container #totals-calc-card.hidden-totals {
        flex: 0 0 0px !important;
        min-width: 0px !important;
        width: 0px !important;
        height: 0px !important;
        max-height: 0px !important;
        opacity: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        transform: scaleX(0);
        overflow: hidden !important;
    }


    /* ================= 2027 LEDGER BUTTON DESIGN ================= */
    @keyframes neonPulse {
        0% { box-shadow: 0 0 5px #0ea5e9, 0 0 10px #0ea5e9, inset 0 0 5px rgba(14, 165, 233, 0.5); border-color: rgba(14, 165, 233, 0.8); }
        50% { box-shadow: 0 0 15px #38bdf8, 0 0 25px #38bdf8, inset 0 0 10px rgba(56, 189, 248, 0.8); border-color: #38bdf8; }
        100% { box-shadow: 0 0 5px #0ea5e9, 0 0 10px #0ea5e9, inset 0 0 5px rgba(14, 165, 233, 0.5); border-color: rgba(14, 165, 233, 0.8); }
    }

    @keyframes subtleShake {
        0% { transform: translateY(-50%) rotate(0deg); }
        25% { transform: translateY(-50%) rotate(0.8deg); }
        50% { transform: translateY(-50%) rotate(0deg); }
        75% { transform: translateY(-50%) rotate(-0.8deg); }
        100% { transform: translateY(-50%) rotate(0deg); }
    }

    .ledger-btn-2027 {
        position: absolute;
        left: 6px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        font-size: 0.8rem;
        font-weight: 800;
        padding: 0 10px;
        height: 26px;
        display: none; /* Displayed via JS when customer selected */
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(2, 132, 199, 0.3));
        color: #e0f2fe;
        border: 1px solid rgba(14, 165, 233, 0.5);
        border-radius: 6px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: neonPulse 2s infinite alternate, subtleShake 4s infinite ease-in-out;
        text-shadow: 0 0 4px rgba(255,255,255,0.4);
        cursor: pointer;
        overflow: hidden;
        font-family: 'Tajawal', sans-serif;
    }

    .ledger-btn-2027::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.4) 0%, transparent 50%);
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.4s ease;
        pointer-events: none;
    }

    .ledger-btn-2027:hover::before {
        opacity: 0.3;
        transform: scale(1);
    }

    .ledger-btn-2027:hover {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.4), rgba(2, 132, 199, 0.7));
        color: #ffffff;
        text-shadow: 0 0 8px #ffffff;
        box-shadow: 0 0 20px #0ea5e9, 0 0 30px #38bdf8, inset 0 0 15px rgba(56, 189, 248, 0.9);
        animation: subtleShake 1s infinite ease-in-out; /* Shake faster on hover */
    }

    :root.light-theme .ledger-btn-2027 {
        color: #0c4a6e;
        text-shadow: none;
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(14, 165, 233, 0.4));
        border-color: #0ea5e9;
        animation: subtleShake 4s infinite ease-in-out; /* Removed pulse from light theme to avoid washed out look */
        box-shadow: 0 2px 8px rgba(14, 165, 233, 0.4);
    }
    :root.light-theme .ledger-btn-2027:hover {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.8), rgba(2, 132, 199, 1));
        color: white;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.6);
    }

    /* Supplier Name Input Customization */
    #hdr-customer-search {
        font-size: 1.15rem !important;
        color: #fbbf24 !important; /* Beautiful Yellow / Amber */
        font-weight: 900 !important;
        text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
    }
    #hdr-customer-search::placeholder {
        font-size: 0.9rem !important;
        color: rgba(255, 255, 255, 0.4) !important;
        font-weight: 500 !important;
        text-shadow: none !important;
    }
    :root.light-theme #hdr-customer-search {
        color: #d97706 !important; /* Darker amber for visibility in light mode */
        text-shadow: none !important;
    }
    
</style>

    <style>
        .hub-action-card {
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            height: 60px;
        }
        .hub-action-card::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            transition: all 0.6s ease;
        }
        .hub-action-card:hover::after {
            left: 200%;
        }
        .hub-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px 0 rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.15);
        }
        .hub-action-card .icon {
            font-size: 1.6rem;
            margin-bottom: 0;
            padding: 6px;
            border-radius: 10px;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
        }
        .hub-action-card:hover .icon {
            transform: scale(1.1) rotate(-5deg);
        }
        .hub-action-card .text-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            text-align: right;
            flex: 1;
        }
        .hub-action-card .title {
            font-size: 1.05rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .hub-action-card .subtitle {
            display: none;
        }

        /* ── Theme-aware Card Styling (Dark Mode Defaults with Magical Glassy Borders & Glow) ── */
        .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(180, 83, 9, 0.08)) !important;
            border: 1px solid rgba(245, 158, 11, 0.6) !important; /* Translucent orange border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-orange .icon { color: #f59e0b; background: rgba(245, 158, 11, 0.18); }
        .card-orange .title { color: #f59e0b !important; }
        .card-orange .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.4), rgba(180, 83, 9, 0.15)) !important;
            border-color: rgba(245, 158, 11, 0.7) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.35), inset 0 0 18px rgba(245, 158, 11, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(3, 105, 161, 0.08)) !important;
            border: 1px solid rgba(14, 165, 233, 0.3) !important; /* Translucent blue border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-blue .icon { color: #38bdf8; background: rgba(56, 189, 248, 0.18); }
        .card-blue .title { color: #38bdf8 !important; }
        .card-blue .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.4), rgba(3, 105, 161, 0.15)) !important;
            border-color: rgba(14, 165, 233, 0.7) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.35), inset 0 0 18px rgba(14, 165, 233, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(190, 24, 74, 0.08)) !important;
            border: 1px solid rgba(236, 72, 153, 0.3) !important; /* Translucent pink border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-pink .icon { color: #f43f5e; background: rgba(244, 63, 94, 0.08); }
        .card-pink .title { color: #f43f5e !important; }
        .card-pink .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.4), rgba(190, 24, 74, 0.15)) !important;
            border-color: rgba(236, 72, 153, 0.7) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.35), inset 0 0 18px rgba(236, 72, 153, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(109, 40, 217, 0.08)) !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important; /* Translucent purple border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-purple .icon { color: #a855f7; background: rgba(168, 85, 247, 0.08); }
        .card-purple .title { color: #a855f7 !important; }
        .card-purple .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(109, 40, 217, 0.15)) !important;
            border-color: rgba(168, 85, 247, 0.7) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.35), inset 0 0 18px rgba(168, 85, 247, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        /* ── Light Mode overrides for Hub Cards (Light Pastel Glassy Gradients, Vibrant Colored Text & Magical Hover Border Glows) ── */
        :root.light-theme .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(251, 191, 36, 0.05)) !important;
            border: 1px solid rgba(245, 158, 11, 0.28) !important; /* Translucent orange glassy border */
            box-shadow: 0 8px 32px 0 rgba(245, 158, 11, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-orange .icon { color: #d97706 !important; background: rgba(245, 158, 11, 0.18) !important; }
        :root.light-theme .card-orange .title { color: #d97706 !important; }
        :root.light-theme .card-orange .subtitle { color: rgba(217, 119, 6, 0.8) !important; }
        :root.light-theme .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.6), rgba(251, 191, 36, 0.1)) !important;
            border-color: rgba(245, 158, 11, 0.6) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.22), inset 0 0 15px rgba(245, 158, 11, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.16), rgba(56, 189, 248, 0.05)) !important;
            border: 1px solid rgba(14, 165, 233, 0.28) !important; /* Translucent blue glassy border */
            box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-blue .icon { color: #0284c7 !important; background: rgba(14, 165, 233, 0.08) !important; }
        :root.light-theme .card-blue .title { color: #0284c7 !important; }
        :root.light-theme .card-blue .subtitle { color: rgba(2, 132, 199, 0.8) !important; }
        :root.light-theme .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(56, 189, 248, 0.1)) !important;
            border-color: rgba(14, 165, 233, 0.6) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.22), inset 0 0 15px rgba(14, 165, 233, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.16), rgba(244, 114, 182, 0.05)) !important;
            border: 1px solid rgba(236, 72, 153, 0.28) !important; /* Translucent pink glassy border */
            box-shadow: 0 8px 32px 0 rgba(236, 72, 153, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-pink .icon { color: #db2777 !important; background: rgba(236, 72, 153, 0.08) !important; }
        :root.light-theme .card-pink .title { color: #db2777 !important; }
        :root.light-theme .card-pink .subtitle { color: rgba(219, 39, 119, 0.8) !important; }
        :root.light-theme .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.3), rgba(244, 114, 182, 0.1)) !important;
            border-color: rgba(236, 72, 153, 0.6) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.22), inset 0 0 15px rgba(236, 72, 153, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.16), rgba(192, 132, 252, 0.05)) !important;
            border: 1px solid rgba(168, 85, 247, 0.28) !important; /* Translucent purple glassy border */
            box-shadow: 0 8px 32px 0 rgba(168, 85, 247, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-purple .icon { color: #7c3aed !important; background: rgba(168, 85, 247, 0.08) !important; }
        :root.light-theme .card-purple .title { color: #7c3aed !important; }
        :root.light-theme .card-purple .subtitle { color: rgba(124, 58, 237, 0.8) !important; }
        :root.light-theme .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(192, 132, 252, 0.1)) !important;
            border-color: rgba(168, 85, 247, 0.6) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.22), inset 0 0 15px rgba(168, 85, 247, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }
        .hub-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        .hub-badge.completed { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .hub-badge.draft { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
    </style>
    <style>
        :root.light-theme {
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --surface-hover: rgba(241, 245, 249, 0.98);
            --border: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.15);
        }
        :root.light-theme body {
            background-color: var(--bg-color);
            color: #000000;
        }
        :root.light-theme .ambient-bg {
            background: radial-gradient(circle at 50% 50%, #f8fafc 0%, #cbd5e1 100%) !important;
        }
        :root.light-theme .panel,
        :root.light-theme .neo-card,
        :root.light-theme .glass-panel {
            background: var(--surface) !important;
            border-color: var(--border) !important;
            color: #000000 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        }
        :root.light-theme td, :root.light-theme th, :root.light-theme tr {
            color: #000000 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .pos-grid thead th {
            background: rgba(0, 0, 0, 0.04) !important;
            color: #000000 !important;
        }
        :root.light-theme .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.95) !important;
        }
        :root.light-theme .pos-grid tbody tr:hover {
            background: rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme input, :root.light-theme select, :root.light-theme textarea {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.18) !important;
        }
    </style>
    <style>
        /* CSS for table enhancements */
        .pos-grid th, .pos-grid td {
            font-size: 1rem;
            padding: 7px 8px !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            vertical-align: middle !important;
        }
        .pos-grid .g-input, .pos-grid .g-select, .pos-grid .g-readonly {
            font-size: inherit;
            padding: 6px 8px !important;
            background: rgba(15, 23, 42, 0.55) !important;
            color: #f8fafc;
            border: 1px solid rgba(56, 189, 248, 0.14) !important;
            border-radius: 8px !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box !important;
        }
        
        /* Modern Glassmorphism Styling */
        .grid-scroll-wrap {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .pos-grid thead th {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gold-primary) !important;
            color: var(--gold-primary) !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.02) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        /* Hover effect on rows - Dynamic Elevation */
        .pos-grid tbody tr:hover td {
            background-color: rgba(56, 189, 248, 0.18) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.1), inset 0 -1px 0 rgba(255,255,255,0.1);
        }
        
        .pos-grid tbody tr:hover {
            transform: translateY(-2px) scale(1.002);
            box-shadow: 0 12px 40px rgba(56, 189, 248, 0.15);
            z-index: 2;
            position: relative;
        }

        /* Input Focus Glow Effect */
        .pos-grid .g-input:focus, .pos-grid .g-select:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.4), 0 0 15px rgba(56, 189, 248, 0.6) !important;
            transform: scale(1.02);
            z-index: 10;
        }
        
        .pos-grid .g-input::placeholder {
            color: #94a3b8 !important;
        }

        /* Resizable columns */
        .pos-grid th {
            position: relative;
        }
        .resizer {
            width: 5px;
            cursor: col-resize;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: transparent;
            z-index: 10;
        }
        .resizer:hover, .resizing {
            background-color: var(--gold-primary);
        }

        /* Context Menu */
        #col-context-menu {
            position: absolute;
            z-index: 100000;
            background: var(--velvet-panel-hover);
            border: 1px solid var(--gold-border);
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            display: none;
            min-width: 150px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .col-toggle-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        .col-toggle-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>

<style>
/* Purchase Top Container CSS */
    #purchase-top-container {
        display: flex; gap: 20px; align-items: stretch; width: 100%; 
        margin-bottom: 25px; /* Margin when totals card is visible */
        transition: margin-bottom 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    #purchase-top-container.form-expanded {
        margin-bottom: 8px; /* Margin when totals card is hidden (matches top margin) */
    }
    #purchase-top-container #form-fields-card {
        flex: 1; 
        min-width: 0; 
        transition: flex 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), max-width 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    }
    #purchase-top-container #totals-calc-card {
        display: flex !important;
        flex: 0 0 360px;
        min-width: 360px;
        max-width: 360px;
        opacity: 1;
        overflow: hidden;
        white-space: nowrap;
        background: linear-gradient(180deg, rgba(15,23,42,0.8), rgba(154,52,18,0.4));
        border: 1px solid rgba(249, 115, 22, 0.3);
        border-top: 3px solid #f97316 !important;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        transform-origin: right center;
    }
    #purchase-top-container #totals-calc-card.hidden-totals {
        flex: 0 0 0px !important;
        min-width: 0px !important;
        max-width: 0px !important;
        height: 0px !important;
        max-height: 0px !important;
        opacity: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border-width: 0 !important;
        overflow: hidden !important;
    }
    .tot-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 6px 0; border-bottom: 1px dashed rgba(255,255,255,0.05);
        }
        .tot-row:last-child { border-bottom: none; }
        .tot-lbl { font-size: 0.7rem; color: var(--text-dim); font-weight: 600; }
        .tot-val { font-size: 0.85rem; font-weight: 800; direction: ltr; }
        .tot-input { width: 70px; padding: 2px 6px; font-size: 0.75rem; text-align: left; }
        .tot-grand {
            margin-top: 10px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
            border-radius: 8px; padding: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .tot-grand .lbl { color: var(--success-color); font-weight: 800; font-size: 0.8rem; }
        .tot-grand .val { color: var(--success-color); font-weight: 900; font-size: 1.2rem; direction: ltr; text-shadow: 0 0 15px rgba(16,185,129,0.4); }

        /* Action Footer in Right Panel */
        .right-footer {
            padding: 15px; border-top: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; gap: 8px; background: transparent;
        }
        .btn-action {
            width: 100%; padding: 12px; border-radius: 8px; font-weight: 800; font-size: 0.9rem;
            cursor: pointer; border: none; transition: 0.3s;
        }
        .btn-submit { background: linear-gradient(135deg, #38bdf8, #818cf8); color: #fff; box-shadow: 0 4px 15px var(--gold-glow); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--gold-glow); }
        .btn-draft { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-draft:hover { background: rgba(255,255,255,0.1); }
        .btn-delete { background: rgba(239,68,68,0.1); color: var(--danger-color); border: 1px solid rgba(239,68,68,0.2); }
        .btn-delete:hover { background: rgba(239,68,68,0.2); }

        /* LEFT PANEL: ITEMS & SEARCH (Flex 1) */
        .split-left {
            flex: 1; display: flex; flex-direction: column; padding: 0 15px 15px 15px;
            overflow: hidden; background: transparent;
        }

        /* Toolbar above table */
        .items-toolbar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
            background: rgba(18, 15, 25, 0.65); padding: 12px 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .quick-search {
            flex: 1; max-width: 400px; position: relative;
        }
        .quick-search input {
            width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.4); border: 1px solid var(--gold-border);
            border-radius: 8px; color: #fff; font-size: 0.9rem; outline: none; transition: 0.3s; box-sizing: border-box;
        }
        .quick-search input:focus { border-color: var(--gold-primary); box-shadow: 0 0 15px rgba(251,191,36,0.1); }

        /* Table Area */
        .table-container {
            background: rgba(18, 15, 25, 0.65); border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            overflow: visible; display: flex; flex-direction: column;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .grid-scroll-wrap { overflow-y: visible; overflow-x: auto; }
        .grid-scroll-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
        .grid-scroll-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 6px; }
        
        .pos-grid { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        .pos-grid thead th {
            background: transparent; color: #94a3b8; font-size: 0.85rem; font-weight: 800;
            padding: 10px 15px; text-align: center; position: sticky; top: 0; z-index: 5;
            border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .pos-grid tbody tr {
            background: rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover {
            background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-grid tbody td {
            padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.02);
            font-size: 0.85rem; vertical-align: middle;
        }
        .pos-grid tbody td:first-child { border-radius: 12px 0 0 12px; border-right: 1px solid rgba(255,255,255,0.02); }
        .pos-grid tbody td:last-child { border-radius: 0 12px 12px 0; border-left: 1px solid rgba(255,255,255,0.02); }
        
        .g-input, .g-select {
            width: 100%; padding: 10px 12px; background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: #f8fafc; text-align: center; outline: none; font-size: 0.9rem; font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        .g-input:focus, .g-select:focus { 
            border-color: #38bdf8; background: rgba(15,23,42,0.8); box-shadow: 0 0 0 2px rgba(56,189,248,0.2), inset 0 2px 4px rgba(0,0,0,0.2); 
        }
        .g-input[type="text"] { text-align: right; }
        
        .g-readonly {
            display: inline-block; padding: 8px 12px; background: rgba(255,255,255,0.05); border-radius: 8px; font-weight: 800; text-align: center; width: 100%; box-sizing: border-box;
        }
        
        .del-btn {
            width: 32px; height: 32px; border-radius: 8px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: var(--danger-color); cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .del-btn:hover { background: var(--danger-color); color: #fff; box-shadow: 0 4px 10px rgba(239,68,68,0.4); transform: scale(1.1); }

        .empty-state {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: var(--text-dim); padding: 40px;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 10px; opacity: 0.5; filter: drop-shadow(0 0 10px var(--gold-glow)); }

        /* Notes Bar at Bottom of Left Panel */
        .notes-bar {
            margin-top: 15px; background: rgba(18, 15, 25, 0.65); padding: 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .notes-bar textarea {
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px; color: #fff; padding: 10px; resize: vertical; min-height: 40px; outline: none;
            box-sizing: border-box; font-family: 'Inter', sans-serif;
        }
        .notes-bar textarea:focus { border-color: var(--gold-primary); }

        /* Modal specific to theme */
        .modal-card { background: var(--velvet-panel); border: 1px solid var(--gold-border); box-shadow: 0 20px 60px rgba(0,0,0,0.8); }
        .modal-header h3 { color: var(--gold-primary); }
        .modal-header { border-bottom-color: rgba(255,255,255,0.05); }
        .modal-footer { border-top-color: rgba(255,255,255,0.05); }

        @media (max-width: 1024px) {
            .split-layout { flex-direction: column; overflow-y: auto; height: auto; }
            .split-right { width: 100%; min-width: 100%; border-left: none; border-bottom: 1px solid var(--gold-border); }
            .split-left { overflow: visible; }
            .table-container { min-height: 400px; }
        }
    
        .fin-strip-inline.visible {
            flex: 0 0 43% !important;
            width: auto !important;
            opacity: 1 !important;
            min-width: 400px !important;
            margin-left: 5px;
        }

    #purchase-top-container #totals-calc-card.hidden-totals {
        flex: 0 0 0px !important;
        min-width: 0px !important;
        width: 0px !important;
        height: 0px !important;
        max-height: 0px !important;
        opacity: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        transform: scaleX(0);
        overflow: hidden !important;
    }

        /* ── Theme-aware Card Styling (Dark Mode Defaults with Magical Glassy Borders & Glow) ── */
        .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(180, 83, 9, 0.08)) !important;
            border: 1px solid rgba(245, 158, 11, 0.6) !important; /* Translucent orange border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-orange .icon { color: #f59e0b; background: rgba(245, 158, 11, 0.18); }
        .card-orange .title { color: #f59e0b !important; }
        .card-orange .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.4), rgba(180, 83, 9, 0.15)) !important;
            border-color: rgba(245, 158, 11, 0.7) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.35), inset 0 0 18px rgba(245, 158, 11, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(3, 105, 161, 0.08)) !important;
            border: 1px solid rgba(14, 165, 233, 0.3) !important; /* Translucent blue border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-blue .icon { color: #38bdf8; background: rgba(56, 189, 248, 0.18); }
        .card-blue .title { color: #38bdf8 !important; }
        .card-blue .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.4), rgba(3, 105, 161, 0.15)) !important;
            border-color: rgba(14, 165, 233, 0.7) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.35), inset 0 0 18px rgba(14, 165, 233, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(190, 24, 74, 0.08)) !important;
            border: 1px solid rgba(236, 72, 153, 0.3) !important; /* Translucent pink border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-pink .icon { color: #f43f5e; background: rgba(244, 63, 94, 0.08); }
        .card-pink .title { color: #f43f5e !important; }
        .card-pink .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.4), rgba(190, 24, 74, 0.15)) !important;
            border-color: rgba(236, 72, 153, 0.7) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.35), inset 0 0 18px rgba(236, 72, 153, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(109, 40, 217, 0.08)) !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important; /* Translucent purple border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-purple .icon { color: #a855f7; background: rgba(168, 85, 247, 0.08); }
        .card-purple .title { color: #a855f7 !important; }
        .card-purple .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(109, 40, 217, 0.15)) !important;
            border-color: rgba(168, 85, 247, 0.7) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.35), inset 0 0 18px rgba(168, 85, 247, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        /* ── Light Mode overrides for Hub Cards (Light Pastel Glassy Gradients, Vibrant Colored Text & Magical Hover Border Glows) ── */
        :root.light-theme .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(251, 191, 36, 0.05)) !important;
            border: 1px solid rgba(245, 158, 11, 0.28) !important; /* Translucent orange glassy border */
            box-shadow: 0 8px 32px 0 rgba(245, 158, 11, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-orange .icon { color: #d97706 !important; background: rgba(245, 158, 11, 0.18) !important; }
        :root.light-theme .card-orange .title { color: #d97706 !important; }
        :root.light-theme .card-orange .subtitle { color: rgba(217, 119, 6, 0.8) !important; }
        :root.light-theme .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.6), rgba(251, 191, 36, 0.1)) !important;
            border-color: rgba(245, 158, 11, 0.6) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.22), inset 0 0 15px rgba(245, 158, 11, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.16), rgba(56, 189, 248, 0.05)) !important;
            border: 1px solid rgba(14, 165, 233, 0.28) !important; /* Translucent blue glassy border */
            box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-blue .icon { color: #0284c7 !important; background: rgba(14, 165, 233, 0.08) !important; }
        :root.light-theme .card-blue .title { color: #0284c7 !important; }
        :root.light-theme .card-blue .subtitle { color: rgba(2, 132, 199, 0.8) !important; }
        :root.light-theme .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(56, 189, 248, 0.1)) !important;
            border-color: rgba(14, 165, 233, 0.6) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.22), inset 0 0 15px rgba(14, 165, 233, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.16), rgba(244, 114, 182, 0.05)) !important;
            border: 1px solid rgba(236, 72, 153, 0.28) !important; /* Translucent pink glassy border */
            box-shadow: 0 8px 32px 0 rgba(236, 72, 153, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-pink .icon { color: #db2777 !important; background: rgba(236, 72, 153, 0.08) !important; }
        :root.light-theme .card-pink .title { color: #db2777 !important; }
        :root.light-theme .card-pink .subtitle { color: rgba(219, 39, 119, 0.8) !important; }
        :root.light-theme .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.3), rgba(244, 114, 182, 0.1)) !important;
            border-color: rgba(236, 72, 153, 0.6) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.22), inset 0 0 15px rgba(236, 72, 153, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.16), rgba(192, 132, 252, 0.05)) !important;
            border: 1px solid rgba(168, 85, 247, 0.28) !important; /* Translucent purple glassy border */
            box-shadow: 0 8px 32px 0 rgba(168, 85, 247, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-purple .icon { color: #7c3aed !important; background: rgba(168, 85, 247, 0.08) !important; }
        :root.light-theme .card-purple .title { color: #7c3aed !important; }
        :root.light-theme .card-purple .subtitle { color: rgba(124, 58, 237, 0.8) !important; }
        :root.light-theme .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(192, 132, 252, 0.1)) !important;
            border-color: rgba(168, 85, 247, 0.6) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.22), inset 0 0 15px rgba(168, 85, 247, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }
        .hub-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        .hub-badge.completed { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .hub-badge.draft { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }

        /* ══════════════════════════════════════════════════════════════════════════
           WORLD-CLASS INTERACTIVE BORDER ILLUMINATION & SEMANTIC GLOW SYSTEM (2027)
           حواف تفاعلية مضيئة ومريحة نفسياً لكل منطقة (البحث، المتاح، الوحدة، الكمية، السعر، المخزن، الجملة)
           ══════════════════════════════════════════════════════════════════════════ */

        /* 1. Base Visible Borders for All Grid Fields & Header Controls */
        .pos-grid .g-input,
        .pos-grid .g-select,
        .pos-grid .g-readonly {
            border: 1.5px solid rgba(148, 163, 184, 0.3) !important;
            border-radius: 9px !important;
            transition: border-color 0.22s cubic-bezier(0.4, 0, 0.2, 1),
                        box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1),
                        background-color 0.22s ease,
                        transform 0.18s ease !important;
        }

        /* Header & Toolbar Search/Control Borders (المورد، المخزن، العملة، البحث السريع) */
        .purchase-glass-card .v-input,
        .items-toolbar #quick-item-search,
        #hdr-customer-search {
            border: 1.5px solid rgba(56, 189, 248, 0.35) !important;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease, transform 0.2s ease !important;
        }

        .purchase-glass-card .v-input:hover,
        .purchase-glass-card .ctrl-group:hover .v-input {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.4), inset 0 0 6px rgba(56, 189, 248, 0.14) !important;
        }

        .purchase-glass-card .v-input:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.35), 0 0 18px rgba(56, 189, 248, 0.65), inset 0 0 8px rgba(56, 189, 248, 0.2) !important;
        }

        /* Supplier Search Special Golden-Cyan Illumination */
        #hdr-customer-search:hover {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 14px rgba(251, 191, 36, 0.45), inset 0 0 8px rgba(251, 191, 36, 0.15) !important;
        }
        #hdr-customer-search:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.35), 0 0 22px rgba(251, 191, 36, 0.7), inset 0 0 10px rgba(251, 191, 36, 0.22) !important;
        }

        /* 2–8. توحيد خطوط وحواف منطقة العمليات (المتاح، الصنف، الوحدة، الكمية، السعر، المخزن، الجملة) بلون خفيف واحد متلائم مع اللون الأساسي */
        .pos-grid td .g-input,
        .pos-grid td .g-select,
        .pos-grid td .avail-val,
        .pos-grid td .total-val {
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
            background: rgba(15, 23, 42, 0.65) !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }
        .pos-grid td[data-col-id="total"] .total-val {
            padding: 6px 10px !important;
        }
        #quick-item-search:hover,
        .quick-search:hover #quick-item-search,
        .pos-grid td .g-input:hover,
        .pos-grid td .g-select:hover,
        .pos-grid td .avail-val:hover,
        .pos-grid td .total-val:hover {
            border-color: rgba(148, 163, 184, 0.38) !important;
            box-shadow: none !important;
            transform: none !important;
        }
        #quick-item-search:focus,
        .pos-grid td .g-input:focus,
        .pos-grid td .g-select:focus {
            border-color: rgba(56, 189, 248, 0.55) !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.16) !important;
            transform: none !important;
        }

        /* Active Row Ambient Highlight when typing inside any cell of the row */
        .pos-grid tbody tr:focus-within td {
            background-color: rgba(56, 189, 248, 0.06) !important;
        }

        /* 9. Light Theme Unified Soft Border Overrides */
        :root.light-theme .pos-grid td .g-input,
        :root.light-theme .pos-grid td .g-select,
        :root.light-theme .pos-grid td .avail-val,
        :root.light-theme .pos-grid td .total-val {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
        :root.light-theme .pos-grid td .g-input:focus,
        :root.light-theme .pos-grid td .g-select:focus,
        :root.light-theme #quick-item-search:focus {
            background: #ffffff !important;
            border-color: #94a3b8 !important;
            box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.2) !important;
        }

        /* Proforma Mode Custom Styles (Lemon/Lime Theme) */
        body.proforma-mode .premium-header {
            background: linear-gradient(135deg, rgba(132, 204, 22, 0.25), rgba(163, 230, 53, 0.15)) !important;
            border-bottom: 1px solid rgba(132, 204, 22, 0.4) !important;
        }
        body.proforma-mode .header-page-title {
            color: #bef264 !important;
        }
        body.proforma-mode .ambient-bg {
            background: radial-gradient(circle at 50% 50%, rgba(132, 204, 22, 0.1) 0%, transparent 70%) !important;
        }
        body.proforma-mode .purchase-glass-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 1), rgba(132, 204, 22, 0.25)) !important;
            border: 1px solid rgba(132, 204, 22, 0.3) !important;
            border-top: 3px solid #bef264 !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.6), inset 0 0 30px rgba(132, 204, 22, 0.15) !important;
        }
        body.proforma-mode .neo-card {
            border-top: 2px solid #84cc16 !important;
            box-shadow: 0 8px 32px 0 rgba(132, 204, 22, 0.1) !important;
        }
        body.proforma-mode .items-toolbar {
            border-color: rgba(132, 204, 22, 0.3) !important;
        }
    </style>

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
                $wStyle = ($colWidth !== 'auto') ? ("width: " . $colWidth . " !important; min-width: " . $colWidth . " !important; max-width: " . $colWidth . " !important;") : "width: auto !important; min-width: 180px !important;";
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
    <style id="invoice-ultra-contrast-2027">
        /* 1. Pre-written Placeholders & Default Hints/Zeros (بلون باهت خفيف جداً للتوضيح مقابل الكتابة الحقيقية الواضحة) */
        input::placeholder,
        textarea::placeholder,
        #hdr-customer-search::placeholder,
        #quick-item-search::placeholder,
        #hdr-description::placeholder,
        #pos-grid input.g-input::placeholder {
            color: rgba(148, 163, 184, 0.36) !important;
            -webkit-text-fill-color: rgba(148, 163, 184, 0.36) !important;
            font-weight: 500 !important;
            opacity: 1 !important;
            text-shadow: none !important;
        }
        :root.light-theme input::placeholder,
        :root.light-theme textarea::placeholder,
        :root.light-theme #hdr-customer-search::placeholder,
        :root.light-theme #quick-item-search::placeholder,
        :root.light-theme #hdr-description::placeholder,
        :root.light-theme #pos-grid input.g-input::placeholder,
        html[data-theme="warm_cream"] input::placeholder,
        html[data-theme="ultra_white"] input::placeholder,
        html[data-theme="exec_gray"] input::placeholder {
            color: rgba(100, 116, 139, 0.42) !important;
            -webkit-text-fill-color: rgba(100, 116, 139, 0.42) !important;
            font-weight: 500 !important;
            opacity: 1 !important;
            text-shadow: none !important;
        }

        /* Default Zero Values (الصفر: 0 / 0.00) & Default Select Hints (نفس الفاتورة / بدون مركز) when unfocused */
        body #pos-grid tbody tr td input.g-input.is-zero-val:not(:focus),
        body #pos-grid tbody tr td .total-val.is-zero-val,
        body #pos-grid tbody tr td .avail-val.is-zero-val,
        body .tot-row input.tot-input.is-zero-val:not(:focus),
        body #pos-grid tbody tr td select.g-select.is-default-hint:not(:focus),
        body select.is-default-hint:not(:focus) {
            color: rgba(148, 163, 184, 0.38) !important;
            -webkit-text-fill-color: rgba(148, 163, 184, 0.38) !important;
            font-weight: 500 !important;
            text-shadow: none !important;
        }
        :root.light-theme body #pos-grid tbody tr td input.g-input.is-zero-val:not(:focus),
        :root.light-theme body #pos-grid tbody tr td .total-val.is-zero-val,
        :root.light-theme body #pos-grid tbody tr td .avail-val.is-zero-val,
        :root.light-theme body .tot-row input.tot-input.is-zero-val:not(:focus),
        :root.light-theme body #pos-grid tbody tr td select.g-select.is-default-hint:not(:focus),
        :root.light-theme body select.is-default-hint:not(:focus),
        html[data-theme="warm_cream"] body #pos-grid tbody tr td input.g-input.is-zero-val:not(:focus),
        html[data-theme="warm_cream"] body #pos-grid tbody tr td .total-val.is-zero-val,
        html[data-theme="warm_cream"] body #pos-grid tbody tr td select.g-select.is-default-hint:not(:focus) {
            color: rgba(100, 116, 139, 0.40) !important;
            -webkit-text-fill-color: rgba(100, 116, 139, 0.40) !important;
            font-weight: 500 !important;
            text-shadow: none !important;
        }
        :root.light-theme body #pos-grid tbody tr td .total-val.is-zero-val {
            background: #f8fafc !important;
            border-color: rgba(148, 163, 184, 0.38) !important;
            box-shadow: none !important;
        }

        /* 2. Light Theme Region Lightening & Deep Black Typography (تفتيح المنطقة وتغميق الخطوط بالأسود الواضح) */
        :root.light-theme #form-fields-card,
        :root.light-theme .purchase-glass-card,
        :root.light-theme .sales-glass-card,
        :root.light-theme .panel-header-row,
        :root.light-theme .table-container,
        :root.light-theme .grid-scroll-wrap,
        :root.light-theme #inline-totals-container,
        :root.light-theme #payment-panel {
            background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
            border: 2px solid #334155 !important;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08) !important;
            color: #000000 !important;
        }
        :root.light-theme .panel-title,
        :root.light-theme .panel-header-row .panel-title span {
            color: #000000 !important;
            text-shadow: none !important;
            font-weight: 900 !important;
        }

        /* Header Labels: رقم الفاتورة، التاريخ، المورد، سعر الصرف، المخزن، مركز الكلفة، البيان */
        :root.light-theme .h-label,
        :root.light-theme #invoice-info-box #hdr-inv-label,
        :root.light-theme #invoice-info-box #hdr-date-label,
        :root.light-theme #purchase-row1-bar .h-label,
        :root.light-theme .group-invoice .h-label,
        :root.light-theme .group-date .h-label,
        :root.light-theme .group-supplier .h-label,
        :root.light-theme .group-currency .h-label,
        :root.light-theme .group-warehouse .h-label,
        :root.light-theme .group-cost-center .h-label,
        :root.light-theme .group-description .h-label {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            font-size: 0.92rem !important;
            text-shadow: none !important;
            opacity: 1 !important;
        }

        /* Invoice Number Box & Serial Text (رقم الفاتورة واضح جداً وبالأسود الغامق) */
        :root.light-theme #invoice-info-box .pro-serial-container,
        :root.light-theme .pro-serial-container,
        :root.light-theme .gold-serial-wrap {
            background: #ffffff !important;
            border: 2px solid #000000 !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06) !important;
        }
        :root.light-theme #invoice-info-box #inv-number,
        :root.light-theme #inv-number,
        :root.light-theme .pro-serial-number {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            font-size: 1.38rem !important;
            text-shadow: none !important;
            opacity: 1 !important;
        }
        :root.light-theme .pro-serial-btn {
            background: #e2e8f0 !important;
            color: #000000 !important;
            border: 1.5px solid #475569 !important;
            font-weight: 900 !important;
            opacity: 1 !important;
        }

        /* Date Input (التاريخ واضح تماماً وباللون الأسود الصريح) */
        :root.light-theme #invoice-info-box #hdr-date,
        :root.light-theme #hdr-date,
        :root.light-theme input[type="date"] {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            color-scheme: light !important;
            border: 2px solid #000000 !important;
            font-weight: 900 !important;
            font-size: 1rem !important;
            opacity: 1 !important;
        }
        :root.light-theme #hdr-date::-webkit-datetime-edit,
        :root.light-theme #hdr-date::-webkit-datetime-edit-fields-wrapper,
        :root.light-theme #hdr-date::-webkit-datetime-edit-text,
        :root.light-theme #hdr-date::-webkit-datetime-edit-month-field,
        :root.light-theme #hdr-date::-webkit-datetime-edit-day-field,
        :root.light-theme #hdr-date::-webkit-datetime-edit-year-field,
        :root.light-theme input[type="date"]::-webkit-datetime-edit,
        :root.light-theme input[type="date"]::-webkit-datetime-edit-month-field,
        :root.light-theme input[type="date"]::-webkit-datetime-edit-day-field,
        :root.light-theme input[type="date"]::-webkit-datetime-edit-year-field {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #hdr-date::-webkit-calendar-picker-indicator {
            filter: none !important;
            opacity: 0.9 !important;
            cursor: pointer !important;
        }

        /* Header Inputs: المورد، سعر الصرف، العملة، المخزن، التكلفة، البيان، والبحث السريع */
        :root.light-theme #hdr-customer-search,
        :root.light-theme #hdr-currency,
        :root.light-theme #hdr-exchange-rate,
        :root.light-theme #hdr-warehouse,
        :root.light-theme #hdr-cost-center:not(.is-default-hint),
        :root.light-theme #hdr-description,
        :root.light-theme #quick-item-search {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 900 !important;
            text-shadow: none !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme #hdr-cost-center.is-default-hint:not(:focus) {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
        }

        /* User Audit Badge & Financial Strip in Light Theme */
        :root.light-theme #purchase-user-audit-container .user-audit-badge,
        :root.light-theme #customer-financial-card .fin-strip-inner {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06) !important;
        }
        :root.light-theme #purchase-user-audit-container .user-audit-badge .uab-label,
        :root.light-theme #purchase-user-audit-container .user-audit-badge .uab-val,
        :root.light-theme #customer-financial-card .fin-strip-inner span {
            color: #000000 !important;
            font-weight: 900 !important;
        }

        /* 3. Grid Table Headers, Typed Inputs & Totals (الكتابة الحقيقية باللون الأسود، مع خطوط فواصل خفيفة موحدة) */
        :root.light-theme body #pos-grid thead tr th {
            background: #dfd3bc !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 1px solid rgba(148, 163, 184, 0.32) !important;
            border-bottom: 1.5px solid #94a3b8 !important;
            font-weight: 900 !important;
        }
        :root.light-theme body #pos-grid tbody tr td {
            background: #f4ecde !important;
            border: 1px solid rgba(148, 163, 184, 0.25) !important;
            color: #000000 !important;
        }
        :root.light-theme body #pos-grid tbody tr:hover td,
        :root.light-theme body #pos-grid tbody tr:focus-within td {
            background: #ebe0cc !important;
        }
        :root.light-theme body #pos-grid tbody tr td input.g-input:not(.is-zero-val),
        :root.light-theme body #pos-grid tbody tr td input.g-input:focus,
        :root.light-theme body #pos-grid tbody tr td select.g-select:not(.is-default-hint),
        :root.light-theme body #pos-grid tbody tr td select.g-select:focus,
        :root.light-theme body #pos-grid tbody tr td .avail-val:not(.is-zero-val),
        :root.light-theme body #pos-grid tbody tr td .row-num {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 900 !important;
            text-shadow: none !important;
        }
        :root.light-theme body #pos-grid tbody tr td input.g-input.is-zero-val:not(:focus),
        :root.light-theme body #pos-grid tbody tr td select.g-select.is-default-hint:not(:focus) {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
        }
        :root.light-theme body #pos-grid select.g-select option {
            color: #000000 !important;
            background: #ffffff !important;
            font-weight: 800 !important;
        }
        :root.light-theme body #pos-grid select.g-select option[value=""] {
            color: #94a3b8 !important;
            font-weight: 500 !important;
        }

        /* الجملة (Total Column & Grand Total) — Unified Soft Border */
        :root.light-theme body #pos-grid tbody tr td[data-col-id="total"] .total-val:not(.is-zero-val),
        :root.light-theme body #pos-grid tbody tr td[data-col-id="col_total"] .total-val:not(.is-zero-val) {
            background: #d1fae5 !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 900 !important;
            box-shadow: none !important;
        }
        :root.light-theme #row-grand-total {
            background: #e5d8c0 !important;
            border-top: 2.5px solid #000000 !important;
            border-bottom: 2.5px solid #000000 !important;
        }
        :root.light-theme #lbl-grand-dynamic {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            text-shadow: none !important;
        }
        :root.light-theme #t-grand-dynamic-loc,
        :root.light-theme #t-grand-dynamic-fc {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2.5px solid #047857 !important;
            font-weight: 900 !important;
            text-shadow: none !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        }

        /* 4. Add & Delete Buttons (إضافة وحذف بألوان واضحة وتباين قوي) */
        body .add-row-btn,
        body #add-row-btn,
        body .quick-add-box button[onclick*="addRow"] {
            background: #059669 !important;
            border: 2px solid #064e3b !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.35) !important;
            opacity: 1 !important;
        }
        body .add-row-btn svg,
        body .quick-add-box button[onclick*="addRow"] svg {
            stroke: #ffffff !important;
            stroke-width: 3px !important;
        }
        body .del-btn,
        body #btn-delete-purchase-invoice {
            background: #dc2626 !important;
            border: 2px solid #7f1d1d !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35) !important;
            opacity: 1 !important;
        }
        body .del-btn svg {
            stroke: #ffffff !important;
            stroke-width: 2.6px !important;
        }

        /* 5. Bottom Extra-Cost & Payment Toggles (ترحيل، عمالة، إضافات، ضريبة، خصم، التوزيع، طرق الدفع — بلون غامق واضح التباين) */
        :root.light-theme .tot-toggle-btn,
        :root.light-theme #btn-landed-cost-mode,
        :root.light-theme #purchase-3way-match-badge,
        :root.light-theme .pay-toggle-btn {
            background: #ffffff !important;
            border: 2px solid #0f172a !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
            text-shadow: none !important;
        }
        :root.light-theme #btn-landed-cost-mode span,
        :root.light-theme #purchase-3way-match-badge span,
        :root.light-theme #toggles-area span {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme .tot-toggle-btn:hover,
        :root.light-theme .pay-toggle-btn:hover,
        :root.light-theme #btn-landed-cost-mode:hover {
            background: #e2e8f0 !important;
            border-color: #000000 !important;
            color: #000000 !important;
        }
        :root.light-theme .tot-toggle-btn.active,
        :root.light-theme .pay-toggle-btn.active {
            background: #0f172a !important;
            border: 2px solid #000000 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25) !important;
        }
        :root.light-theme .tot-row span {
            color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme .tot-row input.tot-input:not(.is-zero-val),
        :root.light-theme .tot-row input.tot-input:focus {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2px solid #000000 !important;
            font-weight: 900 !important;
        }

        /* 6. Nested Child Screens, Modals, Dropdowns, Hub & Payment Sub-Panels (سريان السمة 100% على كل الشاشات الوليدة) */
        /* Dark Themes: Pure Bright White (#FFFFFF) across all nested modals, dropdowns, and child panels */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay .modal-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #grid-settings-modal .glass-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .custom-dropdown-list,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #share-popup-menu,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #auto-send-purchase-dropdown-menu {
            background: #0f172a !important;
            color: #ffffff !important;
            border: 2px solid rgba(255, 255, 255, 0.32) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .custom-dropdown-item,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay h3,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay h4 {
            color: #ffffff !important;
            font-weight: 800 !important;
        }

        /* Light Themes: Warm Stepped Surfaces + Pitch Black (#000000) across ALL Nested Modals, Dropdowns, Payment Sub-Boxes & Hub */
        :root.light-theme .modal-overlay .modal-card,
        :root.light-theme #add-customer-modal .modal-card,
        :root.light-theme #grid-settings-modal .glass-panel,
        :root.light-theme #selected-col-panel,
        :root.light-theme #grid-settings-modal [style*="background:rgba(0,0,0"],
        :root.light-theme #success-modal-overlay > div,
        :root.light-theme #share-popup-menu,
        :root.light-theme #auto-send-purchase-dropdown-menu,
        :root.light-theme .smart-filter-bar,
        :root.light-theme #purchase-hub-view .neo-card,
        :root.light-theme #pay-credit-section,
        :root.light-theme #pay-cash-section,
        :root.light-theme #pay-bank-section,
        :root.light-theme #pay-cheque-section {
            background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
            color: #000000 !important;
            border: 2px solid #0f172a !important;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12) !important;
        }
        :root.light-theme .modal-overlay h3,
        :root.light-theme .modal-overlay h4,
        :root.light-theme .modal-overlay label,
        :root.light-theme .modal-overlay span:not(.col-custom-color-preview),
        :root.light-theme .modal-overlay .v-label,
        :root.light-theme .modal-overlay .modal-close,
        :root.light-theme #grid-settings-modal h3,
        :root.light-theme #grid-settings-modal h4,
        :root.light-theme #grid-settings-modal label,
        :root.light-theme #grid-settings-modal span:not(.col-custom-color-preview),
        :root.light-theme #grid-settings-modal button:not([onclick*="saveAndApplyGridSettingsToDB"]),
        :root.light-theme #grid-settings-modal #col-settings-list button,
        :root.light-theme #share-popup-menu button,
        :root.light-theme #share-popup-menu span,
        :root.light-theme #auto-send-purchase-dropdown-menu span,
        :root.light-theme #payment-panel label,
        :root.light-theme #payment-panel span:not(.pay-toggle-btn.active *),
        :root.light-theme #pay-credit-section span,
        :root.light-theme #pay-cash-section div,
        :root.light-theme #pay-bank-section div,
        :root.light-theme #pay-cheque-section div,
        :root.light-theme #purchase-hub-view th,
        :root.light-theme #purchase-hub-view td,
        :root.light-theme #purchase-hub-view td span,
        :root.light-theme #hub-summary-bar,
        :root.light-theme #hub-summary-bar span,
        :root.light-theme #hub-summary-bar strong,
        :root.light-theme .smart-filter-bar span,
        :root.light-theme .hub-time-btn {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            text-shadow: none !important;
        }
        :root.light-theme #grid-settings-modal #col-settings-list button {
            background: #dfd2bc !important;
            border: 1.5px solid #78350f !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #grid-settings-modal #col-settings-list button:hover:not(:disabled) {
            background: #f59e0b !important;
            border-color: #000000 !important;
        }
        :root.light-theme .modal-overlay input:not([type="color"]):not([type="range"]),
        :root.light-theme .modal-overlay select,
        :root.light-theme #grid-settings-modal input:not([type="color"]):not([type="range"]),
        :root.light-theme #grid-settings-modal select,
        :root.light-theme #pay-credit-amount,
        :root.light-theme #pay-credit-due-date,
        :root.light-theme #pay-cash-amount,
        :root.light-theme #hdr-payment-account,
        :root.light-theme #pay-cash-ref,
        :root.light-theme #pay-cash-note,
        :root.light-theme #bank-transfers-list input,
        :root.light-theme #bank-transfers-list select,
        :root.light-theme #cheques-list input,
        :root.light-theme #cheques-list select,
        :root.light-theme #hub-filter-type,
        :root.light-theme #hub-filter-value {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2px solid #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme .custom-dropdown-list {
            background: #ffffff !important;
            border: 2px solid #000000 !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18) !important;
        }
        :root.light-theme .custom-dropdown-item,
        :root.light-theme .custom-dropdown-item span,
        :root.light-theme .custom-dropdown-item div,
        :root.light-theme .custom-dropdown-item strong {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 800 !important;
            border-bottom-color: #e2e8f0 !important;
        }
        :root.light-theme .custom-dropdown-item:hover,
        :root.light-theme .custom-dropdown-item.selected {
            background: #e2d5be !important;
            color: #000000 !important;
        }
        /* Child Menus & OCR Review Modal Contrast (Light & Dark Themes) */
        :root.light-theme #ocr-review-modal > div,
        html[data-theme="warm_cream"] #ocr-review-modal > div,
        html[data-theme="ultra_white"] #ocr-review-modal > div,
        html[data-theme="exec_gray"] #ocr-review-modal > div,
        :root.light-theme #share-popup-menu,
        html[data-theme="warm_cream"] #share-popup-menu,
        html[data-theme="ultra_white"] #share-popup-menu,
        html[data-theme="exec_gray"] #share-popup-menu,
        :root.light-theme #auto-send-purchase-dropdown-menu,
        html[data-theme="warm_cream"] #auto-send-purchase-dropdown-menu,
        html[data-theme="ultra_white"] #auto-send-purchase-dropdown-menu,
        html[data-theme="exec_gray"] #auto-send-purchase-dropdown-menu,
        :root.light-theme #col-context-menu,
        html[data-theme="warm_cream"] #col-context-menu,
        html[data-theme="ultra_white"] #col-context-menu,
        html[data-theme="exec_gray"] #col-context-menu {
            background: #f5f0e6 !important;
            color: #000000 !important;
            border: 2px solid #94a3b8 !important;
        }
        :root.light-theme #ocr-review-modal h3,
        :root.light-theme #ocr-review-modal p,
        :root.light-theme #ocr-review-modal td,
        :root.light-theme #ocr-review-modal th,
        :root.light-theme #share-popup-menu button,
        :root.light-theme #share-popup-menu span,
        :root.light-theme #auto-send-purchase-dropdown-menu span,
        :root.light-theme #col-context-menu div {
            color: #000000 !important;
            font-weight: 800 !important;
        }
        :root.light-theme #purchase-ai-audit-modal > div,
        :root.light-theme #price-history-overlay .modal-card,
        :root.light-theme #barcode-scanner-modal > div,
        :root.light-theme #pur-light-tone-popover {
            background: linear-gradient(145deg, #faf6ee 0%, #f3e9d6 100%) !important;
            color: #140c06 !important;
            border: 2px solid #9a5822 !important;
            box-shadow: 0 18px 45px rgba(120, 53, 15, 0.22) !important;
        }
        :root.light-theme #purchase-ai-audit-modal > div > div:first-child,
        :root.light-theme #purchase-ai-audit-modal > div > div:last-child,
        :root.light-theme # purchase-ai-audit-modal thead tr,
        :root.light-theme #purchase-ai-audit-modal thead tr,
        :root.light-theme #price-history-overlay thead tr {
            background: linear-gradient(180deg, #f3e9d6 0%, #eadbc2 100%) !important;
            border-color: #9a5822 !important;
            color: #140c06 !important;
        }
        :root.light-theme #purchase-ai-audit-modal div,
        :root.light-theme #purchase-ai-audit-modal span,
        :root.light-theme #purchase-ai-audit-modal label,
        :root.light-theme #purchase-ai-audit-modal strong,
        :root.light-theme #purchase-ai-audit-modal th,
        :root.light-theme #purchase-ai-audit-modal td,
        :root.light-theme #price-history-overlay th,
        :root.light-theme #price-history-overlay td,
        :root.light-theme #price-history-overlay strong,
        :root.light-theme #price-history-overlay span,
        :root.light-theme #pur-light-tone-popover div,
        :root.light-theme #pur-light-tone-popover span {
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 800 !important;
            text-shadow: none !important;
        }
        :root.light-theme #purchase-ai-audit-modal tbody tr {
            background: #ffffff !important;
            border-bottom: 1.5px solid rgba(154, 88, 34, 0.3) !important;
        }
        :root.light-theme #purchase-ai-audit-modal tbody tr:nth-child(even) {
            background: #fbf7ef !important;
        }
        :root.light-theme #purchase-ai-audit-modal input,
        :root.light-theme #purchase-ai-audit-modal .ai-sell-price-input {
            background: #ffffff !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            border: 2px solid #9a5822 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #purchase-ai-audit-modal button:not([onclick*="applyPurchaseSellingPricesAI"]) {
            background: linear-gradient(180deg, #fffdf9 0%, #f3e5c8 100%) !important;
            border: 2px solid #9a5822 !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
        }

        /* ═══ 7. الدستور الرابع (Constitution 4): +2 Stroke/Border/Icon Thickness, Zero Input Color-Inversion & Stepped Ambient Contrast ═══ */
        /* A. +2 Icon Stroke Width & Bold Typography Inheritance across Main Screen & All Child Modals */
        #purchase-editor-view svg,
        #purchase-hub-view svg,
        .modal-overlay svg,
        #grid-settings-modal svg,
        #ocr-review-modal svg {
            stroke-width: 2.8px !important;
        }
        :root.light-theme #purchase-editor-view .h-label svg,
        :root.light-theme #purchase-hub-view .neo-card svg:not(.hub-action-card svg) {
            stroke: #000000 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view svg,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view svg,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay svg {
            stroke: #ffffff !important;
        }

        /* B. Dark Themes: Pure Black Writing Area (#000000) + Crisp White Text (#FFFFFF) + Soft Slate-Gray Border (1px) + Stepped Slate Surroundings (#162038) — ZERO Inversion on :focus / :active / :-webkit-autofill */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #form-fields-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .purchase-glass-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .panel-header-row,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .smart-filter-bar,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view .neo-card {
            background: #162038 !important;
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #pos-grid tbody tr td {
            background: #162038 !important;
            border: 1px solid rgba(148, 163, 184, 0.14) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view textarea,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay textarea,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view input:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view input:active,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view select:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view textarea:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay input:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) input:-webkit-autofill,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) input:-webkit-autofill:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) input:-webkit-autofill:focus {
            background: #0b1120 !important;
            background-color: #0b1120 !important;
            -webkit-box-shadow: 0 0 0 1000px #0b1120 inset !important;
            border: 1px solid rgba(148, 163, 184, 0.28) !important;
            caret-color: #ffffff !important;
            font-weight: 900 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view input:not(.is-zero-val),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view input:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view select:not(.is-default-hint),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view select:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view textarea,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) input:-webkit-autofill,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) input:-webkit-autofill:focus {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        /* C. Light & White Themes: Pure White Writing Area (#FFFFFF) + Deep Pitch-Black Text & Soft Gray Border (1px) + Stepped Beige Surroundings (#f5efe2) */
        :root.light-theme #purchase-editor-view input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        :root.light-theme #purchase-editor-view select,
        :root.light-theme #purchase-editor-view textarea,
        :root.light-theme #purchase-hub-view input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme #purchase-hub-view select,
        :root.light-theme .modal-overlay input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        :root.light-theme .modal-overlay select,
        :root.light-theme .modal-overlay textarea,
        :root.light-theme #purchase-editor-view input:focus,
        :root.light-theme #purchase-editor-view select:focus,
        :root.light-theme input:-webkit-autofill,
        :root.light-theme input:-webkit-autofill:focus,
        html[data-theme="warm_cream"] #purchase-editor-view input:not([type="checkbox"]):not([type="radio"]),
        html[data-theme="ultra_white"] #purchase-editor-view input:not([type="checkbox"]):not([type="radio"]),
        html[data-theme="exec_gray"] #purchase-editor-view input:not([type="checkbox"]):not([type="radio"]) {
            background: #ffffff !important;
            background-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
            border: 1px solid #cbd5e1 !important;
            caret-color: #000000 !important;
            font-weight: 900 !important;
        }
    </style>
    <script id="invoice-faint-zero-hint-engine">
        (function() {
            function isZeroLike(val) {
                if (val === null || val === undefined) return true;
                const s = String(val).trim();
                if (s === '' || s === '—' || s === '-') return true;
                const cleaned = s.replace(/,/g, '');
                if (!/^-?\d+(\.\d+)?$/.test(cleaned)) return false;
                return parseFloat(cleaned) === 0;
            }
            window.syncInvoiceFaintHintsAndZeros = function(root) {
                const scope = root && root.querySelectorAll ? root : document;
                // 1. Numeric inputs in grid and totals footer
                const numInputs = scope.querySelectorAll('#pos-grid input.g-input:not([id^="item-search-"]), .tot-row input.tot-input');
                numInputs.forEach(inp => {
                    if (isZeroLike(inp.value)) inp.classList.add('is-zero-val');
                    else inp.classList.remove('is-zero-val');
                });
                // 2. Readonly total / available spans
                const spans = scope.querySelectorAll('#pos-grid .total-val, #pos-grid .avail-val');
                spans.forEach(sp => {
                    if (isZeroLike(sp.textContent)) sp.classList.add('is-zero-val');
                    else sp.classList.remove('is-zero-val');
                });
                // 3. Warehouse & Cost Center selects with default hint ("نفس الفاتورة ↩", "-- بدون مركز --")
                const selects = scope.querySelectorAll('#pos-grid select.g-select'); const ccEl = document.getElementById('hdr-cost-center'); if (ccEl) ccEl.classList.remove('is-default-hint');
                selects.forEach(sel => {
                    if (!sel.value || String(sel.value).trim() === '') sel.classList.add('is-default-hint');
                    else sel.classList.remove('is-default-hint');
                });
            };
            document.addEventListener('DOMContentLoaded', function() {
                window.syncInvoiceFaintHintsAndZeros(document);
                document.addEventListener('input', function(e) {
                    if (e.target && (e.target.matches('#pos-grid input.g-input, .tot-row input.tot-input, #pos-grid select.g-select, #hdr-cost-center'))) {
                        window.syncInvoiceFaintHintsAndZeros(document);
                    }
                }, true);
                document.addEventListener('change', function(e) {
                    if (e.target && (e.target.matches('#pos-grid input.g-input, .tot-row input.tot-input, #pos-grid select.g-select, #hdr-cost-center'))) {
                        window.syncInvoiceFaintHintsAndZeros(document);
                    }
                }, true);
                document.addEventListener('blur', function() {
                    setTimeout(function() { window.syncInvoiceFaintHintsAndZeros(document); }, 30);
                }, true);
                const gridBody = document.getElementById('grid-body');
                if (gridBody && window.MutationObserver) {
                    const obs = new MutationObserver(function() {
                        window.syncInvoiceFaintHintsAndZeros(gridBody);
                    });
                    obs.observe(gridBody, { childList: true, subtree: true, characterData: true });
                }
            });
        })();
    </script>
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

    <!-- ═══ PURCHASE HUB VIEW ═══ -->
    <div id="purchase-hub-view" style="display: flex; flex-direction: column; flex: 1; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 10px 0; max-width: 100%; margin: 0; overflow-y: auto;">
        
        <!-- Action Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">

            <!-- Direct Purchase Invoice -->
            <button onclick="openPurchaseEditor('direct')" class="hub-action-card card-orange">
                <div class="icon">💵</div>
                <div class="text-content">
                    <div class="title">فاتورة مشتريات</div>
                </div>
            </button>

            <!-- Goods Receipt Note -->
            <button onclick="window.location.href='purchase-receipt.php'" class="hub-action-card card-blue">
                <div class="icon">🚛</div>
                <div class="text-content">
                    <div class="title">استلام بضاعة</div>
                </div>
            </button>

            <!-- Unified Smart Logistics Portal -->
            <button onclick="window.location.href='smart-logistics.php?mode=purchases'" class="hub-action-card card-teal" style="border-color: rgba(56,189,248,0.55); box-shadow: 0 0 22px rgba(14,165,233,0.3);">
                <div class="icon">📥</div>
                <div class="text-content">
                    <div class="title">بوابة استلام المشتريات</div>
                </div>
            </button>



            <!-- Purchase Return -->
            <button onclick="window.location.href='purchase-return.php'" class="hub-action-card card-pink">
                <div class="icon">↩️</div>
                <div class="text-content">
                    <div class="title">استرداد بضاعة</div>
                </div>
            </button>

            <!-- Proforma Invoice -->
            <button onclick="openPurchaseEditor('proforma')" class="hub-action-card card-purple">
                <div class="icon">📋</div>
                <div class="text-content">
                    <div class="title">فاتورة مبدئية</div>
                </div>
            </button>

            <!-- Supplier Reliability 360 Card -->
            <button onclick="window.location.href='supplier_reliability.php'" class="hub-action-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.35)); border: 1px solid rgba(251, 191, 36, 0.5); cursor: pointer;">
                <div class="icon" style="filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.6));">🤝</div>
                <div class="text-content">
                    <div class="title" style="color: #fbbf24; font-weight: 800;">اعتمادية الموردين 360°</div>
                </div>
            </button>

            <!-- OCR Scanner Card -->
            <button onclick="triggerInvoiceOCR()" class="hub-action-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.35)); border: 1px solid rgba(52, 211, 153, 0.5); cursor: pointer;">
                <div class="icon" style="filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.6));">📷</div>
                <div class="text-content">
                    <div class="title" style="color: #34d399; font-weight: 800;">مسح فاتورة</div>
                </div>
            </button>

            <!-- AI Price Spike & Selling Margin Radar -->
            <button onclick="if(window.openPurchaseAIAuditModal) window.openPurchaseAIAuditModal();" class="hub-action-card" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.22), rgba(168, 85, 247, 0.38)); border: 1px solid rgba(168, 85, 247, 0.55); cursor: pointer;">
                <div class="icon" style="filter: drop-shadow(0 0 8px rgba(168, 85, 247, 0.6));">💰</div>
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
                    <button type="button" onclick="if(typeof openGridSettingsModal==='function') openGridSettingsModal();" style="height: 34px; padding: 0 13px; border-radius: 9px; background: linear-gradient(135deg, #0284c7, #0369a1); border: 2px solid #7dd3fc; color: #ffffff !important; font-size: 0.85rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">⚙️ التخصيص</button>
                </div>
                <div id="hub-summary-bar" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 0.9rem; font-weight: 900;"></div>
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
    <div id="purchase-editor-view" style="display: none; flex-direction: column; flex: 1; overflow-y: auto; padding-bottom: 30px;">
    

    <!-- Form Fields (Now first in DOM, rendering on the Right in RTL) -->
        
<!-- WRAPPER FOR TOP SECTION -->
<style id="purchase-worldclass-ux-styles">
    /* ═══ WORLD-CLASS PURCHASE HEADER & BALANCED TOP BAR HARMONY ═══ */
    #purchase-top-container #form-fields-card {
        padding: 12px 14px !important;
        border-radius: 14px !important;
        background: linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(18, 16, 32, 0.92)) !important;
        border: 1px solid rgba(56, 189, 248, 0.22) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
    }
    /* Row 0: Unified 40px Bar for Invoice No, Date, User Badge & Supplier Financial Strip */
    #purchase-row0-bar {
        display: flex !important;
        width: 100% !important;
        gap: 10px !important;
        margin-bottom: 0 !important;
        align-items: center !important;
        min-height: 40px !important;
    }
    #invoice-info-box {
        flex: 0 0 auto !important;
        display: flex !important;
        gap: 8px !important;
        width: auto !important;
        min-height: 40px !important;
        max-height: 40px !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 2px 10px !important;
        border-radius: 10px !important;
        background: rgba(15, 23, 42, 0.55) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-sizing: border-box !important;
    }
    #invoice-info-box .group-invoice {
        flex: 0 0 auto !important;
        min-width: 172px !important;
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
        margin: 0 !important;
        height: 36px !important;
    }
    #invoice-info-box #hdr-inv-label,
    #invoice-info-box #hdr-date-label {
        margin: 0 !important;
        font-size: 0.8rem !important;
        font-weight: 800 !important;
        color: #cbd5e1 !important;
        white-space: nowrap !important;
    }
    #invoice-info-box .pro-serial-container {
        flex: 1 !important;
        min-width: 112px !important;
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0 5px !important;
        border-radius: 9px !important;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.75)) !important;
        border: 1.5px solid rgba(56, 189, 248, 0.45) !important;
        box-shadow: inset 0 0 12px rgba(56, 189, 248, 0.12), 0 2px 8px rgba(0, 0, 0, 0.25) !important;
        box-sizing: border-box !important;
    }
    #invoice-info-box #inv-number {
        font-size: 1.32rem !important;
        font-family: 'Outfit', 'JetBrains Mono', sans-serif !important;
        font-weight: 900 !important;
        color: #38bdf8 !important;
        text-shadow: 0 0 10px rgba(56, 189, 248, 0.35) !important;
        letter-spacing: 0.4px !important;
        white-space: nowrap !important;
        text-align: center !important;
        flex: 1 !important;
        line-height: 1 !important;
        padding: 0 4px !important;
        direction: ltr !important;
    }
    #invoice-info-box .group-date {
        flex: 0 0 auto !important;
        min-width: 142px !important;
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
        margin: 0 !important;
        height: 36px !important;
    }
    #invoice-info-box #hdr-date {
        flex: 1 !important;
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        text-align: center !important;
        padding: 0 6px !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 800 !important;
        font-size: 0.95rem !important;
        border-radius: 9px !important;
        box-sizing: border-box !important;
    }
    /* User Badge hidden in Top Row (relying on bottom status bar user name) */
    #purchase-user-audit-container {
        display: none !important;
    }
    #customer-financial-card {
        flex: 1 1 0% !important;
        min-width: 0 !important;
        width: auto !important;
        opacity: 1 !important;
        display: flex !important;
    }
    /* Financial Strip matching exact 40px height */
    #customer-financial-card .fin-strip-inner {
        width: 100% !important;
        height: 40px !important;
        min-height: 40px !important;
        max-height: 40px !important;
        background: linear-gradient(90deg, rgba(15, 23, 42, 0.85), rgba(30, 58, 138, 0.35)) !important;
        border: 1px solid rgba(56, 189, 248, 0.5) !important;
        border-radius: 10px !important;
        padding: 0 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 6px !important;
        box-shadow: 0 4px 14px rgba(0,0,0,0.22), inset 0 1px 2px rgba(255,255,255,0.06) !important;
        box-sizing: border-box !important;
        white-space: nowrap !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
    }
    #customer-financial-card .fin-strip-inner::-webkit-scrollbar {
        display: none !important;
    }
    #customer-financial-card .fin-metric-pill {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 4px !important;
        white-space: nowrap !important;
    }
    /* Row 1 & Row 2: Comfortable 42px Controls + Aligned Labels */
    #purchase-row1-bar,
    #purchase-row2-bar {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        gap: 12px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
        box-sizing: border-box !important;
    }
    #purchase-row1-bar .h-form-group,
    #purchase-row2-bar .h-form-group {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        height: 42px !important;
        min-height: 42px !important;
        max-height: 42px !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }
    #purchase-row1-bar .h-input-wrapper,
    #purchase-row2-bar .h-input-wrapper {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        height: 42px !important;
        flex: 1 !important;
        min-width: 0 !important;
    }
    #purchase-row1-bar .custom-dropdown,
    #purchase-row1-bar .custom-dropdown-input-wrap {
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        position: relative !important;
    }
    #purchase-row1-bar .h-label,
    #purchase-row2-bar .h-label {
        font-size: 0.85rem !important;
        font-weight: 800 !important;
        min-width: auto !important;
        margin: 0 !important;
        white-space: nowrap !important;
        letter-spacing: 0 !important;
        line-height: 42px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
    }
    #purchase-row1-bar .group-supplier > .h-label,
    #purchase-row2-bar .group-description > .h-label {
        min-width: 64px !important;
    }
    #purchase-row1-bar .ctrl-input,
    #purchase-row2-bar .ctrl-input {
        height: 42px !important;
        min-height: 42px !important;
        max-height: 42px !important;
        border-radius: 9px !important;
        box-sizing: border-box !important;
    }
    #hdr-customer-ledger-btn.ledger-btn-2027 {
        position: absolute !important;
        left: 6px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        height: 26px !important;
        min-height: 26px !important;
        max-height: 26px !important;
        padding: 0 9px !important;
        font-size: 0.74rem !important;
        font-weight: 800 !important;
        border-radius: 6px !important;
        z-index: 12 !important;
        margin: 0 !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        width: auto !important;
        animation: none !important;
    }
    #hdr-customer-ledger-btn.ledger-btn-2027:hover {
        transform: translateY(-50%) scale(1.03) !important;
        animation: none !important;
    }
    #hdr-exchange-rate {
        font-size: 1.35rem !important;
        font-weight: 900 !important;
        font-family: 'Outfit', 'JetBrains Mono', sans-serif !important;
        letter-spacing: 0.5px !important;
        color: #10b981 !important;
        background: rgba(16, 185, 129, 0.12) !important;
        border: 1px solid rgba(16, 185, 129, 0.35) !important;
        text-shadow: 0 0 8px rgba(16, 185, 129, 0.25) !important;
    }
</style>
<div id="purchase-top-container" class="form-expanded" style="display: flex; gap: 0; align-items: stretch; transition: all 0.5s ease; width: 100%; margin: 0; padding: 0;">
<div class="neo-card expanded-form purchase-glass-card" id="form-fields-card" style="margin: 0; width: 100%; box-sizing: border-box;">
            <div style="display: flex; flex-direction: column; gap: 9px;">
                
                <!-- ROW 0 WRAPPER (Unified Slim Level: Invoice No, Date, User, Financial Strip) -->
                <div id="purchase-row0-bar">
                    <!-- RIGHT IN RTL: Invoice, Date & Slim User Badge -->
                    <div id="invoice-info-box"><div id="purchase-screen-title-badge" class="m7-solid-badge-guard" style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; height: 34px; padding: 0 12px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0369a1); border: 1.5px solid #38bdf8; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-size: 0.92rem; font-weight: 900; white-space: nowrap; flex-shrink: 0; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);"><span>🛍️</span><span>مشتريات</span></div><div style="width: 1px; height: 24px; background: rgba(255,255,255,0.12); margin: 0 2px; flex-shrink: 0;"></div>
                        
                        <div class="h-form-group group-invoice">
                            <label id="hdr-inv-label" class="h-label">الرقم:</label>
                            <div class="pro-serial-container">
                                <button class="pro-serial-btn" type="button" onclick="loadPreviousInvoice()" title="السابقة" style="background: rgba(56,189,248,0.1); border: none; border-radius: 6px; color: #38bdf8; font-size: 0.95rem; cursor: pointer; width: 24px; height: 24px; display:flex; align-items:center; justify-content:center; opacity:0.85; transition:0.2s;" onmouseover="this.style.opacity=1; this.style.background='rgba(56,189,248,0.22)'" onmouseout="this.style.opacity=0.85; this.style.background='rgba(56,189,248,0.1)'">❮</button>
                                <span id="inv-number" data-auto-fit-voucher="1" class="pro-serial-number">---</span>
                                <input type="hidden" id="hdr-inv-no">
                                <button class="pro-serial-btn" type="button" onclick="loadNextInvoice()" title="التالية" style="background: rgba(56,189,248,0.1); border: none; border-radius: 6px; color: #38bdf8; font-size: 0.95rem; cursor: pointer; width: 24px; height: 24px; display:flex; align-items:center; justify-content:center; opacity:0.85; transition:0.2s;" onmouseover="this.style.opacity=1; this.style.background='rgba(56,189,248,0.22)'" onmouseout="this.style.opacity=0.85; this.style.background='rgba(56,189,248,0.1)'">❯</button>
                            </div>
                        </div>

                        <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.12); margin: 0 2px; flex-shrink: 0;"></div>

                        <div class="h-form-group group-date">
                            <label class="h-label" id="hdr-date-label">التاريخ:</label>
                            <input type="date" class="v-input ctrl-input" id="hdr-date">
                        </div>

                        <div id="purchase-user-audit-container" style="display: none !important;"></div>
                    </div>
                    
                    <!-- LEFT IN RTL: Slim Single-Line Financial Strip (Takes all remaining row space) -->
                    <div id="customer-financial-card" class="fin-strip-inline" style="flex: 1 1 0%; min-width: 0; width: auto; opacity: 1; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center;">
                        <div class="fin-strip-inner">
                            <button type="button" id="fin-strip-currency-btn" onclick="window.toggleFinStripCurrency && window.toggleFinStripCurrency()" title="اضغط للتبديل بين الجنيه السوداني والدولار" style="background: linear-gradient(180deg, #0284c7 0%, #1d4ed8 100%); border: 1.5px solid #7dd3fc; color: #ffffff; border-radius: 7px; padding: 0 10px; height: 28px; min-width: 78px; font-family: 'Cairo', sans-serif; font-size: 0.85rem; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-align: center; line-height: 1; white-space: nowrap; flex-shrink: 0; box-shadow: 0 2px 8px rgba(2,132,199,0.45); text-shadow: 0 1.5px 3px rgba(0,0,0,0.95); transition: all 0.2s ease;">جنيه ⇄</button><div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.22); flex-shrink:0;"></div><div id="fin-wrap-ledger" class="fin-metric-pill" style="background: rgba(15,23,42,0.78); padding: 2px 8px; border-radius: 6px; border: 1.5px solid rgba(148,163,184,0.65);">
                                <span class="fin-lbl" style="font-size:0.76rem; color:#ffffff; font-weight:900;">الرصيد:</span>
                                <span id="fin-ledger" class="fin-val" style="font-weight:900; color:#ffffff; font-size:0.86rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                            <div id="fin-wrap-debt" class="fin-metric-pill" style="background: rgba(127,29,29,0.55); padding: 2px 8px; border-radius: 6px; border: 1.5px solid rgba(251,113,133,0.75);">
                                <span class="fin-lbl" style="font-size:0.76rem; color:#ffffff; font-weight:900;">المديونية:</span>
                                <span id="fin-total-debt" class="fin-val" style="font-weight:900; color:#fecdd3; font-size:0.86rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                            <div id="fin-wrap-checks" class="fin-metric-pill" style="background: rgba(120,53,15,0.58); padding: 2px 8px; border-radius: 6px; border: 1.5px solid rgba(251,191,36,0.78);">
                                <span class="fin-lbl" style="font-size:0.76rem; color:#ffffff; font-weight:900;">الشيكات:</span>
                                <span id="fin-checks" class="fin-val" style="font-weight:900; color:#fef08a; font-size:0.86rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                            <div id="fin-wrap-limit" class="fin-metric-pill" style="background: rgba(12,74,110,0.58); padding: 2px 8px; border-radius: 6px; border: 1.5px solid rgba(56,189,248,0.78);">
                                <span class="fin-lbl" style="font-size:0.76rem; color:#ffffff; font-weight:900;">الحد:</span>
                                <span id="fin-credit-limit" class="fin-val" style="font-weight:900; color:#bae6fd; font-size:0.86rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                            <div id="fin-wrap-remaining" class="fin-metric-pill" style="background: rgba(6,78,59,0.65); padding: 2px 8px; border-radius: 6px; border: 1.5px solid rgba(52,211,153,0.85);">
                                <span class="fin-lbl" style="font-size:0.76rem; color:#ffffff; font-weight:900;">✨ المتاح:</span>
                                <span id="fin-remaining" class="fin-val" style="font-weight:900; color:#a7f3d0; font-size:0.86rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div id="supplier-performance-rating-pill" style="display:none !important;"><span id="supplier-rating-text"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Unified Row 1: Supplier (Expanded + Ledger Inside + Plus + Last Invoice), Exchange Rate (Expanded), Warehouse (All on 1 Horizontal Level) -->
                <div id="purchase-row1-bar" style="display: flex; gap: 12px; flex-wrap: nowrap; margin-bottom: 0; width: 100%; align-items: center;">
                    <!-- 1. Supplier (Wider allocation + auto-fit supplier name + Ledger button strictly inside input) -->
                    <div class="h-form-group group-supplier" style="flex: 5.0; min-width: 430px; display: flex; align-items: center; gap: 8px; flex-direction: row !important; margin: 0;">
                          <label class="h-label" style="color: #fde047;">المورد</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                              <div class="custom-dropdown" style="flex:1; min-width: 0;">
                                  <div class="custom-dropdown-input-wrap" style="position: relative; width: 100%;">
                                      <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم المورد..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width:100%; height:42px !important; padding-left: 88px !important; padding-right: 12px !important; color: #fde047; font-size: 1.15rem; font-weight: 900; background-color: rgba(251,191,36,0.06); border: 1px solid rgba(148,163,184,0.32);" autocomplete="one-time-code">
                                      <button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('hdr-customer').value)" class="ledger-btn-2027" title="دفتر الأستاذ العام">أستاذ</button>
                                      <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();">
                                      <div class="custom-dropdown-list" id="customer-dropdown-list">
                                          <!-- Populated via JS -->
                                      </div>
                                  </div>
                              </div>
                              <button type="button" id="btn-quick-add-supplier" class="add-row-btn" onclick="openModal('add-customer-modal')" title="إضافة مورد جديد (F4)" style="flex-shrink: 0; width: 42px; height: 42px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                                  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                              </button>
                              <button type="button" id="btn-clone-last-supplier-inv" class="add-row-btn" onclick="window.cloneLastSupplierPurchaseInvoice()" title="نسخ أصناف آخر فاتورة للمورد بضغطة واحدة" style="flex-shrink: 0; height: 42px; padding: 0 14px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; font-size: 0.88rem; font-weight: 900; white-space: nowrap;">
                                  <span>آخر فاتورة</span>
                              </button>
                          </div>
                    </div>
                    
                    <!-- 2. Currency & Exchange Rate (Compact & balanced with Warehouse) -->
                    <div class="h-form-group group-currency" style="flex: 1.42; min-width: 168px; max-width: 205px; display: flex; align-items: center; gap: 5px; flex-direction: row !important; margin: 0;">
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 5px; flex: 1; min-width: 0;">
                            <select class="v-input ctrl-input system-currency-select" id="hdr-currency" title="اضغط لتغيير العملة (جنيه سوداني / دولار)" onchange="handleCurrencyChange(this.value); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="flex: 0.95; min-width: 86px; max-width: 102px; height: 42px !important; padding: 0 6px !important; font-size: 0.96rem !important; font-weight: 900 !important; text-align: center !important; text-align-last: center !important; line-height: 36px !important;">
                                <option value="SDG">🇸🇩 جنيه</option>
                                <option value="USD">🇺🇸 دولار</option>
                                <option value="SAR">🇸🇦 ريال</option>
                                <option value="AED">🇦🇪 درهم</option>
                                <option value="EGP">🇪🇬 مصري</option>
                            </select>
                            <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" placeholder="الصرف" readonly style="flex: 0.88; min-width: 74px; max-width: 92px; height: 42px !important; text-align: center; cursor:pointer; padding: 0 4px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; this.dataset.userEdited='1'; if(window.autoFitHeaderFields) window.autoFitHeaderFields(); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
                        </div>
                    </div>

                    <!-- 3. Warehouse (Expanded with space reclaimed from Currency & Rate) -->
                    <div class="h-form-group group-warehouse" style="flex: 2.88; min-width: 260px; display: flex; align-items: center; gap: 7px; flex-direction: row !important; margin: 0;">
                          <label class="h-label">📦 المخزن</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                            <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width: 100%; height: 42px !important; font-weight: 800; padding: 0 10px;"></select>
                          </div>
                    </div>
                </div>

                <div id="supplier-credit-limit-alert-bar" style="display:none; margin-top:2px; padding:5px 10px; border-radius:8px; background:rgba(239,68,68,0.14); border:1px solid rgba(239,68,68,0.45); color:#fca5a5; font-size:0.76rem; font-weight:900; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;"></div>

                <!-- Row 2: Description + Cost Center at the end -->
                <div id="purchase-row2-bar" style="display: flex; gap: 12px; flex-wrap: nowrap; margin-bottom: 0; width: 100%; align-items: center;">
                    <div class="h-form-group group-description" style="flex: 3.6; min-width: 280px; display:flex; align-items:center; gap:8px; flex-direction: row !important; margin: 0;">
                        <label class="h-label" style="margin: 0; white-space: nowrap; font-size: 0.85rem !important; font-weight: 800 !important;">📝 البيان</label>
                        <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1;">
                            <input type="text" class="v-input ctrl-input" id="hdr-description" placeholder="ملاحظات، أرقام مرجعية..." style="width: 100%; height: 42px !important; min-height: 42px !important; border-radius: 9px !important;">
                        </div>
                    </div>

                    <!-- 4. Cost Center (Moved to the end of Description Row) -->
                    <div class="h-form-group group-cost-center" style="flex: 1.55; min-width: 205px; display: flex; align-items: center; gap: 7px; flex-direction: row !important; margin: 0;">
                        <label class="h-label">🏷️ مركز الكلفة</label>
                        <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                            <select class="v-input ctrl-input" id="hdr-cost-center" onchange="if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width: 100%; height: 42px !important; font-weight: 800; padding: 0 10px;">
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

    <style>
        /* --- PURCHASE GRID 100% FULL WIDTH RESPONSIVE OVERRIDE --- */
        .purchase-bottom {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .purchase-bottom .table-container {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .purchase-bottom .grid-scroll-wrap {
            width: 100% !important;
            min-width: 100% !important;
            overflow-x: auto !important;
            box-sizing: border-box !important;
        }
        #pos-grid {
            width: 100% !important;
            min-width: 900px !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
        }
        #pos-grid thead th {
            white-space: nowrap !important;
            position: relative !important;
        }
        #pos-grid td {
            position: relative !important;
        }
    </style>
    
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
                <button type="button" onclick="if(window.openPurchaseAIAuditModal) window.openPurchaseAIAuditModal();" style="background: linear-gradient(135deg, rgba(168,85,247,0.22), rgba(99,102,241,0.35)); border: 1px solid rgba(168,85,247,0.6); color: #e9d5ff; border-radius: 9px; height: 36px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 800; font-size: 0.85rem; white-space: nowrap; transition: all 0.2s; box-shadow: 0 2px 10px rgba(168,85,247,0.2);" title="الأسعار وهامش البيع">
                    <span>💰</span>
                    <span>الأسعار</span>
                </button>
                <button type="button" onclick="openGridSettingsModal()" style="background: linear-gradient(135deg, rgba(56,189,248,0.18), rgba(37,99,235,0.28)); border: 1px solid rgba(56,189,248,0.55); color: #38bdf8; border-radius: 9px; height: 36px; padding: 0 14px; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-weight: 800; font-size: 0.9rem; white-space: nowrap; transition: all 0.2s; box-shadow: 0 2px 10px rgba(56,189,248,0.2);" title="تخصيص الجدول">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    <span>تخصيص الجدول</span>
                </button>
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
        <style>
            .tot-toggle-btn {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #e2e8f0;
                padding: 6px 12px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 0.9rem;
                transition: all 0.2s;
            }
            .tot-toggle-btn:hover {
                background: rgba(255, 255, 255, 0.1);
            }
            .tot-toggle-btn.active {
                background: rgba(56, 189, 248, 0.2);
                border-color: rgba(56, 189, 248, 0.5);
                color: #38bdf8;
                font-weight: bold;
            }
            .tot-input {
                transition: all 0.2s;
            }
            .tot-row input:focus {
                box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.4);
                outline: none;
            }
            @keyframes totalPulse {
                0% { transform: scale(1); text-shadow: 0 0 0px transparent; }
                50% { transform: scale(1.15); text-shadow: 0 0 15px currentColor; }
                100% { transform: scale(1); text-shadow: 0 0 0px transparent; }
            }
            .animate-total {
                animation: totalPulse 0.4s ease-out;
                display: inline-block;
            }
            body.cur-sdg .hide-if-sdg {
                display: none !important;
            }
        </style>
        <div id="inline-totals-container" style="margin-top: 15px; display: flex; justify-content: space-between; align-items: flex-start; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 15px;">
            
            <!-- Left Side (Visual Right in RTL): Toggles -->
            <div id="toggles-area" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; max-width: 68%;">
                <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('transport')" id="btn-toggle-transport">🚚 ترحيل</button>
                <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('labor')" id="btn-toggle-labor">👷‍♂️ عمالة</button>
                <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('additions')" id="btn-toggle-additions">➕ إضافات</button>
                <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('tax')" id="btn-toggle-tax">💰 ضريبة</button>
                <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('discount')" id="btn-toggle-discount">➖ خصم</button>
                <input type="hidden" id="hdr-landed-cost-mode" value="value">
                <button type="button" id="btn-landed-cost-mode" onclick="window.toggleLandedCostAllocationMode && window.toggleLandedCostAllocationMode()" style="height: 32px; padding: 0 11px; border-radius: 8px; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; font-family: 'Cairo', sans-serif; font-size: 0.76rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease;">
                    <span>⚖️ التوزيع:</span><span id="lbl-landed-cost-mode" style="color:#fde68a;">بالقيمة 💰</span>
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
            <div class="panel" id="payment-panel" style="display: none; z-index: 40; margin-top: 15px; padding: 0 !important; background: rgba(15, 23, 42, 0.95) !important; border: 1px solid rgba(234, 179, 8, 0.22) !important;">
                <style>
                    #payment-panel {
                        animation: none;
                        transition: all 0.4s ease;
                    }
                    @keyframes glassPulseGold {
                        0%, 100% {
                            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37), 0 0 10px rgba(234, 179, 8, 0.18), inset 0 1px 1px rgba(255, 255, 255, 0.05);
                            border-color: rgba(234, 179, 8, 0.22) !important;
                        }
                        50% {
                            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.45), 0 0 20px rgba(234, 179, 8, 0.38), inset 0 1px 1px rgba(255, 255, 255, 0.12);
                            border-color: rgba(234, 179, 8, 0.45) !important;
                        }
                    }
                    .pay-toggle-btn {
                        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                    }
                    .pay-toggle-btn:hover {
                        filter: brightness(1.2);
                        transform: translateY(-2px);
                    }
                    #toggle-credit.active {
                        background: linear-gradient(135deg, #92400e, #b45309) !important;
                        border-color: #b45309 !important;
                        color: #fff !important;
                        box-shadow: 0 6px 16px rgba(180, 83, 9, 0.5) !important;
                    }
                    #toggle-cash.active {
                        background: linear-gradient(135deg, #047857, #059669) !important;
                        border-color: #059669 !important;
                        color: #fff !important;
                        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.5) !important;
                    }
                    #toggle-bank.active {
                        background: linear-gradient(135deg, #0e7490, #0891b2) !important;
                        border-color: #0891b2 !important;
                        color: #fff !important;
                        box-shadow: 0 6px 16px rgba(8, 145, 178, 0.5) !important;
                    }
                    #toggle-cheque.active {
                        background: linear-gradient(135deg, #6d28d9, #7c3aed) !important;
                        border-color: #7c3aed !important;
                        color: #fff !important;
                        box-shadow: 0 6px 16px rgba(124, 58, 237, 0.5) !important;
                    }
                    .payment-summary-card {
                        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                        transform-origin: top;
                        max-height: 0;
                        opacity: 0;
                        overflow: hidden;
                        padding: 0 20px !important;
                        border-color: transparent !important;
                        transform: scaleY(0);
                        flex: 0;
                        min-width: 0 !important;
                        margin: 0;
                    }
                    .payment-summary-card.show-totals {
                        max-height: 350px;
                        opacity: 1;
                        padding: 20px !important;
                        border-color: rgba(255,255,255,0.08) !important;
                        transform: scaleY(1);
                        flex: 1;
                        min-width: 280px !important;
                    }
                    #pay-credit-section {
                        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                        overflow: hidden;
                        max-height: 150px;
                        opacity: 1;
                        transform: scaleY(1);
                        transform-origin: top;
                    }
                    #pay-credit-section.collapsed {
                        max-height: 0 !important;
                        opacity: 0 !important;
                        padding-top: 0 !important;
                        padding-bottom: 0 !important;
                        margin-top: 0 !important;
                        margin-bottom: 0 !important;
                        border-color: transparent !important;
                        transform: scaleY(0) !important;
                        pointer-events: none;
                    }
                    @keyframes errorShake {
                        0%, 100% { transform: translateX(0); }
                        20%, 60% { transform: translateX(-10px); }
                        40%, 80% { transform: translateX(10px); }
                    }
                    .input-error-shake, .input-error-shake:focus {
                        border-color: #ef4444 !important;
                        outline: none !important;
                        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.45) !important;
                        animation: errorShake 0.4s ease-in-out !important;
                    }
                    /* Auto-Expanding heights for payment containers */
                    #payment-panel,
                    .payment-layout,
                    .payment-details-card,
                    .payment-method-box,
                    #bank-transfers-list,
                    #cheques-list {
                        height: auto !important;
                        min-height: fit-content !important;
                        overflow: visible !important;
                    }
                </style>
                <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 12px 20px; background: rgba(15,23,42,0.4); border-bottom: 1px solid rgba(255,255,255,0.06); border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 0;">
                    <div class="panel-title" style="background: linear-gradient(135deg, #ff4d4d, #f87171); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.15rem; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; margin: 0; white-space: nowrap; flex: none;">
                        <span>💳</span> طريقة وتفاصيل الدفع
                    </div>
                    
                    <!-- Sleek Payment Progress Bar (Inline) -->
                    <div class="payment-progress-container" style="flex: 1; display: flex; align-items: center; gap: 12px; min-width: 280px; padding: 0 10px;">
                        <div class="progress-bar-wrapper" style="height: 10px; flex: 1; background: rgba(255,255,255,0.05); border-radius: 5px; overflow: hidden; display: flex; box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); min-width: 120px;">
                            <div id="prog-cash" style="width: 0%; height: 100%; background: linear-gradient(90deg, #10b981, #34d399); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(16,185,129,0.3);"></div>
                            <div id="prog-bank" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #60a5fa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(59,130,246,0.3);"></div>
                            <div id="prog-cheque" style="width: 0%; height: 100%; background: linear-gradient(90deg, #8b5cf6, #a78bfa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(139,92,246,0.3);"></div>
                            <div id="prog-credit" style="width: 100%; height: 100%; background: linear-gradient(90deg, #f59e0b, #fbbf24); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(245,158,11,0.3);"></div>
                        </div>
                        <span id="payment-status-badge" style="font-size: 0.78rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; transition: all 0.3s ease; white-space: nowrap;">⏳ آجلة بالكامل</span>
                    </div>

                    <div class="payment-toggles" style="display: flex; gap: 8px; flex: none; flex-wrap: wrap;">
                        <button type="button" class="pay-toggle-btn" id="toggle-credit" onclick="togglePaymentSection('credit')" style="background: rgba(180, 83, 9, 0.1); border: 1px solid rgba(180, 83, 9, 0.35); color: #fcd34d; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">💳 آجل</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.35); color: #6ee7b7; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">💵 نقدي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.35); color: #67e8f9; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">🏦 تحويل</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(147, 51, 234, 0.1); border: 1px solid rgba(147, 51, 234, 0.35); color: #d8b4fe; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">✍️ شيك</button>
                        <button type="button" class="pay-toggle-btn" id="btn-purchase-financial-ocr" onclick="FinancialOCR.openScanner({ target: 'purchase' })" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(16, 185, 129, 0.2)); border: 1.5px solid #06b6d4; color: #67e8f9; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"><span style="font-size: 1.1rem;">📷</span> مسح إشعار</button>
                        <button type="button" class="pay-toggle-btn" onclick="FinancialOCR.openTrainer()" style="background: rgba(147, 51, 234, 0.1); border: 1px solid rgba(147, 51, 234, 0.35); color: #d8b4fe; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px;"><span>🧠</span> تدريب</button>
                    </div>
                </div>
                
                <div class="payment-layout" id="payment-layout" style="display: none; gap: 15px; padding: 15px; flex-wrap: wrap;">
                    <!-- Right Section: Payment Details Forms -->
                    <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Credit payment box (Toggled off by default) -->
                        <div id="pay-credit-section" class="payment-method-box" style="background: radial-gradient(circle at top left, rgba(217, 119, 6, 0.35), rgba(180, 83, 9, 0.15)); border: 3px solid rgba(217, 119, 6, 0.9); box-shadow: 0 0 25px rgba(217, 119, 6, 0.4), inset 0 0 15px rgba(180, 83, 9, 0.3); border-radius: 12px; padding: 12px 16px; display: none; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                            <div class="pay-credit-title-pill" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 175px; padding: 6px 14px; border-radius: 10px; flex: none;">
                                <span style="font-size: 1.25rem; filter: contrast(1.35) brightness(0.9);">💳</span>
                                <span class="pay-credit-lbl-txt" style="font-weight: 900; font-size: 1.08rem; white-space: nowrap;">آجل على الحساب</span>
                            </div>
                            <div style="display: flex; gap: 12px; align-items: center; flex: 1; min-width: 360px; flex-wrap: wrap;">
                                <input type="number" class="v-input ctrl-input" id="pay-credit-amount" value="0.00" step="any" readonly placeholder="المبلغ الآجل..." oninput="if(window.autoFitSingleInput) window.autoFitSingleInput(this);" style="flex: 1.45 1 235px; min-width: 220px; max-width: 350px; width: auto; background: rgba(15,23,42,0.85); border: 2px solid rgba(180, 83, 9, 0.75); height: 46px; text-align: center; font-size: 1.38rem; font-weight: 900; color: #fcd34d !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.35);">
                                
                                <div id="pay-credit-due-group" style="flex: 1.25 1 250px; min-width: 240px; display: flex; align-items: center; gap: 8px;">
                                    <label class="pay-due-lbl-txt" style="font-size: 1.02rem; font-weight: 900; white-space: nowrap; padding: 5px 10px; border-radius: 8px;">📅 تاريخ الاستحقاق:</label>
                                    <input type="date" class="v-input ctrl-input" id="pay-credit-due-date" style="background: rgba(15,23,42,0.75); border: 2px solid rgba(255,255,255,0.22); height: 46px; font-size: 1.18rem; font-weight: 900; text-align: center; padding: 0 8px; flex: 1; min-width: 175px;">
                                </div>
                                
                                <button type="button" class="btn-action pur-promissory-preview-btn" onclick="previewCurrentPromissoryNote()" style="flex: 0.9 1 175px; min-width: 168px; height: 46px; padding: 0 14px; border-radius: 11px; font-size: 1.04rem; cursor: pointer; font-weight: 900; position: relative; overflow: visible; display: inline-flex; align-items: center; justify-content: center; gap: 7px;"><span class="prom-btn-ico" style="font-size: 1.18rem; filter: contrast(1.6) brightness(0.75);">📄</span><span class="prom-btn-txt">معاينة الكمبيالة</span></button>
                            </div>
                        </div>

                        <!-- Cash payment box (Visible by default) -->
                        <div id="pay-cash-section" style="background: radial-gradient(circle at top left, rgba(16, 185, 129, 0.35), rgba(16, 185, 129, 0.15)); border: 3px solid rgba(16, 185, 129, 0.9); box-shadow: 0 0 25px rgba(16, 185, 129, 0.4), inset 0 0 15px rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; display: flex; flex-direction: column; width: 100%;">
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
                        <div id="pay-bank-section" style="display: none; flex-direction: column; background: radial-gradient(circle at top left, rgba(6, 182, 212, 0.35), rgba(6, 182, 212, 0.15)); border: 3px solid rgba(6, 182, 212, 0.9); box-shadow: 0 0 25px rgba(6, 182, 212, 0.4), inset 0 0 15px rgba(6, 182, 212, 0.3); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; width: 100%;">
                            <div id="bank-transfers-list" style="display: flex; flex-direction: column; gap: 0px; width: 100%;"></div>
                        </div>

                        <!-- Cheques box -->
                        <div id="pay-cheque-section" style="display: none; flex-direction: column; background: radial-gradient(circle at top left, rgba(168, 85, 247, 0.35), rgba(147, 51, 234, 0.15)); border: 3px solid rgba(168, 85, 247, 0.9); box-shadow: 0 0 25px rgba(168, 85, 247, 0.4), inset 0 0 15px rgba(147, 51, 234, 0.3); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; width: 100%;">
                            <div id="cheques-list" style="display: flex; flex-direction: column; gap: 0px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Posting Status Bar (visible only in manual posting mode) -->
        <div id="posting-status-bar" style="display: none; align-items: center; justify-content: space-between; padding: 12px 20px; border-radius: 12px; border: 1px solid rgba(251,191,36,0.3); margin-top: 15px; direction: rtl; font-family: 'Tajawal', sans-serif; transition: all 0.3s ease;"></div>

        <style id="purchase-bottom-toolbar-autofit-styles">
            #purchase-editor-view .items-toolbar {
                padding: 10px 12px !important;
            }
            #purchase-editor-view .items-toolbar .toolbar-actions {
                display: flex !important;
                gap: 7px !important;
                width: 100% !important;
                align-items: center !important;
                justify-content: space-between !important;
                flex-wrap: nowrap !important;
            }
            #purchase-editor-view .items-toolbar .toolbar-actions .btn-action,
            #purchase-editor-view .items-toolbar .toolbar-actions #btn-share-invoice {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                min-width: 0 !important;
                padding: 0 9px !important;
                border-radius: 11px !important;
                display: inline-flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 5px !important;
                font-size: clamp(0.74rem, 0.92vw, 0.92rem) !important;
                font-weight: 900 !important;
                white-space: nowrap !important;
                box-sizing: border-box !important;
                line-height: 1 !important;
            }
            #purchase-editor-view .items-toolbar .toolbar-actions .btn-action svg,
            #purchase-editor-view .items-toolbar .toolbar-actions #btn-share-invoice svg:first-of-type {
                width: 15px !important;
                height: 15px !important;
                flex-shrink: 0 !important;
            }
            #purchase-editor-view .items-toolbar .toolbar-actions #btn-share-invoice svg.share-chevron-svg {
                width: 11px !important;
                height: 11px !important;
                flex-shrink: 0 !important;
            }
            #purchase-editor-view #auto-send-control-wrapper-purchase {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                display: inline-flex !important;
                align-items: stretch !important;
                flex: 1.35 1 0% !important;
                min-width: 0 !important;
            }
            #purchase-editor-view #btn-toggle-auto-send-purchase {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                padding: 0 8px !important;
                gap: 4px !important;
                flex: 1 1 auto !important;
                min-width: 0 !important;
                box-sizing: border-box !important;
            }
            #purchase-editor-view #btn-auto-send-options-purchase {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                width: 26px !important;
                min-width: 26px !important;
                padding: 0 !important;
                flex-shrink: 0 !important;
            }
            @media (max-width: 1150px) {
                #purchase-editor-view .items-toolbar .toolbar-actions {
                    flex-wrap: wrap !important;
                }
            }
        </style>
        <div class="items-toolbar" style="display: flex; align-items: center; justify-content: center; padding: 10px 12px; border-radius: 14px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(18, 15, 28, 0.92)); border: 1px solid rgba(56, 189, 248, 0.16); margin-top: 14px; box-shadow: 0 8px 28px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.06);">
            
            <div class="toolbar-actions" style="display: flex; gap: 7px; width: 100%; align-items: center; flex-wrap: nowrap;">
                
                <!-- Exit Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-exit btn-exit" style="flex: 0.78 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px;" onclick="if(typeof showPurchaseHub === 'function' && document.getElementById('purchase-hub-view')) { showPurchaseHub(); } else if(typeof magaieesGoBack === 'function') { magaieesGoBack(); } else { window.location.href='magaiees_unified_dashboard.php'; }" title="الخروج والرجوع لبوابة المشتريات أو الرئيسية">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span class="btn-fit-label">خروج</span>
                </button>

                <!-- Delete / Cancel Button (Isolated & Inline 2-Click Confirmed) -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-delete btn-delete" id="btn-delete-purchase-invoice" style="flex: 0.85 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px; background: linear-gradient(135deg, rgba(225, 29, 72, 0.85), rgba(159, 18, 57, 0.92)) !important; border: 1px solid rgba(251, 113, 133, 0.45) !important; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.28) !important;" onclick="window.handleSafePurchaseDeleteOrClear(this)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span class="del-btn-label btn-fit-label">حذف</span>
                </button>

                <!-- Preview & Print Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-print btn-preview" id="btn-preview-invoice" style="flex: 1.05 1 0%; min-width: 0; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 10px;" onclick="printCurrentInvoice()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span class="btn-fit-label">طباعة</span>
                    <kbd style="font-size:0.62rem; padding:1px 4px; border-radius:4px; background:rgba(0,0,0,0.28); border:1px solid rgba(255,255,255,0.22); font-family:monospace; color:#e2e8f0; flex-shrink:0;">F8</kbd>
                </button>
                
                <!-- Share Button: Professional with dropdown -->
                <div style="position: relative; flex: 1.02 1 0%; min-width: 0;" id="share-btn-wrapper">
                    <button type="button" class="btn-action royal-crystal-btn royal-btn-whatsapp btn-share" id="btn-share-invoice" style="width: 100%; height: 44px; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 9px;" onclick="toggleShareMenu(event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <span class="btn-fit-label">مشاركة</span>
                        <svg class="share-chevron-svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.8; width: 11px !important; height: 11px !important;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <!-- Share Popup Menu -->
                    <div id="share-popup-menu" style="display: none; position: absolute; bottom: 55px; left: 50%; transform: translateX(-50%) scale(0.9); opacity: 0; min-width: 220px; background: linear-gradient(160deg, rgba(30,27,50,0.98), rgba(15,12,30,0.98)); border: 1px solid rgba(139, 92, 246, 0.35); border-radius: 16px; padding: 8px; box-shadow: 0 15px 40px rgba(0,0,0,0.5), 0 0 30px rgba(139, 92, 246, 0.15); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); z-index: 9999; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">
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

                <!-- OCR Scanner Button -->
                <button type="button" class="btn-action royal-crystal-btn btn-ocr" style="flex: 1.05 1 0%; min-width: 0; height: 44px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.85), rgba(37, 99, 235, 0.92)) !important; color: #fff !important; border: 1px solid rgba(125, 211, 252, 0.5) !important; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important; font-weight: 900; border-radius: 11px; cursor: pointer; transition: all 0.25s ease; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 10px;" onmouseover="this.style.boxShadow='0 6px 22px rgba(56, 189, 248, 0.55)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 4px 15px rgba(14, 165, 233, 0.3)'; this.style.transform='none';" onclick="triggerInvoiceOCR()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span class="btn-fit-label">مسح فاتورة</span>
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
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js?v=20261029_fast"></script>
    <script defer src="js/erp-whatsapp-suite.js?v=20261029_fast"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/accounting-engine.js?v=<?php echo file_exists('js/accounting-engine.js') ? filemtime('js/accounting-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/purchase-pro-v5.js?v=<?php echo file_exists('js/purchase-pro-v5.js') ? filemtime('js/purchase-pro-v5.js') : '20261030_v5'; ?>"></script>

    <script>
        // Real-time synchronization of local and foreign adjustments
        window.syncFc = function(name) {
            let localId = '';
            let fcId = '';
            if (name === 'transport') { localId = 't-transport'; fcId = 't-transport-fc'; }
            else if (name === 'labor') { localId = 't-labor'; fcId = 't-labor-fc'; }
            else if (name === 'additions') { localId = 't-additions-loc'; fcId = 't-additions-fc'; }
            else if (name === 'tax') { localId = 't-tax-loc'; fcId = 't-tax-fc'; }
            else if (name === 'discount') { localId = 't-discount-loc'; fcId = 't-discount-fc'; }

            if (!localId || !fcId) return;

            const rateInput = document.getElementById('hdr-exchange-rate');
            let rate = rateInput ? (parseFloat(rateInput.value) || 1) : 1;
            const curEl = document.getElementById('hdr-currency');
            const isBaseSDG = !curEl || curEl.value === 'SDG';

            // If invoice is in SDG, the rate input is locked to 1. Fetch real USD rate for the foreign column.
            if (isBaseSDG && rate === 1) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD) rate = parseFloat(cfg.rateUSD) || 1;
                } catch(e) {}
            }

            const localEl = document.getElementById(localId);
            const fcEl = document.getElementById(fcId);
            
            if (localEl && fcEl) {
                const pNum = typeof window.parseNum === 'function' ? window.parseNum : function(v) { return parseFloat(v.toString().replace(/,/g, '')) || 0; };
                const fNum = typeof window.fmtNum === 'function' ? window.fmtNum : function(v) { return v % 1 === 0 ? v : parseFloat(v.toFixed(4)); };
                
                const col1Val = pNum(localEl.value);
                const col2Val = isBaseSDG ? (rate > 0 ? col1Val / rate : 0) : (col1Val * rate);
                
                fcEl.value = fNum(col2Val);
                if (typeof window.recalcAll === 'function') window.recalcAll();
            }
        };

        window.syncLocal = function(name) {
            let localId = '';
            let fcId = '';
            if (name === 'transport') { localId = 't-transport'; fcId = 't-transport-fc'; }
            else if (name === 'labor') { localId = 't-labor'; fcId = 't-labor-fc'; }
            else if (name === 'additions') { localId = 't-additions-loc'; fcId = 't-additions-fc'; }
            else if (name === 'tax') { localId = 't-tax-loc'; fcId = 't-tax-fc'; }
            else if (name === 'discount') { localId = 't-discount-loc'; fcId = 't-discount-fc'; }

            if (!localId || !fcId) return;

            const rateInput = document.getElementById('hdr-exchange-rate');
            let rate = rateInput ? (parseFloat(rateInput.value) || 1) : 1;
            const curEl = document.getElementById('hdr-currency');
            const isBaseSDG = !curEl || curEl.value === 'SDG';

            // If invoice is in SDG, the rate input is locked to 1. Fetch real USD rate for the foreign column.
            if (isBaseSDG && rate === 1) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD) rate = parseFloat(cfg.rateUSD) || 1;
                } catch(e) {}
            }

            const localEl = document.getElementById(localId);
            const fcEl = document.getElementById(fcId);
            
            if (localEl && fcEl) {
                const pNum = typeof window.parseNum === 'function' ? window.parseNum : function(v) { return parseFloat(v.toString().replace(/,/g, '')) || 0; };
                const fNum = typeof window.fmtNum === 'function' ? window.fmtNum : function(v) { return v % 1 === 0 ? v : parseFloat(v.toFixed(4)); };
                
                const col2Val = pNum(fcEl.value);
                const col1Val = isBaseSDG ? (col2Val * rate) : (rate > 0 ? col2Val / rate : 0);
                
                localEl.value = fNum(col1Val);
                if (typeof window.recalcAll === 'function') window.recalcAll();
            }
        };

        // Purchase Hub Logic
        let currentPurchaseView = 'hub';

        function handleCurrencyChange(val) {
            const rateInput = document.getElementById('hdr-exchange-rate');
            const curSelect = document.getElementById('hdr-currency');
            const prevCurrency = curSelect ? (curSelect.dataset.prevVal || 'SDG') : 'SDG';
            if (curSelect) curSelect.dataset.prevVal = val;
            
            if (!rateInput) return;
            
            // Always enable editing for all currencies (including SDG)
            rateInput.disabled = false;

            // When switching to USD, delete / clear previous SDG prices from existing rows
            if (val === 'USD' && prevCurrency !== 'USD') {
                if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                    rows.forEach(r => {
                        r.price = '';
                        r.fcPrice = '';
                    });
                }
            }
            
            // If current rate is already valid and > 1, and we are switching between SDG and USD, keep it!
            const currentVal = parseFloat(rateInput.value) || 0;
            if (currentVal > 1 && currentVal !== 3000 && rateInput.dataset.userEdited === '1' && (val === 'SDG' || val === 'USD') && (prevCurrency === 'SDG' || prevCurrency === 'USD')) {
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }

            // For SDG, show USD exchange rate (e.g. 3000)
            const targetCurr = (val === 'SDG' ? 'USD' : val);
            
            // Step 1: Try to get rate from local settings function
            let rate = 0;
            if (window.getExchangeRateForCurrency) {
                rate = window.getExchangeRateForCurrency(targetCurr);
            }
            
            if (rate > 0) {
                rateInput.value = rate;
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            
            // Step 2: Try to read directly from localStorage settings (any mode)
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (targetCurr === 'USD' && cfg.rateUSD) { rateInput.value = cfg.rateUSD; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
                if (targetCurr === 'SAR' && cfg.rateSAR) { rateInput.value = cfg.rateSAR; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
                if (targetCurr === 'AED' && cfg.rateAED) { rateInput.value = cfg.rateAED; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
                const dedicatedUsd = parseFloat(localStorage.getItem('erp_rate_usd'));
                if (targetCurr === 'USD' && dedicatedUsd > 0) { rateInput.value = dedicatedUsd; if (typeof renderGrid === 'function') renderGrid(); if (typeof recalcAll === 'function') recalcAll(); return; }
            } catch(e) {}
            
            // Step 3: Use server-injected SYSTEM_USD_RATE immediately without network roundtrip if available
            if (window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 0) {
                const usdRate = parseFloat(window.SYSTEM_USD_RATE);
                window._cachedGlobalRate = usdRate;
                let finalRate = usdRate;
                if (targetCurr === 'SAR') finalRate = parseFloat((usdRate / 3.75).toFixed(4));
                if (targetCurr === 'AED') finalRate = parseFloat((usdRate / 3.67).toFixed(4));
                if (targetCurr === 'EGP') finalRate = parseFloat((usdRate / 50.0).toFixed(4));
                rateInput.value = finalRate;
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            rateInput.placeholder = '⏳ جاري التحميل...';
            
            fetch('api_index.php/exchange-rate')
                .then(r => r.json())
                .then(data => {
                    if (data && data.rate) {
                        const usdRate = parseFloat(data.rate);
                        if (usdRate > 0) {
                            window._cachedGlobalRate = usdRate;
                            let finalRate = usdRate;
                            if (targetCurr === 'SAR') finalRate = parseFloat((usdRate / 3.75).toFixed(4));
                            if (targetCurr === 'AED') finalRate = parseFloat((usdRate / 3.67).toFixed(4));
                            if (targetCurr === 'EGP') finalRate = parseFloat((usdRate / 50.0).toFixed(4));
                            
                            rateInput.value = finalRate;
                            if (typeof renderGrid === 'function') renderGrid();
                            if (typeof recalcAll === 'function') recalcAll();
                            return;
                        }
                    }
                    if (!rateInput.value || parseFloat(rateInput.value) <= 0) {
                        rateInput.value = window.SYSTEM_USD_RATE || 5999;
                    }
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                })
                .catch(() => {
                    if (!rateInput.value || parseFloat(rateInput.value) <= 0) {
                        rateInput.value = window.SYSTEM_USD_RATE || 5999;
                    }
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadHubTransactions();
            const curEl = document.getElementById('hdr-currency');
            if (curEl && typeof handleCurrencyChange === 'function') {
                handleCurrencyChange(curEl.value || 'SDG');
            }
        });

        if (window.APP_READY) {
            loadHubTransactions();
        } else {
            document.addEventListener('DBReady', () => {
                loadHubTransactions();
            });
        }

        window.addEventListener('storage', (ev) => {
            if (!ev.key || ev.key.includes('erp_purchase_invoices_v1')) {
                loadHubTransactions();
            }
        });


        function showPurchaseHub() {
            document.getElementById('purchase-editor-view').style.display = 'none';
            document.getElementById('purchase-hub-view').style.display = 'flex';
            currentPurchaseView = 'hub';
            document.body.classList.remove('proforma-mode');
            loadHubTransactions();
            
            const pageTitleEl = document.querySelector('.header-page-title');
            if (pageTitleEl && window.originalPageTitleHTML) {
                pageTitleEl.innerHTML = window.originalPageTitleHTML;
            }
            
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { 
                    if (document.referrer && document.referrer.indexOf('general-ledger.php') !== -1) { 
                        history.back(); 
                    } else { 
                        window.location.href = 'magaiees_unified_dashboard.php'; 
                    } 
                };
                backBtn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>';
            }
        }

        function openPurchaseEditor(type = 'direct', isEdit = false) {
            document.getElementById('purchase-hub-view').style.display = 'none';
            document.getElementById('purchase-editor-view').style.display = 'flex';
            currentPurchaseView = 'editor';
            
            const pageTitleEl = document.querySelector('.header-page-title');
            if (pageTitleEl) {
                if (!window.originalPageTitleHTML) {
                    window.originalPageTitleHTML = pageTitleEl.innerHTML;
                }
                let badge = document.getElementById('current-sale-type-badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.id = 'current-sale-type-badge';
                    badge.style.background = 'rgba(132, 204, 22, 0.15)';
                    badge.style.border = '1px solid rgba(132, 204, 22, 0.3)';
                    badge.style.padding = '4px 12px';
                    badge.style.borderRadius = '20px';
                    badge.style.fontSize = '0.9rem';
                    badge.style.color = '#bef264';
                    badge.style.boxShadow = '0 0 10px rgba(132, 204, 22, 0.2)';
                    badge.style.marginLeft = '10px';
                    badge.style.marginRight = '10px';
                    badge.style.display = 'none';
                }
            }
            
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { showPurchaseHub(); };
                backBtn.innerHTML = '<span style="font-weight:bold; font-size:0.8rem; padding: 0 4px;">العودة للقائمة</span>';
                backBtn.style.width = 'auto';
                backBtn.style.padding = '0 10px';
            }

            if (type === 'proforma') {
                document.body.classList.add('proforma-mode');
            } else {
                document.body.classList.remove('proforma-mode');
            }
            
            setSaleType(type);
            
            if (!isEdit) {
                if (typeof onSuccessNewInvoice === 'function') {
                    onSuccessNewInvoice(false);
                }
                if (!window.__mdInteractiveGuideActive) {
                    setTimeout(() => {
                        const firstInp = document.getElementById('hdr-customer-search') || document.getElementById('quick-item-search');
                        if (firstInp) {
                            try { firstInp.focus(); } catch (_e) {}
                        }
                    }, 60);
                }
            }
            
            window.dispatchEvent(new Event('resize'));
        }

        // ═══════════════════════════════════════
        // ═══════════════════════════════════════
        //  HUB TRANSACTIONS — Enhanced with Pagination + Dynamic Dual Filters
        // ═══════════════════════════════════════
        let hubCurrentPage = 1;
        const HUB_PAGE_SIZE = 50;
        let hubFilteredInvoices = [];
        let hubTimePeriod = 'all';

        function updateHubFilterValueUI() {
            const filterType = document.getElementById('hub-filter-type').value;
            const container = document.getElementById('hub-filter-value-container');
            if (!container) return;

            container.innerHTML = '';

            const elementWidth = '220px';

            if (filterType === 'all' || filterType === 'invoiceNo') {
                const input = document.createElement('input');
                input.type = 'text';
                input.id = 'hub-filter-value';
                input.className = 'v-input ctrl-input';
                input.style.width = elementWidth;
                input.placeholder = filterType === 'all' ? 'بحث عام...' : 'أدخل رقم الفاتورة...';
                input.oninput = filterHubTransactions;
                container.appendChild(input);
            } else if (filterType === 'warehouse') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                let warehouses = [];
                try { warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || []; } catch(e) {}
                
                let html = '<option value="">كل المخازن</option>';
                html += '<option value="PENDING_RECEIPT">⏳ قيد الاستلام</option>';
                warehouses.forEach(w => {
                    html += `<option value="${w.id}">📦 ${w.name}</option>`;
                });
                select.innerHTML = html;
                container.appendChild(select);
            } else if (filterType === 'status') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                select.innerHTML = `
                    <option value="">كل الحالات</option>
                    <option value="posted">🔒 مرحلة</option>
                    <option value="confirmed">⏳ مؤكدة</option>
                    <option value="draft">📝 مسودة</option>
                `;
                container.appendChild(select);
            } else if (filterType === 'supplier') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                let suppliers = [];
                try { suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []; } catch(e) {}
                
                let html = '<option value="">كل الموردين</option>';
                suppliers.forEach(s => {
                    if (!s.deleted) html += `<option value="${s.id}">👤 ${s.name}</option>`;
                });
                select.innerHTML = html;
                container.appendChild(select);
            } else if (filterType === 'item') {
                const select = document.createElement('select');
                select.id = 'hub-filter-value';
                select.className = 'v-input ctrl-input';
                select.style.width = elementWidth;
                select.style.cursor = 'pointer';
                select.onchange = filterHubTransactions;

                let items = [];
                try { items = JSON.parse(localStorage.getItem('erp_items_v1')) || []; } catch(e) {}
                
                let html = '<option value="">كل الأصناف</option>';
                items.forEach(i => {
                    if (!i.deleted) html += `<option value="${i.id}">🏷️ ${i.name}</option>`;
                });
                select.innerHTML = html;
                container.appendChild(select);
            } else if (filterType === 'date') {
                const input = document.createElement('input');
                input.type = 'date';
                input.id = 'hub-filter-value';
                input.className = 'v-input ctrl-input';
                input.style.width = elementWidth;
                input.onchange = filterHubTransactions;
                container.appendChild(input);
            }
        }

        if (typeof window.fmtNum !== 'function') {
            window.fmtNum = function(val) {
                const n = parseFloat(val) || 0;
                return n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            };
        }

        function loadHubTransactions() {
            const tbody = document.getElementById('hub-transactions-body');
            const emptyState = document.getElementById('hub-empty-state');
            if (!tbody) return;
            
            const savedData = localStorage.getItem('erp_purchase_invoices_v1');
            let invoices = [];
            if (savedData) {
                try { 
                    let parsed = typeof savedData === 'string' ? JSON.parse(savedData) : savedData; 
                    if (Array.isArray(parsed)) {
                        invoices = parsed.filter(inv => inv && !inv.deleted && !inv.isGRN && !inv.isReturn);
                    }
                } catch(e) { invoices = []; }
            }
            
            invoices = [...invoices];
            
            // Apply time period filter
            if (hubTimePeriod !== 'all') {
                const now = new Date();
                const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                invoices = invoices.filter(inv => {
                    const invDate = new Date(inv.timestamp || inv.date);
                    if (isNaN(invDate.getTime())) return false;
                    if (hubTimePeriod === 'today') return invDate >= todayStart;
                    if (hubTimePeriod === 'week') {
                        const weekStart = new Date(todayStart);
                        weekStart.setDate(weekStart.getDate() - 7);
                        return invDate >= weekStart;
                    }
                    if (hubTimePeriod === 'month') {
                        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
                        return invDate >= monthStart;
                    }
                    return true;
                });
            }

            // Load lookup data
            window._hubSuppCache = window._hubSuppCache || { raw: null, obj: [] };
            let suppliers = window._hubSuppCache.obj;
            let rawSupps = localStorage.getItem('erp_suppliers_v1');
            if (rawSupps !== null && window._hubSuppCache.raw !== rawSupps) {
                try { suppliers = typeof rawSupps === 'string' ? JSON.parse(rawSupps) : rawSupps; } catch(e) { suppliers = []; }
                window._hubSuppCache.raw = rawSupps;
                window._hubSuppCache.obj = suppliers || [];
            }
            function getSupplierName(id) { const s = suppliers.find(x => String(x.id) === String(id)); return s ? s.name : id; }

            window._hubWhCache = window._hubWhCache || { raw: null, obj: [] };
            let warehouses = window._hubWhCache.obj;
            let rawWh = localStorage.getItem('erp_warehouses_v1');
            if (rawWh !== null && window._hubWhCache.raw !== rawWh) {
                try { warehouses = typeof rawWh === 'string' ? JSON.parse(rawWh) : rawWh; } catch(e) { warehouses = []; }
                window._hubWhCache.raw = rawWh;
                window._hubWhCache.obj = warehouses || [];
            }
            function getWarehouseName(id) {
                if (id === 'PENDING_RECEIPT' || !id) return '<span style="color: #f97316; font-weight: 700;">قيد الاستلام</span>';
                const w = warehouses.find(x => x.id === id); return w ? w.name : id;
            }

            let items = [];
            try { let rawItems = localStorage.getItem('erp_items_v1'); if (typeof rawItems === 'string') items = JSON.parse(rawItems) || []; } catch(e) {}
            function getItemName(id) { const item = items.find(x => x.id === id); return item ? item.name : id || 'صنف غير معروف'; }

            if (!invoices || invoices.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'flex';
                updateHubSummary([], 0, 0);
                updateHubPagination(0);
                return;
            }

            // Apply search/filter options
            const filterType = document.getElementById('hub-filter-type')?.value || 'all';
            const filterVal = document.getElementById('hub-filter-value')?.value || '';

            if (filterVal) {
                const normHubAr = (s) => String(s || '').toLowerCase()
                    .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
                    .replace(/[أإآٱ]/g, 'ا')
                    .replace(/ة/g, 'ه')
                    .replace(/[ىئ]/g, 'ي')
                    .replace(/ؤ/g, 'و')
                    .trim();
                if (filterType === 'all') {
                    const query = normHubAr(filterVal);
                    invoices = invoices.filter(inv => {
                        const itemNames = Array.isArray(inv.items) ? inv.items.map(r => r.itemName || r.name || getItemName(r.itemId)).join(' ') : '';
                        const text = normHubAr([inv.invoiceNo, inv.supplierName, getSupplierName(inv.customer || inv.customerId), itemNames, inv.notes, inv.date].join(' '));
                        return text.includes(query);
                    });
                } else if (filterType === 'invoiceNo') {
                    const query = normHubAr(filterVal);
                    invoices = invoices.filter(inv => normHubAr(inv.invoiceNo).includes(query));
                } else if (filterType === 'status') {
                    invoices = invoices.filter(inv => (inv.status || 'confirmed') === filterVal);
                } else if (filterType === 'warehouse') {
                    invoices = invoices.filter(inv => {
                        if (filterVal === 'PENDING_RECEIPT') {
                            return !inv.warehouse || inv.warehouse === 'PENDING_RECEIPT';
                        }
                        return inv.warehouse === filterVal;
                    });
                } else if (filterType === 'supplier') {
                    invoices = invoices.filter(inv => String(inv.customer || inv.customerId || inv.supplierId) === filterVal);
                } else if (filterType === 'item') {
                    invoices = invoices.filter(inv => {
                        const itemsList = inv.items || [];
                        return itemsList.some(row => String(row.itemId) === filterVal);
                    });
                } else if (filterType === 'date') {
                    invoices = invoices.filter(inv => {
                        const invDate = inv.date || '';
                        return invDate.includes(filterVal);
                    });
                }
            }

            emptyState.style.display = 'none';
            
            // Sort by date descending
            invoices.sort((a,b) => {
                const dateA = new Date(a.timestamp || a.date || 0).getTime();
                const dateB = new Date(b.timestamp || b.date || 0).getTime();
                return (isNaN(dateB) ? 0 : dateB) - (isNaN(dateA) ? 0 : dateA);
            });

            hubFilteredInvoices = invoices;

            // Calculate summary
            const postedCount = invoices.filter(i => i.status === 'posted').length;
            const pendingCount = invoices.filter(i => i.status === 'confirmed').length;
            updateHubSummary(invoices, postedCount, pendingCount);

            // Pagination
            const totalPages = Math.ceil(invoices.length / HUB_PAGE_SIZE);
            if (hubCurrentPage > totalPages) hubCurrentPage = totalPages || 1;
            const startIdx = (hubCurrentPage - 1) * HUB_PAGE_SIZE;
            const pageInvoices = invoices.slice(startIdx, startIdx + HUB_PAGE_SIZE);
            
            updateHubPagination(totalPages);

            // Render rows
            tbody.innerHTML = '';
            const fragment = document.createDocumentFragment();
            pageInvoices.forEach(inv => {
                const tr = document.createElement('tr');
                
                let itemText = 'بدون أصناف';
                const lines = inv.items || [];
                if (lines.length === 1) {
                    itemText = lines[0].itemName || lines[0].name || getItemName(lines[0].itemId);
                } else if (lines.length > 1) {
                    itemText = lines.length + ' أصناف';
                }

                // Status badge
                let statusBadge = '';
                const st = inv.status || 'confirmed';
                const recSt = inv.receiptStatus || '';
                const isProforma = inv.saleType === 'proforma' || String(inv.invoiceNo || '').startsWith('PF');
                if (isProforma) {
                    statusBadge = '<span style="background:rgba(168,85,247,0.18); color:#d8b4fe; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(168,85,247,0.4);">📋 مبدئية</span>';
                } else if (st === 'received' || recSt === 'received') {
                    statusBadge = '<span style="background:rgba(16,185,129,0.2); color:#34d399; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:800; border:1px solid rgba(16,185,129,0.45);">✅ مستلمة بالكامل</span>';
                } else if (st === 'partial_received' || recSt === 'partial_received' || recSt === 'partially_received') {
                    statusBadge = '<span style="background:rgba(56,189,248,0.18); color:#38bdf8; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:800; border:1px solid rgba(56,189,248,0.45);">📦 مستلمة جزئياً</span>';
                } else if (st === 'posted') {
                    statusBadge = '<span style="background:rgba(16,185,129,0.15); color:#34d399; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(16,185,129,0.3);">🔒 مرحلة</span>';
                } else if (st === 'confirmed') {
                    statusBadge = '<span style="background:rgba(251,191,36,0.15); color:#fbbf24; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(251,191,36,0.3);">⏳ مؤكدة</span>';
                } else if (st === 'draft') {
                    statusBadge = '<span style="background:rgba(148,163,184,0.15); color:#94a3b8; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(148,163,184,0.3);">📝 مسودة</span>';
                } else {
                    statusBadge = `<span style="background:rgba(148,163,184,0.15); color:#e2e8f0; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700;">${st}</span>`;
                }

                const invCurrency = inv.currency || 'SDG';
                const convertBtnHtml = isProforma
                    ? `<button onclick="convertHubProformaToDirect('${inv.invoiceNo}')" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.45); color:#34d399; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="تحويل لفاتورة مشتريات">🔄</button>`
                    : `<button onclick="window.location.href='smart-logistics.php?mode=purchases&inv=${encodeURIComponent(inv.invoiceNo)}'" style="background:rgba(14,165,233,0.15); border:1px solid rgba(56,189,248,0.45); color:#38bdf8; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="فتح في بوابة استلام المشتريات الذكية">📥</button>
                       <button onclick="window.location.href='purchase-receipt.php?inv=${encodeURIComponent(inv.invoiceNo)}'" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="إصدار إذن استلام مخزني (GRN)">🚛</button>
                       <button onclick="window.location.href='purchase-return.php?inv=${encodeURIComponent(inv.invoiceNo)}'" style="background:rgba(244,63,94,0.12); border:1px solid rgba(244,63,94,0.35); color:#fb7185; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="مرتجع مشتريات">↩️</button>`;

                const rawGrand = parseFloat(inv.totals?.grandTotal || inv.grandTotal || 0) || 0;
                const exRate = (typeof window.getHubExchangeRate === 'function' ? window.getHubExchangeRate() : 2650) || 2650;
                const dispGrand = window._hubShowInUsd
                    ? ('$' + (invCurrency === 'USD' ? rawGrand : rawGrand / exRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
                    : (fmtNum(invCurrency === 'USD' ? rawGrand * exRate : rawGrand) + ' <span class="hub-curr-tag" style="color:#a855f7; font-weight:900;">ج.س</span>');
                const supResolvedName = inv.supplierName || getSupplierName(inv.customer || inv.customerId || inv.supplierId) || 'مورد غير محدد';
                const supResolvedId = inv.customer || inv.customerId || inv.supplierId || '';

                tr.innerHTML = `
                    <td class="hub-col-invno" style="text-align:right; font-weight:900; font-size:1.22rem; font-family:'Outfit','Cairo',sans-serif; color:var(--gold-primary);">${inv.invoiceNo}</td>
                    <td class="hub-col-date" style="text-align:right; font-weight:900; font-size:1.05rem; font-family:'Outfit','Cairo',sans-serif;">${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG-u-nu-latn')}</td>
                    <td class="hub-col-supplier" style="text-align:right; font-weight:900; font-size:1.02rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                            <span>${supResolvedName}</span>
                            <button type="button" onclick="event.stopPropagation(); window.open('suppliers.php?ledger=${encodeURIComponent(supResolvedId)}','_blank');" title="كشف حساب المورد (أستاذ)" style="height:26px; padding:0 8px; border-radius:6px; background:linear-gradient(135deg,#0284c7,#0369a1); border:1.5px solid #38bdf8; color:#fff !important; font-size:0.74rem; font-weight:900; cursor:pointer; flex-shrink:0;">📖 أستاذ</button>
                        </div>
                    </td>
                    <td class="hub-col-items" style="text-align:right; font-weight:800;">${itemText}</td>
                    <td class="hub-col-total" style="text-align:center; font-weight:900; font-size:1.18rem; font-family:'Outfit','Cairo',sans-serif; direction:ltr;">
                        <span style="display:inline-flex; align-items:center; gap:6px;">
                            <span class="pur-price-lamp ${rawGrand > 0 ? 'lamp-green' : 'lamp-amber'}" style="width:16px !important; height:16px !important; min-width:16px !important;"></span>
                            <span>${dispGrand}</span>
                        </span>
                    </td>
                    <td style="text-align:center;">${statusBadge}</td>
                    <td class="hub-col-wh" style="text-align:center; font-weight:900;">${getWarehouseName(inv.warehouse)}${inv.grnNo ? `<div style="font-size:0.72rem; color:#10b981; font-weight:900;">GRN: ${inv.grnNo}</div>` : ''}</td>
                    <td style="text-align:center; white-space:nowrap;">
                        ${convertBtnHtml}
                        <button onclick="editHubTransaction('${inv.invoiceNo}')" style="background:rgba(56,189,248,0.16); border:1.5px solid rgba(56,189,248,0.45); color:#38bdf8; border-radius:6px; width:32px; height:32px; cursor:pointer; margin-left:3px; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض">👁️</button>
                        <button onclick="deleteHubTransaction('${inv.invoiceNo}')" style="background:rgba(239,68,68,0.16); border:1.5px solid rgba(239,68,68,0.45); color:#ef4444; border-radius:6px; width:32px; height:32px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
                    </td>
                `;
                fragment.appendChild(tr);
            });
            tbody.appendChild(fragment);
        }

        function updateHubSummary(invoices, postedCount, pendingCount) {
            let summaryEl = document.getElementById('hub-summary-bar');
            if (!summaryEl) return;
            const total = invoices.length;
            const totalAmount = invoices.reduce((sum, inv) => sum + (parseFloat(inv.totals?.grandTotal || inv.grandTotal) || 0), 0);
            const exRate = (typeof window.getHubExchangeRate === 'function' ? window.getHubExchangeRate() : 2650) || 2650;
            const totalUsd = totalAmount / exRate;
            const currBtnLbl = window._hubShowInUsd ? 'دولار ⇄' : 'جنيه ⇄';
            summaryEl.innerHTML = `
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#334155,#1e293b); border:2px solid #94a3b8; color:#fff !important; padding:4px 11px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">📊 الفواتير: <strong style="color:#fff !important; font-size:1.1rem; font-family:'Outfit';">${total}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#059669,#047857); border:2px solid #6ee7b7; color:#fff !important; padding:4px 11px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">🔒 المرحلة: <strong style="color:#fff !important; font-size:1.1rem; font-family:'Outfit';">${postedCount}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#d97706,#b45309); border:2px solid #fcd34d; color:#fff !important; padding:4px 11px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">⏳ الانتظار: <strong style="color:#fff !important; font-size:1.1rem; font-family:'Outfit';">${pendingCount}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#e11d48,#be123c); border:2px solid #fda4af; color:#fff !important; padding:4px 12px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">💰 الجملة (ج.س): <strong style="color:#fff !important; font-size:1.18rem; font-family:'Outfit';">${fmtNum(totalAmount)}</strong></span>
                <span class="hub-sum-cap" style="background:linear-gradient(135deg,#0284c7,#0369a1); border:2px solid #7dd3fc; color:#fff !important; padding:4px 12px; border-radius:9px; display:inline-flex; align-items:center; gap:5px; font-weight:900;">💵 بالدولار ($): <strong style="color:#fff !important; font-size:1.18rem; font-family:'Outfit';">$${totalUsd.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}</strong></span>
                <button type="button" id="hub-fin-curr-toggle-btn" onclick="window.toggleHubSummaryCurrency && window.toggleHubSummaryCurrency()" style="height:32px; padding:0 12px; border-radius:9px; background:linear-gradient(135deg,#7c3aed,#5b21b6); border:2px solid #c4b5fd; color:#fff !important; font-weight:900; font-size:0.84rem; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">💱 ${currBtnLbl}</button>
            `;
        }

        function updateHubPagination(totalPages) {
            let pagEl = document.getElementById('hub-pagination-bar');
            if (!pagEl) return;
            if (totalPages <= 1) { pagEl.style.display = 'none'; return; }
            pagEl.style.display = 'flex';
            pagEl.innerHTML = `
                <button onclick="hubGoToPage(${hubCurrentPage - 1})" ${hubCurrentPage <= 1 ? 'disabled style="opacity:0.3;"' : ''} style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer; font-family:'Tajawal',sans-serif;">→ السابق</button>
                <span style="color:#94a3b8; font-size:0.9rem;">صفحة <strong style="color:#fff;">${hubCurrentPage}</strong> من <strong style="color:#fff;">${totalPages}</strong></span>
                <button onclick="hubGoToPage(${hubCurrentPage + 1})" ${hubCurrentPage >= totalPages ? 'disabled style="opacity:0.3;"' : ''} style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer; font-family:'Tajawal',sans-serif;">التالي ←</button>
            `;
        }

        function hubGoToPage(page) {
            if (page < 1) return;
            hubCurrentPage = page;
            loadHubTransactions();
        }

        function setHubTimePeriod(period) {
            hubTimePeriod = period;
            hubCurrentPage = 1;
            // Update active button
            document.querySelectorAll('.hub-time-btn').forEach(btn => {
                const isActive = btn.dataset.period === period;
                if (isActive) {
                    btn.style.background = 'rgba(56,189,248,0.15)';
                    btn.style.color = '#38bdf8';
                } else {
                    btn.style.background = 'transparent';
                    btn.style.color = '#94a3b8';
                }
            });
            loadHubTransactions();
        }
        
        function filterHubTransactions() {
            hubCurrentPage = 1;
            loadHubTransactions();
        }

        function editHubTransaction(invNo) {
            openPurchaseEditor('direct', true);
            if(typeof loadInvoiceFromStorage === 'function') {
                setTimeout(() => loadInvoiceFromStorage(invNo), 100);
            }
        }
        
        function deleteHubTransaction(invNo) {
            // Check if invoice is posted — must unpost first
            let invStr = localStorage.getItem('erp_pur_' + invNo);
            let inv = null;
            if (invStr) try { inv = JSON.parse(invStr); } catch(e) {}
            if (!inv) {
                let savedData = localStorage.getItem('erp_purchase_invoices_v1');
                if (savedData) {
                    try {
                        let invoices = JSON.parse(savedData);
                        inv = invoices.find(i => String(i.invoiceNo) === String(invNo) || String(i.id) === String(invNo));
                    } catch(e) {}
                }
            }
            
            if (inv && inv.status === 'posted') {
                showToast('⛔ لا يمكن حذف فاتورة مرحلة — يجب عكس الترحيل أولاً', 'error', 4000);
                return;
            }
            // Check if any GRN warehouse receipt is linked to this invoice
            try {
                const grnList = JSON.parse(localStorage.getItem('erp_grn_v1') || '[]');
                const hasLinkedGrn = Array.isArray(grnList) && grnList.some(g => g && !g.deleted && g.status !== 'cancelled' && (String(g.invoiceNo) === String(invNo) || String(g.refInvoice) === String(invNo)));
                if (hasLinkedGrn) {
                    showToast('⛔ لا يمكن حذف فاتورة مرتبطة بإذن استلام مخزني (GRN) معتمد', 'error', 4000);
                    return;
                }
            } catch (_eg) {}

            if (window._pendingHubDeleteInvNo !== String(invNo)) {
                window._pendingHubDeleteInvNo = String(invNo);
                if (typeof showToast === 'function') {
                    showToast('⚠️ اضغط زر الحذف مرة أخرى خلال 3 ثوانٍ لتأكيد حذف الفاتورة ' + invNo, 'warning', 2800);
                }
                clearTimeout(window._pendingHubDeleteTimer);
                window._pendingHubDeleteTimer = setTimeout(() => { window._pendingHubDeleteInvNo = null; }, 3200);
                return;
            }
            window._pendingHubDeleteInvNo = null;

            // Revert accounting effects if invoice was posted (auto mode)
            if (inv && (inv.status === 'posted' || inv.status === 'confirmed')) {
                if (typeof revertOldInvoiceEffects === 'function') {
                    revertOldInvoiceEffects(invNo);
                }
            }
            
            // Remove individual record
            localStorage.removeItem('erp_pur_' + invNo);
            localStorage.removeItem('erp_draft_' + invNo);

            // Remove from global array
            let savedData = localStorage.getItem('erp_purchase_invoices_v1');
            if (savedData) {
                let invoices = JSON.parse(savedData);
                invoices = invoices.filter(i => i.invoiceNo !== invNo);
                localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(invoices));
            }
            window._cachedPurchaseInvoicesRaw = null;
            window._cachedPurchaseList = null;
            try {
                if (typeof window.recordPurchaseAuditFingerprint === 'function') {
                    window.recordPurchaseAuditFingerprint('DELETE_INVOICE', invNo);
                }
                if (typeof BroadcastChannel !== 'undefined') {
                    const bc = new BroadcastChannel('magaiees_sync_channel');
                    bc.postMessage({ type: 'purchase_invoice_deleted', module: 'purchase', invoiceNo: invNo, timestamp: Date.now() });
                    bc.close();
                }
            } catch (_ebc) {}
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true).catch(e => console.warn("Delete push failed:", e));
            }
            
            showToast('🗑️ تم حذف الفاتورة وعكس تأثيراتها بنجاح', 'success');
            loadHubTransactions();
        }

        document.addEventListener('DBReady', () => {
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const actionParam = urlParams.get('action');
                const idParam = urlParams.get('id');
                if (idParam || localStorage.getItem('cfo_order_draft')) {
                    return;
                }
                if (actionParam === 'direct' || actionParam === 'proforma') {
                    openPurchaseEditor(actionParam);
                } else {
                    showPurchaseHub();
                }
            }, 50);
        });

        // Refresh invoice list after cloud sync completes (smart merge may add records)
        window.addEventListener('cloudDataRestored', () => {
            if (currentPurchaseView === 'hub') {
                loadHubTransactions();
            }
        });
    </script>
    <script>
async function sendInvoiceWA(invoiceNo) {
    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
        const data = await res.json();
        
        if (data.status !== 'CONNECTED' && data.status !== 'AUTHENTICATED') {
            const wantsToConnect = typeof showWAConnectPrompt === 'function' ? await showWAConnectPrompt() : true;
            if (wantsToConnect) {
                if (typeof openWAModal === 'function') {
                    openWAModal();
                } else if (typeof showToast === 'function') {
                    showToast('⚠️ يرجى الذهاب إلى شاشة الإعدادات لربط الواتساب.', 'warning');
                }
            }
            return;
        }
    } catch (err) {
        if (typeof showToast === 'function') showToast('⚠️ خدمة الواتساب غير متاحة حالياً. تأكد من تشغيل الخادم.', 'warning');
        return;
    }

    let allSales = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    let inv = allSales.find(i => i.invoiceNo === invoiceNo);
    if (!inv) {
        if (typeof showToast === 'function') showToast('⚠️ لم يتم العثور على الفاتورة المطلوبة', 'warning');
        return;
    }

    let defaultPhone = '';
    let supplierName = inv.supplierName || (typeof getSupplierName === 'function' ? getSupplierName(inv.customer || inv.customerId || inv.supplierId) : '') || 'مورد غير محدد';
    const supplierId = inv.customer || inv.customerId || inv.supplierId;
    if (supplierId && supplierId !== 'walk-in') {
        let suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
        let sup = suppliers.find(s => String(s.id) === String(supplierId));
        if(sup) {
            defaultPhone = sup.whatsapp || sup.phone || '';
            supplierName = sup.name || supplierName;
        }
    }

    const phone = defaultPhone ? defaultPhone : await new Promise((resolve) => {
        const overlayId = 'wa-phone-prompt-overlay';
        if (document.getElementById(overlayId)) document.getElementById(overlayId).remove();

        window.toggleEditPhone = function() {
            const displayWrap = document.getElementById('wa-phone-display-wrap');
            const inputWrap = document.getElementById('wa-phone-input-wrap');
            if (displayWrap && inputWrap) {
                if (displayWrap.style.display !== 'none') {
                    displayWrap.style.display = 'none';
                    inputWrap.style.display = 'block';
                    const inp = document.getElementById('wa-prompt-input');
                    if (inp) {
                        inp.focus();
                        inp.select();
                    }
                } else {
                    displayWrap.style.display = 'flex';
                    inputWrap.style.display = 'none';
                }
            }
        };
        
        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);">
            <div style="background:linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border:1px solid rgba(37,211,102,0.4); border-radius:24px; padding:35px; width:90%; max-width:450px; text-align:center; transform:translateY(40px) scale(0.9); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(37,211,102,0.2), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <div style="width:80px; height:80px; background:linear-gradient(135deg, #25D366, #128C7E); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#fff; box-shadow:0 10px 20px rgba(37,211,102,0.4); border:3px solid rgba(255,255,255,0.2);">
                    <svg viewBox="0 0 24 24" width="45" height="45" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                
                <h3 style="color:#f8fafc; font-size:1.6rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0; font-weight:800;">إرسال الفاتورة عبر واتساب</h3>
                <p style="color:#94a3b8; font-size:0.95rem; margin-bottom:20px; line-height:1.6; font-family:'Tajawal',sans-serif;">يرجى تأكيد إرسال الفاتورة إلى المورد:</p>
                
                <!-- Display card -->
                <div id="wa-phone-display-wrap" style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 15px 20px; margin-bottom: 25px; text-align: right;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">👤 مورد الفاتورة:</span>
                        <span style="font-size: 1.15rem; color: #25d366; font-weight: 800;">${supplierName}</span>
                        <span style="font-size: 0.95rem; color: #38bdf8; font-weight: 700; direction: ltr; margin-top: 4px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>📞</span> <span id="wa-display-phone-text">${defaultPhone || 'لا يوجد رقم مسجل'}</span>
                        </span>
                    </div>
                    <button type="button" onclick="toggleEditPhone()" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; border-radius: 12px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56, 189, 248, 0.2)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(56, 189, 248, 0.1)'; this.style.transform='scale(1)';" title="إرسال لرقم آخر">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Input container (Hidden by default) -->
                <div id="wa-phone-input-wrap" style="display: none; margin-bottom: 25px; animation: wa-scale-up 0.3s ease-out; text-align: right;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">✏️ إرسال إلى رقم هاتف آخر:</span>
                        <button type="button" onclick="toggleEditPhone()" style="background: transparent; border: none; color: #cbd5e1; font-size: 0.75rem; cursor: pointer; text-decoration: underline; outline: none;">رجوع</button>
                    </div>
                    <div style="position: relative;">
                        <input type="tel" id="wa-prompt-input" placeholder="رقم الهاتف الجديد..." value="${defaultPhone}" oninput="document.getElementById('wa-display-phone-text').textContent = this.value;" style="width:100%; background:rgba(0,0,0,0.4); border:2px solid rgba(255,255,255,0.1); color:#fff; padding:12px 15px; border-radius:12px; font-size:1.15rem; text-align:center; transition:all 0.3s; outline:none; font-family:'Inter',sans-serif;" onfocus="this.style.borderColor='#25d366'; this.style.boxShadow='0 0 12px rgba(37,211,102,0.2)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-send" style="background:linear-gradient(135deg, #25d366, #1da851); color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.3s; box-shadow:0 4px 15px rgba(37,211,102,0.4); display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37,211,102,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)';">
                        <span>إرسال</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <button id="wa-prompt-cancel" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">إلغاء</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        const input = document.getElementById('wa-prompt-input');
        input.value = defaultPhone;

        // Animate in
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0) scale(1)';
            setTimeout(() => input.focus(), 400); // Focus input after animation
        });

        const closePrompt = (resultVal) => {
            overlay.style.opacity = '0';
            modal.style.transform = 'translateY(40px) scale(0.9)';
            setTimeout(() => {
                overlay.remove();
                resolve(resultVal);
            }, 400);
        };

        document.getElementById('wa-prompt-send').onclick = () => closePrompt(input.value.trim());
        document.getElementById('wa-prompt-cancel').onclick = () => closePrompt(null);
        input.addEventListener('keyup', (e) => {
            if(e.key === 'Enter') closePrompt(input.value.trim());
        });
    });

    if (!phone) return;

    const htmlContent = window.InvoicePrinter.buildHTML(inv, 'نسخة العميل');
    
    // Create a fullscreen overlay to display the document clearly while sending
    const overlayWrapper = document.createElement('div');
    overlayWrapper.style.position = 'fixed';
    overlayWrapper.style.top = '0';
    overlayWrapper.style.left = '0';
    overlayWrapper.style.width = '100vw';
    overlayWrapper.style.height = '100vh';
    overlayWrapper.style.background = 'rgba(15, 23, 42, 0.85)';
    overlayWrapper.style.backdropFilter = 'blur(10px)';
    overlayWrapper.style.zIndex = '999999999';
    overlayWrapper.style.display = 'flex';
    overlayWrapper.style.flexDirection = 'column';
    overlayWrapper.style.alignItems = 'center';
    overlayWrapper.style.justifyContent = 'center';
    overlayWrapper.style.overflowY = 'auto';
    overlayWrapper.style.padding = '40px 20px';

    const statusHeader = document.createElement('div');
    statusHeader.innerHTML = `
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
            <div style="width:30px; height:30px; border:3px solid #25d366; border-top-color:transparent; border-radius:50%; animation:spin 1s linear infinite;"></div>
            <h3 style="color:#25d366; font-family:'Tajawal', sans-serif; font-size:1.6rem; margin:0; font-weight:bold;">جاري الإرسال عبر واتساب...</h3>
        </div>
    `;
    
    const printContainer = document.createElement('div');
    printContainer.className = 'print-preview-wrapper';
    printContainer.style.width = '800px';
    printContainer.style.maxWidth = '100%';
    printContainer.style.background = '#f0f4f8';
    printContainer.style.padding = '24px';
    printContainer.style.borderRadius = '16px';
    printContainer.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5)';
    printContainer.style.transform = 'scale(0.85)';
    
    printContainer.innerHTML = htmlContent;
    
    overlayWrapper.appendChild(statusHeader);
    overlayWrapper.appendChild(printContainer);
    document.body.appendChild(overlayWrapper);

    const message = `مرحباً، مرفق فاتورتكم المرجعية رقم ${invoiceNo} من ${JSON.parse(localStorage.getItem('user'))?.company_name || 'مؤسستنا'}.`;
    
    try {
        await sendPDFViaWhatsApp(printContainer, phone, message);
    } catch (e) {
        console.error(e);
    } finally {
        overlayWrapper.remove();
    }
}

function updateActionButtonsVisibility() {
    const previewBtn = document.getElementById('btn-preview-invoice');
    const invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : window.currentViewedInvoiceNo;
    if (invNo && invNo !== '---' && invNo !== 'PUR-NEW' && invNo !== 'جديد') {
        if (previewBtn) previewBtn.style.display = 'flex';
    } else {
        if (previewBtn) previewBtn.style.display = 'none';
    }
    // Share button is permanently visible
}

// ═══════════════════════════════════════
//  SHARE MENU TOGGLE
// ═══════════════════════════════════════
let shareMenuOpen = false;

function toggleShareMenu(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('share-popup-menu');
    if (!menu) return;
    
    if (shareMenuOpen) {
        closeShareMenu();
    } else {
        if (menu.parentElement !== document.body) {
            document.body.appendChild(menu);
            menu.style.position = 'fixed';
            menu.style.zIndex = '999999999';
        }
        const btn = document.getElementById('btn-share-invoice');
        if (btn) {
            const rect = btn.getBoundingClientRect();
            menu.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
            menu.style.left = (rect.left + rect.width / 2) + 'px';
        }
        menu.style.display = 'block';
        // Trigger reflow for animation
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                menu.style.opacity = '1';
                menu.style.transform = 'translateX(-50%) scale(1)';
            });
        });
        shareMenuOpen = true;
    }
}

function closeShareMenu() {
    const menu = document.getElementById('share-popup-menu');
    if (!menu) return;
    menu.style.opacity = '0';
    menu.style.transform = 'translateX(-50%) scale(0.9)';
    setTimeout(() => {
        menu.style.display = 'none';
    }, 250);
    shareMenuOpen = false;
}

// Close share menu on outside click
document.addEventListener('click', function(e) {
    if (shareMenuOpen) {
        const wrapper = document.getElementById('share-btn-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeShareMenu();
        }
    }
});

async function sendCurrentInvoiceWA() {
    let invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : (window.currentViewedInvoiceNo || window._lastSubmittedPurchaseInvoiceNo || document.getElementById('hdr-inv-no')?.value);
    if (invNo && invNo !== '---' && invNo !== 'PUR-NEW' && invNo !== 'جديد') {
        await sendInvoiceWA(invNo);
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
    }
}

async function sendCurrentInvoiceEmail() {
    let invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : (window.currentViewedInvoiceNo || window._lastSubmittedPurchaseInvoiceNo || document.getElementById('hdr-inv-no')?.value);
    if (invNo && invNo !== '---' && invNo !== 'PUR-NEW' && invNo !== 'جديد') {
        showToast('جاري تجهيز الفاتورة للإرسال عبر البريد الإلكتروني...', 'success');
        // Fallback for Email if no backend email API exists
        const mailtoLink = `mailto:?subject=فاتورة مشتريات رقم ${invNo}&body=مرحباً، مرفق تفاصيل الفاتورة.`;
        window.location.href = mailtoLink;
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
    }
}
</script>

<!-- Barcode Scanner Modal -->
<div id="barcode-scanner-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index: 999999; justify-content: center; align-items: center; direction: rtl;">
    <div class="glass-panel" style="width: 90%; max-width: 480px; padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); position: relative; text-align: center; background: rgba(30,41,59,0.9);">
        <button onclick="stopCameraScanner()" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #f43f5e; font-size: 1.8rem; cursor: pointer;">&times;</button>
        <h3 style="color: #38bdf8; margin-bottom: 15px; font-weight: 800;">📷 قارئ الباركود عبر الكاميرا</h3>
        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 20px;">وجه مربع الكاميرا نحو الرمز الشريطي (Barcode) للمنتج لقراءته تلقائياً.</p>
        <div id="scanner-viewport" style="width: 100%; aspect-ratio: 4/3; background: #000; border-radius: 12px; overflow: hidden; border: 2px solid rgba(56,189,248,0.4); position: relative;"></div>
        <div id="scanner-status" style="margin-top: 15px; color: #fbbf24; font-weight: bold; font-size: 0.9rem;">جاري تهيئة الكاميرا...</div>
    </div>
</div>

<script>
let html5QrcodeScanner = null;

function startCameraScanner(targetInputId) {
    document.getElementById('barcode-scanner-modal').style.display = 'flex';
    document.getElementById('scanner-status').innerText = 'جاري تشغيل الكاميرا...';
    document.getElementById('scanner-status').style.color = '#fbbf24';

    loadScannerLibrary(() => {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(() => {});
        }
        
        html5QrcodeScanner = new Html5Qrcode("scanner-viewport");
        const config = { fps: 15, qrbox: { width: 280, height: 180 } };
        
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                const matchedItem = items.find(item => {
                    if (item.barcode === decodedText) return true;
                    if (item.units && Array.isArray(item.units)) {
                        return item.units.some(u => u.barcode === decodedText);
                    }
                    return false;
                });
                
                if (matchedItem) {
                    playScanBeep();
                    if (typeof selectQuickItem === 'function') {
                        selectQuickItem(matchedItem.id);
                    } else {
                        const input = document.getElementById(targetInputId);
                        if (input) {
                            input.value = decodedText;
                            input.dispatchEvent(new Event('input'));
                        }
                    }
                    stopCameraScanner();
                } else {
                    playScanBeep();
                    const input = document.getElementById(targetInputId);
                    if (input) {
                        input.value = decodedText;
                        input.dispatchEvent(new Event('input'));
                    }
                    if (typeof window.focusRequiredPurchaseField === 'function') {
                        window.focusRequiredPurchaseField('#' + targetInputId, `⚠️ لم يتم العثور على صنف بالباركود: ${decodedText}`);
                    } else if (typeof showToast === 'function') {
                        showToast(`⚠️ لم يتم العثور على صنف بالباركود: ${decodedText}`, 'warning');
                    }
                    stopCameraScanner();
                }
            },
            (errorMessage) => {}
        ).then(() => {
            document.getElementById('scanner-status').innerText = '🟢 الكاميرا متصلة وتعمل حالياً';
            document.getElementById('scanner-status').style.color = '#34d399';
        }).catch(err => {
            document.getElementById('scanner-status').innerText = '❌ تعذر الوصول للكاميرا: يرجى إعطاء الصلاحية';
            document.getElementById('scanner-status').style.color = '#f43f5e';
            console.error("Camera access failed", err);
        });
    });
}

function stopCameraScanner() {
    document.getElementById('barcode-scanner-modal').style.display = 'none';
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            return html5QrcodeScanner.clear();
        }).catch(err => {
            console.error("Failed to stop scanner", err);
        }).finally(() => {
            html5QrcodeScanner = null;
        });
    }
}

function loadScannerLibrary(callback) {
    if (window.Html5Qrcode) {
        callback();
        return;
    }
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/html5-qrcode';
    script.onload = callback;
    document.head.appendChild(script);
}

function playScanBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1000, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch(e) {
        console.error("Failed to play audio", e);
    }
}
</script>


<!-- Numeric Custom Context Menu -->
<div id="num-context-menu" class="glass-panel" style="
    display: none;
    position: fixed;
    z-index: 20000;
    width: 280px;
    padding: 15px;
    border-radius: 16px;
    background: rgba(15, 12, 22, 0.95);
    backdrop-filter: blur(12px);
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

<script>
    function applyStylingPrefs() {
        const prefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');
        const color = prefs.color || '';
        const size = prefs.size || '';
        const font = prefs.font || '';

        let css = '';
        if (color) {
            css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { color: ${color}; }\n`;
        }
        if (size) {
            css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { font-size: ${size}; }\n`;
        }
        if (font) {
            let fontStack = '';
            if (font === 'Inter') fontStack = "'Inter', sans-serif";
            else if (font === 'Outfit') fontStack = "'Outfit', sans-serif";
            else if (font === 'JetBrains Mono') fontStack = "'JetBrains Mono', monospace";
            
            if (fontStack) {
                css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { font-family: ${fontStack}; }\n`;
            }
        }

        let styleEl = document.getElementById('custom-num-styles');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'custom-num-styles';
            document.head.appendChild(styleEl);
        }
        styleEl.innerHTML = css;
    }

    window.setNumStyle = function(prop, val) {
        const prefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');
        prefs[prop] = val;
        localStorage.setItem('num_style_prefs', JSON.stringify(prefs));
        applyStylingPrefs();
    };

    window.setNumFormat = function(prop, val) {
        const prefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
        prefs[prop] = val;
        localStorage.setItem('num_format_prefs', JSON.stringify(prefs));
        if (typeof window.recalcAll === 'function') window.recalcAll();
    };

    window.openNumContextMenu = function(e) {
        e.preventDefault();
        const menu = document.getElementById('num-context-menu');
        if (!menu) return;

        menu.style.display = 'block';
        
        let left = e.clientX;
        let top = e.clientY;
        if (left + 280 > window.innerWidth) left = window.innerWidth - 290;
        if (top + 380 > window.innerHeight) top = window.innerHeight - 390;
        
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';

        const formatPrefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
        const stylePrefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');

        const sizeSelect = menu.querySelector('select[onchange*="size"]');
        if (sizeSelect && stylePrefs.size !== undefined) sizeSelect.value = stylePrefs.size;

        const fontSelect = menu.querySelector('select[onchange*="font"]');
        if (fontSelect && stylePrefs.font !== undefined) fontSelect.value = stylePrefs.font;

        const thousandSelect = menu.querySelector('select[onchange*="thousandSep"]');
        if (thousandSelect && formatPrefs.thousandSep !== undefined) thousandSelect.value = formatPrefs.thousandSep;

        const decimalSelect = menu.querySelector('select[onchange*="decimalSep"]');
        if (decimalSelect && formatPrefs.decimalSep !== undefined) decimalSelect.value = formatPrefs.decimalSep;

        const decimalsSelect = menu.querySelector('select[onchange*="decimals"]');
        if (decimalsSelect && formatPrefs.decimals !== undefined) decimalsSelect.value = formatPrefs.decimals;
    };

    // Context menu for number style customization disabled per user request
    document.addEventListener('contextmenu', function(e) {
        const menu = document.getElementById('num-context-menu');
        if (menu) menu.style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('num-context-menu');
        if (menu && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    applyStylingPrefs();

    window.toggleTotRow = function(type) {
        const btn = document.getElementById('btn-toggle-' + type);
        const row = document.getElementById('row-tot-' + type);
        if(!btn || !row) return;

        if (row.style.display === 'none' || row.style.display === '') {
            row.style.display = 'table-row';
            btn.classList.add('active');
            // focus the local input
            const inputLoc = document.getElementById('t-' + type);
            if(inputLoc) inputLoc.focus();
        } else {
            row.style.display = 'none';
            btn.classList.remove('active');
            // reset values to zero when hidden
            const inputLoc = document.getElementById('t-' + type);
            const inputFc = document.getElementById('t-' + type + '-fc') || document.getElementById('t-' + type + '-loc');
            
            if(inputLoc) { inputLoc.value = '0'; }
            if(inputFc && type !== 'transport' && type !== 'labor') { 
                // tax, discount, additions use -loc/-fc in IDs
                const locTax = document.getElementById('t-' + type + '-loc');
                const fcTax = document.getElementById('t-' + type + '-fc');
                if(locTax) locTax.value = '0';
                if(fcTax) fcTax.value = '0';
            } else if (inputFc) {
                inputFc.value = '0';
            }
            if(typeof recalcAll === 'function') recalcAll();
        }
        // Reposition grand total based on visible expenses
        repositionGrandTotal();
    };

    // Dynamically update grand total border when expenses are visible
    window.repositionGrandTotal = function() {
        const grandRow = document.getElementById('row-grand-total') || document.getElementById('grand-total-row');
        if (!grandRow) return;

        const visibleRows = document.querySelectorAll('#grid-foot .tot-row');
        let anyVisible = false;
        visibleRows.forEach(r => { if (r.style.display === 'table-row') anyVisible = true; });

        if (anyVisible) {
            grandRow.style.borderTop = '1px solid rgba(255, 255, 255, 0.08)';
        } else {
            grandRow.style.borderTop = 'none';
        }
    };

    window.syncTotalsVisibility = function() {
        const curEl = document.getElementById('hdr-currency');
        const isSDG = (curEl && curEl.value === 'SDG');
        
        // Hide/Show FC columns in tfoot via CSS class on body
        if (isSDG) {
            document.body.classList.add('cur-sdg');
        } else {
            document.body.classList.remove('cur-sdg');
        }

        // Update Dynamic Grand Total
        const dynLoc = document.getElementById('t-grand-dynamic-loc');
        const dynFc = document.getElementById('t-grand-dynamic-fc');
        const tLocal = document.getElementById('t-grand-local');
        const tFc = document.getElementById('t-grand-fc');
        
        if (tLocal && tFc) {
            if (dynLoc && dynLoc.textContent !== tLocal.textContent) {
                dynLoc.textContent = tLocal.textContent;
                dynLoc.classList.remove('animate-total');
                void dynLoc.offsetWidth; // force reflow
                dynLoc.classList.add('animate-total');
            }
            if (dynFc && dynFc.textContent !== tFc.textContent) {
                dynFc.textContent = tFc.textContent;
                dynFc.classList.remove('animate-total');
                void dynFc.offsetWidth; // force reflow
                dynFc.classList.add('animate-total');
            }
        }
    };

    // Override the global recalcAll to call syncTotalsVisibility after it runs.
    // recalcAll is a global function declaration in purchase-pro-v5.js, so it's on window.
    // We need to wait for it to be defined, then wrap it.
    function wrapRecalcAll() {
        if (typeof recalcAll === 'function' && !recalcAll._wrapped) {
            const origRecalc = recalcAll;
            // Override the global function
            window.recalcAll = function() {
                origRecalc();
                if (typeof window.syncTotalsVisibility === 'function') {
                    window.syncTotalsVisibility();
                }
            };
            window.recalcAll._wrapped = true;
            // Also call sync now
            window.syncTotalsVisibility();
        }
    }

    // Try immediately
    wrapRecalcAll();
    // Also try after a delay (in case JS loads async)
    setTimeout(wrapRecalcAll, 300);
    setTimeout(wrapRecalcAll, 1000);
    setTimeout(wrapRecalcAll, 2000);

    // Fallback: MutationObserver on the hidden spans so we always catch changes
    const observeTarget = document.getElementById('t-grand-local');
    if (observeTarget) {
        const observer = new MutationObserver(() => {
            if (typeof window.syncTotalsVisibility === 'function') {
                window.syncTotalsVisibility();
            }
        });
        observer.observe(observeTarget, { childList: true, characterData: true, subtree: true });
    }
    const observeTargetFc = document.getElementById('t-grand-fc');
    if (observeTargetFc) {
        const observer2 = new MutationObserver(() => {
            if (typeof window.syncTotalsVisibility === 'function') {
                window.syncTotalsVisibility();
            }
        });
        observer2.observe(observeTargetFc, { childList: true, characterData: true, subtree: true });
    }

    // Call once on load
    setTimeout(() => {
        if (typeof window.syncTotalsVisibility === 'function') {
            window.syncTotalsVisibility();
        }
    }, 500);

</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Mouse move first time: focus on Supplier (hdr-customer-search) only when inside editor
    const initialFocusHandler = () => {
        const editorView = document.getElementById('purchase-editor-view');
        if (!editorView || editorView.style.display === 'none') return;
        const supInput = document.getElementById('hdr-customer-search');
        if (supInput && document.activeElement !== supInput && (!document.activeElement || document.activeElement === document.body)) {
            supInput.focus();
            if (typeof showCustomerDropdown === 'function') {
                showCustomerDropdown();
            }
        }
        document.removeEventListener('mousemove', initialFocusHandler);
    };
    document.addEventListener('mousemove', initialFocusHandler);

    // 2. Keyboard flow
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const target = e.target;
            
            // From Supplier -> Item Code (Bypassing Warehouse)
            if (target.id === 'hdr-customer-search') {
                e.preventDefault();
                setTimeout(() => {
                    if (typeof rows !== 'undefined' && rows.length > 0) {
                        const firstSearch = document.getElementById('item-search-' + rows[0].id) || document.getElementById('m-item-search-' + rows[0].id);
                        if (firstSearch) {
                            firstSearch.focus();
                        }
                    }
                }, 100);
            }
            // From Warehouse -> Item Code (If they click it manually)
            else if (target.id === 'hdr-warehouse') {
                e.preventDefault();
                setTimeout(() => {
                    if (typeof rows !== 'undefined' && rows.length > 0) {
                        const firstSearch = document.getElementById('item-search-' + rows[0].id) || document.getElementById('m-item-search-' + rows[0].id);
                        if (firstSearch) {
                            firstSearch.focus();
                        }
                    }
                }, 100);
            }
        }
    });
});
</script>

    
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
    <div id="grid-settings-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:999999; justify-content:center; align-items:center; direction:rtl; font-family:'Tajawal', sans-serif;">
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

    <script>
    // ══════════════════════════════════════════════════════════════════════════
    //  INTERACTIVE DATABASE-PERSISTENT GRID CUSTOMIZER SYSTEM (purchase_grid)
    // ══════════════════════════════════════════════════════════════════════════

    const GRID_ID = 'purchase_grid';

    const DEFAULT_GRID_CONFIG = {
        "grid_id": "purchase_grid",
        "fontSize": 16,
        "fontWeight": "normal",
        "fontFamily": "'Cairo', sans-serif",
        "fontColor": "#ffffff",
        "align": "center",
        "decimalPlaces": 2,
        "thousandSep": ",",
        "columns": [
            {
                "id": "col_index",
                "name": "#",
                "visible": true,
                "width": "45px",
                "align": "center",
                "color": "#94a3b8",
                "fontSize": 15
            },
            {
                "id": "available",
                "name": "المتاح",
                "visible": true,
                "width": "90px",
                "align": "center",
                "color": "#38bdf8",
                "fontSize": 20,
                "decimals": 2
            },
            {
                "id": "warehouse",
                "name": "المخزن",
                "visible": true,
                "width": "130px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 22,
                "fontWeight": "800"
            },
            {
                "id": "item",
                "name": "الصنف",
                "visible": true,
                "width": "324px",
                "align": "right",
                "color": "#ffdc7a",
                "fontSize": 26,
                "fontWeight": "800"
            },
            {
                "id": "unit",
                "name": "الوحدة",
                "visible": true,
                "width": "107px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 20,
                "fontWeight": "bold"
            },
            {
                "id": "quantity",
                "name": "الكمية",
                "visible": true,
                "width": "121px",
                "align": "center",
                "color": "#facc15",
                "fontSize": 36,
                "fontWeight": "800",
                "decimals": 2
            },
            {
                "id": "price",
                "name": "السعر",
                "visible": true,
                "width": "206px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 36,
                "fontWeight": "800",
                "decimals": 0,
                "thousandSep": "default"
            },
            {
                "id": "total",
                "name": "الجملة",
                "visible": true,
                "width": "240px",
                "align": "center",
                "color": "#fe9095",
                "fontSize": 36,
                "fontWeight": "800",
                "decimals": 0,
                "thousandSep": ","
            },
            {
                "id": "action",
                "name": "إجراء",
                "visible": true,
                "width": "90px",
                "align": "center",
                "color": "#ffffff",
                "fontSize": 14
            }
        ]
    };

    let tempGridConfig = null;
    let selectedColIndex = 0;

    function renderModalColumnsList() {
        const listEl = document.getElementById('col-settings-list');
        listEl.innerHTML = '';
        
        tempGridConfig.columns.forEach((col, index) => {
            const item = document.createElement('div');
            const isSelected = (index === selectedColIndex);
            
            item.style.cssText = `
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                padding: 9px 12px;
                border-radius: 10px;
                cursor: pointer;
                transition: all 0.2s;
                background: ${isSelected ? 'rgba(56, 189, 248, 0.18)' : 'rgba(255,255,255,0.03)'};
                border: ${isSelected ? '2px solid #38bdf8' : '1px solid rgba(255,255,255,0.07)'};
                box-shadow: ${isSelected ? '0 0 12px rgba(56,189,248,0.3)' : 'none'};
            `;
            
            item.onclick = (e) => {
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') return;
                selectedColIndex = index;
                renderModalColumnsList();
                populateSelectedColPanel();
            };

            const isLightNow = document.documentElement.classList.contains('light-theme');
            const colColor = col.color || tempGridConfig.fontColor || '#ffffff';
            const displayColColor = (isLightNow && ['#ffffff', '#fff'].includes(String(colColor).toLowerCase())) ? '#000000' : colColor;
            const colSize = col.fontSize || tempGridConfig.fontSize || 16;
            const colWidth = col.width || '100px';
            const btnBg = isLightNow ? '#dfd2bc' : 'rgba(255,255,255,0.1)';
            const btnClr = isLightNow ? '#000000' : '#ffffff';
            const btnBrd = isLightNow ? '1.5px solid #78350f' : 'none';

            item.innerHTML = `
                <!-- Move ▲ ▼ & Checkbox & Name -->
                <div style="display:flex; align-items:center; gap:8px; flex:1;">
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <button type="button" class="col-move-arrow-btn" onclick="moveModalColumn(${index}, 'up'); event.stopPropagation();" style="background:${btnBg}; border:${btnBrd}; border-radius:4px; width:24px; height:19px; cursor:pointer; color:${btnClr}; -webkit-text-fill-color:${btnClr}; font-weight:900; font-size:0.72rem; display:flex; align-items:center; justify-content:center; ${index === 0 ? 'opacity:0.25;' : ''}" ${index === 0 ? 'disabled' : ''}>▲</button>
                        <button type="button" class="col-move-arrow-btn" onclick="moveModalColumn(${index}, 'down'); event.stopPropagation();" style="background:${btnBg}; border:${btnBrd}; border-radius:4px; width:24px; height:19px; cursor:pointer; color:${btnClr}; -webkit-text-fill-color:${btnClr}; font-weight:900; font-size:0.72rem; display:flex; align-items:center; justify-content:center; ${index === tempGridConfig.columns.length - 1 ? 'opacity:0.25;' : ''}" ${index === tempGridConfig.columns.length - 1 ? 'disabled' : ''}>▼</button>
                    </div>
                    
                    <input type="checkbox" id="modal-col-chk-${col.id}" ${col.visible ? 'checked' : ''} onchange="toggleModalColVis(${index}, this.checked); event.stopPropagation();" style="cursor:pointer; width:17px; height:17px; accent-color:#10b981;">
                    <div style="display:flex; flex-direction:column;">
                        <span class="col-custom-color-preview" style="font-weight:900; font-size:0.95rem; color:${displayColColor}; -webkit-text-fill-color:${displayColColor};">${col.name}</span>
                        <span style="font-size:0.75rem; color:${isLightNow ? '#334155' : '#94a3b8'};">${colWidth} | ${colSize}px</span>
                    </div>
                </div>

                <!-- Active Indicator Badge -->
                <div style="display:flex; align-items:center; gap:6px;">
                    <div style="width:14px; height:14px; border-radius:50%; background:${colColor}; border:1.5px solid ${isLightNow ? '#000' : 'rgba(255,255,255,0.4)'};" title="لون الخط"></div>
                    ${isSelected ? '<span style="font-size:0.75rem; color:#38bdf8; font-weight:bold; background:rgba(56,189,248,0.2); padding:2px 6px; border-radius:4px;">👈 محدد</span>' : ''}
                </div>
            `;
            listEl.appendChild(item);
        });
    }

    function populateSelectedColPanel() {
        const col = tempGridConfig.columns[selectedColIndex];
        if (!col) return;

        document.getElementById('lbl-selected-col-name').textContent = col.name;
        document.getElementById('badge-selected-col-id').textContent = col.id;
        document.getElementById('sel-col-name').value = col.name || '';
        
        const fSize = col.fontSize || tempGridConfig.fontSize || 16;
        document.getElementById('sel-col-fontsize').value = fSize;
        document.getElementById('sel-col-fontsize-range').value = fSize;
        
        document.getElementById('sel-col-fontweight').value = col.fontWeight || '';
        
        const fColor = col.color || tempGridConfig.fontColor || '#ffffff';
        document.getElementById('sel-col-color').value = fColor;
        document.getElementById('sel-col-color-text').value = fColor;
        
        document.getElementById('sel-col-align').value = col.align || tempGridConfig.align || 'center';
        
        const widthVal = parseInt(col.width) || (col.id === 'item' || col.id === 'col_item' ? 260 : 100);
        document.getElementById('sel-col-width').value = widthVal;
        document.getElementById('sel-col-width-range').value = widthVal;
        
        document.getElementById('sel-col-decimals').value = col.decimals !== undefined ? col.decimals : '';
        document.getElementById('sel-col-thousandsep').value = col.thousandSep || 'default';
    }

    window.onSelectedColPropChange = function(prop, val) {
        const col = tempGridConfig.columns[selectedColIndex];
        if (!col) return;

        col[prop] = val;
        if (prop === 'color') col.customColor = true;
        renderModalColumnsList();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        if (typeof applyGridSettings === 'function') applyGridSettings();
        if (prop === 'decimals' || prop === 'thousandSep' || prop === 'name') {
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        }
    };

    window.applySelectedColStyleToAll = function() {
        const sourceCol = tempGridConfig.columns[selectedColIndex];
        if (!sourceCol) return;

        tempGridConfig.columns.forEach(col => {
            if (sourceCol.fontSize) col.fontSize = sourceCol.fontSize;
            if (sourceCol.fontWeight) col.fontWeight = sourceCol.fontWeight;
            if (sourceCol.color) { col.color = sourceCol.color; col.customColor = true; }
            if (sourceCol.align) col.align = sourceCol.align;
        });

        renderModalColumnsList();
        populateSelectedColPanel();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        if (typeof applyGridSettings === 'function') applyGridSettings();
        showToast("⚡ تم نسخ حجم وسُمك ولون ومحاذاة العمود المحدد إلى كافة الأعمدة بنجاح", "info", 3000);
    };

    window.toggleModalColVis = function(index, checked) {
        tempGridConfig.columns[index].visible = checked;
        renderModalColumnsList();
    };

    window.moveModalColumn = function(index, direction) {
        const list = tempGridConfig.columns;
        if (direction === 'up' && index > 0) {
            const temp = list[index];
            list[index] = list[index - 1];
            list[index - 1] = temp;
            if (selectedColIndex === index) selectedColIndex = index - 1;
            else if (selectedColIndex === index - 1) selectedColIndex = index;
        } else if (direction === 'down' && index < list.length - 1) {
            const temp = list[index];
            list[index] = list[index + 1];
            list[index + 1] = temp;
            if (selectedColIndex === index) selectedColIndex = index + 1;
            else if (selectedColIndex === index + 1) selectedColIndex = index;
        }
        renderModalColumnsList();
        populateSelectedColPanel();
    };

    function readModalGeneralSettings() {
        const col = tempGridConfig && tempGridConfig.columns ? tempGridConfig.columns[selectedColIndex] : null;
        if (col) {
            const nmEl = document.getElementById('sel-col-name');
            const fsEl = document.getElementById('sel-col-fontsize');
            const fwEl = document.getElementById('sel-col-fontweight');
            const clTxtEl = document.getElementById('sel-col-color-text');
            const clEl = document.getElementById('sel-col-color');
            const alEl = document.getElementById('sel-col-align');
            const wdEl = document.getElementById('sel-col-width');
            const dcEl = document.getElementById('sel-col-decimals');
            const tsEl = document.getElementById('sel-col-thousandsep');
            if (nmEl && nmEl.value.trim()) col.name = nmEl.value.trim();
            if (fsEl && parseInt(fsEl.value)) col.fontSize = parseInt(fsEl.value);
            if (fwEl) col.fontWeight = fwEl.value;
            const pickedClr = (clTxtEl && clTxtEl.value.trim()) ? clTxtEl.value.trim() : (clEl ? clEl.value : '');
            if (pickedClr) {
                if (col.color !== pickedClr) col.customColor = true;
                col.color = pickedClr;
            }
            if (alEl && alEl.value) col.align = alEl.value;
            if (wdEl && parseInt(wdEl.value)) col.width = parseInt(wdEl.value) + 'px';
            if (dcEl) col.decimals = dcEl.value !== '' ? parseInt(dcEl.value) : '';
            if (tsEl && tsEl.value) col.thousandSep = tsEl.value;
        }
        tempGridConfig.grid_id = 'purchase_grid';
        tempGridConfig.fontFamily = document.getElementById('grid-setting-font-family').value;
        tempGridConfig.fontSize = parseInt(document.getElementById('grid-setting-font-size').value) || 16;
        tempGridConfig.fontWeight = document.getElementById('grid-setting-font-weight').value;
        tempGridConfig.fontColor = document.getElementById('grid-setting-font-color').value;
        tempGridConfig.align = document.getElementById('grid-setting-align').value;
        tempGridConfig.decimalPlaces = parseInt(document.getElementById('grid-setting-decimals').value);
        tempGridConfig.thousandSep = document.getElementById('grid-setting-thousand-sep').value;
    }

    window.previewGridSettings = function() {
        readModalGeneralSettings();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        try { localStorage.setItem('erp_grid_config_purchase_grid', JSON.stringify(window.CurrentGridConfig)); } catch (_e) {}
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        showToast("👁️ تم تطبيق التنسيق للمعاينة المباشرة على الجدول", "info", 2000);
    };

    window.saveAndApplyGridSettingsToDB = async function() {
        readModalGeneralSettings();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        window.CurrentGridConfig.grid_id = 'purchase_grid';
        try { localStorage.setItem('erp_grid_config_purchase_grid', JSON.stringify(window.CurrentGridConfig)); } catch (_e) {}
        
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        
        closeGridSettingsModal();
        await saveGridConfigToDB(window.CurrentGridConfig);
        if (typeof showToast === 'function') showToast("✅ تم حفظ وتطبيق تخصيص الجدول نهائياً بنجاح", "success", 2500);
    };

    window.loadGridConfigFromDB = async function(forceRemote) {
        try {
            if (!forceRemote) {
                const cachedCfg = localStorage.getItem('erp_grid_config_purchase_grid');
                if (cachedCfg) {
                    const parsed = JSON.parse(cachedCfg);
                    if (parsed && Array.isArray(parsed.columns)) {
                        window.CurrentGridConfig = parsed;
                        if (typeof applyGridSettings === 'function') applyGridSettings();
                        if (typeof renderGrid === 'function') renderGrid();
                        if (typeof recalcAll === 'function') recalcAll();
                        return;
                    }
                }
            }
            const res = await fetch('api/get_grid_config.php?grid_id=purchase_grid');
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
                try { localStorage.setItem('erp_grid_config_purchase_grid', JSON.stringify(data.config)); } catch (_e) {}
            } else {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
            }
            if (typeof applyGridSettings === 'function') applyGridSettings();
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        } catch (e) {
            if (!window.CurrentGridConfig) {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
                if (typeof applyGridSettings === 'function') applyGridSettings();
            }
        }
    };
    // Smart Auto-Fit Engine for Header Inputs (Supplier, Exchange Rate, Warehouse, Cost Center, Invoice No)
    let _headerMeasureCanvas = null;
    function measureTextWidthPx(text, fontStr) {
        try {
            if (!_headerMeasureCanvas) _headerMeasureCanvas = document.createElement('canvas');
            const ctx = _headerMeasureCanvas.getContext('2d');
            ctx.font = fontStr;
            return ctx.measureText(text || '').width;
        } catch (e) {
            return (text ? text.length : 0) * 10;
        }
    }

    window.autoFitHeaderFields = function() {
        try {
            // 1. Invoice Number (#inv-number) - Large, clear & proportional to its container
            const invEl = document.getElementById('inv-number');
            if (invEl) {
                const invText = (invEl.textContent || '').trim();
                const parentW = (invEl.parentElement ? invEl.parentElement.clientWidth : 170) - 56;
                let invPx = 22;
                if (invText && parentW > 40) {
                    while (invPx > 15 && measureTextWidthPx(invText, `900 ${invPx}px 'Outfit', sans-serif`) > parentW) {
                        invPx -= 0.5;
                    }
                }
                invEl.style.setProperty('font-size', invPx + 'px', 'important');
            }

            // 2. Supplier Input (#hdr-customer-search) - Auto-fit based on supplier name length & box width
            const supInput = document.getElementById('hdr-customer-search');
            if (supInput) {
                const val = (supInput.value || '').trim();
                const availW = Math.max(120, (supInput.clientWidth || 260) - 72);
                let px = 19.5;
                if (val) {
                    while (px > 12.5 && measureTextWidthPx(val, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                supInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 3. Exchange Rate (#hdr-exchange-rate) - Prominent bold number fitted to compact box
            const exInput = document.getElementById('hdr-exchange-rate');
            if (exInput) {
                const val = (exInput.value || '').trim();
                const availW = Math.max(54, (exInput.clientWidth || 82) - 10);
                let px = 19.5;
                if (val) {
                    while (px > 13.5 && measureTextWidthPx(val, `900 ${px}px 'Outfit', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                exInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 4. Warehouse (#hdr-warehouse) & Cost Center (#hdr-cost-center) selects
            ['hdr-warehouse', 'hdr-cost-center'].forEach(selId => {
                const sel = document.getElementById(selId);
                if (!sel) return;
                const optText = (sel.options && sel.selectedIndex >= 0) ? (sel.options[sel.selectedIndex].text || '').trim() : '';
                const availW = Math.max(85, (sel.clientWidth || 175) - 32);
                let px = 16.5;
                if (optText) {
                    while (px > 12 && measureTextWidthPx(optText, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                sel.style.setProperty('font-size', px + 'px', 'important');
            });

            // 4b. Auto-fit Financial Strip (#customer-financial-card .fin-strip-inner) so all metrics (الرصيد، المديونية، شيكات، الحد، المتاح) are 100% visible
            const finInner = document.querySelector('#customer-financial-card .fin-strip-inner');
            if (finInner && finInner.clientWidth > 100) {
                const lbls = finInner.querySelectorAll('.fin-lbl');
                const vals = finInner.querySelectorAll('.fin-val');
                let valPx = 13.5;
                let lblPx = 11.8;
                let gapPx = 6;
                finInner.style.setProperty('gap', gapPx + 'px', 'important');
                finInner.style.setProperty('padding', '0 8px', 'important');
                lbls.forEach(el => el.style.setProperty('font-size', lblPx + 'px', 'important'));
                vals.forEach(el => el.style.setProperty('font-size', valPx + 'px', 'important'));
                while (finInner.scrollWidth > finInner.clientWidth && valPx > 9.5) {
                    valPx -= 0.5;
                    lblPx = Math.max(9.0, lblPx - 0.4);
                    gapPx = Math.max(3, gapPx - 0.5);
                    finInner.style.setProperty('gap', gapPx + 'px', 'important');
                    lbls.forEach(el => el.style.setProperty('font-size', lblPx + 'px', 'important'));
                    vals.forEach(el => el.style.setProperty('font-size', valPx + 'px', 'important'));
                }
            }

            // 5. Auto-Fit Bottom Action Buttons (Scales text up & down automatically with button width/height)
            if (typeof window.autoFitPurchaseBottomButtons === 'function') {
                window.autoFitPurchaseBottomButtons();
            }
        } catch (e) {}
    };

    window.autoFitPurchaseBottomButtons = function() {
        try {
            const buttons = document.querySelectorAll('#purchase-editor-view .items-toolbar .toolbar-actions button.btn-action, #purchase-editor-view #btn-toggle-auto-send-purchase');
            buttons.forEach(btn => {
                const btnW = btn.clientWidth;
                if (!btnW || btnW < 30) return;

                const labelSpan = btn.querySelector('.btn-fit-label') || btn.querySelector('span:not([id*="icon"]):not([id*="pill"])');
                const text = (labelSpan ? labelSpan.textContent : btn.textContent || '').trim();
                if (!text) return;

                // Calculate fixed width taken by icons, kbd badges, pills, gaps & horizontal padding
                let fixedExtra = 20; // left + right padding (~18px) + safety margin
                btn.querySelectorAll('svg').forEach(svg => {
                    fixedExtra += (svg.classList.contains('share-chevron-svg') ? 12 : 16) + 5;
                });
                btn.querySelectorAll('kbd').forEach(k => {
                    fixedExtra += (k.offsetWidth || 24) + 5;
                });
                if (btn.id === 'btn-toggle-auto-send-purchase') {
                    const pill = document.getElementById('auto-send-purchase-status-pill');
                    const ico = document.getElementById('auto-send-purchase-icon');
                    fixedExtra += (pill ? (pill.offsetWidth || 48) : 48) + (ico ? (ico.offsetWidth || 16) : 16) + 10;
                } else {
                    btn.querySelectorAll('span').forEach(sp => {
                        if (sp !== labelSpan && sp.textContent.trim().length <= 3) {
                            fixedExtra += (sp.offsetWidth || 16) + 5;
                        }
                    });
                }

                const availTextW = Math.max(24, btnW - fixedExtra);
                // Scale target max font-size proportionally with button width (grows when button is wide, shrinks when narrow)
                let px = Math.min(16.5, Math.max(11, btnW * 0.135));
                while (px > 10.5 && measureTextWidthPx(text, `900 ${px}px 'Tajawal', 'Cairo', sans-serif`) > availTextW) {
                    px -= 0.5;
                }
                btn.style.setProperty('font-size', px + 'px', 'important');
                if (labelSpan) {
                    labelSpan.style.setProperty('font-size', px + 'px', 'important');
                }
            });
        } catch (_e) {}
    };

    if (typeof ResizeObserver !== 'undefined') {
        const _purBtnResizeObs = new ResizeObserver(() => {
            if (typeof window.autoFitPurchaseBottomButtons === 'function') {
                window.autoFitPurchaseBottomButtons();
            }
        });
        window.addEventListener('DOMContentLoaded', () => {
            const tb = document.querySelector('#purchase-editor-view .items-toolbar .toolbar-actions');
            if (tb) _purBtnResizeObs.observe(tb);
            document.querySelectorAll('#purchase-editor-view .items-toolbar .toolbar-actions button').forEach(b => _purBtnResizeObs.observe(b));
            setTimeout(() => { if (window.autoFitPurchaseBottomButtons) window.autoFitPurchaseBottomButtons(); }, 80);
        });
    }

    // Smart Auto-Fit Engine for Grid Cells (Scales with Column Font Size + Auto-Shrinks Long Item/Warehouse Names)
    window.autoFitGridCells = function() {
        try {
            const cfg = window.CurrentGridConfig;
            const table = document.getElementById('pos-grid');
            if (!cfg || !cfg.columns || !table) return;
            const fontFam = cfg.fontFamily || "'Cairo', sans-serif";

            cfg.columns.forEach(col => {
                if (col.visible === false || col.visible === 'false' || col.visible === 0) return;
                const isNumCol = ['quantity', 'price', 'available', 'total', 'shila', 'shilaRem', 'fcPrice', 'totalFc'].includes(String(col.id));
                const rawBase = parseFloat(col.fontSize) || parseFloat(cfg.fontSize) || 18;
                const basePx = rawBase;
                const weight = col.fontWeight || cfg.fontWeight || '800';

                // 1. Fit Column Header (th) to basePx (scale down slightly only if column is too narrow for header word)
                const th = table.querySelector(`thead th[data-col-id="${col.id}"]`);
                if (th && col.id !== 'action') {
                    const thText = (th.textContent || col.name || '').trim();
                    const thAvailW = Math.max(36, (th.clientWidth || parseInt(col.width) || 100) - 18);
                    let thPx = Math.min(rawBase, 26);
                    if (thText && thAvailW > 25) {
                        while (thPx > 12 && measureTextWidthPx(thText, `800 ${thPx}px ${fontFam}`) > thAvailW) {
                            thPx -= 0.5;
                        }
                    }
                    th.style.setProperty('font-size', thPx + 'px', 'important');
                }

                // 2. Fit Cells (td inputs, selects, spans) to basePx & auto-fit long names to column width
                const tds = table.querySelectorAll(`tbody td[data-col-id="${col.id}"]`);
                tds.forEach(td => {
                    const ctrl = td.querySelector('input.g-input, select.g-select, .avail-val, .total-val, .row-num');
                    if (!ctrl) return;
                    let textVal = '';
                    if (ctrl.tagName === 'SELECT') {
                        textVal = (ctrl.options && ctrl.selectedIndex >= 0) ? (ctrl.options[ctrl.selectedIndex].text || '').trim() : '';
                    } else if (ctrl.tagName === 'INPUT') {
                        textVal = (ctrl.value || '').trim();
                    } else {
                        textVal = (ctrl.textContent || '').trim();
                    }

                    const tdW = ctrl.clientWidth || td.clientWidth || parseInt(col.width) || 140;
                    const paddingOffset = (col.id === 'item') ? 54 : (ctrl.tagName === 'SELECT' ? 28 : (col.id === 'price' ? 38 : (isNumCol ? 16 : 18)));
                    const availW = Math.max(32, tdW - paddingOffset);

                    let targetPx = basePx;
                    if (textVal && availW > 20) {
                        while (targetPx > 7.5 && (measureTextWidthPx(textVal, `${weight} ${targetPx}px 'Outfit', ${fontFam}`) * 1.12) > availW) {
                            targetPx -= 0.5;
                        }
                    }
                    ctrl.style.setProperty('font-size', targetPx + 'px', 'important');
                });
            });
            ['pay-credit-amount'].forEach(id => {
                const el = document.getElementById(id);
                if (el && typeof window.autoFitSingleInput === 'function') window.autoFitSingleInput(el);
            });
        } catch (e) {}
    };

    window.autoFitSingleInput = function(el, maxPx, minPx) {
        try {
            if (!el) return;
            const val = (el.value !== undefined ? String(el.value) : (el.textContent || '')).trim();
            const isPaymentBig = (el.id === 'pay-cash-amount');
            const isCreditAmt = (el.id === 'pay-credit-amount');
            let base = maxPx || (isPaymentBig ? 32 : (isCreditAmt ? 25 : 24.5));
            let colId = '';
            if (el.classList && el.classList.contains('g-input')) {
                const td = el.closest('td[data-col-id]');
                colId = td ? td.getAttribute('data-col-id') : '';
                const cfg = window.CurrentGridConfig;
                const colCfg = (cfg && cfg.columns) ? cfg.columns.find(c => String(c.id) === String(colId)) : null;
                const rawBase = parseFloat(colCfg?.fontSize) || parseFloat(cfg?.fontSize) || 18;
                base = rawBase;
            }
            const floorPx = minPx || 7.5;
            const boxW = el.clientWidth || (el.parentElement ? el.parentElement.clientWidth : 150) || 150;
            const padDeduct = (colId === 'price') ? 38 : 16;
            const availW = Math.max(32, boxW - padDeduct);
            let px = base;
            if (val && availW > 20) {
                while (px > floorPx && (measureTextWidthPx(val, `900 ${px}px 'Outfit', 'Cairo', 'Tajawal', sans-serif`) * 1.12) > availW) {
                    px -= 0.5;
                }
            }
            el.style.setProperty('font-size', px + 'px', 'important');
        } catch (_e) {}
    };

    window.applyGridSettings = function() {
        let cfg = window.CurrentGridConfig;
        if (!cfg || !cfg.columns) return;
        
        // Clear server-side pre-rendered style tag so live changes take immediate priority
        const ssrStyle = document.getElementById('dynamic-pos-grid-style');
        if (ssrStyle) ssrStyle.innerHTML = '';

        let style = document.getElementById('dynamic-grid-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-grid-style';
        }
        (document.body || document.head).appendChild(style);
        
        const globalFont = cfg.fontFamily || "'Cairo', sans-serif";
        const globalSize = (cfg.fontSize || 16) + 'px';
        const globalWeight = cfg.fontWeight || 'normal';
        const globalColorRaw = cfg.fontColor || '#ffffff';
        const globalColor = (globalColorRaw.toLowerCase() === '#ffffff' || globalColorRaw.toLowerCase() === '#fff') ? 'var(--text-primary, #ffffff)' : globalColorRaw;
        const globalAlign = cfg.align || 'center';

        let css = `
            body #pos-grid,
            body #pos-grid thead th,
            body #pos-grid tbody td,
            body #pos-grid tbody td input,
            body #pos-grid tbody td select,
            body #pos-grid tbody td span {
                font-family: ${globalFont} !important;
            }
            body #pos-grid {
                font-size: ${globalSize} !important;
                font-weight: ${globalWeight} !important;
                color: ${globalColor} !important;
            }
        `;
        
        cfg.columns.forEach(col => {
            if (col.visible === false || col.visible === "false" || col.visible === 0) {
                css += `
                    body #pos-grid th[data-col-id="${col.id}"],
                    body #pos-grid td[data-col-id="${col.id}"] {
                        display: none !important;
                    }
                `;
            } else {
                let rawW = (col.width && col.width !== 'auto') ? String(col.width).trim() : 'auto';
                if (rawW !== 'auto' && !rawW.endsWith('px') && !rawW.endsWith('%')) rawW += 'px';
                let widthCss = (rawW !== 'auto')
                    ? `width: ${rawW} !important; min-width: ${rawW} !important; max-width: ${rawW} !important;`
                    : `width: auto !important; min-width: 180px !important;`;
                let align = col.align ? col.align : globalAlign;
                let fontSize = col.fontSize ? (String(col.fontSize).replace('px','') + 'px') : globalSize;
                let colColorRaw = String(col.color ? col.color : globalColorRaw).trim();
                let colColorLc = colColorRaw.toLowerCase();
                let isDefDarkClr = ['#ffffff', '#fff', '#ffdc7a', '#facc15', '#fe9095', '#38bdf8', '#94a3b8'].includes(colColorLc) && !col.customColor;
                let darkColor = (colColorLc === '#ffffff' || colColorLc === '#fff') ? '#ffffff' : colColorRaw;
                let lightColor = isDefDarkClr ? '#120500' : ((colColorLc === '#ffffff' || colColorLc === '#fff') ? '#120500' : colColorRaw);
                let fontWeight = col.fontWeight ? col.fontWeight : globalWeight;
                let flexJustify = (align === 'center') ? 'center' : ((align === 'right') ? 'flex-start' : 'flex-end');
                
                css += `
                    html body #purchase-editor-view #pos-grid#pos-grid thead tr th[data-col-id="${col.id}"] {
                        display: table-cell !important; ${widthCss} text-align: ${align} !important;
                        color: ${darkColor} !important; -webkit-text-fill-color: ${darkColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight === 'normal' ? '800' : fontWeight} !important;
                    }
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] {
                        display: table-cell !important; ${widthCss} text-align: ${align} !important;
                        color: ${darkColor} !important; -webkit-text-fill-color: ${darkColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight} !important;
                    }
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input.is-zero-val:not(:focus),
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:hover,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:focus,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select.is-default-hint:not(:focus),
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:hover,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:focus,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val.is-zero-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val.is-zero-val,
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .row-num {
                        text-align: ${align} !important; text-align-last: ${align} !important; justify-content: ${flexJustify} !important;
                        color: ${darkColor} !important; -webkit-text-fill-color: ${darkColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight} !important;
                    }
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid thead tr th[data-col-id="${col.id}"] {
                        color: ${lightColor} !important; -webkit-text-fill-color: ${lightColor} !important;
                        font-weight: ${fontWeight === 'normal' ? '900' : fontWeight} !important; text-align: ${align} !important;
                    }
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"],
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input.is-zero-val:not(:focus),
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:hover,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:focus,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select.is-default-hint:not(:focus),
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:hover,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:focus,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .total-val.is-zero-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val.is-zero-val,
                    :root.light-theme body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"] .row-num {
                        color: ${lightColor} !important; -webkit-text-fill-color: ${lightColor} !important;
                        font-size: ${fontSize} !important; font-weight: ${fontWeight === 'normal' ? '400' : fontWeight} !important;
                        text-align: ${align} !important; text-align-last: ${align} !important; justify-content: ${flexJustify} !important;
                    }
                    html body #purchase-editor-view #pos-grid#pos-grid tbody tr td[data-col-id="${col.id}"]:not([data-col-id="item"]):not([data-col-id="action"]) > div {
                        display: flex; justify-content: ${flexJustify} !important; align-items: center;
                    }
                `;
            }
        });
        
        style.innerHTML = css;
        if (window.syncInvoiceFaintHintsAndZeros) window.syncInvoiceFaintHintsAndZeros(document);

        // DOM Reordering & Header Name Synchronization
        const table = document.getElementById('pos-grid');
        if (!table) return;

        // Reorder thead & sync column header names
        const theadTr = table.querySelector('thead tr');
        if (theadTr) {
            cfg.columns.forEach(col => {
                const th = theadTr.querySelector(`th[data-col-id="${col.id}"]`);
                if (th) {
                    if (col.name && col.id !== 'price' && col.id !== 'total') {
                        const resizer = th.querySelector('.col-resizer');
                        th.textContent = col.name;
                        if (resizer) th.appendChild(resizer);
                    }
                    theadTr.appendChild(th);
                }
            });
        }

        // Reorder tbody
        const tbodyRows = table.querySelectorAll('tbody tr');
        tbodyRows.forEach(tr => {
            if (tr.children.length === 1 && tr.children[0].colSpan > 1) return;
            cfg.columns.forEach(col => {
                const td = tr.querySelector(`td[data-col-id="${col.id}"]`);
                if (td) {
                    tr.appendChild(td);
                }
            });
        });
        
        // Reorder tfoot (if any)
        const tfootTr = table.querySelector('tfoot tr');
        if (tfootTr && tfootTr.children.length > 1) {
            cfg.columns.forEach(col => {
                const td = tfootTr.querySelector(`th[data-col-id="${col.id}"], td[data-col-id="${col.id}"]`);
                if (td) {
                    tfootTr.appendChild(td);
                }
            });
        }

        if (typeof makeTableResizable === 'function') makeTableResizable();
        if (typeof window.autoFitGridCells === 'function') {
            requestAnimationFrame(() => window.autoFitGridCells());
        }
    };

    window.addEventListener('resize', () => {
        if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
        if (typeof window.autoFitGridCells === 'function') window.autoFitGridCells();
    });
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
            if (typeof window.autoFitGridCells === 'function') window.autoFitGridCells();
        }, 180);
    });





    window.saveGridConfigToDB = async function(config) {
        if (!config || !config.grid_id) return;
        try {
            const res = await fetch('api/save_grid_config.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ grid_id: config.grid_id, config: config })
            });
            const data = await res.json();
            if (data && data.status === 'success') {
                console.log("Grid config saved to DB.");
                // Update localStorage backup if you want
                localStorage.setItem('erp_grid_config_' + config.grid_id, JSON.stringify(config));
            } else {
                console.error("Failed to save grid config", data);
            }
        } catch (e) {
            console.error("Error saving grid config:", e);
        }
    };



        window.openGridSettingsModal = function() {
        if (!window.CurrentGridConfig) return;
        tempGridConfig = JSON.parse(JSON.stringify(window.CurrentGridConfig));
        
        // General settings
        if(document.getElementById('grid-setting-font-family')) document.getElementById('grid-setting-font-family').value = tempGridConfig.fontFamily || "'Cairo', sans-serif";
        if(document.getElementById('grid-setting-font-size')) document.getElementById('grid-setting-font-size').value = tempGridConfig.fontSize || 16;
        if(document.getElementById('grid-setting-font-weight')) document.getElementById('grid-setting-font-weight').value = tempGridConfig.fontWeight || 'normal';
        if(document.getElementById('grid-setting-font-color')) document.getElementById('grid-setting-font-color').value = tempGridConfig.fontColor || '#ffffff';
        if(document.getElementById('grid-setting-align')) document.getElementById('grid-setting-align').value = tempGridConfig.align || 'center';
        if(document.getElementById('grid-setting-decimals')) document.getElementById('grid-setting-decimals').value = tempGridConfig.decimalPlaces !== undefined ? tempGridConfig.decimalPlaces : 2;
        if(document.getElementById('grid-setting-thousand-sep')) document.getElementById('grid-setting-thousand-sep').value = tempGridConfig.thousandSep || ',';
        
        selectedColIndex = 0;
        if (typeof renderModalColumnsList === 'function') renderModalColumnsList();
        if (typeof populateSelectedColPanel === 'function') populateSelectedColPanel();
        
        document.getElementById('grid-settings-modal').style.display = 'flex';
    };



    window.closeGridSettingsModal = function() {
        document.getElementById('grid-settings-modal').style.display = 'none';
    };

    window.quickAlignGrid = function(alignMode) {
        window.CurrentGridConfig.align = alignMode;
        window.CurrentGridConfig.columns.forEach(col => {
            col.align = alignMode;
        });
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        saveGridConfigToDB(window.CurrentGridConfig);
        const menu = document.getElementById('col-context-menu');
        if (menu) menu.style.display = 'none';
        showToast(`تم ضبط المحاذاة إلى ${alignMode === 'right' ? 'اليمين' : alignMode === 'center' ? 'الوسط' : 'اليسار'}`, "success");
    };

    window.quickResetGrid = async function() {
        const menu = document.getElementById('col-context-menu');
        if (menu) menu.style.display = 'none';
        try {
            const res = await fetch('api/get_grid_config.php?grid_id=purchase_grid_default&_t=' + Date.now());
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
                window.CurrentGridConfig.grid_id = 'purchase_grid';
            } else {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
            }
        } catch(e) {
            window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
        }
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        saveGridConfigToDB(window.CurrentGridConfig);
        if (tempGridConfig) {
            tempGridConfig = JSON.parse(JSON.stringify(window.CurrentGridConfig));
            if (typeof renderModalColumnsList === 'function') renderModalColumnsList();
            if (typeof populateSelectedColPanel === 'function') populateSelectedColPanel();
        }
        showToast("تم استعادة الضبط الافتراضي وحفظه في قاعدة البيانات بنجاح ✅", "success");
    };

    // 7. Column Header Dragging and Resizing
    function makeTableResizable() {
        const table = document.getElementById('pos-grid');
        if (!table) return;
        const row = table.querySelector('thead tr');
        const cols = row ? row.children : [];
        if (!cols) return;

        table.querySelectorAll('.col-resizer').forEach(r => r.remove());

        for (let i = 0; i < cols.length; i++) {
            const th = cols[i];
            const colId = th.getAttribute('data-col-id');
            if (colId === "col_del" || colId === "action") continue;

            const resizer = document.createElement('div');
            resizer.className = 'col-resizer';
            resizer.style.cssText = 'position:absolute; left:0; top:0; width:7px; height:100%; cursor:col-resize; user-select:none; z-index:15; transition: background-color 0.2s;';
            resizer.addEventListener('mouseenter', () => resizer.style.backgroundColor = 'rgba(56, 189, 248, 0.6)');
            resizer.addEventListener('mouseleave', () => resizer.style.backgroundColor = 'transparent');
            th.appendChild(resizer);

            let startX, startWidth;

            resizer.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                startX = e.clientX;
                startWidth = th.offsetWidth;

                function doDrag(ev) {
                    const dx = ev.clientX - startX;
                    const newWidth = Math.max(40, startWidth - dx); // RTL
                    
                    th.style.width = newWidth + 'px';
                    th.style.minWidth = newWidth + 'px';
                    th.style.maxWidth = newWidth + 'px';

                    const tds = table.querySelectorAll(`tbody td[data-col-id="${colId}"]`);
                    tds.forEach(td => {
                        td.style.width = newWidth + 'px';
                        td.style.minWidth = newWidth + 'px';
                        td.style.maxWidth = newWidth + 'px';
                    });
                }

                function stopDrag() {
                    document.removeEventListener('mousemove', doDrag);
                    document.removeEventListener('mouseup', stopDrag);

                    const cfg = window.CurrentGridConfig;
                    const colSetting = cfg.columns.find(c => String(c.id) === String(colId));
                    if (colSetting) {
                        colSetting.width = th.style.width;
                        saveGridConfigToDB(cfg);
                    }
                }

                document.addEventListener('mousemove', doDrag);
                document.addEventListener('mouseup', stopDrag);
            });

            // Context Menu on Right Click
            th.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                const menu = document.getElementById('col-context-menu');
                if (menu) {
                    menu.style.display = 'block';
                    menu.style.left = Math.min(window.innerWidth - 250, e.clientX) + 'px';
                    menu.style.top = Math.min(window.innerHeight - 200, e.clientY) + 'px';
                }
            });
        }
    }

    // Close Context Menu on outside click
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('col-context-menu');
        if (menu && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    // Initialize Grid Config from DB on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadGridConfigFromDB();
    });

    // Also trigger on DBReady if applicable
    document.addEventListener('DBReady', () => {
        loadGridConfigFromDB();
    });
    </script>

    <!-- Hidden OCR File/Camera Input -->
    <input type="file" id="ocr-camera-input" accept="image/*" capture="environment" style="display:none;" onchange="handleOCRFileSelect(event)">

    <!-- OCR Scanning Loading Overlay -->
    <div id="ocr-loading-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.88); backdrop-filter:blur(10px); z-index:999999; flex-direction:column; align-items:center; justify-content:center; color:#fff; font-family:'Tajawal',sans-serif; direction:rtl;">
        <div style="width:70px; height:70px; border:4px solid rgba(56,189,248,0.2); border-top-color:#38bdf8; border-radius:50%; animation:ocrSpin 1s linear infinite; margin-bottom:20px;"></div>
        <h2 style="margin:0 0 8px 0; font-size:1.4rem; color:#38bdf8; font-weight:800;">جاري فحص وقراءة الفاتورة بالرؤية الحاسوبية... 🔍</h2>
        <p style="margin:0; color:#94a3b8; font-size:0.95rem;">يتم الآن استخراج المورد، الأصناف، الكميات، والأسعار عبر محرك الذكاء الاصطناعي</p>
    </div>

    <!-- OCR Review & Mapping Modal -->
    <div id="ocr-review-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px); z-index:999998; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
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
    <?php include 'includes/financial-ocr-modal.php'; ?>
    <script src="js/magaies-offline-sync.js?v=20261014"></script>
    <script src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
    <script>
    (function initDesktopPurchaseMirrorSync() {
        let isSyncing = false;
        async function syncPurchaseWithMariaDB() {
            if (isSyncing) return;
            const purInput = document.getElementById('hdr-inv-no');
            const purSpan = document.getElementById('inv-number');
            const isCreatingNew = !window.currentViewedInvoiceNo || window.currentViewedInvoiceNo === '---';
            if (!navigator.onLine) {
                if (isCreatingNew && window.MagaiesOffline && typeof window.MagaiesOffline.peekNextSequentialNumber === 'function' && purInput && purSpan) {
                    if (!purInput.value || purInput.value === '---') {
                        const offNext = window.MagaiesOffline.peekNextSequentialNumber('purchase');
                        purInput.value = offNext;
                        purSpan.textContent = offNext;
                        if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
                    }
                }
                return;
            }
            isSyncing = true;
            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 6000) : null;
            try {
                if (window.MagaiesOffline && typeof window.MagaiesOffline.flushQueue === 'function') {
                    await window.MagaiesOffline.flushQueue();
                }
                const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now(), {
                    signal: controller ? controller.signal : undefined
                });
                if (timeoutId) clearTimeout(timeoutId);
                const data = await res.json();
                if (data && data.success && Array.isArray(data.purchases)) {
                    let localPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
                    if (!Array.isArray(localPurchases)) localPurchases = [];
                    let localMap = new Map();
                    localPurchases.forEach(inv => {
                        const no = inv && (inv.invoiceNo || inv.id);
                        if (no) localMap.set(String(no), inv);
                    });

                    let hasChanges = false;
                    data.purchases.forEach(serverInv => {
                        if (!serverInv || typeof serverInv !== 'object') return;
                        const pNo = String(serverInv.invoiceNo || serverInv.id || '').trim();
                        if (!pNo) return;
                        if (!localMap.has(pNo)) {
                            localPurchases.push(serverInv);
                            localMap.set(pNo, serverInv);
                            try { localStorage.setItem('erp_pur_' + pNo, JSON.stringify(serverInv)); } catch(e) {}
                            hasChanges = true;
                        } else {
                            const existing = localMap.get(pNo);
                            const merged = Object.assign({}, serverInv, existing);
                            if ((!merged.items || !merged.items.length) && Array.isArray(serverInv.items) && serverInv.items.length) {
                                merged.items = serverInv.items;
                                hasChanges = true;
                            }
                            if (existing.isOffline) {
                                merged.isOffline = false;
                                hasChanges = true;
                            }
                            localMap.set(pNo, merged);
                            try { localStorage.setItem('erp_pur_' + pNo, JSON.stringify(merged)); } catch(e) {}
                        }
                    });

                    if (hasChanges) {
                        const finalArr = Array.from(localMap.values());
                        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(finalArr));
                        localStorage.setItem('erp_purchases_v1', JSON.stringify(finalArr));
                        localStorage.setItem('magaies_local_purchase_invoices_v1', JSON.stringify(finalArr));
                        window._cachedPurchaseInvoicesRaw = null;
                        window._cachedPurchaseList = null;
                        if (typeof loadHubTransactions === 'function' && typeof currentPurchaseView !== 'undefined' && currentPurchaseView === 'hub') {
                            loadHubTransactions();
                        }
                    }

                    if (data.nextPurchaseNo) {
                        window._mirrorNextPurchaseNo = data.nextPurchaseNo;
                    }
                    const bestNextPurNo = (typeof peekNextInvoiceNumber === 'function') ? peekNextInvoiceNumber() : (data.nextPurchaseNo || '');
                    if (isCreatingNew && bestNextPurNo && purInput && purSpan) {
                        if (purInput.value !== bestNextPurNo) {
                            purInput.value = bestNextPurNo;
                            purSpan.textContent = bestNextPurNo;
                            if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
                        }
                    }
                }
            } catch(e) {
                console.warn('Desktop purchase mirror sync check:', e);
            } finally {
                isSyncing = false;
            }
        }
        window.initDesktopPurchaseMirrorSync = syncPurchaseWithMariaDB;

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(syncPurchaseWithMariaDB, 400);
        });
                window.addEventListener('online', syncPurchaseWithMariaDB);
    })();

    // ═══════════════════════════════════════════════════════════════════════════════
    // 🏛️ الدستور الحاكم الشامل لشاشة المشتريات (purchase.php?action=direct)
    // 1. مرشد الشاشة المعزول والمفصل من الألف إلى الياء (خانات + أزرار + مسار التسميع)
    // 2. انسيابية المؤشر 60fps والتنقل بزر Enter واختصارات الكيبورد (Ctrl+S / F2)
    // 3. منع النقرات المزدوجة + طابور عدم الاتصال الذكي + التسميع اللحظي عبر BroadcastChannel
    // ═══════════════════════════════════════════════════════════════════════════════
    (function initPurchaseConstitutionEngine() {
        const headerOrder = [
            '#hdr-date',
            '#hdr-customer-search',
            '#hdr-currency',
            '#hdr-exchange-rate',
            '#hdr-warehouse',
            '#hdr-cost-center',
            '#hdr-description',
            '#quick-item-search'
        ];
        const addSuppOrder = ['#new-cust-name', '#new-cust-phone', '#new-cust-credit'];

        const addCustModalEl = document.getElementById('add-customer-modal');
        if (addCustModalEl && typeof MutationObserver !== 'undefined') {
            new MutationObserver(() => {
                if (addCustModalEl.classList.contains('active') || addCustModalEl.style.display === 'flex' || addCustModalEl.style.display === 'block') {
                    setTimeout(() => {
                        const nm = document.getElementById('new-cust-name');
                        if (nm) { try { nm.focus(); } catch (_e) {} }
                    }, 50);
                }
            }).observe(addCustModalEl, { attributes: true, attributeFilter: ['class', 'style'] });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = [
                    'add-customer-modal',
                    'grid-settings-modal',
                    'ocr-review-modal',
                    'barcode-scanner-modal',
                    'success-modal-overlay'
                ];
                for (const mId of openModals) {
                    const mEl = document.getElementById(mId);
                    if (mEl && (mEl.classList.contains('active') || (mEl.style.display && mEl.style.display !== 'none'))) {
                        e.preventDefault();
                        if (mId === 'barcode-scanner-modal' && typeof stopCameraScanner === 'function') {
                            stopCameraScanner();
                        } else if (typeof closeModal === 'function' && mEl.classList.contains('active')) {
                            closeModal(mId);
                        } else {
                            mEl.style.display = 'none';
                            mEl.classList.remove('active');
                        }
                        return;
                    }
                }
                ['customer-dropdown-list', 'quick-item-dropdown-list', 'share-popup-menu', 'auto-send-purchase-dropdown-menu'].forEach(ddId => {
                    const dd = document.getElementById(ddId);
                    if (dd && dd.style.display !== 'none') dd.style.display = 'none';
                });
                return;
            }

            if (addCustModalEl && (addCustModalEl.classList.contains('active') || addCustModalEl.style.display === 'flex')) {
                if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                    const t = e.target;
                    const idx = addSuppOrder.findIndex(sel => t && t.matches(sel));
                    if (idx !== -1) {
                        e.preventDefault();
                        if (idx < addSuppOrder.length - 1) {
                            const nxt = document.querySelector(addSuppOrder[idx + 1]);
                            if (nxt) { nxt.focus(); if (typeof nxt.select === 'function') nxt.select(); }
                        } else if (typeof addNewCustomer === 'function') {
                            addNewCustomer();
                        }
                        return;
                    }
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'س')) {
                    e.preventDefault();
                    if (typeof addNewCustomer === 'function') addNewCustomer();
                    return;
                }
            }

            const editorView = document.getElementById('purchase-editor-view');
            const isEditorVisible = editorView && editorView.style.display !== 'none';
            if (!isEditorVisible) return;

            // F10 or Ctrl+S => Save & Approve Invoice (with smooth mandatory field focus)
            if (e.key === 'F10' || ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'س'))) {
                e.preventDefault();
                if (typeof window.smartValidateAndSubmitPurchase === 'function') {
                    window.smartValidateAndSubmitPurchase();
                } else if (typeof submitInvoice === 'function') {
                    submitInvoice();
                }
                return;
            }
            // F2 => Focus Quick Item Search
            if (e.key === 'F2') {
                e.preventDefault();
                const qSearch = document.getElementById('quick-item-search');
                if (qSearch) { qSearch.focus(); qSearch.select(); }
                return;
            }
            // F4 => Quick Add Supplier Modal
            if (e.key === 'F4') {
                e.preventDefault();
                if (typeof openModal === 'function') openModal('add-customer-modal');
                return;
            }
            // F8 => Print Current Invoice
            if (e.key === 'F8') {
                e.preventDefault();
                if (typeof printCurrentInvoice === 'function') printCurrentInvoice();
                return;
            }
            // Enter Navigation across Header Fields
            if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                const t = e.target;
                if (!t) return;
                const curIdx = headerOrder.findIndex(sel => t.matches(sel));
                if (curIdx !== -1 && curIdx < headerOrder.length - 1) {
                    // Allow customer dropdown selection if open, then move next
                    setTimeout(() => {
                        for (let i = curIdx + 1; i < headerOrder.length; i++) {
                            const nextEl = document.querySelector(headerOrder[i]);
                            if (nextEl && !nextEl.disabled && nextEl.offsetParent !== null) {
                                nextEl.focus();
                                if (typeof nextEl.select === 'function' && nextEl.tagName === 'INPUT') nextEl.select();
                                break;
                            }
                        }
                    }, 40);
                }
            }
        });

        // 🎯 Smooth Mandatory Field Auto-Focus & Visual Save Pulse (Order 4 & Order 8)
        window.smartValidateAndSubmitPurchase = function() {
            const custId = (document.getElementById('hdr-customer')?.value || '').trim();
            const custSearch = document.getElementById('hdr-customer-search');
            if (!custId && custSearch && !(custSearch.value || '').trim()) {
                custSearch.focus();
                custSearch.classList.add('input-error-shake');
                setTimeout(() => custSearch.classList.remove('input-error-shake'), 600);
                if (typeof showCustomerDropdown === 'function') showCustomerDropdown();
                if (typeof showToast === 'function') showToast('👤 يرجى اختيار المورد أولاً', 'warning', 2200);
                return;
            }
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
            if (activeRows.length === 0) {
                const firstItemInp = document.querySelector('#pos-grid input[id^="item-search-"]') || document.getElementById('quick-item-search');
                if (firstItemInp) {
                    firstItemInp.focus();
                    if (typeof firstItemInp.select === 'function') firstItemInp.select();
                }
                if (typeof showToast === 'function') showToast('📦 يرجى اختيار صنف واحد على الأقل وتحديد الكمية', 'warning', 2200);
                return;
            }
            const saveBtn = document.getElementById('btn-submit-purchase-invoice');
            if (saveBtn) {
                saveBtn.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
                saveBtn.style.transform = 'scale(0.97)';
                saveBtn.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.55), 0 0 25px rgba(16, 185, 129, 0.8)';
                setTimeout(() => {
                    saveBtn.style.transform = '';
                    saveBtn.style.boxShadow = '';
                }, 500);
            }
            if (typeof submitInvoice === 'function') submitInvoice();
        };

        // 🔢 Auto-Select ANY Numeric Field on Focus (Order 4: التظليل التلقائي للأرقام)
        document.addEventListener('focusin', function(e) {
            const t = e.target;
            if (!t || t.tagName !== 'INPUT') return;
            const isNumericField = t.type === 'number' ||
                t.getAttribute('inputmode') === 'decimal' ||
                t.hasAttribute('data-qty-input') ||
                t.hasAttribute('data-price-input') ||
                t.classList.contains('num-input') ||
                t.classList.contains('tot-input') ||
                t.id === 'pay-cash-amount' ||
                t.id === 'new-cust-credit' ||
                t.id === 'hdr-exchange-rate' ||
                t.id.startsWith('t-') ||
                t.id.startsWith('pay-');
            if (isNumericField && !t.readOnly) {
                setTimeout(() => { try { t.select(); } catch(_e){} }, 10);
            }
        });

        // 🛡️ Double-Click Prevention across Purchase Action Buttons
        document.addEventListener('click', function(e) {
            const btn = e.target && e.target.closest('button[onclick*="submitInvoice"], button[onclick*="executeDeleteTransaction"], button[onclick*="postInvoice"]');
            if (!btn) return;
            if (btn.dataset.purBusy === '1') {
                e.stopImmediatePropagation();
                e.preventDefault();
                return;
            }
            btn.dataset.purBusy = '1';
            setTimeout(() => { delete btn.dataset.purBusy; }, 1400);
        }, true);

        // 🌐 Weak Internet (2G) Micro-Packet Auto-Flush & Sub-Second Mobile ↔ Laptop Live Sync (Order 2 & 3)
        let _isFlushingPurOffline = false;
        async function flushPurchaseOfflineQueue() {
            if (!navigator.onLine || _isFlushingPurOffline) return;
            _isFlushingPurOffline = true;
            try {
                if (window.MagaiesOffline && typeof window.MagaiesOffline.flushQueue === 'function') {
                    await window.MagaiesOffline.flushQueue();
                }
                let pq = JSON.parse(localStorage.getItem('erp_purchase_offline_queue_v1') || '[]');
                if (Array.isArray(pq) && pq.length > 0) {
                    const remaining = [];
                    for (const item of pq) {
                        if (!item || !item.payload) continue;
                        try {
                            const action = item.type === 'purchase_split' ? 'save_purchase_split' : 'save_purchase_invoice';
                            const res = await fetch('api/mirror_sync.php?action=' + action, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                keepalive: true,
                                body: JSON.stringify(item.payload)
                            }).then(r => r.json());
                            if (!res || !res.success) remaining.push(item);
                        } catch (_ef) {
                            remaining.push(item);
                        }
                    }
                    localStorage.setItem('erp_purchase_offline_queue_v1', JSON.stringify(remaining));
                    if (remaining.length === 0 && typeof showToast === 'function') {
                        showToast('☁️ تم مزامنة الفواتير المحفوظة أوفلاين مع السيرفر تلقائياً', 'success', 2000);
                    }
                }
                if (typeof window.initDesktopPurchaseMirrorSync === 'function') {
                    window.initDesktopPurchaseMirrorSync();
                }
            } catch (_eFlush) {} finally {
                _isFlushingPurOffline = false;
            }
        }
        window.addEventListener('online', flushPurchaseOfflineQueue);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) flushPurchaseOfflineQueue(); });
        if (navigator.connection && typeof navigator.connection.addEventListener === 'function') {
            navigator.connection.addEventListener('change', flushPurchaseOfflineQueue);
        }
        window.addEventListener('storage', function(ev) {
            if (!ev || !ev.key) return;
            if (ev.key.includes('erp_purchase') || ev.key.includes('erp_suppliers') || ev.key.includes('erp_items')) {
                window._cachedPurchaseInvoicesRaw = null;
                window._cachedPurchaseList = null;
                if (typeof loadHubTransactions === 'function' && typeof currentPurchaseView !== 'undefined' && currentPurchaseView === 'hub') {
                    loadHubTransactions();
                }
            }
        });
        setTimeout(flushPurchaseOfflineQueue, 2000);

        // ⚖️ Landed Cost Allocation Mode Toggle (Value vs Quantity)
        window.toggleLandedCostAllocationMode = function(forceMode) {
            const inp = document.getElementById('hdr-landed-cost-mode');
            const lbl = document.getElementById('lbl-landed-cost-mode');
            const btn = document.getElementById('btn-landed-cost-mode');
            if (!inp || !lbl) return;
            const nextMode = forceMode || (inp.value === 'qty' ? 'value' : 'qty');
            inp.value = nextMode;
            if (nextMode === 'qty') {
                lbl.textContent = 'بالكمية 📦';
                lbl.style.color = '#34d399';
                if (btn) {
                    btn.style.background = 'rgba(16, 185, 129, 0.14)';
                    btn.style.borderColor = 'rgba(16, 185, 129, 0.45)';
                    btn.style.color = '#a7f3d0';
                }
                if (!forceMode && typeof showToast === 'function') {
                    showToast('📦 تم ضبط توزيع مصاريف الترحيل والعمالة على الأصناف حسب (الكمية)', 'info', 2800);
                }
            } else {
                lbl.textContent = 'بالقيمة 💰';
                lbl.style.color = '#fde68a';
                if (btn) {
                    btn.style.background = 'rgba(56, 189, 248, 0.12)';
                    btn.style.borderColor = 'rgba(56, 189, 248, 0.35)';
                    btn.style.color = '#38bdf8';
                }
                if (!forceMode && typeof showToast === 'function') {
                    showToast('💰 تم ضبط توزيع مصاريف الترحيل والعمالة على الأصناف حسب (القيمة المالية)', 'info', 2800);
                }
            }
        };

        // 🛡️ Live 3-Way Matching Engine (Invoice vs GRN / Split Deliveries)
        window.updatePurchaseThreeWayMatchBadge = function() {
            const badge = document.getElementById('purchase-3way-match-badge');
            const iconEl = document.getElementById('purchase-3way-match-icon');
            const textEl = document.getElementById('purchase-3way-match-text');
            if (!badge || !textEl) return;

            const invNo = (document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
            const invTotalQty = activeRows.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);

            let linkedGrns = [];
            ['erp_grn_v1', 'erp_purchase_grn_v1', 'erp_split_deliveries_v1', 'erp_warehouse_receipts_v1'].forEach(k => {
                try {
                    const arr = JSON.parse(localStorage.getItem(k) || '[]');
                    if (Array.isArray(arr) && invNo && invNo !== '---') {
                        arr.forEach(g => {
                            if (g && !g.deleted && g.status !== 'cancelled' && (
                                String(g.invoiceNo) === invNo ||
                                String(g.refInvoice) === invNo ||
                                String(g.parentInvoiceNo) === invNo ||
                                String(g.purchaseInvoiceNo) === invNo
                            )) {
                                linkedGrns.push(g);
                            }
                        });
                    }
                } catch (_e) {}
            });

            if (linkedGrns.length > 0) {
                let grnTotalQty = 0;
                linkedGrns.forEach(g => {
                    if (Array.isArray(g.items)) {
                        g.items.forEach(it => { grnTotalQty += (parseFloat(it.receivedQty ?? it.qty ?? it.quantity ?? 0) || 0); });
                    } else {
                        grnTotalQty += (parseFloat(g.totalQty ?? g.qty ?? 0) || 0);
                    }
                });
                const diff = Math.abs(invTotalQty - grnTotalQty);
                if (diff < 0.001 || grnTotalQty === 0) {
                    badge.style.background = 'rgba(16, 185, 129, 0.16)';
                    badge.style.borderColor = 'rgba(16, 185, 129, 0.45)';
                    badge.style.color = '#34d399';
                    if (iconEl) iconEl.textContent = '✅';
                    textEl.textContent = `المطابقة الثلاثية: مطابقة مع إذن الاستلام (${linkedGrns.length}) بنسبة 100%`;
                } else {
                    badge.style.background = 'rgba(245, 158, 11, 0.18)';
                    badge.style.borderColor = 'rgba(245, 158, 11, 0.5)';
                    badge.style.color = '#fbbf24';
                    if (iconEl) iconEl.textContent = '⚠️';
                    textEl.textContent = `المطابقة الثلاثية: الفاتورة (${invTotalQty}) ≠ المستلم مخزنياً (${grnTotalQty})`;
                }
            } else {
                badge.style.background = 'rgba(16, 185, 129, 0.12)';
                badge.style.borderColor = 'rgba(16, 185, 129, 0.35)';
                badge.style.color = '#34d399';
                if (iconEl) iconEl.textContent = '🛡️';
                textEl.textContent = activeRows.length > 0
                    ? `المطابقة الثلاثية: توريد مخزني مباشر (${activeRows.length} صنف / إجمالي كمية ${invTotalQty} متطابق 100%)`
                    : 'المطابقة الثلاثية: توريد مخزني مباشر (متطابق 100%)';
            }
        };

        window.inspectThreeWayMatchDetails = function() {
            window.updatePurchaseThreeWayMatchBadge();
            const txt = document.getElementById('purchase-3way-match-text')?.textContent || '';
            if (typeof showToast === 'function') {
                showToast(`🛡️ ${txt}`, 'info', 4000);
            }
        };

        document.addEventListener('input', function(e) {
            if (e.target && e.target.closest('#purchase-editor-view')) {
                window.updatePurchaseThreeWayMatchBadge();
            }
        });

        // 📈 Smart Purchase Price Variance Alert (>10% above lastPurchaseCost)
        document.addEventListener('change', function(e) {
            const inp = e.target;
            if (!inp || !inp.closest('#pos-grid td[data-col-id="price"]')) return;
            try {
                const tr = inp.closest('tr');
                if (!tr) return;
                const rowIdx = Array.from(tr.parentElement.children).indexOf(tr);
                const rowObj = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows[rowIdx] : null;
                if (!rowObj || !rowObj.itemId) return;
                const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                const itemObj = itemsList.find(i => String(i.id) === String(rowObj.itemId));
                if (!itemObj) return;
                const prevCost = parseFloat(itemObj.lastPurchaseCost || itemObj.costPrice || itemObj.cost || 0) || 0;
                const newPrice = parseFloat(String(inp.value).replace(/,/g, '')) || 0;
                if (prevCost > 0 && newPrice > prevCost * 1.10) {
                    const diffPct = Math.round(((newPrice - prevCost) / prevCost) * 100);
                    if (typeof showToast === 'function') {
                        showToast(`⚠️ تنبيه انحراف سعر: سعر شراء (${itemObj.name}) زاد بنسبة +${diffPct}% عن آخر تكلفة (${prevCost.toLocaleString()})`, 'warning', 4200);
                    }
                }
            } catch (_eVar) {}
        });

        // 🔄 One-Click Proforma-to-Direct Purchase Invoice Converter
        window.convertProformaToDirectInvoice = function() {
            if (typeof currentPurchaseType !== 'undefined') currentPurchaseType = 'direct';
            window.currentViewedInvoiceNo = null;
            const nextNo = (typeof peekNextInvoiceNumber === 'function') ? peekNextInvoiceNumber('direct') : (window._mirrorNextPurchaseNo || 'P2600001');
            const invSpan = document.getElementById('inv-number');
            const invInput = document.getElementById('hdr-inv-no');
            const invLbl = document.getElementById('hdr-inv-label');
            if (invSpan) invSpan.textContent = nextNo;
            if (invInput) invInput.value = nextNo;
            if (invLbl) invLbl.textContent = 'الرقم:';
            const payPanel = document.getElementById('payment-panel');
            if (payPanel) payPanel.style.display = 'block';
            if (typeof showToast === 'function') {
                showToast(`✅ تم تحويل العرض المبدئي إلى فاتورة مشتريات فعلية رقم #${nextNo} — جاهزة للحفظ والاعتماد`, 'success', 3800);
            }
        };

        window.convertHubProformaToDirect = function(invNo) {
            if (typeof editHubTransaction === 'function') {
                editHubTransaction(invNo);
                setTimeout(() => {
                    if (typeof window.convertProformaToDirectInvoice === 'function') {
                        window.convertProformaToDirectInvoice();
                    }
                }, 120);
            }
        };

        setTimeout(window.updatePurchaseThreeWayMatchBadge, 900);

        // 🛡️ Inline Non-Blocking Delete / Clear Confirmation (Zero native confirm/alert)
        window.handleSafePurchaseDeleteOrClear = function(btn) {
            if (window.currentViewedInvoiceNo && typeof confirmDeleteTransaction === 'function') {
                confirmDeleteTransaction();
                return;
            }
            const lbl = btn ? btn.querySelector('.del-btn-label') : null;
            if (btn && btn.dataset.confirmClear !== '1') {
                btn.dataset.confirmClear = '1';
                if (lbl) lbl.textContent = 'تأكيد المسح؟';
                if (typeof showToast === 'function') {
                    showToast('⚠️ اضغط مرة أخرى خلال 3 ثوانٍ لتأكيد تفريغ الفاتورة الحالية', 'warning', 2800);
                }
                setTimeout(() => {
                    if (btn) delete btn.dataset.confirmClear;
                    if (lbl) lbl.textContent = 'حذف';
                }, 3200);
                return;
            }
            if (btn) delete btn.dataset.confirmClear;
            if (lbl) lbl.textContent = 'حذف';
            if (typeof onSuccessNewInvoice === 'function') {
                onSuccessNewInvoice(false);
            }
            if (typeof showToast === 'function') {
                showToast('🧹 تم تفريغ حقول الفاتورة وتجهيز فاتورة جديدة', 'info', 2200);
            }
        };

        // 🛡️ Unsaved Invoice Guard (beforeunload protection when active rows exist)
        window.addEventListener('beforeunload', function(e) {
            try {
                const editorEl = document.getElementById('purchase-editor-view');
                if (!editorEl || editorEl.style.display === 'none') return;
                if (window.currentViewedInvoiceNo) return;
                const hasUnsavedRows = (typeof rows !== 'undefined' && Array.isArray(rows)) &&
                    rows.some(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0);
                if (hasUnsavedRows) {
                    try { if (typeof saveDraft === 'function') saveDraft(true); } catch (_ed) {}
                    e.preventDefault();
                    e.returnValue = '';
                }
            } catch (_eb) {}
        });

        if (typeof BroadcastChannel !== 'undefined') {
            try {
                const purSyncChannel = new BroadcastChannel('magaiees_sync_channel');
                purSyncChannel.onmessage = function(ev) {
                    const msg = ev && ev.data;
                    if (!msg) return;
                    if (msg.module === 'master_data' || msg.type === 'master_data_saved' || msg.type === 'purchase_invoice_saved' || msg.type === 'purchase_invoice_deleted') {
                        window._cachedPurchaseInvoicesRaw = null;
                        window._cachedPurchaseList = null;
                        if (typeof loadHubTransactions === 'function' && typeof currentPurchaseView !== 'undefined' && currentPurchaseView === 'hub') {
                            loadHubTransactions();
                        }
                    }
                };
            } catch (_ebc) {}
        }

        // === 8. Smart Supplier Proposals: Last Supply Badge, 1-Click Clone Last Invoice & Credit Limit Scheduler ===
        window.getPurchaseHistoryPool = function() {
            if (typeof getPurchaseInvoicesList === 'function') {
                const list = getPurchaseInvoicesList();
                if (Array.isArray(list) && list.length > 0) return list;
            }
            let pool = [];
            ['erp_purchase_invoices_v1', 'erp_purchases_v5', 'magaies_local_purchase_invoices_v1'].forEach(k => {
                try {
                    const arr = JSON.parse(localStorage.getItem(k) || '[]');
                    if (Array.isArray(arr)) pool = pool.concat(arr);
                } catch (_e) {}
            });
            return pool;
        };

        window.resolvePurItemMeta = function(row) {
            if (!row) return { id: '', code: '', name: '', unit: 'قطعة', cost: 0 };
            const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
            const found = itemsList.find(i => String(i.id) === String(row.itemId));
            return {
                id: row.itemId || (found ? found.id : ''),
                code: row.itemCode || (found ? (found.code || found.id) : ''),
                name: row.itemName || (found ? found.name : ''),
                unit: row.selectedUnit || row.unitName || (found ? (found.baseUnit || found.mainUnit || found.unit || 'قطعة') : 'قطعة'),
                cost: parseFloat(found ? (found.lastPurchaseCost || found.costPrice || found.price || 0) : 0) || 0
            };
        };

        window.findSupplierLastItemHistory = function(supplierId, supplierName, itemId, itemName) {
            const pool = window.getPurchaseHistoryPool();
            const norm = window.normHubAr || function(t) { return String(t || '').trim().toLowerCase(); };
            const targetSupName = norm(supplierName);
            const targetItemName = norm(itemName);

            let bestSameSupplier = null;
            let bestAnySupplier = null;

            for (let i = pool.length - 1; i >= 0; i--) {
                const inv = pool[i];
                if (!inv || !Array.isArray(inv.items)) continue;
                const invSupId = inv.supplierId || inv.vendorId || inv.customerId || inv.supplier_id || '';
                const invSupName = norm(inv.supplierName || inv.supplier || inv.vendorName || inv.customerName || '');
                const isSameSup = (supplierId && String(invSupId) === String(supplierId)) ||
                                  (targetSupName && invSupName && invSupName === targetSupName);
                const invDate = String(inv.date || inv.invoiceDate || inv.invoice_date || inv.created_at || '').split(' ')[0] || '—';

                for (let j = 0; j < inv.items.length; j++) {
                    const it = inv.items[j];
                    if (!it) continue;
                    const itId = it.itemId || it.item_id || it.id || '';
                    const itName = norm(it.itemName || it.item_name || it.name || '');
                    const matchItem = (itemId && String(itId) === String(itemId)) ||
                                      (targetItemName && itName && itName === targetItemName);
                    if (!matchItem) continue;
                    const price = parseFloat(it.price || it.unit_price || it.cost || 0) || 0;
                    if (price <= 0) continue;
                    const rec = {
                        price: price,
                        date: invDate,
                        qty: parseFloat(it.quantity || it.qty || 1) || 1,
                        unit: it.selectedUnit || it.unitName || it.unit || '',
                        invoiceNo: inv.invoiceNo || inv.invoice_number || inv.id || '',
                        sameSupplier: isSameSup
                    };
                    if (isSameSup && !bestSameSupplier) bestSameSupplier = rec;
                    if (!bestAnySupplier) bestAnySupplier = rec;
                    if (bestSameSupplier) break;
                }
                if (bestSameSupplier) break;
            }
            return bestSameSupplier || bestAnySupplier || null;
        };

        window.refreshSupplierLastSupplyHints = function() {
            if (typeof rows === 'undefined' || !Array.isArray(rows)) return;
            const supId = document.getElementById('hdr-customer')?.value || '';
            const supName = document.getElementById('hdr-customer-search')?.value || '';

            rows.forEach(function(row) {
                if (!row) return;
                const itemInput = document.getElementById('item-search-' + row.id);
                if (!itemInput || !itemInput.parentElement) return;
                let badge = itemInput.parentElement.querySelector('.supplier-last-supply-badge');

                const meta = window.resolvePurItemMeta(row);
                if (!meta.id && !meta.name) {
                    if (badge) badge.style.display = 'none';
                    return;
                }

                const hist = window.findSupplierLastItemHistory(supId, supName, meta.id, meta.name);
                const fallbackPrice = meta.cost;

                if (!hist && !(fallbackPrice > 0)) {
                    if (badge) badge.style.display = 'none';
                    return;
                }

                if (!badge) {
                    badge = document.createElement('div');
                    badge.className = 'supplier-last-supply-badge';
                    badge.style.cssText = 'display:flex; align-items:center; gap:6px; margin-top:3px; padding:2px 7px; border-radius:6px; font-size:0.71rem; font-weight:900; background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.35); color:#10b981; cursor:pointer; width:fit-content; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;';
                    itemInput.parentElement.appendChild(badge);
                }

                const lastPrice = hist ? hist.price : parseFloat(fallbackPrice);
                const lastDate = hist ? hist.date : 'سعر أساسي';
                const tagLabel = (hist && hist.sameSupplier) ? 'من نفس المورد' : (hist ? 'آخر توريد عام' : 'تكلفة البطاقة');
                const fmtPrice = (typeof fmtNum === 'function') ? fmtNum(lastPrice) : lastPrice.toLocaleString('en-US');

                badge.style.display = 'inline-flex';
                badge.title = 'اضغط لتطبيق سعر آخر شراء (' + fmtPrice + ')';
                badge.innerHTML = '<span>📅 ' + lastDate + ' (' + tagLabel + ')</span><span>•</span><span>💰 آخر شراء: <b>' + fmtPrice + '</b></span>';
                badge.onclick = function(ev) {
                    ev.stopPropagation();
                    if (typeof updateRow === 'function') {
                        updateRow(row.id, 'price', lastPrice);
                    } else {
                        row.price = lastPrice;
                    }
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                    if (typeof showToast === 'function') showToast('تم تطبيق سعر آخر شراء: ' + fmtPrice, 'success');
                };
            });
        };

        window.cloneLastSupplierPurchaseInvoice = async function() {
            const supId = document.getElementById('hdr-customer')?.value || '';
            const supName = (document.getElementById('hdr-customer-search')?.value || '').trim();
            if (!supId && !supName) {
                if (typeof showToast === 'function') showToast('يرجى اختيار المورد أولاً لنسخ أصناف آخر فاتورة له', 'warning');
                const sInp = document.getElementById('hdr-customer-search');
                if (sInp) sInp.focus();
                return;
            }

            let pool = window.getPurchaseHistoryPool();
            const norm = window.normHubAr || function(t) { return String(t || '').trim().toLowerCase(); };
            const targetSupName = norm(supName);
            const defaultWh = document.getElementById('hdr-warehouse')?.value || '';

            let lastInv = null;
            for (let i = pool.length - 1; i >= 0; i--) {
                const inv = pool[i];
                if (!inv || !Array.isArray(inv.items) || inv.items.length === 0) continue;
                const invSupId = inv.supplierId || inv.vendorId || inv.customerId || inv.supplier_id || '';
                const invSupName = norm(inv.supplierName || inv.supplier || inv.vendorName || inv.customerName || '');
                if ((supId && String(invSupId) === String(supId)) || (targetSupName && invSupName && invSupName === targetSupName)) {
                    lastInv = inv;
                    break;
                }
            }

            if (!lastInv || !Array.isArray(lastInv.items) || lastInv.items.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد فاتورة مشتريات سابقة مسجلة لهذا المورد', 'info');
                return;
            }

            if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                rows.length = 0;
                const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
                lastInv.items.forEach(function(it) {
                    const rawId = it.itemId || it.item_id || it.id || '';
                    const rawName = norm(it.itemName || it.item_name || it.name || '');
                    let matchedItem = itemsList.find(x => String(x.id) === String(rawId));
                    if (!matchedItem && rawName) {
                        matchedItem = itemsList.find(x => norm(x.name) === rawName);
                    }
                    const finalItemId = matchedItem ? matchedItem.id : rawId;
                    const priceVal = parseFloat(it.price || it.unit_price || it.cost || (matchedItem ? matchedItem.costPrice || matchedItem.price : 0) || 0) || 0;
                    const qtyVal = parseFloat(it.quantity || it.qty || 1) || 1;
                    const unitVal = it.selectedUnit || it.unitName || it.unit || (matchedItem ? (matchedItem.baseUnit || matchedItem.mainUnit || matchedItem.unit || '') : '');
                    if (typeof rowIdCounter !== 'undefined') rowIdCounter++;
                    rows.push({
                        id: (typeof rowIdCounter !== 'undefined') ? rowIdCounter : (Date.now() + Math.floor(Math.random() * 1000)),
                        itemId: finalItemId,
                        quantity: qtyVal,
                        price: priceVal,
                        warehouseId: it.warehouseId || defaultWh,
                        costCenterId: it.costCenterId || '',
                        qrCode: it.qrCode || '',
                        selectedUnit: unitVal
                    });
                });
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                setTimeout(function() {
                    window.refreshSupplierLastSupplyHints();
                    window.checkSupplierCreditLimitAndSchedule();
                    const firstQty = document.querySelector('#grid-body [data-qty-input], #grid-body td[data-col-id="quantity"] input');
                    if (firstQty) { firstQty.focus(); firstQty.select(); }
                }, 60);
                if (typeof showToast === 'function') {
                    showToast('📋 تم نسخ (' + lastInv.items.length + ') أصناف من آخر فاتورة للمورد — عدّل الكميات فقط', 'success');
                }
            }
        };

        window.checkSupplierCreditLimitAndSchedule = function() {
            const alertBar = document.getElementById('supplier-credit-limit-alert-bar');
            if (!alertBar) return;

            const supId = document.getElementById('hdr-customer')?.value || '';
            const vendorsList = (typeof VENDORS !== 'undefined' && Array.isArray(VENDORS)) ? VENDORS : [];
            if (!supId || vendorsList.length === 0) {
                alertBar.style.display = 'none';
                return;
            }

            const sup = vendorsList.find(c => String(c.id) === String(supId));
            if (!sup) {
                alertBar.style.display = 'none';
                return;
            }

            const creditLimit = parseFloat(sup.creditLimit || sup.credit_limit || 0) || 0;
            const currentDebt = Math.max(0, parseFloat(sup.totalDebt || sup.balance || 0) || 0);
            const grandTotalText = (document.getElementById('t-grand-dynamic-loc')?.textContent || document.getElementById('t-grand-local')?.textContent || '0').replace(/[^0-9.-]/g, '');
            const grandTotal = parseFloat(grandTotalText) || 0;
            const paidCash = parseFloat(String(document.getElementById('pay-cash-amount')?.value || '0').replace(/,/g, '')) || 0;
            let paidBank = 0;
            document.querySelectorAll('#bank-transfers-list input[type="number"], #bank-transfers-list .bank-amount-input').forEach(el => {
                paidBank += (parseFloat(el.value) || 0);
            });
            const newCreditRemainder = Math.max(0, grandTotal - paidCash - paidBank);
            const projectedDebt = currentDebt + newCreditRemainder;

            if (creditLimit > 0 && projectedDebt > creditLimit && newCreditRemainder > 0) {
                const excess = projectedDebt - creditLimit;
                const fmtExcess = (typeof fmtNum === 'function') ? fmtNum(excess) : excess.toLocaleString('en-US');
                const fmtLimit = (typeof fmtNum === 'function') ? fmtNum(creditLimit) : creditLimit.toLocaleString('en-US');
                alertBar.style.display = 'flex';
                alertBar.innerHTML =
                    '<span>⚠️ تنبيه ائتماني: المديونية المتوقعة تتجاوز السقف الائتماني للمورد (' + fmtLimit + ') بمقدار <b>' + fmtExcess + '</b></span>' +
                    '<button type="button" onclick="window.scheduleSupplierCreditExcess(' + excess + ')" style="background:#ef4444; color:#fff; border:none; border-radius:6px; padding:3px 9px; font-size:0.73rem; font-weight:900; cursor:pointer;">📅 جدولة الدفعة وتغطية الفارق</button>';
            } else {
                alertBar.style.display = 'none';
            }
        };

        window.scheduleSupplierCreditExcess = function(excessAmount) {
            const payPanel = document.getElementById('payment-panel');
            if (payPanel) payPanel.style.display = 'block';
            const payLayout = document.getElementById('payment-layout');
            if (payLayout) payLayout.style.display = 'flex';
            const creditBtn = document.getElementById('toggle-credit');
            if (creditBtn && !creditBtn.classList.contains('active') && typeof togglePaymentSection === 'function') {
                togglePaymentSection('credit');
            }
            const cashBtn = document.getElementById('toggle-cash');
            if (cashBtn && !cashBtn.classList.contains('active') && typeof togglePaymentSection === 'function') {
                togglePaymentSection('cash');
            }
            const cashInp = document.getElementById('pay-cash-amount');
            if (cashInp && excessAmount > 0) {
                cashInp.value = Math.ceil(excessAmount);
                if (typeof onPaymentAmountInput === 'function') onPaymentAmountInput();
            }
            const dueDateInp = document.getElementById('pay-credit-due-date');
            if (dueDateInp) {
                const d = new Date();
                d.setDate(d.getDate() + 30);
                dueDateInp.value = d.toISOString().split('T')[0];
            }
            window.checkSupplierCreditLimitAndSchedule();
            if (typeof showToast === 'function') {
                showToast('📅 تم فتح جدولة الدفعة وتخصيص الفارق الائتماني وتحديد الاستحقاق (+30 يوماً)', 'success');
            }
        };

        const _origOnCustomerChange = window.onCustomerChange;
        if (typeof _origOnCustomerChange === 'function' && !_origOnCustomerChange._wrappedProposals) {
            window.onCustomerChange = function() {
                const r = _origOnCustomerChange.apply(this, arguments);
                setTimeout(function() {
                    window.refreshSupplierLastSupplyHints();
                    window.checkSupplierCreditLimitAndSchedule();
                }, 40);
                return r;
            };
            window.onCustomerChange._wrappedProposals = true;
        }

        setTimeout(function() {
            window.refreshSupplierLastSupplyHints();
            window.checkSupplierCreditLimitAndSchedule();
        }, 400);

        // === 10. Excel/CSV Export, A4 & 80mm Thermal Print, Barcode Labels & Journal Preview ===
        window.exportCurrentPurchaseInvoiceExcel = function() {
            const invNo = (document.getElementById('inv-number')?.textContent || 'PUR').trim();
            const supName = (document.getElementById('hdr-customer-search')?.value || 'مورد عام').trim();
            const invDate = (document.getElementById('hdr-date')?.value || '').trim();
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
            if (activeRows.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد أصناف في الفاتورة لتصديرها إلى Excel', 'warning');
                return;
            }
            const csvRows = [
                ['رقم الفاتورة', invNo, 'التاريخ', invDate, 'المورد', supName, 'العملة', cur],
                [],
                ['#', 'كود الصنف', 'اسم الصنف', 'الوحدة', 'الكمية', 'سعر الوحدة', 'الإجمالي']
            ];
            activeRows.forEach((r, idx) => {
                const meta = window.resolvePurItemMeta(r);
                const q = parseFloat(r.quantity) || 0;
                const p = parseFloat(r.price) || 0;
                csvRows.push([
                    idx + 1,
                    meta.code,
                    meta.name,
                    meta.unit,
                    q,
                    p,
                    (q * p).toFixed(2)
                ]);
            });
            const grandTxt = (document.getElementById('t-grand-dynamic-loc')?.textContent || document.getElementById('t-grand-local')?.textContent || '0').trim();
            csvRows.push([]);
            csvRows.push(['', '', '', '', '', 'صافي الفاتورة الكلي', grandTxt]);

            const csvContent = '\uFEFF' + csvRows.map(row => row.map(c => '"' + String(c ?? '').replace(/"/g, '""') + '"').join(',')).join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Purchase_Invoice_' + invNo + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (typeof showToast === 'function') showToast('📊 تم تصدير فاتورة المشتريات إلى ملف Excel بنجاح', 'success');
        };

        window.exportPurchaseHubToExcel = function() {
            const trs = Array.from(document.querySelectorAll('#hub-transactions-body tr'));
            if (trs.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد فواتير معروضة حالياً لتصديرها', 'warning');
                return;
            }
            const csvRows = [['رقم الفاتورة', 'التاريخ', 'المورد', 'الأصناف', 'الإجمالي', 'الحالة', 'المخزن']];
            trs.forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 7) {
                    csvRows.push([
                        tds[0].innerText.trim(),
                        tds[1].innerText.trim(),
                        tds[2].innerText.trim(),
                        tds[3].innerText.replace(/\s+/g, ' ').trim(),
                        tds[4].innerText.trim(),
                        tds[5].innerText.trim(),
                        tds[6].innerText.trim()
                    ]);
                }
            });
            const csvContent = '\uFEFF' + csvRows.map(row => row.map(c => '"' + String(c ?? '').replace(/"/g, '""') + '"').join(',')).join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Purchase_Registry_' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (typeof showToast === 'function') showToast('📊 تم تصدير سجل المشتريات إلى Excel بنجاح', 'success');
        };

        window.printPurchaseHubReportA4 = function() {
            window.print();
        };

        window.printThermalPurchaseReceipt80mm = function() {
            const invNo = (document.getElementById('inv-number')?.textContent || 'PUR').trim();
            const supName = (document.getElementById('hdr-customer-search')?.value || 'مورد عام').trim();
            const invDate = (document.getElementById('hdr-date')?.value || '').trim();
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const grandTxt = (document.getElementById('t-grand-dynamic-loc')?.textContent || document.getElementById('t-grand-local')?.textContent || '0').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];

            const w = window.open('', '_blank', 'width=360,height=640');
            if (!w) {
                if (typeof printCurrentInvoice === 'function') printCurrentInvoice();
                return;
            }
            const rowsHtml = activeRows.map(r => {
                const meta = window.resolvePurItemMeta(r);
                const q = parseFloat(r.quantity) || 0;
                const p = parseFloat(r.price) || 0;
                return `<tr>
                    <td style="padding:4px 2px; border-bottom:1px dashed #94a3b8;">${meta.name}</td>
                    <td style="padding:4px 2px; text-align:center; border-bottom:1px dashed #94a3b8;">${q} ${meta.unit}</td>
                    <td style="padding:4px 2px; text-align:left; border-bottom:1px dashed #94a3b8; font-weight:800;">${(q * p).toLocaleString('en-US')}</td>
                </tr>`;
            }).join('');
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>إيصال مشتريات #${invNo}</title>
                <style>
                    @page { size: 80mm auto; margin: 3mm; }
                    body { font-family: 'Cairo', 'Tajawal', sans-serif; width: 74mm; margin: 0 auto; color: #000; font-size: 12px; }
                    h3 { margin: 4px 0; text-align: center; font-size: 15px; }
                    .meta { font-size: 11px; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 5px 0; margin: 6px 0; }
                    table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
                    th { border-bottom: 1.5px solid #000; padding: 4px 2px; text-align: right; }
                    .tot { margin-top: 8px; padding-top: 6px; border-top: 2px solid #000; font-size: 14px; font-weight: 900; display: flex; justify-content: space-between; }
                </style></head><body onload="window.print(); setTimeout(()=>window.close(), 600);">
                <h3>🧾 إيصال فاتورة مشتريات</h3>
                <div class="meta">
                    <div><b>رقم الفاتورة:</b> ${invNo}</div>
                    <div><b>التاريخ:</b> ${invDate}</div>
                    <div><b>المورد:</b> ${supName}</div>
                </div>
                <table>
                    <thead><tr><th>الصنف</th><th style="text-align:center;">الكمية</th><th style="text-align:left;">الإجمالي</th></tr></thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
                <div class="tot"><span>الصافي (${cur}):</span><span>${grandTxt}</span></div>
            </body></html>`);
            w.document.close();
        };

        window.printPurchaseItemsBarcodeLabels = function() {
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0)
                : [];
            if (activeRows.length === 0) {
                if (typeof showToast === 'function') showToast('أضف أصنافاً أولاً لطباعة ملصقات الباركود الخاصة بها', 'warning');
                return;
            }
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const w = window.open('', '_blank', 'width=420,height=640');
            if (!w) return;
            const labelsHtml = activeRows.map(r => {
                const meta = window.resolvePurItemMeta(r);
                const code = String(meta.code || '1001').slice(0, 14);
                const qty = Math.max(1, Math.min(20, Math.round(parseFloat(r.quantity) || 1)));
                const price = (parseFloat(r.price) || 0).toLocaleString('en-US');
                return `<div style="border:1.5px solid #000; border-radius:6px; padding:6px 8px; margin:6px auto; width:52mm; text-align:center; page-break-inside:avoid;">
                    <div style="font-size:11px; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${meta.name}</div>
                    <div style="font-family:monospace; font-size:15px; letter-spacing:3px; font-weight:900; margin:4px 0; border-top:2px solid #000; border-bottom:2px solid #000; padding:2px 0;">||| |||| || |||</div>
                    <div style="font-size:10px; font-weight:800;">#${code} • الكمية: ${qty} ${meta.unit}</div>
                    <div style="font-size:11px; font-weight:900; margin-top:2px;">السعر: ${price} ${cur}</div>
                </div>`;
            }).join('');
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>ملصقات باركود المشتريات</title>
                <style>body{font-family:'Cairo','Tajawal',sans-serif; margin:0; padding:6px; color:#000;}</style>
                </head><body onload="window.print(); setTimeout(()=>window.close(), 600);">${labelsHtml}</body></html>`);
            w.document.close();
        };
    })();
    </script>

    <style id="pur-clean-theme-style">
        #purchase-editor-view #pos-grid,
        #purchase-hub-view .pos-grid {
            border-collapse: collapse !important;
            border: 1px solid rgba(148, 163, 184, 0.18) !important;
        }
        #purchase-editor-view #pos-grid thead th,
        #purchase-hub-view .pos-grid thead th {
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.28) !important;
        }
        #purchase-editor-view #pos-grid tbody td,
        #purchase-hub-view .pos-grid tbody td {
            border: 1px solid rgba(148, 163, 184, 0.15) !important;
        }
        #pos-grid tbody tr:nth-child(even),
        #hub-transactions-body tr:nth-child(even) {
            background-color: rgba(56, 189, 248, 0.03) !important;
        }
        #pos-grid tbody tr:hover,
        #pos-grid tbody tr:focus-within,
        #hub-transactions-body tr:hover {
            background-color: rgba(56, 189, 248, 0.08) !important;
        }
        #pos-grid input,
        #pos-grid td,
        #hub-transactions-body td,
        .tot-row input,
        .tot-row span,
        #customer-financial-card span {
            font-variant-numeric: tabular-nums !important;
        }

        /* 1. CENTERED CURRENCY & GRID HEADERS (متاح، مخزن، صنف، وحدة، دولار في المنتصف) */
        #hdr-currency,
        #fin-strip-currency-btn {
            text-align: center !important;
            text-align-last: center !important;
            justify-content: center !important;
            align-items: center !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
            overflow: visible !important;
        }
        #hdr-currency option {
            text-align: center !important;
            font-weight: 800 !important;
        }
        body #pos-grid thead tr th,
        body #pos-grid thead tr th[data-col-id="item"],
        body #pos-grid thead tr th[data-col-id="available"],
        body #pos-grid thead tr th[data-col-id="warehouse"],
        body #pos-grid thead tr th[data-col-id="unit"],
        body #pos-grid thead tr th[data-col-id="quantity"],
        body #pos-grid thead tr th[data-col-id="price"],
        body #pos-grid thead tr th[data-col-id="total"],
        body #pos-grid thead tr th[data-col-id="action"] {
            text-align: center !important;
            vertical-align: middle !important;
            padding: 9px 6px !important;
        }
        body #pos-grid tbody tr td[data-col-id="available"] .avail-val,
        body #pos-grid tbody tr td[data-col-id="warehouse"] select.g-select,
        body #pos-grid tbody tr td[data-col-id="unit"] select.g-select,
        body #pos-grid tbody tr td[data-col-id="quantity"] input.g-input,
        body #pos-grid tbody tr td[data-col-id="price"] input.g-input,
        body #pos-grid tbody tr td[data-col-id="total"] .total-val {
            text-align: center !important;
            text-align-last: center !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        /* 2. SPACIOUS LUXURY GRAND TOTAL AREA (منطقة الإجمالي الكلي محاذاة مباشرة تحت عمود الإجمالي مع انعكاس تلقائي حسب عملة الفاتورة) */
        html body #purchase-editor-view .panel-header-row .quick-add-box,
        html body #btn-landed-cost-mode,
        html body #purchase-3way-match-badge {
            display: none !important;
        }
        #grid-foot tr#row-grand-total,
        #grid-foot tr.tot-row {
            background: transparent !important;
            border-top: none !important;
        }
        #grid-foot .pur-compact-tot-card {
            display: flex !important;
            direction: rtl !important;
            align-items: center !important;
            justify-content: flex-end !important;
            flex-wrap: nowrap !important;
            gap: 14px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 12px 24px 12px 16px !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(26, 45, 84, 0.96), rgba(17, 30, 58, 0.96)) !important;
            border: 2px solid rgba(56, 189, 248, 0.68) !important;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.5), 0 0 20px rgba(56, 189, 248, 0.26) !important;
            margin: 4px 0 !important;
        }
        #grid-foot #lbl-grand-dynamic {
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-size: 1.25rem !important;
            font-weight: 900 !important;
            color: #38bdf8 !important;
            white-space: nowrap !important;
            margin: 0 !important;
            letter-spacing: 0.2px !important;
        }
        #grid-foot #t-grand-dynamic-loc,
        #grid-foot #t-grand-dynamic-fc {
            font-family: 'Outfit', 'Inter', 'Tajawal', sans-serif !important;
            font-variant-numeric: tabular-nums !important;
            height: 48px !important;
            min-height: 48px !important;
            max-height: 48px !important;
            width: auto !important;
            padding: 0 20px !important;
            font-weight: 900 !important;
            border-radius: 11px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            text-align: center !important;
            white-space: nowrap !important;
            direction: ltr !important;
            letter-spacing: 0.6px !important;
            box-sizing: border-box !important;
            transition: width 0.22s ease, transform 0.24s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.24s ease, border-color 0.24s ease !important;
        }
        #grid-foot #t-grand-dynamic-loc {
            min-width: 175px !important;
            font-size: 1.68rem !important;
            border: 2px solid rgba(16, 185, 129, 0.85) !important;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.26), rgba(5, 150, 105, 0.38)) !important;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.32), inset 0 1px 2px rgba(255, 255, 255, 0.2) !important;
        }
        #grid-foot #t-grand-dynamic-fc {
            min-width: 160px !important;
            font-size: 1.52rem !important;
            color: #38bdf8 !important;
            border: 2px solid rgba(56, 189, 248, 0.8) !important;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.32)) !important;
            box-shadow: 0 4px 16px rgba(56, 189, 248, 0.28), inset 0 1px 2px rgba(255, 255, 255, 0.18) !important;
        }
        body.cur-sdg #grid-foot #t-grand-dynamic-fc {
            display: inline-flex !important;
        }
        #grid-foot #t-grand-dynamic-loc::after {
            content: attr(data-cur-unit);
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-size: 0.78rem !important;
            font-weight: 900 !important;
            padding: 2px 7px !important;
            border-radius: 6px !important;
            background: rgba(16, 185, 129, 0.32) !important;
            border: 1px solid rgba(16, 185, 129, 0.65) !important;
            color: #a7f3d0 !important;
            line-height: 1.2 !important;
            letter-spacing: 0 !important;
        }
        #grid-foot #t-grand-dynamic-fc::after {
            content: attr(data-cur-unit);
            font-family: 'Outfit', 'Inter', sans-serif !important;
            font-size: 0.88rem !important;
            font-weight: 900 !important;
            padding: 2px 7px !important;
            border-radius: 6px !important;
            background: rgba(56, 189, 248, 0.28) !important;
            border: 1px solid rgba(56, 189, 248, 0.65) !important;
            color: #bae6fd !important;
            line-height: 1.2 !important;
            letter-spacing: 0 !important;
        }
        :root.light-theme #grid-foot #t-grand-dynamic-loc {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%) !important;
            color: #064e3b !important;
            -webkit-text-fill-color: #064e3b !important;
            border: 2px solid #059669 !important;
        }
        :root.light-theme #grid-foot #t-grand-dynamic-loc::after {
            background: #059669 !important;
            border-color: #047857 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        :root.light-theme #grid-foot #t-grand-dynamic-fc {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            color: #0c4a6e !important;
            -webkit-text-fill-color: #0c4a6e !important;
            border: 2px solid #0284c7 !important;
        }
        :root.light-theme #grid-foot #t-grand-dynamic-fc::after {
            background: #0284c7 !important;
            border-color: #0369a1 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        #grid-foot tr.tot-row .tot-input {
            height: 36px !important;
            min-height: 36px !important;
            max-width: 150px !important;
            font-family: 'Outfit', 'Inter', 'Cairo', sans-serif !important;
            font-size: 1.18rem !important;
            padding: 0 10px !important;
        }

        /* 3. UNIFIED 2PX BORDERS, LIGHTENED BEIGE PALETTE, ZERO TEXT HALO & CRYSTAL ICONIC BUTTONS (2027) */

        /* ── A. Virtual Invisible Dividers Between Columns & Unclipped Cell Glow ── */
        html body #purchase-editor-view #pos-grid thead th,
        html body #purchase-editor-view #pos-grid tbody td,
        html body #purchase-editor-view #pos-grid tfoot td,
        :root.light-theme body #purchase-editor-view #pos-grid thead th,
        :root.light-theme body #purchase-editor-view #pos-grid tbody td,
        :root.light-theme body #purchase-editor-view #pos-grid tfoot td {
            border-left: none !important;
            border-right: none !important;
            overflow: visible !important;
        }
        html body #purchase-editor-view #pos-grid .col-resizer,
        html body #purchase-editor-view #pos-grid .resizer {
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            opacity: 0 !important;
        }

        /* ── B. Smooth Spring Transitions for All Regions, Fields, Headers & Crystal Buttons ── */
        html body #form-fields-card.purchase-glass-card,
        html body #invoice-info-box,
        html body #invoice-info-box .pro-serial-container,
        html body #customer-financial-card .fin-strip-inner,
        html body #fin-strip-currency-btn,
        html body #purchase-editor-view .panel-header-row,
        html body #purchase-editor-view .table-container,
        html body #purchase-editor-view .grid-scroll-wrap,
        html body #inline-totals-container,
        html body #payment-panel,
        html body #purchase-editor-view .items-toolbar,
        html body #grid-foot .pur-compact-tot-card,
        html body #form-fields-card input.v-input,
        html body #form-fields-card select.v-input,
        html body #pos-grid thead th,
        html body #pos-grid td .g-input,
        html body #pos-grid td .g-select,
        html body #pos-grid td .avail-val,
        html body #pos-grid td .total-val,
        html body #toggles-area .tot-toggle-btn,
        html body .pay-toggle-btn,
        html body #purchase-editor-view .items-toolbar button,
        html body #purchase-editor-view .del-btn,
        html body #purchase-editor-view .add-row-btn {
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1),
                        border-color 0.22s ease,
                        border-width 0.18s ease,
                        box-shadow 0.25s ease,
                        -webkit-box-shadow 0.25s ease,
                        background 0.22s ease,
                        background-color 0.22s ease,
                        filter 0.22s ease !important;
        }

        /* ── GLOBAL ZERO TEXT HALO / GLOW / SHADOW ACROSS ALL THEMES & LABELS (إلغاء أي إضاءة أو شعاع أو ظل حول الكتابة خاصة البيان ومركز الكلفة) ── */
        html body #purchase-editor-view *,
        html body #purchase-hub-view *,
        html body .modal-overlay *,
        html body *,
        html body *::before,
        html body *::after {
            text-shadow: none !important;
        }
        html body #form-fields-card .h-label,
        html body #purchase-row0-bar .h-label,
        html body #purchase-row1-bar .h-label,
        html body #purchase-row2-bar .h-label,
        html body .group-invoice .h-label,
        html body .group-date .h-label,
        html body .group-supplier .h-label,
        html body .group-currency .h-label,
        html body .group-warehouse .h-label,
        html body .group-cost-center .h-label,
        html body .group-description .h-label,
        html body .h-form-group:focus-within .h-label,
        html body .panel-title,
        html body .panel-title span,
        html body #customer-financial-card .fin-lbl,
        html body #customer-financial-card .fin-val {
            text-shadow: none !important;
            filter: none !important;
            -webkit-filter: none !important;
            opacity: 1 !important;
            font-weight: 900 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #form-fields-card .h-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-row0-bar .h-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-row1-bar .h-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-row2-bar .h-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body .group-description .h-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body .group-cost-center .h-label {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: none !important;
            filter: none !important;
        }

        /* ── C. DARK THEMES: Unified 2px Borders + Kinetic & Luminous Hover/Focus ── */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .dash-main {
            background: #040811 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #form-fields-card.purchase-glass-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .items-toolbar {
            background: linear-gradient(145deg, #213766 0%, #172749 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.68) !important;
            border-radius: 14px !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6), 0 0 16px rgba(56, 189, 248, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #form-fields-card.purchase-glass-card:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #form-fields-card.purchase-glass-card:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .items-toolbar:hover {
            transform: translateY(-3.5px) scale(1.003) !important;
            border: 2.5px solid #38bdf8 !important;
            box-shadow: 0 0 0 2.5px rgba(56, 189, 248, 0.38), 0 18px 40px rgba(0, 0, 0, 0.72), 0 0 30px rgba(56, 189, 248, 0.52), inset 0 1px 0 rgba(255, 255, 255, 0.28) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-info-box,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card .fin-strip-inner {
            background: linear-gradient(135deg, #132347 0%, #0f1c3a 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.68) !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45), 0 0 12px rgba(56, 189, 248, 0.2) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-info-box:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-info-box:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card .fin-strip-inner:hover {
            transform: translateY(-2.5px) scale(1.008) !important;
            border: 2.5px solid #7dd3fc !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.35), 0 10px 24px rgba(0, 0, 0, 0.55), 0 0 22px rgba(56, 189, 248, 0.55) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card input.v-input:not([type="hidden"]),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card select.v-input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td input.g-input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td select.g-select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td .avail-val,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td .total-val {
            background: #0c162c !important;
            background-color: #0c162c !important;
            border: 2px solid rgba(56, 189, 248, 0.65) !important;
            border-radius: 9px !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35), 0 0 0 1000px #0c162c inset !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35), 0 0 0 1000px #0c162c inset !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card input.v-input:not([type="hidden"]):hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card select.v-input:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card .h-form-group:hover input.v-input:not([type="hidden"]),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card .h-form-group:hover select.v-input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td input.g-input:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td select.g-select:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td .avail-val:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td .total-val:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td:hover input.g-input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td:hover select.g-select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td:hover .avail-val,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td:hover .total-val,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card input.v-input:not([type="hidden"]):focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card select.v-input:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td input.g-input:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td select.g-select:focus {
            border: 2.5px solid #7dd3fc !important;
            border-color: #7dd3fc !important;
            -webkit-box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.42), 0 0 22px rgba(56, 189, 248, 0.78), 0 0 0 1000px #102042 inset !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.42), 0 0 22px rgba(56, 189, 248, 0.78), 0 0 0 1000px #102042 inset !important;
            transform: translateY(-2.5px) scale(1.014) !important;
            z-index: 5 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view #pos-grid thead th {
            background: linear-gradient(180deg, #263c6e 0%, #1d2f57 100%) !important;
            border-top: 2px solid rgba(56, 189, 248, 0.55) !important;
            border-bottom: 2px solid #38bdf8 !important;
            color: #ffffff !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view #pos-grid thead th:hover {
            background: linear-gradient(180deg, #2f4b87 0%, #233a6b 100%) !important;
            box-shadow: inset 0 0 12px rgba(56, 189, 248, 0.45), 0 4px 12px rgba(56, 189, 248, 0.35) !important;
            transform: translateY(-2px) !important;
        }

        /* ── D. LIGHT THEMES: Lightened Warm Beige Palette, Stepped Contrast, Unified 2px Borders & Luminous Kinetic Hover ── */
        /* Step 1: Soft Warm Cream Background (#f3ece0) — Lighter & Airy with Clear Contrast Against Regions */
        :root.light-theme body,
        :root.light-theme .dash-main,
        :root.light-theme #purchase-editor-view,
        :root.light-theme #purchase-hub-view {
            background: #f3ece0 !important;
        }

        /* Step 2: All Main Regions (الفاتورة والمورد والبيان + أصناف الفاتورة + منطقة العمليات + الإضافات + الأزرار) — Lighter Warm Alabaster-Beige (#faf6ee -> #f5ede0) + Unified 2px Warm-Bronze Border (#9a5822) */
        :root.light-theme body #purchase-top-container #form-fields-card,
        :root.light-theme #purchase-top-container #form-fields-card,
        :root.light-theme #form-fields-card.purchase-glass-card,
        :root.light-theme #purchase-editor-view .panel-header-row,
        :root.light-theme #purchase-editor-view .table-container,
        :root.light-theme #purchase-editor-view .grid-scroll-wrap,
        :root.light-theme #inline-totals-container,
        :root.light-theme #payment-panel,
        :root.light-theme #purchase-editor-view .items-toolbar {
            background: linear-gradient(145deg, #faf6ee 0%, #f5ede0 100%) !important;
            border: 2px solid #9a5822 !important;
            border-radius: 14px !important;
            box-shadow: 0 8px 22px rgba(120, 53, 15, 0.10), inset 0 1.5px 0 #ffffff !important;
            color: #140c06 !important;
        }
        :root.light-theme body #purchase-top-container #invoice-info-box,
        :root.light-theme #invoice-info-box,
        :root.light-theme #customer-financial-card .fin-strip-inner {
            background: linear-gradient(135deg, #fdfbf7 0%, #f7efe2 100%) !important;
            border: 2px solid #9a5822 !important;
            border-radius: 11px !important;
            box-shadow: 0 3px 10px rgba(120, 53, 15, 0.09), inset 0 1px 0 #ffffff !important;
            color: #140c06 !important;
        }

        /* Region Hover & Focus-Within: Border Thickens + Golden-Amber Illumination + Smooth Lift, Returning Smoothly on Mouse Leave */
        :root.light-theme #purchase-top-container #form-fields-card:hover,
        :root.light-theme #purchase-top-container #form-fields-card:focus-within,
        :root.light-theme #form-fields-card.purchase-glass-card:hover,
        :root.light-theme #form-fields-card.purchase-glass-card:focus-within,
        :root.light-theme #purchase-editor-view .panel-header-row:hover,
        :root.light-theme #purchase-editor-view .panel-header-row:focus-within,
        :root.light-theme #purchase-editor-view .table-container:hover,
        :root.light-theme #purchase-editor-view .table-container:focus-within,
        :root.light-theme #inline-totals-container:hover,
        :root.light-theme #payment-panel:hover,
        :root.light-theme #purchase-editor-view .items-toolbar:hover {
            transform: translateY(-3px) scale(1.002) !important;
            border: 2.5px solid #b45309 !important;
            border-color: #b45309 !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.28), 0 0 22px rgba(245, 158, 11, 0.32), 0 14px 30px rgba(120, 53, 15, 0.16), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme #invoice-info-box:hover,
        :root.light-theme #invoice-info-box:focus-within,
        :root.light-theme #customer-financial-card .fin-strip-inner:hover {
            transform: translateY(-2.5px) scale(1.006) !important;
            border: 2.5px solid #b45309 !important;
            box-shadow: 0 0 0 2.5px rgba(245, 158, 11, 0.30), 0 8px 18px rgba(120, 53, 15, 0.16), inset 0 1px 0 #ffffff !important;
        }

        /* Crisp Solid Typography in Light Theme (البيان، مركز الكلفة، المورد، التاريخ، الفاتورة، إلخ — بدون أي هالة أو ظل) */
        :root.light-theme #purchase-top-container #form-fields-card .pro-serial-number,
        :root.light-theme #purchase-top-container #form-fields-card .pro-serial-btn,
        :root.light-theme #purchase-top-container #form-fields-card .h-label,
        :root.light-theme #purchase-top-container #form-fields-card label,
        :root.light-theme #purchase-row0-bar .h-label,
        :root.light-theme #purchase-row1-bar .h-label,
        :root.light-theme #purchase-row2-bar .h-label,
        :root.light-theme .group-description .h-label,
        :root.light-theme .group-cost-center .h-label,
        :root.light-theme .group-supplier .h-label,
        :root.light-theme .group-currency .h-label,
        :root.light-theme .group-warehouse .h-label,
        :root.light-theme .group-invoice .h-label,
        :root.light-theme .group-date .h-label {
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
            text-shadow: none !important;
            filter: none !important;
            opacity: 1 !important;
        }

        /* Operations Table Grid & Column Headers (المتاح، المخزن، الصنف، الوحدة، الكمية، السعر، الجملة) */
        :root.light-theme #purchase-editor-view #pos-grid {
            border: 2px solid #9a5822 !important;
            background: #faf6ee !important;
        }
        :root.light-theme #purchase-editor-view #pos-grid thead tr th {
            background: linear-gradient(180deg, #f3e9d6 0%, #eadbc2 100%) !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            border-top: 2px solid #9a5822 !important;
            border-bottom: 2px solid #9a5822 !important;
            font-weight: 900 !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75) !important;
        }
        :root.light-theme #purchase-editor-view #pos-grid thead tr th:hover {
            background: linear-gradient(180deg, #fffdf9 0%, #f5e6cc 100%) !important;
            border-top-color: #b45309 !important;
            border-bottom: 2.5px solid #d97706 !important;
            box-shadow: 0 4px 12px rgba(180, 83, 9, 0.18), inset 0 0 10px rgba(245, 158, 11, 0.22) !important;
            transform: translateY(-2px) !important;
        }
        :root.light-theme #purchase-editor-view #pos-grid tbody tr,
        :root.light-theme #purchase-editor-view #pos-grid tbody tr td {
            background: #faf6ee !important;
            border-top: 1.5px solid rgba(154, 88, 34, 0.35) !important;
            border-bottom: 1.5px solid rgba(154, 88, 34, 0.35) !important;
            color: #140c06 !important;
        }
        :root.light-theme #purchase-editor-view #pos-grid tbody tr:hover td {
            background: #f4ecde !important;
        }

        /* Step 3: Clear Visual Distinction Between (A) Writing Inputs, (B) Interactive Dropdown Selectors, and (C) 3D Tactile Push-Buttons */
        /* 3-A: Pure Writing Inputs (المورد، سعر الصرف، البيان، التاريخ، المبالغ، الكميات، الأسعار) — Calm Recessed Ivory Canvas (#ffffff) */
        :root.light-theme #purchase-top-container #form-fields-card .pro-serial-container,
        :root.light-theme #invoice-info-box .pro-serial-container,
        :root.light-theme body #purchase-editor-view input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="hidden"]),
        :root.light-theme body #purchase-editor-view textarea,
        :root.light-theme body #purchase-hub-view input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme body .modal-overlay input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        :root.light-theme body .modal-overlay textarea,
        :root.light-theme body #purchase-editor-view #form-fields-card input.v-input:not([type="hidden"]),
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-date,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-customer-search,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-exchange-rate,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-description,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-amount,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-ref,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-note,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-credit-amount,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-credit-due-date,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list input,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list input,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td input.g-input,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .avail-val,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .total-val,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td[data-col-id="total"] .total-val {
            background: #ffffff !important;
            background-color: #ffffff !important;
            border: 2px solid #9a5822 !important;
            border-radius: 9px !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
            -webkit-box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.06), 0 2px 5px rgba(120, 53, 15, 0.08), 0 0 0 1000px #ffffff inset !important;
            box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.06), 0 2px 5px rgba(120, 53, 15, 0.08), 0 0 0 1000px #ffffff inset !important;
        }

        /* 3-B: Interactive Dropdown Selectors (المخزن، مركز الكلفة، الخزنة، الوحدة، والعملة) — Tactile Selector Look with Custom Chevron Arrow (▾) & 3D Lip */
        :root.light-theme body #purchase-editor-view select,
        :root.light-theme body #purchase-hub-view select,
        :root.light-theme body .modal-overlay select,
        :root.light-theme body #purchase-editor-view #form-fields-card select.v-input,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-warehouse,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-cost-center,
        :root.light-theme body #purchase-editor-view #payment-panel #hdr-payment-account,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list select,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list select,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td select.g-select {
            -webkit-appearance: none !important;
            appearance: none !important;
            background-color: #fdf8ee !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239a5822' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(180deg, #ffffff 0%, #fdf8ee 58%, #f3e5c8 100%) !important;
            background-repeat: no-repeat, no-repeat !important;
            background-position: left 9px center, 0 0 !important;
            background-size: 13px 13px, 100% 100% !important;
            padding-left: 26px !important;
            border: 2px solid #9a5822 !important;
            border-bottom: 3px solid #78350f !important;
            border-radius: 9px !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            -webkit-box-shadow: 0 3px 8px rgba(120, 53, 15, 0.14), inset 0 1.5px 0 #ffffff !important;
            box-shadow: 0 3px 8px rgba(120, 53, 15, 0.14), inset 0 1.5px 0 #ffffff !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view select,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card select.v-input,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-warehouse,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-cost-center,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #hdr-payment-account,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #pos-grid tbody tr td select.g-select {
            -webkit-appearance: none !important;
            appearance: none !important;
            background-color: #132347 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(180deg, #1d3566 0%, #102042 100%) !important;
            background-repeat: no-repeat, no-repeat !important;
            background-position: left 9px center, 0 0 !important;
            background-size: 13px 13px, 100% 100% !important;
            padding-left: 26px !important;
            border: 2px solid rgba(56, 189, 248, 0.75) !important;
            border-bottom: 3px solid #0284c7 !important;
            cursor: pointer !important;
            -webkit-box-shadow: 0 4px 10px rgba(0, 0, 0, 0.45), inset 0 1.5px 0 rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.45), inset 0 1.5px 0 rgba(255, 255, 255, 0.22) !important;
        }

        /* 3-C: Currency Selector (#hdr-currency: جنيه / دولار / ريال / درهم / مصري) — Distinct 3D Royal Amber-Gold Pill Button Look vs Numeric Rate Input */
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency {
            -webkit-appearance: none !important;
            appearance: none !important;
            background-color: #fef3c7 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2392400e' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(180deg, #fffbeb 0%, #fef3c7 52%, #fde68a 100%) !important;
            background-repeat: no-repeat, no-repeat !important;
            background-position: left 7px center, 0 0 !important;
            background-size: 13px 13px, 100% 100% !important;
            padding-left: 22px !important;
            padding-right: 8px !important;
            border: 2px solid #b45309 !important;
            border-bottom: 3.5px solid #78350f !important;
            border-radius: 10px !important;
            color: #451a03 !important;
            -webkit-text-fill-color: #451a03 !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            -webkit-box-shadow: 0 4px 12px rgba(180, 83, 9, 0.24), inset 0 1.5px 0 #ffffff !important;
            box-shadow: 0 4px 12px rgba(180, 83, 9, 0.24), inset 0 1.5px 0 #ffffff !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-currency {
            -webkit-appearance: none !important;
            appearance: none !important;
            background-color: #1e1b4b !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23fde047' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(180deg, #312e81 0%, #1e1b4b 100%) !important;
            background-repeat: no-repeat, no-repeat !important;
            background-position: left 7px center, 0 0 !important;
            background-size: 13px 13px, 100% 100% !important;
            padding-left: 22px !important;
            padding-right: 8px !important;
            border: 2px solid #a78bfa !important;
            border-bottom: 3.5px solid #6d28d9 !important;
            border-radius: 10px !important;
            color: #fef08a !important;
            -webkit-text-fill-color: #fef08a !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            -webkit-box-shadow: 0 5px 14px rgba(0, 0, 0, 0.5), 0 0 12px rgba(167, 139, 250, 0.32), inset 0 1.5px 0 rgba(255, 255, 255, 0.28) !important;
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.5), 0 0 12px rgba(167, 139, 250, 0.32), inset 0 1.5px 0 rgba(255, 255, 255, 0.28) !important;
        }

        /* 3-D: Top Balance Currency Switch Button (#fin-strip-currency-btn: جنيه سوداني ⇄ دولار) — Unmistakable 3D Crystal Push-Button */
        html body #fin-strip-currency-btn,
        :root.light-theme body #fin-strip-currency-btn {
            background: linear-gradient(180deg, #0ea5e9 0%, #0284c7 52%, #0369a1 100%) !important;
            border: 2px solid #075985 !important;
            border-bottom: 3.5px solid #082f49 !important;
            border-radius: 9px !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 0.80rem !important;
            height: 30px !important;
            padding: 0 11px !important;
            cursor: pointer !important;
            -webkit-box-shadow: 0 4px 12px rgba(2, 132, 199, 0.38), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.38), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #fin-strip-currency-btn[data-cur-mode="USD"],
        :root.light-theme body #fin-strip-currency-btn[data-cur-mode="USD"] {
            background: linear-gradient(180deg, #10b981 0%, #059669 52%, #047857 100%) !important;
            border: 2px solid #065f46 !important;
            border-bottom: 3.5px solid #022c22 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 4px 12px rgba(5, 150, 105, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #fin-strip-currency-btn:hover,
        :root.light-theme body #fin-strip-currency-btn:hover {
            transform: translateY(-2.5px) scale(1.05) !important;
            border-color: #f59e0b !important;
            -webkit-box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.38), 0 8px 18px rgba(2, 132, 199, 0.48), inset 0 1.5px 0 rgba(255, 255, 255, 0.75) !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.38), 0 8px 18px rgba(2, 132, 199, 0.48), inset 0 1.5px 0 rgba(255, 255, 255, 0.75) !important;
            filter: brightness(1.08) !important;
        }

        /* 3-E: UNIVERSAL LUMINOUS EDGE GLOW ON ALL FIELDS (إضاءة الحواف الساحرة في كل الخانات: المورد، الملحوظة، الدولار، سعر الصرف، المخزن، مركز الكلفة، التاريخ، الخزنة، وحقول الدفع والجدول) */
        /* Light Theme Hover & Focus Illumination on EVERY Header Field, Payment/Safe Field, Extra Cost Field & Grid Cell */
        :root.light-theme #invoice-info-box .pro-serial-container:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card input.v-input:not([type="hidden"]):hover,
        :root.light-theme body #purchase-editor-view #form-fields-card select.v-input:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card .h-form-group:hover input.v-input:not([type="hidden"]),
        :root.light-theme body #purchase-editor-view #form-fields-card .h-form-group:hover select.v-input,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-date:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-customer-search:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-exchange-rate:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-description:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-amount:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-ref:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-note:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-credit-amount:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-credit-due-date:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list input:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list input:hover,
        :root.light-theme body #purchase-editor-view .tot-row input.tot-input:hover,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td input.g-input:hover,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .avail-val:hover,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .total-val:hover,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td[data-col-id="total"] .total-val:hover,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td:hover input.g-input,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td:hover .avail-val,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td:hover .total-val {
            border: 2.5px solid #d97706 !important;
            border-color: #d97706 !important;
            -webkit-box-shadow: 0 0 0 3.5px rgba(245, 158, 11, 0.36), 0 0 20px rgba(245, 158, 11, 0.52), 0 6px 16px rgba(120, 53, 15, 0.18), 0 0 0 1000px #ffffff inset !important;
            box-shadow: 0 0 0 3.5px rgba(245, 158, 11, 0.36), 0 0 20px rgba(245, 158, 11, 0.52), 0 6px 16px rgba(120, 53, 15, 0.18), 0 0 0 1000px #ffffff inset !important;
            transform: translateY(-2.5px) scale(1.014) !important;
            z-index: 4 !important;
        }
        /* Light Theme Hover & Focus Illumination on Dropdown Selectors (الدولار/العملة، المخزن، مركز الكلفة، الخزنة، والوحدة — بدون مسح السهم أو التدرج) */
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency:focus,
        :root.light-theme body #purchase-editor-view #form-fields-card .group-currency:hover #hdr-currency,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-warehouse:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-warehouse:focus,
        :root.light-theme body #purchase-editor-view #form-fields-card .group-warehouse:hover #hdr-warehouse,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-cost-center:hover,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-cost-center:focus,
        :root.light-theme body #purchase-editor-view #form-fields-card .group-cost-center:hover #hdr-cost-center,
        :root.light-theme body #purchase-editor-view #payment-panel #hdr-payment-account:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #hdr-payment-account:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list select:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list select:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list select:hover,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list select:focus,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td select.g-select:hover,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td select.g-select:focus,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td:hover select.g-select {
            border-color: #d97706 !important;
            border-bottom: 3.5px solid #92400e !important;
            -webkit-box-shadow: 0 0 0 3.5px rgba(245, 158, 11, 0.38), 0 0 22px rgba(245, 158, 11, 0.55), 0 6px 16px rgba(120, 53, 15, 0.22), inset 0 1.5px 0 #ffffff !important;
            box-shadow: 0 0 0 3.5px rgba(245, 158, 11, 0.38), 0 0 22px rgba(245, 158, 11, 0.55), 0 6px 16px rgba(120, 53, 15, 0.22), inset 0 1.5px 0 #ffffff !important;
            transform: translateY(-2.5px) scale(1.014) !important;
            z-index: 5 !important;
        }
        /* Light Theme Focus on All Text/Numeric Inputs */
        :root.light-theme body #purchase-editor-view #form-fields-card input.v-input:not([type="hidden"]):focus,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-date:focus,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-customer-search:focus,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-exchange-rate:focus,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-description:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-amount:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-ref:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-note:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-credit-amount:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-credit-due-date:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list input:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list input:focus,
        :root.light-theme body #purchase-editor-view .tot-row input.tot-input:focus,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td input.g-input:focus,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td input.g-input:active {
            border: 2.6px solid #d97706 !important;
            border-color: #d97706 !important;
            -webkit-box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.44), 0 0 24px rgba(245, 158, 11, 0.62), 0 6px 18px rgba(120, 53, 15, 0.22), 0 0 0 1000px #ffffff inset !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.44), 0 0 24px rgba(245, 158, 11, 0.62), 0 6px 18px rgba(120, 53, 15, 0.22), 0 0 0 1000px #ffffff inset !important;
            transform: translateY(-2.5px) scale(1.016) !important;
            z-index: 5 !important;
        }
        /* Dark Theme Hover & Focus Illumination on ALL Header, Payment/Safe & Grid Fields */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-currency:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-currency:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-warehouse:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-warehouse:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-cost-center:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-cost-center:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #hdr-payment-account:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #hdr-payment-account:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-cash-amount:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-cash-amount:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-cash-ref:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-cash-ref:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-cash-note:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-cash-note:focus,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-credit-amount:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #pay-credit-due-date:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #bank-transfers-list input:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #bank-transfers-list select:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #cheques-list input:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #payment-panel #cheques-list select:hover {
            border-color: #7dd3fc !important;
            -webkit-box-shadow: 0 0 0 3.5px rgba(56, 189, 248, 0.45), 0 0 24px rgba(56, 189, 248, 0.78), inset 0 1.5px 0 rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 0 0 3.5px rgba(56, 189, 248, 0.45), 0 0 24px rgba(56, 189, 248, 0.78), inset 0 1.5px 0 rgba(255, 255, 255, 0.3) !important;
            transform: translateY(-2.5px) scale(1.014) !important;
            z-index: 5 !important;
        }
        :root.light-theme #grid-foot .pur-compact-tot-card {
            background: linear-gradient(135deg, #fffdf9 0%, #f7efe2 100%) !important;
            border: 2px solid #9a5822 !important;
            box-shadow: 0 6px 16px rgba(120, 53, 15, 0.14), inset 0 2px 0 #ffffff !important;
            color: #140c06 !important;
        }
        html body #grid-foot .pur-compact-tot-card:hover {
            transform: translateY(-3px) !important;
            border: 2.5px solid #b45309 !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.30), 0 10px 24px rgba(120, 53, 15, 0.22) !important;
        }

        /* ═══════════════════════════════════════════════════════════════════════════════════════════
           4. WORLD-CLASS CRYSTAL-GEM ICONIC BUTTONS, GLASS SHIMMER & TOP/BOTTOM BAR CONTRAST (2027)
           ═══════════════════════════════════════════════════════════════════════════════════════════ */

        /* 0. Glassmorphic Specular Sheen Across Panels & Cards (لمعات زجاجية عالمية على المناطق والبطاقات) */
        :root.light-theme #form-fields-card.purchase-glass-card,
        :root.light-theme #purchase-editor-view .panel-header-row,
        :root.light-theme #purchase-editor-view .table-container,
        :root.light-theme #inline-totals-container,
        :root.light-theme #payment-panel,
        :root.light-theme #purchase-editor-view .items-toolbar {
            background-image: linear-gradient(125deg, rgba(255, 255, 255, 0.72) 0%, rgba(255, 255, 255, 0.18) 38%, rgba(250, 246, 238, 0.92) 65%, #f3e9d7 100%) !important;
            box-shadow: 0 10px 26px rgba(120, 53, 15, 0.12), inset 0 2px 0 #ffffff, inset 0 -1.5px 0 rgba(180, 83, 9, 0.18) !important;
        }

        /* A. زر تخصيص الجدول (button[onclick="openGridSettingsModal()"]) — Prominent 3D Royal Sapphire-Gold Crystal Button */
        :root.light-theme body #purchase-editor-view button[onclick="openGridSettingsModal()"],
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view button[onclick="openGridSettingsModal()"] {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 52%, #1e3a8a 100%) !important;
            border: 2px solid #1e40af !important;
            border-bottom: 3.5px solid #0f172a !important;
            border-radius: 10px !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            padding: 6px 15px !important;
            height: 38px !important;
            box-shadow: 0 6px 16px rgba(30, 58, 138, 0.36), 0 2px 5px rgba(0, 0, 0, 0.18), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view button[onclick="openGridSettingsModal()"] span {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        html body #purchase-editor-view button[onclick="openGridSettingsModal()"] svg,
        html body #purchase-editor-view button[onclick="openGridSettingsModal()"] svg * {
            stroke: #fde047 !important;
            color: #fde047 !important;
        }
        html body #purchase-editor-view button[onclick="openGridSettingsModal()"]:hover {
            transform: translateY(-3.5px) scale(1.055) !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3.5px rgba(245, 158, 11, 0.42), 0 10px 24px rgba(30, 58, 138, 0.48), 0 0 20px rgba(56, 189, 248, 0.5), inset 0 1.5px 0 rgba(255, 255, 255, 0.75) !important;
            filter: brightness(1.08) !important;
        }

        /* A-2. أزرار المورد التفاعلية (أستاذ #hdr-customer-ledger-btn، إضافة مورد #btn-quick-add-supplier، آخر فاتورة #btn-clone-last-supplier-inv) — 3D Crystal Buttons */
        html body #purchase-editor-view #hdr-customer-ledger-btn {
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 55%, #b45309 100%) !important;
            border: 1.5px solid #78350f !important;
            border-bottom: 3px solid #451a03 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            border-radius: 7px !important;
            box-shadow: 0 3px 8px rgba(180, 83, 9, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view #btn-quick-add-supplier {
            background: linear-gradient(180deg, #10b981 0%, #059669 52%, #047857 100%) !important;
            border: 2px solid #065f46 !important;
            border-bottom: 3.5px solid #022c22 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 5px 14px rgba(5, 150, 105, 0.35), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view #btn-clone-last-supplier-inv {
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important;
            border: 2px solid #0c4a6e !important;
            border-bottom: 3.5px solid #082f49 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 5px 14px rgba(2, 132, 199, 0.35), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view #btn-clone-last-supplier-inv span {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        html body #purchase-editor-view #btn-quick-add-supplier:hover,
        html body #purchase-editor-view #btn-clone-last-supplier-inv:hover,
        html body #purchase-editor-view #hdr-customer-ledger-btn:hover {
            transform: translateY(-3px) scale(1.06) !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.4), 0 8px 20px rgba(15, 23, 42, 0.32), inset 0 1.5px 0 rgba(255, 255, 255, 0.75) !important;
            filter: brightness(1.08) !important;
        }

        /* F. المنطقة الأعلى للمقاييس (توضيح كلمة الدولار وسعر الصرف والتاريخ والوقت) + الشريط السفلي الأسود (المستخدم متصل الآن باللون الفاتح الواضح) */
        html body .header-flagship-identity-suite #global-exchange-rate-box,
        :root.light-theme body .header-flagship-identity-suite #global-exchange-rate-box,
        :root.light-theme #global-exchange-rate-box {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #064e3b 100%) !important;
            border: 2px solid #fbbf24 !important;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.55), 0 0 12px rgba(251, 191, 36, 0.38), inset 0 1px 0 rgba(255, 255, 255, 0.32) !important;
        }
        html body .header-flagship-identity-suite #global-exchange-rate-box span,
        :root.light-theme body .header-flagship-identity-suite #global-exchange-rate-box span,
        :root.light-theme #global-exchange-rate-box span {
            color: #fde047 !important;
            -webkit-text-fill-color: #fde047 !important;
            font-weight: 900 !important;
            font-size: 12px !important;
        }
        html body .header-flagship-identity-suite #global-exchange-rate-box #global-exchange-rate-val,
        :root.light-theme body .header-flagship-identity-suite #global-exchange-rate-box #global-exchange-rate-val,
        :root.light-theme #global-exchange-rate-val {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-size: 13px !important;
            font-weight: 900 !important;
        }
        html body .header-flagship-identity-suite .enterprise-time-pill,
        :root.light-theme body .header-flagship-identity-suite .enterprise-time-pill {
            background: linear-gradient(135deg, #0f172a 0%, #020617 100%) !important;
            border: 1.5px solid rgba(251, 191, 36, 0.75) !important;
        }
        html body .header-flagship-identity-suite .enterprise-time-pill #header-date-mini,
        :root.light-theme body .header-flagship-identity-suite .enterprise-time-pill #header-date-mini {
            color: #fef08a !important;
            -webkit-text-fill-color: #fef08a !important;
            font-weight: 900 !important;
        }
        html body .header-flagship-identity-suite .enterprise-time-pill #header-clock,
        :root.light-theme body .header-flagship-identity-suite .enterprise-time-pill #header-clock {
            color: #34d399 !important;
            -webkit-text-fill-color: #34d399 !important;
            font-weight: 900 !important;
        }
        /* الشريط السفلي الأسود (#magaiees-bottom-protocol-bar): لأن الخلفية سوداء/داكنة، يجب أن تكون الكتابة باللون الفاتح الساطع */
        html body #magaiees-bottom-protocol-bar,
        :root.light-theme body #magaiees-bottom-protocol-bar {
            height: 28px !important;
            background: linear-gradient(90deg, #020617 0%, #0f172a 50%, #020617 100%) !important;
            border-top: 1.5px solid rgba(56, 189, 248, 0.5) !important;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
        }
        html body #magaiees-bottom-protocol-bar #header-user-name,
        :root.light-theme body #magaiees-bottom-protocol-bar #header-user-name {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 12px !important;
        }
        html body #magaiees-bottom-protocol-bar #header-user-role,
        :root.light-theme body #magaiees-bottom-protocol-bar #header-user-role {
            color: #fde047 !important;
            -webkit-text-fill-color: #fde047 !important;
            background: rgba(245, 158, 11, 0.25) !important;
            border: 1px solid rgba(251, 191, 36, 0.65) !important;
            font-weight: 900 !important;
        }
        html body #magaiees-bottom-protocol-bar #online-users-badge,
        :root.light-theme body #magaiees-bottom-protocol-bar #online-users-badge {
            background: rgba(16, 185, 129, 0.2) !important;
            border: 1px solid rgba(52, 211, 153, 0.6) !important;
            padding: 1px 8px !important;
            border-radius: 6px !important;
        }
        html body #magaiees-bottom-protocol-bar #online-users-badge span,
        html body #magaiees-bottom-protocol-bar #online-users-badge .online-label-txt,
        :root.light-theme body #magaiees-bottom-protocol-bar #online-users-badge span,
        :root.light-theme body #magaiees-bottom-protocol-bar #online-users-badge .online-label-txt {
            color: #6ee7b7 !important;
            -webkit-text-fill-color: #6ee7b7 !important;
            font-weight: 900 !important;
            font-size: 11.5px !important;
        }
        html body #magaiees-bottom-protocol-bar #online-users-badge #online-users-count,
        :root.light-theme body #magaiees-bottom-protocol-bar #online-users-badge #online-users-count {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            background: #059669 !important;
            border: 1px solid #34d399 !important;
            padding: 0 6px !important;
            border-radius: 4px !important;
            font-weight: 900 !important;
        }

        /* B. أزرار الترحيل والعمالة والإضافات والضريبة والخصم (.tot-toggle-btn) — 3D Crystal Pills with Strong Shadow & Depth */
        html body #toggles-area .tot-toggle-btn {
            border-radius: 10px !important;
            padding: 6px 14px !important;
            font-weight: 900 !important;
            font-size: 0.88rem !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-transport {
            background: linear-gradient(180deg, #ffffff 0%, #fff3e6 100%) !important;
            border: 2px solid #ea580c !important;
            color: #7c2d12 !important;
            -webkit-text-fill-color: #7c2d12 !important;
            box-shadow: 0 5px 14px rgba(234, 88, 12, 0.22), 0 2px 5px rgba(0, 0, 0, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-labor {
            background: linear-gradient(180deg, #ffffff 0%, #eaf6ff 100%) !important;
            border: 2px solid #0284c7 !important;
            color: #0c4a6e !important;
            -webkit-text-fill-color: #0c4a6e !important;
            box-shadow: 0 5px 14px rgba(2, 132, 199, 0.22), 0 2px 5px rgba(0, 0, 0, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-additions {
            background: linear-gradient(180deg, #ffffff 0%, #fef9c3 100%) !important;
            border: 2px solid #ca8a04 !important;
            color: #713f12 !important;
            -webkit-text-fill-color: #713f12 !important;
            box-shadow: 0 5px 14px rgba(202, 138, 4, 0.24), 0 2px 5px rgba(0, 0, 0, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-tax {
            background: linear-gradient(180deg, #ffffff 0%, #ffe4e6 100%) !important;
            border: 2px solid #e11d48 !important;
            color: #881337 !important;
            -webkit-text-fill-color: #881337 !important;
            box-shadow: 0 5px 14px rgba(225, 29, 72, 0.22), 0 2px 5px rgba(0, 0, 0, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-discount {
            background: linear-gradient(180deg, #ffffff 0%, #d1fae5 100%) !important;
            border: 2px solid #059669 !important;
            color: #064e3b !important;
            -webkit-text-fill-color: #064e3b !important;
            box-shadow: 0 5px 14px rgba(5, 150, 105, 0.22), 0 2px 5px rgba(0, 0, 0, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #btn-toggle-transport {
            background: linear-gradient(180deg, rgba(249, 115, 22, 0.30), rgba(194, 65, 12, 0.45)) !important;
            border: 2px solid #fb923c !important;
            color: #ffedd5 !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.55), 0 0 12px rgba(249, 115, 22, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #btn-toggle-labor {
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.30), rgba(2, 132, 199, 0.45)) !important;
            border: 2px solid #38bdf8 !important;
            color: #e0f2fe !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.55), 0 0 12px rgba(56, 189, 248, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #btn-toggle-additions {
            background: linear-gradient(180deg, rgba(234, 179, 8, 0.30), rgba(161, 98, 7, 0.45)) !important;
            border: 2px solid #facc15 !important;
            color: #fef9c3 !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.55), 0 0 12px rgba(234, 179, 8, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #btn-toggle-tax {
            background: linear-gradient(180deg, rgba(244, 63, 94, 0.30), rgba(190, 18, 60, 0.45)) !important;
            border: 2px solid #fb7185 !important;
            color: #ffe4e6 !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.55), 0 0 12px rgba(244, 63, 94, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #btn-toggle-discount {
            background: linear-gradient(180deg, rgba(16, 185, 129, 0.30), rgba(4, 120, 87, 0.45)) !important;
            border: 2px solid #34d399 !important;
            color: #d1fae5 !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.55), 0 0 12px rgba(16, 185, 129, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        html body #btn-toggle-transport:hover,
        html body #btn-toggle-transport.active {
            transform: translateY(-4px) scale(1.06) !important;
            border-color: #f97316 !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.38), 0 8px 22px rgba(249, 115, 22, 0.52), inset 0 1.5px 0 rgba(255, 255, 255, 0.65) !important;
        }
        html body #btn-toggle-labor:hover,
        html body #btn-toggle-labor.active {
            transform: translateY(-4px) scale(1.06) !important;
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.38), 0 8px 22px rgba(2, 132, 199, 0.52), inset 0 1.5px 0 rgba(255, 255, 255, 0.65) !important;
        }
        html body #btn-toggle-additions:hover,
        html body #btn-toggle-additions.active {
            transform: translateY(-4px) scale(1.06) !important;
            border-color: #eab308 !important;
            box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.38), 0 8px 22px rgba(202, 138, 4, 0.52), inset 0 1.5px 0 rgba(255, 255, 255, 0.65) !important;
        }
        html body #btn-toggle-tax:hover,
        html body #btn-toggle-tax.active {
            transform: translateY(-4px) scale(1.06) !important;
            border-color: #e11d48 !important;
            box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.38), 0 8px 22px rgba(225, 29, 72, 0.52), inset 0 1.5px 0 rgba(255, 255, 255, 0.65) !important;
        }
        html body #btn-toggle-discount:hover,
        html body #btn-toggle-discount.active {
            transform: translateY(-4px) scale(1.06) !important;
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.38), 0 8px 22px rgba(5, 150, 105, 0.52), inset 0 1.5px 0 rgba(255, 255, 255, 0.65) !important;
        }

        /* C. زر نقدي وأزرار طرق الدفع (.pay-toggle-btn: #toggle-cash، #toggle-credit، #toggle-bank، #toggle-cheque) — Prominent 3D Crystal Shadow & Depth */
        :root.light-theme body .pay-toggle-btn {
            background: linear-gradient(180deg, #ffffff 0%, #f7efe2 100%) !important;
            border: 2px solid #9a5822 !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
            box-shadow: 0 6px 16px rgba(92, 40, 8, 0.22), 0 2px 5px rgba(0, 0, 0, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #toggle-cash {
            background: linear-gradient(180deg, #ffffff 0%, #d1fae5 100%) !important;
            border: 2px solid #059669 !important;
            color: #064e3b !important;
            -webkit-text-fill-color: #064e3b !important;
            box-shadow: 0 6px 18px rgba(5, 150, 105, 0.28), 0 3px 6px rgba(0, 0, 0, 0.14), inset 0 1.5px 0 #ffffff !important;
        }
        html body .pay-toggle-btn:hover,
        html body #toggle-cash:hover,
        html body #toggle-cash.active {
            transform: translateY(-4px) scale(1.06) !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.38), 0 10px 24px rgba(5, 150, 105, 0.48), inset 0 1.5px 0 rgba(255, 255, 255, 0.6) !important;
        }

        /* D. زر الحذف الشارب الكريستالي (#btn-delete-purchase-invoice & .del-btn) — Razor-Sharp Ruby Crystal with Zero Blurry Red Bleed */
        html body #purchase-editor-view #btn-delete-purchase-invoice,
        :root.light-theme body #purchase-editor-view #btn-delete-purchase-invoice {
            background: linear-gradient(180deg, #f43f5e 0%, #e11d48 48%, #be123c 100%) !important;
            border: 2px solid #881337 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 12px rgba(15, 23, 42, 0.24), inset 0 1.5px 0 rgba(255, 255, 255, 0.42) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #btn-delete-purchase-invoice {
            border: 2px solid #fb7185 !important;
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.55), inset 0 1.5px 0 rgba(255, 255, 255, 0.38) !important;
        }
        html body #purchase-editor-view #btn-delete-purchase-invoice span,
        html body #purchase-editor-view #btn-delete-purchase-invoice svg,
        html body #purchase-editor-view #btn-delete-purchase-invoice svg * {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            stroke: #ffffff !important;
            filter: none !important;
        }
        html body #purchase-editor-view #btn-delete-purchase-invoice:hover {
            transform: translateY(-4px) scale(1.05) !important;
            border-color: #fda4af !important;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.36), 0 10px 24px rgba(159, 18, 57, 0.48), inset 0 1.5px 0 rgba(255, 255, 255, 0.6) !important;
        }
        html body #purchase-editor-view #pos-grid .del-btn,
        :root.light-theme body #purchase-editor-view #pos-grid .del-btn {
            background: linear-gradient(180deg, #f43f5e 0%, #be123c 100%) !important;
            border: 2px solid #881337 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            border-radius: 8px !important;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.20), inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
            opacity: 1 !important;
        }
        html body #purchase-editor-view #pos-grid .del-btn:hover {
            transform: translateY(-2.5px) scale(1.08) !important;
            border-color: #fb7185 !important;
            box-shadow: 0 0 0 2.5px rgba(225, 29, 72, 0.35), 0 6px 14px rgba(159, 18, 57, 0.4) !important;
        }

        /* E. أزرار شريط الأدوات السفلي الأيقونية الكريستالية (معاينة، ملخص، مسح فاتورة، إرسال تلقائي، طباعة، مشاركة، جديد، خروج، حفظ واعتماد) */
        /* 1) معاينة (#btn-preview-purchase-journal) — Crystal Sapphire Gem */
        :root.light-theme body #purchase-editor-view #btn-preview-purchase-journal {
            background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 48%, #bae6fd 100%) !important;
            border: 2px solid #0284c7 !important;
            color: #082f49 !important;
            -webkit-text-fill-color: #082f49 !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 14px rgba(2, 132, 199, 0.24), 0 2px 4px rgba(0, 0, 0, 0.08), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-preview-purchase-journal span {
            color: #082f49 !important;
            -webkit-text-fill-color: #082f49 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #btn-preview-purchase-journal {
            background: linear-gradient(180deg, #0ea5e9 0%, #0284c7 52%, #0369a1 100%) !important;
            border: 2px solid #7dd3fc !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5), 0 0 14px rgba(56, 189, 248, 0.35), inset 0 1.5px 0 rgba(255, 255, 255, 0.38) !important;
        }
        html body #purchase-editor-view #btn-preview-purchase-journal:hover {
            transform: translateY(-4px) scale(1.055) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.40), 0 10px 24px rgba(2, 132, 199, 0.45), 0 0 20px rgba(56, 189, 248, 0.55), inset 0 1.5px 0 #ffffff !important;
        }

        /* 2) ملخص (#btn-speak-purchase-summary) — Crystal Amber-Gold Gem */
        :root.light-theme body #purchase-editor-view #btn-speak-purchase-summary {
            background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 48%, #fde68a 100%) !important;
            border: 2px solid #b45309 !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 14px rgba(180, 83, 9, 0.24), 0 2px 4px rgba(0, 0, 0, 0.08), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-speak-purchase-summary span {
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #btn-speak-purchase-summary {
            background: linear-gradient(180deg, #d97706 0%, #b45309 55%, #92400e 100%) !important;
            border: 2px solid #fcd34d !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5), 0 0 14px rgba(245, 158, 11, 0.35), inset 0 1.5px 0 rgba(255, 255, 255, 0.38) !important;
        }
        html body #purchase-editor-view #btn-speak-purchase-summary:hover {
            transform: translateY(-4px) scale(1.055) !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.40), 0 10px 24px rgba(180, 83, 9, 0.45), 0 0 20px rgba(245, 158, 11, 0.55), inset 0 1.5px 0 #ffffff !important;
        }

        /* 3) مسح فاتورة (.btn-ocr) — Crystal Royal-Cyan Gem */
        html body #purchase-editor-view .btn-ocr,
        :root.light-theme body #purchase-editor-view .btn-ocr {
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important;
            border: 2px solid #38bdf8 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 14px rgba(3, 105, 161, 0.32), inset 0 1.5px 0 rgba(255, 255, 255, 0.38) !important;
        }
        html body #purchase-editor-view .btn-ocr span,
        :root.light-theme body #purchase-editor-view .btn-ocr span {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        html body #purchase-editor-view .btn-ocr svg,
        html body #purchase-editor-view .btn-ocr svg *,
        :root.light-theme body #purchase-editor-view .btn-ocr svg,
        :root.light-theme body #purchase-editor-view .btn-ocr svg * {
            stroke: #bae6fd !important;
            color: #bae6fd !important;
        }

        /* 4) إرسال تلقائي (#btn-toggle-auto-send-purchase & #btn-auto-send-options-purchase) */
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase.inactive,
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase:not(.active),
        :root.light-theme body #purchase-editor-view #btn-auto-send-options-purchase.inactive,
        :root.light-theme body #purchase-editor-view #btn-auto-send-options-purchase:not(.active) {
            background: linear-gradient(180deg, #ffffff 0%, #fffbf2 55%, #f5e6cc 100%) !important;
            border: 2px solid #9a5822 !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 12px rgba(120, 53, 15, 0.18), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase:not(.active) span:not(#auto-send-purchase-status-pill) {
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase:not(.active) #auto-send-purchase-status-pill {
            background: #f3e5c8 !important;
            border: 1.5px solid #9a5822 !important;
            color: #140c06 !important;
            -webkit-text-fill-color: #140c06 !important;
            font-weight: 900 !important;
        }

        /* 5) خروج، طباعة، مشاركة، جديد، حفظ واعتماد — Crystal 3D Polish & Luminous Hover Gestures */
        :root.light-theme body #purchase-editor-view .items-toolbar .btn-exit,
        :root.light-theme body #purchase-editor-view .items-toolbar .btn-preview,
        :root.light-theme body #purchase-editor-view .items-toolbar .btn-share,
        :root.light-theme body #purchase-editor-view .items-toolbar .btn-new {
            box-shadow: 0 5px 14px rgba(15, 23, 42, 0.18), inset 0 1.5px 0 rgba(255, 255, 255, 0.45) !important;
            border-width: 2px !important;
        }
        html body #purchase-editor-view #btn-submit-purchase-invoice {
            background: linear-gradient(180deg, #10b981 0%, #059669 52%, #047857 100%) !important;
            border: 2px solid #064e3b !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 6px 18px rgba(5, 150, 105, 0.38), inset 0 1.5px 0 rgba(255, 255, 255, 0.45) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #btn-submit-purchase-invoice {
            border: 2px solid #34d399 !important;
        }
        html body #purchase-editor-view .items-toolbar .toolbar-actions .btn-action:hover,
        html body #purchase-editor-view .items-toolbar .toolbar-actions #btn-share-invoice:hover,
        html body #purchase-editor-view #btn-toggle-auto-send-purchase:hover,
        html body #purchase-editor-view #btn-auto-send-options-purchase:hover {
            transform: translateY(-4px) scale(1.045) !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.35), 0 10px 24px rgba(15, 23, 42, 0.30), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
            filter: brightness(1.08) !important;
        }
        html body #purchase-editor-view #btn-submit-purchase-invoice:hover {
            transform: translateY(-4px) scale(1.05) !important;
            border-color: #34d399 !important;
            box-shadow: 0 0 0 3.5px rgba(16, 185, 129, 0.40), 0 12px 28px rgba(5, 150, 105, 0.55), inset 0 1.5px 0 rgba(255, 255, 255, 0.65) !important;
        }
                /* â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
           6. STEPPED LIGHT-THEME CONTRAST & DARKER TYPOGRAPHY + SALES/PURCHASE 100% BUTTON PARITY
           (ط§ظ„ط®ظ„ظپظٹط§طھ ط£ظپطھط­ ط¯ط±ط¬ط§طھ ط¯ط±ط¬ط§طھ + ط§ظ„ط®ط·ظˆط· ط£ط؛ظ…ظ‚ ظˆط£ظƒط«ط± ط­ط¯ط© ظˆطھط¨ط§ظٹظ†ط§ظ‹ + طھط·ط§ط¨ظ‚ طھط§ظ… ظ„ظ„ط£ط²ط±ط§ط± ظˆط§ظ„ط­ظˆط§ظپ)
           â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ */

        /* 6-A: Stepped Lighter Backgrounds in Light Mode (ط¯ط±ط¬ط§طھ ط§ظ„ط®ظ„ظپظٹط§طھ ط§ظ„ظپط§طھط­ط© ط§ظ„ظ…طھط¯ط±ط¬ط© ظ„طھط¨ط§ظٹظ† ظپط§ط¦ظ‚) */
        /* Degree 1: Main Screen Canvas â€” Bright Warm Porcelain-Cream (#f9f6f0) */
        :root.light-theme body,
        :root.light-theme .dash-main,
        :root.light-theme #invoice-editor-view,
        :root.light-theme #sales-hub-view,
        :root.light-theme #purchase-editor-view,
        :root.light-theme #purchase-hub-view {
            background: #f9f6f0 !important;
            background-color: #f9f6f0 !important;
        }

        /* Degree 2: Primary Regions & Panels â€” Crisp Warm Alabaster-Ivory (#ffffff -> #fcf8f0) + Sharp 2px Bronze Border */
        :root.light-theme body #sales-top-container #form-fields-card,
        :root.light-theme body #purchase-top-container #form-fields-card,
        :root.light-theme #form-fields-card.sales-glass-card,
        :root.light-theme #form-fields-card.purchase-glass-card,
        :root.light-theme #invoice-editor-view #items-panel,
        :root.light-theme #invoice-editor-view .panel-header-row,
        :root.light-theme #purchase-editor-view .panel-header-row,
        :root.light-theme #invoice-editor-view .table-container,
        :root.light-theme #purchase-editor-view .table-container,
        :root.light-theme #invoice-editor-view .grid-scroll-wrap,
        :root.light-theme #purchase-editor-view .grid-scroll-wrap,
        :root.light-theme #inline-totals-container,
        :root.light-theme #payment-panel,
        :root.light-theme #invoice-editor-view .items-toolbar,
        :root.light-theme #purchase-editor-view .items-toolbar {
            background: linear-gradient(145deg, #ffffff 0%, #fcf8f0 100%) !important;
            border: 2px solid #8a4b19 !important;
            box-shadow: 0 8px 22px rgba(92, 40, 8, 0.09), inset 0 1.5px 0 #ffffff !important;
            color: #040200 !important;
        }

        /* Degree 3: Sub-Containers (Invoice Info Box, Financial Strip, Grand Total Card, Table Header) */
        :root.light-theme #invoice-info-box,
        :root.light-theme #customer-financial-card .fin-strip-inner,
        :root.light-theme #grid-foot .pur-compact-tot-card {
            background: linear-gradient(135deg, #ffffff 0%, #fdfaf3 100%) !important;
            border: 2px solid #8a4b19 !important;
            box-shadow: 0 3px 10px rgba(92, 40, 8, 0.08), inset 0 1px 0 #ffffff !important;
            color: #040200 !important;
        }
        :root.light-theme #invoice-editor-view #pos-grid thead tr th,
        :root.light-theme #purchase-editor-view #pos-grid thead tr th {
            background: linear-gradient(180deg, #f8f2e4 0%, #efe3cc 100%) !important;
            color: #040200 !important;
            -webkit-text-fill-color: #040200 !important;
            border-top: 2px solid #8a4b19 !important;
            border-bottom: 2px solid #8a4b19 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #invoice-editor-view #pos-grid tbody tr,
        :root.light-theme #invoice-editor-view #pos-grid tbody tr td,
        :root.light-theme #purchase-editor-view #pos-grid tbody tr,
        :root.light-theme #purchase-editor-view #pos-grid tbody tr td {
            background: #ffffff !important;
            border-top: 1.5px solid rgba(138, 75, 25, 0.32) !important;
            border-bottom: 1.5px solid rgba(138, 75, 25, 0.32) !important;
            color: #040200 !important;
        }
        :root.light-theme #invoice-editor-view #pos-grid tbody tr:nth-child(even) td,
        :root.light-theme #purchase-editor-view #pos-grid tbody tr:nth-child(even) td {
            background: #fdfbf6 !important;
        }
        :root.light-theme #invoice-editor-view #pos-grid tbody tr:hover td,
        :root.light-theme #purchase-editor-view #pos-grid tbody tr:hover td {
            background: #f6efe0 !important;
        }

        /* 6-B: Darker, Deeper Typography in Light Mode (ط£ظ„ظˆط§ظ† ط§ظ„ط®ط·ظˆط· ط£ط؛ظ…ظ‚ ظˆط£ظƒط«ط± ظ‚ظˆط© ظˆظˆط¶ظˆط­ط§ظ‹) */
        :root.light-theme #form-fields-card .h-label,
        :root.light-theme #sales-row0-bar .h-label,
        :root.light-theme #sales-row1-bar .h-label,
        :root.light-theme #sales-row2-bar .h-label,
        :root.light-theme #purchase-row0-bar .h-label,
        :root.light-theme #purchase-row1-bar .h-label,
        :root.light-theme #purchase-row2-bar .h-label,
        :root.light-theme #invoice-info-box #hdr-inv-label,
        :root.light-theme #invoice-info-box #hdr-date-label,
        :root.light-theme .panel-title,
        :root.light-theme .panel-title span,
        :root.light-theme #grid-foot #lbl-grand-dynamic,
        :root.light-theme #pos-grid tbody tr td .row-num,
        :root.light-theme #pos-grid tbody tr td input.g-input:not(.is-zero-val),
        :root.light-theme #pos-grid tbody tr td select.g-select,
        :root.light-theme #pos-grid tbody tr td .avail-val:not(.is-zero-val),
        :root.light-theme #pos-grid tbody tr td .total-val:not(.is-zero-val),
        :root.light-theme #pos-grid tbody tr td .g-readonly:not(.is-zero-val),
        :root.light-theme #form-fields-card input.v-input:not([type="hidden"]):not(#hdr-exchange-rate),
        :root.light-theme #form-fields-card select.v-input:not(#hdr-currency) {
            color: #040200 !important;
            -webkit-text-fill-color: #040200 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #invoice-info-box #inv-number {
            color: #06395a !important;
            -webkit-text-fill-color: #06395a !important;
            font-weight: 900 !important;
        }
        :root.light-theme #form-fields-card #hdr-exchange-rate {
            background: #ecfdf5 !important;
            color: #022c22 !important;
            -webkit-text-fill-color: #022c22 !important;
            border: 2px solid #047857 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-wrap-ledger .fin-lbl {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-ledger {
            color: #040200 !important;
            -webkit-text-fill-color: #040200 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-wrap-debt .fin-lbl {
            color: #7f1d1d !important;
            -webkit-text-fill-color: #7f1d1d !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-total-debt {
            color: #881337 !important;
            -webkit-text-fill-color: #881337 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-wrap-checks .fin-lbl {
            color: #78350f !important;
            -webkit-text-fill-color: #78350f !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-checks {
            color: #451a03 !important;
            -webkit-text-fill-color: #451a03 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-wrap-limit .fin-lbl {
            color: #0c4a6e !important;
            -webkit-text-fill-color: #0c4a6e !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-credit-limit {
            color: #082f49 !important;
            -webkit-text-fill-color: #082f49 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #customer-financial-card #fin-wrap-remaining {
            background: #d1fae5 !important;
            border: 1.5px solid #047857 !important;
        }
        :root.light-theme #customer-financial-card #fin-wrap-remaining .fin-lbl,
        :root.light-theme #customer-financial-card #fin-remaining {
            color: #022c22 !important;
            -webkit-text-fill-color: #022c22 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #grid-foot #t-grand-dynamic-loc {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%) !important;
            color: #022c22 !important;
            -webkit-text-fill-color: #022c22 !important;
            border: 2px solid #047857 !important;
        }
        :root.light-theme #grid-foot #t-grand-dynamic-fc {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            color: #082f49 !important;
            -webkit-text-fill-color: #082f49 !important;
            border: 2px solid #0369a1 !important;
        }

        /* ═══ STRICT RIGHT-TO-LEFT STRAIGHT VERTICAL LINE ALIGNMENT & +50% REGION SPACING (12PX) ═══ */
        .dash-main#pur-proportional-root {
            width: calc(100% - 24px) !important;
            max-width: calc(100% - 24px) !important;
            margin-inline: auto !important;
            padding: 8px 0 24px 0 !important;
            gap: 12px !important;
            box-sizing: border-box !important;
        }
        .dash-main#pur-proportional-root > header.premium-header.magical-glass-header,
        body > header.premium-header.magical-glass-header {
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 6px 12px !important;
            border-radius: 14px !important;
            animation: none !important;
            box-sizing: border-box !important;
        }
        .magical-glass-header::before,
        .magical-glass-header::after,
        .premium-header::before,
        .premium-header::after,
        .msg-quartet-btn::before,
        .msg-quartet-btn::after {
            display: none !important;
            animation: none !important;
            content: none !important;
        }
        .msg-fixed-command-pod {
            height: 78px !important;
            min-height: 78px !important;
            max-height: 78px !important;
            animation: none !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.45) !important;
        }
        #purchase-editor-view,
        #purchase-hub-view,
        #purchase-top-container,
        .purchase-bottom {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-top: 0 !important;
            box-sizing: border-box !important;
        }
        #purchase-hub-view {
            gap: 12px !important;
        }
        #purchase-hub-view > div {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }
        #purchase-editor-view {
            gap: 12px !important;
        }
        .purchase-bottom {
            margin-top: 0 !important;
            gap: 12px !important;
        }
        #purchase-top-container #form-fields-card > div {
            gap: 13.5px !important;
        }
        #purchase-top-container #form-fields-card,
        .purchase-bottom > .panel-header-row,
        .purchase-bottom > #inline-totals-container,
        .purchase-bottom > #payment-panel,
        .purchase-bottom > .items-toolbar {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }
        .purchase-bottom > .table-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .purchase-bottom .table-container > .grid-scroll-wrap {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }
        :root.light-theme .purchase-bottom .table-container > .grid-scroll-wrap {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        #pos-grid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
        }

        /* ═══ STEPPED CONTRAST & INTERACTIVE REGION/BORDER ILLUMINATION (DARK & LIGHT THEMES) ═══ */
        /* 1. Dark Theme Canvas vs Main Regions Contrast */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .dash-main#pur-proportional-root,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view {
            background: #030712 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .dash-main#pur-proportional-root > header.premium-header.magical-glass-header,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #form-fields-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .items-toolbar {
            background: linear-gradient(145deg, #152342 0%, #0f1a33 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.52) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.14) !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease !important;
        }
        /* Main Region Border Illumination on Hover / Focus */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .dash-main#pur-proportional-root > header.premium-header.magical-glass-header:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #form-fields-card:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #form-fields-card:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .items-toolbar:hover {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.35), 0 0 24px rgba(56, 189, 248, 0.55), 0 12px 30px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.25) !important;
        }

        /* 2. Inner Sub-Regions (المورد، الصرف، المخزن، البيان، مركز الكلفة، وتاريخ ورقم الفاتورة) Stepped Capsules */
        #purchase-row1-bar .h-form-group,
        #purchase-row2-bar .h-form-group {
            padding: 3px 6px !important;
            border-radius: 10px !important;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #invoice-info-box,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card .fin-strip-inner,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .h-form-group,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row2-bar .h-form-group {
            background: linear-gradient(135deg, #1d315c 0%, #162649 100%) !important;
            border: 1.5px solid rgba(56, 189, 248, 0.42) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 3px 10px rgba(0, 0, 0, 0.35) !important;
        }
        /* Distinct Contrast for Field Labels (كلمة المشتريات والمورد، البيان، المركز، المخزن، الصرف، التاريخ، رقم الفاتورة) */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-info-box #hdr-inv-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-info-box #hdr-date-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .h-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row2-bar .h-label {
            background: rgba(9, 16, 34, 0.82) !important;
            border: 1px solid rgba(125, 211, 252, 0.38) !important;
            border-radius: 7px !important;
            padding: 0 8px !important;
            height: 32px !important;
            line-height: 32px !important;
            color: #f8fafc !important;
            -webkit-text-fill-color: #f8fafc !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .group-supplier > .h-label {
            background: linear-gradient(135deg, rgba(180, 83, 9, 0.45), rgba(120, 53, 15, 0.65)) !important;
            border: 1.5px solid #fbbf24 !important;
            color: #fef08a !important;
            -webkit-text-fill-color: #fef08a !important;
        }
        /* Sub-Region Hover & Focus Illumination (الحواف والمنطقة تضيء عند المرور عليها) */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #invoice-info-box:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #invoice-info-box:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card .fin-strip-inner:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .h-form-group:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .h-form-group:focus-within,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row2-bar .h-form-group:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row2-bar .h-form-group:focus-within {
            background: linear-gradient(135deg, #27447e 0%, #1d3566 100%) !important;
            border-color: #7dd3fc !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.35), 0 0 20px rgba(56, 189, 248, 0.55), inset 0 0 14px rgba(56, 189, 248, 0.18) !important;
        }
        /* Special Full-Area + Border Illumination for Supplier Region (منطقة المورد تضيء بالكامل مع الحواف) */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .group-supplier:hover,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .group-supplier:focus-within {
            background: linear-gradient(135deg, #2d4f91 0%, #1f396d 55%, #3b2a14 100%) !important;
            border: 2px solid #fbbf24 !important;
            box-shadow: 0 0 0 2.5px rgba(251, 191, 36, 0.38), 0 0 26px rgba(251, 191, 36, 0.55), 0 0 32px rgba(56, 189, 248, 0.45), inset 0 0 18px rgba(251, 191, 36, 0.22) !important;
        }

        /* 3. Light Theme Sub-Region Contrast, Deep Espresso-Brown Labels & Sculpted Thick Borders */
        :root.light-theme #purchase-row1-bar .h-form-group,
        :root.light-theme #purchase-row2-bar .h-form-group {
            background: linear-gradient(135deg, #ffffff 0%, #f7efe0 100%) !important;
            border: 2.5px solid #6b3410 !important;
            box-shadow: 0 4px 12px rgba(69, 26, 3, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme #invoice-info-box #hdr-inv-label,
        :root.light-theme #invoice-info-box #hdr-date-label,
        :root.light-theme #purchase-row1-bar .h-label,
        :root.light-theme #purchase-row2-bar .h-label {
            background: linear-gradient(180deg, #f6ecda 0%, #ead7b7 100%) !important;
            border: 2px solid #5c2808 !important;
            border-radius: 8px !important;
            padding: 0 10px !important;
            height: 34px !important;
            line-height: 31px !important;
            color: #2b1103 !important;
            -webkit-text-fill-color: #2b1103 !important;
            font-weight: 900 !important;
            font-size: 0.94rem !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85), 0 2px 5px rgba(69, 26, 3, 0.12) !important;
        }
        :root.light-theme #purchase-row1-bar .h-form-group:hover,
        :root.light-theme #purchase-row1-bar .h-form-group:focus-within,
        :root.light-theme #purchase-row2-bar .h-form-group:hover,
        :root.light-theme #purchase-row2-bar .h-form-group:focus-within {
            background: linear-gradient(135deg, #fffdf9 0%, #f9e8c2 100%) !important;
            border: 2.5px solid #b45309 !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.36), 0 0 22px rgba(245, 158, 11, 0.45), inset 0 1.5px 0 #ffffff !important;
        }
        :root.light-theme #purchase-row1-bar .group-supplier:hover,
        :root.light-theme #purchase-row1-bar .group-supplier:focus-within {
            background: linear-gradient(135deg, #fffbeb 0%, #fde68a 100%) !important;
            border: 2.5px solid #92400e !important;
            box-shadow: 0 0 0 3.5px rgba(245, 158, 11, 0.44), 0 0 26px rgba(245, 158, 11, 0.55) !important;
        }

        /* ═══ 7. OPHTHALMIC & VISUAL HARMONY MASTERPIECE (DARK & LIGHT THEMES — 2027) ═══ */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .dash-main#pur-proportional-root,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view {
            background: #010308 !important;
            background-color: #010308 !important;
        }
        :root.light-theme body,
        :root.light-theme .dash-main#pur-proportional-root,
        :root.light-theme #purchase-editor-view,
        :root.light-theme #purchase-hub-view {
            background: linear-gradient(180deg, var(--lt-canvas-1, #efe6d6) 0%, var(--lt-canvas-2, #e5d8c2) 100%) !important;
            background-color: var(--lt-canvas-1, #efe6d6) !important;
        }
        /* Deeper Canvas vs Brighter, Higher-Contrast Regions (+50% Gap Visibility) */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .dash-main#pur-proportional-root > header.premium-header.magical-glass-header {
            background: linear-gradient(145deg, #1b3159 0%, #132341 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.78) !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.92), inset 0 1.5px 0 rgba(255, 255, 255, 0.28) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #form-fields-card {
            background: linear-gradient(145deg, #1d345e 0%, #152647 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.80) !important;
            box-shadow: 0 12px 34px rgba(0, 0, 0, 0.92), inset 0 1.5px 0 rgba(255, 255, 255, 0.28) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row {
            background: linear-gradient(145deg, #1f3763 0%, #17294c 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.78) !important;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.90), inset 0 1.5px 0 rgba(255, 255, 255, 0.28) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container {
            background: linear-gradient(145deg, #1b3057 0%, #132240 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.80) !important;
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.94), inset 0 1.5px 0 rgba(255, 255, 255, 0.26) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container {
            background: linear-gradient(145deg, #1e3560 0%, #152748 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.76) !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.92), inset 0 1.5px 0 rgba(255, 255, 255, 0.26) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .items-toolbar {
            background: linear-gradient(145deg, #1c325b 0%, #132240 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.82) !important;
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.95), inset 0 1.5px 0 rgba(255, 255, 255, 0.30) !important;
        }
        /* Pure Crisp White (#ffffff) + Sharp Black Shadow for All Colored Iconic & Toolbar Buttons (Dark & Light Parity) */
        html body #purchase-screen-title-badge,
        html body #purchase-screen-title-badge span,
        html body #fin-strip-currency-btn,
        html body #btn-quick-add-supplier,
        html body #btn-clone-last-supplier-inv,
        html body #btn-clone-last-supplier-inv span,
        html body #purchase-editor-view button[onclick="openGridSettingsModal()"],
        html body #purchase-editor-view button[onclick="openGridSettingsModal()"] span,
        html body #purchase-editor-view button[onclick*="openPurchaseAIAuditModal"],
        html body #purchase-editor-view button[onclick*="openPurchaseAIAuditModal"] span,
        html body #purchase-editor-view .items-toolbar .btn-action,
        html body #purchase-editor-view .items-toolbar .btn-action span,
        html body #purchase-editor-view .items-toolbar .btn-action kbd,
        html body #purchase-editor-view .items-toolbar #btn-share-invoice,
        html body #purchase-editor-view .items-toolbar #btn-share-invoice span,
        html body #purchase-editor-view #btn-toggle-auto-send-purchase,
        html body #purchase-editor-view #btn-toggle-auto-send-purchase span,
        html body #purchase-editor-view #btn-auto-send-options-purchase,
        html body #purchase-editor-view #btn-preview-purchase-journal,
        html body #purchase-editor-view #btn-preview-purchase-journal span,
        html body #purchase-editor-view #btn-speak-purchase-summary,
        html body #purchase-editor-view #btn-speak-purchase-summary span,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view #pos-grid thead th {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.95), 0 0 2px #000000 !important;
        }
        html body #purchase-editor-view .items-toolbar .btn-action svg,
        html body #purchase-editor-view .items-toolbar #btn-share-invoice svg,
        html body #purchase-editor-view button[onclick="openGridSettingsModal()"] svg,
        html body #purchase-editor-view #btn-quick-add-supplier svg {
            stroke: #ffffff !important;
            color: #ffffff !important;
            filter: drop-shadow(0 1.5px 2px rgba(0, 0, 0, 0.92)) !important;
        }
        /* AI Button Next to Grid Settings (الأسعار / AI) + Title Badge (مشتريات) + Print Button: Rich Dark Crystal 3D */
        html body #purchase-screen-title-badge,
        :root.light-theme body #purchase-screen-title-badge {
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important;
            border: 2px solid #38bdf8 !important;
            border-bottom: 3.5px solid #082f49 !important;
            box-shadow: 0 5px 14px rgba(2, 132, 199, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view button[onclick*="openPurchaseAIAuditModal"],
        :root.light-theme body #purchase-editor-view button[onclick*="openPurchaseAIAuditModal"] {
            background: linear-gradient(180deg, #7e22ce 0%, #6b21a8 52%, #4c1d95 100%) !important;
            border: 2px solid #c084fc !important;
            border-bottom: 3.5px solid #2e1065 !important;
            height: 38px !important;
            padding: 0 14px !important;
            border-radius: 10px !important;
            font-weight: 900 !important;
            font-size: 0.90rem !important;
            box-shadow: 0 6px 16px rgba(88, 28, 135, 0.42), 0 2px 5px rgba(0, 0, 0, 0.22), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view button[onclick*="openPurchaseAIAuditModal"]:hover {
            transform: translateY(-3.5px) scale(1.055) !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3.5px rgba(245, 158, 11, 0.42), 0 10px 24px rgba(88, 28, 135, 0.55), 0 0 20px rgba(192, 132, 252, 0.6), inset 0 1.5px 0 #ffffff !important;
            filter: brightness(1.08) !important;
        }
        html body #purchase-editor-view #btn-preview-invoice,
        :root.light-theme body #purchase-editor-view #btn-preview-invoice {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 52%, #1e3a8a 100%) !important;
            border: 2px solid #93c5fd !important;
            border-bottom: 3.5px solid #172554 !important;
            font-size: 0.96rem !important;
            box-shadow: 0 6px 16px rgba(30, 58, 138, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        /* Enlarged, Fixed-In-Place "أستاذ" Button (Never Leaves Its Center) + Larger Stable Zero (.is-zero-val) */
        html body #purchase-editor-view #hdr-customer-ledger-btn,
        html body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027,
        :root.light-theme body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027 {
            position: absolute !important;
            left: 5px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            height: 34px !important;
            min-height: 34px !important;
            max-height: 34px !important;
            padding: 0 15px !important;
            font-size: 0.94rem !important;
            font-weight: 900 !important;
            border-radius: 8px !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.95), 0 0 1px #000000 !important;
            animation: none !important;
        }
        html body #purchase-editor-view #hdr-customer-ledger-btn:hover,
        html body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027:hover {
            left: 5px !important;
            top: 50% !important;
            transform: translateY(-50%) scale(1.07) !important;
            animation: none !important;
            box-shadow: 0 0 0 2.5px rgba(251, 191, 36, 0.55), 0 0 18px rgba(245, 158, 11, 0.75), inset 0 1.5px 0 rgba(255, 255, 255, 0.8) !important;
        }
        body #pos-grid tbody tr td input.g-input.is-zero-val:not(:focus),
        body .tot-row input.tot-input.is-zero-val:not(:focus) {
            font-size: 1.28rem !important;
            font-weight: 900 !important;
            transform: none !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .group-supplier > .h-label {
            background: linear-gradient(135deg, #451a03 0%, #290f02 100%) !important;
            border: 1.5px solid #facc15 !important;
            color: #fde047 !important;
            -webkit-text-fill-color: #fde047 !important;
            font-size: 0.94rem !important;
            text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.95) !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-customer-search:not(.is-zero-val),
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-currency {
            color: #fde047 !important;
            -webkit-text-fill-color: #fde047 !important;
            text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.95) !important;
        }
        /* Financial Strip (الرصيد، المديونية، الشيكات، الحد، المتاح): Pure Crisp White Labels & High-Contrast Numbers */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card .fin-metric-pill .fin-lbl {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important;
            text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.98), 0 0 2px #000000 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card .fin-metric-pill .fin-val {
            font-weight: 900 !important;
            text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.98), 0 0 2px #000000 !important;
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card #fin-ledger { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card #fin-total-debt { color: #fecdd3 !important; -webkit-text-fill-color: #fecdd3 !important; }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card #fin-checks { color: #fef08a !important; -webkit-text-fill-color: #fef08a !important; }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card #fin-credit-limit { color: #bae6fd !important; -webkit-text-fill-color: #bae6fd !important; }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #customer-financial-card #fin-remaining { color: #a7f3d0 !important; -webkit-text-fill-color: #a7f3d0 !important; }
        /* High Contrast Between Icon/Button Color and Pure White Text for Extra-Cost Toggles (ترحيل، عمالة، إضافات، ضريبة، خصم) */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-row1-bar .group-warehouse > .h-label,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #purchase-editor-view #form-fields-card #hdr-warehouse {
            font-size: 0.95rem !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.95) !important;
        }
        html body #toggles-area .tot-toggle-btn,
        :root.light-theme body #toggles-area .tot-toggle-btn,
        :root.light-theme body #purchase-editor-view #toggles-area .tot-toggle-btn,
        :root.light-theme body #purchase-editor-view #toggles-area .tot-toggle-btn span {
            font-size: 1.0rem !important;
            height: 40px !important;
            padding: 0 16px !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.98), 0 0 2px #000000 !important;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.34), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view #btn-toggle-transport,
        :root.light-theme body #purchase-editor-view #btn-toggle-transport { background: linear-gradient(180deg, #ea580c 0%, #9a3412 100%) !important; border: 2px solid #fdba74 !important; border-bottom: 3.5px solid #431407 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html body #purchase-editor-view #btn-toggle-labor,
        :root.light-theme body #purchase-editor-view #btn-toggle-labor { background: linear-gradient(180deg, #0284c7 0%, #075985 100%) !important; border: 2px solid #7dd3fc !important; border-bottom: 3.5px solid #082f49 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html body #purchase-editor-view #btn-toggle-additions,
        :root.light-theme body #purchase-editor-view #btn-toggle-additions { background: linear-gradient(180deg, #ca8a04 0%, #854d0e 100%) !important; border: 2px solid #fef08a !important; border-bottom: 3.5px solid #422006 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html body #purchase-editor-view #btn-toggle-tax,
        :root.light-theme body #purchase-editor-view #btn-toggle-tax { background: linear-gradient(180deg, #e11d48 0%, #9f1239 100%) !important; border: 2px solid #fda4af !important; border-bottom: 3.5px solid #4c0519 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html body #purchase-editor-view #btn-toggle-discount,
        :root.light-theme body #purchase-editor-view #btn-toggle-discount { background: linear-gradient(180deg, #059669 0%, #065f46 100%) !important; border: 2px solid #6ee7b7 !important; border-bottom: 3.5px solid #022c22 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        :root.light-theme body #customer-financial-card #fin-strip-currency-btn,
        :root.light-theme body #customer-financial-card .fin-metric-pill span,
        :root.light-theme body #customer-financial-card #fin-wrap-debt .fin-lbl,
        :root.light-theme body #customer-financial-card #fin-total-debt,
        :root.light-theme body #customer-financial-card #fin-wrap-ledger .fin-lbl,
        :root.light-theme body #customer-financial-card #fin-ledger,
        :root.light-theme body #customer-financial-card #fin-wrap-checks .fin-lbl,
        :root.light-theme body #customer-financial-card #fin-checks,
        :root.light-theme body #customer-financial-card #fin-wrap-limit .fin-lbl,
        :root.light-theme body #customer-financial-card #fin-credit-limit,
        :root.light-theme body #customer-financial-card #fin-wrap-remaining .fin-lbl,
        :root.light-theme body #customer-financial-card #fin-remaining { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 900 !important; text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.95), 0 0 2px #000000 !important; }
        :root.light-theme body #customer-financial-card #fin-wrap-ledger { background: linear-gradient(180deg, #334155 0%, #0f172a 100%) !important; border: 1.5px solid #94a3b8 !important; }
        :root.light-theme body #customer-financial-card #fin-wrap-debt { background: linear-gradient(180deg, #e11d48 0%, #9f1239 100%) !important; border: 1.5px solid #fda4af !important; }
        :root.light-theme body #customer-financial-card #fin-wrap-checks { background: linear-gradient(180deg, #d97706 0%, #92400e 100%) !important; border: 1.5px solid #fcd34d !important; }
        :root.light-theme body #customer-financial-card #fin-wrap-limit { background: linear-gradient(180deg, #0284c7 0%, #075985 100%) !important; border: 1.5px solid #7dd3fc !important; }
        :root.light-theme body #customer-financial-card #fin-wrap-remaining { background: linear-gradient(180deg, #059669 0%, #065f46 100%) !important; border: 1.5px solid #6ee7b7 !important; }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #row-tot-transport span,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #row-tot-labor span,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #row-tot-additions span,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #row-tot-tax span,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #row-tot-discount span {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-size: 1.38rem !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.98), 0 0 2px #000000 !important;
        }
        /* Unified Dark-Crystal Bottom Toolbar & Journal/Summary Buttons in Both Themes */
        html body #purchase-editor-view .items-toolbar .btn-exit {
            background: linear-gradient(180deg, #f43f5e 0%, #be123c 55%, #881337 100%) !important;
            border: 2px solid #fda4af !important;
            border-bottom: 3.5px solid #4c0519 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 6px 18px rgba(159, 18, 57, 0.48), inset 0 1.5px 0 rgba(255, 255, 255, 0.65) !important;
        }
        html body #purchase-editor-view .items-toolbar .btn-exit:hover {
            background: linear-gradient(180deg, #fb7185 0%, #e11d48 50%, #9f1239 100%) !important;
            border-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(251, 113, 133, 0.55), 0 0 28px rgba(244, 63, 94, 0.85), 0 10px 24px rgba(159, 18, 57, 0.65), inset 0 2px 0 #ffffff !important;
        }
        html body #purchase-editor-view .items-toolbar #btn-share-invoice,
        :root.light-theme body #purchase-editor-view .items-toolbar #btn-share-invoice {
            background: linear-gradient(180deg, #10b981 0%, #059669 52%, #065f46 100%) !important;
            border: 2px solid #6ee7b7 !important;
            border-bottom: 3.5px solid #022c22 !important;
            box-shadow: 0 6px 16px rgba(6, 95, 70, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view .items-toolbar .btn-new,
        :root.light-theme body #purchase-editor-view .items-toolbar .btn-new {
            background: linear-gradient(180deg, #6366f1 0%, #4338ca 52%, #312e81 100%) !important;
            border: 2px solid #a5b4fc !important;
            border-bottom: 3.5px solid #1e1b4b !important;
            box-shadow: 0 6px 16px rgba(49, 46, 129, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view #btn-preview-purchase-journal,
        :root.light-theme body #purchase-editor-view #btn-preview-purchase-journal {
            background: linear-gradient(180deg, #0ea5e9 0%, #0284c7 52%, #075985 100%) !important;
            border: 2px solid #7dd3fc !important;
            border-bottom: 3.5px solid #082f49 !important;
            box-shadow: 0 6px 16px rgba(7, 89, 133, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        html body #purchase-editor-view #btn-speak-purchase-summary,
        :root.light-theme body #purchase-editor-view #btn-speak-purchase-summary {
            background: linear-gradient(180deg, #d97706 0%, #b45309 52%, #78350f 100%) !important;
            border: 2px solid #fcd34d !important;
            border-bottom: 3.5px solid #451a03 !important;
            box-shadow: 0 6px 16px rgba(120, 53, 15, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.55) !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase.inactive,
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase:not(.active),
        :root.light-theme body #purchase-editor-view #btn-auto-send-options-purchase.inactive,
        :root.light-theme body #purchase-editor-view #btn-auto-send-options-purchase:not(.active) {
            background: linear-gradient(180deg, #334155 0%, #1e293b 55%, #0f172a 100%) !important;
            border: 2px solid #94a3b8 !important;
            border-bottom: 3.5px solid #020617 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.42), inset 0 1.5px 0 rgba(255, 255, 255, 0.45) !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase:not(.active) span:not(#auto-send-purchase-status-pill) {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #btn-toggle-auto-send-purchase:not(.active) #auto-send-purchase-status-pill {
            background: rgba(245, 158, 11, 0.28) !important;
            border: 1.5px solid #fbbf24 !important;
            color: #fef08a !important;
            -webkit-text-fill-color: #fef08a !important;
        }
        /* 8. LIGHT THEME SCULPTED 3PX BORDERS, LUMINOUS REGIONS & GLASS SHIMMER */
        :root.light-theme .dash-main#pur-proportional-root > header.premium-header.magical-glass-header,
        :root.light-theme body #purchase-top-container #form-fields-card,
        :root.light-theme #form-fields-card.purchase-glass-card,
        :root.light-theme #purchase-editor-view .panel-header-row,
        :root.light-theme #purchase-editor-view .table-container,
        :root.light-theme #inline-totals-container,
        :root.light-theme #payment-panel,
        :root.light-theme #purchase-editor-view .items-toolbar {
            background-image: linear-gradient(115deg, transparent 36%, rgba(255, 255, 255, 0.85) 47%, rgba(255, 253, 245, 0.96) 50%, rgba(255, 255, 255, 0.85) 53%, transparent 64%), linear-gradient(145deg, var(--lt-card-1, #ffffff) 0%, var(--lt-card-2, #faf5eb) 100%) !important;
            background-size: 240% 100%, 100% 100% !important;
            background-repeat: no-repeat !important;
            border: 3px solid var(--lt-border, #5c2808) !important;
            border-radius: 15px !important;
            box-shadow: 0 12px 28px rgba(43, 17, 3, 0.14), 0 0 0 1px rgba(255, 255, 255, 0.92) inset, inset 0 2px 0 #ffffff !important;
            animation: ophthalmicGlassSweep 8.5s cubic-bezier(0.4, 0, 0.2, 1) infinite !important;
        }
        :root.light-theme #purchase-top-container #form-fields-card:hover,
        :root.light-theme #purchase-top-container #form-fields-card:focus-within,
        :root.light-theme #purchase-editor-view .panel-header-row:hover,
        :root.light-theme #purchase-editor-view .table-container:hover,
        :root.light-theme #purchase-editor-view .table-container:focus-within,
        :root.light-theme #inline-totals-container:hover,
        :root.light-theme #payment-panel:hover,
        :root.light-theme #purchase-editor-view .items-toolbar:hover {
            border: 3px solid var(--lt-border-hover, #b45309) !important;
            box-shadow: 0 0 0 3.5px var(--lt-glow, rgba(245, 158, 11, 0.42)), 0 0 26px var(--lt-glow, rgba(245, 158, 11, 0.45)), 0 16px 34px rgba(43, 17, 3, 0.20), inset 0 2px 0 #ffffff !important;
        }
        :root.light-theme body #purchase-top-container #invoice-info-box,
        :root.light-theme #customer-financial-card .fin-strip-inner,
        :root.light-theme #grid-foot .pur-compact-tot-card {
            background: linear-gradient(135deg, var(--lt-sub-1, #ffffff) 0%, var(--lt-sub-2, #fcf8f0) 100%) !important;
            border: 2.5px solid var(--lt-border, #5c2808) !important;
            box-shadow: 0 6px 16px rgba(43, 17, 3, 0.12), inset 0 1.5px 0 #ffffff !important;
        }
        /* Periodic Calm Glass Specular Shimmer (لمعات زجاجية هادئة ومريحة للعين من حين لآخر) */
        @keyframes ophthalmicGlassSweep {
            0%, 72% { background-position: 200% 0, 0 0; }
            92%, 100% { background-position: -120% 0, 0 0; }
        }
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #form-fields-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .items-toolbar {
            background-image: linear-gradient(115deg, transparent 38%, rgba(255, 255, 255, 0.06) 48%, rgba(56, 189, 248, 0.09) 50%, rgba(255, 255, 255, 0.06) 52%, transparent 62%), linear-gradient(145deg, #162645 0%, #101d36 100%) !important;
            background-size: 240% 100%, 100% 100% !important;
            background-repeat: no-repeat !important;
            animation: ophthalmicGlassSweep 9.5s cubic-bezier(0.4, 0, 0.2, 1) infinite !important;
        }
        /* 9. DEEP CLARITY & UNIFIED 1.08REM SCALE FOR (المتاح، مخزن، صنف، وحدة، كمية) & ALL 5 REGIONS IN LIGHT MODE */
        :root.light-theme body #purchase-editor-view #pos-grid thead tr th {
            background: linear-gradient(180deg, var(--lt-th-1, #faf4e6) 0%, var(--lt-th-2, #efe2ca) 100%) !important;
            color: var(--lt-ink, #120500) !important;
            -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            font-size: 1.08rem !important;
            font-weight: 900 !important;
            border-top: 2.5px solid var(--lt-border, #5c2808) !important;
            border-bottom: 2.5px solid var(--lt-border, #5c2808) !important;
            box-shadow: inset 0 1.5px 0 rgba(255, 255, 255, 0.92) !important;
        }
        :root.light-theme body #purchase-editor-view #pos-grid thead tr th span {
            background: transparent !important;
            color: var(--lt-ink, #120500) !important;
            -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            font-size: 1.08rem !important;
            font-weight: 900 !important;
        }
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td {
            background: var(--lt-row-odd, #ffffff) !important;
            border-top: 1.8px solid rgba(92, 40, 8, 0.36) !important;
            border-bottom: 1.8px solid rgba(92, 40, 8, 0.36) !important;
        }
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr:nth-child(even) td {
            background: var(--lt-row-even, #fbf7ef) !important;
        }
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td input.g-input,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td input.g-input.is-zero-val:not(:focus),
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td select.g-select,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td select.g-select.is-default-hint:not(:focus),
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .avail-val,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .avail-val.is-zero-val,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .total-val,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .total-val.is-zero-val,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td .row-num,
        :root.light-theme body #purchase-editor-view .tot-row input.tot-input.is-zero-val:not(:focus) {
            color: var(--lt-ink, #120500) !important;
            -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            font-size: 1.08rem !important;
            font-weight: 900 !important;
            border: 2.2px solid var(--lt-border, #5c2808) !important;
            opacity: 1 !important;
        }
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-description,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-customer-search,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-exchange-rate,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-warehouse,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-cost-center,
        :root.light-theme body #purchase-editor-view #payment-panel #hdr-payment-account {
            color: var(--lt-ink, #120500) !important;
            -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            font-size: 1.08rem !important;
            font-weight: 900 !important;
            border: 2.2px solid var(--lt-border, #5c2808) !important;
        }
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency {
            background-color: #0369a1 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(180deg, #0284c7 0%, #075985 100%) !important;
            border: 2px solid #7dd3fc !important;
            border-bottom: 3.5px solid #082f49 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 1.5px 3px rgba(0, 0, 0, 0.95), 0 0 2px #000000 !important;
            font-size: 1.08rem !important;
            font-weight: 900 !important;
        }
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency option { background: #0f172a !important; color: #ffffff !important; }
        :root.light-theme body #purchase-editor-view input::placeholder,
        :root.light-theme body #purchase-editor-view textarea::placeholder {
            color: var(--lt-placeholder, #3b1704) !important;
            -webkit-text-fill-color: var(--lt-placeholder, #3b1704) !important;
            font-weight: 900 !important;
            opacity: 0.9 !important;
        }
        /* 10. UNIFIED 1.08REM BOLDER SCALE ACROSS ALL 5 REGIONS + IN-PLACE GLASS BUTTON EDGE ILLUMINATION */
        :root.light-theme body #purchase-top-container #invoice-info-box #hdr-inv-label,
        :root.light-theme body #purchase-top-container #invoice-info-box #hdr-date-label,
        :root.light-theme body #purchase-row1-bar .h-label,
        :root.light-theme body #purchase-row2-bar .h-label,
        :root.light-theme body #purchase-editor-view .panel-header-row .panel-title,
        :root.light-theme body #purchase-editor-view .panel-header-row .panel-title span {
            color: var(--lt-ink, #120500) !important;
            -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            font-size: 1.08rem !important;
            font-weight: 900 !important;
            letter-spacing: 0.1px !important;
        }
        :root.light-theme body #purchase-row1-bar .h-label,
        :root.light-theme body #purchase-row2-bar .h-label,
        :root.light-theme body #invoice-info-box #hdr-inv-label,
        :root.light-theme body #invoice-info-box #hdr-date-label {
            background: linear-gradient(180deg, var(--lt-th-1, #faf4e6) 0%, var(--lt-th-2, #efe2ca) 100%) !important;
            border: 2px solid var(--lt-border, #5c2808) !important;
            height: 35px !important;
            line-height: 31px !important;
            padding: 0 11px !important;
        }
        :root.light-theme body #purchase-row1-bar .h-form-group,
        :root.light-theme body #purchase-row2-bar .h-form-group {
            background: linear-gradient(135deg, var(--lt-sub-1, #ffffff) 0%, var(--lt-sub-2, #fcf8f0) 100%) !important;
            border: 2.5px solid var(--lt-border, #5c2808) !important;
        }
        html body #purchase-screen-title-badge,
        html body #purchase-screen-title-badge span,
        html body #purchase-editor-view .panel-header-row button[onclick*="openPurchaseAIAuditModal"],
        html body #purchase-editor-view .panel-header-row button[onclick*="openPurchaseAIAuditModal"] span,
        html body #purchase-editor-view .panel-header-row button[onclick="openGridSettingsModal()"],
        html body #purchase-editor-view .panel-header-row button[onclick="openGridSettingsModal()"] span,
        html body #purchase-editor-view #toggles-area .tot-toggle-btn,
        html body #purchase-editor-view #toggles-area .tot-toggle-btn span,
        html body #purchase-editor-view .items-toolbar .btn-action,
        html body #purchase-editor-view .items-toolbar .btn-action span,
        html body #purchase-editor-view .items-toolbar #btn-share-invoice,
        html body #purchase-editor-view .items-toolbar #btn-share-invoice span,
        html body #purchase-editor-view #btn-preview-purchase-journal,
        html body #purchase-editor-view #btn-preview-purchase-journal span,
        html body #purchase-editor-view #btn-speak-purchase-summary,
        html body #purchase-editor-view #btn-speak-purchase-summary span,
        html body #purchase-editor-view #btn-toggle-auto-send-purchase,
        html body #purchase-editor-view #btn-toggle-auto-send-purchase span:not(#auto-send-purchase-status-pill),
        html body #purchase-editor-view #btn-submit-purchase-invoice,
        html body #purchase-editor-view #btn-submit-purchase-invoice span {
            font-size: 1.08rem !important;
            font-weight: 900 !important;
        }
        /* 11. WORLD-CLASS GLASS SPECULAR SHIMMER, EDGE ILLUMINATION & IN-PLACE MOTION FOR ALL BUTTONS */
        html body #purchase-editor-view button,
        html body #purchase-editor-view .btn-action,
        html body #purchase-editor-view .tot-toggle-btn,
        html body #purchase-editor-view .pay-toggle-btn,
        html body #purchase-screen-title-badge {
            position: relative !important;
            overflow: hidden !important;
            isolation: isolate !important;
            transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease, border-color 0.22s ease, filter 0.22s ease !important;
        }
        html body #purchase-editor-view #hdr-customer-ledger-btn,
        html body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027 {
            position: absolute !important;
        }
        html body #purchase-editor-view button::after,
        html body #purchase-editor-view .btn-action::after,
        html body #purchase-editor-view .tot-toggle-btn::after,
        html body #purchase-screen-title-badge::after {
            content: "" !important;
            position: absolute !important;
            top: -20% !important;
            left: -130% !important;
            width: 65% !important;
            height: 140% !important;
            background: linear-gradient(110deg, transparent 15%, rgba(255, 255, 255, 0.48) 50%, transparent 85%) !important;
            transform: skewX(-22deg) !important;
            pointer-events: none !important;
            animation: purBtnGlassShimmer 6.5s cubic-bezier(0.4, 0, 0.2, 1) infinite !important;
        }
        @keyframes purBtnGlassShimmer {
            0%, 68% { left: -130%; }
            90%, 100% { left: 165%; }
        }
        html body #purchase-editor-view button:not(#hdr-customer-ledger-btn):hover,
        html body #purchase-editor-view .btn-action:hover,
        html body #purchase-editor-view .tot-toggle-btn:hover,
        html body #purchase-editor-view .pay-toggle-btn:hover {
            transform: scale(1.038) !important;
            border-color: #fde047 !important;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.52), 0 0 22px rgba(250, 204, 21, 0.68), inset 0 2px 0 #ffffff, inset 0 0 12px rgba(255, 255, 255, 0.42) !important;
            filter: brightness(1.09) !important;
        }
        /* 12. PHOTOSHOP LUMINOSITY-PRESERVING LIGHT-MODE TONE PALETTES (UNIFIED ACROSS TOP PURCHASE HEADER & ALL SCREEN REGIONS) */
        :root.light-theme, :root.light-theme[data-light-tone="ivory_default"] {
            --lt-canvas-1: #efe5d0; --lt-canvas-2: #e5d6ba; --lt-hdr-1: #f5ebd6; --lt-hdr-2: #ecdec2; --lt-hdr-3: #e2cfa9;
            --lt-card-1: #ffffff; --lt-card-2: #fcf7ee; --lt-sub-1: #ffffff; --lt-sub-2: #fbf4e6; --lt-th-1: #faf3e3; --lt-th-2: #f1e4ca;
            --lt-row-odd: #ffffff; --lt-row-even: #fefcf7; --lt-border: #5c2808; --lt-border-hover: #b45309;
            --lt-glow: rgba(245, 158, 11, 0.45); --lt-ink: #120500; --lt-placeholder: #3b1704;
        }
        :root.light-theme[data-light-tone="royal_bronze"] {
            --lt-canvas-1: #e6cbb0; --lt-canvas-2: #d9b694; --lt-hdr-1: #eed7be; --lt-hdr-2: #e3c4a2; --lt-hdr-3: #d7b189;
            --lt-card-1: #fffbf7; --lt-card-2: #f9efe3; --lt-sub-1: #fffdfa; --lt-sub-2: #f6e7d5; --lt-th-1: #f5e5d1; --lt-th-2: #ebd2b7;
            --lt-row-odd: #ffffff; --lt-row-even: #fcf6ee; --lt-border: #4a2106; --lt-border-hover: #9a3412;
            --lt-glow: rgba(217, 119, 6, 0.48); --lt-ink: #140600; --lt-placeholder: #431407;
        }
        :root.light-theme[data-light-tone="platinum_gray"] {
            --lt-canvas-1: #d9e3ee; --lt-canvas-2: #cad7e6; --lt-hdr-1: #e2ebf5; --lt-hdr-2: #d4e1ef; --lt-hdr-3: #c5d6e8;
            --lt-card-1: #ffffff; --lt-card-2: #f2f7fc; --lt-sub-1: #ffffff; --lt-sub-2: #eef4fb; --lt-th-1: #ecf3fa; --lt-th-2: #dde8f5;
            --lt-row-odd: #ffffff; --lt-row-even: #f7fafe; --lt-border: #1e293b; --lt-border-hover: #0284c7;
            --lt-glow: rgba(56, 189, 248, 0.45); --lt-ink: #020617; --lt-placeholder: #1e293b;
        }
        :root.light-theme[data-light-tone="crystal_silver"] {
            --lt-canvas-1: #e4e9f0; --lt-canvas-2: #d6dde7; --lt-hdr-1: #ecf0f5; --lt-hdr-2: #e0e6ee; --lt-hdr-3: #d3dbe6;
            --lt-card-1: #ffffff; --lt-card-2: #f5f7fb; --lt-sub-1: #ffffff; --lt-sub-2: #f1f4f9; --lt-th-1: #eff3f8; --lt-th-2: #e3e8f0;
            --lt-row-odd: #ffffff; --lt-row-even: #f9fafc; --lt-border: #111827; --lt-border-hover: #2563eb;
            --lt-glow: rgba(59, 130, 246, 0.45); --lt-ink: #030712; --lt-placeholder: #1f2937;
        }
        :root.light-theme[data-light-tone="sage_pearl"] {
            --lt-canvas-1: #d4eee1; --lt-canvas-2: #c1e4d3; --lt-hdr-1: #ddf3e8; --lt-hdr-2: #cbead9; --lt-hdr-3: #b8e0cb;
            --lt-card-1: #ffffff; --lt-card-2: #eefaf4; --lt-sub-1: #ffffff; --lt-sub-2: #e8f8f0; --lt-th-1: #e5f6ed; --lt-th-2: #d3efe0;
            --lt-row-odd: #ffffff; --lt-row-even: #f6fdf9; --lt-border: #064e3b; --lt-border-hover: #059669;
            --lt-glow: rgba(16, 185, 129, 0.45); --lt-ink: #022c22; --lt-placeholder: #064e3b;
        }
        :root.light-theme[data-light-tone="sky_alabaster"] {
            --lt-canvas-1: #d3e9fb; --lt-canvas-2: #beddf7; --lt-hdr-1: #dceffd; --lt-hdr-2: #cae4fa; --lt-hdr-3: #b7d9f6;
            --lt-card-1: #ffffff; --lt-card-2: #eef7fe; --lt-sub-1: #ffffff; --lt-sub-2: #e7f4fd; --lt-th-1: #e4f2fd; --lt-th-2: #d1e9fb;
            --lt-row-odd: #ffffff; --lt-row-even: #f6fbff; --lt-border: #0c4a6e; --lt-border-hover: #0284c7;
            --lt-glow: rgba(14, 165, 233, 0.45); --lt-ink: #082f49; --lt-placeholder: #0c4a6e;
        }
        :root.light-theme[data-light-tone="warm_mocha_dark"] {
            --lt-canvas-1: #d2baa1; --lt-canvas-2: #c1a384; --lt-hdr-1: #decab4; --lt-hdr-2: #cfb69b; --lt-hdr-3: #bfa182;
            --lt-card-1: #fdfaf6; --lt-card-2: #f6ede2; --lt-sub-1: #fefcf9; --lt-sub-2: #f2e6d8; --lt-th-1: #f0e2d2; --lt-th-2: #e4d1bc;
            --lt-row-odd: #ffffff; --lt-row-even: #faf4ec; --lt-border: #3e1b04; --lt-border-hover: #7c2d12;
            --lt-glow: rgba(180, 83, 9, 0.48); --lt-ink: #0f0400; --lt-placeholder: #2e1102;
        }
        :root.light-theme[data-light-tone="deep_slate_dark"] {
            --lt-canvas-1: #c1cedf; --lt-canvas-2: #aebfd4; --lt-hdr-1: #ced9e8; --lt-hdr-2: #bdcce0; --lt-hdr-3: #acbed6;
            --lt-card-1: #f9fbfe; --lt-card-2: #eef3fa; --lt-sub-1: #fcfdff; --lt-sub-2: #e8eff8; --lt-th-1: #e4ecf6; --lt-th-2: #d5e1f0;
            --lt-row-odd: #ffffff; --lt-row-even: #f4f8fc; --lt-border: #0f172a; --lt-border-hover: #1d4ed8;
            --lt-glow: rgba(37, 99, 235, 0.45); --lt-ink: #020617; --lt-placeholder: #0f172a;
        }
        /* 13. GLOBAL LUXURY GLASS & KINETIC LIGHT GESTURES (BUTTON BACKLIGHT, CURVE HALO, SMOKED TOP GLASS & WAVE SHIMMERS) */
        @keyframes purGlassWaveSweep { 0%, 62% { background-position: 220% 0, 0 0; } 88%, 100% { background-position: -140% 0, 0 0; } }
        @keyframes purCornerAuraPulse { 0%, 100% { filter: drop-shadow(0 0 8px rgba(56,189,248,0.28)); } 50% { filter: drop-shadow(0 0 16px rgba(56,189,248,0.52)); } }
        @keyframes purKineticBurst { 0% { box-shadow: 0 0 0 0 rgba(56,189,248,0.75), 0 0 28px rgba(250,204,21,0.75); } 100% { box-shadow: 0 0 0 14px rgba(56,189,248,0), 0 0 0 rgba(250,204,21,0); } }
        /* A. Darker Smoked Crystal Upper Region with Specular Glass Reflection */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .dash-main#pur-proportional-root > header.premium-header.magical-glass-header,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-top-container #form-fields-card {
            background-image: radial-gradient(520px circle at var(--mx, 50%) var(--my, 20%), rgba(56,189,248,0.16), transparent 60%), linear-gradient(112deg, transparent 34%, rgba(255,255,255,0.11) 47%, rgba(125,211,252,0.22) 50%, rgba(255,255,255,0.09) 53%, transparent 66%), linear-gradient(160deg, rgba(6,13,28,0.95) 0%, rgba(11,22,44,0.93) 52%, rgba(5,10,22,0.97) 100%) !important;
            background-size: 100% 100%, 250% 100%, 100% 100% !important;
            backdrop-filter: blur(22px) saturate(195%) !important; -webkit-backdrop-filter: blur(22px) saturate(195%) !important;
            border: 2px solid rgba(56,189,248,0.86) !important; border-radius: 16px !important;
            box-shadow: 0 -4px 22px rgba(56,189,248,0.25), 0 14px 36px rgba(0,0,0,0.94), 0 0 24px rgba(14,165,233,0.32), inset 0 2px 1px rgba(255,255,255,0.38), inset 0 -2px 12px rgba(56,189,248,0.18) !important;
            animation: purGlassWaveSweep 7.5s cubic-bezier(0.4,0,0.2,1) infinite !important;
        }
        :root.light-theme .dash-main#pur-proportional-root > header.premium-header.magical-glass-header,
        :root.light-theme body #purchase-top-container #form-fields-card {
            background-image: radial-gradient(520px circle at var(--mx, 50%) var(--my, 20%), rgba(255,255,255,0.78), transparent 60%), linear-gradient(112deg, transparent 34%, rgba(255,255,255,0.92) 48%, rgba(255,255,255,0.75) 50%, rgba(255,255,255,0.9) 52%, transparent 66%), linear-gradient(160deg, var(--lt-hdr-1, #eadbc0) 0%, var(--lt-hdr-2, #dec9a4) 52%, var(--lt-hdr-3, #d1b689) 100%) !important;
            background-size: 100% 100%, 250% 100%, 100% 100% !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            border: 3px solid var(--lt-border, #5c2808) !important;
            box-shadow: 0 -3px 18px var(--lt-glow, rgba(180,83,9,0.24)), 0 14px 30px rgba(15,23,42,0.18), inset 0 2.5px 1px rgba(255,255,255,0.95), inset 0 -2px 10px var(--lt-glow, rgba(146,64,14,0.16)) !important;
            animation: purGlassWaveSweep 7.5s cubic-bezier(0.4,0,0.2,1) infinite !important;
        }
        /* B. Backlight Behind Buttons, Luminous Button Rim & Curve Backlight Halo */
        html body #purchase-editor-view button:not(.pro-serial-btn),
        html body #purchase-editor-view .btn-action,
        html body #purchase-editor-view .tot-toggle-btn,
        html body #purchase-editor-view .pay-toggle-btn,
        html body #purchase-screen-title-badge,
        html body #fin-strip-currency-btn {
            box-shadow: 0 0 16px rgba(56,189,248,0.42), 0 6px 18px rgba(2,132,199,0.38), 0 0 0 1px rgba(255,255,255,0.28), inset 0 1.5px 1px rgba(255,255,255,0.72), inset 0 -2px 6px rgba(0,0,0,0.35) !important;
        }
        html body #purchase-editor-view button:not(#hdr-customer-ledger-btn):hover,
        html body #purchase-editor-view .btn-action:hover,
        html body #purchase-editor-view .tot-toggle-btn:hover,
        html body #fin-strip-currency-btn:hover {
            box-shadow: 0 0 0 2.5px rgba(250,204,21,0.68), 0 0 26px rgba(56,189,248,0.85), 0 8px 24px rgba(250,204,21,0.52), inset 0 2px 2px #ffffff, inset 0 0 14px rgba(255,255,255,0.48) !important;
        }
        /* Curve & Edge Backlight on Main Cards & Capsules */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .panel-header-row,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .table-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-editor-view .items-toolbar {
            box-shadow: 0 -2px 18px rgba(56,189,248,0.22), 0 12px 32px rgba(0,0,0,0.92), 0 0 20px rgba(14,165,233,0.25), inset 0 1.5px 1px rgba(255,255,255,0.35), inset 0 0 16px rgba(56,189,248,0.12) !important;
        }
        /* C. Staggered Glass Light Waves in Key Zones (الأسامي، المتاح، المخزن، الصنف، الكمية، الوحدة، السعر، الجملة، الدولار، والمنطقة الغامقة للعمليات) */
        html body .group-currency > .h-label { display: none !important; }
        html body #fin-wrap-remaining, html body #customer-financial-card .fin-metric-pill, html body #hdr-customer-search,
        html body #pos-grid th[data-col-id="available"], html body #pos-grid td[data-col-id="available"], html body #pos-grid td[data-col-id="available"] .avail-val,
        html body #purchase-row1-bar .group-warehouse, html body #pos-grid th[data-col-id="warehouse"], html body #pos-grid td[data-col-id="warehouse"],
        html body #pos-grid th[data-col-id="item"], html body #pos-grid td[data-col-id="item"], html body #pos-grid td[data-col-id="item"] input,
        html body #pos-grid th[data-col-id="quantity"], html body #pos-grid td[data-col-id="quantity"], html body #pos-grid td[data-col-id="quantity"] input,
        html body #pos-grid th[data-col-id="unit"], html body #pos-grid td[data-col-id="unit"],
        html body #pos-grid th[data-col-id="price"], html body #pos-grid td[data-col-id="price"], html body #pos-grid td[data-col-id="price"] input,
        html body #pos-grid th[data-col-id="total"], html body #pos-grid td[data-col-id="total"],
        html body #purchase-row1-bar .group-currency, html body #hdr-exchange-rate, html body #t-grand-dynamic-fc, html body #t-grand-dynamic-loc,
        html:not(.light-theme) #purchase-editor-view .table-container, html:not(.light-theme) #inline-totals-container, html:not(.light-theme) #payment-panel {
            background-image: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.28) 46%, rgba(125,211,252,0.42) 50%, rgba(253,224,71,0.26) 53%, transparent 70%) !important;
            background-size: 250% 100% !important; background-repeat: no-repeat !important;
            box-shadow: inset 0 1.5px 1px rgba(255,255,255,0.42), inset 0 -1px 6px rgba(56,189,248,0.16) !important;
            animation: purGlassWaveSweep 7.2s cubic-bezier(0.4,0,0.2,1) infinite !important;
        }
        html body #fin-wrap-remaining, html body #pos-grid [data-col-id="available"], html body #pos-grid [data-col-id="available"] .avail-val { animation-delay: 0.3s !important; }
        html body #pos-grid [data-col-id="item"], html body #pos-grid [data-col-id="item"] input, html body #hdr-customer-search { animation-delay: 1.2s !important; }
        html body #purchase-row1-bar .group-warehouse, html body #pos-grid [data-col-id="warehouse"] { animation-delay: 2.1s !important; }
        html body #pos-grid [data-col-id="quantity"], html body #pos-grid [data-col-id="quantity"] input { animation-delay: 3.0s !important; }
        html body #pos-grid [data-col-id="unit"] { animation-delay: 3.9s !important; }
        html body #pos-grid [data-col-id="price"], html body #pos-grid [data-col-id="price"] input { animation-delay: 4.8s !important; }
        html body #pos-grid [data-col-id="total"], html body #purchase-row1-bar .group-currency, html body #hdr-exchange-rate { animation-delay: 5.7s !important; }
        /* D. Intense Crystal Glass Shimmer on Extra-Cost & Action Icons (ترحيل، عمالة، إضافات، ضريبة، خصم، التوزيع) */
        html body #toggles-area .tot-toggle-btn, html body #btn-landed-cost-mode {
            box-shadow: 0 6px 18px rgba(0,0,0,0.52), 0 0 16px rgba(56,189,248,0.38), inset 0 2.5px 2px rgba(255,255,255,0.88), inset 0 14px 12px rgba(255,255,255,0.24), inset 0 -3px 8px rgba(0,0,0,0.42) !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.95), 0 0 10px rgba(255,255,255,0.45) !important;
        }
        html body #toggles-area .tot-toggle-btn::after, html body #btn-landed-cost-mode::after {
            background: linear-gradient(110deg, transparent 10%, rgba(255,255,255,0.78) 48%, rgba(254,240,138,0.65) 52%, transparent 88%) !important;
            animation-duration: 4.2s !important;
        }
        /* E. Mizani Royal Emerald-Gold Jewel Currency Toggle Buttons (#hdr-currency & #fin-strip-currency-btn: جنيه ⇄ دولار) */
        html body #purchase-editor-view #form-fields-card #hdr-currency,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency {
            background-color: #065f46 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23fde047' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.42) 48%, rgba(253,224,71,0.45) 51%, transparent 70%), linear-gradient(180deg, #10b981 0%, #059669 48%, #064e3b 100%) !important;
            background-repeat: no-repeat, no-repeat, no-repeat !important;
            background-position: left 7px center, 0 0, 0 0 !important;
            background-size: 13px 13px, 250% 100%, 100% 100% !important;
            border: 2px solid #fde047 !important; border-bottom: 3.5px solid #b45309 !important; border-radius: 11px !important;
            color: #fef08a !important; -webkit-text-fill-color: #fef08a !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.98), 0 0 8px rgba(250,204,21,0.55) !important;
            box-shadow: 0 0 16px rgba(16,185,129,0.52), 0 4px 14px rgba(245,158,11,0.38), inset 0 2px 2px rgba(255,255,255,0.82), inset 0 -2px 6px rgba(0,0,0,0.45) !important;
            animation: purGlassWaveSweep 5.5s cubic-bezier(0.4,0,0.2,1) infinite !important;
        }
        html body #fin-strip-currency-btn, :root.light-theme body #customer-financial-card #fin-strip-currency-btn {
            background: linear-gradient(115deg, transparent 25%, rgba(255,255,255,0.48) 48%, rgba(253,224,71,0.52) 52%, transparent 72%), linear-gradient(180deg, #10b981 0%, #059669 50%, #064e3b 100%) !important;
            background-size: 250% 100%, 100% 100% !important;
            border: 2px solid #fde047 !important; border-bottom: 3.5px solid #b45309 !important;
            color: #fef08a !important; -webkit-text-fill-color: #fef08a !important;
            box-shadow: 0 0 15px rgba(16,185,129,0.55), 0 3px 12px rgba(245,158,11,0.42), inset 0 2px 2px rgba(255,255,255,0.85) !important;
            animation: purGlassWaveSweep 5.2s cubic-bezier(0.4,0,0.2,1) infinite !important;
        }
        html body #fin-strip-currency-btn[data-cur-mode="USD"], :root.light-theme body #customer-financial-card #fin-strip-currency-btn[data-cur-mode="USD"] {
            background: linear-gradient(115deg, transparent 25%, rgba(255,255,255,0.52) 48%, rgba(125,211,252,0.52) 52%, transparent 72%), linear-gradient(180deg, #f59e0b 0%, #d97706 50%, #78350f 100%) !important;
            background-size: 250% 100%, 100% 100% !important;
            border: 2px solid #7dd3fc !important; border-bottom: 3.5px solid #0369a1 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 0 16px rgba(245,158,11,0.62), 0 3px 12px rgba(56,189,248,0.45), inset 0 2px 2px rgba(255,255,255,0.9) !important;
        }
        /* F. Luminous Interactive Edge Illumination + Subtle Motion When Hovering/Focusing Any Zone or Cell */
        html body #purchase-editor-view .h-form-group:hover, html body #purchase-editor-view .h-form-group:focus-within,
        html body #customer-financial-card .fin-metric-pill:hover, html body #pos-grid tbody tr:hover,
        html body #pos-grid td:hover, html body #pos-grid td:focus-within, html body #pos-grid th:hover,
        html body #purchase-editor-view .table-container:hover, html body #inline-totals-container:hover, html body #payment-panel:hover {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56,189,248,0.62), 0 0 20px rgba(56,189,248,0.48), 0 0 32px rgba(250,204,21,0.28), inset 0 0 12px rgba(56,189,248,0.26), inset 0 1.5px 1px rgba(255,255,255,0.75) !important;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease !important;
        }
        /* G. DAY-MODE FULL TOP-HEADER TONE SYNCHRONIZATION (تغيير منطقة المشتريات العليا بالكامل مع لون الشاشة النهاري) */
        :root.light-theme body #purchase-top-container #invoice-info-box,
        :root.light-theme body #customer-financial-card .fin-strip-inner,
        :root.light-theme body #purchase-row1-bar .h-form-group,
        :root.light-theme body #purchase-row2-bar .h-form-group {
            background-image: linear-gradient(115deg, transparent 30%, rgba(255,255,255,0.72) 48%, rgba(255,255,255,0.92) 50%, transparent 68%), linear-gradient(135deg, var(--lt-sub-1, #fffefb) 0%, var(--lt-sub-2, #f6ecda) 55%, var(--lt-th-1, #f5ead4) 100%) !important;
            background-size: 250% 100%, 100% 100% !important;
            border: 2.2px solid var(--lt-border, #5c2808) !important;
            box-shadow: 0 5px 14px rgba(15,23,42,0.10), 0 3px 10px var(--lt-glow, rgba(245,158,11,0.22)), inset 0 2px 1px rgba(255,255,255,0.95) !important;
        }
        :root.light-theme body #invoice-info-box #hdr-inv-label,
        :root.light-theme body #invoice-info-box #hdr-date-label,
        :root.light-theme body #purchase-row1-bar .h-label,
        :root.light-theme body #purchase-row2-bar .h-label {
            background: linear-gradient(180deg, var(--lt-th-1, #f5ead4) 0%, var(--lt-th-2, #e6d4b5) 100%) !important;
            border: 2px solid var(--lt-border, #5c2808) !important;
            color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            box-shadow: inset 0 1.5px 1px rgba(255,255,255,0.9), 0 2px 6px rgba(15,23,42,0.10) !important;
        }
        :root.light-theme body #purchase-top-container #form-fields-card .pro-serial-container,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-date,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-customer-search,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-description {
            background: linear-gradient(180deg, var(--lt-row-odd, #ffffff) 0%, var(--lt-card-1, #fffdf8) 55%, var(--lt-sub-2, #f6ecda) 100%) !important;
            border: 2px solid var(--lt-border, #5c2808) !important;
            color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            -webkit-box-shadow: inset 0 2px 3px rgba(15,23,42,0.05), 0 2px 6px var(--lt-glow, rgba(245,158,11,0.18)), inset 0 1.5px 0 rgba(255,255,255,0.92) !important;
            box-shadow: inset 0 2px 3px rgba(15,23,42,0.05), 0 2px 6px var(--lt-glow, rgba(245,158,11,0.18)), inset 0 1.5px 0 rgba(255,255,255,0.92) !important;
        }
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-warehouse,
        :root.light-theme body #purchase-editor-view #form-fields-card #hdr-cost-center,
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td select.g-select {
            background-color: var(--lt-sub-2, #f6ecda) !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%231e293b' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(115deg, transparent 32%, rgba(255,255,255,0.75) 50%, transparent 68%), linear-gradient(180deg, var(--lt-sub-1, #ffffff) 0%, var(--lt-sub-2, #f6ecda) 58%, var(--lt-th-2, #e6d4b5) 100%) !important;
            background-repeat: no-repeat, no-repeat, no-repeat !important;
            background-position: left 9px center, 0 0, 0 0 !important;
            background-size: 13px 13px, 250% 100%, 100% 100% !important;
            border: 2px solid var(--lt-border, #5c2808) !important; border-bottom: 3px solid var(--lt-border-hover, #b45309) !important;
            color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important;
        }
        /* H. COMPACT CURRENCY & EXCHANGE RATE + EXPANDED WAREHOUSE SIZING */
        html body #purchase-row1-bar .group-currency { flex: 1.38 1 164px !important; min-width: 162px !important; max-width: 202px !important; gap: 5px !important; }
        html body #purchase-row1-bar #hdr-currency { flex: 0.92 1 84px !important; min-width: 84px !important; max-width: 100px !important; padding: 0 18px 0 6px !important; font-size: 0.94rem !important; }
        html body #purchase-row1-bar #hdr-exchange-rate { flex: 0.85 1 72px !important; min-width: 72px !important; max-width: 90px !important; padding: 0 4px !important; font-size: 1.08rem !important; }
        html body #purchase-row1-bar .group-warehouse { flex: 2.95 1 265px !important; min-width: 255px !important; }
        html body #fin-strip-currency-btn { min-width: 66px !important; padding: 0 8px !important; font-size: 0.80rem !important; height: 27px !important; }
        /* I. JEWELER-GRADE BUTTON RIM LIGHTING, UNDER-GLOW PEDESTAL & DIAMOND GLASS SHIMMER (إنارة الحواف + إنارة من تحت الأزرار + لمعات زجاجية) */
        html body #purchase-editor-view button:not(.pro-serial-btn),
        html body #purchase-editor-view .btn-action,
        html body #purchase-editor-view .tot-toggle-btn,
        html body #purchase-editor-view .pay-toggle-btn,
        html body #purchase-screen-title-badge,
        html body #fin-strip-currency-btn {
            box-shadow: 0 7px 16px -2px rgba(56,189,248,0.65), 0 11px 24px -4px rgba(250,204,21,0.45), 0 0 14px rgba(56,189,248,0.45), 0 0 0 1.5px rgba(255,255,255,0.38), inset 0 2.5px 1.5px rgba(255,255,255,0.88), inset 0 12px 10px rgba(255,255,255,0.18), inset 0 -2.5px 6px rgba(56,189,248,0.32) !important;
        }
        html body #purchase-editor-view button:not(.pro-serial-btn)::before,
        html body #purchase-editor-view .btn-action::before,
        html body #purchase-editor-view .tot-toggle-btn::before,
        html body #purchase-editor-view .pay-toggle-btn::before,
        html body #purchase-screen-title-badge::before,
        html body #fin-strip-currency-btn::before {
            content: "" !important; position: absolute !important; left: 10% !important; right: 10% !important; bottom: 0px !important; height: 3.5px !important;
            border-radius: 999px !important; pointer-events: none !important; z-index: 2 !important;
            background: radial-gradient(ellipse at center, #ffffff 0%, #fde047 38%, #38bdf8 72%, transparent 100%) !important;
            box-shadow: 0 0 10px #fde047, 0 2px 12px #38bdf8 !important; opacity: 0.92 !important;
        }
        html body #purchase-editor-view button:not(#hdr-customer-ledger-btn):hover,
        html body #purchase-editor-view .btn-action:hover,
        html body #purchase-editor-view .tot-toggle-btn:hover,
        html body #purchase-editor-view .pay-toggle-btn:hover,
        html body #fin-strip-currency-btn:hover {
            border-color: #fde047 !important;
            box-shadow: 0 10px 22px -2px rgba(250,204,21,0.85), 0 14px 30px -3px rgba(56,189,248,0.78), 0 0 0 2.5px rgba(253,224,71,0.82), 0 0 24px rgba(56,189,248,0.85), inset 0 2.5px 2px #ffffff, inset 0 14px 14px rgba(255,255,255,0.28), inset 0 -3px 8px rgba(250,204,21,0.45) !important;
        }
        /* J. AMBIENT GLASS CAUSTICS & LUMINOUS SHIMMERS IN GENERAL NON-BUTTON REGIONS (لمعات زجاجية وضوئية في المناطق العامة للشاشة) */
        html body #purchase-top-container #form-fields-card,
        html body #purchase-editor-view .panel-header-row,
        html body #purchase-editor-view .table-container,
        html body #inline-totals-container,
        html body #payment-panel,
        html body #purchase-editor-view .items-toolbar,
        html body #grid-foot .pur-compact-tot-card {
            position: relative !important;
            box-shadow: 0 10px 26px -4px rgba(14,165,233,0.32), 0 -2px 16px rgba(56,189,248,0.22), inset 0 2.5px 1.5px rgba(255,255,255,0.78), inset 0 -2px 10px rgba(56,189,248,0.20) !important;
        }
        html:not(.light-theme) body #pos-grid thead tr th {
            background-image: linear-gradient(115deg, transparent 26%, rgba(255,255,255,0.28) 47%, rgba(253,224,71,0.22) 50%, rgba(125,211,252,0.28) 53%, transparent 72%), linear-gradient(180deg, #263c6e 0%, #1d2f57 100%) !important;
            background-size: 250% 100%, 100% 100% !important;
            box-shadow: 0 5px 14px -2px rgba(56,189,248,0.38), inset 0 2px 1px rgba(255,255,255,0.45), inset 0 -2px 6px rgba(56,189,248,0.24) !important;
            animation: purGlassWaveSweep 6.8s cubic-bezier(0.4,0,0.2,1) infinite !important;
        }
        :root.light-theme body #purchase-editor-view #pos-grid thead tr th {
            background-image: linear-gradient(115deg, transparent 26%, rgba(255,255,255,0.78) 47%, rgba(254,240,138,0.48) 50%, rgba(255,255,255,0.75) 53%, transparent 72%), linear-gradient(180deg, var(--lt-th-1, #f5ead4) 0%, var(--lt-th-2, #e6d4b5) 100%) !important;
            background-size: 250% 100%, 100% 100% !important;
            box-shadow: 0 5px 12px -2px var(--lt-glow, rgba(180,83,9,0.28)), inset 0 2px 1px rgba(255,255,255,0.92), inset 0 -2px 6px var(--lt-glow, rgba(180,83,9,0.18)) !important;
            animation: purGlassWaveSweep 6.8s cubic-bezier(0.4,0,0.2,1) infinite !important;
        }
        .pur-kinetic-pulse { animation: purKineticBurst 0.55s cubic-bezier(0.16,1,0.3,1) !important; }
        /* K. CENTERED IN-PLACE LEDGER BUTTON (أستاذ), REFINED CURRENCY BUTTONS (جنيه/دولار), COMPACT "الرقم:" & EXPANDED FINANCIAL STRIP + TOP AESTHETICS */
        html body #purchase-row1-bar .h-form-group, html body #purchase-row2-bar .h-form-group { height: 52px !important; min-height: 52px !important; max-height: 52px !important; padding: 4px 8px !important; align-items: center !important; }
        html body #purchase-row1-bar .h-input-wrapper, html body #purchase-row2-bar .h-input-wrapper, html body #purchase-row1-bar .custom-dropdown, html body #purchase-row1-bar .custom-dropdown-input-wrap { height: 43px !important; min-height: 43px !important; max-height: 43px !important; align-items: center !important; }
        html body #purchase-row1-bar .ctrl-input, html body #purchase-row2-bar .ctrl-input, html body #purchase-editor-view #btn-quick-add-supplier, html body #purchase-editor-view #btn-clone-last-supplier-inv { height: 43px !important; min-height: 43px !important; max-height: 43px !important; border-radius: 9px !important; padding-top: 0 !important; padding-bottom: 0 !important; box-sizing: border-box !important; }
        html body #purchase-editor-view #form-fields-card #hdr-warehouse, html body #purchase-editor-view #form-fields-card #hdr-cost-center, html body #purchase-editor-view #pos-grid tbody tr td select.g-select { text-align: center !important; text-align-last: center !important; line-height: 39px !important; padding-top: 0 !important; padding-bottom: 0 !important; padding-right: 10px !important; padding-left: 26px !important; }
        html body #purchase-editor-view #form-fields-card #hdr-warehouse option, html body #purchase-editor-view #form-fields-card #hdr-cost-center option, html body #purchase-editor-view #pos-grid tbody tr td select.g-select option { text-align: center !important; font-weight: 900 !important; }
        html body #purchase-editor-view #form-fields-card #hdr-customer-search, html body #purchase-editor-view #form-fields-card #hdr-customer-search:hover, html body #purchase-editor-view #form-fields-card #hdr-customer-search:focus, html body #purchase-editor-view #form-fields-card .group-supplier:hover #hdr-customer-search { transform: none !important; padding-left: 84px !important; line-height: 39px !important; }
        html body #purchase-editor-view #hdr-customer-ledger-btn, html body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027, :root.light-theme body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027 {
            position: absolute !important; left: 5px !important; top: 0 !important; bottom: 0 !important; margin: auto 0 !important; transform: none !important;
            height: 31px !important; min-height: 31px !important; max-height: 31px !important; padding: 0 13px !important; border-radius: 7px !important;
            display: inline-flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important;
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important; border: 1.5px solid #7dd3fc !important; border-bottom: 2.5px solid #082f49 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-size: 0.88rem !important; font-weight: 900 !important;
            box-shadow: 0 0 14px rgba(56,189,248,0.55), 0 4px 10px rgba(2,132,199,0.45), inset 0 1.5px 1px rgba(255,255,255,0.85) !important; animation: none !important;
        }
        html body #purchase-editor-view #hdr-customer-ledger-btn:hover, html body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027:hover, :root.light-theme body #purchase-editor-view #hdr-customer-ledger-btn.ledger-btn-2027:hover {
            left: 5px !important; top: 0 !important; bottom: 0 !important; margin: auto 0 !important; transform: scale(1.055) !important;
            border-color: #fde047 !important; box-shadow: 0 0 0 2.5px rgba(250,204,21,0.75), 0 0 20px rgba(56,189,248,0.9), inset 0 2px 2px #ffffff !important; filter: brightness(1.08) !important;
        }
        html body #purchase-editor-view #btn-quick-add-supplier:hover, html body #purchase-editor-view #btn-clone-last-supplier-inv:hover { transform: scale(1.045) !important; }
        html body #purchase-row1-bar .group-currency { flex: 1.52 1 182px !important; min-width: 178px !important; max-width: 218px !important; gap: 6px !important; }
        html body #purchase-editor-view #form-fields-card #hdr-currency, :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency {
            flex: 1.05 1 96px !important; min-width: 94px !important; max-width: 112px !important; height: 43px !important; min-height: 43px !important; max-height: 43px !important;
            padding: 0 20px 0 8px !important; border-radius: 9px !important; font-size: 0.95rem !important; font-weight: 900 !important; line-height: 39px !important; text-align: center !important; text-align-last: center !important;
            background-color: #0369a1 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%23fde047' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"), linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.38) 48%, rgba(125,211,252,0.42) 51%, transparent 70%), linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important;
            background-repeat: no-repeat, no-repeat, no-repeat !important; background-position: left 7px center, 0 0, 0 0 !important; background-size: 12px 12px, 250% 100%, 100% 100% !important;
            border: 1.8px solid #7dd3fc !important; border-bottom: 3px solid #082f49 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 5px 14px rgba(2,132,199,0.45), 0 0 14px rgba(56,189,248,0.45), inset 0 2px 1.5px rgba(255,255,255,0.82) !important; transform: none !important;
        }
        html body #purchase-editor-view #form-fields-card #hdr-currency:hover, :root.light-theme body #purchase-editor-view #form-fields-card #hdr-currency:hover {
            transform: scale(1.035) !important; border-color: #fde047 !important; box-shadow: 0 0 0 2.5px rgba(250,204,21,0.72), 0 0 22px rgba(56,189,248,0.85), inset 0 2px 2px #ffffff !important;
        }
        html body #purchase-row1-bar #hdr-exchange-rate { flex: 0.88 1 76px !important; min-width: 74px !important; max-width: 92px !important; height: 43px !important; line-height: 39px !important; text-align: center !important; border-radius: 9px !important; font-size: 1.08rem !important; transform: none !important; }
        html body #fin-strip-currency-btn, :root.light-theme body #customer-financial-card #fin-strip-currency-btn {
            height: 31px !important; min-height: 31px !important; max-height: 31px !important; min-width: 80px !important; padding: 0 12px !important; border-radius: 8px !important;
            font-size: 0.85rem !important; font-weight: 900 !important; transform: none !important;
            background: linear-gradient(115deg, transparent 25%, rgba(255,255,255,0.42) 48%, rgba(125,211,252,0.45) 52%, transparent 72%), linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important;
            background-size: 250% 100%, 100% 100% !important; border: 1.8px solid #7dd3fc !important; border-bottom: 3px solid #082f49 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 4px 12px rgba(2,132,199,0.48), 0 0 14px rgba(56,189,248,0.5), inset 0 2px 1.5px rgba(255,255,255,0.85) !important;
        }
        html body #fin-strip-currency-btn[data-cur-mode="USD"], :root.light-theme body #customer-financial-card #fin-strip-currency-btn[data-cur-mode="USD"] {
            background: linear-gradient(115deg, transparent 25%, rgba(255,255,255,0.45) 48%, rgba(253,224,71,0.48) 52%, transparent 72%), linear-gradient(180deg, #10b981 0%, #059669 52%, #047857 100%) !important;
            background-size: 250% 100%, 100% 100% !important; border: 1.8px solid #6ee7b7 !important; border-bottom: 3px solid #022c22 !important;
            box-shadow: 0 4px 12px rgba(5,150,105,0.48), 0 0 14px rgba(16,185,129,0.52), inset 0 2px 1.5px rgba(255,255,255,0.85) !important;
        }
        html body #fin-strip-currency-btn:hover, :root.light-theme body #customer-financial-card #fin-strip-currency-btn:hover { transform: scale(1.045) !important; border-color: #fde047 !important; }
        html body #purchase-row0-bar { gap: 8px !important; min-height: 44px !important; }
        html body #invoice-info-box { padding: 3px 8px !important; gap: 6px !important; min-height: 44px !important; max-height: 44px !important; border-radius: 11px !important; }
        html body #invoice-info-box .group-invoice { min-width: 122px !important; gap: 4px !important; }
        html body #invoice-info-box #hdr-inv-label, html body #invoice-info-box #hdr-date-label { padding: 0 7px !important; font-size: 0.82rem !important; height: 31px !important; line-height: 29px !important; }
        html body #customer-financial-card .fin-strip-inner { height: 44px !important; min-height: 44px !important; max-height: 44px !important; padding: 0 8px !important; gap: 6px !important; border-radius: 11px !important; }
        html body #customer-financial-card .fin-metric-pill { flex: 1 1 auto !important; justify-content: center !important; height: 31px !important; padding: 0 10px !important; border-radius: 8px !important; gap: 5px !important; box-shadow: inset 0 1.5px 1px rgba(255,255,255,0.35), 0 2px 8px rgba(0,0,0,0.25) !important; }
        html body #customer-financial-card .fin-metric-pill .fin-lbl { font-size: 0.80rem !important; }
        html body #customer-financial-card .fin-metric-pill .fin-val { font-size: 0.94rem !important; }
        html body header.magical-glass-header .msg-quartet-btn, html body header.magical-glass-header #global-exchange-rate-box, html body header.magical-glass-header .enterprise-time-pill {
            border-radius: 10px !important; box-shadow: 0 4px 14px rgba(2,132,199,0.35), 0 0 12px rgba(56,189,248,0.32), inset 0 1.5px 1px rgba(255,255,255,0.65) !important; transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        }
        html body header.magical-glass-header .msg-quartet-btn:hover, html body header.magical-glass-header #global-exchange-rate-box:hover {
            transform: scale(1.04) !important; box-shadow: 0 0 0 2px rgba(250,204,21,0.7), 0 0 18px rgba(56,189,248,0.75), inset 0 2px 2px #ffffff !important;
        }
        :root.light-theme body #purchase-editor-view #pay-credit-section, :root.light-theme body #purchase-editor-view #pay-cash-section, :root.light-theme body #purchase-editor-view #pay-bank-section, :root.light-theme body #purchase-editor-view #pay-cheque-section {
            background-image: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.75) 48%, rgba(255,255,255,0.92) 50%, transparent 68%), linear-gradient(135deg, var(--lt-sub-1, #fffefb) 0%, var(--lt-sub-2, #f6ecda) 55%, var(--lt-th-1, #f5ead4) 100%) !important;
            background-size: 250% 100%, 100% 100% !important; animation: purGlassWaveSweep 7s cubic-bezier(0.4,0,0.2,1) infinite !important;
            border: 2.2px solid var(--lt-border, #5c2808) !important; border-bottom: 3.5px solid var(--lt-border-hover, #b45309) !important;
            box-shadow: 0 6px 16px rgba(15,23,42,0.12), 0 0 14px var(--lt-glow, rgba(245,158,11,0.28)), inset 0 2px 1px rgba(255,255,255,0.95) !important;
        }
        :root.light-theme body #purchase-editor-view #pay-credit-section span, :root.light-theme body #purchase-editor-view #pay-credit-section label, :root.light-theme body #purchase-editor-view #payment-panel .panel-header-row span {
            color: #090300 !important; -webkit-text-fill-color: #090300 !important; font-weight: 900 !important;
        }
        html body #purchase-editor-view #pay-credit-section .pay-credit-title-pill, html body #purchase-editor-view #pay-credit-section .pay-due-lbl-txt {
            background: linear-gradient(180deg, #fde68a 0%, #f59e0b 55%, #d97706 100%) !important; border: 2px solid #78350f !important; border-bottom: 3px solid #451a03 !important;
            color: #090300 !important; -webkit-text-fill-color: #090300 !important; font-weight: 900 !important; box-shadow: 0 3px 10px rgba(0,0,0,0.28), inset 0 1.5px 1px #ffffff !important;
        }
        html body #purchase-editor-view #pay-credit-section .pay-credit-title-pill .pay-credit-lbl-txt { color: #090300 !important; -webkit-text-fill-color: #090300 !important; font-weight: 900 !important; font-size: 1.08rem !important; }
        :root.light-theme body #purchase-editor-view #pay-credit-section input, :root.light-theme body #purchase-editor-view #pay-cash-section input, :root.light-theme body #purchase-editor-view #pay-bank-section input, :root.light-theme body #purchase-editor-view #pay-cheque-section input {
            background: linear-gradient(180deg, var(--lt-row-odd, #ffffff) 0%, var(--lt-card-1, #fffdf8) 60%, var(--lt-sub-2, #f6ecda) 100%) !important;
            border: 2.2px solid var(--lt-border, #5c2808) !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important;
            box-shadow: inset 0 2px 3px rgba(15,23,42,0.06), 0 2px 6px var(--lt-glow, rgba(245,158,11,0.22)), inset 0 1.5px 0 #ffffff !important;
        }
        html body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"],
        :root.light-theme body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"],
        :root.light-theme body #pay-credit-section .btn-action {
            background: linear-gradient(180deg, #fffbeb 0%, #fde68a 52%, #f59e0b 100%) !important;
            border: 2px solid #92400e !important; border-bottom: 3.5px solid #451a03 !important;
            color: #090300 !important; -webkit-text-fill-color: #090300 !important;
            font-weight: 900 !important; font-size: 1.02rem !important; animation: none !important;
            box-shadow: 0 4px 10px rgba(120,53,15,0.28), inset 0 2px 1px #ffffff !important;
            transition: transform 0.2s cubic-bezier(0.22,1,0.36,1), box-shadow 0.2s ease !important;
        }
        html body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"]::before,
        html body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"]::after,
        html body #purchase-editor-view #payment-panel .pay-toggle-btn::before { display: none !important; content: none !important; }
        html body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"] span,
        :root.light-theme body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"] span {
            color: #090300 !important; -webkit-text-fill-color: #090300 !important; font-weight: 900 !important;
        }
        html body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"]:hover,
        :root.light-theme body #purchase-editor-view #pay-credit-section button[onclick*="previewCurrentPromissoryNote"]:hover {
            transform: translateY(-2px) scale(1.02) !important; border-color: #451a03 !important;
            box-shadow: 0 6px 16px rgba(180,83,9,0.42), inset 0 2px 1.5px #ffffff !important;
            color: #000000 !important; -webkit-text-fill-color: #000000 !important;
        }
        html body #purchase-editor-view #payment-panel .pay-toggle-btn,
        :root.light-theme body #purchase-editor-view #payment-panel .pay-toggle-btn {
            height: 36px !important; padding: 0 15px !important; border-radius: 999px !important;
            font-size: 0.92rem !important; font-weight: 900 !important; color: #050200 !important; -webkit-text-fill-color: #050200 !important;
            text-shadow: 0 1px 0 rgba(255,255,255,0.65) !important; letter-spacing: 0.1px !important;
            transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.22s ease, filter 0.2s ease !important;
        }
        html body #purchase-editor-view #payment-panel .pay-toggle-btn span,
        :root.light-theme body #purchase-editor-view #payment-panel .pay-toggle-btn span { color: #050200 !important; -webkit-text-fill-color: #050200 !important; font-weight: 900 !important; }
        html body #purchase-editor-view #payment-panel #toggle-credit, :root.light-theme body #purchase-editor-view #payment-panel #toggle-credit { background: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.7) 49%, transparent 70%), linear-gradient(180deg, #fef9c3 0%, #fde047 50%, #f59e0b 100%) !important; background-size: 250% 100%, 100% 100% !important; border: 2px solid #92400e !important; border-bottom: 3.5px solid #451a03 !important; box-shadow: 0 5px 14px rgba(245,158,11,0.45), 0 0 14px rgba(253,224,71,0.48), inset 0 2px 1.5px #ffffff !important; animation: purGlassWaveSweep 4.5s infinite !important; }
        html body #purchase-editor-view #payment-panel #toggle-cash, :root.light-theme body #purchase-editor-view #payment-panel #toggle-cash { background: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.7) 49%, transparent 70%), linear-gradient(180deg, #d1fae5 0%, #6ee7b7 50%, #10b981 100%) !important; background-size: 250% 100%, 100% 100% !important; border: 2px solid #065f46 !important; border-bottom: 3.5px solid #022c22 !important; box-shadow: 0 5px 14px rgba(16,185,129,0.45), 0 0 14px rgba(110,231,183,0.48), inset 0 2px 1.5px #ffffff !important; animation: purGlassWaveSweep 4.8s infinite !important; }
        html body #purchase-editor-view #payment-panel #toggle-bank, :root.light-theme body #purchase-editor-view #payment-panel #toggle-bank { background: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.75) 49%, transparent 70%), linear-gradient(180deg, #e0f2fe 0%, #7dd3fc 50%, #0ea5e9 100%) !important; background-size: 250% 100%, 100% 100% !important; border: 2px solid #075985 !important; border-bottom: 3.5px solid #082f49 !important; box-shadow: 0 5px 14px rgba(14,165,233,0.45), 0 0 14px rgba(125,211,252,0.5), inset 0 2px 1.5px #ffffff !important; animation: purGlassWaveSweep 5.1s infinite !important; }
        html body #purchase-editor-view #payment-panel #toggle-cheque, :root.light-theme body #purchase-editor-view #payment-panel #toggle-cheque { background: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.75) 49%, transparent 70%), linear-gradient(180deg, #f3e8ff 0%, #d8b4fe 50%, #a855f7 100%) !important; background-size: 250% 100%, 100% 100% !important; border: 2px solid #581c87 !important; border-bottom: 3.5px solid #2e1065 !important; box-shadow: 0 5px 14px rgba(168,85,247,0.45), 0 0 14px rgba(216,180,254,0.5), inset 0 2px 1.5px #ffffff !important; animation: purGlassWaveSweep 5.4s infinite !important; }
        html body #purchase-editor-view #payment-panel #btn-purchase-financial-ocr, :root.light-theme body #purchase-editor-view #payment-panel #btn-purchase-financial-ocr { background: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.75) 49%, transparent 70%), linear-gradient(180deg, #ccfbf1 0%, #5eead4 50%, #14b8a6 100%) !important; background-size: 250% 100%, 100% 100% !important; border: 2px solid #115e59 !important; border-bottom: 3.5px solid #042f2e !important; box-shadow: 0 5px 14px rgba(20,184,166,0.48), 0 0 14px rgba(94,234,212,0.52), inset 0 2px 1.5px #ffffff !important; animation: purGlassWaveSweep 4.6s infinite !important; }
        html body #purchase-editor-view #payment-panel button[onclick*="FinancialOCR.openTrainer"], :root.light-theme body #purchase-editor-view #payment-panel button[onclick*="FinancialOCR.openTrainer"] { background: linear-gradient(115deg, transparent 28%, rgba(255,255,255,0.75) 49%, transparent 70%), linear-gradient(180deg, #ffe4e6 0%, #fda4af 50%, #fb7185 100%) !important; background-size: 250% 100%, 100% 100% !important; border: 2px solid #9f1239 !important; border-bottom: 3.5px solid #4c0519 !important; box-shadow: 0 5px 14px rgba(244,63,94,0.42), 0 0 14px rgba(253,164,175,0.5), inset 0 2px 1.5px #ffffff !important; animation: purGlassWaveSweep 5.7s infinite !important; }
        html body #purchase-editor-view #payment-panel .pay-toggle-btn:hover, html body #purchase-editor-view #payment-panel .pay-toggle-btn.active, :root.light-theme body #purchase-editor-view #payment-panel .pay-toggle-btn:hover, :root.light-theme body #purchase-editor-view #payment-panel .pay-toggle-btn.active { transform: translateY(-2.5px) scale(1.055) !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; filter: brightness(1.08) saturate(1.12) !important; box-shadow: 0 0 0 2.5px rgba(255,255,255,0.92), 0 8px 20px rgba(0,0,0,0.35), 0 0 20px rgba(253,224,71,0.65), inset 0 2.5px 2px #ffffff !important; }
        /* UNIFIED NUMBER FONT SIZE (+2 DEGREES LARGER) & AUTO-SHRINK SUPPORT FOR 15+ DIGITS */
        html body #purchase-editor-view #inv-number,
        html body #purchase-editor-view #hdr-date,
        html body #purchase-editor-view #pos-grid tbody tr td .avail-val,
        html body #purchase-editor-view #pos-grid tbody tr td .avail-val.is-zero-val,
        html body #purchase-editor-view #pay-credit-due-date,
        html body #customer-financial-card .fin-metric-pill #fin-remaining {
            font-size: 1.58rem !important; font-weight: 900 !important; font-family: 'Outfit', 'Tajawal', sans-serif !important; line-height: 1.1 !important; text-align: center !important;
        }
        html body #purchase-editor-view #pos-grid thead tr th[data-col-id="quantity"],
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="quantity"] { min-width: 142px !important; width: 142px !important; }
        html body #purchase-editor-view #pos-grid thead tr th[data-col-id="price"],
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"],
        html body #purchase-editor-view #pos-grid thead tr th[data-col-id="total"],
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="total"] { min-width: 172px !important; width: 172px !important; }
        html body #purchase-editor-view #pay-credit-amount {
            flex: 1.45 1 245px !important; min-width: 230px !important; max-width: 360px !important; width: auto !important; height: 48px !important;
            font-weight: 900 !important; font-family: 'Outfit', 'Tajawal', sans-serif !important; text-align: center !important; padding: 0 10px !important;
        }
        html body #purchase-editor-view #hdr-date { min-width: 172px !important; padding: 0 8px !important; text-align: center !important; }
        html body #purchase-editor-view #hdr-date::-webkit-datetime-edit, html body #purchase-editor-view #pay-credit-due-date::-webkit-datetime-edit { font-size: 1.38rem !important; font-weight: 900 !important; font-family: 'Outfit', 'Tajawal', sans-serif !important; }
        html body #purchase-editor-view #pay-credit-due-date { flex: 1 1 190px !important; min-width: 185px !important; width: auto !important; height: 48px !important; padding: 0 8px !important; text-align: center !important; }
        /* Lock Quantity, Price, Available, Unit, Warehouse & Total on the EXACT same vertical level with comfortable room */
        html body #purchase-editor-view #pos-grid tbody tr td { vertical-align: middle !important; padding: 7px 5px 22px 5px !important; }
        html body #purchase-editor-view #pos-grid tbody tr td input.g-input,
        html body #purchase-editor-view #pos-grid tbody tr td select.g-select,
        html body #purchase-editor-view #pos-grid tbody tr td .avail-val,
        html body #purchase-editor-view #pos-grid tbody tr td .total-val {
            height: 45px !important; min-height: 45px !important; max-height: 45px !important; line-height: 41px !important;
            padding: 0 5px !important; margin: 0 auto !important; box-sizing: border-box !important;
            vertical-align: middle !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;
        }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] input.g-input,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"]:hover input.g-input,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] input.g-input:hover,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] input.g-input:focus { padding-left: 34px !important; padding-right: 5px !important; transform: none !important; }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="item"] input.g-input { display: block !important; text-align: right !important; padding-right: 10px !important; }
        /* Larger Side-Mounted 3D Lamp (+40% = 20px, Zero Hover Jitter) + Clean Under-Number Reference/Variance Text with Up/Down Arrows */
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill:hover,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill:active {
            position: static !important; width: 0 !important; height: 0 !important; padding: 0 !important; margin: 0 !important;
            background: transparent !important; border: none !important; box-shadow: none !important; pointer-events: none !important;
            transform: none !important; transition: none !important;
        }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-lamp,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-lamp:hover,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-lamp:active {
            position: absolute !important; left: 9px !important; top: 19px !important; transform: none !important;
            width: 20px !important; height: 20px !important; min-width: 20px !important; min-height: 20px !important;
            border-radius: 50% !important; display: inline-block !important; cursor: pointer !important; pointer-events: auto !important; z-index: 30 !important;
            border: 2.2px solid rgba(255,255,255,0.95) !important; transition: filter 0.15s ease, box-shadow 0.15s ease !important;
        }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-lamp:hover { filter: brightness(1.18) !important; }
        .pur-price-lamp.lamp-red { background: radial-gradient(circle at 35% 35%, #fecdd3, #ff1e42 52%, #881337) !important; box-shadow: 0 0 14px #ff1e42, 0 0 22px rgba(244,63,94,0.95), inset 0 2px 3px #ffffff !important; }
        .pur-price-lamp.lamp-amber { background: radial-gradient(circle at 35% 35%, #fef08a, #f59e0b 52%, #92400e) !important; box-shadow: 0 0 13px #fbbf24, 0 0 20px rgba(245,158,11,0.92), inset 0 2px 3px #ffffff !important; }
        .pur-price-lamp.lamp-green { background: radial-gradient(circle at 35% 35%, #a7f3d0, #10b981 52%, #064e3b) !important; box-shadow: 0 0 13px #10b981, 0 0 20px rgba(16,185,129,0.92), inset 0 2px 3px #ffffff !important; }
        .pur-price-lamp.lamp-blue { background: radial-gradient(circle at 35% 35%, #bae6fd, #0ea5e9 52%, #0c4a6e) !important; box-shadow: 0 0 13px #38bdf8, 0 0 20px rgba(14,165,233,0.92), inset 0 2px 3px #ffffff !important; }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-subtxt,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-subtxt:hover,
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-subtxt:active {
            position: absolute !important; bottom: 1px !important; left: 50% !important; transform: translateX(-50%) !important;
            height: 20px !important; min-height: 20px !important; max-height: 20px !important; width: auto !important; padding: 0 4px !important; border-radius: 0 !important;
            display: inline-flex !important; align-items: center !important; justify-content: center !important;
            font-size: 0.94rem !important; font-weight: 900 !important; line-height: 1 !important; white-space: nowrap !important; direction: ltr !important;
            cursor: pointer !important; pointer-events: auto !important; z-index: 26 !important; transition: none !important;
            background: transparent !important; border: none !important; box-shadow: none !important; color: #fef08a !important; -webkit-text-fill-color: #fef08a !important;
        }
        :root.light-theme body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-subtxt {
            background: transparent !important; border: none !important; box-shadow: none !important; color: #0f172a !important; -webkit-text-fill-color: #0f172a !important;
        }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill[data-lamp-state="red"] .pur-price-subtxt { color: #e11d48 !important; -webkit-text-fill-color: #e11d48 !important; }
        html:not(.light-theme) body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill[data-lamp-state="red"] .pur-price-subtxt { color: #ff4d6d !important; -webkit-text-fill-color: #ff4d6d !important; background: transparent !important; }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill[data-lamp-state="amber"] .pur-price-subtxt { color: #b45309 !important; -webkit-text-fill-color: #b45309 !important; }
        html:not(.light-theme) body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill[data-lamp-state="amber"] .pur-price-subtxt { color: #fbbf24 !important; -webkit-text-fill-color: #fbbf24 !important; background: transparent !important; }
        html body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill[data-lamp-state="green"] .pur-price-subtxt { color: #047857 !important; -webkit-text-fill-color: #047857 !important; }
        html:not(.light-theme) body #purchase-editor-view #pos-grid tbody tr td[data-col-id="price"] .pur-price-audit-pill[data-lamp-state="green"] .pur-price-subtxt { color: #34d399 !important; -webkit-text-fill-color: #34d399 !important; background: transparent !important; }
        html body #print-invoice-frame .promissory-note-container,
        :root.light-theme body #print-invoice-frame .promissory-note-container {
            background: #ffffff !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2.5px double #0f172a !important;
        }
        html body #print-invoice-frame .promissory-note-container h2,
        html body #print-invoice-frame .promissory-note-container h4,
        html body #print-invoice-frame .promissory-note-container p,
        html body #print-invoice-frame .promissory-note-container div,
        html body #print-invoice-frame .promissory-note-container span,
        html body #print-invoice-frame .promissory-note-container strong {
            color: #000000 !important; -webkit-text-fill-color: #000000 !important; font-weight: 800 !important; text-shadow: none !important;
        }
        html body #inline-totals-container { display: none !important; }
        html body #grid-foot .pur-compact-tot-card #toggles-area {
            display: inline-flex !important; flex-wrap: nowrap !important; gap: 8px !important; align-items: center !important; max-width: none !important; margin-left: 10px !important;
        }
        html body #purchase-editor-view #payment-panel .payment-row-item {
            display: grid !important;
            grid-template-columns: 85px 22% 18% 18% minmax(0, 1fr) 30px !important;
            gap: 10px !important;
            align-items: center !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        html body #purchase-editor-view #payment-panel .payment-row-item input,
        html body #purchase-editor-view #payment-panel .payment-row-item select {
            width: 100% !important; min-width: 0 !important; max-width: 100% !important; box-sizing: border-box !important; margin: 0 !important;
        }
        :root.light-theme body #purchase-editor-view #pay-cash-section .payment-row-item > div:first-child,
        :root.light-theme body #purchase-editor-view #pay-bank-section .payment-row-item > div:first-child,
        :root.light-theme body #purchase-editor-view #pay-cheque-section .payment-row-item > div:first-child,
        :root.light-theme body #purchase-editor-view #payment-panel .pay-method-row-lbl,
        :root.light-theme body #purchase-editor-view #payment-panel .pay-method-row-lbl span {
            color: #090300 !important; -webkit-text-fill-color: #090300 !important; font-weight: 900 !important; font-size: 1.05rem !important; text-shadow: none !important;
        }
        html body #purchase-editor-view #payment-panel #pay-cash-amount:not(:focus),
        html body #purchase-editor-view #payment-panel #pay-cash-ref:not(:focus),
        html body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-amount-input:not(:focus),
        html body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-ref-input:not(:focus),
        html body #purchase-editor-view #payment-panel #cheques-list input.ch-amount-input:not(:focus),
        html body #purchase-editor-view #payment-panel #cheques-list input.ch-no-input:not(:focus),
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-amount:not(:focus),
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-ref:not(:focus),
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-amount-input:not(:focus),
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-ref-input:not(:focus),
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list input.ch-amount-input:not(:focus),
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list input.ch-no-input:not(:focus) {
            font-size: 2.35rem !important; font-weight: 900 !important;
            width: 100% !important; min-width: 0 !important; margin-right: 0 !important; height: 60px !important;
            box-sizing: border-box !important;
        }
        html body #purchase-editor-view #payment-panel #pay-cash-amount:focus,
        html body #purchase-editor-view #payment-panel #pay-cash-ref:focus,
        html body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-amount-input:focus,
        html body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-ref-input:focus,
        html body #purchase-editor-view #payment-panel #cheques-list input.ch-amount-input:focus,
        html body #purchase-editor-view #payment-panel #cheques-list input.ch-no-input:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-amount:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #pay-cash-ref:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-amount-input:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-ref-input:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list input.ch-amount-input:focus,
        :root.light-theme body #purchase-editor-view #payment-panel #cheques-list input.ch-no-input:focus {
            font-size: 2.80rem !important; font-weight: 900 !important; letter-spacing: 0.5px !important;
            width: 100% !important; min-width: 0 !important; margin-right: 0 !important; height: 64px !important;
            box-sizing: border-box !important;
        }
        html body #purchase-editor-view #payment-panel #pay-cash-amount::placeholder,
        html body #purchase-editor-view #payment-panel #pay-cash-ref::placeholder,
        html body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-amount-input::placeholder,
        html body #purchase-editor-view #payment-panel #bank-transfers-list input.bt-ref-input::placeholder,
        html body #purchase-editor-view #payment-panel #cheques-list input.ch-amount-input::placeholder,
        html body #purchase-editor-view #payment-panel #cheques-list input.ch-no-input::placeholder {
            font-size: 1.15rem !important; font-weight: 700 !important; letter-spacing: 0 !important;
        }
        </style>
        <script id="pur-fin-strip-and-tfoot-organizer">
        (function() {
            window._finStripCurrency = localStorage.getItem('pur_fin_strip_cur') || 'SDG';
            window.getFinStripCurrency = function() {
                return window._finStripCurrency || 'SDG';
            };
            window.convFinStripVal = function(sdgVal) {
                const n = parseFloat(sdgVal) || 0;
                if (window.getFinStripCurrency() === 'USD') {
                    const exRate = parseFloat(document.getElementById('hdr-exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
                    return exRate > 0 ? parseFloat((n / exRate).toFixed(2)) : n;
                }
                return n;
            };
            window.syncFinStripCurrencyBtnUI = function(cur) {
                const btn = document.getElementById('fin-strip-currency-btn');
                if (!btn) return;
                const c = cur || window.getFinStripCurrency();
                btn.setAttribute('data-cur-mode', c);
                if (c === 'USD') {
                    btn.textContent = 'دولار ⇄';
                } else {
                    btn.textContent = 'جنيه ⇄';
                }
            };
            window.toggleFinStripCurrency = function() {
                window._finStripCurrency = (window.getFinStripCurrency() === 'SDG') ? 'USD' : 'SDG';
                try { localStorage.setItem('pur_fin_strip_cur', window._finStripCurrency); } catch (_e) {}
                window.syncFinStripCurrencyBtnUI(window._finStripCurrency);
                if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
            };

            // 🛡️ Silent 3-Way Match Monitor: Badge stays hidden; alerts via toast ONLY on actual discrepancy
            let _last3WayMismatchKey = '';
            window.updatePurchaseThreeWayMatchBadge = function() {
                const badge = document.getElementById('purchase-3way-match-badge');
                if (badge) badge.style.setProperty('display', 'none', 'important');

                const invNo = (document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || '').trim();
                const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0) : [];
                const invTotalQty = activeRows.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);

                let linkedGrns = [];
                ['erp_grn_v1', 'erp_purchase_grn_v1', 'erp_split_deliveries_v1', 'erp_warehouse_receipts_v1'].forEach(k => {
                    try {
                        const arr = JSON.parse(localStorage.getItem(k) || '[]');
                        if (Array.isArray(arr) && invNo && invNo !== '---') {
                            arr.forEach(g => {
                                if (g && !g.deleted && g.status !== 'cancelled' && (
                                    String(g.invoiceNo) === invNo ||
                                    String(g.refInvoice) === invNo ||
                                    String(g.parentInvoiceNo) === invNo ||
                                    String(g.purchaseInvoiceNo) === invNo
                                )) {
                                    linkedGrns.push(g);
                                }
                            });
                        }
                    } catch (_e) {}
                });

                if (linkedGrns.length > 0 && invTotalQty > 0) {
                    let grnTotalQty = 0;
                    linkedGrns.forEach(g => {
                        if (Array.isArray(g.items)) {
                            g.items.forEach(it => { grnTotalQty += (parseFloat(it.receivedQty ?? it.qty ?? it.quantity ?? 0) || 0); });
                        } else {
                            grnTotalQty += (parseFloat(g.totalQty ?? g.qty ?? 0) || 0);
                        }
                    });
                    const diff = Math.abs(invTotalQty - grnTotalQty);
                    if (grnTotalQty > 0 && diff >= 0.001) {
                        const key = `${invNo}:${invTotalQty}:${grnTotalQty}`;
                        if (_last3WayMismatchKey !== key && typeof showToast === 'function') {
                            _last3WayMismatchKey = key;
                            showToast(`⚠️ تنبيه خطأ في المطابقة الثلاثية: كمية الفاتورة (${invTotalQty}) لا تطابق المستلم مخزنياً (${grnTotalQty})`, 'warning', 4500);
                        }
                    } else {
                        _last3WayMismatchKey = '';
                    }
                }
            };

            // 🎯 Align Primary Grand Total Directly Underneath the Invoice "الجملة / الإجمالي" (data-col-id="total") Column
            window.alignGrandTotalUnderTotalColumn = function() {
                try {
                    const table = document.getElementById('pos-grid');
                    const card = document.querySelector('#grid-foot .pur-compact-tot-card');
                    const dynLoc = document.getElementById('t-grand-dynamic-loc');
                    const dynFc = document.getElementById('t-grand-dynamic-fc');
                    if (!table || !card || !dynLoc || !dynFc) return;

                    const curEl = document.getElementById('hdr-currency');
                    const isSDG = (!curEl || curEl.value === 'SDG');
                    const primaryBox = isSDG ? dynLoc : dynFc;

                    const totalCol = table.querySelector('tbody tr td[data-col-id="total"]') || table.querySelector('thead tr th[data-col-id="total"]');
                    if (!totalCol) return;

                    const cardRect = card.getBoundingClientRect();
                    const totalRect = totalCol.getBoundingClientRect();
                    if (!cardRect.width || !totalRect.width) return;

                    const boxW = primaryBox.offsetWidth || 175;
                    const totalCenter = totalRect.left + (totalRect.width / 2);
                    const targetBoxLeft = totalCenter - (boxW / 2);
                    const padLeft = Math.max(10, Math.round(targetBoxLeft - cardRect.left));
                    card.style.setProperty('padding-left', padLeft + 'px', 'important');
                } catch (_e) {}
            };

            window.formatGrandTotalsDualSdgUsd = function() {
                const curEl = document.getElementById('hdr-currency');
                const isSDG = (!curEl || curEl.value === 'SDG');
                const exRate = parseFloat(document.getElementById('hdr-exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 1;
                if (isSDG) document.body.classList.add('cur-sdg');
                else document.body.classList.remove('cur-sdg');

                const dynLoc = document.getElementById('t-grand-dynamic-loc');
                const dynFc = document.getElementById('t-grand-dynamic-fc');
                const tLocal = document.getElementById('t-grand-local');
                const tFc = document.getElementById('t-grand-fc');
                if (!dynLoc || !dynFc) return;

                const parseRaw = (txt) => parseFloat(String(txt || '0').replace(/[^0-9.-]/g, '')) || 0;
                const rawLocal = parseRaw(tLocal ? tLocal.textContent : '0');
                const rawFc = parseRaw(tFc ? tFc.textContent : '0');

                const sdgVal = isSDG ? rawLocal : (rawFc > 0 ? rawFc : (rawLocal * exRate));
                const usdVal = isSDG ? (rawFc > 0 ? rawFc : (exRate > 0 ? rawLocal / exRate : 0)) : rawLocal;

                const fmtSDG = Math.round(sdgVal).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                const fmtUSD = usdVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                dynLoc.classList.remove('hide-if-sdg');
                dynLoc.setAttribute('data-cur-unit', 'ج.س');
                if (dynLoc.textContent !== fmtSDG) {
                    dynLoc.textContent = fmtSDG;
                }
                const lenSdg = fmtSDG.length;
                const sdgPx = lenSdg > 15 ? 20 : (lenSdg > 11 ? 24 : 27);
                dynLoc.style.setProperty('font-size', sdgPx + 'px', 'important');

                dynFc.classList.remove('hide-if-sdg');
                dynFc.setAttribute('data-cur-unit', '$');
                if (dynFc.textContent !== fmtUSD) {
                    dynFc.textContent = fmtUSD;
                }
                const lenUsd = fmtUSD.length;
                const usdPx = lenUsd > 13 ? 19 : (lenUsd > 10 ? 22 : (isSDG ? 24 : 27));
                dynFc.style.setProperty('font-size', usdPx + 'px', 'important');

                // Order in RTL:
                // - When invoice is SDG: first USD (dynFc), then at the end directly under "الجملة" column -> SDG (dynLoc)
                // - When invoice is USD (or non-SDG): reversed -> first SDG (dynLoc), then at the end directly under "الجملة" column -> USD (dynFc)
                const valsWrap = dynLoc.parentElement;
                if (valsWrap && valsWrap === dynFc.parentElement) {
                    if (isSDG) {
                        if (valsWrap.firstElementChild !== dynFc) {
                            valsWrap.appendChild(dynFc);
                            valsWrap.appendChild(dynLoc);
                        }
                    } else {
                        if (valsWrap.firstElementChild !== dynLoc) {
                            valsWrap.appendChild(dynLoc);
                            valsWrap.appendChild(dynFc);
                        }
                    }
                }
                window.alignGrandTotalUnderTotalColumn();
            };
            window.syncTotalsVisibility = window.formatGrandTotalsDualSdgUsd;

            window.normalizePurchaseTfootLayout = function() {
                const tfoot = document.getElementById('grid-foot');
                if (!tfoot) return;
                tfoot.querySelectorAll('tr > td.hide-if-sdg').forEach(td => td.remove());
                tfoot.querySelectorAll('tr > td[colspan="13"]').forEach(td => td.setAttribute('colspan', '9'));
                const grandRow = document.getElementById('row-grand-total');
                if (grandRow && !grandRow.dataset.compactNormalized) {
                    const lbl = document.getElementById('lbl-grand-dynamic');
                    const loc = document.getElementById('t-grand-dynamic-loc');
                    const fc = document.getElementById('t-grand-dynamic-fc');
                    const toggles = document.getElementById('toggles-area');
                    if (lbl && loc && fc) {
                        loc.classList.remove('hide-if-sdg');
                        fc.classList.remove('hide-if-sdg');
                        loc.setAttribute('data-cur-unit', 'ج.س');
                        fc.setAttribute('data-cur-unit', '$');
                        grandRow.innerHTML = '';
                        const tdFull = document.createElement('td');
                        tdFull.colSpan = 9;
                        tdFull.style.cssText = 'border: none !important; padding: 10px 0 4px 0 !important; text-align: left !important; vertical-align: middle !important;';
                        const card = document.createElement('div');
                        card.className = 'pur-compact-tot-card';
                        if (toggles) card.appendChild(toggles);
                        card.appendChild(lbl);
                        const valsWrap = document.createElement('div');
                        valsWrap.style.cssText = 'display: inline-flex; direction: rtl; flex-wrap: nowrap; gap: 14px; align-items: center; justify-content: flex-end;';
                        valsWrap.appendChild(fc);
                        valsWrap.appendChild(loc);
                        card.appendChild(valsWrap);
                        tdFull.appendChild(card);
                        grandRow.appendChild(tdFull);
                        grandRow.dataset.compactNormalized = '1';
                    }
                }
                tfoot.querySelectorAll('tr.tot-row').forEach(tr => {
                    if (tr.dataset.compactNormalized) return;
                    const tds = Array.from(tr.children);
                    if (tds.length >= 4) {
                        tds[0].colSpan = 4;
                        tds[1].colSpan = 2;
                        tds[2].colSpan = 2;
                        tds[2].style.minWidth = '170px';
                        tds[3].colSpan = 1;
                        tr.dataset.compactNormalized = '1';
                    }
                });
                window.formatGrandTotalsDualSdgUsd();
            };

            document.addEventListener('DOMContentLoaded', function() {
                window.syncFinStripCurrencyBtnUI();
                window.normalizePurchaseTfootLayout();
                if (typeof window.onCustomerChange === 'function') {
                    setTimeout(() => window.onCustomerChange(), 80);
                }
                const exInput = document.getElementById('hdr-exchange-rate');
                if (exInput) {
                    exInput.addEventListener('change', () => {
                        if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                        window.formatGrandTotalsDualSdgUsd();
                    });
                    exInput.addEventListener('input', () => {
                        if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
                        window.formatGrandTotalsDualSdgUsd();
                    });
                }
                const curSel = document.getElementById('hdr-currency');
                if (curSel) {
                    curSel.addEventListener('change', () => setTimeout(() => window.formatGrandTotalsDualSdgUsd(), 30));
                }
                window.addEventListener('resize', () => window.alignGrandTotalUnderTotalColumn());
                const tableEl = document.getElementById('pos-grid');
                if (tableEl && typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(() => window.alignGrandTotalUnderTotalColumn()).observe(tableEl);
                }
                // Redirect Enter key from #hdr-description / #hdr-cost-center and F2 key to first grid item input
                document.addEventListener('keydown', function(ev) {
                    if (ev.key === 'F2') {
                        const firstItem = document.querySelector('#pos-grid input[id^="item-search-"]');
                        if (firstItem) {
                            ev.preventDefault();
                            ev.stopImmediatePropagation();
                            firstItem.focus();
                            if (typeof firstItem.select === 'function') firstItem.select();
                        }
                    } else if (ev.key === 'Enter' && !ev.shiftKey && !ev.ctrlKey && !ev.altKey) {
                        if (ev.target && (ev.target.id === 'hdr-description' || ev.target.id === 'hdr-cost-center')) {
                            setTimeout(() => {
                                if (document.activeElement === document.getElementById('quick-item-search') || document.activeElement === ev.target) {
                                    const firstItem = document.querySelector('#pos-grid input[id^="item-search-"]');
                                    if (firstItem) {
                                        firstItem.focus();
                                        if (typeof firstItem.select === 'function') firstItem.select();
                                    }
                                }
                            }, 50);
                        }
                    }
                }, true);
            });
            window.syncFinStripCurrencyBtnUI();
            window.normalizePurchaseTfootLayout();
        })();
        (function() {
            const TONES = [
                { id: 'dark_midnight',   name: 'داكن كحلي ملكي (Dark)',   bg: '#0f172a', fg: '#1e293b', brd: '#38bdf8', isDark: true, themeKey: 'midnight_obsidian' },
                { id: 'ivory_default',   name: 'عاجي دافئ (فاتح افتراضي)', bg: '#d9c39e', fg: '#f3e5ab', brd: '#854d0e', themeKey: 'warm_cream' },
                { id: 'royal_bronze',    name: 'برونزي ملكي فاخر',      bg: '#b07239', fg: '#d9a066', brd: '#78350f', themeKey: 'warm_cream' },
                { id: 'platinum_gray',   name: 'رمادي بلاتيني احترافي', bg: '#64748b', fg: '#94a3b8', brd: '#334155', themeKey: 'exec_gray' },
                { id: 'crystal_silver',  name: 'فضي جليدي ناصع',        bg: '#cbd5e1', fg: '#e2e8f0', brd: '#64748b', themeKey: 'ultra_white' },
                { id: 'sage_pearl',      name: 'لؤلؤي زمردي هادئ',      bg: '#10b981', fg: '#6ee7b7', brd: '#047857', themeKey: 'warm_cream' },
                { id: 'sky_alabaster',   name: 'سماوي لازوردي فاتح',    bg: '#0284c7', fg: '#38bdf8', brd: '#0369a1', themeKey: 'ultra_white' },
                { id: 'warm_mocha_dark', name: 'موكا صحراوي غامق',      bg: '#6b4226', fg: '#9c6b43', brd: '#451a03', themeKey: 'warm_cream' },
                { id: 'deep_slate_dark', name: 'رصاصي فحمي غامق',       bg: '#334155', fg: '#475569', brd: '#0f172a', themeKey: 'exec_gray' }
            ];
            window.applyPurchaseLightTone = function(toneId, notify) {
                const t = TONES.find(x => x.id === toneId) || (['dark_midnight','midnight_obsidian','dark','pure_oled'].includes(toneId) ? TONES[0] : TONES[1]);
                const targetTheme = t.isDark ? 'midnight_obsidian' : (t.themeKey || 'warm_cream');
                document.documentElement.setAttribute('data-light-tone', t.id);
                document.documentElement.setAttribute('data-theme', targetTheme);
                document.documentElement.classList.toggle('light-theme', !t.isDark);
                document.documentElement.classList.toggle('dark-theme', !!t.isDark);
                if (document.body) {
                    document.body.setAttribute('data-light-tone', t.id);
                    document.body.setAttribute('data-theme', targetTheme);
                    document.body.classList.toggle('light-theme', !t.isDark);
                    document.body.classList.toggle('dark-theme', !!t.isDark);
                }
                try {
                    document.cookie = 'magaies_light_tone=' + encodeURIComponent(t.id) + '; path=/; max-age=315360000; SameSite=Lax';
                    document.cookie = 'magaies_active_theme=' + encodeURIComponent(targetTheme) + '; path=/; max-age=315360000; SameSite=Lax';
                    localStorage.setItem('pur_light_tone_palette', t.id);
                    localStorage.setItem('magaies_light_tone', t.id);
                    ['magaies_active_theme','magaiees_global_theme','magaiees_mobile_theme','ui_theme'].forEach(k => localStorage.setItem(k, targetTheme));
                    localStorage.setItem('erp_theme', t.isDark ? 'dark' : 'light');
                } catch (_e) {}
                if (notify) {
                    if (typeof window.applyMasterTheme === 'function') window.applyMasterTheme(targetTheme, false);
                    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: targetTheme, tone: t.id, isLight: !t.isDark } }));
                    if (typeof showToast === 'function') showToast((t.isDark ? '🌙 ' : '🎨 ') + 'تم تثبيت المظهر في كل البرنامج: ' + t.name, 'success', 2600);
                }
            };
            const getCkTone = () => { const m = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/); return m ? decodeURIComponent(m[1]) : ''; };
            const savedTone = getCkTone() || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || document.documentElement.getAttribute('data-light-tone') || 'ivory_default';
            window.applyPurchaseLightTone(savedTone, false);
            window.openPurchaseLightTonePopover = function(anchorEl) {
                if (typeof window.openUniversalErpTonePopover === 'function' && window.openUniversalErpTonePopover !== window.openPurchaseLightTonePopover) {
                    window.openUniversalErpTonePopover(anchorEl);
                    return;
                }
                let pop = document.getElementById('pur-light-tone-popover');
                if (!pop) {
                    pop = document.createElement('div');
                    pop.id = 'pur-light-tone-popover';
                    document.body.appendChild(pop);
                    document.addEventListener('click', (e) => {
                        const isThemeBtn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
                        if (pop.style.display !== 'none' && !pop.contains(e.target) && !isThemeBtn) pop.style.display = 'none';
                    });
                }
                if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
                const isDarkNow = !document.documentElement.classList.contains('light-theme');
                pop.style.cssText = 'position:fixed;z-index:9999999;background:' + (isDarkNow ? 'linear-gradient(145deg,#0f172a,#1e293b)' : 'linear-gradient(145deg,#fffbf2,#f3ead8)') + ';border:2px solid #fbbf24;border-radius:14px;padding:10px;box-shadow:0 18px 42px rgba(0,0,0,0.65),0 0 22px rgba(245,158,11,0.35);min-width:255px;direction:rtl;font-family:Tajawal,Cairo,sans-serif;';
                const cur = isDarkNow ? 'dark_midnight' : (document.documentElement.getAttribute('data-light-tone') || 'ivory_default');
                const hdrCol = isDarkNow ? '#fde047' : '#000000';
                pop.innerHTML = `<div style="color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;font-weight:900;font-size:0.92rem;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid rgba(148,163,184,0.35);display:flex;justify-content:space-between;align-items:center;"><span>🎨 السمات ونغمات النظام (9)</span><span style="font-size:0.75rem;color:${isDarkNow?'#94a3b8':'#000000'}!important;-webkit-text-fill-color:${isDarkNow?'#94a3b8':'#000000'}!important;">موحد 100%</span></div>` +
                    TONES.map(t => {
                        const txtCol = t.isDark ? '#ffffff' : '#000000';
                        const btnBg = t.isDark ? `linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%)` : `linear-gradient(135deg,#ffffff 0%,${t.fg} 100%)`;
                        return `<button type="button" data-tone-dark="${t.isDark?'1':'0'}" onclick="window.applyPurchaseLightTone('${t.id}',true);document.getElementById('pur-light-tone-popover').style.display='none';" style="width:100%;display:flex;align-items:center;gap:10px;padding:7px 10px;margin-bottom:5px;border-radius:9px;cursor:pointer;background:${btnBg}!important;border:${cur===t.id?'2.5px solid #d97706':'1.5px solid '+t.brd}!important;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;font-size:0.88rem;text-align:right;"><span style="width:28px;height:28px;border-radius:7px;background:linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%);border:2px solid ${t.brd};flex-shrink:0;"></span><span style="flex:1;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;">${t.name}</span>${cur===t.id?`<span style="color:${t.isDark?'#fde047':'#047857'}!important;-webkit-text-fill-color:${t.isDark?'#fde047':'#047857'}!important;">✓</span>`:''}</button>`;
                    }).join('');
                const r = anchorEl ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
                pop.style.top = Math.max(10, Math.min(window.innerHeight - 450, r.bottom + 8)) + 'px';
                pop.style.left = Math.max(12, Math.min(window.innerWidth - 280, r.left)) + 'px';
                pop.style.display = 'block';
            };
            document.addEventListener('click', function(e) {
                const btn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
                if (!btn) return;
                e.preventDefault();
                e.stopImmediatePropagation();
                window.openPurchaseLightTonePopover(btn);
            }, true);
            document.addEventListener('contextmenu', function(e) {
                const btn = e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn');
                if (btn) { e.preventDefault(); window.openPurchaseLightTonePopover(btn); }
            }, true);
            // Interactive Kinetic Light Gestures (Cursor Specular Spotlight & Tactile Light Wave Burst)
            let _rafLight = 0;
            document.addEventListener('pointermove', function(e) {
                if (_rafLight) return;
                _rafLight = requestAnimationFrame(() => {
                    _rafLight = 0;
                    const card = e.target && e.target.closest && e.target.closest('#form-fields-card, header.magical-glass-header, .h-form-group, .table-container, .items-toolbar');
                    if (card) {
                        const r = card.getBoundingClientRect();
                        card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                        card.style.setProperty('--my', (e.clientY - r.top) + 'px');
                    }
                });
            }, { passive: true });
            const triggerKineticPulse = (el) => {
                if (!el) return;
                el.classList.remove('pur-kinetic-pulse');
                void el.offsetWidth;
                el.classList.add('pur-kinetic-pulse');
            };
            document.addEventListener('pointerdown', function(e) {
                const t = e.target && e.target.closest && e.target.closest('#purchase-editor-view button, .fin-metric-pill, .h-form-group, #pos-grid td[data-col-id="available"], #pos-grid td[data-col-id="warehouse"], #pos-grid td[data-col-id="unit"], #pos-grid td[data-col-id="price"]');
                if (t) triggerKineticPulse(t);
            }, { passive: true });
            document.addEventListener('change', function(e) {
                const z = e.target && e.target.closest && e.target.closest('.group-currency, .group-warehouse, #pos-grid td[data-col-id="warehouse"], #pos-grid td[data-col-id="unit"], #pos-grid td[data-col-id="price"]');
                if (z) triggerKineticPulse(z);
            }, { passive: true });
        })();
        </script>
    <!-- ══════════════════════════════════════════════════════════════════════════
         PURCHASE DESIGN CONSTITUTION 2027 — HUB VIEW (#purchase-hub-view)
         9 Tones + 3px Sculpted Borders + Crisp White Inputs + Dual Currency Toggle
         ══════════════════════════════════════════════════════════════════════════ -->
    <style id="purchase-hub-constitution-2027-style">
        /* 1. Royal Dark (dark_midnight) on #purchase-hub-view */
        html:not(.light-theme) #purchase-hub-view {
            background: #010308 !important;
            color: #ffffff !important;
        }
        html:not(.light-theme) #purchase-hub-view .neo-card,
        html:not(.light-theme) #purchase-hub-view .smart-filter-bar {
            background: linear-gradient(145deg, #1b3159 0%, #132341 100%) !important;
            border: 2.5px solid rgba(56, 189, 248, 0.78) !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.85), inset 0 1.5px 0 rgba(255, 255, 255, 0.22) !important;
            border-radius: 16px !important;
        }
        html:not(.light-theme) #purchase-hub-view .hub-action-card {
            background: linear-gradient(145deg, #1d345e 0%, #132240 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.65) !important;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.65) !important;
        }
        html:not(.light-theme) #purchase-hub-view .hub-action-card .title {
            color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 1.05rem !important;
        }
        html:not(.light-theme) #purchase-hub-view #hub-filter-type,
        html:not(.light-theme) #purchase-hub-view #hub-filter-value {
            background: #0b1120 !important;
            color: #ffffff !important;
            border: 2px solid #38bdf8 !important;
            font-weight: 900 !important;
        }
        html:not(.light-theme) #purchase-hub-view #hub-pos-grid thead th {
            background: #0d182c !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 0.98rem !important;
            border-bottom: 2.5px solid #38bdf8 !important;
        }
        html:not(.light-theme) #purchase-hub-view #hub-transactions-body tr td {
            color: #ffffff !important;
            font-weight: 900 !important;
            border-bottom: 1px solid rgba(56, 189, 248, 0.22) !important;
        }

        /* 2. All 8 Light Tones (--lt-* variables, 3px sculpted borders, pure white inputs, pitch-black 900 text) */
        html.light-theme #purchase-hub-view {
            background: var(--lt-bg-main, #f3ead8) !important;
            color: var(--lt-text, #120500) !important;
        }
        html.light-theme #purchase-hub-view .neo-card,
        html.light-theme #purchase-hub-view .smart-filter-bar {
            background: var(--lt-bg-card, #e8dbc3) !important;
            border: 3px solid var(--lt-border, #5c2808) !important;
            box-shadow: 0 8px 22px rgba(45, 20, 5, 0.15), inset 0 1.5px 0 rgba(255, 255, 255, 0.75) !important;
            color: var(--lt-text, #120500) !important;
            border-radius: 16px !important;
        }
        html.light-theme #purchase-hub-view .hub-action-card {
            background: var(--lt-bg-subcard, #dfd0b5) !important;
            border: 3px solid var(--lt-border, #5c2808) !important;
            box-shadow: 0 5px 14px rgba(45, 20, 5, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
        }
        html.light-theme #purchase-hub-view .hub-action-card:hover {
            background: #ffffff !important;
            transform: translateY(-2px);
        }
        html.light-theme #purchase-hub-view .hub-action-card .title {
            color: var(--lt-text, #120500) !important;
            -webkit-text-fill-color: var(--lt-text, #120500) !important;
            font-weight: 900 !important;
            font-size: 1.06rem !important;
            text-shadow: none !important;
        }
        html.light-theme #purchase-hub-view #hub-filter-type,
        html.light-theme #purchase-hub-view #hub-filter-value {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2.5px solid var(--lt-border, #5c2808) !important;
            font-weight: 900 !important;
            font-size: 1rem !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06) !important;
        }
        html.light-theme #purchase-hub-view .filter-time-group {
            background: var(--lt-bg-subcard, #dfd0b5) !important;
            border: 2px solid var(--lt-border, #5c2808) !important;
        }
        html.light-theme #purchase-hub-view .hub-time-btn {
            color: var(--lt-text, #120500) !important;
            font-weight: 900 !important;
        }
        html.light-theme #purchase-hub-view #hub-pos-grid thead,
        html.light-theme #purchase-hub-view #hub-pos-grid thead tr,
        html.light-theme #purchase-hub-view #hub-pos-grid thead th {
            background: var(--lt-bg-header, #d6c3a5) !important;
            color: var(--lt-text, #120500) !important;
            -webkit-text-fill-color: var(--lt-text, #120500) !important;
            font-weight: 900 !important;
            font-size: 0.98rem !important;
            border-bottom: 3px solid var(--lt-border, #5c2808) !important;
        }
        html.light-theme #purchase-hub-view #hub-transactions-body tr {
            background: #ffffff !important;
        }
        html.light-theme #purchase-hub-view #hub-transactions-body tr:nth-child(even) {
            background: var(--lt-bg-subcard, #f5ebd8) !important;
        }
        html.light-theme #purchase-hub-view #hub-transactions-body tr td,
        html.light-theme #purchase-hub-view #hub-transactions-body tr td span:not(.pur-price-lamp) {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            border-bottom: 1.5px solid var(--lt-border, #5c2808) !important;
        }
        html.light-theme #purchase-hub-view #hub-transactions-body tr td.hub-col-invno {
            color: #0369a1 !important;
            -webkit-text-fill-color: #0369a1 !important;
        }
        html.light-theme #purchase-hub-view .hub-sum-cap,
        html.light-theme #purchase-hub-view .hub-sum-cap strong,
        html.light-theme #purchase-hub-view #hub-fin-curr-toggle-btn {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
    </style>

    <script id="purchase-hub-constitution-2027-script">
    (function() {
        window._hubShowInUsd = false;
        window.getHubExchangeRate = function() {
            const elRate = document.getElementById('hdr-exchange-rate');
            if (elRate && parseFloat(elRate.value) > 1) return parseFloat(elRate.value);
            const saved = parseFloat(localStorage.getItem('magaies_usd_exchange_rate') || localStorage.getItem('usd_exchange_rate') || '2650');
            return (saved && saved > 1) ? saved : 2650;
        };
        window.toggleHubSummaryCurrency = function() {
            window._hubShowInUsd = !window._hubShowInUsd;
            if (typeof filterHubTransactions === 'function') filterHubTransactions();
            else if (typeof loadHubTransactions === 'function') loadHubTransactions();
        };
    })();
    </script>
</body>
</html>
