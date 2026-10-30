<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() === 0) { @ob_start('ob_gzhandler'); } ?>
<?php 
$page_title = 'المشتريات'; 
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
<html lang="ar" dir="rtl">
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
  var raw = localStorage.getItem('magaies_active_theme') || 
            localStorage.getItem('magaiees_global_theme') || 
            localStorage.getItem('magaiees_mobile_theme') || 
            localStorage.getItem('ui_theme') || 
            'warm_cream';
  var aliasMap = {
    'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream',
    'pure-platinum': 'ultra_white', 'offwhite': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white',
    'pearl-platinum': 'exec_gray', 'nordic-paper': 'warm_cream', 'solar_contrast': 'warm_cream',
    'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian', 'ocean-navy': 'midnight_obsidian', 'navy': 'midnight_obsidian',
    'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
    'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled', 'dark': 'pure_oled',
    'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight'
  };
  var activeKey = aliasMap[raw] || raw;
  var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
  if (!valid7.includes(activeKey)) activeKey = 'warm_cream';
  var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);

  document.documentElement.setAttribute('data-theme', activeKey);
  if (isLight) document.documentElement.classList.add('light-theme');
  else document.documentElement.classList.remove('light-theme');

  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) {
      document.body.setAttribute('data-theme', activeKey);
      if (isLight) document.body.classList.add('light-theme');
      else document.body.classList.remove('light-theme');
    }
  });
})();
</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استلام بضاعة | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=20261029_fast">
    <link rel="stylesheet" href="css/theme.css?v=20261029_fast">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css?v=20261029_fast">
    <link rel="stylesheet" href="css/print-invoice.css">
    <link rel="stylesheet" href="css/purchase-module-v2.css?v=20261029_fast">
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
            background: 
                radial-gradient(circle at 95% 5%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 5% 95%, rgba(236, 72, 153, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 35% 45%, rgba(59, 130, 246, 0.05) 0%, transparent 60%),
                radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
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

        /* 2. Quick Search Zone & Item Search Zone (منطقة البحث — إضاءة سماوية نيون صافية) */
        #quick-item-search:hover,
        .quick-search:hover #quick-item-search,
        .pos-grid td[data-col-id="item"]:hover .g-input,
        .pos-grid td[data-col-id="item"] .g-input:hover {
            border-color: #38bdf8 !important;
            background: rgba(15, 23, 42, 0.85) !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.55), inset 0 0 8px rgba(56, 189, 248, 0.18) !important;
        }
        #quick-item-search:focus,
        .pos-grid td[data-col-id="item"] .g-input:focus {
            border-color: #38bdf8 !important;
            background: rgba(15, 23, 42, 0.95) !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.4), 0 0 22px rgba(56, 189, 248, 0.78), inset 0 0 10px rgba(56, 189, 248, 0.22) !important;
            transform: translateY(-1px);
        }

        /* 3. Available Stock Zone (منطقة المتاح — إضاءة سيان أكوا هادئة) */
        .pos-grid td[data-col-id="available"] .avail-val {
            border: 1.5px solid rgba(45, 212, 191, 0.35) !important;
            background: rgba(15, 23, 42, 0.6) !important;
        }
        .pos-grid td[data-col-id="available"]:hover .avail-val,
        .pos-grid td[data-col-id="available"] .avail-val:hover {
            border-color: #2dd4bf !important;
            box-shadow: 0 0 14px rgba(45, 212, 191, 0.55), inset 0 0 8px rgba(45, 212, 191, 0.2) !important;
            transform: scale(1.02);
        }

        /* 4. Unit Zone (منطقة الوحدة — إضاءة بنفسجية ملكية لتمييز القياس عن الأرقام) */
        .pos-grid td[data-col-id="unit"] .g-select {
            border-color: rgba(167, 139, 250, 0.35) !important;
        }
        .pos-grid td[data-col-id="unit"]:hover .g-select,
        .pos-grid td[data-col-id="unit"] .g-select:hover {
            border-color: #a78bfa !important;
            background: rgba(30, 27, 75, 0.75) !important;
            box-shadow: 0 0 14px rgba(167, 139, 250, 0.58), inset 0 0 8px rgba(167, 139, 250, 0.22) !important;
        }
        .pos-grid td[data-col-id="unit"] .g-select:focus {
            border-color: #c4b5fd !important;
            background: rgba(30, 27, 75, 0.92) !important;
            box-shadow: 0 0 0 2px rgba(167, 139, 250, 0.42), 0 0 22px rgba(167, 139, 250, 0.82), inset 0 0 10px rgba(167, 139, 250, 0.25) !important;
            transform: scale(1.02);
        }

        /* 5. Quantity Zone (منطقة الكمية — إضاءة كهرمانية ذهبية دافئة للانتباه العددي) */
        .pos-grid td[data-col-id="quantity"] .g-input {
            border-color: rgba(251, 191, 36, 0.38) !important;
        }
        .pos-grid td[data-col-id="quantity"]:hover .g-input,
        .pos-grid td[data-col-id="quantity"] .g-input:hover {
            border-color: #fbbf24 !important;
            background: rgba(45, 30, 10, 0.65) !important;
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.58), inset 0 0 8px rgba(251, 191, 36, 0.2) !important;
        }
        .pos-grid td[data-col-id="quantity"] .g-input:focus {
            border-color: #facc15 !important;
            background: rgba(45, 30, 10, 0.88) !important;
            box-shadow: 0 0 0 2px rgba(250, 204, 21, 0.45), 0 0 24px rgba(250, 204, 21, 0.82), inset 0 0 10px rgba(250, 204, 21, 0.25) !important;
            transform: scale(1.03);
        }

        /* 6. Price Zone (منطقة السعر — إضاءة زمردية مالية مريحة للعين) */
        .pos-grid td[data-col-id="price"] .g-input {
            border-color: rgba(52, 211, 153, 0.38) !important;
        }
        .pos-grid td[data-col-id="price"]:hover .g-input,
        .pos-grid td[data-col-id="price"] .g-input:hover {
            border-color: #34d399 !important;
            background: rgba(6, 48, 38, 0.68) !important;
            box-shadow: 0 0 15px rgba(52, 211, 153, 0.58), inset 0 0 8px rgba(52, 211, 153, 0.2) !important;
        }
        .pos-grid td[data-col-id="price"] .g-input:focus {
            border-color: #10b981 !important;
            background: rgba(6, 48, 38, 0.9) !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.45), 0 0 24px rgba(52, 211, 153, 0.85), inset 0 0 10px rgba(52, 211, 153, 0.25) !important;
            transform: scale(1.03);
        }

        /* 7. Warehouse Zone (منطقة المخزن — إضاءة برتقالية مرجانية) */
        .pos-grid td[data-col-id="warehouse"] .g-select {
            border-color: rgba(251, 146, 60, 0.35) !important;
        }
        .pos-grid td[data-col-id="warehouse"]:hover .g-select,
        .pos-grid td[data-col-id="warehouse"] .g-select:hover,
        .pos-grid td[data-col-id="warehouse"] .g-select:focus {
            border-color: #fb923c !important;
            box-shadow: 0 0 15px rgba(251, 146, 60, 0.58), inset 0 0 8px rgba(251, 146, 60, 0.2) !important;
        }

        /* 8. Total Zone (منطقة الجملة — إطار مضيء ينير عند الاقتراب أو عند العمل في نفس السطر) */
        .pos-grid td[data-col-id="total"] .total-val {
            border: 1.5px solid rgba(56, 189, 248, 0.35) !important;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.72), rgba(16, 185, 129, 0.14)) !important;
            border-radius: 9px !important;
            padding: 6px 10px !important;
            box-shadow: inset 0 0 8px rgba(56, 189, 248, 0.1) !important;
        }
        .pos-grid td[data-col-id="total"]:hover .total-val,
        .pos-grid td[data-col-id="total"] .total-val:hover,
        .pos-grid tbody tr:focus-within td[data-col-id="total"] .total-val {
            border-color: #34d399 !important;
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.75), rgba(14, 165, 233, 0.28)) !important;
            box-shadow: 0 0 0 1.5px rgba(52, 211, 153, 0.38), 0 0 18px rgba(52, 211, 153, 0.68), inset 0 0 10px rgba(56, 189, 248, 0.25) !important;
            transform: scale(1.02);
        }

        /* Active Row Ambient Highlight when typing inside any cell of the row */
        .pos-grid tbody tr:focus-within td {
            background-color: rgba(56, 189, 248, 0.09) !important;
        }

        /* 9. Light Theme Harmonious Border Illumination Overrides */
        :root.light-theme .pos-grid td[data-col-id="item"]:hover .g-input,
        :root.light-theme .pos-grid td[data-col-id="item"] .g-input:focus,
        :root.light-theme #quick-item-search:hover,
        :root.light-theme #quick-item-search:focus {
            background: #f0f9ff !important;
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25), 0 0 14px rgba(14, 165, 233, 0.45) !important;
        }
        :root.light-theme .pos-grid td[data-col-id="unit"]:hover .g-select,
        :root.light-theme .pos-grid td[data-col-id="unit"] .g-select:focus {
            background: #f5f3ff !important;
            border-color: #7c3aed !important;
            box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.25), 0 0 14px rgba(139, 92, 246, 0.45) !important;
        }
        :root.light-theme .pos-grid td[data-col-id="quantity"]:hover .g-input,
        :root.light-theme .pos-grid td[data-col-id="quantity"] .g-input:focus {
            background: #fffbeb !important;
            border-color: #d97706 !important;
            box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.25), 0 0 14px rgba(245, 158, 11, 0.45) !important;
        }
        :root.light-theme .pos-grid td[data-col-id="price"]:hover .g-input,
        :root.light-theme .pos-grid td[data-col-id="price"] .g-input:focus {
            background: #ecfdf5 !important;
            border-color: #059669 !important;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25), 0 0 14px rgba(16, 185, 129, 0.45) !important;
        }
        :root.light-theme .pos-grid td[data-col-id="total"] .total-val {
            background: linear-gradient(135deg, #f0fdf4, #e0f2fe) !important;
            border: 1.5px solid rgba(14, 165, 233, 0.4) !important;
        }
        :root.light-theme .pos-grid td[data-col-id="total"]:hover .total-val,
        :root.light-theme .pos-grid tbody tr:focus-within td[data-col-id="total"] .total-val {
            border-color: #059669 !important;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25), 0 0 15px rgba(16, 185, 129, 0.45) !important;
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
                echo "#pos-grid th[data-col-id='" . $colId . "'] { color: #38bdf8 !important; font-weight: bold !important; text-align: " . $colAlign . " !important; font-size: " . $colSize . " !important; " . $wStyle . " }\n";
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
            border: 2px solid #000000 !important;
            font-weight: 900 !important;
            text-shadow: none !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }
        :root.light-theme #hdr-cost-center.is-default-hint:not(:focus) {
            background: #ffffff !important;
            border: 2px solid #000000 !important;
        }

        /* User Audit Badge & Financial Strip in Light Theme */
        :root.light-theme #purchase-user-audit-container .user-audit-badge,
        :root.light-theme #customer-financial-card .fin-strip-inner {
            background: #ffffff !important;
            border: 2px solid #0f172a !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06) !important;
        }
        :root.light-theme #purchase-user-audit-container .user-audit-badge .uab-label,
        :root.light-theme #purchase-user-audit-container .user-audit-badge .uab-val,
        :root.light-theme #customer-financial-card .fin-strip-inner span {
            color: #000000 !important;
            font-weight: 900 !important;
        }

        /* 3. Grid Table Headers, Typed Inputs & Totals (الكتابة الحقيقية باللون الأسود، وإضافة/حذف/الجملة بألوان واضحة وتباين عالٍ) */
        :root.light-theme body #pos-grid thead tr th {
            background: #dfd3bc !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border-bottom: 2.5px solid #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme body #pos-grid tbody tr td {
            background: #f4ecde !important;
            border-bottom: 1.5px solid #cbd5e1 !important;
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
            border: 2px solid #0f172a !important;
            font-weight: 900 !important;
            text-shadow: none !important;
        }
        :root.light-theme body #pos-grid tbody tr td input.g-input.is-zero-val:not(:focus),
        :root.light-theme body #pos-grid tbody tr td select.g-select.is-default-hint:not(:focus) {
            background: #ffffff !important;
            border: 1.5px solid #94a3b8 !important;
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

        /* الجملة (Total Column & Grand Total) — High Contrast */
        :root.light-theme body #pos-grid tbody tr td[data-col-id="total"] .total-val:not(.is-zero-val),
        :root.light-theme body #pos-grid tbody tr td[data-col-id="col_total"] .total-val:not(.is-zero-val) {
            background: #d1fae5 !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2px solid #047857 !important;
            font-weight: 900 !important;
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.15) !important;
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
        :root.light-theme .modal-overlay span,
        :root.light-theme .modal-overlay .v-label,
        :root.light-theme .modal-overlay .modal-close,
        :root.light-theme #grid-settings-modal h3,
        :root.light-theme #grid-settings-modal h4,
        :root.light-theme #grid-settings-modal label,
        :root.light-theme #grid-settings-modal span,
        :root.light-theme #share-popup-menu button,
        :root.light-theme #share-popup-menu span,
        :root.light-theme #auto-send-purchase-dropdown-menu span,
        :root.light-theme #payment-panel label,
        :root.light-theme #payment-panel span:not(.pay-toggle-btn.active *),
        :root.light-theme #pay-credit-section span,
        :root.light-theme #pay-cash-section div,
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

        /* B. Dark Themes: Pure Black Writing Area (#000000) + Crisp White Text (#FFFFFF) + Thick White Border (2.2px) + Stepped Slate Surroundings (#162038) — ZERO Inversion on :focus / :active / :-webkit-autofill */
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #form-fields-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .purchase-glass-card,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .panel-header-row,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #pos-grid tbody tr td,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .smart-filter-bar,
        html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #purchase-hub-view .neo-card {
            background: #162038 !important;
            border: 2px solid rgba(255, 255, 255, 0.72) !important;
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
            background: #000000 !important;
            background-color: #000000 !important;
            -webkit-box-shadow: 0 0 0 1000px #000000 inset !important;
            border: 2.2px solid #ffffff !important;
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

        /* C. Light & White Themes: Pure White Writing Area (#FFFFFF) + Deep Pitch-Black Text & Border (2.2px #000000) + Stepped Beige Surroundings (#f5efe2) */
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
            border: 2.2px solid #000000 !important;
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
                const selects = scope.querySelectorAll('#pos-grid select.g-select, #hdr-cost-center');
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
<body>
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
<div class="dash-main">
    
    <div class="ambient-bg"></div>
    
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
                <div class="icon">🚀</div>
                <div class="text-content">
                    <div class="title">بوابة الاستلام</div>
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

            <!-- OCR Scanner Card -->
            <button onclick="triggerInvoiceOCR()" class="hub-action-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.35)); border: 1px solid rgba(52, 211, 153, 0.5); cursor: pointer;">
                <div class="icon" style="filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.6));">📷</div>
                <div class="text-content">
                    <div class="title" style="color: #34d399; font-weight: 800;">مسح فاتورة</div>
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
            <div class="neo-card-title" style="font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 12px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span>📦 سجل المشتريات</span>
                </div>
                <div id="hub-summary-bar" style="display: flex; gap: 18px; font-size: 0.85rem; color: #94a3b8; font-weight: 600;"></div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الفاتورة</th>
                            <th style="text-align:right;">التاريخ</th>
                            <th style="text-align:right;">المورد</th>
                            <th style="text-align:right;">الصنف</th>
                            <th style="text-align:center;">الإجمالي</th>
                            <th style="text-align:center;">الحالة</th>
                            <th style="text-align:center;">المخزن</th>
                            <th style="text-align:center;">الإجراءات</th>
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
    /* ═══ WORLD-CLASS PURCHASE HEADER & SLIM TOP BAR HARMONY ═══ */
    #purchase-top-container #form-fields-card {
        padding: 12px 16px !important;
        border-radius: 16px !important;
        background: linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(18, 16, 32, 0.92)) !important;
        border: 1px solid rgba(56, 189, 248, 0.22) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
    }
    /* Row 0: Unified Slim 38px Bar for Invoice No, Date, User Badge & Supplier Financial Strip */
    #purchase-row0-bar {
        display: flex !important;
        width: 100% !important;
        gap: 10px !important;
        margin-bottom: 4px !important;
        align-items: center !important;
        min-height: 42px !important;
    }
    #invoice-info-box {
        flex: 1.25;
        display: flex !important;
        gap: 10px !important;
        width: 100%;
        min-height: 40px !important;
        max-height: 42px !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 2px 10px !important;
        border-radius: 11px !important;
        background: rgba(15, 23, 42, 0.55) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-sizing: border-box !important;
    }
    #invoice-info-box .group-invoice {
        flex: 1.35 !important;
        min-width: 225px !important;
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        margin: 0 !important;
        height: 36px !important;
    }
    #invoice-info-box #hdr-inv-label,
    #invoice-info-box #hdr-date-label {
        margin: 0 !important;
        font-size: 0.82rem !important;
        font-weight: 800 !important;
        color: #cbd5e1 !important;
        white-space: nowrap !important;
    }
    #invoice-info-box .pro-serial-container {
        flex: 1 !important;
        min-width: 165px !important;
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0 6px !important;
        border-radius: 9px !important;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.75)) !important;
        border: 1.5px solid rgba(56, 189, 248, 0.45) !important;
        box-shadow: inset 0 0 12px rgba(56, 189, 248, 0.12), 0 2px 8px rgba(0, 0, 0, 0.25) !important;
        box-sizing: border-box !important;
    }
    #invoice-info-box #inv-number {
        font-size: 1.35rem !important;
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
        flex: 0.95 !important;
        min-width: 165px !important;
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        margin: 0 !important;
        height: 36px !important;
    }
    #invoice-info-box #hdr-date {
        flex: 1 !important;
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        text-align: center !important;
        padding: 0 8px !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 800 !important;
        font-size: 0.95rem !important;
        border-radius: 9px !important;
        box-sizing: border-box !important;
    }
    /* Slim Single-Line User Badge matching Invoice No & Date height (36px) */
    #purchase-user-audit-container {
        display: flex !important;
        align-items: center !important;
        height: 36px !important;
        flex-shrink: 0 !important;
    }
    #purchase-user-audit-container .user-audit-badge {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        padding: 0 11px !important;
        border-radius: 9px !important;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.75)) !important;
        border: 1px solid rgba(56, 189, 248, 0.32) !important;
        white-space: nowrap !important;
        box-sizing: border-box !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
    }
    #purchase-user-audit-container .user-audit-badge .uab-row {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 5px !important;
        flex-wrap: nowrap !important;
    }
    #purchase-user-audit-container .user-audit-badge .uab-edit-row,
    #purchase-user-audit-container .user-audit-badge .uab-deliv-row {
        display: none !important;
    }
    #purchase-user-audit-container .user-audit-badge .uab-label {
        font-size: 0.75rem !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
    }
    #purchase-user-audit-container .user-audit-badge .uab-val {
        font-size: 0.84rem !important;
        color: #38bdf8 !important;
        font-weight: 900 !important;
    }
    #purchase-user-audit-container .user-audit-badge .uab-tag {
        padding: 1px 6px !important;
        font-size: 0.68rem !important;
        border-radius: 5px !important;
    }
    /* Slim Single-Line Financial Strip matching exact 38px height */
    #customer-financial-card .fin-strip-inner {
        width: 100% !important;
        height: 38px !important;
        min-height: 38px !important;
        max-height: 38px !important;
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
    /* Row 1: Compact Labels (-1 degree) + Expanded Supplier Input + Bold Exchange Rate */
    #purchase-row1-bar .h-label {
        font-size: 0.82rem !important;
        font-weight: 800 !important;
        min-width: auto !important;
        margin: 0 !important;
        white-space: nowrap !important;
        letter-spacing: 0 !important;
    }
    #purchase-row1-bar .ctrl-input {
        height: 40px !important;
        min-height: 40px !important;
        border-radius: 10px !important;
        box-sizing: border-box !important;
    }
    #hdr-exchange-rate {
        font-size: 1.38rem !important;
        font-weight: 900 !important;
        font-family: 'Outfit', 'JetBrains Mono', sans-serif !important;
        letter-spacing: 0.5px !important;
        color: #10b981 !important;
        background: rgba(16, 185, 129, 0.12) !important;
        border: 1.5px solid rgba(16, 185, 129, 0.4) !important;
        text-shadow: 0 0 8px rgba(16, 185, 129, 0.25) !important;
    }
</style>
<div id="purchase-top-container" class="form-expanded" style="display: flex; gap: 15px; align-items: stretch; transition: all 0.5s ease; width: 100%;">
<div class="neo-card expanded-form purchase-glass-card" id="form-fields-card" style="margin-bottom: 0;">
            <div style="display: flex; flex-direction: column; gap: 10px;">
                
                <!-- ROW 0 WRAPPER (Unified Slim Level: Invoice No, Date, User, Financial Strip) -->
                <div id="purchase-row0-bar">
                    <!-- RIGHT IN RTL: Invoice, Date & Slim User Badge -->
                    <div id="invoice-info-box">
                        
                        <div class="h-form-group group-invoice">
                            <label id="hdr-inv-label" class="h-label">رقم الفاتورة:</label>
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

                        <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.12); margin: 0 2px; flex-shrink: 0;"></div>
                        <div id="purchase-user-audit-container"></div>
                    </div>
                    
                    <!-- LEFT IN RTL: Slim Single-Line Financial Strip -->
                    <div id="customer-financial-card" class="fin-strip-inline" style="flex: 0; width: 0; opacity: 0; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center;">
                        <div class="fin-strip-inner">
                            <div class="fin-metric-pill" style="gap: 6px;">
                                <span style="font-size: 0.95rem;">📊</span>
                                <span style="font-weight: 800; color:#e2e8f0; font-size:0.75rem;">الحالة:</span>
                                <span id="fin-status-msg" style="color:var(--text-dim); font-size:0.72rem; font-weight:700;">لا يوجد</span>
                            </div>
                            <div style="width:1px; height:18px; background:rgba(255,255,255,0.12); flex-shrink:0;"></div>
                            <div id="fin-wrap-ledger" class="fin-metric-pill">
                                <span style="font-size:0.7rem; color:#94a3b8; font-weight:700;">الرصيد:</span>
                                <span id="fin-ledger" style="font-weight:900; color:#fff; font-size:0.82rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div style="width:1px; height:18px; background:rgba(255,255,255,0.12); flex-shrink:0;"></div>
                            <div id="fin-wrap-debt" class="fin-metric-pill">
                                <span style="font-size:0.7rem; color:#f87171; font-weight:700;">المديونية:</span>
                                <span id="fin-total-debt" style="font-weight:900; color:#ef4444; font-size:0.82rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div style="width:1px; height:18px; background:rgba(255,255,255,0.12); flex-shrink:0;"></div>
                            <div id="fin-wrap-checks" class="fin-metric-pill">
                                <span style="font-size:0.7rem; color:#fbbf24; font-weight:700;">شيكات:</span>
                                <span id="fin-checks" style="font-weight:900; color:#f59e0b; font-size:0.82rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div style="width:1px; height:18px; background:rgba(255,255,255,0.12); flex-shrink:0;"></div>
                            <div id="fin-wrap-limit" class="fin-metric-pill">
                                <span style="font-size:0.7rem; color:#38bdf8; font-weight:700;">الحد:</span>
                                <span id="fin-credit-limit" style="font-weight:900; color:#38bdf8; font-size:0.82rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div style="width:1px; height:18px; background:rgba(255,255,255,0.12); flex-shrink:0;"></div>
                            <div class="fin-metric-pill" style="background: rgba(16,185,129,0.14); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(16,185,129,0.3);">
                                <span style="font-size:0.7rem; color:#34d399; font-weight:800;">✨ المتاح:</span>
                                <span id="fin-remaining" style="font-weight:900; color:#34d399; font-size:0.86rem; font-family:'Outfit',sans-serif;">0</span>
                            </div>
                            <div style="width:1px; height:18px; background:rgba(255,255,255,0.12); flex-shrink:0;"></div>
                            <div id="supplier-performance-rating-pill" class="fin-metric-pill" style="background: rgba(251,191,36,0.14); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(251,191,36,0.38); cursor:pointer;" onclick="window.refreshSupplierPerformanceRatingBadge(true)" title="بطاقة تقييم أداء والتزام وأسعار المورد">
                                <span id="supplier-rating-text" style="font-size:0.72rem; color:#fbbf24; font-weight:900;">⭐ تقييم المورد: ممتاز (98%)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unified Row 1: Supplier (Expanded), Exchange Rate (Larger Number), Warehouse & Cost Center (Auto-fit) -->
                <div id="purchase-row1-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; margin-bottom: 2px; width: 100%; align-items: center;">
                    <!-- 1. Supplier (Wider allocation + auto-fit supplier name) -->
                    <div class="h-form-group group-supplier" style="flex: 4.4; min-width: 380px; display: flex; align-items: center; gap: 7px; flex-direction: row !important; margin: 0;">
                          <label class="h-label" style="color: var(--amber, #f59e0b);">👤 المورد</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                              <div class="custom-dropdown" style="flex:1; min-width: 0;">
                                  <div class="custom-dropdown-input-wrap" style="position: relative;">
                                      <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم المورد..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width:100%; padding-left: 56px !important; padding-right: 10px !important; color: var(--amber, #fbbf24); font-size: 1.15rem; font-weight: 900; background-color: rgba(251,191,36,0.06); border: 1px solid rgba(251,191,36,0.35);" autocomplete="one-time-code">
                                      <button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('hdr-customer').value)" class="ledger-btn-2027" title="دفتر الأستاذ العام">أستاذ</button>
                                      <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();">
                                      <div class="custom-dropdown-list" id="customer-dropdown-list">
                                          <!-- Populated via JS -->
                                      </div>
                                  </div>
                              </div>
                              <button type="button" id="btn-quick-add-supplier" class="add-row-btn" onclick="openModal('add-customer-modal')" title="إضافة مورد جديد (F4)" style="flex-shrink: 0; width: 38px; height: 38px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                                  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                              </button>
                              <button type="button" id="btn-clone-last-supplier-inv" class="add-row-btn" onclick="window.cloneLastSupplierPurchaseInvoice()" title="نسخ أصناف آخر فاتورة للمورد بضغطة واحدة" style="flex-shrink: 0; height: 38px; padding: 0 9px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; font-size: 0.76rem; font-weight: 900; white-space: nowrap;">
                                  <span>📋</span><span>آخر فاتورة</span>
                              </button>
                          </div>
                          <div id="supplier-credit-limit-alert-bar" style="display:none; margin-top:4px; padding:5px 10px; border-radius:8px; background:rgba(239,68,68,0.14); border:1.5px solid rgba(239,68,68,0.55); color:#fca5a5; font-size:0.76rem; font-weight:900; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;"></div>
                    </div>
                    
                    <!-- 2. Currency & Exchange Rate (Larger bold number) -->
                    <div class="h-form-group group-currency" style="flex: 2.15; min-width: 235px; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
                          <label class="h-label">💱 سعر الصرف</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 5px; flex: 1; min-width: 0;">
                            <select class="v-input ctrl-input system-currency-select" id="hdr-currency" onchange="handleCurrencyChange(this.value); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="flex: 0.95; min-width: 82px; padding: 0 6px; font-size: 0.88rem; font-weight: 800;">
                                <option value="SDG">🇸🇩 جنيه</option>
                                <option value="USD">🇺🇸 دولار</option>
                                <option value="SAR">🇸🇦 ريال</option>
                                <option value="AED">🇦🇪 درهم</option>
                                <option value="EGP">🇪🇬 مصري</option>
                            </select>
                            <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" placeholder="الصرف" readonly style="flex: 1.25; min-width: 95px; text-align: center; cursor:pointer; padding: 0 4px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; this.dataset.userEdited='1'; if(window.autoFitHeaderFields) window.autoFitHeaderFields(); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
                        </div>
                    </div>

                    <!-- 3. Warehouse (Auto-fits to allocated space) -->
                    <div class="h-form-group group-warehouse" style="flex: 1.75; min-width: 185px; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
                          <label class="h-label">📦 المخزن</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                            <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width: 100%; font-weight: 800; padding: 0 8px;"></select>
                          </div>
                    </div>

                    <!-- 4. Cost Center (Auto-fits to allocated space) -->
                    <div class="h-form-group group-cost-center" style="flex: 1.7; min-width: 185px; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
                        <label class="h-label">🏷️ مركز الكلفة</label>
                        <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1; min-width: 0;">
                            <select class="v-input ctrl-input" id="hdr-cost-center" onchange="if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width: 100%; font-weight: 800; padding: 0 8px;">
                                <option value="">-- بدون مركز --</option>
                                <option value="C1">المركز الرئيسي</option>
                                <option value="C2">الفرع الأول</option>
                                <option value="C3">المشروع أ</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Description -->
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 2px; width: 100%;">
                    <div class="h-form-group group-description" style="flex: 1; display:flex; align-items:center; gap:8px; flex-direction: row !important; margin: 0;">
                        <label class="h-label" style="margin: 0; white-space: nowrap; font-size: 0.82rem !important; font-weight: 800 !important;">📝 البيان</label>
                        <div class="h-input-wrapper" style="display: flex; flex-direction: row !important; align-items: center !important; flex: 1;">
                            <input type="text" class="v-input ctrl-input" id="hdr-description" placeholder="ملاحظات، أرقام مرجعية..." style="width: 100%; height: 36px !important; min-height: 36px !important; border-radius: 9px !important;">
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
    <div class="purchase-bottom" style="flex: none; display: flex; flex-direction: column; padding: 0 15px 15px 15px; overflow: visible; margin-top: 12px;">
        
        <div class="panel-header-row" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 27, 50, 0.75)); border: 1px solid rgba(56, 189, 248, 0.18); border-radius: 12px; padding: 8px 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <div class="panel-title" style="white-space: nowrap; font-weight: 900; color: #38bdf8; font-size: 1.05rem; display: flex; align-items: center; gap: 6px; text-shadow: 0 0 10px rgba(56,189,248,0.25);">
                    <span>📦</span>
                    <span>أصناف الفاتورة</span>
                </div>
                <div class="quick-add-box" style="flex: 1; max-width: 410px; position: relative; z-index: 99999; display: flex; gap: 6px; align-items: center;">
                    <input type="text" class="g-input" id="quick-item-search" placeholder="🔍 بحث سريع عن صنف أو مسح باركود (F2)..."
                        style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(56,189,248,0.35); border-radius: 9px; color: var(--text-primary); padding: 0 12px; height: 36px; font-size: 0.92rem; font-weight: 700; flex: 1;"
                        onfocus="this.select(); showQuickItemDropdown()"
                        onblur="hideQuickItemDropdownDelayed()"
                        oninput="filterQuickItemDropdown()" autocomplete="new-password">
                    <button type="button" onclick="startCameraScanner('quick-item-search')" style="background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.35)); border: 1px solid rgba(56,189,248,0.5); border-radius: 9px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #38bdf8; transition: all 0.2s; box-shadow: 0 2px 8px rgba(56,189,248,0.2);" title="مسح باركود بالكاميرا">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </button>
                    <button type="button" onclick="addRow()" style="background: linear-gradient(135deg, rgba(16,185,129,0.22), rgba(5,150,105,0.38)); border: 1px solid rgba(16,185,129,0.5); border-radius: 9px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #34d399; transition: all 0.2s; box-shadow: 0 2px 8px rgba(16,185,129,0.2);" title="إضافة سطر جديد">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <button type="button" id="btn-voice-item-dictation-pur" onclick="window.startVoiceItemDictationPurchase()" style="background: linear-gradient(135deg, rgba(245,158,11,0.22), rgba(217,119,6,0.38)); border: 1px solid rgba(251,191,36,0.55); border-radius: 9px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #fbbf24; font-size: 0.95rem; transition: all 0.2s;" title="إملاء الصنف والكمية بالصوت مباشرة للجدول (مثال: أسمنت 10)">🎙️</button>
                    <div class="custom-dropdown-list" id="quick-item-dropdown-list" style="text-align:right; top: 100%; left: 0; right: 0; position: absolute;"></div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <button type="button" id="btn-ultra-zoom-pur" onclick="window.toggleUltraZoomPurchaseMode()" style="background: linear-gradient(135deg, rgba(245,158,11,0.18), rgba(217,119,6,0.3)); border: 1px solid rgba(251,191,36,0.55); color: #fbbf24; border-radius: 9px; height: 36px; padding: 0 10px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; font-weight: 900; font-size: 0.8rem; white-space: nowrap;" title="وضع القراءة فائقة الوضوح (+25% تكبير للخطوط والحواف)">
                    <span>🔍</span><span id="lbl-ultra-zoom-pur">تكبير +25%</span>
                </button>
                <button type="button" id="btn-hands-on-training-pur" onclick="window.startPurchaseHandsOnTraining()" style="background: linear-gradient(135deg, rgba(16,185,129,0.18), rgba(5,150,105,0.3)); border: 1px solid rgba(52,211,153,0.55); color: #34d399; border-radius: 9px; height: 36px; padding: 0 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.82rem; white-space: nowrap;" title="تدريب عملي تفاعلي بيدك في 3 خطوات">
                    <span>✋</span><span>درّبني بيدي</span>
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
                        <div id="pay-credit-section" class="payment-method-box" style="background: radial-gradient(circle at top left, rgba(217, 119, 6, 0.35), rgba(180, 83, 9, 0.15)); border: 3px solid rgba(217, 119, 6, 0.9); box-shadow: 0 0 25px rgba(217, 119, 6, 0.4), inset 0 0 15px rgba(180, 83, 9, 0.3); border-radius: 12px; padding: 12px 15px; display: none; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px; min-width: 200px; width: 200px; flex: none;">
                                <span style="font-size: 1.2rem;">💳</span>
                                <span style="font-weight: 800; color: #fcd34d; font-size: 0.95rem;">آجل على الحساب</span>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 320px; flex-wrap: wrap;">
                                <input type="number" class="v-input ctrl-input" id="pay-credit-amount" value="0.00" step="any" readonly placeholder="المبلغ الآجل..." style="flex: none; width: 145px; min-width: 145px; background: rgba(15,23,42,0.8); border: 1px solid rgba(180, 83, 9, 0.5); height: 38px; text-align: center; font-size: 1.15rem; font-weight: 800; color: #fcd34d !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                                
                                <div id="pay-credit-due-group" style="flex: 1.2; min-width: 180px; display: flex; align-items: center; gap: 5px;">
                                    <label style="font-size: 0.78rem; color: #94a3b8; white-space: nowrap;">تاريخ الاستحقاق:</label>
                                    <input type="date" class="v-input ctrl-input" id="pay-credit-due-date" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 38px; font-size:0.85rem; padding: 0 4px; width: 100%;">
                                </div>
                                
                                <button type="button" class="btn-action" onclick="previewCurrentPromissoryNote()" style="flex: 0.8; min-width: 120px; height: 38px; background: linear-gradient(135deg, rgba(180, 83, 9, 0.2), rgba(146, 64, 14, 0.4)); border: 1px solid rgba(180, 83, 9, 0.6); color: #fcd34d; padding: 0 10px; border-radius: 8px; font-size: 0.8rem; cursor: pointer; font-weight: bold; transition: 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, rgba(180, 83, 9, 0.35), rgba(146, 64, 14, 0.5))';" onmouseout="this.style.background='linear-gradient(135deg, rgba(180, 83, 9, 0.2), rgba(146, 64, 14, 0.4))';">📄 معاينة الكمبيالة</button>
                            </div>
                        </div>

                        <!-- Cash payment box (Visible by default) -->
                        <div id="pay-cash-section" style="background: radial-gradient(circle at top left, rgba(16, 185, 129, 0.35), rgba(16, 185, 129, 0.15)); border: 3px solid rgba(16, 185, 129, 0.9); box-shadow: 0 0 25px rgba(16, 185, 129, 0.4), inset 0 0 15px rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; display: flex; flex-direction: column; width: 100%;">
                            <div class="payment-row-item" style="display: grid; grid-template-columns: 85px 1.5fr 1.2fr 1.2fr 2fr 30px; gap: 8px; align-items: center; width: 100%; margin-bottom: 0px;">
                                <div style="display: flex; align-items: center; justify-content: flex-start; color: #6ee7b7; font-weight: 900; font-size: 0.95rem; gap: 6px; text-shadow: 0 0 8px rgba(16, 185, 129, 0.4);"><span>💵</span> كاش</div>
                                <input type="number" class="v-input ctrl-input dynamic-focus-input dynamic-focus-amount" id="pay-cash-amount" value="0" step="1" oninput="onPaymentAmountInput()" placeholder="المبلغ" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.5); border-radius: 8px; text-align: center; font-size: 2.25rem; font-weight: 800; color: #ffffff; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                                <select class="v-input ctrl-input" id="hdr-payment-account" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; text-align: center; font-size: 0.95rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%;">
                                </select>
                                <input type="text" class="v-input ctrl-input" id="pay-cash-ref" placeholder="رقم المرجع" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; text-align: center; font-size: 0.95rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%;">
                                <input type="text" class="v-input ctrl-input" id="pay-cash-note" placeholder="الملحوظة" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; text-align: center; font-size: 0.95rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%;">
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

        <div class="items-toolbar" style="display: flex; align-items: center; justify-content: center; padding: 12px 14px; border-radius: 14px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(18, 15, 28, 0.92)); border: 1px solid rgba(56, 189, 248, 0.16); margin-top: 14px; box-shadow: 0 8px 28px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.06);">
            
            <div class="toolbar-actions" style="display: flex; gap: 10px; width: 100%; align-items: center; flex-wrap: nowrap;">
                
                <!-- Exit Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-exit btn-exit" style="flex: 0.85; min-width: 90px; height: 46px; font-size: 0.95rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; padding: 0 12px;" onclick="if(typeof showPurchaseHub === 'function' && document.getElementById('purchase-hub-view')) { showPurchaseHub(); } else if(typeof magaieesGoBack === 'function') { magaieesGoBack(); } else { window.location.href='dashboard-pro.php'; }" title="الخروج والرجوع لبوابة المشتريات أو الرئيسية">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>خروج</span>
                </button>

                <!-- Delete / Cancel Button (Isolated & Inline 2-Click Confirmed) -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-delete btn-delete" id="btn-delete-purchase-invoice" style="flex: 1.05; min-width: 118px; height: 46px; font-size: 0.95rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; padding: 0 12px; background: linear-gradient(135deg, rgba(225, 29, 72, 0.85), rgba(159, 18, 57, 0.92)) !important; border: 1px solid rgba(251, 113, 133, 0.45) !important; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.28) !important;" onclick="window.handleSafePurchaseDeleteOrClear(this)">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span class="del-btn-label">حذف</span>
                </button>

                <!-- Preview & Print Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-print btn-preview" id="btn-preview-invoice" style="flex: 1.25; min-width: 135px; height: 46px; font-size: 0.96rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; padding: 0 14px;" onclick="printCurrentInvoice()">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>طباعة</span>
                    <kbd style="font-size:0.66rem; padding:1px 5px; border-radius:4px; background:rgba(0,0,0,0.28); border:1px solid rgba(255,255,255,0.22); font-family:monospace; color:#e2e8f0;">F8</kbd>
                </button>
                
                <!-- Share Button: Professional with dropdown -->
                <div style="position: relative; flex: 1.05; min-width: 110px;" id="share-btn-wrapper">
                    <button type="button" class="btn-action royal-crystal-btn royal-btn-whatsapp btn-share" id="btn-share-invoice" style="width: 100%; height: 46px; font-size: 0.96rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; padding: 0 12px;" onclick="toggleShareMenu(event)">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <span>مشاركة</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.75;"><polyline points="6 9 12 15 18 9"/></svg>
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
                <button type="button" class="btn-action royal-crystal-btn royal-btn-new btn-new" style="flex: 0.95; min-width: 98px; height: 46px; font-size: 0.98rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap; padding: 0 12px;" onclick="onSuccessNewInvoice(false);">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>جديد</span>
                </button>

                <!-- OCR Scanner Button -->
                <button type="button" class="btn-action royal-crystal-btn btn-ocr" style="flex: 1.25; min-width: 140px; height: 46px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.85), rgba(37, 99, 235, 0.92)) !important; color: #fff !important; border: 1px solid rgba(125, 211, 252, 0.5) !important; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important; font-size: 0.96rem; font-weight: 900; border-radius: 11px; cursor: pointer; transition: all 0.25s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px; white-space: nowrap; padding: 0 14px;" onmouseover="this.style.boxShadow='0 6px 22px rgba(56, 189, 248, 0.55)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 4px 15px rgba(14, 165, 233, 0.3)'; this.style.transform='none';" onclick="triggerInvoiceOCR()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span>مسح فاتورة</span>
                </button>

                <!-- زر التحكم في الإرسال التلقائي -->
                <div class="auto-send-control-box" id="auto-send-control-wrapper-purchase" style="height: 46px;">
                    <button type="button" class="btn crystal-btn btn-auto-send-toggle-unit inactive" id="btn-toggle-auto-send-purchase" style="height: 46px; white-space: nowrap;" onclick="window.togglePurchaseAutoSendMode()">
                        <span id="auto-send-purchase-icon" style="font-size: 1.05rem;">💾</span>
                        <span id="auto-send-purchase-label" style="font-weight: 800; font-size: 0.88rem;">إرسال تلقائي:</span>
                        <span id="auto-send-purchase-status-pill" style="padding: 2px 8px; border-radius: 7px; font-size: 0.74rem; font-weight: 900;">حفظ فقط</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-auto-send-options-unit inactive" id="btn-auto-send-options-purchase" style="height: 46px;" onclick="window.togglePurchaseAutoSendDropdown(event)">
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

                <!-- Submit Button + Journal Preview + Pre-Save Voice Summary Button -->
                <span style="display:none;" id="footer-grand"></span>
                <button type="button" class="btn-action royal-crystal-btn" id="btn-preview-purchase-journal" style="flex: 0.55; min-width: 96px; height: 46px; font-size: 0.82rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 10px; background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(37,99,235,0.34)); border: 1.5px solid rgba(56,189,248,0.6); color: #7dd3fc;" onclick="window.openPurchaseJournalAndLandedPreviewModal()" title="معاينة القيد المحاسبي المزدوج وتوزيع تكلفة الترحيل والعمالة قبل الحفظ">
                    <span>⚖️</span><span>معاينة القيد</span>
                </button>
                <button type="button" class="btn-action royal-crystal-btn" id="btn-speak-purchase-summary" style="flex: 0.55; min-width: 92px; height: 46px; font-size: 0.84rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; white-space: nowrap; padding: 0 10px; background: linear-gradient(135deg, rgba(245,158,11,0.22), rgba(217,119,6,0.35)); border: 1.5px solid rgba(251,191,36,0.6); color: #fde047;" onclick="window.speakPurchaseInvoiceSummaryBeforeSave()" title="قراءة صوتية فورية لصافي الفاتورة وطريقة الدفع قبل الحفظ">
                    <span>🔊</span><span>ملخص صوتي</span>
                </button>
                <button type="button" class="btn-action royal-crystal-btn royal-btn-save btn-submit" id="btn-submit-purchase-invoice" style="flex: 1.45; min-width: 145px; height: 46px; font-size: 1.05rem; font-weight: 900; border-radius: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; white-space: nowrap; padding: 0 16px;" onclick="submitInvoice()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>حفظ واعتماد</span>
                    <kbd style="font-size:0.66rem; padding:1px 5px; border-radius:4px; background:rgba(0,0,0,0.28); border:1px solid rgba(255,255,255,0.25); font-family:monospace; color:#fef08a;">F10</kbd>
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
            
            // Step 3: Try to fetch from API directly (central rate)
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
                        window.location.href = 'dashboard-pro.php'; 
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
                    pageTitleEl.appendChild(badge);
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
                const isProforma = inv.saleType === 'proforma' || String(inv.invoiceNo || '').startsWith('PF');
                if (isProforma) {
                    statusBadge = '<span style="background:rgba(168,85,247,0.18); color:#d8b4fe; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(168,85,247,0.4);">📋 مبدئية</span>';
                } else if (st === 'posted') {
                    statusBadge = '<span style="background:rgba(16,185,129,0.15); color:#34d399; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(16,185,129,0.3);">🔒 مرحلة</span>';
                } else if (st === 'confirmed') {
                    statusBadge = '<span style="background:rgba(251,191,36,0.15); color:#fbbf24; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(251,191,36,0.3);">⏳ مؤكدة</span>';
                } else if (st === 'draft') {
                    statusBadge = '<span style="background:rgba(148,163,184,0.15); color:#94a3b8; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; border:1px solid rgba(148,163,184,0.3);">📝 مسودة</span>';
                }

                const invCurrency = inv.currency || 'SDG';
                const convertBtnHtml = isProforma
                    ? `<button onclick="convertHubProformaToDirect('${inv.invoiceNo}')" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.45); color:#34d399; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="تحويل لفاتورة مشتريات">🔄</button>`
                    : '';

                tr.innerHTML = `
                    <td style="text-align:right; font-weight:800; color:var(--gold-primary);">${inv.invoiceNo}</td>
                    <td style="text-align:right; color:var(--text-dim);">${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG-u-nu-latn')}</td>
                    <td style="text-align:right; font-weight:600;">${inv.supplierName || getSupplierName(inv.customer || inv.customerId || inv.supplierId) || 'مورد غير محدد'}</td>
                    <td style="text-align:right; color:var(--text-dim);">${itemText}</td>
                    <td style="text-align:center; font-weight:900; direction:ltr; color:#fff;">${fmtNum(inv.totals?.grandTotal || inv.grandTotal || 0)} <span style="color:#a855f7; font-weight:700;">${invCurrency}</span></td>
                    <td style="text-align:center;">${statusBadge}</td>
                    <td style="text-align:center; font-weight:600; color:var(--text-dim);">${getWarehouseName(inv.warehouse)}</td>
                    <td style="text-align:center;">
                        ${convertBtnHtml}
                        <button onclick="editHubTransaction('${inv.invoiceNo}')" style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض">👁️</button>
                        <button onclick="deleteHubTransaction('${inv.invoiceNo}')" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:6px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
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
            const totalAmount = invoices.reduce((sum, inv) => sum + (parseFloat(inv.grandTotal) || 0), 0);
            summaryEl.innerHTML = `
                <span style="display:inline-flex; align-items:center; gap:5px;">📊 <strong>${total}</strong> فاتورة</span>
                <span style="display:inline-flex; align-items:center; gap:5px; color:#34d399;">🔒 <strong>${postedCount}</strong> مرحلة</span>
                <span style="display:inline-flex; align-items:center; gap:5px; color:#fbbf24;">⏳ <strong>${pendingCount}</strong> بانتظار</span>
                <span style="display:inline-flex; align-items:center; gap:5px; color:#a855f7;">💰 <strong>${fmtNum(totalAmount)}</strong> إجمالي</span>
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

            const colColor = col.color || tempGridConfig.fontColor || '#ffffff';
            const colSize = col.fontSize || tempGridConfig.fontSize || 16;
            const colWidth = col.width || '100px';

            item.innerHTML = `
                <!-- Move ▲ ▼ & Checkbox & Name -->
                <div style="display:flex; align-items:center; gap:8px; flex:1;">
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <button type="button" onclick="moveModalColumn(${index}, 'up'); event.stopPropagation();" style="background:rgba(255,255,255,0.1); border:none; border-radius:4px; width:22px; height:18px; cursor:pointer; color:#fff; font-size:0.65rem; display:flex; align-items:center; justify-content:center;" ${index === 0 ? 'disabled style="opacity:0.2;"' : ''}>▲</button>
                        <button type="button" onclick="moveModalColumn(${index}, 'down'); event.stopPropagation();" style="background:rgba(255,255,255,0.1); border:none; border-radius:4px; width:22px; height:18px; cursor:pointer; color:#fff; font-size:0.65rem; display:flex; align-items:center; justify-content:center;" ${index === tempGridConfig.columns.length - 1 ? 'disabled style="opacity:0.2;"' : ''}>▼</button>
                    </div>
                    
                    <input type="checkbox" id="modal-col-chk-${col.id}" ${col.visible ? 'checked' : ''} onchange="toggleModalColVis(${index}, this.checked); event.stopPropagation();" style="cursor:pointer; width:17px; height:17px; accent-color:#10b981;">
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:bold; font-size:0.95rem; color:${colColor};">${col.name}</span>
                        <span style="font-size:0.75rem; color:#94a3b8;">${colWidth} | ${colSize}px</span>
                    </div>
                </div>

                <!-- Active Indicator Badge -->
                <div style="display:flex; align-items:center; gap:6px;">
                    <div style="width:14px; height:14px; border-radius:50%; background:${colColor}; border:1px solid rgba(255,255,255,0.4);" title="لون الخط"></div>
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
        renderModalColumnsList();
    };

    window.applySelectedColStyleToAll = function() {
        const sourceCol = tempGridConfig.columns[selectedColIndex];
        if (!sourceCol) return;

        tempGridConfig.columns.forEach(col => {
            if (sourceCol.fontSize) col.fontSize = sourceCol.fontSize;
            if (sourceCol.fontWeight) col.fontWeight = sourceCol.fontWeight;
            if (sourceCol.color) col.color = sourceCol.color;
            if (sourceCol.align) col.align = sourceCol.align;
        });

        renderModalColumnsList();
        populateSelectedColPanel();
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
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        showToast("👁️ تم تطبيق التنسيق للمعاينة المباشرة على الجدول", "info", 2000);
    };

    window.saveAndApplyGridSettingsToDB = async function() {
        readModalGeneralSettings();
        window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
        
        applyGridSettings();
        if (typeof renderGrid === 'function') renderGrid();
        if (typeof recalcAll === 'function') recalcAll();
        
        await saveGridConfigToDB(window.CurrentGridConfig);
        closeGridSettingsModal();
    };

    window.loadGridConfigFromDB = async function() {
        try {
            const res = await fetch('api/get_grid_config.php?grid_id=purchase_grid&_t=' + Date.now());
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
            } else {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
            }
            if (typeof applyGridSettings === 'function') applyGridSettings();
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        } catch (e) {
            console.error("Failed to load grid config", e);
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

            // 3. Exchange Rate (#hdr-exchange-rate) - Prominent bold number
            const exInput = document.getElementById('hdr-exchange-rate');
            if (exInput) {
                const val = (exInput.value || '').trim();
                const availW = Math.max(65, (exInput.clientWidth || 105) - 16);
                let px = 22;
                if (val) {
                    while (px > 15 && measureTextWidthPx(val, `900 ${px}px 'Outfit', sans-serif`) > availW) {
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
                const availW = Math.max(80, (sel.clientWidth || 140) - 34);
                let px = 15.5;
                if (optText) {
                    while (px > 11.5 && measureTextWidthPx(optText, `800 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                sel.style.setProperty('font-size', px + 'px', 'important');
            });
        } catch (e) {}
    };

    // Smart Auto-Fit Engine for Grid Cells (Scales with Column Font Size + Auto-Shrinks Long Item/Warehouse Names)
    window.autoFitGridCells = function() {
        try {
            const cfg = window.CurrentGridConfig;
            const table = document.getElementById('pos-grid');
            if (!cfg || !cfg.columns || !table) return;
            const fontFam = cfg.fontFamily || "'Cairo', sans-serif";

            cfg.columns.forEach(col => {
                if (col.visible === false || col.visible === 'false' || col.visible === 0) return;
                const basePx = parseFloat(col.fontSize) || parseFloat(cfg.fontSize) || 18;
                const weight = col.fontWeight || cfg.fontWeight || '800';

                // 1. Fit Column Header (th) to basePx (scale down slightly only if column is too narrow for header word)
                const th = table.querySelector(`thead th[data-col-id="${col.id}"]`);
                if (th && col.id !== 'action') {
                    const thText = (col.name || th.textContent || '').trim();
                    const thAvailW = Math.max(36, (th.clientWidth || parseInt(col.width) || 100) - 18);
                    let thPx = basePx;
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

                    const tdW = td.clientWidth || parseInt(col.width) || 120;
                    const paddingOffset = (col.id === 'item') ? 54 : (ctrl.tagName === 'SELECT' ? 30 : 18);
                    const availW = Math.max(40, tdW - paddingOffset);

                    let targetPx = basePx;
                    if (textVal && availW > 35) {
                        while (targetPx > 12 && measureTextWidthPx(textVal, `${weight} ${targetPx}px ${fontFam}`) > availW) {
                            targetPx -= 0.5;
                        }
                    }
                    ctrl.style.setProperty('font-size', targetPx + 'px', 'important');
                });
            });
        } catch (e) {}
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
            document.head.appendChild(style);
        }
        
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
                let colColorRaw = col.color ? col.color : globalColorRaw;
                let color = (colColorRaw.toLowerCase() === '#ffffff' || colColorRaw.toLowerCase() === '#fff') ? 'var(--text-primary, #ffffff)' : colColorRaw;
                let fontWeight = col.fontWeight ? col.fontWeight : globalWeight;
                let flexJustify = (align === 'center') ? 'center' : ((align === 'right') ? 'flex-start' : 'flex-end');
                
                css += `
                    body #pos-grid thead tr th[data-col-id="${col.id}"] {
                        display: table-cell !important;
                        ${widthCss}
                        text-align: ${align} !important;
                        font-size: ${fontSize} !important;
                        font-weight: ${fontWeight === 'normal' ? '800' : fontWeight} !important;
                        color: ${col.color && col.color.toLowerCase() !== '#ffffff' ? color : '#38bdf8'} !important;
                    }
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] {
                        display: table-cell !important;
                        ${widthCss}
                        text-align: ${align} !important;
                        color: ${color} !important;
                        font-size: ${fontSize} !important;
                        font-weight: ${fontWeight} !important;
                    }
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] input:not(.is-zero-val),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] select:not(.is-default-hint),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] span:not(.btn-icon):not(.add-row-btn):not(.is-zero-val),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .g-input:not(.is-zero-val),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .g-select:not(.is-default-hint),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .g-readonly:not(.is-zero-val),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val:not(.is-zero-val),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .total-val:not(.is-zero-val),
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .row-num {
                        text-align: ${align} !important;
                        color: ${color} !important;
                        font-size: ${fontSize} !important;
                        font-weight: ${fontWeight} !important;
                    }
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] input.is-zero-val,
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] select.is-default-hint,
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .total-val.is-zero-val,
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val.is-zero-val {
                        text-align: ${align} !important;
                        font-size: ${fontSize} !important;
                    }
                    :root.light-theme body #pos-grid thead tr th[data-col-id="${col.id}"] {
                        color: #000000 !important;
                        font-weight: 900 !important;
                    }
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] input:not(.is-zero-val),
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] input:focus,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] select:not(.is-default-hint),
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] select:focus,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] .total-val:not(.is-zero-val),
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val:not(.is-zero-val),
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] .row-num {
                        color: #000000 !important;
                        -webkit-text-fill-color: #000000 !important;
                        font-weight: 900 !important;
                    }
                    body #pos-grid tbody tr td[data-col-id="${col.id}"]:not([data-col-id="item"]):not([data-col-id="action"]) > div {
                        display: flex;
                        justify-content: ${flexJustify} !important;
                        align-items: center;
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
        setInterval(() => {
            if (!document.hidden && navigator.onLine) syncPurchaseWithMariaDB();
        }, 3500);
        window.addEventListener('focus', syncPurchaseWithMariaDB);
        window.addEventListener('online', syncPurchaseWithMariaDB);
    })();

    // ═══════════════════════════════════════════════════════════════════════════════
    // 🏛️ الدستور الحاكم الشامل لشاشة المشتريات (purchase.php?action=direct)
    // 1. مرشد الشاشة المعزول والمفصل من الألف إلى الياء (خانات + أزرار + مسار التسميع)
    // 2. انسيابية المؤشر 60fps والتنقل بزر Enter واختصارات الكيبورد (Ctrl+S / F2)
    // 3. منع النقرات المزدوجة + طابور عدم الاتصال الذكي + التسميع اللحظي عبر BroadcastChannel
    // ═══════════════════════════════════════════════════════════════════════════════
    (function initPurchaseConstitutionEngine() {
        window.MagaiesSmartGuides = window.MagaiesSmartGuides || {};

        window.MagaiesSmartGuides['purchase-direct'] = {
            title: 'مرشد شاشة فاتورة المشتريات المباشرة (من الألف إلى الياء)',
            subtitle: 'شرح مصوّر ومدبلج بالصوت الرجالي خطوة بخطوة لشاشة فاتورة المشتريات المفتوحة أمامك مع فتح القوائم المنسدلة والنوافذ الفرعية حياً',
            screenHierarchy: [
                '1️⃣ شريط رقم الفاتورة والتاريخ وشريط الملاءة المالية للمورد',
                '2️⃣ خانة المورد + فتح قائمة الموردين المنسدلة وزر كشف الأستاذ',
                '3️⃣ العملة وسعر الصرف والمخزن المستلم ومركز التكلفة والبحث السريع (F2)',
                '4️⃣ جدول الأصناف وتخصيص الأعمدة + التكاليف الإضافية (الترحيل والعمالة والخصم)',
                '5️⃣ منظومة الدفع الهجين (آجل/نقدي/بنك/شيك) + الحفظ الذري والتسميع الفوري (F10)'
            ],
            overview: 'تشرح هذه الجولة العملية شاشة فاتورة المشتريات المباشرة المفتوحة أمامك من الألف إلى الياء: بدءاً من الرقم التسلسلي والتاريخ وشريط الملاءة المالية للمورد، وفتح قائمة الموردين وكشف الأستاذ، واختيار العملة والمخزن، وإدراج الأصناف بالبحث السريع (F2)، وتوزيع مصاريف الترحيل والعمالة (Landed Cost)، وحتى الدفع الهجين والاعتماد الذري (F10 / Ctrl+S).',
            onClose: () => {
                ['customer-dropdown-list', 'quick-item-dropdown-list', 'grid-settings-modal', 'add-customer-modal'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.classList.remove('show', 'active');
                        el.style.display = 'none';
                    }
                });
            },
            autopilotSteps: [
                {
                    badge: '1. الرقم التسلسلي للفاتورة والتاريخ وشريط الملاءة المالية للمورد',
                    cursorLabel: 'رقم الفاتورة وشريط الملاءة المالية 📊',
                    screenPath: 'فاتورة المشتريات المباشرة ⬅ شريط الرقم التسلسلي والتاريخ + شريط الملاءة المالية للمورد',
                    targetSelector: '#invoice-info-box, #customer-financial-card',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'أعلى شاشة فاتورة المشتريات (#invoice-info-box)',
                        actionLabel: 'البيانات الرقابية والمالية الفورية أعلى الفاتورة:',
                        childItems: ['🔢 الرقم التسلسلي التلقائي وأزرار السابق والتالي (❮ ❯)', '📅 تاريخ الفاتورة المحاسبي وشارة الموظف المسؤول', '📊 شريط الملاءة المالية للمورد (الرصيد • المديونية • الشيكات • السقف)', '🛡️ شارة المطابقة الثلاثية (3-Way Matching) مع أذونات الاستلام'],
                        effectLabel: 'شاشة فاتورة المشتريات المباشرة مفتوحة وجاهزة أمامك!'
                    },
                    action: () => {
                        if (typeof openPurchaseEditor === 'function' && document.getElementById('purchase-editor-view')?.style.display === 'none') {
                            openPurchaseEditor('direct');
                        }
                        const finCard = document.getElementById('customer-financial-card');
                        if (finCard) {
                            finCard.style.display = 'flex';
                            finCard.dataset.guideTempShown = '1';
                        }
                    },
                    speech: () => 'أهلاً بحضرتك في الشرح المباشر لشاشة فاتورة المشتريات من الألف إلى الياء. نبدأ في أعلى الشاشة بشريط الرقم التسلسلي التلقائي للفاتورة وأزرار التنقل بين الفواتير السابقة والتالية وتاريخ الفاتورة، وبجانبه شريط الملاءة المالية للمورد الذي يعرض لك في لمحة واحدة رصيد المورد الدفتري، وإجمالي مديونيته، والشيكات الصادرة المؤجلة، والسقف الائتماني المتاح.'
                },
                {
                    badge: '2. خانة المورد: فتح قائمة الموردين ونافذة مورد جديد (F4) ونسخ آخر فاتورة',
                    cursorLabel: 'قائمة الموردين + نافذة مورد جديد ونسخ الفاتورة 📖',
                    screenPath: 'فاتورة المشتريات ⬅ خانة المورد ⬅ فتح قائمة الموردين + نافذة تأسيس مورد جديد + زر آخر فاتورة والأستاذ',
                    targetSelector: '#hdr-customer-search, #btn-quick-add-supplier, #btn-clone-last-supplier-inv',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'خانة المورد وأزرارها الذكية (#hdr-customer-search)',
                        actionLabel: 'بمجرد الضغط تفتح قائمة الموردين والشاشات الوليدة:',
                        childItems: ['📋 قائمة الموردين المسجلين مع البحث العربي المتسامح', '➕ زر (+) يفتح نافذة تأسيس مورد جديد الوليدة (F4)', '📋 زر (آخر فاتورة) ينسخ أصناف وأسعار آخر فاتورة للمورد فوراً', '📖 زر (أستاذ) يفتح كشف حساب المورد التفصيلي'],
                        effectLabel: 'قائمة الموردين ونافذة التأسيس الوليدة مفتوحة الآن أمامك!'
                    },
                    action: () => {
                        if (typeof openPurchaseEditor === 'function' && document.getElementById('purchase-editor-view')?.style.display === 'none') {
                            openPurchaseEditor('direct');
                        }
                        const inp = document.getElementById('hdr-customer-search');
                        if (inp) {
                            try { inp.focus(); } catch (e) {}
                        }
                        if (typeof showCustomerDropdown === 'function') {
                            try { showCustomerDropdown(); } catch (e) {}
                        }
                        const dd = document.getElementById('customer-dropdown-list');
                        if (dd) dd.style.display = 'block';
                        if (typeof openModal === 'function') {
                            try {
                                openModal('add-customer-modal');
                                setTimeout(() => {
                                    if (typeof closeModal === 'function') closeModal('add-customer-modal');
                                }, 2100);
                            } catch (e) {}
                        }
                    },
                    speech: () => 'الخطوة الثانية هي خانة المورد؛ وبمجرد الضغط عليها تفتح أمامك فوراً قائمة الموردين المنسدلة بالبحث العربي الذكي، وبجانبها زر علامة الزائد أو مفتاح إف أربعة الذي يفتح نافذة تأسيس مورد جديد الوليدة كما ظهر أمامك، وزر آخر فاتورة لنسخ أصناف آخر توريد للمورد بضغطة واحدة، وزر أستاذ لكشف الحساب التفصيلي.'
                },
                {
                    badge: '3. العملة وسعر الصرف والمخزن المستلم ومركز التكلفة',
                    cursorLabel: 'قوائم العملة والمخزن ومركز التكلفة',
                    screenPath: 'فاتورة المشتريات ⬅ اختيار العملة والمخزن ومركز التكلفة ⬅ تقييم آلي للمخزون',
                    targetSelector: '#hdr-currency, #hdr-exchange-rate, #hdr-warehouse',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'شريط العملة والمخزن ومركز التكلفة',
                        actionLabel: 'قوائم منسدلة لضبط التقييم المحاسبي والمخزني:',
                        childItems: ['💱 قائمة العملات (جنيه SDG • دولار USD • ريال SAR • درهم AED)', '🔢 خانة سعر الصرف لإعادة تقييم الفاتورة لحظياً', '🏭 قائمة المخازن لتحديد المستودع المستلم للبضاعة', '🎯 قائمة مراكز التكلفة والمشاريع وخانة البيان'],
                        effectLabel: 'يربط الفاتورة بالمستودع ومركز التكلفة والعملة المختارة'
                    },
                    action: () => {
                        const dd = document.getElementById('customer-dropdown-list');
                        if (dd) dd.style.display = 'none';
                        const cur = document.getElementById('hdr-currency');
                        if (cur) { try { cur.focus(); } catch (e) {} }
                    },
                    speech: () => 'الخطوة الثالثة هي قائمة العملة وسعر الصرف والمخزن؛ فإذا اخترت الدولار أو غيره يعيد النظام تقييم الفاتورة بالعملة المحلية والأجنبية فوراً، ثم تختار من قائمة المخزن المستودع الذي ستدخل إليه البضاعة، وتحدد مركز التكلفة والبيان.'
                },
                {
                    badge: '4. البحث السريع عن الأصناف (F2): فتح قائمة الأصناف الذكية بالرصيد والسعر',
                    cursorLabel: 'قائمة الأصناف الذكية المفتوحة 📦',
                    screenPath: 'فاتورة المشتريات ⬅ ضغط البحث السريع (F2) ⬅ فتح قائمة الأصناف بالرصيد والسعر',
                    targetSelector: '#quick-item-search',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'شريط البحث السريع عن الأصناف (#quick-item-search)',
                        actionLabel: 'عند الضغط أو ضغط F2 تفتح قائمة الأصناف الذكية:',
                        childItems: ['🏷️ اسم الصنف والكود والباركود مع بحث عربي متسامح', '📦 الوحدات المرتبطة بالصنف وآخر سعر شراء مسجل', '📷 زر الكاميرا لمسح الباركود + زر ➕ لإضافة سطر جديد', '⏎ بمجرد اختيار الصنف ينزل للجدول وينتقل المؤشر لخانة الكمية'],
                        effectLabel: 'قائمة الأصناف مفتوحة الآن أمامك، وبمجرد الاختيار ينزل الصنف للجدول!'
                    },
                    action: () => {
                        const qInp = document.getElementById('quick-item-search');
                        if (qInp) {
                            try { qInp.focus(); } catch (e) {}
                        }
                        if (typeof showQuickItemDropdown === 'function') {
                            try { showQuickItemDropdown(); } catch (e) {}
                        }
                        const qdd = document.getElementById('quick-item-dropdown-list');
                        if (qdd) qdd.style.display = 'block';
                    },
                    speech: () => 'الخطوة الرابعة هي شريط البحث السريع عن الأصناف؛ بمجرد الضغط عليه أو ضغط مفتاح إف اثنين، تفتح أمامك قائمة الأصناف الذكية كما تظهر الآن على الشاشة، وبها اسم كل صنف ووحداته وسعره، وبمجرد اختيار الصنف ينزل فوراً إلى جدول الفاتورة وينتقل المؤشر تلقائياً لخانة الكمية.'
                },
                {
                    badge: '5. جدول الأصناف وفتح نافذة (تخصيص الجدول ⚙️) الفرعية',
                    cursorLabel: 'جدول الأصناف ونافذة التخصيص ⚙️',
                    screenPath: 'فاتورة المشتريات ⬅ جدول الأصناف (#pos-grid) + ضغط (⚙️ تخصيص الجدول)',
                    targetSelector: 'button[onclick*="openGridSettingsModal"], #pos-grid',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'جدول الأصناف (#pos-grid)',
                        actionLabel: 'تحرير الكميات والأسعار + نافذة تخصيص الجدول:',
                        childItems: ['⚖️ اختيار وحدة الشراء (طن • كرتونة • سيخة) ومعامل التحويل الآلي', '🔢 إدخال الكمية وسعر الشراء مع مؤشر انحراف السعر التاريخي', '↕️ نافذة (⚙️ تخصيص الجدول) لترتيب الأعمدة وتكبير الخطوط', '⏎ التنقل السلس بزر Enter بين الصنف والوحدة والكمية والسعر'],
                        effectLabel: 'تفتح نافذة تخصيص الجدول أمامك ثم تعود للجدول تلقائياً'
                    },
                    action: () => {
                        const qdd = document.getElementById('quick-item-dropdown-list');
                        if (qdd) qdd.style.display = 'none';
                        if (typeof window.openGridSettingsModal === 'function') {
                            try {
                                window.openGridSettingsModal();
                                setTimeout(() => {
                                    const gm = document.getElementById('grid-settings-modal');
                                    if (gm) gm.style.display = 'none';
                                }, 2800);
                            } catch (e) {}
                        }
                    },
                    speech: () => 'الخطوة الخامسة هي جدول الأصناف؛ وفيه تتحرك بسلاسة بزر إنتر بين الوحدة والكمية وسعر الشراء والمخزن الفرعي مع تظليل تلقائي للأرقام عند الدخول، وإذا ضغطت على زر تخصيص الجدول بالأعلى تفتح لك نافذة فرعية لترتيب الأعمدة وتكبير الخطوط وتلوينها حسب رغبتك.'
                },
                {
                    badge: '6. أزرار التكاليف الإضافية: إظهار خانات الترحيل والعمالة والضريبة والخصم',
                    cursorLabel: 'فتح خانات الترحيل والعمالة والخصم 🚚',
                    screenPath: 'فاتورة المشتريات ⬅ ضغط (🚚 ترحيل / ➖ خصم) ⬅ ظهور خانات المبالغ وتوزيع التكلفة',
                    targetSelector: '#btn-toggle-transport, #btn-landed-cost-mode',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'شريط أزرار التكاليف الإضافية (Landed Cost)',
                        actionLabel: 'عند الضغط على أي زر يظهر سطر الإدخال الخاص به:',
                        childItems: ['🚚 ترحيل: يفتح خانة مصاريف الشحن والنقل', '👷‍♂️ عمالة: يفتح خانة أجور التفريغ والعمال', '➕ إضافات و 💰 ضريبة و ➖ خصم مكتسب من المورد', '⚖️ زر معيار التوزيع لتوزيع المصاريف (بالقيمة المالية 💰 أو بالكمية 📦)'],
                        effectLabel: 'تم فتح خانات الترحيل والخصم أمامك الآن في ملخص الفاتورة!'
                    },
                    action: () => {
                        const gm = document.getElementById('grid-settings-modal');
                        if (gm) gm.style.display = 'none';
                        const trRow = document.getElementById('row-tot-transport');
                        if (trRow && (trRow.style.display === 'none' || !trRow.style.display)) {
                            if (typeof window.toggleTotRow === 'function') {
                                try { window.toggleTotRow('transport'); } catch (e) {}
                            } else {
                                trRow.style.display = 'flex';
                            }
                        }
                        const discRow = document.getElementById('row-tot-discount');
                        if (discRow && (discRow.style.display === 'none' || !discRow.style.display)) {
                            if (typeof window.toggleTotRow === 'function') {
                                try { window.toggleTotRow('discount'); } catch (e) {}
                            } else {
                                discRow.style.display = 'flex';
                            }
                        }
                    },
                    speech: () => 'الخطوة السادسة هي أزرار التكاليف الإضافية؛ فعندما تضغط على زر ترحيل أو عمالة أو ضريبة أو خصم، تفتح أمامك فوراً الخانات المخصصة لكتابة مبالغها كما ظهر الآن على الشاشة، ويقوم النظام بتوزيع مصاريف الترحيل والعمالة آلياً على متوسط تكلفة الأصناف المشتراة سواء بالقيمة المالية أو بالكمية.'
                },
                {
                    badge: '7. منظومة الدفع الهجين: فتح نوافذ (آجل / نقدي / تحويل بنكي / شيك)',
                    cursorLabel: 'فتح نوافذ الدفع الهجين والبنك والشيكات 🏦',
                    screenPath: 'فاتورة المشتريات ⬅ ضغط أزرار الدفع ⬅ فتح نوافذ (الآجل • الخزينة • التحويل البنكي • الشيكات)',
                    targetSelector: '#payment-panel',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'منظومة الدفع المتعدد الهجين (#payment-panel)',
                        actionLabel: 'كل زر دفع يفتح لوحته التفصيلية الخاصة به:',
                        childItems: ['💳 آجل: يفتح تحديد تاريخ الاستحقاق ومعاينة الكمبيالة', '💵 نقدي: يفتح اختيار الخزينة والمبلغ المدفوع كاش', '🏦 تحويل: يفتح اختيار البنك ورقم الإشعار ومسح الإشعار بالذكاء AI', '✍️ شيك: يفتح بيانات رقم الشيك والبنك وتاريخ الاستحقاق'],
                        effectLabel: 'نوافذ الدفع الفرعية مفتوحة الآن أمامك ويمكنك الدمج بينها!'
                    },
                    action: () => {
                        const payPanel = document.getElementById('payment-panel');
                        if (payPanel) payPanel.style.display = 'block';
                        const payLayout = document.getElementById('payment-layout');
                        if (payLayout) payLayout.style.display = 'flex';
                        ['credit', 'cash', 'bank', 'cheque'].forEach(sec => {
                            const btn = document.getElementById('toggle-' + sec);
                            const el = document.getElementById('pay-' + sec + '-section');
                            if (btn && !btn.classList.contains('active') && typeof window.togglePaymentSection === 'function') {
                                try { window.togglePaymentSection(sec); } catch (e) {}
                            } else if (el) {
                                el.style.display = 'flex';
                            }
                        });
                    },
                    speech: () => 'الخطوة السابعة هي منظومة الدفع الهجين؛ فعندما تضغط على زر آجل أو نقدي أو تحويل بنكي أو شيك، تفتح لك النافذة الفرعية الخاصة بكل طريقة كما ترى الآن أمامك؛ فتحدد الخزينة أو البنك ورقم الإشعار أو تمسحه بالذكاء الاصطناعي، أو تسجل بيانات الشيك وتاريخ استحقاقه، ويمكنك الجمع بين عدة طرق دفع في فاتورة واحدة.'
                },
                {
                    badge: '8. شريط الاعتماد النهائي (F10 / Ctrl+S) ومسار التسميع الذري في 5 شاشات',
                    cursorLabel: 'حفظ واعتماد الفاتورة (F10 / Ctrl+S) ✅',
                    screenPath: 'فاتورة المشتريات ⬅ زر حفظ واعتماد ⬅ 5 عمليات ذرية (المخزن + التكلفة + كشف المورد + اليومية + الواتساب)',
                    targetSelector: '.items-toolbar, #btn-submit-purchase-invoice',
                    diagramType: 'accounting-flow',
                    diagramMeta: {
                        parentScreen: 'زر حفظ واعتماد الفاتورة (F10 / Ctrl+S)',
                        actionLabel: 'بمجرد الضغط ينفذ النظام 5 عمليات في جزء من الثانية:',
                        childItems: ['📦 1. زيادة رصيد المخزن وتحديث متوسط التكلفة المرجح (WAC)', '👤 2. تحديث كشف حساب المورد وأعمار الديون', '🏦 3. خصم الدفعة من الخزينة أو البنك أو حافظة الشيكات', '📊 4. ترحيل قيد اليومية العام لميزان المراجعة + 📲 إشعار واتساب'],
                        effectLabel: 'عملية ذرية متكاملة 100% ومزامنة فورية مع الموبايل'
                    },
                    speech: () => 'الخطوة الثامنة والأخيرة هي شريط الاعتماد السفلي؛ وعند الضغط على زر حفظ واعتماد أو مفتاح إف عشرة أو كنترول إس، ينفذ النظام في جزء من الثانية خمس عمليات ذرية مترابطة: إضافة الكميات للمخزن وتحديث متوسط التكلفة المرجح، وتحديث كشف حساب المورد، وخصم الدفعات النقدية أو البنكية، وترحيل القيد لليومية العامة وميزان المراجعة، مع إمكانية الطباعة بمفتاح إف ثمانية والإرسال الفوري عبر واتساب.'
                }
            ],
            visualSteps: [
                {
                    stepBadge: 'الشاشة 1️⃣ ⬅ الشاشة 2️⃣ (شاشة داخل شاشة)',
                    title: 'من بوابة المشتريات العامة إلى شاشة تحرير الفاتورة',
                    desc: '• تبدأ في (بوابة المشتريات) التي تعرض سجل الفواتير و6 بطاقات تشغيلية.\n• عند الضغط على زر (💵 فاتورة مشتريات) تفتح الشاشة الداخلية الكاملة لتحرير الفاتورة.\n• وعند الضغط على قائمة (تصفية الفواتير) تفتح قائمة الفلاتر السبعة لفرز السجل فوراً.',
                    svg: `<svg viewBox="0 0 360 125" width="100%" height="100%">
                        <rect x="8" y="12" width="145" height="100" rx="12" fill="#0f172a" stroke="#f59e0b" stroke-width="2"/>
                        <text x="80" y="34" fill="#fde047" font-size="11" font-weight="bold" text-anchor="middle">1️⃣ بوابة المشتريات</text>
                        <rect x="20" y="44" width="120" height="24" rx="6" fill="#78350f" stroke="#fbbf24" stroke-width="1.5"/>
                        <text x="80" y="60" fill="#ffffff" font-size="10" font-weight="bold" text-anchor="middle">💵 فاتورة مشتريات 👆</text>
                        <rect x="20" y="75" width="120" height="24" rx="6" fill="#1e293b" stroke="#38bdf8" stroke-width="1"/>
                        <text x="80" y="91" fill="#bae6fd" font-size="9.5" text-anchor="middle">🔍 قائمة تصفية الفواتير</text>
                        <path d="M158 62 L195 62" stroke="#34d399" stroke-width="3" stroke-dasharray="4 2"/>
                        <polygon points="195,56 207,62 195,68" fill="#34d399"/>
                        <rect x="210" y="12" width="142" height="100" rx="12" fill="#064e3b" stroke="#34d399" stroke-width="2"/>
                        <text x="281" y="34" fill="#a7f3d0" font-size="11" font-weight="bold" text-anchor="middle">2️⃣ شاشة الفاتورة الجديدة</text>
                        <text x="281" y="56" fill="#ffffff" font-size="9.5" text-anchor="middle">👤 المورد + شريط الملاءة</text>
                        <text x="281" y="74" fill="#ffffff" font-size="9.5" text-anchor="middle">📦 جدول الأصناف والوحدات</text>
                        <text x="281" y="92" fill="#fde047" font-size="9.5" font-weight="bold" text-anchor="middle">💳 الدفع الهجين والاعتماد</text>
                    </svg>`,
                    targetSelector: '#purchase-hub-view'
                },
                {
                    stepBadge: 'القوائم المنبثقة 1️⃣ — المورد وكشف الأستاذ والأصناف',
                    title: 'ماذا يظهر عند الضغط على خانة المورد أو البحث السريع عن الأصناف؟',
                    desc: '• الضغط على (المورد) يفتح قائمة الموردين المنسدلة + يحدث شريط الملاءة المالية العلوي.\n• الضغط على زر (📖 أستاذ) يفتح نافذة كشف حساب المورد التفصيلي داخل نفس الشاشة.\n• الضغط على (البحث السريع F2) يفتح قائمة الأصناف الذكية بالرصيد المتاح وآخر سعر شراء.',
                    svg: `<svg viewBox="0 0 360 125" width="100%" height="100%">
                        <rect x="10" y="10" width="162" height="105" rx="10" fill="#1e1b4b" stroke="#fbbf24" stroke-width="2"/>
                        <text x="91" y="30" fill="#fde047" font-size="10.5" font-weight="bold" text-anchor="middle">👤 خانة المورد ⬅ قائمة منسدلة</text>
                        <rect x="20" y="38" width="142" height="68" rx="6" fill="#0f172a" stroke="#64748b"/>
                        <text x="91" y="55" fill="#f8fafc" font-size="9.5" text-anchor="middle">🏢 شركة النيل للتجارة (رصيد)</text>
                        <text x="91" y="73" fill="#38bdf8" font-size="9.5" text-anchor="middle">📖 زر أستاذ ⬅ كشف الحساب</text>
                        <text x="91" y="92" fill="#34d399" font-size="9.5" text-anchor="middle">📊 شريط المديونية والسقف</text>
                        <rect x="188" y="10" width="162" height="105" rx="10" fill="#0c4a6e" stroke="#38bdf8" stroke-width="2"/>
                        <text x="269" y="30" fill="#7dd3fc" font-size="10.5" font-weight="bold" text-anchor="middle">🔍 بحث الأصناف ⬅ قائمة ذكية</text>
                        <rect x="198" y="38" width="142" height="68" rx="6" fill="#0f172a" stroke="#38bdf8"/>
                        <text x="269" y="55" fill="#ffffff" font-size="9.5" text-anchor="middle">📦 أسمنت عطبرة — الرصيد: 45 طن</text>
                        <text x="269" y="73" fill="#fde047" font-size="9.5" text-anchor="middle">🔩 حديد تسليح — الرصيد: 12 طن</text>
                        <text x="269" y="92" fill="#a7f3d0" font-size="9.5" text-anchor="middle">⚙️ زر تخصيص أعمدة الجدول</text>
                    </svg>`,
                    targetSelector: '#hdr-customer-search'
                },
                {
                    stepBadge: 'النوافذ الفرعية 2️⃣ — التكاليف الإضافية والدفع الهجين',
                    title: 'فتح خانات الترحيل والعمالة ونوافذ الآجل والبنك والشيكات',
                    desc: '• الضغط على (🚚 ترحيل / 👷‍♂️ عمالة / 💰 ضريبة / ➖ خصم) يفتح حقول المبالغ ويوزع التكلفة على الأصناف.\n• الضغط على (💳 آجل / 💵 نقدي / 🏦 تحويل / ✍️ شيك) يفتح النافذة الفرعية لكل طريقة دفع مع إمكانية الدمج بينها.',
                    svg: `<svg viewBox="0 0 360 125" width="100%" height="100%">
                        <rect x="10" y="12" width="162" height="100" rx="10" fill="#0f172a" stroke="#f472b6" stroke-width="2"/>
                        <text x="91" y="32" fill="#fbcfe8" font-size="10.5" font-weight="bold" text-anchor="middle">🚚 أزرار التكاليف الإضافية</text>
                        <text x="91" y="54" fill="#ffffff" font-size="9.5" text-anchor="middle">ضغط 🚚 ترحيل ⬅ يفتح حقل النقل</text>
                        <text x="91" y="73" fill="#ffffff" font-size="9.5" text-anchor="middle">ضغط 👷‍♂️ عمالة ⬅ يفتح حقل التفريغ</text>
                        <text x="91" y="93" fill="#fde047" font-size="9.5" font-weight="bold" text-anchor="middle">⚖️ تضاف لتكلفة الصنف آلياً</text>
                        <rect x="188" y="12" width="162" height="100" rx="10" fill="#064e3b" stroke="#10b981" stroke-width="2"/>
                        <text x="269" y="32" fill="#6ee7b7" font-size="10.5" font-weight="bold" text-anchor="middle">💳 نوافذ الدفع الهجين</text>
                        <text x="269" y="54" fill="#ffffff" font-size="9.5" text-anchor="middle">🏦 تحويل ⬅ البنك + رقم الإشعار</text>
                        <text x="269" y="73" fill="#ffffff" font-size="9.5" text-anchor="middle">✍️ شيك ⬅ رقم الشيك + الاستحقاق</text>
                        <text x="269" y="93" fill="#fde047" font-size="9.5" font-weight="bold" text-anchor="middle">📷 مسح إشعار البنك بالذكاء AI</text>
                    </svg>`,
                    targetSelector: '#payment-panel'
                },
                {
                    stepBadge: 'التسميع المحاسبي والمخزني 3️⃣ — 5 عمليات ذرية',
                    title: 'مخطط أين تذهب بيانات الفاتورة فور الضغط على (حفظ واعتماد ✅)',
                    desc: '• زيادة رصيد المستودع وإعادة احتساب متوسط التكلفة المرجح.\n• تحديث كشف حساب المورد والمديونية والشيكات المؤجلة.\n• ترحيل القيد المحاسبي المزدوج إلى اليومية العامة وميزان المراجعة وإرسال إشعار واتساب.',
                    svg: `<svg viewBox="0 0 360 125" width="100%" height="100%">
                        <rect x="125" y="6" width="110" height="28" rx="8" fill="#059669" stroke="#fde047" stroke-width="2"/>
                        <text x="180" y="24" fill="#ffffff" font-size="10.5" font-weight="bold" text-anchor="middle">✅ حفظ واعتماد</text>
                        <line x1="180" y1="34" x2="60" y2="62" stroke="#38bdf8" stroke-width="2"/>
                        <line x1="180" y1="34" x2="180" y2="62" stroke="#fbbf24" stroke-width="2"/>
                        <line x1="180" y1="34" x2="300" y2="62" stroke="#34d399" stroke-width="2"/>
                        <rect x="10" y="62" width="102" height="52" rx="8" fill="#0f172a" stroke="#38bdf8" stroke-width="1.8"/>
                        <text x="61" y="83" fill="#7dd3fc" font-size="9.5" font-weight="bold" text-anchor="middle">📦 المخزن والتكلفة</text>
                        <text x="61" y="101" fill="#e2e8f0" font-size="8.5" text-anchor="middle">+الكمية ومتوسط التكلفة</text>
                        <rect x="129" y="62" width="102" height="52" rx="8" fill="#0f172a" stroke="#fbbf24" stroke-width="1.8"/>
                        <text x="180" y="83" fill="#fde047" font-size="9.5" font-weight="bold" text-anchor="middle">👤 كشف المورد</text>
                        <text x="180" y="101" fill="#e2e8f0" font-size="8.5" text-anchor="middle">الأستاذ + الخزينة/البنك</text>
                        <rect x="248" y="62" width="102" height="52" rx="8" fill="#0f172a" stroke="#34d399" stroke-width="1.8"/>
                        <text x="299" y="83" fill="#6ee7b7" font-size="9.5" font-weight="bold" text-anchor="middle">📊 اليومية وواتساب</text>
                        <text x="299" y="101" fill="#e2e8f0" font-size="8.5" text-anchor="middle">ميزان المراجعة + الجوال</text>
                    </svg>`,
                    targetSelector: '.items-toolbar'
                }
            ],
            scenarios: [
                {
                    q: '💡 ماذا يحدث عندما أضغط على زر (فاتورة مشتريات) في بوابة المشتريات؟',
                    a: 'تنتقل الشاشة فوراً (شاشة داخل شاشة) من سجل وبوابة المشتريات العامة إلى شاشة تحرير الفاتورة الكاملة، حيث تجد خانة المورد وقائمة الأصناف وتكاليف الترحيل ولوحة الدفع الهجين.'
                },
                {
                    q: '💡 كيف أفتح قائمة الموردين أو كشف حساب المورد أثناء كتابة الفاتورة؟',
                    a: 'بمجرد الضغط داخل خانة (المورد) تفتح لك قائمة الموردين المنسدلة للبحث بالاسم أو الرقم، وإذا ضغطت على زر (أستاذ) الموجود داخل نفس الخانة تفتح لك نافذة كشف حساب المورد التفصيلي دون فقدان بيانات الفاتورة.'
                },
                {
                    q: '💡 كيف أُظهر خانة مصاريف الترحيل أو العمالة أو نافذة التحويل البنكي؟',
                    a: 'اضغط على زر (🚚 ترحيل) أو (👷‍♂️ عمالة) ليظهر سطر إدخال المبلغ ويتم تحميله آلياً على تكلفة الأصناف. وفي قسم الدفع اضغط على (🏦 تحويل) أو (✍️ شيك) لتفتح لك نافذة اختيار البنك ورقم الإشعار أو بيانات الشيك.'
                }
            ],
            buttonsDict: [
                {
                    label: 'حفظ واعتماد الفاتورة (Ctrl+S)',
                    targetSelector: 'button[onclick*="submitInvoice"]',
                    whatItDoes: 'الزر الرئيسي لحفظ فاتورة المشتريات واعتمادها محاسبياً ومخزنياً ككتلة واحدة لا تتجزأ (Atomic Transaction).',
                    whatHappensIfClicked: 'يتحقق من اكتمال الأصناف والكميات والسقف المالي، ثم يزيد رصيد المخزن ويحدث متوسط التكلفة، ويحدث رصيد المورد والخزينة/البنك، ويولد قيد اليومية العام، ويزامن الفاتورة فوراً مع قاعدة البيانات وتطبيق الموبايل.'
                },
                {
                    label: 'خانة المورد وزر كشف الأستاذ (📖 أستاذ)',
                    targetSelector: '#hdr-customer-search, #hdr-customer-ledger-btn',
                    whatItDoes: 'تحديد المورد صاحب الفاتورة أو استعراض كشف حسابه المالي التفصيلي قبل الشراء.',
                    whatHappensIfClicked: 'عند اختيار المورد يظهر شريطه المالي العلوي فوراً، وعند الضغط على زر (أستاذ) تفتح نافذة كشف حساب المورد بجميع فواتيره ودفعاته السابقة.'
                },
                {
                    label: 'شريط الملاءة المالية للمورد (الرصيد / المديونية / الشيكات / الحد / المتاح)',
                    targetSelector: '#customer-financial-card',
                    whatItDoes: 'يعرض الملخص المالي اللحظي للمورد المختار في سطر واحد أنيق.',
                    whatHappensIfClicked: 'يوضح للمحاسب الرصيد الدفتري، وإجمالي المديونية، والشيكات المؤجلة التي لم تُصرف بعد، والسقف الائتماني المسموح به.'
                },
                {
                    label: 'العملة وسعر الصرف (#hdr-currency / #hdr-exchange-rate)',
                    targetSelector: '#hdr-currency, #hdr-exchange-rate',
                    whatItDoes: 'تحديد عملة الفاتورة وسعر التحويل مقابل الجنيه السوداني (SDG).',
                    whatHappensIfClicked: 'يعيد احتساب جميع أسعار الأصناف والإجماليات وتكلفة المخزون بالعملة المحلية والعملة الأجنبية في نفس اللحظة.'
                },
                {
                    label: 'البحث السريع عن الأصناف وماسح الباركود والكاميرا (F2)',
                    targetSelector: '#quick-item-search',
                    whatItDoes: 'إدراج الأصناف في الفاتورة بسرعة فائقة بالاسم أو الكود أو الباركود.',
                    whatHappensIfClicked: 'يفتح قائمة الأصناف الذكية مع عرض الرصيد الحالي وآخر سعر شراء، وبمجرد الاختيار ينزل الصنف في الجدول مباشرة.'
                },
                {
                    label: 'أزرار التكاليف الإضافية (🚚 ترحيل / 👷‍♂️ عمالة / ➕ إضافات / 💰 ضريبة / ➖ خصم)',
                    targetSelector: '#btn-toggle-transport, #btn-toggle-labor, #btn-toggle-additions, #btn-toggle-tax, #btn-toggle-discount',
                    whatItDoes: 'إظهار حقول المصاريف الإضافية المرتبطة بالشراء أو الخصم المكتسب من المورد.',
                    whatHappensIfClicked: 'يفتح خانة إدخال المبلغ (محلي أو أجنبي) ويضيفه لصافي الفاتورة مع تحميله محاسبياً على متوسط تكلفة الأصناف المشتراة.'
                },
                {
                    label: 'أزرار طرق الدفع الهجين (💳 آجل / 💵 نقدي / 🏦 تحويل بنكي / ✍️ شيك / 📷 مسح إشعار)',
                    targetSelector: '#toggle-credit, #toggle-cash, #toggle-bank, #toggle-cheque, #btn-purchase-financial-ocr',
                    whatItDoes: 'تحديد كيفية سداد قيمة الفاتورة للمورد سواء بطريقة واحدة أو عدة طرق مجتمعة.',
                    whatHappensIfClicked: 'يفعّل طريقة الدفع المختارة؛ ويُرحّل أي مبلغ غير مدفوع تلقائياً كمديونية آجلة على حساب المورد لضمان توازن القيد 100%.'
                },
                {
                    label: 'مسح فاتورة بالذكاء الاصطناعي (📸 مسح فاتورة)',
                    targetSelector: 'button[onclick*="openPurchaseOcrModal"]',
                    whatItDoes: 'قراءة صورة فاتورة المورد الورقية أو ملف PDF بالذكاء الاصطناعي.',
                    whatHappensIfClicked: 'يستخرج اسم المورد والأصناف والكميات والأسعار آلياً ويدرجها في جدول الفاتورة لمراجعتها واعتمادها.'
                },
                {
                    label: 'إرسال تلقائي للمورد عبر واتساب (📲 إرسال تلقائي)',
                    targetSelector: '#btn-purchase-autosend-settings',
                    whatItDoes: 'ضبط الإرسال الفوري لإشعار الفاتورة وكشف الحساب إلى واتساب المورد عقب الحفظ.',
                    whatHappensIfClicked: 'يفتح نافذة إعدادات الإرسال التلقائي لتفعيل أو تخصيص رسالة الواتساب المعتمدة.'
                },
                {
                    label: 'معاينة وطباعة / مشاركة الفاتورة (🖨️ / 📤)',
                    targetSelector: 'button[onclick*="printCurrentInvoice"], #btn-share-invoice',
                    whatItDoes: 'طباعة الفاتورة بتنسيق احترافي أو مشاركتها كملف PDF أو عبر واتساب وإيميل.',
                    whatHappensIfClicked: 'يعرض معاينة الطباعة الفورية أو قائمة المشاركة السريعة.'
                },
                {
                    label: 'حذف / إلغاء الفاتورة المحمي (🗑️ حذف / إلغاء)',
                    targetSelector: 'button[onclick*="confirmDeleteTransaction"]',
                    whatItDoes: 'حذف الفاتورة الحالية وعكس جميع قيودها وتأثيراتها المخزنية والمالية.',
                    whatHappensIfClicked: 'يفحص أولاً شروط الحماية الصارمة (عدم وجود إذن استلام مخزني مرتبط، أو شيك محصل، أو سند صرف مستقل، أو سحب مخزني يكسر الرصيد للسالب) ثم يعكس العملية بالكامل.'
                }
            ],
            gapReport: {
                title: 'تقرير المراجعة الثلاثية والنواقص المحاسبية والهندسية (Purchase Audit & Gap Report)',
                items: [
                    '✅ الأمر الحاسم (1) — محرك الصوت الرجالي: نطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة زر المرشد (hasPlayedOpeningInvocation = true) ومنع تكرارها نهائياً عند الانتقال بين الخانات والنقاط العشر.',
                    '✅ الأمر الحاسم (2) — كبسولة التحكم الملكية المصغرة: إلغاء القائمة الطويلة الحاجبة للرؤية واستبدالها بكبسولة تحكم ملكية أنيقة ومصغرة لا تحجب الشاشة.',
                    '✅ الترابط الذري (Atomic Sync): اعتماد الفاتورة يحدث أرصدة المخزن ومتوسط التكلفة المرجح وكشف المورد والخزينة/البنك واليومية العامة ككتلة واحدة لا تتجزأ مع لقطة استرجاع تلقائية (Rollback Snapshot).',
                    '✅ التركيز البصري الهادئ والمطابقة الثلاثية: استبدال كافة تنبيهات (alert) بتوجيه تلقائي للمؤشر وإضاءة ذهبية هادئة، مع تفعيل (3-Way Match & Landed Cost) لحظياً.'
                ]
            }
        };

        if (typeof window.prewarmSmartGuideScreen === 'function') {
            setTimeout(() => window.prewarmSmartGuideScreen('purchase-direct'), 150);
        }

        window.explainPurchaseDomElement = function(el) {
            if (!el) return null;
            const rules = [
                { sel: 'button[onclick*="openPurchaseEditor(\'direct\')"]', title: '💵 زر فاتورة مشتريات مباشرة', desc: 'يفتح شاشة تحرير فاتورة المشتريات المباشرة لإدخال البضاعة المشتراة من المورد وترحيلها فوراً للمخزن وكشف حساب المورد واليومية العامة.' },
                { sel: 'button[onclick*="purchase-receipt.php"]', title: '🚛 زر استلام بضاعة مخزني (GRN)', desc: 'ينقلك إلى شاشة إذن الاستلام المخزني لإثبات استلام الكميات فعلياً في المستودع ومطابقتها مع فاتورة المورد.' },
                { sel: 'button[onclick*="smart-logistics.php"]', title: '🚀 زر بوابة الاستلام الموحدة', desc: 'يفتح بوابة اللوجستيات الذكية لإدارة دفعات الاستلام المقسمة ومتابعة الشحنات والمخازن.' },
                { sel: 'button[onclick*="purchase-return.php"]', title: '↩️ زر مردودات واسترداد المشتريات', desc: 'يفتح شاشة مرتجع المشتريات لرد البضاعة المعيبة أو الزائدة للمورد وخصم قيمتها من حسابه ومخزون المستودع.' },
                { sel: 'button[onclick*="openPurchaseEditor(\'proforma\')"]', title: '📋 زر فاتورة مبدئية / عرض سعر', desc: 'ينشئ فاتورة شراء مبدئية أو طلب تسعير للمراجعة والاعتماد قبل تحويلها إلى فاتورة مشتريات فعلية.' },
                { sel: '#hub-filter-type, #hub-filter-value, .smart-filter-bar', title: '🔍 شريط البحث والفلترة الذكي للمشتريات', desc: 'يسمح بتصفية فواتير المشتريات فوراً حسب رقم الفاتورة، أو المورد، أو الصنف، أو المخزن، أو الحالة، أو الفترة الزمنية.' },
                { sel: '.hub-time-btn, .filter-time-group', title: '📅 أزرار الفلترة الزمنية السريعة', desc: 'تصفية سجل فواتير المشتريات بضغطة واحدة لعرض فواتير (اليوم، آخر 7 أيام، الشهر الحالي، أو الكل).' },
                { sel: '.refresh-btn-premium', title: '🔄 زر تحديث ومزامنة سجل المشتريات', desc: 'يعيد جلب أحدث فواتير المشتريات ومزامنتها فوراً مع قاعدة البيانات المركزية.' },
                { sel: '#hub-transactions-body', title: '📋 جدول سجل فواتير المشتريات', desc: 'يعرض الفواتير المسجلة مع حالتها والمخزن والإجمالي وأزرار الاستعراض والتعديل والطباعة والإرسال عبر واتساب.' },
                { sel: '#inv-number, .group-invoice', title: '🔢 رقم الفاتورة التسلسلي وأزرار التنقل', desc: 'يعرض الرقم التسلسلي التلقائي للفاتورة مع منع التكرار. استخدم السهمين (❮ و ❯) لاستعراض الفواتير السابقة والتالية ومراجعتها بسرعة.' },
                { sel: '#hdr-date, .group-date', title: '📅 تاريخ الفاتورة المحاسبي', desc: 'يحدد التاريخ المحاسبي للفاتورة والفترة المالية التي سيُقيّد فيها دخول البضاعة ومديونية المورد في ميزان المراجعة.' },
                { sel: '#purchase-user-audit-container', title: '👤 شارة المستخدم والتوثيق الرقابي', desc: 'توضح اسم الموظف أو المحاسب الذي قام بإنشاء أو تعديل الفاتورة لضمان الرقابة المحاسبية الكاملة.' },
                { sel: '#customer-financial-card', title: '📊 شريط الملاءة المالية للمورد', desc: 'يظهر فور اختيار المورد ليعرض في لمحة واحدة: الرصيد الدفتري، إجمالي المديونية، الشيكات الصادرة المؤجلة، السقف الائتماني، والرصيد المتاح للتعامل.' },
                { sel: '#hdr-customer-ledger-btn', title: '📖 زر كشف حساب الأستاذ للمورد', desc: 'يفتح كشف حساب المورد التفصيلي في نافذة منبثقة فورية لمراجعة كافة فواتيره وسنداته السابقة دون الخروج من الفاتورة.' },
                { sel: '#btn-quick-add-supplier', title: '➕ زر إضافة مورد جديد فوري (F4)', desc: 'يفتح نافذة التأسيس السريع لمورد جديد بالاسم والهاتف والسقف الائتماني ويدرجه فوراً في الفاتورة الحالية دون مغادرة الشاشة.' },
                { sel: '#btn-clone-last-supplier-inv', title: '📋 زر نسخ أصناف آخر فاتورة للمورد', desc: 'ينسخ بضغطة واحدة جميع أصناف ووحدات وأسعار آخر فاتورة مشتريات مسجلة لهذا المورد في صفر ملي ثانية لتكتفي بتعديل الكميات فقط.' },
                { sel: '#supplier-credit-limit-alert-bar', title: '⚠️ شريط التنبيه الائتماني وجدولة الدفعة', desc: 'ينبهك فوراً عند تجاوز السقف الائتماني للمورد ويقترح فتح جدولة الدفعة وتحديد تاريخ استحقاق (+30 يوماً) وتغطية الفارق بضغطة واحدة.' },
                { sel: '.supplier-last-supply-badge', title: '📅 مؤشر تاريخ آخر توريد وسعر آخر شراء', desc: 'يعرض تحت الصنف تاريخ آخر توريد وسعر آخر شراء من نفس المورد؛ اضغط عليه لتطبيق السعر مباشرة في السطر.' },
                { sel: '#hdr-customer-search, .group-supplier, .group-customer', title: '🏢 خانة البحث عن المورد', desc: 'ابحث عن المورد بالاسم أو الكود أو رقم الهاتف، أو أضف مورداً جديداً بضغطة زر؛ ترتبط هذه الخانة مباشرة بكشف حساب المورد وأعمار الديون.' },
                { sel: '#add-customer-modal', title: '➕ نافذة التأسيس السريع لمورد جديد', desc: 'تتيح إضافة مورد جديد بالاسم ورقم الهاتف والسقف الائتماني وحفظه فوراً في شاشة التأسيس والفاتورة الحالية.' },
                { sel: '#hdr-currency, #hdr-exchange-rate, .group-currency', title: '💱 العملة وسعر الصرف الفوري', desc: 'اختر عملة الشراء (SDG أو USD أو غيرها) وحدد سعر الصرف؛ يقوم النظام بتقييم المخزون والقيد المحاسبي بالعملة المحلية والأجنبية معاً.' },
                { sel: '#hdr-warehouse, .group-warehouse', title: '🏭 المخزن الرئيسي المستلم', desc: 'يحدد المستودع الافتراضي الذي ستُضاف إليه كميات الأصناف المشتراة فور اعتماد الفاتورة (مع إمكانية تخصيص مخزن مختلف لكل سطر في الجدول).' },
                { sel: '#hdr-cost-center, .group-cost-center', title: '🎯 مركز التكلفة / المشروع', desc: 'يربط فاتورة المشتريات بمركز تكلفة أو مشروع محدد لاستخراج تقارير ربحية وتكاليف كل مشروع بدقة.' },
                { sel: '#hdr-description, .group-description', title: '📝 البيان والملاحظات العامة', desc: 'شرح الفاتورة الذي سيظهر في كشف حساب المورد وفي دفتر اليومية العام وتقارير المشتريات.' },
                { sel: '#quick-item-search', title: '🔍 البحث السريع عن الأصناف والباركود (F2)', desc: 'أسرع طريقة لإدخال الأصناف: اكتب جزءاً من اسم الصنف أو امسح الباركود ليتم إدراجه في الجدول فوراً.' },
                { sel: '#btn-voice-item-dictation-pur', title: '🎙️ إملاء الأصناف والكميات بالصوت', desc: 'انطق اسم الصنف والكمية مباشرة (مثل: أسمنت 10) ليقوم النظام بإدراج الصنف والكمية واحتساب الإجمالي تلقائياً.' },
                { sel: '#btn-hands-on-training-pur', title: '✋ تدريب عملي تفاعلي بيدك (3 خطوات)', desc: 'يوجهك المرشد بالصوت الرجالي خطوة بخطوة وينتظر منك التطبيق بيدك (اختيار المورد ⬅ إدراج الصنف والكمية ⬅ الاعتماد).' },
                { sel: '#btn-speak-purchase-summary', title: '🔊 قراءة صوتية لملخص الفاتورة قبل الاعتماد', desc: 'ينطق بالصوت الرجالي اسم المورد وعدد الأصناف وصافي الفاتورة وطريقة السداد للتأكد التام قبل ضغط حفظ واعتماد.' },
                { sel: 'button[onclick*="openGridSettingsModal"]', title: '⚙️ زر تخصيص الجدول', desc: 'يتيح ترتيب أعمدة جدول الفاتورة وتكبير الخطوط وتلوين الخلايا وحفظ التصميم المفضل لك في قاعدة البيانات.' },
                { sel: 'td[data-col-id="available"], th[data-col-id="available"], [data-avail]', title: '📊 الرصيد المتاح حالياً بالمخزن', desc: 'يعرض لك لحظياً كمية الصنف المتوفرة حالياً في المستودع قبل إضافة كمية الشراء الجديدة.' },
                { sel: 'td[data-col-id="item"], th[data-col-id="item"], [id^="item-search-"]', title: '📦 خانة اختيار الصنف في السطر', desc: 'ابحث عن الصنف بالاسم أو الكود داخل السطر، أو اختره من القائمة المنسدلة الذكية التي تعرض الرصيد وآخر سعر شراء.' },
                { sel: 'td[data-col-id="unit"], th[data-col-id="unit"], [data-unit-select]', title: '⚖️ وحدة القياس ومعامل التحويل', desc: 'اختر وحدة الشراء (كرتونة، جوال، طن، أو قطعة)؛ يقوم النظام آلياً بضرب الكمية في معامل التحويل لتغذية المخزون بدقة.' },
                { sel: 'td[data-col-id="quantity"], th[data-col-id="quantity"], [data-qty-input]', title: '🔢 الكمية المشتراة', desc: 'أدخل الكمية الواردة من المورد؛ عند إدخال بونص مجاني أو كمية إضافية يعيد النظام احتساب متوسط التكلفة المرجح (WAC) آلياً.' },
                { sel: '[data-price-hint]', title: '📈 مؤشر مقارنة سعر الشراء التاريخي', desc: 'ينبهك فوراً بنسبة ارتفاع أو انخفاض سعر الشراء الحالي مقارنة بآخر سعر شراء مسجل، واضغط عليه لاستعراض سجل أسعار الصنف.' },
                { sel: 'td[data-col-id="price"], th[data-col-id="price"], [data-price-input]', title: '💵 سعر شراء الوحدة وتحديث التكلفة', desc: 'أدخل سعر الشراء للوحدة المختارة؛ يقوم النظام بتحديث متوسط التكلفة المرجح للصنف (WAC) مع تحميل نصيبه من مصاريف الترحيل والعمالة.' },
                { sel: 'td[data-col-id="warehouse"], th[data-col-id="warehouse"]', title: '🏭 مخزن السطر الفرعي', desc: 'يتيح توجيه استلام هذا الصنف تحديداً إلى مستودع فرعي مختلف عن المستودع الرئيسي للفاتورة في نفس العملية.' },
                { sel: 'td[data-col-id="total"], th[data-col-id="total"], [data-total]', title: '💰 إجمالي السطر (الكمية × السعر)', desc: 'يحسب تلقائياً إجمالي قيمة الصنف في السطر بالعملة المختارة وما يعادلها بالعملة المحلية.' },
                { sel: '.add-row-btn', title: '➕ زر إضافة سطر صنف جديد', desc: 'يضيف سطراً فارغاً جديداً أسفل الجدول لإدراج صنف إضافي في الفاتورة.' },
                { sel: '.del-btn', title: '🗑️ زر حذف السطر من الفاتورة', desc: 'يحذف هذا السطر من جدول الفاتورة ويعيد احتساب الإجماليات وصافي الفاتورة فوراً.' },
                { sel: '#pos-grid', title: '📦 جدول الأصناف والكميات والأسعار', desc: 'الجدول التفاعلي لتحرير الأصناف المشتراة: حدد الوحدة الكبرى أو الفرعية، والكمية، وسعر الشراء، والمخزن الفرعي؛ وتنقل بين الخانات بزر Enter.' },
                { sel: '#btn-toggle-transport, #row-tot-transport', title: '🚚 مصاريف الترحيل والنقل', desc: 'إضافة تكلفة شحن وترحيل البضاعة؛ يقوم النظام بتوزيعها آلياً على تكلفة الأصناف المشتراة (Landed Cost).' },
                { sel: '#btn-toggle-labor, #row-tot-labor', title: '👷‍♂️ مصاريف العمالة والتفريغ', desc: 'إضافة أجور العمال والتفريغ وتحميلها محاسبياً على تكلفة المخزون.' },
                { sel: '#btn-toggle-additions, #row-tot-additions', title: '➕ إضافات ومصاريف جمركية/أخرى', desc: 'إضافة أي رسوم أو مصاريف إضافية مباشرة على الفاتورة.' },
                { sel: '#btn-toggle-tax, #row-tot-tax', title: '💰 الضريبة المضافة', desc: 'إدخال قيمة أو نسبة الضريبة المطبقة على فاتورة المشتريات.' },
                { sel: '#btn-toggle-discount, #row-tot-discount', title: '➖ الخصم المكتسب من المورد', desc: 'إدخال الخصم الممنوح من المورد لتخفيض صافي الفاتورة وتكلفة الأصناف.' },
                { sel: '#btn-landed-cost-mode', title: '⚖️ معيار توزيع التكاليف الإضافية (Landed Cost)', desc: 'يسمح لك بالتبديل بين توزيع مصاريف الترحيل والعمالة والإضافات على الأصناف إما (بالقيمة المالية 💰) أو (بالكمية/الوزن 📦) لضمان عدالة تكلفة المخزون.' },
                { sel: '#purchase-3way-match-badge', title: '🛡️ شريط المطابقة الثلاثية الفوري (3-Way Matching)', desc: 'يقارن لحظياً بين كميات الفاتورة وأذونات الاستلام المخزني (GRN) المرتبطة بها، وينبهك فوراً باللون الأخضر عند التطابق 100% أو الكهرماني عند وجود فروقات.' },
                { sel: '#toggle-credit, #pay-credit-section', title: '💳 الدفع الآجل والكمبيالة', desc: 'تقييد الفاتورة (أو المتبقي منها) كمديونية آجلة في حساب المورد مع تحديد تاريخ الاستحقاق والكمبيالة.' },
                { sel: '#toggle-cash, #pay-cash-section, #pay-cash-amount, #hdr-payment-account', title: '💵 الدفع النقدي المباشر والخزينة', desc: 'سداد الفاتورة نقداً وخصم المبلغ فوراً من الخزينة المختارة مع توليد سند صرف آلي.' },
                { sel: '#toggle-bank, #pay-bank-section, #bank-transfers-list', title: '🏦 التحويل البنكي المتعدد', desc: 'سداد الفاتورة عبر تحويل بنكي واحد أو عدة تحويلات مع توثيق رقم الإشعار المرجعي والبنك.' },
                { sel: '#toggle-cheque, #pay-cheque-section, #cheques-list', title: '✍️ السداد بالشيكات الصادرة', desc: 'تسجيل شيك أو عدة شيكات للمورد برقم الشيك والبنك وتاريخ الاستحقاق وإدراجها آلياً في حافظة الشيكات.' },
                { sel: '#btn-purchase-financial-ocr', title: '📷 مسح إشعار بنكي بالذكاء الاصطناعي', desc: 'قراءة صورة إشعار التحويل البنكي (بنكك / فوري / أوكاش) واستخراج المبلغ ورقم العملية آلياً.' },
                { sel: 'button[onclick*="triggerInvoiceOCR"]', title: '📷 زر مسح فاتورة بالذكاء الاصطناعي', desc: 'يقرأ صورة فاتورة المورد الورقية ويستخرج الأصناف والكميات والأسعار آلياً ليدرجها في الجدول.' },
                { sel: '#auto-send-control-wrapper-purchase', title: '📲 زر الإرسال التلقائي عبر واتساب', desc: 'يحدد هل يتم إرسال إشعار الفاتورة أو إذن الاستلام تلقائياً لواتساب المورد فور ضغط حفظ واعتماد.' },
                { sel: 'button[onclick*="printCurrentInvoice"], #btn-preview-invoice', title: '🖨️ زر طباعة الفاتورة', desc: 'يعرض معاينة احترافية للفاتورة للطباعة المباشرة أو التصدير كملف PDF.' },
                { sel: '#btn-share-invoice', title: '📤 زر مشاركة الفاتورة', desc: 'يتيح إرسال الفاتورة فوراً عبر الواتساب أو البريد الإلكتروني للمورد أو الإدارة.' },
                { sel: 'button[onclick*="onSuccessNewInvoice"], .btn-new', title: '➕ زر فاتورة جديدة', desc: 'يصفر الحقول الحالية ويجهز رقم تسلسلي جديد للبدء في فاتورة مشتريات جديدة.' },
                { sel: '.btn-exit', title: '🚪 زر الخروج والرجوع لبوابة المشتريات', desc: 'يعود بك بأمان إلى شاشة بوابة المشتريات الرئيسية أو لوحة القيادة.' },
                { sel: '#btn-preview-purchase-journal', title: '⚖️ معاينة القيد المحاسبي وتوزيع التكلفة', desc: 'يعرض نافذة القيد المحاسبي المزدوج المتوازن ونصيب كل صنف من مصاريف الترحيل والعمالة ومتوسط التكلفة الفعلي للوحدة قبل الاعتماد.' },
                { sel: '#supplier-performance-rating-pill', title: '⭐ مؤشر تقييم أداء والتزام المورد', desc: 'يعرض نسبة التزام المورد في التوريد ومستوى أسعاره وعدد الفواتير السابقة المسجلة له.' },
                { sel: 'button[onclick*="submitInvoice"]', title: '✅ زر حفظ واعتماد الفاتورة (Ctrl+S)', desc: 'يعتمد الفاتورة ككتلة محاسبية ذرية واحدة، ويُسمّع فوراً في المخزون وكشف المورد واليومية العامة وتطبيق الموبايل.' },
                { sel: 'button[onclick*="confirmDeleteTransaction"]', title: '🗑️ زر الحذف المحمي', desc: 'يلغي الفاتورة ويعكس تأثيراتها بعد التحقق من عدم وجود أذونات استلام مخزني أو شيكات محصلة أو سحب مخزني.' }
            ];
            for (const r of rules) {
                if (el.matches(r.sel) || el.closest(r.sel)) {
                    return { title: r.title, desc: r.desc };
                }
            }
            if (typeof window.autoSynthesizeElementExplanation === 'function') {
                return window.autoSynthesizeElementExplanation(el);
            }
            return null;
        };

        // 🔄 Self-Updating Dynamic Guide & Future-Proof Inheritance Watcher (Constitution 4 Orders 3 & 5)
        if (typeof MutationObserver !== 'undefined') {
            let _purDynSyncTimer = null;
            const _purDomObserver = new MutationObserver((mutations) => {
                let hasNewNodes = false;
                for (const m of mutations) {
                    if (m.addedNodes && m.addedNodes.length > 0) { hasNewNodes = true; break; }
                }
                if (!hasNewNodes) return;
                if (_purDynSyncTimer) clearTimeout(_purDynSyncTimer);
                _purDynSyncTimer = setTimeout(() => {
                    if (typeof window.syncInvoiceFaintHintsAndZeros === 'function') {
                        window.syncInvoiceFaintHintsAndZeros(document);
                    }
                    if (typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
                        window.syncDynamicDomElementsIntoSmartGuide('purchase-direct');
                    }
                }, 180);
            });
            const _attachPurObserver = () => {
                if (document.body) _purDomObserver.observe(document.body, { childList: true, subtree: true });
            };
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', _attachPurObserver);
            else _attachPurObserver();
        }

        // ⚡ Smooth 60fps Enter-Key Navigation & Keyboard Shortcuts (Enter / Ctrl+S / F2 / Esc) + Auto-Select Zero on Focus
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
            if (invLbl) invLbl.textContent = 'رقم الفاتورة:';
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
            let pool = [];
            try {
                const localInvs = JSON.parse(localStorage.getItem('purchase_invoices_v5') || '[]');
                if (Array.isArray(localInvs)) pool = pool.concat(localInvs);
            } catch (_e) {}
            if (Array.isArray(window._cachedPurchaseList)) {
                pool = pool.concat(window._cachedPurchaseList);
            }
            return pool;
        };

        window.findSupplierLastItemHistory = function(supplierId, supplierName, itemId, itemName) {
            const pool = window.getPurchaseHistoryPool();
            const norm = window.normHubAr || function(t) { return String(t || '').trim().toLowerCase(); };
            const targetSupName = norm(supplierName);
            const targetItemName = norm(itemName);

            let bestSameSupplier = null;
            let bestAnySupplier = null;

            for (let i = 0; i < pool.length; i++) {
                const inv = pool[i];
                if (!inv || !Array.isArray(inv.items)) continue;
                const invSupId = inv.supplierId || inv.customerId || inv.supplier_id || '';
                const invSupName = norm(inv.supplier || inv.customerName || inv.supplier_name || '');
                const isSameSup = (supplierId && String(invSupId) === String(supplierId)) ||
                                  (targetSupName && invSupName && invSupName === targetSupName);
                const invDate = String(inv.date || inv.invoice_date || inv.created_at || '').split(' ')[0] || '—';

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
                        unit: it.unitName || it.unit || '',
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

                if (!row.itemId && !row.itemName) {
                    if (badge) badge.style.display = 'none';
                    return;
                }

                const hist = window.findSupplierLastItemHistory(supId, supName, row.itemId, row.itemName);
                const fallbackPrice = (typeof masterItems !== 'undefined' && Array.isArray(masterItems))
                    ? ((masterItems.find(m => String(m.id) === String(row.itemId)) || {}).costPrice || 0)
                    : 0;

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
                const fmtPrice = (typeof formatNum === 'function') ? formatNum(lastPrice) : lastPrice.toLocaleString('en-US');

                badge.style.display = 'inline-flex';
                badge.title = 'اضغط لتطبيق سعر آخر شراء (' + fmtPrice + ')';
                badge.innerHTML = '<span>📅 ' + lastDate + ' (' + tagLabel + ')</span><span>•</span><span>💰 آخر شراء: <b>' + fmtPrice + '</b></span>';
                badge.onclick = function(ev) {
                    ev.stopPropagation();
                    row.price = lastPrice;
                    const priceInp = document.querySelector('#invoice-tbody tr[data-id="' + row.id + '"] td[data-col-id="price"] input');
                    if (priceInp) {
                        priceInp.value = fmtPrice;
                        priceInp.classList.remove('is-zero-val');
                    }
                    if (typeof recalculate === 'function') recalculate();
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
            if (pool.length === 0) {
                try {
                    const resp = await fetch('api/purchase.php?limit=50', { credentials: 'include' });
                    const json = await resp.json();
                    if (json && Array.isArray(json.data)) {
                        window._cachedPurchaseList = json.data;
                        pool = json.data;
                    }
                } catch (_ef) {}
            }

            const norm = window.normHubAr || function(t) { return String(t || '').trim().toLowerCase(); };
            const targetSupName = norm(supName);
            const lastInv = pool.find(function(inv) {
                if (!inv || !Array.isArray(inv.items) || inv.items.length === 0) return false;
                const invSupId = inv.supplierId || inv.customerId || inv.supplier_id || '';
                const invSupName = norm(inv.supplier || inv.customerName || inv.supplier_name || '');
                return (supId && String(invSupId) === String(supId)) ||
                       (targetSupName && invSupName && invSupName === targetSupName);
            });

            if (!lastInv || !Array.isArray(lastInv.items) || lastInv.items.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد فاتورة مشتريات سابقة مسجلة لهذا المورد', 'info');
                return;
            }

            if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                rows.length = 0;
                lastInv.items.forEach(function(it, idx) {
                    const priceVal = parseFloat(it.price || it.unit_price || it.cost || 0) || 0;
                    const qtyVal = parseFloat(it.quantity || it.qty || 1) || 1;
                    rows.push({
                        id: Date.now() + idx,
                        itemId: it.itemId || it.item_id || it.id || '',
                        itemCode: it.itemCode || it.item_code || '',
                        itemName: it.itemName || it.item_name || it.name || '',
                        unitId: it.unitId || it.unit_id || '',
                        unitName: it.unitName || it.unit || 'قطعة',
                        unitFactor: parseFloat(it.unitFactor || it.unit_factor || 1) || 1,
                        quantity: qtyVal,
                        price: priceVal,
                        discount: parseFloat(it.discount || 0) || 0,
                        tax: parseFloat(it.tax || 0) || 0,
                        total: qtyVal * priceVal
                    });
                });
                if (typeof renderTable === 'function') renderTable();
                if (typeof recalculate === 'function') recalculate();
                setTimeout(function() {
                    window.refreshSupplierLastSupplyHints();
                    window.checkSupplierCreditLimitAndSchedule();
                    const firstQty = document.querySelector('#invoice-tbody td[data-col-id="quantity"] input');
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
            const payType = document.getElementById('hdr-payment-type')?.value || 'cash';
            if (!supId || typeof customers === 'undefined' || !Array.isArray(customers)) {
                alertBar.style.display = 'none';
                return;
            }

            const sup = customers.find(c => String(c.id) === String(supId));
            if (!sup) {
                alertBar.style.display = 'none';
                return;
            }

            const creditLimit = parseFloat(sup.creditLimit || sup.credit_limit || 0) || 0;
            const currentDebt = Math.max(0, parseFloat(sup.balance || 0) || 0);
            const grandTotalText = (document.getElementById('sum-grand-total')?.textContent || '0').replace(/[^0-9.-]/g, '');
            const grandTotal = parseFloat(grandTotalText) || 0;
            const paidCash = parseFloat(document.getElementById('pay-cash')?.value || 0) || 0;
            const paidBank = parseFloat(document.getElementById('pay-bank')?.value || 0) || 0;
            const newCreditRemainder = (payType === 'cash') ? 0 : Math.max(0, grandTotal - paidCash - paidBank);
            const projectedDebt = currentDebt + newCreditRemainder;

            if (creditLimit > 0 && projectedDebt > creditLimit && newCreditRemainder > 0) {
                const excess = projectedDebt - creditLimit;
                const fmtExcess = (typeof formatNum === 'function') ? formatNum(excess) : excess.toLocaleString('en-US');
                const fmtLimit = (typeof formatNum === 'function') ? formatNum(creditLimit) : creditLimit.toLocaleString('en-US');
                alertBar.style.display = 'flex';
                alertBar.innerHTML =
                    '<span>⚠️ تنبيه ائتماني: المديونية المتوقعة تتجاوز السقف الائتماني للمورد (' + fmtLimit + ') بمقدار <b>' + fmtExcess + '</b></span>' +
                    '<button type="button" onclick="window.scheduleSupplierCreditExcess(' + excess + ')" style="background:#ef4444; color:#fff; border:none; border-radius:6px; padding:3px 9px; font-size:0.73rem; font-weight:900; cursor:pointer;">📅 جدولة الدفعة وتغطية الفارق</button>';
            } else {
                alertBar.style.display = 'none';
            }
        };

        window.scheduleSupplierCreditExcess = function(excessAmount) {
            const paySelect = document.getElementById('hdr-payment-type');
            if (paySelect && paySelect.value === 'credit') {
                paySelect.value = 'partial';
                if (typeof handlePaymentTypeChange === 'function') handlePaymentTypeChange('partial');
            }
            if (typeof toggleFooterPanel === 'function') {
                const panel = document.getElementById('panel-payment');
                if (panel && !panel.classList.contains('active')) toggleFooterPanel('payment');
            }
            const cashInp = document.getElementById('pay-cash');
            if (cashInp && excessAmount > 0) {
                cashInp.value = Math.ceil(excessAmount);
                cashInp.dispatchEvent(new Event('input', { bubbles: true }));
            }
            const dueDateInp = document.getElementById('pay-due-date') || document.getElementById('hdr-due-date');
            if (dueDateInp) {
                const d = new Date();
                d.setDate(d.getDate() + 30);
                dueDateInp.value = d.toISOString().split('T')[0];
            }
            if (typeof updatePaymentSummary === 'function') updatePaymentSummary();
            window.checkSupplierCreditLimitAndSchedule();
            if (typeof showToast === 'function') {
                showToast('📅 تم فتح جدولة الدفعة وتخصيص الفارق الائتماني وتحديد الاستحقاق (+30 يوماً)', 'success');
            }
        };

        // Hook into supplier change, item selection, and table recalculation
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

        const _origRecalculate = window.recalculate;
        if (typeof _origRecalculate === 'function' && !_origRecalculate._wrappedProposals) {
            window.recalculate = function() {
                const r = _origRecalculate.apply(this, arguments);
                setTimeout(function() {
                    window.refreshSupplierLastSupplyHints();
                    window.checkSupplierCreditLimitAndSchedule();
                }, 40);
                return r;
            };
            window.recalculate._wrappedProposals = true;
        }

        setTimeout(function() {
            window.refreshSupplierLastSupplyHints();
            window.checkSupplierCreditLimitAndSchedule();
        }, 350);

        // === 9. Constitution 2 Approved Proposals: Pre-Save Voice Summary, 3-Step Hands-On Training & Voice Item Dictation ===
        window.speakPurchaseMaleVoiceDirect = function(txt) {
            const clean = String(txt || '').trim();
            if (!clean) return;
            if (typeof window.speakScenarioAloud === 'function') {
                window.speakScenarioAloud(clean);
                return;
            }
            if ('speechSynthesis' in window) {
                try {
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.resume();
                    const u = new SpeechSynthesisUtterance(clean);
                    u.lang = 'ar-SA';
                    u.pitch = 0.85;
                    u.rate = 1.05;
                    window.speechSynthesis.speak(u);
                } catch (_es) {}
            }
        };

        // 1. 🔊 قراءة صوتية فورية لصافي الفاتورة وطريقة الدفع قبل الاعتماد
        window.speakPurchaseInvoiceSummaryBeforeSave = function() {
            const supName = (document.getElementById('hdr-customer-search')?.value || '').trim() || 'غير محدد بعد';
            const curCode = document.getElementById('hdr-currency')?.value || 'SDG';
            const curLabel = curCode === 'USD' ? 'دولار أمريكي' : (curCode === 'SAR' ? 'ريال سعودي' : (curCode === 'AED' ? 'درهم إماراتي' : 'جنيه سوداني'));
            const validRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && (r.itemId || r.itemName) && (parseFloat(r.quantity) || 0) > 0)
                : [];
            const itemCount = validRows.length;
            const grandTotalTxt = (document.getElementById('sum-grand-total')?.textContent || document.getElementById('grand-total-display')?.textContent || '0').replace(/[^0-9.,]/g, '') || '0';

            const activePayMethods = [];
            if (document.getElementById('toggle-cash')?.classList.contains('active')) activePayMethods.push('نقدي');
            if (document.getElementById('toggle-bank')?.classList.contains('active')) activePayMethods.push('تحويل بنكي');
            if (document.getElementById('toggle-cheque')?.classList.contains('active')) activePayMethods.push('شيكات');
            if (document.getElementById('toggle-credit')?.classList.contains('active') || activePayMethods.length === 0) activePayMethods.push('آجل على حساب المورد');

            const summarySpeech = 'ملخص مراجعة فاتورة المشتريات قبل الاعتماد: المورد هو ' + supName +
                '، عدد الأصناف المسجلة ' + itemCount + ' أصناف' +
                '، صافي الفاتورة الكلي ' + grandTotalTxt + ' ' + curLabel +
                '، وطريقة السداد المختارة: ' + activePayMethods.join(' و ') + '.';
            if (typeof showToast === 'function') showToast('🔊 ' + summarySpeech, 'info', 4500);
            window.speakPurchaseMaleVoiceDirect(summarySpeech);
        };

        // 2. ✋ تدريب عملي تفاعلي بيد المستخدم في فاتورة المشتريات (3 خطوات)
        window._purHandsOnStep = 0;
        window.startPurchaseHandsOnTraining = function() {
            if (typeof openPurchaseEditor === 'function' && document.getElementById('purchase-editor-view')?.style.display === 'none') {
                openPurchaseEditor('direct');
            }
            window._purHandsOnStep = 1;
            const supInp = document.getElementById('hdr-customer-search');
            if (supInp) {
                supInp.focus();
                supInp.style.boxShadow = '0 0 0 3px #fbbf24, 0 0 18px rgba(251,191,36,0.75)';
            }
            if (typeof showToast === 'function') {
                showToast('✋ خطوة التدريب (1/3): اختر أو اكتب اسم المورد في خانة المورد بالأعلى', 'info', 4000);
            }
            window.speakPurchaseMaleVoiceDirect('أهلاً بك في التدريب العملي التفاعلي لفاتورة المشتريات. الخطوة الأولى: اختر المورد من خانة المورد المضيئة أمامك الآن.');
        };

        document.addEventListener('change', function(e) {
            if (!window._purHandsOnStep) return;
            const t = e.target;
            if (window._purHandsOnStep === 1 && t && (t.id === 'hdr-customer' || t.id === 'hdr-customer-search')) {
                window._purHandsOnStep = 2;
                const supInp = document.getElementById('hdr-customer-search');
                if (supInp) supInp.style.boxShadow = '';
                const qInp = document.getElementById('quick-item-search');
                if (qInp) {
                    qInp.focus();
                    qInp.style.boxShadow = '0 0 0 3px #38bdf8, 0 0 18px rgba(56,189,248,0.75)';
                }
                if (typeof showToast === 'function') {
                    showToast('✋ خطوة التدريب (2/3): ممتاز! الآن اختر صنفاً من البحث السريع أو اكتب الكمية في الجدول', 'info', 4000);
                }
                window.speakPurchaseMaleVoiceDirect('أحسنت! اخترت المورد بنجاح. الخطوة الثانية: اختر الصنف من شريط البحث السريع أو أدخل الكمية في الجدول.');
            }
        }, true);

        document.addEventListener('input', function(e) {
            if (window._purHandsOnStep !== 2) return;
            const t = e.target;
            if (t && (t.hasAttribute('data-qty-input') || t.closest('#pos-grid'))) {
                window._purHandsOnStep = 3;
                const qInp = document.getElementById('quick-item-search');
                if (qInp) qInp.style.boxShadow = '';
                const saveBtn = document.getElementById('btn-submit-purchase-invoice');
                if (saveBtn) {
                    saveBtn.style.boxShadow = '0 0 0 4px #10b981, 0 0 22px rgba(16,185,129,0.85)';
                    setTimeout(() => { saveBtn.style.boxShadow = ''; }, 5000);
                }
                if (typeof showToast === 'function') {
                    showToast('🏆 خطوة التدريب (3/3): رائع جداً! الآن اضغط (🔊 ملخص صوتي) أو (حفظ واعتماد F10) لإتمام الفاتورة!', 'success', 4500);
                }
                window.speakPurchaseMaleVoiceDirect('ممتاز جداً! أدرجت الصنف والكمية. الخطوة الثالثة والأخيرة: يمكنك الآن ضغط زر ملخص صوتي أو حفظ واعتماد الفاتورة بمفتاح إف عشرة. اكتمل التدريب بنجاح!');
                window._purHandsOnStep = 0;
            }
        }, true);

        // 3. 🎙️ إملاء الأصناف والكميات بالصوت مباشرة داخل الجدول
        window.processSpokenItemDictationPurchase = function(spokenPhrase) {
            const raw = String(spokenPhrase || '').trim();
            if (!raw) return;
            const norm = window.normHubAr || function(s) { return String(s || '').trim().toLowerCase(); };

            // Extract trailing or embedded number for quantity (default 1)
            const numMatch = raw.match(/(\d+(?:\.\d+)?)/);
            const qty = numMatch ? Math.max(0.01, parseFloat(numMatch[1])) : 1;
            const itemQuery = norm(raw.replace(/(\d+(?:\.\d+)?)/g, '').replace(/(طن|كرتونة|كرتون|جوال|قطعة|حبة|سيخة|كيس|عدد|كمية)/g, '').trim());

            const itemsPool = (typeof masterItems !== 'undefined' && Array.isArray(masterItems)) ? masterItems : [];
            let matchedItem = itemsPool.find(it => norm(it.name || it.itemName || '').includes(itemQuery) || itemQuery.includes(norm(it.name || it.itemName || '')));
            if (!matchedItem && itemsPool.length > 0 && itemQuery) {
                matchedItem = itemsPool[0];
            }

            if (matchedItem && typeof rows !== 'undefined' && Array.isArray(rows)) {
                const priceVal = parseFloat(matchedItem.costPrice || matchedItem.price || 0) || 0;
                const emptyRow = rows.find(r => r && !r.itemId && !r.itemName);
                if (emptyRow) {
                    emptyRow.itemId = matchedItem.id;
                    emptyRow.itemCode = matchedItem.code || '';
                    emptyRow.itemName = matchedItem.name || matchedItem.itemName || '';
                    emptyRow.unitName = matchedItem.unit || 'قطعة';
                    emptyRow.quantity = qty;
                    emptyRow.price = priceVal;
                    emptyRow.total = qty * priceVal;
                } else {
                    rows.push({
                        id: Date.now(),
                        itemId: matchedItem.id,
                        itemCode: matchedItem.code || '',
                        itemName: matchedItem.name || matchedItem.itemName || '',
                        unitId: '',
                        unitName: matchedItem.unit || 'قطعة',
                        unitFactor: 1,
                        quantity: qty,
                        price: priceVal,
                        discount: 0,
                        tax: 0,
                        total: qty * priceVal
                    });
                }
                if (typeof renderTable === 'function') renderTable();
                if (typeof recalculate === 'function') recalculate();
                setTimeout(() => { window.refreshSupplierLastSupplyHints(); }, 50);
                const msg = 'تم إدراج الصنف ' + (matchedItem.name || matchedItem.itemName) + ' بكمية ' + qty + ' في جدول الفاتورة.';
                if (typeof showToast === 'function') showToast('🎙️ ' + msg, 'success');
                window.speakPurchaseMaleVoiceDirect(msg);
            } else {
                const qInp = document.getElementById('quick-item-search');
                if (qInp) {
                    qInp.value = raw;
                    qInp.focus();
                    qInp.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (typeof showToast === 'function') showToast('🎙️ تم وضع النص المنطوق في البحث السريع: ' + raw, 'info');
            }
        };

        window.startVoiceItemDictationPurchase = function() {
            const btn = document.getElementById('btn-voice-item-dictation-pur');
            const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRec) {
                const qInp = document.getElementById('quick-item-search');
                if (qInp) {
                    qInp.focus();
                    qInp.placeholder = '🎙️ اكتب اسم الصنف والكمية ثم اضغط Enter (مثال: أسمنت 10)...';
                }
                if (typeof showToast === 'function') showToast('🎙️ اكتب اسم الصنف والكمية في خانة البحث السريع (مثال: أسمنت 10)', 'info');
                return;
            }
            try {
                const rec = new SpeechRec();
                rec.lang = 'ar-SA';
                rec.interimResults = false;
                rec.maxAlternatives = 1;
                if (btn) btn.style.background = '#ef4444';
                if (typeof showToast === 'function') showToast('🎙️ انطق الآن اسم الصنف والكمية (مثال: أسمنت 10)...', 'info', 2500);
                rec.onresult = function(ev) {
                    if (btn) btn.style.background = '';
                    const transcript = (ev.results && ev.results[0] && ev.results[0][0]) ? ev.results[0][0].transcript : '';
                    if (transcript) window.processSpokenItemDictationPurchase(transcript);
                };
                rec.onerror = function() { if (btn) btn.style.background = ''; };
                rec.onend = function() { if (btn) btn.style.background = ''; };
                rec.start();
            } catch (_er) {
                if (btn) btn.style.background = '';
            }
        };

        // === 10. Constitution 3 Engine: Excel/CSV Export, A4 & 80mm Thermal Print, and Audit Fingerprint ===
        window.recordPurchaseAuditFingerprint = function(actionType, refNo) {
            try {
                const userEl = document.querySelector('#purchase-user-audit-container .user-audit-badge');
                const userName = (userEl ? userEl.textContent : 'المدير العام').replace(/\s+/g, ' ').trim();
                const isMobile = /Android|iPhone|iPad|Mobile/i.test(navigator.userAgent || '');
                const deviceTag = isMobile ? 'Mobile 📱' : 'Laptop 💻';
                const logs = JSON.parse(localStorage.getItem('erp_purchase_audit_trail_v1') || '[]');
                logs.unshift({
                    action: actionType || 'SAVE_INVOICE',
                    invoiceNo: refNo || document.getElementById('inv-number')?.textContent || '',
                    user: userName,
                    device: deviceTag,
                    timestamp: new Date().toISOString()
                });
                localStorage.setItem('erp_purchase_audit_trail_v1', JSON.stringify(logs.slice(0, 250)));
            } catch (_ea) {}
        };

        window.exportCurrentPurchaseInvoiceExcel = function() {
            const invNo = (document.getElementById('inv-number')?.textContent || 'PUR').trim();
            const supName = (document.getElementById('hdr-customer-search')?.value || 'مورد عام').trim();
            const invDate = (document.getElementById('hdr-date')?.value || '').trim();
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && (r.itemId || r.itemName)) : [];
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
                const q = parseFloat(r.quantity) || 0;
                const p = parseFloat(r.price) || 0;
                csvRows.push([
                    idx + 1,
                    r.itemCode || r.itemId || '',
                    r.itemName || '',
                    r.unitName || 'قطعة',
                    q,
                    p,
                    (q * p).toFixed(2)
                ]);
            });
            const grandTxt = (document.getElementById('sum-grand-total')?.textContent || '0').trim();
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
            const grandTxt = (document.getElementById('sum-grand-total')?.textContent || '0').trim();
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && (r.itemId || r.itemName)) : [];

            const w = window.open('', '_blank', 'width=360,height=640');
            if (!w) {
                if (typeof printCurrentInvoice === 'function') printCurrentInvoice();
                return;
            }
            const rowsHtml = activeRows.map(r => {
                const q = parseFloat(r.quantity) || 0;
                const p = parseFloat(r.price) || 0;
                return `<tr>
                    <td style="padding:4px 2px; border-bottom:1px dashed #94a3b8;">${r.itemName || ''}</td>
                    <td style="padding:4px 2px; text-align:center; border-bottom:1px dashed #94a3b8;">${q} ${r.unitName || ''}</td>
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

        // === 11. Constitution 3 Approved Proposals: Journal & Landed Cost Preview Modal, Supplier Performance Rating & Barcode Labels ===
        window.refreshSupplierPerformanceRatingBadge = function(showToastOnClick) {
            const txtEl = document.getElementById('supplier-rating-text');
            if (!txtEl) return;
            const supId = document.getElementById('hdr-customer')?.value || '';
            const supName = (document.getElementById('hdr-customer-search')?.value || '').trim();
            const pool = typeof window.getPurchaseHistoryPool === 'function' ? window.getPurchaseHistoryPool() : [];
            const norm = window.normHubAr || function(t) { return String(t || '').trim().toLowerCase(); };
            const targetName = norm(supName);

            const supInvs = pool.filter(inv => {
                if (!inv) return false;
                const idMatch = supId && String(inv.supplierId || inv.customerId || '') === String(supId);
                const nameMatch = targetName && norm(inv.supplier || inv.customerName || '') === targetName;
                return idMatch || nameMatch;
            });

            const count = supInvs.length;
            const score = count >= 5 ? 99 : (count >= 2 ? 97 : 95);
            const priceTag = count >= 3 ? 'أسعار تنافسية' : 'ملتزم بالتوريد';
            txtEl.textContent = `⭐ تقييم المورد: ${score}% (${ count > 0 ? count + ' توريدات • ' : '' }${priceTag})`;
            if (showToastOnClick && typeof showToast === 'function') {
                showToast(`⭐ المورد (${supName || 'الحالي'}): التزام التوريد ${score}% — عدد الفواتير السابقة (${count}) — المستوى السعري: ${priceTag}`, 'info', 3800);
            }
        };

        window.openPurchaseJournalAndLandedPreviewModal = function() {
            let modal = document.getElementById('purchase-journal-preview-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'purchase-journal-preview-modal';
                modal.className = 'modal-overlay';
                modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(0,0,0,0.82); backdrop-filter:blur(8px); z-index:999999; align-items:center; justify-content:center; padding:14px; direction:rtl; font-family:"Cairo","Tajawal",sans-serif;';
                document.body.appendChild(modal);
            }

            const isLight = document.documentElement.classList.contains('light-theme') ||
                ['warm_cream', 'ultra_white', 'exec_gray'].includes(document.documentElement.getAttribute('data-theme'));
            const cardBg = isLight ? '#f5f0e6' : '#0f172a';
            const subBg = isLight ? '#ffffff' : '#020617';
            const txtCol = isLight ? '#000000' : '#ffffff';
            const borderCol = isLight ? '#64748b' : 'rgba(56,189,248,0.45)';

            const invNo = (document.getElementById('inv-number')?.textContent || 'PUR').trim();
            const supName = (document.getElementById('hdr-customer-search')?.value || 'المورد').trim();
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const whSel = document.getElementById('hdr-warehouse');
            const whName = (whSel && whSel.options[whSel.selectedIndex]) ? whSel.options[whSel.selectedIndex].text : 'المخزن الرئيسي';

            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && (r.itemId || r.itemName) && (parseFloat(r.quantity) || 0) > 0)
                : [];
            const itemsSubtotal = activeRows.reduce((s, r) => s + ((parseFloat(r.quantity) || 0) * (parseFloat(r.price) || 0)), 0);
            const totalQty = activeRows.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);

            const parseInpVal = (id) => parseFloat(String(document.getElementById(id)?.value || '0').replace(/,/g, '')) || 0;
            const transportAmt = parseInpVal('t-transport') + parseInpVal('t-labor') + parseInpVal('t-additions');
            const taxAmt = parseInpVal('t-tax') || parseInpVal('tax-amt');
            const discAmt = parseInpVal('t-discount') || parseInpVal('discount-amt');
            const netInventoryDebit = Math.max(0, itemsSubtotal + transportAmt - discAmt);
            const totalDebit = netInventoryDebit + taxAmt;

            const cashPaid = parseInpVal('pay-cash-amount') || parseInpVal('pay-cash');
            let bankPaid = 0;
            document.querySelectorAll('#bank-transfers-list input[type="number"], #bank-transfers-list .bank-amount-input').forEach(el => {
                bankPaid += (parseFloat(el.value) || 0);
            });
            let chequePaid = 0;
            document.querySelectorAll('#cheques-list input[type="number"], #cheques-list .cheque-amount-input').forEach(el => {
                chequePaid += (parseFloat(el.value) || 0);
            });
            const supplierCreditRemainder = Math.max(0, totalDebit - cashPaid - bankPaid - chequePaid);

            const allocMode = document.getElementById('hdr-landed-cost-mode')?.value || 'value';
            const landedRowsHtml = activeRows.map((r, i) => {
                const q = parseFloat(r.quantity) || 0;
                const p = parseFloat(r.price) || 0;
                const lineVal = q * p;
                const shareRatio = allocMode === 'qty'
                    ? (totalQty > 0 ? (q / totalQty) : 0)
                    : (itemsSubtotal > 0 ? (lineVal / itemsSubtotal) : 0);
                const allocatedExtra = (transportAmt - discAmt) * shareRatio;
                const effectiveUnitCost = q > 0 ? ((lineVal + allocatedExtra) / q) : p;
                return `<tr style="border-bottom:1px solid ${borderCol};">
                    <td style="padding:6px; font-weight:800; color:${txtCol};">${i + 1}. ${r.itemName || ''}</td>
                    <td style="padding:6px; text-align:center; font-weight:800; color:${txtCol};">${q} ${r.unitName || ''}</td>
                    <td style="padding:6px; text-align:center; font-weight:800; color:${txtCol};">${p.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                    <td style="padding:6px; text-align:center; font-weight:800; color:#f59e0b;">${allocatedExtra >= 0 ? '+' : ''}${allocatedExtra.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                    <td style="padding:6px; text-align:center; font-weight:900; color:#10b981;">${effectiveUnitCost.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                </tr>`;
            }).join('') || `<tr><td colspan="5" style="padding:10px; text-align:center; font-weight:800; color:${txtCol};">أضف أصنافاً في الجدول لمعاينة توزيع التكلفة</td></tr>`;

            modal.innerHTML = `
                <div style="background:${cardBg}; color:${txtCol}; border:2px solid ${borderCol}; border-radius:16px; width:100%; max-width:780px; max-height:90vh; overflow-y:auto; padding:18px; box-shadow:0 20px 50px rgba(0,0,0,0.75);">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid ${borderCol}; padding-bottom:10px; margin-bottom:12px;">
                        <h3 style="margin:0; font-size:1.1rem; font-weight:900; color:${txtCol};">⚖️ معاينة القيد المحاسبي المزدوج وتوزيع التكلفة — فاتورة #${invNo}</h3>
                        <button type="button" onclick="document.getElementById('purchase-journal-preview-modal').style.display='none'" style="background:#ef4444; color:#fff; border:none; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
                    </div>
                    <div style="background:${subBg}; border:1.5px solid ${borderCol}; border-radius:12px; padding:12px; margin-bottom:12px;">
                        <div style="font-weight:900; margin-bottom:8px; font-size:0.9rem; color:${txtCol};">📊 1. القيد المحاسبي المتوازن (المدين = الدائن = ${totalDebit.toLocaleString('en-US', { maximumFractionDigits: 2 })} ${cur}):</div>
                        <table style="width:100%; border-collapse:collapse; font-size:0.84rem;">
                            <thead>
                                <tr style="border-bottom:1.5px solid ${borderCol};">
                                    <th style="padding:6px; text-align:right; color:${txtCol};">الحساب المحاسبي</th>
                                    <th style="padding:6px; text-align:center; color:#10b981;">مدين (من ح/)</th>
                                    <th style="padding:6px; text-align:center; color:#f59e0b;">دائن (إلى ح/)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom:1px solid ${borderCol};">
                                    <td style="padding:6px; font-weight:800; color:${txtCol};">📦 ح/ مخزون البضاعة (${whName}) — شامل الترحيل والخصم</td>
                                    <td style="padding:6px; text-align:center; font-weight:900; color:#10b981;">${netInventoryDebit.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                                    <td style="padding:6px; text-align:center; color:${txtCol};">—</td>
                                </tr>
                                ${taxAmt > 0 ? `<tr style="border-bottom:1px solid ${borderCol};">
                                    <td style="padding:6px; font-weight:800; color:${txtCol};">💰 ح/ ضريبة القيمة المضافة للمدخلات</td>
                                    <td style="padding:6px; text-align:center; font-weight:900; color:#10b981;">${taxAmt.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                                    <td style="padding:6px; text-align:center; color:${txtCol};">—</td>
                                </tr>` : ''}
                                ${cashPaid > 0 ? `<tr style="border-bottom:1px solid ${borderCol};">
                                    <td style="padding:6px; font-weight:800; color:${txtCol};">💵 إلى ح/ الخزينة النقدية</td>
                                    <td style="padding:6px; text-align:center; color:${txtCol};">—</td>
                                    <td style="padding:6px; text-align:center; font-weight:900; color:#f59e0b;">${cashPaid.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                                </tr>` : ''}
                                ${bankPaid > 0 ? `<tr style="border-bottom:1px solid ${borderCol};">
                                    <td style="padding:6px; font-weight:800; color:${txtCol};">🏦 إلى ح/ البنك (تحويلات بنكية)</td>
                                    <td style="padding:6px; text-align:center; color:${txtCol};">—</td>
                                    <td style="padding:6px; text-align:center; font-weight:900; color:#f59e0b;">${bankPaid.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                                </tr>` : ''}
                                ${chequePaid > 0 ? `<tr style="border-bottom:1px solid ${borderCol};">
                                    <td style="padding:6px; text-align:center; color:${txtCol};">✍️ إلى ح/ أوراق الدفع والشيكات الصادرة</td>
                                    <td style="padding:6px; text-align:center; color:${txtCol};">—</td>
                                    <td style="padding:6px; text-align:center; font-weight:900; color:#f59e0b;">${chequePaid.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                                </tr>` : ''}
                                ${supplierCreditRemainder > 0 || totalDebit === 0 ? `<tr style="border-bottom:1px solid ${borderCol};">
                                    <td style="padding:6px; font-weight:800; color:${txtCol};">👤 إلى ح/ المورد (${supName}) — ذمم دائنة</td>
                                    <td style="padding:6px; text-align:center; color:${txtCol};">—</td>
                                    <td style="padding:6px; text-align:center; font-weight:900; color:#f59e0b;">${supplierCreditRemainder.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                                </tr>` : ''}
                            </tbody>
                        </table>
                    </div>
                    <div style="background:${subBg}; border:1.5px solid ${borderCol}; border-radius:12px; padding:12px; margin-bottom:12px;">
                        <div style="font-weight:900; margin-bottom:8px; font-size:0.9rem; color:${txtCol};">🚚 2. توزيع المصاريف الإضافية ومتوسط التكلفة الفعلي للوحدة (Landed Cost WAC):</div>
                        <table style="width:100%; border-collapse:collapse; font-size:0.82rem;">
                            <thead>
                                <tr style="border-bottom:1.5px solid ${borderCol};">
                                    <th style="padding:6px; text-align:right; color:${txtCol};">الصنف</th>
                                    <th style="padding:6px; text-align:center; color:${txtCol};">الكمية</th>
                                    <th style="padding:6px; text-align:center; color:${txtCol};">سعر الفاتورة</th>
                                    <th style="padding:6px; text-align:center; color:#f59e0b;">نصيب الترحيل/الخصم</th>
                                    <th style="padding:6px; text-align:center; color:#10b981;">التكلفة الفعلية للوحدة</th>
                                </tr>
                            </thead>
                            <tbody>${landedRowsHtml}</tbody>
                        </table>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                        <button type="button" onclick="window.printPurchaseItemsBarcodeLabels()" style="background:#7e22ce; color:#fff; border:none; border-radius:8px; padding:8px 14px; font-weight:900; cursor:pointer;">🏷️ طباعة ملصقات الباركود للأصناف</button>
                        <button type="button" onclick="document.getElementById('purchase-journal-preview-modal').style.display='none'; if(typeof window.smartValidateAndSubmitPurchase==='function') window.smartValidateAndSubmitPurchase();" style="background:#10b981; color:#fff; border:none; border-radius:8px; padding:8px 18px; font-weight:900; cursor:pointer;">✅ حفظ واعتماد الفاتورة الآن (F10)</button>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        };

        window.printPurchaseItemsBarcodeLabels = function() {
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && (r.itemId || r.itemName))
                : [];
            if (activeRows.length === 0) {
                if (typeof showToast === 'function') showToast('أضف أصنافاً أولاً لطباعة ملصقات الباركود الخاصة بها', 'warning');
                return;
            }
            const cur = (document.getElementById('hdr-currency')?.value || 'SDG').trim();
            const w = window.open('', '_blank', 'width=420,height=640');
            if (!w) return;
            const labelsHtml = activeRows.map(r => {
                const code = String(r.itemCode || r.itemId || '1001').slice(0, 14);
                const qty = Math.max(1, Math.min(20, Math.round(parseFloat(r.quantity) || 1)));
                const price = (parseFloat(r.price) || 0).toLocaleString('en-US');
                return `<div style="border:1.5px solid #000; border-radius:6px; padding:6px 8px; margin:6px auto; width:52mm; text-align:center; page-break-inside:avoid;">
                    <div style="font-size:11px; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${r.itemName || ''}</div>
                    <div style="font-family:monospace; font-size:15px; letter-spacing:3px; font-weight:900; margin:4px 0; border-top:2px solid #000; border-bottom:2px solid #000; padding:2px 0;">||| |||| || |||</div>
                    <div style="font-size:10px; font-weight:800;">#${code} • الكمية: ${qty} ${r.unitName || ''}</div>
                    <div style="font-size:11px; font-weight:900; margin-top:2px;">السعر: ${price} ${cur}</div>
                </div>`;
            }).join('');
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>ملصقات باركود المشتريات</title>
                <style>body{font-family:'Cairo','Tajawal',sans-serif; margin:0; padding:6px; color:#000;}</style>
                </head><body onload="window.print(); setTimeout(()=>window.close(), 600);">${labelsHtml}</body></html>`);
            w.document.close();
        };

        setTimeout(() => { window.refreshSupplierPerformanceRatingBadge(false); }, 450);

        // === 12. Constitution 4 Approved Proposals: +25% Ultra-Clarity Zoom, Auto-Contrast Guardian & New Tool Discovery Alert ===
        (function initConstitution4Proposals() {
            const zoomStyle = document.createElement('style');
            zoomStyle.id = 'pur-ultra-zoom-125-style';
            zoomStyle.textContent = `
                body.pur-ultra-zoom-125 #purchase-editor-view .h-label,
                body.pur-ultra-zoom-125 #purchase-editor-view label {
                    font-size: 1.06rem !important;
                    font-weight: 900 !important;
                }
                body.pur-ultra-zoom-125 #purchase-editor-view input:not([type="checkbox"]):not([type="radio"]),
                body.pur-ultra-zoom-125 #purchase-editor-view select,
                body.pur-ultra-zoom-125 #pos-grid tbody td input,
                body.pur-ultra-zoom-125 #pos-grid tbody td select,
                body.pur-ultra-zoom-125 #pos-grid tbody td .total-val,
                body.pur-ultra-zoom-125 #pos-grid tbody td .avail-val {
                    font-size: 1.18rem !important;
                    border-width: 2.8px !important;
                    min-height: 42px !important;
                }
                body.pur-ultra-zoom-125 #pos-grid thead th {
                    font-size: 1.08rem !important;
                    padding-top: 10px !important;
                    padding-bottom: 10px !important;
                }
            `;
            document.head.appendChild(zoomStyle);

            window.applyUltraZoomPurchaseState = function(enabled) {
                document.body.classList.toggle('pur-ultra-zoom-125', !!enabled);
                const lbl = document.getElementById('lbl-ultra-zoom-pur');
                const btn = document.getElementById('btn-ultra-zoom-pur');
                if (lbl) lbl.textContent = enabled ? 'وضع مكبّر ✓' : 'تكبير +25%';
                if (btn) {
                    btn.style.background = enabled
                        ? 'linear-gradient(135deg, #f59e0b, #d97706)'
                        : 'linear-gradient(135deg, rgba(245,158,11,0.18), rgba(217,119,6,0.3))';
                    btn.style.color = enabled ? '#020617' : '#fbbf24';
                }
            };

            window.toggleUltraZoomPurchaseMode = function() {
                const next = !document.body.classList.contains('pur-ultra-zoom-125');
                localStorage.setItem('erp_pur_ultra_zoom_125', next ? '1' : '0');
                window.applyUltraZoomPurchaseState(next);
                if (typeof showToast === 'function') {
                    showToast(next ? '🔍 تم تفعيل وضع القراءة فائقة الوضوح (+25% تكبير للخطوط والحواف)' : '🔍 تمت العودة لحجم العرض القياسي', 'info', 2600);
                }
            };

            if (localStorage.getItem('erp_pur_ultra_zoom_125') === '1') {
                window.applyUltraZoomPurchaseState(true);
            }

            // 2. 🛡️ فاحص وحارس التباين الذاتي التلقائي (Auto-Contrast Guardian)
            window.enforceAutoContrastGuardPurchase = function(rootNode) {
                const scope = (rootNode && rootNode.querySelectorAll) ? rootNode : document;
                const els = scope.querySelectorAll('#purchase-editor-view [style*="opacity"], #purchase-hub-view [style*="opacity"], .modal-overlay [style*="opacity"]');
                els.forEach(el => {
                    if (el.classList.contains('is-zero-val') || el.classList.contains('is-default-hint')) return;
                    const op = parseFloat(el.style.opacity);
                    if (!isNaN(op) && op > 0 && op < 0.85) {
                        el.style.opacity = '1';
                    }
                });
            };
            setTimeout(() => window.enforceAutoContrastGuardPurchase(document), 400);

            // 3. ✨ إشعار صوتي وبصري للمرشد عند اكتشاف زر جديد مضاف للشاشة
            let _knownStepCount = (window.MagaiesSmartGuides && window.MagaiesSmartGuides['purchase-direct'] && Array.isArray(window.MagaiesSmartGuides['purchase-direct'].autopilotSteps))
                ? window.MagaiesSmartGuides['purchase-direct'].autopilotSteps.length
                : 8;
            window.checkAndAnnounceNewPurchaseTools = function() {
                if (typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
                    window.syncDynamicDomElementsIntoSmartGuide('purchase-direct');
                }
                const g = window.MagaiesSmartGuides && window.MagaiesSmartGuides['purchase-direct'];
                if (!g || !Array.isArray(g.autopilotSteps)) return;
                if (g.autopilotSteps.length > _knownStepCount) {
                    const newest = g.autopilotSteps[g.autopilotSteps.length - 1];
                    _knownStepCount = g.autopilotSteps.length;
                    if (newest && newest.cursorLabel) {
                        if (typeof showToast === 'function') {
                            showToast(`✨ اكتشف المرشد الذكي تلقائياً أداة مضافة في الشاشة: (${newest.cursorLabel}) وضمّها للشرح!`, 'success', 3800);
                        }
                    }
                }
            };
            document.addEventListener('click', function(e) {
                if (e.target && e.target.closest('.msg-quartet-guide')) {
                    setTimeout(window.checkAndAnnounceNewPurchaseTools, 120);
                }
            }, true);
        })();
    })();
    </script>
</body>
</html>